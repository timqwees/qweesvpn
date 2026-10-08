<?php

declare(strict_types=1);

use App\Models\Router\Routes;
use App\Config\Session;
use Setting\Route\Function\Controllers\Admin\{AdminDatabase, AdminXray, AdminAuth, Admin};
use Setting\Route\Function\Controllers\Admin\Group\Groups;
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\Client\GetUser;
use Setting\Route\Function\Controllers\Language\LanguageSwitch;
use Setting\Route\Function\Controllers\Kassa\PaymentController;
use Setting\Route\Function\Controllers\Server\Network as ServerNetwork;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;
use Setting\Route\Function\Controllers\Chat\Chat;
use Setting\Route\Function\Controllers\Gifts\Gifts;
use Setting\Route\Function\Controllers\Refer\Refer;
use Setting\Route\Function\Controllers\Admin\Finance\Finance;

//=============================================//MAIN
Routes::get('/', 'on_Main');
//=============================================//INSTALLER
Routes::get('/install', 'on_Install');
//=============================================//LANGUAGE
Routes::post('/language/switch', [LanguageSwitch::class, 'switch']);
//=============================================//PAY
Routes::get('/pay', 'on_Pay');
Routes::get('/pay/status', 'on_PayStatus');
Routes::post('/api/payment/create', [PaymentController::class, 'createPayment']);
//=============================================//ЧЕКИ ПОЛЬЗОВАТЕЛЯ (история из JSON-индекса, квитанция живьём из кассы)
Routes::get('/api/payments/mine', function () {
    header('Content-Type: application/json; charset=UTF-8');
    $uniID = (string) (Session::init('user')['uniID'] ?? '');
    if ($uniID === '') {
        echo json_encode(['status' => 'error', 'message' => 'Войдите в аккаунт'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $idx = \Setting\Route\Function\Controllers\Kassa\PaymentIndex::class;
    $list = $idx::forUser($uniID);
    if ($list === []) { // старые оплаты подтянутся из леджера один раз, дальше пишет хук
        $idx::backfillFromLedger($uniID);
        $list = $idx::forUser($uniID);
    }
    echo json_encode(['status' => 'ok', 'items' => $list], JSON_UNESCAPED_UNICODE);
    exit;
});
Routes::get('/api/payment/receipt', function () {
    $uniID = (string) (Session::init('user')['uniID'] ?? '');
    if ($uniID === '') {
        echo 'Войдите в аккаунт';
        exit;
    }
    $id = trim((string) ($_GET['id'] ?? ''));
    $idx = \Setting\Route\Function\Controllers\Kassa\PaymentIndex::class;
    $info = \Setting\Route\Function\Controllers\Admin\Payments\PaymentInfo::class;
    // 1) быстрый указатель: есть ли id в истории юзера (чужие id отсекаем тут же)
    if (!$idx::owns($uniID, $id)) {
        echo 'Чек не найден';
        exit;
    }
    // 2) живая сверка с кассой: metadata.uniID обязан совпасть с сессией.
    // Подделанный JSON тут не поможет — верит только касса.
    $d = $info::getById($id);
    if (($d['status'] ?? '') !== 'ok' || ($d['uniID'] ?? '') !== $uniID) {
        echo 'Чек не найден';
        exit;
    }
    header('Content-Type: text/html; charset=UTF-8');
    echo $info::renderReceiptHtml($d);
    exit;
});
//=============================================//DELETE
Routes::post('/api/subscription/delete', function () {
    $result = (new Xray())->DeleteKey();
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
});
//=============================================//SERVER SELECT (выбор сервера в профиле)
Routes::post('/api/server/switch', function () {
    header('Content-Type: application/json; charset=UTF-8');
    $uniID = (new GetUser())->getUniID();
    if ($uniID === '') {
        echo json_encode(['status' => 'error', 'message' => 'Пользователь не найден'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode(
        ServerNetwork::switchServer($uniID, (string) ($_POST['server'] ?? '')),
        JSON_UNESCAPED_UNICODE
    );
    exit;
});
//=============================================//REFERRAL
Routes::post('/api/referral/activate', function () {
    (new Refer())->onValidateCode($_POST['code'] ?? '', $_POST['online'] ?? '');
});
Routes::get('/reflink={code}', [Refer::class, 'onValidateCode']);
//=============================================//CHAT (юзер пишет админу)
Routes::post('/api/chat/send', function () {
    $uniID = (string) (Session::init('user')['uniID'] ?? '');//uniID уже в куке, запрос в БД не нужен
    $message = trim((string) ($_POST['message'] ?? ''));
    if ($uniID === '' || $message === '') Chat::error('Сообщение не может быть пустым');
    $chat = new Chat();
    $chat->sendMessage($uniID, mb_substr($message, 0, 2000))
        ? Chat::json(['message' => $message, 'messages' => $chat->getMessages($uniID)])
        : Chat::error('Не удалось отправить');
});
Routes::get('/api/chat/messages', function () {
    $uniID = (string) (Session::init('user')['uniID'] ?? '');//uniID уже в куке, запрос в БД не нужен
    if ($uniID === '') Chat::error('Пользователь не найден');
    $chat = new Chat();
    Chat::json(['messages' => $chat->getMessages($uniID), 'support_online' => $chat->isOnline('admin')]);
});
Routes::post('/api/chat/mark-read', function () {
    $uniID = (string) (Session::init('user')['uniID'] ?? '');//uniID уже в куке, запрос в БД не нужен
    if ($uniID === '') Chat::error('Пользователь не найден');
    (new Chat())->markRead($uniID);
    Chat::json();
});
//=============================================//CHAT ADMIN (только для admin)
Routes::get('/api/chat/dialogs', function () {
    AdminAuth::requirePermission('chat');
    Chat::json(['dialogs' => (new Chat())->getDialogs()]);
});
Routes::get('/api/chat/dialog', function () {
    AdminAuth::requirePermission('chat');
    Chat::json(['messages' => (new Chat())->getDialog(trim((string) ($_GET['uniID'] ?? '')))]);
});
Routes::post('/api/chat/reply', function () {
    AdminAuth::requirePermission('chat');
    $uniID = trim((string) ($_POST['uniID'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    if ($uniID === '' || $message === '') Chat::error('Нет данных');
    $chat = new Chat();
    $chat->reply($uniID, mb_substr($message, 0, 2000))
        ? Chat::json(['message' => $message, 'messages' => $chat->getDialog($uniID)])
        : Chat::error('Не удалось отправить');
});
Routes::post('/api/chat/close', function () {
    AdminAuth::requirePermission('chat');
    $uniID = trim((string) ($_POST['uniID'] ?? ''));
    if ($uniID === '') Chat::error('Нет данных');
    (new Chat())->closeDialog($uniID)
        ? Chat::json()
        : Chat::error('Не удалось закрыть');
});
Routes::post('/api/chat/clear', function () {
    AdminAuth::requirePermission('chat');
    $uniID = trim((string) ($_POST['uniID'] ?? ''));
    if ($uniID === '') Chat::error('Нет данных');
    (new Chat())->clearDialog($uniID)
        ? Chat::json()
        : Chat::error('Не удалось очистить');
});
Routes::get('/api/chat/userinfo', function () {
    AdminAuth::requirePermission('chat');
    $user = (new Chat())->getUserInfo(trim((string) ($_GET['uniID'] ?? '')));
    if (empty($user)) Chat::error('Пользователь не найден');
    Chat::json(['user' => $user]);
});
//=============================================//CHAT PHOTO
Routes::post('/api/chat/upload', function () {
    $chat = new Chat();
    if ((bool) (Session::init('admin')['auth'][0] ?? false)) {//админ шлет в выбранный диалог
        AdminAuth::requirePermission('chat');
        $uniID = trim((string) ($_POST['uniID'] ?? ''));
        $isAdmin = true;
    } else {//юзер шлет в свой диалог, uniID уже в куке, запрос в БД не нужен
        $uniID = (string) (Session::init('user')['uniID'] ?? '');
        $isAdmin = false;
    }
    $up = $_FILES['photo'] ?? null;
    if ($uniID === '' || !is_array($up) || ($up['error'] ?? 1) !== UPLOAD_ERR_OK) Chat::error('Нет файла');
    if (($up['size'] ?? 0) > 5 * 1024 * 1024) Chat::error('Фото больше 5 МБ');
    $mime = @getimagesize($up['tmp_name'] ?? '')['mime'] ?? '';
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'image/heic' => 'heic', 'image/heif' => 'heif'][$mime] ?? '';
    if ($ext === '') Chat::error('Только фото: jpg, png, gif, webp, heic');
    $name = $uniID . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (!@move_uploaded_file($up['tmp_name'], Chat::$uploads . '/' . $name)) Chat::error('Не удалось сохранить');
    if ($isAdmin) {
        $ok = $chat->replyPhoto($uniID, $name);
        $messages = $chat->getDialog($uniID);
    } else {
        $ok = $chat->sendPhoto($uniID, $name);
        $messages = $chat->getMessages($uniID);
    }
    $ok ? Chat::json(['file' => $name, 'messages' => $messages])
        : Chat::error('Не удалось отправить');
});
Routes::get('/api/chat/photo', function () {
    $name = (string) ($_GET['f'] ?? '');
    $isAdmin = (bool) (Session::init('admin')['auth'][0] ?? false);
    $uniID = $isAdmin ? '' : (string) (Session::init('user')['uniID'] ?? '');
    $path = (new Chat())->photoPath($name, $uniID);
    if ($path === '') {
        header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1') . ' 404 Not Found');
        exit;
    }
    $mime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', 'heic' => 'image/heic', 'heif' => 'image/heif'][strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
});
//=============================================//ADMIN RELOCATION (переезд: серверы и домены)
Routes::get('/api/admin/relocation', function () {
    AdminAuth::auth();
    header('Content-Type: application/json; charset=UTF-8');
    $rel = \Setting\Route\Function\Controllers\Admin\Relocation\Relocation::class;
    $srv = \Setting\Route\Function\Controllers\Server\Network::class;
    $action = (string) ($_GET['action'] ?? 'servers');
    // Чтение доступно любому из двух разделов (серверы или переезд)
    $id = (int) (Session::init('admin')['auth'][1] ?? 0);
    $me = (new Admin())->getUsername($id);
    $groups = new Groups();
    if (!$groups->isPermission($me, 'relocation') && !$groups->isPermission($me, 'servers')) {
        echo json_encode(['status' => 'error', 'message' => 'Нет прав'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $out = match ($action) {
        'preview' => $rel::preview((string) ($_GET['target'] ?? '')),
        'state' => $rel::state(),
        'domains' => ['status' => 'ok', 'domains' => $rel::domains(), 'exposure' => $rel::exposure()],
        'check' => ['status' => 'ok', 'check' => $rel::checkServer((string) ($_GET['code'] ?? ''))],
        'server_clients' => ['status' => 'ok', 'code' => (string) ($_GET['code'] ?? ''), 'clients' => $rel::serverClients((string) ($_GET['code'] ?? ''))],
        default => ['status' => 'ok', 'servers' => $rel::servers(), 'raw' => $srv::effectiveServers()],
    };
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
});
Routes::post('/api/admin/relocation', function () {
    AdminAuth::auth();
    header('Content-Type: application/json; charset=UTF-8');
    $rel = \Setting\Route\Function\Controllers\Admin\Relocation\Relocation::class;
    $srv = \Setting\Route\Function\Controllers\Server\Network::class;
    $action = (string) ($_POST['action'] ?? '');
    if (\in_array($action, ['save_servers', 'migrate_from', 'delete_server'], true)) {
        AdminAuth::requirePermission('servers');//реестр и удаление — только управление серверами
    } else {
        AdminAuth::requirePermission('relocation');//миграция/домены — раздел переезда
    }
    if ($action === 'save_servers') {
        $raw = (string) file_get_contents('php://input');
        $data = json_decode($raw, true);
        $servers = \is_array($data) && isset($data['servers']) ? $data['servers'] : null;
        if (!\is_array($servers)) {
            // fallback: form-encoded JSON строкой
            $servers = json_decode((string) ($_POST['servers'] ?? ''), true);
        }
        echo json_encode(\is_array($servers) ? $srv::saveServersJson($servers) : ['status' => 'error', 'message' => 'Нет данных'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $out = match ($action) {
        'start' => $rel::start((string) ($_POST['target'] ?? '')),
        'tick' => $rel::tick(),
        'retry' => $rel::retry(),
        'migrate_from' => $rel::migrateFrom((string) ($_POST['from'] ?? ''), (string) ($_POST['target'] ?? '')),
        'delete_server' => $rel::deleteServer((string) ($_POST['code'] ?? ''), (string) ($_POST['target'] ?? '')),
        default => ['status' => 'error', 'message' => 'Неизвестное действие'],
    };
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
});
Routes::get('/api/admin/roi', function () {
    AdminAuth::auth();
    $id = (int) (Session::init('admin')['auth'][1] ?? 0);
    $me = (new Admin())->getUsername($id);
    $groups = new Groups();
    if (!$groups->isPermission($me, 'roi') && !$groups->isPermission($me, 'profit')) {
        echo json_encode(['status' => 'error', 'message' => 'Нет прав'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(
        \Setting\Route\Function\Controllers\Admin\Finance\Finance::report(
            max(1, min(365, (int) ($_GET['days'] ?? 30))),
            isset($_GET['from']) ? (string) $_GET['from'] : null,
            isset($_GET['to']) ? (string) $_GET['to'] : null
        ),
        JSON_UNESCAPED_UNICODE
    );
    exit;
});
Routes::get('/api/admin/backup-db', function () {
    AdminAuth::requirePermission('database');
    $sql = AdminDatabase::exportSqlData();
    header('Content-Type: application/sql; charset=UTF-8');
    header('Content-Disposition: attachment; filename="qwees_backup_' . date('Y-m-d_H-i') . '.sql"');
    header('Content-Length: ' . strlen($sql));
    echo $sql;
    exit;
});
//=============================================//ADMIN PAYMENTS (чеки + досье)
Routes::get('/api/admin/payment', function () {
    AdminAuth::auth();
    $id = (int) (Session::init('admin')['auth'][1] ?? 0);
    $me = (new Admin())->getUsername($id);
    $groups = new Groups();
    if (!$groups->isPermission($me, 'roi') && !$groups->isPermission($me, 'chat')) {
        echo json_encode(['status' => 'error', 'message' => 'Нет прав'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    header('Content-Type: application/json; charset=UTF-8');
    $which = (string) ($_GET['which'] ?? 'check');
    $out = match ($which) {
        'history' => ['status' => 'ok', 'checks' => \Setting\Route\Function\Controllers\Admin\Payments\PaymentInfo::historyForUser((string) ($_GET['uniID'] ?? ''))],
        default => \Setting\Route\Function\Controllers\Admin\Payments\PaymentInfo::getById((string) ($_GET['id'] ?? '')),
    };
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
});
Routes::post('/api/admin/payments/import', function () {
    AdminAuth::requirePermission('roi');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(
        \Setting\Route\Function\Controllers\Admin\Payments\PaymentInfo::importFromKassa(max(1, min(365, (int) ($_POST['days'] ?? 90)))),
        JSON_UNESCAPED_UNICODE
    );
    exit;
});
Routes::get('/api/admin/client', function () {
    AdminAuth::auth();
    $id = (int) (Session::init('admin')['auth'][1] ?? 0);
    $me = (new Admin())->getUsername($id);
    $groups = new Groups();
    if (!$groups->isPermission($me, 'roi') && !$groups->isPermission($me, 'chat')) {
        echo json_encode(['status' => 'error', 'message' => 'Нет прав'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(
        \Setting\Route\Function\Controllers\Admin\Dossier\Dossier::forUser((string) ($_GET['uniID'] ?? '')),
        JSON_UNESCAPED_UNICODE
    );
    exit;
});
Routes::get('/api/admin/payment/receipt', function () {
    AdminAuth::auth();
    $id = (int) (Session::init('admin')['auth'][1] ?? 0);
    $me = (new Admin())->getUsername($id);
    $groups = new Groups();
    if (!$groups->isPermission($me, 'roi') && !$groups->isPermission($me, 'chat')) {
        echo 'Нет прав';
        exit;
    }
    $d = \Setting\Route\Function\Controllers\Admin\Payments\PaymentInfo::getById((string) ($_GET['id'] ?? ''));
    if (($d['status'] ?? '') !== 'ok') {
        echo 'Чек не найден';
        exit;
    }
    header('Content-Type: text/html; charset=UTF-8');
    echo \Setting\Route\Function\Controllers\Admin\Payments\PaymentInfo::renderReceiptHtml($d);
    exit;
});
//=============================================//ADMIN CLEANUP (очистка неактивных)
Routes::post('/api/admin/cleanup', function () {
    AdminAuth::requirePermission('monitoring');
    header('Content-Type: application/json; charset=UTF-8');
    $ov = \Setting\Route\Function\Controllers\Admin\Overview\Overview::class;
    $mode = (string) ($_POST['mode'] ?? 'preview');
    echo json_encode(
        $mode === 'execute' ? $ov::cleanupExecute() : $ov::cleanupPreview(),
        JSON_UNESCAPED_UNICODE
    );
    exit;
});
//=============================================//ADMIN HEALTH (самопроверки)
Routes::get('/api/admin/health', function () {
    AdminAuth::requirePermission('monitoring');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(
        \Setting\Route\Function\Controllers\Admin\Overview\Overview::getHealth(isset($_GET['fresh'])),
        JSON_UNESCAPED_UNICODE
    );
    exit;
});
//=============================================//ADMIN STATS (ленивая статистика главной)
Routes::get('/api/admin/stats', function () {
    AdminAuth::requirePermission('monitoring');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 'ok',
        'client' => AdminDatabase::getClientStats(),
        'financial' => AdminDatabase::getFinancialStats(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
});
//=============================================//MONITORING (живой дашборд)
Routes::get('/api/admin/overview', function () {
    AdminAuth::requirePermission('monitoring');
    header('Content-Type: application/json; charset=UTF-8');
    $fresh = isset($_GET['fresh']);
    $ov = \Setting\Route\Function\Controllers\Admin\Overview\Overview::class;
    if (!$fresh && \function_exists('fastcgi_finish_request') && ($hit = $ov::getCached()) !== null) {
        // Мгновенно отдаём прошлое, свежие данные досчитываем в фоне после закрытия соединения
        $hit['cached'] = true;
        $hit['refreshing'] = true;
        echo json_encode($hit, JSON_UNESCAPED_UNICODE);
        fastcgi_finish_request();
        $ov::rebuild();
        exit;
    }
    echo json_encode($ov::getOverview($fresh), JSON_UNESCAPED_UNICODE);
    exit;
});
//=============================================//GIFTS (пробные, пока не выкатываем)
Routes::post('/admin/gifts/save', [Gifts::class, 'onSave']);
//=============================================//GIFTS (выдача подарочной подписки)
Routes::post('/api/gifts/give', [Gifts::class, 'giveGifts']);
//=============================================//REFERRAL (настройки из админки)
Routes::post('/admin/refer/save', [Refer::class, 'onSave']);
//=============================================//ROI (расходы из админки)
Routes::post('/admin/roi/save', [Finance::class, 'onSave']);
//=============================================//ABOUT
Routes::get('/about', 'on_About');
//=============================================//РЕКВИЗИТЫ
Routes::get('/requisites', 'on_Requisites');
//=============================================//AUTH
//get
Routes::get('/auth/login', 'on_Login');
Routes::get('/auth/regist', 'on_Regist');
//post
Routes::post('/auth/login', [Auth::class, 'onLogin']);
Routes::post('/auth/regist', [Auth::class, 'onRegist']);
Routes::post('/auth/logout', [Auth::class, 'onLogout']);
//helpers
Routes::post('/auth/mail', [Auth::class, 'onMail']);
Routes::post('/auth/find', [Auth::class, 'isFindUser']);
//=============================================//ADMIN PANEL
//GET
Routes::get('/admin', 'on_Admin');
Routes::get('/admin/database', 'on_AdminDatabase');
Routes::get('/admin/edit', 'on_AdminEdit');
Routes::get('/admin/stats', 'on_AdminStats');
Routes::get('/admin/login', 'on_AdminLogin');
//POST roles (только для admin)
Routes::post('/admin/roles/perms', function () {
    AdminAuth::auth();
    $admin = new Admin();
    if (!$admin->hasRole((int) (Session::init('admin')['auth'][1] ?? 0), 'admin')) {
        header('Location: /admin');
        exit;
    }
    $groups = new Groups();
    $u = $_POST['username'] ?? '';
    $me = $admin->getUsername((int) (Session::init('admin')['auth'][1] ?? 0));
    if (strtolower($u) === strtolower(Admin::OWNER) && strtolower($me) !== strtolower(Admin::OWNER)) {
        header('Location: /admin');
        exit;
    }
    foreach (Admin::FULL_PERMISSIONS as $p => $value) {//синхроним галочки с json
        if (in_array($p, $_POST['perms'] ?? [], true)) $groups->addPermission($u, $p);
        else $groups->removePermission($u, $p);
    }
    $admin->LoggerCRM("обновил права $u");
    header('Location: /admin');
    exit;
});
Routes::post('/admin/roles/add', function () {
    AdminAuth::auth();
    $admin = new Admin();
    if (!$admin->hasRole((int) (Session::init('admin')['auth'][1] ?? 0), 'admin')) {
        header('Location: /admin');
        exit;
    }
    $admin->addManager($_POST['username'] ?? '', $_POST['password'] ?? '', $_POST['role'] ?? 'manager');
    header('Location: /admin');
    exit;
});
Routes::post('/admin/roles/fire', function () {
    AdminAuth::auth();
    $admin = new Admin();
    if (!$admin->hasRole((int) (Session::init('admin')['auth'][1] ?? 0), 'admin')) {
        header('Location: /admin');
        exit;
    }
    $me = $admin->getUsername((int) (Session::init('admin')['auth'][1] ?? 0));
    if (strtolower($_POST['username'] ?? '') === strtolower(Admin::OWNER) && strtolower($me) !== strtolower(Admin::OWNER)) {
        header('Location: /admin');
        exit;
    }
    $admin->deleteManager($_POST['username'] ?? '');
    header('Location: /admin');
    exit;
});
// Правка сотрудника: логин/пароль/роль (роль из таблицы убрана — всё здесь)
Routes::post('/admin/roles/update', function () {
    AdminAuth::auth();
    $admin = new Admin();
    if (!$admin->hasRole((int) (Session::init('admin')['auth'][1] ?? 0), 'admin')) {
        header('Location: /admin');
        exit;
    }
    $me = $admin->getUsername((int) (Session::init('admin')['auth'][1] ?? 0));
    $old = (string) ($_POST['old_username'] ?? '');
    if (strtolower($old) === strtolower(Admin::OWNER) && strtolower($me) !== strtolower(Admin::OWNER)) {
        header('Location: /admin');
        exit;
    }
    $res = $admin->updateManager($old, (string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''), (string) ($_POST['role'] ?? 'manager'));
    header('Location: /admin?message_status=' . ($res['status'] === 'ok' ? 'success' : 'error') . '&message_msg=' . urlencode($res['message']));
    exit;
});
// Завершить все сессии сотрудника
Routes::post('/admin/roles/kill', function () {
    AdminAuth::auth();
    $admin = new Admin();
    if (!$admin->hasRole((int) (Session::init('admin')['auth'][1] ?? 0), 'admin')) {
        header('Location: /admin');
        exit;
    }
    $me = $admin->getUsername((int) (Session::init('admin')['auth'][1] ?? 0));
    $u = (string) ($_POST['username'] ?? '');
    if (strtolower($u) === strtolower(Admin::OWNER) && strtolower($me) !== strtolower(Admin::OWNER)) {
        header('Location: /admin');
        exit;
    }
    $admin->killSessions($u);
    header('Location: /admin');
    exit;
});
// Сотрудники одним JSON (ленивая загрузка раздела)
Routes::get('/api/admin/staff', function () {
    AdminAuth::requirePermission('roles');
    header('Content-Type: application/json; charset=UTF-8');
    $users = (new \Setting\Route\Function\Controllers\Admin\Group\Tools\GetUsers())->getUsers();
    $rows = [];
    foreach ((\is_array($users) ? $users : []) as $u) {
        $rows[] = [
            'username' => (string) ($u['username'] ?? ''),
            'role' => (string) ($u['role'] ?? ''),
            'permissions' => array_keys((array) ($u['permissions'] ?? [])),
        ];
    }
    echo json_encode([
        'status' => 'ok',
        'users' => $rows,
        'perms' => Admin::FULL_PERMISSIONS,
        'roles' => array_keys(Admin::ROLES),
        'templates' => Admin::ROLE_PERMISSIONS,
        'audit' => \Setting\Route\Function\Controllers\Admin\LogViewer::render(
            \Setting\Route\Function\Controllers\Admin\LogViewer::tail(300), 'audit'
        ),
    ], JSON_UNESCAPED_UNICODE);
    exit;
});
//POST
Routes::post('/admin/logout', [AdminAuth::class, 'onLogout']);
Routes::post('/admin/save', [AdminDatabase::class, 'onAdminSave']);
Routes::post('/admin/delete', [AdminDatabase::class, 'onAdminDelete']);
Routes::post('/admin/addClientDays', [AdminXray::class, 'onAdminAddClientDays']);
Routes::post('/admin/addClientHours', [AdminXray::class, 'onAdminAddClientHours']);
Routes::post('/admin/addClientMinutes', [AdminXray::class, 'onAdminAddClientMinutes']);
Routes::post('/admin/reduceClient', [AdminXray::class, 'onAdminReduceClient']);
Routes::post('/admin/cleanlogs', [AdminXray::class, 'onAdminCleanLogs']);
Routes::post('/admin/login', [AdminAuth::class, 'onLogin']);
Routes::post('/admin/getUser', function () {
    (new AdminXray())->getAdminUser($_POST['uniID'] ?? '');
});
Routes::post('/admin/addUser', function () {
    AdminAuth::requirePermission('add_user');
    (new AdminDatabase())->addUser($_POST);
});
