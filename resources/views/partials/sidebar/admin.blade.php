@php
    /*
    |--------------------------------------------------------------------------
    | Sidebar Admin PosyanduCare - Clean Minimal Menu
    |--------------------------------------------------------------------------
    | Menu aktif:
    | 1. Dashboard
    | 2. Kelola User
    | 3. Kelola Bidan
    | 4. Kelola Kader
    */

    $adminName = Auth::user()->name ?? 'Admin Posyandu';
    $initial = strtoupper(substr($adminName, 0, 1));

    $menus = [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-house',
            'route' => route('admin.dashboard'),
            'active' => request()->routeIs('admin.dashboard*'),
        ],
        [
            'label' => 'Kelola User',
            'icon' => 'fa-users',
            'route' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
        ],
        [
            'label' => 'Kelola Bidan',
            'icon' => 'fa-user-doctor',
            'route' => route('admin.bidans.index'),
            'active' => request()->routeIs('admin.bidans.*'),
        ],
        [
            'label' => 'Kelola Kader',
            'icon' => 'fa-user-nurse',
            'route' => route('admin.kaders.index'),
            'active' => request()->routeIs('admin.kaders.*'),
        ],
    ];
@endphp

<div class="pc-light-sidebar">

    {{-- LOGO --}}
    <div class="pc-sidebar-logo-area">
        <a href="{{ route('admin.dashboard') }}" class="js-nav-link pc-logo-link">
            <img
                src="{{ asset('img/logo.png') }}"
                alt="Logo PosyanduCare"
                class="pc-sidebar-logo"
            >
        </a>
    </div>

    {{-- USER CARD --}}
    <div class="pc-user-card">
        <div class="pc-user-avatar">
            {{ $initial }}
        </div>

        <div class="pc-user-info">
            <h4>{{ $adminName }}</h4>
            <p>Administrator</p>

            <div class="pc-online">
                <span></span>
                Online
            </div>
        </div>
    </div>

    {{-- MENU --}}
    <div class="pc-menu-group">
        <p class="pc-menu-title">
            Menu Admin
        </p>

        <div class="pc-menu-list">
            @foreach($menus as $menu)
                <a
                    href="{{ $menu['route'] }}"
                    class="js-nav-link pc-menu-item {{ $menu['active'] ? 'active' : '' }}"
                >
                    <span class="pc-menu-icon">
                        <i class="fa-solid {{ $menu['icon'] }}"></i>
                    </span>

                    <span class="pc-menu-text">
                        {{ $menu['label'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- DEKORASI BAWAH --}}
    <div class="pc-sidebar-decoration" aria-hidden="true">
        <div class="pc-wave pc-wave-1"></div>
        <div class="pc-wave pc-wave-2"></div>
        <div class="pc-wave pc-wave-3"></div>

        <div class="pc-plant">
            <span class="pc-leaf pc-leaf-1"></span>
            <span class="pc-leaf pc-leaf-2"></span>
            <span class="pc-leaf pc-leaf-3"></span>
            <span class="pc-leaf pc-leaf-4"></span>
            <span class="pc-stem"></span>
        </div>
    </div>

</div>