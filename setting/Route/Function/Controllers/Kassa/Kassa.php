<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Kassa;

use YooKassa\Client;
use YooKassa\Request\Payments\CreatePaymentRequest;
use YooKassa\Model\Receipt\Receipt;
use YooKassa\Model\Receipt\ReceiptItem;
use App\Config\Database;
use Setting\Route\Function\Controllers\{Server\Network as ServerNetwork, Vpn\V2ray\Xray, Refer\Tools\ReferRepository, Refer\Refer};
use DateTime, DateTimeZone;

class Kassa
{
    private Client $client;

    /** URL подписки X-UI: сервер пользователя (субдомен его подписки) или сервер по умолчанию из реестра Network. */
    private static function subscriptionUrl(string $uniID): string
    {
        return ServerNetwork::getSubscriptionUrl($uniID);
    }

    /**
     * Сохранение/обновление подписки в БД (MySQL: ON DUPLICATE KEY, SQLite: INSERT OR REPLACE).
     */
    private static function saveSubscriptionToDatabase(
        string $uniID,
        string $status,
        string $subscription,
        mixed $amount,
        int $countDays,
        int $countDevices,
        int $expiryMs
    ): bool {
        $params = [$uniID, $status, $subscription, $amount, $countDays, $countDevices, $expiryMs];

        if (Database::isMysql()) {
            $result = Database::send(
                'INSERT INTO qwees_subscriptions (uniID, status, subscription, amount, count_days, count_devices, expiry, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE
                   status = VALUES(status),
                   subscription = VALUES(subscription),
                   amount = VALUES(amount),
                   count_days = VALUES(count_days),
                   count_devices = VALUES(count_devices),
                   expiry = VALUES(expiry),
                   updated_at = CURRENT_TIMESTAMP',
                $params
            );
        } else {
            $result = Database::send(
                'INSERT OR REPLACE INTO qwees_subscriptions (uniID, status, subscription, amount, count_days, count_devices, expiry, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)',
                $params
            );
        }

        if ($result === false) {
            // Подписка/VPN-ключ уже выданы, но запись в БД не удалась
            $dbReason = Database::lastError() !== '' ? Database::lastError() : 'неизвестна';
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                \sprintf(
                    "[%s] [ПОДПИСКА - ЧАСТИЧНАЯ ВЫДАЧА] %s: подписка %d дней, %d уст. выдана, но обновление БД не удалось. Причина: %s\n",
                    date('Y-m-d H:i:s'),
                    $uniID,
                    $countDays,
                    $countDevices,
                    $dbReason
                ),
                FILE_APPEND
            );
        }
        \Setting\Route\Function\Controllers\Client\Src\Client::forget($uniID);//мемо сбросано — дальше свежее
        ServerNetwork::forgetSub($uniID);//мемо подписки тоже

