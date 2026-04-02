<?php

namespace Setting\Route\Function\Controllers\kassa;

use App\Config\Session;

use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\kassa\Kassa;

class PaymentController
{
    /**
     * Подготовка данных для запроса на создание платежа
     */
    public static function createPayment()
    {
        header('Content-Type: application/json');
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (empty($data['tariff']) || empty($data['paymentMethod']) || empty($data['amount'])) {
                throw new \Exception('Missing required data');
            }
            
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
            
            $tariffDescriptions = [
                '1month_1' => 'Подписка QWEES VPN на 1 месяц (1 устройство)',
                '1month_4' => 'Подписка QWEES VPN на 1 месяц (4 устройства)',
                '1month_10' => 'Подписка QWEES VPN на 1 месяц (10 устройств)',
                '6months_1' => 'Подписка QWEES VPN на 6 месяцев (1 устройство)',
                '6months_4' => 'Подписка QWEES VPN на 6 месяцев (4 устройства)',
                '6months_10' => 'Подписка QWEES VPN на 6 месяцев (10 устройств)',
                '12months_1' => 'Подписка QWEES VPN на 12 месяцев (1 устройство)',
                '12months_4' => 'Подписка QWEES VPN на 12 месяцев (4 устройства)',
                '12months_10' => 'Подписка QWEES VPN на 12 месяцев (10 устройств)'
            ];
            
            $description = $tariffDescriptions[$data['tariff']] ?? 'Подписка QWEES VPN';
            
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
            $paymentResult = $kassa->createPayment(
                amount: (float)$data['amount'],
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
            
            //при успешном переходе на страницу оплаты
            if ($paymentResult['success']) {
                Session::init('kassa', [
                    'payment_id' => $paymentResult['payment_id'],
                    'amount' => $data['amount'],
                    'tariff' => $data['tariff'],
                    'paymentMethod' => $data['paymentMethod']
                ]);
                
                //сохраняем метод оплаты
                if (!empty($paymentResult['payment_method_id'])) {
                    $kassa->savePaymentMethod($uniID, $paymentResult['payment_method_id']);
                }
                
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
