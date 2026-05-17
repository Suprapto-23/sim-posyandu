<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') | PosyanduCare</title>

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

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>

    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --font-poppins: 'Poppins', sans-serif;
        }
    </style>

    <style>
        :root {
            --green-950: #052e24;
            --green-900: #064e3b;
            --green-800: #065f46;
            --green-700: #047857;
            --green-600: #059669;
            --green-500: #10b981;
            --green-400: #34d399;

            --amber-500: #f59e0b;
            --amber-400: #fbbf24;

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

            --sidebar-width: 292px;
            --ease-premium: cubic-bezier(.16, 1, .3, 1);
            --ease-smooth: cubic-bezier(.22, 1, .36, 1);
            --ease-gate: cubic-bezier(.76, 0, .24, 1);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            overflow-y: auto;
            scroll-behavior: auto !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--slate-700);
            overscroll-behavior-y: auto;
            background:
                radial-gradient(circle at 7% 0%, rgba(16,185,129,.10), transparent 28%),
                radial-gradient(circle at 95% 7%, rgba(245,158,11,.075), transparent 28%),
                radial-gradient(circle at 85% 92%, rgba(20,184,166,.10), transparent 30%),
                linear-gradient(135deg, #f8fffc 0%, #f8fafc 46%, #effbf6 100%);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body.pc-scroll-lock {
            overflow: hidden !important;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Poppins', sans-serif;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        ::selection {
            background: rgba(16,185,129,.18);
            color: var(--green-900);
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(148,163,184,.50);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100,116,139,.70);
        }

        /* =========================================================
           BACKGROUND
        ========================================================= */

        .admin-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .admin-bg::before {
            content: "";
            position: absolute;
            width: 580px;
            height: 580px;
            left: -250px;
            top: -240px;
            border-radius: 999px;
            background: rgba(16,185,129,.14);
            filter: blur(90px);
        }

        .admin-bg::after {
            content: "";
            position: absolute;
            width: 540px;
            height: 540px;
            right: -240px;
            bottom: -230px;
            border-radius: 999px;
            background: rgba(20,184,166,.12);
            filter: blur(92px);
        }

        .admin-grid-soft {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            opacity: .12;
            background-image:
                linear-gradient(rgba(15,23,42,.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15,23,42,.035) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: radial-gradient(circle at center, black, transparent 72%);
        }

        .admin-dot-pattern {
            position: fixed;
            right: 42px;
            top: 118px;
            width: 98px;
            height: 98px;
            z-index: 2;
            pointer-events: none;
            opacity: .14;
            background-image: radial-gradient(rgba(16,185,129,.50) 1.2px, transparent 1.2px);
            background-size: 10px 10px;
        }

        .admin-leaf-decor {
            position: fixed;
            left: -92px;
            bottom: -100px;
            width: 360px;
            height: 360px;
            z-index: 1;
            opacity: .055;
            transform: rotate(-12deg);
            pointer-events: none;
        }

        .admin-leaf-decor span {
            position: absolute;
            border-radius: 100% 0 100% 0;
            background: linear-gradient(135deg, rgba(4,120,87,.85), rgba(16,185,129,.05));
        }

        .admin-leaf-decor .leaf-1 {
            width: 140px;
            height: 82px;
            left: 48px;
            bottom: 82px;
            transform: rotate(-24deg);
        }

        .admin-leaf-decor .leaf-2 {
            width: 160px;
            height: 92px;
            left: 112px;
            bottom: 142px;
            transform: rotate(-5deg);
        }

        .admin-leaf-decor .leaf-3 {
            width: 126px;
            height: 72px;
            left: 184px;
            bottom: 62px;
            transform: rotate(28deg);
        }

        /* =========================================================
           ENTRY LOADER
        ========================================================= */

        .dashboard-entry-loader {
            position: fixed;
            inset: 0;
            z-index: 99980;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at center, rgba(255,255,255,.19), transparent 34%),
                linear-gradient(135deg, rgba(4,120,87,.80), rgba(5,150,105,.76), rgba(16,185,129,.80));
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .75s ease, visibility .75s ease;
        }

        html.pc-from-login .dashboard-entry-loader {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        html.pc-loader-out .dashboard-entry-loader {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .dashboard-entry-card {
            width: 238px;
            min-height: 182px;
            border-radius: 34px;
            background: rgba(255,255,255,.16);
            border: 1px solid rgba(255,255,255,.32);
            box-shadow:
                0 32px 90px rgba(0,0,0,.18),
                inset 0 1px 0 rgba(255,255,255,.26);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            transform: scale(.94) translateY(16px);
            opacity: 0;
            animation: dashboardLoaderPop .86s var(--ease-smooth) forwards;
        }

        @keyframes dashboardLoaderPop {
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .dashboard-entry-icon {
            width: 76px;
            height: 76px;
            border-radius: 26px;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.30);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 18px 42px rgba(0,0,0,.12),
                inset 0 1px 0 rgba(255,255,255,.24);
            margin-bottom: 15px;
        }

        .dashboard-entry-icon i {
            font-size: 31px;
        }

        .dashboard-entry-title {
            font-size: 13.5px;
            font-weight: 900;
            letter-spacing: .02em;
        }

        .dashboard-entry-line {
            position: relative;
            width: 124px;
            height: 5px;
            margin-top: 16px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(255,255,255,.25);
        }

        .dashboard-entry-line::after {
            content: "";
            position: absolute;
            top: 0;
            left: -55%;
            width: 55%;
            height: 100%;
            border-radius: inherit;
            background: white;
            animation: dashboardEntryLine 1.15s infinite cubic-bezier(.65,0,.35,1);
        }

        @keyframes dashboardEntryLine {
            to {
                left: 100%;
            }
        }

        /* =========================================================
           ROUTE LOADER
        ========================================================= */

        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            z-index: 99999;
            background: rgba(236,253,245,.75);
            overflow: hidden;
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity .30s ease, transform .30s ease;
        }

        body.is-navigating .page-loader {
            opacity: 1;
            transform: translateY(0);
        }

        .page-loader::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 42%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--green-700), var(--green-500), var(--amber-400));
            box-shadow: 0 0 22px rgba(16,185,129,.34);
            animation: loadingBar 1.25s infinite var(--ease-gate);
        }

        @keyframes loadingBar {
            0% { transform: translateX(-115%); }
            100% { transform: translateX(260%); }
        }

        .route-overlay {
            position: fixed;
            inset: 0;
            z-index: 99990;
            pointer-events: none;
            opacity: 0;
            background:
                radial-gradient(circle at center, rgba(255,255,255,.16), transparent 34%),
                linear-gradient(135deg, rgba(4,120,87,.72), rgba(5,150,105,.68), rgba(16,185,129,.72));
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: opacity .42s ease;
        }

        body.is-navigating .route-overlay {
            opacity: 1;
        }

        .route-loader-card {
            position: fixed;
            inset: 0;
            z-index: 99991;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0;
            transform: scale(.96);
            transition: opacity .42s ease, transform .50s var(--ease-premium);
        }

        body.is-navigating .route-loader-card {
            opacity: 1;
            transform: scale(1);
        }

        .loader-glass-card {
            width: 220px;
            min-height: 168px;
            border-radius: 32px;
            background: rgba(255,255,255,.16);
            border: 1px solid rgba(255,255,255,.32);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            box-shadow:
                0 30px 80px rgba(0,0,0,.16),
                inset 0 1px 0 rgba(255,255,255,.26);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
        }

        .loader-icon {
            width: 72px;
            height: 72px;
            border-radius: 25px;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.30);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            box-shadow:
                0 18px 42px rgba(0,0,0,.12),
                inset 0 1px 0 rgba(255,255,255,.24);
        }

        .loader-icon i {
            font-size: 30px;
        }

        .loader-title {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .02em;
        }

        .loader-line {
            position: relative;
            width: 116px;
            height: 5px;
            margin-top: 15px;
            border-radius: 999px;
            background: rgba(255,255,255,.24);
            overflow: hidden;
        }

        .loader-line::after {
            content: "";
            position: absolute;
            top: 0;
            left: -55%;
            width: 55%;
            height: 100%;
            border-radius: inherit;
            background: white;
            animation: miniLoader 1.05s infinite cubic-bezier(.65,0,.35,1);
        }

        @keyframes miniLoader {
            to {
                left: 100%;
            }
        }

        /* =========================================================
           LAYOUT
        ========================================================= */

        .admin-shell {
            position: relative;
            z-index: 10;
            min-height: 100vh;
        }

        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 70;
            width: var(--sidebar-width);
            height: 100dvh;
            padding: 14px;
            display: flex;
            flex-direction: column;
            overflow: visible;
            background: transparent !important;
            border-right: none !important;
            box-shadow: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            transform: translateX(-108%);
            transition:
                transform .48s var(--ease-premium),
                opacity .36s ease,
                filter .36s ease;
        }

        .admin-sidebar::before,
        .admin-sidebar::after {
            display: none !important;
        }

        .admin-sidebar.is-open {
            transform: translateX(0);
        }

        @media (min-width: 1024px) {
            .admin-sidebar {
                transform: translateX(0);
            }
        }

        .sidebar-nav {
            position: relative;
            z-index: 2;
            padding: 0 !important;
            height: 100%;
            overflow: visible !important;
        }

        .admin-content {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition:
                margin-left .44s var(--ease-premium),
                opacity .34s ease,
                filter .34s ease;
        }

        @media (min-width: 1024px) {
            .admin-content {
                margin-left: var(--sidebar-width);
            }
        }

        body.is-navigating .admin-content {
            opacity: .46;
            filter: blur(2px);
            pointer-events: none;
        }

        /* =========================================================
           MOBILE OVERLAY + CLOSE BUTTON
        ========================================================= */

        .mobile-overlay {
            position: fixed;
            inset: 0;
            z-index: 60;
            background: rgba(2,6,23,.36);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .sidebar-close-floating {
            position: absolute;
            top: 18px;
            right: 18px;
            z-index: 8;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 14px;
            background: rgba(236,253,245,.94);
            color: var(--green-700);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow:
                0 10px 24px rgba(15,23,42,.08),
                inset 0 1px 0 rgba(255,255,255,.85);
            transition:
                transform .26s var(--ease-premium),
                background .26s var(--ease-premium),
                color .26s var(--ease-premium);
        }

        .sidebar-close-floating:hover {
            transform: translateY(-1px);
            background: white;
            color: #dc2626;
        }

        /* =========================================================
           LIGHT SIDEBAR
        ========================================================= */

        .pc-light-sidebar {
            position: relative;
            width: 100%;
            height: calc(100dvh - 28px);
            padding: 24px 18px 18px;
            border-radius: 28px;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;

            background:
                radial-gradient(circle at 50% 0%, rgba(236,253,245,.80), transparent 34%),
                linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,255,252,.94));

            border: 1px solid rgba(226,232,240,.78);

            box-shadow:
                0 24px 70px rgba(15,23,42,.09),
                inset 0 1px 0 rgba(255,255,255,.95);

            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        .pc-light-sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .pc-light-sidebar::-webkit-scrollbar-thumb {
            background: rgba(16,185,129,.22);
        }

        .pc-sidebar-logo-area {
            position: relative;
            z-index: 3;
            display: flex;
            justify-content: center;
            margin-bottom: 22px;
        }

        .pc-logo-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .pc-sidebar-logo {
            width: 154px;
            height: auto;
            object-fit: contain;
            display: block;
            filter:
                drop-shadow(0 12px 22px rgba(15,23,42,.08))
                drop-shadow(0 2px 4px rgba(16,185,129,.08));
        }

        .pc-user-card {
            position: relative;
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 14px;
            margin-bottom: 24px;
            border-radius: 22px;
            background:
                linear-gradient(135deg, rgba(255,255,255,.88), rgba(248,255,252,.78));
            border: 1px solid rgba(209,250,229,.95);
            box-shadow:
                0 16px 34px rgba(15,23,42,.06),
                inset 0 1px 0 rgba(255,255,255,.95);
        }

        .pc-user-avatar {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            border-radius: 999px;
            background:
                linear-gradient(135deg, #10b981 0%, #34d399 45%, #f59e0b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 18px;
            box-shadow:
                0 12px 24px rgba(16,185,129,.18),
                inset 0 1px 0 rgba(255,255,255,.25);
        }

        .pc-user-info {
            flex: 1;
            min-width: 0;
        }

        .pc-user-info h4 {
            margin: 0;
            color: #064e3b;
            font-size: 13.5px;
            font-weight: 900;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pc-user-info p {
            margin: 3px 0 6px;
            color: #64748b;
            font-size: 11px;
            font-weight: 750;
        }

        .pc-online {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #059669;
            font-size: 10px;
            font-weight: 850;
        }

        .pc-online span {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,.12);
        }

        .pc-user-arrow {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #059669;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .25s var(--ease-premium);
        }

        .pc-user-arrow:hover {
            background: #ecfdf5;
            transform: translateY(-1px);
        }

        .pc-menu-group {
            position: relative;
            z-index: 3;
            margin-bottom: 22px;
        }

        .pc-menu-title {
            margin: 0 0 10px;
            padding-left: 4px;
            color: #64748b;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .pc-menu-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .pc-menu-item {
            position: relative;
            width: 100%;
            min-height: 42px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 13px;
            border: 0;
            border-radius: 13px;
            background: transparent;
            color: #334155;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 780;
            cursor: pointer;
            transition:
                background .28s var(--ease-premium),
                color .28s var(--ease-premium),
                transform .28s var(--ease-premium),
                box-shadow .28s var(--ease-premium);
        }

        .pc-menu-item:hover {
            background: rgba(236,253,245,.92);
            color: #047857;
            transform: translateX(3px);
        }

        .pc-menu-item.active {
            background:
                linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.82));
            color: #047857;
            font-weight: 900;
            box-shadow:
                0 10px 24px rgba(16,185,129,.08),
                inset 0 1px 0 rgba(255,255,255,.92);
        }

        .pc-menu-item.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 9px;
            bottom: 9px;
            width: 4px;
            border-radius: 999px;
            background: linear-gradient(180deg, #10b981, #059669);
        }

        .pc-menu-icon {
            width: 22px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 13px;
            transition: color .28s ease;
        }

        .pc-menu-item:hover .pc-menu-icon,
        .pc-menu-item.active .pc-menu-icon {
            color: #059669;
        }

        .pc-menu-text {
            flex: 1;
            min-width: 0;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pc-menu-badge {
            min-width: 22px;
            height: 22px;
            padding: 0 7px;
            border-radius: 999px;
            background: #d1fae5;
            color: #059669;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 900;
        }

        .pc-logout-form {
            margin: 0;
            padding: 0;
        }

        .pc-logout {
            color: #ef4444;
        }

        .pc-logout .pc-menu-icon {
            color: #ef4444;
        }

        .pc-logout:hover {
            background: #fff1f2;
            color: #dc2626;
        }

        .pc-sidebar-decoration {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 128px;
            pointer-events: none;
            overflow: hidden;
            z-index: 1;
        }

        .pc-wave {
            position: absolute;
            left: -20%;
            width: 140%;
            border-radius: 50% 50% 0 0;
        }

        .pc-wave-1 {
            bottom: -60px;
            height: 112px;
            background: rgba(16,185,129,.14);
        }

        .pc-wave-2 {
            bottom: -76px;
            height: 126px;
            background: rgba(5,150,105,.13);
        }

        .pc-wave-3 {
            bottom: -92px;
            height: 132px;
            background: rgba(20,184,166,.10);
        }

        .pc-plant {
            position: absolute;
            right: 16px;
            bottom: 22px;
            width: 76px;
            height: 76px;
        }

        .pc-stem {
            position: absolute;
            left: 36px;
            bottom: 0;
            width: 3px;
            height: 58px;
            border-radius: 999px;
            background: rgba(4,120,87,.35);
            transform: rotate(18deg);
            transform-origin: bottom;
        }

        .pc-leaf {
            position: absolute;
            width: 38px;
            height: 20px;
            border-radius: 100% 0 100% 0;
            background:
                linear-gradient(135deg, rgba(4,120,87,.66), rgba(16,185,129,.24));
            transform-origin: bottom left;
        }

        .pc-leaf-1 {
            right: 22px;
            bottom: 28px;
            transform: rotate(-34deg);
        }

        .pc-leaf-2 {
            right: 38px;
            bottom: 42px;
            transform: rotate(-8deg) scale(.9);
        }

        .pc-leaf-3 {
            right: 8px;
            bottom: 44px;
            transform: rotate(28deg) scale(.86);
        }

        .pc-leaf-4 {
            right: 30px;
            bottom: 14px;
            transform: rotate(46deg) scale(.72);
        }

        .pc-light-sidebar > * {
            opacity: 0;
            transform: translateY(16px);
            animation: pcSidebarSoftIn .85s var(--ease-smooth) forwards;
        }

        .pc-sidebar-logo-area { animation-delay: .08s; }
        .pc-user-card { animation-delay: .18s; }
        .pc-menu-group:nth-of-type(1) { animation-delay: .28s; }
        .pc-menu-group:nth-of-type(2) { animation-delay: .38s; }
        .pc-sidebar-decoration { animation-delay: .48s; }

        @keyframes pcSidebarSoftIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================================================
           TOPBAR
        ========================================================= */

        .admin-topbar {
            position: relative;
            z-index: 35;
            min-height: 78px;
            margin: 24px 28px 0;
            padding: 14px 18px 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-radius: 28px;
            background:
                linear-gradient(135deg, rgba(255,255,255,.90), rgba(255,255,255,.72));
            border: 1px solid rgba(226,232,240,.82);
            backdrop-filter: blur(26px) saturate(1.12);
            -webkit-backdrop-filter: blur(26px) saturate(1.12);
            box-shadow:
                0 22px 55px rgba(15,23,42,.065),
                inset 0 1px 0 rgba(255,255,255,.90);
        }

        .admin-topbar::before {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: 27px;
            pointer-events: none;
            background:
                radial-gradient(circle at 4% 0%, rgba(16,185,129,.10), transparent 34%),
                radial-gradient(circle at 96% 0%, rgba(245,158,11,.08), transparent 34%);
        }

        .topbar-left,
        .topbar-right {
            position: relative;
            z-index: 2;
        }

        .topbar-left {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .topbar-title-block {
            min-width: 0;
        }

        .mobile-menu-btn {
            width: 44px;
            height: 44px;
            border: 0;
            border-radius: 16px;
            background: rgba(255,255,255,.86);
            border: 1px solid rgba(226,232,240,.86);
            color: var(--slate-600);
            box-shadow:
                0 12px 26px rgba(15,23,42,.06),
                inset 0 1px 0 rgba(255,255,255,.80);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .32s var(--ease-premium);
        }

        .mobile-menu-btn:hover {
            color: var(--green-700);
            border-color: rgba(16,185,129,.30);
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(16,185,129,.12);
        }

        .breadcrumb-mini {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--slate-400);
            font-size: 10.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .13em;
        }

        .breadcrumb-mini a {
            color: var(--green-700);
            text-decoration: none;
        }

        .breadcrumb-mini i {
            font-size: 9px;
            opacity: .62;
        }

        .page-title-inline {
            margin: 6px 0 0;
            color: var(--slate-900);
            font-size: 20px;
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -0.045em;
            font-family: 'Poppins', sans-serif;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .system-chip {
            height: 46px;
            padding: 0 18px;
            border-radius: 18px;
            background:
                linear-gradient(135deg, rgba(236,253,245,.84), rgba(255,255,255,.62));
            border: 1px solid rgba(16,185,129,.18);
            color: var(--green-800);
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
            box-shadow:
                0 14px 30px rgba(16,185,129,.045),
                inset 0 1px 0 rgba(255,255,255,.82);
        }

        .system-chip i {
            color: var(--green-600);
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-button {
            height: 50px;
            padding: 5px 14px 5px 5px;
            border: 1px solid rgba(226,232,240,.82);
            border-radius: 999px;
            background: rgba(255,255,255,.78);
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all .32s var(--ease-premium);
            box-shadow:
                0 14px 30px rgba(15,23,42,.045),
                inset 0 1px 0 rgba(255,255,255,.82);
        }

        .profile-button:hover {
            background: white;
            border-color: rgba(16,185,129,.24);
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(15,23,42,.075);
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            color: white;
            background: linear-gradient(135deg, var(--green-700), var(--green-500));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 900;
            box-shadow:
                0 12px 24px rgba(16,185,129,.20),
                inset 0 1px 0 rgba(255,255,255,.22);
        }

        .profile-name {
            color: var(--slate-700);
            font-size: 13px;
            font-weight: 900;
            max-width: 132px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            width: 252px;
            border-radius: 24px;
            background: rgba(255,255,255,.96);
            border: 1px solid rgba(226,232,240,.9);
            box-shadow:
                0 28px 80px rgba(15,23,42,.14),
                inset 0 1px 0 rgba(255,255,255,.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 10px;
            overflow: hidden;
        }

        .profile-menu-head {
            padding: 12px 12px 10px;
            display: flex;
            align-items: center;
            gap: 11px;
            border-bottom: 1px solid var(--slate-100);
            margin-bottom: 8px;
        }

        .profile-menu-avatar {
            width: 42px;
            height: 42px;
            border-radius: 16px;
            color: white;
            background: linear-gradient(135deg, var(--green-700), var(--amber-500));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            flex-shrink: 0;
        }

        .profile-menu-name {
            margin: 0;
            color: var(--slate-900);
            font-size: 13px;
            font-weight: 900;
            line-height: 1.2;
        }

        .profile-menu-role {
            margin: 3px 0 0;
            color: var(--slate-400);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .10em;
        }

        .logout-btn {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: #e11d48;
            border-radius: 16px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 11px;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            transition: all .30s ease;
        }

        .logout-btn:hover {
            background: #fff1f2;
            color: #be123c;
        }

        /* =========================================================
           MAIN CONTENT
        ========================================================= */

        .admin-main {
            position: relative;
            z-index: 10;
            flex: 1;
            width: 100%;
            max-width: 1480px;
            margin: 0 auto;
            padding: 28px 28px 42px;
        }

        .admin-main-inner {
            position: relative;
        }

        .admin-card,
        .stat-card,
        .dashboard-card,
        .content-card,
        .table-card {
            border-radius: 24px;
            background: rgba(255,255,255,.82);
            border: 1px solid rgba(226,232,240,.82);
            box-shadow:
                0 20px 50px rgba(15,23,42,.052),
                inset 0 1px 0 rgba(255,255,255,.82);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        /* =========================================================
           REVEAL CONTENT
        ========================================================= */

        .pc-reveal-item {
            opacity: 0;
            transform: translateY(42px) scale(.982);
            filter: blur(5px);
            will-change: opacity, transform, filter;
        }

        html.pc-normal-entry .pc-reveal-ready .pc-reveal-item {
            animation: dashboardRevealSlow 1.15s var(--ease-smooth) forwards;
            animation-delay: calc(var(--reveal-index, 0) * 180ms);
        }

        html.pc-from-login.pc-content-in .pc-reveal-ready .pc-reveal-item {
            animation: dashboardRevealSlow 1.22s var(--ease-smooth) forwards;
            animation-delay: calc(var(--reveal-index, 0) * 220ms);
        }

        @keyframes dashboardRevealSlow {
            0% {
                opacity: 0;
                transform: translateY(42px) scale(.982);
                filter: blur(5px);
            }

            70% {
                opacity: 1;
                filter: blur(0);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }

        /* =========================================================
           ENTRY ANIMATION
        ========================================================= */

        .sidebar-enter {
            opacity: 0;
            animation: sidebarEnter 1.15s var(--ease-smooth) forwards;
        }

        .topbar-enter {
            opacity: 0;
            transform: translateY(-28px) scale(.985);
            animation: topbarEnter 1.12s var(--ease-smooth) .22s forwards;
        }

        .main-enter {
            opacity: 0;
            transform: translateY(36px) scale(.988);
            animation: mainEnter 1.2s var(--ease-smooth) .42s forwards;
        }

        @keyframes sidebarEnter {
            from {
                opacity: 0;
                transform: translateX(-40px);
                filter: blur(8px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
                filter: blur(0);
            }
        }

        @keyframes topbarEnter {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes mainEnter {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        html.pc-from-login:not(.pc-sidebar-in) .admin-sidebar {
            opacity: 0;
            transform: translateX(-115%);
            filter: blur(8px);
        }

        html.pc-from-login.pc-sidebar-in .admin-sidebar {
            opacity: 1;
            transform: translateX(0);
            filter: blur(0);
            transition:
                opacity 1.45s var(--ease-smooth),
                transform 1.45s var(--ease-smooth),
                filter 1.25s var(--ease-smooth);
        }

        html.pc-from-login:not(.pc-topbar-in) .admin-topbar {
            opacity: 0;
            transform: translateY(-42px) scale(.985);
            filter: blur(8px);
        }

        html.pc-from-login.pc-topbar-in .admin-topbar {
            opacity: 1;
            transform: translateY(0) scale(1);
            filter: blur(0);
            transition:
                opacity 1.25s var(--ease-smooth),
                transform 1.25s var(--ease-smooth),
                filter 1.15s var(--ease-smooth);
        }

        html.pc-from-login:not(.pc-content-in) .admin-main {
            opacity: 0;
            transform: translateY(58px) scale(.988);
            filter: blur(8px);
        }

        html.pc-from-login.pc-content-in .admin-main {
            opacity: 1;
            transform: translateY(0) scale(1);
            filter: blur(0);
            transition:
                opacity 1.35s var(--ease-smooth),
                transform 1.35s var(--ease-smooth),
                filter 1.2s var(--ease-smooth);
        }

        html.pc-from-login .sidebar-enter,
        html.pc-from-login .topbar-enter,
        html.pc-from-login .main-enter {
            animation: none !important;
        }

        html.pc-from-login:not(.pc-content-in) .admin-main-inner > * {
            opacity: 0 !important;
            transform: translateY(42px) scale(.982) !important;
            animation: none !important;
        }

        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 1023px) {
            :root {
                --sidebar-width: 286px;
            }

            .admin-sidebar {
                width: min(286px, calc(100vw - 24px));
                padding: 10px;
                transform: translateX(-110%);
            }

            .admin-sidebar.is-open {
                transform: translateX(0);
            }

            .sidebar-close-floating {
                display: flex;
            }

            .pc-light-sidebar {
                height: calc(100dvh - 20px);
                border-radius: 24px;
                padding: 22px 16px 18px;
            }

            .pc-sidebar-logo {
                width: 142px;
            }

            .mobile-menu-btn {
                display: flex;
            }

            .admin-content {
                margin-left: 0 !important;
                min-height: 100vh;
            }

            .admin-topbar {
                min-height: 72px;
                margin: 14px 14px 0;
                padding: 12px 14px;
                border-radius: 24px;
            }

            .breadcrumb-mini {
                display: none;
            }

            .page-title-inline {
                margin: 0;
                font-size: 16px;
            }

            .system-chip {
                display: none;
            }

            .profile-name {
                display: none;
            }

            .profile-button {
                padding-right: 5px;
            }

            .admin-main {
                padding: 24px 16px 34px;
            }

            html.pc-from-login:not(.pc-sidebar-in) .admin-sidebar,
            html.pc-from-login.pc-sidebar-in .admin-sidebar {
                opacity: 0;
                transform: translateX(-110%);
            }

            html.pc-from-login.pc-topbar-in .admin-topbar,
            html.pc-from-login.pc-content-in .admin-main {
                opacity: 1;
            }
        }

        @media (max-width: 640px) {
            .admin-topbar {
                min-height: 68px;
                margin: 10px 10px 0;
                border-radius: 22px;
            }

            .page-title-inline {
                max-width: 170px;
                font-size: 15px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .admin-main {
                padding: 22px 14px 30px;
            }

            .profile-menu {
                width: 230px;
            }

            .loader-glass-card,
            .dashboard-entry-card {
                width: 190px;
                min-height: 154px;
                border-radius: 30px;
            }

            .loader-icon,
            .dashboard-entry-icon {
                width: 64px;
                height: 64px;
                border-radius: 23px;
            }

            .loader-icon i,
            .dashboard-entry-icon i {
                font-size: 26px;
            }
        }

        @media (max-width: 420px) {
            .pc-sidebar-logo {
                width: 132px;
            }

            .pc-user-card {
                padding: 12px;
            }

            .pc-user-avatar {
                width: 48px;
                height: 48px;
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

<body
    x-data="{ sidebarOpen: false }"
    x-init="
        $watch('sidebarOpen', value => {
            if (window.innerWidth < 1024) {
                document.body.classList.toggle('pc-scroll-lock', value);
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebarOpen = false;
                document.body.classList.remove('pc-scroll-lock');
            }
        });

        window.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                sidebarOpen = false;
                document.body.classList.remove('pc-scroll-lock');
            }
        });
    "
    class="selection:bg-emerald-100 selection:text-emerald-900"
>

    <div class="dashboard-entry-loader" aria-hidden="true">
        <div class="dashboard-entry-card">
            <div class="dashboard-entry-icon">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>

            <div class="dashboard-entry-title">
                Menyiapkan Dashboard
            </div>

            <div class="dashboard-entry-line"></div>
        </div>
    </div>

    <div class="admin-bg" aria-hidden="true"></div>
    <div class="admin-grid-soft" aria-hidden="true"></div>
    <div class="admin-dot-pattern" aria-hidden="true"></div>

    <div class="admin-leaf-decor" aria-hidden="true">
        <span class="leaf-1"></span>
        <span class="leaf-2"></span>
        <span class="leaf-3"></span>
    </div>

    <div class="page-loader" aria-hidden="true"></div>

    <div class="route-overlay" aria-hidden="true"></div>

    <div class="route-loader-card" aria-hidden="true">
        <div class="loader-glass-card">
            <div class="loader-icon">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>

            <div class="loader-title">
                Memuat halaman
            </div>

            <div class="loader-line"></div>
        </div>
    </div>

    <div
        x-show="sidebarOpen"
        x-transition.opacity.duration.260ms
        @click="sidebarOpen = false; document.body.classList.remove('pc-scroll-lock')"
        class="mobile-overlay lg:hidden"
        style="display: none;"
        aria-hidden="true"
    ></div>

    <div class="admin-shell">

        <aside
            :class="sidebarOpen ? 'is-open' : ''"
            class="admin-sidebar sidebar-enter"
        >
            <button
                type="button"
                class="sidebar-close-floating lg:hidden"
                @click="sidebarOpen = false; document.body.classList.remove('pc-scroll-lock')"
                aria-label="Tutup sidebar"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

            <nav class="sidebar-nav">
                @include('partials.sidebar.admin')
            </nav>
        </aside>

        <div class="admin-content">

            <header class="admin-topbar topbar-enter">
                <div class="topbar-left">

                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="mobile-menu-btn"
                        aria-label="Buka sidebar"
                    >
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>

                    <div class="topbar-title-block">
                        <div class="breadcrumb-mini">
                            <a href="{{ route('admin.dashboard') }}" class="js-nav-link">
                                <i class="fa-solid fa-house"></i>
                            </a>

                            <i class="fa-solid fa-chevron-right"></i>

                            <span>
                                @yield('page-name', 'Overview')
                            </span>
                        </div>

                        <h2 class="page-title-inline">
                            @yield('page-title', 'Dashboard Admin')
                        </h2>
                    </div>
                </div>

                <div class="topbar-right">

                    <div class="system-chip">
                        <i class="fa-solid fa-heart-pulse"></i>
                        PosyanduCare System
                    </div>

                    <div class="profile-dropdown" x-data="{ userOpen: false }">
                        <button
                            type="button"
                            @click="userOpen = !userOpen"
                            @click.away="userOpen = false"
                            class="profile-button"
                        >
                            <div class="profile-avatar">
                                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                            </div>

                            <span class="profile-name">
                                {{ Auth::user()->name ?? 'Administrator' }}
                            </span>

                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300"
                               :class="userOpen ? 'rotate-180' : ''"></i>
                        </button>

                        <div
                            x-show="userOpen"
                            x-transition.opacity.scale.95.duration.240ms
                            class="profile-menu"
                            style="display: none;"
                        >
                            <div class="profile-menu-head">
                                <div class="profile-menu-avatar">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                                </div>

                                <div class="min-w-0">
                                    <p class="profile-menu-name truncate">
                                        {{ Auth::user()->name ?? 'Administrator' }}
                                    </p>

                                    <p class="profile-menu-role">
                                        Admin Sistem
                                    </p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button
                                    type="submit"
                                    class="logout-btn js-submit-loader"
                                >
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-main main-enter">
                <div class="admin-main-inner">
                    @yield('content')
                </div>
            </main>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const html = document.documentElement;

            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }

            window.scrollTo(0, 0);

            if (!html.classList.contains('pc-from-login')) {
                body.classList.remove('pc-scroll-lock');
                body.style.overflow = '';
            }

            function startNavigation() {
                body.classList.add('is-navigating');
            }

            function stopNavigation() {
                body.classList.remove('is-navigating');
            }

            const content = document.querySelector('.admin-main-inner');

            if (content) {
                const revealSelectors = [
                    '.dashboard-hero',
                    '.dashboard-section',
                    '.section-title',
                    '.stat-card',
                    '.dashboard-card',
                    '.admin-card',
                    '.content-card',
                    '.table-card',
                    '.chart-card',
                    '.grid > *',
                    'section',
                    'article'
                ];

                let revealItems = [];

                revealSelectors.forEach(function (selector) {
                    content.querySelectorAll(selector).forEach(function (item) {
                        if (!revealItems.includes(item)) {
                            revealItems.push(item);
                        }
                    });
                });

                if (revealItems.length === 0) {
                    revealItems = Array.from(content.children);
                }

                revealItems.forEach(function (item, index) {
                    item.classList.add('pc-reveal-item');
                    item.style.setProperty('--reveal-index', index);
                });

                requestAnimationFrame(function () {
                    content.classList.add('pc-reveal-ready');
                });
            }

            const fromLogin = html.classList.contains('pc-from-login');

            if (fromLogin) {
                stopNavigation();
                body.classList.add('pc-scroll-lock');
                window.scrollTo(0, 0);

                setTimeout(function () {
                    html.classList.add('pc-loader-out');
                }, 1000);

                setTimeout(function () {
                    html.classList.add('pc-sidebar-in');
                }, 1450);

                setTimeout(function () {
                    html.classList.add('pc-topbar-in');
                }, 2250);

                setTimeout(function () {
                    html.classList.add('pc-content-in');
                }, 3050);

                setTimeout(function () {
                    try {
                        sessionStorage.removeItem('pc_from_login');
                    } catch (e) {}

                    html.classList.remove('pc-from-login');
                    html.classList.remove('pc-loader-out');
                    html.classList.remove('pc-sidebar-in');
                    html.classList.remove('pc-topbar-in');
                    html.classList.remove('pc-content-in');
                    html.classList.add('pc-normal-entry');

                    body.classList.remove('pc-scroll-lock');
                    body.style.overflow = '';
                    window.scrollTo(0, 0);
                }, 7200);
            }

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

                    body.classList.remove('pc-scroll-lock');
                    startNavigation();
                });
            });

            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    body.classList.remove('pc-scroll-lock');
                    startNavigation();
                });
            });

            window.addEventListener('pageshow', function () {
                stopNavigation();
                body.classList.remove('pc-scroll-lock');
                body.style.overflow = '';
                window.scrollTo(0, 0);
            });
        });

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function () {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Tersalin ke Clipboard!',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl'
                    }
                });
            });
        }
    </script>

    @if(session('generated_password') || session('reset_password'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let pass = "{{ session('generated_password') ?? session('reset_password') }}";
                let type = "{{ session('generated_password') ? 'Akun Berhasil Dibuat!' : 'Password Direset!' }}";

                Swal.fire({
                    title: `<span style="color:#047857;font-weight:900;">${type}</span>`,
                    html: `
                        <div style="background:#f8fafc;padding:20px;border-radius:22px;border:1px solid #e2e8f0;margin-top:8px;text-align:left;">
                            <p style="font-size:11px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.12em;margin:0 0 4px;">
                                Identitas Akun:
                            </p>

                            <p style="font-weight:900;color:#0f172a;margin:0 0 16px;font-size:15px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ session('user_name') ?? session('reset_name') }}
                            </p>

                            <p style="font-size:11px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.12em;margin:0 0 8px;">
                                Password Login:
                            </p>

                            <div style="display:flex;align-items:center;gap:8px;">
                                <input
                                    type="text"
                                    readonly
                                    value="${pass}"
                                    style="width:100%;background:white;border:1px solid #cbd5e1;color:#047857;font-family:monospace;font-size:20px;font-weight:900;padding:12px 14px;border-radius:16px;text-align:center;outline:none;"
                                >

                                <button
                                    onclick="copyToClipboard('${pass}')"
                                    style="background:#059669;color:white;border:0;padding:14px 18px;border-radius:16px;cursor:pointer;box-shadow:0 12px 24px rgba(5,150,105,.22);"
                                >
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'Selesai',
                    confirmButtonColor: '#059669',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'rounded-3xl shadow-xl',
                        title: 'font-poppins'
                    }
                });
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>