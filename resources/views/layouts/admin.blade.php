<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') | PosyanduCare</title>

    <script>
        (function () {
            try {
                const desktop = window.matchMedia('(min-width: 1024px)').matches;
                const saved = localStorage.getItem('pc_admin_sidebar');

                if (desktop && saved !== '0') {
                    document.documentElement.classList.add('sidebar-open');
                }
            } catch (e) {}

            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }
        })();
    </script>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.webp') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 292px;
            --content-left: 0px;
            --ease: cubic-bezier(.22, 1, .36, 1);
            --emerald: #10b981;
            --emerald-dark: #047857;
            --slate-dark: #0f172a;
            --slate-soft: #64748b;
            --border: rgba(226, 232, 240, .78);
        }

        @media (min-width: 1024px) {
            html.sidebar-open {
                --content-left: var(--sidebar-width);
            }
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
            overflow-x: hidden;
            scroll-behavior: auto !important;
        }

        html.is-locked,
        body.is-locked {
            overflow: hidden !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: #334155;
            background:
                radial-gradient(circle at 7% 0%, rgba(16, 185, 129, .08), transparent 28%),
                radial-gradient(circle at 95% 7%, rgba(14, 165, 233, .06), transparent 28%),
                linear-gradient(135deg, #f8fffc 0%, #f8fafc 48%, #effbf6 100%);
            -webkit-font-smoothing: antialiased;
            text-rendering: geometricPrecision;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', system-ui, sans-serif;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .45);
            border-radius: 999px;
        }

        .pc-bg,
        .pc-grid {
            position: fixed;
            inset: 0;
            pointer-events: none;
        }

        .pc-bg {
            z-index: 0;
            overflow: hidden;
        }

        .pc-bg::before,
        .pc-bg::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(48px);
        }

        .pc-bg::before {
            width: 300px;
            height: 300px;
            left: -135px;
            top: -135px;
            background: rgba(16, 185, 129, .09);
        }

        .pc-bg::after {
            width: 300px;
            height: 300px;
            right: -130px;
            bottom: -120px;
            background: rgba(20, 184, 166, .07);
        }

        .pc-grid {
            z-index: 1;
            opacity: .04;
            background-image:
                linear-gradient(rgba(15, 23, 42, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15, 23, 42, .04) 1px, transparent 1px);
            background-size: 72px 72px;
        }

        #pcNavBar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9990;
            height: 3px;
            opacity: 0;
            transform: scaleX(0);
            transform-origin: left;
            background: linear-gradient(90deg, #047857, #10b981, #f59e0b);
            pointer-events: none;
        }

        #pcNavBar.is-running {
            opacity: 1;
            animation: pcNavRun .72s var(--ease) forwards;
        }

        #pcNavBar.is-done {
            opacity: 0;
            transform: scaleX(1);
            transition: opacity .12s ease, transform .10s ease;
        }

        @keyframes pcNavRun {
            0% { transform: scaleX(0); }
            55% { transform: scaleX(.55); }
            100% { transform: scaleX(.92); }
        }

        /* Loading screen login/logout, disamakan dengan Kader/Bidan */
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
    background: rgba(240, 255, 248, .82);
    backdrop-filter: blur(10px) saturate(1.1);
    -webkit-backdrop-filter: blur(10px) saturate(1.1);
    opacity: 0;
    transition: opacity .2s ease;
}

#pcAdminLoader.show .ld-veil {
    opacity: 1;
}

.ld-panel {
    position: relative;
    z-index: 2;
    min-width: 220px;
    padding: 30px 40px 28px;
    border-radius: 24px;
    background: rgba(255, 255, 255, .97);
    border: 1px solid rgba(16, 185, 129, .13);
    box-shadow: 0 22px 54px rgba(15, 23, 42, .12), inset 0 1px 0 rgba(255, 255, 255, .92);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    opacity: 0;
    transform: translateY(12px) scale(.96);
    transition: opacity .24s var(--ease) .04s, transform .24s var(--ease) .04s;
    will-change: opacity, transform;
}

