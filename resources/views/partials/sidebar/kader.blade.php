@php
    use Illuminate\Support\Str;

    $route = request()->route()?->getName() ?? '';

    $kaderName = Auth::user()->name ?? 'Kader Posyandu';
    $initial = strtoupper(substr($kaderName, 0, 1));

    $isDataWarga = Str::startsWith($route, 'kader.data.');

    $menusUtama = [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-house',
            'route' => route('kader.dashboard'),
            'active' => $route === 'kader.dashboard',
        ],
        [
            'label' => 'Registrasi Hadir',
            'icon' => 'fa-user-check',
            'route' => route('kader.absensi.index'),
            'active' => $route === 'kader.absensi.index',
        ],
        [
            'label' => 'Pengukuran Fisik',
            'icon' => 'fa-stethoscope',
            'route' => route('kader.pemeriksaan.index'),
            'active' => Str::startsWith($route, 'kader.pemeriksaan'),
        ],
        [
            'label' => 'Log Imunisasi',
            'icon' => 'fa-syringe',
            'route' => route('kader.imunisasi.index'),
            'active' => Str::startsWith($route, 'kader.imunisasi'),
        ],
    ];

    $dataPasienMenus = [
        [
            'label' => 'Balita & Anak',
            'route' => route('kader.data.balita.index'),
            'active' => Str::startsWith($route, 'kader.data.balita'),
        ],
        [
            'label' => 'Ibu Hamil',
            'route' => route('kader.data.ibu-hamil.index'),
            'active' => Str::startsWith($route, 'kader.data.ibu-hamil'),
        ],
        [
            'label' => 'Remaja',
            'route' => route('kader.data.remaja.index'),
            'active' => Str::startsWith($route, 'kader.data.remaja'),
        ],
        [
            'label' => 'Lansia',
            'route' => route('kader.data.lansia.index'),
            'active' => Str::startsWith($route, 'kader.data.lansia'),
        ],
    ];

    $menusManajemen = [
        [
            'label' => 'Agenda Posyandu',
            'icon' => 'fa-calendar-check',
            'route' => route('kader.jadwal.index'),
            'active' => Str::startsWith($route, 'kader.jadwal'),
        ],
        [
            'label' => 'Laporan Admin',
            'icon' => 'fa-file-lines',
            'route' => route('kader.laporan.index'),
            'active' => Str::startsWith($route, 'kader.laporan'),
        ],
    ];
@endphp

