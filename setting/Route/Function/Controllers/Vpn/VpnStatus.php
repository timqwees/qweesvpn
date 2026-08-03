<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Vpn;

use Setting\Route\Function\Controllers\Client\GetUser;
use DateTime, DateTimeZone;

class VpnStatus
{
    private GetUser $user;

    // Статическое кэширование для ускорения
    private static array $cache = [];
    private static int $cacheTime = 30; // 30 секунд кэширования

    public function __construct()
    {
        $this->user = new GetUser();
    }

    /**
     * Получает кэшированные данные или выполняет функцию
     */
    private static function getCachedData(string $key, callable $callback, ?int $ttl = null)
    {
        $ttl = $ttl ?? self::$cacheTime;
        $cacheKey = $key . '_' . floor(time() / $ttl);

        if (!isset(self::$cache[$cacheKey])) {
            self::$cache[$cacheKey] = $callback();
        }

        return self::$cache[$cacheKey];
    }

    public function getStatus(): string
    {
        $isActive = $this->user->getStatus() === 'on';
        $subscription = $this->user->getSubscription();
        $expiry = $this->user->getExpiry();

        // Проверяем, не истекла ли подписка
        if ($isActive && $expiry > 0) {
            $isExpired = ($expiry / 1000) < time();
            if ($isExpired) {
                return 'inactive';
            }
        }

        return $isActive && !empty($subscription) ? 'active' : 'inactive';
    }

    public function getStatusText(): string
    {
        $status = $this->user->getStatus();
        $expiry = $this->user->getExpiry();

        // Проверяем, не истекла ли подписка
        if ($status === 'on' && $expiry > 0) {
            if (($expiry / 1000) < time()) {
                return 'Неактивен';
            }
        }

        return $status === 'on' ? 'Активен' : 'Неактивен';
    }

    public function getSubscription(): string
    {
        return $this->user->getSubscription();
    }

    public function getExpiry(): int
    {
        return $this->user->getExpiry();
    }

    public function getDaysLeft(): int
    {
        return $this->calculateDaysLeft($this->user->getExpiry());
    }

    public function getCountDays(): int
    {
        return $this->user->getCountDays();
    }

    public function getCountDevices(): int
    {
        return $this->user->getCountDevices();
    }

    public function getPingMs(): ?float
    {
        return self::getCachedData('ping', function () {
            $host = $_ENV['VLESS_SERVER'] ?? 'localhost';

            // Супер быстрый ping - 0.5 сек таймаут
            $start = microtime(true);
            $socket = @fsockopen($host, 443, $errno, $errstr, 0.5);

            if ($socket) {
                fclose($socket);
                return (float) round((microtime(true) - $start) * 1000, 1);
            }

            return null;
        }, 15); // Кэшируем на 15 секунд
    }

    public function getPingStatus(): string
    {
        $ping = $this->getPingMs();

        if ($ping === null) {
            return 'inactive';
        }

        if ($ping < 50) {
            return 'good';
        } elseif ($ping < 150) {
            return 'medium';
        } else {
            return 'poor';
        }
    }

    public function getProtocol(): string
    {
        return self::getCachedData('protocol', function () {
            return $_ENV['VLESS_PROTOCOL'] ?? 'VLESS';
        }, 300); // Кэшируем на 5 минут
    }

