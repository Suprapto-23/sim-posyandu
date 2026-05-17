<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8fffc">

    <title>@yield('title', 'Otentikasi') — PosyanduCare</title>

    {{-- FAVICON --}}
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    {{-- FONTS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap"
          rel="stylesheet">

    {{-- ICON --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- SWEET ALERT --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- TAILWIND --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --font-poppins: 'Poppins', sans-serif;

            --green-950: #022c22;
            --green-900: #064e3b;
            --green-800: #065f46;
            --green-700: #047857;
            --green-600: #059669;
            --green-500: #10b981;
            --green-400: #34d399;

            --teal-700: #0f766e;
            --teal-600: #0d9488;
            --teal-500: #14b8a6;

            --amber-500: #f59e0b;
            --amber-400: #fbbf24;

            --slate-950: #020617;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;

            --ease-premium: cubic-bezier(.16, 1, .3, 1);
            --ease-smooth: cubic-bezier(.22, 1, .36, 1);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-sans);
            color: var(--slate-800);

            background:
                radial-gradient(circle at 5% 10%, rgba(16,185,129,.16), transparent 30%),
                radial-gradient(circle at 88% 12%, rgba(245,158,11,.09), transparent 28%),
                radial-gradient(circle at 90% 90%, rgba(20,184,166,.14), transparent 34%),
                linear-gradient(135deg, #f8fffc 0%, #f8fafc 42%, #effbf6 100%);

            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body.auth-transition-lock {
            overflow: hidden !important;
            height: 100dvh !important;
            touch-action: none !important;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-poppins);
        }

        button,
        input,
        textarea,
        select {
            font-family: inherit;
        }

        ::selection {
            background: rgba(16,185,129,.18);
            color: var(--green-900);
        }

        /*
        |--------------------------------------------------------------------------
        | BACKGROUND
        |--------------------------------------------------------------------------
        */

        .auth-shell {
            position: relative;
            width: 100%;
            min-height: 100dvh;
            overflow: hidden;
        }

        .auth-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .auth-bg::before {
            content: "";
            position: absolute;
            inset: 0;

            background-image:
                linear-gradient(rgba(16,185,129,.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16,185,129,.035) 1px, transparent 1px);

            background-size: 34px 34px;
            mask-image: radial-gradient(circle at center, black, transparent 72%);
            opacity: .8;
        }

        .auth-orb {
            position: absolute;
            border-radius: 999px;
            filter: blur(70px);
            opacity: .55;
            animation: authOrbFloat 18s infinite alternate ease-in-out;
        }

        .auth-orb-1 {
            width: 420px;
            height: 420px;
            top: -170px;
            left: -160px;
            background: rgba(16,185,129,.28);
        }

        .auth-orb-2 {
            width: 360px;
            height: 360px;
            right: -140px;
            bottom: -130px;
            background: rgba(20,184,166,.24);
            animation-delay: 2s;
        }

        .auth-orb-3 {
            width: 300px;
            height: 300px;
            top: 22%;
            right: 20%;
            background: rgba(245,158,11,.12);
            animation-delay: 4s;
        }

        @keyframes authOrbFloat {
            0% {
                transform: translate3d(0,0,0) scale(1);
            }

            50% {
                transform: translate3d(26px,-34px,0) scale(1.06);
            }

            100% {
                transform: translate3d(-22px,24px,0) scale(.97);
            }
        }

        .auth-dot {
            position: fixed;
            width: 92px;
            height: 92px;
            top: 34px;
            left: 38px;
            z-index: 1;
            opacity: .22;
            pointer-events: none;

            background-image: radial-gradient(rgba(16,185,129,.72) 1.1px, transparent 1.1px);
            background-size: 9px 9px;
        }

        .auth-leaf-left {
            position: fixed;
            left: -72px;
            bottom: -74px;
            width: 300px;
            height: 300px;
            z-index: 1;
            opacity: .085;
            transform: rotate(-12deg);
            pointer-events: none;
        }

        .auth-leaf-left span {
            position: absolute;
            border-radius: 100% 0 100% 0;
            background: linear-gradient(135deg, rgba(4,120,87,.88), rgba(16,185,129,.18));
        }

        .auth-leaf-left .leaf-1 {
            width: 128px;
            height: 76px;
            left: 42px;
            bottom: 74px;
            transform: rotate(-24deg);
        }

        .auth-leaf-left .leaf-2 {
            width: 150px;
            height: 86px;
            left: 102px;
            bottom: 128px;
            transform: rotate(-4deg);
        }

        .auth-leaf-left .leaf-3 {
            width: 112px;
            height: 68px;
            left: 174px;
            bottom: 56px;
            transform: rotate(28deg);
        }

        .auth-wave-bottom {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
            pointer-events: none;
            opacity: .95;
        }

        .auth-wave-bottom svg {
            display: block;
            width: 100%;
            height: auto;
        }

        /*
        |--------------------------------------------------------------------------
        | CONTENT STAGE
        |--------------------------------------------------------------------------
        */

        .auth-main {
            position: relative;
            z-index: 10;

            width: 100%;
            min-height: 100dvh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 32px 18px;
        }

        #authGlobalStage {
            position: relative;
            z-index: 12;
            width: 100%;
            transition:
                opacity .72s var(--ease-premium),
                transform .72s var(--ease-premium),
                filter .72s var(--ease-premium);
            transform-origin: center;
        }

        body.auth-is-leaving #authGlobalStage {
            opacity: 0;
            transform: scale(.965) translateY(10px);
            filter: blur(8px);
        }

        /*
        |--------------------------------------------------------------------------
        | ENTRANCE
        |--------------------------------------------------------------------------
        */

        .view-enter-left {
            opacity: 0;
            transform: translate3d(-34px, 0, 0);
            filter: blur(5px);
            animation: viewEnterLeft .95s var(--ease-smooth) forwards;
        }

        .view-enter-up {
            opacity: 0;
            transform: translate3d(0, 34px, 0) scale(.98);
            filter: blur(5px);
            animation: viewEnterUp .95s var(--ease-smooth) forwards;
            animation-delay: .10s;
        }

        @keyframes viewEnterLeft {
            to {
                opacity: 1;
                transform: translate3d(0,0,0);
                filter: blur(0);
            }
        }

        @keyframes viewEnterUp {
            to {
                opacity: 1;
                transform: translate3d(0,0,0) scale(1);
                filter: blur(0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GLASS CARD UTILITIES
        |--------------------------------------------------------------------------
        */

        .auth-glass {
            background: rgba(255,255,255,.72);
            border: 1px solid rgba(255,255,255,.82);

            box-shadow:
                0 24px 70px rgba(15,23,42,.08),
                inset 0 1px 0 rgba(255,255,255,.72);

            backdrop-filter: blur(24px) saturate(1.18);
            -webkit-backdrop-filter: blur(24px) saturate(1.18);
        }

        .feature-card {
            background: rgba(255,255,255,.72);
            border: 1px solid rgba(255,255,255,.88);

            box-shadow:
                0 12px 28px rgba(15,23,42,.045),
                inset 0 1px 0 rgba(255,255,255,.92);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            transition:
                transform .32s var(--ease-premium),
                box-shadow .32s var(--ease-premium),
                background .32s var(--ease-premium);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,.92);
            box-shadow:
                0 18px 34px rgba(16,185,129,.10),
                inset 0 1px 0 rgba(255,255,255,.95);
        }

        /*
        |--------------------------------------------------------------------------
        | PREMIUM TRANSITION OVERLAY
        |--------------------------------------------------------------------------
        */

        .login-transition-layer {
            position: fixed;
            inset: 0;
            z-index: 99950;

            pointer-events: none;
            visibility: hidden;

            overflow: hidden;
        }

        .login-transition-layer.is-running {
            visibility: visible;
            pointer-events: auto;
        }

        .transition-backdrop {
            position: absolute;
            inset: 0;

            opacity: 0;

            background:
                radial-gradient(circle at 50% 35%, rgba(255,255,255,.20), transparent 28%),
                linear-gradient(135deg, rgba(2,44,34,.42), rgba(4,120,87,.28), rgba(16,185,129,.34));

            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);

            transition:
                opacity .45s ease,
                backdrop-filter .45s ease,
                -webkit-backdrop-filter .45s ease;
        }

        .login-transition-layer.is-running .transition-backdrop {
            opacity: 1;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .transition-panel {
            position: absolute;
            top: 0;
            width: 52%;
            height: 100%;

            overflow: hidden;

            background:
                radial-gradient(circle at 24% 25%, rgba(255,255,255,.20), transparent 20%),
                radial-gradient(circle at 70% 70%, rgba(52,211,153,.24), transparent 24%),
                linear-gradient(135deg, #022c22 0%, #065f46 42%, #10b981 100%);

            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.08),
                0 0 80px rgba(0,0,0,.18);

            transition:
                transform .95s var(--ease-premium),
                filter .95s var(--ease-premium);
        }

        .transition-panel::before {
            content: "";
            position: absolute;
            inset: 0;

            opacity: .18;

            background-image:
                linear-gradient(rgba(255,255,255,.09) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.09) 1px, transparent 1px);

            background-size: 32px 32px;
        }

        .transition-panel::after {
            content: "";
            position: absolute;
            inset: -30%;

            background:
                conic-gradient(
                    from 180deg,
                    transparent,
                    rgba(255,255,255,.10),
                    transparent,
                    rgba(251,191,36,.14),
                    transparent
                );

            animation: transitionShine 5.5s linear infinite;
            opacity: .65;
        }

        @keyframes transitionShine {
            to {
                transform: rotate(360deg);
            }
        }

        .transition-left {
            left: 0;
            transform: translateX(-105%);
            border-right: 1px solid rgba(255,255,255,.16);
        }

        .transition-right {
            right: 0;
            transform: translateX(105%);
            border-left: 1px solid rgba(255,255,255,.16);
        }

        .login-transition-layer.is-closing .transition-left,
        .login-transition-layer.is-closed .transition-left {
            transform: translateX(0);
        }

        .login-transition-layer.is-closing .transition-right,
        .login-transition-layer.is-closed .transition-right {
            transform: translateX(0);
        }

        .login-transition-layer.is-opening .transition-left {
            transform: translateX(-105%);
            filter: blur(5px);
        }

        .login-transition-layer.is-opening .transition-right {
            transform: translateX(105%);
            filter: blur(5px);
        }

        .transition-center-line {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            z-index: 5;

            width: 1px;

            opacity: 0;
            transform: translateX(-50%) scaleY(.2);

            background:
                linear-gradient(
                    to bottom,
                    transparent,
                    rgba(255,255,255,.34),
                    rgba(251,191,36,.42),
                    rgba(255,255,255,.34),
                    transparent
                );

            box-shadow:
                0 0 34px rgba(255,255,255,.22),
                0 0 70px rgba(16,185,129,.30);

            transition:
                opacity .55s ease,
                transform .55s var(--ease-premium);
        }

        .login-transition-layer.is-closing .transition-center-line,
        .login-transition-layer.is-closed .transition-center-line {
            opacity: 1;
            transform: translateX(-50%) scaleY(1);
        }

        .login-transition-layer.is-opening .transition-center-line {
            opacity: 0;
            transform: translateX(-50%) scaleY(.24);
        }

        .transition-wave {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            z-index: 6;
            opacity: .78;
            transform: translateY(24px);
            transition: transform .85s var(--ease-premium), opacity .85s ease;
        }

        .login-transition-layer.is-closing .transition-wave,
        .login-transition-layer.is-closed .transition-wave {
            transform: translateY(0);
        }

        .login-transition-layer.is-opening .transition-wave {
            opacity: 0;
            transform: translateY(38px);
        }

        .transition-wave svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .transition-loader-card {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 10;

            width: 224px;
            min-height: 180px;

            transform: translate(-50%, -50%) scale(.88);
            opacity: 0;
            filter: blur(10px);

            border-radius: 34px;
            border: 1px solid rgba(255,255,255,.34);

            background:
                linear-gradient(135deg, rgba(255,255,255,.20), rgba(255,255,255,.10));

            color: white;

            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;

            backdrop-filter: blur(26px) saturate(1.25);
            -webkit-backdrop-filter: blur(26px) saturate(1.25);

            box-shadow:
                0 30px 90px rgba(0,0,0,.20),
                inset 0 1px 0 rgba(255,255,255,.26);

            transition:
                opacity .62s var(--ease-premium),
                transform .62s var(--ease-premium),
                filter .62s var(--ease-premium);
        }

        .login-transition-layer.is-closing .transition-loader-card,
        .login-transition-layer.is-closed .transition-loader-card {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
            filter: blur(0);
            transition-delay: .28s;
        }

        .login-transition-layer.is-opening .transition-loader-card {
            transform: translate(-50%, -52%) scale(.92);
            opacity: 0;
            filter: blur(8px);
            transition-delay: 0s;
        }

        .transition-loader-icon {
            position: relative;

            width: 72px;
            height: 72px;

            border-radius: 26px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 15px;

            background: rgba(255,255,255,.16);
            border: 1px solid rgba(255,255,255,.30);

            box-shadow:
                0 16px 36px rgba(0,0,0,.12),
                inset 0 1px 0 rgba(255,255,255,.24);
        }

        .transition-loader-icon::before {
            content: "";
            position: absolute;
            inset: -7px;

            border-radius: 30px;

            border: 1px solid rgba(255,255,255,.22);
            animation: loaderPulse 1.65s infinite ease-out;
        }

        @keyframes loaderPulse {
            0% {
                transform: scale(.92);
                opacity: .8;
            }

            100% {
                transform: scale(1.22);
                opacity: 0;
            }
        }

        .transition-loader-icon i {
            font-size: 29px;
            color: white;
            filter: drop-shadow(0 6px 12px rgba(0,0,0,.12));
        }

        .transition-loader-title {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .02em;
        }

        .transition-loader-subtitle {
            margin-top: 5px;

            color: rgba(255,255,255,.72);

            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
        }

        .transition-progress {
            position: relative;

            width: 132px;
            height: 5px;

            margin-top: 16px;

            overflow: hidden;
            border-radius: 999px;

            background: rgba(255,255,255,.22);
        }

        .transition-progress::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 -55%;

            width: 55%;

            border-radius: inherit;
            background:
                linear-gradient(90deg, rgba(255,255,255,.88), rgba(251,191,36,.95), rgba(255,255,255,.88));

            animation: transitionProgress 1.1s infinite cubic-bezier(.65, 0, .35, 1);
        }

        @keyframes transitionProgress {
            to {
                left: 100%;
            }
        }

        .transition-ring {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 7;

            width: 420px;
            height: 420px;

            transform: translate(-50%, -50%) scale(.72);
            opacity: 0;

            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.11);

            box-shadow:
                0 0 0 60px rgba(255,255,255,.018),
                0 0 0 120px rgba(255,255,255,.012);

            transition:
                opacity .72s ease,
                transform .72s var(--ease-premium);
        }

        .login-transition-layer.is-closing .transition-ring,
        .login-transition-layer.is-closed .transition-ring {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .login-transition-layer.is-opening .transition-ring {
            opacity: 0;
            transform: translate(-50%, -50%) scale(1.18);
        }

        /*
        |--------------------------------------------------------------------------
        | SWEET ALERT
        |--------------------------------------------------------------------------
        */

        .custom-swal-popup {
            border-radius: 28px !important;
            padding: 2rem !important;
            font-family: var(--font-sans) !important;
            border: 1px solid rgba(226,232,240,.82) !important;
            background: rgba(255,255,255,.96) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            box-shadow: 0 24px 70px rgba(15,23,42,.16) !important;
        }

        .custom-swal-title {
            font-family: var(--font-poppins) !important;
            font-size: 1.25rem !important;
            font-weight: 900 !important;
            color: var(--slate-900) !important;
        }

        .custom-swal-html {
            font-size: .92rem !important;
            font-weight: 600 !important;
            color: var(--slate-500) !important;
        }

        .custom-swal-confirm-btn {
            padding: .78rem 1.4rem !important;
            border-radius: 16px !important;
            background: linear-gradient(135deg, var(--green-700), var(--green-500)) !important;
            color: white !important;
            font-weight: 900 !important;
            font-size: .82rem !important;
            box-shadow: 0 12px 24px rgba(16,185,129,.22) !important;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1024px) {
            body {
                overflow-y: auto;
            }

            .auth-main {
                align-items: flex-start;
                padding: 22px 14px 34px;
            }

            #authGlobalStage {
                min-height: auto;
            }

            .auth-dot {
                width: 74px;
                height: 74px;
                top: 22px;
                left: 20px;
                opacity: .15;
            }

            .transition-loader-card {
                width: 206px;
                min-height: 168px;
                border-radius: 30px;
            }

            .transition-ring {
                width: 280px;
                height: 280px;
            }
        }

        @media (max-width: 560px) {
            .auth-main {
                min-height: 100dvh;
                padding: 16px 12px 24px;
            }

            .transition-panel {
                width: 54%;
            }

            .transition-loader-card {
                width: 190px;
                min-height: 156px;
            }

            .transition-loader-icon {
                width: 62px;
                height: 62px;
                border-radius: 22px;
            }

            .transition-loader-title {
                font-size: 12px;
            }

            .transition-loader-subtitle {
                font-size: 9px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 1ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 1ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="relative text-slate-800">

<div class="auth-shell">

    {{-- BACKGROUND --}}
    <div class="auth-bg" aria-hidden="true">
        <div class="auth-orb auth-orb-1"></div>
        <div class="auth-orb auth-orb-2"></div>
        <div class="auth-orb auth-orb-3"></div>
    </div>

    <div class="auth-dot" aria-hidden="true"></div>

    <div class="auth-leaf-left" aria-hidden="true">
        <span class="leaf-1"></span>
        <span class="leaf-2"></span>
        <span class="leaf-3"></span>
    </div>

    <div class="auth-wave-bottom" aria-hidden="true">
        <svg viewBox="0 0 1440 260" preserveAspectRatio="none">
            <path fill="#10b981"
                  fill-opacity="0.10"
                  d="M0,210 C180,160 320,182 480,206 C650,232 780,240 960,198 C1130,160 1260,148 1440,188 L1440,260 L0,260 Z">
            </path>

            <path fill="#0f766e"
                  fill-opacity="0.12"
                  d="M0,232 C220,190 390,210 560,230 C760,254 930,226 1110,194 C1260,168 1350,174 1440,190 L1440,260 L0,260 Z">
            </path>

            <path fill="#14b8a6"
                  fill-opacity="0.08"
                  d="M0,246 C240,220 410,238 600,246 C820,256 1020,232 1200,210 C1320,196 1390,202 1440,212 L1440,260 L0,260 Z">
            </path>
        </svg>
    </div>

    {{-- CONTENT --}}
    <main class="auth-main">
        <div id="authGlobalStage">
            @yield('content')
        </div>
    </main>

    {{-- PREMIUM LOGIN TRANSITION --}}
    <div class="login-transition-layer" id="loginTransitionLayer" aria-hidden="true">
        <div class="transition-backdrop"></div>

        <div class="transition-panel transition-left"></div>
        <div class="transition-panel transition-right"></div>

        <div class="transition-center-line"></div>
        <div class="transition-ring"></div>

        <div class="transition-loader-card">
            <div class="transition-loader-icon">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>

            <div class="transition-loader-title">
                PosyanduCare
            </div>

            <div class="transition-loader-subtitle">
                Membuka Portal
            </div>

            <div class="transition-progress"></div>
        </div>

        <div class="transition-wave">
            <svg viewBox="0 0 1440 280" preserveAspectRatio="none">
                <path fill="#34d399"
                      fill-opacity=".18"
                      d="M0,210 C160,150 280,166 390,218 C520,278 610,250 720,188 C850,116 1010,128 1120,196 C1250,276 1350,236 1440,190 L1440,280 L0,280 Z">
                </path>

                <path fill="#10b981"
                      fill-opacity=".22"
                      d="M0,232 C160,194 300,210 450,232 C620,256 740,252 900,212 C1060,170 1220,158 1440,206 L1440,280 L0,280 Z">
                </path>

                <path fill="#ffffff"
                      fill-opacity=".08"
                      d="M0,248 C230,228 400,244 590,254 C800,266 990,240 1160,218 C1300,200 1380,210 1440,222 L1440,280 L0,280 Z">
                </path>
            </svg>
        </div>
    </div>
</div>

<script>
    /*
    |--------------------------------------------------------------------------
    | LOGIN TRANSITION ENGINE
    |--------------------------------------------------------------------------
    | Panggil window.runLoginTransition(form)
    | dari login.blade.php saat submit.
    */

    window.runLoginTransition = function (form) {
        const body = document.body;
        const transitionLayer = document.getElementById('loginTransitionLayer');

        if (!form || !transitionLayer) {
            if (form) {
                form.submit();
            }

            return;
        }

        body.classList.add('auth-transition-lock');
        body.classList.add('auth-is-leaving');

        transitionLayer.classList.remove('is-opening');
        transitionLayer.classList.add('is-running');

        requestAnimationFrame(function () {
            transitionLayer.classList.add('is-closing');
        });

        setTimeout(function () {
            transitionLayer.classList.add('is-closed');
        }, 980);

        /*
         * Penanda agar dashboard tahu ini datang dari login
         * lalu dashboard bisa menjalankan animasi masuk.
         */
        setTimeout(function () {
            try {
                sessionStorage.setItem('pc_from_login', '1');
            } catch (e) {}
        }, 1150);

        setTimeout(function () {
            form.submit();
        }, 1450);
    };

    /*
    |--------------------------------------------------------------------------
    | Auto fallback untuk form login
    |--------------------------------------------------------------------------
    | Kalau login.blade.php belum memanggil window.runLoginTransition(),
    | layout ini tetap menangkap submit form id="loginFormEngine".
    */

    document.addEventListener('DOMContentLoaded', function () {
        const loginForm = document.getElementById('loginFormEngine');

        if (!loginForm) {
            return;
        }

        if (loginForm.dataset.transitionBound === 'true') {
            return;
        }

        loginForm.dataset.transitionBound = 'true';

        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const form = this;
            const btn = document.getElementById('submitActionBtn');
            const txt = document.getElementById('submitTxt');
            const ico = document.getElementById('submitIcon');

            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-90', 'cursor-wait');
                btn.classList.remove('hover:-translate-y-0.5');
            }

            if (txt) {
                txt.innerText = 'Membuka portal...';
            }

            if (ico) {
                ico.classList.remove('fa-arrow-right');
                ico.classList.add('fa-circle-notch', 'fa-spin');
            }

            setTimeout(function () {
                window.runLoginTransition(form);
            }, 180);
        });
    });
</script>

@stack('scripts')

</body>
</html>