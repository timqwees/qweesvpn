<?php

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

//=============================================//MAIN
Routes::get('/', 'on_Main');
//=============================================//AUTH
//get
Routes::get('/auth/login', 'on_Login');
Routes::get('/auth/regist', 'on_Regist');
//post
Routes::post('/auth/login', [Auth::class, 'onLogin']);
Routes::post('/auth/regist', [Auth::class, 'onRegist']);
//helpers
Routes::post('/auth/mail', [Auth::class, 'onMail']);
Routes::post('/auth/find', [Auth::class, 'isFindUser']);
//=============================================//PAY
Routes::get('/pay', 'on_Pay');










// //==================================================================================================//DB CHECK
// Routes::post('/', 'Listener');
// //==================================================================================================//PROFILE
// Routes::get('/profile', 'on_Profile');
// //==================================================================================================//PROFILE
// Routes::get('/profile/card', 'on_Profile_card');
// //==================================================================================================//DOWNLOAD
// Routes::get('/download', 'on_Download');
// //==================================================================================================//PAY
// Routes::get('/pay', 'on_Pay');
// Routes::post('/pay', 'on_Pay');
// //==================================================================================================//FAILD
// Routes::get('/faild', 'on_Faild');
// //==================================================================================================//CRASHDAMP_BUY_FREE
// Routes::get('/crashdamp_buy_free', 'on_Crashdamp_Buy_Freekey');
// //==================================================================================================//SITE
// Routes::get('/site', 'on_Site');
// //==================================================================================================//POLITIC
// Routes::get('/politic', 'on_Politic');
// //==================================================================================================//POLITIC
// Routes::get('/ref_success', 'on_RefSuccess');
// //==================================================================================================//TG_BOT
// Routes::get('/webhook/tg_bot', 'WebhookTgBot');
// Routes::post('/webhook/tg_bot', 'WebhookTgBot');
// //==================================================================================================//uprefers
// Routes::get('/uprefers', function () {
//   (new Functions)->GlobalCheckRefer();
//   Message::set('refer_success', 'Реферное обновление прошла успешно!');
//   Network::onRedirect('/profile');
// });
// //==================================================================================================//CLEANUP
// Routes::post('/autopay/{' . $_ENV['SECKRET_KEY_AUTOPAY'] . '}', function ($key) {
//   $key === $_ENV['SECKRET_KEY_AUTOPAY'] ? (new Functions)->AutoPay() : Network::onRedirect('/');
// });
// //==================================================================================================//ADMIN PANEL
// Routes::get('/admin/{id}', function ($id, $path = '/public/pages/telegram/admin/index.php') {
//   if (intval($id) === intval($_ENV['ADMIN_ID_1'] ?? 0) || intval($id) === intval($_ENV['ADMIN_ID_2'] ?? 0)) {
//     Routes::auto_element(dirname(__DIR__, 2) . $path);
//   } else {
//     Network::onRedirect('/profile');
//   }
// });
// //==================================================================================================//ADMIN PANEL -> TABLE
// Routes::get('/admin/{id}/table', function ($id, $path = '/public/pages/telegram/admin/table/index.php') {
//   if (intval($id) === intval($_ENV['ADMIN_ID_1'] ?? 0) || intval($id) === intval($_ENV['ADMIN_ID_2'] ?? 0)) {
//     Routes::auto_element(dirname(__DIR__, 2) . $path);
//   } else {
//     Network::onRedirect('/profile');
//   }
// });
// //==================================================================================================//ADMIN PANEL -> PARTNERS
// Routes::get('/admin/{id}/partners', function ($id, $path = '/public/pages/telegram/admin/partners/index.php') {
//   if (intval($id) === intval($_ENV['ADMIN_ID_1'] ?? 0) || intval($id) === intval($_ENV['ADMIN_ID_2'] ?? 0)) {
//     Routes::auto_element(dirname(__DIR__, 2) . $path);
//   } else {
//     Network::onRedirect('/profile');
//   }
// });
// //==================================================================================================//CLEANUP
// Routes::post('/cleanup', function () {
//   Functions::CleanUP();
//   Network::onRedirect('/');
//   $client = (new Functions())->client($_SESSION['client'] ?? 0);
//   //ЛОГИРОВАНИЕ
//   file_put_contents(
//     $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//     sprintf(
//       "[%s] [ГЛОБАЛЬНАЯ ОЧИСТКА] cleanup: Глобальная очистка истекших клиентов инициирована пользователем username=%s (name: %s)\n",
//       date('Y-m-d H:i:s'),
//       $client['tg_username'] ?? '-',
//       $client['tg_first_name'] ?? '-'
//     ),
//     FILE_APPEND
//   );
// });
// //==================================================================================================//DELETE SUBSCRIPTION
// // TODO: добавить систему ввода почты для оповещения.
// Routes::get('/delete_key/{tg_id}', function ($tg_id) {
//   (new Functions())->DeleteKey($tg_id);
//   // 	$mailler = new MailController();
//   // $client_mail = '???';
//   // 	ob_start();//запустим буфиризацию дабы получаем строку из инклуда
//   // 	include_once dirname(__DIR__, 2) . '/public/assets/componets/mail/delete_key.php';
//   // 	$body = ob_get_clean();
//   // 	$mailler->onMail($client_mail, 'Удаление ключа - CoraVPN Unlimited', $body);
//   Network::onRedirect('/');
//   $client = (new Functions())->client($_SESSION['client'] ?? 0);
//   //ЛОГИРОВАНИЕ
//   file_put_contents(
//     $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//     sprintf(
//       "[%s] [УДАЛЕНИЕ КЛЮЧА] delete_sub: Запрошено удаление ключа от username=%s (name: %s)\n",
//       date('Y-m-d H:i:s'),
//       $client['tg_username'] ?? '-',
//       $client['tg_first_name'] ?? '-'
//     ),
//     FILE_APPEND
//   );
// });
// //==================================================================================================//SUCCESS
// Routes::get('/success', function ($success_path = '/public/pages/site/success/index.php', $error_path = '/public/pages/site/error/index.php') {
//   $paymentStatus = false;

