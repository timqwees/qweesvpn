<?php declare(strict_types=1);
namespace Setting\Route\Function;

use App\Models\Router\Routes;
use App\Config\Database;
use App\Config\Session;
use App\Models\Network\Network;
use App\Controllers\AuthController;
use App\Controllers\MailController;
use App\Models\Article\Article;
use App\Models\Network\Message;
use App\Models\User\User;
use Exception;
use LDAP\ResultEntry;
use App\Controllers\API\API;

class Functions
{

  // public $reflink;

  //################################# МАРШРУТЫ ######################################

  // Главная страница || Main page
  public function on_Main(
    $path = '/public/pages/main/index.php'
  ) {
    Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
  }

  // Главная страница || Main page
  public function on_Login(
    $path = '/public/pages/auth/login/index.php'
  ) {
    Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
  }

  // Главная страница || Main page
  public function on_Regist(
    $path = '/public/pages/auth/regist/index.php'
  ) {
    Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
  }

  // Главная страница || Main page
  public function on_Pay(
    $path = '/public/pages/pay/index.php'
  ) {
    Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
  }

  // Главная страница || Main page
  public function on_Instailler(
    $path = '/public/pages/download/index.php'
  ) {
    Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
  }

  // Стрнаца статуса || Pay page
  public function on_PayStatus(
    $path = '/public/pages/pay/status.php'
  ) {
    Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
  }

}
