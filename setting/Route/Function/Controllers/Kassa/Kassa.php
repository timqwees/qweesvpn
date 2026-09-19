<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Kassa;

use YooKassa\Client;
use YooKassa\Model\Payment\PaymentInterface;
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
                // 'qr_code' => $this->extractQrCode($payment),
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
                // 'qr_code' => null,
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
        // $startTime = microtime(true);

        // // Логируем начало проверки статуса
        // file_put_contents(
        //     $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
        //     \sprintf(
        //         "[%s] [DEBUG] Начало проверки статуса платежа: %s\n",
        //         date('Y-m-d H:i:s'),
        //         $paymentId
        //     ),
        //     FILE_APPEND
        // );

        try {
            // $apiStart = microtime(true);
            $payment = $this->client->getPaymentInfo($paymentId);
            // $apiTime = round(microtime(true) - $apiStart, 3);

            // file_put_contents(
            //     $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
            //     \sprintf(
            //         "[%s] [DEBUG] API YooKassa ответ: %s сек\n",
            //         date('Y-m-d H:i:s'),
            //         $apiTime
            //     ),
            //     FILE_APPEND
            // );

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

                        return $result;
                    }

                    // Создаем VPN подписку (плюс перенос остатка trial/bonus, если был)
                    // $vpnStart = microtime(true);
                    $xray = new Xray();
                    $vpnResult = $xray->addClient((int) $config['days'] + $carryDays, $uniID, $config['devices']);
                    // $vpnTime = round(microtime(true) - $vpnStart, 3);

                    if ($vpnResult && $vpnResult['success']) {
                        // Источник истины — expiryTime (мс), который панель установила клиенту
                        $expiryMs = (int) ($vpnResult['client_data']['expiryTime'] ?? 0);
                        if ($expiryMs <= 0) {
                            $expiryMs = self::computeExpiryMs($uniID, $config['days']);
                        }

                        // Все записи по покупке — одним коммитом
                        Database::transaction(function () use ($uniID, $config, $expiryMs, $payment) {
                            self::saveSubscriptionToDatabase(
                                $uniID,
                                'on',//status
                                self::subscriptionUrl($uniID),//subscription
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
                        // $totalTime = round(microtime(true) - $startTime, 3);
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

                        // Ждем 5 секунд и пробуем еще раз
                        sleep(5);

                        // $vpnRetryStart = microtime(true);
                        $xray = new Xray();
                        $vpnResult = $xray->addClient((int) $config['days'] + $carryDays, $uniID, $config['devices']);
                        // $vpnRetryTime = round(microtime(true) - $vpnRetryStart, 3);

                        if ($vpnResult && $vpnResult['success']) {
                            // Вторая попытка успешна! Источник истины — expiryTime (мс) из панели
                            $expiryMs = (int) ($vpnResult['client_data']['expiryTime'] ?? 0);
                            if ($expiryMs <= 0) {
                                $expiryMs = self::computeExpiryMs($uniID, $config['days']);
                            }

                            // Все записи по покупке — одним коммитом
                            Database::transaction(function () use ($uniID, $config, $expiryMs, $payment) {
                                self::saveSubscriptionToDatabase(
                                    $uniID,
                                    'on',
                                    self::subscriptionUrl($uniID),
                                    $payment->getAmount()?->getValue(),
                                    $config['days'],
                                    $config['devices'],
                                    $expiryMs
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

                            // $totalTime = round(microtime(true) - $startTime, 3);
                            file_put_contents(
                                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                                \sprintf(
                                    "[%s] [ПОДПИСКА - УСПЕШНАЯ ПОПЫТКА ВЫДАЧИ] %s: VPN создан со 2-й попытки!\n",
                                    date('Y-m-d H:i:s'),
                                    $uniID,
                                    // $vpnTime,
                                    // $vpnRetryTime,
                                    // $totalTime
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
     * Извлекает QR-код из платежа (для СБП)
     */
    private function extractQrCode(PaymentInterface $payment): ?string
    {
        $paymentMethod = $payment->getPaymentMethod();

        if ($paymentMethod && $paymentMethod->getType() === 'sbp') {
            // Для СБП QR-код может быть в ответе платежа
            return $payment->getConfirmation()?->getConfirmationUrl() ?? null;
        }

        return null;
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

    /**
     * Создает автоплатеж
     */
    public function createAutoPayment(
        string $paymentMethodId,
        float $amount,
        string $description = 'Автоплатеж QweesVPN'
    ): array {
        try {
            // Создание запроса на автоплатеж
            $paymentRequest = new CreatePaymentRequest();
            $amountValue = ['value' => $amount, 'currency' => 'RUB'];
            $paymentRequest->setAmount($amountValue);
            $paymentRequest->setDescription($description);
            $paymentRequest->setCapture(true);
            $paymentRequest->setPaymentMethodId($paymentMethodId);

            // Создание чека
            $receipt = $this->createReceipt($amount, $description, null, null);
            $paymentRequest->setReceipt($receipt);

            // Создание платежа
            $payment = $this->client->createPayment($paymentRequest);

            return [
                'success' => true,
                'payment_id' => $payment->getId(),
                'status' => $payment->getStatus(),
                'paid' => $payment->getPaid()
            ];

        } catch (\Exception $e) {
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                \sprintf(
                    "[%s] [ОПЛАТА - ОШИБКА] createAutoPayment: %s\n",
                    date('Y-m-d H:i:s'),
                    $e->getMessage()
                ),
                FILE_APPEND
            );

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}