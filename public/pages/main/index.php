<?php
use App\Models\Network\Network;
use Setting\Route\Function\Controllers\{Auth\Auth, Client\GetUser, Language\Language, OS\OS, Vpn\VpnStatus, Profile\Profile, System\SystemInfo, Refer\Refer, Refer\Config\ReferConfig};
use Setting\Route\Function\Controllers\Server\Network as ServerNetwork;
use Setting\Route\Function\Controllers\Kassa\PaymentIndex;
use Setting\Route\Function\Controllers\Gifts\Gifts;
use Setting\Route\Function\Functions;

Auth::auth();//проверка авторизации
//====================================================================================
$user = new GetUser();
if (!$user->onCheckSubscription() && (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/') !== '/')//получает информацию пользователя и проверят подписку (false - обновление подписки | true - все ок)
    Network::onRedirect('/');
if ($user->onPaymantStatus())//если в сесии есть payment_id, то оплата не проверена
    Network::onRedirect('/pay/status');//перенаправляем на страницу проверки
// Ленивая добивка зависших выдач: один дешёвый SELECT, панели трогаем только если есть pending_vpn
try {
    $pendingFixed = \Setting\Route\Function\Controllers\Kassa\Kassa::retryPendingForUser($user->getUniID(), 2);
    if (($pendingFixed['issued'] ?? 0) > 0) $user = new GetUser();//данные обновились — перечитываем
} catch (\Throwable) {
}
//===================================================================================
$site = Functions::site();//после всех провроек получем уже данные сервиса
$gifts = new Gifts();//пробные
$giftShow = $gifts->isView() && $user->getStatus() !== 'on';//положено + нет активной подписки

// язык
$currentLanguage = Language::getCurrent();
$translations = Language::getTranslations($currentLanguage);

// Хелпер для короткого доступа к переводам
$t = fn(string $key): string => $translations[$key] ?? $key;

// Получаем реальные данные через новые классы
$vpnStatusObj = new VpnStatus();
$usageStats = $vpnStatusObj->getUsageStats();

// Список приглашённых по реферальной ссылке (для секции referal)
$referralsList = (new Refer())->getMyReferrals($user->getID());

// Что получают стороны (цифры из настроек рефералки)
$refNew = ReferConfig::getNewReferralBonus();
$refRef = ReferConfig::getReferrerBonus();
$referWhat = [
    'invited_days' => str_replace('{d}', (string) $refNew['days_added'], $t('refer_g_days')),
    'invited_discount' => str_replace(['{p}', '{n}'], [(string) $refNew['discount_percent'], (string) $refNew['discount_uses']], $t('refer_g_discount')),
    'inviter_each' => str_replace('{d}', (string) $refRef['days_per_referral'], $t('refer_g_each')),
    'inviter_percent' => str_replace(['{p}', '{n}'], [(string) $refRef['percent'], (string) $refRef['takes']], $t('refer_g_percent')),
];

// Серверы для выбора в профиле (реестр Network) + текущий сервер пользователя
$availableServers = ServerNetwork::getAvailableServers();
ServerNetwork::selectServer($user->getUniID());
$currentServerCode = ServerNetwork::getServerCode();

// Оптимизированное формирование данных без лишних вызовов
$vpnStatus = $vpnStatusObj->getStatus();
$isActiveSub = $vpnStatus === 'active';
// Пинг/DNS только при активной подписке: без неё результат всё равно
// заменяется прочерками ниже, а fsockopen (до 0.5с) и gethostbyname
// тормозили бы каждую загрузку главной.
$pingMs = $isActiveSub ? $vpnStatusObj->getPingMs() : null;
$pingStatus = $isActiveSub ? $vpnStatusObj->getPingStatus() : 'inactive';

// Кольцо подписки: остаток от ВЫДАННОГО периода (выдано 90 → кольцо = остаток от 90).
$daysLeftNum = max(0, (int) floor(((int) $user->getExpiry() / 1000 - time()) / 86400));
$periodDays = max(0, (int) $user->getCountDays());
$devicesNum = (int) $vpnStatusObj->getCountDevices();
$devicesLabel = $devicesNum > 0 ? (string) $devicesNum : '∞';
$ringBase = $periodDays > 0 ? $periodDays : 30;
$ringPct = $isActiveSub ? max(4, min(100, (int) round($daysLeftNum / $ringBase * 100))) : 0;
$ringOff = $isActiveSub ? '' : ' bank-ring--off';
// Прогресс периода: дата окончания + доля прошедшего (для полосы под кольцом)
$expiryTs = (int) ($user->getExpiry() / 1000);
$expiryDate = $expiryTs > 0 ? date('d.m.Y', $expiryTs) : '—';
$elapsedDays = max(0, $periodDays - $daysLeftNum);
$elapsedPct = $periodDays > 0 ? min(100, (int) round($elapsedDays / $periodDays * 100)) : 0;
// Маскированный ID счёта в духе банковской карты: •••• 1234
$maskedUni = '•••• ' . substr((string) $user->getUniID(), -4);
// Последняя оплата для подвала обзора (быстрый файловый индекс, без БД)
$lastPay = PaymentIndex::forUser($user->getUniID(), 1)[0] ?? null;
$lastPayLabel = null;
$lastPayShort = null;
if (\is_array($lastPay)) {
    $amt = rtrim(rtrim(number_format((float) ($lastPay['amount'] ?? 0), 2, '.', ' '), '0'), '.');
    $ts = strtotime((string) ($lastPay['date'] ?? ''));
    $lastPayLabel = $amt . ' ₽ · ' . ($ts > 0 ? date('d.m.Y', $ts) : (string) ($lastPay['date'] ?? ''));
    $lastPayShort = $amt . ' ₽' . ($ts > 0 ? ' · ' . date('d.m', $ts) : '');
}

$formattedVpnStatus = [
    'status_text' => $t($vpnStatus === 'active' ? 'active' : 'inactive'),
    'status_class' => $vpnStatus === 'active' ? 'text-green-400' : 'text-red-400',
    'ping_label' => $pingMs !== null ? $pingMs . ' ms' : '—',
    'ping_class' => $pingStatus === 'good' ? 'text-green-400' : ($pingStatus === 'inactive' ? 'text-red-400' : 'text-gray-400'),
    'ping_icon' => $pingStatus === 'good' ? 'fa-arrow-up' : ($pingStatus === 'inactive' ? 'fa-arrow-down' : 'fa-minus'),
    'protocol' => $vpnStatusObj->getProtocol(),
    'ip_address' => $isActiveSub ? $vpnStatusObj->getIpAddress() : '—',
    'location' => $isActiveSub ? $vpnStatusObj->getLocation() : '—',
];

// Без активной подписки не показываем реальные параметры узла (пинг/IP/хост из .env)
if ($vpnStatus !== 'active') {
    $formattedVpnStatus['ping_label'] = '0';
    $formattedVpnStatus['ping_class'] = 'text-gray-400';
    $formattedVpnStatus['ping_icon'] = 'fa-minus';
    $formattedVpnStatus['protocol'] = '—';
    $formattedVpnStatus['ip_address'] = '—';
    $formattedVpnStatus['location'] = '—';
}

$formattedUserProfile = [
    'full_name' => trim($user->getFirstName() . ' ' . $user->getLastName()) ?: $t('user'),
    'status_text' => $t($user->getStatus() === 'on' ? 'active' : 'inactive'),
    'status_class' => $user->getStatus() === 'on' ? 'text-green-400' : 'text-red-400',
    'days_left' => $user->getCountDays(),
    'refer_count' => $user->getReferCount(),
    'has_discount' => $user->getDiscountPercent() > 0 ? $t('yes') : $t('no'),
    'discount_percent' => $user->getDiscountPercent(),
    'subscription_status' => $t($user->getStatus() === 'on' ? 'active' : 'inactive'),
    'theme' => $_COOKIE['theme'] ?? $_SESSION['theme'] ?? 'Темная', // Получаем тему из куки или сессии
    'language' => Language::LANGUAGES[$currentLanguage] ?? 'Русский'
];

$systemInfoObj = new SystemInfo();
$dbStatus = $systemInfoObj->getDbStatus();//один запрос вместо трёх
$formattedSystemInfo = [
    'version' => $systemInfoObj->getVersion(),
    'db_status' => $dbStatus,
    'db_status_text' => $t($dbStatus === 'connected' ? 'yes' : 'no'),
    'db_status_class' => $dbStatus === 'connected' ? 'text-green-400' : 'text-red-400'
];

$activeSection = $_GET['section'] ?? 'main';
if (!in_array($activeSection, ['main', 'profile', 'setting', 'referal', 'support'], true)) {
    $activeSection = 'main';
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLanguage ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $t('profile') ?></title>
    <!-- ========== manifest Apps ================ -->
    <link rel="icon" type="image/png" href="/public/assets/images/icons/logo/manifest/favicon-96x96.png"
        sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/public/assets/images/icons/logo/manifest/favicon.svg" />
    <link rel="shortcut icon" href="/public/assets/images/icons/logo/manifest/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180"
        href="/public/assets/images/icons/logo/manifest/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="QTV" />
    <link rel="manifest" href="/public/assets/images/icons/logo/manifest/site.webmanifest" />
    <!-- ========================================== -->
    <!-- Preload critical resources -->
    <link rel="preload" href="/public/assets/styles/style.css<?= '?v=' . $site['versionApp'] ?>" as="style">
    <link rel="preload" href="/public/assets/images/icons/logo/qweesvpn.svg" as="image" type="image/svg+xml">

    <!-- Critical CSS with onload optimization -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" as="style"
        crossorigin="anonymous" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
            crossorigin="anonymous">
    </noscript>

    <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet">
    </noscript>

    <link rel="stylesheet" href="/public/assets/styles/style.css<?= '?v=' . $site['versionApp'] ?>" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/style.css<?= '?v=' . $site['versionApp'] ?>">
    </noscript>

    <!-- Async/Deferred scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Noscript fallback -->
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/noscript.css<?= '?v=' . $site['versionApp'] ?>">
    </noscript>
</head>

<body class="bg-black bg-no-repeat flex item-center w-full overflow-x-hidden" data-active-section="<?= $activeSection ?>">
    <div class="min-h-screen flex flex-col w-full">

        <?php include_once 'public/components/header.php' ?>

        <main class="flex sm:my-2 w-full h-full">

            <!-- Сюда рендерится активный layout (desktop или mobile) -->
            <div id="layout-root" style="display: contents"></div>

            <!-- ################# LAYOUT DESKTOP (шаблон) ####################-->
            <template id="layout-desktop">
            <aside class="h-full min-w-[300px] z-20">
                <div class="relative sm:text-sm sm:leading-6 my-8">
                    <ul class="fixed flex flex-col gap-6">

                        <li class="flex h-16 gap-4 items-center justify-center">
                            <img decoding="async" loading="lazy" data-theme-invert class="w-auto h-12 object-contain"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                alt="<?= htmlspecialchars($site['ООО']) ?>">
                            <h2 class="text-white text-3xl font-[qwees-urbanist-medium] tracking-wider">
                                Qwees<span class="text-green-400">VPN</span>
                            </h2>
                        </li>

                        <!-- Основные ссылки -->
                        <ul class="desktop list-none fle fle-col mr-4 w-full">
                            <!-- home -->
                            <li class="bg_active relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                                data-toggle-section="main">
                                <span></span>
                                <span class="pl-10 text-xl text-white flex items-center gap-4">
                                    <img class="max-h-6" decoding="async" loading="lazy" data-theme-invert
                                        loading="lazy"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/menu/home.svg"
                                        alt="home" decoding="async">
                                    <?= $t('main') ?>
                                </span>
                            </li>
                            <!-- profile -->
                            <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                                data-toggle-section="profile">
                                <span></span>
                                <span class="pl-10 text-xl text-white flex items-center gap-4">
                                    <img class="max-h-6" decoding="async" loading="lazy" data-theme-invert
                                        loading="lazy"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/menu/profile.svg"
                                        alt="home" decoding="async">
                                    <?= $t('profile') ?>
                                </span>
                            </li>
                            <!-- setting -->
                            <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                                data-toggle-section="setting">
                                <span></span>
                                <span class="pl-10 text-xl text-white flex items-center gap-4">
                                    <img class="max-h-6" decoding="async" loading="lazy" data-theme-invert
                                        loading="lazy"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/menu/setting.svg"
                                        alt="home" decoding="async">
                                    <?= $t('settings') ?>
                                </span>
                            </li>
                            <!-- referal -->
                            <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                                data-toggle-section="referal">
                                <span></span>
                                <span class="pl-10 text-xl text-white flex items-center gap-4">
                                    <img class="max-h-6" decoding="async" loading="lazy" data-theme-invert
                                        loading="lazy"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/menu/refer.svg"
                                        alt="home" decoding="async">
                                    <?= $t('additional') ?>
                                </span>
                            </li>
                            <!-- support -->
                            <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                                data-toggle-section="support">
                                <span></span>
                                <span class="pl-10 text-xl text-white flex items-center gap-4">
                                  <img class="max-h-6" decoding="async" loading="lazy" data-theme-invert
                                      loading="lazy"
                                      src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/menu/support.svg"
                                      alt="home" decoding="async">
                                      <?= $t('support') ?>
                                </span>
                            </li>
                        </ul>
                    </ul>

                </div>
                <span class="absolute bottom-5 left-5 text-white text-sm">
                    QTV <?= $site['versionApp'] ?>
                </span>
            </aside>

            <!-- ################# CONTENT DESCKTOP ####################-->
            <div class="rounded-3xl w-full h-full text-white m-4 overflow-clip outer">

                <div class="card js-sections">
                    <div
                        class="absolute inset-0 z-0 bg-gradient-to-br from-green-900/15 via-transparent to-emerald-900/8">
                    </div>

                    <!-- SECTION = MAIN -->
                    <template data-section="main">
                    <section
                        class="flex flex-col gap-6 box-border h-full w-full p-10 ml-2 relative z-10 rounded-3xl setka"
                        data-section="main">

                        <!-- оглавление DESCKTOP -->
                        <div class="flex items-center gap-3 mb-2" data-reveal>
                            <span class="text-sm font-bold tracking-[.3em] text-white">Qwees<span class="text-green-400">VPN</span></span>
                            <span class="h-px flex-1 bg-gradient-to-r from-white/15 to-transparent"></span>
                        </div>
                        <h1 class="bank-h1 text-3xl font-bold">
                            <?php foreach (mb_str_split($t('main')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>

                        <!-- контент -->
                        <div class="flex items-start justify-center gap-6 w-full">
                            <!-- BLOCK-1 => DISPLAY STATUS -->
                            <div
                                data-reveal class="glow-card bank-glass relative min-h-[600px] flex flex-1 flex-col rounded-2xl overflow-hidden p-8" data-sub-url="<?= htmlspecialchars($user->getSubscription()) ?>">
                                <!-- шапка счёта: статус + локация -->
                                <div class="flex items-center justify-between w-full">
                                    <span class="bank-status <?= $vpnStatus === 'active' ? 'text-green-300 border-green-400/25 bg-green-400/10' : 'text-gray-400' ?>">
                                        <span class="w-2 h-2 rounded-full <?= $vpnStatus === 'active' ? 'bg-green-400' : 'bg-gray-500' ?>"></span>
                                        <?= $vpnStatusObj->getStatusText() ?>
                                    </span>
                                    <span class="text-right shrink-0">
                                        <span class="block text-sm text-gray-400" data-server><?= $formattedVpnStatus['location'] ?></span>
                                        <span class="block font-mono text-[11px] text-gray-600 mt-0.5"><?= htmlspecialchars($maskedUni) ?></span>
                                    </span>
                                </div>

                                <!-- сервер сменился: напомнить обновить подписку в приложении -->
                                <div data-subwatch hidden class="mt-5 flex items-center gap-3 rounded-xl border border-yellow-500/30 bg-yellow-500/10 px-4 py-3">
                                    <i class="fa-solid fa-arrows-rotate text-yellow-300 text-sm shrink-0"></i>
                                    <div class="flex-1 text-[13px] text-gray-200">Сервер обновлён — обновите подписку в приложении</div>
                                    <button type="button" data-subwatch-copy class="shrink-0 text-xs px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10 transition-colors">Копировать</button>
                                    <button type="button" data-subwatch-ok class="shrink-0 text-xs px-2.5 py-1.5 text-gray-400 hover:text-gray-200 transition-colors">OK</button>
                                </div>

                                <!-- кольцо подписки -->
                                <div class="mt-10 flex items-center gap-8">
                                    <div class="relative shrink-0" style="width:190px;height:190px">
                                        <div class="absolute inset-0 rounded-full bg-green-500/15 blur-2xl"></div>
                                        <div class="bank-ticks absolute inset-0 rounded-full"></div>
                                        <div class="bank-ring<?= $ringOff ?> absolute inset-0 rounded-full" style="--pt:<?= $ringPct ?>"></div>
                                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                                            <div class="bank-num text-5xl font-bold tabular-nums tracking-tight"><?= $daysLeftNum ?></div>
                                            <div class="bank-label mt-1"><?= $t('days_short') ?> из <?= $periodDays ?></div>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="py-3 border-b border-white/[0.07]">
                                            <div class="bank-label mb-0.5"><?= rtrim($t('duration_colon'), ':') ?></div>
                                            <div class="text-xl font-semibold text-white tabular-nums"><?= $periodDays ?> <span class="text-sm font-normal text-gray-400"><?= $t('days_short') ?></span></div>
                                        </div>
                                        <div class="py-3">
                                            <div class="bank-label mb-0.5"><?= rtrim($t('devices_colon'), ':') ?></div>
                                            <div class="text-xl font-semibold text-white tabular-nums"><?= $devicesLabel ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="text-gray-400"><?= rtrim($t('valid_until'), ':') ?> <?= $expiryDate ?></span>
                                        <span class="text-gray-300 tabular-nums" data-timeleft>—</span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
                                        <div class="h-full rounded-full <?= $isActiveSub ? 'bg-gradient-to-r from-green-500 to-emerald-400' : 'bg-gray-600' ?>" style="width:<?= $elapsedPct ?>%"></div>
                                    </div>
                                </div>

                                <!-- параметры плиткой -->
                                <div class="grid grid-cols-3 gap-3 mt-8">
                                    <div class="bank-tile px-4 py-3">
                                        <div class="bank-label mb-1"><?= $t('ping') ?>, мс</div>
                                        <div class="text-lg font-semibold text-white tabular-nums flex items-center gap-2">
                                            <i class="fas <?= $formattedVpnStatus['ping_icon'] ?> <?= $formattedVpnStatus['ping_class'] ?> text-sm"></i>
                                            <span class="<?= $formattedVpnStatus['ping_class'] ?>" data-ping><?= $formattedVpnStatus['ping_label'] ?></span>
                                        </div>
                                    </div>
                                    <div class="bank-tile px-4 py-3">
                                        <div class="bank-label mb-1"><?= $t('protocol') ?></div>
                                        <div class="text-lg font-semibold text-white" data-protocol><?= $formattedVpnStatus['protocol'] ?></div>
                                    </div>
                                    <div class="bank-tile px-4 py-3 min-w-0">
                                        <div class="bank-label mb-1"><?= $t('ip_address') ?></div>
                                        <div class="text-base font-semibold text-white tabular-nums truncate" data-ip><?= $formattedVpnStatus['ip_address'] ?></div>
                                    </div>
                                </div>

                                <div class="flex-1"></div>
                                <div class="mt-8 pt-4 border-t border-white/[0.07] flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="bank-label mb-0.5">Последняя оплата</div>
                                        <div class="text-sm font-semibold text-gray-200 tabular-nums truncate"><?= $lastPayLabel !== null ? htmlspecialchars($lastPayLabel) : $t('pay_empty') ?></div>
                                    </div>
                                    <button type="button" data-toggle-section="profile" class="shrink-0 text-xs font-medium px-3.5 py-2 rounded-lg border border-white/15 text-gray-300 hover:bg-white/5 transition-colors">Все чеки →</button>
                                </div>
                            </div>

                            <!-- BLOCK-2 => INFORMATION PANELS -->
                            <div data-reveal data-reveal-delay="100" class="glow-card flex-1 h-full max-w-[350px] p-6 rounded-2xl">
                                <ul class="flex flex-col gap-4 w-full text-xl">
                                    <!-- content 1 -->
                                    <li
                                        class="gradient-border flex p-3 justify-between items-center w-full">
                                        <div class="text-gray-300 text-sm flex items-center gap-2">
                                            <?= $t('ping') ?>, мс
                                        </div>
                                        <div class="text-[white] flex items-center gap-2">
                                            <i
                                                class="fas <?= $formattedVpnStatus['ping_icon'] ?> <?= $formattedVpnStatus['ping_class'] ?> text-sm"></i>
                                            <span class="<?= $formattedVpnStatus['ping_class'] ?>"
                                                data-ping><?= $formattedVpnStatus['ping_label'] ?></span>
                                        </div>
                                    </li>
                                    <!-- content 2 -->
                                    <li
                                        class="gradient-border flex p-3 justify-between items-center w-full">
                                        <span class="text-gray-300 text-sm"><?= $t('protocol') ?>:</span>
                                        <span class="text-[white] text-base font-light"
                                            data-protocol><?= $formattedVpnStatus['protocol'] ?></span>
                                    </li>
                                    <!-- content 3 -->
                                    <li
                                        class="gradient-border flex p-3 justify-between items-center w-full">
                                        <span class="text-gray-300 text-sm"><?= $t('ip_address') ?>:</span>
                                        <span class="text-[white] text-base font-light"
                                            data-ip><?= $formattedVpnStatus['ip_address'] ?></span>
                                    </li>
                                    <!-- content 4 -->
                                    <li
                                        class="gradient-border flex p-3 justify-between items-center w-full">
                                        <span class="text-gray-300 text-sm"><?= $t('server') ?>:</span>
                                        <span class="text-emerald-300 text-sm font-light"
                                            data-server><?= $formattedVpnStatus['location'] ?></span>
                                    </li>
                                    <!-- content 5 -->
                                    <li
                                        class="gradient-border flex p-3 justify-between items-center w-full">
                                        <span class="text-gray-300 text-sm"><?= $t('remaining') ?>:</span>
                                        <span class="text-emerald-300 text-sm font-light" data-server
                                            data-timeleft></span>
                                    </li>
                                </ul>

                                <!-- Action Buttons -->
                                <ul class="flex flex-col gap-3 mt-6">
                                    <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                                        <a href="/install" class="btn_install_tour block w-full">
                                            <li
                                                class="bank-btn-primary bank-btn-split group relative w-full">
                                                <?php $osName = (new OS())->getOS()['os']; ?>
                                                <?php if ($osName === 'Windows' || $osName === 'macOS' || $osName === 'Linux'): ?>
                                                    <img decoding="async" loading="lazy"
                                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/install_desktop.svg"
                                                        alt=""
                                                        class="h-6 opacity-70 invert">
                                                <?php else: ?>
                                                    <img decoding="async"
                                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/install_mobile.svg"
                                                        alt="" loading="lazy"
                                                        class="h-6 opacity-70 invert">
                                                <?php endif; ?>
                                                <span class="text-sm font-semibold text-center flex-1"><?= $t('install_btn') ?> VPN</span>
                                                <img decoding="async" loading="lazy"
                                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow_white.svg"
                                                    alt="" class="h-5 opacity-60 invert">
                                            </li>
                                        </a>
                                    <?php else: ?>
                                        <a href="/pay" class="block w-full">
                                            <li
                                                class="bank-btn-primary bank-btn-split group relative w-full">
                                                <img decoding="async" loading="lazy"
                                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/buy.svg"
                                                    alt="buy" loading="lazy"
                                                    class="h-6 opacity-70 invert">
                                                <span class="text-sm font-semibold text-center flex-1"><?= $t('buy') ?> <?= $t('subscription') ?></span>
                                                <img decoding="async" loading="lazy"
                                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow_white.svg"
                                                    alt="" class="h-5 opacity-60 invert">
                                            </li>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Пробрная подписка -->
                                    <?php if ($giftShow): ?>
                                    
                                    <li class="w-full"> 
                                      <form action="/api/gifts/give" method="post">
                                        <button type="submit" class="bank-btn-light bank-btn-split w-full">
                                                <img decoding="async" loading="lazy" src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/free.svg"
                                                  alt="buy" loading="lazy" decoding="async"
                                                  class="h-6 opacity-70">
                                                    
                                                <span class="flex-1 text-center"><?= $t('trial') ?></span>
                                                <img decoding="async" loading="lazy" src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow.svg"
                                                  alt="" loading="lazy" decoding="async"
                                                  class="h-5 opacity-60">
                                        </button>
                                       </form>
                                    </li>
                                   
                                    <?php endif; ?>
                                    
                                </ul>
    
                            </div>

                        </div>

                    </section>
                    </template>

                    <!-- SECTION = PROFILE -->
                    <template data-section="profile">
                    <section
                        class="flex-col gap-8 box-border h-full w-full p-10 ml-2 relative z-10 rounded-3xl setka"
                        data-section="profile">

                        <!-- Header Card -->
                        <div class="flex flex-col gap-6">
                            <div class="flex items-center justify-between">
                                <h1 class="bank-h1 text-3xl font-bold">
                                    <?php foreach (mb_str_split($t('profile')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>
                                <form action="/auth/logout" method="post">
                                    <button type="submit"
                                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 transition-all duration-300 group">
                                        <i
                                            class="fa-solid fa-right-from-bracket text-red-400 text-sm group-hover:scale-110 transition-transform"></i>
                                        <span class="text-red-400 text-sm font-medium"><?= $t('exit'); ?></span>
                                    </button>
                                </form>
                            </div>

                            <!-- Profile Hero Card -->
                            <div data-reveal class="glow-card bank-glass relative flex items-center gap-6 p-6 rounded-2xl">
                                <div class="relative">
                                    <img decoding="async" loading="lazy"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/avatar/1.png"
                                        alt="avatar" class="rounded-full w-20 h-20 ring-2 ring-white/10">
                                    <div
                                        class="absolute bottom-0 right-0 w-5 h-5 rounded-full <?= $user->getStatus() === 'on' ? 'bg-green-400' : 'bg-red-400' ?> ring-2 ring-black">
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <h2 class="text-[white] text-2xl font-semibold">
                                        <?= htmlspecialchars($formattedUserProfile['full_name']) ?>
                                    </h2>
                                    <p class="text-sm text-gray-400"><?= $formattedUserProfile['status_text'] ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($user->getEmail()) ?></p>
                                </div>
                            </div>

                            <!-- Stats Grid -->
                            <div data-reveal data-reveal-delay="70" class="grid grid-cols-4 gap-4">
                                <div
                                    class="glow-card flex flex-col gap-3 p-4 rounded-xl">
                                    <div class="flex items-center gap-2 text-green-400">
                                        <i class="fa fa-wifi text-lg"></i>
                                        <span class="text-sm font-medium">VPN</span>
                                    </div>
                                    <span
                                        class="text-[white] text-lg font-semibold"><?= $formattedUserProfile['subscription_status'] ?></span>
                                </div>
                                <div
                                    class="glow-card flex flex-col gap-3 p-4 rounded-xl">
                                    <div class="flex items-center gap-2 text-blue-400">
                                        <i class="fa fa-language text-lg"></i>
                                        <span class="text-sm font-medium"><?= $t('language') ?></span>
                                    </div>
                                    <span
                                        class="text-[white] text-lg font-semibold"><?= $formattedUserProfile['language'] ?></span>
                                </div>
                                <div
                                    class="glow-card flex flex-col gap-3 p-4 rounded-xl">
                                    <div class="flex items-center gap-2 text-purple-400">
                                        <i class="fa fa-server text-lg"></i>
                                        <span class="text-sm font-medium"><?= $t('remaining') ?></span>
                                    </div>
                                    <span class="text-[white] text-lg font-semibold" data-timeleft></span>
                                </div>
                                <div
                                    class="glow-card flex flex-col gap-3 p-4 rounded-xl">
                                    <div class="flex items-center gap-2 text-yellow-400">
                                        <i class="fa fa-palette text-lg"></i>
                                        <span class="text-sm font-medium"><?= $t('theme') ?></span>
                                    </div>
                                    <span class="text-[white] text-lg font-semibold" id="profile-theme"
                                        data-dark="<?= $t('dark') ?>"
                                        data-light="<?= $t('light') ?>"><?= $formattedUserProfile['theme'] ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- VPN Key Section -->
                        <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                            <div data-reveal data-reveal-delay="140" class="flex flex-col gap-4">
                                <h3 class="text-xl font-semibold text-gray-300 mt-4"><?= $t('subscription_data') ?></h3>
                                <div class="glow-card relative z-20 flex items-center gap-4 p-5 rounded-xl">
                                    <div class="flex-1 flex flex-col gap-2">
                                        <label class="text-sm text-gray-400 font-medium"><?= $t('vpn_key') ?></label>
                                        <code id="vpn-key-desktop"
                                            class="text-sm text-[white]/70 bg-black/20 px-3 py-2 rounded-lg break-all">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <?= htmlspecialchars($user->getSubscription()) ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </code>
                                    </div>
                                    <div class="flex gap-2 relative z-30">
                                        <button
                                            onclick="window.open('<?= htmlspecialchars($user->getSubscription()) ?>','_blank')"
                                            title="<?= $t('copy') ?>"
                                            class="p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors group cursor-pointer">
                                            <i class="fa fa-share text-gray-400 group-hover:text-white"></i>
                                        </button>
                                        <button onclick="copyVpnKey()" title="<?= $t('copy') ?>"
                                            class="p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors group cursor-pointer">
                                            <i class="fa fa-copy text-gray-400 group-hover:text-white"></i>
                                        </button>
                                        <button onclick="deleteSubscription()" title="<?= $t('delete') ?>"
                                            class="p-3 rounded-lg bg-red-500/10 hover:bg-red-500/20 transition-colors group cursor-pointer">
                                            <i class="fa fa-trash text-red-400 group-hover:text-red-300"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Server Select (radio scroll list) -->
                        <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                            <div data-reveal data-reveal-delay="210" class="flex flex-col gap-4 mt-4">
                                <h3 class="text-xl font-semibold text-gray-300"><?= $t('server_select') ?></h3>
                                <div class="glow-card p-3 rounded-xl">
                                    <div class="flex flex-col gap-1.5 max-h-44 overflow-y-auto pr-1">
                                        <?php foreach ($availableServers as $srv): ?>
                                            <label
                                                class="flex items-center gap-3 p-3 rounded-lg cursor-pointer hover:bg-white/[0.06] transition-colors <?= $srv['code'] === $currentServerCode ? 'bg-white/[0.08] ring-1 ring-green-400/30' : '' ?>">
                                                <input type="radio" name="vpn-server" value="<?= $srv['code'] ?>"
                                                    <?= $srv['code'] === $currentServerCode ? 'checked' : '' ?>
                                                    onchange="changeServer('<?= $srv['code'] ?>')"
                                                    class="accent-green-400 w-4 h-4 cursor-pointer">
                                                <img decoding="async" loading="lazy"
                                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/flags/<?= $srv['flag'] ?>"
                                                    alt="<?= $srv['code'] ?>" class="w-7 h-5 rounded object-cover">
                                                <span class="text-[white] text-sm font-medium flex-1"><?= htmlspecialchars($srv['country']) ?></span>
                                                <?php if ($srv['code'] === $currentServerCode): ?>
                                                    <span class="text-xs text-green-400"><?= $t('server_current') ?></span>
                                                <?php endif; ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Payment History (id+дата+сумма из JSON, квитанция живьём из кассы) -->
                        <div data-reveal data-reveal-delay="280" class="flex flex-col gap-4 mt-6">
                            <h3 class="text-xl font-semibold text-gray-300"><?= $t('pay_history') ?></h3>
                            <div class="glow-card p-4 rounded-xl">
                                <div data-pay-history
                                    data-empty="<?= htmlspecialchars($t('pay_empty')) ?>"
                                    data-error="<?= htmlspecialchars($t('pay_error')) ?>"
                                    data-receipt="<?= htmlspecialchars($t('pay_receipt')) ?>"
                                    class="flex flex-col gap-2">
                                    <span class="text-sm text-gray-500"><?= $t('pay_loading') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Company Links & Logout -->
                        <div class="flex flex-col gap-4 mt-6">
                            <h3 class="text-xl font-semibold text-gray-300"><?= $t('company') ?></h3>
                            <div data-reveal data-reveal-delay="350" class="grid grid-cols-2 gap-4">
                                <a href="/about"
                                    class="glow-card flex items-center gap-4 p-4 rounded-xl hover:bg-white/[0.06] transition-colors group">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center ring-1 ring-white/10">
                                        <i class="fa-solid fa-building text-gray-300 text-xl"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[white] font-medium"><?= $t('about_title') ?></span>
                                        <span class="text-sm text-gray-400"><?= $t('our_story') ?></span>
                                    </div>
                                </a>
                                <a href="/requisites"
                                    class="glow-card flex items-center gap-4 p-4 rounded-xl hover:bg-white/[0.06] transition-colors group">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center ring-1 ring-white/10">
                                        <i class="fa-solid fa-file-invoice text-gray-300 text-xl"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[white] font-medium"><?= $t('requisites') ?></span>
                                        <span class="text-sm text-gray-400"><?= $t('legal_info') ?></span>
                                    </div>
                                </a>
                            </div>
                        </div>

                    </section>
                    </template>

<!-- SECTION = SETTING -->
                    <template data-section="setting">
                    <section
                        class="flex-col gap-8 box-border h-full w-full p-10 ml-2 relative z-10 rounded-3xl setka"
                        data-section="setting">

                        <!-- Header -->
                        <h1 class="text-3xl font-bold">
                            <?php foreach (mb_str_split($t('settings')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>

                        <!-- App Settings -->
                        <div class="flex flex-col gap-4 pt-6">
                            <h3 class="text-lg font-semibold text-gray-300"><?= $t('apps'); ?></h3>
                            <div class="flex flex-col gap-3">
                                <!-- Theme Toggle -->
                                <div
                                    class="glow-card flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                                            <i class="fa fa-sun text-yellow-400 text-lg"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[white] font-medium"><?= $t('change_theme'); ?></span>
                                            <span class="text-sm text-gray-400"><?= $t('change_decoration'); ?></span>
                                        </div>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" value="" class="sr-only peer" data-darkModeToggle>
                                        <div
                                            class="relative w-11 h-6 bg-white/10 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-400">
                                        </div>
                                    </label>
                                </div>

                                <!-- Language Toggle -->
                                <div
                                    class="glow-card flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                            <i class="fa fa-language text-blue-400 text-lg"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[white] font-medium"><?= $t('language') ?></span>
                                            <span class="text-sm text-gray-400"><?= $t('language_switch') ?></span>
                                        </div>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" value="rus" class="sr-only peer" data-language>
                                        <div
                                            class="relative w-11 h-6 bg-white/10 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-400">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Privacy Settings -->
                        <div class="flex flex-col gap-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-300"><?= $t('Confidentiality'); ?></h3>
                            <div class="flex flex-col gap-2">
                                <!-- <a href="/"
                                                                                class="glow-card flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors group">
                                                                                <div class="flex items-center gap-4">
                                                                                    <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                                                                        <i class="fa fa-credit-card text-purple-400 text-lg"></i>
                                                                                    </div>
                                                                                    <span class="text-[white] font-medium"><?= $t('auto_payment') ?></span>
                                                                                </div>
                                                                                <i
                                                                                    class="fa fa-angle-right text-gray-400 group-hover:text-white group-hover:translate-x-1 transition-all"></i>
                                                                                </a> -->

                                <button data-toggle-modal="politic"
                                    class="glow-card flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors group text-left">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                            <i class="fa fa-shield-alt text-emerald-400 text-lg"></i>
                                        </div>
                                        <span class="text-[white] font-medium"><?= $t('politic'); ?></span>
                                    </div>
                                    <i
                                        class="fa fa-angle-right text-gray-400 group-hover:text-white group-hover:translate-x-1 transition-all"></i>
                                </button>

                                <button data-toggle-modal="access"
                                    class="glow-card flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors group text-left">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                            <i class="fa fa-file-contract text-emerald-400 text-lg"></i>
                                        </div>
                                        <span class="text-[white] font-medium"><?= $t('soglashenia'); ?></span>
                                    </div>
                                    <i
                                        class="fa fa-angle-right text-gray-400 group-hover:text-white group-hover:translate-x-1 transition-all"></i>
                                </button>
                            </div>
                        </div>

                    </section>
                    </template>

                    <!-- SECTION = REFER -->
                    <template data-section="referal">
                    <section
                        class="flex-col gap-8 box-border h-full w-full p-10 ml-2 relative z-10 rounded-3xl setka"
                        data-section="referal">

                        <!-- Header -->
                        <h1 class="text-3xl font-bold">
                            <?php foreach (mb_str_split($t('referals')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>

                        <!-- Stats Overview -->
                        <div class="grid grid-cols-3 gap-4 pt-6">
                            <div
                                class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2 text-emerald-400">
                                    <i class="fa fa-signal text-lg"></i>
                                    <span class="text-sm font-medium"><?= $t('status'); ?></span>
                                </div>
                                <span
                                    class="text-[white] text-xl font-semibold"><?= $formattedUserProfile['subscription_status'] ?></span>
                            </div>
                            <div
                                class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2 text-blue-400">
                                    <i class="fa fa-users text-lg"></i>
                                    <span class="text-sm font-medium"><?= $t('referals'); ?></span>
                                </div>
                                <span class="text-[white] text-xl font-semibold"><?= $user->getReferCount() ?></span>
                            </div>
                            <div
                                class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2">
                                    <i class="fa fa-percent text-green-400"></i>
                                    <span class="text-sm font-medium"><?= $t('discount'); ?></span>
                                </div>
                                <span
                                    class="text-[white] text-xl font-semibold"><?= $user->getDiscountPercent() ?>%</span>
                            </div>
                        </div>

                        <!-- Referral Link Cards -->
                        <div class="flex flex-col gap-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-300"><?= $t('your_referal_links'); ?></h3>

                            <!-- Refer Code -->
                            <div
                                class="flex items-center gap-4 p-5 rounded-xl bg-white/[0.03] shadow-[0_4px_16px_rgba(0,0,0,0.2)] ring-1 ring-white/[0.08]">
                                <div
                                    class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                                    <i class="fa fa-ticket text-emerald-400 text-xl"></i>
                                </div>
                                <div class="flex-1 flex flex-col gap-1 min-w-0">
                                    <label class="text-sm text-gray-400"><?= $t('your_code'); ?></label>
                                    <code
                                        class="text-[white] text-lg font-semibold truncate"><?= htmlspecialchars($user->getMyRefer()) ?></code>
                                </div>
                                <button
                                    onclick="copyToClipboard('<?= htmlspecialchars($user->getMyRefer()) ?>', <?= json_encode($t('referal_code')) ?>)"
                                    title="<?= $t('copy_code') ?>"
                                    class="p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors group shrink-0 cursor-pointer">
                                    <i class="fa fa-copy text-gray-400 group-hover:text-white"></i>
                                </button>
                            </div>

                            <!-- Full URL -->
                            <div
                                class="flex items-center gap-4 p-5 rounded-xl bg-white/[0.03] shadow-[0_4px_16px_rgba(0,0,0,0.2)] ring-1 ring-white/[0.08]">
                                <div
                                    class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center shrink-0">
                                    <i class="fa fa-link text-blue-400 text-xl"></i>
                                </div>
                                <div class="flex-1 flex flex-col gap-1 min-w-0">
                                    <label class="text-sm text-gray-400"><?= $t('full_link'); ?></label>
                                    <code
                                        class="text-[white] text-xs truncate"><?= htmlspecialchars($user->getMyRefer() ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/reflink=' . $user->getMyRefer() : '') ?></code>
                                </div>
                                <button
                                    onclick="copyToClipboard('<?= htmlspecialchars($user->getMyRefer() ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/reflink=' . $user->getMyRefer() : '') ?>', <?= json_encode($t('referal_link')) ?>)"
                                    title="<?= $t('copy_link') ?>"
                                    class="p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors group shrink-0 cursor-pointer">
                                    <i class="fa fa-copy text-gray-400 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Detailed Stats -->
                        <div class="flex flex-col gap-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-300"><?= $t('statistic'); ?></h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    class="flex flex-col items-center p-6 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08]">
                                    <span class="text-sm text-gray-400 mb-2"><?= $t('invents'); ?></span>
                                    <span
                                        class="text-3xl font-bold text-green-400"><?= intval($user->getReferCount()) ?></span>
                                    <span class="text-xs text-gray-500 mt-1"><?= $t('humans'); ?></span>
                                </div>
                                <div
                                    class="flex flex-col items-center p-6 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08]">
                                    <span class="text-sm text-gray-400 mb-2"><?= $t('your_discount'); ?></span>
                                    <span
                                        class="text-3xl font-bold text-green-400">-<?= intval($user->getDiscountPercent()) ?>%</span>
                                    <span class="text-xs text-gray-500 mt-1"><?= $t('discount_uses_left'); ?>: <?= intval($user->getDiscountUses()) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Who gets what -->
                        <div class="flex flex-col gap-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-300"><?= $t('refer_what_get'); ?></h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08]">
                                    <div class="flex items-center gap-2 text-emerald-400">
                                        <i class="fa fa-ticket text-lg"></i>
                                        <span class="text-sm font-medium"><?= $t('refer_for_invited'); ?></span>
                                    </div>
                                    <ul class="flex flex-col gap-1.5 text-[white] text-sm">
                                        <li class="flex items-center gap-2"><i class="fa fa-check text-green-400 text-xs"></i><?= htmlspecialchars((string) $referWhat['invited_days']) ?></li>
                                        <li class="flex items-center gap-2"><i class="fa fa-check text-green-400 text-xs"></i><?= htmlspecialchars((string) $referWhat['invited_discount']) ?></li>
                                    </ul>
                                </div>
                                <div class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08]">
                                    <div class="flex items-center gap-2 text-blue-400">
                                        <i class="fa fa-users text-lg"></i>
                                        <span class="text-sm font-medium"><?= $t('refer_for_inviter'); ?></span>
                                    </div>
                                    <ul class="flex flex-col gap-1.5 text-[white] text-sm">
                                        <li class="flex items-center gap-2"><i class="fa fa-check text-green-400 text-xs"></i><?= htmlspecialchars((string) $referWhat['inviter_each']) ?></li>
                                        <li class="flex items-center gap-2"><i class="fa fa-check text-green-400 text-xs"></i><?= htmlspecialchars((string) $referWhat['inviter_percent']) ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Invited by you -->
                        <div class="flex flex-col gap-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-300"><?= $t('invited_by_you'); ?> (<?= count($referralsList) ?>)</h3>
                            <div class="flex flex-col gap-2 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08]">
                                <?php if (!empty($referralsList)): ?>
                                    <?php foreach ($referralsList as $ref): ?>
                                        <div class="flex justify-between items-center py-2 border-b border-white/5">
                                            <span class="font-medium"><?= htmlspecialchars($ref['name'] !== '' ? $ref['name'] : $ref['email']) ?></span>
                                            <span class="text-sm text-gray-400"><?= htmlspecialchars($ref['date']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-sm text-gray-400"><?= $t('invited_empty'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Referrer Info or Enter Code -->
                        <?php if (!empty($user->getRefer())): ?>
                            <div class="flex flex-col gap-4 mt-4">
                                <h3 class="text-lg font-semibold text-gray-300"><?= $t('you_invent'); ?></h3>
                                <div class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08]">
                                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                                        <span class="text-sm text-gray-400"><?= $t('inventor'); ?></span>
                                        <span
                                            class="font-medium"><?= htmlspecialchars(Profile::getReferrerNameStatic($user->getRefer()) ?: $t('unknown')) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-white/5">
                                        <span class="text-sm text-gray-400"><?= $t('code'); ?></span>
                                        <span
                                            class="font-mono text-green-400"><?= htmlspecialchars($user->getRefer()) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm text-gray-400"><?= $t('your_discount'); ?></span>
                                        <span
                                            class="font-bold text-green-400">-<?= intval($user->getDiscountPercent()) ?>%</span>
                                    </div>
                                    <?php if ($user->getDiscountPercent() > 0): ?>
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm text-gray-400"><?= $t('discount_uses_left'); ?></span>
                                        <span class="font-medium"><?= intval($user->getDiscountUses()) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex flex-col gap-4 mt-4">
                                <h3 class="text-lg font-semibold text-gray-300"><?= $t('input_referal_code'); ?></h3>
                                <div
                                    class="flex flex-col gap-4 p-5 rounded-xl bg-white/[0.03] shadow-[0_4px_16px_rgba(0,0,0,0.2)] ring-1 ring-white/[0.08]">
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm text-gray-400"><?= $t('code_referals'); ?></label>
                                        <input type="text" id="referral-code-input"
                                            class="text-[white] w-full bg-black/20 border rounded-lg px-4 py-3 text-center text-xl tracking-widest uppercase placeholder:text-white/20 focus:outline-none focus:border-green-400/50 focus:ring-2 focus:ring-green-400/20 transition-all"
                                            placeholder="XXXXXXX" maxlength="10">
                                    </div>
                                    <button onclick="activateReferralCode()" id="referral-activate-btn"
                                        class="w-full py-3 rounded-lg bg-gradient-to-r from-green-400 to-emerald-500 text-black font-semibold hover:from-green-300 hover:to-emerald-400 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                                        <?= $t('use_code'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                    </section>
                    </template>

                    <!-- SECTION = SUPPORT -->
                    <template data-section="support">
                    <section
                        class="flex flex-col gap-6 box-border h-full w-full p-10 ml-2 relative z-10 rounded-3xl setka"
                        data-section="support">

                        <!-- Header -->
                        <h1 class="text-3xl font-bold">
                            <?php foreach (mb_str_split($t('support')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>

                        <?php $chatRootClass = 'flex flex-col flex-1 min-h-0'; $chatBoxClass = 'flex-1 min-h-[320px]'; include 'public/components/chat_user.php'; unset($chatRootClass, $chatBoxClass); ?>
                    </section>
                    </template>
                </div>
            </div>
            </template>

            <!-- ################# LAYOUT MOBILE (шаблон) ####################-->
            <template id="layout-mobile">
            <aside data-theme-invert
                class="z-50 fixed bottom-4 bg-[rgb(78,78,78,0.38)] left-4 right-4 mx-auto rounded-full px-6 py-2">
                <ul class="mobile flex justify-between items-center gap-4">
                    <li class="bg_active relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
                        data-toggle-section="main">
                        <img class="max-h-6" decoding="async" loading="lazy" data-theme-invert loading="lazy"
                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/menu/home.svg" alt="<?= $t('main') ?>"
                            decoding="async">
                    </li>
                    <li class="relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
                        data-toggle-section="profile">
                        <img class="max-h-6" decoding="async" loading="lazy" data-theme-invert loading="lazy"
                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/menu/profile.svg"
                            alt="<?= $t('profile') ?>" decoding="async">
                    </li>
                    <li class="relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
                        data-toggle-section="setting">
                        <img class="max-h-6" decoding="async" loading="lazy" data-theme-invert loading="lazy"
                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/menu/setting.svg"
                            alt="<?= $t('settings') ?>" decoding="async">
                    </li>
                    <li class="relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
                        data-toggle-section="referal">
                        <img class="max-h-6" decoding="async" loading="lazy" data-theme-invert loading="lazy"
                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/menu/refer.svg"
                            alt="<?= $t('additional') ?>" decoding="async">
                    </li>
                </ul>
            </aside>
            <!-- ################# CONTENT MOBILE ####################-->
            <div class="js-sections w-full text-white overflow-clip outer_mobile">

                <!-- SECTION = MAIN -->
                <template data-section="main">
                <section
                    class="setka overflow-hidden relative flex flex-col justify-between py-[95px] box-border w-full min-h-[100dvh] p-10"
                    data-section="main">

                    <!-- overview -->
                    <div data-reveal class="z-10 w-full bank-tile bank-glass p-5" data-sub-url="<?= htmlspecialchars($user->getSubscription()) ?>">
                        <div class="flex items-center justify-between gap-2">
                            <span class="bank-status <?= $vpnStatus === 'active' ? 'text-green-300 border-green-400/25 bg-green-400/10' : 'text-gray-400' ?>">
                                <span class="w-2 h-2 rounded-full <?= $vpnStatus === 'active' ? 'bg-green-400' : 'bg-gray-500' ?>"></span>
                                <?= htmlspecialchars($formattedVpnStatus['status_text']) ?>
                            </span>
                            <span class="text-right shrink-0">
                                <span class="block text-xs text-gray-400 truncate" data-server><?= htmlspecialchars($formattedVpnStatus['location']) ?></span>
                                <span class="block font-mono text-[10px] text-gray-600 mt-0.5"><?= htmlspecialchars($maskedUni) ?></span>
                            </span>
                        </div>
                        <!-- сервер сменился: напомнить обновить подписку в приложении -->
                        <div data-subwatch hidden class="mt-4 flex items-center gap-2.5 rounded-xl border border-yellow-500/30 bg-yellow-500/10 px-3.5 py-2.5">
                            <i class="fa-solid fa-arrows-rotate text-yellow-300 text-xs shrink-0"></i>
                            <div class="flex-1 text-xs text-gray-200">Сервер обновлён — скопируйте ключ заново в профиле</div>
                            <button type="button" data-subwatch-ok class="shrink-0 text-xs text-gray-300 underline">OK</button>
                        </div>
                        <div class="mt-5 flex items-center gap-5">
                            <div class="relative shrink-0" style="width:128px;height:128px">
                                <div class="absolute inset-0 rounded-full bg-green-500/15 blur-2xl"></div>
                                <div class="bank-ticks absolute inset-0 rounded-full"></div>
                                <div class="bank-ring<?= $ringOff ?> absolute inset-0 rounded-full" style="--pt:<?= $ringPct ?>"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <div class="bank-num text-4xl font-bold tabular-nums tracking-tight"><?= $daysLeftNum ?></div>
                                    <div class="bank-label mt-0.5" style="font-size:.65rem"><?= $t('days_short') ?> из <?= $periodDays ?></div>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="py-2 border-b border-white/[0.07]">
                                    <div class="bank-label mb-0.5"><?= rtrim($t('duration_colon'), ':') ?></div>
                                    <div class="text-lg font-semibold text-white tabular-nums"><?= $periodDays ?> <span class="text-xs font-normal text-gray-400"><?= $t('days_short') ?></span></div>
                                </div>
                                <div class="py-2">
                                    <div class="bank-label mb-0.5"><?= rtrim($t('devices_colon'), ':') ?></div>
                                    <div class="text-lg font-semibold text-white tabular-nums"><?= $devicesLabel ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="text-gray-400"><?= rtrim($t('valid_until'), ':') ?> <?= $expiryDate ?></span>
                                <span class="text-gray-300 tabular-nums" data-timeleft>—</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
                                <div class="h-full rounded-full <?= $isActiveSub ? 'bg-gradient-to-r from-green-500 to-emerald-400' : 'bg-gray-600' ?>" style="width:<?= $elapsedPct ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- information -->
                    <div class="z-10 w-full h-full">
                        <ul class="flex flex-col justify-between items-center gap-4 h-full">
                            <!-- block 1 -->
                            <li
                                class="glow-card_mobile relative w-full flex justify-between items-center p-[15px] rounded-xl">
                                <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                                    <div class="flex items-center gap-4">
                                        <img decoding="async" loading="lazy"
                                            src="<?= $site['baseUrl'] . (new Setting\Route\Function\Controllers\Location\Location)->getLocation()['url'] ?>"
                                            alt="" loading="lazy" decoding="async" class="rounded-md h-6">
                                        <div class="flex flex-col justify-start text-lg text-white">
                                            <p class="lowercase">
                                                <?= htmlspecialchars($formattedVpnStatus['location'] ?: 'vpn') ?>
                                            </p>
                                            <p class="text-sm">
                                                <strong class="text-white/50"><?= $t('status') ?>:</strong><span
                                                    class="text-green-400">&nbsp;<?= htmlspecialchars($formattedVpnStatus['status_text']) ?></span>
                                            </p>
                                        </div>
                                    </div>
                                    <img decoding="async" loading="lazy"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/network_on.svg"
                                        alt="" loading="lazy" decoding="async" class="h-6">
                                <?php else: ?>
                                    <img decoding="async" loading="lazy"
                                        src="<?= $site['baseUrl'] . (new Setting\Route\Function\Controllers\Location\Location)->getLocation()['url'] ?>"
                                        alt="" loading="lozy" decoding="async" class="rounded-md h-6">
                                    <div class="flex flex-col items-center justify-start text-lg text-white">
                                        <!-- no -->
                                        <p class="uppercase">vpn <span class="text-[#FF6378]"><?= $t('inactive') ?></span></p>
                                        <!-- yes -->
                                    </div>
                                    <img decoding="async" loading="lazy"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/network_off.svg"
                                        alt="" loading="lozy" decoding="async" class="h-6">
                                <?php endif; ?>
                            </li>

                            <!-- Пробрная подписка -->
                            <?php if ($giftShow): ?>
                            
                              <li class="w-full"> 
                                <form action="/api/gifts/give" method="post">
                                  <button type="submit" class="bank-btn-light bank-btn-split w-full">
                                          <img decoding="async" loading="lazy" src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/free.svg"
                                            alt="buy" loading="lazy" decoding="async"
                                            class="h-6 opacity-70">
                                              
                                          <span class="flex-1 text-center"><?= $t('trial') ?></span>
                                          <img decoding="async" loading="lazy" src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow.svg"
                                            alt="" loading="lazy" decoding="async"
                                            class="h-5 opacity-60">
                                  </button>
                                 </form>
                              </li>
                           
                            <?php endif; ?>
                            
                            <!-- block 2 -->
                            <li data-reveal data-reveal-delay="100" class="relative w-full">
                                <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                                    <a href="/install" class="btn_install_tour bank-btn-primary bank-btn-split">
                                        <img data-theme-invert decoding="async" loading="lazy"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/install_mobile.svg"
                                            alt="" loading="lazy"
                                            class="rounded-md h-6 opacity-70">
                                        <span class="uppercase text-center flex-1 whitespace-nowrap"><?= $t('install_btn') ?> VPN</span>
                                        <img decoding="async" loading="lazy"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow.svg"
                                            alt="" class="h-5 opacity-60">
                                    </a>
                                <?php else: ?>
                                    <a href="/pay" class="bank-btn-primary bank-btn-split">
                                        <img decoding="async" loading="lazy"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/buy.svg"
                                            alt="" class="h-6 opacity-70 invert">
                                        <span class="text-center flex-1 whitespace-nowrap"><?= $t('buy') ?> <?= $t('subscription') ?></span>
                                        <img decoding="async" loading="lazy"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow.svg"
                                            alt="" class="h-5 opacity-60">
                                    </a>
                                <?php endif; ?>
                            </li>
                            
                            <!-- block 3: только то, чего нет в overview -->
                            <li data-reveal data-reveal-delay="170" class="bank-tile relative w-full flex justify-between gap-2 p-3 text-sm">
                                <!-- 1: оплата -->
                                <div class="flex flex-1 min-w-0 flex-col items-center justify-center gap-0.5 text-center">
                                    <span class="text-[11px] text-gray-500">Оплата</span>
                                    <span class="text-sm font-semibold text-gray-100 tabular-nums truncate max-w-full"><?= $lastPayShort !== null ? htmlspecialchars($lastPayShort) : '—' ?></span>
                                </div>
                                <!-- 2: пинг -->
                                <div class="flex flex-1 min-w-0 flex-col items-center justify-center gap-0.5 text-center">
                                    <span class="text-[11px] text-gray-500"><?= $t('ping') ?>, мс</span>
                                    <span class="text-sm font-semibold tabular-nums <?= $formattedVpnStatus['ping_class'] ?>"><?= htmlspecialchars($formattedVpnStatus['ping_label']) ?></span>
                                </div>
                                <!-- 3: IP -->
                                <div class="flex flex-1 min-w-0 flex-col items-center justify-center gap-0.5 text-center">
                                    <span class="text-[11px] text-gray-500">IP</span>
                                    <span class="text-[13px] font-semibold text-gray-100 tabular-nums truncate max-w-full"><?= htmlspecialchars($formattedVpnStatus['ip_address']) ?></span>
                                </div>
                            </li>
                        </ul>
                    </div>

                </section>
                </template>
                <!-- SECTION = PROFILE -->
                <template data-section="profile">
                <section
                    class="setka overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh]"
                    data-section="profile">
                    <div class="px-6 pt-[5.5rem]">
                        <div class="flex items-center justify-between mb-4">
                            <h1 class="bank-h1 text-2xl font-bold">
                                <?php foreach (mb_str_split($t('profile')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>
                            <form action="/auth/logout" method="post">
                                <button type="submit"
                                    class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 transition-all">
                                    <i class="fa-solid fa-right-from-bracket text-red-400 text-xs"></i>
                                    <span class="text-red-400 text-xs font-medium"><?= $t('exit') ?></span>
                                </button>
                            </form>
                        </div>

                        <div class="flex flex-col gap-4">
                            <div data-reveal class="glow-card_mobile bank-glass relative flex items-center gap-4 p-5 rounded-2xl">
                                <div class="relative">
                                    <img decoding="async" loading="lazy"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/avatar/1.png"
                                        alt="avatar" class="rounded-full w-16 h-16 ring-2 ring-white/10">
                                    <div
                                        class="absolute -bottom-1 right-0 w-4 h-4 rounded-full <?= $user->getStatus() === 'on' ? 'bg-green-400' : 'bg-red-400' ?> ring-2 ring-black">
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1 min-w-0">
                                    <h2 class="text-white text-xl font-semibold truncate" data-user-name>
                                        <?= htmlspecialchars($formattedUserProfile['full_name']) ?>
                                    </h2>
                                    <p class="text-sm text-gray-400" data-profile-status>
                                        <?= $formattedUserProfile['status_text'] ?>
                                    </p>
                                    <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($user->getEmail()) ?></p>
                                </div>
                            </div>

                            <div data-reveal data-reveal-delay="70" class="grid grid-cols-2 gap-3">
                                <div class="glow-card_mobile flex flex-col gap-2 p-4 rounded-xl">
                                    <div class="flex items-center gap-2 text-green-400">
                                        <i class="fa fa-wifi"></i>
                                        <span class="text-xs font-medium">VPN</span>
                                    </div>
                                    <span
                                        class="text-white text-sm font-semibold"><?= $formattedUserProfile['subscription_status'] ?></span>
                                </div>
                                <div class="glow-card_mobile flex flex-col gap-2 p-4 rounded-xl">
                                    <div class="flex items-center gap-2 text-blue-400">
                                        <i class="fa fa-language"></i>
                                        <span class="text-xs font-medium"><?= $t('language') ?></span>
                                    </div>
                                    <span
                                        class="text-white text-sm font-semibold"><?= htmlspecialchars($formattedUserProfile['language']) ?></span>
                                </div>
                                <div class="glow-card_mobile flex flex-col gap-2 p-4 rounded-xl">
                                    <div class="flex items-center gap-2 text-purple-400">
                                        <i class="fa fa-server"></i>
                                        <span class="text-xs font-medium"><?= $t('remaining') ?></span>
                                    </div>
                                    <span class="text-white text-sm font-semibold" data-timeleft></span>
                                </div>
                                <div class="glow-card_mobile flex flex-col gap-2 p-4 rounded-xl">
                                    <div class="flex items-center gap-2 text-yellow-400">
                                        <i class="fa fa-palette"></i>
                                        <span class="text-xs font-medium"><?= $t('theme') ?></span>
                                    </div>
                                    <span class="text-white text-sm font-semibold theme-display"
                                        data-theme-text><?= $formattedUserProfile['theme'] ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- data -->
                        <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                            <div data-reveal data-reveal-delay="140" class="mt-4 flex flex-col gap-4 mb-4">
                                <h4 class="text-white text-xl font-semibold"><?= $t('data') ?></h4>
                                <ul class="flex flex-col gap-2.5">
                                    <li class="glow-card_mobile flex p-4 justify-between items-center rounded-xl">
                                        <!-- info -->
                                        <div class="flex flex-col justify-center w-[150px] gap-1">
                                            <h4 class="text-white text-sm font-semibold"><?= $t('vpn_key') ?></h4>
                                            <code id="vpn-key" class="overflow-hidden h-8 break-all text-[12px] text-white/50">
                                                   <?php echo htmlspecialchars($user->getSubscription()); ?>
                                            </code>
                                        </div>
                                        <!-- button -->
                                        <div class="flex gap-2 justify-end items-center">
                                            <button
                                                onclick="window.open('<?= htmlspecialchars($user->getSubscription()) ?>','_blank')"
                                                title="<?= $t('copy') ?>"
                                                class="z-10 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors group cursor-pointer">
                                                <i class="fa fa-share text-gray-400 group-hover:text-white"></i>
                                            </button>
                                            <button onclick="copyVpnKey()"
                                                class="z-10 text-lg text-gray-400 hover:text-white transition-colors"
                                                title="<?= $t('copy_key') ?>">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                            <button onclick="deleteSubscription()"
                                                class="z-10 text-lg text-red-400 hover:text-red-300 transition-colors"
                                                title="<?= $t('delete_subscription') ?>">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Server Select (radio scroll list) -->
                        <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                            <div data-reveal data-reveal-delay="210" class="mt-4 flex flex-col gap-3 mb-2">
                                <h4 class="text-white text-xl font-semibold"><?= $t('server_select') ?></h4>
                                <div class="glow-card_mobile p-3 rounded-xl">
                                    <div class="flex flex-col gap-1.5 max-h-40 overflow-y-auto pr-1">
                                        <?php foreach ($availableServers as $srv): ?>
                                            <label
                                                class="flex items-center gap-3 p-3 rounded-lg cursor-pointer hover:bg-white/[0.06] transition-colors <?= $srv['code'] === $currentServerCode ? 'bg-white/[0.08] ring-1 ring-green-400/30' : '' ?>">
                                                <input type="radio" name="vpn-server-mobile" value="<?= $srv['code'] ?>"
                                                    <?= $srv['code'] === $currentServerCode ? 'checked' : '' ?>
                                                    onchange="changeServer('<?= $srv['code'] ?>')"
                                                    class="accent-green-400 w-4 h-4 cursor-pointer">
                                                <img decoding="async" loading="lazy"
                                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/flags/<?= $srv['flag'] ?>"
                                                    alt="<?= $srv['code'] ?>" class="w-7 h-5 rounded object-cover">
                                                <span class="text-white text-sm font-medium flex-1"><?= htmlspecialchars($srv['country']) ?></span>
                                                <?php if ($srv['code'] === $currentServerCode): ?>
                                                    <span class="text-xs text-green-400"><?= $t('server_current') ?></span>
                                                <?php endif; ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Payment History (id+дата+сумма из JSON, квитанция живьём из кассы) -->
                        <div data-reveal data-reveal-delay="280" class="flex flex-col gap-4 mt-6">
                            <h3 class="text-xl font-semibold text-gray-300"><?= $t('pay_history') ?></h3>
                            <div class="glow-card p-4 rounded-xl">
                                <div data-pay-history
                                    data-empty="<?= htmlspecialchars($t('pay_empty')) ?>"
                                    data-error="<?= htmlspecialchars($t('pay_error')) ?>"
                                    data-receipt="<?= htmlspecialchars($t('pay_receipt')) ?>"
                                    class="flex flex-col gap-2">
                                    <span class="text-sm text-gray-500"><?= $t('pay_loading') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Company Links & Logout -->
                        <div data-reveal data-reveal-delay="350" class="mt-6 flex flex-col gap-4">
                            <h4 class="text-white text-xl font-semibold"><?= $t('company') ?></h4>
                            <div data-reveal data-reveal-delay="420" class="grid grid-cols-2 gap-3">
                                <a href="/about"
                                    class="glow-card_mobile flex flex-col items-center justify-center gap-2 p-4 rounded-xl">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center ring-1 ring-white/10">
                                        <i class="fa-solid fa-building text-gray-300 text-lg"></i>
                                    </div>
                                    <span class="text-white text-sm font-medium"><?= $t('about_title') ?></span>
                                </a>
                                <a href="/requisites"
                                    class="glow-card_mobile flex flex-col items-center justify-center gap-2 p-4 rounded-xl">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center ring-1 ring-white/10">
                                        <i class="fa-solid fa-file-invoice text-gray-300 text-lg"></i>
                                    </div>
                                    <span class="text-white text-sm font-medium"><?= $t('requisites') ?></span>
                                </a>
                            </div>

                        </div>
                    </div>

                </section>
                </template>
                <!-- SECTION = SETTING -->
                <template data-section="setting">
                <section
                    class="setka px-6 pt-[5rem] overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh]"
                    data-section="setting">

                    <h1 class="text-2xl font-bold mb-4">
                        <?php foreach (mb_str_split($t('settings')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>

                    <!-- 1 -->
                    <div class="flex flex-col gap-4 mb-4">
                        <h4 class="text-white text-xl font-semibold"><?= $t('app_settings') ?></h4>
                        <ul class="flex flex-col gap-2.5">
                            <!-- theme -->
                            <li
                                class="glow-card_mobile flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                                        <i class="fa fa-sun text-yellow-400 text-lg"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-white font-medium"><?= $t('light') ?></span>
                                        <span class="text-sm text-gray-400"><?= $t('change_decoration') ?></span>
                                    </div>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" value="" class="sr-only peer" data-darkModeToggle>
                                    <div
                                        class="relative w-11 h-6 bg-[#857a7a38] rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-400">
                                    </div>
                                </label>
                            </li>
                            <!-- language -->
                            <li
                                class="glow-card_mobile flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                        <i class="fa fa-language text-blue-400 text-lg"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-white font-medium"><?= $t('language') ?></span>
                                        <span class="text-sm text-gray-400"><?= $t('language_switch') ?></span>
                                    </div>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" value="rus" class="sr-only peer" data-language>
                                    <div
                                        class="relative w-11 h-6 bg-[#857a7a38] rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-400">
                                    </div>
                                </label>
                            </li>
                        </ul>
                    </div>


                    <!-- 2 -->
                    <div class="flex flex-col gap-4 mb-4">
                        <h4 class="text-white text-xl font-semibold"><?= $t('Confidentiality') ?></h4>
                        <div class="flex flex-col gap-2">
                            <!-- <a href="/"
                                                                                                            class="glow-card_mobile flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors group">
                                                                                                            <div class="flex items-center gap-4">
                                                                                                                <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                                                                                                    <i class="fa fa-credit-card text-purple-400 text-lg"></i>
                                                                                                                </div>
                                                                                                                <span class="text-white font-medium"><?= $t('auto_payment') ?></span>
                                                                                                            </div>
                                                                                                            <i
                                                                                                                class="fa fa-angle-right text-gray-400 group-hover:text-white group-hover:translate-x-1 transition-all"></i>
                                                                                                        </a> -->
                            <button data-toggle-modal="politic"
                                class="glow-card_mobile flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors group text-left">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                        <i class="fa fa-shield-alt text-emerald-400 text-lg"></i>
                                    </div>
                                    <span class="text-white font-medium"><?= $t('politic');?></span>
                                </div>
                                <i
                                    class="fa fa-angle-right text-gray-400 group-hover:text-white group-hover:translate-x-1 transition-all"></i>
                            </button>
                            <button data-toggle-modal="access"
                                class="glow-card_mobile flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors group text-left">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                        <i class="fa fa-file-contract text-emerald-400 text-lg"></i>
                                    </div>
                                    <span class="text-white font-medium"><?= $t('soglashenia');?></span>
                                </div>
                                <i
                                    class="fa fa-angle-right text-gray-400 group-hover:text-white group-hover:translate-x-1 transition-all"></i>
                            </button>
                          </div>
                        </div>

                </section>
                </template>
                <!-- SECTION = REFER -->
                <template data-section="referal">
                <section
                    class="setka overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh]"
                    data-section="referal">
                    <div class="px-6 pt-[5.5rem] flex flex-col gap-5">
                        <h1 class="text-2xl font-bold">
                            <?php foreach (mb_str_split($t('referals')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>

                        <div data-reveal class="grid grid-cols-2 gap-3">
                            <div
                                class="glow-card_mobile flex flex-col gap-2 p-4 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2 text-emerald-400">
                                    <i class="fa fa-signal text-lg"></i>
                                    <span class="text-xs font-medium"><?= $t('status'); ?></span>
                                </div>
                                <span
                                    class="text-white text-sm font-semibold"><?= $formattedUserProfile['subscription_status'] ?></span>
                            </div>
                            <div
                                class="glow-card_mobile flex flex-col gap-2 p-4 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2 text-blue-400">
                                    <i class="fa fa-users text-lg"></i>
                                    <span class="text-xs font-medium"><?= $t('referals'); ?></span>
                                </div>
                                <span class="text-white text-sm font-semibold"><?= $user->getReferCount() ?></span>
                            </div>
                            <div
                                class="glow-card_mobile flex flex-col gap-2 p-4 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2">
                                    <i class="fa fa-percent text-green-400"></i>
                                    <span class="text-white text-xs font-medium"><?= $t('discount'); ?></span>
                                </div>
                                <span
                                    class="text-white text-sm font-semibold"><?= $user->getDiscountPercent() ?>%</span>
                            </div>
                        </div>

                        <!-- Referral link/cards -->
                        <div class="flex flex-col gap-3">
                            <h4 class="text-white text-lg font-semibold"><?= $t('your_referal_links'); ?></h4>

                            <div class="glow-card_mobile flex items-center gap-3 p-4 rounded-xl">
                                <div
                                    class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                                    <i class="fa fa-ticket text-emerald-400"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs text-gray-400"><?= $t('your_code'); ?></div>
                                    <div class="text-white font-semibold truncate">
                                        <?= htmlspecialchars($user->getMyRefer()) ?>
                                    </div>
                                </div>
                                <button
                                    onclick="copyToClipboard('<?= htmlspecialchars($user->getMyRefer()) ?>', <?= json_encode($t('referal_code')) ?>)"
                                    class="z-10 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors group shrink-0 cursor-pointer"
                                    title="<?= $t('copy_code') ?>">
                                    <i class="fa fa-copy text-gray-400 group-hover:text-white"></i>
                                </button>
                            </div>

                            <div class="glow-card_mobile flex items-center gap-3 p-4 rounded-xl">
                                <div
                                    class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center shrink-0">
                                    <i class="fa fa-link text-blue-400"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs text-gray-400"><?= $t('links'); ?></div>
                                    <p class="text-white text-xs truncate">
                                        <?= htmlspecialchars($user->getMyRefer() ? 'https://' . $_SERVER['HTTP_HOST'] . '/reflink=' . $user->getMyRefer() : '') ?>
                                    </p>
                                </div>
                                <button
                                    onclick="copyToClipboard('<?= htmlspecialchars($user->getMyRefer() ? 'https://' . $_SERVER['HTTP_HOST'] . '/reflink=' . $user->getMyRefer() : '') ?>', <?= json_encode($t('referal_link')) ?>)"
                                    class="z-10 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors group shrink-0 cursor-pointer"
                                    title="<?= $t('copy_link') ?>">
                                    <i class="fa fa-copy text-gray-400 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Who gets what (mobile) -->
                        <div class="flex flex-col gap-3">
                            <h4 class="text-white text-lg font-semibold"><?= $t('refer_what_get'); ?></h4>
                            <div class="glow-card_mobile p-4 rounded-xl flex flex-col gap-3">
                                <div class="flex items-center gap-2 text-emerald-400">
                                    <i class="fa fa-ticket"></i>
                                    <span class="text-xs font-medium"><?= $t('refer_for_invited'); ?></span>
                                </div>
                                <div class="flex flex-col gap-1.5 text-sm text-white">
                                    <div><?= htmlspecialchars((string) $referWhat['invited_days']) ?></div>
                                    <div><?= htmlspecialchars((string) $referWhat['invited_discount']) ?></div>
                                </div>
                            </div>
                            <div class="glow-card_mobile p-4 rounded-xl flex flex-col gap-3">
                                <div class="flex items-center gap-2 text-blue-400">
                                    <i class="fa fa-users"></i>
                                    <span class="text-xs font-medium"><?= $t('refer_for_inviter'); ?></span>
                                </div>
                                <div class="flex flex-col gap-1.5 text-sm text-white">
                                    <div><?= htmlspecialchars((string) $referWhat['inviter_each']) ?></div>
                                    <div><?= htmlspecialchars((string) $referWhat['inviter_percent']) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Activation code -->
                        <div class="flex flex-col gap-3">
                            <h4 class="text-white text-lg font-semibold"><?= $t('active_code'); ?></h4>
                            <div class="glow-card_mobile flex flex-col gap-3 p-4 rounded-xl">
                                <label class="text-xs text-gray-400"><?= $t('code_referals'); ?></label>
                                <input type="text" id="referral-code-input-mobile"
                                    class="z-10 text-white w-full bg-transparent border border-green-400/50 rounded-lg px-4 py-3 text-center text-xl tracking-widest uppercase placeholder:text-white/20 focus:outline-none focus:border-green-400/50 focus:ring-2 focus:ring-green-400/20 transition-all"
                                    placeholder="XXXXXXX" maxlength="10">
                                <button onclick="activateReferralCode('mobile')" id="referral-activate-btn-mobile"
                                    class="w-full py-3 rounded-lg bg-gradient-to-r from-green-400 to-emerald-500 text-black font-semibold hover:from-green-300 hover:to-emerald-400 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                                    <?= $t('use_code'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Referrer info -->
                        <div class="flex flex-col gap-3">
                            <h4 class="text-white text-lg font-semibold"><?= $t('your_refer'); ?></h4>
                            <div class="glow-card_mobile p-4 rounded-xl flex flex-col gap-3">
                                <?php if (!empty($user->getRefer())): ?>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-sm text-gray-400"><?= $t('code'); ?></span>
                                        <span
                                            class="text-sm text-white font-semibold truncate"><?= htmlspecialchars($user->getRefer()) ?></span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-sm text-gray-400"><?= $t('name'); ?></span>
                                        <span
                                            class="text-sm text-white font-semibold truncate"><?= htmlspecialchars(Profile::getReferrerNameStatic($user->getRefer()) ?: $t('unknown')) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-sm text-gray-400"><?= $t('referal_no_have'); ?></div>
                                <?php endif; ?>
                                <div class="flex justify-between gap-4">
                                    <span class="text-sm text-gray-400"><?= $t('you_get'); ?></span>
                                    <span class="text-sm text-white font-semibold">
                                        <?php if ($formattedUserProfile['discount_percent'] > 0): ?>
                                            <span
                                                class="text-green-400">-<?= intval($formattedUserProfile['discount_percent']) ?>%</span>
                                        <?php else: ?>
                                            <span class="text-gray-400"><?= $t('not_discount'); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if ($user->getDiscountPercent() > 0): ?>
                                <div class="flex justify-between gap-4">
                                    <span class="text-sm text-gray-400"><?= $t('discount_uses_left'); ?></span>
                                    <span class="text-sm text-white font-semibold"><?= intval($user->getDiscountUses()) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Invited by you (mobile) -->
                        <div class="flex flex-col gap-3">
                            <h4 class="text-white text-lg font-semibold"><?= $t('invited_by_you'); ?> (<?= count($referralsList) ?>)</h4>
                            <div class="glow-card_mobile p-4 rounded-xl flex flex-col gap-3">
                                <?php if (!empty($referralsList)): ?>
                                    <?php foreach ($referralsList as $ref): ?>
                                        <div class="flex justify-between gap-4">
                                            <span class="text-sm text-white font-semibold truncate"><?= htmlspecialchars($ref['name'] !== '' ? $ref['name'] : $ref['email']) ?></span>
                                            <span class="text-xs text-gray-400 shrink-0"><?= htmlspecialchars($ref['date']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-sm text-gray-400"><?= $t('invited_empty'); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                </section>
                </template>

                <template data-section="support">
                <section
                    class="setka overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh]"
                    data-section="support">
                    <div class="px-6 pt-[5.5rem] flex flex-col gap-5">
                        <h1 class="text-2xl font-bold">
                            <?php foreach (mb_str_split($t('support')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>

                        <?php include 'public/components/chat_user.php'; ?>
                    </div>

                </section>
                </template>
            </div>
            </template>
        </main>

        <!-- modal = <?= $t('politic') ?> -->
        <div data-modal="politic" class="modal-overlay hidden">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?= $t('politic') ?></h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p><strong><?= $t('politic') ?> <?= htmlspecialchars($site['ООО']) ?></strong></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_effective_date') ?></strong> 26.03.2026</p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s1') ?></strong></p>
                    <p><?= $t('pol_s1_1') ?>
                        <?= htmlspecialchars($site['ООО']) ?> <?= $t('pol_s1_1_end') ?>
                    </p>
                    <p><?= $t('pol_s1_2') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s2') ?></strong></p>
                    <p><?= $t('pol_s2_1') ?></p>
                    <p><?= $t('pol_s2_2') ?></p>
                    <p><?= $t('pol_s2_3') ?></p>
                    <p><?= $t('pol_s2_4') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s3') ?></strong></p>
                    <p><?= htmlspecialchars($site['ООО']) ?> <?= $t('pol_s3_1') ?>
                    </p>
                    <p><?= $t('pol_s3_2') ?></p>
                    <p><?= $t('pol_s3_3') ?></p>
                    <p><?= $t('pol_s3_4') ?></p>
                    <p><?= $t('pol_s3_5') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s4') ?></strong></p>
                    <p><?= $t('pol_s4_1') ?></p>
                    <p><?= $t('pol_s4_2') ?></p>
                    <p><?= $t('pol_s4_3') ?></p>
                    <p><?= $t('pol_s4_4') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s5') ?></strong></p>
                    <p><?= $t('pol_s5_1') ?></p>
                    <p><?= $t('pol_s5_2') ?></p>
                    <p><?= $t('pol_s5_3') ?></p>
                    <p><?= $t('pol_s5_4') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s6') ?></strong></p>
                    <p><?= $t('pol_s6_1') ?></p>
                    <p><?= $t('pol_s6_2') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s7') ?></strong></p>
                    <p><?= $t('pol_s7_1') ?></p>
                    <p><?= $t('pol_s7_2') ?></p>
                    <p><?= $t('pol_s7_3') ?></p>
                    <p><?= $t('pol_s7_4') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s8') ?></strong></p>
                    <p><?= $t('pol_s8_1') ?></p>
                    <p><?= $t('pol_s8_2') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s9') ?></strong></p>
                    <p><?= htmlspecialchars($site['ООО']) ?> <?= $t('pol_s9_1') ?></p>
                    <p><?= $t('pol_s9_2') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s10') ?></strong></p>
                    <p>Email: <?= $site['контакты']['Почта'] ?></p>
                    <p><?= $t('website_colon') ?> <?= $site['baseUrl'] ?></p>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn-close"><?= $t('close') ?></button>
                </div>
            </div>
        </div>

        <!-- modal = <?= $t('soglashenia') ?> -->
        <div data-modal="access" class="modal-overlay hidden">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?= $t('soglashenia') ?></h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p><strong><?= $t('soglashenia') ?> <?= htmlspecialchars($site['ООО']) ?></strong></p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_effective_date') ?></strong> 26.03.2026</p>
                    <hr class="my-4">

                    <p><strong><?= $t('pol_s1') ?></strong></p>
                    <p><?= $t('sog_s1_1') ?>
                        <?= htmlspecialchars($site['ООО']) ?> <?= $t('sog_s1_1_end') ?>
                    </p>
                    <p><?= $t('sog_s1_2') ?></p>
                    <p><?= $t('sog_s1_3') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s2') ?></strong></p>
                    <p><?= htmlspecialchars($site['ООО']) ?> <?= $t('sog_s2_1') ?></p>
                    <p><?= $t('sog_s2_2') ?></p>
                    <p><?= $t('sog_s2_3') ?></p>
                    <p><?= $t('sog_s2_4') ?></p>
                    <p><?= $t('sog_s2_5') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s3') ?></strong></p>
                    <p><?= $t('sog_s3_1') ?></p>
                    <p><?= $t('sog_s3_2') ?></p>
                    <p><?= $t('sog_s3_3') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s4') ?></strong></p>
                    <p><?= $t('sog_s4_1') ?></p>
                    <p><?= $t('sog_s4_2') ?></p>
                    <p><?= $t('sog_s4_3') ?></p>
                    <p><?= $t('sog_s4_4') ?></p>
                    <p><?= $t('sog_s4_5') ?></p>
                    <p><?= $t('sog_s4_6') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s5') ?></strong></p>
                    <p><?= htmlspecialchars($site['ООО']) ?> <?= $t('sog_s5_1') ?></p>
                    <p><?= $t('sog_s5_2') ?></p>
                    <p><?= $t('sog_s5_3') ?></p>
                    <p><?= $t('sog_s5_4') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s6') ?></strong></p>
                    <p><?= $t('sog_s6_1') ?></p>
                    <p><?= $t('sog_s6_2') ?></p>
                    <p><?= $t('sog_s6_3') ?></p>
                    <p><?= $t('sog_s6_4') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s7') ?></strong></p>
                    <p><?= htmlspecialchars($site['ООО']) ?> <?= $t('sog_s7_1') ?></p>
                    <p><?= $t('sog_s7_2') ?></p>
                    <p><?= $t('sog_s7_3') ?></p>
                    <p><?= $t('sog_s7_4') ?></p>
                    <p><?= $t('sog_s7_5') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s8') ?></strong></p>
                    <p><?= $t('sog_s8_1') ?></p>
                    <p><?= $t('sog_s8_2') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s9') ?></strong></p>
                    <p><?= $t('sog_s9_1') ?></p>
                    <p><?= $t('sog_s9_2') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s10') ?></strong></p>
                    <p><?= $t('sog_s10_1') ?></p>
                    <hr class="my-4">

                    <p><strong><?= $t('sog_s11') ?></strong></p>
                    <p>Email: <?= $site['контакты']['Почта'] ?></p>
                    <p><?= $t('website_colon') ?> <?= $site['baseUrl'] ?></p>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn-close"><?= $t('close') ?></button>
                </div>
            </div>
            /div>
        </div>

        <script>
            // Устанавливаем тему в localStorage из PHP при загрузке
            const currentThemeFromPHP = '<?= $formattedUserProfile['theme'] ?>';
            if (!localStorage.getItem('theme')) {
                localStorage.setItem('theme', currentThemeFromPHP);
            }
        </script>

        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/main/main.js<?= '?v=' . $site['versionApp'] ?>" defer></script>
        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/theme/main.js<?= '?v=' . $site['versionApp'] ?>" defer></script>
        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/lang/lang.js<?= '?v=' . $site['versionApp'] ?>" defer></script>

        <script defer>
            // Копирование VPN ключа
            function copyVpnKey() {
                const el = document.getElementById('vpn-key-desktop') || document.getElementById('vpn-key');
                const text = el?.textContent?.trim();
                text ? copyToClipboard(text, <?= json_encode($t('vpn_key')) ?>) : showNotification(<?= json_encode($t('vpn_key_not_found')) ?>, 'error');
            }

            // Удаление подписки
            async function deleteSubscription() {
                if (!confirm(<?= json_encode($t('confirm_delete_subscription')) ?>)) return;

                try {
                    const res = await fetch('/api/subscription/delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    });

                    const text = await res.text();
                    let data = {};

                    try {
                        data = JSON.parse(text);
                    } catch {
                        // Если не JSON, считаем успехом если HTTP 200 и подписка пропала
                        if (res.ok) {
                            showNotification(<?= json_encode($t('subscription_deleted')) ?>, 'success');
                            setTimeout(() => location.reload(), 1500);
                            return;
                        }
                    }

                    // Проверяем разные варианты успешного ответа
                    const isOk = data.status === 'ok' || data.success === true || res.ok;
                    const isPartial = data.status === 'partial';

                    if (isOk || isPartial) {
                        showNotification(data.message || <?= json_encode($t('subscription_deleted')) ?>, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(data.message || data.error || <?= json_encode($t('delete_error')) ?>, 'error');
                    }
                } catch (e) {
                    showNotification(<?= json_encode($t('network_error')) ?>, 'error');
                }
            }

            // Смена сервера (radio список в профиле)
            const currentServerCode = <?= json_encode($currentServerCode) ?>;

            function resetServerRadios() {
                document.querySelectorAll('input[name="vpn-server"], input[name="vpn-server-mobile"]').forEach(radio => {
                    radio.checked = radio.value === currentServerCode;
                });
            }

            async function changeServer(code) {
                if (code === currentServerCode) return;
                if (!confirm(<?= json_encode($t('server_change_confirm')) ?>)) {
                    resetServerRadios();
                    return;
                }

                try {
                    const res = await fetch('/api/server/switch', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ server: code })
                    });

                    const data = await res.json();

                    if (data.status === 'ok') {
                        showNotification(data.message || <?= json_encode($t('server_changed')) ?>, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(data.message || data.error || <?= json_encode($t('network_error')) ?>, 'error');
                        resetServerRadios();
                    }
                } catch (e) {
                    showNotification(<?= json_encode($t('network_error')) ?>, 'error');
                    resetServerRadios();
                }
            }

            <?php $refStatus = $_GET['ref_status'] ?? null;
            $refMsg = $_GET['ref_msg'] ?? null;
            if ($refStatus && $refMsg)
                echo "showNotification('" . addslashes($refMsg) . "', '" . $refStatus . "');"; ?>

            // Показать уведомление
            function showNotification(msg, type = 'info') {
                let container = document.getElementById('notification-container') || ((newContainer = document.createElement('div')) => (newContainer.id = 'notification-container', newContainer.className = 'fixed right-2 top-2 z-[999] flex flex-col gap-2', document.body.appendChild(newContainer), newContainer))();
                const element = container.appendChild(document.createElement('div'));
                element.className = `px-6 py-3 rounded-lg text-white z-50 transform translate-x-full transition-transform duration-300 ${{ success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' }[type] || 'bg-blue-500'}`;
                element.innerHTML = '<i class="fa-solid fa-info-circle"></i> ' + msg;
                setTimeout(() => element.classList.remove('translate-x-full'), 100);
                setTimeout(() => element.classList.add('translate-x-full'), 4100);
                setTimeout(() => (element.remove(), container.children.length || container.remove()), 4400);
            }

            // ============================================================================
            // Универсальная функция копирования
            function copyToClipboard(text, label = <?= json_encode($t('text_default')) ?>) {
                if (!text) {
                    showNotification(<?= json_encode($t('nothing_to_copy')) ?>, 'error');
                    return;
                }
                // Пробуем современный API (требует HTTPS)
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(() => {
                        showNotification(`${label} <?= $t('copied') ?>`, 'success');
                    }).catch(err => {
                        console.error(<?= json_encode($t('copy_error_colon')) ?>, err);
                        fallbackCopy(text, label);
                    });
                } else {
                    fallbackCopy(text, label);
                }
            }

            // Fallback для HTTP или старых браузеров
            function fallbackCopy(text, label) {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    showNotification(`${label} <?= $t('copied') ?>`, 'success');
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                    showNotification(<?= json_encode($t('copy_error')) ?>, 'error');
                }
                document.body.removeChild(textarea);
            }

            // Активация реферального кода
            function activateReferralCode(scope = 'desktop') {
                const codeInput = scope === 'mobile'
                    ? document.getElementById('referral-code-input-mobile')
                    : document.getElementById('referral-code-input');
                const btn = scope === 'mobile'
                    ? document.getElementById('referral-activate-btn-mobile')
                    : document.getElementById('referral-activate-btn');
                const code = codeInput ? codeInput.value.trim() : '';

                if (!code) {
                    showNotification(<?= json_encode($t('enter_referal_code')) ?>, 'error');
                    return;
                }

                // Блокируем кнопку на время запроса
                if (btn) {
                    btn.disabled = true;
                    fetch('/api/referral/activate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ code: code, online: "on" })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                showNotification(data.message, 'success');
                                // Перезагружаем страницу через 2 секунды
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            } else {
                                showNotification(data.message, 'error');
                                if (btn) {
                                    btn.disabled = false;
                                    btn.textContent = <?= json_encode($t('use')) ?>;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка:', error);
                            showNotification(<?= json_encode($t('server_error_activation')) ?>, 'error');
                            if (btn) {
                                btn.disabled = false;
                                btn.textContent = <?= json_encode($t('use')) ?>;
                            }
                        });
                }
            }

            // Enter key для активации реферального кода
            // Referral input enter key handler
            const referInputs = [
                document.getElementById('referral-code-input'),
                document.getElementById('referral-code-input-mobile')
            ].filter(Boolean);
            referInputs.forEach((inp) => {
                inp.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        activateReferralCode(inp.id === 'referral-code-input-mobile' ? 'mobile' : 'desktop');
                    }
                });
            });
        </script>

        <script defer>
            const expiry = <?= htmlspecialchars($user->getExpiry()) / 1000 /*секунд*/ ?>;

            /**
             * Обратный отсчёт подписки. Секции рендерятся лениво (появляются/исчезают),
             * поэтому элементы [data-timeleft] ищем в живой DOM каждый тик.
             * Один интервал на страницу — без накопления таймеров.
             */
            setInterval(function () {
                const elements = document.querySelectorAll('[data-timeleft]');
                if (!elements.length) return;

                const remaining = expiry - Math.floor(Date.now() / 1000);
                if (remaining <= 0) {
                    elements.forEach(function (el) {
                        if (!el.classList.contains('text-red-400')) {
                            el.classList.add('text-red-400');
                        }
                        el.textContent = <?= json_encode($t('subscription_inactive')) ?>;//в случае, если клиент не купил подписку ему не показывалось (подписка истекла)
                    });
                    return;
                }

                const days = Math.floor(remaining / 86400);
                const hours = Math.floor((remaining % 86400) / 3600);
                const minutes = Math.floor((remaining % 3600) / 60);
                const seconds = remaining % 60;

                // версии для показа
                let timer_show = 0;
                if (days > 1) { timer_show = `${days} ${days === 1 ? <?= json_encode($t('day_1')) ?> : <?= json_encode($t('days')) ?>}`;}
                  else if (days === 1) { timer_show = `${days} <?= $t('day_1') ?>`; }
                    else if (hours > 0) { timer_show = `${hours} ${hours === 1 ? <?= json_encode($t('hour_1')) ?> : <?= json_encode($t('hours_plural')) ?>}`; } 
                      else if (hours === 1) { timer_show = `${hours} <?= $t('hour_1') ?> ${minutes} ${minutes === 1 ? <?= json_encode($t('minute_1')) ?> : <?= json_encode($t('minutes_plural')) ?>}`; } 
                        else if (minutes > 4) { timer_show = `${minutes} ${minutes === 1 ? <?= json_encode($t('minute_1')) ?> : <?= json_encode($t('minutes_plural')) ?>} ${seconds} ${seconds === 1 ? <?= json_encode($t('second_1')) ?> : <?= json_encode($t('seconds_plural')) ?>}`; } 
                          else if (minutes <= 4 && minutes !== 1) { timer_show = `${minutes} <?= $t('minute_2') ?> ${seconds} ${seconds === 1 ? <?= json_encode($t('second_1')) ?> : <?= json_encode($t('seconds_plural')) ?>}`; } 
                            else if (minutes === 1) { timer_show = `${minutes} <?= $t('minute_1') ?> ${seconds} ${seconds === 1 ? <?= json_encode($t('second_1')) ?> : <?= json_encode($t('seconds_plural')) ?>}`; } 
                              else if (minutes === 0) { timer_show = `${seconds} <?= $t('seconds_plural') ?>`; }

                elements.forEach(function (el) {
                    if (el.classList.contains('text-red-400')) {
                        el.classList.remove('text-red-400');
                    }
                    el.textContent = timer_show;
                });
            }, 1000);
        </script>
    </div>
    <?php include_once "public/components/tour.php" ?>
</body>

</html>