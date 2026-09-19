<?php
// Компонент чата техподдержки (админка).
// Подключение из admin/index.php. Светлая тема, только русский.
$base = $site['baseUrl'] ?? '';
?>
<script src="<?= htmlspecialchars($base) ?>/public/assets/scripts/chat/photo.js<?= '?v=' . ($site['versionApp'] ?? '1') ?>"></script>
<div data-admin-chat class="bg-white rounded-xl shadow-sm overflow-hidden"
    data-avatar-user="<?= htmlspecialchars($base . '/public/assets/images/icons/services/avatar/1.png') ?>"
    data-avatar-admin="<?= htmlspecialchars($base . '/public/assets/images/icons/services/avatar/2.png') ?>">
    <!-- Шапка -->
    <div class="flex items-center justify-between gap-3 px-6 py-4 border-b border-gray-100">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-green-600/10 flex items-center justify-center shrink-0">
                <i class="fa-regular fa-comments text-green-700 text-lg"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-lg font-bold text-gray-800 leading-tight">Чат поддержки</h2>
                <p data-admin-stats class="text-xs text-gray-500 truncate">Загрузка...</p>
            </div>
        </div>
        <button type="button" data-admin-refresh title="Обновить"
            class="p-2.5 rounded-lg text-gray-500 hover:text-green-700 hover:bg-green-50 transition-colors cursor-pointer shrink-0">
            <i class="fa-solid fa-rotate-right"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-[300px_1fr]">
        <!-- Список диалогов -->
        <div data-admin-dialogs class="flex flex-col gap-1.5 p-3 md:border-r md:border-gray-100 max-h-[300px] md:max-h-[560px] overflow-y-auto">
            <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-gray-300"></i>
                <div class="text-sm text-gray-400">Загрузка диалогов...</div>
            </div>
        </div>

        <!-- Переписка -->
        <div class="flex flex-col min-w-0 border-t md:border-t-0 border-gray-100">
            <!-- Панель диалога: кто + действия -->
            <div class="flex flex-wrap items-center gap-2 px-4 py-2.5 border-b border-gray-100 bg-white min-h-[52px]">
                <span data-admin-current class="font-mono text-xs text-gray-500 truncate">Диалог не выбран</span>
                <span data-admin-closed class="hidden text-[11px] font-semibold text-gray-500 bg-gray-100 rounded-full px-2.5 py-1 shrink-0">завершён</span>
                <span class="flex-1"></span>
                <button type="button" data-admin-profile-btn disabled title="Профиль пользователя"
                    class="px-3 py-2 rounded-lg text-gray-600 hover:text-green-700 hover:bg-green-50 transition-colors cursor-pointer disabled:opacity-40 shrink-0 flex items-center gap-1.5 text-xs font-semibold">
                    <i class="fa-regular fa-user"></i>
                    <span class="hidden sm:inline">Данные</span>
                </button>
                <button type="button" data-admin-close disabled title="Завершить диалог"
                    class="px-3 py-2 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors cursor-pointer disabled:opacity-40 shrink-0">
                    Завершить
                </button>
                <button type="button" data-admin-clear disabled title="Очистить диалог (удалить с сервера)"
                    class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer disabled:opacity-40 shrink-0">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </div>
            <div data-admin-thread class="flex-1 min-h-[280px] max-h-[360px] md:max-h-[420px] overflow-y-auto bg-gray-50/70 p-4">
                <div class="flex flex-col items-center justify-center h-full min-h-[240px] gap-2 text-center">
                    <i class="fa-regular fa-comment-dots text-4xl text-gray-200"></i>
                    <div class="text-sm text-gray-400">Выберите диалог слева,<br>чтобы увидеть переписку</div>
                </div>
            </div>
            <div class="p-3 border-t border-gray-100 bg-white">
                <div class="flex items-end gap-1.5 rounded-[28px] border border-gray-200 bg-gray-50 p-1.5 transition-all focus-within:border-green-500 focus-within:ring-2 focus-within:ring-green-100">
                    <button type="button" data-admin-attach title="Прикрепить фото"
                        class="w-10 h-10 shrink-0 rounded-full text-gray-400 hover:text-green-700 hover:bg-green-50 transition-colors flex items-center justify-center cursor-pointer">
                        <i class="fa-regular fa-image"></i>
                    </button>
                    <input type="file" data-admin-file accept="image/jpeg,image/png,image/gif,image/webp,image/heic,image/heif" class="hidden">
                    <input type="text" data-admin-input disabled
                        class="flex-1 bg-transparent px-3 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-none disabled:opacity-60"
                        placeholder="Сначала выберите диалог..." maxlength="2000" autocomplete="off">
                    <button type="button" data-admin-send disabled title="Отправить"
                        class="w-10 h-10 shrink-0 rounded-full bg-green-600 hover:bg-green-500 text-white transition-colors flex items-center justify-center cursor-pointer disabled:opacity-40">
                        <i class="fa-solid fa-arrow-up text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка: профиль пользователя -->
    <div data-admin-modal class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div data-admin-modal-bg class="absolute inset-0 bg-black/40"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[85vh] overflow-y-auto">
            <div class="flex items-center gap-4 px-6 pt-6 pb-5 border-b border-gray-100">
                <img data-admin-profile-avatar src="" alt=""
                    class="w-16 h-16 rounded-full object-cover ring-2 ring-green-100 shrink-0">
                <div class="flex-1 min-w-0">
                    <div data-admin-profile-name class="text-xl font-bold text-gray-900 truncate">...</div>
                    <div data-admin-profile-email class="text-sm text-gray-500 truncate">...</div>
                    <div class="mt-1.5">
                        <span data-admin-profile-status
                            class="inline-block text-[11px] font-semibold rounded-full px-2.5 py-1 text-gray-500 bg-gray-100">...</span>
                    </div>
                </div>
                <button type="button" data-admin-modal-close
                    class="self-start p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div data-admin-profile class="p-6 pt-4 text-sm text-gray-500">Загрузка...</div>
        </div>
    </div>
