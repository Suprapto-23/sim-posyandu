@extends('layouts.kader')

@section('title', 'Detail Pengukuran Fisik')
@section('page-name', 'Detail Pengukuran Fisik')
@section('page-title', 'Detail Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $pasien = optional($pemeriksaan->kunjungan)->pasien;

    $kategori = strtolower((string) ($pemeriksaan->kategori_pasien ?? 'balita'));

    if (! in_array($kategori, ['balita', 'remaja', 'lansia'], true)) {
        $kategori = 'balita';
    }

    $namaPasien = $pasien->nama_lengkap
        ?? $pasien->nama
        ?? $pemeriksaan->nama_pasien
        ?? 'Tanpa Nama';

    $nikPasien = $pasien->nik
        ?? $pemeriksaan->nik_pasien
        ?? '-';

    $tanggal = $pemeriksaan->tanggal_periksa
        ? Carbon::parse($pemeriksaan->tanggal_periksa)->translatedFormat('l, d F Y')
        : '-';

    $statusRaw = strtolower((string) ($pemeriksaan->status_verifikasi ?? 'pending'));

    $statusType = 'pending';

    if (in_array($statusRaw, ['verified', 'terverifikasi', 'valid', 'approved', 'disetujui', 'selesai'], true)) {
        $statusType = 'verified';
    }

    if (in_array($statusRaw, ['ditolak', 'revisi', 'perlu_revisi', 'needs_revision', 'rejected', 'dikembalikan'], true)) {
        $statusType = 'revisi';
    }

    $statusMeta = match ($statusType) {
        'verified' => [
            'label' => 'Tervalidasi',
            'desc' => 'Sudah direview Bidan dan dikunci',
            'icon' => 'fa-circle-check',
            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'soft' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white',
            'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white',
            'editable' => false,
        ],
        'revisi' => [
            'label' => 'Perlu Revisi',
            'desc' => 'Dikembalikan Bidan untuk diperbaiki',
            'icon' => 'fa-rotate-left',
            'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            'soft' => 'border-rose-200 bg-gradient-to-br from-rose-50 via-orange-50 to-white',
            'solid' => 'bg-gradient-to-br from-rose-500 to-orange-500 text-white',
            'editable' => true,
        ],
        default => [
            'label' => 'Menunggu Review',
            'desc' => 'Belum direview Bidan',
            'icon' => 'fa-clock',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
            'soft' => 'border-amber-200 bg-gradient-to-br from-amber-50 via-yellow-50 to-white',
            'solid' => 'bg-gradient-to-br from-amber-500 to-orange-500 text-white',
            'editable' => true,
        ],
    };

    $kategoriMeta = match ($kategori) {
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'badge' => 'border-violet-200 bg-violet-50 text-violet-800',
            'solid' => 'bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'badge' => 'border-sky-200 bg-sky-50 text-sky-800',
            'solid' => 'bg-gradient-to-br from-sky-500 to-cyan-500 text-white',
        ],
        default => [
            'label' => 'Balita',
            'icon' => 'fa-child-reaching',
            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
            'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white',
        ],
    };

    $metric = function ($label, $value, $unit = '', $icon = 'fa-ruler') {
        return [
            'label' => $label,
            'value' => ($value === null || $value === '') ? '-' : trim($value . ' ' . $unit),
            'icon' => $icon,
        ];
    };

    $mainMetrics = [
        $metric('Berat Badan', $pemeriksaan->berat_badan, 'kg', 'fa-weight-scale'),
        $metric('Tinggi Badan', $pemeriksaan->tinggi_badan, 'cm', 'fa-ruler-vertical'),
        $metric('IMT', $pemeriksaan->imt, '', 'fa-chart-simple'),
        $metric('Suhu Tubuh', $pemeriksaan->suhu_tubuh, '°C', 'fa-temperature-half'),
    ];

    $extraMetrics = match ($kategori) {
    'balita' => [
        $metric('Lingkar Kepala', $pemeriksaan->lingkar_kepala, 'cm', 'fa-circle-notch'),
        $metric('Lingkar Lengan', $pemeriksaan->lingkar_lengan, 'cm', 'fa-ruler'),
    ],

    'remaja' => [
        $metric('Lingkar Lengan', $pemeriksaan->lingkar_lengan, 'cm', 'fa-ruler'),
        $metric('Lingkar Perut', $pemeriksaan->lingkar_perut, 'cm', 'fa-arrows-left-right'),
        $metric('Tekanan Darah', $pemeriksaan->tekanan_darah, '', 'fa-heart-pulse'),
        $metric('Hemoglobin', $pemeriksaan->hemoglobin, 'g/dL', 'fa-droplet'),
        $metric('Gula Darah', $pemeriksaan->gula_darah, 'mg/dL', 'fa-droplet'),
    ],

    'lansia' => [
        $metric('Lingkar Perut', $pemeriksaan->lingkar_perut, 'cm', 'fa-arrows-left-right'),
        $metric('Tekanan Darah', $pemeriksaan->tekanan_darah, '', 'fa-heart-pulse'),
        $metric('Gula Darah', $pemeriksaan->gula_darah, 'mg/dL', 'fa-droplet'),
        $metric('Kolesterol', $pemeriksaan->kolesterol, 'mg/dL', 'fa-vial'),
        $metric('Asam Urat', $pemeriksaan->asam_urat, 'mg/dL', 'fa-flask'),
        $metric(
            'Kemandirian',
            $pemeriksaan->tingkat_kemandirian
                ? Str::title(str_replace('_', ' ', $pemeriksaan->tingkat_kemandirian))
                : null,
            '',
            'fa-person-walking'
        ),
    ],

    default => [],
};
$parameterTitle = match ($kategori) {
    'balita' => 'Parameter tumbuh kembang',
    'remaja' => 'Parameter pengukuran remaja',
    'lansia' => 'Parameter kesehatan lansia',
    default => 'Parameter tambahan',
};

    $catatanBidan = $pemeriksaan->catatan_validasi
        ?? $pemeriksaan->catatan_bidan
        ?? $pemeriksaan->catatan_review
        ?? null;

    $verifikator = optional($pemeriksaan->verifikator)->name
        ?? optional($pemeriksaan->verifikator)->nama
        ?? null;

    $tanggalValidasi = $pemeriksaan->verified_at
        ?? $pemeriksaan->tanggal_validasi
        ?? $pemeriksaan->reviewed_at
        ?? null;
