<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin;

use App\Config\Database;
use App\Models\Network\Network;

class AdminDatabase
{
    private static array $readonlyFields = [];
    private static array $DisabledFields = [];
    private static array $RequiredFields = [];

    /* ######################## Просые функции ############################## */

    /**
     * Установить readonly поля
     * @param array $columns массив названий колонок
     * @exemple setReadonly(['name', ...]);
     * @return bool
     */
    public function setReadonly(array $columns): bool
    {
        self::$readonlyFields = [];
        foreach ($columns as $col) {
            self::$readonlyFields[$col] = 'readonly';
        }
        return true;
    }

    /**
     * Получение состяния колонки readonly 
     * @param string $column_name массив названий колонок
     * @exemple setReadonly('column_name');
     * @return mixed 'readonly' | Null
     */
    public function getReadonly(string $column_name): mixed
    {
        return self::$readonlyFields[$column_name] ?? Null;
    }

    /**
     * Установить disabled поля
     * @param array $columns массив названий колонок
     * @exemple setDisabled(['name', ...]);
     * @return bool
     */
    public function setDisabled(array $columns): bool
    {
        self::$readonlyFields = [];
        foreach ($columns as $col) {
            self::$DisabledFields[$col] = 'disabled';
        }
        return true;
    }

    /**
     * Получение состяния колонки disabled 
     * @param string $column_name массив названий колонок
     * @exemple getDisabled('column_name');
     * @return mixed 'disabled' | Null
     */
    public function getDisabled(string $column_name): mixed
    {
        return self::$DisabledFields[$column_name] ?? Null;
    }

    /**
     * Установить required поля
     * @param array $columns массив названий колонок
     * @exemple setRequired(['name', ...]);
     * @return bool
     */
    public function setRequired(array $columns): bool
    {
        self::$readonlyFields = [];
        foreach ($columns as $col) {
            self::$RequiredFields[$col] = 'required';
        }
        return true;
    }

    /**
     * Получение состяния колонки required 
     * @param string $column_name массив названий колонок
     * @exemple getRequired('column_name');
     * @return mixed 'required' | Null
     */
    public function getRequired(string $column_name): mixed
    {
        return self::$RequiredFields[$column_name] ?? Null;
    }

    /* ######################## База-данных функций ############################## */

