@extends('layouts.bidan')

@section('title', 'Edit Jadwal Posyandu')
@section('page-name', 'Edit Jadwal')
@section('page-title', 'Edit Jadwal Posyandu')

@php
    use Carbon\Carbon;

    $kategoriOptions = $kategoriOptions ?? [
        'posyandu' => [
            'label' => 'Posyandu Rutin',
            'desc' => 'Agenda pelayanan umum Posyandu.',
            'icon' => 'ph-house-line',
        ],
        'imunisasi' => [
            'label' => 'Imunisasi Balita',
            'desc' => 'Agenda imunisasi khusus Balita.',
            'icon' => 'ph-syringe',
        ],
        'pemeriksaan' => [
            'label' => 'Pemeriksaan Klinis',
            'desc' => 'Agenda pemeriksaan lanjutan oleh Bidan.',
            'icon' => 'ph-stethoscope',
        ],
        'lainnya' => [
            'label' => 'Kegiatan Lainnya',
            'desc' => 'Agenda tambahan Posyandu.',
            'icon' => 'ph-calendar-plus',
        ],
    ];

    $targetOptions = $targetOptions ?? [
        'semua' => [
            'label' => 'Semua Sasaran',
            'desc' => 'Balita, Remaja, Lansia, dan warga terdaftar.',
            'icon' => 'ph-users-three',
        ],
        'balita' => [
            'label' => 'Balita',
            'desc' => 'Jadwal untuk sasaran Balita.',
            'icon' => 'ph-baby',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Jadwal untuk sasaran Remaja.',
            'icon' => 'ph-user-focus',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Jadwal untuk sasaran Lansia.',
            'icon' => 'ph-heartbeat',
        ],
    ];

    $statusOptions = $statusOptions ?? [
        'aktif' => [
            'label' => 'Aktif',
            'desc' => 'Jadwal masih berlaku.',
            'icon' => 'ph-check-circle',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'desc' => 'Jadwal sudah dilaksanakan.',
            'icon' => 'ph-flag-checkered',
        ],
        'dibatalkan' => [
            'label' => 'Dibatalkan',
            'desc' => 'Jadwal dibatalkan atau ditunda.',
            'icon' => 'ph-x-circle',
        ],
    ];

    $selectedKategori = old('kategori', $jadwal->kategori ?? 'posyandu');
    $selectedTarget = old('target_peserta', $jadwal->target_peserta ?? 'semua');
    $selectedStatus = old('status', $jadwal->status ?? 'aktif');

    if ($selectedKategori === 'imunisasi') {
        $selectedTarget = 'balita';
    }

    $judulValue = old('judul', $jadwal->judul ?? '');
    $tanggalValue = old('tanggal', $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->format('Y-m-d') : now()->format('Y-m-d'));
    $mulaiValue = old('waktu_mulai', $jadwal->waktu_mulai ? Carbon::parse($jadwal->waktu_mulai)->format('H:i') : '08:00');
    $selesaiValue = old('waktu_selesai', $jadwal->waktu_selesai ? Carbon::parse($jadwal->waktu_selesai)->format('H:i') : '10:00');
    $lokasiValue = old('lokasi', $jadwal->lokasi ?? '');
    $deskripsiValue = old('deskripsi', $jadwal->deskripsi ?? '');

    $kategoriLabel = $kategoriOptions[$selectedKategori]['label'] ?? 'Posyandu Rutin';
    $targetLabel = $targetOptions[$selectedTarget]['label'] ?? 'Semua Sasaran';
    $statusLabel = $statusOptions[$selectedStatus]['label'] ?? 'Aktif';

    $formatPreviewDate = function ($date) {
        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatMetaDate = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y, H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    };
@endphp

