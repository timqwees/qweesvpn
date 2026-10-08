<?php

use Setting\Route\Function\Controllers\Admin\AdminAuth;
use Setting\Route\Function\Controllers\Admin\Admin;
use Setting\Route\Function\Controllers\Admin\Group\Groups;
// Проверяем авторизацию администратора
AdminAuth::auth();

$adminUser = new Admin();//вызываем класс
$groups = new Groups();//вызываем класс

use Setting\Route\Function\Controllers\Admin\AdminDatabase;
use Setting\Route\Function\Controllers\Gifts\Gifts;
use Setting\Route\Function\Controllers\Refer\Config\ReferConfig;
use Setting\Route\Function\Controllers\Kassa\PriceConfig;
use App\Config\Database;
use App\Config\Session;
use Setting\Route\Function\Functions;

$site = Functions::site();
$admin = new AdminDatabase();
$gifts = new Gifts();//пробные подписки
$referCfg = ReferConfig::getAll();//настройки рефералки

// Конфигурация тарифов (единый объект из PriceConfig)
$tariffConfig = PriceConfig::getConfig();

// юзеры для datalist инпутов — один запрос вместо четырех
$allUsers = AdminDatabase::getData('qwees_users');
// у кого уже есть подписка — один запрос для подсветки в пробных
$subMap = [];
foreach ((array) Database::send("SELECT uniID, status FROM qwees_subscriptions") as $s) {
    $subMap[$s['uniID']] = ($s['status'] ?? '') === 'on' ? 1 : 0;
}

// Все сроки (объединение по тарифам) — шапка таблицы цен
$periods = [];
foreach ($tariffConfig as $tariff) {
    foreach ($tariff['periods'] as $months => $period) {
        $periods[$months] = $period;
    }
}
ksort($periods);

// Цветовые акценты для карточек тарифов
$tariffAccents = [
    'basic'  => ['badge' => 'bg-blue-500/10 text-blue-400 ring-1 ring-blue-500/30',   'accent' => 'border-l-blue-500'],
    'clasic' => ['badge' => 'bg-green-500/10 text-green-400 ring-1 ring-green-500/30', 'accent' => 'border-l-green-500'],
    'pro'    => ['badge' => 'bg-red-500/10 text-red-400 ring-1 ring-red-500/30',      'accent' => 'border-l-red-500'],
];
$defaultAccent = ['badge' => 'bg-white/10 text-gray-300 ring-1 ring-white/10', 'accent' => 'border-l-gray-400'];

// админ id [true, id]
$adminID = (int) (Session::init('admin')['auth'][1] ?? 0);
$adminUsername = $adminUser->getUsername($adminID);//имя работника
$adminRole = $adminUser->getRole($adminID);//роль работника
?>

<!DOCTYPE html>
<html lang="ru">

<?php include 'includes/head.php'; ?>

