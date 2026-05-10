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
    <title>Реквизиты компании</title>

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
    <script src="/public/assets/scripts/theme/main.js" defer></script>

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
                        src="/public/assets/images/icons/logo/qweesvpn.svg"
                        alt="<?= isset($site['ООО']) ? htmlspecialchars($site['ООО']) : '' ?>">
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
                                <i class="fa-solid fa-file-invoice text-2xl text-green-400"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white tracking-wide">Реквизиты компании</h1>
                                <p class="text-gray-400 text-sm">Официальные данные для документооборота</p>
                            </div>
                        </div>
                        <a href="/export/pdf?type=requisites"
                            class="elite-btn glow-card group relative flex items-center gap-2 px-5 py-3 rounded-xl cursor-pointer transition-all duration-300 hover:scale-105">
                            <i class="fa-solid fa-file-pdf text-green-300 group-hover:text-white transition-colors"></i>
                            <span class="text-[white] font-medium">Экспорт PDF</span>
                        </a>
                    </div>

                    <!-- Content Cards Grid -->
                    <div class="grid grid-cols-2 gap-6 items-start">
                        <!-- Main Info Card -->
                        <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-green-500/5 via-transparent to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div
                                class="absolute -bottom-10 -right-10 w-32 h-32 bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-full blur-2xl">
                            </div>

                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 via-transparent to-transparent flex items-center justify-center text-white font-bold text-lg shrink-0 shadow-sm ">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <h2 class="text-xl font-bold text-white">Основная информация</h2>
                                </div>

                                <div class="space-y-4">
                                    <?php if (isset($site['информация']['Полное название']) && $site['информация']['Полное название'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">Полное название</span>
                                        <span class="text-white font-medium text-sm">
                                            <?= htmlspecialchars($site['информация']['Полное название']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['информация']['ИНН']) && $site['информация']['ИНН'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">ИНН</span>
                                        <span class="text-emerald-400 font-medium font-mono text-sm">
                                            <?= htmlspecialchars($site['информация']['ИНН']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['информация']['ОГРН']) && $site['информация']['ОГРН'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">ОГРН</span>
                                        <span class="text-emerald-400 font-medium font-mono text-sm">
                                            <?= htmlspecialchars($site['информация']['ОГРН']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['информация']['КПП']) && $site['информация']['КПП'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">КПП</span>
                                        <span class="text-emerald-400 font-medium font-mono text-sm">
                                            <?= htmlspecialchars($site['информация']['КПП']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Details Card -->
                        <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 via-transparent to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div
                                class="absolute -top-10 -left-10 w-32 h-32 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 rounded-full blur-2xl">
                            </div>

                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 via-transparent to-transparent flex items-center justify-center text-white font-bold text-lg shrink-0 shadow-sm ">
                                        <i class="fa-solid fa-university"></i>
                                    </div>
                                    <h2 class="text-xl font-bold text-white">Банковские реквизиты</h2>
                                </div>

                                <div class="space-y-4">
                                    <?php if (isset($site['банк']['Банк']) && $site['банк']['Банк'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">Банк</span>
                                        <span class="text-white font-medium text-sm"><?= htmlspecialchars($site['банк']['Банк']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['банк']['БИК']) && $site['банк']['БИК'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">БИК</span>
                                        <span
                                            class="text-cyan-400 font-medium font-mono text-sm"><?= htmlspecialchars($site['банк']['БИК']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['банк']['Расчетный счет']) && $site['банк']['Расчетный счет'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">Расчетный счет</span>
                                        <span
                                            class="text-cyan-400 font-medium font-mono text-xs"><?= htmlspecialchars($site['банк']['Расчетный счет']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['банк']['Корр. счет']) && $site['банк']['Корр. счет'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">Корр. счет</span>
                                        <span
                                            class="text-cyan-400 font-medium font-mono text-xs"><?= htmlspecialchars($site['банк']['Корр. счет']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Address Card -->
                        <!-- <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-amber-500/5 via-transparent to-orange-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div
                                class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-full blur-2xl">
                            </div>

                            <div class="relative z-10">
                            </div>
                        </div> -->

                        <!-- Contact Card -->
                        <div class="glow-card relative p-6 rounded-2xl overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-purple-500/5 via-transparent to-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div
                                class="absolute -bottom-10 -left-10 w-32 h-32 bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-full blur-2xl">
                            </div>

                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 via-transparent to-transparent flex items-center justify-center text-white font-bold text-lg shrink-0 shadow-sm ">
                                        <i class="fa-solid fa-address-card"></i>
                                    </div>
                                    <h2 class="text-xl font-bold text-white">Контактная информация</h2>
                                </div>

                                <div class="space-y-4">
                                    <?php if (isset($site['контакты']['Директор']) && $site['контакты']['Директор'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">Директор</span>
                                        <span
                                            class="text-white font-medium text-sm"><?= htmlspecialchars($site['контакты']['Директор']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['контакты']['Почта']) && $site['контакты']['Почта'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between gap-3 p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">Почта</span>
                                        <span
                                            class="text-purple-400 font-medium"><?= htmlspecialchars($site['контакты']['Почта']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['контакты']['Телефон']) && $site['контакты']['Телефон'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between gap-3 p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">Телефон</span>
                                        <span class="text-white font-medium"><?= htmlspecialchars($site['контакты']['Телефон']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['контакты']['мессенджер']['telegram']) && $site['контакты']['мессенджер']['telegram'] !== ''): ?>
                                    <div
                                        class="gradient-border flex items-center justify-between gap-3 p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300">
                                        <span class="text-gray-400 text-sm">Поддержка</span>
                                        <span class="text-purple-400 font-medium">tg: <a
                                                href="https://t.me/<?= htmlspecialchars(ltrim($site['контакты']['мессенджер']['telegram'], '@')) ?>"><?= htmlspecialchars($site['контакты']['мессенджер']['telegram']) ?></a>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notice
                    <div class="relative p-6 rounded-2xl overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-500/10">
                        </div>
                        <div class="absolute inset-0 border border-amber-400/20 rounded-2xl"></div>
                        <div class="relative z-10 flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500/30 to-orange-600/30 flex items-center justify-center ring-1 ring-amber-400/40 shrink-0">
                                <i class="fa-solid fa-triangle-exclamation text-xl text-amber-400"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white mb-2">Важная информация</h3>
                                <p class="text-gray-400 text-sm leading-relaxed">
                                    Все реквизиты актуальны и действительны для заключения договоров, выставления счетов
                                    и проведения платежей.
                                    При оплате услуг, пожалуйста, указывайте назначение платежа корректно.
                                </p>
                            </div>
                        </div>
                    </div> -->

                        <!-- Quick Actions -->
                        <div class="flex justify-between gap-4">
                            <?php if (isset($site['контакты']['Почта']) && $site['контакты']['Почта'] !== ''): ?>
                            <a href="mailto:<?= htmlspecialchars($site['контакты']['Почта']) ?>"
                                class="flex-1 gradient-border flex items-center justify-center gap-2 p-4 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300 group">
                                <i
                                    class="fa-solid fa-envelope text-purple-400 group-hover:scale-110 transition-transform"></i>
                                <span class="text-gray-400 text-sm">Написать</span>
                            </a>
                            <?php endif; ?>
                            <?php if (isset($site['контакты']['Телефон']) && $site['контакты']['Телефон'] !== ''): ?>
                            <a href="tel:<?= htmlspecialchars(preg_replace('/[^\d+]/', '', $site['контакты']['Телефон'])) ?>"
                                class="flex-1 gradient-border flex items-center justify-center gap-2 p-4 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300 group">
                                <i
                                    class="fa-solid fa-phone text-green-400 group-hover:scale-110 transition-transform"></i>
                                <span class="text-gray-400 text-sm">Позвонить</span>
                            </a>
                            <?php endif; ?>
                            <button onclick="window.print()"
                                class="flex-1 gradient-border flex items-center justify-center gap-2 p-4 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] transition-all duration-300 group cursor-pointer">
                                <i
                                    class="fa-solid fa-print text-cyan-400 group-hover:scale-110 transition-transform"></i>
                                <span class="text-gray-400 text-sm">Печать</span>
                            </button>
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
                                <i class="fa-solid fa-file-invoice text-lg text-green-400"></i>
                            </div>
                            <h1 class="text-xl font-bold text-white">Реквизиты</h1>
                        </div>
                        <a href="/export/pdf?type=requisites"
                            class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-green-500/80 to-green-600/80 text-[white] text-sm font-medium rounded-lg">
                            <i class="fa-solid fa-file-pdf text-xs"></i>
                            PDF
                        </a>
                    </div>

                    <!-- Cards -->
                    <div class="flex flex-col gap-4">
                        <!-- Main Info -->
                        <div class="glow-card relative p-5 rounded-xl overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-green-500/10 via-emerald-500/10 to-green-500/10">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4 rounded-lg bg-white/[0.03]">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 via-transparent to-transparent flex items-center justify-center text-white font-bold text-lg shrink-0 shadow-sm ">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <h2 class="text-lg font-bold text-white">Основная информация</h2>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <?php if (isset($site['информация']['Полное название']) && $site['информация']['Полное название'] !== ''): ?>
                                    <div class="flex justify-between items-center rounded-lg bg-white/[0.03] p-2">
                                        <span class="text-gray-400">Название</span>
                                        <span
                                            class="text-white font-medium text-right"><?= htmlspecialchars($site['информация']['Полное название']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['информация']['ИНН']) && $site['информация']['ИНН'] !== ''): ?>
                                    <div class="flex justify-between items-center rounded-lg bg-white/[0.03] p-2">
                                        <span class="text-gray-400">ИНН</span>
                                        <span
                                            class="text-emerald-400 font-medium font-mono"><?= htmlspecialchars($site['информация']['ИНН']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['информация']['ОГРН']) && $site['информация']['ОГРН'] !== ''): ?>
                                    <div class="flex justify-between items-center rounded-lg bg-white/[0.03] p-2">
                                        <span class="text-gray-400">ОГРН</span>
                                        <span
                                            class="text-emerald-400 font-medium font-mono text-xs"><?= htmlspecialchars($site['информация']['ОГРН']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['информация']['КПП']) && $site['информация']['КПП'] !== ''): ?>
                                    <div class="flex justify-between items-center rounded-lg bg-white/[0.03] p-2">
                                        <span class="text-gray-400">КПП</span>
                                        <span
                                            class="text-emerald-400 font-medium font-mono"><?= htmlspecialchars($site['информация']['КПП']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Bank -->
                        <div class="glow-card relative p-5 rounded-xl overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 via-blue-500/10 to-cyan-500/10">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4 rounded-lg bg-white/[0.03]">
                                    <div class=" w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 via-transparent
                                    to-transparent flex items-center justify-center text-white font-bold text-lg
                                    shrink-0 shadow-sm ">
                                        <i class=" fa-solid fa-university"></i>
                                    </div>
                                    <h2 class="text-lg font-bold text-white">Банковские реквизиты</h2>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <?php if (isset($site['банк']['Банк']) && $site['банк']['Банк'] !== ''): ?>
                                    <div class="flex justify-between items-center rounded-lg bg-white/[0.03] p-2">
                                        <span class="text-gray-400">Банк</span>
                                        <span
                                            class="text-white font-medium text-right"><?= htmlspecialchars($site['банк']['Банк']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['банк']['БИК']) && $site['банк']['БИК'] !== ''): ?>
                                    <div class="flex justify-between items-center rounded-lg bg-white/[0.03] p-2">
                                        <span class="text-gray-400">БИК</span>
                                        <span
                                            class="text-cyan-400 font-medium font-mono"><?= htmlspecialchars($site['банк']['БИК']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['банк']['Расчетный счет']) && $site['банк']['Расчетный счет'] !== ''): ?>
                                    <div class="flex justify-between items-center rounded-lg bg-white/[0.03] p-2">
                                        <span class="text-gray-400">Р/с</span>
                                        <span
                                            class="text-cyan-400 font-medium font-mono text-xs"><?= htmlspecialchars($site['банк']['Расчетный счет']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Contacts -->
                        <div class="glow-card relative p-5 rounded-xl overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-purple-500/10 via-pink-500/10 to-purple-500/10">
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-4 rounded-lg bg-white/[0.03]">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 via-transparent to-transparent flex items-center justify-center text-white font-bold text-lg shrink-0 shadow-sm ">
                                        <i data-theme-invert class="fa-solid fa-address-card"></i>
                                    </div>
                                    <h2 class=" text-lg font-bold text-white">Контактная информация</h2>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <?php if (isset($site['контакты']['Директор']) && $site['контакты']['Директор'] !== ''): ?>
                                    <div class="flex items-center rounded-lg justify-between p-3 bg-white/[0.03]">
                                        <span class="text-gray-400 text-sm">Директор</span>
                                        <span
                                            class="text-white font-medium text-sm"><?= htmlspecialchars($site['контакты']['Директор']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['контакты']['Почта']) && $site['контакты']['Почта'] !== ''): ?>
                                    <div class="flex items-center rounded-lg justify-between p-3 bg-white/[0.03]">
                                        <span class="text-gray-400 text-sm">Почта</span>
                                        <span
                                            class="text-purple-400 font-medium"><?= htmlspecialchars($site['контакты']['Почта']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['контакты']['Телефон']) && $site['контакты']['Телефон'] !== ''): ?>
                                    <div class="flex items-center rounded-lg justify-between p-3 bg-white/[0.03]">
                                        <span class="text-gray-400 text-sm">Телефон</span>
                                        <span class="text-white font-medium"><?= htmlspecialchars($site['контакты']['Телефон']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($site['контакты']['мессенджер']['telegram']) && $site['контакты']['мессенджер']['telegram'] !== ''): ?>
                                    <div class="flex items-center rounded-lg justify-between p-3 bg-white/[0.03]">
                                        <span class="text-gray-400 text-sm">Поддержка</span>
                                        <span class="text-purple-400 font-medium">tg: <a
                                                href="https://t.me/<?= htmlspecialchars(ltrim($site['контакты']['мессенджер']['telegram'], '@')) ?>"><?= htmlspecialchars($site['контакты']['мессенджер']['telegram']) ?></a>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="grid grid-cols-3 gap-2">
                            <?php if (isset($site['контакты']['Почта']) && $site['контакты']['Почта'] !== ''): ?>
                            <a href="mailto:<?= htmlspecialchars($site['контакты']['Почта']) ?>"
                                class="gradient-border flex flex-col items-center justify-center gap-1 p-3 rounded-xl bg-white/[0.03]">
                                <i class="fa-solid fa-envelope text-purple-400 text-lg"></i>
                                <span class="text-gray-400 text-xs">Email</span>
                            </a>
                            <?php endif; ?>
                            <?php if (isset($site['контакты']['Телефон']) && $site['контакты']['Телефон'] !== ''): ?>
                            <a href="tel:<?= htmlspecialchars(preg_replace('/[^\d+]/', '', $site['контакты']['Телефон'])) ?>"
                                class="gradient-border flex flex-col items-center justify-center gap-1 p-3 rounded-xl bg-white/[0.03]">
                                <i class="fa-solid fa-phone text-green-400 text-lg"></i>
                                <span class="text-gray-400 text-xs">Звонок</span>
                            </a>
                            <?php endif; ?>
                            <button onclick="window.print()"
                                class="gradient-border flex flex-col items-center justify-center gap-1 p-3 rounded-xl bg-white/[0.03]">
                                <i class="fa-solid fa-print text-cyan-400 text-lg"></i>
                                <span class="text-gray-400 text-xs">Печать</span>
                            </button>
                        </div>
                    </div>
                </section>
            </div>

        </main>
        <script src="/public/assets/scripts/main/main.js" defer></script>
        <script src="/public/assets/scripts/theme/main.js" defer></script>
    </div>
</body>

</html>