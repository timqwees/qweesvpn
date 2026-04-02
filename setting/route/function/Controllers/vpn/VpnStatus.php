<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\vpn;

use App\Config\Database;
use Setting\Route\Function\Controllers\client\Client;
use Setting\Route\Function\Controllers\vpn\v2ray\Xray;

class VpnStatus
{
    /**
     * Получает полный статус VPN для пользователя
     */
    public static function getVpnStatus(string $uniID): array
    {
        $client = Client::get($uniID);
        
        if (empty($client['uniID'])) {
            return self::getInactiveStatus();
        }

        $isActive = $client['status'] === 'active';
        $subscription = $client['subscription'] ?? '';
        $dateEnd = $client['date_end'] ?? '';
        $countDays = $client['count_days'] ?? 0;
        $countDevices = $client['count_devices'] ?? 0;

        // Определяем статус подключения
        $status = $isActive && !empty($subscription) ? 'active' : 'inactive';
        
        // Получаем информацию о подписке
        $subscriptionInfo = self::getSubscriptionInfo($subscription);

        return [
            'status' => $status,
            'status_text' => $isActive ? 'Активен' : 'Неактивен',
            'subscription' => $subscription,
            'subscription_info' => $subscriptionInfo,
            'date_end' => $dateEnd,
            'days_left' => self::calculateDaysLeft($dateEnd),
            'count_days' => $countDays,
            'count_devices' => $countDevices,
            'ping' => self::getPingStatus(),
            'protocol' => $subscriptionInfo['protocol'] ?? 'gRPC',
            'ip_address' => self::getCurrentIP(),
            'location' => $subscriptionInfo['location'] ?? 'Netherlands',
            'speed' => self::getSpeedStatus()
        ];
    }

    /**
     * Получает статус для неактивного пользователя
     */
    private static function getInactiveStatus(): array
    {
        return [
            'status' => 'inactive',
            'status_text' => 'Неактивен',
            'subscription' => '',
            'subscription_info' => null,
            'date_end' => '',
            'days_left' => 0,
            'count_days' => 0,
            'count_devices' => 0,
            'ping' => ['ms' => 0, 'status' => 'inactive'],
            'protocol' => 'gRPC',
            'ip_address' => '0.0.0.0',
            'location' => 'Netherlands',
            'speed' => ['download' => 0, 'upload' => 0, 'status' => 'inactive']
        ];
    }

    /**
     * Получает информацию о подписке
     */
    private static function getSubscriptionInfo(string $subscription): array
    {
        if (empty($subscription)) {
            return [];
        }

        // Парсим URL подписки для получения информации
        $urlParts = parse_url($subscription);
        
        return [
            'protocol' => self::extractProtocol($subscription),
            'location' => self::extractLocation($subscription),
            'server' => $urlParts['host'] ?? 'unknown',
            'port' => $urlParts['port'] ?? 443,
            'security' => self::extractSecurity($subscription)
        ];
    }

    /**
     * Извлекает протокол из подписки
     */
    private static function extractProtocol(string $subscription): string
    {
        if (strpos($subscription, 'vless') !== false) {
            return 'VLESS';
        } elseif (strpos($subscription, 'vmess') !== false) {
            return 'VMess';
        } elseif (strpos($subscription, 'trojan') !== false) {
            return 'Trojan';
        }
        return 'gRPC';
    }

    /**
     * Извлекает локацию сервера
     */
    private static function extractLocation(string $subscription): string
    {
        // Определяем локацию по домену или IP
        $hosts = [
            'nl' => 'Netherlands',
            'de' => 'Germany', 
            'us' => 'USA',
            'fr' => 'France',
            'uk' => 'United Kingdom'
        ];

        foreach ($hosts as $prefix => $country) {
            if (strpos($subscription, $prefix) !== false) {
                return $country;
            }
        }

        return 'Netherlands';
    }

    /**
     * Извлекает тип безопасности
     */
    private static function extractSecurity(string $subscription): string
    {
        if (strpos($subscription, 'tls') !== false) {
            return 'TLS';
        } elseif (strpos($subscription, 'reality') !== false) {
            return 'Reality';
        }
        return 'None';
    }

    /**
     * Рассчитывает оставшиеся дни
     */
    private static function calculateDaysLeft(string $dateEnd): int
    {
        if (empty($dateEnd)) {
            return 0;
        }

        $endDate = new \DateTime($dateEnd);
        $now = new \DateTime();
        
        if ($endDate < $now) {
            return 0;
        }

        return $now->diff($endDate)->days;
    }

    /**
     * Получает статус пинга
     */
    private static function getPingStatus(): array
    {
        // В реальном приложении здесь будет ping до VPN сервера
        // Для демонстрации возвращаем тестовые данные
        return [
            'ms' => rand(15, 45),
            'status' => 'good'
        ];
    }

    /**
     * Получает текущий IP адрес
     */
    private static function getCurrentIP(): string
    {
        // В реальном приложении здесь будет определение текущего IP
        // Для демонстрации возвращаем тестовые данные
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Получает статус скорости
     */
    private static function getSpeedStatus(): array
    {
        // В реальном приложении здесь будет тест скорости
        // Для демонстрации возвращаем тестовые данные
        return [
            'download' => rand(50, 100),
            'upload' => rand(20, 50),
            'status' => 'good',
            'unit' => 'Mbps'
        ];
    }

    /**
     * Получает статистику использования
     */
    public static function getUsageStats(string $uniID): array
    {
        $client = Client::get($uniID);
        
        if (empty($client['uniID'])) {
            return [
                'total_usage' => 0,
                'remaining_usage' => 0,
                'usage_percentage' => 0
            ];
        }

        // В реальном приложении здесь будет получение данных от XUI
        $totalUsageGB = rand(10, 100);
        $maxUsageGB = 500; // Лимит трафика
        
        return [
            'total_usage' => $totalUsageGB,
            'remaining_usage' => max(0, $maxUsageGB - $totalUsageGB),
            'usage_percentage' => min(100, ($totalUsageGB / $maxUsageGB) * 100),
            'unit' => 'GB'
        ];
    }
}
