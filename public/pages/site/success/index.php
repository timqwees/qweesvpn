<?php
use App\Models\Network\Message;
use Setting\Route\Function\Functions;
use App\Config\Session;

// Инициализация сессии
Session::init();
$client = (new Functions())->client($_SESSION['client'] ?? 0);
$add_v2ray_client = "v2raytun://import/{$client['vpn_subscription']}";
$verefy_free_client = $client['vpn_freekey'] === $_ENV['VPN_FREE_CLIENT_FREEKEY']; //return true || false
// Проверяем тип оплаты из сессии с безопасной проверкой существования ключа
$verefy_card = (isset($_SESSION['selection_pay_type']) && $_SESSION['selection_pay_type'] == 'connect_card');
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoraVPN — Оплата успешна</title>
  <link rel="icon" type="image/x-icon" href="/static/favicon.ico">
  <link rel="stylesheet" href="/public/pages/site/error/style.css">
  <link rel="preconnect" href="https://cdn.tailwindcss.com">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com">
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print"
    onload="this.media='all'">
  <noscript>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  </noscript>
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js" defer></script>
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

    .vpn-field-group {
      width: 100%;
      max-width: 490px;
      margin: 18px auto 0 auto;
      display: flex;
      flex-direction: column;
      gap: 10px;
      align-items: stretch;
    }

    .vpn-field-label {
      font-size: 1rem;
      font-weight: 500;
      color: #5a5a5a;
      margin-bottom: 3px;
    }

    .vpn-field-row {
      display: flex;
      gap: 6px;
    }

    .vpn-input {
      width: 100%;
      font-size: 0.94em;
      padding: 7px 12px;
      border: 1px solid #d3d4db;
      border-radius: 8px;
      outline: none;
      color: #20223c;
      background: #ffffff;
    }

    .vpn-btn {
      font-size: 1em;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      transition: background .15s, color .15s;
      background: linear-gradient(to right, #7B46FF, #DD51ED 92%);
      color: #fff;
      box-shadow: 0 1px 4px -1px #7b46ff1a;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      font-weight: 600;
    }

    .vpn-btn-teleg {
      background: black;
      border: none;
      transition: .5s ease all;
    }

    .vpn-btn-teleg:hover {
      background-color: white;
      color: black;
    }

    @media (max-width: 600px) {
      .vpn-field-group {
        max-width: 99vw;
      }

      .vpn-btn,
      .vpn-btn-teleg {
        font-size: .98em;
        padding: 7px 8px;
      }

      .vpn-input {
        font-size: .95em;
        padding: 8px 7px;
      }

      .vpn-field-label {
        font-size: .93em;
      }
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
  <main class="flex flex-col min-h-screen justify-center items-center px-4 py-10 relative z-10">
    <div class="bg-white/90 rounded-lg shadow-lg max-w-lg w-full flex flex-col items-center py-12 px-6 mt-32 mb-12">
      <div class="bg-green-100 rounded-full p-5 mb-6">
        <i class="fas fa-check-circle text-green-500 text-5xl"></i>
      </div>
      <h1 class="text-2xl md:text-4xl font-semibold text-gray-900 mb-4 text-center">
        <?php if ($verefy_free_client) {
          echo 'Бесплатная подписка успешно выдана!';
        } elseif ($verefy_card) {
          Message::set('success', 'Счёт успешно привязан к вашему профилю! Теперь вы можете управлять автопродлением и использовать все функции сервиса.');
          echo 'Счет привязан успешно!';
        } else {
          echo 'Оплата прошла успешно!';
        }
        ?>
      </h1>
      <p class="text-gray-600 text-lg mb-4 text-center">
        <?php if (!$verefy_card): ?>
          Ваш аккаунт был успешно обновлён, и вы получили доступ к VPN.
        <?php else: ?>
          Ваш аккаунт был успешно обновлён, ваш счёт уже привязан к вашему профилю!
        <?php endif; ?>
      </p>
      <ul class="text-gray-500 text-base mb-8 text-left max-w-md">
        <li class="mb-2 flex items-center"><i class="fas fa-check text-green-400 mr-2"></i>
          <?php if ($verefy_free_client) {
            echo 'VPN успешно установлена на ваш профиль!';
          } elseif ($verefy_card) {
            echo 'Счёт успешно привязан к вашему профилю!';
          } else {
            echo 'Оплата успешно прошла нашим сервисом!';
          }
          ?>
        </li>
        <li class="mb-2 flex items-center"><i class="fas fa-headset text-green-400 mr-2"></i>Если доступ не
          появился сразу — подождите пару минут или обратитесь в поддержку.</li>
      </ul>
      <!-- Subscription (input+copy+install) START -->
      <?php if (!$verefy_card): ?>
        <div class="vpn-field-group mt-3">
          <div class="vpn-field-label">VPN-Подписка</div>
          <div class="vpn-field-row">
            <input type="text" id="sub-link" class="vpn-input text-black"
              value="<?= htmlspecialchars($client['vpn_subscription'] ?? '', ENT_QUOTES) ?>" readonly tabindex="0">
            <button id="copy-sub-btn"
              class="w-12 bg-black text-white rounded-lg transition hover:opacity-80 hover:scale-105" onclick="copySub();"
              title="Скопировать" aria-label="Скопировать ключ"><i class="fa fa-copy"></i></button>
          </div>
        </div>
      <? endif; ?>
      <!-- Subscription END -->
      <div class="flex flex-col sm:flex-row gap-4 w-full justify-center mt-10">
        <?php if (!$verefy_card): ?>
          <button
            class="border px-6 py-3 rounded-lg font-medium flex items-center justify-center gap-2 bg-black text-white transition-all duration-300 ease-in-out hover:opacity-80 hover:scale-105 animate-pulse"
            onclick="window.open('<?= $add_v2ray_client ?>', '_blank');" title="Установить подписку"><i
              class="fa fa-plug rotate-45"></i> Установить подписку</button>
        <? endif; ?>
        <a href="https://t.me/coravpn_bot/CoraVPN"
          class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-8 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
          <i class="fab fa-telegram-plane"></i> В приложение
        </a>
      </div>
      <div class="text-xs text-gray-400 mt-6 text-center">
        Если возникнут вопросы — напишите в поддержку, и мы обязательно поможем!<br>
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
    // particles.js init - отложенная инициализация после загрузки страницы
    window.addEventListener('load', () => {
      // Задержка для первоначальной отрисовки страницы
      setTimeout(() => {
        if (window.particlesJS) {
          particlesJS("particles-js", {
            "particles": {
              "number": {
                "value": 50, // Уменьшено с 100 для лучшей производительности
                "density": { "enable": true, "value_area": 1200 }
              },
              "color": { "value": "#179E44" },
              "shape": {
                "type": "circle",
                "stroke": { "width": 0, "color": "#000" }
              },
              "opacity": { "value": 0.22, "random": true },
              "size": { "value": 4, "random": true }, // Уменьшено с 5
              "line_linked": {
                "enable": true,
                "distance": 120, // Уменьшено с 150
                "color": "#4D9048",
                "opacity": 0.18, // Уменьшено с 0.22
                "width": 1.5 // Уменьшено с 2
              },
              "move": {
                "enable": true,
                "speed": 0.8, // Уменьшено с 1.1 для экономии ресурсов
                "direction": "none",
                "random": true,
                "straight": false,
                "out_mode": "out",
                "bounce": false
              }
            },
            "retina_detect": false // Отключено для ускорения
          });
        }
      }, 100); // Задержка 100мс для первоначальной отрисовки
    });

    function copySub() {
      const input = document.getElementById('sub-link');
      const link = input ? input.value : '';
      if (!link) return;
      navigator.clipboard.writeText(link);
      const btn = document.getElementById('copy-sub-btn');
      btn.innerHTML = '<i class="fa fa-check text-green-500"></i>';
      showCopiedToast();
      setTimeout(() => {
        btn.innerHTML = '<i class="fa fa-copy"></i>';
      }, 2030);
    }

    function copyKey() {
      const input = document.getElementById('key-link');
      const link = input ? input.value : '';
      if (!link) return;
      navigator.clipboard.writeText(link);
      const btn = document.getElementById('copy-key-btn');
      btn.innerHTML = '<i class="fa fa-check text-green-500"></i>';
      showCopiedToast();
      setTimeout(() => {
        btn.innerHTML = '<i class="fa fa-copy"></i>';
      }, 2030);
    }

    function showCopiedToast() {
      let toast = document.createElement('div');
      toast.textContent = 'Скопировано успешно!';
      toast.style.position = 'fixed';
      toast.style.bottom = '18px';
      toast.style.left = '50%';
      toast.style.transform = 'translateX(-50%)';
      toast.style.background = 'linear-gradient(100deg,#84ffb7,#6a54eb)';
      toast.style.color = '#10171f';
      toast.style.width = 'max-content';
      toast.style.padding = '6px 22px';
      toast.style.borderRadius = '15px';
      toast.style.boxShadow = '0 2px 10px 1.5px #7b46ff22';
      toast.style.fontWeight = '500';
      toast.style.fontSize = '.6em';
      toast.style.opacity = '0';
      toast.style.zIndex = '998';
      toast.style.transition = "opacity 0.2s";
      document.body.appendChild(toast);

      setTimeout(() => { toast.style.opacity = 1; }, 20);
      setTimeout(() => { toast.style.opacity = 0; }, 1800);
      setTimeout(() => {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
      }, 2030);
    }
  </script>
</body>

</html>
