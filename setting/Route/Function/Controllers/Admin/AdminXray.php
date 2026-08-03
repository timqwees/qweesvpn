<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin;

use App\Models\Network\Network;
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\Kassa\PriceConfig;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;
use App\Config\Database;
use DateTime, DateTimeZone;

class AdminXray
{
    /**
     * Обновляет данные подписки в БД после успешной выдачи на сервере.
     *
     * @param string $uniID    Уникальный ID пользователя
     * @param int    $days     Количество дней подписки
     * @param int    $devices  Лимит устройств
     * @param float  $amount   Сумма (для админской выдачи = 0)
     * @param int    $expiryMs expiry (мс), установленный панелью 3x-ui (источник истины)
     *
     * @return bool Успешность обновления
     */
    private static function updateUserSubscription(string $uniID, int $days, int $devices, float $amount = 0, int $expiryMs = 0): bool
    {
        if ($expiryMs <= 0) {
            $userData = Database::send("SELECT expiry FROM qwees_subscriptions WHERE uniID = ?", [$uniID]);
            $currentExpiry = (int) ($userData[0]['expiry'] ?? 0);
            $nowMs = new DateTime('now', new DateTimeZone('Europe/Moscow'))->getTimestamp() * 1000;
            $expiryMs = max($nowMs, $currentExpiry) + $days * 86400000;
        }

        $params = [$uniID, 'on', rtrim($_ENV['XUI_URL_SUBSCRIPTION'] ?? '', '/') . '/' . $uniID, $amount, $days, $devices, $expiryMs];

        if (Database::isMysql()) {
            $result = Database::send(
                'INSERT INTO qwees_subscriptions (uniID, status, subscription, amount, count_days, count_devices, expiry, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE
                   status = VALUES(status),
                   subscription = VALUES(subscription),
                   amount = VALUES(amount),
                   count_days = VALUES(count_days),
                   count_devices = VALUES(count_devices),
                   expiry = VALUES(expiry),
                   updated_at = CURRENT_TIMESTAMP',
                $params
            );
        } else {
            $result = Database::send(
                'INSERT OR REPLACE INTO qwees_subscriptions (uniID, status, subscription, amount, count_days, count_devices, expiry, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)',
                $params
            );
        }

        if ($result === false) {
            // Подписка/VPN-ключ уже выданы, но запись в БД не удалась
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                sprintf(
                    "[%s] [ОШИБКА БД] %s: подписка %d дней, %d уст. выдана, но обновление БД не удалось\n",
                    date('Y-m-d H:i:s'),
                    $uniID,
                    $days,
                    $devices
                ),
                FILE_APPEND
            );
        } else {
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                sprintf("[%s] [АДМИН-ВЫДАЧА] %s: %d дней, %d уст., до %s\n", date('Y-m-d H:i:s'), $uniID, $days, $devices, date('Y-m-d H:i:s', (int) ($expiryMs / 1000))),
                FILE_APPEND
            );
        }

        return $result !== false;
    }

    /**
     * Создание подписки пользователю в днях
     */
    public static function onAdminAddClientDays()
    {
        $url = $_POST['url'] ?? '/admin';

        // Новая форма (Панель создания пользователя): email, first_name, subscription, duration_days
        if (!empty($_POST['email'])) {
            $firstName = trim($_POST['first_name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($firstName) || empty($email)) {
                Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Имя и Email обязательны'));
                return;
            }

            // Регистрируем пользователя
            $userData = [
                'first_name' => $firstName,
                'last_name' => trim($_POST['last_name'] ?? ''),
                'email' => $email,
            ];

            $subscription = trim($_POST['subscription'] ?? '');
            $durationDays = (int) ($_POST['duration_days'] ?? 0);

            if (!empty($subscription) && $durationDays >= 1) {
                $userData['subscription'] = $subscription;
                $userData['duration_days'] = $durationDays;
            }

            $result = Auth::registerUser($userData);

            if (empty($result['success'])) {
                Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode($result['message'] ?? 'Ошибка создания пользователя'));
                return;
            }

            $uniID = $result['uniID'];

            // Если выбрана подписка — выдаём VPN-ключ
            if (!empty($subscription) && $durationDays >= 1) {
                $devices = self::getDevicesForSubscription($subscription);

                $planPrice = (float) (PriceConfig::getPrices()[1][$subscription] ?? 0);

                $xray = new Xray();
                $vpnResult = $xray->addClient($durationDays, $uniID, $devices);

                if ($vpnResult === false || empty($vpnResult['success'])) {
                    Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Пользователь создан, но ошибка выдачи VPN-ключа'));
                    return;
                }

                $dbUpdated = self::updateUserSubscription($uniID, $durationDays, $devices, $planPrice, (int) ($vpnResult['client_data']['expiryTime'] ?? 0));

                if (!$dbUpdated) {
                    Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('VPN-ключ выдан, но ошибка обновления БД'));
                    return;
                }
            }

            Network::onRedirect($url . '?message_status=success&message_msg=' . urlencode('Пользователь успешно создан!'));
            return;
        }

        // Старая форма (Выдать подписку): uniID, days, devices
        $days = (int) ($_POST['days'] ?? 0);
        $uniID = (string) ($_POST['uniID'] ?? '');
        $devices = (int) ($_POST['devices'] ?? 0);

        if (empty($days) || empty($uniID) || $devices < 0) {
            Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Не все поля заполнены для добавления подписки'));
            return;
        }

        $xray = new Xray();
        $result = $xray->addClient($days, $uniID, $devices);

        if ($result === false || empty($result['success'])) {
            Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Ошибка добавления подписки'));
            return;
        }

        $dbUpdated = self::updateUserSubscription($uniID, $days, $devices, 0, (int) ($result['client_data']['expiryTime'] ?? 0));

        if (!$dbUpdated) {
            Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Подписка выдана, но ошибка обновления БД'));
            return;
        }

        Network::onRedirect($url . '?message_status=success&message_msg=' . urlencode('Подписка успешно добавлена!'));
    }

    /**
     * Создание подписки пользователю в часах
     */
    public static function onAdminAddClientHours()
    {
        $url = $_POST['url'] ?? '/admin';

        // Новая форма (Панель создания пользователя): email, first_name, subscription, duration_days
        if (!empty($_POST['email'])) {
            $firstName = trim($_POST['first_name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($firstName) || empty($email)) {
                Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Имя и Email обязательны'));
                return;
            }

            // Регистрируем пользователя
            $userData = [
                'first_name' => $firstName,
                'last_name' => trim($_POST['last_name'] ?? ''),
                'email' => $email,
            ];

            $subscription = trim($_POST['subscription'] ?? '');
            $durationHours = (int) ($_POST['duration_hours'] ?? 0);//под добавление в новой форме name='duration_hours'

            if (!empty($subscription) && $durationHours >= 1) {
                $userData['subscription'] = $subscription;
                $userData['duration_hours'] = $durationHours;
            }

            $result = Auth::registerUser($userData);

            if (empty($result['success'])) {
                Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode($result['message'] ?? 'Ошибка создания пользователя'));
                return;
            }

            $uniID = $result['uniID'];

            // Если выбрана подписка — выдаём VPN-ключ
            if (!empty($subscription) && $durationHours >= 1) {
                $devices = self::getDevicesForSubscription($subscription);

                $planPrice = (float) (PriceConfig::getPrices()[1][$subscription] ?? 0);

                $xray = new Xray();
                $vpnResult = $xray->addClient($durationHours, $uniID, $devices, 'hours');

                if ($vpnResult === false || empty($vpnResult['success'])) {
                    Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Пользователь создан, но ошибка выдачи VPN-ключа'));
                    return;
                }

                $dbUpdated = self::updateUserSubscription($uniID, $durationHours, $devices, $planPrice, (int) ($vpnResult['client_data']['expiryTime'] ?? 0));

                if (!$dbUpdated) {
                    Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('VPN-ключ выдан, но ошибка обновления БД'));
                    return;
                }
            }

            Network::onRedirect($url . '?message_status=success&message_msg=' . urlencode('Пользователь успешно создан!'));
            return;
        }

        // Старая форма (Выдать подписку): uniID, days, devices
        $hours = (int) ($_POST['hours'] ?? 0);
        $uniID = (string) ($_POST['uniIDhours'] ?? $_POST['uniID'] ?? '');
        $devices = (int) ($_POST['devices'] ?? 0);

        if (empty($hours) || empty($uniID) || $devices < 0) {
            Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Не все поля заполнены для добавления подписки'));
            return;
        }

        $xray = new Xray();
        $result = $xray->addClient($hours, $uniID, $devices, 'hours');//4 argument = hours - switch (переключатель)

        if ($result === false || empty($result['success'])) {
            Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Ошибка добавления подписки'));
            return;
        }

        $dbUpdated = self::updateUserSubscription($uniID, $hours, $devices, 0, (int) ($result['client_data']['expiryTime'] ?? 0));

        if (!$dbUpdated) {
            Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Подписка выдана, но ошибка обновления БД'));
            return;
        }

        Network::onRedirect($url . '?message_status=success&message_msg=' . urlencode('Подписка успешно добавлена!'));
    }

    /**
     * Создание подписки пользователю в минутах
     */
    public static function onAdminAddClientMinutes()
    {
        $url = $_POST['url'] ?? '/admin';

        // Новая форма (Панель создания пользователя): email, first_name, subscription, duration_days
        if (!empty($_POST['email'])) {
            $firstName = trim($_POST['first_name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($firstName) || empty($email)) {
                Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Имя и Email обязательны'));
                return;
            }

            // Регистрируем пользователя
            $userData = [
                'first_name' => $firstName,
                'last_name' => trim($_POST['last_name'] ?? ''),
                'email' => $email,
            ];

            $subscription = trim($_POST['subscription'] ?? '');
            $durationMinutes = (int) ($_POST['duration_minutes'] ?? 0);//под добавление в новой форме name='duration_hours'

            if (!empty($subscription) && $durationMinutes >= 1) {
                $userData['subscription'] = $subscription;
                $userData['duration_hours'] = $durationMinutes;
            }

            $result = Auth::registerUser($userData);

            if (empty($result['success'])) {
                Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode($result['message'] ?? 'Ошибка создания пользователя'));
                return;
            }

            $uniID = $result['uniID'];

            // Если выбрана подписка — выдаём VPN-ключ
            if (!empty($subscription) && $durationMinutes >= 1) {
                $devices = self::getDevicesForSubscription($subscription);

                $planPrice = (float) (PriceConfig::getPrices()[1][$subscription] ?? 0);

                $xray = new Xray();
                $vpnResult = $xray->addClient($durationMinutes, $uniID, $devices, 'minutes');

                if ($vpnResult === false || empty($vpnResult['success'])) {
                    Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Пользователь создан, но ошибка выдачи VPN-ключа'));
                    return;
                }

                $dbUpdated = self::updateUserSubscription($uniID, $durationMinutes, $devices, $planPrice, (int) ($vpnResult['client_data']['expiryTime'] ?? 0));

                if (!$dbUpdated) {
                    Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('VPN-ключ выдан, но ошибка обновления БД'));
                    return;
                }
            }

            Network::onRedirect($url . '?message_status=success&message_msg=' . urlencode('Пользователь успешно создан!'));
            return;
        }

        // Старая форма (Выдать подписку): uniID, days, devices
        $minutes = (int) ($_POST['minutes'] ?? 0);
        $uniID = (string) ($_POST['uniIDMinutes'] ?? $_POST['uniID'] ?? '');
        $devices = (int) ($_POST['devices'] ?? 0);

        if (empty($minutes) || empty($uniID) || $devices < 0) {
            Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Не все поля заполнены для добавления подписки'));
            return;
        }

        $xray = new Xray();
        $result = $xray->addClient($minutes, $uniID, $devices, 'minutes');//4 argument = hours - switch (переключатель)

        if ($result === false || empty($result['success'])) {
            Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Ошибка добавления подписки'));
            return;
        }

        $dbUpdated = self::updateUserSubscription($uniID, $minutes, $devices, 0, (int) ($result['client_data']['expiryTime'] ?? 0));

        if (!$dbUpdated) {
            Network::onRedirect($url . '?message_status=error&message_msg=' . urlencode('Подписка выдана, но ошибка обновления БД'));
            return;
        }

        Network::onRedirect($url . '?message_status=success&message_msg=' . urlencode('Подписка успешно добавлена!'));
    }

    /**
     * Определяет количество устройств по имени тарифа (basic/clasic/pro).
     */
    private static function getDevicesForSubscription(string $subscriptionName): int
    {
        return PriceConfig::getTariffMeta()[$subscriptionName]['devices'] ?? 1;
    }

    /**
     * Изьятие подписки пользователя
     */
    public static function onAdminReduceClient()
    {
        if (empty($_POST['uniID'])) {
            Network::onRedirect($_POST['url'] ?: '/admin?message_status=error&message_msg=не указан ID пользователя для изьятия подписки');
            return;
        }

        $xray = new Xray();
        $xray->DeleteKey((string) $_POST['uniID']);
        Network::onRedirect($_POST['url'] ?: '/admin?message_status=success&message_msg=Подписка успешно была изьята!');
    }

    /**
     * Получение информации о пользователе (с данными подписки)
     */
    public function getAdminUser(string $uniID)
    {
        $users = Database::send(
            'SELECT u.*, s.status as status, s.subscription, s.amount, s.count_days, s.count_devices, s.expiry 
             FROM qwees_users u 
             LEFT JOIN qwees_subscriptions s ON u.uniID = s.uniID 
             WHERE u.uniID = ?',
            [$uniID]
        );
        header('Content-Type: application/json');
        echo json_encode([
            'status' => !empty($users[0]) ? true : false,
            'data' => $users[0] ?? null
        ]);
        exit;
    }

    public function onAdminCleanLogs()
    {
        $logFile = $_ENV['LOG_FILE_NAME'] ?? 'qwees.log';
        if (file_exists($logFile))
            file_put_contents($logFile, '');
        Network::onRedirect($_POST['url'] ?? '/admin?message_status=success&message_msg=Логи успешно очищены!');
    }
}
