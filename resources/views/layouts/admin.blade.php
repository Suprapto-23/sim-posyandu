
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') | PosyanduCare</title>

    <script>
        (function () {
            try {
                document.documentElement.classList.add(
                    sessionStorage.getItem('pc_from_login') === '1' ? 'pc-from-login' : 'pc-normal-entry'
                );
            } catch (e) {
                document.documentElement.classList.add('pc-normal-entry');
            }
            if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
        })();
    </script>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>

    <style type="text/tailwindcss">
        @theme { --font-sans:'Plus Jakarta Sans',sans-serif; --font-poppins:'Poppins',sans-serif; }
    </style>

    <style>
        /* ── Variables ─────────────────────────────── */
        :root {
            --g950:#052e24;--g900:#064e3b;--g800:#065f46;--g700:#047857;
            --g600:#059669;--g500:#10b981;--g400:#34d399;
            --a500:#f59e0b;--a400:#fbbf24;
            --s950:#020617;--s900:#0f172a;--s800:#1e293b;--s700:#334155;
            --s600:#475569;--s500:#64748b;--s400:#94a3b8;--s300:#cbd5e1;
            --s200:#e2e8f0;--s100:#f1f5f9;--s50:#f8fafc;
            --sw:292px;
            --ease:cubic-bezier(.16,1,.3,1);
            --ease2:cubic-bezier(.22,1,.36,1);
        }

        *,*::before,*::after { box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
        html,body { width:100%; min-height:100%; margin:0; padding:0; overflow-x:hidden; scroll-behavior:auto!important; }
        body {
            font-family:'Plus Jakarta Sans',system-ui,sans-serif;
            color:var(--s700);
            background:
                radial-gradient(circle at 7% 0%,rgba(16,185,129,.08),transparent 28%),
                radial-gradient(circle at 95% 7%,rgba(245,158,11,.05),transparent 28%),
                radial-gradient(circle at 85% 92%,rgba(20,184,166,.07),transparent 30%),
                linear-gradient(135deg,#f8fffc 0%,#f8fafc 48%,#effbf6 100%);
            -webkit-font-smoothing:antialiased;
        }
        body.locked { overflow:hidden!important; touch-action:none; }
        h1,h2,h3,h4,h5,h6 { font-family:'Poppins',system-ui,sans-serif; }
        button,input,select,textarea { font-family:inherit; }
        ::selection { background:rgba(16,185,129,.18); color:var(--g900); }
        ::-webkit-scrollbar { width:7px; height:7px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:rgba(148,163,184,.45); border-radius:999px; }
        ::-webkit-scrollbar-thumb:hover { background:rgba(100,116,139,.65); }

        /* ── Background ────────────────────────────── */
        .admin-bg { position:fixed; inset:0; z-index:0; pointer-events:none; overflow:hidden; }
        .admin-bg::before,.admin-bg::after { content:""; position:absolute; border-radius:999px; }
        .admin-bg::before { width:420px; height:420px; left:-190px; top:-180px; background:rgba(16,185,129,.10); filter:blur(70px); }
        .admin-bg::after  { width:400px; height:400px; right:-180px; bottom:-170px; background:rgba(20,184,166,.085); filter:blur(72px); }
        .admin-grid { position:fixed; inset:0; z-index:1; pointer-events:none; opacity:.065;
            background-image:linear-gradient(rgba(15,23,42,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(15,23,42,.035) 1px,transparent 1px);
            background-size:72px 72px; }

        /* ── Nav progress bar (navigasi biasa) ──────── */
        #pc-nav-bar {
            position:fixed; top:0; left:0; right:0; z-index:9990;
            height:2.5px; pointer-events:none;
            transform:scaleX(0); transform-origin:left;
            background:linear-gradient(90deg,var(--g700),var(--g500),var(--a500));
            transition:transform .08s linear,opacity .22s ease; opacity:0;
        }
        #pc-nav-bar.running { opacity:1; animation:navProg 1.6s var(--ease) forwards; }
        #pc-nav-bar.done   { transform:scaleX(1); opacity:0; transition:transform .18s ease,opacity .24s ease .1s; }
        @keyframes navProg {
            0%{transform:scaleX(0)} 30%{transform:scaleX(.45)}
            70%{transform:scaleX(.75)} 90%{transform:scaleX(.9)} 100%{transform:scaleX(.92)}
        }

        /* ── Full-screen loader (login masuk & logout) ─ */
        #pcAdminLoader {
            position:fixed; inset:0; z-index:99999;
            display:flex; align-items:center; justify-content:center;
            visibility:hidden; pointer-events:none;
        }
        #pcAdminLoader.show { visibility:visible; pointer-events:auto; }
        .ld-veil {
            position:absolute; inset:0;
            background:rgba(240,255,248,.88);
            backdrop-filter:blur(16px) saturate(1.3); -webkit-backdrop-filter:blur(16px) saturate(1.3);
            opacity:0; transition:opacity .26s ease;
        }
        #pcAdminLoader.show .ld-veil { opacity:1; }
        .ld-panel {
            position:relative; z-index:2;
            background:rgba(255,255,255,.96);
            border:1px solid rgba(16,185,129,.13); border-radius:26px;
            padding:36px 48px 32px;
            box-shadow:0 28px 70px rgba(15,23,42,.13),inset 0 1px 0 rgba(255,255,255,.90);
            display:flex; flex-direction:column; align-items:center; text-align:center; min-width:256px;
            opacity:0; transform:translateY(18px) scale(.94);
            transition:opacity .30s var(--ease2) .06s,transform .30s var(--ease2) .06s;
        }
        #pcAdminLoader.show .ld-panel { opacity:1; transform:none; }
        .ld-orbit { position:relative; width:70px; height:70px; margin:0 auto 20px; display:flex; align-items:center; justify-content:center; }
        .ld-ring  { position:absolute; inset:0; border-radius:50%; border:2.5px solid transparent; }
        .ld-ring:nth-child(1){ border-top-color:var(--g500); border-right-color:rgba(16,185,129,.25); animation:spinR 1.1s linear infinite; }
        .ld-ring:nth-child(2){ inset:8px; border-bottom-color:var(--g400); border-left-color:rgba(52,211,153,.25); animation:spinR 1.7s linear infinite reverse; }
        .ld-ring:nth-child(3){ inset:18px; border-top-color:var(--a500); border-right-color:rgba(245,158,11,.22); animation:spinR 2.3s linear infinite; }
        @keyframes spinR { to{transform:rotate(360deg)} }
        .ld-heart { position:relative; z-index:2; font-size:18px; color:var(--g600); animation:hBeat 1.4s ease-in-out infinite; }
        @keyframes hBeat { 0%,100%{transform:scale(1);opacity:.9} 14%{transform:scale(1.2)} 28%{transform:scale(1)} 42%{transform:scale(1.1)} }
        .ld-name  { font-family:'Poppins',sans-serif; font-size:15px; font-weight:800; color:var(--s900); margin-bottom:2px; }
        .ld-label { font-size:10.5px; font-weight:700; color:var(--s500); text-transform:uppercase; letter-spacing:.6px; margin-bottom:16px; }
        .ld-dots  { display:flex; gap:5px; align-items:center; justify-content:center; }
        .ld-dot   { width:6px; height:6px; border-radius:50%; background:var(--g400); animation:dotP .9s ease-in-out infinite both; }
        .ld-dot:nth-child(1){animation-delay:0s}
        .ld-dot:nth-child(2){animation-delay:.18s;background:var(--g500)}
        .ld-dot:nth-child(3){animation-delay:.36s;background:var(--g600)}
        .ld-dot:nth-child(4){animation-delay:.54s;background:var(--a500)}
        @keyframes dotP { 0%,80%,100%{transform:scale(.55);opacity:.35} 40%{transform:scale(1.15);opacity:1} }

        /* ── SweetAlert nexus ──────────────────────── */
        .nexus-swal {
            border-radius:28px!important; font-family:'Plus Jakarta Sans',sans-serif!important;
            background:rgba(255,255,255,.98)!important; border:1px solid rgba(226,232,240,.86)!important;
            box-shadow:0 26px 70px rgba(15,23,42,.16)!important;
        }
        .nexus-confirm { border:0!important; border-radius:14px!important; padding:11px 28px!important; font-weight:900!important; color:#fff!important; background:linear-gradient(135deg,#047857,#10b981)!important; box-shadow:0 10px 22px rgba(5,150,105,.28)!important; }
        .nexus-cancel  { border:0!important; border-radius:14px!important; padding:11px 28px!important; font-weight:900!important; color:var(--s500)!important; background:var(--s100)!important; }

        /* ── Layout ────────────────────────────────── */
        .admin-shell { position:relative; z-index:10; min-height:100vh; }

        .admin-sidebar {
            position:fixed; inset:0 auto 0 0; z-index:70;
            width:var(--sw); height:100dvh; padding:14px;
            background:transparent; border-right:none; box-shadow:none;
            transform:translate3d(calc(-1 * var(--sw) - 18px),0,0);
            transition:transform .22s var(--ease);
            will-change:transform;
        }
        body.sidebar-open .admin-sidebar { transform:translate3d(0,0,0); }

        .admin-content {
            position:relative; min-height:100vh;
            display:flex; flex-direction:column;
            transition:margin-left .22s var(--ease);
            will-change:margin-left;
        }
        @media(min-width:1024px){
            body.sidebar-open .admin-content { margin-left:var(--sw); }
        }

        .mobile-overlay {
            position:fixed; inset:0; z-index:60;
            opacity:0; visibility:hidden; pointer-events:none;
            background:rgba(2,6,23,.30);
            transition:opacity .18s ease,visibility .18s ease;
        }
        body.sidebar-open .mobile-overlay { opacity:1; visibility:visible; pointer-events:auto; }
        @media(min-width:1024px){ .mobile-overlay{display:none!important} }

        .sidebar-close-btn {
            position:absolute; top:18px; right:18px; z-index:8;
            width:34px; height:34px; border:0; border-radius:14px;
            background:rgba(236,253,245,.94); color:var(--g700);
            display:none; align-items:center; justify-content:center;
            cursor:pointer; box-shadow:0 8px 20px rgba(15,23,42,.07),inset 0 1px 0 rgba(255,255,255,.85);
            transition:transform .16s var(--ease),background .16s ease,color .16s ease;
        }
        .sidebar-close-btn:hover { transform:translateY(-1px); background:#fff; color:#dc2626; }

        /* ── Sidebar card ──────────────────────────── */
        .admin-sidebar img,.pc-sidebar img { max-width:168px!important; max-height:92px!important; width:auto!important; height:auto!important; object-fit:contain!important; }

        .pc-sidebar {
            position:relative; width:100%; height:calc(100dvh - 28px);
            padding:24px 18px 18px; border-radius:28px;
            overflow-x:hidden; overflow-y:auto; overscroll-behavior:contain; scrollbar-width:thin;
            background:
                radial-gradient(circle at 50% 0%,rgba(236,253,245,.70),transparent 34%),
                linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,255,252,.94));
            border:1px solid rgba(226,232,240,.75);
            box-shadow:0 18px 52px rgba(15,23,42,.07),inset 0 1px 0 rgba(255,255,255,.95);
            backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px);
        }
        .pc-sidebar::-webkit-scrollbar { width:4px; }
        .pc-sidebar::-webkit-scrollbar-thumb { background:rgba(16,185,129,.20); border-radius:999px; }

        .pc-logo-area { position:relative; z-index:3; display:flex; justify-content:center; margin-bottom:22px; }
        .pc-logo-link { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; }
        .pc-logo      { width:154px!important; max-width:72%!important; max-height:86px!important; object-fit:contain!important; display:block; filter:drop-shadow(0 8px 16px rgba(15,23,42,.07)); }

        .pc-user-card {
            position:relative; z-index:3; display:flex; align-items:center; gap:13px;
            padding:14px; margin-bottom:24px; border-radius:22px;
            background:linear-gradient(135deg,rgba(255,255,255,.88),rgba(248,255,252,.78));
            border:1px solid rgba(209,250,229,.92);
            box-shadow:0 12px 28px rgba(15,23,42,.048),inset 0 1px 0 rgba(255,255,255,.95);
        }
        .pc-avatar {
            width:52px; height:52px; flex-shrink:0; border-radius:999px;
            background:linear-gradient(135deg,#10b981 0%,#34d399 45%,#f59e0b 100%);
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-weight:900; font-size:18px;
            box-shadow:0 10px 20px rgba(16,185,129,.16),inset 0 1px 0 rgba(255,255,255,.22);
        }
        .pc-user-info { flex:1; min-width:0; }
        .pc-user-info h4 { margin:0; color:#064e3b; font-size:13.5px; font-weight:900; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .pc-user-info p  { margin:3px 0 6px; color:#64748b; font-size:11px; font-weight:700; }
        .pc-online { display:inline-flex; align-items:center; gap:5px; padding:3px 8px; border-radius:999px; background:#ecfdf5; color:#059669; font-size:10px; font-weight:800; }
        .pc-online span { width:6px; height:6px; border-radius:999px; background:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.11); }

        .pc-menu-group { position:relative; z-index:3; margin-bottom:22px; }
        .pc-menu-title { margin:0 0 10px; padding-left:4px; color:#64748b; font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
        .pc-menu-list  { display:flex; flex-direction:column; gap:5px; }
        .pc-menu-item {
            position:relative; width:100%; min-height:42px;
            display:flex; align-items:center; gap:12px; padding:10px 13px;
            border:0; border-radius:13px; background:transparent; color:var(--s700);
            text-decoration:none; font-size:12.5px; font-weight:700; cursor:pointer;
            transition:background .14s ease,color .14s ease,transform .14s ease;
        }
        .pc-menu-item:hover { background:rgba(236,253,245,.90); color:var(--g700); transform:translateX(3px); }
        .pc-menu-item.active {
            background:linear-gradient(90deg,rgba(236,253,245,.98),rgba(255,255,255,.80));
            color:var(--g700); font-weight:900;
            box-shadow:0 8px 20px rgba(16,185,129,.07),inset 0 1px 0 rgba(255,255,255,.90);
        }
        .pc-menu-item.active::before {
            content:""; position:absolute; left:0; top:9px; bottom:9px; width:4px;
            border-radius:999px; background:linear-gradient(180deg,#10b981,#059669);
        }
        .pc-menu-icon { width:22px; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:var(--s500); font-size:13px; transition:color .14s ease; }
        .pc-menu-item:hover .pc-menu-icon,.pc-menu-item.active .pc-menu-icon { color:var(--g600); }
        .pc-menu-text  { flex:1; min-width:0; text-align:left; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .pc-menu-badge { min-width:22px; height:22px; padding:0 7px; border-radius:999px; background:#d1fae5; color:#059669; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:900; }
        .pc-logout-form { margin:0; padding:0; }
        .pc-logout,.pc-logout .pc-menu-icon { color:#ef4444; }
        .pc-logout:hover { background:#fff1f2; color:#dc2626; }

        .pc-sidebar-deco { position:absolute; left:0; right:0; bottom:0; height:128px; pointer-events:none; overflow:hidden; z-index:1; }
        .pc-wave { position:absolute; left:-20%; width:140%; border-radius:50% 50% 0 0; }
        .pc-wave-1 { bottom:-60px; height:112px; background:rgba(16,185,129,.13); }
        .pc-wave-2 { bottom:-76px; height:126px; background:rgba(5,150,105,.11); }
        .pc-wave-3 { bottom:-92px; height:132px; background:rgba(20,184,166,.09); }
        .pc-plant  { position:absolute; right:16px; bottom:22px; width:76px; height:76px; }
        .pc-stem   { position:absolute; left:36px; bottom:0; width:3px; height:58px; border-radius:999px; background:rgba(4,120,87,.32); transform:rotate(18deg); transform-origin:bottom; }
        .pc-leaf   { position:absolute; width:38px; height:20px; border-radius:100% 0 100% 0; background:linear-gradient(135deg,rgba(4,120,87,.62),rgba(16,185,129,.20)); transform-origin:bottom left; }
        .pc-leaf-1{right:22px;bottom:28px;transform:rotate(-34deg)}
        .pc-leaf-2{right:38px;bottom:42px;transform:rotate(-8deg) scale(.9)}
        .pc-leaf-3{right:8px;bottom:44px;transform:rotate(28deg) scale(.86)}
        .pc-leaf-4{right:30px;bottom:14px;transform:rotate(46deg) scale(.72)}

        .pc-sidebar > * { opacity:0; transform:translateY(7px); animation:sideIn .32s var(--ease2) forwards; }
        .pc-logo-area{animation-delay:.02s} .pc-user-card{animation-delay:.04s}
        .pc-menu-group:nth-of-type(1){animation-delay:.06s} .pc-menu-group:nth-of-type(2){animation-delay:.08s}
        .pc-sidebar-deco{animation-delay:.10s}
        @keyframes sideIn { to{opacity:1;transform:translateY(0)} }

        /* ── Topbar ────────────────────────────────── */
        .admin-topbar {
            position:relative; z-index:35; min-height:76px;
            margin:24px 28px 0; padding:14px 18px 14px 22px;
            display:flex; align-items:center; justify-content:space-between; gap:16px;
            border-radius:28px;
            background:linear-gradient(135deg,rgba(255,255,255,.92),rgba(255,255,255,.76));
            border:1px solid rgba(226,232,240,.80);
            backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px);
            box-shadow:0 12px 32px rgba(15,23,42,.05),inset 0 1px 0 rgba(255,255,255,.90);
            animation:tbIn .28s var(--ease2) both;
        }
        .admin-topbar::before {
            content:""; position:absolute; inset:1px; border-radius:27px; pointer-events:none;
            background:
                radial-gradient(circle at 4% 0%,rgba(16,185,129,.075),transparent 34%),
                radial-gradient(circle at 96% 0%,rgba(245,158,11,.055),transparent 34%);
        }
        @keyframes tbIn { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:none} }

        .topbar-left,.topbar-right { position:relative; z-index:2; display:flex; align-items:center; }
        .topbar-left { min-width:0; gap:14px; }
        .topbar-right { gap:12px; }

        .sidebar-toggle {
            width:44px; height:44px; border:1px solid rgba(226,232,240,.86); border-radius:16px;
            background:rgba(255,255,255,.88); color:var(--s600); cursor:pointer;
            box-shadow:0 8px 20px rgba(15,23,42,.05),inset 0 1px 0 rgba(255,255,255,.78);
            display:flex; align-items:center; justify-content:center;
            transition:color .14s ease,border-color .14s ease,transform .14s ease,background .14s ease;
        }
        .sidebar-toggle:hover { color:var(--g700); border-color:rgba(16,185,129,.28); background:#fff; transform:translateY(-1px); }

        .breadcrumb { display:flex; align-items:center; gap:8px; color:var(--s400); font-size:10.5px; font-weight:900; text-transform:uppercase; letter-spacing:.13em; }
        .breadcrumb a { color:var(--g700); text-decoration:none; }
        .breadcrumb i  { font-size:9px; opacity:.60; }
        .page-title { margin:6px 0 0; color:var(--s900); font-size:20px; line-height:1.05; font-weight:900; letter-spacing:-.045em; }

        .system-chip {
            height:46px; padding:0 18px; border-radius:18px;
            background:linear-gradient(135deg,rgba(236,253,245,.82),rgba(255,255,255,.60));
            border:1px solid rgba(16,185,129,.16); color:var(--g800);
            display:flex; align-items:center; gap:9px; font-size:12px; font-weight:900; white-space:nowrap;
            box-shadow:0 10px 24px rgba(16,185,129,.04),inset 0 1px 0 rgba(255,255,255,.80);
        }
        .system-chip i { color:var(--g600); }

        .profile-wrap { position:relative; }
        .profile-btn {
            height:50px; padding:5px 14px 5px 5px; border:1px solid rgba(226,232,240,.80); border-radius:999px;
            background:rgba(255,255,255,.80); display:flex; align-items:center; gap:10px; cursor:pointer;
            box-shadow:0 10px 24px rgba(15,23,42,.04),inset 0 1px 0 rgba(255,255,255,.80);
            transition:background .14s ease,border-color .14s ease,transform .14s ease;
        }
        .profile-btn:hover { background:#fff; border-color:rgba(16,185,129,.22); transform:translateY(-1px); }
        .profile-avatar {
            width:40px; height:40px; border-radius:999px; color:#fff;
            background:linear-gradient(135deg,var(--g700),var(--g500));
            display:flex; align-items:center; justify-content:center;
            font-size:13px; font-weight:900;
            box-shadow:0 8px 18px rgba(16,185,129,.18),inset 0 1px 0 rgba(255,255,255,.20);
            overflow:hidden;
        }
        .profile-avatar img { width:100%; height:100%; object-fit:cover; }
        .profile-name { color:var(--s700); font-size:13px; font-weight:900; max-width:132px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .profile-chevron { transition:transform .14s ease; }
        .profile-wrap.open .profile-chevron { transform:rotate(180deg); }

        .profile-menu {
            position:absolute; right:0; top:calc(100% + 12px); width:252px;
            border-radius:24px; background:rgba(255,255,255,.97);
            border:1px solid rgba(226,232,240,.88);
            box-shadow:0 24px 64px rgba(15,23,42,.12),inset 0 1px 0 rgba(255,255,255,.88);
            backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px);
            padding:10px; overflow:hidden;
            opacity:0; transform:translateY(7px) scale(.97);
            visibility:hidden; pointer-events:none;
            transition:opacity .14s ease,transform .14s var(--ease2),visibility .14s ease;
        }
        .profile-wrap.open .profile-menu { opacity:1; transform:none; visibility:visible; pointer-events:auto; }
        .profile-menu-head { padding:12px 12px 10px; display:flex; align-items:center; gap:11px; border-bottom:1px solid var(--s100); margin-bottom:8px; }
        .profile-menu-avatar {
            width:42px; height:42px; border-radius:16px; color:#fff; flex-shrink:0; overflow:hidden;
            background:linear-gradient(135deg,var(--g700),var(--a500));
            display:flex; align-items:center; justify-content:center; font-weight:900;
        }
        .profile-menu-avatar img { width:100%; height:100%; object-fit:cover; }
        .profile-menu-name { margin:0; color:var(--s900); font-size:13px; font-weight:900; line-height:1.2; }
        .profile-menu-role { margin:3px 0 0; color:var(--s400); font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.10em; }
        .profile-link { width:100%; border:0; border-radius:16px; padding:12px 14px; background:transparent; color:var(--s600); display:flex; align-items:center; gap:11px; font-size:13px; font-weight:900; cursor:pointer; text-decoration:none; transition:background .14s ease,color .14s ease; }
        .profile-link:hover { background:#ecfdf5; color:var(--g700); }
        .logout-btn { width:100%; border:0; border-radius:16px; padding:12px 14px; background:transparent; color:#e11d48; display:flex; align-items:center; gap:11px; font-size:13px; font-weight:900; cursor:pointer; transition:background .14s ease,color .14s ease; }
        .logout-btn:hover { background:#fff1f2; color:#be123c; }

        /* ── Main ──────────────────────────────────── */
        .admin-main {
            position:relative; z-index:10; flex:1; width:100%; max-width:1480px;
            margin:0 auto; padding:28px 28px 42px;
            animation:mainIn .30s var(--ease2) .05s both;
        }
        @keyframes mainIn { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:none} }

        .admin-card,.stat-card,.dashboard-card,.content-card,.table-card,.chart-card {
            border-radius:24px; background:rgba(255,255,255,.84);
            border:1px solid rgba(226,232,240,.80);
            box-shadow:0 14px 36px rgba(15,23,42,.04),inset 0 1px 0 rgba(255,255,255,.80);
        }

        /* View Transitions (browser modern) */
        @supports(view-transition-name:none){
            ::view-transition-old(root){ animation:vtOut .14s ease both; }
            ::view-transition-new(root){ animation:vtIn  .18s var(--ease) both; }
            @keyframes vtOut{ to{opacity:0;transform:translateY(-4px)} }
            @keyframes vtIn { from{opacity:0;transform:translateY(6px)} }
        }

        /* ── Responsive ────────────────────────────── */
        @media(max-width:1023px){
            :root{--sw:286px}
            .admin-sidebar{width:min(286px,calc(100vw - 24px));padding:10px;transform:translate3d(-110%,0,0)}
            body.sidebar-open .admin-sidebar{transform:translate3d(0,0,0)}
            .sidebar-close-btn{display:flex}
            .pc-sidebar{height:calc(100dvh - 20px);border-radius:24px;padding:22px 16px 18px}
            .pc-logo{width:142px!important}
            .admin-content{margin-left:0!important}
            .admin-topbar{min-height:72px;margin:14px 14px 0;padding:12px 14px;border-radius:24px}
            .breadcrumb{display:none} .page-title{margin:0;font-size:16px}
            .system-chip{display:none} .profile-name{display:none}
            .profile-btn{padding-right:5px}
            .admin-main{padding:24px 16px 34px}
            .admin-bg::before,.admin-bg::after{filter:blur(54px)}
        }
        @media(max-width:640px){
            .admin-topbar{min-height:68px;margin:10px 10px 0;border-radius:22px}
            .page-title{max-width:180px;font-size:15px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
            .admin-main{padding:22px 14px 30px}
            .profile-menu{width:230px}
        }
        @media(max-width:420px){ .pc-logo{width:132px!important} .pc-user-card{padding:12px} .pc-avatar{width:48px;height:48px} }
        @media(prefers-reduced-motion:reduce){ *,*::before,*::after{animation-duration:1ms!important;transition-duration:1ms!important} }
    </style>

    @stack('styles')
</head>

@php
    $authUser   = Auth::user();
    $adminName  = $authUser->name ?? 'Administrator';
    $adminInit  = strtoupper(substr($adminName, 0, 1));
    $adminFoto  = $authUser->foto ?? null;
@endphp

<body class="selection:bg-emerald-100 selection:text-emerald-900">

    {{-- Progress bar navigasi --}}
    <div id="pc-nav-bar" aria-hidden="true"></div>

    {{-- Full-screen loader: login & logout saja --}}
    <div id="pcAdminLoader" role="status" aria-label="Memuat, harap tunggu..." aria-live="polite">
        <div class="ld-veil"></div>
        <div class="ld-panel">
            <div class="ld-orbit">
                <div class="ld-ring"></div><div class="ld-ring"></div><div class="ld-ring"></div>
                <i class="fa-solid fa-heart-pulse ld-heart"></i>
            </div>
            <div class="ld-name">PosyanduCare</div>
            <div id="pcLoaderLabel" class="ld-label">Memuat Halaman</div>
            <div class="ld-dots">
                <span class="ld-dot"></span><span class="ld-dot"></span>
                <span class="ld-dot"></span><span class="ld-dot"></span>
            </div>
        </div>
    </div>

    <div class="admin-bg" aria-hidden="true"></div>
    <div class="admin-grid" aria-hidden="true"></div>

    <div class="admin-shell">

        {{-- Mobile overlay --}}
        <button type="button" class="mobile-overlay" id="mobileOverlay" aria-label="Tutup sidebar" tabindex="-1"></button>

        <aside id="adminSidebar" class="admin-sidebar" aria-label="Sidebar Admin">
            <button type="button" id="closeSidebarBtn" class="sidebar-close-btn" aria-label="Tutup sidebar">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <nav>
                @include('partials.sidebar.admin')
            </nav>
        </aside>

        <div class="admin-content">

            <header class="admin-topbar">
                <div class="topbar-left">
                    <button type="button" id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="false">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>
                    <div>
                        <div class="breadcrumb">
                            <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard">
                                <i class="fa-solid fa-house"></i>
                            </a>
                            <i class="fa-solid fa-chevron-right"></i>
                            <span>@yield('page-name', 'Overview')</span>
                        </div>
                        <h2 class="page-title">@yield('page-title', 'Dashboard Admin')</h2>
                    </div>
                </div>

                <div class="topbar-right">
                    <div class="system-chip">
                        <i class="fa-solid fa-heart-pulse"></i>
                        PosyanduCare System
                    </div>

                    <div class="profile-wrap" id="profileWrap">
                        <button type="button" id="profileToggle" class="profile-btn" aria-expanded="false">
                            <div class="profile-avatar">
                                @if($adminFoto)
                                    <img src="{{ asset('storage/'.$adminFoto) }}" alt="Foto Profil">
                                @else
                                    {{ $adminInit }}
                                @endif
                            </div>
                            <span class="profile-name">{{ $adminName }}</span>
                            <i class="profile-chevron fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>

                        <div class="profile-menu" id="profileMenu">
                            <div class="profile-menu-head">
                                <div class="profile-menu-avatar">
                                    @if($adminFoto)
                                        <img src="{{ asset('storage/'.$adminFoto) }}" alt="Foto Profil">
                                    @else
                                        {{ $adminInit }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="profile-menu-name truncate">{{ $adminName }}</p>
                                    <p class="profile-menu-role">Admin Sistem</p>
                                </div>
                            </div>

                            {{-- Tambah link profil jika ada route-nya --}}
                            @if(\Illuminate\Support\Facades\Route::has('admin.profile.index'))
                                <a href="{{ route('admin.profile.index') }}" class="profile-link">
                                    <i class="fa-regular fa-user"></i> Profil Saya
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="js-logout-form">
                                @csrf
                                <button type="submit" class="logout-btn">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-main">
                <div id="adminContent">
                    @yield('content')
                </div>
            </main>

        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const html      = document.documentElement;
        const body      = document.body;
        const loader    = document.getElementById('pcAdminLoader');
        const loaderLbl = document.getElementById('pcLoaderLabel');
        const navBar    = document.getElementById('pc-nav-bar');
        const desktop   = window.matchMedia('(min-width:1024px)');

        /* ── Helpers loader ─────────────────────────── */
        let ldTimer = null;
        const showLoader = (msg = 'Memuat Halaman') => {
            clearTimeout(ldTimer);
            if (loaderLbl) loaderLbl.textContent = msg;
            body.classList.add('locked');
            loader?.classList.add('show');
            ldTimer = setTimeout(hideLoader, 5000);
        };
        const hideLoader = () => {
            clearTimeout(ldTimer);
            body.classList.remove('locked');
            loader?.classList.remove('show');
        };

        /* ── Helpers nav bar ─────────────────────────── */
        let nbTimer = null;
        const navStart = () => {
            clearTimeout(nbTimer);
            navBar.classList.remove('done');
            navBar.classList.add('running');
        };
        const navDone = () => {
            navBar.classList.remove('running');
            navBar.classList.add('done');
            nbTimer = setTimeout(() => navBar.classList.remove('done'), 400);
        };

        /* ── Sidebar ─────────────────────────────────── */
        const getSaved  = () => { try{ return localStorage.getItem('pc_admin_sb'); } catch(e){ return null; } };
        const saveSb    = v  => { try{ localStorage.setItem('pc_admin_sb', v ? '1' : '0'); } catch(e){} };
        const isMobile  = () => !desktop.matches;

        const setSidebar = (open, save = true) => {
            body.classList.toggle('sidebar-open', open);
            document.getElementById('sidebarToggle')?.setAttribute('aria-expanded', open);
            if (open && isMobile()) body.classList.add('locked');
            else if (!open) body.classList.remove('locked');
            if (save && !isMobile()) saveSb(open);
        };

        const initSidebar = () => {
            if (isMobile()) setSidebar(false, false);
            else setSidebar(getSaved() !== '0', false);
        };

        initSidebar();
        desktop.addEventListener?.('change', initSidebar) ?? desktop.addListener?.(initSidebar);

        document.getElementById('sidebarToggle')?.addEventListener('click', () => setSidebar(!body.classList.contains('sidebar-open')));
        document.getElementById('closeSidebarBtn')?.addEventListener('click', () => setSidebar(false, false));
        document.getElementById('mobileOverlay')?.addEventListener('click', () => setSidebar(false, false));

        /* ── Profile dropdown ────────────────────────── */
        const profileWrap = document.getElementById('profileWrap');
        const profileToggle = document.getElementById('profileToggle');
        const closeProfile = () => { profileWrap?.classList.remove('open'); profileToggle?.setAttribute('aria-expanded','false'); };

        profileToggle?.addEventListener('click', e => {
            e.stopPropagation();
            const open = !profileWrap.classList.contains('open');
            profileWrap.classList.toggle('open', open);
            profileToggle.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', e => { if (profileWrap && !profileWrap.contains(e.target)) closeProfile(); });

        /* ── Keyboard ────────────────────────────────── */
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') { setSidebar(false, !isMobile()); closeProfile(); }
        });

        /* ── Login masuk: tampilkan loading screen ───── */
        if (html.classList.contains('pc-from-login')) {
            showLoader('Membuka Portal Admin');
            setTimeout(() => {
                try { sessionStorage.removeItem('pc_from_login'); } catch(e){}
                html.classList.replace('pc-from-login', 'pc-normal-entry');
                hideLoader();
                window.scrollTo(0,0);
            }, 950);
        }

        /* ── Navigasi biasa: hanya progress bar ──────── */
        const isRealNav = link => {
            const href = link.getAttribute('href') || '';
            if (!href || href==='#' || href.startsWith('#') || href.startsWith('javascript:') ||
                href.startsWith('mailto:') || href.startsWith('tel:') || link.hasAttribute('download')) return false;
            try {
                const u = new URL(href, location.href);
                return u.origin === location.origin && (u.pathname+u.search) !== (location.pathname+location.search);
            } catch{ return true; }
        };

        document.addEventListener('click', e => {
            const link = e.target.closest('a[href]');
            if (!link || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.defaultPrevented) return;
            if (!isRealNav(link)) return;
            if (isMobile()) setSidebar(false, false);
            navStart();
        });

        window.addEventListener('pageshow', navDone);
        window.addEventListener('load', navDone);

        /* ── Nexus alert helpers ─────────────────────── */
        window.nexusAlert = (title, text, type='success') =>
            Swal.fire({ title, html: text, icon: type, confirmButtonText:'MENGERTI',
                customClass:{popup:'nexus-swal',confirmButton:'nexus-confirm'}, buttonsStyling:false });

        window.nexusConfirm = (opts={}) =>
            Swal.fire({
                title:opts.title||'Konfirmasi', html:opts.text||'Data akan diproses.',
                icon:opts.icon||'warning', showCancelButton:true,
                confirmButtonText:opts.confirmText||'LANJUTKAN', cancelButtonText:opts.cancelText||'BATAL',
                customClass:{popup:'nexus-swal',confirmButton:'nexus-confirm',cancelButton:'nexus-cancel'},
                buttonsStyling:false });

        /* ── Logout: konfirmasi Nexus lalu loading screen ─ */
        document.querySelectorAll('.js-logout-form').forEach(form => {
            form.addEventListener('submit', e => {
                if (form.dataset.confirmed === '1') return;
                e.preventDefault();
                nexusConfirm({
                    title:'Keluar dari sistem?',
                    text:'Sesi admin akan diakhiri dan kamu akan kembali ke halaman login.',
                    icon:'warning', confirmText:'YA, LOGOUT', cancelText:'BATAL'
                }).then(r => {
                    if (r.isConfirmed) {
                        form.dataset.confirmed = '1';
                        showLoader('Keluar Sistem');
                        form.submit();
                    }
                });
            });
        });

        /* ── Flash alerts ────────────────────────────── */
        @if(session('success'))
            setTimeout(() => nexusAlert('Berhasil!', @json(session('success')), 'success'), 120);
        @endif
        @if(session('error'))
            setTimeout(() => nexusAlert('Perhatian!', @json(session('error')), 'error'), 120);
        @endif

        /* ── Safety: bersihkan scroll lock jika loader nyangkut ─ */
        window.addEventListener('pageshow', () => { hideLoader(); body.classList.remove('locked'); initSidebar(); });
    });

    /* ── Copy to clipboard ─────────────────────────── */
    window.copyToClipboard = function (text) {
        if (!navigator.clipboard) {
            const i = document.createElement('input'); i.value = text;
            document.body.appendChild(i); i.select(); document.execCommand('copy'); i.remove();
            return;
        }
        navigator.clipboard.writeText(text).then(() => {
            if (window.Swal) Swal.fire({ toast:true, position:'top-end', icon:'success',
                title:'Tersalin!', showConfirmButton:false, timer:1600, timerProgressBar:true,
                customClass:{popup:'nexus-swal'} });
        });
    };
    </script>

    {{-- Password modal (generated/reset) --}}
    @if(session('generated_password') || session('reset_password'))
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const pass  = @json(session('generated_password') ?? session('reset_password'));
        const title = @json(session('generated_password') ? 'Akun Berhasil Dibuat!' : 'Password Direset!');
        const name  = @json(session('user_name') ?? session('reset_name') ?? 'Pengguna');

        const show = () => Swal.fire({
            title:'<span style="color:#047857;font-weight:900;">'+title+'</span>',
            html:
                '<div style="background:#f8fafc;padding:20px;border-radius:22px;border:1px solid #e2e8f0;margin-top:8px;text-align:left;">'+
                    '<p style="font-size:11px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.12em;margin:0 0 4px;">Identitas Akun:</p>'+
                    '<p style="font-weight:900;color:#0f172a;margin:0 0 16px;font-size:15px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+name+'</p>'+
                    '<p style="font-size:11px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.12em;margin:0 0 8px;">Password Login:</p>'+
                    '<div style="display:flex;align-items:center;gap:8px;">'+
                        '<input type="text" readonly value="'+pass+'" style="width:100%;background:white;border:1px solid #cbd5e1;color:#047857;font-family:monospace;font-size:20px;font-weight:900;padding:12px 14px;border-radius:16px;text-align:center;outline:none;">'+
                        '<button type="button" onclick="copyToClipboard(\''+pass.replace(/'/g,"\\'")+'\')" style="background:#059669;color:white;border:0;padding:14px 18px;border-radius:16px;cursor:pointer;box-shadow:0 12px 24px rgba(5,150,105,.22)"><i class="fas fa-copy"></i></button>'+
                    '</div>'+
                '</div>',
            icon:'success', confirmButtonText:'Selesai', allowOutsideClick:false,
            customClass:{popup:'nexus-swal',confirmButton:'nexus-confirm'}
        });

        window.Swal ? show() : setTimeout(show, 400);
    });
    </script>
    @endif

    @stack('scripts')
</body>
</html>