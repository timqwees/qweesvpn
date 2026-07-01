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
// Network::onTableAllExists();


#цены
Database::send(“INSERT INTO `qwees_price` (`id`, `name`, `price`) VALUES
(1, 'basic', 160),
(2, 'clasic', 190),
(3, 'pro', 220);”);

#подписки
Database::send(“INSERT INTO `qwees_subscriptions` (`id`, `uniID`, `status`, `subscription`, `amount`, `count_days`, `count_devices`, `date_end`, `payment_method_id`, `created_at`, `updated_at`) VALUES
(7, 'qws6a08c59a45f82', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a08c59a45f82', '0', 30, 1, '2026-06-24', NULL, '2026-05-24 23:54:11', '2026-05-24 23:54:11'),
(8, 'qws6a096266c4990', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a096266c4990', '0', 365, 2, '2027-05-25', NULL, '2026-05-24 23:54:39', '2026-05-24 23:54:39'),
(9, 'qws6a07b1f2a1315', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a07b1f2a1315', '0', 25, 1, '2026-06-19', NULL, '2026-05-24 23:55:13', '2026-05-24 23:55:13'),
(10, 'qws6a14712fb4c45', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a14712fb4c45', '0', 365, 4, '2027-05-25', NULL, '2026-05-25 16:35:36', '2026-05-25 16:35:36'),
(14, 'qws6a17353e2bbc0', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a17353e2bbc0', '1020.00', 180, 10, '2026-11-23', '31a94ada-000f-5001-9000-1bce55ae6c9e', '2026-05-27 18:21:51', '2026-05-27 18:26:11'),
(19, 'qws6a2272b37f451', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a2272b37f451', '0', 365, 5, '2027-06-05', NULL, '2026-06-05 06:56:46', '2026-06-05 06:56:46'),
(21, 'qws6a22bd99909e2', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a22bd99909e2', '160.00', 30, 1, '2026-07-05', '31b4d1d4-000f-5001-9000-1eb2eb0c3595', '2026-06-05 12:16:20', '2026-06-05 12:18:06'),
(23, 'qws6a07799e2379b', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a07799e2379b', '0', 365, 10, '2027-06-05', NULL, '2026-06-05 14:22:10', '2026-06-05 14:22:10'),
(24, 'qws6a229de07ca46', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a229de07ca46', '160.00', 30, 1, '2026-07-06', '31b5a030-000f-5000-b000-16464a8973ec', '2026-06-06 02:56:48', '2026-06-06 03:06:18'),
(26, 'qws6a226f751e061', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a226f751e061', '0', 365, 10, '2027-06-07', NULL, '2026-06-07 15:42:05', '2026-06-07 15:42:05'),
(28, 'qws6a285163eb63c', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a285163eb63c', '0', 365, 10, '2027-06-09', NULL, '2026-06-09 17:46:31', '2026-06-09 17:46:31'),
(29, 'qws6a290e46476f4', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a290e46476f4', '190.00', 30, 4, '2026-07-10', NULL, '2026-06-10 08:10:50', '2026-06-10 08:10:50'),
(31, 'qws6a077c4f1037c', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a077c4f1037c', '0', 365, 1, '2027-06-26', NULL, '2026-06-26 13:40:04', '2026-06-26 13:40:04'),
(32, 'qws6a16f7169a92f', 'on', 'https://nl.qweesvpn.online:1005/qweesteam_subscription/qws6a16f7169a92f', '160.00', 30, 1, '2026-07-28', '31d1f608-000f-5000-8000-188e2619884b', '2026-06-27 14:46:32', '2026-06-28 00:06:10');”);

#Пользователи
Database::send(“INSERT INTO `qwees_users` (`id`, `first_name`, `last_name`, `uniID`, `email`, `myrefer`, `refer`, `refer_id`, `refer_count`, `discount_percent`, `created_at`, `bonus_percent`) VALUES
(1, 'tim', 'qwees', 'qws6a07799e2379b', 'timqwees@gmail.com', 'QWEE6EVD', NULL, 0, 0, 0, '2026-05-15 19:53:02', 0),
(2, 'Max', 'Ogan', 'qws6a077c4f1037c', 'maxogan099@gmail.com', 'QWENLOAG', NULL, 0, 0, 0, '2026-05-15 20:04:31', 0),
(3, 'Kotakot1', 'kot', 'qws6a07b1f2a1315', 'dagrooila33@gmail.com', 'QWEIA49G', NULL, 0, 0, 0, '2026-05-15 23:53:22', 0),
(4, 'Владислав', 'Demich', 'qws6a07b475863c4', 'vladdo000@gmail.com', 'QWE:>033', NULL, 0, 0, 0, '2026-05-16 00:04:05', 0),
(5, 'David', 'Ogan', 'qws6a08c59a45f82', 'davogan29@gmail.com', 'QWEVAJ4R', NULL, 0, 0, 0, '2026-05-16 19:29:30', 0),
(6, 'Artur', 'Artur', 'qws6a096266c4990', 'artur.nersisyan.1995@mail.ru', 'QWETZZM3', NULL, 0, 0, 0, '2026-05-17 06:38:30', 0),
(7, 'artem', 'nersisyan', 'qws6a13802c054ec', 'artemnersisyan777@gmail.com', 'QWE6KY5D', NULL, 0, 0, 0, '2026-05-24 22:48:12', 0),
(8, 'Анаид', 'Шахануманц', 'qws6a14712fb4c45', 'anaid1955@mail.ru', 'QWE>AN?O', NULL, 0, 0, 0, '2026-05-25 15:56:31', 0),
(9, 'Artur', 'Nersisyan', 'qws6a155560b5011', 'artur.nersisyan.1999@bk.ru', 'QWEOVHKF', NULL, 0, 0, 0, '2026-05-26 08:10:08', 0),
(10, 'Саша', 'Пофиг', 'qws6a16f7169a92f', 'polyakovalex1976@yandex.ru', 'QWE8:VMZ', NULL, 0, 0, 0, '2026-05-27 13:52:22', 0),
(11, 'Армен', 'Мкрчан', 'qws6a17353e2bbc0', 'mkrtchyan0555@bk.ru', 'QWEFXA2L', NULL, 0, 0, 0, '2026-05-27 18:17:34', 0),
(12, 'Марсель', 'Хамитов', 'qws6a19d1395df25', '135marsel135@mail.ru', 'QWE68XB=', NULL, 0, 0, 0, '2026-05-29 17:47:37', 0),
(13, 'kotanist', 'Иванов', 'qws6a1e6b698a66d', 'andrejivanov6224@gmail.com', 'QWEFW9C5', NULL, 0, 0, 0, '2026-06-02 05:34:33', 0),
(14, 'Наталье', 'Павловне', 'qws6a226f751e061', 'wald_712@mail.ru', 'QWEY5CP4', NULL, 0, 0, 0, '2026-06-05 06:40:53', 0),
(15, 'Артак', 'Брат', 'qws6a2272b37f451', 'artak01071995@icloud.com', 'QWEXK5NC', NULL, 0, 0, 0, '2026-06-05 06:54:43', 0),
(16, 'Bayram', 'Aloev', 'qws6a229de07ca46', 'Aloneinthedark91@mail.ru', 'QWEKZ40P', NULL, 0, 0, 0, '2026-06-05 09:58:56', 0),
(17, 'Dhhh', 'Gghnnn', 'qws6a22bd99909e2', 'sulgasona58@gmail.com', 'QWE::40U', NULL, 0, 0, 0, '2026-06-05 12:14:17', 0),
(18, 'Wizard1k', 'Skebob', 'qws6a26cab1371a2', 'wizardkkdraziw@gmail.com', 'QWE=@O5C', NULL, 0, 0, 0, '2026-06-08 13:59:13', 0),
(19, 'A6syrd', '02', 'qws6a26cd35e0686', 'ilakarebo972@gmail.com', 'QWEOWQQ0', 'QWE=@O5C', 18, 0, 10, '2026-06-08 14:09:57', 0),
(20, 'Ирина', 'Кулибаба', 'qws6a285163eb63c', 'elitprice@mail.ru', 'QWEBMGME', NULL, 0, 0, 0, '2026-06-09 17:46:11', 0),
(21, 'Alex', 'Plato', 'qws6a290e46476f4', 'alecplat13@gmail.com', 'QWE0DGKS', NULL, 0, 0, 0, '2026-06-10 07:12:06', 0),
(22, 'Леонид', 'Бурмалда', 'qws6a2957d9cddbb', 'bondrmm@gmail.com', 'QWE5Q4LH', NULL, 0, 0, 0, '2026-06-10 12:26:01', 0);”);

include_once __DIR__ . '/setting/Route/Routes.php';
Network::onRoute();
