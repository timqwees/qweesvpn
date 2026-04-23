<?php declare(strict_types=1);

use App\Models\Router\Routes;
use App\Config\Database;
use App\Config\Session;
use App\Models\Network\Network;
use App\Controllers\AuthController;
use App\Controllers\MailController;
use App\Models\Article\Article;
use App\Models\Network\Message;
use App\Models\User\User;
use Setting\Route\Function\Functions;
use App\Controllers\API\API;
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\language\LanguageSwitch;
use Setting\Route\Function\Controllers\kassa\PaymentController;
use Setting\Route\Function\Controllers\vpn\v2ray\Xray;
use Setting\Route\Function\Controllers\OS\OS;
use Setting\Route\Function\Controllers\admin\Price\Price;
use Setting\Route\Function\Controllers\client\Client;
use Setting\Route\Function\Controllers\kassa\Kassa;
use Setting\Route\Function\Controllers\language\Language;
use Setting\Route\Function\Controllers\profile\Profile;
use Setting\Route\Function\Controllers\refer\Refer;
use Setting\Route\Function\Controllers\refer\bonus\Bonus;
use Setting\Route\Function\Controllers\refer\config\ReferConfig;
use Setting\Route\Function\Controllers\system\SystemInfo;
use Setting\Route\Function\Controllers\vpn\Math;
use Setting\Route\Function\Controllers\vpn\VpnStatus;

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
//=============================================//REFERRAL
Routes::post('/api/referral/activate', function(){
    (new Refer())->onValidateCode($_POST['code'] ?? '', $_POST['online'] ?? '');
});
Routes::get('/reflink={code}', [Refer::class, 'onValidateCode']);
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