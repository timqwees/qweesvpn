<?php declare(strict_types=1);
use Setting\Route\Function\Controllers\Auth\Auth;
Auth::auth();
?>
<!DOCTYPE html>
<html lang="ru" class="dark">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Оплата</title>
  <!-- fonts + tailwind + normalize + styles + JQuary -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script> -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet" />
  <link rel="stylesheet" href="/public/assets/styles/style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <!--  -->
</head>

<body class="bg-black bg-no-repeat flex item-center w-full overflow-x-hidden">
  <div class="min-h-screen flex flex-col w-full container mx-auto">
    <!-- navbar top -->
    <header class="fixed z-50 left-0 top-2 right-0 h-16 px-6 flex items-center justify-between">
      <!-- refresh -->
      <i class="fa fa-refresh text-white"></i>
      <!-- logo -->
      <div class="flex items-center gap-2">
        <img class="w-auto h-7 object-contain" src="/public/assets/images/icons/logo/qweesvpn.svg" alt="qweesvpn">
        <h2 class="text-white text-xl font-[qwees-poppins-medium] tracking-wider">QWEES <span
            class="text-green-400">VPN</span></h2>
      </div>
      <!-- version -->
      <span class="text-white text-sm">v1.0.0</span>
    </header>
    <main class="flex sm:my-2 w-full">
      <!-- КОНЕЦ БЕЗ ИЗМЕНЕНИЙ -->

      <!-- ################# CONTENT DESCKTOP ####################-->
      <div class="hidden sm:block w-full text-white">
      </div>

      <!-- ################# CONTENT MOBILE ####################-->
      <div class="sm:hidden w-full text-white">
        <!-- main -->
        <section data-section="main"
          class="overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
          <!-- icon -->
          <div class="mobile w-full flex justify-center items-center">
            <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
              <img loading="lazy" src="/public/assets/images/icons/services/buy/crown.svg" alt="Домой" decoding="async">
            </div>
          </div>
          <!-- text -->
          <div class="flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold font-sans">Выберите подписку</h3>
            <div class="text-center text-white/70">Получите полную свободу от реклам и запретов!</div>
          </div>
          <!-- grid -->
          <div class="grid grid-cols-2 grid-rows-2 gap-2">
            <!-- block 1 -->
            <div class="relative flex flex-col p-2 gap-2 border_light_b border_light_r pb-4">
              <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif1.svg" alt="icon1"
                  loading="lazy">
                <h4 class="text-lg font-bold font-sans">Аноним</h4>
              </div>
              <p class="text-sm text-sans text-white/70 break-all">Маскируем вашу сеть от
                перехватов</p>
            </div>
            <!-- block 2 -->
            <div class="relative flex flex-col p-2 gap-2 border_light_b pl-4">
              <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/speed.svg" alt="icon1"
                  loading="lazy">
                <h4 class="text-lg font-bold font-sans">Скрость</h4>
              </div>
              <p class="text-sm text-sans text-white/70 break-all">Даем скорость более 1000 Mb/s</p>
            </div>
            <!-- block 3 -->
            <div class="relative flex flex-col p-2 gap-2 border_light_r">
              <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/ads.svg" alt="icon1"
                  loading="lazy">
                <h4 class="text-lg font-bold font-sans">Без рекламы</h4>
              </div>
              <p class="text-sm text-sans text-white/70 break-all">Блокируем все рекламы в интернете</p>
            </div>
            <!-- block 4 -->
            <div class="relative flex flex-col p-2 gap-2 pl-4">
              <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/shield.svg" alt="icon1"
                  loading="lazy">
                <h4 class="text-lg font-bold font-sans">Скрытность</h4>
              </div>
              <p class="text-sm text-sans text-white/70 break-all">Защита ваших данных в сети</p>
            </div>
          </div>
          <!-- select tarif -->
          <div class="flex flex-col gap-4">
            <!-- inputs -->
            <div class="flex flex-col gap-4 buy">
              <script>
                $(document).ready(function () {
                  // Menu navigation functionality
                  $('[data-select-section]').on('click', function () {
                    var sectionId = $(this).attr('data-select-section');
                    $('#main').attr('data-toggle-section', sectionId);
                  });
                  $('[data-select2-section]').on('click', function () {
                    var sectionId = $(this).attr('data-select2-section');
                    $('#main').attr('data-toggle2-section', sectionId);
                  });
                  
                  // Payment processing
                  $('.payment-submit-btn').on('click', function(e) {
                    e.preventDefault();
                    
                    // Собираем данные формы
                    const selectedTariff = $('input[name="subscription"]:checked').val();
                    const selectedPayment = $('input[name="payment"]:checked').val();
                    
                    if (!selectedTariff || !selectedPayment) {
                      alert('Пожалуйста, выберите тариф и способ оплаты');
                      return;
                    }
                    
                    // Определяем сумму на основе тарифа
                    let amount = 0;
                    let tariffType = '';
                    switch(selectedTariff) {
                      case '1month_1':
                        amount = 150;
                        tariffType = '1month_1';
                        break;
                      case '1month_4':
                        amount = 180;
                        tariffType = '1month_4';
                        break;
                      case '1month_10':
                        amount = 200;
                        tariffType = '1month_10';
                        break;
                      case '6months_1':
                        amount = 720;
                        tariffType = '6months_1';
                        break;
                      case '6months_4':
                        amount = 900;
                        tariffType = '6months_4';
                        break;
                      case '6months_10':
                        amount = 1200;
                        tariffType = '6months_10';
                        break;
                      case '12months_1':
                        amount = 1188;
                        tariffType = '12months_1';
                        break;
                      case '12months_4':
                        amount = 1800;
                        tariffType = '12months_4';
                        break;
                      case '12months_10':
                        amount = 2400;
                        tariffType = '12months_10';
                        break;
                      default:
                        amount = 150;
                        tariffType = '1month_1';
                    }
                    
                    // Показываем индикатор загрузки
                    const btn = $(this);
                    const originalText = btn.html();
                    btn.html('<i class="fas fa-spinner fa-spin"></i> Обработка...').prop('disabled', true);
                    
                    // Отправляем запрос на создание платежа
                    $.ajax({
                      url: '/api/payment/create',
                      method: 'POST',
                      contentType: 'application/json',
                      data: JSON.stringify({
                        tariff: tariffType,
                        paymentMethod: selectedPayment,
                        amount: amount,
                        email: $('#user-email').val(),
                        phone: $('#user-phone').val(),
                        saveCard: $('#save-card').is(':checked')
                      }),
                      success: function(response) {
                        if (response.success) {
                          // Если есть QR-код (для СБП), показываем его
                          if (response.qr_code) {
                            showQrCode(response.qr_code, response.payment_id);
                          } else if (response.payment_url) {
                            // Перенаправляем на страницу оплаты
                            window.location.href = response.payment_url;
                          }
                        } else {
                          alert('Ошибка при создании платежа: ' + response.error);
                          btn.html(originalText).prop('disabled', false);
                        }
                      },
                      error: function(xhr, status, error) {
                        console.error('Payment error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                        
                        // Handle authentication error
                        if (xhr.status === 401) {
                            const response = xhr.responseJSON || {};
                            if (response.redirect) {
                                alert('Требуется авторизация. Перенаправляем на страницу входа...');
                                setTimeout(() => {
                                    window.location.href = response.redirect;
                                }, 1500);
                                return;
                            }
                        }
                        
                        // Показываем детальную ошибку для отладки
                        let errorMessage = 'Произошла ошибка. Пожалуйста, попробуйте еще раз.';
                        
                        if (xhr.responseJSON) {
                            errorMessage = xhr.responseJSON.error || errorMessage;
                            console.log('Server error details:', xhr.responseJSON.debug);
                        } else if (xhr.responseText) {
                            console.log('Raw response:', xhr.responseText);
                        }
                        
                        alert(errorMessage);
                        btn.html(originalText).prop('disabled', false);
                      }
                    });
                  });
                  
                  // Функция показа QR-кода для СБП
                  function showQrCode(qrUrl, paymentId) {
                    // Создаем модальное окно для QR-кода
                    const modal = `
                      <div id="qr-modal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
                        <div class="bg-gray-900 rounded-2xl p-6 max-w-sm w-full">
                          <h3 class="text-white text-xl font-bold mb-4 text-center">Оплата через СБП</h3>
                          <div class="bg-white p-4 rounded-xl mb-4">
                            <img src="${qrUrl}" alt="QR Code" class="w-full h-auto">
                          </div>
                          <p class="text-white/70 text-sm text-center mb-4">Отсканируйте QR-код в банковском приложении</p>
                          <div class="text-white/50 text-xs text-center mb-4">ID платежа: ${paymentId}</div>
                          <button onclick="closeQrModal()" class="w-full bg-green-500 text-white py-3 rounded-xl font-bold hover:bg-green-600 transition">
                            Закрыть
                          </button>
                        </div>
                      </div>
                    `;
                    $('body').append(modal);
                  }
                });
                
                function closeQrModal() {
                  $('#qr-modal').remove();
                }
              </script>
              <!-- input 1 -->
              <label data-select-section="next_1"
                class="flex bg-gradient-to-r from-white/20 to-white/5 bg_active justify-between px-6 py-1.5 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                <!-- titile -->
                <div class="flex flex-col justify-center">
                  <h5 class="text-xl font-bold">1 Месяц</h5>
                  <p class="text-white/70 font-light">Ежемесячная от 150₽</p>
                </div>
                <!-- part 2 -->
                <div class="flex items-center justify-center gap-4">
                  <!-- price -->
                  <div class="flex flex-col text-center">
                    <span class="text-3xl font-bold">200</span>
                    <p class="text-sm">₽/Месяц</p>
                  </div>
                  <!-- radio button -->
                  <div class="flex items-center justify-center">
                    <input type="radio" name="subscription" value="1month" class="sr-only peer" />
                    <div
                      class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                    </div>
                  </div>
                </div>
              </label>
              <!-- input 2 -->
              <label data-select-section="next_6"
                class="flex bg-gradient-to-r from-white/20 to-white/5 bg_active justify-between px-6 py-1.5 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                <!-- titile -->
                <div class="flex flex-col justify-center">
                  <h5 class="text-xl font-bold">6 Месяцев</h5>
                  <p class="text-white/70 font-light">Ежемесячная от 120₽</p>
                </div>
                <!-- part 2 -->
                <div class="flex items-center justify-center gap-4">
                  <!-- price -->
                  <div class="flex flex-col text-center">
                    <span class="text-3xl font-bold">720</span>
                    <p class="text-sm">₽/6 Мес</p>
                  </div>
                  <!-- radio button -->
                  <div class="flex items-center justify-center">
                    <input type="radio" name="subscription" value="6months" class="sr-only peer" />
                    <div
                      class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                    </div>
                  </div>
                </div>
              </label>
              <!-- input 3 -->
              <label data-select-section="next_12"
                class="flex bg-gradient-to-r from-white/20 to-white/5 bg_active justify-between px-6 py-1.5 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                <!-- titile -->
                <div class="flex flex-col justify-center">
                  <h5 class="text-xl font-bold">12 Месяцев</h5>
                  <p class="text-white/70 font-light">Ежемесячная от 99₽</p>
                </div>
                <!-- part 2 -->
                <div class="flex items-center justify-center gap-4">
                  <!-- price -->
                  <div class="flex flex-col text-center">
                    <span class="text-3xl font-bold">1200</span>
                    <p class="text-sm">₽/12 Мес</p>
                  </div>
                  <!-- radio button -->
                  <div class="flex items-center justify-center">
                    <input type="radio" name="subscription" value="12months" class="sr-only peer" />
                    <div
                      class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                    </div>
                  </div>
                </div>
              </label>
            </div>
            <!-- button next to -->
            <button id="main" onclick=" return false" data-toggle-section="main"
              class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
              Выбрать и продолжить <i class="fa fa-arrow-right"></i>
            </button>
            <span class="text-center text-white/70 text-sm">Далее будут тарифы</span>
          </div>

        </section>

        <!-- на 1 месяц -->
        <section data-section="next_1"
          class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
          <!-- icon -->
          <div class="mobile w-full flex justify-center items-center">
            <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
              <img loading="lazy" src="/public/assets/images/icons/services/buy/crown.svg" alt="Домой" decoding="async">
            </div>
          </div>
          <!-- text -->
          <div class="flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold font-sans">Выберите тариф</h3>
            <div class="text-center text-white/70">От выбранного тарифа зависит цена на ежемесячную оплату!</div>
          </div>
          <!-- select tarif -->
          <div class="flex flex-col gap-3">
            <!-- inputs -->
            <div class="flex flex-col gap-4 buy">
              <!-- input 1 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">1 Месяц</h5>
                    <p class="text-white/70 font-light">Тариф MYSELF</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">150</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                    <!-- radio button -->
                    <div class="flex items-center justify-center">
                      <input type="radio" name="subscription" value="1month_1" class="sr-only peer" />
                      <div
                        class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif1.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">1 устройство (для себя)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">150₽</span></p>
              </label>
              <!-- input 2 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">1 Месяц</h5>
                    <p class="text-white/70 font-light">Тариф Family</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">180</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                    <!-- radio button -->
                    <div class="flex items-center justify-center">
                      <input type="radio" name="subscription" value="1month_4" class="sr-only peer" />
                      <div
                        class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif2.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">4 устройства (для семьи)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">180₽</span></p>
              </label>
              <!-- input 3 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">1 Месяц</h5>
                    <p class="text-white/70 font-light">Тариф Business</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">200</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                    <!-- radio button -->
                    <div class="flex items-center justify-center">
                      <input type="radio" name="subscription" value="1month_10" class="sr-only peer" />
                      <div
                        class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif3.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">10 устройств (для бизнеса)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">200₽</span></p>
              </label>
            </div>
            <!-- button next to -->
            <button onclick="return false" data-toggle-section="finish"
              class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
              завершить и купить <i class="fa fa-arrow-right"></i>
            </button>
            <button onclick="return false" data-toggle-section="main"
              class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
              <i class="fa fa-arrow-left"></i> Вернуться назад
            </button>
            <span class="text-center text-white/70 text-sm">Далее будет покупка</span>
          </div>

        </section>

        <!-- на 6 месяцев -->
        <section data-section="next_6"
          class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
          <!-- icon -->
          <div class="mobile w-full flex justify-center items-center">
            <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
              <img loading="lazy" src="/public/assets/images/icons/services/buy/crown.svg" alt="Домой" decoding="async">
            </div>
          </div>
          <!-- text -->
          <div class="flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold font-sans">Выберите тариф</h3>
            <div class="text-center text-white/70">От выбранного тарифа зависит цена на ежемесячную оплату!</div>
          </div>
          <!-- select tarif -->
          <div class="flex flex-col gap-3">
            <!-- inputs -->
            <div class="flex flex-col gap-4 buy">
              <!-- input 1 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">6 Месяцев</h5>
                    <p class="text-white/70 font-light">Тариф MYSELF</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">120</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                    <!-- radio button -->
                    <div class="flex items-center justify-center">
                      <input type="radio" name="subscription" value="6months_1" class="sr-only peer" />
                      <div
                        class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif1.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">1 устройство (для себя)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">720₽</span></p>
              </label>
              <!-- input 2 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">6 Месяцев</h5>
                    <p class="text-white/70 font-light">Тариф Family</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">150</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                    <!-- radio button -->
                    <div class="flex items-center justify-center">
                      <input type="radio" name="subscription" value="6months_4" class="sr-only peer" />
                      <div
                        class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif2.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">4 устройства (для семьи)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">900₽</span></p>
              </label>
              <!-- input 3 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">6 Месяцев</h5>
                    <p class="text-white/70 font-light">Тариф Business</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">180</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                    <!-- radio button -->
                    <div class="flex items-center justify-center">
                      <input type="radio" name="subscription" value="6months_10" class="sr-only peer" />
                      <div
                        class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif3.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">10 устройств (для бизнеса)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">1200₽</span></p>
              </label>
            </div>
            <!-- button next to -->
            <button onclick="return false" data-toggle-section="finish"
              class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
              завершить и купить <i class="fa fa-arrow-right"></i>
            </button>
            <button onclick="return false" data-toggle-section="main"
              class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
              <i class="fa fa-arrow-left"></i> Вернуться назад
            </button>
            <span class="text-center text-white/70 text-sm">Далее будет покупка</span>
          </div>

        </section>

        <!-- на 12 месяцев -->
        <section data-section="next_12"
          class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
          <!-- icon -->
          <div class="mobile w-full flex justify-center items-center">
            <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
              <img loading="lazy" src="/public/assets/images/icons/services/buy/crown.svg" alt="Домой" decoding="async">
            </div>
          </div>
          <!-- text -->
          <div class="flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold font-sans">Выберите тариф</h3>
            <div class="text-center text-white/70">От выбранного тарифа зависит цена на ежемесячную оплату!</div>
          </div>
          <!-- select tarif -->
          <div class="flex flex-col gap-3">
            <!-- inputs -->
            <div class="flex flex-col gap-4 buy">
              <!-- input 1 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">12 Месяцев</h5>
                    <p class="text-white/70 font-light">Тариф MYSELF</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">99</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                    <!-- radio button -->
                    <div class="flex items-center justify-center">
                      <input type="radio" name="subscription" value="12months_1" class="sr-only peer" />
                      <div
                        class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif1.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">1 устройство (для себя)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">1188₽</span></p>
              </label>
              <!-- input 2 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">12 Месяцев</h5>
                    <p class="text-white/70 font-light">Тариф Family</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">120</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                    <!-- radio button -->
                    <div class="flex items-center justify-center">
                      <input type="radio" name="subscription" value="12months_4" class="sr-only peer" />
                      <div
                        class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif2.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">4 устройства (для семьи)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">1800₽</span></p>
              </label>
              <!-- input 3 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">12 Месяцев</h5>
                    <p class="text-white/70 font-light">Тариф Business</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">160</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                    <!-- radio button -->
                    <div class="flex items-center justify-center">
                      <input type="radio" name="subscription" value="12months_10" class="sr-only peer" />
                      <div
                        class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif3.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">10 устройств (для бизнеса)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">2400₽</span></p>
              </label>
            </div>
            <!-- button next to -->
            <button onclick="return false" data-toggle-section="finish"
              class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
              завершить и купить <i class="fa fa-arrow-right"></i>
            </button>
            <button onclick="return false" data-toggle-section="main"
              class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
              <i class="fa fa-arrow-left"></i> Вернуться назад
            </button>
            <span class="text-center text-white/70 text-sm">Далее будет покупка</span>
          </div>

        </section>

        <!--  ОПЛАТА -->
        <section data-section="finish"
          class="hidden overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
          <!-- icon -->
          <div class="mobile w-full flex justify-center items-center">
            <div class="bg_active relative flex items-center justify-center p-3 aspect-square">
              <img loading="lazy" src="/public/assets/images/icons/services/buy/crown.svg" alt="Домой" decoding="async">
            </div>
          </div>
          <!-- text -->
          <div class="flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold font-sans">Завершение</h3>
            <div class="text-center text-white/70">Осталось оплатить собранный вами тариф иначать пользоваться VPN!
            </div>
          </div>
          <!-- select tarif -->
          <div class="flex flex-col gap-4">
            <!-- выбранный тариф -->
            <div class="flex flex-col gap-4 buy">
              <!-- input 1 -->
              <label
                class="flex flex-col gap-2 bg-gradient-to-r from-white/20 to-white/5 bg_active px-6 py-2 rounded-3xl cursor-pointer hover:border-white/40 transition-colors">
                <!-- верхний -->
                <div class="flex justify-between">
                  <!-- titile -->
                  <div class=" flex flex-col justify-center">
                    <h5 class="text-xl font-bold">12 Месяцев</h5>
                    <p class="text-white/70 font-light">Тариф MYSELF</p>
                  </div>
                  <!-- part 2 -->
                  <div class="flex items-center justify-center gap-4">
                    <!-- price -->
                    <div class="flex flex-col text-center">
                      <span class="text-3xl font-bold">99</span>
                      <p class="text-sm">₽/Месяц</p>
                    </div>
                  </div>
                </div>
                <!-- нижний -->
                <div class="relative flex flex-col gap-2 justify-between">
                  <div class="flex">
                    <div class="flex gap-2"><img src="/public/assets/images/icons/services/buy/tarif1.svg" alt="icon1"
                        loading="lazy">
                      <h4 class="text-lg font-bold font-sans uppercase">Количество устройств</h4>
                    </div>
                  </div>
                  <p class="text-white/70 font-light">1 устройство (для себя)</p>
                </div>
                <p class="absolute bottom-2 right-4 text-sm">Итого: <span class="text-white/70">1200₽</span></p>
              </label>
            </div>

            <div class="flex flex-col items-center justify-center">
              <h3 class="text-xl font-bold font-sans">Выберите способ
                оплаты</h3>
              <div class="flex w-full flex-col wrap gap-4 fustify-center items-center mt-4">
                <label
                  class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                  Оплатить через:
                  <div class="flex gap-2 items-center justify-center">
                    <input type="radio" name="payment" value="iomoney" class="sr-only peer" />
                    <img class="h-6" src="/public/assets/images/icons/payment/iomoney.svg" alt="iomoney">
                    <div
                      class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                    </div>
                  </div>
                </label>
                <label
                  class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                  Оплатить через:
                  <div class="flex gap-2 items-center justify-center">
                    <input type="radio" name="payment" value="sber" class="sr-only peer" />
                    <img class="h-6" src=" /public/assets/images/icons/payment/sber-pay-gradient-sign-logo.svg"
                      alt="sber">
                    <div
                      class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                    </div>
                  </div>
                </label>
                <label
                  class="flex w-full font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-between items-center gap-2 p-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                  Оплатить через:
                  <div class="flex gap-2 items-center justify-center">
                    <input type="radio" name="payment" value="sbp" class="sr-only peer" />
                    <img class="h-6" src="/public/assets/images/icons/payment/sbp-logo.svg" alt="sbp">
                    <div
                      class="w-6 h-6 rounded-full border-2 border-white/50 relative peer-checked:after:content-[''] peer-checked:after:block peer-checked:after:absolute peer-checked:after:top-1/2 peer-checked:after:left-1/2 peer-checked:after:-translate-x-1/2 peer-checked:after:-translate-y-1/2 peer-checked:after:w-3.5 peer-checked:after:h-3.5 peer-checked:after:rounded-full peer-checked:after:bg-gradient-to-r peer-checked:after:from-white/50 peer-checked:after:to-white/20 peer-checked:after:animate-pulse">
                    </div>
                  </div>
                </label>
              </div>
            </div>

            <!-- button next to -->
            <button type="button"
              class="payment-submit-btn flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
              завершить и купить <i class="fa-solid fa-cart-shopping"></i>
            </button>

            <a href="/"
              class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
              <i class="fa fa-arrow-left"></i> вернутся на главную
            </a>

          </div>
        </section>

      </div>
    </main>
    <script src="/public/assets/scripts/main/main.js"></script>
    <script src="/public/assets/scripts/theme/main.js"></script>
  </div>
</body>

</html>
