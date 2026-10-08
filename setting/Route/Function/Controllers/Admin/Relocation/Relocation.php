<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin\Relocation;

use App\Config\Database;
use Setting\Route\Function\Controllers\Server\Network as ServerNetwork;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;
use Setting\Route\Function\Controllers\Admin\Group\Groups;
use Setting\Route\Function\Controllers\Admin\Group\Tools\GetUsers;
use Setting\Route\Function\Controllers\Admin\Admin;

// Центр переезда: массовый перенос клиентов между серверами + проверка доменов.
// Порядок переноса безопасный (create-first): сначала создаём ключ на новом,
// только потом удаляем старый. Списки от клиента игнорируем — всё пересчитываем.

class Relocation
{
    private const FILE = __DIR__ . '/migration.json';
    private const PER_TICK = 2;//клиентов за один HTTP-тик (каждый ~3-8 сек панели)

    // ==================== Состояние ====================

    public static function state(): array
    {
        if (!is_file(self::FILE)) return ['status' => 'idle'];
        $d = json_decode((string) @file_get_contents(self::FILE), true);
        return \is_array($d) ? $d : ['status' => 'idle'];
    }

    private static function saveState(array $st): void
    {
        $tmp = self::FILE . '.tmp';
        if (@file_put_contents($tmp, json_encode($st, JSON_UNESCAPED_UNICODE)) !== false) @rename($tmp, self::FILE);
    }

    /** Список серверов для UI (без секретов: только код/страна/URL). */
    public static function servers(): array
    {
        $out = [];
        foreach (ServerNetwork::getServerCodes() as $code) {
            $srv = ServerNetwork::selectServer(null, (string) $code);
            $out[] = [
                'code' => (string) $code,
                'country' => (string) ($srv['country'] ?? ''),
                'panel' => (string) ($srv['XUI_URL_PANEL'] ?? ''),
                'sub' => (string) ($srv['XUI_URL_SUBSCRIPTION'] ?? ''),
                'host' => (string) ($srv['VLESS_SERVER'] ?? ''),
            ];
        }
        return $out;
    }

    /** Сырой реестр для редактора (админка уже за AdminAuth). */
    public static function serversRaw(): array
    {
        return ServerNetwork::serversJson();
    }

    // ==================== Управление серверами ====================

    /** Удаление сервера: только если остаётся >1; клиентов переносим на указанный активный. */
    public static function deleteServer(string $code, string $target): array
    {
        $code = strtolower(trim($code));
        $target = strtolower(trim($target));
        $codes = ServerNetwork::getServerCodes();
        if (!\in_array($code, $codes, true)) return ['status' => 'error', 'message' => 'Нет такого сервера'];
        if (\count($codes) < 2) return ['status' => 'error', 'message' => 'Нельзя удалить последний сервер'];
        if ($target === '' || $target === $code) return ['status' => 'error', 'message' => 'Укажите другой активный сервер для переноса'];
        if (!\in_array($target, $codes, true)) return ['status' => 'error', 'message' => 'Целевой сервер не в реестре'];
        $chk = self::checkServer($target);
        if (!($chk['ok'] ?? false)) return ['status' => 'error', 'message' => 'Целевой сервер недоступен: ' . ($chk['note'] ?? '')];
        $left = self::serverClients($code);
        if ($left > 0) return ['status' => 'error', 'message' => "На $code ещё $left активных — сначала перенесите их"];
        $all = ServerNetwork::serversJson();
        if (!isset($all[$code])) {//реестр из дефолтов — нечего удалять из файла
            return ['status' => 'error', 'message' => 'Сервер только в дефолтах кода, уберите его из Network.php'];
        }
        unset($all[$code]);
        $saved = ServerNetwork::saveServersJson($all);
        if (($saved['status'] ?? '') !== 'ok') return $saved;
        (new Admin())->LoggerCRM("удалил сервер $code (перенос на $target завершён)");
        return ['status' => 'ok', 'message' => "Сервер $code удалён"];
    }

    /** Сколько активных подписок сейчас на сервере. */
    public static function serverClients(string $code): int
    {
        $n = 0;
        foreach (self::candidates() as $c) {
            if ($c['server'] === $code) $n++;
        }
        return $n;
    }

