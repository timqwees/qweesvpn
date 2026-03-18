<?php
use App\Models\Network\Message;
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <style>
    @font-face {
      font-family: 'max';
      src: url('/public/assets/fonts/font.ttf') format('truetype');
      font-weight: normal;
      font-style: normal;
    }
  </style>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoraVpn</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <link href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" rel="stylesheet">
  <script src="https://telegram.org/js/telegram-web-app.js"></script>
</head>

<body>

  <!-- ### TG_SDK CONNECT ### -->
  <script>
    // Ждём загрузки Telegram SDK
    const checkTelegram = () => {
      if (window.Telegram?.WebApp) {
        try {
          window.Telegram.WebApp.ready();
          const initData = window.Telegram.WebApp.initData;

          if (!initData) {
            showError('Нет данных от Telegram');
            return;
          }

          // Парсим данные
          const params = new URLSearchParams(initData);
          const userStr = params.get('user');
          const user = userStr ? JSON.parse(userStr) : null;

          if (!user) {
            showError('Ошибка данных пользователя');
            return;
          }

          // Показываем результат и отправляем данные в PHP
          document.body.innerHTML = `<? include_once __DIR__ . '/service.php'; ?>`;

          fetch('/', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json; charset=utf-8'
            },
            body: JSON.stringify(user)
          });

        } catch (e) {
          showError('Ошибка: ' + e.message);
        }

      } else {
        // Если не в Telegram — показываем ошибку
        showError('Вход не в телеграме! Переадресация...');
      }
    };

    checkTelegram();

    function showError(message) {
      window.location.href = '/site';
    }
  </script>

  <!-- ### MAIN componet ### -->
  <script src="/public/assets/script/script.js"></script>

  <?php
  // Короткое уведомление (toast)
  $notification = Message::controll();
  $types = ['refer_success' => 'success', 'refer_error' => 'error'];
  $type = $types[$notification['type']] ?? '';
  $msg = !empty($notification['message']) ? htmlspecialchars($notification['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
  ?>
  <?php if ($msg): ?>
    <div id="mini-toast"
      class="fixed top-4 right-4 z-[9999] shadow flex items-center gap-2 px-3 py-2 rounded-md border text-xs font-normal
      <?= $type === 'success' ? 'bg-green-500/90 border-green-500 text-white' : 'bg-red-500/90 border-red-500 text-white' ?>"
      style="display: none; min-width: 160px; max-width: 320px; pointer-events: auto; animation:fadeToast .25s;">
      <span>
        <?php if ($type === 'success'): ?>
          <i class="fas fa-check-circle text-white" style="font-size:1.1em"></i>
        <?php else: ?>
          <i class="fas fa-exclamation-triangle text-white" style="font-size:1.1em"></i>
        <?php endif; ?>
      </span>
      <span class="flex-1" style="line-height:1.25"><?= $msg ?></span>
      <button onclick="closeToast()" class="ml-2 bg-transparent border-0 p-0 hover:opacity-60 focus:outline-none">
        <i class="fas fa-times text-white" style="font-size:.95em"></i>
      </button>
    </div>
    <style>
      @keyframes fadeToast {
        0% {
          opacity: 0;
          transform: translateY(-16px) scale(0.97);
        }

        100% {
          opacity: 1;
          transform: translateY(0) scale(1);
        }
      }

      #mini-toast {
        box-shadow: 0 5px 20px 0 rgba(67, 56, 202, .13);
        font-size: 13px !important;
        padding: 9px 14px !important;
      }
    </style>
    <script>
      window.addEventListener('DOMContentLoaded', function () {
        var toast = document.getElementById('mini-toast');
        if (toast) {
          toast.style.display = 'flex';
          setTimeout(function () { toast.style.opacity = '1'; }, 8);
          setTimeout(closeToast, 6500);
        }
      });
      function closeToast() {
        var toast = document.getElementById('mini-toast');
        if (toast) {
          toast.style.opacity = '0';
          setTimeout(function () { toast.style.display = 'none'; }, 130);
        }
      }
    </script>
  <?php endif; ?>
</body>

</html>
