@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    $route = request()->route()?->getName() ?? '';
    $name = Auth::user()->name ?? 'Kader Posyandu';
    $initial = strtoupper(substr($name, 0, 1));
    
    $isData = Str::startsWith($route, ['kader.data.', 'kader.balita', 'kader.remaja', 'kader.lansia']);
    $to = fn ($name, $fallback = null) => Route::has($name) ? route($name) : (($fallback && Route::has($fallback)) ? route($fallback) : '#');

    $mainMenus = [
        ['Dashboard', 'fa-house', $to('kader.dashboard'), $route === 'kader.dashboard'],
        ['Registrasi Hadir', 'fa-user-check', $to('kader.absensi.index'), $route === 'kader.absensi.index'],
        ['Pengukuran Fisik', 'fa-stethoscope', $to('kader.pemeriksaan.index'), Str::startsWith($route, 'kader.pemeriksaan')],
        ['Log Imunisasi', 'fa-syringe', $to('kader.imunisasi.index'), Str::startsWith($route, 'kader.imunisasi')],
    ];

    $dataMenus = [
        ['Balita', $to('kader.data.balita.index', 'kader.balita.index'), Str::startsWith($route, ['kader.data.balita', 'kader.balita'])],
        ['Remaja', $to('kader.data.remaja.index', 'kader.remaja.index'), Str::startsWith($route, ['kader.data.remaja', 'kader.remaja'])],
        ['Lansia', $to('kader.data.lansia.index', 'kader.lansia.index'), Str::startsWith($route, ['kader.data.lansia', 'kader.lansia'])],
    ];

    $manageMenus = [
        ['Agenda', 'fa-calendar-check', $to('kader.jadwal.index'), Str::startsWith($route, 'kader.jadwal')],
        ['Laporan', 'fa-file-lines', $to('kader.laporan.index'), Str::startsWith($route, 'kader.laporan')],
    ];
@endphp

