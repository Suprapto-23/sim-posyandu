<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Kader Workspace') | PosyanduCare</title>

    <!-- Mencegah FOUC (Flash of Unstyled Content) -->
    <script>
        try {
            if (localStorage.getItem('pc_kader_sidebar') !== '0' && matchMedia('(min-width:1024px)').matches) {
                document.documentElement.classList.add('sb-open');
            }
        } catch (e) {}
    </script>

    <link rel="icon" type="image/webp" href="{{ asset('img/logo.webp') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"></noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'" referrerpolicy="no-referrer">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer"></noscript>

    <style>
        :root {
            --sb-width: 280px;
            --slate-900: #0f172a; --slate-700: #334155; --slate-500: #64748b;
            --border: #f1f5f9; --bg-app: #fcfcfd;
            --transition-speed: 0.2s; /* Diperhalus */
            --ease-out: cubic-bezier(0.2, 0.8, 0.2, 1); 
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        [x-cloak] { display: none !important; }

        html, body { 
            margin: 0; height: 100vh; overflow: hidden; 
            font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            color: var(--slate-700); background-color: var(--bg-app);
            -webkit-font-smoothing: antialiased; font-synthesis: none;
            text-rendering: optimizeLegibility;
        }

        html.locked, body.locked { overflow: hidden !important; }

        /* ── SPA LOADER BAR (Progress Bar Atas) ── */
        #spa-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, #10b981, #34d399, #059669);
            background-size: 200% 100%; z-index: 9999999;
            transform-origin: left; transform: scaleX(0); opacity: 0;
            transition: transform 0.2s ease-out, opacity 0.2s ease; pointer-events: none;
        }
        @keyframes loadingBar { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }
        #spa-loader.is-loading { animation: loadingBar 1s infinite linear; }

        /* ── SIDEBAR ── */
        .kader-sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 50;
            width: var(--sb-width); padding: 16px;
            transform: translateX(-100%) translateZ(0); 
            transition: transform var(--transition-speed) var(--ease-out);
            will-change: transform; backface-visibility: hidden;
        }
        html.sb-open .kader-sidebar { transform: translateX(0) translateZ(0); }
        
        .pc-sidebar {
            height: 100%; border-radius: 24px; padding: 24px 16px; 
            overflow-y: auto; overflow-x: hidden; background: #ffffff;
            border: 1px solid var(--border); 
            box-shadow: 0 4px 6px -4px rgba(0, 0, 0, 0.02), 0 10px 15px -3px rgba(0, 0, 0, 0.03);
            scrollbar-width: none !important;
        }
        .pc-sidebar::-webkit-scrollbar { display: none !important; }

        .pc-sidebar a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 12px; color: var(--slate-500);
            font-weight: 700; font-size: 13px; text-decoration: none;
            transition: all 0.1s ease; cursor: pointer;
        }
        .pc-sidebar a:hover { background: #f1f5f9; color: #0f172a; }
        .pc-sidebar a.active { background: #ecfdf5; color: #059669; font-weight: 800; }
        .pc-sidebar a i { width: 20px; text-align: center; font-size: 14px; }

        /* ── LAYOUT ── */
        .app-wrapper {
            display: flex; flex-direction: column; height: 100vh; padding-left: 0;
            transition: padding-left var(--transition-speed) var(--ease-out);
            will-change: padding-left; z-index: 10;
        }
        @media (min-width: 1024px) { html.sb-open .app-wrapper { padding-left: var(--sb-width); } }

        /* ── TOPBAR ── */
        .topbar-wrapper { padding: 16px 24px 0; flex-shrink: 0; z-index: 40; }

        .kader-topbar {
            min-height: 68px; padding: 8px 16px; border-radius: 24px; 
            display: flex; align-items: center; gap: 12px; 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border); box-shadow: 0 4px 6px -4px rgba(0, 0, 0, 0.02);
            transform: translateZ(0); 
        }
        @media (max-width: 1023px) {
            .kader-topbar { backdrop-filter: none; -webkit-backdrop-filter: none; background: rgba(255,255,255,.97); min-height: 60px; }
        }

        .sidebar-toggle {
            width: 44px; height: 44px; border: 1px solid var(--border); border-radius: 14px;
            background: #ffffff; color: var(--slate-500); cursor: pointer; flex-shrink: 0;
            display: grid; place-items: center; transition: all 0.1s ease;
        }
        .sidebar-toggle:hover { color: #0f172a; background: #f8fafc; border-color: #e2e8f0; }
        .sidebar-toggle:active { transform: scale(0.92); }

        .topbar-right { display: flex; align-items: center; gap: 12px; margin-left: auto; }

        /* ── BUTTONS ── */
        .btn-notif { 
            width: 44px; height: 44px; border: 1px solid var(--border); border-radius: 50%; 
            background: #ffffff; color: var(--slate-500); cursor: pointer; 
            display: flex; align-items: center; justify-content: center; 
            position: relative; transition: all 0.1s ease; 
        }
        .btn-notif:hover { background: #f8fafc; color: #0f172a; border-color: #e2e8f0; }
        .btn-notif:active { transform: scale(0.92); }
        
        .profile-button { 
            height: 44px; padding: 4px 12px 4px 4px; border: 1px solid var(--border); 
            border-radius: 50px; background: #ffffff; cursor: pointer; 
            display: flex; align-items: center; gap: 10px; transition: all 0.1s ease; 
        }
        .profile-button:hover { background: #f8fafc; border-color: #e2e8f0; }
        .profile-button:active { transform: scale(0.96); }
        .profile-avatar { 
            width: 34px; height: 34px; border-radius: 50%; 
            background: linear-gradient(135deg, #0f172a, #334155);
            color: #fff; display: grid; place-items: center; font-size: 11px; font-weight: 900; overflow: hidden; 
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name { font-size: 13px; font-weight: 800; color: var(--slate-700); max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* ── DROPDOWNS ── */
        .kader-dropdown { 
            position: absolute; right: 0; top: calc(100% + 12px); width: 240px; z-index: 90; 
            border-radius: 24px; padding: 8px; background: #ffffff; border: 1px solid var(--border); 
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.08), 0 20px 25px -5px rgba(0, 0, 0, 0.04); 
            transform-origin: top right; 
        }
        .notif-dropdown {
            position: absolute; right: 0; top: calc(100% + 12px); width: 360px; z-index: 100;
            border-radius: 24px; padding: 8px; background: #ffffff; border: 1px solid var(--border);
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.08), 0 20px 25px -5px rgba(0, 0, 0, 0.04);
            transform-origin: top right;
        }
        @media (max-width: 1023px) {
            .notif-dropdown { position: fixed; top: 76px; left: 16px; right: 16px; width: auto; border-radius: 20px; padding: 6px; }
            .kader-dropdown { width: 220px; right: -8px; }
        }
        
        .notif-scroll { max-height: 340px; overflow-y: auto; padding: 4px; }
        .notif-scroll::-webkit-scrollbar { width: 4px; }
        .notif-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }

        .dropdown-head { padding: 12px; margin-bottom: 4px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px; }
        .dropdown-name { color: var(--slate-900); font-size: 14px; font-weight: 800; line-height: 1.2; }
        .dropdown-role { color: var(--slate-500); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;}
        .dropdown-link, .dropdown-logout { 
            width: 100%; border: 0; border-radius: 16px; padding: 12px 14px; 
            background: transparent; cursor: pointer; display: flex; align-items: center; gap: 12px; 
            color: var(--slate-700); font-size: 13px; font-weight: 700; text-decoration: none; transition: all 0.1s ease; 
        }
        .dropdown-link:hover { background: #f8fafc; color: #0f172a; }
        .dropdown-logout { color: #e11d48; }
        .dropdown-logout:hover { background: #fff1f2; }

        /* ── MAIN CONTENT SCROLL ── */
        .main-scroll-area {
            flex: 1; overflow-y: auto; overflow-x: hidden; padding: 24px;
            scroll-behavior: smooth; -webkit-overflow-scrolling: touch;
        }
        .main-scroll-area::-webkit-scrollbar { width: 6px; }
        .main-scroll-area::-webkit-scrollbar-track { background: transparent; }
        .main-scroll-area::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* ── SPA ANIMATION (SMOOTH FADE IN/OUT - ZERO DELAY) ── */
        .kader-main {
            /* State Normal (Tampil Penuh) */
            opacity: 1; 
            transform: translateY(0) scale(1);
            transition: opacity 0.25s ease-out, transform 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
            will-change: opacity, transform;
            transform-origin: top center;
        }

        /* State Memuat (Fetch Data API) - Tidak Blank, hanya redup */
        .kader-main.is-loading-state { 
            opacity: 0.5; 
            transform: scale(0.995); 
            pointer-events: none;
        }

        /* State Persiapan Muncul (Posisi awal konten baru) */
        .kader-main.is-entering-state { 
            opacity: 0; 
            transform: translateY(15px); 
            transition: none !important; /* Matikan transisi agar reset posisi instan */
        }

        /* ── MOBILE OVERLAY ── */
        .mobile-overlay {
            position: fixed; inset: 0; z-index: 45; border: 0; background: rgba(15,23,42,.3);
            opacity: 0; visibility: hidden; transition: opacity 0.1s ease, visibility 0.1s ease;
            backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px);
        }
        html.sb-open .mobile-overlay { opacity: 1; visibility: visible; }
        
        @media (min-width: 1024px) { .mobile-overlay { display: none !important; } }
        @media (max-width: 1023px) {
            .app-wrapper { padding-left: 0 !important; }
            .profile-name { display: none; }
            .topbar-wrapper { padding: 12px 16px 0; }
            .main-scroll-area { padding: 16px; }
        }

        /* ── FULL SCREEN SWEETALERT FIX ── */
        div.swal2-container {
            z-index: 9999999 !important; 
            background: rgba(15, 23, 42, 0.45) !important; 
            backdrop-filter: blur(10px) !important; -webkit-backdrop-filter: blur(10px) !important;
            position: fixed !important; inset: 0 !important;
            width: 100vw !important; height: 100vh !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
        }
        div.swal2-container .swal2-popup { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
        
        .nexus-swal { 
            border-radius: 32px !important; padding: 32px !important; 
            font-family: "Plus Jakarta Sans", sans-serif !important; 
            border: 1px solid rgba(255,255,255,0.4) !important; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3) !important;
            width: 95% !important; max-width: 440px !important; 
        }
        .nexus-title { font-size: 22px !important; font-weight: 800 !important; color: var(--slate-900) !important; margin-bottom: 8px !important;}
        .nexus-html { font-size: 14px !important; color: #475569 !important; line-height: 1.6 !important; }
        
        .nexus-ok { border-radius: 14px !important; background: #0f172a !important; color: #fff !important; font-weight: 700 !important; padding: 12px 26px !important; transition: all 0.15s ease !important; border: 0 !important;}
        .nexus-ok:hover { background: #1e293b !important; transform: translateY(-1px) !important; box-shadow: 0 4px 12px rgba(15,23,42,0.2) !important; }
        .nexus-danger { border-radius: 14px !important; background: #e11d48 !important; color: #fff !important; font-weight: 700 !important; padding: 12px 26px !important; transition: all 0.15s ease !important; border: 0 !important;}
        .nexus-danger:hover { background: #be123c !important; transform: translateY(-1px) !important; box-shadow: 0 4px 12px rgba(225,29,72,0.2) !important; }
        .nexus-cancel { border-radius: 14px !important; background: #f8fafc !important; border: 1px solid #e2e8f0 !important; color: #475569 !important; font-weight: 700 !important; padding: 12px 26px !important; transition: all 0.15s ease !important;}
        .nexus-cancel:hover { background: #f1f5f9 !important; color: #0f172a !important; }

        @media (max-width: 480px) {
            .nexus-swal { padding: 24px !important; border-radius: 24px !important; }
            .nexus-title { font-size: 18px !important; }
            .nexus-ok, .nexus-danger, .nexus-cancel { padding: 12px 20px !important; font-size: 13px !important; width: 100% !important; margin-bottom: 8px !important;}
            .swal2-actions { flex-direction: column; width: 100%; }
        }
    </style>
    @stack('styles')
</head>

@php
    $user = Auth::user();
    $name = $user->name ?? 'Kader';
    $initial = strtoupper(substr($name, 0, 1));
    $photo = $user->foto ?? null;
    $profileUrl = \Illuminate\Support\Facades\Route::has('kader.profile.index') ? route('kader.profile.index') : '#';
    $unreadCount = class_exists('\App\Models\Notifikasi') && Auth::check() ? \App\Models\Notifikasi::where('user_id', Auth::id())->where('is_read', false)->count() : 0;
@endphp

<body x-data="layoutApp()" x-init="initApp()" class="antialiased">

    <!-- SPA Loader Bar -->
    <div id="spa-loader"></div>

    <!-- OVERLAY UNTUK MOBILE -->
    <button type="button" class="mobile-overlay" aria-label="Tutup Sidebar" onclick="setSidebar(false)"></button>

    <aside class="kader-sidebar">
        <div class="pc-sidebar">
            @include('partials.sidebar.kader')
        </div>
    </aside>

    <div class="app-wrapper">
        <div class="topbar-wrapper">
            <header class="kader-topbar">
                <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>

                <div class="topbar-right">
                    <div class="relative">
                        <button @click="notifOpen = !notifOpen; profileOpen = false" class="btn-notif">
                            <i class="fa-regular fa-bell text-[18px]"></i>
                            <span x-cloak x-show="unreadCount > 0" class="absolute top-[10px] right-[10px] flex h-[10px] w-[10px]">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-[10px] w-[10px] bg-rose-500 border-2 border-white"></span>
                            </span>
                        </button>

                        <div x-cloak x-show="notifOpen" @click.outside="notifOpen = false" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-3" 
                             class="notif-dropdown">
                             
                            <div class="p-3 border-b border-slate-100 flex items-center justify-between">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Notifikasi</p>
                                <span class="text-[10px] font-bold text-slate-800" x-show="unreadCount > 0" x-text="unreadCount + ' baru'"></span>
                            </div>

                            <div class="notif-scroll">
                                <template x-if="unreadCount === 0">
                                    <div class="text-center text-xs font-semibold text-slate-400 py-8">
                                        <i class="fa-regular fa-circle-check text-2xl block mb-2 text-slate-300"></i>
                                        Belum ada notifikasi.
                                    </div>
                                </template>
                                
                                <template x-if="unreadCount > 0">
                                    <div class="p-4 text-center">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-50 mb-3">
                                            <i class="fa-solid fa-bell-concierge text-slate-400 text-lg"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800">Anda memiliki <span x-text="unreadCount"></span> pesan baru!</p>
                                        <p class="text-xs font-medium text-slate-500 mt-1">Silakan periksa halaman notifikasi.</p>
                                    </div>
                                </template>
                            </div>

                            <div class="p-3 border-t border-slate-100 text-center">
                                <a href="{{ Route::has('kader.notifikasi.index') ? route('kader.notifikasi.index') : '#' }}" class="text-[10px] font-bold text-slate-700 uppercase tracking-widest hover:text-slate-900">
                                    Lihat Semua Notifikasi
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <button @click="profileOpen = !profileOpen; notifOpen = false" class="profile-button">
                            <div class="profile-avatar">
                                @if($photo) <img src="{{ asset('storage/'.$photo) }}" alt="Foto"> @else {{ $initial }} @endif
                            </div>
                            <span class="profile-name">Kader Pusat</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 pr-1 transition-transform duration-300" :class="profileOpen ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-cloak x-show="profileOpen" @click.outside="profileOpen = false" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-3" 
                             class="kader-dropdown">
                            <div class="dropdown-head">
                                <div class="profile-avatar w-12 h-12 text-lg">
                                    @if($photo) <img src="{{ asset('storage/'.$photo) }}"> @else {{ $initial }} @endif
                                </div>
                                <div>
                                    <div class="dropdown-name">{{ $name }}</div>
                                    <div class="dropdown-role">Akses Kader</div>
                                </div>
                            </div>

                            @if($profileUrl !== '#')
                                <a href="{{ $profileUrl }}" class="dropdown-link">
                                    <i class="fa-regular fa-user text-slate-400"></i> Profil Saya
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="js-logout-form m-0 p-0 mt-2 border-t border-slate-100 pt-2">
                                @csrf
                                <button type="submit" class="dropdown-logout">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-rose-400"></i> Keluar Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
        </div>

        <div class="main-scroll-area" id="mainScrollArea">
            <main class="kader-main" id="kaderMain">
                @yield('content')
            </main>
        </div>

    </div>

    <script>
        const root = document.documentElement;

        function setSidebar(open, save = true) {
            root.classList.toggle('sb-open', open);
            if (matchMedia('(max-width:1023px)').matches) {
                root.classList.toggle('locked', open);
                document.body.classList.toggle('locked', open);
            } else {
                root.classList.remove('locked');
                document.body.classList.remove('locked');
            }
            if (save && matchMedia('(min-width:1024px)').matches) {
                try { localStorage.setItem('pc_kader_sidebar', open ? '1' : '0'); } catch (e) {}
            }
        }

        function toggleSidebar() {
            setSidebar(!root.classList.contains('sb-open'));
        }

        // ============================================================
        // SWEETALERT PREMIUM (Tampil Penuh - Menutupi Sidebar)
        // ============================================================
        function nexusConfirm(options) {
            return Swal.fire({
                title: options.title || 'Konfirmasi',
                html: options.text,
                icon: options.icon || 'warning',
                iconColor: options.iconColor || '#0f172a',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: options.yes || 'Ya, Lanjutkan',
                cancelButtonText: options.no || 'Batal',
                buttonsStyling: false,
                customClass: {
                    popup: 'nexus-swal',
                    title: 'nexus-title',
                    htmlContainer: 'nexus-html',
                    confirmButton: options.danger ? 'nexus-danger' : 'nexus-ok',
                    cancelButton: 'nexus-cancel'
                }
            });
        }

        function updateSidebarActive(currentUrl) {
            const url = currentUrl || window.location.href;
            const currentPath = new URL(url).pathname;
            document.querySelectorAll('.pc-sidebar a').forEach(link => {
                const href = link.getAttribute('href');
                if (href) {
                    const linkPath = new URL(href, window.location.origin).pathname;
                    link.classList.toggle('active', linkPath === currentPath);
                }
            });
        }

        // ============================================================
        // SPA NAVIGATION (ZERO DELAY - DIM & SWAP METOD)
        // ============================================================
        let currentAbortController = null;
        let isNavigating = false;

        async function navigateTo(url) {
            if (isNavigating) {
                if (currentAbortController) currentAbortController.abort();
            }

            const currentPath = new URL(window.location.href).pathname + new URL(window.location.href).search;
            const targetPath = new URL(url, window.location.origin).pathname + new URL(url, window.location.origin).search;
            if (currentPath === targetPath) return;

            isNavigating = true;
            const controller = new AbortController();
            currentAbortController = controller;

            const mainEl = document.getElementById('kaderMain') || document.querySelector('main');
            const scrollArea = document.getElementById('mainScrollArea') || window;
            const loader = document.getElementById('spa-loader');

            if (!mainEl) { window.location.href = url; return; }

            // 1. UPDATE MENU SEKARANG JUGA & TAMPILKAN LOADER
            updateSidebarActive(url);
            loader.style.opacity = '1';
            loader.style.transform = 'scaleX(0.3)';
            loader.classList.add('is-loading');
            
            // 2. KONTEN LAMA JANGAN HILANG (Mencegah Blank/Delay Putih)
            // Hanya di-dim (redupkan) sedikit agar user tau sistem sedang memuat.
            mainEl.classList.add('is-loading-state');

            try {
                // 3. FETCH DATA (Sepenuhnya di background tanpa buatan delay)
                const res = await fetch(url, { 
                    signal: controller.signal, 
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } 
                });
                if (!res.ok) throw new Error('Network error'); 

                loader.style.transform = 'scaleX(0.7)';
                const html = await res.text();
                loader.style.transform = 'scaleX(1)';

                const doc = new DOMParser().parseFromString(html, 'text/html');
                const title = doc.querySelector('title');
                if (title) document.title = title.textContent;

                const newContent = doc.getElementById(mainEl.id) || doc.querySelector('main');
                if (!newContent) throw new Error('DOM Mismatch');

                // 4. RESET POSISI DOM INSTAN (is-entering-state menghapus transisi sementara)
                mainEl.classList.remove('is-loading-state');
                mainEl.classList.add('is-entering-state'); 
                
                // SWAP HTML
                mainEl.innerHTML = newContent.innerHTML;
                if (scrollArea.scrollTo) scrollArea.scrollTo({ top: 0, behavior: 'instant' });

                // Evaluasi ulang script (Alpine, Chart, dll)
                mainEl.querySelectorAll('script').forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.textContent = oldScript.textContent;
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                if (window.Alpine && typeof Alpine.initTree === 'function') Alpine.initTree(mainEl);

                // 5. ANIMASI MUNCUL HALUS (FADE IN + SLIDE UP)
                void mainEl.offsetWidth; // Force Reflow browser agar mengenali elemen sudah di-reset
                mainEl.classList.remove('is-entering-state'); // Memicu kembali transisi CSS default (.kader-main)

                document.dispatchEvent(new CustomEvent('spa:loaded', { detail: { url } }));
                window.history.pushState({}, '', url);

            } catch (err) {
                if (err.name !== 'AbortError') window.location.href = url;
            } finally {
                mainEl.style.pointerEvents = 'auto';
                isNavigating = false;
                currentAbortController = null;
                
                setTimeout(() => {
                    loader.style.opacity = '0';
                    loader.classList.remove('is-loading');
                    setTimeout(() => loader.style.transform = 'scaleX(0)', 200);
                }, 100);

                if (matchMedia('(max-width:1023px)').matches && root.classList.contains('sb-open')) {
                    setSidebar(false);
                }
            }
        }

        window.addEventListener('popstate', function() { navigateTo(window.location.href); });

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link || !link.href || link.target || link.host !== window.location.host) return;
            if (link.hasAttribute('download') || link.hasAttribute('data-no-delay') || link.hasAttribute('data-no-spa')) return;
            if (e.ctrlKey || e.metaKey || e.shiftKey) return;

            e.preventDefault();
            navigateTo(link.href);
        });

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (form.dataset.confirmed === '1') return;

            if (form.classList.contains('js-logout-form')) {
                event.preventDefault();
                nexusConfirm({ 
                    title: 'Keluar dari sistem?', text: 'Sesi Anda saat ini akan diakhiri.', icon: 'question', iconColor: '#0f172a', yes: 'Ya, Keluar' 
                }).then(r => {
                    if (r.isConfirmed) { 
                        form.dataset.confirmed = '1'; 
                        document.body.classList.add('content-leave'); 
                        setTimeout(() => form.submit(), 100); 
                    }
                });
            }
            if (form.classList.contains('delete-form') || form.classList.contains('dynamic-form-delete')) {
                event.preventDefault();
                nexusConfirm({ 
                    title: 'Hapus Data?', text: 'Data yang belum direview Bidan dapat dihapus secara permanen. Lanjutkan?', icon: 'warning', iconColor: '#e11d48', yes: 'Ya, Hapus', danger: true 
                }).then(r => {
                    if(r.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); }
                });
            }
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('layoutApp', () => ({
                profileOpen: false,
                notifOpen: false,
                unreadCount: {{ $unreadCount }},
                initApp() {
                    updateSidebarActive(window.location.href);
                    const url = '{{ Route::has("kader.notifikasi.count") ? route("kader.notifikasi.count") : "" }}';
                    if (!url) return;
                    
                    setInterval(async () => {
                        try {
                            const fetchUrl = new URL(url, window.location.origin);
                            fetchUrl.searchParams.append('_t', new Date().getTime()); 

                            const res = await fetch(fetchUrl.toString(), { 
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            
                            if (res.ok) {
                                const contentType = res.headers.get("content-type");
                                if(contentType && contentType.includes("application/json")){
                                    const data = await res.json();
                                    if (data.unread !== undefined) this.unreadCount = data.unread;
                                }
                            }
                        } catch (e) {}
                    }, 8000); 
                }
            }));
        });
    </script>
    
    @stack('scripts')
    @stack('modals')
    
    <script>
    // Security: Anti Inspect
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.onkeydown = function(e) {
        if (
            e.keyCode === 123 || 
            (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) || 
            (e.ctrlKey && e.keyCode === 85)
        ) {
            e.preventDefault();
            return false;
        }
    };
    </script>
</body>
</html>