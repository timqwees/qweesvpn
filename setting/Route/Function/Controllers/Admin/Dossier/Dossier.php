<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin\Dossier;

use App\Config\Database;
use Setting\Route\Function\Controllers\Kassa\PaymentLedger;
use Setting\Route\Function\Controllers\Chat\Chat;
use Setting\Route\Function\Controllers\Server\Network as ServerNetwork;

// Полная сводка по клиенту — только по нужде (лениво из чата/досье).
// Быстрые запросы + один поход в панель; ничего заранее не грузим.

class Dossier
{
    public static function forUser(string $uniID): array
    {
        $uniID = trim($uniID);
        if ($uniID === '') return ['status' => 'error', 'message' => 'Нет uniID'];
        $nowMs = (int) (microtime(true) * 1000);
        $u = Database::send('SELECT u.id, u.first_name, u.last_name, u.email, u.uniID, u.myrefer, u.created_at, s.status AS s_status, s.subscription AS s_sub, s.amount AS s_amount, s.count_devices AS s_devices, s.expiry AS s_expiry FROM qwees_users u LEFT JOIN qwees_subscriptions s ON s.uniID = u.uniID WHERE u.uniID = ? LIMIT 1', [$uniID]);
        $user = (\is_array($u) && isset($u[0])) ? $u[0] : null;
        if ($user === null || ($user['uniID'] ?? '') === '') return ['status' => 'error', 'message' => 'Пользователь не найден'];
        $sub = ($user['s_status'] ?? null) !== null ? $user : null;
        $subInfo = null;
        if ($sub !== null) {
            $expiry = (int) ($sub['s_expiry'] ?? 0);
            $active = (($sub['s_status'] ?? '') === 'on') && $expiry > $nowMs;
            $subInfo = [
                'status' => (string) ($sub['s_status'] ?? ''),
                'active' => $active,
                'plan' => self::planName((string) ($sub['s_sub'] ?? '')),
                'amount' => (string) ($sub['s_amount'] ?? ''),
                'days_left' => $active ? (int) ceil(($expiry - $nowMs) / 86400000) : 0,
                'expiry' => $expiry > 0 ? date('d.m.Y', (int) ($expiry / 1000)) : '—',
                'devices' => (int) ($sub['s_devices'] ?? 0),
            ];
        }
        $payments = PaymentLedger::forUser($uniID, 20);
        $paidTotal = 0.0;
        foreach ($payments as $p) {
            if (($p['status'] ?? '') === 'succeeded') $paidTotal += (float) ($p['amount'] ?? 0);
        }
        $refer = null;
        $myId = (int) ($user['id'] ?? 0);
        if ($myId > 0) {
            $rc = Database::send('SELECT COUNT(*) c FROM qwees_users WHERE refer_id = ?', [$myId]);
            $rl = Database::send('SELECT email, created_at FROM qwees_users WHERE refer_id = ? ORDER BY id DESC LIMIT 10', [$myId]);
            $refer = [
                'myrefer' => (string) ($user['myrefer'] ?? ''),
                'count' => (int) (($rc[0]['c'] ?? 0)),
                'referrals' => array_map(
                    fn($r) => ['email' => (string) ($r['email'] ?? ''), 'created_at' => (string) ($r['created_at'] ?? '')],
                    \is_array($rl) ? $rl : []
                ),
            ];
        }
        $chat = null;
        try {
            $msgs = (new Chat())->getMessages($uniID);
            $chat = ['messages' => \is_array($msgs) ? \count($msgs) : 0];
        } catch (\Throwable) {
        }
        return [
            'status' => 'ok',
            'user' => [
                'name' => trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? '')),
                'email' => (string) ($user['email'] ?? ''),
                'uniID' => $uniID,
                'created' => isset($user['created_at']) ? date('d.m.Y', strtotime((string) $user['created_at'])) : '—',
            ],
            'subscription' => $subInfo,
            'payments' => $payments,
            'paid_total' => round($paidTotal, 2),
            'refer' => $refer,
            'chat' => $chat,
            'xray' => self::xrayState($uniID, $nowMs),
        ];
    }

    /** Ключ в панели: есть/вкл/трафик/онлайн (1 быстрый запрос). */
    private static function xrayState(string $uniID, int $nowMs): ?array
    {
        try {
            ServerNetwork::selectServer($uniID);
            $res = \Setting\Route\Function\Controllers\Vpn\V2ray\PanelHttp::threeXuiHttp('GET', '/panel/api/inbounds/list');
            $list = (\is_array($res) && ($res['success'] ?? false) === true) ? ($res['obj'] ?? null) : null;
        } catch (\Throwable) {
            return null;
        }
        if (!\is_array($list)) return null;
        foreach ($list as $in) {
            foreach ((array) ($in['clientStats'] ?? []) as $c) {
                if ((string) ($c['subId'] ?? '') === $uniID || (string) ($c['email'] ?? '') === $uniID) {
                    $last = (int) ($c['lastOnline'] ?? 0);
                    return [
                        'found' => true,
                        'enable' => (bool) ($c['enable'] ?? false),
                        'online' => ($nowMs - $last) < 300000,
                        'up_h' => self::fmt((int) ($c['up'] ?? 0)),
                        'down_h' => self::fmt((int) ($c['down'] ?? 0)),
                    ];
                }
            }
        }
        return ['found' => false];
    }

    private static function planName(string $sub): string
    {
        if ($sub === '' || $sub === 'trial') return 'Пробная';
        if ($sub === 'bonus') return 'Бонусная';
        if (str_starts_with($sub, 'http')) return 'Оплаченная';
        return $sub;
    }

    private static function fmt(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        $u = ['KB', 'MB', 'GB', 'TB'];
        $i = min((int) floor(log($bytes, 1024)), 4);
        return round($bytes / (1024 ** $i), 2) . ' ' . $u[$i - 1];
    }
}
