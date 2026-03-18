<?php
use Setting\Route\Function\Functions;
$price = (new Functions())->isPrice();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoraVPN — Ознакомительный сайт VPN</title>
  <link rel="icon" type="image/x-icon" href="/static/favicon.ico">
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- particles.js library for the effect -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <style>
    ::-webkit-scrollbar {
      width: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    #particles-js {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100dvh;
      z-index: 0;
    }

    /* Высокая, 100% ширина для саппорта-блока */
    .support-stat {
      width: 100%;
      max-width: 550px;
      margin-left: auto;
      margin-right: auto;
    }

    /* Для секций: убираем лишние внешние отступы между секциями */
    section {
      margin-bottom: 0;
    }
  </style>
</head>

<body class="bg-white text-gray-900 relative">
  <!-- particles.js container -->
  <div id="particles-js"></div>
  <!-- Навигация -->
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
            <a href="#pricing" class="ml-3 bg-gray-900 hover:bg-gray-700 text-white px-6 py-2 rounded-md font-medium">
              Начать
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
    <div class="mobile-menu hidden md:hidden bg-white">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <a href="#features"
          class="text-gray-500 hover:text-gray-900 block px-3 py-2 text-base font-medium">Возможности</a>
        <a href="#locations" class="text-gray-500 hover:text-gray-900 block px-3 py-2 text-base font-medium">Локации</a>
        <a href="#pricing" class="text-gray-500 hover:text-gray-900 block px-3 py-2 text-base font-medium">Тарифы</a>
        <a href="#faq" class="text-gray-500 hover:text-gray-900 block px-3 py-2 text-base font-medium">FAQ</a>
      </div>
      <div class="pt-4 pb-3 border-t border-gray-200">
        <div class="px-5">
          <a href="#pricing"
            class="block w-full bg-gray-900 hover:bg-gray-700 text-white px-4 py-3 rounded-md text-center font-medium">
            Начать
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- главная секция -->
  <section id="hero" class="min-h-screen flex flex-col justify-center items-center pb-2 pt-28 relative z-10">
    <div class=" mx-auto px-4 text-center flex justify-center items-center flex-col">
      <img width="150" src="/public/assets/logo/logo.png" alt="CoraVPN">
      <h1 class="text-4xl md:text-6xl font-medium text-gray-900 mb-6">
        CoraVPN
      </h1>
      <h2 class="text-2xl md:text-4xl font-medium text-gray-700 mb-4">
        Полностью управляемый VPN сервис прямо в телеграм-приложении!
      </h2>
      <p class="text-gray-500 text-lg mb-8 mx-auto">
        <b>
          Все действия (подключение/установка, оплата, поддержка, просмотр, статистика)
          <span class="text-white bg-black p-1 rounded-lg">все в приложении!</span>
        </b>
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center mb-4">
        <a href="https://t.me/coravpn_bot/CoraVPN"
          class="bg-gray-900 hover:bg-gray-700 text-white px-8 py-4 rounded-lg font-medium flex items-center justify-center gap-2"
          target="_blank">
          <span>Попробовать беспалтно!</span>
          <i class="fa far fas fa-star"></i>
        </a>
        <a href="https://t.me/CoraVPNBot"
          class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-8 py-4 rounded-lg font-medium flex items-center justify-center gap-2"
          target="_blank">
          <span>Открыть Telegram-бота</span>
          <i class="fab fa-telegram-plane"></i>
        </a>
        <a href="#features"
          class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-8 py-4 rounded-lg font-medium flex items-center justify-center gap-2">
          <span>Узнать больше</span>
          <i class="fas fa-chevron-down w-5 h-5"></i>
        </a>
      </div>
      <!-- Статистика: wrap, последний пункт выделен шириной 100% -->
      <div class="flex flex-wrap justify-center gap-5 mt-8 mb-2 max-w-[520px] mx-auto">

        <div class="bg-gray-50 rounded-lg px-8 py-5 flex flex-col items-center">
          <h2 class="text-4xl font-medium mb-1 text-gray-900">15+</h2>
          <p class="text-lg text-gray-500 font-semibold tracking-wide uppercase">Стран</p>
        </div>

        <div class="bg-gray-50 rounded-lg px-8 py-5 flex flex-col items-center">
          <h2 class="text-4xl font-medium mb-1 text-gray-900">10 ГБит/с</h2>
          <p class="text-lg text-gray-500 font-semibold tracking-wide uppercase">Скорость</p>
        </div>

        <div class="bg-gray-50 rounded-lg px-8 py-5 flex flex-col items-center">
          <h2 class="text-4xl font-medium mb-1 text-gray-900">0</h2>
          <p class="text-lg text-gray-500 font-semibold tracking-wide uppercase">Логов</p>
        </div>

        <div class="bg-gray-50 rounded-lg px-8 py-5 flex flex-col items-center w-full">
          <h2 class="text-4xl font-medium mb-1 text-gray-900">24/7</h2>
          <p class="text-lg text-gray-500 font-semibold tracking-wide uppercase">Поддержка</p>
        </div>

      </div>
    </div>
  </section>

  <!-- Призыв к действию -->
  <section id="cta" class="py-12 bg-gray-900">
    <div class="max-w-6xl mx-auto px-4 text-center">
      <h2 class="text-3xl font-medium text-white mb-4">Присоединяйтесь к нам в Telegram</h2>
      <p class="text-gray-300 max-w-2xl mx-auto mb-8">
        Все функции и обслуживание — только через наше <a href="https://t.me/coravpn_bot/CoraVPN" class="underline"
          target="_blank">приложение</a> и <a href="https://t.me/CoraVPNBot" class="underline" target="_blank">бота</a>.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="https://t.me/coravpn_bot/CoraVPN"
          class="bg-white text-gray-900 hover:bg-gray-100 px-8 py-4 rounded-lg font-medium flex items-center justify-center gap-2"
          target="_blank">
          <i class="fab fa-telegram-plane"></i>
          <span>Открыть Telegram-приложение</span>
        </a>
        <a href="https://t.me/spcoravpn_bot"
          class="bg-white text-gray-900 hover:bg-gray-100 px-8 py-4 rounded-lg font-medium flex items-center justify-center gap-2"
          target="_blank">
          <i class="fab fa-telegram-plane"></i>
          <span>Открыть Telegram-поддержку AI</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Локации серверов c инфоблоком -->
  <!-- <section id="locations" class="py-14 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-5">
                <h2 class="text-3xl font-medium text-gray-900 mb-2">Наши серверные локации</h2>
                <p class="text-gray-500 max-w-2xl mx-auto mb-2">Высокоскоростные серверы по всему миру</p>
                <p class="text-gray-500 max-w-2xl mx-auto text-sm">
                    Это ознакомительный сайт для сервиса CoraVPN.<br>
                    <b>Все услуги и поддержка доступны исключительно через <a href="https://t.me/coravpn_bot/CoraVPN" class="underline text-blue-700" target="_blank">Telegram-приложение</a> и <a href="https://t.me/CoraVPNBot" class="underline text-blue-700" target="_blank">бота</a>.</b>
                </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white rounded-lg p-4 text-center border border-gray-200">
                    <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-map-marker-alt w-6 h-6 text-gray-700"></i>
                    </div>
                    <h3 class="font-medium">США</h3>
                    <p class="text-sm text-gray-500">Нью-Йорк, Лос-Анджелес</p>
                </div>
            </div>
            <div class="text-center mt-5">
                <p class="text-gray-400 text-xs italic">
                    Мы постоянно расширяем список серверов. Следите за новостями в <a href="https://t.me/coravpn_bot/CoraVPN" class="underline" target="_blank">Telegram-приложении</a>!
                </p>
            </div>
        </div>
    </section> -->

  <!-- Возможности -->
  <section id="features" class="py-12 bg-white animate-on-scroll">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-medium text-gray-900 mb-4">Почему выбирают CoraVPN</h2>
        <p class="text-gray-500 max-w-2xl mx-auto">Инновационные технологии для вашей цифровой свободы</p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">

          <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4 relative">
            <i class="fas fa-shield-alt text-gray-700"></i>
          </div>

          <h3 class="text-xl font-medium mb-3">Военная защита</h3>
          <p class="text-gray-500"><span class="text-white bg-black p-1 rounded-lg">AES-256</span> шифрование
            и протоколы
            WireGuard® обеспечивают максимальную
            безопасность ваших данных.</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
          <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
            <i class="fas fa-bolt text-gray-700"></i>
          </div>
          <h3 class="text-xl font-medium mb-3">Максимальная скорость</h3>
          <p class="text-gray-500">Оптимизированные серверы с SSD хранилищами и <span
              class="text-white bg-black p-1 rounded-lg">10 ГБит/с</span> каналами.</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
          <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
            <i class="fas fa-map-marker text-gray-700"></i>
          </div>
          <h3 class="text-xl font-medium mb-3">Надёжный сервер</h3>
          <p class="text-gray-500">
            <span class="text-white bg-black p-1 rounded-lg">Один выделенный сервер</span> на премиальной
            инфраструктуре, с быстрым
            подключением и стабильной
            работой.
          </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
          <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
            <i class="fas fa-eye-slash text-gray-700"></i>
          </div>
          <h3 class="text-xl font-medium mb-3">Полная анонимность</h3>
          <p class="text-gray-500">Строгая политика <span class="text-white bg-black p-1 rounded-lg">0
              логирования.</span> Ваша
            активность остается только
            вашей.</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
          <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
            <i class="fas fa-unlock-alt text-gray-700"></i>
          </div>
          <h3 class="text-xl font-medium mb-3">Обход блокировок</h3>
          <p class="text-gray-500"><span class="text-white bg-black p-1 rounded-lg">Быстрый</span> доступ к
            заблокированным сайтам, мессенджерам и стриминговым
            сервисам.</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
          <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
            <i class="fas fa-infinity text-gray-700"></i>
          </div>
          <h3 class="text-xl font-medium mb-3">Безлимитный трафик</h3>
          <p class="text-gray-500"><span class="text-white bg-black p-1 rounded-lg">Никаких ограничений</span>
            по объему
            данных — смотрите, скачивайте, работайте без
            лимитов.</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
          <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
            <i class="fas fa-headset text-gray-700"></i>
          </div>
          <h3 class="text-xl font-medium mb-3">Живая поддержка</h3>
          <p class="text-gray-500">Поддержка пользователей <span class="text-white bg-black p-1 rounded-lg">24/7</span>
            и
            быстрые ответы на любые вопросы через
            <a href="https://t.me/CoraVPNBot" class="underline" target="_blank">Telegram-бота</a>.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Тарифы (ознакомительно) -->
  <section id="pricing" class="py-12 bg-gray-50 animate-on-scroll">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-medium text-gray-900 mb-4">Тарифные планы</h2>
        <p class="text-gray-500 max-w-2xl mx-auto">
          Оформление и оплата доступны только в
          <a href="https://t.me/coravpn_bot/CoraVPN" class="underline" target="_blank">Telegram-приложении</a>
          или через
          <a href="https://t.me/CoraVPNBot" class="underline" target="_blank">бота</a>
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- item 1 -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 animate-on-scroll delay-100">
          <div class="mb-6">
            <h3 class="text-xl font-medium mb-2">Базовый</h3>
            <p class="text-gray-500">Для повседневного использования</p>
          </div>
          <div class="mb-6">
            <span class="text-4xl font-medium"><?= $price['basic']; ?></span>
            <span class="text-gray-500">/мес</span>
          </div>
          <ul class="space-y-3 mb-6">
            <!-- <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                                class="text-gray-600">3 устройства</span></li> -->
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">1 страна (Pro)</span></li>
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">Безлимитный трафик</span></li>
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">Статический IPv4</span></li>
            <li class="flex items-center text-gray-400"><i
                class="fas fa-times w-4 h-4 text-gray-400 mr-2"></i><span>Приоритетная поддержка</span>
            </li>
            <li class="flex items-center text-gray-400"><i
                class="fas fa-times w-4 h-4 text-gray-400 mr-2"></i><span>Доступ к бонусным дням</span>
            </li>
          </ul>
          <a href="https://t.me/coravpn_bot/CoraVPN"
            class="block w-full bg-gray-900 hover:bg-gray-700 text-white text-center py-3 rounded-md font-medium"
            target="_blank">
            Получить через приложение
          </a>
        </div>

        <!-- item 2 -->
        <div class="bg-white rounded-lg p-6 border-2 border-gray-900 animate-on-scroll delay-200">
          <div class="mb-6">
            <h3 class="text-xl font-medium mb-2">Профессиональный</h3>
            <p class="text-gray-500">Для максимальной производительности</p>
          </div>
          <div class="mb-6">
            <span class="text-4xl font-medium"><?= $price['plus']; ?></span>
            <span class="text-gray-500">/мес</span>
          </div>
          <ul class="space-y-3 mb-6">
            <!-- <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                                class="text-gray-600">10 устройств</span></li> -->
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">1 страна (Pro)</span></li>
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">Безлимитный трафик</span></li>
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">Приоритетная поддержка</span></li>
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">Доступ
                к бонусным дням</span></li>
          </ul>
          <a href="https://t.me/coravpn_bot/CoraVPN"
            class="block w-full bg-gray-900 hover:bg-gray-700 text-white text-center py-3 rounded-md font-medium"
            target="_blank">
            Получить через приложение
          </a>
        </div>

        <!-- item 3 -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 animate-on-scroll delay-300">
          <div class="mb-6">
            <h3 class="text-xl font-medium mb-2">Бизнес</h3>
            <p class="text-gray-500">Для компаний и организаций</p>
          </div>
          <div class="mb-6">
            <span class="text-4xl font-medium"><?= $price['pro']; ?></span>
            <span class="text-gray-500">/мес</span>
          </div>
          <ul class="space-y-3 mb-6">
            <!-- <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                                class="text-gray-600">Неограниченные устройства</span></li> -->
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">1 страна (Pro)</span></li>
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">Безлимитный трафик</span></li>
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">24/7 поддержка (Pro)</span></li>
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">Увиличение срока на 3 дня</span></li>
            <li class="flex items-center"><i class="fas fa-check w-4 h-4 text-gray-700 mr-2"></i><span
                class="text-gray-600">Доступ к бонусным дням</span></li>
          </ul>
          <a href="https://t.me/coravpn_bot/CoraVPN"
            class="block w-full bg-gray-900 hover:bg-gray-700 text-white text-center py-3 rounded-md font-medium"
            target="_blank">
            Получить через приложение
          </a>
        </div>

      </div>
      <div class="text-center mt-8">
        <span class="inline-flex items-center bg-blue-100 text-blue-700 px-4 py-2 rounded-full text-sm">
          <i class="fas fa-gift mr-2"></i>
          Первый месяц — скидка 15% новым пользователям на любой тариф и на любой срок!
        </span>
      </div>
    </div>
  </section>

  <!-- Отзывы (пример) -->
  <section id="testimonials" class="py-12 bg-white animate-on-scroll">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-medium text-gray-900 mb-4">Что говорят наши пользователи</h2>
        <p class="text-gray-500 max-w-2xl mx-auto">
          Отзывы довольных пользователей по всему миру. Попробуйте <a href="https://t.me/coravpn_bot/CoraVPN"
            class="underline text-blue-700" target="_blank">CoraVPN приложение</a> и <a href="https://t.me/CoraVPNBot"
            class="underline text-blue-700" target="_blank">бота</a> сами!
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 animate-on-scroll delay-100">
          <div class="flex items-center mb-4">
            <img src="https://api.dicebear.com/6.x/thumbs/svg?seed=A&backgroundColor=b6e3f4" alt="User"
              class="w-10 h-10 rounded-full mr-4">
            <div>
              <h4 class="font-medium">Александр К.</h4>
              <div class="flex text-yellow-400">
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star-half-alt w-4 h-4"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-600">"Скорость отличная, подключаюсь к <a href="https://t.me/CoraVPNBot"
              class="underline text-blue-700" target="_blank">Telegram-боту</a> за секунду и уже могу
            пользоваться VPN."</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 animate-on-scroll delay-200">
          <div class="flex items-center mb-4">
            <img src="https://api.dicebear.com/6.x/thumbs/svg?seed=B&backgroundColor=b6e3f4" alt="User"
              class="w-10 h-10 rounded-full mr-4">
            <div>
              <h4 class="font-medium">Екатерина М.</h4>
              <div class="flex text-yellow-400">
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-600">"Все прозрачно — оплату и управление делаю только через <a
              href="https://t.me/CoraVPNBot" class="underline text-blue-700" target="_blank">Telegram-бота</a>, в
            интернете через сайт узнала про него и подарок дали)."
          </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 animate-on-scroll delay-300">
          <div class="flex items-center mb-4">
            <img src="https://api.dicebear.com/6.x/thumbs/svg?seed=C&backgroundColor=b6e3f4" alt="User"
              class="w-10 h-10 rounded-full mr-4">
            <div>
              <h4 class="font-medium">Дмитрий С.</h4>
              <div class="flex text-yellow-400">
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-600">"Telegram-бот — прикольно, да и нет реклам! Никаких аккаунтов и личных
            кабинетов на сайтах — тупо нажал в <a href="https://t.me/coravpn_bot/CoraVPN"
              class="underline text-blue-700" target="_blank">приложение</a> и делай что хочешь."</p>
        </div>
        <!-- Новый отзыв -->
        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 animate-on-scroll delay-400 md:col-span-1">
          <div class="flex items-center mb-4">
            <img src="https://api.dicebear.com/6.x/thumbs/svg?seed=D&backgroundColor=b6e3f4" alt="User"
              class="w-10 h-10 rounded-full mr-4">
            <div>
              <h4 class="font-medium">Ирина Л.</h4>
              <div class="flex text-yellow-400">
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-600">"Поддержка всегда на связи! Решили мой вопрос за 5 минут, не совсем
            пониманю как эти VPN использовать, а тут инструкцию личную кинули — вроде все поняла. Очень
            приятно."</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 animate-on-scroll delay-400 md:col-span-1">
          <div class="flex items-center mb-4">
            <img src="https://api.dicebear.com/6.x/thumbs/svg?seed=D&backgroundColor=b6e3f4" alt="User"
              class="w-10 h-10 rounded-full mr-4">
            <div>
              <h4 class="font-medium">Артем Е.</h4>
              <div class="flex text-yellow-400">
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
                <i class="fas fa-star w-4 h-4"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-600">"Самое прикольное то, что ничего самому делать не нужно! Реально 3 кнопки —
            скачать, подключить — и все, я даже ничего не делал, а VPN уже стоит!"</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="py-12 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-medium text-gray-900 mb-4">Вопросы и ответы</h2>
        <p class="text-gray-500 max-w-2xl mx-auto">Ознакомьтесь с часто задаваемыми вопросами о CoraVPN</p>
      </div>
      <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden">
          <details open>
            <summary class="flex items-center justify-between p-6 cursor-pointer">
              <h3 class="font-medium text-gray-900">Как пользоваться CoraVPN?</h3>
              <i class="fas fa-chevron-down w-5 h-5 text-gray-500 transition-transform duration-200"></i>
            </summary>
            <div class="px-6 pb-6 pt-0 text-gray-600">
              Управление, подключение и оплата — только через <a href="https://t.me/coravpn_bot/CoraVPN"
                class="underline" target="_blank">Telegram-приложение</a> или <a href="https://t.me/CoraVPNBot"
                class="underline" target="_blank">@coravpn_bot</a>.
              Сайт только для справки.
            </div>
          </details>
        </div>
        <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden">
          <details>
            <summary class="flex items-center justify-between p-6 cursor-pointer">
              <h3 class="font-medium text-gray-900">Можно ли подключиться без Telegram?</h3>
              <i class="fas fa-chevron-down w-5 h-5 text-gray-500 transition-transform duration-200"></i>
            </summary>
            <div class="px-6 pb-6 pt-0 text-gray-600">
              Нет, сервис доступен только через <a href="https://t.me/coravpn_bot/CoraVPN" class="underline"
                target="_blank">Telegram-приложение</a> и <a href="https://t.me/CoraVPNBot" class="underline"
                target="_blank">@coravpn_bot</a>. Сайт
              — для ознакомления.
            </div>
          </details>
        </div>
        <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden">
          <details>
            <summary class="flex items-center justify-between p-6 cursor-pointer">
              <h3 class="font-medium text-gray-900">Как оплатить подписку?</h3>
              <i class="fas fa-chevron-down w-5 h-5 text-gray-500 transition-transform duration-200"></i>
            </summary>
            <div class="px-6 pb-6 pt-0 text-gray-600">
              Только в <a href="https://t.me/CoraVPNBot" class="underline" target="_blank">@coravpn_bot</a> —
              принимаются карты, криптовалюта и другие способы
              оплаты.
            </div>
          </details>
        </div>
        <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden">
          <details>
            <summary class="flex items-center justify-between p-6 cursor-pointer">
              <h3 class="font-medium text-gray-900">Где получить поддержку?</h3>
              <i class="fas fa-chevron-down w-5 h-5 text-gray-500 transition-transform duration-200"></i>
            </summary>
            <div class="px-6 pb-6 pt-0 text-gray-600">
              Поддержка работает прямо в <a href="https://t.me/CoraVPNBot" class="underline"
                target="_blank">@coravpn_bot</a> или в приложении <a href="https://t.me/coravpn_bot/CoraVPN"
                class="underline" target="_blank">t.me/coravpn_bot/CoraVPN</a>.
            </div>
          </details>
        </div>
        <!-- Новый вопрос -->
        <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden">
          <details>
            <summary class="flex items-center justify-between p-6 cursor-pointer">
              <h3 class="font-medium text-gray-900">Можно ли тестировать VPN бесплатно?</h3>
              <i class="fas fa-chevron-down w-5 h-5 text-gray-500 transition-transform duration-200"></i>
            </summary>
            <div class="px-6 pb-6 pt-0 text-gray-600">
              Для новых пользователей доступен пробный период — подробности узнавайте в <a
                href="https://t.me/coravpn_bot/CoraVPN" class="underline" target="_blank">нашем
                приложении</a> или у <a href="https://t.me/CoraVPNBot" class="underline"
                target="_blank">@coravpn_bot</a>!
            </div>
          </details>
        </div>
      </div>
    </div>
  </section>

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
          // Основные настройки частиц для particles.js
          "particles": {
            // Количество частиц
            "number": {
              // Сколько частиц одновременно на экране
              "value": 70,
              // Включить плотность (автоматическая подгонка количества под размер canvas)
              "density": {
                "enable": true,
                // Площадь, по которой рассчитывается плотность (чем больше, тем реже частицы)
                "value_area": 900
              }
            },
            // Цвет частиц
            "color": {
              "value": "#2225BB" // Можно установить любой CSS-цвет: "#fff", "red", "rgba(0,0,0,0.5)" и т.д.
            },
            // Форма частиц
            "shape": {
              // Тип формы. Возможные значения:
              // "circle", "edge", "triangle", "polygon", "star", "image"
              // Пример: "type": "star"
              "type": "edge",
              // Параметры обводки (border) для формы
              "stroke": {
                "width": 0,     // Толщина обводки (0 = нет обводки)
                "color": "#000" // Цвет обводки
              },
              // Пример настройки кастомной картинки:
              // "image": { "src": "path/to/image.png", "width": 100, "height": 100 }
            },
            // Прозрачность частиц
            "opacity": {
              "value": 0.7, // Значение прозрачности (0 - полностью прозрачно, 1 - полностью видно)
              "random": true, // Если true — для каждой частицы случайное значение от value до 1
            },
            // Размер
            "size": {
              "value": 6, // Базовый размер (px)
              "random": true // Если true — размер случайный от 1 до value
            },
            // Связывание линиями между частицами
            "line_linked": {
              "enable": true, // Включить линии между частицами?
              "distance": 170, // Максимальное расстояние между частицами для связи линией (px)
              "color": "#6778E9", // Цвет линии
              "opacity": 0.2, // Прозрачность линии
              "width": 2 // Толщина линии (px)
            },
            // Движение частиц
            "move": {
              "enable": true, // Включить анимацию движения?
              "speed": 1.6, // Скорость движения (рекомендация: от 0.1 до 6)
              // Возможные значения direction:
              // "none" (случайно), "top", "top-right", "right", "bottom-right", "bottom", "bottom-left", "left", "top-left"
              "direction": "none",
              "random": true, // Движение каждой частицы своё?
              "straight": false, // Если true — частицы двигаются строго по направлению direction
              // Возможные значения out_mode:
              // "out" (вылетает за пределы, появляется с другой стороны),
              // "bounce" (отскакивает от границы)
              "out_mode": "out",
              "bounce": true // Если true — при столкновении с границей отскакивает
            }
          },
          // Если экран retina — отрисовка для высокой плотности пикселей
          "retina_detect": true
        });
      }
    });
  </script>
</body>

</html>
