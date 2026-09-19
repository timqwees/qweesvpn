<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer\ReferInterface;

//SOLID: ISP — контракт фасада реферальной системы (как InterfaceGroup в Admin/Group)

interface InterfaceRefer
{
    /**
     * Активировать чужой реферальный код для пользователя.
     * @return array{status:bool,message?:string,error?:string}
     */
    public function activate(string $code, int $userId): array;

    /**
     * Сгенерировать уникальный код для нового пользователя.
     */
    public function generateCode(): string;

    /**
     * Список приглашённых пользователем (для кабинета).
     * @return array<int, array{name:string,email:string,date:string}>
     */
    public function getMyReferrals(int $userId, int $limit = 50): array;

    /**
     * Начислить пригласившему % с покупки приглашённого.
     * @return array{given:bool,days:int,left:int}
     */
    public function rewardReferrerFromPurchase(string $buyerUniID, int $boughtDays): array;
}
