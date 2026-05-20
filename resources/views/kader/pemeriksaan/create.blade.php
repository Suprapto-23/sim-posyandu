@extends('layouts.kader')

@section('title', 'Input Pengukuran Fisik')

@php
    use Illuminate\Support\Facades\Route;

    $kategoriAwal = old('kategori_pasien', $kategori_awal ?? request('kategori', 'balita'));
    $pasienIdAwal = old('pasien_id', $pasien_id_awal ?? request('pasien_id'));

    if (!in_array($kategoriAwal, ['balita', 'remaja', 'lansia'], true)) {
        $kategoriAwal = 'balita';
    }

    $apiPasienUrl = Route::has('kader.pemeriksaan.api')
        ? route('kader.pemeriksaan.api')
        : url('/kader/pemeriksaan/api/pasien');

    $routeHas = fn ($name) => Route::has($name);

    $kategoriMenus = [
        'balita' => [
            'label' => 'Balita / Anak',
            'icon' => 'fa-child-reaching',
            'desc' => 'BB, TB/PB, lingkar kepala, LiLA, suhu bila diperlukan.',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'desc' => 'BB, TB, IMT, LiLA, lingkar perut, tekanan darah, Hb.',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'desc' => 'BB, TB, IMT, tekanan darah, lingkar perut, pemeriksaan tambahan.',
        ],
    ];
@endphp

