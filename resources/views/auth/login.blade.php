@extends('layouts.auth')

@section('title', 'Login | PosyanduCare')

@push('styles')
<style>
    .login-page-shell {
        width: 100%;
        max-width: 1280px;
        min-height: min(700px, calc(100svh - 36px));
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(420px, .88fr);
        align-items: center;
        gap: 56px;
        position: relative;
        padding: 0 32px;
    }

    .brand-side {
        position: relative;
        width: 100%;
        min-height: 580px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brand-side::before {
        content: "";
        position: absolute;
        width: 520px;
        height: 520px;
        border-radius: 999px;
        left: 50%;
        top: 48%;
        transform: translate(-50%, -50%);
        background:
            radial-gradient(circle, rgba(255,255,255,.60), rgba(255,255,255,.20) 50%, transparent 72%);
        z-index: -1;
        opacity: .86;
    }

    .brand-content {
        width: 100%;
        max-width: 540px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transform: translate3d(0, -3px, 0);
    }

    .brand-logo-main {
        width: 390px;
        max-width: 100%;
        height: auto;
        display: block;
        object-fit: contain;
        margin-bottom: 16px;
        filter: drop-shadow(0 14px 24px rgba(5,150,105,.08));
        user-select: none;
        pointer-events: none;
    }

    .brand-title-main {
        max-width: 520px;
        margin: 0;
        color: #0f172a;
        font-size: 25px;
        line-height: 1.28;
        font-weight: 900;
        letter-spacing: -0.05em;
    }

    .brand-divider-main {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 13px;
        margin: 14px 0 17px;
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
        max-width: 420px;
        margin: 0 0 24px;
        color: #64748b;
        font-size: 15px;
        line-height: 1.7;
        font-weight: 650;
    }

    .feature-list {
        width: 100%;
        max-width: 430px;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .feature-box {
        aspect-ratio: 1 / 1;
        border-radius: 23px;
        background:
            linear-gradient(180deg, rgba(255,255,255,.82), rgba(255,255,255,.58));
        border: 1px solid rgba(255,255,255,.88);
        box-shadow:
            0 14px 28px rgba(15,23,42,.045),
            inset 0 1px 0 rgba(255,255,255,.82);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 9px;
        transition:
            transform .26s cubic-bezier(.16,1,.3,1),
            box-shadow .26s ease,
            background .26s ease;
    }

    .feature-box:hover {
        transform: translate3d(0, -5px, 0);
        background: rgba(255,255,255,.92);
        box-shadow: 0 20px 36px rgba(16,185,129,.12);
    }

    .feature-icon {
        width: 40px;
        height: 40px;
        border-radius: 15px;
        background: rgba(5,150,105,.10);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }

    .feature-text {
        color: #1e293b;
        font-size: 11.5px;
        line-height: 1;
        font-weight: 900;
    }

    .form-side {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-card {
        position: relative;
        width: 100%;
        max-width: 500px;
        min-height: 570px;
        border-radius: 36px;
        padding: 42px 46px 38px;
        overflow: hidden;
        background:
            linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.90));
        border: 1px solid rgba(226,232,240,.86);
        box-shadow:
            0 26px 70px rgba(15,23,42,.08),
            0 10px 26px rgba(15,23,42,.04),
            inset 0 1px 0 rgba(255,255,255,.92);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    .login-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 15% 0%, rgba(16,185,129,.08), transparent 34%),
            radial-gradient(circle at 94% 100%, rgba(245,158,11,.055), transparent 32%);
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
        margin-bottom: 32px;
    }

    .login-title {
        margin: 0 0 10px;
        color: #064e3b;
        font-size: 29px;
        line-height: 1.14;
        font-weight: 900;
        letter-spacing: -0.052em;
    }

    .login-subtitle {
        margin: 0;
        color: #64748b;
        font-size: 14.5px;
        line-height: 1.55;
        font-weight: 650;
    }

    .login-form {
        display: flex;
        flex-direction: column;
        gap: 19px;
    }

    .form-group-auth {
        width: 100%;
    }

    .auth-label {
        display: block;
        margin: 0 0 9px 2px;
        color: #1e293b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .055em;
    }

    .input-wrap-auth {
        position: relative;
        width: 100%;
    }

    .auth-input-control {
        width: 100%;
        height: 56px;
        border: 1px solid #dbe5ee;
        border-radius: 16px;
        padding: 0 52px 0 50px;
        color: #0f172a;
        background: rgba(255,255,255,.88);
        outline: none;
        font-size: 14.5px;
        font-weight: 650;
        transition:
            border-color .20s ease,
            box-shadow .20s ease,
            background .20s ease;
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
            0 0 0 4px rgba(5,150,105,.095),
            0 10px 22px rgba(5,150,105,.055);
    }

    .input-icon-left {
        position: absolute;
        left: 17px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 17px;
        pointer-events: none;
        transition: color .20s ease;
    }

    .auth-input-control:focus ~ .input-icon-left {
        color: #0f766e;
    }

    .password-toggle {
        position: absolute;
        right: 9px;
        top: 50%;
        transform: translateY(-50%);
        width: 39px;
        height: 39px;
        border: 0;
        border-radius: 13px;
        background: transparent;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition:
            color .20s ease,
            background .20s ease;
    }

    .password-toggle:hover {
        color: #059669;
        background: #f8fafc;
    }

    .forgot-row {
        display: flex;
        justify-content: flex-end;
        margin-top: -3px;
    }

    .forgot-link {
        color: #059669;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
    }

    .forgot-link:hover {
        color: #047857;
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    .submit-area {
        padding-top: 12px;
    }

    .submit-btn {
        width: 100%;
        height: 59px;
        border: 0;
        border-radius: 17px;
        color: white;
        cursor: pointer;
        background:
            linear-gradient(135deg, #047857 0%, #059669 48%, #10b981 100%);
        box-shadow:
            0 16px 32px rgba(5,150,105,.27),
            inset 0 1px 0 rgba(255,255,255,.22);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 11px;
        font-size: 15.5px;
        font-weight: 900;
        transition:
            transform .22s cubic-bezier(.16,1,.3,1),
            box-shadow .22s ease,
            filter .22s ease,
            opacity .22s ease;
    }

    .submit-btn:hover {
        transform: translate3d(0, -2px, 0);
        filter: saturate(1.05);
        box-shadow:
            0 21px 38px rgba(5,150,105,.32),
            inset 0 1px 0 rgba(255,255,255,.26);
    }

    .submit-btn:disabled {
        cursor: wait;
        opacity: .88;
        transform: none;
    }

    .submit-btn i {
        font-size: 14px;
    }

    .system-note {
        margin-top: 27px;
        padding: 16px 17px;
        border-radius: 20px;
        background:
            linear-gradient(135deg, rgba(236,253,245,.84), rgba(255,255,255,.72));
        border: 1px solid rgba(16,185,129,.15);
        box-shadow:
            0 12px 24px rgba(15,23,42,.04),
            inset 0 1px 0 rgba(255,255,255,.78);
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .system-note-icon {
        flex: 0 0 auto;
        width: 37px;
        height: 37px;
        border-radius: 14px;
        background: rgba(5,150,105,.10);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    .system-note-content {
        min-width: 0;
    }

    .system-note-title {
        margin: 0 0 4px;
        color: #064e3b;
        font-size: 13.5px;
        line-height: 1.35;
        font-weight: 900;
    }

    .system-note-text {
        margin: 0;
        color: #64748b;
        font-size: 12.4px;
        line-height: 1.58;
        font-weight: 650;
    }

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
            linear-gradient(135deg, rgba(4,120,87,.30), rgba(16,185,129,.06));
        box-shadow: inset 8px 8px 14px rgba(255,255,255,.14);
    }

    .auth-fade-left {
        opacity: 0;
        transform: translate3d(-24px, 0, 0);
        animation: loginEnterLeft .46s cubic-bezier(.16,1,.3,1) forwards;
    }

    .auth-fade-right {
        opacity: 0;
        transform: translate3d(24px, 0, 0);
        animation: loginEnterRight .46s cubic-bezier(.16,1,.3,1) forwards;
    }

    .auth-fade-up {
        opacity: 0;
        transform: translate3d(0, 16px, 0);
        animation: loginEnterUp .38s cubic-bezier(.16,1,.3,1) forwards;
    }

    .delay-1 {
        animation-delay: .04s;
    }

    .delay-2 {
        animation-delay: .08s;
    }

    .delay-3 {
        animation-delay: .12s;
    }

    .delay-4 {
        animation-delay: .16s;
    }

    .delay-5 {
        animation-delay: .20s;
    }

    @keyframes loginEnterLeft {
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes loginEnterRight {
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes loginEnterUp {
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @media (max-width: 1180px) {
        .login-page-shell {
            grid-template-columns: minmax(0, 1fr) minmax(410px, .92fr);
            gap: 44px;
            padding: 0 24px;
        }

        .brand-logo-main {
            width: 355px;
        }

        .brand-title-main {
            font-size: 23px;
        }

        .login-card {
            max-width: 480px;
            padding: 39px 42px 36px;
        }
    }

    @media (max-width: 1024px) {
        .login-page-shell {
            min-height: calc(100svh - 24px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 0;
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
            width: 280px;
            height: 280px;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border-radius: 999px;
            background:
                radial-gradient(circle, rgba(255,255,255,.62), rgba(255,255,255,.16) 54%, transparent 72%);
            z-index: 0;
        }

        .login-card {
            max-width: 440px;
            min-height: auto;
            padding: 27px 25px 25px;
            border-radius: 31px;
            z-index: 2;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .mobile-brand {
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
        }

        .mobile-brand img {
            width: 156px;
            height: auto;
            display: block;
            object-fit: contain;
            filter: drop-shadow(0 8px 14px rgba(5,150,105,.07));
        }

        .login-header {
            margin-bottom: 25px;
        }

        .login-title {
            font-size: 23px;
        }

        .login-subtitle {
            font-size: 13px;
        }

        .auth-label {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .auth-input-control {
            height: 51px;
            border-radius: 15px;
            font-size: 13px;
            padding-left: 45px;
        }

        .input-icon-left {
            left: 16px;
            font-size: 15px;
        }

        .password-toggle {
            width: 38px;
            height: 38px;
        }

        .submit-btn {
            height: 53px;
            border-radius: 15px;
            font-size: 14px;
        }

        .system-note {
            margin-top: 22px;
            padding: 14px;
            border-radius: 18px;
        }

        .system-note-icon {
            width: 34px;
            height: 34px;
            border-radius: 13px;
            font-size: 14px;
        }

        .system-note-title {
            font-size: 12.8px;
        }

        .system-note-text {
            font-size: 11.8px;
        }

        .leaf-mobile-decor {
            display: block;
            width: 220px;
            height: 220px;
            left: -76px;
            bottom: -70px;
            opacity: .14;
            transform: rotate(-10deg);
        }

        .leaf-mobile-decor .leaf-small.one {
            width: 110px;
            height: 64px;
            left: 18px;
            bottom: 54px;
            transform: rotate(-24deg);
        }

        .leaf-mobile-decor .leaf-small.two {
            width: 125px;
            height: 70px;
            left: 74px;
            bottom: 100px;
            transform: rotate(-4deg);
        }

        .leaf-mobile-decor .leaf-small.three {
            width: 88px;
            height: 52px;
            left: 120px;
            bottom: 42px;
            transform: rotate(22deg);
        }
    }

    @media (max-width: 640px) {
        .login-page-shell {
            min-height: calc(100svh - 18px);
            padding: 8px 0;
        }

        .login-card {
            max-width: 100%;
            width: min(100%, 388px);
            border-radius: 28px;
            padding: 24px 21px 22px;
            box-shadow:
                0 20px 50px rgba(15,23,42,.075),
                0 8px 22px rgba(15,23,42,.035),
                inset 0 1px 0 rgba(255,255,255,.88);
        }

        .mobile-brand {
            margin-bottom: 16px;
        }

        .mobile-brand img {
            width: 145px;
        }

        .login-title {
            font-size: 21px;
        }

        .login-subtitle {
            font-size: 12.4px;
        }

        .login-form {
            gap: 16px;
        }

        .forgot-link {
            font-size: 12.2px;
        }

        .system-note {
            gap: 11px;
        }
    }

    @media (max-width: 390px) {
        .login-card {
            width: 100%;
            border-radius: 25px;
            padding: 22px 17px 20px;
        }

        .mobile-brand img {
            width: 136px;
        }

        .login-title {
            font-size: 20px;
        }

        .auth-input-control {
            height: 49px;
            font-size: 12.6px;
        }

        .system-note-text {
            font-size: 11.5px;
        }
    }

    @media (max-width: 768px) {
        .feature-box,
        .login-card,
        .system-note {
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }

        .auth-fade-left,
        .auth-fade-right,
        .auth-fade-up {
            animation-duration: .28s;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .auth-fade-left,
        .auth-fade-right,
        .auth-fade-up {
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="login-page-shell">

    <div class="leaf-mobile-decor" aria-hidden="true">
        <span class="leaf-small one"></span>
        <span class="leaf-small two"></span>
        <span class="leaf-small three"></span>
    </div>

    <section class="brand-side auth-fade-left">
        <div class="brand-content">
            <img
                src="{{ asset('img/logo.png') }}"
                alt="Logo PosyanduCare"
                class="brand-logo-main"
                width="390"
                height="160"
                loading="eager"
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

    <section class="form-side auth-fade-right delay-1">
        <div class="login-card">
            <div class="login-card-inner">

                <div class="mobile-brand">
                    <img
                        src="{{ asset('img/logo.png') }}"
                        alt="Logo PosyanduCare"
                        width="156"
                        height="70"
                        loading="eager"
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
                    autocomplete="on"
                    novalidate
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
                                autocomplete="username"
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
                                id="passwordToggleBtn"
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
                            <i class="fa-solid fa-right-to-bracket" id="submitIcon"></i>
                        </button>
                    </div>
                </form>

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
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('loginFormEngine');
        const btn = document.getElementById('submitActionBtn');
        const txt = document.getElementById('submitTxt');
        const ico = document.getElementById('submitIcon');
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggleBtn');
        const visionIcon = document.getElementById('visionIcon');

        try {
            @if($errors->any())
                sessionStorage.removeItem('pc_from_login');
            @endif
        } catch (e) {}

        if (passwordToggle && passwordInput && visionIcon) {
            passwordToggle.addEventListener('click', function () {
                const visible = passwordInput.type === 'text';

                passwordInput.type = visible ? 'password' : 'text';

                visionIcon.classList.toggle('fa-eye', !visible);
                visionIcon.classList.toggle('fa-eye-slash', visible);
            });
        }

        if (!form || !btn || !txt || !ico) {
            return;
        }

        let submitted = false;

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (submitted) {
                return;
            }

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            submitted = true;

            try {
                sessionStorage.setItem('pc_from_login', '1');
            } catch (e) {}

            btn.disabled = true;
            btn.style.pointerEvents = 'none';

            txt.innerText = 'Memverifikasi akun';

            ico.classList.remove('fa-right-to-bracket');
            ico.classList.add('fa-spinner', 'fa-spin');

            if (window.PosyanduAuthTransition) {
                window.PosyanduAuthTransition.show('Membuka Portal');
            }

            window.setTimeout(function () {
                form.submit();
            }, 90);
        }, { passive: false });
    });
</script>

@if($errors->any() || session('info') || session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.Swal) {
            return;
        }

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
                backdrop: 'rgba(15, 23, 42, 0.36)'
            });
        @endif

        @if(session('info'))
            Swal.fire({
                icon: 'info',
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
                timer: 3200,
                timerProgressBar: true,
                backdrop: 'rgba(15, 23, 42, 0.28)'
            });
        @endif

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                html: @json(session('success')),
                confirmButtonText: 'Tutup',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    htmlContainer: 'custom-swal-html',
                    confirmButton: 'custom-swal-confirm-btn'
                },
                buttonsStyling: false,
                timer: 2800,
                timerProgressBar: true,
                backdrop: 'rgba(15, 23, 42, 0.28)'
            });
        @endif
    });
</script>
@endif
@endpush