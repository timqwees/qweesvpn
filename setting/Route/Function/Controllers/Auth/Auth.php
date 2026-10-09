<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Auth;

use App\Config\Database;
use App\Config\Session;
use App\Config\Throttle;
use App\Controllers\MailController;
use App\Models\Network\Message;
use App\Models\Network\Network;
use App\Models\User\User;
use Setting\Route\Function\Controllers\Refer\Refer;
use Setting\Route\Function\Controllers\Kassa\PriceConfig;
use DateTime, DateTimeZone;

class Auth extends Network
{
    /** Ключи пользовательской сессии (админскую не трогаем). */
    private const USER_KEYS = ['user', 'kassa', 'pending_refer_code', 'notification'];

    /** Код живёт 15 минут, помним 2 последних (повторный запрос не убивает первый). */
    private const MAIL_CODE_TTL = 900;
    private const MAIL_CODES_KEPT = 2;

    // Страж страниц: нет сессии или юзер удалён — на вход.
    public static function auth(): void
    {
        $uniID = (string) (Session::init('user')['uniID'] ?? '');
        if ($uniID === '') {
            self::onRedirect('/auth/login');
            return;
        }
        $exists = Database::send('SELECT id FROM qwees_users WHERE uniID = ? LIMIT 1', [$uniID]);
        if (!\is_array($exists) || $exists === []) {
            Session::init(self::USER_KEYS, null);
            self::onRedirect('/auth/login');
        }
    }

