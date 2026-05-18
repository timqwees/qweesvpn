<?php
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Functions;
Auth::auth();
$site = Functions::site();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Установка</title>
    <!-- fonts + tailwind + normalize + styles + JQuary -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" defer />
    <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet" />
    <link rel="stylesheet" href="/public/assets/styles/style.css" defer>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!--  -->
</head>

<body class="bg-black bg-no-repeat flex item-center w-full overflow-x-hidden">
    <div class="min-h-screen flex flex-col w-full mx-auto">

        <?php include_once 'public/components/header.php' ?>

        <main class="card flex sm:my-2 w-full">
            <!-- КОНЕЦ БЕЗ ИЗМЕНЕНИЙ -->

            <!-- ################# CONTENT DESCKTOP ####################-->
            <div class="setka hidden sm:block w-full text-white">
                <!-- main -->
                <section data-section="main"
                    class="rounded-3xl overflow-hidden relative min-h-[100dvh] flex flex-col gap-2 justify-between box-border">
                    <div
                        class="absolute inset-0 z-0 bg-gradient-to-br from-green-900/15 via-transparent to-emerald-900/8">
                    </div>
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        class="absolute top-0 bottom-0 mx-auto w-full h-full opacity-70 z-0"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/light.svg" alt="backgroud">

                    <!-- text -->
                    <div
                        class="px-4 pt-[15%] mx-auto right-0 left-0 flex flex-col justify-center items-center gap-3 z-10">
                        <div class="p-6 bg-[#181818] aspect-square rounded-[30px]">
                            <img decoding="async" loading="lazy" class="w-24"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg" alt="logo">
                        </div>
                        <!-- <h3 class="font-[qwees-urbanist-regular] text-2xl"><?= htmlspecialchars($site['ООО']) ?> -->
                        </h3>
                        <p class="text-sm text-center w-[70%] break-world">Начнем установку VPN на ваше устройство
                            <span class="text-green-200 text-lg">
                                <?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['os']) ?></span>
                        </p>
                    </div>

                    <div class="px-4 flex flex-col gap-4 mb-6 justify-center items-center w-full z-10">
                        <button data-toggle-section="start"
                            class="max-w-[50%] bg-white cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-3 w-[90%]">Начать
                            установку</button>
                        <button onclick="window.open('/', '_self')"
                            class="max-w-[50%] bg-transparent border-white border text-white cursor-pointer flex justify-center text-lg rounded-xl flex p-3 w-[90%]">Вернутся</button>
                        <p class="text-[13px]">Установка пройдет в 2 шага</p>
                    </div>

                </section>

                <!-- start -->
                <section data-section="start"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-end pb-4 box-border w-full min-h-[100dvh] bg-black">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="flex flex-col justify-between px-32 pb-4 z-10">
                        <div class="flex flex-col justify-start gap-16">

                            <div class="flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                    alt="logo" class="w-16 aspert-square">
                                <h3 class="text-2xl font-medium">Установка приложения</h3>
                                <p class="text-[13px] text-white/70">Первым делом установим приложения, которое
                                    обеспечит связь с VPN
                                    сервером.
                                </p>
                            </div>

                            <div class="flex gap-4 w-full">
                                <a href="<?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['url']) ?>"
                                    target="_blank"
                                    class="w-full bg-transparent border border-solid border-white cursor-pointer flex justify-center text-white text-lg rounded-xl flex p-2 w-[90%]">
                                    Скачать</a>
                                <button data-toggle-section="finish"
                                    class="w-full bg-white cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-2 w-[90%]">
                                    Далее</button>
                            </div>
                        </div>
                    </div>

                </section>

                <!-- finish -->
                <section data-section="finish"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-end pt-[95px] pb-4 box-border w-full min-h-[100dvh] bg-black">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="flex flex-col justify-between px-32 pb-4 z-10">
                        <div class="flex flex-col justify-start gap-16">

                            <div class="flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                    alt="logo" class="w-16 aspert-square">
                                <h3 class="text-2xl font-medium">Установим ваш VPN</h3>
                                <p class="text-[13px] text-white/70">Установим приобретенный вами VPN-ключ в приложение
                                    и завершим на
                                    этом настройку
                                </p>
                            </div>

                            <div class="flex gap-4 w-full">
                                <a target="_blank"
                                    href="<?= htmlspecialchars('happ://add/' . (new Setting\Route\Function\Controllers\Client\GetUser())->getSubscription()) ?>"
                                    class="w-full bg-transparent border border-solid border-white cursor-pointer flex justify-center items-center text-white text-2sm rounded-xl flex p-2 w-[90%]">
                                    Установить VPN</a>
                                <button onclick="window.location.href = '/';"
                                    class="w-full bg-white cursor-pointer flex justify-center items-center text-black text-lg rounded-xl flex p-2 w-[90%]">
                                    Завершить</button>
                            </div>
                        </div>
                    </div>

                </section>
            </div>

            <!-- ################# CONTENT MOBILE ####################-->
            <div class="sm:hidden w-full text-white">
                <!-- main -->
                <section data-section="main"
                    class="setka overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh]">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        class="absolute top-0 bottom-0 mx-auto w-full h-full opacity-70 z-0"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/light.svg" alt="backgroud">

                    <!-- text -->
                    <div
                        class="px-4 pt-[30%] mx-auto right-0 left-0 flex flex-col justify-center items-center gap-3 z-10">
                        <div class="p-4 bg-[#181818] aspect-square rounded-[40px]">
                            <img decoding="async" loading="lazy" class="w-24"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg" alt="logo">
                        </div>
                        <h3 class="font-[qwees-poppins-semibold] text-2xl"><?= htmlspecialchars($site['ООО']) ?> VPN
                        </h3>
                        <p class="text-sm text-center w-[70%] break-world">Начнем установку VPN на ваше устройство
                            <span class="text-green-200 text-lg">
                                <?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['os']) ?></span>
                        </p>
                    </div>

                    <div class="px-4 flex flex-col gap-4 mb-6 justify-center items-center w-full z-10">
                        <button data-toggle-section="start"
                            class="bg-white cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-3 w-[90%]">Начать
                            установку</button>
                        <button onclick="window.open('/', '_self')"
                            class="bg-transparent border-white border text-white cursor-pointer flex justify-center text-lg rounded-xl flex p-3 w-[90%]">Вернутся</button>
                        <p class="text-[13px]">Установка пройдет в 2 шага</p>
                    </div>

                </section>

                <!-- start -->
                <section data-section="start"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-end items-center pt-[95px] pb-4 box-border w-full min-h-[100dvh] bg-black">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="flex flex-col justify-between items-center px-6 pb-4 z-10">
                        <div class="flex flex-col items-center justify-start gap-16">

                            <div class="flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                    alt="logo" class="w-10 aspert-square">
                                <h3 class="text-2xl font-medium">Установка приложения</h3>
                                <p class="text-[13px] text-white/70">Первым делом установим приложения, которое
                                    обеспечит связь с VPN
                                    сервером.
                                </p>
                            </div>

                            <div class="flex gap-4 w-full">
                                <a href="<?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['url']) ?>"
                                    target="_blank"
                                    class="w-full bg-transparent border border-solid border-white cursor-pointer flex justify-center text-white text-lg rounded-xl flex p-2 w-[90%]">
                                    Скачать</a>
                                <button data-toggle-section="finish"
                                    class="w-full bg-white cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-2 w-[90%]">
                                    Далее</button>
                            </div>
                        </div>
                    </div>

                </section>

                <!-- finish -->
                <section data-section="finish"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-end items-center pt-[95px] pb-4 box-border w-full min-h-[100dvh] bg-black">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="flex flex-col justify-between items-center px-6 pb-4 z-10">
                        <div class="flex flex-col items-center justify-start gap-16">

                            <div class="flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                    alt="logo" class="w-10 aspert-square">
                                <h3 class="text-2xl font-medium">Установим ваш VPN</h3>
                                <p class="text-[13px] text-white/70">Установим приобретенный вами VPN-ключ в приложение
                                    и завершим на
                                    этом настройку
                                </p>
                            </div>

                            <div class="flex gap-4 w-full">
                                <a target="_blank"
                                    href="<?= htmlspecialchars('happ://add/' . (new Setting\Route\Function\Controllers\Client\GetUser())->getSubscription()) ?>"
                                    class="w-full bg-transparent border border-solid border-white cursor-pointer flex justify-center items-center text-white text-2sm rounded-xl flex p-2 w-[90%]">
                                    Установить VPN</a>
                                <button onclick="window.location.href = '/';"
                                    class="w-full bg-white cursor-pointer flex justify-center items-center text-black text-lg rounded-xl flex p-2 w-[90%]">
                                    Завершить</button>
                            </div>
                        </div>
                    </div>

                </section>
        </main>
    </div>

    <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/main/main.js"></script>
    <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/theme/main.js"></script>
</body>

</html>