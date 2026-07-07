@php
    use Illuminate\Support\Str;

    $route = request()->route()?->getName() ?? '';

    // Ambil nama user
    $rawName = Auth::user()->name ?? 'Suci Wulandari';
    
    // Logika Pemotongan Nama Otomatis
    $nameParts = explode(' ', trim($rawName));
    if (count($nameParts) > 1) {
        $bidanName = $nameParts[0] . ' ' . strtoupper(substr($nameParts[1], 0, 1)) . '.';
    } else {
        $bidanName = $rawName;
    }

    $initial = strtoupper(substr($bidanName, 0, 1));

    // Menghitung jumlah pemeriksaan pending
    try {
        $pendingCount = class_exists('\App\Models\Pemeriksaan')
            ? \App\Models\Pemeriksaan::where('status_verifikasi', 'pending')->count()
            : 0;
    } catch (\Throwable $e) {
        $pendingCount = 0;
    }

    // Konfigurasi Menu
    $menusUtama = [
        ['label' => 'Dashboard', 'icon' => 'fa-house', 'route' => route('bidan.dashboard'), 'active' => $route === 'bidan.dashboard'],
    ];

    $menusLayanan = [
        ['label' => 'Pemeriksaan Klinis', 'icon' => 'fa-stethoscope', 'route' => route('bidan.pemeriksaan.index'), 'active' => Str::startsWith($route, 'bidan.pemeriksaan'), 'badge' => $pendingCount],
        ['label' => 'Vaksinasi & Imunisasi', 'icon' => 'fa-syringe', 'route' => route('bidan.imunisasi.index'), 'active' => Str::startsWith($route, 'bidan.imunisasi'), 'badge' => 0],
    ];

    $menusDatabase = [
        ['label' => 'Rekam Medis', 'icon' => 'fa-folder-open', 'route' => route('bidan.rekam-medis.index'), 'active' => Str::startsWith($route, 'bidan.rekam-medis'), 'badge' => 0],
    ];

    $menusAdministrasi = [
        ['label' => 'Kelola Jadwal', 'icon' => 'fa-calendar-check', 'route' => route('bidan.jadwal.index'), 'active' => Str::startsWith($route, 'bidan.jadwal'), 'badge' => 0],
    ];
@endphp

