<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\client;
use App\Config\Database;
class Client
{

  public static function get($uniID): array
  {
    $result = Database::send('SELECT * FROM qwees_users WHERE uniID = ?', [strval($uniID)]);
    
    if (empty($result) || !$result[0]) {
      return [
        'id' => 0,
        'first_name' => '',
        'last_name' => '',
        'uniID' => '',
        'email' => '',
        'status' => 'off',
        'subscription' => '',
        'amount' => 0,
        'refer' => '',
        'myrefer' => '',
        'count_days' => 0,//которое было куплено
        'count_devices' => 0,
        'date_end' => 0,//дата конца подписки в формате d-m-Y
      ];
    }

    $user = $result[0];
    return [
      'id' => isset($user['id']) ? intval($user['id']) : 0,
      'first_name' => isset($user['first_name']) ? strval($user['first_name']) : '',
      'last_name' => isset($user['last_name']) ? strval($user['last_name']) : '',
      'uniID' => isset($user['uniID']) ? intval($user['uniID']) : 0,
      'email' => isset($user['email']) ? strval($user['email']) : '',
      'status' => isset($user['status']) ? strval($user['status']) : 'off',
      'subscription' => isset($user['subscription']) ? strval($user['subscription']) : '',
      'amount' => isset($user['amount']) ? intval($user['amount']) : 0,
      'refer' => isset($user['refer']) ? strval($user['refer']) : '',
      'myrefer' => isset($user['myrefer']) ? strval($user['myrefer']) : '',
      'count_days' => isset($user['count_days']) ? intval($user['count_days']) : 0,
      'count_devices' => isset($user['count_devices']) ? intval($user['count_devices']) : 0,
      'date_end' => isset($user['date_end']) ? strval($user['date_end']) : 0
    ];
  }
}
