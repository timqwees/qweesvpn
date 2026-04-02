$(document).ready(function () {
  const $html = $('html');
  const theme = localStorage.getItem('theme');
  const $darkModeToggle = $('[data-darkModeToggle]');

  if (theme === 'light') {
    $html.addClass('light');
    if ($darkModeToggle.length) $darkModeToggle.prop('checked', true);
  }

  $darkModeToggle.on('change', function () {
    const isDarkMode = $(this).prop('checked');
    $html.toggleClass('light', isDarkMode);
    localStorage.setItem('theme', isDarkMode ? 'light' : '');
  });
});
