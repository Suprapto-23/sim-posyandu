@php
    use Illuminate\Support\Str;

    /*
    |--------------------------------------------------------------------------
    | Sidebar Bidan PosyanduCare - Clean Medical Green Style
    |--------------------------------------------------------------------------
    | Cocok dengan layout bidan baru:
    | - tidak pakai <aside> lagi
    | - hanya isi sidebar
    | - style konsisten dengan admin dan kader
    | - dekorasi bawah ikut scroll dan ada di bawah logout
    */

    $route = request()->route()?->getName() ?? '';

    $bidanName = Auth::user()->name ?? 'Bidan Posyandu';
    $initial = strtoupper(substr($bidanName, 0, 1));

    try {
        $pendingCount = class_exists('\App\Models\Pemeriksaan')
            ? \App\Models\Pemeriksaan::where('status_verifikasi', 'pending')->count()
            : 0;
    } catch (\Throwable $e) {
        $pendingCount = 0;
    }

    $menusUtama = [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-house',
            'route' => route('bidan.dashboard'),
            'active' => $route === 'bidan.dashboard',
        ],
    ];

    $menusLayanan = [
        [
            'label' => 'Pemeriksaan Klinis',
            'icon' => 'fa-stethoscope',
            'route' => route('bidan.pemeriksaan.index'),
            'active' => Str::startsWith($route, 'bidan.pemeriksaan'),
            'badge' => $pendingCount,
        ],
        [
            'label' => 'Vaksinasi & Imunisasi',
            'icon' => 'fa-syringe',
            'route' => route('bidan.imunisasi.index'),
            'active' => Str::startsWith($route, 'bidan.imunisasi'),
            'badge' => 0,
        ],
    ];

    $menusDatabase = [
        [
            'label' => 'Rekam Medis',
            'icon' => 'fa-folder-open',
            'route' => route('bidan.rekam-medis.index'),
            'active' => Str::startsWith($route, 'bidan.rekam-medis'),
            'badge' => 0,
        ],
    ];

    $menusAdministrasi = [
        [
            'label' => 'Kelola Jadwal',
            'icon' => 'fa-calendar-check',
            'route' => route('bidan.jadwal.index'),
            'active' => Str::startsWith($route, 'bidan.jadwal'),
            'badge' => 0,
        ],
    ];
@endphp

