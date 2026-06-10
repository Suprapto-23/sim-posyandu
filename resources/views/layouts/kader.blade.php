<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kader Workspace') | PosyanduCare</title>

    {{-- Sidebar state SEBELUM render agar tidak FOUC --}}
    <script>
        try {
            if (localStorage.getItem('pc_kader_sidebar') !== '0' && matchMedia('(min-width:1024px)').matches) {
                document.documentElement.classList.add('sb-open');
            }
        } catch(e){}
    </script>

    <link rel="icon" type="image/webp" href="{{ asset('img/logo.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --sb-width: 284px;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --border: #e2e8f0;
            --sb-speed: 0.22s;
            --sb-ease: cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Cegah flash putih saat pertama load */
        html { background: #f8fafc; }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        [x-cloak] { display: none !important; }

        html, body {
            margin: 0; height: 100vh; overflow: hidden;
            font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            color: var(--slate-700); background: #f8fafc;
            -webkit-font-smoothing: antialiased;
        }

        /* ── SIDEBAR ──
           transition SELALU ada di elemen (bukan conditional di sb-open)
           → animasi hide SELALU terpanggil, tidak pernah "jump".
        ── */
        .kader-sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
            width: var(--sb-width); padding: 16px;
            transform: translateX(-100%);
            transition: transform var(--sb-speed) var(--sb-ease);
            will-change: transform;
            contain: layout style;
        }
        html.sb-open .kader-sidebar { transform: translateX(0); }

        .pc-sidebar {
            height: 100%; border-radius: 24px; padding: 20px 14px;
            overflow-y: auto; overflow-x: hidden; background: #fff;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(15,23,42,.04);
            scrollbar-width: none;
        }
        .pc-sidebar::-webkit-scrollbar { display: none; }

        /* ── APP WRAPPER ── */
        .app-wrapper {
            display: flex; flex-direction: column; height: 100vh;
            padding-left: 0;
            transition: padding-left var(--sb-speed) var(--sb-ease);
        }
        @media (min-width: 1024px) {
            html.sb-open .app-wrapper { padding-left: var(--sb-width); }
        }

        /* ── TOPBAR ── */
        .topbar-wrapper { padding: 16px 24px 0; flex-shrink: 0; z-index: 50; }
        .kader-topbar {
            min-height: 66px; padding: 8px 16px; border-radius: 20px;
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.95);
            border: 1px solid rgba(226,232,240,0.8);
            box-shadow: 0 4px 15px rgba(15,23,42,.03);
        }
        .sidebar-toggle {
            width: 42px; height: 42px; border: 1px solid var(--border); border-radius: 12px;
            background: #fff; color: var(--slate-500); cursor: pointer; flex-shrink: 0;
            display: grid; place-items: center; transition: 0.15s ease;
        }
        .sidebar-toggle:hover { color: #0d9488; background: #f0fdfa; border-color: #ccfbf1; }
        .sidebar-toggle:active { transform: scale(0.92); }
        .topbar-right { display: flex; align-items: center; gap: 12px; margin-left: auto; }
        .btn-notif { width: 42px; height: 42px; border: 1px solid var(--border); border-radius: 50%; background: #fff; color: var(--slate-500); cursor: pointer; display: flex; align-items: center; justify-content: center; position: relative; transition: 0.15s ease; }
        .btn-notif:hover { background: #f8fafc; color: #0f766e; }
        .btn-notif:active { transform: scale(0.92); }
        .profile-button { height: 42px; padding: 4px 12px 4px 4px; border: 1px solid var(--border); border-radius: 50px; background: #fff; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.15s ease; }
        .profile-button:hover { background: #f8fafc; }
        .profile-button:active { transform: scale(0.96); }
        .profile-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg,#0d9488,#14b8a6); color: #fff; display: grid; place-items: center; font-size: 11px; font-weight: 900; overflow: hidden; }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name { font-size: 13px; font-weight: 800; color: var(--slate-700); max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .kader-dropdown { position: absolute; right: 0; top: calc(100% + 12px); width: 220px; z-index: 90; border-radius: 18px; padding: 6px; background: #fff; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(15,23,42,.08); transform-origin: top right; }
        .dropdown-head { padding: 12px 10px; margin-bottom: 4px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px; }
        .dropdown-name { color: var(--slate-900); font-size: 13px; font-weight: 800; }
        .dropdown-role { color: var(--slate-500); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .dropdown-link, .dropdown-logout { width: 100%; border: 0; border-radius: 12px; padding: 10px 12px; background: transparent; cursor: pointer; display: flex; align-items: center; gap: 12px; color: #475569; font-size: 13px; font-weight: 700; text-decoration: none; transition: 0.15s ease; }
        .dropdown-link:hover { background: #f0fdfa; color: #0d9488; }
        .dropdown-logout { color: #e11d48; }
        .dropdown-logout:hover { background: #fff1f2; }

        /* ── SCROLL AREA ── */
        .main-scroll-area { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 24px; scroll-behavior: smooth; }
        .main-scroll-area::-webkit-scrollbar { width: 6px; }
        .main-scroll-area::-webkit-scrollbar-track { background: transparent; }
        .main-scroll-area::-webkit-scrollbar-thumb { background: rgba(148,163,184,.4); border-radius: 10px; }

        /* ── ANIMASI KONTEN ──
           HANYA #main-content yang beranimasi.
           Sidebar & topbar ada di luar → tidak pernah disentuh.
        ── */
        #main-content { opacity: 1; transform: translateY(0); }

        #main-content.is-leaving {
            transition: opacity 0.1s ease-in, transform 0.1s ease-in;
            opacity: 0; transform: translateY(-6px);
            pointer-events: none;
        }
        #main-content.is-entering {
            opacity: 0; transform: translateY(12px);
        }
        #main-content.is-visible {
            transition: opacity 0.18s ease-out, transform 0.18s ease-out;
            opacity: 1; transform: translateY(0);
        }

        /* Loading bar tipis di atas layar */
        #nav-progress {
            position: fixed; top: 0; left: 0; z-index: 9999;
            height: 2px; width: 0%; opacity: 0;
            background: linear-gradient(90deg, #0d9488, #14b8a6, #0d9488);
            background-size: 200% 100%;
            pointer-events: none;
            transition: width 0.25s ease, opacity 0.2s ease;
        }
        #nav-progress.active {
            opacity: 1; width: 75%;
            animation: progress-shimmer 1s ease infinite;
        }
        #nav-progress.done {
            width: 100%; opacity: 0;
            transition: width 0.12s ease, opacity 0.25s ease 0.08s;
        }
        @keyframes progress-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Mobile overlay */
        .mobile-overlay {
            position: fixed; inset: 0; z-index: 90; border: 0;
            background: rgba(15,23,42,.3);
            opacity: 0; visibility: hidden;
            transition: opacity var(--sb-speed) var(--sb-ease), visibility var(--sb-speed) var(--sb-ease);
        }
        html.sb-open .mobile-overlay { opacity: 1; visibility: visible; }
        @media (min-width: 1024px) { .mobile-overlay { display: none !important; } }
        @media (max-width: 1023px) {
            .app-wrapper { padding-left: 0 !important; }
            .profile-name { display: none; }
            .topbar-wrapper { padding: 12px 16px 0; }
            .main-scroll-area { padding: 16px; }
        }

        /* SweetAlert */
        .nexus-swal { border-radius: 24px !important; padding: 24px !important; font-family: "Plus Jakarta Sans", sans-serif !important; }
        .nexus-title { font-size: 20px !important; font-weight: 800 !important; }
        .nexus-html { font-size: 14px !important; color: #64748b !important; }
        .nexus-ok { border-radius: 12px !important; background: #0d9488 !important; }
        .nexus-danger { border-radius: 12px !important; background: #e11d48 !important; }
        .nexus-cancel { border-radius: 12px !important; background: #f1f5f9 !important; color: #475569 !important; }
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

    <div id="nav-progress"></div>

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
                            <span x-cloak x-show="unreadCount > 0" class="absolute top-[8px] right-[10px] flex h-[10px] w-[10px]">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-[10px] w-[10px] bg-rose-500 border-2 border-white"></span>
                            </span>
                        </button>
                        <div x-cloak x-show="notifOpen" @click.outside="notifOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                             class="kader-dropdown">
                            <div class="p-2 border-b border-slate-100 mb-2">
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Notifikasi (<span x-text="unreadCount"></span>)</p>
                            </div>
                            <div class="max-h-[300px] overflow-y-auto p-2">
                                <p class="text-center text-xs font-medium text-slate-400 py-4" x-show="unreadCount === 0">Belum ada notifikasi.</p>
                                <p class="text-center text-xs font-bold text-teal-600 py-4" x-show="unreadCount > 0">Ada pesan baru!</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <button @click="profileOpen = !profileOpen; notifOpen = false" class="profile-button">
                            <div class="profile-avatar">
                                @if($photo) <img src="{{ asset('storage/'.$photo) }}" alt="Foto"> @else {{ $initial }} @endif
                            </div>
                            <span class="profile-name">Kader</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 pr-1 transition-transform" :class="profileOpen ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-cloak x-show="profileOpen" @click.outside="profileOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                             class="kader-dropdown">
                            <div class="dropdown-head">
                                <div class="profile-avatar w-10 h-10 text-sm">
                                    @if($photo) <img src="{{ asset('storage/'.$photo) }}"> @else {{ $initial }} @endif
                                </div>
                                <div>
                                    <div class="dropdown-name">{{ $name }}</div>
                                    <div class="dropdown-role text-teal-600">Akses Kader</div>
                                </div>
                            </div>
                            @if($profileUrl !== '#')
                                <a href="{{ $profileUrl }}" class="dropdown-link">
                                    <i class="fa-regular fa-user"></i> Profil Saya
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="js-logout-form m-0 p-0 mt-1 border-t border-slate-100 pt-1">
                                @csrf
                                <button type="submit" class="dropdown-logout">
                                    <i class="fa-solid fa-right-from-bracket"></i> Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
        </div>

        <div class="main-scroll-area" id="scroll-area">
            {{--
                HANYA elemen ini yang di-swap saat navigasi.
                Sidebar & topbar ada di LUAR → tidak pernah di-render ulang,
                tidak pernah ngedip, tidak pernah hilang.
            --}}
            <main id="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
    (() => {
        'use strict';

        const root    = document.documentElement;
        const prog    = document.getElementById('nav-progress');
        const main    = document.getElementById('main-content');
        const scroll  = document.getElementById('scroll-area');
        let navigating = false;

        /* ── SIDEBAR
           Double rAF: frame-1 browser hitung layout,
           frame-2 toggle class → transition PASTI jalan, 
           termasuk saat HIDE (sebelumnya sering di-skip).
        ── */
        function setSidebar(open, save = true) {
            requestAnimationFrame(() => requestAnimationFrame(() => {
                root.classList.toggle('sb-open', open);
                root.classList.toggle('locked', open && matchMedia('(max-width:1023px)').matches);
                if (!open || matchMedia('(min-width:1024px)').matches) root.classList.remove('locked');
                if (save && matchMedia('(min-width:1024px)').matches) {
                    try { localStorage.setItem('pc_kader_sidebar', open ? '1' : '0'); } catch(e){}
                }
            }));
        }
        function toggleSidebar() { setSidebar(!root.classList.contains('sb-open')); }

        /* ── PROGRESS BAR ── */
        function progStart() { prog.className = 'active'; }
        function progDone()  {
            prog.className = 'done';
            setTimeout(() => { prog.className = ''; }, 400);
        }

        /* ── SOFT NAVIGATION
           Ini solusi fundamental agar sidebar & topbar tidak ngedip.
           Kita TIDAK pernah reload halaman — hanya swap innerHTML konten.

           Flow:
           1. fade-out konten lama (100ms)
           2. fetch() HTML halaman baru secara paralel
           3. parse → ambil #main-content + <title>
           4. swap innerHTML
           5. fade-in konten baru (180ms)
           6. pushState untuk update URL
           7. re-init Alpine & re-exec scripts konten baru
        ── */
        async function navigate(url) {
            if (navigating || url === window.location.href) return;
            navigating = true;

            progStart();

            /* Fade-out lama */
            main.classList.add('is-leaving');

            /* Fetch paralel selama fade-out */
            const fetchPromise = fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            });

            await sleep(100); /* Tunggu fade-out selesai */

            let doc;
            try {
                const res = await fetchPromise;
                if (!res.ok) throw new Error(res.status);
                const html = await res.text();
                doc = new DOMParser().parseFromString(html, 'text/html');
            } catch(e) {
                /* Fetch gagal → hard navigate */
                window.location.href = url;
                return;
            }

            const newMain = doc.getElementById('main-content');
            if (!newMain) {
                /* Redirect ke halaman non-layout (login, dll) */
                window.location.href = url;
                return;
            }

            /* Update URL & title */
            history.pushState({ softNav: true, url }, doc.title, url);
            document.title = doc.title;

            /* Pisahkan scripts dari konten */
            const scripts = [...newMain.querySelectorAll('script')];
            scripts.forEach(s => s.remove());

            /* Swap konten, siap enter */
            main.className = 'is-entering';
            main.innerHTML = newMain.innerHTML;
            scroll.scrollTop = 0;

            /* Re-init Alpine untuk konten baru */
            if (window.Alpine) {
                try { Alpine.initTree(main); } catch(e) {}
            }

            /* Fade-in: double rAF agar browser paint dulu baru transisi */
            requestAnimationFrame(() => requestAnimationFrame(() => {
                main.classList.add('is-visible');
                main.addEventListener('transitionend', () => {
                    main.className = ''; /* Bersih total */
                }, { once: true });
            }));

            progDone();

            /* Re-exec scripts (untuk @push('scripts') dari page child) */
            for (const s of scripts) {
                const el = document.createElement('script');
                if (s.src) {
                    el.src = s.src;
                    await new Promise(r => { el.onload = el.onerror = r; });
                } else {
                    el.textContent = s.textContent;
                }
                document.body.appendChild(el);
            }

            /* Update active link sidebar */
            const curPath = new URL(url).pathname;
            document.querySelectorAll('.kader-sidebar a[href]').forEach(a => {
                try {
                    const p = new URL(a.href, location.origin).pathname;
                    a.classList.toggle('active', p === curPath || (curPath.startsWith(p) && p.length > 1));
                } catch(e) {}
            });

            navigating = false;
        }

        function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

        /* ── INTERCEPT KLIK LINK ── */
        document.addEventListener('click', e => {
            const a = e.target.closest('a');
            if (!a || !a.href) return;
            if (a.target && a.target !== '_self') return;
            if (a.host !== location.host) return;
            if (a.hasAttribute('download')) return;
            if (e.ctrlKey || e.metaKey || e.shiftKey) return;
            if (a.getAttribute('href') === '#') return;
            if (a.href === location.href) return;
            if (a.pathname === location.pathname && a.hash) return;

            e.preventDefault();
            navigate(a.href);
        });

        /* ── BACK/FORWARD BROWSER ── */
        window.addEventListener('popstate', e => {
            if (e.state?.softNav) {
                navigate(e.state.url || location.href);
            } else {
                location.reload();
            }
        });

        /* ── FORM: LOGOUT & DELETE ── */
        function nexusConfirm(opts) {
            return Swal.fire({
                title: opts.title || 'Konfirmasi', html: opts.text,
                icon: opts.icon || 'warning', iconColor: opts.iconColor || '#0d9488',
                showCancelButton: true, reverseButtons: true,
                confirmButtonText: opts.yes || 'Ya, Lanjutkan',
                cancelButtonText: opts.no || 'Batal',
                buttonsStyling: false,
                customClass: {
                    popup: 'nexus-swal', title: 'nexus-title', htmlContainer: 'nexus-html',
                    confirmButton: opts.danger ? 'nexus-danger' : 'nexus-ok',
                    cancelButton: 'nexus-cancel'
                }
            });
        }

        document.addEventListener('submit', e => {
            const form = e.target;
            if (form.dataset.confirmed === '1') return;

            if (form.classList.contains('js-logout-form')) {
                e.preventDefault();
                nexusConfirm({ title: 'Keluar dari sistem?', text: 'Sesi anda akan ditutup.', icon: 'question', yes: 'Ya, Keluar' })
                    .then(r => { if (r.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); } });
            }
            if (form.classList.contains('delete-form')) {
                e.preventDefault();
                nexusConfirm({ title: 'Hapus Data?', text: 'Tindakan ini tidak bisa dikembalikan.', icon: 'warning', iconColor: '#e11d48', yes: 'Ya, Hapus', danger: true })
                    .then(r => { if (r.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); } });
            }
        });

        /* ── EXPOSE GLOBALS ── */
        window.setSidebar    = setSidebar;
        window.toggleSidebar = toggleSidebar;
        window.nexusConfirm  = nexusConfirm;
        window.softNavigate  = navigate;

        /* ── ALPINE LAYOUT DATA ── */
        document.addEventListener('alpine:init', () => {
            Alpine.data('layoutApp', () => ({
                profileOpen: false,
                notifOpen: false,
                unreadCount: {{ $unreadCount }},
                initApp() {
                    const url = '{{ Route::has("kader.notifikasi.count") ? route("kader.notifikasi.count") : "" }}';
                    if (!url) return;
                    setInterval(async () => {
                        try {
                            const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                            if (r.ok) { const d = await r.json(); if (d.unread !== undefined) this.unreadCount = d.unread; }
                        } catch(e) {}
                    }, 30000);
                }
            }));
        });

    })();
    </script>
    @stack('scripts')
</body>
</html>