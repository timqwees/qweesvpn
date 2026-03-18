<?php
/**
 *
 *  _____                                                                                _____
 * ( ___ )                                                                              ( ___ )
 *  |   |~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~|   |
 *  |   |                                                                                |   |
 *  |   |                                                                                |   |
 *  |   |    ________  ___       __   _______   _______   ________                       |   |
 *  |   |   |\   __  \|\  \     |\  \|\  ___ \ |\  ___ \ |\   ____\                      |   |
 *  |   |   \ \  \|\  \ \  \    \ \  \ \   __/|\ \   __/|\ \  \___|_                     |   |
 *  |   |    \ \  \\\  \ \  \  __\ \  \ \  \_|/_\ \  \_|/_\ \_____  \                    |   |
 *  |   |     \ \  \\\  \ \  \|\__\_\  \ \  \_|\ \ \  \_|\ \|____|\  \                   |   |
 *  |   |      \ \_____  \ \____________\ \_______\ \_______\____\_\  \                  |   |
 *  |   |       \|___| \__\|____________|\|_______|\|_______|\_________\                 |   |
 *  |   |             \|__|                                 \|_________|                 |   |
 *  |   |    ________  ________  ________  _______   ________  ________  ________        |   |
 *  |   |   |\   ____\|\   __  \|\   __  \|\  ___ \ |\   __  \|\   __  \|\   __  \       |   |
 *  |   |   \ \  \___|\ \  \|\  \ \  \|\  \ \   __/|\ \  \|\  \ \  \|\  \ \  \|\  \      |   |
 *  |   |    \ \  \    \ \  \\\  \ \   _  _\ \  \_|/_\ \   ____\ \   _  _\ \  \\\  \     |   |
 *  |   |     \ \  \____\ \  \\\  \ \  \\  \\ \  \_|\ \ \  \___|\ \  \\  \\ \  \\\  \    |   |
 *  |   |      \ \_______\ \_______\ \__\\ _\\ \_______\ \__\    \ \__\\ _\\ \_______\   |   |
 *  |   |       \|_______|\|_______|\|__|\|__|\|_______|\|__|     \|__|\|__|\|_______|   |   |
 *  |   |                                                                                |   |
 *  |   |                                                                                |   |
 *  |___|~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~|___|
 * (_____)                                                                              (_____)
 *
 * Эта программа является свободным программным обеспечением: вы можете распространять ее и/или модифицировать
 * в соответствии с условиями GNU General Public License, опубликованными
 * Фондом свободного программного обеспечения (Free Software Foundation), либо в версии 3 Лицензии, либо (по вашему выбору) в любой более поздней версии.
 *
 *
 * @license GPL-3.0-or-later (см. файл LICENSE.txt)
 * @author TimQwees
 * @link https://github.com/TimQwees/Qwees_CorePro
 *
 *
 */

namespace App\Models\Network;

use App\Config\{Database, Session};
use App\Models\Router\Routes;
use App\Models\Network\Message;
use PHPMailer\PHPMailer\PHPMailer;

class Network extends Session
{
  public static $db;

  ### PATTERNS ROUTER PAGE ###
  public static array $patterns = [
    'GET' => [],
    'POST' => []
  ];

  public function __construct()
  {
    self::$db = Database::getConnection();
  }