    /**
     * Получить список всех таблиц из БД
     * @return array массив названий таблиц
     */
    public static function getTables(): array
    {
        if (Database::isMysql()) {
            // MySQL: используем SHOW TABLES
            $tables = Database::send("SHOW TABLES");
            return is_array($tables) && !empty($tables) ? array_column($tables, array_key_first($tables[0])) : [];
        } else {
            // SQLite: используем sqlite_master
            $tables = Database::send("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            return is_array($tables) && !empty($tables) ? array_column($tables, 'name') : [];
        }
    }

    /**
     * Получить колонки таблицы
     * @param string $table название таблицы
     * @return array массив названий колонок
     */
    public static function getColumns(string $table): array
    {
        // Берем первую строку и используем её ключи как названия колонок
        $data = Database::send("SELECT * FROM {$table} LIMIT 1");

        if (is_array($data) && !empty($data)) { //данные пришли
            return array_keys($data[0]);
        }

        // Если таблица пустая, используем соответствующий запрос для типа БД
        if (Database::isMysql()) {
            // MySQL: используем SHOW COLUMNS
            $info = Database::send("SHOW COLUMNS FROM {$table}");
            if (is_array($info) && !empty($info)) {
                return array_column($info, 'Field');
            }
        } else {
            // SQLite: используем PRAGMA
            $info = Database::send("PRAGMA table_info({$table})");
            if (is_array($info) && !empty($info)) {
                return array_column($info, 'name');
            }
        }

        return [];
    }

    /**
     * Получить данные таблицы с опциональным лимитом и сортировкой
     * @param string $table название таблицы
     * @param int $limit лимит строк (по умолчанию 50)
     * @param string $orderBy колонка для сортировки (по умолчанию 'id')
     * @param string $orderDir направление сортировки ASC/DESC (по умолчанию 'ASC')
     * @return array массив данных
     */
    public static function getData(string $table, int $limit = 50, string $orderBy = 'id', string $orderDir = 'ASC'): array
    {
        //SELECT * FROM users ORDER BY name DESC LIMIT 10 (именно name в сторону понижения ток 10 записей)
        $result = Database::send("SELECT * FROM $table ORDER BY $orderBy $orderDir LIMIT $limit");
        return is_array($result) ? $result : [];
    }

    /**
     * Получить одну строку по ID
     * @param string $table название таблицы
     * @param mixed $id значение ID
     * @return array|null данные строки или null
     */
    public static function getRow(string $table, $id): ?array
    {
        $result = Database::send("SELECT * FROM $table WHERE id = ? LIMIT 1", [$id]);
        return (is_array($result) && !empty($result)) ? $result[0] : null;
    }

    /**
     * Подсчитать количество строк в таблице (записей)
     * @param string $table название таблицы
     * @return int количество строк
     */
    public static function getCount(string $table): int
    {
        $result = Database::send("SELECT COUNT(*) as count FROM $table");
        return (is_array($result) && !empty($result)) ? (int) ($result[0]['count'] ?? 0) : 0;
    }

    /**
     * Фильтрация данных по колонке
     * @param string $table название таблицы
     * @param string $column название колонки
     * @param mixed $value что исчем
     * @param int $limit лимит строк
     * @param string $condition условие (=, !=, >, <)
     * @return array отфильтрованные данные
     */
    public static function filter(string $table, string $column, $value, int $limit = 50, string $condition = '='): array
    {
        $allowedConditions = ['=', '!=', '>', '<', '>=', '<='];
        if (!in_array($condition, $allowedConditions))
            $condition = '=';
        /**нету условий среди них по умолчанию будем искать сходство*/

        // Для != с пустой строкой используем специальную логику
        if ($condition === '!=' && $value === '') {
            $result = Database::send("SELECT * FROM $table WHERE $column != '' AND $column IS NOT NULL LIMIT $limit");
            return is_array($result) ? $result : [];
        }

        //SELECT * FROM users WHERE name == 'hello' LIMIT 10
        $result = Database::send("SELECT * FROM $table WHERE $column $condition ? LIMIT $limit", [$value]);
        return is_array($result) ? $result : [];
    }

    /**
     * Поиск по колонкам (LIKE OR)
     * @param string $table название таблицы
     * @param array $columns массив колонок для поиска
     * @param string $query строка поиска
     * @param int $limit лимит строк
     * @return array найденные данные
     */
    public static function search(string $table, array $columns, string $query, int $limit = 50): array
    {
        if (empty($query)) { //пустой поиск выводим все 
            return self::getData($table, $limit);
        }

        $conditions = []; //тут будут сами LIKE запросы, которые вставятся >> WHERE LOWER(col) LIKE '%' . strtolower($query) . '%'
        $params = []; //тут будут параметры которые, которые вставятся в sql запросы ?

        foreach ($columns as $col) {
            $conditions[] = "LOWER($col) LIKE ?"; //будем искать с маленькой буквы все найденные LIKE значения
            $params[] = '%' . strtolower($query) . '%'; //будем искать слово где бы оно не стояло  ..%(...)%..
            /** Мол напоминание себе:
             * %..% -  ищем в любом месте хоть до слова есть имволы или после
             * ..% - ищем чтобы в начале самом было слово а потом все что-угодно может быть написано
             * %.. - мол в конце только было именно это слово после этого слово ничего не должно быть
             * _..% - мол чтобы был пробел потом слово и все что угодно потом может быть после слова
             * >>> "слово" ставим "%" и этот символ говорит, что далее все что-угодно быть быть, главное до % найти
             */
        }

        $result = Database::send("SELECT * FROM $table WHERE " . implode(' OR ', $conditions) . " LIMIT $limit", $params);
        return is_array($result) ? $result : [];
    }

    /**
     * Обновить запись в таблице
     * @param string $table название таблицы
     * @param mixed $id значение ID записи
     * @param array $data ассоциативный массив [колонка => значение]
     * @return bool успешность операции
     */
    public static function update(string $table, $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $col = [];
        $params = [];

        foreach ($data as $column => $value) {
            $col[] = "$column = ?"; //sql запрос prepare column
            $params[] = $value; //sql запрос prepare value
        }

        $params[] = $id;
        $sql = "UPDATE {$table} SET " . implode(', ', $col) . " WHERE id = ?";

        $result = Database::send($sql, $params);
        return is_array($result) || $result === [];
    }

    // Константы для choices (аналог Django choices)
    const USER_STATUSES = [
        'on' => 'Активен',
        'off' => 'Неактивен',
        'banned' => 'Заблокирован',
    ];

    const SUBSCRIPTION_PLANS = [
        'monthly' => 'Месячная',
        'yearly' => 'Годовая',
        'lifetime' => 'Бессрочная',
    ];

    const USER_ROLES = [
        'user' => 'Пользователь',
        'manager' => 'Менеджер',
        'admin' => 'Администратор',
    ];

    // Сохранение записи || Save record
    public function onAdminSave()
    {
        $table = $_POST['table'] ?? '';
        $id = $_POST['id'] ?? '';
        $url = $_POST['url'] ?? '';
        $action = $_POST['action'] ?? '';

        if (empty($table)) {
            Network::onRedirect('/admin');
        }

        // Специальная обработка для добавления пользователя
        if ($table === 'qwees_users' && $action === 'add_user') {
            $this->addNewUser($url);
            return;
        }

        // Специальная обработка для таблицы цен — обновляем по name
        if ($table === 'qwees_price' && empty($id)) {
            $this->savePrices($url);
            return;
        }

        if (empty($id)) {
            Network::onRedirect('/admin');
        }

        // Собираем данные без table и id
        $updateData = array_diff_key($_POST, ['table' => 1, 'id' => 1, 'url' => 1, 'action' => 1]);

        // Сохраняем и редиректим
        $success = self::update($table, $id, $updateData);
        if (strpos($url, 'edit') !== false) {
            Network::onRedirect(
                $success
                ? "/admin/edit?table=" . urlencode($table) . "&id=" . urlencode($id)
                : "/admin/database?table=" . urlencode($table)
            );
        } else {
            Network::onRedirect($url);
        }
    }

    /**
     * Обработка добавления нового пользователя
     */
    private function addNewUser(string $url): void
    {
        $userData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? 'user',
            'subscription' => $_POST['subscription'] ?? '',
            'duration_days' => $_POST['duration_days'] ?? '',
            'status' => $_POST['status'] ?? 'off'
        ];

        $success = self::addUser($userData);

        // Добавляем параметр сообщения в URL для отображения результата
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $redirectUrl = $url . $separator . 'message_status=' . ($success ? 'success' : 'error') . '&message_msg=' . urlencode($success ? 'Пользователь успешно добавлен' : 'Ошибка при добавлении пользователя');

        Network::onRedirect($redirectUrl);
    }

