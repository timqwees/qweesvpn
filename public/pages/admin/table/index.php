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

// --- GET DATA DATABASE ---
$database = Database::send('SELECT * FROM vpn_users');

// Общее число клиентов
$client_count = is_array($database) ? count($database) : 0;

// Счётчики действующих и истёкших клиентов
$active_clients = 0;
$expired_clients = 0;
$no_used_clients = 0;

if (is_array($database)) {
  foreach ($database as $dbClient) {
    // Проверим статус подписки: если vpn_status в будущем — активна, иначе истекла
    if (isset($dbClient['vpn_freekey']) && $dbClient['vpn_freekey'] !== 'no_used') {
      $status = isset($dbClient['vpn_status']) ? intval($dbClient['vpn_status']) : 0;
      if ($status > (time() * 1000)) {
        $active_clients++;
      } else {
        $expired_clients++;
      }
    }
  }
}

// проверка без подписок
if (is_array($database)) {
  foreach ($database as $dbClient) {
    if (isset($dbClient['vpn_freekey']) && $dbClient['vpn_freekey'] == 'no_used') {
      $no_used_clients++;
    }
  }
}

// --- Actions ---
$actionResult = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
  // @routes.php (34-48) требует, чтобы route-параметры/идентификаторы приходили из реальных полей и корректно обрабатывались здесь.
  // Поэтому используем идентификатор клиента из POST, а не из $client (который из базы).
  $admin_action = $_POST['admin_action'];

  switch ($admin_action) {

    case 'remove_sub': {
      $rem_id = intval($_POST['rem_id'] ?? 0);
      if ($rem_id <= 0) {
        Message::set('error', "Некорректный tg_id клиента.");
        header("Location: /admin/{$adminClient['tg_id']}/table");
        exit();
      }
      $result = $functions->DeleteKey($rem_id);
      if (is_array($result)) {
        if (isset($result['status']) && $result['status'] === 'ok') {
          Message::set('success', "Подписка клиента #$rem_id удалена.");
        } elseif (isset($result['message'])) {
          Message::set('error', "Ошибка удаления подписки: {$result['message']}");
        } else {
          Message::set('error', "Ошибка при удалении подписки.");
        }
      } else {
        Message::set('error', "Некорректный ответ при удалении подписки.");
      }
      header("Location: /admin/{$adminClient['tg_id']}/table");
      exit();
      // break;
    }

    case 'cleanup_subs': {
      Functions::CleanUP();
      Message::set('success', "Выполнена автоочистка!");
      header("Location: /admin/{$adminClient['tg_id']}/table");
      exit();
      // break;
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['autopay'])) {
  (new Functions())->AutoPay();
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoraVPN | Admin</title>
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
      <span class="text-gray-400 hidden sm:inline text-sm">Админка</span>
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
        class="flex items-center gap-3 px-2 py-2 rounded-lg font-bold bg-gradient-to-br from-primary to-white text-transparent bg-clip-text tracking-tight hover:bg-white transition">
        <i class="fas fa-table"></i> Таблица данных</a>
      <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/partners"
        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200 transition">
        <i class="fas fa-user-friends"></i> Партнеры</a>
      <!-- СИСТЕМА АВТООПЛАТЫ -->
      <form action="" method="POST">
        <div class="flex gap-2 justify-center items-center">
          <i class="fas fa-robot text-lg"></i>
          <input name="autopay" type="submit"
            class="flex items-center gap-3 px-2 py-1 rounded-lg bg-gradient-to-r from-primary via-accent to-fuchsia-500 hover:bg-accent/20 text-[#fff] font-bold shadow-md transition cursor-pointer"
            value="Запустить автооплату">
        </div>
      </form>
      <!-- СИСТЕМА АВТООПЛАТЫ -->
      <a href="/uprefers" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200">
        <i class="fas fa-sync-alt"></i> Обновить реферные ссылки</a>
      <a href="<?= $_ENV['TEST_URL_PAGE'] ?? '' ?>"
        class="items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200 <? $_ENV['TEST_TURN'] == 'ON' ? 'flex' : 'hidden' ?>">
        <i class="fas fa-flask"></i> Тестовая часть</a>
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
      <span class="flex-1"><?= $toastMsg ?></span>
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
          class="flex items-center gap-3 px-2 py-2 rounded-lg font-bold text-primary tracking-tight hover:text-primary-800 transition">
          <i class="fas fa-table"></i> Таблица данных</a>
        <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/partners"
          class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200 transition">
          <i class="fas fa-user-friends"></i> Партнеры</a>
        <!-- СИСТЕМА АВТООПЛАТЫ -->
        <form action="" method="POST">
          <div class="flex gap-2 justify-center items-center">
            <i class="fas fa-robot text-lg"></i>
            <input name="autopay" type="submit"
              class="flex items-center gap-3 px-2 py-1 rounded-lg bg-gradient-to-r from-primary via-accent to-fuchsia-500 hover:bg-accent/20 text-[#fff] font-bold shadow-md transition cursor-pointer"
              value="Запустить автооплату">
          </div>
        </form>
        <!-- СИСТЕМА АВТООПЛАТЫ -->
        <a href="/uprefers" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200">
          <i class="fas fa-sync-alt"></i> Обновить реферные ссылки</a>
        <a href="<?= $_ENV['TEST_URL_PAGE'] ?? '' ?>"
          class="items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200 <? $_ENV['TEST_TURN'] == 'ON' ? 'flex' : 'hidden' ?>">
          <i class="fas fa-flask"></i> Тестовая часть</a>
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
          Таблица данных CoraVPN Unlimited</h1>
        <div id="action-result" style="display:none;"
          class="mt-2 rounded-xl border border-success bg-success/10 px-4 py-2 text-success flex gap-2 items-center text-base font-medium transition">
        </div>
      </header>

      <!-- Статистика -->
      <section class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-5">
        <div class="rounded-2xl p-5 flex flex-col items-center bg-card border border-border w-full">
          <span
            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary/20 text-primary text-lg mb-2"><i
              data-feather="users"></i></span>
          <span class="text-xl font-extrabold" id="stat-total">
            <?= $client_count; ?>
          </span>
          <span class="text-xs mt-1 text-gray-300 text-center">Все пользователи</span>
        </div>
        <div class="rounded-2xl p-5 flex flex-col items-center bg-card border border-border w-full">
          <span
            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-400/25 text-green-500 text-lg mb-2"><i
              data-feather="users"></i></span>
          <span class="text-xl font-extrabold" id="stat-active">
            <?= $active_clients; ?>
          </span>
          <span class="text-xs mt-1 text-gray-400">Активные</span>
        </div>
        <div class="rounded-2xl p-5 flex flex-col items-center bg-card border border-border w-full">
          <span
            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-danger/15 text-danger text-lg mb-2"><i
              data-feather="user-x"></i></span>
          <span class="text-xl font-extrabold" id="stat-expired">
            <?= $expired_clients; ?>
          </span>
          <span class="text-xs mt-1 text-gray-400">Истёкшие</span>
        </div>
        <div class="rounded-2xl p-5 flex flex-col items-center bg-card border border-border w-full">
          <span
            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-500/20 text-blue-400 text-lg mb-2"><i
              data-feather="user-minus"></i></span>
          <span class="text-xl font-extrabold" id="stat-no-used">
            <?= $no_used_clients; ?>
          </span>
          <span class="text-xs mt-1 text-blue-300">Без подписки</span>
        </div>
      </section>

      <!-- Клиенты - Таблица -->
      <section class="bg-card border border-border rounded-2xl p-4 sm:p-6 w-full max-w-full">
        <!-- Поиск клиентов -->
        <form method="GET" class="mb-4 flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
          <div class="flex-grow">
            <input type="text" name="search"
              value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
              placeholder="Поиск по ID, username, имени, фамилии или подписке..."
              class="w-full rounded-lg px-3 py-2 border border-border bg-background/60 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-sm text-gray-100"
              autocomplete="off">
          </div>
          <button type="submit"
            class="mt-2 sm:mt-0 sm:ml-3 px-4 py-2 bg-primary/90 hover:bg-primary text-white rounded-lg text-sm font-semibold transition"
            title="Найти">
            <i class="fas fa-search mr-2"></i> Найти
          </button>
          <?php if (!empty($_GET['search'])): ?>
            <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>"
              class="ml-2 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-semibold transition"
              title="Сбросить поиск">
              <i class="fas fa-times mr-1"></i> Сброс
            </a>
          <?php endif; ?>
        </form>

        <?php
        // Фильтрация данных на сервере по поисковому запросу
        $filtered_database = $database;
        if (!empty($_GET['search']) && is_array($database)) {
          $q = mb_strtolower(trim($_GET['search']));
          $filtered_database = array_filter($database, function ($client) use ($q) {
            // Собираем все искомые значения в строку
            $id = $client['tg_id'] ?? '';
            $username = $client['tg_username'] ?? '';
            $fname = $client['tg_first_name'] ?? '';
            $lname = $client['tg_last_name'] ?? '';
            $sub = $client['vpn_subscription'] ?? '';
            return (
              mb_stripos((string) $id, $q) !== false ||
              mb_stripos((string) $username, $q) !== false ||
              mb_stripos((string) $fname, $q) !== false ||
              mb_stripos((string) $lname, $q) !== false ||
              mb_stripos((string) $sub, $q) !== false
            );
          });
        }
        ?>

        <div class="flex flex-col sm:flex-row justify-between gap-4 sm:items-center mb-2">
          <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
            Список клиентов - Общий список</h2>
          <div class="flex gap-2">
            <form action="/admin/<?= $adminClient['tg_id'] ?>" method="POST">
              <input type="text" name="admin_action" class="hidden" value="cleanup_subs">
              <button type="submit"
                class="flex items-center bg-danger hover:bg-danger/90 transition px-2 py-1 rounded-lg text-xs font-semibold gap-2 focus:outline-none focus:ring-2 focus:ring-danger/50"
                title="Удалить клиентов с истёкшей подпиской">
                <i class="fas fa-trash"></i>
                <span class="sm:inline text-white">Очистить истёкших</span>
              </button>
            </form>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full border-separate border-spacing-y-1 overflow-x-auto">
            <thead>
              <tr>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  №
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  ID
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Username
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Имя
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Фамилия
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Статус
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Цена
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Автоподписка
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Дней
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary text-center">
                  Количество устройств
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Истекает
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary text-center">
                  Статус ключа
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Установленный рефер
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Использовано рефералов
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Реферная ссылка
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Подписка
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  IP (CID)
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  Устройство (CID)
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary">
                  ОС (CID)
                </th>
                <th
                  class="px-3 py-2 text-left text-xs sm:text-sm font-bold bg-background border-b border-border text-primary text-center">
                  Действие
                </th>
              </tr>
            </thead>
            <tbody id="client-list" class="text-[13px] sm:text-sm font-mono">
              <?php
              $clients_array = is_array($filtered_database) ? array_values($filtered_database) : [];
              ?>
              <?php if (count($clients_array) > 0): ?>
                <?php foreach ($clients_array as $index => $table_client): ?>
                  <tr>
                    <!-- номер -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?= $index + 1 ?>
                    </td>
                    <!-- id -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?= !empty($table_client['tg_id']) ? htmlspecialchars($table_client['tg_id']) : '-' ?>
                    </td>
                    <!-- username -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?= !empty($table_client['tg_username']) ? htmlspecialchars($table_client['tg_username']) : '-' ?>
                    </td>
                    <!-- Имя -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?= !empty($table_client['tg_first_name']) ? htmlspecialchars($table_client['tg_first_name']) : '-' ?>
                    </td>
                    <!-- Фамилия -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?= !empty($table_client['tg_last_name']) ? htmlspecialchars($table_client['tg_last_name']) : '-' ?>
                    </td>
                    <!-- Статус -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?php
                      $freekey = $table_client['vpn_freekey'] ?? '';
                      if ($freekey === 'no_used') {
                        echo '<span class="text-gray-400">Без подписки</span>';
                      } elseif ($freekey === 'used') {
                        echo '<span class="text-red-500">Истекла/Отменена</span>';
                      } elseif ($freekey === 'buy' || $freekey === 'used_free') {
                        echo '<span class="text-green-500">Активна</span>';
                      } else {
                        echo '<span class="text-gray-400">-</span>';
                      }
                      ?>
                    </td>
                    <!-- Цена -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <!-- vpn_amount = 0 => бесплатная -->
                      <?php
                      $amount = $table_client['vpn_amount'] ?? '';
                      if ($amount > 0) {
                        echo number_format($amount, 2, '.', ' ') . PHP_EOL . '₽';
                      } else {
                        echo '<span class="text-gray-500">Бесплатная</span>';
                      }
                      ?>
                    </td>
                    <!-- Автоподписка -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <!--
                       yk_autopay_active = 0 => неактивна
                       yk_autopay_active = 1 => активна -->
                      <?php
                      $amount = $table_client['yk_autopay_active'] ?? '';
                      if ($amount === 0) {
                        echo '<span class="text-red-400">Не подключено</span>';
                      } else {
                        echo '<span class="text-green-400">Подключено</span>';
                      }
                      ?>
                    </td>
                    <!-- Дней -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?= htmlspecialchars($table_client['vpn_date_count'] ?? '') ?>
                    </td>
                    <!-- Количество устройств -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <span class="flex justify-center items-center gap-2">
                        <?php
                        $divice = $table_client['vpn_divece_count'] ?? '';
                        if ($divice === '' || is_null($divice)) {
                          echo '<span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span><span class="text-red-500"></span>';
                        } elseif ((int) $divice === 0) {
                          echo '<span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span><span class="text-green-500">Безлимитный</span>';
                        } else {
                          echo '<span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span><span class="text-green-500"> ' . htmlspecialchars((int) $divice) . '</span>';
                        }
                        ?>
                      </span>
                    </td>
                    <!-- Истекает -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?php
                      if (!empty($table_client['vpn_status'])) {
                        $date = intval($table_client['vpn_status']) / 1000;
                        echo date("d.m.Y H:i", $date);
                      } else {
                        echo "-";
                      }
                      ?>
                    </td>
                    <!-- Статус ключа -->
                    <td class="px-3 py-2 text-center">
                      <?php
                      $freekey = $table_client['vpn_freekey'] ?? '';
                      if (in_array($freekey, ['used_free', 'buy'])): ?>
                        <span class="text-emerald-500">
                          <!-- активные -->
                          <?= htmlspecialchars($freekey); ?>
                        </span>
                      <?php else: ?>
                        <span class="text-red-500">
                          <!-- не активные -->
                          <?= htmlspecialchars($freekey); ?>
                        </span>
                      <?php endif; ?>
                    </td>
                    <!-- Установленный рефер -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?php
                      if (!empty($table_client['refer_link'])) {
                        $refer = htmlspecialchars($table_client['refer_link']);
                        echo '<span class="text-white flex items-center gap-1"><i class="fas fa-user-friends text-emerald-400"></i>' . $refer . '</span>';
                      } else {
                        echo '<span class="text-gray-400">—</span>';
                      }
                      ?>
                    </td>
                    <!-- Использовано рефералов -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?php
                      $my_refer_count = !empty($table_client['my_refer_count']) ? intval($table_client['my_refer_count']) : 0;
                      if ($my_refer_count > 0) {
                        echo '<span class="text-green-500">' . $my_refer_count . '</span>';
                      } else {
                        echo '<span class="text-red-500">0</span>';
                      }
                      ?>
                    </td>
                    <!-- Реферная ссылка -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?php
                      if (!empty($table_client['my_refer_link'])) {
                        $ref_url = htmlspecialchars($table_client['my_refer_link']);
                        echo '<span class="text-[#615ced] whitespace-nowrap rounded-lg px-2 py-1 flex items-center gap-1"><i class="fas fa-link text-indigo-400"></i>' . $ref_url . '</span>';
                      } else {
                        echo '<span class="text-gray-400">—</span>';
                      }
                      ?>
                    </td>
                    <!-- Подписка -->
                    <td class="px-3 py-2 flex gap-2">
                      <?php $subscribe = $table_client['vpn_subscription'] ?? ''; ?>
                      <?php if (!empty($subscribe)): ?>
                        <button onclick="
                            navigator.clipboard.writeText('<?= htmlspecialchars($subscribe) ?>').then(() => {
                              this.classList.add('ring-emerald-500');
                              showCopyStatus(this, 'Скопировано!', true);
                              setTimeout(() => this.classList.remove('ring-emerald-500'), 900);
                            }).catch(() => {
                              this.classList.add('ring-red-500');
                              showCopyStatus(this, 'Ошибка копирования', false);
                              setTimeout(() => this.classList.remove('ring-red-500'), 900);
                            });
                          "
                          class="copy-sub-btn transition rounded-lg focus:outline-none flex items-center gap-2 bg-transparent relative"
                          title="Скопировать подписку" type="button">
                          <code
                            class="bg-[#efeeff] text-[#615ced] whitespace-nowrap rounded-lg px-2 py-1"><?= htmlspecialchars($subscribe) ?></code>
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <title>Copy</title>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 16h8a2 2 0 002-2V8m-2-4H8a2 2 0 00-2 2v8h2a2 2 0 012 2v2h8a2 2 0 002-2V8a2 2 0 00-2-2z" />
                          </svg>
                          <span
                            class="copy-status-message absolute bottom-8 left-1/2 translate-x-1/2 bg-gray-700 text-xs text-white rounded px-2 py-1 opacity-0 pointer-events-none transition duration-300 whitespace-nowrap z-50"></span>
                        </button>
                        <a href="<?= htmlspecialchars($subscribe) ?>" target="_blank" rel="noopener noreferrer"
                          class="ml-2 inline-flex items-center" title="Открыть подписку">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <title>Open</title>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M14 3h7m0 0v7m0-7L10 14M5 7v10a2 2 0 002 2h10a2 2 0 002-2V9" />
                          </svg>
                        </a>
                        <script>
                          function showCopyStatus(btn, message, success) {
                            let status = btn.querySelector('.copy-status-message');
                            if (status) {
                              status.textContent = message;
                              status.classList.remove('opacity-0');
                              status.style.opacity = 1;
                              status.style.color = success ? '#34D399' : '#f87171'; // emerald/red
                              setTimeout(() => {
                                if (status) {
                                  status.classList.add('opacity-0');
                                  status.style.opacity = 0;
                                }
                              }, 1200);
                            }
                          }
                        </script>
                      <?php else: ?>
                        <span class="text-gray-400">-</span>
                      <?php endif; ?>
                    </td>
                    <!-- IP (CID) -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?php
                      $ip = $table_client['cid_ip'] ?? '';
                      if ($ip === '' || $ip === null) {
                        echo '<span class="text-gray-400 flex items-center gap-1">-</span>';
                      } else {
                        echo '<span class="text-blue-500">' . htmlspecialchars($ip) . '</span>';
                      }
                      ?>
                    </td>
                    <!-- Устройство (CID) -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?php
                      $device = $table_client['cid_device_type'] ?? '';
                      if ($device === '' || $device === null) {
                        echo '<span class="text-gray-400 flex items-center gap-1">-</span>';
                      } else {
                        echo '<span class="text-amber-500">' . htmlspecialchars($device) . '</span>';
                      }
                      ?>
                    </td>
                    <!-- ОС (CID) -->
                    <td class="px-3 py-2 text-xs font-mono">
                      <?php
                      $os = $table_client['cid_os'] ?? '';
                      if ($os === '' || $os === null) {
                        echo '<span class="text-gray-400 flex items-center gap-1">-</span>';
                      } else {
                        echo '<span class="text-green-500">' . htmlspecialchars($os) . '</span>';
                      }
                      ?>
                    </td>
                    <!-- Удалить -->
                    <td class="px-3 py-2 text-center">
                      <?php
                      $status = isset($table_client['vpn_status']) ? intval($table_client['vpn_status']) : 0;
                      $modal_key = 'delete_key_' . htmlspecialchars($table_client['tg_id'] ?? '');
                      if ($status > (time() * 1000)): ?>
                        <button class="ml-2 text-danger hover:text-danger/80" title="Удалить пользователя"
                          onclick="document.getElementById('<?= $modal_key ?>').showModal(); event.stopPropagation(); return false;">
                          <i class="fas fa-trash"></i>
                          <span class="sr-only">Удалить</span>
                        </button>
                        <dialog id="<?= $modal_key ?>" class="w-full h-full bg-black/80" onclick="this.close();">
                          <div class="flex justify-center items-center">
                            <form method="POST" action="/admin/<?= $adminClient['tg_id']; ?>" style="pointer-events: auto;"
                              onclick="event.stopPropagation();">
                              <input type="hidden" name="rem_id"
                                value="<?= htmlspecialchars($table_client['tg_id'] ?? '') ?>">
                              <input type="hidden" name="admin_action" value="remove_sub">
                              <div class="flex justify-center items-center h-full w-full">
                                <div
                                  class="bg-[#181818] rounded-xl shadow-lg p-8 max-w-sm flex flex-col items-center justify-center gap-6 relative">
                                  <button type="button" onclick="document.getElementById('<?= $modal_key ?>').close();"
                                    class="absolute top-4 right-4 text-gray-400 hover:text-white text-2xl" title="Закрыть">
                                    &times;
                                  </button>
                                  <div class="flex flex-col items-center gap-2 w-full">
                                    <i class="fa fa-exclamation-triangle text-4xl text-red-400 mb-2"></i>
                                    <h3 class="text-lg font-bold text-white text-center">
                                      Подтвердите
                                      удаление подписки</h3>
                                    <p class="text-gray-300 text-center text-sm w-full">
                                      Вы действительно хотите удалить VPN-подписку? Действие
                                      необратимо.
                                      После подтверждения подписка будет немедленно отключена.
                                    </p>
                                  </div>
                                  <div class="flex w-full gap-4 mt-2">
                                    <button type="button" onclick="document.getElementById('<?= $modal_key ?>').close();"
                                      class="flex-1 text-gray-200 flex justify-center items-center
            rounded-lg gap-2 w-full bg-[#303030] hover:bg-[#3F3F48] transition py-[15px]">
                                      <i class="fa fa-times"></i>
                                      <span>Отмена</span>
                                    </button>
                                    <button type="submit"
                                      class="flex-1 text-white bg-red-500 hover:bg-red-600 transition flex justify-center items-center rounded-lg gap-2 w-full">
                                      <i class="fa fa-trash"></i>
                                      <span>Удалить</span>
                                    </button>
                                  </div>
                                </div>
                              </div>
                            </form>
                          </div>
                        </dialog>
                      <?php else: ?>
                        <div type="submit" class="ml-2 text-danger hover:text-danger/80 opacity-[0.5]">
                          <i class="fas fa-trash"></i>
                          <span class="sr-only">Удалить</span>
                        </div>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center px-3 py-2 text-gray-400">
                    Нет данных для отображения.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <script src="/public/pages/telegram/admin/src/main.js"></script>
</body>

</html>
