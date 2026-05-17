@extends('layouts.auth')

@section('title', 'Selamat Datang | PosyanduCare')

@push('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | POSYANDUCARE LOGIN PAGE, REFINED FINAL
    |--------------------------------------------------------------------------
    */

    .login-page-shell {
        width: 100%;
        max-width: 1360px;
        min-height: min(720px, calc(100vh - 46px));
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(470px, 0.92fr);
        align-items: center;
        gap: 70px;
        position: relative;
        padding: 0 42px;
    }

    /* =========================
       LEFT BRANDING
    ========================== */

    .brand-side {
        position: relative;
        width: 100%;
        min-height: 620px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 0;
    }

    .brand-side::before {
        content: "";
        position: absolute;
        width: 560px;
        height: 560px;
        border-radius: 999px;
        left: 50%;
        top: 48%;
        transform: translate(-50%, -50%);
        background:
            radial-gradient(circle, rgba(255,255,255,.68), rgba(255,255,255,.24) 48%, transparent 72%);
        z-index: -1;
        opacity: .86;
    }

    .brand-side::after {
        content: "";
        position: absolute;
        width: 420px;
        height: 420px;
        left: -90px;
        bottom: -70px;
        border-radius: 44% 56% 50% 50%;
        background:
            radial-gradient(circle at 32% 35%, rgba(16,185,129,.16), transparent 54%),
            linear-gradient(135deg, rgba(16,185,129,.12), rgba(255,255,255,0));
        z-index: -2;
        opacity: .72;
    }

    .brand-content {
        width: 100%;
        max-width: 570px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transform: translateY(-4px);
    }

    .brand-logo-main {
        width: 430px;
        max-width: 100%;
        height: auto;
        display: block;
        object-fit: contain;
        margin-bottom: 18px;
        filter:
            drop-shadow(0 18px 28px rgba(5,150,105,.08))
            drop-shadow(0 8px 12px rgba(15,23,42,.035));
        user-select: none;
        pointer-events: none;
    }

    .brand-title-main {
        max-width: 560px;
        margin: 0;
        color: #0f172a;
        font-size: 26px;
        line-height: 1.28;
        font-weight: 900;
        letter-spacing: -0.055em;
    }

    .brand-divider-main {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 13px;
        margin: 14px 0 18px;
    }

    .brand-divider-line {
        width: 55px;
        height: 1.6px;
        border-radius: 999px;
    }

    .brand-divider-line.left {
        background: linear-gradient(to right, transparent, #f59e0b);
    }

    .brand-divider-line.right {
        background: linear-gradient(to left, transparent, #f59e0b);
    }

    .brand-divider-dot {
        width: 8px;
        height: 8px;
        border-radius: 2px;
        background: #f59e0b;
        transform: rotate(45deg);
        box-shadow: 0 4px 10px rgba(245,158,11,.26);
    }

    .brand-description-main {
        max-width: 445px;
        margin: 0 0 26px;
        color: #64748b;
        font-size: 15.5px;
        line-height: 1.72;
        font-weight: 650;
    }

    .feature-list {
        width: 100%;
        max-width: 455px;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .feature-box {
        aspect-ratio: 1 / 1;
        border-radius: 24px;
        background:
            linear-gradient(180deg, rgba(255,255,255,.82), rgba(255,255,255,.58));
        border: 1px solid rgba(255,255,255,.88);
        box-shadow:
            0 18px 35px rgba(15,23,42,.055),
            inset 0 1px 0 rgba(255,255,255,.82);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 10px;
        transition:
            transform .34s cubic-bezier(.16,1,.3,1),
            box-shadow .34s ease,
            background .34s ease;
    }

    .feature-box:hover {
        transform: translateY(-7px);
        background: rgba(255,255,255,.92);
        box-shadow: 0 24px 44px rgba(16,185,129,.16);
    }

    .feature-icon {
        width: 43px;
        height: 43px;
        border-radius: 16px;
        background: rgba(5,150,105,.10);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .feature-text {
        color: #1e293b;
        font-size: 12px;
        line-height: 1;
        font-weight: 900;
    }

    /* =========================
       RIGHT LOGIN CARD
    ========================== */

    .form-side {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-card {
        position: relative;
        width: 100%;
        max-width: 535px;
        min-height: 600px;
        border-radius: 38px;
        padding: 48px 52px 42px;
        overflow: hidden;
        background:
            linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.91));
        border: 1px solid rgba(226,232,240,.86);
        box-shadow:
            0 34px 90px rgba(15,23,42,.09),
            0 12px 32px rgba(15,23,42,.045),
            inset 0 1px 0 rgba(255,255,255,.92);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
    }

    .login-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 15% 0%, rgba(16,185,129,.085), transparent 34%),
            radial-gradient(circle at 94% 100%, rgba(245,158,11,.055), transparent 32%);
        pointer-events: none;
    }

    .login-card::after {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        right: -110px;
        top: -120px;
        border-radius: 999px;
        background: rgba(16,185,129,.055);
        pointer-events: none;
    }

    .login-card-inner {
        position: relative;
        z-index: 2;
    }

    .mobile-brand {
        display: none;
    }

    .login-header {
        text-align: center;
        margin-bottom: 36px;
    }

    .login-title {
        margin: 0 0 11px;
        color: #064e3b;
        font-size: 30px;
        line-height: 1.14;
        font-weight: 900;
        letter-spacing: -0.055em;
    }

    .login-subtitle {
        margin: 0;
        color: #64748b;
        font-size: 15px;
        line-height: 1.55;
        font-weight: 650;
    }

    .login-form {
        display: flex;
        flex-direction: column;
        gap: 21px;
    }

    .form-group-auth {
        width: 100%;
    }

    .auth-label {
        display: block;
        margin: 0 0 10px 2px;
        color: #1e293b;
        font-size: 12.5px;
        font-weight: 900;
        letter-spacing: .06em;
    }

    .input-wrap-auth {
        position: relative;
        width: 100%;
    }

    .auth-input-control {
        width: 100%;
        height: 58px;
        border: 1px solid #dbe5ee;
        border-radius: 16px;
        padding: 0 52px 0 52px;
        color: #0f172a;
        background: rgba(255,255,255,.88);
        outline: none;
        font-size: 15px;
        font-weight: 650;
        transition:
            border-color .24s ease,
            box-shadow .24s ease,
            background .24s ease,
            transform .24s ease;
    }

    .auth-input-control::placeholder {
        color: #94a3b8;
        font-weight: 600;
    }

    .auth-input-control:hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }

    .auth-input-control:focus {
        background: #ffffff;
        border-color: #059669;
        box-shadow:
            0 0 0 5px rgba(5,150,105,.10),
            0 12px 24px rgba(5,150,105,.06);
    }

    .input-icon-left {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #0f766e;
        font-size: 18px;
        pointer-events: none;
        transition: color .24s ease;
    }

    .auth-input-control:not(:focus) ~ .input-icon-left {
        color: #94a3b8;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 13px;
        background: transparent;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition:
            color .24s ease,
            background .24s ease;
    }

    .password-toggle:hover {
        color: #059669;
        background: #f8fafc;
    }

    .forgot-row {
        display: flex;
        justify-content: flex-end;
        margin-top: -2px;
    }

    .forgot-link {
        color: #059669;
        font-size: 13.5px;
        font-weight: 900;
        text-decoration: none;
    }

    .forgot-link:hover {
        color: #047857;
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    .submit-area {
        padding-top: 16px;
    }

    .submit-btn {
        width: 100%;
        height: 62px;
        border: 0;
        border-radius: 17px;
        color: white;
        cursor: pointer;
        background:
            linear-gradient(135deg, #047857 0%, #059669 48%, #10b981 100%);
        box-shadow:
            0 18px 36px rgba(5,150,105,.30),
            inset 0 1px 0 rgba(255,255,255,.22);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        font-size: 16px;
        font-weight: 900;
        transition:
            transform .28s cubic-bezier(.16,1,.3,1),
            box-shadow .28s ease,
            filter .28s ease;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        filter: saturate(1.05);
        box-shadow:
            0 24px 44px rgba(5,150,105,.36),
            inset 0 1px 0 rgba(255,255,255,.26);
    }

    .submit-btn:disabled {
        cursor: wait;
        opacity: .9;
        transform: none;
    }

    .submit-btn i {
        font-size: 14px;
        transition: transform .24s ease;
    }

    .submit-btn:hover i.fa-arrow-right {
        transform: translateX(5px);
    }

    /* =========================
       REPLACEMENT INFO SECTION
    ========================== */

    .system-note {
        margin-top: 30px;
        padding: 18px 18px;
        border-radius: 20px;
        background:
            linear-gradient(135deg, rgba(236,253,245,.86), rgba(255,255,255,.72));
        border: 1px solid rgba(16,185,129,.16);
        box-shadow:
            0 14px 30px rgba(15,23,42,.045),
            inset 0 1px 0 rgba(255,255,255,.80);
        display: flex;
        align-items: flex-start;
        gap: 13px;
    }

    .system-note-icon {
        flex: 0 0 auto;
        width: 38px;
        height: 38px;
        border-radius: 14px;
        background: rgba(5,150,105,.10);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .system-note-content {
        min-width: 0;
    }

    .system-note-title {
        margin: 0 0 4px;
        color: #064e3b;
        font-size: 13.8px;
        line-height: 1.35;
        font-weight: 900;
    }

    .system-note-text {
        margin: 0;
        color: #64748b;
        font-size: 12.8px;
        line-height: 1.62;
        font-weight: 650;
    }

    /* =========================
       MOBILE DECOR
    ========================== */

    .leaf-mobile-decor {
        position: absolute;
        display: none;
        pointer-events: none;
        z-index: 1;
    }

    .leaf-mobile-decor .leaf-small {
        position: absolute;
        border-radius: 100% 0 100% 0;
        background:
            linear-gradient(135deg, rgba(4,120,87,.36), rgba(16,185,129,.06));
        box-shadow: inset 8px 8px 14px rgba(255,255,255,.16);
    }

    /* =========================
       ENTRANCE ANIMATION
    ========================== */

    .auth-fade-left {
        opacity: 0;
        transform: translateX(-34px);
        animation: loginEnterLeft .82s cubic-bezier(.16,1,.3,1) forwards;
    }

    .auth-fade-right {
        opacity: 0;
        transform: translateX(34px);
        animation: loginEnterRight .82s cubic-bezier(.16,1,.3,1) forwards;
    }

    .auth-fade-up {
        opacity: 0;
        transform: translateY(24px);
        animation: loginEnterUp .72s cubic-bezier(.16,1,.3,1) forwards;
    }

    .delay-1 { animation-delay: .08s; }
    .delay-2 { animation-delay: .15s; }
    .delay-3 { animation-delay: .22s; }
    .delay-4 { animation-delay: .29s; }
    .delay-5 { animation-delay: .36s; }

    @keyframes loginEnterLeft {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes loginEnterRight {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes loginEnterUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* =========================
       TABLET
    ========================== */

    @media (max-width: 1280px) {
        .login-page-shell {
            max-width: 1180px;
            grid-template-columns: minmax(0, 1fr) minmax(440px, .92fr);
            gap: 56px;
            padding: 0 28px;
        }

        .brand-side {
            min-height: 590px;
        }

        .brand-logo-main {
            width: 390px;
            margin-bottom: 16px;
        }

        .brand-title-main {
            font-size: 24px;
        }

        .brand-description-main {
            font-size: 15px;
            margin-bottom: 24px;
        }

        .feature-list {
            max-width: 430px;
            gap: 14px;
        }

        .login-card {
            max-width: 500px;
            min-height: 585px;
            padding: 44px 46px 38px;
        }

        .login-title {
            font-size: 28px;
        }
    }

    /* =========================
       MOBILE
    ========================== */

    @media (max-width: 1024px) {
        .login-page-shell {
            min-height: calc(100svh - 28px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 0;
        }

        .brand-side {
            display: none;
        }

        .form-side {
            width: 100%;
            position: relative;
        }

        .form-side::before {
            content: "";
            position: absolute;
            width: 330px;
            height: 330px;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border-radius: 999px;
            background:
                radial-gradient(circle, rgba(255,255,255,.70), rgba(255,255,255,.20) 54%, transparent 72%);
            z-index: 0;
        }

        .login-card {
            max-width: 460px;
            min-height: auto;
            padding: 28px 26px 26px;
            border-radius: 32px;
            z-index: 2;
        }

        .mobile-brand {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .mobile-brand img {
            width: 164px;
            height: auto;
            display: block;
            object-fit: contain;
            filter: drop-shadow(0 10px 18px rgba(5,150,105,.08));
        }

        .login-header {
            margin-bottom: 28px;
        }

        .login-title {
            font-size: 24px;
        }

        .login-subtitle {
            font-size: 13.5px;
        }

        .auth-label {
            font-size: 11.2px;
            margin-bottom: 8px;
        }

        .auth-input-control {
            height: 52px;
            border-radius: 15px;
            font-size: 13.3px;
            padding-left: 46px;
        }

        .input-icon-left {
            left: 17px;
            font-size: 15px;
        }

        .submit-btn {
            height: 54px;
            border-radius: 15px;
            font-size: 14.2px;
        }

        .system-note {
            margin-top: 24px;
            padding: 15px 15px;
            border-radius: 18px;
        }

        .system-note-icon {
            width: 35px;
            height: 35px;
            border-radius: 13px;
            font-size: 15px;
        }

        .system-note-title {
            font-size: 13px;
        }

        .system-note-text {
            font-size: 12.2px;
        }

        .leaf-mobile-decor {
            display: block;
            width: 250px;
            height: 250px;
            left: -78px;
            bottom: -72px;
            opacity: .18;
            transform: rotate(-10deg);
        }

        .leaf-mobile-decor .leaf-small.one {
            width: 120px;
            height: 70px;
            left: 20px;
            bottom: 60px;
            transform: rotate(-24deg);
        }

        .leaf-mobile-decor .leaf-small.two {
            width: 135px;
            height: 76px;
            left: 78px;
            bottom: 110px;
            transform: rotate(-4deg);
        }

        .leaf-mobile-decor .leaf-small.three {
            width: 98px;
            height: 58px;
            left: 130px;
            bottom: 45px;
            transform: rotate(22deg);
        }
    }

    @media (max-width: 640px) {
        .login-page-shell {
            min-height: calc(100svh - 20px);
            padding: 12px 0;
        }

        .login-card {
            max-width: 100%;
            width: min(100%, 390px);
            border-radius: 29px;
            padding: 25px 22px 23px;
        }

        .mobile-brand img {
            width: 150px;
        }

        .login-title {
            font-size: 22px;
        }

        .login-subtitle {
            font-size: 12.6px;
        }

        .login-form {
            gap: 17px;
        }

        .forgot-link {
            font-size: 12.4px;
        }

        .system-note {
            gap: 11px;
        }
    }

    @media (max-width: 390px) {
        .login-card {
            width: 100%;
            border-radius: 25px;
            padding: 23px 18px 21px;
        }

        .mobile-brand img {
            width: 140px;
        }

        .login-title {
            font-size: 20px;
        }

        .auth-input-control {
            height: 50px;
            font-size: 12.8px;
        }

        .system-note-text {
            font-size: 11.8px;
        }
    }
</style>
@endpush

@section('content')
<div class="login-page-shell">

    {{-- DEKORASI DAUN MOBILE --}}
    <div class="leaf-mobile-decor" aria-hidden="true">
        <span class="leaf-small one"></span>
        <span class="leaf-small two"></span>
        <span class="leaf-small three"></span>
    </div>

    {{-- KIRI: BRANDING --}}
    <section class="brand-side auth-fade-left">
        <div class="brand-content">

            <img
                src="{{ asset('img/logo.png') }}"
                alt="Logo PosyanduCare"
                class="brand-logo-main"
            >

            <h2 class="brand-title-main">
                Sehat Bersama, Tumbuh Setiap Generasi
            </h2>

            <div class="brand-divider-main">
                <span class="brand-divider-line left"></span>
                <span class="brand-divider-dot"></span>
                <span class="brand-divider-line right"></span>
            </div>

            <p class="brand-description-main">
                Platform layanan kesehatan terpadu untuk masyarakat modern.
            </p>

            <div class="feature-list">
                @php
                    $features = [
                        ['icon' => 'fa-user-group', 'text' => 'Terintegrasi'],
                        ['icon' => 'fa-shield-halved', 'text' => 'Aman'],
                        ['icon' => 'fa-chart-simple', 'text' => 'Efisien'],
                        ['icon' => 'fa-heart-pulse', 'text' => 'Peduli'],
                    ];
                @endphp

                @foreach($features as $index => $feature)
                    <div class="feature-box auth-fade-up delay-{{ $index + 1 }}">
                        <div class="feature-icon">
                            <i class="fa-solid {{ $feature['icon'] }}"></i>
                        </div>

                        <span class="feature-text">
                            {{ $feature['text'] }}
                        </span>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    {{-- KANAN: FORM --}}
    <section class="form-side auth-fade-right delay-1">
        <div class="login-card">
            <div class="login-card-inner">

                {{-- LOGO KHUSUS MOBILE --}}
                <div class="mobile-brand">
                    <img
                        src="{{ asset('img/logo.png') }}"
                        alt="Logo PosyanduCare"
                    >
                </div>

                <div class="login-header">
                    <h1 class="login-title">
                        Selamat Datang Kembali!
                    </h1>

                    <p class="login-subtitle">
                        Masuk untuk melanjutkan ke Portal PosyanduCare
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('login.post') }}"
                    id="loginFormEngine"
                    class="login-form"
                >
                    @csrf

                    <div class="form-group-auth">
                        <label for="login" class="auth-label">
                            Email atau Username
                        </label>

                        <div class="input-wrap-auth">
                            <input
                                type="text"
                                id="login"
                                name="login"
                                value="{{ old('login') }}"
                                class="auth-input-control"
                                placeholder="Masukkan email atau username"
                                required
                                autofocus
                                autocomplete="off"
                            >

                            <i class="fa-regular fa-user input-icon-left"></i>
                        </div>
                    </div>

                    <div class="form-group-auth">
                        <label for="password" class="auth-label">
                            Password
                        </label>

                        <div class="input-wrap-auth">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="auth-input-control"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password"
                            >

                            <i class="fa-solid fa-lock input-icon-left"></i>

                            <button
                                type="button"
                                onclick="toggleVision()"
                                class="password-toggle"
                                aria-label="Tampilkan atau sembunyikan password"
                            >
                                <i class="fa-regular fa-eye-slash" id="visionIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="forgot-row">
                        <a href="#" class="forgot-link">
                            Lupa password?
                        </a>
                    </div>

                    <div class="submit-area">
                        <button
                            type="submit"
                            id="submitActionBtn"
                            class="submit-btn"
                        >
                            <span id="submitTxt">Masuk</span>
                            <i class="fa-solid fa-arrow-right" id="submitIcon"></i>
                        </button>
                    </div>
                </form>

                {{-- PENGGANTI SOCIAL LOGIN --}}
                <div class="system-note auth-fade-up delay-3">
                    <div class="system-note-icon">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>

                    <div class="system-note-content">
                        <p class="system-note-title">
                            Akses khusus pengguna terdaftar
                        </p>

                        <p class="system-note-text">
                            Gunakan akun yang telah dibuat oleh petugas Posyandu untuk mengakses layanan kesehatan digital.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    function toggleVision() {
        const input = document.getElementById('password');
        const icon = document.getElementById('visionIcon');

        if (!input || !icon) {
            return;
        }

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('loginFormEngine');
        const btn = document.getElementById('submitActionBtn');
        const txt = document.getElementById('submitTxt');
        const ico = document.getElementById('submitIcon');

        if (!form || !btn || !txt || !ico) {
            return;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            btn.disabled = true;
            btn.style.pointerEvents = 'none';
            txt.innerText = 'Memverifikasi akun...';

            ico.classList.remove('fa-arrow-right');
            ico.classList.add('fa-spinner', 'fa-spin');

            setTimeout(function () {
                if (window.PosyanduAuthTransition) {
                    window.PosyanduAuthTransition.play();
                }

                setTimeout(function () {
                    form.submit();
                }, 980);
            }, 160);
        });
    });
</script>

@if($errors->any() || session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Otentikasi Gagal',
                html: @json($errors->first()),
                confirmButtonText: 'Coba Lagi',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    htmlContainer: 'custom-swal-html',
                    confirmButton: 'custom-swal-confirm-btn'
                },
                buttonsStyling: false,
                backdrop: 'rgba(15, 23, 42, 0.42)'
            });
        @endif

        @if(session('info'))
            Swal.fire({
                icon: 'success',
                title: 'Informasi Sistem',
                html: @json(session('info')),
                confirmButtonText: 'Tutup',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    htmlContainer: 'custom-swal-html',
                    confirmButton: 'custom-swal-confirm-btn'
                },
                buttonsStyling: false,
                timer: 4000,
                timerProgressBar: true,
                backdrop: 'rgba(15, 23, 42, 0.30)'
            });
        @endif
    });
</script>
@endif
@endpush