<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PosyanduCare')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        referrerpolicy="no-referrer"
    >

    <style>
        :root {
            --pc-green-900: #064e3b;
            --pc-green-800: #065f46;
            --pc-green-700: #047857;
            --pc-green-600: #059669;
            --pc-green-500: #10b981;
            --pc-amber-400: #fbbf24;
            --pc-amber-500: #f59e0b;
            --pc-slate-900: #0f172a;
            --pc-slate-700: #334155;
            --pc-slate-500: #64748b;
            --pc-slate-400: #94a3b8;
            --pc-slate-200: #e2e8f0;
            --pc-bg: #f8fffc;
            --pc-ease: cubic-bezier(.16, 1, .3, 1);
        }

        * {
            box-sizing: border-box;
        }

        html {
            width: 100%;
            min-height: 100%;
            scroll-behavior: smooth;
            -webkit-text-size-adjust: 100%;
        }

        body {
            width: 100%;
            min-height: 100svh;
            margin: 0;
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--pc-slate-900);
            background:
                radial-gradient(circle at 12% 12%, rgba(16, 185, 129, .12), transparent 28%),
                radial-gradient(circle at 88% 10%, rgba(245, 158, 11, .11), transparent 26%),
                radial-gradient(circle at 50% 96%, rgba(14, 165, 233, .075), transparent 32%),
                linear-gradient(135deg, #f8fffc 0%, #f8fafc 56%, #fffaf0 100%);
            overflow-x: hidden;
            text-rendering: geometricPrecision;
            -webkit-font-smoothing: antialiased;
        }

        body.auth-submitting {
            cursor: wait;
        }

        a {
            color: inherit;
        }

        button,
        input {
            font-family: inherit;
        }

        [x-cloak] {
            display: none !important;
        }

        .auth-bg-soft {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .auth-bg-soft::before,
        .auth-bg-soft::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .auth-bg-soft::before {
            width: 430px;
            height: 430px;
            top: -190px;
            left: -180px;
            background: rgba(16, 185, 129, .11);
            filter: blur(72px);
        }

        .auth-bg-soft::after {
            width: 390px;
            height: 390px;
            right: -170px;
            bottom: -160px;
            background: rgba(245, 158, 11, .105);
            filter: blur(74px);
        }

        .auth-grid-soft {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            opacity: .07;
            background-image:
                linear-gradient(rgba(15, 23, 42, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15, 23, 42, .04) 1px, transparent 1px);
            background-size: 72px 72px;
        }

        .auth-main {
            position: relative;
            z-index: 5;
            min-height: 100svh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /*
        |--------------------------------------------------------------------------
        | FAST LOGIN LOADER
        |--------------------------------------------------------------------------
        | Loading screen tetap sama: orbit, heart pulse, dots.
        | Durasi dipangkas supaya HP tidak mengira sedang memproses sensus nasional.
        */

        #pcAuthLoader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            visibility: hidden;
            opacity: 0;
            pointer-events: none;
            transition:
                opacity .18s ease,
                visibility .18s ease;
        }

        #pcAuthLoader.show {
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
        }

        .pc-auth-loader-veil {
            position: absolute;
            inset: 0;
            background: rgba(240, 255, 248, .88);
            backdrop-filter: blur(13px) saturate(1.18);
            -webkit-backdrop-filter: blur(13px) saturate(1.18);
        }

        .pc-auth-loader-panel {
            position: relative;
            z-index: 2;
            width: min(86vw, 265px);
            min-height: 188px;
            padding: 30px 26px 26px;
            border-radius: 34px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .90), rgba(255, 255, 255, .72));
            border: 1px solid rgba(255, 255, 255, .82);
            box-shadow:
                0 28px 70px rgba(15, 23, 42, .12),
                inset 0 1px 0 rgba(255, 255, 255, .90);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transform: translate3d(0, 10px, 0) scale(.96);
            opacity: 0;
        }

        #pcAuthLoader.show .pc-auth-loader-panel {
            animation: authLoaderIn .24s var(--pc-ease) forwards;
        }

        .pc-auth-orbit {
            position: relative;
            width: 68px;
            height: 68px;
            margin-bottom: 14px;
            display: grid;
            place-items: center;
        }

        .pc-auth-ring {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            border: 2px solid transparent;
            border-top-color: var(--pc-green-600);
            border-right-color: rgba(16, 185, 129, .35);
            animation: authSpin .68s linear infinite;
        }

        .pc-auth-ring:nth-child(2) {
            inset: 7px;
            border-top-color: var(--pc-amber-500);
            border-right-color: rgba(245, 158, 11, .28);
            animation-duration: .88s;
            animation-direction: reverse;
        }

        .pc-auth-ring:nth-child(3) {
            inset: 14px;
            border-top-color: rgba(5, 150, 105, .65);
            border-right-color: rgba(5, 150, 105, .20);
            animation-duration: .74s;
        }

        .pc-auth-heart {
            position: relative;
            z-index: 2;
            width: 38px;
            height: 38px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            color: #ffffff;
            background:
                linear-gradient(135deg, var(--pc-green-700), var(--pc-green-500));
            box-shadow: 0 10px 25px rgba(5, 150, 105, .22);
            animation: authHeart .82s ease-in-out infinite;
        }

        .pc-auth-loader-name {
            color: var(--pc-green-900);
            font-size: 15px;
            font-weight: 900;
            letter-spacing: -.02em;
            line-height: 1;
        }

        .pc-auth-loader-label {
            margin-top: 7px;
            color: var(--pc-slate-500);
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
        }

        .pc-auth-dots {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-top: 15px;
        }

        .pc-auth-dot {
            width: 5px;
            height: 5px;
            border-radius: 999px;
            background: var(--pc-green-500);
            opacity: .35;
            animation: authDot .78s ease-in-out infinite;
        }

        .pc-auth-dot:nth-child(2) {
            animation-delay: .10s;
        }

        .pc-auth-dot:nth-child(3) {
            animation-delay: .20s;
        }

        .pc-auth-dot:nth-child(4) {
            animation-delay: .30s;
        }

        @keyframes authLoaderIn {
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        @keyframes authSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes authHeart {
            0%, 100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.06);
            }
        }

        @keyframes authDot {
            0%, 100% {
                opacity: .28;
                transform: translateY(0);
            }

            50% {
                opacity: 1;
                transform: translateY(-3px);
            }
        }

        .custom-swal-popup {
            border-radius: 28px !important;
            padding: 2rem !important;
            font-family: "Plus Jakarta Sans", sans-serif !important;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .16) !important;
        }

        .custom-swal-title {
            color: var(--pc-slate-900) !important;
            font-size: 1.2rem !important;
            font-weight: 900 !important;
        }

        .custom-swal-html {
            color: var(--pc-slate-500) !important;
            font-size: .92rem !important;
            font-weight: 650 !important;
        }

        .custom-swal-confirm-btn {
            border: 0 !important;
            border-radius: 16px !important;
            padding: .8rem 1.25rem !important;
            background: linear-gradient(135deg, var(--pc-green-700), var(--pc-green-500)) !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            cursor: pointer !important;
        }

        @media (max-width: 768px) {
            body {
                background:
                    radial-gradient(circle at 18% 6%, rgba(16, 185, 129, .10), transparent 30%),
                    radial-gradient(circle at 90% 18%, rgba(245, 158, 11, .09), transparent 26%),
                    linear-gradient(135deg, #f8fffc 0%, #f8fafc 66%, #fffaf0 100%);
            }

            .auth-main {
                padding: 12px;
                align-items: center;
            }

            .auth-grid-soft {
                display: none;
            }

            .auth-bg-soft::before,
            .auth-bg-soft::after {
                filter: blur(46px);
            }

            .pc-auth-loader-veil {
                background: rgba(240, 255, 248, .82);
                backdrop-filter: blur(7px);
                -webkit-backdrop-filter: blur(7px);
            }

            .pc-auth-loader-panel {
                width: min(84vw, 232px);
                min-height: 164px;
                padding: 25px 22px 22px;
                border-radius: 30px;
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
            }

            .pc-auth-orbit {
                width: 58px;
                height: 58px;
                margin-bottom: 12px;
            }

            .pc-auth-heart {
                width: 34px;
                height: 34px;
                border-radius: 14px;
            }
        }

        @media (max-width: 420px) {
            .auth-main {
                padding: 10px;
            }

            .pc-auth-loader-panel {
                width: min(86vw, 218px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 1ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 1ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="auth-bg-soft"></div>
    <div class="auth-grid-soft"></div>

    <div id="pcAuthLoader" role="status" aria-live="polite" aria-label="Memuat sistem">
        <div class="pc-auth-loader-veil"></div>

        <div class="pc-auth-loader-panel">
            <div class="pc-auth-orbit">
                <div class="pc-auth-ring"></div>
                <div class="pc-auth-ring"></div>
                <div class="pc-auth-ring"></div>

                <div class="pc-auth-heart">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
            </div>

            <div class="pc-auth-loader-name">PosyanduCare</div>
            <div id="pcAuthLoaderLabel" class="pc-auth-loader-label">Memuat Sistem</div>

            <div class="pc-auth-dots">
                <span class="pc-auth-dot"></span>
                <span class="pc-auth-dot"></span>
                <span class="pc-auth-dot"></span>
                <span class="pc-auth-dot"></span>
            </div>
        </div>
    </div>

    <main class="auth-main">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        (function () {
            const loader = document.getElementById('pcAuthLoader');
            const label = document.getElementById('pcAuthLoaderLabel');

            function setLabel(text) {
                if (label) {
                    label.textContent = text || 'Memuat Sistem';
                }
            }

            function show(text) {
                setLabel(text);

                if (loader) {
                    loader.classList.add('show');
                }

                document.body.classList.add('auth-submitting');
            }

            function hide() {
                if (loader) {
                    loader.classList.remove('show');
                }

                document.body.classList.remove('auth-submitting');
            }

            window.PosyanduAuthTransition = {
                show,
                hide,
                play(text) {
                    show(text || 'Membuka Portal');

                    const isMobile = window.matchMedia('(max-width: 768px)').matches;
                    const duration = isMobile ? 90 : 120;

                    return new Promise(function (resolve) {
                        window.setTimeout(resolve, duration);
                    });
                }
            };

            window.addEventListener('pageshow', function () {
                hide();
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>