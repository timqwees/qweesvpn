<?php
/**
 *
 *  _____                                                                                _____
 * ( ___ )                                                                              ( ___ )
 *  |   |~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~|   |
 *  |   |                                                                                |   |
 *  |   |                                                                                |   |
 *  |   |    ________  ___       __   _______   _______   ________                       |   |
 *  |   |   |\   __  \|\  \     |\  \|\  ___ \ |\  ___ \ |\   ____\                      |   |
 *  |   |   \ \  \|\  \ \  \    \ \  \ \   __/|\ \   __/|\ \  \___|_                     |   |
 *  |   |    \ \  \\\  \ \  \  __\ \  \ \  \_|/_\ \  \_|/_\ \_____  \                    |   |
 *  |   |     \ \  \\\  \ \  \|\__\_\  \ \  \_|\ \ \  \_|\ \|____|\  \                   |   |
 *  |   |      \ \_____  \ \____________\ \_______\ \_______\____\_\  \                  |   |
 *  |   |       \|___| \__\|____________|\|_______|\|_______|\_________\                 |   |
 *  |   |             \|__|                                 \|_________|                 |   |
 *  |   |    ________  ________  ________  _______   ________  ________  ________        |   |
 *  |   |   |\   ____\|\   __  \|\   __  \|\  ___ \ |\   __  \|\   __  \|\   __  \       |   |
 *  |   |   \ \  \___|\ \  \|\  \ \  \|\  \ \   __/|\ \  \|\  \ \  \|\  \ \  \|\  \      |   |
 *  |   |    \ \  \    \ \  \\\  \ \   _  _\ \  \_|/_\ \   ____\ \   _  _\ \  \\\  \     |   |
 *  |   |     \ \  \____\ \  \\\  \ \  \\  \\ \  \_|\ \ \  \___|\ \  \\  \\ \  \\\  \    |   |
 *  |   |      \ \_______\ \_______\ \__\\ _\\ \_______\ \__\    \ \__\\ _\\ \_______\   |   |
 *  |   |       \|_______|\|_______|\|__|\|__|\|_______|\|__|     \|__|\|__|\|_______|   |   |
 *  |   |                                                                                |   |
 *  |   |                                                                                |   |
 *  |___|~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~|___|
 * (_____)                                                                              (_____)
 *
 * Эта программа является свободным программным обеспечением: вы можете распространять ее и/или модифицировать
 * в соответствии с условиями GNU General Public License, опубликованными
 * Фондом свободного программного обеспечения (Free Software Foundation), либо в версии 3 Лицензии, либо (по вашему выбору) в любой более поздней версии.
 *
 *
 * @license GPL-3.0-or-later (см. файл LICENSE.txt)
 * @author TimQwees
 * @link https://github.com/TimQwees/Qwees_CorePro
 *
 *
 */

namespace App\Controllers;

use App\Config\Database;
use App\Config\Session;
use App\Models\Network\Network;
use App\Models\User\User;
use App\Models\Network\Message;

