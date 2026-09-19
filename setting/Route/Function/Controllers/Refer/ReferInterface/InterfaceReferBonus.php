<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer\ReferInterface;

//Контракт начисления бонусов.

interface InterfaceReferBonus
{
    public static function percentDays(int $days, int $percent): int;

    /** @return array{days:int,discount:int,uses:int} */
    public function giveToNewReferral(int $userId, int $referrerId): array;

    /** @return array{days:int,refer_count:int} */
    public function giveToReferrer(int $referrerId, int $newReferralId): array;

    /** @return array{given:bool,days:int,left:int} */
    public function grantPercentDays(string $buyerUniID, int $boughtDays): array;
}
