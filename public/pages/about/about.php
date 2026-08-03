<?php
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\Language\Language;
use Setting\Route\Function\Functions;
// Auth::auth();
$site = Functions::site();
$currentLanguage = Language::getCurrent();
$translations = Language::getTranslations($currentLanguage);
$t = fn(string $key): string => $translations[$key] ?? $key;
?>
<!DOCTYPE html>
<html lang="<?= $currentLanguage ?>" class="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $t('about_title') ?></title>

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

        <?php include_once 'public/components/header.php' ?>

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
                                <h1 class="text-3xl font-bold text-white tracking-wide"><?= $t('about_title') ?></h1>
                                <p class="text-gray-400 text-sm">
                                    <?= htmlspecialchars($site['ООО']) ?> <?= $t('about_desc') ?>
                                </p>
                            </div>
                        </div>
                        <a href="/export/pdf?type=about"
                            class="elite-btn glow-card group relative flex items-center gap-2 px-5 py-3 rounded-xl cursor-pointer transition-all duration-300 hover:scale-105">
                            <i class="fa-solid fa-file-pdf text-green-300 group-hover:text-white transition-colors"></i>
                            <span class="text-[white] font-medium"><?= $t('export_pdf') ?></span>
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
                                    <h2 class="text-xl font-semibold text-white"><?= $t('mission') ?></h2>
                                </div>
                                <p class="text-gray-400 leading-relaxed text-sm">
                                    <?= htmlspecialchars($site['ООО']) ?> <?= $t('mission_desc') ?>
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
                                    <h2 class="text-xl font-semibold text-white"><?= $t('security') ?></h2>
                                </div>
                                <p class="text-gray-400 leading-relaxed text-sm">
                                    <?= $t('security_desc') ?>
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
                                    <h2 class="text-xl font-semibold text-white"><?= $t('global_network') ?></h2>
                                </div>
                                <p class="text-gray-400 leading-relaxed text-sm">
                                    <?= $t('global_network_desc') ?>
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
                                    <h2 class="text-xl font-semibold text-white"><?= $t('support_24_7') ?></h2>
                                </div>
                                <p class="text-gray-400 leading-relaxed text-sm">
                                    <?= $t('support_desc') ?>
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
                                            class="bg-gradient-to-r from-amber-400 to-red-400 bg-clip-text text-transparent"><?= $t('your_personal_eagle') ?></span>
                                        <i class="fa-solid fa-feather text-amber-400 text-lg"></i>
                                    </h2>
                                    <p class="text-gray-400 leading-relaxed mb-4 text-lg">
                                        <span class="text-amber-400 font-semibold"><?= $t('imagine') ?></span> <?= $t('story_1') ?> <span
                                            class="text-white font-bold">
                                            <?= htmlspecialchars($site['ООО']) ?>
                                        </span>.
                                    </p>
                                    <div class="grid grid-cols-3 gap-4 mt-4">
                                        <div class="flex items-center gap-2 bg-white/[0.05] p-3 rounded-xl">
                                            <i class="fa-solid fa-bolt text-amber-400 text-xl"></i>
                                            <div>
                                                <span class="text-white font-semibold text-sm block"><?= $t('speed_light') ?></span>
                                                <span class="text-gray-400 text-xs"><?= $t('no_lags') ?></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 bg-white/[0.05] p-3 rounded-xl">
                                            <i class="fa-solid fa-shield-halved text-amber-400 text-xl"></i>
                                            <div>
                                                <span class="text-white font-semibold text-sm block"><?= $t('protection_army') ?></span>
                                                <span class="text-gray-400 text-xs"><?= $t('aes256_encryption') ?></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 bg-white/[0.05] p-3 rounded-xl">
                                            <i class="fa-solid fa-globe text-amber-400 text-xl"></i>
                                            <div>
                                                <span class="text-white font-semibold text-sm block"><?= $t('netherlands') ?></span>
                                                <span class="text-gray-400 text-xs"><?= $t('amsterdam_nl') ?></span>
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
                                            class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent"><?= $t('why_millions') ?></span>
                                    </h2>
                                    <p class="text-gray-400 leading-relaxed mb-4 text-lg">
                                        <span class="text-blue-400 font-semibold"><?= $t('year_2026') ?></span> <?= $t('story_2') ?> <span class="text-white font-bold">QWEES
                                            VPN</span><?= $t('works_always') ?>
                                    </p>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div
                                            class="flex items-center gap-3 bg-gradient-to-br from-white/[0.08] to-white/[0.03] p-4 rounded-xl border border-white/10">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-yellow-500/30 to-amber-600/30 flex items-center justify-center ring-1 ring-yellow-400/30">
                                                <i class="fa-solid fa-bolt text-yellow-400"></i>
                                            </div>
                                            <div>
                                                <span class="text-white font-semibold text-sm block"><?= $t('instant_start') ?></span>
                                                <span class="text-gray-400 text-xs"><?= $t('connect_2sec') ?></span>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 bg-gradient-to-br from-white/[0.08] to-white/[0.03] p-4 rounded-xl border border-white/10">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500/30 to-emerald-600/30 flex items-center justify-center ring-1 ring-green-400/30">
                                                <i class="fa-solid fa-infinity text-green-400"></i>
                                            </div>
                                            <div>
                                                <span class="text-white font-semibold text-sm block"><?= $t('unlimited_traffic') ?></span>
                                                <span class="text-gray-400 text-xs"><?= $t('download_watch') ?></span>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 bg-gradient-to-br from-white/[0.08] to-white/[0.03] p-4 rounded-xl border border-white/10">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500/30 to-cyan-600/30 flex items-center justify-center ring-1 ring-blue-400/30">
                                                <i class="fa-solid fa-shield-halved text-blue-400"></i>
                                            </div>
                                            <div>
                                                <span class="text-white font-semibold text-sm block"><?= $t('military_protection') ?></span>
                                                <span class="text-gray-400 text-xs"><?= $t('aes256_new_gen') ?></span>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 bg-gradient-to-br from-white/[0.08] to-white/[0.03] p-4 rounded-xl border border-white/10">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500/30 to-pink-600/30 flex items-center justify-center ring-1 ring-purple-400/30">
                                                <i class="fa-solid fa-headset text-purple-400"></i>
                                            </div>
                                            <div>
                                                <span class="text-white font-semibold text-sm block"><?= $t('support_24_7') ?></span>
                                                <span class="text-gray-400 text-xs"><?= $t('always_in_touch') ?></span>
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
                                        <h2 class="text-lg font-bold text-white"><?= $t('genius_behind') ?></h2>
                                        <p class="text-purple-400 text-xs">Founder & Tech Visionary</p>
                                    </div>
                                </div>
                                <p class="text-gray-400 text-sm leading-relaxed mb-3">
                                    <span class="text-purple-400 font-bold">
                                        <?= htmlspecialchars($site['контакты']['Директор']) ?>
                                    </span> <?= $t('dev_disappointed') ?> <span class="text-pink-400 font-semibold"><?= $t('his_mission') ?></span> <?= $t('give_free_internet') ?>
                                </p>
                                <div
                                    class="bg-gradient-to-r from-purple-500/10 to-pink-500/10 p-3 rounded-xl border-l-4 border-purple-400">
                                    <p class="text-gray-400 text-sm italic">
                                        <?= $t('quote_director') ?>
                                    </p>
                                </div>
                                <div class="flex items-center gap-4 pt-3 border-t border-white/10">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-sm font-bold">
                                        T</div>
                                    <span class="text-gray-400 text-xs"><?= $t('architect_freedom') ?></span>
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
                                    <?= $t('elite_lab') ?> <span
                                        class="text-cyan-400 font-semibold"><?= $t('future_tech') ?></span>.
                                    <?= $t('follow_no_trends') ?>
                                </p>
                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    <div class="text-center p-2 bg-white/[0.05] rounded-lg">
                                        <span class="text-cyan-400 font-bold text-lg">1</span>
                                        <span class="text-gray-400 text-xs block"><?= $t('server') ?></span>
                                        <span class="text-cyan-400/70 text-[10px] block"><?= $t('netherlands') ?></span>
                                    </div>
                                    <div class="text-center p-2 bg-white/[0.05] rounded-lg">
                                        <span class="text-blue-400 font-bold text-lg">99.9%</span>
                                        <span class="text-gray-400 text-xs block">Uptime</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 pt-3 border-t border-white/10">
                                    <span
                                        class="px-3 py-1 rounded-full bg-gradient-to-r from-red-500/20 to-orange-500/20 text-red-400 text-xs border border-red-500/30"><?= $t('premium_vpn') ?></span>
                                    <span
                                        class="px-3 py-1 rounded-full bg-gradient-to-r from-cyan-500/20 to-blue-500/20 text-cyan-400 text-xs border border-cyan-500/30"><?= $t('top_2026') ?></span>
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
                                <?= $t('quote_cat') ?> <span class="text-amber-400 font-medium"><?= $t('your_freedom') ?></span>.
                                <br><?= $t('with') ?>
                                <?= htmlspecialchars($site['ООО']) ?> <span class="text-white font-bold"><?= $t('world_in_pocket') ?></span>"
                            </p>
                            <span class="text-amber-400/60 text-sm"><?= $t('join_revolution') ?>
                                <?= htmlspecialchars($site['контакты']['Директор']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="grid grid-cols-4 gap-4 mt-4">
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.03]">
                            <span class="text-3xl font-bold text-green-400">NL</span>
                            <span class="text-gray-400 text-xs mt-1"><?= $t('netherlands') ?></span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.03]">
                            <span class="text-3xl font-bold text-emerald-400">AES-256</span>
                            <span class="text-gray-400 text-xs mt-1"><?= $t('encryption_short') ?></span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.03]">
                            <span class="text-3xl font-bold text-teal-400">24/7</span>
                            <span class="text-gray-400 text-xs mt-1"><?= $t('support_short') ?></span>
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
                            <h1 class="text-xl font-bold text-white"><?= $t('about_title') ?></h1>
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
                                <h2 class="text-lg font-semibold text-white"><?= $t('mission') ?></h2>
                            </div>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                <?= $t('reliable_service') ?>
                            </p>
                        </div>

                        <!-- Security -->
                        <div class="glow-card relative p-5 rounded-xl">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500/20 to-green-600/20 flex items-center justify-center ring-1 ring-emerald-400/30">
                                    <i class="fa-solid fa-shield-halved text-sm text-emerald-400"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-white"><?= $t('security') ?></h2>
                            </div>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                <?= $t('security_mobile') ?>
                            </p>
                        </div>

                        <!-- Network -->
                        <div class="glow-card relative p-5 rounded-xl">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500/20 to-teal-600/20 flex items-center justify-center ring-1 ring-green-400/30">
                                    <i class="fa-solid fa-globe text-sm text-green-400"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-white"><?= $t('global_network') ?></h2>
                            </div>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                <?= $t('network_mobile') ?>
                            </p>
                        </div>

                        <!-- Support -->
                        <div class="glow-card relative p-5 rounded-xl">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500/20 to-green-600/20 flex items-center justify-center ring-1 ring-teal-400/30">
                                    <i class="fa-solid fa-headset text-sm text-teal-400"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-white"><?= $t('support_short') ?></h2>
                            </div>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                <?= $t('support_mobile') ?>
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
                                    <h2 class="text-lg font-bold text-white"><?= $t('we_are_cats') ?></h2>
                                </div>
                                <p class="text-gray-400 text-sm leading-relaxed mb-3">
                                    <span class="text-amber-400 font-medium"><?= $t('cats_desc_1') ?></span>.
                                    <?= $t('cats_desc_2') ?>
                                </p>
                                <div class="flex flex-col gap-2 text-xs">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-eye text-amber-400 w-4"></i>
                                        <span class="text-gray-400"><?= $t('see_all') ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-shoe-prints text-amber-400 w-4"></i>
                                        <span class="text-gray-400"><?= $t('silent_trace') ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-moon text-amber-400 w-4"></i>
                                        <span class="text-gray-400"><?= $t('work_at_night') ?></span>
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
                                        <h2 class="text-lg font-bold text-white"><?= $t('author') ?></h2>
                                        <p class="text-purple-400 text-xs"><?= $t('vision_dev') ?></p>
                                    </div>
                                </div>
                                <p class="text-gray-400 text-sm leading-relaxed">
                                    <?= $t('idea_born') ?> <span class="text-purple-400 font-medium"><?= $t('reliable_beautiful') ?></span> VPN.
                                    <?= $t('no_compromises') ?>
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
                                    <span class="text-cyan-400 font-medium"><?= $t('team_enthusiasts') ?></span><?= $t('digital_products') ?>
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
                                    <?= $t('cat_quote') ?>
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
                            <span id="1" class="text-2xl font-bold text-green-400">NL</span>
                            <span class="text-gray-400 text-xs"><?= $t('netherlands') ?></span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-3 rounded-xl bg-white/[0.03]">
                            <span class="text-2xl font-bold text-emerald-400">AES-256</span>
                            <span class="text-gray-400 text-xs"><?= $t('encryption_short') ?></span>
                        </div>
                        <div
                            class="gradient-border flex flex-col items-center justify-center p-3 rounded-xl bg-white/[0.03]">
                            <span class="text-2xl font-bold text-teal-400">24/7</span>
                            <span class="text-gray-400 text-xs"><?= $t('support_short') ?></span>
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