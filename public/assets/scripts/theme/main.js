$(document).ready(function () {
  const $html = $('html');

  // Восстанавливаем сохранённую тему
  if (localStorage.getItem('theme') === 'light') {
    $html.addClass('light');
  }

  // Обновляем отображение темы в профиле
  function updateProfileTheme() {
    const isLight = localStorage.getItem('theme') === 'light';
    const $profileTheme = $('#profile-theme');
    const themeName = isLight
      ? ($profileTheme.data('light') || 'Светлая')
      : ($profileTheme.data('dark') || 'Темная');

    if ($profileTheme.length && $profileTheme.text() !== themeName) $profileTheme.text(themeName);

    // Мобильная версия профиля (как было раньше через [data-theme-text])
    $('[data-theme-text]').each(function () {
      if ($(this).text() !== themeName) $(this).text(themeName);
    });
  }

  // Применяет сохранённое состояние к чекбоксу,
  // если он появился в DOM (секция "Настройки" рендерится лениво)
  function syncToggle() {
    const $toggle = $('[data-darkModeToggle]');
    if ($toggle.length) {
      $toggle.prop('checked', localStorage.getItem('theme') === 'light');
    }
  }

  // Переключение темы (делегирование — работает и для элементов, рендерящихся позже)
  $(document).on('change', '[data-darkModeToggle]', function () {
    const isDarkMode = $(this).prop('checked');
    $html.toggleClass('light', isDarkMode);
    localStorage.setItem('theme', isDarkMode ? 'light' : '');
    updateProfileTheme();
  });

  function syncAll() {
    syncToggle();
    updateProfileTheme();
  }

  // Первичная синхронизация (например, при ?section=setting)
  syncAll();

  // Секции на главной рендерятся лениво — следим за их появлением
  const root = document.getElementById('layout-root');
  if (root && window.MutationObserver) {
    new MutationObserver(syncAll).observe(root, { childList: true, subtree: true });
  }
});
