<?php
// Проверяем авторизацию администратора
\Setting\Route\Function\Controllers\Admin\AdminAuth::auth();

use App\Models\Network\Network;
use Setting\Route\Function\Controllers\Admin\AdminDatabase;
use Setting\Route\Function\Functions;
$admin = new AdminDatabase();
$site = Functions::site();

// Получаем параметры
$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? '';

if (empty($table) || empty($id)) {
    Network::onRedirect('/admin');
}

// Получаем данные записи
$row = AdminDatabase::getRow($table, $id);
if (!$row) {
    Network::onRedirect("/admin/database?table=" . urlencode($table));
}

$columns = AdminDatabase::getColumns($table);

// Определяем readonly поля (по умолчанию id всегда readonly)
$readonly = $admin->setReadonly(['id']);

// Дополнительные readonly поля в зависимости от таблицы
if ($table === 'qwees_users') {
    $readonly = $admin->setReadonly(['id', 'created_at', 'uniID']);
} elseif ($table === 'qwees_subscriptions') {
    $readonly = $admin->setReadonly(['id', 'created_at', 'updated_at']);
}
?>
<!DOCTYPE html>
<html lang="ru">

<?php include_once 'includes/head.php'; ?>

<body class="bg-no-repeat flex item-center w-full overflow-x-hidden bg-gray-100">
    <div class="min-h-screen flex w-full mx-auto">

        <!-- sidebar -->
        <aside class="flex flex-col shadow-xl bg-white rounded-xl">
            <div class="flex flex-col p-4 gap-4">
                <div class="flex flex-1 justify-between">
                    <div class="flex items-center gap-3">
                        <img decoding="async" loading="lazy" src="/public/assets/images/icons/services/avatar/2.png"
                            class="rounded-full aspect-square w-10 h-10">
                        <div class="flex flex-col gap-0.5">
                            <div class="flex gap-2">
                                <span class="font-medium">
                                    <?= htmlspecialchars($site['контакты']['Директор']) ?>
                                </span>
                                <span
                                    class="flex items-center py-0 px-1 bg-[#ece0f7] rounded-md font-medium text-[#593597] text-sm">admin</span>
                            </div>
                            <span class="text-slate-400	text-sm"><?= htmlspecialchars($site['описание']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Кнопка Назад -->
                <a href="/admin/database?table=<?= htmlspecialchars($table) ?>"
                    class="inline-flex shadow-sm bg-gray-100 w-full justify-center rounded-xl text-sm font-semibold py-2 px-3 text-slate-900 ring-1 ring-slate-900/10 hover:bg-gray-200 items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    Назад к таблице
                </a>
            </div>

            <!-- Инфо -->
            <div class="flex flex-col p-4 gap-2 border-t border-gray-100">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fa-solid fa-pen-to-square text-blue-500"></i>
                    <span class="font-medium">Редактирование</span>
                </div>
                <div class="text-sm text-gray-500">
                    <?= htmlspecialchars($table) ?> #<?= htmlspecialchars($id) ?>
                </div>
            </div>
        </aside>

        <main class="container mx-auto px-20 flex-grow bg-gray-100 overflow-auto">
            <!-- Заголовок -->
            <div class="container mx-auto py-6">
                <h1 class="text-2xl font-semibold text-gray-800">
                    Редактирование записи
                    <span class="text-sm font-normal text-gray-500 ml-2"><?= htmlspecialchars($table) ?>
                        #<?= htmlspecialchars($id) ?></span>
                </h1>
            </div>

            <!-- Форма редактирования -->
            <section class="container mx-auto">
                <form action="/admin/save" method="POST" class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
                    <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                    <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                    <table class="w-full text-sm mb-6">
                        <?php foreach ($columns as $col):
                            $isReadonly = isset($readonly[$col]);
                            $value = $row[$col] ?? '';
                            ?>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 pr-4 font-medium text-gray-700 w-1/3">
                                    <?= htmlspecialchars($col) ?>
                                    <?php if ($isReadonly): ?>
                                        <span class="text-xs text-gray-400 ml-1">(только чтение)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <?php if ($col === 'status' && isset(AdminDatabase::USER_STATUSES[$value])): ?>
                                        <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded">
                                            <?php foreach (AdminDatabase::USER_STATUSES as $statusVal => $statusLabel): ?>
                                                <option value="<?= $statusVal ?>" <?= $value === $statusVal ? 'selected' : '' ?>>
                                                    <?= $statusLabel ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php elseif (strpos($col, 'date') !== false || strpos($col, '_at') !== false): ?>
                                        <input type="text" name="<?= htmlspecialchars($col) ?>"
                                            value="<?= AdminDatabase::formatDate($value) ?>" <?= $isReadonly ? 'readonly' : '' ?>
                                            class="w-full px-3 py-2 border border-gray-200 rounded <?= $isReadonly ? 'bg-gray-100' : '' ?>">
                                    <?php else: ?>
                                        <input type="text" name="<?= htmlspecialchars($col) ?>"
                                            value="<?= htmlspecialchars((string) $value) ?>" <?= $isReadonly ? 'readonly' : '' ?>
                                            class="w-full px-3 py-2 border border-gray-200 rounded <?= $isReadonly ? 'bg-gray-100' : '' ?>">
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 font-medium">
                            Сохранить
                        </button>
                        <a href="/admin/database?table=<?= htmlspecialchars($table) ?>"
                            class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 font-medium">
                            Отмена
                        </a>
                    </div>
                </form>
            </section>
        </main>

    </div>

    <script src="/public/assets/scripts/main/main.js" defer></script>
</body>

</html>