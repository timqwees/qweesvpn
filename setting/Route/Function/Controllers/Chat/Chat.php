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
		$this->touch($uniID);//я тут
		$this->markDialogRead($uniID, 'admin');//ответы админа считаем прочитанными
		return $this->dialog($uniID);
	}

	public function markRead(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		$this->touch($uniID);//я тут
		$this->markDialogRead($uniID, 'admin');
		return true;
	}

	// Пометить прочитанными входящие (от $from) — атомарно под flock
	private function markDialogRead(string $uniID, string $from) : void
	{
		$this->mutate(function (&$data) use ($uniID, $from) {
			if (!isset($data[$uniID]) || !\is_array($data[$uniID])) return;
			foreach ($data[$uniID] as $key => $value) {//пробежимся по сообщениям
				if (($value['sender_type'] ?? '') === $from && empty($value['is_read'])) {
					$data[$uniID][$key]['is_read'] = true;
				}
			}
		});
	}

	public function getTotalUnread() : int
	{
		if (!\is_array($this->data)) return 0;//это массив
		$total = 0;
		foreach ($this->data as $uniID => $dialog) {//пробежимся по диалогам
			if (!\is_array($dialog) || empty($dialog)) continue;//пустых не берем
			if ($this->isClosedDialog($dialog)) continue;//закрытые не считаем
			foreach ($dialog as $value) {//непрочитанное от юзеров
				if (($value['sender_type'] ?? '') === 'user' && empty($value['is_read'])) $total++;
			}
		}
		return $total;
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
		$this->touch('admin');//админ тут
		$this->markDialogRead($uniID, 'user');//написанное юзером считаем прочитанным
		return $this->dialog($uniID);
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
		$state = $this->mutate(function (&$data) use ($uniID) {
			$dialog = (isset($data[$uniID]) && \is_array($data[$uniID])) ? $data[$uniID] : [];
			if (empty($dialog)) return 'empty';//пустых не берем
			$last = end($dialog);
			if (\is_array($last) && ($last['sender_type'] ?? '') === 'system') return 'already';//уже закрыт
			$data[$uniID][] = $this->makeMessage($dialog, 'system', 'user', 'closed', true);//системная пометка, не сообщение
			return 'ok';
		});
		if ($state === 'empty') return false;
		if ($state !== 'ok') return true;//уже закрыт, фото и лог не трогаем
		$this->cleanPhotos($uniID);
		(new Admin())->LoggerCRM("завершил диалог $uniID");
		return true;
	}

	public function clearDialog(string $uniID) : bool
	{
		if ($uniID === '') return false;//пустого не берем
		$existed = $this->mutate(function (&$data) use ($uniID) {
			if (!isset($data[$uniID])) return false;//уже пусто
			unset($data[$uniID]);//сносим ветку целиком
			return true;
		});
		if (!$existed) return true;
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
		if (!preg_match('/^[A-Za-z0-9_-]+\.(jpg|jpeg|png|gif|webp|heic|heif)$/i', $name)) return '';//чужое имя не берем
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
		if (!\is_array($this->data)) return [];
		$dialog = $this->data[$uniID] ?? [];//диалог юзера
		return \is_array($dialog) ? $dialog : [];
	}

	// Атомарное чтение-изменение-запись chats.json под LOCK_EX.
	// Лечит lost update: параллельные опросы юзера и админа + медленная
	// отправка фото больше не затирают друг друга. Локи не вкладываем:
	// внутри $fn нельзя вызывать touch()/mutate().
	private function mutate(callable $fn)
	{
		$file = self::$file;
		$dir = dirname($file);
		if ($dir !== '' && !is_dir($dir)) mkdir($dir, 0755, true);
		$fp = @fopen($file, 'c+');
		if ($fp === false) {//файл недоступен — старое поведение из памяти
			$data = \is_array($this->data) ? $this->data : [];
			$result = $fn($data);
			$this->data = $data;
			(new Save())->save($this->data);
			return $result;
		}
		flock($fp, LOCK_EX);
		$data = json_decode((string) stream_get_contents($fp), true);
		if (!\is_array($data)) $data = [];
		$result = $fn($data);
		ftruncate($fp, 0);
		rewind($fp);
		fwrite($fp, (string) json_encode($data, JSON_UNESCAPED_UNICODE));
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);
		$this->data = $data;//обновляем кэш свежими данными
		return $result;
	}

	private function makeMessage(array $dialog, string $sender, string $receiver, string $message, bool $read = false, array $extra = []) : array
	{
		$lastId = 0;
		foreach ($dialog as $m) $lastId = max($lastId, (int) ($m['id'] ?? 0));//max, а не count — переживает параллельные вставки
		return [
			'id' => $lastId + 1,
			'uniID' => '',
			'sender_type' => $sender,
			'receiver_type' => $receiver,
			'message' => $message,
			'is_read' => $read,
			'created_at' => date('Y-m-d H:i:s')
		] + $extra;
	}

	private function push(string $uniID, string $sender, string $receiver, string $message, bool $read = false, array $extra = []) : void
	{
		$this->mutate(function (&$data) use ($uniID, $sender, $receiver, $message, $read, $extra) {
			$dialog = (isset($data[$uniID]) && \is_array($data[$uniID])) ? $data[$uniID] : [];
			$msg = $this->makeMessage($dialog, $sender, $receiver, $message, $read, $extra);
			$msg['uniID'] = $uniID;
			$dialog[] = $msg;//новое сообщение
			$data[$uniID] = $dialog;
		});
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
		$fp = @fopen(self::$presence, 'c+');
		if ($fp === false) return;
		flock($fp, LOCK_EX);//онлайн-метки тоже правим атомарно
		$cur = json_decode((string) stream_get_contents($fp), true);
		if (!\is_array($cur)) $cur = [];
		$cur[$id] = $now;//я тут
		foreach ($cur as $key => $ts) {//старше суток выкидываем, файл не пухнет
			if (!is_int($ts) || $ts < $now - 86400) unset($cur[$key]);
		}
		ftruncate($fp, 0);
		rewind($fp);
		fwrite($fp, (string) json_encode($cur, JSON_UNESCAPED_UNICODE));
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);
		$this->seen = $cur;
	}

	private function cleanPhotos(string $uniID) : void
	{
		foreach (glob(self::$uploads . '/' . $uniID . '_*') ?: [] as $f) {//фото диалога больше не нужны
			if (is_file($f)) @unlink($f);
		}
	}
}