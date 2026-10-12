<?php

declare(strict_types=1);

namespace App\Config;

// Простой файловый троттлинг: защита входов и почты от перебора и спама.
// Состояние — tmp-файл (окна короткие, переживать рестарт не обязаны).

class Throttle
{
    private static function file(): string
    {
        return rtrim((string) sys_get_temp_dir(), '/\\') . '/qwees_throttle.json';
    }

    /**
     * IP клиента. CF_CONNECTING_IP ставит сам Cloudflare (через CDN не подделать),
     * общий X-Forwarded-For слушаем только за приватным прокси — иначе любой
     * ротацией заголовка обходил бы лимиты и заливал чужие бакеты.
     */
    public static function ip(): string
    {
        $cf = trim((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
        if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP)) return $cf;
        $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $behindProxy = $remote === ''
            || $remote === '127.0.0.1' || $remote === '::1'
            || str_starts_with($remote, '10.') || str_starts_with($remote, '192.168.')
            || (bool) preg_match('/^172\.(1[6-9]|2\d|3[01])\./', $remote);
        if ($behindProxy) {
            $xff = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
            if ($xff !== '') {
                $first = trim(explode(',', $xff)[0]);
                if (filter_var($first, FILTER_VALIDATE_IP)) return $first;
            }
        }
        return $remote !== '' ? $remote : 'cli';
    }

    /** Разрешён ли вызов (и фиксируем попытку). $max попыток за $windowSec. */
    public static function hit(string $key, int $max, int $windowSec): bool
    {
        $now = time();
        $all = self::read();
        $hits = array_values(array_filter($all[$key] ?? [], fn($t) => ($now - (int) $t) < $windowSec));
        if (\count($hits) >= $max) {
            $all[$key] = $hits;
            self::write($all);
            return false;
        }
        $hits[] = $now;
        $all[$key] = $hits;
        self::write($all);
        return true;
    }

    public static function reset(string $key): void
    {
        $all = self::read();
        unset($all[$key]);
        self::write($all);
    }

    private static function read(): array
    {
        $f = self::file();
        if (!is_file($f)) return [];
        $h = @fopen($f, 'r');
        if (!$h) return [];
        $data = [];
        if (flock($h, LOCK_SH)) {
            $raw = stream_get_contents($h);
            flock($h, LOCK_UN);
            $d = json_decode((string) $raw, true);
            if (\is_array($d)) $data = $d;
        }
        fclose($h);
        return $data;
    }

    private static function write(array $all): void
    {
        $h = @fopen(self::file(), 'c+');
        if (!$h) return;
        if (flock($h, LOCK_EX)) {
            ftruncate($h, 0);
            fwrite($h, json_encode($all, JSON_UNESCAPED_UNICODE));
            flock($h, LOCK_UN);
        }
        fclose($h);
    }
}
