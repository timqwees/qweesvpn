<?php
use App\Config\Database;
use Setting\Route\Function\Functions;
use App\Models\Network\Message;
use App\Config\Session;

// Инициализация сессии
Session::init();
$client = (new Functions())->client($_SESSION['client'] ?? 0);

// Получаем автоматическую карту ЮKassa только через функцию Functions::getYooKassaAutopayData
$autoPayData = Functions::getYooKassaAutopayData($client['tg_id']);

$hasCard = !empty($autoPayData['card_token']);
$autopayActive = !empty($autoPayData['autopay_active']) && intval($autoPayData['autopay_active']) === 1;

// Функция для статусного баджа автоплатежа
function autopay_badge($active)
{
  if ($active === null || !$active) {
    return '<font class="animate-poulse"><i class=\'fa fa-xmark-circle\'></i> Не активен</font>';
  }
  if (intval($active) === 1) {
    return '<font class="animate-poulse"><i class="fa fa-bolt fa-shake"></i> Активен</font>';
  }
  return '<font class="animate-poulse"><i class="fa fa-clock"></i> Ожидание</font>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
  $CURRENCY = $_ENV['CURRENCY'] ?? 'RUB';
  $amount = 1;
  $desc = 'Привязка счета в сервисе CoraVPN';
  $customerEmail = trim($_POST['email'] ?? 'artemnersisyan777@gmail.com');

  $result = (new Functions)->Ykassa($amount, $CURRENCY, "https://www.coravpn.ru/success", $desc, $customerEmail);

  // Проверяем, что создание платежа прошло успешно и payment_url выглядит валидным
  if (
    is_array($result)
    && !empty($result['payment_id'])
    && !empty($result['payment_url'])
    && filter_var($result['payment_url'], FILTER_VALIDATE_URL)
    && stripos($result['payment_url'], 'https://') === 0
  ) {
    $_SESSION['temporary_email'] = $customerEmail;
    if (isset($_SESSION['selection_pay_type'])) {
      unset($_SESSION['selection_pay_type']);
    }
    Database::send('UPDATE vpn_users SET kassa_id = ? WHERE tg_id = ?', [strval($result['payment_id']), strval($client['tg_id'])]);
    $_SESSION['selection_pay_type'] = 'connect_card';
    $payment_url = trim(str_replace(["\n", "\r", "\t"], '', $result['payment_url']));
    header('Location: ' . $payment_url);
    exit;
  } else {
    // Собираем детализированное сообщение
    $err_text = 'Ykassa error: не удалось создать платеж';

    if (!empty($client['tg_id'])) {
      $err_text .= '. tg_id: ' . htmlspecialchars($client['tg_id']);
    }
    if (isset($result)) {
      $err_text .= ', response: ' . htmlspecialchars(var_export($result, true));
    }
    Message::set('error_card', $err_text);
  }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoraVpn Card</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://telegram.org/js/telegram-web-app.js"></script>
  <style>
    ::-webkit-scrollbar {
      width: 0;
    }

    body {
      background-image:
        linear-gradient(rgba(255, 255, 255, .07) 2px, transparent 2px),
        linear-gradient(90deg, rgba(255, 255, 255, .07) 2px, transparent 2px),
        linear-gradient(rgba(255, 255, 255, .06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, .06) 1px, transparent 1px);
      background-size: 100px 100px, 100px 100px, 20px 20px, 20px 20px;
      background-position: -2px -2px, -2px -2px, -1px -1px, -1px -1px;
      font-family: "Unica One", sans-serif;
      color: white;
      font-weight: 300;
      letter-spacing: .5px;
      line-height: 1;
    }

    @font-face {
      font-family: 'max';
      src: url('/public/assets/fonts/font.ttf') format('truetype');
    }

    body {
      background-color: #121212;
      color: #ffffff;
      padding: 20px;
      margin: 0;
    }

    .container {
      max-width: 720px !important;
      border-radius: 16px;
      border: 1px solid #838383;
      margin: 0 auto;
      padding: 20px;
    }

    .profile-header {
      text-align: center;
      padding: 20px 0;
      border-bottom: 1px solid #333;
    }

    .avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin: 0 auto 10px;
      border: 2px solid #a492ff;
    }

    .user-id {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-size: 14px;
      color: #aaa;
      margin-top: 5px;
    }

    .menu-item {
      display: flex;
      align-items: center;
      padding: 15px 20px;
      border-bottom: 1px solid #333;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .menu-item:hover {
      background-color: #222;
    }

    .menu-icon {
      width: 24px;
      height: 24px;
      margin-right: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .menu-text {
      font-size: 16px;
    }

    .section {
      background-color: #1e1e1e;
      border-radius: 12px;
      padding: 20px;
      margin: 20px 0;
    }

    .section-title {
      font-size: 14px;
      color: #aaa;
      margin-bottom: 10px;
    }

    .link-input {
      display: flex;
      align-items: center;
      background-color: #222;
      border-radius: 8px;
      padding: 10px 15px;
      margin-bottom: 10px;
    }

    .link-input input {
      flex: 1;
      background: transparent;
      border: none;
      color: white;
      font-size: 14px;
      outline: none;
    }

    .copy-btn {
      background: none;
      border: none;
      color: #aaa;
      cursor: pointer;
      font-size: 16px;
    }

    .action-button {
      width: 100%;
      padding: 15px;
      background-color: #222;
      border-radius: 12px;
      text-align: center;
      cursor: pointer;
      transition: background-color 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .action-button:hover {
      background-color: #333;
    }

    #modal-content::-webkit-scrollbar {
      width: 8px;
    }

    #modal-content::-webkit-scrollbar-thumb {
      background: #333;
      border-radius: 6px;
    }

    #modal-content::-webkit-scrollbar-track {
      background: #222;
      border-radius: 6px;
    }

    #modal-content {
      scrollbar-width: thin;
      scrollbar-color: #333 #222;
    }

    .modal-hidden {
      opacity: 0;
      transform: translateY(100vh);
      z-index: -9999;
    }

    .arrow-back {
      position: fixed;
      top: 24px;
      left: 24px;
      z-index: 100;
      display: flex;
      align-items: center;
      gap: 6px;
      background: rgba(55, 55, 69, 0.95);
      border-radius: 12px;
      padding: 8px 13px 8px 10px;
      box-shadow: 0 2px 10px 0 rgba(99, 86, 255, 0.10);
      cursor: pointer;
      transition: background 0.2s, color 0.13s;
      border: 1px solid #5B44B9;
      color: #beb8ff;
      font-weight: 600;
    }

    .arrow-back:hover {
      background: #413ba1;
      color: #fff;
    }

    .arrow-home {
      margin-left: 5px;
      color: #fff;
      background: #917fff;
      border-radius: 30px;
      padding: 4px 9px;
      font-size: 15px;
      transition: background 0.15s;
      border: none;
      outline: none;
      cursor: pointer;
      text-decoration: none !important;
    }

    .arrow-back svg {
      stroke: #9b94fa;
      width: 22px;
      height: 22px;
      display: flex;
    }

    /* ...custom styles as before... */
    .hidden_content {
      display: none;
    }

    .show_content {
      display: block;
      animation: 1s ease content_show;
    }

    @keyframes content_show {
      0% {
        transform: translateY(200px);
      }

      100% {
        transform: translateY(0px);
      }
    }
  </style>
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

          // Показываем результат
          document.body.innerHTML = `<? include_once __DIR__ . '/view.php' ?>`;

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


  <!-- карта -->
  <style>
    /* Плавающие формы */
    .blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      mix-blend-mode: multiply;
      opacity: 0.6;
      animation: blob 7s infinite;
    }

    .blob:nth-child(1) {
      background: #4facfe;
      width: 200px;
      height: 200px;
      top: -50px;
      right: -50px;
      animation-delay: 0s;
    }

    .blob:nth-child(2) {
      background: #f093fb;
      width: 200px;
      height: 200px;
      bottom: -50px;
      left: -50px;
      animation-delay: 2s;
    }

    .blob:nth-child(3) {
      background: #fee140;
      width: 200px;
      height: 200px;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      animation-delay: 4s;
    }

    @keyframes blob {
      0% {
        transform: translate(0px, 0px) scale(1);
      }

      33% {
        transform: translate(30px, -70px) scale(1.1);
      }

      66% {
        transform: translate(-20px, 20px) scale(0.9);
      }

      100% {
        transform: translate(0px, 0px) scale(1);
      }
    }

    /* Световые лучи */
    .ray {
      position: absolute;
      background: linear-gradient(to bottom, transparent, rgba(255, 255, 255, 0.1));
      width: 2px;
      height: 100%;
      transform-origin: center;
      animation: rayPulse 4s infinite ease-in-out;
    }

    .ray-1 {
      left: 25%;
      transform: rotate(45deg);
      animation-delay: 0s;
    }

    .ray-2 {
      right: 25%;
      transform: rotate(-45deg);
      animation-delay: 1s;
    }

    .ray-3 {
      top: 25%;
      transform: rotate(90deg);
      animation-delay: 2s;
    }

    .ray-4 {
      bottom: 25%;
      transform: rotate(0deg);
      animation-delay: 3s;
    }

    @keyframes rayPulse {
      0% {
        opacity: 0;
        transform: scale(0.5) rotate(45deg);
      }

      50% {
        opacity: 0.8;
        transform: scale(1.2) rotate(45deg);
      }

      100% {
        opacity: 0;
        transform: scale(0.5) rotate(45deg);
      }
    }

    /* Частицы */
    .particles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 1;
    }

    .particle {
      position: absolute;
      width: 2px;
      height: 2px;
      background: #ffffff;
      border-radius: 50%;
      opacity: 0.5;
      animation: float 3s infinite ease-in-out;
    }

    @keyframes float {
      0% {
        transform: translateY(0px) translateX(0px);
        opacity: 0.5;
      }

      50% {
        transform: translateY(-10px) translateX(5px);
        opacity: 0.8;
      }

      100% {
        transform: translateY(0px) translateX(0px);
        opacity: 0.5;
      }
    }

    /* Анимация появления текста */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fadeIn {
      animation: fadeIn 0.8s ease-out forwards;
    }

    .delay-300 {
      animation-delay: 0.3s;
    }

    .delay-500 {
      animation-delay: 0.5s;
    }

    .delay-700 {
      animation-delay: 0.7s;
    }

    .delay-900 {
      animation-delay: 0.9s;
    }

    .delay-1100 {
      animation-delay: 1.1s;
    }

    /* Медленный спин для ореола */
    .animate-spin-slow {
      animation: spin 10s linear infinite;
    }

    @keyframes spin {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    /* Пульсация чипа */
    .animate-pulse {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.8;
      }
    }

    .animate-ping {
      animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    }

    @keyframes ping {

      75%,
      100% {
        transform: scale(2);
        opacity: 0;
      }
    }
  </style>

  <script>
    // Генерация частиц
    function createParticles() {
      const container = document.getElementById('particles');
      for (let i = 0; i < 15; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 3 + 's';
        container.appendChild(particle);
      }
    }

    // Запуск при загрузке
    window.addEventListener('DOMContentLoaded', createParticles);
  </script>

  <?php
  // Короткое уведомление (toast)
  $notification = Message::controll();
  $types = ['success_card' => 'success', 'error_card' => 'error'];
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
