<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer\ReferInterface;

//Контракт доступа к данным рефералки. Вся SQL — в ReferRepository.

interface InterfaceReferRepository
{
    public function ensureTable(): void;

    public function ensureUserColumns(): void;

    public function ensureIndexes(): void;

    public function findUserByCode(string $code): ?array;

    public function findUserById(int $userId): ?array;

    public function findUserByUniID(string $uniID): ?array;

    public function bindReferral(int $userId, string $code, int $referrerId): bool;

    public function recordReferral(array $row): bool;

    /** Дотянуть историю по старым привязкам. Возвращает число добавленных. */
    public function backfillHistory(int $takes): int;

    /** Строка пары «реферер — приглашённый» (там счётчик takes_left). */
    public function getReferralRow(string $referralUniID): ?array;

    /** Потратить одно снятие % с покупок. Возвращает остаток, -1 — нечего. */
    public function useTake(string $referralUniID): int;

    /** +1 приглашённый. Возвращает новый счётчик. */
    public function incrementReferrer(int $referrerId): int;

    public function applyReferralDiscount(int $userId, int $percent, int $uses = 0): int;

    /** @return array{used:bool,left:int} */
    public function useDiscountByUniID(string $uniID): array;

    /** @return array<int, array> */
    public function listReferrals(int $referrerId, int $limit = 50): array;

    public function countReferrals(int $referrerId): int;
}
