@extends('layouts.kader')

@section('title', 'Detail Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $pasien = $pemeriksaan->kunjungan->pasien ?? null;

    $namaPasien = $pasien->nama_lengkap
        ?? $pasien->nama
        ?? $pemeriksaan->nama_pasien
        ?? 'Data sasaran';

    $nikPasien = $pasien->nik
        ?? $pemeriksaan->nik_pasien
        ?? '-';

    $kategori = $pemeriksaan->kategori_pasien ?? 'sasaran';

    $kategoriLabel = match($kategori) {
        'balita' => 'Balita / Anak',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        default => 'Sasaran',
    };

    $kategoriIcon = match($kategori) {
        'balita' => 'fa-child-reaching',
        'remaja' => 'fa-user-graduate',
        'lansia' => 'fa-person-cane',
        default => 'fa-users',
    };

    $statusRaw = strtolower($pemeriksaan->status_verifikasi ?? 'pending');

    $isReviewed = in_array($statusRaw, [
        'verified',
        'terverifikasi',
        'approved',
        'valid',
        'tervalidasi',
        'sudah_ditinjau',
    ]);

    $needFix = in_array($statusRaw, [
        'ditolak',
        'rejected',
        'direvisi',
        'perlu_perbaikan',
    ]);

    $statusData = match(true) {
        $isReviewed => [
            'label' => 'Sudah Ditinjau',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'icon' => 'fa-circle-check',
        ],
        $needFix => [
            'label' => 'Perlu Perbaikan',
            'class' => 'bg-rose-50 text-rose-700 border-rose-100',
            'icon' => 'fa-circle-exclamation',
        ],
        default => [
            'label' => 'Menunggu Review',
            'class' => 'bg-amber-50 text-amber-700 border-amber-100',
            'icon' => 'fa-clock',
        ],
    };

    $tanggal = !empty($pemeriksaan->tanggal_periksa)
        ? Carbon::parse($pemeriksaan->tanggal_periksa)->translatedFormat('l, d F Y')
        : '-';

    $tanggalSingkat = !empty($pemeriksaan->tanggal_periksa)
        ? Carbon::parse($pemeriksaan->tanggal_periksa)->translatedFormat('d M Y')
        : '-';

    $namaPetugas = $pemeriksaan->kunjungan->petugas->name
        ?? $pemeriksaan->kunjungan->petugas->nama
        ?? auth()->user()->name
        ?? 'Kader';

    $namaBidan = $pemeriksaan->verifikator->name
        ?? $pemeriksaan->verifikator->nama
        ?? null;

    $catatanBidan = $pemeriksaan->catatan_validasi
        ?? $pemeriksaan->catatan_bidan
        ?? $pemeriksaan->catatan_review
        ?? null;

    $tanggalReview = $pemeriksaan->tanggal_validasi
        ?? $pemeriksaan->verified_at
        ?? $pemeriksaan->reviewed_at
        ?? null;

    $tanggalReview = !empty($tanggalReview)
        ? Carbon::parse($tanggalReview)->translatedFormat('d F Y')
        : null;

    $kemandirianLabel = match($pemeriksaan->tingkat_kemandirian ?? '') {
        'mandiri' => 'Mandiri',
        'bantuan_sebagian' => 'Perlu Bantuan Sebagian',
        'bantuan_penuh' => 'Perlu Bantuan Penuh',
        default => '-',
    };

    $imt = $pemeriksaan->imt;

    if (empty($imt) && !empty($pemeriksaan->berat_badan) && !empty($pemeriksaan->tinggi_badan) && $kategori !== 'balita') {
        $meter = ((float) $pemeriksaan->tinggi_badan) / 100;
        $imt = $meter > 0 ? round(((float) $pemeriksaan->berat_badan) / ($meter * $meter), 2) : null;
    }

    $routeHas = fn ($name) => Route::has($name);
@endphp

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

    .show-page {
        position: relative;
        isolation: isolate;
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        animation: pageIn .18s ease-out both;
    }

    .show-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 6%, rgba(16,185,129,.12), transparent 26%),
            radial-gradient(circle at 92% 10%, rgba(245,158,11,.10), transparent 24%),
            linear-gradient(135deg, #f7fffc 0%, #f8fafc 55%, #fffaf1 100%);
    }

    @keyframes pageIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .hero {
        border: 1px solid rgba(167,243,208,.65);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.14), transparent 28%),
            radial-gradient(circle at 90% 16%, rgba(245,158,11,.12), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.96), rgba(236,253,245,.78));
        box-shadow: 0 14px 34px rgba(15,23,42,.055);
    }

    .panel {
        border: 1px solid rgba(226,232,240,.88);
        background: rgba(255,255,255,.95);
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
    }

    .badge-title {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        border-radius: 999px;
        border: 1px solid rgba(16,185,129,.18);
        background: rgba(236,253,245,.88);
        color: #047857;
        padding: .55rem .85rem;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .btn-soft {
        border-radius: 16px;
        font-weight: 900;
        transition: background .15s ease, transform .15s ease, border-color .15s ease;
    }

    .btn-soft:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        color: white;
        background: linear-gradient(135deg, #059669, #10b981);
        box-shadow: 0 10px 20px rgba(5,150,105,.14);
    }

    .btn-dark {
        color: white;
        background: linear-gradient(135deg, #0f172a, #1e293b);
        box-shadow: 0 10px 20px rgba(15,23,42,.12);
    }

    .btn-outline {
        border: 1px solid rgba(16,185,129,.18);
        color: #047857;
        background: white;
    }

    .btn-outline:hover {
        background: #ecfdf5;
    }

    .stat-card {
        border: 1px solid rgba(226,232,240,.85);
        background: white;
        box-shadow: 0 8px 20px rgba(15,23,42,.035);
    }

    .stat-emerald {
        background: linear-gradient(145deg, #ffffff, #ecfdf5);
        border-color: rgba(16,185,129,.18);
    }

    .stat-sky {
        background: linear-gradient(145deg, #ffffff, #f0f9ff);
        border-color: rgba(14,165,233,.14);
    }

    .stat-amber {
        background: linear-gradient(145deg, #ffffff, #fff8eb);
        border-color: rgba(245,158,11,.16);
    }

    .stat-rose {
        background: linear-gradient(145deg, #ffffff, #fff1f2);
        border-color: rgba(244,63,94,.14);
    }

    .info-row {
        border: 1px solid rgba(226,232,240,.82);
        background: #fff;
    }

    .metric-box {
        border: 1px solid rgba(226,232,240,.82);
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15,23,42,.032);
    }

    .metric-box.is-empty {
        background: #f8fafc;
        color: #94a3b8;
    }

    .review-box {
        border: 1px solid rgba(16,185,129,.16);
        background: #ecfdf5;
        color: #047857;
    }

    .fix-box {
        border: 1px solid rgba(244,63,94,.16);
        background: #fff1f2;
        color: #be123c;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #printArea,
        #printArea * {
            visibility: visible !important;
        }

        #printArea {
            position: absolute;
            inset: 0;
            width: 100%;
            background: white !important;
        }

        .no-print {
            display: none !important;
        }
    }

    @media (max-width: 640px) {
        .btn-soft:hover {
            transform: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .show-page,
        .btn-soft {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="show-page space-y-5">

    {{-- HERO --}}
    <section class="hero rounded-[28px] p-5 sm:p-6 no-print">
        <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="badge-title mb-3">
                    <i class="fa-solid fa-file-medical"></i>
                    Detail Pengukuran
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Detail Pengukuran Fisik
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Rincian hasil pengukuran awal {{ $kategoriLabel }}. Data ini digunakan sebagai bahan review Bidan dan dasar pemeriksaan lanjutan.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                @if($routeHas('kader.pemeriksaan.index'))
                    <a href="{{ route('kader.pemeriksaan.index') }}" class="btn-soft btn-outline inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                @endif

                @if(!$isReviewed && $routeHas('kader.pemeriksaan.edit'))
                    <a href="{{ route('kader.pemeriksaan.edit', $pemeriksaan->id) }}" class="btn-soft btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid {{ $needFix ? 'fa-screwdriver-wrench' : 'fa-pen-to-square' }}"></i>
                        {{ $needFix ? 'Perbaiki' : 'Edit' }}
                    </a>
                @endif

                <button type="button" onclick="window.print()" class="btn-soft btn-dark col-span-2 inline-flex items-center justify-center gap-2 px-5 py-3 text-sm sm:col-span-1">
                    <i class="fa-solid fa-print"></i>
                    Cetak
                </button>
            </div>
        </div>
    </section>

    <div id="printArea" class="space-y-5">

        {{-- IDENTITAS UTAMA --}}
        <section class="panel rounded-[28px] p-5 sm:p-6">
            <div class="grid gap-5 xl:grid-cols-[1.3fr_.7fr] xl:items-center">
                <div class="flex items-start gap-4">
                    <div class="grid h-16 w-16 shrink-0 place-items-center rounded-3xl bg-emerald-50 text-emerald-700">
                        <i class="fa-solid {{ $kategoriIcon }} text-xl"></i>
                    </div>

                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[.1em] text-emerald-700">
                                {{ $kategoriLabel }}
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $statusData['class'] }}">
                                <i class="fa-solid {{ $statusData['icon'] }}"></i>
                                {{ $statusData['label'] }}
                            </span>
                        </div>

                        <h2 class="text-xl font-black tracking-[-.03em] text-slate-900 sm:text-2xl">
                            {{ $namaPasien }}
                        </h2>

                        <p class="mt-2 text-sm font-bold text-slate-500">
                            <i class="fa-solid fa-id-card mr-1"></i>
                            {{ $nikPasien }}
                            <span class="mx-2">•</span>
                            {{ $tanggal }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Petugas</p>
                        <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $namaPetugas }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tanggal</p>
                        <p class="mt-2 text-sm font-black text-slate-900">{{ $tanggalSingkat }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- STAT RINGKAS --}}
        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            <div class="stat-card stat-emerald rounded-[22px] p-4">
                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Berat</p>
                <h3 class="mt-2 text-2xl font-black text-slate-900">
                    {{ $pemeriksaan->berat_badan ?? '-' }}
                    <span class="text-sm text-slate-400">kg</span>
                </h3>
            </div>

            <div class="stat-card stat-sky rounded-[22px] p-4">
                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tinggi</p>
                <h3 class="mt-2 text-2xl font-black text-slate-900">
                    {{ $pemeriksaan->tinggi_badan ?? '-' }}
                    <span class="text-sm text-slate-400">cm</span>
                </h3>
            </div>

            <div class="stat-card stat-amber rounded-[22px] p-4">
                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">IMT</p>
                <h3 class="mt-2 text-2xl font-black text-slate-900">
                    {{ $imt ?? '-' }}
                </h3>
            </div>

            <div class="stat-card {{ $needFix ? 'stat-rose' : 'stat-emerald' }} rounded-[22px] p-4">
                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Status Review</p>
                <h3 class="mt-2 text-sm font-black {{ $needFix ? 'text-rose-600' : 'text-emerald-700' }}">
                    {{ $statusData['label'] }}
                </h3>
            </div>
        </section>

        {{-- DETAIL PARAMETER --}}
        <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

            {{-- PARAMETER --}}
            <div class="panel rounded-[28px] p-5 xl:col-span-8">
                <div class="mb-4">
                    <h3 class="text-lg font-black text-slate-900">Parameter Pengukuran</h3>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        Nilai yang kosong berarti tidak dilakukan atau tidak diperlukan pada sesi tersebut.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @php
    $baseMetrics = [
        [
            'label' => 'Berat Badan',
            'value' => $pemeriksaan->berat_badan,
            'unit' => 'kg',
            'icon' => 'fa-weight-scale',
        ],
        [
            'label' => $kategori === 'balita' ? 'Tinggi / Panjang Badan' : 'Tinggi Badan',
            'value' => $pemeriksaan->tinggi_badan,
            'unit' => 'cm',
            'icon' => 'fa-ruler-vertical',
        ],
    ];

    $categoryMetrics = match($kategori) {
        'balita' => [
            [
                'label' => 'Suhu Tubuh',
                'value' => $pemeriksaan->suhu_tubuh,
                'unit' => '°C',
                'icon' => 'fa-temperature-half',
            ],
            [
                'label' => 'Lingkar Kepala',
                'value' => $pemeriksaan->lingkar_kepala,
                'unit' => 'cm',
                'icon' => 'fa-child',
            ],
            [
                'label' => 'LiLA',
                'value' => $pemeriksaan->lingkar_lengan,
                'unit' => 'cm',
                'icon' => 'fa-ruler',
            ],
        ],

        'remaja' => [
            [
                'label' => 'IMT',
                'value' => $imt,
                'unit' => '',
                'icon' => 'fa-calculator',
            ],
            [
                'label' => 'LiLA',
                'value' => $pemeriksaan->lingkar_lengan,
                'unit' => 'cm',
                'icon' => 'fa-ruler',
            ],
            [
                'label' => 'Lingkar Perut',
                'value' => $pemeriksaan->lingkar_perut,
                'unit' => 'cm',
                'icon' => 'fa-ruler-combined',
            ],
            [
                'label' => 'Tekanan Darah',
                'value' => $pemeriksaan->tekanan_darah,
                'unit' => '',
                'icon' => 'fa-heart-pulse',
            ],
            [
                'label' => 'Hemoglobin / Hb',
                'value' => $pemeriksaan->hemoglobin,
                'unit' => 'g/dL',
                'icon' => 'fa-notes-medical',
            ],
            [
                'label' => 'Suhu Tubuh',
                'value' => $pemeriksaan->suhu_tubuh,
                'unit' => '°C',
                'icon' => 'fa-temperature-half',
            ],
        ],

        'lansia' => [
            [
                'label' => 'IMT',
                'value' => $imt,
                'unit' => '',
                'icon' => 'fa-calculator',
            ],
            [
                'label' => 'Lingkar Perut',
                'value' => $pemeriksaan->lingkar_perut,
                'unit' => 'cm',
                'icon' => 'fa-ruler-combined',
            ],
            [
                'label' => 'Tekanan Darah',
                'value' => $pemeriksaan->tekanan_darah,
                'unit' => '',
                'icon' => 'fa-heart-pulse',
            ],
            [
                'label' => 'Gula Darah',
                'value' => $pemeriksaan->gula_darah,
                'unit' => 'mg/dL',
                'icon' => 'fa-droplet',
            ],
            [
                'label' => 'Kolesterol',
                'value' => $pemeriksaan->kolesterol,
                'unit' => 'mg/dL',
                'icon' => 'fa-vial',
            ],
            [
                'label' => 'Asam Urat',
                'value' => $pemeriksaan->asam_urat,
                'unit' => 'mg/dL',
                'icon' => 'fa-flask',
            ],
            [
                'label' => 'Hemoglobin / Hb',
                'value' => $pemeriksaan->hemoglobin,
                'unit' => 'g/dL',
                'icon' => 'fa-notes-medical',
            ],
            [
                'label' => 'Tingkat Kemandirian',
                'value' => $kemandirianLabel,
                'unit' => '',
                'icon' => 'fa-person-walking',
            ],
        ],

        default => [],
    };

    $metrics = array_merge($baseMetrics, $categoryMetrics);
@endphp

@foreach($metrics as $metric)
    @php
        $isEmpty = blank($metric['value']) || $metric['value'] === '-';
    @endphp

    <div class="metric-box {{ $isEmpty ? 'is-empty' : '' }} rounded-[20px] p-4">
        <div class="mb-3 flex items-center justify-between">
            <div class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                <i class="fa-solid {{ $metric['icon'] }}"></i>
            </div>

            @if($isEmpty)
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-black uppercase tracking-[.08em] text-slate-400">
                    Kosong
                </span>
            @endif
        </div>

        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">
            {{ $metric['label'] }}
        </p>

        <p class="mt-2 text-lg font-black text-slate-900">
            {{ $metric['value'] ?: '-' }}
            @if(!$isEmpty && !empty($metric['unit']))
                <span class="text-xs text-slate-400">{{ $metric['unit'] }}</span>
            @endif
        </p>
    </div>
@endforeach
                </div>
            </div>

            {{-- REVIEW DAN CATATAN --}}
            <div class="space-y-5 xl:col-span-4">

                <div class="panel rounded-[28px] p-5">
                    <div class="mb-4">
                        <h3 class="text-lg font-black text-slate-900">Status Review Bidan</h3>
                        <p class="mt-1 text-xs font-bold text-slate-400">
                            Status ini menunjukkan tahapan peninjauan oleh Bidan.
                        </p>
                    </div>

                    <div class="{{ $needFix ? 'fix-box' : 'review-box' }} rounded-[22px] p-4">
                        <div class="flex items-center gap-3">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70">
                                <i class="fa-solid {{ $statusData['icon'] }}"></i>
                            </div>

                            <div>
                                <p class="text-sm font-black">{{ $statusData['label'] }}</p>

                                @if($namaBidan)
                                    <p class="mt-1 text-xs font-bold opacity-80">
                                        Oleh {{ $namaBidan }}
                                    </p>
                                @endif

                                @if($tanggalReview)
                                    <p class="mt-1 text-xs font-bold opacity-80">
                                        {{ $tanggalReview }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($isReviewed)
                        <p class="mt-3 rounded-2xl bg-slate-50 px-4 py-3 text-xs font-bold leading-5 text-slate-500">
                            Data sudah ditinjau Bidan dan dikunci agar riwayat pengukuran tidak berubah sembarangan.
                        </p>
                    @elseif($needFix)
                        <p class="mt-3 rounded-2xl bg-rose-50 px-4 py-3 text-xs font-bold leading-5 text-rose-600">
                            Data perlu diperbaiki sesuai catatan Bidan. Setelah diperbaiki, status akan kembali menjadi Menunggu Review.
                        </p>
                    @else
                        <p class="mt-3 rounded-2xl bg-amber-50 px-4 py-3 text-xs font-bold leading-5 text-amber-700">
                            Data sedang menunggu review Bidan sebagai bahan pemeriksaan lanjutan.
                        </p>
                    @endif
                </div>

                <div class="panel rounded-[28px] p-5">
                    <div class="mb-4">
                        <h3 class="text-lg font-black text-slate-900">Catatan</h3>
                        <p class="mt-1 text-xs font-bold text-slate-400">
                            Catatan dari Kader dan Bidan.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="info-row rounded-2xl p-4">
                            <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Keluhan</p>
                            <p class="mt-2 text-sm font-bold leading-6 text-slate-700">
                                {{ $pemeriksaan->keluhan ?: 'Tidak ada keluhan yang dicatat.' }}
                            </p>
                        </div>

                        <div class="info-row rounded-2xl p-4">
                            <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Catatan Kader</p>
                            <p class="mt-2 text-sm font-bold leading-6 text-slate-700">
                                {{ $pemeriksaan->catatan_kader ?: 'Tidak ada catatan tambahan.' }}
                            </p>
                        </div>

                        <div class="info-row rounded-2xl p-4">
                            <p class="text-[10px] font-black uppercase tracking-[.12em] {{ $needFix ? 'text-rose-500' : 'text-slate-400' }}">
                                Catatan Bidan
                            </p>
                            <p class="mt-2 text-sm font-bold leading-6 {{ $needFix ? 'text-rose-700' : 'text-slate-700' }}">
                                {{ $catatanBidan ?: 'Belum ada catatan Bidan.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ACTION BAWAH --}}
    <section class="panel rounded-[26px] p-5 no-print">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900">Detail pengukuran siap digunakan</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Gunakan halaman ini untuk mengecek data sebelum dipakai dalam laporan atau review Bidan.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                @if($routeHas('kader.pemeriksaan.index'))
                    <a href="{{ route('kader.pemeriksaan.index') }}" class="btn-soft btn-outline inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-list"></i>
                        Daftar Pengukuran
                    </a>
                @endif

                @if(!$isReviewed && $routeHas('kader.pemeriksaan.edit'))
                    <a href="{{ route('kader.pemeriksaan.edit', $pemeriksaan->id) }}" class="btn-soft btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid {{ $needFix ? 'fa-screwdriver-wrench' : 'fa-pen-to-square' }}"></i>
                        {{ $needFix ? 'Perbaiki Data' : 'Edit Data' }}
                    </a>
                @endif

                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}" class="btn-soft btn-dark inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-house"></i>
                        Dashboard
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection