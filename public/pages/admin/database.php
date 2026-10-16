<?php
// Проверяем авторизацию администратора
\Setting\Route\Function\Controllers\Admin\AdminAuth::auth();

// право на раздел
$groupsGate = new \Setting\Route\Function\Controllers\Admin\Group\Groups();//вызываем класс
$gateName = (new \Setting\Route\Function\Controllers\Admin\Admin())->getUsername((int) (\App\Config\Session::init('admin')['auth'][1] ?? 0));//кто сидит
if (!$groupsGate->isPermission($gateName, 'database')) {
    http_response_code(403);
    include_once 'includes/head.php';
    echo "<body class='bg-[#0f1115]' style='background:#0f1115;color:#e5e7eb;'><div style='max-width:480px;margin:80px auto;text-align:center;'>"
        . "<i class='fa-solid fa-lock' style='font-size:56px;color:#ef4444;'></i>"
        . "<div style='font-size:20px;font-weight:700;margin-top:12px;color:#f3f4f6;'>Недоступно</div>"
        . "<div style='color:#9ca3af;'>Нет прав на раздел</div>"
        . "<a href='/admin' style='display:inline-block;margin-top:16px;color:#6ea8fe;'>Назад в админку</a></div></body></html>";
    exit;
}

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

AdminDatabase::ensureSubscriptionUnique(); // убрать дубли подписок + уникальный индекс
$columns = AdminDatabase::getColumns($table); // Получаем имена колонок
$count = AdminDatabase::getCount($table); //колв записей

