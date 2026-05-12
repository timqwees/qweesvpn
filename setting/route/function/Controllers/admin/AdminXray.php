<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin;

use App\Models\Network\Network;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;
use App\Config\Database;

class AdminXray
{
    /**
     * Обновляет данные подписки в БД после успешной выдачи на сервере.
     *
     * @param string $uniID   Уникальный ID пользователя
     * @param int    $days    Количество дней подписки
     * @param int    $devices Лимит устройств
     * @param float  $amount  Сумма (для админской выдачи = 0)
     *
     * @return bool Успешность обновления
     */
    private static function updateUserSubscription(string $uniID, int $days, int $devices, float $amount = 0): bool
    {
        $userData = Database::send("SELECT date_end FROM qwees_subscriptions WHERE uniID = ?", [$uniID]);
        $currentEnd = $userData[0]['date_end'] ?? null;

        if ($currentEnd && strtotime($currentEnd) > time()) {
            $endDate = date('Y-m-d', strtotime($currentEnd . " +{$days} days"));
        } else {
            $endDate = date('Y-m-d', strtotime("+{$days} days"));
        }

        $result = Database::send(
            'INSERT OR REPLACE INTO qwees_subscriptions (uniID, status, subscription, amount, count_days, count_devices, date_end, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)',
            [$uniID, 'on', rtrim($_ENV['XUI_URL_SUBSCRIPTION'] ?? '', '/') . '/' . $uniID, $amount, $days, $devices, $endDate]
        );

        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
            sprintf("[%s] [АДМИН-ВЫДАЧА] %s: %d дней, %d уст., до %s\n", date('Y-m-d H:i:s'), $uniID, $days, $devices, $endDate),
            FILE_APPEND
        );

        return $result !== false;
    }

    /**
     * Создание подписки пользователю
     */
    public static function onAdminAddClient()
    {
        if (empty($_POST['days']) || empty($_POST['uniID']) || !isset($_POST['devices'])) {
            Network::onRedirect($_POST['url'] ?? '/admin?message_status=error&message_msg=не все поля заполнены для добавления подписки');
            return;
        }

        $days = (int) $_POST['days'];
        $uniID = (string) $_POST['uniID'];
        $devices = (int) $_POST['devices'];

        $xray = new Xray();
        $result = $xray->addClient($days, $uniID, $devices);

        if ($result === false || empty($result['success'])) {
            Network::onRedirect($_POST['url'] ?? '/admin?message_status=error&message_msg=ошибка добавления подписки');
            return;
        }

        $dbUpdated = self::updateUserSubscription($uniID, $days, $devices, 0);

        if (!$dbUpdated) {
            Network::onRedirect($_POST['url'] ?? '/admin?message_status=error&message_msg=подписка выдана, но ошибка обновления БД');
            return;
        }

        Network::onRedirect($_POST['url'] ?? '/admin?message_status=success&message_msg=Подписка успешно добавлена!');
    }

    /**
     * Изьятие подписки пользователя
     */
    public static function onAdminReduceClient()
    {
        if (empty($_POST['uniID'])) {
            Network::onRedirect($_POST['url'] ?? '/admin?message_status=error&message_msg=не указан ID пользователя для изьятия подписки');
            return;
        }

        $xray = new Xray();
        $xray->DeleteKey((string) $_POST['uniID']);
        Network::onRedirect($_POST['url'] ?? '/admin?message_status=success&message_msg=Подписка успешно была изьята!');
    }

    /**
     * Получение информации о пользователе (с данными подписки)
     */
    public function getAdminUser(string $uniID)
    {
        $users = Database::send(
            'SELECT u.*, s.status as status, s.subscription, s.amount, s.count_days, s.count_devices, s.date_end 
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