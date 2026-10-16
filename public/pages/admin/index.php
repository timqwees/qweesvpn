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

<body class="bg-[#0f1115] text-gray-200 antialiased">
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
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
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
                                <i class="fa-solid fa-wallet text-green-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">Прибыль в месяц</div>
                                <div class="text-2xl font-bold text-green-400"><span data-st="mrr">…</span>₽</div>
                            </div>
                        </div>
                        <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user-plus text-purple-400"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500">Новых за сегодня</div>
                                <div class="text-2xl font-bold text-purple-400"><span data-st="dailyNewUsers">…</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Список новых пользователей за сегодня -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-[11px] uppercase tracking-wider text-gray-500">Новые пользователи сегодня</h2>
                        <span class="text-xs text-gray-500" data-today-users-count>0</span>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-3">
                        <div class="flex flex-col gap-1.5 max-h-56 overflow-y-auto" data-today-users>
                            <div class="text-gray-500 text-xs">Загрузка...</div>
                        </div>
                    </div>
                </div>

                <!-- KPI: подписки -->
                <!-- Модальное окно сводки пользователя -->
                <div id="user-summary-modal" class="hidden fixed inset-0 z-[130] flex items-center justify-center p-4">
                    <div id="user-summary-modal-bg" class="absolute inset-0 bg-black/60"></div>
                    <div class="relative bg-[#16181d] rounded-xl ring-1 ring-white/10 w-full max-w-lg z-10 overflow-hidden">
                        <div class="flex items-center justify-between p-4 border-b border-white/[0.07]">
                            <div class="font-semibold text-white">Сводка пользователя</div>
                            <button type="button" id="user-summary-close"
                                class="p-2 rounded-lg text-gray-400 hover:text-gray-200 hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div id="user-summary-content" class="max-h-[70vh] overflow-y-auto"></div>
                    </div>
                </div>
                <div>
                    <h2 class="text-[11px] uppercase tracking-wider text-gray-500 mb-3">Подписки</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
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
                    <?php if ($groups->isPermission($adminUsername,'logs')): ?>
                    <button data-toggle-section="logs" class="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-gray-300 text-sm hover:border-green-400 hover:text-green-400 transition-all whitespace-nowrap">
                        <i class="fa-solid fa-terminal text-gray-500"></i><span>Логи</span>
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

                <!-- График новых пользователей -->
                <style>
                    .q-tip { position: relative; display: inline-flex; cursor: help; outline: none; }
                    .q-tip::after {
                        content: attr(data-tip); position: absolute; right: -4px; top: calc(100% + 8px);
                        width: max-content; max-width: 240px; z-index: 30;
                        background: #0d1117; color: #e6e9ee; border: 1px solid rgba(255,255,255,.1);
                        border-radius: 10px; padding: 8px 10px; font-size: 11px; line-height: 1.45; font-weight: 400;
                        opacity: 0; visibility: hidden; transform: translateY(-3px); transition: opacity .15s, transform .15s;
                        pointer-events: none; white-space: normal; text-align: left;
                    }
                    .q-tip:hover::after, .q-tip:focus::after, .q-tip:focus-within::after, .q-tip:active::after { opacity: 1; visibility: visible; transform: none; }
                </style>
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-[11px] uppercase tracking-wider text-gray-500">Новые пользователи — пришло в месяц</h2>
                        <span class="q-tip" tabindex="0" data-tip="Количество новых пользователей по месяцам. Данные из /api/admin/stats (ежечасный кэш)."><i class="fa-solid fa-circle-info text-gray-500 text-xs"></i></span>
                    </div>
                    <div class="h-[220px] min-h-[200px] w-full relative">
                        <canvas data-chart="chart_new_users" class="w-full h-full"></canvas>
                        <div data-chart-empty="chart_new_users" class="hidden absolute inset-0 flex items-center justify-center text-sm text-gray-500">Собираю данные…</div>
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
                         class="text-xs font-medium px-3 py-2 min-h-[44px] min-w-[44px] rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10 transition-colors">
                        <i class="fa-solid fa-rotate-right mr-1"></i>Обновить
                    </button>
                </div>

                <!-- Онлайн и пользователи -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">В сети сейчас</div>
                         <div class="text-2xl sm:text-3xl font-bold text-emerald-400" data-ov-online data-lazy-hint="monitoring" title="Собираются при посещении раздела Мониторинг">—</div>
                         <div class="relative w-12 h-12 sm:w-16 sm:h-16 mt-2">
                             <div data-lazy-hint="monitoring" title="Собираются при посещении раздела Мониторинг" class="absolute inset-0 flex items-center justify-center text-xs text-gray-400">—</div>
                             <canvas data-chart="chart_online_main" class="relative z-10 w-full h-full"></canvas>
                         </div>
                    </div>
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Средний за сутки</div>
                        <div class="text-2xl sm:text-3xl font-bold text-cyan-400" data-ov-avg data-lazy-hint="monitoring" title="Собираются при посещении раздела Мониторинг">—</div>
                    </div>
                        <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                            <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Пик сегодня</div>
                            <div class="text-2xl sm:text-3xl font-bold text-white" data-ov-peak data-lazy-hint="monitoring" title="Собираются при посещении раздела Мониторинг">—</div>
                        </div>
                        <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                            <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Показ скорости в мониторинге</div>
                            <label class="relative inline-flex items-center cursor-pointer mt-1">
                                <input type="checkbox" id="speed-toggle" class="sr-only" />
                                <span class="h-6 w-11 rounded-full bg-gray-600 peer-checked:bg-green-500 transition-colors inline-block"></span>
                                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                            </label>
                            <span class="text-xs text-gray-400 mt-1 block" id="speed-toggle-label">вкл.</span>
                        </div>
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Пробных подписей</div>
                        <div class="text-2xl sm:text-3xl font-bold text-amber-400" data-ov-trials data-lazy-hint="monitoring" title="Собираются при посещении раздела Мониторинг">—</div>
                        <div class="text-xs text-gray-500 mt-1">в течение 30 дней</div>
                    </div>
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Активные подписки</div>
                        <div class="text-2xl sm:text-3xl font-bold text-emerald-400" data-ov-active data-lazy-hint="monitoring" title="Собираются при посещении раздела Мониторинг">—</div>
                    </div>
                    <div class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4">
                        <div class="text-[11px] uppercase tracking-wider text-gray-500 mb-1">Пользователей</div>
                        <div class="flex items-end gap-3">
                            <div class="text-2xl sm:text-3xl font-bold text-white" data-ov-users data-lazy-hint="monitoring" title="Собираются при посещении раздела Мониторинг">—</div>
                            <div class="h-8 w-24">
                                <canvas data-chart="chart_users_spark" class="w-full h-full"></canvas>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">+<span data-ov-new>0</span> сегодня</div>
                    </div>
                </div>

                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-[11px] uppercase tracking-wider text-gray-500">Новые пользователи</h2>
                        <span class="q-tip" tabindex="0" data-tip="Регистрация новых пользователей по месяцам. Lazy: данные появятся после входа в раздел Мониторинг."><i class="fa-solid fa-circle-info text-gray-500 text-xs"></i></span>
                    </div>
                    <div class="h-[280px] min-h-[240px] w-full">
                        <canvas data-chart="chart_users_monthly" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Трафик -->
                <div>
                    <h2 class="text-[11px] uppercase tracking-wider text-gray-500 mb-3">Трафик (↓ + ↑)</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
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

                <!-- Клиенты x-ui -->