<body class="bg-no-repeat flex item-center w-full overflow-x-hidden bg-[#0f1115] text-gray-200">
    <div class="min-h-screen flex w-full mx-auto">

        <!-- оверлей (мобильная шторка) -->
        <div id="admin-overlay" class="fixed inset-0 bg-black/40 z-40 hidden md:hidden"></div>

        <!-- кнопка открытия меню (мобильная) -->
        <button id="admin-burger"
            class="md:hidden fixed top-2 left-2 z-[60] bg-[#16181d] rounded-lg shadow-md p-2.5 text-gray-200">
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

        <!-- navbar -->
        <?php include_once 'includes/sidebar.php'; ?>

        <main class="flex-1 min-w-0 px-4 pt-14 md:pt-0 md:px-6 lg:px-8 overflow-x-hidden">

            <!-- Секция: Главная -->
            <?php if ($groups->isPermission($adminUsername,'main')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6" data-section="main">
                <?php /* Статистика подтягивается лениво через /api/admin/stats (см. загрузчик внизу) */ ?>

                <!-- Заголовок -->
                <div class="flex-col flex md:flex-row md:items-center justify-between gap-2">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-100">Главная</h1>
                        <p class="text-xs text-gray-500"><?= date('d.m.Y, l') ?> · операционная сводка сервиса</p>
                    </div>
                    <div class="text-xs text-gray-500">Только необходимые цифры — деньги в <span class="text-gray-300">Прибыли</span>, логи в <span class="text-gray-300">Логах</span></div>
                </div>

                <!-- KPI: пользователи -->
                <div>
                    <h2 class="text-[11px] uppercase tracking-wider text-gray-500 mb-3">Пользователи</h2>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-users text-blue-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">Всего</div>
                                <div class="text-2xl font-bold text-gray-100"><span data-st="totalUsers">…</span></div>
                            </div>
                        </div>
                        <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-circle-check text-green-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">С подписками</div>
                                <div class="text-2xl font-bold text-green-400"><span data-st="usersWithSubscriptions">…</span></div>
                            </div>
                        </div>
                        <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user-clock text-gray-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">Без подписок</div>
                                <div class="text-2xl font-bold text-gray-300"><span data-st="usersWithoutSubscriptions">…</span></div>
                            </div>
                        </div>
                        <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-percent text-blue-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">Конверсия в подписку</div>
                                <div class="text-2xl font-bold text-blue-400"><span data-st="convRate">…</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI: подписки -->
                <div>
                    <h2 class="text-[11px] uppercase tracking-wider text-gray-500 mb-3">Подписки</h2>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-bolt text-green-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">Активных</div>
                                <div class="text-2xl font-bold text-green-400"><span data-st="activeSubscriptions">…</span></div>
                            </div>
                        </div>
                        <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-pause text-gray-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">Неактивных</div>
                                <div class="text-2xl font-bold text-gray-300"><span data-st="inactiveSubscriptions">…</span></div>
                            </div>
                        </div>
                        <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-layer-group text-blue-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">Всего подписок</div>
                                <div class="text-2xl font-bold text-blue-400"><span data-st="totalSubscriptions">…</span></div>
                            </div>
                        </div>
                        <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">В сети сейчас</div>
                                <div class="text-2xl font-bold text-emerald-400"><span data-m-online>…</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Быстрые действия -->
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <?php if ($groups->isPermission($adminUsername,'give')): ?>
                    <button data-toggle-section="give" class="flex items-center gap-2 px-4 py-2 bg-green-600/15 ring-1 ring-green-500/25 rounded-xl text-green-300 text-sm font-medium hover:bg-green-600/25 transition-all whitespace-nowrap">
                        <i class="fa-solid fa-key"></i><span>Выдать подписку</span>
                    </button>
                    <?php endif; ?>
                    <?php if ($groups->isPermission($adminUsername,'chat')): ?>
                    <button data-toggle-section="chat" class="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-gray-300 text-sm hover:border-green-400 hover:text-green-400 transition-all whitespace-nowrap">
                        <i class="fa-solid fa-comments text-gray-500"></i><span>Открыть чат</span>
                    </button>
                    <?php endif; ?>
                    <?php if ($groups->isPermission($adminUsername,'add_user')): ?>
                    <button data-toggle-section="add_user" class="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-gray-300 text-sm hover:border-green-400 hover:text-green-400 transition-all whitespace-nowrap">
                        <i class="fa-solid fa-user-plus text-gray-500"></i><span>Добавить юзера</span>
                    </button>
                    <?php endif; ?>
                    <?php if ($groups->isPermission($adminUsername,'monitoring')): ?>
                    <button data-toggle-section="monitoring" class="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-gray-300 text-sm hover:border-green-400 hover:text-green-400 transition-all whitespace-nowrap">
                        <i class="fa-solid fa-heart-pulse text-gray-500"></i><span>Мониторинг</span>
                    </button>
                    <?php endif; ?>
                    <?php if ($groups->isPermission($adminUsername,'profit')): ?>
                    <button data-toggle-section="profit" class="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-gray-300 text-sm hover:border-green-400 hover:text-green-400 transition-all whitespace-nowrap">
                        <i class="fa-solid fa-wallet text-gray-500"></i><span>Прибыль</span>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Графики -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <h2 class="text-[11px] uppercase tracking-wider text-gray-500 mb-3">Структура базы</h2>
                        <div class="h-[300px]">
                            <canvas data-chart="chart_clients"></canvas>
                        </div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <h2 class="text-[11px] uppercase tracking-wider text-gray-500 mb-3">Новые пользователи</h2>
                        <div class="h-[300px]">
                            <canvas data-chart="chart_users_monthly"></canvas>
                        </div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <h2 class="text-[11px] uppercase tracking-wider text-gray-500 mb-3">Онлайн сейчас</h2>
                        <div class="h-[300px]">
                            <canvas data-chart="chart_online"></canvas>
                        </div>
                    </div>
                </div>
            </section>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="main">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Мониторинг -->
            <?php if ($groups->isPermission($adminUsername,'monitoring')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="monitoring">
                <div class="rounded-2xl bg-[#16181d] text-gray-200 ring-1 ring-white/5 p-4 md:p-6 flex flex-col gap-4">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span data-ov-pill
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 ring-1 ring-white/10 text-gray-200">
                        <span data-ov-pill-dot class="w-2 h-2 rounded-full bg-white/50"></span>
                        <span data-ov-pill-text>Подключение…</span>
                    </span>
                    <span data-ov-badge class="hidden text-xs font-medium px-2.5 py-1 rounded-full"></span>
                    <span class="ml-auto text-xs text-gray-500">Обновлено: <span data-ov-updated>—</span></span>
                    <button data-ov-refresh
                        class="text-xs font-medium px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10 transition-colors">
                        <i class="fa-solid fa-rotate-right mr-1"></i>Обновить
                    </button>
                </div>

                <!-- Онлайн и пользователи -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">В сети сейчас</div>
                        <div class="text-3xl font-bold text-white" data-ov-online>—</div>
                    </div>
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Пик сегодня</div>
                        <div class="text-3xl font-bold text-white" data-ov-peak>—</div>
                    </div>
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Активные подписки</div>
                        <div class="text-3xl font-bold text-emerald-400" data-ov-active>—</div>
                    </div>
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Пользователей</div>
                        <div class="text-3xl font-bold text-white" data-ov-users>—</div>
                        <div class="text-xs text-gray-500 mt-1">+<span data-ov-new>0</span> сегодня</div>
                    </div>
                </div>

                <!-- Трафик -->
                <div>
                    <h2 class="text-[11px] uppercase tracking-wider text-gray-500 mb-3">Трафик (↓ + ↑)</h2>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                            <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Всего</div>
                            <div class="text-2xl font-bold text-white" data-ov-t-total>—</div>
                            <div class="text-xs text-gray-500 mt-1"><span data-ov-t-total-up></span> ↑ · <span data-ov-t-total-down></span> ↓</div>
                        </div>
                        <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                            <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">24 часа</div>
                            <div class="text-2xl font-bold text-white" data-ov-t-24h>—</div>
                        </div>
                        <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                            <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">7 дней</div>
                            <div class="text-2xl font-bold text-white" data-ov-t-7d>—</div>
                        </div>
                        <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                            <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">30 дней</div>
                            <div class="text-2xl font-bold text-white" data-ov-t-30d>—</div>
                        </div>
                    </div>
                </div>

                <!-- Самопроверки -->
                <div>
                    <h2 class="text-[11px] uppercase tracking-wider text-gray-500 mb-3">Самопроверки · БД ↔ панель</h2>
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-3" data-ov-health>
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span data-h-pill class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 ring-1 ring-white/10 text-gray-300">Проверка…</span>
                            <button data-cu-open
                                class="text-xs font-medium px-3 py-1.5 rounded-lg bg-red-500/10 ring-1 ring-red-500/25 text-red-300 hover:bg-red-500/20 transition-colors">
                                <i class="fa-solid fa-broom mr-1"></i>Очистить неактивные
                            </button>
                            <span class="ml-auto text-xs text-gray-500">панель: <b class="text-gray-300" data-h-panel>—</b> · подписок в БД: <b class="text-gray-300" data-h-db>—</b></span>
                        </div>
                        <div class="hidden rounded-lg bg-red-500/5 ring-1 ring-red-500/20 p-3 flex-col gap-2 text-xs" data-cu-preview></div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                            <div class="rounded-lg bg-white/[0.02] ring-1 ring-white/5 p-3">
                                <div class="text-xs text-gray-400 mb-1">Оплачено, но ключа нет · <b class="text-white" data-h-missing-n>—</b></div>
                                <div class="text-xs text-gray-500 max-h-28 overflow-y-auto flex flex-col gap-1" data-h-missing>—</div>
                            </div>
                            <div class="rounded-lg bg-white/[0.02] ring-1 ring-white/5 p-3">
                                <div class="text-xs text-gray-400 mb-1">Ключи без активной подписки · <b class="text-white" data-h-ghosts-n>—</b></div>
                                <div class="text-xs text-gray-500 max-h-28 overflow-y-auto flex flex-col gap-1" data-h-ghosts>—</div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-400 flex flex-col gap-1" data-h-subs></div>
                    </div>
                </div>

                <!-- Серверы -->
                <div data-ov-servers class="flex flex-col gap-4"></div>
                </div>
            </section>
            <script defer>
                $(document).ready(function () {
                    function ovVisible() {
                        return $('[data-section="monitoring"]:not(.hidden)').length > 0;
                    }
                    function ovBytes(b) {
                        b = Math.max(0, Number(b) || 0);
                        if (b < 1024) return b + ' B';
                        var u = ['KB', 'MB', 'GB', 'TB', 'PB'], i = Math.floor(Math.log(b) / Math.log(1024));
                        i = Math.min(i, u.length);
                        return (b / Math.pow(1024, i)).toFixed(2) + ' ' + u[i - 1];
                    }
                    function ovPeriod(sel, x) {
                        $(sel).text(x ? ovBytes((x.up || 0) + (x.down || 0)) : 'сбор данных');
                    }
                    var ovCharts = {};
                    var ovDrawQueue = [];
                    function ovDestroyChart(id) {
                        if (ovCharts[id]) { try { ovCharts[id].destroy(); } catch (e) {} delete ovCharts[id]; }
                    }
                    function ovSpark(id, pairs, color, fill) {
                        var el = document.getElementById(id);
                        if (!el || typeof Chart === 'undefined') return;
                        ovDestroyChart(id);
                        var labels = pairs.map(function (p) { return p[0]; });
                        var vals = pairs.map(function (p) { return p[1]; });
                        var ctx = el.getContext('2d');
                        var g = ctx.createLinearGradient(0, 0, 0, el.height || 56);
                        g.addColorStop(0, color + '55'); g.addColorStop(1, color + '00');
                        ovCharts[id] = new Chart(el, {
                            type: 'line',
                            data: { labels: labels, datasets: [{ data: vals, borderColor: color, borderWidth: 1.5, pointRadius: 0, tension: 0.4, fill: !!fill, backgroundColor: fill ? g : 'transparent' }] },
                            options: { responsive: true, maintainAspectRatio: false, animation: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false } } }
                        });
                    }
                    function ovAvgPeak(pairs) {
                        if (!pairs || !pairs.length) return { avg: null, peak: null };
                        var vs = pairs.map(function (p) { return Number(p[1]) || 0; });
                        return { avg: vs.reduce(function (a, b) { return a + b; }, 0) / vs.length, peak: Math.max.apply(null, vs) };
                    }
                    function ovSpeed(bps) {
                        bps = Math.max(0, Number(bps) || 0);
                        if (bps < 1024) return Math.round(bps) + ' B/s';
                        var u = ['KB/s', 'MB/s', 'GB/s'], i = Math.floor(Math.log(bps) / Math.log(1024));
                        i = Math.min(i, u.length);
                        return (bps / Math.pow(1024, i)).toFixed(2) + ' ' + u[i - 1];
                    }
                    function ovTrafficChart(id, ups, downs) {
                        var el = document.getElementById(id);
                        if (!el || typeof Chart === 'undefined') return;
                        ovDestroyChart(id);
                        function fmtT(ts) {
                            var dt = new Date((Number(ts) || 0) * 1000);
                            return ('0' + dt.getHours()).slice(-2) + ':' + ('0' + dt.getMinutes()).slice(-2);
                        }
                        var labels = ups.map(function (p) { return fmtT(p[0]); });
                        ovCharts[id] = new Chart(el, {
                            type: 'line',
                            data: { labels: labels, datasets: [
                                { label: 'Загрузка', data: ups.map(function (p) { return p[1]; }), borderColor: '#2f81f7', borderWidth: 1.5, pointRadius: 0, tension: 0.4 },
                                { label: 'Скачать', data: downs.map(function (p) { return p[1]; }), borderColor: '#8b949e', borderWidth: 1.5, pointRadius: 0, tension: 0.4 }
                            ] },
                            options: { responsive: true, maintainAspectRatio: false, animation: false,
                                plugins: { legend: { display: false }, tooltip: { enabled: true, backgroundColor: '#0d1117', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1, titleColor: '#9aa4b2', bodyColor: '#e6e9ee', callbacks: { label: function (c) { return ' ' + c.dataset.label + ' ' + ovSpeed(c.parsed.y); } } } },
                                scales: { x: { display: false }, y: { display: false } }, interaction: { mode: 'index', intersect: false } }
                        });
                    }
                    function ovBadge(kind, text) {
                        var $b = $('[data-ov-badge]');
                        if (!text) { $b.addClass('hidden'); return; }
                        $b.removeClass('hidden');
                        var cls = kind === 'red'
                            ? 'bg-red-500/10 text-red-400 ring-1 ring-red-500/25'
                            : 'bg-yellow-500/10 text-yellow-400 ring-1 ring-yellow-500/25';
                        $b.attr('class', 'text-xs font-medium px-2.5 py-1 rounded-full ' + cls).text(text);
                    }
                    function renderOverview(d) {
                        if (!d || d.status !== 'ok') { ovBadge('red', 'Нет данных'); return; }
                        var t = d.totals || {}, u = d.users || {}, tr = d.traffic || {};
                        $('[data-ov-online]').text(t.online_now ?? '—');
                        $('[data-ov-peak]').text(t.peak_today ?? '—');
                        $('[data-ov-active]').text(u.active ?? '—');
                        $('[data-ov-users]').text(u.total ?? '—');
                        $('[data-ov-new]').text(u.new_today ?? 0);
                        $('[data-ov-t-total]').text(ovBytes((t.up_total || 0) + (t.down_total || 0)));
                        $('[data-ov-t-total-up]').text(ovBytes(t.up_total || 0));
                        $('[data-ov-t-total-down]').text(ovBytes(t.down_total || 0));
                        ovPeriod('[data-ov-t-24h]', tr['24h']);
                        ovPeriod('[data-ov-t-7d]', tr['7d']);
                        ovPeriod('[data-ov-t-30d]', tr['30d']);
                        if (d.stale) ovBadge('red', 'Панель недоступна');
                        else if (d.collecting) ovBadge('yellow', 'Сбор истории трафика');
                        else ovBadge(null);
                        $('[data-ov-updated]').text(new Date(d.at_ms || Date.now()).toLocaleTimeString('ru-RU'));
                        var $w = $('[data-ov-servers]').empty();
                        Object.keys(ovCharts).forEach(ovDestroyChart);
                        ovDrawQueue = [];
                        // Пилюля статуса первого сервера
                        var s0 = (d.servers || [])[0];
                        if (s0 && !s0.stale) {
                            $('[data-ov-pill-dot]').attr('class', 'w-2 h-2 rounded-full ' + (s0.xray_state === 'running' ? 'bg-green-500' : 'bg-yellow-500'));
                            $('[data-ov-pill-text]').text('Xray · ' + (s0.xray_state === 'running' ? 'Запущен' : s0.xray_state) + (s0.xray_version ? ' ' + s0.xray_version : '') + ' · ' + (s0.country || s0.code));
                        } else {
                            $('[data-ov-pill-dot]').attr('class', 'w-2 h-2 rounded-full bg-red-500');
                            $('[data-ov-pill-text]').text('Нет связи с панелью');
                        }
                        if (d.stale && !(d.servers || []).length) {
                            $w.append($('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-6 text-sm text-gray-400').text('Нет связи с панелями'));
                        }
                        function ovMetricCard(idp, title, big, sub, histPairs, suffix, color) {
                            var st = ovAvgPeak(histPairs);
                            var $c = $('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-1 min-w-0');
                            $c.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500').text(title));
                            $c.append($('<div>').addClass('text-3xl font-bold text-white').html(big + ' <span class="text-sm font-medium text-gray-500">' + suffix + '</span>'));
                            $c.append($('<div>').addClass('text-xs text-gray-400 truncate').text(sub || ''));
                            $c.append($('<div>').addClass('flex justify-between text-[11px] text-gray-500')
                                .append($('<span>').text('СРЕДНЕЕ ' + (st.avg === null ? '—' : (Math.round(st.avg * 10) / 10) + suffix)))
                                .append($('<span>').text('ПИК ' + (st.peak === null ? '—' : (Math.round(st.peak * 10) / 10) + suffix))));
                            $c.append($('<div>').addClass('h-14').append($('<canvas>').attr('id', idp).css({ width: '100%', height: '100%' })));
                            ovDrawQueue.push({ id: idp, pairs: histPairs, color: color });
                            return $c;
                        }
                        (d.servers || []).forEach(function (s) {
                            var p = 'ovc_' + String(s.code).replace(/[^a-z0-9]/gi, '') + '_';
                            var $srv = $('<div>').addClass('flex flex-col gap-4');
                            $srv.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500').text('Сервер · ' + (s.country || s.code) + ' · ' + s.code));
                            if (s.stale) {
                                $srv.append($('<div>').addClass('rounded-xl bg-red-500/5 ring-1 ring-red-500/20 p-6 text-sm text-red-400').text('Нет связи с панелью ' + (s.country || s.code)));
                                $w.append($srv);
                                return;
                            }
                            var memPct = s.mem_total ? (s.mem_used / s.mem_total * 100) : 0;
                            var diskPct = s.disk_total ? (s.disk_used / s.disk_total * 100) : 0;
                            var swapPct = s.swap_total ? (s.swap_used / s.swap_total * 100) : 0;
                            var h = s.history || {};
                            // Ряд 1: ЦП / Память / Подкачка / Диск
                            var $row = $('<div>').addClass('grid grid-cols-2 lg:grid-cols-4 gap-4');
                            $row.append(ovMetricCard(p + 'cpu', 'ЦП', (s.cpu ?? '—'), s.cpu_sub || '', h.cpu || [], '%', '#2f81f7'));
                            $row.append(ovMetricCard(p + 'mem', 'Память', (s.mem_total ? (Math.round(memPct * 10) / 10) : '—'), s.mem_h || '', h.mem || [], '%', '#2f81f7'));
                            var $swap = $('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-1 min-w-0');
                            $swap.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500').text('Подкачка'));
                            $swap.append($('<div>').addClass('text-3xl font-bold text-white').html((s.swap_total ? (Math.round(swapPct * 10) / 10) : '0') + ' <span class="text-sm font-medium text-gray-500">%</span>'));
                            $swap.append($('<div>').addClass('text-xs text-gray-400 truncate').text(s.swap_h || ''));
                            $row.append($swap);
                            var $disk = $('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-1 min-w-0');
                            $disk.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500').text('Диск'));
                            $disk.append($('<div>').addClass('text-3xl font-bold text-white').html((s.disk_total ? (Math.round(diskPct * 10) / 10) : '—') + ' <span class="text-sm font-medium text-gray-500">%</span>'));
                            $disk.append($('<div>').addClass('text-xs text-gray-400 truncate').text(s.disk_h || ''));
                            $disk.append($('<div>').addClass('flex justify-between text-[11px] text-gray-500').append($('<span>').text('СВОБОДНО ' + (s.disk_free_h || '—'))));
                            $row.append($disk);
                            $srv.append($row);
                            // Ряд 2: трафик + соединения
                            var $row2 = $('<div>').addClass('grid grid-cols-1 lg:grid-cols-3 gap-4');
                            var $tr = $('<div>').addClass('lg:col-span-2 rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-2 min-w-0');
                            var lastUp = (h.netUp && h.netUp.length) ? h.netUp[h.netUp.length - 1][1] : 0;
                            var lastDown = (h.netDown && h.netDown.length) ? h.netDown[h.netDown.length - 1][1] : 0;
                            var $trHead = $('<div>').addClass('flex flex-wrap items-baseline gap-x-4 gap-y-1');
                            $trHead.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500').text('Общая скорость передачи трафика'));
                            $trHead.append($('<div>').addClass('ml-auto text-xs text-gray-400').html('↑ Загрузка <b class="text-white">' + ovSpeed(lastUp) + '</b> &nbsp; ↓ Скачать <b class="text-white">' + ovSpeed(lastDown) + '</b>'));
                            $tr.append($trHead);
                            $tr.append($('<div>').addClass('h-44').append($('<canvas>').attr('id', p + 'traf').css({ width: '100%', height: '100%' })));
                            var avgUp = ovAvgPeak(h.netUp || []).avg || 0, avgDown = ovAvgPeak(h.netDown || []).avg || 0;
                            $tr.append($('<div>').addClass('grid grid-cols-3 gap-2 text-xs')
                                .append($('<div>').append($('<div>').addClass('text-gray-500 text-[11px] uppercase').text('Отправлено')).append($('<div>').addClass('text-white font-bold text-base').text(s.net_sent_h || s.up_total_h || '—')))
                                .append($('<div>').append($('<div>').addClass('text-gray-500 text-[11px] uppercase').text('Получено')).append($('<div>').addClass('text-white font-bold text-base').text(s.net_recv_h || s.down_total_h || '—')))
                                .append($('<div>').append($('<div>').addClass('text-gray-500 text-[11px] uppercase').text('Среднее за период')).append($('<div>').addClass('text-white font-bold text-base').text('↑ ' + ovSpeed(avgUp) + ' ↓ ' + ovSpeed(avgDown)))));
                            $srv.append($tr);
                            ovDrawQueue.push({ traf: p + 'traf', ups: h.netUp || [], downs: h.netDown || [] });
                            var $conn = $('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-2 min-w-0');
                            $conn.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500').text('Количество соединений'));
                            $conn.append($('<div>').addClass('text-3xl font-bold text-white').html(((s.tcp || 0) + (s.udp || 0)) + ' <span class="text-sm font-medium text-gray-500">открытых сокетов</span>'));
                            $conn.append($('<div>').addClass('flex gap-4 text-xs text-gray-400').html('<span><i class="text-[#2f81f7]">—</i> TCP <b class="text-white">' + (s.tcp ?? '—') + '</b></span><span><i class="text-gray-500">—</i> UDP <b class="text-white">' + (s.udp ?? '—') + '</b></span>'));
                            $conn.append($('<div>').addClass('h-44').append($('<canvas>').attr('id', p + 'conn').css({ width: '100%', height: '100%' })));
                            ovDrawQueue.push({ id: p + 'conn', pairs: h.online || [], color: '#2f81f7' });
                            $row2.append($tr); $row2.append($conn);
                            $srv.append($row2);
                            // Ряд 3: аптайм / панель / сеть / топ
                            var $row3 = $('<div>').addClass('grid grid-cols-1 lg:grid-cols-3 gap-4');
                            $row3.append($('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 text-xs')
                                .append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500 mb-2').text('Время работы'))
                                .append($('<div>').addClass('flex gap-6').append($('<div>').append($('<div>').addClass('text-gray-500').text('OS')).append($('<div>').addClass('text-white font-bold text-base').text(s.uptime_h || '—'))).append($('<div>').append($('<div>').addClass('text-gray-500').text('Панель')).append($('<div>').addClass('text-white font-bold text-base').text(s.panel_uptime_h || '—')))));
                            $row3.append($('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 text-xs')
                                .append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500 mb-2').text('Панель · ' + (s.ip || '')))
                                .append($('<div>').addClass('flex gap-6').append($('<div>').append($('<div>').addClass('text-gray-500').text('Память')).append($('<div>').addClass('text-white font-bold text-base').text(s.panel_mem_h || '—'))).append($('<div>').append($('<div>').addClass('text-gray-500').text('Потоки')).append($('<div>').addClass('text-white font-bold text-base').text(s.panel_threads ?? '—'))).append($('<div>').append($('<div>').addClass('text-gray-500').text('В сети')).append($('<div>').addClass('text-emerald-400 font-bold text-base').text(s.online_now ?? '—')))));
                            var $top = $('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 text-xs');
                            $top.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500 mb-2').text('Топ по трафику'));
                            if ((s.top || []).length) {
                                var $t = $('<table>').addClass('w-full text-gray-300');
                                s.top.forEach(function (cl) {
                                    $t.append($('<tr>').addClass('border-t border-white/5')
                                        .append($('<td>').addClass('py-1 pr-2 truncate max-w-[180px]').text(cl.email || ''))
                                        .append($('<td>').addClass('py-1 text-right whitespace-nowrap text-gray-400').text(cl.down_h || '')));
                                });
                                $top.append($t);
                            } else { $top.append($('<div>').addClass('text-gray-500').text('—')); }
                            $row3.append($top);
                            $srv.append($row3);
                            $w.append($srv);
                        });
                        // Рисуем графики только когда canvas уже в документе (иначе нулевой размер)
                        ovDrawQueue.forEach(function (job) {
                            if (job.traf) ovTrafficChart(job.traf, job.ups, job.downs);
                            else ovSpark(job.id, job.pairs, job.color, true);
                        });
                        ovDrawQueue = [];
                        var $dot = $('[data-ov-menu-dot]');
                        if (d.stale) $dot.removeClass('hidden bg-green-500').addClass('bg-red-500');
                        else $dot.removeClass('hidden bg-red-500').addClass('bg-green-500');
                    }
                    var ovRetried = false;
                    window.__healthLoaded = false;
                    function renderHealth(h) {
                        if (!h || h.status !== 'ok') return;
                        var $pill = $('[data-h-pill]');
                        if (h.ok) $pill.attr('class', 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/25 text-emerald-300 text-sm').text('Всё сходится');
                        else $pill.attr('class', 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-yellow-500/10 ring-1 ring-yellow-500/25 text-yellow-300 text-sm').text('Есть расхождения');
                        $('[data-h-panel]').text(h.panel_clients ?? '—');
                        $('[data-h-db]').text(h.db_rows ?? '—');
                        $('[data-h-missing-n]').text(h.missing_count ?? 0);
                        $('[data-h-ghosts-n]').text(h.ghosts_count ?? 0);
                        var $m = $('[data-h-missing]').empty();
                        if (!(h.missing || []).length) $m.append($('<span>').addClass('text-emerald-400/70').text('пусто — все оплаченные с ключами'));
                        (h.missing || []).forEach(function (r) {
                            $m.append($('<span>').addClass('text-red-400').text((r.uniID || '') + ' · ' + (r.email || '')));
                        });
                        var $g = $('[data-h-ghosts]').empty();
                        if (!(h.ghosts || []).length) $g.append($('<span>').addClass('text-emerald-400/70').text('пусто — лишних ключей нет'));
                        (h.ghosts || []).forEach(function (r) {
                            $g.append($('<span>').addClass(r.enable ? 'text-yellow-300' : 'text-gray-500').text((r.key || '') + ' · ' + (r.email || '') + (r.enable ? ' · ВКЛЮЧЁН' : ' · выкл')));
                        });
                        var $s = $('[data-h-subs]').empty();
                        (h.subscriptions || []).forEach(function (x) {
                            var ok = x.ok === true;
                            $s.append($('<div>').addClass('flex flex-wrap gap-x-3')
                                .append($('<span>').addClass(ok ? 'text-emerald-400' : 'text-red-400').text((ok ? '●' : '●') + ' подписка ' + (x.server || '')))
                                .append($('<span>').text('ссылок: ' + (x.link_count ?? 0)))
                                .append($('<span>').text((x.ms ?? 0) + ' мс'))
                                .append($('<span>').addClass('text-gray-500').text(x.ok ? 'reality/pbk/sni на месте' : (x.error || 'проверки не прошли'))));
                        });
                    }
                    function loadHealth(force) {
                        if (!force && (window.__healthLoaded || !ovVisible())) return;
                        $.ajax({
                            url: '/api/admin/health' + (force ? '?fresh=1' : ''), method: 'GET', dataType: 'json', timeout: 60000,
                            success: function (hh) { window.__healthLoaded = true; renderHealth(hh); }
                        });
                    }
                    function cuList(items, key1, key2) {
                        if (!items || !items.length) return '<span class="text-gray-500">—</span>';
                        return items.slice(0, 20).map(function (r) {
                            return '<div class="text-gray-300">' + $('<div>').text((r[key1] || '') + ' · ' + (r[key2] || '')).html() + '</div>';
                        }).join('') + (items.length > 20 ? '<div class="text-gray-500">…и ещё ' + (items.length - 20) + '</div>' : '');
                    }
                    $('[data-cu-open]').on('click', function () {
                        var $box = $('[data-cu-preview]').removeClass('hidden').addClass('flex');
                        $box.html('<div class="text-gray-400">Считаю, что будет удалено…</div>');
                        $.ajax({
                            url: '/api/admin/cleanup', method: 'POST', data: { mode: 'preview' }, dataType: 'json', timeout: 60000,
                            success: function (p) {
                                if (!p || p.status !== 'ok') { $box.html('<div class="text-red-400">Ошибка превью</div>'); return; }
                                var total = (p.db_expired_count || 0) + (p.db_off_count || 0) + (p.panel_ghosts_count || 0);
                                var h = '<div class="text-gray-200 font-medium">Будет удалено: <b>' + total + '</b> (строк БД: ' + ((p.db_expired_count || 0) + (p.db_off_count || 0)) + ', ключей: ' + (p.panel_ghosts_count || 0) + ')</div>';
                                h += '<div class="grid grid-cols-1 md:grid-cols-3 gap-2">';
                                h += '<div><div class="text-gray-400 mb-1">Истёкшие · ' + (p.db_expired_count || 0) + '</div>' + cuList(p.db_expired, 'uniID', 'email') + '</div>';
                                h += '<div><div class="text-gray-400 mb-1">Выключенные · ' + (p.db_off_count || 0) + '</div>' + cuList(p.db_off, 'uniID', 'email') + '</div>';
                                h += '<div><div class="text-gray-400 mb-1">Ключи-призраки · ' + (p.panel_ghosts_count || 0) + '</div>' + cuList(p.panel_ghosts, 'key', 'email') + '</div>';
                                h += '</div>';
                                h += '<div class="text-gray-500">Пропущено: бан ' + (p.skipped_banned || 0) + ', ожидают VPN ' + (p.skipped_pending || 0) + ' — их не трогаем</div>';
                                h += '<div class="flex gap-2"><button data-cu-do class="text-xs font-medium px-4 py-2 rounded-lg bg-red-500/20 ring-1 ring-red-500/40 text-red-200 hover:bg-red-500/30 transition-colors">Удалить ' + total + '</button>';
                                h += '<button data-cu-cancel class="text-xs font-medium px-4 py-2 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-300 hover:bg-white/10 transition-colors">Отмена</button></div>';
                                $box.html(h);
                            },
                            error: function () { $box.html('<div class="text-red-400">Ошибка превью</div>'); }
                        });
                    });
                    $(document).on('click', '[data-cu-cancel]', function () {
                        $('[data-cu-preview]').addClass('hidden').removeClass('flex').empty();
                    });
                    $(document).on('click', '[data-cu-do]', function () {
                        if (window.QSpin) QSpin.show('Удаление…');
                        var $btn = $(this).prop('disabled', true).text('Удаляю…');
                        $.ajax({
                            url: '/api/admin/cleanup', method: 'POST', data: { mode: 'execute' }, dataType: 'json', timeout: 120000,
                            success: function (r) {
                                if (window.QSpin) QSpin.hide();
                                var $box = $('[data-cu-preview]');
                                if (!r || r.status !== 'ok') { $box.html('<div class="text-red-400">Ошибка: ' + ((r && r.message) || 'неизвестно') + '</div>'); return; }
                                var h = '<div class="text-emerald-300 font-medium">Готово: строк БД ' + (r.db_deleted || 0) + ', ключей ' + ((r.panel_deleted || []).length) + '</div>';
                                if ((r.panel_failed || []).length) {
                                    h += '<div class="text-yellow-300">Не удалились (' + r.panel_failed.length + '): ' + r.panel_failed.map(function (f) { return f.key; }).join(', ') + '</div>';
                                }
                                h += '<div><button data-cu-cancel class="text-xs font-medium px-4 py-2 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-300">Закрыть</button></div>';
                                $box.html(h);
                                window.__healthLoaded = false;
                                loadHealth(true);
                            },
                            error: function () { if (window.QSpin) QSpin.hide(); $btn.prop('disabled', false).text('Повторить'); }
                        });
                    });
                    function loadOverview(force) {
                        if (!force && !ovVisible()) return;
                        if (force && window.QSpin) QSpin.show('Обновление…');
                        loadHealth(force);
                        var $btn = $('[data-ov-refresh]');
                        if (force) $btn.find('i').addClass('fa-spin');
                        $.ajax({
                            url: '/api/admin/overview' + (force ? '?fresh=1' : ''), method: 'GET', dataType: 'json', timeout: 60000,
                            success: function (dd) {
                                $btn.find('i').removeClass('fa-spin');
                                if (window.QSpin) QSpin.hide();
                                renderOverview(dd);
                                if (dd && dd.refreshing && !force && !ovRetried) {
                                    ovRetried = true;
                                    setTimeout(function () { loadOverview(false); }, 8000);
                                }
                                if (dd && !dd.refreshing) ovRetried = false;
                            },
                            error: function () { $btn.find('i').removeClass('fa-spin'); if (window.QSpin) QSpin.hide(); ovBadge('red', 'Ошибка загрузки'); }
                        });
                    }
                    $('[data-ov-refresh]').on('click', function () { loadOverview(true); });
                    $(document).on('click', '[data-toggle-section="monitoring"]', function () { loadOverview(true); });
                    setInterval(function () { loadOverview(false); }, 30000);
                    loadOverview(false);
                });
            </script>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="monitoring">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Серверы -->
            <?php if ($groups->isPermission($adminUsername,'servers')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="servers">
                <div class="py-6 flex-col flex md:flex-row justify-between items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">Серверы</h1>
                    <button data-srv-add-open
                        class="text-xs font-medium px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-500 transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i>Добавить сервер
                    </button>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" data-srv-list>
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-6 text-sm text-gray-400">Загрузка…</div>
                </div>
                <!-- Форма добавления/редактирования -->
                <div class="hidden rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-6" data-srv-form-wrap>
                    <h2 class="text-lg font-semibold text-gray-200 mb-4" data-srv-form-title>Новый сервер</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label class="text-xs text-gray-400">Код (латиница, как поддомен)
                            <input data-srv-f="code" placeholder="fi"
                                class="mt-1 w-full font-mono px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                        <label class="text-xs text-gray-400">Страна/название
                            <input data-srv-f="country" placeholder="Finland"
                                class="mt-1 w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                        <label class="text-xs text-gray-400 md:col-span-2">URL панели 3x-ui
                            <input data-srv-f="XUI_URL_PANEL" placeholder="https://fi.example.com:12200/panel_path"
                                class="mt-1 w-full font-mono px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                        <label class="text-xs text-gray-400 md:col-span-2">URL подписок
                            <input data-srv-f="XUI_URL_SUBSCRIPTION" placeholder="https://fi.example.com:1005/sub_path/"
                                class="mt-1 w-full font-mono px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                        <label class="text-xs text-gray-400">VLESS-хост
                            <input data-srv-f="VLESS_SERVER" placeholder="fi.example.com"
                                class="mt-1 w-full font-mono px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                        <label class="text-xs text-gray-400">Номер инбаунда
                            <input data-srv-f="XUI_INBOUND_NUMBER" type="number" value="0" min="0"
                                class="mt-1 w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                        <label class="text-xs text-gray-400">API-токен панели
                            <input data-srv-f="XUI_API_TOKEN" placeholder="(пусто = логин+пароль)"
                                class="mt-1 w-full font-mono px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                        <label class="text-xs text-gray-400">Флаг (файл)
                            <input data-srv-f="flag" placeholder="finland.svg"
                                class="mt-1 w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                        <label class="text-xs text-gray-400">Логин панели
                            <input data-srv-f="XUI_LOGIN" autocomplete="off"
                                class="mt-1 w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                        <label class="text-xs text-gray-400">Пароль панели
                            <input data-srv-f="XUI_PASSWORD" type="password" autocomplete="new-password"
                                class="mt-1 w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm">
                        </label>
                    </div>
                    <div class="flex items-center gap-2 mt-4">
                        <button data-srv-save class="text-xs font-medium px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">Сохранить</button>
                        <button data-srv-cancel class="text-xs font-medium px-4 py-2 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-300 hover:bg-white/10 transition-colors">Отмена</button>
                        <span data-srv-form-msg class="text-xs text-gray-500"></span>
                    </div>
                </div>
                <!-- Удаление с каскадом -->
                <div class="hidden rounded-xl bg-red-500/5 ring-1 ring-red-500/20 p-6 flex-col gap-3" data-srv-del-wrap>
                    <h2 class="text-lg font-semibold text-red-300">Удаление сервера <span data-srv-del-code class="font-mono"></span></h2>
                    <div class="text-sm text-gray-300" data-srv-del-info>Считаю клиентов…</div>
                    <div class="flex flex-wrap items-center gap-2">
                        <select data-srv-del-target class="text-sm bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-gray-200"></select>
                        <button data-srv-del-go class="text-xs font-medium px-4 py-2 rounded-lg bg-red-500/20 ring-1 ring-red-500/40 text-red-200 hover:bg-red-500/30 transition-colors">Перенести и удалить</button>
                        <button data-srv-cancel class="text-xs font-medium px-4 py-2 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-300">Отмена</button>
                    </div>
                    <div class="h-2 rounded-full bg-white/5 overflow-hidden hidden" data-srv-del-progwrap><div data-srv-del-bar class="h-full rounded-full bg-red-400 transition-all" style="width:0%"></div></div>
                    <div class="text-xs text-gray-400" data-srv-del-status></div>
                </div>
            </section>
            <script defer>
                $(document).ready(function () {
                    var srvLoaded = false, srvRaw = {}, srvEditCode = null;
                    function srvVisible() { return $('[data-section="servers"]:not(.hidden)').length > 0; }
                    function srvLoad() {
                        if (srvLoaded || !srvVisible()) return;
                        srvLoaded = true;
                        $.ajax({
                            url: '/api/admin/relocation', method: 'GET', dataType: 'json', timeout: 60000,
                            success: function (d) {
                                srvRaw = d.raw || {};
                                var $w = $('[data-srv-list]').empty();
                                var codes = Object.keys(srvRaw);
                                if (!codes.length) $w.append($('<div>').addClass('text-sm text-gray-500').text('Нет серверов'));
                                codes.forEach(function (code) {
                                    var s = srvRaw[code] || {};
                                    var $c = $('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-5 flex flex-col gap-2');
                                    $c.append($('<div>').addClass('flex items-center justify-between')
                                        .append($('<div>').addClass('font-bold text-white').text((s.country || code) + ' · ' + code))
                                        .append($('<div>').addClass('text-xs text-gray-500 font-mono').attr('data-srv-check', code).text('проверка…')));
                                    $c.append($('<div>').addClass('text-xs text-gray-400 font-mono break-all').text(s.XUI_URL_PANEL || ''));
                                    $c.append($('<div>').addClass('flex flex-wrap gap-2 mt-1')
                                        .append($('<button>').addClass('text-xs px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10').text('Изменить').attr('data-srv-edit', code))
                                        .append($('<button>').addClass('text-xs px-3 py-1.5 rounded-lg bg-red-500/10 ring-1 ring-red-500/25 text-red-300 hover:bg-red-500/20').text('Удалить').attr('data-srv-del', code)));
                                    $w.append($c);
                                    $.ajax({
                                        url: '/api/admin/relocation?action=check&code=' + encodeURIComponent(code), method: 'GET', dataType: 'json', timeout: 30000,
                                        success: function (r) {
                                            var c = (r && r.check) || {};
                                            $('[data-srv-check="' + code + '"]').text(c.ok ? c.note : ('нет: ' + (c.note || ''))).toggleClass('text-green-400', !!c.ok).toggleClass('text-red-400', !c.ok);
                                        }
                                    });
                                });
                            }
                        });
                    }
                    function srvCollect() {
                        var o = {};
                        $('[data-srv-f]').each(function () { o[$(this).attr('data-srv-f')] = $(this).val(); });
                        o.code = (o.code || '').toLowerCase().trim();
                        o.XUI_INBOUND_NUMBER = parseInt(o.XUI_INBOUND_NUMBER || '0', 10) || 0;
                        if (!o.flag) o.flag = 'default.svg';
                        if (!o.XUI_LOGIN_NAME_COOKIE) o.XUI_LOGIN_NAME_COOKIE = 'x-ui';
                        return o;
                    }
                    $('[data-srv-add-open]').on('click', function () {
                        srvEditCode = null;
                        $('[data-srv-form-title]').text('Новый сервер');
                        $('[data-srv-f]').val('');
                        $('[data-srv-f="XUI_INBOUND_NUMBER"]').val('0');
                        $('[data-srv-form-wrap]').removeClass('hidden');
                        $('[data-srv-del-wrap]').addClass('hidden').removeClass('flex');
                    });
                    $(document).on('click', '[data-srv-edit]', function () {
                        var code = $(this).attr('data-srv-edit'), s = srvRaw[code] || {};
                        srvEditCode = code;
                        $('[data-srv-form-title]').text('Сервер ' + code);
                        $('[data-srv-f]').each(function () {
                            var k = $(this).attr('data-srv-f');
                            $(this).val(k === 'code' ? code : (s[k] ?? ''));
                        });
                        $('[data-srv-form-wrap]').removeClass('hidden');
                        $('[data-srv-del-wrap]').addClass('hidden').removeClass('flex');
                    });
                    $('[data-srv-cancel]').on('click', function () {
                        $('[data-srv-form-wrap]').addClass('hidden');
                        $('[data-srv-del-wrap]').addClass('hidden').removeClass('flex');
                    });
                    $('[data-srv-save]').on('click', function () {
                        var o = srvCollect();
                        if (!/^[a-z0-9]{1,16}$/.test(o.code)) { $('[data-srv-form-msg]').text('Код: 1-16 символов латиницы/цифр'); return; }
                        if (!o.XUI_URL_PANEL || !o.VLESS_SERVER) { $('[data-srv-form-msg]').text('Нужны URL панели и VLESS-хост'); return; }
                        var all = $.extend(true, {}, srvRaw);
                        if (srvEditCode && srvEditCode !== o.code) delete all[srvEditCode];
                        delete o.code;
                        all[o.code] = o;
                        $('[data-srv-form-msg]').text('Сохраняю…');
                        $.ajax({
                            url: '/api/admin/relocation', method: 'POST',
                            data: { action: 'save_servers', servers: JSON.stringify(all) },
                            dataType: 'json', timeout: 30000,
                            success: function (r) {
                                $('[data-srv-form-msg]').text(r.status === 'ok' ? r.message : ('Ошибка: ' + (r.message || '')));
                                if (r.status === 'ok') { srvLoaded = false; srvLoad(); $('[data-srv-form-wrap]').addClass('hidden'); }
                            }
                        });
                    });
                    function srvDelTick(from, target) {
                        $.ajax({
                            url: '/api/admin/relocation', method: 'POST',
                            data: { action: 'migrate_from', from: from, target: target },
                            dataType: 'json', timeout: 120000,
                            success: function (r) {
                                if (r.status !== 'ok') { if (window.QSpin) QSpin.hide(); $('[data-srv-del-status]').text('Ошибка: ' + (r.message || '')); return; }
                                $('[data-srv-del-bar]').css('width', '100%');
                                if ((r.failed || []).length) {
                                    if (window.QSpin) QSpin.hide(); $('[data-srv-del-status]').text('Осталось ' + r.rest + ', ошибок ' + r.failed.length + ' — разбираем вручную, удаление заблокировано');
                                    return;
                                }
                                if (r.rest > 0) {
                                    $('[data-srv-del-status]').text('Перенесено, осталось ' + r.rest + '…');
                                    srvDelTick(from, target);
                                    return;
                                }
                                $.ajax({
                                    url: '/api/admin/relocation', method: 'POST',
                                    data: { action: 'delete_server', code: from, target: target },
                                    dataType: 'json', timeout: 30000,
                                    success: function (d2) {
                                        if (window.QSpin) QSpin.hide(); $('[data-srv-del-status]').text(d2.status === 'ok' ? d2.message : ('Ошибка: ' + (d2.message || '')));
                                        if (d2.status === 'ok') { srvLoaded = false; srvLoad(); }
                                    }
                                });
                            },
                            error: function () { if (window.QSpin) QSpin.hide(); $('[data-srv-del-status]').text('Сеть упала — жми «Перенести и удалить» ещё раз (продолжит)'); }
                        });
                    }
                    $(document).on('click', '[data-srv-del]', function () {
                        var code = $(this).attr('data-srv-del');
                        var $wrap = $('[data-srv-del-wrap]').removeClass('hidden').addClass('flex');
                        $('[data-srv-form-wrap]').addClass('hidden');
                        $('[data-srv-del-code]').text(code);
                        $('[data-srv-del-info]').text('Считаю клиентов…');
                        var $sel = $('[data-srv-del-target]').empty();
                        Object.keys(srvRaw).forEach(function (c) {
                            if (c !== code) $sel.append($('<option>').attr('value', c).text(c));
                        });
                        if (Object.keys(srvRaw).length < 2) {
                            $('[data-srv-del-info]').text('Нельзя удалить последний сервер — добавьте сначала новый.');
                            return;
                        }
                        $.ajax({
                            url: '/api/admin/relocation?action=server_clients&code=' + encodeURIComponent(code),
                            method: 'GET', dataType: 'json', timeout: 60000,
                            success: function (cc) {
                                $('[data-srv-del-info]').html('На сервере <b>' + (cc.clients ?? 0) + '</b> активных. Перенос пойдёт на <b>' + $sel.val() + '</b>.');
                            }
                        });
                        $sel.off('change.srvdel').on('change.srvdel', function () {
                            $('[data-srv-del-info]').html('Перенос пойдёт на <b>' + $sel.val() + '</b>.');
                        });
                    });
                    $(document).on('click', '[data-srv-del-go]', function () {
                        var code = $('[data-srv-del-code]').text(), target = $('[data-srv-del-target]').val();
                        if (!confirm('Удалить сервер ' + code + ' после переноса всех на ' + target + '?')) return;
                        $('[data-srv-del-progwrap]').removeClass('hidden');
                        $('[data-srv-del-status]').text('Перенос…');
                        if (window.QSpin) QSpin.show('Перенос клиентов…');
                        srvDelTick(code, target);
                    });
                    $(document).on('click', '[data-toggle-section="servers"]', function () { setTimeout(srvLoad, 350); });
                    srvLoad();
                });
            </script>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="servers">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Переезд -->
            <?php if ($groups->isPermission($adminUsername,'relocation')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="relocation">
                <div class="py-6 flex-col flex md:flex-row justify-between items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">Переезд: серверы и домены</h1>
                    <span class="text-xs text-gray-500">аварийный центр при блокировках</span>
                </div>

                <!-- Серверы -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                    <h2 class="text-lg font-semibold text-gray-300 mb-1">Серверы</h2>
                    <p class="text-xs text-gray-400 mb-4">Реестр живёт в servers.json — правится без деплоя, предыдущая версия копируется в .bak</p>
                    <div class="overflow-x-auto mb-4">
                        <table class="w-full text-sm text-gray-300">
                            <thead><tr class="text-left text-gray-400 text-xs">
                                <th class="py-1 pr-3">Код</th><th class="py-1 pr-3">Страна</th><th class="py-1 pr-3">Панель</th><th class="py-1 pr-3">Связь</th><th></th>
                            </tr></thead>
                            <tbody data-rl-servers><tr><td colspan="5" class="py-3 text-gray-400">Загрузка…</td></tr></tbody>
                        </table>
                    </div>
                    <details>
                        <summary class="text-sm text-gray-500 cursor-pointer hover:text-gray-300">Редактировать JSON реестра</summary>
                        <textarea data-rl-json rows="8" spellcheck="false"
                            class="mt-2 w-full font-mono text-xs border border-white/10 rounded-lg p-3 bg-white/5 text-gray-100" placeholder="…"></textarea>
                        <div class="flex items-center gap-2 mt-2">
                            <button data-rl-save class="text-xs font-medium px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">Сохранить реестр</button>
                            <span data-rl-save-msg class="text-xs text-gray-500"></span>
                        </div>
                    </details>
                </div>

                <!-- Массовая миграция -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                    <h2 class="text-lg font-semibold text-gray-300 mb-1">Перенос клиентов на другой сервер</h2>
                    <p class="text-xs text-gray-400 mb-4">Безопасный порядок: сначала ключ на новом, потом удаление старого. При сбое — откат, клиент ничего не заметит</p>
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <select data-rl-target class="text-sm border border-white/10 rounded-lg px-3 py-2 text-gray-100"></select>
                        <button data-rl-preview class="text-xs font-medium px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-gray-300 transition-colors">Посчитать</button>
                        <button data-rl-start class="text-xs font-medium px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors">Начать перенос</button>
                        <button data-rl-retry class="hidden text-xs font-medium px-4 py-2 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 transition-colors">Повторить ошибки</button>
                    </div>
                    <div data-rl-preview class="text-sm text-gray-400 mb-2"></div>
                    <div class="hidden" data-rl-progress>
                        <div class="h-2 rounded-full bg-white/10 overflow-hidden mb-1"><div data-rl-bar class="h-full rounded-full bg-green-500 transition-all" style="width:0%"></div></div>
                        <div class="text-xs text-gray-500" data-rl-status></div>
                        <div class="text-xs text-red-500 mt-1 flex flex-col gap-0.5" data-rl-failed></div>
                    </div>
                </div>

                <!-- Домены -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                    <h2 class="text-lg font-semibold text-gray-300 mb-1">Домены и адреса</h2>
                    <p class="text-xs text-gray-400 mb-4">Сайт, оплаты и рефссылки строятся от текущего домена сами. Вручную переключаются только серверы (выше) и БД</p>
                    <div data-rl-exposure class="text-xs mb-3"></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-300">
                            <thead><tr class="text-left text-gray-400 text-xs">
                                <th class="py-1 pr-3">Что</th><th class="py-1 pr-3">Значение</th><th class="py-1 pr-3">Комментарий</th>
                            </tr></thead>
                            <tbody data-rl-domains><tr><td colspan="3" class="py-3 text-gray-400">Загрузка…</td></tr></tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-xs text-gray-500 bg-white/5 rounded-lg p-3">
                        Смена БД: в <b>.env</b> поменять <b>DB_HOST</b> (и при необходимости DB_NAME/DB_USERNAME/DB_PASSWORD), затем рестарт php.
                        Клиентам после смены домена серверов нужен <b>refresh подписки</b> в приложении — разошлите уведомление.
                    </div>
                    <div class="mt-3 rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                        <h3 class="font-semibold text-gray-200 mb-2">Готовность к блокировкам и атакам</h3>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <a href="/api/admin/backup-db"
                                class="text-xs font-medium px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">
                                <i class="fa-solid fa-download mr-1"></i>Скачать бэкап БД (.sql)
                            </a>
                            <span class="text-xs text-gray-500">структура — setting/Schema/schema.sql, данные — этим файлом</span>
                        </div>
                        <ul class="text-xs text-gray-400 flex flex-col gap-1 list-disc pl-5">
                            <li>Входы и почта под троттлингом (5–10 попыток / 15 мин), неудачные входы пишутся в аудит</li>
                            <li>Зеркало домена держите готовым в servers.json — перенос клиентов тиками из раздела выше</li>
                            <li>Для DDoS — CDN/WAF перед сайтом (Cloudflare): тогда же заработает реальный IP в троттлинге</li>
                            <li>Тяжёлые выгрузки (PDF таблиц) ограничены 300 строками</li>
                        </ul>
                    </div>
                </div>
            </section>
            <script defer>
                $(document).ready(function () {
                    var rlLoaded = false;
                    function rlVisible() { return $('[data-section="relocation"]:not(.hidden)').length > 0; }
                    function rlGet(url, ok) {
                        $.ajax({ url: url, method: 'GET', dataType: 'json', timeout: 60000, success: ok });
                    }
                    function rlLoad() {
                        if (rlLoaded || !rlVisible()) return;
                        rlLoaded = true;
                        rlGet('/api/admin/relocation?action=servers', function (d) {
                            var $t = $('[data-rl-servers]').empty(), $sel = $('[data-rl-target]').empty();
                            (d.servers || []).forEach(function (s) {
                                $t.append($('<tr>').addClass('border-t border-white/5')
                                    .append($('<td>').addClass('py-2 pr-3 font-mono font-bold').text(s.code))
                                    .append($('<td>').addClass('py-2 pr-3').text(s.country))
                                    .append($('<td>').addClass('py-2 pr-3 font-mono text-xs').text(s.panel || s.host))
                                    .append($('<td>').addClass('py-2 pr-3 text-xs').attr('data-rl-check', s.code).text('…'))
                                    .append($('<td>').addClass('py-2').append($('<button>').addClass('text-xs text-blue-400 hover:underline').text('проверить').attr('data-rl-checkbtn', s.code))));
                                $sel.append($('<option>').attr('value', s.code).text(s.code + ' · ' + s.country));
                            });
                            if (d.raw) $('[data-rl-json]').val(JSON.stringify(d.raw, null, 2));
                            (d.servers || []).forEach(function (s) { rlCheck(s.code); });
                        });
                        rlGet('/api/admin/relocation?action=domains', function (d) {
                            var $t = $('[data-rl-domains]').empty();
                            (d.domains || []).forEach(function (r) {
                                var dot = r.ok === true ? 'bg-green-500' : (r.ok === false ? 'bg-red-500' : 'bg-gray-300');
                                $t.append($('<tr>').addClass('border-t border-white/5')
                                    .append($('<td>').addClass('py-2 pr-3').html('<span class="inline-block w-2 h-2 rounded-full ' + dot + ' mr-1"></span>' + $('<div>').text(r.name).html()))
                                    .append($('<td>').addClass('py-2 pr-3 font-mono text-xs break-all').text(r.value || '—'))
                                    .append($('<td>').addClass('py-2 pr-3 text-xs text-gray-500').text((r.note || '') + '')));
                            });
                            var $e = $('[data-rl-exposure]').empty(), x = d.exposure || {};
                            if (x.exposed) $e.html('<span class="inline-block px-3 py-1.5 rounded-lg bg-red-500/10 ring-1 ring-red-500/30 text-red-400 font-medium">ОПАСНО: нет deny-правил для секретов! ' + $('<div>').text(x.note || '').html() + '</span>');
                            else if (x.checked) $e.html('<span class="inline-block px-3 py-1.5 rounded-lg bg-green-500/10 ring-1 ring-green-500/30 text-green-400">deny-правила на месте' + (x.note ? ' · ' + $('<div>').text(x.note).html() : '') + '</span><div class="mt-2 text-gray-500 font-mono text-[11px]">nginx: location ~ ^/(setting|app|vendor)/ { deny all; } + location = /.env { deny all; }</div>');
                        });
                    }
                    function rlCheck(code) {
                        var $c = $('[data-rl-check="' + code + '"]');
                        $c.text('…');
                        rlGet('/api/admin/relocation?action=check&code=' + encodeURIComponent(code), function (d) {
                            var c = d.check || {};
                            $c.text(c.ok ? c.note : ('нет: ' + (c.note || '')));
                            $c.toggleClass('text-green-400', !!c.ok).toggleClass('text-red-400', !c.ok);
                        });
                    }
                    $(document).on('click', '[data-rl-checkbtn]', function () { rlCheck($(this).attr('data-rl-checkbtn')); });
                    $('[data-rl-preview]').on('click', function () {
                        var t = $('[data-rl-target]').val();
                        $('[data-rl-preview]').text('Считаю…');
                        rlGet('/api/admin/relocation?action=preview&target=' + encodeURIComponent(t), function (p) {
                            if (p.status !== 'ok') { $('[data-rl-preview]').text('Ошибка: ' + (p.message || '')); return; }
                            $('[data-rl-preview]').html('На <b>' + t + '</b>: перенести <b>' + p.move + '</b>, уже там ' + p.already + '. Панель цели: ' + (p.target_ok ? '<b class="text-green-400">доступна</b>' : '<b class="text-red-400">НЕДОСТУПНА</b> (' + (p.target_note || '') + ')'));
                        });
                    });
                    function rlPollTick() {
                        $.ajax({
                            url: '/api/admin/relocation', method: 'POST', data: { action: 'tick' }, dataType: 'json', timeout: 120000,
                            success: function (r) {
                                var st = r.state || {};
                                var total = st.total || 0, done = st.done || 0;
                                $('[data-rl-bar]').css('width', total ? (done / total * 100) : 0 + '%');
                                $('[data-rl-status]').text('Готово ' + done + ' из ' + total + ' · успешно ' + (st.ok || 0));
                                var $f = $('[data-rl-failed]').empty();
                                (st.failed || []).forEach(function (f) { $f.append($('<div>').text((f.email || f.uniID) + ': ' + (f.error || ''))); });
                                if (st.status === 'running') setTimeout(rlPollTick, 3000);
                                else {
                                    $('[data-rl-status]').append(document.createTextNode(st.status === 'done' ? ' · ЗАВЕРШЕНО' : ' · завершено с ошибками'));
                                    if ((st.failed || []).length) $('[data-rl-retry]').removeClass('hidden');
                                }
                            },
                            error: function () { setTimeout(rlPollTick, 5000); }
                        });
                    }
                    $('[data-rl-start]').on('click', function () {
                        if (!confirm('Перенести клиентов на ' + $('[data-rl-target]').val() + '?')) return;
                        $('[data-rl-progress]').removeClass('hidden');
                        $('[data-rl-retry]').addClass('hidden');
                        $.ajax({
                            url: '/api/admin/relocation', method: 'POST', data: { action: 'start', target: $('[data-rl-target]').val() }, dataType: 'json', timeout: 60000,
                            success: function (r) {
                                if (r.status !== 'ok') { $('[data-rl-status]').text('Ошибка: ' + (r.message || '')); return; }
                                rlPollTick();
                            }
                        });
                    });
                    $('[data-rl-retry]').on('click', function () {
                        $.ajax({
                            url: '/api/admin/relocation', method: 'POST', data: { action: 'retry' }, dataType: 'json', timeout: 30000,
                            success: function () { $('[data-rl-retry]').addClass('hidden'); rlPollTick(); }
                        });
                    });
                    $('[data-rl-save]').on('click', function () {
                        if (window.QSpin) QSpin.show('Сохранение…');
                        var $m = $('[data-rl-save-msg]').text('Сохраняю…');
                        $.ajax({
                            url: '/api/admin/relocation', method: 'POST',
                            data: { action: 'save_servers', servers: $('[data-rl-json]').val() },
                            dataType: 'json', timeout: 30000,
                            success: function (r) { if (window.QSpin) QSpin.hide(); $m.text(r.status === 'ok' ? r.message : ('Ошибка: ' + (r.message || ''))); },
                            error: function () { if (window.QSpin) QSpin.hide(); }
                        });
                    });
                    $(document).on('click', '[data-toggle-section="relocation"]', function () { setTimeout(rlLoad, 350); });
                    rlLoad();
                });
            </script>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="relocation">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Графики -->
            <?php if ($groups->isPermission($adminUsername,'charts')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="charts">
                <!-- Заголовок -->
                <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
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
                        <h3 class="text-lg font-semibold text-gray-300 mb-4">Статистика пользователей по месяцам</h3>
                        <canvas data-chart="chart_users_monthly"></canvas>
                    </div>
                </div>

                <!-- Финансовая статистика (только для админов) -->
                <?php if ($adminUser->hasRole($adminID, 'admin')): ?>
                    <div class="py-8">
                        <h2 class="text-lg font-semibold text-gray-300 mb-4">Финансовая статистика</h2>



                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                                <div class="text-sm text-green-400 mb-1">Выручка брутто, 30 дн (касса)</div>
                                <div class="text-3xl font-bold text-green-400">
                                    <span data-kr="gross">…</span> ₽
                                </div>
                            </div>
                            <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                                <div class="text-sm text-blue-400 mb-1">Возвраты, 30 дн (касса)</div>
                                <div class="text-3xl font-bold text-blue-400">
                                    <span data-kr="refunds">…</span> ₽
                                </div>
                            </div>
                            <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                                <div class="text-sm text-green-400 mb-1">Чистая, 30 дн (касса)</div>
                                <div class="text-3xl font-bold text-green-400">
                                    <span data-kr="clean">…</span> ₽
                                </div>
                            </div>
                            <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                                <div class="text-sm text-orange-400 mb-1">Средний чек (касса)</div>
                                <div class="text-3xl font-bold text-orange-400">
                                    <span data-kr="avg_check">…</span> ₽
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- График прибыли по тарифам -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-300 mb-4">Прибыль по тарифам</h2>
                        <div class="mb-8 h-[400px]">
                            <canvas id="chart_plans"></canvas>
                        </div>
                        <script defer>
                            // Строится лениво из /api/admin/stats при первом открытии раздела графиков
                            function buildPlansChart(byTariff, fallbackPlan) {
                            const $plansCtx = $('#chart_plans');
                            if ($plansCtx.length && !window.__plansBuilt) {
                                const rows = (byTariff && byTariff.length) ? byTariff : ((fallbackPlan && fallbackPlan.revenueByPlan) || []);
                                const labels = rows.map(function (r) { return window.tariffName ? window.tariffName(r.tariff || r.plan) : (r.tariff || r.plan); });
                                const data = rows.map(function (r) { return r.revenue; });

                                if (labels.length > 0 && data.length > 0) {
                                    window.__plansBuilt = true;
                                    new Chart($plansCtx[0], {
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
                                    $plansCtx.parent().html('<div class="text-center text-gray-500 py-8">Нет данных для отображения</div>');
                                }
                            }
                            }
                        </script>
                    </div>
                <?php endif; ?>
            </section>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="charts">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Цены -->
            <?php if ($groups->isPermission($adminUsername,'price')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="price">
                <!-- Заголовок -->
                <div class="py-6 flex-col flex md:flex-row md:items-center justify-between gap-2">
                    <h1 class="text-2xl font-bold text-gray-100">
                        Настройка цен
                    </h1>
                    <p class="text-sm text-gray-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-code text-white"></i>
                        Единый объект тарифов PriceConfig.php — применяется сразу
                    </p>
                </div>

                <div class="bg-[#16181d] border border-white/10 rounded-2xl overflow-hidden">
                    <form action="/admin/save" method="POST">
                        <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                        <input type="hidden" name="table" value="price_config">

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px] text-sm table-fixed">
                                <thead>
                                    <tr class="bg-white/5 border-b border-white/10">
                                        <th class="w-44 text-left px-4 py-3.5 font-semibold text-gray-400">
                                            Тариф
                                            <span class="block text-[11px] font-normal text-gray-400 mt-0.5">лимит устройств</span>
                                        </th>
                                        <?php foreach ($periods as $months => $period): ?>
                                            <th class="px-3 py-3.5 text-center font-semibold text-gray-400 whitespace-nowrap">
                                                <span class="text-base"><?= $months ?> мес</span>
                                                <span class="block text-[11px] font-normal text-gray-400 mt-0.5"><?= $period['days'] ?> дней · ₽/мес</span>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tariffConfig as $tariffName => $tariff): ?>
                                        <?php
                                        $accents = $tariffAccents[$tariffName] ?? $defaultAccent;
                                        $devices = (int) $tariff['devices'];
                                        $devWord = $devices === 1 ? 'устройство' : ($devices < 5 ? 'устройства' : 'устройств');
                                        ?>
                                        <tr class="border-b border-white/10 last:border-b-0 hover:bg-white/5/60 transition-colors <?= $accents['accent'] ?> border-l-4">
                                            <td class="px-4 py-4">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg font-bold uppercase tracking-wide text-xs <?= $accents['badge'] ?>">
                                                    <?= htmlspecialchars($tariffName) ?>
                                                </span>
                                                <div class="text-xs text-gray-500 mt-1.5">
                                                    <span class="text-gray-300 font-medium"><?= htmlspecialchars($tariff['label']) ?></span>
                                                    · <?= $devices ?> <?= $devWord ?>
                                                </div>
                                            </td>
                                            <?php foreach ($tariff['periods'] as $months => $period): ?>
                                                <td class="px-3 py-3 align-top">
                                                    <div data-cell="<?= htmlspecialchars($tariffName) ?>-<?= $months ?>" class="rounded-lg p-2 -m-1 transition">
                                                        <div class="flex items-center justify-between mb-1.5 px-0.5">
                                                            <span class="text-[11px] text-gray-400 uppercase tracking-wide">сейчас</span>
                                                            <span class="text-xs font-medium text-gray-400 line-through"><?= $period['price'] ?> ₽</span>
                                                        </div>
                                                        <div class="relative">
                                                            <input type="number"
                                                                name="price[<?= htmlspecialchars($tariffName) ?>][<?= $months ?>]"
                                                                placeholder="<?= $period['price'] ?>"
                                                                data-input="<?= htmlspecialchars($tariffName) ?>-<?= $months ?>"
                                                                data-current="<?= $period['price'] ?>"
                                                                class="w-full px-3 py-2 pr-7 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm"
                                                                min="0">
                                                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">₽</span>
                                                        </div>
                                                        <div class="mt-1.5 flex items-center justify-between px-0.5 h-5">
                                                            <span data-visual class="hidden text-xs font-semibold text-green-400"></span>
                                                            <span data-changed class="hidden text-[10px] font-bold uppercase tracking-wide text-green-400 bg-green-500/10 px-1.5 py-0.5 rounded">изменено</span>
                                                        </div>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Мобильная версия (карточки тарифов) -->
                        <div class="md:hidden flex flex-col gap-3 p-3">
                            <?php foreach ($tariffConfig as $tariffName => $tariff): ?>
                                <?php
                                $accents = $tariffAccents[$tariffName] ?? $defaultAccent;
                                $devices = (int) $tariff['devices'];
                                $devWord = $devices === 1 ? 'устройство' : ($devices < 5 ? 'устройства' : 'устройств');
                                ?>
                                <div class="border border-white/10 rounded-2xl overflow-hidden <?= $accents['accent'] ?> border-l-4">
                                    <div class="px-4 py-2.5 bg-white/5 flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg font-bold uppercase tracking-wide text-xs <?= $accents['badge'] ?>">
                                            <?= htmlspecialchars($tariffName) ?>
                                        </span>
                                        <span class="text-xs text-gray-500 truncate">
                                            <span class="text-gray-300 font-medium"><?= htmlspecialchars($tariff['label']) ?></span>
                                            · <?= $devices ?> <?= $devWord ?>
                                        </span>
                                    </div>
                                    <div class="divide-y divide-border">
                                        <?php foreach ($tariff['periods'] as $months => $period): ?>
                                            <div data-cell="<?= htmlspecialchars($tariffName) ?>-<?= $months ?>"
                                                class="px-4 py-2.5 flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-gray-100 text-sm"><?= $months ?> мес</div>
                                                    <div class="text-xs text-gray-400">
                                                        <?= $period['days'] ?> дней · сейчас <s><?= $period['price'] ?> ₽</s>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <div class="relative w-28">
                                                        <input type="number"
                                                            name="price_m[<?= htmlspecialchars($tariffName) ?>][<?= $months ?>]"
                                                            placeholder="<?= $period['price'] ?>"
                                                            data-input="<?= htmlspecialchars($tariffName) ?>-<?= $months ?>"
                                                            data-current="<?= $period['price'] ?>"
                                                            class="w-full px-3 py-1.5 pr-7 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none text-sm"
                                                            min="0">
                                                        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">₽</span>
                                                    </div>
                                                    <span data-visual class="hidden text-xs font-semibold text-green-400 whitespace-nowrap"></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Нижняя панель -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3.5 bg-white/5 border-t border-white/10">
                            <div class="text-sm text-gray-500 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-white"></i>
                                Изменения применяются сразу · изменено:
                                <span id="changes-count" class="font-bold text-white">0</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="reset"
                                    class="px-4 py-2 rounded-lg border-dashed border-gray-400/60 border-2 text-gray-400 hover:bg-white/20 transition-colors font-semibold text-sm focus:outline-none">
                                    <i class="fa-solid fa-rotate-left mr-1.5"></i>Сбросить
                                </button>
                                <button type="submit"
                                    class="px-5 py-2 rounded-lg bg-primary border-dashed border-green-500/60 border-2 hover:bg-green-500/60 transition-colors font-semibold text-sm focus:outline-none">
                                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Сохранить цены
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <script defer>
                    $(function () {
                        function updateCount() {
                            var n = 0;
                            var seen = {};
                            $('[data-input]').each(function () {
                                var key = $(this).attr('data-input');
                                var val = $(this).val().trim();
                                var changed = val !== '' && parseInt(val) !== parseInt($(this).attr('data-current'));
                                if (changed && !seen[key]) {
                                    seen[key] = true;
                                    n++;
                                }
                            });
                            $('#changes-count').text(n);
                        }

                        $('[data-input]').on('input', function () {
                            var $cell = $('[data-cell="' + $(this).attr('data-input') + '"]');
                            var val = $(this).val().trim();
                            var current = $(this).attr('data-current');
                            var changed = val !== '' && parseInt(val) !== parseInt(current);

                            $cell.toggleClass('ring-2 ring-green-400/60 bg-green-500/10/40', changed);
                            $cell.find('[data-changed]').toggleClass('hidden', !changed);

                            var $vis = $cell.find('[data-visual]');
                            if (val === '') {
                                $vis.addClass('hidden').text('');
                            } else {
                                $vis.removeClass('hidden').text('→ ' + val + ' ₽');
                            }
                            updateCount();
                        });

                        $('form').on('reset', function () {
                            $('[data-cell]').removeClass('ring-2 ring-green-400/60 bg-green-500/10/40');
                            $('[data-changed]').addClass('hidden');
                            $('[data-visual]').addClass('hidden').text('');
                            updateCount();
                        });

                        updateCount();
                    });
                </script>
            </section>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="charts">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Прибыль -->
            <?php if ($groups->isPermission($adminUsername,'profit')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="profit">
                <div class="py-6 flex-col flex md:flex-row justify-between items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                        Показатели прибыли
                    </h1>
                    <div class="flex items-center gap-2">
                        <span data-pf-source class="text-xs text-gray-500"></span>
                        <div class="flex rounded-lg overflow-hidden ring-1 ring-white/10 text-xs font-medium">
                            <button data-pf-days="30" class="px-3 py-1.5 bg-blue-600 text-white">30д</button>
                            <button data-pf-days="90" class="px-3 py-1.5 bg-white/5 text-gray-300 hover:bg-white/10">90д</button>
                            <button data-pf-days="365" class="px-3 py-1.5 bg-white/5 text-gray-300 hover:bg-white/10">год</button>
                        </div>
                        <input type="month" data-pf-month title="Конкретный месяц"
                            class="text-xs bg-white/5 border border-white/10 rounded-lg px-2 py-1.5 text-gray-300 focus:outline-none focus:border-blue-500">
                        <button data-pf-refresh
                            class="text-xs font-medium px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10 transition-colors">
                            <i class="fa-solid fa-rotate-right mr-1"></i>Обновить
                        </button>
                    </div>
                </div>

                <!-- KPI как в кассе -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="text-xs text-gray-500 mb-1">Выручка</div>
                        <div class="text-2xl font-bold text-emerald-400" data-pf="gross">…</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="text-xs text-gray-500 mb-1">Сумма возвратов</div>
                        <div class="text-2xl font-bold text-gray-100" data-pf="refunds">…</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="text-xs text-gray-500 mb-1">Сумма комиссий</div>
                        <div class="text-2xl font-bold text-gray-100" data-pf="commission">…</div>
                        <div class="text-[11px] text-gray-500 mt-0.5" data-pf-sub="commission"></div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="text-xs text-gray-500 mb-1">Средний чек</div>
                        <div class="text-2xl font-bold text-gray-100" data-pf="avg">…</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="text-xs text-gray-500 mb-1">Количество платежей</div>
                        <div class="text-2xl font-bold text-gray-100" data-pf="count">…</div>
                    </div>
                </div>

                <!-- Дневной график -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                    <div class="flex items-baseline justify-between mb-2">
                        <h3 class="font-semibold text-gray-300">Выручка по дням</h3>
                        <span class="text-xs text-gray-500" data-pf-sub="mrr"></span>
                    </div>
                    <div class="h-56"><canvas id="pf-daily" style="width:100%;height:100%"></canvas></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <h3 class="font-semibold text-gray-300 mb-3">По способам оплаты</h3>
                        <div class="flex flex-col gap-1.5 text-sm" data-pf-methods><span class="text-gray-500">…</span></div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <h3 class="font-semibold text-gray-300 mb-3">По тарифам</h3>
                        <div class="flex flex-col gap-1.5 text-sm" data-pf-tariffs><span class="text-gray-500">…</span></div>
                    </div>
                </div>

                <!-- История платежей -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-300">История платежей</h3>
                        <div class="flex items-center gap-2">
                            <span data-roi-import-msg class="text-xs text-gray-500"></span>
                            <input data-receipt-search type="text" spellcheck="false" autocomplete="off" placeholder="ID платежа…" class="w-52 md:w-64 font-mono text-xs px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 placeholder:text-gray-600 focus:outline-none focus:ring-white/25">
                            <button data-receipt-find title="Найти чек по ID" class="text-xs font-medium px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-300 hover:bg-white/10 transition-colors"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <button data-roi-import class="text-xs font-medium px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-300 hover:bg-white/10 transition-colors">Импорт чеков (90 дн.)</button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 text-sm max-h-72 overflow-y-auto" data-pf-recent><span class="text-gray-500">…</span></div>
                </div>
            </section>
            <script defer>
                $(document).ready(function () {
                    var pfDays = 30, pfCache = {}, pfLoading = false, pfMonth = null;
                    function pfUrl() {
                        if (pfMonth) {
                            var y = parseInt(pfMonth.slice(0, 4), 10), m = parseInt(pfMonth.slice(5, 7), 10);
                            var last = new Date(y, m, 0).getDate();
                            var p = function (n) { return (n < 10 ? '0' : '') + n; };
                            return '/api/admin/roi?from=' + y + '-' + p(m) + '-01&to=' + y + '-' + p(m) + '-' + last;
                        }
                        return '/api/admin/roi?days=' + pfDays;
                    }
                    function pfKey() { return pfMonth ? 'm' + pfMonth : 'd' + pfDays; }
                    function pfMoney(v) { return Number(v || 0).toLocaleString('ru-RU', { maximumFractionDigits: v < 100 ? 2 : 0 }) + '₽'; }
                    function pfPlural(n, one, few, many) {
                        n = Math.abs(parseInt(n, 10) || 0) % 100;
                        var d = n % 10;
                        if (n > 10 && n < 20) return many;
                        if (d > 1 && d < 5) return few;
                        if (d === 1) return one;
                        return many;
                    }
                    function pfTariff(code) {
                        return window.tariffName ? window.tariffName(code) : String(code || '—');
                    }
                    function pfVisible() { return $('[data-section="profit"]:not(.hidden)').length > 0; }
                    function pfRender(r) {
                        if (!r || r.status !== 'ok') return;
                        var avg = (r.succeeded || 0) > 0 ? (r.gross || 0) / r.succeeded : 0;
                        $('[data-pf="gross"]').text(pfMoney(r.gross));
                        $('[data-pf="refunds"]').text(pfMoney(r.refunds));
                        $('[data-pf="commission"]').text(pfMoney(r.commission));
                        $('[data-pf-sub="commission"]').text(r.commission_source === 'kassa' ? 'фактическая из кассы' : 'по ставке ' + ((r.commission_rate || 0) * 100) + '%');
                        $('[data-pf="avg"]').text(pfMoney(avg));
                        $('[data-pf="count"]').text(r.succeeded || 0);
                        $('[data-pf-source]').text('источник: ' + (r.source === 'kassa' ? 'ЮKassa' : (r.source === 'ledger' ? 'леджер' : 'БД')) + (r.cached ? ' · кэш' : '') + (r.range ? ' · ' + r.range : ''));
                        $('[data-pf-sub="mrr"]').text('MRR ' + pfMoney((r.clean || 0) / (r.days / 30)));
                        var el = document.getElementById('pf-daily');
                        if (el && typeof Chart !== 'undefined') {
                            if (window.__pfDaily) { try { window.__pfDaily.destroy(); } catch (e) {} }
                            var pts = r.daily || [];
                            window.__pfDaily = new Chart(el, {
                                type: 'line',
                                data: {
                                    labels: pts.map(function (p) { return p.day; }),
                                    datasets: [{ label: 'Выручка', data: pts.map(function (p) { return p.revenue; }), borderColor: '#34d399', borderWidth: 2, pointRadius: 0, pointHoverRadius: 4, tension: 0.4, fill: true, backgroundColor: 'rgba(52,211,153,0.12)' }]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false, animation: false,
                                    interaction: { mode: 'index', intersect: false },
                                    plugins: { legend: { display: false }, tooltip: { enabled: true, backgroundColor: '#0d1117', bodyColor: '#e6e9ee', titleColor: '#9aa4b2', callbacks: { title: function (items) { return window.fmtDayRu ? window.fmtDayRu(items[0].label) : items[0].label; }, label: function (c) { return ' ' + Number(c.parsed.y || 0).toLocaleString('ru-RU') + ' ₽'; } } } },
                                    scales: { x: { ticks: { color: '#6b7280', maxTicksLimit: 12, callback: function (v) { var l = this.getLabelForValue(v); return String(l || '').slice(5); } }, grid: { display: false } }, y: { beginAtZero: true, ticks: { color: '#9aa4b2' }, grid: { color: 'rgba(255,255,255,0.06)' } } }
                                }
                            });
                        }
                        var $m = $('[data-pf-methods]').empty(), methods = r.by_method || {}, mcnt = r.by_method_cnt || {}, mk = Object.keys(methods);
                        if (!mk.length) $m.append($('<span>').addClass('text-gray-500').text('—'));
                        mk.forEach(function (k) {
                            var n = mcnt[k] || 0;
                            $m.append($('<div>').addClass('flex items-center gap-2')
                                .append($('<span>').addClass('flex items-center gap-1.5').html(window.payMethodHtml ? window.payMethodHtml(k) : k))
                                .append($('<span>').addClass('ml-auto text-gray-400 text-xs').text(pfMoney(methods[k]) + ' · ' + n + ' ' + pfPlural(n, 'оплата', 'оплаты', 'оплат'))));
                        });
                        var $t = $('[data-pf-tariffs]').empty();
                        if (!(r.by_tariff || []).length) $t.append($('<span>').addClass('text-gray-500').text('нет данных'));
                        (r.by_tariff || []).forEach(function (row) {
                            var cnt = parseInt(row.cnt || 0, 10), rev = parseFloat(row.revenue || 0);
                            var avg = cnt > 0 ? ' · ~' + pfMoney(rev / cnt) + '/опл.' : '';
                            $t.append($('<div>').addClass('flex flex-col gap-0.5 py-1 border-b border-white/5 last:border-0')
                                .append($('<span>').addClass('text-gray-200 text-sm').text(pfTariff(row.tariff)))
                                .append($('<span>').addClass('text-gray-400 text-xs').text(pfMoney(rev) + ' всего · ' + cnt + ' ' + pfPlural(cnt, 'оплата', 'оплаты', 'оплат') + avg)));
                        });
                        var $rc = $('[data-pf-recent]').empty();
                        if (!(r.recent || []).length) $rc.append($('<span>').addClass('text-gray-500').text('нет данных'));
                        (r.recent || []).forEach(function (p) {
                            var ok = p.status === 'succeeded';
                            var dt = '';
                            try { dt = new Date(p.date).toLocaleString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); } catch (e) { dt = (p.date || '').slice(0, 16); }
                            var $row = $('<button>').attr({ type: 'button', 'data-receipt-open': p.id }).addClass('w-full text-left px-3 py-2.5 rounded-xl transition-colors ' + (ok ? 'bg-[#38ff811a] hover:bg-green-500/[0.08]' : 'bg-white/5 hover:bg-white/10'));
                            var $top = $('<div>').addClass('flex items-center gap-3');
                            $top.append($('<span>').addClass('shrink-0').html(window.payStatusIcon ? window.payStatusIcon(ok) : ''));
                            $top.append($('<div>').addClass('min-w-0').append($('<div>').addClass('text-sm font-medium ' + (ok ? 'text-gray-100' : 'text-gray-400')).text(ok ? 'Оплачен' : 'Не оплачен')).append($('<div>').addClass('text-xs text-gray-500').text(dt)));
                            $top.append($('<span>').addClass('font-mono text-xs text-gray-400 truncate hidden md:block').text(p.id || ''));
                            $top.append($('<span>').addClass('flex items-center gap-1.5 shrink-0 ml-auto').html(window.payMethodHtml(p.method_type || p.method)));
                            $top.append($('<span>').addClass('text-sm font-bold text-white whitespace-nowrap').text(Number(p.amount || 0).toLocaleString('ru-RU') + '₽'));
                            $row.append($top);
                            if (p.description) $row.append($('<div>').addClass('mt-1.5 inline-block text-xs text-gray-400 bg-white/5 rounded-lg px-2.5 py-1').text(String(p.description).slice(0, 80)));
                            $rc.append($row);
                        });
                    }
                    function pfLoad(force) {
                        if (!force && (!pfVisible() || pfLoading)) return;
                        var key = pfKey();
                        if (!force && pfCache[key]) { pfRender(pfCache[key]); return; }
                        pfLoading = true;
                        if (window.QSpin) QSpin.show('Собираю данные из кассы…');
                        $.ajax({
                            url: pfUrl(), method: 'GET', dataType: 'json', timeout: 180000,
                            success: function (r) { pfCache[key] = r; pfRender(r); },
                            complete: function () { pfLoading = false; if (window.QSpin) QSpin.hide(); }
                        });
                    }
                    $('[data-pf-days]').on('click', function () {
                        pfDays = parseInt($(this).attr('data-pf-days'), 10) || 30;
                        pfMonth = null;
                        $('[data-pf-month]').val('');
                        $('[data-pf-days]').removeClass('bg-blue-600 text-white').addClass('bg-white/5 text-gray-300');
                        $(this).removeClass('bg-white/5 text-gray-300').addClass('bg-blue-600 text-white');
                        pfLoad(true);
                    });
                    $('[data-pf-month]').on('change', function () {
                        if (!$(this).val()) return;
                        pfMonth = $(this).val();
                        $('[data-pf-days]').removeClass('bg-blue-600 text-white').addClass('bg-white/5 text-gray-300');
                        pfLoad(true);
                    });
                    $('[data-pf-refresh]').on('click', function () { delete pfCache[pfKey()]; pfLoad(true); });
                    $(document).on('click', '[data-toggle-section="profit"]', function () { setTimeout(function () { pfLoad(false); }, 350); });
                    pfLoad(false);
                });
            </script>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="profit">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: ROI -->
            <?php if ($groups->isPermission($adminUsername,'roi')): ?>
            <?php $roiCosts = \Setting\Route\Function\Controllers\Admin\Finance\Finance::getCosts(); ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="roi">
                <div class="py-6 flex-col flex md:flex-row justify-between items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                        Калькулятор ROI
                    </h1>
                    <div class="flex items-center gap-2">
                        <span data-roi-source class="text-xs text-gray-500"></span>
                        <div class="flex rounded-lg overflow-hidden ring-1 ring-white/10 text-xs font-medium">
                            <button data-roi-days="30" class="px-3 py-1.5 bg-blue-600 text-white">30д</button>
                            <button data-roi-days="90" class="px-3 py-1.5 bg-white/5 text-gray-300 hover:bg-white/10">90д</button>
                            <button data-roi-days="365" class="px-3 py-1.5 bg-white/5 text-gray-300 hover:bg-white/10">год</button>
                        </div>
                        <span class="text-sm font-semibold rounded-full px-3 py-1 bg-white/5 ring-1 ring-white/10 text-gray-300" data-roi-verdict>…</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                        <div class="text-sm text-gray-500 mb-1">Чистая выручка (касса − возвраты − комиссия)</div>
                        <div class="text-3xl font-bold text-gray-100" data-roi="clean">…</div>
                        <div class="text-xs text-gray-500 mt-1" data-roi-sub="gross"></div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                        <div class="text-sm text-gray-500 mb-1">Расходы за период</div>
                        <div class="text-3xl font-bold text-gray-100" data-roi="costs">…</div>
                        <div class="text-xs text-gray-500 mt-1" data-roi-sub="costs"></div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                        <div class="text-sm text-gray-500 mb-1">Прибыль</div>
                        <div class="text-3xl font-bold" data-roi="profit">…</div>
                        <div class="text-xs text-gray-500 mt-1" data-roi-sub="mrr"></div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                        <div class="text-sm text-gray-500 mb-1">ROI</div>
                        <div class="text-3xl font-bold" data-roi="roi">…</div>
                        <div class="text-xs text-gray-500 mt-1" data-roi-sub="margin"></div>
                    </div>
                </div>

                <form action="/admin/roi/save" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6">
                        <h3 class="font-semibold text-gray-300 mb-4">Расходы, ₽/мес + комиссия кассы</h3>
                        <label class="flex flex-col gap-1 mb-4">
                            <span class="text-sm text-gray-500">Серверы</span>
                            <input type="number" name="servers" min="0" step="0.01" value="<?= htmlspecialchars((string) ($roiCosts['servers'] ?? 0)) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                        </label>
                        <label class="flex flex-col gap-1 mb-4">
                            <span class="text-sm text-gray-500">Прочее</span>
                            <input type="number" name="other" min="0" step="0.01" value="<?= htmlspecialchars((string) ($roiCosts['other'] ?? 0)) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-sm text-gray-500">Комиссия кассы, % (возвраты вычитаются автоматически)</span>
                            <input type="number" name="commission" min="0" max="100" step="0.1" value="<?= htmlspecialchars((string) round((float) ($roiCosts['commission'] ?? 0) * 100, 2)) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                        </label>
                    </div>
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6">
                        <h3 class="font-semibold text-gray-300 mb-4">Как считается</h3>
                        <div class="text-sm text-gray-500 flex flex-col gap-2">
                            <div>Выручка — из ЮKassa: succeeded минус возвраты (возвраты могут прийти позже оплаты).</div>
                            <div>Чистая = выручка − комиссия кассы. Прибыль = чистая − расходы за период.</div>
                            <div>ROI = прибыль / расходы × 100%. MRR = чистая за 30 дней.</div>
                        </div>
                        <button type="submit" class="mt-4 px-6 py-2.5 rounded-xl bg-green-600 hover:bg-green-500 text-white text-sm font-semibold transition-colors cursor-pointer">
                            Сохранить
                        </button>
                    </div>
                </form>
            </section>
            <script defer>
                $(document).ready(function () {
                    var roiDays = 30, roiCache = {};
                    function roiMoney(v) { return Number(v || 0).toLocaleString('ru-RU', { maximumFractionDigits: 0 }) + '₽'; }
                    function roiRender(r) {
                        if (!r || r.status !== 'ok') return;
                        $('[data-roi="clean"]').text(roiMoney(r.clean));
                        $('[data-roi-sub="gross"]').text('брутто ' + roiMoney(r.gross) + ' · возвраты ' + roiMoney(r.refunds) + ' (' + (r.refunds_count || 0) + ') · комиссия ' + roiMoney(r.commission));
                        $('[data-roi="costs"]').text(roiMoney(r.costs));
                        $('[data-roi-sub="costs"]').text('оплат: ' + (r.succeeded || 0) + ' · отмен: ' + (r.canceled || 0) + ' · средний чек ' + roiMoney(r.avg_check));
                        var $p = $('[data-roi="profit"]').text((r.profit >= 0 ? '+' : '') + roiMoney(r.profit));
                        $p.toggleClass('text-green-400', r.profit >= 0).toggleClass('text-red-400', r.profit < 0);
                        $('[data-roi-sub="mrr"]').text('MRR ' + roiMoney(r.mrr) + (r.breakeven !== null ? ' · безубыточность: ' + r.breakeven + ' опл.' : ''));
                        var $roi = $('[data-roi="roi"]').text(r.roi === null ? '—' : r.roi + '%');
                        $roi.toggleClass('text-green-400', (r.roi ?? 0) >= 0).toggleClass('text-gray-400', (r.roi ?? 0) < 0);
                        $('[data-roi-sub="margin"]').text('маржа ' + (r.margin === null ? '—' : r.margin + '%'));
                        var $v = $('[data-roi-verdict]').text(r.profit >= 0 ? 'В плюсе: +' + roiMoney(r.profit) : 'В минусе: ' + roiMoney(r.profit));
                        $v.attr('class', 'text-sm font-semibold rounded-full px-3 py-1 ' + (r.profit >= 0 ? 'text-green-400 bg-green-500/10' : 'text-red-400 bg-red-500/10'));
                        $('[data-roi-source]').text('источник: ' + (r.source === 'kassa' ? 'ЮKassa' : (r.source === 'ledger' ? 'леджер' : 'БД')) + (r.cached ? ' · кэш' : ''));
                    }
                    function roiLoad() {
                        if (roiCache[roiDays]) { roiRender(roiCache[roiDays]); return; }
                        $.ajax({
                            url: '/api/admin/roi?days=' + roiDays, method: 'GET', dataType: 'json', timeout: 120000,
                            success: function (r) { roiCache[roiDays] = r; roiRender(r); }
                        });
                    }
                    $('[data-roi-days]').on('click', function () {
                        roiDays = parseInt($(this).attr('data-roi-days'), 10) || 30;
                        $('[data-roi-days]').removeClass('bg-blue-600 text-white').addClass('bg-white/5 text-gray-300');
                        $(this).removeClass('bg-white/5 text-gray-300').addClass('bg-blue-600 text-white');
                        roiLoad();
                    });
                    $('[data-roi-import]').on('click', function () {
                        var $m = $('[data-roi-import-msg]').text('Импорт…');
                        $.ajax({
                            url: '/api/admin/payments/import', method: 'POST', data: { days: 90 }, dataType: 'json', timeout: 180000,
                            success: function (r) { $m.text(r.status === 'ok' ? ('Готово: ' + r.imported + ', пропущено ' + r.skipped) : ('Ошибка: ' + (r.message || ''))); },
                            error: function () { $m.text('Ошибка сети'); }
                        });
                    });
                    $(document).on('click', '[data-toggle-section="roi"]', function () { setTimeout(roiLoad, 350); });
                    $(document).off('click.roireceipt').on('click.roireceipt', '[data-receipt-open]', function (e) {
                        if ($(this).closest('[data-admin-chat]').length) return;//в чате своя делегация
                        if (typeof window.__openReceipt === 'function') window.__openReceipt($(this).attr('data-receipt-open'));
                    });
                    roiLoad();
                });
            </script>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="roi">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Логи -->
            <?php if ($groups->isPermission($adminUsername,'logs')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="logs">
                <!-- Заголовок -->
                <div class="py-6 flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
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
                            Логи <?= htmlspecialchars(basename($_ENV['LOG_FILE_NAME'] ?? 'qwees.log')) ?></div>
                        <div
                            class="relative max-h-[42vw] overflow-scroll flex flex-col gap-0.5 bg-black rounded-b-xl py-2">
                            <?php
                            $logfile = dirname(__DIR__, 3) . '/' . ($_ENV['LOG_FILE_NAME'] ?? 'qwees.log');//тот же файл, куда пишут логгеры
                            if (file_exists($logfile)) {
                                echo Setting\Route\Function\Controllers\Admin\LogViewer::render(
                                    Setting\Route\Function\Controllers\Admin\LogViewer::tail(500)
                                );
                            } else {
                                echo "<div class='text-[13px] italic bg-white/5'>Лог-файл не найден.</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </section>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="logs">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Чат поддержки -->
            <?php if ($groups->isPermission($adminUsername,'chat')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="chat">
                <div class="py-6 flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                        Чат поддержки
                    </h1>
                </div>
                <?php include_once __DIR__ . '/../../components/chat_admin.php'; ?>
            </section>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="chat">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Пробная подписка -->
            <?php if ($groups->isPermission($adminUsername,'gifts')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="gifts">
                <div class="py-6 flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                        Пробная подписка
                    </h1>
                    <?php if ($gifts->isEnabled()): ?>
                        <span class="text-sm font-semibold text-green-400 bg-green-500/10 rounded-full px-3 py-1">Включена</span>
                    <?php else: ?>
                        <span class="text-sm font-semibold text-gray-500 bg-white/10 rounded-full px-3 py-1">Выключена</span>
                    <?php endif; ?>
                </div>
                <form action="/admin/gifts/save" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6">
                        <h3 class="font-semibold text-gray-300 mb-4">Настройки</h3>
                        <label class="flex items-center justify-between gap-4 mb-4 cursor-pointer">
                            <span>
                                <span class="block font-medium text-gray-100">Показ кнопки «Пробная»</span>
                                <span class="block text-sm text-gray-500">Сохраняется кнопкой внизу</span>
                            </span>
                            <span class="relative inline-flex cursor-pointer items-center shrink-0">
                                <input type="checkbox" name="enabled" value="on" class="peer sr-only" <?= $gifts->isEnabled() ? 'checked' : '' ?>>
                                <span class="h-6 w-11 rounded-full bg-gray-600 peer-checked:bg-green-500 transition-colors"></span>
                                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                            </span>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-sm text-gray-500">Срок, дней</span>
                            <input type="number" name="days" min="1" value="<?= (int) ($gifts->data['days'] ?? 3) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-sm text-gray-500">Кому показывать</span>
                            <select id="gifts-mode" name="mode" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                                <option value="all" <?= ($gifts->data['mode'] ?? 'all') === 'all' ? 'selected' : '' ?>>Всем сразу</option>
                                <option value="list" <?= ($gifts->data['mode'] ?? '') === 'list' ? 'selected' : '' ?>>Выборочным пользователям</option>
                            </select>
                        </label>
                    </div>
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6">
                        <h3 class="font-semibold text-gray-300 mb-4">Кому показывать</h3>
                        <style>
                        #gifts-list-wrap { overflow:hidden; max-height:1200px; opacity:1; transition:max-height .35s ease, opacity .3s ease; }
                        #gifts-list-wrap.gifts-closed { max-height:0; opacity:0; }
                        </style>
                        <div id="gifts-list-wrap" class="<?= ($gifts->data['mode'] ?? 'all') === 'list' ? '' : 'gifts-closed' ?>">
                        <div class="flex min-w-0 flex-col gap-3">
                            <div class="flex flex-col gap-2">
                                <span class="text-sm text-gray-500">Быстрое добавление (поиск по клиентам)</span>
                                <div class="flex gap-2">
                                <input type="text" id="gifts-quick" list="admin_users_list" placeholder="uniID..." autocomplete="off"
                                    class="flex-1 min-w-0 border border-white/10 rounded-lg px-4 py-2 text-gray-100 font-mono text-sm focus:outline-none focus:border-green-500">
                                <!-- Единый список uniID для всех поисковых инпутов админки -->
                                <datalist id="admin_users_list">
                                    <?php foreach ($allUsers as $user): ?>
                                        <option value="<?= htmlspecialchars($user['uniID']) ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                                    <button type="button" id="gifts-add-btn"
                                        class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-gray-300 text-sm font-semibold transition-colors cursor-pointer shrink-0">
                                        Добавить
                                    </button>
                                </div>
                            </div>
                            <div id="gifts-card" class="hidden md:flex items-center gap-3 bg-white/5 rounded-xl px-4 py-2 min-h-[58px]">
                                <span class="text-sm text-gray-400">Введи uniID — увидишь клиента</span>
                            </div>
                        </div>
                        <label class="flex flex-col gap-1 mt-3">
                            <span class="text-sm text-gray-500">Список uniID (каждый с новой строки) — <span id="gifts-count" class="font-semibold text-gray-300"></span><span id="gifts-unknown" class="text-red-500"></span></span>
                            <textarea id="gifts-users" name="users" rows="4" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 font-mono text-sm focus:outline-none focus:border-green-500"><?= htmlspecialchars(implode("\n", array_filter((array) ($gifts->data['users'] ?? [])))) ?></textarea>
                        </label>
                        </div>
                    </div>
                        <?php $giftMap = [];
                        foreach ($allUsers as $u) { $giftMap[$u['uniID']] = ['name' => trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')), 'email' => $u['email'] ?? '', 'sub' => $subMap[$u['uniID']] ?? 0]; } ?>
                        <script>
                        const GIFT_USERS = <?= json_encode($giftMap, JSON_UNESCAPED_UNICODE) ?>;
                        $(function () {//виджет пробных: все локально, сервер не трогаем
                            const $input = $('#gifts-quick');
                            const $area = $('#gifts-users');
                            if (!$input.length || !$area.length) return;
                            function esc(s) {
                                return $('<div>').text(s ?? '').html();
                            }
                            function lines() {
                                return String($area.val()).split(/[\r\n,;]+/).map(function (s) { return s.trim(); }).filter(Boolean);
                            }
                            function refresh() {//счетчик, проверка, карточка и режим разом
                                const list = [...new Set(lines())];
                                $('#gifts-count').text('Выбрано: ' + list.length);
                                const bad = list.filter(function (u) { return !GIFT_USERS[u]; }).length;
                                $('#gifts-unknown').text(bad > 0 ? ' · нет в базе: ' + bad : '');
                                $('#gifts-list-wrap').toggleClass('gifts-closed', $('#gifts-mode').val() !== 'list');
                                const $card = $('#gifts-card');
                                const v = String($input.val()).trim();
                                const hit = v ? (GIFT_USERS[v] || null) : null;
                                if (!hit) {
                                    $card.html('<span class="text-sm text-gray-400">' + (v ? 'Не найден в базе' : 'Введи uniID — увидишь клиента') + '</span>');
                                    return;
                                }
                                const avCls = hit.sub ? 'bg-green-500/10 text-green-400' : 'bg-white/10 text-gray-500';
                                const sub = hit.sub
                                    ? '<span class="flex items-center gap-1 text-[11px] font-medium text-green-400"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>подписка есть</span>'
                                    : '<span class="flex items-center gap-1 text-[11px] text-gray-400"><span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>без подписки</span>';
                                $card.html('<span class="w-10 h-10 rounded-full font-bold flex items-center justify-center shrink-0 uppercase ' + avCls + '">'
                                    + esc((hit.name || v).charAt(0)) + '</span>'
                                    + '<span class="min-w-0"><span class="block font-semibold text-gray-100 truncate">' + esc(hit.name || v) + '</span>'
                                    + '<span class="block text-xs text-gray-500 truncate">' + esc(hit.email) + ' · ' + esc(v) + '</span>' + sub + '</span>'
                                    + (list.indexOf(v) !== -1 ? '<span class="ml-auto text-[11px] font-semibold text-green-400 bg-green-500/10 rounded-full px-2 py-0.5 shrink-0">в списке</span>' : ''));
                            }
                            function add() {
                                const v = String($input.val()).trim();
                                if (!v) return;
                                const list = lines();
                                if (list.indexOf(v) === -1) $area.val(list.concat([v]).join('\n'));
                                $input.val('');
                                refresh();
                            }
                            $('#gifts-add-btn').on('click', add);
                            $input.on('keydown', function (e) {
                                if (e.key === 'Enter') { e.preventDefault(); add(); }
                            });
                            $input.on('input', refresh);
                            $area.on('input', refresh);
                            $('#gifts-mode').on('change', refresh);
                            refresh();
                        });
                        </script>
                        <div class="md:col-span-2">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-green-600 hover:bg-green-500 text-white text-sm font-semibold transition-colors cursor-pointer">
                                Сохранить
                            </button>
                        </div>
                    </form>
            </section>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="gifts">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Секция: Реферальная система -->
            <?php if ($groups->isPermission($adminUsername,'refer')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="refer">
                <div class="py-6 flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                        Реферальная система
                    </h1>
                    <?php if (!empty($referCfg['enabled'])): ?>
                        <span class="text-sm font-semibold text-green-400 bg-green-500/10 rounded-full px-3 py-1">Включена</span>
                    <?php else: ?>
                        <span class="text-sm font-semibold text-gray-500 bg-white/10 rounded-full px-3 py-1">Выключена</span>
                    <?php endif; ?>
                </div>
                <form action="/admin/refer/save" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6">
                        <h3 class="font-semibold text-gray-300 mb-4">Приглашённый (кто ввёл код)</h3>
                        <label class="flex flex-col gap-1 mb-4">
                            <span class="text-sm text-gray-500">Дней в подарок</span>
                            <input type="number" name="referral_days" min="0" value="<?= (int) ($referCfg['referral_days'] ?? 3) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                        </label>
                        <label class="flex flex-col gap-1 mb-4">
                            <span class="text-sm text-gray-500">Скидка, %</span>
                            <input type="number" name="referral_discount" min="0" max="100" value="<?= (int) ($referCfg['referral_discount'] ?? 10) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-sm text-gray-500">Скидка действует, покупок</span>
                            <input type="number" name="discount_uses" min="1" max="100" value="<?= (int) ($referCfg['discount_uses'] ?? 5) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                            <span class="text-xs text-gray-400">1 — разовая (сгорит после первой оплаты), 5 — на пять покупок и т.д.</span>
                        </label>
                    </div>
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6">
                        <h3 class="font-semibold text-gray-300 mb-4">Пригласивший (владелец кода)</h3>
                        <label class="flex flex-col gap-1 mb-4">
                            <span class="text-sm text-gray-500">Дней за каждого приглашённого</span>
                            <input type="number" name="referrer_days" min="0" value="<?= (int) ($referCfg['referrer_days'] ?? 3) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                        </label>
                        <label class="flex flex-col gap-1 mb-4">
                            <span class="text-sm text-gray-500">% от дней покупки приглашённого — себе</span>
                            <input type="number" name="referrer_percent" min="0" max="100" value="<?= (int) ($referCfg['referrer_percent'] ?? 5) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                            <span class="text-xs text-gray-400">5% от 30 дней = 2 дня сверху</span>
                        </label>
                        <label class="flex flex-col gap-1 mb-4">
                            <span class="text-sm text-gray-500">Сколько покупок каждого приглашённого дают %</span>
                            <input type="number" name="referrer_takes" min="1" max="100" value="<?= (int) ($referCfg['referrer_takes'] ?? 5) ?>" class="border border-white/10 rounded-lg px-4 py-2 text-gray-100 focus:outline-none focus:border-green-500">
                        </label>
                        <label class="flex items-center justify-between gap-4 cursor-pointer">
                            <span>
                                <span class="block font-medium text-gray-100">Рефералка включена</span>
                                <span class="block text-sm text-gray-500">Выкл — новые коды активировать нельзя</span>
                            </span>
                            <span class="relative inline-flex cursor-pointer items-center shrink-0">
                                <input type="checkbox" name="enabled" value="on" class="peer sr-only" <?= !empty($referCfg['enabled']) ? 'checked' : '' ?>>
                                <span class="h-6 w-11 rounded-full bg-gray-600 peer-checked:bg-green-500 transition-colors"></span>
                                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                            </span>
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-green-600 hover:bg-green-500 text-white text-sm font-semibold transition-colors cursor-pointer">
                            Сохранить
                        </button>
                    </div>
                </form>
            </section>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="refer">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Секция: Выдачи -->
            <?php if ($groups->isPermission($adminUsername,'give')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="give">
                <!-- Заголовок -->
                <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                        Панель выдачи подписок
                    </h1>
                </div>

                <!-- Выдача -->
                <div class="flex min-w-0 flex-col gap-4">

                    <!-- Добавить клиента в днях -->
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6 w-full">
                        <h2
                            class="text-lg sm:text-2xl font-semibold text-white tracking-tight flex items-center gap-2">
                            Выдать подписку
                            <span
                                class="flex items-center py-0 px-1.5 bg-rose-200/50 rounded-md font-medium text-rose-500 text-sm shrink-0">в
                                днях</span>
                        </h2>
                        <form class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4" action="/admin/addClientDays"
                            method="POST">
                            <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                            <!-- uniID -->
                            <div class="relative">
                                <label for="client_id"
                                    class="absolute left-2 border rounded-md -top-1 bg-white/5 px-1 text-gray-100 text-xs">ID
                                    клиента</label>
                                <input type="text" name="uniID" list="admin_users_list"
                                    class="w-full px-3 py-2 text-[15px] rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none mt-2"
                                    placeholder="uniID" required>
                            </div>

                            <!-- count days -->
                            <div class="relative">
                                <label for="give_days"
                                    class="absolute left-2 border rounded-md -top-1 bg-white/5 px-1 text-gray-100 text-xs">Количество
                                    дней</label>
                                <input type="number" name="days" placeholder="от 1 до ∞"
                                    class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none mt-2"
                                    required>
                            </div>

                            <!-- limit divese -->
                            <div class="relative">
                                <label for="give_divece_limit"
                                    class="absolute left-2 border rounded-md -top-1 bg-white/5 px-1 text-gray-100 text-xs">Количество
                                    устройств</label>
                                <input type="number" name="devices" placeholder="от 1 до ∞ (0 безлимит)"
                                    class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none mt-2"
                                    required>
                            </div>

                            <button
                                class="rounded-lg bg-primary border-dashed border-2 border-green-500/60 hover:bg-green-500/60 transition-colors text-gray-100 font-semibold text-base focus:outline-none"
                                type="submit">
                                <i class="fas fa-user-plus mr-2 text-sm"></i>
                                Выдать
                            </button>

                        </form>
                        <div data-user-find class="mt-6">
                            <h2 class="text-lg sm:text-2xl font-semibold text-gray-100/40 tracking-tight">
                                Информация об клиенте</h2>
                            <div class="flex flex-col lg:flex-row gap-6 mt-6 min-w-0">
                                <!-- Contact info -->
                                <div class="flex min-w-0 flex-col gap-4">
                                    <p class="text-gray-500"><span
                                            class="text-gray-100 uppercase border-solid border-r-2 border-black px-2"
                                            data-fuser-id></span> Ф.И: <span class="text-gray-100 uppercase"
                                            data-fuser-name></span></p>
                                    <p class="text-gray-500">UniID: <span
                                            class="text-gray-100 bg-green-500/10 px-2 py-1 rounded-lg break-all" data-fuser-uniID></span>
                                    </p>
                                </div>
                                <!-- Subscription info -->
                                <div class="flex min-w-0 flex-col gap-3">
                                    <p class="text-gray-500">Статус подписки: <span
                                            class="text-gray-100 px-2 py-1 rounded-lg break-all" data-fuser-status></span></p>
                                    <p class="text-gray-500">Активен до: <span class="text-gray-100 px-2 py-1 rounded-sm"
                                            data-fuser-expires></span></p>
                                </div>
                                <!-- Subscription link info -->
                                <div class="flex min-w-0 flex-col gap-3">
                                    <p class="text-gray-500">Количество дней: <span
                                            class="text-gray-100 px-2 py-1 rounded-lg break-all" data-fuser-countdays></span></p>
                                    <p class="text-gray-500">Подписка: <span class="text-gray-100 px-2 py-1 rounded-lg break-all"
                                            data-fuser-subscription></span>
                                        <button
                                            onclick="copyToClipboard($('[data-fuser-subscription]').text(), 'Подписка')"><i
                                                class="fa-solid fa-copy"></i></button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Добавить клиента в часах -->
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6 w-full">
                        <h2
                            class="text-lg sm:text-2xl font-semibold text-white tracking-tight flex gap-2 items-center">
                            Выдать подписку
                            <span
                                class="flex items-center py-0 px-1.5 bg-sky-500/15 rounded-md font-medium text-sky-300 text-sm shrink-0">в
                                часах</span>
                        </h2>
                        <form class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4" action="/admin/addClientHours"
                            method="POST">
                            <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                            <!-- uniID -->
                            <div class="relative">
                                <label for="client_id"
                                    class="absolute left-2 border rounded-md -top-1 bg-white/5 px-1 text-gray-100 text-xs">ID
                                    клиента</label>
                                <input type="text" name="uniIDhours" list="admin_users_list"
                                    class="w-full px-3 py-2 text-[15px] rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none mt-2"
                                    placeholder="uniID" required>
                            </div>

                            <!-- count hours -->
                            <div class="relative">
                                <label for="give_days"
                                    class="absolute left-2 border rounded-md -top-1 bg-white/5 px-1 text-gray-100 text-xs">Количество
                                    часов</label>
                                <input type="number" name="hours" placeholder="от 1 до ∞"
                                    class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none mt-2"
                                    required>
                            </div>

                            <!-- limit divese -->
                            <div class="relative">
                                <label for="give_divece_limit"
                                    class="absolute left-2 border rounded-md -top-1 bg-white/5 px-1 text-gray-100 text-xs">Количество
                                    устройств</label>
                                <input type="number" name="devices" placeholder="от 1 до ∞ (0 безлимит)"
                                    class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none mt-2"
                                    required>
                            </div>

                            <button
                                class="rounded-lg bg-primary border-dashed border-2 border-green-500/60 hover:bg-green-500/60 transition-colors text-gray-100 font-semibold text-base focus:outline-none"
                                type="submit">
                                <i class="fas fa-user-plus mr-2 text-sm"></i>
                                Выдать
                            </button>

                        </form>
                        <div data-user-find-hours class="mt-6">
                            <h2 class="text-lg sm:text-2xl font-semibold text-gray-100/40 tracking-tight">
                                Информация об клиенте</h2>
                            <div class="flex flex-col lg:flex-row gap-6 mt-6 min-w-0">
                                <!-- Contact info -->
                                <div class="flex min-w-0 flex-col gap-4">
                                    <p class="text-gray-500"><span
                                            class="text-gray-100 uppercase border-solid border-r-2 border-black px-2"
                                            data-fuser-id-hours></span> Ф.И: <span class="text-gray-100 uppercase"
                                            data-fuser-name-hours></span></p>
                                    <p class="text-gray-500">UniID: <span
                                            class="text-gray-100 bg-green-500/10 px-2 py-1 rounded-lg break-all"
                                            data-fuser-uniID-hours></span>
                                    </p>
                                </div>
                                <!-- Subscription info -->
                                <div class="flex min-w-0 flex-col gap-3">
                                    <p class="text-gray-500">Статус подписки: <span
                                            class="text-gray-100 px-2 py-1 rounded-lg break-all" data-fuser-status-hours></span></p>
                                    <p class="text-gray-500">Активен до: <span class="text-gray-100 px-2 py-1 rounded-sm"
                                            data-fuser-expires-hours></span></p>
                                </div>
                                <!-- Subscription link info -->
                                <div class="flex min-w-0 flex-col gap-3">
                                    <p class="text-gray-500">Количество дней: <span
                                            class="text-gray-100 px-2 py-1 rounded-lg break-all" data-fuser-countdays-hours></span>
                                    </p>
                                    <p class="text-gray-500">Подписка: <span class="text-gray-100 px-2 py-1 rounded-lg break-all"
                                            data-fuser-subscription-hours></span>
                                        <button
                                            onclick="copyToClipboard($('[data-fuser-subscription]').text(), 'Подписка')"><i
                                                class="fa-solid fa-copy"></i></button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Добавить клиента в минутах -->
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6 w-full">
                        <h2 class="text-lg sm:text-2xl font-semibold text-white tracking-tight flex gap-2 items-center">
                            Выдать подписку
                            <span class="flex items-center py-0 px-1.5 bg-green-400/20 rounded-md font-medium text-green-400 text-sm shrink-0">в
                                минутах</span>
                        </h2>
                        <form class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4" action="/admin/addClientMinutes"
                            method="POST">
                            <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                            <!-- uniID -->
                            <div class="relative">
                                <label for="client_id"
                                    class="absolute left-2 border rounded-md -top-1 bg-white/5 px-1 text-gray-100 text-xs">ID клиента</label>
                                <input type="text" name="uniIDMinutes" list="admin_users_list"
                                    class="w-full px-3 py-2 text-[15px] rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none mt-2"
                                    placeholder="uniID" required>
                            </div>

                            <!-- count minutes -->
                            <div class="relative">
                                <label for="give_days"
                                    class="absolute left-2 border rounded-md -top-1 bg-white/5 px-1 text-gray-100 text-xs">Количество
                                    минут</label>
                                <input type="number" name="minutes" placeholder="от 1 до ∞"
                                    class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none mt-2"
                                    required>
                            </div>

                            <!-- limit divese -->
                            <div class="relative">
                                <label for="give_divece_limit"
                                    class="absolute left-2 border rounded-md -top-1 bg-white/5 px-1 text-gray-100 text-xs">Количество
                                    устройств</label>
                                <input type="number" name="devices" placeholder="от 1 до ∞ (0 безлимит)"
                                    class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:outline-none mt-2"
                                    required>
                            </div>

                            <button
                                class="rounded-lg bg-primary border-dashed border-2 border-green-500/60 hover:bg-green-500/60 transition-colors text-gray-100 font-semibold text-base focus:outline-none"
                                type="submit">
                                <i class="fas fa-user-plus mr-2 text-sm"></i>
                                Выдать
                            </button>

                        </form>
                        <div data-user-find-minutes class="mt-6">
                            <h2 class="text-lg sm:text-2xl font-semibold text-gray-100/40 tracking-tight">
                                Информация об клиенте</h2>
                            <div class="flex flex-col lg:flex-row gap-6 mt-6 min-w-0">
                                <!-- Contact info -->
                                <div class="flex min-w-0 flex-col gap-4">
                                    <p class="text-gray-500"><span
                                            class="text-gray-100 uppercase border-solid border-r-2 border-black px-2"
                                            data-fuser-id-minutes></span> Ф.И: <span class="text-gray-100 uppercase"
                                            data-fuser-name-minutes></span></p>
                                    <p class="text-gray-500">UniID: <span
                                            class="text-gray-100 bg-green-500/10 px-2 py-1 rounded-lg break-all"
                                            data-fuser-uniID-minutes></span>
                                    </p>
                                </div>
                                <!-- Subscription info -->
                                <div class="flex min-w-0 flex-col gap-3">
                                    <p class="text-gray-500">Статус подписки: <span
                                            class="text-gray-100 px-2 py-1 rounded-lg break-all" data-fuser-status-minutes></span></p>
                                    <p class="text-gray-500">Активен до: <span class="text-gray-100 px-2 py-1 rounded-sm"
                                            data-fuser-expires-minutes></span></p>
                                </div>
                                <!-- Subscription link info -->
                                <div class="flex min-w-0 flex-col gap-3">
                                    <p class="text-gray-500">Количество дней: <span
                                            class="text-gray-100 px-2 py-1 rounded-lg break-all" data-fuser-countdays-minutes></span>
                                    </p>
                                    <p class="text-gray-500">Подписка: <span class="text-gray-100 px-2 py-1 rounded-lg break-all"
                                            data-fuser-subscription-minutes></span>
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
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="give">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Изьятие подписок -->
            <?php if ($groups->isPermission($adminUsername,'reduce')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="reduce">
                <!-- Заголовок -->
                <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                        Панель изьятия подписок
                    </h1>
                </div>

                <!-- Изьятие -->
                <div class="flex min-w-0 flex-col gap-4">

                    <!-- Изьятие подписки -->
                    <div class="flex flex-col lg:flex-row gap-6 relative bg-[#16181d] rounded-xl ring-1 ring-white/5 p-6">
                        <div class="flex flex-1 flex-col lg:w-[400px] w-full">
                            <h2 class="text-lg sm:text-2xl font-semibold text-white tracking-tight">
                                Изьятие подписки</h2>
                            <form class="flex flex-col gap-4 mt-4" action="/admin/reduceClient" method="POST">
                                <input type="hidden" name="url"
                                    value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                                <!-- ID клиента -->
                                <div class="flex items-center gap-2">
                                    <label for="client_id" class="text-gray-400 text-sm">uniID клиента</label>
                                    <input id="client_id" type="text" name="uniID" list="admin_users_list"
                                        class="px-3 py-2 rounded-lg border border-white/10 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                        placeholder="uniID" required>
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
                            <h2 class="text-lg sm:text-2xl font-semibold text-gray-100/40 tracking-tight">
                                Информация об клиенте</h2>
                            <div class="flex flex-col lg:flex-row gap-6 mt-6 min-w-0">
                                <!-- Contact info -->
                                <div class="flex min-w-0 flex-1 flex-col gap-4">
                                    <p class="text-gray-500"><span
                                            class="text-gray-100 uppercase border-solid border-r-2 border-black px-2"
                                            data-fuser-id></span> Ф.И: <span class="text-gray-100 uppercase"
                                            data-fuser-name></span></p>
                                    <p class="text-gray-500">UniID: <span
                                            class="text-gray-100 bg-green-500/10 px-2 py-1 rounded-lg break-all" data-fuser-uniID></span>
                                    </p>
                                </div>
                                <!-- Subscription info -->
                                <div class="flex min-w-0 flex-1 flex-col gap-3">
                                    <p class="text-gray-500">Статус подписки: <span
                                            class="text-gray-100 px-2 py-1 rounded-lg break-all" data-fuser-status></span></p>
                                    <p class="text-gray-500">Активен до: <span class="text-gray-100 px-2 py-1 rounded-sm"
                                            data-fuser-expires></span></p>
                                </div>
                                <!-- Subscription link info -->
                                <div class="flex min-w-0 flex-1 flex-col gap-3">
                                    <p class="text-gray-500">Количество дней: <span
                                            class="text-gray-100 px-2 py-1 rounded-lg break-all" data-fuser-countdays></span></p>
                                    <p class="text-gray-500">Подписка: <span class="text-gray-100 px-2 py-1 rounded-lg break-all"
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
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="reduce">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Добавление пользователей -->
            <?php if ($groups->isPermission($adminUsername,'add_user')): ?>
                <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="add_user">
                    <!-- Заголовок -->
                    <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                        <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                            Добавление пользователя
                        </h1>
                        <div class="text-sm text-gray-500">
                            <?= htmlspecialchars($adminUsername) ?>, Ваша роль: <span class="font-semibold text-blue-400">
                                <?= htmlspecialchars($adminRole) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Форма добавления пользователя -->
                    <div class="bg-[#16181d] border border-white/10 rounded-2xl p-4 sm:p-6 w-full">
                        <h2 class="text-lg sm:text-2xl font-semibold text-white tracking-tight">
                            Панель создания пользователя</h2>
                        <form id="form_admin_add_user" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4"
                            action="/admin/addClientDays" method="POST">
                            <input type="hidden" name="url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                            <!-- Основная информация -->
                            <div class="space-y-4">
                                <h3 class="font-semibold text-gray-300">Основная информация</h3>

                                <div class="relative">
                                    <label for="first_name" class="block text-sm font-medium text-gray-300 mb-1">
                                        Имя <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="first_name" name="first_name" required
                                        placeholder="Введите имя пользователя"
                                        class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 placeholder-gray-500 focus:ring-blue-500 focus:outline-none">
                                </div>

                                <div class="relative">
                                    <label for="last_name" class="block text-sm font-medium text-gray-300 mb-1">
                                        Фамилия
                                    </label>
                                    <input type="text" id="last_name" name="last_name" placeholder=" Введите фамилию"
                                        class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 placeholder-gray-500 focus:ring-blue-500 focus:outline-none">
                                </div>

                                <div class="relative">
                                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" id="email" required placeholder="user@example.com"
                                        class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 placeholder-gray-500 focus:ring-blue-500 focus:outline-none">
                                    <p class="text-xs text-gray-500 mt-1">Пользователь сможет войти используя только этот
                                        email</p>
                                </div>
                                <!-- ######## MESSAGE ########## -->
                                <p class="font-sans hidden p-2" id="message_status"></p>
                            </div>

                            <!-- ################################################### -->
                            <!-- Настройки подписки -->
                            <div class="space-y-4">
                                <h3 class="font-semibold text-gray-300">Настройки подписки (необязательно)</h3>

                                <div class="relative">
                                    <label for="subscription" class="block text-sm font-medium text-gray-300 mb-1">
                                        Тип подписки
                                    </label>
                                    <select name="subscription" id="subscription"
                                        class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 placeholder-gray-500 focus:ring-blue-500 focus:outline-none">
                                        <option value="">Без подписки</option>
                                        <?php
                                        $plans = $tariffConfig;
                                        foreach ($plans as $planName => $plan): ?>
                                            <option value="<?= htmlspecialchars($planName) ?>">
                                                <?= htmlspecialchars(ucfirst($planName)) ?> -
                                                <?= htmlspecialchars((string) ($plan['periods'][1]['price'] ?? 0)) ?> ₽
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div id="subscription-details" class="hidden space-y-4">
                                    <div class="relative">
                                        <label for="duration_days" class="block text-sm font-medium text-gray-300 mb-1">
                                            Длительность (дней) <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" name="duration_days" id="duration_days" placeholder="30"
                                            min="1" value="30"
                                            class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 placeholder-gray-500 focus:ring-blue-500 focus:outline-none">
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
            <?php else: ?>
                <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="add_user">
                    <div class="py-10 flex flex-col items-center gap-3 text-center">
                        <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                        <div class="text-lg font-bold text-gray-100">Недоступно</div>
                        <div class="text-sm text-gray-500">Нет прав на раздел</div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Секция: Роли и права -->
            <?php if ($groups->isPermission($adminUsername, 'roles')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="roles">
                <div class="py-6 flex-col flex md:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                        Рабочие
                    </h1>
                    <div class="text-sm text-gray-500">
                        <?= htmlspecialchars($adminUsername) ?>, Ваша роль: <span class="font-semibold text-blue-400">
                            <?= htmlspecialchars($adminRole) ?>
                        </span>
                    </div>
                </div>


                <div class="bg-[#16181d] border border-white/10 rounded-2xl">
                    <div class="overflow-x-auto rounded-2xl">
                        <table class="w-full min-w-[1024px] text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b">
                                    <th class="p-3">Логин</th>
                                    <?php foreach (Setting\Route\Function\Controllers\Admin\Admin::FULL_PERMISSIONS as $p => $value): ?>
                                        <th class="p-3 text-center"><?= htmlspecialchars($p) ?>
                                          <div class="z-[99] relative inline-block group">
                                            <i class="fa-solid fa-circle-info text-gray-400 hover:text-gray-400 cursor-pointer"></i>
                                        
                                            <!-- Подсказка -->
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-50">
                                                <div class="bg-gray-900 text-white text-xs rounded-lg px-3 py-1.5 whitespace-nowrap shadow-lg">
                                                    <?= htmlspecialchars($value) ?>
                                                    <!-- Стрелочка -->
                                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                                </div>
                                            </div>
                                        </div>
                                        </th>
                                    <?php endforeach; ?>
                                    <th class="p-3"></th>
                                </tr>
                            </thead>
                            <tbody data-staff-tbody>
                                <tr><td colspan="99" class="p-6 text-center text-sm text-gray-500"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Загрузка сотрудников…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <h2 class="text-xl font-bold text-gray-100 py-6">Нанять менеджера</h2>
                <form action="/admin/roles/add" method="POST"
                    class="bg-[#16181d] border border-white/10 rounded-2xl p-4 flex flex-col md:flex-row gap-3">
                    <input type="text" name="username" required placeholder="Логин"
                        class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-gray-100 placeholder-gray-500 focus:outline-none focus:border-green-500 flex-1">
                    <input type="text" name="password" required placeholder="Пароль"
                        class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-gray-100 placeholder-gray-500 focus:outline-none focus:border-green-500 flex-1">
                    <select name="role" class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-gray-100 focus:outline-none focus:border-green-500">
                        <?php foreach (array_keys(Setting\Route\Function\Controllers\Admin\Admin::ROLES) as $r): ?>
                            <option value="<?= $r ?>"><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="px-4 py-2 rounded-xl bg-violet-600 text-white font-semibold">Добавить</button>
                </form>
                <div class="text-xs text-gray-500 mt-2">Права выдаются по роли: manager — поддержка (чат, пробные, рефералка), moderator — модерация (чат, логи), admin — всё. Тонкая настройка — галочками в таблице.</div>
                <h2 class="text-xl font-bold text-gray-100 py-6">Логи рабочих</h2>
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-sm text-gray-500">Чьи логи:</span>
                    <select id="wrk-filter" class="px-3 py-2 rounded-lg border text-sm text-gray-100 focus:outline-none focus:border-green-500">
                        <option value="">Все работники</option>
                    </select>
                </div>
                <div class="bg-black rounded-xl p-2 max-h-[40vw] overflow-scroll flex flex-col gap-0.5" data-wrk-log>
                    <div class="text-[13px] italic text-gray-500 px-2 py-4 text-center"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Загрузка журнала…</div>
                </div>
                <script>
                $(function () {//фильтр логов по работнику (WKL), все локально
                    const $sel = $('#wrk-filter');
                    if (!$sel.length) return;
                    $sel.on('change', function () {
                        const v = $sel.val();
                        let shown = 0;
                        $('[data-wkl]').each(function () {
                            const show = !v || $(this).attr('data-wkl') === v;
                            $(this).toggle(show);
                            if (show) shown++;
                        });
                        $('#wrk-empty').toggle(shown === 0);
                    });
                });
                </script>
                <!-- Модалка правки сотрудника -->
                <div data-staff-modal class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4">
                    <div data-staff-modal-bg class="absolute inset-0 bg-black/60"></div>
        <div class="relative bg-[#16181d] ring-1 ring-white/10 rounded-2xl w-full max-w-md overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                            <div class="font-bold text-white">Сотрудник <span data-staff-name class="font-mono text-gray-400"></span></div>
                            <button type="button" data-staff-close class="p-2 rounded-lg text-gray-400 hover:text-gray-200 hover:bg-white/10 cursor-pointer">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <form action="/admin/roles/update" method="POST" class="p-5 flex flex-col gap-3">
                            <input type="hidden" name="old_username" data-staff-old>
                            <label class="text-xs text-gray-400">Логин
                                <input name="username" required data-staff-login
                                    class="mt-1 w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 focus:outline-none focus:border-green-500 text-sm">
                            </label>
                            <label class="text-xs text-gray-400">Новый пароль (пусто — не менять)
                                <input name="password" type="text" autocomplete="new-password" placeholder="••••••"
                                    class="mt-1 w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 placeholder-gray-600 focus:outline-none focus:border-green-500 text-sm">
                            </label>
                            <label class="text-xs text-gray-400">Должность (права пересоберутся по шаблону)
                                <select name="role" data-staff-role
                                    class="mt-1 w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-100 focus:outline-none focus:border-green-500 text-sm"></select>
                            </label>
                            <div class="text-[11px] text-yellow-300/80">Сохранение завершит все сессии сотрудника — ему нужно войти заново.</div>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-500 text-white text-sm font-semibold">Сохранить</button>
                            </div>
                        </form>
                        <form action="/admin/roles/kill" method="POST" class="px-5 pb-5">
                            <input type="hidden" name="username" data-staff-kill>
                            <button class="w-full px-4 py-2 rounded-lg bg-yellow-500/10 ring-1 ring-yellow-500/30 text-yellow-300 hover:bg-yellow-500/20 text-sm font-semibold">Завершить все сессии</button>
                        </form>
                    </div>
                </div>
                <script defer>
                $(document).ready(function () {
                    var staffLoaded = false;
                    function staffVisible() { return $('[data-section="roles"]:not(.hidden)').length > 0; }
                    function staffLoad() {
                        if (staffLoaded || !staffVisible()) return;
                        staffLoaded = true;
                        $.ajax({
                            url: '/api/admin/staff', method: 'GET', dataType: 'json', timeout: 60000,
                            success: function (d) {
                                if (!d || d.status !== 'ok') return;
                                var perms = d.perms || {}, roles = d.roles || [];
                                var $tb = $('[data-staff-tbody]').empty();
                                (d.users || []).forEach(function (u) {
                                    var $tr = $('<tr>').addClass('border-b last:border-0');
                                    $tr.append($('<td>').addClass('p-3 font-semibold text-gray-100').text(u.username));
                                    Object.keys(perms).forEach(function (p) {
                                        var on = (u.permissions || []).indexOf(p) !== -1;
                                        var $f = $('<form>').attr({ action: '/admin/roles/perms', method: 'POST' });
                                        $f.append($('<input>').attr({ type: 'hidden', name: 'username', value: u.username }));
                                        (u.permissions || []).forEach(function (keep) {
                                            if (keep !== p) $f.append($('<input>').attr({ type: 'hidden', name: 'perms[]', value: keep }));
                                        });
                                        var $lab = $('<label>').addClass('relative inline-flex cursor-pointer items-center');
                                        $lab.append($('<input>').attr({ type: 'checkbox', name: 'perms[]', value: p }).addClass('peer sr-only').prop('checked', on).on('change', function () { $f.submit(); }));
                                        $lab.append($('<span>').addClass('h-6 w-11 rounded-full transition-colors ' + (on ? 'bg-green-500' : 'bg-gray-600')));
                                        $lab.append($('<span>').addClass('absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition-transform' + (on ? ' translate-x-5' : '')));
                                        $f.append($lab);
                                        $tr.append($('<td>').addClass('p-3 text-center').append($f));
                                    });
                                    $tr.append($('<td>').addClass('p-3 whitespace-nowrap')
                                        .append($('<button>').attr({ type: 'button', 'data-staff-edit': u.username, 'data-sr': u.role, title: 'Изменить сотрудника' }).addClass('w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/25 text-blue-400 hover:bg-blue-500/20 hover:text-blue-300 transition-all mr-2').append($('<i>').addClass('fa-solid fa-pen-to-square text-sm')))
                                        .append($('<form>').attr({ action: '/admin/roles/fire', method: 'POST' }).addClass('inline')
                                            .append($('<input>').attr({ type: 'hidden', name: 'username', value: u.username }))
                                            .append($('<button>').addClass('w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-500/10 ring-1 ring-red-500/25 text-red-400 hover:bg-red-500/20 hover:text-red-300 transition-all').attr('title', 'Уволить').append($('<i>').addClass('fa-solid fa-trash text-sm')))));
                                    $tb.append($tr);
                                });
                                var $sel = $('#wrk-filter');
                                (d.users || []).forEach(function (u) {
                                    $sel.append($('<option>').attr('value', u.username).text(u.username));
                                });
                                $('[data-wrk-log]').html(d.audit || '<div class="text-[13px] italic text-white">Записей пока нет.</div>');
                                var $rs = $('[data-staff-role]').empty();
                                roles.forEach(function (r) { $rs.append($('<option>').attr('value', r).text(r)); });
                            }
                        });
                    }
                    $(document).on('click', '[data-staff-edit]', function () {
                        var $b = $(this), name = $b.attr('data-staff-edit');
                        $('[data-staff-name]').text(name);
                        $('[data-staff-old]').val(name);
                        $('[data-staff-login]').val(name);
                        $('[data-staff-role]').val($b.attr('data-sr') || '');
                        $('[data-staff-kill]').val(name);
                        $('[data-staff-modal]').removeClass('hidden');
                    });
                    $(document).on('click', '[data-staff-close],[data-staff-modal-bg]', function () {
                        $('[data-staff-modal]').addClass('hidden');
                    });
                    $(document).on('click', '[data-toggle-section="roles"]', function () { setTimeout(staffLoad, 350); });
                    staffLoad();
                });
                </script>
            </section>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="roles">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <script defer>
                <?php
                $message_status = $_GET['message_status'] ?? null;
                $message_msg = $_GET['message_msg'] ?? null;
                if ($message_status && $message_msg)
                    echo "showNotification('" . addslashes($message_msg) . "', '" . $message_status . "');"; ?>

                // Показать уведомление
                function showNotification(msg, type = 'info') {
                    let $container = $('#notification-container');
                    if (!$container.length) {
                        $container = $('<div>', { id: 'notification-container', 'class': 'fixed right-2 top-2 z-[999] flex flex-col gap-2' }).appendTo($(document.body));
                    }
                    const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' };
                    const $element = $('<div>', {
                        'class': 'px-6 py-3 rounded-lg text-white z-50 transform translate-x-full transition-transform duration-300 ' + (colors[type] || colors.info),
                        html: '<i class="fa-solid fa-info-circle"></i> ' + msg
                    }).appendTo($container);
                    setTimeout(() => $element.removeClass('translate-x-full'), 100);
                    setTimeout(() => $element.addClass('translate-x-full'), 4100);
                    setTimeout(() => { $element.remove(); if (!$container.children().length) $container.remove(); }, 4400);
                }

                // Красный счётчик чата в меню: обновляем, даже когда раздел чата закрыт
                setInterval(function () {
                    if (!$('[data-chat-menu-badge]').length) return;
                    $.getJSON('/api/chat/dialogs')
                        .done(function (data) {
                            if (data.status !== 'ok' || !Array.isArray(data.dialogs)) return;
                            var total = 0;
                            data.dialogs.forEach(function (d) { if (!d.closed) total += (d.unread | 0); });
                            $('[data-chat-menu-badge]').each(function () {
                                $(this).text(total);
                                $(this).toggleClass('hidden', !total).toggleClass('inline-flex', !!total);
                            });
                        });
                }, 20000);

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
            <script defer>
                $(document).ready(function () {
                    $('[data-user-find]').hide();//скрываем информационные поля
                    $('[data-user-find-hours]').hide();//скрываем информационные поля
                    $('[data-user-find-minutes]').hide();//скрываем информационные поля
                    $('[data-delete-button]').hide();
                    $('[name="uniID"]').on('input', (event) => {
                        event.preventDefault();
                        $.ajax({
                            url: '/admin/getUser',
                            method: 'POST',
                            data: {
                                uniID: event.target.value
                            },
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
                                    $('[data-fuser-status]').addClass(response.data.status === 'on' ? 'bg-green-500/10' : 'bg-red-500/10');
                                    $('[data-fuser-subscription]').addClass(response.data.status === 'on' ? 'bg-green-500/10' : 'bg-red-500/10');
                                    $('[data-fuser-subscription]').text(response.data.subscription == '' ? '-' : response.data.subscription);
                                    $('[data-fuser-expires]').text(response.data.expiry ? new Date(response.data.expiry).toLocaleDateString('ru-RU', {
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric'
                                    }) : '-');
                                    $('[data-fuser-expires]').addClass(response.data.status === 'on' ? 'bg-green-500/10' : '');
                                    setTimeout(() => {
                                        $('[data-delete-button]').show(250);
                                    }, 250);
                                } else {
                                    $('[data-user-find]').hide(250);
                                    setTimeout(() => {
                                        $('[data-delete-button]').hide(250);
                                    }, 250);
                                }
                            }
                        });
                    });

                    // hours
                    $('[name="uniIDhours"]').on('input', (event) => {
                        event.preventDefault();
                        $.ajax({
                            url: '/admin/getUser',
                            method: 'POST',
                            data: {
                                uniID: event.target.value
                            },
                            success: function (response) {
                                if (response.status) {
                                    $('[data-user-find-hours]').show(250);
                                    //id
                                    $('[data-fuser-id-hours]').text(response.data.id);
                                    $('[data-fuser-id-hours]').addClass('bg-green-300');
                                    //--
                                    $('[data-fuser-name-hours]').text(response.data.first_name + ' ' + response.data.last_name);
                                    $('[data-fuser-uniID-hours]').text(response.data.uniID);
                                    $('[data-fuser-countdays-hours]').text(response.data.count_days == '' ? '-' : response.data.count_days);
                                    // status
                                    $('[data-fuser-status-hours]').text(response.data.status);
                                    $('[data-fuser-status-hours]').addClass(response.data.status === 'on' ? 'bg-green-500/10' : 'bg-red-500/10');
                                    $('[data-fuser-subscription-hours]').addClass(response.data.status === 'on' ? 'bg-green-500/10' : 'bg-red-500/10');
                                    $('[data-fuser-subscription-hours]').text(response.data.subscription == '' ? '-' : response.data.subscription);
                                    $('[data-fuser-expires-hours]').text(response.data.expiry ? new Date(response.data.expiry).toLocaleDateString('ru-RU', {
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric'
                                    }) : '-');
                                    $('[data-fuser-expires-hours]').addClass(response.data.status === 'on' ? 'bg-green-500/10' : '');
                                } else {
                                    $('[data-user-find-hours]').hide(250);
                                }
                            }
                        });
                    });

                    // minutes
                    $('[name="uniIDMinutes"]').on('input', (event) => {
                        event.preventDefault();
                        $.ajax({
                            url: '/admin/getUser',
                            method: 'POST',
                            data: {
                                uniID: event.target.value
                            },
                            success: function (response) {
                                if (response.status) {
                                    $('[data-user-find-minutes]').show(250);
                                    //id
                                    $('[data-fuser-id-minutes]').text(response.data.id);
                                    $('[data-fuser-id-minutes]').addClass('bg-green-300');
                                    //--
                                    $('[data-fuser-name-minutes]').text(response.data.first_name + ' ' + response.data.last_name);
                                    $('[data-fuser-uniID-minutes]').text(response.data.uniID);
                                    $('[data-fuser-countdays-minutes]').text(response.data.count_days == '' ? '-' : response.data.count_days);
                                    // status
                                    $('[data-fuser-status-minutes]').text(response.data.status);
                                    $('[data-fuser-status-minutes]').addClass(response.data.status === 'on' ? 'bg-green-500/10' : 'bg-red-500/10');
                                    $('[data-fuser-subscription-minutes]').addClass(response.data.status === 'on' ? 'bg-green-500/10' : 'bg-red-500/10');
                                    $('[data-fuser-subscription-minutes]').text(response.data.subscription == '' ? '-' : response.data.subscription);
                                    $('[data-fuser-expires-minutes]').text(response.data.expiry ? new Date(response.data.expiry).toLocaleDateString('ru-RU', {
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric'
                                    }) : '-');
                                    $('[data-fuser-expires-minutes]').addClass(response.data.status === 'on' ? 'bg-green-500/10' : '');
                                } else {
                                    $('[data-user-find-minutes]').hide(250);
                                }
                            }
                        });
                    });
                    
                });
            </script>
            <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/main/main.js<?= '?v=' . $site['versionApp'] ?>" defer></script>
            <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/auth/admin/main.js<?= '?v=' . $site['versionApp'] ?>" defer></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // Ленивые графики: строятся из /api/admin/stats (см. загрузчик ниже)
                var __chartsBuilt = {};
                function buildAdminCharts(client, financial, roi365) {
                $('[data-chart="chart_online"]:visible').each(function () {
                    var t = window.__overviewTotals || {};
                    if (!t.clients) return;//ждём данные онлайна, без заглушек
                    if (this.__built) return; this.__built = true;
                    var on = Number(t.online_now || 0), all = Number(t.clients || 0);
                    new Chart(this, {
                        type: 'doughnut',
                        data: {
                            labels: ['В сети', 'Не в сети'],
                            datasets: [{
                                data: [on, Math.max(0, all - on)],
                                backgroundColor: ['rgba(52,211,153,0.85)', 'rgba(148,163,184,0.25)'],
                                borderColor: '#16181d',
                                borderWidth: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: true, position: 'bottom', labels: { color: '#9aa4b2', boxWidth: 12 } }
                            }
                        }
                    });
                });
                $('[data-chart="chart_clients"]:visible').each(function () {
                    if (this.__built) return; this.__built = true;
                    new Chart(this, {
                        type: 'polarArea',
                        data: {
                            labels: ['С подписками', 'Без подписок'],
                            datasets: [{
                                label: 'Пользователей',
                                data: [client.usersWithSubscriptions || 0, client.usersWithoutSubscriptions || 0],
                                backgroundColor: ['rgba(47,129,247,0.75)', 'rgba(239,68,68,0.75)'],
                                borderColor: '#fff',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(255,255,255,0.06)' },
                                    ticks: { color: '#9aa4b2' },
                                    grid: { color: 'rgba(255,255,255,0.06)' }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: { color: '#9aa4b2' }
                                },
                                title: {
                                    display: true,
                                    text: 'График подписок',
                                    color: '#e6e9ee'
                                }
                            }
                        }
                    });
                });

                // График прибыли по месяцам
                $('[data-chart="chart_revenue_monthly"]:visible').each(function () {
                    if (this.__built) return; this.__built = true;
                    var mrc = ((roi365 || {}).monthly && roi365.monthly.length) ? roi365.monthly : (financial.monthlyRevenueChart || []);
                    new Chart(this, {
                        type: 'line',
                        data: {
                            labels: mrc.map(function (r) { return r.month; }),
                            datasets: [{
                                label: 'Прибыль (₽)',
                                data: mrc.map(function (r) { return r.revenue; }),
                                borderColor: 'rgb(34, 197, 94)',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(255,255,255,0.06)' },
                                    ticks: {
                                        callback: function (value) {
                                            return value.toLocaleString('ru-RU') + ' ₽';
                                        },
                                        color: '#9aa4b2'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'center',
                                    labels: { color: '#9aa4b2' }
                                },
                                title: {
                                    display: true,
                                    text: 'График прибыли по месяцам',
                                    color: '#e6e9ee'
                                },
                                tooltip: {
                                    backgroundColor: '#0d1117', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1,
                                    titleColor: '#9aa4b2', bodyColor: '#e6e9ee',
                                    callbacks: { label: function (c) { return ' ' + c.dataset.label + ': ' + Number(c.parsed.y || 0).toLocaleString('ru-RU') + ' ₽'; } }
                                }
                            }
                        }
                    });
                });

                // График статистика количество пользователей
                $('[data-chart="chart_users_monthly"]:visible').each(function () {
                    if (this.__built) return; this.__built = true;
                    var muc = financial.monthlyUsersChart || [];
                    new Chart(this, {
                        type: 'line',
                        data: {
                            labels: muc.map(function (r) { return r.month; }),
                            datasets: [{
                                label: 'Пользователей',
                                data: muc.map(function (r) { return r.users_count; }),
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
                                    grid: { color: 'rgba(255,255,255,0.06)' },
                                    ticks: {
                                        callback: function (value) {
                                            return value.toLocaleString('ru-RU') + ' чел.';
                                        },
                                        color: '#9aa4b2'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: { color: '#9aa4b2' }
                                },
                                title: {
                                    display: true,
                                    text: 'Статистика новых пользователей',
                                    color: '#e6e9ee'
                                }
                            }
                        }
                    });
                });
                }// buildAdminCharts
            </script>
            <script defer>
                // Ленивая статистика: цифры и графики подтягиваются AJAX,
                // кэшируются до перезагрузки страницы (повторно не запрашиваем).
                // Деньги — только из кассы (/api/admin/roi), люди — из БД (/api/admin/stats).
                $(document).ready(function () {
                    window.__adminStats = null;
                    window.__roi30 = null;
                    window.__roi365 = null;
                    function fillStats(client) {
                        var money = function (v) { return Number(v || 0).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
                        $('[data-st]').each(function () {
                            var k = $(this).attr('data-st'), v = client[k];
                            $(this).text(k === 'avgPrice' || k === 'maxPrice' ? money(v) : (v ?? '—'));
                        });
                        var tot = Number(client.totalUsers || 0);
                        $('[data-st="convRate"]').text(tot > 0 ? (Number(client.usersWithSubscriptions || 0) / tot * 100).toFixed(1) + '%' : '—');
                        $('[data-st]').removeClass('animate-pulse');
                        $.ajax({
                            url: '/api/admin/overview', method: 'GET', dataType: 'json', timeout: 60000,
                            success: function (o) {
                                if (o && o.status === 'ok') {
                                    $('[data-m-online]').text((o.totals || {}).online_now ?? '—');
                                    window.__overviewTotals = o.totals || {};
                                    if (window.__adminStats) buildAdminCharts(window.__adminStats.client || {}, window.__adminStats.financial || {});
                                } else $('[data-m-online]').text('—');
                            },
                            error: function () { $('[data-m-online]').text('—'); }
                        });
                    }
                    function fillKassa(r) {
                        if (!r || r.status !== 'ok') return;
                        var money = function (v) { return Number(v || 0).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
                        $('[data-kr]').each(function () {
                            var v = r[$(this).attr('data-kr')];
                            $(this).text(v == null ? '—' : money(v));
                        });
                        $('[data-kr]').removeClass('animate-pulse');
                    }
                    function ensureCharts() {
                        var d = window.__adminStats;
                        if (d) buildAdminCharts(d.client || {}, d.financial || {}, window.__roi365);
                        if ($('[data-section="charts"]:not(.hidden)').length && !window.__plansBuilt) {
                            buildPlansChart((window.__roi365 || {}).by_tariff || [], (window.__adminStats || {}).financial);
                        }
                    }
                    $('[data-st],[data-kr]').addClass('animate-pulse');
                    $.ajax({
                        url: '/api/admin/stats', method: 'GET', dataType: 'json', timeout: 30000,
                        success: function (d) {
                            if (!d || d.status !== 'ok') return;
                            window.__adminStats = d;
                            fillStats(d.client || {});
                            ensureCharts();
                        }
                    });
                    $.ajax({
                        url: '/api/admin/roi?days=30', method: 'GET', dataType: 'json', timeout: 120000,
                        success: function (r) { window.__roi30 = r; fillKassa(r); }
                    });
                    $.ajax({
                        url: '/api/admin/roi?days=365', method: 'GET', dataType: 'json', timeout: 120000,
                        success: function (r) { window.__roi365 = r; ensureCharts(); }
                    });
                    $(document).on('click', '[data-toggle-section]', function () { setTimeout(ensureCharts, 350); });
                });
            </script>
            <!-- Глобальная модалка чека (чат + ROI) -->
    <!-- Модалка чека: живое свидетельство оплаты из кассы -->
    <div id="gl-receipt" data-admin-receipt class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4">
        <div data-admin-receipt-bg class="absolute inset-0 bg-black/60"></div>
        <div class="relative bg-[#16181d] ring-1 ring-white/10 rounded-2xl w-full max-w-md md:max-w-2xl lg:max-w-3xl max-h-[88vh] overflow-y-auto shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                <div class="font-bold text-white">Чек оплаты</div>
                <button type="button" data-admin-receipt-close class="p-2 rounded-lg text-gray-400 hover:text-gray-200 hover:bg-white/10 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div id="gl-receipt-body" class="p-5 text-sm text-gray-400">Загрузка...</div>
        </div>
    </div>
        </main>
    </div>
</body>

</html>