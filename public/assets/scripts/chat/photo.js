// Общее для чата юзера и админа: сжатие фото в JPEG + показ ошибки.
// Чинит HEIC с айфонов и фото больше лимита. Подключается в chat_user.php и chat_admin.php.
window.ChatPhoto = window.ChatPhoto || (function () {
    // Сжимаем в JPEG (макс. 1600px, 82%). Не вышло — вернём оригинал как есть.
    function normalize(file) {
        return new Promise(function (resolve) {
            if (!file) return resolve(null);
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = function () {
                try {
                    const w = img.naturalWidth || 0, h = img.naturalHeight || 0;
                    if (!w || !h) { URL.revokeObjectURL(url); return resolve(file); }
                    const k = Math.min(1, 1600 / Math.max(w, h));
                    const c = document.createElement('canvas');
                    c.width = Math.max(1, Math.round(w * k));
                    c.height = Math.max(1, Math.round(h * k));
                    c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
                    URL.revokeObjectURL(url);
                    if (c.toBlob) c.toBlob(function (b) { resolve(b || file); }, 'image/jpeg', 0.82);
                    else resolve(file);
                } catch (e) { URL.revokeObjectURL(url); resolve(file); }
            };
            img.onerror = function () { URL.revokeObjectURL(url); resolve(file); };
            img.src = url;
        });
    }

    // Красная строка ошибки в ленту (box — DOM-элемент). Сама исчезнет через 4 сек.
    function showError(box, text, cls) {
        if (!box) return;
        const div = document.createElement('div');
        div.setAttribute('data-chat-error', '');
        div.className = cls || 'text-center text-xs text-red-400 my-2';
        div.textContent = text || 'Не удалось отправить фото';
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
        setTimeout(function () { div.remove(); }, 4000);
    }

    return { normalize: normalize, showError: showError };
})();
