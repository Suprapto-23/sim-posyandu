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

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap"
          rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        poppins: ['Poppins', 'sans-serif']
                    }
                }
            }
        };
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
            min-height: 100%;
            scroll-behavior: auto !important;
        }

        body {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;

            overflow-x: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--slate-800);

            background:
                radial-gradient(circle at 0% 0%, rgba(16,185,129,.12), transparent 30%),
                radial-gradient(circle at 100% 100%, rgba(20,184,166,.10), transparent 32%),
                linear-gradient(135deg, #f8fffc 0%, #f8fafc 48%, #effbf6 100%);

            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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
            text-decoration: none;
        }

        ::selection {
            background: rgba(16,185,129,.18);
            color: var(--green-900);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .user-bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .user-bg-layer::before {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            top: -180px;
            left: -160px;
            border-radius: 999px;
            background: rgba(16,185,129,.13);
            filter: blur(58px);
        }

        .user-bg-layer::after {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            right: -150px;
            bottom: -150px;
            border-radius: 999px;
            background: rgba(20,184,166,.12);
            filter: blur(56px);
        }

        .user-dot-pattern {
            position: fixed;
            top: 92px;
            right: 18px;
            width: 88px;
            height: 88px;
            opacity: .10;
            z-index: 1;
            pointer-events: none;
            background-image: radial-gradient(rgba(16,185,129,.52) 1.1px, transparent 1.1px);
            background-size: 9px 9px;
        }

        #pcUserLoader {
            position: fixed;
            inset: 0;
            z-index: 99999;

            display: flex;
            align-items: center;
            justify-content: center;

            visibility: hidden;
            opacity: 0;
            pointer-events: none;

            background: rgba(240,255,248,.78);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);

            transition: opacity .16s ease, visibility .16s ease;
        }

        #pcUserLoader.show {
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
        }

        .ld-panel {
            min-width: 230px;
            padding: 28px 34px 26px;
            border-radius: 24px;

            background: rgba(255,255,255,.96);
            border: 1px solid rgba(16,185,129,.14);

            box-shadow:
                0 22px 54px rgba(15,23,42,.12),
                inset 0 1px 0 rgba(255,255,255,.92);

            text-align: center;

            transform: translateY(10px) scale(.96);
            opacity: 0;
            transition: opacity .2s var(--ease-premium), transform .2s var(--ease-premium);
        }

        #pcUserLoader.show .ld-panel {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .ld-orbit {
            position: relative;
            width: 58px;
            height: 58px;
            margin: 0 auto 16px;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ld-ring {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            border: 2px solid transparent;
        }

        .ld-ring:nth-child(1) {
            border-top-color: var(--green-500);
            border-right-color: rgba(16,185,129,.25);
            animation: spinR .82s linear infinite;
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
            animation: spinR 1.55s linear infinite;
        }

        @keyframes spinR {
            to {
                transform: rotate(360deg);
            }
        }

        .ld-heart {
            position: relative;
            z-index: 2;
            font-size: 17px;
            color: var(--green-600);
        }

        .ld-name {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 900;
            color: var(--slate-900);
            margin-bottom: 2px;
        }

        .ld-label {
            font-size: 10.5px;
            font-weight: 800;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .user-shell {
            position: relative;
            z-index: 10;
            width: 100%;
            min-height: 100dvh;
        }

        .user-app {
            position: relative;
            z-index: 10;
            width: 100%;
            min-height: 100dvh;

            display: flex;
            flex-direction: column;

            opacity: 0;
            transform: translateY(8px);
            animation: userAppEnter .22s var(--ease-premium) forwards;
        }

        @keyframes userAppEnter {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-topbar {
            position: sticky;
            top: 0;
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

            background: rgba(255,255,255,.88);
            border-bottom: 1px solid rgba(226,232,240,.78);

            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);

            box-shadow: 0 10px 26px rgba(15,23,42,.045);
        }

        .user-topbar-left,
        .user-topbar-right {
            display: flex;
            align-items: center;
        }

        .user-topbar-left {
            gap: 10px;
            min-width: 0;
        }

        .user-topbar-right {
            gap: 8px;
        }

        .user-menu-button,
        .user-icon-button {
            width: 42px;
            height: 42px;

            border: 1px solid rgba(226,232,240,.86);
            border-radius: 16px;

            background: rgba(255,255,255,.90);
            color: var(--slate-600);

            display: flex;
            align-items: center;
            justify-content: center;

            box-shadow:
                0 8px 20px rgba(15,23,42,.04),
                inset 0 1px 0 rgba(255,255,255,.88);

            transition:
                transform .14s var(--ease-premium),
                border-color .14s var(--ease-premium),
                color .14s var(--ease-premium),
                background .14s var(--ease-premium);
        }

        .user-menu-button:hover,
        .user-icon-button:hover {
            color: var(--green-700);
            border-color: rgba(16,185,129,.28);
            background: #ffffff;
        }

        .user-menu-button:active,
        .user-icon-button:active {
            transform: scale(.95);
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
            letter-spacing: -.035em;

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
                transform: scale(.85);
                opacity: .65;
            }

            80%,
            100% {
                transform: scale(2);
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

            box-shadow:
                0 12px 24px rgba(16,185,129,.22),
                inset 0 1px 0 rgba(255,255,255,.22);
        }

        .notif-dropdown {
            position: fixed;

            top: calc(76px + env(safe-area-inset-top));
            left: 12px;
            right: 12px;

            z-index: 70;
            max-height: min(440px, calc(100dvh - 110px));

            border-radius: 26px;
            background: rgba(255,255,255,.97);
            border: 1px solid rgba(226,232,240,.90);

            box-shadow:
                0 28px 70px rgba(15,23,42,.16),
                inset 0 1px 0 rgba(255,255,255,.88);

            overflow: hidden;

            opacity: 0;
            visibility: hidden;
            pointer-events: none;

            transform: translateY(10px) scale(.985);

            transition:
                opacity .16s var(--ease-premium),
                transform .16s var(--ease-premium),
                visibility .16s var(--ease-premium);
        }

        .notif-dropdown.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .notif-head {
            padding: 16px;
            border-bottom: 1px solid rgba(241,245,249,.95);

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            background: rgba(248,250,252,.78);
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
            background: rgba(255,255,255,.58);
        }

        .notif-footer {
            padding: 13px 16px;
            border-top: 1px solid rgba(241,245,249,.95);
            background: rgba(248,250,252,.78);
            text-align: center;
        }

        .notif-footer a {
            color: var(--green-700);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .13em;
        }

        .user-sidebar-overlay {
            position: fixed;
            inset: 0;
            z-index: 80;

            background: rgba(2,6,23,.34);

            opacity: 0;
            visibility: hidden;
            pointer-events: none;

            transition:
                opacity .16s ease,
                visibility .16s ease;
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
            opacity: .8;
            pointer-events: none;

            transition:
                transform .2s var(--ease-premium),
                opacity .16s ease;
        }

        .user-sidebar-wrap.show {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

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

            opacity: 0;
            transform: translateY(10px);
            animation: contentIn .22s var(--ease-premium) .02s forwards;
        }

        @keyframes contentIn {
            to {
                opacity: 1;
                transform: translateY(0);
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
                0 16px 36px rgba(15,23,42,.05),
                inset 0 1px 0 rgba(255,255,255,.84);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

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
                0 20px 52px rgba(15,23,42,.13),
                0 6px 18px rgba(16,185,129,.06),
                inset 0 1px 0 rgba(255,255,255,.95);

            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .dock-link {
            position: relative;
            flex: 1;
            min-width: 0;
            height: 54px;

            border-radius: 18px;

            color: var(--slate-400);

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;

            transition:
                transform .14s var(--ease-premium),
                color .14s var(--ease-premium),
                background .14s var(--ease-premium);
        }

        .dock-link:active {
            transform: scale(.95);
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

            background: linear-gradient(135deg, var(--green-700), var(--green-500));
            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 4px solid rgba(255,255,255,.96);

            box-shadow:
                0 18px 34px rgba(16,185,129,.30),
                inset 0 1px 0 rgba(255,255,255,.24);

            transition: transform .14s var(--ease-premium);
        }

        .dock-action:active {
            transform: scale(.95);
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

        @media (min-width: 768px) {
            body {
                min-height: 100vh;
                overflow-y: auto;
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
                pointer-events: auto;
            }

            .user-sidebar-overlay {
                display: none !important;
            }

            .user-shell {
                min-height: 100vh;
                padding-left: calc(var(--user-sidebar-width) + 28px);
            }

            .user-app {
                min-height: 100vh;
                overflow: visible;
            }

            .user-topbar {
                min-height: 84px;

                margin: 14px 18px 0 0;
                padding: 14px 28px;

                border-radius: 28px;
                border: 1px solid rgba(226,232,240,.82);

                background: rgba(255,255,255,.86);

                box-shadow:
                    0 18px 46px rgba(15,23,42,.058),
                    inset 0 1px 0 rgba(255,255,255,.90);
            }

            .user-menu-button {
                display: none;
            }

            .user-page-title {
                max-width: 420px;
                font-size: 21px;
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
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    $userName = Auth::user()->name ?? 'Warga';
    $initial = strtoupper(substr($userName, 0, 1));

    $notifIndexRoute = Route::has('user.notifikasi.index')
        ? route('user.notifikasi.index')
        : '#';

    $profileRoute = Route::has('user.profile.edit')
        ? route('user.profile.edit')
        : '#';

    $dashboardRoute = Route::has('user.dashboard')
        ? route('user.dashboard')
        : '#';

    $jadwalRoute = Route::has('user.jadwal.index')
        ? route('user.jadwal.index')
        : '#';

    $monitoringRoute = Route::has('user.monitoring.index')
        ? route('user.monitoring.index')
        : '#';

    $riwayatRoute = Route::has('user.riwayat.index')
        ? route('user.riwayat.index')
        : '#';
@endphp

<div id="pcUserLoader" role="status" aria-label="Memuat, harap tunggu..." aria-live="polite">
    <div class="ld-panel">
        <div class="ld-orbit">
            <div class="ld-ring"></div>
            <div class="ld-ring"></div>
            <div class="ld-ring"></div>
            <i class="fa-solid fa-heart-pulse ld-heart"></i>
        </div>

        <div class="ld-name">PosyanduCare</div>
        <div id="pcUserLoaderLabel" class="ld-label">Keluar Sistem</div>
    </div>
</div>

<div class="user-bg-layer" aria-hidden="true"></div>
<div class="user-dot-pattern" aria-hidden="true"></div>

<div
    id="sidebarOverlay"
    class="user-sidebar-overlay"
    onclick="closeUserSidebar()"
    aria-hidden="true"
></div>

<div id="userSidebarWrap" class="user-sidebar-wrap">
    @include('partials.sidebar.user')
</div>

<div class="user-shell">
    <div class="user-app">

        <header class="user-topbar">
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
                            <a href="{{ $notifIndexRoute }}">
                                Lihat Semua Notifikasi
                            </a>
                        </div>
                    </div>
                </div>

                <a
                    href="{{ $profileRoute }}"
                    class="mobile-profile-bubble"
                    aria-label="Profil"
                >
                    {{ $initial }}
                </a>
            </div>
        </header>

        <main class="user-main" id="userMainScrollArea">
            <div class="user-main-inner">
                @yield('content')
            </div>
        </main>

        <nav class="mobile-dock" aria-label="Navigasi utama warga">
            <a
                href="{{ $dashboardRoute }}"
                class="dock-link {{ request()->routeIs('user.dashboard*') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-house"></i>
                <span>Home</span>
            </a>

            <a
                href="{{ $jadwalRoute }}"
                class="dock-link {{ request()->routeIs('user.jadwal*') ? 'active' : '' }}"
            >
                <i class="fa-regular fa-calendar-days"></i>
                <span>Jadwal</span>
            </a>

            <div class="dock-center">
                <a
                    href="{{ $monitoringRoute }}"
                    class="dock-action"
                    aria-label="Pantau kesehatan"
                >
                    <i class="fa-solid fa-chart-line"></i>
                </a>

                <span class="dock-center-label">
                    Pantau
                </span>
            </div>

            <a
                href="{{ $riwayatRoute }}"
                class="dock-link {{ request()->routeIs('user.riwayat*') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Riwayat</span>
            </a>

            <a
                href="{{ $profileRoute }}"
                class="dock-link {{ request()->routeIs('user.profile*') ? 'active' : '' }}"
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

    function openUserSidebar() {
        if (!sidebarOverlay || !userSidebarWrap || !isMobileView()) {
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
    }

    function toggleSidebar() {
        if (userSidebarWrap && userSidebarWrap.classList.contains('show')) {
            closeUserSidebar();
            return;
        }

        openUserSidebar();
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

    function showUserLoader(label = 'Keluar Sistem') {
        const loader = document.getElementById('pcUserLoader');
        const labelEl = document.getElementById('pcUserLoaderLabel');

        if (labelEl) {
            labelEl.textContent = label;
        }

        if (loader) {
            loader.classList.add('show');
        }

        document.body.classList.add('user-lock');
    }

    function hideUserLoader() {
        const loader = document.getElementById('pcUserLoader');

        if (loader) {
            loader.classList.remove('show');
        }

        document.body.classList.remove('user-lock');
    }

    function clearFloatingState() {
        closeNotif();

        if (sidebarOverlay && userSidebarWrap) {
            sidebarOverlay.classList.remove('show');

            if (isMobileView()) {
                userSidebarWrap.classList.remove('show');
            }
        }

        document.body.classList.remove('user-lock');
        hideUserLoader();
    }

    window.NexusToast = function (title, body, iconHtml = '') {
        if (typeof Swal === 'undefined') {
            return;
        }

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
            timer: 3800,
            timerProgressBar: true,
            customClass: {
                popup: 'rounded-[24px] border border-slate-100/70 shadow-[0_20px_50px_-10px_rgba(15,23,42,0.16)] !w-auto min-w-[320px] max-w-[92vw] mt-4 !bg-white/95'
            }
        });
    };

    document.addEventListener('click', function (event) {
        if (!event.target.closest('#notifArea')) {
            closeNotif();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            clearFloatingState();
        }
    });

    window.addEventListener('resize', function () {
        if (!isMobileView()) {
            closeUserSidebar();
        }
    });

    window.addEventListener('pageshow', function () {
        hideUserLoader();
        document.body.classList.remove('user-lock');
    });

    document.addEventListener('DOMContentLoaded', function () {
        try {
            sessionStorage.removeItem('pc_from_login');
        } catch (e) {}

        hideUserLoader();

        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                const action = (form.getAttribute('action') || '').toLowerCase();
                const isLogout =
                    form.classList.contains('js-logout-form') ||
                    action.includes('logout');

                if (isLogout) {
                    closeNotif();
                    closeUserSidebar();
                    showUserLoader('Keluar Sistem');
                }
            });
        });

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
        setInterval(syncNotif, 45000);

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