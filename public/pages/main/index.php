<?php
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\Client\Client;
use App\Config\Session;
use Setting\Route\Function\Controllers\language\Language;
use Setting\Route\Function\Controllers\vpn\VpnStatus;
use Setting\Route\Function\Controllers\profile\Profile;
use Setting\Route\Function\Controllers\system\SystemInfo;

Auth::auth();
$user = Client::get(Session::init('user')['uniID']);

// язык
$currentLanguage = Language::getCurrent();
$translations = Language::getTranslations($currentLanguage);

// Получаем реальные данные через новые классы
$vpnStatus = VpnStatus::getVpnStatus($user['uniID'] ?? '');
$userProfile = Profile::getUserProfile($user['uniID'] ?? '');
$systemInfo = SystemInfo::getSystemInfo();
$usageStats = VpnStatus::getUsageStats($user['uniID'] ?? '');

// Форматируем данные для вывода (используем Client класс как helper)
$formattedVpnStatus = [
  'status_text' => $translations[$vpnStatus['status'] === 'active' ? 'active' : 'inactive'],
  'status_class' => $vpnStatus['status'] === 'active' ? 'text-green-400' : 'text-red-400',
  'ping_ms' => $vpnStatus['ping']['ms'],
  'ping_status' => $vpnStatus['ping']['status'],
  'ping_class' => $vpnStatus['ping']['status'] === 'good' ? 'text-green-400' : 'text-red-400',
  'ping_icon' => $vpnStatus['ping']['status'] === 'good' ? 'fa-arrow-up' : 'fa-arrow-down',
  'protocol' => $vpnStatus['protocol'],
  'ip_address' => $vpnStatus['ip_address'],
  'location' => $vpnStatus['location'],
  'monoblock_image' => $vpnStatus['status'] === 'active' ? 'on_top2.svg' : 'off_top2.svg',
  'monoblock_class' => $vpnStatus['status'] === 'active' ? 'animation_monoblock_on' : 'animation_monoblock_off'
];

$formattedUserProfile = [
  'full_name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Пользователь',
  'status_text' => $translations[$user['status'] === 'active' ? 'active' : 'inactive'],
  'status_class' => $user['status'] === 'active' ? 'text-green-400' : 'text-red-400',
  'days_left' => $user['count_days'] ?? 0,
  'refer_count' => $userProfile['referal_info']['refer_count'],
  'has_discount' => $userProfile['referal_info']['has_discount'] ? $translations['yes'] : $translations['no'],
  'subscription_status' => $translations[$userProfile['subscription_info']['status'] === 'active' ? 'active' : 'inactive']
];

$formattedSystemInfo = [
  'version' => $systemInfo['app']['version'],
  'db_status' => $systemInfo['database']['status'],
  'db_status_text' => $translations[$systemInfo['database']['status'] === 'connected' ? 'yes' : 'no'],
  'db_status_class' => $systemInfo['database']['status'] === 'connected' ? 'text-green-400' : 'text-red-400'
];

