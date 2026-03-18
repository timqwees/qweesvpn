<?php
// Подключаем service.php для получения переменных и HTML контента
ob_start();
include_once __DIR__ . '/service.php';
$serviceContent = ob_get_clean();
// Переменные из service.php теперь доступны: $TARIFFS, $PLAN_LABELS, $PLAN_KEYS, $array, etc.
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoraVpn Pay</title>
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

    @keyframes fade-in-up {
      0% {
        opacity: 0;
        transform: translateY(32px) scale(0.98);
      }

      100% {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    @keyframes glow {
      0% {
        box-shadow: 0 0 8px 0 #9f8afb33;
      }

      50% {
        box-shadow: 0 0 18px 6px #a492ff66;
      }

      100% {
        box-shadow: 0 0 8px 0 #9f8afb33;
      }
    }

    body {
      background: linear-gradient(135deg, #2d2353 0%, #181925 100%);
      font-family: 'max', 'Unica One', sans-serif;
      color: #fff;
      min-height: 100vh;
      animation: fade-in-up .9s cubic-bezier(.24, .6, .38, 1.01) 0.06s both;
      margin: 0;
    }

    .container {
      max-width: 520px !important;
      border-radius: 24px;
      margin: 58px auto;
      margin-bottom: 0;
      padding: 32px 22px 22px 22px !important;
      background: rgba(28, 28, 42, 0.99);
      box-shadow: 0 8px 40px 0 #5a49b020;
      backdrop-filter: blur(8px);
      animation: fade-in-up 0.8s cubic-bezier(.33, 1.06, .56, 1) 0.12s both;
      border: 2px solid transparent;
      transition: border 0.4s cubic-bezier(.64, .03, .25, 1), box-shadow 0.3s cubic-bezier(.14, .67, .19, .95);
    }

    .header-title {
      font-size: 2.2rem;
      font-weight: 800;
      text-align: center;
      margin-bottom: 10px;
      letter-spacing: 1px;
      background: linear-gradient(90deg, #a492ff 0%, #fbefff 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .header-desc {
      color: #bdbdfa;
      font-size: 1.12rem;
      text-align: center;
      margin-bottom: 28px;
      letter-spacing: 0.03em;
      opacity: 0.83;
    }

    .plans {
      display: flex;
      flex-direction: column;
      gap: 22px;
      margin-bottom: 34px;
    }

    .plan-card {
      background: linear-gradient(95deg, #232336 83%, #5242c6bd 100%);
      border-radius: 17px;
      padding: 24px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border: 2.5px solid transparent;
      cursor: pointer;
      transition:
        background 0.35s cubic-bezier(.42, .15, .57, 1),
        color 0.23s cubic-bezier(.42, 0, .57, 1),
        border 0.24s cubic-bezier(.42, 0, .57, 1),
        box-shadow 0.21s cubic-bezier(.57, 0, .72, 1.23),
        transform 0.19s cubic-bezier(.44, .53, .54, 1.25);
      animation: fade-in-up .82s cubic-bezier(.14, .94, .52, 1.03);
    }

    .plan-card.selected,
    .plan-card:hover {
      border: 2.5px solid #a492ff;
      box-shadow: 0 4px 36px 0 #a492ff38;
      background: linear-gradient(100deg, #40328b 93%, #836cffcb 100%);
      transform: scale(1.027) translateY(-2px);
      animation: glow 2s infinite linear alternate;
    }

    .plan-info {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }

    .plan-title {
      font-size: 1.14rem;
      font-weight: 700;
      color: #fff;
      transition: color 0.19s;
      letter-spacing: .08em;
    }

    .plan-period {
      color: #b8a6ff;
      font-size: 1rem;
      font-weight: 500;
      transition: color 0.2s;
    }

    .plan-desc {
      font-size: 0.99rem;
      color: #afabea;
      font-weight: 400;
      opacity: .85;
    }

    .plan-price {
      font-size: 1rem;
      font-weight: 900;
      color: #d4d0ff;
      transition: color 0.23s;
      filter: drop-shadow(0 2px 4px #a492ff30);
      text-shadow: 0 1px 4px #4c3dbb27;
      letter-spacing: .11em;
    }

    .pay-section {
      margin-top: 32px;
    }

    .pay-btn {
      width: 100%;
      background: linear-gradient(97deg, #6054C9 0%, #301BBB 57%, #a492ff 100%);
      color: #ffeefe;
      font-weight: 700;
      border: 1.5px solid #855DFC;
      border-radius: 15px;
      cursor: pointer;
      letter-spacing: 0.06em;
      transition:
        background 0.18s cubic-bezier(.7, .04, .54, 1.14),
        color 0.16s cubic-bezier(.48, .31, .02, .97),
        box-shadow 0.26s cubic-bezier(.19, .92, .63, 1.09),
        transform 0.16s cubic-bezier(.22, .92, .54, 1.2),
        letter-spacing 0.13s cubic-bezier(.44, .11, .54, .91);
      margin-bottom: 22px;
      animation: fade-in-up .78s cubic-bezier(.36, .64, .59, .99) 0.13s both, glow 2.8s cubic-bezier(.66, .27, .94, .75) 2 both;
      box-shadow: 0 4px 28px 0 #a492ff66, 0 0 0 2px #fff0fd09 inset, 0 1.5px 16px #4531a696;
      font-size: 1.18em;
      text-shadow: 0 2px 18px #9570ff7d, 0 1px 0 #392697;
      filter: brightness(1.07) saturate(1.14);
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .pay-btn::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(120deg, #ffdbfc3a 20%, #c2b6ff77 55%, #a492ffd5 100%);
      opacity: 0.24;
      border-radius: 15px;
      z-index: -1;
      transition: opacity 0.24s cubic-bezier(.53, .19, .81, .91);
      pointer-events: none;
    }

    .pay-btn:hover:not(:disabled),
    .pay-btn:focus:not(:disabled) {
      background: linear-gradient(95deg, #7d5cf7 0%, #a492ff 70%, #ffafe9 102%);
      color: #fff5fb;
      transform: translateY(-2.5px) scale(1.049) rotate(-0.5deg);
      box-shadow: 0 8px 36px 0 #a492ff81, 0 22px 42px -14px #cea2f851, 0 0 12px #fff0fd31;
      filter: brightness(1.10) saturate(1.19);
      border-color: #e7c5ff;
    }

    .back-btn {
      width: 100%;
      padding: 15px;
      color: #fff;
      font-size: 1.07rem;
      font-weight: 600;
      border: 1.5px solid #4e467d;
      border-radius: 12px;
      cursor: pointer;
      transition: background 0.2s, color 0.21s, border 0.21s, box-shadow 0.22s;
      box-shadow: 0 2px 16px 0 #a492ff1a;
      margin-bottom: 12px;
      background: linear-gradient(100deg, #1e1935 80%, #2a2674 100%);
    }

    .back-btn:hover {
      border-color: #a492ff;
      background: #2f29a0;
      color: #f0ddfc;
      box-shadow: 0 5px 22px -6px #7967ff33;
    }

    .mail-input {
      width: 100%;
      padding: 15px;
      color: #fff;
      font-size: 1.07rem;
      border: 1.5px solid #4e467d;
      border-radius: 12px;
      cursor: pointer;
      transition: background 0.2s, color 0.21s, border 0.21s, box-shadow 0.22s;
      box-shadow: 0 2px 16px 0 #a492ff1a;
      margin-bottom: 12px;
      background: linear-gradient(100deg, #1e1935 80%, #2a2674 100%);
      position: relative;
      letter-spacing: 1.25px;
    }

    .mail-input:hover {
      border-color: #a492ff;
      background: linear-gradient(100deg, #6E4FF9 80%, #2a2674 100%);
      box-shadow: 0 5px 22px -6px #7967ff33;
      color: #f0ddfc;
      text-decoration: underline;
      position: relative;
    }

    .pay-section:has(.mail-input:hover) ::placeholder {
      color: lightgray;
    }

    .pay-section:has(.mail-input) ::placeholder {
      /* content: '✉️'; */
      /* position: absolute;
      top: 105%;
      left: 0; */
      --time: 2s;
      animation: pulse var(--time) infinite;
      transition: --var(--time) ease all;
    }

    @keyframes pulse {
      0% {
        opacity: 1;
      }

      50% {
        opacity: 0;
      }

      100% {
        opacity: 1;
      }
    }

    .pay-desc {
      color: #bdbdfc;
      font-size: 0.97rem;
      text-align: center;
      opacity: 0.76;
    }

    .payment-methods {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      gap: 18px;
      margin-top: 15px;
      margin-bottom: 6px;
      animation: fade-in-up .67s cubic-bezier(.2, .94, .46, 1.12);
    }

    .payment-method {
      background: #232336;
      border-radius: 11px;
      padding: 10px 20px;
      color: #fff;
      font-size: 1.05rem;
      border: 2px solid #35346c;
      cursor: pointer;
      transition: border 0.15s, background 0.29s, color 0.17s, box-shadow 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 2px 4px #0002;
    }

    .payment-method.selected,
    .payment-method:hover {
      border: 2px solid #a492ff;
      background: linear-gradient(94deg, #433ca8 83%, #7464e9c6 100%);
      color: #fff;
      box-shadow: 0 0 20px -8px #a492ff3b, 0 3px 12px #7967ff16;
      transform: scale(1.08);
      animation: glow 1s infinite alternate;
    }

    .plan-range-wrapper {
      margin: 0 0 33px 0;
      animation: fade-in-up .73s cubic-bezier(.14, .74, .42, 1.07);
    }

    .plan-range-labels {
      display: flex;
      justify-content: space-between;
      margin-top: 7px;
      margin-left: 6px;
      margin-right: 6px;
      font-size: 1.02rem;
      color: #ada6d8;
      letter-spacing: 0.12em;
      user-select: none;
    }

    input[type="range"].plan-range {
      width: 99%;
      margin-top: 22px;
      accent-color: #b09afd;
      height: 8px;
      border-radius: 8px;
      background: linear-gradient(90deg, #6e65bc 0%, #b09afd 100%);
      outline: none;
      transition: background 0.37s cubic-bezier(.24, .87, .43, 1.22), box-shadow 0.28s cubic-bezier(.37, .88, .41, 1.09);
      box-shadow: 0px 1px 6px 0 #7b71cc44;
    }

    input[type="range"].plan-range::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 27px;
      height: 27px;
      border-radius: 50%;
      background: linear-gradient(120deg, #b09afd 0%, #fff 97%);
      cursor: pointer;
      box-shadow: 0px 2px 12px #9283e38a;
      border: 4px solid #453987;
      transition: background 0.26s, box-shadow 0.17s, border 0.16s;
    }

    input[type="range"].plan-range:focus::-webkit-slider-thumb,
    input[type="range"].plan-range:hover::-webkit-slider-thumb {
      background: linear-gradient(120deg, #fff 70%, #b09afd 100%);
      box-shadow: 0px 2px 22px #b09afd88, 0 8px 12px -4px #b09afd30;
      border: 4px solid #b09afd;
    }

    input[type="range"].plan-range::-moz-range-thumb {
      width: 27px;
      height: 27px;
      border-radius: 50%;
      background: linear-gradient(120deg, #b09afd 0%, #fff 97%);
      cursor: pointer;
      box-shadow: 0px 2px 12px #9283e38a;
      border: 4px solid #453987;
      transition: background 0.26s, box-shadow 0.17s, border 0.16s;
    }

    input[type="range"].plan-range:focus::-moz-range-thumb,
    input[type="range"].plan-range:hover::-moz-range-thumb {
      background: linear-gradient(120deg, #fff 70%, #b09afd 100%);
      box-shadow: 0px 2px 22px #b09afd88, 0 8px 12px -4px #b09afd30;
      border: 4px solid #b09afd;
    }

    input[type="range"].plan-range::-ms-thumb {
      width: 27px;
      height: 27px;
      border-radius: 50%;
      background: linear-gradient(120deg, #b09afd 0%, #fff 97%);
      box-shadow: 0px 2px 12px #9283e38a;
      border: 4px solid #453987;
      transition: background 0.26s, box-shadow 0.17s, border 0.16s;
    }

    input[type="range"].plan-range:focus::-ms-thumb {
      background: linear-gradient(120deg, #fff 70%, #b09afd 100%);
      box-shadow: 0px 2px 22px #b09afd88, 0 8px 12px -4px #b09afd30;
      border: 4px solid #b09afd;
    }

    input[type="range"].plan-range::-ms-fill-lower {
      background: #18181e;
    }

    input[type="range"].plan-range::-ms-fill-upper {
      background: #29295d;
    }

    input[type="range"].plan-range:focus {
      outline: none;
    }

    @media (max-width: 620px) {
      .container {
        padding: 16px 4px;
      }

      .header-title {
        font-size: 1.27rem;
      }

      .plan-title {
        font-size: 0.97rem;
      }

      .plan-price {
        font-size: 1.03rem;
      }

      .plan-desc,
      .plan-period {
        font-size: .93rem;
      }

      .plan-range-labels {
        font-size: 0.94rem;
      }

      .pay-btn,
      .back-btn {
        font-size: 0.94rem;
        padding: 11px !important;
      }
    }

    .arrow-back {
      position: fixed;
      top: 24px;
      left: 24px;
      z-index: 100;
      display: flex;
      align-items: center;
      gap: 7px;
      background: rgba(77, 70, 134, 0.92);
      border-radius: 15px;
      padding: 10px 16px 10px 13px;
      box-shadow: 0 2px 20px 0 rgba(99, 86, 255, 0.12);
      cursor: pointer;
      transition: background 0.32s, color 0.16s, box-shadow 0.23s;
      border: 1.5px solid #7c6ced;
      color: #e2ddfc;
      font-weight: 800;
      font-size: 1.01em;
      backdrop-filter: blur(2.5px);
    }

    .arrow-back:hover {
      background: #3c37a19d;
      color: #f7f5ff;
      box-shadow: 0 4px 22px 0 #b09afd77;
    }

    .arrow-home {
      margin-left: 6px;
      color: #fff;
      background: #917fff;
      border-radius: 30px;
      padding: 5px 12px;
      font-size: 16px;
      transition: background 0.15s;
      border: none;
      outline: none;
      cursor: pointer;
      text-decoration: none !important;
    }

    .arrow-back svg {
      stroke: #d7d2fa;
      width: 24px;
      height: 24px;
      display: flex;
      filter: drop-shadow(0 1px 4px #b09afd33);
    }

    .hidden_content {
      display: none;
    }

    .show_content {
      display: block;
      animation: fade-in-up .75s cubic-bezier(.3, 1.09, .58, 1.04) both;
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
          document.body.innerHTML = <?= json_encode($serviceContent); ?>;

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

  <!-- ### MAIN componet ### -->
  <script src="/public/assets/script/script.js" defer="true"></script>

  <script>
    // PLAVNYE EFFECTS ON APPEAR
    let stepIndex = 0;
    const steps = [
      "content_start",
      "content_two",
      "content_three",
      "content_for"
    ];

    function showStep(idx) {
      for (let i = 0; i < steps.length; i++) {
        const el = document.getElementById(steps[i]);
        if (el) {
          if (i === idx) {
            el.classList.remove("hidden_content");
            el.classList.add("show_content");
          } else {
            el.classList.remove("show_content");
            el.classList.add("hidden_content");
          }
        }
      }
      stepIndex = idx;
      updateBackBtnText();
    }

    function goBack() {
      if (stepIndex === 0) {
        window.location.href = "/";
        return;
      }
      showStep(stepIndex - 1);
    }

    function Start() {
      if (stepIndex < steps.length - 1) {
        showStep(stepIndex + 1);
      }
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

    function animateSelectionEl(el) {
      if (!el) return;
      el.classList.add('plan-card-anim');
      setTimeout(() => {
        el.classList.remove('plan-card-anim');
      }, 520);
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

      // --- Range slider logic for new 3 тарифы ---
      const PLAN_KEYS = <?php echo json_encode(array_keys($PLAN_LABELS)); ?>;
      const PLAN_LABELS = <?php echo json_encode($PLAN_LABELS); ?>;
      const TARIFFS = <?php
      echo json_encode(array_map(function ($tar) {
        return [
          'title' => $tar['title'],
          'desc' => $tar['desc'],
          'plans' => $tar['plans'],
        ];
      }, $TARIFFS));
      ?>;

      const tariffKeys = Object.keys(TARIFFS);
      const planRange = document.getElementById('plan-range');
      let activePlanIdx = 0;

      function updateAllTariffPlans(planIdx) {
        const refer_discount = <?= json_encode($refer_discount) ?>;
        for (let i = 0; i < tariffKeys.length; ++i) {
          const tk = tariffKeys[i];
          const planKey = PLAN_KEYS[planIdx];
          const plan = TARIFFS[tk]['plans'][planKey];
          const card = document.getElementById('plan-card-' + i);
          if (!card) continue;
          const period = card.querySelector('.plan-period');
          const price = card.querySelector('.plan-price');
          if (plan && period && price) {
            period.innerHTML = '<span style="transition: color .2s ease; color:#b8a6ff;">' + plan.period + '</span>';
            // Применяем скидку, если refer_discount > 0
            if (refer_discount > 0) {
              price.innerHTML =
                '<span style="color: #c1b2ff; font-weight: 600;">' + plan.price + ' ₽</span>' +
                '<span class="text-gray-400 line-through text-[0.97em] font-normal ml-2">' +
                Math.floor(plan.price / (1 - refer_discount / 100)) + ' ₽</span>' +
                '<span class="flex items-center justifyc-center absolute text-[10px] -top-[60px] rounded-lg px-2 py-1 -right-2 flex z-50 w-[100px] bg-[#232336]">скидка<font claass="text-green-400 mr-2">-' + refer_discount + '%</font></span>';
            } else {
              price.innerHTML = '<span style="color: #c1b2ff; font-weight: 600;">' + plan.price + ' ₽</span>'
            }
          }
        }
        document.getElementById('selected-tariff').value = tariffKeys[document.querySelector('.plan-card.selected').dataset.index];
        document.getElementById('selected-period').value = PLAN_KEYS[planIdx];
      }
      const planCards = Array.from({ length: 3 }, (_, i) => document.getElementById('plan-card-' + i));
      planCards.forEach((card, idx) => {
        card.addEventListener('click', function () {
          planCards.forEach(c => c.classList.remove('selected'));
          this.classList.add('selected');
          // Анимация плавного выделения
          animateSelectionEl(this);
          updateAllTariffPlans(activePlanIdx);
        });
      });
      if (planRange) {
        planRange.addEventListener('input', function () {
          activePlanIdx = Math.round(this.value);
          updateAllTariffPlans(activePlanIdx);
          // Highlight the thumb
          this.classList.add('active-slider');
          setTimeout(() => this.classList.remove('active-slider'), 420);
        });
        updateAllTariffPlans(activePlanIdx);
      }
    });

    // Красиво выделение метода оплаты
    window.selectPayment = function (el) {
      const methods = document.querySelectorAll('.payment-method');
      methods.forEach(m => m.classList.remove('selected'));
      el.classList.add('selected');
      document.getElementById('selected-method').value = el.getAttribute('data-method');//тут payment_method вставляется выбранный метод
      el.style.transform = "scale(1.16)";
      el.style.boxShadow = "0 0 30px 2px #b09afd44";
      setTimeout(() => {
        el.style.transform = "";
        el.style.boxShadow = "";
      }, 370);
    };

    // Закрыть модальное окно
    document.getElementById('closeBtn').addEventListener('click', () => {
      document.getElementById('myModal').classList.add('hidden');
    });
  </script>

</body>

</html>
