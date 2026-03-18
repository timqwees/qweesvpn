<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoraVPN — Пользовательское соглашение</title>
    <link rel="icon" type="image/x-icon" href="/static/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        ::-webkit-scrollbar {
            width: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        #particles-js {
            position: fixed;
            z-index: 0;
            width: 100vw;
            height: 100dvh;
            left: 0;
            top: 0;
            pointer-events: none;
        }

        body {
            min-height: 100vh;
        }

        main:focus {
            outline: none;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.81);
            backdrop-filter: blur(12px) saturate(1.4);
            -webkit-backdrop-filter: blur(12px) saturate(1.4);
            box-shadow: 0 10px 32px 0 rgba(30, 40, 90, 0.12), 0 1.5px 6px 0 rgba(70, 120, 210, 0.12);
            border: 1.5px solid rgba(100, 128, 192, 0.13);
        }

        .official-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            background: linear-gradient(90deg, #2c81fe, #7fa0fe 85%);
            color: #fff;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 12px 0 rgba(64, 118, 240, 0.16);
            gap: 0.5rem;
            letter-spacing: .01em;
        }

        .highlight {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.13), rgba(29, 78, 216, 0.07));
            border-radius: 0.5em;
            padding: 0.30em 0.65em;
            font-weight: 600;
            color: #2241a4;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[#f6fafd] via-[#edf0fd] to-[#ecf0fc] text-gray-900 relative min-h-screen">
    <!-- particles.js container -->
    <div id="particles-js"></div>
    <!-- Навигация -->
    <nav class="bg-white/70 backdrop-blur border-b border-gray-200 fixed w-full z-50 transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex-shrink-0 flex items-center gap-2" aria-label="CoraVPN На главную">
                        <img src="/public/assets/logo/logo.png" alt="Логотип CoraVPN"
                            class="h-10 w-auto mr-2 hidden md:inline transition-transform duration-300 hover:scale-110">
                        <span class="text-2xl font-bold text-blue-900 tracking-widest drop-shadow">CoraVPN</span>
                    </a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <a href="/"
                            class="ml-3 bg-blue-800 hover:bg-blue-700 text-white px-6 py-2 shadow font-semibold rounded-md transition duration-200">
                            На главную
                        </a>
                    </div>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <button type="button"
                        class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-900 focus:outline-none"
                        aria-label="Открыть меню">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Мобильное меню -->
        <div class="mobile-menu hidden md:hidden bg-white/80 backdrop-blur">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/" class="text-gray-700 hover:text-blue-700 block px-3 py-2 text-base font-medium">На
                    главную</a>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <main id="user-agreement" tabindex="-1"
        class="relative z-10 pt-28 pb-20 min-h-screen flex justify-center items-center">
        <article class="glass-effect max-w-3xl w-full mx-4 md:mx-auto p-6 md:p-12 rounded-3xl shadow-2xl">
            <div class="official-badge">
                <i class="fa-solid fa-certificate text-lg text-white drop-shadow"></i>
                Официальный документ CoraVPN
            </div>
            <h1 class="text-3xl md:text-4xl font-black mb-4 text-center text-blue-950 tracking-tight drop-shadow">
                Пользовательское соглашение и политика доступности</h1>
            <p class="text-gray-700 mb-10 text-center text-base md:text-lg font-medium">
                Последнее обновление: <span class="highlight">1 июня 2024</span>
            </p>

            <section class="mb-8">
                <h2 class="text-lg md:text-xl font-semibold mb-2 text-blue-800 flex items-center gap-2"><i
                        class="fa-solid fa-gavel text-blue-500"></i> 1. Общие положения</h2>
                <p class="text-gray-700 leading-relaxed">
                    Настоящее <span class="highlight">Пользовательское соглашение</span> (далее — «Соглашение»)
                    регламентирует отношения между сервисом <span class="font-semibold">CoraVPN</span> и Пользователем
                    по вопросам использования сервиса и прав доступа.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-lg md:text-xl font-semibold mb-2 text-blue-800 flex items-center gap-2"><i
                        class="fa-solid fa-globe text-blue-500"></i> 2. Доступность сервиса</h2>
                <ul class="list-disc pl-6 text-gray-700 mb-2 space-y-2 leading-relaxed">
                    <li>CoraVPN доступен на<strong class="highlight"> всех современных устройствах</strong>, включая
                        компьютеры, смартфоны и планшеты. Для доступа требуется любой актуальный браузер и поддержка
                        Telegram.</li>
                    <li>Все основные функции предоставляются исключительно через <a
                            href="https://t.me/coravpn_bot/CoraVPN" class="underline text-blue-700 font-semibold"
                            target="_blank">Telegram-приложение</a> и <a href="https://t.me/CoraVPNBot"
                            class="underline text-blue-700 font-semibold" target="_blank">@coravpn_bot</a>.</li>
                    <li>Гарантируется кроссплатформенность и высокая доступность сервиса 24/7.</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg md:text-xl font-semibold mb-2 text-blue-800 flex items-center gap-2"><i
                        class="fa-solid fa-user-shield text-blue-500"></i> 3. Регистрация и авторизация</h2>
                <ul class="list-disc pl-6 text-gray-700 mb-2 space-y-2 leading-relaxed">
                    <li>Пользователь подтверждает согласие с условиями, начиная использовать сервис.</li>
                    <li>Регистрация и идентификация осуществляются исключительно через Telegram, что обеспечивает
                        безопасность личных данных.</li>
                    <li>Пользователю рекомендуется использовать двухфакторную аутентификацию и самостоятельно
                        контролировать безопасность аккаунта.</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg md:text-xl font-semibold mb-2 text-blue-800 flex items-center gap-2"><i
                        class="fa-solid fa-scale-balanced text-blue-500"></i> 4. Условия использования</h2>
                <ul class="list-disc pl-6 text-gray-700 mb-2 space-y-2 leading-relaxed">
                    <li>Запрещено использовать CoraVPN для противоправных целей и обхода законодательства страны
                        проживания.</li>
                    <li>Обязуетесь использовать сервис честно и в рамках действующего законодательства.</li>
                    <li>Сервис вправе ограничить или прекратить доступ при нарушении условий соглашения.</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg md:text-xl font-semibold mb-2 text-blue-800 flex items-center gap-2"><i
                        class="fa-solid fa-user-secret text-blue-500"></i> 5. Сбор и использование данных</h2>
                <ul class="list-disc pl-6 text-gray-700 mb-2 space-y-2 leading-relaxed">
                    <li><span class="highlight">CoraVPN не ведет журналов активности (No-Log Policy)</span> и не хранит
                        трафик пользователей.</li>
                    <li>Минимальные технические сведения обрабатываются только в целях эффективной работы сервиса и
                        поддержки.</li>
                    <li>Подробнее см. <a href="#" class="underline text-blue-700 font-semibold">Политику
                            конфиденциальности</a>.</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg md:text-xl font-semibold mb-2 text-blue-800 flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-blue-500"></i> 6. Платные услуги и возвраты
                </h2>
                <ul class="list-disc pl-6 text-gray-700 mb-2 space-y-2 leading-relaxed">
                    <li>Оплата подписок производится только через официальный
                        <a href="https://t.me/CoraVPNBot" class="underline text-blue-700 font-semibold"
                            target="_blank">Telegram-бот</a>
                        или приложение.
                    </li>
                    <li>Информация о пробных периодах, акциях и возможностях возврата доступна в вашем личном кабинете
                        Telegram или в поддержке.</li>
                    <li>
                        <span class="highlight">Возврат денежных средств, как правило, не производится</span> после
                        оплаты и активации услуги,
                        за исключением особых случаев, предусмотренных внутренней политикой и условиями сервиса.
                        Все платежи считаются окончательными и не подлежат возврату, если иное прямо не указано в
                        индивидуальном порядке или по согласованию с поддержкой.
                    </li>
                    <li>
                        Все оплаты защищены и прозрачны. Перед оплатой рекомендуется внимательно ознакомиться с
                        условиями предоставления услуг и возможностью возврата.
                    </li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg md:text-xl font-semibold mb-2 text-blue-800 flex items-center gap-2"><i
                        class="fa-solid fa-server text-blue-500"></i> 7. Техническая доступность</h2>
                <ul class="list-disc pl-6 text-gray-700 mb-2 space-y-2 leading-relaxed">
                    <li>Команда <span class="font-bold">CoraVPN</span> обеспечивает непрерывную работу сервиса 24/7,
                        включая резервную инфраструктуру.</li>
                    <li>Техподдержка доступна круглосуточно через Telegram.</li>
                    <li>В случае вопросов обратитесь через <a href="https://t.me/CoraVPNBot" target="_blank"
                            class="underline text-blue-700 font-semibold">@coravpn_bot</a>.</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg md:text-xl font-semibold mb-2 text-blue-800 flex items-center gap-2"><i
                        class="fa-solid fa-pen-fancy text-blue-500"></i> 8. Изменение условий соглашения</h2>
                <ul class="list-disc pl-6 text-gray-700 mb-2 space-y-2 leading-relaxed">
                    <li>Сервис вправе изменять и дополнять настоящее соглашение без отдельного уведомления.</li>
                    <li>Актуальная версия всегда публикуется на официальном сайте.</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-lg md:text-xl font-semibold mb-2 text-blue-800 flex items-center gap-2"><i
                        class="fa-solid fa-comments text-blue-500"></i> 9. Связь и поддержка</h2>
                <ul class="list-disc pl-6 text-gray-700 mb-2 space-y-2 leading-relaxed">
                    <li>По всем вопросам пользования, доступности и ваших прав — обращайтесь в официальную поддержку
                        CoraVPN в Telegram.</li>
                    <li>Контактные адреса: <a href="https://t.me/CoraVPNBot"
                            class="underline text-blue-700 font-semibold" target="_blank">@coravpn_bot</a> и <a
                            href="https://t.me/coravpn_bot/CoraVPN" class="underline text-blue-700 font-semibold"
                            target="_blank">Telegram-приложение</a>.</li>
                </ul>
            </section>

            <hr class="my-10 border-blue-200">

            <div class="flex flex-col items-center gap-3 text-center mb-2">
                <span class="text-gray-500 text-sm">Настоящее соглашение является официальным документом компании
                    CoraVPN.</span>
                <span class="text-gray-700 text-base">Используя сервис, вы <span class="highlight">полностью принимаете
                        все условия и политику конфиденциальности CoraVPN</span>.</span>
                <span class="text-gray-400 text-xs mt-2">
                    Для предотвращения мошенничества пользуйтесь только официальными Telegram-каналами и
                    приложениями.<br>
                    Последняя актуальная версия этого документа всегда размещена на этой странице.
                </span>
            </div>
        </article>
    </main>

    <!-- Футер -->
    <footer
        class="bg-gradient-to-r from-gray-950 via-gray-900 to-gray-800 text-gray-300 pt-10 pb-8 text-sm mt-16 shadow-2xl border-t border-gray-800">
        <div class="max-w-7xl mx-auto flex flex-col items-center px-4">
            <div class="flex items-center gap-3 mb-4">
                <img src="/public/assets/logo/logo.png" alt="Логотип CoraVPN" class="h-8 w-auto drop-shadow-lg">
                <span class="text-2xl font-bold tracking-widest drop-shadow">CoraVPN</span>
            </div>
            <p class="text-blue-100/90 text-center mb-4 max-w-2xl">
                Все услуги, поддержка и управление сервисом доступны только через <a
                    href="https://t.me/coravpn_bot/CoraVPN"
                    class="underline text-blue-200 hover:text-white font-semibold" target="_blank">приложение</a> и <a
                    href="https://t.me/CoraVPNBot" class="underline text-blue-200 hover:text-white font-semibold"
                    target="_blank">бота</a> в Telegram. <br>
                CoraVPN работает на принципах <span
                    class="highlight bg-blue-900/15 text-white font-semibold px-1.5 py-0.5">безопасности</span>, <span
                    class="highlight bg-blue-900/15 text-white font-semibold px-1.5 py-0.5">конфиденциальности</span> и
                <span class="highlight bg-blue-900/15 text-white font-semibold px-1.5 py-0.5">официальности</span>.<br>
            </p>
            <div
                class="border-t border-blue-600/40 mt-4 pt-4 text-center text-blue-200 w-full flex flex-col items-center gap-1">
                <span>
                    &copy;
                    <script>document.write(new Date().getFullYear());</script>
                    <span class="font-bold">CoraVPN</span>. Все права защищены.
                </span>
                <span class="text-xs opacity-80">
                    Данный сайт носит исключительно информационный характер и не является публичной офертой.
                </span>
            </div>
        </div>
    </footer>

    <script>
        // Мобильное меню
        document.querySelectorAll('.mobile-menu-button').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelector('.mobile-menu').classList.toggle('hidden');
            });
        });

        // particles.js
        window.addEventListener('DOMContentLoaded', () => {
            if (window.particlesJS) {
                particlesJS("particles-js", {
                    "particles": {
                        "number": { "value": 60, "density": { "enable": true, "value_area": 900 } },
                        "color": { "value": "#2641b7" },
                        "shape": { "type": "circle", "stroke": { "width": 0, "color": "#000" }, },
                        "opacity": { "value": 0.18, "random": true },
                        "size": { "value": 4, "random": true },
                        "line_linked": { "enable": true, "distance": 160, "color": "#7fa0fe", "opacity": 0.12, "width": 1.3 },
                        "move": {
                            "enable": true,
                            "speed": 1.1,
                            "direction": "none",
                            "random": true,
                            "straight": false,
                            "out_mode": "out",
                            "bounce": false
                        }
                    },
                    "retina_detect": true
                });
            }
        });
    </script>
</body>

</html>