$formattedPricing = $userProfile['pricing_info'] ?? [];
$activeSection = $_GET['section'] ?? 'main';
?>
<!DOCTYPE html>
<html lang="<?= $currentLanguage ?>">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $translations['profile'] ?></title>
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
    <header class="fixed z-50 left-0 top-2 right-0 h-16 px-6 sm:hidden flex items-center justify-between">
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
      <span class="text-white text-sm" data-version><?= $formattedSystemInfo['version'] ?></span>
    </header>

    <main class="flex sm:my-2 w-full h-full">

      <!-- ################# MENU DESCKTOP ####################-->
      <aside
        class="hidden h-full sm:block bg-gradient-to-b from-green-950/50 via-black to-green-950/50 min-w-[300px] z-20 border-r border-solid border-white/20">
        <div class="relative sm:text-sm sm:leading-6 my-8">
          <ul class="fixed flex flex-col gap-6">

            <li class="flex h-16 gap-4 items-center justify-center">
              <img data-theme-invert class="w-auto h-12 object-contain"
                src="public/assets/images/icons/logo/qweesvpn.svg" alt="qweesvpn">
              <h2 class="text-white text-3xl font-[qwees-urbanist-medium] tracking-wider">QWEES <span
                  class="text-green-400">VPN</span></h2>
            </li>

            <!-- Основные ссылки -->
            <ul class="desktop list-none fle fle-col mr-4 w-full">
              <!-- home -->
              <li
                class="bg_active relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                data-toggle-section="main">
                <span></span>
                <span class="pl-10 text-xl text-white flex items-center gap-4">
                  <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/home.svg"
                    alt="home" decoding="async">
                  <?= $translations['main'] ?>
                </span>
              </li>
              <!-- profile -->
              <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                data-toggle-section="profile">
                <span></span>
                <span class="pl-10 text-xl text-white flex items-center gap-4">
                  <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/profile.svg"
                    alt="home" decoding="async">
                  <?= $translations['profile'] ?>
                </span>
              </li>
              <!-- setting -->
              <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                data-toggle-section="setting">
                <span></span>
                <span class="pl-10 text-xl text-white flex items-center gap-4">
                  <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/setting.svg"
                    alt="home" decoding="async">
                  <?= $translations['settings'] ?>
                </span>
              </li>
              <!-- referal -->
              <li class="relative flex items-center py-3 ml-4 rounded-xl transition-all duration-500 cursor-pointer"
                data-toggle-section="referal">
                <span></span>
                <span class="pl-10 text-xl text-white flex items-center gap-4">
                  <img data-theme-invert loading="lazy" src="/public/assets/images/icons/services/menu/refer.svg"
                    alt="home" decoding="async">
                  <?= $translations['additional'] ?>
                </span>
              </li>
            </ul>
          </ul>
        </div>
      </aside>
      <!-- ################# CONTENT DESCKTOP ####################-->
      <div class="hidden sm:block w-full h-full text-white">

        <!-- SECTION = MAIN -->
        <section class="flex flex-col gap-4 box-border w-full p-10 ml-2" data-section="main">
          <!-- оглавление DESCKTOP -->
          <h1 class="text-3xl">
            <?= $translations['main'] ?>
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
                <?php if ($vpnStatus['status'] === 'active'): ?>
                  <img src="/public/assets/images/icons/services/monoblock/on_top2.svg" alt="monoblock_part1"
                    loading="lazy" class="z-20 w-full animation_monoblock_on">
                  <img src="/public/assets/images/icons/services/monoblock/on_down.svg" alt="monoblock_part2"
                    loading="lazy" "-translate-y-[30%] z-10 w-full">
                <?php else: ?>
                  <img src="/public/assets/images/icons/services/monoblock/off_top2.svg" alt="monoblock_part1"
                    loading="lazy" class="z-20 w-full animation_monoblock_off">
                  <img src="/public/assets/images/icons/services/monoblock/off_down.svg" alt="monoblock_part2"
                    loading="lazy" class="-translate-y-[30%] z-10 w-full">
                <?php endif; ?>
              </div>

              <p class="absolute text-2xl font-bold bottom-10">Статус: <?= $vpnStatus['status_text'] ?></p>
            </div>

            <!-- BLOCK-2 => INFORMATION PANELS -->
            <div
              class="flex-1 h-full max-w-[350px] border border-white/20 p-6 rounded-xl bg-gradient-to-r from-green-950/50 via-black to-green-950/40">
              <ul class="flex flex-col gap-4 w-full text-xl">
                <!-- content 1 -->
                <li class="flex border-solid border-white/10 border-b p-2 pb-4 justify-between items-center w-full">
                  <span class="text-gray-400"><?= $translations['ping'] ?>:</span>
                  <div class="flex items-center gap-2">
                    <i
                      class="fas <?= $formattedVpnStatus['ping_icon'] ?> <?= $formattedVpnStatus['ping_class'] ?> text-sm"></i>
                    <span class="<?= $formattedVpnStatus['ping_class'] ?>"
                      data-ping><?= $formattedVpnStatus['ping_ms'] ?> ms</span>
                  </div>
                </li>
                <!-- content 2 -->
                <li class="flex border-solid border-white/10 border-b p-2 pb-4 justify-between items-center w-full">
                  <span class="text-gray-400"><?= $translations['protocol'] ?>:</span>
                  <span class="text-white text-lg font-light"
                    data-protocol><?= $formattedVpnStatus['protocol'] ?></span>
                </li>
                <!-- content 3 -->
                <li class="flex border-solid border-white/10 border-b p-2 pb-4 justify-between items-center w-full">
                  <span class="text-gray-400"><?= $translations['ip_address'] ?>:</span>
                  <span class="text-white text-lg font-light" data-ip><?= $formattedVpnStatus['ip_address'] ?></span>
                </li>
                <!-- content 4 -->
                <li class="flex p-2 justify-between items-center w-full">
                  <span class="text-gray-400"><?= $translations['server'] ?>:</span>
                  <span class="text-yellow-400 text-sm font-light"
                    data-server><?= $formattedVpnStatus['location'] ?></span>
                </li>
              </ul>
            </div>

          </div>

        </section>

        <!-- SECTION = PROFILE -->
        <section
          class="hidden overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black"
          data-section="profile">
          <!-- header logo -->
          <div class="w-full h-[300px]">
            <div
              class="overflow-hidden absolute flex gap-4 px-6 justify-center items-center w-full bg-[#0B0C1A] top-0 h-[300px] rounded-b-xl">
              <!-- backgound -->
              <img data-theme-invert src="/public/assets/images/background/stars.svg" alt="background"
                class="absolute right-0 scale-[1.5] animate-pulse duration-4000" loading="lazy">
              <img data-theme-invert src="/public/assets/images/background/stars.svg" alt="background"
                class="absolute left-0 scale-[1.5] animate-pulse duration-1000" loading="lazy">
              <img data-theme-invert src="/public/assets/images/background/stars.svg" alt="background"
                class="absolute rw-full h-full animate-pulse duration-7000" loading="lazy">

              <img src="/public/assets/images/icons/services/avatar/1.png" alt="avatar" class="rounded-xl w-18 h-18">
              <h3 class="text-2xl font-bold">
                <?= htmlspecialchars($userProfile['personal_info']['first_name'] . ' ' . $userProfile['personal_info']['last_name']) ?>
              </h3>

              <!-- information block -->
              <div class="left-4 right-4 mx-auto bg-white rounded-2xl p-4">
                <h3 class="text-lg font-semibold text-black">Статистика профиля</h3>
                <ul class="grid grid-cols-2 grid-rows-2 gap-1 mt-4 ">
                  <!-- block 1 -->
                  <li class="flex gap-2 items-center">
                    <img data-theme-invert class="w-8" src=" /public/assets/images/icons/services/profile/wifi.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">VPN</h4>
                      <div class="text-[12px] text-gray-400">
                        <?= $userProfile['subscription_info']['status'] === 'active' ? 'Активен' : 'Неактивен' ?></div>
                    </div>
                  </li>
                  <!-- block 2 -->
                  <li class="flex gap-2 items-center justify-center">
                    <img data-theme-invert class=" w-7"
                      src=" /public/assets/images/icons/services/profile/fa_language.svg" alt="icon_wifi"
                      loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Язык</h4>
                      <div class="text-[12px] text-gray-400">Русский</div>
                    </div>
                  </li>
                  <!-- block 3 -->
                  <li class="flex gap-2 items-center">
                    <img data-theme-invert class="w-7" src=" /public/assets/images/icons/services/profile/server.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">
                        <?= $userProfile['subscription_info']['count_days'] ?> дней</h4>
                      <div class="text-[12px] text-gray-400">Осталось</div>
                    </div>
                  </li>
                  <!-- block 4 -->
                  <li class="flex gap-2 items-center justify-center">
                    <img data-theme-invert class="w-7" src=" /public/assets/images/icons/services/profile/theme.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center ">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Тема</h4>
                      <div class="text-[12px] text-gray-400">Темная</div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div class="px-6 pt-4">
            <!-- data -->
            <div class="flex flex-col gap-4 mb-4">
              <h4 class="text-xl font-semibold">Данные</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                  <!-- info -->
                  <div class="flex flex-col justify-center gap-1">
                    <h4 class="text-sm font-semibold">VPN ключ</h4>
                    <p class="overflow-hidden h-8  break-all text-[12px] text-white/50 w-[75%]">
                      vless://dd046f11-ca13-4d75-aae7-c429903d91e9@nl.coravpn.online:443?type=grpc&mode=gun&serviceName=&security=reality&fp=firefox&sni=google.com&pbk=MPAVcf5ZSdhln_H1BxjluPind3sr0TWy_c6EoSfY7BE&sid=63d5&spx=/M0mUAooHJRuKiRg#%F0%9F%87%B3%F0%9F%87%B1TQ-timqwees
                    </p>
                  </div>
                  <div class="flex gap-2 justify-end items-center">
                      <i class="fa fa-copy text-lg text-gray-400"></i>
                      <i class="fa fa-trash text-lg text-red-400"></i>
                    </div>
                </li>
              </ul>
            </div>

          </div>

        </section>

        <!-- SECTION = SETTING -->
        <section
          class="hidden px-6 pt-[5rem] overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black"
          data-section="setting">

          <!-- 1 -->
          <div class="flex flex-col gap-4 mb-4">
            <h4 class="text-xl font-semibold">Настройки приложения</h4>
            <ul class="flex flex-col gap-2.5">
              <!-- theme -->
              <li class="flex bg-[#2C2A2A] p-2 px-2.5 justify-between items-center rounded-2xl">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <i class="fa fa-sun text-[#7DFF6F] text-2xl -rotate-[15deg]"></i>
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
                    <input type="checkbox" value="" class="sr-only peer" data-darkModeToggle>
                    <div
                      class="relative w-9 h-5 bg-white/20 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-400 dark:peer-checked:bg-green-400">
                    </div>
                  </label>
                </div>
              </li>
              <!-- language -->
              <li class="flex bg-[#2C2A2A] p-2 px-2.5 justify-between items-center rounded-2xl">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <i class="fa fa-solid fa-language text-[#7DFF6F] text-2xl"></i>
                  </div>
                  <div class="flex flex-col justify-center translate-y-1.5">
                    <h4 class="text-sm"><?= $translations['language'] ?> English</h4>
                    <p class="overflow-hidden h-8 break-all text-[12px] text-white/50 w-[150px]">
                      <?= $translations['language_switch'] ?>
                    </p>
                  </div>
                </div>
                <!-- button -->
                <div class="flex gap-2 justify-end items-center">
                  <label class="inline-flex items-center me-5 cursor-pointer">
                    <input type="checkbox" value="rus" class="sr-only peer" data-language>
                    <div
                      class="relative w-9 h-5 bg-white/20 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-400 dark:peer-checked:bg-green-400">
                    </div>
                  </label>
                </div>
              </li>
            </ul>
          </div>

          <!-- 2 -->
          <div class="flex flex-col gap-4 mb-4">
            <h4 class="text-xl font-semibold">Конфиденциальность</h4>
            <div class="flex flex-col">
              <!-- theme -->
              <a href="/"
                class="flex bg-[#2C2A2A] p-2 px-4 justify-between items-center border-b-[.5px] border-white/50 cursor-pointer">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <img class="text-2xl" src="/public/assets/images/icons/services/profile/shield.svg"
                      alt="shield icon" loading="lazy">
                  </div>
                  <div class="flex flex-col justify-center">
                    <h4 class="text-sm">Автооплата</h4>
                  </div>
                </div>
                <!-- icon go -->
                <div class="flex justify-center items-center">
                  <i class="fa fa-solid fa-angle-right text-white/70 text-xl"></i>
                </div>
              </a>
              <!-- theme -->
              <button data-toggle-modal="politic"
                class="flex bg-[#2C2A2A] p-2 px-4 justify-between items-center border-b-[.5px] border-white/50 cursor-pointer">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <img class="text-2xl" src="/public/assets/images/icons/services/profile/shield.svg"
                      alt="shield icon" loading="lazy">
                  </div>
                  <div class="flex flex-col justify-center">
                    <h4 class="text-sm">Политика конфиденциальности</h4>
                  </div>
                </div>
                <!-- icon go -->
                <div class="flex justify-center items-center">
                  <i class="fa fa-solid fa-angle-right text-white/70 text-xl"></i>
                </div>
              </button>
              <!-- theme -->
              <button data-toggle-modal="access"
                class="flex bg-[#2C2A2A] p-2 px-4 justify-between items-center cursor-pointer">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <img class="text-2xl" src="/public/assets/images/icons/services/profile/shield.svg"
                      alt="shield icon" loading="lazy">
                  </div>
                  <div class="flex flex-col justify-center">
                    <h4 class="text-sm">Пользовательское соглашение</h4>
                  </div>
                </div>
                <!-- icon go -->
                <div class="flex justify-center items-center">
                  <i class="fa fa-solid fa-angle-right text-white/70 text-xl"></i>
                </div>
              </button>

            </div>
          </div>

        </section>
        <!-- SECTION = REFER -->
        <section
          class="hidden overflow-hidden relative flex flex-col box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black"
          data-section="referal">
          <!-- header logo -->
          <div class="w-full h-[300px]">
            <div
              class="overflow-hidden absolute flex gap-4 px-6 justify-center items-center w-full bg-[#0B0C1A] top-0 h-[300px] rounded-b-xl">
              <!-- backgound -->
              <img data-theme-invert src="/public/assets/images/background/stars.svg" alt="background"
                class="absolute right-0 scale-[1.5] animate-pulse duration-4000" loading="lazy">
              <img data-theme-invert src="/public/assets/images/background/stars.svg" alt="background"
                class="absolute left-0 scale-[1.5] animate-pulse duration-1000" loading="lazy">
              <img data-theme-invert src="/public/assets/images/background/stars.svg" alt="background"
                class="absolute rw-full h-full animate-pulse duration-7000" loading="lazy">

              <img src="/public/assets/images/icons/services/avatar/1.png" alt="avatar" class="rounded-xl w-18 h-18">
              <h3 class="text-2xl font-bold">
                <?= htmlspecialchars($userProfile['personal_info']['first_name'] . ' ' . $userProfile['personal_info']['last_name']) ?>
              </h3>

              <!-- information block -->
              <div class="left-4 right-4 mx-auto bg-white rounded-2xl p-4">
                <h3 class="text-lg font-semibold text-black">Статистика профиля</h3>
                <ul class="grid grid-cols-2 grid-rows-2 gap-1 mt-4 ">
                  <!-- block 1 -->
                  <li class="flex gap-2 items-center">
                    <img data-theme-invert class="w-8" src="/public/assets/images/icons/services/profile/live.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Статус</h4>
                      <div class="text-[12px] text-gray-400">
                        <?= $userProfile['subscription_info']['status'] === 'active' ? 'Активен' : 'Неактивен' ?></div>
                    </div>
                  </li>
                  <!-- block 2 -->
                  <li class="flex gap-2 items-center justify-center">
                    <img data-theme-invert class=" w-7" src=" /public/assets/images/icons/services/profile/piople.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Рефералы</h4>
                      <div class="text-[12px] text-gray-400"><?= $userProfile['referal_info']['refer_count'] ?> человек
                      </div>
                    </div>
                  </li>
                  <!-- block 3 -->
                  <li class="flex gap-2 items-center">
                    <img data-theme-invert class="w-7" src=" /public/assets/images/icons/services/profile/circle.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Скидка</h4>
                      <div class="text-[12px] text-gray-400">
                        <?= $userProfile['referal_info']['has_discount'] ? 'Активна' : 'Нет' ?></div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div class="px-6 pt-4">
            <!-- data -->
            <div class="flex flex-col gap-3 mb-3">
              <h4 class="text-lg font-semibold">Мои реферальные даные</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2.5">
                    <h4 class="text-sm font-semibold">Реферальный код</h4>
                    <input
                      class="overflow-hidden bg-black h-8 break-all text-[12px] text-white/50 w-[90%] py-[5px] uppercase px-2 flex items-cnter rounded-lg focus:outline-none"
                      value="qwees123vpn" readonly>
                  </div>
                  <!-- button -->
                  <div class="flex gap-2 justify-end items-center">
                    <i class="fa fa-copy text-2xl pr-2 text-gray-400"></i>
                  </div>
                </li>
              </ul>
            </div>
            <!-- input code -->
            <div class="flex flex-col gap-3 mb-3">
              <h4 class="text-lg font-semibold">Вставить реферальный код</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex gap-3 flex-col bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2.5">
                    <h4 class="text-sm font-semibold">Введите 4 цифры реферала</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-xl text-center text-white/50 py-6 uppercase px-2 flex items-cnter rounded-lg focus:outline-none"
                      placeholder="qwees * * * * vpn" maxlength="4">
                  </div>
                  <!-- button -->
                  <div class="flex w-full">
                    <button
                      class="bg-white w-full cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-3 py-2">
                      Использовать
                    </button>
                  </div>
                </li>
              </ul>
            </div>
            <!-- data refers -->
            <div class="flex flex-col gap-3 mb-3">
              <h4 class="text-lg font-semibold">Вставить реферальный код</h4>
              <ul class="grid grid-cols-2 grid-rows-2 bg-[#2C2A2A] rounded-xl gap-4 p-4">
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Имя / Фамилия</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                      placeholder="tim qwees" maxlength="4">
                  </div>
                </li>
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Реферальный код</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                      placeholder="qwees1234vpn" maxlength="4">
                  </div>
                </li>
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Дата активации</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                      placeholder="22.12.2006" maxlength="4">
                  </div>
                </li>
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Вы получили</h4>
                    <div type="text"
                      class="overflow-hidden items-center bg-black gap-1.5 h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none">
                      <font class="text-green-400">-20%</font> на все
                    </div>
                  </div>
                </li>
              </ul>
            </div>

            <!-- data refers -->
            <div class="flex flex-col gap-3 mb-3">
              <h4 class="text-lg font-semibold">Вставить реферальный код</h4>
              <ul class="grid grid-cols-2 grid-rows-2 bg-[#2C2A2A] rounded-xl gap-4 p-4">
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Имя / Фамилия</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                      placeholder="tim qwees" maxlength="4">
                  </div>
                </li>
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Реферальный код</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                      placeholder="qwees1234vpn" maxlength="4">
                  </div>
                </li>
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Дата активации</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                      placeholder="22.12.2006" maxlength="4">
                  </div>
                </li>
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Вы получили</h4>
                    <div type="text"
                      class="overflow-hidden items-center bg-black gap-1.5 h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none">
                      <font class="text-green-400">-20%</font> на все
                    </div>
                  </div>
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
                class="absolute h-full right-0 top-0 bottom-0 mx-auto animate-pulse duration-2000" loading="lazy">

              <img src="/public/assets/images/icons/services/avatar/1.png" alt="avatar" class="rounded-xl w-18 h-18">
              <h3 class="text-2xl font-bold" data-user-name><?= $formattedUserProfile['full_name'] ?></h3>

              <!-- information block -->
              <div class="absolute -bottom-[6.5rem] left-4 right-4 mx-auto bg-white rounded-2xl p-4">
                <h3 class="text-lg font-semibold text-black">Статистика профиля</h3>
                <ul class="grid grid-cols-2 grid-rows-2 gap-1 mt-4 justify-between">
                  <!-- block 1 -->
                  <li class="flex gap-2 items-center">
                    <img data-theme-invert class="w-8" src=" /public/assets/images/icons/services/profile/wifi.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">VPN</h4>
                      <div class="text-[12px] text-gray-400" data-profile-status>
                        <?= $formattedUserProfile['status_text'] ?></div>
                    </div>
                  </li>
                  <!-- block 2 -->
                  <li class="flex gap-2 items-center justify-center">
                    <img data-theme-invert class=" w-7"
                      src=" /public/assets/images/icons/services/profile/fa_language.svg" alt="icon_wifi"
                      loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Язык</h4>
                      <div class="text-[12px] text-gray-400">Русский</div>
                    </div>
                  </li>
                  <!-- block 3 -->
                  <li class="flex gap-2 items-center">
                    <img data-theme-invert class="w-7" src=" /public/assets/images/icons/services/profile/server.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Дней</h4>
                      <div class="text-[12px] text-gray-400" data-days-left><?= $formattedUserProfile['days_left'] ?>
                      </div>
                    </div>
                  </li>
                  <!-- block 4 -->
                  <li class="flex gap-2 items-center justify-center">
                    <img data-theme-invert class="w-7" src=" /public/assets/images/icons/services/profile/theme.svg"
                      alt="icon_wifi" loading="lazy">
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
            <?php if ($user['status'] === 'on' && !empty($user['subscription'])): ?>
            <div class="flex flex-col gap-4 mb-4">
              <h4 class="text-xl font-semibold">Данные</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                  <!-- info -->
                  <div class="flex flex-col justify-center w-[150px] gap-1">
                    <h4 class="text-sm font-semibold">VPN ключ</h4>
                    <p id="vpn-key" class="overflow-hidden h-8 break-all text-[12px] text-white/50 w-[150px]">
                      <?php echo htmlspecialchars($user['subscription'] ?? ''); ?>
                    </p>
                  </div>
                  <!-- button -->
                  <div class="flex gap-2 justify-end items-center">
                    <button onclick="copyVpnKey()" class="text-lg text-gray-400 hover:text-white transition-colors" title="Копировать ключ">
                      <i class="fa fa-copy"></i>
                    </button>
                    <button onclick="deleteSubscription()" class="text-lg text-red-400 hover:text-red-300 transition-colors" title="Удалить подписку">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </li>
              </ul>
            </div>
            <?php endif; ?>
            <!-- setting -->
            <!-- <div class="flex flex-col gap-4 mb-4">
              <h4 class="text-xl font-semibold">Настройки приложения</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex bg-[#2C2A2A] p-2 px-2.5 justify-between items-center rounded-2xl">
            <div class="flex justify-center items-center gap-2">
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
            <div class="flex gap-2 justify-end items-center">
              <label class="inline-flex items-center me-5 cursor-pointer">
                <input type="checkbox" value="" class="sr-only peer" data-darkModeToggle>
                <div
                  class="relative w-9 h-5 bg-white/20 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-400 dark:peer-checked:bg-green-400">
                </div>
              </label>
            </div>
            </li>
            </ul>
          </div> -->
          </div>

        </section>
        <!-- SECTION = SETTING -->
        <section
          class="hidden px-6 pt-[5rem] overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black"
          data-section="setting">

          <!-- 1 -->
          <div class="flex flex-col gap-4 mb-4">
            <h4 class="text-xl font-semibold">Настройки приложения</h4>
            <ul class="flex flex-col gap-2.5">
              <!-- theme -->
              <li class="flex bg-[#2C2A2A] p-2 px-2.5 justify-between items-center rounded-2xl">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <i class="fa fa-sun text-[#7DFF6F] text-2xl -rotate-[15deg]"></i>
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
                    <input type="checkbox" value="" class="sr-only peer" data-darkModeToggle>
                    <div
                      class="relative w-9 h-5 bg-white/20 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-400 dark:peer-checked:bg-green-400">
                    </div>
                  </label>
                </div>
              </li>
              <!-- language -->
              <li class="flex bg-[#2C2A2A] p-2 px-2.5 justify-between items-center rounded-2xl">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <i class="fa fa-solid fa-language text-[#7DFF6F] text-2xl"></i>
                  </div>
                  <div class="flex flex-col justify-center translate-y-1.5">
                    <h4 class="text-sm"><?= $translations['language'] ?> English</h4>
                    <p class="overflow-hidden h-8 break-all text-[12px] text-white/50 w-[150px]">
                      <?= $translations['language_switch'] ?>
                    </p>
                  </div>
                </div>
                <!-- button -->
                <div class="flex gap-2 justify-end items-center">
                  <label class="inline-flex items-center me-5 cursor-pointer">
                    <input type="checkbox" value="rus" class="sr-only peer" data-language>
                    <div
                      class="relative w-9 h-5 bg-white/20 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-400 dark:peer-checked:bg-green-400">
                    </div>
                  </label>
                </div>
              </li>
            </ul>
          </div>


          <!-- 2 -->
          <div class="flex flex-col gap-4 mb-4">
            <h4 class="text-xl font-semibold">Конфиденциальность</h4>
            <div class="flex flex-col">
              <!-- theme -->
              <a href="/"
                class="flex bg-[#2C2A2A] p-2 px-4 justify-between items-center border-b-[.5px] border-white/50 cursor-pointer">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <img class="text-2xl" src="/public/assets/images/icons/services/profile/shield.svg"
                      alt="shield icon" loading="lazy">
                  </div>
                  <div class="flex flex-col justify-center">
                    <h4 class="text-sm">Автооплата</h4>
                  </div>
                </div>
                <!-- icon go -->
                <div class="flex justify-center items-center">
                  <i class="fa fa-solid fa-angle-right text-white/70 text-xl"></i>
                </div>
              </a>
              <!-- theme -->
              <button data-toggle-modal="politic"
                class="flex bg-[#2C2A2A] p-2 px-4 justify-between items-center border-b-[.5px] border-white/50 cursor-pointer">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <img class="text-2xl" src="/public/assets/images/icons/services/profile/shield.svg"
                      alt="shield icon" loading="lazy">
                  </div>
                  <div class="flex flex-col justify-center">
                    <h4 class="text-sm">Политика конфиденциальности</h4>
                  </div>
                </div>
                <!-- icon go -->
                <div class="flex justify-center items-center">
                  <i class="fa fa-solid fa-angle-right text-white/70 text-xl"></i>
                </div>
              </button>
              <!-- theme -->
              <button data-toggle-modal="access"
                class="flex bg-[#2C2A2A] p-2 px-4 justify-between items-center cursor-pointer">
                <!-- info -->
                <div class="flex justify-center items-center gap-3">
                  <!-- icon -->
                  <div class="flex justify-center items-center">
                    <img class="text-2xl" src="/public/assets/images/icons/services/profile/shield.svg"
                      alt="shield icon" loading="lazy">
                  </div>
                  <div class="flex flex-col justify-center">
                    <h4 class="text-sm">Пользовательское соглашение</h4>
                  </div>
                </div>
                <!-- icon go -->
                <div class="flex justify-center items-center">
                  <i class="fa fa-solid fa-angle-right text-white/70 text-xl"></i>
                </div>
              </button>

        </section>
        <!-- SECTION = REFER -->
        <section
          class="hidden overflow-hidden relative flex flex-col pb-[95px] box-border w-full min-h-[100dvh] bg-gradient-to-t from-black via-green-950 to-black"
          data-section="referal">
          <!-- header logo -->
          <div class="w-full h-[300px]">
            <div
              class="absolute flex flex-col gap-4 justify-center items-center w-full bg-[#0B0C1A] top-0 h-[300px] rounded-b-xl">
              <!-- backgound -->
              <img data-theme-invert src=" /public/assets/images/background/stars.svg" alt="background"
                class="absolute h-full right-0 top-0 bottom-0 mx-auto animate-pulse duration-2000" loading="lazy">

              <img src="/public/assets/images/icons/services/avatar/1.png" alt="avatar" class="rounded-xl w-18 h-18">
              <h3 class="text-2xl font-bold" data-user-name><?= $formattedUserProfile['full_name'] ?></h3>

              <!-- information block -->
              <div class="absolute -bottom-[6.5rem] left-4 right-4 mx-auto bg-white rounded-2xl p-4">
                <h3 class="text-lg font-semibold text-black">Статистика профиля</h3>
                <ul class="grid grid-cols-2 grid-rows-2 gap-1 mt-4 justify-between">
                  <!-- block 1 -->
                  <li class="flex gap-2 items-center">
                    <img data-theme-invert class="w-8" src=" /public/assets/images/icons/services/profile/wifi.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Статус</h4>
                      <div class="text-[12px] text-gray-400" data-profile-status>
                        <?= $formattedUserProfile['status_text'] ?></div>
                    </div>
                  </li>
                  <!-- block 2 -->
                  <li class="flex gap-2 items-center justify-center">
                    <img data-theme-invert class=" w-7"
                      src=" /public/assets/images/icons/services/profile/fa_language.svg" alt="icon_wifi"
                      loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Вставляли</h4>
                      <div class="text-[12px] text-gray-400">Не использован</div>
                    </div>
                  </li>
                  <!-- block 3 -->
                  <li class="flex gap-2 items-center">
                    <img data-theme-invert class="w-7" src=" /public/assets/images/icons/services/profile/server.svg"
                      alt="icon_wifi" loading="lazy">
                    <div class="flex flex-col justify-center">
                      <h4 class="text-[16px] font-medium text-black translate-y-1">Дней</h4>
                      <div class="text-[12px] text-gray-400" data-days-left><?= $formattedUserProfile['days_left'] ?>
                      </div>
                    </div>
                  </li>

                </ul>
              </div>
            </div>
          </div>

          <div class="px-6 pt-[7.5rem]">
            <!-- data -->
            <div class="flex flex-col gap-3 mb-3">
              <h4 class="text-lg font-semibold">Мои реферальные даные</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2.5">
                    <h4 class="text-sm font-semibold">Реферальный код</h4>
                    <input
                      class="overflow-hidden bg-black h-8 break-all text-[12px] text-white/50 w-[90%] py-[5px] uppercase px-2 flex items-cnter rounded-lg focus:outline-none"
                      value="qwees123vpn" readonly>
                  </div>
                  <!-- button -->
                  <div class="flex gap-2 justify-end items-center">
                    <i class="fa fa-copy text-2xl pr-2 text-gray-400"></i>
                  </div>
                </li>
              </ul>
            </div>
            <!-- input code -->
            <div class="flex flex-col gap-3 mb-3">
              <h4 class="text-lg font-semibold">Вставить реферальный код</h4>
              <ul class="flex flex-col gap-2.5">
                <li class="flex gap-3 flex-col bg-[#2C2A2A] p-4 justify-between items-center rounded-xl">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2.5">
                    <h4 class="text-sm font-semibold">Введите 4 цифры реферала</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-xl text-center text-white/50 py-6 uppercase px-2 flex items-cnter rounded-lg focus:outline-none"
                      placeholder="qwees * * * * vpn" maxlength="4">
                  </div>
                  <!-- button -->
                  <div class="flex w-full">
                    <button
                      class="bg-white w-full cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-3 py-2">
                      Использовать
                    </button>
                  </div>
                </li>
              </ul>
            </div>
            <!-- data refers -->
            <div class="flex flex-col gap-3 mb-3">
              <h4 class="text-lg font-semibold">Вставить реферальный код</h4>
              <ul class="grid grid-cols-2 grid-rows-2 bg-[#2C2A2A] rounded-xl gap-4 p-4">
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Имя / Фамилия</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                      placeholder="tim qwees" maxlength="4">
                  </div>
                </li>
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Реферальный код</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                      placeholder="qwees1234vpn" maxlength="4">
                  </div>
                </li>
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Дата активации</h4>
                    <input type="text"
                      class="overflow-hidden bg-black h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none"
                      placeholder="22.12.2006" maxlength="4">
                  </div>
                </li>
                <li class="flex gap-3 flex-col justify-between items-center">
                  <!-- info -->
                  <div class="flex w-full flex-col justify-center gap-2">
                    <h4 class="text-sm font-semibold">Вы получили</h4>
                    <div type="text"
                      class="overflow-hidden items-center bg-black gap-1.5 h-8 break-all text-sm text-white/50 px-2 flex rounded-lg focus:outline-none">
                      <font class="text-green-400">-20%</font> на все
                    </div>
                  </div>
                </li>
              </ul>
            </div>

          </div>

        </section>
      </div>
    </main>

    <!-- modal = Политика конфиденциальности -->
    <div data-modal="politic" class="modal-overlay hidden">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Политика конфиденциальности</h3>
          <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
          <p><strong>Политика конфиденциальности QweesVPN</strong></p>
          <hr class="my-4">

          <p><strong>Дата вступления в силу:</strong> 26.03.2026</p>
          <hr class="my-4">

          <p><strong>1. Общие положения</strong></p>
          <p>Настоящая Политика конфиденциальности описывает, какие данные собирает сервис QweesVPN и как они
            используются.</p>
          <p>Используя сервис, пользователь соглашается с данной Политикой.</p>
          <hr class="my-4">

          <p><strong>2. Какие данные мы собираем</strong></p>
          <p>Мы можем собирать следующие данные:</p>
          <p>- адрес электронной почты (при регистрации);</p>
          <p>- технические данные устройства (тип устройства, версия ОС);</p>
          <p>- данные об использовании сервиса (ошибки, сбои, диагностика).</p>
          <hr class="my-4">

          <p><strong>3. Какие данные мы НЕ собираем</strong></p>
          <p>QweesVPN придерживается политики конфиденциальности и не собирает:</p>
          <p>- историю посещенных сайтов;</p>
          <p>- содержимое интернет-трафика;</p>
          <p>- DNS-запросы пользователей;</p>
          <p>- реальные IP-адреса (при использовании VPN-соединения).</p>
          <hr class="my-4">

          <p><strong>4. Цели обработки данных</strong></p>
          <p>Собранные данные используются для:</p>
          <p>- предоставления и улучшения сервиса;</p>
          <p>- технической поддержки пользователей;</p>
          <p>- обеспечения безопасности и предотвращения злоупотреблений.</p>
          <hr class="my-4">

          <p><strong>5. Передача данных третьим лицам</strong></p>
          <p>Мы не продаем и не передаем персональные данные третьим лицам, за исключением случаев:</p>
          <p>- требования законодательства;</p>
          <p>- защиты прав и безопасности сервиса;</p>
          <p>- обработки платежей через сторонние платежные системы.</p>
          <hr class="my-4">

          <p><strong>6. Хранение данных</strong></p>
          <p>Данные хранятся только столько, сколько необходимо для работы сервиса.</p>
          <p>Мы принимаем разумные меры для защиты информации от несанкционированного доступа.</p>
          <hr class="my-4">

          <p><strong>7. Права пользователя</strong></p>
          <p>Пользователь имеет право:</p>
          <p>- запросить доступ к своим данным;</p>
          <p>- требовать исправления или удаления данных;</p>
          <p>- отозвать согласие на обработку данных.</p>
          <hr class="my-4">

          <p><strong>8. Cookies</strong></p>
          <p>Мы можем использовать cookies для улучшения работы сайта и сервиса.</p>
          <p>Пользователь может отключить cookies в настройках браузера.</p>
          <hr class="my-4">

          <p><strong>9. Изменения политики</strong></p>
          <p>QweesVPN может обновлять данную Политику.</p>
          <p>Изменения вступают в силу с момента публикации.</p>
          <hr class="my-4">

          <p><strong>10. Контакты</strong></p>
          <p>Email: timqwees@gmail.com</p>
          <p>Сайт: qweesvpn.ru</p>
        </div>
        <div class="modal-footer">
          <button class="modal-btn-close">Закрыть</button>
        </div>
      </div>
    </div>

    <!-- modal = Пользовательское соглашение -->
    <div data-modal="access" class="modal-overlay hidden">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Пользовательское соглашение</h3>
          <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
          <p><strong>Пользовательское соглашение QweesVPN</strong></p>
          <hr class="my-4">

          <p><strong>Дата вступления в силу:</strong> 26.03.2026</p>
          <hr class="my-4">

          <p><strong>1. Общие положения</strong></p>
          <p>Настоящее Пользовательское соглашение регулирует отношения между сервисом QweesVPN и
            пользователем.</p>
          <p>Используя сервис, пользователь подтверждает согласие с условиями.</p>
          <p>Если пользователь не согласен — он обязан прекратить использование.</p>
          <hr class="my-4">

          <p><strong>2. Описание услуги</strong></p>
          <p>QweesVPN предоставляет услуги VPN, включая:</p>
          <p>- шифрование интернет-трафика;</p>
          <p>- защиту конфиденциальности;</p>
          <p>- изменение IP-адреса.</p>
          <p>Сервис предоставляется «как есть» без гарантий.</p>
          <hr class="my-4">

          <p><strong>3. Регистрация и доступ</strong></p>
          <p>Для использования может потребоваться регистрация.</p>
          <p>Пользователь обязан предоставлять достоверные данные и не передавать доступ третьим лицам.</p>
          <p>Пользователь несет ответственность за действия в аккаунте.</p>
          <hr class="my-4">

          <p><strong>4. Допустимое использование</strong></p>
          <p>Запрещено использовать сервис для:</p>
          <p>- нарушения законодательства;</p>
          <p>- распространения вредоносного ПО;</p>
          <p>- атак (DDoS, brute-force и т.д.);</p>
          <p>- спама и мошенничества.</p>
          <p>При нарушении аккаунт может быть заблокирован.</p>
          <hr class="my-4">

          <p><strong>5. Конфиденциальность</strong></p>
          <p>QweesVPN уважает конфиденциальность пользователей.</p>
          <p>Мы можем собирать технические данные для работы сервиса.</p>
          <p>Мы не храним историю посещений и содержимое трафика.</p>
          <p>Данные могут быть раскрыты только по закону.</p>
          <hr class="my-4">

          <p><strong>6. Платежи и подписка</strong></p>
          <p>Некоторые функции доступны по подписке.</p>
          <p>Подписка может продлеваться автоматически.</p>
          <p>Пользователь может отменить подписку.</p>
          <p>Возврат средств осуществляется согласно политике возвратов.</p>
          <hr class="my-4">

          <p><strong>7. Ограничение ответственности</strong></p>
          <p>QweesVPN не несет ответственности за:</p>
          <p>- действия пользователей;</p>
          <p>- потерю данных;</p>
          <p>- сбои в работе сервиса.</p>
          <p>Использование происходит на риск пользователя.</p>
          <hr class="my-4">

          <p><strong>8. Блокировка доступа</strong></p>
          <p>Сервис имеет право ограничить или удалить аккаунт при нарушении условий.</p>
          <p>Пользователь может прекратить использование в любое время.</p>
          <hr class="my-4">

          <p><strong>9. Изменения соглашения</strong></p>
          <p>Сервис может обновлять условия в любое время.</p>
          <p>Продолжение использования означает согласие с изменениями.</p>
          <hr class="my-4">

          <p><strong>10. Применимое право</strong></p>
          <p>Соглашение регулируется законодательством [укажи страну].</p>
          <hr class="my-4">

          <p><strong>11. Контакты</strong></p>
          <p>Email: timqwees@gmail.com</p>
          <p>Сайт: qweesvpn.ru</p>
        </div>
        <div class="modal-footer">
          <button class="modal-btn-close">Закрыть</button>
        </div>
      </div>
    </div>
  </div>
  </section>

  <script src=" /public/assets/scripts/main/main.js"></script>
  <script src="/public/assets/scripts/theme/main.js"></script>
  <script src="/public/assets/scripts/lang/lang.js"></script>
  
  <script>
    // Копирование VPN ключа
    function copyVpnKey() {
      const vpnKeyElement = document.getElementById('vpn-key');
      const vpnKey = vpnKeyElement.textContent.trim();
      
      if (vpnKey) {
        navigator.clipboard.writeText(vpnKey).then(() => {
          // Показать уведомление об успешном копировании
          showNotification('VPN ключ скопирован!', 'success');
        }).catch(err => {
          console.error('Ошибка копирования:', err);
          showNotification('Ошибка копирования ключа', 'error');
        });
      }
    }
    
    // Удаление подписки
    function deleteSubscription() {
      if (confirm('Вы уверены, что хотите удалить подписку? Это действие нельзя отменить.')) {
        fetch('/api/subscription/delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotification('Подписка успешно удалена', 'success');
            // Перезагрузить страницу через 2 секунды
            setTimeout(() => {
              location.reload();
            }, 2000);
          } else {
            showNotification(data.error || 'Ошибка удаления подписки', 'error');
          }
        })
        .catch(error => {
          console.error('Ошибка:', error);
          showNotification('Ошибка удаления подписки', 'error');
        });
      }
    }
    
    // Показать уведомление
    function showNotification(message, type = 'info') {
      // Создать элемент уведомления
      const notification = document.createElement('div');
      notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 transform translate-x-full transition-transform duration-300`;
      
      // Установить цвет в зависимости от типа
      switch(type) {
        case 'success':
          notification.classList.add('bg-green-500');
          break;
        case 'error':
          notification.classList.add('bg-red-500');
          break;
        default:
          notification.classList.add('bg-blue-500');
      }
      
      notification.textContent = message;
      
      // Добавить на страницу
      document.body.appendChild(notification);
      
      // Показать уведомление
      setTimeout(() => {
        notification.classList.remove('translate-x-full');
      }, 100);
      
      // Скрыть через 3 секунды
      setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
          document.body.removeChild(notification);
        }, 300);
      }, 3000);
    }
  </script>
  </div>
</body>

</html>