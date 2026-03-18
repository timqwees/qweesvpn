<?php
use Setting\Route\Function\Functions;
use App\Config\Session;
include_once dirname(__DIR__, 3) . '/assets/script/script.php';
$INSTALL_OS = getV2RayTunInstallUrl();

// Инициализация сессии
Session::init();
$client = (new Functions())->client($_SESSION['client'] ?? 0);
$add_v2ray_client = "v2raytun://import/{$client['vpn_subscription']}";

$free_client = [
  "days" => $_ENV['VPN_FREE_CLIENT_DAYS'] ?: 15,
  "key" => $_ENV['VPN_FREE_CLIENT_FREEKEY'] ?: 'used_free'
];

// Проверка отправки email
$emailSuccess = false;
$redirectUrl = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
  $email = trim($_POST['email']);
  $_SESSION['temporary_email'] = $email;
  $emailSuccess = true; // Для JS

  // Безопасная инициализация переменных
  $tg_id = isset($client['tg_id']) ? strval($client['tg_id']) : '';
  $free_days = isset($free_client['days']) ? strval($free_client['days']) : '';
  $tg_username = isset($client['tg_username']) ? strval($client['tg_username']) : '';
  $free_key = isset($free_client['key']) ? strval($free_client['key']) : '';
  $divece_limit = 1;//unlimited
  $amount = 0;

  $redirectUrl = "/add_vpn_user/" .
    urlencode($tg_id) . "/" .
    urlencode($free_days) . "/" .
    urlencode($tg_username) . "/" .
    urlencode($divece_limit) . "/" .
    urlencode($amount) . "/" .
    urlencode($free_key);

  $_SESSION['redirect_url'] = $redirectUrl;
  $_SESSION['selection_pay_type'] = 'free';
}
?>
<!-- Стрелка-назад в левом верхнем углу -->

<div class="arrow-back" id="globalArrowBack" style="display: flex;">
  <svg viewBox="0 0 24 24" fill="none">
    <path d="M15 19L8 12L15 5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
  </svg>
  <span style="font-size:15px" id="backBtnText">Вернуться</span>
  <a href="/" id="arrowHomeLink" class="arrow-home" title="На главную"><i class="fa fas fa-home"></i></a>
</div>

