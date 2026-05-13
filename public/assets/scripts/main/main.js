$(document).ready(function () {
  $(document).on('click', function (e) {
    var $toggle = $(e.target).closest('[data-toggle-section]');
    if (!$toggle.length) return;

    var $anchor = $(e.target).closest('a');
    if ($anchor.length) {
      var href = ($anchor.attr('href') || '').trim();
      if (href === '' || href === '#') {
        e.preventDefault();
      }
    }

    var sectionId = $toggle.attr('data-toggle-section');

    // /pay и др.: дубли desktop/mobile с одинаковыми data-section — переключаем только внутри колонки
    var $layout = $toggle.closest('[data-pay-layout]');
    var $toggleGroup = $layout.length ? $layout.find('[data-toggle-section]') : $('[data-toggle-section]');
    var $sectionGroup = $layout.length ? $layout.find('[data-section]') : $('[data-section]');

    if ($toggleGroup.filter('.bg_active').length) {
      $toggleGroup.removeClass('bg_active');
      $toggle.addClass('bg_active');
    }

    $sectionGroup.each(function () {
      $(this).css({
        'transition': 'opacity 0.3s',
        'opacity': 0
      });
      setTimeout(function () {
        $(this).addClass('hidden');
      }.bind(this), 300);
    });

    var targetSection = $sectionGroup.filter('[data-section="' + sectionId + '"]');
    if (targetSection.length) {
      setTimeout(function () {
        targetSection.removeClass('hidden');
        targetSection.css({
          'transition': 'opacity 0.3s',
          'opacity': 0
        });
        setTimeout(function () {
          targetSection.css('opacity', 1);
        }, 10);
      }, 300);
    }
  });

  // Modal functionality
  $('[data-toggle-modal]').on('click', function () {
    var modalName = $(this).attr('data-toggle-modal');//get
    var targetModal = $('[data-modal="' + modalName + '"]');

    // Hide all modals first
    $('[data-modal]').each(function () {
      $(this).css({
        'transition': 'opacity 0.3s',
        'opacity': 0
      });
      setTimeout(function () {
        $(this).addClass('hidden');
      }.bind(this), 300);
    });

    // Show target modal
    if (targetModal.length) {
      setTimeout(function () {
        targetModal.removeClass('hidden');
        targetModal.css({
          'transition': 'opacity 0.3s',
          'opacity': 0
        });
        setTimeout(function () {
          targetModal.css('opacity', 1);
        }, 10);
      }, 300);
    }
  });

  // Close modal when clicking outside
  $('[data-modal]').on('click', function (e) {
    if (e.target === this) {
      $(this).css({
        'transition': 'opacity 0.3s',
        'opacity': 0
      });
      setTimeout(function () {
        $(this).addClass('hidden');
      }.bind(this), 300);
    }
  });

  // Close modal with close buttons
  $('.modal-close, .modal-btn-close').on('click', function () {
    var modal = $(this).closest('.modal-overlay');
    modal.css({
      'transition': 'opacity 0.3s',
      'opacity': 0
    });
    setTimeout(function () {
      modal.addClass('hidden');
    }, 300);
  });
});
