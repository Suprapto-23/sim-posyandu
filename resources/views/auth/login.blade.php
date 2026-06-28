@extends('layouts.auth')

@section('title', 'Login | PosyanduCare')

@push('styles')
<style>
    .login-shell {
        width: 100%; max-width: 1140px; 
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 60px;
        overflow: hidden; /* Mencegah scrollbar saat animasi belah */
    }

    /* ===== ANIMASI KIRI (BRAND) & KANAN (FORM) ===== */
    .brand-side, .form-side {
        transition: transform 0.8s cubic-bezier(0.65, 0, 0.1, 1), opacity 0.6s ease;
        will-change: transform, opacity;
    }

    /* Kelas saat disubmit (Terbelah) */
    body.is-splitting .brand-side {
        transform: translateX(-150%);
        opacity: 0;
    }
    body.is-splitting .form-side {
        transform: translateX(150%);
        opacity: 0;
    }

    /* ===== STYLING BRAND KIRI ===== */
    .brand-side { display: flex; flex-direction: column; align-items: center; text-align: center; }
    .brand-logo { width: 280px; height: auto; margin-bottom: 24px; user-select: none; pointer-events: none; }
    .brand-title { color: var(--slate-900); font-size: 24px; font-weight: 800; margin: 0 0 12px; letter-spacing: -0.02em; }
    .brand-divider { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 16px; }
    .bdl { width: 40px; height: 2px; background: #e2e8f0; border-radius: 4px; }
    .bdd { width: 6px; height: 6px; background: var(--amber-500); transform: rotate(45deg); }
    .brand-desc { color: var(--slate-500); font-size: 15px; font-weight: 500; max-width: 380px; margin: 0 0 32px; line-height: 1.6; }

    .feature-grid { display: flex; gap: 16px; justify-content: center; width: 100%; max-width: 440px; }
    .feature-box { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; padding: 16px 12px; width: 90px; display: flex; flex-direction: column; align-items: center; gap: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); }
    .feature-icon { width: 36px; height: 36px; border-radius: 10px; background: #ecfdf5; color: var(--green-600); display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .feature-text { color: var(--slate-700); font-size: 11px; font-weight: 700; }

    /* ===== STYLING FORM KANAN ===== */
    .form-side { display: flex; justify-content: flex-end; }
    .login-card { width: 100%; max-width: 440px; background: #ffffff; border-radius: 28px; padding: 40px; box-shadow: 0 24px 50px rgba(0,0,0,0.04), 0 0 0 8px rgba(255,255,255,0.4); }
    .login-header { text-align: center; margin-bottom: 32px; }
    .login-title { color: var(--green-900); font-size: 26px; font-weight: 800; margin: 0 0 8px; letter-spacing: -0.03em; }
    .login-subtitle { color: var(--slate-500); font-size: 14px; margin: 0; font-weight: 500; }
    .login-form { display: flex; flex-direction: column; gap: 20px; }
    .field-group { width: 100%; }
    .field-label { display: block; font-size: 13px; font-weight: 700; color: var(--slate-900); margin-bottom: 8px; }
    .field-wrap { position: relative; }
    .field-input { width: 100%; height: 52px; padding: 0 44px; border: 1.5px solid #e2e8f0; border-radius: 14px; font-size: 14px; color: var(--slate-900); font-weight: 500; transition: all 0.2s ease; }
    .field-input:focus { border-color: var(--green-500); outline: none; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
    .field-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px; transition: color 0.2s; }
    .field-input:focus ~ .field-icon { color: var(--green-600); }
    .pw-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; }
    
    .submit-btn {
        width: 100%; height: 54px; background: var(--green-600); color: #fff;
        border: none; border-radius: 14px; font-size: 16px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        cursor: pointer; transition: all 0.2s; margin-top: 4px;
    }
    .submit-btn:hover { background: var(--green-700); }
    
    .info-note { margin-top: 24px; padding: 16px; background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 16px; display: flex; align-items: flex-start; gap: 12px; }
    .info-icon { color: var(--green-600); font-size: 20px; }
    .info-title { color: var(--green-900); font-size: 13px; font-weight: 700; margin: 0 0 4px; }
    .info-text { color: var(--slate-600); font-size: 12px; margin: 0; line-height: 1.5; }

    @media (max-width: 992px) {
        .login-shell { grid-template-columns: 1fr; justify-items: center; gap: 40px; }
        .brand-side { display: none; }
        body.is-splitting .form-side { transform: translateY(100%); } /* Di HP Turun ke bawah */
    }
</style>
@endpush

@section('content')
<div class="login-shell">
    <section class="brand-side">
        <img src="{{ asset('img/logo.webp') }}" alt="PosyanduCare" class="brand-logo">
        <h2 class="brand-title">Sehat Bersama, Tumbuh Setiap Generasi</h2>
        <div class="brand-divider"><span class="bdl"></span><span class="bdd"></span><span class="bdl"></span></div>
        <p class="brand-desc">Platform layanan kesehatan terpadu untuk masyarakat modern.</p>
        
        <div class="feature-grid">
            <div class="feature-box"><div class="feature-icon"><i class="fa-solid fa-user-group"></i></div><span class="feature-text">Terintegrasi</span></div>
            <div class="feature-box"><div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div><span class="feature-text">Aman</span></div>
            <div class="feature-box"><div class="feature-icon"><i class="fa-solid fa-chart-simple"></i></div><span class="feature-text">Efisien</span></div>
            <div class="feature-box"><div class="feature-icon"><i class="fa-solid fa-heart-pulse"></i></div><span class="feature-text">Peduli</span></div>
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
                    <label class="field-label">Email atau NIK</label>
                    <div class="field-wrap">
                        <input type="text" name="login" class="field-input" placeholder="Masukkan email atau NIK" required autofocus>
                        <i class="fa-regular fa-user field-icon"></i>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Password</label>
                    <div class="field-wrap">
                        <input type="password" id="password" name="password" class="field-input" placeholder="Masukkan password" required>
                        <i class="fa-solid fa-lock field-icon"></i>
                        <button type="button" id="pwToggle" class="pw-toggle">
                            <i class="fa-regular fa-eye-slash" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="submit-btn">
                    <span id="submitTxt">Masuk</span>
                    <i class="fa-solid fa-arrow-right-to-bracket" id="submitIco"></i>
                </button>
            </form>

            <div class="info-note">
                <i class="fa-solid fa-shield-halved info-icon"></i>
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
    // Toggle Password
    const pwInput = document.getElementById('password');
    document.getElementById('pwToggle').addEventListener('click', function () {
        const isPassword = pwInput.type === 'password';
        pwInput.type = isPassword ? 'text' : 'password';
        document.getElementById('pwIcon').className = isPassword ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
    });

    const form = document.getElementById('loginForm');
    const loginInput = document.querySelector('input[name="login"]');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); 
        
        const loginVal = loginInput.value.trim();
        const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(loginVal);
        const isNumeric = /^\d+$/.test(loginVal);
        const isNIK = isNumeric && loginVal.length === 16;

        // Validasi Cepat Klien (Mencegah submit ke server jika format salah)
        if (!isEmail && !isNIK) {
            let errorMsg = 'Format tidak dikenali. Harap masukkan <b>Email yang valid</b> atau <b>16 Digit NIK</b>.';
            if (isNumeric && loginVal.length !== 16) {
                errorMsg = `Format NIK tidak tepat. NIK harus persis 16 digit angka.<br><br><i>(Saat ini Anda memasukkan ${loginVal.length} digit)</i>`;
            }
            window.showAuthAlert('Format Tidak Valid', errorMsg, 'warning');
            loginInput.focus();
            return;
        }

        // 1. Ubah Tombol Menjadi Loading Instan
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.9';
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Membuka Dasbor...</span>';

        // 2. Picu Animasi Split / Terbelah ke Kiri dan Kanan
        document.body.classList.add('is-splitting');

        // 3. Jeda 500ms agar animasi belah terlihat utuh, lalu kirim form
        setTimeout(() => {
            form.submit();
        }, 500); 
    });

    // Notifikasi jika ada Error validasi dari Server Laravel
    @if($errors->any())
        window.showAuthAlert('Otentikasi Gagal', @json($errors->first()), 'error');
    @endif
});
</script>
@endpush