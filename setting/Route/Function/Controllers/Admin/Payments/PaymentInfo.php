<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin\Payments;

use App\Config\Database;
use Setting\Route\Function\Controllers\Kassa\PaymentLedger;
use YooKassa\Client;

// Свидетельства об оплате: живой чек из кассы, история чеков клиента,
// импорт истории, точная выручка (платежи минус возвраты). Без мусора:
// один класс на все запросы про оплаты.

class PaymentInfo
{
    private const REVENUE_FILE = __DIR__ . '/revenue_cache.json';
    private const REVENUE_TTL = 3600;//пересчёт выручки не чаще раза в час
    private const MAX_PAGES = 20;//предел страниц за один проход (2000 платежей)

    private static ?Client $client = null;
    private static ?array $creds = null;

    private static function kassa(): ?Client
    {
        if (self::$client !== null) return self::$client;
        if (self::$creds === null) {
            $shopId = $_ENV['YOOKASSA_SHOP_ID'] ?? null;
            $secret = $_ENV['YOOKASSA_SECRET_KEY'] ?? null;
            if (!$shopId || !$secret) return null;
            self::$creds = [(string) $shopId, (string) $secret];
        }
        try {
            $c = new Client();
            $c->setAuth(self::$creds[0], self::$creds[1]);
            // Тот же предел, что в Kassa: зависшая касса не должна вешать
            // модалки чеков и пользовательские квитанции (было 80с/30с).
            $api = $c->getApiClient();
            if ($api instanceof \YooKassa\Client\CurlClient) {
                $api->setTimeout(12);
                $api->setConnectionTimeout(6);
            }
            return self::$client = $c;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Сырой список из кассы (без тяжёлой гидратации SDK-моделей — в разы быстрее).
     * @return array{items:array,next:string|null}
     */
    private static function apiList(string $kind, string $from, string $to, ?string $cursor): array
    {
        if (self::$creds === null) {
            $shopId = $_ENV['YOOKASSA_SHOP_ID'] ?? null;
            $secret = $_ENV['YOOKASSA_SECRET_KEY'] ?? null;
            if (!$shopId || !$secret) return ['items' => [], 'next' => null];
            self::$creds = [(string) $shopId, (string) $secret];
        }
        $q = http_build_query([
            'created_at.gte' => $from, 'created_at.lte' => $to, 'limit' => 100,
        ] + ($cursor !== null ? ['cursor' => $cursor] : []));
        $ch = curl_init('https://api.yookassa.ru/v3/' . $kind . '?' . $q);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Basic ' . base64_encode(self::$creds[0] . ':' . self::$creds[1])],
            CURLOPT_TIMEOUT => 25, CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 401 || $code === 403) return ['items' => [], 'next' => null, 'auth_error' => true];
        if ($code !== 200) return ['items' => [], 'next' => null, 'error' => true];
        $d = json_decode((string) $body, true);
        if (!\is_array($d) || !\is_array($d['items'] ?? null)) return ['items' => [], 'next' => null];
        return ['items' => $d['items'], 'next' => $d['next_cursor'] ?? null];
    }

    /** Живой чек из кассы по ID (для модалок). Секретов наружу нет — только факты оплаты. */
    public static function getById(string $paymentId): array
    {
        $paymentId = trim($paymentId);
        if ($paymentId === '') return ['status' => 'error', 'message' => 'Нет ID платежа'];
        $kassa = self::kassa();
        if ($kassa === null) return ['status' => 'error', 'message' => 'Касса не настроена'];
        try {
            $p = $kassa->getPaymentInfo($paymentId);
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'Касса: ' . mb_substr($e->getMessage(), 0, 120)];
        }
        if ($p === null) return ['status' => 'error', 'message' => 'Платёж не найден'];
        $md = [];
        try {
            foreach (($p->getMetadata() ?? []) as $k => $v) $md[(string) $k] = (string) $v;
        } catch (\Throwable) {
        }
        $clientName = '';
        $clientEmail = '';
        $mdUni = (string) ($md['uniID'] ?? '');
        if ($mdUni !== '') {
            $cu = \App\Config\Database::send('SELECT first_name, last_name, email FROM qwees_users WHERE uniID = ? LIMIT 1', [$mdUni]);
            if (\is_array($cu) && isset($cu[0])) {
                $clientName = trim((string) ($cu[0]['first_name'] ?? '') . ' ' . (string) ($cu[0]['last_name'] ?? ''));
                $clientEmail = (string) ($cu[0]['email'] ?? '');
            }
        }
        $method = null;
        $methodType = '';
        try {
            $method = $p->getPaymentMethod();
            $methodType = (string) ($method?->getType() ?: '');
        } catch (\Throwable) {
        }
        $amount = (string) ($p->getAmount()?->getValue() ?? '0');
        $income = null;
        try {
            $incRaw = $p->getIncomeAmount()?->getValue();
            if ($incRaw !== null) $income = (string) $incRaw;
        } catch (\Throwable) {
        }
        $refunded = '0';
        try {
            $refRaw = $p->getRefundedAmount()?->getValue();
            if ($refRaw !== null) $refunded = (string) $refRaw;
        } catch (\Throwable) {
        }
        $captured = '';
        try {
            $captured = $p->getCapturedAt()?->format('d.m.Y H:i') ?? '';
        } catch (\Throwable) {
        }
        return [
            'status' => 'ok',
            'id' => (string) $p->getId(),
            'paid' => (bool) $p->getPaid(),
            'pay_status' => (string) $p->getStatus(),
            'amount' => $amount,
            'currency' => (string) ($p->getAmount()?->getCurrency() ?? 'RUB'),
            'income' => $income,
            'commission' => $income !== null ? (string) round((float) $amount - (float) $income, 2) : null,
            'refunded' => $refunded,
            'captured_at' => $captured,
            'description' => (string) ($p->getDescription() ?? ''),
            'method' => $method !== null ? (string) ($method->getTitle() ?: $method->getType() ?: '') : '',
            'method_type' => $methodType,
            'created_at' => $p->getCreatedAt()?->format('d.m.Y H:i') ?? '',
            'uniID' => (string) ($md['uniID'] ?? ''),
            'client_name' => $clientName,
            'client_email' => $clientEmail !== '' ? $clientEmail : (string) ($md['email'] ?? ($md['customerEmail'] ?? '')),
            'tariff' => (string) ($md['tariff'] ?? ''),
            'email' => (string) ($md['email'] ?? ($md['customerEmail'] ?? '')),
        ];
    }

