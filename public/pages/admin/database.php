<?php
// Проверяем авторизацию администратора
\Setting\Route\Function\Controllers\Admin\AdminAuth::auth();

use Setting\Route\Function\Controllers\Admin\AdminDatabase;
use Setting\Route\Function\Functions;

// получение информации
$site = Functions::site();

// Получаем параметры
$table = $_GET['table'] ?? '';
$filterColumn = $_GET['filter_column'] ?? '';
$filterValue = $_GET['filter_value'] ?? '';
$filterCondition = $_GET['filter_condition'] ?? '=';
$searchQuery = $_GET['search'] ?? '';

if (empty($table)) {
    \App\Models\Network\Network::onRedirect('/admin');
}

$columns = AdminDatabase::getColumns($table); // Получаем имена колонок
$count = AdminDatabase::getCount($table); //колв записей

//теперь просто выбираем с какими условими будем выводить в случае, если используются
if (!empty($searchQuery)) {
    // Поиск по всем колонкам
    $data = AdminDatabase::search($table, array_diff($columns, ['id']), $searchQuery, 50);
} elseif (!empty($filterColumn)) {
    // Фильтрация по колонке
    $data = AdminDatabase::filter($table, $filterColumn, $filterValue, 50, $filterCondition);
} else {
    // Все данные с сортировкой
    $data = AdminDatabase::getData($table, 50, $_GET['sort'] ?? 'id', $_GET['dir'] ?? 'ASC');
}

// Функция для построения URL с параметрами
function buildUrl(string $table, $params = [])
{
    $query = array_merge(['table' => $table], $params);
    return '/admin/database?' . http_build_query($query);
}

// Функция для сортировки (аналог order_by)
$sortUrl = function ($col) use ($table) {
    $currentSort = $_GET['sort'] ?? 'id';
    $currentDir = $_GET['dir'] ?? 'asc';
    $newDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    return "?table={$table}&sort={$col}&dir={$newDir}";
};

// Функция для иконки сортировки
$sortIcon = function ($col) {
    $currentSort = $_GET['sort'] ?? 'id';
    $currentDir = $_GET['dir'] ?? 'asc';
    if ($currentSort !== $col)
        return '';
    return $currentDir === 'asc' ? ' ↑' : ' ↓';
};
?>
<!DOCTYPE html>
<html lang="ru">

<?php include_once 'includes/head.php'; ?>