@endphp

@push('styles')
<style>
    html {
        scroll-behavior: auto !important;
    }

    html.pc-modal-open,
    body.pc-modal-open {
        overflow: hidden !important;
    }

    .pc-page {
        background:
            radial-gradient(circle at 8% 5%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 95% 8%, rgba(14, 165, 233, .13), transparent 26%),
            radial-gradient(circle at 50% 96%, rgba(251, 191, 36, .10), transparent 30%),
            linear-gradient(135deg, #f3fff9 0%, #eef9ff 48%, #f8fafc 100%);
    }

    .pc-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .pc-glass {
        background: rgba(255, 255, 255, .86);
        backdrop-filter: blur(9px);
        -webkit-backdrop-filter: blur(9px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .065);
    }

    .pc-soft-hover {
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .pc-balanced-grid {
    align-items: stretch !important;
}

.pc-left-panel {
    min-height: 100%;
    height: 100%;
}

.pc-side-panel {
    min-height: 100%;
    height: 100%;
    align-self: stretch;
}

.pc-side-fill {
    flex: 1 1 auto;
    min-height: 0;
}

.pc-side-panel > section:last-child {
    flex: 0 0 auto;
}

.pc-note-card {
    min-height: 96px;
}

.pc-left-panel > section {
    width: 100%;
}

.pc-side-panel > section {
    width: 100%;
}

@media (max-width: 1279px) {
    .pc-balanced-grid {
        align-items: start !important;
    }

    .pc-left-panel,
    .pc-side-panel {
        min-height: auto;
        height: auto;
    }

    .pc-side-fill {
        flex: none;
    }
}

    .pc-modal-backdrop {
        position: fixed !important;
        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
        z-index: 2147483647 !important;
        display: none;
        align-items: center;
        justify-content: center;
        width: 100vw !important;
        height: 100vh !important;
        height: 100dvh !important;
        margin: 0 !important;
        padding: 1rem;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .pc-modal-backdrop.is-open {
        display: flex !important;
    }

    .pc-modal-card {
        width: min(100%, 470px);
        transform: translateY(12px) scale(.97);
        opacity: 0;
        border-radius: 1.75rem;
        border: 1px solid rgba(255, 255, 255, .78);
        background:
            radial-gradient(circle at 0% 0%, rgba(244, 63, 94, .12), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(251, 146, 60, .10), transparent 34%),
            rgba(255, 255, 255, .95);
        box-shadow: 0 30px 90px rgba(15, 23, 42, .25);
        transition: transform .18s ease, opacity .18s ease;
    }

    .pc-modal-backdrop.is-open .pc-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="pc-page relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 pc-grid opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1400px] space-y-5">
        <section class="relative overflow-hidden rounded-[1.75rem] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-cyan-50/90 to-white/95 pc-glass">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-24 h-72 w-72 rounded-full bg-sky-300/16 blur-3xl"></div>

            <div class="relative grid gap-6 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.3fr)_340px] xl:items-stretch">
                <div class="flex min-w-0 flex-col justify-between gap-6">
                    <div>
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/75 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Detail Pengukuran Kader
                        </div>

                        <h1 class="mt-4 max-w-4xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2rem]">
                            Detail hasil pengukuran fisik sasaran.
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-slate-600">
                            Halaman ini menampilkan data yang diinput Kader dan status review Bidan. Jadi jelas mana data mentah, mana data yang sudah dikunci. Akhirnya tertib juga, tidak seperti folder “final revisi final banget”.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('kader.pemeriksaan.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-arrow-left"></i>
                            Kembali
                        </a>

                        @if($statusMeta['editable'])
                            <a href="{{ route('kader.pemeriksaan.edit', $pemeriksaan->id) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5">
                                <i class="fa-solid fa-pen"></i>
                                Edit Data
                            </a>
                        @endif
                    </div>
                </div>

                <div class="rounded-[1.55rem] border p-5 {{ $statusMeta['soft'] }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Status Data</p>
                            <h2 class="mt-2 text-xl font-black text-slate-950">{{ $statusMeta['label'] }}</h2>
                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">{{ $statusMeta['desc'] }}</p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $statusMeta['solid'] }}">
                            <i class="fa-solid {{ $statusMeta['icon'] }}"></i>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                        <p class="text-xs font-black uppercase tracking-[.14em] text-slate-500">Tanggal</p>
                        <p class="mt-1 text-sm font-black text-slate-950">{{ $tanggal }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if(session('success') || session('error'))
            <section class="space-y-3">
                @if(session('success'))
                    <div class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50/90 p-4 text-sm font-bold text-emerald-800 shadow-sm">
                        <i class="fa-solid fa-circle-check mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded-[1.35rem] border border-rose-200 bg-rose-50/90 p-4 text-sm font-bold text-rose-800 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif
            </section>
        @endif

        <section class="pc-balanced-grid grid gap-5 xl:grid-cols-[minmax(0,1.25fr)_minmax(330px,.55fr)]">
            <div class="pc-left-panel flex h-full flex-col gap-5">
                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Identitas Sasaran</p>
                            <h2 class="mt-1 text-xl font-black text-slate-950">{{ $namaPasien }}</h2>
                            <p class="mt-1 text-sm font-bold text-slate-500">NIK {{ $nikPasien }}</p>
                        </div>

                        <span class="inline-flex w-fit items-center gap-2 rounded-full border px-3 py-1 text-xs font-black {{ $kategoriMeta['badge'] }}">
                            <i class="fa-solid {{ $kategoriMeta['icon'] }}"></i>
                            {{ $kategoriMeta['label'] }}
                        </span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($mainMetrics as $metric)
                            <div class="pc-soft-hover rounded-[1.25rem] border border-slate-200 bg-white/75 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-black uppercase tracking-[.14em] text-slate-400">{{ $metric['label'] }}</p>
                                        <p class="mt-2 text-lg font-black text-slate-950">{{ $metric['value'] }}</p>
                                    </div>

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                        <i class="fa-solid {{ $metric['icon'] }}"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="mb-5">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-sky-700">Parameter Tambahan</p>
                        <h2 class="mt-1 text-xl font-black text-slate-950">{{ $parameterTitle }}</h2>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($extraMetrics as $metric)
                            <div class="pc-note-card rounded-[1.25rem] border border-slate-200 bg-white/75 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-black uppercase tracking-[.14em] text-slate-400">{{ $metric['label'] }}</p>
                                        <p class="mt-2 text-sm font-black text-slate-950">{{ $metric['value'] }}</p>
                                    </div>

                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                        <i class="fa-solid {{ $metric['icon'] }}"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="grid items-stretch gap-4 lg:grid-cols-2">
    <div class="pc-note-card rounded-[1.25rem] border border-slate-200 bg-white/75 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Keluhan</p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                {{ $pemeriksaan->keluhan ?: '-' }}
                            </p>
                        </div>

                        <div class="pc-note-card rounded-[1.25rem] border border-slate-200 bg-white/75 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Catatan Kader</p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                {{ $pemeriksaan->catatan_kader ?: '-' }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="pc-side-panel flex h-full flex-col gap-5">
                <section class="pc-glass pc-side-fill rounded-[1.75rem] border border-white/80 p-5">
    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Review Bidan</p>
                    <h2 class="mt-1 text-xl font-black text-slate-950">{{ $statusMeta['label'] }}</h2>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        {{ $catatanBidan ?: 'Belum ada catatan dari Bidan.' }}
                    </p>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Petugas Review</span>
                                <span class="line-clamp-1 text-right text-sm font-black text-slate-950">{{ $verifikator ?: '-' }}</span>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Tanggal Review</span>
                                <span class="text-right text-sm font-black text-slate-950">
                                    {{ $tanggalValidasi ? Carbon::parse($tanggalValidasi)->translatedFormat('d M Y') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
    <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Aksi Data</p>

                    <div class="mt-4 space-y-3">
                        @if($statusMeta['editable'])
                            <a href="{{ route('kader.pemeriksaan.edit', $pemeriksaan->id) }}"
                               class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5">
                                <i class="fa-solid fa-pen"></i>
                                Edit Pengukuran
                            </a>

                            <form action="{{ route('kader.pemeriksaan.destroy', $pemeriksaan->id) }}"
                                  method="POST"
                                  data-delete-form>
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-black text-rose-700 transition hover:bg-rose-100">
                                    <i class="fa-solid fa-trash"></i>
                                    Hapus Data
                                </button>
                            </form>
                        @else
                            <div class="rounded-[1.25rem] border border-emerald-200 bg-emerald-50 p-4">
                                <div class="flex gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-600">
                                        <i class="fa-solid fa-lock"></i>
                                    </div>

                                    <div>
                                        <p class="text-sm font-black text-emerald-800">Data dikunci</p>
                                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-600">
                                            Data yang sudah tervalidasi tidak dapat diubah oleh Kader.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('kader.pemeriksaan.index') }}"
                           class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <i class="fa-solid fa-list"></i>
                            Kembali ke Daftar
                        </a>
                    </div>
                </section>
            </aside>
        </section>
    </div>

    <div id="pcDeleteModal" class="pc-modal-backdrop" aria-hidden="true">
        <div class="pc-modal-card p-6">
            <div class="flex gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-rose-500 to-orange-500 text-white shadow-lg shadow-rose-500/20">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-rose-700">Konfirmasi</p>
                    <h3 class="mt-1 text-lg font-black text-slate-950">Hapus data pengukuran?</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Data yang belum direview Bidan dapat dihapus. Data tervalidasi tetap dikunci oleh sistem.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button"
                        id="pcCancelDelete"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="button"
                        id="pcConfirmDelete"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-orange-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-rose-400/20 transition hover:-translate-y-0.5">
                    <i class="fa-solid fa-trash"></i>
                    Hapus Data
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    let targetDeleteForm = null;
    let modal = document.querySelector('#pcDeleteModal');

    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    const cancelBtn = document.querySelector('#pcCancelDelete');
    const confirmBtn = document.querySelector('#pcConfirmDelete');

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function openModal(form) {
        targetDeleteForm = form;

        if (!modal) {
            return;
        }

        lockBody();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        targetDeleteForm = null;

        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-delete-form]');

        if (!form) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        openModal(form);
    }, true);

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!targetDeleteForm) {
                closeModal();
                return;
            }

            confirmBtn.disabled = true;
            confirmBtn.classList.add('opacity-70', 'cursor-not-allowed');
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';

            HTMLFormElement.prototype.submit.call(targetDeleteForm);
        });
    }
})();
</script>
@endpush