#pcAdminLoader.show .ld-panel {
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
    border-top-color: #10b981;
    border-right-color: rgba(16, 185, 129, .25);
    animation: spinR .78s linear infinite;
}

.ld-ring:nth-child(2) {
    inset: 8px;
    border-bottom-color: #34d399;
    border-left-color: rgba(52, 211, 153, .25);
    animation: spinR 1.15s linear infinite reverse;
}

.ld-ring:nth-child(3) {
    inset: 17px;
    border-top-color: #f59e0b;
    border-right-color: rgba(245, 158, 11, .22);
    animation: spinR 1.65s linear infinite;
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
    color: #059669;
    animation: heartBeat 1.08s ease-in-out infinite;
    will-change: transform;
}

@keyframes heartBeat {
    0%, 100% {
        transform: scale(1);
        opacity: .9;
    }

    18% {
        transform: scale(1.16);
    }

    36% {
        transform: scale(1);
    }

    52% {
        transform: scale(1.07);
    }
}

.ld-name {
    font-size: 15px;
    font-weight: 900;
    color: #0f172a;
    margin-bottom: 2px;
}

.ld-label {
    font-size: 10.5px;
    font-weight: 800;
    color: #64748b;
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
    background: #34d399;
    animation: dotPop .72s ease-in-out infinite both;
    will-change: transform, opacity;
}

.ld-dot:nth-child(1) {
    animation-delay: 0s;
}

.ld-dot:nth-child(2) {
    animation-delay: .12s;
    background: #10b981;
}

.ld-dot:nth-child(3) {
    animation-delay: .24s;
    background: #059669;
}

.ld-dot:nth-child(4) {
    animation-delay: .36s;
    background: #f59e0b;
}

@keyframes dotPop {
    0%, 80%, 100% {
        transform: scale(.55);
        opacity: .35;
    }

    40% {
        transform: scale(1.12);
        opacity: 1;
    }
}

