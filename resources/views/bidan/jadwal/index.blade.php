@extends('layouts.bidan')

@section('title', 'Kelola Jadwal Posyandu')
@section('page-name', 'Kelola Jadwal')
@section('page-title', 'Kelola Jadwal Posyandu')

@php
    use Carbon\Carbon;

    $jadwals = $jadwals ?? collect();
    $search = $search ?? request('search', '');
    $status = $status ?? request('status', 'semua');
    $kategori = $kategori ?? request('kategori', 'semua');
    $target = $target ?? request('target', 'semua');

    $stats = $stats ?? [
        'total' => 0,
        'aktif' => 0,
        'bulan_ini' => 0,
        'mendatang' => 0,
    ];

    $kategoriOptions = $kategoriOptions ?? [
        'posyandu' => [
            'label' => 'Posyandu Rutin',
            'desc' => 'Agenda pelayanan Posyandu umum',
            'icon' => 'ph-house-line',
        ],
        'imunisasi' => [
            'label' => 'Imunisasi Balita',
            'desc' => 'Agenda imunisasi untuk Balita',
            'icon' => 'ph-syringe',
        ],
        'pemeriksaan' => [
            'label' => 'Pemeriksaan Klinis',
            'desc' => 'Agenda pemeriksaan oleh Bidan',
            'icon' => 'ph-stethoscope',
        ],
        'lainnya' => [
            'label' => 'Kegiatan Lainnya',
            'desc' => 'Agenda tambahan Posyandu',
            'icon' => 'ph-calendar-plus',
        ],
    ];

    $targetOptions = $targetOptions ?? [
        'semua' => [
            'label' => 'Semua Sasaran',
            'desc' => 'Seluruh sasaran Posyandu',
            'icon' => 'ph-users-three',
        ],
        'balita' => [
            'label' => 'Balita',
            'desc' => 'Sasaran Balita',
            'icon' => 'ph-baby',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Sasaran Remaja',
            'icon' => 'ph-user-focus',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Sasaran Lansia',
            'icon' => 'ph-heartbeat',
        ],
    ];

    $statusOptions = $statusOptions ?? [
        'aktif' => [
            'label' => 'Aktif',
            'desc' => 'Jadwal masih berlaku',
            'icon' => 'ph-check-circle',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'desc' => 'Jadwal sudah dilaksanakan',
            'icon' => 'ph-flag-checkered',
        ],
        'dibatalkan' => [
            'label' => 'Dibatalkan',
            'desc' => 'Jadwal dibatalkan',
            'icon' => 'ph-x-circle',
        ],
    ];

    $formatTanggal = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatHari = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('l');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatBulanPendek = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('M');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatTanggalAngka = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->format('d');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatWaktu = function ($mulai, $selesai) {
        try {
            $mulai = $mulai ? Carbon::parse($mulai)->format('H:i') : '-';
            $selesai = $selesai ? Carbon::parse($selesai)->format('H:i') : '-';

            return "{$mulai} - {$selesai} WIB";
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $isToday = function ($date) {
        if (!$date) {
            return false;
        }

        try {
            return Carbon::parse($date)->isToday();
        } catch (\Throwable $e) {
            return false;
        }
    };

    $isPastDate = function ($date) {
        if (!$date) {
            return false;
        }

        try {
            return Carbon::parse($date)->startOfDay()->lt(now()->startOfDay());
        } catch (\Throwable $e) {
            return false;
        }
    };

    $canModifyJadwal = function ($jadwal) {
        if (($jadwal->status ?? 'aktif') !== 'aktif') {
            return false;
        }

        if (empty($jadwal->tanggal)) {
            return false;
        }

        try {
            $tanggal = Carbon::parse($jadwal->tanggal)->format('Y-m-d');

            $waktuMulai = $jadwal->waktu_mulai
                ? Carbon::parse($jadwal->waktu_mulai)->format('H:i:s')
                : '00:00:00';

            $startDateTime = Carbon::parse($tanggal . ' ' . $waktuMulai);

            return now()->lt($startDateTime);
        } catch (\Throwable $e) {
            return false;
        }
    };

    $scheduleState = function ($jadwal) use ($canModifyJadwal) {
        if (($jadwal->status ?? 'aktif') === 'dibatalkan') {
            return [
                'label' => 'Dibatalkan',
                'desc' => 'Jadwal sudah dibatalkan',
                'icon' => 'ph-x-circle',
                'class' => 'bg-rose-50 text-rose-700 ring-rose-200',
            ];
        }

        if (($jadwal->status ?? 'aktif') === 'selesai') {
            return [
                'label' => 'Terkunci',
                'desc' => 'Jadwal selesai',
                'icon' => 'ph-lock-simple',
                'class' => 'bg-slate-100 text-slate-500 ring-slate-200',
            ];
        }

        if (!$canModifyJadwal($jadwal)) {
            return [
                'label' => 'Terkunci',
                'desc' => 'Waktu mulai sudah lewat',
                'icon' => 'ph-lock-simple',
                'class' => 'bg-slate-100 text-slate-500 ring-slate-200',
            ];
        }

        return [
            'label' => 'Bisa Diedit',
            'desc' => 'Jadwal belum dimulai',
            'icon' => 'ph-pencil-simple',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        ];
    };

    $statusTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'aktif' => [
                'label' => 'Aktif',
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'dot' => 'bg-emerald-500',
            ],
            'selesai' => [
                'label' => 'Selesai',
                'badge' => 'bg-slate-50 text-slate-600 ring-slate-200',
                'dot' => 'bg-slate-400',
            ],
            'dibatalkan' => [
                'label' => 'Dibatalkan',
                'badge' => 'bg-rose-50 text-rose-700 ring-rose-200',
                'dot' => 'bg-rose-500',
            ],
            default => [
                'label' => ucfirst((string) $value),
                'badge' => 'bg-slate-50 text-slate-600 ring-slate-200',
                'dot' => 'bg-slate-400',
            ],
        };
    };

    $kategoriTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'imunisasi' => [
                'badge' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
                'icon' => 'ph-syringe',
            ],
            'pemeriksaan' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'icon' => 'ph-stethoscope',
            ],
            'lainnya' => [
                'badge' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'icon' => 'ph-calendar-plus',
            ],
            default => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'icon' => 'ph-house-line',
            ],
        };
    };

    $targetTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'balita' => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'icon' => 'ph-baby',
            ],
            'remaja' => [
                'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                'icon' => 'ph-user-focus',
            ],
            'lansia' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'icon' => 'ph-heartbeat',
            ],
            default => [
                'badge' => 'bg-slate-50 text-slate-700 ring-slate-200',
                'icon' => 'ph-users-three',
            ],
        };
    };

    $getKategoriLabel = function ($value) use ($kategoriOptions) {
        return $kategoriOptions[$value]['label'] ?? ucfirst(str_replace('_', ' ', (string) $value));
    };

    $getTargetLabel = function ($value) use ($targetOptions) {
        return $targetOptions[$value]['label'] ?? ucfirst(str_replace('_', ' ', (string) $value));
    };

    $totalData = method_exists($jadwals, 'total')
        ? $jadwals->total()
        : count($jadwals);

    $currentCount = method_exists($jadwals, 'count')
        ? $jadwals->count()
        : count($jadwals);

    $summaryCards = [
        [
            'label' => 'Total Jadwal',
            'value' => $stats['total'] ?? 0,
            'icon' => 'ph-calendar-blank',
            'class' => 'bg-slate-50 text-slate-700 ring-slate-100',
        ],
        [
            'label' => 'Jadwal Aktif',
            'value' => $stats['aktif'] ?? 0,
            'icon' => 'ph-check-circle',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Bulan Ini',
            'value' => $stats['bulan_ini'] ?? 0,
            'icon' => 'ph-calendar-check',
            'class' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        ],
        [
            'label' => 'Mendatang',
            'value' => $stats['mendatang'] ?? 0,
            'icon' => 'ph-clock-countdown',
            'class' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
    ];
@endphp

@push('styles')
<style>
    .nexus-font {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .nexus-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, 0.35) transparent;
    }

    .nexus-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .nexus-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .nexus-scroll::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, 0.35);
        border-radius: 999px;
    }

    .nexus-page-enter {
        animation: nexusMainIn .14s cubic-bezier(.22, 1, .36, 1) both;
        will-change: transform, opacity;
    }

    .nexus-panel-enter {
        animation: nexusPanelIn .14s cubic-bezier(.22, 1, .36, 1) both;
        will-change: transform, opacity;
    }

    .nexus-list-stable {
        min-height: 420px;
        contain: layout paint;
    }

    .nexus-live-hidden {
        display: none !important;
    }

    @keyframes nexusMainIn {
        from {
            opacity: 0;
            transform: translate3d(0, 4px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes nexusPanelIn {
        from {
            opacity: 0;
            transform: translate3d(0, 3px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @media (max-width: 768px) {
        .nexus-page-enter,
        .nexus-panel-enter {
            animation-duration: .1s;
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
    <section class="nexus-panel-enter rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                    <i class="ph ph-calendar-check text-base"></i>
                    Agenda Posyandu
                </div>

                <h1 class="mt-4 text-[28px] font-black leading-tight tracking-[-0.03em] text-slate-900 md:text-[34px]">
                    Kelola Jadwal Posyandu
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Bidan dapat membuat dan mengubah jadwal selama jadwal masih aktif dan belum melewati waktu mulai. Jadwal yang sudah mulai, selesai, atau dibatalkan akan dikunci sebagai arsip.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-xs font-black text-emerald-700">
                        <i class="ph ph-database"></i>
                        <span id="jadwalVisibleCount">{{ $currentCount }}</span>
                        <span>jadwal tampil</span>
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-list-magnifying-glass"></i>
                        Total data: {{ $totalData }}
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-lock-simple"></i>
                        Jadwal selesai otomatis terkunci
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-start xl:justify-end">
                <a href="{{ route('bidan.jadwal.create') }}"
                   class="inline-flex min-h-[52px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 py-3 text-sm font-black text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="ph ph-plus text-lg"></i>
                    Buat Jadwal
                </a>
            </div>
        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="nexus-panel-enter grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-slate-500">
                            {{ $card['label'] }}
                        </p>

                        <h2 class="mt-2 truncate text-3xl font-black tracking-tight text-slate-900">
                            {{ $card['value'] }}
                        </h2>
                    </div>

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['class'] }}">
                        <i class="ph {{ $card['icon'] }} text-xl"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- CONTENT --}}
    <section class="nexus-panel-enter rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">

        {{-- TOOLBAR --}}
        <div class="mb-5 grid gap-4 xl:grid-cols-[130px_minmax(0,1fr)] xl:items-center">
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">
                    Daftar Jadwal
                </p>
            </div>

            <form method="GET"
                  action="{{ route('bidan.jadwal.index') }}"
                  id="jadwalFilterForm"
                  class="grid w-full gap-2 md:grid-cols-2 xl:grid-cols-[minmax(240px,1fr)_180px_190px_180px_112px_auto]">

                <div class="relative md:col-span-2 xl:col-span-1">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                    <input type="text"
                           id="jadwalLiveSearch"
                           name="search"
                           value="{{ $search }}"
                           autocomplete="off"
                           spellcheck="false"
                           inputmode="search"
                           placeholder="Ketik judul atau lokasi..."
                           class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-11 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">

                    <button type="button"
                            id="jadwalClearSearch"
                            class="absolute right-3 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>

                <select name="status"
                        id="jadwalStatusFilter"
                        class="min-h-[46px] rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-600 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    <option value="semua" @selected($status === 'semua')>Semua Status</option>
                    @foreach($statusOptions as $key => $option)
                        <option value="{{ $key }}" @selected($status === $key)>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>

                <select name="kategori"
                        id="jadwalKategoriFilter"
                        class="min-h-[46px] rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-600 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    <option value="semua" @selected($kategori === 'semua')>Semua Kategori</option>
                    @foreach($kategoriOptions as $key => $option)
                        <option value="{{ $key }}" @selected($kategori === $key)>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>

                <select name="target"
                        id="jadwalTargetFilter"
                        class="min-h-[46px] rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-600 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    <option value="semua" @selected($target === 'semua')>Semua Sasaran</option>
                    @foreach($targetOptions as $key => $option)
                        <option value="{{ $key }}" @selected($target === $key)>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="ph ph-funnel"></i>
                    Filter
                </button>

                @if($search || $status !== 'semua' || $kategori !== 'semua' || $target !== 'semua')
                    <a href="{{ route('bidan.jadwal.index') }}"
                       class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- RESULT --}}
        <div class="nexus-list-stable">

            {{-- DESKTOP TABLE --}}
            <div class="nexus-scroll hidden max-h-[610px] overflow-auto lg:block">
                <table class="min-w-[1220px] w-full border-separate border-spacing-y-3">
                    <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur">
                        <tr class="text-left text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Agenda</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Sasaran</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Akses</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($jadwals as $jadwal)
                            @php
                                $statusValue = strtolower((string) ($jadwal->status ?? 'aktif'));
                                $kategoriValue = strtolower((string) ($jadwal->kategori ?? 'posyandu'));
                                $targetValue = strtolower((string) ($jadwal->target_peserta ?? 'semua'));

                                $searchTitle = mb_strtolower(trim((string) ($jadwal->judul ?? '')), 'UTF-8');
                                $searchLocation = mb_strtolower(trim((string) ($jadwal->lokasi ?? '')), 'UTF-8');

                                $statusData = $statusTheme($jadwal->status ?? 'aktif');
                                $kategoriData = $kategoriTheme($jadwal->kategori ?? 'posyandu');
                                $targetData = $targetTheme($jadwal->target_peserta ?? 'semua');

                                $today = $isToday($jadwal->tanggal ?? null);
                                $past = $isPastDate($jadwal->tanggal ?? null);
                                $canModify = $canModifyJadwal($jadwal);
                                $stateData = $scheduleState($jadwal);

                                $tanggalLabel = $formatTanggal($jadwal->tanggal ?? null);
                                $hariLabel = $formatHari($jadwal->tanggal ?? null);
                                $waktuLabel = $formatWaktu($jadwal->waktu_mulai ?? null, $jadwal->waktu_selesai ?? null);
                            @endphp

                            <tr class="js-jadwal-row"
                                data-title="{{ $searchTitle }}"
                                data-location="{{ $searchLocation }}"
                                data-status="{{ $statusValue }}"
                                data-kategori="{{ $kategoriValue }}"
                                data-target="{{ $targetValue }}">
                                <td class="rounded-l-2xl border-y border-l border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-2xl bg-white ring-1 ring-slate-100">
                                            <span class="text-[10px] font-black uppercase text-emerald-600">
                                                {{ $formatBulanPendek($jadwal->tanggal ?? null) }}
                                            </span>

                                            <span class="text-xl font-black leading-none text-slate-900">
                                                {{ $formatTanggalAngka($jadwal->tanggal ?? null) }}
                                            </span>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="whitespace-nowrap text-sm font-black text-slate-900">
                                                {{ $hariLabel }}
                                            </p>

                                            <p class="mt-1 whitespace-nowrap text-xs font-semibold text-slate-500">
                                                {{ $tanggalLabel }}
                                            </p>

                                            @if($today)
                                                <span class="mt-2 inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-black text-amber-700 ring-1 ring-amber-200">
                                                    Hari Ini
                                                </span>
                                            @elseif($past && ($jadwal->status ?? '') === 'aktif')
                                                <span class="mt-2 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-500 ring-1 ring-slate-200">
                                                    Terlewat
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="min-w-0">
                                        <p class="max-w-[320px] truncate text-sm font-black text-slate-900">
                                            {{ $jadwal->judul ?? 'Judul Jadwal Tidak Terdata' }}
                                        </p>

                                        <p class="mt-1 max-w-[320px] truncate text-xs font-semibold text-slate-500">
                                            {{ $waktuLabel }}
                                        </p>

                                        <p class="mt-2 flex max-w-[320px] items-center gap-1 truncate text-xs font-semibold text-slate-500">
                                            <i class="ph ph-map-pin"></i>
                                            {{ $jadwal->lokasi ?? '-' }}
                                        </p>
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $kategoriData['badge'] }}">
                                        <i class="ph {{ $kategoriOptions[$jadwal->kategori]['icon'] ?? $kategoriData['icon'] }}"></i>
                                        {{ $getKategoriLabel($jadwal->kategori ?? 'posyandu') }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $targetData['badge'] }}">
                                        <i class="ph {{ $targetOptions[$jadwal->target_peserta]['icon'] ?? $targetData['icon'] }}"></i>
                                        {{ $getTargetLabel($jadwal->target_peserta ?? 'semua') }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $statusData['badge'] }}">
                                        <span class="h-2 w-2 rounded-full {{ $statusData['dot'] }}"></span>
                                        {{ $statusData['label'] }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $stateData['class'] }}"
                                          title="{{ $stateData['desc'] }}">
                                        <i class="ph {{ $stateData['icon'] }}"></i>
                                        {{ $stateData['label'] }}
                                    </span>
                                </td>

                                <td class="rounded-r-2xl border-y border-r border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('bidan.jadwal.show', $jadwal->id) }}"
                                           class="inline-flex min-h-[40px] items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                                            Detail
                                        </a>

                                        @if($canModify)
                                            <a href="{{ route('bidan.jadwal.edit', $jadwal->id) }}"
                                               class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700"
                                               title="Edit jadwal">
                                                <i class="ph ph-pencil-simple"></i>
                                            </a>

                                            <form action="{{ route('bidan.jadwal.destroy', $jadwal->id) }}"
                                                  method="POST"
                                                  class="js-delete-jadwal">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-rose-100 bg-rose-50 text-rose-600 transition hover:bg-rose-100"
                                                        title="Hapus jadwal">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-100 text-slate-300"
                                                  title="Jadwal tidak dapat diedit">
                                                <i class="ph ph-lock-simple"></i>
                                            </span>

                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-100 text-slate-300"
                                                  title="Jadwal tidak dapat dihapus">
                                                <i class="ph ph-trash"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                                        <i class="ph ph-calendar-x text-3xl"></i>
                                    </div>

                                    <h3 class="mt-4 text-lg font-black text-slate-800">
                                        Jadwal Tidak Ditemukan
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Belum ada jadwal yang cocok dengan filter saat ini.
                                    </p>

                                    <a href="{{ route('bidan.jadwal.create') }}"
                                       class="mt-5 inline-flex min-h-[44px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                                        <i class="ph ph-plus"></i>
                                        Buat Jadwal
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE CARD --}}
            <div class="space-y-3 lg:hidden">
                @forelse($jadwals as $jadwal)
                    @php
                        $statusValue = strtolower((string) ($jadwal->status ?? 'aktif'));
                        $kategoriValue = strtolower((string) ($jadwal->kategori ?? 'posyandu'));
                        $targetValue = strtolower((string) ($jadwal->target_peserta ?? 'semua'));

                        $searchTitle = mb_strtolower(trim((string) ($jadwal->judul ?? '')), 'UTF-8');
                        $searchLocation = mb_strtolower(trim((string) ($jadwal->lokasi ?? '')), 'UTF-8');

                        $statusData = $statusTheme($jadwal->status ?? 'aktif');
                        $kategoriData = $kategoriTheme($jadwal->kategori ?? 'posyandu');
                        $targetData = $targetTheme($jadwal->target_peserta ?? 'semua');

                        $today = $isToday($jadwal->tanggal ?? null);
                        $canModify = $canModifyJadwal($jadwal);
                        $stateData = $scheduleState($jadwal);

                        $tanggalLabel = $formatTanggal($jadwal->tanggal ?? null);
                        $hariLabel = $formatHari($jadwal->tanggal ?? null);
                        $waktuLabel = $formatWaktu($jadwal->waktu_mulai ?? null, $jadwal->waktu_selesai ?? null);
                    @endphp

                    <article class="js-jadwal-card rounded-2xl border border-slate-100 bg-slate-50/80 p-4"
                             data-title="{{ $searchTitle }}"
                             data-location="{{ $searchLocation }}"
                             data-status="{{ $statusValue }}"
                             data-kategori="{{ $kategoriValue }}"
                             data-target="{{ $targetValue }}">
                        <div class="flex items-start gap-3">
                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-2xl bg-white ring-1 ring-slate-100">
                                <span class="text-[10px] font-black uppercase text-emerald-600">
                                    {{ $formatBulanPendek($jadwal->tanggal ?? null) }}
                                </span>

                                <span class="text-xl font-black leading-none text-slate-900">
                                    {{ $formatTanggalAngka($jadwal->tanggal ?? null) }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="line-clamp-2 text-base font-black text-slate-900">
                                            {{ $jadwal->judul ?? 'Judul Jadwal Tidak Terdata' }}
                                        </h3>

                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                            {{ $hariLabel }}, {{ $tanggalLabel }}
                                        </p>
                                    </div>

                                    @if($today)
                                        <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-black text-amber-700 ring-1 ring-amber-200">
                                            Hari Ini
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase text-slate-400">Waktu</p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-900">
                                            {{ $waktuLabel }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase text-slate-400">Status</p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-900">
                                            {{ $statusData['label'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $kategoriData['badge'] }}">
                                        <i class="ph {{ $kategoriOptions[$jadwal->kategori]['icon'] ?? $kategoriData['icon'] }}"></i>
                                        {{ $getKategoriLabel($jadwal->kategori ?? 'posyandu') }}
                                    </span>

                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $targetData['badge'] }}">
                                        <i class="ph {{ $targetOptions[$jadwal->target_peserta]['icon'] ?? $targetData['icon'] }}"></i>
                                        {{ $getTargetLabel($jadwal->target_peserta ?? 'semua') }}
                                    </span>

                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $stateData['class'] }}">
                                        <i class="ph {{ $stateData['icon'] }}"></i>
                                        {{ $stateData['label'] }}
                                    </span>
                                </div>

                                <p class="mt-3 flex items-center gap-1 truncate text-xs font-semibold text-slate-500">
                                    <i class="ph ph-map-pin"></i>
                                    {{ $jadwal->lokasi ?? '-' }}
                                </p>

                                <div class="mt-4 grid grid-cols-[1fr_auto_auto] gap-2">
                                    <a href="{{ route('bidan.jadwal.show', $jadwal->id) }}"
                                       class="inline-flex min-h-[42px] items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                                        Detail
                                    </a>

                                    @if($canModify)
                                        <a href="{{ route('bidan.jadwal.edit', $jadwal->id) }}"
                                           class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700"
                                           title="Edit jadwal">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>

                                        <form action="{{ route('bidan.jadwal.destroy', $jadwal->id) }}"
                                              method="POST"
                                              class="js-delete-jadwal">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-rose-100 bg-rose-50 text-rose-600 transition hover:bg-rose-100"
                                                    title="Hapus jadwal">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-slate-100 bg-slate-100 text-slate-300"
                                              title="Jadwal tidak dapat diedit">
                                            <i class="ph ph-lock-simple"></i>
                                        </span>

                                        <span class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-slate-100 bg-slate-100 text-slate-300"
                                              title="Jadwal tidak dapat dihapus">
                                            <i class="ph ph-trash"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                            <i class="ph ph-calendar-x text-3xl"></i>
                        </div>

                        <h3 class="mt-4 text-lg font-black text-slate-800">
                            Jadwal Tidak Ditemukan
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Belum ada jadwal yang cocok dengan filter saat ini.
                        </p>

                        <a href="{{ route('bidan.jadwal.create') }}"
                           class="mt-5 inline-flex min-h-[44px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                            <i class="ph ph-plus"></i>
                            Buat Jadwal
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- LIVE EMPTY STATE --}}
            <div id="jadwalLiveEmpty"
                 class="hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                    <i class="ph ph-magnifying-glass text-3xl"></i>
                </div>

                <h3 class="mt-4 text-lg font-black text-slate-800">
                    Jadwal Tidak Cocok
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Tidak ada jadwal pada halaman ini yang sesuai dengan judul, lokasi, atau filter yang dipilih.
                </p>
            </div>

            {{-- PAGINATION --}}
            @if(method_exists($jadwals, 'hasPages') && $jadwals->hasPages())
                <div id="jadwalPagination" class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-5 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm font-semibold text-slate-500">
                        Menampilkan {{ $jadwals->firstItem() }} sampai {{ $jadwals->lastItem() }} dari {{ $jadwals->total() }} jadwal
                    </p>

                    <div>
                        {{ $jadwals->links() }}
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
        const searchInput = document.getElementById('jadwalLiveSearch');
        const clearSearchButton = document.getElementById('jadwalClearSearch');
        const statusSelect = document.getElementById('jadwalStatusFilter');
        const kategoriSelect = document.getElementById('jadwalKategoriFilter');
        const targetSelect = document.getElementById('jadwalTargetFilter');
        const visibleCountText = document.getElementById('jadwalVisibleCount');
        const liveEmpty = document.getElementById('jadwalLiveEmpty');

        const desktopRows = Array.from(document.querySelectorAll('.js-jadwal-row'));
        const mobileCards = Array.from(document.querySelectorAll('.js-jadwal-card'));

        const normalize = (value) => {
            return String(value || '')
                .toLowerCase()
                .trim();
        };

        const itemMatches = (item, keyword, selectedStatus, selectedKategori, selectedTarget) => {
            const itemTitle = normalize(item.dataset.title);
            const itemLocation = normalize(item.dataset.location);
            const itemStatus = normalize(item.dataset.status);
            const itemKategori = normalize(item.dataset.kategori);
            const itemTarget = normalize(item.dataset.target);

            const keywordMatch = keyword === ''
                || itemTitle.includes(keyword)
                || itemLocation.includes(keyword);

            const statusMatch = selectedStatus === 'semua' || itemStatus === selectedStatus;
            const kategoriMatch = selectedKategori === 'semua' || itemKategori === selectedKategori;
            const targetMatch = selectedTarget === 'semua' || itemTarget === selectedTarget;

            return keywordMatch && statusMatch && kategoriMatch && targetMatch;
        };

        const setHidden = (element, isHidden) => {
            if (!element) {
                return;
            }

            element.classList.toggle('nexus-live-hidden', isHidden);
        };

        let frameId = null;

        const applyLiveFilter = () => {
            if (frameId) {
                cancelAnimationFrame(frameId);
            }

            frameId = requestAnimationFrame(() => {
                const keyword = normalize(searchInput?.value);
                const selectedStatus = normalize(statusSelect?.value || 'semua');
                const selectedKategori = normalize(kategoriSelect?.value || 'semua');
                const selectedTarget = normalize(targetSelect?.value || 'semua');

                let desktopVisible = 0;

                desktopRows.forEach((row) => {
                    const visible = itemMatches(row, keyword, selectedStatus, selectedKategori, selectedTarget);

                    setHidden(row, !visible);

                    if (visible) {
                        desktopVisible += 1;
                    }
                });

                mobileCards.forEach((card) => {
                    const visible = itemMatches(card, keyword, selectedStatus, selectedKategori, selectedTarget);

                    setHidden(card, !visible);
                });

                if (visibleCountText) {
                    visibleCountText.textContent = String(desktopVisible);
                }

                if (liveEmpty) {
                    const hasData = desktopRows.length > 0 || mobileCards.length > 0;
                    liveEmpty.classList.toggle('hidden', !hasData || desktopVisible > 0);
                }

                if (clearSearchButton) {
                    const hasKeyword = keyword !== '';
                    clearSearchButton.classList.toggle('hidden', !hasKeyword);
                    clearSearchButton.classList.toggle('inline-flex', hasKeyword);
                }
            });
        };

        searchInput?.addEventListener('input', applyLiveFilter, { passive: true });
        statusSelect?.addEventListener('change', applyLiveFilter);
        kategoriSelect?.addEventListener('change', applyLiveFilter);
        targetSelect?.addEventListener('change', applyLiveFilter);

        clearSearchButton?.addEventListener('click', () => {
            if (!searchInput) {
                return;
            }

            searchInput.value = '';
            searchInput.focus();
            applyLiveFilter();
        });

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('.js-delete-jadwal');

            if (!form) {
                return;
            }

            const confirmed = confirm('Hapus jadwal ini? Jadwal yang dihapus tidak akan tampil lagi pada daftar agenda.');

            if (!confirmed) {
                event.preventDefault();
            }
        });

        applyLiveFilter();
    })();
</script>
@endpush