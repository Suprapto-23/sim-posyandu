@extends('layouts.auth')

@section('title', 'Selamat Datang | PosyanduCare')

@push('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | POSYANDUCARE LOGIN - PRECISION FINAL
    |--------------------------------------------------------------------------
    */

    .pc-login-shell {
        position: relative;
        width: 100%;
        max-width: 1420px;
        min-height: calc(100svh - 38px);
        margin: 0 auto;
        padding: 0 56px;

        display: grid;
        grid-template-columns: minmax(560px, 1fr) minmax(500px, .9fr);
        align-items: center;
        gap: 76px;
    }

    /*
    |--------------------------------------------------------------------------
    | LEFT BRAND AREA
    |--------------------------------------------------------------------------
    */

    .pc-login-brand {
        position: relative;
        width: 100%;
        height: 650px;
        min-height: 650px;

        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pc-login-brand::before {
        content: "";
        position: absolute;
        width: 620px;
        height: 620px;
        left: 48%;
        top: 47%;
        transform: translate(-50%, -50%);

        border-radius: 999px;

        background:
            radial-gradient(circle, rgba(255,255,255,.76), rgba(255,255,255,.35) 47%, transparent 72%);

        z-index: -1;
        opacity: .95;
    }

    .pc-login-brand::after {
        content: "";
        position: absolute;
        width: 440px;
        height: 440px;
        left: -120px;
        bottom: -96px;

        border-radius: 46% 54% 50% 50%;

        background:
            radial-gradient(circle at 34% 32%, rgba(16,185,129,.16), transparent 54%),
            linear-gradient(135deg, rgba(16,185,129,.12), rgba(255,255,255,0));

        z-index: -2;
        opacity: .7;
    }

    .pc-brand-inner {
        width: 100%;
        max-width: 650px;

        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;

        transform: translate(-6px, -4px);
    }

    .pc-brand-logo {
        width: 430px;
        max-width: 100%;
        height: auto;

        display: block;
        object-fit: contain;

        margin-bottom: 66px;

        filter:
            drop-shadow(0 22px 34px rgba(5,150,105,.075))
            drop-shadow(0 8px 12px rgba(15,23,42,.035));

        user-select: none;
        pointer-events: none;
    }

    .pc-brand-title {
        max-width: 640px;
        margin: 0;

        color: #0f172a;

        font-family: 'Poppins', sans-serif;
        font-size: 28px;
        line-height: 1.18;
        font-weight: 900;
        letter-spacing: -0.07em;
    }

    .pc-brand-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 13px;

        margin: 16px 0 18px;
    }

    .pc-brand-divider-line {
        width: 58px;
        height: 1.7px;
        border-radius: 999px;
    }

    .pc-brand-divider-line.left {
        background: linear-gradient(to right, transparent, #f59e0b);
    }

    .pc-brand-divider-line.right {
        background: linear-gradient(to left, transparent, #f59e0b);
    }

    .pc-brand-divider-dot {
        width: 8px;
        height: 8px;

        border-radius: 2px;
        background: #f59e0b;

        transform: rotate(45deg);

        box-shadow: 0 5px 12px rgba(245,158,11,.28);
    }

    .pc-brand-description {
        max-width: 510px;
        margin: 0 0 34px;

        color: #64748b;

        font-size: 15.4px;
        line-height: 1.65;
        font-weight: 750;
    }

    .pc-feature-grid {
        width: 100%;
        max-width: 500px;

        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .pc-feature-card {
        height: 104px;

        border-radius: 24px;

        background:
            linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,255,255,.64));

        border: 1px solid rgba(255,255,255,.94);

        box-shadow:
            0 16px 32px rgba(15,23,42,.052),
            inset 0 1px 0 rgba(255,255,255,.88);

        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);

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

    .pc-feature-card:hover {
        transform: translateY(-6px);

        background: rgba(255,255,255,.95);

        box-shadow:
            0 24px 44px rgba(16,185,129,.15),
            inset 0 1px 0 rgba(255,255,255,.92);
    }

    .pc-feature-icon {
        width: 44px;
        height: 44px;

        border-radius: 16px;

        background: rgba(5,150,105,.10);
        color: #059669;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 17px;
    }

    .pc-feature-text {
        color: #1e293b;

        font-size: 12px;
        line-height: 1;
        font-weight: 900;
    }

    /*
    |--------------------------------------------------------------------------
    | RIGHT FORM AREA
    |--------------------------------------------------------------------------
    */

    .pc-login-form-side {
        position: relative;
        width: 100%;
        height: 650px;
        min-height: 650px;

        display: flex;
        align-items: center;
        justify-content: center;

        transform: translateX(4px);
    }

    .pc-login-card {
        position: relative;
        width: 100%;
        max-width: 545px;
        min-height: 610px;

        padding: 46px 54px 38px;

        overflow: hidden;

        border-radius: 42px;

        background:
            linear-gradient(180deg, rgba(255,255,255,.965), rgba(255,255,255,.91));

        border: 1px solid rgba(226,232,240,.86);

        box-shadow:
            0 34px 90px rgba(15,23,42,.086),
            0 14px 34px rgba(15,23,42,.042),
            inset 0 1px 0 rgba(255,255,255,.94);

        backdrop-filter: blur(26px);
        -webkit-backdrop-filter: blur(26px);
    }

    .pc-login-card::before {
        content: "";
        position: absolute;
        inset: 0;

        background:
            radial-gradient(circle at 14% 0%, rgba(16,185,129,.082), transparent 34%),
            radial-gradient(circle at 96% 100%, rgba(245,158,11,.052), transparent 32%);

        pointer-events: none;
    }

    .pc-login-card::after {
        content: "";
        position: absolute;
        width: 285px;
        height: 285px;
        right: -118px;
        top: -134px;

        border-radius: 999px;
        background: rgba(16,185,129,.06);

        pointer-events: none;
    }

    .pc-login-card-inner {
        position: relative;
        z-index: 2;
    }

    .pc-mobile-logo {
        display: none;
    }

    .pc-login-header {
        text-align: center;
        margin-bottom: 36px;
    }

    .pc-login-title {
        margin: 0 0 12px;

        color: #064e3b;

        font-family: 'Poppins', sans-serif;
        font-size: 31px;
        line-height: 1.12;
        font-weight: 900;
        letter-spacing: -0.065em;
    }

    .pc-login-subtitle {
        margin: 0;

        color: #64748b;

        font-size: 14.8px;
        line-height: 1.55;
        font-weight: 700;
    }

    .pc-login-form {
        display: flex;
        flex-direction: column;
        gap: 21px;
    }

    .pc-form-group {
        width: 100%;
    }

    .pc-label {
        display: block;

        margin: 0 0 9px 2px;

        color: #1e293b;

        font-size: 12.2px;
        font-weight: 900;
        letter-spacing: .055em;
    }

    .pc-input-wrap {
        position: relative;
        width: 100%;
    }

    .pc-input {
        width: 100%;
        height: 59px;

        border: 1px solid #dbe5ee;
        border-radius: 18px;

        padding: 0 54px 0 54px;

        color: #0f172a;
        background: rgba(255,255,255,.88);

        outline: none;

        font-size: 14.8px;
        font-weight: 700;

        transition:
            border-color .24s ease,
            box-shadow .24s ease,
            background .24s ease,
            transform .24s ease;
    }

    .pc-input::placeholder {
        color: #94a3b8;
        font-weight: 650;
    }

    .pc-input:hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }

    .pc-input:focus {
        background: #ffffff;
        border-color: #059669;

        box-shadow:
            0 0 0 5px rgba(5,150,105,.10),
            0 14px 28px rgba(5,150,105,.065);
    }

    .pc-input-icon {
        position: absolute;
        left: 19px;
        top: 50%;
        transform: translateY(-50%);

        color: #0f766e;

        font-size: 17px;

        pointer-events: none;

        transition: color .24s ease;
    }

    .pc-input:not(:focus) ~ .pc-input-icon {
        color: #94a3b8;
    }

    .pc-password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);

        width: 42px;
        height: 42px;

        border: 0;
        border-radius: 14px;

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

    .pc-password-toggle:hover {
        color: #059669;
        background: #f8fafc;
    }

    .pc-forgot-row {
        display: flex;
        justify-content: flex-end;

        margin-top: -5px;
    }

    .pc-forgot-link {
        color: #059669;

        font-size: 13.2px;
        font-weight: 900;
        text-decoration: none;
    }

    .pc-forgot-link:hover {
        color: #047857;
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    .pc-submit-area {
        padding-top: 16px;
    }

    .pc-submit-btn {
        width: 100%;
        height: 62px;

        border: 0;
        border-radius: 18px;

        color: white;
        cursor: pointer;

        background:
            linear-gradient(135deg, #047857 0%, #059669 48%, #10b981 100%);

        box-shadow:
            0 18px 38px rgba(5,150,105,.30),
            inset 0 1px 0 rgba(255,255,255,.24);

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

    .pc-submit-btn:hover {
        transform: translateY(-2px);
        filter: saturate(1.06);

        box-shadow:
            0 25px 46px rgba(5,150,105,.36),
            inset 0 1px 0 rgba(255,255,255,.28);
    }

    .pc-submit-btn:disabled {
        cursor: wait;
        opacity: .9;
        transform: none;
    }

    .pc-submit-btn i {
        font-size: 14px;
        transition: transform .24s ease;
    }

    .pc-submit-btn:hover i.fa-arrow-right {
        transform: translateX(5px);
    }

    /*
    |--------------------------------------------------------------------------
    | NOTE BOX
    |--------------------------------------------------------------------------
    */

    .pc-system-note {
        margin-top: 32px;
        padding: 17px;

        border-radius: 21px;

        background:
            linear-gradient(135deg, rgba(236,253,245,.88), rgba(255,255,255,.74));

        border: 1px solid rgba(16,185,129,.17);

        box-shadow:
            0 14px 30px rgba(15,23,42,.045),
            inset 0 1px 0 rgba(255,255,255,.84);

        display: flex;
        align-items: flex-start;
        gap: 13px;
    }

    .pc-note-icon {
        flex: 0 0 auto;

        width: 39px;
        height: 39px;

        border-radius: 15px;

        background: rgba(5,150,105,.10);
        color: #059669;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 16px;
    }

    .pc-note-content {
        min-width: 0;
    }

    .pc-note-title {
        margin: 0 0 4px;

        color: #064e3b;

        font-size: 13.8px;
        line-height: 1.35;
        font-weight: 900;
    }

    .pc-note-text {
        margin: 0;

        color: #64748b;

        font-size: 12.8px;
        line-height: 1.62;
        font-weight: 700;
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE DECOR
    |--------------------------------------------------------------------------
    */

    .pc-mobile-leaf {
        position: absolute;
        display: none;
        pointer-events: none;
        z-index: 1;
    }

    .pc-mobile-leaf span {
        position: absolute;

        border-radius: 100% 0 100% 0;

        background:
            linear-gradient(135deg, rgba(4,120,87,.36), rgba(16,185,129,.06));

        box-shadow: inset 8px 8px 14px rgba(255,255,255,.16);
    }

    /*
    |--------------------------------------------------------------------------
    | ANIMATION
    |--------------------------------------------------------------------------
    */

    .pc-enter-left {
        opacity: 0;
        transform: translateX(-30px);
        filter: blur(5px);
        animation: pcEnterLeft .86s cubic-bezier(.16,1,.3,1) forwards;
    }

    .pc-enter-right {
        opacity: 0;
        transform: translateX(30px);
        filter: blur(5px);
        animation: pcEnterRight .86s cubic-bezier(.16,1,.3,1) forwards;
    }

    .pc-enter-up {
        opacity: 0;
        transform: translateY(20px);
        filter: blur(4px);
        animation: pcEnterUp .76s cubic-bezier(.16,1,.3,1) forwards;
    }

    .pc-delay-1 { animation-delay: .08s; }
    .pc-delay-2 { animation-delay: .15s; }
    .pc-delay-3 { animation-delay: .22s; }
    .pc-delay-4 { animation-delay: .29s; }
    .pc-delay-5 { animation-delay: .36s; }

    @keyframes pcEnterLeft {
        to {
            opacity: 1;
            transform: translateX(0);
            filter: blur(0);
        }
    }

    @keyframes pcEnterRight {
        to {
            opacity: 1;
            transform: translateX(0);
            filter: blur(0);
        }
    }

    @keyframes pcEnterUp {
        to {
            opacity: 1;
            transform: translateY(0);
            filter: blur(0);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1280px) {
        .pc-login-shell {
            max-width: 1220px;
            grid-template-columns: minmax(0, 1fr) minmax(450px, .92fr);
            gap: 58px;
            padding: 0 34px;
        }

        .pc-login-brand,
        .pc-login-form-side {
            height: 620px;
            min-height: 620px;
        }

        .pc-brand-logo {
            width: 405px;
            margin-bottom: 56px;
        }

        .pc-brand-title {
            font-size: 24px;
        }

        .pc-brand-description {
            font-size: 15px;
            margin-bottom: 28px;
        }

        .pc-feature-grid {
            max-width: 440px;
            gap: 14px;
        }

        .pc-feature-card {
            height: 100px;
        }

        .pc-login-card {
            max-width: 505px;
            min-height: 580px;
            padding: 40px 46px 34px;
        }

        .pc-login-title {
            font-size: 28px;
        }

        .pc-login-header {
            margin-bottom: 30px;
        }

        .pc-login-form {
            gap: 18px;
        }

        .pc-system-note {
            margin-top: 26px;
        }
    }

    @media (max-width: 1024px) {
        .pc-login-shell {
            min-height: calc(100svh - 28px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 0;
        }

        .pc-login-brand {
            display: none;
        }

        .pc-login-form-side {
            width: 100%;
            height: auto;
            min-height: auto;
            position: relative;
            transform: none;
        }

        .pc-login-form-side::before {
            content: "";
            position: absolute;
            width: 330px;
            height: 330px;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);

            border-radius: 999px;

            background:
                radial-gradient(circle, rgba(255,255,255,.72), rgba(255,255,255,.22) 54%, transparent 72%);

            z-index: 0;
        }

        .pc-login-card {
            max-width: 460px;
            min-height: auto;
            padding: 28px 26px 26px;

            border-radius: 32px;

            z-index: 2;
            transform: none;
        }

        .pc-mobile-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .pc-mobile-logo img {
            width: 164px;
            height: auto;

            display: block;
            object-fit: contain;

            filter: drop-shadow(0 10px 18px rgba(5,150,105,.08));
        }

        .pc-login-header {
            margin-bottom: 28px;
        }

        .pc-login-title {
            font-size: 24px;
        }

        .pc-login-subtitle {
            font-size: 13.5px;
        }

        .pc-label {
            font-size: 11.2px;
            margin-bottom: 8px;
        }

        .pc-input {
            height: 52px;
            border-radius: 15px;
            font-size: 13.3px;
            padding-left: 46px;
            padding-right: 48px;
        }

        .pc-input-icon {
            left: 17px;
            font-size: 15px;
        }

        .pc-submit-btn {
            height: 54px;
            border-radius: 15px;
            font-size: 14.2px;
        }

        .pc-system-note {
            margin-top: 24px;
            padding: 15px;
            border-radius: 18px;
        }

        .pc-note-icon {
            width: 35px;
            height: 35px;
            border-radius: 13px;
            font-size: 15px;
        }

        .pc-note-title {
            font-size: 13px;
        }

        .pc-note-text {
            font-size: 12.2px;
        }

        .pc-mobile-leaf {
            display: block;
            width: 250px;
            height: 250px;
            left: -78px;
            bottom: -72px;
            opacity: .18;
            transform: rotate(-10deg);
        }

        .pc-mobile-leaf .one {
            width: 120px;
            height: 70px;
            left: 20px;
            bottom: 60px;
            transform: rotate(-24deg);
        }

        .pc-mobile-leaf .two {
            width: 135px;
            height: 76px;
            left: 78px;
            bottom: 110px;
            transform: rotate(-4deg);
        }

        .pc-mobile-leaf .three {
            width: 98px;
            height: 58px;
            left: 130px;
            bottom: 45px;
            transform: rotate(22deg);
        }
    }

    @media (max-width: 640px) {
        .pc-login-shell {
            min-height: calc(100svh - 20px);
            padding: 12px 0;
        }

        .pc-login-card {
            max-width: 100%;
            width: min(100%, 390px);
            border-radius: 29px;
            padding: 25px 22px 23px;
        }

        .pc-mobile-logo img {
            width: 150px;
        }

        .pc-login-title {
            font-size: 22px;
        }

        .pc-login-subtitle {
            font-size: 12.6px;
        }

        .pc-login-form {
            gap: 17px;
        }

        .pc-forgot-link {
            font-size: 12.4px;
        }

        .pc-system-note {
            gap: 11px;
        }
    }

    @media (max-width: 390px) {
        .pc-login-card {
            width: 100%;
            border-radius: 25px;
            padding: 23px 18px 21px;
        }

        .pc-mobile-logo img {
            width: 140px;
        }

        .pc-login-title {
            font-size: 20px;
        }

        .pc-input {
            height: 50px;
            font-size: 12.8px;
        }

        .pc-note-text {
            font-size: 11.8px;
        }
    }
</style>
@endpush

@section('content')
<div class="pc-login-shell">

    <div class="pc-mobile-leaf" aria-hidden="true">
        <span class="one"></span>
        <span class="two"></span>
        <span class="three"></span>
    </div>

    {{-- LEFT BRANDING --}}
    <section class="pc-login-brand pc-enter-left">
        <div class="pc-brand-inner">

            <img
                src="{{ asset('img/logo.png') }}"
                alt="Logo PosyanduCare"
                class="pc-brand-logo"
            >

            <h2 class="pc-brand-title">
                Sehat Bersama, Tumbuh Setiap Generasi
            </h2>

            <div class="pc-brand-divider">
                <span class="pc-brand-divider-line left"></span>
                <span class="pc-brand-divider-dot"></span>
                <span class="pc-brand-divider-line right"></span>
            </div>

            <p class="pc-brand-description">
                Platform layanan kesehatan terpadu untuk masyarakat modern.
            </p>

            <div class="pc-feature-grid">
                @php
                    $features = [
                        ['icon' => 'fa-user-group', 'text' => 'Terintegrasi'],
                        ['icon' => 'fa-shield-halved', 'text' => 'Aman'],
                        ['icon' => 'fa-chart-simple', 'text' => 'Efisien'],
                        ['icon' => 'fa-heart-pulse', 'text' => 'Peduli'],
                    ];
                @endphp

                @foreach($features as $index => $feature)
                    <div class="pc-feature-card pc-enter-up pc-delay-{{ $index + 1 }}">
                        <div class="pc-feature-icon">
                            <i class="fa-solid {{ $feature['icon'] }}"></i>
                        </div>

                        <span class="pc-feature-text">
                            {{ $feature['text'] }}
                        </span>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    {{-- RIGHT FORM --}}
    <section class="pc-login-form-side pc-enter-right pc-delay-1">
        <div class="pc-login-card">
            <div class="pc-login-card-inner">

                <div class="pc-mobile-logo">
                    <img
                        src="{{ asset('img/logo.png') }}"
                        alt="Logo PosyanduCare"
                    >
                </div>

                <div class="pc-login-header">
                    <h1 class="pc-login-title">
                        Selamat Datang Kembali!
                    </h1>

                    <p class="pc-login-subtitle">
                        Masuk untuk melanjutkan ke Portal PosyanduCare
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('login.post') }}"
                    id="loginFormEngine"
                    class="pc-login-form"
                >
                    @csrf

                    <div class="pc-form-group">
                        <label for="login" class="pc-label">
                            Email atau Username
                        </label>

                        <div class="pc-input-wrap">
                            <input
                                type="text"
                                id="login"
                                name="login"
                                value="{{ old('login') }}"
                                class="pc-input"
                                placeholder="Masukkan email atau username"
                                required
                                autofocus
                                autocomplete="off"
                            >

                            <i class="fa-regular fa-user pc-input-icon"></i>
                        </div>
                    </div>

                    <div class="pc-form-group">
                        <label for="password" class="pc-label">
                            Password
                        </label>

                        <div class="pc-input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="pc-input"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password"
                            >

                            <i class="fa-solid fa-lock pc-input-icon"></i>

                            <button
                                type="button"
                                onclick="toggleVision()"
                                class="pc-password-toggle"
                                aria-label="Tampilkan atau sembunyikan password"
                            >
                                <i class="fa-regular fa-eye-slash" id="visionIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pc-forgot-row">
                        <a href="#" class="pc-forgot-link">
                            Lupa password?
                        </a>
                    </div>

                    <div class="pc-submit-area">
                        <button
                            type="submit"
                            id="submitActionBtn"
                            class="pc-submit-btn"
                        >
                            <span id="submitTxt">Masuk</span>
                            <i class="fa-solid fa-arrow-right" id="submitIcon"></i>
                        </button>
                    </div>
                </form>

                <div class="pc-system-note pc-enter-up pc-delay-3">
                    <div class="pc-note-icon">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>

                    <div class="pc-note-content">
                        <p class="pc-note-title">
                            Akses khusus pengguna terdaftar
                        </p>

                        <p class="pc-note-text">
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

        form.dataset.transitionBound = 'true';

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            btn.disabled = true;
            btn.style.pointerEvents = 'none';
            btn.classList.add('opacity-90', 'cursor-wait');

            txt.innerText = 'Membuka portal...';

            ico.classList.remove('fa-arrow-right');
            ico.classList.remove('fa-spinner');
            ico.classList.add('fa-circle-notch', 'fa-spin');

            setTimeout(function () {
                if (typeof window.runLoginTransition === 'function') {
                    window.runLoginTransition(form);
                    return;
                }

                form.submit();
            }, 180);
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