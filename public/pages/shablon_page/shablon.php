<?php
use Setting\Route\Function\Controllers\Auth\Auth;
Auth::auth();
?>
<!DOCTYPE html>
<html lang="ru">

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
        <!-- содержание -->
      </div>

      <!-- ################# CONTENT MOBILE ####################-->
      <div class="sm:hidden w-full text-white">
        <section
          class="overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh] px-4 bg-gradient-to-t from-black via-green-950 to-black">
          <!-- содержание -->
        </section>
      </div>

    </main>
    <script src="/public/assets/scripts/main/main.js"></script>
  </div>
</body>

</html>
