@extends('layouts.auth')

@section('title', 'Selamat Datang | PosyanduCare')

@section('content')

<h1 class="card-title">Selamat Datang Kembali!</h1>
<p class="card-sub">Masuk untuk melanjutkan ke Portal PosyanduCare</p>

<form method="POST" action="{{ route('login.post') }}" id="loginFormEngine">
    @csrf

    <div class="field">
        <label for="login" class="field-label">Email atau Username</label>
        <div class="field-wrap">
            <i class="fa-regular fa-user fi-l"></i>
            <input type="text" id="login" name="login" value="{{ old('login') }}"
                class="field-input {{ $errors->has('login') ? 'err' : '' }}"
                placeholder="Masukkan email atau username"
                required autofocus autocomplete="off">
        </div>
    </div>

    <div class="field">
        <label for="password" class="field-label">Password</label>
        <div class="field-wrap">
            <i class="fa-solid fa-lock fi-l"></i>
            <input type="password" id="password" name="password"
                class="field-input {{ $errors->has('password') ? 'err' : '' }}"
                placeholder="Masukkan password"
                required autocomplete="current-password">
            <button type="button" class="fi-eye" onclick="togglePw()" aria-label="Tampilkan password">
                <i class="fa-regular fa-eye-slash" id="eyeIcon"></i>
            </button>
        </div>
    </div>

    <div class="forgot"><a href="#">Lupa password?</a></div>

    <button type="submit" id="submitActionBtn" class="btn-submit">
        <span id="submitTxt">Masuk</span>
        <i class="fa-solid fa-arrow-right" id="submitIcon"></i>
    </button>
</form>

<p class="account-note">
    Akun Anda didaftarkan oleh petugas Posyandu.<br>
    Hubungi petugas setempat atau <a href="#">minta bantuan di sini</a>.
</p>

@endsection

@push('scripts')
<script>
function togglePw() {
    var inp  = document.getElementById('password');
    var ico  = document.getElementById('eyeIcon');
    var show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    ico.classList.toggle('fa-eye-slash', !show);
    ico.classList.toggle('fa-eye', show);
}

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
        if (ico) {
            ico.classList.remove('fa-arrow-right');
            ico.classList.add('fa-circle-notch', 'fa-spin');
        }
        if (typeof window.runLoginTransition === 'function') {
            window.runLoginTransition(form);
        } else {
            form.submit();
        }
    });
});
</script>

{{-- ── ALERT: identitas (email/username/NIK) ── --}}
@if($errors->has('login'))
@php
    $loginErr   = $errors->first('login');
    $isNotFound = str_contains($loginErr, 'tidak ditemukan') || str_contains($loginErr, 'belum terdaftar');
    $isInactive = str_contains($loginErr, 'tidak aktif');
    $isFormat   = str_contains($loginErr, 'Format') || str_contains($loginErr, 'tidak valid');
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($isNotFound)
    window.NxAlert.fire({
        type        : 'error',
        title       : 'Akun Tidak Ditemukan',
        message     : 'Identitas yang Anda masukkan <strong>belum terdaftar</strong> di sistem PosyanduCare.'
                    + '<br><br>Pastikan email, username, atau NIK sudah benar. Jika belum memiliki akun, hubungi petugas Posyandu setempat.',
        confirmText : 'Mengerti',
    });
    @elseif($isInactive)
    window.NxAlert.fire({
        type        : 'error',
        title       : 'Akun Dinonaktifkan',
        message     : 'Akun Anda saat ini <strong>tidak aktif</strong> dan tidak dapat digunakan untuk masuk.'
                    + '<br><br>Silakan hubungi petugas atau admin Posyandu untuk mengaktifkan kembali akun Anda.',
        confirmText : 'Mengerti',
    });
    @elseif($isFormat)
    window.NxAlert.fire({
        type        : 'error',
        title       : 'Format Tidak Valid',
        message     : 'Masukkan <strong>email</strong>, <strong>username</strong>, atau <strong>NIK (16 digit angka)</strong> yang valid untuk masuk ke sistem.',
        confirmText : 'Coba Lagi',
    });
    @else
    window.NxAlert.fire({
        type        : 'error',
        title       : 'Login Gagal',
        message     : @json($loginErr),
        confirmText : 'Coba Lagi',
    });
    @endif
});
</script>
@endif

{{-- ── ALERT: password salah ── --}}
@if($errors->has('password'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.NxAlert.fire({
        type        : 'error',
        title       : 'Password Salah',
        message     : 'Password yang Anda masukkan <strong>tidak cocok</strong> dengan akun ini.'
                    + '<br><br>Periksa huruf kapital dan spasi, atau gunakan fitur <em>Lupa password</em> jika Anda lupa.',
        confirmText : 'Coba Lagi',
    });
});
</script>
@endif

{{-- ── ALERT: pesan sesi (logout, dll) ── --}}
@if(session('info'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.NxAlert.fire({
        type        : 'success',
        title       : 'Informasi',
        message     : @json(session('info')),
        confirmText : 'Tutup',
        timer       : 4000,
    });
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.NxAlert.fire({
        type        : 'error',
        title       : 'Terjadi Kesalahan',
        message     : @json(session('error')),
        confirmText : 'Tutup',
    });
});
</script>
@endif
@endpush