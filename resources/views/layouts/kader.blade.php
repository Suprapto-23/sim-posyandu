<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kader Workspace') | PosyanduCare</title>

    {{--
        pc_from_login  → loading screen masuk (login)
        pc_doing_logout → loading screen keluar (logout)
        Navigasi biasa  → TIDAK ada loading screen, cukup progress bar tipis
    --}}
    <script>
        (function () {
            try {
                var fl = sessionStorage.getItem('pc_from_login') === '1';
                document.documentElement.classList.add(fl ? 'pc-from-login' : 'pc-normal-entry');
            } catch (e) {
                document.documentElement.classList.add('pc-normal-entry');
            }
            if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
        })();
    </script>

    <meta name="theme-color" content="#f8fffc">
    <link rel="icon" type="image/png" href="{{ asset('img/logo.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

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

        body.locked { overflow:hidden !important; touch-action:none; }
        [x-cloak] { display:none!important; }
        button,input,select,textarea { font-family:inherit; outline:none; }
        ::selection { background:rgba(16,185,129,.18); color:var(--g900); }
        ::-webkit-scrollbar { width:6px; height:6px; }
        ::-webkit-scrollbar-thumb { background:rgba(148,163,184,.42); border-radius:999px; }

        /* ─── Background FX ─────────────────────────────── */
        .bgfx,.gridfx { position:fixed; inset:0; pointer-events:none; }
        .bgfx { z-index:0; overflow:hidden; }
        .bgfx:before,.bgfx:after {
            content:""; position:absolute; width:440px; height:440px; border-radius:999px; filter:blur(68px);
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

        /* ─── Progress bar navigasi (ringan, tanpa spinner) ─ */
        #pc-nav-bar {
            position:fixed; top:0; left:0; right:0; z-index:9990;
            height:2.5px; pointer-events:none;
            transform:scaleX(0); transform-origin:left;
            background:linear-gradient(90deg,var(--g700),var(--g500),var(--a500));
            transition:transform .08s linear, opacity .22s ease;
            opacity:0;
        }
        #pc-nav-bar.running {
            opacity:1;
            animation:navProgress 1.6s var(--ease) forwards;
        }
        #pc-nav-bar.done {
            transform:scaleX(1);
            opacity:0;
            transition:transform .18s ease, opacity .24s ease .1s;
        }
        @keyframes navProgress {
            0%   { transform:scaleX(0); }
            30%  { transform:scaleX(.45); }
            70%  { transform:scaleX(.75); }
            90%  { transform:scaleX(.9); }
            100% { transform:scaleX(.92); }
        }

        /* ─── Loading screen HANYA login/logout ─────────── */
        #pcKaderLoader {
            position:fixed; inset:0; z-index:99999;
            display:flex; align-items:center; justify-content:center;
            visibility:hidden; pointer-events:none;
        }
        #pcKaderLoader.show {
            visibility:visible; pointer-events:auto;
        }
        .ld-veil {
            position:absolute; inset:0;
            background:rgba(240,255,248,.82);
            backdrop-filter:blur(10px) saturate(1.1);
            -webkit-backdrop-filter:blur(10px) saturate(1.1);
            opacity:0; transition:opacity .2s ease;
        }
        #pcKaderLoader.show .ld-veil { opacity:1; }
        .ld-panel {
            position:relative; z-index:2;
            min-width:220px; padding:30px 40px 28px;
            border-radius:24px;
            background:rgba(255,255,255,.97);
            border:1px solid rgba(16,185,129,.13);
            box-shadow:0 22px 54px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.92);
            display:flex; flex-direction:column; align-items:center; text-align:center;
            opacity:0; transform:translateY(12px) scale(.96);
            transition:opacity .24s var(--ease) .04s, transform .24s var(--ease) .04s;
            will-change:opacity,transform;
        }
        #pcKaderLoader.show .ld-panel { opacity:1; transform:none; }
        .ld-orbit {
            position:relative; width:62px; height:62px; margin:0 auto 17px;
            display:flex; align-items:center; justify-content:center;
        }
        .ld-ring {
            position:absolute; inset:0; border-radius:50%;
            border:2.25px solid transparent; will-change:transform;
        }
        .ld-ring:nth-child(1) { border-top-color:var(--g500); border-right-color:rgba(16,185,129,.25); animation:spinR .78s linear infinite; }
        .ld-ring:nth-child(2) { inset:8px; border-bottom-color:var(--g400); border-left-color:rgba(52,211,153,.25); animation:spinR 1.15s linear infinite reverse; }
        .ld-ring:nth-child(3) { inset:17px; border-top-color:var(--a500); border-right-color:rgba(245,158,11,.22); animation:spinR 1.65s linear infinite; }
        @keyframes spinR { to { transform:rotate(360deg); } }
        .ld-heart {
            position:relative; z-index:2; font-size:17px; color:var(--g600);
            animation:heartBeat 1.08s ease-in-out infinite; will-change:transform;
        }
        @keyframes heartBeat {
            0%,100% { transform:scale(1); opacity:.9; }
            18%     { transform:scale(1.16); }
            36%     { transform:scale(1); }
            52%     { transform:scale(1.07); }
        }
        .ld-name  { font-size:15px; font-weight:900; color:var(--s900); margin-bottom:2px; }
        .ld-label { font-size:10.5px; font-weight:800; color:var(--s500); text-transform:uppercase; letter-spacing:.6px; margin-bottom:14px; }
        .ld-dots  { display:flex; gap:5px; align-items:center; justify-content:center; }
        .ld-dot   { width:6px; height:6px; border-radius:50%; background:var(--g400); animation:dotPop .72s ease-in-out infinite both; will-change:transform,opacity; }
        .ld-dot:nth-child(1){ animation-delay:0s; }
        .ld-dot:nth-child(2){ animation-delay:.12s; background:var(--g500); }
        .ld-dot:nth-child(3){ animation-delay:.24s; background:var(--g600); }
        .ld-dot:nth-child(4){ animation-delay:.36s; background:var(--a500); }
        @keyframes dotPop {
            0%,80%,100% { transform:scale(.55); opacity:.35; }
            40%         { transform:scale(1.12); opacity:1; }
        }

        /* ─── Shell & Sidebar ────────────────────────────── */
        .shell { position:relative; z-index:5; min-height:100vh; }
        .sidebar {
            position:fixed; inset:0 auto 0 0; z-index:90;
            width:var(--open); height:100dvh; padding:12px;
            transform:translateX(-105%);
            transition:width .22s var(--ease), transform .2s var(--ease);
            will-change:width,transform;
        }
        .sidebar.open { transform:translateX(0); }
        .content {
            min-height:100vh; display:flex; flex-direction:column;
            transition:margin-left .22s var(--ease);
        }
        .backdrop {
            position:fixed; inset:0; z-index:60;
            background:rgba(15,23,42,.34);
        }

        .side-card {
            height:calc(100dvh - 24px);
            display:flex; flex-direction:column; overflow:hidden; border-radius:26px;
            background:linear-gradient(180deg,#fff 0%,#f8fffc 100%);
            border:1px solid rgba(226,232,240,.82);
            box-shadow:0 22px 55px rgba(15,23,42,.13), inset 0 1px 0 rgba(255,255,255,.95);
            transition:.20s var(--ease);
        }
        .side-card,.side-card * { filter:none; text-shadow:none; }
        .side-head { min-height:82px; padding:18px; display:grid; place-items:center; flex-shrink:0; }
        .side-logo img { width:142px; max-height:54px; object-fit:contain; transition:.20s var(--ease); filter:none; }
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
            transition:.20s var(--ease);
        }
        .avatar {
            width:42px; height:42px; display:grid; place-items:center; flex-shrink:0; overflow:hidden;
            border-radius:15px; color:#fff; font-weight:900;
            background:linear-gradient(135deg,var(--g600),var(--g400));
            box-shadow:0 12px 22px rgba(16,185,129,.18);
        }
        .avatar img { width:100%; height:100%; object-fit:cover; }
        .side-user h4 { margin:0; max-width:150px; color:var(--g900); font-size:13px; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .side-user p  { margin:3px 0 0; color:var(--s500); font-size:11px; font-weight:700; }
        .side-scroll { flex:1; min-height:0; overflow-y:auto; overflow-x:hidden; padding:0 12px 14px; scrollbar-width:none; }
        .side-scroll::-webkit-scrollbar { display:none; }
        .side-user-info,.menu-title,.menu-text,.caret,.submenu { transition:opacity .16s ease,width .22s var(--ease),max-width .22s var(--ease); overflow:hidden; }

        .menu-group { margin-bottom:16px; }
        .menu-title { margin:0 0 8px 8px; color:var(--s400); font-size:10px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; white-space:nowrap; }
        .menu-list  { display:grid; gap:5px; }
        .menu-item {
            position:relative; min-height:43px; width:100%; padding:10px 12px;
            display:flex; align-items:center; gap:12px; border:1px solid transparent;
            border-radius:15px; color:var(--s600); background:transparent;
            text-decoration:none; font-size:13px; font-weight:800; cursor:pointer;
            transition:.14s var(--ease);
        }
        .menu-item:hover { color:var(--g700); background:rgba(236,253,245,.86); transform:translateX(2px); }
        .menu-item.active { color:var(--g800); background:#fff; border-color:rgba(209,250,229,.86); box-shadow:0 9px 18px rgba(16,185,129,.07); }
        .menu-item.active:before { content:""; position:absolute; left:0; top:11px; bottom:11px; width:4px; border-radius:0 999px 999px 0; background:var(--g500); }
        .menu-icon { width:22px; display:grid; place-items:center; flex-shrink:0; color:var(--s400); transition:.14s var(--ease); }
        .menu-item:hover .menu-icon,.menu-item.active .menu-icon { color:var(--g600); }
        .menu-text { flex:1; min-width:0; text-align:left; white-space:nowrap; text-overflow:ellipsis; }
        .caret { color:var(--s400); font-size:11px; transition:transform .22s var(--ease); }
        .rotate-180 { transform:rotate(180deg); }
        .submenu { max-height:0; margin-left:24px; padding-left:13px; border-left:1px dashed rgba(203,213,225,.9); }
        .submenu.open { max-height:180px; margin-top:5px; }
        .submenu a {
            min-height:34px; padding:8px 10px; display:flex; align-items:center; gap:9px;
            border-radius:12px; color:var(--s500); text-decoration:none; font-size:12px; font-weight:800; transition:.12s ease;
        }
        .submenu a:hover,.submenu a.active { color:var(--g800); background:rgba(236,253,245,.86); }
        .dot { width:6px; height:6px; border-radius:999px; background:var(--s300); }
        .submenu a.active .dot,.submenu a:hover .dot { background:var(--g500); }
        .submenu-icon { width:18px; display:grid; place-items:center; color:var(--s400); font-size:11px; flex-shrink:0; }
        .submenu a:hover .submenu-icon,.submenu a.active .submenu-icon { color:var(--g600); }
        .logout { color:#dc2626; }
        .logout:hover { background:#fff1f2; color:#b91c1c; }

        /* ─── Topbar ─────────────────────────────────────── */
        .topbar {
            position:sticky; top:12px; z-index:40; min-height:68px;
            margin:16px 22px 0; padding:10px 12px;
            display:flex; align-items:center; justify-content:space-between; gap:12px;
            border-radius:24px; background:rgba(255,255,255,.88);
            border:1px solid rgba(226,232,240,.84);
            box-shadow:0 8px 24px rgba(15,23,42,.06), inset 0 1px 0 rgba(255,255,255,.86);
            /* Kurangi backdrop-filter agar lebih ringan */
            backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
        }
        .top-left,.top-right { display:flex; align-items:center; gap:10px; min-width:0; }
        .top-right { margin-left:auto; }
        .icon-btn,.notif-btn,.profile-btn {
            height:46px; border:1px solid rgba(226,232,240,.86);
            background:rgba(255,255,255,.9); color:var(--s600);
            box-shadow:0 4px 12px rgba(15,23,42,.04), inset 0 1px 0 rgba(255,255,255,.82);
            cursor:pointer; transition:.14s var(--ease);
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

        /* ─── Main content ───────────────────────────────── */
        .main { width:100%; max-width:1480px; margin:0 auto; padding:24px 24px 42px; flex:1; }
        .main-inner { animation:contentIn .18s var(--ease) both; }
        @keyframes contentIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

        /* View Transitions API untuk perpindahan mulus */
        @supports (view-transition-name: none) {
            ::view-transition-old(root) { animation:vtOut .16s ease both; }
            ::view-transition-new(root) { animation:vtIn  .2s var(--ease) both; }
            @keyframes vtOut { to { opacity:0; transform:translateY(-4px); } }
            @keyframes vtIn  { from { opacity:0; transform:translateY(6px); } }
        }

        .admin-card,.stat-card,.dashboard-card,.content-card,.table-card,.kader-card {
            border-radius:24px; background:rgba(255,255,255,.84);
            border:1px solid rgba(226,232,240,.84);
            box-shadow:0 18px 44px rgba(15,23,42,.052), inset 0 1px 0 rgba(255,255,255,.84);
        }

        /* ─── SweetAlert ─────────────────────────────────── */
        .swal2-popup.nexus-swal {
            border-radius:28px!important; font-family:'Plus Jakarta Sans',sans-serif!important;
            background:rgba(255,255,255,.98)!important; border:1px solid rgba(226,232,240,.86)!important;
            box-shadow:0 26px 70px rgba(15,23,42,.16)!important;
        }
        .btn-nexus-confirm,.btn-nexus-cancel { border:0!important; border-radius:15px!important; padding:12px 24px!important; font-weight:900!important; }
        .btn-nexus-confirm { color:#fff!important; background:linear-gradient(135deg,var(--g500),var(--g700))!important; }
        .btn-nexus-cancel  { color:var(--s500)!important; background:var(--s100)!important; }

        /* ─── Responsive ─────────────────────────────────── */
        @media (min-width:1024px) {
            .sidebar { transform:translateX(0); }
            .sidebar.collapsed { width:var(--mini); }
            .content { margin-left:var(--open); }
            .content.collapsed { margin-left:var(--mini); }
            .desktop-only { display:grid; }
            .mobile-only { display:none; }

            .sidebar.collapsed .side-card { border-radius:24px; }
            .sidebar.collapsed .side-logo img { width:42px; transform:scale(.96); }
            .sidebar.collapsed .side-user { justify-content:center; margin-inline:10px; padding:10px; gap:0; }
            .sidebar.collapsed .side-user-info,
            .sidebar.collapsed .menu-title,
            .sidebar.collapsed .menu-text,
            .sidebar.collapsed .caret { opacity:0; width:0; max-width:0; flex:0 0 0; pointer-events:none; }
            .sidebar.collapsed .side-scroll { padding-inline:9px; }
            .sidebar.collapsed .menu-group { margin-bottom:12px; }
            .sidebar.collapsed .menu-item { justify-content:center; padding-inline:0; gap:0; }
            .sidebar.collapsed .menu-icon { width:44px; font-size:15px; }
            .sidebar.collapsed .menu-item:hover { transform:translateY(-1px); }
            .sidebar.collapsed .submenu { display:none; }
        }

        @media (max-width:1023px) {
            .sidebar { width:min(300px, calc(100vw - 24px)); padding:10px; transform:translateX(-110%); transition:transform .26s var(--ease); }
            .sidebar.open { transform:translateX(0); }
            .side-card { height:calc(100dvh - 20px); border-radius:23px; }
            .side-close { display:grid; }
            .topbar { top:10px; min-height:64px; margin:10px 10px 0; padding:9px 10px; border-radius:22px; }
            .chip { display:none; }
            .main { padding:20px 12px 32px; }
            body.locked .content { pointer-events:none; }
            .side-card,.side-card * { filter:none!important; text-shadow:none!important; }
        }

        @media (max-width:640px) {
            .profile-meta { display:none; }
            .profile-btn { width:46px; padding:3px; }
            .dropdown { width:min(270px, calc(100vw - 24px)); }
        }

        @media (max-width:390px) {
            .ld-panel { padding:26px 22px 24px; min-width:unset; width:86vw; }
            .ld-orbit { width:58px; height:58px; margin-bottom:15px; }
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
        $unread = \App\Models\Notifikasi::where('user_id', Auth::id())->where('is_read', false)->count();
    }
@endphp

<body x-data="layoutKader()" x-init="init()" @close-sidebar.window="closeSide()">
    <div class="bgfx"></div>
    <div class="gridfx"></div>

    {{-- Progress bar navigasi — ringan, tanpa spinner --}}
    <div id="pc-nav-bar" aria-hidden="true"></div>

    {{-- Loading screen HANYA untuk login masuk & logout keluar --}}
    <div id="pcKaderLoader" role="status" aria-label="Memuat, harap tunggu..." aria-live="polite">
        <div class="ld-veil"></div>
        <div class="ld-panel">
            <div class="ld-orbit">
                <div class="ld-ring"></div>
                <div class="ld-ring"></div>
                <div class="ld-ring"></div>
                <i class="fa-solid fa-heart-pulse ld-heart"></i>
            </div>
            <div class="ld-name">PosyanduCare</div>
            <div id="pcKaderLoaderLabel" class="ld-label">Memuat Halaman</div>
            <div class="ld-dots">
                <span class="ld-dot"></span>
                <span class="ld-dot"></span>
                <span class="ld-dot"></span>
                <span class="ld-dot"></span>
            </div>
        </div>
    </div>

    <div class="shell">
        <div
            x-cloak
            x-show="sideOpen"
            x-transition.opacity.duration.140ms
            @click="closeSide()"
            class="backdrop lg:hidden"
            aria-hidden="true"
        ></div>

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
                        <div x-cloak x-show="notifOpen" @click.outside="notifOpen = false" x-transition.opacity.scale.95.duration.120ms class="dropdown">
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
                        <div x-cloak x-show="profileOpen" @click.outside="profileOpen = false" x-transition.opacity.scale.95.duration.120ms class="dropdown">
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
        /* ─── Alpine layout state ───────────────────────── */
        function layoutKader() {
            return {
                sideOpen:    false,
                sideMini:    false,
                notifOpen:   false,
                profileOpen: false,

                init() {
                    try { this.sideMini = localStorage.getItem('pc_kader_side_mini') === '1'; } catch (e) {}
                    this.$watch('sideOpen', v => {
                        document.body.classList.toggle('locked', v && window.innerWidth < 1024);
                    });
                    window.addEventListener('resize', () => { if (window.innerWidth >= 1024) this.closeSide(); });
                    window.addEventListener('keydown', e => {
                        if (e.key === 'Escape') { this.closeSide(); this.notifOpen = false; this.profileOpen = false; }
                    });
                },
                openSide()   { this.sideOpen = true; },
                closeSide()  { this.sideOpen = false; document.body.classList.remove('locked'); },
                toggleMini() {
                    this.sideMini = !this.sideMini;
                    try { localStorage.setItem('pc_kader_side_mini', this.sideMini ? '1' : '0'); } catch (e) {}
                },
                toggleNotif() {
                    this.notifOpen = !this.notifOpen;
                    this.profileOpen = false;
                    if (this.notifOpen) this.loadNotif();
                },
                loadNotif() {
                    @if(\Illuminate\Support\Facades\Route::has('kader.notifikasi.fetch'))
                        fetch("{{ route('kader.notifikasi.fetch') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(data => { if (data.html) { const el = document.getElementById('notifList'); if (el) el.innerHTML = data.html; } })
                            .catch(() => {});
                    @endif
                }
            }
        }

        /* ─── DOMContentLoaded: navigasi, loader login/logout ── */
        document.addEventListener('DOMContentLoaded', () => {
            const html   = document.documentElement;
            const body   = document.body;
            const loader = document.getElementById('pcKaderLoader');
            const label  = document.getElementById('pcKaderLoaderLabel');
            const navBar = document.getElementById('pc-nav-bar');

            /* -- Progress bar ringan (bukan full-screen loader) -- */
            let navTimer = null;
            const navStart = () => {
                clearTimeout(navTimer);
                navBar.classList.remove('done');
                navBar.classList.add('running');
            };
            const navDone = () => {
                navBar.classList.remove('running');
                navBar.classList.add('done');
                navTimer = setTimeout(() => navBar.classList.remove('done'), 400);
            };

            /* -- Full-screen loader: hanya login & logout -- */
            let ldTimer = null;
            const showLoader = (msg = 'Memuat Halaman') => {
                clearTimeout(ldTimer);
                if (label) label.textContent = msg;
                body.classList.add('locked');
                loader?.classList.add('show');
                ldTimer = setTimeout(hideLoader, 5000); // safety fallback
            };
            const hideLoader = () => {
                clearTimeout(ldTimer);
                body.classList.remove('locked');
                loader?.classList.remove('show');
            };

            /* -- Login masuk -- */
            if (html.classList.contains('pc-from-login')) {
                showLoader('Membuka Workspace');
                setTimeout(() => {
                    try { sessionStorage.removeItem('pc_from_login'); } catch (e) {}
                    html.classList.replace('pc-from-login', 'pc-normal-entry');
                    hideLoader();
                    window.scrollTo(0, 0);
                }, 900);
            }

            /* -- Navigasi biasa: hanya progress bar, TANPA loader -- */
            const isRealNav = link => {
                const href = link.getAttribute('href') || '';
                if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:') ||
                    href.startsWith('mailto:') || href.startsWith('tel:') || link.hasAttribute('download')) return false;
                try {
                    const url = new URL(href, location.href);
                    return url.origin === location.origin && (url.pathname + url.search) !== (location.pathname + location.search);
                } catch { return true; }
            };

            document.addEventListener('click', e => {
                const link = e.target.closest('a[href]');
                if (!link || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.defaultPrevented) return;
                if (isRealNav(link)) navStart();
            });

            /* -- View Transitions API (browser modern) -- */
            if (document.startViewTransition) {
                document.addEventListener('click', e => {
                    const link = e.target.closest('a[href]');
                    if (!link || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.defaultPrevented) return;
                    if (!isRealNav(link)) return;
                    // Biarkan browser handle, VT akan aktif otomatis
                }, { capture: true });
            }

            window.addEventListener('pageshow', navDone);
            window.addEventListener('load', navDone);

            /* -- SweetAlert helpers -- */
            window.nexusAlert = (title, text, type = 'success') =>
                Swal.fire({ title, text, icon: type, confirmButtonText: 'MENGERTI',
                    customClass: { popup: 'nexus-swal', confirmButton: 'btn-nexus-confirm' },
                    buttonsStyling: false });

            window.nexusConfirm = (opts = {}) =>
                Swal.fire({
                    title: opts.title || 'Konfirmasi', text: opts.text || 'Data akan diproses.',
                    icon: opts.icon || 'warning', showCancelButton: true,
                    confirmButtonText: opts.confirmText || 'LANJUTKAN', cancelButtonText: opts.cancelText || 'BATAL',
                    customClass: { popup: 'nexus-swal', confirmButton: 'btn-nexus-confirm', cancelButton: 'btn-nexus-cancel' },
                    buttonsStyling: false });

            /* -- Logout: tampilkan full-screen loader -- */
            document.querySelectorAll('.js-logout-form').forEach(form => {
                form.addEventListener('submit', e => {
                    if (form.dataset.confirmed === '1') return;
                    e.preventDefault();
                    nexusConfirm({
                        title: 'Keluar dari akun?',
                        text: 'Sesi kamu akan ditutup dan kamu harus login ulang untuk masuk lagi.',
                        icon: 'warning', confirmText: 'YA, KELUAR', cancelText: 'BATAL'
                    }).then(r => {
                        if (r.isConfirmed) {
                            form.dataset.confirmed = '1';
                            showLoader('Keluar Sistem');
                            form.submit();
                        }
                    });
                });
            });

            /* -- Session flash alerts -- */
            @if(session('success'))
                setTimeout(() => nexusAlert('Berhasil!', "{{ addslashes(session('success')) }}", 'success'), 120);
            @endif
            @if(session('error'))
                setTimeout(() => nexusAlert('Perhatian!', "{{ addslashes(session('error')) }}", 'error'), 120);
            @endif
        });
    </script>

@stack('modals')
@stack('scripts')
</body>
</html>