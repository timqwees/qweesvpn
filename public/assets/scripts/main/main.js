$(function () {

  // ─────────────────────────────────────────────
  // 1. MODAL
  // ─────────────────────────────────────────────
  function closeAllModals(callback) {
    const $modals = $('[data-modal]:not(.hidden)');
    if (!$modals.length) {
      if (callback) callback();
      return;
    }

    $modals.css({ opacity: 0, transition: 'opacity 0.25s ease' });

    setTimeout(function () {
      $modals.addClass('hidden');
      if (callback) callback();
    }, 250);
  }

  function openModal(name) {
    const $modal = $('[data-modal="' + name + '"]');
    if (!$modal.length) return;

    closeAllModals(function () {
      $modal
        .removeClass('hidden')
        .css({ opacity: 0, transition: 'opacity 0.25s ease' });

      // Force reflow
      $modal[0].offsetHeight;

      $modal.css('opacity', 1);
    });
  }

  // Open
  $(document).on('click', '[data-toggle-modal]', function () {
    openModal($(this).attr('data-toggle-modal'));
  });

  // Close by clicking the overlay itself
  $(document).on('click', '[data-modal]', function (e) {
    if (e.target === this) {
      closeAllModals();
    }
  });

  // Close buttons
  $(document).on('click', '.modal-close, .modal-btn-close', function () {
    closeAllModals();
  });

  // ─────────────────────────────────────────────
  // 2. SECTION TOGGLE
  // ─────────────────────────────────────────────
  // Смена сервера (переезд/фолбэк): URL подписки изменился —
  // показываем баннер «обновите подписку в приложении».
  // Храним последний виденный URL в localStorage, сравниваем при каждом рендере.
  function watchSubUrl() {
    const host = document.querySelector('[data-sub-url]');
    if (!host) return;
    const cur = host.getAttribute('data-sub-url') || '';
    if (!cur) return;
    let saved = null;
    try { saved = localStorage.getItem('qwees_sub_url'); } catch (e) {}
    if (!saved) {
      try { localStorage.setItem('qwees_sub_url', cur); } catch (e) {}
      return;
    }
    if (saved === cur) return;
    const bar = host.querySelector('[data-subwatch]');
    if (!bar) return;
    bar.hidden = false;
    const remember = function () {
      try { localStorage.setItem('qwees_sub_url', cur); } catch (e) {}
      bar.hidden = true;
    };
    const copyBtn = bar.querySelector('[data-subwatch-copy]');
    if (copyBtn) copyBtn.addEventListener('click', function () {
      const done = function () { copyBtn.textContent = 'Готово'; setTimeout(remember, 900); };
      if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(cur).then(done, done);
      else {
        const t = document.createElement('textarea');
        t.value = cur; document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); } catch (e) {}
        t.remove(); done();
      }
    });
    const okBtn = bar.querySelector('[data-subwatch-ok]');
    if (okBtn) okBtn.addEventListener('click', remember);
  }
  // Reveal on scroll: [data-reveal] всплывают при входе в кадр.
  // Один observer на страницу; секции рендерятся лениво —
  // armReveals() вызывается после каждой отрисовки секции.
  const revealIO = ('IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (en.isIntersecting) {
        en.target.classList.add('revealed');
        revealIO.unobserve(en.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -32px 0px' }) : null;
  function armReveals() {
    if (!revealIO) {
      document.querySelectorAll('[data-reveal]').forEach(function (el) { el.classList.add('revealed'); });
      return;
    }
    document.querySelectorAll('[data-reveal]:not(.revealed)').forEach(function (el) {
      const dl = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
      if (dl > 0) el.style.transitionDelay = dl + 'ms';
      revealIO.observe(el);
    });
  }
  // Reveal только после закрытия сплэша QweesTeam Studio —
  // иначе анимация проигрывается под чёрным экраном и её не видно.
  function loaderVisible() {
    const loader = document.getElementById('loader');
    if (!loader || loader.dataset.done === '1' || loader.style.display === 'none') return false;
    return !(window.getComputedStyle && getComputedStyle(loader).display === 'none');
  }
  // Кольцо подписки: пока сплэш виден — стоим на нуле,
  // после закрытия докручиваемся до значения (CSS-анимация).
  function pauseRingsWhileLoading() {
    if (!loaderVisible()) return false;
    document.querySelectorAll('.bank-ring').forEach(function (el) { el.style.animationPlayState = 'paused'; });
    return true;
  }
  window.addEventListener('qwees:loader-done', function ringGo() {
    document.querySelectorAll('.bank-ring').forEach(function (el) { el.style.animationPlayState = 'running'; });
  });
  function armRevealsWhenReady() {
    pauseRingsWhileLoading();
    if (!loaderVisible()) { armReveals(); return; }
    const onDone = function () {
      window.removeEventListener('qwees:loader-done', onDone);
      armReveals();
    };
    window.addEventListener('qwees:loader-done', onDone);
    setTimeout(armReveals, 6000); // страховка, если событие потерялось
  }
  // История оплат в профиле: id+дата+сумма из JSON-индекса,
  // полная квитанция — живым запросом в кассу (ссылка, без нагрузки на сайт).
  function escHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function loadPayHistory() {
    const $box = $('[data-pay-history]');
    if (!$box.length || $box.data('loaded')) return;
    $box.data('loaded', true);
    $.getJSON('/api/payments/mine')
      .done(function (d) {
        if (!d || d.status !== 'ok') { $box.html('<span class="text-sm text-red-400">' + escHtml($box.data('error')) + '</span>'); return; }
        const items = d.items || [];
        if (!items.length) { $box.html('<span class="text-sm text-gray-500">' + escHtml($box.data('empty')) + '</span>'); return; }
        $box.html(items.map(function (p) {
          const amt = Number(p.amount || 0).toLocaleString('ru-RU', { maximumFractionDigits: 2 }) + ' ₽';
          return '<div class="flex items-center gap-3 p-3 rounded-lg bg-white/[0.04] hover:bg-white/[0.07] transition-colors">'
            + '<span class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-receipt text-green-400 text-sm"></i></span>'
            + '<span class="flex-1 min-w-0"><span class="block font-semibold text-[white] tabular-nums">' + escHtml(amt) + '</span>'
            + '<span class="block text-xs text-gray-500 tabular-nums">' + escHtml(p.date || '') + '</span></span>'
            + '<a href="/api/payment/receipt?id=' + encodeURIComponent(p.id) + '" target="_blank" rel="noopener" class="shrink-0 text-xs font-medium px-3 py-2 rounded-lg bg-white/5 ring-1 ring-white/10 text-gray-300 hover:bg-white/10 transition-colors">' + escHtml($box.data('receipt')) + ' <i class="fa-solid fa-download text-[10px]"></i></a>'
            + '</div>';
        }).join(''));
      })
      .fail(function () {
        $box.data('loaded', false);
        $box.html('<span class="text-sm text-red-400">' + escHtml($box.data('error')) + '</span>');
      });
  }
  const $root = $('#layout-root');

  if ($root.length && document.getElementById('layout-desktop') && document.getElementById('layout-mobile')) {

    // ===== Главная страница: ленивый рендер секций =====
    // В DOM находится только активная секция активного layout (desktop/mobile).
    // Остальные лежат в <template> и не рендерятся: нет фоновых анимаций,
    // изображения не загружаются, нагрузка идёт только на видимый экран.
    const SECTIONS = ['main', 'profile', 'setting', 'referal', 'support'];

    let activeSection = document.body.dataset.activeSection || 'main';
    if (SECTIONS.indexOf(activeSection) === -1) activeSection = 'main';

    let currentLayout = null;
    let busy = false;

    const mq = window.matchMedia('(min-width: 640px)');

    function layoutId() {
      return mq.matches ? 'layout-desktop' : 'layout-mobile';
    }

    function updateMenuActive(sectionId) {
      $('[data-toggle-section]').removeClass('bg_active');
      $('[data-toggle-section="' + sectionId + '"]').addClass('bg_active');
    }

    // Собирает layout целиком: меню + оболочка + только активная секция
    function render(layoutId, sectionId) {
      const layoutTpl = document.getElementById(layoutId);
      const frag = layoutTpl.content.cloneNode(true);

      // Убираем пустые шаблоны секций из клона
      frag.querySelectorAll('template[data-section]').forEach(function (t) {
        t.remove();
      });

      // Вставляем активную секцию (нет такой в layout — откат на main)
      const sectionTpl = layoutTpl.content.querySelector('template[data-section="' + sectionId + '"]')
        || layoutTpl.content.querySelector('template[data-section="main"]');
      frag.querySelector('.js-sections').appendChild(sectionTpl.content.cloneNode(true));

      return frag;
    }

    // Переключение: смена секции (меню остаётся в DOM) или смена layout desktop/mobile
    function show(layoutId, sectionId) {
      if (busy || (layoutId === currentLayout && sectionId === activeSection)) return;
      busy = true;

      const $current = $('[data-section]');

      const done = function () {
        if (layoutId !== currentLayout) {
          // Смена desktop ↔ mobile: пересобираем весь layout
          currentLayout = layoutId;
          $root.empty().append(render(layoutId, sectionId));
        } else {
          // Смена секции: меняем только секцию, меню не трогаем
          // (нет такой в layout — откат на main)
          const sectionTpl = document.getElementById(currentLayout)
            .content.querySelector('template[data-section="' + sectionId + '"]')
            || document.getElementById(currentLayout)
            .content.querySelector('template[data-section="main"]');
          $root.find('.js-sections').empty().append(sectionTpl.content.cloneNode(true));
        }

        activeSection = sectionId;
        updateMenuActive(sectionId);

        // Плавное появление новой секции
        const $section = $('[data-section]');
        $section.css({ opacity: 0, transition: 'opacity 0.3s' });
        setTimeout(function () { $section.css('opacity', 1); }, 10);

        busy = false;
        loadPayHistory();
        watchSubUrl();
        armRevealsWhenReady();
      };

      if ($current.length) {
        $current.css({ opacity: 0, transition: 'opacity 0.3s ease' });
        setTimeout(done, 300);
      } else {
        done();
      }
    }

    // Клик по пунктам меню
    $(document).on('click', '[data-toggle-section]', function (e) {
      // Prevent empty # links from jumping
      const $a = $(this).closest('a');
      if ($a.length) {
        const href = ($a.attr('href') || '').trim();
        if (href === '' || href === '#') e.preventDefault();
      }

      show(currentLayout, $(this).attr('data-toggle-section'));
    });

    // Смена desktop ↔ mobile при изменении ширины окна
    if (mq.addEventListener) {
      mq.addEventListener('change', function (e) { show(e.matches ? 'layout-desktop' : 'layout-mobile', activeSection); });
    } else if (mq.addListener) {
      mq.addListener(function (e) { show(e.matches ? 'layout-desktop' : 'layout-mobile', activeSection); });
    }

    // Инициализация: рендерим только активную секцию активного layout
    show(layoutId(), activeSection);

  } else {

    // ===== Остальные страницы: классическое переключение через .hidden =====
    function showSection(sectionId) {
      // Нет такой секции (чужая кнопка) — ничего не трогаем, страницу не гасим
      const $target = $('[data-section="' + sectionId + '"]');
      if (!$target.length) return;

      // Hide every section
      $('[data-section]').addClass('hidden').css('opacity', 0);

      $target.removeClass('hidden');

      // Force reflow so the transition works
      $target[0].offsetHeight;

      $target.css({
        opacity: 1,
        transition: 'opacity 0.3s ease'
      });
      loadPayHistory();
      watchSubUrl();
      armRevealsWhenReady();
    }

    // Initial state: hide everything that already has .hidden
    $('[data-section].hidden').css('opacity', 0);

    // Click handler
    $(document).on('click', '[data-toggle-section]', function (e) {
      const $btn = $(this);
      const sectionId = $btn.attr('data-toggle-section');
      if (!sectionId) return;

      // Prevent empty # links from jumping
      const $a = $btn.closest('a');
      if ($a.length) {
        const href = ($a.attr('href') || '').trim();
        if (href === '' || href === '#') e.preventDefault();
      }

      // Already showing this section? → do nothing
      if ($('[data-section="' + sectionId + '"]:not(.hidden)').length) return;

      // Active button styling
      const $layout = $btn.closest('[data-pay-layout]');
      const $group  = $layout.length
        ? $layout.find('[data-toggle-section]')
        : $('[data-toggle-section]');

      $group.removeClass('bg_active');
      $btn.addClass('bg_active');

      showSection(sectionId);
    });
  }

});
