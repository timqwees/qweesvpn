<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\Client;

class getUser extends \Setting\Route\Function\Controllers\Client\src\Client
{

    private array $client;

    public function __construct()
    {
        $this->client = (array) self::get();
    }

    public function getID(): int
    {
        return (int) ($this->client['id'] ?? 0);
    }

    public function getFistName(): string
    {
        return (string) ($this->client['first_name'] ?? '');
    }

    public function getLastName(): string
    {
        return (string) ($this->client['last_name'] ?? '');
    }

    public function getUniID(): string
    {
        return (string) ($this->client['uniID'] ?? '');
    }

    public function getЕmail(): string
    {
        return (string) ($this->client['email'] ?? '');
    }

    public function getEmail(): string
    {
        return $this->getЕmail();
    }

    public function getStatus(): string
    {
        return (string) ($this->client['status'] ?? 'off');
    }

    public function getSubscription(): string
    {
        return (string) ($this->client['subscription'] ?? '');
    }

    public function getSub()/*aliases*/
    {
        return $this->getSubscription();
    }

    public function getAmount(): int
    {
        return (int) ($this->client['amount'] ?? 0);
    }

    public function getRefer(): string
    {
        return (string) ($this->client['refer'] ?? '');
    }

    public function getMyRefer(): string
    {
        return (string) ($this->client['myrefer'] ?? '');
    }

    public function getReferLink(): string
    {
        return (string) ($this->client['refer_link'] ?? '');
    }

    public function getCountDays(): int
    {
        return (int) ($this->client['count_days'] ?? 0);
    }

    public function getCountDevices(): int
    {
        return (int) ($this->client['count_devices'] ?? 0);
    }

    public function getDateEnd(): string
    {
        return (string) ($this->client['date_end'] ?? '');
    }

    public function getDiscountPercent(): int
    {
        return (int) ($this->client['discount_percent'] ?? 0);
    }

    public function getBonusPercent(): int
    {
        return (int) ($this->client['bonus_percent'] ?? 0);
    }

    public function getReferCount(): int
    {
        return (int) ($this->client['refer_count'] ?? 0);
    }
}