<?
function getV2RayTunInstallUrl()
{
  $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

  // iOS/iPadOS devices
  if (preg_match('/iPhone|iPad|iPod|iOS|iPadOS/i', $ua)) {
    return [
      'os' => 'iOS',
      'url' => 'https://apps.apple.com/us/app/v2raytun/id6476628951'
    ];
  }
  // MacOS
  if (preg_match('/Macintosh|Mac OS|Macintosh; Intel Mac OS/i', $ua)) {
    return [
      'os' => 'macOS',
      'url' => 'https://apps.apple.com/us/app/v2raytun/id6476628951'
    ];
  }

  // Windows
  if (preg_match('/Windows NT/i', $ua)) {
    return [
      'os' => 'Windows',
      'url' => 'https://storage.v2raytun.com/v2RayTun_Setup.exe'
    ];
  }

  // Linux
  if (preg_match('/Linux/i', $ua) && !preg_match('/Android/i', $ua)) {
    return [
      'os' => 'Linux',
      'url' => 'https://github.com/mdf45/v2raytun/archive/refs/tags/v2.5.8.tar.gz'
    ];
  }

  // Huawei new models (2019+), MatePad, HMS, AppGallery
  if (
    preg_match('/HUAWEI|Huawei|HONOR|MatePad/i', $ua)
    && !preg_match('/Google/i', $ua)
  ) {
    return [
      'os' => 'Huawei',
      'url' => 'https://github.com/DigneZzZ/v2raytun/releases/latest/download/v2RayTun_universal.apk'
    ];
  }

  // Android with Google Play
  if (
    preg_match('/Android/i', $ua) &&
    (
      preg_match('/Samsung|SM-|Galaxy|Pixel|Mi|Redmi|POCO|OnePlus|OPPO|Vivo|Realme|Motorola|Sony|Nokia/i', $ua)
      || preg_match('/Google/i', $ua)
      || preg_match('/GMS/i', $ua)
    )
  ) {
    return [
      'os' => 'Android',
      'url' => 'https://play.google.com/store/apps/details?id=com.v2raytun.android'
    ];
  }

  // Other Android
  if (preg_match('/Android/i', $ua)) {
    return [
      'os' => 'Android',
      'url' => 'https://play.google.com/store/apps/details?id=com.v2raytun.android'
    ];
  }

  // Fallback
  return [
    'os' => 'Other',
    'url' => 'https://github.com/DigneZzZ/v2raytun/releases/latest/download/v2RayTun_universal.apk'
  ];
}
