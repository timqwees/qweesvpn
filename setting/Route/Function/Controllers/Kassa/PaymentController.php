<?php

namespace Setting\Route\Function\Controllers\Kassa;

use App\Config\Session;

use Setting\Route\Function\Controllers\Kassa\Kassa;
use Setting\Route\Function\Functions;

class PaymentController
{
    /**
     * Подготовка данных для запроса на создание платежа
     */
    public static function createPayment()
    {
        header('Content-Type: application/json');
        $startTime = microtime(true);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (empty($data['tariff']) || empty($data['paymentMethod'])) {
                throw new \Exception('Missing required data');
            }

            // Сумма считается СЕРВЕРОМ из единого объекта тарифов — клиент не может её подменить
            $tariffCfg = PriceConfig::getTariffConfig()[$data['tariff']] ?? null;
            if ($tariffCfg === null) {
                throw new \Exception('Invalid tariff');
            }
            $amount = PriceConfig::getPrices(PriceConfig::hasReferralDiscount())[$tariffCfg['months']][$tariffCfg['tariff']] * $tariffCfg['months'];

            // Check authentication but don't redirect - return proper error
            $uniID = Session::init('user')['uniID'] ?? null;
            if (!$uniID) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication required. Please login first.',
                    'redirect' => '/auth/login'
                ]);
                return;
            }

            // Используем готовую систему Kassa
            $kassa = new Kassa();

            $site = Functions::site();
            // Описания тарифов строятся автоматически из конфигурации PriceConfig
            $tariffDescriptions = [];
            foreach (PriceConfig::getTariffConfig() as $tariffKey => $cfg) {
                $monthsWord = $cfg['months'] === 1 ? 'месяц' : ($cfg['months'] < 5 ? 'месяца' : 'месяцев');
                $devicesWord = $cfg['devices'] === 1 ? 'устройство' : ($cfg['devices'] < 5 ? 'устройства' : 'устройств');
                $tariffDescriptions[$tariffKey] = 'Подписка ' . $site['ООО'] . ' на ' . $cfg['months'] . ' ' . $monthsWord . ' (' . $cfg['devices'] . ' ' . $devicesWord . ')';
            }

            $description = $tariffDescriptions[$data['tariff']] ?? 'Подписка ' . $site['ООО'];

            $yookassaPaymentMethod = 'card';
            switch ($data['paymentMethod']) {
                case 'sbp':
                    $yookassaPaymentMethod = 'sbp';
                    break;
                case 'sber':
                    $yookassaPaymentMethod = 'sberbank';
                    break;
                case 'iomoney':
                    $yookassaPaymentMethod = 'card';
                    break;
            }

            // запрос на создание оплаты
            $apiStart = microtime(true);
            $paymentResult = $kassa->createPayment(
                amount: (float) $amount,
                description: $description,
                customerEmail: $data['email'] ?? null,
                customerPhone: $data['phone'] ?? null,
                saveCard: $data['saveCard'] ?? false,
                paymentMethod: $yookassaPaymentMethod,
                returnUrl: 'http://' . $_SERVER['HTTP_HOST'] . '/pay/status',
                metadata: [
                    'uniID' => $uniID,
                    'tariff' => $data['tariff'],
                    'payment_method' => $data['paymentMethod']
                ]
            );

            $apiTime = round(microtime(true) - $apiStart, 3);

            //при успешном переходе на страницу оплаты
            if ($paymentResult['success']) {
                Session::init('kassa', [
                    'payment_id' => $paymentResult['payment_id'],
                    'amount' => $amount,
                    'tariff' => $data['tariff'],
                    'paymentMethod' => $data['paymentMethod']
                ]);

                //сохраняем метод оплаты
                if (!empty($paymentResult['payment_method_id'])) {
                    $kassa->savePaymentMethod($uniID, $paymentResult['payment_method_id']);
                }

                $totalTime = round(microtime(true) - $startTime, 3);

                // Логируем время
                file_put_contents(
                    $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
                    sprintf(
                        "[%s] [ОПЛАТА] Создан платеж %s: API=%s сек, Всего=%s сек\n",
                        date('Y-m-d H:i:s'),
                        $paymentResult['payment_id'],
                        $apiTime,
                        $totalTime
                    ),
                    FILE_APPEND
                );

                echo json_encode([
                    'success' => true,
                    'payment_url' => $paymentResult['payment_url'],
                    'payment_id' => $paymentResult['payment_id'],
                    'qr_code' => $paymentResult['qr_code'],
                    'payment_method' => $paymentResult['payment_method']
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new \Exception($paymentResult['error'] ?? 'Payment creation failed');
            }

        } catch (\Exception $e) {
            error_log("Payment Error: " . $e->getMessage());
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
