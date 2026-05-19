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

require_once __DIR__ . '/vendor/autoload.php';
## env connect
if (file_exists(__DIR__ . '/.env')) {
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();
}

# connect Route App
use App\Models\Network\Network;
use App\Config\Database;
// Инициализируем подключение к БД и структуру таблиц перед запуском роутинга
Database::getConnection();
Network::onTableAllExists();
Database::send("INSERT OR IGNORE INTO `qwees_price` (`id`, `name`, `price`) VALUES
(1, 'basic', 150),
(2, 'clasic', 180),
(3, 'pro', 200);");
Database::send("INSERT OR IGNORE INTO `qwees_subscriptions` (`id`, `uniID`, `status`, `subscription`, `amount`, `count_days`, `count_devices`, `date_end`, `payment_method_id`, `created_at`, `updated_at`) VALUES
(2, 'qws6a077c4f1037c', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a077c4f1037c', '0', '60', '1', '2026-07-14', NULL, '2026-05-15 20:29:07', '2026-05-15 20:29:07'),
(4, 'qws6a07b1f2a1315', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a07b1f2a1315', '0', '30', '1', '2026-06-15', '3199c692-000f-5000-8000-16e6f37e886d', '2026-05-15 23:57:06', '2026-05-16 05:05:39'),
(6, 'qws6a08c59a45f82', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a08c59a45f82', '0', '60', '1', '2026-07-15', NULL, '2026-05-16 19:30:55', '2026-05-16 19:30:55'),
(7, 'qws6a096266c4990', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a096266c4990', '0', '365', '1', '2027-05-17', NULL, '2026-05-17 06:40:54', '2026-05-17 06:40:54'),
(8, 'qws6a07799e2379b', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a07799e2379b', '150.00', '30', '1', '2026-06-16', '319bcf05-000f-5000-b000-16a717442892', '2026-05-17 12:57:41', '2026-05-17 12:58:13');
");
Database::send("INSERT OR IGNORE INTO `qwees_users` (`id`, `first_name`, `last_name`, `uniID`, `email`, `myrefer`, `refer`, `refer_id`, `refer_count`, `discount_percent`, `created_at`, `bonus_percent`) VALUES
(1, 'tim', 'qwees', 'qws6a07799e2379b', 'timqwees@gmail.com', 'QWEE6EVD', NULL, 0, 0, 0, '2026-05-15 19:53:02', 0),
(2, 'Max', 'Ogan', 'qws6a077c4f1037c', 'maxogan099@gmail.com', 'QWENLOAG', NULL, 0, 0, 0, '2026-05-15 20:04:31', 0),
(3, 'Kotakot1', 'kot', 'qws6a07b1f2a1315', 'dagrooila33@gmail.com', 'QWEIA49G', NULL, 0, 0, 0, '2026-05-15 23:53:22', 0),
(4, 'Владислав', 'Demich', 'qws6a07b475863c4', 'vladdo000@gmail.com', 'QWE:>033', NULL, 0, 0, 0, '2026-05-16 00:04:05', 0),
(5, 'David', 'Ogan', 'qws6a08c59a45f82', 'davogan29@gmail.com', 'QWEVAJ4R', NULL, 0, 0, 0, '2026-05-16 19:29:30', 0),
(6, 'Artur', 'Artur', 'qws6a096266c4990', 'artur.nersisyan.1995@mail.ru', 'QWETZZM3', NULL, 0, 0, 0, '2026-05-17 06:38:30', 0);
");
include_once __DIR__ . '/setting/Route/Routes.php';
Network::onRoute();
