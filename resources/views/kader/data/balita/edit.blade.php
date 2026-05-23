@extends('layouts.kader')

@section('title', 'Edit Data Balita')
@section('page-name', 'Edit Data Balita')

@php
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);
@endphp

@push('styles')
<style>
    .balita-edit-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .balita-edit-page::before {
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

    .gender-card {
        border: 1px solid rgba(226,232,240,.82);
        background: rgba(255,255,255,.58);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .gender-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 18px 38px rgba(15,23,42,.06);
    }

    .gender-card.active {
        border-color: rgba(16,185,129,.40);
        background: rgba(236,253,245,.86);
        box-shadow: 0 14px 32px rgba(5,150,105,.08);
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
<div class="balita-edit-page space-y-5">

    {{-- CUSTOM TOAST --}}
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

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-amber-700">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Mode Edit Data
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Edit Data Balita
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Perbarui data Balita dengan hati-hati. Perubahan NIK akan memengaruhi proses sinkronisasi akun warga dan pencarian data pada layanan Posyandu.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.data.balita.index'))
                    <a href="{{ route('kader.data.balita.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                @endif

                @if($routeHas('kader.data.balita.show'))
                    <a href="{{ route('kader.data.balita.show', $balita->id) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(15,23,42,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-slate-800">
                        <i class="fa-solid fa-eye"></i>
                        Detail
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- SERVER ERROR --}}
    @if($errors->any() || session('error'))
        <section class="rounded-[24px] border border-rose-100 bg-rose-50/80 p-4 text-sm font-bold text-rose-700">
            <div class="mb-2 flex items-center gap-2 font-black">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Data belum bisa diperbarui
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

    <form id="balitaForm" method="POST" action="{{ route('kader.data.balita.update', $balita->id) }}" class="grid grid-cols-1 gap-5 xl:grid-cols-12" novalidate>
        @csrf
        @method('PUT')

        {{-- LEFT FORM --}}
        <section class="space-y-5 xl:col-span-8">

            {{-- IDENTITAS --}}
            <div class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-5 flex items-start gap-3">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                        <i class="fa-solid fa-id-card"></i>
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-slate-900">1. Identitas Balita</h2>
                        <p class="mt-1 text-xs font-bold leading-5 text-slate-400">
                            Pastikan perubahan identitas sesuai data keluarga atau catatan Posyandu.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nama_lengkap"
                            id="nama_lengkap"
                            value="{{ old('nama_lengkap', $balita->nama_lengkap) }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('nama_lengkap') input-error @enderror"
                            placeholder="Contoh: Ahmad Fauzan"
                            autocomplete="off"
                            required
                        >
                        @error('nama_lengkap')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                            NIK Balita <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nik"
                            id="nik"
                            value="{{ old('nik', $balita->nik) }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('nik') input-error @enderror"
                            placeholder="16 digit NIK"
                            inputmode="numeric"
                            maxlength="16"
                            autocomplete="off"
                            required
                        >
                        @error('nik')
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
                            value="{{ old('tempat_lahir', $balita->tempat_lahir) }}"
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
                            value="{{ old('tanggal_lahir', optional($balita->tanggal_lahir)->format('Y-m-d') ?? $balita->tanggal_lahir) }}"
                            max="{{ now('Asia/Jakarta')->toDateString() }}"
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
                        <label class="gender-card {{ old('jenis_kelamin', $balita->jenis_kelamin) === 'L' ? 'active' : '' }} cursor-pointer rounded-[24px] p-4" data-gender-card="L">
                            <input type="radio" name="jenis_kelamin" value="L" class="sr-only gender-radio" {{ old('jenis_kelamin', $balita->jenis_kelamin) === 'L' ? 'checked' : '' }}>
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

                        <label class="gender-card {{ old('jenis_kelamin', $balita->jenis_kelamin) === 'P' ? 'active' : '' }} cursor-pointer rounded-[24px] p-4" data-gender-card="P">
                            <input type="radio" name="jenis_kelamin" value="P" class="sr-only gender-radio" {{ old('jenis_kelamin', $balita->jenis_kelamin) === 'P' ? 'checked' : '' }}>
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
            </div>

            {{-- ORANG TUA --}}
            <div class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-5 flex items-start gap-3">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-amber-50/90 text-amber-700">
                        <i class="fa-solid fa-people-roof"></i>
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-slate-900">2. Orang Tua dan Domisili</h2>
                        <p class="mt-1 text-xs font-bold leading-5 text-slate-400">
                            Data keluarga dipakai untuk identifikasi sasaran dan pencarian data.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                            Nama Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nama_ibu"
                            id="nama_ibu"
                            value="{{ old('nama_ibu', $balita->nama_ibu) }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('nama_ibu') input-error @enderror"
                            placeholder="Nama ibu kandung / wali"
                            autocomplete="off"
                            required
                        >
                        @error('nama_ibu')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                            Nama Ayah
                        </label>
                        <input
                            type="text"
                            name="nama_ayah"
                            id="nama_ayah"
                            value="{{ old('nama_ayah', $balita->nama_ayah) }}"
                            class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700 @error('nama_ayah') input-error @enderror"
                            placeholder="Boleh dikosongkan"
                            autocomplete="off"
                        >
                        @error('nama_ayah')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
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
                        >{{ old('alamat', $balita->alamat) }}</textarea>
                        @error('alamat')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- DATA LAHIR --}}
            <div class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-5 flex items-start gap-3">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-sky-50/90 text-sky-700">
                        <i class="fa-solid fa-weight-scale"></i>
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-slate-900">3. Data Lahir</h2>
                        <p class="mt-1 text-xs font-bold leading-5 text-slate-400">
                            Lengkapi atau koreksi data lahir jika tersedia.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                            Berat Lahir
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                name="berat_lahir"
                                id="berat_lahir"
                                value="{{ old('berat_lahir', $balita->berat_lahir) }}"
                                class="input-premium h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700 @error('berat_lahir') input-error @enderror"
                                placeholder="Contoh: 3.2"
                                min="0"
                                step="0.01"
                            >
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">kg</span>
                        </div>
                        @error('berat_lahir')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                            Panjang Lahir
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                name="panjang_lahir"
                                id="panjang_lahir"
                                value="{{ old('panjang_lahir', $balita->panjang_lahir) }}"
                                class="input-premium h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700 @error('panjang_lahir') input-error @enderror"
                                placeholder="Contoh: 49"
                                min="0"
                                step="0.01"
                            >
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                        </div>
                        @error('panjang_lahir')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- RIGHT PANEL --}}
        <aside class="space-y-5 xl:col-span-4">
            <section class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                        <i class="fa-solid fa-link"></i>
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-slate-900">Status Akun</h2>
                        <p class="mt-1 text-xs font-bold text-slate-400">
                            Sinkronisasi warga.
                        </p>
                    </div>
                </div>

                @if($balita->user_id)
                    <div class="rounded-[24px] border border-emerald-100 bg-emerald-50/80 p-4">
                        <div class="flex items-start gap-3">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70 text-emerald-700">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-emerald-800">Akun Terhubung</p>
                                <p class="mt-1 text-xs font-bold leading-5 text-emerald-700">
                                    Data Balita sudah terhubung dengan akun warga.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-[24px] border border-amber-100 bg-amber-50/80 p-4">
                        <div class="flex items-start gap-3">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70 text-amber-700">
                                <i class="fa-solid fa-circle-exclamation"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-amber-800">Belum Terhubung</p>
                                <p class="mt-1 text-xs font-bold leading-5 text-amber-700">
                                    Setelah data disimpan, sistem akan mencoba mencocokkan akun berdasarkan NIK Balita.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </section>

            <section class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-50/90 text-sky-700">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-slate-900">Validasi Edit</h2>
                        <p class="mt-1 text-xs font-bold text-slate-400">
                            Perubahan tetap dicek backend.
                        </p>
                    </div>
                </div>

                <div class="space-y-3 text-xs font-bold text-slate-500">
                    <div class="flex gap-3 rounded-2xl bg-slate-50/70 p-3">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                        <p>NIK Balita wajib 16 digit dan tidak boleh sama dengan data lain.</p>
                    </div>

                    <div class="flex gap-3 rounded-2xl bg-slate-50/70 p-3">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                        <p>Tanggal lahir tidak boleh melebihi hari ini.</p>
                    </div>

                    <div class="flex gap-3 rounded-2xl bg-slate-50/70 p-3">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                        <p>Nama lengkap, nama ibu, dan alamat tetap wajib diisi.</p>
                    </div>

                    <div class="flex gap-3 rounded-2xl bg-slate-50/70 p-3">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                        <p>Sinkronisasi akun diperbarui ulang setelah penyimpanan.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[30px] border border-amber-100 bg-amber-50/70 p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70 text-amber-700">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>

                    <div>
                        <h3 class="text-sm font-black text-amber-800">Catatan Edit</h3>
                        <p class="mt-1 text-xs font-bold leading-5 text-amber-700">
                            Perubahan data identitas sebaiknya dilakukan hanya jika ada kesalahan input atau pembaruan data resmi.
                        </p>
                    </div>
                </div>
            </section>
        </aside>

        {{-- ACTION --}}
        <section class="glass-panel rounded-[26px] p-4 xl:col-span-12">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-900">Simpan Perubahan Data Balita</h3>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        Data akan diperbarui dan sinkronisasi akun warga akan dicek ulang.
                    </p>
                </div>

                <button type="submit"
                        id="submitBtn"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Perubahan
                </button>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('balitaForm');
    const submitBtn = document.getElementById('submitBtn');
    const toast = document.getElementById('customToast');
    const toastText = document.getElementById('customToastText');
    const genderCards = document.querySelectorAll('[data-gender-card]');
    const genderRadios = document.querySelectorAll('.gender-radio');
    const tanggalLahir = document.getElementById('tanggal_lahir');
    const usiaPreview = document.getElementById('usiaPreview');
    const nikInput = document.getElementById('nik');

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

    const calculateAgeMonths = (dateValue) => {
        if (!dateValue) return null;

        const birthDate = new Date(dateValue + 'T00:00:00');
        const today = new Date();

        if (birthDate > today) return 'future';

        let months = (today.getFullYear() - birthDate.getFullYear()) * 12;
        months += today.getMonth() - birthDate.getMonth();

        if (today.getDate() < birthDate.getDate()) {
            months -= 1;
        }

        return Math.max(0, months);
    };

    const updateAgePreview = () => {
        const result = calculateAgeMonths(tanggalLahir.value);

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

        const tahun = Math.floor(result / 12);
        const bulan = result % 12;

        usiaPreview.textContent = tahun > 0
            ? `Perkiraan usia: ${tahun} tahun ${bulan} bulan.`
            : `Perkiraan usia: ${bulan} bulan.`;

        usiaPreview.className = 'mt-2 text-xs font-bold text-emerald-600';
    };

    tanggalLahir?.addEventListener('change', updateAgePreview);

    form?.addEventListener('submit', (event) => {
        const requiredFields = [
            { id: 'nama_lengkap', label: 'Nama lengkap Balita wajib diisi.' },
            { id: 'nik', label: 'NIK Balita wajib diisi 16 digit.' },
            { id: 'tempat_lahir', label: 'Tempat lahir wajib diisi.' },
            { id: 'tanggal_lahir', label: 'Tanggal lahir wajib diisi.' },
            { id: 'nama_ibu', label: 'Nama ibu wajib diisi.' },
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

        if (!/^\d{16}$/.test(nikInput.value.trim())) {
            event.preventDefault();
            setError(nikInput);
            showToast('NIK Balita harus berisi tepat 16 digit angka.');
            nikInput.focus();
            return;
        }

        if (!document.querySelector('.gender-radio:checked')) {
            event.preventDefault();
            showToast('Pilih jenis kelamin Balita terlebih dahulu.');
            return;
        }

        const ageCheck = calculateAgeMonths(tanggalLahir.value);

        if (ageCheck === 'future') {
            event.preventDefault();
            setError(tanggalLahir);
            showToast('Tanggal lahir tidak boleh melebihi hari ini.');
            tanggalLahir.focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan Perubahan...';
    });

    updateGenderUI();
    updateAgePreview();
});
</script>
@endpush