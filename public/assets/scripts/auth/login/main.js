$(document).ready(function () {
  const email = $('#email');
  const message_status = $('#message_status');
  const prefix_message_status = '[Внимание] ';
  function setBorderState($el, ok) {
    if (ok) $el.removeClass('border-red-500').addClass('border-green-500');
    else $el.addClass('border-red-500').removeClass('border-green-500');
  }

  //блоки разделов
  const part1 = $('#part1');
  const part2 = $('#part2');
  //кнопки переключения
  const button_email = $('[data-button="email"]');

  let expectedCode = null;
  let mailSent = false;

  function checkVerefyAndAutoSubmit() {
    if (!expectedCode) return;
    let raw = String($('#verefy').val() || '');
    let val = raw.replace(/\D/g, '').slice(0, 4);
    if (raw !== val) $('#verefy').val(val);
    const expected = String(expectedCode).replace(/\D/g, '').slice(0, 4);
    if (val === expected && val.length === 4) {
      $('#verefy_status').addClass('hidden');
      $('#verefy').removeClass('border-red-500').addClass('border-green-500');
      const $btn = $('[data-button="verefy"]');
      $btn.removeAttr('disabled');
      // при автоматической вставке сразу воспринимаем как нажатие "Войти"
      setTimeout(() => {
        if (!$btn.is(':disabled')) {
          // эквивалент клика по кнопке войти — сабмитим форму
          $btn.closest('form').submit();
        }
      }, 120);
    } else if (val.length === 0) {
      $('#verefy_status').addClass('hidden');
      $('#verefy').removeClass('border-red-500 border-green-500');
      $('[data-button="verefy"]').attr('disabled', 'disabled');
    } else {
      $('#verefy_status').removeClass('hidden');
      $('#verefy').addClass('border-red-500').removeClass('border-green-500');
      $('#verefy_status').text(prefix_message_status + 'Неверный код верефикации');
      $('[data-button="verefy"]').attr('disabled', 'disabled');
    }
  }

  function bindVerefyHandlers() {
    const $verefy = $('#verefy');
    $verefy.off('input paste change').on('input paste change', function () {
      // paste/input — проверяем после вставки
      setTimeout(checkVerefyAndAutoSubmit, 0);
    });
    $verefy.off('paste.auto').on('paste.auto', function (e) {
      const clipboard = (e.originalEvent.clipboardData || window.clipboardData);
      if (clipboard) {
        const pasted = clipboard.getData('text') || '';
        const digits = (pasted.match(/\d/g) || []).join('').slice(0, 4);
        if (digits.length === 4) {
          e.preventDefault();
          $(this).val(digits);
          setTimeout(checkVerefyAndAutoSubmit, 0);
        }
      }
    });
    // OTP autofill (WebOTP) может не триггерить input — поллим
    let pollCount = 0;
    const poll = setInterval(() => {
      if ($('#verefy').val() && $('#verefy').val().length >= 4) {
        checkVerefyAndAutoSubmit();
        clearInterval(poll);
      }
      if (pollCount++ > 20) clearInterval(poll);
    }, 300);
    setTimeout(checkVerefyAndAutoSubmit, 150);
  }

  //при нажатии на кнопку "ПРОДОЛЖИТЬ"
  button_email.click(function () {
    if (mailSent) return;
    if (button_email.is(':disabled')) return;
    mailSent = true;
    button_email.attr('disabled', 'disabled');
    //первым делом отправляем сообщение на почту в фоновом виде
    $.ajax({
      url: '/auth/mail',
      method: 'POST',
      data: {
        email: email.val()
      },
      success: (data) => {
        let status;
        try {
          status = JSON.parse(data);
        } catch (e) {
          status = {};
        }
        // поддержка обоих форматов: {code:1234} или {success:true,code:1234}
        expectedCode = status.code ?? status ?? null;
        if (status.success === false) {
          message_status.removeClass('hidden');
          message_status.text(prefix_message_status + 'Ошибка отправки почты: ' + (status.error || 'Неизвестная ошибка'));
          // возвращаем возможность повторить
          mailSent = false;
          button_email.removeAttr('disabled');
          part1.stop(true, true).slideDown(300);
          part2.stop(true, true).slideUp(300, () => part2.addClass('hidden'));
          return;
        }
        bindVerefyHandlers();
      },
      error: (error) => {
        message_status.removeClass('hidden');
        setBorderState($(this), false);
        message_status.text(prefix_message_status + 'Ошибка: ' + error);
        $(this).addClass('border-red-500');
        mailSent = false;
        button_email.removeAttr('disabled');
        part1.stop(true, true).slideDown(300);
        part2.stop(true, true).slideUp(300, () => part2.addClass('hidden'));
      }
    });
    //закрываем окно — детерминированно, без toggle чтобы не открылось снова при автоподстановке
    part1.stop(true, true).slideUp(300);
    setTimeout(() => {
      part2.removeClass('hidden').hide().slideDown(300);
      setTimeout(() => $('#verefy').focus(), 350);
    }, 350);
  });

  // проверка всех полей только в #part1
  $('#part1 input').on('input', function () {
    if ($(this).val() === '') {
      message_status.removeClass('hidden');
      message_status.text(prefix_message_status + 'Заполните поле почты');
      $(this).addClass('border-red-500');
      button_email.attr('disabled', 'disabled');
    } else if (!$(this).val().includes('@')) {
      message_status.removeClass('hidden');
      message_status.text(prefix_message_status + 'Укажите обязательно @');
      $(this).addClass('border-red-500');
      button_email.attr('disabled', 'disabled');
    } else if (!/^[\w._]+@[\w.-]+\.[\w]{2,}$/.test($(this).val())) {
      message_status.removeClass('hidden');
      message_status.text(prefix_message_status + 'Некорректный формат почты');
      $(this).addClass('border-red-500');
      button_email.attr('disabled', 'disabled');
    } else {
      $.ajax({
        url: '/auth/find',
        method: 'POST',
        data: {//send $_POST['email']
          email: $(this).val()
        },
        success: (data) => {
          const status = JSON.parse(data);//get JSON [true|false]
          if (status) {//true - find
            message_status.addClass('hidden');
            $(this).removeClass('border-red-500');
            $(this).addClass('border-green-500');
            if (!mailSent) button_email.removeAttr('disabled');
          } else {//false - not find
            message_status.removeClass('hidden');
            $(this).addClass('border-red-500');
            $(this).removeClass('border-green-500');
            message_status.html(prefix_message_status + '<span class="text-red-500">' + email.val() + '</span> не зарегестрирован!');
            button_email.attr('disabled', 'disabled');
          }
        },
        error: (error) => {
          message_status.removeClass('hidden');
          setBorderState($(this), false);
          message_status.text(prefix_message_status + 'Ошибка: ' + error);
          $(this).addClass('border-red-500');
          button_email.attr('disabled', 'disabled');
        }
      });
    }
  });
});
