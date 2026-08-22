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

        /* BACKGROUND STATIS SAJA */
        .auth-bg { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden; }
        .auth-bg-base {
            position: absolute; inset: 0;
            background: radial-gradient(circle at 10% 10%, rgba(16,185,129,0.15), transparent 40%),
                        radial-gradient(circle at 90% 90%, rgba(245,158,11,0.12), transparent 40%),
                        linear-gradient(135deg, #eefcf6 0%, #ffffff 50%, #fff7ed 100%);
        }

        /* FORM TRANSISI FADE IN (Hanya saat pertama masuk, langsung muncul seketika) */
        .auth-main {
            position: relative; z-index: 10; min-height: 100svh;
            display: flex; align-items: center; justify-content: center; padding: 24px;
            animation: instantFade 0.3s ease-out forwards;
        }
        
        @keyframes instantFade {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        /* PERHATIAN: Semua kode class .is-splitting SUDAH DIHAPUS agar layar tidak pernah blank */

        /* PREMIUM ALERT */
        .premium-alert-backdrop {
            position: fixed; inset: 0; z-index: 999;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(6px);
            opacity: 0; pointer-events: none;
            transition: opacity 0.18s ease;
        }
        .premium-alert-backdrop.is-open { opacity: 1; pointer-events: auto; }

        .premium-alert {
            position: fixed; top: 50%; left: 50%; z-index: 1000;
            width: calc(100% - 48px); max-width: 380px;
            background: rgba(255,255,255,0.98);
            border-radius: 24px;
            padding: 32px 28px 24px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.18);
            text-align: center;
            transform: translate(-50%, -50%) scale(0.92);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.18s ease;
            will-change: transform, opacity;
            overflow: hidden;
        }
        .premium-alert.is-open { transform: translate(-50%, -50%) scale(1); opacity: 1; pointer-events: auto; }

        .premium-alert-close {
            position: absolute; top: 14px; right: 14px;
            width: 30px; height: 30px; border-radius: 10px;
            background: none; border: none; color: var(--slate-500);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background 0.15s ease, color 0.15s ease;
        }
        .premium-alert-close:hover { background: #f1f5f9; color: var(--slate-900); }

        .premium-alert-icon-wrap {
            width: 60px; height: 60px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
        }
        .premium-alert-icon-wrap .icon { width: 28px; height: 28px; }
        .premium-alert-icon-wrap.is-success { background: #ecfdf5; color: var(--green-600); }
        .premium-alert-icon-wrap.is-error   { background: #fef2f2; color: #dc2626; }
        .premium-alert-icon-wrap.is-warning { background: #fffbeb; color: var(--amber-500); }
        .premium-alert-icon-wrap.is-info    { background: #eff6ff; color: #2563eb; }

        .premium-alert-title { color: var(--slate-900); font-size: 1.15rem; font-weight: 800; margin: 0 0 6px; letter-spacing: -0.02em; }
        .premium-alert-msg { color: var(--slate-600); font-size: 0.9rem; font-weight: 500; line-height: 1.55; margin: 0; }
        .premium-alert-msg b { color: var(--slate-900); }

        .premium-alert-confirm {
            border: none; border-radius: 12px; background: var(--slate-900); color: #fff;
            font-weight: 700; font-size: 0.9rem; padding: 12px 24px; width: 100%;
            margin-top: 20px; cursor: pointer; transition: transform 0.1s ease-out, background 0.15s ease;
            font-family: inherit;
        }
        .premium-alert-confirm:hover { background: var(--green-900); }
        .premium-alert-confirm:active { transform: scale(0.96); }

        .premium-alert-progress-track {
            position: absolute; left: 0; right: 0; bottom: 0; height: 4px;
            background: rgba(15,23,42,0.06); overflow: hidden;
        }
        .premium-alert-progress { height: 100%; width: 100%; background: var(--green-500); transform-origin: left center; }
        @keyframes premiumAlertShrink { from { transform: scaleX(1); } to { transform: scaleX(0); } }

        @media (prefers-reduced-motion: reduce) {
            .auth-main, .premium-alert, .premium-alert-backdrop { animation: none !important; transition: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sprite SVG tunggal -->
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
            var lastFocused = null;

            var ICONS = {
                success: { icon: '#icon-check', cls: 'is-success' },
                error:   { icon: '#icon-x', cls: 'is-error' },
                warning: { icon: '#icon-alert-triangle', cls: 'is-warning' },
                info:    { icon: '#icon-info', cls: 'is-info' }
            };

            function closeAlert() {
                modal.classList.remove('is-open');
                backdrop.classList.remove('is-open');
                document.body.style.overflow = '';
                clearTimeout(closeTimer);
                if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
            }

            function openAlert(title, html, type, autoCloseMs) {
                clearTimeout(closeTimer);
                var cfg = ICONS[type] || ICONS.info;
                lastFocused = document.activeElement;

                iconWrap.className = 'premium-alert-icon-wrap ' + cfg.cls;
                iconUse.setAttribute('href', cfg.icon);
                titleEl.textContent = title;
                msgEl.innerHTML = html;

                modal.classList.add('is-open');
                backdrop.classList.add('is-open');
                document.body.style.overflow = 'hidden';

                progressEl.style.animation = 'none';
                void progressEl.offsetWidth;
                progressEl.style.animation = 'premiumAlertShrink ' + autoCloseMs + 'ms linear forwards';

                confirmBtn.focus();
                closeTimer = setTimeout(closeAlert, autoCloseMs);
            }

            closeBtn.addEventListener('click', closeAlert);
            confirmBtn.addEventListener('click', closeAlert);
            backdrop.addEventListener('click', closeAlert);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.classList.contains('is-open')) closeAlert();
            });

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
    <script>
    document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
    document.onkeydown = function(e) {
        if (
            e.keyCode === 123 ||
            (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) ||
            (e.ctrlKey && e.keyCode === 85)
        ) {
            e.preventDefault();
            return false;
        }
    };
    </script>
</body>
</html>