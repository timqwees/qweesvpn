<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoraVpn Download</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <!-- Подключение Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://telegram.org/js/telegram-web-app.js"></script>
  <style>
    ::-webkit-scrollbar {
      width: 0;
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

    body {
      background-color: #121212;
      color: #ffffff;
      padding: 20px;
      margin: 0;
    }

    .container {
      max-width: 720px !important;
      border-radius: 16px;
      /* border: 1px solid #838383; */
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

    /* ===  animation hidden */

    /* исчезает вниз */
    .ihidden-bottom {
      transition: 1.25s ease all;
      transform: translateY(200px);
      opacity: 0;
    }

    /* просто исчезает */
    .ihidden {
      transition: 1.25s ease all;
      opacity: 0;
    }

    /* просто появляеться */
    .ishow {
      transition: 1.25s ease all;
      opacity: 1 !important;
      animation: ishow ease-in 1.25s;
    }

    @keyframes ishow {
      0% {
        transform: scale(0);
      }

      100% {
        transform: scale(0.725);
      }
    }

    /* просто появляеться */
    .ishow2 {
      transition: 1.25s ease all;
      opacity: 1 !important;
      animation: ishow2 ease-in 1.25s;
    }

    @keyframes ishow2 {
      0% {
        transform: scale(0);
      }

      100% {
        transform: scale(1);
      }
    }

    /* === content */

    /* скрываем контент */
    .hidden_content {
      display: none;
    }

    /* показываем контент */
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

    /* смена цвета */
    .next_color:first-child::before {
      content: '';
      animation: progress 1.5s forwards;
    }

    .next_color::before {
      content: '';
      animation: progress_up 1.5s forwards;
    }

    @keyframes progress {
      0% {
        width: 15px;
      }

      100% {
        width: 100%;
        background: #a492ff;
        border-top-right-radius: 0px;
        border-bottom-right-radius: 0px;
      }
    }

    @keyframes progress_up {
      0% {
        width: 15px;
      }

      100% {
        width: 100%;
        background: #a492ff;
        border-radius: 0px;
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

  <!-- redirect success -->
  <?php
  if (isset($emailSuccess) && $emailSuccess && !empty($_SESSION['redirect_url'])) {
    $redirect = $_SESSION['redirect_url'];
    unset($_SESSION['redirect_url']);
    ?>
    <script>
      window.open("<?= htmlspecialchars($redirect) ?>", "_self");
    </script>
    <?php
  }
  ?>

  <!-- ### LOCAL componets ### -->
  <script src="/public/pages/download/assets/script.js" defer="true"></script>
  <!-- ### MAIN componet ### -->
  <script src="/public/assets/script/script.js" defer="true"></script>

  <!-- local script -->
  <script>
    // lines progress
    const line_1 = document.getElementById('line-1');
    const line_2 = document.getElementById('line-2');
    const line_3 = document.getElementById('line-3');
    // progress rounded
    const progress_rouded_1 = document.getElementById('progress_rouded_1');
    const progress_rouded_2 = document.getElementById('progress_rouded_2');
    const progress_rouded_3 = document.getElementById('progress_rouded_3');
    const progress_rouded_main = document.getElementById('progress_rouded_main');
    // contents
    const content_start = document.getElementById('content_start');
    const content_two = document.getElementById('content_two');
    const content_three = document.getElementById('content_three');
    const content_for = document.getElementById('content_for');
    // icon
    const icon = document.getElementById('icon');
    // index
    let stepIndex = 0;

    const steps = [
      "content_start",
      "content_two",
      "content_three",
      "content_for"
    ];
    const progress_roundeds = [
      progress_rouded_3,
      progress_rouded_2,
      progress_rouded_1,
      null // no round for the last step
    ];
    const lines = [
      line_1,
      line_2,
      line_3
    ];

    function setStepClasses(idx, direction) {
      // direction = 1 (forward), -1 (back), 0 (init)
      // resets/hides/shows all contents & progresses/hovers as needed

      // --- Content Panels ---
      [content_start, content_two, content_three, content_for].forEach((el, i) => {
        // clean up dynamic state for every panel
        el.classList.remove('ihidden-bottom', 'show_content', 'hidden_content');
        el.style.display = '';
      });

      if (idx >= 0 && idx < steps.length) {
        [content_start, content_two, content_three, content_for].forEach((el, i) => {
          if (i === idx) {
            el.classList.add('show_content');
            el.classList.remove('hidden_content');
          } else {
            el.classList.add('hidden_content');
            el.classList.remove('show_content');
          }
        });
      }

      // --- Progress Circles ---
      [progress_rouded_3, progress_rouded_2, progress_rouded_1].forEach((el, i) => {
        if (!el) return;
        // Показываем все, кроме тех что шаг дальше текущего
        if (i < idx) {
          el.classList.add('ihidden');
          setTimeout(() => {
            if (el) el.style.display = 'none';
          }, 1000);
        } else {
          el.classList.remove('ihidden');
          setTimeout(() => {
            if (el) el.style.display = 'flex';
          }, 800);
        }
      });

      // --- Lines ---
      [line_1, line_2, line_3].forEach((line, i) => {
        line.classList.remove('next_color');
        if (i < idx) {
          line.classList.add('next_color');
        }
      });

      // --- Icon state ---
      if (idx === 3) {
        icon.classList.remove('fa-plug');
        icon.classList.add('fa-check-circle');
        icon.classList.remove('ihidden');
        icon.classList.add('ishow');
        icon.classList.add('rotate-[0deg]');
      } else {
        icon.classList.remove('fa-check-circle', 'ishow', 'ihidden', 'rotate-[0deg]');
        icon.classList.add('fa-plug');
      }
    }

    function Start() {
      // Перейти вперед на шаг
      if (stepIndex < steps.length - 1) {
        console.log(`[DEBUG] Увеличение индекса шага: ${stepIndex} -> ${stepIndex + 1}`);
        stepIndex++;
        setStepClasses(stepIndex, 1);
      }
    }

    function showStep(idx) {
      stepIndex = idx;
      setStepClasses(stepIndex, 0);
      updateBackBtnText();
    }

    function goBack() {
      if (stepIndex === 0) {
        window.location.href = "/";
        return;
      }
      stepIndex--;
      setStepClasses(stepIndex, -1);
      updateBackBtnText();
    }

    function updateBackBtnText() {
      const btnText = document.getElementById('backBtnText');
      const arrowHome = document.getElementById('arrowHomeLink');
      if (btnText) {
        if (stepIndex === 0) {
          btnText.textContent = "Вернуться";
          if (arrowHome) arrowHome.style.display = "";
        } else {
          btnText.textContent = "Назад";
          if (arrowHome) arrowHome.style.display = "none";
        }
      }
    }

    window.addEventListener("DOMContentLoaded", () => {
      showStep(0);
      updateBackBtnText();
      const backBtn = document.getElementById('globalArrowBack');
      if (backBtn) {
        backBtn.addEventListener('click', function () {
          goBack();
        });
      }
      document.addEventListener('keydown', (ev) => {
        if ((ev.key === "ArrowLeft")) {
          ev.preventDefault();
          goBack();
        }
      });
    });
  </script>

</body>

</html>
