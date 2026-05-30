@extends('layouts.kader')

@section('title', 'Edit Data Remaja')
@section('page-name', 'Edit Data Remaja')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $sessionType = session('success') ? 'success' : (session('warning') ? 'warning' : (session('error') ? 'error' : null));
    $sessionMessage = session('success') ?? session('warning') ?? session('error');

    $jenisKelamin = old('jenis_kelamin', $remaja->jenis_kelamin ?? '');
    $akunTerhubung = filled($remaja->user_id ?? null);

    $tanggalLahirValue = old(
        'tanggal_lahir',
        filled($remaja->tanggal_lahir ?? null)
            ? Carbon::parse($remaja->tanggal_lahir)->format('Y-m-d')
            : ''
    );
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

        {{-- SESSION TOAST --}}
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
            <div class="pointer-events-none absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-sky-200/20 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-2xl border border-emerald-100 bg-white/70 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        <i class="ph-fill ph-pencil-simple text-base"></i>
                        Mode Edit Data
                    </div>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                        Edit Data Remaja
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-7 text-slate-600">
                        Perbarui data Remaja dengan hati-hati. Perubahan NIK akan memengaruhi sinkronisasi akun warga.
                    </p>

                    <div class="mt-3 max-w-2xl rounded-2xl border border-emerald-100 bg-white/60 px-4 py-3 text-xs font-bold leading-6 text-slate-600">
                        <i class="ph-fill ph-info mr-1 text-emerald-600"></i>
                        Gunakan <span class="font-black text-emerald-700">NIK Remaja</span> sebagai identitas utama untuk akun warga dan akses data.
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row lg:items-center">
                    @if($routeHas('kader.data.remaja.index'))
                        <a
                            href="{{ route('kader.data.remaja.index') }}"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 shadow-sm transition-all duration-150 ease-out hover:bg-slate-50"
                        >
                            <i class="ph-bold ph-arrow-left text-lg"></i>
                            Kembali
                        </a>
                    @endif

                    @if($routeHas('kader.data.remaja.show'))
                        <a
                            href="{{ route('kader.data.remaja.show', $remaja->id) }}"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800"
                        >
                            <i class="ph-fill ph-eye text-lg"></i>
                            Detail
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
                            Data belum bisa diperbarui
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
            id="editRemajaForm"
            action="{{ route('kader.data.remaja.update', $remaja->id) }}"
            method="POST"
            class="grid gap-5 xl:grid-cols-[1fr_340px]"
            novalidate
        >
            @csrf
            @method('PUT')

            <div class="space-y-5">

                {{-- IDENTITAS --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-emerald-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">
                            <i class="ph-fill ph-identification-card"></i>
                            Identitas
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            1. Identitas Remaja
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Pastikan data identitas sesuai catatan keluarga atau layanan Posyandu.
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
                                value="{{ old('nama_lengkap', $remaja->nama_lengkap) }}"
                                placeholder="Contoh: Galih Saputra"
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
                                NIK Remaja <span class="text-rose-500">*</span>
                            </label>

                            <input
                                id="nik"
                                name="nik"
                                type="text"
                                inputmode="numeric"
                                maxlength="16"
                                value="{{ old('nik', $remaja->nik) }}"
                                placeholder="16 digit NIK Remaja"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('nik') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="NIK Remaja"
                                data-nik="true"
                            >

                            <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                                Jika NIK diubah, sistem akan mengecek ulang sinkronisasi akun warga.
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
                                value="{{ old('tempat_lahir', $remaja->tempat_lahir) }}"
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
                                value="{{ $tanggalLahirValue }}"
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

                {{-- SEKOLAH --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-sky-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-sky-700">
                            <i class="ph-fill ph-student"></i>
                            Pendidikan
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            2. Data Sekolah
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Data sekolah bersifat opsional, tapi berguna untuk pendataan kelompok Remaja.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="sekolah" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Sekolah
                            </label>

                            <input
                                id="sekolah"
                                name="sekolah"
                                type="text"
                                value="{{ old('sekolah', $remaja->sekolah) }}"
                                placeholder="Contoh: SMP Negeri 1"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('sekolah') border-rose-300 ring-4 ring-rose-100 @enderror"
                            >

                            @error('sekolah')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="kelas" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Kelas
                            </label>

                            <input
                                id="kelas"
                                name="kelas"
                                type="text"
                                value="{{ old('kelas', $remaja->kelas) }}"
                                placeholder="Contoh: VIII A"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('kelas') border-rose-300 ring-4 ring-rose-100 @enderror"
                            >

                            @error('kelas')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- KELUARGA --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-teal-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-teal-700">
                            <i class="ph-fill ph-house-line"></i>
                            Keluarga
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            3. Orang Tua/Wali dan Domisili
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Data keluarga digunakan untuk identifikasi dan kontak pendukung.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="nama_ortu" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Orang Tua/Wali <span class="text-rose-500">*</span>
                            </label>

                            <input
                                id="nama_ortu"
                                name="nama_ortu"
                                type="text"
                                value="{{ old('nama_ortu', $remaja->nama_ortu) }}"
                                placeholder="Nama orang tua atau wali"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('nama_ortu') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="Nama orang tua/wali"
                            >

                            @error('nama_ortu')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="telepon_ortu" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Telepon Orang Tua/Wali
                            </label>

                            <input
                                id="telepon_ortu"
                                name="telepon_ortu"
                                type="text"
                                inputmode="numeric"
                                maxlength="20"
                                value="{{ old('telepon_ortu', $remaja->telepon_ortu) }}"
                                placeholder="Contoh: 081234567890"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('telepon_ortu') border-rose-300 ring-4 ring-rose-100 @enderror"
                            >

                            @error('telepon_ortu')
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
                                placeholder="Alamat lengkap Remaja"
                                class="form-control w-full resize-none rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-bold leading-7 text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('alamat') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="Alamat tinggal"
                            >{{ old('alamat', $remaja->alamat) }}</textarea>

                            @error('alamat')
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
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl border
                            {{ $akunTerhubung ? 'border-teal-100 bg-teal-50 text-teal-700' : 'border-amber-100 bg-amber-50 text-amber-700' }}"
                        >
                            <i class="ph-fill {{ $akunTerhubung ? 'ph-link-simple' : 'ph-warning-circle' }} text-2xl"></i>
                        </div>

                        <div>
                            <h3 class="text-lg font-black text-slate-900">
                                Status Akun
                            </h3>
                            <p class="text-sm font-semibold text-slate-500">
                                Sinkronisasi warga.
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 rounded-2xl border px-4 py-3
                        {{ $akunTerhubung ? 'border-teal-100 bg-teal-50 text-teal-800' : 'border-amber-100 bg-amber-50 text-amber-800' }}"
                    >
                        <p class="text-sm font-black">
                            {{ $akunTerhubung ? 'Akun Terhubung' : 'Belum Terhubung' }}
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5">
                            {{ $akunTerhubung ? 'Data Remaja sudah terhubung dengan akun warga.' : 'Setelah data disimpan, sistem akan mencoba mencocokkan akun berdasarkan NIK Remaja.' }}
                        </p>
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <h4 class="text-sm font-black text-slate-800">
                            Validasi Edit
                        </h4>

                        <ul class="mt-3 space-y-2 text-xs font-semibold leading-5 text-slate-600">
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>NIK Remaja wajib 16 digit dan tidak boleh sama dengan data lain.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Tanggal lahir tidak boleh melebihi hari ini.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Nama lengkap, orang tua/wali, dan alamat tetap wajib diisi.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Sinkronisasi akun diperbarui ulang setelah penyimpanan.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                        <h4 class="text-sm font-black text-amber-800">
                            Catatan Edit
                        </h4>

                        <p class="mt-2 text-xs font-semibold leading-5 text-amber-700">
                            Perubahan NIK sebaiknya dilakukan hanya jika ada kesalahan input. Salah NIK itu bukan typo kecil, itu undangan error berjamaah.
                        </p>
                    </div>

                    <div class="mt-5">
                        <h4 class="text-sm font-black text-slate-900">
                            Simpan Perubahan
                        </h4>

                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                            Data akan diperbarui dan sinkronisasi akun warga dicek ulang.
                        </p>

                        <button
                            id="submitButton"
                            type="submit"
                            class="mt-4 inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800 active:scale-[.99]"
                        >
                            <i class="ph-fill ph-floppy-disk text-lg"></i>
                            Simpan Perubahan
                        </button>

                        @if($routeHas('kader.data.remaja.index'))
                            <a
                                href="{{ route('kader.data.remaja.index') }}"
                                class="mt-2 inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 transition-all duration-150 ease-out hover:bg-slate-50"
                            >
                                <i class="ph-bold ph-x text-lg"></i>
                                Batal Edit
                            </a>
                        @endif
                    </div>
                </section>
            </aside>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('editRemajaForm');
            const submitButton = document.getElementById('submitButton');
            const nikInput = document.getElementById('nik');
            const teleponInput = document.getElementById('telepon_ortu');
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

            if (teleponInput) {
                teleponInput.addEventListener('input', function () {
                    teleponInput.value = teleponInput.value.replace(/[^\d+]/g, '').slice(0, 20);
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

                usiaPreview.textContent = 'Usia terdeteksi ' + years + ' tahun ' + remainingMonths + ' bulan.';

                if (years < 10 || years > 19) {
                    usiaPreview.className = 'mt-2 text-xs font-bold leading-5 text-amber-700';
                    usiaPreview.textContent += ' Periksa lagi, usia ini di luar rentang Remaja umum.';
                    return;
                }

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
                        showToast('NIK Remaja harus berisi tepat 16 digit angka.');
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