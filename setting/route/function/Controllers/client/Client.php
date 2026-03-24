<?php
declare(strict_types=1);//cтрогая типизация
namespace setting\route\function\resourses\client;
use App\Config\Database;
class Client extends Database
{

  public static function client($uniID): mixed
  {
    $user = Database::send('SELECT * FROM vpn_users WHERE tg_id = ?', [$tg_id])[0];
    if (!is_array($user) || empty($user)) {//have not
      return [
        'id' => '',
        'uniID' => '',
        'first_name' => '',
        'last_name' => '',
        'subscription' => '',
        'status' => 'off',
        'expiryTime' => 'Нет подписки',
        'refer' => '',
        'mrefer' => '',
        'count_days' => 0,
        'count_devices' => 0,
        'refer_count' => 0,
        'amount' => 0,
      ];
    }

    $expiry_ms = intval($user['date_end'] ?? 0);
    $now = time();
    $showTime = '';
    $showDate = '';
    $status = 'off';

    if ($expiry_ms > 0) {
      $expiry_s = intdiv($expiry_ms, 1000);
      $time_left = $expiry_s - $now;

      if ($time_left > 0) {
        $status = 'on';

        if ($time_left < 86400) {
          $isToday = date('Y-m-d', $now) === date('Y-m-d', $expiry_s);
          $humanTime = date('H:i', $expiry_s);
          $showDate = $isToday ? "Сегодня до $humanTime" : "Завтра до $humanTime";
        } else {
          $showDate = 'до ' . date('d.m.Y', $expiry_s);
        }
        $days = floor($time_left / 86400);
        $hours = floor(($time_left % 86400) / 3600);
        $minutes = floor(($time_left % 3600) / 60);

        if ($days == 1) {
          $daysStr = "$days день";
        } elseif ($days > 1 && $days < 5) {
          $daysStr = "$days дня";
        } elseif ($days > 0) {
          $daysStr = "$days дней";
        } else {
          $daysStr = '';
        }
        $showTime = trim(sprintf('%s %02d ч %02d м', $daysStr, $hours, $minutes));
      } else {
        $status = 'off';
        $showDate = 'Истекла';
        $showTime = 'Подписка отсувствует!';
        Database::send(
          'UPDATE vpn_users SET vpn_freekey = ?, vpn_date_count = ? WHERE tg_id = ?',
          [strval('used')/*купить подписку*/ , intval(0), strval($tg_id)]
        );
        self::CleanUP();
      }
    } else {
      $showDate = 'Нет подписки';
    }

    return [
      'id' => isset($user['id']) ? strval($user['id']) : '', // в Listener создан
      'tg_id' => isset($user['tg_id']) ? strval($user['tg_id']) : '', // в Listener создан
      'tg_username' => isset($user['tg_username']) ? strval($user['tg_username']) : '', // в Listener создан
      'tg_first_name' => isset($user['tg_first_name']) ? strval($user['tg_first_name']) : '', // в Listener создан
      'tg_last_name' => isset($user['tg_last_name']) ? strval($user['tg_last_name']) : '', // в Listener создан
      'vpn_uuid' => isset($user['vpn_uuid']) ? strval($user['vpn_uuid']) : '', // в add_client ещё создан
      'vpn_subscription' => isset($user['vpn_subscription']) ? strval($user['vpn_subscription']) : '', // в add_client ещё создан
      'vpn_freekey' => isset($vpn_freekey) ? $vpn_freekey : 'used', // соответствует логике кнопок
      'kassa_id' => isset($user['kassa_id']) ? strval($user['kassa_id']) : '', // в Ykassa создан
      'vpn_date_count' => isset($user['vpn_date_count']) ? strval($user['vpn_date_count']) : 0, // в add_client создан
      'vpn_divece_count' => isset($user['vpn_divece_count']) ? strval($user['vpn_divece_count']) : 0, // в add_client создан
      'vpn_amount' => isset($user['vpn_amount']) ? intval($user['vpn_amount']) : null, // новое поле
      'card_token' => isset($user['card_token']) ? strval($user['card_token']) : '', // Добавим card_token в ответ
      'refer_link' => isset($user['refer_link']) ? strval($user['refer_link']) : '', // реферальная ссылка
      'refer_count' => isset($user['my_refer_count']) ? intval($user['my_refer_count']) : 0, // количество приглашённых
      'mrefer' => isset($user['my_refer_link']) ? strval($user['my_refer_link']) : '', // собственная реферальная ссылка
      'status' => $status,
      'time' => $showTime,
      'date' => $showDate,
    ];
  }
}