<style>
    /* SIDEBAR BIDAN CLEAN MOBILE */
    .pc-bidan-sidebar {
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
    .pc-bidan-sidebar::before {
        content: ""; position: absolute; width: 260px; height: 260px; top: -120px; left: -110px;
        border-radius: 999px; background: rgba(16,185,129,.12); filter: blur(70px); pointer-events: none;
    }
    .pc-bidan-sidebar::after {
        content: ""; position: absolute; width: 250px; height: 250px; right: -120px; bottom: -110px;
        border-radius: 999px; background: rgba(20,184,166,.12); filter: blur(70px); pointer-events: none;
    }

    /* TOP & LOGO */
    .pc-bidan-top { position: relative; z-index: 4; flex-shrink: 0; padding: 24px 18px 0; }
    .pc-bidan-logo-wrap { display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
    .pc-bidan-logo-link { display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
    .pc-bidan-logo {
        width: 148px; height: auto; object-fit: contain; display: block;
        filter: drop-shadow(0 12px 22px rgba(15,23,42,.08)) drop-shadow(0 2px 4px rgba(16,185,129,.08));
    }

    /* USER CARD */
    .pc-bidan-card {
        display: flex; align-items: center; gap: 14px; padding: 12px 14px; margin-bottom: 24px;
        border-radius: 20px; background: rgba(255,255,255,.6); border: 1px solid rgba(255,255,255,.8);
        box-shadow: 0 4px 20px rgba(0,0,0,.03); backdrop-filter: blur(10px);
    }
    .pc-bidan-avatar {
        position: relative; width: 44px; height: 44px; flex-shrink: 0; border-radius: 14px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: flex;
        align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 16px;
        box-shadow: 0 8px 16px rgba(16,185,129,.25);
    }
    .pc-bidan-active-dot {
        position: absolute; bottom: -2px; right: -2px; width: 12px; height: 12px;
        background-color: #10b981; border: 2px solid #ffffff; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .pc-bidan-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
    .pc-bidan-info h4 { margin: 0; color: #1e293b; font-size: 13.5px; font-weight: 900; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pc-bidan-info p { margin: 3px 0 0 0; color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }

    /* SCROLL AREA */
    .pc-bidan-scroll {
        position: relative; z-index: 3; flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden;
        padding: 0 18px 120px; scrollbar-width: none; -ms-overflow-style: none;
        overscroll-behavior: contain; overscroll-behavior-y: contain; touch-action: pan-y; -webkit-overflow-scrolling: touch;
    }
    .pc-bidan-scroll::-webkit-scrollbar { width: 0; height: 0; display: none; }

    /* MENU */
    .pc-bidan-menu-group { margin-bottom: 20px; }
    .pc-bidan-menu-group:last-child { margin-bottom: 0; }
    .pc-bidan-menu-title { margin: 0 0 10px; padding-left: 4px; color: #94a3b8; font-size: 10.5px; font-weight: 900; text-transform: uppercase; letter-spacing: .12em; }
    .pc-bidan-menu-list { display: flex; flex-direction: column; gap: 6px; }
    .pc-bidan-menu-item {
        position: relative; width: 100%; min-height: 42px; display: flex; align-items: center; gap: 12px;
        padding: 10px 13px; border: 0; border-radius: 15px; background: transparent; color: #475569;
        text-decoration: none; font-size: 13px; font-weight: 800; cursor: pointer; text-align: left;
        transition: background .28s cubic-bezier(.16, 1, .3, 1), color .28s cubic-bezier(.16, 1, .3, 1), transform .28s cubic-bezier(.16, 1, .3, 1), box-shadow .28s cubic-bezier(.16, 1, .3, 1);
    }
    .pc-bidan-menu-item:hover { background: rgba(236,253,245,.92); color: #047857; transform: translateX(3px); }
    .pc-bidan-menu-item.active {
        background: linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.84));
        color: #047857; font-weight: 900; box-shadow: 0 10px 24px rgba(16,185,129,.08), inset 0 1px 0 rgba(255,255,255,.92);
    }
    .pc-bidan-menu-item.active::before {
        content: ""; position: absolute; left: 0; top: 9px; bottom: 9px; width: 4px;
        border-radius: 999px; background: linear-gradient(180deg, #10b981, #059669);
    }
    .pc-bidan-menu-icon {
        width: 22px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        color: #94a3b8; font-size: 14px; transition: color .28s ease, transform .28s ease;
    }
    .pc-bidan-menu-item:hover .pc-bidan-menu-icon, .pc-bidan-menu-item.active .pc-bidan-menu-icon { color: #059669; transform: scale(1.08); }
    .pc-bidan-menu-text { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    
    .pc-bidan-menu-badge {
        min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background: #fee2e2;
        color: #ef4444; display: inline-flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; margin-left: auto;
    }

    /* Tombol Logout */
    .pc-bidan-logout { color: #ef4444; }
    .pc-bidan-logout .pc-bidan-menu-icon { color: #f87171; }
    .pc-bidan-logout:hover { background: #fef2f2; color: #dc2626; }
    .pc-bidan-logout:hover .pc-bidan-menu-icon { color: #dc2626; transform: scale(1.08); }

    /* BOTTOM DECOR */
    .pc-bidan-bottom-decor {
        position: absolute; bottom: 0; left: 0; width: 100%; height: 110px; z-index: 1;
        overflow: hidden; pointer-events: none; border-radius: 0 0 28px 28px;
    }
    .pc-bidan-wave { position: absolute; left: -20%; width: 140%; border-radius: 50% 50% 0 0; }
    .pc-bidan-wave-1 { bottom: -30px; height: 96px; background: rgba(16,185,129,.14); }
    .pc-bidan-wave-2 { bottom: -45px; height: 106px; background: rgba(5,150,105,.13); }
    .pc-bidan-wave-3 { bottom: -55px; height: 116px; background: rgba(20,184,166,.10); }
    .pc-bidan-plant { position: absolute; right: 22px; bottom: 12px; width: 64px; height: 64px; }
    .pc-bidan-stem {
        position: absolute; left: 31px; bottom: 0; width: 3px; height: 46px; border-radius: 999px;
        background: rgba(4,120,87,.35); transform: rotate(18deg); transform-origin: bottom;
    }
    .pc-bidan-leaf {
        position: absolute; width: 31px; height: 16px; border-radius: 100% 0 100% 0;
        background: linear-gradient(135deg, rgba(4,120,87,.66), rgba(16,185,129,.24)); transform-origin: bottom left;
    }
    .pc-bidan-leaf-1 { right: 19px; bottom: 23px; transform: rotate(-34deg); }
    .pc-bidan-leaf-2 { right: 32px; bottom: 35px; transform: rotate(-8deg) scale(.9); }
    .pc-bidan-leaf-3 { right: 7px; bottom: 36px; transform: rotate(28deg) scale(.86); }
    .pc-bidan-leaf-4 { right: 25px; bottom: 11px; transform: rotate(46deg) scale(.72); }

    /* ANIMATION */
    .pc-bidan-top, .pc-bidan-menu-group { opacity: 0; transform: translateY(16px); animation: pcBidanSidebarIn .85s cubic-bezier(.22, 1, .36, 1) forwards; }
    .pc-bidan-bottom-decor { opacity: 0; animation: pcBidanDecorIn .85s ease forwards; }
    .pc-bidan-top { animation-delay: .06s; }
    .pc-bidan-menu-group:nth-child(1) { animation-delay: .14s; }
    .pc-bidan-menu-group:nth-child(2) { animation-delay: .22s; }
    .pc-bidan-bottom-decor { animation-delay: .30s; }

    @keyframes pcBidanSidebarIn { to { opacity: 1; transform: translateY(0); } }
    @keyframes pcBidanDecorIn { to { opacity: 1; } }
    @media (max-width: 420px) {
        .pc-bidan-sidebar { border-radius: 24px; }
        .pc-bidan-top { padding: 22px 16px 0; }
        .pc-bidan-scroll { padding: 0 16px 120px; }
        .pc-bidan-logo { width: 138px; }
    }
</style>

<div class="pc-bidan-sidebar">

    <div class="pc-bidan-top">
        <div class="pc-bidan-logo-wrap">
            <a href="{{ route('bidan.dashboard') }}" class="pc-bidan-logo-link">
                <img src="{{ asset('img/logo.webp') }}" alt="Logo PosyanduCare" class="pc-bidan-logo" onerror="this.src='{{ asset('img/logo.png') }}'">
            </a>
        </div>

        <div class="pc-bidan-card">
            <div class="pc-bidan-avatar">
                {{ $initial }}
                <span class="pc-bidan-active-dot" title="Portal Aktif"></span>
            </div>
            <div class="pc-bidan-info">
                <h4 title="{{ $rawName }}">{{ $bidanName }}</h4>
                <p>Tenaga Bidan</p>
            </div>
        </div>
    </div>

    <div class="pc-bidan-scroll" id="bidanSidebarScrollArea">

        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">Menu Utama</p>
            <div class="pc-bidan-menu-list">
                @foreach($menusUtama as $menu)
                    <a href="{{ $menu['route'] }}" class="pc-bidan-menu-item {{ $menu['active'] ? 'active' : '' }}">
                        <span class="pc-bidan-menu-icon"><i class="fa-solid {{ $menu['icon'] }}"></i></span>
                        <span class="pc-bidan-menu-text">{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">Layanan Medis</p>
            <div class="pc-bidan-menu-list">
                @foreach($menusLayanan as $menu)
                    <a href="{{ $menu['route'] }}" class="pc-bidan-menu-item {{ $menu['active'] ? 'active' : '' }}">
                        <span class="pc-bidan-menu-icon"><i class="fa-solid {{ $menu['icon'] }}"></i></span>
                        <span class="pc-bidan-menu-text">{{ $menu['label'] }}</span>
                        @if(!empty($menu['badge']) && $menu['badge'] > 0)
                            <span class="pc-bidan-menu-badge">{{ $menu['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">Arsip & Database</p>
            <div class="pc-bidan-menu-list">
                @foreach($menusDatabase as $menu)
                    <a href="{{ $menu['route'] }}" class="pc-bidan-menu-item {{ $menu['active'] ? 'active' : '' }}">
                        <span class="pc-bidan-menu-icon"><i class="fa-solid {{ $menu['icon'] }}"></i></span>
                        <span class="pc-bidan-menu-text">{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">Administrasi</p>
            <div class="pc-bidan-menu-list">
                @foreach($menusAdministrasi as $menu)
                    <a href="{{ $menu['route'] }}" class="pc-bidan-menu-item {{ $menu['active'] ? 'active' : '' }}">
                        <span class="pc-bidan-menu-icon"><i class="fa-solid {{ $menu['icon'] }}"></i></span>
                        <span class="pc-bidan-menu-text">{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="pc-bidan-menu-group">
            <p class="pc-bidan-menu-title">Sesi Akun</p>
            <div class="pc-bidan-menu-list">
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="pc-bidan-menu-item pc-bidan-logout">
                        <span class="pc-bidan-menu-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span class="pc-bidan-menu-text">Keluar Aplikasi</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

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

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollArea = document.getElementById('bidanSidebarScrollArea');
        if (!scrollArea) return;

        scrollArea.addEventListener('wheel', function (e) {
            const atTop = scrollArea.scrollTop <= 0;
            const atBottom = Math.ceil(scrollArea.scrollTop + scrollArea.clientHeight) >= scrollArea.scrollHeight;
            if ((e.deltaY < 0 && atTop) || (e.deltaY > 0 && atBottom)) e.preventDefault();
            e.stopPropagation();
        }, { passive: false });

        let touchStartY = 0;
        scrollArea.addEventListener('touchstart', e => {
            if (e.touches.length > 0) touchStartY = e.touches[0].clientY;
        }, { passive: true });

        scrollArea.addEventListener('touchmove', function (e) {
            if (!e.touches.length) return;
            const delta = touchStartY - e.touches[0].clientY;
            const atTop = scrollArea.scrollTop <= 0;
            const atBottom = Math.ceil(scrollArea.scrollTop + scrollArea.clientHeight) >= scrollArea.scrollHeight;
            if ((delta < 0 && atTop) || (delta > 0 && atBottom)) e.preventDefault();
            e.stopPropagation();
        }, { passive: false });
    });
</script>
@endonce