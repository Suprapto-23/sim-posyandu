<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') | PosyanduCare</title>

    <script>
        try {
            if (localStorage.getItem('pc_admin_sidebar') !== '0' && matchMedia('(min-width:1024px)').matches) {
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
            --sb-width: 284px;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --border: #f1f5f9;
            --bg-app: #fcfcfd;
            --transition-speed: 0.12s; /* Dioptimasi untuk Snappy Feel */
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
            --emerald-600: #059669;
            --emerald-50: #ecfdf5;
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
            -webkit-font-smoothing: antialiased; font-synthesis: none;
            text-rendering: optimizeLegibility;
        }

        /* ── SIDEBAR ── */
        .admin-sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; 
            width: var(--sb-width); padding: 16px;
            transform: translateX(-100%);
            transition: transform 0.2s var(--ease-out);
            will-change: transform; 
        }
        html.sb-open .admin-sidebar { transform: translateX(0); }
        
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

        /* ── SIDEBAR LINKS (SUPER CEPAT) ── */
        .pc-sidebar a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 12px; color: var(--slate-500);
            font-weight: 700; font-size: 13px; text-decoration: none;
            transition: background-color 0.1s ease, color 0.1s ease;
            cursor: pointer; will-change: background-color, color;
        }
        .pc-sidebar a:hover { background: #f1f5f9; color: #0f172a; }
        .pc-sidebar a.active { background: var(--emerald-50); color: var(--emerald-600); font-weight: 800; }
        .pc-sidebar a i { width: 20px; text-align: center; font-size: 14px; }

        /* ── LAYOUT CONTENT WRAPPER ── */
        .app-wrapper {
            display: flex; flex-direction: column; height: 100vh; padding-left: 0;
            transition: padding-left 0.2s var(--ease-out);
            will-change: padding-left;
        }
        @media (min-width: 1024px) { html.sb-open .app-wrapper { padding-left: var(--sb-width); } }

        /* ── TOPBAR ── */
        .topbar-wrapper { padding: 16px 24px 0; flex-shrink: 0; z-index: 50; }

        .admin-topbar {
            min-height: 68px; padding: 8px 16px; border-radius: 24px; 
            display: flex; align-items: center; gap: 12px; 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border); box-shadow: 0 4px 6px -4px rgba(0, 0, 0, 0.02);
            will-change: backdrop-filter;
        }
        @media (max-width: 1023px) {
            .admin-topbar { backdrop-filter: none; -webkit-backdrop-filter: none; background: rgba(255,255,255,.97); }
        }

        .sidebar-toggle {
            width: 44px; height: 44px; border: 1px solid var(--border); border-radius: 14px;
            background: #ffffff; color: var(--slate-500); cursor: pointer; flex-shrink: 0; display: grid; place-items: center; 
            transition: all 0.1s ease; will-change: transform;
        }
        .sidebar-toggle:hover { color: #0f172a; background: #f8fafc; border-color: #e2e8f0; }
        .sidebar-toggle:active { transform: scale(0.92); }

        .topbar-right { display: flex; align-items: center; gap: 12px; margin-left: auto; }

        .system-chip {
            height: 38px; padding: 0 16px; border-radius: 14px;
            background: #ecfdf5; border: 1px solid rgba(16,185,129,.18);
            color: #065f46; font-size: 11.5px; font-weight: 900;
            display: flex; align-items: center; gap: 7px; white-space: nowrap;
        }

        .profile-button { 
            height: 44px; padding: 4px 12px 4px 4px; border: 1px solid var(--border); 
            border-radius: 50px; background: #ffffff; cursor: pointer; 
            display: flex; align-items: center; gap: 10px; transition: all 0.1s ease; will-change: transform;
        }
        .profile-button:hover { background: #f8fafc; border-color: #e2e8f0; }
        .profile-button:active { transform: scale(0.96); }
        .profile-avatar { 
            width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #0f172a, #334155);
            color: #fff; display: grid; place-items: center; font-size: 11px; font-weight: 900; overflow: hidden; 
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name { font-size: 13px; font-weight: 800; color: var(--slate-700); max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* ── DROPDOWN ── */
        .admin-dropdown { 
            position: absolute; right: 0; top: calc(100% + 12px); width: 240px; z-index: 90; 
            border-radius: 24px; padding: 8px; background: #ffffff; border: 1px solid var(--border); 
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.08), 0 20px 25px -5px rgba(0, 0, 0, 0.04); transform-origin: top right; 
        }
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

        /* ── MAIN CONTENT (Smooth Scroll Hardware Accelerated) ── */
        .main-scroll-area {
            flex: 1; overflow-y: auto; overflow-x: hidden;
            padding: 24px; scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .main-scroll-area::-webkit-scrollbar { width: 6px; }
        .main-scroll-area::-webkit-scrollbar-track { background: transparent; }
        .main-scroll-area::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .main-scroll-area::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── ANIMASI KONTEN (SPA - Butter Smooth) ── */
        .admin-main {
            opacity: 1; transform: translateY(0);
            transition: opacity var(--transition-speed) var(--ease-out), transform var(--transition-speed) var(--ease-out);
            will-change: opacity, transform;
        }
        .admin-main.is-leaving { opacity: 0; transform: translateY(-6px); }
        .admin-main.is-entering { opacity: 0; transform: translateY(6px); }
        .admin-main.is-enter-done { opacity: 1; transform: translateY(0); }

        /* ── MOBILE OVERLAY ── */
        .mobile-overlay {
            position: fixed; inset: 0; z-index: 90; border: 0; background: rgba(15,23,42,.3);
            opacity: 0; visibility: hidden; transition: opacity 0.15s ease, visibility 0.15s ease;
        }
        html.sb-open .mobile-overlay { opacity: 1; visibility: visible; }
        
        @media (min-width: 1024px) { .mobile-overlay { display: none !important; } }
        @media (max-width: 1023px) {
            .app-wrapper { padding-left: 0 !important; }
            .profile-name, .system-chip { display: none; }
            .topbar-wrapper { padding: 12px 16px 0; }
            .main-scroll-area { padding: 16px; }
        }

        /* ── SWEETALERT PALETTE EMERALD ── */
        .nexus-swal { border-radius: 32px !important; padding: 32px !important; font-family: "Plus Jakarta Sans", sans-serif !important; border: 1px solid var(--border) !important; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1) !important;}
        .nexus-title { font-size: 22px !important; font-weight: 800 !important; color: var(--slate-900) !important;}
        .nexus-html { font-size: 14px !important; color: #64748b !important; }

        .nexus-ok { border-radius: 16px !important; background: linear-gradient(135deg, #059669, #10b981) !important; color: #fff !important; font-weight: 700 !important; padding: 12px 24px !important; box-shadow: 0 4px 12px rgba(5,150,105,0.3) !important; border: 0 !important; transition: all 0.1s; }
        .nexus-ok:hover { transform: translateY(-1px) !important; box-shadow: 0 6px 16px rgba(5,150,105,0.4) !important; }
        .nexus-danger { border-radius: 16px !important; background: linear-gradient(135deg, #dc2626, #ef4444) !important; color: #fff !important; font-weight: 700 !important; padding: 12px 24px !important; box-shadow: 0 4px 12px rgba(220,38,38,0.3) !important; border: 0 !important; transition: all 0.1s; }
        .nexus-danger:hover { transform: translateY(-1px) !important; box-shadow: 0 6px 16px rgba(220,38,38,0.4) !important; }
        .nexus-cancel { border-radius: 16px !important; background: #f8fafc !important; border: 1px solid #e2e8f0 !important; color: #475569 !important; font-weight: 700 !important; padding: 12px 24px !important; transition: all 0.1s; }
        .nexus-cancel:hover { background: #f1f5f9 !important; }
    </style>
    @stack('styles')
</head>

@php
    $user = auth()->user();
    $name = $user->name ?? 'Administrator';
    $initial = strtoupper(substr($name, 0, 1));
    $photo = $user->foto ?? null;
    // Admin tidak memiliki halaman profil (by design) — link "Profil Saya" dihapus.
@endphp

<body x-data="layoutApp()" x-init="initApp()" class="antialiased">

    <button type="button" class="mobile-overlay" aria-label="Tutup Sidebar" onclick="setSidebar(false)"></button>

    <aside class="admin-sidebar">
        <div class="pc-sidebar">
            @include('partials.sidebar.admin')
        </div>
    </aside>

    <div class="app-wrapper">
        <div class="topbar-wrapper">
            <header class="admin-topbar">
                <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>

                <div class="topbar-right">
                    <div class="system-chip">
                        <i class="fa-solid fa-heart-pulse"></i> PosyanduCare System
                    </div>

                    <div class="relative">
                        <button @click="profileOpen = !profileOpen" class="profile-button">
                            <div class="profile-avatar">
                                @if($photo) <img src="{{ asset('storage/'.$photo) }}" alt="Foto"> @else {{ $initial }} @endif
                            </div>
                            <span class="profile-name">{{ $name }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 pr-1 transition-transform duration-300" :class="profileOpen ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-cloak x-show="profileOpen" @click.outside="profileOpen = false" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-3" 
                             class="admin-dropdown">
                            <div class="dropdown-head">
                                <div class="profile-avatar w-12 h-12 text-lg">
                                    @if($photo) <img src="{{ asset('storage/'.$photo) }}"> @else {{ $initial }} @endif
                                </div>
                                <div>
                                    <div class="dropdown-name">{{ $name }}</div>
                                    <div class="dropdown-role">Akses Admin</div>
                                </div>
                            </div>

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
            <main class="admin-main" id="adminMain">
                @yield('content')
            </main>
        </div>

    </div>

    @stack('modals')

    <script>
        const root = document.documentElement;

        function setSidebar(open, save = true) {
            root.classList.toggle('sb-open', open);
            if (matchMedia('(max-width:1023px)').matches) root.classList.toggle('locked', open);
            else root.classList.remove('locked');
            if (save && matchMedia('(min-width:1024px)').matches) {
                try { localStorage.setItem('pc_admin_sidebar', open ? '1' : '0'); } catch (e) {}
            }
        }

        function toggleSidebar() { setSidebar(!root.classList.contains('sb-open')); }

        // ── SweetAlert Configuration ──
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
                customClass: {
                    popup: 'nexus-swal', title: 'nexus-title', htmlContainer: 'nexus-html',
                    confirmButton: options.danger ? 'nexus-danger' : 'nexus-ok', cancelButton: 'nexus-cancel'
                }
            });
        }

        // ============================================================
        // UPDATE SIDEBAR ACTIVE STATE
        // ============================================================
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
        // SPA NAVIGATION (BUTTERY SMOOTH)
        // ============================================================
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link || !link.href || link.target || link.host !== window.location.host) return;
            if (link.hasAttribute('download') || link.hasAttribute('data-no-spa')) return;
            if (e.ctrlKey || e.metaKey || e.shiftKey) return;

            e.preventDefault();
            navigateTo(link.href);
        });

        let currentAbortController = null;
        let isNavigating = false;

        function navigateTo(url) {
            if (isNavigating) {
                if (currentAbortController) currentAbortController.abort();
                return;
            }

            const currentPath = window.location.pathname + window.location.search;
            const targetPath = new URL(url).pathname + new URL(url).search;
            if (currentPath === targetPath) return;

            isNavigating = true;
            const controller = new AbortController();
            currentAbortController = controller;

            const mainEl = document.getElementById('adminMain');
            const scrollArea = document.getElementById('mainScrollArea');

            // Eksekusi Animasi Keluar
            mainEl.classList.remove('is-enter-done', 'is-entering');
            mainEl.classList.add('is-leaving');

            fetch(url, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(res => { if (!res.ok) throw new Error('Network error'); return res.text(); })
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const title = doc.querySelector('title');
                if (title) document.title = title.textContent;

                const newContent = doc.querySelector('#adminMain');
                if (!newContent) { window.location.href = url; return; }

                // Swap Konten
                mainEl.innerHTML = newContent.innerHTML;
                
                // Set Animasi Masuk
                mainEl.classList.remove('is-leaving');
                mainEl.classList.add('is-entering');

                if (scrollArea) scrollArea.scrollTo({ top: 0, behavior: 'instant' });

                // Eksekusi ulang tag <script> baru agar berfungsi
                mainEl.querySelectorAll('script').forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.textContent = oldScript.textContent;
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                // Re-bind komponen Alpine.js pada konten baru (fix: dulu tidak di-rebind)
                if (window.Alpine && typeof Alpine.initTree === 'function') Alpine.initTree(mainEl);

                // Tunggu sebentar lalu fade-in halus
                requestAnimationFrame(() => {
                    setTimeout(() => {
                        mainEl.classList.remove('is-entering');
                        mainEl.classList.add('is-enter-done');
                        document.dispatchEvent(new CustomEvent('spa:loaded', { detail: { url } }));
                        updateSidebarActive(url);
                    }, 50); 
                });

                window.history.pushState({}, '', url);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                window.location.href = url;
            })
            .finally(() => {
                isNavigating = false;
                currentAbortController = null;
            });
        }

        window.addEventListener('popstate', function() { navigateTo(window.location.href); });

        // ============================================================
        // GLOBAL FORM INTERCEPTOR (SOLUSI BUG SWEETALERT)
        // ============================================================
        document.addEventListener('submit', function (event) {
            const form = event.target;
            
            // Bypass jika sudah dikonfirmasi
            if (form.dataset.confirmed === '1') return;

            // 1. Aksi Logout
            if (form.classList.contains('js-logout-form')) {
                event.preventDefault();
                nexusConfirm({
                    title: 'Keluar dari sistem?', text: 'Sesi Anda saat ini akan diakhiri.', icon: 'question', iconColor: '#10b981', yes: 'Ya, Keluar'
                }).then(r => {
                    if (r.isConfirmed) { form.dataset.confirmed = '1'; document.body.classList.add('content-leave'); setTimeout(() => form.submit(), 150); }
                });
                return;
            }
            
            // 2. Aksi Hapus (Warga/Kader/Bidan)
            if (form.classList.contains('dynamic-form-delete') || form.classList.contains('delete-form')) {
                event.preventDefault();
                const btn = form.querySelector('button');
                const name = btn ? btn.dataset.name : 'Data ini';
                nexusConfirm({
                    title: 'Hapus Permanen?', text: `Apakah Anda yakin ingin menghapus <b>${name}</b>? Data yang terhapus tidak dapat dikembalikan.`, icon: 'warning', iconColor: '#f43f5e', yes: 'Ya, Hapus', danger: true
                }).then(r => {
                    if (r.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); }
                });
                return;
            }

            // 3. Aksi Toggle Status (Aktif/Nonaktif)
            if (form.classList.contains('dynamic-form-toggle')) {
                event.preventDefault();
                const btn = form.querySelector('button');
                const name = btn ? btn.dataset.name : 'Akun ini';
                const action = btn ? btn.dataset.action : 'ubah status';
                const isActivating = action === 'aktifkan';

                nexusConfirm({
                    title: isActivating ? 'Aktifkan Akun?' : 'Nonaktifkan Akun?', text: `Anda akan <b>${action}</b> akun milik <b>${name}</b>.`, icon: 'question', iconColor: isActivating ? '#10b981' : '#f59e0b', yes: isActivating ? 'Ya, Aktifkan' : 'Ya, Nonaktifkan'
                }).then(r => {
                    if (r.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); }
                });
                return;
            }

            // 4. Aksi Reset Password
            if (form.classList.contains('dynamic-form-reset')) {
                event.preventDefault();
                const btn = form.querySelector('button');
                const name = btn ? btn.dataset.name : 'Akun ini';

                nexusConfirm({
                    title: 'Reset Password?', text: `Sistem akan membuat password baru secara otomatis untuk <b>${name}</b>.`, icon: 'question', iconColor: '#3b82f6', yes: 'Ya, Reset'
                }).then(r => {
                    if (r.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); }
                });
                return;
            }
        });

        // ── FIX BROWSER BFCACHE (Back/Forward Cache) ──
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                document.body.classList.remove('content-leave');
                const mainEl = document.getElementById('adminMain');
                if (mainEl) {
                    mainEl.classList.remove('is-leaving', 'is-entering');
                    mainEl.classList.add('is-enter-done');
                }
                updateSidebarActive(window.location.href);
            }
        });

        // ── ALPINE & INISIASI ──
        document.addEventListener('alpine:init', () => {
            Alpine.data('layoutApp', () => ({
                profileOpen: false,
                initApp() { setTimeout(() => updateSidebarActive(window.location.href), 50); }
            }));
        });

        document.addEventListener('spa:loaded', function(e) {
            if (e.detail.url.includes('/admin/dashboard')) document.dispatchEvent(new Event('DOMContentLoaded'));
        });

        document.addEventListener('DOMContentLoaded', () => updateSidebarActive(window.location.href));
    </script>
    
    @stack('scripts')
    
    <script>
    // Security: Anti Inspect & Right Click
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