//   // Пытаемся получить tg_id из URL параметров (когда приходит из браузера после оплаты)
//   $tg_id_from_url = isset($_GET['tg_id']) ? strval($_GET['tg_id']) : null;
//   $pay_type_from_url = isset($_GET['pay_type']) ? strval($_GET['pay_type']) : null;

//   // Если tg_id есть в URL, используем его напрямую, иначе пробуем из сессии
//   if (!empty($tg_id_from_url)) {
//     // Восстанавливаем сессию для браузера
//     Session::init();
//     $_SESSION['client'] = intval($tg_id_from_url);
//     // Восстанавливаем selection_pay_type из URL параметров, если он есть, иначе используем значение по умолчанию
//     $_SESSION['selection_pay_type'] = !empty($pay_type_from_url) ? $pay_type_from_url : 'pay_tarif';
//     $client = (new Functions())->client(intval($tg_id_from_url));
//   } else {
//     // Если tg_id нет в URL, используем сессию (для Telegram WebApp)
//     Session::init();
//     // Убеждаемся, что selection_pay_type установлен, если его нет в сессии
//     if (!isset($_SESSION['selection_pay_type'])) {
//       $_SESSION['selection_pay_type'] = 'pay_tarif';
//     }
//     $client = (new Functions())->client($_SESSION['client'] ?? 0);
//   }

//   // Если клиент все еще не найден, пробуем найти по payment_id из URL (если ЮKassa передает)
//   if (empty($client) || empty($client['tg_id'])) {
//     $payment_id_from_url = isset($_GET['payment_id']) ? strval($_GET['payment_id']) : null;
//     if (!empty($payment_id_from_url)) {
//       // Ищем пользователя по kassa_id в базе данных
//       $user_result = Database::send('SELECT tg_id FROM vpn_users WHERE kassa_id = ? LIMIT 1', [$payment_id_from_url]);
//       if (is_array($user_result) && !empty($user_result) && isset($user_result[0]['tg_id'])) {
//         $found_tg_id = $user_result[0]['tg_id'];
//         Session::init();
//         $_SESSION['client'] = intval($found_tg_id);
//         // Устанавливаем значение по умолчанию для pay_type, если его нет в URL
//         if (empty($pay_type_from_url)) {
//           $_SESSION['selection_pay_type'] = 'pay_tarif';
//         }
//         $client = (new Functions())->client(intval($found_tg_id));
//       }
//     }
//   }

//   $freekey_check = $_ENV['VPN_FREE_CLIENT_FREEKEY'] ?: 'used_free';
//   $tg_id = $client['tg_id'] ?: 0;

