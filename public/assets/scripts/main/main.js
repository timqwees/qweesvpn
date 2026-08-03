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
  const $root = $('#layout-root');

  if ($root.length && document.getElementById('layout-desktop') && document.getElementById('layout-mobile')) {

    // ===== Главная страница: ленивый рендер секций =====
    // В DOM находится только активная секция активного layout (desktop/mobile).
    // Остальные лежат в <template> и не рендерятся: нет фоновых анимаций,
    // изображения не загружаются, нагрузка идёт только на видимый экран.
    const SECTIONS = ['main', 'profile', 'setting', 'referal'];

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

      // Вставляем активную секцию
      const sectionTpl = layoutTpl.content.querySelector('template[data-section="' + sectionId + '"]');
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
          const sectionTpl = document.getElementById(currentLayout)
            .content.querySelector('template[data-section="' + sectionId + '"]');
          $root.find('.js-sections').empty().append(sectionTpl.content.cloneNode(true));
        }

        activeSection = sectionId;
        updateMenuActive(sectionId);

        // Плавное появление новой секции
        const $section = $('[data-section]');
        $section.css({ opacity: 0, transition: 'opacity 0.3s' });
        setTimeout(function () { $section.css('opacity', 1); }, 10);

        busy = false;
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
      // Hide every section
      $('[data-section]').addClass('hidden').css('opacity', 0);

      // Show the requested ones
      const $target = $('[data-section="' + sectionId + '"]');
      if (!$target.length) return;

      $target.removeClass('hidden');

      // Force reflow so the transition works
      $target[0].offsetHeight;

      $target.css({
        opacity: 1,
        transition: 'opacity 0.3s ease'
      });
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
