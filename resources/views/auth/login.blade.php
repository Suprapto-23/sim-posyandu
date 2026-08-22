@extends('layouts.auth')

@section('title', 'Login | PosyanduCare')

@push('styles')
<style>
    .login-shell {
        width: 100%; 
        max-width: 1140px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 60px;
        overflow: hidden;
        padding: 24px;
    }

    /* AREA DESKTOP */
    .desktop-brand-area { display: flex; flex-direction: column; align-items: center; text-align: center; }
    .partner-logos { display: inline-flex; align-items: center; justify-content: center; gap: 24px; margin-bottom: 32px; background: rgba(255, 255, 255, 0.85); padding: 12px 32px; border-radius: 100px; box-shadow: 0 10px 25px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; backdrop-filter: blur(10px); }
    .partner-logo { height: 48px; width: auto; object-fit: contain; pointer-events: none; }
    .partner-divider { width: 2px; height: 32px; background: #e2e8f0; border-radius: 2px; }
    .brand-logo { width: 320px; height: auto; margin-bottom: 24px; pointer-events: none; }
    .brand-divider { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 16px; }
    .bdl { width: 40px; height: 2px; background: #e2e8f0; border-radius: 4px; }
    .bdd { width: 6px; height: 6px; background: var(--amber-500); transform: rotate(45deg); }
    .brand-desc { color: var(--slate-700); font-size: 15px; font-weight: 500; max-width: 380px; margin: 0 0 32px; line-height: 1.6; }

    .feature-grid { display: flex; gap: 16px; justify-content: center; width: 100%; max-width: 440px; }
    .feature-box { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; padding: 16px 12px; width: 90px; display: flex; flex-direction: column; align-items: center; gap: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); transition: transform 0.2s; }
    .feature-box:hover { transform: translateY(-3px); }
    .feature-icon { width: 36px; height: 36px; border-radius: 10px; background: #ecfdf5; color: var(--green-700); display: flex; align-items: center; justify-content: center; }
    .feature-icon .icon { width: 18px; height: 18px; }
    .feature-text { color: var(--slate-700); font-size: 11px; font-weight: 700; }

    .mobile-brand-area { display: none; }

    /* AREA FORM */
    .form-side { display: flex; justify-content: flex-end; }
    .login-card { width: 100%; max-width: 440px; background: #ffffff; border-radius: 28px; padding: 40px; box-shadow: 0 24px 50px rgba(0,0,0,0.06), 0 0 0 8px rgba(255,255,255,0.4); }
    .login-header { text-align: center; margin-bottom: 32px; }
    .login-title { color: var(--green-900); font-size: 26px; font-weight: 800; margin: 0 0 8px; letter-spacing: -0.03em; }
    .login-subtitle { color: var(--slate-700); font-size: 14px; margin: 0; font-weight: 500; }

    .login-form { display: flex; flex-direction: column; gap: 20px; }
    .field-group { width: 100%; }
    .field-label { display: block; font-size: 13px; font-weight: 700; color: var(--slate-900); margin-bottom: 8px; }
    .field-wrap { position: relative; }

    .field-input { width: 100%; height: 52px; padding: 0 44px; border: 1.5px solid #e2e8f0; border-radius: 14px; font-size: 14px; color: var(--slate-900); font-weight: 500; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
    .field-input:focus { border-color: var(--green-500); outline: none; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
    .field-input::placeholder { color: var(--slate-500); opacity: 1; font-weight: 500; }

    .field-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--slate-500); transition: color 0.2s; pointer-events: none; }
    .field-input:focus ~ .field-icon { color: var(--green-600); }

    .pw-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--slate-500); cursor: pointer; padding: 4px; border-radius: 8px; transition: background 0.2s; display: flex; }
    .pw-toggle:hover { background: #f1f5f9; color: var(--slate-900); }
    .pw-toggle .icon { width: 17px; height: 17px; }

    .submit-btn { width: 100%; height: 54px; background: var(--green-600); color: #fff; border: none; border-radius: 14px; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; transition: background 0.2s, box-shadow 0.2s, transform 0.2s; margin-top: 4px; }
    .submit-btn:hover:not(:disabled) { background: var(--green-700); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2); transform: translateY(-1px); }
    .submit-btn:disabled { opacity: 0.9; cursor: default; }
    .submit-btn .icon { width: 18px; height: 18px; }

    /* SPINNER SUPER KILAT (0.1s) */
    .btn-spinner {
        width: 18px; height: 18px; border-radius: 50%; display: inline-block;
        border: 2.5px solid rgba(255,255,255,0.35); border-top-color: #fff;
        animation: fluentSpin 0.1s linear infinite; 
    }
    @keyframes fluentSpin { 100% { transform: rotate(360deg); } }

    .info-note { margin-top: 24px; padding: 16px; background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 16px; display: flex; align-items: flex-start; gap: 12px; }
    .info-icon { color: var(--green-700); width: 20px; height: 20px; flex-shrink: 0; }
    .info-title { color: var(--green-900); font-size: 13px; font-weight: 700; margin: 0 0 4px; }
    .info-text { color: var(--slate-700); font-size: 12px; margin: 0; line-height: 1.5; font-weight: 500; }

    /* MOBILE */
    @media (max-width: 992px) {
        .login-shell { grid-template-columns: 1fr; justify-items: center; gap: 20px; padding: 16px; }
        .desktop-brand-area { display: none; }
        .mobile-brand-area { display: flex; align-items: center; justify-content: center; gap: 20px; width: 100%; margin-bottom: 8px; }
        .icon-crop-dikti { width: 44px; height: 44px; object-fit: cover; object-position: left center; }
        .icon-crop-posyandu { width: 80px; height: 38px; object-fit: cover; object-position: center top; }
        .icon-crop-itsnu { width: 44px; height: 44px; object-fit: contain; }
        .form-side { width: 100%; justify-content: center; }
        .login-card { padding: 32px 24px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08), 0 0 0 4px rgba(255,255,255,0.5); }
        .login-title { font-size: 22px; }
    }
</style>
@endpush

@section('content')
<div class="login-shell">
    <section class="brand-side">
        <!-- DESKTOP -->
        <div class="desktop-brand-area">
            <div class="partner-logos">
                <img src="{{ asset('img/diktisaintek.webp') }}" alt="Diktisaintek" class="partner-logo">
                <div class="partner-divider"></div>
                <img src="{{ asset('img/itsnu.webp') }}" alt="ITS NU" class="partner-logo">
            </div>
            <img src="{{ asset('img/logo.webp') }}" alt="PosyanduCare" class="brand-logo" width="320">
            <div class="brand-divider"><span class="bdl"></span><span class="bdd"></span><span class="bdl"></span></div>
            <p class="brand-desc">Platform layanan kesehatan terpadu untuk masyarakat modern.</p>
            <div class="feature-grid">
                <div class="feature-box"><div class="feature-icon"><svg class="icon"><use href="#icon-user-group"></use></svg></div><span class="feature-text">Terintegrasi</span></div>
                <div class="feature-box"><div class="feature-icon"><svg class="icon"><use href="#icon-shield"></use></svg></div><span class="feature-text">Aman</span></div>
                <div class="feature-box"><div class="feature-icon"><svg class="icon"><use href="#icon-chart"></use></svg></div><span class="feature-text">Efisien</span></div>
                <div class="feature-box"><div class="feature-icon"><svg class="icon"><use href="#icon-heart-pulse"></use></svg></div><span class="feature-text">Peduli</span></div>
            </div>
        </div>

        <!-- MOBILE -->
        <div class="mobile-brand-area">
            <img src="{{ asset('img/diktisaintek.webp') }}" alt="Icon Dikti" class="icon-crop-dikti">
            <img src="{{ asset('img/logo.webp') }}" alt="Icon PosyanduCare" class="icon-crop-posyandu">
            <img src="{{ asset('img/itsnu.webp') }}" alt="Icon ITS NU" class="icon-crop-itsnu">
        </div>
    </section>

    <section class="form-side">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Selamat Datang Kembali!</h1>
                <p class="login-subtitle">Masuk untuk melanjutkan ke Portal PosyanduCare</p>
            </div>

            <form id="loginForm" method="POST" action="{{ route('login.post') }}" class="login-form">
                @csrf
                <div class="field-group">
                    <label class="field-label" for="login">Email atau NIK</label>
                    <div class="field-wrap">
                        <input type="text" id="login" name="login" class="field-input" placeholder="Masukkan email atau NIK" required autofocus autocomplete="username">
                        <svg class="icon field-icon"><use href="#icon-user"></use></svg>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap">
                        <input type="password" id="password" name="password" class="field-input" placeholder="Masukkan password" required autocomplete="current-password">
                        <svg class="icon field-icon"><use href="#icon-lock"></use></svg>
                        <button type="button" id="pwToggle" class="pw-toggle" aria-label="Tampilkan password">
                            <svg class="icon" id="pwIcon"><use href="#icon-eye-slash"></use></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="submit-btn">
                    <span>Masuk</span>
                    <svg class="icon"><use href="#icon-arrow-right"></use></svg>
                </button>
            </form>

            <div class="info-note">
                <svg class="icon info-icon"><use href="#icon-shield"></use></svg>
                <div>
                    <p class="info-title">Akses khusus pengguna terdaftar</p>
                    <p class="info-text">Gunakan akun yang telah dibuat oleh petugas Posyandu untuk mengakses layanan kesehatan digital.</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pwInput = document.getElementById('password');
    const pwIconUse = document.querySelector('#pwIcon use');
    
    document.getElementById('pwToggle').addEventListener('click', function () {
        const isPassword = pwInput.type === 'password';
        pwInput.type = isPassword ? 'text' : 'password';
        pwIconUse.setAttribute('href', isPassword ? '#icon-eye' : '#icon-eye-slash');
    });

    const form = document.getElementById('loginForm');
    const loginInput = document.getElementById('login');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        const loginVal = loginInput.value.trim();
        const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(loginVal);
        const isNumeric = /^\d+$/.test(loginVal);
        const isNIK = isNumeric && loginVal.length === 16;

        if (!isEmail && !isNIK) {
            e.preventDefault(); 
            let errorMsg = 'Format tidak dikenali. Harap masukkan <b>Email yang valid</b> atau <b>16 Digit NIK</b>.';
            if (isNumeric && loginVal.length !== 16) {
                errorMsg = `Format NIK tidak tepat. NIK harus persis 16 digit angka.<br><br><i>(Saat ini Anda memasukkan ${loginVal.length} digit)</i>`;
            }
            window.showAuthAlert('Format Tidak Valid', errorMsg, 'warning');
            loginInput.focus();
            return;
        }

        // Tidak ada manipulasi opacity, transisi, atau animasi layar sama sekali!
        // Hanya mematikan tombol dan menyalakan spinner 0.1 detik
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="btn-spinner"></span><span style="margin-left:8px;">Masuk</span>';
    });

    @if($errors->any())
        window.showAuthAlert('Otentikasi Gagal', @json($errors->first()), 'error');
    @endif
});
</script>
@endpush