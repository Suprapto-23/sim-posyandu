@php
    $admin = auth()->user();
    $adminName = $admin->name ?? 'Administrator';
    $initial = strtoupper(substr($adminName, 0, 1));

    $isDashboard = request()->routeIs('admin.dashboard');
    $isUsers = request()->routeIs('admin.users.*');
    $isBidans = request()->routeIs('admin.bidans.*');
    $isKaders = request()->routeIs('admin.kaders.*');
@endphp

<style>
    /* SIDEBAR ADMIN CLEAN MOBILE */
    .pc-admin-sidebar {
        position: relative; width: 100%; height: calc(100dvh - 20px); min-height: calc(100dvh - 20px);
        display: flex; flex-direction: column; overflow: hidden; border-radius: 28px;
        border: 1px solid rgba(226, 232, 240, .82);
        background: radial-gradient(circle at 50% 0%, rgba(236,253,245,.92), transparent 34%),
                    radial-gradient(circle at 100% 100%, rgba(20,184,166,.10), transparent 32%),
                    linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,255,252,.95));
        box-shadow: 0 24px 70px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.96);
        backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
        overscroll-behavior: contain; touch-action: pan-y;
    }
    .pc-admin-sidebar::before {
        content: ""; position: absolute; width: 260px; height: 260px; top: -120px; left: -110px;
        border-radius: 999px; background: rgba(16,185,129,.12); filter: blur(70px); pointer-events: none;
    }
    .pc-admin-sidebar::after {
        content: ""; position: absolute; width: 250px; height: 250px; right: -120px; bottom: -110px;
        border-radius: 999px; background: rgba(20,184,166,.12); filter: blur(70px); pointer-events: none;
    }

    /* TOP & LOGO */
    .pc-admin-top { position: relative; z-index: 4; flex-shrink: 0; padding: 24px 18px 0; }
    .pc-admin-logo-wrap { display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
    .pc-admin-logo-link { display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
    .pc-admin-logo {
        width: 148px; height: auto; object-fit: contain; display: block;
        filter: drop-shadow(0 12px 22px rgba(15,23,42,.08)) drop-shadow(0 2px 4px rgba(16,185,129,.08));
    }

    /* USER CARD */
    .pc-admin-card {
        display: flex; align-items: center; gap: 14px; padding: 12px 14px; margin-bottom: 24px;
        border-radius: 20px; background: rgba(255,255,255,.6); border: 1px solid rgba(255,255,255,.8);
        box-shadow: 0 4px 20px rgba(0,0,0,.03); backdrop-filter: blur(10px);
    }
    .pc-admin-avatar {
        position: relative; width: 44px; height: 44px; flex-shrink: 0; border-radius: 14px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: flex;
        align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 16px;
        box-shadow: 0 8px 16px rgba(16,185,129,.25);
    }
    .pc-admin-active-dot {
        position: absolute; bottom: -2px; right: -2px; width: 12px; height: 12px;
        background-color: #10b981; border: 2px solid #ffffff; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .pc-admin-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
    .pc-admin-info h4 { margin: 0; color: #1e293b; font-size: 13.5px; font-weight: 900; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pc-admin-info p { margin: 3px 0 0 0; color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }

    /* SCROLL AREA */
    .pc-admin-scroll {
        position: relative; z-index: 3; flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden;
        padding: 0 18px 120px; scrollbar-width: none; -ms-overflow-style: none;
        overscroll-behavior: contain; overscroll-behavior-y: contain; touch-action: pan-y; -webkit-overflow-scrolling: touch;
    }
    .pc-admin-scroll::-webkit-scrollbar { width: 0; height: 0; display: none; }

    /* MENU */
    .pc-admin-menu-group { margin-bottom: 20px; }
    .pc-admin-menu-group:last-child { margin-bottom: 0; }
    .pc-admin-menu-title { margin: 0 0 10px; padding-left: 4px; color: #94a3b8; font-size: 10.5px; font-weight: 900; text-transform: uppercase; letter-spacing: .12em; }
    .pc-admin-menu-list { display: flex; flex-direction: column; gap: 6px; }
    .pc-admin-menu-item {
        position: relative; width: 100%; min-height: 42px; display: flex; align-items: center; gap: 12px;
        padding: 10px 13px; border: 0; border-radius: 15px; background: transparent; color: #475569;
        text-decoration: none; font-size: 13px; font-weight: 800; cursor: pointer; text-align: left;
        transition: background .28s cubic-bezier(.16, 1, .3, 1), color .28s cubic-bezier(.16, 1, .3, 1), transform .28s cubic-bezier(.16, 1, .3, 1), box-shadow .28s cubic-bezier(.16, 1, .3, 1);
    }
    .pc-admin-menu-item:hover { background: rgba(236,253,245,.92); color: #047857; transform: translateX(3px); }
    .pc-admin-menu-item.active {
        background: linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.84));
        color: #047857; font-weight: 900; box-shadow: 0 10px 24px rgba(16,185,129,.08), inset 0 1px 0 rgba(255,255,255,.92);
    }
    .pc-admin-menu-item.active::before {
        content: ""; position: absolute; left: 0; top: 9px; bottom: 9px; width: 4px;
        border-radius: 999px; background: linear-gradient(180deg, #10b981, #059669);
    }
    .pc-admin-menu-icon {
        width: 22px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        color: #94a3b8; font-size: 14px; transition: color .28s ease, transform .28s ease;
    }
    .pc-admin-menu-item:hover .pc-admin-menu-icon, .pc-admin-menu-item.active .pc-admin-menu-icon { color: #059669; transform: scale(1.08); }
    .pc-admin-menu-text { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    
    /* Tombol Logout Spesifik */
    .pc-admin-logout { color: #ef4444; }
    .pc-admin-logout .pc-admin-menu-icon { color: #f87171; }
    .pc-admin-logout:hover { background: #fef2f2; color: #dc2626; }
    .pc-admin-logout:hover .pc-admin-menu-icon { color: #dc2626; transform: scale(1.08); }

    /* BOTTOM DECOR */
    .pc-admin-bottom-decor {
        position: absolute; bottom: 0; left: 0; width: 100%; height: 110px; z-index: 1;
        overflow: hidden; pointer-events: none; border-radius: 0 0 28px 28px;
    }
    .pc-admin-wave { position: absolute; left: -20%; width: 140%; border-radius: 50% 50% 0 0; }
    .pc-admin-wave-1 { bottom: -30px; height: 96px; background: rgba(16,185,129,.14); }
    .pc-admin-wave-2 { bottom: -45px; height: 106px; background: rgba(5,150,105,.13); }
    .pc-admin-wave-3 { bottom: -55px; height: 116px; background: rgba(20,184,166,.10); }
    .pc-admin-plant { position: absolute; right: 22px; bottom: 12px; width: 64px; height: 64px; }
    .pc-admin-stem {
        position: absolute; left: 31px; bottom: 0; width: 3px; height: 46px; border-radius: 999px;
        background: rgba(4,120,87,.35); transform: rotate(18deg); transform-origin: bottom;
    }
    .pc-admin-leaf {
        position: absolute; width: 31px; height: 16px; border-radius: 100% 0 100% 0;
        background: linear-gradient(135deg, rgba(4,120,87,.66), rgba(16,185,129,.24)); transform-origin: bottom left;
    }
    .pc-admin-leaf-1 { right: 19px; bottom: 23px; transform: rotate(-34deg); }
    .pc-admin-leaf-2 { right: 32px; bottom: 35px; transform: rotate(-8deg) scale(.9); }
    .pc-admin-leaf-3 { right: 7px; bottom: 36px; transform: rotate(28deg) scale(.86); }
    .pc-admin-leaf-4 { right: 25px; bottom: 11px; transform: rotate(46deg) scale(.72); }

    /* ANIMATION */
    .pc-admin-top, .pc-admin-menu-group { opacity: 0; transform: translateY(16px); animation: pcAdminSidebarIn .85s cubic-bezier(.22, 1, .36, 1) forwards; }
    .pc-admin-bottom-decor { opacity: 0; animation: pcAdminDecorIn .85s ease forwards; }
    .pc-admin-top { animation-delay: .06s; }
    .pc-admin-menu-group:nth-child(1) { animation-delay: .14s; }
    .pc-admin-menu-group:nth-child(2) { animation-delay: .22s; }
    .pc-admin-bottom-decor { animation-delay: .30s; }

    @keyframes pcAdminSidebarIn { to { opacity: 1; transform: translateY(0); } }
    @keyframes pcAdminDecorIn { to { opacity: 1; } }
    @media (max-width: 420px) {
        .pc-admin-sidebar { border-radius: 24px; }
        .pc-admin-top { padding: 22px 16px 0; }
        .pc-admin-scroll { padding: 0 16px 120px; }
        .pc-admin-logo { width: 138px; }
    }
</style>

<div class="pc-admin-sidebar">

    <div class="pc-admin-top">
        <div class="pc-admin-logo-wrap">
            <a href="{{ route('admin.dashboard') }}" class="pc-admin-logo-link">
                <img src="{{ asset('img/logo.webp') }}" alt="Logo PosyanduCare" class="pc-admin-logo" onerror="this.src='{{ asset('img/logo.png') }}'">
            </a>
        </div>

        <div class="pc-admin-card">
            <div class="pc-admin-avatar">
                {{ $initial }}
                <span class="pc-admin-active-dot" title="Portal Aktif"></span>
            </div>
            <div class="pc-admin-info">
                <h4 title="{{ $adminName }}">{{ $adminName }}</h4>
                <p>Administrator</p>
            </div>
        </div>
    </div>

    <div class="pc-admin-scroll" id="adminSidebarScrollArea">

        <div class="pc-admin-menu-group">
            <p class="pc-admin-menu-title">Menu Utama</p>
            <div class="pc-admin-menu-list">
                <a href="{{ route('admin.dashboard') }}" class="pc-admin-menu-item {{ $isDashboard ? 'active' : '' }}">
                    <span class="pc-admin-menu-icon"><i class="fa-solid fa-house"></i></span>
                    <span class="pc-admin-menu-text">Dashboard</span>
                </a>
            </div>
        </div>

        <div class="pc-admin-menu-group">
            <p class="pc-admin-menu-title">Manajemen Akun</p>
            <div class="pc-admin-menu-list">
                <a href="{{ route('admin.users.index') }}" class="pc-admin-menu-item {{ $isUsers ? 'active' : '' }}">
                    <span class="pc-admin-menu-icon"><i class="fa-solid fa-users"></i></span>
                    <span class="pc-admin-menu-text">Kelola Warga</span>
                </a>
                <a href="{{ route('admin.bidans.index') }}" class="pc-admin-menu-item {{ $isBidans ? 'active' : '' }}">
                    <span class="pc-admin-menu-icon"><i class="fa-solid fa-user-doctor"></i></span>
                    <span class="pc-admin-menu-text">Kelola Bidan</span>
                </a>
                <a href="{{ route('admin.kaders.index') }}" class="pc-admin-menu-item {{ $isKaders ? 'active' : '' }}">
                    <span class="pc-admin-menu-icon"><i class="fa-solid fa-user-nurse"></i></span>
                    <span class="pc-admin-menu-text">Kelola Kader</span>
                </a>
            </div>
        </div>

        <div class="pc-admin-menu-group">
            <p class="pc-admin-menu-title">Sesi Akun</p>
            <div class="pc-admin-menu-list">
                <form method="POST" action="{{ route('logout') }}" class="js-logout-form m-0 p-0">
                    @csrf
                    <button type="submit" class="pc-admin-menu-item pc-admin-logout">
                        <span class="pc-admin-menu-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span class="pc-admin-menu-text">Keluar Aplikasi</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <div class="pc-admin-bottom-decor" aria-hidden="true">
        <div class="pc-admin-wave pc-admin-wave-1"></div>
        <div class="pc-admin-wave pc-admin-wave-2"></div>
        <div class="pc-admin-wave pc-admin-wave-3"></div>
        <div class="pc-admin-plant">
            <span class="pc-admin-leaf pc-admin-leaf-1"></span>
            <span class="pc-admin-leaf pc-admin-leaf-2"></span>
            <span class="pc-admin-leaf pc-admin-leaf-3"></span>
            <span class="pc-admin-leaf pc-admin-leaf-4"></span>
            <span class="pc-admin-stem"></span>
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scrollArea = document.getElementById('adminSidebarScrollArea');
            if (!scrollArea) return;

            scrollArea.addEventListener('wheel', function (event) {
                const delta = event.deltaY;
                const atTop = scrollArea.scrollTop <= 0;
                const atBottom = Math.ceil(scrollArea.scrollTop + scrollArea.clientHeight) >= scrollArea.scrollHeight;
                if ((delta < 0 && atTop) || (delta > 0 && atBottom)) event.preventDefault();
                event.stopPropagation();
            }, { passive: false });

            let touchStartY = 0;
            scrollArea.addEventListener('touchstart', function (event) {
                if (event.touches.length > 0) touchStartY = event.touches[0].clientY;
            }, { passive: true });

            scrollArea.addEventListener('touchmove', function (event) {
                if (event.touches.length === 0) return;
                const touchY = event.touches[0].clientY;
                const delta = touchStartY - touchY;
                const atTop = scrollArea.scrollTop <= 0;
                const atBottom = Math.ceil(scrollArea.scrollTop + scrollArea.clientHeight) >= scrollArea.scrollHeight;
                if ((delta < 0 && atTop) || (delta > 0 && atBottom)) event.preventDefault();
                event.stopPropagation();
            }, { passive: false });
        });
    </script>
@endonce