<div class="relative flex justify-between items-center flex-row overflow-auto flex-col overflow-hidden">

  <div class="container h-[50vh] flex justify-end items-center flex-col gap-4">

    <div
      class="rounded-full border w-[150px] h-[150px] flex justify-center items-center animate-pulse border-purple-900"
      id="progress_rouded_main">
      <!-- 1 -->
      <span class=" border w-[130px] h-[130px] absolute rounded-full scale-150 border-b-transparent border-purple-400"
        id="progress_rouded_1"></span>
      <!-- 2 -->
      <span class="border w-[160px] h-[160px] absolute rounded-full scale-150 border-b-transparent border-purple-400"
        id="progress_rouded_2"></span>
      <!-- 3 -->
      <span class="border w-[190px] h-[190px] absolute rounded-full scale-150 border-b-transparent border-purple-400"
        id="progress_rouded_3"></span>

      <i class="fa fas fa-plug text-7xl rotate-45 text-purple-700" aria-hidden="true" id="icon"></i>
    </div>

    <nav class="flex first">
      <li
        class="flex items-center bg-white w-10 h-[3px] flex before:w-[15px] before:h-[15px] before:bg-white before:rounded-lg"
        id="line-1">
      </li>
      <li
        class="flex items-center bg-white w-10 h-[3px] flex before:w-[15px] before:h-[15px] before:bg-white before:rounded-lg"
        id="line-2"></li>
      <li
        class="flex items-center bg-white w-10 h-[3px] flex before:w-[15px] before:h-[15px] before:bg-white before:rounded-lg after:w-[15px] after:h-[15px] after:bg-white after:rounded-lg after:absolute after:-right-3 relative"
        id="line-3"></li>
    </nav>
  </div>

  <!-- start content #1 -->
  <div class="container overflow-hidden" id="content_start">
    <div class="mb-4 leading-[1.8]">
      <h2 class="text-white text-center">Настройка на <span
          class="text-[#615ced] bg-[#efeeff] py-[1px] px-[5px] rounded-md">
          <?= $INSTALL_OS['os'] ?>
        </span>
      </h2>
      <p class="text-gray-300 text-xs text-center">Настройка VPN происходит в 3 шага занимает меьще 1 минуты
      </p>
    </div>
    <div class="flex justify-center items-start flex-col gap-2">
      <button
        class="w-full transition-all relative flex gap-2 px-4 justify-center text-center items-center py-3.5 text-xs text-white text-base rounded-xl bg-gradient-to-r from-purple-600 to-purple-400 shadow-lg hover:from-purple-700 hover:to-purple-500 hover:animate-none hover:from-white hover:text-black transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden animate-pulse"
        onclick="Start()">
        <i class="fa fa-cogs text-lg relative z-10"></i>
        <span class="relative z-10">Начать настройку на этом устройстве</span>
      </button>
      <a class="w-full relative flex justify-center gap-2 px-4 items-center text-center py-3.5 text-xs text-white text-base rounded-xl shadow-lg bg-[#3F3447] border border-gray-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden"
        href="https://teletype.in/@artemon36/helps">
        <i class="fa fa-cloud-download text-lg" aria-hidden="true"></i>
        <span class="relative z-10">продолжить на другом устройстве</span>
      </a>
    </div>
  </div>

  <!-- content #2 -->
  <div class="hidden_content container overflow-hidden transition" id="content_two">
    <div class="mb-4">
      <h2 class="text-white text-center">Установка приложения</h2>
      <p class="text-gray-300 text-xs text-center leading-[1.8]">Для работы c VPN требуеться установить
        приложение
        <span class="text-[#615ced] bg-[#efeeff] p-[2px] px-[5px] rounded-md">v2Ray</span>
      </p>
    </div>
    <div class="flex justify-center items-stratch gap-2">
      <a class="w-full transition-all relative flex gap-2 px-4 justify-center items-center py-3.5 text-xs text-white text-base rounded-xl bg-gradient-to-r from-purple-600 to-purple-400 shadow-lg hover:from-purple-700 hover:to-purple-500 hover:animate-none hover:from-white hover:text-black transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden animate-pulse"
        href="<?= $INSTALL_OS['url'] ?>">
        <i class="fa fas fa-download text-lg relative z-10"></i>
        <span class="relative z-10">Установить</span>
      </a>
      <button
        class="w-full relative flex justify-center gap-2 px-4 items-center py-3.5 text-xs text-white text-base rounded-xl shadow-lg bg-[#3F3447] border border-gray-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden"
        onclick="Start()">
        <i class="fa fas fas fa-chevron-right text-lg" aria-hidden="true"></i>
        <span class="relative z-10">Далее</span>
      </button>
    </div>
  </div>

  <!-- content #3 -->
  <div class="hidden_content container overflow-hidden transition" id="content_three">
    <div class="mb-4">
      <h2 class="text-white text-center">Установите соединение</h2>
      <p class="text-gray-300 text-xs text-center leading-[1.5]">Осталось только привязать <span
          class="text-[#615ced] bg-[#efeeff] p-[2px] px-[5px] rounded-md">VLESS</span> ключ в <span
          class="text-[#615ced] bg-[#efeeff] p-[2px] px-[5px] rounded-md">v2Ray</span> и пользоваться
        VPN</p>
    </div>
    <div class="flex justify-center items-stratch gap-2">
      <?php
      /*
      vpn_freekey стадии:
      - no_used   : Новый клиент. Бесплатная подписка еще не активирована ("Подключить бесплатно!")
      - used      : Бесплатная подписка активирована и удалена/завершена ("Купить подписку")
      - buy       : Купил подписку, но не подключено в v2Ray ("Подключить подписку")
      - used_free : Активна бесплатная подписка, но не подключено в v2Ray ("Подключить подписку")
      */

      if ($client['vpn_freekey'] === 'no_used') {//новый клиент
        // Новый клиент - предложить бесплатное подключение
        echo '<button class="w-full transition-all relative flex gap-2 px-4 justify-center items-center text-xs text-white text-base rounded-xl bg-gradient-to-r from-purple-600 to-purple-400 shadow-lg hover:from-purple-700 hover:to-purple-500 hover:animate-none hover:from-white hover:text-black transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden animate-pulse" command="show-modal" commandfor="setting_email">
					<i class="fa fas fab fa-sellsy text-lg relative z-10"></i>
					<span class="relative z-10">Подключить бесплатно!</span>
				</button>';
      } elseif ($client['vpn_freekey'] === 'used') {// использован бесплатный
        // Бесплатная использована/отключена, только покупка
        echo '<a href="/pay" target="_self"
					class="w-full transition-all relative flex gap-2 px-4 justify-center items-center text-xs text-white text-base rounded-xl bg-gradient-to-r from-pink-500 to-purple-400 shadow-lg hover:from-purple-700 hover:to-purple-500 hover:animate-none hover:from-white hover:text-black transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden animate-pulse">
	<i class="fa fab fa-cc-visa text-lg relative z-10"></i>
					<span class="relative z-10">Купить подписку</span>
				</a>';
      } elseif ($client['vpn_freekey'] === 'buy' || $client['vpn_freekey'] === ($_ENV['VPN_FREE_CLIENT_FREEKEY'] ?: 'used_free')) {
        // Активна подписка (купленная или бесплатная, но нет подключения в v2Ray)
        echo '<a
					class="w-full transition-all relative flex gap-2 px-4 justify-center items-center text-xs text-white text-base rounded-xl bg-gradient-to-r from-purple-600 to-purple-400 shadow-lg hover:from-purple-700 hover:to-purple-500 hover:animate-none hover:from-white hover:text-black transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden animate-pulse"
					href="' . $add_v2ray_client . '" target="_blank">
					<i class="fa fab fab fa-cloudflare text-lg relative z-10"></i>
					<span class="relative z-10">Подключить подписку</span>
				</a>';
      } else {
        // Любой другой случай: показываем только покупку по-умолчанию
        echo '<a href="/pay" target="_self"
					class="w-full transition-all relative flex gap-2 px-4 justify-center items-center py-2.5 text-xs text-white text-base rounded-xl bg-gradient-to-r from-pink-500 to-purple-400 shadow-lg hover:from-purple-700 hover:to-purple-500 hover:animate-none hover:from-white hover:text-black transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden animate-pulse">
	<i class="fa fab fab fa-cc-visa text-lg relative z-10"></i>
					<span class="relative z-10">Купить подписку</span>
				</a>';
      }
      ?>
      <button
        class="w-full relative flex justify-center gap-2 px-4 items-center py-4 text-xs text-white text-base rounded-xl shadow-lg bg-[#3F3447] border border-gray-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden"
        onclick="Start()">
        <i class="fa fas fas fa-chevron-right text-lg" aria-hidden="true"></i>
        <span class="relative z-10">Далее</span>
      </button>
    </div>
  </div>

  <!-- content #4 FINISH -->
  <div class="hidden_content container overflow-hidden transition" id="content_for">
    <div class="mb-4">
      <h2 class="text-white text-center">Готово!</h2>
      <p class="text-gray-300 text-xs text-center leading-[1.5]">Поздравляем, вы успешно завершили
        установку
        VPN!
      </p>
    </div>
    <div class="flex justify-center items-start gap-2">
      <button
        class="w-full relative flex justify-center gap-2 px-4 items-center py-4 text-xs text-white text-base rounded-xl shadow-lg bg-gradient-to-r from-purple-700 to-purple-500 border border-gray-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-300 overflow-hidden"
        onclick="window.location.href = '/'">
        <i class="fa fas fas fa-shield-alt text-lg" aria-hidden="true"></i>
        <span class="relative z-10">Завершить настройку</span>
      </button>
    </div>
  </div>
</div>

<!-- Dialog Modal for free VPN email -->
<dialog id="setting_email" class="z-50 bg-black bg-opacity-60 w-full h-full">
  <div class="flex flex-col items-center justify-center w-full h-full">
    <button class="absolute top-4 right-4 text-gray-400 hover:text-white text-2xl" command="close"
      commandfor="setting_email" title="Закрыть">
      &times;
    </button>
    <form method="POST" class="w-full max-w-xs mx-auto" id="getFreeVpnForm" autocomplete="off" novalidate>
      <div class="relative bg-white rounded-2xl shadow-2xl p-8 flex flex-col gap-4">
        <h3 class="text-lg text-center font-semibold text-gray-800 mb-2">Введите email</h3>
        <input type="email" name="email" placeholder="noreply@coravpn.ru"
          class="border text-sm border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600"
          required />
        <button type="submit"
          class="transition-all relative flex gap-2 px-4 justify-center items-center py-4 text-xs text-white text-base rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 shadow-lg hover:from-purple-800 hover:to-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-300">
          <i class="fa fa-envelope-open text-lg relative z-10"></i>
          <span class="relative z-10">Получить бесплатный VPN</span>
        </button>
      </div>
    </form>
  </div>
</dialog>
