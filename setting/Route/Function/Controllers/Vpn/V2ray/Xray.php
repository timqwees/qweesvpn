<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Vpn\V2ray;

use Setting\Route\Function\Controllers\Client\GetUser;
use Setting\Route\Function\Controllers\Server\Network as ServerNetwork;
use App\Config\Database;
use Setting\Route\Function\Functions;
use DateTime, DateTimeZone;
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
        return \sprintf(
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

    /**
     * Настройка текущего сервера из реестра Network (Server/Network.php).
     * Транспорт уехал в PanelHttp (SRP) — здесь только делегаты и домен.
     */
    private static function serverSetting(string $key): string
    {
        return PanelHttp::server($key);
    }

    /** VLESS сервер хост (для client_data). */
    private static function vlessHost(): string
    {
        return self::serverSetting('VLESS_SERVER');
    }

    /** Номер inbound'а в списке list. */
    private static function inboundNumber(): int
    {
        return (int) self::serverSetting('XUI_INBOUND_NUMBER');
    }

    /** Абсолютный путь лог-файла (корень проекта — туда же смотрят читалки админки). */
    private static function logFile(): string
    {
        return dirname(__DIR__, 6) . '/' . basename($_ENV['LOG_FILE_NAME'] ?? 'qwees.log');
    }

    /** Единая точка логирования Xray: один формат, один файл. */
    public static function log(string $message): void
    {
        @file_put_contents(self::logFile(), $message, FILE_APPEND);
    }

    // ==================== МОНИТОРИНГ (дашборд) ====================
    // Тонкие публичные обёртки над PanelHttp — по apixray.md.
    // Сервер уже должен быть выбран через ServerNetwork::selectServer($code).

    /** Снапшот железа: cpu/mem/swap/disk/netIO/xray/tcp. GET /panel/api/server/status. */
    public static function panelServerStatus(): array|false
    {
        $res = PanelHttp::threeXuiHttp('GET', '/panel/api/server/status');
        if (!\is_array($res) || ($res['success'] ?? false) !== true || !\is_array($res['obj'] ?? null)) return false;
        return $res['obj'];
    }

    /**
     * Параллельный пакет GET-запросов к панели (curl_multi) — дашборд одним заходом.
     * @param string[] $paths пути от корня панели, напр. ['/panel/api/server/status']
     * @return array<string,array|false> [path => декодированный JSON или false]
     */
    public static function panelBatch(array $paths): array
    {
        return PanelHttp::panelBatch($paths);
    }


    /** В ответе API streamSettings иногда строка JSON. */    private static function streamSettingsArray(array $inbound): array
    {
        $ss = $inbound['streamSettings'] ?? null;
        if (is_string($ss)) {
            $d = json_decode($ss, true);
            return is_array($d) ? $d : [];
        }
        return is_array($ss) ? $ss : [];
    }

    /**
     * Синхронизация expiry (мс) в БД по uniID (источник — время истечения, которое панель 3x-ui
     * установила клиенту). Приходит из ответа панели, чтобы не было рассинхронизации.
     */
    private static function syncUserExpiryByUniID(string $uniID, int $expiryMs): void
    {
        if ($expiryMs <= 0 || $uniID === '') {
            return;
        }
        Database::send(
            'UPDATE qwees_subscriptions SET expiry = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?',
            [$expiryMs, $uniID]
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
    private function addClientPanelApi(int $days, string $uniID, int $device_limit, string $switch = '', ?int $fixedExpiryMs = null): array|false
    {
        $user = new GetUser($uniID);

        // Выбираем сервер клиента (субдомен его подписки, без подписки — дефолтный)
        ServerNetwork::selectServer($uniID);

        $data = PanelHttp::threeXuiHttp('GET', '/panel/api/inbounds/list');
        if ($data === false || empty($data['success']) || empty($data['obj']) || !\is_array($data['obj'])) {
            self::log(\sprintf("[%s] [ПОДПИСКА -> СЕРВЕР] Список inbounds недоступен\n", date('Y-m-d H:i:s')));
            return false;
        }

        $inboundIdx = self::inboundNumber();
        if (empty($data['obj'][$inboundIdx])) {
            self::log(\sprintf("[%s] [ПОДПИСКА -> СЕРВЕР] Нет inbound с индексом %d\n", date('Y-m-d H:i:s'), $inboundIdx));
            return false;
        }

        $inbound = $data['obj'][$inboundIdx];
        if (empty($inbound['id']) || ($inbound['enable'] ?? true) === false) {
            return false;
        }

        $protocol = strtolower((string) ($inbound['protocol'] ?? ''));
        $rawSettings = $inbound['settings'] ?? '{}';
        $settings = \is_array($rawSettings) ? $rawSettings : json_decode((string) $rawSettings, true);
        if (!\is_array($settings)) {
            $settings = [];
        }
        if (!isset($settings['clients']) || !\is_array($settings['clients'])) {
            $settings['clients'] = [];
        }

        if ($device_limit === null) {
            $device_limit = isset($_ENV['XUI_DEVICE_LIMIT']) ? (int) $_ENV['XUI_DEVICE_LIMIT'] : 1;
        }

        $needsUuid = \in_array($protocol, ['vless', 'vmess'], true);

        // Ищем клиента по subId (= uniID) — надёжнее имени, т.к. имя может совпадать у разных юзеров
        $existingIndex = null;
        $currentExpiryMs = 0;
        foreach ($settings['clients'] as $idx => $c) {
            if (!\is_array($c)) {
                continue;
            }
            if (($c['subId'] ?? '') === $uniID || ($c['email'] ?? '') === $uniID) {
                $existingIndex = $idx;
                $currentExpiryMs = (int) ($c['expiryTime'] ?? 0);
                break;
            }
        }

        // Продление или выдача с нуля: при обновлении существующего клиента срок считается от
        // max(сейчас, текущий expiry панели), иначе — от текущего момента (не сбрасываем остаток).
        $now = new DateTime('now', new DateTimeZone('Europe/Moscow'));
        $base = new DateTime('@' . (max($now->getTimestamp() * 1000, $currentExpiryMs) / 1000));
        $base->setTimezone(new DateTimeZone('Europe/Moscow'));

        if ($fixedExpiryMs !== null && $fixedExpiryMs > 0) {
            // Фиксированный срок (перенос клиента на другой сервер — остаток дней сохраняется)
            $expiry = $fixedExpiryMs;
        } elseif ($switch === 'hours') {
            $expiry = $base->modify('+' . (int) $days . ' hours')->getTimestamp() * 1000;
        } elseif($switch === 'minutes') {
            $expiry = $base->modify('+' . (int) $days . ' minutes')->getTimestamp() * 1000;
        } else {//default
          // настройка выдачи времени подписки (всем по умолчанию ровно до конца отсеченного дня 23:59:59, пишем 22 так как +1 xray делает при 23 = 00:59:59)
          $expiry = $base->modify('+' . (int) $days . ' days')->setTime(22, 59, 59)->getTimestamp() * 1000;
        }

        if ($existingIndex !== null) {
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
            $clientEmail = (string) $user->getEmail();
            if ($clientEmail === '') {//3.9.0 строго требует email — без него панель отвергнет
                self::log(\sprintf("[%s] [ПОДПИСКА -> СЕРВЕР] Нет email для %s, выдача невозможна\n", date('Y-m-d H:i:s'), $uniID));
                return false;
            }
            $client = [
                'id' => $clientId,
                'email' => $clientEmail, // отображаемая почта в панели
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

        $updateUser = PanelHttp::threeXuiHttp('POST', $path, $payload);
        if ($updateUser === false || ($updateUser['success'] ?? false) !== true) {
            self::log(\sprintf(
                "[%s] [ПОДПИСКА -> СЕРВЕР] Ошибка API (%s): %s\n",
                date('Y-m-d H:i:s'),
                $path,
                json_encode($updateUser, JSON_UNESCAPED_UNICODE)
            ));
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
                'server' => ServerNetwork::getServerCode(), // код сервера из реестра Network
                'subscription_url' => ServerNetwork::getSubscriptionUrl($uniID),
            ],
        ];
    }

    /**
     * Создаёт или обновляет клиента VPN через REST API панели 3x-ui (`/panel/api/clients/*`).
     * Bearer `XUI_API_TOKEN` или сессия POST `/login` + CSRF. Inbound — индекс `XUI_INBOUND_NUMBER` в списке list.
     * Сервер выбирается автоматически по подписке пользователя (субдомен), см. Server/Network.php.
     *
     * @param int|string $days         Количество дней действия подписки
     * @param string     $uniID        Уникальный идентификатор пользователя
     * @param int|null   $device_limit Лимит устройств (опционально, по умолчанию из XUI_DEVICE_LIMIT)
     * @param string     $switch       Переключатель срока: '' | 'hours' | 'minutes'
     * @param int|null   $expiryMs     Фиксированный expiry в мс (перенос между серверами — срок сохраняется)
     *
     * @return array|false              Возвращает массив с данными клиента при успехе:
     *                                 [
     *                                   'success' => true,
     *                                   'client_data' => [
     *                                     'id' => string,           // UUID клиента
     *                                     'email' => string,        // Имя клиента (getEmail)
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
     *                                     'network' => string,      // Тип сети (ws)
     *                                     'server' => string,       // Код сервера (nl, fi, ...)
     *                                     'subscription_url' => string // URL подписки на этом сервере
     *                                   ]
     *                                 ]
     *                                 При ошибке возвращает false и записывает лог
     */
    public function addClient(int $days, string $uniID, int $device_limit, string $switch = '', ?int $expiryMs = null): array|false
    {
        return $this->addClientPanelApi($days, $uniID, $device_limit, $switch, $expiryMs);
    }

    /**
     * Продление клиента (3x-ui): updateClient или создание через addClientPanelApi.
     *
     * @return array{status: string, message: string}
     */
    private function xuiUpdate3xUi(string $uniID, int $bonusDays): array
    {
        ServerNetwork::selectServer($uniID); // сервер клиента по его подписке
        $data = PanelHttp::threeXuiHttp('GET', '/panel/api/inbounds/list');
        if ($data === false || empty($data['success']) || empty($data['obj'])) {
            return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
        }
        $inboundIdx = self::inboundNumber();
        $inbound = $data['obj'][$inboundIdx] ?? null;
        if (!$inbound || empty($inbound['id'])) {
            return ['status' => 'error', 'message' => 'Inbound не найден'];
        }

        $rawSettings = $inbound['settings'] ?? '{}';
        $settings = \is_array($rawSettings) ? $rawSettings : json_decode((string) $rawSettings, true);
        if (!\is_array($settings)) {
            $settings = [];
        }
        if (!isset($settings['clients']) || !\is_array($settings['clients'])) {
            $settings['clients'] = [];
        }

        $found = false;
        $clientRow = null;
        $newExpiry = 0;
        foreach ($settings['clients'] as $idx => $c) {
            if (!\is_array($c)) {
                continue;
            }
            // Ищем по subId (= uniID) — уникальный ключ; email (= имя) может совпадать у разных юзеров
            if (($c['subId'] ?? '') === $uniID || ($c['email'] ?? '') === $uniID) {
                $currentExpiry = (int) ($c['expiryTime'] ?? 0);
                $nowMs = new DateTime('now', new DateTimeZone('Europe/Moscow'))->getTimestamp() * 1000;
                $baseMs = max($nowMs, $currentExpiry);
                $newExpiry = $baseMs + $bonusDays * 86400000;
                $settings['clients'][$idx]['expiryTime'] = $newExpiry;
                $settings['clients'][$idx]['enable'] = true;
                $found = true;
                $clientRow = $settings['clients'][$idx];
                break;
            }
        }

        if (!$found) {
            $lim = isset($_ENV['XUI_DEVICE_LIMIT']) ? (int) $_ENV['XUI_DEVICE_LIMIT'] : 1;
            $add = $this->addClientPanelApi($bonusDays, $uniID, $lim);
            if (\is_array($add) && ($add['success'] ?? false) === true) {
                self::syncUserExpiryByUniID($uniID, (int) ($add['client_data']['expiryTime'] ?? 0));
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
        $updateUser = PanelHttp::threeXuiHttp('POST', $path, $payload);
        if ($updateUser !== false && ($updateUser['success'] ?? false) === true) {
            self::syncUserExpiryByUniID($uniID, $newExpiry);
            return ['status' => 'ok', 'message' => 'Бонусные дни добавлены'];
        }
        self::log(\sprintf(
            "[%s] [ПОДПИСКА -> СЕРВЕР] Update failed (%s): %s\n",
            date('Y-m-d H:i:s'),
            $path,
            json_encode($updateUser, JSON_UNESCAPED_UNICODE)
        ));
        return ['status' => 'error', 'message' => 'Не удалось обновить клиента в панели 3x-ui'];
    }

    /**
     * Продлевает клиента через API панели 3x-ui (`updateClient` / `addClient`). Обновляет expiry (мс) в БД.
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
     * Удаление клиента только из панели 3x-ui (запись в БД не трогается).
     * Используется DeleteKey (плюс очистка БД) и Network::switchServer (перенос на другой сервер).
     *
     * 3.1.0: POST /panel/api/clients/del/:email
     * :email = реальный email клиента из панели (= getFirstName(), установленный при создании).
     * Ищем клиента по subId (= uniID), берём его email и подставляем в URL.
     *
     * @return array{status: string, message: string}
     */
    public static function deleteClientFromPanel(string $uniID, ?string $serverCode = null): array
    {
        if ($serverCode !== null) ServerNetwork::selectServer(null, $serverCode);//явный сервер (чистка призраков)
        else ServerNetwork::selectServer($uniID); // сервер клиента по его подписке

        $data = PanelHttp::threeXuiHttp('GET', '/panel/api/inbounds/list');
        if ($data === false || empty($data['success']) || empty($data['obj'])) {
            return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
        }
        $inboundIdx = self::inboundNumber();
        if (empty($data['obj'][$inboundIdx])) {
            return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
        }
        $inbound = $data['obj'][$inboundIdx];
        $rawSettings = $inbound['settings'] ?? '{}';
        $settings = is_array($rawSettings) ? $rawSettings : json_decode((string) $rawSettings, true);
        if (!\is_array($settings)) {
            $settings = [];
        }

        // Ищем по subId (= uniID) — уникальный ключ
        // email может быть именем (getFirstName), subId всегда uniID
        $found = false;
        $deleteEmail = $uniID; // запасной вариант
        foreach ($settings['clients'] ?? [] as $c) {
            if (!\is_array($c)) {
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
            return ['status' => 'partial', 'message' => 'Клиент не найден в панели'];
        }

        // 3.1.0: POST /panel/api/clients/del/:email (убран inboundId из пути)
        $delPath = '/panel/api/clients/del/' . rawurlencode($deleteEmail);
        $delResult = PanelHttp::threeXuiHttp('POST', $delPath, null);

        if ($delResult !== false && ($delResult['success'] ?? false) === true) {
            // Верификация: клиент действительно удалён из inbound
            $verify = PanelHttp::threeXuiHttp('GET', '/panel/api/inbounds/list');
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
            return ['status' => 'ok', 'message' => 'Клиент удалён с сервера'];
        }
        return [
            'status' => 'partial',
            'message' => 'Успешно удалено из хранилища, но ошибка при удалении из сервера: '
                . json_encode($delResult, JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * Удаление ключа через API 3x-ui + очистка подписки в БД.
     *
     * @return array<string, string>
     */
    private function deleteKey3xUi(string $uniID): array
    {
        $result = self::deleteClientFromPanel($uniID);

        if (($result['status'] ?? '') === 'ok') {
            Database::send('DELETE FROM qwees_subscriptions WHERE uniID = ?', [strval($uniID)]);
            \Setting\Route\Function\Controllers\Client\Src\Client::forget(strval($uniID));//мемо сброшено
            return ['status' => 'ok', 'message' => 'Подписка успешно удалёна'];
        }

        return $result;
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

        self::log(\sprintf(
            "[%s] [ПОДПИСКА -> УДАЛЕНИЕ] Начало удаления ключа для пользователя uniID: %s\n",
            date('Y-m-d H:i:s'),
            $uniID
        ));

        if (empty($uniID)) {
            return ['status' => 'error', 'message' => 'Пользователь не найден'];
        }

        $result = $this->deleteKey3xUi((string) $uniID);

        return $result;
    }
}
