@php
    use Illuminate\Support\Str;

    $route = request()->route()?->getName() ?? '';

    // Ambil nama user
    $rawName = Auth::user()->name ?? 'Suci Wulandari';
    
    // Logika Pemotongan Nama Otomatis
    $nameParts = explode(' ', trim($rawName));
    if (count($nameParts) > 1) {
        // Ambil kata pertama + inisial kata kedua (Misal: "Suci W.")
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
    /* =========================================
       1. CONTAINER SIDEBAR UTAMA
       ========================================= */
    .pc-bidan-sidebar {
        position: relative;
        width: 100%;
        height: calc(100dvh - 28px);
        min-height: calc(100dvh - 28px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .pc-bidan-top {
        position: relative;
        z-index: 10;
        flex-shrink: 0;
        padding: 24px 20px 12px;
        background: transparent;
    }

    /* =========================================
       2. LOGO AREA
       ========================================= */
    .pc-bidan-logo-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
    }

    .pc-bidan-logo {
        width: 130px;
        height: auto;
        object-fit: contain;
        display: block;
    }

    /* =========================================
       3. USER CARD (PROFIL) - PRESISI & ANTI-BOCOR
       ========================================= */
    .pc-bidan-user-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px; 
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    }

    .pc-bidan-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #ffffff;
        font-size: 16px;
        font-weight: 800;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
    }

    .pc-bidan-user-meta {
        flex: 1;
        min-width: 0; /* Kunci agar teks nama bisa terpotong dengan '...' */
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .pc-bidan-user-name {
        margin: 0 0 2px 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .pc-bidan-user-role {
        margin: 0 0 6px 0;
        color: #64748b;
        font-size: 11px;
        font-weight: 600;
        line-height: 1;
    }

    .pc-bidan-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 6px;
        background: #ecfdf5;
        border: 1px solid #d1fae5;
        width: max-content;
    }

    .pc-bidan-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        flex-shrink: 0;
    }

    .pc-bidan-status-text {
        color: #047857;
        font-size: 9.5px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }

    /* =========================================
       4. SCROLL AREA & NAVIGATION MENUS
       ========================================= */
    .pc-bidan-scroll {
        position: relative;
        z-index: 5;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0 20px 24px;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .pc-bidan-scroll::-webkit-scrollbar { display: none; width: 0; height: 0; }

    .pc-bidan-menu-group { margin-bottom: 22px; }
    .pc-bidan-menu-group:last-child { margin-bottom: 0; }

    .pc-bidan-menu-title {
        margin: 0 0 10px;
        padding-left: 6px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .pc-bidan-menu-list { display: flex; flex-direction: column; gap: 4px; }

    .pc-bidan-menu-item {
        position: relative;
        width: 100%;
        min-height: 42px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border: 0;
        border-radius: 12px;
        background: transparent;
        color: #475569;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    .pc-bidan-menu-item:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .pc-bidan-menu-item.active {
        background: #ecfdf5;
        color: #047857;
        font-weight: 700;
    }

    .pc-bidan-menu-item.active::before {
        content: "";
        position: absolute;
        left: 0; top: 10px; bottom: 10px;
        width: 3.5px;
        border-radius: 0 4px 4px 0;
        background: #10b981;
    }

    .pc-bidan-menu-icon {
        width: 18px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 14px;
    }

    .pc-bidan-menu-item:hover .pc-bidan-menu-icon { color: #475569; }
    .pc-bidan-menu-item.active .pc-bidan-menu-icon { color: #10b981; }

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
        padding: 0 6px;
        border-radius: 999px;
        background: #fee2e2;
        color: #ef4444;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
    }

    /* Tombol Logout */
    .pc-bidan-logout-form { margin: 0; padding: 0; }
    .pc-bidan-logout { color: #dc2626; }
    .pc-bidan-logout .pc-bidan-menu-icon { color: #f87171; }
    .pc-bidan-logout:hover { background: #fef2f2; color: #991b1b; }
    .pc-bidan-logout:hover .pc-bidan-menu-icon { color: #991b1b; }
</style>

<div class="pc-bidan-sidebar">

    <div class="pc-bidan-top">
        <div class="pc-bidan-logo-wrap">
            <a href="{{ route('bidan.dashboard') }}">
                <img src="{{ asset('img/logo.webp') }}" alt="Logo" class="pc-bidan-logo">
            </a>
        </div>

        <div class="pc-bidan-user-card">
            <div class="pc-bidan-avatar">
                {{ $initial }}
            </div>
            <div class="pc-bidan-user-meta">
                <h4 class="pc-bidan-user-name" title="{{ $rawName }}">
                    {{ $bidanName }}
                </h4>
                <p class="pc-bidan-user-role">Tenaga Bidan</p>
                <div class="pc-bidan-status">
                    <span class="pc-bidan-status-dot"></span>
                    <span class="pc-bidan-status-text">Akses Klinis Aktif</span>
                </div>
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
            <form method="POST" action="{{ route('logout') }}" class="pc-bidan-logout-form js-logout-form">
                @csrf
                <button type="submit" class="pc-bidan-menu-item pc-bidan-logout">
                    <span class="pc-bidan-menu-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                    <span class="pc-bidan-menu-text">Keluar Aplikasi</span>
                </button>
            </form>
        </div>

    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollArea = document.getElementById('bidanSidebarScrollArea');
        if (!scrollArea) return;

        // Mencegah body belakang ikut ter-scroll saat scroll sidebar di PC (Mouse wheel)
        scrollArea.addEventListener('wheel', function (e) {
            const atTop = scrollArea.scrollTop <= 0;
            const atBottom = Math.ceil(scrollArea.scrollTop + scrollArea.clientHeight) >= scrollArea.scrollHeight;
            if ((e.deltaY < 0 && atTop) || (e.deltaY > 0 && atBottom)) e.preventDefault();
            e.stopPropagation();
        }, { passive: false });

        // Mencegah body belakang ikut ter-scroll saat swipe sidebar di HP (Touch)
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