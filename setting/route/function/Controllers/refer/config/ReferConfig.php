<?php declare(strict_types=1);

namespace Setting\Route\Function\Controllers\refer\config;

/**
 * ReferConfig - Конфигурация реферальной системы
 * Как config.yml в плагинах Minecraft
 */
class ReferConfig
{
    /**
     * Получить настройки бонусов для нового реферала
     */
    public static function getNewReferralBonus(): array
    {
        return [
            'days_added' => 3,        // Дней подписки в подарок
            'discount_percent' => 10, // Скидка на первую покупку
        ];
    }
    
    /**
     * Получить настройки бонусов для пригласившего (реферрера)
     */
    public static function getReferrerBonus(): array
    {
        return [
            'days_per_referral' => 5,     // Дней за каждого приглашённого
            'bonus_percent' => 5,          // Процент бонуса к будущим покупкам
            'max_discount_cap' => 50,      // Максимальная скидка (%)
        ];
    }
    
    /**
     * Получить минимальную длину реферального кода
     */
    public static function getCodeLength(): int
    {
        return 10;
    }
    
    /**
     * Получить шаблон генерации кода
     */
    public static function getCodePattern(): string
    {
        return 'QWE#####';
    }
}
