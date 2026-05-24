<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Bidan Workspace') | PosyanduCare</title>

    {{-- Deteksi transisi dari login --}}
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

    <meta name="theme-color" content="#f8fffc">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    {{-- Aman untuk struktur blade kamu sekarang. Nanti kalau mau lebih kencang lagi, pindahkan Tailwind ke Vite. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

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

            --cyan-700: #0e7490;
            --cyan-600: #0891b2;
            --cyan-500: #06b6d4;
            --cyan-400: #22d3ee;

            --amber-500: #f59e0b;
            --amber-400: #fbbf24;

            --rose-600: #e11d48;
            --rose-500: #f43f5e;

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
            overscroll-behavior-y: auto;
            background:
                radial-gradient(circle at 7% 0%, rgba(16,185,129,.08), transparent 28%),
                radial-gradient(circle at 95% 7%, rgba(245,158,11,.05), transparent 28%),
                radial-gradient(circle at 85% 92%, rgba(20,184,166,.07), transparent 30%),
                linear-gradient(135deg, #f8fffc 0%, #f8fafc 48%, #effbf6 100%);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body.pc-scroll-lock {
            overflow: hidden !important;
            touch-action: none;
        }

        h1,h2,h3,h4,h5,h6,
        .font-poppins {
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

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148,163,184,.42);
            border-radius: 999px;
        }

        /* =========================================================
           BACKGROUND RINGAN
        ========================================================= */
        .bidan-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .bidan-bg::before,
        .bidan-bg::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .bidan-bg::before {
            width: 420px;
            height: 420px;
            left: -190px;
            top: -180px;
            background: rgba(16,185,129,.10);
            filter: blur(70px);
        }

        .bidan-bg::after {
            width: 400px;
            height: 400px;
            right: -180px;
            bottom: -170px;
            background: rgba(20,184,166,.085);
            filter: blur(72px);
        }

        .bidan-grid-soft {
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

        .bidan-dot-pattern,
        .bidan-leaf-decor {
            display: none;
        }

        /* =========================================================
           LOGIN-STYLE LOADER
           Sama gaya seperti login/admin: orbit ring + heart pulse + dots.
        ========================================================= */
        #pcBidanLoader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            visibility: hidden;
            pointer-events: none;
        }

        #pcBidanLoader.show {
            visibility: visible;
            pointer-events: auto;
        }

        .ld-veil {
            position: absolute;
            inset: 0;
            background: rgba(240,255,248,.80);
            backdrop-filter: blur(10px) saturate(1.14);
            -webkit-backdrop-filter: blur(10px) saturate(1.14);
            opacity: 0;
            transition: opacity .20s ease;
        }

        #pcBidanLoader.show .ld-veil {
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
            box-shadow: 0 22px 54px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.92);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            opacity: 0;
            transform: translateY(12px) scale(.96);
            transition: opacity .24s var(--ease-smooth) .04s, transform .24s var(--ease-smooth) .04s;
            will-change: opacity, transform;
        }

        #pcBidanLoader.show .ld-panel {
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
        .bidan-shell {
            position: relative;
            z-index: 10;
            min-height: 100vh;
        }

        .bidan-sidebar {
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

        body.sidebar-open .bidan-sidebar {
            transform: translate3d(0, 0, 0);
        }

        .sidebar-nav {
            position: relative;
            z-index: 2;
            height: 100%;
            padding: 0 !important;
            overflow: visible !important;
        }

        .bidan-content {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left .24s var(--ease-premium);
            will-change: margin-left;
        }

        @media (min-width: 1024px) {
            body.sidebar-open .bidan-content {
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
           SIDEBAR COMPATIBILITY
        ========================================================= */
        .bidan-sidebar img,
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
        .bidan-topbar {
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

        .bidan-topbar::before {
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

        .workspace-chip {
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

        .workspace-chip i {
            color: var(--green-600);
        }

        .notif-button,
        .profile-button {
            height: 50px;
            border: 1px solid rgba(226,232,240,.80);
            border-radius: 999px;
            background: rgba(255,255,255,.80);
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background .16s ease, border-color .16s ease, transform .16s ease;
            box-shadow: 0 10px 24px rgba(15,23,42,.04), inset 0 1px 0 rgba(255,255,255,.80);
        }

        .notif-button {
            width: 50px;
            justify-content: center;
            color: var(--slate-500);
            position: relative;
        }

        .notif-button:hover,
        .profile-button:hover {
            background: white;
            border-color: rgba(16,185,129,.22);
            transform: translateY(-1px);
        }

        .notif-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 11px;
            height: 11px;
            border-radius: 999px;
            background: var(--rose-500);
            border: 2px solid white;
            box-shadow: 0 0 0 3px rgba(244,63,94,.10);
        }

        .profile-button {
            padding: 5px 14px 5px 5px;
            gap: 10px;
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
            overflow: hidden;
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

        .profile-role {
            margin-top: 2px;
            color: var(--slate-400);
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .10em;
        }

        .profile-chevron {
            transition: transform .16s ease;
        }

        .floating-box.is-open .profile-chevron {
            transform: rotate(180deg);
        }

        .floating-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            width: 280px;
            border-radius: 24px;
            background: rgba(255,255,255,.97);
            border: 1px solid rgba(226,232,240,.88);
            box-shadow: 0 24px 64px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 10px;
            overflow: hidden;
            z-index: 80;
            opacity: 0;
            transform: translateY(7px) scale(.97);
            visibility: hidden;
            pointer-events: none;
            transition: opacity .16s ease, transform .16s var(--ease-smooth), visibility .16s ease;
        }

        .floating-box.is-open .floating-menu {
            opacity: 1;
            transform: translateY(0) scale(1);
            visibility: visible;
            pointer-events: auto;
        }

        .notif-menu {
            width: 360px;
            max-width: calc(100vw - 32px);
        }

        .floating-menu-head {
            padding: 12px 12px 10px;
            display: flex;
            align-items: center;
            gap: 11px;
            border-bottom: 1px solid var(--slate-100);
            margin-bottom: 8px;
        }

        .floating-menu-title {
            margin: 0;
            color: var(--slate-900);
            font-size: 13px;
            font-weight: 900;
            line-height: 1.2;
        }

        .floating-menu-subtitle {
            margin: 3px 0 0;
            color: var(--slate-400);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .10em;
        }

        .floating-link,
        .logout-btn {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--slate-600);
            border-radius: 16px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 11px;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            text-decoration: none;
            transition: background .16s ease, color .16s ease;
        }

        .floating-link:hover {
            background: #ecfdf5;
            color: var(--green-700);
        }

        .logout-btn {
            color: #e11d48;
        }

        .logout-btn:hover {
            background: #fff1f2;
            color: #be123c;
        }

        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
            border-radius: 18px;
            color: var(--slate-700);
            text-decoration: none;
            transition: background .16s ease, transform .16s ease;
        }

        .notif-item:hover {
            background: #ecfdf5;
            transform: translateX(2px);
        }

        .notif-icon {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 15px;
            background: #ecfdf5;
            color: var(--green-700);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notif-title {
            margin: 0;
            color: var(--slate-800);
            font-size: 12.5px;
            font-weight: 900;
            line-height: 1.3;
        }

        .notif-meta {
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--slate-400);
            font-size: 10px;
            font-weight: 800;
        }

        /* =========================================================
           MAIN CONTENT
        ========================================================= */
        .bidan-main {
            position: relative;
            z-index: 10;
            flex: 1;
            width: 100%;
            max-width: 1480px;
            margin: 0 auto;
            padding: 28px 28px 104px;
        }

        .bidan-main-inner {
            position: relative;
        }

        .admin-card,
        .stat-card,
        .dashboard-card,
        .content-card,
        .table-card,
        .bidan-card,
        .chart-card {
            border-radius: 24px;
            background: rgba(255,255,255,.84);
            border: 1px solid rgba(226,232,240,.80);
            box-shadow: 0 14px 36px rgba(15,23,42,.04), inset 0 1px 0 rgba(255,255,255,.80);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* =========================================================
           MOBILE BOTTOM NAV
        ========================================================= */
        .bottom-nav {
            position: fixed;
            left: 12px;
            right: 12px;
            bottom: 10px;
            z-index: 55;
            min-height: 68px;
            padding: 8px 10px;
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            border-radius: 26px;
            background: rgba(255,255,255,.88);
            border: 1px solid rgba(226,232,240,.86);
            backdrop-filter: blur(16px) saturate(1.10);
            -webkit-backdrop-filter: blur(16px) saturate(1.10);
            box-shadow: 0 18px 48px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.92);
        }

        .bottom-nav-link {
            flex: 1;
            min-width: 0;
            height: 52px;
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            color: var(--slate-400);
            text-decoration: none;
            font-size: 9.5px;
            font-weight: 900;
            transition: background .16s ease, color .16s ease;
        }

        .bottom-nav-link i {
            font-size: 16px;
        }

        .bottom-nav-link:hover,
        .bottom-nav-link.active {
            color: var(--green-700);
            background: #ecfdf5;
        }

        .bottom-nav-center {
            position: relative;
            flex: 1;
            height: 52px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .bottom-nav-action {
            position: absolute;
            top: -22px;
            width: 58px;
            height: 58px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--green-700), var(--green-500));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 16px 34px rgba(16,185,129,.30), inset 0 1px 0 rgba(255,255,255,.22);
            border: 4px solid rgba(255,255,255,.94);
            transition: transform .16s ease;
        }

        .bottom-nav-action:hover {
            transform: translateY(-2px);
        }

        .bottom-nav-action i {
            font-size: 20px;
        }

        .bottom-nav-alert {
            position: absolute;
            top: 0;
            right: 0;
            width: 13px;
            height: 13px;
            border-radius: 999px;
            background: var(--rose-500);
            border: 2px solid white;
        }

        /* =========================================================
           REVEAL CONTENT
        ========================================================= */
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

        html.pc-from-login:not(.pc-topbar-in) .bidan-topbar {
            opacity: 0;
            transform: translateY(-14px) scale(.994);
        }

        html.pc-from-login.pc-topbar-in .bidan-topbar {
            opacity: 1;
            transform: translateY(0) scale(1);
            transition: opacity .30s var(--ease-smooth), transform .30s var(--ease-smooth);
        }

        html.pc-from-login:not(.pc-content-in) .bidan-main {
            opacity: 0;
            transform: translateY(18px) scale(.996);
        }

        html.pc-from-login.pc-content-in .bidan-main {
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

            .bidan-sidebar {
                width: min(286px, calc(100vw - 24px));
                padding: 10px;
                transform: translate3d(-110%, 0, 0);
            }

            body.sidebar-open .bidan-sidebar {
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

            .bidan-content {
                margin-left: 0 !important;
                min-height: 100vh;
            }

            .bidan-topbar {
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

            .workspace-chip {
                display: none;
            }

            .profile-name,
            .profile-role {
                display: none;
            }

            .profile-button {
                padding-right: 5px;
            }

            .bidan-main {
                padding: 24px 16px 104px;
            }

            .bottom-nav {
                display: flex;
            }

            .bidan-bg::before,
            .bidan-bg::after {
                filter: blur(54px);
            }
        }

        @media (max-width: 640px) {
            .bidan-topbar {
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

            .bidan-main {
                padding: 22px 14px 104px;
            }

            .floating-menu {
                width: 230px;
            }

            .notif-menu {
                width: calc(100vw - 24px);
                right: -74px;
            }

            .ld-panel {
                padding: 26px 22px 24px;
                min-width: unset;
                width: 86vw;
            }

            .ld-orbit {
                width: 58px;
                height: 58px;
                margin-bottom: 15px;
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

            .bottom-nav {
                left: 8px;
                right: 8px;
                bottom: 8px;
                border-radius: 22px;
            }

            .bottom-nav-link {
                font-size: 8.5px;
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

    @php
        use Illuminate\Support\Str;
        use Illuminate\Support\Facades\Route;
        use Illuminate\Support\Facades\Schema;

        $route = request()->route()?->getName() ?? '';
        $bidanName = Auth::user()->name ?? 'Bidan';
        $bidanInitial = strtoupper(substr($bidanName, 0, 1));

        $safeRoute = function ($name, $fallback = '#') {
            return Route::has($name) ? route($name) : $fallback;
        };

        $notifCount = 0;
        $pendingNotifs = collect();

        try {
            if (
                class_exists('\App\Models\Pemeriksaan') &&
                Schema::hasTable('pemeriksaans') &&
                Schema::hasColumn('pemeriksaans', 'status_verifikasi')
            ) {
                $baseNotifQuery = \App\Models\Pemeriksaan::query()
                    ->where('status_verifikasi', 'pending');

                $notifCount = (clone $baseNotifQuery)->count();

                $pendingNotifs = $baseNotifQuery
                    ->latest()
                    ->take(5)
                    ->get();
            }
        } catch (\Throwable $e) {
            $notifCount = 0;
            $pendingNotifs = collect();
        }

        $dashboardUrl = $safeRoute('bidan.dashboard');
        $profileUrl = $safeRoute('bidan.profile.index');
        $rekamUrl = $safeRoute('bidan.rekam-medis.index');
        $pemeriksaanUrl = $safeRoute('bidan.pemeriksaan.index');
        $imunisasiUrl = $safeRoute('bidan.imunisasi.index');
        $jadwalUrl = $safeRoute('bidan.jadwal.index');
    @endphp

    {{-- LOADING SCREEN: sama gaya dengan login/admin --}}
    <div id="pcBidanLoader" role="status" aria-label="Memuat, harap tunggu..." aria-live="polite">
        <div class="ld-veil"></div>
        <div class="ld-panel">
            <div class="ld-orbit">
                <div class="ld-ring"></div>
                <div class="ld-ring"></div>
                <div class="ld-ring"></div>
                <i class="fa-solid fa-heart-pulse ld-heart"></i>
            </div>
            <div class="ld-name">PosyanduCare</div>
            <div id="pcBidanLoaderLabel" class="ld-label">Memuat Halaman</div>
            <div class="ld-dots">
                <span class="ld-dot"></span>
                <span class="ld-dot"></span>
                <span class="ld-dot"></span>
                <span class="ld-dot"></span>
            </div>
        </div>
    </div>

    {{-- BACKGROUND --}}
    <div class="bidan-bg" aria-hidden="true"></div>
    <div class="bidan-grid-soft" aria-hidden="true"></div>

    {{-- PROGRESS BAR --}}
    <div class="page-loader" aria-hidden="true"></div>

    {{-- MOBILE SIDEBAR OVERLAY --}}
    <button
        type="button"
        id="mobileOverlay"
        class="mobile-overlay"
        aria-label="Tutup sidebar"
    ></button>

    <div class="bidan-shell">

        {{-- SIDEBAR --}}
        <aside id="bidanSidebar" class="bidan-sidebar" aria-label="Sidebar Bidan">
            <button
                type="button"
                id="closeSidebar"
                class="sidebar-close-floating"
                aria-label="Tutup sidebar"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

            <nav class="sidebar-nav">
                @include('partials.sidebar.bidan')
            </nav>
        </aside>

        {{-- CONTENT --}}
        <div class="bidan-content">

            {{-- TOPBAR --}}
            <header class="bidan-topbar topbar-enter">
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
                            <a href="{{ $dashboardUrl }}" class="js-nav-link spa-route smooth-route" aria-label="Dashboard Bidan">
                                <i class="fa-solid fa-house"></i>
                            </a>

                            <i class="fa-solid fa-chevron-right"></i>

                            <span>@yield('page-name', 'Workspace')</span>
                        </div>

                        <h2 class="page-title-inline">
                            @yield('page-title', 'Bidan Workspace')
                        </h2>
                    </div>
                </div>

                <div class="topbar-right">
                    <div class="workspace-chip">
                        <i class="fa-solid fa-user-doctor"></i>
                        Bidan Workspace
                    </div>

                    {{-- NOTIFIKASI --}}
                    <div id="notifDropdown" class="relative floating-box">
                        <button
                            type="button"
                            id="notifToggle"
                            class="notif-button"
                            aria-label="Buka notifikasi"
                            aria-expanded="false"
                        >
                            <i class="fa-regular fa-bell text-[18px]"></i>

                            @if($notifCount > 0)
                                <span class="notif-dot"></span>
                            @endif
                        </button>

                        <div class="floating-menu notif-menu">
                            <div class="floating-menu-head">
                                <div class="profile-avatar">
                                    <i class="fa-solid fa-notes-medical"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="floating-menu-title">Antrian Medis</p>
                                    <p class="floating-menu-subtitle">
                                        {{ $notifCount }} Pemeriksaan Menunggu
                                    </p>
                                </div>
                            </div>

                            <div class="max-h-[330px] overflow-y-auto custom-scrollbar px-1 pb-1">
                                @forelse($pendingNotifs as $notif)
                                    @php
                                        $namaPasien = $notif->nama_pasien ?? ('Pasien #' . ($notif->pasien_id ?? $notif->id));
                                        $kategoriPasien = strtolower($notif->kategori_pasien ?? 'pasien');
                                        $targetUrl = Route::has('bidan.pemeriksaan.show')
                                            ? route('bidan.pemeriksaan.show', $notif->id)
                                            : $pemeriksaanUrl;
                                    @endphp

                                    <a href="{{ $targetUrl }}" class="notif-item js-nav-link spa-route smooth-route">
                                        <div class="notif-icon">
                                            <i class="fa-solid fa-stethoscope"></i>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="notif-title truncate">{{ $namaPasien }}</p>

                                            <div class="notif-meta">
                                                <span class="uppercase text-emerald-600">{{ $kategoriPasien }}</span>
                                                <span>•</span>
                                                <span>{{ optional($notif->created_at)->diffForHumans() ?? '-' }}</span>
                                            </div>
                                        </div>

                                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 mt-3"></i>
                                    </a>
                                @empty
                                    <div class="py-9 text-center">
                                        <div class="mx-auto mb-3 w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                            <i class="fa-regular fa-circle-check text-xl"></i>
                                        </div>

                                        <p class="text-[12px] font-bold text-slate-400">
                                            Belum ada antrian pemeriksaan.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- PROFILE --}}
                    <div id="profileDropdown" class="relative floating-box">
                        <button
                            type="button"
                            id="profileToggle"
                            class="profile-button"
                            aria-expanded="false"
                        >
                            <div class="profile-avatar">{{ $bidanInitial }}</div>

                            <div class="hidden sm:block text-left min-w-0">
                                <p class="profile-name">{{ $bidanName }}</p>
                                <p class="profile-role">Tenaga Bidan</p>
                            </div>

                            <i class="profile-chevron fa-solid fa-chevron-down text-[10px] text-slate-400 hidden sm:block"></i>
                        </button>

                        <div class="floating-menu">
                            <div class="floating-menu-head">
                                <div class="profile-avatar">{{ $bidanInitial }}</div>

                                <div class="min-w-0">
                                    <p class="floating-menu-title truncate">{{ $bidanName }}</p>
                                    <p class="floating-menu-subtitle">Tenaga Bidan</p>
                                </div>
                            </div>

                            @if(Route::has('bidan.profile.index'))
                                <a href="{{ $profileUrl }}" class="floating-link js-nav-link spa-route smooth-route">
                                    <i class="fa-regular fa-user"></i>
                                    Profil Akun
                                </a>
                            @endif

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

            {{-- MAIN --}}
            <main class="bidan-main main-enter">
                <div class="bidan-main-inner">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- MOBILE BOTTOM NAV --}}
    <nav class="bottom-nav lg:hidden">
        <a
            href="{{ $dashboardUrl }}"
            class="bottom-nav-link js-nav-link spa-route smooth-route {{ $route === 'bidan.dashboard' ? 'active' : '' }}"
        >
            <i class="fa-solid fa-chart-pie"></i>
            Beranda
        </a>

        <a
            href="{{ $rekamUrl }}"
            class="bottom-nav-link js-nav-link spa-route smooth-route {{ Str::startsWith($route, 'bidan.rekam-medis') ? 'active' : '' }}"
        >
            <i class="fa-solid fa-folder-open"></i>
            EMR
        </a>

        <div class="bottom-nav-center">
            <a
                href="{{ $pemeriksaanUrl }}"
                class="bottom-nav-action js-nav-link spa-route smooth-route"
                aria-label="Pemeriksaan"
            >
                <i class="fa-solid fa-stethoscope"></i>

                @if($notifCount > 0)
                    <span class="bottom-nav-alert"></span>
                @endif
            </a>
        </div>

        <a
            href="{{ $imunisasiUrl }}"
            class="bottom-nav-link js-nav-link spa-route smooth-route {{ Str::startsWith($route, 'bidan.imunisasi') ? 'active' : '' }}"
        >
            <i class="fa-solid fa-syringe"></i>
            Vaksin
        </a>

        <a
            href="{{ $jadwalUrl }}"
            class="bottom-nav-link js-nav-link spa-route smooth-route {{ Str::startsWith($route, 'bidan.jadwal') ? 'active' : '' }}"
        >
            <i class="fa-solid fa-calendar-days"></i>
            Jadwal
        </a>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const html = document.documentElement;

            const sidebarToggle = document.getElementById('sidebarToggle');
            const closeSidebarBtn = document.getElementById('closeSidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');

            const notifDropdown = document.getElementById('notifDropdown');
            const notifToggle = document.getElementById('notifToggle');

            const profileDropdown = document.getElementById('profileDropdown');
            const profileToggle = document.getElementById('profileToggle');

            const content = document.querySelector('.bidan-main-inner');
            const desktopQuery = window.matchMedia('(min-width: 1024px)');

            const pcBidanLoader = document.getElementById('pcBidanLoader');
            const pcBidanLoaderLabel = document.getElementById('pcBidanLoaderLabel');

            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }

            window.scrollTo(0, 0);

            function isDesktop() {
                return desktopQuery.matches;
            }

            function showBidanLoader(label) {
                if (pcBidanLoaderLabel && label) {
                    pcBidanLoaderLabel.textContent = label;
                }

                pcBidanLoader?.classList.add('show');
                body.classList.add('pc-scroll-lock');
            }

            function hideBidanLoader() {
                pcBidanLoader?.classList.remove('show');
                body.classList.remove('pc-scroll-lock');
                body.style.overflow = '';
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
                    localStorage.setItem('pc_bidan_sidebar_open', isOpen ? '1' : '0');
                } catch (e) {}
            }

            function getDesktopSidebarState() {
                try {
                    const saved = localStorage.getItem('pc_bidan_sidebar_open');
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

            function closeFloatingMenus() {
                notifDropdown?.classList.remove('is-open');
                notifToggle?.setAttribute('aria-expanded', 'false');

                profileDropdown?.classList.remove('is-open');
                profileToggle?.setAttribute('aria-expanded', 'false');
            }

            function toggleFloating(dropdown, toggle) {
                const open = !dropdown.classList.contains('is-open');

                closeFloatingMenus();

                dropdown.classList.toggle('is-open', open);
                toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            notifToggle?.addEventListener('click', function (event) {
                event.stopPropagation();
                toggleFloating(notifDropdown, notifToggle);
            });

            profileToggle?.addEventListener('click', function (event) {
                event.stopPropagation();
                toggleFloating(profileDropdown, profileToggle);
            });

            document.addEventListener('click', function (event) {
                if (
                    notifDropdown && !notifDropdown.contains(event.target) &&
                    profileDropdown && !profileDropdown.contains(event.target)
                ) {
                    closeFloatingMenus();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setSidebar(false, { save: isDesktop() });
                    closeFloatingMenus();
                    stopNavigation();
                }
            });

            function startNavigation(label = 'Memuat Halaman') {
                clearTimeout(window.__pcBidanRouteDelay);
                clearTimeout(window.__pcBidanRouteFallback);

                window.__pcBidanRouteDelay = setTimeout(function () {
                    body.classList.add('is-navigating');
                    showBidanLoader(label);
                }, 80);

                window.__pcBidanRouteFallback = setTimeout(function () {
                    stopNavigation();
                }, 4200);
            }

            function stopNavigation() {
                clearTimeout(window.__pcBidanRouteDelay);
                clearTimeout(window.__pcBidanRouteFallback);
                body.classList.remove('is-navigating');
                hideBidanLoader();
            }

            /* Reveal konten dibatasi biar HP tidak dipaksa jadi workstation NASA. */
            if (content) {
                const revealSelectors = [
                    '.dashboard-hero',
                    '.dashboard-section',
                    '.section-title',
                    '.stat-card',
                    '.dashboard-card',
                    '.admin-card',
                    '.bidan-card',
                    '.content-card',
                    '.table-card',
                    '.chart-card',
                    'section',
                    'article'
                ];

                const seen = [];

                revealSelectors.forEach(function (selector) {
                    content.querySelectorAll(selector).forEach(function (item) {
                        if (!seen.includes(item)) {
                            seen.push(item);
                        }
                    });
                });

                const items = seen.length ? seen : Array.from(content.children);

                items.slice(0, 12).forEach(function (item, index) {
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
                showBidanLoader('Membuka Workspace');
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
                        'pc-sidebar-in',
                        'pc-topbar-in',
                        'pc-content-in'
                    ].forEach(function (className) {
                        html.classList.remove(className);
                    });

                    html.classList.add('pc-normal-entry');
                    hideBidanLoader();
                    window.scrollTo(0, 0);
                }, 950);
            } else {
                hideBidanLoader();
            }

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

                    closeFloatingMenus();
                    startNavigation('Memuat Halaman');
                });
            });

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
                        closeFloatingMenus();
                        startNavigation('Keluar Sistem');
                        form.submit();
                    };

                    if (window.Swal) {
                        Swal.fire({
                            title: 'Keluar dari sistem?',
                            text: 'Sesi bidan akan diakhiri dan kamu akan kembali ke halaman login.',
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

            document.querySelectorAll('form:not([data-confirm-logout="true"])').forEach(function (form) {
                const action = form.getAttribute('action') || '';

                if (action.includes('logout')) return;

                form.addEventListener('submit', function () {
                    startNavigation('Memproses Data');
                });
            });

            window.addEventListener('pageshow', function () {
                stopNavigation();
                unlockScroll();
                window.scrollTo(0, 0);
                initSidebar();
            });

            setTimeout(function () {
                stopNavigation();
            }, 1600);

            /* SweetAlert flash sederhana, kalau session flash dipakai di controller. */
            @if(session('success'))
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: @json(session('success')),
                        showConfirmButton: false,
                        timer: 2200,
                        timerProgressBar: true,
                        background: '#ffffff',
                        color: '#0f172a'
                    });
                }
            @endif

            @if(session('error'))
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: @json(session('error')),
                        confirmButtonColor: '#059669',
                        background: '#ffffff',
                        color: '#0f172a'
                    });
                }
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>
