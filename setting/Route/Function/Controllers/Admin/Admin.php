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
declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin;

use Setting\Route\Function\Controllers\Admin\Group\{Tools\GetUsers, Tools\Save};
use App\Config\Session;

class Admin extends AdminAuth
{
	//==============ВЛАДЕЛЕЦ===================================
    public const OWNER = 'timqwees';//его должность меняем только мы сами

	//==============ПРАВА======================================
    public const FULL_PERMISSIONS = [
    'main' => 'Главное меню', 
    'price' => 'Раздел настроек цен', 
    'give' => 'Раздел выдачи подпиок', 
    'reduce' => 'Раздел изьятие подписок', 
    'database' =>  'Таблицы базы-данных', 
    'charts' => 'Статистики', 
    'logs' => 'Просмотр логов', 
    'add_user' => 'Создание пользователей', 
    'roles' => 'Панель администратора по управлению ролями',
    'chat' => 'Чат поддержки',
    'refer' => 'Реферальная система',
    'gifts' => 'Пробная подписка',
    'roi' => 'Калькулятор ROI',
    'profit' => 'Показатели прибыли',
    'monitoring' => 'Мониторинг серверов',
    'relocation' => 'Переезд (серверы и домены)',
    'servers' => 'Управление серверами'
    ];
    public const DEFAULT_PERMISSIONS = ['main' => 'Главное меню'];

	//==============РОЛИ======================================
    public const ROLES = ['manager' => 1, 'moderator' => 1, 'admin' => 2];
    // Стартовый набор прав по роли (выдаётся при найме/смене роли; тонкая настройка — галочками)
    public const ROLE_PERMISSIONS = [
        'manager' => ['main', 'chat', 'gifts', 'refer', 'monitoring'],
        'moderator' => ['main', 'chat', 'logs', 'monitoring'],
        'admin' => [],//admin = все (FULL_PERMISSIONS), список ниже строится динамически
    ];

    public function getUsername(int $id): string
    {
        foreach (self::$ADMIN_USERS as $admin) {//ищем по id
            if ($admin['id'] === $id) return $admin['username'];
        }
        return 'unknown';
    }

    public function getRole(int $id): string
    {
        foreach (self::$ADMIN_USERS as $admin) {//ищем по id
            if ($admin['id'] === $id) return $admin['role'];
        }
        return 'manager';
    }

    public function hasRole(int $id, string $role): bool
    {
        $roles = self::ROLES;//иерархия
        $clientLevel = $roles[$this->getRole($id)] ?? 1;//клиентская роль - число в иерархии
        $selectLevel = $roles[$role] ?? 1;//требуемая роль - число в иерархии
        return $clientLevel >= $selectLevel;//bool
    }

    /** Стартовые права роли (admin — все). Ключ => описание. */
    public static function rolePermissions(string $role): array
    {
        if ($role === 'admin') return self::FULL_PERMISSIONS;
        $out = [];
        foreach (self::ROLE_PERMISSIONS[$role] ?? ['main'] as $p) {
            $out[$p] = self::FULL_PERMISSIONS[$p] ?? $p;
        }
        return $out;
    }

    // === найм: добавляем в $ADMIN_USERS + строку в json ===
    public function addManager(string $username, string $password, string $role = 'manager'): bool
    {
        $username = trim($username);
        if ($username === '' || $password === '') return false;//пустых не берем
        if (!isset(self::ROLES[$role])) $role = 'manager';//неизвестную роль не берем
        foreach (self::$ADMIN_USERS as $admin) {//такой уже есть
            if (strtolower($admin['username']) === strtolower($username)) return false;
        }
        $id = 1;
        foreach (self::$ADMIN_USERS as $admin) $id = max($id, $admin['id'] + 1);//новый id
        self::$ADMIN_USERS[] = ['id' => $id, 'username' => $username, 'password' => $password, 'role' => $role, 'session_ver' => 1];
        $this->saveUsers();//пишем в файл трейта
        $rows = (new GetUsers())->getUsers();
        $rows[] = ['username' => $username, 'role' => $role, 'permissions' => self::rolePermissions($role)];
        (new Save())->save($rows);
        $this->LoggerCRM("нанял $username с ролью $role");
        return true;
    }

    // === увольнение: админа уволить нельзя ===
    public function deleteManager(string $username): bool
    {
        foreach (self::$ADMIN_USERS as $admin) {//админа не трогаем
            if (strtolower($admin['username']) === strtolower($username) && ($admin['role'] ?? '') === 'admin') return false;
        }
        self::$ADMIN_USERS = array_values(array_filter(self::$ADMIN_USERS, fn($a) => strtolower($a['username']) !== strtolower($username)));
        $this->saveUsers();//пишем в файл трейта
        $rows = array_values(array_filter((new GetUsers())->getUsers(), fn($r) => strtolower($r['username'] ?? '') !== strtolower($username)));
        (new Save())->save($rows);
        $this->LoggerCRM("уволил $username");
        return true;
    }

