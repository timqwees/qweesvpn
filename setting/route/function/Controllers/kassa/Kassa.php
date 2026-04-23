<?php declare(strict_types=1);

namespace Setting\Route\Function\Controllers\kassa;

use YooKassa\Client;
use YooKassa\Model\Payment\PaymentInterface;
use YooKassa\Request\Payments\CreatePaymentRequest;
use YooKassa\Model\Receipt\Receipt;
use YooKassa\Model\Receipt\ReceiptItem;
use YooKassa\Model\MonetaryAmount;
use App\Config\Database;
use Setting\Route\Function\Controllers\vpn\v2ray\Xray;

class Kassa
{
    private Client $client;

    /** URL подписки X-UI из .env (единый формат без разных доменов/портов в коде). */
    private static function subscriptionUrl(string $uniID): string
    {
        $base = rtrim($_ENV['XUI_URL_SUBSCRIPTION'] ?? '', '/');
        return $base . '/' . $uniID;
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
                'qr_code' => $this->extractQrCode($payment),
                'payment_method' => $paymentMethod,
                'status' => $payment->getStatus()
            ];

        } catch (\Exception $e) {
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                sprintf(
                    "[%s] [ОШИБКА - YOOKASSA] createPayment: %s\n",
                    date('Y-m-d H:i:s'),
                    $e->getMessage()
                ),
                FILE_APPEND
            );

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'payment_url' => null,
                'payment_id' => null,
                'payment_method_id' => null,
                'qr_code' => null,
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
     *               - subscription_end_date: string - дата окончания подписки (при успешной оплате)
     *               - vpn_data: array - данные VPN клиента (при успешной оплате)
     *               - subscription_error: string - ошибка создания VPN (если произошла)
     *               - error: string - текст ошибки выполнения функции
     */
    public function startPaymentStatus(string $paymentId): array
    {
        // Логируем начало проверки статуса
        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf(
                "[%s] [DEBUG] Начало проверки статуса платежа: %s\n",
                date('Y-m-d H:i:s'),
                $paymentId
            ),
            FILE_APPEND
        );

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
                $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                sprintf(
                    "[%s] [DEBUG] Статус платежа: %s, оплачен: %s\n",
                    date('Y-m-d H:i:s'),
                    $payment->getStatus(),
                    $payment->getPaid() ? 'YES' : 'NO'
                ),
                FILE_APPEND
            );

            // Если платеж успешно оплачен, выдаем подписку
            if ($payment->getPaid() && $payment->getStatus() === 'succeeded') {
                $metadata = $payment->getMetadata();
                $uniID = $metadata['uniID'] ?? null;
                $tariff = $metadata['tariff'] ?? null;

                if ($uniID && $tariff) {
                    // Конфигурация тарифов
                    $tariffConfig = [
                        // 1 месяц
                        '1month_1' => ['days' => 30, 'devices' => 1],
                        '1month_4' => ['days' => 30, 'devices' => 4],
                        '1month_10' => ['days' => 30, 'devices' => 10],
                        // 6 месяцев
                        '6months_1' => ['days' => 180, 'devices' => 1],
                        '6months_4' => ['days' => 180, 'devices' => 4],
                        '6months_10' => ['days' => 180, 'devices' => 10],
                        // 12 месяцев
                        '12months_1' => ['days' => 365, 'devices' => 1],
                        '12months_4' => ['days' => 365, 'devices' => 4],
                        '12months_10' => ['days' => 365, 'devices' => 10]
                    ];

                    $config = $tariffConfig[$tariff] ?? ['days' => 30, 'devices' => 1];

                    // Проверяем, не была ли уже выдана подписка для этого платежа
                    $existingUserData = Database::send("SELECT status, date_end FROM qwees_users WHERE uniID = ?", [$uniID]);
                    $existingUser = $existingUserData[0] ?? null;

                    if ($existingUser && $existingUser['status'] === 'on' && strtotime($existingUser['date_end']) > time()) {
                        // Подписка уже активна, не создаем новую
                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                            sprintf(
                                "[%s] [ПОДПИСКА] %s: Подписка уже активна до %s, пропуск создания\n",
                                date('Y-m-d H:i:s'),
                                $uniID,
                                $existingUser['date_end']
                            ),
                            FILE_APPEND
                        );

                        $result['subscription_issued'] = true;
                        $result['subscription_days'] = $config['days'];
                        $result['subscription_devices'] = $config['devices'];
                        $result['subscription_end_date'] = $existingUser['date_end'];
                        $result['vpn_data'] = ['subscription_url' => self::subscriptionUrl($uniID)];

                        return $result;
                    }

                    // Создаем VPN подписку
                    $xray = new Xray();
                    $vpnResult = $xray->addClient($config['days'], $uniID, $config['devices']);

                    if ($vpnResult && $vpnResult['success']) {
                        // Проверяем текущую дату окончания (учитываем бонусные дни от рефералки)
                        $userData = Database::send("SELECT date_end FROM qwees_users WHERE uniID = ?", [$uniID]);
                        $currentEnd = $userData['date_end'] ?? null;

                        // Если подписка активна (включая бонусные дни) - прибавляем, иначе отсчёт от сегодня
                        if ($currentEnd && strtotime($currentEnd) > time()) {
                            $endDate = date('Y-m-d', strtotime($currentEnd . " +{$config['days']} days"));
                        } else {
                            $endDate = date('Y-m-d', strtotime("+{$config['days']} days"));
                        }

                        // Обновляем все необходимые поля в базе данных с реальными данными VPN
                        Database::send(
                            'UPDATE qwees_users SET 
                                status = ?, 
                                subscription = ?, 
                                amount = ?, 
                                count_days = ?, 
                                count_devices = ?, 
                                date_end = ? 
                             WHERE uniID = ?',
                            [
                                'on',
                                self::subscriptionUrl($uniID),
                                $payment->getAmount()?->getValue(),
                                $config['days'],
                                $config['devices'],
                                $endDate,
                                $uniID
                            ]
                        );

                        $result['subscription_issued'] = true;
                        $result['subscription_days'] = $config['days'];
                        $result['subscription_devices'] = $config['devices'];
                        $result['subscription_end_date'] = $endDate;
                        $result['vpn_data'] = $vpnResult['client_data'];

                        // Логирование
                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                            sprintf(
                                "[%s] [ПОДПИСКА] %s: %s (%d дней, %d уст.) - Подписка: %s\n",
                                date('Y-m-d H:i:s'),
                                $uniID,
                                $tariff,
                                $config['days'],
                                $config['devices'],
                                self::subscriptionUrl($uniID)
                            ),
                            FILE_APPEND
                        );
                    } else {
                        // Ошибка при создании VPN клиента - пробуем еще раз с задержкой
                        $result['subscription_error'] = 'Failed to create VPN client. Retrying...';
                        $result['subscription_issued'] = false;

                        // Логируем детальную информацию об ошибке
                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                            sprintf(
                                "[%s] [ОШИБКА - ПОДПИСКА] Первая попытка создания VPN не удалась для %s. Повторная попытка через 5 секунд.\n",
                                date('Y-m-d H:i:s'),
                                $uniID
                            ),
                            FILE_APPEND
                        );

                        // Ждем 5 секунд и пробуем еще раз
                        sleep(5);

                        $xray = new Xray();
                        $vpnResult = $xray->addClient($config['days'], $uniID, $config['devices']);

                        if ($vpnResult && $vpnResult['success']) {
                            // Вторая попытка успешна! Учитываем бонусные дни
                            $userData = Database::send("SELECT date_end FROM qwees_users WHERE uniID = ?", [$uniID]);
                            $currentEnd = $userData['date_end'] ?? null;

                            if ($currentEnd && strtotime($currentEnd) > time()) {
                                $endDate = date('Y-m-d', strtotime($currentEnd . " +{$config['days']} days"));
                            } else {
                                $endDate = date('Y-m-d', strtotime("+{$config['days']} days"));
                            }

                            Database::send(
                                'UPDATE qwees_users SET 
                                    status = ?, 
                                    subscription = ?, 
                                    amount = ?, 
                                    count_days = ?, 
                                    count_devices = ?, 
                                    date_end = ? 
                                 WHERE uniID = ?',
                                [
                                    'on',
                                    self::subscriptionUrl($uniID),
                                    $payment->getAmount()?->getValue(),
                                    $config['days'],
                                    $config['devices'],
                                    $endDate,
                                    $uniID
                                ]
                            );

                            $result['subscription_issued'] = true;
                            $result['subscription_days'] = $config['days'];
                            $result['subscription_devices'] = $config['devices'];
                            $result['subscription_end_date'] = $endDate;
                            $result['vpn_data'] = $vpnResult['client_data'];

                            file_put_contents(
                                $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                                sprintf(
                                    "[%s] [ПОДПИСКА] %s: VPN клиент успешно создан со второй попытки!\n",
                                    date('Y-m-d H:i:s'),
                                    $uniID
                                ),
                                FILE_APPEND
                            );
                        } else {
                            // Вторая попытка тоже неудачна
                            $result['subscription_error'] = 'Failed to create VPN client after retry. Please check server logs.';
                            $result['subscription_issued'] = false;

                            file_put_contents(
                                $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                                sprintf(
                                    "[%s] [ОШИБКА - ПОДПИСКА] Вторая попытка создания VPN также не удалась для %s. Тариф: %s, Дней: %d, Устройств: %d\n",
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
                                $userData = Database::send("SELECT date_end FROM qwees_users WHERE uniID = ?", [$uniID]);
                                $currentEnd = $userData['date_end'] ?? null;

                                if ($currentEnd && strtotime($currentEnd) > time()) {
                                    $endDate = date('Y-m-d', strtotime($currentEnd . " +{$config['days']} days"));
                                } else {
                                    $endDate = date('Y-m-d', strtotime("+{$config['days']} days"));
                                }

                                Database::send(
                                    'UPDATE qwees_users SET 
                                        status = ?, 
                                        subscription = ?, 
                                        amount = ?, 
                                        count_days = ?, 
                                        count_devices = ?, 
                                        date_end = ? 
                                     WHERE uniID = ?',
                                    [
                                        'pending_vpn',
                                        "pending_payment_{$paymentId}",
                                        $payment->getAmount()?->getValue(),
                                        $config['days'],
                                        $config['devices'],
                                        $endDate,
                                        $uniID
                                    ]
                                );

                                file_put_contents(
                                    $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                                    sprintf(
                                        "[%s] [ПОДПИСКА] %s: Данные обновлены, статус 'pending_vpn'\n",
                                        date('Y-m-d H:i:s'),
                                        $uniID
                                    ),
                                    FILE_APPEND
                                );
                            } catch (\Exception $dbError) {
                                file_put_contents(
                                    $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                                    sprintf(
                                        "[%s] [ОШИБКА - БД] Не удалось обновить данные пользователя: %s\n",
                                        date('Y-m-d H:i:s'),
                                        $dbError->getMessage()
                                    ),
                                    FILE_APPEND
                                );
                            }
                        }

                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                            sprintf(
                                "[%s] [ОШИБКА - ПОДПИСКА] Не удалось создать VPN клиент для %s. Платеж оплачен, но требуется ручная настройка.\n",
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
            Database::send(
                'UPDATE qwees_users SET payment_method_id = ? WHERE uniID = ?',
                [$paymentMethodId, $uniID]
            );
            return true;
        } catch (\Exception $e) {
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                sprintf(
                    "[%s] [ОШИБКА - YOOKASSA] savePaymentMethod: %s\n",
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
        string $description = 'Автоплатеж CoraVPN'
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
                $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                sprintf(
                    "[%s] [ОШИБКА - YOOKASSA] createAutoPayment: %s\n",
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