<style>
    /* Wadah utama dibuat lebih bersih */
    .side-shell { display: flex; flex-direction: column; min-height: 100%; position: relative; }

    /* ── KONDISI NORMAL (TANPA CARD BERTUMPUK) ── */
    
    /* Logo dibersihkan dari background dan box-shadow agar menyatu dengan sidebar */
    .side-logo {
        display: flex; justify-content: center; align-items: center; padding: 12px 16px; margin-bottom: 12px;
        transition: opacity 0.2s ease;
    }
    .side-logo img { width: 140px; max-width: 90%; max-height: 45px; object-fit: contain; }

    /* Area User dibuat 'Flat' dan transparan, menghindari shadow-thrashing saat animasi */
    .side-user {
        display: grid; grid-template-columns: 48px minmax(0, 1fr); align-items: center; gap: 12px; 
        padding: 12px; margin-bottom: 24px; border-radius: 18px;
        background: rgba(20, 184, 166, 0.05); /* Tint warna hijau yang sangat lembut */
        border: 1px solid rgba(20, 184, 166, 0.1);
    }
    
    /* Avatar disederhanakan shadow-nya agar ringan di GPU */
    .side-avatar {
        width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; 
        font-size: 18px; font-weight: 900; color: #fff;
        background: linear-gradient(135deg, #14b8a6, #0d9488); 
        overflow: hidden;
    }
    .side-avatar img { width: 100%; height: 100%; object-fit: cover; }
    
    .side-user-meta { display: flex; flex-direction: column; overflow: hidden; }
    .side-user-name { color: #0f172a; font-size: 13.5px; font-weight: 900; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }
    .side-user-role { color: #64748b; font-size: 11px; font-weight: 800; margin-top: 2px; }
    
    .side-group { margin-bottom: 20px; }
    .side-title { margin: 0 0 8px 6px; color: #94a3b8; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }
    
    /* Transisi diganti dari 'all' menjadi spesifik (background-color, color) agar tidak Lag */
    .side-link {
        display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 14px; color: #475569; 
        font-size: 13px; font-weight: 800; text-decoration: none; width: 100%; min-height: 44px; border: none; 
        cursor: pointer; background: transparent;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .side-link:hover, .side-link.active { background: rgba(20, 184, 166, 0.08); color: #0d9488; }
    .side-link.active { position: relative; font-weight: 900; }
    .side-link.active::before { content: ""; position: absolute; left: 0; top: 10px; bottom: 10px; width: 4px; border-radius: 9px; background: #0d9488; }
    
    .side-icon { width: 22px; display: flex; justify-content: center; color: #94a3b8; font-size: 16px; transition: color 0.15s ease; }
    .side-link:hover .side-icon, .side-link.active .side-icon { color: #0d9488; }
    .side-text { white-space: nowrap; }
    .caret { margin-left: auto; font-size: 11px; transition: transform 0.2s ease; color: #94a3b8; }
    
    .side-submenu { display: flex; flex-direction: column; gap: 2px; padding: 4px 0 0 34px; }
    .side-sublink { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 12px; color: #64748b; font-size: 12.5px; font-weight: 800; transition: color 0.15s ease, background-color 0.15s ease; }
    .side-sublink:hover, .side-sublink.active { color: #0d9488; background: rgba(20, 184, 166, 0.05); }
    .side-subicon { font-size: 6px; opacity: 0.5; transition: color 0.15s ease, opacity 0.15s ease; }
    .side-sublink:hover .side-subicon, .side-sublink.active .side-subicon { color: #0d9488; opacity: 1; }

    /* ── KONDISI KETIKA SIDEBAR COLLAPSED (JIKA MENGGUNAKAN MINI SIDEBAR) ── */
    .collapsed-mode .side-shell { padding: 16px 8px; }
    .collapsed-mode .side-logo { padding: 12px 0; background: transparent; border: none; box-shadow: none; margin-bottom: 8px; }
    .collapsed-mode .side-logo img { display: none; }
    .collapsed-mode .side-logo::after { content: "P"; font-size: 30px; font-weight: 900; background: linear-gradient(135deg, #14b8a6, #0d9488); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    
    .collapsed-mode .side-user { grid-template-columns: 1fr; padding: 8px; margin-bottom: 24px; background: transparent; border: none; }
    .collapsed-mode .side-avatar { width: 40px; height: 40px; border-radius: 12px; margin: 0 auto; }
    .collapsed-mode .side-user-meta { display: none; }
    
    .collapsed-mode .side-group { margin-bottom: 12px; }
    .collapsed-mode .side-title { display: none; }
    .collapsed-mode .side-link { padding: 14px 0; justify-content: center; border-radius: 12px; }
    .collapsed-mode .side-text { display: none; }
    .collapsed-mode .side-icon { margin: 0; font-size: 18px; }
    .collapsed-mode .caret { display: none; }
    .collapsed-mode .side-submenu { display: none !important; }
</style>

<div class="side-shell pc-sidebar">

    <a href="{{ route('kader.dashboard') }}" class="side-logo">
        <img src="{{ asset('img/logo.webp') }}" alt="PosyanduCare">
    </a>

    <div class="side-user">
        <div class="side-avatar">
            @if(Auth::check() && !empty(Auth::user()->foto))
                <img src="{{ asset('storage/' . Auth::user()->foto) }}" alt="Foto">
            @else
                {{ $initial }}
            @endif
        </div>
        <div class="side-user-meta">
            <h4 class="side-user-name">{{ $name }}</h4>
            <div class="side-user-role">Kader Aktif</div>
        </div>
    </div>

    <div class="side-group">
        <div class="side-title">Menu Utama</div>
        @foreach($mainMenus as [$label, $icon, $url, $active])
            <a href="{{ $url }}" class="side-link {{ $active ? 'active' : '' }}" title="{{ $label }}">
                <span class="side-icon"><i class="fa-solid {{ $icon }}"></i></span>
                <span class="side-text">{{ $label }}</span>
            </a>
        @endforeach
    </div>

    <div class="side-group" x-data="{ open: {{ $isData ? 'true' : 'false' }} }">
        <div class="side-title">Database Warga</div>
        <button type="button" @click="open = !open" class="side-link {{ $isData ? 'active' : '' }}" title="Data Pasien">
            <span class="side-icon"><i class="fa-solid fa-address-book"></i></span>
            <span class="side-text">Data Pasien</span>
            <i class="fa-solid fa-chevron-down caret" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="open" x-collapse class="side-submenu">
            @foreach($dataMenus as [$label, $url, $active])
                <a href="{{ $url }}" class="side-sublink {{ $active ? 'active' : '' }}">
                    <i class="fa-solid fa-circle side-subicon"></i>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="side-group">
        <div class="side-title">Manajemen</div>
        @foreach($manageMenus as [$label, $icon, $url, $active])
            <a href="{{ $url }}" class="side-link {{ $active ? 'active' : '' }}" title="{{ $label }}">
                <span class="side-icon"><i class="fa-solid {{ $icon }}"></i></span>
                <span class="side-text">{{ $label }}</span>
            </a>
        @endforeach
    </div>

</div>