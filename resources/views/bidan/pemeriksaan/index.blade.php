@extends('layouts.bidan')

@section('title', 'Pemeriksaan Klinis')
@section('page-name', 'Pemeriksaan Klinis')
@section('page-title', 'Pemeriksaan Klinis')

@php
    use Carbon\Carbon;

    $pemeriksaans = $pemeriksaans ?? collect();
    $tab = $tab ?? request('tab', 'pending');
    $kategori = $kategori ?? request('kategori', 'semua');
    $search = $search ?? request('search', '');

    $kategoriOptions = $kategoriOptions ?? [
        'balita' => [
            'label' => 'Balita',
            'icon' => 'ph-baby',
            'desc' => 'Pemeriksaan pertumbuhan Balita.',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'ph-user-focus',
            'desc' => 'Pemeriksaan kesehatan Remaja.',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'ph-heartbeat',
            'desc' => 'Pemeriksaan kesehatan Lansia.',
        ],
    ];

    $stats = $stats ?? [
        'total' => 0,
        'pending' => 0,
        'verified' => 0,
        'bulan_ini' => 0,
        'kategori' => [
            'balita' => 0,
            'remaja' => 0,
            'lansia' => 0,
        ],
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

    $formatDateTime = function ($date) {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y, H:i') . ' WIB';
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $displayValue = function (mixed $value, string $suffix = '') {
        if ($value === null || $value === '' || $value === '-') {
            return '-';
        }

        return trim((string) $value . ' ' . $suffix);
    };

    $getPasien = function ($item) {
        return data_get($item, 'kunjungan.pasien')
            ?? data_get($item, 'balita')
            ?? data_get($item, 'remaja')
            ?? data_get($item, 'lansia')
            ?? null;
    };

    $getKategori = function ($item) use ($getValue) {
        $raw = strtolower((string) $getValue($item, ['kategori_pasien'], ''));

        if (in_array($raw, ['balita', 'remaja', 'lansia'], true)) {
            return $raw;
        }

        if (data_get($item, 'balita')) {
            return 'balita';
        }

        if (data_get($item, 'remaja')) {
            return 'remaja';
        }

        if (data_get($item, 'lansia')) {
            return 'lansia';
        }

        $pasien = data_get($item, 'kunjungan.pasien');

        if ($pasien) {
            $class = strtolower(class_basename($pasien));

            if (in_array($class, ['balita', 'remaja', 'lansia'], true)) {
                return $class;
            }
        }

        return 'balita';
    };

    $getNamaPasien = function ($item) use ($getPasien, $getValue) {
        $pasien = $getPasien($item);

        if ($pasien) {
            return $getValue($pasien, [
                'nama_lengkap',
                'nama',
                'nama_balita',
                'nama_remaja',
                'nama_lansia',
            ], 'Nama tidak tersedia');
        }

        return $getValue($item, ['nama_pasien', 'nama'], 'Nama tidak tersedia');
    };

    $getNikPasien = function ($item) use ($getPasien, $getValue) {
        $pasien = $getPasien($item);

        if ($pasien) {
            return $getValue($pasien, ['nik', 'nik_anak'], '-');
        }

        return $getValue($item, ['nik', 'nik_pasien', 'nik_anak'], '-');
    };

    $getPetugas = function ($item) {
        return data_get($item, 'pemeriksa.name')
            ?? data_get($item, 'pemeriksa.nama')
            ?? data_get($item, 'kunjungan.petugas.name')
            ?? data_get($item, 'kunjungan.petugas.nama')
            ?? '-';
    };

    $getVerifikator = function ($item) {
        return data_get($item, 'verifikator.name')
            ?? data_get($item, 'verifikator.nama')
            ?? data_get($item, 'verifikatorLegacy.name')
            ?? data_get($item, 'verifikatorLegacy.nama')
            ?? '-';
    };

    $isVerified = function ($item) use ($getValue) {
        $status = strtolower((string) $getValue($item, ['status_verifikasi'], ''));

        return in_array($status, ['verified', 'tervalidasi', 'approved'], true);
    };

    $statusLabel = function ($item) use ($isVerified, $getValue) {
        $status = strtolower((string) $getValue($item, ['status_verifikasi'], ''));

        if ($isVerified($item)) {
            return 'Tervalidasi';
        }

        if (in_array($status, ['rejected', 'ditolak'], true)) {
            return 'Perlu Revisi';
        }

        return 'Menunggu Validasi';
    };

    $statusTheme = function ($item) use ($isVerified, $getValue) {
        $status = strtolower((string) $getValue($item, ['status_verifikasi'], ''));

        if ($isVerified($item)) {
            return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
        }

        if (in_array($status, ['rejected', 'ditolak'], true)) {
            return 'bg-rose-50 text-rose-700 ring-rose-200';
        }

        return 'bg-amber-50 text-amber-700 ring-amber-200';
    };

    $kategoriTheme = function ($key) {
        return match ($key) {
            'remaja' => [
                'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                'iconBox' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
                'active' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
            ],
            'lansia' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'iconBox' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                'active' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            ],
            default => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'iconBox' => 'bg-sky-50 text-sky-700 ring-sky-100',
                'active' => 'border-sky-200 bg-sky-50 text-sky-700',
            ],
        };
    };

    $getParameterUtama = function ($item, string $kategori) use ($displayValue) {
        if ($kategori === 'lansia') {
            return [
                'Tensi' => $displayValue(data_get($item, 'tekanan_darah')),
                'Gula' => $displayValue(data_get($item, 'gula_darah'), 'mg/dL'),
                'Kolesterol' => $displayValue(data_get($item, 'kolesterol'), 'mg/dL'),
                'Kemandirian' => $displayValue(data_get($item, 'tingkat_kemandirian')),
            ];
        }

        if ($kategori === 'remaja') {
            return [
                'BB' => $displayValue(data_get($item, 'berat_badan'), 'kg'),
                'TB' => $displayValue(data_get($item, 'tinggi_badan'), 'cm'),
                'IMT' => $displayValue(data_get($item, 'imt')),
                'Tensi' => $displayValue(data_get($item, 'tekanan_darah')),
            ];
        }

        return [
            'BB' => $displayValue(data_get($item, 'berat_badan'), 'kg'),
            'TB' => $displayValue(data_get($item, 'tinggi_badan'), 'cm'),
            'LK' => $displayValue(data_get($item, 'lingkar_kepala'), 'cm'),
            'Gizi' => $displayValue(data_get($item, 'status_gizi')),
        ];
    };

    $buildUrl = function (array $overrides = []) use ($tab, $kategori, $search) {
        $query = array_merge([
            'tab' => $tab,
            'kategori' => $kategori,
            'search' => $search,
        ], $overrides);

        $query = collect($query)
            ->filter(fn ($value) => $value !== null && $value !== '' && $value !== 'semua')
            ->all();

        return route('bidan.pemeriksaan.index', $query);
    };

    $visibleCount = method_exists($pemeriksaans, 'count')
        ? $pemeriksaans->count()
        : count($pemeriksaans);

    $totalData = method_exists($pemeriksaans, 'total')
        ? $pemeriksaans->total()
        : $visibleCount;

    $pageTitle = $tab === 'verified'
        ? 'Data Pemeriksaan Tervalidasi'
        : 'Data Menunggu Validasi';

    $pageSubtitle = $tab === 'verified'
        ? 'Pemeriksaan yang sudah disahkan Bidan dan menjadi arsip Rekam Medis.'
        : 'Pemeriksaan dari Kader yang perlu ditinjau dan divalidasi Bidan.';

    $summaryCards = [
        [
            'label' => 'Total',
            'value' => $stats['total'] ?? 0,
            'icon' => 'ph-stethoscope',
            'class' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        ],
        [
            'label' => 'Menunggu',
            'value' => $stats['pending'] ?? 0,
            'icon' => 'ph-clock-countdown',
            'class' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
        [
            'label' => 'Tervalidasi',
            'value' => $stats['verified'] ?? 0,
            'icon' => 'ph-check-circle',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Bulan Ini',
            'value' => $stats['bulan_ini'] ?? 0,
            'icon' => 'ph-calendar-check',
            'class' => 'bg-sky-50 text-sky-700 ring-sky-100',
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
        min-height: 390px;
        contain: layout paint;
    }

    .parameter-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        max-width: 100%;
        border-radius: 999px;
        border: 1px solid rgb(226 232 240);
        background: rgba(255, 255, 255, .78);
        padding: 6px 10px;
        font-size: 11px;
        line-height: 1;
        font-weight: 800;
        color: rgb(71 85 105);
        white-space: nowrap;
    }

    .parameter-chip strong {
        color: rgb(15 23 42);
        font-weight: 900;
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
            min-height: 300px;
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
<div class="nexus-font nexus-page-enter space-y-4 pb-8 text-slate-800">

    {{-- HEADER --}}
    <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3.5 py-1.5 text-[11px] font-black uppercase tracking-[0.14em] text-emerald-700">
                    <i class="ph ph-stethoscope text-sm"></i>
                    Layanan Medis
                </div>

                <h1 class="mt-3 text-[24px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[28px]">
                    Pemeriksaan Klinis
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Bidan meninjau data pemeriksaan dari Kader, memberi catatan, lalu memvalidasi hasil pemeriksaan.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3.5 py-2 text-xs font-black text-emerald-700">
                    <i class="ph ph-check-circle"></i>
                    Output: Pemeriksaan Tervalidasi
                </span>

                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                    <i class="ph ph-database"></i>
                    <span id="pemeriksaanVisibleCount">{{ $visibleCount }}</span> tampil
                </span>
            </div>
        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="nexus-panel-enter grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="rounded-[20px] border border-white/80 bg-white/85 p-3.5 shadow-sm shadow-slate-200/60 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-black uppercase tracking-[0.11em] text-slate-400">
                            {{ $card['label'] }}
                        </p>

                        <h2 class="mt-1.5 text-lg font-black tracking-tight text-slate-900">
                            {{ $card['value'] }}
                        </h2>
                    </div>

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['class'] }}">
                        <i class="ph {{ $card['icon'] }} text-base"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- FILTER BAR --}}
    <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-4 shadow-sm shadow-slate-200/70 backdrop-blur md:p-5">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="inline-flex w-full rounded-2xl border border-slate-200 bg-slate-50 p-1 sm:w-fit">
                    <a href="{{ $buildUrl(['tab' => 'pending']) }}"
                       class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition sm:flex-none {{ $tab === 'pending' ? 'bg-white text-amber-700 shadow-sm ring-1 ring-amber-100' : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="ph ph-clock-countdown"></i>
                        Menunggu
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] text-amber-700 ring-1 ring-amber-100">
                            {{ $stats['pending'] ?? 0 }}
                        </span>
                    </a>

                    <a href="{{ $buildUrl(['tab' => 'verified']) }}"
                       class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition sm:flex-none {{ $tab === 'verified' ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-emerald-100' : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="ph ph-check-circle"></i>
                        Tervalidasi
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] text-emerald-700 ring-1 ring-emerald-100">
                            {{ $stats['verified'] ?? 0 }}
                        </span>
                    </a>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ $buildUrl(['kategori' => 'semua']) }}"
                       class="inline-flex min-h-[36px] items-center gap-2 rounded-full border px-3.5 py-2 text-xs font-black transition {{ $kategori === 'semua' ? 'border-slate-300 bg-slate-100 text-slate-800' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                        <i class="ph ph-users-three"></i>
                        Semua
                    </a>

                    @foreach($kategoriOptions as $key => $option)
                        @php
                            $theme = $kategoriTheme($key);
                            $active = $kategori === $key;
                        @endphp

                        <a href="{{ $buildUrl(['kategori' => $key]) }}"
                           class="inline-flex min-h-[36px] items-center gap-2 rounded-full border px-3.5 py-2 text-xs font-black transition {{ $active ? $theme['active'] : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                            <i class="ph {{ $option['icon'] }}"></i>
                            {{ $option['label'] }}
                            <span class="rounded-full bg-white/80 px-2 py-0.5 text-[10px] text-slate-500 ring-1 ring-slate-100">
                                {{ data_get($stats, "kategori.$key", 0) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- CONTENT --}}
    <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-emerald-600">
                    Direktori Pemeriksaan
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    {{ $pageTitle }}
                </h2>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    {{ $pageSubtitle }}
                </p>
            </div>

            <form method="GET"
                  action="{{ route('bidan.pemeriksaan.index') }}"
                  class="grid w-full gap-2 xl:max-w-2xl md:grid-cols-[1fr_auto_auto]">
                <input type="hidden" name="tab" value="{{ $tab }}">

                @if($kategori !== 'semua')
                    <input type="hidden" name="kategori" value="{{ $kategori }}">
                @endif

                <div class="relative">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                    <input type="text"
                           id="pemeriksaanLiveSearch"
                           name="search"
                           value="{{ $search }}"
                           autocomplete="off"
                           spellcheck="false"
                           inputmode="search"
                           placeholder="Cari nama atau NIK sasaran..."
                           class="min-h-[44px] w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-11 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">

                    <button type="button"
                            id="pemeriksaanClearSearch"
                            class="absolute right-3 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>

                <button type="submit"
                        class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                    <i class="ph ph-funnel"></i>
                    Filter
                </button>

                @if($search)
                    <a href="{{ $buildUrl(['search' => null]) }}"
                       class="inline-flex min-h-[44px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="nexus-list-stable">

            {{-- DESKTOP TABLE --}}
            <div class="nexus-scroll hidden max-h-[620px] overflow-auto lg:block">
                <table class="min-w-[1140px] w-full border-separate border-spacing-y-2">
                    <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur">
                        <tr class="text-left text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                            <th class="px-4 py-3">Sasaran</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Parameter</th>
                            <th class="px-4 py-3">Petugas</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="pemeriksaanTableBody">
                        @forelse($pemeriksaans as $pemeriksaan)
                            @php
                                $itemKategori = $getKategori($pemeriksaan);
                                $itemMeta = $kategoriOptions[$itemKategori] ?? $kategoriOptions['balita'];
                                $itemTheme = $kategoriTheme($itemKategori);

                                $namaPasien = $getNamaPasien($pemeriksaan);
                                $nikPasien = $getNikPasien($pemeriksaan);
                                $tanggal = $getValue($pemeriksaan, [
                                    'tanggal_periksa',
                                    'kunjungan.tanggal_kunjungan',
                                    'created_at',
                                ], null);

                                $parameterUtama = $getParameterUtama($pemeriksaan, $itemKategori);
                                $petugas = $getPetugas($pemeriksaan);
                                $verifikator = $getVerifikator($pemeriksaan);
                                $verified = $isVerified($pemeriksaan);

                                $searchName = mb_strtolower(trim((string) $namaPasien), 'UTF-8');
                                $searchNik = mb_strtolower(trim((string) $nikPasien), 'UTF-8');
                            @endphp

                            <tr class="js-pemeriksaan-row"
                                data-name="{{ $searchName }}"
                                data-nik="{{ $searchNik }}"
                                data-order="{{ $loop->index }}">
                                <td class="rounded-l-2xl border-y border-l border-slate-100 bg-slate-50/75 px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $itemTheme['iconBox'] }}">
                                            <i class="ph {{ $itemMeta['icon'] }} text-base"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="max-w-[230px] truncate text-sm font-black text-slate-900">
                                                {{ $namaPasien }}
                                            </p>

                                            <p class="mt-0.5 max-w-[230px] truncate text-xs font-semibold text-slate-500">
                                                NIK: {{ $nikPasien }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/75 px-4 py-3">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $itemTheme['badge'] }}">
                                        <i class="ph {{ $itemMeta['icon'] }}"></i>
                                        {{ $itemMeta['label'] }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/75 px-4 py-3">
                                    <p class="whitespace-nowrap text-sm font-black text-slate-800">
                                        {{ $formatDate($tanggal) }}
                                    </p>

                                    <p class="mt-0.5 whitespace-nowrap text-[11px] font-semibold text-slate-500">
                                        {{ $formatDateTime(data_get($pemeriksaan, 'created_at')) }}
                                    </p>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/75 px-4 py-3">
                                    <div class="flex max-w-[420px] flex-wrap gap-1.5">
                                        @foreach($parameterUtama as $label => $value)
                                            <span class="parameter-chip">
                                                {{ $label }}:
                                                <strong>{{ $value }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/75 px-4 py-3">
                                    <p class="max-w-[160px] truncate text-sm font-black text-slate-700">
                                        {{ $petugas }}
                                    </p>

                                    <p class="mt-0.5 max-w-[160px] truncate text-[11px] font-semibold text-slate-500">
                                        {{ $verified ? 'Validasi: ' . $verifikator : 'Menunggu Bidan' }}
                                    </p>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/75 px-4 py-3">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $statusTheme($pemeriksaan) }}">
                                        <i class="ph {{ $verified ? 'ph-check-circle' : 'ph-clock-countdown' }}"></i>
                                        {{ $statusLabel($pemeriksaan) }}
                                    </span>
                                </td>

                                <td class="rounded-r-2xl border-y border-r border-slate-100 bg-slate-50/75 px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('bidan.pemeriksaan.show', $pemeriksaan->id) }}"
                                           class="inline-flex min-h-[38px] items-center justify-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-black text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700">
                                            Detail
                                        </a>

                                        @unless($verified)
                                            <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}"
                                               class="inline-flex min-h-[38px] items-center justify-center rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                                                Validasi
                                            </a>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                                        <i class="ph ph-folder-simple-dashed text-2xl"></i>
                                    </div>

                                    <h3 class="mt-4 text-base font-black text-slate-800">
                                        Data Pemeriksaan Belum Ada
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Data akan tampil setelah pemeriksaan sasaran dicatat oleh Kader.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE CARD --}}
            <div id="pemeriksaanCardContainer" class="space-y-3 lg:hidden">
                @forelse($pemeriksaans as $pemeriksaan)
                    @php
                        $itemKategori = $getKategori($pemeriksaan);
                        $itemMeta = $kategoriOptions[$itemKategori] ?? $kategoriOptions['balita'];
                        $itemTheme = $kategoriTheme($itemKategori);

                        $namaPasien = $getNamaPasien($pemeriksaan);
                        $nikPasien = $getNikPasien($pemeriksaan);
                        $tanggal = $getValue($pemeriksaan, [
                            'tanggal_periksa',
                            'kunjungan.tanggal_kunjungan',
                            'created_at',
                        ], null);

                        $parameterUtama = $getParameterUtama($pemeriksaan, $itemKategori);
                        $petugas = $getPetugas($pemeriksaan);
                        $verified = $isVerified($pemeriksaan);

                        $searchName = mb_strtolower(trim((string) $namaPasien), 'UTF-8');
                        $searchNik = mb_strtolower(trim((string) $nikPasien), 'UTF-8');
                    @endphp

                    <article class="js-pemeriksaan-card rounded-2xl border border-slate-100 bg-slate-50/80 p-4"
                             data-name="{{ $searchName }}"
                             data-nik="{{ $searchNik }}"
                             data-order="{{ $loop->index }}">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $itemTheme['iconBox'] }}">
                                <i class="ph {{ $itemMeta['icon'] }} text-base"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="line-clamp-2 text-base font-black text-slate-900">
                                            {{ $namaPasien }}
                                        </h3>

                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                            NIK: {{ $nikPasien }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $itemTheme['badge'] }}">
                                        {{ $itemMeta['label'] }}
                                    </span>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach($parameterUtama as $label => $value)
                                        <span class="parameter-chip">
                                            {{ $label }}:
                                            <strong>{{ $value }}</strong>
                                        </span>
                                    @endforeach
                                </div>

                                <div class="mt-3 rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                    <p class="text-[10px] font-black uppercase text-slate-400">
                                        Tanggal / Petugas
                                    </p>

                                    <p class="mt-1 text-sm font-black text-slate-900">
                                        {{ $formatDate($tanggal) }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                        Petugas: {{ $petugas }}
                                    </p>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $statusTheme($pemeriksaan) }}">
                                        <i class="ph {{ $verified ? 'ph-check-circle' : 'ph-clock-countdown' }}"></i>
                                        {{ $statusLabel($pemeriksaan) }}
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-2 {{ $verified ? 'grid-cols-1' : 'grid-cols-2' }}">
                                    <a href="{{ route('bidan.pemeriksaan.show', $pemeriksaan->id) }}"
                                       class="inline-flex min-h-[40px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                                        Detail
                                    </a>

                                    @unless($verified)
                                        <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}"
                                           class="inline-flex min-h-[40px] items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                                            Validasi
                                        </a>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                            <i class="ph ph-folder-simple-dashed text-2xl"></i>
                        </div>

                        <h3 class="mt-4 text-base font-black text-slate-800">
                            Data Pemeriksaan Belum Ada
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Data akan tampil setelah pemeriksaan sasaran dicatat oleh Kader.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- LIVE EMPTY --}}
            <div id="pemeriksaanLiveEmpty"
                 class="hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                    <i class="ph ph-magnifying-glass text-2xl"></i>
                </div>

                <h3 class="mt-4 text-base font-black text-slate-800">
                    Data Tidak Cocok
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Tidak ada data pada halaman ini yang sesuai dengan nama atau NIK sasaran.
                </p>
            </div>

            {{-- PAGINATION --}}
            @if(method_exists($pemeriksaans, 'hasPages') && $pemeriksaans->hasPages())
                <div id="pemeriksaanPagination" class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-5 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm font-semibold text-slate-500">
                        Menampilkan {{ $pemeriksaans->firstItem() }} sampai {{ $pemeriksaans->lastItem() }} dari {{ $pemeriksaans->total() }} data
                    </p>

                    <div>
                        {{ $pemeriksaans->links() }}
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
        const searchInput = document.getElementById('pemeriksaanLiveSearch');
        const clearButton = document.getElementById('pemeriksaanClearSearch');
        const visibleCountText = document.getElementById('pemeriksaanVisibleCount');
        const liveEmpty = document.getElementById('pemeriksaanLiveEmpty');
        const pagination = document.getElementById('pemeriksaanPagination');
        const tableBody = document.getElementById('pemeriksaanTableBody');
        const cardContainer = document.getElementById('pemeriksaanCardContainer');

        const rows = Array.from(document.querySelectorAll('.js-pemeriksaan-row'));
        const cards = Array.from(document.querySelectorAll('.js-pemeriksaan-card'));

        const normalize = (value) => {
            return String(value || '')
                .toLowerCase()
                .trim();
        };

        const isNumericKeyword = (keyword) => {
            return /^[0-9]+$/.test(keyword);
        };

        const getMatchRank = (item, keyword) => {
            if (keyword === '') {
                return 10 + Number(item.dataset.order || 0);
            }

            const name = normalize(item.dataset.name);
            const nik = normalize(item.dataset.nik);

            if (isNumericKeyword(keyword)) {
                if (nik.startsWith(keyword)) {
                    return 0;
                }

                if (nik.includes(keyword)) {
                    return 1;
                }

                return 999;
            }

            if (name.startsWith(keyword)) {
                return 0;
            }

            if (name.includes(keyword)) {
                return 1;
            }

            return 999;
        };

        const setHidden = (element, hidden) => {
            if (!element) {
                return;
            }

            element.classList.toggle('nexus-live-hidden', hidden);
        };

        const sortAndRender = (items, keyword, container) => {
            let visible = 0;

            const rankedItems = items
                .map((item) => {
                    return {
                        item,
                        rank: getMatchRank(item, keyword),
                        name: normalize(item.dataset.name),
                        order: Number(item.dataset.order || 0),
                    };
                })
                .sort((a, b) => {
                    if (a.rank !== b.rank) {
                        return a.rank - b.rank;
                    }

                    if (a.name !== b.name) {
                        return a.name.localeCompare(b.name);
                    }

                    return a.order - b.order;
                });

            rankedItems.forEach((entry) => {
                const matched = entry.rank < 999;

                setHidden(entry.item, !matched);

                if (matched) {
                    visible += 1;

                    if (container) {
                        container.appendChild(entry.item);
                    }
                }
            });

            return visible;
        };

        let frameId = null;

        const applyFilter = () => {
            if (frameId) {
                cancelAnimationFrame(frameId);
            }

            frameId = requestAnimationFrame(() => {
                const keyword = normalize(searchInput?.value);

                const visibleRows = sortAndRender(rows, keyword, tableBody);
                const visibleCards = sortAndRender(cards, keyword, cardContainer);

                const visibleCount = rows.length > 0 ? visibleRows : visibleCards;
                const hasData = rows.length > 0 || cards.length > 0;

                if (visibleCountText) {
                    visibleCountText.textContent = String(visibleCount);
                }

                if (liveEmpty) {
                    liveEmpty.classList.toggle('hidden', !hasData || visibleCount > 0);
                }

                if (clearButton) {
                    const hasKeyword = keyword !== '';
                    clearButton.classList.toggle('hidden', !hasKeyword);
                    clearButton.classList.toggle('inline-flex', hasKeyword);
                }

                if (pagination) {
                    pagination.classList.toggle('hidden', keyword !== '');
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