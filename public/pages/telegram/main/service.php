<?php
use Setting\Route\Function\Functions;
use App\Config\Session;

include_once dirname(__DIR__, 3) . '/assets/script/script.php';
// Инициализация сессии
Session::init();
$client = (new Functions())->client($_SESSION['client'] ?? 0);
$price = (new Functions())->isPrice();

$INSTALL_OS = getV2RayTunInstallUrl();
// Улучшенная проверка и оформление кнопки "Попробовать бесплатно" или выбора ОС
$isFreeKeyAvailable = isset($client['vpn_freekey']) && $client['vpn_freekey'] === 'no_used';
$osName = isset($INSTALL_OS['os']) && $INSTALL_OS['os'] ? htmlspecialchars($INSTALL_OS['os']) : 'Скачать';

$verefy_start_cient = $isFreeKeyAvailable
  ? '<span class="bg-gradient-to-r from-[#8A2BE2]/80 to-[#00D2FF]/90 text-white px-3 py-1 rounded-lg font-extrabold shadow border text-sm border-none transition duration-200 hover:from-[#9A4DFF] hover:to-[#36EDFF] hover:shadow-lg cursor-pointer">
		Бесплатно
	  </span>'
  : '<span class="bg-gradient-to-r from-[#6CE2FF] to-[#6440e0] text-white px-4 py-1.5 rounded-lg font-bold shadow-lg border-0 text-sm transition duration-300 hover:from-[#89f0ff] hover:to-[#9786ff] hover:scale-105 cursor-pointer flex items-center gap-2">
		<i class="fa fa-download text-white text-base"></i>'
  . $osName .
  '</span>';
?>

