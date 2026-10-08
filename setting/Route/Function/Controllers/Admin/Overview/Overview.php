<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin\Overview;

use App\Config\Database;
use Setting\Route\Function\Controllers\Server\Network as ServerNetwork;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;
use Setting\Route\Function\Controllers\Admin\Group\Groups;
use Setting\Route\Function\Controllers\Admin\Group\Tools\GetUsers;
use DateTime, DateTimeZone;

// Живой дашборд админки: состояние панелей 3x-ui + агрегаты из БД.
// Источники — apixray.md: /server/status, /server/history/{metric}/180,
// /inbounds/list. Периоды 24ч/7д/30д — дельты по собственным снапшотам
// (панель хранит историю только ~3 ч). Нет истории — честный null,
// фронт показывает «сбор данных», нули не выдумываем.

class Overview
{
    private const CACHE_FILE = __DIR__ . '/overview_cache.json';
    private const CACHE_TTL_MS = 60000;//не дёргаем панели чаще раза в минуту
    private const CACHE_V = 2;//структура clients_list (старый кэш инвалидируем)
    private const SNAPSHOT_EVERY_MS = 300000;//пишем снапшот не чаще раза в 5 минут
    private const ONLINE_WINDOW_MS = 300000;//lastOnline свежее 5 минут = в сети
    private const HISTORY_BUCKET = 180;//бакеты 120/300 панель отвергает (проверено живьём)
    private const DAY_MS = 86400000;

    /** Точка входа: готовый массив для JSON (с кэшем 60 сек; fresh — мимо кэша). */
    public static function getOverview(bool $fresh = false): array
    {
        if (!$fresh && ($hit = self::getCached()) !== null) {
            $hit['cached'] = true;
            return $hit;
        }
        return self::rebuild();
    }

    /** Прошлое из кэша (даже протухшее — для мгновенной отдачи). Null — кэша нет. */
    public static function getCached(): ?array
    {
        $nowMs = (int) (microtime(true) * 1000);
        $cached = self::readCache();
        if (!\is_array($cached) || !isset($cached['data'])) return null;
        if (($cached['v'] ?? 0) !== self::CACHE_V) return null;
        if (($nowMs - (int) ($cached['at_ms'] ?? 0)) > self::CACHE_TTL_MS) return null;
        return $cached['data'];
    }

    /** Полная сборка + запись кэша (атомарно через rename). */
    public static function rebuild(): array
    {
        $nowMs = (int) (microtime(true) * 1000);
        $data = self::build($nowMs);
        $data['cached'] = false;
        $tmp = self::CACHE_FILE . '.tmp';
        if (@file_put_contents($tmp, json_encode(['v' => self::CACHE_V, 'at_ms' => $nowMs, 'data' => $data], JSON_UNESCAPED_UNICODE)) !== false) {
            @rename($tmp, self::CACHE_FILE);
        }
        return $data;
    }

