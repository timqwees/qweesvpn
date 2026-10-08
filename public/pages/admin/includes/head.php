<?php
/**
 * Шаблонный head для админ-панели
 * Использование: include 'includes/head.php';
 */
?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Админ панель</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" defer />
    <?php
    if (!isset($site)) {
        $site = \Setting\Route\Function\Functions::site();
    }
    ?>
    <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet" defer />
    <link rel="stylesheet" href="/public/assets/styles/style.css<?= '?v=' . $site['versionApp'] ?>" defer>
    <link rel="stylesheet" href="/public/assets/styles/admin.css<?= '?v=' . $site['versionApp'] ?>" defer>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        #qwees-spin { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background: rgba(10, 12, 16, .55); backdrop-filter: blur(2px); }
        #qwees-spin .box { display: flex; flex-direction: column; align-items: center; gap: 12px; }
        #qwees-spin .ring { width: 44px; height: 44px; border-radius: 9999px; border: 3px solid rgba(255,255,255,.15); border-top-color: #2f81f7; animation: qwees-spin 0.8s linear infinite; }
        #qwees-spin .txt { color: #e5e7eb; font-size: 13px; }
        @keyframes qwees-spin { to { transform: rotate(360deg); } }
    </style>
    <script>
        // Центральный спиннер: QSpin.show('Загрузка...') / QSpin.hide() (счётчик вложенных вызовов)
        window.QSpin = window.QSpin || { n: 0,
            show: function (text) {
                this.n++;
                var el = document.getElementById('qwees-spin');
                if (!el) {
                    el = document.createElement('div');
                    el.id = 'qwees-spin';
                    el.innerHTML = '<div class="box"><div class="ring"></div><div class="txt"></div></div>';
                    document.body.appendChild(el);
                }
                el.querySelector('.txt').textContent = text || 'Загрузка...';
                el.style.display = 'flex';
            },
            hide: function () {
                this.n = Math.max(0, this.n - 1);
                if (this.n === 0) {
                    var el = document.getElementById('qwees-spin');
                    if (el) el.style.display = 'none';
                }
            }
        };
    </script>
    <script>
        // Способ оплаты: иконка + название (иконки лежат в /public/assets/images/icons/payment/)
        window.payMethod = window.payMethod || function (type) {
            var t = String(type || '').toLowerCase();
            var map = {
                sbp: ['sbp.svg', 'СБП'], sberbank: ['sberbank.svg', 'SberPay'], sber: ['sberbank.svg', 'SberPay'],
                sber_loan: ['sberbank.svg', 'SberPay'], yoo_money: ['iomoney.svg', 'ЮMoney'], iomoney: ['iomoney.svg', 'ЮMoney'],
                tbank: ['tbank.svg', 'T-Bank'], tinkoff: ['tbank.svg', 'T-Bank'], card: [null, 'Карта'], bank_card: [null, 'Карта']
            };
            var m = map[t] || null;
            if (!m) {
                for (var k in map) { if (t.indexOf(k) === 0 || k.indexOf(t) === 0 && t) { m = map[k]; break; } }
            }
            return { icon: m ? m[0] : null, name: m ? m[1] : (type || '—') };
        };
        window.payMethodHtml = window.payMethodHtml || function (type) {
            var m = window.payMethod(type);
            var img = m.icon
                ? '<img src="/public/assets/images/icons/payment/' + m.icon + '" alt="" class="w-5 h-5 object-contain shrink-0">'
                : '<i class="fa-solid fa-credit-card text-gray-500 text-sm w-5 text-center shrink-0"></i>';
            return img + '<span class="text-sm text-gray-200">' + $('<div>').text(m.name).html() + '</span>';
        };
        // Статус оплаты: шеврон в круге (оплачен — зелёный, иначе серый)
        window.payStatusIcon = window.payStatusIcon || function (paid) {
            var c = paid ? '#22c55e' : '#6b7280';
            return '<svg width="30" height="30" viewBox="0 0 30 30"><circle cx="50%" cy="50%" r="50%" fill="' + c + '" fill-opacity="0.15"></circle><g style="transform: translate(7px, 7px);"><path fill="' + c + '" fill-rule="evenodd" clip-rule="evenodd" d="M10.3904 8.0001L5.52833 3.1382L6.47113 2.19538L11.8046 7.52868C11.9296 7.65371 11.9998 7.82327 11.9998 8.00009C11.9998 8.1769 11.9296 8.34647 11.8046 8.47149L6.47134 13.8048L5.52852 12.862L10.3904 8.0001Z"/></g></svg>';
        };
        // Дата 'YYYY-MM-DD' -> '3 октября' (тултипы графиков)
        window.fmtDayRu = window.fmtDayRu || function (iso) {
            var M = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
            var p = String(iso || '').slice(0, 10).split('-');
            if (p.length < 3) return String(iso || '');
            return (parseInt(p[2], 10) || '') + ' ' + (M[parseInt(p[1], 10) - 1] || '');
        };
        // Код тарифа '6months_4' -> '6 месяцев · 4 устройства'
        window.tariffName = window.tariffName || function (code) {
            function pl(n, one, few, many) {
                n = Math.abs(parseInt(n, 10) || 0) % 100;
                var d = n % 10;
                if (n > 10 && n < 20) return many;
                if (d > 1 && d < 5) return few;
                if (d === 1) return one;
                return many;
            }
            var m = /^(\d+)\s*months?_(\d+)$/.exec(String(code || ''));
            if (!m) return String(code || '—');
            return parseInt(m[1], 10) + ' ' + pl(m[1], 'месяц', 'месяца', 'месяцев')
                + ' · ' + parseInt(m[2], 10) + ' ' + pl(m[2], 'устройство', 'устройства', 'устройств');
        };
    </script>
</head>