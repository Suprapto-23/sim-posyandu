@extends('layouts.auth')

@section('title', 'Login | PosyanduCare')

@push('styles')
<style>
    /* ===== LAYOUT LOGIN – SUPER CEPAT (TANPA ANIMASI) ===== */
    :root {
        --g900: #064e3b;
        --g700: #047857;
        --g600: #059669;
        --g500: #10b981;
        --a500: #f59e0b;
        --s900: #0f172a;
        --s800: #1e293b;
        --s700: #334155;
        --s500: #64748b;
        --s400: #94a3b8;
        --s200: #e2e8f0;
        --s100: #f1f5f9;
        --ease: cubic-bezier(.16, 1, .3, 1);
    }

    .login-shell {
        width: 100%; max-width: 1280px;
        min-height: min(700px, calc(100svh - 36px));
        margin: 0 auto; padding: 0 32px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(420px, .88fr);
        align-items: center;
        gap: 56px;
        position: relative;
    }

    /* Brand */
    .brand-side {
        position: relative; width: 100%; min-height: 580px;
        display: flex; align-items: center; justify-content: center;
    }
    .brand-side::before {
        content: "";
        position: absolute; width: 520px; height: 520px; border-radius: 999px;
        left: 50%; top: 48%; transform: translate(-50%, -50%);
        background: radial-gradient(circle, rgba(255,255,255,.60), rgba(255,255,255,.20) 50%, transparent 72%);
        z-index: -1;
    }
    .brand-content {
        width: 100%; max-width: 540px;
        display: flex; flex-direction: column; align-items: center; text-align: center;
    }
    .brand-logo {
        width: 390px; max-width: 100%; height: auto;
        margin-bottom: 16px;
        filter: drop-shadow(0 14px 24px rgba(5,150,105,.08));
        user-select: none; pointer-events: none;
    }
    .brand-title {
        margin: 0; color: var(--s900); font-size: 25px; font-weight: 900;
        line-height: 1.28; letter-spacing: -.05em;
    }
    .brand-divider {
        display: flex; align-items: center; justify-content: center;
        gap: 13px; margin: 14px 0 17px;
    }
    .bdl { width: 55px; height: 1.6px; border-radius: 999px; }
    .bdl.l { background: linear-gradient(to right, transparent, var(--a500)); }
    .bdl.r { background: linear-gradient(to left, transparent, var(--a500)); }
    .bdd {
        width: 8px; height: 8px; border-radius: 2px;
        background: var(--a500);
        transform: rotate(45deg);
        box-shadow: 0 4px 10px rgba(245,158,11,.26);
    }
    .brand-desc {
        max-width: 420px; margin: 0 0 24px;
        color: var(--s500); font-size: 15px; line-height: 1.7; font-weight: 650;
    }
    .feature-grid {
        width: 100%; max-width: 430px;
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px;
    }
    .feature-box {
        aspect-ratio: 1/1; border-radius: 23px;
        background: linear-gradient(180deg, rgba(255,255,255,.82), rgba(255,255,255,.58));
        border: 1px solid rgba(255,255,255,.88);
        box-shadow: 0 14px 28px rgba(15,23,42,.045), inset 0 1px 0 rgba(255,255,255,.82);
        backdrop-filter: blur(12px);
        display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 9px;
        transition: transform .2s var(--ease), box-shadow .2s ease, background .2s ease;
        will-change: transform;
    }
    .feature-box:hover {
        transform: translateY(-4px);
        background: rgba(255,255,255,.92);
        box-shadow: 0 20px 36px rgba(16,185,129,.12);
    }
    .feature-icon {
        width: 40px; height: 40px; border-radius: 15px;
        background: rgba(5,150,105,.10); color: var(--g600);
        display: flex; align-items: center; justify-content: center; font-size: 17px;
    }
    .feature-text {
        color: var(--s800); font-size: 11.5px; font-weight: 900;
    }

    /* Form Side */
    .form-side {
        width: 100%; display: flex; align-items: center; justify-content: center;
    }
    .login-card {
        position: relative; width: 100%; max-width: 500px; min-height: 540px;
        border-radius: 36px; padding: 42px 46px 38px; overflow: hidden;
        background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.90));
        border: 1px solid rgba(226,232,240,.86);
        box-shadow: 0 26px 70px rgba(15,23,42,.08), 0 10px 26px rgba(15,23,42,.04), inset 0 1px 0 rgba(255,255,255,.92);
        backdrop-filter: blur(18px);
    }
    .login-card::before {
        content: "";
        position: absolute; inset: 0; pointer-events: none;
        background: radial-gradient(circle at 15% 0%, rgba(16,185,129,.08), transparent 34%),
                    radial-gradient(circle at 94% 100%, rgba(245,158,11,.055), transparent 32%);
    }
    .card-inner { position: relative; z-index: 2; }
    .mobile-brand { display: none; justify-content: center; margin-bottom: 18px; }
    .mobile-brand img { width: 156px; height: auto; filter: drop-shadow(0 8px 14px rgba(5,150,105,.07)); }

    .login-header { text-align: center; margin-bottom: 28px; }
    .login-title { margin: 0 0 8px; color: var(--g900); font-size: 29px; font-weight: 900; line-height: 1.14; letter-spacing: -.052em; }
    .login-subtitle { margin: 0; color: var(--s500); font-size: 14.5px; line-height: 1.55; font-weight: 650; }

    /* Form Fields */
    .login-form { display: flex; flex-direction: column; gap: 18px; }
    .field-group { width: 100%; }
    .field-label { display: block; margin: 0 0 8px 2px; color: var(--s800); font-size: 12px; font-weight: 900; letter-spacing: .055em; }
    .field-wrap { position: relative; width: 100%; }
    .field-input {
        width: 100%; height: 54px; padding: 0 52px 0 50px;
        border: 1px solid #dbe5ee; border-radius: 16px;
        background: rgba(255,255,255,.88); color: var(--s900);
        font-size: 14.5px; font-weight: 650; outline: none;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .field-input::placeholder { color: var(--s400); font-weight: 600; }
    .field-input:hover { background: #fff; border-color: var(--s200); }
    .field-input:focus {
        background: #fff; border-color: var(--g600);
        box-shadow: 0 0 0 4px rgba(5,150,105,.095), 0 10px 22px rgba(5,150,105,.055);
    }
    .field-icon {
        position: absolute; left: 17px; top: 50%; transform: translateY(-50%);
        color: var(--s400); font-size: 17px; pointer-events: none;
        transition: color .15s ease;
    }
    .field-input:focus ~ .field-icon { color: #0f766e; }
    .pw-toggle {
        position: absolute; right: 9px; top: 50%; transform: translateY(-50%);
        width: 39px; height: 39px; border: 0; border-radius: 13px;
        background: transparent; color: var(--s400); cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: color .15s ease, background .15s ease;
    }
    .pw-toggle:hover { color: var(--g600); background: var(--s100); }

    /* Hapus Lupa Password */
    .forgot-row { display: none; }

    .submit-wrap { padding-top: 10px; }
    .submit-btn {
        width: 100%; height: 59px; border: 0; border-radius: 17px; color: #fff; cursor: pointer;
        background: linear-gradient(135deg, var(--g700) 0%, var(--g600) 48%, var(--g500) 100%);
        box-shadow: 0 16px 32px rgba(5,150,105,.27), inset 0 1px 0 rgba(255,255,255,.22);
        display: flex; align-items: center; justify-content: center; gap: 11px;
        font-size: 15.5px; font-weight: 900;
        transition: transform .2s var(--ease), box-shadow .2s ease, filter .2s ease;
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        filter: saturate(1.05);
        box-shadow: 0 21px 38px rgba(5,150,105,.32), inset 0 1px 0 rgba(255,255,255,.26);
    }
    .submit-btn:disabled {
        opacity: .88; cursor: wait; pointer-events: none; transform: none;
    }

    .info-note {
        margin-top: 24px; padding: 16px 17px; border-radius: 20px;
        background: linear-gradient(135deg, rgba(236,253,245,.84), rgba(255,255,255,.72));
        border: 1px solid rgba(16,185,129,.15);
        box-shadow: 0 12px 24px rgba(15,23,42,.04), inset 0 1px 0 rgba(255,255,255,.78);
        display: flex; align-items: flex-start; gap: 12px;
    }
    .info-icon {
        flex: 0 0 auto; width: 37px; height: 37px; border-radius: 14px;
        background: rgba(5,150,105,.10); color: var(--g600);
        display: flex; align-items: center; justify-content: center; font-size: 15px;
    }
    .info-title { margin: 0 0 4px; color: var(--g900); font-size: 13.5px; font-weight: 900; line-height: 1.35; }
    .info-text { margin: 0; color: var(--s500); font-size: 12.4px; line-height: 1.58; font-weight: 650; }

    /* ===== SEMUA ANIMASI DIHAPUS ===== */
    /* Tidak ada animasi sama sekali, langsung tampil */

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1180px) {
        .login-shell { grid-template-columns: minmax(0, 1fr) minmax(410px, .92fr); gap: 44px; padding: 0 24px; }
        .brand-logo { width: 355px; }
        .brand-title { font-size: 23px; }
        .login-card { max-width: 480px; padding: 39px 42px 36px; }
    }
    @media (max-width: 1024px) {
        .login-shell {
            min-height: calc(100svh - 24px);
            display: flex; align-items: center; justify-content: center;
            padding: 14px 0;
        }
        .brand-side { display: none; }
        .form-side { position: relative; }
        .form-side::before {
            content: "";
            position: absolute; width: 280px; height: 280px;
            left: 50%; top: 50%; transform: translate(-50%, -50%);
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255,255,255,.62), rgba(255,255,255,.16) 54%, transparent 72%);
            z-index: 0;
        }
        .login-card {
            max-width: 440px; min-height: auto;
            padding: 27px 25px 25px; border-radius: 31px;
            z-index: 2; backdrop-filter: blur(10px);
        }
        .mobile-brand { display: flex; }
        .login-header { margin-bottom: 22px; }
        .login-title { font-size: 23px; }
        .login-subtitle { font-size: 13px; }
        .field-label { font-size: 11px; margin-bottom: 6px; }
        .field-input { height: 51px; border-radius: 15px; font-size: 13px; padding-left: 45px; }
        .field-icon { left: 16px; font-size: 15px; }
        .submit-btn { height: 53px; border-radius: 15px; font-size: 14px; }
        .info-note { margin-top: 18px; padding: 14px; border-radius: 18px; }
    }
    @media (max-width: 640px) {
        .login-shell {
            min-height: calc(100svh - 18px);
            padding: 8px 0;
        }
        .login-card {
            max-width: 100%; width: min(100%, 388px);
            border-radius: 28px; padding: 24px 21px 22px;
        }
        .mobile-brand img { width: 145px; }
        .login-title { font-size: 21px; }
        .login-subtitle { font-size: 12.4px; }
        .login-form { gap: 16px; }
    }
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 1ms !important;
            transition-duration: 1ms !important;
        }
    }
