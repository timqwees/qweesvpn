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

namespace Setting\Route\Function\Controllers\Admin\Group;

use Setting\Route\Function\Controllers\Admin\{AdminInterface\InterfaceGroup, Group\Tools\Save, Group\Tools\Load, Group\Tools\GetUsers};

class Groups implements InterfaceGroup
{
	public static string $file = __DIR__ . '/Permissions/permissions.json';//абсолютный путь: относительный ломался при другом CWD и плодил копии
	public array $data;

	public function __construct()
	{
		(new Load())->load();
		$this->data = (new GetUsers())->getUsers();
	}

	// ==================== Основные методы ====================

	public function addPermission(string $username, string $permission) : bool
	{
		if (!\is_array($this->data)) $this->data = [];//это массив
		foreach ($this->data as $key => $value) {//пробежимся по каждому пользователю
			if (strtolower($value['username']) === strtolower($username)) {//пользователь нужный
				if (!isset($value['permissions'][$permission])){//такого права еще нет
					$full = \Setting\Route\Function\Controllers\Admin\Admin::FULL_PERMISSIONS;
					$this->data[$key]['permissions'][$permission] = $full[$permission] ?? $permission;//ключ => описание
					(new Save())->save($this->data);
					return true;
				}
			}
		}
		return false;
	}

	public function removePermission(string $username, string $permission) : bool
	{
		if (!\is_array($this->data)) $this->data = [];//это массив
		foreach ($this->data as $key => $value) {//пробежимся по каждому пользователю
			if (strtolower($value['username']) === strtolower($username)) {//пользователь нужный
				if (isset($value['permissions'][$permission])){//право есть
					unset($this->data[$key]['permissions'][$permission]);
					(new Save())->save($this->data);
					return true;
				}
			}
		}
		return false;
	}

	public function hasPermission(string $username): array
	{
		if (!\is_array($this->data)) $this->data = [];//это массив
		foreach ($this->data as $key => $value) {//пробежимся по каждому пользователю
			if (strtolower($value['username']) === strtolower($username)) {//пользователь нужный
				return array_keys((array) $this->data[$key]['permissions']);//только ключи прав
			}
		}
		return [];
	}

	public function isPermission(string $username, string $permission):  bool|int
	{
		if (!\is_array($this->data)) $this->data = [];//это массив
		foreach ($this->data as $key => $value) {//пробежимся по каждому пользователю
			if (strtolower($value['username']) === strtolower($username)) {//пользователь нужный
				return isset($value['permissions'][$permission]) ? 1 : 0;//ищем ключ
			}
		}
		return 0;
	}
}