    /** История чеков клиента из леджера (мгновенно, локально). */
    public static function historyForUser(string $uniID): array
    {
        return PaymentLedger::forUser($uniID);
    }

    /** Разовый импорт истории из кассы: сверяем metadata.uniID с нашими юзерами. */
    public static function importFromKassa(int $days = 90): array
    {
        @set_time_limit(240);
        $users = Database::send('SELECT uniID, email FROM qwees_users');
        $byUni = [];
        foreach ((\is_array($users) ? $users : []) as $u) {
            $byUni[(string) ($u['uniID'] ?? '')] = (string) ($u['email'] ?? '');
        }
        $to = new \DateTime('now', new \DateTimeZone('UTC'));
        $from = (clone $to)->modify("-$days days");
        $ff = fn(\DateTime $d) => $d->format('Y-m-d\TH:i:s') . '.000Z';
        $imported = 0;
        $skipped = 0;
        $pages = 0;
        $cursor = null;
        do {
            $list = self::apiList('payments', $ff($from), $ff($to), $cursor);
            if ($pages === 0 && ($list['auth_error'] ?? false)) return ['status' => 'error', 'message' => 'Касса: неверные ключи'];
            if ($pages === 0 && ($list['error'] ?? false)) return ['status' => 'error', 'message' => 'Касса недоступна'];
            if ($list['items'] === [] && $list['next'] === null) break;
            foreach ($list['items'] as $p) {
                if (!\is_array($p)) {
                    $skipped++;
                    continue;
                }
                $md = (array) ($p['metadata'] ?? []);
                $uni = (string) ($md['uniID'] ?? '');
                if ($uni === '' || !isset($byUni[$uni])) {
                    $skipped++;
                    continue;
                }
                PaymentLedger::record([
                    'payment_id' => (string) ($p['id'] ?? ''),
                    'uniID' => $uni,
                    'email' => $byUni[$uni],
                    'amount' => (float) (($p['amount'] ?? [])['value'] ?? 0),
                    'currency' => (string) (($p['amount'] ?? [])['currency'] ?? 'RUB'),
                    'status' => (string) ($p['status'] ?? ''),
                    'tariff' => (string) ($md['tariff'] ?? ''),
                    'method' => (string) (($p['payment_method'] ?? [])['type'] ?? ''),
                    'description' => (string) ($p['description'] ?? ''),
                ]);
                $imported++;
            }
            $cursor = $list['next'];
            $pages++;
        } while ($cursor !== null && $pages < self::MAX_PAGES);
        return ['status' => 'ok', 'imported' => $imported, 'skipped' => $skipped, 'pages' => $pages, 'truncated' => $cursor !== null];
    }

