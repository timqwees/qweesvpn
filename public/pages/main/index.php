<?php
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\Client\getUser;
use Setting\Route\Function\Controllers\language\Language;
use Setting\Route\Function\Controllers\vpn\VpnStatus;
use Setting\Route\Function\Controllers\profile\Profile;
use Setting\Route\Function\Controllers\system\SystemInfo;

Auth::auth();
$user = new getUser();

// язык
$currentLanguage = Language::getCurrent();
$translations = Language::getTranslations($currentLanguage);

// Хелпер для короткого доступа к переводам
$t = fn(string $key): string => $translations[$key] ?? $key;

// Получаем реальные данные через новые классы
$vpnStatusObj = new VpnStatus();
$profileObj = new Profile();
$usageStats = $vpnStatusObj->getUsageStats();

// Форматируем данные для вывода (используем стрелочные геттеры)
$pingSt = $vpnStatusObj->getPingStatus();
$formattedVpnStatus = [
    'status_text' => $t($vpnStatusObj->getStatus() === 'active' ? 'active' : 'inactive'),
    'status_class' => $vpnStatusObj->getStatus() === 'active' ? 'text-green-400' : 'text-red-400',
    'ping_ms' => $vpnStatusObj->getPingMs(),
    'ping_label' => $vpnStatusObj->getPingMs() !== null
        ? $vpnStatusObj->getPingMs() . ' ms'
        : '—',
    'ping_status' => $pingSt,
    'ping_class' => $pingSt === 'good' ? 'text-green-400' : ($pingSt === 'inactive' ? 'text-red-400' : 'text-gray-400'),
    'ping_icon' => $pingSt === 'good' ? 'fa-arrow-up' : ($pingSt === 'inactive' ? 'fa-arrow-down' : 'fa-minus'),
    'protocol' => $vpnStatusObj->getProtocol(),
    'ip_address' => $vpnStatusObj->getIpAddress(),
    'location' => $vpnStatusObj->getLocation(),
    'monoblock_image' => $vpnStatusObj->getStatus() === 'active' ? 'on_top2.svg' : 'off_top2.svg',
    'monoblock_class' => $vpnStatusObj->getStatus() === 'active' ? 'animation_monoblock_on' : 'animation_monoblock_off'
];