    /** Тик переноса только с одного сервера (для каскада перед удалением). */
    public static function migrateFrom(string $from, string $target, int $n = 2): array
    {
        $from = strtolower(trim($from));
        $target = strtolower(trim($target));
        if ($from === '' || $from === $target) return ['status' => 'error', 'message' => 'Некорректная пара серверов'];
        if (!\in_array($target, ServerNetwork::getServerCodes(), true)) return ['status' => 'error', 'message' => 'Нет целевого сервера'];
        $moved = 0;
        $failed = [];
        foreach (self::candidates() as $c) {
            if ($c['server'] !== $from) continue;
            if ($moved >= max(1, $n)) break;
            $r = self::moveOne($c, $target);
            if (($r['status'] ?? '') === 'ok') $moved++;
            else $failed[] = ['uniID' => $c['uniID'], 'email' => $c['email'], 'error' => (string) ($r['message'] ?? 'ошибка')];
        }
        $rest = 0;
        foreach (self::candidates() as $c) {
            if ($c['server'] === $from) $rest++;
        }
        return ['status' => 'ok', 'moved' => $moved, 'failed' => $failed, 'rest' => $rest];
    }

    // ==================== Кандидаты ====================

    /** Активные подписки с привязкой к текущему серверу (по субдомену их subscription). */
    private static function candidates(): array
    {
        $nowMs = (int) (microtime(true) * 1000);
        $rows = Database::send("SELECT s.uniID, u.email, s.subscription, s.expiry, s.count_devices FROM qwees_subscriptions s LEFT JOIN qwees_users u ON u.uniID = s.uniID WHERE s.status = 'on' AND s.expiry > ?", [$nowMs]);
        $rows = \is_array($rows) ? $rows : [];
        $out = [];
        foreach ($rows as $r) {
            $uni = (string) ($r['uniID'] ?? '');
            if ($uni === '') continue;
            $code = ServerNetwork::getServerCodeFromUrl((string) ($r['subscription'] ?? ''));
            if ($code === '') $code = 'gb';//старые строки без URL — считаем дефолтным сервером
            $out[] = [
                'uniID' => $uni,
                'email' => (string) ($r['email'] ?? ''),
                'server' => $code,
                'expiry' => (int) ($r['expiry'] ?? 0),
                'devices' => max(1, (int) ($r['count_devices'] ?? 1)),
            ];
        }
        return $out;
    }

    // ==================== Превью / старт / тик ====================

    public static function preview(string $target): array
    {
        self::ensurePermission();
        $target = strtolower(trim($target));
        $codes = ServerNetwork::getServerCodes();
        if (!\in_array($target, $codes, true)) return ['status' => 'error', 'message' => 'Нет такого сервера'];
        $cands = self::candidates();
        $move = array_values(array_filter($cands, fn($c) => $c['server'] !== $target));
        $already = \count($cands) - \count($move);
        $check = self::checkServer($target);
        return [
            'status' => 'ok', 'target' => $target,
            'target_ok' => (bool) ($check['ok'] ?? false), 'target_note' => (string) ($check['note'] ?? ''),
            'move' => \count($move), 'already' => $already,
            'sample' => \array_slice(array_map(fn($c) => $c['email'] !== '' ? $c['email'] : $c['uniID'], $move), 0, 10),
        ];
    }

    public static function start(string $target): array
    {
        $p = self::preview($target);
        if (($p['status'] ?? '') !== 'ok') return $p;
        if (!($p['target_ok'] ?? false)) return ['status' => 'error', 'message' => 'Целевой сервер недоступен: ' . ($p['target_note'] ?? '')];
        if (($p['move'] ?? 0) === 0) return ['status' => 'ok', 'message' => 'Все уже на ' . $target, 'state' => self::state()];
        $st = [
            'status' => 'running', 'target' => $target,
            'total' => (int) $p['move'], 'done' => 0, 'ok' => 0,
            'failed' => [], 'warnings' => [],
            'started_at' => date('Y-m-d H:i:s'), 'finished_at' => null,
        ];
        self::saveState($st);
        (new Admin())->LoggerCRM("старт переезда на $target: {$p['move']} клиентов");
        return ['status' => 'ok', 'state' => $st];
    }

