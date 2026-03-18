<?php
namespace App\Models\Router;

use App\Models\Network\Network;
use Setting\Route\Function\Functions;

class Routes extends Network
{
  /**
   * **Регистрирует маршрут по HTTP-методу, пути и обработчику**
   *
   * ---
   * ##### <u>Возможности</u>:
   * - **Методы**: 'GET', 'POST', 'PUT', 'DELETE', ...
   * - _Гибкие плейсхолдеры_ — поддержка вида `/user/{id}` или `item={sku}`
   * - <i>Любой обработчик</i>: функция, `[Класс, метод]`, имя метода класса функций
   *
   * ---
   * **Параметры**:
   * - <b>string</b> <code>$method</code>
   *   HTTP-метод маршрута, например: `'GET'`, `'POST'`
   * - <b>string</b> <code>$path</code>
   *   URI-путь, например: <kbd>/user/{id}</kbd>
   * - <b>callable|array|string</b> <code>$callback</code>
   *   Функция-обработчик, массив [Класс, метод] или строка — имя метода
   *
   * ---
   * **Примеры использования:**
   * 1. <kbd>self::addRoute('GET', '/user/{id}', [UserController::class, 'show']);</kbd>
   *    <sub>— отдаёт обработку UserController::show для пути /user/123</sub>
   * 2. <kbd>self::addRoute('POST', '/login', 'loginFunction');</kbd>
   *    <sub>— вызывает встроенную функцию loginFunction для POST /login</sub>
   *
   * @return void
   */
  public static function addRoute(string $method, string $path, $callback): void
  {
    $method = strtoupper($method);

    // Преобразуем плейсхолдеры в пути в именованные группы RegExp
    // Примеры преобразования:
    // "/user/{id}"         => "~^/user/(?P<id>[^/]+)$~"
    // "/item={sku}/view"   => "~^/item=(?P<sku>[^/&?]+)/view$~"

    // Сначала ищем ключ=значение с плейсхолдером: параметр в виде "item={sku}"
    $path = preg_replace('~([a-zA-Z0-9_-]+)=\{([a-zA-Z0-9_]+)\}~', '$1=(?P<$2>[^/&?]+)', $path);
    // Затем обычный плейсхолдер "{id}"
    $pattern = preg_replace('~\{([a-zA-Z0-9_]+)\}~', '(?P<$1>[^/]+)', $path);
    // Начало и конец строки, общая регулярка
    $pattern = "~^" . $pattern . "$~";

    // вызов встроенных функций
    if (is_string($callback) && method_exists(Functions::class, $callback)) {
      Network::$patterns[$method][$pattern] = [Functions::class, $callback];
      //вызов ручных функций
    } elseif (is_callable($callback)) {
      Network::$patterns[$method][$pattern] = $callback;
      //вызов контроллеров
    } elseif (is_array($callback) && isset($callback[0], $callback[1])) {
      Network::$patterns[$method][$pattern] = [$callback[0], $callback[1]];
    } else {
      Network::handleInvalidCallback($path, $callback);
    }
  }

  /**
   * **Быстрая регистрация GET-маршрута**
   *
   * ---
   * <b>Удобство:</b> <i>Позволяет объявлять маршруты для GET-запросов лаконично.</i>
   *
   * @param string $path      <span style="color:#777">Путь, поддерживаются плейсхолдеры</span>
   * @param callable|array|string $callback <span style="color:#777">Функция-обработчик/класс/метод</span>
   *
   * ---
   * <b>Примеры:</b>
   * - <kbd>self::get('/article/{slug}', [ArticleController::class, 'view'])</kbd>
   * - <kbd>self::get('/login', 'loginForm')</kbd>
   *
   * ---
   * @return void
   */
  public static function get(string $path, $callback): void
  {
    self::addRoute('GET', $path, $callback);
  }

  /**
   * **Быстрая регистрация POST-маршрута**
   *
   * ---
   * <b>Для обработки форм и POST-запросов.</b>
   *
   * @param string $path                URI с плейсхолдерами или без
   * @param callable|array|string $callback   Любая функция, [класс, метод] или имя метода встроенного класса
   *
   * ---
   * <b>Примеры:</b>
   * - <code>self::post('/profile/{id}/update', [ProfileController::class, 'update'])</code>
   * - <code>self::post('/login', 'submitLogin')</code>
   *
   * ---
   * @return void
   */
  public static function post(string $path, $callback): void
  {
    self::addRoute('POST', $path, $callback);
  }

