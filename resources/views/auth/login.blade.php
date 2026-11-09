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

    .info-note { margin-top: 24px; padding: 16px; background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 16px; display: flex; align-items: flex-start; gap: 12px; }
    .info-icon { color: var(--green-700); width: 20px; height: 20px; flex-shrink: 0; }
    .info-title { color: var(--green-900); font-size: 13px; font-weight: 700; margin: 0 0 4px; }
    .info-text { color: var(--slate-700); font-size: 12px; margin: 0; line-height: 1.5; font-weight: 500; }

    /* === STATE LOGIN: spinner, sukses, error, transisi keluar === */
    .submit-btn { position: relative; }
    .submit-btn .spinner { display: none; width: 18px; height: 18px; border: 2.5px solid rgba(255,255,255,0.35); border-top-color: #fff; border-radius: 50%; animation: btnSpin .6s linear infinite; }
    .submit-btn.is-loading .spinner { display: block; }
    .submit-btn.is-loading .btn-arrow { display: none; }
    .submit-btn.is-success { background: var(--green-700); }
    .submit-btn:disabled { opacity: 1; }
    @keyframes btnSpin { to { transform: rotate(360deg); } }

    .field-input.is-invalid { border-color: var(--rose-500); box-shadow: 0 0 0 4px rgba(244,63,94,0.10); }
    .login-card.shake { animation: cardShake .38s cubic-bezier(.36,.07,.19,.97); }
    @keyframes cardShake { 10%,90% { transform: translateX(-2px); } 20%,80% { transform: translateX(4px); } 30%,50%,70% { transform: translateX(-6px); } 40%,60% { transform: translateX(6px); } }

    .login-card { transition: opacity .18s ease, transform .18s ease; }
    .login-shell.is-leaving .login-card { opacity: .55; transform: scale(.985); pointer-events: none; }

    @media (prefers-reduced-motion: reduce) {
        .login-card.shake { animation: none; }
        .login-card, .submit-btn .spinner { transition: none; }
    }

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

            <form id="loginForm" method="POST" action="{{ route('login.post') }}" class="login-form" data-no-overlay>
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
                    <span id="btnLabel">Masuk</span>
                    <svg class="icon btn-arrow" id="btnIcon"><use href="#icon-arrow-right"></use></svg>
                    <span class="spinner" aria-hidden="true"></span>
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
    const form      = document.getElementById('loginForm');
    const shell     = document.querySelector('.login-shell');
    const card      = document.querySelector('.login-card');
    const loginInp  = document.getElementById('login');
    const pwInp     = document.getElementById('password');
    const btn       = document.getElementById('submitBtn');
    const btnLabel  = document.getElementById('btnLabel');
    const btnIconUse = document.querySelector('#btnIcon use');
    const pwIconUse = document.querySelector('#pwIcon use');
    const csrf      = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let busy = false;

    // Intip password
    document.getElementById('pwToggle').addEventListener('click', function () {
        const show = pwInp.type === 'password';
        pwInp.type = show ? 'text' : 'password';
        pwIconUse.setAttribute('href', show ? '#icon-eye' : '#icon-eye-slash');
    });

    // Hapus tanda error begitu user mengetik lagi
    [loginInp, pwInp].forEach(el => el.addEventListener('input', () => el.classList.remove('is-invalid')));

    // ---- helper state tombol ----
    function setLoading(on) {
        busy = on;
        btn.disabled = on;
        btn.classList.toggle('is-loading', on);
        btnLabel.textContent = on ? 'Memeriksa...' : 'Masuk';
        if (on) { window.startProgress && window.startProgress(); }
        else    { window.stopProgress && window.stopProgress(); }
    }

    function resetUI() {
        setLoading(false);
        btn.classList.remove('is-success');
        btnIconUse.setAttribute('href', '#icon-arrow-right');
        shell.classList.remove('is-leaving');
    }

    function fail(message, field) {
        setLoading(false);
        card.classList.remove('shake');
        void card.offsetWidth;            // restart animasi
        card.classList.add('shake');
        if (field) { field.classList.add('is-invalid'); field.focus(); field.select && field.select(); }
        window.showModernAlert(message, 'error');
    }

    function success(url) {
        window.finishProgress && window.finishProgress();
        btn.classList.remove('is-loading');
        btn.classList.add('is-success');
        btnLabel.textContent = 'Berhasil';
        btnIconUse.setAttribute('href', '#icon-check');
        shell.classList.add('is-leaving');
        // Pindah SEKETIKA. Halaman lama tetap tampil (transisi halus) sampai dashboard siap.
        window.location.replace(url);
    }

    // ---- submit via fetch ----
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (busy) return;

        const loginVal = loginInp.value.trim();
        const pwVal    = pwInp.value;

        const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(loginVal);
        const isNIK   = /^\d{16}$/.test(loginVal);
        if (!isEmail && !isNIK) {
            return fail('Masukkan email yang valid atau NIK 16 digit.', loginInp);
        }
        if (!pwVal) {
            return fail('Password wajib diisi.', pwInp);
        }

        setLoading(true);                 // feedback instan: spinner di tombol + progress bar tipis

        const ctrl  = new AbortController();
        const timer = setTimeout(() => ctrl.abort(), 15000);   // jaga-jaga: tidak pernah loading selamanya

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                signal: ctrl.signal,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ login: loginVal, password: pwVal })
            });
            clearTimeout(timer);

            const data = await res.json().catch(() => ({}));

            if (res.ok && data.success && data.redirect) {
                return success(data.redirect);
            }

            if (res.status === 419) {      // sesi/CSRF kedaluwarsa
                window.showModernAlert('Sesi berakhir. Memuat ulang halaman...', 'error');
                return setTimeout(() => window.location.reload(), 700);
            }

            fail(data.message || 'Kredensial tidak ditemukan atau password salah.',
                 data.field === 'login' ? loginInp : pwInp);
        } catch (err) {
            clearTimeout(timer);
            fail(err.name === 'AbortError'
                ? 'Server terlalu lama merespons. Silakan coba lagi.'
                : 'Gagal terhubung ke server. Periksa koneksi Anda.');
        }
    });

    // Tombol "Back" dari dashboard (bfcache) → pastikan halaman tidak tersangkut di status loading
    window.addEventListener('pageshow', function (ev) { if (ev.persisted) resetUI(); });
});
</script>
@endpush