<style>
  .pix {
    image-rendering: optimizeSpeed;
  }

  ::-webkit-scrollbar {
    width: 0;
  }

  body {
    font-family: 'max', Arial, Helvetica, sans-serif;
    min-height: 100vh;
    margin: 0;
  }

  @font-face {
    font-family: 'max';
    src: url('/public/assets/fonts/font.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
  }

  .enhanced-shadow {
    box-shadow: 0 4px 24px 0 rgba(99, 86, 255, 0.13), 0 1.5px 6px 0 rgba(80, 50, 170, .1);
  }

  .glow-btn:hover,
  .glow-btn:focus {
    box-shadow: 0 0 10px 2px #a492ff99, 0 0 2px #fff inset;
  }

  .divider {
    background: linear-gradient(to right, #6440e0, #a492ff, #2d2547);
    height: 2px;
    border-radius: 2px;
    margin: 20px 0;
    opacity: 0.12;
  }
</style>

<div
  class="relative flex flex-col items-center justify-center min-h-screen overflow-hidden font-sans enhanced-shadow bg-[#040B20]">
  <?php include dirname(__DIR__, 3) . '/assets/componets/spiner.php'; ?>

  <!-- Основной блок с улучшенным дизайном -->
  <div class="flex flex-col items-center justify-center my-4">
    <img width="280" src="/public/assets/video/1.gif" alt="CoraVPN"
      class="pix rounded-full border-4 border-[#a492ff44] shadow-xl" style="box-shadow: 0 0 0 4px #a492ff22;" />
  </div>

  <!-- Статус и действия с улучшенным дизайном -->
  <div
    class="bg-gradient-to-br from-[#1e1e2f] via-[#23213a] to-[#2d2547] rounded-2xl enhanced-shadow p-4 h-[49vh] border border-[#2d2547] backdrop-blur-lg w-full max-w-[90%] h-full overflow-hidden relative">

    <div class="flex justify-between items-center py-3 relative h-[4.2rem]">
      <div class="h-full">
        <div class="flex items-center gap-3">
          <div
            class="w-14 h-14 rounded-xl shadow-md border-2 border-purple-400/60 bg-[#23213a] flex items-center justify-center p-2 ring-2 ring-[#a492ff33]">
            <img src="/public/assets/logo/logo.png" alt="CoraVPN" class="pix rounded-lg w-full"
              style="transform: translateY(1px);" />
          </div>
          <div class="relative">
            <h1 class="text-white text-lg tracking-wide drop-shadow-sm flex items-center font-bold mb-1">
              CoraVPN
            </h1>
            <div
              class="<?php echo $client['vpn_status'] === 'online' ? 'text-green-400 animate-pulse' : 'text-red-400'; ?>  flex gap-1 items-center text-center transition-colors duration-300">
              <i class="fa fas fa-globe text-sm -mb-0.5"></i>
              <p class="text-sm tracking-wider translate-y-[1.5px] uppercase whitespace-nowrap">
                <?php echo $client['vpn_status'] ?: "offline"; ?>
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="flex flex-col items-end h-full">
        <span
          class="text-slate-200 text-[11px] font-semibold bg-[#23213a]/70 px-3 py-1 rounded-lg shadow border border-[#23213a]/40 mb-2">
          <?php echo $client['vpn_expiryDate'] ?: "Нет подписки"; ?>
        </span>
        <div class="flex items-center justify-center px-2 py-1 rounded-lg border transition-all duration-300 shadow
            <?php echo $client['vpn_status'] === 'online'
              ? 'bg-gradient-to-r from-green-900/70 to-green-800/30 animate-pulse border-green-200/60'
              : 'bg-gradient-to-r from-red-900/70 to-red-800/30 animate-pulse border-red-300/40'; ?>">
          <span class="text-xs flex items-center justify-center gap-1
                    <?php echo $client['vpn_status'] === 'online'
                      ? 'text-green-300 animate-pulse'
                      : 'text-red-300'; ?>">
            <i class="fa far fa-clock text-xs"></i>
            <span class="font-semibold tracking-wide text-[11px] drop-shadow-sm">
              <?php echo $client['vpn_expiryTime'] ?: "Ожидание..."; ?>
            </span>
          </span>
        </div>
      </div>
    </div>

    <div class="divider"></div>

    <div class="flex flex-col gap-4 text-base">
      <a class="w-full glow-btn transition-all flex gap-3 px-4 py-3 justify-between items-center text-black text-base rounded-2xl bg-gradient-to-r from-white via-purple-600 to-purple-600 shadow-xl hover:from-purple-800 hover:to-purple-400 hover:text-white duration-200 focus:outline-none focus:ring-2 focus:ring-purple-400/50 overflow-hidden font-extrabold tracking-wider border-purple-500/30 active:scale-95"
        href="/pay" target="_self">
        <span class="flex items-center gap-2">
          <i class="fa fas fa-shopping-cart text-xl drop-shadow"></i>
          Купить
        </span>
        <span
          class="bg-[#23213a] text-[#6cc2ff] px-3 py-1 rounded-lg font-bold shadow border border-[#6cc2ff]/30 flex gap-2">
          от <?= $price['basic'] ?> ₽
        </span>
      </a>

      <a class="w-full glow-btn flex items-center justify-between gap-3 px-4 py-4 text-base font-bold rounded-2xl bg-gradient-to-r from-[#23213a] to-[#614C98] text-white border border-[#A492FF] hover:border-white transition shadow-lg active:scale-95"
        href="/download" title="Установить и настроить VPN" target="_self">
        <span class="flex items-center gap-4">
          <i class="fa fas fa-plug rotate-45 text-lg text-[#A492FF] drop-shadow"></i>
          <span class="tracking-wider">Установка</span>
        </span>
        <?php if (!empty($verefy_start_cient)): ?>
          <?= $verefy_start_cient; ?>
        <?php endif; ?>
      </a>

      <div class="flex gap-3">
        <a href="https://t.me/spcoravpn_bot"
          class="flex-1 glow-btn flex items-center justify-center gap-2 px-4 py-4 text-[14px] font-bold rounded-2xl text-white border border-[#A492FF] hover:bg-[#2d2547] hover:border-white transition shadow bg-gradient-to-r from-[#23213a] to-[#614C98] active:scale-95">
          <i class="fa-brands far fas fa-hands-helping text-white text-base"></i>
          Поддержка
        </a>
        <a href="/profile" target="_self"
          class="flex-1 glow-btn flex items-center justify-center gap-2 px-4 py-2.5 text-[14px] font-bold rounded-2xl text-[#A492FF] border border-[#A492FF] hover:bg-[#2d2547] hover:border-white transition shadow bg-gradient-to-r from-[#23213a] to-[#614C98] active:scale-95">
          <i class="fa-brands far fa-user text-white text-base bg-[#A492FF] px-2 py-1 rounded-lg"></i>
          Профиль
        </a>
      </div>

    </div>
  </div>
</div>
