<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\vpn\v2ray;
use Setting\Route\Function\Controllers\Client\src\Client;
use Setting\Route\Function\Controllers\Client\getUser;
use App\Config\Session;
use App\Config\Database;
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

    /** База панели X-UI из .env (без хардкода домена в десяти местах). */
    private static function panelBase(): string
    {
        return rtrim($_ENV['XUI_URL_PANEL'] ?? '', '/');
    }

    /** Путь для авторизации в X-UI. */
    private static function pathLogin(): string
    {
        return $_ENV['XUI_PATH_LOGIN'] ?? '/to/login';
    }

    /** Путь для получения списка inbounds. */
    private static function pathList(): string
    {
        return $_ENV['XUI_PATH_LIST'] ?? '/to/xui/API/inbounds/';
    }

    /** Путь для обновления inbound (добавления/изменения клиента). */
    private static function pathUpdate(): string
    {
        return $_ENV['XUI_PATH_UPDATE'] ?? '/to/xui/API/inbounds/update/';
    }

    /** Имя cookie для сессии X-UI. */
    private static function cookieName(): string
    {
        return $_ENV['XUI_LOGIN_NAME_COOKIE'] ?? 'x-ui';
    }

    /** Логин для авторизации в X-UI. */
    private static function xuiLogin(): string
    {
        return $_ENV['XUI_LOGIN'] ?? '';
    }

    /** Пароль для авторизации в X-UI. */
    private static function xuiPassword(): string
    {
        return $_ENV['XUI_PASSWORD'] ?? '';
    }

    /** VLESS сервер хост. */
    private static function vlessHost(): string
    {
        return $_ENV['VLESS_SERVER'] ?? '';
    }

    /** Имя файла для логирования. */
    private static function logFile(): string
    {
        return $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log';
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


    /** Продление date_end в БД при реферальном бонусе. */
    private static function syncUserDateEnd(string $uniID, int $bonusDays, getUser $ref): void
    {
        $dateEnd = $ref->getDateEnd();
        $base = !empty($dateEnd) ? max(strtotime($dateEnd . ' 23:59:59'), time()) : time();
        $newEnd = $base + $bonusDays * 86400;
        Database::send(
            'UPDATE qwees_users SET date_end = ? WHERE uniID = ?',
            [date('Y-m-d', $newEnd), $uniID]
        );
    }

    /**
     * Создаёт нового клиента VPN на сервере XUI через API, возвращает его параметры для дальнейшей регистрации в системе.
     *
     * Производит авторизацию в панели XUI, получает список инбаундов (inbounds),
     * формирует уникальные параметры (uuid/subId/email/expiry time), добавляет клиента в первый инбаунд,
     * возвращает параметры подключения для последующего сохранения в базе данных.
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
     *                                     'email' => string,        // Email клиента
     *                                     'subId' => string,        // ID подписки
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
        // Логин и получение куки
        $ch = curl_init(self::panelBase() . self::pathLogin());
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'username' => self::xuiLogin(),
                'password' => self::xuiPassword()
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        $cookieName = self::cookieName();
        // Handle both 'x-ui' and '3x-ui' cookie names
        $cookiePattern = '/Set-Cookie:\s*(3?' . $cookieName . '=[^;]+)/i';
        if ($code !== 200 || !preg_match($cookiePattern, $response, $m)) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [ВЫДАЧА ПОДПИСКИ] Auth error on XUI panel: HTTP %d\n",
                    date('Y-m-d H:i:s'),
                    $code
                ),
                FILE_APPEND
            );
            return false;
        }
        $cookie = $m[1];

        // Список inbounds
        $ch = curl_init(self::panelBase() . self::pathList());
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($response === false || !empty($curlError)) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] Ошибка cURL при получении списка inbounds: %s\n",
                    date('Y-m-d H:i:s'),
                    $curlError
                ),
                FILE_APPEND
            );
            return false;
        }

        $data = json_decode($response, true);
        $inboundIdx = (int) ($_ENV['XUI_INBOUND_NUMBER'] ?? 0);
        if ($code !== 200 || empty($data['success']) || empty($data['obj'][$inboundIdx])) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] Ошибка получения списка inbounds: HTTP %d, индекс %d\n",
                    date('Y-m-d H:i:s'),
                    $code,
                    $inboundIdx
                ),
                FILE_APPEND
            );
            return false;
        }

        // Подготовка inbound
        $inbound = $data['obj'][$inboundIdx];

        // Логируем информацию о inbound для диагностики
        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DEBUG] Inbound info: %s\n",
                date('Y-m-d H:i:s'),
                json_encode($inbound, JSON_UNESCAPED_UNICODE)
            ),
            FILE_APPEND
        );

        // Проверяем наличие ID у inbound
        if (empty($inbound['id'])) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] У inbound отсутствует поле 'id'\n",
                    date('Y-m-d H:i:s')
                ),
                FILE_APPEND
            );
            return false;
        }

        $protocol = strtolower((string) $inbound['protocol']);
        $settings = json_decode($inbound['settings'], true);

        if (!isset($settings['clients']) || !is_array($settings['clients'])) {
            $settings['clients'] = [];
        }

        $expiry = (time() + ($days * 86400)) * 1000; // ms
        if ($device_limit == null) {
            $device_limit = isset($_ENV['XUI_DEVICE_LIMIT']) ? intval($_ENV['XUI_DEVICE_LIMIT']) : 1;
        }

        // VLESS/VMess: поле id должно быть UUID. uniID из БД (uniqid) — не UUID, панель отклоняет inbound update.
        $needsUuid = in_array($protocol, ['vless', 'vmess'], true);

        $client = null;
        $existingIndex = null;
        foreach ($settings['clients'] as $idx => $c) {
            if (!is_array($c)) {
                continue;
            }
            if (($c['email'] ?? '') === $uniID || ($c['subId'] ?? '') === $uniID) {
                $existingIndex = $idx;
                break;
            }
        }

        if ($existingIndex !== null) {
            $settings['clients'][$existingIndex]['expiryTime'] = $expiry;
            $settings['clients'][$existingIndex]['limitIp'] = $device_limit;
            $settings['clients'][$existingIndex]['enable'] = true;
            $settings['clients'][$existingIndex]['subId'] = $uniID;
            $settings['clients'][$existingIndex]['email'] = $uniID;
            if ($needsUuid && !self::isUuid((string) ($settings['clients'][$existingIndex]['id'] ?? ''))) {
                $settings['clients'][$existingIndex]['id'] = self::generateUuidV4();
            }
            $client = $settings['clients'][$existingIndex];
        } else {
            $clientId = $needsUuid ? self::generateUuidV4() : $uniID;
            $client = [
                'id' => $clientId,
                'email' => $uniID,
                'expiryTime' => $expiry,
                'subId' => $uniID,
                'enable' => true,
                'totalGB' => 0,
                'limitIp' => $device_limit,
                'comment' => '',
                'created_at' => time() * 1000,
                'updated_at' => time() * 1000
            ];
            $settings['clients'][] = $client;
        }

        $inbound['settings'] = json_encode($settings);

        $updateUrl = self::panelBase() . self::pathUpdate() . $inbound['id'];

        // Логируем URL для диагностики
        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DEBUG] URL для обновления inbound: %s\n",
                date('Y-m-d H:i:s'),
                $updateUrl
            ),
            FILE_APPEND
        );

        $ch = curl_init($updateUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($inbound),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Cookie: $cookie",
                'Content-Type: application/json',
                'Expect:' // Отключаем 100-continue
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)',
            // Фикс для SSL_ERROR_SYSCALL
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_NOSIGNAL => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TCP_NODELAY => 1,
            CURLOPT_BUFFERSIZE => 128000,
            CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
            CURLOPT_ACCEPT_ENCODING => ''
        ]);

        // Retry mechanism for SSL errors and connection issues
        $maxRetries = 3;
        $response = false;
        $curlError = '';

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $response = curl_exec($ch);
            $curlError = curl_error($ch);

            if ($response !== false && empty($curlError)) {
                break; // Success, exit retry loop
            }

            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [DEBUG] Попытка %d/%d: Ошибка cURL: %s\n",
                    date('Y-m-d H:i:s'),
                    $attempt,
                    $maxRetries,
                    $curlError
                ),
                FILE_APPEND
            );

            if ($attempt < $maxRetries) {
                // Wait before retry (shorter delays like old code)
                usleep(100000 * $attempt); // 100ms, 200ms, 300ms
                curl_reset($ch);
                curl_setopt_array($ch, [
                    CURLOPT_URL => $updateUrl,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($inbound),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        "Cookie: $cookie",
                        'Content-Type: application/json',
                        'Expect:'
                    ],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_CONNECTTIMEOUT => 15,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)',
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_TCP_KEEPALIVE => 1,
                    CURLOPT_NOSIGNAL => 1,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 3,
                    CURLOPT_TCP_NODELAY => 1,
                    CURLOPT_BUFFERSIZE => 128000,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                    CURLOPT_ACCEPT_ENCODING => ''
                ]);
            }
        }

        $updateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || !empty($curlError)) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] Ошибка cURL при обновлении inbound: %s. Проверяем, создался ли клиент...\n",
                    date('Y-m-d H:i:s'),
                    $curlError
                ),
                FILE_APPEND
            );

            // Fallback: проверяем, создался ли клиент несмотря на ошибку cURL
            $ch = curl_init(self::panelBase() . self::pathList());
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
            ]);
            $checkResponse = curl_exec($ch);
            $checkCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($checkResponse !== false && $checkCode === 200) {
                $checkData = json_decode($checkResponse, true);
                $inboundIdx = (int) ($_ENV['XUI_INBOUND_NUMBER'] ?? 0);
                if (!empty($checkData['success']) && !empty($checkData['obj'][$inboundIdx])) {
                    $checkInbound = $checkData['obj'][$inboundIdx];
                    $checkSettings = json_decode($checkInbound['settings'], true);
                    $clientExists = false;
                    foreach ($checkSettings['clients'] ?? [] as $c) {
                        if (($c['email'] ?? '') === $uniID || ($c['subId'] ?? '') === $uniID) {
                            $clientExists = true;
                            $client = $c;
                            break;
                        }
                    }
                    if ($clientExists) {
                        file_put_contents(
                            self::logFile(),
                            sprintf(
                                "[%s] [ПОДПИСКА] Клиент %s найден в панели несмотря на SSL-ошибку! Считаем операцию успешной.\n",
                                date('Y-m-d H:i:s'),
                                $uniID
                            ),
                            FILE_APPEND
                        );
                        $ss = self::streamSettingsArray($checkInbound);
                        return [
                            'success' => true,
                            'client_data' => [
                                'id' => $client['id'],
                                'email' => $client['email'],
                                'subId' => $client['subId'],
                                'expiryTime' => $client['expiryTime'],
                                'limitIp' => $client['limitIp'],
                                'enable' => $client['enable'],
                                'totalGB' => $client['totalGB'],
                                'inbound_id' => $checkInbound['id'],
                                'protocol' => $checkInbound['protocol'],
                                'host' => self::vlessHost(),
                                'port' => $checkInbound['port'] ?? 443,
                                'security' => $ss['security'] ?? 'tls',
                                'network' => $ss['network'] ?? 'ws'
                            ]
                        ];
                    }
                }
            }
            return false;
        }

        $updateUser = json_decode($response, true);

        if ($updateCode === 200 && isset($updateUser['success']) && $updateUser['success']) {
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
                    'totalGB' => $client['totalGB'],
                    'inbound_id' => $inbound['id'],
                    'protocol' => $inbound['protocol'],
                    'host' => self::vlessHost(),
                    'port' => $inbound['port'] ?? 443,
                    'security' => $ss['security'] ?? 'tls',
                    'network' => $ss['network'] ?? 'ws'
                ]
            ];
        } else {
            // ЛОГИРУЕМ
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] add_client: Ошибка добавления пользователя! Подробности: %s\n",
                    date('Y-m-d H:i:s'),
                    json_encode($updateUser, JSON_UNESCAPED_UNICODE)
                ),
                FILE_APPEND
            );
            return false;
        }
    }

    /**
     * Продлевает клиента в X-UI на N дней (реферальный бонус) и обновляет date_end в БД.
     * Если клиента в панели ещё нет — создаёт через addClient.
     *
     * @return array{status: string, message: string}
     */
    public function xui_update(string $uniID, int $bonusDays): array
    {
        if ($bonusDays <= 0) {
            return ['status' => 'error', 'message' => 'Некорректное число дней'];
        }
        $ref = new getUser();
        if (empty($ref->getUniID())) {
            return ['status' => 'error', 'message' => 'Пользователь не найден'];
        }

        $base = self::panelBase();
        $ch = curl_init($base . self::pathLogin());
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'username' => self::xuiLogin(),
                'password' => self::xuiPassword()
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cookieName = self::cookieName();
        $cookiePattern = '/Set-Cookie:\s*(3?' . $cookieName . '=[^;]+)/i';
        if ($code !== 200 || !preg_match($cookiePattern, $response, $m)) {
            file_put_contents(
                self::logFile(),
                sprintf("[%s] [xui_update] Auth failed HTTP %d\n", date('Y-m-d H:i:s'), $code),
                FILE_APPEND
            );
            return ['status' => 'error', 'message' => 'Не удалось войти в X-UI'];
        }
        $cookie = $m[1];

        $ch = curl_init($base . self::pathList());
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        if ($response === false || $curlErr !== '') {
            return ['status' => 'error', 'message' => 'Ошибка сети при запросе X-UI'];
        }
        $data = json_decode($response, true);
        if ($code !== 200 || empty($data['success']) || empty($data['obj'])) {
            return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
        }
        $inboundIdx = (int) ($_ENV['XUI_INBOUND_NUMBER'] ?? 0);
        $inbound = $data['obj'][$inboundIdx] ?? null;
        if (!$inbound || empty($inbound['id'])) {
            return ['status' => 'error', 'message' => 'Inbound не найден'];
        }

        $settings = json_decode($inbound['settings'], true);
        if (!is_array($settings)) {
            $settings = [];
        }
        if (!isset($settings['clients']) || !is_array($settings['clients'])) {
            $settings['clients'] = [];
        }

        $found = false;
        foreach ($settings['clients'] as $idx => $c) {
            if (!is_array($c)) {
                continue;
            }
            if (($c['email'] ?? '') === $uniID || ($c['subId'] ?? '') === $uniID) {
                $currentExpiry = (int) ($c['expiryTime'] ?? 0);
                $nowMs = time() * 1000;
                $baseMs = max($nowMs, $currentExpiry);
                $settings['clients'][$idx]['expiryTime'] = $baseMs + $bonusDays * 86400000;
                $settings['clients'][$idx]['enable'] = true;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $lim = isset($_ENV['XUI_DEVICE_LIMIT']) ? (int) $_ENV['XUI_DEVICE_LIMIT'] : 1;
            $add = $this->addClient($bonusDays, $uniID, $lim);
            if ($add && !empty($add['success'])) {
                self::syncUserDateEnd($uniID, $bonusDays, $ref);
                return ['status' => 'ok', 'message' => 'Клиент создан, бонусные дни начислены'];
            }
            return ['status' => 'error', 'message' => 'Клиент не найден в панели и не удалось создать'];
        }

        $inbound['settings'] = json_encode($settings);
        $updateUrl = $base . '/to/xui/API/inbounds/update/' . $inbound['id'];
        $ch = curl_init($updateUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($inbound),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Cookie: ' . $cookie,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $response = curl_exec($ch);
        $updateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $updateUser = json_decode((string) $response, true);
        if ($updateCode === 200 && !empty($updateUser['success'])) {
            self::syncUserDateEnd($uniID, $bonusDays, $ref);
            return ['status' => 'ok', 'message' => 'Бонусные дни добавлены'];
        }
        file_put_contents(
            self::logFile(),
            sprintf("[%s] [xui_update] Update failed: %s\n", date('Y-m-d H:i:s'), json_encode($updateUser, JSON_UNESCAPED_UNICODE)),
            FILE_APPEND
        );
        return ['status' => 'error', 'message' => 'Не удалось обновить клиента в X-UI'];
    }

    /**
     * Удаляет ключ (VPN пользователя) из панели XUI и обнуляет связанные данные в базе данных.
     *
     * Ищет клиента в inbound по uniID (поля email/subId в панели = uniID), удаляет по id клиента (UUID).
     *
     * @return array            - Массив ["status" => "ok"|"partial"|"error", "message" => ...]
     */
    public function DeleteKey()
    {
        $client = new getUser();
        $uniID = $client->getUniID();

        // Логируем начало процесса удаления
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

        // 1. Авторизация
        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Попытка авторизации в X-UI\n",
                date('Y-m-d H:i:s')
            ),
            FILE_APPEND
        );

        $ch = curl_init(self::panelBase() . self::pathLogin());
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'username' => self::xuiLogin(),
                'password' => self::xuiPassword()
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Результат авторизации: HTTP %d, cURL Error: %s\n",
                date('Y-m-d H:i:s'),
                $code,
                $curlError
            ),
            FILE_APPEND
        );

        $cookieName = self::cookieName();
        // Handle both 'x-ui' and '3x-ui' cookie names
        $cookiePattern = '/Set-Cookie:\s*(3?' . $cookieName . '=[^;]+)/i';
        if ($code !== 200 || !preg_match($cookiePattern, $response, $m)) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [DELETE KEY] Auth error on XUI panel: HTTP %d\n",
                    date('Y-m-d H:i:s'),
                    $code
                ),
                FILE_APPEND
            );
            return ['status' => 'error', 'message' => 'Не удалось авторизоваться в X-UI'];
        }
        $cookie = $m[1];

        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Авторизация успешна, получена cookie\n",
                date('Y-m-d H:i:s')
            ),
            FILE_APPEND
        );

        // 2. Получаем inbounds
        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Получение списка inbounds\n",
                date('Y-m-d H:i:s')
            ),
            FILE_APPEND
        );

        $ch = curl_init(self::panelBase() . self::pathList());
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Результат получения inbounds: HTTP %d, cURL Error: %s\n",
                date('Y-m-d H:i:s'),
                $code,
                $curlError
            ),
            FILE_APPEND
        );

        if ($response === false || !empty($curlError)) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [DELETE KEY] Ошибка cURL при получении inbounds: %s\n",
                    date('Y-m-d H:i:s'),
                    $curlError
                ),
                FILE_APPEND
            );
            return ['status' => 'error', 'message' => 'Ошибка cURL при получении inbounds: ' . $curlError];
        }

        $data = json_decode($response, true);
        $inboundIdx = (int) ($_ENV['XUI_INBOUND_NUMBER'] ?? 0);
        if ($code !== 200 || empty($data['success']) || empty($data['obj'][$inboundIdx])) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [DELETE KEY] Ошибка получения inbounds: HTTP %d, Response: %s\n",
                    date('Y-m-d H:i:s'),
                    $code,
                    json_encode($data, JSON_UNESCAPED_UNICODE)
                ),
                FILE_APPEND
            );
            return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
        }

        $inbound = $data['obj'][$inboundIdx];
        $inboundId = $inbound['id'];

        $settings = json_decode($inbound['settings'], true);
        if (!is_array($settings)) {
            $settings = [];
        }
        $panelClient = null;
        foreach ($settings['clients'] ?? [] as $c) {
            if (!is_array($c)) {
                continue;
            }
            if (($c['email'] ?? '') === $uniID || ($c['subId'] ?? '') === $uniID) {
                $panelClient = $c;
                break;
            }
        }

        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Получен inbound ID: %d\n",
                date('Y-m-d H:i:s'),
                $inboundId
            ),
            FILE_APPEND
        );

        // API delClient ожидает id клиента (UUID в VLESS), не почту из БД
        $clientId = $panelClient['id'] ?? null;

        if ($clientId === null || $clientId === '') {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [DELETE KEY] Клиент с uniID=%s не найден в панели, только очистка БД\n",
                    date('Y-m-d H:i:s'),
                    $uniID
                ),
                FILE_APPEND
            );
            Database::send(
                'UPDATE qwees_users SET subscription = ?, status = ?, count_days = ?, count_devices = ?, amount = ?, date_end = ? WHERE uniID = ?',
                ['', strval('off'), intval(0), intval(0), intval(0), strval(''), $uniID]
            );
            return ['status' => 'partial', 'message' => 'Клиент не найден в X-UI, данные в БД очищены'];
        }

        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Удаление клиента clientId: %s из inbound %d\n",
                date('Y-m-d H:i:s'),
                $clientId,
                $inboundId
            ),
            FILE_APPEND
        );

        // 4. Удаляем клиента по clientId
        $ch = curl_init(self::panelBase() . '/to/xui/API/inbounds/' . $inboundId . '/delClient/' . rawurlencode((string) $clientId));
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Cookie: ' . $cookie
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $res = curl_exec($ch);
        $delCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $delCurlError = curl_error($ch);
        $delResult = json_decode($res, true);

        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Результат удаления клиента: HTTP %d, cURL Error: %s, Response: %s\n",
                date('Y-m-d H:i:s'),
                $delCode,
                $delCurlError,
                json_encode($delResult, JSON_UNESCAPED_UNICODE)
            ),
            FILE_APPEND
        );


        // 5. Удаляем из БД и явно переводим status в 'off'
        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Очистка данных в БД для пользователя uniID: %s\n",
                date('Y-m-d H:i:s'),
                $uniID
            ),
            FILE_APPEND
        );

        Database::send(
            'UPDATE qwees_users SET subscription = ?, status = ?, count_days = ?, count_devices = ?, amount = ?, date_end = ? WHERE uniID = ?',
            [strval(''), strval('off'), intval(0), intval(0), intval(0), strval(''), strval($uniID)]
        );

        file_put_contents(
            self::logFile(),
            sprintf(
                "[%s] [DELETE KEY] Данные в БД очищены\n",
                date('Y-m-d H:i:s')
            ),
            FILE_APPEND
        );

        if (isset($delResult['success']) && $delResult['success']) {
            // Дополнительная проверка - убеждаемся что клиент действительно удален
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [DELETE KEY] Проверка фактического удаления клиента\n",
                    date('Y-m-d H:i:s')
                ),
                FILE_APPEND
            );

            // Получаем обновленный список inbounds для проверки
            $ch = curl_init(self::panelBase() . self::pathList());
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
            ]);
            $verifyResponse = curl_exec($ch);
            $verifyCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $verifyError = curl_error($ch);

            if ($verifyResponse !== false && empty($verifyError) && $verifyCode === 200) {
                $verifyData = json_decode($verifyResponse, true);
                $vIdx = (int) ($_ENV['XUI_INBOUND_NUMBER'] ?? 0);
                if (!empty($verifyData['success']) && !empty($verifyData['obj'][$vIdx])) {
                    $verifyInbound = $verifyData['obj'][$vIdx];
                    $verifySettings = json_decode($verifyInbound['settings'], true);
                    $clientStillExists = false;

                    if (isset($verifySettings['clients']) && is_array($verifySettings['clients'])) {
                        foreach ($verifySettings['clients'] as $cl) {
                            if (($cl['email'] ?? '') === $uniID || ($cl['subId'] ?? '') === $uniID) {
                                $clientStillExists = true;
                                break;
                            }
                        }
                    }

                    if ($clientStillExists) {
                        file_put_contents(
                            self::logFile(),
                            sprintf(
                                "[%s] [DELETE KEY] ❌ ОШИБКА: Клиент uniID=%s все еще существует в inbound %d после удаления!\n",
                                date('Y-m-d H:i:s'),
                                $uniID,
                                $inboundId
                            ),
                            FILE_APPEND
                        );
                        return ['status' => 'error', 'message' => 'X-UI сообщил об успешном удалении, но клиент все еще существует на сервере'];
                    }
                    file_put_contents(
                        self::logFile(),
                        sprintf(
                            "[%s] [DELETE KEY] ✅ Подтверждено: клиент uniID=%s удалён с сервера\n",
                            date('Y-m-d H:i:s'),
                            $uniID
                        ),
                        FILE_APPEND
                    );
                }
            }

            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [DELETE KEY] Успешное завершение: ключ удалён из X-UI и БД\n",
                    date('Y-m-d H:i:s')
                ),
                FILE_APPEND
            );
            return ['status' => 'ok', 'message' => 'Ключ успешно удалён из X-UI и базы данных'];
        } else {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [DELETE KEY] Частичное завершение: удалён из БД, но ошибка при удалении из X-UI: %s\n",
                    date('Y-m-d H:i:s'),
                    $res
                ),
                FILE_APPEND
            );
            return ['status' => 'partial', 'message' => 'Удалён из БД, но ошибка при удалении из X-UI: ' . $res];
        }
    }

    /**
     * Выполняет очистку устаревших (истекших) пользователей в XUI и синхронизирует состояние с базой данных.
     *
     * Производит авторизацию на сервере XUI, вызывает специальный API очистки истёкших клиентов.
     * Затем обновляет таблицу пользователей в БД, выставляя у всех истёкших подписок статус used и обнуляя ключевые VPN-поля.
     *
     * @return void
     */
    public static function CleanUP()
    {
        // Логин и получение куки
        $ch = curl_init(self::panelBase() . self::pathLogin());
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'username' => self::xuiLogin(),
                'password' => self::xuiPassword()
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);


        $cookieName = self::cookieName();
        // Handle both 'x-ui' and '3x-ui' cookie names
        $cookiePattern = '/Set-Cookie:\s*(3?' . $cookieName . '=[^;]+)/i';
        if ($code !== 200 || !preg_match($cookiePattern, $response, $m)) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [CLEANUP] Auth error on XUI panel: HTTP %d, Error: %s\n",
                    date('Y-m-d H:i:s'),
                    $code,
                    $curlError
                ),
                FILE_APPEND
            );
            return;
        }
        $cookie = $m[1];

        // Список inbounds
        $ch = curl_init(self::panelBase() . self::pathList());
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);


        if ($response === false || !empty($curlError)) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [ОШИБКА - ГЛОБАЛЬНАЯ ОЧИСТКА] Ошибка cURL при получении inbounds: %s\n",
                    date('Y-m-d H:i:s'),
                    $curlError
                ),
                FILE_APPEND
            );
            return;
        }

        $data = json_decode($response, true);
        $inboundIdx = (int) ($_ENV['XUI_INBOUND_NUMBER'] ?? 0);
        if ($code !== 200 || empty($data['success']) || empty($data['obj'][$inboundIdx])) {
            file_put_contents(
                self::logFile(),
                sprintf(
                    "[%s] [ОШИБКА - ГЛОБАЛЬНАЯ ОЧИСТКА] Ошибка получения списка inbounds: HTTP %d\n",
                    date('Y-m-d H:i:s'),
                    $code
                ),
                FILE_APPEND
            );
            return;
        }

        $inbound = $data['obj'][$inboundIdx];
        $inboundId = $inbound['id'];

        // 3. Удаляем всех истекших клиентов
        $ch = curl_init(self::panelBase() . '/to/xui/API/inbounds/delDepletedClients/' . $inboundId);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Cookie: ' . $cookie
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $res = curl_exec($ch);
        $result = json_decode($res, true);


        // Переписано: error_log на лог-файл
        if (isset($result['success']) && $result['success']) {
            file_put_contents(self::logFile(), "[" . date('Y-m-d H:i:s') . "] [УСПЕШНО - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: Успешно удалены истекшие клиенты из X-UI\n", FILE_APPEND);
        } else {
            file_put_contents(self::logFile(), "[" . date('Y-m-d H:i:s') . "] [ОШИБКА - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: Ошибка при удалении истекших клиентов: " . $res . "\n", FILE_APPEND);
            return;
        }

        $nowMs = time() * 1000;

        Database::send(
            'UPDATE qwees_users SET subscription = ?, status = ?, count_days = ?, count_devices = ?, amount = ?, date_end = ? WHERE status != ? AND date_end < ?',
            [strval(''), strval('off'), intval(0), intval(0), intval(0), strval(''), strval('off'), date('Y-m-d')]
        );

        file_put_contents(self::logFile(), "[" . date('Y-m-d H:i:s') . "] [УСПЕШНО - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: Обновлена БД — помечены как просроченные\n", FILE_APPEND);
    }
}