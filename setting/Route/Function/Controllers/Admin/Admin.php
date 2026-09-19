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
    'roi' => 'Калькулятор ROI'
    ];
    public const DEFAULT_PERMISSIONS = ['main' => 'Главное меню'];

	//==============РОЛИ======================================
    public const ROLES = ['manager' => 1, 'moderator' => 1, 'admin' => 2];
    // права новичка по умолчанию

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

    // === найм: добавляем в $ADMIN_USERS + строку в json ===
    public function addManager(string $username, string $password, string $role = 'manager'): bool
    {
        $username = trim($username);
        if ($username === '' || $password === '') return false;//пустых не берем
        foreach (self::$ADMIN_USERS as $admin) {//такой уже есть
            if (strtolower($admin['username']) === strtolower($username)) return false;
        }
        $id = 1;
        foreach (self::$ADMIN_USERS as $admin) $id = max($id, $admin['id'] + 1);//новый id
        self::$ADMIN_USERS[] = ['id' => $id, 'username' => $username, 'password' => $password, 'role' => $role];
        $this->saveUsers();//пишем в файл трейта
        $rows = (new GetUsers())->getUsers();
        $rows[] = ['username' => $username, 'role' => $role, 'permissions' => self::DEFAULT_PERMISSIONS];
        (new Save())->save($rows);
        $this->LoggerCRM("нанял $username с ролью $role");
        return true;
    }

    // === увольнение: админа уволить нельзя ===
    public function deleteManager(string $username): bool
    {
    		$adminID = (array) (Session::init('admin')['auth'] ?? 0);
        foreach (self::$ADMIN_USERS as $admin) {//админа не трогаем
            if (strtolower($admin['username']) === strtolower($username) && $admin['role'] === 'admin' && \in_array([1], $adminID)) return false;
        }
        self::$ADMIN_USERS = array_values(array_filter(self::$ADMIN_USERS, fn($a) => strtolower($a['username']) !== strtolower($username)));
        $this->saveUsers();//пишем в файл трейта
        $rows = array_values(array_filter((new GetUsers())->getUsers(), fn($r) => strtolower($r['username'] ?? '') !== strtolower($username)));
        (new Save())->save($rows);
        $this->LoggerCRM("уволил $username");
        return true;
    }

    // === смена роли ===
    public function changeRole(string $username, string $role): bool
    {
        foreach (self::$ADMIN_USERS as $k => $admin) {//в массиве
            if (strtolower($admin['username']) === strtolower($username)) self::$ADMIN_USERS[$k]['role'] = $role;
        }
        $this->saveUsers();//пишем в файл трейта
        $rows = (new GetUsers())->getUsers();
        foreach ($rows as $k => $r) {//в json
            if (strtolower($r['username'] ?? '') === strtolower($username)) $rows[$k]['role'] = $role;
        }
        (new Save())->save($rows);
        $this->LoggerCRM("сменил роль $username на $role");
        return true;
    }

    // === сохраняем $ADMIN_USERS в файл трейта ===
    private function saveUsers(): void
    {
        $file = __DIR__ . '/Users/Users.php';
        $export = var_export(array_values(self::$ADMIN_USERS), true);
        file_put_contents($file, "<?php declare(strict_types=1);\n\nnamespace Setting\\Route\\Function\\Controllers\\Admin\\Users;\n\ntrait Users {\n\n\tpublic static array \$ADMIN_USERS = {$export};\n}\n\n?>");
        if (function_exists('opcache_invalidate')) opcache_invalidate($file, true);//сброс кэша
    }

    // === лог действий рабочих ===
    public function LoggerCRM(string $message): void
    {
        $id = (int) (Session::init('admin')['auth'][1] ?? 0);//кто сидит
        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
            \sprintf("[WLC] [%s] %s: %s\n", date('Y-m-d H:i:s'), $this->getRole($id), $this->getUsername($id), $message),
            FILE_APPEND
        );
    }
}
