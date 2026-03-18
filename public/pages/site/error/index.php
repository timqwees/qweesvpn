<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoraVPN — Ошибка оплаты</title>
  <link rel="icon" type="image/x-icon" href="/static/favicon.ico">
  <link rel="stylesheet" href="/public/pages/site/error/style.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- particles.js library for the effect -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <style>
    ::-webkit-scrollbar {
      width: 0;
    }

    #particles-js {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100dvh;
      z-index: 0;
    }
  </style>
</head>

<body class="bg-white text-gray-900 relative min-h-screen">
  <div id="particles-js"></div>
  <nav class="bg-white border-b border-gray-100 fixed w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <a href="/" class="flex-shrink-0 flex items-center">
            <span class="text-2xl font-medium text-gray-900">CoraVPN</span>
          </a>
        </div>
        <div class="hidden md:block">
          <div class="ml-4 flex items-center md:ml-6">
            <a href="https://t.me/coravpn_bot/CoraVPN"
              class="ml-3 bg-gray-900 hover:bg-gray-700 text-white px-6 py-2 rounded-md font-medium" target="_blank">
              В приложение
            </a>
          </div>
        </div>
        <div class="-mr-2 flex md:hidden">
          <button type="button"
            class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-900 focus:outline-none">
            <i class="fas fa-bars w-6 h-6"></i>
          </button>
        </div>
      </div>
    </div>
    <!-- Мобильное меню -->
    <div class="mobile-menu hidden md:hidden bg-white z-[999]">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <a href="/" class="text-gray-500 hover:text-gray-900 block px-3 py-2 text-base font-medium">Главная</a>
        <a href="https://t.me/coravpn_bot/CoraVPN"
          class="text-gray-500 hover:text-gray-900 block px-3 py-2 text-base font-medium" target="_blank">Приложение</a>
        <a href="https://t.me/CoraVPNBot"
          class="text-gray-500 hover:text-gray-900 block px-3 py-2 text-base font-medium" target="_blank">Бот</a>
        <a href="https://t.me/spcoravpn_bot"
          class="text-gray-500 hover:text-gray-900 block px-3 py-2 text-base font-medium" target="_blank">Поддержка</a>
      </div>
    </div>
  </nav>

  <!-- Ошибка оплаты -->
  <main class="flex flex-col min-h-screen justify-center items-center px-4 py-10 relative z-10">
    <div class="bg-white/90 rounded-lg shadow-lg max-w-lg w-full flex flex-col items-center py-12 px-6 mt-32 mb-12">
      <div class="bg-red-100 rounded-full p-5 mb-6">
        <i class="fas fa-times-circle text-red-500 text-5xl"></i>
      </div>
      <h1 class="text-3xl md:text-4xl font-semibold text-gray-900 mb-4">
        Ошибка оплаты
      </h1>
      <p class="text-gray-600 text-lg mb-4 text-center">
        К сожалению, оплата не была завершена.
      </p>
      <ul class="text-gray-500 text-base mb-8 text-left max-w-md">
        <li class="mb-2 flex items-center"><i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>Платёж
          был отклонён вашим банком или платёжной системой.</li>
        <li class="mb-2 flex items-center"><i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>Возможно, ваша
          сессия истекла или
          заказ уже оплачен другим способом.</li>
        <li class="mb-2 flex items-center"><i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>Если это
          ошибка — свяжитесь с поддержкой, чтобы мы помогли разобраться.</li>
      </ul>
      <div class="flex flex-col sm:flex-row gap-4 w-full justify-center">
        <a href="https://t.me/coravpn_bot/CoraVPN"
          class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-8 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
          <i class="fab fa-telegram-plane"></i> На главное меню
        </a>
        <a href="https://t.me/spcoravpn_bot" target="_blank"
          class="bg-gray-900 hover:bg-gray-700 text-white px-8 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
          <i class="fab fa-telegram-plane"></i> Поддержка
        </a>
      </div>
      <div class="text-xs text-gray-400 mt-6 text-center">
        Если ничего не помогает — опишите ошибку в поддержку и приложите скриншот сообщения<br>
        <span class="inline-block py-1 px-2 bg-gray-100 rounded mt-2">CoraVPN</span>
      </div>
    </div>
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
        Все услуги, поддержка и управление сервисом доступны только через <a href="https://t.me/coravpn_bot/CoraVPN"
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
    // particles.js init
    window.addEventListener('DOMContentLoaded', () => {
      if (window.particlesJS) {
        particlesJS("particles-js", {
          "particles": {
            "number": {
              "value": 100,
              "density": { "enable": true, "value_area": 900 }
            },
            "color": { "value": "#fd3a47" },
            "shape": {
              "type": "circle",
              "stroke": { "width": 0, "color": "#000" }
            },
            "opacity": { "value": 0.25, "random": true },
            "size": { "value": 5, "random": true },
            "line_linked": {
              "enable": true,
              "distance": 150,
              "color": "#fd3a47",
              "opacity": 0.25,
              "width": 2
            },
            "move": {
              "enable": true,
              "speed": 1.3,
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
