<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Kassa;

use App\Config\Database;

// Указатель чеков пользователя: uniID => [{id, date, amount}], + archive.
// Хранит МИНИМУМ (id платежа, дата, сумма) в JSON — без нагрузки на БД.
// ВАЖНО: это НЕ источник правды и НЕ пропуск к чужим данным.
// Источник правды — касса: полная квитанция всегда строится живым запросом
// в ЮKassa, а владение перепроверяется по metadata.uniID == сессии.
// Связка взаимная, лазеек нет:
//   JSON (быстрый список) -> касса (факты) -> metadata.uniID (владелец) -> сессия.
// Даже подделанный JSON не откроет чужой чек: сверка идёт с кассой, а не с файлом.
// При удалении пользователя записи НЕ стираются, а уезжают в archive
// (финансовая целостность + разбор споров), из профиля они пропадают.

class PaymentIndex
{
    private const MAX_PER_USER = 200;

    private static function file(): string
    {
        $dir = __DIR__ . '/Config';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return $dir . '/payments_index.json';
    }

    private static function blank(): array
    {
        return ['users' => [], 'archive' => []];
    }

    /** Атомарное чтение-изменение-запись под LOCK_EX (паттерн Chat::mutate). */
    private static function mutate(callable $fn)
    {
        $file = self::file();
        $fp = @fopen($file, 'c+');
        if ($fp === false) return null;
        flock($fp, LOCK_EX);
        $data = json_decode((string) stream_get_contents($fp), true);
        if (!\is_array($data)) $data = self::blank();
        if (!isset($data['users']) || !\is_array($data['users'])) $data['users'] = [];
        if (!isset($data['archive']) || !\is_array($data['archive'])) $data['archive'] = [];
        $result = $fn($data);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string) json_encode($data, JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return $result;
    }

    /** Быстрое чтение под LOCK_SH (профиль, проверка владения). */
    private static function load(): array
    {
        $file = self::file();
        if (!is_file($file)) return self::blank();
        $fp = @fopen($file, 'r');
        if ($fp === false) return self::blank();
        flock($fp, LOCK_SH);
        $data = json_decode((string) stream_get_contents($fp), true);
        flock($fp, LOCK_UN);
        fclose($fp);
        if (!\is_array($data)) return self::blank();
        return $data;
    }

    private static function validId(string $id): bool
    {
        return (bool) preg_match('/^[\w\-]{8,64}$/u', $id);
    }

    /** Запись покупки (идемпотентно по id). Только успешные оплаты. */
    public static function add(string $uniID, string $paymentId, string $date, float $amount): bool
    {
        $uniID = trim($uniID);
        $paymentId = trim($paymentId);
        if ($uniID === '' || !self::validId($paymentId) || $amount < 0) return false;
        if ($date === '') $date = date('Y-m-d H:i:s');
        return (bool) self::mutate(function (&$data) use ($uniID, $paymentId, $date, $amount) {
            $list = $data['users'][$uniID] ?? [];
            if (!\is_array($list)) $list = [];
            foreach ($list as $row) {
                if (($row['id'] ?? '') === $paymentId) return true; // уже есть
            }
            array_unshift($list, ['id' => $paymentId, 'date' => $date, 'amount' => round($amount, 2)]);
            $data['users'][$uniID] = \array_slice($list, 0, self::MAX_PER_USER);
            return true;
        });
    }

    /** Список чеков пользователя, новые сверху. Только id+дата+сумма. */
    public static function forUser(string $uniID, int $limit = 50): array
    {
        $uniID = trim($uniID);
        if ($uniID === '') return [];
        $data = self::load();
        $list = $data['users'][$uniID] ?? [];
        if (!\is_array($list)) return [];
        $limit = max(1, min(self::MAX_PER_USER, $limit));
        $out = [];
        foreach (\array_slice($list, 0, $limit) as $row) {
            if (!\is_array($row) || !self::validId((string) ($row['id'] ?? ''))) continue;
            $out[] = [
                'id' => (string) $row['id'],
                'date' => (string) ($row['date'] ?? ''),
                'amount' => (float) ($row['amount'] ?? 0),
            ];
        }
        return $out;
    }

    /** Есть ли id в истории пользователя (предпроверка; финал — всегда по кассе). */
    public static function owns(string $uniID, string $paymentId): bool
    {
        $uniID = trim($uniID);
        $paymentId = trim($paymentId);
        if ($uniID === '' || !self::validId($paymentId)) return false;
        $data = self::load();
        $list = $data['users'][$uniID] ?? [];
        if (!\is_array($list)) return false;
        foreach ($list as $row) {
            if (($row['id'] ?? '') === $paymentId) return true;
        }
        return false;
    }

    /**
     * Удаление пользователя: история уезжает в archive (не стирается).
     * Профиль исчезает вместе с аккаунтом, но финансы и разбор споров целы.
     * Ключ архива — uniID + время, чтобы повторная регистрация не подхватила чужое.
     */
    public static function archive(string $uniID): bool
    {
        $uniID = trim($uniID);
        if ($uniID === '') return false;
        return (bool) self::mutate(function (&$data) use ($uniID) {
            $list = $data['users'][$uniID] ?? null;
            unset($data['users'][$uniID]);
            if (\is_array($list) && $list !== []) {
                $data['archive'][$uniID . '@' . date('Ymd-His')] = [
                    'moved_at' => date('Y-m-d H:i:s'),
                    'items' => \array_slice($list, 0, self::MAX_PER_USER),
                ];
            }
            return true;
        });
    }

    /**
     * Ленивый бэкфилл старых оплат из леджера (один раз на пользователя:
     * вызывается, только если индекс пуст). Дальше всё пишется хуком при оплате.
     */
    public static function backfillFromLedger(string $uniID): int
    {
        $uniID = trim($uniID);
        if ($uniID === '') return 0;
        try {
            PaymentLedger::ensureTable();
            $rows = Database::send(
                "SELECT payment_id, amount, created_at FROM qwees_payments WHERE uniID = ? AND status = 'succeeded' ORDER BY created_at DESC LIMIT " . self::MAX_PER_USER,
                [$uniID]
            );
        } catch (\Throwable) {
            return 0;
        }
        if (!\is_array($rows) || $rows === []) return 0;
        $n = 0;
        foreach (array_reverse($rows) as $r) { // старые первыми, новые всплывут наверх
            $id = (string) ($r['payment_id'] ?? '');
            if (!self::validId($id)) continue;
            if (self::add($uniID, $id, (string) ($r['created_at'] ?? ''), (float) ($r['amount'] ?? 0))) $n++;
        }
        return $n;
    }
}
