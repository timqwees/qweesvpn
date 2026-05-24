<?php

use Setting\Route\Function\Functions;
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\Kassa\PriceConfig;

Auth::auth();
$site = Functions::site();

// === Формирование цен из базы данных ===
$hasReferral = PriceConfig::hasReferralDiscount();
$prices = PriceConfig::getPrices($hasReferral);   // [1 => ['basic'=>150,...], 6 => [...], 12 => [...]]
$tariffMeta = PriceConfig::getTariffMeta();

// Удобные переменные для шаблона
$p1 = $prices[1];   // цены за 1 месяц
$p6 = $prices[6];   // цены за 6 месяцев (за месяц)
$p12 = $prices[12]; // цены за 12 месяцев (за месяц)

// Итого за период
$t1  = ['basic' => $p1['basic'] * 1,  'clasic' => $p1['clasic'] * 1,  'pro' => $p1['pro'] * 1];
$t6  = ['basic' => $p6['basic'] * 6,  'clasic' => $p6['clasic'] * 6,  'pro' => $p6['pro'] * 6];
$t12 = ['basic' => $p12['basic'] * 12, 'clasic' => $p12['clasic'] * 12, 'pro' => $p12['pro'] * 12];
?>
<!DOCTYPE html>
<html lang="ru" class="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Оплата</title>

    <!-- Preload critical resources -->
    <link rel="preload" href="/public/assets/styles/style.css" as="style" defer>
    <link rel="preload" href="/public/assets/images/icons/logo/qweesvpn.svg" as="image" type="image/svg+xml" defer>
    <link rel="preload" href="/public/assets/images/icons/services/buy/crown.svg" as="image" defer>

    <!-- Critical CSS with onload optimization -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" as="style"
        crossorigin="anonymous" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
            crossorigin="anonymous">
    </noscript>

    <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet" media="print" onload="this.media='all'"
        defer>
    <noscript>
        <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet">
    </noscript>

    <link rel="stylesheet" href="/public/assets/styles/style.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/style.css">
    </noscript>

    <!-- Deferred scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Noscript fallback -->
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/noscript.css">
    </noscript>
</head>

