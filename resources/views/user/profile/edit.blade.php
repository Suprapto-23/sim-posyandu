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

    $updateRoute = Route::has('user.profile.update')
        ? route('user.profile.update')
        : '#';

    $passwordRoute = Route::has('user.password.update')
        ? route('user.password.update')
        : '#';

    $dashboardRoute = Route::has('user.dashboard')
        ? route('user.dashboard')
        : '#';

    $monitoringRoute = Route::has('user.monitoring.index')
        ? route('user.monitoring.index')
        : '#';

    $birthValue = old('tanggal_lahir');

    if (! $birthValue && filled($profile->tanggal_lahir ?? null)) {
        try {
            $birthValue = Carbon::parse($profile->tanggal_lahir)->format('Y-m-d');
        } catch (Throwable $e) {
            $birthValue = $profile->tanggal_lahir;
        }
    }

    $avatarInitial = strtoupper(mb_substr($user->name ?? 'W', 0, 1));

    $statusClass = ($profileSummary['status_tone'] ?? 'amber') === 'emerald'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-amber-200 bg-amber-50 text-amber-700';

    $cardTone = function ($tone) {
        return match ($tone) {
            'rose' => [
                'wrap' => 'border-rose-100 bg-rose-50/70 text-rose-800',
                'icon' => 'border-rose-100 bg-white text-rose-500',
            ],
            'sky' => [
                'wrap' => 'border-sky-100 bg-sky-50/70 text-sky-800',
                'icon' => 'border-sky-100 bg-white text-sky-500',
            ],
            'amber' => [
                'wrap' => 'border-amber-100 bg-amber-50/70 text-amber-800',
                'icon' => 'border-amber-100 bg-white text-amber-500',
            ],
            default => [
                'wrap' => 'border-emerald-100 bg-emerald-50/70 text-emerald-800',
                'icon' => 'border-emerald-100 bg-white text-emerald-500',
            ],
        };
    };
@endphp