    /** Полная сборка: панели + БД + снапшоты. */
    private static function build(int $nowMs): array
    {
        self::ensureSnapshots();
        self::backfillPermission();
        $servers = [];
        foreach (ServerNetwork::getServerCodes() as $code) {
            $servers[] = self::serverData((string) $code, $nowMs);
        }
        $fresh = array_filter($servers, fn($s) => ($s['stale'] ?? true) === false);
        $sum = fn(string $k) => array_sum(array_map(fn($s) => (int) ($s[$k] ?? 0), $fresh));
        $totals = [
            'online_now' => $sum('online_now'),
            'clients' => $sum('clients'),
            'up_total' => $sum('up_total'),
            'down_total' => $sum('down_total'),
        ];
        $periods = ['24h' => self::DAY_MS, '7d' => 7 * self::DAY_MS, '30d' => 30 * self::DAY_MS];
        $midnight = (new DateTime('today', new DateTimeZone('Europe/Moscow')))->getTimestamp() * 1000;
        $histories = [];
        foreach ($fresh as $s) {
            $histories[(string) $s['code']] = self::historyWindow((string) $s['code'], $nowMs, $midnight);
        }
        $traffic = [];
        $collecting = false;
        foreach ($periods as $name => $span) {
            $up = 0;
            $down = 0;
            $ok = false;
            foreach ($fresh as $s) {
                $base = ($histories[(string) $s['code']]['cuts'] ?? [])[$span] ?? null;
                if ($base === null) {
                    $collecting = true;
                    continue;
                }
                $ok = true;
                $up += max(0, (int) $s['up_total'] - (int) ($base['up_bytes'] ?? 0));
                $down += max(0, (int) $s['down_total'] - (int) ($base['down_bytes'] ?? 0));
            }
            $traffic[$name] = $ok ? ['up' => $up, 'down' => $down, 'total' => $up + $down] : null;
        }
        $peak = 0;
        $peakOk = false;
        foreach ($fresh as $s) {
            $p = ($histories[(string) $s['code']] ?? [])['peak'] ?? null;
            if ($p !== null) {
                $peakOk = true;
                $peak = max($peak, (int) $p);
            }
        }
        $users = self::userStats();
        return [
            'status' => 'ok',
            'at_ms' => $nowMs,
            'stale' => $fresh === [],
            'collecting' => $collecting,
            'servers' => $servers,
            'totals' => $totals + ['peak_today' => $peakOk ? $peak : null],
            'traffic' => $traffic,
            'users' => $users,
        ];
    }

