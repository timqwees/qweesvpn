/**
 * Section toggle: detach/restore instead of hidden class.
 * Only the active section stays in DOM — animations, backdrop-filter,
 * and GPU layers only exist for the visible page.
 */
var sectionStore = {};

$(document).ready(function () {

  // ── Phase 1: On load, detach all hidden sections ──
  $('[data-section]').each(function () {
    var $section = $(this);
    // Store parent reference on element for later re-attachment
    $section.data('sectionParent', $section.parent());

    if ($section.hasClass('hidden')) {
      var sectionId = $section.attr('data-section');
      $section.detach().removeClass('hidden').css('opacity', 0);
      if (!sectionStore[sectionId]) {
        sectionStore[sectionId] = [];
      }
      sectionStore[sectionId].push($section);
    }
  });

  // ── Phase 2: Toggle sections via DOM remove/restore ──
  $(document).on('click', function (e) {
    var $toggle = $(e.target).closest('[data-toggle-section]');
    if (!$toggle.length) return;

    // Prevent default on empty anchors
    var $anchor = $(e.target).closest('a');
    if ($anchor.length) {
      var href = ($anchor.attr('href') || '').trim();
      if (href === '' || href === '#') {
        e.preventDefault();
      }
    }

    var sectionId = $toggle.attr('data-toggle-section');

    // Skip if already on this section
    var $currentSections = $('[data-section]');
    if ($currentSections.filter('[data-section="' + sectionId + '"]').length) return;

    // Toggle active button styles
    var $layout = $toggle.closest('[data-pay-layout]');
    var $toggleGroup = $layout.length ? $layout.find('[data-toggle-section]') : $('[data-toggle-section]');
    $toggleGroup.removeClass('bg_active');
    $toggle.addClass('bg_active');

    // 1. Detach all currently visible sections, store them
    $currentSections.each(function () {
      var $s = $(this);
      var id = $s.attr('data-section');
      $s.data('sectionParent', $s.parent()); // refresh parent ref
      $s.detach();
      if (!sectionStore[id]) {
        sectionStore[id] = [];
      }
      sectionStore[id].push($s);
    });

    // 2. Restore target sections from store
    if (sectionStore[sectionId] && sectionStore[sectionId].length) {
      var sections = sectionStore[sectionId];
      sectionStore[sectionId] = [];

      sections.forEach(function ($s) {
        var $parent = $s.data('sectionParent');
        $parent.append($s);

        // Fade-in animation
        $s.css({ 'opacity': 0, 'transition': 'opacity 0.3s' });
        setTimeout(function () {
          $s.css('opacity', 1);
        }, 10);
      });
    }
  });

  // ── Modal functionality ──
  $('[data-toggle-modal]').on('click', function () {
    var modalName = $(this).attr('data-toggle-modal');
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