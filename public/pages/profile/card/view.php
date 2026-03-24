<div class="flex flex-col min-h-screen bg-[#040B20]/90">
  <!-- Кнопка назад -->
  <div class="flex items-center px-4 pt-4">
    <a href="/profile" class="text-gray-200 hover:text-white flex gap-2">
      <svg width="28" height="28" fill="none" viewBox="0 0 24 24">
        <path d="M15 19L8 12L15 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span class="text-gray-300 font-medium">Вернуться</span>
    </a>
  </div>

  <!-- Основной блок -->
  <div class="container bg-[#07051a] max-w-lg mx-auto my-10 rounded-2xl p-7 shadow-lg border border-[#230b3c]/30">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-white mb-2 font-[max]">
        Автоплатёж — <span class="text-[#b276f7]">Карты</span>
      </h1>
      <p class="text-gray-400 mb-1">
        Здесь вы можете увидеть свою карту для автоплатежей и управлять её статусом.
      </p>
    </div>
    <div class="mb-4">


      <?php if ($hasCard): ?>
        <div class="w-full flex flex-col items-center ">
          <div class="relative w-full max-w-sm mx-auto mb-6 select-none">
            <div class="relative rounded-3xl p-6 md:p-8 shadow-2xl" style="
                  background: linear-gradient(145deg, #3a2476 0%, #7c4ad4 100%);
                  min-height: 220px;
                  box-shadow:
                    0 12px 40px rgba(124, 74, 212, 0.3),
                    0 4px 20px rgba(178, 118, 247, 0.2),
                    inset 0 1px 0 rgba(255,255,255,0.08);
                  transform-style: preserve-3d;
                  perspective: 1000px;
                  overflow: hidden;
                ">
              <!-- Световые линии (лучи) -->
              <div class="absolute inset-0 pointer-events-none z-0">
                <div class="ray ray-1"></div>
                <div class="ray ray-2"></div>
                <div class="ray ray-3"></div>
                <div class="ray ray-4"></div>
              </div>

              <!-- Микро-частицы -->
              <div class="particles" id="particles"></div>

              <!-- Основной контент -->
              <div class="relative z-10 flex flex-col h-full justify-between" style="min-height: 175px">
                <!-- Верхний блок: название и имя -->
                <div class="flex justify-between items-start">
                  <div>
                    <span
                      class="block uppercase text-xs tracking-widest font-semibold mb-2 text-[#f5edff]/70 animate-fadeIn">
                      CoraVPN • CARD
                    </span>
                    <span
                      class="text-lg md:text-xl font-bold text-white tracking-tight leading-tight max-w-[180px] truncate animate-fadeIn delay-300">
                      <?= htmlspecialchars($client['tg_first_name'] ?: 'CoraVPN User', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </div>
                  <div class="flex flex-col items-end">
                    <span
                      class="text-[10px] uppercase tracking-widest font-bold text-[#d0c5ff] mb-1 flex items-center gap-1 animate-fadeIn delay-500">
                      <i class="fa-solid fas fa-key text-[#b276f7] text-xs"></i>
                      ID
                    </span>
                    <span
                      class="text-sm md:text-base font-mono tracking-wider text-white font-medium animate-fadeIn delay-700">
                      <?php
                      $id = !empty($autoPayData['autopay_id'])
                        ? $autoPayData['autopay_id']
                        : (!empty($autoPayData['card_token']) ? $autoPayData['card_token'] : '');
                      echo mb_strtoupper(substr($id, 0, 8) ?: '— — — —');
                      ?>
                    </span>
                  </div>
                </div>

                <!-- Чип + статус -->
                <div class="flex items-center justify-between mt-6">
                  <div class="flex items-center space-x-3">
                    <!-- Чип (с внутренним свечением) -->
                    <div class="relative">
                      <div
                        class="bg-gradient-to-br from-[#ffd54f] via-[#ffca28] to-[#ffb300] rounded-[6px] w-10 h-7 shadow-lg border border-yellow-200 flex items-center justify-center overflow-hidden animate-pulse">
                        <div class="w-6 h-3 border-t-2 border-[#ff8f00] border-opacity-40 border-b border-[#ffecb3]">
                        </div>
                      </div>
                      <div class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-[#ff6d00] animate-ping"></div>
                    </div>
                    <span
                      class="text-[11px] font-mono tracking-wide text-white/50 uppercase animate-fadeIn delay-900"><?= autopay_badge($autoPayData['autopay_active']) ?></span>
                  </div>
                </div>

                <!-- Логотип CoraVPN (внизу-справа, с ореолом) -->
                <div class="absolute bottom-0 text-center text-xs right-5 flex flex-col justify-center items-center">
                  <svg width="48" height="48" viewBox="0 0 48 48" fill="none" class="drop-shadow-md animate-pulse">
                    <circle cx="24" cy="24" r="23" stroke="#b276f7" stroke-width="1.5" fill="#2a1f49" />
                    <circle cx="24" cy="24" r="22" stroke="#ffffff10" stroke-width="1" fill="none" />
                    <text x="24" y="30" text-anchor="middle" font-size="20"
                      font-family="Montserrat, system-ui, sans-serif" fill="#b276f7" font-weight="700"
                      letter-spacing="0.5">CV</text>
                    <!-- Ореол вокруг логотипа -->
                    <circle cx="24" cy="24" r="24" stroke="#b276f7" stroke-width="1" fill="none" stroke-dasharray="10 10"
                      class="animate-spin-slow"></circle>
                  </svg>
                  <p>CoraVPN<br>UNLIMITED</p>
                </div>
              </div>
            </div>
          </div>

          <!-- 💡 Кнопки действий -->
          <div class="w-full mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">

            <?php if ($autopayActive): ?>
              <a href="/autopay/unbind/<?= htmlspecialchars($client['tg_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                class="group relative overflow-hidden flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-gradient-to-tr from-orange-400 via-orange-500 to-orange-600 text-white font-extrabold shadow-2xl transition-all duration-150 hover:from-orange-300 hover:via-orange-400 hover:to-orange-500 hover:scale-[1.033] active:scale-95 border border-orange-300/40">
                <span
                  class="absolute left-0 top-0 w-full h-full bg-gradient-to-tr from-orange-100/20 to-transparent opacity-90 pointer-events-none group-hover:opacity-100 transition"></span>
                <span class="relative z-10 flex items-center gap-2">
                  <span
                    class="flex items-center justify-center bg-orange-400 group-hover:bg-orange-500 bg-opacity-80 rounded-full p-2 shadow-lg animate-pulse transition-all duration-200">
                    <i class="fa-solid fas fa-power-off fa-lg drop-shadow-[0_1px_3px_rgba(255,140,0,0.45)]"></i>
                  </span>
                  <span class="whitespace-nowrap tracking-wider drop-shadow-[0_1px_3px_rgba(255,140,0,0.25)]">Отключить
                    автоплатёж</span>
                </span>
                <span
                  class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none group-hover:right-2 transition-all duration-200 opacity-60 scale-75 group-hover:scale-90">
                  <svg width="32" height="32" viewBox="0 0 32 32" class="text-orange-400/80 animate-spin-slow">
                    <defs>
                      <radialGradient id="g_orange2" cx="60%" cy="40%" r="100%">
                        <stop stop-color="#ffe4b3" offset="0%" />
                        <stop stop-color="#ffd34f" offset="70%" />
                        <stop stop-color="#ff9800" offset="100%" />
                      </radialGradient>
                    </defs>
                    <circle cx="16" cy="16" r="13" stroke="url(#g_orange2)" stroke-width="2.7" fill="none"
                      stroke-dasharray="5 20" />
                  </svg>
                </span>
              </a>

            <?php else: ?>

              <a href="/autopay/bind/<?= htmlspecialchars($client['tg_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                class="group relative overflow-hidden flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-gradient-to-tr from-[#9a76ff] via-[#7846fa] to-[#2e3278] text-white font-extrabold shadow-2xl transition-all duration-150 hover:from-[#f5c2ff] hover:via-violet-400 hover:to-[#5e13df] hover:scale-[1.028] active:scale-95 border border-violet-300/20">
                <span
                  class="absolute left-0 top-0 w-full h-full bg-gradient-to-r from-[#c9b7ff]/20 to-transparent opacity-80 pointer-events-none group-hover:opacity-100 transition"></span>
                <span class="relative z-10 flex items-center gap-2">
                  <span
                    class="flex items-center justify-center bg-violet-500 group-hover:bg-violet-600 bg-opacity-80 rounded-full p-2 shadow-lg animate-pulse transition-all duration-200">
                    <i class="fa-solid fas fa-bolt fa-lg drop-shadow-[0_1px_3px_rgba(155,100,255,0.45)]"></i>
                  </span>
                </span>
                <span class="whitespace-nowrap tracking-wider drop-shadow-[0_1px_1px_rgba(120,70,250,0.25)]">Включить
                  автоплатёж</span>
                </span>
                <span
                  class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none group-hover:right-2 transition-all duration-200 opacity-60 scale-75 group-hover:scale-90">
                  <svg width="32" height="32" viewBox="0 0 32 32" class="text-[#c7bfff]/80 animate-spin-slow">
                    <defs>
                      <radialGradient id="c2" cx="60%" cy="40%" r="100%">
                        <stop stop-color="#e2daff" offset="0%" />
                        <stop stop-color="#ab8cff" offset="80%" />
                        <stop stop-color="#4e36c5" offset="100%" />
                      </radialGradient>
                    </defs>
                    <circle cx="16" cy="16" r="13" stroke="url(#c2)" stroke-width="2.7" fill="none"
                      stroke-dasharray="5 20" />
                  </svg>
                </span>
              </a>

            <?php endif; ?>

            <a href="/autopay/delete-card/<?= htmlspecialchars($client['tg_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
              class="group relative isolate overflow-hidden flex items-center justify-center gap-3 py-4 px-7 rounded-2xl bg-gradient-to-br from-[#510a24] via-[#a50f2d] to-[#330d18] text-white font-extrabold shadow-xl transition-all duration-200 hover:from-[#ff4444] hover:via-[#ff1666] hover:to-[#cf344a] hover:text-white hover:scale-[1.06] active:scale-95 border-2 border-red-700/30 focus:ring-4 focus:ring-pink-400/40 outline-none"
              style="box-shadow: 0 4px 24px 2px #a50f2d60, 0 1.5px 8px 0 #ff2c2c15;">
              <!-- Decorative floating shape -->
              <span
                class="absolute -left-10 -top-10 w-28 h-28 bg-[#ffb3b3]/30 rounded-full blur-2xl opacity-50 pointer-events-none group-hover:opacity-70 transition"></span>
              <span class="relative z-10 flex items-center gap-4">
                <span
                  class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-tr from-[#ff5e5e]/60 to-[#a50f2d]/80 shadow-lg group-hover:from-[#ff4a6b] group-hover:to-[#c32f4b] transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white group-hover:text-red-100 transition"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h10" />
                  </svg>
                </span>
                <span class="flex flex-col items-start text-left">
                  <span class="text-xl font-bold tracking-tight drop-shadow-[0_2px_8px_rgba(255,44,44,0.20)]">Удалить
                    карту</span>
                  <span
                    class="text-xs text-red-200/90 font-normal mt-1 drop-shadow-[0_1px_4px_rgba(255,44,44,0.10)] group-hover:text-white transition">
                    Это действие нельзя отменить
                  </span>
                </span>
              </span>
              <!-- Subtle overlay highlight -->
              <span
                class="absolute inset-0 bg-gradient-to-r from-red-200/5 via-transparent to-red-400/10 opacity-60 pointer-events-none group-hover:opacity-80 transition"></span>
            </a>

            <style>
              @keyframes pulseSlow {

                0%,
                100% {
                  opacity: 0.8;
                }

                50% {
                  opacity: 0.4;
                }
              }

              .animate-pulse-slow {
                animation: pulseSlow 2.4s ease-in-out infinite;
              }
            </style>

          </div>
        </div>

      <?php else: ?>
        <div class="flex flex-col items-center justify-center py-10">
          <span class="text-xl text-white font-bold mb-4">Карта не привязана</span>
          <span class="text-gray-400 text-center mb-3 block max-w-md">
            Для привязки карты и включения автоплатежа требуется провести оплату 1&nbsp;₽ через ЮKassa.
            Это
            необходимо
            для
            подтверждения вашей карты.<br><br>
          </span>
          <span class="text-gray-500 text-center text-xs mb-4 block max-w-md">
            После успешной оплаты автоматическое продление и удобная оплата будут доступны без повторного
            ввода
            карты.
          </span>
          <form id="paymentForm2" action="" method="POST" autocomplete="off">
            <div class="relative">
              <label for="email" class="absolute left-2 border rounded-md -top-1 bg-card px-1 text-white text-xs">Почта
              </label>
              <input id='email' type="email" name="email" required autocomplete="email" placeholder="Ваш email"
                class="block w-full px-4 py-3 mb-4 rounded-lg bg-[#22193d] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#654ea3]">
            </div>
            <button type="submit"
              class="inline-block px-7 py-3 rounded-xl bg-[#37097b] text-white hover:bg-[#522aba] font-semibold transition text-base">
              <i class="fa fa-credit-card mr-2"></i> Привязать карту: 1&nbsp;₽
            </button>
          </form>
          <span class="text-[13px] text-center text-gray-500 mt-6 block">Оплата 1&nbsp;₽ — это не списание
            за
            подписку,
            а
            только операция для привязки карты.</span>
        </div>
      <?php endif; ?>
    </div>

    <div class="bg-[#18172B] p-4 rounded-lg border border-[#33235a] mb-6">
      <ul class="list-inside text-gray-400 text-sm space-y-1">
        <li class="flex items-center">
          <i class="fa fa-lock mr-2 text-[#b276f7]"></i> Данные защищены PCI DSS
        </li>
        <li class="flex items-center">
          <i class="fa fa-sparkles mr-2 text-blue-400"></i> Моментальное автопродление — без повторного
          ввода
          карты
        </li>
        <li class="flex items-center">
          <i class="fa fa-rocket mr-2 text-pink-300"></i> Отвязать карту и отменить автоплатёж можно в
          любой
          момент
        </li>
      </ul>
    </div>

    <div class="text-xs text-center text-gray-500 pt-5">
      <span>Оставайтесь на связи.&nbsp;CoraVPN ©
        <?= date('Y') ?>
      </span>
    </div>
  </div>
</div>