    /** Данные одного сервера: панель + дельты + пик. */
    private static function serverData(string $code, int $nowMs): array
    {
        ServerNetwork::selectServer(null, $code);
        $srv = ServerNetwork::getServer();
        $base = [
            'code' => $code,
            'country' => (string) ($srv['country'] ?? $code),
            'stale' => true,
        ];
        // Один параллельный заход: статус + инбаунды + 5 таймсерий (было 7 последовательных)
        $batch = Xray::panelBatch([
            '/panel/api/server/status',
            '/panel/api/inbounds/list',
            '/panel/api/server/history/cpu/' . self::HISTORY_BUCKET,
            '/panel/api/server/history/mem/' . self::HISTORY_BUCKET,
            '/panel/api/server/history/netUp/' . self::HISTORY_BUCKET,
            '/panel/api/server/history/netDown/' . self::HISTORY_BUCKET,
            '/panel/api/server/history/online/' . self::HISTORY_BUCKET,
        ]);
        $status = self::batchObj($batch['/panel/api/server/status'] ?? false);
        $inbounds = self::batchObj($batch['/panel/api/inbounds/list'] ?? false);
        if ($status === false || $inbounds === false) return $base;//панель недоступна
        $up = 0;
        $down = 0;
        $clients = [];
        foreach ($inbounds as $in) {
            $up += (int) ($in['up'] ?? 0);
            $down += (int) ($in['down'] ?? 0);
            foreach ((array) ($in['clientStats'] ?? []) as $c) {
                $clients[] = [
                    'email' => (string) ($c['email'] ?? ''),
                    'subId' => (string) ($c['subId'] ?? ''),
                    'up' => (int) ($c['up'] ?? 0),
                    'down' => (int) ($c['down'] ?? 0),
                    'enable' => (bool) ($c['enable'] ?? false),
                    'online' => ($nowMs - (int) ($c['lastOnline'] ?? 0)) < self::ONLINE_WINDOW_MS,
                ];
            }
        }
        $onlineNow = \count(array_filter($clients, fn($c) => $c['online'] && $c['enable']));
        self::writeSnapshot($code, $up, $down, $onlineNow, $nowMs);
        usort($clients, fn($a, $b) => $b['down'] <=> $a['down']);
        $mem = (array) ($status['mem'] ?? []);
        $disk = (array) ($status['disk'] ?? []);
        $swap = (array) ($status['swap'] ?? []);
        $xray = (array) ($status['xray'] ?? []);
        $netTraffic = (array) ($status['netTraffic'] ?? []);
        $appStats = (array) ($status['appStats'] ?? []);
        $publicIP = (array) ($status['publicIP'] ?? []);
        return array_merge($base, [
            'stale' => false,
            'online_now' => $onlineNow,
            'clients' => \count($clients),
            'up_total' => $up,
            'down_total' => $down,
            'up_total_h' => self::formatBytes($up),
            'down_total_h' => self::formatBytes($down),
            'cpu' => round((float) ($status['cpu'] ?? 0), 1),
            'cpu_sub' => trim((int) ($status['cpuCores'] ?? 0) . ' Core / ' . (int) ($status['logicalPro'] ?? 0) . 'T · ' . round((float) ($status['cpuSpeedMhz'] ?? 0) / 1000, 2) . ' GHz', ' /·'),
            'mem_used' => (int) ($mem['current'] ?? 0),
            'mem_total' => (int) ($mem['total'] ?? 0),
            'mem_h' => self::formatBytes((int) ($mem['current'] ?? 0)) . ' / ' . self::formatBytes((int) ($mem['total'] ?? 0)),
            'swap_used' => (int) ($swap['current'] ?? 0),
            'swap_total' => (int) ($swap['total'] ?? 0),
            'swap_h' => self::formatBytes((int) ($swap['current'] ?? 0)) . ' / ' . self::formatBytes((int) ($swap['total'] ?? 0)),
            'disk_used' => (int) ($disk['current'] ?? 0),
            'disk_total' => (int) ($disk['total'] ?? 0),
            'disk_h' => self::formatBytes((int) ($disk['current'] ?? 0)) . ' / ' . self::formatBytes((int) ($disk['total'] ?? 0)),
            'disk_free_h' => self::formatBytes(max(0, (int) ($disk['total'] ?? 0) - (int) ($disk['current'] ?? 0))),
            'uptime_s' => (int) ($status['uptime'] ?? 0),
            'uptime_h' => self::formatUptime((int) ($status['uptime'] ?? 0)),
            'xray_state' => (string) ($xray['state'] ?? 'unknown'),
            'xray_version' => (string) ($xray['version'] ?? ''),
            'tcp' => (int) ($status['tcpCount'] ?? 0),
            'udp' => (int) ($status['udpCount'] ?? 0),
            'net_sent' => (int) ($netTraffic['sent'] ?? 0),
            'net_recv' => (int) ($netTraffic['recv'] ?? 0),
            'net_sent_h' => self::formatBytes((int) ($netTraffic['sent'] ?? 0)),
            'net_recv_h' => self::formatBytes((int) ($netTraffic['recv'] ?? 0)),
            'panel_mem_h' => self::formatBytes((int) ($appStats['mem'] ?? 0)),
            'panel_threads' => (int) ($appStats['threads'] ?? 0),
            'panel_uptime_h' => self::formatUptime((int) ($appStats['uptime'] ?? 0)),
            'ip' => (string) ($publicIP['ipv4'] ?? ''),
            'history' => self::serverHistory($batch),
            'clients_list' => array_map(fn($c) => ['subId' => $c['subId'], 'email' => $c['email'], 'enable' => $c['enable']], $clients),
            'top' => \array_slice(array_map(fn($c) => $c + ['down_h' => self::formatBytes($c['down'])], $clients), 0, 5),
        ]);
    }

    /** Таймсерии ~3 ч для спарклайнов (по одной точке в 3 мин). Данные уже приехали батчем. */
    private static function serverHistory(array $batch): array
    {
        $out = [];
        foreach (['cpu' => 'cpu', 'mem' => 'mem', 'netUp' => 'netUp', 'netDown' => 'netDown', 'online' => 'online'] as $k => $m) {
            $pts = self::batchObj($batch['/panel/api/server/history/' . $m . '/' . self::HISTORY_BUCKET] ?? false);
            $out[$k] = $pts === false ? [] : array_map(fn($p) => [(int) ($p['t'] ?? 0), (float) ($p['v'] ?? 0)], $pts);
        }
        return $out;
    }

    /** Распаковка {success,obj} из батча. */
    private static function batchObj(array|false $res): array|false
    {
        if (!\is_array($res) || ($res['success'] ?? false) !== true || !\is_array($res['obj'] ?? null)) return false;
        return $res['obj'];
    }

