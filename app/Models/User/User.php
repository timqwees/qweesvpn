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

namespace App\Models\User;

use App\Config\Database;
use App\Models\Network\Network;
use App\Models\Network\Message;

class User extends Network
{
  public $table_name;

  public function __construct()
  {
    $this->table_name = isset($_ENV['DB_USERS_TABLE']) ? $_ENV['DB_USERS_TABLE'] : 'users';
  }

  /**
   * 📦 **Получение данных пользователя по типу идентификатора**
   *
   * ---
   *
   * **Возможности использования:**
   *
   * 1. **Поиск по ID**
   *    _Получает данные пользователя по уникальному идентификатору:_
   *    ```php
   *    $this->getUser('id', 1);
   *    ```
   *
   * 2. **Поиск по имени пользователя**
   *    _Возвращает информацию о пользователе по username:_
   *    ```php
   *    $this->getUser('username', 'admin');
   *    ```
   *
   * 3. **Поиск по e-mail**
   *    _Получить по эл. почте:_
   *    ```php
   *    $this->getUser('email', 'admin@example.com');
   *    ```
   *
   * ---
   *
   * **Параметры:**
   * - `string $type` &mdash; _Тип идентификатора_ (`id`, `username`, `email` и др.)
   * - `int|string $value` &mdash; _Значение для поиска (например, 1, 'admin', 'email@example.com')_
   *
   * **Возвращает:**
   * - `array` — если пользователь найден
   * - `false` — если не найден или ошибка
   *
   * _Позволяет гибко получать пользователя по разным уникальным полям._
   */
  public function getUser(string $type, $value): array|bool
  {
    try {
      switch ($type) {
        case 'id':
        case 'index':
        case 'identification':
          $result = Database::send("SELECT * FROM " . $this->table_name . " WHERE id = ?", [$value]);
          break;
        case 'username':
        case 'name':
        case 'nickname':
          $result = Database::send("SELECT * FROM " . $this->table_name . " WHERE username = ?", [$value]);
          break;
        case 'email':
        case 'mail':
          $result = Database::send("SELECT * FROM " . $this->table_name . " WHERE mail = ?", [$value]);
          break;
      }
      return is_array($result) && !empty($result) ? $result[0] : false;
    } catch (\PDOException $e) {
      error_log("Ошибка при получении пользователя: " . $e->getMessage());
      return false;
    }
  }

