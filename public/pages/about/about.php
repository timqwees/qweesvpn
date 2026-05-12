<?php
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Functions;
Auth::auth();
$site = Functions::site();
?>
<!DOCTYPE html>
<html lang="ru" class="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>О компании</title>

    <!-- Preload critical resources -->
    <link rel="preload" href="/public/assets/styles/style.css" as="style">
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

    <!-- Deferred scripts -->
    <script src="https://cdn.tailwindcss.com" defer></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" defer></script>
    <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/theme/main.js" defer></script>

    <!-- Noscript fallback -->
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/noscript.css">
    </noscript>
</head>

<body class="bg-black bg-no-repeat flex item-center w-full overflow-x-hidden">
    <div class="min-h-screen flex flex-col w-full">

        <!-- navbar top -->
        <header class="fixed z-50 left-0 top-2 right-0 h-16 px-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/" class="text-white hover:text-green-400 transition-colors">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <!-- logo -->
                <div class="flex items-center gap-2">
                    <img decoding="async" loading="lazy" class="bg-black/90 rounded-full p-1 w-auto h-7 object-contain"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                        alt="<?= htmlspecialchars($site['ООО']) ?>">
                    <h2 class="text-white text-xl font-[qwees-poppins-medium] tracking-wider">
                        Qwees<span class="text-green-400">VPN</span>
                    </h2>
                </div>
            </div>
            <!-- version -->
            <span class="text-white text-sm">v1.0.0</span>
        </header>

        <main class="flex sm:my-2 w-full h-full">
            <!-- ################# CONTENT DESCKTOP ####################-->
            <div class="hidden sm:block rounded-3xl w-full h-full text-white m-6 overflow-clip">

                <!-- Background gradient -->
                <div class="absolute inset-0 z-0 bg-gradient-to-br from-green-900/15 via-transparent to-emerald-900/8">
                </div>

                <section class="flex flex-col gap-8 box-border h-full w-full p-10 ml-2 relative z-10">
                    <!-- Header with export button -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-500/20 to-emerald-600/20 flex items-center justify-center ring-1 ring-green-400/30">
                                <i class="fa-solid fa-building text-2xl text-green-400"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white tracking-wide">О компании</h1>
                                <p class="text-gray-400 text-sm">
                                    <?= htmlspecialchars($site['ООО']) ?> — надежный сервис VPN
                                </p>
                            </div>
                        </div>
                        <a href="/export/pdf?type=about"
                            class="elite-btn glow-card group relative flex items-center gap-2 px-5 py-3 rounded-xl cursor-pointer transition-all duration-300 hover:scale-105">
                            <i class="fa-solid fa-file-pdf text-green-300 group-hover:text-white transition-colors"></i>
                            <span class="text-[white] font-medium">Экспорт PDF</span>
                        </a>
                    </div>

                    <!-- Content Cards Grid -->
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Mission Card -->
                        <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-green-500/5 via-transparent to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500/20 to-emerald-600/20 flex items-center justify-center ring-1 ring-green-400/30">
                                        <i class="fa-solid fa-rocket text-green-400"></i>
                                    </div>
                                    <h2 class="text-xl font-semibold text-white">Наша миссия</h2>
                                </div>
                                <p class="text-gray-400 leading-relaxed text-sm">
                                    <?= htmlspecialchars($site['ООО']) ?> — это надежный и безопасный сервис для защиты
                                    вашей приватности в
                                    интернете.
                                    Мы предоставляем высокоскоростные VPN-соединения с современными протоколами
                                    шифрования,
                                    гарантируя полную анонимность и безопасность ваших данных.
                                </p>
                            </div>
                        </div>

                        <!-- Security Card -->
                        <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-green-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500/20 to-green-600/20 flex items-center justify-center ring-1 ring-emerald-400/30">
                                        <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                                    </div>
                                    <h2 class="text-xl font-semibold text-white">Безопасность</h2>
                                </div>
                                <p class="text-gray-400 leading-relaxed text-sm">
                                    Мы используем передовые технологии шифрования AES-256 и протоколы OpenVPN,
                                    WireGuard,
                                    и Shadowsocks. Все наши серверы расположены в юрисдикциях с строгими законами
                                    о защите персональных данных.
                                </p>
                            </div>
                        </div>

                        <!-- Global Network Card -->
                        <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-green-500/5 via-transparent to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500/20 to-teal-600/20 flex items-center justify-center ring-1 ring-green-400/30">
                                        <i class="fa-solid fa-globe text-green-400"></i>
                                    </div>
                                    <h2 class="text-xl font-semibold text-white">Глобальная сеть</h2>
                                </div>
                                <p class="text-gray-400 leading-relaxed text-sm">
                                    Наш сервер расположен в Нидерландах, Амстердам — одном из лучших дата-центров
                                    Европы.
                                    Мы постоянно расширяем нашу инфраструктуру, чтобы обеспечить максимальную скорость
                                    и стабильность соединения для наших клиентов.
                                </p>
                            </div>
                        </div>

                        <!-- Support Card -->
                        <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-teal-500/5 via-transparent to-green-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500/20 to-green-600/20 flex items-center justify-center ring-1 ring-teal-400/30">
                                        <i class="fa-solid fa-headset text-teal-400"></i>
                                    </div>
                                    <h2 class="text-xl font-semibold text-white">Поддержка 24/7</h2>
                                </div>
                                <p class="text-gray-400 leading-relaxed text-sm">
                                    Наша команда технической поддержки готова помочь вам в любое время суток.
                                    Мы предлагаем быструю и профессиональную помощь на русском и английском языках.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Story Section - Full Width -->
                    <div class="glow-card relative p-8 rounded-2xl overflow-hidden mt-4">
                        <div class="absolute inset-0 bg-gradient-to-r from-amber-500/5 via-orange-500/5 to-red-500/5">
                        </div>
                        <div
                            class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-amber-400/10 to-red-500/10 rounded-full blur-3xl">
                        </div>

                        <div class="relative z-10">
                            <div class="flex items-start gap-6">
                                <div
                                    class="w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-500/30 to-red-600/30 flex items-center justify-center ring-2 ring-amber-400/40 shrink-0 p-3">
                                    <img src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                        alt="<?= htmlspecialchars($site['ООО']) ?>"
                                        class="w-full h-full object-contain ">
                                </div>
                                <div class="flex-1">
                                    <h2 class="text-2xl font-bold text-white mb-3 flex items-center gap-2">
                                        <span
                                            class="bg-gradient-to-r from-amber-400 to-red-400 bg-clip-text text-transparent">Ваш
                                            личный Орел в мире VPN</span>
                                        <i class="fa-solid fa-feather text-amber-400 text-lg"></i>
                                    </h2>
                                    <p class="text-gray-400 leading-relaxed mb-4 text-lg">
                                        <span class="text-amber-400 font-semibold">Представьте:</span> интернет без
                                        границ, без ограничений, без страха.
                                        Где бы вы ни были — вы свободны. Это не мечта. Это <span
                                            class="text-white font-bold">
                                            <?= htmlspecialchars($site['ООО']) ?>
                                        </span>.
                                    </p>
                                    <div class="grid grid-cols-3 gap-4 mt-4">
                                        <div class="flex items-center gap-2 bg-white/[0.05] p-3 rounded-xl">
                                            <i class="fa-solid fa-bolt text-amber-400 text-xl"></i>
                                            <div>
                                                <span class="text-white font-semibold text-sm block">Скорость
                                                    света</span>
                                                <span class="text-gray-400 text-xs">Никаких лагов</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 bg-white/[0.05] p-3 rounded-xl">
                                            <i class="fa-solid fa-shield-halved text-amber-400 text-xl"></i>
                                            <div>
                                                <span class="text-white font-semibold text-sm block">Армия защиты</span>
                                                <span class="text-gray-400 text-xs">AES-256 шифрование</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 bg-white/[0.05] p-3 rounded-xl">
                                            <i class="fa-solid fa-globe text-amber-400 text-xl"></i>
                                            <div>
                                                <span class="text-white font-semibold text-sm block">Нидерланды</span>
                                                <span class="text-gray-400 text-xs">Амстердам, NL</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Origin Story - How it all started -->
                    <div class="glow-card relative p-8 rounded-2xl overflow-hidden mt-4">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-purple-500/5 to-pink-500/5">
                        </div>
                        <div
                            class="absolute -bottom-10 -left-10 w-48 h-48 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-full blur-3xl">
                        </div>

                        <div class="relative z-10">
                            <div class="flex items-start gap-6">
                                <div
                                    class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500/30 to-purple-600/30 flex items-center justify-center ring-2 ring-blue-400/40 shrink-0">
                                    <i class="fa-solid fa-rocket text-4xl text-blue-400"></i>
                                </div>
                                <div class="flex-1">
                                    <h2 class="text-2xl font-bold text-white mb-3 flex items-center gap-2">
                                        <span
                                            class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Почему
                                            миллионы выбирают нас</span>
                                    </h2>
                                    <p class="text-gray-400 leading-relaxed mb-4 text-lg">
                                        <span class="text-blue-400 font-semibold">2026 год.</span> Санкции, блокировки,
                                        ограничения.
                                        Обычные VPN не справляются. Мы создали <span class="text-white font-bold">QWEES
                                            VPN</span>, который работает всегда.
                                    </p>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div
                                            class="flex items-center gap-3 bg-gradient-to-br from-white/[0.08] to-white/[0.03] p-4 rounded-xl border border-white/10">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-yellow-500/30 to-amber-600/30 flex items-center justify-center ring-1 ring-yellow-400/30">
                                                <i class="fa-solid fa-bolt text-yellow-400"></i>
                                            </div>
                                            <div>
                                                <span class="text-white font-semibold text-sm block">Мгновенный
                                                    старт</span>
                                                <span class="text-gray-400 text-xs">Подключение за 2 секунды</span>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 bg-gradient-to-br from-white/[0.08] to-white/[0.03] p-4 rounded-xl border border-white/10">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500/30 to-emerald-600/30 flex items-center justify-center ring-1 ring-green-400/30">
                                                <i class="fa-solid fa-infinity text-green-400"></i>
                                            </div>
                                            <div>
                                                <span class="text-white font-semibold text-sm block">Безлимитный
                                                    трафик</span>
                                                <span class="text-gray-400 text-xs">Качайте и смотрите сколько
                                                    хотите</span>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 bg-gradient-to-br from-white/[0.08] to-white/[0.03] p-4 rounded-xl border border-white/10">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500/30 to-cyan-600/30 flex items-center justify-center ring-1 ring-blue-400/30">
                                                <i class="fa-solid fa-shield-halved text-blue-400"></i>
                                            </div>
                                            <div>
                                                <span class="text-white font-semibold text-sm block">Военная
                                                    защита</span>
                                                <span class="text-gray-400 text-xs">АES-256 + протоколы нового
                                                    поколения</span>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 bg-gradient-to-br from-white/[0.08] to-white/[0.03] p-4 rounded-xl border border-white/10">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500/30 to-pink-600/30 flex items-center justify-center ring-1 ring-purple-400/30">
                                                <i class="fa-solid fa-headset text-purple-400"></i>
                                            </div>
                                            <div>
                                                <span class="text-white font-semibold text-sm block">Поддержка
                                                    24/7</span>
                                                <span class="text-gray-400 text-xs">Всегда на связи</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <!-- Creator Card -->
                        <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-purple-500/5 via-transparent to-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div
                                class="absolute -bottom-10 -right-10 w-32 h-32 bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-full blur-2xl">
                            </div>

                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500/30 to-pink-600/30 flex items-center justify-center ring-1 ring-purple-400/40 overflow-hidden">
                                        <img src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                            alt="<?= htmlspecialchars($site['контакты']['Директор']) ?>"
                                            class="w-8 h-8 object-contain">
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-white">Гений за продуктом</h2>
                                        <p class="text-purple-400 text-xs">Founder & Tech Visionary</p>
                                    </div>
                                </div>
                                <p class="text-gray-400 text-sm leading-relaxed mb-3">
                                    <span class="text-purple-400 font-bold">
                                        <?= htmlspecialchars($site['контакты']['Директор']) ?>
                                    </span> — разработчик, который
                                    разочаровался в существующих VPN
                                    и создал идеальный. <span class="text-pink-400 font-semibold">Его миссия</span> —
                                    дать каждому свободный интернет.
                                </p>
                                <div
                                    class="bg-gradient-to-r from-purple-500/10 to-pink-500/10 p-3 rounded-xl border-l-4 border-purple-400">
                                    <p class="text-gray-400 text-sm italic">
                                        "Я хотел VPN, который просто работает. Без лагов, без страха, без ограничений."
                                    </p>
                                </div>
                                <div class="flex items-center gap-4 pt-3 border-t border-white/10">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-sm font-bold">
                                        T</div>
                                    <span class="text-gray-400 text-xs">Архитектор свободы</span>
                                </div>
                            </div>
                        </div>

                        <!-- Studio Card -->
                        <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 via-transparent to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div
                                class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 rounded-full blur-2xl">
                            </div>

                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500/30 to-blue-600/30 flex items-center justify-center ring-1 ring-cyan-400/40 overflow-hidden p-2">
                                        <img src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                            alt="<?= htmlspecialchars($site['ООО']) ?>"
                                            class="w-full h-full object-contain ">
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-white">
                                            <?= htmlspecialchars($site['ООО']) ?> Studio
                                        </h2>
                                        <p class="text-cyan-400 text-xs"><?= htmlspecialchars($site['студия']) ?></p>
                                    </div>
                                </div>
                                <p class="text-gray-400 text-sm leading-relaxed mb-4">
                                    Элитная лаборатория, где рождаются <span
                                        class="text-cyan-400 font-semibold">технологии будущего</span>.
                                    Мы не следуем трендам — мы их создаем.
                                </p>
                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    <div class="text-center p-2 bg-white/[0.05] rounded-lg">
                                        <span class="text-cyan-400 font-bold text-lg">1</span>
                                        <span class="text-gray-400 text-xs block">Сервер</span>
                                        <span class="text-cyan-400/70 text-[10px] block">Нидерланды</span>
                                    </div>
                                    <div class="text-center p-2 bg-white/[0.05] rounded-lg">
                                        <span class="text-blue-400 font-bold text-lg">99.9%</span>
                                        <span class="text-gray-400 text-xs block">Uptime</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 pt-3 border-t border-white/10">
                                    <span
                                        class="px-3 py-1 rounded-full bg-gradient-to-r from-red-500/20 to-orange-500/20 text-red-400 text-xs border border-red-500/30">Премиум
                                        VPN</span>
                                    <span
                                        class="px-3 py-1 rounded-full bg-gradient-to-r from-cyan-500/20 to-blue-500/20 text-cyan-400 text-xs border border-cyan-500/30">Топ-1
                                        в 2026</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quote Section -->
                    <div class="relative mt-4 p-6 rounded-2xl overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-red-500/10">
                        </div>
                        <div class="absolute inset-0 border border-amber-400/20 rounded-2xl"></div>
                        <div class="relative z-10 text-center">
                            <div
                                class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-amber-500/30 to-red-600/30 flex items-center justify-center ring-2 ring-amber-400/40 p-3">
                                <img src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                    alt="<?= htmlspecialchars($site['ООО']) ?>" class="w-full h-full object-contain ">
                            </div>
                            <p class="text-2xl text-white font-light italic mb-2 leading-relaxed">
                                "Не позволяйте границам ограничивать <span class="text-amber-400 font-medium">вашу
                                    свободу</span>.
                                <br>С
                                <?= htmlspecialchars($site['ООО']) ?> <span class="text-white font-bold">весь мир</span>
                                у вас в кармане"
                            </p>
                            <span class="text-amber-400/60 text-sm">— Присоединяйтесь к революции
                                <?= htmlspecialchars($site['контакты']['Директор']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="grid grid-cols-4 gap-4 mt-4">
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.03]">
                            <span class="text-3xl font-bold text-green-400">NL</span>
                            <span class="text-gray-400 text-xs mt-1">Нидерланды</span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.03]">
                            <span class="text-3xl font-bold text-emerald-400">AES-256</span>
                            <span class="text-gray-400 text-xs mt-1">Шифрование</span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.03]">
                            <span class="text-3xl font-bold text-teal-400">24/7</span>
                            <span class="text-gray-400 text-xs mt-1">Поддержка</span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.03]">
                            <span class="text-3xl font-bold text-green-400">99.9%</span>
                            <span class="text-gray-400 text-xs mt-1">Uptime</span>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ################# CONTENT MOBILE ####################-->
            <div class="sm:hidden w-full text-white">
                <section
                    class="overflow-hidden relative flex flex-col gap-6 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950/30 to-black">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500/20 to-emerald-600/20 flex items-center justify-center ring-1 ring-green-400/30">
                                <i class="fa-solid fa-building text-lg text-green-400"></i>
                            </div>
                            <h1 class="text-xl font-bold text-white">О компании</h1>
                        </div>
                        <a href="/export/pdf?type=about"
                            class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-green-500/80 to-green-600/80 text-[white] text-sm font-medium rounded-lg">
                            <i class="fa-solid fa-file-pdf text-xs"></i>
                            PDF
                        </a>
                    </div>

                    <!-- Cards -->
                    <div class="flex flex-col gap-4">
                        <!-- Mission -->
                        <div class="glow-card relative p-5 rounded-xl">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500/20 to-emerald-600/20 flex items-center justify-center ring-1 ring-green-400/30">
                                    <i class="fa-solid fa-rocket text-sm text-green-400"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-white">Наша миссия</h2>
                            </div>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                Надежный сервис для защиты приватности с современными протоколами шифрования.
                            </p>
                        </div>

                        <!-- Security -->
                        <div class="glow-card relative p-5 rounded-xl">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500/20 to-green-600/20 flex items-center justify-center ring-1 ring-emerald-400/30">
                                    <i class="fa-solid fa-shield-halved text-sm text-emerald-400"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-white">Безопасность</h2>
                            </div>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                AES-256, OpenVPN, WireGuard, Shadowsocks. Серверы в юрисдикциях с защитой данных.
                            </p>
                        </div>

                        <!-- Network -->
                        <div class="glow-card relative p-5 rounded-xl">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500/20 to-teal-600/20 flex items-center justify-center ring-1 ring-green-400/30">
                                    <i class="fa-solid fa-globe text-sm text-green-400"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-white">Глобальная сеть</h2>
                            </div>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                Сервер в Нидерландах, Амстердам. Высокая скорость и стабильность соединения.
                            </p>
                        </div>

                        <!-- Support -->
                        <div class="glow-card relative p-5 rounded-xl">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500/20 to-green-600/20 flex items-center justify-center ring-1 ring-teal-400/30">
                                    <i class="fa-solid fa-headset text-sm text-teal-400"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-white">Поддержка</h2>
                            </div>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                Техподдержка 24/7 на русском и английском языках.
                            </p>
                        </div>

                        <!-- Cat Philosophy -->
                        <div class="glow-card relative p-5 rounded-xl overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-amber-500/10 via-orange-500/10 to-amber-500/10">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500/30 to-orange-600/30 flex items-center justify-center ring-1 ring-amber-400/40">
                                        <i class="fa-solid fa-cat text-xl text-amber-400"></i>
                                    </div>
                                    <h2 class="text-lg font-bold text-white">Мы — коты</h2>
                                </div>
                                <p class="text-gray-400 text-sm leading-relaxed mb-3">
                                    <span class="text-amber-400 font-medium">Независимы, гибки и всегда на
                                        высоте</span>.
                                    Ценим свободу и защищаем ваше личное пространство с кошачьей грацией.
                                </p>
                                <div class="flex flex-col gap-2 text-xs">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-eye text-amber-400 w-4"></i>
                                        <span class="text-gray-400">Всё видим, никому не говорим</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-shoe-prints text-amber-400 w-4"></i>
                                        <span class="text-gray-400">Бесшумный след в сети</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-moon text-amber-400 w-4"></i>
                                        <span class="text-gray-400">Работаем ночью</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Author -->
                        <div class="glow-card relative p-5 rounded-xl overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-purple-500/10 via-pink-500/10 to-purple-500/10">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500/30 to-pink-600/30 flex items-center justify-center ring-1 ring-purple-400/40">
                                        <i class="fa-solid fa-user-astronaut text-xl text-purple-400"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-white">Автор</h2>
                                        <p class="text-purple-400 text-xs">Видение & Разработка</p>
                                    </div>
                                </div>
                                <p class="text-gray-400 text-sm leading-relaxed">
                                    Идея родилась из потребности в <span class="text-purple-400 font-medium">надежном и
                                        красивом</span> VPN.
                                    Мы не верим в компромиссы.
                                </p>
                            </div>
                        </div>

                        <!-- Studio -->
                        <div class="glow-card relative p-5 rounded-xl overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 via-blue-500/10 to-cyan-500/10">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500/30 to-blue-600/30 flex items-center justify-center ring-1 ring-cyan-400/40">
                                        <i class="fa-solid fa-rocket text-xl text-cyan-400"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-white"><?= htmlspecialchars($site['ООО']) ?>
                                            Studio</h2>
                                        <p class="text-cyan-400 text-xs"><?= htmlspecialchars($site['студия']) ?></p>
                                    </div>
                                </div>
                                <p class="text-gray-400 text-sm leading-relaxed mb-3">
                                    <span class="text-cyan-400 font-medium">Команда энтузиастов</span>, создающих
                                    цифровые продукты с душой.
                                </p>
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        class="px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 text-xs border border-cyan-500/20">VPN</span>
                                    <span
                                        class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 text-xs border border-blue-500/20">Tech</span>
                                    <span
                                        class="px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-400 text-xs border border-purple-500/20">Innovation</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quote -->
                        <div class="relative p-4 rounded-xl overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-green-500/10 via-emerald-500/10 to-teal-500/10">
                            </div>
                            <div class="absolute inset-0 border border-white/10 rounded-xl"></div>
                            <div class="relative z-10 text-center">
                                <i class="fa-solid fa-quote-left text-xl text-green-400/30 mb-2"></i>
                                <p class="text-sm text-white font-light italic mb-1">
                                    "Будь как кот — незаметный, независимый и свободный"
                                </p>
                                <span class="text-green-400/60 text-xs">—
                                    <?= htmlspecialchars($site['ООО']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-3 rounded-xl bg-white/[0.03]">
                            <span class="text-2xl font-bold text-green-400">NL</span>
                            <span class="text-gray-400 text-xs">Нидерланды</span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-3 rounded-xl bg-white/[0.03]">
                            <span class="text-2xl font-bold text-emerald-400">AES-256</span>
                            <span class="text-gray-400 text-xs">Шифрование</span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-3 rounded-xl bg-white/[0.03]">
                            <span class="text-2xl font-bold text-teal-400">24/7</span>
                            <span class="text-gray-400 text-xs">Поддержка</span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-3 rounded-xl bg-white/[0.03]">
                            <span class="text-2xl font-bold text-green-400">99.9%</span>
                            <span class="text-gray-400 text-xs">Uptime</span>
                        </div>
                    </div>
                </section>
            </div>

        </main>
        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/main/main.js" defer></script>
        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/theme/main.js" defer></script>
    </div>
</body>

</html>