  /**
   * **Инициализация всех таблиц из schema.sql**
   *
   * -- _Этот метод автоматически создаёт все отсутствующие таблицы, определённые в вашей схеме базы данных_
   *
   * ---
   * **Применение:**
   * - ✔ <b>Автоматическая инициализация структуры БД</b> при первом запуске или обновлении структуры
   *
   * ---
   * **Выполняет:**
   *  - Читает файл схемы (`schema.sql`)
   *  - Проверяет существование каждой таблицы
   *  - Создаёт отсутствующие таблицы
   *
   * ---
   * **Примеры:**
   * - <code>Network::onTableAllExists();</code>
   *
   * ---
   * @return void
   * @see Network::onTableExists() для проверки отдельных таблиц
   */
  public static function onTableAllExists()
  {
    $schemaFile = Database::$schema_name;
    if (!file_exists($schemaFile)) {
      Message::set('error', "Файл схемы не найден: $schemaFile");
      return;
    }
    $schema = file_get_contents($schemaFile);
    if ($schema === false || trim($schema) === '') {
      Message::set('error', "Схема пуста или не может быть прочитана: $schemaFile");
      return;
    }

    $tables = [];
    if (preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+([`"]?)([a-zA-Z0-9_]+)\1/i', $schema, $matches)) {
      $tables = $matches[2];

      foreach ($tables as $table) {
        if (!self::onTableExists($table)) {
          try {
            $pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?' . preg_quote($table, '/') . '[`"]?\s*\((.*?)\);/is';
            if (preg_match($pattern, $schema, $tableMatch)) {
              $createSql = "CREATE TABLE IF NOT EXISTS `$table` (" . trim($tableMatch[1]) . ");";

              // Преобразуем SQLite синтаксис в MySQL синтаксис для MySQL
              $db_selection = $_ENV['DATABASE'] ?? getenv('DATABASE') ?? 'mysql';
              if ($db_selection === 'mysql') {
                $createSql = preg_replace('/INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT/i', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY', $createSql);
              }

              Database::send($createSql);
              Message::set('info', "Создана таблица '$table' по схеме.");
            } else {
              Message::set('error', "Не удалось найти SQL для создания таблицы '$table' в " . Database::$schema_name);
            }
          } catch (\PDOException $e) {
            Message::set('error', "Ошибка при создании таблицы '$table': " . $e->getMessage());
          }
        }
      }
    }
  }

  /**
   * **Проверка существования таблицы в базе данных**
   *
   * _Метод для автоматической проверки: есть ли указанная таблица._
   *
   * ---
   * **Использование:**
   * - <i>Возвращает <b>true</b>, если таблица существует, иначе <b>false</b>.</i>
   *
   * ---
   * **Параметры:**
   * - <b>$tableName</b> (string): Имя таблицы для проверки.
   *
   * ---
   * <b>Пример:</b>
   * <code>Network::onTableExists('users');</code>
   *
   * ---
   * @param string $tableName Имя таблицы для проверки
   * @return bool
   * @see Network::onTableAllExists() для пакетной инициализации всех таблиц
   */
  private static function onTableExists(string $tableName)
  {
    try {
      $db_selection = $_ENV['DATABASE'] ?? getenv('DATABASE') ?? 'mysql';
      if ($db_selection === 'sqlite') {
        $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name=?";
        $params = [$tableName];
        $result = Database::send($sql, $params);
        return is_array($result) && count($result) > 0;
      } else {
        // MySQL
        $sql = "SHOW TABLES LIKE ?";
        $params = [$tableName];
        $result = Database::send($sql, $params);
        return is_array($result) && count($result) > 0;
      }
    } catch (\PDOException $e) {
      Message::set('error', "Ошибка при проверке существования таблицы '$tableName': " . $e->getMessage());
      return false;
    }
  }

  /**
   * **Авто-проверка и добавление колонки в таблицу**
   *
   * _Позволяет убедиться, что указанная колонка присутствует. Если колонки нет — она будет создана автоматически._
   *
   * ---
   * **Сценарии использования:**
   * 1. Проверить и создать новую колонку
   *    - <code>$this->onColumnExists('my_column', 'users');</code>
   *
   * ---
   * **Аргументы функции:**
   * - <b>$columnName</b> (string): название добавляемой/проверяемой колонки.
   * - <b>$tableName</b>  (string): таблица для проверки/создания.
   *
   * ---
   * **Возвращает:** <b>true</b> при успехе, <b>false</b> — при ошибке.
   *
   * ---
   * @param string $columnName
   * @param string $tableName
   * @return bool
   */
  public static function onColumnExists(string $columnName, string $tableName)
  {
    try {
      $db_selection = $_ENV['DATABASE'] ?? getenv('DATABASE') ?? 'mysql';

      // Проверяем, существует ли колонка в таблице
      $columnExists = false;

      if ($db_selection === 'sqlite') {
        $sql = "PRAGMA table_info($tableName)";
        $columns = Database::send($sql);
        if (is_array($columns)) {
          foreach ($columns as $column) {
            if (isset($column['name']) && $column['name'] === $columnName) {
              $columnExists = true;
              break;
            }
          }
        }
      } else {
        // MySQL
        $sql = "SHOW COLUMNS FROM `$tableName` LIKE ?";
        $columns = Database::send($sql, [$columnName]);
        if (is_array($columns) && count($columns) > 0) {
          $columnExists = true;
        }
      }

      // Если не существует, пытаемся добавить колонку
      if (!$columnExists) {
        $addColumnSql = ($db_selection === 'sqlite')
          ? "ALTER TABLE \"$tableName\" ADD COLUMN \"$columnName\" TEXT"
          : "ALTER TABLE `$tableName` ADD COLUMN `$columnName` VARCHAR(255)";
        Database::send($addColumnSql);
        Message::set('error', "Создание новой колонки '$columnName' в таблице '$tableName'");
      }

      return true;
    } catch (\Throwable $e) {
      Message::set('error', "Ошибка при проверке/создании колонки '$columnName' в таблице '$tableName': " . $e->getMessage());
      return false;
    }
  }

