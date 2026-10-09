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
use DateTime, DateTimeZone;
class User extends Network
{
  public string $table_name;

  public function __construct()
  {
    $this->table_name = isset($_ENV['DB_USERS_TABLE']) ? $_ENV['DB_USERS_TABLE'] : 'qwees_users';
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
  public function getUser(string $type, string $value): array|bool
  {
    $result = null;
    try {
      switch ($type) {
        case 'id':
        case 'index':
        case 'identification':
          $result = Database::send("SELECT * FROM " . $this->table_name . " WHERE id = ?", [$value]);
          break;
        case 'uniID':
          $result = Database::send("SELECT * FROM " . $this->table_name . " WHERE uniID = ?", [$value]);
          break;
        case 'username':
        case 'name':
        case 'nickname':
          $result = Database::send("SELECT * FROM " . $this->table_name . " WHERE username = ?", [$value]);
          break;
        case 'email':
        case 'mail':
          $result = Database::send("SELECT * FROM " . $this->table_name . " WHERE email = ?", [$value]);
          break;
      }
      return is_array($result) && !empty($result) ? $result[0] : false;
    } catch (\PDOException $e) {
      error_log("Ошибка при получении пользователя: " . $e->getMessage());
      return false;
    }
  }



}
