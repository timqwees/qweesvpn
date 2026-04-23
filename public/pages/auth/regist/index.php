<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Auth</title>

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

    <!-- Noscript fallback -->
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/noscript.css">
    </noscript>
</head>

<body class="bg-black flex item-center justify-center">
    <div class="min-h-screen flex flex-col justify-center items-center container">

        <!-- LOGO CONTENT -->
        <div class="flex flex-col justify-center items-center gap-2">
            <img src="/public/assets/images/icons/logo/qweesvpn.svg" alt="QweesVPN" loading="lazy">
            <h2 class="text-white text-3xl font-[qwees-urbanist-medium] tracking-wider">QWEES <span
                    class="text-green-400">VPN</span></h2>
            <p class="text-white text-sm font-sans letter-specing">Добро пожаловать!</p>
        </div>

        <!-- FORM CONTENT -->
        <div
            class="flex flex-col bg-[#151414] rounded-lg p-6 py-10 w-full max-w-[376px] border border-solid border-[0.5px] border-white/20 my-8 gap-4">

            <form action="/auth/regist" method="POST">

                <!-- part 1 -->
                <div class="flex flex-col gap-4 w-full" id="part1">
                    <!-- INPUT = first_name -->
                    <label for="first_name" class="text-white/70 text-2xl font-sans">Имя</label>
                    <div class="relative flex">
                        <i class="fa fas fa-phone text-white absolute left-5 top-[35%]"></i>
                        <input type="text" id="first_name" name="first_name" placeholder="Tim" required
                            class="w-full text-white rounded-2xl p-3 border border-solid border-white/20 bg-transparent pl-14 text-lg font-sans outline-none">
                    </div>
                    <!-- INPUT = last_name -->
                    <label for="last_name" class="text-white/70 text-2xl font-sans">Фамилия</label>
                    <div class="relative flex">
                        <i class="fa fas fa-phone text-white absolute left-5 top-[35%]"></i>
                        <input type="text" id="last_name" name="last_name" placeholder="Qwees" required
                            class="w-full text-white rounded-2xl p-3 border border-solid border-white/20 bg-transparent pl-14 text-lg font-sans outline-none">
                    </div>
                    <!-- INPUT = email -->
                    <label for="email" class="text-white/70 text-2xl font-sans">Почта</label>
                    <div class="relative flex">
                        <i class="fa fas fa-phone text-white absolute left-5 top-[35%]"></i>
                        <input type="email" id="email" name="email" placeholder="your@example.com" required
                            class="w-full text-white rounded-2xl p-3 border border-solid border-white/20 bg-transparent pl-14 text-lg font-[qwees-poppins-regular] outline-none">
                    </div>
                    <p class="font-sans text-white hidden p-2" id="message_status"></p>
                    <!-- BUTTON -->
                    <button data-button="email" onclick="return false"
                        class="bg-[#6BFF5B] p-2 text-center rounded-2xl font-sans" disabled>Продолжить</button>

                    <!-- OTHER -->
                    <div class="flex justify-center items-center gap-6">
                        <div class="flex flex-1 bg-white/50 h-[1px]"></div>
                        <p class="text-white text-xl font-sans uppercase">или</p>
                        <div class="flex flex-1 bg-white/50 h-[1px]"></div>
                    </div>
                    <a href="/auth/login"
                        class="flex gap-4 justify-center items-center w-full text-white rounded-2xl p-3 border border-solid border-[#6BFF5B]/20 bg-transparent text-lg font-sans">
                        <i class="fa fa-plus text-[#6BFF5B]"></i> Войти</a>
                </div>

                <!-- part 2 -->
                <div class="hidden" id="part2">
                    <div class="flex flex-col gap-4 w-full">
                        <!-- INPUT -->
                        <label for="verefy" class="text-white/70 text-2xl font-sans">Отправили код на почту</label>
                        <div class="flex flex-col md:flex-row justify-center items-center gap-4">
                            <div class="flex-1 relative">
                                <input type="text" id="verefy" placeholder="****" maxlength="4" required
                                    class="w-full text-center tracking-[20px] pl-4 py-3 text-white border-b border-solid border-white/20 bg-transparent text-2xl font-[qwees-poppins-regular] outline-none">
                            </div>
                            <!-- BUTTON -->
                            <button data-button="verefy" type="submit"
                                class="w-full flex-1 bg-[#6BFF5B] p-2 text-center rounded-2xl font-sans"
                                disabled>Войти</button>
                        </div>
                        <p class="font-sans text-white hidden p-2" id="verefy_status"></p>
                    </div>
                </div>

            </form>

        </div>

        <!-- customized scripts -->
        <script defer src="/public/assets/scripts/auth/regist/main.js"></script>
        <!--  -->

    </div>
</body>

</html>