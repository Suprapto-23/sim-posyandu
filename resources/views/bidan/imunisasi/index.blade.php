@extends('layouts.bidan')

@section('title', 'Vaksinasi & Imunisasi')
@section('page-name', 'Imunisasi')
@section('page-title', 'Vaksinasi & Imunisasi Balita')

@php
    use Carbon\Carbon;

    $imunisasis = $imunisasis ?? collect();
    $search = $search ?? request('search', '');

    $stats = $stats ?? [
        'total' => 0,
        'bulan_ini' => 0,
        'total_balita' => 0,
        'vaksin_tercatat' => 0,
    ];

    $programOptions = $programOptions ?? [
        'BCG' => 'BCG',
        'Polio' => 'Polio',
        'DPT-HB-Hib' => 'DPT-HB-Hib',
        'Hepatitis B' => 'Hepatitis B',
        'Campak / MR' => 'Campak / MR',
        'IPV' => 'IPV',
        'PCV' => 'PCV',
        'Rotavirus' => 'Rotavirus',
        'Lainnya' => 'Lainnya',
    ];

    $getValue = function ($item, array $keys, mixed $default = '-') {
        foreach ($keys as $key) {
            $value = data_get($item, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    };

    $getBalita = function ($item) {
        $pasien = data_get($item, 'kunjungan.pasien');

        if ($pasien) {
            return $pasien;
        }

        $balita = data_get($item, 'balita');

        return $balita ?: null;
    };

    $getNamaBalita = function ($item) use ($getBalita, $getValue) {
        $balita = $getBalita($item);

        if ($balita) {
            return $getValue($balita, ['nama_lengkap', 'nama', 'nama_balita'], 'Balita tidak terdata');
        }

        return $getValue($item, ['nama_balita', 'nama_pasien'], 'Balita tidak terdata');
    };

    $getNikBalita = function ($item) use ($getBalita, $getValue) {
        $balita = $getBalita($item);

        if ($balita) {
            return $getValue($balita, ['nik', 'nik_anak'], '-');
        }

        return $getValue($item, ['nik', 'nik_balita', 'nik_anak'], '-');
    };

    $getWaliBalita = function ($item) use ($getBalita, $getValue) {
        $balita = $getBalita($item);

        if ($balita) {
            return $getValue($balita, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
        }

        return $getValue($item, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
    };

    $getAlamatBalita = function ($item) use ($getBalita, $getValue) {
        $balita = $getBalita($item);

        if ($balita) {
            return $getValue($balita, ['alamat', 'alamat_lengkap', 'dusun'], '-');
        }

        return $getValue($item, ['alamat', 'alamat_balita'], '-');
    };

    $getPetugas = function ($item) {
        $petugas = data_get($item, 'kunjungan.petugas');

        if (!$petugas) {
            return '-';
        }

        return data_get($petugas, 'name')
            ?? data_get($petugas, 'nama')
            ?? data_get($petugas, 'nama_lengkap')
            ?? '-';
    };

    $formatDate = function ($date) {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatBulanPendek = function ($date) {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('M');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatTanggalAngka = function ($date) {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return Carbon::parse($date)->format('d');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $isThisMonth = function ($date) {
        if (!$date || $date === '-') {
            return false;
        }

        try {
            return Carbon::parse($date)->isSameMonth(now());
        } catch (\Throwable $e) {
            return false;
        }
    };

    $visibleCount = method_exists($imunisasis, 'count')
        ? $imunisasis->count()
        : count($imunisasis);

    $totalData = method_exists($imunisasis, 'total')
        ? $imunisasis->total()
        : $visibleCount;

    $summaryCards = [
        [
            'label' => 'Total Catatan',
            'value' => $stats['total'] ?? 0,
            'icon' => 'ph-syringe',
            'class' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        ],
        [
            'label' => 'Bulan Ini',
            'value' => $stats['bulan_ini'] ?? 0,
            'icon' => 'ph-calendar-check',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Total Balita',
            'value' => $stats['total_balita'] ?? 0,
            'icon' => 'ph-baby',
            'class' => 'bg-sky-50 text-sky-700 ring-sky-100',
        ],
        [
            'label' => 'Jenis Vaksin',
            'value' => $stats['vaksin_tercatat'] ?? 0,
            'icon' => 'ph-list-checks',
            'class' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
    ];
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

    .nexus-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, .35) transparent;
    }

    .nexus-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .nexus-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .nexus-scroll::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, .35);
        border-radius: 999px;
    }

    .nexus-live-hidden {
        display: none !important;
    }

    .nexus-list-stable {
        min-height: 420px;
        contain: layout paint;
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

        .nexus-list-stable {
            min-height: 320px;
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
                <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.16em] text-cyan-700">
                    <i class="ph ph-syringe text-base"></i>
                    Layanan Balita
                </div>

                <h1 class="mt-4 max-w-4xl text-[26px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[30px]">
                    Vaksinasi & Imunisasi Balita
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Halaman ini digunakan Bidan untuk mencatat dan melihat riwayat imunisasi pada sasaran Balita.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3.5 py-2 text-xs font-black text-cyan-700">
                        <i class="ph ph-baby"></i>
                        Khusus Balita
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-database"></i>
                        <span id="imunisasiVisibleCount">{{ $visibleCount }}</span>
                        catatan tampil
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-list-magnifying-glass"></i>
                        Total data: {{ $totalData }}
                    </span>
                </div>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row xl:pt-12">
                <a href="{{ route('bidan.imunisasi.create') }}"
                   class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="ph ph-plus"></i>
                    Catat Imunisasi
                </a>
            </div>
        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="nexus-panel-enter grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="rounded-[22px] border border-white/80 bg-white/85 p-4 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                            {{ $card['label'] }}
                        </p>

                        <h2 class="mt-2 line-clamp-1 text-xl font-black tracking-tight text-slate-900">
                            {{ $card['value'] }}
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['class'] }}">
                        <i class="ph {{ $card['icon'] }} text-lg"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- OUTPUT INFO --}}
    <section class="nexus-panel-enter rounded-[22px] border border-cyan-100 bg-cyan-50/70 p-4">
        <div class="flex gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-cyan-700 ring-1 ring-cyan-100">
                <i class="ph ph-info text-lg"></i>
            </div>

            <div>
                <h2 class="text-sm font-black text-slate-900">
                    Output Modul Imunisasi
                </h2>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Data yang ditampilkan berupa catatan layanan imunisasi Balita, meliputi identitas Balita, jenis imunisasi, vaksin, dosis, nomor batch, tanggal layanan, dan catatan tambahan.
                </p>
            </div>
        </div>
    </section>

    {{-- CONTENT --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 grid gap-4 xl:grid-cols-[180px_minmax(0,1fr)] xl:items-center">
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-cyan-600">
                    Direktori
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Catatan Imunisasi
                </h2>
            </div>

            <form method="GET"
                  action="{{ route('bidan.imunisasi.index') }}"
                  class="grid w-full gap-2 md:grid-cols-[1fr_auto_auto]">
                <div class="relative">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                    <input type="text"
                           id="imunisasiLiveSearch"
                           name="search"
                           value="{{ $search }}"
                           autocomplete="off"
                           spellcheck="false"
                           inputmode="search"
                           placeholder="Cari nama, NIK, imunisasi, atau vaksin..."
                           class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-11 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-300 focus:bg-white focus:ring-4 focus:ring-cyan-100">

                    <button type="button"
                            id="imunisasiClearSearch"
                            class="absolute right-3 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>

                <button type="submit"
                        class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                    <i class="ph ph-funnel"></i>
                    Filter
                </button>

                @if($search)
                    <a href="{{ route('bidan.imunisasi.index') }}"
                       class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="nexus-list-stable">

            {{-- DESKTOP TABLE --}}
            <div class="nexus-scroll hidden max-h-[620px] overflow-auto lg:block">
                <table class="min-w-[1180px] w-full border-separate border-spacing-y-3">
                    <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur">
                        <tr class="text-left text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Balita</th>
                            <th class="px-4 py-3">Wali</th>
                            <th class="px-4 py-3">Jenis Imunisasi</th>
                            <th class="px-4 py-3">Vaksin</th>
                            <th class="px-4 py-3">Dosis</th>
                            <th class="px-4 py-3">Petugas</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($imunisasis as $imunisasi)
                            @php
                                $namaBalita = $getNamaBalita($imunisasi);
                                $nikBalita = $getNikBalita($imunisasi);
                                $waliBalita = $getWaliBalita($imunisasi);
                                $alamatBalita = $getAlamatBalita($imunisasi);

                                $jenisImunisasi = $getValue($imunisasi, ['jenis_imunisasi', 'nama_imunisasi', 'jenis'], 'Imunisasi');
                                $vaksin = $getValue($imunisasi, ['vaksin', 'nama_vaksin'], '-');
                                $dosis = $getValue($imunisasi, ['dosis', 'dosis_ke'], '-');
                                $batch = $getValue($imunisasi, ['batch_number', 'no_batch', 'nomor_batch'], '-');
                                $tanggal = $getValue($imunisasi, ['tanggal_imunisasi', 'tanggal', 'created_at'], null);
                                $petugas = $getPetugas($imunisasi);

                                $searchName = mb_strtolower(trim((string) $namaBalita), 'UTF-8');
                                $searchNik = mb_strtolower(trim((string) $nikBalita), 'UTF-8');
                                $searchJenis = mb_strtolower(trim((string) $jenisImunisasi), 'UTF-8');
                                $searchVaksin = mb_strtolower(trim((string) $vaksin), 'UTF-8');

                                $bulanIni = $isThisMonth($tanggal);
                            @endphp

                            <tr class="js-imunisasi-row"
                                data-name="{{ $searchName }}"
                                data-nik="{{ $searchNik }}"
                                data-jenis="{{ $searchJenis }}"
                                data-vaksin="{{ $searchVaksin }}">
                                <td class="rounded-l-2xl border-y border-l border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-2xl bg-white ring-1 ring-slate-100">
                                            <span class="text-[10px] font-black uppercase text-cyan-600">
                                                {{ $formatBulanPendek($tanggal) }}
                                            </span>

                                            <span class="text-xl font-black leading-none text-slate-900">
                                                {{ $formatTanggalAngka($tanggal) }}
                                            </span>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="whitespace-nowrap text-sm font-black text-slate-900">
                                                {{ $formatDate($tanggal) }}
                                            </p>

                                            @if($bulanIni)
                                                <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black text-emerald-700 ring-1 ring-emerald-200">
                                                    Bulan Ini
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                                            <i class="ph ph-baby text-lg"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="max-w-[220px] truncate text-sm font-black text-slate-900">
                                                {{ $namaBalita }}
                                            </p>

                                            <p class="mt-1 max-w-[220px] truncate text-xs font-semibold text-slate-500">
                                                NIK: {{ $nikBalita }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="min-w-0">
                                        <p class="max-w-[180px] truncate text-sm font-black text-slate-700">
                                            {{ $waliBalita }}
                                        </p>

                                        <p class="mt-1 max-w-[180px] truncate text-xs font-semibold text-slate-500">
                                            {{ $alamatBalita }}
                                        </p>
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 text-xs font-black text-cyan-700 ring-1 ring-cyan-200">
                                        <i class="ph ph-syringe"></i>
                                        {{ $jenisImunisasi }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="min-w-0">
                                        <p class="max-w-[160px] truncate text-sm font-black text-slate-900">
                                            {{ $vaksin }}
                                        </p>

                                        <p class="mt-1 max-w-[160px] truncate text-xs font-semibold text-slate-500">
                                            Batch: {{ $batch }}
                                        </p>
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-black text-slate-600 ring-1 ring-slate-200">
                                        {{ $dosis }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <p class="max-w-[150px] truncate text-sm font-semibold text-slate-500">
                                        {{ $petugas }}
                                    </p>
                                </td>

                                <td class="rounded-r-2xl border-y border-r border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('bidan.imunisasi.show', $imunisasi->id) }}"
                                           class="inline-flex min-h-[40px] items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                                            Detail
                                        </a>

                                        <a href="{{ route('bidan.imunisasi.edit', $imunisasi->id) }}"
                                           class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700"
                                           title="Perbaiki catatan">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                                        <i class="ph ph-syringe text-3xl"></i>
                                    </div>

                                    <h3 class="mt-4 text-base font-black text-slate-800">
                                        Catatan Imunisasi Belum Ada
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Data akan tampil setelah Bidan mencatat layanan imunisasi Balita.
                                    </p>

                                    <a href="{{ route('bidan.imunisasi.create') }}"
                                       class="mt-5 inline-flex min-h-[44px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                                        <i class="ph ph-plus"></i>
                                        Catat Imunisasi
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE CARD --}}
            <div class="space-y-3 lg:hidden">
                @forelse($imunisasis as $imunisasi)
                    @php
                        $namaBalita = $getNamaBalita($imunisasi);
                        $nikBalita = $getNikBalita($imunisasi);
                        $waliBalita = $getWaliBalita($imunisasi);
                        $alamatBalita = $getAlamatBalita($imunisasi);

                        $jenisImunisasi = $getValue($imunisasi, ['jenis_imunisasi', 'nama_imunisasi', 'jenis'], 'Imunisasi');
                        $vaksin = $getValue($imunisasi, ['vaksin', 'nama_vaksin'], '-');
                        $dosis = $getValue($imunisasi, ['dosis', 'dosis_ke'], '-');
                        $batch = $getValue($imunisasi, ['batch_number', 'no_batch', 'nomor_batch'], '-');
                        $tanggal = $getValue($imunisasi, ['tanggal_imunisasi', 'tanggal', 'created_at'], null);

                        $searchName = mb_strtolower(trim((string) $namaBalita), 'UTF-8');
                        $searchNik = mb_strtolower(trim((string) $nikBalita), 'UTF-8');
                        $searchJenis = mb_strtolower(trim((string) $jenisImunisasi), 'UTF-8');
                        $searchVaksin = mb_strtolower(trim((string) $vaksin), 'UTF-8');
                    @endphp

                    <article class="js-imunisasi-card rounded-2xl border border-slate-100 bg-slate-50/80 p-4"
                             data-name="{{ $searchName }}"
                             data-nik="{{ $searchNik }}"
                             data-jenis="{{ $searchJenis }}"
                             data-vaksin="{{ $searchVaksin }}">
                        <div class="flex items-start gap-3">
                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-2xl bg-white ring-1 ring-slate-100">
                                <span class="text-[10px] font-black uppercase text-cyan-600">
                                    {{ $formatBulanPendek($tanggal) }}
                                </span>

                                <span class="text-xl font-black leading-none text-slate-900">
                                    {{ $formatTanggalAngka($tanggal) }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="line-clamp-2 text-base font-black text-slate-900">
                                            {{ $namaBalita }}
                                        </h3>

                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                            NIK: {{ $nikBalita }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full bg-cyan-50 px-3 py-1 text-[11px] font-black text-cyan-700 ring-1 ring-cyan-200">
                                        Balita
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase text-slate-400">
                                            Imunisasi
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-900">
                                            {{ $jenisImunisasi }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase text-slate-400">
                                            Vaksin
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-900">
                                            {{ $vaksin }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase text-slate-400">
                                            Dosis
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-900">
                                            {{ $dosis }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase text-slate-400">
                                            Tanggal
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-900">
                                            {{ $formatDate($tanggal) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                    <p class="text-[10px] font-black uppercase text-slate-400">
                                        Wali / Alamat
                                    </p>

                                    <p class="mt-1 truncate text-sm font-black text-slate-900">
                                        {{ $waliBalita }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                        {{ $alamatBalita }}
                                    </p>
                                </div>

                                <div class="mt-4 grid grid-cols-[1fr_auto] gap-2">
                                    <a href="{{ route('bidan.imunisasi.show', $imunisasi->id) }}"
                                       class="inline-flex min-h-[42px] items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                                        Detail
                                    </a>

                                    <a href="{{ route('bidan.imunisasi.edit', $imunisasi->id) }}"
                                       class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700"
                                       title="Perbaiki catatan">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                            <i class="ph ph-syringe text-3xl"></i>
                        </div>

                        <h3 class="mt-4 text-base font-black text-slate-800">
                            Catatan Imunisasi Belum Ada
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Data akan tampil setelah Bidan mencatat layanan imunisasi Balita.
                        </p>

                        <a href="{{ route('bidan.imunisasi.create') }}"
                           class="mt-5 inline-flex min-h-[44px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                            <i class="ph ph-plus"></i>
                            Catat Imunisasi
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- LIVE EMPTY --}}
            <div id="imunisasiLiveEmpty"
                 class="hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                    <i class="ph ph-magnifying-glass text-3xl"></i>
                </div>

                <h3 class="mt-4 text-base font-black text-slate-800">
                    Catatan Tidak Cocok
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Tidak ada catatan pada halaman ini yang sesuai dengan nama, NIK, jenis imunisasi, atau vaksin.
                </p>
            </div>

            {{-- PAGINATION --}}
            @if(method_exists($imunisasis, 'hasPages') && $imunisasis->hasPages())
                <div id="imunisasiPagination" class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-5 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm font-semibold text-slate-500">
                        Menampilkan {{ $imunisasis->firstItem() }} sampai {{ $imunisasis->lastItem() }} dari {{ $imunisasis->total() }} catatan
                    </p>

                    <div>
                        {{ $imunisasis->links() }}
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const searchInput = document.getElementById('imunisasiLiveSearch');
        const clearButton = document.getElementById('imunisasiClearSearch');
        const visibleCountText = document.getElementById('imunisasiVisibleCount');
        const liveEmpty = document.getElementById('imunisasiLiveEmpty');

        const rows = Array.from(document.querySelectorAll('.js-imunisasi-row'));
        const cards = Array.from(document.querySelectorAll('.js-imunisasi-card'));

        const normalize = (value) => {
            return String(value || '')
                .toLowerCase()
                .trim();
        };

        const isNumericKeyword = (keyword) => {
            return /^[0-9]+$/.test(keyword);
        };

        const itemMatches = (item, keyword) => {
            const name = normalize(item.dataset.name);
            const nik = normalize(item.dataset.nik);
            const jenis = normalize(item.dataset.jenis);
            const vaksin = normalize(item.dataset.vaksin);

            if (keyword === '') {
                return true;
            }

            if (isNumericKeyword(keyword)) {
                return nik.includes(keyword);
            }

            return name.includes(keyword)
                || jenis.includes(keyword)
                || vaksin.includes(keyword);
        };

        const setHidden = (element, hidden) => {
            if (!element) {
                return;
            }

            element.classList.toggle('nexus-live-hidden', hidden);
        };

        let frameId = null;

        const applyFilter = () => {
            if (frameId) {
                cancelAnimationFrame(frameId);
            }

            frameId = requestAnimationFrame(() => {
                const keyword = normalize(searchInput?.value);
                let visibleCount = 0;

                rows.forEach((row) => {
                    const visible = itemMatches(row, keyword);

                    setHidden(row, !visible);

                    if (visible) {
                        visibleCount += 1;
                    }
                });

                cards.forEach((card) => {
                    const visible = itemMatches(card, keyword);

                    setHidden(card, !visible);
                });

                if (visibleCountText) {
                    visibleCountText.textContent = String(visibleCount);
                }

                if (liveEmpty) {
                    const hasData = rows.length > 0 || cards.length > 0;
                    liveEmpty.classList.toggle('hidden', !hasData || visibleCount > 0);
                }

                if (clearButton) {
                    const hasKeyword = keyword !== '';
                    clearButton.classList.toggle('hidden', !hasKeyword);
                    clearButton.classList.toggle('inline-flex', hasKeyword);
                }
            });
        };

        searchInput?.addEventListener('input', applyFilter, { passive: true });
        searchInput?.addEventListener('keyup', applyFilter, { passive: true });
        searchInput?.addEventListener('search', applyFilter, { passive: true });

        clearButton?.addEventListener('click', () => {
            if (!searchInput) {
                return;
            }

            searchInput.value = '';
            searchInput.focus();
            applyFilter();
        });

        applyFilter();
    })();
</script>
@endpush