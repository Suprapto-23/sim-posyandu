@extends('layouts.kader')

@section('title', 'Edit Data Balita')
@section('page-name', 'Edit Data Balita')
@section('page-title', 'Edit Data Balita')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $backRoute = $routeHas('kader.data.balita.index')
        ? route('kader.data.balita.index')
        : url('/kader/data/balita');

    $showRoute = $routeHas('kader.data.balita.show')
        ? route('kader.data.balita.show', $balita->id)
        : null;

    $updateRoute = $routeHas('kader.data.balita.update')
        ? route('kader.data.balita.update', $balita->id)
        : url('/kader/data/balita/' . $balita->id);

    $selectedGender = old('jenis_kelamin', $balita->jenis_kelamin ?? '');

    $tanggalLahir = old(
        'tanggal_lahir',
        $balita->tanggal_lahir ? Carbon::parse($balita->tanggal_lahir)->format('Y-m-d') : ''
    );

    $akunTerhubung = filled($balita->user_id ?? null);

    $sessionType = session('success')
        ? 'success'
        : (session('warning') ? 'warning' : (session('error') ? 'error' : null));

    $sessionMessage = session('success') ?? session('warning') ?? session('error');

    $statusClass = $akunTerhubung
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-amber-200 bg-amber-50 text-amber-700';

    $statusText = $akunTerhubung ? 'Akun Terhubung' : 'Belum Terhubung';
@endphp

