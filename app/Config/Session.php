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

use DateTime, DateTimeZone;

class Session
{
    // Пользовательская сессия — неделя. Админская — отдельно, сутки.
    // Ключ 'admin' всегда идёт в свой cookie и не пересекается с пользователем:
    // выход из профиля не выкидывает из админки и наоборот.
    private static $cookieName = "__qweescore_cookie";
    private static $adminCookieName = "__qweescore_admin";
    private static $data = null;
    private static $adminData = null;
    private static $lifetime = 604800; // 7 дней
    private static $adminLifetime = 86400; // 1 день

    /**
     * Универсальный метод для управления cookie-сессией.
     * ---
     * ### Все варианты использования:
     * 1. Получить все значения (без админа):       *```Session::init()```*
     * 2. Получить значение по ключу:              *```Session::init('key')```*
     * 3. Получить значения по нескольким ключам:  *```Session::init(['key1', 'key2'])```*
     * 4. Установить значение:                     *```Session::init('key', 'value')```*
     * 5. Установить несколько значений:           *```foreach ($arr as $k=>$v) Session::init($k, $v)```*
     * 6. Удалить ключ:                            *```Session::init('key', null)```*
     * 7. Удалить несколько ключей:                *```Session::init(['key1', 'key2'], null)```*
     * 8. Очистить всё (пользователь + админ):     *```Session::init(null)```*
     * Админка живёт отдельно:                     *```Session::init('admin')```*
     *---
     * @param null|string|array $name Ключ, массив ключей или null для полной очистки
     * @param mixed $value Значение (null - удаление)
     * @return mixed
     */
    public static function init($name = '', $value = null)
    {
        $hasValueArg = func_num_args() >= 2;
        $isAdmin = ($name === 'admin');

        self::loadStore($isAdmin);
        if ($isAdmin) {
            $data = &self::$adminData;
        } else {
            $data = &self::$data;
        }

        // === Удаление всей сессии (обе) ===
        if ($name === null) {
            self::$data = [];
            self::$adminData = [];
            self::rewrite(true, false);
            self::rewrite(true, true);
            return true;
        }

        // === Получение всех данных (без админа) ===
        if ($name === '' || $name === false || empty($name)) {
            return $data;
        }

        // === Удаление нескольких ключей ===
        if (is_array($name) && $value === null) {
            foreach ($name as $key) {
                unset($data[$key]);
            }
            self::rewrite(false, $isAdmin);
            return true;
        }

        // === Получение нескольких значений ===
        if (is_array($name) && !$hasValueArg) {
            $result = [];
            foreach ($name as $key) {
                $result[$key] = $data[$key] ?? null;
            }
            return $result;
        }

        // === Получение нескольких значений (обратная совместимость) ===
        if (is_array($name) && $value !== null) {
            $result = [];
            foreach ($name as $key) {
                $result[$key] = $data[$key] ?? null;
            }
            return $result;
        }

        // === Получение одного значения ===
        if (!$hasValueArg) {
            return $data[$name] ?? null;
        }

        // === Удаление одного ключа ===
        if ($value === null) {
            unset($data[$name]);
            self::rewrite(false, $isAdmin);
            return true;
        }

        // === Установка значения ===
        $data[$name] = $value;
        self::rewrite(false, $isAdmin);
        return true;
    }

    /**
     * Ленивая загрузка хранилища из cookie
     */
    private static function loadStore(bool $isAdmin): void
    {
        if ($isAdmin) {
            if (self::$adminData !== null) {
                return;
            }
            self::$adminData = [];
            if (!empty($_COOKIE[self::$adminCookieName])) {
                $tmp = json_decode($_COOKIE[self::$adminCookieName], true);
                if (is_array($tmp)) {
                    self::$adminData = $tmp;
                }
            }
            return;
        }
        if (self::$data !== null) {
            return;
        }
        self::$data = [];
        if (!empty($_COOKIE[self::$cookieName])) {
            $tmp = json_decode($_COOKIE[self::$cookieName], true);
            if (is_array($tmp)) {
                self::$data = $tmp;
            }
        }
    }

    /**
     * Записывает cookie
     * @param bool $all_remove удалить cookie
     * @param bool $isAdmin чьё хранилище пишем
     */
    private static function rewrite($all_remove = false, bool $isAdmin = false)
    {
        $cookie = $isAdmin ? self::$adminCookieName : self::$cookieName;
        $lifetime = $isAdmin ? self::$adminLifetime : self::$lifetime;
        $payload = $isAdmin ? self::$adminData : self::$data;

        if ($all_remove) {
            @setcookie(
                $cookie,
                '',
                new DateTime('now', new DateTimeZone('Europe/Moscow'))->getTimestamp() - 3600,
                '/',
                '',
                false,
                true
            );
            return true;
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        @setcookie(
            $cookie,
            $json,
            new DateTime('now', new DateTimeZone('Europe/Moscow'))->getTimestamp() + $lifetime,
            '/',
            '',
            false,
            true
        );

        return true;
    }
}