    /** Нормализация окна: либо дни назад, либо явный from/to (макс. 366 дней). */
    private static function parseRange(int $days, ?string $fromStr, ?string $toStr): array
    {
        $to = new \DateTime('now', new \DateTimeZone('UTC'));
        if ($fromStr !== null && $toStr !== null
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromStr)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toStr)) {
            try {
                $from = new \DateTime($fromStr . ' 00:00:00', new \DateTimeZone('UTC'));
                $end = new \DateTime($toStr . ' 00:00:00', new \DateTimeZone('UTC'));
                if ($end >= $from) {
                    $span = (int) $from->diff($end)->days + 1;
                    if ($span >= 1 && $span <= 366) {
                        return [
                            'days' => $span,
                            'from' => $from,
                            'to' => $end,
                            'fromStr' => $fromStr,
                            'toStr' => $toStr,
                        ];
                    }
                }
            } catch (\Throwable) {
            }
        }
        $days = max(1, min(365, $days));
        return [
            'days' => $days,
            'from' => (clone $to)->modify("-$days days"),
            'to' => $to,
            'fromStr' => '',
            'toStr' => '',
        ];
    }

    /**
     * Точная выручка из кассы: succeeded минус возвраты (возвраты могут прийти позже оплаты).
     * Комиссия кассы в API нет — считаем по ставке из настроек.
     */
    public static function syncRevenue(int $days, float $commissionRate = 0.0, ?string $fromStr = null, ?string $toStr = null): array
    {
        $range = self::parseRange($days, $fromStr, $toStr);
        $days = $range['days'];
        $key = "v6:d$days:r" . round($commissionRate, 4) . ':' . $range['fromStr'] . '_' . $range['toStr'];
        if (is_file(self::REVENUE_FILE)) {
            $hit = json_decode((string) @file_get_contents(self::REVENUE_FILE), true);
            if (\is_array($hit) && ($hit['key'] ?? '') === $key && (time() - (int) ($hit['at'] ?? 0)) < self::REVENUE_TTL && isset($hit['data'])) {
                $hit['data']['cached'] = true;
                return $hit['data'];
            }
        }
        @set_time_limit(240);
        $from = $range['from'];
        $to = $range['to'];
        $ff = fn(\DateTime $d) => $d->format('Y-m-d\TH:i:s') . '.000Z';
        $gross = 0.0;
        $succeeded = 0;
        $canceled = 0;
        $byMethod = [];
        $byMethodCnt = [];
        $byMonth = [];
        $byDay = [];
        $byTariff = [];
        $byTariffCnt = [];
        $incomeTotal = 0.0;
        $incomeCovered = 0;
        $recent = [];
        $cursor = null;
        $pages = 0;
        do {
            $list = self::apiList('payments', $ff($from), $ff($to), $cursor);
            if ($pages === 0 && ($list['auth_error'] ?? false)) return ['status' => 'error', 'message' => 'Касса: неверные ключи'];
            if ($pages === 0 && ($list['error'] ?? false)) return ['status' => 'error', 'message' => 'Касса недоступна'];
            if ($list['items'] === [] && $list['next'] === null) break;
            foreach ($list['items'] as $p) {
                if (!\is_array($p)) continue;
                if (($p['status'] ?? '') === 'succeeded' && ($p['paid'] ?? false)) {
                    $amt = (float) (($p['amount'] ?? [])['value'] ?? 0);
                    $gross += $amt;
                    $succeeded++;
                    $mt = (string) (($p['payment_method'] ?? [])['type'] ?? 'other');
                    $byMethod[$mt] = ($byMethod[$mt] ?? 0) + $amt;
                    $byMethodCnt[$mt] = ($byMethodCnt[$mt] ?? 0) + 1;
                    $tc = (string) ((($p['metadata'] ?? [])['tariff'] ?? '') ?: '—');
                    $byTariff[$tc] = ($byTariff[$tc] ?? 0) + $amt;
                    $byTariffCnt[$tc] = ($byTariffCnt[$tc] ?? 0) + 1;
                    $mk = substr((string) ($p['created_at'] ?? ''), 0, 7);
                    if (preg_match('/^\d{4}-\d{2}$/', $mk)) $byMonth[$mk] = ($byMonth[$mk] ?? 0) + $amt;
                    $dk = substr((string) ($p['created_at'] ?? ''), 0, 10);
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dk)) $byDay[$dk] = ($byDay[$dk] ?? 0) + $amt;
                    $inc = ($p['income_amount'] ?? [])['value'] ?? null;
                    if ($inc !== null) {
                        $incomeTotal += (float) $inc;
                        $incomeCovered++;
                    }
                } elseif (($p['status'] ?? '') === 'canceled') {
                    $canceled++;
                }
                if (\count($recent) < 10 && isset($p['id'])) {
                    $recent[] = [
                        'id' => (string) $p['id'],
                        'date' => substr((string) ($p['created_at'] ?? ''), 0, 16),
                        'status' => (string) ($p['status'] ?? ''),
                        'amount' => (string) (($p['amount'] ?? [])['value'] ?? '0'),
                        'method' => (string) (($p['payment_method'] ?? [])['type'] ?? ''),
                        'method_type' => (string) (($p['payment_method'] ?? [])['type'] ?? ''),
                        'description' => mb_substr((string) ($p['description'] ?? ''), 0, 80),
                    ];
                }
            }
            $cursor = $list['next'];
            $pages++;
        } while ($cursor !== null && $pages < self::MAX_PAGES);
        $refunds = 0.0;
        $refundsCount = 0;
        $cursor = null;
        $pages = 0;
        do {
            $list = self::apiList('refunds', $ff($from), $ff($to), $cursor);
            if ($list['items'] === [] && $list['next'] === null && $pages === 0) break;
            foreach ($list['items'] as $r) {
                if (!\is_array($r)) continue;
                if (($r['status'] ?? '') === 'succeeded') {
                    $refunds += (float) (($r['amount'] ?? [])['value'] ?? 0);
                    $refundsCount++;
                }
            }
            $cursor = $list['next'];
            $pages++;
        } while ($cursor !== null && $pages < self::MAX_PAGES);
        $net = max(0, $gross - $refunds);
        $commissionActual = $incomeCovered > 0 ? max(0, $gross - $incomeTotal) : null;
        $commission = $commissionActual ?? ($net * max(0, $commissionRate));
        ksort($byMonth);
        $monthly = [];
        foreach (\array_slice($byMonth, -12, 12, true) as $mk => $rv) {
            $monthly[] = ['month' => $mk, 'revenue' => round($rv, 2)];
        }
        $tariffs = [];
        foreach ($byTariff as $tc => $rv) {
            $tariffs[] = ['tariff' => $tc, 'revenue' => round($rv, 2), 'cnt' => $byTariffCnt[$tc] ?? 0];
        }
        usort($tariffs, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        $daily = [];
        $cursorDay = clone $from;
        for ($i = 0; $i < $days; $i++) {
            $k = $cursorDay->format('Y-m-d');
            $daily[] = ['day' => $k, 'revenue' => round($byDay[$k] ?? 0, 2)];
            $cursorDay->modify('+1 day');
        }
        $data = [
            'status' => 'ok', 'days' => $days, 'cached' => false,
            'range' => $range['fromStr'] !== '' ? ($range['fromStr'] . ' — ' . $range['toStr']) : ($days . ' дн.'),
            'gross' => round($gross, 2), 'refunds' => round($refunds, 2), 'refunds_count' => $refundsCount,
            'net' => round($net, 2), 'commission' => round($commission, 2),
            'clean' => round($net - $commission, 2),
            'succeeded' => $succeeded, 'canceled' => $canceled, 'by_method' => $byMethod,
            'by_method_cnt' => $byMethodCnt, 'by_tariff' => $tariffs,
            'monthly' => $monthly, 'daily' => $daily, 'recent' => $recent,
            'commission_actual' => $commissionActual !== null ? round($commissionActual, 2) : null,
            'commission_source' => $commissionActual !== null ? 'kassa' : 'rate',
        ];
        $tmp = self::REVENUE_FILE . '.tmp';
        if (@file_put_contents($tmp, json_encode(['key' => $key, 'at' => time(), 'data' => $data], JSON_UNESCAPED_UNICODE)) !== false) {
            @rename($tmp, self::REVENUE_FILE);
        }
        return $data;
    }

    /**
     * Печатная квитанция (HTML + window.print). Данные — только живьём из кассы,
     * секретов нет. Одна верстка на админку и на личный кабинет пользователя.
     */
    public static function renderReceiptHtml(array $d): string
    {
        $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
        $rows = [
            ['Статус', ($d['paid'] ?? false) ? 'Оплачен' : ($d['pay_status'] ?? '—')],
            ['ID платежа', $d['id'] ?? ''], ['Способ', $d['method'] ?? ''], ['Описание', $d['description'] ?? '—'],
            ['Тариф', $d['tariff'] ?? '—'], ['Клиент', trim(($d['uniID'] ?? '') . ' ' . ($d['email'] ?? ''))],
            ['Дата создания', $d['created_at'] ?? '—'], ['Оплачен', $d['captured_at'] ?? '—'],
            ['Комиссия кассы', ($d['commission'] ?? null) !== null ? $d['commission'] . ' ' . ($d['currency'] ?? 'RUB') : '—'],
            ['К получению', ($d['income'] ?? null) !== null ? $d['income'] . ' ' . ($d['currency'] ?? 'RUB') : '—'],
            ['Возвращено', $d['refunded'] ?? '0'],
        ];
        $trs = '';
        foreach ($rows as [$k, $v]) {
            $trs .= '<tr><td>' . $e($k) . '</td><td><b>' . $e($v) . '</b></td></tr>';
        }
        return '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Квитанция ' . $e($d['id'] ?? '') . '</title>'
            . '<style>body{font-family:sans-serif;color:#111;max-width:640px;margin:32px auto;padding:0 16px}'
            . 'h1{font-size:28px;margin:0}h1 small{font-size:14px;color:#666}'
            . 'table{width:100%;border-collapse:collapse;margin:16px 0}td{padding:8px;border-bottom:1px solid #ddd;font-size:14px}td:first-child{color:#666;width:40%}'
            . '.btn{display:inline-block;margin:8px 8px 0 0;padding:10px 18px;background:#16a34a;color:#fff;border-radius:8px;text-decoration:none;font-size:14px}'
            . '@media print{.btn{display:none}}</style></head><body>'
            . '<h1>Квитанция об оплате<br><small>' . $e($d['amount'] ?? '') . ' ' . $e($d['currency'] ?? '') . ' · QweesVPN</small></h1>'
            . '<table>' . $trs . '</table>'
            . '<a class="btn" href="#" onclick="window.print();return false;">Печать / сохранить PDF</a>'
            . '</body></html>';
    }
}
