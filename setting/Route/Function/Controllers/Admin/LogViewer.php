<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin;

// Единый показ логов: парсинг строк, уровни по тегам (не по подстроке),
// WLC-аудит отделён от системных (wlc-режим — только для Ролей).

class LogViewer
{
    public const LEVEL_COLORS = [
        'error' => 'text-red-400',
        'warn' => 'text-yellow-300',
        'ok' => 'text-green-400',
        'admin' => 'text-violet-300',
        'auth' => 'text-slate-400',
        'move' => 'text-blue-400',
        'audit' => 'text-violet-300',
        'info' => 'text-gray-200',
    ];

    public static function path(): string
    {
        return dirname(__DIR__, 5) . '/' . basename($_ENV['LOG_FILE_NAME'] ?? 'qwees.log');
    }

    /** Последние $limit строк, новые сверху. Читаем с конца — весь файл в память не тянем. */
    public static function tail(int $limit = 300): array
    {
        $file = self::path();
        if (!is_file($file)) return [];
        $limit = max(1, $limit);
        $h = @fopen($file, 'r');
        if (!$h) return [];
        $size = filesize($file);
        $chunk = 65536;
        $pos = $size;
        $buf = '';
        $lines = [];
        while ($pos > 0 && \count($lines) <= $limit) {
            $read = (int) min($chunk, $pos);
            $pos -= $read;
            fseek($h, $pos);
            $buf = (string) fread($h, $read) . $buf;
            $lines = explode("\n", $buf);
        }
        fclose($h);
        $lines = array_values(array_filter(array_map('trim', $lines), fn($l) => $l !== ''));
        return \array_slice(array_reverse($lines), 0, $limit);
    }

    /** Разбор строки: дата, тег, уровень, служебный ли (АДМИН/ВХОД — ранее WLC), работник. */
    public static function parse(string $line): array
    {
        $audit = str_starts_with($line, '[АДМИН]')
            || str_starts_with($line, '[ВХОД]')
            || str_starts_with($line, '[WLC]');
        $date = null;
        $tag = '';
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $m)) {
            $date = $m[1];
        }
        if ($audit) {
            $tag = 'ВХОД';
        } elseif (preg_match('/^\[[^\]]+\]\s+\[([^\]]+)\]/', $line, $m)) {
            $tag = trim($m[1]);
        }
        $worker = null;
        if ($audit && preg_match('/^\[(?:АДМИН|ВХОД|WLC)\] \[[^\]]+\] (.*?): /u', $line, $m)) {
            $worker = trim($m[1]);
        }
        return ['date' => $date, 'tag' => $tag, 'wlc' => $audit, 'worker' => $worker, 'level' => self::level($tag, $line, $audit)];
    }

    private static function level(string $tag, string $line, bool $audit): string
    {
        if ($audit) return 'audit';
        $t = mb_strtoupper($tag);
        if (str_contains($t, 'ОШИБКА') || preg_match('/\b(fail|error)\b|не удал|отклон|Ошибка выполнения/i', $line)) return 'error';
        if (str_starts_with($line, 'DEBUG') || str_starts_with($line, 'WARNING') || str_contains($t, 'ЧАСТИЧНАЯ')) return 'warn';
        if (str_contains($t, 'УСПЕШН') || str_contains($t, 'ВЫДАЧ') || str_contains($t, 'РЕГИСТРАЦИЯ') || str_contains($t, 'СОЗДАН')) return 'ok';
        if (str_starts_with($t, 'АДМИН ПАНЕЛЬ')) return 'admin';
        if (str_contains($t, 'АВТОРИЗАЦИЯ')) return 'auth';
        if (str_contains($t, 'ПЕРЕЕЗД')) return 'move';
        return 'info';
    }

    /**
     * HTML строк. $mode: 'system' (без WLC) или 'audit' (только WLC + data-wkl).
     */
    public static function render(array $lines, string $mode = 'system'): string
    {
        $out = '';
        $lastDate = null;
        foreach ($lines as $line) {
            $p = self::parse((string) $line);
            if ($mode === 'audit' && !$p['wlc']) continue;
            if ($mode === 'system' && $p['wlc']) continue;
            $day = $p['date'] !== null ? substr($p['date'], 0, 10) : null;
            if ($day !== null && $day !== $lastDate) {
                $out .= "<div class='flex gap-2 items-center justify-between text-white/70 text-sm px-2 py-0.5'>"
                    . date('d M Y', strtotime($day) ?: time())
                    . "<div class='flex-1 h-0.5 w-full bg-white/70'></div></div>";
                $lastDate = $day;
            }
            $color = self::LEVEL_COLORS[$p['level']] ?? self::LEVEL_COLORS['info'];
            $esc = htmlspecialchars((string) $line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $wkl = ($mode === 'audit' && $p['worker'] !== null)
                ? " data-wkl='" . htmlspecialchars($p['worker'], ENT_QUOTES) . "'"
                : '';
            $out .= "<div{$wkl} class='text-[13px] font-mono {$color} px-2 py-0.5 rounded select-text'>{$esc}</div>";
        }
        return $out;
    }
}