    /** Агрегаты пользователей из БД (один запрос). */
    private static function userStats(): array
    {
        if (Database::isMysql()) {
            $dateCond = "DATE(created_at) = CURDATE()";
        } else {
            $dateCond = "DATE(created_at) = DATE('now')";
        }
        $row = Database::send("SELECT (SELECT COUNT(*) FROM qwees_users) total, (SELECT COUNT(*) FROM qwees_subscriptions WHERE status = 'on') active, (SELECT COUNT(*) FROM qwees_users WHERE $dateCond) today");
        return [
            'total' => (int) (($row[0]['total'] ?? 0)),
            'active' => (int) (($row[0]['active'] ?? 0)),
            'new_today' => (int) (($row[0]['today'] ?? 0)),
        ];
    }

    // ==================== Снапшоты ====================

    private static function ensureSnapshots(): void
    {
        Database::send('CREATE TABLE IF NOT EXISTS qwees_traffic_snapshots (server VARCHAR(16) NOT NULL, up_bytes BIGINT NOT NULL DEFAULT 0, down_bytes BIGINT NOT NULL DEFAULT 0, online_now INT NOT NULL DEFAULT 0, at_ms BIGINT NOT NULL, PRIMARY KEY (server, at_ms))');
    }

    /** Пишем снапшот не чаще раза в 5 минут (троттл — локальный файл, без запроса в БД). */
    private static function writeSnapshot(string $server, int $up, int $down, int $online, int $nowMs): void
    {
        $flag = sys_get_temp_dir() . '/qwees_snap_' . preg_replace('/[^a-z0-9]/i', '', $server);
        $last = is_file($flag) ? (int) @file_get_contents($flag) : 0;
        if (($nowMs - $last) < self::SNAPSHOT_EVERY_MS) return;
        Database::send('INSERT INTO qwees_traffic_snapshots (server, up_bytes, down_bytes, online_now, at_ms) VALUES (?, ?, ?, ?, ?)', [$server, $up, $down, $online, $nowMs]);
        @file_put_contents($flag, (string) $nowMs);
        if (random_int(1, 20) === 1) {
            Database::send('DELETE FROM qwees_traffic_snapshots WHERE at_ms < ?', [$nowMs - 35 * self::DAY_MS]);
        }
    }

    /**
     * История сервера двумя запросами: срезы 24ч/7д/30д одним UNION + пик сегодня.
     * @return array{cuts:array<int,array>,peak:?int}
     */
    private static function historyWindow(string $server, int $nowMs, int $midnight): array
    {
        $cuts = [];
        $rows = Database::send(
            "(SELECT 'd1' AS k, up_bytes, down_bytes FROM qwees_traffic_snapshots WHERE server = ? AND at_ms <= ? ORDER BY at_ms DESC LIMIT 1)
             UNION ALL
             (SELECT 'd7' AS k, up_bytes, down_bytes FROM qwees_traffic_snapshots WHERE server = ? AND at_ms <= ? ORDER BY at_ms DESC LIMIT 1)
             UNION ALL
             (SELECT 'd30' AS k, up_bytes, down_bytes FROM qwees_traffic_snapshots WHERE server = ? AND at_ms <= ? ORDER BY at_ms DESC LIMIT 1)",
            [$server, $nowMs - self::DAY_MS, $server, $nowMs - 7 * self::DAY_MS, $server, $nowMs - 30 * self::DAY_MS]
        );
        $map = ['d1' => self::DAY_MS, 'd7' => 7 * self::DAY_MS, 'd30' => 30 * self::DAY_MS];
        foreach ((\is_array($rows) ? $rows : []) as $r) {
            if (isset($map[$r['k'] ?? ''])) $cuts[$map[$r['k']]] = $r;
        }
        $peak = Database::send('SELECT MAX(online_now) m FROM qwees_traffic_snapshots WHERE server = ? AND at_ms >= ?', [$server, $midnight]);
        return ['cuts' => $cuts, 'peak' => isset($peak[0]['m']) && $peak[0]['m'] !== null ? (int) $peak[0]['m'] : null];
    }