    private function savePrices(string $url): void
    {
        // Получаем все тарифы
        $prices = self::getData('qwees_price');

        foreach ($prices as $priceRow) {
            $name = $priceRow['name'];
            if (isset($_POST[$name]) && $_POST[$name] !== '') {//поле есть
                $newPrice = (int) $_POST[$name];//получаем содержание поля name='...'
                if ($newPrice > 0) {
                    Database::send(
                        "UPDATE qwees_price SET price = ? WHERE name = ?",
                        [$newPrice, $name]
                    );
                    // Записываем в лог для отладки
                    $logFile = $_ENV['LOG_FILE_NAME'] ?? 'app.log';
                    file_put_contents(
                        $logFile,
                        sprintf(
                            "[%s] [SUCCESS] Изменена цена тарифа %s с %s ₽ на %s ₽\n",
                            date('Y-m-d H:i:s'),
                            strtoupper($name),
                            $priceRow['price'],
                            $newPrice
                        ),
                        FILE_APPEND
                    );
                }
            }
        }

        Network::onRedirect($url);
    }

    /* ######################## Состовные функции ############################## */

    /**
     * URL для редактирования записи (аналог get_absolute_url)
     */
    public static function editUrl(string $table, $id): string
    {
        return "/admin/edit?table=" . urlencode($table) . "&id=" . urlencode($id);
    }

    /**
     * Агрегация COUNT/AVG/SUM/MAX/MIN (аналог Django aggregate)
     */
    public static function aggregate(string $table, string $func, string $column = '*', string $where = ''): mixed
    {
        $sql = "SELECT {$func}({$column}) as result FROM {$table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $result = Database::send($sql);
        return $result[0]['result'] ?? 0;
    }

    /**
     * Форматирование даты для вывода
     */
    public static function formatDate(?string $date, string $format = 'd.m.Y'): string
    {
        if (empty($date))
            return '-';
        return date($format, strtotime($date));
    }

    /**
     * Подсчет пользователей по статусу подписки (JOIN с qwees_subscriptions)
     * @param string $status статус подписки ('on', 'off', 'banned')
     * @return int количество пользователей
     */
    public static function countUsersBySubscriptionStatus(string $status): int
    {
        $sql = "SELECT COUNT(*) as count FROM qwees_users u 
                LEFT JOIN qwees_subscriptions s ON u.uniID = s.uniID 
                WHERE s.status = ? OR (s.status IS NULL AND ? = 'off')";
        $result = Database::send($sql, [$status, $status]);
        return (int) ($result[0]['count'] ?? 0);
    }

