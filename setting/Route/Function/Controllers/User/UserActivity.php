<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\User;

class UserActivity
{
    private const ACTIVITY_DIR = __DIR__ . '/storage/activity';
    private const MAX_RECORDS = 100;
    private const DAY_MS = 86400000;

    private static function file(string $uniID): string
    {
        $hash = hash('xxh3', $uniID);
        return self::ACTIVITY_DIR . '/' . substr($hash, 0, 2) . '/' . $uniID . '.json';
    }

    private static function ensureDir(): void
    {
        if (!is_dir(self::ACTIVITY_DIR)) {
            mkdir(self::ACTIVITY_DIR, 0755, true);
        }
    }

    public static function log(string $uniID, string $action): bool
    {
        if ($uniID === '' || $action === '') {
            return false;
        }
        self::ensureDir();
        $file = self::file($uniID);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $entry = ['action' => $action, 'ts' => time()];
        $records = self::read($uniID);
        $records[] = $entry;
        if (\count($records) > self::MAX_RECORDS) {
            $records = \array_slice($records, -self::MAX_RECORDS);
        }
        $tmp = $file . '.tmp';
        $ok = file_put_contents($tmp, json_encode($records, JSON_UNESCAPED_UNICODE), LOCK_EX);
        if ($ok !== false) {
            rename($tmp, $file);
            self::rotateOld($file, $records);
            return true;
        }
        return false;
    }

    private static function rotateOld(string $file, array $records): void
    {
        $now = time();
        $kept = [];
        foreach ($records as $r) {
            if (($now - (int) ($r['ts'] ?? 0)) < 30 * self::DAY_MS) {
                $kept[] = $r;
            }
        }
        if (\count($kept) !== \count($records)) {
            file_put_contents($file, json_encode($kept, JSON_UNESCAPED_UNICODE), LOCK_EX);
        }
    }

    public static function read(string $uniID): array
    {
        $file = self::file($uniID);
        if (!is_file($file)) {
            return [];
        }
        $data = @file_get_contents($file);
        if ($data === false) {
            return [];
        }
        $arr = json_decode($data, true);
        return \is_array($arr) ? $arr : [];
    }

    public static function getActions(string $uniID, int $limit = 20): array
    {
        $records = self::read($uniID);
        $records = array_reverse($records);
        return array_slice($records, 0, $limit);
    }
}
