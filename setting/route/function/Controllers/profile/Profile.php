<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\profile;

use App\Config\Database;
use Setting\Route\Function\Controllers\client\Client;
use Setting\Route\Function\Controllers\refer\config\ReferConfig;

class Profile
{
    /**
     * Получает полный профиль пользователя
     */
    public static function getUserProfile(string $uniID): array
    {
        $client = Client::get($uniID);
        
        if (empty($client['uniID'])) {
            return self::getEmptyProfile();
        }

        return [
            'personal_info' => [
                'first_name' => $client['first_name'] ?? '',
                'last_name' => $client['last_name'] ?? '',
                'email' => $client['email'] ?? '',
                'uniID' => $client['uniID'],
                'registration_date' => self::getRegistrationDate($uniID)
            ],
            'subscription_info' => [
                'status' => $client['status'] ?? 'off',
                'subscription' => $client['subscription'] ?? '',
                'date_end' => $client['date_end'] ?? '',
                'count_days' => intval($client['count_days'] ?? 0),
                'count_devices' => intval($client['count_devices'] ?? 0),
                'amount' => $client['amount'] ?? 0
            ],
            'referal_info' => [
                'refer_link' => $client['refer_link'] ?? '',
                'my_refer_link' => $client['my_refer_link'] ?? '',
                'refer_count' => intval($client['refer_count'] ?? 0),
                'has_discount' => !empty($client['refer_link'])
            ],
            'pricing_info' => self::getPricingInfo()
        ];
    }

    /**
     * Получает пустой профиль для неавторизованного пользователя
     */
    private static function getEmptyProfile(): array
    {
        return [
            'personal_info' => [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'uniID' => '',
                'registration_date' => ''
            ],
            'subscription_info' => [
                'status' => 'off',
                'subscription' => '',
                'date_end' => '',
                'count_days' => 0,
                'count_devices' => 0,
                'amount' => 0
            ],
            'referal_info' => [
                'refer_link' => '',
                'my_refer_link' => '',
                'refer_count' => 0,
                'has_discount' => false
            ],
            'pricing_info' => self::getPricingInfo()
        ];
    }

    /**
     * Получает информацию о тарифах
     */
    public static function getPricingInfo(): array
    {
        try {
            // Прямой запрос к базе данных для получения цен
            $prices = Database::send('SELECT * FROM qwees_price');
            
            if (!is_array($prices) || empty($prices)) {
                return self::getDefaultPricing();
            }
            
            $priceData = $prices[0] ?? [];
            
            return [
                'basic' => [
                    'name' => 'Basic',
                    'price' => $priceData['basic'] ?? 100,
                    'days' => 30,
                    'devices' => 2,
                    'features' => ['Высокая скорость', 'Шифрование', '2 устройства']
                ],
                'classic' => [
                    'name' => 'Classic',
                    'price' => $priceData['clasic'] ?? 200,
                    'days' => 30,
                    'devices' => 5,
                    'features' => ['Высокая скорость', 'Шифрование', '5 устройств', 'Приоритетная поддержка']
                ],
                'pro' => [
                    'name' => 'Pro',
                    'price' => $priceData['pro'] ?? 300,
                    'days' => 30,
                    'devices' => 10,
                    'features' => ['Максимальная скорость', 'Военное шифрование', '10 устройств', 'VIP поддержка', 'Выделенный IP']
                ]
            ];
        } catch (\Exception $e) {
            return self::getDefaultPricing();
        }
    }

    /**
     * Получает тарифы по умолчанию
     */
    private static function getDefaultPricing(): array
    {
        return [
            'basic' => [
                'name' => 'Basic',
                'price' => 100,
                'days' => 30,
                'devices' => 2,
                'features' => ['Высокая скорость', 'Шифрование', '2 устройства']
            ],
            'classic' => [
                'name' => 'Classic',
                'price' => 200,
                'days' => 30,
                'devices' => 5,
                'features' => ['Высокая скорость', 'Шифрование', '5 устройств', 'Приоритетная поддержка']
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 300,
                'days' => 30,
                'devices' => 10,
                'features' => ['Максимальная скорость', 'Военное шифрование', '10 устройств', 'VIP поддержка', 'Выделенный IP']
            ]
        ];
    }

    /**
     * Получает дату регистрации пользователя
     */
    private static function getRegistrationDate(string $uniID): string
    {
        // В реальном приложении здесь будет получение даты из БД
        // Для демонстрации возвращаем текущую дату
        return date('Y-m-d H:i:s');
    }

    /**
     * Генерирует реферальную ссылку для пользователя
     */
    public static function generateReferLink(string $uniID): string
    {
        $baseUrl = $_SERVER['HTTP_HOST'] ?? 'coravpn.online';
        $myReferLink = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 7);
        
        // Сохраняем реферальную ссылку в БД
        Database::send(
            'UPDATE qwees_users SET my_refer_link = ? WHERE uniID = ?',
            [$myReferLink, $uniID]
        );
        
        return "https://{$baseUrl}/?ref={$myReferLink}";
    }

    /**
     * Получает статистику рефералов
     */
    public static function getReferStats(string $uniID): array
    {
        $client = Client::get($uniID);
        
        if (empty($client['my_refer_link'])) {
            return [
                'total_referrals' => 0,
                'active_referrals' => 0,
                'total_earned_days' => 0
            ];
        }

        // Получаем всех рефералов пользователя
        $referrals = Database::send(
            'SELECT * FROM qwees_users WHERE refer_link = ?',
            [$client['my_refer_link']]
        );

        if (!is_array($referrals)) {
            return [
                'total_referrals' => 0,
                'active_referrals' => 0,
                'total_earned_days' => 0
            ];
        }

        $activeReferrals = 0;
        $totalEarnedDays = 0;

        foreach ($referrals as $referral) {
            if ($referral['status'] === 'active') {
                $activeReferrals++;
                $days = intval($referral['count_days'] ?? 0);
                $bonusPercent = ReferConfig::getReferralDaysBonus();
                $totalEarnedDays += floor($days * $bonusPercent / 100);
            }
        }

        return [
            'total_referrals' => count($referrals),
            'active_referrals' => $activeReferrals,
            'total_earned_days' => $totalEarnedDays
        ];
    }
}
