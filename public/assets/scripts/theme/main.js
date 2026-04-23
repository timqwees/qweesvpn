$(document).ready(function () {
  const $html = $('html');
  const theme = localStorage.getItem('theme');
  const $darkModeToggle = $('[data-darkModeToggle]');

  // Обновляем отображение темы в профиле
  function updateProfileTheme() {
    const $profileTheme = $('#profile-theme');
    if ($profileTheme.length) {
      const isLight = localStorage.getItem('theme') === 'light';
      const themeName = isLight ? $profileTheme.data('light') : $profileTheme.data('dark');
      $profileTheme.text(themeName);
    }
  }

  if (theme === 'light') {
    $html.addClass('light');
    if ($darkModeToggle.length) $darkModeToggle.prop('checked', true);
  }

  // Инициализируем отображение темы в профиле
  updateProfileTheme();

  $darkModeToggle.on('change', function () {
    const isDarkMode = $(this).prop('checked');
    $html.toggleClass('light', isDarkMode);
    localStorage.setItem('theme', isDarkMode ? 'light' : '');
    // Обновляем отображение при переключении
    updateProfileTheme();
  });
});
