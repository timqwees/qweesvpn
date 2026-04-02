<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\vpn\v2ray;
use Setting\Route\Function\Controllers\client\Client;
use App\Config\Session;
use App\Config\Database;
class Xray
{

/**
   * Создаёт нового клиента VPN на сервере XUI через API, возвращает его параметры для дальнейшей регистрации в системе.
   *
   * Производит авторизацию в панели XUI, получает список инбаундов (inbounds),
   * формирует уникальные параметры (uuid/subId/email/expiry time), добавляет клиента в первый инбаунд,
   * возвращает параметры подключения для последующего сохранения в базе данных.
   *
   * @param int|string $days         Количество дней действия подписки
   * @param string     $uniID        Уникальный идентификатор пользователя
   * @param int|null   $device_limit Лимит устройств (опционально, по умолчанию из XUI_DEVICE_LIMIT)
   *
   * @return array|false              Возвращает массив с данными клиента при успехе:
   *                                 [
   *                                   'success' => true,
   *                                   'client_data' => [
   *                                     'id' => string,           // UUID клиента
   *                                     'email' => string,        // Email клиента
   *                                     'subId' => string,        // ID подписки
   *                                     'expiryTime' => int,      // Время истечения в мс
   *                                     'limitIp' => int,         // Лимит IP адресов
   *                                     'enable' => bool,         // Статус активности
   *                                     'totalGB' => int,         // Лимит трафика (0 = безлимит)
   *                                     'inbound_id' => int,      // ID inbound'а
   *                                     'protocol' => string,     // Протокол (vless, vmess)
   *                                     'host' => string,         // Хост сервера
   *                                     'port' => int,            // Порт подключения
   *                                     'security' => string,     // Тип безопасности (tls)
   *                                     'network' => string       // Тип сети (ws)
   *                                   ]
   *                                 ]
   *                                 При ошибке возвращает false и записывает лог
   */
  public function addClient($days, $uniID, $device_limit = null): array|false
  {
    // Логин и получение куки
    $ch = curl_init('https://nl.coravpn.online:12200/to/login');
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => json_encode([
        'username' => $_ENV['XUI_LOGIN'] ?: 'timqwees',
        'password' => $_ENV['XUI_PASSWORD'] ?: 'timqwees1220066'
      ]),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    

    $cookieName = 'x-ui';
    // Handle both 'x-ui' and '3x-ui' cookie names
    $cookiePattern = '/Set-Cookie:\s*(3?' . $cookieName . '=[^;]+)/i';
    if ($code !== 200 || !preg_match($cookiePattern, $response, $m)) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [ВЫДАЧА ПОДПИСКИ] Auth error on XUI panel: HTTP %d\n",
          date('Y-m-d H:i:s'),
          $code
        ),
        FILE_APPEND
      );
      return false;
    }
    $cookie = $m[1];

    // Список inbounds
    $ch = curl_init('https://nl.coravpn.online:12200/to/xui/API/inbounds/');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    if ($response === false || !empty($curlError)) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] Ошибка cURL при получении списка inbounds: %s\n",
          date('Y-m-d H:i:s'),
          $curlError
        ),
        FILE_APPEND
      );
      return false;
    }
    
    $data = json_decode($response, true);
    if ($code !== 200 || !$data['success'] || empty($data['obj'][0])) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] Ошибка получения списка inbounds: HTTP %d\n",
          date('Y-m-d H:i:s'),
          $code
        ),
        FILE_APPEND
      );
      return false;
    }

    // Подготовка inbound
    $inbound = $data['obj'][0];//получаем первый inbound
    $protocol = strtolower($inbound['protocol']);
    $settings = json_decode($inbound['settings'], true);

    if (!isset($settings['clients']) || !is_array($settings['clients'])) {
      $settings['clients'] = [];
    }

    $expiry = (time() + ($days * 86400)) * 1000; // ms
    if ($device_limit == null) {
      $device_limit = isset($_ENV['XUI_DEVICE_LIMIT']) ? intval($_ENV['XUI_DEVICE_LIMIT']) : 1;
    }

    $client = [
        'id' => $uniID,
        'email' => $uniID,
        'expiryTime' => $expiry,
        'subId' => $uniID,
        'enable' => true,
        'totalGB' => 0,
        'limitIp' => $device_limit,
        'comment' => '',
        'created_at' => time() * 1000,
        'updated_at' => time() * 1000
      ];
    
    $settings['clients'][] = $client;
    $inbound['settings'] = json_encode($settings);

    $ch = curl_init('https://nl.coravpn.online:12200/to/xui/API/inbounds/update/' . $inbound['id']);
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => json_encode($inbound),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        'Cookie: ' . $cookie,
        'Content-Type: application/json'
      ],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
    ]);
    
    // Retry mechanism for SSL errors
    $maxRetries = 3;
    $response = false;
    $curlError = '';
    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        
        if ($response !== false && empty($curlError)) {
            break; // Success, exit retry loop
        }
        
        if ($attempt < $maxRetries) {
            // Wait before retry (exponential backoff)
            usleep(100000 * $attempt); // 100ms, 200ms, 300ms
            curl_reset($ch);
            curl_setopt_array($ch, [
              CURLOPT_POST => true,
              CURLOPT_POSTFIELDS => json_encode($inbound),
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_HTTPHEADER => [
                'Cookie: ' . $cookie,
                'Content-Type: application/json'
              ],
              CURLOPT_SSL_VERIFYPEER => false,
              CURLOPT_SSL_VERIFYHOST => 0,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_CONNECTTIMEOUT => 10,
              CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
            ]);
        }
    }
    
    $updateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($response === false || !empty($curlError)) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] Ошибка cURL при обновлении inbound: %s\n",
          date('Y-m-d H:i:s'),
          $curlError
        ),
        FILE_APPEND
      );
      return false;
    }
    
    $updateUser = json_decode($response, true);

    if ($updateCode === 200 && isset($updateUser['success']) && $updateUser['success']) {
      // Возвращаем реальные данные VPN клиента
      return [
        'success' => true,
        'client_data' => [
          'id' => $client['id'],
          'email' => $client['email'],
          'subId' => $client['subId'],
          'expiryTime' => $client['expiryTime'],
          'limitIp' => $client['limitIp'],
          'enable' => $client['enable'],
          'totalGB' => $client['totalGB'],
          'inbound_id' => $inbound['id'],
          'protocol' => $inbound['protocol'],
          'host' => 'nl.coravpn.online',
          'port' => $inbound['port'] ?? 443,
          'security' => $inbound['streamSettings']['security'] ?? 'tls',
          'network' => $inbound['streamSettings']['network'] ?? 'ws'
        ]
      ];
    } else {
      // ЛОГИРУЕМ
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [ОШИБКА - ВЫДАЧА ПОДПИСКИ] add_client: Ошибка добавления пользователя! Подробности: %s\n",
          date('Y-m-d H:i:s'),
          json_encode($updateUser, JSON_UNESCAPED_UNICODE)
        ),
        FILE_APPEND
      );
      return false;
    }
  }

  /**
   * Удаляет ключ (VPN пользователя) из панели XUI и обнуляет связанные данные в базе данных.
   *
   * Осуществляет авторизацию на XUI, ищет нужного клиента среди первого inbounds по uuid/email.
   * Если данные для удаления отсутствуют, просто обнуляет данные в БД.
   * Если найден, удаляет клиента через API XUI и сбрасывает значения в базе, возвращает статус результата.
   *
   * @param string|int $tg_id - Telegram ID пользователя
   * @return array            - Массив ["status" => "ok"|"partial"|"error", "message" => ...]
    */
  public function DeleteKey()
  {
    // Получаем данные пользователя
    $uniID = strval(Session::init('user')['uniID']);
    
    // Логируем начало процесса удаления
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Начало удаления ключа для пользователя uniID: %s\n",
        date('Y-m-d H:i:s'),
        $uniID
      ),
      FILE_APPEND
    );
    
    $client = Client::get($uniID);
    $email = $client['email'] ?? null;
    
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Получены данные клиента: email=%s\n",
        date('Y-m-d H:i:s'),
        $email ?? 'null'
      ),
      FILE_APPEND
    );
    
    if (!$email) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [DELETE KEY] Email не найден, выполняется только очистка БД\n",
          date('Y-m-d H:i:s')
        ),
        FILE_APPEND
      );
      
      Database::send(
        'UPDATE qwees_users SET subscription = ?, status = ?, count_days = ?, count_devices = ?, amount = ?, date_end = ? WHERE uniID = ?',
        ['', strval('off'), intval(0), intval(0), intval(0), strval(''), $uniID]
      );
      return ['status' => 'partial', 'message' => 'Удалён из БД, но не из X-UI (нет данных для удаления)'];
    }

    // 1. Авторизация
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Попытка авторизации в X-UI\n",
        date('Y-m-d H:i:s')
      ),
      FILE_APPEND
    );
    
    $ch = curl_init('https://nl.coravpn.online:12200/to/login');
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => json_encode([
        'username' => $_ENV['XUI_LOGIN'] ?: 'timqwees',
        'password' => $_ENV['XUI_PASSWORD'] ?: 'timqwees1220066'
      ]),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Результат авторизации: HTTP %d, cURL Error: %s\n",
        date('Y-m-d H:i:s'),
        $code,
        $curlError
      ),
      FILE_APPEND
    );
    
    $cookieName = 'x-ui';
    // Handle both 'x-ui' and '3x-ui' cookie names
    $cookiePattern = '/Set-Cookie:\s*(3?' . $cookieName . '=[^;]+)/i';
    if ($code !== 200 || !preg_match($cookiePattern, $response, $m)) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [DELETE KEY] Auth error on XUI panel: HTTP %d\n",
          date('Y-m-d H:i:s'),
          $code
        ),
        FILE_APPEND
      );
      return ['status' => 'error', 'message' => 'Не удалось авторизоваться в X-UI'];
    }
    $cookie = $m[1];
    
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Авторизация успешна, получена cookie\n",
        date('Y-m-d H:i:s')
      ),
      FILE_APPEND
    );

    // 2. Получаем inbounds
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Получение списка inbounds\n",
        date('Y-m-d H:i:s')
      ),
      FILE_APPEND
    );
    
    $ch = curl_init('https://nl.coravpn.online:12200/to/xui/API/inbounds/');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Результат получения inbounds: HTTP %d, cURL Error: %s\n",
        date('Y-m-d H:i:s'),
        $code,
        $curlError
      ),
      FILE_APPEND
    );
    
    if ($response === false || !empty($curlError)) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [DELETE KEY] Ошибка cURL при получении inbounds: %s\n",
          date('Y-m-d H:i:s'),
          $curlError
        ),
        FILE_APPEND
      );
      return ['status' => 'error', 'message' => 'Ошибка cURL при получении inbounds: ' . $curlError];
    }

    $data = json_decode($response, true);
    if ($code !== 200 || !$data['success'] || empty($data['obj'][0])) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [DELETE KEY] Ошибка получения inbounds: HTTP %d, Response: %s\n",
          date('Y-m-d H:i:s'),
          $code,
          json_encode($data, JSON_UNESCAPED_UNICODE)
        ),
        FILE_APPEND
      );
      return ['status' => 'error', 'message' => 'Не удалось получить inbounds'];
    }

    $inbound = $data['obj'][0];
    $inboundId = $inbound['id'];
    
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Получен inbound ID: %d\n",
        date('Y-m-d H:i:s'),
        $inboundId
      ),
      FILE_APPEND
    );

    $clientId = $email;

    if (!$clientId) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [DELETE KEY] Не найден clientId для удаления\n",
          date('Y-m-d H:i:s')
        ),
        FILE_APPEND
      );
      return ['status' => 'error', 'message' => 'Не найден clientId для удаления'];
    }
    
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Удаление клиента clientId: %s из inbound %d\n",
        date('Y-m-d H:i:s'),
        $clientId,
        $inboundId
      ),
      FILE_APPEND
    );

    // 4. Удаляем клиента по clientId
    $ch = curl_init('https://nl.coravpn.online:12200/to/xui/API/inbounds/' . $inboundId . '/delClient/' . $clientId);
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Cookie: ' . $cookie
      ],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
    ]);
    $res = curl_exec($ch);
    $delCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $delCurlError = curl_error($ch);
    $delResult = json_decode($res, true);
    
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Результат удаления клиента: HTTP %d, cURL Error: %s, Response: %s\n",
        date('Y-m-d H:i:s'),
        $delCode,
        $delCurlError,
        json_encode($delResult, JSON_UNESCAPED_UNICODE)
      ),
      FILE_APPEND
    );
    

    // 5. Удаляем из БД и явно переводим status в 'off'
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Очистка данных в БД для пользователя uniID: %s\n",
        date('Y-m-d H:i:s'),
        $uniID
      ),
      FILE_APPEND
    );
    
    Database::send(
      'UPDATE qwees_users SET subscription = ?, status = ?, count_days = ?, count_devices = ?, amount = ?, date_end = ? WHERE uniID = ?',
      [strval(''), strval('off'), intval(0), intval(0), intval(0), strval(''), strval($uniID)]
    );
    
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [DELETE KEY] Данные в БД очищены\n",
        date('Y-m-d H:i:s')
      ),
      FILE_APPEND
    );

    if (isset($delResult['success']) && $delResult['success']) {
      // Дополнительная проверка - убеждаемся что клиент действительно удален
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [DELETE KEY] Проверка фактического удаления клиента\n",
          date('Y-m-d H:i:s')
        ),
        FILE_APPEND
      );
      
      // Получаем обновленный список inbounds для проверки
      $ch = curl_init('https://nl.coravpn.online:12200/to/xui/API/inbounds/');
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
      ]);
      $verifyResponse = curl_exec($ch);
      $verifyCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $verifyError = curl_error($ch);
      
      if ($verifyResponse !== false && empty($verifyError) && $verifyCode === 200) {
        $verifyData = json_decode($verifyResponse, true);
        if ($verifyData['success'] && !empty($verifyData['obj'][0])) {
          $verifyInbound = $verifyData['obj'][0];
          $verifySettings = json_decode($verifyInbound['settings'], true);
          $clientStillExists = false;
          
          if (isset($verifySettings['clients']) && is_array($verifySettings['clients'])) {
            foreach ($verifySettings['clients'] as $client) {
              if ($client['email'] === $clientId) {
                $clientStillExists = true;
                break;
              }
            }
          }
          
          if ($clientStillExists) {
            file_put_contents(
              $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
              sprintf(
                "[%s] [DELETE KEY] ❌ ОШИБКА: Клиент %s все еще существует в inbound %d после удаления!\n",
                date('Y-m-d H:i:s'),
                $clientId,
                $inboundId
              ),
              FILE_APPEND
            );
            return ['status' => 'error', 'message' => 'X-UI сообщил об успешном удалении, но клиент все еще существует на сервере'];
          } else {
            file_put_contents(
              $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
              sprintf(
                "[%s] [DELETE KEY] ✅ Подтверждено: клиент %s действительно удален с сервера\n",
                date('Y-m-d H:i:s'),
                $clientId
              ),
              FILE_APPEND
            );
          }
        }
      }
      
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [DELETE KEY] Успешное завершение: ключ удалён из X-UI и БД\n",
          date('Y-m-d H:i:s')
        ),
        FILE_APPEND
      );
      return ['status' => 'ok', 'message' => 'Ключ успешно удалён из X-UI и базы данных'];
    } else {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [DELETE KEY] Частичное завершение: удалён из БД, но ошибка при удалении из X-UI: %s\n",
          date('Y-m-d H:i:s'),
          $res
        ),
        FILE_APPEND
      );
      return ['status' => 'partial', 'message' => 'Удалён из БД, но ошибка при удалении из X-UI: ' . $res];
    }
  }

  /**
   * Выполняет очистку устаревших (истекших) пользователей в XUI и синхронизирует состояние с базой данных.
   *
   * Производит авторизацию на сервере XUI, вызывает специальный API очистки истёкших клиентов.
   * Затем обновляет таблицу пользователей в БД, выставляя у всех истёкших подписок статус used и обнуляя ключевые VPN-поля.
   *
   * @return void
   */
  public static function CleanUP()
  {
    // Логин и получение куки
    $ch = curl_init('https://nl.coravpn.online:12200/to/login');
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => json_encode([
        'username' => $_ENV['XUI_LOGIN'] ?: 'timqwees',
        'password' => $_ENV['XUI_PASSWORD'] ?: 'timqwees1220066'
      ]),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    

    $cookieName = 'x-ui';
    // Handle both 'x-ui' and '3x-ui' cookie names
    $cookiePattern = '/Set-Cookie:\s*(3?' . $cookieName . '=[^;]+)/i';
    if ($code !== 200 || !preg_match($cookiePattern, $response, $m)) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [CLEANUP] Auth error on XUI panel: HTTP %d, Error: %s\n",
          date('Y-m-d H:i:s'),
          $code,
          $curlError
        ),
        FILE_APPEND
      );
      return;
    }
    $cookie = $m[1];

    // Список inbounds
    $ch = curl_init('https://nl.coravpn.online:12200/to/xui/API/inbounds/');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => ['Cookie: ' . $cookie],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; QWEESVPN/1.0)'
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    
    if ($response === false || !empty($curlError)) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [ОШИБКА - ГЛОБАЛЬНАЯ ОЧИСТКА] Ошибка cURL при получении inbounds: %s\n",
          date('Y-m-d H:i:s'),
          $curlError
        ),
        FILE_APPEND
      );
      return;
    }

    $data = json_decode($response, true);
    if ($code !== 200 || !$data['success'] || empty($data['obj'][0])) {
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [ОШИБКА - ГЛОБАЛЬНАЯ ОЧИСТКА] Ошибка получения списка inbounds: HTTP %d\n",
          date('Y-m-d H:i:s'),
          $code
        ),
        FILE_APPEND
      );
      return;
    }

    // Подготовка inbound
    $inbound = $data['obj'][0];
    $inboundId = $inbound['id'];

    // 3. Удаляем всех истекших клиентов
    $ch = curl_init('https://nl.coravpn.online:12200/to/xui/API/inbounds/delDepletedClients/' . $inboundId);
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Cookie: ' . $cookie
      ],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    $res = curl_exec($ch);
    $result = json_decode($res, true);
    

    // Переписано: error_log на лог-файл
    if (isset($result['success']) && $result['success']) {
      file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', "[" . date('Y-m-d H:i:s') . "] [УСПЕШНО - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: Успешно удалены истекшие клиенты из X-UI\n", FILE_APPEND);
    } else {
      file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', "[" . date('Y-m-d H:i:s') . "] [ОШИБКА - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: Ошибка при удалении истекших клиентов: " . $res . "\n", FILE_APPEND);
      return;
    }

    $nowMs = time() * 1000;

    Database::send(
      'UPDATE qwees_users SET subscription = ?, status = ?, count_days = ?, count_devices = ?, amount = ?, date_end = ? WHERE status != ? AND date_end < ?',
      [strval(''), strval('off'), intval(0), intval(0), intval(0), strval(''), strval('off'), date('Y-m-d')]
    );

    file_put_contents($_ENV['LOG_FILE_NAME'] ?? 'coravpn.log', "[" . date('Y-m-d H:i:s') . "] [УСПЕШНО - ГЛОБАЛЬНАЯ ОЧИСТКА] Cleanup: Обновлена БД — помечены как просроченные\n", FILE_APPEND);
  }
}