@push('styles')
<style>
    .ukur-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .ukur-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.12), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(245,158,11,.10), transparent 26%),
            linear-gradient(135deg, #f8fffc, #f8fafc 58%, #fffaf0);
    }

    .ukur-hero,
    .ukur-panel {
        border: 1px solid rgba(226,232,240,.88);
        background: rgba(255,255,255,.95);
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
    }

    .ukur-hero {
        border-color: rgba(167,243,208,.7);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.14), transparent 30%),
            radial-gradient(circle at 88% 16%, rgba(245,158,11,.12), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.96), rgba(236,253,245,.76));
    }

    .ukur-input {
        border: 1px solid rgba(226,232,240,.9);
        background: #fff;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }

    .ukur-input:focus {
        border-color: rgba(16,185,129,.36);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
    }

    .ukur-input.is-error {
        border-color: rgba(244,63,94,.5);
        background: #fff1f2;
        box-shadow: 0 0 0 4px rgba(244,63,94,.08);
    }

    .kategori-card {
        border: 1px solid rgba(226,232,240,.9);
        background: #fff;
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }

    .kategori-card.active {
        border-color: rgba(16,185,129,.35);
        background: #ecfdf5;
        box-shadow: 0 8px 20px rgba(16,185,129,.08);
    }

    .warga-list {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 4px;
        overscroll-behavior: contain;
    }

    .warga-list::-webkit-scrollbar { width: 7px; }
    .warga-list::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 999px; }
    .warga-list::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }

    .warga-item {
        border: 1px solid rgba(226,232,240,.86);
        background: #fff;
        transition: border-color .12s ease, background .12s ease;
    }

    .warga-item:hover,
    .warga-item.active {
        border-color: rgba(16,185,129,.35);
        background: #ecfdf5;
    }

    .field-section[hidden] {
        display: none !important;
    }

    .toast-custom {
        position: fixed;
        top: 96px;
        right: 24px;
        z-index: 90;
        width: min(390px, calc(100vw - 32px));
        opacity: 0;
        transform: translateY(-10px);
        pointer-events: none;
        transition: .18s ease;
    }

    .toast-custom.show {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
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
<div class="ukur-page space-y-5">

    <div id="customToast" class="toast-custom">
        <div class="rounded-[24px] border border-rose-100 bg-white p-4 shadow-[0_22px_60px_rgba(15,23,42,.22)]">
            <div class="flex gap-3">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-rose-50 text-rose-600">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div>
                    <p class="text-sm font-black text-slate-900">Data belum lengkap</p>
                    <p id="customToastText" class="mt-1 text-xs font-bold leading-5 text-slate-500">
                        Lengkapi data terlebih dahulu.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <section class="ukur-hero rounded-[28px] p-5 sm:p-6">
        <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-stethoscope"></i>
                    Input Pengukuran
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Input Pengukuran Fisik
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Pilih sasaran, lalu isi parameter sesuai kategori. Form hanya menampilkan kebutuhan kategori yang dipilih.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                @if($routeHas('kader.pemeriksaan.index'))
                    <a href="{{ route('kader.pemeriksaan.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white px-5 py-3 text-sm font-black text-emerald-700">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                @endif

                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white">
                        <i class="fa-solid fa-chart-simple"></i>
                        Dashboard
                    </a>
                @endif
            </div>
        </div>
    </section>

    @if($errors->any())
        <section class="rounded-[22px] border border-rose-100 bg-rose-50 p-4 text-sm font-bold text-rose-700">
            <div class="mb-2 flex items-center gap-2 font-black">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Data belum lengkap
            </div>
            <ul class="ml-5 list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <form method="POST" action="{{ route('kader.pemeriksaan.store') }}" id="formPengukuran" class="grid grid-cols-1 gap-5 xl:grid-cols-12" novalidate>
        @csrf

        <input type="hidden" name="pasien_id" id="pasien_id" value="{{ $pasienIdAwal }}">

        <section class="ukur-panel rounded-[26px] p-4 sm:p-5 xl:col-span-4">
            <h2 class="text-lg font-black text-slate-900">1. Pilih Sasaran</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Pilih kategori, lalu pilih warga dari data sasaran.</p>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Kategori</label>

                    <div class="space-y-3">
                        @foreach($kategoriMenus as $key => $item)
    <div>
        <input
            type="radio"
            name="kategori_pasien"
            value="{{ $key }}"
            id="kategori_{{ $key }}"
            class="sr-only kategori-radio"
            {{ $kategoriAwal === $key ? 'checked' : '' }}
        >

        <button
            type="button"
            class="kategori-card kategori-btn {{ $kategoriAwal === $key ? 'active' : '' }} flex w-full cursor-pointer select-none gap-3 rounded-2xl p-4 text-left"
            data-category-card="{{ $key }}"
            data-category="{{ $key }}"
            aria-pressed="{{ $kategoriAwal === $key ? 'true' : 'false' }}"
        >
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                <i class="fa-solid {{ $item['icon'] }}"></i>
            </div>

            <div>
                <p class="text-sm font-black text-slate-900">{{ $item['label'] }}</p>
                <p class="mt-1 text-xs font-semibold leading-5 text-slate-400">{{ $item['desc'] }}</p>
            </div>
        </button>
    </div>
@endforeach
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Cari Warga</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                        <input type="text" id="searchPasien" class="ukur-input h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700" placeholder="Cari nama atau NIK..." autocomplete="off">
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="block text-xs font-black uppercase tracking-[.12em] text-slate-400">Daftar Warga</label>
                        <span id="pasienCounter" class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-700">Memuat...</span>
                    </div>

                    <div id="patientList" class="warga-list space-y-2">
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center text-sm font-bold text-slate-400">
                            Memuat data warga...
                        </div>
                    </div>
                </div>

                <div id="selectedPatientBox" class="hidden rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">Warga Terpilih</p>
                    <h3 id="selectedPatientName" class="mt-2 text-sm font-black text-slate-900">-</h3>
                    <p id="selectedPatientNik" class="mt-1 text-xs font-bold text-slate-500">-</p>
                </div>
            </div>
        </section>

        <section class="ukur-panel rounded-[26px] p-4 sm:p-5 xl:col-span-8">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">2. Isi Pengukuran</h2>
                    <p id="categoryHelp" class="mt-1 text-xs font-bold leading-5 text-slate-500">
                        Isi data sesuai kategori yang dipilih.
                    </p>
                </div>

                <span class="w-fit rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-[.12em] text-amber-700">
                    Menunggu Review
                </span>
            </div>

            <div class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs font-bold leading-5 text-emerald-700">
                Data yang dicatat Kader menjadi pengukuran awal dan akan ditinjau Bidan sebelum digunakan sebagai dasar pemeriksaan lanjutan.
            </div>

            <div class="space-y-5">
                <div class="rounded-[22px] border border-slate-200 bg-white p-4">
                    <h3 class="mb-3 text-sm font-black text-slate-900">Tanggal Pengukuran</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                                Tanggal <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_periksa" id="tanggal_periksa" value="{{ old('tanggal_periksa', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="ukur-input js-required h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700" data-label="Tanggal pengukuran">
                        </div>
                    </div>
                </div>

                {{-- BALITA --}}
                <section class="field-section rounded-[22px] border border-slate-200 bg-white p-4" data-field-category="balita" {{ $kategoriAwal !== 'balita' ? 'hidden' : '' }}>
                    <h3 class="mb-3 text-sm font-black text-slate-900">Parameter Balita / Anak</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Berat Badan <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="berat_badan" value="{{ old('berat_badan') }}" placeholder="20" class="ukur-input js-required h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="Berat badan">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">kg</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tinggi / Panjang Badan <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="tinggi_badan" value="{{ old('tinggi_badan') }}" placeholder="100" class="ukur-input js-required h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="Tinggi atau panjang badan">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Suhu Tubuh</label>
                            <input type="number" step="0.1" name="suhu_tubuh" value="{{ old('suhu_tubuh') }}" placeholder="36.5" class="ukur-input h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Lingkar Kepala <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_kepala" value="{{ old('lingkar_kepala') }}" placeholder="45" class="ukur-input js-required h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="Lingkar kepala">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">LiLA <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_lengan" value="{{ old('lingkar_lengan') }}" placeholder="14" class="ukur-input js-required h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="LiLA">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- REMAJA --}}
                <section class="field-section rounded-[22px] border border-slate-200 bg-white p-4" data-field-category="remaja" {{ $kategoriAwal !== 'remaja' ? 'hidden' : '' }}>
                    <h3 class="mb-3 text-sm font-black text-slate-900">Parameter Remaja</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Berat Badan <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="berat_badan" value="{{ old('berat_badan') }}" placeholder="55" class="ukur-input js-required js-imt-weight h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="Berat badan">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">kg</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tinggi Badan <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="tinggi_badan" value="{{ old('tinggi_badan') }}" placeholder="160" class="ukur-input js-required js-imt-height h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="Tinggi badan">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">IMT Otomatis</label>
                            <input type="text" id="imt_preview" value="-" class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 text-sm font-black text-slate-700" readonly>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">LiLA <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_lengan" value="{{ old('lingkar_lengan') }}" placeholder="24" class="ukur-input js-required h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="LiLA">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Lingkar Perut <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_perut" value="{{ old('lingkar_perut') }}" placeholder="75" class="ukur-input js-required h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="Lingkar perut">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tekanan Darah <span class="text-rose-500">*</span></label>
                            <input type="text" name="tekanan_darah" value="{{ old('tekanan_darah') }}" placeholder="120/80" class="ukur-input js-required js-blood h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700" data-label="Tekanan darah">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Hemoglobin / Hb</label>
                            <input type="number" step="0.1" name="hemoglobin" value="{{ old('hemoglobin') }}" placeholder="13.5" class="ukur-input h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                        </div>
                    </div>
                </section>

                {{-- LANSIA --}}
                <section class="field-section rounded-[22px] border border-slate-200 bg-white p-4" data-field-category="lansia" {{ $kategoriAwal !== 'lansia' ? 'hidden' : '' }}>
                    <h3 class="mb-3 text-sm font-black text-slate-900">Parameter Lansia</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Berat Badan <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="berat_badan" value="{{ old('berat_badan') }}" placeholder="60" class="ukur-input js-required js-imt-weight h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="Berat badan">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">kg</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tinggi Badan <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="tinggi_badan" value="{{ old('tinggi_badan') }}" placeholder="160" class="ukur-input js-required js-imt-height h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="Tinggi badan">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">IMT Otomatis</label>
                            <input type="text" id="imt_preview_lansia" value="-" class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 text-sm font-black text-slate-700" readonly>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Lingkar Perut <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_perut" value="{{ old('lingkar_perut') }}" placeholder="85" class="ukur-input js-required h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700" data-label="Lingkar perut">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tekanan Darah <span class="text-rose-500">*</span></label>
                            <input type="text" name="tekanan_darah" value="{{ old('tekanan_darah') }}" placeholder="120/80" class="ukur-input js-required js-blood h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700" data-label="Tekanan darah">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Gula Darah</label>
                            <input type="number" step="0.1" name="gula_darah" value="{{ old('gula_darah') }}" placeholder="120" class="ukur-input h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Kolesterol</label>
                            <input type="number" step="1" name="kolesterol" value="{{ old('kolesterol') }}" placeholder="180" class="ukur-input h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Asam Urat</label>
                            <input type="number" step="0.1" name="asam_urat" value="{{ old('asam_urat') }}" placeholder="6.5" class="ukur-input h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Hemoglobin / Hb</label>
                            <input type="number" step="0.1" name="hemoglobin" value="{{ old('hemoglobin') }}" placeholder="13.5" class="ukur-input h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                        </div>

                        <div class="md:col-span-3">
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tingkat Kemandirian <span class="text-rose-500">*</span></label>
                            <select name="tingkat_kemandirian" class="ukur-input js-required h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700" data-label="Tingkat kemandirian">
                                <option value="">Pilih tingkat kemandirian</option>
                                <option value="mandiri" {{ old('tingkat_kemandirian') === 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                                <option value="bantuan_sebagian" {{ old('tingkat_kemandirian') === 'bantuan_sebagian' ? 'selected' : '' }}>Perlu Bantuan Sebagian</option>
                                <option value="bantuan_penuh" {{ old('tingkat_kemandirian') === 'bantuan_penuh' ? 'selected' : '' }}>Perlu Bantuan Penuh</option>
                            </select>
                        </div>
                    </div>
                </section>

                <div class="rounded-[22px] border border-slate-200 bg-white p-4">
                    <h3 class="mb-3 text-sm font-black text-slate-900">Catatan Kader</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Keluhan</label>
                            <textarea name="keluhan" rows="4" class="ukur-input w-full rounded-2xl px-4 py-3 text-sm font-bold text-slate-700" placeholder="Contoh: pusing, demam, batuk, atau tidak ada keluhan...">{{ old('keluhan') }}</textarea>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Catatan Tambahan</label>
                            <textarea name="catatan_kader" rows="4" class="ukur-input w-full rounded-2xl px-4 py-3 text-sm font-bold text-slate-700" placeholder="Catatan pengukuran atau informasi pendukung...">{{ old('catatan_kader') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-[24px] border border-emerald-100 bg-white p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-black text-slate-900">Simpan Pengukuran Fisik</p>
                            <p class="mt-1 text-xs font-bold text-slate-400">Data lengkap akan disimpan sebagai Menunggu Review Bidan.</p>
                        </div>

                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-[0_10px_20px_rgba(5,150,105,.18)]">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Simpan Pengukuran
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const apiUrl = @json($apiPasienUrl);
    const initialPasienId = @json((string) $pasienIdAwal);

    const patientList = document.getElementById('patientList');
    const pasienCounter = document.getElementById('pasienCounter');
    const searchPasien = document.getElementById('searchPasien');
    const pasienIdInput = document.getElementById('pasien_id');
    const selectedBox = document.getElementById('selectedPatientBox');
    const selectedName = document.getElementById('selectedPatientName');
    const selectedNik = document.getElementById('selectedPatientNik');
    const categoryHelp = document.getElementById('categoryHelp');
    const toast = document.getElementById('customToast');
    const toastText = document.getElementById('customToastText');
    const form = document.getElementById('formPengukuran');

    let currentKategori = document.querySelector('.kategori-radio:checked')?.value || 'balita';
    let patients = [];
    let controller = null;
    let searchTimer = null;
    let toastTimer = null;

    const helpText = {
        balita: 'Balita tidak menggunakan IMT pada form ini. Isi BB, TB/PB, lingkar kepala, dan LiLA.',
        remaja: 'Remaja menggunakan IMT otomatis dari berat dan tinggi badan.',
        lansia: 'Lansia menggunakan IMT otomatis dan parameter tambahan sesuai pemeriksaan yang tersedia.',
    };

    const normalize = value => String(value || '').toLowerCase().trim();

    const escapeHtml = value => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const showToast = message => {
        if (!toast || !toastText) return;

        toastText.textContent = message;
        toast.classList.add('show');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    };

    const setPatientMessage = (message, error = false) => {
        if (!patientList) return;

        patientList.innerHTML = `
            <div class="rounded-2xl border border-dashed ${error ? 'border-rose-100 bg-rose-50 text-rose-600' : 'border-slate-200 bg-slate-50 text-slate-400'} p-5 text-center text-sm font-bold">
                ${escapeHtml(message)}
            </div>
        `;
    };

    const clearSelectedPatient = () => {
        pasienIdInput.value = '';
        selectedBox?.classList.add('hidden');
        if (selectedName) selectedName.textContent = '-';
        if (selectedNik) selectedNik.textContent = '-';
    };

    const updateFields = () => {
        document.querySelectorAll('[data-category-card]').forEach(card => {
            const active = card.dataset.categoryCard === currentKategori;
            card.classList.toggle('active', active);
            card.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        document.querySelectorAll('.field-section').forEach(section => {
            const active = section.dataset.fieldCategory === currentKategori;
            section.hidden = !active;

            section.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = !active;
                input.classList.remove('is-error');
            });
        });

        if (categoryHelp) {
            categoryHelp.textContent = helpText[currentKategori] || 'Isi data sesuai kategori yang dipilih.';
        }

        updateImt();
    };

    const updateImt = () => {
        const activeSection = document.querySelector(`.field-section[data-field-category="${currentKategori}"]`);
        if (!activeSection) return;

        const preview = activeSection.querySelector('input[readonly]');
        if (!preview) return;

        const weight = activeSection.querySelector('.js-imt-weight');
        const height = activeSection.querySelector('.js-imt-height');

        const berat = parseFloat(weight?.value || 0);
        const tinggi = parseFloat(height?.value || 0);

        if (!berat || !tinggi) {
            preview.value = '-';
            return;
        }

        const meter = tinggi / 100;
        const imt = berat / (meter * meter);

        preview.value = Number.isFinite(imt) ? imt.toFixed(2) : '-';
    };

    const renderPatients = () => {
        const keyword = normalize(searchPasien?.value);
        const filtered = patients.filter(item => {
            const nama = normalize(item.nama || item.nama_lengkap);
            const nik = normalize(item.nik);
            return nama.includes(keyword) || nik.includes(keyword);
        });

        if (pasienCounter) {
            pasienCounter.textContent = `${filtered.length} dari ${patients.length} data`;
        }

        if (!filtered.length) {
            setPatientMessage(keyword ? 'Tidak ada warga yang cocok dengan pencarian.' : 'Belum ada data warga pada kategori ini.');
            return;
        }

        patientList.innerHTML = filtered.map(item => {
            const id = escapeHtml(item.id);
            const nama = escapeHtml(item.nama || item.nama_lengkap || 'Tanpa Nama');
            const nik = escapeHtml(item.nik || '-');
            const active = String(item.id) === String(pasienIdInput.value) ? 'active' : '';

            return `
                <button type="button" class="warga-item ${active} w-full rounded-2xl p-3 text-left" data-id="${id}">
                    <div class="flex items-center gap-3">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-900">${nama}</p>
                            <p class="mt-1 text-xs font-bold text-slate-400">${nik}</p>
                        </div>
                    </div>
                </button>
            `;
        }).join('');

        document.querySelectorAll('.warga-item').forEach(button => {
            button.addEventListener('click', () => {
                const selected = patients.find(item => String(item.id) === String(button.dataset.id));
                if (!selected) return;

                pasienIdInput.value = selected.id;

                selectedBox?.classList.remove('hidden');
                selectedName.textContent = selected.nama || selected.nama_lengkap || 'Tanpa Nama';
                selectedNik.textContent = `NIK: ${selected.nik || '-'}`;

                document.querySelectorAll('.warga-item').forEach(item => {
                    item.classList.toggle('active', String(item.dataset.id) === String(selected.id));
                });
            });
        });
    };

    const loadPatients = async () => {
        if (controller) controller.abort();

        controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 8000);

        clearSelectedPatient();

        if (pasienCounter) pasienCounter.textContent = 'Memuat...';
        setPatientMessage('Memuat data warga...');

        try {
            const url = new URL(apiUrl, window.location.origin);
            url.searchParams.set('kategori', currentKategori);
            url.searchParams.set('_', Date.now());

            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controller.signal,
            });

            clearTimeout(timeout);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const result = await response.json();

            if (Array.isArray(result)) {
                patients = result;
            } else if (Array.isArray(result.data)) {
                patients = result.data;
            } else if (result.data && Array.isArray(result.data.data)) {
                patients = result.data.data;
            } else {
                patients = [];
            }

            renderPatients();

            if (initialPasienId) {
                const selected = patients.find(item => String(item.id) === String(initialPasienId));
                if (selected) {
                    pasienIdInput.value = selected.id;
                    selectedBox?.classList.remove('hidden');
                    selectedName.textContent = selected.nama || selected.nama_lengkap || 'Tanpa Nama';
                    selectedNik.textContent = `NIK: ${selected.nik || '-'}`;
                    renderPatients();
                }
            }
        } catch (error) {
            clearTimeout(timeout);

            if (error.name === 'AbortError') {
                patients = [];
                if (pasienCounter) pasienCounter.textContent = 'Timeout';
                setPatientMessage('Data warga terlalu lama dimuat. Coba refresh halaman atau cek route API pasien.', true);
                return;
            }

            patients = [];
            if (pasienCounter) pasienCounter.textContent = 'Gagal';
            setPatientMessage('Gagal memuat data warga. Periksa route API pasien.', true);
        }
    };

    const validateForm = () => {
        document.querySelectorAll('.is-error').forEach(el => {
            el.classList.remove('is-error');
        });

        if (!pasienIdInput.value) {
            showToast('Pilih warga dari daftar sasaran terlebih dahulu.');
            searchPasien?.focus();
            return false;
        }

        const activeSection = document.querySelector(`.field-section[data-field-category="${currentKategori}"]`);

        if (!activeSection) {
            showToast('Kategori belum valid. Pilih kategori sasaran terlebih dahulu.');
            return false;
        }

        const requiredFields = [
            document.getElementById('tanggal_periksa'),
            ...activeSection.querySelectorAll('.js-required')
        ].filter(Boolean);

        const empty = requiredFields.find(input => !String(input.value || '').trim());

        if (empty) {
            empty.classList.add('is-error');
            empty.focus();
            showToast(`${empty.dataset.label || 'Data wajib'} wajib diisi terlebih dahulu.`);
            return false;
        }

        const blood = activeSection.querySelector('.js-blood');
        const bloodValue = String(blood?.value || '').trim();

        if (blood && bloodValue && !/^[0-9]{2,3}\/[0-9]{2,3}$/.test(bloodValue)) {
            blood.classList.add('is-error');
            blood.focus();
            showToast('Format tekanan darah harus seperti 120/80.');
            return false;
        }

        return true;
    };

    document.querySelectorAll('.kategori-btn').forEach(button => {
        button.addEventListener('click', () => {
            const nextKategori = button.dataset.category;

            if (!nextKategori || nextKategori === currentKategori) {
                return;
            }

            currentKategori = nextKategori;

            const radio = document.querySelector(`.kategori-radio[value="${currentKategori}"]`);
            if (radio) radio.checked = true;

            if (searchPasien) searchPasien.value = '';

            updateFields();
            loadPatients();
        });
    });

    searchPasien?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(renderPatients, 90);
    });

    document.addEventListener('input', event => {
        if (event.target.classList.contains('js-imt-weight') || event.target.classList.contains('js-imt-height')) {
            updateImt();
        }

        if (event.target.classList.contains('is-error')) {
            event.target.classList.remove('is-error');
        }
    });

    form?.addEventListener('submit', event => {
        if (!validateForm()) {
            event.preventDefault();
        }
    });

    updateFields();
    loadPatients();
});
</script>
@endpush