    /**
     * Получить всех пользователей с данными подписок (JOIN)
     * @param int $limit лимит строк
     * @return array массив пользователей с подписками
     */
    public static function getUsersWithSubscriptions(int $limit = 50): array
    {
        $sql = "SELECT u.*, s.status as sub_status, s.subscription, s.amount, s.count_days, s.count_devices, s.date_end 
                FROM qwees_users u 
                LEFT JOIN qwees_subscriptions s ON u.uniID = s.uniID 
                ORDER BY u.id DESC 
                LIMIT ?";
        return Database::send($sql, [$limit]);
    }

    /**
     * Получить статистику по клиентам и подпискам
     * @return array массив со статистикой
     */
    public static function getClientStats(): array
    {
        // Общая статистика
        $totalUsers = self::aggregate('qwees_users', 'COUNT');
        $totalSubscriptions = self::aggregate('qwees_subscriptions', 'COUNT');

        // Статистика по статусам подписок
        $activeSubscriptions = self::aggregate('qwees_subscriptions', 'COUNT', '*', "status = 'on'");
        $inactiveSubscriptions = self::aggregate('qwees_subscriptions', 'COUNT', '*', "status = 'off'");
        $bannedSubscriptions = self::aggregate('qwees_subscriptions', 'COUNT', '*', "status = 'banned'");

        // Получаем уникальных пользователей с подписками
        $subscriptionData = self::getData('qwees_subscriptions');
        $usersWithSubscriptions = 0;

        if (!empty($subscriptionData)) {
            // Определяем поле с ID пользователя
            $userIdField = null;
            if (isset($subscriptionData[0]['user_id'])) {
                $userIdField = 'user_id';
            } elseif (isset($subscriptionData[0]['uniID'])) {
                $userIdField = 'uniID';
            } elseif (isset($subscriptionData[0]['user'])) {
                $userIdField = 'user';
            }

            if ($userIdField) {
                $usersWithSubscriptions = count(array_unique(array_column($subscriptionData, $userIdField)));
            } else {
                // Если поле не найдено, считаем что все подписки принадлежат разным пользователям
                $usersWithSubscriptions = count($subscriptionData);
            }
        }

        $usersWithoutSubscriptions = max(0, $totalUsers - $usersWithSubscriptions);

        // Статистика цен
        $avgPrice = self::aggregate('qwees_price', 'AVG', 'price');
        $maxPrice = self::aggregate('qwees_price', 'MAX', 'price');

        return [
            'totalUsers' => $totalUsers,
            'totalSubscriptions' => $totalSubscriptions,
            'activeSubscriptions' => $activeSubscriptions,
            'inactiveSubscriptions' => $inactiveSubscriptions,
            'bannedSubscriptions' => $bannedSubscriptions,
            'usersWithSubscriptions' => $usersWithSubscriptions,
            'usersWithoutSubscriptions' => $usersWithoutSubscriptions,
            'avgPrice' => $avgPrice,
            'maxPrice' => $maxPrice,
            // Для обратной совместимости
            'activeUsers' => $activeSubscriptions,
            'inactiveUsers' => $inactiveSubscriptions,
            'bannedUsers' => $bannedSubscriptions
        ];
    }

    /**
     * Получить финансовую статистику
     * @return array массив с финансовыми данными
     */
    public static function getFinancialStats(): array
    {
        // Прибыль за разные периоды
        $monthlyRevenue = self::getRevenueByPeriod('month');
        $weeklyRevenue = self::getRevenueByPeriod('week');
        $dailyRevenue = self::getRevenueByPeriod('day');

        // Общая прибыль
        $totalRevenue = self::aggregate('qwees_subscriptions', 'SUM', 'amount');

        // Прибыль по месяцам для графика
        $monthlyRevenueChart = self::getMonthlyRevenueChart();

        // Статистика пользователей по месяцам для графика
        $monthlyUsersChart = self::getMonthlyUsersChart();

        // Прибыль по тарифам
        $revenueByPlan = self::getRevenueByPlan();

        // Средний чек
        $avgCheck = $totalRevenue > 0 ? $totalRevenue / max(1, self::aggregate('qwees_subscriptions', 'COUNT')) : 0;

        return [
            'monthlyRevenue' => $monthlyRevenue,
            'weeklyRevenue' => $weeklyRevenue,
            'dailyRevenue' => $dailyRevenue,
            'totalRevenue' => $totalRevenue,
            'avgCheck' => $avgCheck,
            'monthlyRevenueChart' => $monthlyRevenueChart,
            'monthlyUsersChart' => $monthlyUsersChart,
            'revenueByPlan' => $revenueByPlan
        ];
    }

