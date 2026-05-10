$(document).ready(function () {
    const email = $('#email');
    const message_status = $('#message_status');
    const prefix_message_status = '[Внимание] ';

    // проверка всех полей только в #part1
    $('#form_admin_add_user input').on('input', function () {
        if ($('#first_name').val() === '' || $('#last_name').val() === '' || email.val() === '') {
            message_status.removeClass('hidden');
            message_status.addClass('text-red-400');
            message_status.text(prefix_message_status + 'Не все поля заполнены!');
            button_email.attr('disabled', 'disabled');
        } else if (!email.val().includes('@')) {
            message_status.removeClass('hidden');
            message_status.addClass('text-red-400');
            message_status.text(prefix_message_status + 'Укажите обязательно @');
            email.addClass('border-red-500');
            button_email.attr('disabled', 'disabled');
        } else if (!/^[\w._]+@[\w.-]+\.[\w]{2,}$/.test(email.val())) {
            message_status.removeClass('hidden');
            message_status.addClass('text-red-400');
            message_status.text(prefix_message_status + 'Некорректный формат почты');
            email.addClass('border-red-500');
            button_email.attr('disabled', 'disabled');
        } else {
            $.ajax({
                url: '/auth/find',
                method: 'POST',
                data: {//send $_POST['email']
                    email: email.val()
                },
                success: (data) => {
                    const status = JSON.parse(data);//get JSON [true|false]
                    if (!status) {//false - not find
                        message_status.addClass('hidden');
                        email.removeClass('border-red-500');
                        email.addClass('border-green-500');
                        button_email.removeAttr('disabled');
                    } else {//true - find
                        message_status.removeClass('hidden');
                        email.addClass('border-red-500');
                        email.removeClass('border-green-500');
                        message_status.html(prefix_message_status + '<span class="text-red-500">' + email.val() + '</span> уже зарегестрирован!');
                        button_email.attr('disabled', 'disabled');
                    }
                },
                error: (error) => {
                    message_status.removeClass('hidden');
                    setBorderState($(this), false);
                    message_status.text(prefix_message_status + 'Ошибка: ' + error);
                    email.addClass('border-red-500');
                    button_email.attr('disabled', 'disabled');
                }
            });
        }
    });

    // Subscription toggle - show/hide subscription details
    const subscriptionSelect = $('#subscription');
    const subscriptionDetails = $('#subscription-details');
    const durationDays = $('#duration_days');

    function toggleSubscriptionFields() {
        if (subscriptionSelect.val() === '') {
            subscriptionDetails.slideUp(200);
        } else {
            subscriptionDetails.slideDown(200);
        }
    }

    subscriptionSelect.on('change', toggleSubscriptionFields);
    toggleSubscriptionFields(); // Initial check

    // Validation: ensure duration_days >= 1 when subscription selected
    $('#form_admin_add_user').on('submit', function (e) {
        if (subscriptionSelect.val() !== '') {
            const days = parseInt(durationDays.val());
            if (!days || days < 1) {
                e.preventDefault();
                alert('Укажите корректную длительность подписки (минимум 1 день)');
                durationDays.focus();
                return false;
            }
        }
    });
});