//   // Проверяем состояние vpn_freekey
//   if (isset($client['vpn_freekey']) && $client['vpn_freekey'] === $freekey_check) {
//     // Бесплатная подписка — отображаем страницу успеха
//     $paymentStatus = true;
//   } else {
//     // Если не бесплатная — значит оплата (kassa) после нажатии на кнопку в базу заноситься kassa_id
//     if (!isset($client['kassa_id']) || empty($client['kassa_id'])) {
//       // не приступил к странице оплате — страница ошибки
//       $paymentStatus = false;
//     } else {// посетил страницу оплаты (kassaid = есть в базе)
//       $status = (new Functions())->YkassaCheck($client['kassa_id']); // true/false
//       if ($status === true) { //оплата была проведена

//         // Получаем тип оплаты из сессии, если не указан - по умолчанию 'pay_tarif' (основной поток оплаты)
//         $selection_pay_type = isset($_SESSION['selection_pay_type']) ? $_SESSION['selection_pay_type'] : 'pay_tarif';

//         if ($selection_pay_type === 'connect_card') {//определенный выбор оплаты на страицах задаеться
//           // Получаем данные платежа
//           $paymentData = (new Functions)->getPaymentDetails($client['kassa_id']);

//           // Сохраняем payment_method.id в card_token
//           if (!empty($paymentData['payment_method']['id'])) {
//             Database::send(
//               'UPDATE vpn_users SET card_token = ?, yk_autopay_active = 1 WHERE tg_id = ?',
//               [strval($paymentData['payment_method']['id']), strval($tg_id)]
//             );
//           } else {
//             // ЛОГИРУЕМ
//             file_put_contents(
//               $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//               sprintf(
//                 "[%s] [ОШИБКА - ПРИВЯЗКА СЧЕТА] Не удалось получить card_token (payment_method.id) для tg_id=%s, paymentData: %s\n",
//                 date('Y-m-d H:i:s'),
//                 strval($tg_id),
//                 json_encode($paymentData, JSON_UNESCAPED_UNICODE)
//               ),
//               FILE_APPEND
//             );
//           }
//         } elseif ($selection_pay_type === 'pay_tarif') {
//           // Оплата успешна — сразу создаём клиента без редиректа
//           $add_return = (new Functions())->add_client($client['vpn_date_count'], $client['tg_username'], $client['vpn_divece_count']);
//           if (!empty($add_return)) {
//             // Получаем данные платежа
//             $paymentData = (new Functions)->getPaymentDetails($client['kassa_id']);

//             // Сохраняем payment_method.id в card_token
//             if (!empty($paymentData['payment_method']['id'])) {
//               if ($client['vpn_freekey'] === 'no_used' && $client['yk_autopay_active'] === 0 || $client['vpn_freekey'] === 'used' && $client['yk_autopay_active'] === 0) {//впервые оплатил
//                 Database::send(
//                   'UPDATE vpn_users SET card_token = ?, yk_autopay_active = 1 WHERE tg_id = ?',
//                   [strval($paymentData['payment_method']['id']), strval($tg_id)]
//                 );
//               } else {//не в первые, менять статус автоплатежа не нужно
//                 Database::send(
//                   'UPDATE vpn_users SET card_token = ? WHERE tg_id = ?',
//                   [strval($paymentData['payment_method']['id']), strval($tg_id)]
//                 );
//               }
//             } else {
//               // ЛОГИРУЕМ
//               file_put_contents(
//                 $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//                 sprintf(
//                   "[%s] [ОШИБКА - ПРИВЯЗКА СЧЕТА - ТАРИФ] Не удалось получить card_token (payment_method.id) для tg_id=%s, paymentData: %s\n",
//                   date('Y-m-d H:i:s'),
//                   strval($tg_id),
//                   json_encode($paymentData, JSON_UNESCAPED_UNICODE)
//                 ),
//                 FILE_APPEND
//               );
//             }

//             // Получаем сумму из данных платежа и устанавливаем её как vpn_amount
//             $paymentAmount = 0;
//             if (!empty($paymentData['amount']['value'])) {
//               $paymentAmount = intval($paymentData['amount']['value']);
//             }

