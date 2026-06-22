@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    $kaderName = $user->name ?? $user->nama ?? 'Kader Posyandu';
    $initial = strtoupper(substr($kaderName, 0, 1));
    $photo = $user->foto ?? null;

    $route = request()->route()?->getName() ?? '';
    $to = fn ($name, $fallback = null) => Route::has($name) ? route($name) : (($fallback && Route::has($fallback)) ? route($fallback) : '#');

    $isDashboard = $route === 'kader.dashboard';
    $isKunjungan = Str::startsWith($route, 'kader.kunjungan');
    $isAbsensi = $route === 'kader.absensi.index';
    $isPemeriksaan = Str::startsWith($route, 'kader.pemeriksaan');
    $isImunisasi = Str::startsWith($route, 'kader.imunisasi');

    $isBalita = Str::startsWith($route, ['kader.data.balita', 'kader.balita']);
    $isRemaja = Str::startsWith($route, ['kader.data.remaja', 'kader.remaja']);
    $isLansia = Str::startsWith($route, ['kader.data.lansia', 'kader.lansia']);
    $isDbWarga = $isBalita || $isRemaja || $isLansia;

    $isJadwal = Str::startsWith($route, 'kader.jadwal');
    $isLaporan = Str::startsWith($route, 'kader.laporan');
@endphp

