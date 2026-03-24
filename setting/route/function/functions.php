<?php
declare(strict_types=1);//cтрогая типизация

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

  // // Профиль || Profile page
  // public function on_Profile($path = '/public/pages/telegram/profile/index.php')
  // {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path);
  // }

  // // Профиль карты || Profile card page
  // public function on_Profile_card($path = '/public/pages/telegram/profile/card/index.php')
  // {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path);
  // }

  // // Скачивание || Download page
  // public function on_Download($path = '/public/pages/telegram/download/index.php')
  // {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path);
  // }

  // // Оплата || Pay page
  // public function on_Pay($path = '/public/pages/telegram/pay/index.php')
  // {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path);
  // }

  // // Ошибка оплаты || Error page
  // public function on_Faild($path = '/public/pages/site/error/index.php')
  // {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path);
  // }

  // // Главная страница на сайте || Site page
  // public function on_Site($path = '/public/pages/site/main/index.php')
  // {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path);
  // }

  // // Ошибка начала оплаты || CrashPay page
  // public function on_Crashdamp_Buy_Freekey($path = '/public/pages/telegram/pay/crash/index.php')
  // {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path);
  // }

  // // Политика конфидициальности || Politic page
  // public function on_Politic($path = '/public/pages/site/politic/index.php')
  // {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path);
  // }

  // //WebHook TG bot
  // public function WebhookTgBot($path = '/public/pages/telegram_bot/constructor.php')
  // {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path);
  // }

  // // Страница успеха || Success page
  // public function on_RefSuccess(
  //   $path = '/public/pages/site/ref_success/index.php'
  // ) {
  //   Routes::auto_element(dirname(__DIR__, 3) . $path); // мини добавка элементов проверок
  // }

  // //################################# VPN FUNCTIONS ######################################

  // /**
  //  * Создаёт нового клиента VPN на сервере XUI через API, возвращает его параметры для дальнейшей регистрации в системе.
  //  *
  //  * Производит авторизацию в панели XUI, получает список инбаундов (inbounds),
  //  * формирует уникальные параметры (uuid/subId/email/expiry time), добавляет клиента в первый инбаунд,
  //  * возвращает параметры подключения для последующего сохранения в базе данных.
  //  *
  //  * @param int|string $tg_id        Telegram ID пользователя
  //  * @param int|string $days_limit   Количество дней действия подписки
  //  * @param string     $tg_username  Имя пользователя Telegram
  //  *
  //  * @return array|void              Ассоциативный массив с данными vpn_subscription, vpn_status и uuid, либо выводит ошибку
  //  */
  // public function add_client($days_limit, $tg_username, $device_limit = null)
  // {
  //   $base = $_ENV['XUI_URL_PANEL'] ?? 'https://nl.coravpn.online:12200/to';

  //   // Логин и получение куки
  //   $ch = curl_init($base . ($_ENV['XUI_URL_LOGIN'] ?: '/login'));
  //   curl_setopt_array($ch, [
  //     CURLOPT_POST => true,
  //     CURLOPT_POSTFIELDS => json_encode([
  //       'username' => $_ENV['XUI_LOGIN'] ?? 'timqwees',
  //       'password' => $_ENV['XUI_PASSWORD'] ?? 'timqwees1220066'
  //     ]),
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HEADER => true,
  //     CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
  //     CURLOPT_SSL_VERIFYPEER => true,
  //     CURLOPT_SSL_VERIFYHOST => 2,
  //   ]);
  //   $response = curl_exec($ch);
  //   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   $cookieName = $_ENV['XUI_LOGIN_NAME_COOKIE'] ?? 'x-ui';
  //   if ($code !== 200 || !preg_match('/Set-Cookie:\s*(' . $cookieName . '=[^;]+)/i', $response, $m)) {
  //     http_response_code(500);
  //     header('Content-Type: application/json; charset=utf-8');
  //     echo "Ошибка авторизации c сервером coravpn";
  //     return;
  //   }
  //   $cookie = $m[1];

  //   // Список inbounds
  //   $ch = curl_init($base . ($_ENV['XUI_URL_LIST'] ?? $_ENV['XUI_URL_API'] . 'list'));
  //   curl_setopt_array($ch, [
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
  //     CURLOPT_SSL_VERIFYPEER => true,
  //     CURLOPT_SSL_VERIFYHOST => 2,
  //   ]);
  //   $response = curl_exec($ch);
  //   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   $data = json_decode($response, true);
  //   if ($code !== 200 || !$data['success'] || empty($data['obj'][0])) {
  //     http_response_code(500);
  //     header('Content-Type: application/json; charset=utf-8');
  //     echo "Ошибка получения списка: inbounds";
  //     return;
  //   }

  //   // Подготовка inbound
  //   $inbound = $data['obj'][0];//получаем первый inbound
  //   $protocol = strtolower($inbound['protocol']);
  //   $settings = json_decode($inbound['settings'], true);

  //   if (!isset($settings['clients']) || !is_array($settings['clients'])) {
  //     $settings['clients'] = [];
  //   }

  //   // Генерация subId и email
  //   $subId = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 16) . '-' . $tg_username;
  //   $email = strval($tg_username);
  //   $expiry = (time() + ($days_limit * 86400)) * 1000; // ms

  //   // Ограничение по устройствам (limitIp)
  //   // Логика: если не передано - берём из ENV или по умолчанию 1
  //   if ($device_limit == null) {
  //     $device_limit = isset($_ENV['XUI_DEVICE_LIMIT']) ? intval($_ENV['XUI_DEVICE_LIMIT']) : 1;
  //   }

  //   if ($protocol === 'vmess' || $protocol === 'vless') {
  //     $uuid = sprintf(
  //       '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
  //       mt_rand(0, 0xffff),
  //       mt_rand(0, 0xffff),
  //       mt_rand(0, 0xffff),
  //       mt_rand(0, 0x0fff) | 0x4000,
  //       mt_rand(0, 0x3fff) | 0x8000,
  //       mt_rand(0, 0xffff),
  //       mt_rand(0, 0xffff),
  //       mt_rand(0, 0xffff)
  //     );
  //     $client = [
  //       'id' => $uuid,
  //       'email' => $email,
  //       'expiryTime' => $expiry,
  //       'subId' => $subId,
  //       'enable' => true,
  //       'totalGB' => 0,
  //       'limitIp' => $device_limit,
  //       'comment' => '',
  //       'created_at' => time() * 1000,
  //       'updated_at' => time() * 1000
  //     ];
  //     if ($protocol === 'vmess') {
  //       $client['alterId'] = 0;
  //     }
  //   } elseif ($protocol === 'trojan') {
  //     $client = [
  //       'password' => bin2hex(random_bytes(16)),
  //       'email' => $email,
  //       'expiryTime' => $expiry,
  //       'subId' => $subId,
  //       'enable' => true,
  //       'totalGB' => 0,
  //       'limitIp' => $device_limit,
  //       'comment' => '',
  //       'created_at' => time() * 1000,
  //       'updated_at' => time() * 1000
  //     ];
  //   } else {
  //     http_response_code(400);
  //     header('Content-Type: application/json; charset=utf-8');
  //     return;
  //   }

  //   $settings['clients'][] = $client;
  //   $inbound['settings'] = json_encode($settings);

  //   $ch = curl_init($base . ($_ENV['XUI_URL_ADD_CLIENT'] ?? $_ENV['XUI_URL_API'] . "update/") . $inbound['id']);
  //   curl_setopt($ch, CURLOPT_POST, true);
  //   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($inbound));
  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //   curl_setopt($ch, CURLOPT_HTTPHEADER, [
  //     'Cookie: ' . $cookie,
  //     'Content-Type: application/json'
  //   ]);
  //   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  //   $response = curl_exec($ch);
  //   $updateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   $updateUser = json_decode($response, true);

  //   if ($updateCode === 200 && isset($updateUser['success']) && $updateUser['success']) {
  //     // Для trojan используем password, для vmess/vless используем id
  //     $vpn_uuid = (string) (isset($client['id']) ? $client['id'] : (isset($client['password']) ? $client['password'] : ''));
  //     return [
  //       'vpn_subscription' => (string) ((isset($_ENV['XUI_URL_SUBSCRIPTION']) ? $_ENV['XUI_URL_SUBSCRIPTION'] : '') . $subId),
  //       'vpn_status' => (string) $expiry, /*ms миллисекнды для расчетов*/
  //       'vpn_uuid' => (string) $vpn_uuid, /*uuid пользователя или password для trojan*/
  //     ];
  //   } else {
  //     http_response_code(500);
  //     header('Content-Type: application/json; charset=utf-8');
  //     // ЛОГИРУЕМ
  //     file_put_contents(
  //       $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //       sprintf(
  //         "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] add_client: Ошибка добавления пользователя! Подробности: %s\n",
  //         date('Y-m-d H:i:s'),
  //         json_encode($updateUser, JSON_UNESCAPED_UNICODE)
  //       ),
  //       FILE_APPEND
  //     );
  //     echo json_encode([
  //       'error' => 'Ошибка добавления пользователя!',
  //       'detail' => $updateUser
  //     ], JSON_UNESCAPED_UNICODE);
  //   }
  // }

  // /**
  //  * Удаляет ключ (VPN пользователя) из панели XUI и обнуляет связанные данные в базе данных.
  //  *
  //  * Осуществляет авторизацию на XUI, ищет нужного клиента среди первого inbounds по uuid/email.
  //  * Если данные для удаления отсутствуют, просто обнуляет данные в БД.
  //  * Если найден, удаляет клиента через API XUI и сбрасывает значения в базе, возвращает статус результата.
  //  *
  //  * @param string|int $tg_id - Telegram ID пользователя
  //  * @return array            - Массив ["status" => "ok"|"partial"|"error", "message" => ...]
  //  */
  // public function DeleteKey($tg_id)
  // {
  //   // Получаем данные пользователя
  //   $client = $this->client($tg_id);
  //   $email = $client['tg_username'] ?? null;
  //   $uuid = $client['vpn_uuid'] ?? null;
  //   if (!$email || !$uuid) {
  //     Database::send(
  //       'UPDATE vpn_users SET vpn_uuid = ?, vpn_subscription = ?, vpn_status = ?, vpn_date_count = ?, vpn_freekey = ?, kassa_id = ?, vpn_divece_count = ?, vpn_amount = ? WHERE tg_id = ?',
  //       [strval(''), strval(''), intval(0), intval(0), strval('used'), strval(''), strval(''), strval(''), strval($tg_id)]
  //     );
  //     return ['status' => 'partial', 'message' => 'Удалён из БД, но не из X-UI (нет данных для удаления)'];
  //   }

  //   $base = $_ENV['XUI_URL_PANEL'] ?: 'https://nl.coravpn.online:12200/to';

  //   // 1. Авторизация
  //   $ch = curl_init($base . ($_ENV['XUI_URL_LOGIN'] ?: '/login'));
  //   curl_setopt_array($ch, [
  //     CURLOPT_POST => true,
  //     CURLOPT_POSTFIELDS => json_encode([
  //       'username' => $_ENV['XUI_LOGIN'] ?: 'timqwees',
  //       'password' => $_ENV['XUI_PASSWORD'] ?: 'timqwees1220066'
  //     ]),
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HEADER => true,
  //     CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
  //     CURLOPT_SSL_VERIFYPEER => true,
  //     CURLOPT_SSL_VERIFYHOST => 2,
  //   ]);
  //   $response = curl_exec($ch);
  //   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   $cookieName = $_ENV['XUI_LOGIN_NAME_COOKIE'] ?: 'x-ui';
  //   if ($code !== 200 || !preg_match('/Set-Cookie:\s*(' . $cookieName . '=[^;]+)/i', $response, $m)) {
  //     http_response_code(500);
  //     return ['status' => 'error', 'message' => 'Не удалось авторизоваться в X-UI'];
  //   }
  //   $cookie = $m[1];

  //   // 2. Получаем inbounds
  //   $ch = curl_init($base . ($_ENV['XUI_URL_LIST'] ?: $_ENV['XUI_URL_API'] . 'list'));
  //   curl_setopt_array($ch, [
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
  //     CURLOPT_SSL_VERIFYPEER => true,
  //     CURLOPT_SSL_VERIFYHOST => 2,
  //   ]);
  //   $response = curl_exec($ch);
  //   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   $data = json_decode($response, true);
  //   if ($code !== 200 || !$data['success'] || empty($data['obj'][0])) {
  //     http_response_code(500);
  //     return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
  //   }

  //   $inbound = $data['obj'][0];
  //   $inboundId = $inbound['id'];

  //   $clientId = $uuid;

  //   if (!$clientId) {
  //     return ['status' => 'error', 'message' => 'Не найден clientId для удаления'];
  //   }

  //   // 4. Удаляем клиента по clientId
  //   $ch = curl_init($base . $_ENV['XUI_URL_API'] . "{$inboundId}/delClient/{$clientId}");
  //   curl_setopt_array($ch, [
  //     CURLOPT_POST => true,
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HTTPHEADER => [
  //       'Content-Type: application/json',
  //       'Cookie: ' . $cookie
  //     ],
  //     CURLOPT_SSL_VERIFYPEER => false,
  //     CURLOPT_SSL_VERIFYHOST => 0,
  //   ]);
  //   $res = curl_exec($ch);
  //   $delResult = json_decode($res, true);
  //   curl_close($ch);

  //   // 5. Удаляем из БД и явно переводим vpn_freekey в 'used' (бесплатная активирована и завершена)
  //   Database::send(
  //     'UPDATE vpn_users SET vpn_uuid = ?, vpn_subscription = ?, vpn_status = ?, vpn_date_count = ?, vpn_freekey = ?, kassa_id = ?, vpn_divece_count = ?, vpn_amount = ? WHERE tg_id = ?',
  //     [strval(''), strval(''), intval(0), intval(0), strval('used'), strval(''), strval(''), strval(''), strval($tg_id)]
  //   );

  //   if (isset($delResult['success']) && $delResult['success']) {
  //     return ['status' => 'ok', 'message' => 'Ключ успешно удалён из X-UI и базы данных'];
  //   } else {
  //     return ['status' => 'partial', 'message' => 'Удалён из БД, но ошибка при удалении из X-UI: ' . $res];
  //   }
  // }

  // /**
  //  * Выполняет очистку устаревших (истекших) пользователей в XUI и синхронизирует состояние с базой данных.
  //  *
  //  * Производит авторизацию на сервере XUI, вызывает специальный API очистки истёкших клиентов.
  //  * Затем обновляет таблицу пользователей в БД, выставляя у всех истёкших подписок статус used и обнуляя ключевые VPN-поля.
  //  *
  //  * @return void
  //  */
  // static public function CleanUP()
  // {
  //   $base = $_ENV['XUI_URL_PANEL'] ?: 'https://nl.coravpn.online:12200/to';

  //   // Логин и получение куки
  //   $ch = curl_init($base . ($_ENV['XUI_URL_LOGIN'] ?: '/login'));
  //   curl_setopt_array($ch, [
  //     CURLOPT_POST => true,
  //     CURLOPT_POSTFIELDS => json_encode([
  //       'username' => $_ENV['XUI_LOGIN'] ?: 'timqwees',
  //       'password' => $_ENV['XUI_PASSWORD'] ?: 'timqwees1220066'
  //     ]),
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HEADER => true,
  //     CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
  //     CURLOPT_SSL_VERIFYPEER => true,
  //     CURLOPT_SSL_VERIFYHOST => 2,
  //   ]);
  //   $response = curl_exec($ch);
  //   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   $cookieName = $_ENV['XUI_LOGIN_NAME_COOKIE'] ?: 'x-ui';
  //   if ($code !== 200 || !preg_match('/Set-Cookie:\s*(' . $cookieName . '=[^;]+)/i', $response, $m)) {
  //     http_response_code(500);
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', "[" . date('Y-m-d H:i:s') . "] [CLEANUP] Auth error on XUI panel\n", FILE_APPEND);
  //     return;
  //   }
  //   $cookie = $m[1];

  //   // Список inbounds
  //   $ch = curl_init($base . ($_ENV['XUI_URL_LIST'] ?: $_ENV['XUI_URL_API'] . 'list'));
  //   curl_setopt_array($ch, [
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
  //     CURLOPT_SSL_VERIFYPEER => true,
  //     CURLOPT_SSL_VERIFYHOST => 2,
  //   ]);
  //   $response = curl_exec($ch);
  //   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   $data = json_decode($response, true);
  //   if ($code !== 200 || !$data['success'] || empty($data['obj'][0])) {
  //     http_response_code(500);
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', "[" . date('Y-m-d H:i:s') . "] [ОШИБКА - ГЛОБАЛЬНАЯ ОЧИСТКА] Ошибка получения списка: inbounds\n", FILE_APPEND);
  //     return;
  //   }

  //   // Подготовка inbound
  //   $inbound = $data['obj'][0];
  //   $inboundId = $inbound['id'];

  //   // 3. Удаляем всех истекших клиентов
  //   $ch = curl_init($base . $_ENV['XUI_URL_API'] . "delDepletedClients/{$inboundId}");
  //   curl_setopt_array($ch, [
  //     CURLOPT_POST => true,
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HTTPHEADER => [
  //       'Content-Type: application/json',
  //       'Cookie: ' . $cookie
  //     ],
  //     CURLOPT_SSL_VERIFYPEER => false,
  //     CURLOPT_SSL_VERIFYHOST => 0,
  //   ]);
  //   $res = curl_exec($ch);
  //   $result = json_decode($res, true);
  //   curl_close($ch);

  //   // Переписано: error_log на лог-файл
  //   if (isset($result['success']) && $result['success']) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', "[" . date('Y-m-d H:i:s') . "] [УСПЕШНО - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: Успешно удалены истекшие клиенты из X-UI\n", FILE_APPEND);
  //   } else {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', "[" . date('Y-m-d H:i:s') . "] [ОШИБКА - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: Ошибка при удалении истекших клиентов: " . $res . "\n", FILE_APPEND);
  //     return;
  //   }

  //   $nowMs = time() * 1000;

  //   Database::send(
  //     'UPDATE vpn_users SET vpn_uuid = ?, vpn_subscription = ?, vpn_status = ?, vpn_date_count = ?, vpn_freekey = ?, kassa_id = ?, vpn_divece_count = ? WHERE vpn_status > 0 AND vpn_status < ?',
  //     ['', '', 0, 0, 'used', '', '', $nowMs]
  //   );

  //   file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', "[" . date('Y-m-d H:i:s') . "] [УСПЕШНО - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: Обновлена БД — помечены как просроченные\n", FILE_APPEND);
  // }

  // //################################# YOOKASSA ######################################

  // /**
  //  * Генерирует и инициализирует платёжную ссылку для оплаты через ЮKassa.
  //  * При $saveCard = true — сохраняет способ оплаты для будущих автоплатежей.
  //  *
  //  * @param float $amount            - Сумма платежа
  //  * @param string $currency         - Валюта (по умолч. 'RUB')
  //  * @param string|null $returnUrl   - URL возврата после оплаты
  //  * @param string $description      - Описание платежа
  //  * @param string|null $customerEmail
  //  * @param string|null $customerPhone
  //  * @param bool $saveCard           - Сохранять способ оплаты? (для автоплатежей)
  //  * @param string $paymentMethod    - Способ оплаты: 'card' (по умолчанию) или 'sbp'
  //  * @return array Возвращает массив с результатом создания платежа:
  //  *   [
  //  *     'payment_url' => string|null,         // Ссылка для переадресации пользователя на оплату в ЮKassa, либо null/строка с ошибкой
  //  *     'payment_id' => string|null,          // Уникальный идентификатор созданного платежа (payment_id от ЮKassa), либо null/ошибка
  //  *     'payment_method_id' => string|null,    // Идентификатор способа оплаты (если сохранён/создан автоплатёж), либо null
  //  *     'qr_code' => string|null,              // QR-код для СБП (если paymentMethod = 'sbp'), либо null
  //  *     'payment_method' => string            // Способ оплаты: 'card' или 'sbp'
  //  *   ]
  //  */
  // public function Ykassa(
  //   $amount,
  //   $currency = 'RUB',
  //   $returnUrl = null,
  //   $description = 'Оплата в сервисе CoraVPN',
  //   $customerEmail = null,
  //   $customerPhone = null,
  //   $saveCard = true,
  //   $paymentMethod = 'card'
  // ): array {
  //   $shopId = $_ENV['YOOKASSA_SHOP_ID'] ?? null;
  //   $secretKey = $_ENV['YOOKASSA_SECRET_KEY'] ?? null;

  //   if (!$shopId || !$secretKey) {
  //     return [
  //       'payment_url' => 'Yookassa: Не заданы ключи магазина',
  //       'payment_id' => 'Yookassa: Не заданы ключи магазина',
  //       'payment_method_id' => null,
  //       'qr_code' => null,
  //       'payment_method' => $paymentMethod
  //     ];
  //   }

  //   // Генерация идемпотентного ключа
  //   if (function_exists('random_bytes')) {
  //     $idempotenceKey = bin2hex(random_bytes(16));
  //   } else {
  //     $idempotenceKey = uniqid('', true);
  //   }

  //   // Формируем товар для чека (receipt)
  //   $item = [
  //     'description' => mb_substr($description, 0, 128, 'UTF-8'),
  //     'quantity' => '1.00',
  //     'amount' => [
  //       'value' => number_format($amount, 2, '.', ''),
  //       'currency' => $currency
  //     ],
  //     'vat_code' => 1
  //   ];

  //   $receiptData = [
  //     'items' => [$item]
  //   ];

  //   // Указание покупателя для чека
  //   if ($customerEmail) {
  //     $receiptData['customer'] = ['email' => $customerEmail];
  //   } elseif ($customerPhone) {
  //     $receiptData['customer'] = ['phone' => $customerPhone];
  //   } else {
  //     $receiptData['customer'] = ['email' => 'support@coravpn.ru'];
  //   }

  //   // Определяем тип подтверждения и способ оплаты
  //   $isSBP = ($paymentMethod === 'sbp');//true/false
  //   $isSberPay = ($paymentMethod === 'sberbank');//true/false

  //   //по умолчанию запрос товарный
  //   $data = [
  //     'amount' => [
  //       'value' => number_format($amount, 2, '.', ''),
  //       'currency' => $currency
  //     ],
  //     'capture' => true,
  //     'description' => mb_substr($description, 0, 128, 'UTF-8'),
  //     'receipt' => $receiptData
  //   ];

  //   //================================== ФОРМИРОВАИЯ ЧЕКА СПОСОБЫ ОПЛАТЫ ===================================================================


  //   if ($isSBP) { //============= СБП ===================
  //     // Для СБП не указываем payment_method_data, чтобы YooKassa автоматически определил доступные методы
  //     // Если нужно принудительно использовать СБП, можно указать, но это может вызвать ошибку если метод не доступен
  //     // Попробуем сначала без принудительного указания метода
  //     $data['confirmation'] = [
  //       'type' => 'redirect',
  //       'return_url' => $returnUrl,
  //     ];
  //     // Пытаемся указать предпочтительный метод оплаты через payment_method_data
  //     // Если метод недоступен, YooKassa вернет ошибку, которую мы обработаем
  //     $data['payment_method_data'] = [
  //       'type' => 'sbp'
  //     ];
  //   } elseif ($isSberPay) { //============= SberPay ===================
  //     $data['payment_method_data'] = [
  //       'type' => 'sberbank'
  //     ];
  //     $data['confirmation'] = [
  //       'type' => 'redirect',
  //       'return_url' => $returnUrl,
  //     ];
  //   } else {
  //     $data['confirmation'] = [
  //       'type' => 'redirect',
  //       'return_url' => $returnUrl,
  //     ];
  //   }

  //   //================================== ФОРМИРОВАИЯ ЧЕКА СПОСОБЫ ОПЛАТЫ ===================================================================


  //   // Сохранение способы оплаты === карта
  //   if ($saveCard/* использовна */ && !$isSBP/* не была использовна */ && !$isSberPay/* не была использовна */) {
  //     $data['save_payment_method'] = true;
  //   }

  //   $headers = [
  //     'Idempotence-Key: ' . $idempotenceKey,
  //     'Content-Type: application/json'
  //   ];

  //   $ch = curl_init('https://api.yookassa.ru/v3/payments');
  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //   curl_setopt($ch, CURLOPT_POST, true);
  //   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  //   curl_setopt($ch, CURLOPT_USERPWD, $shopId . ':' . $secretKey);
  //   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  //   //curl_setopt($ch, CURLOPT_VERBOSE, 1); // Debug

  //   $response = curl_exec($ch);
  //   $curl_error = curl_error($ch);
  //   $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   if ($response === false) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА - YOOKASSA] Ykassa CURL error: %s\n", date('Y-m-d H:i:s'), $curl_error), FILE_APPEND);
  //     return [
  //       'payment_url' => "Yookassa: CURL error: " . $curl_error,
  //       'payment_id' => null,
  //       'payment_method_id' => null,
  //       'qr_code' => null,
  //       'payment_method' => $paymentMethod
  //     ];
  //   }

  //   if ($httpCode !== 200 && $httpCode !== 201) {
  //     $json_error = json_decode($response, true);
  //     $isPaymentMethodError = false;

  //     // Проверяем, является ли ошибка связанной с недоступным методом оплаты
  //     if (
  //       isset($json_error['code']) && $json_error['code'] === 'invalid_request' &&
  //       isset($json_error['description']) &&
  //       (stripos($json_error['description'], 'Payment method is not available') !== false ||
  //         stripos($json_error['description'], 'Способ оплаты недоступен') !== false)
  //     ) {
  //       $isPaymentMethodError = true;
  //     }

  //     // Если ошибка связана с недоступным методом оплаты (SBP или SberPay), пробуем без принудительного указания метода
  //     if ($isPaymentMethodError && ($isSBP || $isSberPay)) {
  //       file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ПРЕДУПРЕЖДЕНИЕ - YOOKASSA] Метод оплаты %s недоступен, пробуем без принудительного указания метода. Response: %s\n", date('Y-m-d H:i:s'), $paymentMethod, $response), FILE_APPEND);

  //       // Убираем принудительное указание метода оплаты и пробуем снова
  //       unset($data['payment_method_data']);

  //       // Генерируем новый идемпотентный ключ для повторного запроса
  //       if (function_exists('random_bytes')) {
  //         $idempotenceKey = bin2hex(random_bytes(16));
  //       } else {
  //         $idempotenceKey = uniqid('', true);
  //       }

  //       $headers = [
  //         'Idempotence-Key: ' . $idempotenceKey,
  //         'Content-Type: application/json'
  //       ];

  //       $ch = curl_init('https://api.yookassa.ru/v3/payments');
  //       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //       curl_setopt($ch, CURLOPT_POST, true);
  //       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  //       curl_setopt($ch, CURLOPT_USERPWD, $shopId . ':' . $secretKey);
  //       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

  //       $response = curl_exec($ch);
  //       $curl_error = curl_error($ch);
  //       $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //       curl_close($ch);

  //       if ($response === false) {
  //         file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА - YOOKASSA] Ykassa CURL error (retry): %s\n", date('Y-m-d H:i:s'), $curl_error), FILE_APPEND);
  //         return [
  //           'payment_url' => "Yookassa: CURL error: " . $curl_error,
  //           'payment_id' => null,
  //           'payment_method_id' => null,
  //           'qr_code' => null,
  //           'payment_method' => $paymentMethod
  //         ];
  //       }

  //       // Если повторный запрос тоже не удался, возвращаем ошибку
  //       if ($httpCode !== 200 && $httpCode !== 201) {
  //         file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА - YOOKASSA] Ykassa HTTP Code (retry): %s | Response: %s\n", date('Y-m-d H:i:s'), $httpCode, $response), FILE_APPEND);
  //         return [
  //           'payment_url' => "Yookassa: Ошибка платежа ($httpCode): " . $response,
  //           'payment_id' => null,
  //           'payment_method_id' => null,
  //           'qr_code' => null,
  //           'payment_method' => $paymentMethod
  //         ];
  //       }

  //       // Если повторный запрос успешен, продолжаем обработку ответа ниже
  //     } else {
  //       // Для других ошибок возвращаем стандартный ответ
  //       file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА - YOOKASSA] Ykassa HTTP Code: %s | Response: %s\n", date('Y-m-d H:i:s'), $httpCode, $response), FILE_APPEND);
  //       return [
  //         'payment_url' => "Yookassa: Ошибка платежа ($httpCode): " . $response,
  //         'payment_id' => null,
  //         'payment_method_id' => null,
  //         'qr_code' => null,
  //         'payment_method' => $paymentMethod
  //       ];
  //     }
  //   }

  //   $json = json_decode($response, true);

  //   if (json_last_error() !== JSON_ERROR_NONE) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА - YOOKASSA] Ykassa Ошибка json_decode: %s | Response: %s\n", date('Y-m-d H:i:s'), json_last_error_msg(), $response), FILE_APPEND);
  //     return [
  //       'payment_url' => 'Yookassa: Ошибка парсинга ответа',
  //       'payment_id' => null,
  //       'payment_method_id' => null,
  //       'qr_code' => null,
  //       'payment_method' => $paymentMethod
  //     ];
  //   }

  //   if (
  //     isset($json['status']) &&
  //     $json['status'] === 'pending'
  //   ) {
  //     $paymentMethodId = null;
  //     $paymentUrl = null;

  //     // получаем confirmation_url (ссылка на готовую страницу ЮKassa)
  //     if (isset($json['confirmation']['confirmation_url'])) {
  //       $paymentUrl = $json['confirmation']['confirmation_url'];
  //     }

  //     return [
  //       'payment_url' => $paymentUrl,
  //       'payment_id' => $json['id'],
  //       'payment_method_id' => $paymentMethodId,
  //       'qr_code' => null,
  //       'payment_method' => $paymentMethod
  //     ];
  //   } else {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА - YOOKASSA] Ykassa Некорректный ответ: %s\n", date('Y-m-d H:i:s'), $response), FILE_APPEND);
  //     return [
  //       'payment_url' => 'Yookassa: Некорректный ответ: ' . $response,
  //       'payment_id' => null,
  //       'payment_method_id' => null,
  //       'qr_code' => null,
  //       'payment_method' => $paymentMethod
  //     ];
  //   }
  // }

  // /**
  //  * Получает детали платежа по paymentId
  //  */
  // public function getPaymentDetails($paymentId)
  // {
  //   $shopId = $_ENV['YOOKASSA_SHOP_ID'] ?? null;
  //   $secretKey = $_ENV['YOOKASSA_SECRET_KEY'] ?? null;
  //   if (!$shopId || !$secretKey) {
  //     return null;
  //   }

  //   $url = 'https://api.yookassa.ru/v3/payments/' . urlencode($paymentId);
  //   $ch = curl_init($url);
  //   curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  //   curl_setopt($ch, CURLOPT_USERPWD, $shopId . ':' . $secretKey);
  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //   curl_setopt($ch, CURLOPT_HTTPHEADER, [
  //     'Content-Type: application/json',
  //     'Accept: application/json'
  //   ]);

  //   $response = curl_exec($ch);
  //   $curl_error = curl_error($ch);
  //   $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   if ($response === false || empty($response)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА - ПОЛУЧЕНИЕ ДЕТАЛЕЙ ПЛАТЕЖА] getPaymentDetails CURL error: %s\n", date('Y-m-d H:i:s'), $curl_error), FILE_APPEND);
  //     return null;
  //   }
  //   if ($httpCode !== 200 && $httpCode !== 201) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА -  ПОЛУЧЕНИЕ ДЕТАЛЕЙ ПЛАТЕЖА] getPaymentDetails HTTP code %s, response: %s\n", date('Y-m-d H:i:s'), $httpCode, $response), FILE_APPEND);
  //     return null;
  //   }

  //   $json = json_decode($response, true);
  //   if (json_last_error() !== JSON_ERROR_NONE) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА - ПЛУЧЕНИЕ ДЕТАЛЕЙ ПЛАТЕЖА] getPaymentDetails Ошибка json_decode: %s | Response: %s\n", date('Y-m-d H:i:s'), json_last_error_msg(), $response), FILE_APPEND);
  //     return null;
  //   }

  //   return $json;
  // }

  // /**
  //  * Создаёт рекуррентный платёж по payment_method_id (списывает платеж если автоплатеж включен)
  //  *
  //  * @param string $paymentMethodId - ID сохранённого способа оплаты
  //  * @param float $amount
  //  * @param string $currency
  //  * @param string $description
  //  * @param string|null $customerEmail
  //  * @param string|null $customerPhone
  //  * @return array|false - ['payment_id' => ..., 'status' => ...] или false при ошибке
  //  */
  // public function createRecurringPayment($paymentMethodId, $amount, $currency = 'RUB', $description = 'Продление CoraVPN', $customerEmail = null, $customerPhone = null)
  // {
  //   $shopId = $_ENV['YOOKASSA_SHOP_ID'] ?? null;
  //   $secretKey = $_ENV['YOOKASSA_SECRET_KEY'] ?? null;
  //   if (!$shopId || !$secretKey) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА СПИСАНИЯ АВТОПЛАТЕЖА] createRecurringPayment: Не заданы ключи магазина\n", date('Y-m-d H:i:s')), FILE_APPEND);
  //     return false;
  //   }

  //   if (function_exists('random_bytes')) {
  //     $idempotenceKey = bin2hex(random_bytes(16));
  //   } else {
  //     $idempotenceKey = uniqid('', true);
  //   }

  //   $receiptData = [
  //     'items' => [
  //       [
  //         'description' => mb_substr($description, 0, 128, 'UTF-8'),
  //         'quantity' => '1.00',
  //         'amount' => [
  //           'value' => number_format($amount, 2, '.', ''),
  //           'currency' => $currency
  //         ],
  //         'vat_code' => 1
  //       ]
  //     ]
  //   ];

  //   if ($customerEmail) {
  //     $receiptData['customer'] = ['email' => $customerEmail];
  //   } elseif ($customerPhone) {
  //     $receiptData['customer'] = ['phone' => $customerPhone];
  //   } else {
  //     $receiptData['customer'] = ['email' => 'support@coravpn.ru'];
  //   }

  //   $data = [
  //     'amount' => [
  //       'value' => number_format($amount, 2, '.', ''),
  //       'currency' => $currency
  //     ],
  //     'capture' => true,
  //     'payment_method_id' => $paymentMethodId, // 👈 Используем сохранённый способ оплаты
  //     'description' => mb_substr($description, 0, 128, 'UTF-8'),
  //     'receipt' => $receiptData
  //   ];

  //   $headers = [
  //     'Idempotence-Key: ' . $idempotenceKey,
  //     'Content-Type: application/json'
  //   ];

  //   $ch = curl_init('https://api.yookassa.ru/v3/payments');
  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //   curl_setopt($ch, CURLOPT_POST, true);
  //   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  //   curl_setopt($ch, CURLOPT_USERPWD, $shopId . ':' . $secretKey);
  //   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

  //   $response = curl_exec($ch);
  //   $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   if ($httpCode !== 200 && $httpCode !== 201) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА СПИСАНИЯ АВТОПЛАТЕЖА] createRecurringPayment: Ошибка (%s): %s\n", date('Y-m-d H:i:s'), $httpCode, $response), FILE_APPEND);
  //     return false;
  //   }

  //   $json = json_decode($response, true);
  //   if (isset($json['status']) && $json['status'] === 'succeeded') {
  //     return [
  //       'payment_id' => $json['id'],
  //       'status' => 'succeeded',
  //       'created_at' => $json['created_at']
  //     ];
  //   } else {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОШИБКА СПИСАНИЯ АВТОПЛАТЕЖА] createRecurringPayment: Неудачный ответ: %s\n", date('Y-m-d H:i:s'), $response), FILE_APPEND);
  //     return false;
  //   }
  // }

  // /**
  //  * AutoPay
  //  *
  //  * Автоматически продлевает подписки пользователей, у которых активирован автоплатёж (yk_autopay_active = 1).
  //  *
  //  * Логика метода:
  //  * - Получает из базы данных всех пользователей с автоплатежом и истёкшей/истекающей подпиской.
  //  * - Для каждого пользователя инициирует рекуррентный платёж через YooKassa с использованием сохранённого card_token.
  //  * - В случае успешного списания создаёт клиента в XUI с новым сроком действия.
  //  * - После успешного создания обновляет все необходимые поля пользователя в базе: статус, ссылку на подписку, UUID клиента, дату и id автоплатежа.
  //  * - Логирует результаты (успехи и ошибки) в файл coravpn.log.
  //  * - При необходимости отправляет email-уведомление пользователю.
  //  *
  //  * Метод можно запускать регулярно по cron для поддержки автопродлений.
  //  */
  // public function AutoPay()
  // {
  //   // Логируем начало выполнения
  //   file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', date('Y-m-d H:i:s') . " [ЗАПУСК СИСТЕМЫ АВТОПЛАТЕЖА] Автоплатежи запущены\n", FILE_APPEND);

  //   // Находим всех пользователей с активным автоплатежом и истекшей подпиской
  //   $nowMs = time() * 1000; // текущее время в миллисекундах

  //   $users = Database::send(
  //     'SELECT * FROM vpn_users WHERE yk_autopay_active = 1 AND vpn_status > 0 AND vpn_status < ? AND card_token IS NOT NULL',
  //     [$nowMs]
  //   );

  //   if (!is_array($users) || empty($users)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', date('Y-m-d H:i:s') . " [СИСТЕМЫ АВТОПЛАТЕЖА - ИНФОРМАЦИЯ] Нет пользователей для автоплатежа\n", FILE_APPEND);
  //     return;
  //   }

  //   foreach ($users as $user) {
  //     $tg_id = $user['tg_id'];
  //     $cardToken = $user['card_token']; // это payment_method.id
  //     $daysLimit = intval($user['vpn_date_count']) ?: 30; // по умолчанию 30 дней

  //     // Для автоплатежа используем сумму из предыдущей оплаты или стандартную цену
  //     $amount = intval($user['vpn_amount']) ?: $_ENV['BASIC_AMOUNT'] ?? 150; // по умолчанию базовая цена

  //     // Создаём рекуррентный платёж
  //     $recurringResult = $this->createRecurringPayment($cardToken, $amount, 'RUB', "Продление CoraVPN для tg_id=$tg_id");

  //     if ($recurringResult && $recurringResult['status'] === 'succeeded') {
  //       // Платёж прошёл успешно — создаём нового клиента в XUI
  //       $add_return = $this->add_client($daysLimit, $user['tg_username'], $user['vpn_divece_count']);

  //       if (!empty($add_return)) {
  //         // Обновляем данные пользователя
  //         Database::send(
  //           'UPDATE vpn_users SET vpn_subscription = ?, vpn_status = ?, vpn_uuid = ?, vpn_freekey = ?, yk_autopay_id = ?, yk_autopay_date = ?, vpn_amount = ? WHERE tg_id = ?',
  //           [
  //             strval($add_return['vpn_subscription']),
  //             strval($add_return['vpn_status']),
  //             strval($add_return['vpn_uuid']),
  //             'buy', // или оставить как было — зависит от логики
  //             $recurringResult['payment_id'],
  //             date('Y-m-d H:i:s'), // дата последнего автоплатежа
  //             $amount, // Устанавливаем сумму успешного автоплатежа
  //             strval($tg_id)
  //           ]
  //         );

  //         // Логируем успех
  //         file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', date('Y-m-d H:i:s') . " [СИСТЕМА - УСПЕШНО] Автоплатеж для tg_id=$tg_id ({$user['tg_username']}), payment_id=" . $recurringResult['payment_id'] . "\n", FILE_APPEND);

  //         // Отправляем email уведомление (опционально)
  //         $mailler = new MailController();
  //         ob_start();
  //         include_once dirname(__DIR__, 3) . '/public/assets/componets/mail/success_renewal.php';
  //         $body = ob_get_clean();
  //         $mailler->onMail(
  //           $_SESSION['temporary_email'] ?? $user['tg_username'] . '@example.com',
  //           'Подписка автоматически продлена - CoraVPN Unlimited',
  //           $body
  //         );

  //       } else {
  //         // Ошибка создания клиента в XUI
  //         file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', date('Y-m-d H:i:s') . " [СИСТЕМА - ОШИБКА] Не удалось создать клиента XUI для tg_id=$tg_id после успешного платежа\n", FILE_APPEND);
  //       }

  //     } else {
  //       // Ошибка платежа — отключаем автоплатёж
  //       Database::send(
  //         'UPDATE vpn_users SET yk_autopay_active = 0 WHERE tg_id = ?',
  //         [strval($tg_id)]
  //       );

  //       // Логируем ошибку
  //       file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', date('Y-m-d H:i:s') . " [СИСТЕМА - ОШИБКА] Ошибка автоплатежа для tg_id=$tg_id, card_token=$cardToken\n", FILE_APPEND);

  //       // Отправляем email уведомление об ошибке (опционально)
  //       $mailler = new MailController();
  //       ob_start();
  //       include_once dirname(__DIR__, 2) . '/public/assets/componets/mail/fail_renewal.php';
  //       $body = ob_get_clean();
  //       $mailler->onMail(
  //         $_SESSION['temporary_email'] ?? $user['tg_username'] . '@example.com',
  //         'Ошибка автоматического продления подписки - CoraVPN Unlimited',
  //         $body
  //       );
  //     }
  //   }
  //   file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', date('Y-m-d H:i:s') . " [КОНЕЦ] Автоплатежи завершены\n", FILE_APPEND);
  // }

  // /**
  //  * Проверка статуса платежа ЮKassa по paymentId.
  //  * Для production лучше использовать webhooks (HTTP-уведомления) по адресу:
  //  * https://www.coravpn.ru/success (настраивается в кабинете ЮKassa).
  //  * Рекомендуемые события для контроля:
  //  *   - payment.succeeded (платёж успешно прошёл)
  //  *   - payment.canceled (отмена или неуспех)
  //  *   - refund.succeeded (успешный возврат)
  //  * Это ручная проверка: используйте её только как доп. запасной вариант.
  //  */
  // public function YkassaCheck($paymentId)
  // {
  //   $shopId = $_ENV['YOOKASSA_SHOP_ID'] ?? null;
  //   $secretKey = $_ENV['YOOKASSA_SECRET_KEY'] ?? null;
  //   if (!$shopId || !$secretKey) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ПРОВЕРКА ЮКАССА - ОШИБКА] Yookassa: Не заданы ключи магазина для проверки оплаты\n", date('Y-m-d H:i:s')), FILE_APPEND);
  //     return false;
  //   }

  //   $url = 'https://api.yookassa.ru/v3/payments/' . urlencode($paymentId);
  //   $ch = curl_init($url);
  //   curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  //   curl_setopt($ch, CURLOPT_USERPWD, $shopId . ':' . $secretKey);
  //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // Таймаут подключения 2 секунды
  //   curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Общий таймаут 3 секунды
  //   curl_setopt($ch, CURLOPT_HTTPHEADER, [
  //     'Content-Type: application/json',
  //     'Accept: application/json'
  //   ]);

  //   $response = curl_exec($ch);
  //   $curl_error = curl_error($ch);
  //   $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   if ($response === false || empty($response)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ПРОВЕРКА ЮКАССА - ОШИБКА] YookassaCheck: CURL error: %s\n", date('Y-m-d H:i:s'), $curl_error), FILE_APPEND);
  //     return false;
  //   }
  //   if ($httpCode !== 200 && $httpCode !== 201) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ПРОВЕРКА ЮКАССА - ОШИБКА] YookassaCheck: HTTP code %s, response: %s\n", date('Y-m-d H:i:s'), $httpCode, $response), FILE_APPEND);
  //     return false;
  //   }

  //   $json = json_decode($response, true);
  //   if (json_last_error() !== JSON_ERROR_NONE) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ПРОВЕРКА ЮКАССА - ОШИБКА] YookassaCheck: Ошибка json_decode: %s | Response: %s\n", date('Y-m-d H:i:s'), json_last_error_msg(), $response), FILE_APPEND);
  //     return false;
  //   }
  //   if (!is_array($json)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ПРОВЕРКА ЮКАССА - ОШИБКА] YookassaCheck: Ответ не является массивом. Response: %s\n", date('Y-m-d H:i:s'), $response), FILE_APPEND);
  //     return false;
  //   }

  //   // Классическое API-поведение Юкассы для ручной проверки
  //   if (array_key_exists('status', $json)) {
  //     $status = $json['status'];
  //     if ($status === 'succeeded') { // payment.succeeded
  //       return true;
  //     }
  //     if ($status === 'canceled') { // payment.canceled
  //       return 'canceled';
  //     }
  //     return $status;
  //   }
  //   // Возможные ошибки от Юкассы через type и description:
  //   if (isset($json['type']) && isset($json['description'])) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ПРОВЕРКА ЮКАССА - ОШИБКА] YookassaCheck: Yookassa error: %s - %s\n", date('Y-m-d H:i:s'), $json['type'], $json['description']), FILE_APPEND);
  //     return false;
  //   }

  //   file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%-s] [ПРОВЕРКА ЮКАССА - ОШИБКА] YookassaCheck: Не удалось разобрать ответ: %s\n", date('Y-m-d H:i:s'), $response), FILE_APPEND);
  //   return false;
  // }

  // /**
  //  * Получает данные автоплатежа по ЮKassa для пользователя.
  //  * @param array $client - массив с данными пользователя (может содержать tg_id или иной идентификатор)
  //  * @return array|null - Ассоциативный массив с данными автоплатежа или null, если данных нет
  //  */
  // public static function getYooKassaAutopayData($tg_id)
  // {
  //   if (empty($tg_id)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ДАННЫЕ АВТОПЛАТЕЖА - ОШИБКА] getYooKassaAutopayData: tg_id пусты или невалидны\n", date('Y-m-d H:i:s')), FILE_APPEND);
  //     return null;
  //   }

  //   // В таблице vpn_users есть поля yk_autopay_active, yk_autopay_id, card_token, yk_autopay_date
  //   $res = Database::send(
  //     "SELECT yk_autopay_active, yk_autopay_id, yk_autopay_date, card_token FROM vpn_users WHERE tg_id = ? LIMIT 1",
  //     [strval($tg_id)]
  //   );

  //   if (
  //     empty($res) ||
  //     !is_array($res[0]) ||
  //     count($res) === 0
  //   ) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ДАННЫЕ АВТОПЛАТЕЖА - ОШИБКА] getYooKassaAutopayData: Нет записи в БД для tg_id=%s\n", date('Y-m-d H:i:s'), $tg_id), FILE_APPEND);
  //     return null;
  //   }

  //   $data = $res[0];
  //   return [
  //     'autopay_active' => isset($data['yk_autopay_active']) ? $data['yk_autopay_active'] : null,
  //     'autopay_id' => isset($data['yk_autopay_id']) ? $data['yk_autopay_id'] : null,
  //     'autopay_date' => isset($data['yk_autopay_date']) ? $data['yk_autopay_date'] : null,
  //     'card_token' => isset($data['card_token']) ? $data['card_token'] : null
  //   ];
  // }

  // //################################# UNLINK CARD ######################################

  // /**
  //  * Отвязывает карту пользователя и отключает автоплатеж.
  //  * @param string|int $tg_id
  //  * @return bool
  //  */
  // public function UnlinkCard($tg_id)
  // {
  //   // Проверка входящего tg_id
  //   if (empty($tg_id)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [УДАЛЕНИЕ КАРТЫ - ОШИБКА] UnlinkCard: tg_id пустой!\n", date('Y-m-d H:i:s')), FILE_APPEND);
  //     return false;
  //   }

  //   // Проверяем, есть ли пользователь в БД
  //   $user = Database::send(
  //     'SELECT card_token FROM vpn_users WHERE tg_id = ? LIMIT 1',
  //     [strval($tg_id)]
  //   );
  //   if (
  //     empty($user) ||               // Результат пустой
  //     !is_array($user) ||           // Не массив
  //     !isset($user[0]) ||           // Нет первой строки
  //     !is_array($user[0])           // Первая строка не массив
  //   ) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [УДАЛЕНИЕ КАРТЫ - ОШИБКА] UnlinkCard: Пользователь с tg_id %s не найден.\n", date('Y-m-d H:i:s'), $tg_id), FILE_APPEND);
  //     return false;
  //   }

  //   // Чистим поля: card_token, yk_autopay_id, и деактивируем автоплатеж
  //   $result = Database::send(
  //     "UPDATE vpn_users SET card_token = ?, yk_autopay_active = ?, yk_autopay_id = ? WHERE tg_id = ?",
  //     [
  //       strval(''),
  //       0,
  //       strval(''),
  //       strval($tg_id)
  //     ]
  //   );

  //   // Логируем действия
  //   file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [УДАЛЕНИЕ КАРТЫ - УСПЕШНО] %s tg_id=%s (email: %s)\n", date('Y-m-d H:i:s'), ($result !== false) ? 'Успешно отвязана карта и отключён автоплатеж' : 'НЕ УДАЛОСЬ отвязать карту/отключить автоплатеж', $tg_id, $_SESSION['temporary_email'] ?? '-'), FILE_APPEND);

  //   return $result !== false;
  // }

  // //################################# AUTOPAY ######################################

  // /**
  //  * Включает автоплатеж пользователю по tg_id (ставит yk_autopay_active = 1).
  //  * Не добавляет карту! Просто включает автоплатеж, если карта уже есть.
  //  *
  //  * @param string|int $tg_id
  //  * @return bool
  //  */
  // public function EnableAutopay($tg_id)
  // {
  //   if (empty($tg_id)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ВКЛЮЧИТЬ АВТОПРОДЛЕНИЕ - ОШИБКА] EnableAutopay: tg_id пустой!\n", date('Y-m-d H:i:s')), FILE_APPEND);
  //     return false;
  //   }

  //   // Проверяем, есть ли пользователь
  //   $user = Database::send(
  //     'SELECT card_token FROM vpn_users WHERE tg_id = ? LIMIT 1',
  //     [strval($tg_id)]
  //   );
  //   if (empty($user) || !is_array($user)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ВКЛЮЧИТЬ АВТОПРОДЛЕНИЕ - ОШИБКА] EnableAutopay: Пользователь с tg_id %s не найден или невалиден.\n", date('Y-m-d H:i:s'), $tg_id), FILE_APPEND);
  //     return false;
  //   }

  //   // Проверяем, есть ли вообще карта для автоплатежа — бизнес-правило
  //   $card_token = $user[0]['card_token'] ?? null;
  //   if (empty($card_token)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ВКЛЮЧИТЬ АВТОПРОДЛЕНИЕ - ОШИБКА] EnableAutopay: Не установлено card_token для tg_id %s, не включаю автоплатеж.\n", date('Y-m-d H:i:s'), $tg_id), FILE_APPEND);
  //     return false;
  //   }

  //   $result = Database::send(
  //     'UPDATE vpn_users SET yk_autopay_active = 1 WHERE tg_id = ?',
  //     [strval($tg_id)]
  //   );

  //   file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ВКЛЮЧИТЬ АВТОПРОДЛЕНИЕ - ОК] %s tg_id=%s (email: %s)\n", date('Y-m-d H:i:s'), $result !== false ? 'Включён автоплатеж' : 'Ошибка включения автоплатежа', $tg_id, $_SESSION['temporary_email'] ?? '-'), FILE_APPEND);

  //   return $result !== false;
  // }

  // /**
  //  * Отключает автоплатеж пользователю по tg_id (ставит yk_autopay_active = 0).
  //  * Карта не отвязывается!
  //  *
  //  * @param string|int $tg_id
  //  * @return bool
  //  */
  // public function DisableAutopay($tg_id)
  // {
  //   if (empty($tg_id)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОТКЛЮЧИТЬ АВТОПРОДЛЕНИЕ - ОШИБКА] DisableAutopay: tg_id пустой!\n", date('Y-m-d H:i:s')), FILE_APPEND);
  //     return false;
  //   }

  //   // Проверяем, есть ли пользователь
  //   $user = Database::send(
  //     'SELECT yk_autopay_active FROM vpn_users WHERE tg_id = ? LIMIT 1',
  //     [strval($tg_id)]
  //   );
  //   if (empty($user) || !is_array($user)) {
  //     file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОТКЛЮЧИТЬ АВТОПРОДЛЕНИЕ - ОШИБКА] DisableAutopay: Пользователь с tg_id %s не найден или невалиден.\n", date('Y-m-d H:i:s'), $tg_id), FILE_APPEND);
  //     return false;
  //   }

  //   $result = Database::send(
  //     'UPDATE vpn_users SET yk_autopay_active = 0 WHERE tg_id = ?',
  //     [strval($tg_id)]
  //   );

  //   file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', sprintf("[%s] [ОТКЛЮЧИТЬ АВТОПРОДЛЕНИЕ - ОК] %s tg_id=%s (email: %s)\n", date('Y-m-d H:i:s'), $result !== false ? 'Отключён автоплатеж' : 'Ошибка отключения автоплатежа', $tg_id, $_SESSION['temporary_email'] ?? '-'), FILE_APPEND);

  //   return $result !== false;
  // }

  // //################################# XUI UPDATE ######################################

  // /**
  //  * Обновляет срок действия клиента в X-UI (продлевает на $bonus дней)
  //  * @param int|string $tg_id Telegram ID клиента
  //  * @param int $bonus Количество бонусных дней (положительное)
  //  * @return array Статус операции
  //  */
  // public function xui_update($tg_id, $bonus)
  // {
  //   $client = $this->client($tg_id);
  //   $email = $client['tg_username'] ?? null;
  //   $uuid = $client['vpn_uuid'] ?? null;

  //   if (!$email || !$uuid) {
  //     return ['status' => 'error', 'message' => 'Нет UUID/email для обновления'];
  //   }

  //   $base = $_ENV['XUI_URL_PANEL'] ?: 'https://nl.coravpn.online:12200/to';

  //   // === 1. Авторизация ===
  //   $ch = curl_init($base . ($_ENV['XUI_URL_LOGIN'] ?: '/login'));
  //   curl_setopt_array($ch, [
  //     CURLOPT_POST => true,
  //     CURLOPT_POSTFIELDS => json_encode([
  //       'username' => $_ENV['XUI_LOGIN'] ?: 'timqwees',
  //       'password' => $_ENV['XUI_PASSWORD'] ?: 'timqwees1220066'
  //     ]),
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HEADER => true,
  //     CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
  //     CURLOPT_SSL_VERIFYPEER => true,
  //     CURLOPT_SSL_VERIFYHOST => 2,
  //   ]);
  //   $response = curl_exec($ch);
  //   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   $cookieName = $_ENV['XUI_LOGIN_NAME_COOKIE'] ?: 'x-ui';
  //   if ($code !== 200 || !preg_match('/Set-Cookie:\s*(' . $cookieName . '=[^;]+)/i', $response, $m)) {
  //     return ['status' => 'error', 'message' => 'Не удалось авторизоваться в X-UI'];
  //   }
  //   $cookie = $m[1];

  //   // === 2. Получаем inbounds ===
  //   $ch = curl_init($base . ($_ENV['XUI_URL_LIST'] ?: $_ENV['XUI_URL_API'] . 'list'));
  //   curl_setopt_array($ch, [
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
  //     CURLOPT_SSL_VERIFYPEER => true,
  //     CURLOPT_SSL_VERIFYHOST => 2,
  //   ]);
  //   $response = curl_exec($ch);
  //   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   curl_close($ch);

  //   $data = json_decode($response, true);
  //   if ($code !== 200 || !$data['success'] || empty($data['obj'][0])) {
  //     return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
  //   }

  //   $inbound = $data['obj'][0];
  //   $inboundId = $inbound['id'];
  //   $settings = json_decode($inbound['settings'], true);
  //   if (!isset($settings['clients']) || !is_array($settings['clients'])) {
  //     return ['status' => 'error', 'message' => 'Inbound не содержит clients'];
  //   }

  //   // === 3. Находим клиента по UUID/email ===
  //   $targetClientIndex = null;
  //   foreach ($settings['clients'] as $i => $c) {
  //     if (
  //       (isset($c['id']) && $c['id'] === $uuid) ||
  //       (isset($c['password']) && $c['password'] === $uuid) ||
  //       (isset($c['email']) && $c['email'] === $email)
  //     ) {
  //       $targetClientIndex = $i;
  //       break;
  //     }
  //   }

  //   if ($targetClientIndex === null) {
  //     return ['status' => 'error', 'message' => "Клиент не найден в inbound (uuid: $uuid, email: $email)"];
  //   }

  //   // === 4. Обновляем expiryTime (текущее + бонус * 86400 * 1000) ===
  //   $currentExpiry = intval($settings['clients'][$targetClientIndex]['expiryTime'] ?? 0);
  //   $nowMs = time() * 1000;
  //   $newExpiryMs = ($currentExpiry > $nowMs ? $currentExpiry : $nowMs) + ($bonus * 86400 * 1000);

  //   $settings['clients'][$targetClientIndex]['expiryTime'] = $newExpiryMs;

  //   // Обновляем строку settings
  //   $inbound['settings'] = json_encode($settings);

  //   // === 5. Отправляем PATCH/POST на /xui/API/inbounds/update/{inboundId} ===
  //   $ch = curl_init($base . $_ENV['XUI_URL_API'] . "update/$inboundId");
  //   curl_setopt_array($ch, [
  //     CURLOPT_POST => true,
  //     CURLOPT_POSTFIELDS => json_encode($inbound),
  //     CURLOPT_RETURNTRANSFER => true,
  //     CURLOPT_HTTPHEADER => [
  //       'Content-Type: application/json',
  //       'Cookie: ' . $cookie
  //     ],
  //     CURLOPT_SSL_VERIFYPEER => true,
  //     CURLOPT_SSL_VERIFYHOST => 2,
  //   ]);

  //   $res = curl_exec($ch);
  //   $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  //   $result = json_decode($res, true);
  //   curl_close($ch);

  //   $logFile = $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log';
  //   $logMsg = sprintf(
  //     "[%s] [XUI UPDATE] tg_id=%s, uuid=%s, bonus=%d дней, newExpiry=%s (%s)\n",
  //     date('Y-m-d H:i:s'),
  //     $tg_id,
  //     $uuid,
  //     $bonus,
  //     date('Y-m-d H:i:s', $newExpiryMs / 1000),
  //     ($httpCode === 200 && isset($result['success']) && $result['success']) ? '✅ OK' : '❌ FAIL (HTTP ' . $httpCode . ')'
  //   );
  //   file_put_contents($logFile, $logMsg, FILE_APPEND);

  //   if ($httpCode === 200 && isset($result['success']) && $result['success']) {
  //     // ✅ Успешно — обновляем expiryTime и дни в БД
  //     Database::send(
  //       'UPDATE vpn_users SET vpn_status = ? WHERE tg_id = ?',
  //       [strval($newExpiryMs), strval($tg_id)]
  //     );
  //     return ['status' => 'ok', 'message' => "Продлено на $bonus дней"];
  //   } else {
  //     return [
  //       'status' => 'error',
  //       'message' => "Ошибка XUI: HTTP $httpCode, ответ: " . substr($res, 0, 250)
  //     ];
  //   }
  // }

  // //################################# ADD BONUS DAYS ######################################

  // /**
  //  * @param mixed $buyer_tg_id
  //  *
  //  * @return [type]
  //  */
  // public function add_bonus_days($buyer_tg_id)
  // {
  //   //получаем код клиента
  //   $buyer = $this->client($buyer_tg_id);
  //   if (empty($buyer['refer_link'])) {//проверяем что реферный код есть
  //     return;
  //   }

  //   $ref = Database::send(
  //     "SELECT * FROM vpn_users WHERE my_refer_link=? LIMIT 1",
  //     [$buyer['refer_link']]
  //   );//находим реферала по реферному коду
  //   if (!is_array($ref) || empty($ref[0])) {
  //     return;
  //   }

  //   $ref_tg_id = $ref[0]['tg_id'];//id реферала
  //   $days_buy = intval($buyer['vpn_date_count']);//дни клиента

  //   $percent = intval($_ENV['REREF_PERCENTAGE_DAYS'] ?? 3);//процент дней который получит реферал от клиента
  //   $bonus = floor($days_buy * $percent / 100);//считаем процент дней от клиента

  //   if ($bonus <= 0) {
  //     return;
  //   }

  //   //запускем обновление
  //   $this->xui_update($ref_tg_id, $bonus);
  // }

  // //################################# REFER ######################################

  // /**
  //  * Генерация реферальной ссылки
  //  *
  //  * @return string
  //  */
  // public function GenerationRefer()
  // {
  //   return $this->reflink = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 7);
  // }

  // /**
  //  * Проверяет наличие уникальной реферальной ссылки у всех пользователей.
  //  * Если ссылки нет или она пуста, генерирует новую для каждого такого пользователя.
  //  * Возвращает массив tg_id => my_refer_link для всех пользователей.
  //  */
  // public function GlobalCheckRefer()
  // {
  //   // Получаем всех пользователей с tg_id и my_refer_link
  //   $sql = Database::send('SELECT tg_id, my_refer_link FROM vpn_users');
  //   $users = [];
  //   if (is_array($sql)) {
  //     if (isset($sql[0]) && is_array($sql[0]) && array_keys($sql[0]) === range(0, count($sql[0]) - 1)) {
  //       $users = $sql[0];
  //     } elseif (array_keys($sql) === range(0, count($sql) - 1)) {
  //       $users = $sql;
  //     }
  //   }

  //   if (empty($users) || !is_array($users)) {
  //     return [];
  //   }

  //   $result = [];

  //   foreach ($users as $user) {
  //     $tg_id = $user['tg_id'];
  //     $current_link = isset($user['my_refer_link']) ? $user['my_refer_link'] : '';
  //     if (empty($current_link)) {
  //       $this->GenerationRefer();
  //       $newRefer = $this->reflink;
  //       Database::send('UPDATE vpn_users SET my_refer_link = ? WHERE tg_id = ?', [$newRefer, strval($tg_id)]);
  //       $result[$tg_id] = $newRefer;
  //     } else {
  //       $result[$tg_id] = $current_link;
  //     }
  //   }

  //   return $result;
  // }

  // /**
  //  * Активирует реферальную ссылку для пользователя.
  //  *
  //  * @param mixed $my_id ID пользователя, который активирует ссылку
  //  * @param string $refer_link Реферальная ссылка (без префикса 'ref=')
  //  * @param string $type Тип перенаправления после успешного активации
  //  * @param bool $skipRedirect Если true, не выполнять редирект (для использования в Listener)
  //  * @return bool true, если активация успешна, false в случае ошибки
  //  */
  // public function setRefer(
  //   $my_id,
  //   $refer_link,
  //   $type = '/profile',
  //   $skipRedirect = false
  // ) {
  //   // Проверка: пользователь не может активировать свою собственную ссылку
  //   $client_user = $this->client($my_id);

  //   // ВАЖНО: Проверяем, существует ли пользователь в базе данных
  //   // Если tg_id пустой, значит пользователя нет в БД
  //   if (empty($client_user['tg_id']) || $client_user['tg_id'] === '') {
  //     if (!$skipRedirect) {
  //       Message::set('refer_error', 'Пользователь не найден. Пожалуйста, сначала зарегистрируйтесь.');
  //       Network::onRedirect($type);
  //     }

  //     // Логируем попытку активации несуществующим пользователем
  //     file_put_contents(
  //       $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //       sprintf(
  //         "[%s] [ОШИБКА - РЕФЕРАЛ] setRefer: Попытка активации реферальной ссылки несуществующим пользователем tg_id=%s, refer_link=%s\n",
  //         date('Y-m-d H:i:s'),
  //         $my_id,
  //         $refer_link
  //       ),
  //       FILE_APPEND
  //     );

  //     return false;
  //   }

  //   // Уже есть активированная ссылка у пользователя? (не даём заново активировать)
  //   if (!empty($client_user['refer_link'])) {
  //     if (!$skipRedirect) {
  //       Message::set('refer_error', 'Вы уже активировали реферальную ссылку!');
  //       Network::onRedirect($type);
  //     }
  //     return false;
  //   }

  //   // ========= Разделяем ссылку вида ref=xxxxxxx и извлекаем id (xxxxxxx)
  //   if (strpos($refer_link, 'ref=') === 0) {
  //     $refer_link = substr($refer_link, strlen('ref='));
  //   }
  //   // ======================================================

  //   // Проверка, существует ли реферальная ссылка и чья она
  //   $result = Database::send('SELECT * FROM vpn_users WHERE my_refer_link = ? LIMIT 1', [$refer_link]);
  //   if (!is_array($result) || count($result) === 0) {
  //     if (!$skipRedirect) {
  //       Message::set('refer_error', 'Реферальная ссылка не найдена');
  //       Network::onRedirect($type);
  //     }
  //     return false;
  //   }

  //   $client_refer = $this->client($result[0]['tg_id']);

  //   // Нельзя активировать свою же ссылку
  //   if ($client_refer['tg_id'] == $my_id) {
  //     if (!$skipRedirect) {
  //       Message::set('refer_error', 'Нельзя использовать свою собственную реферальную ссылку');
  //       Network::onRedirect($type);
  //     }
  //     return false;
  //   }

  //   // Нельзя активировать повторно ту же ссылку (дублирование безопасности)
  //   if (!empty($client_user['refer_link'])) {
  //     if (!$skipRedirect) {
  //       Message::set('refer_error', 'Вы уже активировали реферальную ссылку!');
  //       Network::onRedirect($type);
  //     }
  //     return false;
  //   }

  //   // Активируем: увеличиваем счетчик рефера и сохраняем для пользователя активированную ссылку
  //   $updateReferCount = Database::send('UPDATE vpn_users SET my_refer_count = my_refer_count + 1 WHERE tg_id = ?', [intval($client_refer['tg_id'])]);
  //   // Устанавливаем реферальную ссылку и скидку 20% для пользователя
  //   $updateUserRefer = Database::send('UPDATE vpn_users SET refer_link = ?, refer_discount = 20 WHERE tg_id = ?', [strval($refer_link), intval($my_id)]);

  //   // Проверяем, что обновления прошли успешно
  //   if ($updateReferCount === false || $updateUserRefer === false) {
  //     if (!$skipRedirect) {
  //       Message::set('refer_error', 'Ошибка при активации реферальной ссылки. Попробуйте позже.');
  //       Network::onRedirect($type);
  //     }

  //     // Логируем ошибку обновления
  //     file_put_contents(
  //       $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //       sprintf(
  //         "[%s] [ОШИБКА - РЕФЕРАЛ] setRefer: Ошибка обновления БД при активации реферальной ссылки. tg_id=%s, refer_link=%s\n",
  //         date('Y-m-d H:i:s'),
  //         $my_id,
  //         $refer_link
  //       ),
  //       FILE_APPEND
  //     );

  //     return false;
  //   }

  //   if (!$skipRedirect) {
  //     Message::set('refer_success', 'Реферальная ссылка успешно активирована! Вы получили скидку 20% на все тарифы!');
  //   }

  //   //ЛОГИРОВАНИЕ
  //   file_put_contents(
  //     $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //     sprintf(
  //       "[%s] [SUCCESS] setRefer: Пользователь с tg_id=%s активировал реферальную ссылку '%s' (рефер: tg_id=%s). Скидка 20%% применена.\n",
  //       date('Y-m-d H:i:s'),
  //       $my_id,
  //       $refer_link,
  //       $client_refer['tg_id']
  //     ),
  //     FILE_APPEND
  //   );

  //   if (!$skipRedirect) {
  //     Network::onRedirect($type);
  //   }
  //   return true;
  // }

  // //################################# CID ######################################

  // /**
  //  * Определяет CID пользователя и информацию о его устройстве (браузер/ОС).
  //  *
  //  * @return array Ассоциативный массив с полями 'ip', 'user_agent', 'device_type', 'os'.
  //  */
  // public static function getClientCID()
  // {
  //   // Определяем IP
  //   $ip = '';
  //   if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
  //     $ip = $_SERVER['HTTP_CLIENT_IP'];
  //   } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  //     $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
  //   } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
  //     $ip = $_SERVER['REMOTE_ADDR'];
  //   }

  //   // User Agent
  //   $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

  //   // Мини-анализатор устройства и ОС
  //   $device_type = 'Unknown';
  //   $os = 'Unknown';

  //   if (preg_match('/android/i', $user_agent)) {
  //     $device_type = 'Mobile';
  //     $os = 'Android';
  //   } elseif (preg_match('/iphone|ipad|ipod/i', $user_agent)) {
  //     $device_type = 'Mobile';
  //     $os = 'iOS';
  //   } elseif (preg_match('/windows/i', $user_agent)) {
  //     $device_type = 'PC';
  //     $os = 'Windows';
  //   } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
  //     $device_type = 'PC';
  //     $os = 'MacOS';
  //   } elseif (preg_match('/linux/i', $user_agent)) {
  //     $device_type = 'PC';
  //     $os = 'Linux';
  //   }

  //   if (preg_match('/mobile/i', $user_agent)) {
  //     $device_type = 'Mobile';
  //   } elseif (preg_match('/tablet|ipad/i', $user_agent)) {
  //     $device_type = 'Tablet';
  //   } elseif ($device_type === 'Unknown') {
  //     $device_type = 'PC';
  //   }

  //   return [
  //     // IP-адрес клиента, например: '93.184.216.34'
  //     'ip' => $ip,
  //     // User-Agent браузера, например: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)...'
  //     'user_agent' => $user_agent,
  //     // Тип устройства: 'Mobile', 'Tablet', 'PC', например: 'PC'
  //     'device_type' => $device_type,
  //     // Операционная система: 'Windows', 'Android', 'iOS', 'MacOS', 'Linux', например: 'Windows'
  //     'os' => $os
  //   ];

  // }

  // /**
  //  * Ищет клиента в базе данных по CID данным (IP, User-Agent, Device Type, OS).
  //  * Использует многоуровневый поиск с постепенным ослаблением критериев.
  //  *
  //  * @param array $cidData Массив с полями 'ip', 'user_agent', 'device_type', 'os' из getClientCID()
  //  * @return array|false Массив с данными клиента или false, если не найден
  //  */
  // public static function findClientByCID($cidData)
  // {
  //   if (!is_array($cidData) || empty($cidData['ip'])) {
  //     return false;
  //   }

  //   $ip = strval($cidData['ip']);
  //   $user_agent = strval($cidData['user_agent'] ?? '');
  //   $device_type = strval($cidData['device_type'] ?? '');
  //   $os = strval($cidData['os'] ?? '');

  //   // Уровень 1: Точное совпадение всех параметров (IP + User-Agent + Device Type + OS)
  //   if (!empty($user_agent) && !empty($device_type) && !empty($os)) {
  //     $result = Database::send(
  //       'SELECT * FROM vpn_users WHERE cid_ip = ? AND cid_user_agent = ? AND cid_device_type = ? AND cid_os = ? LIMIT 1',
  //       [$ip, $user_agent, $device_type, $os]
  //     );

  //     if (is_array($result) && !empty($result) && isset($result[0])) {
  //       return $result[0];
  //     }
  //   }

  //   // Уровень 2: Поиск по IP + Device Type + OS (без User-Agent, так как он может изменяться)
  //   if (!empty($device_type) && !empty($os)) {
  //     $result = Database::send(
  //       'SELECT * FROM vpn_users WHERE cid_ip = ? AND cid_device_type = ? AND cid_os = ? AND cid_ip != "" LIMIT 1',
  //       [$ip, $device_type, $os]
  //     );

  //     if (is_array($result) && !empty($result) && isset($result[0])) {
  //       return $result[0];
  //     }
  //   }

  //   // Уровень 3: Поиск по IP + OS (если Device Type не определен)
  //   if (!empty($os)) {
  //     $result = Database::send(
  //       'SELECT * FROM vpn_users WHERE cid_ip = ? AND cid_os = ? AND cid_ip != "" LIMIT 1',
  //       [$ip, $os]
  //     );

  //     if (is_array($result) && !empty($result) && isset($result[0])) {
  //       return $result[0];
  //     }
  //   }

  //   // Уровень 4: Поиск только по IP (последний вариант, менее надежно)
  //   $result = Database::send(
  //     'SELECT * FROM vpn_users WHERE cid_ip = ? AND cid_ip != "" LIMIT 1',
  //     [$ip]
  //   );

  //   if (is_array($result) && !empty($result) && isset($result[0])) {
  //     return $result[0];
  //   }

  //   return false;
  // }

  // /**
  //  * Сохраняет CID данные для пользователя.
  //  *
  //  * @param int|string $tg_id Telegram ID пользователя
  //  * @param array $cidData Массив с полями 'ip', 'user_agent', 'device_type', 'os' из getClientCID()
  //  * @return bool true в случае успеха, false при ошибке
  //  */
  // public static function saveClientCID($tg_id, $cidData)
  // {
  //   if (!is_array($cidData) || empty($cidData['ip'])) {
  //     return false;
  //   }

  //   $result = Database::send(
  //     'UPDATE vpn_users SET cid_ip = ?, cid_user_agent = ?, cid_device_type = ?, cid_os = ? WHERE tg_id = ?',
  //     [
  //       strval($cidData['ip']),
  //       strval($cidData['user_agent'] ?? ''),
  //       strval($cidData['device_type'] ?? ''),
  //       strval($cidData['os'] ?? ''),
  //       strval($tg_id)
  //     ]
  //   );

  //   return $result !== false;
  // }

  // //################################# PENDING REFER CODES ######################################

  // /**
  //  * Сохраняет pending реферальный код в БД с привязкой к IP/CID данным.
  //  * Код будет активирован автоматически при входе пользователя с таким IP.
  //  *
  //  * @param string $referCode Реферальный код
  //  * @param array $cidData Массив с полями 'ip', 'user_agent', 'device_type', 'os'
  //  * @param int $expireDays Количество дней до истечения (по умолчанию 30)
  //  * @return bool true в случае успеха, false при ошибке
  //  */
  // public static function savePendingReferCode($referCode, $cidData, $expireDays = 30)
  // {
  //   if (empty($referCode) || !is_array($cidData) || empty($cidData['ip'])) {
  //     return false;
  //   }

  //   $now = time();
  //   $expiresAt = $now + ($expireDays * 24 * 60 * 60);

  //   // Удаляем старые истекшие коды для этого IP
  //   self::cleanExpiredPendingReferCodes($cidData['ip']);

  //   $ip = strval($cidData['ip']);
  //   $user_agent = strval($cidData['user_agent'] ?? '');
  //   $device_type = strval($cidData['device_type'] ?? '');
  //   $os = strval($cidData['os'] ?? '');

  //   // Проверяем, есть ли уже неактивированный код для этого устройства (IP + Device + OS)
  //   // Если есть - обновляем его вместо создания нового (избегаем дубликатов)
  //   $existingCode = null;

  //   // Сначала ищем по точному совпадению (IP + Device + OS)
  //   if (!empty($device_type) && !empty($os)) {
  //     $existing = Database::send(
  //       'SELECT * FROM pending_refer_codes WHERE cid_ip = ? AND cid_device_type = ? AND cid_os = ? AND activated = 0 AND expires_at > ? ORDER BY created_at DESC LIMIT 1',
  //       [$ip, $device_type, $os, $now]
  //     );

  //     if (is_array($existing) && !empty($existing) && isset($existing[0])) {
  //       $existingCode = $existing[0];
  //     }
  //   }

  //   // Если не нашли по точному совпадению, ищем только по IP (менее точно, но все равно обновим)
  //   if (!$existingCode) {
  //     $existing = Database::send(
  //       'SELECT * FROM pending_refer_codes WHERE cid_ip = ? AND activated = 0 AND expires_at > ? ORDER BY created_at DESC LIMIT 1',
  //       [$ip, $now]
  //     );

  //     if (is_array($existing) && !empty($existing) && isset($existing[0])) {
  //       $existingCode = $existing[0];
  //     }
  //   }

  //   if ($existingCode) {
  //     // Обновляем существующий код (обновляем CID данные и срок действия)
  //     $result = Database::send(
  //       'UPDATE pending_refer_codes SET refer_code = ?, cid_user_agent = ?, cid_device_type = ?, cid_os = ?, created_at = ?, expires_at = ? WHERE id = ?',
  //       [
  //         strval($referCode),
  //         $user_agent,
  //         $device_type,
  //         $os,
  //         $now,
  //         $expiresAt,
  //         intval($existingCode['id'])
  //       ]
  //     );

  //     if ($result !== false) {
  //       file_put_contents(
  //         $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //         sprintf(
  //           "[%s] [PENDING REFER UPDATED] Реферальный код обновлен в БД для IP=%s, device=%s, os=%s, refer_code=%s, expires_at=%s (id=%s)\n",
  //           date('Y-m-d H:i:s'),
  //           $ip,
  //           $device_type ?: 'unknown',
  //           $os ?: 'unknown',
  //           $referCode,
  //           date('Y-m-d H:i:s', $expiresAt),
  //           $existingCode['id']
  //         ),
  //         FILE_APPEND
  //       );
  //     }
  //   } else {
  //     // Создаем новый код
  //     $result = Database::send(
  //       'INSERT INTO pending_refer_codes (refer_code, cid_ip, cid_user_agent, cid_device_type, cid_os, created_at, expires_at, activated) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
  //       [
  //         strval($referCode),
  //         $ip,
  //         $user_agent,
  //         $device_type,
  //         $os,
  //         $now,
  //         $expiresAt,
  //         0
  //       ]
  //     );

  //     if ($result !== false) {
  //       file_put_contents(
  //         $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //         sprintf(
  //           "[%s] [PENDING REFER SAVED] Реферальный код сохранен в БД для IP=%s, device=%s, os=%s, refer_code=%s, expires_at=%s\n",
  //           date('Y-m-d H:i:s'),
  //           $ip,
  //           $device_type ?: 'unknown',
  //           $os ?: 'unknown',
  //           $referCode,
  //           date('Y-m-d H:i:s', $expiresAt)
  //         ),
  //         FILE_APPEND
  //       );
  //     }
  //   }

  //   return $result !== false;
  // }

  // /**
  //  * Ищет pending реферальный код по IP и другим CID данным.
  //  * Использует многоуровневый поиск с приоритетом более точных совпадений.
  //  * Приоритет: IP+Device+OS > IP+OS > только IP
  //  *
  //  * @param array $cidData Массив с полями 'ip', 'user_agent', 'device_type', 'os'
  //  * @return array|false Массив с данными pending кода или false, если не найден
  //  */
  // public static function findPendingReferCodeByCID($cidData)
  // {
  //   if (!is_array($cidData) || empty($cidData['ip'])) {
  //     return false;
  //   }

  //   $ip = strval($cidData['ip']);
  //   $user_agent = strval($cidData['user_agent'] ?? '');
  //   $device_type = strval($cidData['device_type'] ?? '');
  //   $os = strval($cidData['os'] ?? '');
  //   $now = time();

  //   // Уровень 1: Точное совпадение всех параметров (IP + User-Agent + Device Type + OS)
  //   // Самый точный поиск - приоритет для устройств с полным совпадением
  //   if (!empty($user_agent) && !empty($device_type) && !empty($os)) {
  //     $result = Database::send(
  //       'SELECT * FROM pending_refer_codes WHERE cid_ip = ? AND cid_user_agent = ? AND cid_device_type = ? AND cid_os = ? AND activated = 0 AND expires_at > ? ORDER BY created_at DESC LIMIT 1',
  //       [$ip, $user_agent, $device_type, $os, $now]
  //     );

  //     if (is_array($result) && !empty($result) && isset($result[0])) {
  //       return $result[0];
  //     }
  //   }

  //   // Уровень 2: Поиск по IP + Device Type + OS (без User-Agent)
  //   // Хорошее совпадение - разные браузеры на одном устройстве
  //   if (!empty($device_type) && !empty($os)) {
  //     $result = Database::send(
  //       'SELECT * FROM pending_refer_codes WHERE cid_ip = ? AND cid_device_type = ? AND cid_os = ? AND activated = 0 AND expires_at > ? AND cid_ip != "" ORDER BY created_at DESC LIMIT 1',
  //       [$ip, $device_type, $os, $now]
  //     );

  //     if (is_array($result) && !empty($result) && isset($result[0])) {
  //       return $result[0];
  //     }
  //   }

  //   // Уровень 3: Поиск по IP + OS
  //   // Среднее совпадение - разные устройства одной ОС в одной сети
  //   if (!empty($os)) {
  //     $result = Database::send(
  //       'SELECT * FROM pending_refer_codes WHERE cid_ip = ? AND cid_os = ? AND activated = 0 AND expires_at > ? AND cid_ip != "" ORDER BY created_at DESC LIMIT 1',
  //       [$ip, $os, $now]
  //     );

  //     if (is_array($result) && !empty($result) && isset($result[0])) {
  //       return $result[0];
  //     }
  //   }

  //   // Уровень 4: Поиск только по IP (последний вариант, наименее точно)
  //   // Используется только если нет более точных совпадений
  //   // ВАЖНО: Может вернуть код для другого устройства в той же сети
  //   $result = Database::send(
  //     'SELECT * FROM pending_refer_codes WHERE cid_ip = ? AND activated = 0 AND expires_at > ? AND cid_ip != "" ORDER BY created_at DESC LIMIT 1',
  //     [$ip, $now]
  //   );

  //   if (is_array($result) && !empty($result) && isset($result[0])) {
  //     return $result[0];
  //   }

  //   return false;
  // }

  // /**
  //  * Атомарно помечает pending реферальный код как активированный.
  //  * Использует проверку activated = 0 для предотвращения коллизий (race condition).
  //  * Если код уже активирован другим устройством - вернет false.
  //  *
  //  * @param int $pendingId ID pending кода
  //  * @param string $tg_id Telegram ID пользователя, который активировал код
  //  * @return bool true если код успешно активирован, false если уже активирован или ошибка
  //  */
  // public static function markPendingReferCodeAsActivated($pendingId, $tg_id)
  // {
  //   // Атомарная операция: обновляем только если код еще не активирован (activated = 0)
  //   // Это предотвращает ситуацию, когда два устройства одновременно пытаются активировать один код
  //   $result = Database::send(
  //     'UPDATE pending_refer_codes SET activated = 1, activated_tg_id = ? WHERE id = ? AND activated = 0',
  //     [strval($tg_id), intval($pendingId)]
  //   );

  //   if ($result !== false) {
  //     // Проверяем, действительно ли код был обновлен (не был уже активирован)
  //     $check = Database::send(
  //       'SELECT activated, activated_tg_id FROM pending_refer_codes WHERE id = ? LIMIT 1',
  //       [intval($pendingId)]
  //     );

  //     if (is_array($check) && !empty($check) && isset($check[0])) {
  //       if ($check[0]['activated'] == 1 && $check[0]['activated_tg_id'] == strval($tg_id)) {
  //         // Код успешно активирован этим пользователем
  //         return true;
  //       } else if ($check[0]['activated'] == 1 && $check[0]['activated_tg_id'] != strval($tg_id)) {
  //         // Код уже активирован другим пользователем - коллизия!
  //         file_put_contents(
  //           $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //           sprintf(
  //             "[%s] [КОЛЛИЗИЯ РЕФЕРАЛ] Код pending_refer_codes id=%s уже активирован другим пользователем tg_id=%s (попытка активации от tg_id=%s)\n",
  //             date('Y-m-d H:i:s'),
  //             $pendingId,
  //             $check[0]['activated_tg_id'],
  //             $tg_id
  //           ),
  //           FILE_APPEND
  //         );
  //         return false;
  //       }
  //     }
  //   }

  //   return false;
  // }

  // /**
  //  * Удаляет истекшие pending реферальные коды для указанного IP.
  //  *
  //  * @param string $ip IP адрес
  //  * @return void
  //  */
  // public static function cleanExpiredPendingReferCodes($ip = null)
  // {
  //   $now = time();

  //   if ($ip) {
  //     // Удаляем истекшие коды для конкретного IP
  //     Database::send(
  //       'DELETE FROM pending_refer_codes WHERE cid_ip = ? AND expires_at < ?',
  //       [strval($ip), $now]
  //     );
  //   } else {
  //     // Удаляем все истекшие коды
  //     Database::send(
  //       'DELETE FROM pending_refer_codes WHERE expires_at < ?',
  //       [$now]
  //     );
  //   }
  // }

  // /**
  //  * @param string $type
  //  * @param array $change
  //  *
  //  * @return array
  //  */
  // public static function isPrice(string $type = '', array $change = []): array
  // {
  //   $db = Database::send('SELECT * FROM vpn_price')[0];
  //   $result = [];
  //   if ($type !== 'edit') {
  //     foreach ($db as $name => $amount) {
  //       $amount !== false ? $result[$name] = $amount : $result[$name] = 0;
  //     }
  //     return $result;
  //   }
  //   foreach ($change as $name => $amount) {
  //     if (isset($db[$name])) {
  //       if ($amount !== false) {
  //         $result = Database::send("UPDATE vpn_price SET $name = ?", [intval($amount)]);
  //         if ($result)
  //           file_put_contents(
  //             $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //             sprintf(
  //               "[%s] [ИЗМЕНЕНИЕ ЦЕН - SUCCESS] Цена тарфифа '$name' изменена на >> $amount ₽ \n",
  //               date('Y-m-d H:i:s')
  //             ),
  //             FILE_APPEND
  //           );
  //       }
  //     }
  //   }
  //   return self::isPrice();
  // }

  // /**
  //  * Рассчитывает и сохраняет ежемесячные доходы партнеров
  //  * @param int $year - год (по умолчанию текущий)
  //  * @param int $month - месяц (по умолчанию текущий)
  //  * @return bool - успешность операции
  //  */
  // public static function calculateMonthlyPartnerRevenue($year = null, $month = null)
  // {
  //   if ($year === null)
  //     $year = date('Y');
  //   if ($month === null)
  //     $month = date('n');

  //   $currentTimestamp = time();

  //   try {
  //     // Получаем всех партнеров (у кого есть my_refer_link)
  //     $partners = Database::send('SELECT DISTINCT tg_id, my_refer_link FROM vpn_users WHERE my_refer_link != "" AND my_refer_link IS NOT NULL');

  //     if (!is_array($partners)) {
  //       return false;
  //     }

  //     foreach ($partners as $partner) {
  //       $partnerTgId = $partner['tg_id'];
  //       $referLink = $partner['my_refer_link'];

  //       // Получаем всех рефералов партнера, которые оплатили в указанном месяце
  //       $referrals = Database::send(
  //         'SELECT vpn_amount, vpn_date_count FROM vpn_users WHERE refer_link = ? AND vpn_amount > 0',
  //         [$referLink]
  //       );

  //       if (!is_array($referrals)) {
  //         continue;
  //       }

  //       $monthlyRevenue = 0;
  //       $referralCount = 0;

  //       foreach ($referrals as $referral) {
  //         $amount = floatval($referral['vpn_amount'] ?? 0);
  //         $dateCount = $referral['vpn_date_count'] ?? '';

  //         // Проверяем, что оплата была в указанном месяце
  //         if ($amount > 0 && self::isPaymentInMonth($dateCount, $year, $month)) {
  //           $monthlyRevenue += $amount;
  //           $referralCount++;
  //         }
  //       }

  //       // Сохраняем или обновляем данные о доходах за месяц
  //       Database::send(
  //         'INSERT OR REPLACE INTO partner_monthly_revenue
  //          (partner_tg_id, year, month, revenue_amount, referral_count, created_at, updated_at)
  //          VALUES (?, ?, ?, ?, ?, ?, ?)',
  //         [
  //           strval($partnerTgId),
  //           intval($year),
  //           intval($month),
  //           number_format($monthlyRevenue, 2, '.', ''),
  //           intval($referralCount),
  //           intval($currentTimestamp),
  //           intval($currentTimestamp)
  //         ]
  //       );
  //     }

  //     // Логируем успешное выполнение
  //     file_put_contents(
  //       $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //       sprintf("[%s] [PARTNER_REVENUE] Рассчитаны доходы партнеров за %s-%s\n", date('Y-m-d H:i:s'), $year, str_pad($month, 2, '0', STR_PAD_LEFT)),
  //       FILE_APPEND
  //     );

  //     return true;
  //   } catch (Exception $e) {
  //     // Логируем ошибку
  //     file_put_contents(
  //       $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
  //       sprintf("[%s] [PARTNER_REVENUE_ERROR] Ошибка при расчете доходов: %s\n", date('Y-m-d H:i:s'), $e->getMessage()),
  //       FILE_APPEND
  //     );
  //     return false;
  //   }
  // }

  // /**
  //  * Проверяет, относится ли дата оплаты к указанному месяцу
  //  * @param string $dateCount - дата в формате vpn_date_count
  //  * @param int $year - год
  //  * @param int $month - месяц
  //  * @return bool
  //  */
  // private static function isPaymentInMonth($dateCount, $year, $month)
  // {
  //   if (empty($dateCount)) {
  //     return false;
  //   }

  //   // Пытаемся распарсить дату из vpn_date_count
  //   $timestamp = strtotime($dateCount);
  //   if ($timestamp === false) {
  //     return false;
  //   }

  //   $paymentYear = date('Y', $timestamp);
  //   $paymentMonth = date('n', $timestamp);

  //   return ($paymentYear == $year && $paymentMonth == $month);
  // }

  // /**
  //  * Получает доход партнера за указанный месяц
  //  * @param string $partnerTgId - ID партнера
  //  * @param int $year - год
  //  * @param int $month - месяц
  //  * @return array|null - данные о доходе или null
  //  */
  // public static function getPartnerMonthlyRevenue($partnerTgId, $year, $month)
  // {
  //   $result = Database::send(
  //     'SELECT * FROM partner_monthly_revenue WHERE partner_tg_id = ? AND year = ? AND month = ?',
  //     [strval($partnerTgId), intval($year), intval($month)]
  //   );

  //   return (is_array($result) && !empty($result[0])) ? $result[0] : null;
  // }

  // /**
  //  * Получает историю доходов партнера по месяцам
  //  * @param string $partnerTgId - ID партнера
  //  * @param int $limit - лимит записей (по умолчанию 12)
  //  * @return array - массив с историей доходов
  //  */
  // public static function getPartnerRevenueHistory($partnerTgId, $limit = 12)
  // {
  //   $result = Database::send(
  //     'SELECT * FROM partner_monthly_revenue WHERE partner_tg_id = ?
  //      ORDER BY year DESC, month DESC LIMIT ?',
  //     [strval($partnerTgId), intval($limit)]
  //   );

  //   return is_array($result) ? $result : [];
  // }

  // /**
  //  * Подсчитывает количество новых рефералов партнера за указанный месяц
  //  * @param string $partnerTgId - Telegram ID партнера
  //  * @param int $year - год
  //  * @param int $month - месяц
  //  * @return int - количество новых рефералов
  //  */
  // public static function getPartnerNewReferralsCount($partnerTgId, $year, $month)
  // {
  //   // Получаем реферальную ссылку партнера
  //   $partner = Database::send('SELECT my_refer_link FROM vpn_users WHERE tg_id = ?', [strval($partnerTgId)]);

  //   if (!is_array($partner) || empty($partner[0])) {
  //     return 0;
  //   }

  //   $referLink = $partner[0]['my_refer_link'];

  //   // Получаем всех рефералов партнера
  //   $referrals = Database::send(
  //     'SELECT vpn_date_count FROM vpn_users
  //      WHERE refer_link = ? AND refer_link != "" AND refer_link IS NOT NULL',
  //     [strval($referLink)]
  //   );

  //   if (!is_array($referrals)) {
  //     return 0;
  //   }

  //   $newReferralsCount = 0;

  //   // Фильтруем рефералов по месяцу регистрации
  //   foreach ($referrals as $referral) {
  //     $dateCount = $referral['vpn_date_count'] ?? '';

  //     // Проверяем, что дата регистрации в указанном месяце
  //     if ($dateCount && self::isPaymentInMonth($dateCount, $year, $month)) {
  //       $newReferralsCount++;
  //     }
  //   }

  //   return $newReferralsCount;
  // }

}
