<?php
use App\Config\Database;
use App\Config\Session;
use App\Models\Network\Network;
use Setting\Route\Function\Functions;

// Инициализация сессии
Session::init();
$client = (new Functions())->client($_SESSION['client'] ?? 0);
$verefy_free_client = ($client['vpn_freekey'] === $_ENV['VPN_FREE_CLIENT_FREEKEY'] ?: 'used_free'); //return true || false
$price = (new Functions())->isPrice();

$basic_amount = $price['basic'];
$plus_amount = $price['plus'];
$pro_amount = $price['pro'];

// ================= Применяем n% скидку
$refer_discount = isset($client['refer_discount']) ? intval($client['refer_discount']) : 0;
if ($refer_discount > 0) {
  $basic_amount = floor($basic_amount * (100 - $refer_discount) / 100);
  $plus_amount = floor($plus_amount * (100 - $refer_discount) / 100);
  $pro_amount = floor($pro_amount * (100 - $refer_discount) / 100);
}
// ==================

// 3 разных тарифа
$TARIFFS = [
  'basic' => [
    'title' => 'Базовый',
    'desc' => '1 устройство, ограниченный функционал',
    'divice' => 1,
    'plans' => [
      '1m' => ['price' => $basic_amount, 'period' => '1 месяц', 'count_days' => 30],
      '3m' => ['price' => $basic_amount * 3 - 50, 'period' => '3 месяца', 'count_days' => 90],
      '6m' => ['price' => $basic_amount * 6 - 150, 'period' => '6 месяцев', 'count_days' => 120],
      '12m' => ['price' => $basic_amount * 12 - 250, 'period' => '12 месяцев', 'count_days' => 365],
    ],
  ],
  'plus' => [
    'title' => 'Профессиональный',
    'desc' => '5 устройств, расширенный функционал',
    'divice' => 5,
    'plans' => [
      '1m' => ['price' => $plus_amount, 'period' => '1 месяц', 'count_days' => 30],
      '3m' => ['price' => $plus_amount * 3 - 50, 'period' => '3 месяца', 'count_days' => 90],
      '6m' => ['price' => $plus_amount * 6 - 150, 'period' => '6 месяцев', 'count_days' => 120],
      '12m' => ['price' => $plus_amount * 12 - 250, 'period' => '12 месяцев', 'count_days' => 365],
    ],
  ],
  'pro' => [
    'title' => 'Бизнес',
    'desc' => '10 устройств, максимальный функционал',
    'divice' => 10,
    'plans' => [
      '1m' => ['price' => $pro_amount, 'period' => '1 месяц', 'count_days' => 30],
      '3m' => ['price' => $pro_amount * 3 - 50, 'period' => '3 месяца', 'count_days' => 60],
      '6m' => ['price' => $pro_amount * 6 - 150, 'period' => '6 месяцев', 'count_days' => 90],
      '12m' => ['price' => $pro_amount * 12 - 250, 'period' => '12 месяцев', 'count_days' => 365],
    ],
  ],
];
$PLAN_LABELS = [
  '1m' => '1 мес',
  '3m' => '3 мес',
  '6m' => '6 мес',
  '12m' => '12 мес',
];
$PLAN_KEYS = array_keys($PLAN_LABELS);
$CURRENCY = $_ENV['CURRENCY'] ?? 'RUB';