<body class="bg-black bg-no-repeat flex items-center w-full overflow-x-hidden">
    <div class="min-h-screen flex flex-col w-full container mx-auto">

        <main class="flex sm:my-2 w-full">
            <!-- КОНЕЦ БЕЗ ИЗМЕНЕНИЙ -->

            <!-- ################# CONTENT DESCKTOP ####################-->
            <div class="hidden sm:block w-full text-white" data-pay-layout="desktop">
                <!-- main -->
                <section data-section="main"
                    class="overflow-hidden relative flex flex-col gap-2 justify-between pt-[65px] pb-4 box-border w-full min-h-[100dvh] px-64 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center flex-col gap-4 items-center">
                        <div class="bg_active relative flex items-center justify-center p-6 aspect-square">
                            <img class="max-h-12" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                        <!-- text -->
                        <div class="flex flex-col items-center justify-center">
                            <h3 class="text-xl font-bold font-sans">Выберите подписку</h3>
                            <div class="text-center text-white/70">Получите полную свободу от реклам и запретов!</div>
                        </div>
                    </div>
                    <!-- grid -->
                    <div class="grid grid-cols-2 grid-rows-2 gap-2">
                        <!-- block 1 -->
                        <div class="relative flex items-end pr-6 flex-col p-2 gap-2 border_light_b border_light_r pb-4">
                            <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                    alt="icon1" loading="lazy">
                                <h4 class="text-lg font-bold font-sans">Аноним</h4>
                            </div>
                            <p class="text-sm text-sans text-white/70 break-all">Маскируем вашу сеть от
                                перехватов</p>
                        </div>
                        <!-- block 2 -->
                        <div class="relative flex flex-col p-2 gap-2 border_light_b pl-4">
                            <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/speed.svg"
                                    alt="icon1" loading="lazy">
                                <h4 class="text-lg font-bold font-sans">Скрость</h4>
                            </div>
                            <p class="text-sm text-sans text-white/70 break-all">Даем скорость более 1000 Mb/s</p>
                        </div>
                        <!-- block 3 -->
                        <div class="relative flex items-end pr-6 flex-col p-2 gap-2 border_light_r">
                            <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/ads.svg"
                                    alt="icon1" loading="lazy">
                                <h4 class="text-lg font-bold font-sans">Без рекламы</h4>
                            </div>
                            <p class="text-sm text-sans text-white/70 break-all">Блокируем все рекламы в интернете</p>
                        </div>
                        <!-- block 4 -->
                        <div class="relative flex flex-col p-2 gap-2 pl-4">
                            <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/shield.svg"
                                    alt="icon1" loading="lazy">
                                <h4 class="text-lg font-bold font-sans">Скрытность</h4>
                            </div>
                            <p class="text-sm text-sans text-white/70 break-all">Защита ваших данных в сети</p>
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-4">
                        <!-- inputs -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label data-select-section="next_1"
                                class="flex bg-gradient-to-r from-white/20 to-white/5 bg_active justify-between px-6 py-1.5 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                <!-- titile -->
                                <div class="flex flex-col justify-center">
                                    <h5 class="text-xl font-bold">1 Месяц</h5>
                                    <p class="text-white/70 font-light">Ежемесячная от <?= $p1['basic'] ?>₽</p>
                                </div>
                                <!-- part 2 -->
                                <div class="flex items-center justify-center gap-4">
                                    <!-- price -->
                                    <div class="flex flex-col text-center">
                                        <span class="text-3xl font-bold"><?= $p1['pro'] ?></span>
                                        <p class="text-sm">₽/Месяц</p>
                                    </div>
                                    <!-- radio button -->
                                    <div class="flex items-center justify-center">
                                        <input type="radio" name="subscription" value="1month" class="sr-only peer" />
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <!-- input 2 -->
                            <label data-select-section="next_6"
                                class="flex bg-gradient-to-r from-white/20 to-white/5 bg_active justify-between px-6 py-1.5 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                <!-- titile -->
                                <div class="flex flex-col justify-center">
                                    <h5 class="text-xl font-bold">6 Месяцев</h5>
                                    <p class="text-white/70 font-light">Ежемесячная от <?= $p6['basic'] ?>₽</p>
                                </div>
                                <!-- part 2 -->
                                <div class="flex items-center justify-center gap-4">
                                    <!-- price -->
                                    <div class="flex flex-col text-center">
                                        <span class="text-3xl font-bold"><?= $t6['basic'] ?></span>
                                        <p class="text-sm">₽/6 Мес</p>
                                    </div>
                                    <!-- radio button -->
                                    <div class="flex items-center justify-center">
                                        <input type="radio" name="subscription" value="6months" class="sr-only peer" />
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <!-- input 3 -->
                            <label data-select-section="next_12"
                                class="flex bg-gradient-to-r from-white/20 to-white/5 bg_active justify-between px-6 py-1.5 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                <!-- titile -->
                                <div class="flex flex-col justify-center">
                                    <h5 class="text-xl font-bold">12 Месяцев</h5>
                                    <p class="text-white/70 font-light">Ежемесячная от <?= $p12['basic'] ?>₽</p>
                                </div>
                                <!-- part 2 -->
                                <div class="flex items-center justify-center gap-4">
                                    <!-- price -->
                                    <div class="flex flex-col text-center">
                                        <span class="text-3xl font-bold"><?= $t12['basic'] ?></span>
                                        <p class="text-sm">₽/12 Мес</p>
                                    </div>
                                    <!-- radio button -->
                                    <div class="flex items-center justify-center">
                                        <input type="radio" name="subscription" value="12months" class="sr-only peer" />
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <!-- button next to -->
                        <button onclick=" return false" data-toggle-section="main" data-main
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            Выбрать и продолжить <i class="fa fa-arrow-right"></i>
                        </button>
                        <span class="text-center text-white/70 text-sm">Далее будут тарифы</span>
                    </div>

                </section>

                <!-- на 1 месяц -->
                <section data-section="next_1"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-64 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center flex-col gap-4 items-center mb-6">
                        <div class="bg_active relative flex items-center justify-center p-6 aspect-square">
                            <img class="max-h-12" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                        <!-- text -->
                        <div class="flex flex-col items-center justify-center">
                            <h3 class="text-xl font-bold font-sans">Выберите тариф</h3>
                            <div class="text-center text-white/70">От выбранного тарифа зависит цена на ежемесячную
                                оплату!
                            </div>
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-3">
                        <!-- inputs -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">1 Месяц</h5>
                                        <p class="text-white/70 font-light">Тариф MYSELF</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p1['basic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="1month_1"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">1 устройство (для себя)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t1['basic'] ?>₽</span></p>
                            </label>
                            <!-- input 2 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">1 Месяц</h5>
                                        <p class="text-white/70 font-light">Тариф Family</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p1['clasic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="1month_4"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif2.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">4 устройства (для семьи)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t1['clasic'] ?>₽</span></p>
                            </label>
                            <!-- input 3 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">1 Месяц</h5>
                                        <p class="text-white/70 font-light">Тариф Business</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p1['pro'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="1month_10"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif3.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">10 устройств (для бизнеса)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t1['pro'] ?>₽</span></p>
                            </label>
                        </div>
                        <!-- button next to -->
                        <button onclick="return false" data-toggle-section="finish"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            завершить и купить <i class="fa fa-arrow-right"></i>
                        </button>
                        <button onclick="return false" data-toggle-section="main"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            <i class="fa fa-arrow-left"></i> Вернуться назад
                        </button>
                        <span class="text-center text-white/70 text-sm">Далее будет покупка</span>
                    </div>

                </section>

                <!-- на 6 месяцев -->
                <section data-section="next_6"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-64 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center flex-col gap-4 items-center mb-6">
                        <div class="bg_active relative flex items-center justify-center p-6 aspect-square">
                            <img class="max-h-12" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                        <!-- text -->
                        <div class="flex flex-col items-center justify-center">
                            <h3 class="text-xl font-bold font-sans">Выберите тариф</h3>
                            <div class="text-center text-white/70">От выбранного тарифа зависит цена на ежемесячную
                                оплату!
                            </div>
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-3">
                        <!-- inputs -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">6 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф MYSELF</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p6['basic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="6months_1"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">1 устройство (для себя)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t6['basic'] ?>₽</span></p>
                            </label>
                            <!-- input 2 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">6 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф Family</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p6['clasic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="6months_4"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif2.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">4 устройства (для семьи)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t6['clasic'] ?>₽</span></p>
                            </label>
                            <!-- input 3 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">6 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф Business</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p6['pro'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="6months_10"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif3.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">10 устройств (для бизнеса)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t6['pro'] ?>₽</span></p>
                            </label>
                        </div>
                        <!-- button next to -->
                        <button onclick="return false" data-toggle-section="finish"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            завершить и купить <i class="fa fa-arrow-right"></i>
                        </button>
                        <button onclick="return false" data-toggle-section="main"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            <i class="fa fa-arrow-left"></i> Вернуться назад
                        </button>
                        <span class="text-center text-white/70 text-sm">Далее будет покупка</span>
                    </div>

                </section>

                <!-- на 12 месяцев -->
                <section data-section="next_12"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-64 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center flex-col gap-4 items-center mb-6">
                        <div class="bg_active relative flex items-center justify-center p-6 aspect-square">
                            <img class="max-h-12" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                        <!-- text -->
                        <div class="flex flex-col items-center justify-center">
                            <h3 class="text-xl font-bold font-sans">Выберите тариф</h3>
                            <div class="text-center text-white/70">От выбранного тарифа зависит цена на ежемесячную
                                оплату!
                            </div>
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-3">
                        <!-- inputs -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">12 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф MYSELF</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p12['basic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="12months_1"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">1 устройство (для себя)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t12['basic'] ?>₽</span></p>
                            </label>
                            <!-- input 2 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">12 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф Family</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p12['clasic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="12months_4"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif2.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">4 устройства (для семьи)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t12['clasic'] ?>₽</span></p>
                            </label>
                            <!-- input 3 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">12 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф Business</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p12['pro'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="12months_10"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif3.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">10 устройств (для бизнеса)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t12['pro'] ?>₽</span></p>
                            </label>
                        </div>
                        <!-- button next to -->
                        <button onclick="return false" data-toggle-section="finish"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            завершить и купить <i class="fa fa-arrow-right"></i>
                        </button>
                        <button onclick="return false" data-toggle-section="main"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            <i class="fa fa-arrow-left"></i> Вернуться назад
                        </button>
                        <span class="text-center text-white/70 text-sm">Далее будет покупка</span>
                    </div>

                </section>

                <!-- ОПЛАТА -->
                <section data-section="finish"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-64 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center flex-col gap-4 items-center mb-6">
                        <div class="bg_active relative flex items-center justify-center p-6 aspect-square">
                            <img class="max-h-12" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                        <!-- text -->
                        <div class="flex flex-col items-center justify-center">
                            <h3 class="text-xl font-bold font-sans">Завершение</h3>
                            <div class="text-center text-white/70">Осталось оплатить собранный вами тариф иначать
                                пользоваться VPN!
                            </div>
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-4">
                        <!-- выбранный тариф -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold" id="finish-period">12 Месяцев</h5>
                                        <p class="text-white/70 font-light" id="finish-tariff">Тариф MYSELF</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold" id="finish-price-per-month"></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light" id="finish-devices">1 устройство (для себя)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70" id="finish-total"></span>₽</p>
                            </label>
                        </div>

                        <div class="flex flex-col items-center justify-center">
                            <h3 class="text-xl font-bold font-sans">Выберите способ
                                оплаты</h3>
                            <div class="flex w-full flex-col wrap gap-4 justify-center items-center mt-4">
                                <label
                                    class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                    Оплатить через:
                                    <div class="flex gap-2 items-center justify-center">
                                        <input type="radio" name="payment-desktop" value="sbp" class="sr-only peer" />
                                        <img decoding="async" loading="lazy" class="h-6"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/payment/sbp.svg"
                                            alt="sbp">
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </label>
                                <label
                                    class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                    Оплатить через:
                                    <div class="flex gap-2 items-center justify-center">
                                        <input type="radio" name="payment-desktop" value="iomoney" class="sr-only peer" />
                                        <img decoding="async" loading="lazy" class="h-6"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/payment/iomoney.svg"
                                            alt="iomoney">
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </label>
                                <label
                                    class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                    Оплатить через:
                                    <div class="flex gap-2 items-center justify-center">
                                        <input type="radio" name="payment-desktop" value="sber" class="sr-only peer" />
                                        <img decoding="async" loading="lazy" class="h-6"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/payment/sberbank.svg"
                                            alt="sber">
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </label>
                                <label
                                    class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                    Оплатить через:
                                    <div class="flex gap-2 items-center justify-center">
                                        <input type="radio" name="payment-desktop" value="tbank" class="sr-only peer" />
                                        <img decoding="async" loading="lazy" class="h-6"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/payment/tbank.svg"
                                            alt="tbank">
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- button next to -->
                        <button type="button"
                            class="payment-submit-btn flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            завершить и купить <i class="fa-solid fa-cart-shopping"></i>
                        </button>

                        <a href="/"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            <i class="fa fa-arrow-left"></i> вернутся на главную
                        </a>

                    </div>
                </section>
            </div>

            <!-- ################# CONTENT MOBILE ####################-->
            <div class="sm:hidden w-full text-white" data-pay-layout="mobile">
                <!-- main -->
                <section data-section="main"
                    class="overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center items-center">
                        <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
                            <img class="max-h-6" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                    </div>
                    <!-- text -->
                    <div class="flex flex-col items-center justify-center">
                        <h3 class="text-xl font-bold font-sans">Выберите подписку</h3>
                        <div class="text-center text-white/70">Получите полную свободу от реклам и запретов!</div>
                    </div>
                    <!-- grid -->
                    <div class="grid grid-cols-2 grid-rows-2 gap-2">
                        <!-- block 1 -->
                        <div class="relative flex flex-col p-2 gap-2 border_light_b border_light_r pb-4">
                            <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                    alt="icon1" loading="lazy">
                                <h4 class="text-lg font-bold font-sans">Аноним</h4>
                            </div>
                            <p class="text-sm text-sans text-white/70 break-all">Маскируем вашу сеть от
                                перехватов</p>
                        </div>
                        <!-- block 2 -->
                        <div class="relative flex flex-col p-2 gap-2 border_light_b pl-4">
                            <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/speed.svg"
                                    alt="icon1" loading="lazy">
                                <h4 class="text-lg font-bold font-sans">Скрость</h4>
                            </div>
                            <p class="text-sm text-sans text-white/70 break-all">Даем скорость более 1000 Mb/s</p>
                        </div>
                        <!-- block 3 -->
                        <div class="relative flex flex-col p-2 gap-2 border_light_r">
                            <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/ads.svg"
                                    alt="icon1" loading="lazy">
                                <h4 class="text-lg font-bold font-sans">Без рекламы</h4>
                            </div>
                            <p class="text-sm text-sans text-white/70 break-all">Блокируем все рекламы в интернете</p>
                        </div>
                        <!-- block 4 -->
                        <div class="relative flex flex-col p-2 gap-2 pl-4">
                            <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/shield.svg"
                                    alt="icon1" loading="lazy">
                                <h4 class="text-lg font-bold font-sans">Скрытность</h4>
                            </div>
                            <p class="text-sm text-sans text-white/70 break-all">Защита ваших данных в сети</p>
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-4">
                        <!-- inputs -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label data-select-section="next_1"
                                class="flex bg-gradient-to-r from-white/20 to-white/5 bg_active justify-between px-6 py-1.5 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                <!-- titile -->
                                <div class="flex flex-col justify-center">
                                    <h5 class="text-xl font-bold">1 Месяц</h5>
                                    <p class="text-white/70 font-light">Ежемесячная от <?= $p1['basic'] ?>₽</p>
                                </div>
                                <!-- part 2 -->
                                <div class="flex items-center justify-center gap-4">
                                    <!-- price -->
                                    <div class="flex flex-col text-center">
                                        <span class="text-3xl font-bold"><?= $p1['pro'] ?></span>
                                        <p class="text-sm">₽/Месяц</p>
                                    </div>
                                    <!-- radio button -->
                                    <div class="flex items-center justify-center">
                                        <input type="radio" name="subscription" value="1month" class="sr-only peer" />
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <!-- input 2 -->
                            <label data-select-section="next_6"
                                class="flex bg-gradient-to-r from-white/20 to-white/5 bg_active justify-between px-6 py-1.5 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                <!-- titile -->
                                <div class="flex flex-col justify-center">
                                    <h5 class="text-xl font-bold">6 Месяцев</h5>
                                    <p class="text-white/70 font-light">Ежемесячная от <?= $p6['basic'] ?>₽</p>
                                </div>
                                <!-- part 2 -->
                                <div class="flex items-center justify-center gap-4">
                                    <!-- price -->
                                    <div class="flex flex-col text-center">
                                        <span class="text-3xl font-bold"><?= $t6['basic'] ?></span>
                                        <p class="text-sm">₽/6 Мес</p>
                                    </div>
                                    <!-- radio button -->
                                    <div class="flex items-center justify-center">
                                        <input type="radio" name="subscription" value="6months" class="sr-only peer" />
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <!-- input 3 -->
                            <label data-select-section="next_12"
                                class="flex bg-gradient-to-r from-white/20 to-white/5 bg_active justify-between px-6 py-1.5 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                <!-- titile -->
                                <div class="flex flex-col justify-center">
                                    <h5 class="text-xl font-bold">12 Месяцев</h5>
                                    <p class="text-white/70 font-light">Ежемесячная от <?= $p12['basic'] ?>₽</p>
                                </div>
                                <!-- part 2 -->
                                <div class="flex items-center justify-center gap-4">
                                    <!-- price -->
                                    <div class="flex flex-col text-center">
                                        <span class="text-3xl font-bold"><?= $t12['basic'] ?></span>
                                        <p class="text-sm">₽/12 Мес</p>
                                    </div>
                                    <!-- radio button -->
                                    <div class="flex items-center justify-center">
                                        <input type="radio" name="subscription" value="12months" class="sr-only peer" />
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <!-- button next to -->
                        <button onclick=" return false" data-toggle-section="main" data-main
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            Выбрать и продолжить <i class="fa fa-arrow-right"></i>
                        </button>
                        <span class="text-center text-white/70 text-sm">Далее будут тарифы</span>
                    </div>

                </section>

                <!-- на 1 месяц -->
                <section data-section="next_1"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center items-center">
                        <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
                            <img class="max-h-6" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                    </div>
                    <!-- text -->
                    <div class="flex flex-col items-center justify-center">
                        <h3 class="text-xl font-bold font-sans">Выберите тариф</h3>
                        <div class="text-center text-white/70">От выбранного тарифа зависит цена на ежемесячную оплату!
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-3">
                        <!-- inputs -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">1 Месяц</h5>
                                        <p class="text-white/70 font-light">Тариф MYSELF</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p1['basic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="1month_1"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">1 устройство (для себя)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t1['basic'] ?>₽</span></p>
                            </label>
                            <!-- input 2 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">1 Месяц</h5>
                                        <p class="text-white/70 font-light">Тариф Family</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p1['clasic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="1month_4"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif2.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">4 устройства (для семьи)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t1['clasic'] ?>₽</span></p>
                            </label>
                            <!-- input 3 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">1 Месяц</h5>
                                        <p class="text-white/70 font-light">Тариф Business</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p1['pro'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="1month_10"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif3.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">10 устройств (для бизнеса)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t1['pro'] ?>₽</span></p>
                            </label>
                        </div>
                        <!-- button next to -->
                        <button onclick="return false" data-toggle-section="finish"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            завершить и купить <i class="fa fa-arrow-right"></i>
                        </button>
                        <button onclick="return false" data-toggle-section="main"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            <i class="fa fa-arrow-left"></i> Вернуться назад
                        </button>
                        <span class="text-center text-white/70 text-sm">Далее будет покупка</span>
                    </div>

                </section>

                <!-- на 6 месяцев -->
                <section data-section="next_6"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center items-center">
                        <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
                            <img class="max-h-6" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                    </div>
                    <!-- text -->
                    <div class="flex flex-col items-center justify-center">
                        <h3 class="text-xl font-bold font-sans">Выберите тариф</h3>
                        <div class="text-center text-white/70">От выбранного тарифа зависит цена на ежемесячную оплату!
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-3">
                        <!-- inputs -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">6 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф MYSELF</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p6['basic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="6months_1"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">1 устройство (для себя)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t6['basic'] ?>₽</span></p>
                            </label>
                            <!-- input 2 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">6 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф Family</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p6['clasic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="6months_4"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif2.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">4 устройства (для семьи)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t6['clasic'] ?>₽</span></p>
                            </label>
                            <!-- input 3 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">6 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф Business</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p6['pro'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="6months_10"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif3.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">10 устройств (для бизнеса)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t6['pro'] ?>₽</span></p>
                            </label>
                        </div>
                        <!-- button next to -->
                        <button onclick="return false" data-toggle-section="finish"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            завершить и купить <i class="fa fa-arrow-right"></i>
                        </button>
                        <button onclick="return false" data-toggle-section="main"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            <i class="fa fa-arrow-left"></i> Вернуться назад
                        </button>
                        <span class="text-center text-white/70 text-sm">Далее будет покупка</span>
                    </div>

                </section>

                <!-- на 12 месяцев -->
                <section data-section="next_12"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center items-center">
                        <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
                            <img class="max-h-6" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                    </div>
                    <!-- text -->
                    <div class="flex flex-col items-center justify-center">
                        <h3 class="text-xl font-bold font-sans">Выберите тариф</h3>
                        <div class="text-center text-white/70">От выбранного тарифа зависит цена на ежемесячную оплату!
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-3">
                        <!-- inputs -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">12 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф MYSELF</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p12['basic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="12months_1"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">1 устройство (для себя)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t12['basic'] ?>₽</span></p>
                            </label>
                            <!-- input 2 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">12 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф Family</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p12['clasic'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="12months_4"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif2.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">4 устройства (для семьи)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t12['clasic'] ?>₽</span></p>
                            </label>
                            <!-- input 3 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold">12 Месяцев</h5>
                                        <p class="text-white/70 font-light">Тариф Business</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold"><?= $p12['pro'] ?></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                        <!-- radio button -->
                                        <div class="flex items-center justify-center">
                                            <input type="radio" name="subscription" value="12months_10"
                                                class="sr-only peer" />
                                            <div
                                                class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif3.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light">10 устройств (для бизнеса)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70"><?= $t12['pro'] ?>₽</span></p>
                            </label>
                        </div>
                        <!-- button next to -->
                        <button onclick="return false" data-toggle-section="finish"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            завершить и купить <i class="fa fa-arrow-right"></i>
                        </button>
                        <button onclick="return false" data-toggle-section="main"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            <i class="fa fa-arrow-left"></i> Вернуться назад
                        </button>
                        <span class="text-center text-white/70 text-sm">Далее будет покупка</span>
                    </div>

                </section>

                <!--  ОПЛАТА -->
                <section data-section="finish"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- icon -->
                    <div class="mobile w-full flex justify-center items-center">
                        <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
                            <img class="max-h-6" decoding="async" loading="lazy"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/crown.svg"
                                alt="Домой" decoding="async">
                        </div>
                    </div>
                    <!-- text -->
                    <div class="flex flex-col items-center justify-center">
                        <h3 class="text-xl font-bold font-sans">Завершение</h3>
                        <div class="text-center text-white/70">Осталось оплатить собранный вами тариф иначать
                            пользоваться VPN!
                        </div>
                    </div>
                    <!-- select tarif -->
                    <div class="flex flex-col gap-4">
                        <!-- выбранный тариф -->
                        <div class="flex flex-col gap-4 buy">
                            <!-- input 1 -->
                            <label
                                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                                <!-- верхний -->
                                <div class="flex justify-between">
                                    <!-- titile -->
                                    <div class=" flex flex-col justify-center">
                                        <h5 class="text-xl font-bold" id="finish-period-m">12 Месяцев</h5>
                                        <p class="text-white/70 font-light" id="finish-tariff-m">Тариф MYSELF</p>
                                    </div>
                                    <!-- part 2 -->
                                    <div class="flex items-center justify-center gap-4">
                                        <!-- price -->
                                        <div class="flex flex-col text-center">
                                            <span class="text-3xl font-bold" id="finish-price-per-month-m"></span>
                                            <p class="text-sm">₽/Месяц</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- нижний -->
                                <div class="relative flex flex-col gap-2 justify-between">
                                    <div class="flex">
                                        <div class="flex gap-2"><img class="max-h-6" decoding="async" loading="lazy"
                                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/buy/tarif1.svg"
                                                alt="icon1" loading="lazy">
                                            <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                                        </div>
                                    </div>
                                    <p class="text-white/70 font-light" id="finish-devices-m">1 устройство (для себя)</p>
                                </div>
                                <p class="absolute bottom-2 right-4 text-sm">Итого: <span
                                        class="text-white/70" id="finish-total-m"></span>₽</p>
                            </label>
                        </div>

                        <div class="flex flex-col items-center justify-center">
                            <h3 class="text-xl font-bold font-sans">Выберите способ
                                оплаты</h3>
                            <div class="flex w-full flex-col wrap gap-4 justify-center items-center mt-4">
                                <label
                                    class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                    Оплатить через:
                                    <div class="flex gap-2 items-center justify-center">
                                        <input type="radio" name="payment-mobile" value="sbp" class="sr-only peer" />
                                        <img decoding="async" loading="lazy" class="h-6"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/payment/sbp.svg"
                                            alt="sbp">
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </label>
                                <label
                                    class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                    Оплатить через:
                                    <div class="flex gap-2 items-center justify-center">
                                        <input type="radio" name="payment-mobile" value="iomoney" class="sr-only peer" />
                                        <img decoding="async" loading="lazy" class="h-6"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/payment/iomoney.svg"
                                            alt="iomoney">
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </label>
                                <label
                                    class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                    Оплатить через:
                                    <div class="flex gap-2 items-center justify-center">
                                        <input type="radio" name="payment-mobile" value="sber" class="sr-only peer" />
                                        <img decoding="async" loading="lazy" class="h-6"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/payment/sberbank.svg"
                                            alt="sber">
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </label>
                                <label
                                    class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                                    Оплатить через:
                                    <div class="flex gap-2 items-center justify-center">
                                        <input type="radio" name="payment-mobile" value="tbank" class="sr-only peer" />
                                        <img decoding="async" loading="lazy" class="h-6"
                                            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/payment/tbank.svg"
                                            alt="tbank">
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- button next to -->
                        <button type="button"
                            class="payment-submit-btn flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            завершить и купить <i class="fa-solid fa-cart-shopping"></i>
                        </button>

                        <a href="/"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            <i class="fa fa-arrow-left"></i> вернутся на главную
                        </a>

                    </div>
                </section>

            </div>
        </main>
        <script>
            const PRICES = <?= json_encode($prices, JSON_UNESCAPED_UNICODE) ?>;
            const TARIFF_META = <?= json_encode($tariffMeta, JSON_UNESCAPED_UNICODE) ?>;
            const HAS_REFERRAL = <?= $hasReferral ? 'true' : 'false' ?>;

            const TARIFF_DATA = {
                '1month_1': {
                    period: 1,
                    tariff: 'basic',
                    periodLabel: '1 Месяц'
                },
                '1month_4': {
                    period: 1,
                    tariff: 'clasic',
                    periodLabel: '1 Месяц'
                },
                '1month_10': {
                    period: 1,
                    tariff: 'pro',
                    periodLabel: '1 Месяц'
                },
                '6months_1': {
                    period: 6,
                    tariff: 'basic',
                    periodLabel: '6 Месяцев'
                },
                '6months_4': {
                    period: 6,
                    tariff: 'clasic',
                    periodLabel: '6 Месяцев'
                },
                '6months_10': {
                    period: 6,
                    tariff: 'pro',
                    periodLabel: '6 Месяцев'
                },
                '12months_1': {
                    period: 12,
                    tariff: 'basic',
                    periodLabel: '12 Месяцев'
                },
                '12months_4': {
                    period: 12,
                    tariff: 'clasic',
                    periodLabel: '12 Месяцев'
                },
                '12months_10': {
                    period: 12,
                    tariff: 'pro',
                    periodLabel: '12 Месяцев'
                },
            };

            // Запоминаем последний выбранный тариф для каждого layout
            var lastSelected = {
                desktop: null,
                mobile: null
            };

            function updateFinishSection(layoutKey, tariffVal) {
                var data = TARIFF_DATA[tariffVal];
                if (!data) return;

                var pricePerMonth = PRICES[data.period][data.tariff];
                var total = pricePerMonth * data.period;
                var meta = TARIFF_META[data.tariff];
                var suffix = layoutKey === 'desktop' ? '' : '-m';

                var $layout = $('[data-pay-layout="' + layoutKey + '"]');
                $layout.find('#finish-period' + suffix).text(data.periodLabel);
                $layout.find('#finish-tariff' + suffix).text('Тариф ' + meta.label);
                $layout.find('#finish-price-per-month' + suffix).text(pricePerMonth);
                $layout.find('#finish-devices' + suffix).text(meta.desc);
                $layout.find('#finish-total' + suffix).text(total + '₽');
            }

            $(document).ready(function() {

                // Выбор периода — запоминаем целевую секцию в [data-main]
                $('[data-select-section]').on('click', function() {
                    var sectionId = $(this).attr('data-select-section');
                    var $layout = $(this).closest('[data-pay-layout]');
                    $layout.find('[data-main]').attr('data-toggle-section', sectionId);
                });

                // При выборе тарифа — сразу запоминаем и обновляем finish
                $(document).on('change', 'input[name="subscription"]', function() {
                    var val = $(this).val();
                    if (!TARIFF_DATA[val]) return;

                    var $layout = $(this).closest('[data-pay-layout]');
                    var layoutKey = $layout.attr('data-pay-layout');
                    lastSelected[layoutKey] = val;
                    updateFinishSection(layoutKey, val);
                });

                // Кнопка "завершить и купить" (переход на finish) — обновляем на случай если JS ещё не отработал
                $(document).on('click', '[data-toggle-section="finish"]', function() {
                    var $layout = $(this).closest('[data-pay-layout]');
                    var layoutKey = $layout.attr('data-pay-layout');

                    // Сначала пробуем взять checked прямо сейчас
                    var checked = $layout.find('input[name="subscription"]:checked').val();

                    // Если не нашли — берём из запомненного
                    var tariffVal = checked || lastSelected[layoutKey];

                    if (tariffVal) {
                        lastSelected[layoutKey] = tariffVal;
                        updateFinishSection(layoutKey, tariffVal);
                    }
                });

                // Также обновляем finish секцию когда она становится видимой (после toggle)
                $(document).on('click', '[data-toggle-section]', function() {
                    var sectionId = $(this).attr('data-toggle-section');
                    if (sectionId === 'finish') {
                        setTimeout(function() {
                            var isDesktop = window.matchMedia('(min-width: 640px)').matches;
                            var layoutKey = isDesktop ? 'desktop' : 'mobile';
                            var $layout = $('[data-pay-layout="' + layoutKey + '"]');
                            
                            var checked = $layout.find('input[name="subscription"]:checked').val();
                            var tariffVal = checked || lastSelected[layoutKey];
                            
                            if (tariffVal) {
                                updateFinishSection(layoutKey, tariffVal);
                            }
                        }, 100);
                    }
                });

                // Кнопка оплатить
                $('.payment-submit-btn').on('click', function(e) {
                    e.preventDefault();

                    var isDesktop = window.matchMedia('(min-width: 640px)').matches;
                    var layoutKey = isDesktop ? 'desktop' : 'mobile';
                    var $layout = $('[data-pay-layout="' + layoutKey + '"]');

                    var selectedTariff = $layout.find('input[name="subscription"]:checked').val() ||
                        lastSelected[layoutKey];
                    var paymentName = layoutKey === 'desktop' ? 'payment-desktop' : 'payment-mobile';
                    var selectedPayment = $layout.find('input[name="' + paymentName + '"]:checked').val();

                    if (!selectedTariff) {
                        alert('Пожалуйста, выберите тариф');
                        return;
                    }
                    if (!selectedPayment) {
                        alert('Пожалуйста, выберите способ оплаты');
                        return;
                    }

                    var tariffInfo = TARIFF_DATA[selectedTariff];
                    var amount = tariffInfo ?
                        PRICES[tariffInfo.period][tariffInfo.tariff] * tariffInfo.period :
                        0;

                    var btn = $(this);
                    var originalText = btn.html();
                    btn.html('<i class="fas fa-spinner fa-spin"></i> Обработка...').prop('disabled', true);

                    $.ajax({
                        url: '/api/payment/create',
                        method: 'POST',
                        contentType: 'application/json',
                        timeout: 60000,
                        data: JSON.stringify({
                            tariff: selectedTariff,
                            paymentMethod: selectedPayment,
                            amount: amount
                        }),
                        success: function(response) {
                            if (response.success) {
                                window.location.href = response.payment_url;
                            } else {
                                alert('Ошибка при создании платежа: ' + response.error);
                                btn.html(originalText).prop('disabled', false);
                            }
                        },
                        error: function(xhr, status) {
                            if (status === 'timeout') {
                                alert('Время ожидания истекло. Попробуйте снова.');
                            } else if (xhr.status === 401) {
                                var r = xhr.responseJSON || {};
                                if (r.redirect) window.location.href = r.redirect;
                            } else {
                                var msg = (xhr.responseJSON && xhr.responseJSON.error) ?
                                    xhr.responseJSON.error :
                                    'Произошла ошибка. Попробуйте ещё раз.';
                                alert(msg);
                            }
                            btn.html(originalText).prop('disabled', false);
                        }
                    });
                });
            });

            function closeQrModal() {
                $('#qr-modal').remove();
            }
        </script>
        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/main/main.js" defer></script>
        <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/theme/main.js" defer></script>
    </div>
</body>

</html>