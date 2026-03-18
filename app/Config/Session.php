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

namespace App\Config;

class Session
{
  private static $cookieName = "__qweescore_cookie";
  private static $data = null;
  private static $lifetime = 86400; // 1 day

  /**
   * Универсальный метод для управления cookie-сессией.
   * ---
   * ### Все варианты использования:
   * 1. Получить все значения:                   *```Session::init()```*
   * 2. Получить значение по ключу:              *```Session::init('key')```*
   * 3. Получить значения по нескольким ключам:  *```Session::init(['key1', 'key2'])```*
   * 4. Установить значение:                     *```Session::init('key', 'value')```*
   * 5. Установить несколько значений:           *```foreach ($arr as $k=>$v) Session::init($k, $v)```*
   * 6. Удалить ключ:                            *```Session::init('key', null)```*
   * 7. Удалить несколько ключей:                *```Session::init(['key1', 'key2'], null)```*
   * 8. Очистить всю сессию:                     *```Session::init(null)```*
   *---
   * @param null|string|array $name Ключ, массив ключей или null для полной очистки
   * @param mixed $value Значение (null - удаление)
   * @return mixed
   */
  public static function init($name = '', $value = null)
  {
    // Загружаем данные из cookie
    if (self::$data === null) {
      self::$data = [];
      if (!empty($_COOKIE[self::$cookieName])) {
        $tmp = json_decode($_COOKIE[self::$cookieName], true);
        if (is_array($tmp)) {
          self::$data = $tmp;
        }
      }
    }

    // === Удаление всей сессии ===
    if ($name === null) {
      self::$data = [];
      self::rewrite(true); // удаление cookie
      return true;
    }

    // === Получение всех данных ===
    if ($name === '' || $name === false || empty($name)) {
      return self::$data;
    }

    // === Удаление нескольких ключей ===
    if (is_array($name) && $value === null) {
      foreach ($name as $key) {
        unset(self::$data[$key]);
      }
      self::rewrite();
      return true;
    }

    // === Получение нескольких значений ===
    if (is_array($name) && $value !== null) {
      $result = [];
      foreach ($name as $key) {
        $result[$key] = self::$data[$key] ?? null;
      }
      return $result;
    }

    // === Получение одного значения ===
    if ($value === null && isset(self::$data[$name])) {
      return self::$data[$name];
    }

    // === Удаление одного ключа ===
    if ($value === null) {
      unset(self::$data[$name]);
      self::rewrite();
      return true;
    }

    // === Установка значения ===
    self::$data[$name] = $value;
    self::rewrite();
    return true;
  }

  /**
   * Записывает cookie
   * @param bool $remove удалить cookie
   */
  private static function rewrite($all_remove = false)
  {
    if ($all_remove) {
      setcookie(
        self::$cookieName,
        '',
        time() - 3600,
        '/',
        '',
        false,
        true
      );
      return true;
    }

    setcookie(
      self::$cookieName,
      json_encode(self::$data, JSON_UNESCAPED_UNICODE),
      time() + self::$lifetime,
      '/',
      '',
      false,
      true
    );

    return true;
  }
}
