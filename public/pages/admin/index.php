<?php

use Setting\Route\Function\Controllers\Admin\AdminAuth;
// Проверяем авторизацию администратора
AdminAuth::auth();

use Setting\Route\Function\Controllers\Admin\AdminDatabase;
use App\Config\Session;
$admin = new AdminDatabase();

// админ id
$adminID = Session::init('admin')['auth'][1];
?>

<!DOCTYPE html>
<html lang="ru">

<?php include 'includes/head.php'; ?>

<body class="bg-no-repeat flex item-center w-full overflow-x-hidden bg-gray-100">
    <div class="min-h-screen flex w-full mx-auto">

        <!-- navbar -->
        <?php include_once 'includes/sidebar.php'; ?>

        <main class="flex-1 px-4 md:px-6 lg:px-8 overflow-x-hidden">

            <!-- Секция: Главная -->
            <section class="max-w-7xl mx-auto my-3" data-section="main">
                <?php
                // Получаем всю статистику одним вызовом
                $stats = Setting\Route\Function\Controllers\Admin\AdminDatabase::getClientStats();
                $financialStats = Setting\Route\Function\Controllers\Admin\AdminDatabase::getFinancialStats();
                ?>

                <!-- Заголовок -->
                <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                        Статистика
                    </h1>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="h-[400px]">
                        <canvas data-chart="chart_clients"></canvas>
                    </div>
                    <div class="h-[400px]">
                        <canvas data-chart="chart_users_monthly"></canvas>
                    </div>
                </div>

                <!-- Статистика пользователей -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Пользователи</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-gray-500 mb-1">Всего</div>
                            <div class="text-3xl font-bold text-gray-800"><?= $stats['totalUsers'] ?></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-green-600 mb-1">С подписками</div>
                            <div class="text-3xl font-bold text-green-700"><?= $stats['usersWithSubscriptions'] ?></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-gray-500 mb-1">Без подписок</div>
                            <div class="text-3xl font-bold text-gray-600"><?= $stats['usersWithoutSubscriptions'] ?>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-red-600 mb-1">Заблокированных</div>
                            <div class="text-3xl font-bold text-red-700"><?= $stats['bannedUsers'] ?></div>
                        </div>
                    </div>
                </div>

                <!-- Статистика по статусам подписок -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Статистика подписок</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-green-600 mb-1">Активных подписок</div>
                            <div class="text-3xl font-bold text-green-700"><?= $stats['activeSubscriptions'] ?></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-gray-500 mb-1">Неактивных подписок</div>
                            <div class="text-3xl font-bold text-gray-600"><?= $stats['inactiveSubscriptions'] ?></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-red-600 mb-1">Заблокированных подписок</div>
                            <div class="text-3xl font-bold text-red-700"><?= $stats['bannedSubscriptions'] ?></div>
                        </div>
                    </div>
                </div>

                <!-- Статистика цен подписок -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Статистика цен подписок</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-gray-500 mb-1">Всего подписок</div>
                            <div class="text-3xl font-bold text-blue-700"><?= $stats['totalSubscriptions'] ?></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-gray-500 mb-1">Средняя цена</div>
                            <div class="text-3xl font-bold text-gray-800"><?= number_format($stats['avgPrice'], 2) ?> ₽
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="text-sm text-gray-500 mb-1">Максимальная цена</div>
                            <div class="text-3xl font-bold text-gray-800"><?= number_format($stats['maxPrice'], 2) ?> ₽
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Сводка по таблицам -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Сводка по таблицам</h2>
                    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-700">Таблица</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-700">Записей</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach (Setting\Route\Function\Controllers\Admin\AdminDatabase::getTables() as $tableName): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-700 font-medium">
                                            <a href="/admin/database?table=<?= urlencode($tableName) ?>"
                                                class="text-blue-600 hover:underline">
                                                <?= htmlspecialchars($tableName) ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-600">
                                            <?= Setting\Route\Function\Controllers\Admin\AdminDatabase::getCount($tableName) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- PDF Экспорт -->
                        <div
                            class="mt-4 p-4 bg-gradient-to-br from-slate-50 to-gray-100 rounded-xl border border-slate-200">
                            <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white text-xs">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>
                                <span>Экспорт отчетов</span>
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <a href="/export/pdf?type=subscriptions&format=rich"
                                    class="flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 text-sm hover:border-green-400 hover:text-green-600 hover:shadow-sm transition-all">
                                    <i class="fa-solid fa-chart-pie text-slate-400"></i>
                                    <span>Подписки</span>
                                </a>
                                <a href="/export/pdf?type=users&format=rich"
                                    class="flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 text-sm hover:border-green-400 hover:text-green-600 hover:shadow-sm transition-all">
                                    <i class="fa-solid fa-users text-slate-400"></i>
                                    <span>Пользователи</span>
                                </a>
                                <a href="/export/pdf?type=about"
                                    class="flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 text-sm hover:border-green-400 hover:text-green-600 hover:shadow-sm transition-all">
                                    <i class="fa-solid fa-building text-slate-400"></i>
                                    <span>О компании</span>
                                </a>
                                <a href="/export/pdf?type=requisites"
                                    class="flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 text-sm hover:border-green-400 hover:text-green-600 hover:shadow-sm transition-all">
                                    <i class="fa-solid fa-file-invoice text-slate-400"></i>
                                    <span>Реквизиты</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Логи -->
                <div class="container mx-auto">
                    <!-- Заголовок -->
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Логи данных <i
                            class="fa-solid fa-eye-low-vision cursor-pointer" data-show-logs></i>
                    </h2>

                    <div data-logs class="block blur-sm">
                        <div
                            class="bg-black/75 text-white border-b-white p-2 text-start flex items-center px-4 rounded-t-xl">
                            Логи qwees.log</div>
                        <div
                            class="relative max-h-[25vw] overflow-scroll flex flex-col gap-0.5 bg-black rounded-b-xl py-2">
                            <?php
                            $logfile = dirname(__DIR__, 3) . '/qwees.log';
                            if (file_exists($logfile)) {
                                $lines = array_reverse(file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
                                $last_date = null;
                                foreach ($lines as $line) {
                                    $escaped = htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");

                                    $colors = [
                                        'error' => 'text-red-300',
                                        'success' => 'text-green-400',
                                        'Подтверждено' => 'text-green-400',
                                        'Успешное' => 'text-green-400',
                                        'warning' => 'text-yellow-300',
                                        'ОШИБКА' => 'text-yellow-300',
                                        'info' => 'text-blue-400',
                                        'Статус' => 'text-blue-400',
                                        'mail' => 'text-pink-200',
                                    ];

                                    $color = 'text-white';
                                    foreach ($colors as $key => $value) {
                                        if (stripos($line, $key) !== false) {
                                            $color = $value;
                                            break;
                                        }
                                    }

                                    $current_date = null;
                                    if (preg_match('/^\[(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                                        $current_date = $matches[1];
                                    }

                                    if ($current_date && $last_date && $current_date !== $last_date) {
                                        ob_start();
                                        ?>
                                        <div class='flex gap-2 items-center justify-between text-white/70 text-sm px-2 py-0.5'>
                                            <?= date('d M Y', strtotime($matches[1])) ?>
                                            <div class='flex-1 h-0.5 w-full bg-white/70'></div>
                                        </div>
                                        <?php
                                        ob_end_flush();
                                    }

                                    if ($current_date) {
                                        $last_date = $current_date;
                                    }

                                    echo "<div class='text-[13px] font-mono $color px-2 py-0.5 rounded select-text'>$escaped</div>";
                                }
                            } else {
                                echo "<div class='text-[13px] italic bg-card'>Лог-файл не найден.</div>";
                            }
                            ?>
                        </div>
                    </div>

                    <script>
                        $(document).ready(function () {
                            $('[data-show-logs]').on('click', function (event) {
                                $('[data-logs]').toggleClass('blur-sm');
                                if ($(this).hasClass('fa-eye-low-vision')) {
                                    $(this).removeClass('fa-eye-low-vision');
                                    $(this).addClass('fa-eye');
                                } else if ($(this).hasClass('fa-eye')) {
                                    $(this).removeClass('fa-eye');
                                    $(this).addClass('fa-eye-low-vision');
                                }
                            });
                        });
                    </script>
                </div>

            </section>

            <!-- Секция: Графики -->
            <section class="max-w-7xl mx-auto my-3 hidden" data-section="charts">
                <!-- Заголовок -->
                <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                        Графики и аналитика
                    </h1>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="h-[400px]">
                        <canvas data-chart="chart_clients"></canvas>
                    </div>
                    <div class="h-[400px]">
                        <canvas data-chart="chart_revenue_monthly"></canvas>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="h-[400px]">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Статистика пользователей по месяцам</h3>
                        <canvas data-chart="chart_users_monthly"></canvas>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    const ctx = document.querySelectorAll('[data-chart="chart_clients"]');
                    for (const chart of ctx) {
                        new Chart(chart, {
                            type: 'polarArea',
                            data: {
                                labels: ['С подписками', 'Без подписок'],
                                datasets: [{
                                    label: 'Пользователей',
                                    data: [<?= htmlspecialchars((string) intval($stats['usersWithSubscriptions'])) ?>, <?= htmlspecialchars((string) intval($stats['usersWithoutSubscriptions'])) ?>],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    },
                                    title: {
                                        display: true,
                                        text: 'График подписок',
                                    }
                                }
                            }
                        });
                    }

                    // График прибыли по месяцам
                    const revenueMonthlyCtx = document.querySelectorAll('[data-chart="chart_revenue_monthly"]');
                    for (const chart of revenueMonthlyCtx) {
                        new Chart(chart, {
                            type: 'line',
                            data: {
                                labels: <?= json_encode(array_column($financialStats['monthlyRevenueChart'], 'month')) ?>,
                                datasets: [{
                                    label: 'Прибыль (₽)',
                                    data: <?= json_encode(array_column($financialStats['monthlyRevenueChart'], 'revenue')) ?>,
                                    borderColor: 'rgb(34, 197, 94)',
                                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function (value) {
                                                return value.toLocaleString('ru-RU') + ' ₽';
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'center'
                                    },
                                    title: {
                                        display: true,
                                        text: 'График прибыли по месяцам',
                                    }
                                }
                            }
                        });
                    }

                    // График статистика количество пользователей
                    const usersMonthlyCtx = document.querySelectorAll('[data-chart="chart_users_monthly"]');
                    for (const chart of usersMonthlyCtx) {
                        new Chart(chart, {
                            type: 'line',
                            data: {
                                labels: <?= json_encode(array_column($financialStats['monthlyUsersChart'], 'month')) ?>,
                                datasets: [{
                                    label: 'Пользователей',
                                    data: <?= json_encode(array_column($financialStats['monthlyUsersChart'], 'users_count')) ?>,
                                    borderColor: 'rgb(59, 130, 246)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function (value) {
                                                return value.toLocaleString('ru-RU') + ' чел.';
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    },
                                    title: {
                                        display: true,
                                        text: 'Статистика новых пользователей',
                                    }
                                }
                            }
                        });
                    }
                </script>

                <!-- Финансовая статистика (только для админов) -->
                <?php if (AdminAuth::hasRole($adminID, 'admin')): ?>
                    <div class="py-8">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Финансовая статистика</h2>

                        <!-- PDF Экспорт графиков -->
                        <div class="mb-4 flex gap-2">
                            <a href="/export/pdf?type=charts"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Экспорт графиков</span>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-white rounded-xl shadow-sm p-6">
                                <div class="text-sm text-green-600 mb-1">Прибыль за месяц</div>
                                <div class="text-3xl font-bold text-green-700">
                                    <?= number_format($financialStats['monthlyRevenue'], 2) ?> ₽
                                </div>
                            </div>
                            <div class="bg-white rounded-xl shadow-sm p-6">
                                <div class="text-sm text-blue-600 mb-1">Прибыль за неделю</div>
                                <div class="text-3xl font-bold text-blue-700">
                                    <?= number_format($financialStats['weeklyRevenue'], 2) ?> ₽
                                </div>
                            </div>
                            <div class="bg-white rounded-xl shadow-sm p-6">
                                <div class="text-sm text-green-600 mb-1">Прибыль за день</div>
                                <div class="text-3xl font-bold text-green-700">
                                    <?= number_format($financialStats['dailyRevenue'], 2) ?> ₽
                                </div>
                            </div>
                            <div class="bg-white rounded-xl shadow-sm p-6">
                                <div class="text-sm text-orange-600 mb-1">Средний чек</div>
                                <div class="text-3xl font-bold text-orange-700">
                                    <?= number_format($financialStats['avgCheck'], 2) ?> ₽
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- График прибыли по тарифам -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Прибыль по тарифам</h2>
                        <div class="mb-8 h-[400px]">
                            <canvas id="chart_plans"></canvas>
                        </div>
                        <script>
                            const plansCtx = document.getElementById('chart_plans');
                            if (plansCtx) {
                                const labels = <?= json_encode(isset($financialStats['revenueByPlan']) ? array_column($financialStats['revenueByPlan'], 'subscription') : []) ?>;
                                const data = <?= json_encode(isset($financialStats['revenueByPlan']) ? array_column($financialStats['revenueByPlan'], 'revenue') : []) ?>;

                                if (labels.length > 0 && data.length > 0) {
                                    new Chart(plansCtx, {
                                        type: 'doughnut',
                                        data: {
                                            labels: labels,
                                            datasets: [{
                                                label: 'Прибыль (₽)',
                                                data: data,
                                                backgroundColor: [
                                                    'rgba(59, 130, 246, 0.8)',
                                                    'rgba(34, 197, 94, 0.8)',
                                                    'rgba(251, 146, 60, 0.8)',
                                                    'rgba(244, 63, 94, 0.8)',
                                                    'rgba(147, 51, 234, 0.8)'
                                                ],
                                                borderWidth: 2,
                                                borderColor: '#fff'
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: {
                                                legend: {
                                                    display: true,
                                                    position: 'right'
                                                },
                                                tooltip: {
                                                    callbacks: {
                                                        label: function (context) {
                                                            const label = context.label || '';
                                                            const value = context.parsed || 0;
                                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                                            return label + ': ' + value.toLocaleString('ru-RU') + ' ₽ (' + percentage + '%)';
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    plansCtx.parentElement.innerHTML = '<div class="text-center text-gray-500 py-8">Нет данных для отображения</div>';
                                }
                            }
                        </script>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Секция: Цены -->
            <section class="max-w-7xl mx-auto my-3 hidden" data-section="price">
                <!-- Заголовок -->
                <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                        Настройка цен
                    </h1>
                </div>

                <!-- Измение цен -->
                <div class="bg-white border border-border rounded-2xl p-4 sm:p-6 w-full">
                    <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
                        Панель смены цен</h2>
                    <form class="grid grid-cols-1 sm:grid-cols-2 gap-10 mt-4" action="/admin/save" method="POST">
                        <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                        <input type="hidden" name="table" value="qwees_price">

                        <!-- Текущие цены -->
                        <div class="relative">
                            <h3>Текущие цены:</h3>
                            <div class="flex flex-col gap-4">
                                <?php if ($admin->getReadonly('price') === Null)
                                    $admin->setReadonly(['price']);
                                foreach (AdminDatabase::getData('qwees_price') as $key => $value): ?>
                                    <!-- ITEMS 1 -->
                                    <div class="flex gap-3 items-center">
                                        <!-- не пишем name, чтобы не отправлялись в POST -->
                                        <input
                                            class="px-3 py-2 rounded-lg bg-muted border border-border text-purple-400 placeholder-gray-400 focus:ring-accent focus:outline-none mt-2 cursor-no-drop"
                                            value="<?= htmlspecialchars($value['price']) ?> ₽"
                                            <?= $admin->getReadonly('price') ?>>
                                        <label for="plus-val" class="px-1 text-xs">Тариф<br>
                                            <?= strtoupper(htmlspecialchars($value['name'])) ?>:
                                        </label>
                                        <!-- chnage visual -->
                                        <ul data-visual="<?= htmlspecialchars($value['name']) ?>"
                                            class="flex gap-2 items-center transition">
                                            <li class="text-red-500 line-through">
                                                <?= htmlspecialchars($value['price']) ?> ₽
                                            </li>
                                            <li>>></li>
                                            <li
                                                class="text-white underline animate-pulse bg-[#6783FF] px-3 py-1 rounded-lg">
                                            </li>
                                        </ul>
                                        <!-- end -->
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="relative">
                            <h3>Изменение цен:</h3>
                            <div class="flex flex-col gap-4">

                                <?php foreach (AdminDatabase::getData('qwees_price') as $key => $value): ?>
                                    <div class="relative">
                                        <label for="<?= htmlspecialchars($value['name']) ?>"
                                            class="absolute right-10 border rounded-md top-5 bg-card px-1 text-xs">Тариф
                                            <?= strtoupper(htmlspecialchars($value['name'])) ?>
                                        </label>
                                        <input type="number" name="<?= htmlspecialchars($value['name']) ?>"
                                            placeholder="от 1 рубля"
                                            class="w-full px-3 py-2 rounded-lg bg-muted border border-border placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
                                            min="0">
                                    </div>
                                <?php endforeach; ?>

                            </div>
                        </div>
                        <button
                            class="p-2 rounded-lg bg-primary border-dashed border-green-500/60 border-2 hover:bg-green-500/60 transition-colors font-semibold text-base focus:outline-none"
                            type="submit">
                            <i class="far fa-money-bill-alt mr-2 text-sm"></i>
                            Изменить
                        </button>
                        <script defer>
                            document.addEventListener('DOMContentLoaded', function () {
                                $('[data-visual]').hide();
                                <?php foreach (AdminDatabase::getData('qwees_price') as $key => $value): ?>
                                    $("[name='<?= htmlspecialchars($value['name']) ?>']").on('input', (event) => {
                                        $('[data-visual="<?= htmlspecialchars($value['name']) ?>"]').show();
                                        $('[data-visual="<?= htmlspecialchars($value['name']) ?>"]').find('li').eq(2).text(event.target.value + ' ₽');
                                    });
                                <?php endforeach; ?>
                            });
                        </script>
                    </form>
                </div>
            </section>

            <!-- Секция: Логи -->
            <section class="max-w-7xl mx-auto my-3 hidden" data-section="logs">
                <!-- Заголовок -->
                <div class="py-6 flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                        Журнал логов сервера
                    </h1>
                    <form action="/admin/cleanlogs" method="post">
                        <button
                            class="p-2 rounded-lg border-dashed border-red-500/60 border-2 hover:bg-red-500 hover:text-white transition-colors font-semibold text-base focus:outline-none"
                            type="submit">
                            <i class="far fa-trash-alt mr-2 text-sm"></i>
                            Очистить логи
                        </button>
                    </form>
                </div>

                <!-- Логи -->
                <div>

                    <div class="block">
                        <div
                            class="bg-black/75 text-white border-b-white p-2 text-start flex items-center px-4 rounded-t-xl">
                            Логи qwees.log</div>
                        <div
                            class="relative max-h-[42vw] overflow-scroll flex flex-col gap-0.5 bg-black rounded-b-xl py-2">
                            <?php
                            $logfile = dirname(__DIR__, 3) . '/qwees.log';
                            if (file_exists($logfile)) {
                                $lines = array_reverse(file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
                                $last_date = null;
                                foreach ($lines as $line) {
                                    $escaped = htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");

                                    $colors = [
                                        'error' => 'text-red-300',
                                        'success' => 'text-green-400',
                                        'Подтверждено' => 'text-green-400',
                                        'Успешное' => 'text-green-400',
                                        'warning' => 'text-yellow-300',
                                        'ОШИБКА' => 'text-yellow-300',
                                        'info' => 'text-blue-400',
                                        'Статус' => 'text-blue-400',
                                        'mail' => 'text-pink-200',
                                    ];

                                    $color = 'text-white';
                                    foreach ($colors as $key => $value) {
                                        if (stripos($line, $key) !== false) {
                                            $color = $value;
                                            break;
                                        }
                                    }

                                    $current_date = null;
                                    if (preg_match('/^\[(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                                        $current_date = $matches[1];
                                    }

                                    if ($current_date && $last_date && $current_date !== $last_date) {
                                        ob_start();
                                        ?>
                                        <div class='flex gap-2 items-center justify-between text-white/70 text-sm px-2 py-0.5'>
                                            <?= date('d M Y', strtotime($matches[1])) ?>
                                            <div class='flex-1 h-0.5 w-full bg-white/70'></div>
                                        </div>
                                        <?php
                                        ob_end_flush();
                                    }

                                    if ($current_date) {
                                        $last_date = $current_date;
                                    }

                                    echo "<div class='text-[13px] font-mono $color px-2 py-0.5 rounded select-text'>$escaped</div>";
                                }
                            } else {
                                echo "<div class='text-[13px] italic bg-card'>Лог-файл не найден.</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Секция: Выдачи -->
            <section class="max-w-7xl mx-auto my-3 hidden" data-section="give">
                <!-- Заголовок -->
                <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                        Панель выдачи подписок
                    </h1>
                </div>

                <!-- Выдача -->
                <div class="flex flex-col gap-4">

                    <!-- Добавить клиента -->
                    <div class="bg-white border border-border rounded-2xl p-4 sm:p-6 w-full">
                        <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
                            Выдать подписку</h2>
                        <form class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4" action="/admin/addClient"
                            method="POST">
                            <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                            <!-- uniID -->
                            <div class="relative">
                                <label for="client_id"
                                    class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-black text-xs">ID
                                    клиента</label>
                                <input type="text" name="uniID" list="list_uniID"
                                    class="w-full px-3 py-2 text-[15px] rounded-lg bg-muted border border-border text-black placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
                                    placeholder="uniID" required>
                                <datalist id="list_uniID">
                                    <?php foreach (AdminDatabase::getData('qwees_users') as $user): ?>
                                        <option value="<?= htmlspecialchars($user['uniID']) ?>">
                                            <?= htmlspecialchars($user['uniID']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>

                            <!-- count days -->
                            <div class="relative">
                                <label for="give_days"
                                    class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-black text-xs">Количество
                                    дней</label>
                                <input type="number" name="days" placeholder="от 1 до ∞"
                                    class="w-full px-3 py-2 rounded-lg bg-muted border border-border text-black placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
                                    required>
                            </div>

                            <!-- limit divese -->
                            <div class="relative">
                                <label for="give_divece_limit"
                                    class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-black text-xs">Количество
                                    устройств</label>
                                <input type="number" name="devices" placeholder="от 1 до ∞ (0 безлимит)"
                                    class="w-full px-3 py-2 rounded-lg bg-muted border border-border text-black placeholder-gray-400 focus:ring-accent focus:outline-none mt-2"
                                    required>
                            </div>

                            <button
                                class="rounded-lg bg-primary border-dashed border-2 border-green-500/60 hover:bg-green-500/60 transition-colors text-black font-semibold text-base focus:outline-none"
                                type="submit">
                                <i class="fas fa-user-plus mr-2 text-sm"></i>
                                Выдать
                            </button>

                        </form>
                        <div data-user-find class="mt-6">
                            <h2 class="text-lg sm:text-2xl font-semibold text-black/40 tracking-tight">
                                Информация об клиенте</h2>
                            <div class="flex gap-6 mt-6">
                                <!-- Contact info -->
                                <div class="flex flex-col gap-4">
                                    <p class="text-gray-500"><span
                                            class="text-black uppercase border-solid border-r-2 border-black px-2"
                                            data-fuser-id></span> Ф.И: <span class="text-black uppercase"
                                            data-fuser-name></span></p>
                                    <p class="text-gray-500">UniID: <span
                                            class="text-black bg-green-50 px-2 py-1 rounded-lg" data-fuser-uniID></span>
                                    </p>
                                </div>
                                <!-- Subscription info -->
                                <div class="flex flex-col gap-3">
                                    <p class="text-gray-500">Статус подписки: <span
                                            class="text-black px-2 py-1 rounded-lg" data-fuser-status></span></p>
                                    <p class="text-gray-500">Активен до: <span class="text-black px-2 py-1 rounded-sm"
                                            data-fuser-expires></span></p>
                                </div>
                                <!-- Subscription link info -->
                                <div class="flex flex-col gap-3">
                                    <p class="text-gray-500">Количество дней: <span
                                            class="text-black px-2 py-1 rounded-lg" data-fuser-countdays></span></p>
                                    <p class="text-gray-500">Подписка: <span class="text-black px-2 py-1 rounded-lg"
                                            data-fuser-subscription></span>
                                        <button
                                            onclick="copyToClipboard($('[data-fuser-subscription]').text(), 'Подписка')"><i
                                                class="fa-solid fa-copy"></i></button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Секция: Изьятие подписок -->
            <section class="max-w-7xl mx-auto my-3 hidden" data-section="reduce">
                <!-- Заголовок -->
                <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                        Панель изьятия подписок
                    </h1>
                </div>

                <!-- Изьятие -->
                <div class="flex flex-col gap-4">

                    <!-- Изьятие подписки -->
                    <div class="flex flex-col lg:flex-row gap-6 relative bg-white rounded-xl shadow-sm p-6">
                        <div class="flex flex-1 flex-col lg:w-[400px] w-full">
                            <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
                                Изьятие подписки</h2>
                            <form class="flex flex-col gap-4 mt-4" action="/admin/reduceClient" method="POST">
                                <input type="hidden" name="url"
                                    value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                                <!-- ID клиента -->
                                <div class="flex items-center gap-2">
                                    <label for="client_id" class="text-gray-400 text-sm">uniID клиента</label>
                                    <input id="client_id" type="text" name="uniID" list="list_uniID"
                                        class="px-3 py-2 rounded-lg border border-gray-300 text-gray-900 placeholder-gray-400 focus:ring-accent focus:border-accent focus:outline-none"
                                        placeholder="uniID" required>
                                    <datalist id="list_uniID">
                                        <?php foreach (AdminDatabase::getData('qwees_users') as $user): ?>
                                            <option value="<?= htmlspecialchars($user['uniID']) ?>">
                                                <?= htmlspecialchars($user['uniID']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>

                                <!-- Кнопка -->
                                <button data-delete-button
                                    class="rounded-lg px-4 py-2 bg-red-500 text-white font-semibold text-base focus:outline-none hover:bg-red-600"
                                    type="submit">
                                    Изьять подписку
                                </button>

                            </form>
                        </div>
                        <div data-user-find class="flex flex-col w-full">
                            <h2 class="text-lg sm:text-2xl font-semibold text-black/40 tracking-tight">
                                Информация об клиенте</h2>
                            <div class="flex gap-6 mt-6">
                                <!-- Contact info -->
                                <div class="flex flex-1 flex-col gap-4">
                                    <p class="text-gray-500"><span
                                            class="text-black uppercase border-solid border-r-2 border-black px-2"
                                            data-fuser-id></span> Ф.И: <span class="text-black uppercase"
                                            data-fuser-name></span></p>
                                    <p class="text-gray-500">UniID: <span
                                            class="text-black bg-green-50 px-2 py-1 rounded-lg" data-fuser-uniID></span>
                                    </p>
                                </div>
                                <!-- Subscription info -->
                                <div class="flex flex-1 flex-col gap-3">
                                    <p class="text-gray-500">Статус подписки: <span
                                            class="text-black px-2 py-1 rounded-lg" data-fuser-status></span></p>
                                    <p class="text-gray-500">Активен до: <span class="text-black px-2 py-1 rounded-sm"
                                            data-fuser-expires></span></p>
                                </div>
                                <!-- Subscription link info -->
                                <div class="flex flex-1 flex-col gap-3">
                                    <p class="text-gray-500">Количество дней: <span
                                            class="text-black px-2 py-1 rounded-lg" data-fuser-countdays></span></p>
                                    <p class="text-gray-500">Подписка: <span class="text-black px-2 py-1 rounded-lg"
                                            data-fuser-subscription></span>
                                        <button
                                            onclick="copyToClipboard($('[data-fuser-subscription]').text(), 'Подписка')"><i
                                                class="fa-solid fa-copy"></i></button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Секция: Добавление пользователей (только для админов и менеджеров) -->
            <?php if (AdminAuth::hasRole($adminID, 'admin')): ?>
                <section class="max-w-7xl mx-auto my-3 hidden" data-section="add_user">
                    <!-- Заголовок -->
                    <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                        <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                            Добавление пользователя
                        </h1>
                        <div class="text-sm text-gray-500">
                            Ваша роль: <span class="font-semibold text-blue-600">
                                <?= AdminAuth::getRole($adminID) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Форма добавления пользователя -->
                    <div class="bg-white border border-border rounded-2xl p-4 sm:p-6 w-full">
                        <h2 class="text-lg sm:text-2xl font-semibold text-primary-400 tracking-tight">
                            Панель создания пользователя</h2>
                        <form id="form_admin_add_user" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4"
                            action="/admin/addUser" method="POST">
                            <!-- <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>"> -->

                            <!-- Основная информация -->
                            <div class="space-y-4">
                                <h3 class="font-semibold text-gray-700">Основная информация</h3>

                                <div class="relative">
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Имя <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="first_name" name="first_name" required
                                        placeholder="Введите имя пользователя"
                                        class="w-full px-3 py-2 rounded-lg bg-muted border border-border placeholder-gray-400 focus:ring-accent focus:outline-none">
                                </div>

                                <div class="relative">
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Фамилия
                                    </label>
                                    <input type="text" id="last_name" name="last_name" placeholder=" Введите фамилию"
                                        class="w-full px-3 py-2 rounded-lg bg-muted border border-border placeholder-gray-400 focus:ring-accent focus:outline-none">
                                </div>

                                <div class="relative">
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" id="email" required placeholder="user@example.com"
                                        class="w-full px-3 py-2 rounded-lg bg-muted border border-border placeholder-gray-400 focus:ring-accent focus:outline-none">
                                    <p class="text-xs text-gray-500 mt-1">Пользователь сможет войти используя только этот
                                        email</p>
                                </div>
                                <!-- ######## MESSAGE ########## -->
                                <p class="font-sans hidden p-2" id="message_status"></p>
                            </div>

                            <!-- ################################################### -->
                            <!-- Настройки подписки -->
                            <div class="space-y-4">
                                <h3 class="font-semibold text-gray-700">Настройки подписки (необязательно)</h3>

                                <div class="relative">
                                    <label for="subscription" class="block text-sm font-medium text-gray-700 mb-1">
                                        Тип подписки
                                    </label>
                                    <select name="subscription" id="subscription"
                                        class="w-full px-3 py-2 rounded-lg bg-muted border border-border placeholder-gray-400 focus:ring-accent focus:outline-none">
                                        <option value="">Без подписки</option>
                                        <?php
                                        $plans = AdminDatabase::getData('qwees_price');
                                        foreach ($plans as $plan): ?>
                                            <option value="<?= htmlspecialchars($plan['name']) ?>">
                                                <?= htmlspecialchars(ucfirst($plan['name'])) ?> -
                                                <?= htmlspecialchars($plan['price']) ?> ₽
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div id="subscription-details" class="hidden space-y-4">
                                    <div class="relative">
                                        <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-1">
                                            Длительность (дней) <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" name="duration_days" id="duration_days" placeholder="30"
                                            min="1" value="30"
                                            class="w-full px-3 py-2 rounded-lg bg-muted border border-border placeholder-gray-400 focus:ring-accent focus:outline-none">
                                    </div>

                                    <input type="hidden" name="status" value="on">
                                </div>
                            </div>

                            <!-- Кнопка отправки -->
                            <div class="md:col-span-2">
                                <button type="submit"
                                    class="w-full md:w-auto p-3 rounded-lg bg-primary border-dashed border-green-500/60 border-2 hover:bg-green-500/60 transition-colors font-semibold text-base focus:outline-none">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Добавить пользователя
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
                <script src="/public/assets/scripts/auth/admin/main.js"></script>
            <?php endif; ?>

            <script>
                <?php
                $message_status = $_GET['message_status'] ?? null;
                $message_msg = $_GET['message_msg'] ?? null;
                if ($message_status && $message_msg)
                    echo "showNotification('" . addslashes($message_msg) . "', '" . $message_status . "');"; ?>

                // Показать уведомление
                function showNotification(msg, type = 'info') {
                    let container = document.getElementById('notification-container') || ((newContainer = document.createElement('div')) => (newContainer.id = 'notification-container', newContainer.className = 'fixed right-2 top-2 z-[999] flex flex-col gap-2', document.body.appendChild(newContainer), newContainer))();
                    const element = container.appendChild(document.createElement('div'));
                    element.className = `px-6 py-3 rounded-lg text-white z-50 transform translate-x-full transition-transform duration-300 ${{ success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' }[type] || 'bg-blue-500'}`;
                    element.innerHTML = '<i class="fa-solid fa-info-circle"></i> ' + msg;
                    setTimeout(() => element.classList.remove('translate-x-full'), 100);
                    setTimeout(() => element.classList.add('translate-x-full'), 4100);
                    setTimeout(() => (element.remove(), container.children.length || container.remove()), 4400);
                }

                function copyToClipboard(text, label = 'Текст') {
                    if (!text) {
                        showNotification('Нечего копировать', 'error');
                        return;
                    }
                    navigator.clipboard.writeText(text).then(() => {
                        showNotification(`${label} скопирован!`, 'success');
                    });
                }
            </script>
            <script>
                $(document).ready(function () {
                    $('[data-user-find]').hide();
                    $('[data-delete-button]').hide();
                    $('[name="uniID"]').on('input', (event) => {
                        event.preventDefault();
                        $.ajax({
                            url: '/admin/getUser',
                            method: 'POST',
                            data: { uniID: event.target.value },
                            success: function (response) {
                                if (response.status) {
                                    $('[data-user-find]').show(250);
                                    //id
                                    $('[data-fuser-id]').text(response.data.id);
                                    $('[data-fuser-id]').addClass('bg-green-300');
                                    //--
                                    $('[data-fuser-name]').text(response.data.first_name + ' ' + response.data.last_name);
                                    $('[data-fuser-uniID]').text(response.data.uniID);
                                    $('[data-fuser-countdays]').text(response.data.count_days == '' ? '-' : response.data.count_days);
                                    // status
                                    $('[data-fuser-status]').text(response.data.status);
                                    $('[data-fuser-status]').addClass(response.data.status === 'on' ? 'bg-green-100' : 'bg-red-100');
                                    $('[data-fuser-subscription]').addClass(response.data.status === 'on' ? 'bg-green-100' : 'bg-red-100');
                                    $('[data-fuser-subscription]').text(response.data.subscription == '' ? '-' : response.data.subscription);
                                    $('[data-fuser-expires]').text(response.data.date_end ? new Date(response.data.date_end).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }) : '-');
                                    $('[data-fuser-expires]').addClass(response.data.status === 'on' ? 'bg-green-100' : '');
                                    setTimeout(() => { $('[data-delete-button]').show(250); }, 250);
                                } else {
                                    $('[data-user-find]').hide(250);
                                    setTimeout(() => { $('[data-delete-button]').hide(250); }, 250);
                                }
                            }
                        });
                    });
                });
            </script>
            <script src="/public/assets/scripts/main/main.js" defer></script>
        </main>
    </div>
</body>

</html>