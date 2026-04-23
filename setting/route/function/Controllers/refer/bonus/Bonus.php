<?php declare(strict_types=1);

namespace Setting\Route\Function\Controllers\refer\bonus;

use App\Config\Database;
use Setting\Route\Function\Controllers\refer\config\ReferConfig;

/**
 * Bonus - Обработка бонусов реферальной системы
 * Простая логика начисления наград
 */
class Bonus
{
    /**
     * Дать бонус новому рефералу (кто пришёл по ссылке)
     */
    public function giveToNewReferral(int $userId, int $referrerId): void
    {
        $config = ReferConfig::getNewReferralBonus();

        // Добавляем бонусные дни к подписке
        if ($config['days_added'] > 0) {
            $this->addBonusDays($userId, $config['days_added']);
        }

        // Записываем скидку в профиль
        if ($config['discount_percent'] > 0) {
            Database::send("UPDATE users SET discount_percent = ? WHERE id = ?", [$config['discount_percent'], $userId]);
        }
    }

    /**
     * Дать бонус пригласившему (реферреру)
     */
    public function giveToReferrer(int $referrerId, int $newReferralId): void
    {
        $config = ReferConfig::getReferrerBonus();

        // Добавляем бонусные дни пригласившему
        if ($config['days_per_referral'] > 0) {
            $this->addBonusDays($referrerId, $config['days_per_referral']);
        }

        // Увеличиваем счётчик и бонус (с ограничением максимума)
        Database::send("
            UPDATE users 
            SET refer_count = refer_count + 1,
                bonus_percent = MIN(bonus_percent + ?, ?)
            WHERE id = ?
        ", [$config['bonus_percent'], $config['max_discount_cap'], $referrerId]);
    }

    /**
     * Добавить бонусные дни к подписке пользователя
     */
    private function addBonusDays(int $userId, int $days): void
    {
        // Получаем текущую дату окончания
        $result = Database::send("SELECT date_end FROM users WHERE id = ?", [$userId]);
        $currentEnd = $result['date_end'] ?? null;

        // Если подписка активна - продлеваем, если нет - отсчёт от сегодня
        if ($currentEnd && strtotime($currentEnd) > time()) {
            $newEnd = date('Y-m-d H:i:s', strtotime($currentEnd . " +{$days} days"));
        } else {
            $newEnd = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }

        Database::send("UPDATE users SET date_end = ? WHERE id = ?", [$newEnd, $userId]);
    }

    /**
     * Получить общую скидку пользователя (реферальная + другие)
     */
    public static function getTotalDiscount(int $userId): int
    {
        $result = Database::send("SELECT discount_percent FROM users WHERE id = ?", [$userId]);
        return (int) ($result['discount_percent'] ?? 0);
    }

    /**
     * Получить бонусный процент пользователя
     */
    public static function getBonusPercent(int $userId): int
    {
        $result = Database::send("SELECT bonus_percent FROM users WHERE id = ?", [$userId]);
        return (int) ($result['bonus_percent'] ?? 0);
    }
}
