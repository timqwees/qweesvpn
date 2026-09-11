<?php
// Компонент чата техподдержки (пользователь).
// Использование: секция support (desktop), секция setting (mobile).
// Стиль Apple: круглые пузыри, аватары, стеклянное поле ввода.
// Поиск элементов по data-атрибутам, без дублирующихся id.
$t = $t ?? fn(string $k): string => $k;
$base = $site['baseUrl'] ?? '';
?>
<div data-chat-root
    data-avatar-user="<?= htmlspecialchars($base . '/public/assets/images/icons/services/avatar/1.png') ?>"
    data-avatar-admin="<?= htmlspecialchars($base . '/public/assets/images/icons/services/avatar/2.png') ?>">
    <div class="flex items-center gap-2 mb-3">
        <span data-chat-dot class="w-2 h-2 rounded-full bg-gray-500 shrink-0"></span>
        <span data-chat-status class="text-xs text-gray-400"></span>
    </div>
    <div data-chat-messages class="h-96 overflow-y-auto rounded-[20px] bg-black/20 p-4 mb-3 border border-white/[0.08]">
        <!-- Сообщения подгружаются через AJAX -->
    </div>

    <div class="flex items-end gap-1.5 rounded-[28px] border border-white/10 bg-white/10 backdrop-blur-xl p-1.5">
        <button type="button" data-chat-attach title="<?= $t('attach_photo') ?>"
            class="w-10 h-10 shrink-0 rounded-full text-gray-300 hover:bg-white/10 transition-colors flex items-center justify-center cursor-pointer">
            <i class="fa-regular fa-image"></i>
        </button>
        <input type="file" data-chat-file accept="image/jpeg,image/png,image/gif,image/webp" class="hidden">
        <textarea rows="1" data-chat-input
            class="flex-1 bg-transparent px-2 py-2.5 text-white text-[15px] placeholder:text-gray-500 focus:outline-none resize-none overflow-y-auto max-h-32"
            placeholder="<?= $t('type_message') ?>..." maxlength="2000" autocomplete="off"></textarea>
        <button type="button" data-chat-send title="<?= $t('send') ?>"
            class="w-10 h-10 shrink-0 rounded-full bg-green-500 hover:bg-green-400 text-black transition-colors flex items-center justify-center cursor-pointer">
            <i class="fa-solid fa-arrow-up text-sm"></i>
        </button>
    </div>
</div>

