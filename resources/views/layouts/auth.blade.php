<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal layanan kesehatan terpadu PosyanduCare.">

    <link rel="preload" as="image" href="{{ asset('img/logo.webp') }}" type="image/webp" fetchpriority="high">
    <title>@yield('title', 'PosyanduCare')</title>
    <link rel="icon" type="image/webp" href="{{ asset('img/logo.webp') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" as="style" fetchpriority="low">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" media="print" onload="this.media='all'">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lottie dimuat malas (lazy) hanya saat overlay benar-benar dibutuhkan -->

    <script type="speculationrules">
    {
        "prerender": [{ "where": { "href_matches": "/*" }, "eagerness": "moderate" }]
    }
    </script>

    <style>
        :root {
            --green-900: #064e3b; --green-700: #047857; --green-600: #059669; --green-500: #10b981;
            --slate-900: #0f172a; --slate-700: #334155; --slate-600: #475569; --slate-500: #64748b;
            --amber-500: #f59e0b; --rose-500: #f43f5e;
        }

        * { box-sizing: border-box; }
        html, body { width: 100%; min-height: 100svh; margin: 0; background-color: #f8fafc !important; }

        body {
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif;
            color: var(--slate-900);
            -webkit-font-smoothing: antialiased; font-synthesis: none;
            overflow-x: hidden; position: relative;
        }

        .icon-sprite { position: absolute; width: 0; height: 0; overflow: hidden; }
        .icon { display: inline-block; vertical-align: middle; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* BACKGROUND STATIS ASLI */
        .auth-bg { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden; }
        .auth-bg-base {
            position: absolute; inset: 0;
            background: radial-gradient(circle at 10% 10%, rgba(16,185,129,0.15), transparent 40%),
                        radial-gradient(circle at 90% 90%, rgba(245,158,11,0.12), transparent 40%),
                        linear-gradient(135deg, #eefcf6 0%, #ffffff 50%, #fff7ed 100%);
        }

        .auth-main { position: relative; z-index: 10; min-height: 100svh; display: flex; align-items: center; justify-content: center; padding: 24px; }

        /* HALUS MASUK (FADE IN) */
        .auth-main { animation: pageFadeIn .2s cubic-bezier(0.22, 1, 0.36, 1) both; }
        @keyframes pageFadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }

        /* Transisi halus lintas-halaman (Chrome/Edge). Tambahkan juga di layout dashboard agar login → dashboard ter-crossfade */
        @view-transition { navigation: auto; }
        ::view-transition-old(root), ::view-transition-new(root) { animation-duration: .18s; }

        /* === LOADING LOTTIE STYLE PREMIUM (INSTAN & HALUS) === */
        .fast-overlay {
            position: fixed; inset: 0; z-index: 5000;
            background: rgba(240, 244, 248, 0.5);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            opacity: 0; visibility: hidden; pointer-events: none;
            /* Dipercepat menjadi 0.2s agar responsif dan instan saat menghilang */
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }
        .fast-overlay.active { opacity: 1; visibility: visible; pointer-events: auto; }
        
        /* Kartu Glassmorphism untuk Loading */
        .loading-glass-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgba(255, 255, 255, 0.65);
            padding: 25px 45px 35px 45px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .lottie-wrapper {
            margin-bottom: -15px;
        }

        .loading-text {
            color: var(--slate-900);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
            margin-bottom: 20px;
            text-align: center;
        }

        .dot-container {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .dot-1 { background-color: var(--green-500); animation-delay: -0.32s; }
        .dot-2 { background-color: var(--amber-500); animation-delay: -0.16s; }
        .dot-3 { background-color: var(--slate-900); animation-delay: 0s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); } 
            40% { transform: scale(1); }
        }

        /* === TOAST ALERT MODERN INSTAN === */
        .toast-container { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 6000; display: flex; flex-direction: column; gap: 10px; align-items: center; pointer-events: none;}
        .toast-alert {
            pointer-events: auto; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);
            padding: 14px 20px 14px 14px; border-radius: 100px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid rgba(244, 63, 94, 0.2);
            display: flex; align-items: center; gap: 12px; color: var(--slate-900); font-size: 14px; font-weight: 700;
            transform: translateY(-20px) scale(0.95); opacity: 0;
            animation: toastPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        .toast-icon { width: 28px; height: 28px; border-radius: 50%; background: #fff1f2; color: var(--rose-500); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 900;}
        .toast-alert.is-success { border-color: rgba(16, 185, 129, 0.2); }
        .toast-alert.is-success .toast-icon { background: #ecfdf5; color: var(--green-600); }
        @keyframes toastPop { to { transform: translateY(0) scale(1); opacity: 1; } }
        
        .toast-fadeout { animation: toastOut 0.25s ease forwards; }
        @keyframes toastOut { to { transform: translateY(-20px) scale(0.95); opacity: 0; } }

        /* === PROGRESS BAR TIPIS (ringan, tanpa blur/Lottie) === */
        .top-progress { position: fixed; top: 0; left: 0; height: 3px; width: 0; z-index: 7000; opacity: 0; pointer-events: none;
            background: linear-gradient(90deg, var(--green-500), var(--amber-500)); border-radius: 0 3px 3px 0; box-shadow: 0 0 8px rgba(16,185,129,.5); }

        @media (prefers-reduced-motion: reduce) {
            .auth-main { animation: none; }
            ::view-transition-group(*), ::view-transition-old(*), ::view-transition-new(*) { animation: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Komponen Overlay Loading dengan Lottie, Teks, dan Animasi Titik -->
    <div class="fast-overlay" id="fastOverlay">
        <div class="loading-glass-card">
            <!-- Animasi Lottie di Atas -->
            <div class="lottie-wrapper">
                <lottie-player 
                    src="{{ asset('lottie/loading-animation.json') }}" 
                    background="transparent"  
                    speed="1"  
                    style="width: 180px; height: 180px;"  
                    loop  
                    autoplay>
                </lottie-player>
            </div>

            <!-- Teks Lucu & Gemas di Tengah -->
            <p class="loading-text">Sabar yaa, masih loading... 🥺🚀</p>

            <!-- Animasi 3 Titik di Bawah -->
            <div class="dot-container">
                <span class="dot dot-1"></span>
                <span class="dot dot-2"></span>
                <span class="dot dot-3"></span>
            </div>
        </div>
    </div>
    
    <!-- Wadah Toast Alert -->
    <div class="toast-container" id="toastContainer"></div>
    <div class="top-progress" id="topProgress"></div>

    <!-- Master SVG Icons -->
    <svg class="icon-sprite" aria-hidden="true">
        <symbol id="icon-user-group" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"></path><circle cx="17.5" cy="9" r="2.2"></circle><path d="M15.5 20c.3-2.5 2.1-4.4 4.5-4.8"></path></symbol>
        <symbol id="icon-shield" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 5-3.2 8.4-7 10-3.8-1.6-7-5-7-10V6l7-3z"></path><path d="M9 12l2 2 4-4"></path></symbol>
        <symbol id="icon-chart" viewBox="0 0 24 24"><rect x="4" y="12" width="3.4" height="8" rx="1"></rect><rect x="10.3" y="7" width="3.4" height="13" rx="1"></rect><rect x="16.6" y="3" width="3.4" height="17" rx="1"></rect></symbol>
        <symbol id="icon-heart-pulse" viewBox="0 0 24 24"><path d="M12 20.5S3.5 15 3.5 9a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20.5 9c0 6-8.5 11.5-8.5 11.5z"></path><path d="M6.2 11h2.4l1.4-2.8 2 4 1.3-1.2H17.8"></path></symbol>
        <symbol id="icon-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.4"></circle><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"></path></symbol>
        <symbol id="icon-lock" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"></rect><path d="M8 11V8a4 4 0 0 1 8 0v3"></path></symbol>
        <symbol id="icon-eye" viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="2.6"></circle></symbol>
        <symbol id="icon-eye-slash" viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="2.6"></circle><line x1="3" y1="3" x2="21" y2="21"></line></symbol>
        <symbol id="icon-arrow-right" viewBox="0 0 24 24"><path d="M13 5l7 7-7 7"></path><path d="M20 12H4"></path></symbol>
        <symbol id="icon-check" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M8 12.5l2.5 2.5L16 9.5"></path></symbol>
        <symbol id="icon-x" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></symbol>
    </svg>

    <div class="auth-bg"><div class="auth-bg-base"></div></div>

    <main class="auth-main">
        @yield('content')
    </main>

    <script>
        // FUNGSI JAVASCRIPT GLOBAL UNTUK TOAST ALERT MODERN
        window.showModernAlert = function(message, type = 'error') {
            const container = document.getElementById('toastContainer');
            container.innerHTML = ''; 

            const toast = document.createElement('div');
            toast.className = `toast-alert ${type === 'success' ? 'is-success' : ''}`;
            const iconHtml = type === 'success' ? '✓' : '✗';
            const icon = document.createElement('div');
            icon.className = 'toast-icon';
            icon.textContent = iconHtml;
            const text = document.createElement('span');
            text.textContent = message;
            toast.append(icon, text);
            
            container.appendChild(toast);
            
            const overlay = document.getElementById('fastOverlay');
            if(overlay) overlay.classList.remove('active');

            setTimeout(() => {
                toast.classList.add('toast-fadeout');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // TRIGGER FLASH MESSAGES DAN HILANGKAN LOADING TANPA PENUNDAAN (INSTAN)
        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success')) 
                window.showModernAlert(@json(session('success')), 'success'); 
            @endif
            @if(session('error')) 
                window.showModernAlert(@json(session('error')), 'error'); 
            @endif
            @if($errors->any()) 
                window.showModernAlert(@json($errors->first()), 'error'); 
            @endif

            // Langsung hilangkan loading tanpa jeda waktu (setTimeout dihapus)
            const overlay = document.getElementById('fastOverlay');
            if(overlay) {
                overlay.classList.remove('active');
            }
        });

        // === PROGRESS BAR TIPIS ===
        (function () {
            const bar = document.getElementById('topProgress');
            let timer = null, w = 0;
            window.startProgress = function () {
                clearInterval(timer);
                w = 8; bar.style.transition = 'none'; bar.style.opacity = '1'; bar.style.width = w + '%';
                requestAnimationFrame(() => {
                    bar.style.transition = 'width .25s ease';
                    timer = setInterval(() => { w += (90 - w) * 0.12; bar.style.width = w + '%'; }, 120);
                });
            };
            window.finishProgress = function () {          // sukses: biarkan penuh sampai halaman berpindah
                clearInterval(timer); bar.style.transition = 'width .15s ease'; bar.style.width = '100%';
            };
            window.stopProgress = function () {            // gagal: sembunyikan
                clearInterval(timer); bar.style.transition = 'opacity .2s ease'; bar.style.opacity = '0';
            };
        })();

        // === OVERLAY LOTTIE (lazy) — hanya untuk form lain; form login memakai data-no-overlay ===
        let lottieLoading = false;
        function loadLottie() {
            if (lottieLoading || window.customElements.get('lottie-player')) return;
            lottieLoading = true;
            const sc = document.createElement('script');
            sc.src = 'https://unpkg.com/@lottiefiles/lottie-player@2.0.8/dist/lottie-player.js';
            sc.async = true;
            document.head.appendChild(sc);
        }
        document.querySelectorAll('form:not([data-no-overlay])').forEach(form => {
            form.addEventListener('submit', () => {
                const overlay = document.getElementById('fastOverlay');
                if (overlay) { loadLottie(); overlay.classList.add('active'); }
            });
        });

        // Kembali via tombol Back (bfcache): pastikan overlay/progress tidak tersangkut
        window.addEventListener('pageshow', (e) => {
            if (!e.persisted) return;
            const overlay = document.getElementById('fastOverlay');
            if (overlay) overlay.classList.remove('active');
            window.stopProgress && window.stopProgress();
        });
    </script>
    @stack('scripts')
</body>
</html>