//теперь просто выбираем с какими условими будем выводить в случае, если используются
if (!empty($searchQuery)) {
    // Поиск по всем колонкам
    $data = AdminDatabase::search($table, array_diff($columns, ['id']), $searchQuery, 50);
} elseif (!empty($filterColumn)) {
    // Фильтрация по колонке
    $data = AdminDatabase::filter($table, $filterColumn, $filterValue, 50, $filterCondition);
} elseif ($table === 'qwees_subscriptions') {
    // Подписки с почтой владельца (JOIN) + колонка email сразу после status
    $data = AdminDatabase::getSubscriptionsWithEmail(50, $_GET['sort'] ?? 'id', $_GET['dir'] ?? 'ASC');
    $columns = array_values(array_filter($columns, fn($c) => $c !== 'email'));
    $pos = array_search('status', $columns, true);
    array_splice($columns, $pos === false ? count($columns) : $pos + 1, 0, ['email']);
    } elseif ($table === 'qwees_users') {
        // Пользователи со статусом подписки (JOIN) + колонка status сразу после email
        $data = AdminDatabase::getUsersWithSubscription(100, $_GET['sort'] ?? 'created_at', $_GET['dir'] ?? 'desc');
    $columns = array_values(array_filter($columns, fn($c) => $c !== 'status'));
    $pos = array_search('id', $columns, true);
    array_splice($columns, $pos === false ? 0 : $pos + 1, 0, ['status']);
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

<body class="bg-no-repeat flex item-center w-full overflow-x-hidden bg-[#0f1115] text-gray-200">
    <div class="min-h-screen flex w-full mx-auto">

        <!-- оверлей (мобильная шторка) -->
        <div id="admin-overlay" class="fixed inset-0 bg-black/40 z-40 hidden md:hidden"></div>

        <!-- кнопка открытия меню (мобильная) -->
        <button id="admin-burger"
            class="md:hidden fixed top-2 left-2 z-[60] bg-[#16181d] rounded-lg shadow-md p-2.5 text-gray-300">
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

        <!-- navbar миниатюрное (в стиле основного меню) -->
        <aside id="admin-sidebar"
            class="fixed inset-y-0 left-0 z-50 -translate-x-full transition-transform duration-300 md:relative md:translate-x-0 flex flex-col shadow-xl bg-[#16181d] rounded-xl shrink-0 min-w-[260px] max-w-[260px]">
            <!-- header -->
            <div class="flex flex-col p-4 gap-4">
                <div class="flex flex-1 justify-between items-center">
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-400 via-transparent to-transparent flex items-center justify-center text-green-500 font-bold text-lg shrink-0 shadow-lg shadow-green-500/30">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <div class="flex flex-col gap-0.5 min-w-0">
                            <span class="font-medium truncate text-gray-100">База данных</span>
                            <span class="text-slate-400 text-xs truncate"><?= htmlspecialchars($table) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Кнопка Назад -->
                <a href="/admin"
                    class="inline-flex shadow-sm bg-white/5 w-full justify-center rounded-xl text-sm font-semibold py-2.5 px-4 text-gray-300 ring-1 ring-white/10 hover:ring-white/20 hover:bg-white/5 items-center gap-2 transition-all">
                    <i class="fa-solid fa-arrow-left text-gray-500"></i>
                    <span>Назад в админку</span>
                </a>
            </div>

            <!-- Инфо о таблице -->
            <div class="flex flex-col px-4 pb-4 gap-3 border-b border-white/5">
                <div class="flex items-center justify-between py-2 px-3 bg-white/5 rounded-lg">
                    <div class="flex items-center gap-2 text-gray-400">
                        <i class="fa-solid fa-table text-green-500"></i>
                        <span class="text-sm font-medium"><?= htmlspecialchars($table) ?></span>
                    </div>
                    <span
                        class="text-xs font-semibold bg-green-500/10 text-green-400 px-2 py-0.5 rounded-full"><?= $count ?></span>
                </div>
            </div>

            <!-- Миниatюрное меню (без запросов к БД) -->
            <nav class="flex-1 overflow-y-auto py-2 px-2">
                <ul class="menu menu-xs rounded-box w-full text-xs flex flex-col gap-0.5">
                    <li class="list-none">
                        <a href="/admin"
                            class="flex items-center gap-2 py-1.5 px-2.5 rounded-md cursor-pointer text-gray-400 hover:text-gray-200 hover:bg-white/5 transition-colors duration-150">
                            <i class="fa-regular fa-house text-gray-500"></i>
                            <span>Главная</span>
                        </a>
                    </li>
                    <?php
                    // Миниатюрный список таблиц — без getTables(), используем кэшированный список
                    $menuTables = AdminDatabase::getTables();
                    ?>
                    <?php foreach ($menuTables as $tn): ?>
                    <li class="list-none">
                        <a href="/admin/database?table=<?= urlencode($tn) ?>"
                            class="flex items-center gap-2 py-1.5 px-2.5 rounded-md cursor-pointer transition-colors duration-150 <?= $tn === $table ? 'text-gray-200 bg-white/5' : 'text-gray-500 hover:text-gray-200 hover:bg-white/5' ?>">
                            <i class="fa-solid fa-database"></i>
                            <span class="truncate"><?= htmlspecialchars($tn) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- Поиск -->
            <div class="flex px-4 flex-col py-4 gap-3 border-t border-white/5">
                <form action="/admin/database" method="get" class="flex flex-col gap-2">
                    <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                    <div class="relative group">
                        <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>"
                            placeholder="Поиск..."
                            class="w-full px-3 py-2 pl-9 pr-9 text-sm bg-white/5 border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 focus:bg-white/10 transition-all">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-green-500 transition-colors"></i>
                    </div>
                </form>
            </div>
        </aside>

        <main class="mx-auto container px-4 md:px-20 pt-14 md:pt-0 flex-grow min-w-0 bg-white/10 overflow-auto">
            <!-- Заголовок -->
            <div class="container mx-auto py-6">
                <h1 class="text-2xl font-semibold text-gray-100">
                    <?= htmlspecialchars($table) ?>
                    <span class="text-sm font-normal text-gray-500 ml-2">(<?= count($data) ?> из <?= $count ?>)</span>
                </h1>
            </div>
            <!-- Таблица данных -->
            <section class="container mx-auto">
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max text-sm">
                            <thead class="bg-white/5 border-b border-white/10">
                                <tr>
                                    <?php foreach ($columns as $col): ?>
                                        <th
                                            class="px-4 py-3 text-left font-medium text-gray-300 whitespace-nowrap cursor-pointer hover:bg-white/10">
                                            <a href="<?= $sortUrl($col) ?>"
                                                class="flex items-center gap-1 text-gray-300 hover:text-green-400">
                                                <?= htmlspecialchars($table === 'qwees_users' && $col === 'status' ? 'статус подписки' : $col) ?>
                                                <span class="text-xs text-gray-400"><?= $sortIcon($col) ?></span>
                                            </a>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php foreach ($data as $row): ?>
                                    <tr class="hover:bg-white/5">
                                        <?php foreach ($columns as $col): ?>
                                            <td class="px-4 py-3 text-gray-400">
                                                <?php if ($col === 'id'): ?>
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-regular fa-hand-pointer text-green-400"></i>
                                                        <a href=" /admin/edit?table=<?= urlencode($table) ?>&id=<?= $row[$col] ?>"
                                                            class="text-green-400 hover:underline font-medium">
                                                            <?= htmlspecialchars((string) $row[$col]) ?>
                                                        </a>
                                                        <?php if ($table === 'qwees_users'): ?>
                                                        <form action="/admin/delete" method="POST" class="inline"
                                                            onsubmit="return confirm('Удалить пользователя вместе с подписками?');">
                                                            <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $row[$col]) ?>">
                                                            <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                                            <button type="submit" title="Удалить пользователя"
                                                                class="text-red-400 hover:text-red-400 transition-colors">
                                                                <i class="fa-solid fa-trash text-xs"></i>
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php elseif ($col === 'status' && isset(AdminDatabase::USER_STATUSES[$row[$col]])): ?>
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= $row[$col] === 'on' ? 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30' : ($row[$col] === 'banned' ? 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30' : 'bg-white/10 text-gray-300') ?>">
                                                        <?= AdminDatabase::USER_STATUSES[$row[$col]] ?>
                                                    </span>
                                                <?php elseif ($col === 'uniID'): ?>
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-regular fa-hand-pointer text-green-400"></i>
                                                        <a href="/admin/database?table=qwees_users&search=<?= $row[$col] ?>"
                                                            class="text-green-400 hover:underline font-medium">
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

    <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/main/main.js<?= '?v=' . $site['versionApp'] ?>" defer></script>
</body>

</html>