    /** Один тик: переносит следующих PER_TICK клиентов. Фронт дёргает, пока status=running. */
    public static function tick(): array
    {
        $st = self::state();
        if (($st['status'] ?? 'idle') !== 'running') return ['status' => 'ok', 'state' => $st];
        $target = (string) ($st['target'] ?? '');
        $cands = self::candidates();
        $pending = array_values(array_filter($cands, fn($c) => $c['server'] !== $target));
        // уже обработанные в этом забеге пропускаем (done-счётчик + failed ждут retry)
        $skip = [];
        foreach ((array) ($st['failed'] ?? []) as $f) $skip[(string) ($f['uniID'] ?? '')] = true;
        $doneKeys = [];
        // сколько уже реально перенесено — считаем по факту ниже через done-счётчик
        $batch = [];
        foreach ($pending as $c) {
            if (isset($skip[$c['uniID']])) continue;
            if (\count($batch) >= self::PER_TICK) break;
            $batch[] = $c;
        }
        foreach ($batch as $c) {
            $r = self::moveOne($c, $target);
            $st['done'] = (int) ($st['done'] ?? 0) + 1;
            if (($r['status'] ?? '') === 'ok') {
                $st['ok'] = (int) ($st['ok'] ?? 0) + 1;
                if (($r['warning'] ?? '') !== '') $st['warnings'][] = $c['uniID'] . ': ' . $r['warning'];
            } else {
                $st['failed'][] = ['uniID' => $c['uniID'], 'email' => $c['email'], 'error' => (string) ($r['message'] ?? 'ошибка')];
            }
            self::saveState($st);
        }
        // Готово, если pending пуст
        $rest = 0;
        foreach (self::candidates() as $c) {
            if ($c['server'] === $target) continue;
            $isFailed = false;
            foreach ((array) ($st['failed'] ?? []) as $f) {
                if (($f['uniID'] ?? '') === $c['uniID']) {
                    $isFailed = true;
                    break;
                }
            }
            if (!$isFailed) $rest++;
        }
        if ($rest === 0) {
            $st['status'] = \count($st['failed'] ?? []) > 0 ? 'done_with_errors' : 'done';
            $st['finished_at'] = date('Y-m-d H:i:s');
            self::saveState($st);
            (new Admin())->LoggerCRM("переезд на $target завершён: ok {$st['ok']}, ошибок " . \count($st['failed'] ?? []));
        }
        return ['status' => 'ok', 'state' => $st, 'rest' => $rest];
    }

    /** Повтор ошибок: failed снова в очередь. */
    public static function retry(): array
    {
        $st = self::state();
        if (($st['status'] ?? '') !== 'done_with_errors') return ['status' => 'error', 'message' => 'Нечего повторять'];
        $st['failed'] = [];
        $st['status'] = 'running';
        $st['finished_at'] = null;
        self::saveState($st);
        return ['status' => 'ok', 'state' => $st];
    }

    /** Перенос одного клиента (create-first): новый ключ → проверка → удаление старого. */
    private static function moveOne(array $c, string $target): array
    {
        $uniID = $c['uniID'];
        $oldCode = $c['server'];
        $newUrl = rtrim((string) (ServerNetwork::selectServer(null, $target)['XUI_URL_SUBSCRIPTION'] ?? ''), '/') . '/' . $uniID;
        $oldUrl = null;
        $row = Database::send('SELECT subscription FROM qwees_subscriptions WHERE uniID = ? LIMIT 1', [$uniID]);
        if (!empty($row)) $oldUrl = (string) ($row[0]['subscription'] ?? '');
        // 1. переключаем БД на новый сервер (addClient выберет панель по субдомену)
        Database::send('UPDATE qwees_subscriptions SET subscription = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?', [$newUrl, $uniID]);
        // 2. создаём на новой панели с тем же сроком
        $added = (new Xray())->addClient(1, $uniID, $c['devices'], '', $c['expiry']);
        if (!\is_array($added) || ($added['success'] ?? false) !== true) {
            if ($oldUrl !== null) {//откат БД — старый ключ цел, клиент ничего не заметил
                Database::send('UPDATE qwees_subscriptions SET subscription = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?', [$oldUrl, $uniID]);
            }
            Xray::log(\sprintf("[%s] [ПЕРЕЕЗД] %s: не создан на %s, откат\n", date('Y-m-d H:i:s'), $uniID, $target));
            return ['status' => 'error', 'message' => 'Не создан на новом сервере'];
        }
        // 3. удаляем со старого (старый ключ уже не нужен)
        if ($oldCode !== $target) {
            $del = Xray::deleteClientFromPanel($uniID, $oldCode);
            if (($del['status'] ?? '') !== 'ok' && ($del['status'] ?? '') !== 'partial') {
                Xray::log(\sprintf("[%s] [ПЕРЕЕЗД] %s: создан на %s, но старый ключ не удалён: %s\n", date('Y-m-d H:i:s'), $uniID, $target, $del['message'] ?? ''));
                return ['status' => 'ok', 'warning' => 'старый ключ на ' . $oldCode . ' удалить вручную'];
            }
        }
        Xray::log(\sprintf("[%s] [ПЕРЕЕЗД] %s: %s -> %s ok\n", date('Y-m-d H:i:s'), $uniID, $oldCode, $target));
        return ['status' => 'ok'];
    }

    // ==================== Проверки ====================

