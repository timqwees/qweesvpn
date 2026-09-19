<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer\Bonus;

use App\Config\Database;
use Setting\Route\Function\Controllers\Refer\Config\ReferConfig;
use Setting\Route\Function\Controllers\Refer\ReferInterface\{InterfaceReferBonus, InterfaceReferLog};
use Setting\Route\Function\Controllers\Refer\Tools\{ReferLog, ReferRepository};
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;
use DateTime, DateTimeZone;

//Только начисление бонусов. Данные — ReferRepository, события — ReferLog.

class Bonus implements InterfaceReferBonus
{
    private ReferRepository $repo;
    private InterfaceReferLog $log;
    private Xray $xray;

    public function __construct(?ReferRepository $repo = null, ?InterfaceReferLog $log = null, ?Xray $xray = null)
    {
        $this->log = $log ?? new ReferLog();
        $this->repo = $repo ?? new ReferRepository($this->log);
        $this->xray = $xray ?? new Xray();
    }

    /**
     * Автопересчёт: N% от количества дней. 5% от 30 = 2 (округление, минимум 1).
     */
    public static function percentDays(int $days, int $percent): int
    {
        if ($days <= 0 || $percent <= 0) {
            return 0;
        }
        return max(1, (int) round($days * $percent / 100));
    }

    /** Бонус новому рефералу: дни + скидка на N покупок. */
    public function giveToNewReferral(int $userId, int $referrerId): array
    {
        $config = ReferConfig::getNewReferralBonus();
        $days = (int) ($config['days_added'] ?? 0);
        $discount = (int) ($config['discount_percent'] ?? 0);
        $uses = (int) ($config['discount_uses'] ?? 0);

        $daysResult = $this->addBonusDays($userId, $days);
        $appliedDiscount = $discount > 0 ? $this->repo->applyReferralDiscount($userId, $discount, $uses) : 0;

        $this->log->log('БОНУС-РЕФЕРАЛУ', [
            'user_id' => $userId,
            'referrer_id' => $referrerId,
            'days' => $daysResult['added'] ? $days : 0,
            'discount' => $appliedDiscount,
            'discount_uses' => $appliedDiscount > 0 ? $uses : 0,
        ]);

        return ['days' => $daysResult['added'] ? $days : 0, 'discount' => $appliedDiscount, 'uses' => $appliedDiscount > 0 ? $uses : 0];
    }

    /** Бонус пригласившему за нового: дни + счётчик. */
    public function giveToReferrer(int $referrerId, int $newReferralId): array
    {
        $config = ReferConfig::getReferrerBonus();
        $days = (int) ($config['days_per_referral'] ?? 0);

        $daysResult = $this->addBonusDays($referrerId, $days);
        $count = $this->repo->incrementReferrer($referrerId);

        $this->log->log('БОНУС-РЕФЕРЕРУ', [
            'referrer_id' => $referrerId,
            'new_referral_id' => $newReferralId,
            'days' => $daysResult['added'] ? $days : 0,
            'refer_count' => $count,
        ]);

        return ['days' => $daysResult['added'] ? $days : 0, 'refer_count' => $count];
    }