//             Database::send(
//               'UPDATE vpn_users SET vpn_subscription = ?, vpn_status = ?, vpn_uuid = ?, vpn_freekey = ?, vpn_date_count = ?, vpn_divece_count = ?, vpn_amount = ? WHERE tg_id = ?',
//               [
//                 strval($add_return['vpn_subscription']),
//                 strval($add_return['vpn_status']),
//                 strval($add_return['vpn_uuid']),
//                 'buy',
//                 strval($client['vpn_date_count']),
//                 strval($client['vpn_divece_count']),
//                 $paymentAmount, // Используем сумму из платежа YooKassa
//                 strval($tg_id)
//               ]
//             );
//             //бонус выдаем в конце
//             (new Functions())->add_bonus_days($client['tg_id']);
//           }
//         }
//         $paymentStatus = true;
//       } elseif ($status === false) {
//         // Однозначный отказ или ошибка при оплате
//         $paymentStatus = false;
//       } elseif ($status === 'pending' || $status === 'waiting_for_capture' || $status === 'canceled') {
//         // Оплата не была завершена или отменена (или возвратился назад)
//         $paymentStatus = false;
//       } else {
//         // Другие статусы (зависят от логики YkassaCheck)
//         $paymentStatus = $status;
//       }
//     }
//   }

//   $mail_to = isset($_GET['mail']) ? strval($_GET['mail']) : null;
//   $mailler = new MailController();
//   if (boolval($paymentStatus) === true) {
//     /*
//     3 случая успеха у нас есть
//     - после покупки buy, но в начале no_used, а после бесплатной used
//     - used_free
//     используем if - else
//     */
//     if (isset($client['vpn_freekey']) && in_array($client['vpn_freekey'], ["buy", "no_used", "used"])) {
//       ob_start();
//       include_once dirname(__DIR__, 2) . '/public/assets/componets/mail/success_buy.php';
//       $body = ob_get_clean();

//       // Временно отключаем отправку email для избежания ошибок PHPMailer
//       // TODO: Исправить проблему с PHPMailer и включить обратно
//       /*
//       // Получаем email из сессии или из данных клиента
//       $email = $_SESSION['temporary_email'] ?? $mail_to;

//       // Если email не найден в сессии, попробуем получить из данных клиента
//       if (empty($email) || $email === $mail_to) {
//         // Здесь можно добавить логику получения email из профиля клиента
//         // Но пока используем mail_to как запасной вариант
//         $email = $mail_to;
//       }

//       $mailler->onMail(
//         $email,
//         'Оплата успешна - CoraVPN Unlimited',
//         $body
//       );
//       */
//       // ЛОГИРУЕМ
//       file_put_contents(
//         $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//         sprintf("[%s] [УСПЕШНО - ОПЛАТА ТАРИФА] pay_sub: Успешная оплата клиента (%s) (name: %s)\n", date('Y-m-d H:i:s'), $client['tg_username'] ?? '-', $client['tg_first_name'] ?? '-'),
//         FILE_APPEND
//       );
//     } else {//Бесплатная
//       ob_start();
//       include_once dirname(__DIR__, 2) . '/public/assets/componets/mail/success_free.php';
//       $body = ob_get_clean();

//       // Получаем email из сессии или из данных клиента
//       $email = $_SESSION['temporary_email'] ?? $mail_to;

//       // Если email не найден в сессии, попробуем получить из данных клиента
//       if (empty($email) || $email === $mail_to) {
//         // Здесь можно добавить логику получения email из профиля клиента
//         // Но пока используем mail_to как запасной вариант
//         $email = $mail_to;
//       }

//       $mailler->onMail(
//         $email,
//         'Бесплатная подписка - CoraVPN Unlimited',
//         $body
//       );
//       // Лог бесплатной подписки
//       file_put_contents(
//         $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//         sprintf("[%s] [УСПЕШНО - БЕСПЛАТНАЯ ПОДПИСКА] free_sub: Клиент получил бесплатную подписку (tg_id: %s, email: %s)\n", date('Y-m-d H:i:s'), $client['tg_id'] ?? '-', $email ?? '-'),
//         FILE_APPEND
//       );
//     }

//     // ============ Открываем страницу успеха
//     Routes::auto_element(dirname(__DIR__, 2) . $success_path);
//   } else {// ошибка
//     if (isset($client['vpn_freekey']) && $client['vpn_freekey'] !== 'buy') {

//       //=========================================================
//       //заносим почтове уведомлеия в проверку дабы быть готовым к непредвидинным ошибкам где сообщение об оплате не уместно
//       ob_start();//запустим буфиризацию дабы получаем строку из инклуда
//       include_once dirname(__DIR__, 2) . '/public/assets/componets/mail/error.php';
//       $body = ob_get_clean();

