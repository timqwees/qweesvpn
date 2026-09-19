<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer\Config;

//Простой конфиг рефералки: всё лежит в refer.json рядом, правится из админки.
//Нет файла — берутся значения по умолчанию. Код ничего не знает про админку.

class ReferConfig
{
    public const CODE_LENGTH = 10;   // полная длина кода
    public const CODE_PREFIX = 'QWE'; // нестираемый префикс
    public const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // без 0/O, 1/I

    private const FILE = __DIR__ . '/refer.json';
    private static ?array $data = null;

    private static function defaults(): array
    {
        return [
            'enabled' => true,       // рефералка вкл/выкл целиком
            'referral_days' => 3,    // дни приглашённому
            'referral_discount' => 10, // скидка приглашённому, %
            'discount_uses' => 5,    // на сколько покупок хватает скидки
            'referrer_days' => 3,    // дни пригласившему за каждого
            'referrer_percent' => 5, // % от дней каждой покупки приглашённого — себе
            'referrer_takes' => 5,   // сколько покупок каждого приглашённого дают %
        ];
    }

    /** Все настройки одним массивом (для админки). */
    public static function getAll(): array
    {
        if (self::$data === null) {
            $data = self::defaults();
            if (is_file(self::FILE)) {
                $json = json_decode((string) file_get_contents(self::FILE), true);
                if (is_array($json)) {
                    $data = array_merge($data, $json);
                }
            }
            self::$data = $data;
        }
        return self::$data;
    }

    /** Сохранить настройки (вызывает админка). Лишние ключи игнорируются. */
    public static function save(array $input): bool
    {
        $cur = self::getAll();
        $data = [
            'enabled' => !empty($input['enabled']),
            'referral_days' => max(0, (int) ($input['referral_days'] ?? $cur['referral_days'])),
            'referral_discount' => min(100, max(0, (int) ($input['referral_discount'] ?? $cur['referral_discount']))),
            'discount_uses' => min(100, max(1, (int) ($input['discount_uses'] ?? $cur['discount_uses']))),
            'referrer_days' => max(0, (int) ($input['referrer_days'] ?? $cur['referrer_days'])),
            'referrer_percent' => min(100, max(0, (int) ($input['referrer_percent'] ?? $cur['referrer_percent']))),
            'referrer_takes' => min(100, max(1, (int) ($input['referrer_takes'] ?? $cur['referrer_takes']))),
        ];
        $ok = (bool) file_put_contents(self::FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($ok) {
            self::$data = $data;
        }
        return $ok;
    }

    public static function isEnabled(): bool
    {
        return (bool) (self::getAll()['enabled'] ?? true);
    }

    /** На сколько покупок хватает скидки приглашённого. */
    public static function getDiscountUses(): int
    {
        return max(1, (int) (self::getAll()['discount_uses'] ?? 5));
    }

    public static function getNewReferralBonus(): array
    {
        $c = self::getAll();
        return [
            'days_added' => (int) $c['referral_days'],
            'discount_percent' => (int) $c['referral_discount'],
            'discount_uses' => self::getDiscountUses(),
        ];
    }

    public static function getReferrerBonus(): array
    {
        $c = self::getAll();
        return [
            'days_per_referral' => (int) $c['referrer_days'],
            'percent' => min(100, max(0, (int) $c['referrer_percent'])),
            'takes' => max(1, (int) $c['referrer_takes']),
        ];
    }

    public static function getCodeLength(): int
    {
        return self::CODE_LENGTH;
    }

    public static function getCodePrefix(): string
    {
        return self::CODE_PREFIX;
    }

    public static function getCodeAlphabet(): string
    {
        return self::CODE_ALPHABET;
    }
}
