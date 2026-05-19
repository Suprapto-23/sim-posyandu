<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Otentikasi') — PosyanduCare</title>

    {{-- Favicon SVG inline (infinity + heart motif, green/amber) --}}
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><rect width='64' height='64' rx='14' fill='%23047857'/><text y='46' x='8' font-size='42' font-family='Arial'>&#x267E;</text><circle cx='44' cy='14' r='10' fill='%23f59e0b'/></svg>">
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ─── TOKENS ─── */
        :root {
            --g600: #059669; --g500: #10b981; --g400: #34d399;
            --g700: #047857; --g900: #064e3b; --g950: #022c22;
            --amber: #f59e0b; --amber2: #d97706;
            --s900: #0f172a; --s800: #1e293b; --s700: #334155;
            --s600: #475569; --s500: #64748b; --s400: #94a3b8;
            --s300: #cbd5e1; --s200: #e2e8f0; --s100: #f1f5f9; --s50: #f8fafc;
            --ease: cubic-bezier(.16,1,.3,1);
            --font: 'Plus Jakarta Sans', sans-serif;
            --poppins: 'Poppins', sans-serif;
            --card-r: 28px;
        }

        *, *::before, *::after { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; padding: 0; width: 100%; min-height: 100%; overflow-x: hidden; }

        body {
            font-family: var(--font);
            color: var(--s800);
            background:
                radial-gradient(ellipse 70% 65% at 0% 0%,   rgba(16,185,129,.18), transparent),
                radial-gradient(ellipse 60% 55% at 100% 100%, rgba(20,184,166,.13), transparent),
                linear-gradient(160deg, #e8f8f1 0%, #f8fafc 55%, #edf9f4 100%);
            -webkit-font-smoothing: antialiased;
        }
        body.lock { overflow: hidden !important; height: 100dvh !important; touch-action: none !important; }
        h1,h2,h3,h4,h5,h6 { font-family: var(--poppins); }
        button, input, select, textarea { font-family: inherit; }
        ::selection { background: rgba(16,185,129,.18); color: var(--g900); }

        /* ─── DECORATIVE ORBS ─── */
        .orb { position: fixed; border-radius: 999px; filter: blur(80px); pointer-events: none; z-index: 0; animation: orbf 22s infinite alternate ease-in-out; }
        .orb1 { width: 420px; height: 420px; top: -130px; left: -120px; background: rgba(16,185,129,.22); opacity: .55; }
        .orb2 { width: 340px; height: 340px; bottom: -100px; right: -80px; background: rgba(20,184,166,.18); opacity: .48; animation-delay: 5s; }
        .orb3 { width: 240px; height: 240px; top: 40%; left: 22%; background: rgba(245,158,11,.10); opacity: .45; animation-delay: 9s; }
        @keyframes orbf {
            0%   { transform: translate3d(0,0,0) scale(1); }
            50%  { transform: translate3d(20px,-28px,0) scale(1.05); }
            100% { transform: translate3d(-18px,20px,0) scale(.97); }
        }

        /* ─── BIG LEAVES (bottom-left, like reference) ─── */
        .deco-leaves {
            position: fixed; bottom: -30px; left: -40px;
            width: 380px; pointer-events: none; z-index: 1;
            transform: rotate(-8deg);
            opacity: 1;
        }
        .deco-leaves svg { width: 100%; height: auto; }

        /* ─── WAVE ─── */
        .auth-wave {
            position: fixed; left: 0; right: 0; bottom: 0;
            z-index: 0; pointer-events: none;
        }
        .auth-wave svg { display: block; width: 100%; height: auto; }

        /* ─── DOTS PATTERN (top-left corner) ─── */
        .deco-dots {
            position: fixed; top: 32px; left: 32px;
            width: 72px; height: 72px;
            opacity: .22; pointer-events: none; z-index: 0;
        }
        .deco-dots svg { width: 100%; height: 100%; }

        /* ─── LAYOUT ─── */
        .auth-wrap {
            min-height: 100dvh;
            display: grid;
            grid-template-columns: 1fr 520px;
            align-items: center;
            padding: 48px 64px;
            gap: 72px;
            max-width: 1360px;
            margin: 0 auto;
            position: relative; z-index: 10;
        }

        /* ─── BRAND SIDE ─── */
        .brand-side {
            display: flex; flex-direction: column;
            align-items: center; text-align: center;
            animation: eLeft .85s var(--ease) both;
        }
        @keyframes eLeft {
            from { opacity: 0; transform: translateX(-30px); filter: blur(6px); }
            to   { opacity: 1; transform: none; filter: none; }
        }

        .brand-logo {
            width: min(320px, 88%);
            height: auto; display: block;
            margin-bottom: 20px;
            filter: drop-shadow(0 14px 28px rgba(5,150,105,.13));
            user-select: none; pointer-events: none;
        }

        

        /* Amber divider line + diamond */
        .brand-divider {
            display: flex; align-items: center; gap: 10px;
            margin: 8px 0 14px;
        }
        .bd-line { width: 44px; height: 1.8px; border-radius: 99px; }
        .bd-line.l { background: linear-gradient(to right, transparent, var(--amber)); }
        .bd-line.r { background: linear-gradient(to left,  transparent, var(--amber)); }
        .bd-dot {
            width: 7px; height: 7px; border-radius: 2px;
            background: var(--amber); transform: rotate(45deg);
            box-shadow: 0 3px 8px rgba(245,158,11,.3);
        }

        .brand-sub {
            margin: 0 0 28px;
            color: var(--s600);
            font-size: clamp(13px, 1.35vw, 15px);
            font-weight: 600;
            line-height: 1.65;
            max-width: 380px;
        }

        /* Feature cards grid */
        .feat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            width: 100%;
            max-width: 400px;
        }
        .feat-card {
            height: 86px; border-radius: 18px;
            background: rgba(255,255,255,.80);
            border: 1.5px solid rgba(255,255,255,.95);
            box-shadow: 0 6px 20px rgba(15,23,42,.06), inset 0 1px 0 rgba(255,255,255,.9);
            backdrop-filter: blur(16px);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 7px;
            transition: transform .3s var(--ease), box-shadow .3s ease;
            cursor: default;
        }
        .feat-card:hover { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(16,185,129,.13); }
        .feat-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(5,150,105,.10);
            color: var(--g600);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }
        .feat-label { font-size: 10.5px; font-weight: 800; color: var(--s800); }

        /* ─── FORM SIDE ─── */
        .form-side {
            position: relative; z-index: 10;
            animation: eUp .9s var(--ease) .08s both;
        }
        @keyframes eUp {
            from { opacity: 0; transform: translateY(26px); filter: blur(5px); }
            to   { opacity: 1; transform: none; filter: none; }
        }

        .login-card {
            background: rgba(255,255,255,.97);
            border: 1px solid rgba(220,232,220,.6);
            border-radius: var(--card-r);
            padding: 44px 44px 40px;
            box-shadow:
                0 24px 72px rgba(15,23,42,.10),
                0 4px 16px rgba(15,23,42,.04),
                inset 0 1px 0 rgba(255,255,255,.98);
            backdrop-filter: blur(20px);
        }

        /* Card header */
        .card-title {
            margin: 0 0 6px;
            font-size: clamp(18px, 2vw, 22px);
            font-weight: 900; color: var(--g900);
            text-align: center; line-height: 1.2;
        }
        .card-sub {
            margin: 0 0 28px;
            font-size: 13.5px; color: var(--s500);
            font-weight: 500; text-align: center;
        }

        /* Fields */
        .field { margin-bottom: 16px; }
        .field-label {
            display: block; font-size: 13px;
            font-weight: 700; color: var(--s800); margin-bottom: 7px;
        }
        .field-wrap { position: relative; }
        .field-input {
            width: 100%; height: 52px;
            padding: 0 46px 0 46px;
            border-radius: 13px;
            border: 1.5px solid var(--s200);
            background: #fff;
            font-size: 14px; color: var(--s900);
            outline: none;
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .field-input::placeholder { color: var(--s400); }
        .field-input:focus {
            border-color: var(--g500);
            background: #fff;
            box-shadow: 0 0 0 3.5px rgba(16,185,129,.12);
        }
        .field-input.err {
            border-color: #f43f5e;
            box-shadow: 0 0 0 3px rgba(244,63,94,.09);
        }
        .fi-l {
            position: absolute; left: 15px; top: 50%;
            transform: translateY(-50%);
            color: var(--s400); font-size: 14px; pointer-events: none;
        }
        .fi-eye {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--s400); background: none; border: none;
            cursor: pointer; padding: 6px; font-size: 15px;
            transition: color .15s;
        }
        .fi-eye:hover { color: var(--g600); }
        .field-err { display: block; margin-top: 5px; font-size: 11.5px; color: #f43f5e; }

        /* Forgot */
        .forgot { text-align: right; margin: -4px 0 18px; }
        .forgot a {
            font-size: 13px; font-weight: 700;
            color: var(--g600); text-decoration: none;
        }
        .forgot a:hover { text-decoration: underline; }

        /* Submit button */
        .btn-submit {
            width: 100%; height: 54px; border-radius: 13px;
            background: linear-gradient(135deg, var(--g700) 0%, var(--g500) 100%);
            color: #fff; font-size: 15px; font-weight: 700;
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 8px 22px rgba(16,185,129,.30);
            transition: transform .22s var(--ease), box-shadow .22s ease, opacity .18s;
            letter-spacing: .01em;
        }
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(16,185,129,.36);
        }
        .btn-submit:disabled { opacity: .72; cursor: wait; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 10px;
            margin: 20px 0 16px;
            color: var(--s400); font-size: 12.5px; font-weight: 600;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: var(--s200);
        }

        /* Social buttons */
        .social-row {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 10px; margin-bottom: 20px;
        }
        .btn-social {
            height: 48px; border-radius: 13px;
            border: 1.5px solid var(--s200); background: #fff;
            cursor: pointer; font-size: 13.5px; font-weight: 700;
            color: var(--s700);
            display: flex; align-items: center; justify-content: center; gap: 9px;
            transition: border-color .18s, box-shadow .18s, transform .18s;
        }
        .btn-social:hover {
            border-color: var(--s300);
            box-shadow: 0 4px 14px rgba(15,23,42,.08);
            transform: translateY(-1px);
        }
        /* Google "G" colored logo */
        .google-g {
            width: 18px; height: 18px; flex-shrink: 0;
        }
        /* Microsoft logo */
        .ms-logo {
            width: 18px; height: 18px; flex-shrink: 0;
        }

        /* Register note */
        .register-note {
            text-align: center;
            font-size: 13px; color: var(--s500); font-weight: 600;
        }
        .register-note a {
            color: var(--g600); font-weight: 700; text-decoration: none;
        }
        .register-note a:hover { text-decoration: underline; }

        /* Account note (replaces "Akun dibuat oleh petugas...") */
        .account-note {
            text-align: center;
            font-size: 12px; color: var(--s400); font-weight: 500;
            margin-top: 14px; line-height: 1.6;
        }
        .account-note a {
            color: var(--g600); font-weight: 700; text-decoration: none;
        }
        .account-note a:hover { text-decoration: underline; }

        /* ─── LOADING OVERLAY ─── */
        #pcLoader {
            position: fixed; inset: 0; z-index: 99999;
            display: flex; align-items: center; justify-content: center;
            visibility: hidden; pointer-events: none;
        }
        #pcLoader.show { visibility: visible; pointer-events: auto; }
        #pcLoader .ld-bg {
            position: absolute; inset: 0;
            background: rgba(248,255,252,.84);
            backdrop-filter: blur(20px) saturate(1.5);
            opacity: 0; transition: opacity .32s ease;
        }
        #pcLoader.show .ld-bg { opacity: 1; }
        #pcLoader .ld-card {
            position: relative; z-index: 2;
            display: flex; flex-direction: column; align-items: center;
            background: rgba(255,255,255,.94);
            border: 1px solid rgba(16,185,129,.15);
            border-radius: 26px;
            padding: 36px 48px 32px;
            box-shadow: 0 24px 72px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.85);
            opacity: 0; transform: translateY(18px) scale(.96);
            transition: opacity .36s var(--ease) .1s, transform .36s var(--ease) .1s;
            min-width: 260px; text-align: center;
        }
        #pcLoader.show .ld-card { opacity: 1; transform: none; }
        .ld-ring-wrap { position: relative; width: 72px; height: 72px; margin: 0 auto 20px; }
        .ld-ring {
            width: 72px; height: 72px; border-radius: 50%;
            border: 3px solid rgba(16,185,129,.12);
            border-top-color: var(--g500);
            animation: spin .8s linear infinite;
            position: absolute; inset: 0;
        }
        .ld-ring2 {
            width: 72px; height: 72px; border-radius: 50%;
            border: 3px solid transparent;
            border-bottom-color: rgba(16,185,129,.28);
            animation: spin 1.4s linear infinite reverse;
            position: absolute; inset: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .ld-icon {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            color: var(--g600); font-size: 22px;
            animation: ldPulse 1.8s ease infinite;
        }
        @keyframes ldPulse { 0%,100%{transform:scale(1);opacity:.85;}50%{transform:scale(1.12);opacity:1;} }
        .ld-title { font-family: var(--poppins); font-size: 15px; font-weight: 800; color: var(--s900); margin-bottom: 4px; }
        .ld-sub { font-size: 11.5px; color: var(--s500); font-weight: 600; letter-spacing: .04em; text-transform: uppercase; margin-bottom: 18px; }
        .ld-bar { width: 160px; height: 3px; border-radius: 99px; background: rgba(16,185,129,.12); overflow: hidden; margin: 0 auto; }
        .ld-bar-fill {
            height: 100%; width: 40%; border-radius: 99px;
            background: linear-gradient(90deg, var(--g400), var(--g600));
            animation: ldBar 1.2s cubic-bezier(.65,0,.35,1) infinite;
        }
        @keyframes ldBar { 0%{transform:translateX(-100%);}100%{transform:translateX(350%)} }

        /* ─── NEXUS ALERT ─── */
        .nxa-overlay {
            position: fixed; inset: 0; z-index: 99998;
            display: flex; align-items: center; justify-content: center; padding: 20px;
            visibility: hidden; opacity: 0;
            background: rgba(15,23,42,.28);
            backdrop-filter: blur(6px);
            transition: opacity .26s ease, visibility .26s;
        }
        .nxa-overlay.open { visibility: visible; opacity: 1; }
        .nxa-box {
            background: #fff; border-radius: 24px;
            padding: 36px 40px 32px;
            width: 100%; max-width: 400px;
            box-shadow: 0 32px 80px rgba(15,23,42,.18), 0 0 0 1px rgba(226,232,240,.7);
            display: flex; flex-direction: column; align-items: center; text-align: center;
            transform: translateY(20px) scale(.96);
            transition: transform .32s var(--ease) .04s;
        }
        .nxa-overlay.open .nxa-box { transform: none; }
        .nxa-icon-wrap {
            width: 64px; height: 64px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 18px; position: relative;
        }
        .nxa-icon-wrap::after {
            content: ''; position: absolute; inset: -6px; border-radius: 50%;
            opacity: .15; animation: nxaPulse 2s ease infinite;
        }
        @keyframes nxaPulse { 0%,100%{transform:scale(1);}50%{transform:scale(1.12);} }
        .nxa-icon-wrap.success { background: rgba(16,185,129,.12); }
        .nxa-icon-wrap.success::after { background: var(--g500); }
        .nxa-icon-wrap.error   { background: rgba(244,63,94,.10); }
        .nxa-icon-wrap.error::after   { background: #f43f5e; }
        .nxa-icon-wrap.question { background: rgba(100,116,139,.10); }
        .nxa-icon-wrap.question::after { background: var(--s400); }
        .nxa-icon { font-size: 26px; }
        .nxa-icon-wrap.success  .nxa-icon { color: var(--g600); }
        .nxa-icon-wrap.error    .nxa-icon { color: #f43f5e; }
        .nxa-icon-wrap.question .nxa-icon { color: var(--s500); }
        .nxa-title { font-family: var(--poppins); font-size: 18px; font-weight: 900; color: var(--s900); margin-bottom: 8px; }
        .nxa-msg   { font-size: 13.5px; color: var(--s500); font-weight: 600; line-height: 1.6; margin-bottom: 26px; }
        .nxa-actions { display: flex; gap: 10px; width: 100%; }
        .nxa-btn {
            flex: 1; height: 46px; border-radius: 12px; border: none;
            cursor: pointer; font-size: 13.5px; font-weight: 700;
            transition: transform .18s, box-shadow .18s, opacity .18s;
        }
        .nxa-btn:hover { transform: translateY(-1px); }
        .nxa-btn.primary { background: linear-gradient(135deg,#047857,var(--g500)); color: #fff; box-shadow: 0 6px 16px rgba(16,185,129,.24); }
        .nxa-btn.primary:hover { box-shadow: 0 10px 22px rgba(16,185,129,.32); }
        .nxa-btn.secondary { background: var(--s100); color: var(--s700); }
        .nxa-btn.secondary:hover { background: var(--s200); }
        .nxa-timer { width: 100%; height: 2.5px; background: rgba(16,185,129,.12); border-radius: 99px; overflow: hidden; margin-top: 18px; }
        .nxa-timer-fill { height: 100%; background: var(--g500); border-radius: 99px; width: 100%; transform-origin: left; animation: nxaTimer linear forwards; }
        @keyframes nxaTimer { to { transform: scaleX(0); } }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1100px) {
            .auth-wrap { padding: 36px 32px 56px; gap: 48px; }
            .feat-grid { max-width: 360px; }
        }
        @media (max-width: 900px) {
            .auth-wrap {
                grid-template-columns: 1fr;
                padding: 36px 20px 60px;
                gap: 32px;
                max-width: 520px;
            }
            .form-side { order: -1; }
            .brand-side { order: 1; }
            .deco-leaves { width: 240px; }
        }
        @media (max-width: 520px) {
            .login-card { padding: 30px 22px 26px; border-radius: 22px; }
            .feat-grid  { grid-template-columns: repeat(4,1fr); gap: 8px; }
            .feat-card  { height: 76px; }
            .feat-label { font-size: 9.5px; }
            .social-row { grid-template-columns: 1fr 1fr; }
            .nxa-box    { padding: 28px 22px 24px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 1ms !important; transition-duration: 1ms !important; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- ── Decorative Orbs ── --}}
<div class="orb orb1" aria-hidden="true"></div>
<div class="orb orb2" aria-hidden="true"></div>
<div class="orb orb3" aria-hidden="true"></div>

{{-- ── Dot grid top-left ── --}}
<div class="deco-dots" aria-hidden="true">
    <svg viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g fill="#10b981">
            @for($r = 0; $r < 5; $r++)
                @for($c = 0; $c < 5; $c++)
                    <circle cx="{{ 8 + $c * 14 }}" cy="{{ 8 + $r * 14 }}" r="2.5"/>
                @endfor
            @endfor
        </g>
    </svg>
</div>

{{-- ── Big leaves bottom-left ── --}}
<div class="deco-leaves" aria-hidden="true">
    <svg viewBox="0 0 380 320" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Large back leaf -->
        <path d="M30 310 C40 240 100 160 200 110 C280 70 340 80 355 100 C360 108 345 130 310 155 C260 190 190 220 140 270 C100 305 60 320 30 310Z"
              fill="url(#lg1)" opacity=".82"/>
        <!-- Medium front leaf -->
        <path d="M0 290 C20 230 80 160 160 120 C220 90 270 95 280 115 C285 125 268 146 238 168 C195 198 135 228 85 268 C48 297 12 308 0 290Z"
              fill="url(#lg2)" opacity=".72"/>
        <!-- Small accent leaf -->
        <path d="M160 300 C172 268 210 235 255 215 C285 202 305 206 308 218 C310 226 296 240 274 252 C245 266 210 278 185 293 C170 302 158 308 160 300Z"
              fill="url(#lg3)" opacity=".60"/>
        <!-- Vein lines -->
        <path d="M180 280 C200 240 250 200 300 170" stroke="#047857" stroke-width="1.5" stroke-linecap="round" opacity=".25"/>
        <path d="M60 285 C90 250 140 210 200 180" stroke="#047857" stroke-width="1.5" stroke-linecap="round" opacity=".20"/>
        <defs>
            <linearGradient id="lg1" x1="30" y1="310" x2="355" y2="100" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#059669" stop-opacity=".9"/>
                <stop offset="100%" stop-color="#34d399" stop-opacity=".5"/>
            </linearGradient>
            <linearGradient id="lg2" x1="0" y1="290" x2="280" y2="115" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#047857" stop-opacity=".85"/>
                <stop offset="100%" stop-color="#6ee7b7" stop-opacity=".45"/>
            </linearGradient>
            <linearGradient id="lg3" x1="160" y1="300" x2="308" y2="215" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#10b981" stop-opacity=".75"/>
                <stop offset="100%" stop-color="#a7f3d0" stop-opacity=".4"/>
            </linearGradient>
        </defs>
    </svg>
</div>

{{-- ── Wave ── --}}
<div class="auth-wave" aria-hidden="true">
    <svg viewBox="0 0 1440 170" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path fill="#10b981" fill-opacity=".07"
              d="M0,130 C300,90 580,122 860,128 C1120,134 1310,104 1440,88 L1440,170 L0,170 Z"/>
        <path fill="#0f766e" fill-opacity=".08"
              d="M0,148 C330,118 630,142 910,150 C1160,158 1330,128 1440,112 L1440,170 L0,170 Z"/>
    </svg>
</div>

<main style="position:relative;z-index:10;">
    <div class="auth-wrap">

        {{-- ── BRAND SIDE ── --}}
        <section class="brand-side" aria-label="PosyanduCare branding">

            <img src="{{ asset('img/logo.png') }}" alt="Logo PosyanduCare" class="brand-logo">
            <div class="brand-divider" aria-hidden="true">
                <span class="bd-line l"></span>
                <span class="bd-dot"></span>
                <span class="bd-line r"></span>
            </div>

            <p class="brand-sub">Platform layanan kesehatan terpadu<br>untuk masyarakat modern.</p>

            <div class="feat-grid" role="list">
                @foreach([
                    ['fa-user-group',    'Terintegrasi'],
                    ['fa-shield-halved', 'Aman'],
                    ['fa-chart-simple',  'Efisien'],
                    ['fa-heart-pulse',   'Peduli'],
                ] as $f)
                <div class="feat-card" role="listitem">
                    <div class="feat-icon" aria-hidden="true">
                        <i class="fa-solid {{ $f[0] }}"></i>
                    </div>
                    <span class="feat-label">{{ $f[1] }}</span>
                </div>
                @endforeach
            </div>

        </section>

        {{-- ── FORM SIDE ── --}}
        <section class="form-side" aria-label="Form masuk">
            <div class="login-card">
                @yield('content')
            </div>
        </section>

    </div>
</main>

{{-- ── LOADING OVERLAY ── --}}
<div id="pcLoader" role="status" aria-label="Memuat, harap tunggu...">
    <div class="ld-bg"></div>
    <div class="ld-card">
        <div class="ld-ring-wrap">
            <div class="ld-ring"></div>
            <div class="ld-ring2"></div>
            <div class="ld-icon"><i class="fa-solid fa-heart-pulse"></i></div>
        </div>
        <div class="ld-title">PosyanduCare</div>
        <div class="ld-sub">Membuka Portal</div>
        <div class="ld-bar"><div class="ld-bar-fill"></div></div>
    </div>
</div>

{{-- ── NEXUS ALERT ── --}}
<div class="nxa-overlay" id="nxaOverlay" role="dialog" aria-modal="true" aria-live="assertive">
    <div class="nxa-box">
        <div class="nxa-icon-wrap" id="nxaIconWrap">
            <i class="nxa-icon" id="nxaIcon"></i>
        </div>
        <div class="nxa-title"   id="nxaTitle"></div>
        <div class="nxa-msg"     id="nxaMsg"></div>
        <div class="nxa-actions" id="nxaActions"></div>
        <div class="nxa-timer"   id="nxaTimerWrap" style="display:none">
            <div class="nxa-timer-fill" id="nxaTimerFill"></div>
        </div>
    </div>
</div>

<script>
/* ── NEXUS ALERT ENGINE ── */
window.NxAlert = (function () {
    var ov    = document.getElementById('nxaOverlay');
    var wrap  = document.getElementById('nxaIconWrap');
    var icon  = document.getElementById('nxaIcon');
    var title = document.getElementById('nxaTitle');
    var msg   = document.getElementById('nxaMsg');
    var acts  = document.getElementById('nxaActions');
    var timerW = document.getElementById('nxaTimerWrap');
    var timerF = document.getElementById('nxaTimerFill');
    var _tmr;

    function close() { ov.classList.remove('open'); clearTimeout(_tmr); }

    function fire(opts) {
        var type  = opts.type || 'success';
        var icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', question: 'fa-circle-question' };

        wrap.className       = 'nxa-icon-wrap ' + type;
        icon.className       = 'nxa-icon fa-solid ' + (icons[type] || 'fa-circle-check');
        title.textContent    = opts.title   || '';
        msg.innerHTML        = opts.message || '';

        acts.innerHTML = '';
        if (opts.cancelText) {
            var c = document.createElement('button');
            c.className   = 'nxa-btn secondary';
            c.textContent = opts.cancelText;
            c.onclick = function () { close(); if (opts.onCancel) opts.onCancel(); };
            acts.appendChild(c);
        }
        var b = document.createElement('button');
        b.className   = 'nxa-btn primary';
        b.textContent = opts.confirmText || 'Tutup';
        b.onclick = function () { close(); if (opts.onConfirm) opts.onConfirm(); };
        acts.appendChild(b);

        if (opts.timer) {
            timerW.style.display = '';
            timerF.style.animation = 'none';
            timerF.offsetHeight; // reflow
            timerF.style.animation = 'nxaTimer ' + opts.timer + 'ms linear forwards';
            _tmr = setTimeout(close, opts.timer);
        } else {
            timerW.style.display = 'none';
        }
        ov.classList.add('open');
    }

    ov.addEventListener('click', function (e) { if (e.target === ov) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

    return { fire: fire, close: close };
})();

/* ── LOADER ENGINE ── */
window.runLoginTransition = function (form) {
    var loader = document.getElementById('pcLoader');
    if (!form) return;
    if (!loader) { form.submit(); return; }

    document.body.classList.add('lock');
    loader.classList.add('show');

    try { sessionStorage.setItem('pc_from_login', '1'); } catch (e) {}
    setTimeout(function () { form.submit(); }, 1600);
};

/* ── AUTO BIND ── */
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('loginFormEngine');
    if (!form || form.dataset.bound) return;
    form.dataset.bound = '1';
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('submitActionBtn');
        var txt = document.getElementById('submitTxt');
        var ico = document.getElementById('submitIcon');
        if (btn) btn.disabled = true;
        if (txt) txt.textContent = 'Membuka portal...';
        if (ico) { ico.classList.remove('fa-arrow-right'); ico.classList.add('fa-circle-notch', 'fa-spin'); }
        setTimeout(function () { window.runLoginTransition(form); }, 160);
    });
});
</script>

@stack('scripts')
</body>
</html>