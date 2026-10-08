<?php
$site = Setting\Route\Function\Functions::site();
$groups = new Setting\Route\Function\Controllers\Admin\Group\Groups();//вызываем класс
$adminMenu = new Setting\Route\Function\Controllers\Admin\Admin();//вызываем класс
$menuId = (int) (App\Config\Session::init('admin')['auth'][1] ?? 0);//кто сидит [true, id]
$adminUsername = $adminMenu->getUsername($menuId);
$adminRole = $adminMenu->getRole($menuId);
?>

<aside id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-50 w-[260px] -translate-x-full transition-all duration-300 md:relative md:translate-x-0 flex flex-col shadow-xl bg-[#16181d] text-gray-200 shrink-0">
    <style>
        #admin-sidebar .bg_active{background:rgba(47,129,247,.18)!important;color:#fff!important;border-radius:.5rem}
        #admin-sidebar .bg_active i{color:#6ea8fe!important}
        #admin-sidebar summary::-webkit-details-marker{display:none}
        #admin-sidebar .arrow{border-color:#6b7280}
        @media(min-width:768px){
            #admin-sidebar.sb-min{width:64px}
            #admin-sidebar.sb-min .sb-inner{min-width:0!important}
            #admin-sidebar.sb-min h5,
            #admin-sidebar.sb-min .sb-uinfo,
            #admin-sidebar.sb-min ul.menu a span,
            #admin-sidebar.sb-min ul.menu summary>span:last-child,
            #admin-sidebar.sb-min ul.menu summary>.fa-angle-right,
            #admin-sidebar.sb-min .fa-lock{display:none!important}
            #admin-sidebar.sb-min ul.menu a{justify-content:center;padding-left:.5rem;padding-right:.5rem}
            #admin-sidebar.sb-min ul.menu summary{justify-content:center}
            #admin-sidebar.sb-min ul.menu ul{display:none}
            #admin-sidebar.sb-min button[data-toggle-section]{font-size:0;justify-content:center}
        }
    </style>
    <script>
        $(function () {
            var KEY = 'qwees_sb_min';
            function sbApply(min) {
                $('#admin-sidebar').toggleClass('sb-min', !!min);
                try { localStorage.setItem(KEY, min ? '1' : '0'); } catch (e) {}
                $('#sb-collapse i').attr('class', min ? 'fa-solid fa-angles-right text-sm' : 'fa-solid fa-angles-left text-sm');
            }
            var init = false;
            try { init = localStorage.getItem(KEY) === '1'; } catch (e) {}
            if (window.matchMedia('(min-width:768px)').matches) sbApply(init);
            $('#sb-collapse').on('click', function () { sbApply(!$('#admin-sidebar').hasClass('sb-min')); });
            $('#admin-sidebar ul.menu a').each(function () {
                var t = $(this).find('span').first().text().trim();
                if (t && !$(this).attr('title')) $(this).attr('title', t);
            });
            $(document).on('click', '#admin-sidebar.sb-min summary', function () { sbApply(false); });
        });
    </script>
    <!-- main -->
    <div class="flex flex-col p-4 gap-4 min-w-[260px] sb-inner">
        <div class="flex flex-1 justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <img decoding="async" loading="lazy" src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/avatar/2.png"
                    class="rounded-full aspect-square w-10 h-10 shrink-0">
                <div class="flex flex-col gap-0.5 min-w-0 sb-uinfo">
                    <div class="flex gap-2 items-center">
                        <span class="font-medium truncate text-white">
                            <?= htmlspecialchars($adminUsername) ?>
                        </span>
                        <span
                            class="flex items-center py-0 px-1 bg-purple-500/15 rounded-md font-medium text-purple-300 text-sm shrink-0"><?= htmlspecialchars($adminRole) ?></span><!-- кто сидит + роль -->
                    </div>
                </div>
            </div>
            <div class="flex gap-1 shrink-0 items-center">
                <!-- УВЕДОМЛЕНИЕ
                <button class="inline-flex p-1 flex justify-center items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </button>-->
                <div class="relative">
                    <button id="sb-collapse" title="Свернуть меню"
                        class="inline-flex p-1.5 justify-center items-center text-gray-400 hover:text-white hover:bg-white/5 rounded-md transition-colors">
                        <i class="fa-solid fa-angles-left text-sm"></i>
                    </button>
                </div>
                <div class="relative">
                    <button class="inline-flex p-1 flex justify-center items-center" id="openbtn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                    </button>
                    <div class="z-[99] absolute bg-[#1e2128] ring-1 ring-white/10 rounded-md shadow-lg mt-1 w-48 hidden" id="dropdown_menu">
                        <ul class="py-1 px-2 text-sm text-gray-300">
                            <li class="block px-4 py-2 text-sm text-red-500 hover:bg-red-500/10 bg-red-500/10 rounded-md">
                                <form action="/admin/logout" method="POST">
                                    <button type="submit" class="w-full text-left">Выйти</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <script defer>
                    $(document).ready(function () {
                        $('#openbtn').click(() => {
                            $('#dropdown_menu').slideToggle();
                        });
                    });
                </script>
            </div>
        </div>
        <div class="flex flex-1 gap-3">
            <?php if ($groups->isPermission($adminUsername,'add_user')): ?>
                <button data-toggle-section="add_user"
                    class="relative inline-flex shadow-sm bg-white/5 w-full justify-center rounded-xl text-sm font-semibold py-2 px-3 text-gray-100 ring-1 ring-white/10 hover:ring-white/25 items-center gap-2">
                    <svg class="text-gray-400 w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4V20M4 12H20" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                    </svg>
                    Добавить пользователя
                </button>
            <? else: ?>
            <button data-toggle-section="add_user"
                class="relative inline-flex shadow-sm bg-white/5 w-full justify-center rounded-xl text-sm font-semibold py-2 px-3 text-gray-100 ring-1 ring-white/10 hover:ring-white/25 items-center gap-2">
                <svg class="text-gray-400 w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 4V20M4 12H20" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                </svg>
                Добавить пользователя
                <span class="left-2.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
            </button>
            <? endif; ?>
        </div>
    </div>
    <!-- menu -->
    <div class="flex px-4 flex-col mb-4">
        <h5 class="text-gray-500 text-sm mb-3">Меню</h5>
        <ul class="menu menu-xs rounded-box max-w-xs w-full text-sm flex flex-col">
            <!-- Главная -->
            <?php if ($groups->isPermission($adminUsername,'main')): ?>
                <li class="list-none relative" data-toggle-section="main">
                    <a
                        class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                        <i class="fa-regular fa-house text-gray-500"></i>
                        <span>Главная</span>
                    </a>
                </li>
            <?php else: ?>
            <li class="list-none relative">
                <a
                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                    <i class="fa-regular fa-house text-gray-500"></i>
                    <span>Главная</span>
                </a>
                <span class="left-1 bottom-0 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
            </li>
            <?php endif; ?>
            <!-- Мониторинг -->
            <?php if ($groups->isPermission($adminUsername,'monitoring')): ?>
                <li class="list-none relative" data-toggle-section="monitoring">
                    <a
                        class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                        <i class="fa-solid fa-heart-pulse text-gray-500"></i>
                        <span>Мониторинг</span>
                        <span data-ov-menu-dot class="ml-auto hidden w-2 h-2 rounded-full bg-green-500"></span>
                    </a>
                </li>
            <?php else: ?>
            <li class="list-none relative">
                <a
                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                    <i class="fa-solid fa-heart-pulse text-gray-500"></i>
                    <span>Мониторинг</span>
                </a>
                <span class="left-1 bottom-0 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
            </li>
            <?php endif; ?>
            <!-- Чат поддержки -->
            <?php $chatUnread = (new \Setting\Route\Function\Controllers\Chat\Chat())->getTotalUnread(); ?>
            <li class="list-none relative" data-toggle-section="chat">
                <a
                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                    <i class="fa-regular fa-comments text-gray-500"></i>
                    <span>Чат</span>
                    <span data-chat-menu-badge
                        class="ml-auto text-[11px] font-bold text-white bg-red-500 rounded-full min-w-[20px] h-5 px-1.5 items-center justify-center <?= $chatUnread > 0 ? 'inline-flex' : 'hidden' ?>"><?= (int) $chatUnread ?></span>
                </a>
            </li>
            <!-- Пробная подписка -->
            <li class="list-none relative" data-toggle-section="gifts">
                <a
                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                    <i class="fa-solid fa-gift text-gray-500"></i>
                    <span>Пробные подписки</span>
                </a>
            </li>
            <!-- Реферальная система -->
            <li class="list-none relative" data-toggle-section="refer">
                <a
                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                    <i class="fa-solid fa-users text-gray-500"></i>
                    <span>Рефералка</span>
                </a>
            </li>
            <!-- element 2 -->
            <li class="list-none relative">
                <details>
                    <summary
                        class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150 list-none">
                        <i class="fa-solid fa-angle-right text-gray-500"></i>
                        <i class="fa-regular fa-folder f-closed text-gray-500"></i><i class="fa-regular fa-folder-open f-open text-gray-500"></i>
                        <span>Управление</span>
                    </summary>
                    <ul class="pl-5 m-0 relative">
                        <!-- price setting -->
                        <?php if ($groups->isPermission($adminUsername,'price')): ?>
                            <li class="list-none relative" data-toggle-section="price">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-ruble-sign text-gray-500"></i>
                                    <span>Настройка цен</span>
                                </a>
                            </li>
                        <?php else: ?>
                        <li class="list-none relative" data-toggle-section="price">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                <i class="fa-solid fa-ruble-sign text-gray-500"></i>
                                <span>Настройка цен</span>
                            </a>
                            <span class="left-0.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                        </li>
                        <?php endif; ?>
                        <!-- Панель выдачи -->
                        <?php if ($groups->isPermission($adminUsername,'give')): ?>
                            <li class="list-none relative" data-toggle-section="give">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-key text-gray-500"></i>
                                    <span>Панель выдачи подписок</span>
                                </a>
                            </li>
                        <?php else: ?>
                        <li class="list-none relative" data-toggle-section="give">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                <i class="fa-solid fa-key text-gray-500"></i>
                                <span>Панель выдачи подписок</span>
                            </a>
                            <span class="left-0.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                        </li>
                        <?php endif; ?>
                        <!-- Панель изьятия -->
                        <?php if ($groups->isPermission($adminUsername,'reduce')): ?>
                            <li class="list-none relative" data-toggle-section="reduce">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-user-slash text-gray-500"></i>
                                    <span>Панель изьятия подписок</span>
                                </a>
                            </li>
                        <?php else: ?>
                        <li class="list-none relative" data-toggle-section="reduce">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                <i class="fa-solid fa-user-slash text-gray-500"></i>
                                <span>Панель изьятия подписок</span>
                            </a>
                            <span class="left-0.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                        </li>
                        <?php endif; ?>
                        <!-- База данных -->
                        <details>
                            <summary
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150 list-none">
                                <i class="fa-solid fa-angle-right text-gray-500"></i>
                                <i class="fa-regular fa-folder f-closed text-gray-500"></i><i class="fa-regular fa-folder-open f-open text-gray-500"></i>
                                <span>База данных</span>
                            </summary>
                            <ul class="pl-5 m-0 relative">
                                <?php foreach (Setting\Route\Function\Controllers\Admin\AdminDatabase::getTables() as $tableName): ?>
                                    <?php if ($groups->isPermission($adminUsername,'database')): ?>
                                        <li class="list-none relative" data-toggle-section="database">
                                            <a href="/admin/database?table=<?= urlencode($tableName) ?>"
                                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                                <i class="fa-solid fa-database text-gray-500"></i>
                                                <span><?= htmlspecialchars($tableName) ?></span>
                                            </a>
                                        </li>
                                    <?php else: ?>
                                    <li class="list-none relative" data-toggle-section="database">
                                            <a href="/admin/database?table=<?= urlencode($tableName) ?>"
                                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                                <i class="fa-solid fa-database text-gray-500"></i>
                                                <span><?= htmlspecialchars($tableName) ?></span>
                                            </a>
                                            <span class="left-0.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    </ul>
                </details>
            </li>
            <!-- element 3 -->
            <li class="list-none relative">
                <details>
                    <summary
                        class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150 list-none">
                        <i class="fa-solid fa-angle-right text-gray-500"></i>
                        <i class="fa-regular fa-folder f-closed text-gray-500"></i><i class="fa-regular fa-folder-open f-open text-gray-500"></i>
                        <span>Аналитика</span>
                    </summary>
                    <ul class="pl-5 m-0 relative">
                        <!-- charts -->
                        <?php if ($groups->isPermission($adminUsername,'charts')): ?>
                            <li class="list-none relative" data-toggle-section="charts">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-chart-line text-gray-500"></i>
                                    <span>Графики</span>
                                </a>
                            </li>
                        <?php else: ?>
                        <li class="list-none relative" data-toggle-section="charts">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                <i class="fa-solid fa-chart-line text-gray-500"></i>
                                <span>Графики</span>
                            </a>
                            <span class="left-0.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                        </li>
                        <?php endif; ?>

                        <!-- profit -->
                        <?php if ($groups->isPermission($adminUsername,'profit')): ?>
                            <li class="list-none relative" data-toggle-section="profit">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-wallet text-gray-500"></i>
                                    <span>Прибыль</span>
                                </a>
                            </li>
                        <?php else: ?>
                        <li class="list-none relative" data-toggle-section="profit">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                <i class="fa-solid fa-wallet text-gray-500"></i>
                                <span>Прибыль</span>
                            </a>
                            <span class="left-0.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                        </li>
                        <?php endif; ?>

                        <!-- roi -->
                        <?php if ($groups->isPermission($adminUsername,'roi')): ?>
                            <li class="list-none relative" data-toggle-section="roi">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-calculator text-gray-500"></i>
                                    <span>ROI</span>
                                </a>
                            </li>
                        <?php else: ?>
                        <li class="list-none relative" data-toggle-section="roi">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                <i class="fa-solid fa-calculator text-gray-500"></i>
                                <span>ROI</span>
                            </a>
                            <span class="left-0.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                        </li>
                        <?php endif; ?>

                        <!-- log see -->
                        <?php if ($groups->isPermission($adminUsername,'logs')): ?>
                            <li class="list-none relative" data-toggle-section="logs">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-scroll text-gray-500"></i>
                                    <span>Логи</span>
                                </a>
                            </li>
                        <?php else: ?>
                        <li class="list-none relative" data-toggle-section="logs">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                <i class="fa-solid fa-scroll text-gray-500"></i>
                                <span>Логи</span>
                            </a>
                            <span class="left-0.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </details>
            </li>
            <!-- element 4 -->
            <li class="list-none relative">
                <details>
                    <summary
                        class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150 list-none">
                        <i class="fa-solid fa-angle-right text-gray-500"></i>
                        <i class="fa-regular fa-folder f-closed text-gray-500"></i><i class="fa-regular fa-folder-open f-open text-gray-500"></i>
                        <span>Администрирование</span>
                    </summary>
                    <ul class="pl-5 m-0 relative">
                        <!-- Роли -->
                        <!-- Роли и права -->
                        <?php if ($groups->isPermission($adminUsername, 'roles')): ?>
                            <li class="list-none relative" data-toggle-section="roles">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-user-shield text-gray-500"></i>
                                    <span>Роли и права</span>
                                </a>
                            </li>
                        <?php else: ?>
                        <li class="list-none relative" data-toggle-section="roles">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                <i class="fa-solid fa-user-shield text-gray-500"></i>
                                <span>Роли и права</span>
                            </a>
                            <span class="left-0.5 -bottom-1 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>            </li>
                        <?php endif; ?>
                    </ul>
                </details>
            </li>
            <!-- Папка: Серверы -->
            <li class="list-none relative">
                <details>
                    <summary
                        class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150 list-none">
                        <i class="fa-solid fa-angle-right text-gray-500"></i>
                        <i class="fa-regular fa-folder f-closed text-gray-500"></i><i class="fa-regular fa-folder-open f-open text-gray-500"></i>
                        <span>Серверы</span>
                    </summary>
                    <ul class="pl-5 m-0 relative">
                        <!-- Список и управление -->
                        <?php if ($groups->isPermission($adminUsername,'servers')): ?>
                            <li class="list-none relative" data-toggle-section="servers">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-server text-gray-500"></i>
                                    <span>Серверы</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="list-none relative">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-list text-gray-500"></i>
                                    <span>Серверы</span>
                                </a>
                                <span class="left-1 bottom-0 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                            </li>
                        <?php endif; ?>
                        <!-- Переезд -->
                        <?php if ($groups->isPermission($adminUsername,'relocation')): ?>
                            <li class="list-none relative" data-toggle-section="relocation">
                                <a
                                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                    <i class="fa-solid fa-truck-fast text-gray-500"></i>
                                    <span>Переезд</span>
                                </a>
                            </li>
                        <?php else: ?>
                        <li class="list-none relative">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-300 hover:bg-white/5 transition-colors duration-150">
                                <i class="fa-solid fa-truck-fast text-gray-500"></i>
                                <span>Переезд</span>
                            </a>
                            <span class="left-1 bottom-0 text-[11px] absolute inset-0 flex items-center justify-center text-red-500 fa fa-lock"></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </details>
            </li>
            
        </ul>
    </div>
</aside>