  /**
   * 🛠️ **Обновление профиля пользователя**
   *
   * ---
   *
   * **Как использовать:**
   *
   * 1. _Обновить несколько полей пользователя по ID:_
   *    ```php
   *    $this->onUpdateProfile('users', ['username' => 'admin', 'email' => 'timqwees@gmail.com'], 1);
   *    ```
   *
   * ---
   *
   * **Параметры:**
   * - `string $tableName` — _Имя таблицы, где обновлять данные_
   * - `array $new_data` — _Массив новых данных (`['поле' => 'значение', ...]`)_
   * - `int $userId` — _ID пользователя_
   *
   * **Результат:**
   * - `true` — если успешно
   * - `false` — если ошибка
   *
   * _Удобен для массового или точечного изменения профиля пользователя._
   */
  public function onUpdateProfile(string $tableName, array $new_data, int $userId)
  {
    try {

      foreach ($new_data as $column => $value) {
        Network::onColumnExists($column, $tableName);
      }

      $setColumns = [];//column
      $setParam = [];//value

      foreach ($new_data as $column => $value) {
        $setColumns[] = "`$column` = ?";
        $setParam[] = $value;
      }

      // add userId into last list
      $setParam[] = $userId;

      $result = Database::send("UPDATE " . $this->table_name . " SET " . implode(', ', $setColumns) . " WHERE id = ?", [$setParam]);

      if ($result) {
        Message::set('success', 'Профиль успешно обновлен');
        return true;
      }
      Message::set('error', 'Ошибка при обновлении профиля');
      return false;
    } catch (\PDOException $e) {
      Message::set('error', 'Ошибка при обновлении профиля: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * 📤 **Загрузка файла с сохранением пути**
   *
   * ---
   *
   * **Сценарии использования:**
   *
   * - _Загрузка аватара пользователя:_
   *   ```php
   *   $this->uploadFile($_FILES['file'], '10', 'avatar10');
   *   ```
   * - _Указать только id пользователя:_
   *   ```php
   *   $this->uploadFile($_FILES['file'], 'id пользователя');
   *   ```
   *
   * ---
   *
   * **Параметры:**
   * - `array $file` — _Массив из $_FILES_
   * - `string $prefix` — _Префикс для имени_ (обычно — id)
   * - `string|null $customName` — _Свое имя файла (без расширения)_
   *
   * **Возвращает:**
   * - `string` — путь `avatar/имя_файла` при успехе
   * - `false` — если ошибка
   *
   * _Защищает от опасных расширений, ограничивает размер и создает директории при необходимости._
   */
  function uploadFile(array $file, string $prefix = '', ?string $customName = null): string|false
  {
    try {
      // Проверяем тип файла
      $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
      if (!in_array($file['type'], $allowedTypes)) {
        throw new \Exception('Недопустимый тип файла. Разрешены только JPG, PNG и GIF.');
      }

      // Проверяем размер файла (максимум 5MB)
      if ($file['size'] > 5 * 1024 * 1024) {
        throw new \Exception('Размер файла превышает 5MB.');
      }

      $uploadPath = __DIR__ . '/../../../public/avatar';

      if (!is_dir($uploadPath)) {
        if (!mkdir($uploadPath, 0777, true)) {
          throw new \Exception('Не удалось создать директорию для загрузки.');
        }
      }

      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

      // Формируем имя файла
      if ($customName !== null) {
        // Очищаем пользовательское имя от небезопасных символов
        $customName = preg_replace('/[^a-zA-Z0-9_-]/', '', $customName);
        $fileName = $customName . ".$ext";
      } else {
        $fileName = $prefix . '_' . time() . ".$ext";
      }

      $fullPath = "$uploadPath/$fileName";

      // Проверяем, существует ли файл с таким именем
      if (file_exists($fullPath)) {
        // Добавляем временную метку к имени файла
        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . time() . ".$ext";
        $fullPath = "$uploadPath/$fileName";
      }

      if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        throw new \Exception('Ошибка при загрузке файла на сервер.');
      }

      // Возвращаем относительный путь для сохранения в БД
      return "avatar/$fileName";
    } catch (\Exception $e) {
      error_log("Ошибка при загрузке файла: " . $e->getMessage());
      return false;
    }
  }

  // /**
  //  * Проверяет сессию пользователя по его ID
  //  *
  //  * @param int $index ID пользователя
  //  * @return bool true если сессия активна, false если нет или при ошибке
  //  *
  //  * @example $this->onSessionUser(0);
  //  * @description проверяет сессию пользователя с указанным ID / check user session by user ID
  //  */
  // public function onSessionUser(int $index)
  // {
  //   try {
  //     if ($index === False) {
  //       Network::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER'] ?: '/');
  //       session_destroy();
  //       return false;
  //     }

  //     $result = Database::send("SELECT `session` FROM " . $this->table_name . " WHERE id = ?", [$index]);

  //     if ($result['session'] === 'off') {//сессия отключена / вышел с аккаунта
  //       Network::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER'] ?: '/');
  //       session_destroy();
  //       return false;
  //     } else {
  //       Network::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER'] ?: '/');
  //       session_destroy();
  //       return true;
  //     }
  //   } catch (\PDOException $e) {
  //     error_log("Ошибка при проверке пользователя: " . $e->getMessage());
  //     Network::onRedirect($_ENV['REDIRECT_LOG_UNSIGN_USER'] ?: '/');
  //     session_destroy();
  //     return false;
  //   }
  // }

  // /**
  //  * Обновляет статус сессии пользователя
  //  *
  //  * @param string $status Новый статус сессии ('on' или 'off')
  //  * @param int $userId ID пользователя
  //  * @return bool true в случае успеха, false в случае ошибки
  //  *
  //  * @example $this->updateSessionStatus('on', 1);
  //  * @description обновляет статус сессии пользователя с указанным ID / update user session status by user ID
  //  */
  // public function updateSessionStatus(string $status, int $userId)
  // {
  //   try {
  //     Database::send("UPDATE " . $this->table_name . " SET `session` = ? WHERE id = ?", [
  //       $status,
  //       $userId
  //     ]);
  //     return true;
  //   } catch (\PDOException $e) {
  //     error_log("Ошибка при обновлении статуса сессии: " . $e->getMessage());
  //     return false;
  //   }
  // }
}
