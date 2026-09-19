<?php

declare(strict_types=1);

use App\Models\Router\Routes;
use App\Config\Session;
use Setting\Route\Function\Controllers\Admin\{AdminDatabase, AdminXray, PdfController, AdminAuth, Admin};
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
//=============================================//DELETE
Routes::post('/api/subscription/delete', [Xray::class, 'DeleteKey']);
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
    AdminAuth::auth();
    Chat::json(['dialogs' => (new Chat())->getDialogs()]);
});
Routes::get('/api/chat/dialog', function () {
    AdminAuth::auth();
    Chat::json(['messages' => (new Chat())->getDialog(trim((string) ($_GET['uniID'] ?? '')))]);
});
Routes::post('/api/chat/reply', function () {
    AdminAuth::auth();
    $uniID = trim((string) ($_POST['uniID'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    if ($uniID === '' || $message === '') Chat::error('Нет данных');
    $chat = new Chat();
    $chat->reply($uniID, mb_substr($message, 0, 2000))
        ? Chat::json(['message' => $message, 'messages' => $chat->getDialog($uniID)])
        : Chat::error('Не удалось отправить');
});
Routes::post('/api/chat/close', function () {
    AdminAuth::auth();
    $uniID = trim((string) ($_POST['uniID'] ?? ''));
    if ($uniID === '') Chat::error('Нет данных');
    (new Chat())->closeDialog($uniID)
        ? Chat::json()
        : Chat::error('Не удалось закрыть');
});
Routes::post('/api/chat/clear', function () {
    AdminAuth::auth();
    $uniID = trim((string) ($_POST['uniID'] ?? ''));
    if ($uniID === '') Chat::error('Нет данных');
    (new Chat())->clearDialog($uniID)
        ? Chat::json()
        : Chat::error('Не удалось очистить');
});
Routes::get('/api/chat/userinfo', function () {
    AdminAuth::auth();
    $user = (new Chat())->getUserInfo(trim((string) ($_GET['uniID'] ?? '')));
    if (empty($user)) Chat::error('Пользователь не найден');
    Chat::json(['user' => $user]);
});
//=============================================//CHAT PHOTO
Routes::post('/api/chat/upload', function () {
    $chat = new Chat();
    if ((bool) (Session::init('admin')['auth'][0] ?? false)) {//админ шлет в выбранный диалог
        AdminAuth::auth();
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
Routes::post('/admin/roles/role', function () {
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
    $admin->changeRole($_POST['username'] ?? '', $_POST['role'] ?? 'manager');
    header('Location: /admin');
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
    (new AdminDatabase())->addUser($_POST);
});
//=============================================//EXPORT
Routes::get('/export/pdf', [PdfController::class, 'handleExport']);