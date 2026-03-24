$(document).ready(function () {
  // Menu navigation functionality
  $('[data-toggle-section]').on('click', function () {
    var sectionId = $(this).attr('data-toggle-section');

    // устанавливаем active выбранного каталога
    $('[data-toggle-section]').each(function () {
      $(this).removeClass('bg_active');
    });
    $(this).addClass('bg_active');

    // Скрыть все секции с плавным исчезновением
    $('[data-section]').each(function () {
      $(this).css({
        'transition': 'opacity 0.3s',
        'opacity': 0
      });
      setTimeout(function () {
        $(this).addClass('hidden');
      }.bind(this), 300);
    });

    // Показать выбранную секцию с плавным появлением
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
});