    /**
     * Получить прибыль за период
     * @param string $period 'day', 'week', 'month', 'year'
     * @return float
     */
    private static function getRevenueByPeriod(string $period): float
    {
        if (Database::isMysql()) {
            $dateCondition = match ($period) {
                'day' => "DATE(created_at) = CURDATE()",
                'week' => "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
                'month' => "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
                'year' => "created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)",
                default => "1=1"
            };
        } else {
            $dateCondition = match ($period) {
                'day' => "DATE(created_at) = DATE('now')",
                'week' => "created_at >= DATE('now', '-7 days')",
                'month' => "created_at >= DATE('now', '-30 days')",
                'year' => "created_at >= DATE('now', '-365 days')",
                default => "1=1"
            };
        }

        return (float) self::aggregate('qwees_subscriptions', 'SUM', 'amount', $dateCondition);
    }

    /**
     * Получить прибыль по месяцам для графика
     * @return array
     */
    private static function getMonthlyRevenueChart(): array
    {
        if (Database::isMysql()) {
            $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        SUM(amount) as revenue,
                        COUNT(*) as count
                    FROM qwees_subscriptions 
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month";
        } else {
            $sql = "SELECT 
                        strftime('%Y-%m', created_at) as month,
                        SUM(amount) as revenue,
                        COUNT(*) as count
                    FROM qwees_subscriptions 
                    WHERE created_at >= DATE('now', '-12 months')
                    GROUP BY strftime('%Y-%m', created_at)
                    ORDER BY month";
        }

        $result = Database::send($sql);
        return is_array($result) ? $result : [];
    }

    /**
     * Получить статистику регистраций пользователей по месяцам
     * @return array
     */
    private static function getMonthlyUsersChart(): array
    {
        if (Database::isMysql()) {
            $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as users_count
                    FROM qwees_users 
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month";
        } else {
            $sql = "SELECT 
                        strftime('%Y-%m', created_at) as month,
                        COUNT(*) as users_count
                    FROM qwees_users 
                    WHERE created_at >= DATE('now', '-12 months')
                    GROUP BY strftime('%Y-%m', created_at)
                    ORDER BY month";
        }
        
        $result = Database::send($sql);
        return is_array($result) ? $result : [];
    }

    /**
     * Получить прибыль по тарифам
     * @return array
     */
    private static function getRevenueByPlan(): array
    {
        $sql = "SELECT 
                    subscription,
                    SUM(amount) as revenue,
                    COUNT(*) as count
                FROM qwees_subscriptions 
                GROUP BY subscription
                ORDER BY revenue DESC";

        $result = Database::send($sql);
        return is_array($result) ? $result : [];
    }

    /**
     * Добавить нового пользователя (использует универсальный метод регистрации)
     *
     * @param array $userData данные пользователя
     *     - обязательные: **[email, first_name]**
     *     - дополнительно: **[uniID, myrefer, last_name, role]**
     *     - доп.подписка: **[subscription, duration_days]**
     *     - Дополнительно: проверка на наличие у данного пользователя реферала (установка)
     *     - session: pending_refer_code
     *
     * @return bool успешность операции
     */
    public function addUser(array $userData): void
    {
        // Используем универсальный метод регистрации из Auth
        $result = \Setting\Route\Function\Controllers\Auth\Auth::registerUser($userData);//return [succes, uniid, message]

        if (!$result['success']) {//false
            $logFile = $_ENV['LOG_FILE_NAME'] ?? 'app.log';
            file_put_contents(
                $logFile,
                sprintf(
                    "[%s] [ADMIN] Ошибка добавления пользователя: %s\n",
                    date('Y-m-d H:i:s'),
                    $result['message']
                ),
                FILE_APPEND
            );
            Network::onRedirect('/admin?message_status=error&message_msg=Ошибка добавления пользователя!');
            return;
        }

        // Записываем успешное добавление в лог
        $logFile = $_ENV['LOG_FILE_NAME'] ?? 'app.log';
        file_put_contents(
            $logFile,
            sprintf(
                "[%s] [ADMIN] Добавлен новый пользователь: %s (%s), uniID: %s\n",
                date('Y-m-d H:i:s'),
                $userData['first_name'] . ' ' . ($userData['last_name'] ?? ''),
                $userData['email'],
                $result['uniID']
            ),
            FILE_APPEND
        );
        Network::onRedirect('/admin?message_status=success&message_msg=Успешно создание пользователя: ' . $userData['first_name']);
        return;
    }
}
