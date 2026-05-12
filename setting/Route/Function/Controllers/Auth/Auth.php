<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Auth;

use App\Config\Database;
use App\Config\Session;
use App\Controllers\MailController;
use App\Models\Network\Network;
use App\Models\User\User;
use Setting\Route\Function\Controllers\Refer\Refer;

class Auth extends Network
{

    public static function auth(): void
    {
        if (!isset(Session::init('user')['uniID']))
            Network::onRedirect('/auth/login');
    }

    // ======= GLOBAL FUNCTION AUTH =============

    /**
     * Summary of onMail
     * @return bool
     * @description отправка почтового уведолмения для POST запроса
     */
    public function onMail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            return;
        }

        $email = isset($_POST['email']) && !empty($_POST['email']) ? strval(trim($_POST['email'])) : '';
        $code = mt_rand(1000, 9999);

        // Записываем в лог для отладки
        $logFile = $_ENV['LOG_FILE_NAME'] ?? 'app.log';
        file_put_contents(
            $logFile,
            sprintf(
                "[%s] [AUTH] Попытка отправки кода %s на %s\n",
                date('Y-m-d H:i:s'),
                $code,
                $email
            ),
            FILE_APPEND
        );

        $result = (new MailController())->onMail($email, 'Код верификации', "Ваш код верификации: $code");

        if ($result) {
            echo json_encode(['success' => true, 'code' => $code]);
        } else {
            // Получаем ошибки из Message
            $notification = \App\Models\Network\Message::controll();
            $errorMessage = !empty($notification['message']) ? $notification['message'] : 'Ошибка отправки почты. Проверьте настройки SMTP.';

            echo json_encode(['success' => false, 'error' => $errorMessage]);
        }
    }

    /**
     * Summary of FindUser
     * @return bool
     * @description Проверка на существование поользователя
     */
    public function isFindUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            echo json_encode(false);

        $email = isset($_POST['email']) && !empty($_POST['email']) ? trim($_POST['email']) : '';
        try {
            $find = (new User())->getUser('email', $email);
            echo json_encode($find ? true : false);
            return;
        } catch (\Exception $e) {
            echo json_encode(false);
            return;
        }
    }

    // ======= LOGIN =============

    /**
     * Summary of onLogin
     * @return bool
     * @description авторизауия с установкой session
     */
    public function onLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            echo json_encode(false);

        $email = isset($_POST['email']) ? (string) trim($_POST['email']) : '';
        try {
            $user = Database::send('SELECT uniID FROM qwees_users WHERE email = ?', [$email]);
            Session::init(null);
            Session::init('user', $user[0]);
            Session::init('lang', 'ru');
            Network::onRedirect('/');
        } catch (\Exception $e) {
            return;
        }
    }

    // ======= REGIST =============

    /**
     * Регистрация пользователя
     */
    public static function registerUser(array $userData): array
    {
        // Проверка обязательных полей
        if (empty($userData['first_name']) || empty($userData['email'])) {
            return ['success' => false, 'message' => 'Заполните имя и email'];
        }

        // Проверка существования email
        $existing = Database::send("SELECT id FROM qwees_users WHERE email = ? LIMIT 1", [$userData['email']]);
        if (!empty($existing)) {
            return ['success' => false, 'message' => 'Email уже существует'];
        }

        // Генерация данных
        $uniID = $userData['uniID'] ?? uniqid('qws');
        $myreferCode = $userData['myrefer'] ?? (new Refer())->generationRefer();

        // Добавление пользователя
        try {
            Database::send("INSERT INTO qwees_users (first_name, last_name, email, uniID, myrefer) VALUES (?, ?, ?, ?, ?)", [
                $userData['first_name'],
                $userData['last_name'] ?? '',
                $userData['email'],
                $uniID,
                $myreferCode
            ]);

            // Подписка (если указана)
            if (!empty($userData['subscription']) && !empty($userData['duration_days'])) {
                $plan = Database::send("SELECT price FROM qwees_price WHERE name = ? LIMIT 1", [$userData['subscription']]);
                $planPrice = $plan[0]['price'] ?? 0;
                $endDate = date('Y-m-d H:i:s', strtotime('+' . $userData['duration_days'] . ' days'));

                Database::send("INSERT INTO qwees_subscriptions (uniID, status, subscription, amount, count_days, date_end) VALUES (?, ?, ?, ?, ?, ?)", [
                    $uniID,
                    'on',
                    $userData['subscription'],
                    $planPrice,
                    $userData['duration_days'],
                    $endDate
                ]);
            }

            // Реферальный код
            $pendingReferCode = Session::init('pending_refer_code') ?? '';
            if (!empty($pendingReferCode)) {
                (new Refer())->setRefer($uniID, $pendingReferCode, true);
                Session::init('pending_refer_code', null);
            }

            // Логирование
            $logFile = $_ENV['LOG_FILE_NAME'] ?? 'app.log';
            file_put_contents($logFile, sprintf(
                "[%s] REGISTER: %s (%s)\n",
                date('Y-m-d H:i:s'),
                $userData['first_name'] . ' ' . ($userData['last_name'] ?? ''),
                $userData['email']
            ), FILE_APPEND);

            return ['success' => true, 'uniID' => $uniID, 'message' => 'Успешно'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Регистрация пользователя
     */
    public function onRegist(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(false);
            return;
        }

        $userData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? '')
        ];

        $result = self::registerUser($userData);

        if ($result['success']) {
            self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
        } else {
            self::onRedirect($_ENV['REDIRECT_REG_UNSIGN_USER']);
        }
    }

    /**
     * Выход из системы
     * @return void
     */
    public static function onLogout(): void
    {
        Session::init(null);
        self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
        exit();
    }
}