    /** Жив ли сервер: панель отвечает + версия (IP берём из curl, без блокирующего DNS). */
    public static function checkServer(string $code): array
    {
        $codes = ServerNetwork::getServerCodes();
        if (!\in_array($code, $codes, true)) return ['ok' => false, 'note' => 'нет в реестре'];
        $srv = ServerNetwork::selectServer(null, $code);
        $panel = (string) ($srv['XUI_URL_PANEL'] ?? '');
        if ($panel === '') return ['ok' => false, 'note' => 'нет URL панели'];
        $t = microtime(true);
        $ch = curl_init(rtrim($panel, '/') . '/panel/api/server/status');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Authorization: Bearer ' . ($srv['XUI_API_TOKEN'] ?? '')],
            CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ip = (string) curl_getinfo($ch, CURLINFO_PRIMARY_IP);
        $ms = (int) round((microtime(true) - $t) * 1000);
        curl_close($ch);
        $d = json_decode((string) $body, true);
        if ($httpCode !== 200 || !\is_array($d) || ($d['success'] ?? false) !== true || !\is_array($d['obj'] ?? null)) {
            return ['ok' => false, 'note' => 'панель не отвечает' . ($ip !== '' ? ' (ip ' . $ip . ')' : '')];
        }
        $o = $d['obj'];
        return ['ok' => true, 'note' => 'xray ' . ($o['xray']['state'] ?? '?') . ' ' . ($o['xray']['version'] ?? '') . ', ip ' . $ip . ', ' . $ms . ' мс'];
    }

    /** Карта доменных зависимостей + живые проверки для блока «Домены». */
    public static function domains(): array
    {
        $out = [];
        $siteHost = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $out[] = ['name' => 'Сайт и оплаты', 'value' => $siteHost ?: '—', 'note' => 'динамика от HTTP_HOST — переезд = DNS + vhost, код не трогаем', 'ok' => $siteHost !== ''];
        foreach (ServerNetwork::getServerCodes() as $code) {
            $srv = ServerNetwork::selectServer(null, $code);
            $panel = (string) ($srv['XUI_URL_PANEL'] ?? '');
            $sub = (string) ($srv['XUI_URL_SUBSCRIPTION'] ?? '');
            $vless = (string) ($srv['VLESS_SERVER'] ?? '');
            $chk = self::checkServer($code);
            $out[] = ['name' => "Панель [$code]", 'value' => $panel, 'note' => (string) ($chk['note'] ?? ''), 'ok' => (bool) ($chk['ok'] ?? false)];
            $out[] = ['name' => "Подписки [$code]", 'value' => $sub, 'note' => 'раздаёт ключи клиентам', 'ok' => null];
            $out[] = ['name' => "VLESS-хост [$code]", 'value' => $vless, 'note' => 'зашит в ссылки клиентов — после смены им нужен refresh подписки', 'ok' => null];
        }
        $out[] = ['name' => 'База данных', 'value' => (string) ($_ENV['DB_HOST'] ?? ''), 'note' => 'меняется в .env (DB_HOST) + рестарт php', 'ok' => null];
        $out[] = ['name' => 'Контактная почта', 'value' => 'info@qweesvpn.online', 'note' => 'вручную: Functions::site() + MX-записи', 'ok' => null];
        return $out;
    }

    /** Самопроверка: закрыт ли веб-доступ к секретам (по правилам, без самозапроса — он дедлочит однопоточный сервер). */
    public static function exposure(): array
    {
        $ht = @file_get_contents(dirname(__DIR__, 6) . '/.htaccess');
        $ht = \is_string($ht) ? $ht : '';
        $blocksSetting = (bool) preg_match('#\^?\(setting\|app\|vendor\)#', $ht);
        $blocksEnv = str_contains($ht, '.env') && (str_contains($ht, 'denied') || str_contains($ht, '[F'));
        if ($blocksSetting && $blocksEnv) {
            return ['checked' => true, 'exposed' => false, 'note' => 'правила deny на месте (apache); для nginx добавьте location-deny из подсказки ниже'];
        }
        return ['checked' => true, 'exposed' => true, 'url' => 'setting/*.json, .env'];
    }

    private static function ensurePermission(): void
    {
        $groups = new Groups();
        foreach ((array) (new GetUsers())->getUsers() as $u) {
            $name = (string) ($u['username'] ?? '');
            if ($name === '') continue;
            if (!$groups->isPermission($name, 'main')) continue;
            foreach (['relocation', 'servers'] as $perm) {
                if (!$groups->isPermission($name, $perm)) $groups->addPermission($name, $perm);
            }
        }
    }
}
