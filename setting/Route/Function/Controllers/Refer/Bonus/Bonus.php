<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer\Bonus;

use App\Config\Database;
use Setting\Route\Function\Controllers\Refer\Config\ReferConfig;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;
use DateTime, DateTimeZone;
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
            Database::send("UPDATE qwees_users SET discount_percent = ? WHERE id = ?", [$config['discount_percent'], $userId]);
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
            UPDATE qwees_users 
            SET refer_count = refer_count + 1,
                bonus_percent = MIN(bonus_percent + ?, ?)
            WHERE id = ?
        ", [$config['bonus_percent'], $config['max_discount_cap'], $referrerId]);
    }

    /**
     * Добавить бонусные дни к подписке пользователя (панель 3x-ui + БД).
     */
    private function addBonusDays(int $userId, int $days): void
    {
        if ($days <= 0) {
            return;
        }
        $users = Database::send("SELECT uniID FROM qwees_users WHERE id = ?", [$userId]);
        if (!is_array($users) || $users === [] || empty($users[0]['uniID'])) {
            return;
        }
        $uniID = (string) $users[0]['uniID'];

        $sub = Database::send('SELECT uniID FROM qwees_subscriptions WHERE uniID = ? LIMIT 1', [$uniID]);
        if (!is_array($sub) || $sub === [] || empty($sub[0]['uniID'])) {
            return;
        }

        $xray = new Xray();
        $vpn = $xray->xui_update($uniID, $days);
        if (($vpn['status'] ?? '') !== 'ok') {
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                sprintf(
                    "[%s] [РЕФЕРАЛ-БОНУС] xui_update для %s не удалось: %s — продление только в БД\n",
                    date('Y-m-d H:i:s'),
                    $uniID,
                    (string) ($vpn['message'] ?? json_encode($vpn, JSON_UNESCAPED_UNICODE))
                ),
                FILE_APPEND
            );
            $this->extendSubscriptionExpiryDbOnly($uniID, $days);
        }
    }

    /** Резерв: продлить expiry (мс) в БД без панели (как в старой логике). */
    private function extendSubscriptionExpiryDbOnly(string $uniID, int $days): void
    {
        $result = Database::send('SELECT expiry FROM qwees_subscriptions WHERE uniID = ? LIMIT 1', [$uniID]);
        $currentExpiry = (is_array($result) && $result !== []) ? (int) ($result[0]['expiry'] ?? 0) : 0;
        $nowMs = (new DateTime('now', new DateTimeZone('Europe/Moscow')))->getTimestamp() * 1000;
        $newExpiry = max($nowMs, $currentExpiry) + $days * 86400000;
        Database::send(
            'UPDATE qwees_subscriptions SET expiry = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?',
            [$newExpiry, $uniID]
        );
    }

    /**
     * Получить общую скидку пользователя (реферальная + другие)
     */
    public static function getTotalDiscount(int $userId): int
    {
        $result = Database::send("SELECT discount_percent FROM qwees_users WHERE id = ?", [$userId]);
        return (int) ((is_array($result) && isset($result[0])) ? ($result[0]['discount_percent'] ?? 0) : 0);
    }

    /**
     * Получить бонусный процент пользователя
     */
    public static function getBonusPercent(int $userId): int
    {
        $result = Database::send("SELECT bonus_percent FROM qwees_users WHERE id = ?", [$userId]);
        return (int) ((is_array($result) && isset($result[0])) ? ($result[0]['bonus_percent'] ?? 0) : 0);
    }
}