//       // Временно отключаем отправку email для избежания ошибок PHPMailer
//       // TODO: Исправить проблему с PHPMailer и включить обратно
//       //=========================================================

//       Database::send(
//         'UPDATE vpn_users SET vpn_subscription = ?, vpn_status = ?, vpn_uuid = ?, vpn_freekey = ?, vpn_date_count = ?, vpn_divece_count = ? WHERE tg_id = ?',
//         [
//           strval(''),
//           intval(0),
//           strval(''),
//           'used',
//           intval(0),
//           strval($tg_id)
//         ]
//       );
//       // Лог ошибки оплаты
//       file_put_contents(
//         $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//         sprintf("[%s] [ОШИБКА - БЕСПЛАТНАЯ ПОДПИСКА] free_sub: Ошибка при предоставлении бесплатной подписки клиенту (%s) (email: -)\n", date('Y-m-d H:i:s'), $client['tg_id'] ?? '-'),
//         FILE_APPEND
//       );
//     }

//     // ============ Открываем страницу ошибки
//     Routes::auto_element(dirname(__DIR__, 2) . $error_path);
//   }
// });
// //==================================================================================================//BIND
// Routes::get('/autopay/bind/{tg_id}', function ($tg_id) {
//   //TODO Проверка CSRF не реализован — при необходимости добавить
//   $functions = new Functions();

//   $success = $functions->EnableAutopay($tg_id);

//   if ($success) {
//     Message::set('success_card', 'Автоплатёж успешно подключён');
//     Network::onRedirect('/profile/card');
//   } else {
//     Message::set('error_card', 'ошибка: ' . $success);
//     Network::onRedirect('/profile/card');
//   }
// });
// //==================================================================================================//UNBIND
// Routes::get('/autopay/unbind/{tg_id}', function ($tg_id) {

//   //TODO Проверка CSRF не реализован — при необходимости добавить
//   $functions = new Functions();

//   $success = $functions->DisableAutopay($tg_id);

//   if ($success) {
//     Message::set('success_card', 'Автоплатёж успешно отключён');
//     Network::onRedirect('/profile/card');
//   } else {
//     Message::set('error_card', 'ошибка: ' . $success);
//     Network::onRedirect('/profile/card');
//   }
// });
// //==================================================================================================//DELETE-KEY
// Routes::get('/autopay/delete-card/{tg_id}', function ($tg_id) {

//   //TODO Проверка CSRF не реализован — при необходимости добавить
//   $functions = new Functions();

//   $success = $functions->UnlinkCard($tg_id);

//   if ($success) {
//     Message::set('success_card', 'успех');
//     Network::onRedirect('/profile/card');
//   } else {
//     Message::set('error_card', 'ошибка: ' . $success);
//     Network::onRedirect('/profile/card');
//   }
// });
// //==================================================================================================//VIDEO SERVE
// Routes::get('/assets/video/{filename}', function ($filename) {
//   $videoPath = dirname(__DIR__, 2) . '/public/assets/video/' . basename($filename);

//   if (!file_exists($videoPath)) {
//     http_response_code(404);
//     exit('Video not found');
//   }

//   $extension = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));
//   $mimeTypes = [
//     'mp4' => 'video/mp4',
//     'webm' => 'video/webm',
//     'ogg' => 'video/ogg',
//     'avi' => 'video/x-msvideo',
//     'mov' => 'video/quicktime',
//     'gif' => 'image/gif'
//   ];

//   $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

//   header('Content-Type: ' . $mimeType);
//   header('Content-Length: ' . filesize($videoPath));
//   header('Cache-Control: public, max-age=31536000');
//   header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));

//   readfile($videoPath);
//   exit;
// });
// //==================================================================================================//ADD CLIENT
// // Логика ячейки vpn_freekey:
// // - "no_used": Новый клиент, ещё не активировал (можем выдать бесплатный)
// // - "used": Бесплатная активирована и уже использована/завершена (Купить подписку)
// // - "buy": Купленная подписка, но v2Ray не видел подключения (Подключить подписку)
// // - "used_free": Активна бесплатная подписка и не подключено в v2Ray (Подключить подписку)
// Routes::get('/add_vpn_user/{tg_id}/{days_limit}/{tg_username}/{divice_limit}/{amount}/{freekey}', function ($tg_id, $days_limit, $tg_username, $divice_limit, $amount, $freekey = '') {
//   try {
//     // Проверяем параметры
//     if (empty($tg_id) || empty($days_limit) || empty($tg_username)) {
//       // ЛОГИРУЕМ
//       file_put_contents(
//         $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//         sprintf("[%s] [ОШИБКА - ДОБАВЛЕНИЕ КЛИЕНТА] add_client: Попытка создания клиента с некорректными параметрами (tg_id: %s, days_limit: %s, tg_username: %s)\n", date('Y-m-d H:i:s'), $tg_id ?? '-', $days_limit ?? '-', $tg_username ?? '-'),
//         FILE_APPEND
//       );
//       return;
//     }

