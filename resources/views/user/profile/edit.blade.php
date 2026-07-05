@extends('layouts.user')

@section('title', 'Profil Saya')
@section('page_title', 'Profil Saya')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    $user = $user ?? auth()->user();
    $profile = $profile ?? $user->profile ?? null;

    $profileSummary = $profileSummary ?? [
        'nik' => $user->nik ?? $profile->nik ?? null,
        'status_label' => 'Belum Terhubung',
        'status_tone' => 'amber',
        'total_sasaran' => 0,
        'total_balita' => 0,
        'total_remaja' => 0,
        'total_lansia' => 0,
        'peran' => 'Umum',
    ];

    $connectionCards = $connectionCards ?? [];

    $updateRoute = Route::has('user.profile.update') ? route('user.profile.update') : '#';
    $passwordRoute = Route::has('user.password.update') ? route('user.password.update') : '#';
    $dashboardRoute = Route::has('user.dashboard') ? route('user.dashboard') : '#';
    $monitoringRoute = Route::has('user.monitoring.index') ? route('user.monitoring.index') : '#';

    $birthValue = old('tanggal_lahir');
    if (! $birthValue && filled($profile->tanggal_lahir ?? null)) {
        try {
            $birthValue = Carbon::parse($profile->tanggal_lahir)->format('Y-m-d');
        } catch (Throwable $e) {
            $birthValue = $profile->tanggal_lahir;
        }
    }

    $avatarInitial = strtoupper(mb_substr($user->name ?? 'W', 0, 1));

    $cardTone = function ($tone) {
        return match ($tone) {
            'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-500', 'border' => 'border-rose-100', 'hover' => 'hover:border-rose-300'],
            'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-500', 'border' => 'border-sky-100', 'hover' => 'hover:border-sky-300'],
            'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-500', 'border' => 'border-amber-100', 'hover' => 'hover:border-amber-300'],
            default => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-500', 'border' => 'border-emerald-100', 'hover' => 'hover:border-emerald-300'],
        };
    };
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image: radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .animate-pop-in {
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .hero-grid {
        background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px);
        background-size: 24px 24px;
    }

    .btn-pill {
        border-radius: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.97); }

    .input-soft {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        outline: none;
        transition: all .2s ease;
    }
    .input-soft:focus {
        background: #ffffff;
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16,185,129,.1);
    }
    
    .input-error {
        border-color: rgba(244, 63, 94, 0.4) !important;
        background: rgba(255, 241, 242, 0.5) !important;
    }
    .input-error:focus {
        border-color: rgba(244, 63, 94, 0.8) !important;
        box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.1) !important;
    }

    .password-meter {
        height: 6px;
        width: 100%;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
    }
    .password-meter span {
        display: block;
        height: 100%;
        width: 0%;
        border-radius: 999px;
        background: linear-gradient(90deg, #f59e0b, #10b981);
        transition: width .3s ease;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- 1. HERO SECTION --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[2.5rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(20,184,166,.35)] border border-white/20 flex flex-col md:flex-row items-center justify-between gap-6 transition-all duration-500">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[70px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex items-center gap-5 md:w-2/3">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1rem] bg-white text-2xl font-black text-emerald-500 shadow-md border-2 border-white/40">
                {{ $avatarInitial }}
            </div>
            <div>
                <div class="flex flex-wrap gap-2 mb-1.5">
                    <span class="btn-pill bg-white/20 border border-white/30 text-white px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest backdrop-blur-md shadow-sm">
                        {{ $profileSummary['peran'] ?: 'Umum' }}
                    </span>
                    <span class="btn-pill bg-white/20 border border-white/30 text-white px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest backdrop-blur-md shadow-sm flex items-center gap-1">
                        <i class="fas fa-circle text-[6px] {{ ($profileSummary['status_tone'] ?? 'amber') === 'emerald' ? 'text-emerald-300' : 'text-amber-300' }}"></i> 
                        {{ $profileSummary['status_label'] }}
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight leading-tight line-clamp-1">
                    {{ $user->name ?? 'Warga Posyandu' }}
                </h1>
                <p class="text-emerald-50 text-[11px] font-semibold mt-1 opacity-90">
                    NIK Aktif: {{ $profileSummary['nik'] ?: 'Belum diisi' }}
                </p>
            </div>
        </div>

        <div class="relative z-10 shrink-0">
            <a href="{{ $monitoringRoute }}" data-no-delay="true" class="btn-pill bg-white hover:bg-emerald-50 text-emerald-600 px-6 py-3.5 text-[10px] font-black uppercase tracking-widest shadow-md transition-all flex items-center gap-2">
                <i class="fas fa-heart-pulse"></i> Buka Monitoring
            </a>
        </div>
    </section>

    {{-- FLASH MESSAGE SUKSES / GAGAL GLOBAL --}}
    @if(session('success') || session('error'))
        <div class="rounded-[1.25rem] border p-4 shadow-sm flex items-center gap-3 font-bold text-sm {{ session('error') ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
            <i class="fas {{ session('error') ? 'fa-triangle-exclamation' : 'fa-check-circle' }} text-lg"></i>
            {{ session('error') ?: session('success') }}
        </div>
    @endif

    {{-- 2. METRIK KONEKSI KELUARGA --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-[1.5rem] border border-slate-100 p-4 flex flex-col justify-center text-center shadow-sm hover:-translate-y-1 transition-all duration-300">
            <div class="w-10 h-10 mx-auto rounded-full bg-slate-50 text-slate-500 border border-slate-100 flex items-center justify-center text-lg mb-2 shadow-inner">
                <i class="fas fa-users-viewfinder"></i>
            </div>
            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Total Terhubung</p>
            <p class="text-2xl font-black text-slate-800 leading-none">{{ $profileSummary['total_sasaran'] }}</p>
        </div>

        @foreach($connectionCards as $item)
            @php $tone = $cardTone($item['tone'] ?? 'emerald'); @endphp
            <div class="bg-white rounded-[1.5rem] border border-slate-100 p-4 flex flex-col justify-center text-center shadow-sm transition-all duration-300 {{ $tone['hover'] }}">
                <div class="w-10 h-10 mx-auto rounded-full {{ $tone['bg'] }} {{ $tone['text'] }} {{ $tone['border'] }} border flex items-center justify-center text-lg mb-2 shadow-inner">
                    <i class="fas {{ $item['icon'] }}"></i>
                </div>
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">{{ $item['label'] }}</p>
                <p class="text-2xl font-black text-slate-800 leading-none">{{ $item['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- 3. AREA UTAMA: FORM PROFIL & SECURITY VAULT --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- KOLOM KIRI: Form Edit Identitas --}}
        <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col">
            <div class="bg-slate-50/70 px-6 sm:px-8 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-emerald-500 flex items-center justify-center shadow-sm shrink-0">
                    <i class="fas fa-id-card-clip text-xs"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-800">Kelola Informasi Warga</h2>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Data Identitas Utama</p>
                </div>
            </div>

            <form action="{{ $updateRoute }}" method="POST" class="flex flex-col h-full">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-6 sm:px-8">
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest mb-1.5 pl-1 text-slate-500">
                            Nomor Induk Kependudukan (NIK)
                        </label>
                        {{-- ATRIBUT READONLY DITAMBAHKAN DI SINI BESERTA WARNA LATAR ABU-ABU --}}
                        <input type="text" name="nik" value="{{ $user->nik ?? $profile->nik ?? '' }}" class="input-soft w-full bg-slate-100 cursor-not-allowed text-slate-500 opacity-70" placeholder="Belum Terdaftar" readonly>
                        <p class="mt-1.5 text-[10px] font-bold text-amber-500 pl-1 leading-relaxed">
                            <i class="fas fa-lock mr-1"></i> Terkunci. Hubungi Kader atau Bidan jika Anda perlu melakukan perubahan NIK.
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest mb-1.5 pl-1 {{ $errors->has('name') ? 'text-rose-500' : 'text-slate-500' }}">
                            Nama Lengkap
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name ?? $profile->full_name ?? '') }}" class="input-soft w-full {{ $errors->has('name') ? 'input-error' : '' }}" placeholder="Nama lengkap warga sesuai KTP">
                        @error('name')
                            <p class="mt-1.5 text-[10px] font-bold text-rose-500 pl-1"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5 pl-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="input-soft w-full cursor-pointer">
                            <option value="">Pilih Jenis Kelamin...</option>
                            <option value="L" @selected(old('jenis_kelamin', $profile->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin', $profile->jenis_kelamin ?? '') === 'P')>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5 pl-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ $birthValue }}" class="input-soft w-full">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5 pl-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $profile->tempat_lahir ?? '') }}" class="input-soft w-full" placeholder="Contoh: Pekalongan">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest {{ $errors->has('telepon') ? 'text-rose-500' : 'text-slate-500' }} mb-1.5 pl-1">No. WhatsApp / HP</label>
                        <input type="text" name="telepon" value="{{ old('telepon', $profile->telepon ?? '') }}" class="input-soft w-full {{ $errors->has('telepon') ? 'input-error' : '' }}" placeholder="Contoh: 089922773355">
                        @error('telepon')
                            <p class="mt-1.5 text-[10px] font-bold text-rose-500 pl-1"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5 pl-1">Alamat Domisili</label>
                        <textarea name="alamat" rows="3" class="input-soft w-full resize-none" placeholder="Masukkan alamat lengkap domisili saat ini...">{{ old('alamat', $profile->alamat ?? '') }}</textarea>
                    </div>
                </div>

                {{-- ACTION FOOTER (Solusi agar tidak mengambang) --}}
                <div class="mt-auto bg-slate-50/80 px-6 sm:px-8 py-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-[10px] font-bold text-slate-400 leading-relaxed text-center sm:text-left max-w-xs">
                        <i class="fas fa-info-circle mr-1 text-slate-300"></i> Pastikan alamat domisili Anda sesuai dengan KTP.
                    </p>
                    <button type="submit" class="btn-pill w-full sm:w-auto bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3.5 text-[11px] font-black uppercase tracking-widest shadow-sm flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Simpan Profil
                    </button>
                </div>
            </form>
        </div>

        {{-- KOLOM KANAN: Form Password & Bantuan --}}
        <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-6">
            
            {{-- Form Kata Sandi --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                <div class="bg-slate-50/70 px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-amber-500 flex items-center justify-center shadow-sm shrink-0">
                        <i class="fas fa-shield-halved text-xs"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800">Ubah Kata Sandi</h2>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Security Vault</p>
                    </div>
                </div>

                <form action="{{ $passwordRoute }}" method="POST" class="flex flex-col h-full">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4 p-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest mb-1.5 pl-1 {{ $errors->has('current_password') ? 'text-rose-500' : 'text-slate-500' }}">
                                Kata Sandi Saat Ini
                            </label>
                            <div class="relative">
                                <input type="password" name="current_password" autocomplete="current-password" class="input-soft w-full pr-12 password-field {{ $errors->has('current_password') ? 'input-error' : '' }}">
                                <button type="button" class="toggle-password absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1.5 text-[10px] font-bold text-rose-500 pl-1"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest mb-1.5 pl-1 {{ $errors->has('password') ? 'text-rose-500' : 'text-slate-500' }}">
                                Kata Sandi Baru
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="newPasswordInput" autocomplete="new-password" class="input-soft w-full pr-12 password-field {{ $errors->has('password') ? 'input-error' : '' }}">
                                <button type="button" class="toggle-password absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-[10px] font-bold text-rose-500 pl-1"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror

                            <div class="mt-3 px-1">
                                <div class="password-meter"><span id="passwordMeterBar"></span></div>
                                <p id="passwordMeterText" class="mt-1.5 text-[9px] font-black uppercase tracking-widest text-slate-400">Kekuatan Sandi</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5 pl-1">
                                Konfirmasi Sandi Baru
                            </label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" autocomplete="new-password" class="input-soft w-full pr-12 password-field">
                                <button type="button" class="toggle-password absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION FOOTER (Solusi agar tidak mengambang) --}}
                    <div class="mt-auto bg-slate-50/80 px-6 py-5 border-t border-slate-100">
                        <button type="submit" class="btn-pill w-full bg-amber-500 hover:bg-amber-600 text-white px-4 py-3.5 text-[11px] font-black uppercase tracking-widest shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-key"></i> Perbarui Sandi
                        </button>
                    </div>
                </form>
            </div>

            {{-- Kartu Bantuan Singkat --}}
            <div class="bg-sky-50/60 rounded-[2rem] border border-sky-100 p-6 flex items-start gap-4 shadow-sm">
                <div class="w-12 h-12 rounded-[1rem] bg-white border border-sky-200 text-sky-500 flex items-center justify-center shrink-0 shadow-sm text-lg">
                    <i class="fas fa-circle-info"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800">Data Tidak Muncul?</h3>
                    <p class="text-[11px] font-semibold text-slate-600 mt-1.5 leading-relaxed">
                        Jika NIK sudah benar tapi data belum tampil, NIK anak/lansia Anda di database Bidan mungkin belum di-update. Hubungi Kader Posyandu untuk proses sinkronisasi.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle Mata Kata Sandi
        document.querySelectorAll('.toggle-password').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = this.parentElement.querySelector('.password-field');
                const icon = this.querySelector('i');
                if (!input) return;

                const visible = input.getAttribute('type') === 'text';
                input.setAttribute('type', visible ? 'password' : 'text');

                if (icon) {
                    icon.classList.toggle('fa-eye', visible);
                    icon.classList.toggle('fa-eye-slash', !visible);
                }
            });
        });

        // Meteran Kekuatan Kata Sandi
        const passwordInput = document.getElementById('newPasswordInput');
        const meterBar = document.getElementById('passwordMeterBar');
        const meterText = document.getElementById('passwordMeterText');

        if (passwordInput && meterBar && meterText) {
            passwordInput.addEventListener('input', function () {
                const value = this.value;
                let score = 0;

                if (value.length >= 8) score++;
                if (/[A-Za-z]/.test(value)) score++;
                if (/[0-9]/.test(value)) score++;
                if (/[^A-Za-z0-9]/.test(value)) score++;

                const width = [0, 25, 50, 75, 100][score];
                meterBar.style.width = width + '%';

                if (!value) { meterText.textContent = 'Kekuatan Sandi'; return; }
                if (score <= 1) { meterText.textContent = 'Sandi Masih Lemah'; meterText.className = 'mt-1.5 text-[9px] font-black uppercase tracking-widest text-rose-500'; return; }
                if (score === 2) { meterText.textContent = 'Sandi Cukup'; meterText.className = 'mt-1.5 text-[9px] font-black uppercase tracking-widest text-amber-500'; return; }
                
                meterText.textContent = 'Sandi Kuat'; 
                meterText.className = 'mt-1.5 text-[9px] font-black uppercase tracking-widest text-emerald-500';
            });
        }
    });
</script>
@endpush