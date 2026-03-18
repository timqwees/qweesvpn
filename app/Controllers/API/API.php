<?php
namespace App\Controllers\API;

class API
{
  /**
   * ## Унифицированный генератор JSON-ответов для API
   *
   * Этот метод автоматически формирует и отправляет структурированный JSON-ответ для любого API-запроса.
   *
   * ---
   *
   * ### Функционал:
   * - Устанавливает необходимый HTTP-статус код
   * - Формирует ответ в следующем формате:
   *
   * ```json
   * {
   *   "status": "success" | "error" | "qwees_crash", // статус запроса
   *   "data": <array>,                 // полезная нагрузка (данные ответа)
   *   "message": <string>,             // поясняющее сообщение или причина ошибки
   *   "meta": {                        // дополнительные метаданные запроса
   *     "method": <string>,            // HTTP-метод обращения (GET, POST, PUT, PATCH, DELETE и пр.)
   *     "path":   <string>             // путь запроса относительно корня приложения
   *   }
   * }
   * ```
   *
   * ---
   *
   * ### Особенности
   * - Можно обращаться к API как изнутри приложения, так и с помощью cURL или других HTTP-клиентов, отправляя POST (или PUT, PATCH) запросы.
   * - При использовании cURL достаточно указать заголовок `Content-Type: application/json` и передать данные через опцию `CURLOPT_POSTFIELDS` в виде JSON-строки.
   * - Метод автоматически подхватит и обработает данные из потока `php://input`, если они были отправлены в теле POST/PUT/PATCH запроса (например, через cURL).
   * - Корректно выдает ошибку, если данные не были переданы или они пусты.
   *
   * ---
   *
   * ### Параметры
   * @param string $file     Путь до файла с JSON-данными
   * @param int $httpCode    HTTP-код ответа (по умолчанию 200)
   *
   * ---
   *
   * ### Возвращает
   * `string` — Готовую к отправке JSON-строку (результат также отправляется клиенту сразу)
   *
   * ---
   *
   * ### Пример использования:
   * `API::request('/public/resourse/json/my.json');`
   *
   * **Результат:**
   * ```json
   * {
   *   "status": "success",
   *   "data": {
   *     "profile": {
   *       "id": 1,
   *       "name": "TimQwees"
   *     }
   *   },
   *   "message": "OK",
   *   "meta": {
   *     "method": "POST",
   *     "path": "/api/profile"
   *   }
   * }
   * ```
   *
   * ---
   *
   * ### Пример использования через cURL:
   * ```bash
   * curl -X POST https://example.com/api/profile/addProfile \
   *      -H "Content-Type: application/json" \
   *      -d '{"profile": {"id": 1, "name": "TimQwees"}}'
   * ```
   *
   * ---
   * - `[i]` Рекомендуется использовать для всех API-эндпоинтов, возвращающих JSON
   * - `[i]` Можно также вызывать как `API::send(...)` (альтернативное имя)
   * - `[i]` Поддерживаются как внутренние обращения из кода, так и обращения через cURL и другие HTTP-клиенты.
   */
  public static function request(string $file, int $httpCode = 200)
  {
    //get data with file
    $data = json_decode(file_get_contents(dirname(__DIR__, 3) . $file), true) ?: [];
    //first check method curl
    if ($method = in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'], true)) {
      $CURLOPT_POSTFIELDS = json_decode(file_get_contents('php://input'), true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($CURLOPT_POSTFIELDS)) {
        file_put_contents(dirname(__DIR__, 3) . $file, json_encode(array_replace_recursive($data, $CURLOPT_POSTFIELDS), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
      }
    }

    //json data
    $api_result = [
      'status' => ($httpCode >= 200 && $httpCode < 300) ? 'success' : 'error',
      'data' => null,
      'message' => null,
      'meta' => [
        'method' => $_SERVER['REQUEST_METHOD'],
        'path' => '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')
      ]
    ];

    //path
    $API_URL = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
    $args = (is_array($API_URL) && isset($API_URL[0]) && !empty($API_URL[0])) ? $API_URL : ['invalid'];
    $isAPI = ($args[0] === ($_ENV['API_URL'] ?? 'api'));

    try {
      if (!$isAPI) {
        $api_result['status'] = 'qwees_crash';
        $api_result['data'] = [
          [
            'field' => 'url',
            'error' => 'Invalid API endpoint'
          ]
        ];
        $api_result['message'] = 'Invalid API URL, url should start with /' . ($_ENV['API_URL'] ?: 'api') . '/...';
        $httpCode = 404;
      } elseif ($method) {//empty curl
        $api_result['status'] = 'error';
        $api_result['data'] = [
          [
            'field' => 'data',
            'error' => 'No data available'
          ]
        ];
        $api_result['message'] = 'Данные отсутствуют или пусты/data no have or empty';
      } else {
        $api_result['data'] = $data;
        $api_result['message'] = 'OK';
      }
    } catch (\Throwable $e) {
      $api_result['status'] = 'error';
      $api_result['message'] = 'Error processing request: ' . $e->getMessage();
      $api_result['data'] = [
        [
          'field' => 'exception',
          'error' => $e->getMessage(),
          'trace' => $e->getTraceAsString()
        ]
      ];
      $httpCode = 500;
    }

    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    return json_encode([
      'status' => $api_result['status'],
      'data' => $api_result['data'],
      'message' => $api_result['message'],
      'meta' => $api_result['meta']
    ], JSON_UNESCAPED_UNICODE);
  }


  /**
   * ## Унифицированный генератор JSON-ответов для API
   *
   * Этот метод автоматически формирует и отправляет структурированный JSON-ответ для любого API-запроса.
   *
   * ---
   *
   * ### Функционал:
   * - Устанавливает необходимый HTTP-статус код
   * - Формирует ответ в следующем формате:
   *
   * ```json
   * {
   *   "status": "success" | "error" | "qwees_crash", // статус запроса
   *   "data": <array>,                 // полезная нагрузка (данные ответа)
   *   "message": <string>,             // поясняющее сообщение или причина ошибки
   *   "meta": {                        // дополнительные метаданные запроса
   *     "method": <string>,            // HTTP-метод обращения (GET, POST, PUT, PATCH, DELETE и пр.)
   *     "path":   <string>             // путь запроса относительно корня приложения
   *   }
   * }
   * ```
   *
   * ---
   *
   * ### Особенности
   * - Можно обращаться к API как изнутри приложения, так и с помощью cURL или других HTTP-клиентов, отправляя POST (или PUT, PATCH) запросы.
   * - При использовании cURL достаточно указать заголовок `Content-Type: application/json` и передать данные через опцию `CURLOPT_POSTFIELDS` в виде JSON-строки.
   * - Метод автоматически подхватит и обработает данные из потока `php://input`, если они были отправлены в теле POST/PUT/PATCH запроса (например, через cURL).
   * - Корректно выдает ошибку, если данные не были переданы или они пусты.
   *
   * ---
   *
   * ### Параметры
   * @param string $file     Путь до файла с JSON-данными
   * @param int $httpCode    HTTP-код ответа (по умолчанию 200)
   *
   * ---
   *
   * ### Возвращает
   * `string` — Готовую к отправке JSON-строку (результат также отправляется клиенту сразу)
   *
   * ---
   *
   * ### Пример использования:
   * `API::send('/public/resourse/json/my.json');`
   *
   * **Результат:**
   * ```json
   * {
   *   "status": "success",
   *   "data": {
   *     "profile": {
   *       "id": 1,
   *       "name": "TimQwees"
   *     }
   *   },
   *   "message": "OK",
   *   "meta": {
   *     "method": "POST",
   *     "path": "/api/profile"
   *   }
   * }
   * ```
   *
   * ---
   *
   * ### Пример использования через cURL:
   * ```bash
   * curl -X POST https://example.com/api/profile/addProfile \
   *      -H "Content-Type: application/json" \
   *      -d '{"profile": {"id": 1, "name": "TimQwees"}}'
   * ```
   *
   * ---
   * - `[i]` Рекомендуется использовать для всех API-эндпоинтов, возвращающих JSON
   * - `[i]` Можно также вызывать как `API::request(...)` (альтернативное имя)
   * - `[i]` Поддерживаются как внутренние обращения из кода, так и обращения через cURL и другие HTTP-клиенты.
   */
  public static function send(string $file, int $httpCode = 200)
  {
    return self::request($file, $httpCode);
  }
}
