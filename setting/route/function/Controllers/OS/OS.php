<?php declare(strict_types=1);

namespace Setting\Route\Function\Controllers\OS;

class OS
{

    public string $UA;

    public function __construct()
    {
        $this->UA = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function getOS()
    {

        // iOS/iPadOS devices
        if (preg_match('/iPhone|iPad|iPod|iOS|iPadOS/i', self::$UA)) {
            return [
                'os' => 'iOS',
                'url' => 'https://apps.apple.com/ru/app/happ-proxy-utility-plus/id6746188973'
            ];
        }
        // MacOS
        if (preg_match('/Macintosh|Mac OS|Macintosh; Intel Mac OS/i', self::$UA)) {
            return [
                'os' => 'macOS',
                'url' => 'https://apps.apple.com/sv/app/happ-proxy-utility/id6504287215'
            ];
        }

        // Windows
        if (preg_match('/Windows NT/i', self::$UA)) {
            return [
                'os' => 'Windows',
                'url' => 'https://github.com/Happ-proxy/happ-desktop/releases/latest/download/setup-Happ.x64.exe'
            ];
        }

        // Linux
        if (preg_match('/Linux/i', self::$UA) && !preg_match('/Android/i', self::$UA)) {
            return [
                'os' => 'Linux',
                'url' => 'https://github.com/Happ-proxy/happ-desktop/releases/latest/download/Happ.linux.x64.pkg.tar.zst'
            ];
        }

        // Huawei new models (2019+), MatePad, HMS, AppGallery
        if (
            preg_match('/HUAWEI|Huawei|HONOR|MatePad/i', self::$UA)
            && !preg_match('/Google/i', self::$UA)
        ) {
            return [
                'os' => 'Huawei',
                'url' => 'https://github.com/Happ-proxy/happ-android/releases/latest/download/Happ.apk'
            ];
        }

        // Android with Google Play
        if (
            preg_match('/Android/i', self::$UA) &&
            (
                preg_match('/Samsung|SM-|Galaxy|Pixel|Mi|Redmi|POCO|OnePlus|OPPO|Vivo|Realme|Motorola|Sony|Nokia/i', self::$UA)
                || preg_match('/Google/i', self::$UA)
                || preg_match('/GMS/i', self::$UA)
            )
        ) {
            return [
                'os' => 'Android',
                'url' => 'https://play.google.com/store/apps/details?id=com.happproxy'
            ];
        }

        // Other Android
        if (preg_match('/Android/i', self::$UA)) {
            return [
                'os' => 'Android',
                'url' => 'https://github.com/Happ-proxy/happ-android/releases/latest/download/Happ.apk'
            ];
        }

        // Fallback
        return [
            'os' => 'Other',
            'url' => 'https://github.com/Happ-proxy/happ-android/releases/latest/download/Happ.apk'
        ];
    }
}