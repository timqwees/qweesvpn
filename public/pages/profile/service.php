<?php
use App\Config\Database;
?>
<div
  class="relative flex justify-between flex-col overflow-auto from-slate-800 to-slate-900 min-h-screen font-sans bg-[#040B20]">

  <!-- Стрелка-назад в левом верхнем углу -->
  <div class="arrow-back" id="globalArrowBack" style="display: flex;">
    <svg viewBox="0 0 24 24" fill="none">
      <path d="M15 19L8 12L15 5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    </svg>
    <span style="font-size:15px" id="backBtnText">Вернуться</span>
    <a href="/" id="arrowHomeLink" class="arrow-home" title="На главную"><i class="fa fas fa-home"></i></a>
  </div>

  <div class="container bg-black">

    <!-- Профиль -->
    <div class="profile-header">
      <img id="avatar" src="${user.photo_url}" alt="${user.first_name}" class="avatar" />
      <h2>${user.first_name} ${user.last_name} - @${user.username}</h2>
      <div class="user-id" id="user-id">
        id: <span id="copy-id">${user.id}</span>
        <button onclick="copyId()" class="copy-btn" id="copy-id-btn">
          <i class="fa fa-clone"></i>
        </button>
      </div>
    </div>

    <!-- Меню -->
    <nav class="menu">

      <li class="menu-item">
        <a href="/profile/card" class="flex items-center">
          <div class="menu-icon w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center"
            style="color: #2ecc71;">
            <i class="fa fa-credit-card"></i>
          </div>
          <div class="menu-text">Оплата</div>
        </a>
      </li>

      <li class="menu-item">
        <div class="menu-icon w-8 h-8 bg-[#a492ff]/20 rounded-lg flex items-center justify-center"
          style="color: #a492ff;">
          <i class="fa fa-list"></i>
        </div>
        <div class="menu-text">Мои транзакции <span class="text-red-400">(неактивна)</span>
        </div>
      </li>

      <?php if (
        (isset($client['tg_id']) && (
          ($client['tg_id'] === ($_ENV['ADMIN_ID_1'] ?? '0')) ||
          ($client['tg_id'] === ($_ENV['ADMIN_ID_2'] ?? '0'))
        ))
      ): ?>
        <li class="menu-item">
          <a href="/admin/<?= $client['tg_id'] ?>" class="flex items-center gap-1">
            <div class="menu-icon w-8 h-8 rounded-lg flex items-center justify-center
              bg-gradient-to-br from-purple-500 via-pink-400 via-45% to-cyan-300
                tracking-tight">
              <i class="fa fa-user-shield text-black"></i>
            </div>
            <div
              class="bg-gradient-to-br from-purple-500 via-pink-400 via-45% to-cyan-300 text-transparent bg-clip-text font-medium tracking-tight drop-shadow-lg">
              Панель администратора
            </div>
          </a>
        </li>
      <?php endif; ?>

      <a href="https://t.me/spcoravpn_bot">
        <li class="menu-item">
          <div class="menu-icon w-8 h-8 bg-[#f39c12]/20 rounded-lg flex items-center justify-center"
            style="color: #f39c12;">
            <i class="fa fa-comment"></i>
          </div>
          <div class="menu-text">Тех-поддержка AI</div>
        </li>
      </a>

      <li class="menu-item" id="open-modal-btn" onclick="openModal(this)">
        <div class="menu-icon w-8 h-8 bg-[#a492ff]/20 rounded-lg flex items-center justify-center text-[#a492ff]">
          <i class="fa fa-file-alt"></i>
        </div>
        <div class="modal-title menu-text" style="color: #a492ff;">Пользовательское соглашение</div>
        <div class="modal-content hidden text-sm leading-relaxed max-h-96 overflow-y-auto p-2"
          style="text-align:left; color: #ede9fe;">
          <strong class="text-base font-bold block mb-2" style="color: #a492ff;">Пользовательское
            соглашение и
            политика доступности</strong>
          <p class="mb-4" style="color: #c3bafc;">
            Последнее обновление:
            <span class="rounded px-2 py-0.5 font-semibold" style="background-color: #ede9fe; color: #a492ff;">
              1 июня 2024
            </span>
          </p>

          <p class="font-bold mb-2" style="color: #a492ff;">1. Общие положения</p>
          <p class="mb-3" style="color: #ede9fe;">Настоящее <span class="font-semibold"
              style="color: #a492ff;">Пользовательское соглашение</span> (далее
            — «Соглашение») регламентирует отношения между сервисом <span class="font-semibold"
              style="color: #a492ff;">CoraVPN</span> и Пользователем по вопросам использования сервиса
            и
            прав доступа.</p>

          <p class="font-bold mb-2" style="color: #a492ff;">2. Доступность сервиса</p>
          <ul class="list-disc pl-5 mb-3 space-y-1" style="color: #ede9fe;">
            <li>CoraVPN доступен на всех современных устройствах, включая компьютеры, смартфоны и
              планшеты. Для доступа требуется любой актуальный браузер и поддержка Telegram.</li>
            <li>Все основные функции предоставляются исключительно через Telegram-приложение и <span
                class="font-semibold" style="color: #a492ff;">@coravpn_bot</span>.</li>
            <li>Гарантируется кроссплатформенность и высокая доступность сервиса 24/7.</li>
          </ul>

          <p class="font-bold mb-2" style="color: #a492ff;">3. Регистрация и авторизация</p>
          <ul class="list-disc pl-5 mb-3 space-y-1" style="color: #ede9fe;">
            <li>Пользователь подтверждает согласие с условиями, начиная использовать сервис.</li>
            <li>Регистрация и идентификация осуществляются исключительно через Telegram, что
              обеспечивает безопасность личных данных.</li>
            <li>Рекомендуется использовать двухфакторную аутентификацию и самостоятельно контролировать
              безопасность аккаунта.</li>
          </ul>

          <p class="font-bold mb-2" style="color: #a492ff;">4. Условия использования</p>
          <ul class="list-disc pl-5 mb-3 space-y-1" style="color: #ede9fe;">
            <li>Запрещено использовать CoraVPN для противоправных целей и обхода законодательства страны
              проживания.</li>
            <li>Обязуетесь использовать сервис честно и в рамках действующего законодательства.</li>
            <li>Сервис вправе ограничить или прекратить доступ при нарушении условий соглашения.</li>
          </ul>

          <p class="font-bold mb-2" style="color: #a492ff;">5. Сбор и использование данных</p>
          <ul class="list-disc pl-5 mb-3 space-y-1" style="color: #ede9fe;">
            <li><strong class="highlight font-semibold" style="color: #a492ff;">CoraVPN не ведет
                журналов активности (No-Log
                Policy)</strong> и не хранит трафик пользователей.</li>
            <li>Минимальные технические сведения обрабатываются только в целях эффективной работы
              сервиса и поддержки.</li>
            <li>Подробнее см. Политику конфиденциальности.</li>
          </ul>

          <p class="font-bold mb-2" style="color: #a492ff;">6. Платные услуги и возвраты</p>
          <ul class="list-disc pl-5 mb-3 space-y-1" style="color: #ede9fe;">
            <li>Оплата подписок производится только через официальный Telegram-бот или приложение.</li>
            <li>Информация о пробных периодах, акциях и возвратах доступна в вашем личном кабинете или
              поддержке.</li>
            <li><span class="highlight font-semibold" style="color: #a492ff;">Возврат денежных средств,
                как правило, не
                производится</span> после оплаты и активации, за исключением особых случаев,
              предусмотренных внутренней политикой.</li>
            <li>Перед оплатой рекомендуется ознакомиться с условиями предоставления услуг и возможностью
              возврата.</li>
          </ul>

          <p class="font-bold mb-2" style="color: #a492ff;">7. Техническая доступность</p>
          <ul class="list-disc pl-5 mb-3 space-y-1" style="color: #ede9fe;">
            <li>Команда CoraVPN обеспечивает непрерывную работу сервиса 24/7, включая резервную
              инфраструктуру.</li>
            <li>Техподдержка доступна круглосуточно через Telegram.</li>
          </ul>

          <p class="font-bold mb-2" style="color: #a492ff;">8. Изменение условий соглашения</p>
          <ul class="list-disc pl-5 mb-3 space-y-1" style="color: #ede9fe;">
            <li>Сервис вправе изменять и дополнять настоящее соглашение без отдельного уведомления.</li>
            <li>Актуальная версия всегда публикуется на официальном сайте.</li>
          </ul>

          <p class="font-bold mb-2" style="color: #a492ff;">9. Связь и поддержка</p>
          <ul class="list-disc pl-5 mb-2 space-y-1" style="color: #ede9fe;">
            <li>По вопросам пользования, доступности и ваших прав — обращайтесь в официальную поддержку
              CoraVPN в Telegram:
              <a href="https://t.me/CoraVPNBot" class="underline" style="color: #a492ff;"
                target="_blank">@coravpn_bot</a>.
            </li>
          </ul>
          <hr class="my-2" style="border-color: #c3bafc;">
          <p class="text-xs mt-2" style="color:#babbf1;">
            Используя сервис, вы полностью принимаете все условия и политику конфиденциальности
            CoraVPN.<br>
            Для предотвращения мошенничества пользуйтесь только официальными Telegram-каналами и
            приложениями.<br>
            Актуальная версия соглашения размещена на официальном сайте.
          </p>
        </div>
      </li>

    </nav>

    <!-- Ссылка на подписку -->
    <div class="section <?php echo $client['vpn_status'] === 'online' ? 'block' : 'hidden'; ?>">
      <div class="section-title">Ссылка на подписку:</div>
      <div class="link-input">
        <input id="subscription-link" class="text-green-400 animate-pulse" type="text"
          value="<?php echo $client['vpn_subscription']; ?>" readonly />
        <button class="copy-btn" id="copy-link-btn" onclick="copyLink()">
          <i class="fa fa-clone"></i>
        </button>
      </div>
      <div class="flex flex-col items-center justify-center gap-2">
        <button class="action-button text-white" onclick="window.location.href='<?= $sub_link; ?>'">
          <i class="fa fas fas fa-users-cog"></i>
          <span>Открыть подписку</span>
        </button>
        <button class="action-button text-red-300" command="show-modal" commandfor="delete_key">
          <?php if ($client['vpn_freekey'] === 'coravpn_success_free_connect') {
            echo 'Отменить бесплатную подписку';
          } else {
            echo 'Отменить подписку';
          }
          ?>
        </button>
      </div>
    </div>

    <dialog id="delete_key" class="w-full h-full bg-black/80">
      <div class="flex justify-center items-center h-full w-full">
        <div
          class="bg-[#181818] rounded-xl shadow-lg p-8 max-w-sm flex flex-col items-center justify-center gap-6 relative">
          <button class="absolute top-4 right-4 text-gray-400 hover:text-white text-2xl" command="close"
            commandfor="delete_key" title="Закрыть">
            &times;
          </button>
          <div class="flex flex-col items-center gap-2 w-full">
            <i class="fa fa-exclamation-triangle text-4xl text-red-400 mb-2"></i>
            <h3 class="text-lg font-bold text-white text-center">Подтвердите удаление подписки</h3>
            <p class="text-gray-300 text-center text-sm w-full">
              Вы действительно хотите удалить вашу VPN-подписку? Действие необратимо.
              После подтверждения подписка будет немедленно отключена.
            </p>
          </div>
          <div class="flex w-full gap-4 mt-2">
            <button class="flex-1 text-gray-200 flex justify-center items-center
            rounded-lg gap-2 w-full bg-[#303030] hover:bg-[#3F3F48] transition py-[15px]" command="close"
              commandfor="delete_key">
              <i class="fa fa-times"></i>
              <span>Отмена</span>
            </button>
            <a href="/delete_key/<?php echo $client['tg_id'] ?>" class="flex-1 text-white bg-red-500 hover:bg-red-600 transition flex justify-center items-center
            rounded-lg gap-2 w-full">
              <i class="fa fa-trash"></i>
              <span>Удалить</span>
            </a>
          </div>
        </div>
      </div>
    </dialog>

    <!-- ввод кода -->
    <? if (empty($client['refer_link'])): ?>
      <div class="section">
        <div class="section-title">Ввод реферальной ссылки:</div>
        <form id="Form3" action="" method="POST" autocomplete="off">
          <div class="link-input">
            <input type="text" name="reflink" placeholder="Введите ссылку/код" required />
          </div>
          <button class="action-button" type="submit">
            <i class="fa fa-qrcode"></i>
            <span>Ввести код</span>
          </button>
        </form>
      </div>
    <? else: ?>
      <div class="section">
        <div class="section-title">Реферальная ссылка активирована</div>
        <div class="flex flex-col items-center justify-center gap-3">
          <div class="bg-green-600/20 text-green-400 px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fa fa-check-circle"></i>
            <span>Вы уже активировали реферальную ссылку.</span>
          </div>
          <div class="text-xs text-gray-400 break-all">
            Активная реферальная ссылка:
            <?php
            $refer_link_code = $client['refer_link'] ?? '';
            $refer_name = '';
            if ($refer_link_code) {
              $ref = Database::send(
                'SELECT * FROM vpn_users WHERE my_refer_link = ? LIMIT 1',
                [$refer_link_code]
              );
              if (!empty($ref[0])) {
                $u = $ref[0];
                $refer_name = trim(($u['tg_first_name'] ?? '') . ' ' . ($u['tg_last_name'] ?? ''));
                if (!$refer_name && !empty($u['tg_username']))
                  $refer_name = '@' . $u['tg_username'];
              }
            }
            ?>
            <div class="flex items-center gap-2 mt-1 text-xs">
              <i class="fa fa-link text-pink-500"></i>
              <span class="font-mono bg-[#222] px-2 py-1 rounded-lg text-white select-all cursor-pointer">
                <?= htmlspecialchars($refer_link_code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
              </span>
              <?php if ($refer_name): ?>
                <span
                  class="text-xs px-2 py-1 rounded-lg bg-green-900/40 text-green-300 font-medium tracking-tight flex items-center gap-1 shadow-sm border border-green-800 animate-pulse">
                  <i class="fa fa-user text-green-400"></i>
                  <span> by:</span>
                  <span class="italic underline decoration-dotted decoration-green-400">
                    <?= htmlspecialchars($refer_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                  </span>
                </span>
              <?php endif; ?>
            </div>
            </span>
          </div>
        </div>
      </div>
    <? endif; ?>

    <div class="section">
      <div class="section-title">Моя реферальная ссылка</div>
      <div class="flex flex-col items-center justify-center gap-3">
        <div class="bg-blue-600/20 text-blue-400 px-4 py-2 rounded-lg text-sm flex items-center gap-2">
          <i class="fa fa-link"></i>
          <span>Скопируйте свою реферальную ссылку и делитесь с друзьями!</span>
        </div>
        <?php
        $my_ref_link = isset($client['my_refer_link']) ? trim($client['my_refer_link']) : '';
        $escaped_my_ref_link = htmlspecialchars($my_ref_link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        ?>
        <div class="w-full flex flex-col gap-1">
          <label for="copy-refs" class="mb-1">Копировать реферный код</label>
          <div class="flex items-center gap-2 link-input ">
            <input id="copy-refs" class="text-green-400 animate-pulse" type="text"
              value="<?= 'ref=' . $escaped_my_ref_link ?>" readonly />
            <button class="copy-btn" id="copy-ref-btn" onclick="copyRef()">
              <i class="fa fa-clone"></i>
            </button>
          </div>
        </div>
        <div class="w-full flex flex-col gap-1">
          <label for="copy-fullrefs" class="mb-1">Копировать реферную ссылку</label>
          <div class="flex items-center gap-2 link-input ">
            <input id="copy-fullrefs" class="text-green-400 animate-pulse" type="text"
              value="<?= $refer_link_url . 'ref=' . $escaped_my_ref_link ?>" readonly />
            <button class="copy-btn" id="copy-fullref-btn" onclick="copyFullRef()">
              <i class="fa fa-clone"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-2 justify-center items-center">

      <!-- Инструкция -->
      <a href="https://teletype.in/@artemon36/helps" class="action-button">
        <i class="fa fa-laptop"></i>
        <span>Инструкция для всех платформ</span>
      </a>

      <!-- вернуться в меню -->
      <a href="/" class="action-button">
        <i class="fa fas fa-door-open text-lg"></i>
        <span>Вернуться в меню</span>
      </a>

    </div>

  </div>

  <!-- Модальное окно -->
  <div id="modal"
    class="fixed inset-0 bg-[#0000005D] flex items-center justify-center z-50 modal-hidden transition duration-200 cursor-pointer">
    <div class="bg-[#181818] top-[30vh] rounded-lg p-8 w-full h-full relative">
      <button id="close-modal"
        class="absolute top-2 right-4 text-gray-400 hover:text-white text-[2rem]">&times;</button>
      <h2 id="modal-title" class="text-xl font-bold mb-4"></h2>
      <div id="modal-content" class="mb-4 max-h-[600px] overflow-y-auto"></div>
    </div>
  </div>
</div>