  /**
   * **Перенаправление пользователя**
   *
   * -- _Безопасно выполняет redirect на указанный путь. Обнаруживает циклические и некорректные перенаправления._
   *
   * ---
   * **Аргументы:**
   * - <b>$path</b> (string): Путь для перенаправления (например, <code>'/profile'</code>)
   *
   * ---
   * **Возвращает:**
   * - <code>false</code> (в случае ошибки)
   * - <i>Завершает выполнение скрипта при успешном редиректе</i>
   *
   * ---
   * **Формат использования:**
   * <code>Network::onRedirect('/profile');</code>
   *
   * ---
   * @param string $path путь для перенаправления (абсолютный)
   * @throws \Exception при ошибке редиректа
   * @return bool false при ошибке, exit при успехе
   */
  public static function onRedirect(string $path)
  {
    try {
      if (empty($path)) {
        throw new \Exception("Путь для перенаправления не может быть пустым");
      }

      // Убираем дублирование search в пути
      $path = preg_replace('#^/search/search/#', '/search/', $path);
      $path = preg_replace('#^search/search/#', 'search/', $path);

      // Проверяем на бесконечные редиректы (только для GET запросов)
      // После POST запроса редирект на ту же страницу допустим
      $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
      $normalizedPath = parse_url($path, PHP_URL_PATH) ?? $path;

      // Нормализуем пути (убираем лишние слэши)
      $currentUri = preg_replace('#/+#', '/', rtrim($currentUri, '/')) ?: '/';
      $normalizedPath = preg_replace('#/+#', '/', rtrim($normalizedPath, '/')) ?: '/';

      // Проверяем только для GET запросов, чтобы разрешить POST -> GET редиректы
      if ($_SERVER['REQUEST_METHOD'] === 'GET' && $currentUri === $normalizedPath) {
        throw new \Exception("Обнаружен циклический редирект на: " . $path);
      }

      // Добавляем слеш в начало, если его нет
      if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
      }

      if (ob_get_level()) {
        ob_end_clean(); // чистим буфер вывода
      }

      if (headers_sent($file, $line)) {
        throw new \Exception("Заголовки уже были отправлены в файле $file на строке $line");
      }

      header("Location: " . $path, true, 302);
      exit();

    } catch (\Exception $e) {
      Message::set('error', "Ошибка при перенаправлении: " . $e->getMessage());

      if (!headers_sent()) {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Произошла внутренняя ошибка. Пожалуйста, проверьте ваше интернет соединение!";
        exit();
      } else {
        return false;
      }
    }
  }

  /**
   * <b>Запуск маршрутизации в приложении</b>
   *
   * -- _Автоматически выбирает метод и маршрут, запускает контроллеры и методы в зависимости от совпадения_
   *
   * ---
   * **Сценарии использования:**
   * - Запустить маршрутизацию: <code>Network::onRoute();</code>
   *
   * ---
   * **Поддерживает:**
   *  - GET/POST/HEAD маршруты
   *  - Фолбэк GET-обработчиков для POST/HEAD
   *  - Очистку и унификацию маршрутов
   *  - Автоматический вызов метода контроллера или callables
   *
   * ---
   * @return void
   * @see self::$patterns для добавления маршрутов вручную
   */
  public static function onRoute()
  {
    self::onAutoloadRegister();
    Database::getConnection();
    // Определяем HTTP-метод
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Получаем текущий маршрут из .htaccess или REQUEST_URI
    if (isset($_GET['route'])) {
      $route = trim($_GET['route']);
      // Убираем все ведущие и конечные слэши, затем добавляем ровно один ведущий слэш
      $route = '/' . ltrim($route, '/');
      // Если после обработки $route стал пустым (например, был только слэш), делаем его "/"
      if ($route === '/') {
        // ничего не делаем, уже "/"
      } elseif ($route === '//') {
        $route = '/';
      }
    } else {
      $route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
      // Убираем дублирующиеся слэши (например, // -> /)
      $route = preg_replace('#/+#', '/', $route);
      if ($route === '') {
        $route = '/';
      }
    }

    // Убираем дублирующиеся слэши в любом случае (например, // -> /)
    $route = preg_replace('#/+#', '/', $route);

    $findRoute = false;

    // Порядок методов для поиска маршрута: текущий, затем безопасные фолбэки
    $candidateMethods = [$method];
    if ($method === 'POST') {
      $candidateMethods[] = 'GET';
    } elseif ($method === 'HEAD') {
      $candidateMethods[] = 'GET';
    }

    foreach ($candidateMethods as $candidateMethod) {
      $routes = self::$patterns[$candidateMethod] ?? [];
      foreach ($routes as $pattern => $callback) {
        if (preg_match($pattern, $route, $matches)) {
          $findRoute = true;
          array_shift($matches); // убираем полный путь

          if (is_array($callback) && isset($callback[0], $callback[1]) && class_exists($callback[0])) {
            $controller = new $callback[0];
            $action = $callback[1];
            if (method_exists($controller, $action)) {
              $controller->$action(...$matches);
            } else {
              self::handleInvalidCallback($route, $callback);
            }
          } elseif (is_callable($callback)) {
            // Извлекаем именованные параметры для callables (совместимо с {param} в пути)
            $named_params = [];
            foreach ($matches as $key => $value) {
              if (is_string($key)) {
                $named_params[$key] = $value;
              }
            }
            call_user_func_array($callback, array_values($named_params));
          } else {
            self::handleInvalidCallback($route, $callback);
          }

          break 2; // найден маршрут, выходим из обоих циклов
        }
      }
    }

    if (!$findRoute) {
      header("HTTP/1.1 404 Страница не найдена");
      (new Routes())->error_404($route);
      exit();
    }
  }

  /**
   * <b>Авто-загрузчик классов приложения</b>
   *
   * -- _Гарантирует автоматическую подгрузку всех классов из пространства имён App\Models\..._
   *
   * ---
   * **Использование:**
   * - Для автозагрузки классов: <code>Network::onAutoloadRegister();</code>
   *
   * ---
   * @return void
   * @see https://www.php.net/manual/ru/function.spl-autoload-register.php для низкоуровневой информации
   */
  public static function onAutoloadRegister(): void
  {
    spl_autoload_register(function ($className) {

      $filePath = dirname(__DIR__) . '/' . str_replace(['\\', 'App\Models'], ['/', ''], $className) . '.php';

      if (file_exists($filePath)) {//have't file
        require_once $filePath;
      } else {
        Message::set('error', "Ошибка загрузки класса '$className'. Файл не существует по пути: $filePath");
      }
    });
  }

  /**
   * <b>Отправка email через PHPMailer</b>
   *
   * -- <i>Фасад для быстрой отправки писем в современном формате. Учитывает все параметры письма.</i>
   *
   * ---
   * **Сценарии:**
   * - Массовая рассылка, служебное сообщение, уведомления.
   *
   * ---
   * **Ожидаемые ключи в массиве <code>$data</code>:**
   *   - <b>'to_email'</b>   — email получателя (string)
   *   - <b>'subject'</b>    — тема письма (string)
   *   - <b>'body'</b>       — HTML/текст для тела (string)
   *
   * ---
   * **Пример вызова:**
   * <pre>
   * $this->onPHPMailer([
   *     'to_email' => 'user@example.com',
   *     'subject'  => 'Тема письма',
   *     'body'     => '&lt;b&gt;Привет!&lt;/b&gt; Это тест.'
   * ]);
   * </pre>
   *
   * ---
   * @param array $data ассоциативный массив с параметрами письма
   * @return bool true, если письмо отправлено успешно, иначе false
   */
  public function onPHPMailer(array $data)
  {

    try {
      $mail = new PHPMailer();
      $mail->CharSet = 'UTF-8';

      $mail->isSMTP();
      $mail->SMTPAuth = true;
      $mail->SMTPDebug = 0;

      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //TLS

      $mail->Port = $_ENV['EMAIL_PORT'] ?: '';
      $mail->Username = $_ENV['EMAIL'] ?: '';
      $mail->Password = $_ENV['EMAIL_PASSWORD'] ?: '';
      $mail->setFrom($_ENV['EMAIL'] ?: '', $_ENV['EMAIL_NICKNAME'] ?: '');
      $mail->addAddress($data['to_email']);
      $mail->Subject = $data['subject'];

      $mail->msgHTML($data['body']);

      // Прикрепить файл
      //$mail->addAttachment('path_to_file.jpg');

      //Отправка
      $mail->send();
      $mail->clearAddresses();

      return true;
    } catch (\Exception $e) {
      Message::set('error', 'Ошибка при отправке письма: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * <b>Обработка некорректных callback-ов для маршрутов</b>
   *
   * -- <i>Автоматически отображает страницу с ошибкой при попытке вызова неизвестного метода/обработчика</i>
   *
   * ---
   * **Аргументы:**
   * - <b>$path</b> (string): путь маршрута
   * - <b>$callback</b>: объект или функция, вызвавшие ошибку
   *
   * ---
   * <b>Использование:</b>
   * <code>Network::handleInvalidCallback('/profile', 'UserController@show');</code>
   *
   * ---
   * @param string $path
   * @param mixed $callback
   * @return void
   */
  public static function handleInvalidCallback(string $path, $callback): void
  {
    $callbackType = is_object($callback) ? get_class($callback) : gettype($callback);
    $callbackName = is_string($callback) ? $callback : $callbackType;
    $request_method = $_SERVER['REQUEST_METHOD'] === 'POST' ? 'post' : 'get';
    http_response_code(500);

    ob_start(); ?>
    <!DOCTYPE html>
    <html lang='en'>

    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <title>Call to unknown method: <?= htmlspecialchars($callbackName) ?></title>
      <script src='https://cdn.tailwindcss.com'></script>
    </head>

    <body class='flex items-center justify-center h-[100dvh]'>

      <div class='container mx-auto w-full p-10 flex flex-col items-center justify-center'>

        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center">
            <img src="app/Models/Network/assets/logo.png" alt="logo">
          </div>
          <span class="text-black text-2xl font-bold">QweesCore</span>
        </div>

        <div class='text-9xl font-bold text-red-500'>ERROR</div>
        <div class='text-6xl font-bold text-red-700'>UNKNOWN METHOD!</div>
        <!-- <div class='text-xl font-medium text-red-500 mt-4 opacity-70'>Call to unknown method: <span
                        class='bg-red-200 px-2'> <? echo htmlspecialchars($callbackName); ?></span>
                </div> -->

        <div class='bg-black rounded-lg p-4 text-sm font-mono relative mt-4'>
          <div class='flex gap-1 mb-2'>
            <span class='w-2 h-2 bg-red-400 rounded-full'></span>
            <span class='w-2 h-2 bg-yellow-400 rounded-full'></span>
            <span class='w-2 h-2 bg-green-400 rounded-full'></span>
          </div>
          <div class='text-gray-400 mb-2'>// Вызываеться неизвестный метод при вызове:</div>
          <div class='text-white'>
            <span class='text-blue-400'>Routes</span><span class='text-blue-300'>::</span><span
              class='text-indigo-400'><?= $request_method ?> ?></span><span class='text-yellow-300'>(</span><span
              class='text-red-300'>'</span><span class='text-green-300'><?= htmlspecialchars(
                $path
              ) ?></span><span class='text-red-300'>'</span>,
            <span class='text-red-300'>'</span><span
              class='bg-red-400 px-2'><?= htmlspecialchars($callbackName) ?></span><span class='text-red-300'>'</span><span
              class='text-yellow-300'>)</span>;
          </div>
        </div>
      </div>
    </body>

    </html>
    <?php
    echo ob_get_clean();
    exit;
  }

  /**
   * <b>Отладочная детализация</b>
   *
   * ---
   * <i>Автоматически выводит подробную информацию о состоянии, ошибках и параметрах запроса для диагностики.</i>
   *
   * ---
   * **Аргументы:**
   * - <b>$debug_info</b> (array): массив строк с подсказками/описаниями
   * - <b>$db_error</b> (mixed): массив или объект с деталями SQL/DB-ошибки (или null)
   * - <b>$title</b> (string): заголовок блока вывода
   * - <b>$as_json</b> (bool): также вывести диагностический блок в формате JSON
   *
   * ---
   * <b>Рекомендуется:</b> при обработке исключений и сложных ошибок взаимодействия с БД.
   *
   * ---
   * @return void
   */
  public static function debugOutput(array $debug_info = [], $db_error = null, string $title = 'Диагностика проблемы', bool $as_json = true)
  {
    global $DB_ERROR_INFO;

    // Если db_error не передан, пытаемся получить из глобальной переменной
    if ($db_error === null && isset($DB_ERROR_INFO)) {
      $db_error = $DB_ERROR_INFO;
    }

    $output = [];

    if (!empty($debug_info)) {
      $output['debug'] = $debug_info;
    }

    if ($db_error !== null) {
      $output['db_error'] = [
        'type' => $db_error['type'] ?? 'unknown',
        'message' => $db_error['message'] ?? '',
        'exception' => $db_error['exception'] ?? null,
        'code' => $db_error['code'] ?? null,
      ];

      if (isset($db_error['errorInfo'])) {
        $output['db_error']['pdo_error'] = $db_error['errorInfo'];
      }

      if (isset($db_error['rowCount'])) {
        $output['db_error']['rows_affected'] = $db_error['rowCount'];
      }
    }

    // HTML вывод
    echo "<pre style='background:#f0f0f0;padding:10px;border:1px solid #ccc;margin:10px;font-size:12px;'>";
    echo "<strong style='color:#d32f2f;'>" . htmlspecialchars($title) . "</strong>\n\n";

    if (!empty($debug_info)) {
      echo htmlspecialchars(implode("\n", $debug_info)) . "\n";
    }

    if ($db_error !== null) {
      echo "\n<strong style='color:#d32f2f;'>Ошибка БД:</strong>\n";
      echo "Тип: " . htmlspecialchars($db_error['type'] ?? 'unknown') . "\n";
      if (isset($db_error['message'])) {
        echo "Сообщение: " . htmlspecialchars($db_error['message']) . "\n";
      }
      if (isset($db_error['code'])) {
        echo "Код: " . htmlspecialchars($db_error['code']) . "\n";
      }
    }

    echo "</pre>";

    // JSON вывод (если нужно)
    if ($as_json) {
      echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
  }

  /**
   * <b>Быстрый минимальный вывод ошибки</b>
   *
   * -- <i>Позволяет отправить структурированное сообщение об ошибке с HTTP-кодом и деталями</i>
   *
   * ---
   * **Аргументы:**
   * - <b>$message</b> (string): текст ошибки
   * - <b>$db_error</b> (mixed): подробности из базы данных (опционально)
   * - <b>$http_code</b> (int): HTTP-код ответа
   *
   * ---
   * **Пример:**
   * <code>Network::errorOutput('Фатальная ошибка!', null, 500);</code>
   *
   * ---
   * @return void
   */
  public static function errorOutput(string $message, $db_error = null, int $http_code = 400)
  {
    http_response_code($http_code);

    global $DB_ERROR_INFO;
    if ($db_error === null && isset($DB_ERROR_INFO)) {
      $db_error = $DB_ERROR_INFO;
    }

    $debug_info = ["Ошибка: " . $message];

    if ($db_error !== null) {
      $debug_info[] = "Детали: " . ($db_error['message'] ?? 'неизвестно');
    }

    self::debugOutput($debug_info, $db_error, 'Ошибка', true);
  }


}