// Проверка POST запроса ДО вывода любого HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_data'])) {
  $tarif = $_POST['tariff'];
  $period = $_POST['period'];
  $customerEmail = $_POST['customerEmail'];
  $paymentMethod = $_POST['payment_method'] ?? 'card'; // 'card' или 'sbp' или 'sberbank'

  if (!empty($TARIFFS) && isset($TARIFFS[$tarif]) && isset($TARIFFS[$tarif]['plans'][$period])) {
    //get data
    $amount = $TARIFFS[$tarif]['plans'][$period]['price'];
    $get_period = $TARIFFS[$tarif]['plans'][$period]['period'];
    $count_days = $TARIFFS[$tarif]['plans'][$period]['count_days'];
    $divice_limit = $TARIFFS[$tarif]['divice'];

    // Применяем скидку n% если у пользователя активирована реферальная ссылка
    if ($refer_discount > 0) {
      $desc = 'VPN тариф: ' . $TARIFFS[$tarif]['title'] . ' - ' . $TARIFFS[$tarif]['desc'] . ' - ' . $get_period . ' - ' . $divice_limit . ' устройств(о) (скидка ' . $refer_discount . '%)';
    } else {
      $desc = 'VPN тариф: ' . $TARIFFS[$tarif]['title'] . ' - ' . $TARIFFS[$tarif]['desc'] . ' - ' . $get_period . ' - ' . $divice_limit . ' устройств(о)';
    }

    // Передаем выбранный способ оплаты в функцию Ykassa
    //===========================================================================
    $tg_id = $client['tg_id'] ?? '';
    $pay_type = 'pay_tarif'; // Тип оплаты для восстановления сессии после возврата из браузера
    $returnUrl = "https://www.coravpn.ru/success";
    if (!empty($tg_id)) {
      $returnUrl .= "?tg_id=" . urlencode($tg_id) . "&pay_type=" . urlencode($pay_type) . "&mail=" . urlencode($customerEmail);
    }
    $array = (new Functions())->Ykassa($amount, $CURRENCY, $returnUrl, $desc, $customerEmail, null, true, $paymentMethod);
    //============================================================================
    // Проверяем, что это не ошибка
    if (!empty($array) && is_array($array) && isset($array['payment_id'])) {
      // Устанавливаем только ID платежа, цену запишем только после успешной оплаты
      Database::send('UPDATE vpn_users SET kassa_id = ?, vpn_date_count = ?, vpn_divece_count = ? WHERE tg_id = ?', [strval($array['payment_id']), intval($count_days), intval($divice_limit), strval($client['tg_id'])]);
      $_SESSION['temporary_email'] = $customerEmail;

      // Для СБП и карты - редирект на готовую страницу ЮKassa
      // При использовании confirmation.type = 'redirect' QR-код будет отображаться на странице ЮKassa
      if (isset($array['payment_url']) && strpos($array['payment_url'], 'https://') === 0) {
        $payment_url = $array['payment_url'];
        $payment_url = trim($payment_url);
        $payment_url = str_replace(["\n", "\r", "\t"], '', $payment_url);
        $payment_url = filter_var($payment_url, FILTER_SANITIZE_URL);

        // Проверяем, что это валидный URL
        if (filter_var($payment_url, FILTER_VALIDATE_URL)) {
          // Открываем ссылку в браузере через Telegram WebApp API
          if (
            $client['vpn_freekey'] !== ($_ENV['VPN_FREE_CLIENT_FREEKEY'] ?? 'used_free')
            && $client['vpn_freekey'] !== 'buy'
          ) {
            if (isset($_SESSION['selection_pay_type'])) {
              unset($_SESSION['selection_pay_type']);
            }
            $_SESSION['selection_pay_type'] = 'pay_tarif';
            // Возвращаем HTML страницу с JavaScript для открытия ссылки в браузере
            echo '<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Перенаправление на оплату</title>
  <script src="https://telegram.org/js/telegram-web-app.js"></script>
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: linear-gradient(135deg, #2d2353 0%, #181925 100%);
      color: #fff;
      font-family: sans-serif;
      margin: 0;
    }
    .container {
      text-align: center;
      padding: 20px;
    }
    .spinner {
      border: 4px solid #a492ff33;
      border-top: 4px solid #a492ff;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      animation: spin 1s linear infinite;
      margin: 20px auto;
    }
    .back-btn {
      margin-top: 35px;
      padding: 10px 22px;
      background: #4134ad;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.23s;
      box-shadow: 0 2px 10px #18192540;
    }
    .back-btn:hover {
      background: #685cff;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="spinner"></div>
    <p>Открываем страницу оплаты в браузере...</p>
    <button class="back-btn" onclick="goBack()">Вернуться</button>
  </div>
  <script>
    function goBack() {
      if (window.Telegram && window.Telegram.WebApp) {
        window.location.href = "/";
      } else if (window.history.length > 1) {
        window.history.back();
      } else {
        window.location.href = "/pay";
      }
    }

    (function() {
      const paymentUrl = ' . json_encode($payment_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';

      if (window.Telegram && window.Telegram.WebApp) {
        // Используем Telegram WebApp API для открытия ссылки в браузере
        window.Telegram.WebApp.openLink(paymentUrl);
      } else {
        // Fallback для обычных браузеров
        window.open(paymentUrl, "_blank");
      }
    })();
  </script>
</body>
</html>';
            exit;
          } else {
            Network::onRedirect('/crashdamp_buy_free');
          }
          exit;
        } else {
          echo 'Invalid payment URL: ' . htmlspecialchars($payment_url);
        }
      } else {
        echo 'Ykassa error: не удалось получить payment_url. tg_id: ' . $client['tg_id'] . ', response: ' . print_r($array, true);
      }
    } else {
      // Ошибка создания платежа
      echo 'Ykassa error: не удалось создать платеж. tg_id: ' . $client['tg_id'] . ', response: ' . print_r($array, true);
    }
  } else {
    echo 'Invalid tariff or period: ' . $tarif . ' / ' . $period;
  }
}
?>