<style>
    .pc-kader-sidebar {
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

    .pc-kader-top {
        position: relative;
        z-index: 4;
        flex-shrink: 0;

        padding: 24px 18px 0;
        background: transparent;
    }

    .pc-kader-logo-wrap {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .pc-kader-logo-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .pc-kader-logo {
        width: 154px;
        height: auto;
        object-fit: contain;
        display: block;
        filter:
            drop-shadow(0 12px 22px rgba(15,23,42,.08))
            drop-shadow(0 2px 4px rgba(16,185,129,.08));
    }

    .pc-kader-user-card {
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

    .pc-kader-avatar {
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

    .pc-kader-user-info {
        flex: 1;
        min-width: 0;
    }

    .pc-kader-user-info h4 {
        margin: 0;

        color: #064e3b;
        font-size: 13.5px;
        font-weight: 900;
        line-height: 1.2;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pc-kader-user-info p {
        margin: 3px 0 6px;

        color: #64748b;
        font-size: 11px;
        font-weight: 750;
    }

    .pc-kader-online {
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

    .pc-kader-online span {
        width: 6px;
        height: 6px;

        border-radius: 999px;
        background: #10b981;

        box-shadow: 0 0 0 3px rgba(16,185,129,.12);
    }

    /*
    |--------------------------------------------------------------------------
    | AREA MENU YANG SCROLL
    |--------------------------------------------------------------------------
    */

    .pc-kader-scroll {
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

    .pc-kader-scroll::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }

    .pc-kader-menu-group {
        margin-bottom: 20px;
    }

    .pc-kader-menu-group:last-child {
        margin-bottom: 0;
    }

    .pc-kader-menu-title {
        margin: 0 0 10px;
        padding-left: 4px;

        color: #64748b;
        font-size: 10.5px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .12em;
    }

    .pc-kader-menu-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .pc-kader-menu-item {
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

    .pc-kader-menu-item:hover {
        background: rgba(236,253,245,.92);
        color: #047857;
        transform: translateX(3px);
    }

    .pc-kader-menu-item.active {
        background:
            linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.82));

        color: #047857;
        font-weight: 900;

        box-shadow:
            0 10px 24px rgba(16,185,129,.08),
            inset 0 1px 0 rgba(255,255,255,.92);
    }

    .pc-kader-menu-item.active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 9px;
        bottom: 9px;

        width: 4px;

        border-radius: 999px;
        background: linear-gradient(180deg, #10b981, #059669);
    }

    .pc-kader-menu-icon {
        width: 22px;
        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        color: #64748b;
        font-size: 13px;

        transition: color .28s ease;
    }

    .pc-kader-menu-item:hover .pc-kader-menu-icon,
    .pc-kader-menu-item.active .pc-kader-menu-icon {
        color: #059669;
    }

    .pc-kader-menu-text {
        flex: 1;
        min-width: 0;

        text-align: left;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pc-kader-menu-caret {
        margin-left: auto;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        color: #64748b;
        font-size: 11px;

        transition:
            transform .28s cubic-bezier(.16, 1, .3, 1),
            color .28s cubic-bezier(.16, 1, .3, 1);
    }

    .pc-kader-menu-item:hover .pc-kader-menu-caret,
    .pc-kader-menu-item.active .pc-kader-menu-caret {
        color: #059669;
    }

    /*
    |--------------------------------------------------------------------------
    | DROPDOWN DATA PASIEN
    |--------------------------------------------------------------------------
    */

    .pc-kader-submenu-wrap {
        overflow: hidden;
    }

    .pc-kader-submenu {
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 7px !important;

        width: 100% !important;

        margin: 8px 0 2px 0 !important;
        padding: 8px 0 4px 36px !important;
    }

    .pc-kader-submenu::before,
    .pc-kader-submenu::after {
        content: none !important;
        display: none !important;
    }

    .pc-kader-submenu-item {
        position: relative;

        width: 100% !important;
        min-height: 36px;

        display: flex !important;
        align-items: center !important;
        gap: 10px !important;

        padding: 9px 12px !important;

        border-radius: 14px;

        color: #64748b;
        background: transparent;

        text-decoration: none;
        font-size: 12.5px;
        font-weight: 800;
        line-height: 1.2;

        transition:
            background .28s cubic-bezier(.16, 1, .3, 1),
            color .28s cubic-bezier(.16, 1, .3, 1),
            transform .28s cubic-bezier(.16, 1, .3, 1),
            box-shadow .28s cubic-bezier(.16, 1, .3, 1);
    }

    .pc-kader-submenu-item:hover {
        color: #047857;
        background: rgba(236,253,245,.88);
        transform: translateX(3px);
    }

    .pc-kader-submenu-item.active {
        color: #047857;
        background:
            linear-gradient(90deg, rgba(236,253,245,.96), rgba(255,255,255,.80));

        font-weight: 900;

        box-shadow:
            0 10px 24px rgba(16,185,129,.07),
            inset 0 1px 0 rgba(255,255,255,.90);
    }

    .pc-kader-submenu-dot {
        width: 7px;
        height: 7px;
        flex-shrink: 0;

        border-radius: 999px;
        background: #cbd5e1;

        transition:
            background .28s cubic-bezier(.16, 1, .3, 1),
            box-shadow .28s cubic-bezier(.16, 1, .3, 1),
            transform .28s cubic-bezier(.16, 1, .3, 1);
    }

    .pc-kader-submenu-item:hover .pc-kader-submenu-dot,
    .pc-kader-submenu-item.active .pc-kader-submenu-dot {
        background: #10b981;
        box-shadow: 0 0 0 4px rgba(16,185,129,.12);
        transform: scale(1.08);
    }

    .pc-kader-submenu-text {
        display: block !important;
        flex: 1;
        min-width: 0;

        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    .pc-kader-logout-form {
        margin: 0;
        padding: 0;
    }

    .pc-kader-logout {
        color: #ef4444;
    }

    .pc-kader-logout .pc-kader-menu-icon {
        color: #ef4444;
    }

    .pc-kader-logout:hover {
        background: #fff1f2;
        color: #dc2626;
    }

    .pc-kader-logout:hover .pc-kader-menu-icon {
        color: #dc2626;
    }

    /*
    |--------------------------------------------------------------------------
    | DEKORASI BAWAH IKUT SCROLL
    | Posisi tetap setelah tombol Keluar.
    |--------------------------------------------------------------------------
    */

    .pc-kader-bottom-decor {
        position: relative;

        z-index: 1;

        height: 96px;
        min-height: 96px;

        margin: 8px -18px 0;

        overflow: hidden;
        pointer-events: none;
    }

    .pc-kader-wave {
        position: absolute;
        left: -20%;
        width: 140%;

        border-radius: 50% 50% 0 0;
    }

    .pc-kader-wave-1 {
        bottom: -54px;
        height: 96px;
        background: rgba(16,185,129,.14);
    }

    .pc-kader-wave-2 {
        bottom: -67px;
        height: 106px;
        background: rgba(5,150,105,.13);
    }

    .pc-kader-wave-3 {
        bottom: -78px;
        height: 116px;
        background: rgba(20,184,166,.10);
    }

    .pc-kader-plant {
        position: absolute;
        right: 22px;
        bottom: 11px;

        width: 64px;
        height: 64px;
    }

    .pc-kader-stem {
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

    .pc-kader-leaf {
        position: absolute;

        width: 31px;
        height: 16px;

        border-radius: 100% 0 100% 0;
        background:
            linear-gradient(135deg, rgba(4,120,87,.66), rgba(16,185,129,.24));

        transform-origin: bottom left;
    }

    .pc-kader-leaf-1 {
        right: 19px;
        bottom: 23px;
        transform: rotate(-34deg);
    }

    .pc-kader-leaf-2 {
        right: 32px;
        bottom: 35px;
        transform: rotate(-8deg) scale(.9);
    }

    .pc-kader-leaf-3 {
        right: 7px;
        bottom: 36px;
        transform: rotate(28deg) scale(.86);
    }

    .pc-kader-leaf-4 {
        right: 25px;
        bottom: 11px;
        transform: rotate(46deg) scale(.72);
    }

    /*
    |--------------------------------------------------------------------------
    | ANIMATION
    |--------------------------------------------------------------------------
    */

    .pc-kader-top,
    .pc-kader-menu-group,
    .pc-kader-bottom-decor {
        opacity: 0;
        transform: translateY(16px);
        animation: pcKaderSidebarIn .85s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .pc-kader-top {
        animation-delay: .06s;
    }

    .pc-kader-menu-group:nth-child(1) {
        animation-delay: .14s;
    }

    .pc-kader-menu-group:nth-child(2) {
        animation-delay: .22s;
    }

    .pc-kader-menu-group:nth-child(3) {
        animation-delay: .30s;
    }

    .pc-kader-menu-group:nth-child(4) {
        animation-delay: .38s;
    }

    .pc-kader-bottom-decor {
        animation-delay: .46s;
    }

    @keyframes pcKaderSidebarIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 1023px) {
        .pc-kader-sidebar {
            height: calc(100dvh - 20px);
            min-height: calc(100dvh - 20px);
            border-radius: 24px;
        }

        .pc-kader-top {
            padding: 22px 16px 0;
        }

        .pc-kader-scroll {
            padding: 0 16px 0;
        }

        .pc-kader-logo {
            width: 142px;
        }

        .pc-kader-bottom-decor {
            margin-left: -16px;
            margin-right: -16px;
        }
    }
</style>

<div class="pc-kader-sidebar">

    {{-- TOP AREA TIDAK IKUT SCROLL --}}
    <div class="pc-kader-top">

        {{-- LOGO --}}
        <div class="pc-kader-logo-wrap">
            <a href="{{ route('kader.dashboard') }}" class="js-nav-link spa-route pc-kader-logo-link">
                <img
                    src="{{ asset('img/logo.png') }}"
                    alt="Logo PosyanduCare"
                    class="pc-kader-logo"
                >
            </a>
        </div>

        {{-- USER CARD --}}
        <div class="pc-kader-user-card">
            <div class="pc-kader-avatar">
                {{ $initial }}
            </div>

            <div class="pc-kader-user-info">
                <h4>{{ $kaderName }}</h4>
                <p>Petugas Kader</p>

                <div class="pc-kader-online">
                    <span></span>
                    Online
                </div>
            </div>
        </div>
    </div>

    {{-- MENU SAJA YANG SCROLL --}}
    <div class="pc-kader-scroll" id="kaderSidebarScrollArea">

        {{-- MENU UTAMA --}}
        <div class="pc-kader-menu-group">
            <p class="pc-kader-menu-title">
                Menu Utama
            </p>

            <div class="pc-kader-menu-list">
                @foreach($menusUtama as $menu)
                    <a
                        href="{{ $menu['route'] }}"
                        class="js-nav-link spa-route pc-kader-menu-item {{ $menu['active'] ? 'active' : '' }}"
                    >
                        <span class="pc-kader-menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>

                        <span class="pc-kader-menu-text">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- DATA PASIEN --}}
        <div class="pc-kader-menu-group" x-data="{ openData: {{ $isDataWarga ? 'true' : 'false' }} }">
            <p class="pc-kader-menu-title">
                Database Warga
            </p>

            <div class="pc-kader-menu-list">
                <button
                    type="button"
                    @click="openData = !openData"
                    class="pc-kader-menu-item {{ $isDataWarga ? 'active' : '' }}"
                >
                    <span class="pc-kader-menu-icon">
                        <i class="fa-solid fa-address-book"></i>
                    </span>

                    <span class="pc-kader-menu-text">
                        Data Pasien
                    </span>

                    <span class="pc-kader-menu-caret" :class="openData ? 'rotate-180' : ''">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </button>

                <div
                    x-show="openData"
                    x-collapse
                    class="pc-kader-submenu-wrap"
                >
                    <div class="pc-kader-submenu">
                        @foreach($dataPasienMenus as $submenu)
                            <a
                                href="{{ $submenu['route'] }}"
                                class="js-nav-link spa-route pc-kader-submenu-item {{ $submenu['active'] ? 'active' : '' }}"
                            >
                                <span class="pc-kader-submenu-dot"></span>

                                <span class="pc-kader-submenu-text">
                                    {{ $submenu['label'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- MANAJEMEN --}}
        <div class="pc-kader-menu-group">
            <p class="pc-kader-menu-title">
                Manajemen
            </p>

            <div class="pc-kader-menu-list">
                @foreach($menusManajemen as $menu)
                    <a
                        href="{{ $menu['route'] }}"
                        class="js-nav-link spa-route pc-kader-menu-item {{ $menu['active'] ? 'active' : '' }}"
                    >
                        <span class="pc-kader-menu-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>

                        <span class="pc-kader-menu-text">
                            {{ $menu['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- SESI AKUN --}}
        <div class="pc-kader-menu-group">
            <p class="pc-kader-menu-title">
                Sesi Akun
            </p>

            <form method="POST" action="{{ route('logout') }}" class="pc-kader-logout-form">
                @csrf

                <button type="submit" class="pc-kader-menu-item pc-kader-logout">
                    <span class="pc-kader-menu-icon">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </span>

                    <span class="pc-kader-menu-text">
                        Keluar
                    </span>
                </button>
            </form>
        </div>

        {{-- DEKORASI IKUT SCROLL DAN POSISINYA SELALU DI BAWAH KELUAR --}}
        <div class="pc-kader-bottom-decor" aria-hidden="true">
            <div class="pc-kader-wave pc-kader-wave-1"></div>
            <div class="pc-kader-wave pc-kader-wave-2"></div>
            <div class="pc-kader-wave pc-kader-wave-3"></div>

            <div class="pc-kader-plant">
                <span class="pc-kader-leaf pc-kader-leaf-1"></span>
                <span class="pc-kader-leaf pc-kader-leaf-2"></span>
                <span class="pc-kader-leaf pc-kader-leaf-3"></span>
                <span class="pc-kader-leaf pc-kader-leaf-4"></span>
                <span class="pc-kader-stem"></span>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scrollArea = document.getElementById('kaderSidebarScrollArea');

            if (!scrollArea) {
                return;
            }

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