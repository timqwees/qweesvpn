<?php

use App\Models\Network\Message;
use App\Models\Network\Network;
use Setting\Route\Function\Functions;

$client = (new Functions())->client($_SESSION['client'] ?? 0);
$sub_link = $client['vpn_subscription'] ?? '';
$refer_link_url = 'https://coravpn.ru/';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reflink'])) {
  if (strpos($_POST['reflink'], 'https') !== 0) {//код ref=xxxxxxx
    $result_link = $_POST['reflink'];//ref=xxxxxxx
  } else {//если есть https то это ссылка
    $refer_link = $_POST['reflink'];
    if (strpos($refer_link, $refer_link_url) !== False) {//если в ссылке есть адрес
      $result_link = substr($refer_link, strlen($refer_link_url));//ref=xxxxxxx
    }
  }
  // Обновляем CID данные перед установкой реферальной ссылки
  $cidData = Functions::getClientCID();
  Functions::saveClientCID($client['tg_id'], $cidData);
  (new Functions())->setRefer($client['tg_id'], $result_link);
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoraVpn Profile</title>
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
      font-family: 'max', sans-serif;
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
          document.body.innerHTML = `<? include_once __DIR__ . '/service.php' ?>`;

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

  <!-- ### LOCAL componets ### -->
  <script>
    function copyId() {
      const id = document.getElementById('copy-id')?.textContent.trim();
      if (!id) return;
      navigator.clipboard.writeText(id);
      const btn = document.getElementById('copy-id-btn');
      btn.classList.add('copied');
      btn.innerHTML = '<i class="fa fa-check mr-2 text-green-500"></i><font class="text-green-500">Скопировано!</font>';
      setTimeout(() => {
        btn.classList.remove('copied');
        btn.innerHTML = '<i class="fa fa-clone mr-2 text-gray-400"></i>';
      }, 10000);
    }

    function copyLink() {
      const link = document.getElementById('subscription-link')?.value;
      if (!link) return;
      navigator.clipboard.writeText(link);
      const btn = document.getElementById('copy-link-btn');
      btn.innerHTML = '<i class="fa fa-check text-green-500"></i>';
      setTimeout(() => {
        btn.innerHTML = '<i class="fa fa-clone text-gray-400"></i>';
      }, 10000);
    }

    function copyRef() {
      const refCode = document.getElementById('copy-refs')?.value;
      if (!refCode) return;
      navigator.clipboard.writeText(refCode);
      const btn = document.getElementById('copy-ref-btn');
      btn.classList.add('copied');
      btn.innerHTML = '<i class="fa fa-check text-green-500"></i>';
      setTimeout(() => {
        btn.classList.remove('copied');
        btn.innerHTML = '<i class="fa fa-clone mr-2 text-gray-400"></i>';
      }, 10000);
    }

    function copyFullRef() {
      const refLink = document.getElementById('copy-fullrefs')?.value;
      if (!refLink) return;
      navigator.clipboard.writeText(refLink);
      const btn = document.getElementById('copy-fullref-btn');
      btn.classList.add('copied');
      btn.innerHTML = '<i class="fa fa-check text-green-500"></i>';
      setTimeout(() => {
        btn.classList.remove('copied');
        btn.innerHTML = '<i class="fa fa-clone mr-2 text-gray-400"></i>';
      }, 10000);
    }

    // Модалка
    const modal = document.getElementById('modal');
    const closeBtn = document.getElementById('close-modal');
    function openModal(ev) {
      const t = ev.querySelector('.modal-title'), c = ev.querySelector('.modal-content');
      document.getElementById('modal-title').innerText = t ? t.innerText : '';
      document.getElementById('modal-content').innerHTML = c ? c.innerHTML : '';
      modal.classList.remove('modal-hidden');
    }
    closeBtn.onclick = e => { e.stopPropagation(); modal.classList.add('modal-hidden'); };
    modal.onclick = e => { if (e.target === modal) modal.classList.add('modal-hidden'); };

    // Шаги
    let stepIndex = 0, steps = ["content_start", "content_two", "content_three", "content_for"];
    function showStep(idx) {
      steps.forEach((id, i) => {
        const el = document.getElementById(id);
        if (el) el.classList[i === idx ? 'add' : 'remove']('show_content'), el.classList[i !== idx ? 'add' : 'remove']('hidden_content');
      });
      stepIndex = idx; updateBackBtnText();
    }
    function goBack() {
      if (!stepIndex) return location.href = "/";
      showStep(stepIndex - 1);
    }
    function Start() { if (stepIndex < steps.length - 1) showStep(stepIndex + 1); }
    function updateBackBtnText() {
      const btnText = document.getElementById('backBtnText'), arrowHome = document.getElementById('arrowHomeLink');
      if (btnText) {
        btnText.textContent = stepIndex ? "Назад" : "Вернуться";
        if (arrowHome) arrowHome.style.display = stepIndex ? "none" : "";
      }
    }
    window.addEventListener("DOMContentLoaded", () => {
      showStep(0); updateBackBtnText();
      document.getElementById('globalArrowBack')?.addEventListener('click', goBack);
      document.addEventListener('keydown', ev => { if (ev.key === "ArrowLeft") { ev.preventDefault(); goBack(); } });
    });
  </script>

  <!-- ### MAIN componet ### -->
  <script src="/public/assets/script/script.js" defer="true"></script>

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
