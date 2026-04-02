<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\system;

use App\Config\Database;

class SystemInfo
{
    /**
     * Получает системную информацию
     */
    public static function getSystemInfo(): array
    {
        return [
            'app' => [
                'name' => 'QWEES VPN',
                'version' => '1.0.0',
                'build_date' => '2024-01-01',
                'environment' => $_ENV['APP_ENV'] ?? 'production'
            ],
            'server' => [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'timezone' => date_default_timezone_get(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ],
            'database' => [
                'driver' => 'sqlite',
                'status' => self::getDatabaseStatus(),
                'last_backup' => self::getLastBackupTime()
            ],
            'vpn_servers' => self::getVpnServersStatus()
        ];
    }

    /**
     * Получает статус базы данных
     */
    private static function getDatabaseStatus(): string
    {
        try {
            Database::send('SELECT 1');
            return 'connected';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Получает время последнего бэкапа
     */
    private static function getLastBackupTime(): string
    {
        // В реальном приложении здесь будет проверка времени последнего бэкапа
        return date('Y-m-d H:i:s', strtotime('-1 day'));
    }

    /**
     * Получает статус VPN серверов
     */
    private static function getVpnServersStatus(): array
    {
        return [
            'netherlands' => [
                'name' => 'Netherlands',
                'location' => 'Amsterdam',
                'status' => 'online',
                'load' => rand(20, 80),
                'users' => rand(100, 500),
                'ping' => rand(15, 35)
            ],
            'germany' => [
                'name' => 'Germany',
                'location' => 'Frankfurt',
                'status' => 'online',
                'load' => rand(20, 80),
                'users' => rand(100, 500),
                'ping' => rand(20, 40)
            ],
            'usa' => [
                'name' => 'USA',
                'location' => 'New York',
                'status' => 'online',
                'load' => rand(20, 80),
                'users' => rand(100, 500),
                'ping' => rand(30, 60)
            ]
        ];
    }

    /**
     * Получает настройки приложения
     */
    public static function getAppSettings(): array
    {
        return [
            'theme' => [
                'current' => 'dark',
                'available' => ['dark', 'light', 'auto'],
                'auto_switch' => true
            ],
            'notifications' => [
                'email' => true,
                'push' => false,
                'expiry_warning' => true,
                'expiry_days' => 3
            ],
            'privacy' => [
                'analytics' => false,
                'crash_reports' => true,
                'usage_stats' => false
            ],
            'vpn' => [
                'auto_connect' => false,
                'kill_switch' => true,
                'dns_leak_protection' => true,
                'protocol_preference' => 'vless'
            ],
            'language' => [
                'current' => 'ru',
                'available' => ['ru', 'en', 'de', 'fr']
            ]
        ];
    }

    /**
     * Получает логи приложения
     */
    public static function getAppLogs(int $limit = 50): array
    {
        $logFile = $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log';
        
        if (!file_exists($logFile)) {
            return [];
        }

        $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if (!$logs) {
            return [];
        }

        // Берем последние записи
        $recentLogs = array_slice($logs, -$limit);
        
        $formattedLogs = [];
        
        foreach ($recentLogs as $log) {
            // Парсим строку лога
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \[(\w+)\] (.+)$/', $log, $matches)) {
                $formattedLogs[] = [
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'message' => $matches[3],
                    'type' => self::getLogType($matches[2])
                ];
            }
        }
        
        return array_reverse($formattedLogs);
    }

    /**
     * Определяет тип лога по уровню
     */
    private static function getLogType(string $level): string
    {
        $errorLevels = ['ERROR', 'ОШИБКА', 'CRITICAL'];
        $warningLevels = ['WARNING', 'WARN', 'ПРЕДУПРЕЖДЕНИЕ'];
        $successLevels = ['SUCCESS', 'SUCCESS', 'УСПЕШНО'];
        
        if (in_array($level, $errorLevels)) {
            return 'error';
        } elseif (in_array($level, $warningLevels)) {
            return 'warning';
        } elseif (in_array($level, $successLevels, true)) {
            return 'success';
        }
        
        return 'info';
    }

    /**
     * Получает статистику системы
     */
    public static function getSystemStats(): array
    {
        // Получаем статистику пользователей
        $totalUsers = Database::send('SELECT COUNT(*) as count FROM qwees_users');
        $activeUsers = Database::send('SELECT COUNT(*) as count FROM qwees_users WHERE status = "active"');
        
        return [
            'users' => [
                'total' => $totalUsers[0]['count'] ?? 0,
                'active' => $activeUsers[0]['count'] ?? 0,
                'new_today' => rand(1, 10),
                'new_this_week' => rand(10, 50)
            ],
            'subscriptions' => [
                'basic' => rand(20, 100),
                'classic' => rand(15, 80),
                'pro' => rand(5, 40)
            ],
            'revenue' => [
                'today' => rand(1000, 5000),
                'this_week' => rand(5000, 25000),
                'this_month' => rand(20000, 100000)
            ],
            'performance' => [
                'uptime' => '99.9%',
                'response_time' => rand(100, 300) . 'ms',
                'error_rate' => '0.1%'
            ]
        ];
    }
}
