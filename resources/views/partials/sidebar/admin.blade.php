@php
    $route = request()->route()?->getName() ?? '';
    $adminName = Auth::user()->name ?? 'Administrator';
    $initial = strtoupper(substr($adminName, 0, 1));

    $menusUtama = [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-house',
            'route' => route('admin.dashboard'),
            'active' => request()->routeIs('admin.dashboard') || request()->routeIs('admin.'),
            'badge' => 0,
        ],
    ];

    $menusAkun = [
        [
            'label' => 'Kelola Warga',
            'icon' => 'fa-users',
            'route' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
            'badge' => 0,
        ],
        [
            'label' => 'Kelola Bidan',
            'icon' => 'fa-user-doctor',
            'route' => route('admin.bidans.index'),
            'active' => request()->routeIs('admin.bidans.*'),
            'badge' => 0,
        ],
        [
            'label' => 'Kelola Kader',
            'icon' => 'fa-user-nurse',
            'route' => route('admin.kaders.index'),
            'active' => request()->routeIs('admin.kaders.*'),
            'badge' => 0,
        ],
    ];
@endphp

<style>
    .pc-sidebar {
        position: relative;
        width: 100%;
        height: calc(100dvh - 28px);
        padding: 24px 18px 18px;
        border-radius: 28px;
        overflow-x: hidden;
        overflow-y: auto;
        background:
            radial-gradient(circle at 50% 0%, rgba(236, 253, 245, .70), transparent 34%),
            linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 255, 252, .94));
        border: 1px solid rgba(226, 232, 240, .75);
        box-shadow: 0 18px 52px rgba(15, 23, 42, .07);
        scrollbar-width: thin;
    }

    .pc-sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .pc-sidebar::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, .22);
        border-radius: 999px;
    }

    .pc-logo-area {
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

    .pc-logo {
        width: 154px;
        max-width: 78%;
        max-height: 88px;
        object-fit: contain;
        display: block;
        filter: drop-shadow(0 8px 16px rgba(15, 23, 42, .07));
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
        background: linear-gradient(135deg, rgba(255, 255, 255, .88), rgba(248, 255, 252, .78));
        border: 1px solid rgba(209, 250, 229, .92);
        box-shadow: 0 12px 28px rgba(15, 23, 42, .048), inset 0 1px 0 rgba(255, 255, 255, .95);
    }

    .pc-avatar {
        width: 52px;
        height: 52px;
        flex-shrink: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, #10b981 0%, #34d399 45%, #f59e0b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 900;
        font-size: 18px;
        box-shadow: 0 10px 20px rgba(16, 185, 129, .16), inset 0 1px 0 rgba(255, 255, 255, .22);
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
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .11);
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
        font-weight: 800;
        cursor: pointer;
        transition: background .14s ease, color .14s ease, transform .14s ease;
    }

    .pc-menu-item:hover {
        background: rgba(236, 253, 245, .90);
        color: #047857;
        transform: translateX(3px);
    }

    .pc-menu-item.active {
        background: linear-gradient(90deg, rgba(236, 253, 245, .98), rgba(255, 255, 255, .80));
        color: #047857;
        font-weight: 900;
        box-shadow: 0 8px 20px rgba(16, 185, 129, .07), inset 0 1px 0 rgba(255, 255, 255, .90);
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
        transition: color .14s ease;
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

    .pc-sidebar-deco {
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
        background: rgba(16, 185, 129, .13);
    }

    .pc-wave-2 {
        bottom: -76px;
        height: 126px;
        background: rgba(5, 150, 105, .11);
    }

    .pc-wave-3 {
        bottom: -92px;
        height: 132px;
        background: rgba(20, 184, 166, .09);
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
        background: rgba(4, 120, 87, .32);
        transform: rotate(18deg);
        transform-origin: bottom;
    }

    .pc-leaf {
        position: absolute;
        width: 38px;
        height: 20px;
        border-radius: 100% 0 100% 0;
        background: linear-gradient(135deg, rgba(4, 120, 87, .62), rgba(16, 185, 129, .20));
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

    @media (prefers-reduced-motion: reduce) {
        .pc-menu-item {
            transition: none;
        }
    }
</style>

<div class="pc-sidebar">
    {{-- LOGO --}}
    <div class="pc-logo-area">
        <a href="{{ route('admin.dashboard') }}" class="pc-logo-link">
            <img src="{{ asset('img/logo.webp') }}"
                 alt="Logo PosyanduCare"
                 class="pc-logo"
                 onerror="this.src='{{ asset('public/img/logo.webp') }}'">
        </a>
    </div>

    {{-- USER CARD --}}
    <div class="pc-user-card">
        <div class="pc-avatar">
            {{ $initial }}
        </div>

        <div class="pc-user-info">
            <h4>{{ $adminName }}</h4>
            <p>Administrator</p>

            <div class="pc-online">
                <span></span>
                Akses Admin Aktif
            </div>
        </div>
    </div>

    {{-- SCROLL MENU --}}
    <div class="pc-menu-group">
        <div class="pc-menu-title">Menu Utama</div>

        <div class="pc-menu-list">
            @foreach($menusUtama as $menu)
                <a href="{{ $menu['route'] }}"
                   class="pc-menu-item {{ $menu['active'] ? 'active' : '' }}">
                    <span class="pc-menu-icon">
                        <i class="fa-solid {{ $menu['icon'] }}"></i>
                    </span>

                    <span class="pc-menu-text">
                        {{ $menu['label'] }}
                    </span>

                    @if(!empty($menu['badge']) && $menu['badge'] > 0)
                        <span class="pc-menu-badge">{{ $menu['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <div class="pc-menu-group">
        <div class="pc-menu-title">Manajemen Akun</div>

        <div class="pc-menu-list">
            @foreach($menusAkun as $menu)
                <a href="{{ $menu['route'] }}"
                   class="pc-menu-item {{ $menu['active'] ? 'active' : '' }}">
                    <span class="pc-menu-icon">
                        <i class="fa-solid {{ $menu['icon'] }}"></i>
                    </span>

                    <span class="pc-menu-text">
                        {{ $menu['label'] }}
                    </span>

                    @if(!empty($menu['badge']) && $menu['badge'] > 0)
                        <span class="pc-menu-badge">{{ $menu['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <div class="pc-menu-group">
        <div class="pc-menu-title">Sesi Akun</div>

        <div class="pc-menu-list">
            <form method="POST" action="{{ route('logout') }}" class="pc-logout-form js-logout-form">
                @csrf

                <button type="submit" class="pc-menu-item pc-logout">
                    <span class="pc-menu-icon">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </span>

                    <span class="pc-menu-text">
                        Keluar
                    </span>
                </button>
            </form>
        </div>
    </div>

    {{-- DEKORASI --}}
    <div class="pc-sidebar-deco">
        <div class="pc-wave pc-wave-1"></div>
        <div class="pc-wave pc-wave-2"></div>
        <div class="pc-wave pc-wave-3"></div>

        <div class="pc-plant">
            <span class="pc-stem"></span>
            <span class="pc-leaf pc-leaf-1"></span>
            <span class="pc-leaf pc-leaf-2"></span>
            <span class="pc-leaf pc-leaf-3"></span>
            <span class="pc-leaf pc-leaf-4"></span>
        </div>
    </div>
</div>