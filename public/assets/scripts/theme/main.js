
const html = document.documentElement;
const theme = localStorage.getItem('theme');
const darkModeToggle = document.getElementById('darkModeToggle');

if (theme === 'light' || !theme) {
  html.classList.add('light');
  if (darkModeToggle) darkModeToggle.checked = true;
}

if (darkModeToggle) {
  darkModeToggle.addEventListener('change', () => {
    const isDarkMode = darkModeToggle.checked;
    html.classList.toggle('light', isDarkMode);
    localStorage.setItem('theme', isDarkMode ? 'light' : '');
  });
}
