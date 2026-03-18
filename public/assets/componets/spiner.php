  <style>
    @keyframes slideInFromBottom {
      0% {
        transform: translateY(-5vh);
        opacity: 0;
      }

      50% {
        transform: translateY(0vh);
        opacity: 1;
      }

      100% {
        transform: translateY(-100vh);
        opacity: 0;
      }
    }

    .slide-in-bottom {
      opacity: 0;
      animation: slideInFromBottom 2.5s ease-in infinite;
    }
  </style>
  
  <div id="spiner-card"
    class="absolute inset-0 z-50 bg-black h-full w-full overflow-hidden w-full flex gap-2 justify-between itmems-center">

    <img class="slide-in-bottom" src="/public/assets/image/1.svg" alt="line 1">
    <img class="slide-in-bottom" src="/public/assets/image/2.svg" alt="line 2">
    <img class="slide-in-bottom" src="/public/assets/image/1.svg" alt="line 1">
    <img class="slide-in-bottom" src="/public/assets/image/2.svg" alt="line 2">
    <img class="slide-in-bottom" src="/public/assets/image/1.svg" alt="line 1">
    <img class="slide-in-bottom" src="/public/assets/image/2.svg" alt="line 2">

  </div>