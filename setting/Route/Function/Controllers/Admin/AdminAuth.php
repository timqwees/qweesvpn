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

use Setting\Route\Function\Controllers\Admin\Users\Users;
use Setting\Route\Function\Controllers\Admin\Group\Groups;
use App\Models\Network\Network;
use App\Config\Session;

class AdminAuth
{

	use Users;//испольузем трейд

    public static function auth(): void
    {
        $adminSession = Session::init('admin');
        if (!\is_array($adminSession) || !isset($adminSession['auth']) || !\is_array($adminSession['auth']) || $adminSession['auth'][0] !== true) {
            Network::onRedirect('/admin/login');
            exit();
        }
        // Пользователь существует и версия сессии актуальна (иначе — уволен/сброшен)
        $id = (int) ($adminSession['auth'][1] ?? 0);
        $ver = (int) ($adminSession['auth'][2] ?? 1);
        foreach (self::$ADMIN_USERS as $a) {
            if ((int) ($a['id'] ?? 0) === $id && (int) ($a['session_ver'] ?? 1) === $ver) return;
        }
        Network::onRedirect('/admin/login');
        exit();
    }

    /** Авторизация + право на раздел (иначе назад в админку). Имя берём из трейта Users — без цикла наследования. */
    public static function requirePermission(string $perm): void
    {
        self::auth();
        $id = (int) (Session::init('admin')['auth'][1] ?? 0);
        $username = 'unknown';
        foreach (self::$ADMIN_USERS as $a) {
            if ((int) ($a['id'] ?? 0) === $id) {
                $username = (string) ($a['username'] ?? 'unknown');
                break;
            }
        }
        if (!(new Groups())->isPermission($username, $perm)) {
            Network::onRedirect('/admin');
            exit();
        }
    }

    public static function onLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (!\App\Config\Throttle::hit('admin_login:' . \App\Config\Throttle::ip(), 5, 900)) {
                Network::onRedirect('/admin/login?error=Слишком много попыток. Подождите 15 минут.');
                return;
            }
            foreach (self::$ADMIN_USERS as $admin) {
                if ($admin['username'] === $username && $admin['password'] === $password) {
                    $adminSession = Session::init('admin');
                    if (!\is_array($adminSession)) {
                        $adminSession = [];
                    }
                    $adminSession['auth'] = [true, $admin['id'], (int) ($admin['session_ver'] ?? 1)];
                    Session::init('admin', $adminSession);
                    \App\Config\Throttle::reset('admin_login:' . \App\Config\Throttle::ip());
                    (new Admin())->LoggerCRM("вошёл в панель", "ВХОД");
                    Network::onRedirect('/admin');
                    return;
                }
            }

            @file_put_contents(
                dirname(__DIR__, 5) . '/' . basename($_ENV['LOG_FILE_NAME'] ?? 'qwees.log'),
                \sprintf("[%s] [ВХОД] Неудачный вход в админку: %s с %s\n", date('Y-m-d H:i:s'), $username, \App\Config\Throttle::ip()),
                FILE_APPEND
            );
            Network::onRedirect('/admin/login?error=Неверные учетные данные');
        } else {
            Network::onRedirect('/admin/login');
        }
    }

    public static function onLogout(): void
    {
        (new Admin())->LoggerCRM("вышел из панели", "ВХОД");
        Session::init('admin', null);
        Network::onRedirect('/admin/login');
        exit();
    }
}