@push('styles')
<style>
    .edit-nexus-bg {
        background:
            radial-gradient(circle at 8% 10%, rgba(16, 185, 129, 0.16), transparent 30%),
            radial-gradient(circle at 92% 14%, rgba(245, 158, 11, 0.13), transparent 28%),
            radial-gradient(circle at 78% 84%, rgba(14, 165, 233, 0.12), transparent 30%),
            linear-gradient(135deg, #f8fafc 0%, #ecfdf5 45%, #eff6ff 100%);
    }

    .edit-nexus-bg::before {
        content: "";
        position: fixed;
        inset: 0;
        pointer-events: none;
        background-image:
            linear-gradient(rgba(15, 23, 42, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, 0.035) 1px, transparent 1px);
        background-size: 34px 34px;
        mask-image: linear-gradient(to bottom, black, transparent 82%);
    }

    .edit-glass {
        border: 1px solid rgba(255, 255, 255, 0.72);
        background: rgba(255, 255, 255, 0.78);
        backdrop-filter: blur(22px);
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
    }

    .form-label {
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgb(100, 116, 139);
    }

    .form-input,
    .form-textarea {
        width: 100%;
        border-radius: 1.1rem;
        border: 1px solid rgba(203, 213, 225, 0.92);
        background: rgba(248, 250, 252, 0.86);
        font-size: 0.95rem;
        font-weight: 800;
        color: rgb(15, 23, 42);
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .form-input {
        height: 3.35rem;
        padding: 0 1rem;
    }

    .form-textarea {
        min-height: 8rem;
        resize: vertical;
        padding: 1rem;
        line-height: 1.7;
    }

    .form-input::placeholder,
    .form-textarea::placeholder {
        color: rgb(148, 163, 184);
        font-weight: 800;
    }

    .form-input:focus,
    .form-textarea:focus {
        border-color: rgba(5, 150, 105, 0.75);
        background: rgb(255, 255, 255);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
    }

    .form-error {
        border-color: rgba(225, 29, 72, 0.75) !important;
        box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.1) !important;
    }

    .form-error-text {
        margin-top: 0.5rem;
        font-size: 0.75rem;
        font-weight: 800;
        color: rgb(225, 29, 72);
    }

    .gender-card {
        display: flex;
        min-height: 3.35rem;
        cursor: pointer;
        align-items: center;
        gap: 0.8rem;
        border-radius: 1.1rem;
        border: 1px solid rgba(203, 213, 225, 0.92);
        background: rgba(248, 250, 252, 0.86);
        padding: 0.6rem 0.75rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
    }

    .gender-card:hover,
    .gender-card:has(input:checked) {
        border-color: rgba(5, 150, 105, 0.75);
        background: rgb(255, 255, 255);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }

    .gender-card:hover {
        transform: translateY(-1px);
    }

    .gender-dot {
        display: flex;
        height: 2.35rem;
        width: 2.35rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 0.85rem;
        font-size: 0.8rem;
        font-weight: 900;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .nexus-modal {
        opacity: 0;
        pointer-events: none;
        transition: opacity .22s ease;
    }

    .nexus-modal.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .nexus-modal-card {
        transform: translateY(16px) scale(.96);
        opacity: 0;
        transition: transform .24s ease, opacity .24s ease;
    }

    .nexus-modal.is-open .nexus-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    @media (max-width: 640px) {
        .form-input {
            height: 3.2rem;
        }

        .gender-card {
            min-height: 3.2rem;
        }
    }
</style>
@endpush

@section('content')
<div class="edit-nexus-bg relative min-h-[calc(100vh-96px)] px-4 py-6 sm:px-6 lg:px-8">
    <div class="relative z-10 mx-auto max-w-6xl space-y-6">

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[34px] bg-gradient-to-br from-slate-950 via-emerald-950 to-teal-800 shadow-[0_30px_90px_rgba(15,23,42,0.16)]">
            <div class="absolute right-0 top-0 h-44 w-44 rounded-bl-[90px] bg-white/10"></div>
            <div class="absolute -bottom-20 right-40 h-40 w-40 rounded-full bg-amber-300/10 blur-2xl"></div>
            <div class="absolute bottom-0 left-12 h-24 w-60 rounded-t-[80px] bg-emerald-300/10"></div>

            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-300/25 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[0.22em] text-emerald-100 backdrop-blur-xl">
                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            Mode Perubahan Data
                        </div>

                        <h1 class="mt-5 text-4xl font-black tracking-tight text-white sm:text-5xl">
                            Edit Data Balita
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold leading-7 text-emerald-50/80 sm:text-base">
                            Perbarui data Balita seperlunya. Perubahan NIK akan memengaruhi pencarian dan sinkronisasi akun warga,
                            jadi jangan asal ubah seperti orang edit caption tengah malam.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:w-[320px]">
                        <a href="{{ $backRoute }}"
                           class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 text-center text-sm font-black text-white backdrop-blur-xl transition hover:bg-white/15">
                            Kembali
                        </a>

                        @if($showRoute)
                            <a href="{{ $showRoute }}"
                               class="rounded-2xl bg-emerald-400 px-4 py-4 text-center text-sm font-black text-emerald-950 shadow-[0_18px_40px_rgba(52,211,153,0.24)] transition hover:bg-emerald-300">
                                Detail
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- SESSION ALERT --}}
        @if($sessionType && $sessionMessage)
            @php
                $alertClass = match ($sessionType) {
                    'error' => 'border-rose-200 bg-rose-50/90 text-rose-800',
                    'warning' => 'border-amber-200 bg-amber-50/90 text-amber-800',
                    default => 'border-emerald-200 bg-emerald-50/90 text-emerald-800',
                };

                $alertTitle = match ($sessionType) {
                    'error' => 'Aksi gagal',
                    'warning' => 'Perhatian',
                    default => 'Berhasil',
                };
            @endphp

            <div class="rounded-[24px] border px-5 py-4 shadow-sm backdrop-blur-xl {{ $alertClass }}">
                <p class="text-sm font-black">{{ $alertTitle }}</p>
                <p class="mt-1 text-sm font-semibold leading-6">{{ $sessionMessage }}</p>
            </div>
        @endif

        {{-- VALIDATION ALERT --}}
        @if($errors->any())
            <div class="rounded-[24px] border border-rose-200 bg-rose-50/90 px-5 py-4 text-rose-800 shadow-sm backdrop-blur-xl">
                <p class="text-sm font-black">Data belum bisa diperbarui</p>
                <ul class="mt-2 space-y-1 text-sm font-semibold leading-6">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM --}}
        <form id="balitaEditForm"
              action="{{ $updateRoute }}"
              method="POST"
              class="edit-glass overflow-hidden rounded-[34px]">
            @csrf
            @method('PUT')

            @if(isset($balita->nik_ibu) && filled($balita->nik_ibu))
                <input type="hidden" name="nik_ibu" value="{{ old('nik_ibu', $balita->nik_ibu) }}">
            @endif

            {{-- FORM HEADER --}}
            <div class="border-b border-emerald-100/80 bg-gradient-to-r from-white/85 via-emerald-50/70 to-amber-50/50 px-6 py-5 sm:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.20em] text-emerald-700">
                            Formulir Perubahan Data
                        </p>

                        <h2 class="mt-2 text-2xl font-black text-slate-950">
                            {{ $balita->nama_lengkap ?? 'Data Balita' }}
                        </h2>

                        <p class="mt-1 text-sm font-semibold text-slate-500">
                            NIK {{ $balita->nik ?? '-' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-2xl border bg-white px-4 py-3 text-sm font-black shadow-sm {{ $statusClass }}">
                            {{ $statusText }}
                        </div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-black text-amber-700 shadow-sm">
                            Mode Edit
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-7 p-6 sm:p-8">

                {{-- IDENTITAS --}}
                <section class="rounded-[28px] border border-emerald-100/80 bg-white/72 p-5 shadow-[0_16px_50px_rgba(15,23,42,0.04)] sm:p-6">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
                            01
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-950">
                                Identitas Balita
                            </h3>
                            <p class="text-sm font-semibold text-slate-500">
                                Perbaiki data dasar sasaran Posyandu.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label for="nama_lengkap" class="form-label">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="nama_lengkap"
                                   name="nama_lengkap"
                                   value="{{ old('nama_lengkap', $balita->nama_lengkap) }}"
                                   required
                                   autocomplete="off"
                                   placeholder="Contoh: Aisyah Putri Ramadhani"
                                   class="form-input @error('nama_lengkap') form-error @enderror">
                            @error('nama_lengkap')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nik" class="form-label">
                                NIK Balita <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="nik"
                                   name="nik"
                                   value="{{ old('nik', $balita->nik) }}"
                                   required
                                   maxlength="16"
                                   inputmode="numeric"
                                   pattern="[0-9]{16}"
                                   autocomplete="off"
                                   placeholder="16 digit NIK Balita"
                                   class="form-input @error('nik') form-error @enderror">
                            @error('nik')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">
                                Jenis Kelamin <span class="text-rose-500">*</span>
                            </label>

                            <div class="grid grid-cols-2 gap-3">
                                <label class="gender-card">
                                    <input type="radio"
                                           name="jenis_kelamin"
                                           value="L"
                                           class="peer sr-only"
                                           {{ $selectedGender === 'L' ? 'checked' : '' }}
                                           required>
                                    <span class="gender-dot bg-cyan-50 text-cyan-700 peer-checked:bg-cyan-600 peer-checked:text-white">
                                        L
                                    </span>
                                    <span>
                                        <span class="block text-sm font-black text-slate-800">Laki-laki</span>
                                        <span class="block text-xs font-bold text-slate-400">Kode L</span>
                                    </span>
                                </label>

                                <label class="gender-card">
                                    <input type="radio"
                                           name="jenis_kelamin"
                                           value="P"
                                           class="peer sr-only"
                                           {{ $selectedGender === 'P' ? 'checked' : '' }}
                                           required>
                                    <span class="gender-dot bg-rose-50 text-rose-600 peer-checked:bg-rose-500 peer-checked:text-white">
                                        P
                                    </span>
                                    <span>
                                        <span class="block text-sm font-black text-slate-800">Perempuan</span>
                                        <span class="block text-xs font-bold text-slate-400">Kode P</span>
                                    </span>
                                </label>
                            </div>

                            @error('jenis_kelamin')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tempat_lahir" class="form-label">
                                Tempat Lahir <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="tempat_lahir"
                                   name="tempat_lahir"
                                   value="{{ old('tempat_lahir', $balita->tempat_lahir) }}"
                                   required
                                   autocomplete="off"
                                   placeholder="Contoh: Pekalongan"
                                   class="form-input @error('tempat_lahir') form-error @enderror">
                            @error('tempat_lahir')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal_lahir" class="form-label">
                                Tanggal Lahir <span class="text-rose-500">*</span>
                            </label>
                            <input type="date"
                                   id="tanggal_lahir"
                                   name="tanggal_lahir"
                                   value="{{ $tanggalLahir }}"
                                   max="{{ now()->format('Y-m-d') }}"
                                   required
                                   class="form-input @error('tanggal_lahir') form-error @enderror">
                            @error('tanggal_lahir')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- KELUARGA --}}
                <section class="rounded-[28px] border border-cyan-100/80 bg-white/72 p-5 shadow-[0_16px_50px_rgba(15,23,42,0.04)] sm:p-6">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-100 text-sm font-black text-cyan-700">
                            02
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-950">
                                Orang Tua dan Domisili
                            </h3>
                            <p class="text-sm font-semibold text-slate-500">
                                Data pendukung identitas keluarga.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div>
                            <label for="nama_ibu" class="form-label">
                                Nama Ibu <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="nama_ibu"
                                   name="nama_ibu"
                                   value="{{ old('nama_ibu', $balita->nama_ibu) }}"
                                   required
                                   autocomplete="off"
                                   placeholder="Nama ibu kandung"
                                   class="form-input @error('nama_ibu') form-error @enderror">
                            @error('nama_ibu')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nama_ayah" class="form-label">
                                Nama Ayah
                            </label>
                            <input type="text"
                                   id="nama_ayah"
                                   name="nama_ayah"
                                   value="{{ old('nama_ayah', $balita->nama_ayah) }}"
                                   autocomplete="off"
                                   placeholder="Opsional"
                                   class="form-input @error('nama_ayah') form-error @enderror">
                            @error('nama_ayah')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label for="alamat" class="form-label">
                                Alamat Tinggal <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="alamat"
                                      name="alamat"
                                      rows="4"
                                      required
                                      placeholder="Tulis alamat lengkap Balita"
                                      class="form-textarea @error('alamat') form-error @enderror">{{ old('alamat', $balita->alamat) }}</textarea>
                            @error('alamat')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- DATA LAHIR --}}
                <section class="rounded-[28px] border border-amber-100/90 bg-white/72 p-5 shadow-[0_16px_50px_rgba(15,23,42,0.04)] sm:p-6">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-100 text-sm font-black text-amber-700">
                            03
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-950">
                                Data Lahir
                            </h3>
                            <p class="text-sm font-semibold text-slate-500">
                                Lengkapi jika data tersedia.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div>
                            <label for="berat_lahir" class="form-label">
                                Berat Lahir
                            </label>
                            <div class="relative">
                                <input type="number"
                                       id="berat_lahir"
                                       name="berat_lahir"
                                       value="{{ old('berat_lahir', $balita->berat_lahir) }}"
                                       step="0.01"
                                       min="0"
                                       max="20"
                                       placeholder="Contoh: 3.20"
                                       class="form-input pr-14 @error('berat_lahir') form-error @enderror">
                                <span class="absolute inset-y-0 right-4 flex items-center text-sm font-black text-slate-400">
                                    kg
                                </span>
                            </div>
                            @error('berat_lahir')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="panjang_lahir" class="form-label">
                                Panjang Lahir
                            </label>
                            <div class="relative">
                                <input type="number"
                                       id="panjang_lahir"
                                       name="panjang_lahir"
                                       value="{{ old('panjang_lahir', $balita->panjang_lahir) }}"
                                       step="0.1"
                                       min="0"
                                       max="100"
                                       placeholder="Contoh: 49.0"
                                       class="form-input pr-14 @error('panjang_lahir') form-error @enderror">
                                <span class="absolute inset-y-0 right-4 flex items-center text-sm font-black text-slate-400">
                                    cm
                                </span>
                            </div>
                            @error('panjang_lahir')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- ACTION --}}
            <div class="border-t border-emerald-100/80 bg-gradient-to-r from-white/80 via-emerald-50/70 to-amber-50/45 px-6 py-5 sm:px-8">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-bold text-slate-500">
                        Simpan hanya jika data sudah sesuai catatan Posyandu.
                    </p>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <a href="{{ $backRoute }}"
                           class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </a>

                        <button type="submit"
                                class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-700 px-7 text-sm font-black text-white shadow-[0_14px_35px_rgba(4,120,87,0.24)] transition hover:bg-emerald-800">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('modals')
