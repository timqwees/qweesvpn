<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Profile;

use App\Config\Database;

// От класса осталась только живая статика (имя пригласившего для кабинета).
// Инстанс-методы и конструктор с лишним запросом удалены как мёртвые.

class Profile
{
    /**
     * Статический метод для получения имени реферера
     */
    public static function getReferrerNameStatic(string $referCode): string
    {
        return self::_referrerName($referCode);
    }

    private static function _referrerName(string $referCode): string
    {
        if (empty($referCode)) {
            return '';
        }
        $result = Database::send('SELECT first_name, last_name FROM qwees_users WHERE myrefer = ? LIMIT 1', [$referCode]);
        if (!empty($result[0])) {
            return trim(($result[0]['first_name'] ?? '') . ' ' . ($result[0]['last_name'] ?? ''));
        }
        return '';
    }
}
