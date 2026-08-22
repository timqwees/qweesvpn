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

    /** Реестр серверов: новый сервер = новая запись массива. */
    private const SERVERS = [
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

    /**
     * Выбрать сервер: по коду ('nl') или по подписке пользователя.
     * Без аргументов — сервер текущего пользователя сессии (или дефолтный).
     */
    public static function selectServer(?string $uniID = null, ?string $code = null): array
    {
        if ($code !== null && isset(self::SERVERS[$code])) {
            self::$current = $code;
            return self::SERVERS[$code];
        }

        $uniID = $uniID ?? self::sessionUniID();
        $sub = $uniID !== '' ? Database::send('SELECT subscription FROM qwees_subscriptions WHERE uniID = ?', [$uniID]) : [];

        self::$current = self::getServerCodeFromUrl((string) ($sub[0]['subscription'] ?? '')) ?: self::DEFAULT_SERVER;
        return self::SERVERS[self::$current];
    }

    /** Данные текущего сервера (после selectServer()). */
    public static function getServer(): array
    {
        return self::SERVERS[self::$current];
    }

    /** Код текущего сервера ('nl', 'fi', ...). */
    public static function getServerCode(): string
    {
        return self::$current;
    }

    /** Коды всех серверов реестра (для перебора панелей). */
    public static function getServerCodes(): array
    {
        return array_keys(self::SERVERS);
    }

    /**
     * Список серверов для будущего UI выбора локации пользователем.
     */
    public static function getAvailableServers(): array
    {
        $list = [];
        foreach (self::SERVERS as $code => $server) {
            $list[] = ['code' => $code, 'country' => $server['country'], 'flag' => $server['flag'], 'host' => $server['VLESS_SERVER']];
        }
        return $list;
    }

    /** Код сервера из URL/хоста подписки: https://nl.qweesvpn.online:1005/... => nl */
    public static function getServerCodeFromUrl(string $url): string
    {
        $host = strtolower((string) (parse_url(trim($url), PHP_URL_HOST) ?: ''));
        $first = explode('.', $host)[0] ?? '';
        return isset(self::SERVERS[$first]) ? $first : '';
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

        if (!isset(self::SERVERS[$newCode])) {
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

        $oldUrl = rtrim(self::SERVERS[$oldCode]['XUI_URL_SUBSCRIPTION'], '/') . '/' . $uniID;
        $newUrl = rtrim(self::SERVERS[$newCode]['XUI_URL_SUBSCRIPTION'], '/') . '/' . $uniID;

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

        // 3. создаём клиента на новой панели с тем же сроком (expiryMs сохраняет остаток дней)
        $xray = new Xray();
        $added = $xray->addClient(1, $uniID, max(1, (int) ($sub[0]['count_devices'] ?? 1)), '', $expiryMs);

        if (!is_array($added) || ($added['success'] ?? false) !== true) {
            // откат подписки на старый сервер — клиента нужно перевыдать вручную
            Database::send(
                'UPDATE qwees_subscriptions SET subscription = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?',
                [$oldUrl, $uniID]
            );
            return ['status' => 'error', 'message' => 'Не удалось создать клиента на новом сервере'];
        }

        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
            sprintf("[%s] [СМЕНА СЕРВЕРА] %s: %s -> %s\n", date('Y-m-d H:i:s'), $uniID, $oldCode, $newCode),
            FILE_APPEND
        );

        return ['status' => 'ok', 'message' => 'Сервер изменён: ' . self::SERVERS[$newCode]['country']];
    }

    /** uniID авторизованного пользователя сессии. */
    private static function sessionUniID(): string
    {
        $user = \App\Config\Session::init('user');
        return is_array($user) ? strval($user['uniID'] ?? '') : '';
    }
}