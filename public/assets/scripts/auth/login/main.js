$(document).ready(function () {
  const email = $('#email');
  const message_status = $('#message_status');
  const prefix_message_status = '[Внимание] ';

  //блоки разделов
  const part1 = $('#part1');
  const part2 = $('#part2');
  //кнопки переключения
  const button_email = $('[data-button="email"]');

  //при нажатии на кнопку "ПРОДОЛЖИТЬ"
  button_email.click(function () {
    //первым делом отправляем сообщение на почту в фоновом виде
    $.ajax({
      url: '/auth/mail',
      method: 'POST',
      data: {
        email: email.val()
      },
      success: (data) => {
        const status = JSON.parse(data);//get JSON [****]
        // событие написания в поле верефикации
        $('#verefy').on('input', function () {
          if ($('#verefy').val() == status) {//true - reight
            $('#verefy_status').addClass('hidden');
            $('#verefy').removeClass('border-red-500');
            $('#verefy').addClass('border-green-500');
            $('[data-button="verefy"]').removeAttr('disabled');
          } else {//false - no right
            $('#verefy_status').removeClass('hidden');
            $('#verefy').addClass('border-red-500');
            $('#verefy').removeClass('border-green-500');
            $('#verefy_status').text(prefix_message_status + 'Неверный код верефикации');
            $('[data-button="verefy"]').attr('disabled', 'disabled');
          }
        });
      },
      error: (error) => {
        message_status.removeClass('hidden');
        setBorderState($(this), false);
        message_status.text(prefix_message_status + 'Ошибка: ' + error);
        $(this).addClass('border-red-500');
        button_email.attr('disabled', 'disabled');
      }
    });
    //закрываем окно
    part1.animate({
      height: 'toggle'
    });
    //открываем окно через 1с
    setTimeout(() => {
      part2.animate({
        height: 'toggle'
      });
    }, 1000);
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
            button_email.removeAttr('disabled');
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
