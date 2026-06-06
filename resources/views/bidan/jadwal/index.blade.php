@extends('layouts.bidan')

@section('title', 'Kelola Jadwal Posyandu')
@section('page-name', 'Kelola Jadwal')
@section('page-title', 'Kelola Jadwal Posyandu')

@php
    use Carbon\Carbon;

    Carbon::setLocale('id');

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
            'icon' => 'ph ph-house-line',
        ],
        'imunisasi' => [
            'label' => 'Imunisasi Balita',
            'desc' => 'Agenda imunisasi untuk Balita',
            'icon' => 'ph ph-syringe',
        ],
        'pemeriksaan' => [
            'label' => 'Pemeriksaan Klinis',
            'desc' => 'Agenda pemeriksaan oleh Bidan',
            'icon' => 'ph ph-stethoscope',
        ],
        'lainnya' => [
            'label' => 'Kegiatan Lainnya',
            'desc' => 'Agenda tambahan Posyandu',
            'icon' => 'ph ph-calendar-plus',
        ],
    ];

    $targetOptions = $targetOptions ?? [
        'semua' => [
            'label' => 'Semua Sasaran',
            'desc' => 'Seluruh sasaran Posyandu',
            'icon' => 'ph ph-users-three',
        ],
        'balita' => [
            'label' => 'Balita',
            'desc' => 'Sasaran Balita',
            'icon' => 'ph ph-baby',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Sasaran Remaja',
            'icon' => 'ph ph-user-focus',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Sasaran Lansia',
            'icon' => 'ph ph-heartbeat',
        ],
    ];

    $statusOptions = $statusOptions ?? [
        'aktif' => [
            'label' => 'Aktif',
            'desc' => 'Jadwal masih berlaku',
            'icon' => 'ph ph-check-circle',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'desc' => 'Jadwal sudah dilaksanakan',
            'icon' => 'ph ph-flag-checkered',
        ],
        'dibatalkan' => [
            'label' => 'Dibatalkan',
            'desc' => 'Jadwal dibatalkan',
            'icon' => 'ph ph-x-circle',
        ],
    ];

    $formatTanggal = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatHari = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('l');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatBulanPendek = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('M');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatTanggalAngka = function ($date) {
        if (! $date) {
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
        if (! $date) {
            return false;
        }

        try {
            return Carbon::parse($date)->isToday();
        } catch (\Throwable $e) {
            return false;
        }
    };

    $isPastDate = function ($date) {
        if (! $date) {
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
                'icon' => 'ph ph-x-circle',
                'class' => 'bg-rose-50 text-rose-700 ring-rose-200',
            ];
        }

        if (($jadwal->status ?? 'aktif') === 'selesai') {
            return [
                'label' => 'Terkunci',
                'desc' => 'Jadwal selesai',
                'icon' => 'ph ph-lock-simple',
                'class' => 'bg-slate-100 text-slate-500 ring-slate-200',
            ];
        }

        if (! $canModifyJadwal($jadwal)) {
            return [
                'label' => 'Terkunci',
                'desc' => 'Waktu mulai sudah lewat',
                'icon' => 'ph ph-lock-simple',
                'class' => 'bg-slate-100 text-slate-500 ring-slate-200',
            ];
        }

        return [
            'label' => 'Bisa Diedit',
            'desc' => 'Jadwal belum dimulai',
            'icon' => 'ph ph-pencil-simple',
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
                'icon' => 'ph ph-syringe',
            ],
            'pemeriksaan' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'icon' => 'ph ph-stethoscope',
            ],
            'lainnya' => [
                'badge' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'icon' => 'ph ph-calendar-plus',
            ],
            default => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'icon' => 'ph ph-house-line',
            ],
        };
    };

    $targetTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'balita' => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'icon' => 'ph ph-baby',
            ],
            'remaja' => [
                'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                'icon' => 'ph ph-user-focus',
            ],
            'lansia' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'icon' => 'ph ph-heartbeat',
            ],
            default => [
                'badge' => 'bg-slate-50 text-slate-700 ring-slate-200',
                'icon' => 'ph ph-users-three',
            ],
        };
    };

    $getKategoriLabel = function ($value) use ($kategoriOptions) {
        return $kategoriOptions[$value]['label'] ?? ucfirst(str_replace('_', ' ', (string) $value));
    };

    $getTargetLabel = function ($value) use ($targetOptions) {
        return $targetOptions[$value]['label'] ?? ucfirst(str_replace('_', ' ', (string) $value));
    };

    $totalData = method_exists($jadwals, 'total') ? $jadwals->total() : count($jadwals);
    $currentCount = method_exists($jadwals, 'count') ? $jadwals->count() : count($jadwals);

    $summaryCards = [
        [
            'label' => 'Total Jadwal',
            'value' => $stats['total'] ?? 0,
            'icon' => 'ph ph-calendar-blank',
            'class' => 'bg-slate-50 text-slate-700 ring-slate-100',
        ],
        [
            'label' => 'Jadwal Aktif',
            'value' => $stats['aktif'] ?? 0,
            'icon' => 'ph ph-check-circle',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Bulan Ini',
            'value' => $stats['bulan_ini'] ?? 0,
            'icon' => 'ph ph-calendar-check',
            'class' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        ],
        [
            'label' => 'Mendatang',
            'value' => $stats['mendatang'] ?? 0,
            'icon' => 'ph ph-clock-countdown',
            'class' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
    ];
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

    .pc-jadwal-page {
        background:
            radial-gradient(circle at 8% 6%, rgba(16, 185, 129, .13), transparent 28%),
            radial-gradient(circle at 92% 8%, rgba(14, 165, 233, .12), transparent 26%),
            radial-gradient(circle at 52% 100%, rgba(245, 158, 11, .09), transparent 30%),
            linear-gradient(135deg, #f4fff9 0%, #eef9ff 46%, #f8fafc 100%);
    }

    .pc-grid-bg {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .pc-glass {
        background: rgba(255, 255, 255, .84);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .065);
    }

    .pc-field {
        width: 100%;
        border-radius: 1rem;
        border: 1px solid rgba(203, 213, 225, .96);
        background: rgba(255, 255, 255, .9);
        padding: .78rem 1rem;
        font-size: .875rem;
        font-weight: 800;
        color: #334155;
        outline: none;
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .pc-field:focus {
        border-color: rgba(16, 185, 129, .56);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .10);
        background: rgba(255, 255, 255, .98);
    }

    .pc-label {
        display: block;
        margin-bottom: .45rem;
        font-size: .7rem;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #64748b;
    }

    .pc-summary-card {
        min-height: 118px;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .pc-summary-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, .075);
    }

    .pc-jadwal-head,
    .pc-jadwal-row-main {
        display: grid;
        grid-template-columns: 94px minmax(280px, 1.1fr) minmax(145px, .55fr) minmax(145px, .55fr) minmax(145px, .55fr) minmax(145px, .55fr) minmax(210px, .72fr);
        gap: 14px;
        align-items: center;
    }

    .pc-jadwal-head {
        padding: 0 16px 10px 16px;
    }

    .pc-jadwal-head span {
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #94a3b8;
        white-space: nowrap;
    }

    .pc-jadwal-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .pc-jadwal-row {
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .95);
        border-radius: 1.35rem;
        background: rgba(255, 255, 255, .92);
        padding: 14px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .04);
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease, background .16s ease;
    }

    .pc-jadwal-row:hover {
        transform: translateY(-1px);
        border-color: rgba(16, 185, 129, .24);
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 16px 38px rgba(15, 23, 42, .065);
    }

    .pc-date-box {
        display: flex;
        min-height: 82px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 1.1rem;
        border: 1px solid rgba(16, 185, 129, .18);
        background: linear-gradient(135deg, rgba(236, 253, 245, .95), rgba(240, 249, 255, .9));
    }

    .pc-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .36rem .68rem;
        font-size: 11px;
        font-weight: 950;
        line-height: 1;
        white-space: nowrap;
        box-shadow: inset 0 0 0 1px currentColor;
        box-shadow: none;
        ring: 1px;
    }

    .pc-soft-box {
        min-height: 62px;
        border-radius: 1rem;
        border: 1px solid rgba(226, 232, 240, .86);
        background: rgba(248, 250, 252, .78);
        padding: 10px 12px;
    }

    .pc-mini-label {
        display: block;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .13em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .pc-mini-value {
        margin-top: 4px;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.35;
        color: #0f172a;
    }

    .pc-mini-help {
        margin-top: 2px;
        font-size: 11px;
        font-weight: 750;
        line-height: 1.35;
        color: #64748b;
    }

    .pc-line-1 {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pc-line-2 {
        display: -webkit-box;
        overflow: hidden;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .pc-action-bar {
        margin-top: 12px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        border-top: 1px solid rgba(226, 232, 240, .75);
        padding-top: 12px;
    }

    .pc-action-btn {
        display: inline-flex;
        min-height: 38px;
        min-width: 94px;
        align-items: center;
        justify-content: center;
        gap: .42rem;
        border-radius: .9rem;
        padding: .48rem .78rem;
        font-size: 12px;
        font-weight: 950;
        line-height: 1;
        transition: transform .16s ease, background .16s ease, border-color .16s ease;
        white-space: nowrap;
    }

    .pc-action-btn:hover {
        transform: translateY(-1px);
    }

    .pc-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
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
        width: min(100%, 500px);
        transform: translateY(12px) scale(.97);
        opacity: 0;
        border-radius: 1.75rem;
        border: 1px solid rgba(255, 255, 255, .78);
        background:
            radial-gradient(circle at 0% 0%, rgba(244, 63, 94, .14), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(14, 165, 233, .12), transparent 34%),
            rgba(255, 255, 255, .96);
        box-shadow: 0 30px 90px rgba(15, 23, 42, .25);
        transition: transform .18s ease, opacity .18s ease;
    }

    .pc-modal-backdrop.is-open .pc-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    @media (max-width: 1535px) {
        .pc-jadwal-head,
        .pc-jadwal-row-main {
            grid-template-columns: 86px minmax(250px, 1.15fr) minmax(145px, .58fr) minmax(145px, .58fr) minmax(145px, .58fr) minmax(200px, .75fr);
        }

        .pc-col-access {
            display: none;
        }
    }

    @media (max-width: 1279px) {
        .pc-jadwal-head {
            display: none;
        }

        .pc-jadwal-row-main {
            grid-template-columns: 78px minmax(0, 1fr);
            align-items: start;
        }

        .pc-col-category,
        .pc-col-target,
        .pc-col-status,
        .pc-col-access {
            grid-column: 2 / -1;
        }

        .pc-action-bar {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            justify-content: stretch;
        }

        .pc-action-btn {
            width: 100%;
            min-width: 0;
        }
    }

    @media (max-width: 640px) {
        .pc-jadwal-row {
            padding: 12px;
        }

        .pc-jadwal-row-main {
            grid-template-columns: 70px minmax(0, 1fr);
            gap: 10px;
        }

        .pc-date-box {
            min-height: 76px;
        }

        .pc-action-bar {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
@endpush

@section('content')
<div class="pc-jadwal-page relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 pc-grid-bg opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1500px] space-y-5">
        <section class="pc-glass relative overflow-hidden rounded-[1.8rem] border border-white/80 p-5 sm:p-6">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-24 h-72 w-72 rounded-full bg-sky-300/16 blur-3xl"></div>

            <div class="relative grid gap-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/80 px-3 py-1.5 text-xs font-black text-emerald-700">
                        <i class="ph ph-calendar-check"></i>
                        Agenda Posyandu
                    </div>

                    <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                        Kelola Jadwal Posyandu
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm font-semibold leading-6 text-slate-600">
                        Bidan dapat membuat dan mengubah jadwal selama jadwal masih aktif dan belum melewati waktu mulai. Jadwal yang sudah mulai, selesai, atau dibatalkan akan dikunci sebagai arsip.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3 xl:justify-end">
                    <div class="rounded-2xl border border-white/80 bg-white/70 px-4 py-3 shadow-sm">
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-slate-400">
                            Data Tampil
                        </p>
                        <p class="mt-1 text-sm font-black text-slate-900">
                            {{ $currentCount }} jadwal tampil
                        </p>
                        <p class="text-xs font-bold text-slate-500">
                            Total data: {{ $totalData }}
                        </p>
                    </div>

                    <a href="{{ route('bidan.jadwal.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5">
                        <i class="ph ph-plus-circle text-lg"></i>
                        Buat Jadwal
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach($summaryCards as $card)
                <div class="pc-summary-card pc-glass rounded-[1.5rem] border border-white/80 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.15em] text-slate-400">
                                {{ $card['label'] }}
                            </p>
                            <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                                {{ number_format($card['value']) }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['class'] }}">
                            <i class="{{ $card['icon'] }} text-xl"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
            <form method="GET"
                  action="{{ route('bidan.jadwal.index') }}"
                  id="jadwalFilterForm"
                  class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_210px_210px_210px_auto_auto] xl:items-end">
                <div>
                    <label class="pc-label" for="jadwalSearch">Cari Jadwal</label>
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="jadwalSearch"
                               type="search"
                               name="search"
                               value="{{ $search }}"
                               autocomplete="off"
                               class="pc-field pl-11"
                               placeholder="Cari judul atau lokasi jadwal...">
                    </div>
                </div>

                <div>
                    <label class="pc-label" for="jadwalStatus">Status</label>
                    <select id="jadwalStatus" name="status" class="pc-field">
                        <option value="semua" @selected($status === 'semua')>Semua Status</option>
                        @foreach($statusOptions as $key => $option)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="pc-label" for="jadwalKategori">Kategori</label>
                    <select id="jadwalKategori" name="kategori" class="pc-field">
                        <option value="semua" @selected($kategori === 'semua')>Semua Kategori</option>
                        @foreach($kategoriOptions as $key => $option)
                            <option value="{{ $key }}" @selected($kategori === $key)>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="pc-label" for="jadwalTarget">Sasaran</label>
                    <select id="jadwalTarget" name="target" class="pc-field">
                        <option value="semua" @selected($target === 'semua')>Semua Sasaran</option>
                        @foreach($targetOptions as $key => $option)
                            <option value="{{ $key }}" @selected($target === $key)>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="inline-flex h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5">
                    <i class="ph ph-funnel"></i>
                    Filter
                </button>

                @if($search || $status !== 'semua' || $kategori !== 'semua' || $target !== 'semua')
                    <a href="{{ route('bidan.jadwal.index') }}"
                       class="inline-flex h-[46px] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                        <i class="ph ph-arrow-clockwise"></i>
                        Reset
                    </a>
                @endif
            </form>
        </section>

        <section id="jadwalListBlock" class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
            <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                        Daftar Jadwal
                    </p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                        Agenda Pelayanan Posyandu
                    </h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        Jadwal aktif dapat diedit dan dihapus selama belum melewati waktu mulai.
                    </p>
                </div>

                <span id="jadwalVisibleCounter"
                      class="inline-flex w-fit items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs font-black uppercase tracking-[.14em] text-emerald-700">
                    {{ number_format($currentCount) }} Data
                </span>
            </div>

            <div class="pc-jadwal-head">
                <span>Tanggal</span>
                <span>Agenda</span>
                <span>Kategori</span>
                <span>Sasaran</span>
                <span>Status</span>
                <span class="pc-col-access">Akses</span>
                <span class="text-right">Aksi</span>
            </div>

            <div class="pc-jadwal-list">
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

                    <article class="pc-jadwal-row"
                             data-live-row
                             data-title="{{ $searchTitle }}"
                             data-location="{{ $searchLocation }}"
                             data-status="{{ $statusValue }}"
                             data-kategori="{{ $kategoriValue }}"
                             data-target="{{ $targetValue }}">
                        <div class="pc-jadwal-row-main">
                            <div class="pc-date-box">
                                <p class="text-[11px] font-black uppercase tracking-[.12em] text-emerald-700">
                                    {{ $formatBulanPendek($jadwal->tanggal ?? null) }}
                                </p>
                                <p class="text-3xl font-black leading-none text-slate-950">
                                    {{ $formatTanggalAngka($jadwal->tanggal ?? null) }}
                                </p>
                                <p class="mt-1 text-[11px] font-black text-slate-500">
                                    {{ $hariLabel }}
                                </p>
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="pc-line-1 text-base font-black text-slate-950">
                                        {{ $jadwal->judul ?? 'Judul Jadwal Tidak Terdata' }}
                                    </h3>

                                    @if($today)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-[.08em] text-emerald-700">
                                            Hari Ini
                                        </span>
                                    @elseif($past && ($jadwal->status ?? '') === 'aktif')
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-[.08em] text-amber-700">
                                            Terlewat
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                    <div class="pc-soft-box">
                                        <span class="pc-mini-label">Waktu</span>
                                        <p class="pc-mini-value">{{ $waktuLabel }}</p>
                                        <p class="pc-mini-help">{{ $tanggalLabel }}</p>
                                    </div>

                                    <div class="pc-soft-box">
                                        <span class="pc-mini-label">Lokasi</span>
                                        <p class="pc-mini-value pc-line-1">{{ $jadwal->lokasi ?? '-' }}</p>
                                        <p class="pc-mini-help">Tempat pelayanan</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pc-col-category">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-black ring-1 {{ $kategoriData['badge'] }}">
                                    <i class="{{ $kategoriData['icon'] }}"></i>
                                    {{ $getKategoriLabel($jadwal->kategori ?? 'posyandu') }}
                                </span>
                            </div>

                            <div class="pc-col-target">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-black ring-1 {{ $targetData['badge'] }}">
                                    <i class="{{ $targetData['icon'] }}"></i>
                                    {{ $getTargetLabel($jadwal->target_peserta ?? 'semua') }}
                                </span>
                            </div>

                            <div class="pc-col-status">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-black ring-1 {{ $statusData['badge'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $statusData['dot'] }}"></span>
                                    {{ $statusData['label'] }}
                                </span>
                            </div>

                            <div class="pc-col-access">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-black ring-1 {{ $stateData['class'] }}">
                                    <i class="{{ $stateData['icon'] }}"></i>
                                    {{ $stateData['label'] }}
                                </span>
                            </div>

                            <div class="hidden xl:block"></div>
                        </div>

                        <div class="pc-action-bar">
                            <a href="{{ route('bidan.jadwal.show', $jadwal) }}"
                               class="pc-action-btn border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                                <i class="ph ph-eye"></i>
                                Detail
                            </a>

                            @if($canModify)
                                <a href="{{ route('bidan.jadwal.edit', $jadwal) }}"
                                   class="pc-action-btn border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100">
                                    <i class="ph ph-pencil-simple"></i>
                                    Edit
                                </a>

                                <form action="{{ route('bidan.jadwal.destroy', $jadwal) }}"
                                      method="POST"
                                      data-delete-form
                                      data-delete-title="Hapus jadwal ini?"
                                      data-delete-message="Jadwal {{ $jadwal->judul ?? 'ini' }} akan dihapus dan tidak tampil lagi pada daftar agenda.">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="pc-action-btn border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">
                                        <i class="ph ph-trash"></i>
                                        Hapus
                                    </button>
                                </form>
                            @else
                                <span class="pc-action-btn border border-slate-200 bg-slate-50 text-slate-500">
                                    <i class="ph ph-lock-simple"></i>
                                    Terkunci
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-emerald-200 bg-emerald-50/70 p-8 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                            <i class="ph ph-folder-open text-xl"></i>
                        </div>

                        <h3 class="mt-4 text-base font-black text-slate-950">
                            Jadwal Tidak Ditemukan
                        </h3>

                        <p class="mt-2 text-sm font-semibold text-slate-500">
                            Belum ada jadwal yang cocok dengan filter saat ini.
                        </p>

                        <a href="{{ route('bidan.jadwal.create') }}"
                           class="mt-4 inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-500/20">
                            <i class="ph ph-plus-circle"></i>
                            Buat Jadwal
                        </a>
                    </div>
                @endforelse
            </div>

            <div id="jadwalNoLiveResult" class="mt-4 hidden rounded-[1.5rem] border border-dashed border-amber-200 bg-amber-50/80 p-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-amber-600 shadow-sm">
                    <i class="ph ph-magnifying-glass text-xl"></i>
                </div>

                <h3 class="mt-4 text-base font-black text-slate-950">
                    Jadwal Tidak Cocok
                </h3>

                <p class="mt-2 text-sm font-semibold text-slate-500">
                    Tidak ada jadwal pada halaman ini yang sesuai dengan judul, lokasi, atau filter yang dipilih.
                </p>
            </div>

            @if(method_exists($jadwals, 'hasPages') && $jadwals->hasPages())
                <div class="mt-5 rounded-2xl border border-slate-200 bg-white/70 p-4">
                    <div class="mb-3 text-sm font-bold text-slate-500">
                        Menampilkan {{ $jadwals->firstItem() }} sampai {{ $jadwals->lastItem() }} dari {{ $jadwals->total() }} jadwal
                    </div>

                    {{ $jadwals->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('modals')
<div id="pcJadwalDeleteModal" class="pc-modal-backdrop" aria-hidden="true">
    <div class="pc-modal-card p-6">
        <div class="flex gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-rose-500 to-orange-500 text-white shadow-lg shadow-rose-500/20">
                <i class="ph ph-warning-circle text-2xl"></i>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-black uppercase tracking-[.18em] text-rose-700">
                    Konfirmasi
                </p>

                <h3 id="pcJadwalDeleteTitle" class="mt-1 text-lg font-black text-slate-950">
                    Hapus jadwal ini?
                </h3>

                <p id="pcJadwalDeleteMessage" class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                    Jadwal yang dihapus tidak akan tampil lagi pada daftar agenda.
                </p>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button"
                    id="pcJadwalDeleteCancel"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                Batal
            </button>

            <button type="button"
                    id="pcJadwalDeleteSubmit"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-orange-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-rose-400/20 transition hover:-translate-y-0.5">
                <i class="ph ph-trash"></i>
                Hapus Jadwal
            </button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    const filterForm = document.querySelector('#jadwalFilterForm');
    const searchInput = document.querySelector('#jadwalSearch');
    const statusInput = document.querySelector('#jadwalStatus');
    const kategoriInput = document.querySelector('#jadwalKategori');
    const targetInput = document.querySelector('#jadwalTarget');
    const visibleCounter = document.querySelector('#jadwalVisibleCounter');
    const emptyLive = document.querySelector('#jadwalNoLiveResult');

    let modal = document.querySelector('#pcJadwalDeleteModal');
    const modalTitle = document.querySelector('#pcJadwalDeleteTitle');
    const modalMessage = document.querySelector('#pcJadwalDeleteMessage');
    const modalCancel = document.querySelector('#pcJadwalDeleteCancel');
    const modalSubmit = document.querySelector('#pcJadwalDeleteSubmit');

    let selectedDeleteForm = null;

    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    function normalize(value) {
        return String(value || '').toLowerCase().trim();
    }

    function rows() {
        return Array.from(document.querySelectorAll('[data-live-row]'));
    }

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function showDeleteModal(form) {
        if (!modal) {
            HTMLFormElement.prototype.submit.call(form);
            return;
        }

        selectedDeleteForm = form;

        if (modalTitle) {
            modalTitle.textContent = form.dataset.deleteTitle || 'Hapus jadwal ini?';
        }

        if (modalMessage) {
            modalMessage.textContent = form.dataset.deleteMessage || 'Jadwal yang dihapus tidak akan tampil lagi pada daftar agenda.';
        }

        if (modalSubmit) {
            modalSubmit.disabled = false;
            modalSubmit.classList.remove('opacity-70', 'cursor-not-allowed');
            modalSubmit.innerHTML = '<i class="ph ph-trash"></i> Hapus Jadwal';
        }

        lockBody();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function hideDeleteModal() {
        if (!modal) {
            return;
        }

        selectedDeleteForm = null;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    function filterRows() {
        const keyword = normalize(searchInput ? searchInput.value : '');
        const status = statusInput ? statusInput.value : 'semua';
        const kategori = kategoriInput ? kategoriInput.value : 'semua';
        const target = targetInput ? targetInput.value : 'semua';

        let totalVisible = 0;

        rows().forEach(function (row) {
            const title = normalize(row.dataset.title);
            const location = normalize(row.dataset.location);
            const rowStatus = normalize(row.dataset.status);
            const rowKategori = normalize(row.dataset.kategori);
            const rowTarget = normalize(row.dataset.target);

            let visible = true;

            if (keyword !== '') {
                visible = title.includes(keyword) || location.includes(keyword);
            }

            if (visible && status !== 'semua') {
                visible = rowStatus === status;
            }

            if (visible && kategori !== 'semua') {
                visible = rowKategori === kategori;
            }

            if (visible && target !== 'semua') {
                visible = rowTarget === target;
            }

            row.classList.toggle('hidden', !visible);

            if (visible) {
                totalVisible++;
            }
        });

        if (visibleCounter) {
            visibleCounter.textContent = totalVisible + ' Data';
        }

        if (emptyLive) {
            emptyLive.classList.toggle('hidden', totalVisible !== 0 || rows().length === 0);
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }

    [statusInput, kategoriInput, targetInput].forEach(function (input) {
        if (!input) {
            return;
        }

        input.addEventListener('change', filterRows);
    });

    if (filterForm) {
        filterForm.addEventListener('submit', function () {
            filterRows();
        });
    }

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-delete-form]');

        if (!form) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        showDeleteModal(form);
    }, true);

    if (modalCancel) {
        modalCancel.addEventListener('click', hideDeleteModal);
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                hideDeleteModal();
            }
        });
    }

    if (modalSubmit) {
        modalSubmit.addEventListener('click', function () {
            if (!selectedDeleteForm) {
                hideDeleteModal();
                return;
            }

            modalSubmit.disabled = true;
            modalSubmit.classList.add('opacity-70', 'cursor-not-allowed');
            modalSubmit.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Menghapus...';

            HTMLFormElement.prototype.submit.call(selectedDeleteForm);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            hideDeleteModal();
        }
    });

    filterRows();
})();
</script>
@endpush