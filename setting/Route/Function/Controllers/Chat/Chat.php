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

namespace Setting\Route\Function\Controllers\Chat;

use Setting\Route\Function\Controllers\Chat\{AdminInterface\InterfaceChat, AdminInterface\InterfaceChatAdmin, Tools\Save, Tools\Load};
use Setting\Route\Function\Controllers\Client\GetUser;
use Setting\Route\Function\Controllers\Kassa\PriceConfig;
use Setting\Route\Function\Controllers\Admin\Admin;

class Chat implements InterfaceChat, InterfaceChatAdmin
{
	public static string $file = '';
	public static string $uploads = '';
	public static string $presence = '';
	public array $data = [];
	private ?array $seen = null;//кто когда был, читаем раз за запрос

	public function __construct()
	{
		if (self::$file === '') {
			self::$file = __DIR__ . '/Dialogs/chats.json';
		}
		$dir = dirname(self::$file);
		if ($dir !== '' && !is_dir($dir)) mkdir($dir, 0755, true);//создаем цепочку директорий
		if (self::$uploads === '') {
			self::$uploads = __DIR__ . '/Uploads';
		}
		if (!is_dir(self::$uploads)) mkdir(self::$uploads, 0755, true);//папка для фото
		if (self::$presence === '') {
			self::$presence = __DIR__ . '/Dialogs/presence.json';
		}
		$loader = new Load();
		$loader->load();
		$this->data = $loader->getData();
		if (!\is_array($this->data)) $this->data = [];//это массив
	}

	// ==================== Ответы JSON для роутов ====================

	public static function json(array $extra = []) : void
	{
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode(['status' => 'ok'] + $extra, JSON_UNESCAPED_UNICODE);
		exit;
	}

	public static function error(string $message) : void
	{
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
		exit;
	}

	// ==================== Пользователь ====================

