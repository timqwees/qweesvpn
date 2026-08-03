<link rel="preload" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css" as="style"
  crossorigin="anonymous" onload="this.onload=null;this.rel='stylesheet'">
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
<script defer>
  const driver = window.driver.js.driver;
  if (!localStorage.getItem('tourShown')) {
    const btn = [...document.querySelectorAll('.btn_install_tour')].find(el => el.offsetParent !== null);
    if (btn) {
      const d = driver({
        steps: [{
          element: btn,
          popover: {
            title: 'Давайте установим VPN',
            description: 'Нажмите, чтобы начать установку VPN'
          }
        }],
        onDestroyed: () => localStorage.setItem('tourShown', '1')
      });
      window.addEventListener('load', () => setTimeout(() => d.drive(), 3500));
    }
  }
  // INSTALL PAGE TOUR ========================================================
  function visible(sel) {
    return [...document.querySelectorAll(sel)].find(el => el.offsetParent !== null);
  }

  function createClickTour(steps, key) {
    let i = 0;

    function show() {
      const el = visible(steps[i].element);
      if (!el) return;

      const d = driver({
        steps: [{ element: el, popover: steps[i].popover }]
      });

      const next = () => {
        el.removeEventListener('click', next);
        d.destroy();
        i++;
        if (i < steps.length) {
          setTimeout(show, 300); // ждём переключение секции
        } else if (key) {
          localStorage.setItem(key, '1');
        }
      };

      el.addEventListener('click', next);
      d.drive();
    }

    show();
  }

  // INSTALL PAGE TOUR
  window.addEventListener('load', () => {
    setTimeout(() => {
      if (!visible('.btn_install_page_tour')) return;
      else if (!localStorage.getItem('tourShown2')) {
        createClickTour([
          {
            element: '.btn_install_page_tour',
            popover: { title: 'Начать установку', description: 'Нажмите, чтобы начать установку VPN' }
          },
          {
            element: '.btn_download_page_tour',
            popover: { title: 'Скачаем приложение', description: 'Установите приложение и вернитесь на сайт' }
          },
          {
            element: '.btn_next_page_tour',
            popover: { title: 'Почти готово', description: 'Нажмите, чтобы перейти к последнему шагу' }
          },
          {
            element: '.btn_vpn_install_page_tour',
            popover: { title: 'Установим VPN', description: 'Подписка установится в приложение автоматически' }
          },
          {
            element: '.btn_finish_page_tour',
            popover: { title: 'Готово!', description: 'VPN установлен. Приятного пользования!' }
          }
        ], 'tourShown2');
      }
    }, 3000);
  });
</script>