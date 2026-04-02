<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\refer\config;

class ReferConfig
{
    /**
     * Получает процент бонусных дней для реферала
     */
    public static function getReferralDaysBonus(): int
    {
        return intval($_ENV['REFER_REFERRAL_DAYS_PERCENT'] ?? 10);
    }

    /**
     * Получает процент скидки для приглашенного пользователя
     */
    public static function getInvitedDiscountPercent(): int
    {
        return intval($_ENV['REFER_INVITED_DISCOUNT_PERCENT'] ?? 10);
    }

    /**
     * Получает множитель для расчета скидки (1.0 - 0.10 = 0.90)
     */
    public static function getInvitedDiscountMultiplier(): float
    {
        $discount = self::getInvitedDiscountPercent();
        return 1.0 - ($discount / 100.0);
    }

    /**
     * Получает все конфигурационные параметры реферальной системы
     */
    public static function getAllConfig(): array
    {
        return [
            'referral_days_percent' => self::getReferralDaysBonus(),
            'invited_discount_percent' => self::getInvitedDiscountPercent(),
            'invited_discount_multiplier' => self::getInvitedDiscountMultiplier(),
        ];
    }

    /**
     * Проверяет валидность конфигурации
     */
    public static function validateConfig(): array
    {
        $errors = [];
        $config = self::getAllConfig();

        if ($config['referral_days_percent'] < 0 || $config['referral_days_percent'] > 100) {
            $errors[] = 'REFER_REFERRAL_DAYS_PERCENT должен быть между 0 и 100';
        }

        if ($config['invited_discount_percent'] < 0 || $config['invited_discount_percent'] > 100) {
            $errors[] = 'REFER_INVITED_DISCOUNT_PERCENT должен быть между 0 и 100';
        }

        return $errors;
    }
}
