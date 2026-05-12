<?php

declare(strict_types=1);

namespace Setting\Route\Function;

use App\Models\Router\Routes;
use App\Config\Session;

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
    public function on_About(
        $path = '/public/pages/about/about.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Главная страница || Main page
    public function on_Requisites(
        $path = '/public/pages/about/requisites.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Главная страница || Main page
    public function on_Regist(
        $path = '/public/pages/auth/regist/index.php'
    ) {
        // Сохраняем реферальный код из URL в сессию
        $refCode = $_GET['ref'] ?? '';
        if (!empty($refCode)) {
            Session::init('pending_refer_code', $refCode);
        }
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Главная страница || Main page
    public function on_Pay(
        $path = '/public/pages/pay/index.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Главная страница || Main page
    public function on_Install(
        $path = '/public/pages/install/index.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Стрнаца статуса || Pay page
    public function on_PayStatus(
        $path = '/public/pages/pay/status.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Главная страница || Main page
    public function on_Admin(
        $path = '/public/pages/admin/index.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Главная страница || Main page
    public function on_AdminLogin(
        $path = '/public/pages/admin/auth/index.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Страница просмотра таблицы БД || Database table view
    public function on_AdminDatabase(
        $path = '/public/pages/admin/database.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Страница редактирования записи || Edit record page
    public function on_AdminEdit(
        $path = '/public/pages/admin/edit.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
    }

    // Страница статистики || Stats page
    public function on_AdminStats(
        $path = '/public/pages/admin/stats.php'
    ) {
        Routes::auto_element(dirname(__DIR__, 3) . $path);
    }

    public static function site(): array
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'qweesvpn.online';
        $baseUrl = $protocol . $host;
        $url = $baseUrl . ($_SERVER['REQUEST_URI'] ?? '');

        return [
            'url' => $url,
            'baseUrl' => $baseUrl,
            'ООО' => 'QweesVPN',
            'описание' => 'Сетевой продукт от студии QweesTeam Studio.',
            'телефон' => '+7 (977) 777-09-94',
            'почта' => 'info@qweesvpn.online',
            'банк' => [
                'Банк' => 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ «ОЗОН БАНК»',
                'БИК' => '044525068',
                'Расчетный счет' => '40817810900001763261',
                'Корр. счет' => '30101810645374525068'
            ],
            'информация' => [
                'Полное название' => 'QweesTeam Studio',
                'ИНН' => '9703077050',
                'ОГРН' => '',
                'КПП' => '770301001'
            ],
            'контакты' => [
                'Директор' => 'Нерсисян.А.А',
                'Почта' => 'info@qweesvpn.online',
                'Телефон' => '+7 (977) 777-09-94',
                'мессенджер' => [
                    'telegram' => '@qweesvpn_support'
                ]
            ],
            'сервера' => [
                'Нидерланды, Амстердам' => 'nl.qweesvpn.online'
            ],
            'студия' => 'Digital Innovation IT-Studio'
        ];
    }
}