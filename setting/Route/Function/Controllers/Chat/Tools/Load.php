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

namespace Setting\Route\Function\Controllers\Chat\Tools;

use Setting\Route\Function\Controllers\Chat\AdminInterface\InterfaceChatLoad;
use Setting\Route\Function\Controllers\Chat\Tools\Save;

class Load implements InterfaceChatLoad
{

	private array $data = [];
	private string $file;

	public function __construct()
	{
		$this->file = \Setting\Route\Function\Controllers\Chat\Chat::$file ?? '';
	}

	public function load(): void
	{
		if (!\is_file($this->file)) {
			$this->data = [];
			(new Save())->save($this->data); // создаём файл
			return;
		}

		$contents = file_get_contents($this->file);
		if ($contents === false || trim((string)$contents) === ''){//существует, но пустой
			$this->data = [];
			(new Save())->save($this->data);
			return;
		}

		$decoded = json_decode($contents, true);
		$this->data = \is_array($decoded) ? $decoded : [];
	}

	public function getData(): array
	{
		return $this->data;
	}
}