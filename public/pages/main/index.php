<?php
use Setting\Route\Function\Controllers\Auth\Auth;
Auth::auth();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Профиль</title>
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
  <div class="min-h-screen flex flex-col w-full">

    <!-- navbar top -->
    <header class="absolute z-50 left-0 top-2 right-0 h-16 px-6 sm:hidden flex items-center justify-between">
      <!-- refresh -->
      <i class="fa fa-refresh text-white"></i>
      <!-- logo -->
      <div class="flex items-center gap-2">
        <img data-theme-invert class=" w-auto h-7 object-contain" src="/public/assets/images/icons/logo/qweesvpn.svg"
          alt="qweesvpn">
        <h2 class="text-white text-xl font-[qwees-poppins-medium] tracking-wider">QWEES <span
            class="text-green-400">VPN</span></h2>
      </div>
      <!-- version -->
      <span class="text-white text-sm">v1.0.0</span>
    </header>

    <main class="flex sm:my-2 w-full">

      <!-- ################# MENU DESCKTOP ####################-->
      <aside
        class="hidden sm:block bg-gradient-to-b from-green-950/50 via-black to-green-950/50 min-w-[300px] z-20 border-r border-solid h-screen border-white/20">
        <div class="relative sm:text-sm sm:leading-6 my-8">
          <ul class="flex flex-col gap-6">

            <li class="flex h-16 gap-4 items-center justify-center">
              <img data-theme-invert class="w-auto h-12 object-contain"
                src="public/assets/images/icons/logo/qweesvpn.svg" alt="qweesvpn">
              <h2 class="text-white text-3xl font-[qwees-urbanist-medium] tracking-wider">QWEES <span
                  class="text-green-400">VPN</span></h2>
            </li>

            <!-- Основные ссылки -->
            <ul class="desktop list-none fle fle-col mr-4">
              <!-- home -->
              <li
                class="bg_active relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                data-toggle-section="main">
                <span></span>
                <span class="pl-10 text-xl text-white flex items-center gap-4">
                  <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/home.svg"
                    alt="home" decoding="async">
                  Главная
                </span>
              </li>
              <!-- profile -->
              <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                data-toggle-section="main">
                <span></span>
                <span class="pl-10 text-xl text-white flex items-center gap-4">
                  <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/profile.svg"
                    alt="home" decoding="async">
                  Профиль
                </span>
              </li>
              <!-- setting -->
              <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                data-toggle-section="main">
                <span></span>
                <span class="pl-10 text-xl text-white flex items-center gap-4">
                  <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/setting.svg"
                    alt="home" decoding="async">
                  Настройки
                </span>
              </li>
              <!-- referal -->
              <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                data-toggle-section="main">
                <span></span>
                <span class="pl-10 text-xl text-white flex items-center gap-4">
                  <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/refer.svg"
                    alt="home" decoding="async">
                  Дополнительное
                </span>
              </li>
            </ul>
          </ul>
        </div>
      </aside>
      <!-- ################# CONTENT DESCKTOP ####################-->
      <div class="hidden sm:block w-full text-white">
        <section class="flex flex-col gap-4 box-border w-full p-10 ml-2" data-section="main">
          <!-- оглавление DESCKTOP -->
          <h1 class="text-3xl">
            Главная
          </h1>

          <!-- контент -->
          <div class="flex items-start justify-center gap-4 w-full">
            <!-- BLOCK-1 => DISPLAY STATUS -->
            <div
              class="relative min-h-[600px] flex flex-1 flex-col items-center justify-center rounded-xl border border-white/10 overflow-hidden bg-gradient-to-b from-black via-green-950/50 to-black">
              <!-- backgound -->
              <img src="/public/assets/images/background/world.svg" alt="background"
                class="absolute w-full h-full opacity-20" loading="lazy">

              <!-- Monoblock decorative elements -->
              <div class="flex justify-center items-center flex-col w-1/3">
                <img src="/public/assets/images/icons/services/monoblock/off_top2.svg" alt="monoblock_part1"
                  loading="lazy" class="z-20 w-full animation_monoblock_off">
                <img src="/public/assets/images/icons/services/monoblock/off_down.svg" alt="monoblock_part2"
                  loading="lazy" class="-translate-y-[30%] z-10 w-full">
              </div>

              <p class="absolute text-2xl font-bold bottom-10">Статус: Неактивен</p>
            </div>

            <!-- BLOCK-2 => INFORMATION PANELS -->
            <div
              class="flex-1 h-full max-w-[350px] border border-white/20 p-6 rounded-xl bg-gradient-to-r from-green-950/50 via-black to-green-950/40">
              <ul class="flex flex-col gap-4 w-full text-xl">
                <!-- content 1 -->
                <li class="flex border-solid border-white/10 border-b p-2 pb-4 justify-between items-center w-full">
                  <span class="text-gray-400">Пинг:</span>
                  <div class="flex items-center gap-2">
                    <i class="fas fa-arrow-up text-green-400 text-sm"></i>
                    <span class="text-green-400">0 ms</span>
                  </div>
                </li>
                <!-- content 2 -->
                <li class="flex border-solid border-white/10 border-b p-2 pb-4 justify-between items-center w-full">
                  <span class="text-gray-400">Протокол:</span>
                  <span class="text-white text-lg font-light">gRPC</span>
                </li>
                <!-- content 3 -->
                <li class="flex border-solid border-white/10 border-b p-2 pb-4 justify-between items-center w-full">
                  <span class="text-gray-400">IP-адрес:</span>
                  <span class="text-white text-lg font-light">0.0.0.0</span>
                </li>
                <!-- content 4 -->
                <li class="flex p-2 justify-between items-center w-full">
                  <span class="text-gray-400">Сервер:</span>
                  <span class="text-yellow-400 text-sm font-light">Ожидание подключения...</span>
                </li>
              </ul>
            </div>
          </div>

        </section>
      </div>

      <!-- ################# MENU MOBILE ####################-->
      <aside data-theme-invert
        class="sm:hidden z-50 fixed bottom-4 bg-[rgb(78,78,78,0.38)] left-4 right-4 mx-auto rounded-full px-6 py-2">
        <ul class="mobile flex justify-between items-center gap-4">
          <li
            class="bg_active relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
            data-toggle-section="main">
            <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/home.svg" alt="Домой"
              decoding="async">
          </li>
          <li
            class="relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
            data-toggle-section="profile">
            <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/profile.svg"
              alt="Профиль" decoding="async">
          </li>
          <li
            class="relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
            data-toggle-section="setting">
            <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/setting.svg"
              alt="Настройки" decoding="async">
          </li>
          <li
            class="relative flex items-center justify-center p-3 aspect-square transition-all duration-500 cursor-pointer"
            data-toggle-section="referal">
            <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/refer.svg"
              alt="Дополнительное" decoding="async">
          </li>
        </ul>
      </aside>
      <!-- ################# CONTENT MOBILE ####################-->
      <div class="sm:hidden w-full text-white">
        <!-- SECTION = MAIN -->
        <section
          class="overflow-hidden relative flex flex-col justify-between py-[95px] box-border w-full min-h-[100dvh] p-10 bg-gradient-to-t from-black via-green-950/50 to-black"
          data-section="main">

          <!-- backgound -->
          <img src="/public/assets/images/background/world.svg" alt="background"
            class="absolute h-full opacity-20 -left-[3rem] right-0 top-0 bottom-0 mx-auto scale-[2.5] z-0"
            loading="lazy">

          <!-- Monoblock decorative elements -->
          <div class="flex justify-center items-center flex-col max-h-[300px] max-w-[200px] m-auto">
            <img src="/public/assets/images/icons/services/monoblock/off_top2.svg" alt="monoblock_part1" loading="lazy"
              class="z-20 w-full animation_monoblock_off">
            <img src="/public/assets/images/icons/services/monoblock/off_down.svg" alt="monoblock_part2" loading="lazy"
              class="-translate-y-[30%] z-10 w-full">
          </div>

          <!-- information -->
          <div class="z-10 w-full h-full">
            <ul class="flex flex-col justify-between items-center gap-4 h-full">
              <!-- block 1 -->
              <li
                class="relative w-full flex justify-between items-center p-[15px] bg-[rgb(255,255,255,0.1)] rounded-xl">
                <img src="/public/assets/images/icons/services/default/netherlands.svg" alt="" loading="lozy"
                  decoding="async">
                <div class="flex flex-col items-center justify-start text-lg text-white">
                  <!-- no -->
                  <p class="uppercase">vpn <span class="text-[#FF6378]">неактивен</span></p>
                  <!-- yes -->
                </div>
                <img src="/public/assets/images/icons/services/default/notnetwork.svg" alt="" loading="lozy"
                  decoding="async">
              </li>
              <!-- block 2 -->
              <li
                class="relative w-full flex justify-between items-center p-[15px] bg-[rgb(255,255,255,0.1)] rounded-xl">
                <img src="/public/assets/images/icons/services/default/buy.svg" alt="" loading="lozy" decoding="async"
                  class="invert">
                <div class="flex flex-col items-center justify-start text-lg text-white">
                  <!-- no -->
                  <a href="/pay" class="uppercase text-center flex gap-2">купить <span
                      class="word_hidden">подписку</span>
                  </a>
                  <!-- yes -->
                </div>
                <img src="/public/assets/images/icons/services/default/arrow.svg" alt="" loading="lozy" decoding="async"
                  class="invert">
              </li>
              <!-- block 3 -->
              <li class="relative w-full flex justify-between px-4 rounded-lg text-sm">
                <!-- 1 -->
                <div class="flex flex-col items-center justify-between gap-2">
                  <img src="/public/assets/images/icons/services/default/protocol.svg" alt="protocol" loading="lazy">
                  <p class="text-[#93A7C8] font-bold">gRPC</p>
                </div>
                <!-- 2 -->
                <div class="flex flex-col items-center justify-between gap-2">
                  <p class="text-lg">Ожидание...</p>
                  <p class="text-[#93A7C8]">0.0.0.0</p>
                </div>
                <!-- 3 -->
                <div class="flex flex-col items-center justify-between gap-2">
                  <div class="flex gap-2 items-center justify-center h-8">
                    <span class="bg-red-500 h-2 w-2 rounded-full aspect-square"></span>
                    <span class="bg-red-500 h-2 w-2 rounded-full aspect-square"></span>
                    <span class="bg-red-500 h-2 w-2 rounded-full aspect-square"></span>
                  </div>
                  <p class="text-[#93A7C8] font-bold">0ms</p>
                </div>
              </li>
            </ul>
          </div>

        </section>
        <!-- SECTION = PROFILE -->
        <section
          class="hidden overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black"
          data-section="profile">
          <!-- header logo -->
          <div class="w-full h-[300px]">
            <div
              class="absolute flex flex-col gap-4 justify-center items-center w-full bg-[#0B0C1A] top-0 h-[300px] rounded-b-xl">
              <!-- backgound -->
              <img data-theme-invert src=" /public/assets/images/background/stars.svg" alt="background"
                class="absolute h-full right-0 top-0 bottom-0 mx-auto" loading="lazy">

              <img src="/public/assets/images/icons/services/avatar/1.png" alt="avatar" class="rounded-xl w-18 h-18">
              <h3 class="text-2xl font-bold">Anna Howard</h3>

              <!-- information block -->
              <div class="absolute -bottom-[6.5rem] left-4 right-4 mx-auto bg-white rounded-2xl p-4">
                <h3 class="text-lg font-semibold text-black">Статистика профиля</h3>
                <ul class="grid grid-cols-2 grid-rows-2 gap-1 mt-4 justify-between">
                  <!-- block 1 -->
                  <li class="flex gap-2 items-center">
                    <img class="w-8" src=" /public/assets/images/icons/services/profile/wifi.svg" alt="icon_wifi"
                      loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">VPN</h4>
                      <div class="text-[12px] text-gray-400">Активен</div>
                    </div>
                  </li>
                  <!-- block 2 -->
                  <li class="flex gap-2 items-center justify-center">
                    <img class=" w-7" src=" /public/assets/images/icons/services/profile/fa_language.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Язык</h4>
                      <div class="text-[12px] text-gray-400">Русский</div>
                    </div>
                  </li>
                  <!-- block 3 -->
                  <li class="flex gap-2 items-center">
                    <img class="w-7" src=" /public/assets/images/icons/services/profile/server.svg" alt="icon_wifi"
                      loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Пробный</h4>
                      <div class="text-[12px] text-gray-400">Не подключен</div>
                    </div>
                  </li>
                  <!-- block 4 -->
                  <li class="flex gap-2 items-center justify-center">
                    <img class="w-7" src=" /public/assets/images/icons/services/profile/theme.svg" alt="icon_wifi"
                      loading="lazy">
                    <div class="flex flex-col justify-center ">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Тема</h4>
                      <div class="text-[12px] text-gray-400">Темная</div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div class="px-6 pt-[7.5rem]">
            <!-- data -->
            <div class="flex flex-col gap-4 mb-4">
              <h4 class="text-xl font-semibold">Данные</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                  <!-- info -->
                  <div class="flex flex-col justify-center w-[150px] gap-1">
                    <h4 class="text-sm font-semibold">VPN ключ</h4>
                    <p class="overflow-hidden h-8  break-all text-[12px] text-white/50 w-[150px]">
                      vless://dd046f11-ca13-4d75-aae7-c429903d91e9@nl.coravpn.online:443?type=grpc&mode=gun&serviceName=&security=reality&fp=firefox&sni=google.com&pbk=MPAVcf5ZSdhln_H1BxjluPind3sr0TWy_c6EoSfY7BE&sid=63d5&spx=/M0mUAooHJRuKiRg#%F0%9F%87%B3%F0%9F%87%B1TQ-timqwees
                    </p>
                  </div>
                  <!-- button -->
                  <div class="flex gap-2 justify-end items-center">
                    <i class="fa fa-copy text-lg text-gray-400"></i>
                    <i class="fa fa-trash text-lg text-red-400"></i>
                  </div>
                </li>
                <li class="flex bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                  <!-- info -->
                  <div class="flex flex-col justify-center w-[150px] gap-1">
                    <h4 class="text-sm font-semibold">VPN ключ</h4>
                    <p class="overflow-hidden h-8  break-all text-[12px] text-white/50 w-[150px]">
                      vless://dd046f11-ca13-4d75-aae7-c429903d91e9@nl.coravpn.online:443?type=grpc&mode=gun&serviceName=&security=reality&fp=firefox&sni=google.com&pbk=MPAVcf5ZSdhln_H1BxjluPind3sr0TWy_c6EoSfY7BE&sid=63d5&spx=/M0mUAooHJRuKiRg#%F0%9F%87%B3%F0%9F%87%B1TQ-timqwees
                    </p>
                  </div>
                  <!-- button -->
                  <div class="flex gap-2 justify-end items-center">
                    <i class="fa fa-copy text-lg text-gray-400"></i>
                    <i class="fa fa-trash text-lg text-red-400"></i>
                  </div>
                </li>
              </ul>
            </div>
            <!-- setting -->
            <div class="flex flex-col gap-4 mb-4">
              <h4 class="text-xl font-semibold">Настройки приложения</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex bg-[#2C2A2A] p-2 px-2.5 justify-between items-center rounded-2xl">
                  <!-- info -->
                  <div class="flex justify-center items-center gap-2">
                    <!-- icon -->
                    <div class="flex justify-center items-center">
                      <i class="fa fa-sun text-green-400 text-2xl -rotate-[15deg]"></i>
                    </div>
                    <div class="flex flex-col justify-center translate-y-1.5">
                      <h4 class="text-sm">Светлая тема</h4>
                      <p class="overflow-hidden h-8 break-all text-[12px] text-white/50 w-[150px]">
                        Всегда будет включена
                      </p>
                    </div>
                  </div>
                  <!-- button -->
                  <div class="flex gap-2 justify-end items-center">
                    <label class="inline-flex items-center me-5 cursor-pointer">
                      <input type="checkbox" value="" class="sr-only peer" id="darkModeToggle">
                      <div
                        class="relative w-9 h-5 bg-white/20 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-400 dark:peer-checked:bg-green-400">
                      </div>
                    </label>
                  </div>
                </li>
              </ul>
            </div>
          </div>

        </section>
      </div>
    </main>

    <script src="/public/assets/scripts/main/main.js"></script>
    <script src="/public/assets/scripts/theme/main.js"></script>
  </div>
</body>

</html>
