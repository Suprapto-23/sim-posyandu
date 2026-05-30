@extends('layouts.kader')

@section('title', 'Edit Data Lansia')
@section('page-name', 'Edit Data Lansia')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $sessionType = session('success') ? 'success' : (session('warning') ? 'warning' : (session('error') ? 'error' : null));
    $sessionMessage = session('success') ?? session('warning') ?? session('error');

    $jenisKelamin = old('jenis_kelamin', $lansia->jenis_kelamin ?? '');
    $tingkatKemandirian = old('tingkat_kemandirian', $lansia->tingkat_kemandirian ?? '');
    $akunTerhubung = filled($lansia->user_id ?? null);

    $tanggalLahirValue = old(
        'tanggal_lahir',
        filled($lansia->tanggal_lahir ?? null)
            ? Carbon::parse($lansia->tanggal_lahir)->format('Y-m-d')
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
            <div class="pointer-events-none absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-amber-200/20 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-2xl border border-emerald-100 bg-white/70 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        <i class="ph-fill ph-pencil-simple text-base"></i>
                        Mode Edit Data
                    </div>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                        Edit Data Lansia
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-7 text-slate-600">
                        Perbarui data Lansia, pemeriksaan kesehatan dasar, tingkat kemandirian, dan sinkronisasi akun warga.
                    </p>

                    <div class="mt-3 max-w-2xl rounded-2xl border border-emerald-100 bg-white/60 px-4 py-3 text-xs font-bold leading-6 text-slate-600">
                        <i class="ph-fill ph-info mr-1 text-emerald-600"></i>
                        Perubahan <span class="font-black text-emerald-700">NIK Lansia</span> akan membuat sistem mengecek ulang akun warga yang terhubung.
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row lg:items-center">
                    @if($routeHas('kader.data.lansia.index'))
                        <a
                            href="{{ route('kader.data.lansia.index') }}"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 shadow-sm transition-all duration-150 ease-out hover:bg-slate-50"
                        >
                            <i class="ph-bold ph-arrow-left text-lg"></i>
                            Kembali
                        </a>
                    @endif

                    @if($routeHas('kader.data.lansia.show'))
                        <a
                            href="{{ route('kader.data.lansia.show', $lansia->id) }}"
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
            id="editLansiaForm"
            action="{{ route('kader.data.lansia.update', $lansia->id) }}"
            method="POST"
            class="grid gap-5 xl:grid-cols-[1fr_360px]"
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
                            1. Identitas Lansia
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Pastikan data identitas Lansia sesuai data keluarga atau catatan layanan.
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
                                value="{{ old('nama_lengkap', $lansia->nama_lengkap) }}"
                                placeholder="Contoh: Siti Aminah"
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
                                NIK Lansia <span class="text-rose-500">*</span>
                            </label>

                            <input
                                id="nik"
                                name="nik"
                                type="text"
                                inputmode="numeric"
                                maxlength="16"
                                value="{{ old('nik', $lansia->nik) }}"
                                placeholder="16 digit NIK Lansia"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('nik') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="NIK Lansia"
                                data-nik="true"
                            >

                            <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                                Jika NIK diubah, sinkronisasi akun warga akan dicek ulang.
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
                                value="{{ old('tempat_lahir', $lansia->tempat_lahir) }}"
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
                                max="{{ now()->subYears(45)->format('Y-m-d') }}"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('tanggal_lahir') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="Tanggal lahir"
                            >

                            <p id="usiaPreview" class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                                Lansia/Pra-Lansia minimal berusia 45 tahun.
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

                        <div class="md:col-span-2">
                            <label for="alamat" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Alamat Tinggal <span class="text-rose-500">*</span>
                            </label>

                            <textarea
                                id="alamat"
                                name="alamat"
                                rows="4"
                                placeholder="Alamat lengkap Lansia"
                                class="form-control w-full resize-none rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-bold leading-7 text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('alamat') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-required="true"
                                data-label="Alamat tinggal"
                            >{{ old('alamat', $lansia->alamat) }}</textarea>

                            @error('alamat')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- PENGUKURAN FISIK --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-sky-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-sky-700">
                            <i class="ph-fill ph-ruler"></i>
                            Pengukuran Fisik
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            2. Pengukuran Dasar
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Berat dan tinggi badan digunakan untuk menghitung ulang IMT.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="berat_badan" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Berat Badan
                            </label>

                            <div class="relative">
                                <input
                                    id="berat_badan"
                                    name="berat_badan"
                                    type="number"
                                    step="0.1"
                                    min="1"
                                    max="300"
                                    value="{{ old('berat_badan', $lansia->berat_badan) }}"
                                    placeholder="Contoh: 60"
                                    class="form-control numeric-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 pr-12 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('berat_badan') border-rose-300 ring-4 ring-rose-100 @enderror"
                                >
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">kg</span>
                            </div>

                            @error('berat_badan')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tinggi_badan" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Tinggi Badan
                            </label>

                            <div class="relative">
                                <input
                                    id="tinggi_badan"
                                    name="tinggi_badan"
                                    type="number"
                                    step="0.1"
                                    min="50"
                                    max="250"
                                    value="{{ old('tinggi_badan', $lansia->tinggi_badan) }}"
                                    placeholder="Contoh: 160"
                                    class="form-control numeric-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 pr-12 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('tinggi_badan') border-rose-300 ring-4 ring-rose-100 @enderror"
                                >
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>

                            @error('tinggi_badan')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="lingkar_perut" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Lingkar Perut
                            </label>

                            <div class="relative">
                                <input
                                    id="lingkar_perut"
                                    name="lingkar_perut"
                                    type="number"
                                    step="0.1"
                                    min="20"
                                    max="200"
                                    value="{{ old('lingkar_perut', $lansia->lingkar_perut) }}"
                                    placeholder="Contoh: 82"
                                    class="form-control numeric-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 pr-12 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('lingkar_perut') border-rose-300 ring-4 ring-rose-100 @enderror"
                                >
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>

                            @error('lingkar_perut')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div id="imtPreviewBox" class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-sm font-black text-slate-800">
                            Preview IMT:
                            <span id="imtPreview" class="text-emerald-700">
                                {{ old('imt', $lansia->imt ?? 'Belum dihitung') }}
                            </span>
                        </p>

                        <p id="imtCategory" class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                            IMT akan dihitung ulang saat berat atau tinggi badan diubah.
                        </p>
                    </div>
                </section>

                {{-- PEMERIKSAAN KESEHATAN --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-amber-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-amber-700">
                            <i class="ph-fill ph-heartbeat"></i>
                            Pemeriksaan Kesehatan
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            3. Pemeriksaan Dasar Lansia
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Perbarui indikator kesehatan dasar Lansia secara berkala.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label for="tekanan_darah" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Tekanan Darah
                            </label>

                            <input
                                id="tekanan_darah"
                                name="tekanan_darah"
                                type="text"
                                inputmode="numeric"
                                value="{{ old('tekanan_darah', $lansia->tekanan_darah) }}"
                                placeholder="120/80"
                                class="form-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('tekanan_darah') border-rose-300 ring-4 ring-rose-100 @enderror"
                                data-tensi="true"
                            >

                            <p class="mt-2 text-xs font-semibold text-slate-500">
                                Format: 120/80
                            </p>

                            @error('tekanan_darah')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="gula_darah" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Gula Darah
                            </label>

                            <div class="relative">
                                <input
                                    id="gula_darah"
                                    name="gula_darah"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="999"
                                    value="{{ old('gula_darah', $lansia->gula_darah) }}"
                                    placeholder="Contoh: 120"
                                    class="form-control numeric-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 pr-16 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('gula_darah') border-rose-300 ring-4 ring-rose-100 @enderror"
                                >
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">mg/dL</span>
                            </div>

                            @error('gula_darah')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="kolesterol" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Kolesterol
                            </label>

                            <div class="relative">
                                <input
                                    id="kolesterol"
                                    name="kolesterol"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="999"
                                    value="{{ old('kolesterol', $lansia->kolesterol) }}"
                                    placeholder="Contoh: 180"
                                    class="form-control numeric-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 pr-16 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('kolesterol') border-rose-300 ring-4 ring-rose-100 @enderror"
                                >
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">mg/dL</span>
                            </div>

                            @error('kolesterol')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="asam_urat" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Asam Urat
                            </label>

                            <div class="relative">
                                <input
                                    id="asam_urat"
                                    name="asam_urat"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="99"
                                    value="{{ old('asam_urat', $lansia->asam_urat) }}"
                                    placeholder="Contoh: 6.5"
                                    class="form-control numeric-control h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 pr-16 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('asam_urat') border-rose-300 ring-4 ring-rose-100 @enderror"
                                >
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">mg/dL</span>
                            </div>

                            @error('asam_urat')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- KEMANDIRIAN DAN RIWAYAT --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-teal-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-teal-700">
                            <i class="ph-fill ph-hand-heart"></i>
                            Kondisi Lansia
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            4. Kemandirian, Riwayat Penyakit, dan Keluhan
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Perbarui kondisi fungsional dan keluhan Lansia secara ringkas.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Tingkat Kemandirian
                            </p>

                            <div class="grid gap-3 md:grid-cols-3">
                                <label class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-50/80 p-3 transition-all duration-150 ease-out hover:bg-white">
                                    <input
                                        type="radio"
                                        name="tingkat_kemandirian"
                                        value="mandiri"
                                        class="peer sr-only"
                                        {{ $tingkatKemandirian === 'mandiri' ? 'checked' : '' }}
                                    >

                                    <div class="rounded-xl border border-transparent p-3 peer-checked:border-emerald-200 peer-checked:bg-emerald-50">
                                        <p class="text-sm font-black text-slate-800">Mandiri</p>
                                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                            Masih mampu melakukan aktivitas harian utama.
                                        </p>
                                    </div>
                                </label>

                                <label class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-50/80 p-3 transition-all duration-150 ease-out hover:bg-white">
                                    <input
                                        type="radio"
                                        name="tingkat_kemandirian"
                                        value="bantuan_sebagian"
                                        class="peer sr-only"
                                        {{ $tingkatKemandirian === 'bantuan_sebagian' ? 'checked' : '' }}
                                    >

                                    <div class="rounded-xl border border-transparent p-3 peer-checked:border-amber-200 peer-checked:bg-amber-50">
                                        <p class="text-sm font-black text-slate-800">Bantuan Sebagian</p>
                                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                            Membutuhkan bantuan pada beberapa aktivitas.
                                        </p>
                                    </div>
                                </label>

                                <label class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-50/80 p-3 transition-all duration-150 ease-out hover:bg-white">
                                    <input
                                        type="radio"
                                        name="tingkat_kemandirian"
                                        value="ketergantungan_penuh"
                                        class="peer sr-only"
                                        {{ $tingkatKemandirian === 'ketergantungan_penuh' ? 'checked' : '' }}
                                    >

                                    <div class="rounded-xl border border-transparent p-3 peer-checked:border-rose-200 peer-checked:bg-rose-50">
                                        <p class="text-sm font-black text-slate-800">Ketergantungan Penuh</p>
                                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                            Membutuhkan bantuan penuh dalam aktivitas harian.
                                        </p>
                                    </div>
                                </label>
                            </div>

                            @error('tingkat_kemandirian')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="penyakit_bawaan" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Riwayat Penyakit Bawaan
                            </label>

                            <textarea
                                id="penyakit_bawaan"
                                name="penyakit_bawaan"
                                rows="3"
                                placeholder="Contoh: Hipertensi, diabetes, asam urat, jantung..."
                                class="form-control w-full resize-none rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-bold leading-7 text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('penyakit_bawaan') border-rose-300 ring-4 ring-rose-100 @enderror"
                            >{{ old('penyakit_bawaan', $lansia->penyakit_bawaan) }}</textarea>

                            @error('penyakit_bawaan')
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="keluhan" class="mb-2 block text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                Keluhan Saat Ini
                            </label>

                            <textarea
                                id="keluhan"
                                name="keluhan"
                                rows="3"
                                placeholder="Contoh: Pusing, pegal, sulit tidur, nyeri sendi..."
                                class="form-control w-full resize-none rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-bold leading-7 text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('keluhan') border-rose-300 ring-4 ring-rose-100 @enderror"
                            >{{ old('keluhan', $lansia->keluhan) }}</textarea>

                            @error('keluhan')
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
                            {{ $akunTerhubung ? 'Data Lansia sudah terhubung dengan akun warga.' : 'Setelah data disimpan, sistem akan mencoba mencocokkan akun berdasarkan NIK Lansia.' }}
                        </p>
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <h4 class="text-sm font-black text-slate-800">
                            Validasi Edit
                        </h4>

                        <ul class="mt-3 space-y-2 text-xs font-semibold leading-5 text-slate-600">
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>NIK wajib 16 digit dan tidak boleh sama dengan data lain.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Usia Lansia/Pra-Lansia minimal 45 tahun.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Format tekanan darah harus seperti 120/80.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>IMT dihitung ulang dari berat dan tinggi badan.</span>
                            </li>
                        </ul>
                    </div>

                    <div id="healthSummaryBox" class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                        <h4 class="text-sm font-black text-amber-800">
                            Ringkasan Kesehatan
                        </h4>

                        <p id="healthSummaryText" class="mt-2 text-xs font-semibold leading-5 text-amber-700">
                            Isi pemeriksaan dasar untuk melihat ringkasan cepat.
                        </p>
                    </div>

                    <div class="mt-5 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3">
                        <h4 class="text-sm font-black text-rose-800">
                            Catatan Edit
                        </h4>

                        <p class="mt-2 text-xs font-semibold leading-5 text-rose-700">
                            Jangan ubah NIK kecuali memang salah input. Salah NIK bikin akun warga putus nyambung, seperti sinyal WiFi pas hujan.
                        </p>
                    </div>

                    <div class="mt-5">
                        <h4 class="text-sm font-black text-slate-900">
                            Simpan Perubahan
                        </h4>

                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                            Data Lansia akan diperbarui dan sinkronisasi akun warga dicek ulang.
                        </p>

                        <button
                            id="submitButton"
                            type="submit"
                            class="mt-4 inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800 active:scale-[.99]"
                        >
                            <i class="ph-fill ph-floppy-disk text-lg"></i>
                            Simpan Perubahan
                        </button>

                        @if($routeHas('kader.data.lansia.index'))
                            <a
                                href="{{ route('kader.data.lansia.index') }}"
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
            const form = document.getElementById('editLansiaForm');
            const submitButton = document.getElementById('submitButton');

            const nikInput = document.getElementById('nik');
            const tanggalLahirInput = document.getElementById('tanggal_lahir');
            const usiaPreview = document.getElementById('usiaPreview');

            const beratInput = document.getElementById('berat_badan');
            const tinggiInput = document.getElementById('tinggi_badan');
            const imtPreview = document.getElementById('imtPreview');
            const imtCategory = document.getElementById('imtCategory');

            const tensiInput = document.getElementById('tekanan_darah');
            const gulaInput = document.getElementById('gula_darah');
            const kolesterolInput = document.getElementById('kolesterol');
            const asamUratInput = document.getElementById('asam_urat');
            const lingkarPerutInput = document.getElementById('lingkar_perut');
            const healthSummaryText = document.getElementById('healthSummaryText');

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

            if (tensiInput) {
                tensiInput.addEventListener('input', function () {
                    let value = tensiInput.value.replace(/[^\d/]/g, '');
                    const parts = value.split('/');

                    if (parts.length > 2) {
                        value = parts[0] + '/' + parts[1];
                    }

                    tensiInput.value = value.slice(0, 7);
                    updateHealthSummary();
                });
            }

            function calculateAgeYears(dateValue) {
                if (!dateValue) {
                    return null;
                }

                const birthDate = new Date(dateValue);
                const today = new Date();

                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age -= 1;
                }

                return age;
            }

            function updateUsiaPreview() {
                if (!tanggalLahirInput || !usiaPreview || !tanggalLahirInput.value) {
                    return;
                }

                const age = calculateAgeYears(tanggalLahirInput.value);

                if (age === null || age < 0) {
                    usiaPreview.textContent = 'Tanggal lahir tidak valid.';
                    usiaPreview.className = 'mt-2 text-xs font-bold leading-5 text-rose-600';
                    return;
                }

                usiaPreview.textContent = 'Usia terdeteksi ' + age + ' tahun.';

                if (age < 45) {
                    usiaPreview.className = 'mt-2 text-xs font-bold leading-5 text-rose-600';
                    usiaPreview.textContent += ' Minimal kategori Lansia/Pra-Lansia adalah 45 tahun.';
                    return;
                }

                usiaPreview.className = 'mt-2 text-xs font-semibold leading-5 text-emerald-700';
            }

            function calculateImt() {
                if (!beratInput || !tinggiInput || !imtPreview || !imtCategory) {
                    return;
                }

                const berat = parseFloat(beratInput.value);
                const tinggiCm = parseFloat(tinggiInput.value);

                if (!berat || !tinggiCm || tinggiCm <= 0) {
                    imtPreview.textContent = 'Belum dihitung';
                    imtCategory.textContent = 'Isi berat dan tinggi badan untuk menghitung IMT otomatis.';
                    imtCategory.className = 'mt-1 text-xs font-semibold leading-5 text-slate-500';
                    return;
                }

                const tinggiM = tinggiCm / 100;
                const imt = berat / (tinggiM * tinggiM);
                const rounded = Math.round(imt * 100) / 100;

                let category = 'Normal';
                let className = 'mt-1 text-xs font-semibold leading-5 text-emerald-700';

                if (rounded < 18.5) {
                    category = 'Berat badan kurang';
                    className = 'mt-1 text-xs font-bold leading-5 text-amber-700';
                } else if (rounded >= 25 && rounded < 30) {
                    category = 'Berat badan berlebih';
                    className = 'mt-1 text-xs font-bold leading-5 text-amber-700';
                } else if (rounded >= 30) {
                    category = 'Obesitas';
                    className = 'mt-1 text-xs font-bold leading-5 text-rose-700';
                }

                imtPreview.textContent = rounded;
                imtCategory.textContent = 'Kategori IMT: ' + category + '.';
                imtCategory.className = className;
            }

            function updateHealthSummary() {
                if (!healthSummaryText) {
                    return;
                }

                const notes = [];

                const tensi = tensiInput ? tensiInput.value.trim() : '';
                const gula = gulaInput && gulaInput.value ? parseFloat(gulaInput.value) : null;
                const kolesterol = kolesterolInput && kolesterolInput.value ? parseFloat(kolesterolInput.value) : null;
                const asamUrat = asamUratInput && asamUratInput.value ? parseFloat(asamUratInput.value) : null;
                const lingkarPerut = lingkarPerutInput && lingkarPerutInput.value ? parseFloat(lingkarPerutInput.value) : null;

                if (tensi !== '') {
                    const validTensi = /^\d{2,3}\/\d{2,3}$/.test(tensi);
                    notes.push(validTensi ? 'Tensi tercatat.' : 'Format tensi belum sesuai.');
                }

                if (gula !== null) {
                    notes.push(gula >= 200 ? 'Gula darah perlu perhatian.' : 'Gula darah tercatat.');
                }

                if (kolesterol !== null) {
                    notes.push(kolesterol >= 240 ? 'Kolesterol perlu perhatian.' : 'Kolesterol tercatat.');
                }

                if (asamUrat !== null) {
                    notes.push(asamUrat >= 8 ? 'Asam urat perlu perhatian.' : 'Asam urat tercatat.');
                }

                if (lingkarPerut !== null) {
                    notes.push('Lingkar perut tercatat.');
                }

                healthSummaryText.textContent = notes.length
                    ? notes.join(' ')
                    : 'Isi pemeriksaan dasar untuk melihat ringkasan cepat.';
            }

            function markInvalid(input) {
                input.classList.add('border-rose-300', 'ring-4', 'ring-rose-100');
            }

            function clearInvalid(input) {
                input.classList.remove('border-rose-300', 'ring-4', 'ring-rose-100');
            }

            [tanggalLahirInput, beratInput, tinggiInput, gulaInput, kolesterolInput, asamUratInput, lingkarPerutInput].forEach(function (input) {
                if (!input) {
                    return;
                }

                input.addEventListener('input', function () {
                    calculateImt();
                    updateHealthSummary();
                });

                input.addEventListener('change', function () {
                    updateUsiaPreview();
                    calculateImt();
                    updateHealthSummary();
                });
            });

            updateUsiaPreview();
            calculateImt();
            updateHealthSummary();

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
                        showToast('NIK Lansia harus berisi tepat 16 digit angka.');
                        return;
                    }

                    const selectedGender = form.querySelector('input[name="jenis_kelamin"]:checked');

                    if (!selectedGender) {
                        event.preventDefault();
                        showToast('Jenis kelamin wajib dipilih.');
                        return;
                    }

                    const age = tanggalLahirInput ? calculateAgeYears(tanggalLahirInput.value) : null;

                    if (age !== null && age < 45) {
                        event.preventDefault();
                        tanggalLahirInput.focus();
                        showToast('Kategori Lansia/Pra-Lansia minimal harus berusia 45 tahun.');
                        return;
                    }

                    if (tensiInput && tensiInput.value.trim() !== '' && !/^\d{2,3}\/\d{2,3}$/.test(tensiInput.value.trim())) {
                        event.preventDefault();
                        markInvalid(tensiInput);
                        tensiInput.focus();
                        showToast('Format tekanan darah harus seperti 120/80.');
                        return;
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