<?php declare(strict_types=1);
use Setting\Route\Function\Controllers\Kassa\Kassa;
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\Language\Language;
use Setting\Route\Function\Functions;
use App\Config\Session;
$site = Functions::site();
$currentLanguage = Language::getCurrent();
$translations = Language::getTranslations($currentLanguage);
$t = fn(string $key): string => $translations[$key] ?? $key;

Auth::auth();
$paymentId = Session::init('kassa')['payment_id'] ?? null;
$paymentStatus = [
    'success' => false,
    'status' => 'unknown',
    'paid' => false,
    'error' => null
];//подготовили данные по умолчанию

if ($paymentId) {//true - this is object
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
<html lang="<?= $currentLanguage ?>" class="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $t($paymentStatus['paid'] ? 'pay_success' : 'pay_status_title') ?></title>

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

        <?php include_once 'public/components/header.php' ?>

        <main class="rounded-xl card flex sm:my-2 w-full">
            <div class="w-full text-white setka">
                <section
                    class="lg:px-64 overflow-hidden relative flex flex-col gap-6 justify-center pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4">
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
                                echo $t('pay_ok');
                            } elseif ($paymentStatus['status'] === 'pending') {
                                echo $t('pay_pending');
                            } elseif ($paymentStatus['status'] === 'canceled') {
                                echo $t('pay_canceled');
                            } elseif ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid']) {
                                echo $t('pay_activating');
                            } else {
                                echo $t('check_status');
                            }
                            ?>
                        </h3>
                        <div class="text-white/70">
                            <?php
                            if ($paymentStatus['paid']) {
                                echo $t('sub_active');
                            } elseif ($paymentStatus['status'] === 'pending') {
                                echo $t('sub_processing');
                            } elseif ($paymentStatus['status'] === 'canceled') {
                                echo $t('sub_not_completed');
                            } elseif ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid']) {
                                echo $t('sub_confirm_activating');
                            } else {
                                echo $t('checking_status');
                            }
                            ?>
                        </div>
                    </div>

                    <!-- payment info -->
                    <div class="bg-white/10 rounded-2xl p-4 mb-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-white/70"><?= $t('payment_id') ?></span>
                            <span
                                id="payment-id text-end"><?php echo htmlspecialchars($paymentId ?? 'TEST_PAYMENT'); ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-white/70"><?= $t('status_colon') ?></span>
                            <span
                                class="<?php echo $paymentStatus['paid'] ? 'text-green-400' : ($paymentStatus['status'] === 'canceled' ? 'text-red-400' : ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid'] ? 'text-blue-400' : 'text-yellow-400')); ?>">
                                <?php
                                if ($paymentStatus['paid']) {
                                    echo $t('paid');
                                } elseif ($paymentStatus['status'] === 'pending') {
                                    echo $t('in_process');
                                } elseif ($paymentStatus['status'] === 'canceled') {
                                    echo $t('canceled');
                                } elseif ($paymentStatus['status'] === 'succeeded' && !$paymentStatus['paid']) {
                                    echo $t('activating');
                                } else {
                                    echo $t('checking');
                                }
                                ?>
                            </span>
                        </div>
                        <?php if ($paymentStatus['success']): ?>
                            <div class="flex justify-between mb-2">
                                <span class="text-white/70"><?= $t('amount_colon') ?></span>
                                <span><?php echo htmlspecialchars((string) ($paymentStatus['amount'] ?? '0')); ?>
                                    <?php echo htmlspecialchars($paymentStatus['currency'] ?? 'RUB'); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span class="text-white/70"><?= $t('activation_colon') ?></span>
                            <span><?php echo $paymentStatus['paid'] ? $t('instantly') : $t('after_payment'); ?></span>
                        </div>
                    </div>

                    <!-- subscription info -->
                    <?php if ($paymentStatus['paid'] && isset($subscriptionInfo)): ?>
                        <div class="bg-green-500/10 border border-green-500/30 rounded-2xl p-4 mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <i class="fas fa-shield-alt text-green-400"></i>
                                <h4 class="text-green-400 font-bold"><?= $t('sub_info') ?></h4>
                            </div>

                            <?php if ($subscriptionInfo['issued']): ?>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-white/70"><?= $t('sub_status') ?></span>
                                        <span class="text-green-400 font-medium"><?= $t('active_fem') ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/70"><?= $t('duration_colon') ?></span>
                                        <span><?php echo htmlspecialchars((string) $subscriptionInfo['days']); ?> <?= $t('days') ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/70"><?= $t('valid_until') ?></span>
                                        <span><?php echo htmlspecialchars((int) $subscriptionInfo['end_date'] > 0 ? date('d.m.Y', (int) $subscriptionInfo['end_date'] / 1000) : ''); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/70"><?= $t('devices_colon') ?></span>
                                        <span><?php echo htmlspecialchars((string) $subscriptionInfo['devices']); ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-red-400 text-sm">
                                    <?php if ($subscriptionInfo['error']): ?>
                                        <?= $t('activation_error') ?><?php echo htmlspecialchars($subscriptionInfo['error']); ?>
                                        <div class="text-yellow-400 text-xs mt-2">
                                            <?= $t('vpn_client_error') ?>
                                            <?= $t('contact_support') ?>
                                        </div>
                                    <?php else: ?>
                                        <?= $t('activating_minutes') ?>
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
                                <i class="fa fa-refresh"></i> <?= $t('refresh_status') ?>
                            </button>
                        <?php endif; ?>

                        <?php if ($paymentStatus['status'] === 'canceled' || !$paymentStatus['success']): ?>
                            <a href="/pay"
                                class="flex font-bold bg-gradient-to-r from-red-500/20 to-red-500/5 border border-red-500/30 justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-red-500/50 transition-colors">
                                <i class="fa fa-credit-card"></i> <?= $t('try_again') ?>
                            </a>
                        <?php endif; ?>

                        <a href="/"
                            class="flex font-bold bg-gradient-to-r from-green-500/20 to-green-500/5 border border-green-500/30 justify-center items-center gap-2 px-6 py-4 rounded-full cursor-pointer hover:border-green-500/50 transition-colors">
                            <i class="fa fa-home"></i> <?= $t('go_home') ?>
                        </a>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>

</html>