<?php
use App\Config\Session;
use App\Models\Network\Message;

Session::init();
$notification = Message::controll();
$isReferSuccess = isset($notification['type']) && $notification['type'] === 'refer_success';
$isReferError = isset($notification['type']) && $notification['type'] === 'refer_error';
$msg = !empty($notification['message']) ? htmlspecialchars($notification['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';

// Проверяем, есть ли сохраненный реферальный код (для неизвестных клиентов)
$hasPendingRefer = isset($_COOKIE['pending_refer_code']) || isset($_SESSION['pending_refer_code']);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoraVPN — Активация реферальной программы</title>
  <link rel="icon" type="image/x-icon" href="/static/favicon.ico">
  <link rel="preconnect" href="https://cdn.tailwindcss.com">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print"
    onload="this.media='all'">
  <noscript>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  </noscript>
  <style>
    html,
    body {
      background: #f8fafc;
      color: #23272f;
    }

    ::-webkit-scrollbar {
      width: 0;
    }

    body {
      min-height: 100vh;
      font-family: 'Inter', system-ui, sans-serif;
      font-size: 18px;
      line-height: 1.74;
      letter-spacing: 0.01em;
      background: #f8fafc;
      color: #23272f;
    }

    .vpn-shadow {
      box-shadow: 0 4px 24px 0 #daeaff90;
    }

    .white-box-border {
      border: 1.5px solid #e5e7eb !important;
      background: #fff !important;
    }

    .text-muted {
      color: #7b7b7b !important;
    }

    .main-bg {
      background: linear-gradient(120deg, #f4f7fb 0%, #eaf1fa 100%);
    }

    .bg-gradient-blue {
      background: linear-gradient(90deg, #eef6ff 60%, #ddeaff 100%);
    }

    .section-card {
      background: #fff;
      border-radius: 1.35rem;
      box-shadow: 0 8px 48px 0 #badcff35;
      padding: 3rem 2.5rem 2.5rem 2.5rem;
      max-width: 620px;
      width: 100%;
      margin: 92px auto 60px auto;
      border: 1.5px solid #e5e7eb;
      transition: box-shadow 0.22s, border-color .18s;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .section-card .icon {
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 4rem;
      width: 98px;
      height: 98px;
      margin: 0 auto 2.2rem auto;
      border-radius: 50%;
      background: linear-gradient(176deg, #f2f7ff 62%, #eef4fd 100%);
      box-shadow: 0 7px 30px 0 #bae0ff27;
      border: 2px solid #d6e6fc;
    }

    .btn-main {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 1.1rem;
      padding: 1.18rem 0;
      font-size: 19px;
      background: #191b1f !important;
      color: #fff !important;
      font-weight: 700;
      border: none;
      border-radius: 0.9rem;
      box-shadow: 0 5px 20px 0 #191b1f1a;
      transition: background 170ms cubic-bezier(.4, 0, .2, 1), box-shadow 140ms, transform 120ms, letter-spacing .13s;
      margin-top: 2rem;
      width: 100%;
      letter-spacing: 0.01em;
      min-width: 190px;
      border: 1.5px solid #24292e;
      text-shadow: 0px 2px 10px #0ccbe221;
      text-align: center;
    }

    .btn-main:hover,
    .btn-main:focus {
      background: #101214 !important;
      box-shadow: 0 9px 32px 0 #191b1f22;
      color: #fff !important;
      transform: translateY(-2px) scale(1.03);
      text-decoration: none;
      letter-spacing: 0.045em;
    }

    .section-card h1 {
      font-size: 2.35rem;
      font-weight: 900;
      text-align: center;
      color: #222b36;
      letter-spacing: -0.027em;
      text-shadow: 0px 4px 12px #0D2D5505;
    }

    .section-card .statusdesc {
      font-size: 1.21rem;
      color: #212932;
      background: none !important;
      border-radius: 0.8rem;
      text-align: center;
      font-weight: 600;
      line-height: 1.63;
      width: 100%;
      padding: 1.12rem 7px 1.12rem 7px;
      box-shadow: none !important;
      border: none !important;
      max-width: 495px;
      letter-spacing: 0.005em;
    }

    .section-card ul {
      padding: 1.05rem 0.7rem 0.7rem 1.5rem;
      border-radius: 0.6rem;
      background: none !important;
      border: none !important;
      font-size: 1.11rem;
      color: #212933;
      max-width: 470px;
      width: 100%;
      line-height: 1.7;
      font-weight: 500;
      box-shadow: none !important;
      display: flex;
      flex-direction: column;
      gap: 0;

    }

    .section-card ul li {
      margin-bottom: 0.55rem;
      display: flex;
      align-items: center;
      line-height: 1.6;
      font-size: 1.09em;
      padding-left: 0.2em;
      padding-right: 0.2em;
      min-height: 2.1em;
    }

    .section-card ul li:last-child {
      margin-bottom: 0 !important;
    }

    .section-card ul li i {
      margin-top: 1px;
      min-width: 24px;
      text-align: left;
    }

    .info-block-extended {
      width: 100%;
      max-width: 470px;
      padding: 1.05rem 1.3rem 1.05rem 1.3rem;
      background: none !important;
      border: none !important;
      color: #364663;
      font-size: 1.09rem;
      text-align: center;
      font-weight: 500;
      box-shadow: none !important;
      letter-spacing: .009em;
      display: block;
    }

    .info-block-extended[style] {
      color: #ce3a28 !important;
    }

    .tiny-note {
      margin-top: 2.39rem;
      text-align: center;
      font-size: 13.6px;
      color: #4a5c6e;
      opacity: .92;
      font-weight: 400;
      letter-spacing: 0.01em;
      background: none;
      border-radius: 0.6rem;
    }

    .mobile-menu-button {
      outline: none;
    }

    @media (max-width: 900px) {
      .section-card {
        max-width: 97vw;
        padding: 1.2rem 0.5rem;
      }

      .section-card ul,
      .info-block-extended,
      .section-card .statusdesc {
        max-width: 97vw;
      }
    }

    .footer-section {
      background: linear-gradient(90deg, #fff, #f0f7ff 80%);
      padding-top: 40px;
      padding-bottom: 28px;
      box-shadow: 0 0 32px #e8edfa38;
      border-top: 1.5px solid #e1e5ea;
      color: #3576b3;
      font-size: 14px;
    }

    .footer-section .brand {
      font-weight: 800;
      color: #2182e0
    }

    .footer-section .slogan span {
      background: #e8f3fe;
      color: #1387ec;
      font-size: 13.7px;
      font-weight: bold;
      border-radius: 9px;
      padding: 1px 5px 1px 4px;
      margin: 0 2px;
    }

    /* Animated toast styles */
    #mini-toast {
      display: none;
      opacity: 0;
      min-width: 260px;
      max-width: 555px;
      pointer-events: auto;
      background: #212933;
      color: #fff;
      box-shadow: 0 18px 60px 0 #192c4427;
      font-size: 16px !important;
      padding: 17px 34px !important;
      border-width: 2px !important;
      border-color: #b2d1f6 !important;
      transition:
        opacity .36s cubic-bezier(.35, 1.2, .53, 1),
        transform .34s cubic-bezier(.51, 1.45, .36, .99);
      border-radius: 17px;
      transform: translateY(-28px) scale(.93);
      z-index: 8889;
      align-items: center;
      font-weight: 600;
      letter-spacing: 0.01em;
      margin-right: 0.5rem;
      backdrop-filter: blur(2px);
    }

    #mini-toast.showing {
      opacity: 1 !important;
      display: flex !important;
      transform: translateY(0) scale(1);
      animation: toastAppear 0.49s cubic-bezier(.43, 1.7, .36, .99);
    }

    #mini-toast.hiding {
      opacity: 0 !important;
      transform: translateY(-22px) scale(.985);
      animation: toastHide 190ms cubic-bezier(.65, .01, .99, 1);
    }

    @keyframes toastAppear {
      0% {
        opacity: 0;
        transform: translateY(-36px) scale(0.95);
      }

      60% {
        opacity: 1;
        transform: translateY(9px) scale(1.035);
      }

      100% {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    @keyframes toastHide {
      from {
        opacity: 1;
        transform: translateY(0) scale(1);
      }

      to {
        opacity: 0;
        transform: translateY(-22px) scale(.985);
      }
    }

    .toast-icon {
      font-size: 23px;
      min-width: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 2px;
    }

    .toast-close-btn {
      margin-left: 1.1rem;
      background: none;
      border: 0;
      color: #e2e2e6;
      padding: 3px 11px;
      cursor: pointer;
      border-radius: 0.48rem;
      font-size: 1.26rem;
      opacity: .77;
      transition: background 90ms, opacity .13s, color .13s, transform 120ms;
    }

    .toast-close-btn:hover,
    .toast-close-btn:focus {
      color: #fff;
      background: #23262b13;
      opacity: 1;
      transform: scale(1.18) rotate(13deg);
    }
  </style>
</head>

<body class="main-bg body-blackwhite">

  <?php if ($msg): ?>
    <div id="mini-toast" class="fixed top-7 right-8 z-[9999] flex items-center gap-3 px-7 py-3 rounded-xl border font-medium shadow
      <?=
        $isReferSuccess
        ? 'border-blue-300'
        : ($isReferError ? 'border-rose-400' : 'border-gray-400')
        ?>
      bg-[#212933] text-white">
      <span class="toast-icon mr-2 flex-shrink-0">
        <?php if ($isReferSuccess): ?>
          <i class="fas fa-circle-check" style="color:#14c86c;"></i>
        <?php elseif ($isReferError): ?>
          <i class="fas fa-triangle-exclamation" style="color:#f15454;"></i>
        <?php else: ?>
          <i class="fas fa-info-circle" style="color:#3c98ff;"></i>
        <?php endif; ?>
      </span>
      <span class="flex-1" style="line-height:1.36; word-break:break-word;">
        <?= $msg ?>
      </span>
      <button onclick="closeToast()" class="toast-close-btn" aria-label="Закрыть">
        <i class="fas fa-times text-lg"></i>
      </button>
    </div>
    <script>
      function showToastAnimated() {
        var toast = document.getElementById('mini-toast');
        if (toast) {
          toast.style.display = 'flex';
          void toast.offsetWidth;
          toast.classList.add('showing');
          setTimeout(function () {
            toast.classList.remove('showing');
          }, 15770);
          setTimeout(function () { closeToast(); }, 17750);
        }
      }
      function closeToast() {
        var toast = document.getElementById('mini-toast');
        if (toast && toast.style.display !== 'none') {
          toast.classList.remove('showing');
          toast.classList.add('hiding');
          setTimeout(function () {
            toast.style.display = 'none';
            toast.classList.remove('hiding');
          }, 230);
        }
      }
      window.addEventListener('DOMContentLoaded', showToastAnimated);
    </script>
  <?php endif; ?>

  <nav class="bg-white/95 border-b border-gray-200 fixed w-full z-50 vpn-shadow backdrop-blur-md main-nav"
    style="min-height:58px;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <a href="/" class="flex-shrink-0 flex items-center">
            <img src="/public/assets/logo/logo.png" alt="CoraVPN" class="h-8 w-auto mr-2">
            <span class="text-2xl font-bold tracking-tight text-blue-900">CoraVPN</span>
          </a>
        </div>
        <div class="hidden md:block">
          <div class="ml-4 flex items-center md:ml-6">
            <a href="https://t.me/coravpn_bot/CoraVPN"
              class="ml-3 bg-black shadow-lg hover:scale-105 text-white px-6 py-2 rounded-lg font-semibold focus:ring-2 ring-offset-1 ring-blue-400 transition"
              style="background: #191b1f !important; color: #fff !important; border-color: #191b1f !important;"
              target="_blank">
              <i class="fa-brands fa-telegram"></i>
              <span class="ml-2">В приложение</span>
            </a>
          </div>
        </div>
        <div class="-mr-2 flex md:hidden">
          <button type="button"
            class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-blue-600 hover:text-blue-800 focus:outline-none">
            <i class="fas fa-bars w-6 h-6"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="mobile-menu hidden md:hidden bg-white z-[999]">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <a href="/" class="text-blue-900 hover:text-blue-700 block px-3 py-2 text-base font-semibold">Главная</a>
        <a href="https://t.me/coravpn_bot/CoraVPN"
          class="text-blue-900 hover:text-blue-700 block px-3 py-2 text-base font-semibold"
          target="_blank">Приложение</a>
        <a href="https://t.me/CoraVPNBot"
          class="text-blue-900 hover:text-blue-700 block px-3 py-2 text-base font-semibold" target="_blank">Бот</a>
        <a href="https://t.me/spcoravpn_bot"
          class="text-blue-900 hover:text-blue-700 block px-3 py-2 text-base font-semibold"
          target="_blank">Поддержка</a>
      </div>
    </div>
  </nav>

  <main class="flex flex-col min-h-screen justify-center items-center px-4 py-10 relative z-10"
    style="background: none;">
    <div class="section-card white-box-border">
      <div class="icon mb-4 shadow-lg">
        <?php if ($isReferSuccess): ?>
          <i class="fa-solid fa-circle-check" style="color:#14c86c;"></i>
        <?php elseif ($isReferError): ?>
          <i class="fa-solid fa-circle-xmark" style="color:#f15454;"></i>
        <?php else: ?>
          <i class="fa-solid fa-circle-question" style="color:#ffc84a;"></i>
        <?php endif; ?>
      </div>
      <h1>
        <?php if ($isReferSuccess): ?>
          Успешная активация <span style="font-size:1.15em;">🎉</span>
        <?php elseif ($isReferError): ?>
          Ошибка активации
        <?php elseif ($hasPendingRefer): ?>
          Реферальная ссылка сохранена <span style="font-size:1.15em;">📌</span>
        <?php else: ?>
          Неопределённый статус
        <?php endif; ?>
      </h1>

      <?php if ($isReferSuccess): ?>
        <div class="statusdesc">
          Ваша реферальная программа успешно активирована. <br>
          <span style="display:block; margin-top:6px;"><b>Добро пожаловать в команду CoraVPN 🚀</b></span>
        </div>
        <div class="info-block-extended">
          <i class="fa-solid fa-info-circle text-blue-500 mr-2" style="vertical-align:middle"></i>
          <span>Ожидайте уведомлений в личном кабинете и в Telegram,<br>
            как только реферальные возможности станут доступны.</span>
        </div>
      <?php elseif ($hasPendingRefer): ?>
        <div class="statusdesc">
          Реферальная ссылка сохранена! <br>
          <span style="display:block; margin-top:6px;"><b>Войдите в приложение, чтобы активировать её 🎁</b></span>
        </div>
        <div class="info-block-extended">
          <i class="fa-solid fa-info-circle text-blue-500 mr-2" style="vertical-align:middle"></i>
          <span>Мы сохранили вашу реферальную ссылку. <br>
            После входа в приложение через Telegram она будет автоматически активирована,<br>
            и вы получите <b>скидку 20%</b> на все тарифы!</span>
        </div>
      <?php elseif ($isReferError): ?>
        <div class="info-block-extended" style="color:#ce3a28;">
          <i class="fa-solid fa-triangle-exclamation text-rose-400 mr-2" style="vertical-align:middle"></i>
          <?= $msg ?>
        </div>
      <?php else: ?>
        <div class="statusdesc">
          Не удалось определить ситуацию.<br>
          Попробуйте позже или обратитесь в поддержку.
        </div>
        <div class="info-block-extended">
          <i class="fa-solid fa-circle-question text-yellow-400 mr-2" style="vertical-align:middle"></i>
          Если видите это сообщение случайно — уточните причину в поддержке.
        </div>
      <?php endif; ?>

      <ul>
        <?php if ($hasPendingRefer): ?>
          <li>
            <i class="fas fa-link text-muted mr-2"></i>
            <span>Реферальная ссылка сохранена и будет активирована автоматически.</span>
          </li>
          <li>
            <i class="fas fa-telegram text-muted mr-2"></i>
            <span>Войдите в приложение через <b>Telegram</b> для активации.</span>
          </li>
          <li>
            <i class="fas fa-gift text-muted mr-2"></i>
            <span>После входа вы получите <b>скидку 20%</b> на все тарифы!</span>
          </li>
        <?php else: ?>
          <li>
            <i class="fas fa-bell text-muted mr-2"></i>
            <span>Ожидайте информации о результате активации.</span>
          </li>
          <li>
            <i class="fas fa-user-shield text-muted mr-2"></i>
            <span>Следите за статусом <b>в личном кабинете</b>.</span>
          </li>
          <li>
            <i class="fas fa-headset text-muted mr-2"></i>
            <span>Остались вопросы? Пишите нам в поддержку!</span>
          </li>
        <?php endif; ?>
      </ul>

      <a href="https://t.me/coravpn_bot/CoraVPN" class="btn-main">
        <i class="fa-solid fa-user-astronaut"></i>
        <span><?= $hasPendingRefer ? 'Войти в приложение' : 'Вернуться в профиль' ?></span>
      </a>

      <div class="tiny-note">
        <?php if ($hasPendingRefer): ?>
          <span>Реферальная ссылка будет активирована автоматически при первом входе через Telegram.</span>
        <?php else: ?>
          <span>Результат будет отображён в вашем кабинете и/или Telegram.</span>
        <?php endif; ?>
        <hr style="border:none; border-top:1px dashed #c5d8fd; width:50%; margin: 12px auto 12px auto; opacity:.25;">
        <span style="font-size:0.98em;">
          Поддержка:
          <a href="https://t.me/spcoravpn_bot" target="_blank"
            style="text-decoration:underline;color:#1e6de0;">@spcoravpn_bot</a>
        </span>
        <br>
        <span class="brand">CoraVPN</span>
      </div>
    </div>
  </main>

  <footer class="footer-section">
    <div class="max-w-7xl mx-auto flex flex-col items-center px-4">
      <div class="flex items-center gap-3 mb-4">
        <img src="/public/assets/logo/logo.png" alt="Логотип CoraVPN" class="h-9 w-auto drop-shadow-2xl">
        <span class="text-2xl font-extrabold drop-shadow-lg tracking-wide brand">CoraVPN</span>
      </div>
      <p class="text-muted text-center mb-2 max-w-2xl font-medium" style="line-height:1.67;">
        Все услуги доступны только через
        <a href="https://t.me/coravpn_bot/CoraVPN" class="underline text-blue-800 font-semibold transition-colors"
          target="_self"><i class="fa-brands fa-telegram"></i> Личный кабинет</a>
        и <a href="https://t.me/CoraVPNBot" class="underline text-blue-800 font-semibold" target="_blank">бота</a>
        в Telegram.<br>
        <span class="slogan">
          CoraVPN работает по принципам:
          <span>безопасность</span>
          <span>конфиденциальность</span>
          <span>официальность</span>
        </span>
      </p>
      <div class="border-t border-blue-100 mt-4 pt-4 text-center text-blue-600 w-full flex flex-col items-center gap-1">
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
    document.querySelectorAll('.mobile-menu-button').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelector('.mobile-menu').classList.toggle('hidden');
      });
    });
  </script>
</body>

</html>
