<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bidan Workspace') | PosyanduCare</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">

    {{-- Icon libraries --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <style>
        :root {
            --g600: #059669; --g500: #10b981; --g400: #34d399;
            --s900: #0f172a; --s700: #334155; --s500: #64748b; --s100: #f1f5f9;
            --amber: #f59e0b; --rose: #f43f5e;
            --sb-w: 292px;
            --sb-gap: 14px;
            --ease: cubic-bezier(.16,1,.3,1);
        }

        *, *::before, *::after { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--s700);
            background: linear-gradient(135deg, #f0fdf8 0%, #f8fafc 55%, #ecfdf5 100%);
            -webkit-font-smoothing: antialiased;
        }
        h1,h2,h3,h4,h5,h6 { font-family: 'Poppins', sans-serif; margin: 0; }

        /* ══ SHELL ══ */
        #shell {
            display: flex;
            height: 100dvh;
            overflow: hidden;
        }

        /* ══ SIDEBAR SLOT ══
           Hanya mengurus posisi & transisi.
           Tampilan (card, background, shadow) ada di partial. */
        #sidebar-slot {
    width: var(--sb-w);
    flex-shrink: 0;
    height: 100dvh;
    padding: var(--sb-gap);
    overflow: visible;
    position: relative;
    z-index: 70;
    animation: slideInLeft .38s var(--ease) both;
    transition: transform .28s var(--ease), width .28s var(--ease), padding .28s var(--ease);
    will-change: transform, width;
}

        /* Desktop — sidebar tersembunyi: geser ke kiri */
        body.sb-closed #sidebar-slot {
    width: 0;
    padding-left: 0;
    padding-right: 0;
    transform: translateX(calc(-1 * var(--sb-w) - 4px));
    overflow: hidden;
}

        /* ══ MAIN AREA ══ */
        #main-area {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            height: 100dvh;
            overflow: hidden;
        }

        /* ── Topbar (tidak scroll) ── */
        #topbar {
            flex-shrink: 0;
            height: 72px;
            margin: 16px 18px 0;
            padding: 0 18px 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-radius: 22px;
            background: rgba(255,255,255,.90);
            border: 1px solid rgba(226,232,240,.8);
            backdrop-filter: blur(14px);
            box-shadow: 0 8px 28px rgba(15,23,42,.06), inset 0 1px 0 rgba(255,255,255,.9);
            position: relative;
            z-index: 50;
            animation: slideInTop .36s var(--ease) both;
        }

        /* ── Konten scroll ── */
        #page-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior-y: contain;
            scrollbar-width: thin;
            scrollbar-color: rgba(148,163,184,.3) transparent;
            animation: slideInBottom .40s var(--ease) .08s both;
        }
        #page-content::-webkit-scrollbar { width: 6px; }
        #page-content::-webkit-scrollbar-thumb { background: rgba(148,163,184,.32); border-radius: 99px; }
        #page-content::-webkit-scrollbar-thumb:hover { background: rgba(100,116,139,.5); }

        .page-inner {
            max-width: 1440px;
            margin: 0 auto;
            padding: 24px 20px 80px;
        }

        /* ══ KEYFRAMES ══ */
        @keyframes slideInLeft {
            from { transform: translateX(calc(-1 * var(--sb-w) - 20px)); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideInTop {
            from { transform: translateY(-20px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideInBottom {
            from { transform: translateY(18px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }

        /* ══ MOBILE OVERLAY ══ */
        #overlay {
            display: none;
            position: fixed; inset: 0; z-index: 60;
            background: rgba(2,6,23,.28);
            backdrop-filter: blur(3px);
            opacity: 0;
            transition: opacity .2s ease;
        }
        #overlay.show { opacity: 1; }

        /* ══ TOPBAR PIECES ══ */
        .tb-left  { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .tb-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

        .tb-toggle {
            width: 42px; height: 42px; border-radius: 14px; flex-shrink: 0;
            border: 1px solid rgba(226,232,240,.8);
            background: rgba(248,250,252,.9); color: var(--s500);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: color .15s, border-color .15s, transform .15s;
        }
        .tb-toggle:hover { color: var(--g600); border-color: rgba(16,185,129,.25); transform: translateY(-1px); }

        .tb-breadcrumb {
            font-size: 10px; font-weight: 900; text-transform: uppercase;
            letter-spacing: .12em; color: var(--s500);
            display: flex; align-items: center; gap: 6px;
        }
        .tb-breadcrumb a { color: var(--g600); text-decoration: none; }
        .tb-title { font-size: 18px; font-weight: 900; color: var(--s900); letter-spacing: -.03em; margin-top: 3px; }

        .tb-chip {
            height: 42px; padding: 0 16px; border-radius: 14px;
            background: rgba(236,253,245,.7); border: 1px solid rgba(16,185,129,.15);
            color: #065f46; font-size: 11.5px; font-weight: 900;
            display: flex; align-items: center; gap: 8px;
        }

        .tb-icon-btn {
            width: 46px; height: 46px; border-radius: 50%;
            border: 1px solid rgba(226,232,240,.8); background: rgba(255,255,255,.8);
            color: var(--s500); display: flex; align-items: center; justify-content: center;
            cursor: pointer; position: relative;
            transition: background .15s, border-color .15s, transform .15s;
        }
        .tb-icon-btn:hover { background: #fff; border-color: rgba(16,185,129,.2); transform: translateY(-1px); }
        .notif-dot {
            position: absolute; top: 8px; right: 7px;
            width: 10px; height: 10px; border-radius: 50%;
            background: var(--rose); border: 2px solid #fff;
        }

        .tb-profile {
            height: 46px; padding: 3px 12px 3px 3px; border-radius: 99px;
            border: 1px solid rgba(226,232,240,.8); background: rgba(255,255,255,.8);
            display: flex; align-items: center; gap: 9px; cursor: pointer;
            transition: background .15s, border-color .15s, transform .15s;
        }
        .tb-profile:hover { background: #fff; border-color: rgba(16,185,129,.2); transform: translateY(-1px); }

        .tb-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, var(--g600), var(--g400));
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 900;
            box-shadow: 0 4px 12px rgba(16,185,129,.2);
        }
        .tb-name { font-size: 12.5px; font-weight: 900; color: var(--s700); line-height: 1.2; }
        .tb-role { font-size: 9px; font-weight: 800; color: var(--s500); text-transform: uppercase; letter-spacing: .08em; }

        /* ══ DROPDOWN ══ */
        .dropdown { position: relative; }
        .dropdown-menu {
            position: absolute; right: 0; top: calc(100% + 10px);
            min-width: 250px; border-radius: 20px; padding: 8px;
            background: rgba(255,255,255,.97); border: 1px solid rgba(226,232,240,.85);
            box-shadow: 0 20px 50px rgba(15,23,42,.13);
            backdrop-filter: blur(12px);
            opacity: 0; transform: translateY(8px) scale(.96);
            visibility: hidden; pointer-events: none;
            transition: opacity .16s ease, transform .16s var(--ease), visibility .16s;
            z-index: 80;
        }
        .notif-menu { min-width: 330px; max-width: calc(100vw - 24px); }
        .dropdown.open .dropdown-menu { opacity: 1; transform: none; visibility: visible; pointer-events: auto; }

        .dm-head { padding: 10px; border-bottom: 1px solid var(--s100); margin-bottom: 6px; display: flex; align-items: center; gap: 10px; }
        .dm-title { font-size: 13px; font-weight: 900; color: var(--s900); }
        .dm-sub   { font-size: 9.5px; font-weight: 800; color: var(--s500); text-transform: uppercase; letter-spacing: .08em; margin-top: 2px; }

        .dm-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 13px; border: 0; background: transparent;
            color: var(--s500); font-size: 12.5px; font-weight: 800;
            text-decoration: none; cursor: pointer; width: 100%;
            transition: background .15s, color .15s;
        }
        .dm-link:hover { background: #ecfdf5; color: var(--g600); }
        .dm-link.danger { color: #e11d48; }
        .dm-link.danger:hover { background: #fff1f2; color: #be123c; }

        .notif-item {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 10px; border-radius: 14px; text-decoration: none; color: var(--s700);
            transition: background .15s;
        }
        .notif-item:hover { background: #ecfdf5; }
        .ni-icon {
            width: 36px; height: 36px; flex-shrink: 0; border-radius: 12px;
            background: #ecfdf5; color: var(--g600);
            display: flex; align-items: center; justify-content: center;
        }
        .ni-title { font-size: 12px; font-weight: 900; color: var(--s900); }
        .ni-meta  { font-size: 10px; font-weight: 700; color: var(--s500); margin-top: 3px; display: flex; gap: 5px; }
        .notif-scroll { max-height: 300px; overflow-y: auto; padding: 0 2px 2px; }
        .notif-scroll::-webkit-scrollbar { width: 4px; }
        .notif-scroll::-webkit-scrollbar-thumb { background: rgba(16,185,129,.2); border-radius: 99px; }

        /* ══ CARD BASE ══ */
        .bidan-card, .admin-card, .stat-card, .dashboard-card,
        .content-card, .table-card, .chart-card {
            border-radius: 20px;
            background: rgba(255,255,255,.82);
            border: 1px solid rgba(226,232,240,.75);
            box-shadow: 0 8px 24px rgba(15,23,42,.04);
            backdrop-filter: blur(8px);
        }

        /* ══ CONTENT REVEAL ══ */
        .reveal-item {
            opacity: 0; transform: translateY(12px);
            animation: revealIn .32s var(--ease) both;
        }
        @keyframes revealIn { to { opacity: 1; transform: translateY(0); } }

        /* ══ LOADER ══ */
        #pc-loader {
            position: fixed; inset: 0; z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            background: rgba(240,255,248,.82); backdrop-filter: blur(10px);
            opacity: 0; visibility: hidden; pointer-events: none;
            transition: opacity .2s ease, visibility .2s ease;
        }
        #pc-loader.show { opacity: 1; visibility: visible; pointer-events: auto; }
        .ld-panel {
            padding: 28px 36px; border-radius: 22px;
            background: rgba(255,255,255,.96); border: 1px solid rgba(16,185,129,.12);
            box-shadow: 0 20px 50px rgba(15,23,42,.12);
            display: flex; flex-direction: column; align-items: center; gap: 14px;
            transform: scale(.94); transition: transform .22s var(--ease);
        }
        #pc-loader.show .ld-panel { transform: scale(1); }
        .ld-orbit { width: 58px; height: 58px; position: relative; display: flex; align-items: center; justify-content: center; }
        .ld-ring { position: absolute; inset: 0; border-radius: 50%; border: 2px solid transparent; }
        .ld-ring:nth-child(1) { border-top-color: var(--g500); border-right-color: rgba(16,185,129,.2); animation: spin .8s linear infinite; }
        .ld-ring:nth-child(2) { inset: 8px; border-bottom-color: var(--g400); animation: spin 1.2s linear infinite reverse; }
        .ld-ring:nth-child(3) { inset: 16px; border-top-color: var(--amber); animation: spin 1.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .ld-heart { font-size: 16px; color: var(--g600); position: relative; z-index: 2; animation: hbeat 1.1s ease-in-out infinite; }
        @keyframes hbeat { 0%,100%{transform:scale(1)} 40%{transform:scale(1.18)} 80%{transform:scale(1.08)} }
        .ld-label { font-family: 'Poppins',sans-serif; font-size: 13px; font-weight: 900; color: var(--s900); }
        .ld-dots { display: flex; gap: 5px; }
        .ld-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--g400); animation: dotpop .72s ease-in-out infinite both; }
        .ld-dot:nth-child(2){animation-delay:.12s;background:var(--g500)}
        .ld-dot:nth-child(3){animation-delay:.24s;background:var(--g600)}
        .ld-dot:nth-child(4){animation-delay:.36s;background:var(--amber)}
        @keyframes dotpop { 0%,80%,100%{transform:scale(.5);opacity:.3} 40%{transform:scale(1.1);opacity:1} }

        /* ══ MOBILE BOTTOM NAV ══ */
        .bottom-nav {
            display: none; position: fixed; left: 10px; right: 10px; bottom: 8px;
            z-index: 55; height: 64px; padding: 6px 8px;
            border-radius: 24px; align-items: center; justify-content: space-around;
            background: rgba(255,255,255,.88); border: 1px solid rgba(226,232,240,.85);
            backdrop-filter: blur(14px); box-shadow: 0 14px 36px rgba(15,23,42,.12);
        }
        .bn-link {
            flex: 1; height: 100%; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 3px;
            color: var(--s500); font-size: 9px; font-weight: 900; text-decoration: none;
            border-radius: 16px; transition: color .15s, background .15s;
        }
        .bn-link i { font-size: 15px; }
        .bn-link.active, .bn-link:hover { color: var(--g600); background: #ecfdf5; }
        .bn-center { position: relative; flex: 1; display: flex; justify-content: center; align-items: center; height: 100%; }
        .bn-fab {
            position: absolute; top: -18px;
            width: 54px; height: 54px; border-radius: 50%;
            background: linear-gradient(135deg, var(--g600), var(--g400));
            color: #fff; display: flex; align-items: center; justify-content: center;
            border: 4px solid rgba(255,255,255,.92);
            box-shadow: 0 10px 24px rgba(16,185,129,.3); transition: transform .15s;
            text-decoration: none;
        }
        .bn-fab:hover { transform: translateY(-2px); }
        .bn-fab i { font-size: 19px; }
        .bn-alert { position: absolute; top: -1px; right: -1px; width: 11px; height: 11px; border-radius: 50%; background: var(--rose); border: 2px solid #fff; }

        /* ══ RESPONSIVE ══ */
        @media (max-width: 1023px) {
            /* Mobile: sidebar keluar dari flow, fixed */
            #sidebar-slot {
                position: fixed; left: 0; top: 0; height: 100dvh;
                padding: 10px;
                animation: none;
                /* default tersembunyi */
                transform: translateX(calc(-1 * var(--sb-w) - 10px));
                transition: transform .28s var(--ease);
            }
            /* Terbuka di mobile */
            body.sb-open #sidebar-slot {
                transform: translateX(0);
            }
            body.sb-open #overlay { display: block; opacity: 1; }

            .bottom-nav { display: flex; }
            .tb-chip { display: none; }
            #tb-name-block, #profile-chevron { display: none; }
            .tb-profile { padding-right: 3px; }
            .tb-breadcrumb { display: none; }
            .tb-title { font-size: 15px; }
            #topbar { margin: 10px 10px 0; height: 64px; border-radius: 18px; }
            .page-inner { padding: 18px 14px 90px; }
        }

        @media (max-width: 640px) {
            :root { --sb-w: 260px; }
            .notif-menu { right: -60px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 1ms !important; transition-duration: 1ms !important; }
        }
    </style>

    @stack('styles')
</head>
<body>

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Schema;

    $route        = request()->route()?->getName() ?? '';
    $bidanName    = Auth::user()->name ?? 'Bidan';
    $bidanInitial = strtoupper(substr($bidanName, 0, 1));

    $safeRoute = fn($name, $fb = '#') => Route::has($name) ? route($name) : $fb;

    $notifCount    = 0;
    $pendingNotifs = collect();
    try {
        if (class_exists('\App\Models\Pemeriksaan') && Schema::hasTable('pemeriksaans') && Schema::hasColumn('pemeriksaans','status_verifikasi')) {
            $q = \App\Models\Pemeriksaan::where('status_verifikasi','pending');
            $notifCount    = (clone $q)->count();
            $pendingNotifs = $q->latest()->take(5)->get();
        }
    } catch (\Throwable) {}

    $dashboardUrl   = $safeRoute('bidan.dashboard');
    $profileUrl     = $safeRoute('bidan.profile.index');
    $rekamUrl       = $safeRoute('bidan.rekam-medis.index');
    $pemeriksaanUrl = $safeRoute('bidan.pemeriksaan.index');
    $imunisasiUrl   = $safeRoute('bidan.imunisasi.index');
    $jadwalUrl      = $safeRoute('bidan.jadwal.index');
@endphp

{{-- LOADER --}}
<div id="pc-loader" role="status" aria-live="polite" aria-label="Memuat halaman">
    <div class="ld-panel">
        <div class="ld-orbit">
            <div class="ld-ring"></div><div class="ld-ring"></div><div class="ld-ring"></div>
            <i class="fa-solid fa-heart-pulse ld-heart"></i>
        </div>
        <div class="ld-label" id="ld-label">Memuat Halaman</div>
        <div class="ld-dots">
            <span class="ld-dot"></span><span class="ld-dot"></span>
            <span class="ld-dot"></span><span class="ld-dot"></span>
        </div>
    </div>
</div>

{{-- OVERLAY mobile --}}
<div id="overlay" aria-hidden="true"></div>

<div id="shell">

    {{-- SIDEBAR SLOT: hanya wrapper posisi, tampilan di partial --}}
    <aside id="sidebar-slot" aria-label="Sidebar Navigasi Bidan">
        @include('partials.sidebar.bidan')
    </aside>

    {{-- MAIN AREA --}}
    <div id="main-area">

        {{-- TOPBAR --}}
        <header id="topbar">
            <div class="tb-left">
                <button id="sidebar-toggle" class="tb-toggle" aria-label="Toggle sidebar" aria-expanded="true">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <div>
                    <div class="tb-breadcrumb">
                        <a href="{{ $dashboardUrl }}"><i class="fa-solid fa-house"></i></a>
                        <i class="fa-solid fa-chevron-right" style="font-size:8px;opacity:.45"></i>
                        <span>@yield('page-name', 'Workspace')</span>
                    </div>
                    <h2 class="tb-title">@yield('page-title', 'Bidan Workspace')</h2>
                </div>
            </div>

            <div class="tb-right">
                <div class="tb-chip">
                    <i class="fa-solid fa-user-doctor" style="color:var(--g600)"></i>
                    Bidan Workspace
                </div>

                {{-- Notifikasi --}}
                <div class="dropdown" id="notif-dd">
                    <button class="tb-icon-btn" id="notif-btn" aria-label="Notifikasi" aria-expanded="false">
                        <i class="fa-regular fa-bell" style="font-size:17px"></i>
                        @if($notifCount > 0)<span class="notif-dot"></span>@endif
                    </button>
                    <div class="dropdown-menu notif-menu">
                        <div class="dm-head">
                            <div class="tb-avatar" style="width:36px;height:36px;font-size:12px">
                                <i class="fa-solid fa-notes-medical"></i>
                            </div>
                            <div>
                                <div class="dm-title">Antrian Medis</div>
                                <div class="dm-sub">{{ $notifCount }} Menunggu</div>
                            </div>
                        </div>
                        <div class="notif-scroll">
                            @forelse($pendingNotifs as $notif)
                                @php
                                    $namaPasien = $notif->nama_pasien ?? 'Pasien #' . $notif->id;
                                    $targetUrl  = Route::has('bidan.pemeriksaan.show') ? route('bidan.pemeriksaan.show', $notif->id) : $pemeriksaanUrl;
                                @endphp
                                <a href="{{ $targetUrl }}" class="notif-item">
                                    <div class="ni-icon"><i class="fa-solid fa-stethoscope"></i></div>
                                    <div>
                                        <div class="ni-title">{{ $namaPasien }}</div>
                                        <div class="ni-meta">
                                            <span style="color:var(--g600);text-transform:uppercase">{{ $notif->kategori_pasien ?? 'pasien' }}</span>
                                            <span>•</span>
                                            <span>{{ optional($notif->created_at)->diffForHumans() ?? '-' }}</span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div style="padding:28px;text-align:center;color:var(--s500);font-size:12px;font-weight:700">
                                    <i class="fa-regular fa-circle-check" style="font-size:22px;color:var(--g500);display:block;margin-bottom:8px"></i>
                                    Belum ada antrian pemeriksaan.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Profil --}}
                <div class="dropdown" id="profile-dd">
                    <button class="tb-profile" id="profile-btn" aria-expanded="false">
                        <div class="tb-avatar">{{ $bidanInitial }}</div>
                        <div id="tb-name-block">
                            <div class="tb-name">{{ $bidanName }}</div>
                            <div class="tb-role">Tenaga Bidan</div>
                        </div>
                        <i class="fa-solid fa-chevron-down" id="profile-chevron" style="font-size:10px;color:var(--s500)"></i>
                    </button>
                    <div class="dropdown-menu">
                        <div class="dm-head">
                            <div class="tb-avatar">{{ $bidanInitial }}</div>
                            <div>
                                <div class="dm-title">{{ $bidanName }}</div>
                                <div class="dm-sub">Tenaga Bidan</div>
                            </div>
                        </div>
                        @if(Route::has('bidan.profile.index'))
                        <a href="{{ $profileUrl }}" class="dm-link"><i class="fa-regular fa-user"></i> Profil Akun</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" id="lf-dd">
                            @csrf
                            <button type="submit" class="dm-link danger"><i class="fa-solid fa-right-from-bracket"></i> Keluar Aplikasi</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- KONTEN: satu-satunya yang scroll --}}
        <div id="page-content">
            <div class="page-inner">
                @yield('content')
            </div>
        </div>

    </div>{{-- /main-area --}}
</div>{{-- /shell --}}

{{-- MOBILE BOTTOM NAV --}}
<nav class="bottom-nav" aria-label="Navigasi bawah">
    <a href="{{ $dashboardUrl }}" class="bn-link {{ $route === 'bidan.dashboard' ? 'active' : '' }}">
        <i class="fa-solid fa-chart-pie"></i>Beranda
    </a>
    <a href="{{ $rekamUrl }}" class="bn-link {{ Str::startsWith($route, 'bidan.rekam-medis') ? 'active' : '' }}">
        <i class="fa-solid fa-folder-open"></i>EMR
    </a>
    <div class="bn-center">
        <a href="{{ $pemeriksaanUrl }}" class="bn-fab" aria-label="Pemeriksaan">
            <i class="fa-solid fa-stethoscope"></i>
            @if($notifCount > 0)<span class="bn-alert"></span>@endif
        </a>
    </div>
    <a href="{{ $imunisasiUrl }}" class="bn-link {{ Str::startsWith($route, 'bidan.imunisasi') ? 'active' : '' }}">
        <i class="fa-solid fa-syringe"></i>Vaksin
    </a>
    <a href="{{ $jadwalUrl }}" class="bn-link {{ Str::startsWith($route, 'bidan.jadwal') ? 'active' : '' }}">
        <i class="fa-solid fa-calendar-days"></i>Jadwal
    </a>
</nav>

<script>
(function () {
    'use strict';

    const body    = document.body;
    const overlay = document.getElementById('overlay');
    const toggle  = document.getElementById('sidebar-toggle');
    const loader  = document.getElementById('pc-loader');
    const ldLabel = document.getElementById('ld-label');
    const mq      = window.matchMedia('(min-width: 1024px)');

    const isDesktop = () => mq.matches;
    const getSaved  = () => { try { return localStorage.getItem('pc_sb'); } catch { return null; } };
    const save      = v  => { try { localStorage.setItem('pc_sb', v); } catch {} };

    /* ── Sidebar toggle ──────────────────────────────
       Desktop: body.sb-closed  → sidebar tersembunyi (default: terbuka)
       Mobile:  body.sb-open    → sidebar muncul     (default: tersembunyi)
    ─────────────────────────────────────────────── */
    function setSidebar(open, persist) {
        if (isDesktop()) {
            body.classList.remove('sb-open');           // bersihkan class mobile
            body.classList.toggle('sb-closed', !open);  // desktop pakai sb-closed
            overlay.style.display = 'none';
        } else {
            body.classList.remove('sb-closed');         // bersihkan class desktop
            body.classList.toggle('sb-open', open);     // mobile pakai sb-open
        }
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (persist && isDesktop()) save(open ? '1' : '0');
    }

    function initSidebar() {
        if (isDesktop()) {
            // Desktop: buka by default kecuali user pernah menutup
            setSidebar(getSaved() !== '0', false);
        } else {
            // Mobile: selalu mulai tertutup
            setSidebar(false, false);
        }
    }

    initSidebar();
    mq.addEventListener('change', initSidebar);

    // Hamburger: baca state sekarang lalu balik
    toggle?.addEventListener('click', function () {
        if (isDesktop()) {
            // Desktop: jika sb-closed ada → sedang tutup → buka; sebaliknya tutup
            setSidebar(body.classList.contains('sb-closed'), true);
        } else {
            // Mobile: jika sb-open ada → sedang buka → tutup; sebaliknya buka
            setSidebar(!body.classList.contains('sb-open'), false);
        }
    });

    overlay.addEventListener('click', () => setSidebar(false, false));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') setSidebar(false, isDesktop()); });

    /* ── Dropdowns ── */
    function closeDropdowns() {
        document.querySelectorAll('.dropdown.open').forEach(d => {
            d.classList.remove('open');
            d.querySelector('[aria-expanded]')?.setAttribute('aria-expanded', 'false');
        });
    }
    document.querySelectorAll('.dropdown').forEach(dd => {
        dd.querySelector('[aria-expanded]')?.addEventListener('click', e => {
            e.stopPropagation();
            const was = dd.classList.contains('open');
            closeDropdowns();
            if (!was) {
                dd.classList.add('open');
                dd.querySelector('[aria-expanded]')?.setAttribute('aria-expanded', 'true');
            }
        });
    });
    document.addEventListener('click', closeDropdowns);

    /* ── Loader ── */
    function showLoader(label) {
        if (ldLabel && label) ldLabel.textContent = label;
        loader?.classList.add('show');
    }
    function hideLoader() { loader?.classList.remove('show'); }

    let navTimer;
    const startNav = label => { clearTimeout(navTimer); navTimer = setTimeout(() => showLoader(label || 'Memuat Halaman'), 90); };
    const stopNav  = ()    => { clearTimeout(navTimer); hideLoader(); };

    /* ── Nav links ── */
    document.querySelectorAll('a[href]').forEach(a => {
        const href = a.getAttribute('href') || '';
        if (a.target === '_blank' || /^(#|javascript:|mailto:|tel:)/.test(href) || a.hasAttribute('download')) return;
        a.addEventListener('click', e => {
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.defaultPrevented) return;
            try { if (new URL(href, location.origin).origin !== location.origin) return; } catch {}
            if (!isDesktop()) setSidebar(false, false);
            closeDropdowns();
            startNav('Memuat Halaman');
        });
    });

    /* ── Logout confirm ── */
    document.querySelectorAll('form[action*="logout"]').forEach(f => {
        f.addEventListener('submit', function (e) {
            if (this.dataset.ok) { startNav('Keluar Sistem'); return; }
            e.preventDefault();
            const go = () => { this.dataset.ok = '1'; startNav('Keluar Sistem'); this.submit(); };
            if (window.Swal) {
                Swal.fire({
                    title: 'Keluar dari sistem?', text: 'Sesi bidan akan diakhiri.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#059669', cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Logout', cancelButtonText: 'Batal',
                    reverseButtons: true, background: '#fff', color: '#0f172a',
                    customClass: { popup: 'rounded-3xl', confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
                }).then(r => r.isConfirmed && go());
            } else { confirm('Keluar?') && go(); }
        });
    });

    document.querySelectorAll('form:not([action*="logout"])').forEach(f => {
        f.addEventListener('submit', () => startNav('Memproses Data'));
    });

    window.addEventListener('pageshow', stopNav);
    setTimeout(stopNav, 1500);

    /* ── Content reveal ── */
    const pc = document.getElementById('page-content');
    if (pc) {
        const els = pc.querySelectorAll(
            '.stat-card,.dashboard-card,.admin-card,.bidan-card,.content-card,.table-card,.chart-card,section,article'
        );
        (els.length ? els : Array.from(pc.firstElementChild?.children || [])).forEach((el, i) => {
            if (i >= 10) return;
            el.classList.add('reveal-item');
            el.style.animationDelay = (i * 45 + 100) + 'ms';
        });
    }

    /* ── Flash messages ── */
    @if(session('success'))
    if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 2400, timerProgressBar: true, background: '#fff', color: '#0f172a' });
    @endif
    @if(session('error'))
    if (window.Swal) Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: @json(session('error')), confirmButtonColor: '#059669', background: '#fff', color: '#0f172a' });
    @endif

})();
</script>

@stack('scripts')
</body>
</html>