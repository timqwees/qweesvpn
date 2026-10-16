<?php
declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin\Overview;

class OverviewConfig
{
    private const FILE = __DIR__ . '/turn_flags.json';

    private static ?array $cache = null;

    public static function getFlags(): array
    {
        if (self::$cache === null) {
            $data = ['turn_speed_show' => true];
            if (is_file(self::FILE)) {
                $json = json_decode((string) file_get_contents(self::FILE), true);
                if (is_array($json)) $data = array_merge($data, $json);
            }
            self::$cache = $data;
        }
        return self::$cache;
    }

    public static function isSet(string $key): bool
    {
        return (bool) (self::getFlags()[$key] ?? false);
    }
}
