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
  $admin_action = $_POST['admin_action'];

  switch ($admin_action) {
    case 'give_sub': {
      $client_id = intval($_POST['client_id'] ?? 0);
      $days = intval($_POST['give_days'] ?? 7);
      $divece_limit = intval($_POST['give_divece_limit'] ?? 1);
      // Проверяем, что client_id корректен
      if ($client_id <= 0) {
        Message::set('error', "Некорректный tg_id клиента.");
        // Лог ошибки
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf("[%s] [ERROR] give_sub: Некорректный tg_id клиента (%s) (admin %s)\n", date('Y-m-d H:i:s'), $client_id, $adminClient['tg_id'] ?? '-'),
          FILE_APPEND
        );
        header("Location: /admin/{$adminClient['tg_id']}");
        exit();
      }

      // Ищем пользователя в базе
      $user_row = Database::send('SELECT tg_username FROM vpn_users WHERE tg_id = ?', [strval($client_id)]);
      if (!is_array($user_row) || empty($user_row[0]) || empty($user_row[0]['tg_username'])) {
        Message::set('error', "Клиент с tg_id #$client_id не найден в базе.");
        // Лог ошибки
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf("[%s] [ERROR] give_sub: Клиент с tg_id %s не найден в базе (admin %s)\n", date('Y-m-d H:i:s'), $client_id, $adminClient['tg_id'] ?? '-'),
          FILE_APPEND
        );
        header("Location: /admin/{$adminClient['tg_id']}");
        exit();
      }

      $username = strval($user_row[0]['tg_username']);

      $result = $functions->add_client($days, $username, $divece_limit);//array

      if (!is_array($result) || empty($result['vpn_subscription']) || empty($result['vpn_status']) || empty($result['vpn_uuid'])) {
        Message::set('error', 'Не удалось создать клиента VPN');
        // Лог ошибки
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf(
            "[%s] [ERROR] give_sub: Не удалось создать клиента VPN для tg_id %s, username '%s' (admin %s)\n",
            date('Y-m-d H:i:s'),
            $client_id,
            $username,
            $adminClient['tg_id'] ?? '-'
          ),
          FILE_APPEND
        );
        header("Location: /admin/{$adminClient['tg_id']}");
        exit();
      }

      // Обновляем данные пользователя и лимит устройств
      $database_request = Database::send(
        'UPDATE vpn_users SET vpn_subscription = ?, vpn_status = ?, vpn_uuid = ?, vpn_freekey = ?, vpn_date_count = ?, vpn_divece_count = ?, vpn_amount = ? WHERE tg_id = ?',
        [
          strval($result['vpn_subscription']),
          strval($result['vpn_status']),
          strval($result['vpn_uuid']),
          strval('used_free'),
          intval($days),
          intval($divece_limit),
          intval(0),
          strval($client_id)
        ]
      );
      if ($database_request === false) {
        Message::set('error', 'Ошибка обновления данных пользователя');
        // Лог ошибки
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf(
            "[%s] [ERROR] give_sub: Ошибка обновления данных пользователя для tg_id %s (admin %s)\n",
            date('Y-m-d H:i:s'),
            $client_id,
            $adminClient['tg_id'] ?? '-'
          ),
          FILE_APPEND
        );
        header("Location: /admin/{$adminClient['tg_id']}");
        exit();
      }

      Message::set('success', "Подписка выдана клиенту #$client_id на $days дней.");
      // ЛОГИРУЕМ
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [SUCCESS] give_sub: Подписка выдана клиенту #$client_id (@%s) на {$days} день/дней (admin %s)\n",
          date('Y-m-d H:i:s'),
          $client_id,
          $username,
          $adminClient['tg_id'] ?? '-'
        ),
        FILE_APPEND
      );
      header("Location: /admin/{$adminClient['tg_id']}");
      exit();
      // break;
    }

    case 'remove_sub': {
      $rem_id = intval($_POST['rem_id'] ?? 0);
      if ($rem_id <= 0) {
        Message::set('error', "Некорректный tg_id клиента.");
        // Лог ошибки
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf("[%s] [ERROR] remove_sub: Некорректный tg_id клиента (%s) (admin %s)\n", date('Y-m-d H:i:s'), $rem_id, $adminClient['tg_id'] ?? '-'),
          FILE_APPEND
        );
        header("Location: /admin/{$adminClient['tg_id']}");
        exit();
      }
      $result = $functions->DeleteKey($rem_id);

      // Лог результата удаления
      if (is_array($result)) {
        if (isset($result['status']) && $result['status'] === 'ok') {
          Message::set('success', "Подписка клиента #$rem_id удалена.");
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf("[%s] [SUCCESS] remove_sub: Подписка клиента #$rem_id удалена (admin %s)\n", date('Y-m-d H:i:s'), $adminClient['tg_id'] ?? '-'),
            FILE_APPEND
          );
        } elseif (isset($result['message'])) {
          Message::set('error', "Ошибка удаления подписки: {$result['message']}");
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf("[%s] [ERROR] remove_sub: Ошибка удаления подписки у #$rem_id: %s (admin %s)\n", date('Y-m-d H:i:s'), $result['message'], $adminClient['tg_id'] ?? '-'),
            FILE_APPEND
          );
        } else {
          Message::set('error', "Ошибка при удалении подписки.");
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf("[%s] [ERROR] remove_sub: Общая ошибка удаления подписки у #$rem_id (admin %s)\n", date('Y-m-d H:i:s'), $adminClient['tg_id'] ?? '-'),
            FILE_APPEND
          );
        }
      } else {
        Message::set('error', "Некорректный ответ при удалении подписки.");
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf("[%s] [ERROR] remove_sub: Некорректный ответ при удалении подписки у #$rem_id (admin %s)\n", date('Y-m-d H:i:s'), $adminClient['tg_id'] ?? '-'),
          FILE_APPEND
        );
      }
      header("Location: /admin/{$adminClient['tg_id']}");
      exit();
      // break;
    }

    case 'give_test': {
      $random_id = substr(str_shuffle('0123456789'), 0, 10);
      $test_days = intval($_POST['test_days'] ?? 3);
      $username = strval($_POST['test_username'] ?? 'Тестовый пользователь');
      $divece_limit = intval($_POST['test_divece_limit'] ?? 1);

      // Проверяем, есть ли уже пользователь с таким именем или tg_id в базе
      $check = Database::send(
        "SELECT COUNT(*) as cnt FROM vpn_users WHERE tg_username = ? OR tg_id = ?",
        [strval($username), strval($random_id)]
      );
      if (!empty($check[0]['cnt']) && $check[0]['cnt'] > 0) {
        Message::set('error', "Пользователь с именем '{$username}' или tg_id '{$random_id}' уже существует.");
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf("[%s] [ERROR] give_test: Пользователь с tg_username '%s' или tg_id '%s' уже существует (admin %s)\n", date('Y-m-d H:i:s'), $username, $random_id, $adminClient['tg_id'] ?? '-'),
          FILE_APPEND
        );
        header("Location: /admin/{$adminClient['tg_id']}");
        exit();
      } else {

        $result = $functions->add_client($test_days, $username, $divece_limit);

        if (!is_array($result) || empty($result['vpn_subscription']) || empty($result['vpn_status']) || empty($result['vpn_uuid'])) {
          Message::set('error', 'Ошибка выдачи тестовой подписки.');
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf("[%s] [ERROR] give_test: Ошибка выдачи тестовой подписки (username: %s, random_id: %s) (admin %s)\n", date('Y-m-d H:i:s'), $username, $random_id, $adminClient['tg_id'] ?? '-'),
            FILE_APPEND
          );
          header("Location: /admin/{$adminClient['tg_id']}");
          exit();
        }

        // Обновляем данные пользователя как тестовая подписка
        $update = Database::send(
          'INSERT INTO vpn_users (tg_id, tg_username, tg_first_name, tg_last_name, vpn_uuid, vpn_subscription, vpn_status, vpn_date_count, vpn_freekey, kassa_id, vpn_divece_count, vpn_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
          [
            strval($random_id),             /* tg_id */
            strval($username),              /* tg_username */
            'Тестовый',                     /* tg_first_name */
            'Пользователь',                 /* tg_last_name */
            strval($result['vpn_uuid']),    /* vpn_uuid */
            strval($result['vpn_subscription']), /* vpn_subscription */
            strval($result['vpn_status']),  /* vpn_status */
            intval($test_days),             /* vpn_date_count */
            'used_free',                    /* vpn_freekey */
            '',                             /* kassa_id */
            intval($divece_limit),           /* vpn_divece_count */
            0                               /* vpn_amount */
          ]
        );

        if ($update === false) {
          Message::set('error', 'Ошибка обновления данных пользователя для тестовой подписки.');
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf("[%s] [ERROR] give_test: Ошибка вставки тестовой подписки для %s (%s) (admin %s)\n", date('Y-m-d H:i:s'), $username, $random_id, $adminClient['tg_id'] ?? '-'),
            FILE_APPEND
          );
          header("Location: /admin/{$adminClient['tg_id']}");
          exit();
        } else {
          Message::set('success', "Тестовая подписка выдана клиенту #$random_id на $test_days дня.");
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf("[%s] [SUCCESS] give_test: Тестовая подписка выдана %s (#%s) на %d дней (admin %s)\n", date('Y-m-d H:i:s'), $username, $random_id, $test_days, $adminClient['tg_id'] ?? '-'),
            FILE_APPEND
          );
          header("Location: /admin/{$adminClient['tg_id']}");
          exit();
        }
      }
      // break;
    }

    case 'cleanup_subs': {
      Functions::CleanUP();
      Message::set('success', "Выполнена автоочистка!");
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf("[%s] [SUCCESS] cleanup_subs: Выполнена автоочистка по требованию администратора (admin %s)\n", date('Y-m-d H:i:s'), $adminClient['tg_id'] ?? '-'),
        FILE_APPEND
      );
      header("Location: /admin/{$adminClient['tg_id']}");
      exit();
      // break;
    }

    case 'execute_sql': {
      $sql_query = trim($_POST['sql_query'] ?? '');

      if (empty($sql_query)) {
        $sql_result = ['error' => 'SQL запрос не может быть пустым'];
      } else {
        try {
          // Логирование SQL запроса
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf(
              "[%s] [SQL_EXEC] Администратор %s выполнил SQL запрос: %s\n",
              date('Y-m-d H:i:s'),
              $adminClient['tg_id'] ?? '-',
              substr($sql_query, 0, 200) . (strlen($sql_query) > 200 ? '...' : '')
            ),
            FILE_APPEND
          );

          // Выполняем SQL запрос
          $result = Database::send($sql_query);

          if (is_array($result)) {
            $sql_result = ['data' => $result, 'error' => null];
          } else {
            $sql_result = ['data' => null, 'error' => null];
          }

        } catch (Exception $e) {
          $sql_result = ['error' => $e->getMessage()];

          // Логирование ошибки
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf(
              "[%s] [SQL_ERROR] Ошибка SQL запроса от администратора %s: %s\n",
              date('Y-m-d H:i:s'),
              $adminClient['tg_id'] ?? '-',
              $e->getMessage()
            ),
            FILE_APPEND
          );
        }
      }
      break;
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['autopay'])) {
  (new Functions())->AutoPay();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_price'])) {
  $request = [];
  $basic = isset($_POST['basic']) && !empty($_POST['basic']) ? $request['basic'] = $_POST['basic'] : null;
  $plus = isset($_POST['plus']) && !empty($_POST['plus']) ? $request['plus'] = $_POST['plus'] : null;
  $pro = isset($_POST['pro']) && !empty($_POST['pro']) ? $request['pro'] = $_POST['pro'] : null;

  if (!empty($request)) {
    $edit_price = (new Functions())->isPrice('edit', $request);
  }
}
$price = (new Functions())->isPrice();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoraVPN | Admin</title>
  <link href="https://fonts.cdnfonts.com/css/inter" rel="stylesheet">
  <link href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" rel="stylesheet">
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
      <a href="#"
        class="flex items-center gap-3 px-2 py-2 rounded-lg font-bold text-primary-400 tracking-tight hover:text-primary-800 transition">
        <i class="fas fa-street-view"></i> Доска управления</a>
      <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/table"
        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200 transition">
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
  // Короткое уведомление (toast)
  $notification = Message::controll();
  $types = ['success' => 'success', 'error' => 'error'];
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
      <span class="flex-1" style="line-height:1.25">
        <?= $msg ?>
      </span>
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
          setTimeout(closeToast, 3500);
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


  <!-- content -->
  <div class="flex min-h-[calc(100dvh-56px)] transition-bg bg-background">

    <!-- Sidebar (desktop/tablet) -->
    <aside
      class="hidden sm:flex min-w-[220px] max-w-[270px] bg-card flex-col h-[calc(100dvh-56px)] border-r border-border sticky top-[56px] z-30">
      <nav class="py-6 px-4 flex-1 flex flex-col gap-2">
        <a href="#"
          class="flex items-center gap-3 px-2 py-2 rounded-lg font-bold tracking-tight hover:text-purple-200 transition text-primary">
          <i class="fas fa-street-view"></i> Доска управления</a>
        <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/table"
          class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200">
          <i class="fas fa-table"></i> Таблица данных</a>
        <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/partners"
          class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-accent/10 text-gray-200">
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
        <a href="/profile"
          class="flex items-center gap-3 px-2 py-2 rounded-lg text-gray-200 hover:bg-purple-500/10 transition">
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
          Управление клиентами VPN</h1>
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
          <span class="text-xs mt-1 text-gray-400">Без подписки</span>
        </div>
      </section>

      <!-- Клиенты - Таблица -->
      <section class="bg-card border border-border rounded-2xl p-4 sm:p-6 w-full max-w-full">
        <div class="flex flex-col sm:flex-row justify-between gap-4 sm:items-center mb-2">
          <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
            Список клиентов</h2>
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
              <?php if (is_array($database) && count($database) > 0): ?>
                <?php foreach (array_slice(array_reverse($database), 0, 10) as $index => $table_client): ?>
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
                        echo '<span class="text-gray-400">-</span>';
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

        <div class="flex justify-center items-center mt-6 w-full">
          <a href="/admin/<?= htmlspecialchars($adminClient['tg_id']) ?>/table"
            class="inline-flex items-center px-4 py-2 bg-border hover:bg-black/40 rounded-lg font-medium text-[#fff] transition-colors duration-150 border border-border">
            <i class="fas fa-table mr-2"></i>
            <span>Вся таблица</span>
          </a>
        </div>
      </section>

      <!-- Добавить клиента -->
      <section class="bg-card border border-border rounded-2xl p-4 sm:p-6 w-full">
        <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
          Выдать подписку</h2>
        <form class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4" action="#" method="POST">

          <!-- give_sub selected -->
          <input type="hidden" name="admin_action" value="give_sub">

          <!-- id -->
          <div class="relative">
            <label for="client_id" class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-white text-xs">ID
              клиента</label>
            <input type="number" id="client_id" name="client_id"
              class="w-full px-3 py-2 text-[15px] rounded-lg bg-muted border border-border text-white placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
              placeholder="ID" required>
          </div>

          <!-- count days -->
          <div class="relative">
            <label for="give_days"
              class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-white text-xs">Количество
              дней</label>
            <input type="number" id="give_days" name="give_days" placeholder="от 1 до ∞"
              class="w-full px-3 py-2 rounded-lg bg-muted border border-border text-white placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
              required>
          </div>

          <!-- limit divese -->
          <div class="relative">
            <label for="give_divece_limit"
              class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-white text-xs">Количество
              устройств</label>
            <input type="number" id="give_divece_limit" name="give_divece_limit" placeholder="от 1 до ∞ (0 безлимит)"
              class="w-full px-3 py-2 rounded-lg bg-muted border border-border text-white placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
              required>
          </div>

          <button
            class="px-2 rounded-lg bg-primary hover:bg-purple-500/60 transition-colors text-white font-semibold text-base focus:outline-none"
            type="submit">
            <i class="fas fa-user-plus mr-2 text-sm"></i>
            Выдать
          </button>

        </form>
      </section>

      <!-- Создать тестовый ключ -->
      <section class="bg-card border border-border rounded-2xl p-4 sm:p-6 w-full">
        <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
          Создание тестовой подписки</h2>
        <form class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4" action="#" method="POST">

          <!-- give_sub selected -->
          <input type="hidden" name="admin_action" value="give_test">

          <!-- test - username -->
          <div class="relative">
            <label for="test_username"
              class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-white text-xs">Имя
              пользователя</label>
            <input type="text" id="test_username" name="test_username"
              class="w-full px-3 py-2 text-[15px] rounded-lg bg-muted border border-border text-white placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
              placeholder="Имя пользователя" required>
          </div>

          <!-- test - count days -->
          <div class="relative">
            <label for="test_days"
              class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-white text-xs">Количество
              дней</label>
            <input type="number" id="test_days" name="test_days" placeholder="от 1 до ∞"
              class="w-full px-3 py-2 rounded-lg bg-muted border border-border text-white placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
              required>
          </div>

          <!-- limit divese -->
          <div class="relative">
            <label for="test_divece_limit"
              class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-white text-xs">Количество
              устройств</label>
            <input type="number" id="test_divece_limit" name="test_divece_limit" placeholder="от 1 до ∞ (0 безлимит)"
              class="w-full px-3 py-2 rounded-lg bg-muted border border-border text-white placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
              required>
          </div>

          <button
            class="px-2 rounded-lg bg-primary hover:bg-purple-500/60 transition-colors text-white font-semibold text-base focus:outline-none"
            type="submit">
            <i class="fas fa-key mr-2 text-sm"></i>
            Создать
          </button>

        </form>
      </section>

      <!-- Измение цен -->
      <section class="bg-card border border-border rounded-2xl p-4 sm:p-6 w-full">
        <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
          Изменение цен</h2>
        <form class="grid grid-cols-1 sm:grid-cols-2 gap-10 mt-4" action="#" method="POST">

          <!-- Текущие цены -->
          <div class="relative">
            <h3>Текущие цены:</h3>
            <div class="flex flex-col gap-4">

              <!-- ITEMS 1 -->
              <div class="flex gap-3 items-center">
                <input
                  class="w-1/4 px-3 py-2 rounded-lg bg-muted border border-border text-purple-400 placeholder-gray-400 focus:ring-accent focus:outline-none mt-2 cursor-no-drop"
                  value="<?= $price['basic'] ?> ₽" readonly id="basic-val">
                </input>
                <label for="basic-val" class="px-1 text-white text-xs">Тариф
                  БАЗОВЫЙ:
                </label>
                <!-- chnage visual -->
                <div id="change-basic-visual" class="opacity-0 transition">
                  <span class="text-red-500 line-through">
                    <?= $price['basic'] ?> ₽
                  </span>
                  >>
                  <span class="text-green-500 underline animate-pulse" id="change-basic-new-amount"></span>
                </div>
                <!-- end -->
              </div>

              <!-- ITEMS 2 -->
              <div class="flex gap-3 items-center">
                <input
                  class="w-1/4 px-3 py-2 rounded-lg bg-muted border border-border text-purple-400 placeholder-gray-400 focus:ring-accent focus:outline-none mt-2 cursor-no-drop"
                  value="<?= $price['plus'] ?> ₽" readonly id="plus-val">
                </input>
                <label for="plus-val" class="px-1 text-white text-xs">Тариф
                  ПЛЮС:
                </label>
                <!-- chnage visual -->
                <div id="change-plus-visual" class="opacity-0 transition">
                  <span class="text-red-500 line-through">
                    <?= $price['plus'] ?> ₽
                  </span>
                  >>
                  <span class="text-green-500 underline animate-pulse" id="change-plus-new-amount"></span>
                </div>
                <!-- end -->
              </div>

              <!-- ITEMS 3 -->
              <div class="flex gap-3 items-center">
                <input
                  class="w-1/4 px-3 py-2 rounded-lg bg-muted border border-border text-purple-400 placeholder-gray-400 focus:ring-accent focus:outline-none mt-2 cursor-no-drop"
                  value="<?= $price['pro'] ?> ₽" readonly id="pro-val">
                </input>
                <label for="pro-val" class="px-1 text-white text-xs">Тариф
                  ПРО:
                </label>
                <!-- chnage visual -->
                <div id="change-pro-visual" class="opacity-0 transition">
                  <span class="text-red-500 line-through">
                    <?= $price['pro'] ?> ₽
                  </span>
                  >>
                  <span class="text-green-500 underline animate-pulse" id="change-pro-new-amount"></span>
                </div>
                <!-- end -->
              </div>

            </div>
          </div>

          <div class="relative">
            <h3>Изменение цен:</h3>
            <div class="flex flex-col gap-4">

              <div class="relative">
                <label for="basic"
                  class="absolute right-10 border rounded-md top-5 bg-card px-1 text-white text-xs">Тариф
                  БАЗОВЫЙ
                </label>
                <input type="number" id="change-basic-input" name="basic" placeholder="от 1 рубля"
                  class="w-full px-3 py-2 rounded-lg bg-muted border border-border text-white placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
                  min="0">
              </div>

              <div class="relative">
                <label for="plus"
                  class="absolute right-10 border rounded-md top-5 bg-card px-1 text-white text-xs">Тариф
                  ПЛЮС
                </label>
                <input type="number" id="change-plus-input" name="plus" placeholder="от 1 рубля"
                  class="w-full px-3 py-2 rounded-lg bg-muted border border-border text-white placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
                  min="0">
              </div>

              <div class="relative">
                <label for="pro" class="absolute right-10 border rounded-md top-5 bg-card px-1 text-white text-xs">Тариф
                  ПРО
                </label>
                <input type="number" id="change-pro-input" name="pro" placeholder="от 1 рубля"
                  class="w-full px-3 py-2 rounded-lg bg-muted border border-border text-white placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
                  min="0">
              </div>
            </div>
          </div>
          <button
            class="p-2 rounded-lg bg-primary hover:bg-purple-500/60 transition-colors text-white font-semibold text-base focus:outline-none"
            type="submit">
            <i class="far fa-money-bill-alt mr-2 text-sm"></i>
            Изменить
          </button>
          <input type="hidden" name="edit_price">
        </form>
      </section>

      <script>
        //basic
        const change_basic = document.getElementById('change-basic-visual');
        const change_basic_input = document.getElementById('change-basic-input');
        const change_basic_new_amount = document.getElementById('change-basic-new-amount');

        //plus
        const change_plus = document.getElementById('change-plus-visual');
        const change_plus_input = document.getElementById('change-plus-input');
        const change_plus_new_amount = document.getElementById('change-plus-new-amount');

        //pro
        const change_pro = document.getElementById('change-pro-visual');
        const change_pro_input = document.getElementById('change-pro-input');
        const change_pro_new_amount = document.getElementById('change-pro-new-amount');

        //basic event
        change_basic_input.addEventListener('input', () => {
          if (change_basic_input.value && change_basic_input.value != <?= $price['basic'] ?>) {
            change_basic.classList.remove('opacity-0');
            change_basic_new_amount.innerHTML = change_basic_input.value + ' ₽';
          } else {
            change_basic.classList.add('opacity-0');
          }
        });

        //plus event
        change_plus_input.addEventListener('input', () => {
          if (change_plus_input.value && change_plus_input.value != <?= $price['basic'] ?>) {
            change_plus.classList.remove('opacity-0');
            change_plus_new_amount.innerHTML = change_plus_input.value + ' ₽';
          } else {
            change_plus.classList.add('opacity-0');
          }
        });

        //pro event
        change_pro_input.addEventListener('input', () => {
          if (change_pro_input.value && change_pro_input.value != <?= $price['basic'] ?>) {
            change_pro.classList.remove('opacity-0');
            change_pro_new_amount.innerHTML = change_pro_input.value + ' ₽';
          } else {
            change_pro.classList.add('opacity-0');
          }
        });
      </script>

      <!-- Логи действий -->
      <section class="bg-card border border-border rounded-2xl p-4 sm:p-6 w-full">
        <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
          Логи данных</h2>
        <div class="max-h-44 sm:max-h-72 overflow-y-auto flex flex-col gap-0.5">
          <?php
          $logfile = dirname(__DIR__, 4) . '/coravpn.log';
          if (file_exists($logfile)) {
            $lines = file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lines = array_reverse($lines);
            foreach ($lines as $line) {
              $escaped = htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");

              // Дефолтные цвета
              $color = 'text-white'; // для обычных сообщений
              $bg = '';

              // Кастомизация цвета/фона лога по ключевым словам (дата, тип логов, статус)
              if (stripos($line, 'ошибка') !== false || stripos($line, 'error') !== false || stripos($line, 'fail') !== false || stripos($line, 'failed') !== false || stripos($line, 'critical') !== false) {
                $color = 'text-red-300';
                $bg = 'bg-red-950/30';
              } elseif (
                stripos($line, 'успешн') !== false ||
                stripos($line, 'УСПЕШНО') !== false ||
                stripos($line, 'success') !== false ||
                stripos($line, 'включ') !== false && stripos($line, 'автоплатеж') !== false
              ) {
                $color = 'text-green-600';
                $bg = 'bg-green-950/30';
              } elseif (
                stripos($line, 'ЗАПУСК') !== false ||
                stripos($line, 'КОНЕЦ') !== false
              ) {
                $color = 'text-green-300';
                $bg = 'bg-green-900/10';
              } elseif (
                stripos($line, 'АВТОПЛАТЕЖА') !== false
                && (stripos($line, 'запуск') !== false || stripos($line, 'system start') !== false)
              ) {
                $color = 'text-blue-300';
                $bg = 'bg-blue-900/10';
              } elseif (stripos($line, 'warning') !== false || stripos($line, 'warn') !== false) {
                $color = 'text-yellow-300';
                $bg = 'bg-yellow-950/10';
              } elseif (stripos($line, 'информация') !== false || stripos($line, 'info') !== false) {
                $color = 'text-blue-400';
                $bg = 'bg-blue-950/10';
              } elseif (stripos($line, 'новый пользователь') !== false) {
                $color = 'text-cyan-300';
                $bg = 'bg-cyan-950/20';
              } elseif (stripos($line, 'добавлен новый пользователь') !== false) {
                $color = 'text-cyan-300';
                $bg = 'bg-cyan-950/20';
              } elseif (stripos($line, 'mail') !== false || stripos($line, 'email') !== false) {
                $color = 'text-pink-200';
                $bg = 'bg-pink-950/15';
              }

              // Подсветка даты в начале строки
              // Пример: [2024-04-25 14:33:22]
              $escaped = preg_replace(
                '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/',
                '<span class="text-gray-500">[$1]</span>',
                $escaped
              );

              // Вторая подсветка для лейблов в [] после даты
              $escaped = preg_replace_callback(
                '/\] \[([^\]]+)\]/',
                function ($m) {
                  $label = strtolower($m[1]);
                  $color = 'text-blue-400'; // Дефолт
                  if (preg_match('/ошибка|error|fail|failed|critical/u', $label))
                    $color = 'text-red-400';
                  elseif (preg_match('/успешн|success|УСПЕШНО/u', $label))
                    $color = 'text-green-400';
                  elseif (preg_match('/новый|new|info|инфор/u', $label))
                    $color = 'text-cyan-300';
                  elseif (preg_match('/warning|warn|предупрежд/u', $label))
                    $color = 'text-yellow-300';
                  elseif (preg_match('/admin|login|вход|ИНФОРМАЦИЯ/u', $label))
                    $color = 'text-purple-300';
                  return '] <span class="' . $color . '">[' . htmlspecialchars($m[1], ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") . ']</span>';
                },
                $escaped
              );

              // Применяем цвета
              echo "<div class='text-[13px] font-mono $color $bg px-2 py-0.5 rounded select-text'>$escaped</div>";
            }
          } else {
            echo "<div class='text-[13px] italic bg-card'>Лог-файл не найден.</div>";
          }
          ?>
        </div>
      </section>

      <!-- SQL Консоль -->
      <section class="bg-card border border-border rounded-2xl p-4 sm:p-6 w-full max-w-full">
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
          <i data-feather="database" class="w-5 h-5"></i>
          SQL Консоль
        </h3>

        <form method="POST" class="space-y-4">
          <input type="hidden" name="admin_action" value="execute_sql">

          <!-- Поле для SQL запроса -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">SQL Запрос:</label>
            <textarea name="sql_query" rows="4"
              class="w-full px-3 py-2 bg-muted border border-border rounded-lg text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-accent"
              placeholder="Введите SQL запрос..." required><?= htmlspecialchars($_POST['sql_query'] ?? '') ?></textarea>
          </div>

          <!-- Кнопка выполнения -->
          <div class="flex gap-2">
            <button type="submit"
              class="px-4 py-2 bg-accent hover:bg-accent/90 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
              <i data-feather="play" class="w-4 h-4"></i>
              Выполнить запрос
            </button>
            <button type="button" onclick="clearSqlQuery()"
              class="px-4 py-2 bg-muted hover:bg-muted/80 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
              <i data-feather="x" class="w-4 h-4"></i>
              Очистить
            </button>
          </div>
        </form>

        <!-- Подсказки популярных запросов -->
        <div class="mt-6">
          <h4 class="text-sm font-semibold text-gray-300 mb-3">Популярные запросы:</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <button onclick="setQuery('SELECT COUNT(*) as total_users FROM vpn_users;')"
              class="text-left px-3 py-2 bg-muted/50 hover:bg-muted/70 rounded-lg text-xs text-gray-300 transition">
              <span class="text-accent font-mono">COUNT</span> - Посчитать всех пользователей
            </button>
            <button onclick="setQuery('SELECT * FROM vpn_users WHERE tg_id = 123456789;')"
              class="text-left px-3 py-2 bg-muted/50 hover:bg-muted/70 rounded-lg text-xs text-gray-300 transition">
              <span class="text-accent font-mono">SELECT</span> - Найти пользователя по TG ID
            </button>
            <button onclick="setQuery('SELECT * FROM vpn_users WHERE refer_link != \" \" ORDER BY tg_id DESC LIMIT
              10;')"
              class="text-left px-3 py-2 bg-muted/50 hover:bg-muted/70 rounded-lg text-xs text-gray-300 transition">
              <span class="text-accent font-mono">REFERALS</span> - Последние рефералы
            </button>
            <button
              onclick="setQuery('SELECT * FROM vpn_users WHERE vpn_amount > 0 ORDER BY CAST(vpn_amount AS REAL) DESC LIMIT 10;')"
              class="text-left px-3 py-2 bg-muted/50 hover:bg-muted/70 rounded-lg text-xs text-gray-300 transition">
              <span class="text-accent font-mono">TOP PAID</span> - Топ платящих пользователей
            </button>
            <button onclick="setQuery('DELETE FROM vpn_users WHERE tg_id = 123456789;')"
              class="text-left px-3 py-2 bg-danger/20 hover:bg-danger/30 rounded-lg text-xs text-red-300 transition">
              <span class="text-danger font-mono">⚠ DELETE</span> - Удалить пользователя
            </button>
            <button onclick="setQuery('UPDATE vpn_users SET refer_link = \" \" WHERE tg_id=123456789;')"
              class="text-left px-3 py-2 bg-warning/20 hover:bg-warning/30 rounded-lg text-xs text-yellow-300 transition">
              <span class="text-warning font-mono">⚠ UNLINK</span> - Удалить реферальную привязку
            </button>
            <button onclick="setQuery('SELECT refer_link, COUNT(*) as count FROM vpn_users WHERE refer_link != \" \"
              GROUP BY refer_link ORDER BY count DESC;')"
              class="text-left px-3 py-2 bg-muted/50 hover:bg-muted/70 rounded-lg text-xs text-gray-300 transition">
              <span class="text-accent font-mono">STATS</span> - Статистика рефералов
            </button>
          </div>
        </div>

        <!-- Результат выполнения запроса -->
        <?php if (isset($sql_result)): ?>
          <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-300 mb-3">Результат:</h4>
            <div class="bg-muted/30 rounded-lg p-4 overflow-x-auto">
              <?php if ($sql_result['error']): ?>
                <div class="text-red-400 text-sm font-mono">
                  <strong>Ошибка:</strong> <?= htmlspecialchars($sql_result['error']) ?>
                </div>
              <?php else: ?>
                <?php if (is_array($sql_result['data'])): ?>
                  <?php if (count($sql_result['data']) > 0): ?>
                    <table class="w-full text-sm">
                      <thead class="border-b border-border">
                        <tr>
                          <?php foreach (array_keys($sql_result['data'][0]) as $column): ?>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-300"><?= htmlspecialchars($column) ?>
                            </th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-border">
                        <?php foreach ($sql_result['data'] as $row): ?>
                          <tr>
                            <?php foreach ($row as $value): ?>
                              <td class="px-3 py-2 text-xs text-gray-400"><?= htmlspecialchars($value ?? 'NULL') ?></td>
                            <?php endforeach; ?>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                    <div class="mt-2 text-xs text-gray-500">
                      Найдено записей: <?= count($sql_result['data']) ?>
                    </div>
                  <?php else: ?>
                    <div class="text-gray-400 text-sm">Запрос выполнен успешно, но не вернул данных.</div>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="text-green-400 text-sm">Запрос выполнен успешно.</div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <script src="/public/pages/telegram/admin/src/main.js"></script>
  <script>
    function setQuery(query) {
      document.querySelector('textarea[name="sql_query"]').value = query;
    }

    function clearSqlQuery() {
      document.querySelector('textarea[name="sql_query"]').value = '';
      // Убираем результат выполнения
      const resultSection = document.querySelector('[class*="mt-6"]:has(h4:contains("Результат:"))');
      if (resultSection) {
        resultSection.remove();
      }
    }
  </script>
</body>

</html>
