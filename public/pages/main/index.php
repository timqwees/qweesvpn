<?php
use App\Models\Network\Network;
use Setting\Route\Function\Controllers\{Auth\Auth, Client\GetUser, Language\Language, OS\OS, Vpn\VpnStatus, Profile\Profile, System\SystemInfo};
use Setting\Route\Function\Functions;

Auth::auth();//проверка авторизации
//====================================================================================
$user = new GetUser();
if (!$user->onCheckSubscription() && (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/') !== '/')//получает информацию пользователя и проверят подписку (false - обновление подписки | true - все ок)
    Network::onRedirect('/');
if ($user->onPaymantStatus())//если в сесии есть payment_id, то оплата не проверена
    Network::onRedirect('/pay/status');//перенаправляем на страницу проверки
//===================================================================================
$site = Functions::site();//после всех провроек получем уже данные сервиса

// язык
$currentLanguage = Language::getCurrent();
$translations = Language::getTranslations($currentLanguage);

// Хелпер для короткого доступа к переводам
$t = fn(string $key): string => $translations[$key] ?? $key;

// Получаем реальные данные через новые классы
$vpnStatusObj = new VpnStatus();
$profileObj = new Profile();
$usageStats = $vpnStatusObj->getUsageStats();

// Оптимизированное формирование данных без лишних вызовов
$vpnStatus = $vpnStatusObj->getStatus();
$pingMs = $vpnStatusObj->getPingMs();
$pingStatus = $vpnStatusObj->getPingStatus();

$formattedVpnStatus = [
    'status_text' => $t($vpnStatus === 'active' ? 'active' : 'inactive'),
    'status_class' => $vpnStatus === 'active' ? 'text-green-400' : 'text-red-400',
    'ping_label' => $pingMs !== null ? $pingMs . ' ms' : '—',
    'ping_class' => $pingStatus === 'good' ? 'text-green-400' : ($pingStatus === 'inactive' ? 'text-red-400' : 'text-gray-400'),
    'ping_icon' => $pingStatus === 'good' ? 'fa-arrow-up' : ($pingStatus === 'inactive' ? 'fa-arrow-down' : 'fa-minus'),
    'protocol' => $vpnStatusObj->getProtocol(),
    'ip_address' => $vpnStatusObj->getIpAddress(),
    'location' => $vpnStatusObj->getLocation(),
    'background_world' => $vpnStatus === 'active' ? 'world_green.png' : 'world_red.png',
    'monoblock_image' => [
        'layout_bg' => $vpnStatus === 'active' ? 'layout_bg_green.png' : 'layout_bg_red.png',
        'layout_spin' => $vpnStatus === 'active' ? 'layout_spin_green.png' : 'layout_spin_red.png',
        'layout_center' => 'layout_center.png'
    ],
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
    'bonus_percent' => $user->getBonusPercent(),
    'subscription_status' => $t($user->getStatus() === 'on' ? 'active' : 'inactive'),
    'theme' => $_COOKIE['theme'] ?? $_SESSION['theme'] ?? 'Темная', // Получаем тему из куки или сессии
    'language' => Language::LANGUAGES[$currentLanguage] ?? 'Русский'
];

$systemInfoObj = new SystemInfo();
$formattedSystemInfo = [
    'version' => $systemInfoObj->getVersion(),
    'db_status' => $systemInfoObj->getDbStatus(),
    'db_status_text' => $t($systemInfoObj->getDbStatus() === 'connected' ? 'yes' : 'no'),
    'db_status_class' => $systemInfoObj->getDbStatus() === 'connected' ? 'text-green-400' : 'text-red-400'
];

$activeSection = $_GET['section'] ?? 'main';
if (!in_array($activeSection, ['main', 'profile', 'setting', 'referal'], true)) {
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
    <link rel="preload" href="/public/assets/styles/style.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" as="style"
        crossorigin="anonymous">
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

    <link rel="stylesheet" href="/public/assets/styles/style.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/style.css">
    </noscript>

    <!-- Async/Deferred scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Noscript fallback -->
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/noscript.css">
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
                        <h1 class="text-3xl font-bold">
                            <?php foreach (mb_str_split($t('main')) as $letter): ?>
                                    <span class="loader-letter text-[white]"><?= htmlspecialchars($letter) ?></span>
                                <?php endforeach; ?></h1>

                        <!-- контент -->
                        <div class="flex items-start justify-center gap-6 w-full">
                            <!-- BLOCK-1 => DISPLAY STATUS -->
                            <div
                                class="glow-card relative min-h-[600px] flex flex-1 flex-col items-center justify-center rounded-2xl overflow-hidden">
                                <!-- backgound -->
                                <img decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/background/<?= htmlspecialchars($formattedVpnStatus['background_world']) ?>"
                                    alt="background" class="absolute w-full h-full opacity-20" loading="lazy">

                                <!-- Monoblock decorative elements -->
                                <div class="relative flex justify-center items-center flex-col w-1/3">
                                  <!-- bg -->
                                    <img decoding="async"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/monoblock/<?= htmlspecialchars($formattedVpnStatus['monoblock_image']['layout_bg']) ?>"
                                        alt="monoblock_top" title="monoblock_top" loading="lazy"
                                        class="z-20 w-full absolute z-10">
                                  <!-- spin -->
                                    <img decoding="async"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/monoblock/<?= htmlspecialchars($formattedVpnStatus['monoblock_image']['layout_spin']) ?>"
                                        alt="monoblock_down" title="monoblock_down" loading="lazy"
                                        class="z-10 w-[70%] absolute z-20 animate-spin [animation-duration:10s]">
                                  <!-- center -->
                                    <img decoding="async"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/monoblock/<?= htmlspecialchars($formattedVpnStatus['monoblock_image']['layout_center']) ?>"
                                        alt="monoblock_down" title="monoblock_down" loading="lazy"
                                        class="z-10 w-[35%] absolute z-30">
                                </div>

                                <p
                                    class="text-[white] status-glow absolute text-xl font-medium bottom-10 px-6 py-3 rounded-full bg-white/[0.05] backdrop-blur-md ring-1 ring-white/[0.1]">
                                    <?= $t('status') ?>:
                                    <span
                                        class="<?= $formattedVpnStatus['status_class'] ?>"><?= $vpnStatusObj->getStatusText() ?></span>
                                </p>
                            </div>

                            <!-- BLOCK-2 => INFORMATION PANELS -->
                            <div class="glow-card flex-1 h-full max-w-[350px] p-6 rounded-2xl">
                                <ul class="flex flex-col gap-4 w-full text-xl">
                                    <!-- content 1 -->
                                    <li
                                        class="gradient-border flex p-3 justify-between items-center w-full rounded-xl hover:bg-white/[0.06] transition-all duration-300">
                                        <div class="text-gray-300 text-sm flex items-center gap-2">
                                            <?= $t('ping') ?>:
                                            <div class="relative group">
                                                <i class="fas fa-question-circle text-gray-400 text-xs cursor-help"></i>
                                                <div
                                                    class="absolute left-0 top-full mt-1 w-64 p-3 bg-gray-900/95 backdrop-blur-sm rounded-lg border border-gray-700/50 text-xs text-gray-300 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                                    <div class="font-semibold text-white mb-1"><?= $t('ping_status') ?></div>
                                                    <div class="space-y-1">
                                                        <div class="flex items-center gap-2">
                                                            <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                                                            <span><?= $t('ping_excellent') ?></span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                                            <span><?= $t('ping_good') ?></span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                                            <span><?= $t('ping_slow') ?></span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                                                            <span><?= $t('no_connection') ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 pt-2 border-t border-gray-700/50 text-gray-400">
                                                        <?= $t('ping_hint') ?>
                                                    </div>
                                                </div>
                                            </div>
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
                                        class="gradient-border flex p-3 justify-between items-center w-full rounded-xl hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-300 text-sm"><?= $t('protocol') ?>:</span>
                                        <span class="text-[white] text-base font-light"
                                            data-protocol><?= $formattedVpnStatus['protocol'] ?></span>
                                    </li>
                                    <!-- content 3 -->
                                    <li
                                        class="gradient-border flex p-3 justify-between items-center w-full rounded-xl hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-300 text-sm"><?= $t('ip_address') ?>:</span>
                                        <span class="text-[white] text-base font-light"
                                            data-ip><?= $formattedVpnStatus['ip_address'] ?></span>
                                    </li>
                                    <!-- content 4 -->
                                    <li
                                        class="gradient-border flex p-3 justify-between items-center w-full rounded-xl hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-300 text-sm"><?= $t('server') ?>:</span>
                                        <span class="text-emerald-300 text-sm font-light"
                                            data-server><?= $formattedVpnStatus['location'] ?></span>
                                    </li>
                                    <!-- content 5 -->
                                    <li
                                        class="gradient-border flex p-3 justify-between items-center w-full rounded-xl hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-300 text-sm"><?= $t('remaining') ?>:</span>
                                        <span class="text-emerald-300 text-sm font-light" data-server
                                            data-timeleft></span>
                                    </li>
                                </ul>

                                <!-- Action Buttons -->
                                <ul class="flex flex-col gap-3 mt-6">
                                    <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                                        <a href="/install" class="btn_install_tour">
                                            <li
                                                class="neon-btn elite-btn group relative w-full flex justify-between items-center p-4 rounded-xl cursor-pointer">
                                                <?php if ((new OS())->getOS()['os'] === 'Windows' || (new OS())->getOS()['os'] === 'macOS' || (new OS())->getOS()['os'] === 'Linux'): ?>
                                                    <img decoding="async" loading="lazy"
                                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/install_desktop.svg"
                                                        alt=""
                                                        class="h-6 opacity-70 group-hover:opacity-100 transition-opacity">
                                                <?php else: ?>
                                                    <img decoding="async"
                                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/install_mobile.svg"
                                                        alt="" loading="lazy"
                                                        class="h-6 opacity-70 group-hover:opacity-100 transition-opacity">
                                                <?php endif; ?>
                                                <div class="flex flex-col items-center justify-start">
                                                    <span
                                                        class="text-sm font-medium text-[white] text-center flex gap-2 tracking-wide"><?= $t('install_btn') ?>
                                                        <span class="text-emerald-300">VPN</span>
                                                    </span>
                                                </div>
                                                <img decoding="async" loading="lazy"
                                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow_white.svg"
                                                    alt="" loading="lazy"
                                                    class="h-6 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all">
                                            </li>
                                        </a>
                                    <?php else: ?>
                                        <a href="/pay" class="block w-full">
                                            <li
                                                class="elite-btn glow-card group relative w-full flex justify-between items-center p-4 rounded-xl cursor-pointer">
                                                <img decoding="async" loading="lazy"
                                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/buy.svg"
                                                    alt="buy" loading="lazy" decoding="async"
                                                    class="h-6 opacity-70 group-hover:opacity-100 transition-opacity">
                                                <div class="flex flex-col items-center justify-start">
                                                    <span
                                                        class="text-sm font-medium text-[white] text-center flex gap-2 tracking-wide"><?= $t('buy') ?>
                                                        <span class="text-emerald-300"><?= $t('subscription') ?></span></span>
                                                </div>
                                                <img decoding="async" loading="lazy"
                                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow_white.svg"
                                                    alt="" loading="lazy" decoding="async"
                                                    class="h-6 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all">
                                            </li>
                                        </a>
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
                                <h1 class="text-3xl font-bold">
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
                            <div class="glow-card relative flex items-center gap-6 p-6 rounded-2xl">
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
                                </div>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-4 gap-4">
                                <div
                                    class="glow-card flex flex-col gap-3 p-4 rounded-xl hover:bg-white/[0.06] transition-colors">
                                    <div class="flex items-center gap-2 text-green-400">
                                        <i class="fa fa-wifi text-lg"></i>
                                        <span class="text-sm font-medium">VPN</span>
                                    </div>
                                    <span
                                        class="text-[white] text-lg font-semibold"><?= $formattedUserProfile['subscription_status'] ?></span>
                                </div>
                                <div
                                    class="glow-card flex flex-col gap-3 p-4 rounded-xl hover:bg-white/[0.06] transition-colors">
                                    <div class="flex items-center gap-2 text-blue-400">
                                        <i class="fa fa-language text-lg"></i>
                                        <span class="text-sm font-medium"><?= $t('language') ?></span>
                                    </div>
                                    <span
                                        class="text-[white] text-lg font-semibold"><?= $formattedUserProfile['language'] ?></span>
                                </div>
                                <div
                                    class="glow-card flex flex-col gap-3 p-4 rounded-xl hover:bg-white/[0.06] transition-colors">
                                    <div class="flex items-center gap-2 text-purple-400">
                                        <i class="fa fa-server text-lg"></i>
                                        <span class="text-sm font-medium"><?= $t('remaining') ?></span>
                                    </div>
                                    <span class="text-[white] text-lg font-semibold" data-timeleft></span>
                                </div>
                                <div
                                    class="glow-card flex flex-col gap-3 p-4 rounded-xl hover:bg-white/[0.06] transition-colors">
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
                            <div class="flex flex-col gap-4">
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

                        <!-- Company Links & Logout -->
                        <div class="flex flex-col gap-4 mt-6">
                            <h3 class="text-xl font-semibold text-gray-300"><?= $t('company') ?></h3>
                            <div class="grid grid-cols-2 gap-4">
                                <a href="/about"
                                    class="glow-card flex items-center gap-4 p-4 rounded-xl hover:bg-white/[0.06] transition-colors group">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500/20 to-orange-600/20 flex items-center justify-center ring-1 ring-amber-400/30">
                                        <i class="fa-solid fa-building text-amber-400 text-xl"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[white] font-medium"><?= $t('about_title') ?></span>
                                        <span class="text-sm text-gray-400"><?= $t('our_story') ?></span>
                                    </div>
                                </a>
                                <a href="/requisites"
                                    class="glow-card flex items-center gap-4 p-4 rounded-xl hover:bg-white/[0.06] transition-colors group">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 flex items-center justify-center ring-1 ring-cyan-400/30">
                                        <i class="fa-solid fa-file-invoice text-cyan-400 text-xl"></i>
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
                                                                                <div
                                                                                    class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
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
                            <div
                                class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2 text-purple-400">
                                    <i class="fa fa-gift text-lg"></i>
                                    <span class="text-sm font-medium"><?= $t('my_bonus'); ?></span>
                                </div>
                                <span class="text-[white] text-xl font-semibold"><?= $user->getBonusPercent() ?>%</span>
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
                                    <span class="text-sm text-gray-400 mb-2"><?= $t('my_bonus'); ?></span>
                                    <span
                                        class="text-3xl font-bold text-green-400"><?= intval($user->getBonusPercent()) ?>%</span>
                                    <span class="text-xs text-gray-500 mt-1"><?= $t('days_for_buy'); ?></span>
                                </div>
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

                <div class="absolute inset-0 z-0 bg-gradient-to-br from-green-900/35 via-transparent to-emerald-900/48">
                </div>
                <!-- SECTION = MAIN -->
                <template data-section="main">
                <section
                    class="setka overflow-hidden relative flex flex-col justify-between py-[95px] box-border w-full min-h-[100dvh] p-10"
                    data-section="main">

                    <!-- backgound -->
                    <img decoding="async" loading="lazy"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/<?= htmlspecialchars($formattedVpnStatus['background_world']) ?>" alt="background"
                        class="absolute h-full opacity-20 -left-[3rem] right-0 top-0 bottom-0 mx-auto scale-[2.5] z-0"
                        loading="lazy">

                    <!-- Monoblock decorative elements -->
                    <div class="flex justify-center items-center flex-col max-h-[300px] max-w-[200px] m-auto">
                      <!-- bg -->
                        <img decoding="async" loading="lazy"
                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/monoblock/<?= htmlspecialchars($formattedVpnStatus['monoblock_image']['layout_bg']) ?>"
                            alt="monoblock_top" title="monoblock_top" loading="lazy"
                            class="z-20 w-[70%] absolute z-10">
                      <!-- spin -->
                        <img decoding="async" loading="lazy"
                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/monoblock/<?= htmlspecialchars($formattedVpnStatus['monoblock_image']['layout_spin']) ?>"
                            alt="monoblock_down" title="monoblock_down"
                            class="z-10 w-[50%] absolute z-20 animate-spin [animation-duration:10s]">
                      <!-- center -->
                        <img decoding="async" loading="lazy"
                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/monoblock/<?= htmlspecialchars($formattedVpnStatus['monoblock_image']['layout_center']) ?>"
                            alt="monoblock_down" title="monoblock_down"
                            class="z-10 w-[22%] absolute z-30">
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
                            <!-- block 2 -->
                            <li class="glow-card_mobile relative w-full p-[15px] bg-[rgb(255,255,255,0.1)] rounded-xl">
                                <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                                    <a href="/install" class="btn_install_tour z-10 flex justify-between items-center">
                                        <img data-theme-invert decoding="async" loading="lazy"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/install_mobile.svg"
                                            alt="" loading="lazy"
                                            class="rounded-md h-6 opacity-70 group-hover:opacity-100 transition-opacity">
                                        <div class="flex flex-col items-center justify-start text-lg text-white">
                                            <span class="z-10 uppercase text-center flex gap-2"><?= $t('install_btn') ?> <span
                                                    class="word_hidden">vpn</span>
                                            </span>
                                        </div>
                                        <img decoding="async" loading="lazy"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow.svg"
                                            alt="" loading="lazy" decoding="async" class="h-6 invert">
                                    </a>
                                <?php else: ?>
                                    <a href="/pay" class="z-10 flex justify-between items-center">
                                        <img decoding="async" loading="lazy"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/buy.svg"
                                            alt="" loading="lozy" decoding="async" class="h-6 invert">
                                        <div class="flex flex-col items-center justify-start text-lg text-white">
                                            <!-- no -->
                                            <span href="/pay" class="z-10 uppercase text-center flex gap-2"><?= $t('buy') ?> <span
                                                    class="word_hidden"><?= $t('subscription') ?></span>
                                            </span>
                                            <!-- yes -->
                                        </div>
                                        <img decoding="async" loading="lazy"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/arrow.svg"
                                            alt="" loading="lozy" decoding="async" class="h-6 invert">
                                    </a>
                                <?php endif; ?>
                            </li>
                            <!-- block 3 -->
                            <li class="relative w-full flex justify-between px-4 py-3 rounded-xl text-sm">
                                <!-- 1 -->
                                <div class="flex flex-col items-center justify-between gap-2">
                                    <img decoding="async" loading="lazy"
                                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/default/protocol.svg"
                                        alt="protocol" loading="lazy" class="h-6">
                                    <p class="text-[#93A7C8] font-bold">
                                        <?= htmlspecialchars($formattedVpnStatus['protocol'] ?: '—') ?>
                                    </p>
                                </div>
                                <!-- 2 -->
                                <div class="flex flex-col items-center justify-between gap-2">
                                    <p class="text-white text-lg"><?= $t('main') ?></p>
                                    <p class="text-[#93A7C8]">
                                        <?= htmlspecialchars($formattedVpnStatus['ip_address'] ?: '—') ?>
                                    </p>
                                </div>
                                <!-- 3 -->
                                <div class="flex flex-col items-center justify-between gap-2">
                                    <div class="flex gap-2 items-center justify-center h-8">
                                        <span
                                            class="<?= htmlspecialchars($formattedVpnStatus['ping_class']) ?> bg-current h-2 w-2 rounded-full aspect-square"></span>
                                        <span
                                            class="<?= htmlspecialchars($formattedVpnStatus['ping_class']) ?> bg-current h-2 w-2 rounded-full aspect-square"></span>
                                        <span
                                            class="<?= htmlspecialchars($formattedVpnStatus['ping_class']) ?> bg-current h-2 w-2 rounded-full aspect-square"></span>
                                    </div>
                                    <p class="text-[#93A7C8] font-bold">
                                        <?= htmlspecialchars($formattedVpnStatus['ping_label']) ?>
                                    </p>
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
                            <h1 class="text-2xl font-bold">
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
                            <div class="glow-card_mobile relative flex items-center gap-4 p-5 rounded-2xl">
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
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
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
                            <div class="mt-4 flex flex-col gap-4 mb-4">
                                <h4 class="text-white text-xl font-semibold"><?= $t('data') ?></h4>
                                <ul class="flex flex-col gap-2.5">
                                    <li class="glow-card_mobile flex p-4 justify-between items-center rounded-xl">
                                        <!-- info -->
                                        <div class="flex flex-col justify-center w-[150px] gap-1">
                                            <h4 class="text-white text-sm font-semibold"><?= $t('vpn_key') ?></h4>
                                            <code id="vpn-key"
                                                class="overflow-hidden h-8 break-all text-[12px] text-white/50">
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

                        <!-- Company Links & Logout -->
                        <div class="mt-6 flex flex-col gap-4">
                            <h4 class="text-white text-xl font-semibold"><?= $t('company') ?></h4>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="/about"
                                    class="glow-card_mobile flex flex-col items-center justify-center gap-2 p-4 rounded-xl">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500/20 to-orange-600/20 flex items-center justify-center">
                                        <i class="fa-solid fa-building text-amber-400 text-lg"></i>
                                    </div>
                                    <span class="text-white text-sm font-medium"><?= $t('about_title') ?></span>
                                </a>
                                <a href="/requisites"
                                    class="glow-card_mobile flex flex-col items-center justify-center gap-2 p-4 rounded-xl">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-600/20 flex items-center justify-center">
                                        <i class="fa-solid fa-file-invoice text-cyan-400 text-lg"></i>
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

                        <div class="grid grid-cols-2 gap-3">
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
                            <div
                                class="glow-card_mobile flex flex-col gap-2 p-4 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2 text-purple-400">
                                    <i class="fa fa-gift text-lg"></i>
                                    <span class="text-xs font-medium"><?= $t('bonus'); ?></span>
                                </div>
                                <span class="text-white text-sm font-semibold"><?= $user->getBonusPercent() ?>%</span>
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
                            </div>
                        </div>

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

        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/main/main.js" defer></script>
        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/theme/main.js" defer></script>
        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/lang/lang.js" defer></script>

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