<div
  class="relative flex flex-col items-center justify-center min-h-screen from-slate-800 to-slate-900 font-sans bg-[#040B20]">

  <!-- Стрелка-назад в левом верхнем углу -->
  <div class="arrow-back" id="globalArrowBack" style="display: flex;">
    <svg viewBox="0 0 24 24" fill="none">
      <path d="M15 19L8 12L15 5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    </svg>
    <span style="font-size:15px" id="backBtnText">Вернуться</span>
    <a href="/" id="arrowHomeLink" class="arrow-home" title="На главную"><i class="fa fas fa-home"></i></a>
  </div>

  <div class="container">
    <h1 class="header-title">Покупка подписки VPN</h1>
    <div class="header-desc">
      Настройте тариф и срок. Цена зависит от выбранной подписки.
    </div>
    <form id="vpn-subscription-form" method="POST" action="/pay">
      <!-- 3 блока тарифа -->
      <div class="plans mb-4" id="tariff-block-list">
        <?php
        $tariffIdx = 0;
        foreach ($TARIFFS as $key => $tariff):
          $plan = $tariff['plans']['1m'];
          ?>
          <div class="plan-card<?= $tariffIdx === 0 ? ' selected' : '' ?>" id="plan-card-<?= $tariffIdx ?>"
            data-index="<?= $tariffIdx ?>"
            style="opacity:0;animation:fade-in-up .52s cubic-bezier(.4,.75,.57,1.16) <?= 0.12 * $tariffIdx ?>s both;">
            <div class="plan-info">
              <div class="plan-title">
                <?= htmlspecialchars($tariff['title']) ?>
              </div>
              <div class="plan-period">
                <?= htmlspecialchars($plan['period']) ?>
              </div>
              <div class="plan-desc">
                <?= htmlspecialchars($tariff['desc']) ?>
              </div>
            </div>
            <div class="plan-price flex flex-col w-[100px]">
              <? if ($refer_discount > 0): ?>
                <span style="color: #c1b2ff; font-weight: 600;">
                  <?= htmlspecialchars($plan['price']) ?> ₽
                </span>
                <span class="text-gray-400 line-through text-[0.97em] font-normal ml-2">
                  <?= htmlspecialchars(floor($plan['price'] / (1 - $refer_discount / 100))) ?>
                  ₽
                </span>
              <? else: ?>
                <span class="text-gray-400 line-through text-[0.97em] font-normal ml-2">
                  <?= htmlspecialchars(floor($plan['price'] / (1 - $refer_discount / 100))) ?>
                  ₽
                </span>
              <? endif; ?>
            </div>
          </div>
          <?php $tariffIdx++; endforeach; ?>
      </div>
      <div class="plan-range-wrapper">
        <label style="font-weight:700;color:#b09afd;font-size:1.14em;letter-spacing:.06em;">Срок
          подписки:</label>
        <input type="range" id="plan-range" class="plan-range" min="0" max="3" step="1" value="0">
        <div class="plan-range-labels">
          <?php foreach ($PLAN_LABELS as $label): ?>
            <span>
              <?= htmlspecialchars($label) ?>
            </span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="pay-section flex flex-col justify-center items-center gap-2 relative">
        <label for="customerEmail" class="absolute z-10 -top-3.5 left-2 px-4 py-1">Email <span class="text-red-400">*
          </span>
        </label>
        <input class="mail-input" type="email" name="customerEmail" placeholder="noreply@coravpn.ru" id="customerEmail"
          required>

        <!-- Выбор способа оплаты -->
        <div class="payment-methods w-full mt-2">
          <div class="payment-method selected" data-method="card" onclick="selectPayment(this)">
            <i class="fa fas fa-credit-card"></i>
            <span>Банковская карта</span>
          </div>
          <div class="payment-method" data-method="sbp" onclick="selectPayment(this)">
            <img src="/public/assets/logo/payment/sbp-logo.svg" alt="СБП" style="width:70px;">
          </div>
          <div class="payment-method" data-method="sberbank" onclick="selectPayment(this)">
            <img src="/public/assets/logo/payment/sber-pay-gradient-sign-logo.svg" alt="SberPay" style="width:45px;">
            <span>SberPay</span>
          </div>
        </div>

        <div class="flex flex-col justify-center items-center w-full">
          <?php if ($verefy_free_client)
            echo '<span class="pay-desc font-[0.92em] mb-4 text-xs">После оплаты бесплатная подписка станет
						недоступна (Даже после нажатия на кнопку: <font class="text-white">оплатить</font>)!</span>';
          ?>
          <button type="submit" class="pay-btn h-12 relative flex gap-2 justify-center items-center" id="pay-btn">
            <img style="transform: translateY(1px)" class="w-[100px]"
              src="/public/assets/logo/yoookassa/iomoney_fill_white.svg">
            <span class="w-[1px] h-5 bg-white" style="transform: translateY(-1px)"></span> Оплатить
          </button>
          <a href="/" class="back-btn flex justify-center items-center gap-2">
            <i class="fa fas fa-door-open text-lg"></i>
            Вернуться</a>
        </div>
        <span class="pay-desc font-[0.92em] mb-4 text-xs">После оплаты вы получите оффициальную справку об
          услуге на указанный email, а так же не забудьте вернуться в магазин после оплаты! Пожалуйста,
          ознакомтесь
          с
          нашей
          <a href='/politic' class="text-white underline">политикой конфидициальности</a></span>
      </div>
      <!-- traker check post form -->
      <input type="hidden" name="payment_data">
      <!-- traker check post form -->
      <input type="hidden" name="payment_method" id="selected-method" value="card">
      <input type="hidden" name="tariff" id="selected-tariff" value="basic">
      <input type="hidden" name="period" id="selected-period" value="1m">
    </form>
  </div>
