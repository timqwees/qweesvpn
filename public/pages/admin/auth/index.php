<?php
use Setting\Route\Function\Functions;
use App\Config\Session;
$site = Functions::site();
$adminSession = Session::init('admin');
if (is_array($adminSession) && isset($adminSession['auth']) && $adminSession['auth'] === true) {
    \App\Models\Network\Network::onRedirect('/admin');
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Авторизация</title>

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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Noscript fallback -->
    <noscript>
        <link rel="stylesheet" href="/public/assets/styles/noscript.css">
    </noscript>
</head>

<body class="bg-black flex item-center justify-center">
    <div class="min-h-screen flex flex-col justify-center items-center container">

        <!-- LOGO CONTENT -->
        <div class="flex flex-col justify-center items-center gap-2">
            <img decoding="async" loading="lazy"
                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                alt="<?= htmlspecialchars($site['ООО']) ?>" loading="lazy">
            <h2 class="text-white text-3xl font-[qwees-urbanist-medium] tracking-wider">
                Qwees<span class="text-green-400">VPN</span>
            </h2>
            <p class="text-white text-sm font-sans letter-specing">Вход в администрацию!</p>
        </div>

        <!-- FORM CONTENT -->
        <div
            class="flex flex-col bg-[#151414] rounded-lg p-6 py-10 w-full max-w-[376px] border border-solid border-[0.5px] border-white/20 my-8 gap-4">

            <form action="/admin/login" method="POST">

                <div class="flex flex-col gap-4 w-full" id="part1">
                    <!-- INPUT = email -->
                    <label for="username" class="text-white/70 text-2xl font-sans">Имя пользователя</label>
                    <div class="relative flex">
                        <i class="fa fas fa-user text-white absolute left-5 top-[35%]"></i>
                        <input type="username" name="username" placeholder="your_username" required
                            class="!bg-transparent w-full text-white rounded-2xl p-3 border border-solid border-white/20 bg-transparent pl-14 text-lg font-[qwees-poppins-regular] outline-none">
                    </div>
                    <label for="password" class="text-white/70 text-2xl font-sans">Пароль</label>
                    <div class="relative flex">
                        <i class="fa fas fa-lock text-white absolute left-5 top-[35%]"></i>
                        <input type="password" name="password" placeholder="your_password" required
                            class="!bg-transparent w-full text-white rounded-2xl p-3 border border-solid border-white/20 bg-transparent pl-14 text-lg font-[qwees-poppins-regular] outline-none">
                    </div>
                    <!-- BUTTON -->
                    <button class="bg-[#6BFF5B] p-2 text-center rounded-2xl font-sans">Войти</button>

                </div>

            </form>

        </div>

    </div>

    <script>
        <?php
        $message = $_GET['error'] ?? null;
        if (isset($message))
            echo "showNotification('" . addslashes($message) . "');";
        ?>
        function showNotification(msg) {
            let container = ((newContainer = document.createElement('div')) => (newContainer.id = 'notification-container', newContainer.className = 'fixed right-2 top-2 z-[999] flex flex-col gap-2', document.body.appendChild(newContainer), newContainer))();
            const element = container.appendChild(document.createElement('div'));
            element.className = `px-6 py-3 rounded-lg text-white z-50 transform translate-x-full transition-transform duration-300 bg-red-500`;
            element.innerHTML = '<i class="fa-solid fa-info-circle"></i> ' + msg;
            setTimeout(() => element.classList.remove('translate-x-full'), 100);
            setTimeout(() => element.classList.add('translate-x-full'), 4100);
            setTimeout(() => (element.remove(), container.children.length || container.remove()), 4400);
        }
    </script>
</body>

</html>