<?php
use Setting\Route\Function\Controllers\Auth\Auth;
use Setting\Route\Function\Controllers\Language\Language;
use Setting\Route\Function\Functions;
Auth::auth();
$site = Functions::site();
$currentLanguage = Language::getCurrent();
$translations = Language::getTranslations($currentLanguage);
$t = fn(string $key): string => $translations[$key] ?? $key;
?>
<!DOCTYPE html>
<html lang="<?= $currentLanguage ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $t('install') ?></title>
    <!-- fonts + tailwind + normalize + styles + JQuary -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" defer />
    <link href="https://unpkg.com/@csstools/normalize.css" rel="stylesheet" />
    <link rel="stylesheet" href="/public/assets/styles/style.css" defer>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!--  -->
</head>

<body class="bg-black bg-no-repeat flex item-center w-full overflow-hidden">
    <div class="min-h-screen flex flex-col w-full mx-auto">

        <?php include_once 'public/components/header.php' ?>

        <main class="card flex sm:my-2 w-full">
            <!-- КОНЕЦ БЕЗ ИЗМЕНЕНИЙ -->

            <!-- ################# CONTENT DESCKTOP ####################-->
            <div class="setka hidden sm:block w-full text-white">
                <!-- main -->
                <section data-section="main"
                    class="rounded-3xl overflow-hidden relative min-h-[100dvh] flex flex-col gap-2 justify-between box-border">
                    <div
                        class="absolute inset-0 z-0 bg-gradient-to-br from-green-900/15 via-transparent to-emerald-900/8">
                    </div>
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        class="absolute top-0 bottom-0 mx-auto w-full h-full opacity-70 z-0"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/light.svg" alt="backgroud">

                    <!-- text -->
                    <div
                        class="px-4 pt-[15%] mx-auto right-0 left-0 flex flex-col justify-center items-center gap-3 z-10">
                        <div class="p-6 bg-[#181818] aspect-square rounded-[30px]">
                            <img decoding="async" loading="lazy" class="w-24"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg" alt="logo">
                        </div>
                        <!-- <h3 class="font-[qwees-urbanist-regular] text-2xl"><?= htmlspecialchars($site['ООО']) ?> </h3> -->
                        <p class="text-sm text-center w-[70%] break-world"><?= $t('start_install_desc') ?>
                            <span class="text-green-200 text-lg">
                                <?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['os']) ?></span>
                        </p>
                    </div>

                    <div class="px-4 flex flex-col gap-4 mb-6 justify-center items-center w-full z-10">
                        <button data-toggle-section="start"
                            class="btn_install_page_tour max-w-[50%] bg-white cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-3 w-[90%] items-center gap-2">
                            <?= $t('start_install') ?>
                        </button>
                        <button onclick="window.open('/', '_self')"
                            class="max-w-[50%] bg-transparent border-white border text-white cursor-pointer flex justify-center text-lg rounded-xl flex p-3 w-[90%] items-center gap-4">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                fill="#fff" version="1.1" id="Capa_1" width="20px" height="20px"
                                viewBox="0 0 528.919 528.919" xml:space="preserve">
                                <g>
                                    <g>
                                        <path
                                            d="M518.946,196.148c-5.836-19.461-14.184-38.143-24.813-55.521l-39.156,23.954c8.571,14.012,15.301,29.067,20.004,44.75    L518.946,196.148z" />
                                        <path
                                            d="M492.208,391.4l-38.785-24.547c-8.771,13.855-19.309,26.566-31.319,37.781l31.322,33.553    C468.297,424.302,481.345,408.562,492.208,391.4z" />
                                        <path
                                            d="M524.559,309.989l-45.062-8.745c-3.125,16.096-8.326,31.744-15.463,46.512l41.329,19.97    C514.222,349.396,520.682,329.971,524.559,309.989z" />
                                        <path
                                            d="M362.513,442.524l17.057,42.613c18.847-7.546,36.698-17.516,53.061-29.64l-27.326-36.882    C392.106,428.399,377.706,436.44,362.513,442.524z" />
                                        <path
                                            d="M528.919,264.472c0-14.085-1.242-28.207-3.696-41.968l-45.188,8.06c1.979,11.108,2.986,22.516,2.983,33.908    c0,5.046-0.195,10.135-0.587,15.123l45.759,3.577C528.674,276.996,528.919,270.705,528.919,264.472z" />
                                        <path
                                            d="M341.818,449.461c-15.753,4.29-32.1,6.545-48.59,6.708l0.446,45.896c20.41-0.199,40.665-2.996,60.203-8.317    L341.818,449.461z" />
                                        <path
                                            d="M70.846,324.068c3.21,3.926,8.409,3.926,11.619,0l69.162-84.621c3.21-3.926,1.698-7.108-3.372-7.108h-45.903    c0.364-2.148,0.716-4.299,1.151-6.423c0.006-0.028,0.012-0.059,0.018-0.086c0.566-2.769,1.218-5.514,1.903-8.253    c9.345-37.026,29.48-69.805,56.864-94.771c0.159-0.144,0.321-0.288,0.48-0.435c1.661-1.505,3.351-2.98,5.064-4.428    c5.141-4.339,10.511-8.415,16.093-12.204c0.128-0.085,0.254-0.177,0.382-0.263c27.929-18.859,61.068-30.551,96.763-32.439    c0.468-0.024,0.936-0.034,1.404-0.055c2.473-0.11,4.951-0.172,7.433-0.19c0.719-0.006,1.438-0.021,2.157-0.019    c2.567,0.009,5.126,0.077,7.675,0.184c0.908,0.04,1.812,0.095,2.717,0.147c1.998,0.113,3.99,0.26,5.982,0.435    c0.771,0.067,1.546,0.123,2.316,0.199c2.564,0.257,5.116,0.572,7.656,0.93c0.808,0.113,1.612,0.242,2.417,0.367    c8.256,1.27,16.381,3.072,24.337,5.389c1.202,0.352,2.398,0.729,3.592,1.102c1.261,0.395,2.516,0.805,3.768,1.227    c2.074,0.697,4.131,1.435,6.178,2.203c1.169,0.438,2.341,0.869,3.501,1.331c1.478,0.587,2.943,1.208,4.406,1.833    c0.826,0.352,1.649,0.716,2.47,1.08c2.968,1.319,5.905,2.711,8.807,4.18c0.33,0.168,0.667,0.325,0.997,0.496    c1.444,0.744,2.873,1.518,4.3,2.298c0.826,0.453,1.646,0.912,2.463,1.377c1.315,0.744,2.632,1.487,3.929,2.265l0.004-0.006    c6.624,3.969,13.017,8.375,19.17,13.164c2.394,1.861,4.743,3.782,7.057,5.762c0.637,0.544,1.285,1.071,1.916,1.625    c2.387,2.096,4.722,4.266,7.02,6.49c6.371,6.172,12.329,12.766,17.794,19.768l36.182-28.241    c-6.436-8.244-13.415-16.047-20.863-23.37c-0.059-0.061-0.11-0.125-0.172-0.184c-0.284-0.278-0.584-0.535-0.868-0.813    c-2.137-2.075-4.309-4.098-6.509-6.083c-0.649-0.584-1.295-1.175-1.949-1.753c-2.405-2.124-4.848-4.192-7.323-6.209    c-0.89-0.722-1.799-1.423-2.698-2.133c-0.931-0.734-1.846-1.484-2.788-2.203l-0.03,0.04c-7.203-5.521-14.688-10.575-22.405-15.199    l0.024-0.043c-1.775-1.062-3.574-2.083-5.374-3.1c-0.722-0.407-1.443-0.814-2.169-1.212c-6.087-3.348-12.305-6.414-18.647-9.204    c-0.561-0.248-1.12-0.493-1.684-0.734c-6.193-2.668-12.497-5.067-18.904-7.191c-1.059-0.352-2.117-0.701-3.18-1.037    c-1.826-0.578-3.659-1.15-5.502-1.686c-0.106-0.031-0.214-0.067-0.321-0.098l-0.003,0.009c-8.656-2.494-17.476-4.474-26.423-5.97    l0.009-0.052c-0.908-0.153-1.829-0.272-2.741-0.416c-1.166-0.181-2.329-0.364-3.501-0.529c-3.069-0.432-6.147-0.811-9.244-1.123    c-1.001-0.101-2.004-0.171-3.008-0.26c-2.448-0.214-4.905-0.395-7.369-0.536c-1.092-0.061-2.182-0.131-3.276-0.181    c-3.198-0.138-6.405-0.22-9.624-0.232c-0.291,0-0.578-0.021-0.869-0.021c-0.545,0-1.077,0.037-1.622,0.04    c-3.146,0.021-6.278,0.095-9.394,0.238c-0.554,0.025-1.108,0.037-1.662,0.067c-2.8,0.147-5.597,0.331-8.378,0.576l0.006,0.064    c-9.082,0.802-18.032,2.057-26.793,3.855l-0.003-0.015c-3.724,0.762-7.417,1.643-11.099,2.583    c-0.223,0.059-0.45,0.113-0.676,0.171c-15.78,4.079-31.126,9.762-45.75,17.008l0.052,0.107    c-8.106,4.027-15.94,8.513-23.492,13.406l-0.086-0.134c-17.05,11.028-32.659,24.226-46.396,39.232l0.095,0.089    c-6.089,6.649-11.775,13.666-17.087,20.973l-0.128-0.092c-11.968,16.49-21.769,34.45-29.128,53.382l0.11,0.043    c-3.265,8.375-6.108,16.955-8.418,25.759l-0.046-0.012c-1.025,3.917-1.919,7.871-2.742,11.839    c-0.071,0.336-0.144,0.67-0.211,1.006c-0.725,3.571-1.371,7.17-1.934,10.796c-0.076,0.496-0.141,0.998-0.214,1.493    c-0.147,1-0.297,1.999-0.435,3.002H5.057c-5.071,0-6.579,3.183-3.373,7.108L70.846,324.068z" />
                                    </g>
                                </g>
                            </svg>
                            <?= $t('back') ?></button>
                        <p class="text-[13px]"><?= $t('install_2_steps') ?></p>
                    </div>

                </section>

                <!-- start -->
                <section data-section="start"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-end pb-4 box-border w-full min-h-[100dvh] bg-black">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="flex flex-col justify-between px-32 pb-4 z-10">
                        <div class="flex flex-col justify-start gap-16">

                            <style>
                                .ring {
                                    fill: rgba(255, 255, 255, 0.03);
                                    stroke: rgba(255, 255, 255, 0.08);
                                    stroke-width: 1;
                                    transform-origin: 400px 250px;
                                    opacity: 0;
                                    transform: scale(0);
                                    animation: ringIn 1.2s cubic-bezier(0.22, 1, 0.36, 1) forwards;
                                }

                                .ring:nth-child(1) {
                                    animation-delay: 0.1s;
                                }

                                .ring:nth-child(2) {
                                    animation-delay: 0.2s;
                                }

                                .ring:nth-child(3) {
                                    animation-delay: 0.3s;
                                }

                                .ring:nth-child(4) {
                                    animation-delay: 0.4s;
                                }

                                @keyframes ringIn {
                                    to {
                                        opacity: 1;
                                        transform: scale(1);
                                    }
                                }

                                .link {
                                    fill: none;
                                    stroke: rgba(255, 255, 255, 0.35);
                                    stroke-width: 1.2;
                                    stroke-linecap: round;
                                    stroke-dasharray: 400;
                                    stroke-dashoffset: 400;
                                    animation: drawLink 1s ease forwards;
                                }

                                .link:nth-of-type(5) {
                                    animation-delay: 0.7s;
                                }

                                .link:nth-of-type(6) {
                                    animation-delay: 0.78s;
                                }

                                .link:nth-of-type(7) {
                                    animation-delay: 0.86s;
                                }

                                .link:nth-of-type(8) {
                                    animation-delay: 0.94s;
                                }

                                .link:nth-of-type(9) {
                                    animation-delay: 1.02s;
                                }

                                .link:nth-of-type(10) {
                                    animation-delay: 1.1s;
                                }

                                @keyframes drawLink {
                                    to {
                                        stroke-dashoffset: 0;
                                    }
                                }

                                .brain {
                                    opacity: 0;
                                    transform: translate(-50%, -50%) scale(0);
                                    animation: brainIn 0.9s cubic-bezier(0.34, 1.4, 0.64, 1) 0.35s forwards;
                                }

                                .brain:hover {
                                    transform: translate(-50%, -50%) scale(1.08);
                                }

                                @keyframes brainIn {
                                    to {
                                        opacity: 1;
                                        transform: translate(-50%, -50%) scale(1);
                                    }
                                }

                                .icon-tile {
                                    opacity: 0;
                                    transform: scale(0);
                                    animation: tileIn 0.7s cubic-bezier(0.34, 1.4, 0.64, 1) forwards;
                                }

                                .tile-1 {
                                    animation-delay: 0.55s;
                                }

                                .tile-2 {
                                    animation-delay: 0.62s;
                                }

                                .tile-3 {
                                    animation-delay: 0.69s;
                                }

                                .tile-4 {
                                    animation-delay: 0.76s;
                                }

                                .tile-5 {
                                    animation-delay: 0.83s;
                                }

                                .tile-6 {
                                    animation-delay: 0.9s;
                                }

                                @keyframes tileIn {
                                    to {
                                        opacity: 1;
                                        transform: scale(1);
                                    }
                                }

                                .icon-tile:hover {
                                    transform: scale(1.1);
                                }
                            </style>

                            <div class="m-auto relative w-full max-w-[720px] aspect-[1.5/1] m-6 max-sm:aspect-[1/1.05]">
                                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 800 500"
                                    preserveAspectRatio="xMidYMid meet">
                                    <circle class="ring" cx="400" cy="250" r="60"></circle>
                                    <circle class="ring" cx="400" cy="250" r="110"></circle>
                                    <circle class="ring" cx="400" cy="250" r="160"></circle>
                                    <circle class="ring" cx="400" cy="250" r="210"></circle>
                                    <!-- <path class="link" d="M140 75 Q250 170 395 247"></path>
                                    <path class="link" d="M660 75 Q545 170 405 247"></path>
                                    <path class="link" d="M80 270 Q230 258 388 250"></path>
                                    <path class="link" d="M720 270 Q570 258 412 250"></path>
                                    <path class="link" d="M140 425 Q250 335 395 253"></path>
                                    <path class="link" d="M660 425 Q545 335 405 253"></path> -->
                                </svg>

                                <div
                                    class="flex flex-col justify-center items-center brain absolute left-1/2 top-[54%] w-[70px] z-10 cursor-pointer transition-transform duration-300 max-sm:w-16 max-sm:h-14">
                                    <!-- Apps -->
                                    <img decoding="async" loading="lazy" src="<?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['data']['logo']) ?>" alt="apps" title="apps" loading="lazy">
                                    <p class="text-center"><?= $t('app_hitwave') ?> <b><?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['data']['name']) ?></b></p>
                                    <!-- end -->
                                </div>

                                <!-- <div
                                    class="icon-tile tile-1 absolute left-[10%] top-[6%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2.2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                        <path d="M5 4 L5 20"></path>
                                        <path d="M5 12 L13 4"></path>
                                        <path d="M5 12 L13 20"></path>
                                        <path d="M14 8 L19 6 L19 10"></path>
                                        <path d="M14 16 L19 14 L19 18"></path>
                                    </svg>
                                </div>

                                <div
                                    class="icon-tile tile-2 absolute right-[10%] top-[6%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2"
                                        stroke-linecap="round" class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                        <circle cx="12" cy="12" r="8"></circle>
                                        <circle cx="9.5" cy="10.5" r="0.8" fill="#0a0a0c"></circle>
                                        <circle cx="14.5" cy="10.5" r="0.8" fill="#0a0a0c"></circle>
                                        <path d="M9 14 Q12 17 15 14"></path>
                                    </svg>
                                </div>

                                <div
                                    class="icon-tile tile-3 absolute left-[2%] top-[44%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2"
                                        stroke-linecap="round" class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                        <ellipse cx="12" cy="12" rx="8" ry="9"></ellipse>
                                        <line x1="6" y1="18" x2="18" y2="6"></line>
                                    </svg>
                                </div>

                                <div
                                    class="icon-tile tile-4 absolute right-[2%] top-[44%] w-[84px] max-sm:w-16 h-16 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                                    <svg viewBox="0 0 72 24" fill="#0a0a0c" class="w-auto h-7 max-sm:h-[22px]">
                                        <text x="0" y="19" font-family="system-ui,sans-serif" font-size="28"
                                            font-weight="800" letter-spacing="-1.2">Rytr</text>
                                    </svg>
                                </div>

                                <div
                                    class="icon-tile tile-5 absolute left-[10%] bottom-[6%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2.4"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path
                                            d="M15 8 Q12 7 10 9 Q9 11 11 12.5 Q13 13.5 14 14.5 Q15 16 13 16.8 Q11 17.3 9 16">
                                        </path>
                                    </svg>
                                </div>

                                <div
                                    class="icon-tile tile-6 absolute right-[10%] bottom-[6%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2.2"
                                        stroke-linecap="round" class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                        <path d="M8 5 Q14 5 16 9 Q17 12 13 13 L11 13 Q7 13 8 16 Q9 19 15 19"></path>
                                    </svg>
                                </div> -->

                            </div>


                            <div class="flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                    alt="logo" class="w-16 aspert-square">
                                <h3 class="text-2xl font-medium"><?= $t('install_app') ?></h3>
                                <p class="text-[13px] text-white/70"><?= $t('install_app_desc') ?></p>
                            </div>

                            <div class="flex gap-4 w-full">
                                <a href="<?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['url']) ?>"
                                    target="_blank"
                                    class="btn_download_page_tour w-full bg-transparent border border-solid border-white cursor-pointer flex justify-center text-white text-lg rounded-xl flex p-2 w-[90%]">
                                    <?= $t('download') ?></a>
                                <button data-toggle-section="finish"
                                    class="btn_next_page_tour w-full bg-white cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-2 w-[90%]">
                                    <?= $t('next') ?></button>
                            </div>
                        </div>
                    </div>

                </section>

                <!-- finish -->
                <section data-section="finish"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-end pt-[95px] pb-4 box-border w-full min-h-[100dvh] bg-black">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="flex flex-col justify-between px-32 pb-4 z-10">
                        <div class="flex flex-col justify-start gap-16">

                            <div class="flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                    alt="logo" class="w-16 aspert-square">
                                <h3 class="text-2xl font-medium"><?= $t('install_vpn') ?></h3>
                                <p class="text-[13px] text-white/70"><?= $t('install_vpn_desc') ?></p>
                            </div>

                            <div class="flex gap-4 w-full">
                                <a target="_blank"
                                    href="<?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['api'] . (new Setting\Route\Function\Controllers\Client\GetUser())->getSubscription()) ?>"
                                    class="btn_vpn_install_page_tour w-full bg-transparent border border-solid border-white cursor-pointer flex justify-center items-center text-white text-2sm rounded-xl flex p-2 w-[90%]">
                                    <?= $t('install_vpn_btn') ?>
                                </a>
                                <button onclick="window.location.href = '/';"
                                    class="btn_finish_page_tour w-full bg-white cursor-pointer flex justify-center items-center text-black text-lg rounded-xl flex p-2 w-[90%]">
                                    <?= $t('finish') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                </section>
            </div>

            <!-- ################# CONTENT MOBILE ####################-->
            <div class="sm:hidden w-full text-white">
                <!-- main -->
                <section data-section="main"
                    class="setka overflow-hidden relative flex flex-col gap-2 justify-between pt-[95px] pb-4 box-border w-full min-h-[100dvh]">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        class="absolute top-0 bottom-0 mx-auto w-full h-full opacity-70 z-0"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/light.svg" alt="backgroud">

                    <!-- text -->
                    <div
                        class="px-4 pt-[30%] mx-auto right-0 left-0 flex flex-col justify-center items-center gap-3 z-10">
                        <div class="p-4 bg-[#181818] aspect-square rounded-[40px]">
                            <img decoding="async" loading="lazy" class="w-24"
                                src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg" alt="logo">
                        </div>

                        <h3 class=" font-[qwees-poppins-semibold] text-2xl">
                            <?= htmlspecialchars($site['ООО']) ?>

                        </h3>
                        <p class="text-sm text-center w-[70%] break-world"><?= $t('start_install_desc') ?>
                            <span class="text-green-200 text-lg">
                                <?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['os']) ?>
                            </span>
                        </p>
                    </div>

                    <div class="px-4 flex flex-col gap-4 mb-6 justify-center items-center w-full z-10">
                        <button data-toggle-section="start"
                            class="btn_install_page_tour bg-white cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-3 w-[90%]"><?= $t('start_install') ?></button>
                        <button onclick="window.open('/', '_self')"
                            class="bg-transparent border-white border text-white cursor-pointer flex justify-center text-lg rounded-xl flex p-3 w-[90%]"><i
                                class="fa fa-left-arrow"></i><?= $t('back') ?></button>
                        <p class="text-[13px]"><?= $t('install_2_steps') ?></p>
                    </div>

                </section>

                <!-- start -->
                <section data-section="start"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-end items-center pt-[95px] pb-4 box-border w-full min-h-[100dvh] bg-black">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="m-auto relative w-full max-w-[720px] aspect-[1.5/1] m-6 max-sm:aspect-[1/1.05]">
                        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 800 500"
                            preserveAspectRatio="xMidYMid meet">
                            <circle class="ring" cx="400" cy="250" r="60"></circle>
                            <circle class="ring" cx="400" cy="250" r="110"></circle>
                            <circle class="ring" cx="400" cy="250" r="160"></circle>
                            <circle class="ring" cx="400" cy="250" r="210"></circle>
                            <!-- <path class="link" d="M140 75 Q250 170 395 247"></path>
                            <path class="link" d="M660 75 Q545 170 405 247"></path>
                            <path class="link" d="M80 270 Q230 258 388 250"></path>
                            <path class="link" d="M720 270 Q570 258 412 250"></path>
                            <path class="link" d="M140 425 Q250 335 395 253"></path>
                            <path class="link" d="M660 425 Q545 335 405 253"></path> -->
                        </svg>

                        <div
                            class="flex flex-col justify-center items-center brain absolute left-1/2 top-[54%] w-[70px] z-10 cursor-pointer transition-transform duration-300 max-sm:w-16 max-sm:h-14">
                            <!-- Apps -->
                            <img decoding="async" loading="lazy" src="<?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['data']['logo']) ?>" alt="apps" title="apps" loading="lazy">
                            <p class="text-center"><?= $t('app_hitwave') ?> <b><?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['data']['name']) ?></b></p>
                            <!-- end -->
                        </div>

                        <!-- <div
                            class="icon-tile tile-1 absolute left-[10%] top-[6%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                <path d="M5 4 L5 20"></path>
                                <path d="M5 12 L13 4"></path>
                                <path d="M5 12 L13 20"></path>
                                <path d="M14 8 L19 6 L19 10"></path>
                                <path d="M14 16 L19 14 L19 18"></path>
                            </svg>
                        </div>

                        <div
                            class="icon-tile tile-2 absolute right-[10%] top-[6%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2"
                                stroke-linecap="round" class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                <circle cx="12" cy="12" r="8"></circle>
                                <circle cx="9.5" cy="10.5" r="0.8" fill="#0a0a0c"></circle>
                                <circle cx="14.5" cy="10.5" r="0.8" fill="#0a0a0c"></circle>
                                <path d="M9 14 Q12 17 15 14"></path>
                            </svg>
                        </div>

                        <div
                            class="icon-tile tile-3 absolute left-[2%] top-[44%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2"
                                stroke-linecap="round" class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                <ellipse cx="12" cy="12" rx="8" ry="9"></ellipse>
                                <line x1="6" y1="18" x2="18" y2="6"></line>
                            </svg>
                        </div>

                        <div
                            class="icon-tile tile-4 absolute right-[2%] top-[44%] w-[84px] max-sm:w-16 h-16 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                            <svg viewBox="0 0 72 24" fill="#0a0a0c" class="w-auto h-7 max-sm:h-[22px]">
                                <text x="0" y="19" font-family="system-ui,sans-serif" font-size="28"
                                    font-weight="800" letter-spacing="-1.2">Rytr</text>
                            </svg>
                        </div>

                        <div
                            class="icon-tile tile-5 absolute left-[10%] bottom-[6%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2.4"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path
                                    d="M15 8 Q12 7 10 9 Q9 11 11 12.5 Q13 13.5 14 14.5 Q15 16 13 16.8 Q11 17.3 9 16">
                                </path>
                            </svg>
                        </div>

                        <div
                            class="icon-tile tile-6 absolute right-[10%] bottom-[6%] w-16 h-16 max-sm:w-12 max-sm:h-12 rounded-2xl max-sm:rounded-xl bg-white flex items-center justify-center cursor-pointer z-20 shadow-[0_16px_28px_-12px_rgba(0,0,0,0.55),0_4px_8px_-2px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:shadow-[0_22px_36px_-12px_rgba(0,0,0,0.65)]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0c" stroke-width="2.2"
                                stroke-linecap="round" class="w-7 h-7 max-sm:w-[22px] max-sm:h-[22px]">
                                <path d="M8 5 Q14 5 16 9 Q17 12 13 13 L11 13 Q7 13 8 16 Q9 19 15 19"></path>
                            </svg>
                        </div> -->

                    </div>

                    <div class="flex flex-col justify-between items-center px-6 pb-4 z-10">
                        <div class="flex flex-col items-center justify-start gap-16">

                            <div class="flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img decoding="async" loading="lazy"
                                    src="<?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg"
                                    alt="logo" class="w-10 aspert-square">
                                <h3 class="text-2xl font-medium"><?= $t('install_app') ?></h3>
                                <p class="text-[13px] text-white/70"><?= $t('install_app_desc') ?></p>
                            </div>

                            <div class="flex gap-4 w-full">
                                <a href="<?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['url']) ?>"
                                    target="_blank"
                                    class="btn_download_page_tour w-full bg-transparent border border-solid border-white cursor-pointer flex justify-center text-white text-lg rounded-xl flex p-2 w-[90%]">
                                    <?= $t('download') ?></a>
                                <button data-toggle-section="finish"
                                    class="btn_next_page_tour w-full bg-white cursor-pointer flex justify-center text-black text-lg rounded-xl flex p-2 w-[90%]">
                                    <?= $t('next') ?></button>
                            </div>
                        </div>
                    </div>

                </section>

                <!-- finish -->
                <section data-section="finish"
                    class="hidden overflow-hidden relative flex flex-col gap-2 justify-end items-center pt-[95px] pb-4 box-border w-full min-h-[100dvh] bg-black">
                    <!-- background -->
                    <img decoding="async" loading="lazy"
                        src="<?= $site['baseUrl'] ?>/public/assets/images/background/map.svg" alt="map"
                        class="absolute right-0 top-0 h-full z-0">

                    <div class="flex flex-col justify-between items-center px-6 pb-4 z-10">
                        <div class="flex flex-col items-center justify-start gap-16">

                            <div class=" flex flex-col items-start justify-start gap-3">
                                <!-- logo -->
                                <img decoding="async" loading="lazy" src="
                        <?= $site['baseUrl'] ?>/public/assets/images/icons/logo/qweesvpn.svg" alt="logo"
                                    class="w-10 aspert-square">
                                <h3 class="text-2xl font-medium"><?= $t('install_vpn') ?></h3>
                                <p class="text-[13px] text-white/70"><?= $t('install_vpn_desc') ?></p>
                            </div>

                            <div class="flex gap-4 w-full">
                                <a target="_blank"
                                    href="<?= htmlspecialchars((new Setting\Route\Function\Controllers\OS\OS())->getOS()['api'] . (new Setting\Route\Function\Controllers\Client\GetUser())->getSubscription()) ?>"
                                    class="btn_vpn_install_page_tour w-full bg-transparent border border-solid border-white cursor-pointer flex justify-center items-center text-white text-2sm rounded-xl flex p-2 w-[90%]">
                                    <?= $t('install_vpn_btn') ?>
                                </a>
                                <button onclick="window.location.href = '/';"
                                    class="btn_finish_page_tour w-full bg-white cursor-pointer flex justify-center items-center text-black text-lg rounded-xl flex p-2 w-[90%]">
                                    <?= $t('finish') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                </section>
            </div>
        </main>
    </div>

    <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/main/main.js"></script>
    <script src="<?= $site['baseUrl'] ?>/public/assets/scripts/theme/main.js"></script>
    <?php include_once "public/components/tour.php"; ?>
</body>

</html>