    /** Раздаём право monitoring всем, у кого уже есть main (аддитивно, идемпотентно). */
    private static function backfillPermission(): void
    {
        $groups = new Groups();
        foreach ((array) (new GetUsers())->getUsers() as $u) {
            $name = (string) ($u['username'] ?? '');
            if ($name === '') continue;
            if (!$groups->isPermission($name, 'main')) continue;
            foreach (['monitoring', 'profit'] as $perm) {
                if (!$groups->isPermission($name, $perm)) $groups->addPermission($name, $perm);
            }
        }
    }

    // ==================== Самопроверки ====================

    private const HEALTH_FILE = __DIR__ . '/health_cache.json';
    private const HEALTH_TTL_MS = 300000;//сверка тяжёлая — кэш 5 минут

    /** Сверка БД↔панель + проверка подписки как у клиента. */
    public static function getHealth(bool $fresh = false): array
    {
        $nowMs = (int) (microtime(true) * 1000);
        if (!$fresh && is_file(self::HEALTH_FILE)) {
            $hit = json_decode((string) @file_get_contents(self::HEALTH_FILE), true);
            if (\is_array($hit) && ($nowMs - (int) ($hit['at_ms'] ?? 0)) < self::HEALTH_TTL_MS && isset($hit['data'])) {
                $hit['data']['cached'] = true;
                return $hit['data'];
            }
        }
        $data = self::buildHealth($nowMs);
        $data['cached'] = false;
        $tmp = self::HEALTH_FILE . '.tmp';
        if (@file_put_contents($tmp, json_encode(['at_ms' => $nowMs, 'data' => $data], JSON_UNESCAPED_UNICODE)) !== false) {
            @rename($tmp, self::HEALTH_FILE);
        }
        return $data;
    }

    private static function buildHealth(int $nowMs): array
    {
        $ov = self::getOverview();
        $panel = [];//ключ (subId, иначе email) => ['server','email','enable']
        foreach ((array) ($ov['servers'] ?? []) as $s) {
            if (($s['stale'] ?? true)) continue;
            foreach ((array) ($s['clients_list'] ?? []) as $c) {
                $key = ($c['subId'] ?? '') !== '' ? $c['subId'] : ($c['email'] ?? '');
                if ($key === '') continue;
                $panel[$key] = ['server' => (string) ($s['code'] ?? ''), 'email' => (string) ($c['email'] ?? ''), 'enable' => (bool) ($c['enable'] ?? false)];
            }
        }
        $rows = Database::send('SELECT s.uniID, u.email, s.status, s.expiry FROM qwees_subscriptions s LEFT JOIN qwees_users u ON u.uniID = s.uniID');
        $rows = \is_array($rows) ? $rows : [];
        $byUni = [];
        foreach ($rows as $r) $byUni[(string) ($r['uniID'] ?? '')] = $r;
        $missing = [];//оплачено в БД, а ключа в панели нет
        foreach ($rows as $r) {
            $uni = (string) ($r['uniID'] ?? '');
            if ($uni === '') continue;
            $active = (($r['status'] ?? '') === 'on') && ((int) ($r['expiry'] ?? 0) > $nowMs);
            if (!$active) continue;
            if (!isset($panel[$uni]) && !isset($panel[(string) ($r['email'] ?? '')])) {
                $missing[] = ['uniID' => $uni, 'email' => (string) ($r['email'] ?? '')];
            }
        }
        $ghosts = [];//ключ в панели, а активной подписки в БД нет
        foreach ($panel as $key => $p) {
            $r = $byUni[$key] ?? null;
            if ($r === null) {
                foreach ($rows as $rr) {
                    if ((string) ($rr['email'] ?? '') === $key) {
                        $r = $rr;
                        break;
                    }
                }
            }
            $active = $r !== null && (($r['status'] ?? '') === 'on') && ((int) ($r['expiry'] ?? 0) > $nowMs);
            if (!$active) $ghosts[] = ['key' => $key, 'email' => $p['email'], 'server' => $p['server'], 'enable' => $p['enable']];
        }
        $subs = [];
        foreach ((array) ($ov['servers'] ?? []) as $s) {
            if (($s['stale'] ?? true)) continue;
            $subs[] = self::checkSubscription((string) ($s['code'] ?? ''), (array) ($s['clients_list'] ?? []));
        }
        $ok = ($missing === [] && $ghosts === []) && array_reduce($subs, fn($a, $x) => $a && ($x['ok'] ?? false), true);
        return [
            'status' => 'ok',
            'at_ms' => $nowMs,
            'ok' => $ok,
            'panel_clients' => \count($panel),
            'db_rows' => \count($rows),
            'missing' => \array_slice($missing, 0, 50),
            'missing_count' => \count($missing),
            'ghosts' => \array_slice($ghosts, 0, 50),
            'ghosts_count' => \count($ghosts),
            'subscriptions' => $subs,
        ];
    }

