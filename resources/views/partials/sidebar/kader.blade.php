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
    /* SIDEBAR KADER CLEAN MOBILE */
    .pc-kader-sidebar {
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
    .pc-kader-sidebar::before {
        content: ""; position: absolute; width: 260px; height: 260px; top: -120px; left: -110px;
        border-radius: 999px; background: rgba(16,185,129,.12); filter: blur(70px); pointer-events: none;
    }
    .pc-kader-sidebar::after {
        content: ""; position: absolute; width: 250px; height: 250px; right: -120px; bottom: -110px;
        border-radius: 999px; background: rgba(20,184,166,.12); filter: blur(70px); pointer-events: none;
    }

    /* TOP & LOGO */
    .pc-kader-top { position: relative; z-index: 4; flex-shrink: 0; padding: 24px 18px 0; }
    .pc-kader-logo-wrap { display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
    .pc-kader-logo-link { display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
    .pc-kader-logo {
        width: 148px; height: auto; object-fit: contain; display: block;
        filter: drop-shadow(0 12px 22px rgba(15,23,42,.08)) drop-shadow(0 2px 4px rgba(16,185,129,.08));
    }

    /* USER CARD */
    .pc-kader-card {
        display: flex; align-items: center; gap: 14px; padding: 12px 14px; margin-bottom: 24px;
        border-radius: 20px; background: rgba(255,255,255,.6); border: 1px solid rgba(255,255,255,.8);
        box-shadow: 0 4px 20px rgba(0,0,0,.03); backdrop-filter: blur(10px);
    }
    .pc-kader-avatar {
        position: relative; width: 44px; height: 44px; flex-shrink: 0; border-radius: 14px; overflow: hidden;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: flex;
        align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 16px;
        box-shadow: 0 8px 16px rgba(16,185,129,.25);
    }
    .pc-kader-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pc-kader-active-dot {
        position: absolute; bottom: -2px; right: -2px; width: 12px; height: 12px; z-index: 2;
        background-color: #10b981; border: 2px solid #ffffff; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .pc-kader-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
    .pc-kader-info h4 { margin: 0; color: #1e293b; font-size: 13.5px; font-weight: 900; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pc-kader-info p { margin: 3px 0 0 0; color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }

    /* SCROLL AREA */
    .pc-kader-scroll {
        position: relative; z-index: 3; flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden;
        padding: 0 18px 120px; scrollbar-width: none; -ms-overflow-style: none;
        overscroll-behavior: contain; overscroll-behavior-y: contain; touch-action: pan-y; -webkit-overflow-scrolling: touch;
    }
    .pc-kader-scroll::-webkit-scrollbar { width: 0; height: 0; display: none; }

    /* MENU */
    .pc-kader-menu-group { margin-bottom: 20px; }
    .pc-kader-menu-group:last-child { margin-bottom: 0; }
    .pc-kader-menu-title { margin: 0 0 10px; padding-left: 4px; color: #94a3b8; font-size: 10.5px; font-weight: 900; text-transform: uppercase; letter-spacing: .12em; }
    .pc-kader-menu-list { display: flex; flex-direction: column; gap: 6px; }
    .pc-kader-menu-item {
        position: relative; width: 100%; min-height: 42px; display: flex; align-items: center; gap: 12px;
        padding: 10px 13px; border: 0; border-radius: 15px; background: transparent; color: #475569;
        text-decoration: none; font-size: 13px; font-weight: 800; cursor: pointer; text-align: left;
        transition: background .28s cubic-bezier(.16, 1, .3, 1), color .28s cubic-bezier(.16, 1, .3, 1), transform .28s cubic-bezier(.16, 1, .3, 1), box-shadow .28s cubic-bezier(.16, 1, .3, 1);
    }
    .pc-kader-menu-item:hover { background: rgba(236,253,245,.92); color: #047857; transform: translateX(3px); }
    .pc-kader-menu-item.active {
        background: linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.84));
        color: #047857; font-weight: 900; box-shadow: 0 10px 24px rgba(16,185,129,.08), inset 0 1px 0 rgba(255,255,255,.92);
    }
    .pc-kader-menu-item.active::before {
        content: ""; position: absolute; left: 0; top: 9px; bottom: 9px; width: 4px;
        border-radius: 999px; background: linear-gradient(180deg, #10b981, #059669);
    }
    .pc-kader-menu-icon {
        width: 22px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        color: #94a3b8; font-size: 14px; transition: color .28s ease, transform .28s ease;
    }
    .pc-kader-menu-item:hover .pc-kader-menu-icon, .pc-kader-menu-item.active .pc-kader-menu-icon { color: #059669; transform: scale(1.08); }
    .pc-kader-menu-text { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* DROPDOWN KADER */
    .pc-kader-collapse-icon { margin-left: auto; font-size: 11px; color: #94a3b8; transition: transform .2s ease; }
    .pc-kader-collapse-wrapper.is-open .pc-kader-collapse-icon { transform: rotate(180deg); color: #059669; }
    .pc-kader-collapse-content { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .25s cubic-bezier(0.4, 0, 0.2, 1); }
    .pc-kader-collapse-wrapper.is-open .pc-kader-collapse-content { grid-template-rows: 1fr; }
    .pc-kader-collapse-inner { overflow: hidden; display: flex; flex-direction: column; gap: 4px; padding-left: 36px; }
    .pc-kader-collapse-wrapper.is-open .pc-kader-collapse-inner { padding-top: 6px; padding-bottom: 6px; }
    .pc-kader-sublink {
        display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 12px; color: #64748b; font-size: 12.5px;
        font-weight: 800; text-decoration: none; transition: color .15s ease, background-color .15s ease;
    }
    .pc-kader-sublink:hover, .pc-kader-sublink.active { color: #059669; background: rgba(16,185,129,.06); }
    .pc-kader-subicon { font-size: 5px; opacity: 0.4; transition: color .15s ease, opacity .15s ease; }
    .pc-kader-sublink:hover .pc-kader-subicon, .pc-kader-sublink.active .pc-kader-subicon { color: #059669; opacity: 1; }

    /* Tombol Logout */
    .pc-kader-logout { color: #ef4444; }
    .pc-kader-logout .pc-kader-menu-icon { color: #f87171; }
    .pc-kader-logout:hover { background: #fef2f2; color: #dc2626; }
    .pc-kader-logout:hover .pc-kader-menu-icon { color: #dc2626; transform: scale(1.08); }

    /* BOTTOM DECOR */
    .pc-kader-bottom-decor {
        position: absolute; bottom: 0; left: 0; width: 100%; height: 110px; z-index: 1;
        overflow: hidden; pointer-events: none; border-radius: 0 0 28px 28px;
    }
    .pc-kader-wave { position: absolute; left: -20%; width: 140%; border-radius: 50% 50% 0 0; }
    .pc-kader-wave-1 { bottom: -30px; height: 96px; background: rgba(16,185,129,.14); }
    .pc-kader-wave-2 { bottom: -45px; height: 106px; background: rgba(5,150,105,.13); }
    .pc-kader-wave-3 { bottom: -55px; height: 116px; background: rgba(20,184,166,.10); }
    .pc-kader-plant { position: absolute; right: 22px; bottom: 12px; width: 64px; height: 64px; }
    .pc-kader-stem {
        position: absolute; left: 31px; bottom: 0; width: 3px; height: 46px; border-radius: 999px;
        background: rgba(4,120,87,.35); transform: rotate(18deg); transform-origin: bottom;
    }
    .pc-kader-leaf {
        position: absolute; width: 31px; height: 16px; border-radius: 100% 0 100% 0;
        background: linear-gradient(135deg, rgba(4,120,87,.66), rgba(16,185,129,.24)); transform-origin: bottom left;
    }
    .pc-kader-leaf-1 { right: 19px; bottom: 23px; transform: rotate(-34deg); }
    .pc-kader-leaf-2 { right: 32px; bottom: 35px; transform: rotate(-8deg) scale(.9); }
    .pc-kader-leaf-3 { right: 7px; bottom: 36px; transform: rotate(28deg) scale(.86); }
    .pc-kader-leaf-4 { right: 25px; bottom: 11px; transform: rotate(46deg) scale(.72); }

    /* ANIMATION */
    .pc-kader-top, .pc-kader-menu-group { opacity: 0; transform: translateY(16px); animation: pcKaderSidebarIn .85s cubic-bezier(.22, 1, .36, 1) forwards; }
    .pc-kader-bottom-decor { opacity: 0; animation: pcKaderDecorIn .85s ease forwards; }
    .pc-kader-top { animation-delay: .06s; }
    .pc-kader-menu-group:nth-child(1) { animation-delay: .14s; }
    .pc-kader-menu-group:nth-child(2) { animation-delay: .22s; }
    .pc-kader-bottom-decor { animation-delay: .30s; }

    @keyframes pcKaderSidebarIn { to { opacity: 1; transform: translateY(0); } }
    @keyframes pcKaderDecorIn { to { opacity: 1; } }
    @media (max-width: 420px) {
        .pc-kader-sidebar { border-radius: 24px; }
        .pc-kader-top { padding: 22px 16px 0; }
        .pc-kader-scroll { padding: 0 16px 120px; }
        .pc-kader-logo { width: 138px; }
    }
</style>

<div class="pc-kader-sidebar">

    <div class="pc-kader-top">
        <div class="pc-kader-logo-wrap">
            <a href="{{ $to('kader.dashboard') }}" class="pc-kader-logo-link app-link">
                <img src="{{ asset('img/logo.webp') }}" alt="Logo PosyanduCare" class="pc-kader-logo" onerror="this.src='{{ asset('img/logo.png') }}'">
            </a>
        </div>

        <div class="pc-kader-card">
            <div class="pc-kader-avatar">
                @if($photo)
                    <img src="{{ asset('storage/' . $photo) }}" alt="Foto">
                @else
                    {{ $initial }}
                @endif
                <span class="pc-kader-active-dot" title="Portal Aktif"></span>
            </div>
            <div class="pc-kader-info">
                <h4 title="{{ $kaderName }}">{{ $kaderName }}</h4>
                <p>Kader Posyandu</p>
            </div>
        </div>
    </div>

    <div class="pc-kader-scroll" id="kaderSidebarScrollArea">

        <div class="pc-kader-menu-group">
            <p class="pc-kader-menu-title">Menu Utama</p>
            <div class="pc-kader-menu-list">
                <a href="{{ $to('kader.dashboard') }}" class="pc-kader-menu-item app-link {{ $isDashboard ? 'active' : '' }}">
                    <span class="pc-kader-menu-icon"><i class="fa-solid fa-house"></i></span>
                    <span class="pc-kader-menu-text">Dashboard</span>
                </a>
                <a href="{{ $to('kader.kunjungan.index') }}" class="pc-kader-menu-item app-link {{ $isKunjungan ? 'active' : '' }}">
                    <span class="pc-kader-menu-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    <span class="pc-kader-menu-text">Buku Kunjungan</span>
                </a>
                <a href="{{ $to('kader.absensi.index') }}" class="pc-kader-menu-item app-link {{ $isAbsensi ? 'active' : '' }}">
                    <span class="pc-kader-menu-icon"><i class="fa-solid fa-user-check"></i></span>
                    <span class="pc-kader-menu-text">Registrasi Hadir</span>
                </a>
                <a href="{{ $to('kader.pemeriksaan.index') }}" class="pc-kader-menu-item app-link {{ $isPemeriksaan ? 'active' : '' }}">
                    <span class="pc-kader-menu-icon"><i class="fa-solid fa-stethoscope"></i></span>
                    <span class="pc-kader-menu-text">Pengukuran Fisik</span>
                </a>
                <a href="{{ $to('kader.imunisasi.index') }}" class="pc-kader-menu-item app-link {{ $isImunisasi ? 'active' : '' }}">
                    <span class="pc-kader-menu-icon"><i class="fa-solid fa-syringe"></i></span>
                    <span class="pc-kader-menu-text">Log Imunisasi</span>
                </a>
            </div>
        </div>

        <div class="pc-kader-menu-group pc-kader-collapse-wrapper {{ $isDbWarga ? 'is-open' : '' }}">
            <p class="pc-kader-menu-title">Database Warga</p>
            <button type="button" class="pc-kader-menu-item" onclick="this.parentElement.classList.toggle('is-open')">
                <span class="pc-kader-menu-icon"><i class="fa-solid fa-address-book"></i></span>
                <span class="pc-kader-menu-text">Data Pasien</span>
                <i class="fa-solid fa-chevron-down pc-kader-collapse-icon"></i>
            </button>
            <div class="pc-kader-collapse-content">
                <div class="pc-kader-collapse-inner">
                    <a href="{{ $to('kader.data.balita.index', 'kader.balita.index') }}" class="pc-kader-sublink app-link {{ $isBalita ? 'active' : '' }}">
                        <i class="fa-solid fa-circle pc-kader-subicon"></i>
                        <span>Balita</span>
                    </a>
                    <a href="{{ $to('kader.data.remaja.index', 'kader.remaja.index') }}" class="pc-kader-sublink app-link {{ $isRemaja ? 'active' : '' }}">
                        <i class="fa-solid fa-circle pc-kader-subicon"></i>
                        <span>Remaja</span>
                    </a>
                    <a href="{{ $to('kader.data.lansia.index', 'kader.lansia.index') }}" class="pc-kader-sublink app-link {{ $isLansia ? 'active' : '' }}">
                        <i class="fa-solid fa-circle pc-kader-subicon"></i>
                        <span>Lansia</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="pc-kader-menu-group">
            <p class="pc-kader-menu-title">Manajemen</p>
            <div class="pc-kader-menu-list">
                <a href="{{ $to('kader.jadwal.index') }}" class="pc-kader-menu-item app-link {{ $isJadwal ? 'active' : '' }}">
                    <span class="pc-kader-menu-icon"><i class="fa-solid fa-calendar-check"></i></span>
                    <span class="pc-kader-menu-text">Agenda Posyandu</span>
                </a>
                <a href="{{ $to('kader.laporan.index') }}" class="pc-kader-menu-item app-link {{ $isLaporan ? 'active' : '' }}">
                    <span class="pc-kader-menu-icon"><i class="fa-solid fa-file-lines"></i></span>
                    <span class="pc-kader-menu-text">Laporan Kegiatan</span>
                </a>
            </div>
        </div>

        <div class="pc-kader-menu-group">
            <p class="pc-kader-menu-title">Sesi Akun</p>
            <div class="pc-kader-menu-list">
                <form method="POST" action="{{ route('logout') }}" class="js-logout-form m-0 p-0">
                    @csrf
                    <button type="submit" class="pc-kader-menu-item pc-kader-logout">
                        <span class="pc-kader-menu-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span class="pc-kader-menu-text">Keluar Aplikasi</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

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

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollArea = document.getElementById('kaderSidebarScrollArea');
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