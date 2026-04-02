$(document).ready(function () {
  $('[data-toggle-section]').on('click', function () {
    var sectionId = $(this).attr('data-toggle-section');

    if ($('[data-toggle-section]').hasClass('bg_active')) {
      $('[data-toggle-section]').each(function () {
        $(this).removeClass('bg_active');
      });
      $(this).addClass('bg_active');
    }

    $('[data-section]').each(function () {
      $(this).css({
        'transition': 'opacity 0.3s',
        'opacity': 0
      });
      setTimeout(function () {
        $(this).addClass('hidden');
      }.bind(this), 300);
    });

    var targetSection = $('[data-section="' + sectionId + '"]');
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
