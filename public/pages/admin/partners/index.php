<?php
use App\Config\Database;
use App\Models\Network\Message;
use App\Models\Network\Network;
use Setting\Route\Function\Functions;
use App\Config\Session;

Session::init();

// Получаем текущего авторизованного клиента-админа по сессии
$functions = new Functions();
$sessionClientId = $_SESSION['client'] ?? 0;
$adminClient = $functions->client($sessionClientId);

// --- Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
  $admin_action = $_POST['admin_action'];

  switch ($admin_action) {
    // Другие действия администратора могут быть добавлены здесь
  }
}

// Получаем выбранный месяц и год из GET параметров
$currentYear = date('Y');
$currentMonth = date('n');
$selected_month = $_GET['month'] ?? $currentMonth;
$selected_year = $_GET['year'] ?? $currentYear;

// --- GET DATA DATABASE ---
// Получаем всех пользователей, которые являются рефералами (у них есть refer_link)
$referrers = Database::send('SELECT DISTINCT refer_link FROM vpn_users WHERE refer_link != "" AND refer_link IS NOT NULL');

$partners_data = [];
$total_partners = 0;
$total_referrals = 0;
$total_revenue = 0;

if (is_array($referrers)) {
  foreach ($referrers as $referrer) {
    $refer_link = $referrer['refer_link'];

    // Получаем информацию о партнере (реферере)
    $partner_info = Database::send('SELECT * FROM vpn_users WHERE my_refer_link = ?', [$refer_link]);

    if (is_array($partner_info) && !empty($partner_info[0])) {
      $partner = $partner_info[0];
      $partner_id = $partner['tg_id'];

      // Получаем всех рефералов этого партнера
      $referrals = Database::send('SELECT * FROM vpn_users WHERE refer_link = ?', [$refer_link]);

      if (is_array($referrals)) {
        $referral_count = count($referrals);
        $partner_revenue = 0;

        // Рассчитываем выручку от рефералов (сумма всех оплаченных подписок)
        foreach ($referrals as $referral) {
          $amount = floatval($referral['vpn_amount'] ?? 0);
          if ($amount > 0) {
            // Доход в чистом виде - полная сумма оплаченной подписки
            $partner_revenue += $amount;
          }
        }

        // Получаем ежемесячный доход за выбранный месяц
        $monthlyRevenue = Functions::getPartnerMonthlyRevenue($partner['tg_id'], $selected_year, $selected_month);
        $current_month_revenue = $monthlyRevenue ? floatval($monthlyRevenue['revenue_amount']) : 0;

        // Получаем историю доходов партнера
        $revenue_history = Functions::getPartnerRevenueHistory($partner['tg_id']);

        $partners_data[] = [
          'partner' => $partner,
          'referrals' => $referrals,
          'referral_count' => $referral_count,
          'revenue' => $partner_revenue,
          'current_month_revenue' => $current_month_revenue,
          'revenue_history' => $revenue_history
        ];

        $total_partners++;
        $total_referrals += $referral_count;
        $total_revenue += $partner_revenue;
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoraVPN | Партнеры</title>
  <link href="https://fonts.cdnfonts.com/css/inter" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        fontFamily: {
          'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
        },
        extend: {
          colors: {
            primary: '#6366f1',
            accent: '#0ea5e9',
            danger: '#ef4444',
            success: '#22c55e',
            background: '#17181c',
            card: '#191a1f',
            border: '#23242b',
            muted: '#26272E'
          },
          boxShadow: {
            card: 'none'
          }
        }
      }
    }
  </script>
  <style>
    html,
    body {
      overflow-x: hidden;
    }

    ::-webkit-scrollbar {
      width: 0;
    }

    .transition-bg {
      transition: background 0.2s;
    }

    /* Theme Switch: Light Mode */
    :root {
      --bg-background: #fff;
      --bg-card: #f9fafb;
      --bg-muted: #f1f5f9;
      --border-border: #e5e7eb;
      --text-main: #23272e;
      --text-muted: #575b6d;
      --text-primary: #6366f1;
    }

    .dark {
      --bg-background: #17181c;
      --bg-card: #191a1f;
      --bg-muted: #26272E;
      --border-border: #23242b;
      --text-main: #fff;
      --text-muted: #9ca3af;
      --text-primary: #6366f1;
    }

    body,
    .bg-background {
      background-color: var(--bg-background) !important;
    }

    .bg-card {
      background-color: var(--bg-card) !important;
    }

    .bg-muted {
      background-color: var(--bg-muted) !important;
    }

    .border-border {
      border-color: var(--border-border) !important;
    }

    .text-white,
    .text-black,
    .text-gray-400,
    .text-gray-200,
    .text-gray-100,
    .text-gray-300,
    .text-white\/80 {
      color: var(--text-main) !important;
    }

    .text-primary {
      color: var(--text-primary) !important;
    }

    .text-success {
      color: #22c55e !important;
    }

    .text-danger {
      color: #ef4444 !important;
    }

    .text-accent {
      color: #0ea5e9 !important;
    }

    .bg-success\/10 {
      background-color: rgba(34, 197, 94, 0.10) !important;
    }

    .bg-success\/20 {
      background-color: rgba(34, 197, 94, 0.20) !important;
    }

    .bg-danger\/10 {
      background-color: rgba(239, 68, 68, 0.10) !important;
    }

    .bg-danger\/15 {
      background-color: rgba(239, 68, 68, 0.15) !important;
    }

    .bg-danger\/20 {
      background-color: rgba(239, 68, 68, 0.20) !important;
    }

    .bg-primary\/10 {
      background-color: rgba(99, 102, 241, 0.10) !important;
    }

    .bg-primary\/20 {
      background-color: rgba(99, 102, 241, 0.20) !important;
    }

    .bg-primary\/25 {
      background-color: rgba(99, 102, 241, 0.25) !important;
    }

    .bg-accent\/10 {
      background-color: rgba(14, 165, 233, 0.10) !important;
    }

    .bg-accent\/15 {
      background-color: rgba(14, 165, 233, 0.15) !important;
    }

    /* Lighter border for light mode*/
    .light .border-border {
      border-color: var(--border-border) !important;
    }

    /* Remove any box shadows (тени) globally */
    .shadow,
    .shadow-card,
    .shadow-lg,
    .shadow-md,
    .shadow-sm,
    .shadow-xl,
    .shadow-2xl,
    [class*="shadow"] {
      box-shadow: none !important;
    }
  </style>
</head>

<body class="min-h-screen font-sans transition-bg bg-background text-white">
  <!-- Header bar for mobile/tablet/desktop -->
  <header
    class="w-full sticky top-0 z-40 bg-card border-b border-border flex items-center justify-between px-4 sm:px-8 py-3">
    <div class="flex items-center gap-2">
      <button id="nav-toggle" class="sm:hidden block text-primary hover:text-accent p-2 -ml-2 focus:outline-none"><i
          data-feather="menu" class="w-6 h-6"></i></button>
      <span
        class="font-bold text-xl tracking-tight bg-gradient-to-tr from-fuchsia-600 via-primary to-accent text-transparent bg-clip-text select-none drop-shadow-[0_1px_5px_rgba(99,102,241,0.25)]">
        <span
          class="font-extrabold tracking-widest bg-gradient-to-tr from-primary via-accent to-fuchsia-500 text-transparent bg-clip-text">
          CoraVPN
        </span>
        <span
          class="ml-2 px-2 py-0.5 rounded bg-green-500/15 text-green-500 font-semibold text-xs tracking-wide drop-shadow-[0_1px_5px_rgba(34,197,94,0.20)] align-super animate-pulse">ADMIN</span>
      </span>
    </div>
    <div class="flex items-center gap-3">
      <button id="theme-toggle"
        class="rounded-full w-9 h-9 flex items-center justify-center transition hover:bg-accent/20 border border-border"
        aria-label="Сменить тему"><i data-feather="moon" id="theme-icon" class="w-5 h-5 text-primary"></i></button>
      <span class="text-gray-400 hidden sm:inline text-sm">Партнеры</span>
    </div>
  </header>

  <!-- Mobile nav drawer -->
  <nav id="mobile-nav"
    class="fixed h-full inset-0 bg-black/70 z-50 flex sm:hidden items-start pointer-events-none opacity-0 transition-opacity duration-200">
    <div
      class="w-64 bg-card h-full border-r border-border flex flex-col pt-8 px-5 gap-2 pointer-events-auto -translate-x-72 transition-transform duration-200"
      id="mobile-menu">
      <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>"
        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200 transition">
        <i class="fas fa-street-view"></i> Доска управления</a>
      <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/table"
        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200 transition">
        <i class="fas fa-table"></i> Таблица данных</a>
      <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/partners"
        class="flex items-center gap-3 px-2 py-2 rounded-lg font-bold bg-gradient-to-br from-primary to-white text-transparent bg-clip-text tracking-tight hover:bg-white transition">
        <i class="fas fa-user-friends"></i> Партнеры</a>
      <a href="/profile" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200">
        <i class="fas fa-long-arrow-alt-left"></i>Выйти</a>
      <div class="border-t border-border mt-8 pt-5 text-xs text-gray-500">©
        <script>document.write(new Date().getFullYear());</script> СoraVPN
      </div>
    </div>
  </nav>

  <?php
  // Использовать Message::controll() для получения системного уведомления из сессии
  $notification = Message::controll();
  $toastType = '';
  $toastMsg = '';

  if (!empty($notification['type']) && !empty($notification['message'])) {
    if ($notification['type'] === 'success') {
      $toastType = 'success';
    } elseif ($notification['type'] === 'error') {
      $toastType = 'error';
    }
    $toastMsg = htmlspecialchars($notification['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
  ?>
  <?php if ($toastMsg): ?>
    <div id="admin-toast" class="fixed top-5 right-6 z-[9999] shadow-2xl flex items-start gap-2 px-5 py-4 rounded-xl border text-base font-semibold
      <?= $toastType === 'success'
        ? 'bg-success/95 border-success text-white'
        : 'bg-danger/95 border-danger text-white'
        ?>
    "
      style="display:none; min-width:220px; max-width:350px; pointer-events:auto; animation:toast-pop 0.3s cubic-bezier(.2,.8,.2,1);">
      <span>
        <?php if ($toastType === 'success'): ?>
          <i data-feather="check-circle" class="mr-2"></i>
        <?php else: ?>
          <i data-feather="alert-triangle" class="mr-2"></i>
        <?php endif; ?>
      </span>
      <span class="flex-1">
        <?= $toastMsg ?>
      </span>
      <button onclick="closeToast()"
        class="ml-2 bg-transparent border-0 p-0 pointer-events-auto hover:opacity-70 focus:outline-none">
        <i data-feather="x"></i>
      </button>
    </div>
    <style>
      @keyframes toast-pop {
        0% {
          transform: translateY(-40px) scale(0.94);
          opacity: 0;
        }

        100% {
          transform: translateY(0) scale(1);
          opacity: 1;
        }
      }

      #admin-toast {
        box-shadow: 0 8px 32px 0 rgba(34, 197, 94, 0.25), 0 1.5px 8px 0 rgba(239, 68, 68, 0.10);
        transition: opacity 0.2s, transform 0.2s;
      }
    </style>
    <script>
      function showToast() {
        const toast = document.getElementById('admin-toast');
        if (toast) {
          toast.style.display = 'flex';
          feather.replace(); // update icons
          setTimeout(() => {
            if (toast) toast.style.opacity = "1";
          }, 10);
          // Hide after 4 seconds
          setTimeout(() => {
            if (toast) toast.style.opacity = "0";
          }, 8000);
          setTimeout(() => {
            if (toast) toast.style.display = 'none';
          }, 9200);
        }
      }
      function closeToast() {
        const toast = document.getElementById('admin-toast');
        if (toast) {
          toast.style.opacity = "0";
          setTimeout(() => { toast.style.display = 'none'; }, 220);
        }
      }
      window.addEventListener('DOMContentLoaded', showToast);
    </script>
  <?php endif; ?>

  <!-- content -->
  <div class="flex min-h-[calc(100dvh-56px)] transition-bg bg-background">

    <!-- Sidebar (desktop/tablet) -->
    <aside
      class="hidden sm:flex min-w-[220px] max-w-[270px] bg-card flex-col h-[calc(100dvh-56px)] border-r border-border sticky top-[56px] z-30">
      <nav class="py-6 px-4 flex-1 flex flex-col gap-2">
        <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>"
          class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200 transition">
          <i class="fas fa-street-view"></i> Доска управления</a>
        <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/table"
          class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200 transition">
          <i class="fas fa-table"></i> Таблица данных</a>
        <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/partners"
          class="flex items-center gap-3 px-2 py-2 rounded-lg font-bold text-primary tracking-tight hover:text-primary-800 transition">
          <i class="fas fa-user-friends"></i> Партнеры</a>
        <a href="/profile" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200">
          <i class="fas fa-long-arrow-alt-left"></i>Выйти</a>
      </nav>
      <div class="px-5 pb-5 mt-auto pt-2">
        <p class="text-xs text-gray-400">©
          <script>document.write(new Date().getFullYear());</script> CoraVPN
        </p>
      </div>
    </aside>
    <main class="flex-1 px-2 sm:px-6 py-8 flex flex-col gap-8 mx-auto max-w-[1350px]">

      <!-- Title -->
      <header>
        <h1 class="text-2xl sm:text-4xl font-bold mb-1 tracking-tight text-primary-400 tracking-tight">
          Управление партнерами CoraVPN</h1>
        <div id="action-result" style="display:none;"
          class="mt-2 rounded-xl border border-success bg-success/10 px-4 py-2 text-success flex gap-2 items-center text-base font-medium transition">
        </div>
      </header>

      <!-- Общая статистика -->
      <section class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-5">
        <div class="rounded-2xl p-5 flex flex-col items-center bg-card border border-border w-full">
          <span
            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary/20 text-primary text-lg mb-2"><i
              data-feather="users"></i></span>
          <span class="text-xl font-extrabold" id="stat-partners">
            <?= $total_partners; ?>
          </span>
          <span class="text-xs mt-1 text-gray-300 text-center">Активные партнеры</span>
        </div>
        <div class="rounded-2xl p-5 flex flex-col items-center bg-card border border-border w-full">
          <span
            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-400/25 text-green-500 text-lg mb-2"><i
              data-feather="user-plus"></i></span>
          <span class="text-xl font-extrabold" id="stat-referrals">
            <?= $total_referrals; ?>
          </span>
          <span class="text-xs mt-1 text-gray-400">Всего рефералов</span>
        </div>
        <div class="rounded-2xl p-5 flex flex-col items-center bg-card border border-border w-full">
          <span
            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-400/25 text-emerald-500 text-lg mb-2"><i
              data-feather="dollar-sign"></i></span>
          <span class="text-xl font-extrabold" id="stat-revenue">
            <?= number_format($total_revenue, 2, '.', ' '); ?> ₽
          </span>
          <span class="text-xs mt-1 text-emerald-300">Общая сумма рефералов</span>
        </div>
      </section>

      <!-- Ежемесячная статистика -->
      <section class="bg-card border border-border rounded-2xl p-4 sm:p-6 w-full max-w-full">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
          <h3 class="text-xl font-bold flex items-center gap-2">
            <i data-feather="calendar" class="w-5 h-5"></i>
            Ежемесячные доходы
          </h3>

          <!-- Селектор месяца и года -->
          <div class="flex gap-2">
            <select id="month-select"
              class="px-3 py-2 bg-muted border border-border rounded-lg text-sm text-white focus:outline-none focus:ring-2 focus:ring-accent">
              <?php
              $months = [
                1 => 'Январь',
                2 => 'Февраль',
                3 => 'Март',
                4 => 'Апрель',
                5 => 'Май',
                6 => 'Июнь',
                7 => 'Июль',
                8 => 'Август',
                9 => 'Сентябрь',
                10 => 'Октябрь',
                11 => 'Ноябрь',
                12 => 'Декабрь'
              ];

              $current_month = date('n');
              $current_year = date('Y');
              $selected_month = $_GET['month'] ?? $current_month;
              $selected_year = $_GET['year'] ?? $current_year;

              foreach ($months as $num => $name) {
                $selected = ($num == $selected_month) ? 'selected' : '';
                echo "<option value=\"$num\" $selected>$name</option>";
              }
              ?>
            </select>

            <select id="year-select"
              class="px-3 py-2 bg-muted border border-border rounded-lg text-sm text-white focus:outline-none focus:ring-2 focus:ring-accent">
              <?php
              for ($year = $current_year; $year >= 2023; $year--) {
                $selected = ($year == $selected_year) ? 'selected' : '';
                echo "<option value=\"$year\" $selected>$year</option>";
              }
              ?>
            </select>

            <button onclick="loadMonthlyData()"
              class="px-4 py-2 bg-accent hover:bg-accent/90 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
              <i data-feather="search" class="w-4 h-4"></i>
              Показать
            </button>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="rounded-2xl p-5 flex flex-col items-center bg-card border border-border">
            <span
              class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-accent/25 text-accent text-lg mb-2"><i
                data-feather="trending-up"></i></span>
            <span class="text-xl font-extrabold" id="stat-monthly-revenue">
              <?= number_format(array_sum(array_column($partners_data, 'current_month_revenue')), 2, '.', ' '); ?> ₽
            </span>
            <span class="text-xs mt-1 text-accent">
              Доход за <?= $months[$selected_month] ?> <?= $selected_year ?>
            </span>
          </div>
        </div>
      </section>

      <!-- Партнеры - Таблица -->
      <section class="bg-card border border-border rounded-2xl p-4 sm:p-6 w-full max-w-full">
        <div class="flex flex-col sm:flex-row justify-between gap-4 sm:items-center mb-2">
          <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
            Список партнеров и их рефералы</h2>
        </div>

        <!-- Легенда -->
        <div class="bg-muted/30 rounded-lg p-3 mb-4">
          <h4 class="text-sm font-semibold text-gray-300 mb-2">Пояснения:</h4>
          <div class="flex flex-wrap gap-4 text-xs">
            <div class="flex items-center gap-2">
              <span class="text-amber-500 font-medium">*до реф.</span>
              <span class="text-gray-400">- реферал оплатил до активации партнерской ссылки</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-accent font-medium">Доход за выбранный месяц</span>
              <span class="text-gray-400">- доход от рефералов за выбранный период</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-primary font-medium">Выбор периода</span>
              <span class="text-gray-400">- используйте селекторы для просмотра данных за любой месяц</span>
            </div>
          </div>
        </div>

        <?php if (!empty($partners_data)): ?>
          <?php foreach ($partners_data as $partner_data): ?>
            <?php
            $partner = $partner_data['partner'];
            $referrals = $partner_data['referrals'];
            $referral_count = $partner_data['referral_count'];
            $revenue = $partner_data['revenue'];
            ?>

            <!-- Карточка партнера -->
            <div class="mb-6 border border-border rounded-xl p-4 bg-muted/30">
              <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-4">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                    <i data-feather="user" class="w-6 h-6 text-primary"></i>
                  </div>
                  <div>
                    <h3 class="text-lg font-bold text-white">
                      <?= htmlspecialchars($partner['tg_first_name'] . ' ' . $partner['tg_last_name']) ?>
                    </h3>
                    <p class="text-sm text-gray-400">
                      ID:
                      <?= htmlspecialchars($partner['tg_id']) ?>
                      <?php if (!empty($partner['tg_username'])): ?>
                        (@
                        <?= htmlspecialchars($partner['tg_username']) ?>)
                      <?php endif; ?>
                    </p>
                  </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                  <div class="text-center">
                    <p class="text-xs text-gray-400">Рефералов</p>
                    <p class="text-lg font-bold text-primary">
                      <?= $referral_count ?>
                    </p>
                  </div>
                  <div class="text-center">
                    <p class="text-xs text-gray-400">Доход за все время</p>
                    <p class="text-lg font-bold text-emerald-500"><?= number_format($revenue, 2, '.', ' ') ?> </p>
                  </div>
                  <div class="text-center">
                    <p class="text-xs text-gray-400">Доход за <?= $months[$selected_month] ?></p>
                    <p class="text-lg font-bold text-accent"><?= number_format($current_month_revenue, 2, '.', ' ') ?> </p>
                  </div>
                  <div class="text-center">
                    <p class="text-xs text-gray-400">Реф. ссылка</p>
                    <p class="text-sm font-mono text-accent">
                      <?= htmlspecialchars($partner['my_refer_link']) ?>
                    </p>
                  </div>
                </div>
              </div>

              <!-- Таблица рефералов партнера -->
              <?php if (!empty($referrals)): ?>
                <div class="mt-4">
                  <h4 class="text-md font-semibold text-gray-300 mb-3">Привлеченные рефералы (
                    <?= count($referrals) ?>)
                  </h4>
                  <div class="overflow-x-auto">
                    <table class="min-w-full border-separate border-spacing-y-1 text-sm">
                      <thead>
                        <tr>
                          <th class="px-3 py-2 text-left text-xs font-bold bg-background border-b border-border text-primary">
                            ID</th>
                          <th class="px-3 py-2 text-left text-xs font-bold bg-background border-b border-border text-primary">
                            Имя</th>
                          <th class="px-3 py-2 text-left text-xs font-bold bg-background border-b border-border text-primary">
                            Username</th>
                          <th class="px-3 py-2 text-left text-xs font-bold bg-background border-b border-border text-primary">
                            Статус</th>
                          <th class="px-3 py-2 text-left text-xs font-bold bg-background border-b border-border text-primary">
                            Сумма</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($referrals as $referral): ?>
                          <tr class="hover:bg-muted/50 transition">
                            <td class="px-3 py-2 text-xs font-mono">
                              <?= htmlspecialchars($referral['tg_id']) ?>
                            </td>
                            <td class="px-3 py-2 text-xs">
                              <?= htmlspecialchars($referral['tg_first_name'] . ' ' . $referral['tg_last_name']) ?>
                            </td>
                            <td class="px-3 py-2 text-xs font-mono">
                              <?= !empty($referral['tg_username']) ? '@' . htmlspecialchars($referral['tg_username']) : '-' ?>
                            </td>
                            <td class="px-3 py-2 text-xs">
                              <?php
                              $freekey = $referral['vpn_freekey'] ?? '';
                              if ($freekey === 'no_used') {
                                echo '<span class="text-gray-400">Без подписки</span>';
                              } elseif ($freekey === 'used') {
                                echo '<span class="text-red-500">Истекла</span>';
                              } elseif ($freekey === 'buy' || $freekey === 'used_free') {
                                echo '<span class="text-green-500">Активна</span>';
                              } else {
                                echo '<span class="text-gray-400">-</span>';
                              }
                              ?>
                            </td>
                            <td class="px-3 py-2 text-xs">
                              <?php
                              $amount = floatval($referral['vpn_amount'] ?? 0);
                              if ($amount > 0) {
                                echo '<span class="text-emerald-500">' . number_format($amount, 2, '.', ' ') . ' ₽</span>';

                                // Проверяем, была ли оплата до активации реферала
                                // Используем более надежные способы определения времени
                                $referLinkSet = !empty($referral['refer_link']);
                                $hasPayment = $amount > 0;

                                // Если у реферала есть оплата но нет реферальной ссылки, значит он оплатил до реф. программы
                                if ($hasPayment && empty($referral['refer_link'])) {
                                  echo '<br><span class="text-xs text-amber-500">*до реф.</span>';
                                }
                              } else {
                                echo '<span class="text-gray-500">Бесплатная</span>';
                              }
                              ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- История доходов партнера -->
                <?php if (!empty($revenue_history)): ?>
                  <div class="mt-6">
                    <h4 class="text-md font-semibold text-gray-300 mb-3">История доходов по месяцам</h4>
                    <div class="overflow-x-auto">
                      <table class="w-full text-sm">
                        <thead class="bg-muted/50 border-b border-border">
                          <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-300">Месяц</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-300">Год</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-300">Доход</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-300">Рефералы</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-300">Обновлено</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                          <?php foreach ($revenue_history as $history): ?>
                            <tr class="hover:bg-muted/30 transition-colors">
                              <td class="px-3 py-2 text-xs">
                                <?= date('F', mktime(0, 0, 0, $history['month'], 1)) ?>
                              </td>
                              <td class="px-3 py-2 text-xs">
                                <?= $history['year'] ?>
                              </td>
                              <td class="px-3 py-2 text-xs">
                                <span class="text-emerald-500 font-semibold">
                                  <?= number_format(floatval($history['revenue_amount']), 2, '.', ' ') ?> ₽
                                </span>
                              </td>
                              <td class="px-3 py-2 text-xs">
                                <span class="text-primary">
                                  <?= $history['referral_count'] ?>
                                </span>
                              </td>
                              <td class="px-3 py-2 text-xs text-gray-400">
                                <?= date('d.m.Y H:i', $history['updated_at']) ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                <?php endif; ?>
              <?php else: ?>
                <p class="text-gray-400 text-sm mt-3">У этого партнера пока нет привлеченных рефералов</p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center py-8">
            <i data-feather="user-friends" class="w-12 h-12 text-gray-500 mx-auto mb-4"></i>
            <p class="text-gray-400 text-lg">Партнеры не найдены</p>
            <p class="text-gray-500 text-sm mt-2">В системе пока нет пользователей с реферальными ссылками</p>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <script src="/public/pages/telegram/admin/src/main.js"></script>
  <script>
    function loadMonthlyData() {
      const month = document.getElementById('month-select').value;
      const year = document.getElementById('year-select').value;

      // Перезагружаем страницу с новыми параметрами
      const url = new URL(window.location);
      url.searchParams.set('month', month);
      url.searchParams.set('year', year);

      window.location.href = url.toString();
    }

    // Автоматически загружаем данные при изменении селектов
    document.addEventListener('DOMContentLoaded', function () {
      const monthSelect = document.getElementById('month-select');
      const yearSelect = document.getElementById('year-select');

      if (monthSelect) {
        monthSelect.addEventListener('change', function () {
          loadMonthlyData();
        });
      }

      if (yearSelect) {
        yearSelect.addEventListener('change', function () {
          loadMonthlyData();
        });
      }
    });
  </script>
</body>

</html>
