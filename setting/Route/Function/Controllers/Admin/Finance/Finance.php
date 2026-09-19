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
        return ['servers' => 0, 'other' => 0];
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

    /** Сохранение расходов из админки (POST /admin/roi/save). */
    public function onSave(): void
    {
        $url = (string) ($_POST['url'] ?? '/admin');
        self::saveCosts([
            'servers' => $_POST['servers'] ?? null,
            'other' => $_POST['other'] ?? null,
        ]);
        $c = self::getCosts();
        (new Admin())->LoggerCRM("сохранил расходы: серверы {$c['servers']}₽/мес, прочие {$c['other']}₽/мес");
        Network::onRedirect($url);
    }
}
