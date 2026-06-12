<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Client;

use DateTime;
use DateTimeZone;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;

class GetUser extends \Setting\Route\Function\Controllers\Client\Src\Client
{

    private array $client;

    public function __construct($uniID = null)
    {
        $this->client = $uniID !== null ? (array) self::get($uniID) : (array) self::get();
    }
    /**
     * Summary of onCheckSubscription
     * @return bool True - все в порядке | False - статус подписки обновлен
     */
    public function onCheckSubscription(): bool
    {
        $status = (string) $this->client['status'] ?? 'off';
        $dateEnd = (string) $this->client['date_end'] ?? '';
        $uniID = (string) ($this->client['uniID'] ?? '');
        if ($status === 'on' && !empty($dateEnd)) {
            //проверка времени
            $timezone = new DateTimeZone('Europe/Moscow');//UTC+3
            $current_DateTime = new DateTime('now', $timezone);//текущее время
            $end_DateTime = new DateTime($dateEnd . " 23:59:59", $timezone);//время окончания
            if ($end_DateTime < $current_DateTime) {//время в подписке не менее текущего
                if (!empty($uniID)) {
                    $xray = new Xray();
                    $result = $xray->DeleteKey($uniID);
                    if ($result['status'] === 'ok') {
                        // Логируем удаление
                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                            sprintf(
                                "[%s] [ИСТЕКШАЯ] Подписка %s истекла (%s) — удалена из БД и X-UI\n",
                                date('Y-m-d H:i:s'),
                                $uniID,
                                $dateEnd
                            ),
                            FILE_APPEND
                        );
                        return false;
                    }
                }
                return false;//в случаи пустого uniID
            }
            return true;//в случаи не истечени подписки
        }
        return true;
    }

    public function getID(): int
    {
        return (int) ($this->client['id'] ?? 0);
    }

    public function getFirstName(): string
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

    public function onPaymantStatus(): bool
    {
        return (isset(\App\Config\Session::init('kassa')['payment_id']) && !empty(\App\Config\Session::init('kassa')['payment_id'])) ? true : false;
    }
}
