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
 *  |   |   \ \  \___|\ \  \    \ \  \ \   __/|\ \   __/|\ \  \___|_                     |   |
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

namespace App\Config;

use PDO;
use RuntimeException;
use App\Models\Network\Network;
use InvalidArgumentException;

class Database extends Network
{
  private static ?PDO $instance = null;
  public static $schema_name;
  public static $database_name;
  private static $folder_sqlite;

  /**
   * **[Инициализация папки SQLite]**
   * _Инициализирует папку для хранения файлов базы данных SQLite._
   *
   * ---
   * **Типовые сценарии:**
   * - Автоматически задаёт путь к каталогу хранения, если он ещё не задан.
   * - По умолчанию использует директорию `/Storage/` в корне приложения.
   *
   * ---
   * **Возвращает:**
   * `void`
   *
   * ---
   * **Пример использования:**
   * ```
   * Database::initSqliteFolder();
   * ```
   *
   * _Описание:_
   * Устанавливает путь к папке для хранения файлов SQLite.
   */
  private static function initSqliteFolder()
  {
    if (empty(self::$folder_sqlite)) {
      self::$folder_sqlite = dirname(__DIR__, 1) . "/Storage/";
    }
  }

  /**
   * **[Инициализация имени схемы БД]**
   *
   * _Устанавливает имя схемы, используемой при работе с базой данных._
   *
   * **Возможности:**
   * - Если окружение задаёт `SCHEMA_NAME`, ищет соответствующий файл схемы в каталоге `/setting/schema/`.
   * - Если переменная не задана — используется `schema.sql` по умолчанию.
   *
   * ---
   * **Возвращает:**
   * `void`
   *
   * ---
   * **Пример:**
   * ```
   * Database::initSchemaName();
   * ```
   *
   * _Описание:_
   * Автоматически определяет имя схемы для последующей работы с БД.
   */
  public static function initSchemaName()
  {
    if (empty(self::$schema_name)) {
      self::$schema_name = dirname(__DIR__, 2) . '/setting/schema/' . (isset($_ENV['SCHEMA_NAME']) && !empty($_ENV['SCHEMA_NAME']) ? ($_ENV['SCHEMA_NAME'] . '.sql') : 'schema.sql');
    }
  }

