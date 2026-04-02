$(document).ready(function() {
  const currentlang = localStorage.getItem('language');
  const $languageToggle = $('[data-language]');

  if (currentlang === 'en') {
    $languageToggle.prop('checked', true);
  }

  $languageToggle.on('change', function() {
    const newLanguage = $(this).is(':checked') ? 'en' : 'ru';
    localStorage.setItem('language', newLanguage);

    $.post('/language/switch', { language: newLanguage })
      .done(function() {
        location.reload();
      })
      .fail(function() {
        alert('Ошибка запроса');
      });
  });
});