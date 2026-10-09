<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Server;

use App\Config\Database;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;

/**
 * Выбор VPN-сервера.
 *
 * Все серверы лежат в одном реестре SERVERS (раньше эти данные были в .env).
 * Сервер клиента определяется по субдомену его подписки qwees_subscription
 * (nl.qweesvpn.online -> nl), без подписки — сервер по умолчанию.
 *
 * Схема работы: Network::selectServer($uniID) выбирает сервер -> Network::getServer() даёт его данные.
 */
class Network
{

    /** Серверы по умолчанию (страховка). Живой реестр — servers.json рядом (правится из админки без деплоя). */
    private const DEFAULT_SERVERS = [
        'gb' => [
            'country' => 'London',
            'flag' => 'london.svg',
            'XUI_API_TOKEN' => "hz4QtjFxLdfuqatWop7yFFiKOpasegmlsV43Bw5KuoiuKA39", #куки токен
            'XUI_LOGIN' => "timqwees", #логин
            'XUI_PASSWORD' => "timqwees2018$", #пароль
            'XUI_LOGIN_NAME_COOKIE' => "x-ui", #имя куки
            'XUI_URL_PANEL' => "https://gb.qweesvpn.online:12200/qwees_administrator", #панель
            'XUI_URL_SUBSCRIPTION' => "https://gb.qweesvpn.online:1005/qweesteam_subscription/", #сабскрипшн
            'XUI_INBOUND_NUMBER' => 0, #номер инбаунда
            'VLESS_SERVER' => "gb.qweesvpn.online", #сервер для vless
        ],
    ];

    /** Сервер по умолчанию (используется клиентами без подписки). */
    private const DEFAULT_SERVER = 'gb';

    /** Код текущего выбранного сервера. */
    private static string $current = self::DEFAULT_SERVER;

    /** Запиненный сервер: пока взведён, selectServer() не перевыбирает (фолбэк выдачи). */
    private static bool $pinned = false;

    /** Мемо URL подписки на запрос (SELECT за render — один, а не пачка). Сброс — forgetSub(). */
    private static array $subCache = [];

    /** Сбросить мемо подписки (после любой перезаписи subscription в БД). */
    public static function forgetSub(?string $uniID = null): void
    {
        if ($uniID === null) self::$subCache = [];
        else unset(self::$subCache[$uniID]);
    }

    /** Зафиксировать сервер на время операции (только код из реестра). */
    public static function pinServer(string $code): void
    {
        if (isset(self::servers()[$code])) {
            self::$current = $code;
            self::$pinned = true;
        }
    }

    /** Снять фиксацию (текущий код остаётся последним удачным). */
    public static function unpinServer(): void
    {
        self::$pinned = false;
    }

    private static ?array $serversCache = null;

    /** Живой реестр: servers.json поверх дефолтов; битый файл — игнор с логом. */
    private static function servers(): array
    {
        if (self::$serversCache !== null) return self::$serversCache;
        $base = self::DEFAULT_SERVERS;
        $file = __DIR__ . '/servers.json';
        if (is_file($file)) {
            $json = json_decode((string) @file_get_contents($file), true);
            if (\is_array($json) && $json !== []) {
                $valid = [];
                foreach ($json as $code => $srv) {
                    $code = (string) $code;
                    if (!\is_array($srv) || ($srv['VLESS_SERVER'] ?? '') === '' || ($srv['XUI_URL_PANEL'] ?? '') === '') continue;
                    $valid[$code] = $srv + ($base[$code] ?? []);
                }
                if ($valid !== []) $base = $valid;
            }
        }
        return self::$serversCache = $base;
    }

    /** Эффективный реестр (файл поверх дефолтов) — для редактора в админке. */
    public static function effectiveServers(): array
    {
        return self::servers();
    }

    /** Сырое содержимое servers.json для админки (редактирование). */
    public static function serversJson(): array
    {
        $file = __DIR__ . '/servers.json';
        if (!is_file($file)) return [];
        $json = json_decode((string) @file_get_contents($file), true);
        return \is_array($json) ? $json : [];
    }

