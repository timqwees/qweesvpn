<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Kassa;

use Setting\Route\Function\Controllers\Client\GetUser;

/**
 * PriceConfig - Конфигурация тарифов
 *
 * ЕДИНЫЙ объект: тариф => всё его настройки в одном массиве.
 * Добавил период в periods - и он появится на странице оплаты, в выдаче, в описаниях чеков.
 */
class PriceConfig
{
	/**
	 * Вся конфигурация одним объектом:
	 * тариф (name из БД) => label для витрины, лимит устройств,
	 * desc - ключ перевода описания (device_1/device_4/device_10 в Language.php),
	 * periods: сроки (месяцев) => дней выдачи + цена за месяц
	 */
	private static array $config = [
		'basic' => [
			'label'   => 'MYSELF',
			'devices' => 1,
			'desc'    => 'device_1',
			'periods' => [
				 1 => ['days' => 30, 'price' => 150],
				 3 => ['days' => 90, 'price' => 135],
				 6 => ['days' => 180, 'price' => 120],
				12 => ['days' => 365, 'price' => 99],
			],
		],
		'clasic' => [
			'label'   => 'Family',
			'devices' => 4,
			'desc'    => 'device_4',
			'periods' => [
				 1 => ['days' => 30, 'price' => 180],
				 3 => ['days' => 90, 'price' => 165],
				 6 => ['days' => 180, 'price' => 150],
				12 => ['days' => 365, 'price' => 129],
			],
		],
		'pro' => [
			'label'   => 'Business',
			'devices' => 10,
			'desc'    => 'device_10',
			'periods' => [
				 1 => ['days' => 30, 'price' => 200],
				 3 => ['days' => 90, 'price' => 185],
				 6 => ['days' => 180, 'price' => 170],
				12 => ['days' => 365, 'price' => 149],
			],
		],
	];

	/**
	 * Получить весь объект тарифов
	 */
	public static function getConfig(): array
	{
		return self::$config;
	}

	/**
	 * Получить все цены в виде объекта:
	 * [1 => ['basic' => 150, 'clasic' => 180, 'pro' => 200], 3 => [...], 6 => [...], 12 => [...]]
	 * 
	 * @param bool $applyReferralDiscount Применить реферальную скидку (10%)
	 * @return array Массив [period_months => [tariff_name => price_per_month]]
	 */
	public static function getPrices(bool $applyReferralDiscount = false): array
	{
		$prices = [];

		foreach (self::$config as $tariffName => $meta) {
			foreach ($meta['periods'] as $months => $period) {
				$price = $period['price'];

				if ($applyReferralDiscount) {
					$price = (int) round($price * 0.9);
				}

				$prices[$months][$tariffName] = $price;
			}
		}

		ksort($prices);
		return $prices;
	}

	/**
	 * Проверить, есть ли у текущего пользователя реферальная скидка
	 */
	public static function hasReferralDiscount(): bool
	{
		$user = new GetUser();
		return $user->getDiscountPercent() > 0;
	}

	/**
	 * Получить метаданные тарифов (label/devices/desc)
	 */
	public static function getTariffMeta(): array
	{
		$meta = [];
		foreach (self::$config as $tariffName => $tariff) {
			$meta[$tariffName] = [
				'label'   => $tariff['label'],
				'devices' => $tariff['devices'],
				'desc'    => $tariff['desc'],
			];
		}
		return $meta;
	}

	/**
	 * Получить конфигурацию тарифов для выдачи подписки:
	 * ['1month_1' => ['months' => 1, 'days' => 30, 'devices' => 1, 'tariff' => 'basic'], ...]
	 */
	public static function getTariffConfig(): array
	{
		$config = [];
		foreach (self::$config as $tariffName => $tariff) {
			foreach ($tariff['periods'] as $months => $period) {
				$key = $months . 'month' . ($months > 1 ? 's' : '') . '_' . $tariff['devices'];
				$config[$key] = [
					'months'  => $months,
					'days'    => $period['days'],
					'devices' => $tariff['devices'],
					'tariff'  => $tariffName,
				];
			}
		}
		return $config;
	}
}
