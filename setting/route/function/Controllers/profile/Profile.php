<?php declare(strict_types=1);

namespace Setting\Route\Function\Controllers\profile;

use App\Config\Database;

class Profile
{
    private $user;

    public function __construct()
    {
        $this->user = new \Setting\Route\Function\Controllers\Client\getUser;
    }

    /**
     * Проверка наличия скидки
     */
    public function hasDiscount(): bool
    {
        return !empty($this->user->getRefer());
    }

    /**
     * Получить имя пригласившего по коду реферала
     */
    public function getReferrerName(): string
    {
        return self::_referrerName($this->user->getRefer());
    }

    /**
     * Статический метод для получения имени реферера
     */
    public static function getReferrerNameStatic(string $referCode): string
    {
        return self::_referrerName($referCode);
    }

    /**
     * Получить процент бонусных дней (для отображения)
     */
    public function getBonusDaysPercent(): int
    {
        return $this->user->getBonusPercent();
    }

    /**
     * Получить процент бонуса
     */
    public function getBonusPercent(): int
    {
        return $this->user->getBonusPercent();
    }

    public function getPricingInfo(): array
    {
        return self::_pricingInfo();
    }

    public function getUserProfile(): array
    {
        if (empty($this->user->getUniID())) {
            return self::getEmptyProfile();
        }

        return [
            'personal_info' => [
                'first_name' => $this->user->getFistName(),
                'last_name' => $this->user->getLastName(),
                'email' => $this->user->getEmail(),
                'uniID' => $this->user->getUniID(),
                'registration_date' => ''
            ],
            'subscription_info' => [
                'status' => $this->user->getStatus() === 'on' ? 'active' : 'inactive',
                'subscription' => $this->user->getSubscription(),
                'date_end' => $this->user->getDateEnd(),
                'count_days' => $this->user->getCountDays(),
                'count_devices' => $this->user->getCountDevices(),
                'amount' => $this->user->getAmount()
            ],
            'referal_info' => [
                'refer_link' => $this->user->getRefer(),
                'my_refer_link' => $this->user->getMyRefer(),
                'my_refer_url' => !empty($this->user->getMyRefer()) ? 'https://' . $_SERVER['HTTP_HOST'] . '/reflink=' . $this->user->getMyRefer() : '',
                'refer_count' => $this->user->getReferCount(),
                'has_discount' => $this->user->getDiscountPercent() > 0,
                'discount_percent' => $this->user->getDiscountPercent(),
                'bonus_percent' => $this->user->getBonusPercent(),
                'referrer_name' => self::_referrerName($this->user->getRefer())
            ],
            'pricing_info' => self::_pricingInfo()
        ];
    }

    private static function getEmptyProfile(): array
    {
        return [
            'personal_info' => ['first_name' => '', 'last_name' => '', 'email' => '', 'uniID' => '', 'registration_date' => ''],
            'subscription_info' => ['status' => 'inactive', 'subscription' => '', 'date_end' => '', 'count_days' => 0, 'count_devices' => 0, 'amount' => 0],
            'referal_info' => ['refer_link' => '', 'my_refer_link' => '', 'refer_count' => 0, 'has_discount' => false],
            'pricing_info' => self::_pricingInfo()
        ];
    }

    private static function _pricingInfo(): array
    {
        $prices = Database::send('SELECT * FROM qwees_price');
        $priceData = $prices[0] ?? [];

        return [
            'basic' => ['name' => 'Basic', 'price' => $priceData['basic'] ?? 100, 'days' => 30, 'devices' => 2],
            'classic' => ['name' => 'Classic', 'price' => $priceData['clasic'] ?? 200, 'days' => 30, 'devices' => 5],
            'pro' => ['name' => 'Pro', 'price' => $priceData['pro'] ?? 300, 'days' => 30, 'devices' => 10]
        ];
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