</style>
@endpush

@section('content')
<div class="login-shell">

    <!-- Brand (desktop) -->
    <section class="brand-side" aria-hidden="true">
        <div class="brand-content">
            <img src="{{ asset('img/logo.webp') }}" alt="Logo PosyanduCare" class="brand-logo" width="390" height="160" loading="eager" fetchpriority="high" decoding="async">
            <h2 class="brand-title">Sehat Bersama, Tumbuh Setiap Generasi</h2>
            <div class="brand-divider">
                <span class="bdl l"></span><span class="bdd"></span><span class="bdl r"></span>
            </div>
            <p class="brand-desc">Platform layanan kesehatan terpadu untuk masyarakat modern.</p>
            <div class="feature-grid">
                @foreach([['fa-user-group','Terintegrasi'],['fa-shield-halved','Aman'],['fa-chart-simple','Efisien'],['fa-heart-pulse','Peduli']] as $i => $f)
                <div class="feature-box">
                    <div class="feature-icon"><i class="fa-solid {{ $f[0] }}"></i></div>
                    <span class="feature-text">{{ $f[1] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Login card -->
    <section class="form-side">
        <div class="login-card">
            <div class="card-inner">

                <div class="mobile-brand">
                    <img src="{{ asset('img/logo.webp') }}" alt="Logo" width="156" height="70" loading="eager" fetchpriority="high">
                </div>

                <div class="login-header">
                    <h1 class="login-title">Selamat Datang Kembali!</h1>
                    <p class="login-subtitle">Masuk untuk melanjutkan ke Portal PosyanduCare</p>
                </div>

                <form id="loginForm" method="POST" action="{{ route('login.post') }}" class="login-form" autocomplete="on" novalidate>
                    @csrf

                    <div class="field-group">
                        <label for="login_field" class="field-label">Email atau NIK</label>
                        <div class="field-wrap">
                            <input type="text" id="login_field" name="login" value="{{ old('login') }}" class="field-input" placeholder="Masukkan email atau NIK" required autofocus autocomplete="username">
                            <i class="fa-regular fa-user field-icon"></i>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="password" class="field-label">Password</label>
                        <div class="field-wrap">
                            <input type="password" id="password" name="password" class="field-input" placeholder="Masukkan password" required autocomplete="current-password">
                            <i class="fa-solid fa-lock field-icon"></i>
                            <button type="button" id="pwToggle" class="pw-toggle" aria-label="Lihat/sembunyikan password">
                                <i class="fa-regular fa-eye-slash" id="pwIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Lupa password dihapus -->
                    <!-- <div class="forgot-row">...</div> -->

                    <div class="submit-wrap">
                        <button type="submit" id="submitBtn" class="submit-btn">
                            <span id="submitTxt">Masuk</span>
                            <i class="fa-solid fa-right-to-bracket" id="submitIco"></i>
                        </button>
                    </div>
                </form>

                <div class="info-note">
                    <div class="info-icon"><i class="fa-solid fa-shield-heart"></i></div>
                    <div>
                        <p class="info-title">Akses khusus pengguna terdaftar</p>
                        <p class="info-text">Gunakan akun yang telah dibuat oleh petugas Posyandu untuk mengakses layanan kesehatan digital.</p>
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
    // --- Password toggle ---
    const pwInput = document.getElementById('password');
    const pwToggle = document.getElementById('pwToggle');
    const pwIcon = document.getElementById('pwIcon');
    if (pwToggle) {
        pwToggle.addEventListener('click', function () {
            const show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            pwIcon.classList.toggle('fa-eye', show);
            pwIcon.classList.toggle('fa-eye-slash', !show);
        });
    }

    // --- Form submit – LANGSUNG, tanpa delay ---
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitTxt = document.getElementById('submitTxt');
    const submitIco = document.getElementById('submitIco');
    let submitted = false;

    form?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (submitted) return;
        if (!form.checkValidity()) { form.reportValidity(); return; }
        submitted = true;

        // Loading state tombol
        submitBtn.disabled = true;
        submitTxt.textContent = 'Memverifikasi…';
        submitIco.className = 'fa-solid fa-spinner fa-spin';

        // Submit langsung (tanpa setTimeout)
        form.submit();
    });

    // --- Tampilkan error dari server ---
    @if($errors->any())
        window.showAuthAlert('Otentikasi Gagal', @json($errors->first()), 'error');
    @endif

    @if(session('info'))
        window.showAuthAlert('Informasi', @json(session('info')), 'info');
    @endif

    @if(session('success'))
        window.showAuthAlert('Berhasil', @json(session('success')), 'success');
    @endif
});
</script>
@endpush