    public function getIpAddress(): string
    {
        return self::getCachedData('ip_address', function () {
            $vpnServerHost = $_ENV['VLESS_SERVER'] ?? null;

            // Быстрая проверка локального IP
            if ($vpnServerHost) {
                if (filter_var($vpnServerHost, FILTER_VALIDATE_IP)) {
                    return $vpnServerHost;
                }

                // Кэшированное разрешение DNS
                $ip = gethostbyname($vpnServerHost);
                if ($ip !== $vpnServerHost) {
                    return $ip;
                }
            }

            // Быстрый запасной вариант - локальный IP
            return $_SERVER['SERVER_ADDR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }, 60); // Кэшируем на 1 минуту
    }

    public function getLocation(): string
    {
        return self::getCachedData('location', function () {
            $serverCode = $_ENV['VLESS_SERVER'] ?? 'FI';

            // Быстрое преобразование кода в название
            $countryNames = [
                'NL' => 'Netherlands',
                'DE' => 'Germany',
                'US' => 'United States',
                'GB' => 'United Kingdom',
                'FR' => 'France',
                'RU' => 'Russia',
                'UA' => 'Ukraine',
                'KZ' => 'Kazakhstan',
                'TR' => 'Turkey',
                'CA' => 'Canada',
                'AU' => 'Australia',
                'JP' => 'Japan',
                'SG' => 'Singapore',
                'FI' => 'Finland',
                'SE' => 'Sweden'
            ];

            return $countryNames[$serverCode] ?? $serverCode;
        }, 300); // Кэшируем на 5 минут
    }

    /**
     * Получает полный статус VPN для пользователя
     */
    public function getVpnStatus(): array
    {
        if (empty($this->user->getUniID())) {
            return $this->getInactiveStatus();
        }

        $isActive = $this->user->getStatus() === 'on';
        $subscription = $this->user->getSubscription();
        $expiry = $this->user->getExpiry();
        $countDays = $this->user->getCountDays();
        $countDevices = $this->user->getCountDevices();

        // Проверяем, не истекла ли подписка
        $isExpired = false;
        if ($isActive && $expiry > 0) {
            $isExpired = ($expiry / 1000) < time();
            if ($isExpired) {
                $isActive = false;
            }
        }

        // Определяем статус подключения
        $status = $isActive && !empty($subscription) ? 'active' : 'inactive';

        $serverLabel = $_ENV['VLESS_SERVER'] ?? 'NL';

        return [
            'status' => $status,
            'status_text' => $isActive ? 'Активен' : 'Неактивен',
            'subscription' => $subscription,
            'expiry' => $expiry,
            'days_left' => $this->calculateDaysLeft($expiry),
            'count_days' => $countDays,
            'count_devices' => $countDevices,
            // Реальный пинг/скорость с сервера здесь не измеряются — не подставляем случайные числа
            'ping' => ['ms' => null, 'status' => 'unknown'],
            'protocol' => 'VLESS',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'location' => $serverLabel,
            'speed' => ['download' => null, 'upload' => null, 'status' => 'unknown']
        ];
    }

    /**
     * Возвращает статус неактивного VPN
     */
    private function getInactiveStatus(): array
    {
        return [
            'status' => 'inactive',
            'status_text' => 'Неактивен',
            'subscription' => '',
            'expiry' => 0,
            'days_left' => 0,
            'count_days' => 0,
            'count_devices' => 0,
            'ping' => ['ms' => null, 'status' => 'inactive'],
            'protocol' => 'VLESS',
            'ip_address' => '0.0.0.0',
            'location' => $_ENV['VLESS_SERVER'] ?? 'Сервер не активен :(',
            'speed' => ['download' => null, 'upload' => null, 'status' => 'inactive']
        ];
    }

    /**
     * Рассчитывает оставшиеся дни
     */
    private function calculateDaysLeft(int $expiryMs): int
    {
        if ($expiryMs <= 0)
            return 0;

        $endDate = new DateTime('@' . ($expiryMs / 1000), new DateTimeZone('Europe/Moscow'));
        $now = new DateTime('now', new DateTimeZone('Europe/Moscow'));

        return $endDate < $now ? 0 : $now->diff($endDate)->days;//сколько дней осталось
    }

    /**
     * Получает статистику использования
     */
    public function getUsageStats(): array
    {
        if (empty($this->user->getUniID())) {
            return ['total_usage' => 0, 'remaining_usage' => 0, 'usage_percentage' => 0];
        }

        // Трафик из X-UI сюда не подтягивается — без фиктивных значений
        return [
            'total_usage' => null,
            'remaining_usage' => null,
            'usage_percentage' => null,
            'unit' => 'GB'
        ];
    }
}
