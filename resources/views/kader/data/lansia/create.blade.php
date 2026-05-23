@extends('layouts.kader')

@section('title', 'Tambah Data Lansia')
@section('page-name', 'Tambah Data Lansia')

@php
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $kemandirianOptions = [
        'mandiri' => 'Mandiri',
        'bantuan_sebagian' => 'Membutuhkan Bantuan Sebagian',
        'ketergantungan_penuh' => 'Ketergantungan Penuh',
    ];
@endphp

@push('styles')
<style>
    .lansia-create-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .lansia-create-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.13), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(245,158,11,.10), transparent 26%),
            radial-gradient(circle at 50% 100%, rgba(14,165,233,.08), transparent 32%),
            linear-gradient(135deg, #f8fffc 0%, #f8fafc 58%, #fffaf0 100%);
    }

    .glass-panel {
        border: 1px solid rgba(255,255,255,.78);
        background: rgba(255,255,255,.64);
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .hero-panel {
        border: 1px solid rgba(167,243,208,.72);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.16), transparent 32%),
            radial-gradient(circle at 88% 16%, rgba(245,158,11,.13), transparent 32%),
            linear-gradient(135deg, rgba(255,255,255,.72), rgba(236,253,245,.70));
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .input-premium {
        border: 1px solid rgba(226,232,240,.9);
        background: rgba(255,255,255,.72);
        outline: none;
        transition: all .3s ease-in-out;
    }

    .input-premium:focus {
        border-color: rgba(16,185,129,.42);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
        background: rgba(255,255,255,.86);
    }

    .input-error {
        border-color: rgba(244,63,94,.45) !important;
        box-shadow: 0 0 0 4px rgba(244,63,94,.08) !important;
    }

    .choice-card {
        border: 1px solid rgba(226,232,240,.82);
        background: rgba(255,255,255,.58);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .choice-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 18px 38px rgba(15,23,42,.06);
    }

    .choice-card.active {
        border-color: rgba(16,185,129,.42);
        background: rgba(236,253,245,.86);
        box-shadow: 0 14px 32px rgba(5,150,105,.08);
    }

    .imt-preview {
        border: 1px solid rgba(226,232,240,.82);
        background: rgba(255,255,255,.58);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .toast-custom {
        position: fixed;
        right: 24px;
        top: 96px;
        z-index: 90;
        width: min(420px, calc(100vw - 32px));
        opacity: 0;
        pointer-events: none;
        transform: translateY(-10px);
        transition: all .3s ease-in-out;
    }

    .toast-custom.show {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }

    @media (max-width: 640px) {
        .toast-custom {
            left: 16px;
            right: 16px;
            top: 82px;
        }
    }
</style>
@endpush

@section('content')
<div class="lansia-create-page space-y-5">

    <div id="customToast" class="toast-custom">
        <div class="rounded-[24px] border border-rose-100 bg-white/80 p-4 shadow-[0_22px_60px_rgba(15,23,42,.22)] backdrop-blur-xl">
            <div class="flex gap-3">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-rose-50 text-rose-600">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div>
                    <p class="text-sm font-black text-slate-900">Form belum lengkap</p>
                    <p id="customToastText" class="mt-1 text-xs font-bold leading-5 text-slate-500">
                        Lengkapi data wajib terlebih dahulu.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-person-cane"></i>
                    Input Data Lansia
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Tambah Data Lansia
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Masukkan data Lansia sebagai sasaran layanan Posyandu. Data ini digunakan untuk absensi, pengukuran fisik, pemantauan kesehatan, rekam medis, dan laporan.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.data.lansia.index'))
                    <a href="{{ route('kader.data.lansia.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                @endif

                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create', ['type' => 'lansia']) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(15,23,42,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-slate-800">
                        <i class="fa-solid fa-file-import"></i>
                        Import Excel
                    </a>
                @endif
            </div>
        </div>
    </section>

    @if($errors->any() || session('error'))
        <section class="rounded-[24px] border border-rose-100 bg-rose-50/80 p-4 text-sm font-bold text-rose-700">
            <div class="mb-2 flex items-center gap-2 font-black">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Data belum bisa disimpan
            </div>

            @if(session('error'))
                <p class="leading-6">{{ session('error') }}</p>
            @endif

            @if($errors->any())
                <ul class="ml-5 list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    <form id="lansiaForm" method="POST" action="{{ route('kader.data.lansia.store') }}" class="space-y-5" novalidate>
        @csrf

        {{-- 1. IDENTITAS LANSIA --}}
        <section class="glass-panel rounded-[30px] p-4 sm:p-5">
            <div class="mb-5 flex items-start gap-3">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                    <i class="fa-solid fa-id-card"></i>
                </div>

                <div>
                    <h2 class="text-lg font-black text-slate-900">1. Identitas Lansia</h2>
                    <p class="mt-1 text-xs font-bold leading-5 text-slate-400">
                        Data utama Lansia untuk pendataan sasaran dan sinkronisasi akun warga.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        NIK Lansia
                    </label>
                    <input
                        type="text"
                        name="nik"
                        id="nik"
                        value="{{ old('nik') }}"
                        class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('nik') input-error @enderror"
                        placeholder="16 digit NIK, boleh dikosongkan"
                        inputmode="numeric"
                        maxlength="16"
                        autocomplete="off"
                    >
                    @error('nik')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs font-bold text-slate-400">
                        NIK digunakan untuk sinkron akun warga jika tersedia.
                    </p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_lengkap"
                        id="nama_lengkap"
                        value="{{ old('nama_lengkap') }}"
                        class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('nama_lengkap') input-error @enderror"
                        placeholder="Contoh: Siti Aminah"
                        autocomplete="off"
                        required
                    >
                    @error('nama_lengkap')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Tempat Lahir <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="tempat_lahir"
                        id="tempat_lahir"
                        value="{{ old('tempat_lahir') }}"
                        class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('tempat_lahir') input-error @enderror"
                        placeholder="Contoh: Pekalongan"
                        autocomplete="off"
                        required
                    >
                    @error('tempat_lahir')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Tanggal Lahir <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="tanggal_lahir"
                        id="tanggal_lahir"
                        value="{{ old('tanggal_lahir') }}"
                        max="{{ now('Asia/Jakarta')->subYears(45)->toDateString() }}"
                        class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('tanggal_lahir') input-error @enderror"
                        required
                    >
                    @error('tanggal_lahir')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                    <p id="usiaPreview" class="mt-2 text-xs font-bold text-slate-400">
                        Usia akan dihitung otomatis setelah tanggal lahir dipilih.
                    </p>
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-3 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="choice-card {{ old('jenis_kelamin') === 'L' ? 'active' : '' }} cursor-pointer rounded-[24px] p-4" data-gender-card="L">
                        <input type="radio" name="jenis_kelamin" value="L" class="sr-only gender-radio" {{ old('jenis_kelamin') === 'L' ? 'checked' : '' }}>

                        <div class="flex items-center gap-3">
                            <div class="grid h-11 w-11 place-items-center rounded-2xl bg-sky-50 text-sky-700">
                                <i class="fa-solid fa-mars"></i>
                            </div>

                            <div>
                                <p class="text-sm font-black text-slate-900">Laki-laki</p>
                                <p class="mt-1 text-xs font-bold text-slate-400">Kode: L</p>
                            </div>
                        </div>
                    </label>

                    <label class="choice-card {{ old('jenis_kelamin') === 'P' ? 'active' : '' }} cursor-pointer rounded-[24px] p-4" data-gender-card="P">
                        <input type="radio" name="jenis_kelamin" value="P" class="sr-only gender-radio" {{ old('jenis_kelamin') === 'P' ? 'checked' : '' }}>

                        <div class="flex items-center gap-3">
                            <div class="grid h-11 w-11 place-items-center rounded-2xl bg-pink-50 text-pink-700">
                                <i class="fa-solid fa-venus"></i>
                            </div>

                            <div>
                                <p class="text-sm font-black text-slate-900">Perempuan</p>
                                <p class="mt-1 text-xs font-bold text-slate-400">Kode: P</p>
                            </div>
                        </div>
                    </label>
                </div>

                @error('jenis_kelamin')
                    <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-5">
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                    Alamat Tinggal <span class="text-rose-500">*</span>
                </label>
                <textarea
                    name="alamat"
                    id="alamat"
                    rows="4"
                    class="input-premium w-full rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 @error('alamat') input-error @enderror"
                    placeholder="Contoh: Dusun Krajan RT 01 RW 02"
                    required
                >{{ old('alamat') }}</textarea>
                @error('alamat')
                    <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

        {{-- 2. PEMERIKSAAN KESEHATAN DASAR --}}
        <section class="glass-panel rounded-[30px] p-4 sm:p-5">
            <div class="mb-5 flex items-start gap-3">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-sky-50/90 text-sky-700">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>

                <div>
                    <h2 class="text-lg font-black text-slate-900">2. Pemeriksaan Kesehatan Dasar</h2>
                    <p class="mt-1 text-xs font-bold leading-5 text-slate-400">
                        Data awal kesehatan Lansia. Pemeriksaan berkala tetap dicatat melalui fitur Pengukuran Fisik.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Tingkat Kemandirian
                    </label>
                    <select
                        name="tingkat_kemandirian"
                        id="tingkat_kemandirian"
                        class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('tingkat_kemandirian') input-error @enderror"
                    >
                        <option value="">Pilih tingkat kemandirian</option>
                        @foreach($kemandirianOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('tingkat_kemandirian') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tingkat_kemandirian')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Berat Badan
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            name="berat_badan"
                            id="berat_badan"
                            value="{{ old('berat_badan') }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700 @error('berat_badan') input-error @enderror"
                            placeholder="Contoh: 58"
                            min="1"
                            max="300"
                            step="0.1"
                        >
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">kg</span>
                    </div>
                    @error('berat_badan')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Tinggi Badan
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            name="tinggi_badan"
                            id="tinggi_badan"
                            value="{{ old('tinggi_badan') }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700 @error('tinggi_badan') input-error @enderror"
                            placeholder="Contoh: 158"
                            min="50"
                            max="250"
                            step="0.1"
                        >
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                    </div>
                    @error('tinggi_badan')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-3">
                    <div id="imtPreview" class="imt-preview rounded-[24px] p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-3">
                                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                                    <i class="fa-solid fa-calculator"></i>
                                </div>

                                <div>
                                    <p class="text-sm font-black text-slate-900">Preview IMT Otomatis</p>
                                    <p id="imtText" class="mt-1 text-xs font-bold leading-5 text-slate-400">
                                        Isi berat dan tinggi badan untuk melihat estimasi IMT.
                                    </p>
                                </div>
                            </div>

                            <div id="imtBadge" class="hidden rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3 text-center">
                                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">IMT</p>
                                <p id="imtValue" class="mt-1 text-xl font-black text-slate-900">-</p>
                            </div>
                        </div>
                    </div>

                    <p class="mt-2 text-xs font-bold text-slate-400">
                        Nilai IMT final tetap dihitung ulang oleh server saat data disimpan.
                    </p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Tekanan Darah / Tensi
                    </label>
                    <input
                        type="text"
                        name="tekanan_darah"
                        id="tekanan_darah"
                        value="{{ old('tekanan_darah') }}"
                        class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('tekanan_darah') input-error @enderror"
                        placeholder="Contoh: 120/80"
                        maxlength="7"
                        autocomplete="off"
                    >
                    @error('tekanan_darah')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Lingkar Perut
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            name="lingkar_perut"
                            id="lingkar_perut"
                            value="{{ old('lingkar_perut') }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700 @error('lingkar_perut') input-error @enderror"
                            placeholder="Contoh: 85"
                            min="20"
                            max="200"
                            step="0.1"
                        >
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                    </div>
                    @error('lingkar_perut')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Gula Darah
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            name="gula_darah"
                            id="gula_darah"
                            value="{{ old('gula_darah') }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 pr-16 text-sm font-bold text-slate-700 @error('gula_darah') input-error @enderror"
                            placeholder="Contoh: 120"
                            min="0"
                            max="999"
                            step="0.1"
                        >
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">mg/dL</span>
                    </div>
                    @error('gula_darah')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Kolesterol
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            name="kolesterol"
                            id="kolesterol"
                            value="{{ old('kolesterol') }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 pr-16 text-sm font-bold text-slate-700 @error('kolesterol') input-error @enderror"
                            placeholder="Contoh: 180"
                            min="0"
                            max="999"
                            step="0.1"
                        >
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">mg/dL</span>
                    </div>
                    @error('kolesterol')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Asam Urat
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            name="asam_urat"
                            id="asam_urat"
                            value="{{ old('asam_urat') }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 pr-16 text-sm font-bold text-slate-700 @error('asam_urat') input-error @enderror"
                            placeholder="Contoh: 6.5"
                            min="0"
                            max="99"
                            step="0.1"
                        >
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">mg/dL</span>
                    </div>
                    @error('asam_urat')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- 3. RIWAYAT DAN KELUHAN --}}
        <section class="glass-panel rounded-[30px] p-4 sm:p-5">
            <div class="mb-5 flex items-start gap-3">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-rose-50/90 text-rose-700">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>

                <div>
                    <h2 class="text-lg font-black text-slate-900">3. Riwayat dan Keluhan</h2>
                    <p class="mt-1 text-xs font-bold leading-5 text-slate-400">
                        Catatan awal untuk membantu pemantauan kondisi Lansia.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Riwayat Penyakit Bawaan
                    </label>
                    <textarea
                        name="penyakit_bawaan"
                        id="penyakit_bawaan"
                        rows="4"
                        class="input-premium w-full rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 @error('penyakit_bawaan') input-error @enderror"
                        placeholder="Contoh: Hipertensi, diabetes, asam urat. Kosongkan jika tidak ada."
                    >{{ old('penyakit_bawaan') }}</textarea>
                    @error('penyakit_bawaan')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Keluhan Saat Ini
                    </label>
                    <textarea
                        name="keluhan"
                        id="keluhan"
                        rows="4"
                        class="input-premium w-full rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 @error('keluhan') input-error @enderror"
                        placeholder="Contoh: Sering pusing, nyeri lutut, mudah lelah. Kosongkan jika tidak ada."
                    >{{ old('keluhan') }}</textarea>
                    @error('keluhan')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="glass-panel rounded-[26px] p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-900">Simpan Data Lansia</h3>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        Setelah disimpan, data masuk ke database Lansia dan dapat digunakan pada fitur layanan Posyandu.
                    </p>
                </div>

                <button type="submit"
                        id="submitBtn"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Lansia
                </button>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('lansiaForm');
    const submitBtn = document.getElementById('submitBtn');
    const toast = document.getElementById('customToast');
    const toastText = document.getElementById('customToastText');

    const genderCards = document.querySelectorAll('[data-gender-card]');
    const genderRadios = document.querySelectorAll('.gender-radio');

    const tanggalLahir = document.getElementById('tanggal_lahir');
    const usiaPreview = document.getElementById('usiaPreview');

    const nikInput = document.getElementById('nik');
    const beratInput = document.getElementById('berat_badan');
    const tinggiInput = document.getElementById('tinggi_badan');
    const tekananDarahInput = document.getElementById('tekanan_darah');

    const lingkarPerutInput = document.getElementById('lingkar_perut');
    const gulaDarahInput = document.getElementById('gula_darah');
    const kolesterolInput = document.getElementById('kolesterol');
    const asamUratInput = document.getElementById('asam_urat');

    const imtText = document.getElementById('imtText');
    const imtBadge = document.getElementById('imtBadge');
    const imtValue = document.getElementById('imtValue');

    let toastTimer = null;

    const showToast = (message) => {
        toastText.textContent = message;
        toast.classList.add('show');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3600);
    };

    const setError = (el) => {
        if (el) el.classList.add('input-error');
    };

    const clearError = (el) => {
        if (el) el.classList.remove('input-error');
    };

    const updateGenderUI = () => {
        const selected = document.querySelector('.gender-radio:checked')?.value;

        genderCards.forEach(card => {
            card.classList.toggle('active', card.dataset.genderCard === selected);
        });
    };

    genderRadios.forEach(radio => {
        radio.addEventListener('change', updateGenderUI);
    });

    nikInput?.addEventListener('input', () => {
        nikInput.value = nikInput.value.replace(/\D/g, '').slice(0, 16);
        clearError(nikInput);
    });

    tekananDarahInput?.addEventListener('input', () => {
        tekananDarahInput.value = tekananDarahInput.value.replace(/[^\d/]/g, '').slice(0, 7);
        clearError(tekananDarahInput);
    });

    const sanitizeDecimal = (input, maxLength = 6) => {
        input?.addEventListener('input', () => {
            input.value = input.value
                .replace(/,/g, '.')
                .replace(/[^\d.]/g, '')
                .replace(/(\..*)\./g, '$1')
                .slice(0, maxLength);

            clearError(input);
        });
    };

    sanitizeDecimal(beratInput, 6);
    sanitizeDecimal(tinggiInput, 6);
    sanitizeDecimal(lingkarPerutInput, 6);
    sanitizeDecimal(gulaDarahInput, 6);
    sanitizeDecimal(kolesterolInput, 6);
    sanitizeDecimal(asamUratInput, 5);

    const calculateAge = (dateValue) => {
        if (!dateValue) return null;

        const birthDate = new Date(dateValue + 'T00:00:00');
        const today = new Date();

        if (birthDate > today) return 'future';

        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();

        if (today.getDate() < birthDate.getDate()) {
            years -= 1;
            months += 12;
        }

        return { years, months };
    };

    const updateAgePreview = () => {
        const result = calculateAge(tanggalLahir.value);

        clearError(tanggalLahir);

        if (result === null) {
            usiaPreview.textContent = 'Usia akan dihitung otomatis setelah tanggal lahir dipilih.';
            usiaPreview.className = 'mt-2 text-xs font-bold text-slate-400';
            return;
        }

        if (result === 'future') {
            usiaPreview.textContent = 'Tanggal lahir tidak boleh melebihi hari ini.';
            usiaPreview.className = 'mt-2 text-xs font-bold text-rose-600';
            setError(tanggalLahir);
            return;
        }

        if (result.years < 45) {
            usiaPreview.textContent = `Usia terdeteksi ${result.years} tahun ${result.months} bulan. Lansia minimal 45 tahun.`;
            usiaPreview.className = 'mt-2 text-xs font-bold text-rose-600';
            setError(tanggalLahir);
            return;
        }

        usiaPreview.textContent = `Perkiraan usia: ${result.years} tahun ${result.months} bulan.`;
        usiaPreview.className = 'mt-2 text-xs font-bold text-emerald-600';
    };

    const updateImtPreview = () => {
        const berat = parseFloat(beratInput.value);
        const tinggi = parseFloat(tinggiInput.value);

        clearError(beratInput);
        clearError(tinggiInput);

        if (!berat || !tinggi) {
            imtBadge.classList.add('hidden');
            imtText.textContent = 'Isi berat dan tinggi badan untuk melihat estimasi IMT.';
            imtText.className = 'mt-1 text-xs font-bold leading-5 text-slate-400';
            return;
        }

        if (berat < 1 || berat > 300) {
            setError(beratInput);
            imtBadge.classList.add('hidden');
            imtText.textContent = 'Berat badan harus berada pada rentang 1 sampai 300 kg.';
            imtText.className = 'mt-1 text-xs font-bold leading-5 text-rose-600';
            return;
        }

        if (tinggi < 50 || tinggi > 250) {
            setError(tinggiInput);
            imtBadge.classList.add('hidden');
            imtText.textContent = 'Tinggi badan harus berada pada rentang 50 sampai 250 cm.';
            imtText.className = 'mt-1 text-xs font-bold leading-5 text-rose-600';
            return;
        }

        const meter = tinggi / 100;
        const imt = berat / (meter * meter);
        const rounded = imt.toFixed(2);

        let label = 'Normal';
        let labelClass = 'text-emerald-600';

        if (imt < 18.5) {
            label = 'Kurus';
            labelClass = 'text-amber-600';
        } else if (imt >= 25 && imt < 30) {
            label = 'Berlebih';
            labelClass = 'text-amber-600';
        } else if (imt >= 30) {
            label = 'Obesitas';
            labelClass = 'text-rose-600';
        }

        imtBadge.classList.remove('hidden');
        imtValue.textContent = rounded;
        imtText.textContent = `Estimasi kategori IMT: ${label}.`;
        imtText.className = `mt-1 text-xs font-bold leading-5 ${labelClass}`;
    };

    tanggalLahir?.addEventListener('change', updateAgePreview);
    beratInput?.addEventListener('input', updateImtPreview);
    tinggiInput?.addEventListener('input', updateImtPreview);

    form?.addEventListener('submit', (event) => {
        const requiredFields = [
            { id: 'nama_lengkap', label: 'Nama lengkap Lansia wajib diisi.' },
            { id: 'tempat_lahir', label: 'Tempat lahir wajib diisi.' },
            { id: 'tanggal_lahir', label: 'Tanggal lahir wajib diisi.' },
            { id: 'alamat', label: 'Alamat tinggal wajib diisi.' },
        ];

        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

        for (const field of requiredFields) {
            const el = document.getElementById(field.id);

            if (!el || !String(el.value).trim()) {
                event.preventDefault();
                setError(el);
                showToast(field.label);
                el?.focus();
                return;
            }
        }

        if (nikInput.value.trim() !== '' && !/^\d{16}$/.test(nikInput.value.trim())) {
            event.preventDefault();
            setError(nikInput);
            showToast('Jika NIK Lansia diisi, NIK harus berisi tepat 16 digit angka.');
            nikInput.focus();
            return;
        }

        if (!document.querySelector('.gender-radio:checked')) {
            event.preventDefault();
            showToast('Pilih jenis kelamin Lansia terlebih dahulu.');
            return;
        }

        const ageCheck = calculateAge(tanggalLahir.value);

        if (ageCheck === 'future' || !ageCheck || ageCheck.years < 45) {
            event.preventDefault();
            setError(tanggalLahir);
            showToast('Tanggal lahir harus menunjukkan usia minimal 45 tahun.');
            tanggalLahir.focus();
            return;
        }

        if (tekananDarahInput.value.trim() !== '' && !/^\d{2,3}\/\d{2,3}$/.test(tekananDarahInput.value.trim())) {
            event.preventDefault();
            setError(tekananDarahInput);
            showToast('Format tekanan darah harus seperti 120/80.');
            tekananDarahInput.focus();
            return;
        }

        const numericChecks = [
            { el: beratInput, min: 1, max: 300, label: 'Berat badan harus berada pada rentang 1 sampai 300 kg.' },
            { el: tinggiInput, min: 50, max: 250, label: 'Tinggi badan harus berada pada rentang 50 sampai 250 cm.' },
            { el: lingkarPerutInput, min: 20, max: 200, label: 'Lingkar perut harus berada pada rentang 20 sampai 200 cm.' },
            { el: gulaDarahInput, min: 0, max: 999, label: 'Gula darah harus berada pada rentang 0 sampai 999 mg/dL.' },
            { el: kolesterolInput, min: 0, max: 999, label: 'Kolesterol harus berada pada rentang 0 sampai 999 mg/dL.' },
            { el: asamUratInput, min: 0, max: 99, label: 'Asam urat harus berada pada rentang 0 sampai 99 mg/dL.' },
        ];

        for (const item of numericChecks) {
            if (!item.el || item.el.value.trim() === '') {
                continue;
            }

            const value = parseFloat(item.el.value);

            if (Number.isNaN(value) || value < item.min || value > item.max) {
                event.preventDefault();
                setError(item.el);
                showToast(item.label);
                item.el.focus();
                return;
            }
        }

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan Data...';
    });

    updateGenderUI();
    updateAgePreview();
    updateImtPreview();
});
</script>
@endpush