class AuthController extends Network
{
  /**
   * ### Вход пользователя в систему
   *
   * Осуществляет аутентификацию пользователя по email или имени пользователя и паролю.
   *
   * ---
   * **Функционал:**
   * - Проверяет метод запроса (должен быть POST)
   * - Поддерживает вход как по email, так и по логину (username)
   * - Валидирует обязательные поля
   * - Выполняет сравнение пароля с хэшем из БД
   * - Устанавливает сессию пользователя при успешном входе
   * - Сообщает об успехе или ошибках через сообщения
   * ---
   *
   * **Примеры использования:**
   * - *Вход пользователя:*
   *   ```php
   *   $this->onLogin();
   *   ```
   *
   * __Возвращает:__
   * bool — true при успешном входе, false — при ошибке
   *
   * _Сообщение об ошибке или успешном входе автоматически выставляется через Message_
   */
  public function onLogin(): bool
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      Message::set('error', 'Некорректный метод запроса');
      self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
      return false;
    }

    $login = '';
    if (isset($_POST['mail']) && $_POST['mail'] !== '') {
      $login = trim($_POST['mail']);
    } elseif (isset($_POST['username']) && $_POST['username'] !== '') {
      $login = trim($_POST['username']);
    }

    $password = isset($_POST['password']) ? (string) trim($_POST['password']) : '';

    if (empty($login) || empty($password)) {
      Message::set('error', 'Пожалуйста, заполните все поля');
      self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
      return false;
    }

    $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
    $user = null;
    try {
      if ($isEmail) {//email valid => have ...@a.b
        $user = (new User())->getUser('mail', $login);
      } else {
        $user = (new User())->getUser('username', $login);
      }

      if ($user && password_verify($password, $user['password'] ?? '')) {
        Session::init('user', [
          'id' => $user['id'],
          'username' => $user['username'],
          'mail' => $user['mail']
        ]);
        Message::set('success', 'Вы успешно вошли в систему');
        return self::onRedirect($_ENV['REDIRECT_SIGN_USER']);
      } else {
        Message::set('error', $isEmail ? 'Неверная почта или пароль' : 'Неверное имя пользователя или пароль');
        return self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
      }
    } catch (\Exception $e) {
      Message::set('error', 'Произошла ошибка при входе в систему: ' . $e->getMessage());
      return self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
    }
  }

  /**
   * ### Регистрация нового пользователя
   *
   * Выполняет регистрацию нового пользователя с валидацией данных.
   *
   * ---
   * **Возможности:**
   * - Принимает данные методом POST: email, имя пользователя, пароль, группа
   * - Проверяет уникальность email и имени пользователя
   * - Проверяет минимальные требования к длине username и пароля
   * - Хэширует пароль перед записью в базу данных
   * - Устанавливает уведомления об ошибках и успехе через сообщения
   * ---
   *
   * **Пример:**
   * ```php
   * $this->onRegist();
   * ```
   *
   * __Возвращает:__
   * mixed — перенаправляет пользователя и завершает выполнение
   *
   * _При возникновении ошибок перенаправляет обратно на форму регистрации_
   */
  public function onRegist()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      Message::set('error', 'Некорректный метод запроса');
      self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
      return false;
    }

    $mail = isset($_POST['mail']) ? (string) trim($_POST['mail']) : '';
    $username = isset($_POST['username']) ? (string) trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Валидация
    if (empty($username) || empty($password) || empty($mail)) {
      Message::set('error', 'Пожалуйста, заполните все поля');
      return self::onRedirect($_ENV['REDIRECT_REG_UNSIGN_USER']);
    }

    if (strlen($username) < 3) {
      Message::set('error', 'Имя пользователя должно содержать минимум 3 символа');
      return self::onRedirect($_ENV['REDIRECT_REG_UNSIGN_USER']);
    }

    if (strlen($password) < 6) {
      Message::set('error', 'Пароль должен содержать минимум 6 символов');
      return self::onRedirect($_ENV['REDIRECT_REG_UNSIGN_USER']);
    }

    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
      Message::set('error', 'Неверный формат почты');
      return self::onRedirect($_ENV['REDIRECT_REG_UNSIGN_USER']);
    }

    try {
      $result_name = Database::send("SELECT COUNT(*) as count FROM " . (new User())->table_name . " WHERE username = ?", [$username]);
      if (is_array($result_name) && $result_name[0]['count'] > 0) {
        Message::set('error', "Пользователь с именем: $username уже существует");
        self::onRedirect($_ENV['REDIRECT_REG_UNSIGN_USER']);
        return false;
      }

      $result_email = Database::send("SELECT COUNT(*) as count FROM " . (new User())->table_name . " WHERE mail = ?", [$mail]);
      if (is_array($result_email) && $result_email[0]['count'] > 0) {
        Message::set('error', "Почта: $mail уже существует");
        return self::onRedirect($_ENV['REDIRECT_REG_UNSIGN_USER']);
      }

      Database::send("INSERT INTO " . (new User())->table_name . " (username, mail, password) VALUES (?, ?, ?)", [
        $username,
        $mail,
        password_hash($password, PASSWORD_DEFAULT)
      ]);
      Message::set('success', "Регистрация успешна! $username, Теперь вы можете войти");
      return self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
    } catch (\PDOException $e) {
      Message::set('error', 'Ошибка при регистрации: ' . $e->getMessage());
      return self::onRedirect($_ENV['REDIRECT_REG_UNSIGN_USER']);
    }
  }

  /**
   * #### Выход пользователя из системы
   *
   * Очищает пользовательскую сессию и выводит сообщение об успешном выходе.
   *
   * ---
   * **Основные действия:**
   * - Сброс сессии пользователя (`Session::init(null)`)
   * - Установка уведомления Message о выходе
   * - Перенаправление на страницу входа
   *
   * **Пример:**
   * ```php
   * $this->logout();
   * ```
   *
   * __Возвращает:__
   * mixed (результат редиректа)
   */
  public function logout()
  {
    Session::init(null);
    Message::set('success', 'Вы успешно вышли из системы');
    return self::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER']);
  }
}