    /**
     * % пригласившему с покупки приглашённого.
     * Вызывается из Kassa после успешной выдачи подписки.
     * @return array{given:bool,days:int,left:int} left — осталось снятий % с этого приглашённого
     */
    public function grantPercentDays(string $buyerUniID, int $boughtDays): array
    {
        $buyer = $this->repo->findUserByUniID($buyerUniID);
        $referrerId = (int) ($buyer['refer_id'] ?? 0);
        if ($buyer === null || $referrerId <= 0 || $boughtDays <= 0) {
            return ['given' => false, 'days' => 0, 'left' => 0];
        }

        $config = ReferConfig::getReferrerBonus();
        $days = self::percentDays($boughtDays, (int) ($config['percent'] ?? 0));
        if ($days <= 0) {
            return ['given' => false, 'days' => 0, 'left' => 0];//процент выключен — take не жжём
        }

        $this->repo->backfillHistory((int) ($config['takes'] ?? 5));
        $left = $this->repo->useTake((string) ($buyer['uniID'] ?? ''));
        if ($left < 0) {
            return ['given' => false, 'days' => 0, 'left' => 0];
        }

        $this->addBonusDays($referrerId, $days);

        $this->log->log('БОНУС-%-С-ПОКУПКИ', [
            'buyer_uniID' => $buyerUniID,
            'bought_days' => $boughtDays,
            'referrer_id' => $referrerId,
            'percent' => (int) ($config['percent'] ?? 0),
            'days' => $days,
            'takes_left' => $left,
        ]);

        return ['given' => true, 'days' => $days, 'left' => $left];
    }

    /**
     * Добавить бонусные дни (панель 3x-ui + БД).
     * Подписки нет — создаём pending-строку, дни не сгорают.
     * @return array{added:bool,expiry:int,pending:bool}
     */
    private function addBonusDays(int $userId, int $days): array
    {
        if ($days <= 0) {
            return ['added' => false, 'expiry' => 0, 'pending' => false];
        }
        $user = $this->repo->findUserById($userId);
        if ($user === null || empty($user['uniID'])) {
            $this->log->log('БОНУС-ОШИБКА', ['user_id' => $userId, 'days' => $days, 'reason' => 'пользователь не найден']);
            return ['added' => false, 'expiry' => 0, 'pending' => false];
        }
        $uniID = (string) $user['uniID'];

        $sub = Database::send('SELECT uniID FROM qwees_subscriptions WHERE uniID = ? LIMIT 1', [$uniID]);
        if (!\is_array($sub) || $sub === [] || empty($sub[0]['uniID'])) {
            $expiry = $this->nowMs() + $days * 86400000;
            Database::send(
                "INSERT INTO qwees_subscriptions (uniID, status, subscription, count_days, expiry) VALUES (?, 'off', 'bonus', ?, ?)",
                [$uniID, $days, $expiry]
            );
            $this->log->log('БОНУС-ДНИ', ['uniID' => $uniID, 'days' => $days, 'mode' => 'pending(подписки не было)']);
            return ['added' => true, 'expiry' => $expiry, 'pending' => true];
        }

        $vpn = $this->xray->xui_update($uniID, $days);
        if (($vpn['status'] ?? '') !== 'ok') {
            $expiry = $this->extendExpiryDbOnly($uniID, $days);
            $this->log->log('БОНУС-ДНИ', ['uniID' => $uniID, 'days' => $days, 'mode' => 'только БД', 'expiry' => $expiry]);
            return ['added' => true, 'expiry' => $expiry, 'pending' => false];
        }

        $row = Database::send('SELECT expiry FROM qwees_subscriptions WHERE uniID = ? LIMIT 1', [$uniID]);
        $expiry = (\is_array($row) && isset($row[0])) ? (int) ($row[0]['expiry'] ?? 0) : 0;
        $this->log->log('БОНУС-ДНИ', ['uniID' => $uniID, 'days' => $days, 'mode' => 'панель+БД']);
        return ['added' => true, 'expiry' => $expiry, 'pending' => false];
    }

    private function extendExpiryDbOnly(string $uniID, int $days): int
    {
        $result = Database::send('SELECT expiry FROM qwees_subscriptions WHERE uniID = ? LIMIT 1', [$uniID]);
        $current = (\is_array($result) && isset($result[0])) ? (int) ($result[0]['expiry'] ?? 0) : 0;
        $new = max($this->nowMs(), $current) + $days * 86400000;
        Database::send('UPDATE qwees_subscriptions SET expiry = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?', [$new, $uniID]);
        return $new;
    }

    private function nowMs(): int
    {
        return (new DateTime('now', new DateTimeZone('Europe/Moscow')))->getTimestamp() * 1000;
    }
}