@push('styles')
<style>
    .profile-page {
        background:
            radial-gradient(circle at 10% 10%, rgba(16,185,129,.16), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(14,165,233,.12), transparent 26%),
            radial-gradient(circle at 78% 88%, rgba(245,158,11,.10), transparent 28%),
            linear-gradient(135deg,#f8fafc 0%,#ecfdf5 48%,#eff6ff 100%);
    }

    .profile-enter {
        opacity: 0;
        animation: profileEnter .36s cubic-bezier(.16,1,.3,1) forwards;
    }

    .profile-enter-delay {
        opacity: 0;
        animation: profileEnter .36s cubic-bezier(.16,1,.3,1) .08s forwards;
    }

    @keyframes profileEnter {
        from {
            opacity: 0;
            transform: translateY(12px) scale(.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .profile-glass {
        border: 1px solid rgba(255,255,255,.78);
        background: rgba(255,255,255,.72);
        backdrop-filter: blur(20px);
        box-shadow: 0 16px 46px rgba(15,23,42,.06);
    }

    .profile-card {
        border: 1px solid rgba(226,232,240,.82);
        background: rgba(255,255,255,.76);
        backdrop-filter: blur(16px);
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
    }

    .identity-card {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(16,185,129,.18);
        background:
            linear-gradient(135deg, rgba(6,78,59,.96), rgba(6,95,70,.94) 48%, rgba(15,118,110,.94)),
            radial-gradient(circle at 20% 20%, rgba(255,255,255,.20), transparent 26%);
        box-shadow: 0 22px 52px rgba(6,78,59,.18);
    }

    .identity-card::before {
        content: "";
        position: absolute;
        inset: -80px auto auto -80px;
        width: 210px;
        height: 210px;
        border-radius: 999px;
        background: rgba(255,255,255,.10);
    }

    .identity-card::after {
        content: "";
        position: absolute;
        right: -70px;
        bottom: -95px;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(251,191,36,.16);
    }

    .profile-input {
        height: 48px;
        width: 100%;
        border-radius: 18px;
        border: 1px solid rgba(203,213,225,.78);
        background: rgba(255,255,255,.78);
        padding: 0 16px;
        font-size: 14px;
        font-weight: 800;
        color: rgb(51,65,85);
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .profile-input:focus {
        border-color: rgba(16,185,129,.46);
        background: rgba(255,255,255,.92);
        box-shadow: 0 0 0 4px rgba(16,185,129,.10);
    }

    .password-meter {
        height: 7px;
        width: 100%;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(226,232,240,.95);
    }

    .password-meter span {
        display: block;
        height: 100%;
        width: 0%;
        border-radius: 999px;
        background: linear-gradient(90deg, #f59e0b, #10b981);
        transition: width .2s ease;
    }

    @media (prefers-reduced-motion: reduce) {
        .profile-enter,
        .profile-enter-delay {
            animation: none;
            opacity: 1;
        }
    }
</style>
@endpush

@section('content')
<div class="profile-page -mx-4 -my-4 min-h-[calc(100vh-96px)] px-4 py-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">

        <section class="profile-enter grid grid-cols-1 gap-5 xl:grid-cols-[420px_minmax(0,1fr)]">
            <div class="identity-card rounded-[32px] p-6 text-white">
                <div class="relative z-10">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-emerald-100/90">
                                Kartu Profil Warga
                            </p>

                            <h1 class="mt-3 text-3xl font-black tracking-tight">
                                Profil Saya
                            </h1>
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-[22px] border border-white/20 bg-white/16 text-2xl font-black shadow-inner backdrop-blur-xl">
                            {{ $avatarInitial }}
                        </div>
                    </div>

                    <div class="mt-8 rounded-[26px] border border-white/18 bg-white/12 p-5 backdrop-blur-xl">
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-emerald-100/80">
                            Nama Warga
                        </p>

                        <p class="mt-2 text-2xl font-black leading-tight">
                            {{ $user->name ?? 'Warga Posyandu' }}
                        </p>

                        <div class="mt-5 grid grid-cols-1 gap-3">
                            <div class="rounded-[20px] border border-white/16 bg-white/10 px-4 py-3">
                                <p class="text-[9px] font-black uppercase tracking-[0.20em] text-emerald-100/70">NIK Aktif</p>
                                <p class="mt-1 break-all text-sm font-black text-white">
                                    {{ $profileSummary['nik'] ?: 'Belum diisi' }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-[20px] border border-white/16 bg-white/10 px-4 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.18em] text-emerald-100/70">Status</p>
                                    <p class="mt-1 text-sm font-black text-white">
                                        {{ $profileSummary['status_label'] }}
                                    </p>
                                </div>

                                <div class="rounded-[20px] border border-white/16 bg-white/10 px-4 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.18em] text-emerald-100/70">Peran</p>
                                    <p class="mt-1 truncate text-sm font-black text-white">
                                        {{ $profileSummary['peran'] ?: 'Umum' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ $monitoringRoute }}"
                       class="smooth-route mt-5 inline-flex h-12 w-full items-center justify-center rounded-[20px] border border-white/20 bg-white px-5 text-xs font-black uppercase tracking-[0.14em] text-emerald-700 shadow-[0_14px_30px_rgba(0,0,0,.10)] transition hover:bg-emerald-50">
                        Buka Monitoring Kesehatan
                    </a>
                </div>
            </div>

            <div class="profile-glass rounded-[32px] p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full border px-3.5 py-2 text-[10px] font-black uppercase tracking-[0.18em] {{ $statusClass }}">
                            <span class="h-2 w-2 rounded-full {{ ($profileSummary['status_tone'] ?? 'amber') === 'emerald' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $profileSummary['status_label'] }}
                        </span>

                        <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-800 sm:text-4xl">
                            Pusat Identitas Akun
                        </h2>

                        <p class="mt-2 max-w-3xl text-sm font-semibold leading-7 text-slate-600">
                            NIK menjadi kunci sinkronisasi data keluarga. Kalau NIK salah, halaman monitoring ikut tersesat seperti kurir yang cuma diberi petunjuk “rumahnya dekat pohon”.
                        </p>
                    </div>

                    <div class="rounded-[26px] border border-emerald-100 bg-white/72 px-5 py-4 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                            Total Terhubung
                        </p>

                        <p class="mt-1 text-4xl font-black text-emerald-700">
                            {{ $profileSummary['total_sasaran'] }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    @foreach($connectionCards as $item)
                        @php $tone = $cardTone($item['tone'] ?? 'emerald'); @endphp

                        <div class="rounded-[24px] border p-4 {{ $tone['wrap'] }}">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] opacity-70">
                                        {{ $item['label'] }}
                                    </p>

                                    <p class="mt-1 text-3xl font-black">
                                        {{ $item['value'] }}
                                    </p>

                                    <p class="mt-1 text-xs font-bold opacity-70">
                                        {{ $item['caption'] }}
                                    </p>
                                </div>

                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[18px] border text-base {{ $tone['icon'] }}">
                                    <i class="fas {{ $item['icon'] }}"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @if(session('success') || session('error') || $errors->any())
            <section class="profile-enter-delay rounded-[24px] border p-4 shadow-sm backdrop-blur-xl {{ session('error') || $errors->any() ? 'border-rose-200 bg-rose-50/85 text-rose-800' : 'border-emerald-200 bg-emerald-50/85 text-emerald-800' }}">
                <p class="text-sm font-black">
                    {{ session('error') ?: (session('success') ?: 'Terdapat data yang perlu diperbaiki.') }}
                </p>

                @if($errors->any())
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm font-semibold">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        <section class="profile-enter-delay grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
            <form action="{{ $updateRoute }}" method="POST" class="profile-card overflow-hidden rounded-[32px]">
                @csrf
                @method('PATCH')

                <div class="border-b border-emerald-100/80 bg-gradient-to-r from-white/88 via-emerald-50/76 to-sky-50/62 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.20em] text-emerald-700">
                        Data Identitas
                    </p>

                    <h2 class="mt-1 text-xl font-black text-slate-800">
                        Kelola Informasi Warga
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                            NIK
                        </label>

                        <input type="text"
                               name="nik"
                               value="{{ old('nik', $user->nik ?? $profile->nik ?? '') }}"
                               maxlength="16"
                               inputmode="numeric"
                               autocomplete="off"
                               class="profile-input mt-2"
                               placeholder="Masukkan 16 digit NIK">

                        <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                            NIK dipakai untuk menghubungkan akun dengan data Balita, Remaja, atau Lansia.
                        </p>
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                            Nama Lengkap
                        </label>

                        <input type="text"
                               name="name"
                               value="{{ old('name', $user->name ?? $profile->full_name ?? '') }}"
                               class="profile-input mt-2"
                               placeholder="Nama lengkap warga">
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                            Jenis Kelamin
                        </label>

                        <select name="jenis_kelamin" class="profile-input mt-2">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="L" @selected(old('jenis_kelamin', $profile->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin', $profile->jenis_kelamin ?? '') === 'P')>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                            Tempat Lahir
                        </label>

                        <input type="text"
                               name="tempat_lahir"
                               value="{{ old('tempat_lahir', $profile->tempat_lahir ?? '') }}"
                               class="profile-input mt-2"
                               placeholder="Contoh: Pekalongan">
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                            Tanggal Lahir
                        </label>

                        <input type="date"
                               name="tanggal_lahir"
                               value="{{ $birthValue }}"
                               class="profile-input mt-2">
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                            No. WhatsApp
                        </label>

                        <input type="text"
                               name="telepon"
                               value="{{ old('telepon', $profile->telepon ?? '') }}"
                               class="profile-input mt-2"
                               placeholder="Contoh: 089922773355">
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                            Status Akun
                        </label>

                        <div class="mt-2 flex h-12 items-center rounded-[18px] border border-emerald-100 bg-emerald-50/70 px-4 text-sm font-black text-emerald-700">
                            Portal Warga Aktif
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                            Alamat
                        </label>

                        <textarea name="alamat"
                                  rows="4"
                                  class="mt-2 w-full rounded-[18px] border border-slate-200 bg-white/78 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10"
                                  placeholder="Alamat domisili lengkap">{{ old('alamat', $profile->alamat ?? '') }}</textarea>
                    </div>

                    <div class="sm:col-span-2 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs font-semibold leading-5 text-slate-500">
                            Perubahan NIK dapat memengaruhi data yang tampil pada halaman monitoring.
                        </p>

                        <button type="submit"
                                class="inline-flex h-12 items-center justify-center rounded-[20px] bg-emerald-600 px-6 text-xs font-black uppercase tracking-[0.14em] text-white shadow-[0_12px_26px_rgba(16,185,129,.22)] transition hover:bg-emerald-700">
                            Simpan Profil
                        </button>
                    </div>
                </div>
            </form>

            <aside class="space-y-5">
                <form action="{{ $passwordRoute }}" method="POST" class="profile-card overflow-hidden rounded-[32px]">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-amber-100/80 bg-gradient-to-r from-white/88 via-amber-50/78 to-emerald-50/62 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-amber-700">
                            Security Vault
                        </p>

                        <h2 class="mt-1 text-xl font-black text-slate-800">
                            Ubah Kata Sandi
                        </h2>

                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                            Gunakan minimal 8 karakter, mengandung huruf dan angka.
                        </p>
                    </div>

                    <div class="space-y-4 p-5">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                                Kata Sandi Saat Ini
                            </label>

                            <div class="relative mt-2">
                                <input type="password"
                                       name="current_password"
                                       class="profile-input pr-12 password-field"
                                       autocomplete="current-password">

                                <button type="button"
                                        class="toggle-password absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                                Kata Sandi Baru
                            </label>

                            <div class="relative mt-2">
                                <input type="password"
                                       name="password"
                                       id="newPasswordInput"
                                       class="profile-input pr-12 password-field"
                                       autocomplete="new-password">

                                <button type="button"
                                        class="toggle-password absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <div class="mt-3">
                                <div class="password-meter">
                                    <span id="passwordMeterBar"></span>
                                </div>

                                <p id="passwordMeterText" class="mt-2 text-xs font-bold text-slate-500">
                                    Kekuatan sandi belum dinilai.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">
                                Konfirmasi Kata Sandi
                            </label>

                            <div class="relative mt-2">
                                <input type="password"
                                       name="password_confirmation"
                                       class="profile-input pr-12 password-field"
                                       autocomplete="new-password">

                                <button type="button"
                                        class="toggle-password absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit"
                                class="inline-flex h-12 w-full items-center justify-center rounded-[20px] bg-amber-500 px-6 text-xs font-black uppercase tracking-[0.14em] text-amber-950 shadow-[0_12px_26px_rgba(245,158,11,.22)] transition hover:bg-amber-400">
                            Perbarui Sandi
                        </button>
                    </div>
                </form>

                <section class="profile-card rounded-[32px] p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.20em] text-sky-700">
                        Bantuan Sinkronisasi
                    </p>

                    <h2 class="mt-2 text-xl font-black text-slate-800">
                        Data tidak muncul?
                    </h2>

                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Jika NIK sudah benar tetapi data kesehatan belum tampil, hubungi Kader untuk sinkronisasi data sasaran.
                    </p>

                    <a href="{{ $dashboardRoute }}"
                       class="smooth-route mt-4 inline-flex h-11 w-full items-center justify-center rounded-[18px] border border-sky-100 bg-white/82 px-5 text-xs font-black uppercase tracking-[0.12em] text-sky-700 transition hover:bg-sky-50">
                        Kembali ke Dashboard
                    </a>
                </section>
            </aside>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nikInput = document.querySelector('input[name="nik"]');

        if (nikInput) {
            nikInput.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 16);
            });
        }

        document.querySelectorAll('.toggle-password').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = this.parentElement.querySelector('.password-field');
                const icon = this.querySelector('i');

                if (! input) {
                    return;
                }

                const visible = input.getAttribute('type') === 'text';

                input.setAttribute('type', visible ? 'password' : 'text');

                if (icon) {
                    icon.classList.toggle('fa-eye', visible);
                    icon.classList.toggle('fa-eye-slash', ! visible);
                }
            });
        });

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

                if (! value) {
                    meterText.textContent = 'Kekuatan sandi belum dinilai.';
                    return;
                }

                if (score <= 1) {
                    meterText.textContent = 'Sandi masih lemah.';
                    return;
                }

                if (score === 2) {
                    meterText.textContent = 'Sandi cukup, tapi masih bisa dibuat lebih kuat.';
                    return;
                }

                if (score === 3) {
                    meterText.textContent = 'Sandi sudah kuat.';
                    return;
                }

                meterText.textContent = 'Sandi sangat kuat.';
            });
        }
    });
</script>
@endpush