  /**
   * <b>Запуск маршрутизатора и обработка текущего запроса</b>
   *
   * ---
   * <u>Что делает:</u>
   * - Определяет HTTP-метод и запрошенный URI
   * - **Сопоставляет** маршрут с зарегистрированными шаблонами (регулярными выражениями)
   * - Передаёт параметры адреса **обработчику** (функция, контроллер или встроенный метод)
   * - *Если ничего не найдено — автоматически отдаёт страницу 404*
   *
   * ---
   * <b>Пример запуска:</b>
   * <code>Routes::dispatch();</code>
   *
   * <details><summary>Варианты маршрутов</summary>
   * <ul>
   *   <li><code>GET /user/{id}</code> вызовет <i>UserController::show</i></li>
   *   <li><code>POST /login</code> вызовет <i>loginFunction</i></li>
   * </ul>
   * </details>
   *
   * ---
   * @return void
   */
  public static function dispatch(): void
  {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    if ($uri === '') {
      $uri = '/';
    }

    $routes = Network::$patterns[$method] ?? [];

    foreach ($routes as $pattern => $callback) {
      if (preg_match($pattern, $uri, $matches)) {
        array_shift($matches);

        // Извлекаем именованные параметры
        // Пример:
        //   URI:            '/pay_send/100/VPN7/123456'
        //   $matches:       [0=>'100', 1=>'VPN7', 2=>'123456']
        //   $named_params:  ['param_function_1'=>'100', 'param_function_2'=>'VPN7', 'param_function_3'=>'123456']
        $named_params = [];
        foreach ($matches as $key => $value) {
          if (is_string($key)) {
            $named_params[$key] = $value;
          }
        }

        if (is_array($callback) && isset($callback[0], $callback[1])) {
          // Контроллеры получают позиционные аргументы пути:
          //   $controller->$action(...$matches)
          // где порядок соответствует порядку плейсхолдеров в маршруте
          $controllerClass = $callback[0];
          $action = $callback[1];

          // Проверяем, является ли метод статическим
          $reflection = new \ReflectionClass($controllerClass);
          $isStatic = false;
          if ($reflection->hasMethod($action)) {
            $method = $reflection->getMethod($action);
            $isStatic = $method->isStatic();
          }

          if ($isStatic) {
            // Вызываем статический метод напрямую
            if (method_exists($controllerClass, $action)) {
              if (isset($callback[2])) {
                $params_string = $callback[2];
                $params = [];
                if (!empty($params_string)) {
                  $params_string = trim($params_string);
                  if (preg_match('/^[\'"](.+)[\'"]$/', $params_string, $quote_matches)) {
                    $params = [$quote_matches[1]];
                  }
                }
                $controllerClass::$action(...$params);
              } else {
                $controllerClass::$action(...$matches);
              }
              return;
            }
          } else {
            // Вызываем метод через экземпляр
            $controller = is_string($controllerClass) ? new $controllerClass : $controllerClass;
            if (method_exists($controller, $action)) {
              // Если есть третий элемент (параметры для метода класса)
              if (isset($callback[2])) {
                $params_string = $callback[2];
                $params = [];
                if (!empty($params_string)) {
                  $params_string = trim($params_string);
                  if (preg_match('/^[\'"](.+)[\'"]$/', $params_string, $quote_matches)) {
                    $params = [$quote_matches[1]];
                  }
                }
                $controller->$action(...$params);
              } else {
                $controller->$action(...$matches);
              }
              return;
            }
          }
        } elseif (is_callable($callback)) {
          // Замыкания/функции получают именованные параметры по ключам
          // Пример вызова для URI выше: function ($amount, $description, $telegram_id)
          //   call_user_func_array($callback, ['amount'=>'100','description'=>'VPN7','telegram_id'=>'123456'])
          call_user_func_array($callback, $named_params);
          return;
        }
        Network::handleInvalidCallback($uri, $callback);
        return;
      }
    }

    // Если маршрут не найден, показываем 404
    self::error_404($uri);
  }

  /**
   * <b>Выводит страницу 404 для несуществующего маршрута</b>
   *
   * @param string $path URI или путь, по которому не найден маршрут
   * @return void
   */
  public static function error_404(
    string $path
  ) {
    $link = dirname(__DIR__, 2) . '/Models/Router/view/404/404.html';
    if (file_exists($link)) {
      include_once $link;
    }
  }

  /**
   * **Автоматическое подключение и отображение view-элементов**
   *
   * _Подключает файл по указанному пути с передачей параметров (если они есть), либо показывает 404._
   *
   * ---
   * @param string $path <b>Путь</b> к подключаемому PHP/HTML-файлу
   * @param array  $params <i>Массив параметров</i> (будут доступны как переменные внутри файла)
   *
   * @return void
   *
   * ---
   * <b>Примеры:</b>
   * - <kbd>Routes::auto_element('/view/blocks/user_card.php', ['user'=>$user]);</kbd>
   * - <kbd>Routes::auto_element('/templates/404.php');</kbd>
   *
   * ---
   */
  public static function auto_element(string $path, array $params = [])
  {
    if (file_exists($path)) {
      if (!empty($params)) {
        extract($params, EXTR_SKIP);
      }
      include_once $path;
    } else {
      self::error_404(__METHOD__);
    }
  }

}
include_once dirname(__DIR__, 3) . '/setting/route/function/functions.php';
