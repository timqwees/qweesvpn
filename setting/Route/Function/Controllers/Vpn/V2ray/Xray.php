<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Vpn\V2ray;

use Setting\Route\Function\Controllers\Client\GetUser;
use App\Config\Database;
use Setting\Route\Function\Functions;

class Xray
{
    /**
     * UUID v4 без внешних зависимостей (VLESS/VMess в 3x-ui требуют id в формате UUID).
     */
    private static function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        );
    }

    private static function isUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        );
    }

    /** Базовый URL панели 3x-ui из .env (например https://host:port), без суффикса /panel/api. */
    private static function panelBase(): string
    {
        return rtrim($_ENV['XUI_URL_PANEL'] ?? '', '/');
    }

    /** Имя cookie сессии после POST /login (если не используется XUI_API_TOKEN). */
    private static function cookieName(): string
    {
        return $_ENV['XUI_LOGIN_NAME_COOKIE'] ?? 'x-ui';
    }

    /** Логин администратора панели (POST /login без Bearer). */
    private static function xuiLogin(): string
    {
        return $_ENV['XUI_LOGIN'] ?? '';
    }

    /** Пароль администратора панели. */
    private static function xuiPassword(): string
    {
        return $_ENV['XUI_PASSWORD'] ?? '';
    }

    /** VLESS сервер хост (для client_data). */
    private static function vlessHost(): string
    {
        return $_ENV['VLESS_SERVER'] ?? '';
    }

    /** Имя файла для логирования. */
    private static function logFile(): string
    {
        return $_ENV['LOG_FILE_NAME'] ?? 'qwees.log';
    }

    /** API Token панели (Settings → Security). Пусто — POST /login и cookie + CSRF. */
    private static function xuiApiToken(): string
    {
        return trim((string) ($_ENV['XUI_API_TOKEN'] ?? ''), " \t\n\r\0\x0B\"'");
    }

    /** Одна сессия cookie (+ CSRF) на HTTP-запрос PHP при работе без Bearer. */
    private static ?string $threeXuiCookieCache = null;

    private static ?string $threeXuiCsrfCache = null;

    private static bool $threeXuiAuthResolved = false;

    /** Базовые SSL/UA опции (int 0/1 — совместимость с curl_setopt_array в PHP 8+). */
    private static function curlSslUserAgentOpts(): array
    {
        return [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; ' . Functions::site()['ООО'] . '/1.0)',
        ];
    }

    /**
     * @param array<int, mixed> $opts
     * @return array<int, mixed>
     */
    private static function curlOptsMerge(array $opts): array
    {
        // array_merge reindexes numeric keys (CURLOPT_* are ints) → invalid keys for curl_setopt_array (PHP 8+).
        return array_replace(self::curlSslUserAgentOpts(), $opts);
    }

    /** Авторизация 3x-ui по паролю (если нет Bearer-токена). */
    private static function threeXuiLoginCookie(): ?string
    {
        $maxRetries = 3;
        $code = 0;
        $cookie = '';
        $cookieName = self::cookieName();
        $cookiePattern = '/Set-Cookie:\s*(3?' . $cookieName . '=[^;]+)/i';

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $loginBody = [
                'username' => self::xuiLogin(),
                'password' => self::xuiPassword(),
            ];
            $otp = trim((string) ($_ENV['XUI_TWO_FACTOR_CODE'] ?? ''), " \t\n\r\0\x0B\"'");
            if ($otp !== '') {
                $loginBody['twoFactorCode'] = $otp;
            }

            $ch = curl_init(self::panelBase() . '/login');
            curl_setopt_array($ch, self::curlOptsMerge([
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($loginBody, JSON_UNESCAPED_UNICODE),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
                CURLOPT_TIMEOUT => 45,
                CURLOPT_CONNECTTIMEOUT => 20,
            ]));
            $response = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($code === 200 && is_string($response) && preg_match($cookiePattern, $response, $m)) {
                $headerEnd = strpos($response, "\r\n\r\n");
                $bodyOff = $headerEnd !== false ? $headerEnd + 4 : false;
                if ($bodyOff === false) {
                    $headerEnd = strpos($response, "\n\n");
                    $bodyOff = $headerEnd !== false ? $headerEnd + 2 : false;
                }
                if ($bodyOff !== false && $bodyOff < strlen($response)) {
                    $loginJson = json_decode(substr($response, $bodyOff), true);
                    if (is_array($loginJson) && array_key_exists('success', $loginJson) && $loginJson['success'] !== true) {
                        file_put_contents(
                            self::logFile(),
                            sprintf(
                                "[%s] [3X-UI] Login отклонён панелью: %s\n",
                                date('Y-m-d H:i:s'),
                                json_encode($loginJson, JSON_UNESCAPED_UNICODE)
                            ),
                            FILE_APPEND
                        );
                        if ($attempt < $maxRetries) {
                            usleep(500000 * $attempt);
                        }
                        continue;
                    }
                }
                $cookie = $m[1];
                break;
            }

            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [3X-UI] Login attempt %d/%d failed: HTTP %d, cURL: %s\n",
                    date('Y-m-d H:i:s'),
                    $attempt,
                    $maxRetries,
                    $code,
                    $curlError
                ),
                FILE_APPEND
            );
            if ($attempt < $maxRetries) {
                usleep(500000 * $attempt);
            }
        }

        return $cookie !== '' ? $cookie : null;
    }

    /** GET /csrf-token — для cookie-сессии на POST нужен заголовок X-CSRF-Token (см. API Docs). */
    private static function threeXuiFetchCsrfToken(string $cookieHeaderValue): ?string
    {
        foreach (['/csrf-token', '/panel/api/csrf-token'] as $path) {
            $ch = curl_init(self::panelBase() . $path);
            curl_setopt_array($ch, self::curlOptsMerge([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Accept: application/json', 'Cookie: ' . $cookieHeaderValue],
                CURLOPT_TIMEOUT => 20,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]));
            $response = curl_exec($ch);
            curl_close($ch);
            if (!is_string($response) || $response === '') {
                continue;
            }
            $decoded = json_decode($response, true);
            if (is_array($decoded) && ($decoded['success'] ?? false) === true && isset($decoded['obj'])) {
                $t = $decoded['obj'];
                if (is_string($t) && $t !== '') {
                    return $t;
                }
            }
        }

        return null;
    }

    /**
     * @return array{0: string, 1: ?string}|false [Cookie: value, csrf или null]
     */
    private static function threeXuiCookieAuthSession(): array|false
    {
        if (self::$threeXuiAuthResolved) {
            return self::$threeXuiCookieCache !== null
                ? [self::$threeXuiCookieCache, self::$threeXuiCsrfCache]
                : false;
        }
        self::$threeXuiAuthResolved = true;
        $cookie = self::threeXuiLoginCookie();
        if ($cookie === null) {
            return false;
        }
        self::$threeXuiCookieCache = $cookie;
        self::$threeXuiCsrfCache = self::threeXuiFetchCsrfToken($cookie);
        if (self::$threeXuiCsrfCache === null) {
            file_put_contents(
                self::logFile(),
                sprintf("[%s] [3X-UI] CSRF токен не получен; POST может быть отклонён панелью\n", date('Y-m-d H:i:s')),
                FILE_APPEND
            );
        }

        return [self::$threeXuiCookieCache, self::$threeXuiCsrfCache];
    }

    /**
     * HTTP к 3x-ui: path от корня сайта, например /panel/api/inbounds/list.
     *
     * @return array<string,mixed>|false Декодированный JSON; при ошибке сети/ответа — false
     */
    private static function threeXuiHttp(string $method, string $path, ?array $jsonBody = null): array|false
    {
        $url = self::panelBase() . $path;
        $token = self::xuiApiToken();
        $headers = ['Accept: application/json'];
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        } else {
            $sess = self::threeXuiCookieAuthSession();
            if ($sess === false) {
                return false;
            }
            [$cookieVal, $csrf] = $sess;
            $headers[] = 'Cookie: ' . $cookieVal;
            $methodU = strtoupper($method);
            if (
                $csrf !== null && $csrf !== ''
                && in_array($methodU, ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            ) {
                $headers[] = 'X-CSRF-Token: ' . $csrf;
            }
        }
        if ($jsonBody !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        $ch = curl_init($url);
        $opts = self::curlOptsMerge([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 15,
        ]);
        if (strtoupper($method) === 'POST') {
            $opts[CURLOPT_POST] = true;
            if ($jsonBody !== null) {
                $opts[CURLOPT_POSTFIELDS] = json_encode($jsonBody, JSON_UNESCAPED_UNICODE);
            }
        } elseif (strtoupper($method) !== 'GET') {
            $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
            if ($jsonBody !== null) {
                $opts[CURLOPT_POSTFIELDS] = json_encode($jsonBody, JSON_UNESCAPED_UNICODE);
            }
        }
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlErr !== '') {
            file_put_contents(
                self::logFile(),
                sprintf("[%s] [3X-UI] HTTP %s %s failed: %s\n", date('Y-m-d H:i:s'), $method, $path, $curlErr),
                FILE_APPEND
            );
            return false;
        }
        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            file_put_contents(
                self::logFile(),
                sprintf("[%s] [3X-UI] Invalid JSON from %s HTTP %d\n", date('Y-m-d H:i:s'), $path, $httpCode),
                FILE_APPEND
            );
            return false;
        }

        if ($httpCode >= 400) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [3X-UI] %s %s HTTP %d body: %s\n",
                    date('Y-m-d H:i:s'),
                    $method,
                    $path,
                    $httpCode,
                    substr((string) $response, 0, 500)
                ),
                FILE_APPEND
            );
        }

        return $decoded;
    }

    /** В ответе API streamSettings иногда строка JSON. */
    private static function streamSettingsArray(array $inbound): array
    {
        $ss = $inbound['streamSettings'] ?? null;
        if (is_string($ss)) {
            $d = json_decode($ss, true);
            return is_array($d) ? $d : [];
        }
        return is_array($ss) ? $ss : [];
    }

    /**
     * Продление date_end в БД по uniID (источник — строка подписки, не сессия GetUser).
     * Логика согласована с панелью: max(сейчас, текущий date_end) + bonusDays календарных дней.
     */
    private static function syncUserDateEndByUniID(string $uniID, int $bonusDays): void
    {
        if ($bonusDays <= 0 || $uniID === '') {
            return;
        }
        $subData = Database::send('SELECT date_end FROM qwees_subscriptions WHERE uniID = ?', [$uniID]);
        if (empty($subData[0])) {
            return;
        }
        $dateEnd = (string) ($subData[0]['date_end'] ?? '');
        $base = !empty($dateEnd) ? max(strtotime($dateEnd . ' 23:59:59'), time()) : time();
        $newEnd = $base + $bonusDays * 86400;
        Database::send(
            'UPDATE qwees_subscriptions SET date_end = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?',
            [date('Y-m-d', $newEnd), $uniID]
        );
    }

    /**
     * Выдача/обновление клиента через API 3x-ui (/panel/api/clients/*).
     *
     * Изменения для 3x-ui 3.1.0:
     * - Добавление:  POST /panel/api/clients/add        { client: {...}, inboundIds: [id] }
     * - Обновление:  POST /panel/api/clients/update/:email  (полный объект клиента, replace)
     * - Поиск существующего: по subId === uniID (надёжнее имени)
     * - email в URL при update/delete — реальный email из записи панели (= getFirstName())
     */
    private function addClientPanelApi(int $days, string $uniID, $device_limit = null): array|false
    {
        $user = new GetUser($uniID);

        $data = self::threeXuiHttp('GET', '/panel/api/inbounds/list');
        if ($data === false || empty($data['success']) || empty($data['obj']) || !is_array($data['obj'])) {
            file_put_contents(
                self::logFile(),
                sprintf("[%s] [3X-UI addClient] Список inbounds недоступен\n", date('Y-m-d H:i:s')),
                FILE_APPEND
            );
            return false;
        }

        $inboundIdx = (int) ($_ENV['XUI_INBOUND_NUMBER'] ?? 0);
        if (empty($data['obj'][$inboundIdx])) {
            file_put_contents(
                self::logFile(),
                sprintf("[%s] [3X-UI addClient] Нет inbound с индексом %d\n", date('Y-m-d H:i:s'), $inboundIdx),
                FILE_APPEND
            );
            return false;
        }

        $inbound = $data['obj'][$inboundIdx];
        if (empty($inbound['id']) || ($inbound['enable'] ?? true) === false) {
            return false;
        }

        $protocol = strtolower((string) ($inbound['protocol'] ?? ''));
        $rawSettings = $inbound['settings'] ?? '{}';
        $settings = is_array($rawSettings) ? $rawSettings : json_decode((string) $rawSettings, true);
        if (!is_array($settings)) {
            $settings = [];
        }
        if (!isset($settings['clients']) || !is_array($settings['clients'])) {
            $settings['clients'] = [];
        }

        $expiry = (time() + ((int) $days * 86400)) * 1000;
        if ($device_limit === null) {
            $device_limit = isset($_ENV['XUI_DEVICE_LIMIT']) ? (int) $_ENV['XUI_DEVICE_LIMIT'] : 1;
        }

        $needsUuid = in_array($protocol, ['vless', 'vmess'], true);

        // Ищем клиента по subId (= uniID) — надёжнее имени, т.к. имя может совпадать у разных юзеров
        $existingIndex = null;
        foreach ($settings['clients'] as $idx => $c) {
            if (!is_array($c)) {
                continue;
            }
            if (($c['subId'] ?? '') === $uniID || ($c['email'] ?? '') === $uniID) {
                $existingIndex = $idx;
                break;
            }
        }

        if ($existingIndex !== null) {
            // --- ОБНОВЛЕНИЕ существующего клиента ---
            // 3.1.0: POST /panel/api/clients/update/:email
            // :email = реальный email клиента из панели (= getFirstName(), установленный при создании)
            // Тело — полный объект (replace, не patch), берём существующий и перезаписываем нужные поля
            $existingClient = $settings['clients'][$existingIndex];
            $currentEmail = (string) ($existingClient['email'] ?? $uniID);

            $client = array_merge($existingClient, [
                'expiryTime' => $expiry,
                'enable' => true,
                'limitIp' => $device_limit,
                'subId' => $uniID,
                'totalGB' => $existingClient['totalGB'] ?? 0,
            ]);

            $path = '/panel/api/clients/update/' . rawurlencode($currentEmail);
            $payload = $client;
        } else {
            // --- СОЗДАНИЕ нового клиента ---
            // 3.1.0: POST /panel/api/clients/add  { client: {...}, inboundIds: [id] }
            // email = getFirstName() — отображаемое имя в панели (может быть не уникальным!)
            // subId = uniID        — уникальный ключ для поиска/продления/удаления
            $clientId = $needsUuid ? self::generateUuidV4() : $uniID;
            $client = [
                'id' => $clientId,
                'email' => $user->getFirstName(), // отображаемое имя в панели
                'expiryTime' => $expiry,
                'subId' => $uniID,               // уникальный ключ — всегда uniID
                'enable' => true,
                'totalGB' => 0,
                'limitIp' => $device_limit,
                'flow' => '',
                'tgId' => 0,
            ];

            $path = '/panel/api/clients/add';
            $payload = ['client' => $client, 'inboundIds' => [(int) $inbound['id']]];
        }

        $updateUser = self::threeXuiHttp('POST', $path, $payload);
        if ($updateUser === false || ($updateUser['success'] ?? false) !== true) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [3X-UI addClient] Ошибка API (%s): %s\n",
                    date('Y-m-d H:i:s'),
                    $path,
                    json_encode($updateUser, JSON_UNESCAPED_UNICODE)
                ),
                FILE_APPEND
            );
            return false;
        }

        $ss = self::streamSettingsArray($inbound);
        return [
            'success' => true,
            'client_data' => [
                'id' => $client['id'],
                'email' => $client['email'],
                'subId' => $client['subId'],
                'expiryTime' => $client['expiryTime'],
                'limitIp' => $client['limitIp'],
                'enable' => $client['enable'],
                'totalGB' => $client['totalGB'] ?? 0,
                'inbound_id' => $inbound['id'],
                'protocol' => $inbound['protocol'],
                'host' => self::vlessHost(),
                'port' => $inbound['port'] ?? 443,
                'security' => $ss['security'] ?? 'tls',
                'network' => $ss['network'] ?? 'ws',
            ],
        ];
    }

    /**
     * Создаёт или обновляет клиента VPN через REST API панели 3x-ui (`/panel/api/clients/*`).
     * Bearer `XUI_API_TOKEN` или сессия POST `/login` + CSRF. Inbound — индекс `XUI_INBOUND_NUMBER` в списке list.
     *
     * @param int|string $days         Количество дней действия подписки
     * @param string     $uniID        Уникальный идентификатор пользователя
     * @param int|null   $device_limit Лимит устройств (опционально, по умолчанию из XUI_DEVICE_LIMIT)
     *
     * @return array|false              Возвращает массив с данными клиента при успехе:
     *                                 [
     *                                   'success' => true,
     *                                   'client_data' => [
     *                                     'id' => string,           // UUID клиента
     *                                     'email' => string,        // Имя клиента (getFirstName)
     *                                     'subId' => string,        // ID подписки (= uniID)
     *                                     'expiryTime' => int,      // Время истечения в мс
     *                                     'limitIp' => int,         // Лимит IP адресов
     *                                     'enable' => bool,         // Статус активности
     *                                     'totalGB' => int,         // Лимит трафика (0 = безлимит)
     *                                     'inbound_id' => int,      // ID inbound'а
     *                                     'protocol' => string,     // Протокол (vless, vmess)
     *                                     'host' => string,         // Хост сервера
     *                                     'port' => int,            // Порт подключения
     *                                     'security' => string,     // Тип безопасности (tls)
     *                                     'network' => string       // Тип сети (ws)
     *                                   ]
     *                                 ]
     *                                 При ошибке возвращает false и записывает лог
     */
    public function addClient($days, $uniID, $device_limit = null): array|false
    {
        return $this->addClientPanelApi($days, (string) $uniID, $device_limit);
    }

    /**
     * Продление клиента (3x-ui): updateClient или создание через addClientPanelApi.
     *
     * @return array{status: string, message: string}
     */
    private function xuiUpdate3xUi(string $uniID, int $bonusDays): array
    {
        $data = self::threeXuiHttp('GET', '/panel/api/inbounds/list');
        if ($data === false || empty($data['success']) || empty($data['obj'])) {
            return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
        }
        $inboundIdx = (int) ($_ENV['XUI_INBOUND_NUMBER'] ?? 0);
        $inbound = $data['obj'][$inboundIdx] ?? null;
        if (!$inbound || empty($inbound['id'])) {
            return ['status' => 'error', 'message' => 'Inbound не найден'];
        }

        $rawSettings = $inbound['settings'] ?? '{}';
        $settings = is_array($rawSettings) ? $rawSettings : json_decode((string) $rawSettings, true);
        if (!is_array($settings)) {
            $settings = [];
        }
        if (!isset($settings['clients']) || !is_array($settings['clients'])) {
            $settings['clients'] = [];
        }

        $found = false;
        $clientRow = null;
        foreach ($settings['clients'] as $idx => $c) {
            if (!is_array($c)) {
                continue;
            }
            // Ищем по subId (= uniID) — уникальный ключ; email (= имя) может совпадать у разных юзеров
            if (($c['subId'] ?? '') === $uniID || ($c['email'] ?? '') === $uniID) {
                $currentExpiry = (int) ($c['expiryTime'] ?? 0);
                $nowMs = time() * 1000;
                $baseMs = max($nowMs, $currentExpiry);
                $settings['clients'][$idx]['expiryTime'] = $baseMs + $bonusDays * 86400000;
                $settings['clients'][$idx]['enable'] = true;
                $found = true;
                $clientRow = $settings['clients'][$idx];
                break;
            }
        }

        if (!$found) {
            $lim = isset($_ENV['XUI_DEVICE_LIMIT']) ? (int) $_ENV['XUI_DEVICE_LIMIT'] : 1;
            $add = $this->addClientPanelApi($bonusDays, $uniID, $lim);
            if (is_array($add) && ($add['success'] ?? false) === true) {
                self::syncUserDateEndByUniID($uniID, $bonusDays);
                return ['status' => 'ok', 'message' => 'Клиент создан, бонусные дни начислены'];
            }
            return ['status' => 'error', 'message' => 'Клиент не найден в панели и не удалось создать'];
        }

        // :email в URL = реальный email клиента из панели (= getFirstName(), установленный при создании)
        $currentEmail = (string) ($clientRow['email'] ?? '');
        if ($currentEmail === '') {
            return ['status' => 'error', 'message' => 'Некорректный email клиента'];
        }

        // 3.1.0: POST /panel/api/clients/update/:email — тело полный объект (replace, не patch)
        $path = '/panel/api/clients/update/' . rawurlencode($currentEmail);
        $payload = array_merge($clientRow, ['enable' => true]);
        $updateUser = self::threeXuiHttp('POST', $path, $payload);
        if ($updateUser !== false && ($updateUser['success'] ?? false) === true) {
            self::syncUserDateEndByUniID($uniID, $bonusDays);
            return ['status' => 'ok', 'message' => 'Бонусные дни добавлены'];
        }
        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [3X-UI xui_update] Update failed (%s): %s\n",
                date('Y-m-d H:i:s'),
                $path,
                json_encode($updateUser, JSON_UNESCAPED_UNICODE)
            ),
            FILE_APPEND
        );
        return ['status' => 'error', 'message' => 'Не удалось обновить клиента в панели 3x-ui'];
    }

    /**
     * Продлевает клиента через API панели 3x-ui (`updateClient` / `addClient`). Обновляет date_end в БД.
     *
     * @return array{status: string, message: string}
     */
    public function xui_update(string $uniID, int $bonusDays): array
    {
        if ($bonusDays <= 0) {
            return ['status' => 'error', 'message' => 'Некорректное число дней'];
        }
        $uniID = trim($uniID);
        if ($uniID === '') {
            return ['status' => 'error', 'message' => 'Не указан uniID'];
        }

        return $this->xuiUpdate3xUi($uniID, $bonusDays);
    }

    /**
     * Удаление ключа через API 3x-ui.
     *
     * 3.1.0: POST /panel/api/clients/del/:email
     * :email = реальный email клиента из панели (= getFirstName(), установленный при создании).
     * Ищем клиента по subId (= uniID), берём его email и подставляем в URL.
     *
     * @return array<string, string>
     */
    private function deleteKey3xUi(string $uniID): array
    {
        $data = self::threeXuiHttp('GET', '/panel/api/inbounds/list');
        if ($data === false || empty($data['success']) || empty($data['obj'])) {
            return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
        }
        $inboundIdx = (int) ($_ENV['XUI_INBOUND_NUMBER'] ?? 0);
        if (empty($data['obj'][$inboundIdx])) {
            return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
        }
        $inbound = $data['obj'][$inboundIdx];
        $rawSettings = $inbound['settings'] ?? '{}';
        $settings = is_array($rawSettings) ? $rawSettings : json_decode((string) $rawSettings, true);
        if (!is_array($settings)) {
            $settings = [];
        }

        // Ищем по subId (= uniID) — уникальный ключ
        // email может быть именем (getFirstName), subId всегда uniID
        $found = false;
        $deleteEmail = $uniID; // запасной вариант
        foreach ($settings['clients'] ?? [] as $c) {
            if (!is_array($c)) {
                continue;
            }
            if (($c['subId'] ?? '') === $uniID || ($c['email'] ?? '') === $uniID) {
                $found = true;
                $e = (string) ($c['email'] ?? '');
                // Берём реальный email из панели для URL — именно он ключ в /clients/del/:email
                $deleteEmail = $e !== '' ? $e : $uniID;
                break;
            }
        }

        if (!$found) {
            return ['status' => 'partial', 'message' => 'Клиент не найден в панели, данные подписки очищены'];
        }

        // 3.1.0: POST /panel/api/clients/del/:email (убран inboundId из пути)
        $delPath = '/panel/api/clients/del/' . rawurlencode($deleteEmail);
        $delResult = self::threeXuiHttp('POST', $delPath, null);

        if ($delResult !== false && ($delResult['success'] ?? false) === true) {
            // Верификация: клиент действительно удалён из inbound
            $verify = self::threeXuiHttp('GET', '/panel/api/inbounds/list');
            if ($verify !== false && !empty($verify['success']) && !empty($verify['obj'][$inboundIdx])) {
                $vIn = $verify['obj'][$inboundIdx];
                $rawSettings = $vIn['settings'] ?? '{}';
                $vSettings = is_array($rawSettings) ? $rawSettings : json_decode((string) $rawSettings, true);
                if (is_array($vSettings)) {
                    foreach ($vSettings['clients'] ?? [] as $cl) {
                        if (is_array($cl) && (($cl['subId'] ?? '') === $uniID || ($cl['email'] ?? '') === $deleteEmail)) {
                            return ['status' => 'error', 'message' => 'Панель сообщила об успехе, но клиент всё ещё в inbound'];
                        }
                    }
                }
            }
            Database::send('DELETE FROM qwees_subscriptions WHERE uniID = ?', [strval($uniID)]);
            return ['status' => 'ok', 'message' => 'Подписка успешно удалёна'];
        }
        return [
            'status' => 'partial',
            'message' => 'Успешно удалено из хранилища, но ошибка при удалении из сервера: '
                . json_encode($delResult, JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * Удаляет ключ пользователя в панели 3x-ui (`delClientByEmail`) и очищает подписку в БД.
     *
     * @return array            - Массив ["status" => "ok"|"partial"|"error", "message" => ...]
     */
    public function DeleteKey($uniID = null)
    {
        if (is_object($uniID)) {
            $uniID = null;
        }
        $client = new GetUser();
        $uniID = $uniID === null ? $client->getUniID() : $uniID;

        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Начало удаления ключа для пользователя uniID: %s\n",
                date('Y-m-d H:i:s'),
                $uniID
            ),
            FILE_APPEND
        );

        if (empty($uniID)) {
            return ['status' => 'error', 'message' => 'Пользователь не найден'];
        }

        $result = $this->deleteKey3xUi((string) $uniID);

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if (str_contains($uri, 'subscription/delete')) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        }

        return $result;
    }

    /**
     * Очистка истёкших клиентов на панели 3x-ui.
     *
     * 3.1.0: POST /panel/api/clients/delDepleted — глобальный эндпоинт без inboundId.
     * Старый /inbounds/delDepletedClients/:id удалён из API.
     * Ответ содержит obj.deleted (количество удалённых клиентов).
     */
    private static function cleanUp3xUi(): void
    {
        // 3.1.0: глобальный вызов — GET inbounds/list перед этим не нужен
        $result = self::threeXuiHttp('POST', '/panel/api/clients/delDepleted', null);

        if ($result !== false && ($result['success'] ?? false) === true) {
            $deleted = (int) ($result['obj']['deleted'] ?? 0);
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [УСПЕШНО - ГЛОБАЛЬНАЯ ОЧИСТКА] 3x-ui: delDepleted удалил %d клиентов\n",
                    date('Y-m-d H:i:s'),
                    $deleted
                ),
                FILE_APPEND
            );
        } else {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [ОШИБКА - ГЛОБАЛЬНАЯ ОЧИСТКА] 3x-ui delDepleted: %s\n",
                    date('Y-m-d H:i:s'),
                    json_encode($result, JSON_UNESCAPED_UNICODE)
                ),
                FILE_APPEND
            );
            return;
        }

        Database::send(
            'DELETE FROM qwees_subscriptions WHERE status != ? AND date_end < ?',
            [strval('off'), date('Y-m-d')]
        );
        file_put_contents(
            self::logFile(),
            sprintf("[%s] [УСПЕШНО - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: обновлена БД\n", date('Y-m-d H:i:s')),
            FILE_APPEND
        );
    }

    /**
     * Очистка истёкших клиентов: `POST /panel/api/clients/delDepleted` и синхронизация БД.
     *
     * @return void
     */
    public static function CleanUP()
    {
        self::cleanUp3xUi();
    }
}
