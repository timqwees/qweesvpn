$(document).ready(function () {
  // Применяет сохранённый язык к чекбоксу,
  // если он появился в DOM (секция "Настройки" рендерится лениво)
  function syncToggle() {
    const $toggle = $('[data-language]');
    if ($toggle.length) {
      $toggle.prop('checked', localStorage.getItem('language') === 'en');
    }
  }

  // Переключение языка (делегирование — работает и для элементов, рендерящихся позже)
  $(document).on('change', '[data-language]', function () {
    const newLanguage = $(this).is(':checked') ? 'en' : 'ru';
    localStorage.setItem('language', newLanguage);

    $.post('/language/switch', { language: newLanguage })
      .done(function () {
        location.reload();
      })
      .fail(function () {
        alert('Ошибка запроса');
      });
  });

  syncToggle();

  // Секции на главной рендерятся лениво — следим за их появлением
  const root = document.getElementById('layout-root');
  if (root && window.MutationObserver) {
    new MutationObserver(syncToggle).observe(root, { childList: true, subtree: true });
  }
});
