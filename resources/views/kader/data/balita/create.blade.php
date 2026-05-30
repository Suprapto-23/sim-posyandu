@extends('layouts.kader')

@section('title', 'Tambah Data Balita')
@section('page-name', 'Tambah Data Balita')

@php
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $sessionType = session('success') ? 'success' : (session('warning') ? 'warning' : (session('error') ? 'error' : null));
    $sessionMessage = session('success') ?? session('warning') ?? session('error');

    $jenisKelamin = old('jenis_kelamin', '');
@endphp

@section('content')
    <style>
        .nexus-toast-show {
            animation: nexusToastShow .22s ease-out both;
        }

        .nexus-toast-hide {
            animation: nexusToastHide .18s ease-in both;
        }

        @keyframes nexusToastShow {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes nexusToastHide {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateY(-10px) scale(.985);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .nexus-toast-show,
            .nexus-toast-hide {
                animation: none !important;
            }
        }
    </style>

    <div class="w-full space-y-5">

        {{-- TOAST SESSION --}}
        @if($sessionType && $sessionMessage)
            <div
                id="nexusSessionToast"
                class="fixed right-4 top-4 z-[100000] w-[calc(100%-2rem)] max-w-md nexus-toast-show"
            >
                <div
                    class="overflow-hidden rounded-3xl border bg-white shadow-2xl shadow-slate-900/10
                    {{ $sessionType === 'success' ? 'border-emerald-100' : ($sessionType === 'warning' ? 'border-amber-100' : 'border-rose-100') }}"
                >
                    <div class="flex items-start gap-3 p-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border
                            {{ $sessionType === 'success' ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : ($sessionType === 'warning' ? 'border-amber-100 bg-amber-50 text-amber-700' : 'border-rose-100 bg-rose-50 text-rose-700') }}"
                        >
                            <i class="ph-fill {{ $sessionType === 'success' ? 'ph-check-circle' : ($sessionType === 'warning' ? 'ph-warning-circle' : 'ph-x-circle') }} text-2xl"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm font-black
                                {{ $sessionType === 'success' ? 'text-emerald-800' : ($sessionType === 'warning' ? 'text-amber-800' : 'text-rose-800') }}"
                            >
                                {{ $sessionType === 'success' ? 'Berhasil Diproses' : ($sessionType === 'warning' ? 'Perhatian Sistem' : 'Aksi Gagal') }}
                            </p>

                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                                {{ $sessionMessage }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="nexus-toast-close flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Tutup notifikasi"
                        >
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <div
                        class="h-1 w-full
                        {{ $sessionType === 'success' ? 'bg-emerald-500' : ($sessionType === 'warning' ? 'bg-amber-500' : 'bg-rose-500') }}"
                    ></div>
                </div>
            </div>
        @endif

        {{-- CLIENT TOAST --}}
        <div id="clientToast" class="fixed right-4 top-4 z-[100000] hidden w-[calc(100%-2rem)] max-w-md">
            <div class="rounded-3xl border border-amber-100 bg-white p-4 shadow-2xl shadow-slate-900/10">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-amber-100 bg-amber-50 text-amber-700">
                        <i class="ph-fill ph-warning-circle text-2xl"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-black text-amber-800">
                            Form belum lengkap
                        </p>
                        <p id="clientToastMessage" class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                            Lengkapi data wajib terlebih dahulu.
                        </p>
                    </div>

                    <button
                        type="button"
                        id="clientToastClose"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    >
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[1.75rem] border border-emerald-100 bg-gradient-to-br from-emerald-50 via-teal-50 to-slate-50 p-5 shadow-sm sm:p-6">
            <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-emerald-200/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-amber-200/20 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-2xl border border-emerald-100 bg-white/70 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        <i class="ph-fill ph-plus-circle text-base"></i>
                        Input Data Baru
                    </div>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                        Tambah Data Balita
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-7 text-slate-600">
                        Tambahkan data sasaran Balita untuk kebutuhan absensi, pengukuran fisik, imunisasi, rekam kesehatan, dan laporan Posyandu.
                    </p>

                    <div class="mt-3 max-w-2xl rounded-2xl border border-emerald-100 bg-white/60 px-4 py-3 text-xs font-bold leading-6 text-slate-600">
                        <i class="ph-fill ph-info mr-1 text-emerald-600"></i>
                        Gunakan <span class="font-black text-emerald-700">NIK Balita</span> sebagai identitas utama. Orang tua atau wali menggunakan akun warga tersebut untuk melihat data kesehatan.
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row lg:items-center">
                    @if($routeHas('kader.data.balita.index'))
                        <a
                            href="{{ route('kader.data.balita.index') }}"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 shadow-sm transition-all duration-150 ease-out hover:bg-slate-50"
                        >
                            <i class="ph-bold ph-arrow-left text-lg"></i>
                            Kembali
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- SERVER ERROR --}}
        @if($errors->any() || session('error'))
            <section class="rounded-[1.75rem] border border-rose-100 bg-rose-50 p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-rose-700">
                        <i class="ph-fill ph-x-circle text-2xl"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-black text-rose-800">
                            Data belum bisa disimpan
                        </p>

                        @if(session('error'))
                            <p class="mt-1 text-sm font-semibold leading-6 text-rose-700">
                                {{ session('error') }}
                            </p>
                        @endif

                        @if($errors->any())
                            <ul class="mt-2 space-y-1 text-sm font-semibold leading-6 text-rose-700">
                                @foreach($errors->all() as $error)
                                    <li class="flex gap-2">
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-500"></span>
                                        <span>{{ $error }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <form
            id="createBalitaForm"
            action="{{ route('kader.data.balita.store') }}"
            method="POST"
            class="grid gap-5 xl:grid-cols-[1fr_340px]"
            novalidate
        >
            @csrf

            {{-- LEFT FORM --}}
            <div class="space-y-5">

                {{-- IDENTITAS --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-emerald-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">
                            <i class="ph-fill ph-identification-card"></i>
                            Identitas
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            1. Identitas Balita
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Isi data identitas sesuai dokumen keluarga atau catatan Posyandu.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="nama_lengkap" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>

                            <input
                                id="nama_lengkap"
                                name="nama_lengkap"
                                type="text"
                                value="{{ old('nama_lengkap') }}"
                                placeholder="Contoh: Ahmad Fauzan"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('nama_lengkap') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="Nama lengkap"
                            >

                            @error('nama_lengkap')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nik" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                NIK Balita <span class="text-rose-500">*</span>
                            </label>

                            <input
                                id="nik"
                                name="nik"
                                type="text"
                                inputmode="numeric"
                                maxlength="16"
                                value="{{ old('nik') }}"
                                placeholder="16 digit NIK Balita"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('nik') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="NIK Balita"
                                data-nik="true"
                            >

                            <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                                NIK ini dipakai untuk sinkron akun warga. Admin harus membuat akun warga dengan NIK Balita yang sama.
                            </p>

                            @error('nik')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tempat_lahir" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Tempat Lahir <span class="text-rose-500">*</span>
                            </label>

                            <input
                                id="tempat_lahir"
                                name="tempat_lahir"
                                type="text"
                                value="{{ old('tempat_lahir') }}"
                                placeholder="Contoh: Pekalongan"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('tempat_lahir') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="Tempat lahir"
                            >

                            @error('tempat_lahir')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal_lahir" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Tanggal Lahir <span class="text-rose-500">*</span>
                            </label>

                            <input
                                id="tanggal_lahir"
                                name="tanggal_lahir"
                                type="date"
                                value="{{ old('tanggal_lahir') }}"
                                max="{{ now()->format('Y-m-d') }}"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('tanggal_lahir') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="Tanggal lahir"
                            >

                            <p id="usiaPreview" class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                                Usia akan dihitung otomatis setelah tanggal lahir dipilih.
                            </p>

                            @error('tanggal_lahir')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <p class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Jenis Kelamin <span class="text-rose-500">*</span>
                            </p>

                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-50/80 p-3 transition-all duration-150 ease-out hover:bg-white">
                                    <input
                                        type="radio"
                                        name="jenis_kelamin"
                                        value="L"
                                        class="peer sr-only"
                                        {{ $jenisKelamin === 'L' ? 'checked' : '' }}
                                    >

                                    <div class="rounded-xl border border-transparent p-2 peer-checked:border-sky-200 peer-checked:bg-sky-50">
                                        <p class="text-sm font-black text-slate-800">Laki-laki</p>
                                        <p class="text-xs font-semibold text-slate-500">Kode: L</p>
                                    </div>
                                </label>

                                <label class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-50/80 p-3 transition-all duration-150 ease-out hover:bg-white">
                                    <input
                                        type="radio"
                                        name="jenis_kelamin"
                                        value="P"
                                        class="peer sr-only"
                                        {{ $jenisKelamin === 'P' ? 'checked' : '' }}
                                    >

                                    <div class="rounded-xl border border-transparent p-2 peer-checked:border-pink-200 peer-checked:bg-pink-50">
                                        <p class="text-sm font-black text-slate-800">Perempuan</p>
                                        <p class="text-xs font-semibold text-slate-500">Kode: P</p>
                                    </div>
                                </label>
                            </div>

                            @error('jenis_kelamin')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ORANG TUA --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-teal-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-teal-700">
                            <i class="ph-fill ph-house-line"></i>
                            Keluarga
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            2. Orang Tua dan Domisili
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Data keluarga digunakan untuk identifikasi sasaran dan pencarian data.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="nama_ibu" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Ibu <span class="text-rose-500">*</span>
                            </label>

                            <input
                                id="nama_ibu"
                                name="nama_ibu"
                                type="text"
                                value="{{ old('nama_ibu') }}"
                                placeholder="Nama ibu kandung"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('nama_ibu') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="Nama ibu"
                            >

                            @error('nama_ibu')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nama_ayah" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Ayah
                            </label>

                            <input
                                id="nama_ayah"
                                name="nama_ayah"
                                type="text"
                                value="{{ old('nama_ayah') }}"
                                placeholder="Nama ayah jika tersedia"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('nama_ayah') border-rose-300 ring-4 ring-rose-100 @enderror"
                            >

                            @error('nama_ayah')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="alamat" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Alamat Tinggal <span class="text-rose-500">*</span>
                            </label>

                            <textarea
                                id="alamat"
                                name="alamat"
                                rows="4"
                                placeholder="Alamat lengkap Balita"
                                class="form-control w-full resize-none rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-bold leading-7 text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('alamat') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="Alamat tinggal"
                            >{{ old('alamat') }}</textarea>

                            @error('alamat')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- DATA LAHIR --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-amber-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-amber-700">
                            <i class="ph-fill ph-ruler"></i>
                            Data Lahir
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            3. Data Lahir
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Data lahir dapat membantu pembacaan awal tumbuh kembang Balita.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="berat_lahir" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Berat Lahir
                            </label>

                            <div class="relative">
                                <input
                                    id="berat_lahir"
                                    name="berat_lahir"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value="{{ old('berat_lahir') }}"
                                    placeholder="Contoh: 3.20"
                                    class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 pr-12 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('berat_lahir') border-rose-300 ring-4 ring-rose-100 @enderror"
                                >

                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">
                                    kg
                                </span>
                            </div>

                            @error('berat_lahir')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="panjang_lahir" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Panjang Lahir
                            </label>

                            <div class="relative">
                                <input
                                    id="panjang_lahir"
                                    name="panjang_lahir"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    value="{{ old('panjang_lahir') }}"
                                    placeholder="Contoh: 49.0"
                                    class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 pr-12 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('panjang_lahir') border-rose-300 ring-4 ring-rose-100 @enderror"
                                >

                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">
                                    cm
                                </span>
                            </div>

                            @error('panjang_lahir')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- RIGHT PANEL --}}
            <aside class="space-y-5">
                <section class="sticky top-5 rounded-[1.75rem] border border-slate-100 bg-white/90 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700">
                            <i class="ph-fill ph-baby text-2xl"></i>
                        </div>

                        <div>
                            <h3 class="text-lg font-black text-slate-900">
                                Ringkasan Input
                            </h3>
                            <p class="text-sm font-semibold text-slate-500">
                                Data Balita baru.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                        <p class="text-sm font-black text-emerald-800">
                            Alur Sinkron Akun
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-emerald-700">
                            Setelah data disimpan, sistem akan mencoba mencocokkan NIK Balita dengan akun warga yang dibuat oleh Admin.
                        </p>
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <h4 class="text-sm font-black text-slate-800">
                            Validasi Tambah Data
                        </h4>

                        <ul class="mt-3 space-y-2 text-xs font-semibold leading-5 text-slate-600">
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>NIK Balita wajib 16 digit dan tidak boleh duplikat.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Usia Balita maksimal 59 bulan.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Nama lengkap, nama ibu, dan alamat wajib diisi.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Akun warga boleh dibuat sebelum atau sesudah data Balita.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                        <h4 class="text-sm font-black text-amber-800">
                            Catatan Penting
                        </h4>

                        <p class="mt-2 text-xs font-semibold leading-5 text-amber-700">
                            Jangan masukkan NIK ibu atau ayah pada kolom NIK Balita. Kolom ini khusus identitas Balita.
                        </p>
                    </div>

                    <div class="mt-5">
                        <h4 class="text-sm font-black text-slate-900">
                            Simpan Data Balita
                        </h4>

                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                            Data akan masuk ke database sasaran Balita dan siap digunakan untuk layanan Posyandu.
                        </p>

                        <button
                            id="submitButton"
                            type="submit"
                            class="mt-4 inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800 active:scale-[.99]"
                        >
                            <i class="ph-fill ph-floppy-disk text-lg"></i>
                            Simpan Data Balita
                        </button>

                        @if($routeHas('kader.data.balita.index'))
                            <a
                                href="{{ route('kader.data.balita.index') }}"
                                class="mt-2 inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 transition-all duration-150 ease-out hover:bg-slate-50"
                            >
                                <i class="ph-bold ph-x text-lg"></i>
                                Batal
                            </a>
                        @endif
                    </div>
                </section>
            </aside>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('createBalitaForm');
            const submitButton = document.getElementById('submitButton');
            const nikInput = document.getElementById('nik');
            const tanggalLahirInput = document.getElementById('tanggal_lahir');
            const usiaPreview = document.getElementById('usiaPreview');

            const clientToast = document.getElementById('clientToast');
            const clientToastMessage = document.getElementById('clientToastMessage');
            const clientToastClose = document.getElementById('clientToastClose');
            const sessionToast = document.getElementById('nexusSessionToast');

            let toastTimer = null;

            function showToast(message) {
                if (!clientToast || !clientToastMessage) {
                    return;
                }

                clientToastMessage.textContent = message;
                clientToast.classList.remove('hidden', 'nexus-toast-hide');
                clientToast.classList.add('nexus-toast-show');

                clearTimeout(toastTimer);

                toastTimer = setTimeout(function () {
                    hideToast();
                }, 3000);
            }

            function hideToast() {
                if (!clientToast) {
                    return;
                }

                clientToast.classList.remove('nexus-toast-show');
                clientToast.classList.add('nexus-toast-hide');

                setTimeout(function () {
                    clientToast.classList.add('hidden');
                }, 220);
            }

            function closeSessionToast() {
                if (!sessionToast) {
                    return;
                }

                sessionToast.classList.remove('nexus-toast-show');
                sessionToast.classList.add('nexus-toast-hide');

                setTimeout(function () {
                    sessionToast.remove();
                }, 240);
            }

            if (clientToastClose) {
                clientToastClose.addEventListener('click', hideToast);
            }

            if (sessionToast) {
                setTimeout(closeSessionToast, 3800);

                const closeButton = sessionToast.querySelector('.nexus-toast-close');

                if (closeButton) {
                    closeButton.addEventListener('click', closeSessionToast);
                }
            }

            if (nikInput) {
                nikInput.addEventListener('input', function () {
                    nikInput.value = nikInput.value.replace(/\D/g, '').slice(0, 16);
                });
            }

            function updateUsiaPreview() {
                if (!tanggalLahirInput || !usiaPreview || !tanggalLahirInput.value) {
                    return;
                }

                const birthDate = new Date(tanggalLahirInput.value);
                const today = new Date();

                if (birthDate > today) {
                    usiaPreview.textContent = 'Tanggal lahir tidak boleh melebihi hari ini.';
                    usiaPreview.className = 'mt-2 text-xs font-bold leading-5 text-rose-600';
                    return;
                }

                let months = (today.getFullYear() - birthDate.getFullYear()) * 12;
                months += today.getMonth() - birthDate.getMonth();

                if (today.getDate() < birthDate.getDate()) {
                    months -= 1;
                }

                if (months < 0) {
                    months = 0;
                }

                const years = Math.floor(months / 12);
                const remainingMonths = months % 12;

                if (months >= 60) {
                    usiaPreview.textContent = 'Usia terdeteksi ' + years + ' tahun ' + remainingMonths + ' bulan. Modul Balita hanya menerima usia maksimal 59 bulan.';
                    usiaPreview.className = 'mt-2 text-xs font-bold leading-5 text-rose-600';
                    return;
                }

                usiaPreview.textContent = years > 0
                    ? 'Usia terdeteksi ' + years + ' tahun ' + remainingMonths + ' bulan.'
                    : 'Usia terdeteksi ' + remainingMonths + ' bulan.';

                usiaPreview.className = 'mt-2 text-xs font-semibold leading-5 text-emerald-700';
            }

            if (tanggalLahirInput) {
                tanggalLahirInput.addEventListener('change', updateUsiaPreview);
                updateUsiaPreview();
            }

            function markInvalid(input) {
                input.classList.add('border-rose-300', 'ring-4', 'ring-rose-100');
            }

            function clearInvalid(input) {
                input.classList.remove('border-rose-300', 'ring-4', 'ring-rose-100');
            }

            if (form) {
                form.addEventListener('submit', function (event) {
                    const requiredInputs = Array.from(form.querySelectorAll('[data-required="true"]'));

                    for (const input of requiredInputs) {
                        clearInvalid(input);

                        if (!input.value || input.value.trim() === '') {
                            event.preventDefault();
                            markInvalid(input);
                            input.focus();
                            showToast(input.dataset.label + ' wajib diisi.');
                            return;
                        }
                    }

                    if (nikInput && nikInput.value.length !== 16) {
                        event.preventDefault();
                        markInvalid(nikInput);
                        nikInput.focus();
                        showToast('NIK Balita harus berisi tepat 16 digit angka.');
                        return;
                    }

                    const selectedGender = form.querySelector('input[name="jenis_kelamin"]:checked');

                    if (!selectedGender) {
                        event.preventDefault();
                        showToast('Jenis kelamin wajib dipilih.');
                        return;
                    }

                    if (tanggalLahirInput && tanggalLahirInput.value) {
                        const birthDate = new Date(tanggalLahirInput.value);
                        const today = new Date();

                        if (birthDate > today) {
                            event.preventDefault();
                            tanggalLahirInput.focus();
                            showToast('Tanggal lahir tidak boleh melebihi hari ini.');
                            return;
                        }

                        let months = (today.getFullYear() - birthDate.getFullYear()) * 12;
                        months += today.getMonth() - birthDate.getMonth();

                        if (today.getDate() < birthDate.getDate()) {
                            months -= 1;
                        }

                        if (months >= 60) {
                            event.preventDefault();
                            tanggalLahirInput.focus();
                            showToast('Usia Balita melewati batas layanan modul ini, yaitu maksimal 59 bulan.');
                            return;
                        }
                    }

                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.classList.add('opacity-70', 'cursor-wait');
                        submitButton.innerHTML = '<i class="ph-fill ph-spinner-gap animate-spin text-lg"></i> Menyimpan...';
                    }
                });

                form.querySelectorAll('.form-control').forEach(function (input) {
                    input.addEventListener('input', function () {
                        clearInvalid(input);
                    });
                });
            }
        });
    </script>
@endsection