<body class="bg-no-repeat flex item-center w-full overflow-x-hidden bg-gray-100">
    <div class="min-h-screen flex w-full mx-auto">

        <!-- оверлей (мобильная шторка) -->
        <div id="admin-overlay" class="fixed inset-0 bg-black/40 z-40 hidden md:hidden"></div>

        <!-- кнопка открытия меню (мобильная) -->
        <button id="admin-burger"
            class="md:hidden fixed top-2 left-2 z-[60] bg-white rounded-lg shadow-md p-2.5 text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <script defer>
            $(document).ready(function () {
                var $sidebar = $('#admin-sidebar');
                var $overlay = $('#admin-overlay');

                function closeSidebar() {
                    $sidebar.removeClass('translate-x-0').addClass('-translate-x-full');
                    $overlay.addClass('hidden');
                }

                $('#admin-burger').on('click', function () {
                    var open = $sidebar.hasClass('translate-x-0');
                    $sidebar.toggleClass('translate-x-0', !open).toggleClass('-translate-x-full', open);
                    $overlay.toggleClass('hidden', open);
                });

                $overlay.on('click', closeSidebar);
                $sidebar.on('click', 'a, [data-toggle-section]', closeSidebar);
            });
        </script>

        <!-- navbar с фильтрами -->
        <aside id="admin-sidebar"
            class="fixed inset-y-0 left-0 z-50 -translate-x-full transition-transform duration-300 md:relative md:translate-x-0 flex flex-col shadow-xl bg-white rounded-xl shrink-0 min-w-[280px] max-w-[280px]">
            <!-- header -->
            <div class="flex flex-col p-4 gap-4">
                <div class="flex flex-1 justify-between items-center">
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-400 via-transparent to-transparent flex items-center justify-center text-green-500 font-bold text-lg shrink-0 shadow-lg shadow-green-500/30">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                        <div class="flex flex-col gap-0.5 min-w-0">
                            <span class="font-medium truncate text-gray-800">База данных</span>
                            <span class="text-slate-400 text-xs truncate"><?= htmlspecialchars($table) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Кнопка Назад -->
                <a href="/admin"
                    class="inline-flex shadow-sm bg-white w-full justify-center rounded-xl text-sm font-semibold py-2.5 px-4 text-slate-700 ring-1 ring-slate-900/10 hover:ring-slate-900/20 hover:bg-gray-50 items-center gap-2 transition-all">
                    <i class="fa-solid fa-arrow-left text-gray-500"></i>
                    <span>Назад в админку</span>
                </a>
            </div>

            <!-- Инфо о таблице -->
            <div class="flex flex-col px-4 pb-4 gap-3 border-b border-gray-100">
                <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="fa-solid fa-table text-green-500"></i>
                        <span class="text-sm font-medium"><?= htmlspecialchars($table) ?></span>
                    </div>
                    <span
                        class="text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full"><?= $count ?></span>
                </div>

                <!-- PDF Экспорт -->
                <?php if ($table): ?>
                    <div class="flex flex-col gap-2 pt-3 border-t border-gray-100">
                        <h5 class="text-slate-400 text-xs font-medium uppercase tracking-wider">Экспорт таблицы:
                            <?= htmlspecialchars($table) ?>
                        </h5>

                        <!-- Кнопка экспорта текущей таблицы -->
                        <a href="/export/pdf?type=table&table=<?= urlencode($table) ?>"
                            class="flex items-center gap-2 px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm hover:border-green-400 hover:text-green-600 hover:shadow-sm transition-all">
                            <div
                                class="w-6 h-6 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white text-xs">
                                <i class="fa-solid fa-file-pdf"></i>
                            </div>
                            <span class="font-medium">Экспорт <?= htmlspecialchars($table) ?></span>
                        </a>

                        <?php
                        // Специфичные отчеты для разных таблиц
                        if ($table === 'qwees_subscriptions'): ?>
                            <a href="/export/pdf?type=subscriptions"
                                class="flex items-center gap-2 px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm hover:border-green-400 hover:text-green-600 hover:shadow-sm transition-all">
                                <div
                                    class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs">
                                    <i class="fa-solid fa-chart-pie"></i>
                                </div>
                                <span class="font-medium">Статистика подписок</span>
                            </a>
                        <?php elseif ($table === 'qwees_users'): ?>
                            <a href="/export/pdf?type=users"
                                class="flex items-center gap-2 px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm hover:border-green-400 hover:text-green-600 hover:shadow-sm transition-all">
                                <div
                                    class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <span class="font-medium">Статистика пользователей</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Если таблица не выбрана - показать общие отчеты -->
                    <div class="flex flex-col gap-2 pt-3 border-t border-gray-100">
                        <h5 class="text-slate-400 text-xs font-medium uppercase tracking-wider">Общие отчеты</h5>

                        <a href="/export/pdf?type=subscriptions"
                            class="flex items-center gap-2 px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm hover:border-green-400 hover:text-green-600 hover:shadow-sm transition-all">
                            <div
                                class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                            <span class="font-medium">Статистика подписок</span>
                        </a>

                        <a href="/export/pdf?type=users"
                            class="flex items-center gap-2 px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm hover:border-green-400 hover:text-green-600 hover:shadow-sm transition-all">
                            <div
                                class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <span class="font-medium">Статистика пользователей</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Поиск -->
            <div class="flex px-4 flex-col py-4 gap-3">
                <h5 class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Поиск</h5>
                <form action="/admin/database" method="get" class="flex flex-col gap-2">
                    <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                    <?php if ($filterColumn): ?>
                        <input type="hidden" name="filter_column" value="<?= htmlspecialchars($filterColumn) ?>">
                        <input type="hidden" name="filter_value" value="<?= htmlspecialchars($filterValue) ?>">
                    <?php endif; ?>
                    <div class="relative group">
                        <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>"
                            placeholder="Поиск по таблице..."
                            class="w-full px-3 py-2.5 pl-9 pr-9 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 focus:bg-white transition-all">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                        <?php if ($searchQuery): ?>
                            <a href="<?= buildUrl($table, $filterColumn ? ['filter_column' => $filterColumn, 'filter_value' => $filterValue] : []) ?>"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 hover:bg-red-100 hover:text-red-500 transition-colors">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <button type="submit"
                        class="w-full bg-green-500 hover:bg-green-600 text-white text-sm font-medium py-2 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-search"></i>
                        Найти
                    </button>
                </form>
            </div>

            <!-- Фильтры -->
            <div class="flex px-4 flex-col flex-1 overflow-y-auto">
                <h5 class="text-slate-500 text-xs font-semibold uppercase tracking-wider mb-3">Фильтры</h5>
                <ul class="flex flex-col gap-1">

                    <?php
                    // Определяем доступные фильтры в зависимости от таблицы
                    $availableFilters = [];
                    if ($table === 'qwees_subscriptions') {
                        $availableFilters = [
                            'status' => [
                                'name' => 'Статус подписки',
                                'icon' => 'fa-circle-check',
                                'options' => [
                                    ['value' => 'on', 'label' => 'Активные', 'condition' => '=', 'color' => 'green'],
                                    ['value' => 'off', 'label' => 'Неактивные', 'condition' => '=', 'color' => 'gray'],
                                    ['value' => 'banned', 'label' => 'Заблокированные', 'condition' => '=', 'color' => 'red'],
                                    ['value' => 'pending_vpn', 'label' => 'Ожидают VPN', 'condition' => '=', 'color' => 'yellow'],
                                ]
                            ],
                            'subscription' => [
                                'name' => 'Наличие подписки',
                                'icon' => 'fa-credit-card',
                                'options' => [
                                    ['value' => '', 'label' => 'Без подписки', 'condition' => '=', 'color' => 'gray'],
                                    ['value' => '', 'label' => 'С подпиской', 'condition' => '!=', 'color' => 'green'],
                                ]
                            ],
                        ];
                    } elseif ($table === 'qwees_users') {
                        $availableFilters = [
                            'role' => [
                                'name' => 'Роль пользователя',
                                'icon' => 'fa-user-shield',
                                'options' => [
                                    ['value' => 'user', 'label' => 'Пользователь', 'condition' => '=', 'color' => 'green'],
                                    ['value' => 'manager', 'label' => 'Менеджер', 'condition' => '=', 'color' => 'yellow'],
                                    ['value' => 'admin', 'label' => 'Администратор', 'condition' => '=', 'color' => 'red'],
                                ]
                            ],
                        ];
                    } elseif ($table === 'qwees_payments') {
                        $availableFilters = [
                            'status' => [
                                'name' => 'Статус платежа',
                                'icon' => 'fa-money-bill',
                                'options' => [
                                    ['value' => 'completed', 'label' => 'Завершен', 'condition' => '=', 'color' => 'green'],
                                    ['value' => 'pending', 'label' => 'В обработке', 'condition' => '=', 'color' => 'yellow'],
                                    ['value' => 'failed', 'label' => 'Ошибка', 'condition' => '=', 'color' => 'red'],
                                ]
                            ],
                        ];
                    }
                    ?>

                    <?php foreach ($availableFilters as $col => $filter): ?>
                        <li class="list-none relative">
                            <details <?= ($filterColumn === $col) ? 'open' : '' ?> class="group">
                                <summary
                                    class="flex items-center gap-2 py-2 px-3 rounded-xl cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150 list-none select-none">
                                    <i
                                        class="fa-solid fa-chevron-right text-gray-400 text-xs transition-transform group-open:rotate-90"></i>
                                    <i class="fa-solid <?= $filter['icon'] ?> text-gray-500"></i>
                                    <span class="text-sm font-medium"><?= $filter['name'] ?></span>
                                </summary>
                                <ul class="pl-4 mt-1 flex flex-col gap-0.5">
                                    <li class="list-none relative">
                                        <a href="<?= buildUrl($table, $searchQuery ? ['search' => $searchQuery] : []) ?>"
                                            class="flex items-center gap-2 py-1.5 px-3 rounded-lg text-sm cursor-pointer <?= empty($filterColumn) || $filterColumn !== $col ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-50' ?> transition-colors duration-150">
                                            <span
                                                class="w-2 h-2 rounded-full <?= empty($filterColumn) || $filterColumn !== $col ? 'bg-green-500' : 'bg-gray-300' ?>"></span>
                                            <span>Все записи</span>
                                        </a>
                                    </li>
                                    <?php foreach ($filter['options'] as $opt):
                                        $isActive = ($filterColumn === $col && $filterValue === $opt['value'] && $filterCondition === $opt['condition']);
                                        $optParams = ['filter_column' => $col, 'filter_value' => $opt['value'], 'filter_condition' => $opt['condition']];
                                        if ($searchQuery)
                                            $optParams['search'] = $searchQuery;
                                        $colorClass = $opt['color'] ?? 'gray';
                                        $colorMap = [
                                            'green' => 'bg-green-500',
                                            'red' => 'bg-red-500',
                                            'yellow' => 'bg-yellow-500',
                                            'gray' => 'bg-gray-400'
                                        ];
                                        $activeColor = $colorMap[$colorClass] ?? 'bg-gray-400';
                                        ?>
                                        <li class="list-none relative">
                                            <a href="<?= $isActive ? buildUrl($table, $searchQuery ? ['search' => $searchQuery] : []) : buildUrl($table, $optParams) ?>"
                                                class="flex items-center gap-2 py-1.5 px-3 rounded-lg text-sm cursor-pointer <?= $isActive ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-50' ?> transition-colors duration-150">
                                                <span
                                                    class="w-2 h-2 rounded-full <?= $isActive ? $activeColor : 'bg-gray-300' ?>"></span>
                                                <span><?= $opt['label'] ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </details>
                        </li>
                    <?php endforeach; ?>

                    <?php if (empty($availableFilters)): ?>
                        <li class="list-none relative text-gray-400 text-sm py-3 px-2 bg-gray-50 rounded-xl text-center">
                            <i class="fa-solid fa-filter-circle-xmark mb-2 text-gray-300 text-xl"></i>
                            <p>Нет фильтров для этой таблицы</p>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>

        <main class="mx-auto container px-4 md:px-20 pt-14 md:pt-0 flex-grow bg-gray-100 overflow-auto">
            <!-- Заголовок -->
            <div class="container mx-auto py-6">
                <h1 class="text-2xl font-semibold text-gray-800">
                    <?= htmlspecialchars($table) ?>
                    <span class="text-sm font-normal text-gray-500 ml-2">(<?= count($data) ?> из <?= $count ?>)</span>
                </h1>
            </div>
            <!-- Таблица данных -->
            <section class="container mx-auto">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <?php foreach ($columns as $col): ?>
                                        <th
                                            class="px-4 py-3 text-left font-medium text-gray-700 whitespace-nowrap cursor-pointer hover:bg-gray-100">
                                            <a href="<?= $sortUrl($col) ?>"
                                                class="flex items-center gap-1 text-gray-700 hover:text-green-600">
                                                <?= htmlspecialchars($col) ?>
                                                <span class="text-xs text-gray-400"><?= $sortIcon($col) ?></span>
                                            </a>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($data as $row): ?>
                                    <tr class="hover:bg-gray-50">
                                        <?php foreach ($columns as $col): ?>
                                            <td class="px-4 py-3 text-gray-600">
                                                <?php if ($col === 'id'): ?>
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-regular fa-hand-pointer text-green-600"></i>
                                                        <a href=" /admin/edit?table=<?= urlencode($table) ?>&id=<?= $row[$col] ?>"
                                                            class="text-green-600 hover:underline font-medium">
                                                            <?= htmlspecialchars((string) $row[$col]) ?>
                                                        </a>
                                                    </div>
                                                <?php elseif ($col === 'status' && isset(AdminDatabase::USER_STATUSES[$row[$col]])): ?>
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= $row[$col] === 'on' ? 'bg-green-100 text-green-800' : ($row[$col] === 'banned' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') ?>">
                                                        <?= AdminDatabase::USER_STATUSES[$row[$col]] ?>
                                                    </span>
                                                <?php elseif ($col === 'uniID'): ?>
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-regular fa-hand-pointer text-green-600"></i>
                                                        <a href="/admin/database?table=qwees_users&search=<?= $row[$col] ?>"
                                                            class="text-green-600 hover:underline font-medium">
                                                            <?= htmlspecialchars((string) $row[$col]) ?>
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <?= isset($row[$col]) ? htmlspecialchars((string) $row[$col]) : '-' ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($data)): ?>
                        <div class="p-8 text-center text-gray-500">
                            Нет данных для отображения
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>

    </div>

    <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/main/main.js" defer></script>
</body>

</html>