    /** Сохранить реестр из админки (валидация + бэкап предыдущего). */
    public static function saveServersJson(array $servers): array
    {
        foreach ($servers as $code => $srv) {
            if (!\is_array($srv) || ($srv['VLESS_SERVER'] ?? '') === '' || ($srv['XUI_URL_PANEL'] ?? '') === '') {
                return ['status' => 'error', 'message' => 'Сервер ' . $code . ': нужны VLESS_SERVER и XUI_URL_PANEL'];
            }
        }
        $file = __DIR__ . '/servers.json';
        if (is_file($file)) @copy($file, $file . '.bak');
        $ok = @file_put_contents($file, json_encode($servers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        self::$serversCache = null;
        if ($ok === false) return ['status' => 'error', 'message' => 'Не удалось записать файл'];
        return ['status' => 'ok', 'message' => 'Реестр сохранён'];
    }

    /**
     * Выбрать сервер: по коду ('nl') или по подписке пользователя.
     * Без аргументов — сервер текущего пользователя сессии (или дефолтный).
     */
    public static function selectServer(?string $uniID = null, ?string $code = null): array
    {
        // Запиненный сервер (фолбэк выдачи): не перевыбираем, работаем на нём.
        if (self::$pinned && isset(self::servers()[self::$current])) {
            return self::servers()[self::$current];
        }
        if ($code !== null && isset(self::servers()[$code])) {
            self::$current = $code;
            return self::servers()[$code];
        }

        $uniID = $uniID ?? self::sessionUniID();
        if (!isset(self::$subCache[$uniID])) {
            self::$subCache[$uniID] = $uniID !== ''
                ? (string) (Database::send('SELECT subscription FROM qwees_subscriptions WHERE uniID = ?', [$uniID])[0]['subscription'] ?? '')
                : '';
        }
        $sub = self::$subCache[$uniID];

        self::$current = self::getServerCodeFromUrl($sub) ?: self::DEFAULT_SERVER;
        return self::servers()[self::$current];
    }

    /** Данные текущего сервера (после selectServer()). С протухшим кодом не падаем. */
    public static function getServer(): array
    {
        $all = self::servers();
        if (!isset($all[self::$current])) {
            self::$current = array_key_first($all) ?: self::DEFAULT_SERVER;
        }
        return $all[self::$current] ?? [];
    }

    /** Код текущего сервера ('nl', 'fi', ...). */
    public static function getServerCode(): string
    {
        return self::$current;
    }

    /** Коды всех серверов реестра (для перебора панелей). */
    public static function getServerCodes(): array
    {
        return array_keys(self::servers());
    }

    /**
     * Список серверов для будущего UI выбора локации пользователем.
     */
    public static function getAvailableServers(): array
    {
        $list = [];
        foreach (self::servers() as $code => $server) {
            $list[] = ['code' => $code, 'country' => $server['country'], 'flag' => $server['flag'], 'host' => $server['VLESS_SERVER']];
        }
        return $list;
    }

    /** Код сервера из URL/хоста подписки: https://nl.qweesvpn.online:1005/... => nl */
    public static function getServerCodeFromUrl(string $url): string
    {
        $host = strtolower((string) (parse_url(trim($url), PHP_URL_HOST) ?: ''));
        $first = explode('.', $host)[0] ?? '';
        return isset(self::servers()[$first]) ? $first : '';
    }

    /** Полный URL подписки пользователя на его сервере. */
    public static function getSubscriptionUrl(string $uniID = ''): string
    {
        $uniID = $uniID !== '' ? $uniID : self::sessionUniID();
        return rtrim(self::selectServer($uniID)['XUI_URL_SUBSCRIPTION'], '/') . '/' . $uniID;
    }

    /**
     * Смена сервера пользователем: переносим клиента на новую панель 3x-ui
     * с тем же сроком и перезаписываем подписку в БД (меняется субдомен).
     *
     * @return array{status: string, message: string}
     */
    public static function switchServer(string $uniID, string $newCode): array
    {
        $uniID = trim($uniID);
        $newCode = strtolower(trim($newCode));

        if (!isset(self::servers()[$newCode])) {
            return ['status' => 'error', 'message' => 'Сервер недоступен: ' . $newCode];
        }

        $sub = Database::send('SELECT status, expiry, count_devices FROM qwees_subscriptions WHERE uniID = ?', [$uniID]);
        if (($sub[0]['status'] ?? '') !== 'on') {
            return ['status' => 'error', 'message' => 'Активная подписка не найдена'];
        }

        $expiryMs = (int) ($sub[0]['expiry'] ?? 0);
        if ($expiryMs <= round(microtime(true) * 1000)) {
            return ['status' => 'error', 'message' => 'Подписка истекла'];
        }

        // текущий сервер клиента (по субдомену его подписки)
        self::selectServer($uniID);
        $oldCode = self::getServerCode();
        if ($oldCode === $newCode) {
            return ['status' => 'ok', 'message' => 'Этот сервер уже используется'];
        }

        $oldUrl = rtrim(self::servers()[$oldCode]['XUI_URL_SUBSCRIPTION'], '/') . '/' . $uniID;
        $newUrl = rtrim(self::servers()[$newCode]['XUI_URL_SUBSCRIPTION'], '/') . '/' . $uniID;

        // 1. удаляем клиента со старой панели (запись БД пока остаётся)
        $deleted = Xray::deleteClientFromPanel($uniID);
        if (($deleted['status'] ?? '') === 'error') {
            return ['status' => 'error', 'message' => 'Не удалось отвязать клиента от текущего сервера'];
        }

        // 2. перезаписываем подписку — теперь addClient сам выберет новую панель по субдомену
        Database::send(
            'UPDATE qwees_subscriptions SET subscription = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?',
            [$newUrl, $uniID]
        );
        self::forgetSub($uniID);

        // 3. создаём клиента на новой панели с тем же сроком (expiryMs сохраняет остаток дней)
        $xray = new Xray();
        $added = $xray->addClient(1, $uniID, max(1, (int) ($sub[0]['count_devices'] ?? 1)), '', $expiryMs);

        if (!\is_array($added) || ($added['success'] ?? false) !== true) {
            // откат подписки на старый сервер — клиента нужно перевыдать вручную
            Database::send(
                'UPDATE qwees_subscriptions SET subscription = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?',
                [$oldUrl, $uniID]
            );
            self::forgetSub($uniID);
            return ['status' => 'error', 'message' => 'Не удалось создать клиента на новом сервере'];
        }

        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
            \sprintf("[%s] [ПОДПИСКА -> СМЕНА СЕРВЕРА] %s: %s -> %s\n", date('Y-m-d H:i:s'), $uniID, $oldCode, $newCode),
            FILE_APPEND
        );

        return ['status' => 'ok', 'message' => 'Сервер изменён: ' . self::servers()[$newCode]['country']];
    }

    /** uniID авторизованного пользователя сессии. */
    private static function sessionUniID(): string
    {
        $user = \App\Config\Session::init('user');
        return is_array($user) ? strval($user['uniID'] ?? '') : '';
    }
}