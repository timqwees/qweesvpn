<?php 

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin;

use App\Models\Network\Network;
use App\Config\Session;

class AdminAuth
{
    const ADMIN_USERS = [
        [
            'id' => 1,
            'username' => 'timqwees',
            'password' => 'timqwees1220066$',
            'role' => 'admin'
        ]
    ];

    public static function auth(): void
    {
        $adminSession = Session::init('admin');
        if (!is_array($adminSession) || !isset($adminSession['auth']) || !is_array($adminSession['auth']) || $adminSession['auth'][0] !== true) {
            Network::onRedirect('/admin/login');
            exit();
        }
    }

    public static function onLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            foreach (self::ADMIN_USERS as $admin) {
                if ($admin['username'] === $username && $admin['password'] === $password) {
                    $adminSession = Session::init('admin');
                    if (!is_array($adminSession)) {
                        $adminSession = [];
                    }
                    $adminSession['auth'] = [true, $admin['id']];
                    Session::init('admin', $adminSession);
                    Network::onRedirect('/admin');
                    return;
                }
            }

            Network::onRedirect('/admin/login?error=Неверные учетные данные');
        } else {
            Network::onRedirect('/admin/login');
        }
    }

    public static function getRole(int $id): string
    {
        foreach (self::ADMIN_USERS as $admin) {
            if ($admin['id'] === $id) {
                return $admin['role'];
            }
        }
        return 'manager';
    }

    public static function hasRole(int $id, string $role): bool
    {
        $roles = ['manager' => 1, 'admin' => 2];
        $clientLevel = $roles[self::getRole($id)] ?: 1;//клиентская роль - число в иерархии
        $selectLevel = $roles[$role] ?: 1;//требуемая роль - число в иерархии
        return $clientLevel >= $selectLevel;//bool
    }

    public static function onLogout(): void
    {
        Session::init('admin', null);
        Network::onRedirect('/admin/login');
        exit();
    }
}