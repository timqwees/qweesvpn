<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin\Finance;

use App\Models\Network\Network;
use Setting\Route\Function\Controllers\Admin\Admin;
use Setting\Route\Function\Controllers\Admin\AdminDatabase;

//Простой калькулятор ROI: выручка из AdminDatabase, расходы — из costs.json (₽/мес).
//Прибыль = выручка − расходы. ROI = прибыль / расходы × 100%.

class Finance
{
    private const FILE = __DIR__ . '/costs.json';

    private static ?array $costs = null;

    private static function defaults(): array
    {
        return ['servers' => 0, 'other' => 0, 'commission' => 0.0];
    }

    /** Расходы ₽/мес одним массивом. */
    public static function getCosts(): array
    {
        if (self::$costs === null) {
            $data = self::defaults();
            if (is_file(self::FILE)) {
                $json = json_decode((string) file_get_contents(self::FILE), true);
                if (is_array($json)) {
                    $data = array_merge($data, $json);
                }
            }
            self::$costs = $data;
        }
        return self::$costs;
    }

    public static function monthlyCosts(): float
    {
        $c = self::getCosts();
        return max(0, (float) ($c['servers'] ?? 0)) + max(0, (float) ($c['other'] ?? 0));
    }

    public static function saveCosts(array $input): bool
    {
        $cur = self::getCosts();
        $data = [
            'servers' => max(0, (float) ($input['servers'] ?? $cur['servers'])),
            'other' => max(0, (float) ($input['other'] ?? $cur['other'])),
            'commission' => min(1, max(0, (float) ($input['commission'] ?? $cur['commission']))),
        ];
        $ok = (bool) file_put_contents(self::FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($ok) {
            self::$costs = $data;
        }
        return $ok;
    }

    /**
     * Посчитать итоги за месяц (выручка 30 дней из статистики).
     * @return array{revenue:float,costs:float,profit:float,roi:?float,margin:?float,avgCheck:float,breakeven:?int}
     */
    public static function calc(): array
    {
        $fin = AdminDatabase::getFinancialStats();
        $revenue = (float) ($fin['monthlyRevenue'] ?? 0);
        $costs = self::monthlyCosts();
        $profit = $revenue - $costs;
        $avgCheck = (float) ($fin['avgCheck'] ?? 0);
        return [
            'revenue' => $revenue,
            'costs' => $costs,
            'profit' => $profit,
            'roi' => $costs > 0 ? round($profit / $costs * 100, 1) : null,
            'margin' => $revenue > 0 ? round($profit / $revenue * 100, 1) : null,
            'avgCheck' => $avgCheck,
            'breakeven' => $avgCheck > 0 ? (int) ceil($costs / $avgCheck) : null,
        ];
    }

    /**
     * Точный отчёт за период: выручка из кассы (succeeded минус возвраты минус комиссия),
     * расходы за тот же период, MRR, раскладка по тарифам. Фолбэк — леджер, затем БД.
     */
    public static function report(int $days = 30, ?string $from = null, ?string $to = null): array
    {
        $days = max(1, min(365, $days));
        $costs = self::getCosts();
        $rate = (float) ($costs['commission'] ?? 0);
        $rev = \Setting\Route\Function\Controllers\Admin\Payments\PaymentInfo::syncRevenue($days, $rate, $from, $to);
        $days = (int) ($rev['days'] ?? $days);
        $source = 'kassa';
        if (($rev['status'] ?? '') !== 'ok') {
            $local = \Setting\Route\Function\Controllers\Kassa\PaymentLedger::revenue($days);
            $net = max(0, $local['gross'] - $local['lost']);
            $rev = [
                'status' => 'ok', 'days' => $days, 'cached' => false,
                'gross' => round($local['gross'], 2), 'refunds' => round($local['lost'], 2), 'refunds_count' => 0,
                'net' => round($net, 2), 'commission' => round($net * $rate, 2),
                'clean' => round($net * (1 - $rate), 2),
                'succeeded' => $local['count'], 'canceled' => 0, 'by_method' => [],
            ];
            $source = 'ledger';
        }
        $months = $days / 30;
        $totalCosts = (self::monthlyCosts()) * $months;
        $clean = (float) ($rev['clean'] ?? 0);
        $profit = $clean - $totalCosts;
        $avgCheck = ($rev['succeeded'] ?? 0) > 0 ? (float) ($rev['gross'] ?? 0) / max(1, (int) $rev['succeeded']) : 0;
        return [
            'status' => 'ok', 'days' => $days, 'source' => $source,
            'range' => (string) ($rev['range'] ?? ''),
            'cached' => (bool) ($rev['cached'] ?? false),
            'gross' => (float) ($rev['gross'] ?? 0),
            'refunds' => (float) ($rev['refunds'] ?? 0),
            'refunds_count' => (int) ($rev['refunds_count'] ?? 0),
            'net' => round((float) ($rev['gross'] ?? 0) - (float) ($rev['refunds'] ?? 0), 2),
            'commission' => (float) ($rev['commission'] ?? 0),
            'commission_rate' => $rate,
            'clean' => $clean,
            'succeeded' => (int) ($rev['succeeded'] ?? 0),
            'canceled' => (int) ($rev['canceled'] ?? 0),
            'by_method' => $rev['by_method'] ?? [],
            'by_method_cnt' => $rev['by_method_cnt'] ?? [],
            'monthly' => $rev['monthly'] ?? [],
            'daily' => $rev['daily'] ?? [],
            'recent' => $rev['recent'] ?? [],
            'commission_actual' => $rev['commission_actual'] ?? null,
            'commission_source' => (string) ($rev['commission_source'] ?? 'rate'),
            'by_tariff' => $rev['by_tariff'] ?? [],
            'costs' => round($totalCosts, 2),
            'costs_monthly' => self::monthlyCosts(),
            'profit' => round($profit, 2),
            'roi' => $totalCosts > 0 ? round($profit / $totalCosts * 100, 1) : null,
            'margin' => $clean > 0 ? round($profit / $clean * 100, 1) : null,
            'mrr' => round($clean / $months, 2),
            'avg_check' => round($avgCheck, 2),
            'breakeven' => $avgCheck > 0 ? (int) ceil($totalCosts / $avgCheck) : null,
        ];
    }

    /** Сохранение расходов из админки (POST /admin/roi/save). */
    public function onSave(): void
    {
        \Setting\Route\Function\Controllers\Admin\AdminAuth::requirePermission('roi');
        $url = (string) ($_POST['url'] ?? '/admin');
        self::saveCosts([
            'servers' => $_POST['servers'] ?? null,
            'other' => $_POST['other'] ?? null,
            'commission' => isset($_POST['commission']) ? ((float) $_POST['commission'] / 100) : null,
        ]);
        $c = self::getCosts();
        (new Admin())->LoggerCRM("сохранил расходы: серверы {$c['servers']}₽/мес, прочие {$c['other']}₽/мес, комиссия " . ($c['commission'] * 100) . "%");
        Network::onRedirect($url);
    }
}
