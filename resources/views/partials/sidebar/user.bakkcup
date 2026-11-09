@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $route = request()->route()?->getName() ?? '';

    $userName = Auth::user()->name ?? 'Warga Posyandu';
    $initial = strtoupper(substr(trim($userName), 0, 1)) ?: 'W';

    $routeTo = function (string $name, array $params = []) {
        return Route::has($name) ? route($name, $params) : '#';
    };

    $isDashboard = $route === 'user.dashboard';
    $isJadwal = Str::startsWith($route, ['user.jadwal']);
    $isMonitoring = Str::startsWith($route, ['user.monitoring', 'user.balita', 'user.remaja', 'user.lansia']);
    $isRiwayat = Str::startsWith($route, ['user.riwayat']);

    $mainMenus = [
        [
            'label' => 'Beranda',
            'icon' => 'fa-house',
            'route' => $routeTo('user.dashboard'),
            'active' => $isDashboard,
        ],
        [
            'label' => 'Agenda Posyandu',
            'icon' => 'fa-calendar-days',
            'route' => $routeTo('user.jadwal.index'),
            'active' => $isJadwal,
        ],
    ];

    $healthMenus = [
        [
            'label' => 'Pantau Kesehatan',
            'icon' => 'fa-heart-pulse',
            'route' => $routeTo('user.monitoring.index'),
            'active' => $isMonitoring,
        ],
        [
            'label' => 'Riwayat Terpadu',
            'icon' => 'fa-notes-medical',
            'route' => $routeTo('user.riwayat.index'),
            'active' => $isRiwayat,
        ],
    ];
@endphp