@media (max-width: 390px) {
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

        .pc-shell {
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
            transform: translate3d(calc(-1 * var(--sidebar-width) - 18px), 0, 0);
            transition: transform .16s var(--ease);
            will-change: transform;
            contain: layout paint;
        }

        html.sidebar-open .admin-sidebar {
            transform: translate3d(0, 0, 0);
        }

        .admin-content {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: 100%;
            padding-left: var(--content-left);
        }

        html.sidebar-moving .admin-sidebar {
            will-change: transform;
        }

        html.sidebar-moving .pc-sidebar {
            pointer-events: none;
        }

        .mobile-overlay {
            position: fixed;
            inset: 0;
            z-index: 60;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            background: rgba(2, 6, 23, .28);
            transition: opacity .12s ease, visibility .12s ease;
        }

        html.sidebar-open .mobile-overlay {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        @media (min-width: 1024px) {
            .mobile-overlay {
                display: none !important;
            }
        }

        .sidebar-close-btn {
            position: absolute;
            top: 18px;
            right: 18px;
            z-index: 8;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 14px;
            background: rgba(236, 253, 245, .96);
            color: var(--emerald-dark);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .07);
        }

        .pc-sidebar {
            position: relative;
            width: 100%;
            height: calc(100dvh - 28px);
            padding: 22px 16px 16px;
            border-radius: 28px;
            overflow-x: hidden;
            overflow-y: auto;
            background:
                radial-gradient(circle at 50% 0%, rgba(236, 253, 245, .72), transparent 34%),
                linear-gradient(180deg, rgba(255, 255, 255, .99), rgba(248, 255, 252, .98));
            border: 1px solid rgba(226, 232, 240, .78);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .055);
            contain: paint;
        }

        .pc-sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .pc-sidebar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, .22);
            border-radius: 999px;
        }

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
            background: linear-gradient(135deg, rgba(255, 255, 255, .97), rgba(255, 255, 255, .90));
            border: 1px solid var(--border);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
            animation: pcTopbarIn .14s var(--ease) both;
        }

        @keyframes pcTopbarIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .topbar-left,
        .topbar-right {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
        }

        .topbar-left {
            min-width: 0;
            gap: 14px;
        }

        .topbar-right {
            gap: 12px;
        }

        .sidebar-toggle {
            width: 44px;
            height: 44px;
            border: 1px solid rgba(226, 232, 240, .86);
            border-radius: 16px;
            background: rgba(255, 255, 255, .95);
            color: var(--slate-soft);
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .035);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform .10s ease, color .10s ease, border-color .10s ease;
        }

        .sidebar-toggle:hover {
            color: var(--emerald-dark);
            border-color: rgba(16, 185, 129, .28);
            transform: translateY(-1px);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            font-size: 10.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .13em;
        }

        .breadcrumb a {
            color: var(--emerald-dark);
            text-decoration: none;
        }

        .breadcrumb i {
            font-size: 9px;
            opacity: .65;
        }

        .page-title {
            margin: 6px 0 0;
            color: var(--slate-dark);
            font-size: 20px;
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -.04em;
        }

        .system-chip {
            height: 46px;
            padding: 0 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(236, 253, 245, .84), rgba(255, 255, 255, .72));
            border: 1px solid rgba(16, 185, 129, .16);
            color: #065f46;
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .profile-wrap {
            position: relative;
        }

        .profile-btn {
            height: 50px;
            padding: 5px 14px 5px 5px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: rgba(255, 255, 255, .92);
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(15, 23, 42, .035);
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(135deg, #047857, #10b981);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 900;
            overflow: hidden;
        }

        .profile-avatar img,
        .profile-menu-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            color: #334155;
            font-size: 13px;
            font-weight: 900;
            max-width: 132px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-chevron {
            transition: transform .10s ease;
        }

        .profile-wrap.open .profile-chevron {
            transform: rotate(180deg);
        }

        .profile-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            width: 252px;
            border-radius: 24px;
            background: rgba(255, 255, 255, .99);
            border: 1px solid var(--border);
            box-shadow: 0 18px 42px rgba(15, 23, 42, .09);
            padding: 10px;
            opacity: 0;
            transform: translateY(6px) scale(.985);
            visibility: hidden;
            pointer-events: none;
            transition: opacity .10s ease, transform .10s var(--ease), visibility .10s ease;
        }

        .profile-wrap.open .profile-menu {
            opacity: 1;
            transform: none;
            visibility: visible;
            pointer-events: auto;
        }

        .profile-menu-head {
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 11px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 8px;
        }

        .profile-menu-avatar {
            width: 42px;
            height: 42px;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(135deg, #047857, #f59e0b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            overflow: hidden;
        }

        .profile-menu-name {
            margin: 0;
            color: #0f172a;
            font-size: 13px;
            font-weight: 900;
            line-height: 1.2;
        }

        .profile-menu-role {
            margin: 3px 0 0;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .10em;
        }

        .profile-link,
        .logout-btn {
            width: 100%;
            border: 0;
            border-radius: 16px;
            padding: 12px 14px;
            background: transparent;
            display: flex;
            align-items: center;
            gap: 11px;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            text-decoration: none;
            transition: background .10s ease, color .10s ease;
        }

        .profile-link {
            color: #475569;
        }

        .profile-link:hover {
            background: #ecfdf5;
            color: #047857;
        }

        .logout-btn {
            color: #e11d48;
        }

        .logout-btn:hover {
            background: #fff1f2;
            color: #be123c;
        }

        .admin-main {
            position: relative;
            z-index: 10;
            flex: 1;
            width: 100%;
            max-width: 1480px;
            margin: 0 auto;
            padding: 28px 28px 42px;
            animation: pcMainIn .14s var(--ease) .02s both;
        }

        @keyframes pcMainIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .nexus-swal {
            border-radius: 28px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background: rgba(255, 255, 255, .98) !important;
            border: 1px solid rgba(226, 232, 240, .86) !important;
            box-shadow: 0 24px 58px rgba(15, 23, 42, .14) !important;
        }

        .nexus-confirm {
            border: 0 !important;
            border-radius: 14px !important;
            padding: 11px 28px !important;
            font-weight: 900 !important;
            color: #fff !important;
            background: linear-gradient(135deg, #047857, #10b981) !important;
        }

        .nexus-cancel {
            border: 0 !important;
            border-radius: 14px !important;
            padding: 11px 28px !important;
            font-weight: 900 !important;
            color: #64748b !important;
            background: #f1f5f9 !important;
        }

        @media (max-width: 1023px) {
            :root {
                --sidebar-width: 286px;
                --content-left: 0px;
            }

            .admin-sidebar {
                width: min(286px, calc(100vw - 24px));
                padding: 10px;
                transform: translate3d(-110%, 0, 0);
            }

            html.sidebar-open .admin-sidebar {
                transform: translate3d(0, 0, 0);
            }

            .sidebar-close-btn {
                display: flex;
            }

            .pc-sidebar {
                height: calc(100dvh - 20px);
                border-radius: 24px;
                padding: 22px 16px 18px;
            }

            .admin-content {
                padding-left: 0 !important;
            }

            .admin-topbar {
                min-height: 72px;
                margin: 14px 14px 0;
                padding: 12px 14px;
                border-radius: 24px;
            }

            .breadcrumb {
                display: none;
            }

            .page-title {
                margin: 0;
                font-size: 16px;
            }

            .system-chip,
            .profile-name {
                display: none;
            }

            .profile-btn {
                padding-right: 5px;
            }

            .admin-main {
                padding: 24px 16px 34px;
            }
        }

        @media (max-width: 640px) {
            .admin-topbar {
                min-height: 68px;
                margin: 10px 10px 0;
                border-radius: 22px;
            }

            .page-title {
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
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 1ms !important;
                transition-duration: 1ms !important;
            }
        }
    </style>

    @stack('styles')
</head>

@php
    $authUser = auth()->user();
    $adminName = $authUser->name ?? 'Administrator';
    $adminInit = strtoupper(substr($adminName, 0, 1));
    $adminFoto = $authUser->foto ?? null;
@endphp

<body>
    <div id="pcNavBar" aria-hidden="true"></div>

    {{-- Loading screen login/logout --}}
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

    <div class="pc-bg" aria-hidden="true"></div>
    <div class="pc-grid" aria-hidden="true"></div>

    <div class="pc-shell">
        <button type="button" class="mobile-overlay" id="mobileOverlay" aria-label="Tutup sidebar" tabindex="-1"></button>

        <aside id="adminSidebar" class="admin-sidebar" aria-label="Sidebar Admin">
            <button type="button" id="closeSidebarBtn" class="sidebar-close-btn" aria-label="Tutup sidebar">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <nav>
                @include('partials.sidebar.admin')
            </nav>
        </aside>

        <div class="admin-content">
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button type="button" id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="false">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>

                    <div>
                        <div class="breadcrumb">
                            <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard">
                                <i class="fa-solid fa-house"></i>
                            </a>
                            <i class="fa-solid fa-chevron-right"></i>
                            <span>@yield('page-name', 'Overview')</span>
                        </div>

                        <h2 class="page-title">@yield('page-title', 'Dashboard Admin')</h2>
                    </div>
                </div>

                <div class="topbar-right">
                    <div class="system-chip">
                        <i class="fa-solid fa-heart-pulse"></i>
                        PosyanduCare System
                    </div>

                    <div class="profile-wrap" id="profileWrap">
                        <button type="button" id="profileToggle" class="profile-btn" aria-expanded="false">
                            <div class="profile-avatar">
                                @if($adminFoto)
                                    <img src="{{ asset('storage/'.$adminFoto) }}" alt="Foto Profil">
                                @else
                                    {{ $adminInit }}
                                @endif
                            </div>

                            <span class="profile-name">{{ $adminName }}</span>
                            <i class="profile-chevron fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>

                        <div class="profile-menu" id="profileMenu">
                            <div class="profile-menu-head">
                                <div class="profile-menu-avatar">
                                    @if($adminFoto)
                                        <img src="{{ asset('storage/'.$adminFoto) }}" alt="Foto Profil">
                                    @else
                                        {{ $adminInit }}
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <p class="profile-menu-name truncate">{{ $adminName }}</p>
                                    <p class="profile-menu-role">Admin Sistem</p>
                                </div>
                            </div>

                            @if(\Illuminate\Support\Facades\Route::has('admin.profile.index'))
                                <a href="{{ route('admin.profile.index') }}" class="profile-link">
                                    <i class="fa-regular fa-user"></i>
                                    Profil Saya
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="js-logout-form">
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

            <main class="admin-main">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const html = document.documentElement;
            const body = document.body;
            const navBar = document.getElementById('pcNavBar');
            const adminLoader = document.getElementById('pcAdminLoader');
const adminLoaderLabel = document.getElementById('pcAdminLoaderLabel');
            const desktop = window.matchMedia('(min-width: 1024px)');

            let sidebarTimer = null;
            let navTimer = null;

            function isMobile() {
                return !desktop.matches;
            }

            function lockPage(lock) {
                html.classList.toggle('is-locked', lock);
                body.classList.toggle('is-locked', lock);
            }

            function saveSidebar(open) {
                try {
                    localStorage.setItem('pc_admin_sidebar', open ? '1' : '0');
                } catch (e) {}
            }

            function getSidebarSaved() {
                try {
                    return localStorage.getItem('pc_admin_sidebar');
                } catch (e) {
                    return null;
                }
            }

            function setSidebar(open, save) {
                clearTimeout(sidebarTimer);

                html.classList.add('sidebar-moving');
                html.classList.toggle('sidebar-open', open);

                const toggle = document.getElementById('sidebarToggle');

                if (toggle) {
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                }

                if (isMobile()) {
                    lockPage(open);
                } else {
                    lockPage(false);
                }

                if (save !== false && !isMobile()) {
                    saveSidebar(open);
                }

                sidebarTimer = setTimeout(function () {
                    html.classList.remove('sidebar-moving');
                }, 190);
            }

            function initSidebar() {
                if (isMobile()) {
                    setSidebar(false, false);
                    return;
                }

                setSidebar(getSidebarSaved() !== '0', false);
            }

            function startNavBar() {
                if (!navBar) {
                    return;
                }

                clearTimeout(navTimer);
                navBar.classList.remove('is-done');
                navBar.classList.add('is-running');
            }

            function doneNavBar() {
                if (!navBar) {
                    return;
                }

                navBar.classList.remove('is-running');
                navBar.classList.add('is-done');

                navTimer = setTimeout(function () {
                    navBar.classList.remove('is-done');
                }, 220);
            }

            function closeProfile() {
                const wrap = document.getElementById('profileWrap');
                const toggle = document.getElementById('profileToggle');

                if (wrap) {
                    wrap.classList.remove('open');
                }

                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            }

            let adminLoaderTimer = null;

function showAdminLoader(message = 'Memuat Halaman') {
    clearTimeout(adminLoaderTimer);

    if (adminLoaderLabel) {
        adminLoaderLabel.textContent = message;
    }

    if (adminLoader) {
        adminLoader.classList.add('show');
    }

    lockPage(true);

    adminLoaderTimer = setTimeout(function () {
        hideAdminLoader();
    }, 5000);
}

function hideAdminLoader() {
    clearTimeout(adminLoaderTimer);

    if (adminLoader) {
        adminLoader.classList.remove('show');
    }

    lockPage(false);
}

            function isRealNavigation(link) {
                const href = link.getAttribute('href') || '';

                if (
                    !href ||
                    href === '#' ||
                    href.indexOf('#') === 0 ||
                    href.indexOf('javascript:') === 0 ||
                    href.indexOf('mailto:') === 0 ||
                    href.indexOf('tel:') === 0 ||
                    link.hasAttribute('download')
                ) {
                    return false;
                }

                try {
                    const url = new URL(href, location.href);

                    return url.origin === location.origin &&
                        (url.pathname + url.search) !== (location.pathname + location.search);
                } catch (e) {
                    return true;
                }
            }

            initSidebar();

            if (desktop.addEventListener) {
                desktop.addEventListener('change', initSidebar);
            } else if (desktop.addListener) {
                desktop.addListener(initSidebar);
            }

            document.getElementById('sidebarToggle')?.addEventListener('click', function () {
                setSidebar(!html.classList.contains('sidebar-open'), true);
            });

            document.getElementById('closeSidebarBtn')?.addEventListener('click', function () {
                setSidebar(false, false);
            });

            document.getElementById('mobileOverlay')?.addEventListener('click', function () {
                setSidebar(false, false);
            });

            document.getElementById('profileToggle')?.addEventListener('click', function (event) {
                event.stopPropagation();

                const wrap = document.getElementById('profileWrap');

                if (!wrap) {
                    return;
                }

                const open = !wrap.classList.contains('open');
                wrap.classList.toggle('open', open);
                this.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            document.addEventListener('click', function (event) {
                const profileWrap = document.getElementById('profileWrap');

                if (profileWrap && !profileWrap.contains(event.target)) {
                    closeProfile();
                }

                const link = event.target.closest('a[href]');

                if (
                    !link ||
                    event.ctrlKey ||
                    event.metaKey ||
                    event.shiftKey ||
                    event.altKey ||
                    event.defaultPrevented
                ) {
                    return;
                }

                if (!isRealNavigation(link)) {
                    return;
                }

                if (isMobile()) {
                    setSidebar(false, false);
                }

                startNavBar();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setSidebar(false, false);
                    closeProfile();
                }
            });

            window.addEventListener('pageshow', function () {
                doneNavBar();
                lockPage(false);
                initSidebar();

                hideAdminLoader();
            });

            window.addEventListener('load', doneNavBar);

            window.pcDialog = function (options) {
                if (!window.Swal) {
                    return Promise.resolve({ isConfirmed: true });
                }

                return Swal.fire({
                    title: options.title || 'Konfirmasi',
                    html: options.text || 'Data akan diproses.',
                    icon: options.icon || 'warning',
                    showCancelButton: true,
                    confirmButtonText: options.yes || 'LANJUTKAN',
                    cancelButtonText: options.no || 'BATAL',
                    customClass: {
                        popup: 'nexus-swal',
                        confirmButton: 'nexus-confirm',
                        cancelButton: 'nexus-cancel'
                    },
                    buttonsStyling: false
                });
            };

            window.pcToast = function (title, text, type) {
                if (!window.Swal) {
                    return;
                }

                Swal.fire({
                    title: title,
                    html: text,
                    icon: type || 'success',
                    confirmButtonText: 'MENGERTI',
                    customClass: {
                        popup: 'nexus-swal',
                        confirmButton: 'nexus-confirm'
                    },
                    buttonsStyling: false
                });
            };

            document.querySelectorAll('.js-logout-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.ready === '1') {
                        showAdminLoader('Keluar Sistem');
                        return;
                    }

                    event.preventDefault();

                    window.pcDialog({
                        title: 'Keluar dari sistem?',
                        text: 'Sesi admin akan diakhiri dan pengguna kembali ke halaman login.',
                        icon: 'warning',
                        yes: 'YA, LOGOUT',
                        no: 'BATAL'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.ready = '1';
                            showAdminLoader('Keluar Sistem');

                            setTimeout(function () {
                                form.submit();
                            }, 220);
                        }
                    });
                });
            });

            @if(session('success'))
                setTimeout(function () {
                    window.pcToast('Berhasil!', @json(session('success')), 'success');
                }, 120);
            @endif

            @if(session('error'))
                setTimeout(function () {
                    window.pcToast('Perhatian!', @json(session('error')), 'error');
                }, 120);
            @endif
        });

        window.copyToClipboard = function (text) {
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
                        title: 'Tersalin!',
                        showConfirmButton: false,
                        timer: 1400,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'nexus-swal'
                        }
                    });
                }
            });
        };
    </script>

    @stack('scripts')
</body>
</html>