<style>
    .pc-sidebar {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    .pc-sidebar::-webkit-scrollbar,
    .pc-sidebar *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .side-shell {
        min-height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .side-logo {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 18px 16px 20px;
        margin-bottom: 16px;
        border-radius: 26px;
        background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,255,252,.88));
        border: 1px solid rgba(226,232,240,.78);
        box-shadow: 0 16px 34px rgba(15,23,42,.055);
    }

    .side-logo img {
        width: 152px;
        max-width: 86%;
        max-height: 82px;
        object-fit: contain;
        display: block;
        filter: drop-shadow(0 8px 15px rgba(15,23,42,.08));
    }

    .side-user {
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        padding: 14px;
        margin: 0 2px 24px;
        border-radius: 24px;
        background:
            radial-gradient(circle at 18% 22%, rgba(16,185,129,.14), transparent 34%),
            linear-gradient(135deg, rgba(255,255,255,.94), rgba(240,253,250,.82));
        border: 1px solid rgba(16,185,129,.20);
        box-shadow:
            0 16px 34px rgba(15,23,42,.055),
            inset 0 1px 0 rgba(255,255,255,.96);
    }

    .side-avatar {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
        font-size: 20px;
        font-weight: 900;
        background: linear-gradient(135deg, #22c55e 0%, #10b981 50%, #f59e0b 100%);
        box-shadow:
            0 14px 26px rgba(16,185,129,.22),
            inset 0 1px 0 rgba(255,255,255,.25);
        overflow: hidden;
    }
    
    .side-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .side-user-meta {
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .side-user-name {
        width: 100%;
        margin: 0;
        color: #064e3b;
        font-size: 13.5px;
        font-weight: 900;
        line-height: 1.15;
        letter-spacing: -.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .side-user-role {
        margin: 4px 0 6px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
    }

    .side-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 24px;
        padding: 5px 9px;
        border-radius: 999px;
        background: rgba(236,253,245,.96);
        border: 1px solid rgba(16,185,129,.13);
        color: #047857;
        font-size: 9.5px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
        max-width: 100%;
    }

    .side-status span:last-child {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .side-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16,185,129,.12);
        flex-shrink: 0;
    }

    .side-group {
        margin-bottom: 22px;
        position: relative;
        z-index: 2;
    }

    .side-title {
        margin: 0 0 10px 6px;
        color: #64748b;
        font-size: 10.5px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .10em;
    }

    .side-menu {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .side-link,
    .side-logout {
        position: relative;
        width: 100%;
        min-height: 46px;
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 11px 14px;
        border: 0;
        border-radius: 15px;
        background: transparent;
        color: #334155;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        transition: background .10s ease, color .10s ease, transform .10s ease;
    }

    .side-link:hover,
    .side-link.active {
        background: linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.86));
        color: #047857;
    }

    .side-link:hover {
        transform: translateX(2px);
    }

    .side-link.active {
        box-shadow:
            0 10px 22px rgba(16,185,129,.07),
            inset 0 1px 0 rgba(255,255,255,.90);
    }

    .side-link.active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 10px;
        bottom: 10px;
        width: 4px;
        border-radius: 999px;
        background: linear-gradient(180deg, #10b981, #059669);
    }

    .side-icon {
        width: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 14px;
        flex-shrink: 0;
        transition: color .10s ease;
    }

    .side-link:hover .side-icon,
    .side-link.active .side-icon {
        color: #059669;
    }

    .side-text {
        min-width: 0;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
    }

    /* ── Dropdown Style ── */
    .side-collapse-icon {
        margin-left: auto;
        font-size: 11px;
        color: #94a3b8;
        transition: transform .2s ease;
    }
    
    .side-collapse-wrapper.is-open .side-collapse-icon {
        transform: rotate(180deg);
        color: #059669;
    }

    .side-collapse-content {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows .25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .side-collapse-wrapper.is-open .side-collapse-content {
        grid-template-rows: 1fr;
    }

    .side-collapse-inner {
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding-left: 36px;
    }

    .side-collapse-wrapper.is-open .side-collapse-inner {
        padding-top: 6px;
        padding-bottom: 6px;
    }

    .side-sublink {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border-radius: 12px;
        color: #64748b;
        font-size: 12.5px;
        font-weight: 800;
        text-decoration: none;
        transition: color .15s ease, background-color .15s ease;
    }

    .side-sublink:hover,
    .side-sublink.active {
        color: #059669;
        background: rgba(16,185,129,.06);
    }

    .side-subicon {
        font-size: 5px;
        opacity: 0.4;
        transition: color .15s ease, opacity .15s ease;
    }

    .side-sublink:hover .side-subicon,
    .side-sublink.active .side-subicon {
        color: #059669;
        opacity: 1;
    }

    .side-logout {
        color: #ef4444;
    }

    .side-logout .side-icon {
        color: #ef4444;
    }

    .side-logout:hover {
        background: #fff1f2;
        color: #dc2626;
    }

    .side-logout:hover .side-icon {
        color: #dc2626;
    }

    .side-bottom {
        margin-top: auto;
        height: 96px;
        position: relative;
        pointer-events: none;
        overflow: hidden;
        opacity: .82;
    }

    .side-bottom::before {
        content: "";
        position: absolute;
        left: -30px;
        right: -30px;
        bottom: -56px;
        height: 108px;
        border-radius: 50% 50% 0 0;
        background:
            radial-gradient(circle at 72% 18%, rgba(16,185,129,.26), transparent 18%),
            linear-gradient(160deg, rgba(16,185,129,.14), rgba(14,165,233,.08));
    }

    .side-bottom::after {
        content: "";
        position: absolute;
        right: 38px;
        bottom: 18px;
        width: 72px;
        height: 52px;
        border-radius: 80% 0 80% 0;
        background: rgba(16,185,129,.32);
        transform: rotate(-18deg);
    }

    @media (max-height: 720px) {
        .side-logo { padding: 14px 14px 16px; margin-bottom: 12px; }
        .side-logo img { width: 132px; }
        .side-user { padding: 13px; margin-bottom: 18px; }
        .side-avatar { width: 48px; height: 48px; border-radius: 16px; font-size: 18px; }
        .side-group { margin-bottom: 16px; }
        .side-link, .side-logout { min-height: 42px; padding: 9px 13px; }
        .side-bottom { height: 58px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .side-link, .side-logout, .side-icon, .side-collapse-content { transition: none !important; }
    }
</style>

<div class="side-shell">

    <!-- Area Logo -->
    <div class="side-logo">
        <a href="{{ $to('kader.dashboard') }}" aria-label="Dashboard Kader" class="app-link">
            <img src="{{ asset('img/logo.webp') }}" alt="Logo PosyanduCare" onerror="this.src='{{ asset('img/logo.png') }}'">
        </a>
    </div>

    <!-- Area Profil Pengguna -->
    <div class="side-user">
        <div class="side-avatar">
            @if($photo)
                <img src="{{ asset('storage/' . $photo) }}" alt="Foto">
            @else
                {{ $initial }}
            @endif
        </div>

        <div class="side-user-meta">
            <h4 class="side-user-name" title="{{ $kaderName }}">
                {{ $kaderName }}
            </h4>

            <div class="side-user-role">
                Kader Posyandu
            </div>

            <div class="side-status" title="Akses Kader Aktif">
                <span class="side-status-dot"></span>
                <span>Akses Kader Aktif</span>
            </div>
        </div>
    </div>

    <!-- Menu Utama -->
    <div class="side-group">
        <div class="side-title">Menu Utama</div>

        <div class="side-menu">
            <a href="{{ $to('kader.dashboard') }}" class="side-link app-link {{ $isDashboard ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-house"></i></span>
                <span class="side-text">Dashboard</span>
            </a>

            <!-- Ditambahkan Kembali: Buku Induk Kunjungan -->
            <a href="{{ $to('kader.kunjungan.index') }}" class="side-link app-link {{ $isKunjungan ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                <span class="side-text">Buku Kunjungan</span>
            </a>
            
            <a href="{{ $to('kader.absensi.index') }}" class="side-link app-link {{ $isAbsensi ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-user-check"></i></span>
                <span class="side-text">Registrasi Hadir</span>
            </a>
            
            <a href="{{ $to('kader.pemeriksaan.index') }}" class="side-link app-link {{ $isPemeriksaan ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-stethoscope"></i></span>
                <span class="side-text">Pengukuran Fisik</span>
            </a>

            <a href="{{ $to('kader.imunisasi.index') }}" class="side-link app-link {{ $isImunisasi ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-syringe"></i></span>
                <span class="side-text">Log Imunisasi</span>
            </a>
        </div>
    </div>

    <!-- Database Warga (Dropdown Mulus CSS Grid) -->
    <div class="side-group side-collapse-wrapper {{ $isDbWarga ? 'is-open' : '' }}">
        <div class="side-title">Database Warga</div>

        <button type="button" class="side-link" onclick="this.parentElement.classList.toggle('is-open')">
            <span class="side-icon"><i class="fa-solid fa-address-book"></i></span>
            <span class="side-text">Data Pasien</span>
            <i class="fa-solid fa-chevron-down side-collapse-icon"></i>
        </button>

        <div class="side-collapse-content">
            <div class="side-collapse-inner">
                <a href="{{ $to('kader.data.balita.index', 'kader.balita.index') }}" class="side-sublink app-link {{ $isBalita ? 'active' : '' }}">
                    <i class="fa-solid fa-circle side-subicon"></i>
                    <span>Balita</span>
                </a>

                <a href="{{ $to('kader.data.remaja.index', 'kader.remaja.index') }}" class="side-sublink app-link {{ $isRemaja ? 'active' : '' }}">
                    <i class="fa-solid fa-circle side-subicon"></i>
                    <span>Remaja</span>
                </a>

                <a href="{{ $to('kader.data.lansia.index', 'kader.lansia.index') }}" class="side-sublink app-link {{ $isLansia ? 'active' : '' }}">
                    <i class="fa-solid fa-circle side-subicon"></i>
                    <span>Lansia</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Manajemen -->
    <div class="side-group">
        <div class="side-title">Manajemen</div>

        <div class="side-menu">
            <a href="{{ $to('kader.jadwal.index') }}" class="side-link app-link {{ $isJadwal ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-calendar-check"></i></span>
                <span class="side-text">Agenda Posyandu</span>
            </a>

            <a href="{{ $to('kader.laporan.index') }}" class="side-link app-link {{ $isLaporan ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-file-lines"></i></span>
                <span class="side-text">Laporan Kegiatan</span>
            </a>
        </div>
    </div>

    <!-- Sesi Akun -->
    <div class="side-group">
        <div class="side-title">Sesi Akun</div>

        <div class="side-menu">
            <form method="POST" action="{{ route('logout') }}" class="js-logout-form m-0 p-0">
                @csrf
                <button type="submit" class="side-logout w-full text-left">
                    <span class="side-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                    <span class="side-text">Keluar Aplikasi</span>
                </button>
            </form>
        </div>
    </div>

    <div class="side-bottom"></div>
</div>