<?php declare(strict_types=1);
use Setting\Route\Function\Controllers\kassa\Kassa;
use Setting\Route\Function\Controllers\Auth\Auth;
use App\Config\Session;

Auth::auth();
$paymentId = Session::init('kassa')['payment_id'] ?? null;
$paymentStatus = [
    'success' => false,
    'status' => 'unknown',
    'paid' => false,
    'error' => null
];

if ($paymentId) {
    $paymentStatus = (new Kassa())->startPaymentStatus($paymentId);//DB SEND + KEY VPN
    Session::init('kassa', null);

    if ($paymentStatus['subscription_issued'] ?? false) {
        $subscriptionInfo = [
            'issued' => true,
            'days' => $paymentStatus['subscription_days'] ?? 0,
            'devices' => $paymentStatus['subscription_devices'] ?? 1,
            'end_date' => $paymentStatus['subscription_end_date'] ?? ''
        ];
    } else {
        $subscriptionInfo = [
            'issued' => false,
            'error' => $paymentStatus['subscription_error'] ?? null
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ru" class="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $paymentStatus['paid'] ? 'Оплата успешна' : 'Статус оплаты'; ?></title>

    <!-- Preload critical resources -->
    <link rel="preload" href="/public/assets/styles/style.css" as="style">
    <link rel="preload" href="/public/assets/images/icons/logo/qweesvpn.svg" as="image" type="image/svg+xml">

    <!-- Critical CSS with onload optimization -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" as="style"
        crossorigin="anonymous" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
            crossorigin="anonymous">
    </noscript>

    <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet">
    </noscript>

    <link rel="stylesheet" href="/public/assets/styles/style.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/style.css">
    </noscript>

    <!-- Deferred scripts -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Noscript fallback -->
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/noscript.css">
    </noscript>

    <?php if ($paymentStatus['status'] === 'pending'): ?>
        <meta http-equiv="refresh" content="10">
    <?php endif; ?>
</head>

<body class="bg-black bg-no-repeat flex item-center w-full overflow-x-hidden">
    <div class="min-h-screen flex flex-col w-full container mx-auto">
        <!-- navbar top -->
        <header class="fixed z-50 left-0 top-2 right-0 h-16 px-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img class="w-auto h-7 object-contain" src="/public/assets/images/icons/logo/qweesvpn.svg"
                    alt="qweesvpn">
                <h2 class="text-white text-xl font-[qwees-poppins-medium] tracking-wider">QWEES <span
                        class="text-green-400">VPN</span></h2>
            </div>
            <span class="text-white text-sm">v1.0.0</span>
        </header>

        <main class="flex sm:my-2 w-full">
            <div class="w-full text-white">
                <section
                    class="lg:px-64 overflow-hidden relative flex flex-col gap-6 justify-center pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
                    <!-- success icon -->
                    <div class="w-full flex justify-center items-center">
                        <div
                            class="<?php echo $paymentStatus['paid'] ? 'bg-green-500/20' : ($paymentStatus['status'] === 'canceled' ? 'bg-red-500/20' : ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid'] ? 'bg-blue-500/20' : 'bg-yellow-500/20')); ?> relative flex items-center justify-center p-6 aspect-square rounded-full">
                            <i
                                class="fas <?php echo $paymentStatus['paid'] ? 'fa-check text-green-400' : ($paymentStatus['status'] === 'canceled' ? 'fa-times text-red-400' : ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid'] ? 'fa-spinner fa-spin text-blue-400' : 'fa-clock text-yellow-400')); ?> text-4xl"></i>
                        </div>
                    </div>

                    <!-- success message -->
                    <div class="flex flex-col items-center justify-center text-center">
                        <h3 class="text-2xl font-bold font-sans mb-2">
                            <?php
                            if ($paymentStatus['paid']) {
                                echo 'Оплата прошла успешно!';
                            } elseif ($paymentStatus['status'] === 'pending') {
                                echo 'Оплата в обработке';
                            } elseif ($paymentStatus['status'] === 'canceled') {
                                echo 'Оплата отменена';
                            } elseif ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid']) {
                                echo 'Оплата подтверждена, активация...';
                            } else {
                                echo 'Проверка статуса платежа';
                            }
                            ?>
                        </h3>
                        <div class="text-white/70">
                            <?php
                            if ($paymentStatus['paid']) {
                                echo 'Ваша подписка активирована';
                            } elseif ($paymentStatus['status'] === 'pending') {
                                echo 'Платеж обрабатывается, это может занять несколько минут';
                            } elseif ($paymentStatus['status'] === 'canceled') {
                                echo 'Платеж не был завершен. Вы можете попробовать снова';
                            } elseif ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid']) {
                                echo 'Платеж успешно подтвержден, активация VPN в процессе...';
                            } else {
                                echo 'Проверяем статус вашего платежа...';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- payment info -->
                    <div class="bg-white/10 rounded-2xl p-4 mb-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-white/70">ID платежа:</span>
                            <span
                                id="payment-id text-end"><?php echo htmlspecialchars($paymentId ?? 'TEST_PAYMENT'); ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-white/70">Статус:</span>
                            <span
                                class="<?php echo $paymentStatus['paid'] ? 'text-green-400' : ($paymentStatus['status'] === 'canceled' ? 'text-red-400' : ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid'] ? 'text-blue-400' : 'text-yellow-400')); ?>">
                                <?php
                                if ($paymentStatus['paid']) {
                                    echo 'Оплачено';
                                } elseif ($paymentStatus['status'] === 'pending') {
                                    echo 'В обработке';
                                } elseif ($paymentStatus['status'] === 'canceled') {
                                    echo 'Отменено';
                                } elseif ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid']) {
                                    echo 'Активация...';
                                } else {
                                    echo 'Проверка...';
                                }
                                ?>
                            </span>
                        </div>
                        <?php if ($paymentStatus['success']): ?>
                            <div class="flex justify-between mb-2">
                                <span class="text-white/70">Сумма:</span>
                                <span><?php echo htmlspecialchars((string) ($paymentStatus['amount'] ?? '0')); ?>
                                    <?php echo htmlspecialchars($paymentStatus['currency'] ?? 'RUB'); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span class="text-white/70">Активация:</span>
                            <span><?php echo $paymentStatus['paid'] ? 'Мгновенно' : 'После оплаты'; ?></span>
                        </div>
                    </div>

                    <!-- subscription info -->
                    <?php if ($paymentStatus['paid'] && isset($subscriptionInfo)): ?>
                        <div class="bg-green-500/10 border border-green-500/30 rounded-2xl p-4 mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <i class="fas fa-shield-alt text-green-400"></i>
                                <h4 class="text-green-400 font-bold">Информация о подписке</h4>
                            </div>

                            <?php if ($subscriptionInfo['issued']): ?>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-white/70">Статус подписки:</span>
                                        <span class="text-green-400 font-medium">Активна</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/70">Длительность:</span>
                                        <span><?php echo htmlspecialchars((string) $subscriptionInfo['days']); ?> дней</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/70">Действует до:</span>
                                        <span><?php echo htmlspecialchars(date('d.m.Y', strtotime($subscriptionInfo['end_date']))); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/70">Устройств:</span>
                                        <span><?php echo htmlspecialchars((string) $subscriptionInfo['devices']); ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-red-400 text-sm">
                                    <?php if ($subscriptionInfo['error']): ?>
                                        Ошибка активации: <?php echo htmlspecialchars($subscriptionInfo['error']); ?>
                                        <div class="text-yellow-400 text-xs mt-2">
                                            Ваш платеж успешно обработан, но возникла проблема с созданием VPN-клиента.
                                            Пожалуйста, свяжитесь с поддержкой для активации подписки.
                                        </div>
                                    <?php else: ?>
                                        Подписка активируется в течение нескольких минут...
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- action buttons -->
                    <div class="flex flex-col gap-4">
                        <?php if ($paymentStatus['status'] === 'pending' || ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid'])): ?>
                            <button onclick="location.reload()"
                                class="flex font-bold bg-gradient-to-r from-yellow-500/20 to-yellow-500/5 border border-yellow-500/30 justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-yellow-500/50 transition-colors">
                                <i class="fa fa-refresh"></i> Обновить статус
                            </button>
                        <?php endif; ?>

                        <?php if ($paymentStatus['status'] === 'canceled' || !$paymentStatus['success']): ?>
                            <a href="/public/pages/pay"
                                class="flex font-bold bg-gradient-to-r from-red-500/20 to-red-500/5 border border-red-500/30 justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-red-500/50 transition-colors">
                                <i class="fa fa-credit-card"></i> Попробовать снова
                            </a>
                        <?php endif; ?>

                        <a href="/"
                            class="flex font-bold bg-gradient-to-r from-green-500/20 to-green-500/5 border border-green-500/30 justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-green-500/50 transition-colors">
                            <i class="fa fa-home"></i> На главную
                        </a>
                        <a href="/public/pages/profile"
                            class="flex font-bold bg-gradient-to-r from-white/10 to-white/5 bg_active justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-white/40 transition-colors">
                            <i class="fa fa-user"></i> Мой профиль
                        </a>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>

</html>