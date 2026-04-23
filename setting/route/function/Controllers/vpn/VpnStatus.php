<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\vpn;

use App\Config\Database;
use Setting\Route\Function\Controllers\Client\getUser;

class VpnStatus
{
    private getUser $user;

    public function __construct()
    {
        $this->user = new getUser();
    }

    public function getStatus(): string
    {
        $isActive = $this->user->getStatus() === 'on';
        $subscription = $this->user->getSubscription();
        return $isActive && !empty($subscription) ? 'active' : 'inactive';
    }

    public function getStatusText(): string
    {
        return $this->user->getStatus() === 'on' ? 'Активен' : 'Неактивен';
    }

    public function getSubscription(): string
    {
        return $this->user->getSubscription();
    }

    public function getDateEnd(): string
    {
        return $this->user->getDateEnd();
    }

    public function getDaysLeft(): int
    {
        return $this->calculateDaysLeft($this->user->getDateEnd());
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
        return null;
    }

    public function getPingStatus(): string
    {
        return 'unknown';
    }

    public function getProtocol(): string
    {
        return 'VLESS';
    }

    public function getIpAddress(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function getLocation(): string
    {
        return $_ENV['VLESS_SERVER'] ?? 'NL';
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
        $dateEnd = $this->user->getDateEnd();
        $countDays = $this->user->getCountDays();
        $countDevices = $this->user->getCountDevices();

        // Определяем статус подключения
        $status = $isActive && !empty($subscription) ? 'active' : 'inactive';

        $serverLabel = $_ENV['VLESS_SERVER'] ?? 'NL';

        return [
            'status' => $status,
            'status_text' => $isActive ? 'Активен' : 'Неактивен',
            'subscription' => $subscription,
            'date_end' => $dateEnd,
            'days_left' => $this->calculateDaysLeft($dateEnd),
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
            'date_end' => '',
            'days_left' => 0,
            'count_days' => 0,
            'count_devices' => 0,
            'ping' => ['ms' => null, 'status' => 'inactive'],
            'protocol' => 'VLESS',
            'ip_address' => '0.0.0.0',
            'location' => $_ENV['VLESS_SERVER'] ?? 'NL',
            'speed' => ['download' => null, 'upload' => null, 'status' => 'inactive']
        ];
    }

    /**
     * Рассчитывает оставшиеся дни
     */
    private function calculateDaysLeft(string $dateEnd): int
    {
        if (empty($dateEnd))
            return 0;

        $endDate = new \DateTime($dateEnd);
        $now = new \DateTime();

        return $endDate < $now ? 0 : $now->diff($endDate)->days;
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
