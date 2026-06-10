<!DOCTYPE html>
<html lang="id" style="background-color: #f8fafc;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bidan Workspace') | PosyanduCare</title>

    <script>
        // Mencegah kedip / FOUC pada sidebar saat reload
        try {
            if (localStorage.getItem('pc_bidan_sidebar') !== '0' && matchMedia('(min-width:1024px)').matches) {
                document.documentElement.classList.add('sb-open');
            }
        } catch (e) {}
    </script>

    <link rel="icon" type="image/webp" href="{{ asset('img/logo.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <style>
        :root {
            --sb-w: 280px;
            --emerald: #059669;
            --emerald-500: #10b981;
            --emerald-400: #34d399;
            --emerald-50: #ecfdf5;
            --teal: #0d9488;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --rose: #f43f5e;
            --speed: 300ms;
            --ease: cubic-bezier(0.16, 1, 0.3, 1); /* Lebih snappy dan premium */
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        
        /* Memastikan background konsisten untuk menghindari flash putih */
        html { height: 100%; background-color: var(--slate-50); }
        body {
            margin: 0; height: 100%; overflow: hidden;
            font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            color: var(--slate-700);
            background-color: var(--slate-50);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        h1, h2, h3, h4, h5, h6 { font-weight: 700; color: var(--slate-900); letter-spacing: -0.02em; }
        a { text-decoration: none; color: inherit; }
        button, input, select, textarea { font-family: inherit; }

        #layout-shell { height: 100dvh; display: flex; overflow: hidden; }

        /* --- SIDEBAR --- */
        .bidan-sidebar {
            position: fixed; inset: 0 auto 0 0; z-index: 80;
            width: var(--sb-w); height: 100dvh; padding: 20px 16px;
            background: transparent;
            transform: translate3d(calc(-100% - 12px), 0, 0);
            transition: transform var(--speed) var(--ease);
            will-change: transform;
            contain: layout paint style;
        }
        html.sb-open .bidan-sidebar { transform: translate3d(0, 0, 0); }

        .app-wrapper {
            flex: 1; height: 100dvh; display: flex; flex-direction: column; overflow: hidden;
            padding-left: 0;
            transition: padding-left var(--speed) var(--ease);
            will-change: padding-left;
            position: relative;
        }
        @media(min-width: 1024px) {
            html.sb-open .app-wrapper { padding-left: var(--sb-w); }
        }

        /* --- TOPBAR --- */
        .topbar-wrapper { padding: 16px 24px 0; flex-shrink: 0; position: relative; z-index: 50; }
        .bidan-topbar {
            min-height: 64px; padding: 8px 16px; border-radius: 16px;
            display: flex; align-items: center; gap: 16px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(226, 232, 240, 0.6);
            box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.04), 0 1px 3px -1px rgba(15, 23, 42, 0.02);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .sidebar-toggle {
            width: 40px; height: 40px; border-radius: 12px;
            border: 1px solid transparent; background: transparent;
            color: var(--slate-500); cursor: pointer; flex-shrink: 0;
            display: grid; place-items: center; font-size: 1.1rem;
            transition: all 0.2s ease;
        }
        .sidebar-toggle:hover { color: var(--emerald); background: var(--slate-100); }
        .sidebar-toggle:active { transform: scale(0.92); }

        .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 12px; }
        
        .workspace-chip {
            height: 38px; padding: 0 14px; border-radius: 10px;
            background: var(--emerald-50);
            color: var(--emerald); font-size: 12px; font-weight: 700;
            display: flex; align-items: center; gap: 8px; white-space: nowrap;
            letter-spacing: 0.02em;
        }

        .topbar-icon {
            width: 40px; height: 40px; border-radius: 12px;
            border: 1px solid var(--slate-200); background: #fff;
            color: var(--slate-500); cursor: pointer; position: relative;
            display: grid; place-items: center;
            transition: all 0.2s ease;
        }
        .topbar-icon:hover { background: var(--slate-50); color: var(--emerald); border-color: var(--slate-300); }
        
        .notif-dot {
            position: absolute; top: 8px; right: 8px; width: 8px; height: 8px;
            border-radius: 50%; background: var(--rose);
            box-shadow: 0 0 0 2px #fff;
        }

        .profile-button {
            height: 40px; padding: 2px 12px 2px 2px; border-radius: 12px;
            border: 1px solid var(--slate-200); background: #fff;
            cursor: pointer; display: flex; align-items: center; gap: 10px;
            transition: all 0.2s ease;
        }
        .profile-button:hover { background: var(--slate-50); }

        .avatar {
            width: 34px; height: 34px; border-radius: 9px;
            background: linear-gradient(135deg, var(--emerald-400), var(--emerald));
            color: #fff; display: grid; place-items: center;
            font-size: 13px; font-weight: 700; overflow: hidden;
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        
        #profile-text { text-align: left; }
        .profile-name {
            font-size: 13px; font-weight: 700; color: var(--slate-800);
            line-height: 1.2; max-width: 140px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .profile-role { font-size: 11px; font-weight: 500; color: var(--slate-500); }

        /* --- DROPDOWNS --- */
        .dropdown { position: relative; }
        .dropdown-menu {
            position: absolute; right: 0; top: calc(100% + 12px); z-index: 100;
            width: 260px; padding: 8px; border-radius: 16px;
            background: #ffffff; border: 1px solid var(--slate-200);
            box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.1);
            opacity: 0; visibility: hidden; pointer-events: none;
            transform: translateY(10px); transform-origin: top right;
            transition: all 0.2s var(--ease);
        }
        .dropdown.open .dropdown-menu {
            opacity: 1; visibility: visible; pointer-events: auto; transform: translateY(0);
        }
        
        .notif-menu { width: 340px; max-width: calc(100vw - 32px); }
        .dropdown-head {
            padding: 12px; margin-bottom: 8px; border-bottom: 1px solid var(--slate-100);
            display: flex; align-items: center; gap: 12px;
        }
        .dropdown-title { font-size: 14px; font-weight: 700; color: var(--slate-900); }
        .dropdown-sub { font-size: 12px; color: var(--slate-500); margin-top: 2px;}
        
        .dropdown-link, .dropdown-logout {
            width: 100%; border: 0; border-radius: 10px; padding: 10px 12px;
            background: transparent; color: var(--slate-600); cursor: pointer;
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 600;
            transition: all 0.15s ease;
        }
        .dropdown-link:hover { background: var(--slate-50); color: var(--emerald); }
        .dropdown-logout { color: var(--rose); }
        .dropdown-logout:hover { background: #fff1f2; color: #e11d48; }

        .notif-scroll { max-height: 320px; overflow-y: auto; padding-right: 4px; }
        .notif-scroll::-webkit-scrollbar { width: 4px; }
        .notif-scroll::-webkit-scrollbar-thumb { background: var(--slate-200); border-radius: 4px; }
        .notif-item {
            display: flex; align-items: flex-start; gap: 12px; padding: 12px;
            border-radius: 10px; color: var(--slate-700);
            transition: background 0.15s ease;
        }
        .notif-item:hover { background: var(--slate-50); }
        .notif-icon {
            width: 36px; height: 36px; flex-shrink: 0; border-radius: 10px;
            background: var(--emerald-50); color: var(--emerald);
            display: grid; place-items: center; font-size: 14px;
        }
        .notif-title { font-size: 13px; font-weight: 600; color: var(--slate-800); }
        .notif-meta { margin-top: 4px; display: flex; gap: 6px; font-size: 11px; color: var(--slate-500); }

        /* --- MAIN CONTENT & LOADER (Penting untuk efek SPA) --- */
        .main-scroll {
            flex: 1; overflow-y: auto; overflow-x: hidden;
            padding: 24px; position: relative; /* Menahan loader agar hanya di area ini */
            scrollbar-width: thin; scrollbar-color: var(--slate-300) transparent;
        }
        .main-scroll::-webkit-scrollbar { width: 6px; }
        .main-scroll::-webkit-scrollbar-track { background: transparent; }
        .main-scroll::-webkit-scrollbar-thumb { background: var(--slate-300); border-radius: 10px; }
        .main-inner { max-width: 1440px; margin: 0 auto; min-height: 100%; }

        /* Efek Animasi Transisi Halaman (Ilusi SPA) */
        .page-enter {
            animation: contentFadeIn 0.45s var(--ease) forwards;
        }
        @keyframes contentFadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Loader: Sekarang hanya menutupi konten utama, topbar/sidebar aman */
        #pc-loader {
            position: absolute; inset: 0; z-index: 60;
            display: flex; align-items: center; justify-content: center;
            background: rgba(248, 250, 252, 0.7); /* Transparan lembut */
            backdrop-filter: blur(4px);
            opacity: 0; visibility: hidden; pointer-events: none;
            transition: all 0.25s ease;
        }
        #pc-loader.show { opacity: 1; visibility: visible; pointer-events: auto; }
        
        .loader-panel {
            padding: 24px 32px; border-radius: 20px;
            background: #ffffff; border: 1px solid var(--slate-100);
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.08);
            display: flex; flex-direction: column; align-items: center; gap: 16px;
            transform: scale(0.95); transition: transform 0.3s var(--ease);
        }
        #pc-loader.show .loader-panel { transform: scale(1); }

        .loader-orbit { width: 50px; height: 50px; position: relative; display: grid; place-items: center; }
        .loader-ring { position: absolute; inset: 0; border-radius: 50%; border: 3px solid transparent; }
        .loader-ring:nth-child(1) { border-top-color: var(--emerald-500); animation: spin 0.8s ease infinite; }
        .loader-ring:nth-child(2) { inset: 8px; border-bottom-color: var(--teal); animation: spin 1s ease infinite reverse; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loader-heart { font-size: 16px; color: var(--emerald); z-index: 2; }
        .loader-label { font-size: 13px; font-weight: 700; color: var(--slate-800); }

        /* --- PREMIUM SWEETALERT2 --- */
        .swal2-container.pc-backdrop {
            background: rgba(15, 23, 42, 0.4) !important; 
            backdrop-filter: blur(6px) !important; 
        }
        .pc-popup {
            border-radius: 24px !important; 
            padding: 2.5rem 2rem 2rem !important;
            background: #ffffff !important; 
            border: 1px solid var(--slate-100) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
        }
        .swal2-icon.pc-icon {
            border-color: var(--rose) !important;
            color: var(--rose) !important;
            margin-top: 0 !important;
        }
        .pc-title {
            font-family: "Plus Jakarta Sans", sans-serif !important;
            font-size: 1.35rem !important; 
            font-weight: 800 !important;
            color: var(--slate-900) !important; 
            margin-bottom: 0.5rem !important;
        }
        .pc-html { 
            font-family: "Plus Jakarta Sans", sans-serif !important;
            font-size: 0.95rem !important; 
            font-weight: 500 !important; 
            color: var(--slate-500) !important; 
        }
        .pc-actions { 
            gap: 12px !important; 
            margin-top: 1.75rem !important; 
            width: 100% !important; 
        }
        .pc-confirm, .pc-cancel {
            border: 0 !important; 
            border-radius: 12px !important;
            padding: 12px 24px !important; 
            font-size: 0.9rem !important; 
            font-weight: 700 !important;
            transition: all 0.2s ease !important;
            flex: 1 !important; /* Membuat tombol sama besar */
            margin: 0 !important;
        }
        .pc-confirm { 
            background: linear-gradient(135deg, var(--emerald), var(--emerald-500)) !important; 
            color: #ffffff !important; 
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25) !important;
        }
        .pc-confirm:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35) !important; }
        .pc-cancel { 
            background: var(--slate-100) !important; 
            color: var(--slate-700) !important; 
        }
        .pc-cancel:hover { background: var(--slate-200) !important; }

        /* Mobile Adjustments */
        .mobile-overlay {
            position: fixed; inset: 0; z-index: 70; border: 0;
            background: rgba(15, 23, 42, 0.3); backdrop-filter: blur(2px);
            opacity: 0; visibility: hidden; pointer-events: none;
            transition: all var(--speed) var(--ease);
        }
        html.sb-open .mobile-overlay { opacity: 1; visibility: visible; pointer-events: auto; }
        
        .bottom-nav {
            display: none; position: fixed; left: 16px; right: 16px; bottom: 16px; z-index: 60;
            height: 64px; padding: 6px; border-radius: 20px;
            align-items: center; justify-content: space-around;
            background: rgba(255, 255, 255, 0.95); border: 1px solid var(--slate-200);
            backdrop-filter: blur(10px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
        .bn-link {
            flex: 1; height: 100%; border-radius: 14px;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
            color: var(--slate-400); font-size: 10px; font-weight: 700; transition: all 0.2s ease;
        }
        .bn-link i { font-size: 18px; }
        .bn-link.active, .bn-link:hover { color: var(--emerald); background: var(--slate-50); }
        .bn-center { flex: 1; height: 100%; position: relative; display: flex; align-items: center; justify-content: center; }
        .bn-fab {
            position: absolute; top: -24px; width: 56px; height: 56px; border-radius: 16px;
            background: linear-gradient(135deg, var(--emerald), var(--emerald-500)); 
            color: #fff; display: grid; place-items: center; font-size: 20px;
            border: 4px solid var(--slate-50); box-shadow: 0 8px 16px rgba(16, 185, 129, 0.25);
        }

        @media(min-width: 1024px) { .mobile-overlay { display: none !important; } }
        @media(max-width: 1023px) {
            :root { --sb-w: 280px; }
            .app-wrapper { padding-left: 0 !important; }
            .topbar-wrapper { padding: 16px 16px 0; }
            .bidan-topbar { min-height: 56px; border-radius: 12px; }
            .workspace-chip { display: none; }
            #profile-text, #profile-chevron { display: none; }
            .profile-button { padding-right: 2px; }
            .main-scroll { padding: 20px 16px 100px; }
            .bottom-nav { display: flex; }
            .notif-menu { right: -60px; }
        }
        @media(max-width: 640px) { :root { --sb-w: 260px; } }
    </style>

    @stack('styles')
</head>

<body>
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Schema;

    $route = request()->route()?->getName() ?? '';
    $user = auth()->user();
    $bidanName = $user->name ?? $user->nama ?? 'Bidan';
    $bidanInitial = Str::upper(Str::substr($bidanName, 0, 1));
    $bidanPhoto = $user->foto ?? null;
    $safeRoute = fn ($name, $fallback = '#') => Route::has($name) ? route($name) : $fallback;

    $notifCount = 0;
    $pendingNotifs = collect();

    try {
        if (class_exists('\App\Models\Pemeriksaan') && Schema::hasTable('pemeriksaans') && Schema::hasColumn('pemeriksaans', 'status_verifikasi')) {
            $query = \App\Models\Pemeriksaan::where('status_verifikasi', 'pending');
            $notifCount = (clone $query)->count();
            $pendingNotifs = Schema::hasColumn('pemeriksaans', 'created_at')
                ? (clone $query)->latest()->take(5)->get()
                : (clone $query)->orderByDesc('id')->take(5)->get();
        }
    } catch (\Throwable $e) {
        $notifCount = 0;
        $pendingNotifs = collect();
    }

    $dashboardUrl = $safeRoute('bidan.dashboard');
    $profileUrl = $safeRoute('bidan.profile.index');
    $rekamUrl = $safeRoute('bidan.rekam-medis.index');
    $pemeriksaanUrl = $safeRoute('bidan.pemeriksaan.index');
    $imunisasiUrl = $safeRoute('bidan.imunisasi.index');
    $jadwalUrl = $safeRoute('bidan.jadwal.index');
@endphp

<button type="button" class="mobile-overlay" aria-label="Tutup sidebar" onclick="setSidebar(false)"></button>

<div id="layout-shell">
    <aside class="bidan-sidebar" aria-label="Sidebar Navigasi Bidan">
        @include('partials.sidebar.bidan')
    </aside>

    <div class="app-wrapper">
        <!-- TOPBAR: Tetap statis dan aman di atas -->
        <div class="topbar-wrapper">
            <header class="bidan-topbar">
                <button type="button" id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>

                <div class="topbar-right">
                    <div class="workspace-chip">
                        <i class="fa-solid fa-user-doctor"></i>
                        Bidan Workspace
                    </div>

                    <div class="dropdown">
                        <button type="button" class="topbar-icon" data-dropdown-button aria-label="Notifikasi">
                            <i class="fa-regular fa-bell" style="font-size:18px"></i>
                            @if($notifCount > 0)
                                <span class="notif-dot"></span>
                            @endif
                        </button>

                        <div class="dropdown-menu notif-menu">
                            <div class="dropdown-head">
                                <div class="avatar" style="width:40px;height:40px;font-size:14px">
                                    <i class="fa-solid fa-notes-medical"></i>
                                </div>
                                <div>
                                    <div class="dropdown-title">Antrian Medis</div>
                                    <div class="dropdown-sub">{{ $notifCount }} pasien menunggu</div>
                                </div>
                            </div>

                            <div class="notif-scroll">
                                @forelse($pendingNotifs as $notif)
                                    @php
                                        $namaPasien = $notif->nama_pasien ?? 'Pasien #' . $notif->id;
                                        $targetUrl = Route::has('bidan.pemeriksaan.show')
                                            ? route('bidan.pemeriksaan.show', $notif->id)
                                            : $pemeriksaanUrl;
                                    @endphp

                                    <a href="{{ $targetUrl }}" class="notif-item app-link">
                                        <div class="notif-icon"><i class="fa-solid fa-stethoscope"></i></div>
                                        <div>
                                            <div class="notif-title">{{ $namaPasien }}</div>
                                            <div class="notif-meta">
                                                <span style="font-weight:700; color:var(--emerald); text-transform:capitalize">
                                                    {{ $notif->kategori_pasien ?? 'Pasien' }}
                                                </span>
                                                <span>•</span>
                                                <span>{{ optional($notif->created_at)->diffForHumans() ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div style="padding:32px 16px; text-align:center; color:var(--slate-500); font-size:13px;">
                                        <i class="fa-regular fa-circle-check" style="font-size:28px; color:var(--slate-300); display:block; margin-bottom:12px"></i>
                                        Tidak ada antrian saat ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button type="button" class="profile-button" data-dropdown-button aria-label="Menu profil">
                            <div class="avatar">
                                @if($bidanPhoto)
                                    <img src="{{ asset('storage/' . $bidanPhoto) }}" alt="Foto Bidan">
                                @else
                                    {{ $bidanInitial }}
                                @endif
                            </div>
                            <div id="profile-text">
                                <div class="profile-name">{{ $bidanName }}</div>
                                <div class="profile-role">Tenaga Bidan</div>
                            </div>
                            <i id="profile-chevron" class="fa-solid fa-chevron-down" style="font-size:10px; color:var(--slate-400); margin: 0 4px;"></i>
                        </button>

                        <div class="dropdown-menu">
                            <div class="dropdown-head">
                                <div class="avatar" style="width:40px;height:40px;font-size:14px">{{ $bidanInitial }}</div>
                                <div>
                                    <div class="dropdown-title">{{ $bidanName }}</div>
                                    <div class="dropdown-sub">Tenaga Bidan Aktif</div>
                                </div>
                            </div>

                            @if(Route::has('bidan.profile.index'))
                                <a href="{{ $profileUrl }}" class="dropdown-link app-link">
                                    <i class="fa-regular fa-user"></i>
                                    Profil Akun
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="logout-form m-0" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-logout">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
        </div>

        <!-- MAIN SCROLL: Area ini yang akan memuat transisi dan loader -->
        <div class="main-scroll relative">
            <!-- Loader sekarang DIBATASI HANYA di area konten -->
            <div id="pc-loader" role="status" aria-live="polite">
                <div class="loader-panel">
                    <div class="loader-orbit">
                        <div class="loader-ring"></div>
                        <div class="loader-ring"></div>
                        <i class="fa-solid fa-heart-pulse loader-heart"></i>
                    </div>
                    <div class="loader-label" id="loader-label">Memproses data...</div>
                </div>
            </div>

            <!-- Konten memiliki animasi page-enter (SPA Illusion) -->
            <main class="main-inner page-enter">
                @yield('content')
            </main>
        </div>
    </div>
</div>

<nav class="bottom-nav" aria-label="Navigasi bawah">
    <a href="{{ $dashboardUrl }}" class="bn-link app-link {{ $route === 'bidan.dashboard' ? 'active' : '' }}">
        <i class="fa-solid fa-house"></i>
        Beranda
    </a>
    <a href="{{ $rekamUrl }}" class="bn-link app-link {{ Str::startsWith($route, 'bidan.rekam-medis') ? 'active' : '' }}">
        <i class="fa-solid fa-folder-open"></i>
        EMR
    </a>
    <div class="bn-center">
        <a href="{{ $pemeriksaanUrl }}" class="bn-fab app-link" aria-label="Pemeriksaan">
            <i class="fa-solid fa-stethoscope"></i>
            @if($notifCount > 0)
                <span class="bn-alert"></span>
            @endif
        </a>
    </div>
    <a href="{{ $imunisasiUrl }}" class="bn-link app-link {{ Str::startsWith($route, 'bidan.imunisasi') ? 'active' : '' }}">
        <i class="fa-solid fa-syringe"></i>
        Vaksin
    </a>
    <a href="{{ $jadwalUrl }}" class="bn-link app-link {{ Str::startsWith($route, 'bidan.jadwal') ? 'active' : '' }}">
        <i class="fa-solid fa-calendar"></i>
        Jadwal
    </a>
</nav>

@stack('modals')

<script>
(function () {
    'use strict';

    const root = document.documentElement;
    const toggle = document.getElementById('sidebar-toggle');
    const loader = document.getElementById('pc-loader');
    const loaderLabel = document.getElementById('loader-label');
    const isDesktop = () => matchMedia('(min-width:1024px)').matches;

    let timer = null;

    function saveSidebar(open) {
        try { localStorage.setItem('pc_bidan_sidebar', open ? '1' : '0'); } catch (e) {}
    }

    function setSidebar(open, save = true) {
        clearTimeout(timer);
        requestAnimationFrame(() => {
            root.classList.toggle('sb-open', open);
            if (save && isDesktop()) saveSidebar(open);
            if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            timer = setTimeout(() => root.classList.remove('sb-toggling'), 340);
        });
    }

    function toggleSidebar() {
        root.classList.add('sb-toggling');
        setSidebar(!root.classList.contains('sb-open'));
    }

    function closeDropdowns() {
        document.querySelectorAll('.dropdown.open').forEach((item) => item.classList.remove('open'));
    }

    function showLoader(text) {
        if (loaderLabel && text) loaderLabel.textContent = text;
        if (loader) loader.classList.add('show');
    }

    function hideLoader() {
        if (loader) loader.classList.remove('show');
    }

    // Konfigurasi SweetAlert2 Premium
    function confirmBox(options) {
        return Swal.fire({
            title: options.title || 'Konfirmasi',
            html: options.text || '',
            icon: options.icon || 'warning',
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: options.yes || 'Ya, Lanjutkan',
            cancelButtonText: options.no || 'Batal',
            reverseButtons: true,
            customClass: {
                container: 'pc-backdrop',
                popup: 'pc-popup',
                icon: 'pc-icon',
                title: 'pc-title',
                htmlContainer: 'pc-html',
                actions: 'pc-actions',
                confirmButton: 'pc-confirm',
                cancelButton: 'pc-cancel'
            }
        });
    }

    if (toggle) toggle.addEventListener('click', toggleSidebar);

    document.addEventListener('click', function (event) {
        const dropdownButton = event.target.closest('[data-dropdown-button]');

        if (dropdownButton) {
            event.stopPropagation();
            const parent = dropdownButton.closest('.dropdown');
            const isOpen = parent.classList.contains('open');
            closeDropdowns();
            if (!isOpen) parent.classList.add('open');
            return;
        }

        if (!event.target.closest('.dropdown')) closeDropdowns();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDropdowns();
            if (!isDesktop()) setSidebar(false, false);
        }
    });

    // Simulasi transisi mulus saat navigasi internal
    document.querySelectorAll('.app-link, .bidan-sidebar a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Jangan halangi link yang punya target blank atau aksi khusus
            if (this.target === '_blank' || e.ctrlKey || e.metaKey || this.getAttribute('onclick')) return;
            
            showLoader('Memuat data...');
        });
    });

    document.addEventListener('submit', function (event) {
        const form = event.target;

        if (form.dataset.confirmed === '1') {
            showLoader('Memproses...');
            return;
        }

        // Intersepsi Form Logout untuk memunculkan SweetAlert cantik
        if (form.classList.contains('logout-form') || (form.action && form.action.includes('logout'))) {
            event.preventDefault();

            const doLogout = () => {
                form.dataset.confirmed = '1';
                showLoader('Keluar Sistem...');
                form.submit();
            };

            if (window.Swal) {
                confirmBox({
                    title: 'Keluar dari sistem?',
                    text: 'Sesi bidan akan diakhiri dengan aman. Anda harus login kembali untuk masuk.',
                    icon: 'warning',
                    yes: 'Ya, Logout',
                    no: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) doLogout();
                });
            } else if (confirm('Keluar dari sistem?')) {
                doLogout();
            }
            return;
        }

        if (!form.dataset.noLoader) showLoader('Menyimpan Data...');
    });

    // Sembunyikan loader saat back/forward browser
    window.addEventListener('pageshow', hideLoader);

    @if(session('success'))
    document.addEventListener('DOMContentLoaded', function () {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: @json(session('success')),
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#fff',
                color: '#0f172a'
            });
        }
    });
    @endif

    @if(session('error'))
    document.addEventListener('DOMContentLoaded', function () {
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: @json(session('error')),
                confirmButtonColor: '#059669',
                background: '#fff',
                color: '#0f172a',
                customClass: { popup: 'pc-popup', title: 'pc-title' }
            });
        }
    });
    @endif

    window.setSidebar = setSidebar;
    window.toggleSidebar = toggleSidebar;
    window.hideLoader = hideLoader;
    window.confirmBox = confirmBox; // Export ke window agar bisa dipanggil dari view lain
})();
</script>

@stack('scripts')
</body>
</html>