    // Почта: отправить код. POST /auth/mail.
    public function onMail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            return;
        }
        $email = self::post('email');
        if (!Throttle::hit('mail:' . Throttle::ip(), 8, 900)) {
            echo json_encode(['success' => false, 'error' => 'Слишком много запросов кода. Подождите 15 минут.']);
            return;
        }
        $code = mt_rand(1000, 9999);
        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
            \sprintf("[%s] [АВТОРИЗАЦИЯ - КОД] Код отправлен **** на %s\n", date('Y-m-d H:i:s'), $email),
            FILE_APPEND
        );
        if (!(new MailController())->onMail($email, 'Код верификации', "Ваш код верификации: $code")) {
            $notification = Message::controll();
            $error = !empty($notification['message']) ? $notification['message'] : 'Ошибка отправки почты. Проверьте настройки SMTP.';
            echo json_encode(['success' => false, 'error' => $error]);
            return;
        }
        self::rememberCode($email, (string) $code);
        echo json_encode(['success' => true, 'code' => $code]);
    }

    // Есть ли такой email. POST /auth/find.
    public function isFindUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(false);
            return;
        }
        try {
            echo json_encode((bool) (new User())->getUser('email', self::post('email')));
        } catch (\Exception) {
            echo json_encode(false);
        }
    }

    // Вход. POST /auth/login.
    public function onLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(false);
            return;
        }
        $email = self::post('email');
        if (!Throttle::hit('login:' . Throttle::ip(), 10, 900)) {
            self::deny('Слишком много попыток входа. Подождите 15 минут.', '/auth/login');
            return;
        }
        if (!self::verifyMailCode($email, self::post('verefy'))) {
            if (self::authedUniID() !== '') {
                self::onRedirect('/'); // двойной сабмит: первый уже впустил
                return;
            }
            self::deny('Неверный или просроченный код. Запросите код ещё раз.', '/auth/login');
            return;
        }
        try {
            $user = Database::send('SELECT uniID FROM qwees_users WHERE email = ?', [$email]);
        } catch (\Exception) {
            self::deny('Ошибка сервера, попробуйте позже.', '/auth/login');
            return;
        }
        if (empty($user)) {
            self::deny('Пользователь с таким email не найден. Зарегистрируйтесь.', '/auth/login');
            return;
        }
        self::establishUserSession((string) $user[0]['uniID']);
        self::onRedirect('/');
    }

    // Регистрация. POST /auth/regist.
    public function onRegist(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(false);
            return;
        }
        $userData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
        ];
        if (!Throttle::hit('login:' . Throttle::ip(), 10, 900)) {
            self::deny('Слишком много попыток. Подождите 15 минут.', '/auth/regist');
            return;
        }
        if (!self::verifyMailCode($userData['email'], self::post('verefy'))) {
            if (self::authedUniID() !== '') {
                self::onRedirect($_ENV['REDIRECT_SIGN_USER']); // двойной сабмит: первый уже впустил
                return;
            }
            self::deny('Неверный или просроченный код. Запросите код ещё раз.', '/auth/regist');
            return;
        }
        $result = (array) self::registerUser($userData);
        if (!$result['success']) {
            self::deny((string) ($result['message'] ?? 'Не удалось зарегистрироваться'), '/auth/regist');
            return;
        }
        self::establishUserSession((string) $result['uniID']); // сразу авторизован
        self::onRedirect($_ENV['REDIRECT_SIGN_USER']);
    }

    // Создание юзера в БД: юзер + подписка одним коммитом.
    public static function registerUser(array $userData): array
    {
        if (empty($userData['first_name']) || empty($userData['email'])) {
            return ['success' => false, 'message' => 'Заполните имя и email'];
        }
        $exists = Database::send('SELECT id FROM qwees_users WHERE email = ? LIMIT 1', [$userData['email']]);
        if (!empty($exists)) {
            return ['success' => false, 'message' => 'Email уже существует'];
        }
        $uniID = $userData['uniID'] ?? uniqid('qws');
        $myreferCode = $userData['myrefer'] ?? (new Refer())->generateCode();
        try {
            $saved = Database::transaction(function () use ($userData, $uniID, $myreferCode) {
                if (Database::send('INSERT INTO qwees_users (first_name, last_name, email, uniID, myrefer) VALUES (?, ?, ?, ?, ?)', [
                    $userData['first_name'], $userData['last_name'] ?? '', $userData['email'], $uniID, $myreferCode,
                ]) === false) return false;
                if (!empty($userData['subscription']) && !empty($userData['duration_days'])) {
                    $price = PriceConfig::getPrices()[1][$userData['subscription']] ?? 0;
                    $expiry = (new DateTime('now', new DateTimeZone('Europe/Moscow')))
                        ->modify('+' . (int) $userData['duration_days'] . ' days')->getTimestamp() * 1000;
                    if (Database::send('INSERT INTO qwees_subscriptions (uniID, status, subscription, amount, count_days, expiry) VALUES (?, ?, ?, ?, ?, ?)', [
                        $uniID, 'on', $userData['subscription'], $price, $userData['duration_days'], $expiry,
                    ]) === false) return false;
                }
                return true;
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
        if (!$saved) {
            return ['success' => false, 'message' => 'Ошибка записи в базу данных'];
        }
        $pendingRefer = Session::init('pending_refer_code') ?? '';
        if ($pendingRefer !== '') {
            (new Refer())->setRefer($uniID, $pendingRefer);
            Session::init('pending_refer_code', null);
        }
        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'app.log',
            \sprintf("[%s] [РЕГИСТРАЦИЯ - НОВЫЙ ПОЛЬЗОВАТЕЛЬ]: %s (%s)\n", date('Y-m-d H:i:s'), $userData['first_name'] . ' ' . ($userData['last_name'] ?? ''), $userData['email']),
            FILE_APPEND
        );
        return ['success' => true, 'uniID' => $uniID, 'message' => 'Успешно'];
    }

    // Выход (админка и язык живы).
    public static function onLogout(): void
    {
        Session::init(self::USER_KEYS, null);
        self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
        exit();
    }

    // --- мелочь ---

    private static function post(string $key): string
    {
        return trim((string) ($_POST[$key] ?? ''));
    }

    private static function digits(string $s): string
    {
        return (string) preg_replace('/\D/', '', $s);
    }

    private static function deny(string $msg, string $url): void
    {
        Message::set('error', $msg);
        self::onRedirect($url);
    }

    private static function authedUniID(): string
    {
        return (string) (Session::init('user')['uniID'] ?? '');
    }

    // Помним N последних кодов этого email (при смене email — сброс).
    private static function rememberCode(string $email, string $code): void
    {
        $prev = Session::init('mail_code');
        $codes = [];
        if (\is_array($prev) && ($prev['email'] ?? '') === $email) {
            $codes = \is_array($prev['codes'] ?? null) ? $prev['codes'] : (isset($prev['code']) ? [['code' => $prev['code'], 'at' => $prev['at'] ?? 0]] : []);
        }
        $codes[] = ['code' => $code, 'at' => time()];
        $codes = \array_slice(array_values(array_filter($codes, fn($c) => \is_array($c) && ($c['code'] ?? '') !== '')), -self::MAIL_CODES_KEPT);
        Session::init('mail_code', ['email' => $email, 'codes' => $codes, 'at' => time()]);
    }

    // Код из письма: любой из помнимых, в пределах TTL. Совпал — гасим.
    private static function verifyMailCode(string $email, string $code): bool
    {
        $saved = Session::init('mail_code');
        if (!\is_array($saved) || ($saved['email'] ?? '') !== $email || $email === '') return false;
        $code = self::digits($code);
        if ($code === '') return false;
        $codes = $saved['codes'] ?? null;
        if (!\is_array($codes)) $codes = isset($saved['code']) ? [['code' => $saved['code'], 'at' => $saved['at'] ?? 0]] : [];
        foreach ($codes as $c) {
            if (!\is_array($c)) continue;
            $one = self::digits((string) ($c['code'] ?? ''));
            if ($one !== '' && hash_equals($one, $code) && (time() - (int) ($c['at'] ?? 0)) <= self::MAIL_CODE_TTL) {
                Session::init('mail_code', null);
                return true;
            }
        }
        return false;
    }

    // Одна установка сессии (админку не трогаем).
    private static function establishUserSession(string $uniID): void
    {
        Session::init(self::USER_KEYS, null);
        Session::init('user', ['uniID' => $uniID]);
        Session::init('lang', 'ru');
    }
}
