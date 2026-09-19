<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer\Tools;

use App\Config\Database;
use Setting\Route\Function\Controllers\Refer\ReferInterface\{InterfaceReferRepository, InterfaceReferLog};

//Вся SQL рефералки — только здесь. Таблицы сами дотягиваются (ensure*),
//поэтому работает и на старых БД без ручных миграций.

class ReferRepository implements InterfaceReferRepository
{
    private const USERS_COLS = 'id, uniID, first_name, last_name, myrefer, refer, refer_id, refer_count, discount_percent, discount_uses';

    private InterfaceReferLog $log;
    private static bool $tableEnsured = false;
    private static bool $userColsEnsured = false;
    private static bool $backfilled = false;
    private static bool $indexesEnsured = false;

    public function __construct(?InterfaceReferLog $log = null)
    {
        $this->log = $log ?? new ReferLog();
    }

    public function findUserByCode(string $code): ?array
    {
        $this->ensureUserColumns();
        $this->ensureIndexes();
        $rows = Database::send('SELECT ' . self::USERS_COLS . ' FROM qwees_users WHERE myrefer = ? LIMIT 1', [trim(strtoupper($code))]);
        return (\is_array($rows) && isset($rows[0])) ? $rows[0] : null;
    }

    public function findUserById(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }
        $this->ensureUserColumns();
        $rows = Database::send('SELECT ' . self::USERS_COLS . ' FROM qwees_users WHERE id = ? LIMIT 1', [$userId]);
        return (\is_array($rows) && isset($rows[0])) ? $rows[0] : null;
    }

    public function findUserByUniID(string $uniID): ?array
    {
        if ($uniID === '') {
            return null;
        }
        $this->ensureUserColumns();
        $rows = Database::send('SELECT ' . self::USERS_COLS . ' FROM qwees_users WHERE uniID = ? LIMIT 1', [$uniID]);
        return (\is_array($rows) && isset($rows[0])) ? $rows[0] : null;
    }

    public function bindReferral(int $userId, string $code, int $referrerId): bool
    {
        Database::send('UPDATE qwees_users SET refer = ?, refer_id = ? WHERE id = ?', [$code, $referrerId, $userId]);
        $check = $this->findUserById($userId);
        return $check !== null && ($check['refer'] ?? '') === $code && (int) ($check['refer_id'] ?? 0) === $referrerId;
    }

    public function recordReferral(array $row): bool
    {
        $this->ensureTable();
        $params = [
            (int) ($row['referrer_id'] ?? 0),
            (string) ($row['referrer_uniID'] ?? ''),
            (int) ($row['referral_id'] ?? 0),
            (string) ($row['referral_uniID'] ?? ''),
            (string) ($row['code'] ?? ''),
            (int) ($row['days_to_referral'] ?? 0),
            (int) ($row['days_to_referrer'] ?? 0),
            (int) ($row['discount_percent'] ?? 0),
            (int) ($row['takes_left'] ?? 0),
        ];
        $cols = 'referrer_id, referrer_uniID, referral_id, referral_uniID, code, days_to_referral, days_to_referrer, discount_percent, takes_left';
        if (Database::isMysql()) {
            return (bool) Database::send(
                "INSERT INTO qwees_refer ($cols) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE takes_left = VALUES(takes_left)",
                $params
            );
        }
        return (bool) Database::send(
            "INSERT INTO qwees_refer ($cols) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON CONFLICT(referral_uniID) DO UPDATE SET takes_left = excluded.takes_left",
            $params
        );
    }

    /**
     * Дотянуть историю по старым привязкам (код ввели до появления таблицы).
     * Идемпотентно: добавляет только пары, которых нет. Возвращает число добавленных.
     */
    /** Индексы горячих путей (список рефералов/поиск кода при каждом заходе). Раз за процесс. */
    public function ensureIndexes(): void
    {
        if (self::$indexesEnsured) {
            return;
        }
        self::$indexesEnsured = true;
        foreach (['refer_id', 'myrefer'] as $col) {
            if (Database::isMysql()) {
                $idx = Database::send("SHOW INDEX FROM qwees_users WHERE Column_name = '{$col}'");
                if (!\is_array($idx) || $idx === []) {
                    Database::send("CREATE INDEX idx_users_{$col} ON qwees_users ({$col})");
                }
            } else {
                $idx = Database::send("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'qwees_users' AND sql LIKE '%{$col}%'");
                if (!\is_array($idx) || $idx === []) {
                    Database::send("CREATE INDEX IF NOT EXISTS idx_users_{$col} ON qwees_users ({$col})");
                }
            }
        }
    }

    public function backfillHistory(int $takes): int
    {
        if (self::$backfilled) {
            return 0;//старые привязки дотягиваем один раз, дальше их нет
        }
        self::$backfilled = true;
        $this->ensureTable();
        $takes = max(1, $takes);
        $rows = Database::send(
            'SELECT u.id, u.uniID, u.refer, u.discount_percent, u.discount_uses, r.id AS rid, r.uniID AS runi
             FROM qwees_users u JOIN qwees_users r ON r.id = u.refer_id
             WHERE u.refer_id > 0 AND NOT EXISTS (SELECT 1 FROM qwees_refer h WHERE h.referral_uniID = u.uniID)'
        );
        if (!\is_array($rows) || $rows === []) {
            return 0;
        }
        $n = 0;
        foreach ($rows as $u) {
            if ($this->recordReferral([
                'referrer_id' => (int) ($u['rid'] ?? 0),
                'referrer_uniID' => (string) ($u['runi'] ?? ''),
                'referral_id' => (int) ($u['id'] ?? 0),
                'referral_uniID' => (string) ($u['uniID'] ?? ''),
                'code' => (string) ($u['refer'] ?? ''),
                'days_to_referral' => 0,
                'days_to_referrer' => 0,
                'discount_percent' => (int) ($u['discount_percent'] ?? 0),
                'takes_left' => $takes,
            ])) {
                $n++;
            }
        }
        if ($n > 0) {
            $this->log->log('БЭКФИЛЛ', ['added' => $n, 'takes' => $takes]);
        }
        return $n;
    }

    /** Строка пары «реферер — приглашённый» (там живёт счётчик takes_left). */
    public function getReferralRow(string $referralUniID): ?array
    {
        $this->ensureTable();
        if ($referralUniID === '') {
            return null;
        }
        $rows = Database::send('SELECT * FROM qwees_refer WHERE referral_uniID = ? LIMIT 1', [$referralUniID]);
        return (\is_array($rows) && isset($rows[0])) ? $rows[0] : null;
    }

    /**
     * Потратить одно снятие % с покупок приглашённого.
     * Возвращает остаток после траты, -1 — снимать нечего.
     */
    public function useTake(string $referralUniID): int
    {
        $row = $this->getReferralRow($referralUniID);
        $left = (int) ($row['takes_left'] ?? 0);
        if ($row === null || $left <= 0) {
            return -1;
        }
        Database::send('UPDATE qwees_refer SET takes_left = ? WHERE referral_uniID = ?', [$left - 1, $referralUniID]);
        return $left - 1;
    }

    /** +1 приглашённый. Возвращает новый счётчик. */
    public function incrementReferrer(int $referrerId): int
    {
        $current = $this->findUserById($referrerId);
        $count = (int) ($current['refer_count'] ?? 0) + 1;
        Database::send('UPDATE qwees_users SET refer_count = ? WHERE id = ?', [$count, $referrerId]);
        return $count;
    }

    public function applyReferralDiscount(int $userId, int $percent, int $uses = 0): int
    {
        $this->ensureUserColumns();
        $current = $this->findUserById($userId);
        if ($current === null) {
            return 0;
        }
        $new = max((int) ($current['discount_percent'] ?? 0), $percent);
        if ($percent > 0 && ($new !== (int) ($current['discount_percent'] ?? 0) || $uses > (int) ($current['discount_uses'] ?? 0))) {
            Database::send('UPDATE qwees_users SET discount_percent = ?, discount_uses = ? WHERE id = ?', [$new, $uses, $userId]);
        }
        return $new;
    }

    public function useDiscountByUniID(string $uniID): array
    {
        $this->ensureUserColumns();
        if ($uniID === '') {
            return ['used' => false, 'left' => 0];
        }
        $user = $this->findUserByUniID($uniID);
        $discount = (int) ($user['discount_percent'] ?? 0);
        if ($discount <= 0) {
            return ['used' => false, 'left' => 0];
        }
        $left = (int) ($user['discount_uses'] ?? 0);
        if ($left > 0) {
            $left--;
            if ($left > 0) {
                Database::send('UPDATE qwees_users SET discount_uses = ? WHERE uniID = ?', [$left, $uniID]);
            } else {
                Database::send('UPDATE qwees_users SET discount_uses = 0, discount_percent = 0 WHERE uniID = ?', [$uniID]);
            }
            $this->log->log('СКИДКА-ИСПОЛЬЗОВАНА', ['uniID' => $uniID, 'discount' => $discount, 'left' => $left]);
            return ['used' => true, 'left' => $left];
        }
        Database::send('UPDATE qwees_users SET discount_percent = 0 WHERE uniID = ? AND discount_percent > 0', [$uniID]);
        $this->log->log('СКИДКА-СПИСАНА', ['uniID' => $uniID, 'discount' => $discount, 'reason' => 'legacy(без счётчика)']);
        return ['used' => true, 'left' => 0];
    }

    public function listReferrals(int $referrerId, int $limit = 50): array
    {
        if ($referrerId <= 0) {
            return [];
        }
        $this->ensureIndexes();
        $limit = max(1, min(200, $limit));
        $rows = Database::send(
            'SELECT id, first_name, last_name, email, uniID, created_at FROM qwees_users WHERE refer_id = ' . (int) $referrerId . ' ORDER BY id DESC LIMIT ' . $limit
        );
        return \is_array($rows) ? $rows : [];
    }

    public function countReferrals(int $referrerId): int
    {
        if ($referrerId <= 0) {
            return 0;
        }
        $this->ensureIndexes();
        $rows = Database::send('SELECT COUNT(*) AS c FROM qwees_users WHERE refer_id = ?', [$referrerId]);
        return (\is_array($rows) && isset($rows[0])) ? (int) ($rows[0]['c'] ?? 0) : 0;
    }

    // ==================== Структура ====================

    /** Дотянуть discount_uses на живых БД (в свежих схемах уже есть). */
    public function ensureUserColumns(): void
    {
        if (self::$userColsEnsured) {
            return;
        }
        if (Database::isMysql()) {
            $cols = Database::send('SHOW COLUMNS FROM qwees_users');
            $names = \is_array($cols) ? array_column($cols, 'Field') : [];
            if (!\in_array('discount_uses', $names, true)) {
                Database::send('ALTER TABLE qwees_users ADD COLUMN discount_uses INT DEFAULT 0');
            }
        } else {
            $rows = Database::send("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'qwees_users'");
            $sql = (\is_array($rows) && isset($rows[0])) ? (string) ($rows[0]['sql'] ?? '') : '';
            if (strpos($sql, 'discount_uses') === false) {
                Database::send('ALTER TABLE qwees_users ADD COLUMN discount_uses INTEGER NOT NULL DEFAULT 0');
            }
        }
        self::$userColsEnsured = true;
    }

    public function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }
        if (Database::isMysql()) {
            $cols = Database::send('SHOW COLUMNS FROM qwees_refer');
            if (!\is_array($cols) || $cols === []) {
                Database::send($this->newTableMysql());
            } elseif (!\in_array('takes_left', array_column($cols, 'Field'), true)) {
                $this->migrateLegacy('mysql');
            }
        } else {
            $rows = Database::send("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'qwees_refer'");
            $sql = (\is_array($rows) && isset($rows[0])) ? (string) ($rows[0]['sql'] ?? '') : '';
            if ($sql === '') {
                Database::send($this->newTableSqlite());
            } elseif (strpos($sql, 'takes_left') === false) {
                $this->migrateLegacy('sqlite');
            }
        }
        self::$tableEnsured = true;
    }

    private function newTableSqlite(): string
    {
        return 'CREATE TABLE IF NOT EXISTS qwees_refer (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            referrer_id INTEGER NOT NULL DEFAULT 0,
            referrer_uniID TEXT NOT NULL DEFAULT \'\',
            referral_id INTEGER NOT NULL DEFAULT 0,
            referral_uniID TEXT NOT NULL DEFAULT \'\',
            code TEXT NOT NULL DEFAULT \'\',
            days_to_referral INTEGER NOT NULL DEFAULT 0,
            days_to_referrer INTEGER NOT NULL DEFAULT 0,
            discount_percent INTEGER NOT NULL DEFAULT 0,
            takes_left INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (referral_uniID)
        )';
    }

    private function newTableMysql(): string
    {
        return 'CREATE TABLE IF NOT EXISTS qwees_refer (
            id INT NOT NULL AUTO_INCREMENT,
            referrer_id INT NOT NULL DEFAULT 0,
            referrer_uniID VARCHAR(255) NOT NULL DEFAULT \'\',
            referral_id INT NOT NULL DEFAULT 0,
            referral_uniID VARCHAR(255) NOT NULL DEFAULT \'\',
            code VARCHAR(255) NOT NULL DEFAULT \'\',
            days_to_referral INT NOT NULL DEFAULT 0,
            days_to_referrer INT NOT NULL DEFAULT 0,
            discount_percent INT NOT NULL DEFAULT 0,
            takes_left INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_referral (referral_uniID),
            KEY idx_referrer (referrer_id)
        )';
    }

    private function migrateLegacy(string $driver): void
    {
        $count = Database::send('SELECT COUNT(*) AS c FROM qwees_refer');
        $hasRows = (\is_array($count) && isset($count[0]) && (int) ($count[0]['c'] ?? 0) > 0);
        if ($hasRows) {
            Database::send($driver === 'mysql' ? 'RENAME TABLE qwees_refer TO qwees_refer_legacy' : 'ALTER TABLE qwees_refer RENAME TO qwees_refer_legacy');
            $this->log->log('МИГРАЦИЯ', ['table' => 'qwees_refer', 'action' => 'legacy сохранена в qwees_refer_legacy']);
        } else {
            Database::send('DROP TABLE qwees_refer');
        }
        Database::send($driver === 'mysql' ? $this->newTableMysql() : $this->newTableSqlite());
    }
}