<style>
    /*
    |--------------------------------------------------------------------------
    | USER SIDEBAR CLEAN MOBILE
    |--------------------------------------------------------------------------
    */

    .pc-user-sidebar {
        position: relative;
        width: 100%;
        height: calc(100dvh - 20px);
        min-height: calc(100dvh - 20px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(226, 232, 240, .82);
        background:
            radial-gradient(circle at 50% 0%, rgba(236,253,245,.92), transparent 34%),
            radial-gradient(circle at 100% 100%, rgba(20,184,166,.10), transparent 32%),
            linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,255,252,.95));
        box-shadow:
            0 24px 70px rgba(15,23,42,.12),
            inset 0 1px 0 rgba(255,255,255,.96);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        overscroll-behavior: contain;
        touch-action: pan-y;
    }

    .pc-user-sidebar::before {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        top: -120px;
        left: -110px;
        border-radius: 999px;
        background: rgba(16,185,129,.12);
        filter: blur(70px);
        pointer-events: none;
    }

    .pc-user-sidebar::after {
        content: "";
        position: absolute;
        width: 250px;
        height: 250px;
        right: -120px;
        bottom: -110px;
        border-radius: 999px;
        background: rgba(20,184,166,.12);
        filter: blur(70px);
        pointer-events: none;
    }

    /*
    |--------------------------------------------------------------------------
    | TOP & LOGO
    |--------------------------------------------------------------------------
    */

    .pc-user-top {
        position: relative;
        z-index: 4;
        flex-shrink: 0;
        padding: 24px 18px 0;
    }

    .pc-user-logo-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
    }

    .pc-user-logo-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .pc-user-logo {
        width: 148px;
        height: auto;
        object-fit: contain;
        display: block;
        filter:
            drop-shadow(0 12px 22px rgba(15,23,42,.08))
            drop-shadow(0 2px 4px rgba(16,185,129,.08));
    }

    /*
    |--------------------------------------------------------------------------
    | REFINED USER CARD (Sleek & Modern)
    |--------------------------------------------------------------------------
    */

    .pc-user-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 14px;
        margin-bottom: 24px;
        border-radius: 20px;
        background: rgba(255,255,255,.6);
        border: 1px solid rgba(255,255,255,.8);
        box-shadow: 0 4px 20px rgba(0,0,0,.03);
        backdrop-filter: blur(10px);
    }

    .pc-user-avatar {
        position: relative;
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 900;
        font-size: 16px;
        box-shadow: 0 8px 16px rgba(16,185,129,.25);
    }

    .pc-user-active-dot {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 12px;
        height: 12px;
        background-color: #10b981;
        border: 2px solid #ffffff;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .pc-user-info {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .pc-user-info h4 {
        margin: 0;
        color: #1e293b;
        font-size: 13.5px;
        font-weight: 900;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pc-user-info p {
        margin: 3px 0 0 0;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /*
    |--------------------------------------------------------------------------
    | SCROLL AREA
    |--------------------------------------------------------------------------
    */

    .pc-user-scroll {
        position: relative;
        z-index: 3;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        /* Padding bawah ditambahkan agar menu terakhir tidak tertutup tanaman */
        padding: 0 18px 120px;
        scrollbar-width: none;
        -ms-overflow-style: none;
        overscroll-behavior: contain;
        overscroll-behavior-y: contain;
        touch-action: pan-y;
        -webkit-overflow-scrolling: touch;
    }

    .pc-user-scroll::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }

    /*
    |--------------------------------------------------------------------------
    | MENU
    |--------------------------------------------------------------------------
    */

    .pc-user-menu-group {
        margin-bottom: 20px;
    }

    .pc-user-menu-group:last-child {
        margin-bottom: 0;
    }

    .pc-user-menu-title {
        margin: 0 0 10px;
        padding-left: 4px;
        color: #94a3b8;
        font-size: 10.5px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .12em;
    }

    .pc-user-menu-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .pc-user-menu-item {
        position: relative;
        width: 100%;
        min-height: 42px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 13px;
        border: 0;
        border-radius: 15px;
        background: transparent;
        color: #475569;
        text-decoration: none;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition:
            background .28s cubic-bezier(.16, 1, .3, 1),
            color .28s cubic-bezier(.16, 1, .3, 1),
            transform .28s cubic-bezier(.16, 1, .3, 1),
            box-shadow .28s cubic-bezier(.16, 1, .3, 1);
    }

    .pc-user-menu-item:hover {
        background: rgba(236,253,245,.92);
        color: #047857;
        transform: translateX(3px);
    }

    .pc-user-menu-item.active {
        background: linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.84));
        color: #047857;
        font-weight: 900;
        box-shadow:
            0 10px 24px rgba(16,185,129,.08),
            inset 0 1px 0 rgba(255,255,255,.92);
    }

    .pc-user-menu-item.active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 9px;
        bottom: 9px;
        width: 4px;
        border-radius: 999px;
        background: linear-gradient(180deg, #10b981, #059669);
    }

    .pc-user-menu-icon {
        width: 22px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 14px;
        transition:
            color .28s ease,
            transform .28s ease;
    }

    .pc-user-menu-item:hover .pc-user-menu-icon,
    .pc-user-menu-item.active .pc-user-menu-icon {
        color: #059669;
        transform: scale(1.08);
    }

    .pc-user-menu-text {
        flex: 1;
        min-width: 0;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /*
    |--------------------------------------------------------------------------
    | BOTTOM DECOR TERKUNCI DI BAWAH (ABSOLUTE)
    |--------------------------------------------------------------------------
    */

    .pc-user-bottom-decor {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 110px;
        z-index: 1;
        overflow: hidden;
        pointer-events: none;
        border-radius: 0 0 28px 28px;
    }

    .pc-user-wave {
        position: absolute;
        left: -20%;
        width: 140%;
        border-radius: 50% 50% 0 0;
    }

    .pc-user-wave-1 {
        bottom: -30px;
        height: 96px;
        background: rgba(16,185,129,.14);
    }

    .pc-user-wave-2 {
        bottom: -45px;
        height: 106px;
        background: rgba(5,150,105,.13);
    }

    .pc-user-wave-3 {
        bottom: -55px;
        height: 116px;
        background: rgba(20,184,166,.10);
    }

    .pc-user-plant {
        position: absolute;
        right: 22px;
        bottom: 12px;
        width: 64px;
        height: 64px;
    }

    .pc-user-stem {
        position: absolute;
        left: 31px;
        bottom: 0;
        width: 3px;
        height: 46px;
        border-radius: 999px;
        background: rgba(4,120,87,.35);
        transform: rotate(18deg);
        transform-origin: bottom;
    }

    .pc-user-leaf {
        position: absolute;
        width: 31px;
        height: 16px;
        border-radius: 100% 0 100% 0;
        background: linear-gradient(135deg, rgba(4,120,87,.66), rgba(16,185,129,.24));
        transform-origin: bottom left;
    }

    .pc-user-leaf-1 { right: 19px; bottom: 23px; transform: rotate(-34deg); }
    .pc-user-leaf-2 { right: 32px; bottom: 35px; transform: rotate(-8deg) scale(.9); }
    .pc-user-leaf-3 { right: 7px; bottom: 36px; transform: rotate(28deg) scale(.86); }
    .pc-user-leaf-4 { right: 25px; bottom: 11px; transform: rotate(46deg) scale(.72); }

    /*
    |--------------------------------------------------------------------------
    | ANIMATION
    |--------------------------------------------------------------------------
    */

    .pc-user-top,
    .pc-user-menu-group {
        opacity: 0;
        transform: translateY(16px);
        animation: pcUserSidebarIn .85s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .pc-user-bottom-decor {
        opacity: 0;
        animation: pcUserDecorIn .85s ease forwards;
    }

    .pc-user-top { animation-delay: .06s; }
    .pc-user-menu-group:nth-child(1) { animation-delay: .14s; }
    .pc-user-menu-group:nth-child(2) { animation-delay: .22s; }
    .pc-user-bottom-decor { animation-delay: .30s; }

    @keyframes pcUserSidebarIn {
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pcUserDecorIn {
        to { opacity: 1; }
    }

    @media (max-width: 420px) {
        .pc-user-sidebar { border-radius: 24px; }
        .pc-user-top { padding: 22px 16px 0; }
        .pc-user-scroll { padding: 0 16px 120px; }
        .pc-user-logo { width: 138px; }
    }
</style>

<div class="pc-user-sidebar">

    {{-- TOP --}}
    <div class="pc-user-top">

        {{-- LOGO --}}
        <div class="pc-user-logo-wrap">
            <a href="{{ route('user.dashboard') }}" class="js-nav-link pc-user-logo-link">
                <img
                    src="{{ asset('img/logo.webp') }}"
                    alt="Logo PosyanduCare"
                    class="pc-user-logo"
                >
            </a>
        </div>

        {{-- USER CARD (REFINED) --}}
        <div class="pc-user-card">
            <div class="pc-user-avatar">
                {{ $initial }}
                {{-- Indikator Portal Aktif Ala Online Status --}}
                <span class="pc-user-active-dot" title="Portal Aktif"></span>
            </div>

            <div class="pc-user-info">
                <h4>{{ ucwords($userName) }}</h4>
                <p>Akun Warga</p>
            </div>
        </div>
    </div>

    {{-- SCROLL MENU --}}
    <div class="pc-user-scroll" id="userSidebarScrollArea">

        {{-- MENU UTAMA --}}
        <div class="pc-user-menu-group">
            <p class="pc-user-menu-title">
                Menu Utama
            </p>

            <div class="pc-user-menu-list">
                @foreach($mainMenus as $menu)
                    <a href="{{ $menu['route'] }}" class="js-nav-link pc-user-menu-item {{ $menu['active'] ? 'active' : '' }}">
                        <span class="pc-user-menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>
                        <span class="pc-user-menu-text">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- KESEHATAN --}}
        <div class="pc-user-menu-group">
            <p class="pc-user-menu-title">
                Kesehatan
            </p>

            <div class="pc-user-menu-list">
                @foreach($healthMenus as $menu)
                    <a href="{{ $menu['route'] }}" class="js-nav-link pc-user-menu-item {{ $menu['active'] ? 'active' : '' }}">
                        <span class="pc-user-menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>
                        <span class="pc-user-menu-text">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

    </div>

    {{-- DEKORASI BAWAH TERKUNCI MENTOK DI BAWAH (Di Luar Scroll Area) --}}
    <div class="pc-user-bottom-decor" aria-hidden="true">
        <div class="pc-user-wave pc-user-wave-1"></div>
        <div class="pc-user-wave pc-user-wave-2"></div>
        <div class="pc-user-wave pc-user-wave-3"></div>

        <div class="pc-user-plant">
            <span class="pc-user-leaf pc-user-leaf-1"></span>
            <span class="pc-user-leaf pc-user-leaf-2"></span>
            <span class="pc-user-leaf pc-user-leaf-3"></span>
            <span class="pc-user-leaf pc-user-leaf-4"></span>
            <span class="pc-user-stem"></span>
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scrollArea = document.getElementById('userSidebarScrollArea');

            if (!scrollArea) {
                return;
            }

            /* Stop scroll leak */
            scrollArea.addEventListener('wheel', function (event) {
                const delta = event.deltaY;
                const atTop = scrollArea.scrollTop <= 0;
                const atBottom = Math.ceil(scrollArea.scrollTop + scrollArea.clientHeight) >= scrollArea.scrollHeight;

                if ((delta < 0 && atTop) || (delta > 0 && atBottom)) {
                    event.preventDefault();
                }

                event.stopPropagation();
            }, { passive: false });

            let touchStartY = 0;

            scrollArea.addEventListener('touchstart', function (event) {
                if (event.touches.length > 0) {
                    touchStartY = event.touches[0].clientY;
                }
            }, { passive: true });

            scrollArea.addEventListener('touchmove', function (event) {
                if (event.touches.length === 0) {
                    return;
                }

                const touchY = event.touches[0].clientY;
                const delta = touchStartY - touchY;

                const atTop = scrollArea.scrollTop <= 0;
                const atBottom = Math.ceil(scrollArea.scrollTop + scrollArea.clientHeight) >= scrollArea.scrollHeight;

                if ((delta < 0 && atTop) || (delta > 0 && atBottom)) {
                    event.preventDefault();
                }

                event.stopPropagation();
            }, { passive: false });
        });
    </script>
@endonce