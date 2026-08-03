<style>
    #loader {
        position: fixed;
        inset: 0;
        background: black;
        z-index: 100000;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 2rem;
    }

    .loader-name {
        font-family: 'Syne', sans-serif;
        font-size: clamp(32px, 6vw, 80px);
        font-weight: 800;
        color: #FAF7F2;
        letter-spacing: -0.03em;
        opacity: 0;
        transform: translateY(20px);
    }

    .loader-bar-wrap {
        width: 200px;
        height: 1px;
        background: rgba(255, 255, 255, 0.15);
        position: relative;
        overflow: hidden;
    }

    .loader-bar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background: #00ff00;
        width: 0;
    }

    .loader-counter {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.4);
        letter-spacing: 0.2em;
    }
</style>
<!-- LOADER -->
<div id="loader">
    <div class="loader-name" id="loader-name">QweesTeam Studio<span style="color:#00ff00">.</span></div>
    <div class="loader-bar-wrap">
        <div class="loader-bar" id="loader-bar"></div>
    </div>
    <div class="loader-counter" id="loader-counter">0%</div>
</div>
<script defer>
    window.addEventListener('load', () => {
        const loader = document.getElementById('loader');
        const bar = document.getElementById('loader-bar');
        const counter = document.getElementById('loader-counter');
        const name = document.getElementById('loader-name');

        // Animate name in
        gsap.to(name, {
            opacity: 1, y: 0, duration: 0.8, ease: 'power3.out', delay: 0.1
        });

        let progress = 0;

        const interval = setInterval(() => {
            progress += Math.random() * 15;

            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);

                setTimeout(() => {
                    gsap.to(loader, {
                        opacity: 0,
                        duration: 0.9,
                        ease: 'power4.inOut',
                        onComplete: () => {
                            loader.style.display = 'none';
                        }
                    });
                }, 300);
            }
            bar.style.width = progress + '%';
            counter.textContent = String(Math.floor(progress)).padStart(1, '0') + '%';
        }, 60);
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>