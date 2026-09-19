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
use Setting\Route\Function\Controllers\{Server\Network as ServerNetwork, Client\GetUser, Admin\Admin, Vpn\V2ray\Xray};

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

	public function isView() : bool
	{
		$uniID = (string) (new GetUser())->getUniID();
		if ($uniID === '') return false;//пустого не берем
		if (!$this->isEnabled()) return false;//выключено
		$users = $this->data['users'] ?? [];
		if (!\is_array($users)) return false;
		return (!\in_array($uniID, $users, true));//только списку
	}

	// ==================== Выдача ====================

	public function giveGifts() : void
	{
		$uniID = (string) (new GetUser())->getUniID();//получаем ID
		if ($uniID === '') Network::onRedirect('/');//пустого не берем
		$days = $this->getDays();
		if ($days < 1) Network::onRedirect('/');;//срок беплатной подписки не настроен корректно
		$nowMs = time() * 1000;//мс как везде
		$newExpiry = $nowMs + $days * 86400000;
		$users = $this->data['users'] ?? [];//получаем заранее данные, чтобы провреять был он уже или нет
		$xray = new Xray();//xnтбы выдавать в панель
		
		$row = Database::send('SELECT status, subscription, expiry, count_days FROM qwees_subscriptions WHERE uniID = ? LIMIT 1', [$uniID]);
		$cur = (\is_array($row) && isset($row[0])) ? $row[0] : null;
		if ($cur !== null) {//пользователь имеет данные в таблице подписок
			$status = (string) ($cur['status'] ?? '');//статус получаем
			if ($status === 'on') Network::onRedirect('/');;//получить пробную нельзя есть есть уже подписка
			if (\in_array($uniID, $users, true)) Network::onRedirect('/');
      $vpnResult = $xray->addClient((int) $days, $uniID, 0);
      if(\is_array($vpnResult)){//при успехе
   			Database::send("UPDATE qwees_subscriptions SET status = 'on', subscription = ?, count_days = ?, expiry = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?", [(string) ServerNetwork::getSubscriptionUrl($uniID), $days, $newExpiry, $uniID]);
				$this->addUser($uniID);//добавляю пользователя в список
      }
		} else {//первый раз
			//а если его в списке не было то выдаем так как это може быть пользователь который просто не оплатил и получил статус off
			if (\in_array($uniID, $users, true)) Network::onRedirect('/');;//уже в списке
      $vpnResult = $xray->addClient((int) $days, $uniID, 0);
      if(\is_array($vpnResult)){//при успехе
        Database::send("INSERT INTO qwees_subscriptions (uniID, status, subscription, count_days, expiry) VALUES (?, 'on', ?, ?, ?)", [$uniID, (string) ServerNetwork::getSubscriptionUrl($uniID), $days, $newExpiry]);
				$this->addUser($uniID);//добавляю пользователя в список
      }
		}
		file_put_contents(
			$_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
			\sprintf("[%s] [ПРОБНАЯ ПОДПИСКА - ВЫДАЧА] %s: выдано %d дней\n", date('Y-m-d H:i:s'), $uniID, $days),
			FILE_APPEND
		);
		Network::onRedirect('/');;//ключ VPN докинем при выкате через Xray
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
		if (\array_key_exists('users', $_POST)) {
			$users = preg_split('/[\r\n,;]+/', (string) $_POST['users']);
			$this->data['users'] = array_values(array_unique(array_filter(array_map('trim', $users ?: []))));
		}
		(new Save())->save($this->data);
		(new Admin())->LoggerCRM("сохранил пробные: " . ($this->data['enabled'] ? 'вкл' : 'выкл') . ", " . (int) ($this->data['days'] ?? 0) . " дн");
		Network::onRedirect($url);
	}
}