</div>

<script>
$(function () {
    if (window.__chatAdminInit) return;
    window.__chatAdminInit = true;

    function esc(text) {
        return $('<div>').text(text ?? '').html();
    }

    function isVisible(el) {
        return !document.hidden && !!(el.offsetWidth || el.offsetHeight);
    }

    function plural(n, forms) {
        n = Math.abs(n) % 100;
        const d = n % 10;
        if (n > 10 && n < 20) return forms[2];
        if (d > 1 && d < 5) return forms[1];
        if (d === 1) return forms[0];
        return forms[2];
    }

    function fmtTime(raw) {
        if (!raw) return '';
        const d = new Date(String(raw).replace(' ', 'T'));
        if (isNaN(d)) return String(raw);
        const now = new Date();
        const time = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        if (d.toDateString() === now.toDateString()) return time;
        return String(d.getDate()).padStart(2, '0') + '.' + String(d.getMonth() + 1).padStart(2, '0') + ', ' + time;
    }

    function shortId(uniID) {
        uniID = String(uniID || '');
        return uniID.length > 14 ? uniID.slice(0, 14) + '...' : uniID;
    }

    function setStats(root, dialogs) {
        const unread = dialogs.reduce(function (s, d) { return s + (d.unread || 0); }, 0);
        const online = dialogs.filter(function (d) { return d.online; }).length;
        const dWord = plural(dialogs.length, ['диалог', 'диалога', 'диалогов']);
        $(root).find('[data-admin-stats]').text(dialogs.length
            ? dialogs.length + ' ' + dWord
                + (online > 0 ? ' · ' + online + ' онлайн' : '')
                + (unread > 0 ? ' · ' + unread + ' без ответа' : ' · всё прочитано')
            : 'Диалогов пока нет');
    }

    function setReplyEnabled(root, enabled) {
        const $input = $(root).find('[data-admin-input]');
        const $btn = $(root).find('[data-admin-send]');
        $input.prop('disabled', !enabled);
        $btn.prop('disabled', !enabled);
        $input.attr('placeholder', enabled ? 'Ответ пользователю...' : 'Сначала выберите диалог...');
    }

    function paintHead(root, closed) {
        const $box = $(root);
        const uniID = root.dataset.selected || '';
        $box.find('[data-admin-current]').text(uniID ? shortId(uniID) : 'Диалог не выбран').attr('title', uniID);
        $box.find('[data-admin-closed]').toggleClass('hidden', !closed);
        $box.find('[data-admin-profile-btn]').prop('disabled', !uniID);
        $box.find('[data-admin-close]').prop('disabled', !uniID || closed).text(closed ? 'Завершён' : 'Завершить');
        $box.find('[data-admin-clear]').prop('disabled', !uniID);
    }

    function loadDialogs(root) {
        const $list = $(root).find('[data-admin-dialogs]');
        if (!$list.length || root._busyD) return;
        root._busyD = true;
        $.getJSON('/api/chat/dialogs')
            .done(function (data) {
                if (data.status !== 'ok' || !Array.isArray(data.dialogs)) return;
                const dialogs = data.dialogs;
                const key = JSON.stringify(dialogs.map(function (d) {
                    return [d.uniID, d.count, d.unread, d.last_at, d.closed ? 1 : 0, d.online ? 1 : 0];
                }));
                setStats(root, dialogs);
                var totalUnread = dialogs.reduce(function (s, d) { return s + (d.closed ? 0 : (d.unread | 0)); }, 0);
                $('[data-chat-menu-badge]').each(function () {//красный счётчик в меню
                    $(this).text(totalUnread);
                    $(this).toggleClass('hidden', !totalUnread).toggleClass('inline-flex', !!totalUnread);
                });
                if (!dialogs.length) {
                    root.dataset.selected = '';
                    $list.html('<div class="flex flex-col items-center justify-center gap-2 py-10 text-center">'
                        + '<i class="fa-solid fa-inbox text-4xl text-gray-200"></i>'
                        + '<div class="text-sm text-gray-400">Пока никто не писал</div></div>');
                    setReplyEnabled(root, false);
                    paintHead(root, false);
                    return;
                }
                // первый заход: открываем самый горящий диалог
                if (!root.dataset.selected) {
                    const hot = dialogs.find(function (d) { return d.unread > 0 && !d.closed; }) || dialogs[0];
                    root.dataset.selected = hot.uniID;
                    root.dataset.threadKey = '';
                }
                if (!dialogs.some(function (d) { return d.uniID === root.dataset.selected; })) {
                    root.dataset.selected = dialogs[0].uniID;
                    root.dataset.threadKey = '';
                }
                if (root.dataset.dialogsKey === key) return;//ничего не изменилось
                root.dataset.dialogsKey = key;
                const scroll = $list.scrollTop();//запоминаем прокрутку
                $list.html(dialogs.map(function (d) {
                    const active = root.dataset.selected === d.uniID;
                    const onlineDot = d.online
                        ? '<span class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full bg-green-500 ring-2 ring-white"></span>'
                        : '';
                    const badge = d.unread > 0
                        ? '<span class="text-[11px] font-bold text-white bg-green-500 rounded-full min-w-[20px] h-5 px-1.5 flex items-center justify-center shrink-0">' + d.unread + '</span>'
                        : '';
                    const closedBadge = d.closed
                        ? '<span class="text-[11px] font-semibold text-gray-500 bg-gray-100 rounded-full px-2 py-0.5 shrink-0">закрыт</span>'
                        : '';
                    const sub = d.online
                        ? '<span class="text-xs font-medium text-green-600 truncate flex-1">online</span>'
                        : '<span class="text-xs text-gray-500 truncate flex-1">' + esc(d.last_message || '—') + '</span>';
                    return '<button type="button" data-dialog="' + esc(d.uniID) + '" title="' + esc(d.uniID) + '"'
                        + ' class="w-full text-left p-2.5 rounded-xl transition-colors flex items-center gap-3 cursor-pointer'
                        + (active ? ' bg-green-50/70' : ' hover:bg-gray-50') + '">'
                        + '<span class="relative shrink-0">'
                        + '<span class="w-11 h-11 rounded-full bg-gray-100 text-gray-500 font-bold flex items-center justify-center uppercase">'
                        + esc(String(d.uniID || '?').charAt(0)) + '</span>'
                        + onlineDot + '</span>'
                        + '<span class="flex-1 min-w-0">'
                        + '<span class="flex items-baseline gap-2">'
                        + '<span class="font-semibold text-[15px] text-gray-900 truncate">' + esc(shortId(d.uniID)) + '</span>'
                        + '<span class="ml-auto text-xs text-gray-400 shrink-0">' + esc(fmtTime(d.last_at)) + '</span>'
                        + '</span>'
                        + '<span class="flex items-center gap-2 mt-0.5">' + sub + closedBadge + badge + '</span>'
                        + '</span></button>';
                }).join(''));
                $list.scrollTop(scroll);//возвращаем прокрутку
                setReplyEnabled(root, !!root.dataset.selected);
                const current = dialogs.find(function (d) { return d.uniID === root.dataset.selected; });
                paintHead(root, !!(current && current.closed));
            })
            .fail(function () { /* тихо: повтор через интервал */ })
            .always(function () { root._busyD = false; });
    }

    function threadKey(messages) {
        return JSON.stringify(messages.map(function (m) { return m.id; }));
    }

    function avatars(root) {
        return { user: root.dataset.avatarUser || '', admin: root.dataset.avatarAdmin || '' };
    }

    function photoUrl(file) {
        return '/api/chat/photo?f=' + encodeURIComponent(file);
    }

    function paintThread($box, messages, av) {
        $box.html(messages.length ? messages.map(function (msg) {
            if (msg.sender_type === 'system') {//служебная пометка, не сообщение
                return '<div class="text-center text-xs text-gray-400 my-3">— Диалог завершён · '
                    + esc(fmtTime(msg.created_at)) + ' —</div>';
            }
            const isUser = msg.sender_type === 'user';
            const body = (msg.type === 'image' && msg.file)
                ? '<a href="' + photoUrl(msg.file) + '" target="_blank" rel="noopener">'
                    + '<img loading="lazy" src="' + photoUrl(msg.file) + '" alt="фото" class="rounded-xl max-w-[220px] max-h-[220px] object-cover"></a>'
                : '<p class="' + (isUser ? 'text-gray-800' : 'text-white') + ' text-sm leading-relaxed break-words">' + esc(msg.message) + '</p>';
            const sub = isUser ? 'text-gray-400' : 'text-green-100';
            const who = isUser ? 'Пользователь' : 'Вы';
            const ava = '<img src="' + esc(isUser ? av.user : av.admin) + '" alt="" class="w-8 h-8 rounded-full object-cover shrink-0">';
            const bubble = '<div class="p-3 rounded-xl ' + (isUser ? 'rounded-bl bg-white border border-gray-200 shadow-sm' : 'rounded-br bg-green-600 shadow-sm') + ' max-w-[75%]">'
                + '<div class="text-[11px] font-semibold mb-1 ' + sub + '">' + who + ' · ' + esc(fmtTime(msg.created_at)) + '</div>'
                + body + '</div>';
            return isUser
                ? '<div class="flex items-end gap-1.5 mb-2.5">' + ava + bubble + '</div>'
                : '<div class="flex items-end gap-1.5 mb-2.5 justify-end">' + bubble + ava + '</div>';
        }).join('') : '<div class="flex flex-col items-center justify-center h-full gap-2 text-center py-10">'
            + '<i class="fa-regular fa-comment text-3xl text-gray-200"></i>'
            + '<div class="text-sm text-gray-400">Сообщений нет</div></div>');
        $box.scrollTop($box.prop('scrollHeight'));
    }

    function loadThread(root) {
        const uniID = root.dataset.selected || '';
        const $box = $(root).find('[data-admin-thread]');
        if (!uniID || !$box.length || root._busyT) return;
        root._busyT = true;
        if (root._threadXHR) root._threadXHR.abort();//старый опрос не ждем
        root._threadXHR = $.getJSON('/api/chat/dialog?uniID=' + encodeURIComponent(uniID))
            .done(function (data) {
                if (data.status !== 'ok' || !Array.isArray(data.messages)) return;
                const key = threadKey(data.messages);
                if (root.dataset.threadKey === key) return;
                root.dataset.threadKey = key;
                const closed = data.messages.length > 0
                    && data.messages[data.messages.length - 1].sender_type === 'system';
                paintHead(root, closed);
                paintThread($box, data.messages, avatars(root));
            })
            .fail(function () { /* тихо: повтор через интервал */ })
            .always(function () { root._busyT = false; });
    }

    function openProfile(root) {
        const uniID = root.dataset.selected || '';
        const $modal = $(root).find('[data-admin-modal]');
        const $body = $(root).find('[data-admin-profile]');
        if (!uniID || !$modal.length || !$body.length) return;
        $body.html('<div class="text-sm text-gray-400">Загрузка...</div>');
        $modal.removeClass('hidden');
        $.getJSON('/api/chat/userinfo?uniID=' + encodeURIComponent(uniID))
            .done(function (data) {
                if (data.status !== 'ok' || !data.user) {
                    $body.html('<div class="text-sm text-red-500">Пользователь не найден</div>');
                    return;
                }
                const u = data.user;
                const active = (u.status === 'on');
                $(root).find('[data-admin-profile-avatar]').attr('src', root.dataset.avatarUser || '');
                $(root).find('[data-admin-profile-name]').text(u.name || u.uniID);
                $(root).find('[data-admin-profile-email]').text(u.email || '—');
                $(root).find('[data-admin-profile-status]')
                    .text(active ? 'Подписка активна' : 'Нет подписки')
                    .attr('class', 'inline-block text-[11px] font-semibold rounded-full px-2.5 py-1 '
                        + (active ? 'text-green-700 bg-green-100' : 'text-gray-500 bg-gray-100'));
                function card(label, value) {
                    const v = String(value ?? '') || '—';
                    return '<div class="bg-gray-50 rounded-xl px-3 py-2.5 min-w-0">'
                        + '<div class="text-[11px] text-gray-400 mb-0.5">' + label + '</div>'
                        + '<div class="font-semibold text-gray-800 truncate" title="' + esc(v) + '">' + esc(v) + '</div></div>';
                }
                const isLink = /^https?:\/\//i.test(u.subscription || '');
                $body.html((isLink
                    ? '<a href="' + esc(u.subscription) + '" target="_blank" rel="noopener"'
                        + ' class="mb-3 flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-500 text-white text-sm font-semibold transition-colors">'
                        + '<i class="fa-solid fa-arrow-up-right-from-bracket text-xs"></i>Открыть подписку</a>'
                    : '')
                    + '<div class="grid grid-cols-2 gap-2">'
                    + card('Тариф', u.plan || (!isLink ? u.subscription : ''))
                    + card('Осталось дней', u.days)
                    + card('Устройств', u.devices)
                    + card('Пригласил', u.refer)
                    + card('Его код', u.myrefer)
                    + card('ID', u.uniID)
                    + '</div>');
            })
            .fail(function () {
                $body.html('<div class="text-sm text-red-500">Ошибка загрузки</div>');
            });
    }

    function tick() {
        $('[data-admin-chat]').each(function () {
            const root = this;
            if (!root.dataset.init) {
                root.dataset.init = '1';
                initRoot(root);
            }
            if (!isVisible(root)) return;
            loadDialogs(root);
            if (root.dataset.selected) loadThread(root);
        });
    }

    // Общее сжатие/ошибки — в photo.js; нет файла — шлём как есть
    const CP = window.ChatPhoto || {
        normalize: function (f) { return Promise.resolve(f); },
        showError: function () {}
    };

    function uploadPhoto(root, file) {//фото: превью сразу, сервер догонит
        const uniID = root.dataset.selected || '';
        if (!uniID || !file || String(file.type || '').indexOf('image/') !== 0) return;
        const $box = $(root).find('[data-admin-thread]');
        const url = URL.createObjectURL(file);
        if (root._threadXHR) root._threadXHR.abort();
        if ($box.length) {
            $box.append(
                '<div class="flex items-end gap-1.5 mb-2.5 justify-end" data-chat-pending>'
                + '<div class="p-2 rounded-xl rounded-br bg-green-600 shadow-sm max-w-[75%] opacity-70">'
                + '<img src="' + url + '" alt="фото" class="rounded-xl max-w-[220px] max-h-[220px] object-cover">'
                + '</div></div>');
            $box.scrollTop($box.prop('scrollHeight'));
        }
        CP.normalize(file).then(function (blob) {
            if (!blob) { URL.revokeObjectURL(url); return; }
            const fd = new FormData();
            fd.append('photo', blob, 'photo.jpg');
            fd.append('uniID', uniID);
            $.ajax({ url: '/api/chat/upload', method: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
                .done(function (data) {
                    URL.revokeObjectURL(url);
                    if (data.status === 'ok' && $box.length && Array.isArray(data.messages)) {
                        root.dataset.threadKey = threadKey(data.messages);
                        paintHead(root, false);
                        paintThread($box, data.messages, avatars(root));
                        loadDialogs(root);
                        return;
                    }
                    $box.find('[data-chat-pending]').remove();
                    CP.showError($box[0], data.message, 'text-center text-xs text-red-500 my-2');
                })
                .fail(function () {
                    URL.revokeObjectURL(url);
                    $box.find('[data-chat-pending]').remove();
                    CP.showError($box[0], 'Не удалось отправить фото', 'text-center text-xs text-red-500 my-2');
                });
        });
    }

    function initRoot(root) {
        const $input = $(root).find('[data-admin-input]');
        const $btn = $(root).find('[data-admin-send]');
        const $attach = $(root).find('[data-admin-attach]');
        const $fileInput = $(root).find('[data-admin-file]');
        if ($attach.length && $fileInput.length) {
            $attach.on('click', function () { $fileInput.click(); });
            $fileInput.on('change', function () {
                const files = $fileInput.prop('files');
                if (files && files[0]) uploadPhoto(root, files[0]);
                $fileInput.val('');
            });
        }
        const $modal = $(root).find('[data-admin-modal]');

        $(root).on('click', function (e) {
            const $t = $(e.target);
            if ($t.closest('[data-admin-refresh]').length) {
                root.dataset.threadKey = '';
                root.dataset.dialogsKey = '';
                loadDialogs(root);
                loadThread(root);
                return;
            }
            if ($t.closest('[data-admin-profile-btn]').length) {
                openProfile(root);
                return;
            }
            if ($t.closest('[data-admin-modal-close]').length || $t.closest('[data-admin-modal-bg]').length) {
                $modal.addClass('hidden');
                return;
            }
            if ($t.closest('[data-admin-close]').length) {
                const uniID = root.dataset.selected || '';
                if (!uniID || !confirm('Завершить диалог с ' + uniID + '?')) return;
                $.ajax({
                    url: '/api/chat/close',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ uniID: uniID }),
                    dataType: 'json'
                })
                    .done(function (data) {
                        if (data.status === 'ok') {
                            root.dataset.threadKey = '';
                            root.dataset.dialogsKey = '';
                            loadThread(root);
                            loadDialogs(root);
                        }
                    })
                    .fail(function () { });
                return;
            }
            if ($t.closest('[data-admin-clear]').length) {
                const uniID = root.dataset.selected || '';
                if (!uniID || !confirm('Очистить диалог с ' + uniID + '? Сообщения и фото удалятся с сервера.')) return;
                $.ajax({
                    url: '/api/chat/clear',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ uniID: uniID }),
                    dataType: 'json'
                })
                    .done(function (data) {
                        if (data.status !== 'ok') return;
                        root.dataset.selected = '';
                        root.dataset.threadKey = '';
                        root.dataset.dialogsKey = '';
                        paintHead(root, false);
                        setReplyEnabled(root, false);
                        const $box = $(root).find('[data-admin-thread]');
                        if ($box.length) $box.html('<div class="text-center text-xs text-gray-400 my-3">— Диалог очищен —</div>');
                        loadDialogs(root);
                    })
                    .fail(function () { });
                return;
            }
            const $item = $t.closest('[data-dialog]');
            if (!$item.length) return;
            root.dataset.selected = $item.attr('data-dialog');
            root.dataset.threadKey = '';
            setReplyEnabled(root, true);
            loadDialogs(root);
            loadThread(root);
        });

        function send() {
            const uniID = root.dataset.selected || '';
            const message = ($input.val() || '').trim();
            if (!uniID || !message) return;
            const $box = $(root).find('[data-admin-thread]');
            if (root._threadXHR) root._threadXHR.abort();//опрос не ждет, ответ важнее
            $input.val('');//показываем сразу, сервер догонит
            if ($box.length) {
                $box.append(
                    '<div class="flex items-end gap-1.5 mb-2.5 justify-end" data-chat-pending>'
                    + '<div class="p-3 rounded-xl rounded-br bg-green-600 shadow-sm max-w-[80%] opacity-70">'
                    + '<div class="text-[11px] font-semibold mb-1 text-green-100">Вы</div>'
                    + '<p class="text-white text-sm leading-relaxed break-words">' + esc(message) + '</p>'
                    + '</div></div>');
                $box.scrollTop($box.prop('scrollHeight'));
            }
            $btn.prop('disabled', true);
            $.ajax({
                url: '/api/chat/reply',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ uniID: uniID, message: message }),
                dataType: 'json'
            })
                .done(function (data) {
                    if (data.status === 'ok' && $box.length && Array.isArray(data.messages)) {
                        root.dataset.threadKey = threadKey(data.messages);//сверено сразу
                        paintHead(root, false);//ответ переоткрывает диалог
                        paintThread($box, data.messages, avatars(root));
                        loadDialogs(root);//обновляем превью слева
                    } else {//не ушло — убираем и возвращаем текст
                        $box.find('[data-chat-pending]').remove();
                        $input.val(message);
                    }
                })
                .fail(function () { })
                .always(function () { $btn.prop('disabled', false); $input.focus(); });
        }

        $btn.on('click', send);
        $input.on('keypress', function (e) {
            if (e.key === 'Enter') send();
        });
    }

    let tickTimer = null;
    function scheduleTick(delay) {//клеим очередь тиков в один запрос
        if (tickTimer) clearTimeout(tickTimer);
        tickTimer = setTimeout(tick, delay || 150);
    }

    $(tick);
    $(document).on('click', function (e) {
        if ($(e.target).closest('[data-toggle-section]').length) setTimeout(tick, 300);
    });
    setInterval(tick, 5000);
    setInterval(function () {
        $('[data-admin-chat]').each(function () {
            if (this.dataset.selected && isVisible(this)) loadThread(this);
        });
    }, 3000);
});
</script>
