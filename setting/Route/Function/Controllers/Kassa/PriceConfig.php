<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Kassa;

use App\Config\Database;
use Setting\Route\Function\Controllers\Refer\Bonus\Bonus;
use Setting\Route\Function\Controllers\Client\GetUser;

/**
 * PriceConfig - Формирование цен из базы данных
 * 
 * Логика:
 * 1. Запрос в БД → получение базовых цен (1 месяц)
 * 2. Математический расчёт цен для каждого срока (6м, 12м)
 * 3. Проверка реферальной скидки → применение 10% скидки на итоговую стоимость
 */
class PriceConfig
{
	/**
	 * Фиксированная скидка с ежемесячной цены по срокам
	 * Для каждого срока — одно и то же число вычитается из базовой цены для ВСЕХ тарифов
	 */
	private static array $periodDiscounts = [
		1  => 0,   // 1 месяц — без скидки
		6  => 30,  // 6 месяцев — -30₽/мес от базовой цены
		12 => 51,  // 12 месяцев — -51₽/мес от базовой цены
	];

	/**
	 * Названия тарифов (ключ = name из БД)
	 * Порядок важен — используется для отображения
	 */
	private static array $tariffMeta = [
		'basic'  => ['label' => 'MYSELF',   'devices' => 1,  'desc' => '1 устройство (для себя)'],
		'clasic' => ['label' => 'Family',    'devices' => 4,  'desc' => '4 устройства (для семьи)'],
		'pro'    => ['label' => 'Business',  'devices' => 10, 'desc' => '10 устройств (для бизнеса)'],
	];

	/**
	 * Получить все цены в виде объекта:
	 * [1 => ['basic' => 150, 'clasic' => 180, 'pro' => 200], 6 => [...], 12 => [...]]
	 * 
	 * @param bool $applyReferralDiscount Применить реферальную скидку (10%) к итоговой стоимости
	 * @return array Массив [period_months => [tariff_name => price_per_month]]
	 */
	public static function getPrices(bool $applyReferralDiscount = false): array
	{
		$basePrices = self::fetchBasePricesFromDb();
		$prices = [];

		foreach (self::$periodDiscounts as $months => $discount) {
			$prices[$months] = [];
			foreach ($basePrices as $tariffName => $basePrice) {
				// Расчёт: базовая цена - фиксированная скидка за срок
				$pricePerMonth = $basePrice - $discount;

				if ($applyReferralDiscount) {
					// Реферальная скидка 10% от итоговой стоимости
					$totalForPeriod = $pricePerMonth * $months;
					$discountedTotal = $totalForPeriod - ($totalForPeriod * 0.10);
					$pricePerMonth = (int) round($discountedTotal / $months);
				}

				$prices[$months][$tariffName] = $pricePerMonth;
			}
		}

		return $prices;
	}

	/**
	 * Получить итоговую стоимость (за весь период)
	 * 
	 * @param int $months Количество месяцев
	 * @param string $tariffName Название тарифа (basic/clasic/pro)
	 * @param bool $applyReferralDiscount Применить реферальную скидку
	 * @return int Итого в рублях
	 */
	public static function getTotal(int $months, string $tariffName, bool $applyReferralDiscount = false): int
	{
		$prices = self::getPrices($applyReferralDiscount);
		$pricePerMonth = $prices[$months][$tariffName] ?? 0;
		return $pricePerMonth * $months;
	}

	/**
	 * Получить цену за месяц
	 */
	public static function getPricePerMonth(int $months, string $tariffName, bool $applyReferralDiscount = false): int
	{
		$prices = self::getPrices($applyReferralDiscount);
		return $prices[$months][$tariffName] ?? 0;
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
	 * Получить метаданные тарифов
	 */
	public static function getTariffMeta(): array
	{
		return self::$tariffMeta;
	}

	/**
	 * Получить фиксированные скидки по срокам
	 */
	public static function getPeriodDiscounts(): array
	{
		return self::$periodDiscounts;
	}

	/**
	 * Запрос базовых цен из БД (цены за 1 месяц без скидок)
	 * Таблица: qwees_price (id, name, price)
	 */
	private static function fetchBasePricesFromDb(): array
	{
		$rows = Database::send("SELECT name, price FROM qwees_price");
		$prices = [];

		if (is_array($rows) && $rows !== []) {
			foreach ($rows as $row) {
				$name = $row['name'] ?? '';
				$price = (int) ($row['price'] ?? 0);
				if ($name !== '' && $price > 0) {
					$prices[$name] = $price;
				}
			}
		}

		// Fallback если БД недоступна
		if (empty($prices)) {
			$prices = ['basic' => 150, 'clasic' => 180, 'pro' => 200];
		}

		return $prices;
	}
}