@push('styles')
<style>
    .nexus-font {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .nexus-page-enter {
        animation: nexusMainIn .12s cubic-bezier(.22, 1, .36, 1) both;
        will-change: transform, opacity;
    }

    .nexus-panel-enter {
        animation: nexusPanelIn .12s cubic-bezier(.22, 1, .36, 1) both;
        will-change: transform, opacity;
    }

    .jadwal-choice {
        border: 1px solid rgba(226, 232, 240, .95);
        background: rgba(248, 250, 252, .8);
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .jadwal-choice:hover {
        border-color: rgba(110, 231, 183, .85);
        background: rgba(236, 253, 245, .48);
    }

    .jadwal-choice.is-active {
        border-color: rgba(52, 211, 153, .9);
        background: rgba(236, 253, 245, .86);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .09);
    }

    .jadwal-choice.is-disabled {
        opacity: .45;
        pointer-events: none;
        filter: grayscale(.25);
    }

    .status-choice[data-status="dibatalkan"].is-active {
        border-color: rgba(251, 113, 133, .9);
        background: rgba(255, 241, 242, .9);
        box-shadow: 0 0 0 3px rgba(244, 63, 94, .08);
    }

    .status-choice[data-status="selesai"].is-active {
        border-color: rgba(148, 163, 184, .9);
        background: rgba(248, 250, 252, .95);
        box-shadow: 0 0 0 3px rgba(100, 116, 139, .08);
    }

    @keyframes nexusMainIn {
        from {
            opacity: 0;
            transform: translate3d(0, 3px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes nexusPanelIn {
        from {
            opacity: 0;
            transform: translate3d(0, 2px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @media (max-width: 768px) {
        .nexus-page-enter,
        .nexus-panel-enter {
            animation-duration: .08s;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .nexus-page-enter,
        .nexus-panel-enter {
            animation: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="nexus-font nexus-page-enter space-y-5 pb-8 text-slate-800">

    {{-- HEADER --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <a href="{{ route('bidan.jadwal.show', $jadwal->id) }}"
                   class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700">
                    <i class="ph ph-arrow-left"></i>
                    Kembali
                </a>

                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">
                    <i class="ph ph-pencil-simple text-base"></i>
                    Edit Agenda
                </div>

                <h1 class="mt-4 max-w-4xl text-[26px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[30px]">
                    Edit Jadwal Posyandu
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Perbarui agenda selama jadwal masih aktif dan belum melewati waktu mulai.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3.5 py-2 text-xs font-black text-emerald-700">
                        <i class="ph ph-check-circle"></i>
                        Mode edit aktif
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-bell-ringing"></i>
                        Notifikasi dikirim jika jadwal berubah
                    </span>
                </div>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row xl:pt-12">
                <button type="submit"
                        form="jadwalEditForm"
                        class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="ph ph-floppy-disk"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <section class="nexus-panel-enter rounded-[22px] border border-rose-100 bg-rose-50/80 p-4 text-rose-700">
            <div class="flex gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-rose-600 ring-1 ring-rose-100">
                    <i class="ph ph-warning-circle text-lg"></i>
                </div>

                <div>
                    <h2 class="text-sm font-black text-rose-800">
                        Periksa kembali input jadwal
                    </h2>

                    <ul class="mt-2 space-y-1 text-sm font-semibold leading-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @endif

    <form id="jadwalEditForm"
          method="POST"
          action="{{ route('bidan.jadwal.update', $jadwal->id) }}"
          class="space-y-5">
        @csrf
        @method('PUT')

        <input type="hidden" name="kategori" id="kategoriInput" value="{{ $selectedKategori }}">
        <input type="hidden" name="target_peserta" id="targetInput" value="{{ $selectedTarget }}">
        <input type="hidden" name="status" id="statusInput" value="{{ $selectedStatus }}">

        {{-- RINGKASAN --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Ringkasan
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Preview Perubahan
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <i class="ph ph-eye text-lg"></i>
                </div>
            </div>

            <div class="rounded-[22px] bg-gradient-to-br from-emerald-500 to-teal-500 p-5 text-white">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="min-w-0">
                        <p id="previewKategori" class="text-[11px] font-black uppercase tracking-[0.16em] text-white/80">
                            {{ $kategoriLabel }}
                        </p>

                        <h3 id="previewJudul" class="mt-3 text-xl font-black tracking-[-0.025em] md:text-2xl">
                            {{ $judulValue ?: 'Judul agenda belum diisi' }}
                        </h3>

                        <p id="previewTanggal" class="mt-2 text-sm font-semibold leading-6 text-white/85">
                            {{ $formatPreviewDate($tanggalValue) }}
                        </p>
                    </div>

                    <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25">
                        <span id="previewBulan" class="text-[10px] font-black uppercase text-white/80">
                            {{ Carbon::parse($tanggalValue)->translatedFormat('M') }}
                        </span>

                        <span id="previewHari" class="text-xl font-black leading-none text-white">
                            {{ Carbon::parse($tanggalValue)->format('d') }}
                        </span>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-black text-white ring-1 ring-white/25">
                        <i class="ph ph-clock mr-1"></i>
                        <span id="previewWaktu">{{ $mulaiValue }} - {{ $selesaiValue }} WIB</span>
                    </span>

                    <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-black text-white ring-1 ring-white/25">
                        <i class="ph ph-map-pin mr-1"></i>
                        <span id="previewLokasi">{{ $lokasiValue ?: 'Lokasi belum diisi' }}</span>
                    </span>

                    <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-black text-white ring-1 ring-white/25">
                        <i class="ph ph-users-three mr-1"></i>
                        <span id="previewTarget">{{ $targetLabel }}</span>
                    </span>

                    <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-black text-white ring-1 ring-white/25">
                        <i class="ph ph-check-circle mr-1"></i>
                        <span id="previewStatus">{{ $statusLabel }}</span>
                    </span>
                </div>
            </div>
        </section>

        {{-- INFORMASI DASAR --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Informasi Dasar
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Detail Pelaksanaan
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <i class="ph ph-clipboard-text text-lg"></i>
                </div>
            </div>

            <div class="grid gap-4">
                <div>
                    <label for="judul" class="mb-2 block text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                        Judul Kegiatan <span class="text-rose-500">*</span>
                    </label>

                    <input type="text"
                           id="judul"
                           name="judul"
                           value="{{ $judulValue }}"
                           maxlength="191"
                           required
                           spellcheck="false"
                           placeholder="Contoh: Posyandu Balita Bulan Juni"
                           class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">

                    @error('judul')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="tanggal" class="mb-2 block text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                            Tanggal <span class="text-rose-500">*</span>
                        </label>

                        <input type="date"
                               id="tanggal"
                               name="tanggal"
                               value="{{ $tanggalValue }}"
                               required
                               class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">

                        @error('tanggal')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="waktu_mulai" class="mb-2 block text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                            Jam Mulai <span class="text-rose-500">*</span>
                        </label>

                        <input type="time"
                               id="waktu_mulai"
                               name="waktu_mulai"
                               value="{{ $mulaiValue }}"
                               required
                               class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">

                        @error('waktu_mulai')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="waktu_selesai" class="mb-2 block text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                            Jam Selesai <span class="text-rose-500">*</span>
                        </label>

                        <input type="time"
                               id="waktu_selesai"
                               name="waktu_selesai"
                               value="{{ $selesaiValue }}"
                               required
                               class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">

                        @error('waktu_selesai')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="lokasi" class="mb-2 block text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                        Lokasi <span class="text-rose-500">*</span>
                    </label>

                    <input type="text"
                           id="lokasi"
                           name="lokasi"
                           value="{{ $lokasiValue }}"
                           maxlength="191"
                           required
                           spellcheck="false"
                           placeholder="Contoh: Balai Desa Bantarkulon"
                           class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">

                    @error('lokasi')
                        <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- STATUS --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Status
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Kondisi Jadwal
                    </h2>

                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Gunakan status dibatalkan bila agenda tidak jadi dilaksanakan.
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-50 text-slate-600 ring-1 ring-slate-100">
                    <i class="ph ph-check-circle text-lg"></i>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                @foreach($statusOptions as $key => $option)
                    <button type="button"
                            data-status="{{ $key }}"
                            data-label="{{ $option['label'] }}"
                            class="js-status-btn status-choice jadwal-choice rounded-2xl p-4 text-left {{ $selectedStatus === $key ? 'is-active' : '' }}">
                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-700 ring-1 ring-emerald-100">
                                <i class="ph {{ $option['icon'] }} text-lg"></i>
                            </div>

                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-slate-900">
                                    {{ $option['label'] }}
                                </h3>

                                <p class="mt-1 line-clamp-2 text-xs font-semibold leading-5 text-slate-500">
                                    {{ $option['desc'] }}
                                </p>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            @error('status')
                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
            @enderror
        </section>

        {{-- KATEGORI --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Kategori
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Jenis Layanan
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                    <i class="ph ph-squares-four text-lg"></i>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach($kategoriOptions as $key => $option)
                    <button type="button"
                            data-kategori="{{ $key }}"
                            data-label="{{ $option['label'] }}"
                            class="js-kategori-btn jadwal-choice rounded-2xl p-4 text-left {{ $selectedKategori === $key ? 'is-active' : '' }}">
                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-700 ring-1 ring-emerald-100">
                                <i class="ph {{ $option['icon'] }} text-lg"></i>
                            </div>

                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-slate-900">
                                    {{ $option['label'] }}
                                </h3>

                                <p class="mt-1 line-clamp-2 text-xs font-semibold leading-5 text-slate-500">
                                    {{ $option['desc'] }}
                                </p>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            @error('kategori')
                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
            @enderror
        </section>

        {{-- TARGET --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Target
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Sasaran Peserta
                    </h2>

                    <p id="targetHint" class="mt-1 text-xs font-semibold text-slate-500">
                        Pilih sasaran yang akan menerima jadwal.
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <i class="ph ph-users-three text-lg"></i>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach($targetOptions as $key => $option)
                    <button type="button"
                            data-target="{{ $key }}"
                            data-label="{{ $option['label'] }}"
                            class="js-target-btn jadwal-choice rounded-2xl p-4 text-left {{ $selectedTarget === $key ? 'is-active' : '' }}">
                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-700 ring-1 ring-emerald-100">
                                <i class="ph {{ $option['icon'] }} text-lg"></i>
                            </div>

                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-slate-900">
                                    {{ $option['label'] }}
                                </h3>

                                <p class="mt-1 line-clamp-2 text-xs font-semibold leading-5 text-slate-500">
                                    {{ $option['desc'] }}
                                </p>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            @error('target_peserta')
                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
            @enderror
        </section>

        {{-- CATATAN --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Catatan
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Deskripsi Tambahan
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                    <i class="ph ph-note-pencil text-lg"></i>
                </div>
            </div>

            <textarea id="deskripsi"
                      name="deskripsi"
                      rows="4"
                      maxlength="1000"
                      spellcheck="false"
                      placeholder="Contoh: peserta membawa buku pemeriksaan atau kartu identitas masing-masing."
                      class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">{{ $deskripsiValue }}</textarea>

            @error('deskripsi')
                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
            @enderror
        </section>

        {{-- RIWAYAT --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Riwayat
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Data Sistem
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-50 text-slate-600 ring-1 ring-slate-100">
                    <i class="ph ph-clock-counter-clockwise text-lg"></i>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                        Dibuat
                    </p>

                    <p class="mt-1 text-sm font-black text-slate-900">
                        {{ $formatMetaDate($jadwal->created_at ?? null) }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                        Diperbarui
                    </p>

                    <p class="mt-1 text-sm font-black text-slate-900">
                        {{ $formatMetaDate($jadwal->updated_at ?? null) }}
                    </p>
                </div>
            </div>
        </section>

        {{-- ACTION --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
            <div class="grid gap-3 sm:grid-cols-[1fr_auto_auto]">
                <a href="{{ route('bidan.jadwal.show', $jadwal->id) }}"
                   class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                    Batal
                </a>

                <button type="reset"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-black text-slate-500 transition hover:bg-slate-100">
                    Reset Form
                </button>

                <button type="submit"
                        class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                    <i class="ph ph-floppy-disk"></i>
                    Simpan Perubahan
                </button>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const kategoriInput = document.getElementById('kategoriInput');
        const targetInput = document.getElementById('targetInput');
        const statusInput = document.getElementById('statusInput');

        const kategoriButtons = Array.from(document.querySelectorAll('.js-kategori-btn'));
        const targetButtons = Array.from(document.querySelectorAll('.js-target-btn'));
        const statusButtons = Array.from(document.querySelectorAll('.js-status-btn'));

        const targetHint = document.getElementById('targetHint');

        const judulInput = document.getElementById('judul');
        const tanggalInput = document.getElementById('tanggal');
        const mulaiInput = document.getElementById('waktu_mulai');
        const selesaiInput = document.getElementById('waktu_selesai');
        const lokasiInput = document.getElementById('lokasi');

        const previewKategori = document.getElementById('previewKategori');
        const previewJudul = document.getElementById('previewJudul');
        const previewTanggal = document.getElementById('previewTanggal');
        const previewBulan = document.getElementById('previewBulan');
        const previewHari = document.getElementById('previewHari');
        const previewWaktu = document.getElementById('previewWaktu');
        const previewLokasi = document.getElementById('previewLokasi');
        const previewTarget = document.getElementById('previewTarget');
        const previewStatus = document.getElementById('previewStatus');

        const initialKategori = kategoriInput?.value || 'posyandu';
        const initialTarget = targetInput?.value || 'semua';
        const initialStatus = statusInput?.value || 'aktif';

        let selectedKategori = initialKategori;
        let selectedTarget = initialTarget;
        let selectedStatus = initialStatus;

        const formatDate = (value) => {
            if (!value) {
                return {
                    full: '-',
                    month: '-',
                    day: '-',
                };
            }

            const date = new Date(`${value}T00:00:00`);

            if (Number.isNaN(date.getTime())) {
                return {
                    full: '-',
                    month: '-',
                    day: '-',
                };
            }

            return {
                full: date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                }),
                month: date.toLocaleDateString('id-ID', {
                    month: 'short',
                }),
                day: date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                }),
            };
        };

        const setActiveButton = (buttons, key, dataName) => {
            buttons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset[dataName] === key);
            });
        };

        const syncTargetLock = () => {
            const lockBalita = selectedKategori === 'imunisasi';

            if (lockBalita) {
                selectedTarget = 'balita';

                if (targetInput) {
                    targetInput.value = 'balita';
                }
            }

            targetButtons.forEach((button) => {
                const disabled = lockBalita && button.dataset.target !== 'balita';

                button.classList.toggle('is-disabled', disabled);
                button.disabled = disabled;
            });

            if (targetHint) {
                targetHint.textContent = lockBalita
                    ? 'Kategori Imunisasi Balita otomatis menggunakan target Balita.'
                    : 'Pilih sasaran yang akan menerima jadwal.';
            }

            setActiveButton(targetButtons, selectedTarget, 'target');
        };

        const updatePreview = () => {
            const activeKategori = kategoriButtons.find((button) => button.dataset.kategori === selectedKategori);
            const activeTarget = targetButtons.find((button) => button.dataset.target === selectedTarget);
            const activeStatus = statusButtons.find((button) => button.dataset.status === selectedStatus);

            const date = formatDate(tanggalInput?.value);

            if (previewKategori) {
                previewKategori.textContent = activeKategori?.dataset.label || 'Posyandu Rutin';
            }

            if (previewJudul) {
                previewJudul.textContent = judulInput?.value?.trim() || 'Judul agenda belum diisi';
            }

            if (previewTanggal) {
                previewTanggal.textContent = date.full;
            }

            if (previewBulan) {
                previewBulan.textContent = date.month;
            }

            if (previewHari) {
                previewHari.textContent = date.day;
            }

            if (previewWaktu) {
                previewWaktu.textContent = `${mulaiInput?.value || '--:--'} - ${selesaiInput?.value || '--:--'} WIB`;
            }

            if (previewLokasi) {
                previewLokasi.textContent = lokasiInput?.value?.trim() || 'Lokasi belum diisi';
            }

            if (previewTarget) {
                previewTarget.textContent = activeTarget?.dataset.label || 'Semua Sasaran';
            }

            if (previewStatus) {
                previewStatus.textContent = activeStatus?.dataset.label || 'Aktif';
            }
        };

        const selectKategori = (key) => {
            selectedKategori = key || 'posyandu';

            if (kategoriInput) {
                kategoriInput.value = selectedKategori;
            }

            setActiveButton(kategoriButtons, selectedKategori, 'kategori');
            syncTargetLock();
            updatePreview();
        };

        const selectTarget = (key) => {
            if (selectedKategori === 'imunisasi' && key !== 'balita') {
                return;
            }

            selectedTarget = key || 'semua';

            if (targetInput) {
                targetInput.value = selectedTarget;
            }

            setActiveButton(targetButtons, selectedTarget, 'target');
            updatePreview();
        };

        const selectStatus = (key) => {
            selectedStatus = key || 'aktif';

            if (statusInput) {
                statusInput.value = selectedStatus;
            }

            setActiveButton(statusButtons, selectedStatus, 'status');
            updatePreview();
        };

        kategoriButtons.forEach((button) => {
            button.addEventListener('click', () => selectKategori(button.dataset.kategori));
        });

        targetButtons.forEach((button) => {
            button.addEventListener('click', () => selectTarget(button.dataset.target));
        });

        statusButtons.forEach((button) => {
            button.addEventListener('click', () => selectStatus(button.dataset.status));
        });

        [judulInput, tanggalInput, mulaiInput, selesaiInput, lokasiInput].forEach((input) => {
            input?.addEventListener('input', updatePreview, { passive: true });
            input?.addEventListener('change', updatePreview);
        });

        document.getElementById('jadwalEditForm')?.addEventListener('reset', () => {
            window.setTimeout(() => {
                selectedKategori = initialKategori;
                selectedTarget = initialTarget;
                selectedStatus = initialStatus;

                if (kategoriInput) {
                    kategoriInput.value = selectedKategori;
                }

                if (targetInput) {
                    targetInput.value = selectedTarget;
                }

                if (statusInput) {
                    statusInput.value = selectedStatus;
                }

                setActiveButton(kategoriButtons, selectedKategori, 'kategori');
                setActiveButton(targetButtons, selectedTarget, 'target');
                setActiveButton(statusButtons, selectedStatus, 'status');
                syncTargetLock();
                updatePreview();
            }, 0);
        });

        selectKategori(selectedKategori);
        selectTarget(selectedTarget);
        selectStatus(selectedStatus);
        updatePreview();
    })();
</script>
@endpush