<div id="nexusEditConfirm"
     class="nexus-modal fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm">
    <div class="nexus-modal-card w-full max-w-md overflow-hidden rounded-[32px] border border-white/70 bg-white/90 shadow-[0_30px_100px_rgba(15,23,42,0.28)] backdrop-blur-2xl">
        <div class="relative overflow-hidden bg-gradient-to-br from-emerald-950 via-emerald-800 to-teal-700 px-6 py-6 text-white">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-16 left-8 h-28 w-44 rounded-t-[80px] bg-amber-300/15"></div>

            <div class="relative">
                <div class="mb-4 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-50">
                    Konfirmasi Edit
                </div>

                <h3 class="text-2xl font-black tracking-tight">
                    Simpan Perubahan Data?
                </h3>

                <p class="mt-2 text-sm font-semibold leading-6 text-white/75">
                    Data Balita akan diperbarui dan sistem akan mengecek ulang sinkronisasi akun warga berdasarkan NIK.
                </p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white via-emerald-50/50 to-amber-50/35 px-6 py-5">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold leading-6 text-amber-800">
                Pastikan NIK, nama, tanggal lahir, dan alamat sudah benar sebelum menyimpan.
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <button type="button"
                        id="nexusEditCancel"
                        class="h-12 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="button"
                        id="nexusEditOk"
                        class="h-12 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-[0_14px_35px_rgba(4,120,87,0.22)] transition hover:bg-emerald-800">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('balitaEditForm');
        const nikInput = document.getElementById('nik');

        const modal = document.getElementById('nexusEditConfirm');
        const cancelButton = document.getElementById('nexusEditCancel');
        const okButton = document.getElementById('nexusEditOk');

        let confirmedSubmit = false;

        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        function openModal() {
            modal?.classList.add('is-open');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            modal?.classList.remove('is-open');
            document.body.classList.remove('overflow-hidden');
        }

        nikInput?.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 16);
        });

        form?.addEventListener('submit', function (event) {
            if (confirmedSubmit) {
                return;
            }

            event.preventDefault();
            openModal();
        });

        cancelButton?.addEventListener('click', closeModal);

        modal?.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                closeModal();
            }
        });

        okButton?.addEventListener('click', function () {
            confirmedSubmit = true;
            closeModal();
            HTMLFormElement.prototype.submit.call(form);
        });
    });
</script>
@endpush