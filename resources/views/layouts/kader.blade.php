<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kader Workspace') | PosyanduCare</title>

    <meta name="theme-color" content="#f8fffc">
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --g900:#064e3b; --g800:#065f46; --g700:#047857; --g600:#059669; --g500:#10b981; --g400:#34d399;
            --a500:#f59e0b; --r500:#f43f5e;
            --s900:#0f172a; --s800:#1e293b; --s700:#334155; --s600:#475569; --s500:#64748b; --s400:#94a3b8; --s300:#cbd5e1; --s200:#e2e8f0; --s100:#f1f5f9;
            --open:290px; --mini:92px; --ease:cubic-bezier(.16,1,.3,1);
        }

        * { box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
        html, body { width:100%; min-height:100%; margin:0; overflow-x:hidden; scroll-behavior:auto!important; }
        body {
            font-family:'Plus Jakarta Sans',sans-serif;
            color:var(--s700);
            background:
                radial-gradient(circle at 0 0, rgba(16,185,129,.10), transparent 34%),
                radial-gradient(circle at 100% 0, rgba(245,158,11,.06), transparent 30%),
                linear-gradient(135deg,#f8fffc 0%,#f8fafc 55%,#effbf6 100%);
            -webkit-font-smoothing:antialiased;
        }

        body.locked { overflow:hidden; }
        [x-cloak] { display:none!important; }
        button,input,select,textarea { font-family:inherit; outline:none; }
        ::selection { background:rgba(16,185,129,.18); color:var(--g900); }
        ::-webkit-scrollbar { width:6px; height:6px; }
        ::-webkit-scrollbar-thumb { background:rgba(148,163,184,.42); border-radius:999px; }

        .bgfx,.gridfx { position:fixed; inset:0; pointer-events:none; }
        .bgfx { z-index:0; overflow:hidden; }
        .bgfx:before,.bgfx:after {
            content:""; position:absolute; width:440px; height:440px; border-radius:999px; filter:blur(86px);
        }
        .bgfx:before { left:-220px; top:-220px; background:rgba(16,185,129,.12); }
        .bgfx:after { right:-220px; bottom:-220px; background:rgba(20,184,166,.08); }
        .gridfx {
            z-index:1; opacity:.08;
            background-image:
                linear-gradient(rgba(15,23,42,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15,23,42,.04) 1px, transparent 1px);
            background-size:72px 72px;
            mask-image:radial-gradient(circle at center,#000,transparent 72%);
        }

        .loader-bar {
            position:fixed; top:0; left:0; right:0; z-index:9999; height:3px;
            opacity:0; transform:translateY(-3px); overflow:hidden;
            background:rgba(236,253,245,.92); transition:.16s ease;
        }
        body.loading .loader-bar { opacity:1; transform:translateY(0); }
        .loader-bar:before {
            content:""; position:absolute; inset:0 auto 0 0; width:38%; border-radius:999px;
            background:linear-gradient(90deg,var(--g700),var(--g500),var(--a500));
            animation:loadbar .65s infinite var(--ease);
        }
        @keyframes loadbar { from{transform:translateX(-115%)} to{transform:translateX(290%)} }

        .loader-toast {
            position:fixed; top:18px; left:50%; z-index:9998;
            display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:999px;
            color:var(--g800); background:rgba(255,255,255,.9); border:1px solid rgba(226,232,240,.86);
            box-shadow:0 18px 44px rgba(15,23,42,.10);
            opacity:0; pointer-events:none; transform:translate(-50%,-10px) scale(.96);
            transition:.18s var(--ease);
        }
        body.loading .loader-toast { opacity:1; transform:translate(-50%,0) scale(1); }
        .loader-spin {
            width:16px; height:16px; border-radius:999px;
            border:2px solid rgba(16,185,129,.18); border-top-color:var(--g600);
            animation:spin .65s linear infinite;
        }
        @keyframes spin { to{transform:rotate(360deg)} }

        .shell { position:relative; z-index:5; min-height:100vh; }
        .sidebar {
            position:fixed; inset:0 auto 0 0; z-index:90;
            width:var(--open); height:100dvh; padding:12px;
            transform:translateX(-105%);
            transition:width .32s var(--ease), transform .28s var(--ease);
            will-change:width,transform;
        }
        .sidebar.open { transform:translateX(0); }
        .content {
            min-height:100vh; display:flex; flex-direction:column;
            transition:margin-left .32s var(--ease), opacity .16s ease;
        }
        body.loading .content { opacity:.94; }
        .backdrop {
            position:fixed; inset:0; z-index:60;
            background:rgba(15,23,42,.34);
            backdrop-filter:none; -webkit-backdrop-filter:none;
        }

        .side-card {
            height:calc(100dvh - 24px);
            display:flex; flex-direction:column; overflow:hidden; border-radius:26px;
            background:linear-gradient(180deg,#fff 0%,#f8fffc 100%);
            border:1px solid rgba(226,232,240,.82);
            box-shadow:0 22px 55px rgba(15,23,42,.13), inset 0 1px 0 rgba(255,255,255,.95);
            backdrop-filter:none; -webkit-backdrop-filter:none;
            transition:.28s var(--ease);
        }
        .side-card,.side-card * { filter:none; text-shadow:none; }
        .side-head { min-height:82px; padding:18px; display:grid; place-items:center; flex-shrink:0; }
        .side-logo img { width:142px; max-height:54px; object-fit:contain; transition:.28s var(--ease); filter:none; }
        .side-close {
            position:absolute; top:18px; right:18px; width:36px; height:36px;
            display:none; place-items:center; border:0; border-radius:14px;
            color:var(--g700); background:rgba(236,253,245,.95);
            box-shadow:0 10px 22px rgba(15,23,42,.07); cursor:pointer;
        }

        .side-user {
            margin:0 14px 14px; padding:12px;
            display:flex; align-items:center; gap:11px; flex-shrink:0;
            border-radius:18px; background:rgba(255,255,255,.78);
            border:1px solid rgba(16,185,129,.16);
            transition:.28s var(--ease);
        }
        .avatar {
            width:42px; height:42px; display:grid; place-items:center; flex-shrink:0; overflow:hidden;
            border-radius:15px; color:#fff; font-weight:900;
            background:linear-gradient(135deg,var(--g600),var(--g400));
            box-shadow:0 12px 22px rgba(16,185,129,.18);
        }
        .avatar img { width:100%; height:100%; object-fit:cover; }
        .side-user h4 { margin:0; max-width:150px; color:var(--g900); font-size:13px; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .side-user p { margin:3px 0 0; color:var(--s500); font-size:11px; font-weight:700; }

        .side-scroll { flex:1; min-height:0; overflow-y:auto; overflow-x:hidden; padding:0 12px 14px; scrollbar-width:none; }
        .side-scroll::-webkit-scrollbar { display:none; }
        .side-user-info,.menu-title,.menu-text,.caret,.submenu {
            transition:opacity .2s ease,width .28s var(--ease),max-width .28s var(--ease);
            overflow:hidden;
        }

        .menu-group { margin-bottom:16px; }
        .menu-title { margin:0 0 8px 8px; color:var(--s400); font-size:10px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; white-space:nowrap; }
        .menu-list { display:grid; gap:5px; }
        .menu-item {
            position:relative; min-height:43px; width:100%; padding:10px 12px;
            display:flex; align-items:center; gap:12px; border:1px solid transparent;
            border-radius:15px; color:var(--s600); background:transparent;
            text-decoration:none; font-size:13px; font-weight:800; cursor:pointer;
            transition:.18s var(--ease);
        }
        .menu-item:hover { color:var(--g700); background:rgba(236,253,245,.86); transform:translateX(2px); }
        .menu-item.active { color:var(--g800); background:#fff; border-color:rgba(209,250,229,.86); box-shadow:0 9px 18px rgba(16,185,129,.07); }
        .menu-item.active:before { content:""; position:absolute; left:0; top:11px; bottom:11px; width:4px; border-radius:0 999px 999px 0; background:var(--g500); }
        .menu-icon { width:22px; display:grid; place-items:center; flex-shrink:0; color:var(--s400); transition:.18s var(--ease); }
        .menu-item:hover .menu-icon,.menu-item.active .menu-icon { color:var(--g600); }
        .menu-text { flex:1; min-width:0; text-align:left; white-space:nowrap; text-overflow:ellipsis; }
        .caret { color:var(--s400); font-size:11px; }
        .submenu { max-height:0; margin-left:24px; padding-left:13px; border-left:1px dashed rgba(203,213,225,.9); }
        .submenu.open { max-height:180px; margin-top:5px; }
        .submenu a {
            min-height:34px; padding:8px 10px; display:flex; align-items:center; gap:9px;
            border-radius:12px; color:var(--s500); text-decoration:none; font-size:12px; font-weight:800; transition:.15s ease;
        }
        .submenu a:hover,.submenu a.active { color:var(--g800); background:rgba(236,253,245,.86); }
        .dot { width:6px; height:6px; border-radius:999px; background:var(--s300); }
        .submenu a.active .dot,.submenu a:hover .dot { background:var(--g500); }
        .logout { color:#dc2626; }
        .logout:hover { background:#fff1f2; color:#b91c1c; }

        .topbar {
            position:sticky; top:12px; z-index:40; min-height:68px;
            margin:16px 22px 0; padding:10px 12px;
            display:flex; align-items:center; justify-content:space-between; gap:12px;
            border-radius:24px; background:rgba(255,255,255,.82);
            border:1px solid rgba(226,232,240,.84);
            box-shadow:0 18px 42px rgba(15,23,42,.06), inset 0 1px 0 rgba(255,255,255,.86);
            backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px);
        }
        .top-left,.top-right { display:flex; align-items:center; gap:10px; min-width:0; }
        .top-right { margin-left:auto; }
        .icon-btn,.notif-btn,.profile-btn {
            height:46px; border:1px solid rgba(226,232,240,.86);
            background:rgba(255,255,255,.86); color:var(--s600);
            box-shadow:0 12px 26px rgba(15,23,42,.045), inset 0 1px 0 rgba(255,255,255,.82);
            cursor:pointer; transition:.16s var(--ease);
        }
        .icon-btn,.notif-btn { width:46px; display:grid; place-items:center; border-radius:16px; }
        .icon-btn:hover,.notif-btn:hover,.profile-btn:hover { transform:translateY(-1px); color:var(--g700); border-color:rgba(16,185,129,.28); background:#fff; }
        .desktop-only { display:none; }
        .chip {
            height:46px; padding:0 16px; display:flex; align-items:center; gap:9px;
            border-radius:17px; color:var(--g800);
            background:linear-gradient(135deg,rgba(236,253,245,.92),rgba(255,255,255,.74));
            border:1px solid rgba(16,185,129,.18); font-size:12px; font-weight:900; white-space:nowrap;
        }
        .notif-wrap,.profile-wrap { position:relative; }
        .notif-btn { position:relative; }
        .notif-dot { position:absolute; top:8px; right:8px; width:10px; height:10px; border-radius:999px; background:var(--r500); border:2px solid #fff; }
        .profile-btn { padding:4px 12px 4px 4px; display:flex; align-items:center; gap:10px; border-radius:999px; }
        .profile-meta { text-align:left; }
        .profile-name { max-width:130px; color:var(--s700); font-size:13px; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .profile-role { margin-top:1px; color:var(--s400); font-size:9px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }

        .dropdown {
            position:absolute; top:calc(100% + 10px); right:0; z-index:80;
            width:270px; padding:10px; border-radius:22px;
            background:rgba(255,255,255,.98); border:1px solid rgba(226,232,240,.88);
            box-shadow:0 26px 64px rgba(15,23,42,.13);
        }
        .drop-head { padding:10px 10px 12px; margin-bottom:7px; display:flex; align-items:center; gap:10px; border-bottom:1px solid var(--s100); }
        .drop-title { margin:0; color:var(--s900); font-size:13px; font-weight:900; }
        .drop-sub { margin:2px 0 0; color:var(--s400); font-size:10px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
        .drop-link,.drop-logout {
            width:100%; padding:11px 12px; display:flex; align-items:center; gap:10px;
            border:0; border-radius:15px; color:var(--s600); background:transparent;
            text-decoration:none; font-size:13px; font-weight:900; cursor:pointer;
        }
        .drop-link:hover { color:var(--g700); background:#ecfdf5; }
        .drop-logout { color:#dc2626; }
        .drop-logout:hover { color:#be123c; background:#fff1f2; }

        .main { width:100%; max-width:1480px; margin:0 auto; padding:24px 24px 42px; flex:1; }
        .main-inner { animation:contentIn .24s var(--ease) both; }
        @keyframes contentIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
        .admin-card,.stat-card,.dashboard-card,.content-card,.table-card,.kader-card {
            border-radius:24px; background:rgba(255,255,255,.84);
            border:1px solid rgba(226,232,240,.84);
            box-shadow:0 18px 44px rgba(15,23,42,.052), inset 0 1px 0 rgba(255,255,255,.84);
        }

        .swal2-popup.nexus-swal {
            border-radius:28px!important; font-family:'Plus Jakarta Sans',sans-serif!important;
            background:rgba(255,255,255,.98)!important; border:1px solid rgba(226,232,240,.86)!important;
            box-shadow:0 26px 70px rgba(15,23,42,.16)!important;
        }
        .btn-nexus-confirm,.btn-nexus-cancel { border:0!important; border-radius:15px!important; padding:12px 24px!important; font-weight:900!important; }
        .btn-nexus-confirm { color:#fff!important; background:linear-gradient(135deg,var(--g500),var(--g700))!important; }
        .btn-nexus-cancel { color:var(--s500)!important; background:var(--s100)!important; }

        @media (min-width:1024px) {
            .sidebar { transform:translateX(0); }
            .sidebar.collapsed { width:var(--mini); }
            .content { margin-left:var(--open); }
            .content.collapsed { margin-left:var(--mini); }
            .desktop-only { display:grid; }
            .mobile-only { display:none; }

            .sidebar.collapsed .side-card { border-radius:24px; box-shadow:0 18px 42px rgba(15,23,42,.07), inset 0 1px 0 rgba(255,255,255,.9); }
            .sidebar.collapsed .side-logo img { width:42px; transform:scale(.96); }
            .sidebar.collapsed .side-user { justify-content:center; margin-inline:10px; padding:10px; gap:0; }
            .sidebar.collapsed .side-user-info,
            .sidebar.collapsed .menu-title,
            .sidebar.collapsed .menu-text,
            .sidebar.collapsed .caret {
                opacity:0; width:0; max-width:0; flex:0 0 0; pointer-events:none;
            }
            .sidebar.collapsed .side-scroll { padding-inline:9px; }
            .sidebar.collapsed .menu-group { margin-bottom:12px; }
            .sidebar.collapsed .menu-item { justify-content:center; padding-inline:0; gap:0; }
            .sidebar.collapsed .menu-icon { width:44px; font-size:15px; }
            .sidebar.collapsed .menu-item:hover { transform:translateY(-1px); }
            .sidebar.collapsed .submenu { display:none; }
        }

        @media (max-width:1023px) {
            .sidebar { width:min(300px, calc(100vw - 24px)); padding:10px; transform:translateX(-110%); transition:transform .28s var(--ease); }
            .sidebar.open { transform:translateX(0); }
            .side-card { height:calc(100dvh - 20px); border-radius:23px; }
            .side-close { display:grid; }
            .topbar { top:10px; min-height:64px; margin:10px 10px 0; padding:9px 10px; border-radius:22px; }
            .chip { display:none; }
            .main { padding:20px 12px 32px; }
            body.locked .content { filter:none; transform:none; opacity:1; pointer-events:none; }
        }

        @media (max-width:640px) {
            .profile-meta { display:none; }
            .profile-btn { width:46px; padding:3px; }
            .dropdown { width:min(270px, calc(100vw - 24px)); }
        }

        @media (prefers-reduced-motion:reduce) {
            *,*:before,*:after { animation-duration:1ms!important; transition-duration:1ms!important; }
        }
    </style>
    @stack('styles')
</head>

@php
    $user = Auth::user();
    $name = $user->name ?? 'Kader Posyandu';
    $initial = strtoupper(substr($name, 0, 1));
    $profileUrl = \Illuminate\Support\Facades\Route::has('kader.profile.index') ? route('kader.profile.index') : '#';
    $unread = 0;

    if (class_exists('\App\Models\Notifikasi') && Auth::check()) {
        $unread = \App\Models\Notifikasi::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }
@endphp

<body x-data="layoutKader()" x-init="init()" @close-sidebar.window="closeSide()">
    <div class="bgfx"></div>
    <div class="gridfx"></div>
    <div class="loader-bar"></div>
    <div class="loader-toast">
        <span class="loader-spin"></span>
        <span class="text-[12px] font-black tracking-[.03em]">Memuat...</span>
    </div>

    
    <div class="shell">
        <aside class="sidebar" :class="{ 'open': sideOpen, 'collapsed': sideMini }">
            @include('partials.sidebar.kader')
        </aside>

        <div class="content" :class="{ 'collapsed': sideMini }">
            <header class="topbar">
                <div class="top-left">
                    <button type="button" class="icon-btn mobile-only" @click="openSide()" aria-label="Buka sidebar">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>
                    <button type="button" class="icon-btn desktop-only" @click="toggleMini()" aria-label="Sembunyikan sidebar">
                        <i class="fa-solid" :class="sideMini ? 'fa-angles-right' : 'fa-angles-left'"></i>
                    </button>
                </div>

                <div class="top-right">
                    <div class="chip">
                        <i class="fa-solid fa-user-nurse"></i>
                        <span>Kader Workspace</span>
                    </div>

                    <div class="notif-wrap">
                        <button type="button" class="notif-btn" @click="toggleNotif()" aria-label="Notifikasi">
                            <i class="fa-regular fa-bell"></i>
                            @if($unread > 0)
                                <span class="notif-dot"></span>
                            @endif
                        </button>

                        <div x-cloak x-show="notifOpen" @click.outside="notifOpen = false" x-transition.opacity.scale.95.duration.140ms class="dropdown">
                            <div class="drop-head">
                                <div class="avatar"><i class="fa-regular fa-bell"></i></div>
                                <div>
                                    <p class="drop-title">Pemberitahuan</p>
                                    <p class="drop-sub">{{ $unread }} Pesan Baru</p>
                                </div>
                            </div>
                            <div id="notifList" class="max-h-[300px] overflow-y-auto">
                                <p class="py-7 text-center text-xs font-bold text-slate-400">Belum ada notifikasi.</p>
                            </div>
                        </div>
                    </div>

                    <div class="profile-wrap">
                        <button type="button" class="profile-btn" @click="profileOpen = !profileOpen; notifOpen = false">
                            <div class="avatar">
                                @if(!empty($user?->foto))
                                    <img src="{{ asset('storage/' . $user->foto) }}" alt="Foto Profil">
                                @else
                                    {{ $initial }}
                                @endif
                            </div>
                            <div class="profile-meta">
                                <div class="profile-name">{{ $name }}</div>
                                <div class="profile-role">Kader</div>
                            </div>
                        </button>

                        <div x-cloak x-show="profileOpen" @click.outside="profileOpen = false" x-transition.opacity.scale.95.duration.140ms class="dropdown">
                            <div class="drop-head">
                                <div class="avatar">
                                    @if(!empty($user?->foto))
                                        <img src="{{ asset('storage/' . $user->foto) }}" alt="Foto Profil">
                                    @else
                                        {{ $initial }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="drop-title truncate">{{ $name }}</p>
                                    <p class="drop-sub">Petugas Kader</p>
                                </div>
                            </div>

                            @if($profileUrl !== '#')
                                <a href="{{ $profileUrl }}" class="drop-link">
                                    <i class="fa-regular fa-user"></i>
                                    Profil Saya
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="js-logout-form">
                                @csrf
                                <button type="submit" class="drop-logout">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="main">
                <div class="main-inner">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        function layoutKader() {
            return {
                sideOpen: false,
                sideMini: false,
                notifOpen: false,
                profileOpen: false,

                init() {
                    try {
                        this.sideMini = localStorage.getItem('pc_kader_side_mini') === '1';
                    } catch (e) {}

                    this.$watch('sideOpen', value => {
                        document.body.classList.toggle('locked', value && window.innerWidth < 1024);
                    });

                    window.addEventListener('resize', () => {
                        if (window.innerWidth >= 1024) this.closeSide();
                    });

                    window.addEventListener('keydown', event => {
                        if (event.key === 'Escape') {
                            this.closeSide();
                            this.notifOpen = false;
                            this.profileOpen = false;
                        }
                    });
                },

                openSide() {
                    this.sideOpen = true;
                },

                closeSide() {
                    this.sideOpen = false;
                    document.body.classList.remove('locked');
                },

                toggleMini() {
                    this.sideMini = !this.sideMini;

                    try {
                        localStorage.setItem('pc_kader_side_mini', this.sideMini ? '1' : '0');
                    } catch (e) {}
                },

                toggleNotif() {
                    this.notifOpen = !this.notifOpen;
                    this.profileOpen = false;

                    if (this.notifOpen) this.loadNotif();
                },

                loadNotif() {
                    @if(\Illuminate\Support\Facades\Route::has('kader.notifikasi.fetch'))
                        fetch("{{ route('kader.notifikasi.fetch') }}", {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.html) {
                                    const list = document.getElementById('notifList');
                                    if (list) list.innerHTML = data.html;
                                }
                            })
                            .catch(() => {});
                    @endif
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            let loaderTimer = null;

            const showLoader = () => {
                clearTimeout(loaderTimer);
                body.classList.add('loading');

                loaderTimer = setTimeout(() => {
                    body.classList.remove('loading');
                }, 1200);
            };

            const hideLoader = () => {
                clearTimeout(loaderTimer);
                body.classList.remove('loading');
                body.classList.remove('locked');
            };

            window.nexusAlert = function (title, text, type = 'success') {
                Swal.fire({
                    title,
                    text,
                    icon: type,
                    confirmButtonText: 'MENGERTI',
                    customClass: {
                        popup: 'nexus-swal',
                        confirmButton: 'btn-nexus-confirm'
                    },
                    buttonsStyling: false
                });
            };

            window.nexusConfirm = function (options = {}) {
                return Swal.fire({
                    title: options.title || 'Konfirmasi',
                    text: options.text || 'Data akan diproses.',
                    icon: options.icon || 'warning',
                    showCancelButton: true,
                    confirmButtonText: options.confirmText || 'LANJUTKAN',
                    cancelButtonText: options.cancelText || 'BATAL',
                    customClass: {
                        popup: 'nexus-swal',
                        confirmButton: 'btn-nexus-confirm',
                        cancelButton: 'btn-nexus-cancel'
                    },
                    buttonsStyling: false
                });
            };

            const realNavigation = link => {
                const rawHref = link.getAttribute('href') || '';

                if (
                    rawHref === '#' ||
                    rawHref.endsWith('#') ||
                    rawHref.startsWith('#') ||
                    rawHref.startsWith('javascript:') ||
                    rawHref.startsWith('mailto:') ||
                    rawHref.startsWith('tel:') ||
                    link.hasAttribute('download')
                ) {
                    return false;
                }

                try {
                    const url = new URL(rawHref, window.location.href);
                    const now = window.location.pathname + window.location.search;
                    const target = url.pathname + url.search;

                    return url.origin === window.location.origin && now !== target;
                } catch (e) {
                    return true;
                }
            };

            document.addEventListener('click', event => {
                const link = event.target.closest('a[href]');

                if (
                    !link ||
                    event.ctrlKey ||
                    event.metaKey ||
                    event.shiftKey ||
                    event.altKey ||
                    event.defaultPrevented
                ) {
                    return;
                }

                if (realNavigation(link)) showLoader();
            });

            document.querySelectorAll('.js-logout-form').forEach(form => {
                form.addEventListener('submit', event => {
                    if (form.dataset.confirmed === '1') return;

                    event.preventDefault();

                    nexusConfirm({
                        title: 'Keluar dari akun?',
                        text: 'Sesi kamu akan ditutup dan kamu harus login ulang untuk masuk lagi.',
                        icon: 'warning',
                        confirmText: 'YA, KELUAR',
                        cancelText: 'BATAL'
                    }).then(result => {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = '1';
                            showLoader();
                            form.submit();
                        }
                    });
                });
            });

            document.addEventListener('submit', event => {
                if (!event.target.classList.contains('js-logout-form')) showLoader();
            });

            window.addEventListener('pageshow', hideLoader);
            window.addEventListener('load', hideLoader);

            @if(session('success'))
                setTimeout(() => nexusAlert('Berhasil!', "{{ session('success') }}", 'success'), 180);
            @endif

            @if(session('error'))
                setTimeout(() => nexusAlert('Perhatian!', "{{ session('error') }}", 'error'), 180);
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>