        return $result !== false;
    }

    /**
     * Разобрать существующую строку подписки: конвертируемая ли (trial/bonus/pending)
     * и сколько целых дней остатка перенести в новую выдачу.
     * @return array{convertible:bool,days:int}
     */
    public static function carryFromRow($existingUser, int $nowMs): array
    {
        $existingSub = \is_array($existingUser) ? (string) ($existingUser['subscription'] ?? '') : '';
        $convertible = \in_array($existingSub, ['trial', 'bonus', ''], true) || strpos($existingSub, 'pending_') === 0;
        $days = 0;
        if (\is_array($existingUser) && $convertible && (int) ($existingUser['expiry'] ?? 0) > $nowMs) {
            $days = (int) ceil(((int) $existingUser['expiry'] - $nowMs) / 86400000);
        }
        return ['convertible' => $convertible, 'days' => $days];
    }

    /**
     * Расчёт expiry (мс) при отсутствии ответа панели: max(текущий expiry, сейчас) + days календарных дней.
     */
    private static function computeExpiryMs(string $uniID, int $days): int
    {
        $subData = Database::send("SELECT expiry FROM qwees_subscriptions WHERE uniID = ?", [$uniID]);
        $currentExpiry = (int) ($subData[0]['expiry'] ?? 0);
        $nowMs = new DateTime('now', new DateTimeZone('Europe/Moscow'))->getTimestamp() * 1000;
        return max($nowMs, $currentExpiry) + $days * 86400000;
    }

    /**
     * Выдача клиента с фолбэком по панелям: свой сервер первым, затем остальные
     * из реестра. Смена локации видна пользователю, но рабочий VPN лучше pending.
     * С одним сервером в реестре — обычная повторная попытка. Возвращает
     * ['vpn' => array|false, 'code' => ?string] (код панели-победителя).
     */
    private static function addClientFailover(string $uniID, int $days, int $devices): array
    {
        ServerNetwork::selectServer($uniID);
        $first = ServerNetwork::getServerCode();
        $ordered = array_values(array_unique(array_merge([$first], ServerNetwork::getServerCodes())));
        $last = false;
        foreach ($ordered as $code) {
            ServerNetwork::pinServer($code);
            try {
                $r = (new Xray())->addClient($days, $uniID, $devices);
            } catch (\Throwable $e) {
                $r = false;
                file_put_contents(
                    $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                    \sprintf(
                        "[%s] [ПОДПИСКА - ФОЛБЭК] %s: панель %s бросила исключение: %s\n",
                        date('Y-m-d H:i:s'),
                        $uniID,
                        $code,
                        mb_substr($e->getMessage(), 0, 160)
                    ),
                    FILE_APPEND
                );
            } finally {
                ServerNetwork::unpinServer();
            }
            if (\is_array($r) && ($r['success'] ?? false)) {
                if ($code !== $first) {
                    file_put_contents(
                        $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                        \sprintf(
                            "[%s] [ПОДПИСКА - ФОЛБЭК] %s: свой сервер %s мёртв, выдано на %s (локация сменилась)\n",
                            date('Y-m-d H:i:s'),
                            $uniID,
                            $first,
                            $code
                        ),
                        FILE_APPEND
                    );
                }
                return ['vpn' => $r, 'code' => $code];
            }
            $last = $r;
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                \sprintf(
                    "[%s] [ПОДПИСКА - ФОЛБЭК] %s: панель %s недоступна, пробуем дальше\n",
                    date('Y-m-d H:i:s'),
                    $uniID,
                    $code
                ),
                FILE_APPEND
            );
        }
        return ['vpn' => \is_array($last) ? $last : ['success' => false], 'code' => null];
    }

    /** URL подписки на ЯВНО указанном сервере (запись после фолбэка). */
    private static function subscriptionUrlOn(string $uniID, string $code): string
    {
        $srv = ServerNetwork::selectServer($uniID, $code);
        return rtrim((string) ($srv['XUI_URL_SUBSCRIPTION'] ?? ''), '/') . '/' . $uniID;
    }

    /**
     * Добивка pending_vpn: перевыпуск ключей с фолбэком (ленивый воркер).
     * Дёшево: один SELECT; панели трогаем только если есть зависшие.
     * Идемпотентно: успех переводит в on (+ чек в индекс и леджер), неуспех оставляет.
     */
    public static function retryPendingForUser(string $uniID, int $limit = 3): array
    {
        $uniID = trim($uniID);
        if ($uniID === '') return ['retried' => 0, 'issued' => 0];
        try {
            $rows = Database::send(
                "SELECT uniID, subscription, amount, count_days, count_devices FROM qwees_subscriptions WHERE uniID = ? AND status = 'pending_vpn' LIMIT " . max(1, min(10, $limit)),
                [$uniID]
            );
        } catch (\Throwable) {
            return ['retried' => 0, 'issued' => 0];
        }
        if (!\is_array($rows) || $rows === []) return ['retried' => 0, 'issued' => 0];
        return self::retryPendingRows($rows);
    }

    /** Добивка всех зависших (крон админки). */
    public static function retryPendingAll(int $limit = 20): array
    {
        try {
            $rows = Database::send(
                "SELECT uniID, subscription, amount, count_days, count_devices FROM qwees_subscriptions WHERE status = 'pending_vpn' ORDER BY updated_at ASC LIMIT " . max(1, min(100, $limit))
            );
        } catch (\Throwable) {
            return ['retried' => 0, 'issued' => 0];
        }
        if (!\is_array($rows) || $rows === []) return ['retried' => 0, 'issued' => 0];
        return self::retryPendingRows($rows);
    }

    private static function retryPendingRows(array $rows): array
    {
        $retried = 0;
        $issued = 0;
        foreach ($rows as $row) {
            if (!\is_array($row)) continue;
            $uniID = (string) ($row['uniID'] ?? '');
            if ($uniID === '') continue;
            $paymentId = '';
            if (preg_match('/^pending_payment_(.+)$/', (string) ($row['subscription'] ?? ''), $m)) $paymentId = $m[1];
            $days = max(1, (int) ($row['count_days'] ?? 30));
            $devices = max(1, (int) ($row['count_devices'] ?? 1));
            $amount = (float) ($row['amount'] ?? 0);
            $retried++;
            $issue = self::addClientFailover($uniID, $days, $devices);
            $vpnResult = $issue['vpn'];
            if (!\is_array($vpnResult) || !($vpnResult['success'] ?? false)) continue;
            $winCode = (string) ($issue['code'] ?? '');
            $expiryMs = (int) ($vpnResult['client_data']['expiryTime'] ?? 0);
            if ($expiryMs <= 0) $expiryMs = self::computeExpiryMs($uniID, $days);
            $subUrl = $winCode !== '' ? self::subscriptionUrlOn($uniID, $winCode) : self::subscriptionUrl($uniID);
            try {
                Database::transaction(function () use ($uniID, $subUrl, $amount, $days, $devices, $expiryMs, $paymentId) {
                    self::saveSubscriptionToDatabase($uniID, 'on', $subUrl, $amount, $days, $devices, $expiryMs);
                    if ($paymentId !== '') {
                        $uemail = Database::send('SELECT email FROM qwees_users WHERE uniID = ? LIMIT 1', [$uniID]);
                        PaymentLedger::record([
                            'payment_id' => $paymentId,
                            'uniID' => $uniID,
                            'email' => (string) (($uemail[0]['email'] ?? '') ?: ''),
                            'amount' => $amount,
                            'currency' => 'RUB',
                            'status' => 'succeeded',
                            'tariff' => '',
                            'method' => '',
                            'description' => 'Добивка pending_vpn',
                        ]);
                        PaymentIndex::add($uniID, $paymentId, date('Y-m-d H:i:s'), $amount);
                    }
                    (new ReferRepository())->useDiscountByUniID($uniID);
                    (new Refer())->rewardReferrerFromPurchase($uniID, $days);
                    return true;
                });
                $issued++;
                file_put_contents(
                    $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                    \sprintf(
                        "[%s] [ПОДПИСКА - ДОБИВКА] %s: pending_vpn закрыт, ключ выдан (%s)\n",
                        date('Y-m-d H:i:s'),
                        $uniID,
                        $winCode !== '' ? $winCode : '?'
                    ),
                    FILE_APPEND
                );
            } catch (\Throwable) {
                continue;
            }
        }
        return ['retried' => $retried, 'issued' => $issued];
    }

    public function __construct()
    {
        $shopId = $_ENV['YOOKASSA_SHOP_ID'] ?? null;
        $secretKey = $_ENV['YOOKASSA_SECRET_KEY'] ?? null;

        if (!$shopId || !$secretKey) {
            throw new \Exception('YooKassa credentials not configured. Please check your environment variables.');
        }

        try {
            $this->client = new Client();
            $this->client->setAuth($shopId, $secretKey);
            // Жёсткий предел ожидания кассы: было 80с/30с по умолчанию SDK —
            // зависшая касса вешала /pay/status и квитанции. 12с/6с достаточно живьём.
            $api = $this->client->getApiClient();
            if ($api instanceof \YooKassa\Client\CurlClient) {
                $api->setTimeout(12);
                $api->setConnectionTimeout(6);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to initialize YooKassa client: ' . $e->getMessage());
        }
    }

    /**
     * Создает платеж через YooKassa SDK
     * 
     * @param float $amount Сумма платежа
     * @param string $description Описание платежа
     * @param string|null $customerEmail Email клиента
     * @param string|null $customerPhone Телефон клиента
     * @param bool $saveCard Сохранять карту для автоплатежей
     * @param string $paymentMethod Способ оплаты (card/sbp/sberbank)
     * @param string|null $returnUrl URL возврата
     * @param array|null $metadata Метаданные платежа
     * @return array Результат создания платежа
     */
    public function createPayment(
        float $amount,
        string $description = 'Оплата в сервисе CoraVPN',
        ?string $customerEmail = null,
        ?string $customerPhone = null,
        bool $saveCard = true,
        string $paymentMethod = 'card',
        ?string $returnUrl = null,
        ?array $metadata = null
    ): array {
        try {
            // Создание запроса на платеж
            $paymentRequest = new CreatePaymentRequest();

            // Установка суммы
            $amountValue = ['value' => $amount, 'currency' => 'RUB'];
            $paymentRequest->setAmount($amountValue);

            // Установка описания
            $paymentRequest->setDescription(mb_substr($description, 0, 128, 'UTF-8'));

            // Установка подтверждения
            $paymentRequest->setConfirmation([
                'type' => 'redirect',
                'return_url' => $returnUrl ?? $_SERVER['HTTP_REFERER'] ?? '/',
            ]);

            // Настройка способа оплаты
            if ($paymentMethod === 'sbp') {
                $paymentRequest->setPaymentMethodData(['type' => 'sbp']);
            } elseif ($paymentMethod === 'sberbank') {
                $paymentRequest->setPaymentMethodData(['type' => 'sberbank']);
            } elseif ($paymentMethod === 'tbank') {
                $paymentRequest->setPaymentMethodData(['type' => 'tinkoff_bank']);
            }

            // Сохранение карты для автоплатежей
            if ($saveCard && $paymentMethod === 'card') {
                $paymentRequest->setSavePaymentMethod(true);
            }

            // Создание чека
            $receipt = $this->createReceipt($amount, $description, $customerEmail, $customerPhone);
            $paymentRequest->setReceipt($receipt);

            // Установка метаданных
            if ($metadata) {
                $paymentRequest->setMetadata($metadata);
            }

            // Создание платежа
            $payment = $this->client->createPayment($paymentRequest);

            return [
                'success' => true,
                'payment_url' => $payment->getConfirmation()?->getConfirmationUrl(),
                'payment_id' => $payment->getId(),
                'payment_method_id' => $payment->getPaymentMethod()?->getId(),
                'payment_method' => $paymentMethod,
                'status' => $payment->getStatus()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'payment_url' => null,
                'payment_id' => null,
                'payment_method_id' => null,
                'payment_method' => $paymentMethod
            ];
        }
    }

    /**
     * Проверяет статус платежа YooKassa и автоматически активирует VPN подписку
     * 
     * @param string $paymentId ID платежа YooKassa
     * @return array Массив с информацией о статусе платежа и подписке:
     *               - success: bool - успешность выполнения запроса
     *               - status: string - статус платежа ('succeeded', 'pending', 'canceled')
     *               - paid: bool - оплачен ли платеж
     *               - amount: float - сумма платежа
     *               - currency: string - валюта платежа
     *               - created_at: string - дата создания платежа
     *               - subscription_issued: bool - выдана ли подписка (при успешной оплате)
     *               - subscription_days: int - количество дней подписки (при успешной оплате)
     *               - subscription_devices: int - количество устройств (при успешной оплате)
     *               - subscription_end_date: int - expiry подписки в мс (при успешной оплате)
     *               - vpn_data: array - данные VPN клиента (при успешной оплате)
     *               - subscription_error: string - ошибка создания VPN (если произошла)
     *               - error: string - текст ошибки выполнения функции
     */
    public function startPaymentStatus(string $paymentId): array
    {
        try {
            $payment = $this->client->getPaymentInfo($paymentId);

            $result = [
                'success' => true,
                'status' => $payment->getStatus(),
                'paid' => $payment->getPaid(),
                'amount' => $payment->getAmount()?->getValue(),
                'currency' => $payment->getAmount()?->getCurrency(),
                'created_at' => $payment->getCreatedAt()?->format('Y-m-d H:i:s')
            ];

            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                \sprintf(
                    "[%s] [ПОКУПКА - СТАТУС] Статус платежа системы: %s, статус оплаты: %s\n",
                    date('Y-m-d H:i:s'),
                    $payment->getStatus() ? 'Успешно' : 'Отказано',
                    $payment->getPaid() ? 'Оплачен' : 'Не оплачен'
                ),
                FILE_APPEND
            );

            // Если платеж успешно оплачен, выдаем подписку
            if ($payment->getPaid() && $payment->getStatus() === 'succeeded') {
                $metadata = $payment->getMetadata();
                $uniID = $metadata['uniID'] ?? null;
                $first_name = $metadata['first_name'] ?? null;
                $last_name = $metadata['last_name'] ?? null;
                $tariff = $metadata['tariff'] ?? null;

                if ($uniID && $tariff) {
                    // Конфигурация тарифов (сроки и устройства берутся из PriceConfig)
                    $config = PriceConfig::getTariffConfig()[$tariff] ?? ['days' => 30, 'devices' => 1];

                    // Проверяем, не была ли уже выдана подписка для этого платежа
                    $existingUserData = Database::send("SELECT status, subscription, expiry FROM qwees_subscriptions WHERE uniID = ?", [$uniID]);
                    $existingUser = $existingUserData[0] ?? null;//получение данных
                    $current_time_MSK = new DateTime('now', new DateTimeZone('Europe/Moscow'))->getTimestamp();//текущее время MSK
                    $nowMs = $current_time_MSK * 1000;
                    // Триал/бонус/pending — не «активная платная», а конвертируемые: покупку не блокируем,
                    // остаток дней переносим в выдачу (иначе сгорит в панели, где клиента ещё нет)
                    $carry = self::carryFromRow($existingUser, $nowMs);
                    $convertible = $carry['convertible'];
                    $carryDays = $carry['days'];

                    if ($existingUser && $existingUser['status'] === 'on' && (int) $existingUser['expiry'] > $nowMs && !$convertible) {
                        // Подписка уже активна, не создаем новую
                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                            \sprintf(
                                "[%s] [ПОДПИСКА - ЗАЩИТА] %s: Подписка уже активна до %s, пропуск создания\n",
                                date('Y-m-d H:i:s'),
                                $uniID,
                                date('Y-m-d H:i:s', (int) $existingUser['expiry'] / 1000)
                            ),
                            FILE_APPEND
                        );

                        $result['subscription_issued'] = true;
                        $result['subscription_days'] = $config['days'];
                        $result['subscription_devices'] = $config['devices'];
                        $result['subscription_end_date'] = (int) $existingUser['expiry'];
                        $result['vpn_data'] = ['subscription_url' => self::subscriptionUrl($uniID)];

                        // Указатель чека в JSON-индекс (идемпотентно; повторный опрос не дублирует)
                        PaymentIndex::add($uniID, (string) $payment->getId(), date('Y-m-d H:i:s'), (float) ($payment->getAmount()?->getValue() ?? 0));

                        return $result;
                    }

                    // Создаем VPN подписку (плюс перенос остатка trial/bonus, если был)
                    $xray = new Xray();
                    $vpnResult = $xray->addClient((int) $config['days'] + $carryDays, $uniID, $config['devices']);

                    if ($vpnResult && $vpnResult['success']) {
                        // Источник истины — expiryTime (мс), который панель установила клиенту
                        $expiryMs = (int) ($vpnResult['client_data']['expiryTime'] ?? 0);
                        if ($expiryMs <= 0) {
                            $expiryMs = self::computeExpiryMs($uniID, $config['days']);
                        }

                        // Все записи по покупке — одним коммитом
                        Database::transaction(function () use ($uniID, $config, $expiryMs, $payment, $tariff, $metadata) {
                            self::saveSubscriptionToDatabase(
                                $uniID,
                                'on',//status
                                self::subscriptionUrl($uniID),//subscription
                                $payment->getAmount()?->getValue(),//amount
                                $config['days'],//days
                                $config['devices'],//count diveces
                                $expiryMs//expiry (мс)
                            );
                            // Чек в леджер тем же коммитом (email добираем из юзера)
                            $uemail = Database::send('SELECT email FROM qwees_users WHERE uniID = ? LIMIT 1', [$uniID]);
                            PaymentLedger::record([
                                'payment_id' => (string) $payment->getId(),
                                'uniID' => $uniID,
                                'email' => (string) (($uemail[0]['email'] ?? '') ?: ''),
                                'amount' => (float) ($payment->getAmount()?->getValue() ?? 0),
                                'currency' => (string) ($payment->getAmount()?->getCurrency() ?? 'RUB'),
                                'status' => (string) $payment->getStatus(),
                                'tariff' => (string) ($tariff ?? ''),
                                'method' => (string) ($metadata['payment_method'] ?? ''),
                                'description' => (string) ($payment->getDescription() ?? ''),
                            ]);
                            // Реферальная скидка: тратим одно использование из N
                            (new ReferRepository())->useDiscountByUniID($uniID);
                            // Реферер забирает % днями с этой покупки
                            (new Refer())->rewardReferrerFromPurchase($uniID, $config['days']);
                            return true;
                        });

                        $result['subscription_issued'] = true;
                        $result['subscription_days'] = $config['days'];
                        $result['subscription_devices'] = $config['devices'];
                        $result['subscription_end_date'] = $expiryMs;
                        $result['vpn_data'] = $vpnResult['client_data'];

                        // Указатель чека в JSON-индекс (идемпотентно; повторный опрос не дублирует)
                        PaymentIndex::add($uniID, (string) $payment->getId(), date('Y-m-d H:i:s'), (float) ($payment->getAmount()?->getValue() ?? 0));

                        if ($carryDays > 0) {
                            file_put_contents(
                                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                                \sprintf(
                                    "[%s] [ПОДПИСКА - ПЕРЕНОС ОСТАТКА] %s: остаток trial/bonus +%d дн. перенесён в выдачу\n",
                                    date('Y-m-d H:i:s'),
                                    $uniID,
                                    $carryDays
                                ),
                                FILE_APPEND
                            );
                        }

                        // Логирование с временем
                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                            \sprintf(
                                "[%s] [ПОКУПКА - ВЫДАЧА ПОДПИСКИ] %s (%s %s): %s (%d дней, %d уст.)\n",
                                date('Y-m-d H:i:s'),
                                $uniID,
                                $first_name,
                                $last_name,
                                $tariff,
                                $config['days'],
                                $config['devices'],
                            ),
                            FILE_APPEND
                        );
                    } else {
                        // Ошибка при создании VPN клиента - пробуем еще раз с задержкой
                        $result['subscription_error'] = 'Failed to create VPN client. Retrying...';
                        $result['subscription_issued'] = false;

                        // Логируем детальную информацию об ошибке
                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                            \sprintf(
                                "[%s] [ПОДПИСКА - ОШИБКА] Первая попытка создания VPN не удалась для %s. Повторная попытка через 5 секунд.\n",
                                date('Y-m-d H:i:s'),
                                $uniID
                            ),
                            FILE_APPEND
                        );

                        // Ждем 5 секунд и пробуем еще раз — с фолбэком по панелям
                        sleep(5);

                        $issue = self::addClientFailover($uniID, (int) $config['days'] + $carryDays, (int) $config['devices']);
                        $vpnResult = $issue['vpn'];

                        if ($vpnResult && $vpnResult['success']) {
                            // Вторая попытка успешна! Источник истины — expiryTime (мс) из панели
                            $expiryMs = (int) ($vpnResult['client_data']['expiryTime'] ?? 0);
                            if ($expiryMs <= 0) {
                                $expiryMs = self::computeExpiryMs($uniID, $config['days']);
                            }

                            // URL — строго с панели-победителя (после фолбэка сервер мог смениться)
                            $winCode = (string) ($issue['code'] ?? '');
                            $subUrl = $winCode !== '' ? self::subscriptionUrlOn($uniID, $winCode) : self::subscriptionUrl($uniID);

                            // Все записи по покупке — одним коммитом
                            Database::transaction(function () use ($uniID, $config, $expiryMs, $payment, $subUrl) {
                                self::saveSubscriptionToDatabase(
                                    $uniID,
                                    'on',//status
                                    $subUrl,//subscription
                                    $payment->getAmount()?->getValue(),//amount
                                    $config['days'],//days
                                    $config['devices'],//count diveces
                                    $expiryMs//expiry (мс)
                                );
                                // Реферальная скидка: тратим одно использование из N
                                (new ReferRepository())->useDiscountByUniID($uniID);
                                // Реферер забирает % днями с этой покупки
                                (new Refer())->rewardReferrerFromPurchase($uniID, $config['days']);
                                return true;
                            });

                            $result['subscription_issued'] = true;
                            $result['subscription_days'] = $config['days'];
                            $result['subscription_devices'] = $config['devices'];
                            $result['subscription_end_date'] = $expiryMs;
                            $result['vpn_data'] = $vpnResult['client_data'];

                            file_put_contents(
                                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                                \sprintf(
                                    "[%s] [ПОДПИСКА - УСПЕШНАЯ ПОПЫТКА ВЫДАЧИ] %s: VPN создан со 2-й попытки!\n",
                                    date('Y-m-d H:i:s'),
                                    $uniID,
                                ),
                                FILE_APPEND
                            );
                        } else {
                            // Вторая попытка тоже неудачна
                            $result['subscription_error'] = 'Ошибка создания подписки! Посмотриет в логах';
                            $result['subscription_issued'] = false;

                            file_put_contents(
                                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                                \sprintf(
                                    "[%s] [ПОДПИСКА - ОШИБКА] Вторая попытка создания VPN также не удалась для %s. Тариф: %s, Дней: %d, Устройств: %d\n",
                                    date('Y-m-d H:i:s'),
                                    $uniID,
                                    $tariff,
                                    $config['days'],
                                    $config['devices']
                                ),
                                FILE_APPEND
                            );

                            // Still update the database with payment info but mark subscription as pending
                            try {
                                // Даже при pending статусе учитываем бонусные дни
                                $expiryMs = self::computeExpiryMs($uniID, $config['days']);

                                self::saveSubscriptionToDatabase(
                                    $uniID,
                                    'pending_vpn',
                                    "pending_payment_{$paymentId}",
                                    $payment->getAmount()?->getValue(),
                                    $config['days'],
                                    $config['devices'],
                                    $expiryMs
                                );

                                file_put_contents(
                                    $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                                    \sprintf(
                                        "[%s] [ПОДПИСКА - ОБНОВЛЕНИЕ] %s: Данные обновлены, статус 'pending_vpn' - ожидание впн\n",
                                        date('Y-m-d H:i:s'),
                                        $uniID
                                    ),
                                    FILE_APPEND
                                );
                            } catch (\Exception $dbError) {
                                file_put_contents(
                                    $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                                    \sprintf(
                                        "[%s] [ПОДПИСКА - ОШИБКА] Не удалось обновить данные пользователя: %s\n",
                                        date('Y-m-d H:i:s'),
                                        $dbError->getMessage()
                                    ),
                                    FILE_APPEND
                                );
                            }
                        }

                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                            \sprintf(
                                "[%s] [ПОДПИСКА - ОШИБКА] Не удалось создать VPN клиент для %s. Платеж оплачен, но требуется ручная настройка.\n",
                                date('Y-m-d H:i:s'),
                                $uniID
                            ),
                            FILE_APPEND
                        );
                    }
                }
            }

            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Создает чек для платежа
     */
    private function createReceipt(
        float $amount,
        string $description,
        ?string $customerEmail,
        ?string $customerPhone
    ): Receipt {
        $receipt = new Receipt();

        // Создание товара в чеке
        $item = new ReceiptItem();
        $item->setDescription(mb_substr($description, 0, 128, 'UTF-8'));
        $item->setQuantity(1);
        $item->setVatCode(1); // Без НДС

        $receipt->setItems([
            [
                'description' => mb_substr($description, 0, 128, 'UTF-8'),
                'quantity' => '1.00',
                'amount' => [
                    'value' => (string) $amount,
                    'currency' => 'RUB'
                ],
                'vat_code' => 1
            ]
        ]);

        // Установка покупателя
        if ($customerEmail) {
            $receipt->setCustomer(['email' => $customerEmail]);
        } elseif ($customerPhone) {
            $receipt->setCustomer(['phone' => $customerPhone]);
        } else {
            $receipt->setCustomer(['email' => 'support@coravpn.ru']);
        }

        // Установка системы налогообложения
        $receipt->setTaxSystemCode(1);

        return $receipt;
    }

    /**
     * Сохраняет метод оплаты для автоплатежей
     */
    public function savePaymentMethod(string $uniID, string $paymentMethodId): bool
    {
        try {
            // Получаем текущие данные подписки
            $subData = Database::send('SELECT * FROM qwees_subscriptions WHERE uniID = ?', [$uniID]);

            if (!empty($subData[0])) {
                // Обновляем существующую запись
                Database::send(
                    'UPDATE qwees_subscriptions SET payment_method_id = ?, updated_at = CURRENT_TIMESTAMP WHERE uniID = ?',
                    [$paymentMethodId, $uniID]
                );
            } else {
                // Создаем новую запись только с payment_method_id
                Database::send(
                    'INSERT INTO qwees_subscriptions (uniID, status, payment_method_id, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)',
                    [$uniID, 'off', $paymentMethodId]
                );
            }
            return true;
        } catch (\Exception $e) {
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                \sprintf(
                    "[%s] [ОПЛАТА - ОШИБКА] savePaymentMethod: %s\n",
                    date('Y-m-d H:i:s'),
                    $e->getMessage()
                ),
                FILE_APPEND
            );
            return false;
        }
    }

}