<!DOCTYPE html>
<html lang="id" class="pc-html">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8fffc">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <title>@yield('title', 'Portal Warga') | PosyanduCare</title>

    {{-- DETEKSI TRANSISI DARI LOGIN --}}
    <script>
        (function () {
            try {
                if (sessionStorage.getItem('pc_from_login') === '1') {
                    document.documentElement.classList.add('pc-from-login');
                } else {
                    document.documentElement.classList.add('pc-normal-entry');
                }
            } catch (e) {
                document.documentElement.classList.add('pc-normal-entry');
            }

            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }
        })();
    </script>

    {{-- FAVICON --}}
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    {{-- FONTS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap"
          rel="stylesheet">

    {{-- TAILWIND --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- ICON --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- SWEET ALERT --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        poppins: ['Poppins', 'sans-serif']
                    },
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --green-900: #064e3b;
            --green-800: #065f46;
            --green-700: #047857;
            --green-600: #059669;
            --green-500: #10b981;
            --green-400: #34d399;

            --amber-500: #f59e0b;

            --slate-950: #020617;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;

            --ease-premium: cubic-bezier(.16, 1, .3, 1);
            --ease-smooth: cubic-bezier(.22, 1, .36, 1);

            --dock-height: 82px;
            --topbar-height: 72px;
            --user-sidebar-width: 292px;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            width: 100%;
            height: 100%;
            overflow: hidden;
            scroll-behavior: auto !important;
        }

        body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;

            overflow: hidden;

            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--slate-800);

            background:
                radial-gradient(circle at top left, rgba(16,185,129,.12), transparent 34%),
                radial-gradient(circle at 95% 10%, rgba(245,158,11,.07), transparent 28%),
                radial-gradient(circle at bottom right, rgba(20,184,166,.13), transparent 35%),
                linear-gradient(135deg, #f8fffc 0%, #f8fafc 46%, #effbf6 100%);

            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;

            overscroll-behavior: none;
        }

        body.user-lock {
            overflow: hidden !important;
            height: 100dvh !important;
            touch-action: none !important;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .font-poppins {
            font-family: 'Poppins', sans-serif;
        }

        button,
        input,
        textarea,
        select {
            font-family: inherit;
        }

        a {
            color: inherit;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        ::selection {
            background: rgba(16,185,129,.18);
            color: var(--green-900);
        }

        /*
        |--------------------------------------------------------------------------
        | BACKGROUND
        |--------------------------------------------------------------------------
        */

        .user-bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .user-bg-layer::before {
            content: "";
            position: absolute;
            width: 420px;
            height: 420px;
            top: -190px;
            left: -180px;
            border-radius: 999px;
            background: rgba(16,185,129,.16);
            filter: blur(64px);
        }

        .user-bg-layer::after {
            content: "";
            position: absolute;
            width: 380px;
            height: 380px;
            right: -180px;
            bottom: -180px;
            border-radius: 999px;
            background: rgba(20,184,166,.15);
            filter: blur(62px);
        }

        .user-dot-pattern {
            position: fixed;
            top: 92px;
            right: 18px;
            width: 88px;
            height: 88px;
            opacity: .12;
            z-index: 1;
            pointer-events: none;
            background-image: radial-gradient(rgba(16,185,129,.52) 1.1px, transparent 1.1px);
            background-size: 9px 9px;
        }

        /*
        |--------------------------------------------------------------------------
        | LOADING SCREEN CEPAT, SAMA DENGAN LOGIN/ADMIN/BIDAN/KADER
        |--------------------------------------------------------------------------
        */

        #pcUserLoader {
            position: fixed;
            inset: 0;
            z-index: 99999;

            display: flex;
            align-items: center;
            justify-content: center;

            visibility: hidden;
            pointer-events: none;
        }

        #pcUserLoader.show {
            visibility: visible;
            pointer-events: auto;
        }

        .ld-veil {
            position: absolute;
            inset: 0;

            background: rgba(240,255,248,.78);
            backdrop-filter: blur(9px) saturate(1.12);
            -webkit-backdrop-filter: blur(9px) saturate(1.12);

            opacity: 0;
            transition: opacity .18s ease;
        }

        #pcUserLoader.show .ld-veil {
            opacity: 1;
        }

        .ld-panel {
            position: relative;
            z-index: 2;

            min-width: 236px;
            padding: 30px 40px 28px;
            border-radius: 24px;

            background: rgba(255,255,255,.96);
            border: 1px solid rgba(16,185,129,.13);

            box-shadow:
                0 22px 54px rgba(15,23,42,.12),
                inset 0 1px 0 rgba(255,255,255,.92);

            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;

            opacity: 0;
            transform: translateY(12px) scale(.96);

            transition:
                opacity .24s var(--ease-smooth) .04s,
                transform .24s var(--ease-smooth) .04s;

            will-change: opacity, transform;
        }

        #pcUserLoader.show .ld-panel {
            opacity: 1;
            transform: none;
        }

        .ld-orbit {
            position: relative;

            width: 62px;
            height: 62px;
            margin: 0 auto 17px;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ld-ring {
            position: absolute;
            inset: 0;

            border-radius: 50%;
            border: 2.25px solid transparent;

            will-change: transform;
        }

        .ld-ring:nth-child(1) {
            border-top-color: var(--green-500);
            border-right-color: rgba(16,185,129,.25);
            animation: spinR .78s linear infinite;
        }

        .ld-ring:nth-child(2) {
            inset: 8px;
            border-bottom-color: var(--green-400);
            border-left-color: rgba(52,211,153,.25);
            animation: spinR 1.15s linear infinite reverse;
        }

        .ld-ring:nth-child(3) {
            inset: 17px;
            border-top-color: var(--amber-500);
            border-right-color: rgba(245,158,11,.22);
            animation: spinR 1.65s linear infinite;
        }

        @keyframes spinR {
            to { transform: rotate(360deg); }
        }

        .ld-heart {
            position: relative;
            z-index: 2;

            font-size: 17px;
            color: var(--green-600);

            animation: heartBeat 1.08s ease-in-out infinite;
            will-change: transform;
        }

        @keyframes heartBeat {
            0%,100% { transform: scale(1); opacity: .9; }
            18%     { transform: scale(1.16); }
            36%     { transform: scale(1); }
            52%     { transform: scale(1.07); }
        }

        .ld-name {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 2px;
        }

        .ld-label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: 14px;
        }

        .ld-dots {
            display: flex;
            gap: 5px;
            align-items: center;
            justify-content: center;
        }

        .ld-dot {
            width: 6px;
            height: 6px;

            border-radius: 50%;
            background: var(--green-400);

            animation: dotPop .72s ease-in-out infinite both;
            will-change: transform, opacity;
        }

        .ld-dot:nth-child(1) { animation-delay: 0s; }
        .ld-dot:nth-child(2) { animation-delay: .12s; background: var(--green-500); }
        .ld-dot:nth-child(3) { animation-delay: .24s; background: var(--green-600); }
        .ld-dot:nth-child(4) { animation-delay: .36s; background: var(--amber-500); }

        @keyframes dotPop {
            0%,80%,100% { transform: scale(.55); opacity: .35; }
            40%         { transform: scale(1.12); opacity: 1; }
        }

        /*
        |--------------------------------------------------------------------------
        | PAGE LOADER BAR
        |--------------------------------------------------------------------------
        */

        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;

            height: 3px;
            z-index: 99998;

            overflow: hidden;
            opacity: 0;
            transform: translateY(-3px);
            pointer-events: none;

            transition:
                opacity .14s ease,
                transform .14s ease;
        }

        body.is-navigating .page-loader {
            opacity: 1;
            transform: translateY(0);
        }

        .page-loader::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;

            width: 46%;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(16,185,129,0), var(--green-500), rgba(52,211,153,.55));

            animation: loadingBar .72s infinite cubic-bezier(.76, 0, .24, 1);
        }

        @keyframes loadingBar {
            0%   { transform: translateX(-115%); }
            100% { transform: translateX(265%); }
        }

        .route-overlay {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | MAIN APP MOBILE FIRST
        |--------------------------------------------------------------------------
        */

        .user-shell {
            position: relative;
            z-index: 10;

            width: 100%;
            height: 100dvh;
            min-height: 100dvh;

            overflow: hidden;
        }

        .user-app {
            position: relative;
            z-index: 10;

            width: 100%;
            height: 100dvh;
            min-height: 100dvh;

            display: flex;
            flex-direction: column;

            overflow: hidden;

            opacity: 0;
            transform: translateY(10px);

            animation: userAppEnter .34s var(--ease-smooth) forwards;
        }

        @keyframes userAppEnter {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        html.pc-from-login .user-app {
            opacity: 0;
            transform: translateY(16px);
            filter: none;
            animation: none;
        }

        html.pc-from-login.pc-content-in .user-app {
            opacity: 1;
            transform: translateY(0);
            filter: blur(0);

            transition:
                opacity .34s var(--ease-smooth),
                transform .34s var(--ease-smooth),
                filter .34s var(--ease-smooth);
        }

        /*
        |--------------------------------------------------------------------------
        | TOPBAR DIAM DI ATAS
        |--------------------------------------------------------------------------
        */

        .user-topbar {
            position: relative;
            z-index: 40;

            flex-shrink: 0;

            min-height: var(--topbar-height);

            padding:
                calc(10px + env(safe-area-inset-top))
                14px
                10px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            background:
                linear-gradient(135deg, rgba(255,255,255,.94), rgba(255,255,255,.80));

            border-bottom: 1px solid rgba(226,232,240,.76);

            backdrop-filter: blur(14px) saturate(1.08);
            -webkit-backdrop-filter: blur(14px) saturate(1.08);

            box-shadow: 0 12px 30px rgba(15,23,42,.045);
        }

        .user-topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .user-menu-button,
        .user-icon-button {
            width: 42px;
            height: 42px;

            border: 1px solid rgba(226,232,240,.86);
            border-radius: 16px;

            background: rgba(255,255,255,.86);
            color: var(--slate-600);

            display: flex;
            align-items: center;
            justify-content: center;

            box-shadow:
                0 10px 22px rgba(15,23,42,.045),
                inset 0 1px 0 rgba(255,255,255,.88);

            transition: all .16s var(--ease-premium);
        }

        .user-menu-button:active,
        .user-icon-button:active {
            transform: scale(.94);
        }

        .user-menu-button:hover,
        .user-icon-button:hover {
            color: var(--green-700);
            border-color: rgba(16,185,129,.28);
            background: white;
        }

        .user-title-block {
            min-width: 0;
        }

        .user-page-title {
            margin: 0;

            max-width: 190px;

            color: var(--slate-900);

            font-size: 16.5px;
            font-family: 'Poppins', sans-serif;
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -0.035em;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-page-subtitle {
            margin-top: 4px;

            color: var(--green-700);

            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .14em;
        }

        .user-topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-notif-area {
            position: relative;
        }

        .notif-dot,
        .notif-dot-pulse {
            position: absolute;
            top: 9px;
            right: 9px;

            width: 10px;
            height: 10px;

            border-radius: 999px;
            background: #f43f5e;
            border: 2px solid white;
        }

        .notif-dot-pulse {
            animation: notifPulse 1.4s infinite;
            border: 0;
            opacity: .55;
        }

        @keyframes notifPulse {
            0% {
                transform: scale(.8);
                opacity: .65;
            }

            80%,
            100% {
                transform: scale(2.1);
                opacity: 0;
            }
        }

        .mobile-profile-bubble {
            width: 42px;
            height: 42px;

            border-radius: 16px;

            background: linear-gradient(135deg, var(--green-700), var(--green-500));
            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 13px;
            font-weight: 900;
            text-decoration: none;

            box-shadow:
                0 12px 26px rgba(16,185,129,.22),
                inset 0 1px 0 rgba(255,255,255,.22);
        }

        /*
        |--------------------------------------------------------------------------
        | NOTIF DROPDOWN
        |--------------------------------------------------------------------------
        */

        .notif-dropdown {
            position: fixed;

            top: calc(76px + env(safe-area-inset-top));
            left: 12px;
            right: 12px;

            z-index: 70;

            max-height: min(440px, calc(100dvh - 110px));

            border-radius: 26px;
            background: rgba(255,255,255,.96);
            border: 1px solid rgba(226,232,240,.90);

            box-shadow:
                0 28px 80px rgba(15,23,42,.16),
                inset 0 1px 0 rgba(255,255,255,.88);

            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);

            overflow: hidden;

            opacity: 0;
            visibility: hidden;
            pointer-events: none;

            transform: translateY(14px) scale(.98);

            transition:
                opacity .18s var(--ease-premium),
                transform .18s var(--ease-premium),
                visibility .18s var(--ease-premium);
        }

        .notif-dropdown.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .notif-head {
            padding: 16px 16px 13px;

            border-bottom: 1px solid rgba(241,245,249,.9);

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            background: rgba(248,250,252,.74);
        }

        .notif-head-title {
            margin: 0;

            color: var(--slate-900);

            font-size: 14px;
            font-weight: 900;
            font-family: 'Poppins', sans-serif;
        }

        .notif-status {
            padding: 5px 9px;

            border-radius: 999px;
            background: var(--slate-100);
            color: var(--slate-500);

            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .08em;
            white-space: nowrap;
        }

        .notif-status.is-new {
            background: #fff1f2;
            color: #e11d48;
        }

        .notif-list {
            max-height: 330px;
            overflow-y: auto;
            background: rgba(255,255,255,.56);
        }

        .notif-footer {
            padding: 13px 16px;

            border-top: 1px solid rgba(241,245,249,.9);

            background: rgba(248,250,252,.74);

            text-align: center;
        }

        .notif-footer a {
            color: var(--green-700);

            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .13em;
            text-decoration: none;
        }

        /*
        |--------------------------------------------------------------------------
        | SIDEBAR MOBILE
        |--------------------------------------------------------------------------
        */

        .user-sidebar-overlay {
            position: fixed;
            inset: 0;

            z-index: 80;

            background: rgba(2,6,23,.38);

            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);

            opacity: 0;
            visibility: hidden;
            pointer-events: none;

            transition:
                opacity .18s ease,
                visibility .18s ease;
        }

        .user-sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .user-sidebar-wrap {
            position: fixed;
            inset: 10px auto 10px 10px;

            width: min(292px, calc(100vw - 22px));

            z-index: 90;

            transform: translateX(calc(-100% - 18px));
            opacity: .6;
            filter: none;
            pointer-events: none;

            transition:
                transform .24s var(--ease-premium),
                opacity .18s ease;
        }

        .user-sidebar-wrap.show {
            transform: translateX(0);
            opacity: 1;
            filter: blur(0);
            pointer-events: auto;
        }

        /*
        |--------------------------------------------------------------------------
        | KONTEN MOBILE YANG SCROLL
        |--------------------------------------------------------------------------
        */

        .user-main {
            position: relative;
            z-index: 10;

            flex: 1;
            min-height: 0;

            width: 100%;

            overflow-y: auto;
            overflow-x: hidden;

            -webkit-overflow-scrolling: touch;
            overscroll-behavior-y: contain;

            padding:
                16px
                14px
                calc(var(--dock-height) + 38px + env(safe-area-inset-bottom));

            scroll-behavior: auto !important;

            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .user-main::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        .user-main-inner {
            max-width: 1040px;
            margin: 0 auto;
        }

        .user-main-inner > * {
            opacity: 0;
            transform: translateY(18px);
            filter: none;

            animation: contentUp .34s var(--ease-smooth) forwards;
            animation-delay: calc(min(var(--content-index, 0), 8) * 30ms);
        }

        @keyframes contentUp {
            to {
                opacity: 1;
                transform: translateY(0);
                filter: blur(0);
            }
        }

        .mobile-card,
        .user-card,
        .dashboard-card,
        .content-card,
        .table-card {
            border-radius: 24px;
            background: rgba(255,255,255,.84);
            border: 1px solid rgba(226,232,240,.82);
            box-shadow:
                0 18px 44px rgba(15,23,42,.055),
                inset 0 1px 0 rgba(255,255,255,.84);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /*
        |--------------------------------------------------------------------------
        | BOTTOM DOCK DIAM DI BAWAH MOBILE
        |--------------------------------------------------------------------------
        */

        .mobile-dock {
            position: fixed;

            left: 12px;
            right: 12px;
            bottom: calc(10px + env(safe-area-inset-bottom));

            z-index: 60;

            height: 72px;
            padding: 8px 10px;

            border-radius: 28px;

            background: rgba(255,255,255,.92);
            border: 1px solid rgba(255,255,255,.96);

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;

            box-shadow:
                0 22px 60px rgba(15,23,42,.14),
                0 6px 18px rgba(16,185,129,.07),
                inset 0 1px 0 rgba(255,255,255,.95);

            backdrop-filter: blur(16px) saturate(1.10);
            -webkit-backdrop-filter: blur(16px) saturate(1.10);

            transform: translateZ(0);
            will-change: transform;
        }

        .dock-link {
            position: relative;

            flex: 1;
            min-width: 0;

            height: 54px;

            border-radius: 18px;

            color: var(--slate-400);
            text-decoration: none;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;

            transition: all .16s var(--ease-premium);
        }

        .dock-link i {
            font-size: 16px;
        }

        .dock-link span {
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
            line-height: 1;
        }

        .dock-link.active {
            color: var(--green-700);
            background: #ecfdf5;
        }

        .dock-link.active::before {
            content: "";
            position: absolute;
            top: 5px;

            width: 5px;
            height: 5px;

            border-radius: 999px;
            background: var(--green-500);

            box-shadow: 0 0 0 4px rgba(16,185,129,.10);
        }

        .dock-center {
            position: relative;

            flex: 1;
            min-width: 0;
            height: 54px;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dock-action {
            position: absolute;
            top: -25px;

            width: 58px;
            height: 58px;

            border-radius: 999px;

            background:
                linear-gradient(135deg, var(--green-700), var(--green-500));

            color: white;
            text-decoration: none;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 4px solid rgba(255,255,255,.96);

            box-shadow:
                0 18px 34px rgba(16,185,129,.34),
                inset 0 1px 0 rgba(255,255,255,.24);

            transition: all .16s var(--ease-premium);
        }

        .dock-action:active {
            transform: scale(.94);
        }

        .dock-action i {
            font-size: 20px;
        }

        .dock-center-label {
            position: absolute;
            bottom: -5px;

            color: var(--green-700);

            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        /*
        |--------------------------------------------------------------------------
        | ANIMASI MASUK
        |--------------------------------------------------------------------------
        */

        .pc-animate-topbar {
            opacity: 0;
            transform: translateY(-22px);
            animation: topbarIn .32s var(--ease-smooth) forwards;
            animation-delay: .03s;
        }

        .pc-animate-main {
            opacity: 0;
            transform: translateY(24px);
            animation: mainIn .34s var(--ease-smooth) forwards;
            animation-delay: .05s;
        }

        .pc-animate-dock {
            opacity: 0;
            transform: translateY(24px) translateZ(0);
            animation: dockIn .34s var(--ease-smooth) forwards;
            animation-delay: .07s;
        }

        @keyframes topbarIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes mainIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes dockIn {
            to {
                opacity: 1;
                transform: translateY(0) translateZ(0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DESKTOP / TABLET
        |--------------------------------------------------------------------------
        | Desktop sidebar tampil permanen. Mobile tetap off-canvas.
        |--------------------------------------------------------------------------
        */

        @media (min-width: 768px) {
            html {
                height: auto;
                min-height: 100%;
                overflow-x: hidden;
                overflow-y: auto;
            }

            body {
                height: auto;
                min-height: 100vh;
                overflow-x: hidden;
                overflow-y: auto;
                overscroll-behavior-y: auto;
            }

            body.user-lock {
                overflow: hidden !important;
                height: 100vh !important;
            }

            .user-sidebar-wrap {
                position: fixed;

                top: 14px;
                left: 14px;
                bottom: 14px;
                right: auto;

                width: var(--user-sidebar-width);

                z-index: 70;

                transform: translateX(0);
                opacity: 1;
                filter: blur(0);
                pointer-events: auto;

                animation: desktopSidebarIn .32s var(--ease-smooth) both;
            }

            @keyframes desktopSidebarIn {
                from {
                    opacity: 0;
                    transform: translateX(-34px);
                    filter: blur(8px);
                }

                to {
                    opacity: 1;
                    transform: translateX(0);
                    filter: blur(0);
                }
            }

            .user-sidebar-overlay {
                display: none !important;
            }

            .user-shell {
                height: auto;
                min-height: 100vh;
                overflow: visible;

                padding-left: calc(var(--user-sidebar-width) + 28px);
            }

            .user-app {
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }

            .user-topbar {
                position: sticky;
                top: 0;

                min-height: 84px;

                margin: 14px 18px 0 0;
                padding: 14px 28px;

                border-radius: 28px;

                border: 1px solid rgba(226,232,240,.82);

                background:
                    linear-gradient(135deg, rgba(255,255,255,.92), rgba(255,255,255,.76));

                box-shadow:
                    0 22px 55px rgba(15,23,42,.065),
                    inset 0 1px 0 rgba(255,255,255,.90);
            }

            .user-menu-button {
                display: none;
            }

            .user-page-title {
                max-width: 420px;
                font-size: 21px;
            }

            .user-page-subtitle {
                display: block;
            }

            .user-main {
                flex: none;
                min-height: calc(100vh - 98px);

                overflow: visible;

                padding: 34px 32px 52px 18px;
            }

            .user-main-inner {
                max-width: 1180px;
            }

            .mobile-dock {
                display: none;
            }

            .notif-dropdown {
                position: absolute;
                top: calc(100% + 12px);
                left: auto;
                right: 0;

                width: 380px;
                max-height: 480px;
            }
        }

        @media (max-width: 380px) {
            .user-main {
                padding-left: 12px;
                padding-right: 12px;
            }

            .mobile-dock {
                left: 8px;
                right: 8px;

                height: 70px;

                border-radius: 24px;

                padding-left: 7px;
                padding-right: 7px;
            }

            .dock-link span,
            .dock-center-label {
                font-size: 8px;
            }

            .dock-action {
                width: 56px;
                height: 56px;
            }

            .user-page-title {
                max-width: 160px;
            }
        }

        @media (max-width: 390px) {
            .ld-panel {
                min-width: unset;
                width: 86vw;
                padding: 26px 22px 24px;
            }

            .ld-orbit {
                width: 58px;
                height: 58px;
                margin-bottom: 15px;
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

@php
    $userName = Auth::user()->name ?? 'Warga';
    $initial = strtoupper(substr($userName, 0, 1));
@endphp

{{-- LOADING SCREEN: sama gaya dengan Login/Admin/Bidan/Kader --}}
<div id="pcUserLoader" role="status" aria-label="Memuat, harap tunggu..." aria-live="polite">
    <div class="ld-veil"></div>
    <div class="ld-panel">
        <div class="ld-orbit">
            <div class="ld-ring"></div>
            <div class="ld-ring"></div>
            <div class="ld-ring"></div>
            <i class="fa-solid fa-heart-pulse ld-heart"></i>
        </div>

        <div class="ld-name">PosyanduCare</div>
        <div id="pcUserLoaderLabel" class="ld-label">Memuat Halaman</div>

        <div class="ld-dots">
            <span class="ld-dot"></span>
            <span class="ld-dot"></span>
            <span class="ld-dot"></span>
            <span class="ld-dot"></span>
        </div>
    </div>
</div>

{{-- BACKGROUND --}}
<div class="user-bg-layer" aria-hidden="true"></div>
<div class="user-dot-pattern" aria-hidden="true"></div>

{{-- ROUTE LOADER --}}
<div class="page-loader" aria-hidden="true"></div>
<div class="route-overlay" aria-hidden="true"></div>

{{-- SIDEBAR OVERLAY MOBILE --}}
<div
    id="sidebarOverlay"
    class="user-sidebar-overlay"
    onclick="closeUserSidebar()"
    aria-hidden="true"
></div>

{{-- SIDEBAR --}}
<div id="userSidebarWrap" class="user-sidebar-wrap">
    @include('partials.sidebar.user')
</div>

{{-- APP --}}
<div class="user-shell">
    <div class="user-app">

        {{-- TOPBAR --}}
        <header class="user-topbar pc-animate-topbar">
            <div class="user-topbar-left">
                <button
                    type="button"
                    onclick="openUserSidebar()"
                    class="user-menu-button"
                    aria-label="Buka menu"
                >
                    <i class="fa-solid fa-bars-staggered text-[15px]"></i>
                </button>

                <div class="user-title-block">
                    <h1 class="user-page-title">
                        @yield('page_title', 'Beranda')
                    </h1>

                    <div class="user-page-subtitle">
                        Portal Warga Aktif
                    </div>
                </div>
            </div>

            <div class="user-topbar-right">

                {{-- NOTIF --}}
                <div class="user-notif-area" id="notifArea">
                    <button
                        type="button"
                        onclick="toggleNotif()"
                        id="notifBtn"
                        class="user-icon-button"
                        aria-label="Buka notifikasi"
                    >
                        <i class="fa-regular fa-bell text-[16px]"></i>

                        <span id="notifBadge" class="notif-dot hidden"></span>
                        <span id="notifBadgePulse" class="notif-dot-pulse hidden"></span>
                    </button>

                    <div id="notifDropdown" class="notif-dropdown">
                        <div class="notif-head">
                            <h3 class="notif-head-title">
                                Pemberitahuan
                            </h3>

                            <span id="notifStatus" class="notif-status">
                                Sinkronisasi
                            </span>
                        </div>

                        <div id="notifList" class="notif-list no-scrollbar">
                            <div class="py-12 text-center flex flex-col items-center">
                                <i class="fa-solid fa-circle-notch fa-spin text-emerald-500 text-2xl mb-3"></i>
                                <p class="text-[11px] font-semibold text-slate-400">
                                    Memuat data...
                                </p>
                            </div>
                        </div>

                        <div class="notif-footer">
                            <a href="{{ route('user.notifikasi.index') }}" class="js-nav-link">
                                Lihat Semua Notifikasi
                            </a>
                        </div>
                    </div>
                </div>

                {{-- PROFILE --}}
                <a
                    href="{{ route('user.profile.edit') }}"
                    class="mobile-profile-bubble js-nav-link"
                    aria-label="Profil"
                >
                    {{ $initial }}
                </a>
            </div>
        </header>

        {{-- MAIN CONTENT --}}
        <main class="user-main pc-animate-main" id="userMainScrollArea">
            <div class="user-main-inner">
                @yield('content')
            </div>
        </main>

        {{-- BOTTOM DOCK MOBILE --}}
        <nav class="mobile-dock pc-animate-dock" aria-label="Navigasi utama warga">
            <a
                href="{{ route('user.dashboard') }}"
                class="dock-link js-nav-link {{ request()->routeIs('user.dashboard*') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-house"></i>
                <span>Home</span>
            </a>

            <a
                href="{{ route('user.jadwal.index') }}"
                class="dock-link js-nav-link {{ request()->routeIs('user.jadwal*') ? 'active' : '' }}"
            >
                <i class="fa-regular fa-calendar-days"></i>
                <span>Jadwal</span>
            </a>

            <div class="dock-center">
                <a
                    href="{{ route('user.monitoring.index') }}"
                    class="dock-action js-nav-link"
                    aria-label="Pantau kesehatan"
                >
                    <i class="fa-solid fa-chart-line"></i>
                </a>

                <span class="dock-center-label">
                    Pantau
                </span>
            </div>

            <a
                href="{{ route('user.riwayat.index') }}"
                class="dock-link js-nav-link {{ request()->routeIs('user.riwayat*') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Riwayat</span>
            </a>

            <a
                href="{{ route('user.profile.edit') }}"
                class="dock-link js-nav-link {{ request()->routeIs('user.profile*') ? 'active' : '' }}"
            >
                <i class="fa-regular fa-user"></i>
                <span>Profil</span>
            </a>
        </nav>
    </div>
</div>

<script>
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const userSidebarWrap = document.getElementById('userSidebarWrap');

    function isMobileView() {
        return window.innerWidth < 768;
    }

    function applyResponsiveScrollMode() {
        if (isMobileView()) {
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            document.body.style.height = '100%';
        } else {
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            document.body.style.height = '';
            document.body.classList.remove('user-lock');

            if (sidebarOverlay && userSidebarWrap) {
                sidebarOverlay.classList.remove('show');
                userSidebarWrap.classList.remove('show');
            }
        }
    }

    function openUserSidebar() {
        if (!sidebarOverlay || !userSidebarWrap) {
            return;
        }

        if (!isMobileView()) {
            return;
        }

        sidebarOverlay.classList.add('show');
        userSidebarWrap.classList.add('show');

        document.body.classList.add('user-lock');
    }

    function closeUserSidebar() {
        if (!sidebarOverlay || !userSidebarWrap) {
            return;
        }

        sidebarOverlay.classList.remove('show');

        if (isMobileView()) {
            userSidebarWrap.classList.remove('show');
        }

        document.body.classList.remove('user-lock');

        applyResponsiveScrollMode();
    }

    function toggleSidebar() {
        if (userSidebarWrap && userSidebarWrap.classList.contains('show')) {
            closeUserSidebar();
        } else {
            openUserSidebar();
        }
    }

    function toggleNotif() {
        const dropdown = document.getElementById('notifDropdown');

        if (!dropdown) {
            return;
        }

        dropdown.classList.toggle('show');
    }

    function closeNotif() {
        const dropdown = document.getElementById('notifDropdown');

        if (!dropdown) {
            return;
        }

        dropdown.classList.remove('show');
    }

    let __pcUserRouteDelay = null;
    let __pcUserRouteFallback = null;

    function showUserLoader(label = 'Memuat Halaman') {
        const loader = document.getElementById('pcUserLoader');
        const labelEl = document.getElementById('pcUserLoaderLabel');

        if (labelEl) {
            labelEl.textContent = label;
        }

        document.body.classList.add('user-lock');

        if (loader) {
            loader.classList.add('show');
        }
    }

    function hideUserLoader() {
        const loader = document.getElementById('pcUserLoader');

        if (loader) {
            loader.classList.remove('show');
        }

        document.body.classList.remove('user-lock');
    }

    function startNavigation(label = 'Memuat Halaman') {
        clearTimeout(__pcUserRouteDelay);
        clearTimeout(__pcUserRouteFallback);

        __pcUserRouteDelay = setTimeout(function () {
            document.body.classList.add('is-navigating');
            showUserLoader(label);
        }, 70);

        __pcUserRouteFallback = setTimeout(function () {
            stopNavigation();
        }, 4200);
    }

    function stopNavigation() {
        clearTimeout(__pcUserRouteDelay);
        clearTimeout(__pcUserRouteFallback);

        document.body.classList.remove('is-navigating');
        hideUserLoader();
        hideUserLoader();
    }

    function clearStuckState() {
        const html = document.documentElement;

        document.body.classList.remove('user-lock');
        document.body.classList.remove('is-navigating');

        html.classList.remove('pc-loader-out');
        html.classList.remove('pc-content-in');

        closeNotif();

        if (sidebarOverlay && userSidebarWrap) {
            sidebarOverlay.classList.remove('show');

            if (isMobileView()) {
                userSidebarWrap.classList.remove('show');
            }
        }

        applyResponsiveScrollMode();
    }

    window.addEventListener('resize', function () {
        applyResponsiveScrollMode();
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('#notifArea')) {
            closeNotif();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            clearStuckState();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | TOAST
    |--------------------------------------------------------------------------
    */

    window.NexusToast = function (title, body, iconHtml = '') {
        Swal.fire({
            html: `
                <div class="flex items-center gap-4 text-left p-1">
                    <div class="w-12 h-12 rounded-[16px] bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-sm">
                        ${iconHtml || '<i class="fa-solid fa-bell text-[16px]"></i>'}
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-black text-slate-800 font-poppins leading-tight truncate">
                            ${title}
                        </p>

                        <p class="text-[11px] font-medium text-slate-500 mt-0.5 leading-relaxed">
                            ${body}
                        </p>
                    </div>
                </div>
            `,
            position: 'top',
            showConfirmButton: false,
            timer: 4200,
            timerProgressBar: true,
            customClass: {
                popup: 'rounded-[24px] border border-slate-100/70 shadow-[0_20px_50px_-10px_rgba(15,23,42,0.16)] !w-auto min-w-[320px] max-w-[92vw] mt-4 !bg-white/92 !backdrop-blur-xl'
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        const body = document.body;
        const html = document.documentElement;
        const userMain = document.getElementById('userMainScrollArea');

        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        applyResponsiveScrollMode();

        stopNavigation();
        body.classList.remove('user-lock');

        /*
        |--------------------------------------------------------------------------
        | Stagger Content
        |--------------------------------------------------------------------------
        */

        const content = document.querySelector('.user-main-inner');

        if (content) {
            Array.from(content.children).forEach(function (item, index) {
                item.style.setProperty('--content-index', index);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Stop Scroll Leak Khusus Mobile
        |--------------------------------------------------------------------------
        */

        if (userMain) {
            userMain.addEventListener('wheel', function (event) {
                if (!isMobileView()) {
                    return;
                }

                const delta = event.deltaY;
                const atTop = userMain.scrollTop <= 0;
                const atBottom = Math.ceil(userMain.scrollTop + userMain.clientHeight) >= userMain.scrollHeight;

                if ((delta < 0 && atTop) || (delta > 0 && atBottom)) {
                    event.preventDefault();
                }

                event.stopPropagation();
            }, { passive: false });

            let touchStartY = 0;

            userMain.addEventListener('touchstart', function (event) {
                if (!isMobileView()) {
                    return;
                }

                if (event.touches.length > 0) {
                    touchStartY = event.touches[0].clientY;
                }
            }, { passive: true });

            userMain.addEventListener('touchmove', function (event) {
                if (!isMobileView()) {
                    return;
                }

                if (event.touches.length === 0) {
                    return;
                }

                const touchY = event.touches[0].clientY;
                const delta = touchStartY - touchY;

                const atTop = userMain.scrollTop <= 0;
                const atBottom = Math.ceil(userMain.scrollTop + userMain.clientHeight) >= userMain.scrollHeight;

                if ((delta < 0 && atTop) || (delta > 0 && atBottom)) {
                    event.preventDefault();
                }

                event.stopPropagation();
            }, { passive: false });
        }

        /*
        |--------------------------------------------------------------------------
        | Animasi dari Login
        |--------------------------------------------------------------------------
        */

        if (html.classList.contains('pc-from-login')) {
            showUserLoader('Membuka Portal');
            window.scrollTo(0, 0);

            setTimeout(function () {
                html.classList.add('pc-content-in');
            }, 420);

            setTimeout(function () {
                try {
                    sessionStorage.removeItem('pc_from_login');
                } catch (e) {}

                html.classList.remove('pc-from-login');
                html.classList.remove('pc-loader-out');
                html.classList.remove('pc-content-in');
                html.classList.add('pc-normal-entry');

                stopNavigation();
                applyResponsiveScrollMode();
            }, 950);
        } else {
            stopNavigation();
        }

        setTimeout(function () {
            stopNavigation();
            applyResponsiveScrollMode();
        }, 1600);

        /*
        |--------------------------------------------------------------------------
        | Link Loader
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll('a[href]').forEach(function (link) {
            const href = link.getAttribute('href');

            if (!href) {
                return;
            }

            const ignored =
                link.target === '_blank' ||
                href.startsWith('#') ||
                href.startsWith('javascript:') ||
                href.startsWith('mailto:') ||
                href.startsWith('tel:') ||
                link.hasAttribute('download');

            if (ignored) {
                return;
            }

            link.addEventListener('click', function (event) {
                if (
                    event.ctrlKey ||
                    event.metaKey ||
                    event.shiftKey ||
                    event.altKey ||
                    event.defaultPrevented
                ) {
                    return;
                }

                const currentUrl = window.location.href.split('#')[0];
                const targetUrl = link.href.split('#')[0];

                if (currentUrl === targetUrl) {
                    event.preventDefault();
                    clearStuckState();
                    return;
                }

                closeUserSidebar();
                closeNotif();
                startNavigation('Memuat Halaman');
            });
        });

        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                closeUserSidebar();
                closeNotif();
                startNavigation('Memproses Data');
            });
        });

        window.addEventListener('pageshow', function () {
            clearStuckState();
        });

        window.addEventListener('beforeunload', function () {
            body.classList.remove('user-lock');
        });

        /*
        |--------------------------------------------------------------------------
        | Notifikasi
        |--------------------------------------------------------------------------
        */

        let currentCount = -1;

        const badge = document.getElementById('notifBadge');
        const pulse = document.getElementById('notifBadgePulse');
        const list = document.getElementById('notifList');
        const status = document.getElementById('notifStatus');

        function syncNotif() {
            @if(Route::has('user.notifikasi.fetch'))
                fetch("{{ route('user.notifikasi.fetch') }}", {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    const unreadCount = Number(data.unreadCount || 0);

                    if (unreadCount > 0) {
                        badge?.classList.remove('hidden');
                        pulse?.classList.remove('hidden');

                        if (status) {
                            status.innerText = unreadCount + ' Baru';
                            status.classList.add('is-new');
                        }
                    } else {
                        badge?.classList.add('hidden');
                        pulse?.classList.add('hidden');

                        if (status) {
                            status.innerText = 'Terbaca';
                            status.classList.remove('is-new');
                        }
                    }

                    if (data.html && list) {
                        list.innerHTML = data.html;
                    }

                    if (currentCount !== -1 && unreadCount > currentCount) {
                        window.NexusToast(
                            data.latest_title || 'Notifikasi Baru',
                            data.latest_body || 'Ada pembaruan informasi.'
                        );
                    }

                    currentCount = unreadCount;
                })
                .catch(function () {});
            @endif
        }

        syncNotif();
        setInterval(syncNotif, 20000);

        @if(session('success'))
            window.NexusToast(
                'Berhasil',
                "{{ session('success') }}",
                '<i class="fa-solid fa-circle-check text-[16px] text-emerald-600"></i>'
            );
        @endif

        @if(session('error'))
            window.NexusToast(
                'Perhatian',
                "{{ session('error') }}",
                '<i class="fa-solid fa-triangle-exclamation text-[16px] text-rose-600"></i>'
            );
        @endif
    });
</script>

@stack('scripts')
</body>
</html>