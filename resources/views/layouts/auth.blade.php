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

    {{-- Prerender halaman tujuan saat link di-hover/ditekan (Chrome) -> navigasi terasa instan, bukan cuma animasi --}}
    <script type="speculationrules">
    {
        "prerender": [{ "where": { "href_matches": "/*" }, "eagerness": "moderate" }]
    }
    </script>

    <style>
        :root {
            --green-900: #064e3b; --green-700: #047857; --green-600: #059669; --green-500: #10b981;
            --slate-900: #0f172a; --slate-700: #334155; --slate-600: #475569; --slate-500: #64748b;
            --amber-500: #f59e0b;
        }

        * { box-sizing: border-box; }
        html, body { width: 100%; min-height: 100svh; margin: 0; background-color: #f8fafc !important; }

        body {
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif;
            color: var(--slate-900);
            -webkit-font-smoothing: antialiased; font-synthesis: none;
            overflow-x: hidden;
            position: relative;
        }

        .icon-sprite { position: absolute; width: 0; height: 0; overflow: hidden; }
        .icon { display: inline-block; vertical-align: middle; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* BACKGROUND STATIS */
        .auth-bg { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden; }
        .auth-bg-base {
            position: absolute; inset: 0;
            background: radial-gradient(circle at 10% 10%, rgba(16,185,129,0.15), transparent 40%),
                        radial-gradient(circle at 90% 90%, rgba(245,158,11,0.12), transparent 40%),
                        linear-gradient(135deg, #eefcf6 0%, #ffffff 50%, #fff7ed 100%);
        }

        /* CONTAINER UTAMA - DIBUAT INSTAN TANPA ANIMASI DELAY */
        .auth-main {
            position: relative; z-index: 10; min-height: 100svh;
            display: flex; align-items: center; justify-content: center; padding: 24px;
        }

        /* PAGE TRANSITION - NATIVE VIEW TRANSITION (hilangkan blank putih saat pindah halaman) */
        @view-transition { navigation: auto; }

        ::view-transition-old(root) {
            animation: authViewOut .32s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        ::view-transition-new(root) {
            animation: authViewIn .38s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        @keyframes authViewOut { to { opacity: 0; transform: scale(0.985); } }
        @keyframes authViewIn { from { opacity: 0; transform: scale(1.015); } }

        @media (prefers-reduced-motion: reduce) {
            ::view-transition-old(root), ::view-transition-new(root) { animation: none !important; }
        }

        /* Fallback untuk browser yang belum dukung View Transitions, dan untuk submit form login (POST) */
        body { animation: pageFadeIn .18s cubic-bezier(0.22, 1, 0.36, 1) both; }
        @keyframes pageFadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

        body.page-leaving {
            animation: none;
            opacity: 1;
            transition: opacity .18s ease-in;
        }
        body.page-leaving.is-hidden { opacity: 0; }

        /* PREMIUM ALERT */
        .premium-alert-backdrop {
            position: fixed; inset: 0; z-index: 999;
            background: rgba(8, 15, 30, 0.55);
            backdrop-filter: blur(14px) saturate(140%);
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .premium-alert-backdrop.is-open { opacity: 1; pointer-events: auto; }

        .premium-alert {
            position: fixed; top: 50%; left: 50%; z-index: 1000;
            width: calc(100% - 48px); max-width: 380px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 28px;
            padding: 34px 28px 24px;
            box-shadow: 0 30px 70px -12px rgba(2, 6, 23, 0.35), 0 0 0 1px rgba(255,255,255,0.4) inset;
            text-align: center;
            transform: translate(-50%, -46%) scale(0.9);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.32s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
            overflow: hidden;
        }
        .premium-alert.is-open { transform: translate(-50%, -50%) scale(1); opacity: 1; pointer-events: auto; }

        .premium-alert-close {
            position: absolute; top: 14px; right: 14px;
            width: 30px; height: 30px; border-radius: 10px;
            background: rgba(15,23,42,0.04); border: none; color: var(--slate-500);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background 0.15s ease, color 0.15s ease, transform 0.15s ease;
        }
        .premium-alert-close:hover { background: #f1f5f9; color: var(--slate-900); transform: rotate(90deg); }

        .premium-alert-icon-wrap {
            width: 64px; height: 64px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            transform: scale(0);
            opacity: 0;
        }
        .premium-alert.is-open .premium-alert-icon-wrap {
            animation: alertIconPop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) 0.08s both;
        }
        @keyframes alertIconPop { to { transform: scale(1); opacity: 1; } }
        .premium-alert-icon-wrap .icon { width: 30px; height: 30px; }
        .premium-alert-icon-wrap.is-success { background: #ecfdf5; color: var(--green-600); box-shadow: 0 0 0 8px rgba(16,185,129,0.08), 0 10px 20px -6px rgba(16,185,129,0.35); }
        .premium-alert-icon-wrap.is-error   { background: #fef2f2; color: #dc2626; box-shadow: 0 0 0 8px rgba(220,38,38,0.08), 0 10px 20px -6px rgba(220,38,38,0.35); }
        .premium-alert-icon-wrap.is-warning { background: #fffbeb; color: var(--amber-500); box-shadow: 0 0 0 8px rgba(245,158,11,0.08), 0 10px 20px -6px rgba(245,158,11,0.35); }
        .premium-alert-icon-wrap.is-info    { background: #eff6ff; color: #2563eb; box-shadow: 0 0 0 8px rgba(37,99,235,0.08), 0 10px 20px -6px rgba(37,99,235,0.35); }

        .premium-alert-title { color: var(--slate-900); font-size: 1.2rem; font-weight: 800; margin: 0 0 6px; letter-spacing: -0.03em; }
        .premium-alert-msg { color: var(--slate-600); font-size: 0.9rem; font-weight: 500; line-height: 1.6; margin: 0; }
        .premium-alert-msg b { color: var(--slate-900); }

        .premium-alert-confirm {
            border: none; border-radius: 14px; background: var(--slate-900); color: #fff;
            font-weight: 700; font-size: 0.9rem; padding: 13px 24px; width: 100%;
            margin-top: 22px; cursor: pointer; transition: transform 0.12s ease-out, background 0.15s ease, box-shadow 0.15s ease;
            font-family: inherit;
        }
        .premium-alert-confirm:hover { background: var(--green-900); box-shadow: 0 8px 20px -6px rgba(6,78,59,0.4); }
        .premium-alert-confirm:active { transform: scale(0.96); }

        .premium-alert-progress-track {
            position: absolute; left: 0; right: 0; bottom: 0; height: 3px;
            background: rgba(15,23,42,0.05); overflow: hidden;
        }
        .premium-alert-progress { height: 100%; width: 100%; background: linear-gradient(90deg, var(--green-500), var(--green-600)); transform-origin: left center; }
        @keyframes premiumAlertShrink { from { transform: scaleX(1); } to { transform: scaleX(0); } }

        /* GERBANG TRANSISI - nutup layar instan (ga ada celah putih), lalu kebuka dari tengah begitu halaman siap */
        .gate-overlay { position: fixed; inset: 0; z-index: 3000; pointer-events: none; overflow: hidden; }

        /* Background dikunci ke viewport (fixed) - kedua panel jadi "jendela" ke satu gradient yang sama,
           persis nyambung di tengah tanpa belang atau garis jahitan */
        .gate-panel {
            position: absolute; top: 0; bottom: 0; width: 50%;
            background-image:
                radial-gradient(circle at 32% 18%, rgba(94,234,212,0.35), transparent 48%),
                radial-gradient(circle at 68% 88%, rgba(45,212,191,0.22), transparent 46%),
                linear-gradient(160deg, #10b981 0%, #0f766e 38%, #134e4a 68%, #0a1f1c 100%);
            background-size: 100vw 100vh;
            background-attachment: fixed, fixed, fixed;
            background-position: 0 0;
            will-change: transform, filter;
            transition: transform .62s cubic-bezier(0.83, 0, 0.17, 1), filter .62s ease;
        }
        .gate-left  { left: 0; }
        .gate-right { right: 0; transition-delay: .04s; }
        .gate-overlay.is-open .gate-left  { transform: translateX(-102%) scale(1.03); filter: blur(2px); }
        .gate-overlay.is-open .gate-right { transform: translateX(102%) scale(1.03); filter: blur(2px); }

        /* Kilau tipis yang menyapu pas gerbang mau kebuka - kasih kesan premium */
        .gate-seam {
            position: absolute; top: 0; bottom: 0; left: 50%; width: 140px; margin-left: -70px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18) 48%, transparent);
            opacity: 0; pointer-events: none;
        }
        .gate-overlay.is-open .gate-seam { animation: gateSheen .62s cubic-bezier(0.4,0,0.2,1) both; }
        @keyframes gateSheen { 0% { opacity: 0; transform: scaleX(0.4); } 35% { opacity: 1; } 100% { opacity: 0; transform: scaleX(3.2); } }


        .gate-brand {
            position: absolute; inset: 0; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 18px;
            opacity: 1; transform: scale(1) translateY(0); filter: blur(0);
            transition: opacity .3s ease, transform .4s cubic-bezier(0.34, 1.56, 0.64, 1), filter .3s ease;
        }
        .gate-overlay.is-open .gate-brand { opacity: 0; transform: scale(0.92) translateY(-6px); filter: blur(4px); }

        .gate-logo-wrap { position: relative; width: 148px; display: flex; align-items: center; justify-content: center; }
        .gate-logo-wrap::before {
            content: ''; position: absolute; inset: -20px -34px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.22), transparent 70%);
            animation: gateGlow 2.2s ease-in-out infinite;
        }
        @keyframes gateGlow { 0%, 100% { opacity: .5; transform: scale(0.92); } 50% { opacity: 1; transform: scale(1.08); } }
        .gate-logo { position: relative; width: 100%; height: auto; filter: brightness(0) invert(1); opacity: .97; animation: gateFloat 3s ease-in-out infinite; }
        @keyframes gateFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }

        .gate-bar { width: 120px; height: 3px; border-radius: 999px; background: rgba(255,255,255,0.18); overflow: hidden; margin-top: 4px; }
        .gate-bar span {
            display: block; width: 40%; height: 100%; border-radius: 999px;
            background: linear-gradient(90deg, transparent, #fff, transparent);
            animation: gateBarSweep 1.1s cubic-bezier(0.4,0,0.2,1) infinite;
        }
        @keyframes gateBarSweep { 0% { transform: translateX(-120%); } 100% { transform: translateX(280%); } }

        @media (prefers-reduced-motion: reduce) {
            .gate-panel, .gate-brand { transition: none !important; }
            .gate-logo, .gate-logo-wrap::before, .gate-bar span, .gate-seam { animation: none !important; }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="gate-overlay" id="gateOverlay" aria-hidden="true">
        <div class="gate-panel gate-left"></div>
        <div class="gate-panel gate-right"></div>
        <div class="gate-seam"></div>
        <div class="gate-brand">
            <div class="gate-logo-wrap">
                <img src="{{ asset('img/logo.webp') }}" alt="" class="gate-logo">
            </div>
            <div class="gate-bar"><span></span></div>
        </div>
    </div>

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
        <symbol id="icon-close" viewBox="0 0 24 24"><line x1="5" y1="5" x2="19" y2="19"></line><line x1="19" y1="5" x2="5" y2="19"></line></symbol>
        <symbol id="icon-alert-triangle" viewBox="0 0 24 24"><path d="M12 3.5L21.5 20h-19L12 3.5z"></path><line x1="12" y1="9.5" x2="12" y2="13.5"></line><circle cx="12" cy="16.6" r="0.9" fill="currentColor" stroke="none"></circle></symbol>
        <symbol id="icon-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="11" x2="12" y2="16"></line><circle cx="12" cy="7.8" r="0.9" fill="currentColor" stroke="none"></circle></symbol>
    </svg>

    <div class="auth-bg">
        <div class="auth-bg-base"></div>
    </div>

    <main class="auth-main">
        @yield('content')
    </main>

    <div class="premium-alert-backdrop" id="premiumAlertBackdrop"></div>
    <div class="premium-alert" id="premiumAlert" role="alertdialog" aria-modal="true" aria-live="assertive" aria-labelledby="premiumAlertTitle">
        <button type="button" class="premium-alert-close" id="premiumAlertClose" aria-label="Tutup notifikasi">
            <svg class="icon" width="14" height="14"><use href="#icon-close"></use></svg>
        </button>
        <div class="premium-alert-icon-wrap" id="premiumAlertIconWrap">
            <svg class="icon"><use id="premiumAlertIconUse" href="#icon-check"></use></svg>
        </div>
        <h3 class="premium-alert-title" id="premiumAlertTitle"></h3>
        <p class="premium-alert-msg" id="premiumAlertMsg"></p>
        <button type="button" class="premium-alert-confirm" id="premiumAlertConfirm">Tutup</button>
        <div class="premium-alert-progress-track"><div class="premium-alert-progress" id="premiumAlertProgress"></div></div>
    </div>

    <script>
        (function () {
            var backdrop = document.getElementById('premiumAlertBackdrop');
            var modal = document.getElementById('premiumAlert');
            var iconWrap = document.getElementById('premiumAlertIconWrap');
            var iconUse = document.getElementById('premiumAlertIconUse');
            var titleEl = document.getElementById('premiumAlertTitle');
            var msgEl = document.getElementById('premiumAlertMsg');
            var progressEl = document.getElementById('premiumAlertProgress');
            var closeBtn = document.getElementById('premiumAlertClose');
            var confirmBtn = document.getElementById('premiumAlertConfirm');
            var closeTimer = null;

            function closeAlert() {
                modal.classList.remove('is-open');
                backdrop.classList.remove('is-open');
                clearTimeout(closeTimer);
            }

            function openAlert(title, html, type, autoCloseMs) {
                clearTimeout(closeTimer);
                var cfg = {
                    success: { icon: '#icon-check', cls: 'is-success' },
                    error:   { icon: '#icon-x', cls: 'is-error' },
                    warning: { icon: '#icon-alert-triangle', cls: 'is-warning' },
                    info:    { icon: '#icon-info', cls: 'is-info' }
                }[type] || { icon: '#icon-info', cls: 'is-info' };

                iconWrap.className = 'premium-alert-icon-wrap ' + cfg.cls;
                iconUse.setAttribute('href', cfg.icon);
                titleEl.textContent = title;
                msgEl.innerHTML = html;

                modal.classList.add('is-open');
                backdrop.classList.add('is-open');

                progressEl.style.animation = 'none';
                void progressEl.offsetWidth;
                progressEl.style.animation = 'premiumAlertShrink ' + autoCloseMs + 'ms linear forwards';

                closeTimer = setTimeout(closeAlert, autoCloseMs);
            }

            closeBtn.addEventListener('click', closeAlert);
            confirmBtn.addEventListener('click', closeAlert);
            backdrop.addEventListener('click', closeAlert);

            // GERBANG: buka begitu halaman siap (minimal biar ga glitchy, maksimal biar ga nyangkut lama)
            var gate = document.getElementById('gateOverlay');
            var gateOpened = false;

            function openGate() {
                if (!gate || gateOpened) return;
                gateOpened = true;
                gate.classList.add('is-open');
                setTimeout(function () { gate.style.visibility = 'hidden'; }, 520);
            }

            var MIN_CLOSED_MS = 220, MAX_WAIT_MS = 900;
            window.addEventListener('load', function () { setTimeout(openGate, MIN_CLOSED_MS); });
            setTimeout(openGate, MAX_WAIT_MS);

            // Dipanggil sebelum pindah halaman (mis. submit login): tutup gerbang dulu, baru navigasi.
            // Total delay tambahan cuma ~350ms - cukup buat halus, tetap terasa cepat.
            window.fadeNavigate = function (proceed) {
                if (!gate) { proceed(); return; }
                gate.style.visibility = 'visible';
                gate.classList.remove('is-open');
                void gate.offsetWidth; // paksa reflow biar animasi nutup kepicu
                setTimeout(proceed, 350);
            };

            window.showAuthAlert = function (title, message, icon) {
                openAlert(title, message, icon || 'info', 3000);
            };
        })();

        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                window.showAuthAlert('Berhasil', @json(session('success')), 'success');
            @endif
            @if(session('error'))
                window.showAuthAlert('Gagal', @json(session('error')), 'error');
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>