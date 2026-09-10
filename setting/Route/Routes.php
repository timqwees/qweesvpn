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
use Setting\Route\Function\Controllers\Refer\Refer;

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
    $admin->changeRole($_POST['username'] ?? '', $_POST['role'] ?? 'manager');
    header('Location: /admin');
    exit;
});
//POST
Routes::post('/admin/logout', [AdminAuth::class, 'onLogout']);
Routes::post('/admin/save', [AdminDatabase::class, 'onAdminSave']);
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