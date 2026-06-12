<?php 

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Client\Src;

use App\Config\Database;
use DateTime;
use DateTimeZone;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;
class Client
{
    public static function get($uniID = null): array
    {
        if(!isset($uniID) || $uniID === null){
        $sessionUser = \App\Config\Session::init('user');
        $uniID = is_array($sessionUser) ? ($sessionUser['uniID'] ?? '') : '';
        }
        
        // LEFT JOIN с подписками - сохраняем тот же API
        $user = Database::send(
            'SELECT u.*, s.status as sub_status, s.subscription, s.amount, s.count_days, s.count_devices, s.date_end 
             FROM qwees_users u 
             LEFT JOIN qwees_subscriptions s ON u.uniID = s.uniID 
             WHERE u.uniID = ?',
            [$uniID]
        );

        if (empty($user) || empty($user[0]))
            return ['id' => 0, 'first_name' => '', 'last_name' => '', 'uniID' => '', 'email' => '', 'status' => 'off', 'subscription' => '', 'amount' => 0, 'refer' => '', 'myrefer' => '', 'refer_link' => '', 'count_days' => 0, 'count_devices' => 0, 'date_end' => ''];

        $data = $user[0];

        $status = strval($data['sub_status'] ?? 'off');
        $dateEnd = strval($data['date_end'] ?? '');
        $uniID = strval($data['uniID'] ?? '');

        // Проверяем, не истекла ли подписка
        if ($status === 'on' && !empty($dateEnd)) {
            //проверка времени
            $timezone = new DateTimeZone('Europe/Moscow');//UTC+3
            $current_DateTime = new DateTime('now', $timezone);//текущее время
            $end_DateTime = new DateTime($dateEnd . " 23:59:59", $timezone);//время окончания
            if ($end_DateTime < $current_DateTime) {
                $status = 'off'; // Подписка истекла

                // Удаляем истекшую подписку из БД и X-UI
                if (!empty($uniID)) {
                    try {
                        $xray = new Xray();
                        $xray->DeleteKey($uniID);
                    } catch (\Exception $e) {
                        // Игнорируем ошибки панели — клиент мог быть уже удалён
                    }

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
                }
                $dateEnd = '';
                $data['subscription'] = '';
                $data['count_days'] = 0;
                $data['count_devices'] = 0;
                $data['amount'] = 0;
            }
        }

        return [
            'id' => intval($data['id']),
            'first_name' => strval($data['first_name'] ?? ''),
            'last_name' => strval($data['last_name'] ?? ''),
            'uniID' => strval($data['uniID'] ?? ''),
            'email' => strval($data['email'] ?? ''),
            'status' => $status,  // из qwees_subscriptions с проверкой истечения
            'subscription' => strval($data['subscription'] ?? ''),  // из qwees_subscriptions
            'amount' => intval($data['amount'] ?? 0),  // из qwees_subscriptions
            'refer' => strval($data['refer'] ?? ''),
            'myrefer' => strval($data['myrefer'] ?? ''),
            'refer_link' => strval($data['refer_link'] ?? ''),
            'count_days' => intval($data['count_days'] ?? 0),  // из qwees_subscriptions
            'count_devices' => intval($data['count_devices'] ?? 0),  // из qwees_subscriptions
            'date_end' => $dateEnd  // из qwees_subscriptions
        ];
    }
}