<div class="xui rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-3" data-ov-panel-clients>
                    <style>
                    .client-traffic-cell { box-sizing: border-box; background: rgba(255,255,255,0.04); border-radius: 999px; align-items: center; gap: 8px; width: 100%; min-width: 0; padding: 2px 10px; display: flex; }
                    .client-traffic-cell-used { font-variant-numeric: tabular-nums; white-space: nowrap; flex: 0 0 72px; min-width: 72px; font-size: 12px; text-align: end; color: #e5e7eb; }
                    .client-traffic-cell-bar { flex: 60px; min-width: 48px; }
                    .client-traffic-cell-bar .ant-progress-rail { height: 8px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden; }
                    .client-traffic-cell-bar .ant-progress-track { height: 100%; background: #22c55e; }
                    .client-traffic-cell.is-unlimited .client-traffic-cell-bar .ant-progress-track { background: #7c3aed8c; border: 1px solid #7c3aed59; }
                    </style>
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Клиенты x-ui панелей</h3>
                        <span class="text-xs text-gray-500">обновлено: <span data-ov-pc-updated>—</span></span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-center mb-3">
                        <div class="bg-white/5 rounded-lg px-3 py-2.5">
                            <div class="text-2xl font-bold text-gray-200" data-ov-pc-total>—</div>
                            <div class="text-[10px] text-gray-500">Клиенты</div>
                        </div>
                        <div class="bg-white/5 rounded-lg px-3 py-2.5">
                            <div class="text-2xl font-bold text-emerald-400" data-ov-pc-on>—</div>
                            <div class="text-[10px] text-gray-500">В сети</div>
                        </div>
                        <div class="bg-white/5 rounded-lg px-3 py-2.5">
                            <div class="text-2xl font-bold text-red-400" data-ov-pc-exhausted>—</div>
                            <div class="text-[10px] text-gray-500">Исчерпано</div>
                        </div>
                        <div class="bg-white/5 rounded-lg px-3 py-2.5">
                            <div class="text-2xl font-bold text-orange-400" data-ov-pc-near>—</div>
                            <div class="text-[10px] text-gray-500">Почти исчерпано</div>
                        </div>
                        <div class="bg-white/5 rounded-lg px-3 py-2.5">
                            <div class="text-2xl font-bold text-gray-400" data-ov-pc-off>—</div>
                            <div class="text-[10px] text-gray-500">Отключено</div>
                        </div>
                        <div class="bg-white/5 rounded-lg px-3 py-2.5">
                            <div class="text-2xl font-bold text-emerald-300" data-ov-pc-active>—</div>
                            <div class="text-[10px] text-gray-500">Активна</div>
                        </div>
                    </div>
                    <style>
                    :root {
                        --ant-blue-1: #111a2c; --ant-blue-7: #3c89e8; --ant-green-1: #162312; --ant-green-7: #6abe39;
                        --ant-orange-1: #2b1910; --ant-purple-1: #1a1325; --ant-info-bg: #111a2c;
                        --ant-color-text-secondary: rgba(255,255,255,.65); --ant-color-warning-bg: #2b2111; --ant-color-warning-text: #d89614;
                        --ant-color-fill-tertiary: rgba(255,255,255,.08); --ant-color-success-bg: #162312; --ant-color-text: rgba(255,255,255,.85);
                        --ant-color-border: #50566e; --ant-color-fill-quaternary: rgba(255,255,255,.04); --ant-border-radius: 6px;
                    }
.client-traffic-cell {
    box-sizing: border-box;
    background: var(--ant-color-fill-quaternary, rgba(255,255,255,.04));
    border-radius: 999px;
    align-items: center;
    gap: 8px;
    width: 100%;
    min-width: 0;
    padding: 2px 10px;
    display: flex;
}
    .ov-pc-wrapper {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
        min-width: min-content;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
    }
    .table-cell-uniform {
        background: rgba(255,255,255,0.05);
        border-radius: 6px;
        padding: 4px 8px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 24px;
        white-space: nowrap;
    }
 .table-speed-tag {
        background: rgba(6,115,230,0.12);
        color: #3b82f6;
        border-radius: 6px;
        font-size: 11px;
        padding: 2px 10px;
        min-width: 140px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .type-free {
        background: var(--ant-blue-1);
        color: var(--ant-blue-7);
        border-radius: var(--ant-border-radius);
        font-size: 11px;
        padding: 2px 10px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .type-paid {
        background: var(--ant-green-1);
        color: var(--ant-green-7);
        border-radius: var(--ant-border-radius);
        font-size: 11px;
        padding: 2px 10px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .type-trial {
        background: var(--ant-orange-1);
        color: #d89614;
        border-radius: var(--ant-border-radius);
        font-size: 11px;
        padding: 2px 10px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .type-bonus {
        background: var(--ant-purple-1);
        color: var(--ant-purple-7);
        border-radius: var(--ant-border-radius);
        font-size: 11px;
        padding: 2px 10px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .table-speed-tag-empty {
        background: var(--ant-info-bg);
        color: var(--ant-color-text-secondary);
        border-radius: var(--ant-border-radius);
        font-size: 11px;
        font-weight: 500;
        padding: 2px 10px;
        min-width: 140px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .duration-cell-value {
        background: var(--ant-green-1);
        color: var(--ant-green-7);
        border-radius: 6px;
        font-size: 12px;
        padding: 2px 10px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    .duration-cell-warning {
        background: var(--ant-color-warning-bg);
        color: var(--ant-color-warning-text);
        border-radius: 6px;
        font-size: 12px;
        padding: 2px 10px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    .dot-tag {
        background: var(--ant-color-fill-tertiary);
        border-radius: 999px;
        padding: 2px 10px;
        font-size: 11px;
        font-weight: 500;
    }
    .dot-tag.xui.online {
        background: var(--ant-color-success-bg);
        color: var(--ant-green-7);
    }
    .dot-tag.xui.offline {
        background: var(--ant-color-fill-tertiary);
        color: var(--ant-color-text-secondary);
    }
    .online-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        margin-right: 5px;
        flex-shrink: 0;
    }
    .dot-tag.xui.online .online-dot {
        background: var(--ant-green-7);
        box-shadow: 0 0 6px var(--ant-green-7);
    }
    .dot-tag.xui.offline .online-dot {
        background: var(--ant-color-text-secondary);
    }
.email-cell {
    flex-direction: column;
    display: flex;
}
/* Ant Design v5 table emulation */
[data-ov-pc-table] {
    table-layout: auto;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 6px;
    overflow: hidden;
}
.ov-pc-wrapper::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.ov-pc-wrapper::-webkit-scrollbar-track {
    background: transparent;
}
.ov-pc-wrapper::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 3px;
}
.ov-pc-wrapper::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.25);
}
[data-ov-pc-table] .ant-table-thead {
    background: var(--ant-color-bg-container);
    border-bottom: 1px solid var(--ant-color-border-secondary);
}
[data-ov-pc-tbody].ant-table-tbody tr {
    background: transparent;
    border-bottom: 1px solid var(--ant-color-border-secondary);
}
[data-ov-pc-tbody].ant-table-tbody tr:last-child {
    border-bottom: none;
}
[data-ov-pc-tbody].ant-table-tbody tr.ant-table-cell-row-hover {
}
[data-ov-pc-tbody].ant-table-tbody tr:nth-child(even) {
    background: rgba(255, 255, 255, 0.015);
}
[data-ov-pc-table] .ant-table-cell {
    padding: 8px 12px;
    border: 1px solid transparent;
    text-align: unset;
    vertical-align: middle;
}
[data-ov-pc-table] .ant-table-cell-row-hover {
}
.ant-table-measure-row {
    height: 0;
    overflow: hidden;
    visibility: hidden;
    font-size: 0;
}
/* Cell uniform tags */
.client-traffic-cell {
    box-sizing: border-box;
    background: var(--ant-color-fill-quaternary, #1a1d23);
    border-radius: 999px;
    align-items: center;
    gap: 8px;
    width: 100%;
    min-width: 0;
    padding: 2px 10px;
    display: flex;
}
.client-traffic-cell.is-unlimited {
    background: rgba(110, 110, 120, 0.3);
}
.client-traffic-cell-limit {
    font-size: 12px;
    color: #9aa0a6;
    white-space: nowrap;
}
.client-traffic-cell-used {
    font-size: 12px;
    color: var(--ant-color-text);
.ant-progress-rail {
    width: 100%;
    height: 6px;
    background: var(--ant-color-border);
    border-radius: 999px;
    overflow: hidden;
    margin: 0 8px;
    flex-shrink: 0;
}
.ant-progress-track {
    height: 100%;
    background: var(--ant-blue-7);
    border-radius: 999px;
    transition: width 320ms ease;
}
.ant-progress.is-unlimited .ant-progress-track {
    width: 100%;
    background: var(--ant-color-text-quaternary);
}
/* Action buttons in Actions column */
.pc-actions {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    justify-content: center;
}
    .pc-actions .pc-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        background: var(--ant-color-fill-tertiary);
        color: var(--ant-color-text-secondary);
        border: 1px solid var(--ant-color-border-secondary);
        cursor: pointer;
        font-size: 11px;
        transition: all 150ms ease;
    }
    .pc-actions .pc-btn:hover {
        background: rgba(58, 139, 232, 0.2);
        color: var(--ant-blue-7);
    }
    .pc-actions .pc-btn-danger:hover {
        background: var(--ant-color-error-bg);
        color: var(--ant-color-error-text);
    }
/* Checkbox column cell */
.pc-checkbox-cell {
    text-align: center;
    padding: 0 !important;
}
/* ant-switch for Включён */
.pc-switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}
.pc-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
    .pc-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--ant-color-border);
        border-radius: 999px;
        transition: 0.2s;
    }
    .pc-slider:before {
        content: "";
        position: absolute;
        height: 16px;
        width: 16px;
        left: 2px;
        bottom: 2px;
        background: var(--ant-color-bg-container);
        border-radius: 50%;
        transition: 0.2s;
    }
    .pc-switch input:checked + .pc-slider {
        background: var(--ant-blue-7);
    }
.pc-switch input:checked + .pc-slider:before {
    transform: translateX(20px);
}
/* Server tag */
    .server-tag {
        background: var(--ant-color-bg-container);
        border: 1px solid var(--ant-color-border-secondary);
        color: var(--ant-color-text);
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 11px;
        font-family: monospace;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .server-tag .server-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--ant-green-7);
        box-shadow: 0 0 6px var(--ant-green-7);
    }
.server-tag.offline .server-dot {
    background: #64748b;
    box-shadow: none;
}
    /* Group tag - авто-ширина с минимумом 50px, использует цвета из duration-cell-value / table-speed-tag */
    .group-tag {
        min-width: 50px !important;
        border-radius: 6px;
        font-size: 12px;
        padding: 2px 10px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    .group-tag-empty {
        background: var(--ant-color-fill-tertiary);
        border: 1px solid var(--ant-color-border-secondary);
        color: var(--ant-color-text-secondary);
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
/* Incoming count tag */
.inbound-tag {
    background: rgba(12, 26, 46, 0.3);
    border: 1px solid var(--ant-color-primary-border);
    color: var(--ant-blue-8);
    border-radius: 4px;
     padding: 2px 8px;
     font-size: 11px;
     display: inline-flex;
     align-items: center;
     justify-content: center;
}
/* Empty state */
.pc-empty {
    text-align: center;
    padding: 20px;
    color: #64748b;
    font-size: 12px;
}
</style>
                    <div class="ov-pc-wrapper">
                        <table class="w-full text-xs whitespace-nowrap ant-table ant-table-default ant-table-scroll-position-start ant-table-borderless ant-table-no-borders" data-ov-pc-table>
                            <colgroup>
                                <col class="w-[90px]">
                                <col class="w-[220px]">
                                <col class="w-[150px]">
                                <col class="w-[300px]">
                                <col class="w-[216px]">
                                <col class="w-[170px]">
                            </colgroup>
                            <thead class="ant-table-thead">
                                <tr class="ant-table-row">
                                    <th class="ant-table-cell text-center">В сети</th>
                                    <th class="ant-table-cell text-center">Клиент</th>
                                    <th class="ant-table-cell text-center">Группа</th>
                                    <th class="ant-table-cell text-center">Трафик</th>
                                    <th class="ant-table-cell text-center speed-col">Скорость</th>
                                    <th class="ant-table-cell text-center">Длительность</th>
                                    <!-- <th class="ant-table-cell text-center">Привязанные входящие</th> -->
                                </tr>
                            </thead>
                            <tbody class="ant-table-tbody" data-ov-pc-tbody></tbody>
                        </table>
                    </div>

                <!-- Серверы -->
                <div data-ov-servers class="flex flex-col gap-4"></div>
                </div>
            </section>
            <script defer>
                $(document).ready(function () {
                    window.__sectionLoaded = window.__sectionLoaded || {};
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
                        pairs = pairs || [];
                        if (!pairs.length) return;
                        var el = document.getElementById(id);
                        if (!el || typeof Chart === 'undefined') return;
                        ovDestroyChart(id);
                        var labels = pairs.map(function (p) { return p[0]; });
                        var vals = pairs.map(function (p) { return Number((p && p[1]) || 0); });
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
                        var vs = pairs.map(function (p) { return Number((p && p[1]) || 0); });
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
                        ups = ups || []; downs = downs || [];
                        if (!ups.length && !downs.length) return;
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
            : kind === 'blue'
                ? 'bg-blue-500/10 text-blue-400 ring-1 ring-blue-500/25'
                : 'bg-yellow-500/10 text-yellow-400 ring-1 ring-yellow-500/25';
                        $b.attr('class', 'text-xs font-medium px-2.5 py-1 rounded-full ' + cls).text(text);
                    }
                    function renderOverview(d) {
                        if (!d || d.status !== 'ok') { ovBadge('red', 'Нет данных'); return; }
                        var t = d.totals || {}, u = d.users || {}, tr = d.traffic || {};
                        $('[data-ov-online]').text(t.online_now ?? '—');
                        $('[data-ov-peak]').text(t.peak_today ?? '—');
                        $('[data-ov-avg]').text(t.online_avg != null ? t.online_avg : '—');
                        $('[data-ov-active]').text(u.active ?? '—');
                $('[data-ov-trials]').text(d.trials_count ?? 0);
                        $('[data-ov-users]').text(u.total ?? '—');
                        $('[data-ov-new]').text(u.new_today ?? 0);
                    lazyHints();
                        $('[data-ov-t-total]').text(ovBytes((t.up_total || 0) + (t.down_total || 0)));
                        $('[data-ov-t-total-up]').text(ovBytes(t.up_total || 0));
                        $('[data-ov-t-total-down]').text(ovBytes(t.down_total || 0));
                        ovPeriod('[data-ov-t-24h]', tr['24h']);
                        ovPeriod('[data-ov-t-7d]', tr['7d']);
                        ovPeriod('[data-ov-t-30d]', tr['30d']);
                renderMainCharts(t);
                lazyHints();
                if (d.stale) ovBadge('red', 'Панель недоступна');
                else if (d.collecting) ovBadge('blue', 'История');
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
                            $c.append($('<div>').addClass('text-2xl sm:text-3xl font-bold text-white').html(big + ' <span class="text-sm font-medium text-gray-500">' + suffix + '</span>'));
                            $c.append($('<div>').addClass('text-xs text-gray-400 truncate').text(sub || ''));
                                $c.append($('<div>').addClass('flex justify-between text-[10px] sm:text-[11px] text-gray-500')
                                .append($('<span>').text('СРЕДНЕЕ ' + (st.avg === null ? '—' : (Math.round(st.avg * 10) / 10) + suffix)))
                                .append($('<span>').text('ПИК ' + (st.peak === null ? '—' : (Math.round(st.peak * 10) / 10) + suffix))));
                            $c.append($('<div>').addClass('h-14').append($('<canvas>').attr('id', idp).css({ width: '100%', height: '100%' })));
                            ovDrawQueue.push({ id: idp, pairs: histPairs, color: color });
                            return $c;
                        }
                        (d.servers || []).forEach(function (s) {
                            var p = 'ovc_' + String(s.code).replace(/[^a-z0-9]/gi, '') + '_';
                            var $srv = $('<div>').addClass('flex flex-col gap-4');
                            $srv.append($('<div>').addClass('text-[10px] sm:text-[11px] uppercase tracking-wider text-gray-500').text('Сервер · ' + (s.country || s.code) + ' · ' + s.code));
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
                            var $row = $('<div>').addClass('grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4');
                            $row.append(ovMetricCard(p + 'cpu', 'ЦП', (s.cpu ?? '—'), s.cpu_sub || '', h.cpu || [], '%', '#2f81f7'));
                            $row.append(ovMetricCard(p + 'mem', 'Память', (s.mem_total ? (Math.round(memPct * 10) / 10) : '—'), s.mem_h || '', h.mem || [], '%', '#2f81f7'));
                            var $swap = $('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-1 min-w-0');
                            $swap.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500').text('Подкачка'));
                             $swap.append($('<div>').addClass('text-2xl sm:text-3xl font-bold text-white').html((s.swap_total ? (Math.round(swapPct * 10) / 10) : '0') + ' <span class="text-sm font-medium text-gray-500">%</span>'));
                            $swap.append($('<div>').addClass('text-xs text-gray-400 truncate').text(s.swap_h || ''));
                            $row.append($swap);
                            var $disk = $('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-1 min-w-0');
                            $disk.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500').text('Диск'));
                            $disk.append($('<div>').addClass('text-2xl sm:text-3xl font-bold text-white').html((s.disk_total ? (Math.round(diskPct * 10) / 10) : '—') + ' <span class="text-sm font-medium text-gray-500">%</span>'));
                            $disk.append($('<div>').addClass('text-xs text-gray-400 truncate').text(s.disk_h || ''));
                            $disk.append($('<div>').addClass('flex justify-between text-[11px] text-gray-500').append($('<span>').text('СВОБОДНО ' + (s.disk_free_h || '—'))));
                            $row.append($disk);
                            $srv.append($row);
                            // Ряд 2: трафик + соединения
                            var turnSpeedShow = !!d.turn_speed_show;
                            var $row2 = $('<div>').addClass('grid grid-cols-1 lg:grid-cols-3 gap-4');
                            var $tr = $('<div>').addClass('lg:col-span-2 rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-2 min-w-0');
                            if (turnSpeedShow) {
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
                                ovDrawQueue.push({ traf: p + 'traf', ups: h.netUp || [], downs: h.netDown || [] });
                            }
                            var $conn = $('<div>').addClass((turnSpeedShow ? 'lg:col-span-1' : 'lg:col-span-3') + ' rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 flex flex-col gap-2 min-w-0');
                            $conn.append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500').text('Количество соединений'));
                            $conn.append($('<div>').addClass('text-3xl font-bold text-white').html(((s.tcp || 0) + (s.udp || 0)) + ' <span class="text-sm font-medium text-gray-500">открытых сокетов</span>'));
                            $conn.append($('<div>').addClass('flex gap-4 text-xs text-gray-400').html('<span><i class="text-[#2f81f7]">—</i> TCP <b class="text-white">' + (s.tcp ?? '—') + '</b></span><span><i class="text-gray-500">—</i> UDP <b class="text-white">' + (s.udp ?? '—') + '</b></span>'));
                            $conn.append($('<div>').addClass('h-44').append($('<canvas>').attr('id', p + 'conn').css({ width: '100%', height: '100%' })));
                            ovDrawQueue.push({ id: p + 'conn', pairs: h.online || [], color: '#2f81f7' });
                            if (turnSpeedShow) $row2.append($tr); $row2.append($conn);
                            $srv.append($row2);
                            // Ряд 3: аптайм / панель / сеть / топ
                            var $row3 = $('<div>').addClass('grid grid-cols-1 lg:grid-cols-3 gap-4');
                            $row3.append($('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 text-xs')
                                .append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500 mb-2').text('Время работы'))
                                .append($('<div>').addClass('flex gap-6').append($('<div>').append($('<div>').addClass('text-gray-500').text('OS')).append($('<div>').addClass('text-white font-bold text-base').text(s.uptime_h || '—'))).append($('<div>').append($('<div>').addClass('text-gray-500').text('Панель')).append($('<div>').addClass('text-white font-bold text-base').text(s.panel_uptime_h || '—'))).append($('<div>').append($('<div>').addClass('text-gray-500').text('В сети')).append($('<div>').addClass('text-emerald-400 font-bold text-base').text(s.online_now ?? '—')))));
                            $row3.append($('<div>').addClass('rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-4 text-xs')
                                .append($('<div>').addClass('text-[11px] uppercase tracking-wider text-gray-500 mb-2').text('Панель · ' + (s.ip || '')))
                                .append($('<div>').addClass('flex gap-6').append($('<div>').append($('<div>').addClass('text-gray-500').text('Память')).append($('<div>').addClass('text-white font-bold text-base').text(s.panel_mem_h || '—'))).append($('<div>').append($('<div>').addClass('text-gray-500').text('Потоки')).append($('<div>').addClass('text-white font-bold text-base').text(s.panel_threads ?? '—')))));
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
                    // Общие хелперы без дублей: деньги + один LTV-запрос на страницу + один рендер цен
                    if (!window.QMoney) window.QMoney = function (v) { v = Number(v || 0); return v.toLocaleString('ru-RU', { maximumFractionDigits: v < 100 && v % 1 !== 0 ? 2 : 0 }) + '₽'; };
                    if (!window.QLtvFetch) window.QLtvFetch = function (fresh) {
                        if (!fresh && window.QLtvFetch._d) { var d = $.Deferred(); d.resolve(window.QLtvFetch._d); return d.promise(); }
                        if (window.QLtvFetch._p) return window.QLtvFetch._p;
                        window.QLtvFetch._p = $.ajax({ url: '/api/admin/analytics/ltv' + (fresh ? '?fresh=1' : ''), method: 'GET', dataType: 'json', timeout: fresh ? 120000 : 20000 })
                            .done(function (d) { if (!fresh || d) window.QLtvFetch._d = d; })
                            .always(function () { window.QLtvFetch._p = null; });
                        return window.QLtvFetch._p;
                    };
                    if (!window.QPrices) window.QPrices = function ($tb, $ex, p) {
                        $tb = $($tb); $ex = $($ex); $tb.empty();
                        if (!p) { $ex.text('не хватает данных'); return; }
                        var tariffs = p.tariffs || [];
                        if (!tariffs.length) { $ex.text(p.explain || 'Нет активных платных подписок — данных для анализа недостаточно.'); $tb.append('<tr><td colspan="6" class="py-3 text-center text-gray-500">Нет данных</td></tr>'); return; }
                        // заголовок ставит вызывающий — тут только строки
                        tariffs.forEach(function (t) {
                            var hasNew = Number(t.new_price || 0) > 0;
                            var st = t.status || 'ok';
                            var stBadge = st === 'loss'
                                ? '<span class="text-red-400 font-medium">убыток</span>'
                                : (st === 'low' ? '<span class="text-amber-400 font-medium">низкая</span>'
                                : (st === 'ladder' ? '<span class="text-blue-400 font-medium">лесенка</span>'
                                : (st === 'empty' ? '<span class="text-gray-500">—</span>' : '<span class="text-emerald-400">ок</span>')));
                            var marginTxt = (t.margin != null ? (Number(t.margin).toFixed(1) + '%') : '—') + ' ' + stBadge;
                            $tb.append($('<tr>').addClass('border-b border-white/5')
                                .append($('<td>').addClass('py-1').text(t.name))
                                .append($('<td>').addClass('py-1').attr('title', 'Средний чек по факту: ' + window.QMoney(t.old_price)).text(t.config_price != null ? window.QMoney(t.config_price) : '—'))
                                .append($('<td>').addClass('py-1 ' + (hasNew ? 'text-amber-400 font-medium' : 'text-emerald-400')).attr('title', t.reason || '').text(hasNew ? window.QMoney(t.new_price) : '—'))
                                .append($('<td>').addClass('py-1').text(t.count))
                                .append($('<td>').addClass('py-1').html(marginTxt))
                                .append($('<td>').addClass('py-1 ' + (Number(t.uplift_month || 0) > 0 ? 'text-emerald-400 font-medium' : 'text-gray-500')).attr('title', t.reason || '').text(Number(t.uplift_month || 0) > 0 ? ('+' + window.QMoney(t.uplift_month)) : '—')));
                        });
                    };
                    var LAZY_HINTS = {
                        monitoring: 'Собираются при посещении раздела Мониторинг',
                        forecast: 'Собираются при посещении раздела Прогноз прибыли'
                    };
                    function lazyHints() {
                        $('[data-lazy-hint]').each(function () {
                            var $el = $(this);
                            var v = $.trim($el.text());
                            if (v === '—' || v === '') {
                                var key = $(this).data('lazy-hint');
                                $el.html('<span class="text-xs text-gray-500">' + (LAZY_HINTS[key] || key) + '</span>');
                            }
                        });
                    };
                     function renderMainCharts(totals) {
                        totals = totals || {};
                        if (typeof Chart === 'undefined') {
                            window.__rmcTries = (window.__rmcTries || 0) + 1;
                            if (window.__rmcTries > 50) { $('[data-chart-empty="chart_new_users"]').text('Не загрузилась библиотека графиков. Проверьте сеть.').show(); return; }
                            setTimeout(function(){ renderMainCharts(totals); }, 200); return;
                        }
                        window.__rmcTries = 0;
                        var statsReady = !!(window.__adminStats && window.__adminStats.financial);
                        var on = Number(totals.online_now || 0), all = Number(totals.clients || 0);
                        var $oc = $('#chart_online_main');
                        if ($oc.length && all > 0) {
                            $oc[0].previousElementSibling && ($oc[0].previousElementSibling.style.display='none');
                            if (!$oc[0].__chart) $oc[0].__chart = new Chart($oc, {
                                type: 'doughnut',
                                data: { labels: ['В сети', 'Не в сети'], datasets: [{ data: [on, Math.max(0, all - on)], backgroundColor: ['rgba(52,211,153,0.85)', 'rgba(148,163,184,0.25)'], borderWidth: 0 }] },
                                options: { cutout: '64%', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                            }); else { $oc[0].__chart.data.datasets[0].data = [on, Math.max(0, all - on)]; $oc[0].__chart.update(); }
                        }
                        var muc = (window.__adminStats && window.__adminStats.financial && window.__adminStats.financial.monthlyUsersChart) || [];
                        if (!statsReady) return;
                        var $empty2 = $('[data-chart-empty="chart_new_users"]');
                        if (!muc.length) { $empty2.text('Нет данных — пользователи пока не регистрировались.').show(); return; }
                        $empty2.hide();
                        var $bar2 = $('[data-chart="chart_new_users"]').get(0);
                        var $bar3 = $('[data-chart="chart_users_monthly"]:visible').get(0);
                        var labels = muc.map(function(r){return r.month;});
                        var vals = muc.map(function(r){return (r.users_count||0);});
                        if ($bar2 && Chart) {
                            var bctx = $bar2.getContext('2d');
                            var bgrad = bctx.createLinearGradient(0, 0, 0, $bar2.height || 220);
                            bgrad.addColorStop(0, 'rgba(96,165,250,0.95)');
                            bgrad.addColorStop(1, 'rgba(59,130,246,0.12)');
                            var bmax = Math.max.apply(null, vals.concat([0]));
                            var cfg = {
                                type: 'bar',
                                data: { labels: labels, datasets: [{ label: 'Новых', data: vals, backgroundColor: bgrad, hoverBackgroundColor: 'rgb(147,197,253)', borderRadius: 7, borderSkipped: false, barPercentage: 0.55, categoryPercentage: 0.68 }] },
                                options: {
                                    responsive: true, maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            backgroundColor: '#0d1117', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1,
                                            titleColor: '#9aa4b2', bodyColor: '#e6e9ee', padding: 10, displayColors: false,
                                            callbacks: { label: function (c) { return ' ' + Number(c.parsed.y || 0).toLocaleString('ru-RU') + ' новых'; } }
                                        }
                                    },
                                    scales: {
                                        x: { grid: { display: false }, ticks: { color: '#9aa4b2' } },
                                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#9aa4b2', stepSize: Math.max(1, Math.ceil(bmax / 5)), callback: function (v) { return v + ' чел.'; } } }
                                    }
                                }
                            };
                            if (!$bar2.__chart) $bar2.__chart = new Chart($bar2, cfg);
                            else { $bar2.__chart.data = { labels: labels, datasets: cfg.data.datasets }; $bar2.__chart.update(); }
                        }
                        if ($bar3 && Chart) {
                            var cfg2 = {
                                type: 'line',
                                data: { labels: labels, datasets: [{ data: vals, borderColor: 'rgb(59,130,246)', backgroundColor: 'rgba(59,130,246,0.15)', tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: 'rgb(59,130,246)' }] },
                                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c){return Number(c.parsed.y).toLocaleString('ru-RU') + ' новых';} } } }, scales: { x: { grid: { color: 'rgba(255,255,255,0.06)' } }, y: { grid: { color: 'rgba(255,255,255,0.06)' } } } }
                            };
                            if (!$bar3.__chart) $bar3.__chart = new Chart($bar3, cfg2);
                            else { $bar3.__chart.data = { labels: labels, datasets: cfg2.data.datasets }; $bar3.__chart.update(); }
                        }
                        var $sp = $('#chart_users_spark');
                        if ($sp.length && Chart && muc.length) {
                            if (!$sp[0].__chart) $sp[0].__chart = new Chart($sp, {
                                type: 'line',
                                data: { labels: labels, datasets: [{ data: vals, borderColor: 'rgb(59,130,246)', backgroundColor: 'rgba(59,130,246,0.15)', tension: 0.4, fill: true, pointRadius: 2, pointBackgroundColor: 'rgb(59,130,246)' }] },
                                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: true, callbacks: { label: function(c){return Number(c.parsed.y).toLocaleString('ru-RU') + ' новых';} } } }, scales: { x: { display: false, grid: { display: false } }, y: { display: false, grid: { display: false } } } }
                            }); else { $sp[0].__chart.data.datasets[0].data = vals; $sp[0].__chart.data.labels = labels; $sp[0].__chart.update(); }
                        }
                    }
                    window.renderMainCharts = renderMainCharts;
                    function renderHealth(h) {
                        if (!h || h.status !== 'ok') return;
                        var ts = (h.turn_speed_show !== undefined) ? !!h.turn_speed_show : true;
                        $('#speed-toggle').prop('checked', ts).off('change.toggleSpeed').on('change.toggleSpeed', function () {
                            var val = $(this).is(':checked') ? 'on' : 'off';
                            $('#speed-toggle-label').text($(this).is(':checked') ? 'вкл.' : 'выкл.');
                            $.ajax({ url: '/api/admin/monitoring/toggle_flag', method: 'POST', dataType: 'json', timeout: 15000, data: { flag: 'turn_speed_show', value: val }, success: function (r) { $('#speed-toggle-label').text(r.status === 'ok' ? 'сохранено' : 'ошибка'); } });
                            renderPanelClients(h);
                        });
                        $('#speed-toggle-label').text(ts ? 'вкл.' : 'выкл.');
                        $('[data-ov-pill-text]').text('Готово');
                        $('[data-ov-pill-dot]').attr('class', 'w-2 h-2 rounded-full bg-emerald-400');
                        $('[data-ov-updated]').text(new Date(h.at_ms || Date.now()).toLocaleTimeString('ru-RU'));
                        var $pill = $('[data-h-pill]');
                        if (h.ok) $pill.attr('class', 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/25 text-emerald-300 text-sm').text('Всё сходится');
                        else $pill.attr('class', 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-yellow-500/10 ring-1 ring-yellow-500/25 text-yellow-300 text-sm').text('Есть расхождения');
        $('[data-h-panel]').text(h.panel_clients ?? '—');
        $('[data-h-panel-on]').text(h.panel_enabled ?? 0);
        $('[data-h-panel-off]').text(h.panel_disabled ?? 0);
                        $('[data-h-missing-n]').text(h.missing_count ?? 0);                        $('[data-h-ghosts-n]').text(h.ghosts_count ?? 0);
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
                    function renderForecast(d) {
                        if (!d || d.error) {
                            $('[data-fc-period-label]').text('Ошибка: ' + (d && d.error ? d.error : 'Нет данных'));
                            $('[data-fc="actual"],[data-fc="predicted"],[data-fc="forecast-min"],[data-fc="forecast-max"],[data-fc="forecast-base"],[data-fc="forecast-opt"],[data-fc="forecast-pes"],[data-fc="avg_ltv"],[data-fc="repeat-days"]').text('—');
                            $('[data-fc="recurring"],[data-fc="recurring-suffix"],[data-fc="total_clients"],[data-fc="trial-convert"]').text(0);
                            $('[data-fc-recurring-clients]').html('<tr><td colspan="6" class="py-4 text-center text-gray-500">Ошибка загрузки</td></tr>');
                            $('[data-fc-trial-table]').html('<tr><td colspan="5" class="py-4 text-center text-gray-500">Ошибка загрузки</td></tr>');
                            return;
                        }
                        var m = d.metrics || {};
                        $('[data-fc-period-label]').text('Период: ' + (d.period_label || 'все время'));
                        function fmt(v) { if (v === null || v === undefined || v === '') return 'не хватает данных'; v = Number(v); return isFinite(v) ? (v.toLocaleString('ru-RU') + ' ₽') : 'не хватает данных'; }
                        $('[data-fc="actual"]').text(fmt(m.actual_monthly_revenue));
                        $('[data-fc="predicted"]').text(fmt(m.predicted_monthly_revenue));
                        $('[data-fc="forecast-min"]').text(fmt(m.forecast_min));
                        $('[data-fc="forecast-max"]').text(fmt(m.forecast_max));
                        $('[data-fc="recurring"]').text(m.recurring_clients ?? 0);
                        $('[data-fc="recurring-suffix"]').text(m.recurring_clients ?? 0);
                        $('[data-fc="avg_ltv"]').text(m.avg_ltv ? (m.avg_ltv + ' ₽') : 'не хватает данных');
                        $('[data-fc="total_clients"]').text(m.active_subscriptions ?? 0);
                        $('[data-fc="repeat-days"]').text(m.repeat_purchase_days ? (m.repeat_purchase_days + ' дн') : 'не хватает данных');
                        var tc = d.trial_conversions || [];
                        $('[data-fc="trial-convert"]').text(tc.length ? tc.length : '—');
        var predicted = m.predicted_monthly_revenue || 0;
        $('[data-fc="forecast-base"]').text(fmt(predicted * 0.7));
        $('[data-fc="forecast-opt"]').text(fmt(predicted * 0.9));
            $('[data-fc="forecast-pes"]').text(fmt(predicted * 0.5));

                        var $rc = $('[data-fc-recurring-clients]').empty();
                        (d.recurring_clients || []).forEach(function (c) {
                            var ne = c.next_month_expectation || {};
                            var monthLabel = (c.next_payment ? c.next_payment.slice(0, 7) : (ne.month || '—'));
                            var expectVal = ne.expected ? (Number(ne.expected).toLocaleString('ru-RU') + ' ₽') : 'не хватает данных';
                            $rc.append($('<tr>').addClass('border-b border-white/5')
                                .append($('<td>').addClass('py-2').text((c.uniID || '').slice(0, 8)))
                                .append($('<td>').addClass('py-2').text(c.ltv ? (Number(c.ltv).toLocaleString('ru-RU') + ' ₽') : 'не хватает данных'))
                                .append($('<td>').addClass('py-2').text(c.pay_count || '—'))
                                .append($('<td>').addClass('py-2').text(c.expected_month ? (Number(c.expected_month).toLocaleString('ru-RU') + ' ₽') : 'не хватает данных'))
                                .append($('<td>').addClass('py-2').text(c.next_payment || 'не хватает данных'))
                                .append($('<td>').addClass('py-2').text(monthLabel + ': ' + expectVal)));
                        });
                        if (!(d.recurring_clients || []).length) $rc.append($('<tr><td colspan="6" class="py-4 text-center text-gray-500">Нет постоянных клиентов</td></tr>'));

                        var $tt = $('[data-fc-trial-table]').empty();
                        (tc || []).forEach(function (c) {
                            $tt.append($('<tr>').addClass('border-b border-white/5')
                                .append($('<td>').addClass('py-2').text((c.uniID || '').slice(0, 8)))
                                .append($('<td>').addClass('py-2').text(c.trial_days ? (c.trial_days + 'д') : '—'))
                                .append($('<td>').addClass('py-2').text(c.pay_count || '—'))
                                .append($('<td>').addClass('py-2').text(c.first_payment || '—'))
                                .append($('<td>').addClass('py-2').text(c.interval || '—')));
                        });
                        if (!tc.length) $tt.append($('<tr><td colspan="5" class="py-4 text-center text-gray-500">Нет пробников с покупками</td></tr>'));

                        if (!window.__fcChart) {
                            if (typeof Chart === 'undefined' || !document.getElementById('fc-forecast')) return;
                            var mr = d.monthly_revenue || {};
                            var labels = Object.keys(mr).sort();
                            var data = labels.map(function (k) { return mr[k] || 0; });
                            if (!labels.length) { labels = ['Сегодня']; data = [m.predicted_monthly_revenue || 0]; }
                            window.__fcChart = new Chart(document.getElementById('fc-forecast'), {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Прогноз (₽)',
                                        data: data,
                                        borderColor: 'rgb(139, 92, 246)',
                                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                        tension: 0.4,
                                        fill: true,
                                        pointBackgroundColor: '#fff',
                                        pointBorderColor: 'rgb(139, 92, 246)',
                                        pointBorderWidth: 2
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
                                                callback: function (value) { return value.toLocaleString('ru-RU') + ' ₽'; },
                                                color: '#9aa4b2'
                                            }
                                        },
                                        x: { grid: { display: false }, ticks: { color: '#9aa4b2' } }
                                    },
                                    plugins: {
                                        legend: { display: true, position: 'top', labels: { color: '#9aa4b2' } },
                                        title: { display: true, text: 'Прогноз /мес по месяцам создания', color: '#e6e9ee', font: { size: 14 } },
                                        tooltip: {
                                            backgroundColor: '#0d1117', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1,
                                            titleColor: '#9aa4b2', bodyColor: '#e6e9ee',
                                            callbacks: { label: function (c) { return ' ' + c.dataset.label + ': ' + Number(c.parsed.y || 0).toLocaleString('ru-RU') + ' ₽'; } }
                                        }
                                    }
                                }
                            });
                        } else {
                            var mr2 = d.monthly_revenue || {};
                            var labels2 = Object.keys(mr2).sort();
                            var data2 = labels2.map(function (k) { return mr2[k] || 0; });
                            if (!labels2.length) { labels2 = ['Сегодня']; data2 = [m.predicted_monthly_revenue || 0]; }
                            window.__fcChart.data.labels = labels2;
                            window.__fcChart.data.datasets[0].data = data2;
                            window.__fcChart.update();
                        }
                    }
                    function loadForecast(force) {
                        if (!force && !$('[data-section="forecast"]:not(.hidden)').length) return;
                        if (!force && window.__fcLoading) return;
                        window.__fcLoading = true;
                        $('[data-fc-refresh]').prop('disabled', true);
                        if (force && window.QSpin) QSpin.show('Обновление бюджета…');
                        var days = $('[data-fc-days].bg-blue-600').data('fc-days');
                        var month = $('[data-fc-month]').val();
                        var url = '/api/admin/analytics/ltv';
                        if (days) url += '?days=' + encodeURIComponent(days);
                        else if (month) url += '?month=' + encodeURIComponent(month);
                        else if (force) url += '?fresh=1';
                        // days/month всегда свежий срез, базовый экран — кэш (тяжёлый скан не чаще раза в 2 мин)
                        var needFresh = !!days || !!month || !!force;
                        var isSlice = !!(days || month);
                        var req = (needFresh && window.QLtvFetch && !days && !month)
                            ? window.QLtvFetch(true)
                            : $.ajax({ url: url, method: 'GET', dataType: 'json', timeout: needFresh ? 120000 : 20000 });
                        req.done(function (d) { renderForecast(d); if (d && !d.error && !isSlice) window.QLtvFetch._d = d; })
                            .fail(function (xhr) { $('[data-fc-period-label]').text('Ошибка загрузки: ' + (xhr.status || 'нет соединения')); })
                            .always(function () { $('[data-fc-refresh]').prop('disabled', false); window.__fcLoading = false; if (window.QSpin) QSpin.hide(); });
                    }
                    function renderPanelClients(h) {
                        if (!h || h.status !== 'ok') return;
                        $('[data-ov-pc-total]').text(h.panel_clients ?? 0);
                        $('[data-ov-pc-on]').text(h.panel_online ?? 0);
                        $('[data-ov-pc-off]').text(h.panel_disabled ?? 0);
                        $('[data-ov-pc-active]').text(h.panel_active ?? 0);
                        $('[data-ov-pc-exhausted]').text(h.panel_exhausted ?? 0);
                        $('[data-ov-pc-near]').text(h.panel_near_expiry ?? 0);
                        $('[data-ov-pc-updated]').text(new Date(h.at_ms || Date.now()).toLocaleTimeString('ru-RU'));
                        var tbody = $('[data-ov-pc-tbody]').empty();
                        var turnSpeedShow = !!h.turn_speed_show;
                        $('.speed-col').toggle(turnSpeedShow);
                        var list = (h.panel_clients_list || []);
                        var pcColspan = turnSpeedShow ? 6 : 5;
                        if (!list.length) {
                            tbody.append('<tr class="ant-table-measure-row"><td></td></tr><tr><td colspan="' + pcColspan + '" class="pc-empty">Нет клиентов в панелях</td></tr>');
                            return;
                        }
                        tbody.append('<tr class="ant-table-measure-row"><td></td></tr>');
            list.forEach(function (c) {
                var esc = function (s) { return $('<div>').text(s == null ? '' : String(s)).html(); };
                var onlineTag = c.online ?
                    '<span class="ant-tag ant-tag-filled dot-tag xui online"><span class="online-dot"></span>В сети</span>' :
                    '<span class="ant-tag ant-tag-filled dot-tag xui offline"><span class="online-dot"></span>Не в сети</span>';
                var used = (c.up || 0) + (c.down || 0);
                            var total = (c.totalGB || 0);
                            var limitLabel = total <= 0 ? '∞' : ovBytes(total);
                            var usedLabel = ovBytes(used);
                            var barCls = total <= 0 ? 'client-traffic-cell-bar ant-progress is-unlimited' : 'client-traffic-cell-bar ant-progress';
                            var barWidth = total <= 0 ? '100%' : (Math.min(100, (used / total) * 100)).toFixed(0) + '%';
                            if (c.expiry_days_num !== undefined && c.expiry_days_num <= 3 && c.expiry_days_num >= 0) {
                                barCls += ' traffic-low';
                            }
                            var trafficCell = '<div class="client-traffic-cell ' + (total <= 0 ? 'is-unlimited' : '') + '">' +
                                '<span class="client-traffic-cell-used">' + usedLabel + '</span>' +
                                '<div class="' + barCls + '">' +
                                    '<div class="ant-progress-rail"><div class="ant-progress-track" style="width:' + barWidth + '"></div></div>' +
                                '</div>' +
                                '<span class="client-traffic-cell-limit">' + limitLabel + '</span>' +
                            '</div>';
                            var speedTag;
                            var hasSpd = (Number(c.up) || 0) > 0 || (Number(c.down) || 0) > 0;
                            if (turnSpeedShow && c.online && hasSpd) {
                                speedTag = '<span class="table-speed-tag">↑ ' + esc(c.up_h || '0.00 KB') + ' / ↓ ' + esc(c.down_h || '0.00 KB') + '</span>';
                            } else {
                                speedTag = '<span class="table-speed-tag-empty">—</span>';
                            }
                             var durationTag = '<span class="text-gray-500">—</span>';
                             if (c.expiry_days) {
                                 var durCls = (c.expiry_days_num !== undefined && c.expiry_days_num <= 3) ? 'duration-cell-warning' : 'duration-cell-value';
                                 durationTag = '<span class="' + durCls + '">' + esc(c.expiry_days) + '</span>';
                             }
                             var emailCell = '<div class="font-mono text-xs break-all email-cell">' + esc(c.email || c.subId || '—') + '</div>' + (c.subId ? '<div class="text-gray-400 text-[10px] break-all email-cell">' + esc(c.subId) + '</div>' : '');
                             var groupTag;
                             if (c.group && c.group !== 'Оплаченная') {
                                 var tagCls = (c.group === 'Бесплатные') ? 'type-free group-tag' : (c.group === 'Пробная') ? 'type-trial group-tag' : (c.group === 'Бонусная') ? 'type-bonus group-tag' : 'duration-cell-warning group-tag';
                                 groupTag = '<span class="' + tagCls + '">' + esc(c.group) + '</span>';
                             } else {
                                 groupTag = '<span class="group-tag-empty">—</span>';
                             }
                             var $tr = $('<tr>').addClass('ant-table-row ant-table-cell-row-hover text-gray-200').attr('data-row-key', (c.subId || c.email || ''));
                             $tr.append(
                                     '<td class="ant-table-cell text-center">' + onlineTag + '</td>' +
                                     '<td class="ant-table-cell text-center">' + emailCell + '</td>' +
                                     '<td class="ant-table-cell text-center">' + groupTag + '</td>' +
                                     '<td class="ant-table-cell text-center">' + trafficCell + '</td>' +
                                     (turnSpeedShow ? '<td class="ant-table-cell text-center">' + speedTag + '</td>' : '') +
                                     '<td class="ant-table-cell text-center">' + durationTag + '</td>');
                             tbody.append($tr);
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
                                    h += '<div class="text-yellow-300">Не удалились (' + r.panel_failed.length + '): ' + r.panel_failed.map(function (f) { return $('<div>').text(f.key).html(); }).join(', ') + '</div>';
                                }
                                h += '<div><button data-cu-cancel class="text-xs font-medium px-4 py-2 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-300">Закрыть</button></div>';
                                $box.html(h);
                                loadOverview(true);
                            },
                            error: function () { if (window.QSpin) QSpin.hide(); $btn.prop('disabled', false).text('Повторить'); }
                        });
                    });
                    function loadOverview(force) {
                        if (!force && !($('[data-section="monitoring"]:not(.hidden)').length || $('[data-section="main"]:not(.hidden)').length)) return;
                        if (force && window.QSpin) QSpin.show('Обновление…');
                        var $btn = $('[data-ov-refresh]');
                        if (force) $btn.find('i').addClass('fa-spin');
                        // Один запрос: overview уже содержит все health-поля — панели дёргаем раз, а не два
                        var op = '/api/admin/overview' + (force ? '?fresh=1' : '');
                        $.ajax({
                            url: op, method: 'GET', dataType: 'json', timeout: 60000,
                            success: function (dd) {
                                if (!dd || dd.status !== 'ok') { ovBadge('red', (dd && dd.error) || 'Ошибка загрузки'); return; }
                                renderOverview(dd);
                                renderHealth(dd);
                                renderPanelClients(dd);
                                window.__overviewTotals = dd.totals || {};
                                if (dd.refreshing && !force && !ovRetried) {
                                    ovRetried = true;
                                    setTimeout(function () { window.__ovLoading = false; loadOverview(false); }, 8000);
                                }
                                if (!dd.refreshing) ovRetried = false;
                            },
                            error: function (xhr) {
                                if ((xhr.statusText || '') === 'timeout') {
                                    ovBadge('red', 'Превышено время ожидания');
                                    $('[data-ov-pill-text]').text('Превышено время ожидания');
                                } else {
                                    ovBadge('red', 'Ошибка загрузки');
                                    $('[data-ov-pill-text]').text('Ошибка подключения');
                                }
                                $('[data-ov-pill-dot]').attr('class', 'w-2 h-2 rounded-full bg-red-400');
                            },
                            complete: function () {
                                $btn.find('i').removeClass('fa-spin');
                                if (window.QSpin) QSpin.hide();
                                window.__ovLoading = false;
                            }
                        });
                    }
                    $('[data-ov-refresh]').on('click', function () { loadOverview(true); });
                    $(document).on('click', '[data-toggle-section="forecast"]', function () { setTimeout(function () { loadForecast(false); }, 150); });
                    $(document).on('click', '[data-fc-days]', function () {
                        $('[data-fc-days]').removeClass('bg-blue-600 text-white').addClass('bg-white/5 text-gray-300');
                        $(this).addClass('bg-blue-600 text-white').removeClass('bg-white/5 text-gray-300');
                        $('[data-fc-month]').val('');
                        loadForecast(true);
                    });
                    $(document).on('change', '[data-fc-month]', function () {
                        $('[data-fc-days]').removeClass('bg-blue-600 text-white').addClass('bg-white/5 text-gray-300');
                        loadForecast(true);
                    });
                    $(document).on('click', '[data-fc-refresh]', function () { loadForecast(true); });
                    $(document).on('click', '[data-toggle-section="monitoring"]', function () { setTimeout(function () { loadOverview(false); }, 150); });
                    var monitoringInterval = null;
                    function startMonitoringInterval() {
                        stopMonitoringInterval();
                        monitoringInterval = setInterval(function () {
                            loadOverview(false);
                        }, 60000);
                    }
                    function stopMonitoringInterval() {
                        if (monitoringInterval) { clearInterval(monitoringInterval); monitoringInterval = null; }
                    }
                    $(document).on('click', '[data-toggle-section]', function () {
                        var sec = $(this).data('toggle-section');
                        if (sec === 'monitoring') { startMonitoringInterval(); }
                        else { stopMonitoringInterval(); }
                    });
                    if (ovVisible()) startMonitoringInterval();
                    $(window).on('beforeunload', function () { stopMonitoringInterval(); });
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
                    <div class="flex items-center gap-2">
                        <button data-srv-refresh
                            class="text-xs font-medium px-4 py-2 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10 transition-colors" title="Обновить список">
                            <i class="fa-solid fa-rotate-right mr-1"></i>Обновить
                        </button>
                        <button data-srv-add-open
                            class="text-xs font-medium px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-500 transition-colors">
                            <i class="fa-solid fa-plus mr-1"></i>Добавить сервер
                        </button>
                    </div>
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
                    var srvLoaded = false, srvLoading = false, srvRaw = {}, srvEditCode = null;
                    function srvVisible() { return $('[data-section="servers"]:not(.hidden)').length > 0; }
                    function srvLoad(force) {
                        if (!force && (srvLoaded || !srvVisible())) return;
                        if (srvLoading) return;
                        srvLoading = true;
                        $('[data-srv-refresh]').find('i').addClass('fa-spin');
                        $.ajax({
                            url: '/api/admin/relocation', method: 'GET', dataType: 'json', timeout: 60000,
                            success: function (d) {
                                if (!d || d.status === 'error') {
                                    $('[data-srv-list]').html('<div class="rounded-xl bg-red-500/5 ring-1 ring-red-500/20 p-6 text-sm text-red-400">Ошибка загрузки: ' + ((d && d.message) || 'неизвестно') + '</div>');
                                    return;
                                }
                                srvLoaded = true;
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
                                            $(document).find('[data-srv-check]').filter(function () { return $(this).attr('data-srv-check') === code; })
                                                .text(c.ok ? c.note : ('нет: ' + (c.note || ''))).toggleClass('text-green-400', !!c.ok).toggleClass('text-red-400', !c.ok);
                                        },
                                        error: function () {
                                            $(document).find('[data-srv-check]').filter(function () { return $(this).attr('data-srv-check') === code; })
                                                .text('ошибка проверки').addClass('text-red-400');
                                        }
                                    });
                                });
                            },
                            error: function () {
                                $('[data-srv-list]').html('<div class="rounded-xl bg-red-500/5 ring-1 ring-red-500/20 p-6 text-sm text-red-400">Ошибка сети. Нажмите «Обновить».</div>');
                            },
                            complete: function () { srvLoading = false; $('[data-srv-refresh]').find('i').removeClass('fa-spin'); }
                        });
                    }
                    function srvCollect() {
                        var o = {};
                        $('[data-srv-f]').each(function () { o[$(this).attr('data-srv-f')] = $(this).val(); });
                        o.code = (o.code || '').toLowerCase().trim();
                        o.XUI_INBOUND_NUMBER = parseInt(o.XUI_INBOUND_NUMBER || '0', 10) || 0;
                        if (!o.flag) o.flag = 'none.svg';
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
                    $(document).on('click', '[data-toggle-section="servers"]', function () { setTimeout(function () { srvLoad(false); }, 350); });
                    $(document).on('click', '[data-srv-refresh]', function () { srvLoaded = false; srvLoad(true); });
                    srvLoad(false);
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
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">аварийный центр при блокировках</span>
                        <button data-rl-refresh
                            class="text-xs font-medium px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10 transition-colors" title="Обновить данные">
                            <i class="fa-solid fa-rotate-right mr-1"></i>Обновить
                        </button>
                    </div>
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
                    var rlLoaded = false, rlLoading = false;
                    function rlVisible() { return $('[data-section="relocation"]:not(.hidden)').length > 0; }
                    function rlGet(url, ok, fail) {
                        $.ajax({ url: url, method: 'GET', dataType: 'json', timeout: 60000, success: ok, error: fail || function () {} });
                    }
                    function rlLoad(force) {
                        if (!force && (rlLoaded || !rlVisible())) return;
                        if (rlLoading) return;
                        rlLoading = true;
                        $('[data-rl-refresh]').find('i').addClass('fa-spin');
                        var pending = 2;
                        function one() { if (--pending <= 0) { rlLoading = false; $('[data-rl-refresh]').find('i').removeClass('fa-spin'); } }
                        rlGet('/api/admin/relocation?action=servers', function (d) {
                            rlLoaded = true;
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
                            one();
                        }, function () {
                            $('[data-rl-servers]').html('<tr><td colspan="5" class="py-3 text-red-400">Ошибка сети. Нажмите «Обновить».</td></tr>');
                            one();
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
                            one();
                        }, function () {
                            $('[data-rl-domains]').html('<tr><td colspan="3" class="py-3 text-red-400">Ошибка сети. Нажмите «Обновить».</td></tr>');
                            one();
                        });
                    }
                    function rlCheck(code) {
                        var $c = $(document).find('[data-rl-check]').filter(function () { return $(this).attr('data-rl-check') === code; });
                        $c.text('…');
                        rlGet('/api/admin/relocation?action=check&code=' + encodeURIComponent(code), function (d) {
                            var c = (d && d.check) || {};
                            $c.text(c.ok ? c.note : ('нет: ' + (c.note || '')));
                            $c.toggleClass('text-green-400', !!c.ok).toggleClass('text-red-400', !c.ok);
                        }, function () { $c.text('ошибка проверки').addClass('text-red-400'); });
                    }
                    $(document).on('click', '[data-rl-checkbtn]', function () { rlCheck($(this).attr('data-rl-checkbtn')); });
                    $('[data-rl-preview]').on('click', function () {
                        var t = $('[data-rl-target]').val();
                        var $pv = $('[data-rl-preview]').text('Считаю…');
                        rlGet('/api/admin/relocation?action=preview&target=' + encodeURIComponent(t), function (p) {
                            if (!p || p.status !== 'ok') { $pv.text('Ошибка: ' + ((p && p.message) || '')); return; }
                            $pv.empty()
                                .append(document.createTextNode('На ')).append($('<b>').text(t))
                                .append(document.createTextNode(': перенести ')).append($('<b>').text(p.move))
                                .append(document.createTextNode(', уже там ' + (p.already || 0) + '. Панель цели: '))
                                .append(p.target_ok ? $('<b>').addClass('text-green-400').text('доступна') : $('<b>').addClass('text-red-400').text('НЕДОСТУПНА (' + (p.target_note || '') + ')'));
                        }, function () { $pv.text('Ошибка сети'); });
                    });
                    var rlPolling = false;
                    function rlPollTick() {
                        if (!rlVisible()) { rlPolling = false; return; }
                        $.ajax({
                            url: '/api/admin/relocation', method: 'POST', data: { action: 'tick' }, dataType: 'json', timeout: 120000,
                            success: function (r) {
                                var st = r.state || {};
                                var total = st.total || 0, done = st.done || 0;
                                $('[data-rl-bar]').css('width', (total ? (done / total * 100) : 0) + '%');
                                $('[data-rl-status]').text('Готово ' + done + ' из ' + total + ' · успешно ' + (st.ok || 0));
                                var $f = $('[data-rl-failed]').empty();
                                (st.failed || []).forEach(function (f) { $f.append($('<div>').text((f.email || f.uniID) + ': ' + (f.error || ''))); });
                                if (st.status === 'running') setTimeout(rlPollTick, 3000);
                                else {
                                    rlPolling = false;
                                    $('[data-rl-status]').append(document.createTextNode(st.status === 'done' ? ' · ЗАВЕРШЕНО' : ' · завершено с ошибками'));
                                    if ((st.failed || []).length) $('[data-rl-retry]').removeClass('hidden');
                                }
                            },
                            error: function () { setTimeout(rlPollTick, 5000); }
                        });
                    }
                    function rlPollStart() { if (rlPolling) return; rlPolling = true; rlPollTick(); }
                    $('[data-rl-start]').on('click', function () {
                        if (!confirm('Перенести клиентов на ' + $('[data-rl-target]').val() + '?')) return;
                        $('[data-rl-progress]').removeClass('hidden');
                        $('[data-rl-retry]').addClass('hidden');
                        $.ajax({
                            url: '/api/admin/relocation', method: 'POST', data: { action: 'start', target: $('[data-rl-target]').val() }, dataType: 'json', timeout: 60000,
                            success: function (r) {
                                if (r.status !== 'ok') { $('[data-rl-status]').text('Ошибка: ' + (r.message || '')); return; }
                                rlPollStart();
                            }
                        });
                    });
                    $('[data-rl-retry]').on('click', function () {
                        $.ajax({
                            url: '/api/admin/relocation', method: 'POST', data: { action: 'retry' }, dataType: 'json', timeout: 30000,
                            success: function () { $('[data-rl-retry]').addClass('hidden'); rlPollStart(); }
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
                    $(document).on('click', '[data-toggle-section="relocation"]', function () { setTimeout(function () { rlLoad(false); }, 350); });
                    $(document).on('click', '[data-rl-refresh]', function () { rlLoaded = false; rlLoad(true); });
                    rlLoad(false);
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
                <div class="py-6 flex-col flex md:flex-row justify-between items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-100 mb-4 md:mb-0">
                        Графики и аналитика
                    </h1>
                    <div class="flex items-center gap-2">
                        <span data-charts-updated class="text-xs text-gray-500"></span>
                        <button data-charts-refresh
                            class="text-xs font-medium px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10 transition-colors">
                            <i class="fa-solid fa-rotate-right mr-1"></i>Обновить
                        </button>
                    </div>
                </div>

                <div data-charts-loading class="rounded-xl bg-white/[0.03] ring-1 ring-white/5 p-6 text-sm text-gray-400 flex items-center gap-3">
                    <i class="fa-solid fa-circle-notch fa-spin text-blue-400"></i>Собираю данные…
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <h3 class="font-semibold text-gray-300 mb-3">Прибыль по месяцам</h3>
                        <div class="h-[300px]"><canvas data-chart="chart_revenue_monthly" class="w-full h-full"></canvas></div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <h3 class="font-semibold text-gray-300 mb-3">Новые пользователи по месяцам</h3>
                        <div class="h-[300px]"><canvas data-chart="chart_users_monthly" class="w-full h-full"></canvas></div>
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
                                <div class="text-2xl sm:text-3xl font-bold text-blue-400">
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
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <h3 class="font-semibold text-gray-300 mb-3">Прибыль по тарифам</h3>
                        <div class="h-[300px]"><canvas id="chart_plans" class="w-full h-full"></canvas></div>
                        <script defer>
                            // Строится лениво из /api/admin/stats при первом открытии раздела графиков
                            function buildPlansChart(byTariff, fallbackPlan) {
                            const $plansCtx = $('#chart_plans');
                            if ($plansCtx.length && !window.__plansBuilt && typeof Chart !== 'undefined') {
                                const rows = (byTariff && byTariff.length) ? byTariff : ((fallbackPlan && fallbackPlan.revenueByPlan) || []);
                                const labels = rows.map(function (r) { return window.tariffName ? window.tariffName(r.tariff || r.plan) : (r.tariff || r.plan); });
                                const data = rows.map(function (r) { return r.revenue; });

                                if (labels.length > 0 && data.length > 0) {
                                    try { if (window.__plansChart) window.__plansChart.destroy(); } catch (e) {}
                                    window.__plansBuilt = true;
                                    window.__plansChart = new Chart($plansCtx[0], {
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
                                    // Данных нет — canvas не трогаем, попробуем снова при следующем обновлении
                                    return;
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
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="price">
                <div class="py-10 flex flex-col items-center gap-3 text-center">
                    <i class="fa-solid fa-lock text-5xl text-red-500"></i>
                    <div class="text-lg font-bold text-gray-100">Недоступно</div>
                    <div class="text-sm text-gray-500">Нет прав на раздел</div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Секция: Прогнозирование прибыли -->
            <?php if ($groups->isPermission($adminUsername,'roi')): ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="forecast">
                <!-- Шапка с описанием -->
                <div class="pb-4 border-b border-white/5">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-line-chart text-2xl text-emerald-400"></i>
                         <h1 class="text-2xl font-bold text-gray-100">Прогнозирование прибыли</h1>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 max-w-2xl">
                        Прогноз ежемесячной выручки на основе LTV (жизненной ценности клиента).
                        Анализируются активные подписки с 2+ покупками — они способны рефинансировать.
                        Данные кэшируются на 2 минуты.
                    </p>
                    <p class="mt-2 text-xs text-gray-600">
                        Прогноз появляется, когда в базе есть активные подписки (<code>status='on'</code>, <code>expiry &gt; now</code>)
                        с <strong>2+ успешными платежами</strong> (<code>qwees_payments.status='succeeded'</code>). Если показатель <code>—</code>,
                        значит постоянных клиентов пока нет — нужны повторные покупки.
                    </p>
                </div>

                <!-- Период выбора -->
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-gray-400">Период:</span>
                    <div class="flex rounded-lg overflow-hidden ring-1 ring-white/10 text-xs font-medium">
                        <button data-fc-days="30" class="px-3 py-1.5 bg-blue-600 text-white" title="За 30 дней">30д</button>
                        <button data-fc-days="90" class="px-3 py-1.5 bg-white/5 text-gray-300 hover:bg-white/10" title="За 90 дней">90д</button>
                        <button data-fc-days="365" class="px-3 py-1.5 bg-white/5 text-gray-300 hover:bg-white/10" title="За год">год</button>
                    </div>
                    <input type="month" data-fc-month title="Выберите конкретный месяц (год-месяц)"
                        class="text-xs bg-white/5 border border-white/10 rounded-lg px-2 py-1.5 text-gray-300 focus:outline-none focus:border-purple-500">
                    <button data-fc-refresh
                        class="text-xs font-medium px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10 transition-colors" title="Обновить данные">
                        <i class="fa-solid fa-rotate-right mr-1"></i>Обновить
                    </button>
                </div>

                <!-- KPI прогноза -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div class="text-xs text-gray-500">Фактическая выручка (30д)</div>
                            <span class="q-tip" tabindex="0" data-tip="Та же цифра что в прибыли (gross ЮKassa за 30 дней). Без кэша кассы — сумма успешных платежей леджера"><i class="fa-solid fa-circle-info text-gray-600 hover:text-gray-400 text-xs"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-cyan-400" data-fc="actual">—</div>
                        <div class="text-[11px] text-gray-600 mt-0.5">₽</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div class="text-xs text-gray-500">Прогнозируемая выручка</div>
                            <span class="q-tip" tabindex="0" data-tip="Ожидаемая выручка на следующий месяц от постоянных клиентов (базовый сценарий)"><i class="fa-solid fa-circle-info text-gray-600 hover:text-gray-400 text-xs"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-emerald-400" data-fc="predicted">—</div>
                        <div class="text-[11px] text-gray-600 mt-0.5">₽ / месяц</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div class="text-xs text-gray-500">Прогноз (мин)</div>
                            <span class="q-tip" tabindex="0" data-tip="Минимальный сценарий: 50% постоянных продлят (пессимистично)"><i class="fa-solid fa-circle-info text-gray-600 hover:text-gray-400 text-xs"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-amber-400" data-fc="forecast-min">—</div>
                        <div class="text-[11px] text-gray-600 mt-0.5">₽ / месяц</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div class="text-xs text-gray-500">Прогноз (макс)</div>
                            <span class="q-tip" tabindex="0" data-tip="Максимальный сценарий: 90% постоянных продлят (оптимистично)"><i class="fa-solid fa-circle-info text-gray-600 hover:text-gray-400 text-xs"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-cyan-400" data-fc="forecast-max">—</div>
                        <div class="text-[11px] text-gray-600 mt-0.5">₽ / месяц</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div class="text-xs text-gray-500">Постоянных клиентов</div>
                            <span class="q-tip" tabindex="0" data-tip="Активные клиенты с 2+ покупками — вероятны повторные платежи"><i class="fa-solid fa-circle-info text-gray-600 hover:text-gray-400 text-xs"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-blue-400" data-fc="recurring">—</div>
                        <div class="text-[11px] text-gray-600 mt-0.5">человек</div>
                    </div>
                </div>

                <!-- Доп. KPI: LTV, интервал, клиенты -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div class="text-xs text-gray-500">Средний LTV</div>
                            <span class="q-tip" tabindex="0" data-tip="Средняя сумма потраченная одним активным клиентом за выбранный период"><i class="fa-solid fa-circle-info text-gray-600 hover:text-gray-400 text-xs"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-cyan-400" data-fc="avg_ltv">—</div>
                        <div class="text-[11px] text-gray-600 mt-0.5">₽ на клиента</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div class="text-xs text-gray-500">Средний интервал покупки</div>
                            <span class="q-tip" tabindex="0" data-tip="Средний срок между повторными покупками постоянных клиентов. Помогает прогнозировать, когда клиент снова заплатит"><i class="fa-solid fa-circle-info text-gray-600 hover:text-gray-400 text-xs"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-amber-400" data-fc="repeat-days">—</div>
                        <div class="text-[11px] text-gray-600 mt-0.5">дней</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div class="text-xs text-gray-500">Всего клиентов</div>
                            <span class="q-tip" tabindex="0" data-tip="Уникальные активные клиенты с expiry > сейчас"><i class="fa-solid fa-circle-info text-gray-600 hover:text-gray-400 text-xs"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-gray-200" data-fc="total_clients">—</div>
                        <div class="text-[11px] text-gray-600 mt-0.5">шт.</div>
                    </div>
                    <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div class="text-xs text-gray-500">Вероятных новых покупок</div>
                            <span class="q-tip" tabindex="0" data-tip="Пробники, у которых уже есть первый платёж — они в очереди на оплату"><i class="fa-solid fa-circle-info text-gray-600 hover:text-gray-400 text-xs"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-purple-400" data-fc="trial-convert">—</div>
                        <div class="text-[11px] text-gray-600 mt-0.5">чел.</div>
                    </div>
                </div>

                <!-- Сценарии прогноза (3 колоны) -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-chart-line text-purple-400 mr-1"></i>
                        <h3 class="font-semibold text-gray-300">Сценарии прогноза на следующий месяц</h3>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Пессимистичный 50%</div>
                            <div class="text-xl font-bold text-amber-400" data-fc="forecast-pes">—</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Базовый 70%</div>
                            <div class="text-xl font-bold text-purple-400" data-fc="forecast-base">—</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Оптимистичный 90%</div>
                            <div class="text-xl font-bold text-cyan-400" data-fc="forecast-opt">—</div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 mt-2">Все сценарии × на количество постоянных клиентов (<span data-fc="recurring-suffix">0</span> человек)</div>
                    <div class="text-xs text-gray-600 mt-2">Подсказка по ценам живёт в разделе ROI — единый советник на всю админку.</div>
                </div>

                <!-- Прогнозируемые постоянные клиенты -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="font-semibold text-gray-300">Прогнозируемые постоянные клиенты</h3>
                        <span class="q-tip" tabindex="0" data-tip="Активные клиенты с 2+ покупками — расчёт будущей выручки. Столбец 'Ожид. доход' = amount × 30/count_days. Столбец 'След. платёж' = когда ожидается следующий платёж"><i class="fa-solid fa-circle-info text-gray-500 text-xs"></i></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-gray-500 border-b border-white/5">
                                    <th class="pb-2">uniID</th>
                                    <th class="pb-2">LTV</th>
                                    <th class="pb-2">Покупок</th>
                                    <th class="pb-2">Ожид. доход / мес</th>
                                    <th class="pb-2">След. платёж</th>
                                    <th class="pb-2">Ожид. в месяц</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-300" data-fc-recurring-clients></tbody>
                        </table>
                    </div>
                </div>

                <!-- Конверсия пробников -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="font-semibold text-gray-300">Конверсия пробников в покупатели</h3>
                        <span class="q-tip" tabindex="0" data-tip="Пробники с уже имеющимся первым платежом — они в очереди на оплату. Интервал = дней между пробной подпиской и первой оплатой"><i class="fa-solid fa-circle-info text-gray-500 text-xs"></i></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-gray-500 border-b border-white/5">
                                    <th class="pb-2">uniID</th>
                                    <th class="pb-2">Пробный срок</th>
                                    <th class="pb-2">Покупок</th>
                                    <th class="pb-2">Первый платёж</th>
                                    <th class="pb-2">Интервал конверсии</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-300" data-fc-trial-table></tbody>
                        </table>
                    </div>
                </div>

                <!-- График прогноза по месяцам -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                    <div class="flex items-baseline justify-between mb-4">
                            <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-300">Прогноз по месяцам создания</h3>
                            <span class="q-tip" tabindex="0" data-tip="Сумма месячного эквивалента активных подписок (amount / days × 30) по месяцам их создания. Если данных нет, показывается текущий прогноз (predicted)"><i class="fa-solid fa-circle-info text-gray-500 text-xs"></i></span>
                        </div>
                        <span class="text-xs text-gray-500" data-fc-period-label>—</span>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="fc-forecast" style="width:100%;height:100%"></canvas>
                    </div>
                </div>

                <!-- Легенда прогноза -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-4">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Как рассчитывается прогноз</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-gray-500">
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-emerald-400 mt-0.5"></i>
                            <span><strong class="text-gray-400">Прогноз /мес</strong> = Σ активных подписок × (amount / days × 30)</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-blue-400 mt-0.5"></i>
                            <span><strong class="text-gray-400">Фактическая выручка</strong> = gross ЮKassa за 30 дней (та же цифра что в прибыли; без кэша кассы — сумма успешных платежей леджера)</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-purple-400 mt-0.5"></i>
                            <span><strong class="text-gray-400">Постоянные</strong> = активные клиенты с 2+ покупками (rebilling)</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-cyan-400 mt-0.5"></i>
                            <span><strong class="text-gray-400">Мин/Макс</strong> = Прогноз × {50% / 90%} — пессимистичный и оптимистичный сценарии</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-amber-400 mt-0.5"></i>
                            <span><strong class="text-gray-400">Интервал покупки</strong> = средний срок между повторными платежами постоянных клиентов</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-gray-600 mt-0.5"></i>
                            <span><strong class="text-gray-400">След. платёж</strong> = дата, когда постоянный клиент, скорее всего, снова заплатит (последний платёж + count_days)</span>
                        </div>
                    </div>
                </div>
            </section>
            <?php else: ?>
            <section class="box-border flex w-full flex-col gap-4 p-4 md:p-6 hidden" data-section="forecast">
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
                    function pfMoney(v) { return window.QMoney ? window.QMoney(v) : (Number(v || 0).toLocaleString('ru-RU', { maximumFractionDigits: Number(v) < 100 ? 2 : 0 }) + '₽'); }
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
                        var pfDaysN = Number(r.days || 0);
                        $('[data-pf-sub="mrr"]').text('MRR ' + (pfDaysN > 0 ? pfMoney((r.clean || 0) / (pfDaysN / 30)) : '—'));
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
                            var cnt = parseInt(row.cnt != null ? row.cnt : (row.count || 0), 10), rev = parseFloat(row.revenue || 0);
                            var tkey = row.tariff || row.plan || '—';
                            var avg = cnt > 0 ? ' · ~' + pfMoney(rev / cnt) + '/опл.' : '';
                            $t.append($('<div>').addClass('flex flex-col gap-0.5 py-1 border-b border-white/5 last:border-0')
                                .append($('<span>').addClass('text-gray-200 text-sm').text(pfTariff(tkey)))
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
                        <button data-roi-refresh
                            class="text-xs font-medium px-3 py-1.5 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-200 hover:bg-white/10 transition-colors" title="Обновить данные">
                            <i class="fa-solid fa-rotate-right mr-1"></i>Обновить
                        </button>
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

                <!-- Советник цен: один на всю админку. Цель — маржа 30% чистыми -->
                <div class="bg-[#16181d] rounded-xl ring-1 ring-white/5 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="font-semibold text-gray-300">Советник цен: повышать или нет</h3>
                        <span class="q-tip" tabindex="0" data-tip="Считает по каждому тарифу: доход, комиссию кассы и долю серверных расходов. Цель — 30% чистыми. Статус: убыток / низкая маржа / ок. Колонка «Надо» — цена для целевой маржи, «+Прибыль» — сколько даст в месяц."><i class="fa-solid fa-circle-info text-gray-500 text-xs"></i></span>
                    </div>
                    <div class="text-xs text-gray-500 mb-2" data-roi-price-explain>загрузка…</div>
                    <div class="overflow-x-auto"><table class="w-full text-xs">
                        <thead><tr class="text-left text-gray-500 border-b border-white/5">
                            <th class="pb-2">Тариф</th><th class="pb-2">В конфиге</th><th class="pb-2">Надо</th>
                            <th class="pb-2">Клиентов</th><th class="pb-2">Маржа</th><th class="pb-2">+Прибыль / мес</th>
                        </tr></thead>
                        <tbody class="text-gray-300" data-roi-price-tbody></tbody>
                    </table></div>
                    <div class="mt-3 text-xs text-gray-500 flex flex-col gap-1">
                        <div><strong class="text-gray-400">Что такое маржа:</strong> сколько чистыми остаётся с каждого рубля. Маржа = (доход − комиссия кассы − доля серверных расходов) / доход. Цель — 30%: с каждых 100₽ выручки 30₽ прибыли.</div>
                        <div><strong class="text-gray-400">Лесенка цен:</strong> за месяц длинный срок дешевле короткого (1 мес ≥ 3 мес ≥ 6 мес ≥ 12 мес). Если поднятие ломает порядок — короткие сроки подтягиваются тоже, иначе все купят длинный.</div>
                    </div>
                </div>
            </section>
            <script defer>
                $(document).ready(function () {
                    var roiDays = 30, roiCache = {}, roiLoading = false;
                    function roiVisible() { return $('[data-section="roi"]:not(.hidden)').length > 0; }
                    function roiMoney(v) { return window.QMoney ? window.QMoney(v) : (Number(v || 0).toLocaleString('ru-RU', { maximumFractionDigits: 0 }) + '₽'); }
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
                        roiLoadPrices();
                    }
                    function roiLoadPrices() {
                        if (!window.QPrices || !window.QLtvFetch) { $('[data-roi-price-explain]').text('не хватает данных'); return; }
                        window.QLtvFetch(false).done(function (d) {
                            var p = (d && d.price_optimization) || null;
                            if (p && (p.tariffs || []).length) {
                                var mt = Math.round(Number(p.margin_target || 0.3) * 100);
                                $('[data-roi-price-explain]').html('Цель: маржа <b>' + mt + '%</b> · Чистыми сейчас: <b>' + window.QMoney(p.total_net_month || 0) + '/мес</b> · Потенциал: <b class="text-emerald-400">+' + window.QMoney(p.total_uplift_month || 0) + '/мес</b><br>' + $('<div>').text(p.explain || '').html());
                            }
                            window.QPrices('[data-roi-price-tbody]', '[data-roi-price-explain]', p);
                        }).fail(function () { $('[data-roi-price-explain]').text('Ошибка загрузки подсказки цен'); });
                    }
                    function roiLoad(force) {
                        if (!force && !roiVisible()) return;
                        if (!force && roiLoading) return;
                        if (!force && roiCache[roiDays]) { roiRender(roiCache[roiDays]); return; }
                        roiLoading = true;
                        if (force && window.QSpin) QSpin.show('Сбор данных ROI…');
                        $.ajax({
                            url: '/api/admin/roi?days=' + roiDays, method: 'GET', dataType: 'json', timeout: 120000,
                            success: function (r) { roiCache[roiDays] = r; roiRender(r); },
                            error: function () { $('[data-roi-source]').text('Ошибка загрузки'); },
                            complete: function () { roiLoading = false; if (window.QSpin) QSpin.hide(); }
                        });
                    }
                    $('[data-roi-days]').on('click', function () {
                        roiDays = parseInt($(this).attr('data-roi-days'), 10) || 30;
                        $('[data-roi-days]').removeClass('bg-blue-600 text-white').addClass('bg-white/5 text-gray-300');
                        $(this).removeClass('bg-white/5 text-gray-300').addClass('bg-blue-600 text-white');
                        roiLoad(true);
                    });
                    $('[data-roi-import]').on('click', function () {
                        var $m = $('[data-roi-import-msg]').text('Импорт…');
                        $.ajax({
                            url: '/api/admin/payments/import', method: 'POST', data: { days: 90 }, dataType: 'json', timeout: 180000,
                            success: function (r) { $m.text(r.status === 'ok' ? ('Готово: ' + r.imported + ', пропущено ' + r.skipped) : ('Ошибка: ' + (r.message || ''))); },
                            error: function () { $m.text('Ошибка сети'); }
                        });
                    });
                    $(document).on('click', '[data-toggle-section="roi"]', function () { setTimeout(function () { roiLoad(false); }, 350); });
                    $(document).on('click', '[data-roi-refresh]', function () { delete roiCache[roiDays]; roiLoad(true); });
                    $(document).off('click.roireceipt').on('click.roireceipt', '[data-receipt-open]', function (e) {
                        if ($(this).closest('[data-admin-chat]').length) return;//в чате своя делегация
                        if (typeof window.__openReceipt === 'function') window.__openReceipt($(this).attr('data-receipt-open'));
                    });
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
                            class="relative h-[calc(100vh-200px)] overflow-y-auto flex flex-col gap-0.5 bg-black rounded-b-xl py-2">
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
:root {
    --ant-blue: #1677FF;
    --ant-purple: #722ED1;
    --ant-cyan: #13C2C2;
    --ant-green: #52C41A;
    --ant-magenta: #EB2F96;
    --ant-pink: #EB2F96;
    --ant-red: #F5222D;
    --ant-orange: #FA8C16;
    --ant-yellow: #FADB14;
    --ant-volcano: #FA541C;
    --ant-geekblue: #2F54EB;
    --ant-gold: #FAAD14;
    --ant-lime: #A0D911;
    --ant-color-primary: #1668dc;
    --ant-color-success: #49aa19;
    --ant-color-warning: #d89614;
    --ant-color-error: #dc4446;
    --ant-color-info: #1668dc;
    --ant-color-link: #1668dc;
    --ant-color-text-base: #fff;
    --ant-color-bg-base: #1a1b1f;
    --ant-font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
    --ant-font-family-code: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
    --ant-font-size: 14px;
    --ant-line-width: 1px;
    --ant-line-type: solid;
    --ant-motion-ease-out-circ: cubic-bezier(0.08, 0.82, 0.17, 1);
    --ant-motion-ease-in-out-circ: cubic-bezier(0.78, 0.14, 0.15, 0.86);
    --ant-motion-ease-out: cubic-bezier(0.215, 0.61, 0.355, 1);
    --ant-motion-ease-in-out: cubic-bezier(0.645, 0.045, 0.355, 1);
    --ant-motion-ease-out-back: cubic-bezier(0.12, 0.4, 0.29, 1.46);
    --ant-motion-ease-in-back: cubic-bezier(0.71, -0.46, 0.88, 0.6);
    --ant-motion-ease-in-quint: cubic-bezier(0.755, 0.05, 0.855, 0.06);
    --ant-motion-ease-out-quint: cubic-bezier(0.23, 1, 0.32, 1);
    --ant-border-radius: 6px;
    --ant-size-unit: 4px;
    --ant-size-step: 4px;
    --ant-size-popup-arrow: 16px;
    --ant-control-height: 32px;
    --ant-z-index-base: 0;
    --ant-z-index-popup-base: 1000;
    --ant-opacity-image: 1;
    --ant-color-bg-layout: #1a1b1f;
    --ant-color-bg-container: #23252b;
    --ant-color-bg-elevated: #2d2f37;
    --ant-blue-1: #111a2c;
    --ant-blue-2: #112545;
    --ant-blue-3: #15325b;
    --ant-blue-4: #15417e;
    --ant-blue-5: #1554ad;
    --ant-blue-6: #1668dc;
    --ant-blue-7: #3c89e8;
    --ant-blue-8: #65a9f3;
    --ant-blue-9: #8dc5f8;
    --ant-blue-10: #b7dcfa;
    --ant-purple-1: #1a1325;
    --ant-purple-2: #24163a;
    --ant-purple-3: #301c4d;
    --ant-purple-4: #3e2069;
    --ant-purple-5: #51258f;
    --ant-purple-6: #642ab5;
    --ant-purple-7: #854eca;
    --ant-purple-8: #ab7ae0;
    --ant-purple-9: #cda8f0;
    --ant-purple-10: #ebd7fa;
    --ant-cyan-1: #112123;
    --ant-cyan-2: #113536;
    --ant-cyan-3: #144848;
    --ant-cyan-4: #146262;
    --ant-cyan-5: #138585;
    --ant-cyan-6: #13a8a8;
    --ant-cyan-7: #33bcb7;
    --ant-cyan-8: #58d1c9;
    --ant-cyan-9: #84e2d8;
    --ant-cyan-10: #b2f1e8;
    --ant-orange-1: #2b1910;
    --ant-orange-7: #d89614;
    --ant-green-1: #162312;
    --ant-green-2: #1d3712;
    --ant-green-3: #274916;
    --ant-green-4: #306317;
    --ant-green-5: #3c8618;
    --ant-green-6: #49aa19;
    --ant-green-7: #6abe39;
    --ant-green-8: #8fd460;
    --ant-green-9: #b2e58b;
    --ant-green-10: #d5f2bb;
    --ant-geekblue-1: #e8f4fd;
    --ant-geekblue-7: #113598;
    --ant-color-text: rgba(255, 255, 255, 0.85);
    --ant-color-text-secondary: rgba(255, 255, 255, 0.65);
    --ant-color-text-tertiary: rgba(255, 255, 255, 0.45);
    --ant-color-text-quaternary: rgba(255, 255, 255, 0.25);
    --ant-color-fill: rgba(255, 255, 255, 0.18);
    --ant-color-fill-secondary: rgba(255, 255, 255, 0.12);
    --ant-color-fill-tertiary: rgba(255, 255, 255, 0.08);
    --ant-color-fill-quaternary: rgba(255, 255, 255, 0.04);
    --ant-color-bg-solid: rgba(255, 255, 255, 0.95);
    --ant-color-bg-text-hover: rgba(255, 255, 255, 0.12);
    --ant-color-bg-text-active: rgba(255, 255, 255, 0.18);
    --ant-color-border: #50566e;
    --ant-color-border-secondary: #414659;
    --ant-color-primary-bg: #15325b;
    --ant-color-primary-bg-hover: #15417e;
    --ant-color-primary-border: #15325b;
    --ant-color-primary-border-hover: #15417e;
    --ant-color-primary-hover: #3c89e8;
    --ant-color-primary-active: #1554ad;
    --ant-color-primary-text-hover: #3c89e8;
    --ant-color-primary-text: #1668dc;
    --ant-color-primary-text-active: #1554ad;
    --ant-color-success-bg: #162312;
    --ant-color-success-bg-hover: #1d3712;
    --ant-color-success-border: #274916;
    --ant-color-success-border-hover: #306317;
    --ant-color-success-hover: #306317;
    --ant-color-success-active: #3c8618;
    --ant-color-success-text-hover: #6abe39;
    --ant-color-success-text: #49aa19;
    --ant-color-success-text-active: #3c8618;
    --ant-color-error-bg: #2c1618;
    --ant-color-error-bg-hover: #451d1f;
    --ant-color-error-border: #5b2526;
    --ant-color-error-border-hover: #7e2e2f;
    --ant-color-error-hover: #e86e6b;
    --ant-color-error-active: #ad393a;
    --ant-color-error-text-hover: #e86e6b;
    --ant-color-error-text: #dc4446;
    --ant-color-error-text-active: #ad393a;
    --ant-color-warning-bg: #2b2111;
    --ant-color-warning-bg-hover: #443111;
    --ant-color-warning-border: #594214;
    --ant-color-warning-border-hover: #7c5914;
    --ant-color-warning-hover: #7c5914;
    --ant-color-warning-active: #aa7714;
    --ant-color-warning-text-hover: #e8b339;
    --ant-color-warning-text: #d89614;
    --ant-color-warning-text-active: #aa7714;
    --ant-color-info-bg: #111a2c;
    --ant-info-bg: #111a2c;
    --ant-color-info-bg-hover: #112545;
    --ant-color-info-border: #15325b;
    --ant-color-info-border-hover: #15417e;
    --ant-color-info-hover: #15417e;
    --ant-color-info-active: #1554ad;
    --ant-color-info-text-hover: #3c89e8;
    --ant-color-info-text: #1668dc;
    --ant-color-info-text-active: #1554ad;
    --ant-color-link-hover: #15417e;
    --ant-color-link-active: #1554ad;
    --ant-blue-hover: #1554ad;
    --ant-blue-active: #3c89e8;
    --ant-purple-hover: #51258f;
    --ant-purple-active: #854eca;
    --ant-cyan-hover: #138585;
    --ant-cyan-active: #33bcb7;
    --ant-green-hover: #3c8618;
    --ant-green-active: #6abe39;
    --ant-orange-hover: #aa6215;
    --ant-orange-active: #e89a3c;
    --ant-yellow-hover: #aa9514;
    --ant-yellow-active: #e8d639;
    --ant-color-bg-mask: rgba(0, 0, 0, 0.45);
    --ant-color-white: #fff;
    --ant-color-bg-container-disabled: rgba(255, 255, 255, 0.08);
    --ant-color-border-bg: #23252b;
    --ant-color-split: rgba(171, 187, 252, 0.22);
    --ant-color-text-placeholder: rgba(255, 255, 255, 0.25);
    --ant-color-text-disabled: rgba(255, 255, 255, 0.25);
    --ant-color-text-heading: rgba(255, 255, 255, 0.85);
    --ant-color-text-label: rgba(255, 255, 255, 0.65);
    --ant-color-text-description: rgba(255, 255, 255, 0.45);
    --ant-color-text-light-solid: #fff;
    --ant-color-highlight: #dc4446;
    --ant-color-icon: rgba(255, 255, 255, 0.45);
    --ant-color-icon-hover: rgba(255, 255, 255, 0.85);
    --ant-color-error-outline: rgba(55, 3, 0, 0.44);
    --ant-color-warning-outline: rgba(48, 30, 0, 0.6);
    --ant-font-weight-strong: 600;
    --ant-opacity-loading: 0.65;
    --ant-padding-xs: 8px;
    --ant-padding-sm: 12px;
    --ant-padding: 16px;
    --ant-margin-xs: 8px;
    --ant-margin-sm: 12px;
    --ant-margin: 16px;
    --ant-border-radius-sm: 4px;
    --ant-border-radius-lg: 8px;
    --ant-border-radius-outer: 4px;
    --ant-color-fill-content: rgba(255, 255, 255, 0.12);
    --ant-color-fill-content-hover: rgba(255, 255, 255, 0.18);
    --ant-color-fill-alter: rgba(255, 255, 255, 0.04);
    --ant-color-bg-text-hover: rgba(255, 255, 255, 0.12);
    --ant-color-bg-text-active: rgba(255, 255, 255, 0.18);
    --ant-tag-default-bg: rgba(255, 255, 255, 0.06);
}
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
                        const GIFT_USERS = <?= json_encode($giftMap, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
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
                                    class="absolute left-2 border rounded-md -top-1 bg-red-500/20 px-1 text-red-200 text-xs">Количество
                                    дней</label>
                                <input type="number" name="days" placeholder="от 1 до ∞"
                                    class="w-full px-3 py-2 rounded-lg bg-red-500/5 border border-red-500/20 text-gray-100 placeholder-gray-500 focus:ring-red-500 focus:outline-none mt-2"
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
                    $(document).on('click', '#user-summary-close,#user-summary-modal-bg', function () {
                        $('#user-summary-modal').addClass('hidden');
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
                        html: '<i class="fa-solid fa-circle-info"></i> ' + msg
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
                function destroyAdminCharts() {
                    $('[data-chart]').each(function () {
                        try { if (this.__chart) this.__chart.destroy(); } catch (e) {}
                        delete this.__chart; delete this.__built;
                    });
                    try { if (window.__plansChart) window.__plansChart.destroy(); } catch (e) {}
                    delete window.__plansChart; window.__plansBuilt = false;
                }
                function buildAdminCharts(client, financial, roi365) {
                if (typeof Chart === 'undefined') return;

                // График прибыли по месяцам
                $('[data-chart="chart_revenue_monthly"]:visible').each(function () {
                    if (this.__built) return; this.__built = true;
                    var mrc = ((roi365 || {}).monthly && roi365.monthly.length) ? roi365.monthly : (financial.monthlyRevenueChart || []);
                    if (!mrc.length) { delete this.__built; return; }
                    this.__chart = new Chart(this, {
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
                    if (!muc.length) { delete this.__built; return; }
                    this.__chart = new Chart(this, {
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
                    window.__sectionLoaded = window.__sectionLoaded || {};
                    window.__adminStats = null;
                    window.__roi30 = null;
                    window.__roi365 = null;
                    function fillStats(client, financial) {
                        var money = function (v) { return Number(v || 0).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
                        $('[data-st]').each(function () {
                            var k = $(this).attr('data-st'), v = client[k];
                            $(this).text(k === 'avgPrice' || k === 'maxPrice' ? money(v) : (v ?? '—'));
                        });
                        $('[data-st="mrr"]').text(money(financial.mrr));
                        $('[data-st]').removeClass('animate-pulse');
                        loadTodayUsers();
                        // Онлайн уже есть из мониторинга — лишний запрос не делаем
                        var ot = window.__overviewTotals || {};
                        if (ot.clients != null) {
                            $('[data-m-online]').text(ot.online_now ?? '—');
                        } else {
                            $.ajax({
                                url: '/api/admin/overview', method: 'GET', dataType: 'json', timeout: 60000,
                                success: function (o) {
                                    if (o && o.status === 'ok') {
                                        $('[data-m-online]').text((o.totals || {}).online_now ?? '—');
                                        window.__overviewTotals = o.totals || {};
                                    } else $('[data-m-online]').text('—');
                                },
                                error: function () { $('[data-m-online]').text('—'); }
                            });
                        }
                    }
                    function loadTodayUsers() {
                        $.ajax({
                            url: '/api/admin/today-users', method: 'GET', dataType: 'json', timeout: 15000,
                            success: function (r) {
                                var $box = $('[data-today-users]');
                                var users = (r && r.status === 'ok') ? (r.users || []) : [];
                                $('[data-today-users-count]').text(users.length + ' новых');
                                if (!users.length) {
                                    $box.html('<div class="text-gray-500 text-xs">Нет новых пользователей сегодня</div>');
                                    return;
                                }
                                $box.empty();
                                users.forEach(function (u) {
                                    var time = u.created_at ? new Date(u.created_at).toLocaleTimeString('ru-RU', {hour: '2-digit', minute: '2-digit'}) : '';
                                    var $row = $('<div>')
                                        .addClass('flex items-center gap-3 p-2 rounded-lg hover:bg-white/[0.05] transition-colors cursor-pointer group')
                                        .attr('data-user-row', u.uniID || u.id);
                                    var badge = (u.sub_status === 'on') ? 'bg-green-500/10 text-green-400 ring-1 ring-green-500/25'
                                               : (u.sub_status === 'off') ? 'bg-gray-500/10 text-gray-400 ring-1 ring-gray-500/25'
                                               : 'bg-yellow-500/10 text-yellow-400 ring-1 ring-yellow-500/25';
                                    $row.append(
                                        $('<div>').addClass('w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center shrink-0').append(
                                            $('<i>').addClass('fa-solid fa-envelope text-sm text-gray-400')
                                        ),
                                        $('<div>').addClass('flex-1 min-w-0').append(
                                            $('<div>').addClass('flex items-center gap-2').append(
                                                $('<span>').addClass('text-gray-300 text-sm font-medium truncate').text(u.email || '—'),
                                                $('<span>').addClass('inline-block px-2 py-0.5 rounded text-xs ' + badge).text(u.sub_status === 'on' ? 'Подписка' : (u.sub_status || 'нет подписки'))
                                            )
                                        ),
                                        $('<div>').addClass('text-xs text-gray-500').text(time)
                                    );
                                    $box.append($row);
                                });
                                $(document).off('click', '[data-user-row]').on('click', '[data-user-row]', function () {
                                    showUserModal($(this).attr('data-user-row'));
                                });
                            },
                            error: function () {
                                $('[data-today-users]').html('<div class="text-red-400 text-xs">Ошибка загрузки</div>');
                            }
                        });
                    }
                    function showUserModal(uniID) {
                        $('#user-summary-modal').removeClass('hidden');
                        $('#user-summary-content').html('<div class="text-gray-400 p-6">Загрузка…</div>');
                        $.ajax({
                            url: '/api/admin/user-summary?id=' + encodeURIComponent(uniID), method: 'GET', dataType: 'json', timeout: 15000,
                            success: function (r) {
                                if (!r || r.status !== 'ok') { $('#user-summary-content').html('<div class="text-red-400 p-4">Ошибка загрузки данных</div>'); return; }
                                var u = r.user || {};
                                var s = r.subscription || {};
                                var html = '';
                                html += '<div class="p-6">';
                                html += '<div class="flex items-center gap-3 mb-4">';
                                html += '<div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400"><i class="fa-solid fa-user"></i></div>';
                                html += '<div class="flex-1 min-w-0">';
                                html += '<div class="font-semibold text-white text-lg">' + (u.full_name || u.email || u.uniID || '—') + '</div>';
                                html += '<div class="text-sm text-gray-400 break-all">' + (u.email || '—') + '</div>';
                                html += '</div></div>';
                                html += '<div class="grid grid-cols-2 gap-3 text-sm">';
                                html += '<div class="bg-[#12161b] rounded-lg p-3"><div class="text-gray-500 text-xs">uniID</div><div class="text-white font-mono text-xs break-all">' + (u.uniID || '—') + '</div></div>';
                                html += '<div class="bg-[#12161b] rounded-lg p-3"><div class="text-gray-500 text-xs">Статус</div><div class="text-white">' + (s.status === 'on' ? '<span class="text-green-400">Активна</span>' : '<span class="text-gray-400">Неактивна</span>') + '</div></div>';
                                html += '<div class="bg-[#12161b] rounded-lg p-3"><div class="text-gray-500 text-xs">Срок действия</div><div class="text-white">' + (s.expiry ? new Date(s.expiry).toLocaleString('ru-RU') : '—') + '</div></div>';
                                html += '<div class="bg-[#12161b] rounded-lg p-3"><div class="text-gray-500 text-xs">Покупка</div><div class="text-white">' + (s.last_payment ? new Date(s.last_payment).toLocaleString('ru-RU') : '—') + '</div></div>';
                                html += '<div class="bg-[#12161b] rounded-lg p-3"><div class="text-gray-500 text-xs">Пробная подписка</div><div class="text-white">' + (s.gift_used ? '<span class="text-green-400">Да</span>' : '<span class="text-gray-400">Нет</span>') + '</div></div>';
                                html += '<div class="bg-[#12161b] rounded-lg p-3"><div class="text-gray-500 text-xs">Реферал</div><div class="text-white">' + (u.myrefer ? u.myrefer : '—') + '</div></div>';
                                html += '</div></div>';
                                html += '<div class="text-xs text-gray-500 mt-3" data-user-activity>Активность: загрузка...</div>';
                                $('#user-summary-content').html(html);
                                $.ajax({
                                    url: '/api/admin/user-activity?id=' + encodeURIComponent(uniID), method: 'GET', dataType: 'json', timeout: 10000,
                                success: function (a) {
                                    if (a && a.status === 'ok') {
                                        var acts = (a.actions || []).map(function(e) {
                                            return '<span class="inline-block mr-3">' + (e.action || '') + ' (' + (e.when || '') + ')</span>';
                                        }).join('');
                                        $('[data-user-activity]').html('Активность: ' + (acts || 'нет данных'));
                                    } else {
                                        $('[data-user-activity]').html('Активность: <span class="text-gray-500">нет данных</span>');
                                    }
                                },
                                error: function () {
                                    $('[data-user-activity]').html('Активность: <span class="text-gray-500">нет данных</span>');
                                }
                                });
                            },
                            error: function () { $('#user-summary-content').html('<div class="text-red-400 p-4">Ошибка загрузки</div>'); }
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
                $('[data-st],[data-kr]').addClass('animate-pulse');
                    // Старт: только видимая секция. /stats нужен главной (data-st) — остальным подтянется при открытии.
                    var statsBusy = false;
                    function loadStats(done, fail) {
                        if (statsBusy) return;
                        statsBusy = true;
                        $.ajax({
                            url: '/api/admin/stats', method: 'GET', dataType: 'json', timeout: 30000,
                            success: function (d) {
                                if (!d || d.status !== 'ok') { if (typeof fail === 'function') fail(); return; }
                                window.__adminStats = d;
                                fillStats(d.client || {}, d.financial || {});
                                if (typeof done === 'function') done(d);
                            },
                            error: function () { if (typeof fail === 'function') fail(); },
                            complete: function () { statsBusy = false; }
                        });
                    }
                    if ($('[data-section="main"]:not(.hidden)').length) {
                        setTimeout(function () { loadOverview(false); loadTodayUsers(); }, 50);
                        $('[data-chart-empty="chart_new_users"]').text('Собираю данные…').show();
                        loadStats(function () { ensureCharts(); }, function () {
                            $('[data-chart-empty="chart_new_users"]').text('Ошибка загрузки статистики. Обновите страницу.').show();
                        });
                    }
                    function chartsVisible() { return $('[data-section="charts"]:not(.hidden)').length > 0; }
                    var chartsBusy = false, chartsSpinHtml = null;
                    function chartsLoad(force) {
                        if (!force && !chartsVisible()) return;
                        if (chartsBusy) return;
                        if (!force && window.__chartsDone) return;
                        chartsBusy = true;
                        var $box = $('[data-charts-loading]');
                        if (chartsSpinHtml === null) chartsSpinHtml = $box.html();
                        $box.html(chartsSpinHtml).show();
                        $('[data-charts-updated]').text('');
                        loadStats(function () {
                            ensureCharts();
                            window.__chartsDone = true;
                            $box.hide();
                            $('[data-charts-updated]').text('обновлено ' + new Date().toLocaleTimeString('ru-RU'));
                            chartsBusy = false;
                        }, function () {
                            $box.html('<span class="text-red-400">Ошибка загрузки. Нажмите «Обновить».</span>').show();
                            chartsBusy = false;
                        });
                    }
                    function loadCashRoi() {
                        if (window.__sectionLoaded._cash) return;
                        window.__sectionLoaded._cash = true;
                        $.ajax({ url: '/api/admin/roi?days=30', method: 'GET', dataType: 'json', timeout: 120000,
                            success: function (r) { window.__roi30 = r; fillKassa(r); } });
                        $.ajax({ url: '/api/admin/roi?days=365', method: 'GET', dataType: 'json', timeout: 120000,
                            success: function (r) { window.__roi365 = r; ensureCharts(); } });
                    }
                    function ensureCharts() {
                        var d = window.__adminStats;
                        if (d) buildAdminCharts(d ? (d.client || {}) : {}, d ? (d.financial || {}) : {}, window.__roi365);
                        if (chartsVisible() && !window.__plansBuilt && typeof window.buildPlansChart === 'function') {
                            buildPlansChart((window.__roi365 || {}).by_tariff || [], (window.__adminStats || {})?.financial);
                        }
                        if ($('[data-section="main"]:not(.hidden)').length && window.__adminStats) {
                            if (typeof window.renderMainCharts === 'function') renderMainCharts(window.__overviewTotals || {});
                        }
                        if (chartsVisible() && !window.__sectionLoaded._cash) {
                            $('[data-st],[data-kr]').addClass('animate-pulse');
                            loadCashRoi();
                        }
                    }
                    $(document).on('click', '[data-toggle-section]', function () { setTimeout(ensureCharts, 350); });
                    $(document).on('click', '[data-toggle-section="charts"]', function () { setTimeout(function () { chartsLoad(false); }, 350); });
                    $(document).on('click', '[data-charts-refresh]', function () {
                        if (typeof window.destroyAdminCharts === 'function') window.destroyAdminCharts();
                        window.__chartsDone = false;
                        window.__sectionLoaded._cash = false;
                        chartsLoad(true);
                    });
                    chartsLoad(false);
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