<style>
    /*
    |--------------------------------------------------------------------------
    | SIDEBAR BIDAN FINAL
    |--------------------------------------------------------------------------
    */

    .pc-bidan-sidebar {
        position: relative;
        width: 100%;
        height: calc(100dvh - 28px);
        min-height: calc(100dvh - 28px);

        display: flex;
        flex-direction: column;

        overflow: hidden;

        border-radius: 28px;
        border: 1px solid rgba(226, 232, 240, .78);

        background:
            radial-gradient(circle at 50% 0%, rgba(236,253,245,.86), transparent 34%),
            linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,255,252,.94));

        box-shadow:
            0 24px 70px rgba(15,23,42,.09),
            inset 0 1px 0 rgba(255,255,255,.95);

        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);

        overscroll-behavior: contain;
        touch-action: pan-y;
    }

    /*
    |--------------------------------------------------------------------------
    | TOP AREA
    |--------------------------------------------------------------------------
    */

    .pc-bidan-top {
        position: relative;
        z-index: 4;
        flex-shrink: 0;

        padding: 24px 18px 0;
        background: transparent;
    }

    .pc-bidan-logo-wrap {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .pc-bidan-logo-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .pc-bidan-logo {
        width: 154px;
        height: auto;

        object-fit: contain;
        display: block;

        filter:
            drop-shadow(0 12px 22px rgba(15,23,42,.08))
            drop-shadow(0 2px 4px rgba(16,185,129,.08));
    }

    .pc-bidan-user-card {
        display: flex;
        align-items: center;
        gap: 13px;

        padding: 14px;
        margin-bottom: 16px;

        border-radius: 22px;
        background:
            linear-gradient(135deg, rgba(255,255,255,.88), rgba(248,255,252,.78));

        border: 1px solid rgba(209,250,229,.95);

        box-shadow:
            0 16px 34px rgba(15,23,42,.06),
            inset 0 1px 0 rgba(255,255,255,.95);
    }

    .pc-bidan-avatar {
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

    .pc-bidan-user-info {
        flex: 1;
        min-width: 0;
    }

    .pc-bidan-user-info h4 {
        margin: 0;

        color: #064e3b;
        font-size: 13.5px;
        font-weight: 900;
        line-height: 1.2;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pc-bidan-user-info p {
        margin: 3px 0 6px;

        color: #64748b;
        font-size: 11px;
        font-weight: 750;
    }

    .pc-bidan-online {
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

    .pc-bidan-online span {
        width: 6px;
        height: 6px;

        border-radius: 999px;
        background: #10b981;

        box-shadow: 0 0 0 3px rgba(16,185,129,.12);
    }

    /*
    |--------------------------------------------------------------------------
    | SCROLL AREA
    |--------------------------------------------------------------------------
    */

    .pc-bidan-scroll {
        position: relative;
        z-index: 3;

        flex: 1;
        min-height: 0;

        overflow-y: auto;
        overflow-x: hidden;

        padding: 0 18px 0;

        scrollbar-width: none;
        -ms-overflow-style: none;

        overscroll-behavior: contain;
        overscroll-behavior-y: contain;
        touch-action: pan-y;
        -webkit-overflow-scrolling: touch;
    }

    .pc-bidan-scroll::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }

    /*
    |--------------------------------------------------------------------------
    | MENU
    |--------------------------------------------------------------------------
    */

    .pc-bidan-menu-group {
        margin-bottom: 20px;
    }

    .pc-bidan-menu-group:last-child {
        margin-bottom: 0;
    }

    .pc-bidan-menu-title {
        margin: 0 0 10px;
        padding-left: 4px;

        color: #64748b;
        font-size: 10.5px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .12em;
    }

    .pc-bidan-menu-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .pc-bidan-menu-item {
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

        color: #334155;
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

    .pc-bidan-menu-item:hover {
        background: rgba(236,253,245,.92);
        color: #047857;
        transform: translateX(3px);
    }

    .pc-bidan-menu-item.active {
        background:
            linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.82));

        color: #047857;
        font-weight: 900;

        box-shadow:
            0 10px 24px rgba(16,185,129,.08),
            inset 0 1px 0 rgba(255,255,255,.92);
    }

    .pc-bidan-menu-item.active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 9px;
        bottom: 9px;

        width: 4px;

        border-radius: 999px;
        background: linear-gradient(180deg, #10b981, #059669);
    }

    .pc-bidan-menu-icon {
        width: 22px;
        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        color: #64748b;
        font-size: 13px;

        transition: color .28s ease;
    }

    .pc-bidan-menu-item:hover .pc-bidan-menu-icon,
    .pc-bidan-menu-item.active .pc-bidan-menu-icon {
        color: #059669;
    }

    .pc-bidan-menu-text {
        flex: 1;
        min-width: 0;

        text-align: left;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pc-bidan-menu-badge {
        min-width: 22px;
        height: 22px;

        padding: 0 7px;

        border-radius: 999px;
        background: #fff1f2;
        color: #e11d48;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        font-size: 10px;
        font-weight: 900;

        box-shadow:
            0 8px 18px rgba(244,63,94,.12),
            inset 0 1px 0 rgba(255,255,255,.85);
    }

    .pc-bidan-menu-item.active .pc-bidan-menu-badge {
        background: rgba(255,255,255,.95);
        color: #e11d48;
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    .pc-bidan-logout-form {
        margin: 0;
        padding: 0;
    }

    .pc-bidan-logout {
        color: #ef4444;
    }

    .pc-bidan-logout .pc-bidan-menu-icon {
        color: #ef4444;
    }

    .pc-bidan-logout:hover {
        background: #fff1f2;
        color: #dc2626;
    }

    .pc-bidan-logout:hover .pc-bidan-menu-icon {
        color: #dc2626;
    }

    /*
    |--------------------------------------------------------------------------
    | BOTTOM DECORATION IKUT SCROLL
    |--------------------------------------------------------------------------
    */

    .pc-bidan-bottom-decor {
        position: relative;

        z-index: 1;

        height: 96px;
        min-height: 96px;

        margin: 8px -18px 0;

        overflow: hidden;
        pointer-events: none;
    }

    .pc-bidan-wave {
        position: absolute;
        left: -20%;
        width: 140%;

        border-radius: 50% 50% 0 0;
    }

    .pc-bidan-wave-1 {
        bottom: -54px;
        height: 96px;
        background: rgba(16,185,129,.14);
    }

    .pc-bidan-wave-2 {
        bottom: -67px;
        height: 106px;
        background: rgba(5,150,105,.13);
    }

    .pc-bidan-wave-3 {
        bottom: -78px;
        height: 116px;
        background: rgba(20,184,166,.10);
    }

    .pc-bidan-plant {
        position: absolute;
        right: 22px;
        bottom: 11px;

        width: 64px;
        height: 64px;
    }

    .pc-bidan-stem {
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

    .pc-bidan-leaf {
        position: absolute;

        width: 31px;
        height: 16px;

        border-radius: 100% 0 100% 0;
        background:
            linear-gradient(135deg, rgba(4,120,87,.66), rgba(16,185,129,.24));

        transform-origin: bottom left;
    }

    .pc-bidan-leaf-1 {
        right: 19px;
        bottom: 23px;
        transform: rotate(-34deg);
    }

    .pc-bidan-leaf-2 {
        right: 32px;
        bottom: 35px;
        transform: rotate(-8deg) scale(.9);
    }

    .pc-bidan-leaf-3 {
        right: 7px;
        bottom: 36px;
        transform: rotate(28deg) scale(.86);
    }

    .pc-bidan-leaf-4 {
        right: 25px;
        bottom: 11px;
        transform: rotate(46deg) scale(.72);
    }

    /*
    |--------------------------------------------------------------------------
    | ANIMATION
    |--------------------------------------------------------------------------
    */

    .pc-bidan-top,
    .pc-bidan-menu-group,
    .pc-bidan-bottom-decor {
        opacity: 0;
        transform: translateY(16px);
        animation: pcBidanSidebarIn .85s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .pc-bidan-top {
        animation-delay: .06s;
    }

    .pc-bidan-menu-group:nth-child(1) {
        animation-delay: .14s;
    }

    .pc-bidan-menu-group:nth-child(2) {
        animation-delay: .22s;
    }

    .pc-bidan-menu-group:nth-child(3) {
        animation-delay: .30s;
    }

    .pc-bidan-menu-group:nth-child(4) {
        animation-delay: .38s;
    }

    .pc-bidan-menu-group:nth-child(5) {
        animation-delay: .46s;
    }

    .pc-bidan-bottom-decor {
        animation-delay: .54s;
    }

    @keyframes pcBidanSidebarIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 1023px) {
        .pc-bidan-sidebar {
            height: calc(100dvh - 20px);
            min-height: calc(100dvh - 20px);
            border-radius: 24px;
        }

        .pc-bidan-top {
            padding: 22px 16px 0;
        }

        .pc-bidan-scroll {
            padding: 0 16px 0;
        }

        .pc-bidan-logo {
            width: 142px;
        }

        .pc-bidan-bottom-decor {
            margin-left: -16px;
            margin-right: -16px;
        }
    }
</style>

<div class="pc-bidan-sidebar">

    {{-- TOP AREA --}}
    <div class="pc-bidan-top">

        {{-- LOGO --}}
        <div class="pc-bidan-logo-wrap">
            <a href="{{ route('bidan.dashboard') }}" class="js-nav-link spa-route smooth-route pc-bidan-logo-link">
                <img
                    src="{{ asset('img/logo.png') }}"
                    alt="Logo PosyanduCare"
                    class="pc-bidan-logo"
                >
            </a>
        </div>

        {{-- USER CARD --}}
        <div class="pc-bidan-user-card">
            <div class="pc-bidan-avatar">
                {{ $initial }}
            </div>

            <div class="pc-bidan-user-info">
                <h4>{{ $bidanName }}</h4>
                <p>Tenaga Bidan</p>

                <div class="pc-bidan-online">
                    <span></span>
                    Akses Klinis Aktif
                </div>
            </div>
        </div>
    </div>

    {{-- SCROLL MENU --}}
    <div class="pc-bidan-scroll" id="bidanSidebarScrollArea">

        {{-- MENU UTAMA --}}
        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">
                Menu Utama
            </p>

            <div class="pc-bidan-menu-list">
                @foreach($menusUtama as $menu)
                    <a
                        href="{{ $menu['route'] }}"
                        class="js-nav-link spa-route smooth-route pc-bidan-menu-item {{ $menu['active'] ? 'active' : '' }}"
                    >
                        <span class="pc-bidan-menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>

                        <span class="pc-bidan-menu-text">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- LAYANAN MEDIS --}}
        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">
                Layanan Medis
            </p>

            <div class="pc-bidan-menu-list">
                @foreach($menusLayanan as $menu)
                    <a
                        href="{{ $menu['route'] }}"
                        class="js-nav-link spa-route smooth-route pc-bidan-menu-item {{ $menu['active'] ? 'active' : '' }}"
                    >
                        <span class="pc-bidan-menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>

                        <span class="pc-bidan-menu-text">
                            {{ $menu['label'] }}
                        </span>

                        @if(!empty($menu['badge']) && $menu['badge'] > 0)
                            <span class="pc-bidan-menu-badge">
                                {{ $menu['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- DATABASE --}}
        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">
                Arsip & Database
            </p>

            <div class="pc-bidan-menu-list">
                @foreach($menusDatabase as $menu)
                    <a
                        href="{{ $menu['route'] }}"
                        class="js-nav-link spa-route smooth-route pc-bidan-menu-item {{ $menu['active'] ? 'active' : '' }}"
                    >
                        <span class="pc-bidan-menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>

                        <span class="pc-bidan-menu-text">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ADMINISTRASI --}}
        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">
                Administrasi
            </p>

            <div class="pc-bidan-menu-list">
                @foreach($menusAdministrasi as $menu)
                    <a
                        href="{{ $menu['route'] }}"
                        class="js-nav-link spa-route smooth-route pc-bidan-menu-item {{ $menu['active'] ? 'active' : '' }}"
                    >
                        <span class="pc-bidan-menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>

                        <span class="pc-bidan-menu-text">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- SESI AKUN --}}
        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">
                Sesi Akun
            </p>

            <form method="POST" action="{{ route('logout') }}" class="pc-bidan-logout-form">
                @csrf

                <button type="submit" class="pc-bidan-menu-item pc-bidan-logout">
                    <span class="pc-bidan-menu-icon">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </span>

                    <span class="pc-bidan-menu-text">
                        Keluar
                    </span>
                </button>
            </form>
        </div>

        {{-- DEKORASI IKUT SCROLL DAN POSISINYA SETELAH KELUAR --}}
        <div class="pc-bidan-bottom-decor" aria-hidden="true">
            <div class="pc-bidan-wave pc-bidan-wave-1"></div>
            <div class="pc-bidan-wave pc-bidan-wave-2"></div>
            <div class="pc-bidan-wave pc-bidan-wave-3"></div>

            <div class="pc-bidan-plant">
                <span class="pc-bidan-leaf pc-bidan-leaf-1"></span>
                <span class="pc-bidan-leaf pc-bidan-leaf-2"></span>
                <span class="pc-bidan-leaf pc-bidan-leaf-3"></span>
                <span class="pc-bidan-leaf pc-bidan-leaf-4"></span>
                <span class="pc-bidan-stem"></span>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scrollArea = document.getElementById('bidanSidebarScrollArea');

            if (!scrollArea) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Stop scroll leak
            |--------------------------------------------------------------------------
            | Scroll sidebar tidak akan nyeret main content.
            | Karena browser kadang merasa jadi sutradara, padahal cuma disuruh scroll.
            */

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