	public function sendMessage(string $uniID, string $message) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		if (trim($message) === '') return false;//пустые не берем
		$this->touch($uniID);//я тут
		$this->push($uniID, 'user', 'admin', $message);
		return true;
	}

	public function getMessages(string $uniID) : array
	{
		if ($uniID === '') return [];//пустого не берем
		if (!\is_array($this->data)) return [];//это массив
		$this->touch($uniID);//я тут
		$dialog = $this->dialog($uniID);
		$changed = false;
		foreach ($dialog as $key => $value) {//пробежимся по сообщениям
			if (($value['sender_type'] ?? '') === 'admin' && empty($value['is_read'])) {//ответ админа еще не читали
				$dialog[$key]['is_read'] = true;
				$changed = true;
			}
		}
		if ($changed) $this->store($uniID, $dialog);//что-то пометили, пишем в файл
		return $dialog;
	}

	public function markRead(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		if (!\is_array($this->data)) return false;//это массив
		$this->touch($uniID);//я тут
		$dialog = $this->dialog($uniID);
		$changed = false;
		foreach ($dialog as $key => $value) {//пробежимся по сообщениям
			if (($value['sender_type'] ?? '') === 'admin' && empty($value['is_read'])) {//ответ админа еще не читали
				$dialog[$key]['is_read'] = true;
				$changed = true;
			}
		}
		if (!$changed) return true;//уже все прочитано, файл не трогаем
		$this->store($uniID, $dialog);
		return true;
	}

	public function getUnreadCount(string $uniID) : int
	{
		if ($uniID === '') return 0;//пустого не берем
		if (!\is_array($this->data)) return 0;//это массив
		$count = 0;
		foreach ($this->dialog($uniID) as $value) {//пробежимся по сообщениям
			if (($value['sender_type'] ?? '') === 'user' && empty($value['is_read'])) $count++;//юзер написал, админ еще не читал
		}
		return $count;
	}

	public function isClosed(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		if (!\is_array($this->data)) return false;//это массив
		$dialog = $this->dialog($uniID);
		if (empty($dialog)) return false;
		return $this->isClosedDialog($dialog);
	}

	public function isOnline(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		$seen = $this->presence();
		if (!isset($seen[$uniID])) return false;
		return (time() - (int) $seen[$uniID]) < 15;//был тут 15 секунд назад
	}

	// ==================== Админка ====================

	public function getDialogs() : array
	{
		if (!\is_array($this->data)) return [];//это массив
		$this->touch('admin');//админ тут
		$seen = $this->presence();//кто когда заглядывал
		$now = time();
		$dialogs = [];
		foreach ($this->data as $uniID => $dialog) {//пробежимся по юзерам
			if (!\is_array($dialog) || empty($dialog)) continue;//пустых не берем
			$last = end($dialog);//последнее сообщение
			$closed = $this->isClosedDialog($dialog);//диалог завершен
			$preview = (string) ($last['message'] ?? '');
			if (($last['type'] ?? '') === 'image') $preview = 'Фото';//в превью текст не нужен
			$unread = 0;
			if (!$closed) {
				foreach ($dialog as $value) {//непрочитанные считаем заодно, второй проход не нужен
					if (($value['sender_type'] ?? '') === 'user' && empty($value['is_read'])) $unread++;
				}
			}
			$dialogs[] = [
				'uniID' => (string) $uniID,
				'count' => count($dialog),
				'unread' => $unread,
				'closed' => $closed,
				'online' => !$closed && ($now - (int) ($seen[(string) $uniID] ?? 0)) < 15,//был тут 15 секунд назад
				'last_message' => $closed ? 'Диалог завершён' : $preview,
				'last_at' => (string) ($last['created_at'] ?? '')
			];
		}
		usort($dialogs, fn($a, $b) => strcmp($b['last_at'], $a['last_at']));//свежие сверху
		return $dialogs;
	}

	public function getDialog(string $uniID) : array
	{
		if ($uniID === '') return [];//пустого не берем
		if (!\is_array($this->data)) return [];//это массив
		$this->touch('admin');//админ тут
		$dialog = $this->dialog($uniID);
		$changed = false;
		foreach ($dialog as $key => $value) {//пробежимся по сообщениям
			if (($value['sender_type'] ?? '') === 'user' && empty($value['is_read'])) {//юзер написал, админ смотрит
				$dialog[$key]['is_read'] = true;
				$changed = true;
			}
		}
		if ($changed) $this->store($uniID, $dialog);//что-то пометили, пишем в файл
		return $dialog;
	}

	public function reply(string $uniID, string $message) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		if (trim($message) === '') return false;//пустые не берем
		$this->touch('admin');//админ тут
		$this->push($uniID, 'admin', 'user', $message);
		(new Admin())->LoggerCRM("ответил в чат $uniID");
		return true;
	}

	public function closeDialog(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		if (!\is_array($this->data)) return false;//это массив
		$dialog = $this->dialog($uniID);
		if (empty($dialog)) return false;//пустых не берем
		if ($this->isClosedDialog($dialog)) return true;//уже закрыт
		$this->push($uniID, 'system', 'user', 'closed', true);//системная пометка, не сообщение
		$this->cleanPhotos($uniID);
		(new Admin())->LoggerCRM("завершил диалог $uniID");
		return true;
	}

	public function clearDialog(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		if (!\is_array($this->data)) return false;//это массив
		if (!isset($this->data[$uniID])) return true;//уже пусто
		unset($this->data[$uniID]);//сносим ветку целиком
		(new Save())->save($this->data);//пишем в файл
		$this->cleanPhotos($uniID);
		(new Admin())->LoggerCRM("очистил диалог $uniID");
		return true;
	}

	public function sendPhoto(string $uniID, string $name) : bool
	{
		if ($uniID === '' || $name === '') return false;//пустых не берем
		$this->push($uniID, 'user', 'admin', '', false, ['type' => 'image', 'file' => $name]);//фото, не текст
		return true;
	}

	public function replyPhoto(string $uniID, string $name) : bool
	{
		if ($uniID === '' || $name === '') return false;//пустых не берем
		$this->touch('admin');//админ тут
		$this->push($uniID, 'admin', 'user', '', false, ['type' => 'image', 'file' => $name]);//фото, не текст
		(new Admin())->LoggerCRM("отправил фото в чат $uniID");
		return true;
	}

	public function photoPath(string $name, string $uniID) : string
	{
		if (!preg_match('/^[A-Za-z0-9_-]+\.(jpg|jpeg|png|gif|webp)$/i', $name)) return '';//чужое имя не берем
		if ($uniID !== '' && !str_starts_with($name, $uniID . '_')) return '';//чужое фото не отдаем
		$path = self::$uploads . '/' . $name;
		return is_file($path) ? $path : '';
	}

	public function getUserInfo(string $uniID) : array
	{
		if ($uniID === '') return [];//пустого не берем
		$user = new GetUser($uniID);//готовый класс профиля
		if ($user->getUniID() === '') return [];//такого нет
		$sub = $user->getSubscription();
		$config = PriceConfig::getConfig();//готовые названия тарифов
		return [//только нужное для админки
			'name' => trim($user->getFirstName() . ' ' . $user->getLastName()),
			'email' => $user->getEmail(),
			'uniID' => $user->getUniID(),
			'status' => $user->getStatus(),
			'subscription' => $sub,
			'plan' => (string) ($config[$sub]['label'] ?? ''),//MYSELF, Family, Business...
			'days' => $user->getCountDays(),
			'devices' => $user->getCountDevices(),
			'refer' => $user->getRefer(),
			'myrefer' => $user->getMyRefer()
		];
	}

	// ==================== Внутреннее ====================

	private function dialog(string $uniID) : array
	{
		$dialog = $this->data[$uniID] ?? [];//диалог юзера
		return \is_array($dialog) ? $dialog : [];
	}

	private function store(string $uniID, array $dialog) : void
	{
		$this->data[$uniID] = $dialog;//кладем обратно
		(new Save())->save($this->data);//пишем в файл
	}

	private function push(string $uniID, string $sender, string $receiver, string $message, bool $read = false, array $extra = []) : void
	{
		if (!\is_array($this->data)) $this->data = [];//это массив
		$dialog = $this->dialog($uniID);
		$dialog[] = [//новое сообщение
			'id' => \count($dialog) + 1,
			'uniID' => $uniID,
			'sender_type' => $sender,
			'receiver_type' => $receiver,
			'message' => $message,
			'is_read' => $read,
			'created_at' => date('Y-m-d H:i:s')
		] + $extra;
		$this->store($uniID, $dialog);
	}

	private function isClosedDialog(array $dialog) : bool
	{
		$last = end($dialog);//последнее сообщение
		return \is_array($last) && ($last['sender_type'] ?? '') === 'system';//диалог завершен
	}

	private function presence() : array
	{
		if ($this->seen === null) {//читаем раз за запрос
			$this->seen = [];
			if (is_file(self::$presence)) {
				$all = json_decode((string) file_get_contents(self::$presence), true);
				if (\is_array($all)) $this->seen = $all;
			}
		}
		return $this->seen;
	}

	private function touch(string $id) : void
	{
		if ($id === '') return;//пустого не берем
		$all = $this->presence();
		$now = time();
		if (isset($all[$id]) && $now - (int) $all[$id] < 5) return;//были недавно, файл не трогаем
		$all[$id] = $now;//я тут
		foreach ($all as $key => $ts) {//старше суток выкидываем, файл не пухнет
			if (!is_int($ts) || $ts < $now - 86400) unset($all[$key]);
		}
		@file_put_contents(self::$presence, json_encode($all, JSON_UNESCAPED_UNICODE));
		$this->seen = $all;
	}

	private function cleanPhotos(string $uniID) : void
	{
		foreach (glob(self::$uploads . '/' . $uniID . '_*') ?: [] as $f) {//фото диалога больше не нужны
			if (is_file($f)) @unlink($f);
		}
	}
}