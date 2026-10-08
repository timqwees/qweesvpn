<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Kassa;

use App\Config\Database;

// Леджер платежей: каждая оплата фиксируется при создании и при успехе.
// Источник правды для чеков, досье и точной выручки (вместо гаданий по подпискам).

class PaymentLedger
{
    public static function ensureTable(): void
    {
        Database::send('CREATE TABLE IF NOT EXISTS qwees_payments (payment_id VARCHAR(64) NOT NULL, uniID VARCHAR(255) NOT NULL DEFAULT \'\', email VARCHAR(255) NOT NULL DEFAULT \'\', amount DECIMAL(10,2) NOT NULL DEFAULT 0, currency VARCHAR(8) NOT NULL DEFAULT \'RUB\', status VARCHAR(32) NOT NULL DEFAULT \'\', tariff VARCHAR(64) NOT NULL DEFAULT \'\', method VARCHAR(32) NOT NULL DEFAULT \'\', description VARCHAR(255) NOT NULL DEFAULT \'\', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (payment_id))');
    }

    /** Миграция существующих таблиц (CREATE IF NOT EXISTS колонку не добавит). */
    private static bool $methodEnsured = false;

    private static function ensureMethodColumn(): void
    {
        if (self::$methodEnsured) return;
        self::$methodEnsured = true;
        $has = false;
        if (Database::isMysql()) {
            $cols = Database::send('SHOW COLUMNS FROM qwees_payments');
            foreach ((\is_array($cols) ? $cols : []) as $c) {
                if (($c['Field'] ?? '') === 'method') {
                    $has = true;
                    break;
                }
            }
        } else {
            $t = Database::send("SELECT sql FROM sqlite_master WHERE type='table' AND name='qwees_payments'");
            $has = str_contains((string) (($t[0]['sql'] ?? '')), 'method');
        }
        if (!$has) {
            Database::send('ALTER TABLE qwees_payments ADD COLUMN method VARCHAR(32) NOT NULL DEFAULT \'\'');
        }
    }

    /**
     * Запись/обновление платежа (идемпотентно по payment_id).
     */
    public static function record(array $p): bool
    {
        self::ensureTable();
        self::ensureMethodColumn();
        $id = (string) ($p['payment_id'] ?? '');
        if ($id === '') return false;
        $params = [
            $id,
            (string) ($p['uniID'] ?? ''),
            (string) ($p['email'] ?? ''),
            (float) ($p['amount'] ?? 0),
            (string) ($p['currency'] ?? 'RUB'),
            (string) ($p['status'] ?? ''),
            (string) ($p['tariff'] ?? ''),
            (string) ($p['method'] ?? ''),
            (string) ($p['description'] ?? ''),
        ];
        if (Database::isMysql()) {
            $res = Database::send(
                'INSERT INTO qwees_payments (payment_id, uniID, email, amount, currency, status, tariff, method, description, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE uniID = VALUES(uniID), email = VALUES(email), amount = VALUES(amount),
                 currency = VALUES(currency), status = VALUES(status), tariff = VALUES(tariff), method = VALUES(method),
                 description = VALUES(description), updated_at = CURRENT_TIMESTAMP',
                $params
            );
        } else {
            $res = Database::send(
                'INSERT OR REPLACE INTO qwees_payments (payment_id, uniID, email, amount, currency, status, tariff, method, description, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)',
                $params
            );
        }
        return $res !== false;
    }

    /** История чеков пользователя, новые сверху. */
    public static function forUser(string $uniID, int $limit = 50): array
    {
        self::ensureTable();
        $limit = max(1, min(200, $limit));
        $rows = Database::send("SELECT payment_id, uniID, email, amount, currency, status, tariff, method, description, created_at FROM qwees_payments WHERE uniID = ? ORDER BY created_at DESC LIMIT $limit", [$uniID]);
        return \is_array($rows) ? $rows : [];
    }

    /** Выручка из леджера за N дней (быстро, локально). */
    public static function revenue(int $days): array
    {
        self::ensureTable();
        $cut = date('Y-m-d H:i:s', time() - $days * 86400);
        $row = Database::send("SELECT SUM(CASE WHEN status = 'succeeded' THEN amount ELSE 0 END) gross, SUM(CASE WHEN status IN ('canceled','refunded') THEN amount ELSE 0 END) lost, COUNT(CASE WHEN status = 'succeeded' THEN 1 END) cnt FROM qwees_payments WHERE created_at >= ?", [$cut]);
        $row = (\is_array($row) && isset($row[0])) ? $row[0] : [];
        return [
            'gross' => (float) ($row['gross'] ?? 0),
            'lost' => (float) ($row['lost'] ?? 0),
            'count' => (int) ($row['cnt'] ?? 0),
        ];
    }

}