<script>
(function () {
    if (window.__chatUserInit) return;
    window.__chatUserInit = true;
    const T_CLOSED = <?= json_encode($t('dialog_closed')) ?>;
    const T_EMPTY = <?= json_encode($t('chat_empty')) ?>;
    const T_ONLINE = <?= json_encode($t('support_online')) ?>;
    const T_OFFLINE = <?= json_encode($t('support_offline')) ?>;

    function setStatus(root, online) {
        const dot = root.querySelector('[data-chat-dot]');
        const label = root.querySelector('[data-chat-status]');
        if (!dot || !label) return;
        dot.className = 'w-2 h-2 rounded-full shrink-0 ' + (online ? 'bg-green-500' : 'bg-gray-500');
        label.textContent = online ? T_ONLINE : T_OFFLINE;
    }

    function fmtTime(raw) {
        if (!raw) return '';
        const d = new Date(String(raw).replace(' ', 'T'));
        if (isNaN(d)) return String(raw);
        const time = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        if (d.toDateString() === new Date().toDateString()) return time;
        return String(d.getDate()).padStart(2, '0') + '.' + String(d.getMonth() + 1).padStart(2, '0') + ', ' + time;
    }

    function esc(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function isVisible(el) {
        return !document.hidden && !!(el.offsetWidth || el.offsetHeight);
    }

    function render(box, messages, av) {
        if (!messages.length) {//пусто — дружелюбная заглушка вместо пустоты
            box.innerHTML = '<div class="flex flex-col items-center justify-center h-full min-h-[200px] gap-2 text-center py-8">'
                + '<i class="fa-regular fa-comments text-4xl text-white/20"></i>'
                + '<div class="text-sm text-gray-400">' + esc(T_EMPTY) + '</div></div>';
            return;
        }
        // умный скролл: вниз — только если читали конец или первая загрузка
        const stick = !box.dataset.touched
            || (box.scrollHeight - box.scrollTop - box.clientHeight < 80);
        box.dataset.touched = '1';
        box.innerHTML = messages.map(function (msg) {
            if (msg.sender_type === 'system') {//служебная пометка, не сообщение
                const text = msg.message === 'closed' ? T_CLOSED : msg.message;
                return '<div class="text-center text-xs text-gray-500 my-3">— ' + esc(text) + ' —</div>';
            }
            const isAdmin = msg.sender_type === 'admin';
            const body = (msg.type === 'image' && msg.file)
                ? '<a href="' + photoUrl(msg.file) + '" target="_blank" rel="noopener">'
                    + '<img loading="lazy" src="' + photoUrl(msg.file) + '" alt="фото" class="rounded-xl max-w-[220px] max-h-[220px] object-cover"></a>'
                : '<p class="' + (isAdmin ? 'text-white' : 'text-emerald-300') + ' text-[15px] break-words">' + esc(msg.message) + '</p>';
            const time = '<div class="text-[11px] mt-1 opacity-60 ' + (isAdmin ? 'text-white' : 'text-emerald-300') + '">'
                + esc(fmtTime(msg.created_at)) + '</div>';
            const ava = '<img src="' + esc(isAdmin ? av.admin : av.user) + '" alt="" class="w-7 h-7 rounded-full object-cover shrink-0">';
            return isAdmin
                ? '<div class="flex items-end gap-1.5 mb-3">' + ava
                    + '<div class="p-3 rounded-xl rounded-bl bg-white/20 max-w-[75%]">' + body + time + '</div></div>'
                : '<div class="flex items-end gap-1.5 mb-3 justify-end">'
                    + '<div class="p-3 rounded-xl rounded-br bg-green-500/25 max-w-[75%]">' + body + time + '</div>' + ava + '</div>';
        }).join('');
        if (stick) box.scrollTop = box.scrollHeight;
    }

    function avatars(root) {
        return { user: root.dataset.avatarUser || '', admin: root.dataset.avatarAdmin || '' };
    }

    function photoUrl(file) {
        return '/api/chat/photo?f=' + encodeURIComponent(file);
    }

    function userBubble(root, message) {//пузырь своего сообщения, как в render
        const av = avatars(root);
        return '<div class="flex items-end gap-1.5 mb-3 justify-end" data-chat-pending>'
            + '<div class="p-3 rounded-xl rounded-br bg-green-500/25 max-w-[75%] opacity-70">'
            + '<p class="text-emerald-300 text-[15px] break-words">' + esc(message) + '</p>'
            + '</div>'
            + '<img src="' + esc(av.user) + '" alt="" class="w-7 h-7 rounded-full object-cover shrink-0">'
            + '</div>';
    }

    function photoBubble(url) {//превью фото перед отправкой
        return '<div class="flex items-end gap-1.5 mb-3 justify-end" data-chat-pending>'
            + '<div class="p-2 rounded-xl rounded-br bg-green-500/25 max-w-[75%] opacity-70">'
            + '<img src="' + url + '" alt="фото" class="rounded-xl max-w-[220px] max-h-[220px] object-cover">'
            + '</div></div>';
    }

    function keyOf(messages) {
        return JSON.stringify(messages.map(function (m) { return m.id; }));
    }

    function loadRoot(root) {
        if (!isVisible(root)) return;
        const box = root.querySelector('[data-chat-messages]');
        if (!box) return;
        if (root._pollCtrl) root._pollCtrl.abort();//старый опрос не ждем
        root._pollCtrl = new AbortController();
        fetch('/api/chat/messages', { signal: root._pollCtrl.signal })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status !== 'ok' || !Array.isArray(data.messages)) return;
                if (typeof data.support_online !== 'undefined') setStatus(root, !!data.support_online);
                const key = keyOf(data.messages);
                if (root.dataset.chatKey === key) return;
                root.dataset.chatKey = key;
                render(box, data.messages, avatars(root));
            })
            .catch(function () { /* тихо: повтор через интервал */ });
    }

    function tick() {
        document.querySelectorAll('[data-chat-root]').forEach(function (root) {
            if (!root.dataset.chatInit) initRoot(root);
            loadRoot(root);
        });
    }

    function initRoot(root) {
        root.dataset.chatInit = '1';
        const input = root.querySelector('[data-chat-input]');
        const btn = root.querySelector('[data-chat-send]');
        if (!input || !btn) return;

        function autogrow() {//поле тянется за текстом, до 4 строк
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 128) + 'px';
        }

        function upload(file) {//фото: превью сразу, сервер догонит
            if (!file || String(file.type || '').indexOf('image/') !== 0) return;
            if (file.size > 5 * 1024 * 1024) return;//лимит дублирует серверный
            const box = root.querySelector('[data-chat-messages]');
            const url = URL.createObjectURL(file);
            if (root._pollCtrl) root._pollCtrl.abort();
            if (box) {
                box.insertAdjacentHTML('beforeend', photoBubble(url));
                box.scrollTop = box.scrollHeight;
            }
            const fd = new FormData();
            fd.append('photo', file);
            fetch('/api/chat/upload', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    URL.revokeObjectURL(url);
                    if (data.status === 'ok' && box && Array.isArray(data.messages)) {
                        root.dataset.chatKey = keyOf(data.messages);
                        render(box, data.messages, avatars(root));
                        return;
                    }
                    if (box) {
                        const pending = box.querySelector('[data-chat-pending]');
                        if (pending) pending.remove();
                    }
                })
                .catch(function () { URL.revokeObjectURL(url); });
        }

        function send() {
            const message = input.value.trim();
            if (!message) return;
            const box = root.querySelector('[data-chat-messages]');
            if (root._pollCtrl) root._pollCtrl.abort();//опрос не ждет, отправка важнее
            input.value = '';//показываем сразу, сервер догонит
            autogrow();
            if (box) {
                box.insertAdjacentHTML('beforeend', userBubble(root, message));
                box.scrollTop = box.scrollHeight;
            }
            btn.disabled = true;
            fetch('/api/chat/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.status === 'ok' && box && Array.isArray(data.messages)) {
                        root.dataset.chatKey = keyOf(data.messages);//сверено сразу, тик не нужен
                        render(box, data.messages, avatars(root));
                        return;
                    }
                    if (box) {//не ушло — убираем и возвращаем текст
                        const pending = box.querySelector('[data-chat-pending]');
                        if (pending) pending.remove();
                        input.value = message;
                        autogrow();
                    }
                })
                .catch(function () { })
                .finally(function () { btn.disabled = false; input.focus(); });
        }

        const attach = root.querySelector('[data-chat-attach]');
        const fileInput = root.querySelector('[data-chat-file]');
        if (attach && fileInput) {
            attach.addEventListener('click', function () { fileInput.click(); });
            fileInput.addEventListener('change', function () {
                if (fileInput.files && fileInput.files[0]) upload(fileInput.files[0]);
                fileInput.value = '';
            });
        }

        btn.addEventListener('click', send);
        input.addEventListener('input', autogrow);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {//Enter — отправить, Shift+Enter — новая строка
                e.preventDefault();
                send();
            }
        });
    }

    let tickTimer = null;
    function scheduleTick(delay) {//клеим очередь тиков в один запрос
        if (tickTimer) clearTimeout(tickTimer);
        tickTimer = setTimeout(tick, delay || 150);
    }

    document.addEventListener('DOMContentLoaded', tick);
    // секции рендерятся лениво (main.js) — проверяем заново после переключения
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-toggle-section]')) scheduleTick(450);
    });
    if ('MutationObserver' in window) {
        new MutationObserver(function () { scheduleTick(); })
            .observe(document.documentElement, { childList: true, subtree: true });
    }
    setInterval(tick, 3000);
})();
</script>
