<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Client\Src;

use App\Config\Database;
class Client
{
    public static function get($uniID = null): array
    {
        if (!isset($uniID) || $uniID === null) {
            $sessionUser = \App\Config\Session::init('user');
            $uniID = is_array($sessionUser) ? ($sessionUser['uniID'] ?? '') : '';
        }

        // LEFT JOIN с подписками - сохраняем тот же API.
        // Строка одна: с максимальным expiry (наследие дублей в старых БД без UNIQUE).
        $user = Database::send(
            'SELECT u.*, s.status as sub_status, s.subscription, s.amount, s.count_days, s.count_devices, s.expiry
             FROM qwees_users u
             LEFT JOIN qwees_subscriptions s ON s.id = (
                 SELECT s2.id FROM qwees_subscriptions s2 WHERE s2.uniID = u.uniID ORDER BY s2.expiry DESC, s2.id DESC LIMIT 1
             )
             WHERE u.uniID = ?',
            [$uniID]
        );

        if (empty($user) || empty($user[0]))
            return ['id' => 0, 'first_name' => '', 'last_name' => '', 'uniID' => '', 'email' => '', 'status' => 'off', 'subscription' => '', 'amount' => 0, 'refer' => '', 'myrefer' => '', 'refer_link' => '', 'refer_id' => 0, 'refer_count' => 0, 'discount_percent' => 0, 'discount_uses' => 0, 'count_days' => 0, 'count_devices' => 0, 'expiry' => 0];

        $data = $user[0];

        return [
            'id' => intval($data['id']),
            'first_name' => strval($data['first_name'] ?? ''),
            'last_name' => strval($data['last_name'] ?? ''),
            'uniID' => strval($data['uniID'] ?? ''),
            'email' => strval($data['email'] ?? ''),
            'status' => strval($data['sub_status'] ?? 'off'),  // из qwees_subscriptions с проверкой истечения
            'subscription' => strval($data['subscription'] ?? ''),  // из qwees_subscriptions
            'amount' => intval($data['amount'] ?? 0),  // из qwees_subscriptions
            'refer' => strval($data['refer'] ?? ''),
            'myrefer' => strval($data['myrefer'] ?? ''),
            'refer_link' => strval($data['refer_link'] ?? ''),
            'refer_id' => intval($data['refer_id'] ?? 0),
            'refer_count' => intval($data['refer_count'] ?? 0),
            'discount_percent' => intval($data['discount_percent'] ?? 0),
            'discount_uses' => intval($data['discount_uses'] ?? 0),
            'count_days' => intval($data['count_days'] ?? 0),  // из qwees_subscriptions
            'count_devices' => intval($data['count_devices'] ?? 0),  // из qwees_subscriptions
            'expiry' => intval($data['expiry'] ?? 0)// из qwees_subscriptions с проверкой истечения (мс)
        ];
    }
}