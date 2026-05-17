<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Otentikasi') | PosyanduCare</title>

    {{-- FAVICON --}}
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    {{-- GOOGLE FONTS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap"
          rel="stylesheet">

    {{-- ICON --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- SWEETALERT --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- TAILWIND --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --font-poppins: 'Poppins', sans-serif;
        }
    </style>

    <style>
        /* =========================================================
           BASE
        ========================================================= */

        :root {
            --green-950: #052e24;
            --green-900: #064e3b;
            --green-800: #065f46;
            --green-700: #047857;
            --green-600: #059669;
            --green-500: #10b981;
            --green-soft: #ecfdf5;
            --teal-soft: #ccfbf1;
            --amber: #f59e0b;
            --amber-soft: #fef3c7;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --white: #ffffff;
            --ease-premium: cubic-bezier(.16, 1, .3, 1);
            --ease-gate: cubic-bezier(.76, 0, .24, 1);
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
        }

        html {
            overflow-x: hidden;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--slate-900);
            background: #f7fffb;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Poppins', sans-serif;
        }

        button,
        input,
        textarea,
        select {
            font-family: inherit;
        }

        ::selection {
            color: var(--green-900);
            background: rgba(16, 185, 129, .18);
        }

        /* =========================================================
           PAGE ROOT
        ========================================================= */

        .auth-page {
            position: relative;
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
            isolation: isolate;
            background:
                radial-gradient(circle at 14% 18%, rgba(16, 185, 129, .20), transparent 24%),
                radial-gradient(circle at 72% 16%, rgba(245, 158, 11, .10), transparent 22%),
                radial-gradient(circle at 78% 72%, rgba(20, 184, 166, .15), transparent 27%),
                linear-gradient(135deg, #eafff7 0%, #fbfffd 38%, #f2fdf8 70%, #e9fbf5 100%);
        }

        .auth-page::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background:
                linear-gradient(
                    90deg,
                    rgba(255, 255, 255, .62) 0%,
                    rgba(255, 255, 255, .14) 22%,
                    rgba(255, 255, 255, .04) 50%,
                    rgba(255, 255, 255, .34) 100%
                ),
                radial-gradient(circle at center, transparent 0%, rgba(255,255,255,.40) 80%);
        }

        .auth-page::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.24) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.24) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: radial-gradient(circle at center, rgba(0,0,0,.20), transparent 70%);
            opacity: .22;
        }

        .auth-stage {
            position: relative;
            z-index: 30;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 26px 42px;
            transition:
                opacity 460ms ease,
                transform 680ms var(--ease-premium),
                filter 680ms ease;
            will-change: opacity, transform, filter;
        }

        .auth-stage.is-leaving {
            opacity: 0;
            transform: scale(.965) translateY(8px);
            filter: blur(5px);
        }

        /* =========================================================
           BACKGROUND DECORATION
        ========================================================= */

        .auth-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .soft-glow {
            position: absolute;
            border-radius: 999px;
            filter: blur(84px);
            opacity: .55;
            transform: translate3d(0, 0, 0);
            animation: glowFloat 18s infinite alternate ease-in-out;
        }

        .soft-glow.one {
            width: 560px;
            height: 560px;
            top: -190px;
            left: -155px;
            background: rgba(16, 185, 129, .25);
        }

        .soft-glow.two {
            width: 560px;
            height: 560px;
            right: -210px;
            bottom: -170px;
            background: rgba(20, 184, 166, .20);
            animation-delay: 2.8s;
        }

        .soft-glow.three {
            width: 430px;
            height: 430px;
            top: 12%;
            right: 24%;
            background: rgba(245, 158, 11, .12);
            animation-delay: 5s;
        }

        .soft-glow.four {
            width: 310px;
            height: 310px;
            left: 36%;
            bottom: 10%;
            background: rgba(16, 185, 129, .10);
            animation-delay: 7s;
        }

        @keyframes glowFloat {
            0% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(28px, -34px) scale(1.07);
            }

            100% {
                transform: translate(-24px, 22px) scale(.96);
            }
        }

        .dot-pattern {
            position: absolute;
            width: 98px;
            height: 98px;
            background-image: radial-gradient(rgba(16, 185, 129, .38) 1.25px, transparent 1.25px);
            background-size: 10px 10px;
            opacity: .72;
        }

        .dot-pattern.top-left {
            top: 56px;
            left: 58px;
        }

        .dot-pattern.bottom-right {
            right: 112px;
            bottom: 128px;
            opacity: .26;
            transform: rotate(8deg);
        }

        .circle-blur {
            position: absolute;
            width: 222px;
            height: 222px;
            left: 54px;
            bottom: 172px;
            border-radius: 50%;
            background:
                radial-gradient(circle, rgba(16,185,129,.10), rgba(255,255,255,.02) 68%);
            opacity: .74;
        }

        .subtle-ring {
            position: absolute;
            width: 420px;
            height: 420px;
            right: 17%;
            top: 18%;
            border-radius: 999px;
            border: 1px solid rgba(16, 185, 129, .08);
            box-shadow:
                inset 0 0 70px rgba(16,185,129,.035),
                0 0 100px rgba(255,255,255,.34);
            opacity: .8;
        }

        /* =========================================================
           LEAF ILLUSTRATION, CSS ONLY
        ========================================================= */

        .leaf-cluster {
            position: absolute;
            left: -44px;
            bottom: -30px;
            width: 380px;
            height: 380px;
            opacity: .24;
            transform: rotate(-9deg);
        }

        .leaf {
            position: absolute;
            border-radius: 100% 0 100% 0;
            background:
                linear-gradient(135deg, rgba(4,120,87,.36), rgba(16,185,129,.05));
            transform-origin: bottom right;
            box-shadow: inset 10px 10px 18px rgba(255,255,255,.20);
        }

        .leaf::after {
            content: "";
            position: absolute;
            width: 72%;
            height: 2px;
            top: 48%;
            left: 14%;
            background: rgba(4,120,87,.22);
            transform: rotate(42deg);
            border-radius: 999px;
        }

        .leaf.l1 {
            width: 132px;
            height: 80px;
            left: 34px;
            bottom: 58px;
            transform: rotate(-20deg);
        }

        .leaf.l2 {
            width: 154px;
            height: 88px;
            left: 98px;
            bottom: 116px;
            transform: rotate(-4deg);
        }

        .leaf.l3 {
            width: 120px;
            height: 74px;
            left: 166px;
            bottom: 38px;
            transform: rotate(24deg);
        }

        .leaf.l4 {
            width: 102px;
            height: 64px;
            left: 58px;
            bottom: 192px;
            transform: rotate(-42deg);
            opacity: .72;
        }

        .leaf-line {
            position: absolute;
            left: 34px;
            bottom: 106px;
            width: 260px;
            height: 1.5px;
            background: rgba(4,120,87,.12);
            transform: rotate(32deg);
            border-radius: 999px;
        }

        /* =========================================================
           BOTTOM WAVE
        ========================================================= */

        .wave-wrap {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -2px;
            z-index: 2;
            width: 100%;
        }

        .wave-wrap svg {
            display: block;
            width: 100%;
            height: 230px;
        }

        /* =========================================================
           PAGE ENTRANCE HELPERS
        ========================================================= */

        .auth-fade-left {
            opacity: 0;
            transform: translateX(-34px);
            animation: authEnterLeft .82s var(--ease-premium) forwards;
        }

        .auth-fade-right {
            opacity: 0;
            transform: translateX(34px);
            animation: authEnterRight .82s var(--ease-premium) forwards;
        }

        .auth-fade-up {
            opacity: 0;
            transform: translateY(24px);
            animation: authEnterUp .72s var(--ease-premium) forwards;
        }

        .delay-1 {
            animation-delay: .08s;
        }

        .delay-2 {
            animation-delay: .14s;
        }

        .delay-3 {
            animation-delay: .20s;
        }

        .delay-4 {
            animation-delay: .26s;
        }

        .delay-5 {
            animation-delay: .32s;
        }

        @keyframes authEnterLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes authEnterRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes authEnterUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================================================
           PREMIUM SPLIT TRANSITION
        ========================================================= */

        .split-transition-layer {
            position: fixed;
            inset: 0;
            z-index: 9990;
            pointer-events: none;
            overflow: hidden;
        }

        .split-panel {
            position: absolute;
            top: 0;
            width: 50.08%;
            height: 100%;
            overflow: hidden;
            background:
                radial-gradient(circle at 26% 22%, rgba(255,255,255,.24), transparent 30%),
                radial-gradient(circle at 74% 80%, rgba(245,158,11,.18), transparent 28%),
                linear-gradient(135deg, #044e3a 0%, #047857 40%, #10b981 100%);
            transition:
                transform 760ms var(--ease-gate),
                filter 760ms ease;
            will-change: transform;
        }

        .split-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(rgba(255,255,255,.20) 1px, transparent 1px);
            background-size: 14px 14px;
            opacity: .15;
        }

        .split-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(
                    90deg,
                    rgba(255,255,255,0),
                    rgba(255,255,255,.10),
                    rgba(255,255,255,0)
                );
            transform: translateX(-120%);
            opacity: .6;
        }

        .split-panel.gate-lock-closed::after {
            animation: panelSheen 920ms 180ms ease forwards;
        }

        @keyframes panelSheen {
            to {
                transform: translateX(120%);
            }
        }

        .split-panel-left {
            left: 0;
            transform: translateX(-102%);
            border-right: 1px solid rgba(255,255,255,.18);
        }

        .split-panel-right {
            right: 0;
            transform: translateX(102%);
            border-left: 1px solid rgba(255,255,255,.18);
        }

        .split-panel-left.gate-lock-closed,
        .split-panel-right.gate-lock-closed {
            transform: translateX(0);
        }

        .split-decor {
            position: absolute;
            inset: 0;
            opacity: .24;
        }

        .split-decor::before {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.28);
            top: 18%;
            left: 18%;
        }

        .split-decor::after {
            content: "";
            position: absolute;
            width: 190px;
            height: 190px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            bottom: 18%;
            right: 12%;
            filter: blur(1px);
        }

        .split-wave {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -3px;
            width: 100%;
            opacity: .48;
        }

        .split-wave svg {
            display: block;
            width: 100%;
            height: 260px;
        }

        .transition-brand {
            position: fixed;
            inset: 0;
            z-index: 10020;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0;
            transform: scale(.92);
            transition:
                opacity 380ms ease,
                transform 520ms var(--ease-premium);
        }

        .transition-brand.is-visible {
            opacity: 1;
            transform: scale(1);
        }

        .transition-box {
            width: 224px;
            min-height: 178px;
            border-radius: 34px;
            padding: 24px 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.32);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            box-shadow:
                0 30px 90px rgba(0,0,0,.18),
                inset 0 1px 0 rgba(255,255,255,.26);
        }

        .transition-icon {
            width: 76px;
            height: 76px;
            border-radius: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.30);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.24),
                0 18px 42px rgba(0,0,0,.12);
        }

        .transition-icon i {
            font-size: 31px;
        }

        .transition-text {
            margin-top: 14px;
            color: white;
            font-size: 13.5px;
            font-weight: 900;
            letter-spacing: .02em;
        }

        .transition-loader {
            position: relative;
            width: 124px;
            height: 5px;
            overflow: hidden;
            margin-top: 15px;
            border-radius: 999px;
            background: rgba(255,255,255,.22);
        }

        .transition-loader::after {
            content: "";
            position: absolute;
            top: 0;
            left: -55%;
            width: 55%;
            height: 100%;
            border-radius: inherit;
            background: white;
            animation: loaderMove 880ms infinite cubic-bezier(.65, 0, .35, 1);
        }

        @keyframes loaderMove {
            to {
                left: 100%;
            }
        }

        /* =========================================================
           SWEETALERT
        ========================================================= */

        .custom-swal-popup {
            border-radius: 26px !important;
            padding: 28px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            box-shadow: 0 28px 90px rgba(15, 23, 42, .18) !important;
        }

        .custom-swal-title {
            font-family: 'Poppins', sans-serif !important;
            font-size: 21px !important;
            font-weight: 900 !important;
            color: #0f172a !important;
        }

        .custom-swal-html {
            color: #64748b !important;
            font-size: 14px !important;
            font-weight: 600 !important;
        }

        .custom-swal-confirm-btn {
            border: 0 !important;
            outline: 0 !important;
            cursor: pointer !important;
            color: white !important;
            background: #059669 !important;
            border-radius: 14px !important;
            padding: 12px 22px !important;
            font-size: 13px !important;
            font-weight: 900 !important;
            transition: all .25s ease !important;
        }

        .custom-swal-confirm-btn:hover {
            background: #047857 !important;
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(5,150,105,.24);
        }

        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 1280px) {
            .auth-stage {
                padding: 22px 30px;
            }

            .wave-wrap svg {
                height: 212px;
            }

            .dot-pattern.bottom-right {
                right: 70px;
            }
        }

        @media (max-width: 1024px) {
            body {
                overflow-y: auto;
            }

            .auth-page {
                min-height: 100svh;
            }

            .auth-stage {
                min-height: 100svh;
                padding: 22px 18px;
                align-items: center;
            }

            .dot-pattern.top-left {
                top: 28px;
                left: 28px;
                opacity: .36;
            }

            .dot-pattern.bottom-right {
                display: none;
            }

            .subtle-ring {
                width: 300px;
                height: 300px;
                right: -120px;
                top: 18%;
                opacity: .45;
            }

            .leaf-cluster {
                width: 310px;
                height: 310px;
                left: -86px;
                bottom: -82px;
                opacity: .15;
            }

            .circle-blur {
                left: -70px;
                bottom: 162px;
                opacity: .42;
            }

            .wave-wrap svg {
                height: 165px;
            }
        }

        @media (max-width: 768px) {
            .auth-stage {
                padding: 18px 16px;
            }

            .soft-glow {
                filter: blur(70px);
            }

            .soft-glow.one {
                width: 360px;
                height: 360px;
                top: -120px;
                left: -110px;
            }

            .soft-glow.two {
                width: 360px;
                height: 360px;
                right: -140px;
                bottom: -120px;
            }

            .soft-glow.three {
                width: 280px;
                height: 280px;
                top: 24%;
                right: -70px;
            }

            .soft-glow.four {
                display: none;
            }

            .wave-wrap svg {
                height: 150px;
                width: 160%;
                transform: translateX(-24%);
            }

            .split-panel {
                width: 50.2%;
            }

            .transition-box {
                width: 188px;
                min-height: 156px;
                border-radius: 30px;
            }

            .transition-icon {
                width: 64px;
                height: 64px;
                border-radius: 23px;
            }

            .transition-icon i {
                font-size: 27px;
            }
        }

        @media (max-width: 480px) {
            .auth-stage {
                padding: 14px 14px;
            }

            .dot-pattern.top-left {
                width: 74px;
                height: 74px;
                top: 20px;
                left: 18px;
                background-size: 9px 9px;
                opacity: .30;
            }

            .leaf-cluster {
                opacity: .12;
            }

            .wave-wrap svg {
                height: 135px;
                width: 190%;
                transform: translateX(-33%);
            }

            .transition-text {
                font-size: 12.5px;
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

<body>
    <div class="auth-page">

        {{-- BACKGROUND --}}
        <div class="auth-bg" aria-hidden="true">
            <div class="soft-glow one"></div>
            <div class="soft-glow two"></div>
            <div class="soft-glow three"></div>
            <div class="soft-glow four"></div>

            <div class="dot-pattern top-left"></div>
            <div class="dot-pattern bottom-right"></div>

            <div class="circle-blur"></div>
            <div class="subtle-ring"></div>

            <div class="leaf-cluster">
                <span class="leaf-line"></span>
                <span class="leaf l1"></span>
                <span class="leaf l2"></span>
                <span class="leaf l3"></span>
                <span class="leaf l4"></span>
            </div>

            <div class="wave-wrap">
                <svg viewBox="0 0 1440 320" preserveAspectRatio="none">
                    <path fill="#10b981"
                          fill-opacity="0.12"
                          d="M0,256L48,240C96,224,192,192,288,186.7C384,181,480,203,576,224C672,245,768,267,864,261.3C960,256,1056,224,1152,202.7C1248,181,1344,171,1392,165.3L1440,160L1440,320L0,320Z">
                    </path>

                    <path fill="#0f766e"
                          fill-opacity="0.14"
                          d="M0,288L60,272C120,256,240,224,360,224C480,224,600,256,720,266.7C840,277,960,267,1080,240C1200,213,1320,171,1380,149.3L1440,128L1440,320L0,320Z">
                    </path>

                    <path fill="#059669"
                          fill-opacity="0.08"
                          d="M0,300L80,286.7C160,273,320,247,480,245.3C640,243,800,267,960,261.3C1120,256,1280,224,1360,208L1440,192L1440,320L0,320Z">
                    </path>
                </svg>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <main id="authGlobalStage" class="auth-stage">
            @yield('content')
        </main>

        {{-- SPLIT TRANSITION --}}
        <div class="split-transition-layer" aria-hidden="true">
            <div id="splitPanelLeft" class="split-panel split-panel-left">
                <div class="split-decor"></div>

                <div class="split-wave">
                    <svg viewBox="0 0 720 320" preserveAspectRatio="none">
                        <path fill="#ffffff"
                              fill-opacity="0.15"
                              d="M0,224L48,213.3C96,203,192,181,288,192C384,203,480,245,576,250.7C672,256,768,224,816,208L864,192L864,320L0,320Z">
                        </path>
                    </svg>
                </div>
            </div>

            <div id="splitPanelRight" class="split-panel split-panel-right">
                <div class="split-decor"></div>

                <div class="split-wave">
                    <svg viewBox="0 0 720 320" preserveAspectRatio="none">
                        <path fill="#ffffff"
                              fill-opacity="0.15"
                              d="M0,192L48,208C96,224,192,256,288,250.7C384,245,480,203,576,192C672,181,768,203,816,213.3L864,224L864,320L0,320Z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- CENTER TRANSITION --}}
        <div id="transitionBrand" class="transition-brand" aria-hidden="true">
            <div class="transition-box">
                <div class="transition-icon">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>

                <div class="transition-text">
                    PosyanduCare
                </div>

                <div class="transition-loader"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const authStage = document.getElementById('authGlobalStage');
            const splitLeft = document.getElementById('splitPanelLeft');
            const splitRight = document.getElementById('splitPanelRight');
            const transitionBrand = document.getElementById('transitionBrand');

            window.PosyanduAuthTransition = {
                play: function () {
                    if (authStage) {
                        authStage.classList.add('is-leaving');
                    }

                    setTimeout(function () {
                        if (splitLeft) {
                            splitLeft.classList.add('gate-lock-closed');
                        }

                        if (splitRight) {
                            splitRight.classList.add('gate-lock-closed');
                        }
                    }, 140);

                    setTimeout(function () {
                        if (transitionBrand) {
                            transitionBrand.classList.add('is-visible');
                        }
                    }, 470);
                },

                reset: function () {
                    if (authStage) {
                        authStage.classList.remove('is-leaving');
                    }

                    if (splitLeft) {
                        splitLeft.classList.remove('gate-lock-closed');
                    }

                    if (splitRight) {
                        splitRight.classList.remove('gate-lock-closed');
                    }

                    if (transitionBrand) {
                        transitionBrand.classList.remove('is-visible');
                    }
                }
            };

            window.addEventListener('pageshow', function () {
                if (window.PosyanduAuthTransition) {
                    window.PosyanduAuthTransition.reset();
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>