    /** Качаем подписку как клиент и валидируем ссылки (секреты наружу не отдаём — только факты). */
    private static function checkSubscription(string $code, array $clients): array
    {
        $base = ['server' => $code, 'ok' => false, 'checks' => []];
        $subId = '';
        foreach ($clients as $c) {
            if (($c['enable'] ?? false) && ($c['subId'] ?? '') !== '') {
                $subId = $c['subId'];
                break;
            }
        }
        if ($subId === '') return $base + ['error' => 'нет клиента для проверки'];
        ServerNetwork::selectServer(null, $code);
        $url = rtrim((string) (ServerNetwork::getServer()['XUI_URL_SUBSCRIPTION'] ?? ''), '/') . '/' . $subId;
        if ($url === '/' . $subId) return $base + ['error' => 'нет URL подписки'];
        $t = microtime(true);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'QweesVPN-healthcheck/1.0',
        ]);
        $body = curl_exec($ch);
        $code_http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        $ms = (int) round((microtime(true) - $t) * 1000);
        $checks = ['http' => $code_http === 200 && $err === '', 'non_empty' => \is_string($body) && strlen($body) > 0];
        $links = [];
        $text = trim((string) ($body ?? ''));
        if ($text !== '' && strpos($text, '://') === false) {//подписка обычно base64-блобом
            $dec = base64_decode($text, true);
            if ($dec !== false && strpos($dec, '://') !== false) $text = $dec;
        }
        if ($text !== '') {
            foreach (preg_split('/\s+/', $text) as $ln) {
                if (str_starts_with($ln, 'vless://')) $links[] = $ln;
            }
        }
        $checks['has_links'] = $links !== [];
        $params = [];
        if ($links !== []) {
            parse_str((string) (parse_url($links[0], PHP_URL_QUERY) ?? ''), $params);
        }
        $checks['reality'] = ($params['security'] ?? '') === 'reality';
        $checks['pbk'] = ($params['pbk'] ?? '') !== '';
        $checks['sni'] = ($params['sni'] ?? '') !== '';
        $base['checks'] = $checks;
        $base['ms'] = $ms;
        $base['link_count'] = \count($links);
        $base['ok'] = !\in_array(false, $checks, true);
        if (!$base['ok'] && $err !== '') $base['error'] = mb_substr($err, 0, 120);
        return $base;
    }

    // ==================== Очистка неактивных ====================
    // Правило: удаляем строки подписок, где НЕ (status='on' И срок в будущем).
    // Никогда не трогаем: pending_vpn (оплачено, ждёт ключ) и banned (решение модерации).
    // Ключи в панели удаляем всем призракам (нет активной подписки в БД).

    /** Превью очистки: что будет удалено, без изменений. */
    public static function cleanupPreview(): array
    {
        $nowMs = (int) (microtime(true) * 1000);
        $rows = Database::send("SELECT s.uniID, u.email, s.status, s.expiry FROM qwees_subscriptions s LEFT JOIN qwees_users u ON u.uniID = s.uniID WHERE (s.status <> 'on' OR s.expiry <= ?) AND s.status NOT IN ('pending_vpn','banned')", [$nowMs]);
        $rows = \is_array($rows) ? $rows : [];
        $expired = [];
        $off = [];
        foreach ($rows as $r) {
            if (($r['status'] ?? '') === 'on') $expired[] = $r;
            else $off[] = $r;
        }
        $skBanned = Database::send("SELECT COUNT(*) c FROM qwees_subscriptions WHERE status = 'banned'");
        $skPending = Database::send("SELECT COUNT(*) c FROM qwees_subscriptions WHERE status = 'pending_vpn'");
        $health = self::getHealth(true);
        return [
            'status' => 'ok',
            'db_expired' => array_map(fn($r) => ['uniID' => (string) ($r['uniID'] ?? ''), 'email' => (string) ($r['email'] ?? '')], \array_slice($expired, 0, 100)),
            'db_expired_count' => \count($expired),
            'db_off' => array_map(fn($r) => ['uniID' => (string) ($r['uniID'] ?? ''), 'email' => (string) ($r['email'] ?? '')], \array_slice($off, 0, 100)),
            'db_off_count' => \count($off),
            'skipped_banned' => (int) (($skBanned[0]['c'] ?? 0)),
            'skipped_pending' => (int) (($skPending[0]['c'] ?? 0)),
            'panel_ghosts' => $health['ghosts'] ?? [],
            'panel_ghosts_count' => (int) ($health['ghosts_count'] ?? 0),
        ];
    }

    /** Выполнение очистки: пересчитываем всё на сервере, списки от клиента игнорируем. */
    public static function cleanupExecute(): array
    {
        $nowMs = (int) (microtime(true) * 1000);
        $res = ['status' => 'ok', 'db_deleted' => 0, 'panel_deleted' => [], 'panel_failed' => []];
        $rows = Database::send("SELECT uniID FROM qwees_subscriptions WHERE (status <> 'on' OR expiry <= ?) AND status NOT IN ('pending_vpn','banned')", [$nowMs]);
        $rows = \is_array($rows) ? $rows : [];
        if ($rows !== []) {
            $del = Database::send("DELETE FROM qwees_subscriptions WHERE (status <> 'on' OR expiry <= ?) AND status NOT IN ('pending_vpn','banned')", [$nowMs]);
            if ($del === false) return ['status' => 'error', 'message' => 'Не удалось очистить базу'];
            $res['db_deleted'] = \count($rows);
        }
        $health = self::getHealth(true);
        foreach ((array) ($health['ghosts'] ?? []) as $g) {
            $key = (string) ($g['key'] ?? '');
            if ($key === '') continue;
            $del = \Setting\Route\Function\Controllers\Vpn\V2ray\Xray::deleteClientFromPanel($key, (string) ($g['server'] ?? '') !== '' ? (string) $g['server'] : null);
            $st = (string) ($del['status'] ?? '');
            if ($st === 'ok' || $st === 'partial') $res['panel_deleted'][] = $key;//partial = ключа уже нет, цель достигнута
            else $res['panel_failed'][] = ['key' => $key, 'error' => (string) ($del['message'] ?? 'ошибка')];
        }
        @unlink(self::HEALTH_FILE);
        @unlink(self::CACHE_FILE);
        (new \Setting\Route\Function\Controllers\Admin\Admin())->LoggerCRM(
            "очистка неактивных: строк БД {$res['db_deleted']}, ключей панели " . \count($res['panel_deleted']) . ", ошибок " . \count($res['panel_failed'])
        );
        return $res;
    }

    private static function readCache(): ?array
    {
        if (!is_file(self::CACHE_FILE)) return null;
        $d = json_decode((string) @file_get_contents(self::CACHE_FILE), true);
        return \is_array($d) ? $d : null;
    }

    // ==================== Формат ====================

    public static function formatBytes(int|float $bytes): string
    {
        $bytes = max(0, (float) $bytes);
        if ($bytes < 1024) return (int) $bytes . ' B';
        $units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, \count($units));
        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i - 1];
    }

    public static function formatUptime(int $sec): string
    {
        $sec = max(0, $sec);
        $d = (int) floor($sec / 86400);
        $h = (int) floor(($sec % 86400) / 3600);
        $m = (int) floor(($sec % 3600) / 60);
        return $d > 0 ? "{$d}д {$h}ч" : ($h > 0 ? "{$h}ч {$m}м" : "{$m}м");
    }
}
