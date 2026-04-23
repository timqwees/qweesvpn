<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\system;

use App\Config\Database;

class SystemInfo
{
    public function getAppName(): string
    {
        return 'QWEES VPN';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getEnvironment(): string
    {
        return $_ENV['APP_ENV'] ?? 'production';
    }

    public function getPhpVersion(): string
    {
        return PHP_VERSION;
    }

    public function getServerSoftware(): string
    {
        return $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
    }

    public function getTimezone(): string
    {
        return date_default_timezone_get();
    }

    public function getDbDriver(): string
    {
        return 'sqlite';
    }

    public function getDbStatus(): string
    {
        try {
            Database::send('SELECT 1');
            return 'connected';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    public function getSystemInfo(): array
    {
        return [
            'app' => [
                'name' => $this->getAppName(),
                'version' => $this->getVersion(),
                'environment' => $this->getEnvironment()
            ],
            'server' => [
                'php_version' => $this->getPhpVersion(),
                'server_software' => $this->getServerSoftware(),
                'timezone' => $this->getTimezone()
            ],
            'database' => [
                'driver' => $this->getDbDriver(),
                'status' => $this->getDbStatus()
            ]
        ];
    }

    private function getDatabaseStatus(): string
    {
        try {
            Database::send('SELECT 1');
            return 'connected';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    public function getSystemStats(): array
    {
        $totalUsers = Database::send('SELECT COUNT(*) as count FROM qwees_users');
        $activeUsers = Database::send('SELECT COUNT(*) as count FROM qwees_users WHERE status = "on"');

        return [
            'users' => [
                'total' => $totalUsers[0]['count'] ?? 0,
                'active' => $activeUsers[0]['count'] ?? 0
            ]
        ];
    }
}
