<?php Setting\Route\Function\Controllers\Auth\Auth::auth(); ?>
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
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet" />
    <link rel="stylesheet" href="/public/assets/styles/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!--  -->
</head>

<body class="bg-black bg-no-repeat flex item-center w-full overflow-x-hidden">
    <div class="min-h-screen flex flex-col w-full container mx-auto">
        <!-- navbar top -->
        <header class="fixed z-50 left-0 top-2 right-0 h-16 px-6 flex items-center justify-between">
            <!-- refresh -->
            <i class="fa fa-refresh text-white"></i>
            <!-- logo -->
            <div class="flex items-center gap-2">
                <img class="w-auto h-7 object-contain" src="/public/assets/images/icons/logo/qweesvpn.svg"
                    alt="qweesvpn">
                <h2 class="text-white text-xl font-[qwees-poppins-medium] tracking-wider">QWEES <span
                        class="text-green-400">VPN</span></h2>
            </div>
            <!-- version -->
            <span class="text-white text-sm">v1.0.0</span>
        </header>
        <main class="flex sm:my-2 w-full">
            <!-- КОНЕЦ БЕЗ ИЗМЕНЕНИЙ -->

            <!-- ################# CONTENT DESCKTOP ####################-->
            <div class="hidden sm:block w-full text-white">
                <!-- содержание -->
            </div>

            <!-- ################# CONTENT MOBILE ####################-->
            <div class="sm:hidden w-full text-white">
                <!-- main -->
                <section data-section="main"
                    class="overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- background -->
                    <img class="absolute top-0 bottom-0 mx-auto w-full h-full opacity-70 z-0"
                        src="/public/assets/images/background/light.svg" alt="backgroud">

                    <!-- text -->
                    <div
                        class="px-4 py-[40%] mx-auto right-0 left-0 flex flex-col justify-center items-center gap-3 z-10">
                        <div class="p-8 bg-[#181818] aspect-square rounded-[30px]">
                            <img class="w-14" src="/public/assets/images/icons/logo/qweesvpn.svg" alt="logo">
                        </div>
                        <h3 class="font-[qwees-poppins-semibold] text-2xl">QWEES VPN</h3>
                        <p class="text-sm text-center w-[70%] break-world">Начнем установку VPN на ваше устройство
                            <?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['os']) ?>
                        </p>
                    </div>

                    <div class="px-4 flex flex-col gap-4 mb-6 justify-center items-center w-full z-10">
                        <button data-toggle-section="start"
                            class="bg-white cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-3 w-[90%]">Начать
                            установку</button>
                        <p class="text-[13px]">Установка пройдет в 2 шага</p>
                    </div>

                </section>

                <!-- start -->
                <section data-section="start"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-end items-center pt-[95px] pb-4 box-border w-full min-h-[100dvh] bg-black">
                    <!-- background -->
                    <img src="/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="flex flex-col justify-between items-center px-6 pb-4 z-10">
                        <div class="flex flex-col items-center justify-start gap-16">

                            <div class="flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img src="/public/assets/images/icons/logo/qweesvpn.svg" alt="logo"
                                    class="w-10 aspert-square">
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
                    <img src="/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="flex flex-col justify-between items-center px-6 pb-4 z-10">
                        <div class="flex flex-col items-center justify-start gap-16">

                            <div class="flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img src="/public/assets/images/icons/logo/qweesvpn.svg" alt="logo"
                                    class="w-10 aspert-square">
                                <h3 class="text-2xl font-medium">Установим ваш VPN</h3>
                                <p class="text-[13px] text-white/70">Установим приобретенный вами VPN-ключ в приложение
                                    и завершим на
                                    этом настройку
                                </p>
                            </div>

                            <div class="flex gap-4 w-full">
                                <a target="_blank"
                                    href="<?= htmlspecialchars('' . (new Setting\Route\Function\Controllers\Client\getUser())->getSubscription()) ?>"
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

    <script src="/public/assets/scripts/main/main.js"></script>
    <script src="/public/assets/scripts/theme/main.js"></script>
</body>

</html>