{{-- ▸ Konten ini di-inject ke @yield('content') di auth-layout.blade.php --}}
{{-- File: resources/views/auth/login.blade.php --}}

@extends('layouts.auth')
@section('title','Masuk')

@section('content')

<h2 class="card-title">Selamat Datang Kembali!</h2>
<p class="card-sub">Masuk untuk melanjutkan ke Portal PosyanduCare</p>

{{-- ── Laravel session errors ── --}}
@if($errors->any())
  <script>
    document.addEventListener('DOMContentLoaded',function(){
      @if($errors->has('nik'))
        NxAlert.fire({
          type:'error',
          title:'NIK Tidak Terdaftar',
          message:'NIK yang Anda masukkan <strong>belum terdaftar</strong> di sistem.<br>Silakan hubungi admin Posyandu untuk pendaftaran akun.',
          confirmText:'Mengerti'
        });
      @elseif($errors->has('password'))
        NxAlert.fire({
          type:'error',
          title:'Sandi Salah',
          message:'Password yang Anda masukkan tidak sesuai.<br>Periksa kembali atau gunakan <strong>Lupa password?</strong> di bawah.',
          confirmText:'Coba Lagi'
        });
      @elseif($errors->has('inactive'))
        NxAlert.fire({
          type:'warning',
          title:'Akun Tidak Aktif',
          message:'Akun Anda belum diaktifkan.<br>Silakan hubungi petugas Posyandu untuk aktivasi.',
          confirmText:'Mengerti'
        });
      @elseif($errors->has('locked'))
        NxAlert.fire({
          type:'warning',
          title:'Akun Terkunci',
          message:'Akun Anda terkunci sementara karena terlalu banyak percobaan gagal.<br>Coba lagi dalam <strong>30 menit</strong> atau hubungi admin.',
          confirmText:'Mengerti'
        });
      @else
        NxAlert.fire({
          type:'error',
          title:'Login Gagal',
          message:'{{ $errors->first() }}',
          confirmText:'Tutup'
        });
      @endif
    });
  </script>
@endif

{{-- ── Flash success ── --}}
@if(session('status'))
  <script>
    document.addEventListener('DOMContentLoaded',function(){
      NxAlert.fire({
        type:'success',
        title:'Berhasil',
        message:'{{ session("status") }}',
        confirmText:'Tutup',
        timer:4000
      });
    });
  </script>
@endif

<form id="loginFormEngine" method="POST" action="{{ route('login') }}" novalidate>
  @csrf

  {{-- NIK Field --}}
  <div class="field">
    <label for="nikInput" class="field-label">NIK atau Email</label>
    <div class="field-wrap">
      <input
        type="text"
        id="nikInput"
        name="nik"
        class="field-input {{ $errors->has('nik') ? 'is-err' : '' }}"
        placeholder="Masukkan NIK (16 digit) atau email"
        value="{{ old('nik') }}"
        autocomplete="username"
        inputmode="text"
        required
      >
      <i class="fa-solid fa-user fi-icon-l" aria-hidden="true"></i>
    </div>
    <span class="field-msg err {{ $errors->has('nik') ? 'show' : '' }}" id="nikMsg">
      {{ $errors->first('nik') }}
    </span>
  </div>

  {{-- Password Field --}}
  <div class="field">
    <label for="passInput" class="field-label">Sandi</label>
    <div class="field-wrap">
      <input
        type="password"
        id="passInput"
        name="password"
        class="field-input {{ $errors->has('password') ? 'is-err' : '' }}"
        placeholder="Masukkan sandi Anda"
        autocomplete="current-password"
        required
      >
      <i class="fa-solid fa-lock fi-icon-l" aria-hidden="true"></i>
      <button type="button" class="fi-eye" id="eyeBtn" aria-label="Tampilkan/sembunyikan sandi">
        <i class="fa-solid fa-eye"></i>
      </button>
    </div>
    <span class="field-msg err {{ $errors->has('password') ? 'show' : '' }}" id="passMsg">
      {{ $errors->first('password') }}
    </span>
  </div>

  <!--  -->

  {{-- Submit Button --}}
  <button type="submit" class="btn-submit" id="submitActionBtn">
    <span id="submitTxt">Masuk</span>
    <i class="fa-solid fa-arrow-right ic-arrow" id="submitIcon" aria-hidden="true"></i>
    <span class="ic-spin" aria-hidden="true"></span>
  </button>

</form>

{{-- Admin note --}}
<div class="admin-note" role="note">
  <div class="admin-note-icon" aria-hidden="true">
    <i class="fa-solid fa-circle-info"></i>
  </div>
  <p class="admin-note-text">
    Akun dibuat oleh petugas Posyandu. Belum punya akun atau ada kendala?
    <a href="mailto:admin@posyanducare.id">Hubungi admin</a>.
  </p>
</div>

@endsection

@push('scripts')
<script>
/* ── CLIENT-SIDE VALIDATION ── */
(function(){
  var form    = document.getElementById('loginFormEngine');
  var nikEl   = document.getElementById('nikInput');
  var passEl  = document.getElementById('passInput');
  var nikMsg  = document.getElementById('nikMsg');
  var passMsg = document.getElementById('passMsg');

  function setErr(input,msgEl,text){
    input.classList.add('is-err');
    msgEl.textContent=text;
    msgEl.classList.add('show','err');
  }
  function clearErr(input,msgEl){
    input.classList.remove('is-err');
    msgEl.classList.remove('show');
  }
  function isEmail(v){return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);}
  function isNIK(v){return /^\d{16}$/.test(v);}

  if(!form)return;

  nikEl.addEventListener('blur',function(){
    var v=nikEl.value.trim();
    if(!v){setErr(nikEl,nikMsg,'NIK atau email tidak boleh kosong.');}
    else if(!isNIK(v)&&!isEmail(v)){setErr(nikEl,nikMsg,'Masukkan NIK 16 digit atau format email yang valid.');}
    else{clearErr(nikEl,nikMsg);}
  });
  passEl.addEventListener('blur',function(){
    if(!passEl.value){setErr(passEl,passMsg,'Sandi tidak boleh kosong.');}
    else{clearErr(passEl,passMsg);}
  });
  nikEl.addEventListener('input',function(){if(nikEl.classList.contains('is-err'))clearErr(nikEl,nikMsg);});
  passEl.addEventListener('input',function(){if(passEl.classList.contains('is-err'))clearErr(passEl,passMsg);});
})();
</script>
@endpush