<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Bidan Workspace') | PosyanduCare</title>

    <script>
        try {
            if (localStorage.getItem('pc_bidan_sidebar') !== '0' && matchMedia('(min-width:1024px)').matches) {
                document.documentElement.classList.add('sb-open');
            }
        } catch (e) {}
    </script>

    <link rel="icon" type="image/webp" href="{{ asset('img/logo.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=optional" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=optional"></noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'" referrerpolicy="no-referrer">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer"></noscript>

    <style>
        :root {
            --sb-width: 280px;
            --green-700: #047857;
            --green-600: #059669;
            --green-500: #10b981;
            --amber-500: #f59e0b;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-300: #cbd5e1;
            --border: #f1f5f9;
            --bg-app: #fcfcfd;
            --transition-speed: 0.08s;
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
            --blur-strength: 6px;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        [x-cloak] { display: none !important; }

        html, body {
            margin: 0;
            height: 100vh;
            overflow: hidden;
            font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            color: var(--slate-700);
            background-color: var(--bg-app);
            -webkit-font-smoothing: antialiased;
        }

        /* ── SIDEBAR ── */
        .bidan-sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
            width: var(--sb-width); max-width: 88vw; padding: 16px;
            transform: translateX(-100%);
            transition: transform var(--transition-speed) var(--ease-out), filter 0.18s var(--ease-out);
            contain: layout style paint;
        }
        html.sb-open .bidan-sidebar { transform: translateX(0); }
        /* will-change hanya aktif selama animasi buka/tutup berlangsung, bukan permanen,
           supaya browser tidak terus-menerus menahan compositing layer di GPU. */
        html.sb-animating .bidan-sidebar { will-change: transform; }
        @media (max-width: 360px) {
            .bidan-sidebar { padding: 10px; }
        }

        .pc-sidebar {
            height: 100%;
            border-radius: 24px;
            padding: 24px 16px;
            overflow-y: auto; overflow-x: hidden;
            background: #ffffff;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -4px rgba(0, 0, 0, 0.02), 0 10px 15px -3px rgba(0, 0, 0, 0.03);
            scrollbar-width: none !important;
        }
        .pc-sidebar::-webkit-scrollbar { display: none !important; }

        .pc-sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            color: var(--slate-500);
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            transition: background-color 0.06s ease, color 0.06s ease;
            cursor: pointer;
        }
        .pc-sidebar a:hover { background: #f1f5f9; color: #0f172a; }
        .pc-sidebar a.active { background: #ecfdf5; color: var(--green-600); font-weight: 800; }
        .pc-sidebar a i { width: 20px; text-align: center; font-size: 14px; }

        /* ── LAYOUT ── */
        .app-wrapper {
            display: flex; flex-direction: column;
            height: 100vh;
            padding-left: 0;
            transition: padding-left var(--transition-speed) var(--ease-out), filter 0.18s var(--ease-out);
            contain: layout style;
        }
        @media (min-width: 1024px) {
            html.sb-open .app-wrapper { padding-left: var(--sb-width); }
        }

        /* ── FULL PAGE BLUR SAAT MODAL KONFIRMASI TERBUKA ──
           Sidebar + seluruh app-wrapper (topbar & konten) ikut diburamkan langsung
           lewat filter, bukan mengandalkan backdrop-filter SweetAlert saja, karena
           backdrop-filter tidak selalu menembus elemen yang punya will-change /
           backdrop-filter sendiri di semua browser. Popup SweetAlert tetap tajam
           karena berada di luar dua container ini. */
        html.pc-blur-active .bidan-sidebar,
        html.pc-blur-active .app-wrapper {
            filter: blur(var(--blur-strength));
            pointer-events: none;
        }

        /* ── TOPBAR ── */
        .topbar-wrapper { padding: 16px 24px 0; flex-shrink: 0; z-index: 50; position: relative; }

        .bidan-topbar {
            min-height: 68px;
            padding: 8px 16px;
            border-radius: 24px;
            display: flex; align-items: center; gap: 12px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -4px rgba(0, 0, 0, 0.02);
            contain: layout style paint;
        }
        @media (max-width: 1023px) {
            .bidan-topbar {
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
                background: rgba(255,255,255,0.95);
                min-height: 60px;
                padding: 6px 12px;
                border-radius: 18px;
            }
        }

        .sidebar-toggle {
            width: 44px; height: 44px; border: 1px solid var(--border); border-radius: 14px;
            background: #ffffff; color: var(--slate-500); cursor: pointer; flex-shrink: 0;
            display: grid; place-items: center; transition: background-color 0.08s ease, color 0.08s ease, border-color 0.08s ease, transform 0.08s ease;
        }
        .sidebar-toggle:hover { color: #0f172a; background: #f8fafc; border-color: #e2e8f0; }
        .sidebar-toggle:active { transform: scale(0.92); }
        @media (max-width: 1023px) {
            .sidebar-toggle { width: 38px; height: 38px; border-radius: 12px; }
        }

        .topbar-right { display: flex; align-items: center; gap: 12px; margin-left: auto; }

        .workspace-chip {
            height: 38px; padding: 0 16px; border-radius: 14px;
            background: #ecfdf5; border: 1px solid rgba(16,185,129,.18);
            color: #065f46; font-size: 11.5px; font-weight: 900;
            display: flex; align-items: center; gap: 7px; white-space: nowrap;
        }
        @media (max-width: 1023px) {
            .workspace-chip { display: none; }
        }

        /* ── NOTIFICATION BUTTON ── */
        .btn-notif {
            width: 44px; height: 44px; border: 1px solid var(--border); border-radius: 50%;
            background: #ffffff; color: var(--slate-500); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            position: relative; transition: background-color 0.08s ease, color 0.08s ease, border-color 0.08s ease, transform 0.08s ease;
        }
        .btn-notif:hover { background: #f8fafc; color: var(--green-600); border-color: #e2e8f0; }
        .btn-notif:active { transform: scale(0.92); }

        .btn-notif .notif-badge {
            position: absolute; top: 8px; right: 8px;
            width: 18px; height: 18px;
            border-radius: 50%;
            background: #ef4444;
            color: #fff;
            font-size: 9px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(239,68,68,0.3);
        }
        .btn-notif .notif-pulse {
            position: absolute; top: 8px; right: 8px;
            width: 18px; height: 18px;
            border-radius: 50%;
            background: #ef4444;
            animation: notifPulse 1.4s infinite;
            opacity: 0.4;
        }
        @keyframes notifPulse {
            0% { transform: scale(0.8); opacity: 0.6; }
            100% { transform: scale(1.8); opacity: 0; }
        }
        @media (max-width: 1023px) {
            .btn-notif { width: 38px; height: 38px; }
            .btn-notif .notif-badge { width: 16px; height: 16px; font-size: 8px; top: 6px; right: 6px; }
            .btn-notif .notif-pulse { width: 16px; height: 16px; top: 6px; right: 6px; }
        }

        /* ── PROFILE BUTTON ── */
        .profile-button {
            height: 44px; padding: 4px 12px 4px 4px; border: 1px solid var(--border);
            border-radius: 50px; background: #ffffff; cursor: pointer;
            display: flex; align-items: center; gap: 10px; transition: background-color 0.08s ease, color 0.08s ease, border-color 0.08s ease, transform 0.08s ease;
        }
        .profile-button:hover { background: #f8fafc; border-color: #e2e8f0; }
        .profile-button:active { transform: scale(0.96); }
        .profile-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, var(--green-700), var(--green-500));
            color: #fff; display: grid; place-items: center; font-size: 11px; font-weight: 900; overflow: hidden;
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name { font-size: 13px; font-weight: 800; color: var(--slate-700); max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        @media (max-width: 1023px) {
            .profile-button { height: 38px; padding: 2px 8px 2px 2px; gap: 6px; }
            .profile-avatar { width: 30px; height: 30px; font-size: 10px; }
            .profile-name { display: none; }
        }

        /* ── DROPDOWN NOTIFIKASI ── */
        .notif-dropdown {
            position: absolute; right: 0; top: calc(100% + 12px); width: 360px; z-index: 100;
            border-radius: 24px; padding: 8px; background: #ffffff; border: 1px solid var(--border);
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.08), 0 20px 25px -5px rgba(0, 0, 0, 0.04);
            transform-origin: top right;
        }
        @media (max-width: 1023px) {
            .notif-dropdown {
                position: fixed; top: 76px; left: 16px; right: 16px; width: auto;
                border-radius: 20px; padding: 6px; transform-origin: top right;
            }
        }

        /* ── DROPDOWN PROFILE ── */
        .profile-dropdown {
            position: absolute; right: 0; top: calc(100% + 12px); width: 260px; z-index: 90;
            border-radius: 24px; padding: 8px; background: #ffffff; border: 1px solid var(--border);
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.08), 0 20px 25px -5px rgba(0, 0, 0, 0.04);
            transform-origin: top right;
        }
        @media (max-width: 1023px) { .profile-dropdown { width: 220px; right: -8px; } }

        .dropdown-head { padding: 12px; margin-bottom: 4px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px; }
        .dropdown-name { color: var(--slate-900); font-size: 14px; font-weight: 800; line-height: 1.2; }
        .dropdown-role { color: var(--slate-500); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }
        .dropdown-link, .dropdown-logout {
            width: 100%; border: 0; border-radius: 16px; padding: 12px 14px;
            background: transparent; cursor: pointer; display: flex; align-items: center; gap: 12px;
            color: var(--slate-700); font-size: 13px; font-weight: 700; text-decoration: none; transition: background-color 0.08s ease, color 0.08s ease, border-color 0.08s ease, transform 0.08s ease;
        }
        .dropdown-link:hover { background: #f8fafc; color: #0f172a; }
        .dropdown-logout { color: #e11d48; }
        .dropdown-logout:hover { background: #fff1f2; }

        /* ── NOTIF SCROLL ── */
        .notif-scroll { max-height: 340px; overflow-y: auto; padding: 4px; }
        .notif-scroll::-webkit-scrollbar { width: 4px; }
        .notif-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        .notif-scroll::-webkit-scrollbar-track { background: transparent; }

        .notif-item { display: block; padding: 12px 12px; border-radius: 14px; transition: background 0.1s ease; border-bottom: 1px solid rgba(241,245,249,0.8); }
        .notif-item:last-child { border-bottom: 0; }
        .notif-item:hover { background: #f8fafc; }
        .notif-item .notif-icon { flex-shrink: 0; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .notif-item .notif-icon.bg-emerald-50 { background: #ecfdf5; color: #10b981; }
        .notif-item .notif-icon.bg-slate-100 { background: #f1f5f9; color: #94a3b8; }
        .notif-item .notif-title { font-size: 13px; font-weight: 800; color: #0f172a; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .notif-item .notif-body { font-size: 12px; font-weight: 600; color: #64748b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; }
        .notif-item .notif-time { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
        .notif-item .notif-dot { flex-shrink: 0; width: 6px; height: 6px; border-radius: 50%; background: #ef4444; margin-top: 4px; }
        @media (max-width: 1023px) {
            .notif-item { padding: 10px 10px; }
            .notif-item .notif-icon { width: 32px; height: 32px; font-size: 12px; }
            .notif-item .notif-title { font-size: 12px; }
            .notif-item .notif-body { font-size: 11px; }
            .notif-item .notif-time { font-size: 9px; }
        }

        /* ── MAIN CONTENT ── */
        .main-scroll { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 24px; scroll-behavior: smooth; }
        .main-scroll::-webkit-scrollbar { width: 6px; }
        .main-scroll::-webkit-scrollbar-track { background: transparent; }
        .main-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .main-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .main-inner { width: 100%; min-height: 100%; opacity: 0; animation: contentEnter 0.08s ease-out forwards; }
        @keyframes contentEnter { 0% { opacity: 0; } 100% { opacity: 1; } }
        .content-leave .main-inner { animation: contentLeave 0.05s ease-in forwards !important; }
        @keyframes contentLeave { 0% { opacity: 1; } 100% { opacity: 0; } }

        /* Padding disesuaikan karena bottom-nav sudah dihapus */
        @media (max-width: 1023px) { .main-scroll { padding: 16px; } }
        @media (max-width: 480px) { .main-scroll { padding: 12px; } }

        /* ── MOBILE OVERLAY ── */
        .mobile-overlay {
            position: fixed; inset: 0; z-index: 90; border: 0;
            background: rgba(15,23,42,.3); backdrop-filter: blur(3px);
            opacity: 0; visibility: hidden;
            transition: opacity 0.08s ease, visibility 0.08s ease;
        }
        html.sb-open .mobile-overlay { opacity: 1; visibility: visible; }
        @media (min-width: 1024px) { .mobile-overlay { display: none !important; } }
        @media (max-width: 1023px) { .app-wrapper { padding-left: 0 !important; } .topbar-wrapper { padding: 16px 16px 0; } }
        @media (max-width: 480px) { .topbar-wrapper { padding: 12px 12px 0; } }

        .pc-modal-layer { position: relative; z-index: 300; }

        /* ── SWEETALERT OVERLAY CONFIGURATION ──
           backdrop-filter dihapus dari sini karena kini blur dilakukan langsung
           pada sidebar & app-wrapper (lebih konsisten lintas browser & lebih
           murah secara render daripada backdrop-filter berlapis). Overlay cukup
           meredupkan sebagai lapisan pemisah visual terhadap popup. */
        div.swal2-container {
            z-index: 9999999 !important;
            background: rgba(15, 23, 42, 0.55) !important;
        }

        /* ── STYLING POPUP SWEETALERT ── */
        .nexus-swal {
            border-radius: 32px !important;
            padding: 32px !important;
            font-family: "Plus Jakarta Sans", sans-serif !important;
            border: 1px solid var(--border) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1) !important;
            width: 90% !important;
            max-width: 400px !important;
        }

        .nexus-title { font-size: 22px !important; font-weight: 800 !important; color: var(--slate-900) !important; margin-bottom: 8px !important; }
        .nexus-html { font-size: 14px !important; color: #64748b !important; }

        .nexus-ok {
            border-radius: 16px !important;
            background: #0f172a !important;
            color: #fff !important; font-weight: 700 !important; padding: 12px 24px !important; border: 0 !important; transition: all 0.15s ease !important;
        }
        .nexus-danger {
            border-radius: 16px !important;
            background: #e11d48 !important;
            color: #fff !important; font-weight: 700 !important; padding: 12px 24px !important; border: 0 !important; transition: all 0.15s ease !important;
        }
        .nexus-cancel {
            border-radius: 16px !important;
            background: #f8fafc !important; border: 1px solid #e2e8f0 !important;
            color: #475569 !important; font-weight: 700 !important; padding: 12px 24px !important; transition: all 0.15s ease !important;
        }

        .swal2-icon.swal2-question { border-color: var(--green-500) !important; color: var(--green-500) !important; }
        .swal2-icon.swal2-warning { border-color: #f59e0b !important; color: #f59e0b !important; }
        .swal2-icon.swal2-success { border-color: var(--green-500) !important; color: var(--green-500) !important; }
        .swal2-icon.swal2-error { border-color: #e11d48 !important; color: #e11d48 !important; }

        @media (max-width: 480px) {
            .nexus-swal { padding: 20px !important; border-radius: 20px !important; }
            .nexus-title { font-size: 18px !important; }
            .nexus-html { font-size: 13px !important; }
            .nexus-ok, .nexus-danger, .nexus-cancel { padding: 10px 18px !important; font-size: 13px !important; }
        }

        /* ── HORMATI PREFERENSI REDUCED MOTION (aksesibilitas + hemat resource) ── */
        @media (prefers-reduced-motion: reduce) {
            .bidan-sidebar, .app-wrapper, .main-inner, .notif-pulse, .mobile-overlay {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
    @stack('styles')
</head>

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
    try {
        if (class_exists('\App\Models\Notifikasi') && Schema::hasTable('notifikasis')) {
            $notifCount = \App\Models\Notifikasi::where('user_id', auth()->id())->where('is_read', false)->count();
        }
    } catch (\Throwable $e) { $notifCount = 0; }

    $dashboardUrl = $safeRoute('bidan.dashboard');
    $profileUrl = $safeRoute('bidan.profile.index');
    $rekamUrl = $safeRoute('bidan.rekam-medis.index');
    $pemeriksaanUrl = $safeRoute('bidan.pemeriksaan.index');
    $imunisasiUrl = $safeRoute('bidan.imunisasi.index');
    $jadwalUrl = $safeRoute('bidan.jadwal.index');
    $notifFetchRoute = Route::has('bidan.notifikasi.fetch') ? route('bidan.notifikasi.fetch') : '';
@endphp

<body x-data="layoutApp()" x-init="initApp()" class="antialiased">

    <button type="button" class="mobile-overlay" aria-label="Tutup Sidebar" onclick="setSidebar(false)"></button>

    <aside class="bidan-sidebar">
        <div class="pc-sidebar">
            @include('partials.sidebar.bidan')
        </div>
    </aside>

    <div class="app-wrapper">
        <div class="topbar-wrapper">
            <header class="bidan-topbar">
                <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>

                <div class="topbar-right">
                    <div class="workspace-chip">
                        <i class="fa-solid fa-heart-pulse"></i> Bidan Workspace
                    </div>

                    <div class="relative">
                        <button @click="notifOpen = !notifOpen; profileOpen = false" class="btn-notif" aria-label="Notifikasi">
                            <i class="fa-regular fa-bell text-[18px]"></i>
                            <template x-if="unreadCount > 0"><span class="notif-badge" x-text="unreadCount > 9 ? '9+' : unreadCount"></span></template>
                            <template x-if="unreadCount > 0"><span class="notif-pulse"></span></template>
                        </button>

                        <div x-cloak x-show="notifOpen" @click.outside="notifOpen = false"
                                x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95 translate-y-3" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                                class="notif-dropdown">

                                <div class="p-3 border-b border-slate-100 flex items-center justify-between">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Notifikasi</p>
                                    <span class="text-[10px] font-bold text-emerald-600" x-text="unreadCount + ' baru'"></span>
                                </div>
                                <div class="notif-scroll">
                                    <template x-if="notifItems.length === 0">
                                        <div class="text-center text-xs font-semibold text-slate-400 py-8">
                                            <i class="fa-regular fa-circle-check text-2xl block mb-2 text-emerald-400"></i> Belum ada notifikasi.
                                        </div>
                                    </template>
                                    <template x-for="item in notifItems" :key="item.id">
                                        <a :href="item.link" class="notif-item flex gap-3 items-start">
                                            <div class="notif-icon" :class="item.is_read ? 'bg-slate-100 text-slate-400' : 'bg-emerald-50 text-emerald-600'">
                                                <i class="fas" :class="item.icon || 'fa-bell'"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <p class="notif-title" x-text="item.judul"></p>
                                                    <span x-show="!item.is_read" class="notif-dot"></span>
                                                </div>
                                                <p class="notif-body" x-text="item.pesan"></p>
                                                <p class="notif-time" x-text="item.waktu"></p>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                                <div class="p-3 border-t border-slate-100 text-center">
                                    <a href="{{ Route::has('bidan.notifikasi.index') ? route('bidan.notifikasi.index') : '#' }}" class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest hover:text-emerald-700">
                                        Lihat Semua Notifikasi
                                    </a>
                                </div>
                        </div>
                    </div>

                    <div class="relative">
                        <button @click="profileOpen = !profileOpen; notifOpen = false" class="profile-button">
                            <div class="profile-avatar">
                                @if($bidanPhoto) <img src="{{ asset('storage/'.$bidanPhoto) }}" alt="Foto"> @else {{ $bidanInitial }} @endif
                            </div>
                            <span class="profile-name">{{ $bidanName }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 pr-1 transition-transform duration-300" :class="profileOpen ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-cloak x-show="profileOpen" @click.outside="profileOpen = false"
                                x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95 translate-y-3" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                                class="profile-dropdown">
                            <div class="dropdown-head">
                                <div class="profile-avatar w-12 h-12 text-lg">
                                    @if($bidanPhoto) <img src="{{ asset('storage/'.$bidanPhoto) }}"> @else {{ $bidanInitial }} @endif
                                </div>
                                <div>
                                    <div class="dropdown-name">{{ $bidanName }}</div>
                                    <div class="dropdown-role">Tenaga Bidan</div>
                                </div>
                            </div>
                            @if($profileUrl !== '#')
                                <a href="{{ $profileUrl }}" class="dropdown-link"><i class="fa-regular fa-user text-slate-400"></i> Profil Saya</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="js-logout-form m-0 p-0 mt-2 border-t border-slate-100 pt-2">
                                @csrf
                                <button type="submit" class="dropdown-logout"><i class="fa-solid fa-arrow-right-from-bracket text-rose-400"></i> Keluar Sistem</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
        </div>

        <div class="main-scroll">
            <main class="main-inner">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="pc-modal-layer">
        @stack('modals')
    </div>

    <script>
        const root = document.documentElement;
        const notifFetchUrl = @json($notifFetchRoute);

        function setSidebar(open, save = true) {
            root.classList.add('sb-animating');
            root.classList.toggle('sb-open', open);
            if (matchMedia('(max-width:1023px)').matches) {
                root.classList.toggle('locked', open);
            } else { root.classList.remove('locked'); }
            if (save && matchMedia('(min-width:1024px)').matches) {
                try { localStorage.setItem('pc_bidan_sidebar', open ? '1' : '0'); } catch (e) {}
            }
            clearTimeout(window.__sbAnimTimeout);
            window.__sbAnimTimeout = setTimeout(() => root.classList.remove('sb-animating'), 150);
        }

        function toggleSidebar() { setSidebar(!root.classList.contains('sb-open')); }

        function nexusConfirm(options) {
            return Swal.fire({
                title: options.title || 'Konfirmasi',
                html: options.text,
                icon: options.icon || 'question',
                iconColor: options.iconColor || '#10b981',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: options.yes || 'Ya, Lanjutkan',
                cancelButtonText: options.no || 'Batal',
                buttonsStyling: false,
                // Blur seluruh sidebar + topbar + konten saat popup terbuka,
                // dan lepas lagi begitu popup mulai ditutup.
                didOpen: () => { root.classList.add('pc-blur-active'); },
                willClose: () => { root.classList.remove('pc-blur-active'); },
                customClass: {
                    popup: 'nexus-swal',
                    title: 'nexus-title',
                    htmlContainer: 'nexus-html',
                    confirmButton: options.danger ? 'nexus-danger' : 'nexus-ok',
                    cancelButton: 'nexus-cancel'
                }
            });
        }

        function nexusToast(title, text, icon) {
            if (!window.Swal) return;
            Swal.fire({
                toast: true, position: 'top-end', title: title, html: text || '', icon: icon || 'success',
                showConfirmButton: false, timer: 1400, timerProgressBar: true, customClass: { popup: 'nexus-swal' }
            });
        }
        window.nexusConfirm = nexusConfirm;
        window.nexusToast = nexusToast;

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && !link.target && link.host === window.location.host && !link.hasAttribute('download') && !link.hasAttribute('data-no-delay')) {
                if (e.ctrlKey || e.metaKey || e.shiftKey) return;
                e.preventDefault();

                const targetHref = link.href;
                const mainInner = document.querySelector('.main-inner');
                let navigated = false;
                const go = () => { if (!navigated) { navigated = true; window.location.href = targetHref; } };

                document.body.classList.add('content-leave');
                // Navigasi dipicu tepat saat animasi leave selesai (bukan angka tebakan),
                // dengan fallback timeout sebagai jaring pengaman.
                if (mainInner) mainInner.addEventListener('animationend', go, { once: true });
                setTimeout(go, 90);
            }
        });

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (form.dataset.confirmed === '1') return;

            if (form.classList.contains('js-logout-form')) {
                event.preventDefault();
                nexusConfirm({ title: 'Keluar dari sistem?', text: 'Sesi Anda saat ini akan diakhiri.', icon: 'question', iconColor: '#10b981', yes: 'Ya, Keluar' }).then(r => {
                    if (r.isConfirmed) { form.dataset.confirmed = '1'; document.body.classList.add('content-leave'); setTimeout(() => form.submit(), 100); }
                });
            }
            if (form.classList.contains('delete-form')) {
                event.preventDefault();
                nexusConfirm({ title: 'Hapus Data?', text: 'Tindakan ini tidak bisa dibatalkan.', icon: 'warning', iconColor: '#e11d48', yes: 'Ya, Hapus', danger: true }).then(r => {
                    if(r.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); }
                });
            }
        });

        window.addEventListener('pageshow', function (event) { if (event.persisted) document.body.classList.remove('content-leave'); });

        document.addEventListener('alpine:init', () => {
            Alpine.data('layoutApp', () => ({
                profileOpen: false, notifOpen: false, unreadCount: {{ $notifCount }}, notifItems: [],
                _pollTimer: null,
                initApp() {
                    if (!notifFetchUrl) return;
                    this.fetchNotifikasi();
                    // Polling berhenti otomatis saat tab tidak aktif, dan langsung
                    // refresh begitu tab aktif kembali — hemat request & CPU.
                    this._pollTimer = setInterval(() => {
                        if (document.visibilityState === 'visible') this.fetchNotifikasi();
                    }, 30000);
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible') this.fetchNotifikasi();
                    });
                },
                async fetchNotifikasi() {
                    if (!notifFetchUrl) return;
                    try {
                        const res = await fetch(notifFetchUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        if (res.ok) {
                            const data = await res.json();
                            this.unreadCount = data.unreadCount || 0;
                            this.notifItems = data.items || [];
                            // Badge sudah reaktif lewat x-text di template, tidak perlu
                            // manipulasi DOM manual tambahan di sini.
                        }
                    } catch (e) {}
                }
            }));
        });

        @if(session('success')) nexusToast('Berhasil', @json(session('success')), 'success'); @endif
        @if(session('error')) nexusToast('Perhatian', @json(session('error')), 'error'); @endif
        @if(session('warning')) nexusToast('Perhatian', @json(session('warning')), 'warning'); @endif
    </script>
    @stack('scripts')
</body>
</html>