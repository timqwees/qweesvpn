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
        $status = (string) ($this->client['status'] ?? 'off');//on | off
        $expiryMs = (int) ($this->client['expiry'] ?? 0);//мс
        $uniID = (string) ($this->client['uniID'] ?? '');//qwe......
        if ($status !== 'on' || $expiryMs <= 0) return true;//нечего проверять
        $nowMs = (new DateTime('now', new DateTimeZone('Europe/Moscow')))->getTimestamp() * 1000;
        if ($expiryMs > $nowMs) return true;//активна
        // --- истекла: сначала предохранители, удаление — в самом конце ---
        $log = function (string $msg) use ($uniID) {
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                \sprintf("[%s] [ПОДПИСКА - ПРОСРОЧКА] %s: %s\n", date('Y-m-d H:i:s'), $uniID, $msg),
                FILE_APPEND
            );
        };
        $expDate = date('Y-m-d H:i:s', (int) ($expiryMs / 1000));
        // 1) строку только что записали (гонка выдачи/переезда/фолбэка) — не трогаем
        $updatedAt = strtotime((string) ($this->client['updated_at'] ?? ''));
        if ($updatedAt > 0 && (time() - $updatedAt) < 900) {
            $log("пропуск: строка свежая (обновлена " . date('Y-m-d H:i:s', $updatedAt) . "), expiry $expDate");
            return true;
        }
        // 2) за 48ч есть успешная оплата — деньги взяты, удалять запрещено (глюк данных, не истечение)
        try {
            $paid = \App\Config\Database::send(
                "SELECT created_at FROM qwees_payments WHERE uniID = ? AND status = 'succeeded' ORDER BY created_at DESC LIMIT 1",
                [$uniID]
            );
        } catch (\Throwable) {
            $paid = null;
        }
        $paidAt = 0;
        if (\is_array($paid) && isset($paid[0]['created_at'])) {
            $paidAt = strtotime((string) $paid[0]['created_at']) ?: 0;
        }
        if ($paidAt > 0 && (time() - $paidAt) < 172800) {
            $log("СТОП: успешная оплата " . date('Y-m-d H:i:s', $paidAt) . " при expiry $expDate — удаление запрещено, нужен разбор");
            return true;
        }
        // 3) всё честно истекло — удаляем как раньше, но с полными значениями в лог
        if (!empty($uniID)) {
            $xray = new Xray();
            $result = $xray->DeleteKey($uniID);
            if ($result['status'] === 'ok') {
                // Логируем удаление
                file_put_contents(
                    $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                    \sprintf(
                        "[%s] [ПОДПИСКА - УДАЛЕНИЕ] Подписка %s истекла (%s) — удаление завершено успешно\n",
                        date('Y-m-d H:i:s'),
                        $uniID,
                        $expDate
                    ),
                    FILE_APPEND
                );
                return false;
            }
        }
        return false;//в случаи пустого uniID
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

    public function getEmail(): string
    {
        return (string) ($this->client['email'] ?? '');
    }

    public function getStatus(): string
    {
        return (string) ($this->client['status'] ?? 'off');
    }

    public function getSubscription(): string
    {
        return (string) ($this->client['subscription'] ?? '');
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

    public function getCountDays(): int
    {
        return (int) ($this->client['count_days'] ?? 0);
    }

    public function getCountDevices(): int
    {
        return (int) ($this->client['count_devices'] ?? 0);
    }

    public function getExpiry(): int
    {
        return (int) ($this->client['expiry'] ?? 0);//мс
    }

    public function getDiscountPercent(): int
    {
        return (int) ($this->client['discount_percent'] ?? 0);
    }

    public function getDiscountUses(): int
    {
        return (int) ($this->client['discount_uses'] ?? 0);
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
