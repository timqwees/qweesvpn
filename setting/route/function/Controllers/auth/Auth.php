<?php
declare(strict_types=1);
namespace Setting\Route\Function\Controllers\Auth;

use App\Config\Database;
use App\Config\Session;
use App\Controllers\MailController;
use App\Models\Network\Network;
use App\Models\User\User;

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
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
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
   * Summary of onRegist
   * @return bool
   * @description регистрирую пользователя
   */
  public function onRegist(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
      echo json_encode(false);

    // входные данные
    $first_name = isset($_POST['first_name']) ? (string) trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? (string) trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? (string) trim($_POST['email']) : '';
    $uniID = uniqid('qws');//по умолчанию more_entropy (false) = генераци 13 символов
    try {
      Database::send("INSERT INTO qwees_users (first_name, last_name, email, uniID) VALUES (?, ?, ?, ?)", [
        $first_name,
        $last_name,
        $email,
        $uniID
      ]);
      self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
    } catch (\PDOException $e) {
      self::onRedirect($_ENV['REDIRECT_REG_UNSIGN_USER']);
    }
  }

  /**
   * Summary of logout
   * @return void
   */
  public static function logout(): void
  {
    Session::init(null);
    self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
    exit();
  }
}
