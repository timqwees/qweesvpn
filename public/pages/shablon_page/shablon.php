<?php
use Setting\Route\Function\Controllers\Auth\Auth;
Auth::auth();
?>
<!DOCTYPE html>
<html lang="ru" class="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Оплата</title>

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
                <section
                    class="overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- содержание -->
                </section>
            </div>

        </main>
        <script src="/public/assets/scripts/main/main.js" defer></script>
        <script src="/public/assets/scripts/theme/main.js" defer></script>
    </div>
</body>

</html>