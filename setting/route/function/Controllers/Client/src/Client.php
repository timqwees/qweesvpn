<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\Client\src;
use App\Config\Database;

class Client
{
    public static function get(): array
    {
        $user = Database::send('SELECT * FROM qwees_users WHERE uniID = ?', [strval(\App\Config\Session::init('user')['uniID'])]);
        if (empty($user) || empty($user[0]))
            return ['id' => 0, 'first_name' => '', 'last_name' => '', 'uniID' => '', 'email' => '', 'status' => 'off', 'subscription' => '', 'amount' => 0, 'refer' => '', 'myrefer' => '', 'refer_link' => '', 'count_days' => 0, 'count_devices' => 0, 'date_end' => ''];
        return [
            'id' => intval($user[0]['id']),
            'first_name' => strval($user[0]['first_name'] ?? ''),
            'last_name' => strval($user[0]['last_name'] ?? ''),
            'uniID' => strval($user[0]['uniID'] ?? ''),
            'email' => strval($user[0]['email'] ?? ''),
            'status' => strval($user[0]['status'] ?? 'off'),
            'subscription' => strval($user[0]['subscription'] ?? ''),
            'amount' => intval($user[0]['amount'] ?? 0),
            'refer' => strval($user[0]['refer'] ?? ''),
            'myrefer' => strval($user[0]['myrefer'] ?? ''),
            'refer_link' => strval($user[0]['refer_link'] ?? ''),
            'count_days' => intval($user[0]['count_days'] ?? 0),
            'count_devices' => intval($user[0]['count_devices'] ?? 0),
            'date_end' => strval($user[0]['date_end'] ?? '')
        ];
    }
}
