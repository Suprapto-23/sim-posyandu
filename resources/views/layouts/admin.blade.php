<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') | PosyanduCare</title>

    {{-- Loader hanya aktif setelah login, bukan setiap pindah halaman. Karena kalau tiap klik loading layar penuh, itu bukan smooth, itu drama. --}}
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
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Aman untuk project kamu sekarang. Nanti kalau sudah stabil, pindahkan ke Vite biar production lebih ringan. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
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
            --slate-50:  #f8fafc;

            --sidebar-width: 292px;
            --ease-premium: cubic-bezier(.16, 1, .3, 1);
            --ease-smooth: cubic-bezier(.22, 1, .36, 1);
        }

        *,
        *::before,
        *::after {
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
            scroll-behavior: auto !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--slate-700);
            background:
                radial-gradient(circle at 7% 0%, rgba(16,185,129,.08), transparent 28%),
                radial-gradient(circle at 95% 7%, rgba(245,158,11,.05), transparent 28%),
                radial-gradient(circle at 85% 92%, rgba(20,184,166,.07), transparent 30%),
                linear-gradient(135deg, #f8fffc 0%, #f8fafc 48%, #effbf6 100%);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overscroll-behavior-y: auto;
        }

        body.pc-scroll-lock {
            overflow: hidden !important;
            touch-action: none;
        }

        h1,h2,h3,h4,h5,h6 {
            font-family: 'Poppins', system-ui, sans-serif;
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
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(148,163,184,.45);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100,116,139,.65);
        }

        /* =========================================================
           BACKGROUND RINGAN
        ========================================================= */
        .admin-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .admin-bg::before,
        .admin-bg::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .admin-bg::before {
            width: 420px;
            height: 420px;
            left: -190px;
            top: -180px;
            background: rgba(16,185,129,.10);
            filter: blur(70px);
        }

        .admin-bg::after {
            width: 400px;
            height: 400px;
            right: -180px;
            bottom: -170px;
            background: rgba(20,184,166,.085);
            filter: blur(72px);
        }

        .admin-grid-soft {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            opacity: .065;
            background-image:
                linear-gradient(rgba(15,23,42,.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15,23,42,.035) 1px, transparent 1px);
            background-size: 72px 72px;
        }

        /* =========================================================
           LOGIN-STYLE LOADING SCREEN
           Diambil dari layout login: orbit ring + heart pulse + dots.
           Dipakai untuk entry dari login dan pindah halaman admin.
        ========================================================= */
        #pcAdminLoader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            visibility: hidden;
            pointer-events: none;
        }

        #pcAdminLoader.show {
            visibility: visible;
            pointer-events: auto;
        }

        .ld-veil {
            position: absolute;
            inset: 0;
            background: rgba(240, 255, 248, .88);
            backdrop-filter: blur(16px) saturate(1.3);
            -webkit-backdrop-filter: blur(16px) saturate(1.3);
            opacity: 0;
            transition: opacity .26s ease;
        }

        #pcAdminLoader.show .ld-veil {
            opacity: 1;
        }

        .ld-panel {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, .96);
            border: 1px solid rgba(16, 185, 129, .13);
            border-radius: 26px;
            padding: 36px 48px 32px;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .13), inset 0 1px 0 rgba(255, 255, 255, .90);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-width: 256px;
            opacity: 0;
            transform: translateY(18px) scale(.94);
            transition: opacity .30s var(--ease-smooth) .06s, transform .30s var(--ease-smooth) .06s;
        }

        #pcAdminLoader.show .ld-panel {
            opacity: 1;
            transform: none;
        }

        .ld-orbit {
            position: relative;
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ld-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2.5px solid transparent;
        }

        .ld-ring:nth-child(1) {
            border-top-color: var(--green-500);
            border-right-color: rgba(16, 185, 129, .25);
            animation: spinR 1.1s linear infinite;
        }

        .ld-ring:nth-child(2) {
            inset: 8px;
            border-bottom-color: var(--green-400);
            border-left-color: rgba(52, 211, 153, .25);
            animation: spinR 1.7s linear infinite reverse;
        }

        .ld-ring:nth-child(3) {
            inset: 18px;
            border-top-color: var(--amber-500);
            border-right-color: rgba(245, 158, 11, .22);
            animation: spinR 2.3s linear infinite;
        }

        @keyframes spinR {
            to { transform: rotate(360deg); }
        }

        .ld-heart {
            position: relative;
            z-index: 2;
            font-size: 18px;
            color: var(--green-600);
            animation: heartBeat 1.4s ease-in-out infinite;
        }

        @keyframes heartBeat {
            0%,100% { transform: scale(1); opacity: .9; }
            14%     { transform: scale(1.2); }
            28%     { transform: scale(1); }
            42%     { transform: scale(1.1); }
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
            margin-bottom: 16px;
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
            animation: dotPop .9s ease-in-out infinite both;
        }

        .ld-dot:nth-child(1) { animation-delay: 0s; }
        .ld-dot:nth-child(2) { animation-delay: .18s; background: var(--green-500); }
        .ld-dot:nth-child(3) { animation-delay: .36s; background: var(--green-600); }
        .ld-dot:nth-child(4) { animation-delay: .54s; background: var(--amber-500); }

        @keyframes dotPop {
            0%,80%,100% { transform: scale(.55); opacity: .35; }
            40%         { transform: scale(1.15); opacity: 1; }
        }

        /* =========================================================
           ROUTE LOADER: progress bar tetap ada, panelnya memakai #pcAdminLoader di atas.
        ========================================================= */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 99998;
            height: 3px;
            opacity: 0;
            overflow: hidden;
            transform: translateY(-3px);
            transition: opacity .14s ease, transform .14s ease;
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
            transform: translate3d(calc(-1 * var(--sidebar-width) - 18px), 0, 0);
            transition: transform .24s var(--ease-premium);
            will-change: transform;
        }

        body.sidebar-open .admin-sidebar {
            transform: translate3d(0, 0, 0);
        }

        .sidebar-nav {
            position: relative;
            z-index: 2;
            height: 100%;
            padding: 0 !important;
            overflow: visible !important;
        }

        .admin-content {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left .24s var(--ease-premium);
            will-change: margin-left;
        }

        @media (min-width: 1024px) {
            body.sidebar-open .admin-content {
                margin-left: var(--sidebar-width);
            }
        }

        .mobile-overlay {
            position: fixed;
            inset: 0;
            z-index: 60;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            background: rgba(2,6,23,.30);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            border: 0;
            padding: 0;
            margin: 0;
            transition: opacity .18s ease, visibility .18s ease;
        }

        body.sidebar-open .mobile-overlay {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        @media (min-width: 1024px) {
            .mobile-overlay {
                display: none !important;
            }
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
            box-shadow: 0 8px 20px rgba(15,23,42,.07), inset 0 1px 0 rgba(255,255,255,.85);
            transition: transform .16s var(--ease-premium), background .16s ease, color .16s ease;
        }

        .sidebar-close-floating:hover {
            transform: translateY(-1px);
            background: white;
            color: #dc2626;
        }

        /* =========================================================
           GUARD PARTIAL SIDEBAR
           Ini supaya logo tidak berubah jadi billboard kampanye.
        ========================================================= */
        .admin-sidebar img,
        .sidebar-nav img,
        .pc-light-sidebar img {
            max-width: 168px !important;
            max-height: 92px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
        }

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
                radial-gradient(circle at 50% 0%, rgba(236,253,245,.70), transparent 34%),
                linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,255,252,.94));
            border: 1px solid rgba(226,232,240,.75);
            box-shadow: 0 18px 52px rgba(15,23,42,.07), inset 0 1px 0 rgba(255,255,255,.95);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .pc-light-sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .pc-light-sidebar::-webkit-scrollbar-thumb {
            background: rgba(16,185,129,.20);
            border-radius: 999px;
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
            width: 154px !important;
            max-width: 72% !important;
            max-height: 86px !important;
            object-fit: contain !important;
            display: block;
            filter: drop-shadow(0 8px 16px rgba(15,23,42,.07)) drop-shadow(0 2px 4px rgba(16,185,129,.07));
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
            background: linear-gradient(135deg, rgba(255,255,255,.88), rgba(248,255,252,.78));
            border: 1px solid rgba(209,250,229,.92);
            box-shadow: 0 12px 28px rgba(15,23,42,.048), inset 0 1px 0 rgba(255,255,255,.95);
        }

        .pc-user-avatar {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, #10b981 0%, #34d399 45%, #f59e0b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 18px;
            box-shadow: 0 10px 20px rgba(16,185,129,.16), inset 0 1px 0 rgba(255,255,255,.22);
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
            font-weight: 700;
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
            font-weight: 800;
        }

        .pc-online span {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,.11);
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
            transition: background .16s ease, transform .16s ease;
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
            font-weight: 700;
            cursor: pointer;
            transition: background .16s ease, color .16s ease, transform .16s ease;
        }

        .pc-menu-item:hover {
            background: rgba(236,253,245,.90);
            color: #047857;
            transform: translateX(3px);
        }

        .pc-menu-item.active {
            background: linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.80));
            color: #047857;
            font-weight: 900;
            box-shadow: 0 8px 20px rgba(16,185,129,.07), inset 0 1px 0 rgba(255,255,255,.90);
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
            transition: color .16s ease;
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

        .pc-logout,
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
            background: rgba(16,185,129,.13);
        }

        .pc-wave-2 {
            bottom: -76px;
            height: 126px;
            background: rgba(5,150,105,.11);
        }

        .pc-wave-3 {
            bottom: -92px;
            height: 132px;
            background: rgba(20,184,166,.09);
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
            background: rgba(4,120,87,.32);
            transform: rotate(18deg);
            transform-origin: bottom;
        }

        .pc-leaf {
            position: absolute;
            width: 38px;
            height: 20px;
            border-radius: 100% 0 100% 0;
            background: linear-gradient(135deg, rgba(4,120,87,.62), rgba(16,185,129,.20));
            transform-origin: bottom left;
        }

        .pc-leaf-1 { right:22px; bottom:28px; transform:rotate(-34deg); }
        .pc-leaf-2 { right:38px; bottom:42px; transform:rotate(-8deg) scale(.9); }
        .pc-leaf-3 { right:8px;  bottom:44px; transform:rotate(28deg) scale(.86); }
        .pc-leaf-4 { right:30px; bottom:14px; transform:rotate(46deg) scale(.72); }

        .pc-light-sidebar > * {
            opacity: 0;
            transform: translateY(7px);
            animation: pcSidebarIn .32s var(--ease-smooth) forwards;
        }

        .pc-sidebar-logo-area { animation-delay: .02s; }
        .pc-user-card { animation-delay: .04s; }
        .pc-menu-group:nth-of-type(1) { animation-delay: .06s; }
        .pc-menu-group:nth-of-type(2) { animation-delay: .08s; }
        .pc-sidebar-decoration { animation-delay: .10s; }

        @keyframes pcSidebarIn {
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
            min-height: 76px;
            margin: 24px 28px 0;
            padding: 14px 18px 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(255,255,255,.92), rgba(255,255,255,.76));
            border: 1px solid rgba(226,232,240,.80);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 16px 42px rgba(15,23,42,.05), inset 0 1px 0 rgba(255,255,255,.90);
        }

        .admin-topbar::before {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: 27px;
            pointer-events: none;
            background:
                radial-gradient(circle at 4% 0%, rgba(16,185,129,.075), transparent 34%),
                radial-gradient(circle at 96% 0%, rgba(245,158,11,.055), transparent 34%);
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

        .sidebar-toggle-btn {
            width: 44px;
            height: 44px;
            border: 0;
            border-radius: 16px;
            background: rgba(255,255,255,.88);
            border: 1px solid rgba(226,232,240,.86);
            color: var(--slate-600);
            box-shadow: 0 8px 20px rgba(15,23,42,.05), inset 0 1px 0 rgba(255,255,255,.78);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: color .16s ease, border-color .16s ease, transform .16s ease, background .16s ease;
        }

        .sidebar-toggle-btn:hover {
            color: var(--green-700);
            border-color: rgba(16,185,129,.28);
            background: white;
            transform: translateY(-1px);
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
            opacity: .60;
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
            background: linear-gradient(135deg, rgba(236,253,245,.82), rgba(255,255,255,.60));
            border: 1px solid rgba(16,185,129,.16);
            color: var(--green-800);
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
            box-shadow: 0 10px 24px rgba(16,185,129,.04), inset 0 1px 0 rgba(255,255,255,.80);
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
            border: 1px solid rgba(226,232,240,.80);
            border-radius: 999px;
            background: rgba(255,255,255,.80);
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: background .16s ease, border-color .16s ease, transform .16s ease;
            box-shadow: 0 10px 24px rgba(15,23,42,.04), inset 0 1px 0 rgba(255,255,255,.80);
        }

        .profile-button:hover {
            background: white;
            border-color: rgba(16,185,129,.22);
            transform: translateY(-1px);
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
            box-shadow: 0 8px 18px rgba(16,185,129,.18), inset 0 1px 0 rgba(255,255,255,.20);
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

        .profile-chevron {
            transition: transform .16s ease;
        }

        .profile-dropdown.is-open .profile-chevron {
            transform: rotate(180deg);
        }

        .profile-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            width: 252px;
            border-radius: 24px;
            background: rgba(255,255,255,.97);
            border: 1px solid rgba(226,232,240,.88);
            box-shadow: 0 24px 64px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 10px;
            overflow: hidden;
            opacity: 0;
            transform: translateY(7px) scale(.97);
            visibility: hidden;
            pointer-events: none;
            transition: opacity .16s ease, transform .16s var(--ease-smooth), visibility .16s ease;
        }

        .profile-dropdown.is-open .profile-menu {
            opacity: 1;
            transform: translateY(0) scale(1);
            visibility: visible;
            pointer-events: auto;
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
            transition: background .16s ease, color .16s ease;
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
        .table-card,
        .chart-card {
            border-radius: 24px;
            background: rgba(255,255,255,.84);
            border: 1px solid rgba(226,232,240,.80);
            box-shadow: 0 14px 36px rgba(15,23,42,.04), inset 0 1px 0 rgba(255,255,255,.80);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .pc-reveal-item {
            opacity: 0;
            transform: translateY(14px) scale(.995);
            will-change: opacity, transform;
        }

        .pc-reveal-ready .pc-reveal-item {
            animation: revealItem .34s var(--ease-smooth) forwards;
            animation-delay: calc(min(var(--reveal-index, 0), 8) * 30ms);
        }

        @keyframes revealItem {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .topbar-enter {
            opacity: 0;
            transform: translateY(-10px) scale(.997);
            animation: topbarEnter .32s var(--ease-smooth) .03s forwards;
        }

        .main-enter {
            opacity: 0;
            transform: translateY(14px) scale(.997);
            animation: mainEnter .34s var(--ease-smooth) .05s forwards;
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

        html.pc-from-login:not(.pc-topbar-in) .admin-topbar {
            opacity: 0;
            transform: translateY(-14px) scale(.994);
        }

        html.pc-from-login.pc-topbar-in .admin-topbar {
            opacity: 1;
            transform: translateY(0) scale(1);
            transition: opacity .30s var(--ease-smooth), transform .30s var(--ease-smooth);
        }

        html.pc-from-login:not(.pc-content-in) .admin-main {
            opacity: 0;
            transform: translateY(18px) scale(.996);
        }

        html.pc-from-login.pc-content-in .admin-main {
            opacity: 1;
            transform: translateY(0) scale(1);
            transition: opacity .34s var(--ease-smooth), transform .34s var(--ease-smooth);
        }

        html.pc-from-login .topbar-enter,
        html.pc-from-login .main-enter {
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
                transform: translate3d(-110%, 0, 0);
            }

            body.sidebar-open .admin-sidebar {
                transform: translate3d(0, 0, 0);
            }

            .sidebar-close-floating {
                display: flex;
            }

            .pc-light-sidebar {
                height: calc(100dvh - 20px);
                border-radius: 24px;
                padding: 22px 16px 18px;
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }

            .pc-sidebar-logo {
                width: 142px !important;
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

            .admin-bg::before,
            .admin-bg::after {
                filter: blur(54px);
            }
        }

        @media (max-width: 640px) {
            .admin-topbar {
                min-height: 68px;
                margin: 10px 10px 0;
                border-radius: 22px;
            }

            .page-title-inline {
                max-width: 180px;
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

            .dashboard-entry-card {
                padding: 24px 20px 22px;
            }

            .dashboard-entry-icon {
                width: 54px;
                height: 54px;
                border-radius: 18px;
            }

            .dashboard-entry-icon i {
                font-size: 22px;
            }
        }

        @media (max-width: 420px) {
            .pc-sidebar-logo {
                width: 132px !important;
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

<body class="selection:bg-emerald-100 selection:text-emerald-900">

    {{-- LOADING SCREEN: disamakan dengan layout login --}}
    <div id="pcAdminLoader" role="status" aria-label="Memuat, harap tunggu..." aria-live="polite">
        <div class="ld-veil"></div>
        <div class="ld-panel">
            <div class="ld-orbit">
                <div class="ld-ring"></div>
                <div class="ld-ring"></div>
                <div class="ld-ring"></div>
                <i class="fa-solid fa-heart-pulse ld-heart"></i>
            </div>
            <div class="ld-name">PosyanduCare</div>
            <div id="pcAdminLoaderLabel" class="ld-label">Memuat Halaman</div>
            <div class="ld-dots">
                <span class="ld-dot"></span>
                <span class="ld-dot"></span>
                <span class="ld-dot"></span>
                <span class="ld-dot"></span>
            </div>
        </div>
    </div>

    {{-- BACKGROUND --}}
    <div class="admin-bg" aria-hidden="true"></div>
    <div class="admin-grid-soft" aria-hidden="true"></div>

    {{-- ROUTE LOADER: progress bar + kartu Memuat ringan --}}
    <div class="page-loader" aria-hidden="true"></div>

   

    <div class="admin-shell">

        <aside id="adminSidebar" class="admin-sidebar" aria-label="Sidebar Admin">
            <button
                type="button"
                id="closeSidebar"
                class="sidebar-close-floating"
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
                        id="sidebarToggle"
                        class="sidebar-toggle-btn"
                        aria-label="Toggle sidebar"
                        aria-expanded="false"
                    >
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>

                    <div class="topbar-title-block">
                        <div class="breadcrumb-mini">
                            <a href="{{ route('admin.dashboard') }}" class="js-nav-link" aria-label="Dashboard Admin">
                                <i class="fa-solid fa-house"></i>
                            </a>
                            <i class="fa-solid fa-chevron-right"></i>
                            <span>@yield('page-name', 'Overview')</span>
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

                    <div id="profileDropdown" class="profile-dropdown">
                        <button
                            type="button"
                            id="profileToggle"
                            class="profile-button"
                            aria-expanded="false"
                        >
                            <div class="profile-avatar">
                                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                            </div>

                            <span class="profile-name">
                                {{ Auth::user()->name ?? 'Administrator' }}
                            </span>

                            <i class="profile-chevron fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>

                        <div id="profileMenu" class="profile-menu">
                            <div class="profile-menu-head">
                                <div class="profile-menu-avatar">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                                </div>

                                <div class="min-w-0">
                                    <p class="profile-menu-name truncate">
                                        {{ Auth::user()->name ?? 'Administrator' }}
                                    </p>
                                    <p class="profile-menu-role">Admin Sistem</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('logout') }}" data-confirm-logout="true">
                                @csrf
                                <button type="submit" class="logout-btn">
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

            const sidebarToggle = document.getElementById('sidebarToggle');
            const closeSidebarBtn = document.getElementById('closeSidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');

            const profileDropdown = document.getElementById('profileDropdown');
            const profileToggle = document.getElementById('profileToggle');

            const content = document.querySelector('.admin-main-inner');
            const desktopQuery = window.matchMedia('(min-width: 1024px)');

            const pcAdminLoader = document.getElementById('pcAdminLoader');
            const pcAdminLoaderLabel = document.getElementById('pcAdminLoaderLabel');

            function showAdminLoader(label) {
                if (pcAdminLoaderLabel && label) {
                    pcAdminLoaderLabel.textContent = label;
                }
                pcAdminLoader?.classList.add('show');
                body.classList.add('pc-scroll-lock');
            }

            function hideAdminLoader() {
                pcAdminLoader?.classList.remove('show');
                body.classList.remove('pc-scroll-lock');
                body.style.overflow = '';
            }

            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }

            window.scrollTo(0, 0);

            function isDesktop() {
                return desktopQuery.matches;
            }

            function lockScroll() {
                if (!isDesktop()) {
                    body.classList.add('pc-scroll-lock');
                }
            }

            function unlockScroll() {
                body.classList.remove('pc-scroll-lock');
                body.style.overflow = '';
            }

            function saveDesktopSidebarState(isOpen) {
                try {
                    localStorage.setItem('pc_admin_sidebar_open', isOpen ? '1' : '0');
                } catch (e) {}
            }

            function getDesktopSidebarState() {
                try {
                    const saved = localStorage.getItem('pc_admin_sidebar_open');
                    if (saved === '0') return false;
                    if (saved === '1') return true;
                } catch (e) {}
                return true;
            }

            function setSidebar(open, options = {}) {
                const shouldSave = options.save ?? isDesktop();

                body.classList.toggle('sidebar-open', open);

                if (sidebarToggle) {
                    sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                }

                if (open) {
                    lockScroll();
                } else {
                    unlockScroll();
                }

                if (shouldSave && isDesktop()) {
                    saveDesktopSidebarState(open);
                }
            }

            function initSidebar() {
                if (isDesktop()) {
                    setSidebar(getDesktopSidebarState(), { save: false });
                } else {
                    setSidebar(false, { save: false });
                }
            }

            initSidebar();

            sidebarToggle?.addEventListener('click', function () {
                setSidebar(!body.classList.contains('sidebar-open'));
            });

            closeSidebarBtn?.addEventListener('click', function () {
                setSidebar(false, { save: false });
            });

            mobileOverlay?.addEventListener('click', function () {
                setSidebar(false, { save: false });
            });

            if (desktopQuery.addEventListener) {
                desktopQuery.addEventListener('change', initSidebar);
            } else if (desktopQuery.addListener) {
                desktopQuery.addListener(initSidebar);
            }

            let resizeTimer = null;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(initSidebar, 100);
            });

            function closeProfileMenu() {
                profileDropdown?.classList.remove('is-open');
                profileToggle?.setAttribute('aria-expanded', 'false');
            }

            profileToggle?.addEventListener('click', function (event) {
                event.stopPropagation();
                const open = !profileDropdown.classList.contains('is-open');
                profileDropdown.classList.toggle('is-open', open);
                profileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            document.addEventListener('click', function (event) {
                if (profileDropdown && !profileDropdown.contains(event.target)) {
                    closeProfileMenu();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setSidebar(false, { save: isDesktop() });
                    closeProfileMenu();
                }
            });

            function startNavigation(label = 'Memuat Halaman') {
                clearTimeout(window.__pcRouteDelay);
                clearTimeout(window.__pcRouteFallback);

                /* Delay kecil biar loader tidak kedip kalau klik sangat cepat. */
                window.__pcRouteDelay = setTimeout(function () {
                    body.classList.add('is-navigating');
                    showAdminLoader(label);
                }, 80);

                /* Pengaman kalau navigasi batal atau browser balik dari cache. */
                window.__pcRouteFallback = setTimeout(function () {
                    stopNavigation();
                }, 4200);
            }

            function stopNavigation() {
                clearTimeout(window.__pcRouteDelay);
                clearTimeout(window.__pcRouteFallback);
                body.classList.remove('is-navigating');
                hideAdminLoader();
            }

            /* Reveal konten dibatasi biar tidak semua elemen ikut joget. */
            if (content) {
                const selectors = [
                    '.dashboard-hero',
                    '.dashboard-section',
                    '.section-title',
                    '.stat-card',
                    '.dashboard-card',
                    '.admin-card',
                    '.content-card',
                    '.table-card',
                    '.chart-card',
                    'section',
                    'article'
                ];

                const seen = [];

                selectors.forEach(function (selector) {
                    content.querySelectorAll(selector).forEach(function (element) {
                        if (!seen.includes(element)) {
                            seen.push(element);
                        }
                    });
                });

                const items = seen.length ? seen : Array.from(content.children);

                items.slice(0, 12).forEach(function (element, index) {
                    element.classList.add('pc-reveal-item');
                    element.style.setProperty('--reveal-index', index);
                });

                requestAnimationFrame(function () {
                    content.classList.add('pc-reveal-ready');
                });
            }

            /* Loader login memakai animasi yang sama seperti layout login. */
            if (html.classList.contains('pc-from-login')) {
                stopNavigation();
                showAdminLoader('Membuka Portal');
                window.scrollTo(0, 0);

                setTimeout(function () {
                    html.classList.add('pc-topbar-in');
                }, 380);

                setTimeout(function () {
                    html.classList.add('pc-content-in');
                }, 520);

                setTimeout(function () {
                    try {
                        sessionStorage.removeItem('pc_from_login');
                    } catch (e) {}

                    [
                        'pc-from-login',
                        'pc-loader-out',
                        'pc-topbar-in',
                        'pc-content-in'
                    ].forEach(function (className) {
                        html.classList.remove(className);
                    });

                    html.classList.add('pc-normal-entry');
                    hideAdminLoader();
                    window.scrollTo(0, 0);
                }, 950);
            } else {
                hideAdminLoader();
            }

            /* Navigasi: tampilkan loader Memuat ringan seperti kader. */
            document.querySelectorAll('a[href]').forEach(function (link) {
                const href = link.getAttribute('href');

                if (!href) return;

                if (
                    link.target === '_blank' ||
                    href.startsWith('#') ||
                    href.startsWith('javascript:') ||
                    href.startsWith('mailto:') ||
                    href.startsWith('tel:') ||
                    link.hasAttribute('download')
                ) {
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

                    try {
                        const url = new URL(href, window.location.origin);
                        if (url.origin !== window.location.origin) return;
                    } catch (e) {
                        return;
                    }

                    if (!isDesktop()) {
                        setSidebar(false, { save: false });
                    }

                    startNavigation('Memuat Halaman');
                });
            });

            /* Alert logout untuk semua form logout. */
            document.querySelectorAll('form[data-confirm-logout="true"], form[action*="logout"]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        startNavigation('Keluar Sistem');
                        return;
                    }

                    event.preventDefault();

                    const submitLogout = function () {
                        form.dataset.confirmed = 'true';
                        unlockScroll();
                        startNavigation('Keluar Sistem');
                        form.submit();
                    };

                    if (window.Swal) {
                        Swal.fire({
                            title: 'Keluar dari sistem?',
                            text: 'Sesi admin akan diakhiri dan kamu akan kembali ke halaman login.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#059669',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Ya, Logout',
                            cancelButtonText: 'Batal',
                            reverseButtons: true,
                            background: '#ffffff',
                            color: '#0f172a',
                            customClass: {
                                popup: 'rounded-3xl shadow-xl',
                                confirmButton: 'rounded-xl',
                                cancelButton: 'rounded-xl'
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                submitLogout();
                            }
                        });
                    } else {
                        if (confirm('Keluar dari sistem?')) {
                            submitLogout();
                        }
                    }
                });
            });

            window.addEventListener('pageshow', function () {
                stopNavigation();
                unlockScroll();
                window.scrollTo(0, 0);
                initSidebar();
            });

            /* Pengaman kalau browser balik dari BFCache dan class loader masih nyangkut. */
            setTimeout(function () {
                body.classList.remove('is-navigating');
            }, 1500);
        });

        function copyToClipboard(text) {
            if (!navigator.clipboard) {
                const input = document.createElement('input');
                input.value = text;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                input.remove();
                return;
            }

            navigator.clipboard.writeText(text).then(function () {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Tersalin ke Clipboard!',
                        showConfirmButton: false,
                        timer: 1600,
                        timerProgressBar: true,
                        background: '#ffffff',
                        color: '#0f172a',
                        customClass: { popup: 'rounded-2xl shadow-xl' }
                    });
                }
            });
        }
    </script>

    @if(session('generated_password') || session('reset_password'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const pass = @json(session('generated_password') ?? session('reset_password'));
                const title = @json(session('generated_password') ? 'Akun Berhasil Dibuat!' : 'Password Direset!');
                const name = @json(session('user_name') ?? session('reset_name') ?? 'Pengguna');

                function showPasswordModal() {
                    if (!window.Swal) {
                        alert(title + "\n" + name + "\nPassword: " + pass);
                        return;
                    }

                    Swal.fire({
                        title: '<span style="color:#047857;font-weight:900;">' + title + '</span>',
                        html:
                            '<div style="background:#f8fafc;padding:20px;border-radius:22px;border:1px solid #e2e8f0;margin-top:8px;text-align:left;">' +
                                '<p style="font-size:11px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.12em;margin:0 0 4px;">Identitas Akun:</p>' +
                                '<p style="font-weight:900;color:#0f172a;margin:0 0 16px;font-size:15px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + name + '</p>' +
                                '<p style="font-size:11px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.12em;margin:0 0 8px;">Password Login:</p>' +
                                '<div style="display:flex;align-items:center;gap:8px;">' +
                                    '<input type="text" readonly value="' + pass + '" style="width:100%;background:white;border:1px solid #cbd5e1;color:#047857;font-family:monospace;font-size:20px;font-weight:900;padding:12px 14px;border-radius:16px;text-align:center;outline:none;">' +
                                    '<button type="button" onclick="copyToClipboard(\'' + pass.replace(/'/g, "\\'") + '\')" style="background:#059669;color:white;border:0;padding:14px 18px;border-radius:16px;cursor:pointer;box-shadow:0 12px 24px rgba(5,150,105,.22);"><i class="fas fa-copy"></i></button>' +
                                '</div>' +
                            '</div>',
                        icon: 'success',
                        confirmButtonText: 'Selesai',
                        confirmButtonColor: '#059669',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'rounded-3xl shadow-xl',
                            title: 'font-poppins'
                        }
                    });
                }

                if (window.Swal) {
                    showPasswordModal();
                } else {
                    setTimeout(showPasswordModal, 400);
                }
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
