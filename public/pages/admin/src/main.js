function setDark(isDark) {
  const html = document.documentElement;
  html.classList.toggle('dark', isDark);
  html.classList.toggle('light', !isDark);
  localStorage.setItem('vpn-theme', isDark ? 'dark' : 'light');
  const icon = document.getElementById('theme-icon');
  if (icon) icon.setAttribute('data-feather', isDark ? 'moon' : 'sun');
  if (typeof feather !== "undefined") feather.replace();
}

document.addEventListener("DOMContentLoaded", function () {
  const saved = localStorage.getItem('vpn-theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  setDark(saved ? saved === 'dark' : prefersDark);

  const themeToggle = document.getElementById("theme-toggle");
  if (themeToggle) {
    themeToggle.onclick = function () {
      const isDark = document.documentElement.classList.contains('dark');
      setDark(!isDark);
    };
  }

  if (typeof feather !== "undefined") feather.replace();
});

// Mobile nav drawer logic
const navBtn = document.getElementById('nav-toggle');
const mobileNav = document.getElementById('mobile-nav');
const mobileMenu = document.getElementById('mobile-menu');

if (navBtn && mobileNav && mobileMenu) {
  navBtn.addEventListener('click', () => {
    mobileNav.style.opacity = '1';
    mobileNav.style.pointerEvents = "auto";
    mobileMenu.style.transform = "translateX(0)";
  });

  mobileNav.addEventListener('click', (e) => {
    if (e.target === mobileNav) {
      mobileNav.style.opacity = '0';
      mobileNav.style.pointerEvents = "none";
      mobileMenu.style.transform = "translateX(-288px)";
    }
  });
}
