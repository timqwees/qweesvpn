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
 */

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Gifts;

use App\Config\Database;
use App\Models\Network\Network;
use Setting\Route\Function\Controllers\Gifts\{GiftsInterface\InterfaceGifts, Tools\Save, Tools\Load, Tools\GetGifts};
use Setting\Route\Function\Controllers\Admin\Admin;

class Gifts implements InterfaceGifts
{
	public static string $file = __DIR__ . '/Config/gifts.json';//абсолютный путь: относительный ломался при другом CWD и плодил копии
	public array $data;

	public function __construct()
	{
		(new Load())->load();
		$this->data = (new GetGifts())->getGifts();
	}

	// ==================== Вкл / выкл ====================

	public function enable() : bool
	{
		if (!\is_array($this->data)) $this->data = [];//это массив
		$this->data['enabled'] = true;
		(new Save())->save($this->data);
		return true;
	}

	public function disable() : bool
	{
		if (!\is_array($this->data)) $this->data = [];//это массив
		$this->data['enabled'] = false;
		(new Save())->save($this->data);
		return true;
	}

	// ==================== Срок и кому ====================

	public function setDays(int $days) : bool
	{
		if ($days < 1) return false;//пустых не берем
		if (!\is_array($this->data)) $this->data = [];//это массив
		$this->data['days'] = $days;
		(new Save())->save($this->data);
		return true;
	}

	public function setMode(string $mode) : bool
	{
		if ($mode !== 'all' && $mode !== 'list') return false;//только всем или списку
		if (!\is_array($this->data)) $this->data = [];//это массив
		$this->data['mode'] = $mode;
		(new Save())->save($this->data);
		return true;
	}

	public function addUser(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		if (!\is_array($this->data)) $this->data = [];//это массив
		$users = $this->data['users'] ?? [];//заполняем данными
		if (!\is_array($users)) $users = [];
		if (\in_array($uniID, $users, true)) return true;//уже в списке
		$users[] = $uniID;
		$this->data['users'] = array_values($users);
		(new Save())->save($this->data);
		return true;
	}

	public function removeUser(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		if (!\is_array($this->data)) $this->data = [];//это массив
		$users = $this->data['users'] ?? [];
		if (!\is_array($users)) $users = [];
		$key = array_search($uniID, $users, true);
		if ($key !== false) unset($users[$key]);//нашли — убрали
		$this->data['users'] = array_values($users);//без дырок в ключах, а то json даст объект
		(new Save())->save($this->data);
		return true;
	}

	// ==================== Проверка ====================

	public function isEnabled() : bool
	{
		if (!\is_array($this->data)) return false;//это массив
		return (bool) ($this->data['enabled'] ?? false);
	}

	public function getDays() : int
	{
		if (!\is_array($this->data)) return 0;//это массив
		return (int) ($this->data['days'] ?? 0);
	}

	public function canSee(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		if (!$this->isEnabled()) return false;//выключено
		if (($this->data['mode'] ?? 'all') === 'all') return true;//всем сразу
		$users = $this->data['users'] ?? [];
		if (!\is_array($users)) return false;
		return in_array($uniID, $users, true);//только списку
	}

	// ==================== Выдача ====================

	public function giveTrial(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		$days = $this->getDays();
		if ($days < 1) return false;//срок не настроен
		$nowMs = time() * 1000;//мс как везде
		$row = Database::send('SELECT status, subscription, expiry, count_days FROM qwees_subscriptions WHERE uniID = ? LIMIT 1', [$uniID]);
		$cur = (\is_array($row) && isset($row[0])) ? $row[0] : null;
		if ($cur !== null) {
			$status = (string) ($cur['status'] ?? '');
			$sub = (string) ($cur['subscription'] ?? '');
			$expiry = (int) ($cur['expiry'] ?? 0);
			if ($status === 'pending_vpn') return false;//оплачено, ждёт VPN — триалом не перекрываем
			if ($status === 'on' && $expiry > $nowMs && !\in_array($sub, ['trial', 'bonus', ''], true)) {
				return false;//активная платная подписка уже есть, пробную не даем
			}
			if ($status === 'on' && $expiry > $nowMs) return false;//активный триал/бонус уже идёт, повторно не даем
			// Триал ДОБАВЛЯЕМ поверх остатка (в т.ч. bonus-дней рефералки), а не затираем
			$newExpiry = max($nowMs, $expiry) + $days * 86400000;
			$newCount = (int) ($cur['count_days'] ?? 0) + $days;
			Database::send("UPDATE qwees_subscriptions SET status = 'on', subscription = 'trial', count_days = ?, expiry = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?", [$newCount, $newExpiry, $uniID]);
		} else {//первый раз
			$newExpiry = $nowMs + $days * 86400000;
			Database::send("INSERT INTO qwees_subscriptions (uniID, status, subscription, count_days, expiry) VALUES (?, 'on', 'trial', ?, ?)", [$uniID, $days, $newExpiry]);
		}
		file_put_contents(
			$_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
			\sprintf("[%s] [ПРОБНАЯ ПОДПИСКА - ВЫДАЧА] %s: выдано %d дней\n", date('Y-m-d H:i:s'), $uniID, $days),
			FILE_APPEND
		);
		return true;//ключ VPN докинем при выкате через Xray
	}

	// ==================== Админка ====================

	public function onSave()
	{
		$url = $_POST['url'] ?? '/admin';
		if (!\is_array($this->data)) $this->data = [];//это массив
		$this->data['enabled'] = isset($_POST['enabled']) && $_POST['enabled'] === 'on';//галочки нет — выкл
		$days = (int) ($_POST['days'] ?? 0);
		if ($days > 0) $this->data['days'] = $days;
		$mode = $_POST['mode'] ?? 'all';
		if ($mode === 'all' || $mode === 'list') $this->data['mode'] = $mode;
		if (array_key_exists('users', $_POST)) {
			$users = preg_split('/[\r\n,;]+/', (string) $_POST['users']);
			$this->data['users'] = array_values(array_unique(array_filter(array_map('trim', $users ?: []))));
		}
		(new Save())->save($this->data);
		(new Admin())->LoggerCRM("сохранил пробные: " . ($this->data['enabled'] ? 'вкл' : 'выкл') . ", " . (int) ($this->data['days'] ?? 0) . " дн");
		Network::onRedirect($url);
	}
}