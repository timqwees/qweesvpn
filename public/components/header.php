
<header class="fixed z-50 left-0 top-2 right-0 h-16 px-6 sm:hidden flex items-center justify-between">
    <!-- refresh -->
    <button class="fa fa-refresh text-white cursor-pointer" onclick="window.location.reload()"></button>
    <!-- logo -->
    <div class="flex items-center gap-2">
        <img decoding="async" loading="lazy" data-theme-invert class=" w-auto h-12 object-contain"
            src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
            alt="<?= htmlspecialchars($site['ООО']) ?>">
        <!-- <h2 class="text-white text-xl font-[qwees-poppins-medium] tracking-wider">
            Qwees<span class="text-green-400">VPN</span>
        </h2> -->
    </div>
    <!-- version -->
    <span class="text-white text-sm" data-version>
        <?= $site['versionApp'] ?>
    </span>
</header>