//     // Создаем клиента VPN
//     $functions = new Functions();
//     $add_return = $functions->add_client($days_limit, $tg_username, $divice_limit);

//     if (!is_array($add_return) || empty($add_return['vpn_subscription']) || empty($add_return['vpn_status']) || empty($add_return['vpn_uuid'])) {
//       // ЛОГИРУЕМ
//       file_put_contents(
//         $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//         sprintf("[%s] [ОШИБКА - ДОБАВЛЕНИЕ КЛЕИНТА] add_client: Не удалось создать клиента VPN (tg_id: %s, days_limit: %s, tg_username: %s)\n", date('Y-m-d H:i:s'), $tg_id ?? '-', $days_limit ?? '-', $tg_username ?? '-'),
//         FILE_APPEND
//       );
//       return;
//     }

//     // Проверяем существование пользователя
//     $current_user = Database::send('SELECT * FROM vpn_users WHERE tg_id = ?', [strval($tg_id)]);
//     if (empty($current_user)) {
//       // ЛОГИРУЕМ
//       file_put_contents(
//         $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//         sprintf("[%s] [ОШИБКА - ДОБАВЛЕНИЕ КЛИЕНТА] add_client: Пользователь с tg_id %s не найден в базе данных\n", date('Y-m-d H:i:s'), $tg_id ?? '-'),
//         FILE_APPEND
//       );
//       return;
//     }

//     // Обновляем данные пользователя
//     $result = Database::send(
//       'UPDATE vpn_users SET vpn_subscription = ?, vpn_status = ?, vpn_uuid = ?, vpn_freekey = ?, vpn_date_count = ?, vpn_divece_count = ?, vpn_amount = ? WHERE tg_id = ?',
//       [
//         strval($add_return['vpn_subscription']),
//         strval($add_return['vpn_status']),
//         strval($add_return['vpn_uuid']),
//         strval($freekey),
//         intval($days_limit),
//         strval($divice_limit),
//         intval($amount),
//         strval($tg_id)
//       ]
//     );

//     if ($result === false) {
//       // ЛОГИРУЕМ
//       file_put_contents(
//         $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//         sprintf("[%s] [ОШИБКА - ДОБАВЛЕНИЕ КЛИЕНТА] Не удалось обновить данные пользователя (tg_id: %s)\n", date('Y-m-d H:i:s'), $tg_id ?? '-'),
//         FILE_APPEND
//       );
//       return;
//     }

//     // редирект на страницу успеха
//     Network::onRedirect('/success');
//   } catch (Exception $e) {
//     file_put_contents(
//       $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
//       sprintf("[%s] [ОШИБКА - ДОБАВЛЕНИЕ КЛИЕНТА] %s\n", date('Y-m-d H:i:s'), $e->getMessage()),
//       FILE_APPEND
//     );
//   }
// });

/*
##======ПРИМЕРЫ/EXEMPLES===========##
# main (path, function)
# Routes::get('/', 'on_Main');

# exemple 2 (path, manual function)
# Routes::get('/', function(){ echo 'hello'; });

# exemple 3 (path, [class, function name])
# Routes::get('/', [App\Models\Router\Routes::class, 'on_Main']);

# exemple 4 (parametrs)
Routes::get('/user/{id}', function($id){ echo $id; });

# exemple 5 (API)
Routes::get('/api/getQwees', function() { echo API::send('/public/pages/main/qwees.json'); });
Routes::post('/api/setQwees', function() { echo API::send('/public/pages/main/qwees.json'); });
// short version
Routes::get('/api/getQwees', fn() => print(API::send('/public/pages/main/qwees.json')));
Routes::post('/api/setQwees', fn() => print(API::send('/public/pages/main/qwees.json')));
*/
