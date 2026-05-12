<?php
$site = Setting\Route\Function\Functions::site();
?>

<aside class="flex flex-col shadow-xl bg-white rounded-xl shrink-0">
    <!-- main -->
    <div class="flex flex-col p-4 gap-4 min-w-[260px]">
        <div class="flex flex-1 justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <img decoding="async" loading="lazy" src="<?= $site['baseUrl'] ?>/public/assets/images/icons/services/avatar/2.png"
                    class="rounded-full aspect-square w-10 h-10 shrink-0">
                <div class="flex flex-col gap-0.5 min-w-0">
                    <div class="flex gap-2 items-center">
                        <span class="font-medium truncate">
                            <?= htmlspecialchars($site['контакты']['Директор']) ?>
                        </span>
                        <span
                            class="flex items-center py-0 px-1 bg-[#ece0f7] rounded-md font-medium text-[#593597] text-sm shrink-0"><?= Setting\Route\Function\Controllers\Admin\AdminAuth::getRole(App\Config\Session::init('admin')['auth'][1]) ?></span><!-- get role - session получения id админа [true, id] -->
                    </div>
                    <span class="text-slate-400 text-sm truncate">
                        <?= htmlspecialchars($site['ООО']) ?>
                    </span>
                </div>
            </div>
            <div class="flex gap-1 shrink-0 items-center">
                <button class="inline-flex p-1 flex justify-center items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </button>
                <div class="relative">
                    <button class="inline-flex p-1 flex justify-center items-center" id="openbtn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                    </button>
                    <div class="absolute bg-white rounded-md shadow-lg mt-1 w-48 hidden" id="dropdown_menu">
                        <ul class="py-1 px-2 text-sm text-gray-700">
                            <li class="block px-4 py-2 text-sm text-red-500 hover:bg-red-100 bg-red-50 rounded-md">
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
            <button data-toggle-section="add_user"
                class="inline-flex shadow-sm bg-white w-full justify-center rounded-xl text-sm font-semibold py-2 px-3 text-slate-900 ring-1 ring-slate-900/10 hover:ring-slate-900/20 items-center gap-2">
                <svg class="text-gray-600 w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 4V20M4 12H20" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                </svg>
                Добавить пользователя
            </button>
            <!-- <button
                class="inline-flex shadow-sm bg-white w-full justify-center rounded-xl text-sm font-semibold p-1 px-2 flex-1 text-slate-900 ring-1 ring-slate-900/10 hover:ring-slate-900/20 items-center gap-2">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17 17L22 22M19.5 10.75C19.5 15.5825 15.5825 19.5 10.75 19.5C5.91751 19.5 2 15.5825 2 10.75C2 5.91751 5.91751 2 10.75 2C15.5825 2 19.5 5.91751 19.5 10.75Z"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                </svg>
            </button> -->
        </div>
    </div>
    <!-- menu -->
    <div class="flex px-4 flex-col mb-4">
        <h5 class="text-slate-600 text-sm mb-3">Меню</h5>
        <ul class="menu menu-xs bg-base-200 rounded-box max-w-xs w-full text-sm flex flex-col">
            <!-- Главная -->
            <li class="list-none relative" data-toggle-section="main">
                <a
                    class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                    <i class="fa-regular fa-house text-gray-500"></i>
                    <span>Главная</span>
                </a>
            </li>
            <!-- element 2 -->
            <li class="list-none relative">
                <details>
                    <summary
                        class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150 list-none">
                        <span class="arrow shrink-0"></span>
                        <i class="fa-regular fa-folder text-gray-500"></i>
                        <span>Управление</span>
                    </summary>
                    <ul class="pl-5 m-0 relative">
                        <!-- price setting -->
                        <li class="list-none relative" data-toggle-section="price">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <i class="fa-solid fa-ruble-sign text-gray-500"></i>
                                <span>Настройка цен</span>
                            </a>
                        </li>
                        <!-- Панель выдачи -->
                        <li class="list-none relative" data-toggle-section="give">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <i class="fa-brands fa-unsplash text-gray-500"></i>
                                <span>Панель выдачи подписок</span>
                            </a>
                        </li>
                        <!-- Панель выдачи -->
                        <li class="list-none relative" data-toggle-section="reduce">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <i class="fa-solid fa-user-slash text-gray-500"></i>
                                <span>Панель изьятия подписок</span>
                            </a>
                        </li>
                        <!-- База данных -->
                        <details>
                            <summary
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150 list-none">
                                <span class="arrow shrink-0"></span>
                                <i class="fa-regular fa-folder text-gray-500"></i>
                                <span>База данных</span>
                            </summary>
                            <ul class="pl-5 m-0 relative">
                                <?php foreach (Setting\Route\Function\Controllers\Admin\AdminDatabase::getTables() as $tableName): ?>
                                    <li class="list-none relative" data-toggle-section="database">
                                        <a href="/admin/database?table=<?= urlencode($tableName) ?>"
                                            class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                            <i class="fa-solid fa-database text-gray-500"></i>
                                            <span><?= htmlspecialchars($tableName) ?></span>
                                        </a>
                                    </li>
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
                        class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150 list-none">
                        <span class="arrow shrink-0"></span>
                        <i class="fa-regular fa-folder text-gray-500"></i>
                        <span>Аналитика</span>
                    </summary>
                    <ul class="pl-5 m-0 relative">
                        <!-- charts -->
                        <li class="list-none relative" data-toggle-section="charts">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <i class="fa-solid fa-chart-line text-gray-500"></i>
                                <span>Графики</span>
                            </a>
                        </li>

                        <!-- log see -->
                        <li class="list-none relative" data-toggle-section="logs">
                            <a
                                class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <i class="fa-solid fa-scroll text-gray-500"></i>
                                <span>Логи</span>
                            </a>
                        </li>
                    </ul>
                </details>
            </li>
        </ul>
    </div>
</aside>