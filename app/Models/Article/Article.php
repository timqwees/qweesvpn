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

namespace App\Models\Article;

use App\Config\Database;
use App\Models\Network\Network;
use App\Models\Network\Message;
use App\Models\User\User;
use PDO;

class Article extends Network
{
  public $table_name;

  public function __construct()
  {
    $this->table_name = 'article';
  }

  /**
   * **Добавление новой статьи**
   *
   * ---
   *
   * _Универсальная функция для сохранения новой статьи в базу данных._
   *
   * **Примеры использования:**
   * - `addArticle('Заголовок', 'Контент', 1);` — _создаёт новую статью от пользователя с ID 1_
   *
   * **Параметры:**
   * - `string $title` — заголовок статьи
   * - `string $content` — содержимое
   * - `int $userId` — ID пользователя-автора
   *
   * **Возвращает:**
   * - `true` — при успехе
   * - `false` — если возникли ошибки
   *
   * _Добавляет новую статью с заданным заголовком, содержанием и автором._
   */
  public function addArticle(
    string $title,
    string $content,
    int $userId
  ) {
    $driver = Database::getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
    $timestamp = ($driver === 'sqlite') ? 'CURRENT_TIMESTAMP' : 'NOW()';
    try {
      $result = Database::send(
        "INSERT INTO {$this->table_name} (title, content, user_id, created_at) VALUES (?, ?, ?, $timestamp)",
        [$title, $content, $userId]
      );
      if ($result) {
        Message::set('success', 'Статья успешно создана');
        return true;
      }
      Message::set('error', 'Ошибка при создании статьи');
      return false;
    } catch (\PDOException $e) {
      Message::set('error', 'Ошибка при создании статьи: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * **Удаление статьи по ID**
   *
   * ---
   *
   * _Удаляет статью, если она принадлежит пользователю._
   *
   * **Пример:**
   * - `removeArticle(5, 1);` — _удалить статью с ID 5, если user_id == 1_
   *
   * **Параметры:**
   * - `int $id` — ID удаляемой статьи
   * - `int $userId` — ID пользователя-автора
   *
   * **Возврат:**
   * - `true` — при успешном удалении
   * - `false` — при ошибках либо если удаление невозможно
   */
  public function removeArticle(int $id, int $userId)
  {
    try {
      Database::getConnection()->beginTransaction();
      $result = Database::send(
        "DELETE FROM {$this->table_name} WHERE id = ? AND user_id = ?",
        [$id, $userId]
      );
      if ($result) {
        Database::getConnection()->commit();
        Message::set('success', 'Статья успешно удалена');
        return true;
      }
      Message::set('error', 'Ошибка при удалении статьи');
      Database::getConnection()->rollBack();
      return false;
    } catch (\PDOException $e) {
      if (Database::getConnection()->inTransaction()) {
        Database::getConnection()->rollBack();
      }
      Message::set('error', 'Ошибка при удалении статьи: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * **Получение конкретной статьи пользователя**
   *
   * ---
   *
   * _Возвращает данные одной статьи и имя её автора по двум идентификаторам._
   *
   * **Пример:**
   * - `getArticle(1, 5);` — _статья id=5, если принадлежит пользователю id=1_
   *
   * **Параметры:**
   * - `int $user_index` — ID пользователя
   * - `int $article_index` — ID статьи
   *
   * **Возвращает:**
   * - `array` с подробностями статьи и username
   * - или `false`, если статья не найдена/ошибка
   */
  public function getArticle(int $user_index, int $article_index)
  {
    try {
      $send = Database::send(
        "SELECT art.*, user.username FROM {$this->table_name} art JOIN " . (new User())->table_name . " user ON art.user_id = user.id WHERE art.user_id = ? AND art.id = ?",
        [$user_index, $article_index]
      );
      return $send ?: false;
    } catch (\PDOException $e) {
      Message::set('error', 'Ошибка при получении статьи: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * **Получение всех статей (с именами авторов)**
   *
   * ---
   *
   * _Возвращает список всех статей в системе, включая username авторов._
   *
   * **Использование:**
   * - `getArticleAll();`
   *
   * **Возвращает:**
   * - `array` — все статьи с именами пользователей
   * - `false` — при ошибке
   */
  public function getArticleAll()
  {
    try {
      $result = Database::send(
        "SELECT art.*, user.username FROM {$this->table_name} art JOIN " . (new User())->table_name . " user ON art.user_id = user.id ORDER BY art.id DESC"
      );
      return $result;
    } catch (\PDOException $e) {
      Message::set('error', 'Ошибка при получении статей: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * **Получение всех статей пользователя**
   *
   * ---
   *
   * _Достаёт все статьи для конкретного пользователя по ID._
   *
   * **Пример:**
   * - `getAllArticleById(1);`
   *
   * **Параметры:**
   * - `int $user_index` — автор статьи
   *
   * **Возвращает:**
   * - `array` — массив статей
   * - `false` — при ошибке
   */
  public function getAllArticleById(int $user_index)
  {
    try {
      $result = Database::send(
        "SELECT art.*, user.username FROM {$this->table_name} art JOIN " . (new User())->table_name . " user ON art.user_id = user.id WHERE art.user_id = ?",
        [$user_index]
      );
      return $result;
    } catch (\PDOException $e) {
      Message::set('error', 'Ошибка при получении статьи: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * **Получение собственных статей пользователя**
   *
   * ---
   *
   * _Список статей определённого пользователя, отсортированных по дате создания._
   *
   * **Пример вызова:**
   * - `getListMyArticle(1);`
   *
   * **Параметры:**
   * - `int $my_id` — ID пользователя-автора
   *
   * **Возвращает:**
   * - `array` — список статей
   * - `false` — при ошибках
   */
  public function getListMyArticle(int $my_id)
  {
    try {
      $result = Database::send(
        "SELECT art.*, user.username FROM {$this->table_name} art JOIN " . (new User())->table_name . " user ON art.user_id = user.id WHERE user.id = ? ORDER BY art.created_at DESC",
        [$my_id]
      );
      return $result;
    } catch (\PDOException $e) {
      Message::set('error', 'Ошибка при получении статей пользователя: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * **Получение одной своей статьи**
   *
   * ---
   *
   * _Данные о конкретной статье пользователя._
   *
   * **Пример:**
   * - `getMyArticle(1, 5);` — получить статью с ID 5 для пользователя 1
   *
   * **Параметры:**
   * - `int $user_index` — ID пользователя
   * - `int $article_index` — ID статьи
   *
   * **Возвращает:**
   * - `array` — если статья найдена
   * - `false` — иначе
   */
  public function getMyArticle(int $user_index, int $article_index)
  {
    try {
      $result = Database::send(
        "SELECT art.*, user.username FROM {$this->table_name} art JOIN " . (new User())->table_name . " user ON art.user_id = user.id WHERE user.id = ? AND art.id = ?",
        [$user_index, $article_index]
      );
      return $result;
    } catch (\PDOException $e) {
      Message::set('error', 'Ошибка при получении статей пользователя: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * **Обновление содержимого статьи**
   *
   * ---
   *
   * _Позволяет заменить содержание и заголовок для статьи конкретного автора._
   *
   * **Пример:**
   * - `onUpdateArticle('Новый заголовок', 'Новый контент', 5, 1);`
   *
   * **Параметры:**
   * - `string $title` — новый заголовок
   * - `string $content` — новое содержимое
   * - `int $articleId` — какой именно ID статьи
   * - `int $userId` — ID пользователя-автора
   *
   * **Возвращает:**
   * - `true` — если изменения успешно внесены
   * - `false` — если не удалось обновить данные
   */
  public function onUpdateArticle(string $title, string $content, int $articleId, int $userId)
  {
    try {
      $timestamp = (Database::getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') ? 'CURRENT_TIMESTAMP' : 'NOW()';
      $result = Database::send(
        "UPDATE {$this->table_name} SET title = ?, content = ?, created_at = $timestamp WHERE id = ? AND user_id = ?",
        [$title, $content, $articleId, $userId]
      );
      if ($result) {
        Message::set('success', 'Статья успешно обновлена');
        return true;
      }
      Message::set('error', 'Ошибка при обновлении статьи');
      return false;
    } catch (\PDOException $e) {
      Message::set('error', 'Ошибка при обновлении статьи: ' . $e->getMessage());
      return false;
    }
  }
}
