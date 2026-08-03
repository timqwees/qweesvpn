<?php

declare(strict_types=1);

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
        if (preg_match('/iPhone|iPad|iPod|iOS|iPadOS/i', $this->UA)) {
          return [
              'os' => 'iOS',
              'url' => 'https://apps.apple.com/am/app/%D0%B8%D0%B7%D0%B8-vpn/id6746414734',//изи впн
              'api' => 'easyvpn://import?url=',
              'data' => ['logo' => '/public/assets/images/icons/services/install/изиvpn.svg', 'name' => 'Изи VPN']
          ];
        }

        // MacOS
        if (preg_match('/Macintosh|Mac OS|Macintosh; Intel Mac OS/i', $this->UA)) {
          return [
              'os' => 'macOS',
              'url' => 'https://apps.apple.com/am/app/%D0%B8%D0%B7%D0%B8-vpn/id6746414734',//изи впн
              'api' => 'easyvpn://import?url=',
              'data' => ['logo' => '/public/assets/images/icons/services/install/изиvpn.svg', 'name' => 'Изи VPN']
          ];
        }

        // Windows
        if (preg_match('/Windows NT/i', $this->UA)) {
            return [
                'os' => 'Windows',
                'url' => 'https://github.com/INCY-DEV/incy-platforms/releases/download/desktop-v3.3.9/incy-windows-setup.exe',
                'api' => 'incy://add/',
                'data' => ['logo' => 'public/assets/images/icons/services/install/INCY.svg', 'name' => 'INCY']
            ];
        }

        // Linux
        if (preg_match('/Linux/i', $this->UA) && !preg_match('/Android/i', $this->UA)) {
            return [
                'os' => 'Linux',
                'url' => 'https://github.com/INCY-DEV/incy-platforms/releases/download/desktop-v3.3.9/incy-linux-arm64.deb',
                'api' => 'incy://add/',
                'data' => ['logo' => 'public/assets/images/icons/services/install/INCY.svg', 'name' => 'INCY']
            ];
        }

        // Huawei new models (2019+), MatePad, HMS, AppGallery
        if (
            preg_match('/HUAWEI|Huawei|HONOR|MatePad/i', $this->UA) && !preg_match('/Google/i', $this->UA)
        ) {
            return [
                'os' => 'Huawei',
                'url' => 'https://github.com/INCY-DEV/incy-platforms/releases/download/desktop-v3.3.9/Incy.apk',
                'api' => 'incy://add/',
                'data' => ['logo' => 'public/assets/images/icons/services/install/INCY.svg', 'name' => 'INCY']
            ];
        }

        // Android with Google Play
        if (
            preg_match('/Android/i', $this->UA) && (preg_match('/Samsung|SM-|Galaxy|Pixel|Mi|Redmi|POCO|OnePlus|OPPO|Vivo|Realme|Motorola|Sony|Nokia/i', $this->UA) || preg_match('/Google/i', $this->UA) || preg_match('/GMS/i', $this->UA))
        ) {
            return [
                'os' => 'Android',
                'url' => 'https://play.google.com/store/apps/details?id=llc.itdev.incy&pli=1',
                'api' => 'incy://add/',
                'data' => ['logo' => 'public/assets/images/icons/services/install/INCY.svg', 'name' => 'INCY']
            ];
        }

        // Other Android
        if (preg_match('/Android/i', $this->UA)) {
            return [
                'os' => 'Android',
                'url' => 'https://github.com/INCY-DEV/incy-platforms/releases/download/desktop-v3.3.9/Incy.apk',
                'api' => 'incy://add/',
                'data' => ['logo' => 'public/assets/images/icons/services/install/INCY.svg', 'name' => 'INCY']
            ];
        }

        // Fallback
        return [
            'os' => 'Other',
            'url' => 'https://github.com/INCY-DEV/incy-platforms/releases/download/desktop-v3.3.9/Incy.apk',
            'api' => 'incy://add/',
            'data' => ['logo' => 'public/assets/images/icons/services/install/INCY.svg', 'name' => 'INCY']
        ];
    }
}