  /**
   * **[Инициализация имени базы данных]**
   *
   * _Устанавливает имя базы данных, используемой при подключении._
   *
   * **Возможности:**
   * - Если окружение задаёт `SQLITE_DB_NAME`, оно используется как имя БД для SQLite (или `DB_NAME` для MySQL).
   * - Если переменная не задана, используется имя базы данных по умолчанию — `datebase`.
   *
   * ---
   * **Возвращает:**
   * `void`
   *
   * ---
   * **Пример:**
   * ```
   * Database::initDatabaseName();
   * ```
   *
   * _Описание:_
   * Автоматически определяет имя базы данных для использования при подключении.
   * Опирается на наличие переменных окружения SQLITE_DB_NAME (для sqlite) или DB_NAME (для mysql).
   */
  public static function initDatabaseName()
  {
    if (!empty(self::$database_name)) {
      return;
    }

    $dbType = strtolower($_ENV['DATABASE'] ?? '');

    switch ($dbType) {
      case 'sqlite':
        self::$database_name = !empty($_ENV['SQLITE_DB_NAME']) ? $_ENV['SQLITE_DB_NAME'] : 'datebase';
        break;
      case 'mysql':
        self::$database_name = !empty($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'datebase';
        break;
      default:
        self::$database_name = 'datebase';
        break;
    }
  }

  /**
   * **[Закрытие соединения с базой данных]**
   *
   * _Явно завершает текущее соединение с базой данных — устанавливает экземпляр PDO в `null`._
   *
   * ---
   * **Когда использовать:**
   * - При завершении работы приложения
   * - Для программной очистки соединения, либо пересоздания подключения
   *
   * ---
   * **Возвращает:**
   * `void`
   */
  public static function closeConnection()
  {
    self::$instance = null;
  }

  /**
   * ## Получение соединения PDO с базой данных
   *
   * _Позволяет получать готовый экземпляр PDO для выполнения SQL-запросов._
   *
   * ---
   * **Поддерживаемые режимы:**
   * - *MySQL* — при наличии соответствующих переменных окружения
   * - *SQLite* — default/fallback
   *
   * ---
   * **Автоматическое создание:**
   * Соединение создаётся лениво и кэшируется — повторный вызов возвращает уже созданный экземпляр.
   *
   * ---
   * **Возвращает:**
   * `\PDO` — объект для работы с SQL
   *
   * ---
   * **Пример использования:**
   * ```php
   * $pdo = Database::getConnection();
   * ```
   *
   * _Описание:_
   * Универсальный доступ к PDO для SQL-запросов (MySQL или SQLite).
   */
  public static function getConnection()
  {
    if (self::$instance === null) {
      self::initSqliteFolder();//получение пути storage
      self::initSchemaName();//получение имени схемы
      self::initDatabaseName();// Получаем имя базы данных
      try {
        $db_selection = $_ENV['DATABASE'] ?? getenv('DATABASE') ?? 'sqlite';
        if ($db_selection === 'sqlite') {
          $db_base = $_ENV['SQLITE_DB_NAME'] ?? getenv('SQLITE_DB_NAME') ?: 'database';
          $db_name = $db_base . '.sqlite';
          if (!file_exists(self::$folder_sqlite . $db_name)) {
            self::$instance = self::createSQlite($db_name);
          } else {
            self::$instance = new PDO('sqlite:' . self::$folder_sqlite . $db_name);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          }
        } elseif ($db_selection === 'mysql') {
          // Получаем параметры окружения для MySQL
          $DB_HOST = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '';
          $DB_PORT = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
          $DB_USERNAME = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: '';
          $DB_PASSWORD = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
          $DB_NAME = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '';

          // Проверка обязательных параметров
          if (!$DB_HOST || !$DB_PORT || !$DB_USERNAME || $DB_NAME === null || $DB_NAME === "") {
            throw new RuntimeException('Некорректные параметры подключения к MySQL. Требуются DB_HOST, DB_PORT, DB_USERNAME, DB_NAME');
          }

          // Установка опций для PDO MySQL
          $options = [
              // Включение обработки ошибок через исключения (обязательно для правильной обработки ошибок)
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
              // Устанавливаем режим по умолчанию для fetch — удобно работать с ассоциативными массивами
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
              // Отключаем эмуляцию подготовленных выражений для повышения безопасности и производительности
            PDO::ATTR_EMULATE_PREPARES => false,
              // Таймаут соединения (в секундах), чтобы избежать зависаний при проблемах сети
            PDO::ATTR_TIMEOUT => 10,
              // Использовать буферизированные результаты — удобно для работы с большими наборами данных
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
              // Настройки сессии при инициализации соединения
              // Важно для корректной работы с кодировками, режимами и тайм-аутами
            PDO::MYSQL_ATTR_INIT_COMMAND =>
              // Все общие команды разделяются точкой с запятой (MySQL разрешает одну строку)
              "SET NAMES utf8mb4;" .
              "SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';" .
              "SET SESSION innodb_lock_wait_timeout=10;" .
              "SET SESSION wait_timeout=120;" .
              "SET SESSION interactive_timeout=120;" .
              "SET SESSION net_read_timeout=60;" .
              "SET SESSION net_write_timeout=60;" .
              "SET SESSION max_execution_time=2000;" .
              "SET SESSION max_allowed_packet=16777216;" .
              "SET SESSION net_buffer_length=32768;",
          ];

          // Формируем DSN для MySQL
          $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
          // Подключение к MySQL
          self::$instance = new PDO($dsn, $DB_USERNAME, $DB_PASSWORD, $options);
        } elseif (empty($db_selection)) {
          self::closeConnection();
        } else {
          throw new RuntimeException('Неизвестный тип базы данных. Укажите DATABASE=sqlite или DATABASE=mysql в .env');
        }

        return self::$instance;
      } catch (\PDOException $e) {
        error_log("Ошибка подключения к базе данных: " . $e->getMessage());
        // Не делаем редирект здесь, чтобы избежать циклических редиректов
        throw new RuntimeException('Ошибка подключения к базе данных. Пожалуйста, проверьте настройки.');
      }
    }

    return self::$instance;//PDO
  }

  /**
   * ### Выполнение подготовленного SQL-запроса с параметрами
   *
   * _Универсальный метод для выполнения любого SQL-запроса с параметрами (безопасно)!_
   *
   * ---
   * **Аргументы:**
   * - `string $sql` — SQL-запрос с плейсхолдерами (`?`)
   * - `array $params` — параметры запроса (по порядку)
   *
   * ---
   * **Результат:**
   * - `array` — результат для SELECT/SHOW и т.п. (массив ассоциативных массивов)
   * - `true` — при успешном изменении данных (INSERT/UPDATE/DELETE)
   * - `false` — при ошибке выполнения запроса
   *
   * ---
   * **Сценарии:**
   * 1. Выполнение SELECT:
   *    ```php
   *    $data = Database::send("SELECT * FROM users WHERE id = ?", [42]);
   *    ```
   * 2. INSERT/UPDATE/DELETE:
   *    ```php
   *    $ok = Database::send("INSERT INTO ...", [$v1, $v2]);
   *    ```
   *
   * ---
   * _Описание:_
   * Выполняет любой SQL с подстановкой переменных (безопасно через prepare). Для SELECT возвращает результат, иначе — успех/неудача.
   */
  public static function send(string $sql, array $params = [])
  {
    // Проверка, что ни один из параметров не является массивом
    foreach ($params as $key => $value) {
      if (is_array($value)) {
        error_log("Ошибка: параметр (ключ: $key) должен быть скалярным, получен массив");
        throw new InvalidArgumentException("Параметр для запроса не должен быть массивом (ключ: $key)");
      }
    }

    try {
      $pdo = self::getConnection();
      if ($pdo === null)
        return false;
      $stmt = $pdo->prepare($sql);
      if ($stmt === false) {
        $errorInfo = $pdo->errorInfo();
        $errorMsg = "Ошибка подготовки запроса: " . implode(", ", $errorInfo) . " | SQL: $sql | Параметры: " . json_encode($params, JSON_UNESCAPED_UNICODE);
        error_log($errorMsg);
        return false;
      }

      // Определяем тип базы данных
      $db_selection = $_ENV['DATABASE'] ?? getenv('DATABASE') ?? 'sqlite';
      if ($db_selection === 'mysql') {
        // Явно привязываем параметры с типами для MySQL
        foreach ($params as $index => $value) {
          $paramType = PDO::PARAM_STR; // По умолчанию строка
          if (is_int($value) || is_bool($value)) {
            $paramType = is_bool($value) ? PDO::PARAM_BOOL : PDO::PARAM_INT;
          } elseif (is_null($value)) {
            $paramType = PDO::PARAM_NULL;
          }
          $stmt->bindValue($index + 1, $value, $paramType);
        }
        // Выполняем запрос без параметров, так как они уже привязаны
        $result = $stmt->execute();
      } else {
        // Для SQLite используем стандартный метод
        $result = $stmt->execute($params);
      }

      // Проверяем тип запроса (SELECT/SHOW/EXPLAIN)
      $queryType = strtoupper(strtok(ltrim($sql), " \t\n\r"));
      if (in_array($queryType, ['SELECT', 'SHOW', 'EXPLAIN', 'DESCRIBE'])) {
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data !== false ? $data : [];
      }

      // Для UPDATE/INSERT/DELETE запросов проверяем количество затронутых строк
      if (in_array($queryType, ['UPDATE', 'INSERT', 'DELETE'])) {
        $rowCount = $stmt->rowCount();
        if ($rowCount === 0 && $queryType === 'UPDATE') {
          $errorMsg = "WARNING: UPDATE запрос выполнен, но не обновил ни одной строки (0 rows affected) | SQL: $sql | Параметры: " . json_encode($params, JSON_UNESCAPED_UNICODE);
          error_log($errorMsg);
          return false;
        }
        // Для INSERT возвращаем true, если execute был успешным (даже если rowCount === 0 для некоторых СУБД)
        if ($queryType === 'INSERT') {
          return $result === true ? true : false;
        }
        error_log("DEBUG: Запрос $queryType выполнен успешно, затронуто строк: $rowCount");
      }

      // Для остальных запросов возвращаем true/false об успехе execute
      return $result;
    } catch (\PDOException $e) {
      $errorMsg = "Ошибка выполнения запроса: " . $e->getMessage() . " | SQL: $sql | Параметры: " . json_encode($params, JSON_UNESCAPED_UNICODE) . " | Code: " . $e->getCode();
      error_log($errorMsg);
      return false;
    } catch (InvalidArgumentException $e) {
      $errorMsg = "Ошибка параметров запроса: " . $e->getMessage() . " | SQL: $sql | Параметры: " . json_encode($params, JSON_UNESCAPED_UNICODE);
      error_log($errorMsg);
      return false;
    }
  }

  /**
   * **[Создание либо открытие SQLite БД]**
   *
   * _Создаёт (или открывает) файл SQLite базы данных в указанной папке (по умолчанию `/app/Storage/`)._
   *
   * ---
   * **Параметры:**
   * - `string $db_name` — имя файла SQLite (например, `'test.sqlite'`)
   *
   * ---
   * **Результат:**
   * - `\PDO` — для работы с указанной SQLite БД
   *
   * ---
   * **Типовые случаи:**
   * - БД не существует: будет создан новый файл и если найдётся файл схемы — произойдёт начальная инициализация структуры.
   * - БД существует: просто открывает и возвращает PDO к ней.
   *
   * ---
   * **Пример использования:**
   * ```php
   * $pdo = Database::createSQlite('mydb.sqlite');
   * ```
   *
   * _Описание:_
   * Создаёт/открывает SQLite БД и при необходимости заливает структуру из схемы.
   */
  public static function createSQlite($db_name)
  {
    try {
      if (!is_dir(self::$folder_sqlite)) {//не найден
        mkdir(self::$folder_sqlite, 0777, true);//создаем
      }
      $db_path = rtrim(self::$folder_sqlite, '/\\') . '/' . $db_name;
      $isDATABASE = !file_exists($db_path);
      $pdo = new PDO('sqlite:' . $db_path);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      if ($isDATABASE) {
        $schema_file = self::$schema_name;//schema
        if (file_exists($schema_file)) {
          $schema = file_get_contents($schema_file);
          if ($schema !== false && trim($schema) !== '') {
            $statements = preg_split('/;\s*[\r\n]+/', $schema);
            foreach ($statements as $statement) {
              $statement = trim($statement);
              if ($statement !== '') {
                try {
                  $pdo->exec($statement);
                } catch (\PDOException $e) {
                  error_log("Ошибка выполнения SQL для SQLite: " . $e->getMessage() . " | SQL: " . $statement);
                }
              }
            }
          }
        } else {
          error_log("Файл схемы SQLite не найден: " . $schema_file);
        }
      }

      return $pdo;
    } catch (\PDOException $e) {
      error_log("Ошибка создания SQLite: " . $e->getMessage());
      throw new RuntimeException('Ошибка создания SQLite базы данных: ' . $e->getMessage());
    } catch (RuntimeException $e) {
      error_log($e->getMessage());
      throw $e;
    }
  }

  /**
   * #### Обновление структуры SQLite по актуальной схеме
   *
   * Обновляет структуру указанной базы данных SQLite, применяя все SQL-операторы из последней версии файла схемы.
   *
   * ---
   * **Параметры:**
   * - `string $db_name` — имя файла базы данных (например, `'main.sqlite'`)
   *
   * ---
   * **Возвращает:**
   * `void`
   *
   * ---
   * **Сценарии использования:**
   * - Быстрое обновление (синхронизация DDL) структуры SQLite до актуального состояния, без применения сложных миграций.
   *
   * ---
   * **Пример использования:**
   * ```php
   * Database::updateSqlite('mydb.sqlite');
   * ```
   *
   * _Описание:_
   * Выполняет все SQL-выражения из актуального файла схемы и применяет их к выбранной базе данных SQLite, чтобы поддерживать структуру в актуальном состоянии.
   */
  public static function updateSqlite($db_name = null)
  {

    if ($db_name === null) {
      $db_name = self::$database_name . '.sqlite';
    }

    $db_path = rtrim(self::$folder_sqlite, '/\\') . '/' . $db_name;
    if (!file_exists($db_path)) {
      throw new RuntimeException("База данных не найдена: $db_path");
    }
    $schema_file = self::$schema_name;
    if (!file_exists($schema_file)) {
      throw new RuntimeException("Файл схемы не найден: $schema_file");
    }
    $schema = file_get_contents($schema_file);
    if ($schema === false || trim($schema) === '') {
      throw new RuntimeException("Схема пуста или не может быть прочитана: $schema_file");
    }
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $statements = preg_split('/;\s*[\r\n]+/', $schema);
    foreach ($statements as $statement) {
      $statement = trim($statement);
      if ($statement !== '') {
        try {
          $pdo->exec($statement);
        } catch (\PDOException $e) {
          error_log("Ошибка выполнения SQL при обновлении схемы: " . $e->getMessage() . " | SQL: " . $statement);
        }
      }
    }
  }

}