    /** Роль + шаблонные права в обоих хранилищах (без лога — логер зовёт caller). */
    private function applyRole(string $username, string $role): bool
    {
        $found = false;
        foreach (self::$ADMIN_USERS as $k => $admin) {
            if (strtolower($admin['username']) === strtolower($username)) {
                self::$ADMIN_USERS[$k]['role'] = $role;
                $found = true;
            }
        }
        if (!$found) return false;
        $this->saveUsers();
        $rows = (new GetUsers())->getUsers();
        foreach ($rows as $k => $r) {
            if (strtolower($r['username'] ?? '') === strtolower($username)) {
                $rows[$k]['role'] = $role;
                $rows[$k]['permissions'] = self::rolePermissions($role);
            }
        }
        (new Save())->save($rows);
        return true;
    }

    /**
     * Правка сотрудника: логин/пароль/роль. Пустой пароль — не меняем.
     * Любое изменение гасит все его сессии (надо войти заново).
     */
    public function updateManager(string $oldUsername, string $newUsername, string $password, string $role): array
    {
        $oldUsername = trim($oldUsername);
        $newUsername = trim($newUsername);
        if ($oldUsername === '' || $newUsername === '' || !isset(self::ROLES[$role])) {
            return ['status' => 'error', 'message' => 'Заполните логин и роль'];
        }
        $idx = null;
        foreach (self::$ADMIN_USERS as $k => $admin) {
            if (strtolower($admin['username']) === strtolower($oldUsername)) $idx = $k;
            if (strtolower($admin['username']) === strtolower($newUsername) && strtolower($newUsername) !== strtolower($oldUsername)) {
                return ['status' => 'error', 'message' => 'Такой логин уже занят'];
            }
        }
        if ($idx === null) return ['status' => 'error', 'message' => 'Сотрудник не найден'];
        self::$ADMIN_USERS[$idx]['username'] = $newUsername;
        if ($password !== '') self::$ADMIN_USERS[$idx]['password'] = $password;
        self::$ADMIN_USERS[$idx]['session_ver'] = (int) (self::$ADMIN_USERS[$idx]['session_ver'] ?? 1) + 1;
        $this->saveUsers();
        $rows = (new GetUsers())->getUsers();
        foreach ($rows as $k => $r) {
            if (strtolower($r['username'] ?? '') === strtolower($oldUsername)) {
                $rows[$k]['username'] = $newUsername;
            }
        }
        (new Save())->save($rows);
        $this->applyRole($newUsername, $role);
        $this->LoggerCRM("обновил сотрудника $oldUsername -> $newUsername (роль $role)" . ($password !== '' ? ', пароль сменён' : ''));
        return ['status' => 'ok', 'message' => 'Сохранено, сессии сотрудника завершены'];
    }

    /** Завершить все сессии сотрудника (смена версии — старые куки мертвы). */
    public function killSessions(string $username): bool
    {
        $found = false;
        foreach (self::$ADMIN_USERS as $k => $admin) {
            if (strtolower($admin['username']) === strtolower($username)) {
                self::$ADMIN_USERS[$k]['session_ver'] = (int) ($admin['session_ver'] ?? 1) + 1;
                $found = true;
            }
        }
        if (!$found) return false;
        $this->saveUsers();
        $this->LoggerCRM("завершил сессии $username");
        return true;
    }

    // === сохраняем $ADMIN_USERS в файл трейта ===
    private function saveUsers(): void
    {
        foreach (self::$ADMIN_USERS as $k => $admin) {
            if (!isset($admin['session_ver'])) self::$ADMIN_USERS[$k]['session_ver'] = 1;
        }        $file = __DIR__ . '/Users/Users.php';
        $export = var_export(array_values(self::$ADMIN_USERS), true);
        file_put_contents($file, "<?php declare(strict_types=1);\n\nnamespace Setting\\Route\\Function\\Controllers\\Admin\\Users;\n\ntrait Users {\n\n\tpublic static array \$ADMIN_USERS = {$export};\n}\n\n?>");
        if (function_exists('opcache_invalidate')) opcache_invalidate($file, true);//сброс кэша
    }

    // === лог действий рабочих ===
    public function LoggerCRM(string $message, string $tag = 'АДМИН'): void
    {
        $id = (int) (Session::init('admin')['auth'][1] ?? 0);//кто сидит
        $tag = preg_match('/^[А-ЯA-Z]{2,12}$/u', $tag) ? $tag : 'АДМИН';
        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
            \sprintf("[%s] [%s] %s: %s\n", $tag, date('Y-m-d H:i:s'), $this->getUsername($id), $message),
            FILE_APPEND
        );
    }
}