$formattedUserProfile = [
    'full_name' => trim($user->getFistName() . ' ' . $user->getLastName()) ?: 'Пользователь',
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
?>
<!DOCTYPE html>
<html lang="<?= $currentLanguage ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $t('profile') ?></title>

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
    <script src="https://cdn.tailwindcss.com" defer></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" defer></script>

    <!-- Noscript fallback -->
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/noscript.css">
    </noscript>
</head>

<body class="bg-black bg-no-repeat flex item-center w-full overflow-x-hidden">
    <div class="min-h-screen flex flex-col w-full">

        <!-- navbar top -->
        <header class="fixed z-50 left-0 top-2 right-0 h-16 px-6 sm:hidden flex items-center justify-between">
            <!-- refresh -->
            <i class="fa fa-refresh text-white"></i>
            <!-- logo -->
            <div class="flex items-center gap-2">
                <img data-theme-invert class=" w-auto h-7 object-contain"
                    src="/public/assets/images/icons/logo/qweesvpn.svg" alt="qweesvpn">
                <h2 class="text-white text-xl font-[qwees-poppins-medium] tracking-wider">QWEES <span
                        class="text-green-400">VPN</span></h2>
            </div>
            <!-- version -->
            <span class="text-white text-sm" data-version><?= $formattedSystemInfo['version'] ?></span>
        </header>

        <main class="flex sm:my-2 w-full h-full">

            <!-- ################# MENU DESCKTOP ####################-->
            <aside class="hidden h-full sm:block min-w-[300px] z-20">
                <div class="relative sm:text-sm sm:leading-6 my-8">
                    <ul class="fixed flex flex-col gap-6">

                        <li class="flex h-16 gap-4 items-center justify-center">
                            <img data-theme-invert class="w-auto h-12 object-contain"
                                src="public/assets/images/icons/logo/qweesvpn.svg" alt="qweesvpn">
                            <h2 class="text-white text-3xl font-[qwees-urbanist-medium] tracking-wider">QWEES <span
                                    class="text-green-400">VPN</span></h2>
                        </li>

                        <!-- Основные ссылки -->
                        <ul class="desktop list-none fle fle-col mr-4 w-full">
                            <!-- home -->
                            <li class="bg_active relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                                data-toggle-section="main">
                                <span></span>
                                <span class="pl-10 text-xl text-white flex items-center gap-4">
                                    <img data-theme-invert loading="lazy"
                                        src="/public/assets/images/icons/services/menu/home.svg" alt="home"
                                        decoding="async">
                                    <?= $t('main') ?>
                                </span>
                            </li>
                            <!-- profile -->
                            <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                                data-toggle-section="profile">
                                <span></span>
                                <span class="pl-10 text-xl text-white flex items-center gap-4">
                                    <img data-theme-invert loading="lazy"
                                        src="/public/assets/images/icons/services/menu/profile.svg" alt="home"
                                        decoding="async">
                                    <?= $t('profile') ?>
                                </span>
                            </li>
                            <!-- setting -->
                            <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                                data-toggle-section="setting">
                                <span></span>
                                <span class="pl-10 text-xl text-white flex items-center gap-4">
                                    <img data-theme-invert loading="lazy"
                                        src="/public/assets/images/icons/services/menu/setting.svg" alt="home"
                                        decoding="async">
                                    <?= $t('settings') ?>
                                </span>
                            </li>
                            <!-- referal -->
                            <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                                data-toggle-section="referal">
                                <span></span>
                                <span class="pl-10 text-xl text-white flex items-center gap-4">
                                    <img data-theme-invert loading="lazy"
                                        src="/public/assets/images/icons/services/menu/refer.svg" alt="home"
                                        decoding="async">
                                    <?= $t('additional') ?>
                                </span>
                            </li>
                        </ul>
                    </ul>
                </div>
            </aside>

            <!-- ################# CONTENT DESCKTOP ####################-->
            <div class="hidden sm:block rounded-3xl w-full h-full text-white m-4 overflow-clip outer">

                <div class="card">
                    <div
                        class="absolute inset-0 z-0 bg-gradient-to-br from-green-900/15 via-transparent to-emerald-900/8">
                    </div>

                    <!-- SECTION = MAIN -->
                    <section
                        class="flex flex-col gap-6 box-border h-full w-full p-10 ml-2 relative z-10 rounded-3xl setka"
                        data-section="main">

                        <!-- оглавление DESCKTOP -->
                        <h1 class="text-3xl font-bold">
                            <span class="text-[white] loader-letter">Г</span>
                            <span class="text-[white] loader-letter">л</span>
                            <span class="text-[white] loader-letter">а</span>
                            <span class="text-[white] loader-letter">в</span>
                            <span class="text-[white] loader-letter">н</span>
                            <span class="text-[white] loader-letter">а</span>
                            <span class="text-[white] loader-letter">я</span>
                        </h1>

                        <!-- контент -->
                        <div class="flex items-start justify-center gap-6 w-full">
                            <!-- BLOCK-1 => DISPLAY STATUS -->
                            <div
                                class="glow-card relative min-h-[600px] flex flex-1 flex-col items-center justify-center rounded-2xl overflow-hidden">
                                <!-- backgound -->
                                <img src="/public/assets/images/background/world.svg" alt="background"
                                    class="absolute w-full h-full opacity-20" loading="lazy">

                                <!-- Monoblock decorative elements -->
                                <div class="flex justify-center items-center flex-col w-1/3">
                                    <?php if ($vpnStatusObj->getStatus() === 'active'): ?>
                                        <img src="/public/assets/images/icons/services/monoblock/on_top2.svg"
                                            alt="monoblock_part1" loading="lazy" class="z-20 w-full animation_monoblock_on">
                                        <img src="/public/assets/images/icons/services/monoblock/on_down.svg"
                                            alt="monoblock_part2" loading="lazy" class="-translate-y-[30%] z-10 w-full">
                                    <?php else: ?>
                                        <img src="/public/assets/images/icons/services/monoblock/off_top2.svg"
                                            alt="monoblock_part1" loading="lazy"
                                            class="z-20 w-full animation_monoblock_off">
                                        <img src="/public/assets/images/icons/services/monoblock/off_down.svg"
                                            alt="monoblock_part2" loading="lazy" class="-translate-y-[30%] z-10 w-full">
                                    <?php endif; ?>
                                </div>

                                <p
                                    class="text-[white] status-glow absolute text-xl font-medium bottom-10 px-6 py-3 rounded-full bg-white/[0.05] backdrop-blur-md ring-1 ring-white/[0.1]">
                                    Статус:
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
                                        <span class="text-gray-300 text-sm"><?= $t('ping') ?>:</span>
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
                                </ul>

                                <!-- Action Buttons -->
                                <ul class="flex flex-col gap-3 mt-6">
                                    <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                                        <li
                                            class="neon-btn elite-btn group relative w-full flex justify-between items-center p-4 rounded-xl cursor-pointer">
                                            <img src="/public/assets/images/icons/services/default/download.svg" alt=""
                                                loading="lazy" decoding="async"
                                                class="invert opacity-70 group-hover:opacity-100 transition-opacity">
                                            <div class="flex flex-col items-center justify-start">
                                                <a href="/install"
                                                    class="text-sm font-medium text-emerald-100 text-center flex gap-2 tracking-wide">Установить
                                                    <span class="text-emerald-300">VPN</span></a>
                                            </div>
                                            <img src="/public/assets/images/icons/services/default/arrow.svg" alt=""
                                                loading="lazy" decoding="async"
                                                class="invert opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all">
                                        </li>
                                    <?php else: ?>
                                        <a href="/pay" class="block w-full">
                                            <li
                                                class="elite-btn glow-card group relative w-full flex justify-between items-center p-4 rounded-xl cursor-pointer">
                                                <img src="/public/assets/images/icons/services/default/buy_white.svg"
                                                    alt="buy" loading="lazy" decoding="async"
                                                    class="opacity-70 group-hover:opacity-100 transition-opacity">
                                                <div class="flex flex-col items-center justify-start">
                                                    <span
                                                        class="text-sm font-medium text-[white] text-center flex gap-2 tracking-wide">Купить
                                                        <span class="text-emerald-300">подписку</span></span>
                                                </div>
                                                <img src="/public/assets/images/icons/services/default/arrow_white.svg"
                                                    alt="" loading="lazy" decoding="async"
                                                    class="opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all">
                                            </li>
                                        </a>
                                    <?php endif; ?>
                                </ul>
                            </div>

                        </div>

                    </section>

                    <!-- SECTION = PROFILE -->
                    <section
                        class="hidden flex-col gap-8 box-border h-full w-full p-10 ml-2 relative z-10 rounded-3xl setka"
                        data-section="profile">

                        <!-- Header Card -->
                        <div class="flex flex-col gap-6">
                            <h1 class="text-3xl font-bold">
                                <span class="loader-letter text-[white]">П</span>
                                <span class="loader-letter text-[white]">р</span>
                                <span class="loader-letter text-[white]">о</span>
                                <span class="loader-letter text-[white]">ф</span>
                                <span class="loader-letter text-[white]">и</span>
                                <span class="loader-letter text-[white]">л</span>
                                <span class="loader-letter text-[white]">ь</span>
                            </h1>

                            <!-- Profile Hero Card -->
                            <div class="glow-card relative flex items-center gap-6 p-6 rounded-2xl">
                                <div class="relative">
                                    <img src="/public/assets/images/icons/services/avatar/1.png" alt="avatar"
                                        class="rounded-2xl w-20 h-20 ring-2 ring-white/10">
                                    <div
                                        class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full <?= $user->getStatus() === 'on' ? 'bg-green-400' : 'bg-red-400' ?> ring-2 ring-black">
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
                                    <span
                                        class="text-[white] text-lg font-semibold"><?= $formattedUserProfile['days_left'] ?>
                                        <?= $t('days') ?></span>
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
                                <h3 class="text-xl font-semibold text-gray-300 mt-4">Данные подписки</h3>
                                <div class="glow-card relative z-20 flex items-center gap-4 p-5 rounded-xl">
                                    <div class="flex-1 flex flex-col gap-2">
                                        <label class="text-sm text-gray-400 font-medium">VPN ключ</label>
                                        <code id="vpn-key-desktop"
                                            class="text-sm text-white/70 bg-black/20 px-3 py-2 rounded-lg break-all">
                                                                                                                            <?= htmlspecialchars($user->getSubscription()) ?>
                                                                                                                                            </code>
                                        </div>
                                        <div class="flex gap-2 relative z-30">
                                            <button onclick="copyVpnKey()" title="Копировать"
                                                class="p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors group cursor-pointer">
                                                <i class="fa fa-copy text-gray-400 group-hover:text-white"></i>
                                            </button>
                                            <button onclick="deleteSubscription()" title="Удалить"
                                                class="p-3 rounded-lg bg-red-500/10 hover:bg-red-500/20 transition-colors group cursor-pointer">
                                                <i class="fa fa-trash text-red-400 group-hover:text-red-300"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                        <?php endif; ?>

                    </section>

                    <!-- SECTION = SETTING -->
                    <section
                        class="hidden flex-col gap-8 box-border h-full w-full p-10 ml-2 relative z-10 rounded-3xl setka"
                        data-section="setting">

                        <!-- Header -->
                        <h1 class="text-3xl font-bold">
                            <span class="loader-letter text-[white]">Н</span>
                            <span class="loader-letter text-[white]">а</span>
                            <span class="loader-letter text-[white]">с</span>
                            <span class="loader-letter text-[white]">т</span>
                            <span class="loader-letter text-[white]">р</span>
                            <span class="loader-letter text-[white]">о</span>
                            <span class="loader-letter text-[white]">й</span>
                            <span class="loader-letter text-[white]">к</span>
                            <span class="loader-letter text-[white]">и</span>
                        </h1>

                        <!-- App Settings -->
                        <div class="flex flex-col gap-4 pt-6">
                            <h3 class="text-lg font-semibold text-gray-300">Приложение</h3>
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
                                            <span class="text-[white] font-medium">Светлая тема</span>
                                            <span class="text-sm text-gray-400">Переключить оформление</span>
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
                            <h3 class="text-lg font-semibold text-gray-300">Конфиденциальность</h3>
                            <div class="flex flex-col gap-2">
                                <a href="/"
                                    class="glow-card flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors group">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                            <i class="fa fa-credit-card text-purple-400 text-lg"></i>
                                        </div>
                                        <span class="text-[white] font-medium">Автооплата</span>
                                    </div>
                                    <i
                                        class="fa fa-angle-right text-gray-400 group-hover:text-white group-hover:translate-x-1 transition-all"></i>
                                </a>

                                <button data-toggle-modal="politic"
                                    class="glow-card flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.06] transition-colors group text-left">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                            <i class="fa fa-shield-alt text-emerald-400 text-lg"></i>
                                        </div>
                                        <span class="text-[white] font-medium">Политика конфиденциальности</span>
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
                                        <span class="text-[white] font-medium">Пользовательское соглашение</span>
                                    </div>
                                    <i
                                        class="fa fa-angle-right text-gray-400 group-hover:text-white group-hover:translate-x-1 transition-all"></i>
                                </button>
                            </div>
                        </div>

                    </section>

                    <!-- SECTION = REFER -->
                    <section
                        class="hidden flex-col gap-8 box-border h-full w-full p-10 ml-2 relative z-10 rounded-3xl setka"
                        data-section="referal">

                        <!-- Header -->
                        <h1 class="text-3xl font-bold">
                            <span class="loader-letter text-[white]">Р</span>
                            <span class="loader-letter text-[white]">е</span>
                            <span class="loader-letter text-[white]">ф</span>
                            <span class="loader-letter text-[white]">е</span>
                            <span class="loader-letter text-[white]">р</span>
                            <span class="loader-letter text-[white]">а</span>
                            <span class="loader-letter text-[white]">л</span>
                            <span class="loader-letter text-[white]">ы</span>
                        </h1>

                        <!-- Stats Overview -->
                        <div class="grid grid-cols-3 gap-4 pt-6">
                            <div
                                class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2 text-emerald-400">
                                    <i class="fa fa-signal text-lg"></i>
                                    <span class="text-sm font-medium">Статус</span>
                                </div>
                                <span
                                    class="text-[white] text-xl font-semibold"><?= $formattedUserProfile['subscription_status'] ?></span>
                            </div>
                            <div
                                class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2 text-blue-400">
                                    <i class="fa fa-users text-lg"></i>
                                    <span class="text-sm font-medium">Рефералы</span>
                                </div>
                                <span class="text-[white] text-xl font-semibold"><?= $user->getReferCount() ?></span>
                            </div>
                            <div
                                class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2">
                                    <i class="fa fa-percent text-green-400"></i>
                                    <span class="text-sm font-medium">Скидка</span>
                                </div>
                                <span
                                    class="text-[white] text-xl font-semibold"><?= $user->getDiscountPercent() ?>%</span>
                            </div>
                            <div
                                class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08] hover:bg-white/[0.06] transition-colors">
                                <div class="flex items-center gap-2 text-purple-400">
                                    <i class="fa fa-gift text-lg"></i>
                                    <span class="text-sm font-medium">Бонус</span>
                                </div>
                                <span class="text-[white] text-xl font-semibold"><?= $user->getBonusPercent() ?>%</span>
                            </div>
                        </div>

                        <!-- Referral Link Cards -->
                        <div class="flex flex-col gap-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-300">Ваша реферальная ссылка</h3>

                            <!-- Refer Code -->
                            <div
                                class="flex items-center gap-4 p-5 rounded-xl bg-white/[0.03] shadow-[0_4px_16px_rgba(0,0,0,0.2)] ring-1 ring-white/[0.08]">
                                <div
                                    class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                                    <i class="fa fa-ticket text-emerald-400 text-xl"></i>
                                </div>
                                <div class="flex-1 flex flex-col gap-1 min-w-0">
                                    <label class="text-sm text-gray-400">Ваш код</label>
                                    <code
                                        class="text-[white] text-lg font-semibold truncate"><?= htmlspecialchars($user->getMyRefer()) ?></code>
                                </div>
                                <button
                                    onclick="copyToClipboard('<?= htmlspecialchars($user->getMyRefer()) ?>', 'Реферальный код')"
                                    title="Копировать код"
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
                                    <label class="text-sm text-gray-400">Полная ссылка</label>
                                    <code
                                        class="text-[white] text-xs truncate"><?= htmlspecialchars($user->getMyRefer() ? 'https://' . $_SERVER['HTTP_HOST'] . '/reflink=' . $user->getMyRefer() : '') ?></code>
                                </div>
                                <button
                                    onclick="copyToClipboard('<?= htmlspecialchars($user->getMyRefer() ? 'https://' . $_SERVER['HTTP_HOST'] . '/reflink=' . $user->getMyRefer() : '') ?>', 'Реферальная ссылка')"
                                    title="Копировать ссылку"
                                    class="p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors group shrink-0 cursor-pointer">
                                    <i class="fa fa-copy text-gray-400 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Detailed Stats -->
                        <div class="flex flex-col gap-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-300">Статистика</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    class="flex flex-col items-center p-6 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08]">
                                    <span class="text-sm text-gray-400 mb-2">Приглашено</span>
                                    <span
                                        class="text-3xl font-bold text-green-400"><?= intval($user->getReferCount()) ?></span>
                                    <span class="text-xs text-gray-500 mt-1">человек</span>
                                </div>
                                <div
                                    class="flex flex-col items-center p-6 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08]">
                                    <span class="text-sm text-gray-400 mb-2">Бонус</span>
                                    <span
                                        class="text-3xl font-bold text-green-400"><?= intval($user->getBonusPercent()) ?>%</span>
                                    <span class="text-xs text-gray-500 mt-1">дней за покупку</span>
                                </div>
                            </div>
                        </div>

                        <!-- Referrer Info or Enter Code -->
                        <?php if (!empty($user->getRefer())): ?>
                                <div class="flex flex-col gap-4 mt-4">
                                    <h3 class="text-lg font-semibold text-gray-300">Вы приглашены</h3>
                                    <div class="flex flex-col gap-3 p-5 rounded-xl bg-white/[0.03] ring-1 ring-white/[0.08]">
                                        <div class="flex justify-between items-center py-2 border-b border-white/5">
                                            <span class="text-sm text-gray-400">Пригласил</span>
                                            <span
                                                class="font-medium"><?= htmlspecialchars(Profile::getReferrerNameStatic($user->getRefer()) ?: 'Неизвестно') ?></span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-white/5">
                                            <span class="text-sm text-gray-400">Код</span>
                                            <span
                                                class="font-mono text-green-400"><?= htmlspecialchars($user->getRefer()) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-sm text-gray-400">Ваша скидка</span>
                                            <span
                                                class="font-bold text-green-400">-<?= intval($user->getDiscountPercent()) ?>%</span>
                                        </div>
                                    </div>
                                </div>
                        <?php else: ?>
                                <div class="flex flex-col gap-4 mt-4">
                                    <h3 class="text-lg font-semibold text-gray-300">Ввести реферальный код</h3>
                                    <div
                                        class="flex flex-col gap-4 p-5 rounded-xl bg-white/[0.03] shadow-[0_4px_16px_rgba(0,0,0,0.2)] ring-1 ring-white/[0.08]">
                                        <div class="flex flex-col gap-2">
                                            <label class="text-sm text-gray-400">Код реферала</label>
                                            <input type="text" id="referral-code-input"
                                                class="text-[white] w-full bg-black/20 border rounded-lg px-4 py-3 text-center text-xl tracking-widest uppercase placeholder:text-white/20 focus:outline-none focus:border-green-400/50 focus:ring-2 focus:ring-green-400/20 transition-all"
                                                placeholder="XXXXXXX" maxlength="10">
                                        </div>
                                        <button onclick="activateReferralCode()" id="referral-activate-btn"
                                            class="w-full py-3 rounded-lg bg-gradient-to-r from-green-400 to-emerald-500 text-black font-semibold hover:from-green-300 hover:to-emerald-400 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                                            Использовать код
                                        </button>
                                    </div>
                                </div>
                        <?php endif; ?>

                    </section>

                </div>
            </div>

            <!-- ################# MENU MOBILE ####################-->
            <aside data-theme-invert
                class="sm:hidden z-50 fixed bottom-4 bg-[rgb(78,78,78,0.38)] left-4 right-4 mx-auto rounded-full px-6 py-2">
                <ul class="mobile flex justify-between items-center gap-4">
                    <li class="bg_active relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
                        data-toggle-section="main">
                        <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/home.svg"
                            alt="Домой" decoding="async">
                    </li>
                    <li class="relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
                        data-toggle-section="profile">
                        <img data-theme-invert loading="lazy"
                            src="/public/assets/images/icons/services/menu/profile.svg" alt="Профиль" decoding="async">
                    </li>
                    <li class="relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
                        data-toggle-section="setting">
                        <img data-theme-invert loading="lazy"
                            src="/public/assets/images/icons/services/menu/setting.svg" alt="Настройки"
                            decoding="async">
                    </li>
                    <li class="relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
                        data-toggle-section="referal">
                        <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/refer.svg"
                            alt="Дополнительное" decoding="async">
                    </li>
                </ul>
            </aside>
            <!-- ################# CONTENT MOBILE ####################-->
            <div class="sm:hidden w-full text-white">
                <!-- SECTION = MAIN -->
                <section
                    class="overflow-hidden relative flex flex-col justify-between py-[95px] box-border w-full min-h-[100dvh] p-10 bg-gradient-to-t from-black via-green-950/50 to-black"
                    data-section="main">

                    <!-- backgound -->
                    <img src="/public/assets/images/background/world.svg" alt="background"
                        class="absolute h-full opacity-20 -left-[3rem] right-0 top-0 bottom-0 mx-auto scale-[2.5] z-0"
                        loading="lazy">

                    <!-- Monoblock decorative elements -->
                    <div class="flex justify-center items-center flex-col max-h-[300px] max-w-[200px] m-auto">
                        <img src="/public/assets/images/icons/services/monoblock/off_top2.svg" alt="monoblock_part1"
                            loading="lazy" class="z-20 w-full animation_monoblock_off">
                        <img src="/public/assets/images/icons/services/monoblock/off_down.svg" alt="monoblock_part2"
                            loading="lazy" class="-translate-y-[30%] z-10 w-full">
                    </div>

                    <!-- information -->
                    <div class="z-10 w-full h-full">
                        <ul class="flex flex-col justify-between items-center gap-4 h-full">
                            <!-- block 1 -->
                            <li
                                class="relative w-full flex justify-between items-center p-[15px] bg-[rgb(255,255,255,0.1)] rounded-xl">
                                <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                                        <img src="/public/assets/images/icons/services/default/netherlands.svg" alt=""
                                            loading="lazy" decoding="async">
                                        <div class="flex flex-col items-center justify-start text-lg text-white">
                                            <p class="uppercase">netherlands</p>
                                            <p class="text-sm text-green-400">STATUS: CONNECTION</p>
                                        </div>
                                        <img src="/public/assets/images/icons/services/default/signal.svg" alt="" loading="lazy"
                                            decoding="async">
                                <?php else: ?>
                                        <img src="/public/assets/images/icons/services/default/netherlands.svg" alt=""
                                            loading="lozy" decoding="async">
                                        <div class="flex flex-col items-center justify-start text-lg text-white">
                                            <!-- no -->
                                            <p class="uppercase">vpn <span class="text-[#FF6378]">неактивен</span></p>
                                            <!-- yes -->
                                        </div>
                                        <img src="/public/assets/images/icons/services/default/notnetwork.svg" alt=""
                                            loading="lozy" decoding="async">
                                <?php endif; ?>
                            </li>
                            <!-- block 2 -->
                            <li
                                class="relative w-full flex justify-between items-center p-[15px] bg-[rgb(255,255,255,0.1)] rounded-xl">
                                <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                                        <img src="/public/assets/images/icons/services/default/download.svg" alt=""
                                            loading="lazy" decoding="async" class="invert">
                                        <div class="flex flex-col items-center justify-start text-lg text-white">
                                            <a href="/install" class="uppercase text-center flex gap-2">установить <span
                                                    class="word_hidden">vpn</span>
                                            </a>
                                        </div>
                                        <img src="/public/assets/images/icons/services/default/arrow.svg" alt="" loading="lazy"
                                            decoding="async" class="invert">
                                <?php else: ?>
                                        <img src="/public/assets/images/icons/services/default/buy.svg" alt="" loading="lozy"
                                            decoding="async" class="invert">
                                        <div class="flex flex-col items-center justify-start text-lg text-white">
                                            <!-- no -->
                                            <a href="/pay" class="uppercase text-center flex gap-2">купить <span
                                                    class="word_hidden">подписку</span>
                                            </a>
                                            <!-- yes -->
                                        </div>
                                        <img src="/public/assets/images/icons/services/default/arrow.svg" alt="" loading="lozy"
                                            decoding="async" class="invert">
                                <?php endif; ?>
                            </li>
                            <!-- block 3 -->
                            <li class="relative w-full flex justify-between px-4 rounded-lg text-sm">
                                <!-- 1 -->
                                <div class="flex flex-col items-center justify-between gap-2">
                                    <img src="/public/assets/images/icons/services/default/protocol.svg" alt="protocol"
                                        loading="lazy">
                                    <p class="text-[#93A7C8] font-bold">gRPC</p>
                                </div>
                                <!-- 2 -->
                                <div class="flex flex-col items-center justify-between gap-2">
                                    <p class="text-lg">Ожидание...</p>
                                    <p class="text-[#93A7C8]">0.0.0.0</p>
                                </div>
                                <!-- 3 -->
                                <div class="flex flex-col items-center justify-between gap-2">
                                    <div class="flex gap-2 items-center justify-center h-8">
                                        <span class="bg-red-500 h-2 w-2 rounded-full aspect-square"></span>
                                        <span class="bg-red-500 h-2 w-2 rounded-full aspect-square"></span>
                                        <span class="bg-red-500 h-2 w-2 rounded-full aspect-square"></span>
                                    </div>
                                    <p class="text-[#93A7C8] font-bold">0ms</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                </section>
                <!-- SECTION = PROFILE -->
                <section
                    class="hidden overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black"
                    data-section="profile">
                    <!-- header logo -->
                    <div class="w-full h-[300px]">
                        <div
                            class="absolute flex flex-col gap-4 justify-center items-center w-full bg-[#0B0C1A] top-0 h-[300px] rounded-b-xl">
                            <!-- backgound -->
                            <img data-theme-invert src=" /public/assets/images/background/stars.svg" alt="background"
                                class="absolute h-full right-0 top-0 bottom-0 mx-auto animate-pulse duration-2000"
                                loading="lazy">

                            <img src="/public/assets/images/icons/services/avatar/1.png" alt="avatar"
                                class="rounded-xl w-18 h-18">
                            <h3 class="text-2xl font-bold" data-user-name>
                                <?= htmlspecialchars($formattedUserProfile['full_name']) ?>
                            </h3>

                            <!-- information block -->
                            <div class="absolute -bottom-[6.5rem] left-4 right-4 mx-auto bg-white rounded-2xl p-4">
                                <h3 class="text-lg font-semibold text-black">Статистика профиля</h3>
                                <ul class="grid grid-cols-2 grid-rows-2 gap-1 mt-4 justify-between">
                                    <!-- block 1 -->
                                    <li class="flex gap-2 items-center">
                                        <img data-theme-invert class="w-8"
                                            src=" /public/assets/images/icons/services/profile/wifi.svg" alt="icon_wifi"
                                            loading="lazy">
                                        <div class="flex flex-col justify-center">
                                            <h4 class="text-[16px] font-medium text-black translate-y-1">VPN
                                            </h4>
                                            <div class="text-[12px] text-gray-500" data-profile-status>
                                                <?= $formattedUserProfile['status_text'] ?>
                                            </div>
                                        </div>
                                    </li>
                                    <!-- block 2 -->
                                    <li class="flex gap-2 items-center justify-center">
                                        <img data-theme-invert class=" w-7"
                                            src=" /public/assets/images/icons/services/profile/fa_language.svg"
                                            alt="icon_wifi" loading="lazy">
                                        <div class="flex flex-col justify-center">
                                            <h4 class="text-[16px] font-medium text-black translate-y-1">Язык
                                            </h4>
                                            <div class="text-[12px] text-gray-500">Русский</div>
                                        </div>
                                    </li>
                                    <!-- block 3 -->
                                    <li class="flex gap-2 items-center">
                                        <img data-theme-invert class="w-7"
                                            src=" /public/assets/images/icons/services/profile/server.svg"
                                            alt="icon_wifi" loading="lazy">
                                        <div class="flex flex-col justify-center">
                                            <h4 class="text-[16px] font-medium text-black translate-y-1">Дней
                                            </h4>
                                            <div class="text-[12px] text-gray-500" data-days-left>
                                                <?= $formattedUserProfile['days_left'] ?>
                                            </div>
                                        </div>
                                    </li>
                                    <!-- block 4 -->
                                    <li class="flex gap-2 items-center justify-center">
                                        <img data-theme-invert class="w-7"
                                            src=" /public/assets/images/icons/services/profile/theme.svg"
                                            alt="icon_wifi" loading="lazy">
                                        <div class="flex flex-col justify-center ">
                                            <h4 class="text-[16px] font-medium text-black translate-y-1">Тема
                                            </h4>
                                            <div class="text-[12px] text-gray-500 theme-display" data-theme-text>
                                                <?= $formattedUserProfile['theme'] ?>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 pt-[7.5rem]">
                        <!-- data -->
                        <?php if ($user->getStatus() === 'on' && !empty($user->getSubscription())): ?>
                                <div class="flex flex-col gap-4 mb-4">
                                    <h4 class="text-xl font-semibold">Данные</h4>
                                    <ul class="flex flex-col gap-2.5">
                                        <li class="flex bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                                            <!-- info -->
                                            <div class="flex flex-col justify-center w-[150px] gap-1">
                                                <h4 class="text-sm font-semibold">VPN ключ</h4>
                                                <p id="vpn-key"
                                                    class="overflow-hidden h-8 break-all text-[12px] text-white/50 w-[150px]">
                                                    <?php echo htmlspecialchars($user->getSubscription()); ?>
                                                        </p>
                                                    </div>
                                                    <!-- button -->
                                                    <div class="flex gap-2 justify-end items-center">
                                                        <button onclick="copyVpnKey()"
                                                            class="text-lg text-gray-400 hover:text-white transition-colors"
                                                            title="Копировать ключ">
                                                            <i class="fa fa-copy"></i>
                                                        </button>
                                                        <button onclick="deleteSubscription()"
                                                            class="text-lg text-red-400 hover:text-red-300 transition-colors"
                                                            title="Удалить подписку">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                        <?php endif; ?>
                        <!-- setting -->
                        <!-- <div class="flex flex-col gap-4 mb-4">
              <h4 class="text-xl font-semibold">Настройки приложения</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex bg-[#2C2A2A] p-2 px-2.5 justify-between items-center rounded-2xl">
            <div class="flex justify-center items-center gap-2">
            <div class="flex justify-center items-center">
              <i class="fa fa-sun text-green-400 text-2xl -rotate-[15deg]"></i>
            </div>
            <div class="flex flex-col justify-center translate-y-1.5">
              <h4 class="text-sm">Светлая тема</h4>
              <p class="overflow-hidden h-8 break-all text-[12px] text-white/50 w-[150px]">
                Всегда будет включена
              </p>
            </div>
          </div>
            <div class="flex gap-2 justify-end items-center">
              <label class="inline-flex items-center me-5 cursor-pointer">
                <input type="checkbox" value="" class="sr-only peer" data-darkModeToggle>
                <div
                  class="relative w-9 h-5 bg-white/20 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-400 dark:peer-checked:bg-green-400">
                </div>
              </label>
            </div>
            </li>
            </ul>
          </div> -->
                    </div>

                </section>
                <!-- SECTION = SETTING -->
                <section
                    class="hidden px-6 pt-[5rem] overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black"
                    data-section="setting">

                    <!-- 1 -->
                    <div class="flex flex-col gap-4 mb-4">
                        <h4 class="text-xl font-semibold">Настройки приложения</h4>
                        <ul class="flex flex-col gap-2.5">
                            <!-- theme -->
                            <li class="flex bg-[#2C2A2A] p-2 px-2.5 justify-between items-center rounded-2xl">
                                <!-- info -->
                                <div class="flex justify-center items-center gap-3">
                                    <!-- icon -->
                                    <div class="flex justify-center items-center">
                                        <i class="fa fa-sun text-[#7DFF6F] text-2xl -rotate-[15deg]"></i>
                                    </div>
                                    <div class="flex flex-col justify-center translate-y-1.5">
                                        <h4 class="text-sm">Светлая тема</h4>
                                        <p class="overflow-hidden h-8 break-all text-[12px] text-white/50 w-[150px]">
                                            Всегда будет включена
                                        </p>
                                    </div>
                                </div>
                                <!-- button -->
                                <div class="flex gap-2 justify-end items-center">
                                    <label class="inline-flex items-center me-5 cursor-pointer">
                                        <input type="checkbox" value="" class="sr-only peer" data-darkModeToggle>
                                        <div
                                            class="relative w-9 h-5 bg-white/20 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-400 dark:peer-checked:bg-green-400">
                                        </div>
                                    </label>
                                </div>
                            </li>
                            <!-- language -->
                            <li class="flex bg-[#2C2A2A] p-2 px-2.5 justify-between items-center rounded-2xl">
                                <!-- info -->
                                <div class="flex justify-center items-center gap-3">
                                    <!-- icon -->
                                    <div class="flex justify-center items-center">
                                        <i class="fa fa-solid fa-language text-[#7DFF6F] text-2xl"></i>
                                    </div>
                                    <div class="flex flex-col justify-center translate-y-1.5">
                                        <h4 class="text-sm"><?= $t('language') ?> English</h4>
                                        <p class="overflow-hidden h-8 break-all text-[12px] text-white/50 w-[150px]">
                                            <?= $t('language_switch') ?>
                                        </p>
                                    </div>
                                </div>
                                <!-- button -->
                                <div class="flex gap-2 justify-end items-center">
                                    <label class="inline-flex items-center me-5 cursor-pointer">
                                        <input type="checkbox" value="rus" class="sr-only peer" data-language>
                                        <div
                                            class="relative w-9 h-5 bg-white/20 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-400 dark:peer-checked:bg-green-400">
                                        </div>
                                    </label>
                                </div>
                            </li>
                        </ul>
                    </div>


                    <!-- 2 -->
                    <div class="flex flex-col gap-4 mb-4">
                        <h4 class="text-xl font-semibold">Конфиденциальность</h4>
                        <div class="flex flex-col">
                            <!-- theme -->
                            <a href="/"
                                class="flex bg-[#2C2A2A] p-2 px-4 justify-between items-center border-b-[.5px] border-white/50 cursor-pointer">
                                <!-- info -->
                                <div class="flex justify-center items-center gap-3">
                                    <!-- icon -->
                                    <div class="flex justify-center items-center">
                                        <img class="text-2xl"
                                            src="/public/assets/images/icons/services/profile/shield.svg"
                                            alt="shield icon" loading="lazy">
                                    </div>
                                    <div class="flex flex-col justify-center">
                                        <h4 class="text-sm">Автооплата</h4>
                                    </div>
                                </div>
                                <!-- icon go -->
                                <div class="flex justify-center items-center">
                                    <i class="fa fa-solid fa-angle-right text-white/70 text-xl"></i>
                                </div>
                            </a>
                            <!-- theme -->
                            <button data-toggle-modal="politic"
                                class="flex bg-[#2C2A2A] p-2 px-4 justify-between items-center border-b-[.5px] border-white/50 cursor-pointer">
                                <!-- info -->
                                <div class="flex justify-center items-center gap-3">
                                    <!-- icon -->
                                    <div class="flex justify-center items-center">
                                        <img class="text-2xl"
                                            src="/public/assets/images/icons/services/profile/shield.svg"
                                            alt="shield icon" loading="lazy">
                                    </div>
                                    <div class="flex flex-col justify-center">
                                        <h4 class="text-sm">Политика конфиденциальности</h4>
                                    </div>
                                </div>
                                <!-- icon go -->
                                <div class="flex justify-center items-center">
                                    <i class="fa fa-solid fa-angle-right text-white/70 text-xl"></i>
                                </div>
                            </button>
                            <!-- theme -->
                            <button data-toggle-modal="access"
                                class="flex bg-[#2C2A2A] p-2 px-4 justify-between items-center cursor-pointer">
                                <!-- info -->
                                <div class="flex justify-center items-center gap-3">
                                    <!-- icon -->
                                    <div class="flex justify-center items-center">
                                        <img class="text-2xl"
                                            src="/public/assets/images/icons/services/profile/shield.svg"
                                            alt="shield icon" loading="lazy">
                                    </div>
                                    <div class="flex flex-col justify-center">
                                        <h4 class="text-sm">Пользовательское соглашение</h4>
                                    </div>
                                </div>
                                <!-- icon go -->
                                <div class="flex justify-center items-center">
                                    <i class="fa fa-solid fa-angle-right text-white/70 text-xl"></i>
                                </div>
                            </button>

                </section>
                <!-- SECTION = REFER -->
                <section
                    class="hidden overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black"
                    data-section="referal">
                    <!-- header logo -->
                    <div class="w-full h-[300px]">
                        <div
                            class="absolute flex flex-col gap-4 justify-center items-center w-full bg-[#0B0C1A] top-0 h-[300px] rounded-b-xl">
                            <!-- backgound -->
                            <img data-theme-invert src=" /public/assets/images/background/stars.svg" alt="background"
                                class="absolute h-full right-0 top-0 bottom-0 mx-auto animate-pulse duration-2000"
                                loading="lazy">

                            <img src="/public/assets/images/icons/services/avatar/1.png" alt="avatar"
                                class="rounded-xl w-18 h-18">
                            <h3 class="text-2xl font-bold" data-user-name>
                                <?= $formattedUserProfile['full_name'] ?>
                            </h3>

                            <!-- information block -->
                            <div class="absolute -bottom-[6.5rem] left-4 right-4 mx-auto bg-white rounded-2xl p-4">
                                <h3 class="text-lg font-semibold text-black">Статистика профиля</h3>
                                <ul class="grid grid-cols-2 grid-rows-2 gap-1 mt-4 justify-between">
                                    <!-- block 1 -->
                                    <li class="flex gap-2 items-center">
                                        <img data-theme-invert class="w-8"
                                            src=" /public/assets/images/icons/services/profile/wifi.svg" alt="icon_wifi"
                                            loading="lazy">
                                        <div class="flex flex-col justify-center">
                                            <h4 class="text-[16px] font-medium text-black translate-y-1">Статус
                                            </h4>
                                            <div class="text-[12px] text-gray-400" data-profile-status>
                                                <?= $formattedUserProfile['status_text'] ?>
                                            </div>
                                        </div>
                                    </li>
                                    <!-- block 2 -->
                                    <li class="flex gap-2 items-center justify-center">
                                        <img data-theme-invert class=" w-7"
                                            src=" /public/assets/images/icons/services/profile/piople.svg"
                                            alt="icon_wifi" loading="lazy">
                                        <div class="flex flex-col justify-center">
                                            <h4 class="text-[16px] font-medium text-black translate-y-1">
                                                Рефералы</h4>
                                            <div class="text-[12px] text-gray-400">
                                                <?= $formattedUserProfile['refer_count'] ?> человек
                                            </div>
                                        </div>
                                    </li>
                                    <!-- block 3 -->
                                    <li class="flex gap-2 items-center">
                                        <img data-theme-invert class="w-7"
                                            src=" /public/assets/images/icons/services/profile/circle.svg"
                                            alt="icon_wifi" loading="lazy">
                                        <div class="flex flex-col justify-center">
                                            <h4 class="text-[16px] font-medium text-black translate-y-1">Скидка
                                            </h4>
                                            <div class="text-[12px] text-gray-400">
                                                <?= $formattedUserProfile['discount_percent'] ?>%
                                            </div>
                                        </div>
                                    </li>
                                    <!-- block 4 - Бонус -->
                                    <li class="flex gap-2 items-center justify-center">
                                        <img data-theme-invert class="w-7"
                                            src=" /public/assets/images/icons/services/profile/circle.svg"
                                            alt="icon_bonus" loading="lazy">
                                        <div class="flex flex-col justify-center">
                                            <h4 class="text-[16px] font-medium text-black translate-y-1">
                                                Бонус</h4>
                                            <div class="text-[12px] text-gray-400">
                                                <?= $formattedUserProfile['bonus_percent'] ?>%
                                            </div>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 pt-[7.5rem]">
                        <!-- data -->
                        <div class="flex flex-col gap-3 mb-3">
                            <h4 class="text-lg font-semibold">Мои реферальные даные</h4>
                            <ul class="flex flex-col gap-2.5">
                                <li class="flex bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                            </ul>
                        </div>
                        <!-- input code -->
                        <div class="flex flex-col gap-3 mb-3">
                            <h4 class="text-lg font-semibold">Вставить реферальный код</h4>
                            <ul class="flex flex-col gap-2.5">
                                <li
                                    class="flex gap-3 flex-col bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                                    <!-- info -->
                                    <div class="flex w-full flex-col justify-center gap-2.5">
                                        <h4 class="text-sm font-semibold">Введите 4 цифры реферала</h4>
                                        <input type="text"
                                            class="overflow-hidden bg-black h-8 break-all text-xl text-center text-white/50 py-6 uppercase px-2 flex items-cnter rounded-lg focus:outline-none"
                                            placeholder="qwees * * * * vpn" maxlength="4">
                                    </div>
                                    <!-- button -->
                                    <div class="flex w-full">
                                        <button
                                            class="bg-white w-full cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-3 py-2">
                                            Использовать
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <!-- data refers -->
                        <div class="flex flex-col gap-3 mb-3">
                            <h4 class="text-lg font-semibold">Вставить реферальный код</h4>
                            <ul class="grid grid-cols-2 grid-rows-2 bg-[#2C2A2A] rounded-xl gap-4 p-4">
                                <li class="flex gap-3 flex-col justify-between items-center">
                                    <!-- info -->
                                    <div class="flex w-full flex-col justify-center gap-2">
                                        <h4 class="text-sm font-semibold">Имя / Фамилия</h4>
                                        <input type="text"
                                            class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                                            placeholder="tim qwees" maxlength="4">
                                    </div>
                                </li>
                                <li class="flex gap-3 flex-col justify-between items-center">
                                    <!-- info -->
                                    <div class="flex w-full flex-col justify-center gap-2">
                                        <h4 class="text-sm font-semibold">Реферальный код</h4>
                                        <input type="text"
                                            class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                                            placeholder="qwees1234vpn" maxlength="4">
                                    </div>
                                </li>
                                <li class="flex gap-3 flex-col justify-between items-center">
                                    <!-- info -->
                                    <div class="flex w-full flex-col justify-center gap-2">
                                        <h4 class="text-sm font-semibold">Дата активации</h4>
                                        <input type="text"
                                            class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                                            placeholder="22.12.2006" maxlength="4">
                                    </div>
                                </li>
                                <li class="flex gap-3 flex-col justify-between items-center">
                                    <!-- info -->
                                    <div class="flex w-full flex-col justify-center gap-2">
                                        <h4 class="text-sm font-semibold">Вы получили</h4>
                                        <div type="text"
                                            class="overflow-hidden items-center bg-black gap-1.5 h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none">
                                            <?php if ($formattedUserProfile['discount_percent'] > 0): ?>
                                                            <font class="text-green-400">
                                                                -<?= $formattedUserProfile['discount_percent'] ?>%</font> скидка
                                            <?php else: ?>
                                                            <span>Нет скидки</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                    </div>

                </section>
            </div>
        </main>

        <!-- modal = Политика конфиденциальности -->
        <div data-modal="politic" class="modal-overlay hidden">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Политика конфиденциальности</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p><strong>Политика конфиденциальности QweesVPN</strong></p>
                    <hr class="my-4">

                    <p><strong>Дата вступления в силу:</strong> 26.03.2026</p>
                    <hr class="my-4">

                    <p><strong>1. Общие положения</strong></p>
                    <p>Настоящая Политика конфиденциальности описывает, какие данные собирает сервис QweesVPN и как они
                        используются.</p>
                    <p>Используя сервис, пользователь соглашается с данной Политикой.</p>
                    <hr class="my-4">

                    <p><strong>2. Какие данные мы собираем</strong></p>
                    <p>Мы можем собирать следующие данные:</p>
                    <p>- адрес электронной почты (при регистрации);</p>
                    <p>- технические данные устройства (тип устройства, версия ОС);</p>
                    <p>- данные об использовании сервиса (ошибки, сбои, диагностика).</p>
                    <hr class="my-4">

                    <p><strong>3. Какие данные мы НЕ собираем</strong></p>
                    <p>QweesVPN придерживается политики конфиденциальности и не собирает:</p>
                    <p>- историю посещенных сайтов;</p>
                    <p>- содержимое интернет-трафика;</p>
                    <p>- DNS-запросы пользователей;</p>
                    <p>- реальные IP-адреса (при использовании VPN-соединения).</p>
                    <hr class="my-4">

                    <p><strong>4. Цели обработки данных</strong></p>
                    <p>Собранные данные используются для:</p>
                    <p>- предоставления и улучшения сервиса;</p>
                    <p>- технической поддержки пользователей;</p>
                    <p>- обеспечения безопасности и предотвращения злоупотреблений.</p>
                    <hr class="my-4">

                    <p><strong>5. Передача данных третьим лицам</strong></p>
                    <p>Мы не продаем и не передаем персональные данные третьим лицам, за исключением случаев:</p>
                    <p>- требования законодательства;</p>
                    <p>- защиты прав и безопасности сервиса;</p>
                    <p>- обработки платежей через сторонние платежные системы.</p>
                    <hr class="my-4">

                    <p><strong>6. Хранение данных</strong></p>
                    <p>Данные хранятся только столько, сколько необходимо для работы сервиса.</p>
                    <p>Мы принимаем разумные меры для защиты информации от несанкционированного доступа.</p>
                    <hr class="my-4">

                    <p><strong>7. Права пользователя</strong></p>
                    <p>Пользователь имеет право:</p>
                    <p>- запросить доступ к своим данным;</p>
                    <p>- требовать исправления или удаления данных;</p>
                    <p>- отозвать согласие на обработку данных.</p>
                    <hr class="my-4">

                    <p><strong>8. Cookies</strong></p>
                    <p>Мы можем использовать cookies для улучшения работы сайта и сервиса.</p>
                    <p>Пользователь может отключить cookies в настройках браузера.</p>
                    <hr class="my-4">

                    <p><strong>9. Изменения политики</strong></p>
                    <p>QweesVPN может обновлять данную Политику.</p>
                    <p>Изменения вступают в силу с момента публикации.</p>
                    <hr class="my-4">

                    <p><strong>10. Контакты</strong></p>
                    <p>Email: timqwees@gmail.com</p>
                    <p>Сайт: qweesvpn.ru</p>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn-close">Закрыть</button>
                </div>
            </div>
        </div>

        <!-- modal = Пользовательское соглашение -->
        <div data-modal="access" class="modal-overlay hidden">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Пользовательское соглашение</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p><strong>Пользовательское соглашение QweesVPN</strong></p>
                    <hr class="my-4">

                    <p><strong>Дата вступления в силу:</strong> 26.03.2026</p>
                    <hr class="my-4">

                    <p><strong>1. Общие положения</strong></p>
                    <p>Настоящее Пользовательское соглашение регулирует отношения между сервисом QweesVPN и
                        пользователем.</p>
                    <p>Используя сервис, пользователь подтверждает согласие с условиями.</p>
                    <p>Если пользователь не согласен — он обязан прекратить использование.</p>
                    <hr class="my-4">

                    <p><strong>2. Описание услуги</strong></p>
                    <p>QweesVPN предоставляет услуги VPN, включая:</p>
                    <p>- шифрование интернет-трафика;</p>
                    <p>- защиту конфиденциальности;</p>
                    <p>- изменение IP-адреса.</p>
                    <p>Сервис предоставляется «как есть» без гарантий.</p>
                    <hr class="my-4">

                    <p><strong>3. Регистрация и доступ</strong></p>
                    <p>Для использования может потребоваться регистрация.</p>
                    <p>Пользователь обязан предоставлять достоверные данные и не передавать доступ третьим лицам.</p>
                    <p>Пользователь несет ответственность за действия в аккаунте.</p>
                    <hr class="my-4">

                    <p><strong>4. Допустимое использование</strong></p>
                    <p>Запрещено использовать сервис для:</p>
                    <p>- нарушения законодательства;</p>
                    <p>- распространения вредоносного ПО;</p>
                    <p>- атак (DDoS, brute-force и т.д.);</p>
                    <p>- спама и мошенничества.</p>
                    <p>При нарушении аккаунт может быть заблокирован.</p>
                    <hr class="my-4">

                    <p><strong>5. Конфиденциальность</strong></p>
                    <p>QweesVPN уважает конфиденциальность пользователей.</p>
                    <p>Мы можем собирать технические данные для работы сервиса.</p>
                    <p>Мы не храним историю посещений и содержимое трафика.</p>
                    <p>Данные могут быть раскрыты только по закону.</p>
                    <hr class="my-4">

                    <p><strong>6. Платежи и подписка</strong></p>
                    <p>Некоторые функции доступны по подписке.</p>
                    <p>Подписка может продлеваться автоматически.</p>
                    <p>Пользователь может отменить подписку.</p>
                    <p>Возврат средств осуществляется согласно политике возвратов.</p>
                    <hr class="my-4">

                    <p><strong>7. Ограничение ответственности</strong></p>
                    <p>QweesVPN не несет ответственности за:</p>
                    <p>- действия пользователей;</p>
                    <p>- потерю данных;</p>
                    <p>- сбои в работе сервиса.</p>
                    <p>Использование происходит на риск пользователя.</p>
                    <hr class="my-4">

                    <p><strong>8. Блокировка доступа</strong></p>
                    <p>Сервис имеет право ограничить или удалить аккаунт при нарушении условий.</p>
                    <p>Пользователь может прекратить использование в любое время.</p>
                    <hr class="my-4">

                    <p><strong>9. Изменения соглашения</strong></p>
                    <p>Сервис может обновлять условия в любое время.</p>
                    <p>Продолжение использования означает согласие с изменениями.</p>
                    <hr class="my-4">

                    <p><strong>10. Применимое право</strong></p>
                    <p>Соглашение регулируется законодательством [укажи страну].</p>
                    <hr class="my-4">

                    <p><strong>11. Контакты</strong></p>
                    <p>Email: timqwees@gmail.com</p>
                    <p>Сайт: qweesvpn.ru</p>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn-close">Закрыть</button>
                </div>
            </div>
            /div>
        </div>
        </section>

        <script src="/public/assets/scripts/main/main.js" defer></script>
        <script src="/public/assets/scripts/theme/main.js" defer></script>
        <script src="/public/assets/scripts/lang/lang.js" defer></script>

        <script defer>
            // Копирование VPN ключа
            function copyVpnKey() {
                const el = document.getElementById('vpn-key-desktop') || document.getElementById('vpn-key');
                const text = el?.textContent?.trim();
                text ? copyToClipboard(text, 'VPN ключ') : showNotification('VPN ключ не найден', 'error');
            }

            // Удаление подписки
            async function deleteSubscription() {
                if (!confirm('Вы уверены, что хотите удалить подписку?')) return;

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
                            showNotification('Подписка удалена', 'success');
                            setTimeout(() => location.reload(), 1500);
                            return;
                        }
                    }

                    // Проверяем разные варианты успешного ответа
                    const isOk = data.status === 'ok' || data.success === true || res.ok;
                    const isPartial = data.status === 'partial';

                    if (isOk || isPartial) {
                        showNotification(data.message || 'Подписка удалена', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(data.message || data.error || 'Ошибка удаления', 'error');
                    }
                } catch (e) {
                    showNotification('Ошибка сети', 'error');
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
                element.className = `px-6 py-3 rounded-lg text-white z-50 transform translate-x-full transition-transform duration-300 ${{success:'bg-green-500',error:'bg-red-500',info:'bg-blue-500'}[type]||'bg-blue-500'}`;
                element.innerHTML = '<i class="fa-solid fa-info-circle"></i> ' + msg;
                setTimeout(() => element.classList.remove('translate-x-full'), 100);
                setTimeout(() => element.classList.add('translate-x-full'), 4100);
                setTimeout(() => (element.remove(), container.children.length || container.remove()), 4400);
            }

            // Устанавливаем тему в localStorage из PHP при загрузке
            const currentThemeFromPHP = '<?= $formattedUserProfile['theme'] ?>';
            if (!localStorage.getItem('theme')) {
                localStorage.setItem('theme', currentThemeFromPHP);
            }

            // Обновление темы в статистике профиля из localStorage
            function updateThemeInProfile() {
                const currentTheme = localStorage.getItem('theme') || 'Темная';
                const themeElements = document.querySelectorAll('[data-theme-text]');

                themeElements.forEach(element => {
                    element.textContent = currentTheme;
                });

                // Также обновляем все элементы с классом .theme-display
                const themeDisplays = document.querySelectorAll('.theme-display');
                themeDisplays.forEach(element => {
                    element.textContent = currentTheme;
                });
            }

            // Вызываем при загрузке страницы
            document.addEventListener('DOMContentLoaded', updateThemeInProfile);

            // Вызываем при изменении темы
            const darkModeToggle = document.querySelector('[data-darkModeToggle]');
            if (darkModeToggle) {
                darkModeToggle.addEventListener('change', () => {
                    const newTheme = darkModeToggle.checked ? 'Светлая' : 'Темная';
                    localStorage.setItem('theme', newTheme);
                    updateThemeInProfile();
                });
            }
            // Универсальная функция копирования
            function copyToClipboard(text, label = 'Текст') {
                if (!text) {
                    showNotification('Нечего копировать', 'error');
                    return;
                }
                // Пробуем современный API (требует HTTPS)
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(() => {
                        showNotification(`${label} скопирован!`, 'success');
                    }).catch(err => {
                        console.error('Ошибка копирования:', err);
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
                    showNotification(`${label} скопирован!`, 'success');
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                    showNotification('Ошибка копирования', 'error');
                }
                document.body.removeChild(textarea);
            }

            // Активация реферального кода
            function activateReferralCode() {
                const codeInput = document.getElementById('referral-code-input');
                const btn = document.getElementById('referral-activate-btn');
                const code = codeInput ? codeInput.value.trim() : '';

                if (!code) {
                    showNotification('Пожалуйста, введите реферальный код', 'error');
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
                                    btn.textContent = 'Использовать';
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка:', error);
                            showNotification('Ошибка сервера при активации', 'error');
                            if (btn) {
                                btn.disabled = false;
                                btn.textContent = 'Использовать';
                            }
                        });
                }
            }

            // Enter key для активации реферального кода
            // Referral input enter key handler
            const referInput = document.getElementById('referral-code-input');
            if (referInput) {
                referInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        activateReferralCode();
                    }
                });
            }
        </script>
    </div>
</body>

</html>