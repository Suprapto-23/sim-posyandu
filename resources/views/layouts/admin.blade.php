<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') | PosyanduCare</title>

    <script>
        try {
            if (localStorage.getItem('pc_admin_sidebar') !== '0' && matchMedia('(min-width:1024px)').matches) {
                document.documentElement.classList.add('sb-open');
            }
        } catch (e) {}
    </script>

    <link rel="icon" href="{{ asset('img/logo.webp') }}">
    <link rel="preload" as="image" href="{{ asset('img/logo.webp') }}" type="image/webp" fetchpriority="high">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <link rel="preload"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          as="style"
          onload="this.onload=null;this.rel='stylesheet'"
          referrerpolicy="no-referrer">

    <noscript>
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
              referrerpolicy="no-referrer">
    </noscript>

    <style>
        :root {
            --sb: 284px;
            --green-900: #064e3b;
            --green-800: #065f46;
            --green-700: #047857;
            --green-600: #059669;
            --green-500: #10b981;
            --amber-500: #f59e0b;
            --blue-600: #2563eb;
            --sky-500: #0ea5e9;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --border: rgba(226,232,240,.82);
            --ease: cubic-bezier(.16, 1, .3, 1);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            overflow-x: hidden;
        }

        html.locked {
            overflow: hidden !important;
        }

        body {
            font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            color: var(--slate-700);
            background:
                radial-gradient(circle at 12% 12%, rgba(16,185,129,.10), transparent 28%),
                radial-gradient(circle at 88% 10%, rgba(245,158,11,.08), transparent 26%),
                radial-gradient(circle at 50% 96%, rgba(14,165,233,.07), transparent 32%),
                linear-gradient(135deg, #f8fffc 0%, #f8fafc 58%, #fffaf0 100%);
            -webkit-font-smoothing: antialiased;
            text-rendering: geometricPrecision;
        }

        body.admin-submitting {
            cursor: wait;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: "Poppins", "Plus Jakarta Sans", sans-serif;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        body::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        body::-webkit-scrollbar-thumb {
            background: rgba(148,163,184,.42);
            border-radius: 999px;
        }

        .admin-sidebar,
        .pc-sidebar,
        .pc-sidebar * {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        .admin-sidebar::-webkit-scrollbar,
        .pc-sidebar::-webkit-scrollbar,
        .pc-sidebar *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

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
            transition: opacity .16s ease, visibility .16s ease;
        }

        #pcAuthLoader.show {
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
        }

        .pc-auth-loader-veil {
            position: absolute;
            inset: 0;
            background: rgba(240,255,248,.88);
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
            background: linear-gradient(180deg, rgba(255,255,255,.90), rgba(255,255,255,.72));
            border: 1px solid rgba(255,255,255,.82);
            box-shadow: 0 28px 70px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.90);
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
            animation: authLoaderIn .22s var(--ease) forwards;
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
            border-top-color: var(--green-600);
            border-right-color: rgba(16,185,129,.35);
            animation: authSpin .68s linear infinite;
        }

        .pc-auth-ring:nth-child(2) {
            inset: 7px;
            border-top-color: var(--amber-500);
            border-right-color: rgba(245,158,11,.28);
            animation-duration: .88s;
            animation-direction: reverse;
        }

        .pc-auth-ring:nth-child(3) {
            inset: 14px;
            border-top-color: rgba(5,150,105,.65);
            border-right-color: rgba(5,150,105,.20);
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
            color: #fff;
            background: linear-gradient(135deg, var(--green-700), var(--green-500));
            box-shadow: 0 10px 25px rgba(5,150,105,.22);
            animation: authHeart .82s ease-in-out infinite;
        }

        .pc-auth-loader-name {
            color: var(--green-900);
            font-size: 15px;
            font-weight: 900;
            letter-spacing: -.02em;
            line-height: 1;
        }

        .pc-auth-loader-label {
            margin-top: 7px;
            color: var(--slate-500);
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
            background: var(--green-500);
            opacity: .35;
            animation: authDot .78s ease-in-out infinite;
        }

        .pc-auth-dot:nth-child(2) { animation-delay: .10s; }
        .pc-auth-dot:nth-child(3) { animation-delay: .20s; }
        .pc-auth-dot:nth-child(4) { animation-delay: .30s; }

        @keyframes authLoaderIn {
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        @keyframes authSpin {
            to { transform: rotate(360deg); }
        }

        @keyframes authHeart {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
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

        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 70;
            width: var(--sb);
            padding: 12px;
            transform: translate3d(calc(-1 * var(--sb) - 16px), 0, 0);
            transition: transform .10s var(--ease);
            will-change: transform;
            contain: layout paint;
        }

        html.sb-open .admin-sidebar {
            transform: translate3d(0, 0, 0);
        }

        .pc-sidebar {
            height: calc(100dvh - 24px);
            border-radius: 26px;
            padding: 20px 14px;
            overflow-y: auto;
            overflow-x: hidden;
            background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,255,252,.96));
            border: 1px solid var(--border);
            box-shadow: 0 12px 34px rgba(15,23,42,.07);
        }

        .sidebar-close {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 8;
            width: 32px;
            height: 32px;
            border: 0;
            border-radius: 12px;
            background: rgba(236,253,245,.96);
            color: var(--green-700);
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 18px rgba(15,23,42,.08);
        }

        .admin-content {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-left: 0;
        }

        html.sb-open .admin-content {
            transition: padding-left .08s ease;
        }

        @media (min-width: 1024px) {
            html.sb-open .admin-content {
                padding-left: var(--sb);
            }
        }

        .admin-topbar {
            margin: 20px 22px 0;
            min-height: 66px;
            padding: 12px 16px 12px 18px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,.96);
            border: 1px solid var(--border);
            box-shadow: 0 8px 20px rgba(15,23,42,.045);
        }

        .sidebar-toggle {
            width: 40px;
            height: 40px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: #fff;
            color: var(--slate-500);
            cursor: pointer;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            transition: background .08s ease, color .08s ease;
        }

        .sidebar-toggle:hover {
            color: var(--green-700);
            background: #ecfdf5;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }

        .system-chip {
            height: 38px;
            padding: 0 15px;
            border-radius: 14px;
            background: rgba(236,253,245,.92);
            border: 1px solid rgba(16,185,129,.18);
            color: var(--green-800);
            font-size: 11.5px;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
        }

        .profile-wrap {
            position: relative;
        }

        .profile-button {
            height: 44px;
            padding: 3px 10px 3px 3px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 5px 14px rgba(15,23,42,.035);
        }

        .profile-avatar,
        .dropdown-avatar {
            overflow: hidden;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--green-700), var(--green-500));
            color: #fff;
            font-weight: 900;
            display: grid;
            place-items: center;
        }

        .profile-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 12px;
        }

        .dropdown-avatar {
            width: 38px;
            height: 38px;
            border-radius: 13px;
            font-size: 13px;
        }

        .profile-avatar img,
        .dropdown-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 12.5px;
            font-weight: 900;
            color: var(--slate-700);
            max-width: 112px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .profile-arrow {
            font-size: 9px;
            color: var(--slate-400);
            transition: transform .08s ease;
        }

        .profile-wrap.open .profile-arrow {
            transform: rotate(180deg);
        }

        .profile-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: 236px;
            z-index: 90;
            border-radius: 20px;
            padding: 8px;
            background: rgba(255,255,255,.98);
            border: 1px solid var(--border);
            box-shadow: 0 18px 42px rgba(15,23,42,.12);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translate3d(0, 4px, 0) scale(.98);
            transition: opacity .08s ease, transform .08s var(--ease), visibility .08s ease;
        }

        .profile-wrap.open .profile-dropdown {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translate3d(0, 0, 0) scale(1);
        }

        .dropdown-head {
            padding: 10px;
            margin-bottom: 6px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-name {
            color: var(--slate-900);
            font-size: 13px;
            font-weight: 900;
        }

        .dropdown-role {
            color: var(--slate-400);
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .dropdown-link,
        .dropdown-logout {
            width: 100%;
            border: 0;
            border-radius: 13px;
            padding: 10px 12px;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #475569;
            font-size: 12.5px;
            font-weight: 900;
            text-decoration: none;
            transition: background .08s ease, color .08s ease;
        }

        .dropdown-link:hover {
            background: #ecfdf5;
            color: var(--green-700);
        }

        .dropdown-logout {
            color: #e11d48;
        }

        .dropdown-logout:hover {
            background: #fff1f2;
        }

        .admin-main {
            flex: 1;
            padding: 22px 22px 40px;
        }

        .mobile-overlay {
            position: fixed;
            inset: 0;
            z-index: 60;
            border: 0;
            background: rgba(2,6,23,.18);
            opacity: 0;
            visibility: hidden;
            transition: opacity .08s ease, visibility .08s ease;
        }

        html.sb-open .mobile-overlay {
            opacity: 1;
            visibility: visible;
        }

        .nexus-swal {
            border-radius: 28px !important;
            padding: 28px !important;
            font-family: "Plus Jakarta Sans", sans-serif !important;
            background: rgba(255,255,255,.98) !important;
            border: 1px solid rgba(226,232,240,.86) !important;
            box-shadow: 0 30px 80px rgba(15,23,42,.20) !important;
        }

        .nexus-title {
            color: var(--slate-900) !important;
            font-size: 24px !important;
            font-weight: 900 !important;
            letter-spacing: -.03em !important;
        }

        .nexus-html {
            color: var(--slate-500) !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            line-height: 1.55 !important;
        }

        .nexus-ok,
        .nexus-danger,
        .nexus-cancel {
            border: 0 !important;
            border-radius: 16px !important;
            padding: 12px 24px !important;
            font-size: 14px !important;
            font-weight: 900 !important;
        }

        .nexus-ok {
            color: #fff !important;
            background: linear-gradient(135deg, var(--blue-600), var(--sky-500)) !important;
        }

        .nexus-danger {
            color: #fff !important;
            background: linear-gradient(135deg, #f43f5e, #e11d48) !important;
        }

        .nexus-cancel {
            color: #475569 !important;
            background: #f1f5f9 !important;
        }

        div:where(.swal2-container) {
            z-index: 99999 !important;
            background: rgba(15,23,42,.36) !important;
            backdrop-filter: blur(6px) !important;
        }

        @media (min-width: 1024px) {
            .mobile-overlay {
                display: none !important;
            }
        }

        @media (max-width: 1023px) {
            .sidebar-close {
                display: flex;
            }

            .admin-content {
                padding-left: 0 !important;
            }

            .system-chip,
            .profile-name {
                display: none;
            }

            .admin-topbar {
                margin: 12px 12px 0;
                min-height: 60px;
                padding: 10px 12px;
                border-radius: 20px;
            }

            .admin-main {
                padding: 18px 14px 32px;
            }
        }

        @media (max-width: 768px) {
            .pc-auth-loader-veil {
                background: rgba(240,255,248,.82);
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

        @media (max-width: 640px) {
            .admin-topbar {
                margin: 8px 8px 0;
            }

            .profile-dropdown {
                width: 216px;
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

@php
    $admin = auth()->user();
    $adminName = $admin->name ?? 'Administrator';
    $adminInitial = strtoupper(substr($adminName, 0, 1));
    $adminPhoto = $admin->foto ?? null;
@endphp

<body>
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
            <div id="pcAuthLoaderLabel" class="pc-auth-loader-label">Mengakhiri Sesi</div>

            <div class="pc-auth-dots">
                <span class="pc-auth-dot"></span>
                <span class="pc-auth-dot"></span>
                <span class="pc-auth-dot"></span>
                <span class="pc-auth-dot"></span>
            </div>
        </div>
    </div>

    <button type="button" class="mobile-overlay" id="mobileOverlay" tabindex="-1" aria-hidden="true"></button>

    <aside class="admin-sidebar" aria-label="Navigasi Admin">
        <button type="button" id="sidebarClose" class="sidebar-close" aria-label="Tutup sidebar">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="pc-sidebar">
            @include('partials.sidebar.admin')
        </div>
    </aside>

    <div class="admin-content">
        <header class="admin-topbar">
            <button type="button" id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="false">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>

            <div class="topbar-right">
                <div class="system-chip">
                    <i class="fa-solid fa-heart-pulse"></i>
                    PosyanduCare System
                </div>

                <div class="profile-wrap" id="profileWrap">
                    <button type="button" id="profileToggle" class="profile-button" aria-expanded="false">
                        <div class="profile-avatar">
                            @if($adminPhoto)
                                <img src="{{ asset('storage/'.$adminPhoto) }}" alt="Foto Profil">
                            @else
                                {{ $adminInitial }}
                            @endif
                        </div>

                        <span class="profile-name">{{ $adminName }}</span>
                        <i class="profile-arrow fa-solid fa-chevron-down"></i>
                    </button>

                    <div class="profile-dropdown">
                        <div class="dropdown-head">
                            <div class="dropdown-avatar">
                                @if($adminPhoto)
                                    <img src="{{ asset('storage/'.$adminPhoto) }}" alt="Foto Profil">
                                @else
                                    {{ $adminInitial }}
                                @endif
                            </div>

                            <div>
                                <div class="dropdown-name">{{ $adminName }}</div>
                                <div class="dropdown-role">Admin Sistem</div>
                            </div>
                        </div>

                        @if(\Illuminate\Support\Facades\Route::has('admin.profile.index'))
                            <a href="{{ route('admin.profile.index') }}" class="dropdown-link">
                                <i class="fa-regular fa-user"></i>
                                Profil Saya
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="js-logout-form">
                            @csrf
                            <button type="submit" class="dropdown-logout">
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

    @stack('modals')

    <script>
        (function () {
            const root = document.documentElement;
            const desktop = matchMedia('(min-width:1024px)');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const profileWrap = document.getElementById('profileWrap');
            const profileToggle = document.getElementById('profileToggle');
            const loader = document.getElementById('pcAuthLoader');
            const loaderLabel = document.getElementById('pcAuthLoaderLabel');

            function isDesktop() {
                return desktop.matches;
            }

            function setSidebar(open, save = true) {
                root.classList.toggle('sb-open', open);
                sidebarToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');

                if (!isDesktop()) {
                    root.classList.toggle('locked', open);
                } else {
                    root.classList.remove('locked');
                }

                if (save && isDesktop()) {
                    try {
                        localStorage.setItem('pc_admin_sidebar', open ? '1' : '0');
                    } catch (e) {}
                }
            }

            function initSidebar() {
                if (!isDesktop()) {
                    setSidebar(false, false);
                    return;
                }

                try {
                    setSidebar(localStorage.getItem('pc_admin_sidebar') !== '0', false);
                } catch (e) {
                    setSidebar(true, false);
                }
            }

            function closeProfile() {
                profileWrap?.classList.remove('open');
                profileToggle?.setAttribute('aria-expanded', 'false');
            }

            function showLoader(text = 'Mengakhiri Sesi') {
                if (loaderLabel) loaderLabel.textContent = text;
                loader?.classList.add('show');
                document.body.classList.add('admin-submitting');
                root.classList.add('locked');
            }

            function hideLoader() {
                loader?.classList.remove('show');
                document.body.classList.remove('admin-submitting');
                root.classList.remove('locked');
            }

            function nexusConfirm(options) {
                if (!window.Swal) {
                    return Promise.resolve({ isConfirmed: true });
                }

                return Swal.fire({
                    title: options.title || 'Konfirmasi',
                    html: options.text || 'Lanjutkan tindakan ini?',
                    icon: options.icon || 'warning',
                    iconColor: options.iconColor || '#2563eb',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: options.yes || 'Ya, Lanjutkan',
                    cancelButtonText: options.no || 'Batalkan',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'nexus-swal',
                        title: 'nexus-title',
                        htmlContainer: 'nexus-html',
                        confirmButton: options.danger ? 'nexus-danger' : 'nexus-ok',
                        cancelButton: 'nexus-cancel'
                    }
                });
            }

            function nexusToast(title, text, icon = 'success') {
                if (!window.Swal) return;

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    title: title,
                    html: text || '',
                    icon: icon,
                    showConfirmButton: false,
                    timer: 1400,
                    timerProgressBar: true,
                    customClass: { popup: 'nexus-swal' }
                });
            }

            window.nexusConfirm = nexusConfirm;
            window.nexusToast = nexusToast;

            desktop.addEventListener?.('change', initSidebar);

            sidebarToggle?.addEventListener('click', () => {
                setSidebar(!root.classList.contains('sb-open'));
            });

            sidebarClose?.addEventListener('click', () => setSidebar(false, false));
            mobileOverlay?.addEventListener('click', () => setSidebar(false, false));

            profileToggle?.addEventListener('click', function (event) {
                event.stopPropagation();
                const open = profileWrap.classList.toggle('open');
                profileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            document.addEventListener('click', function (event) {
                if (profileWrap && !profileWrap.contains(event.target)) {
                    closeProfile();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setSidebar(false, false);
                    closeProfile();
                }
            });

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!form || form.dataset.confirmed === '1') return;

                if (form.classList.contains('js-logout-form')) {
                    event.preventDefault();

                    nexusConfirm({
                        title: 'Keluar dari sistem?',
                        text: 'Sesi admin akan diakhiri.',
                        icon: 'question',
                        iconColor: '#2563eb',
                        yes: 'Ya, Logout',
                        no: 'Batalkan'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = '1';
                            showLoader('Mengakhiri Sesi');

                            setTimeout(function () {
                                form.submit();
                            }, 120);
                        }
                    });

                    return;
                }

                if (form.classList.contains('delete-form') || form.dataset.confirm === 'delete') {
                    event.preventDefault();

                    const button = form.querySelector('[data-name]');
                    const name = button ? button.dataset.name : 'data ini';

                    nexusConfirm({
                        title: form.dataset.title || 'Hapus Data?',
                        text: form.dataset.text || 'Data <b>' + name + '</b> akan dihapus jika tidak memiliki riwayat yang terhubung.',
                        icon: 'warning',
                        iconColor: '#f43f5e',
                        yes: form.dataset.yes || 'Ya, Hapus',
                        no: 'Batalkan',
                        danger: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = '1';
                            form.submit();
                        }
                    });

                    return;
                }

                if (form.classList.contains('reset-form') || form.dataset.confirm === 'reset') {
                    event.preventDefault();

                    const button = form.querySelector('[data-name]');
                    const name = button ? button.dataset.name : 'akun ini';

                    nexusConfirm({
                        title: form.dataset.title || 'Reset Password?',
                        text: form.dataset.text || 'Sistem akan membuat password baru untuk <b>' + name + '</b>.',
                        icon: 'question',
                        iconColor: '#2563eb',
                        yes: form.dataset.yes || 'Ya, Reset',
                        no: 'Batalkan'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = '1';
                            form.submit();
                        }
                    });
                }
            });

            window.copyToClipboard = function (text) {
                if (!text) return;

                const fallback = function () {
                    const input = document.createElement('input');
                    input.value = text;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    input.remove();
                };

                const task = navigator.clipboard?.writeText
                    ? navigator.clipboard.writeText(text)
                    : Promise.resolve(fallback());

                task.then(function () {
                    nexusToast('Tersalin', '', 'success');
                });
            };

            window.addEventListener('pageshow', function () {
                hideLoader();
                initSidebar();
            });

            initSidebar();

            @if(session('success'))
                setTimeout(function () {
                    nexusToast('Berhasil', @json(session('success')), 'success');
                }, 80);
            @endif

            @if(session('error'))
                setTimeout(function () {
                    nexusToast('Perhatian', @json(session('error')), 'error');
                }, 80);
            @endif

            @if(session('warning'))
                setTimeout(function () {
                    nexusToast('Perhatian', @json(session('warning')), 'warning');
                }, 80);
            @endif
        })();
    </script>

    @stack('scripts')
</body>
</html>