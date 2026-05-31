@extends('layouts.bidan')

@section('title', 'Dashboard Bidan')
@section('page-name', 'Dashboard')
@section('page-title', 'Dashboard Bidan')

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    $stats = $stats ?? [];
    $monthlyStats = collect($monthlyStats ?? []);
    $recentPemeriksaans = collect($recentPemeriksaans ?? []);
    $recentImunisasi = collect($recentImunisasi ?? []);
    $jadwalTerdekat = collect($jadwalTerdekat ?? []);
    $notifications = collect($notifications ?? []);

    if ($monthlyStats->isEmpty()) {
        $monthlyStats = collect(range(5, 0))->map(function ($monthOffset) {
            $date = now('Asia/Jakarta')->copy()->subMonthsNoOverflow($monthOffset);

            return [
                'label' => $date->translatedFormat('M Y'),
                'short' => $date->translatedFormat('M'),
                'count' => 0,
            ];
        });
    }

    $trendData = $monthlyStats->take(6)->values()->map(function ($item) {
        return [
            'label' => data_get($item, 'label', data_get($item, 'short', '-')),
            'short' => data_get($item, 'short', data_get($item, 'label', '-')),
            'count' => (int) data_get($item, 'count', 0),
        ];
    });

    $routeSafe = function (string $name, mixed $params = []) {
        if (!Route::has($name)) {
            return '#';
        }

        try {
            return route($name, $params);
        } catch (\Throwable $e) {
            return '#';
        }
    };

    $number = function ($value) {
        return number_format((int) ($value ?? 0), 0, ',', '.');
    };

    $stat = function (string $key, mixed $default = 0) use ($stats) {
        return data_get($stats, $key, $default);
    };

    $user = Auth::user();

    $userName = data_get($user, 'name')
        ?? data_get($user, 'nama')
        ?? data_get($user, 'nama_lengkap')
        ?? 'Bidan';

    $todayLabel = now('Asia/Jakarta')->translatedFormat('l, d F Y');

    $pendingCount = (int) $stat('menunggu_validasi');
    $verifiedCount = (int) $stat('tervalidasi');
    $revisionCount = (int) $stat('perlu_revisi');

    $statusActualTotal = $pendingCount + $verifiedCount + $revisionCount;
    $statusTotal = max(1, $statusActualTotal);

    $pendingPercent = round(($pendingCount / $statusTotal) * 100, 2);
    $verifiedPercent = round(($verifiedCount / $statusTotal) * 100, 2);
    $revisionStart = $pendingPercent + $verifiedPercent;

    $donutStyle = $statusActualTotal > 0
        ? "background: conic-gradient(#f59e0b 0 {$pendingPercent}%, #10b981 {$pendingPercent}% {$revisionStart}%, #f43f5e {$revisionStart}% 100%);"
        : "background: #e2e8f0;";

    $totalSasaran = max(1, (int) $stat('total_sasaran'));

    $kategoriTheme = function ($kategori) {
        return match (strtolower((string) $kategori)) {
            'remaja' => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200',
            'lansia' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
            default => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
        };
    };

    $statusTheme = function ($status) {
        $status = strtolower((string) $status);

        return match ($status) {
            'verified', 'tervalidasi', 'approved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
            'rejected', 'ditolak', 'revisi', 'perlu_revisi', 'perlu_perbaikan' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
            default => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        };
    };

    $kpiCards = [
        [
            'label' => 'Menunggu Validasi',
            'value' => $stat('menunggu_validasi'),
            'desc' => 'Data perlu ditinjau',
            'icon' => 'ph-clock-countdown',
            'url' => $routeSafe('bidan.pemeriksaan.index', ['tab' => 'pending']),
            'accent' => 'bg-amber-500',
            'accentLight' => 'bg-amber-50 text-amber-600 ring-1 ring-amber-200',
        ],
        [
            'label' => 'Tervalidasi',
            'value' => $stat('tervalidasi'),
            'desc' => 'Masuk Rekam Medis',
            'icon' => 'ph-check-circle',
            'url' => $routeSafe('bidan.pemeriksaan.index', ['tab' => 'verified']),
            'accent' => 'bg-emerald-500',
            'accentLight' => 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200',
        ],
        [
            'label' => 'Jadwal Aktif',
            'value' => $stat('jadwal_aktif'),
            'desc' => 'Agenda pelayanan',
            'icon' => 'ph-calendar-check',
            'url' => $routeSafe('bidan.jadwal.index'),
            'accent' => 'bg-sky-500',
            'accentLight' => 'bg-sky-50 text-sky-600 ring-1 ring-sky-200',
        ],
        [
            'label' => 'Imunisasi Bulan Ini',
            'value' => $stat('imunisasi_bulan_ini'),
            'desc' => 'Catatan Balita',
            'icon' => 'ph-syringe',
            'url' => $routeSafe('bidan.imunisasi.index'),
            'accent' => 'bg-cyan-500',
            'accentLight' => 'bg-cyan-50 text-cyan-600 ring-1 ring-cyan-200',
        ],
    ];

    $quickActions = [
        [
            'label' => 'Pemeriksaan Klinis',
            'desc' => 'Validasi pemeriksaan dari Kader',
            'icon' => 'ph-stethoscope',
            'url' => $routeSafe('bidan.pemeriksaan.index', ['tab' => 'pending']),
        ],
        [
            'label' => 'Rekam Medis',
            'desc' => 'Lihat arsip pemeriksaan',
            'icon' => 'ph-folder-simple-user',
            'url' => $routeSafe('bidan.rekam-medis.index'),
        ],
        [
            'label' => 'Kelola Jadwal',
            'desc' => 'Atur agenda pelayanan',
            'icon' => 'ph-calendar-plus',
            'url' => $routeSafe('bidan.jadwal.index'),
        ],
        [
            'label' => 'Imunisasi Balita',
            'desc' => 'Catat layanan imunisasi',
            'icon' => 'ph-syringe',
            'url' => $routeSafe('bidan.imunisasi.index'),
        ],
    ];

    $sasaranItems = [
        [
            'label' => 'Balita',
            'value' => $stat('balita'),
            'icon' => 'ph-baby',
            'theme' => 'bg-sky-50 text-sky-600 ring-1 ring-sky-200',
            'bar' => 'bg-sky-500',
        ],
        [
            'label' => 'Remaja',
            'value' => $stat('remaja'),
            'icon' => 'ph-user-focus',
            'theme' => 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-200',
            'bar' => 'bg-indigo-500',
        ],
        [
            'label' => 'Lansia',
            'value' => $stat('lansia'),
            'icon' => 'ph-heartbeat',
            'theme' => 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200',
            'bar' => 'bg-emerald-500',
        ],
    ];
@endphp

@push('styles')
<style>
    .bidan-dashboard {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background:
            radial-gradient(circle at 8% 6%, rgba(16, 185, 129, .08), transparent 28%),
            radial-gradient(circle at 92% 10%, rgba(14, 165, 233, .07), transparent 28%),
            linear-gradient(135deg, #f6fbf9 0%, #f8fafc 52%, #eefbf6 100%);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .nx-card {
        background: rgba(255, 255, 255, .88);
        border: 1px solid rgba(226, 232, 240, .72);
        box-shadow:
            0 0 0 1px rgba(0, 0, 0, .01),
            0 1px 2px rgba(0, 0, 0, .035),
            0 12px 32px rgba(15, 23, 42, .035);
        transition:
            box-shadow .25s ease,
            transform .25s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .nx-card:hover {
        border-color: rgba(203, 213, 225, .9);
        box-shadow:
            0 0 0 1px rgba(0, 0, 0, .01),
            0 6px 18px rgba(15, 23, 42, .055),
            0 18px 46px rgba(15, 23, 42, .04);
    }

    .nx-soft {
        background: rgba(248, 250, 252, .72);
        border: 1px solid rgba(226, 232, 240, .58);
    }

    .nx-enter {
        animation: nxFadeUp .45s cubic-bezier(.22, 1, .36, 1) both;
    }

    .nx-d1 { animation-delay: .05s; }
    .nx-d2 { animation-delay: .10s; }
    .nx-d3 { animation-delay: .15s; }
    .nx-d4 { animation-delay: .20s; }
    .nx-d5 { animation-delay: .25s; }

    @keyframes nxFadeUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .nx-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 90% 12%, rgba(255, 255, 255, .12), transparent 32%),
            radial-gradient(circle at 8% 92%, rgba(20, 184, 166, .20), transparent 34%),
            linear-gradient(155deg, #052e24 0%, #0f766e 48%, #0891b2 100%);
    }

    .nx-hero::before {
        content: "";
        position: absolute;
        top: -42%;
        right: -8%;
        width: 330px;
        height: 330px;
        background: radial-gradient(circle, rgba(255, 255, 255, .08) 0%, transparent 65%);
        border-radius: 999px;
        pointer-events: none;
    }

    .nx-hero::after {
        content: "";
        position: absolute;
        bottom: -26%;
        left: -4%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(255, 255, 255, .06) 0%, transparent 65%);
        border-radius: 999px;
        pointer-events: none;
    }

    .nx-donut {
        box-shadow: inset 0 2px 12px rgba(0, 0, 0, .06);
    }

    .nx-table tbody tr {
        transition: background-color .18s ease;
    }

    .nx-table tbody tr:hover {
        background-color: rgba(248, 250, 252, .9);
    }

    .nx-btn {
        background: #0f172a;
        color: #fff;
        transition: all .2s ease;
    }

    .nx-btn:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(5, 150, 105, .30);
    }

    .nx-btn:active {
        transform: translateY(0);
        transition-duration: .08s;
    }

    .nx-action {
        transition: all .25s cubic-bezier(.22, 1, .36, 1);
        border: 1px solid rgba(226, 232, 240, .62);
    }

    .nx-action:hover {
        border-color: rgba(13, 148, 136, .22);
        background: rgba(240, 253, 250, .62);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
    }

    .nx-progress {
        animation: progFill 1s cubic-bezier(.22, 1, .36, 1) both;
        transform-origin: left;
    }

    @keyframes progFill {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }

    .clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* =========================================================
       Responsive Realtime SVG Trend Chart
    ========================================================= */
    .rt-chart-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(226, 232, 240, .75);
        background:
            radial-gradient(circle at 88% 12%, rgba(16, 185, 129, .10), transparent 28%),
            linear-gradient(135deg, rgba(248, 250, 252, .95), rgba(236, 253, 245, .58));
    }

    .rt-chart-wrap::before {
        content: "";
        position: absolute;
        inset: 0;
        opacity: .75;
        pointer-events: none;
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 34px 34px;
    }

    .rt-chart-svg {
        position: relative;
        z-index: 2;
        display: block;
        width: 100%;
        height: clamp(240px, 30vw, 330px);
    }

    .rt-chart-point,
    .rt-chart-hit,
    .rt-month-btn {
        cursor: pointer;
    }

    .rt-chart-point {
        transition: transform .16s cubic-bezier(.22, 1, .36, 1), filter .16s ease;
        transform-box: fill-box;
        transform-origin: center;
    }

    .rt-chart-point.is-active {
        filter: drop-shadow(0 10px 16px rgba(5, 150, 105, .22));
    }

    .rt-chart-hit {
        fill: transparent;
    }

    .rt-chart-tooltip {
        position: absolute;
        z-index: 8;
        min-width: 132px;
        pointer-events: none;
        transform: translate(-50%, -120%) scale(.96);
        opacity: 0;
        transition: opacity .14s ease, transform .14s cubic-bezier(.22, 1, .36, 1);
    }

    .rt-chart-tooltip.is-show {
        opacity: 1;
        transform: translate(-50%, -128%) scale(1);
    }

    .rt-tooltip-inner {
        position: relative;
        border-radius: 14px;
        background: #0f172a;
        color: #ffffff;
        padding: 10px 12px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, .24);
    }

    .rt-tooltip-inner::after {
        content: "";
        position: absolute;
        left: 50%;
        top: 100%;
        transform: translateX(-50%);
        border: 7px solid transparent;
        border-top-color: #0f172a;
    }

    .rt-month-btn {
        border: 1px solid rgba(226, 232, 240, .9);
        background: rgba(255, 255, 255, .72);
        transition: all .16s cubic-bezier(.22, 1, .36, 1);
    }

    .rt-month-btn:hover,
    .rt-month-btn.is-active {
        border-color: rgba(16, 185, 129, .35);
        background: rgba(236, 253, 245, .95);
        color: #047857;
        transform: translateY(-1px);
    }

    .rt-live-dot {
        position: relative;
        display: inline-flex;
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #10b981;
    }

    .rt-live-dot::after {
        content: "";
        position: absolute;
        inset: -5px;
        border-radius: 999px;
        border: 1px solid rgba(16, 185, 129, .35);
        animation: rtPulse 1.6s ease-out infinite;
    }

    @keyframes rtPulse {
        from {
            transform: scale(.6);
            opacity: 1;
        }

        to {
            transform: scale(1.4);
            opacity: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .nx-enter,
        .nx-d1,
        .nx-d2,
        .nx-d3,
        .nx-d4,
        .nx-d5,
        .nx-progress {
            animation: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="bidan-dashboard nx-enter -m-4 min-h-screen p-4 pb-6 text-slate-800 md:-m-6 md:p-6">
    <div class="mx-auto max-w-[1540px] space-y-4">

        {{-- HERO --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <div class="nx-hero flex h-full min-h-[280px] flex-col justify-between rounded-2xl p-6 text-white shadow-lg shadow-teal-900/10 md:p-7">
                    <div class="relative z-10">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-teal-100/80 backdrop-blur-sm">
                            <i class="ph-fill ph-heartbeat"></i>
                            Dashboard Bidan
                        </div>
                        <h1 class="max-w-lg text-2xl font-bold leading-tight tracking-tight md:text-3xl">
                            Ringkasan Layanan Kesehatan Posyandu
                        </h1>
                        <p class="mt-2 max-w-md text-sm leading-relaxed text-teal-100/70">
                            Pantau validasi pemeriksaan, jadwal pelayanan, imunisasi Balita, dan Rekam Medis dalam satu ruang kerja terintegrasi.
                        </p>
                    </div>
                    <div class="relative z-10 mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3.5 backdrop-blur-sm">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-teal-200/60">Hari Ini</p>
                            <p class="mt-1.5 text-2xl font-bold tabular-nums">{{ $number($stat('pemeriksaan_hari_ini')) }}</p>
                            <p class="mt-0.5 text-[11px] text-teal-100/50">Pemeriksaan</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3.5 backdrop-blur-sm">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-teal-200/60">Bulan Ini</p>
                            <p class="mt-1.5 text-2xl font-bold tabular-nums">{{ $number($stat('pemeriksaan_bulan_ini')) }}</p>
                            <p class="mt-0.5 text-[11px] text-teal-100/50">Total data</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3.5 backdrop-blur-sm">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-teal-200/60">Prioritas</p>
                            <p class="mt-1.5 text-2xl font-bold tabular-nums">{{ $number($stat('menunggu_validasi')) }}</p>
                            <p class="mt-0.5 text-[11px] text-teal-100/50">Menunggu validasi</p>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="xl:col-span-5">
                <div class="nx-card flex h-full min-h-[280px] flex-col rounded-2xl p-5 md:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-teal-600">Ruang Kerja</p>
                            <h2 class="mt-0.5 text-lg font-bold text-slate-900">Aksi Cepat</h2>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 ring-1 ring-teal-200">
                            <i class="ph-fill ph-briefcase-medical text-base"></i>
                        </div>
                    </div>
                    <div class="grid flex-1 gap-2.5 sm:grid-cols-2">
                        @foreach($quickActions as $action)
                            <a href="{{ $action['url'] }}" class="nx-action group flex items-start gap-3 rounded-xl bg-slate-50/50 p-3.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-teal-600 shadow-sm ring-1 ring-slate-200 transition group-hover:scale-105 group-hover:shadow-md">
                                    <i class="ph-fill {{ $action['icon'] }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-semibold text-slate-900">{{ $action['label'] }}</h3>
                                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">{{ $action['desc'] }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-3 flex items-center gap-3 rounded-xl border border-teal-100 bg-teal-50/60 px-4 py-3">
                        <div class="flex-1">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-teal-700">Tanggal Sistem</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-700">{{ $todayLabel }}</p>
                        </div>
                        <i class="ph-fill ph-calendar-check text-xl text-teal-600"></i>
                    </div>
                </div>
            </aside>
        </section>

        {{-- KPI --}}
        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($kpiCards as $i => $card)
                <a href="{{ $card['url'] }}" class="nx-card nx-enter nx-d{{ $i + 1 }} group relative overflow-hidden rounded-2xl p-5">
                    <div class="absolute inset-x-0 top-0 h-[3px] {{ $card['accent'] }}"></div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                            <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900">{{ $number($card['value']) }}</p>
                            <p class="mt-1 truncate text-[11px] text-slate-500">{{ $card['desc'] }}</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $card['accentLight'] }}">
                            <i class="ph-fill {{ $card['icon'] }} text-lg"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </section>

        {{-- ANALYTICS --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            {{-- Responsive Realtime Trend Chart --}}
            <div class="xl:col-span-8">
                <div class="nx-card h-full rounded-2xl p-5 md:p-6">
                    <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-teal-600">
                                    Tren Pemeriksaan
                                </p>

                                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">
                                    <span class="rt-live-dot"></span>
                                    Live View
                                </span>
                            </div>

                            <h2 class="mt-1 text-lg font-bold text-slate-900">
                                Aktivitas Pemeriksaan 6 Bulan
                            </h2>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Klik titik atau bulan untuk melihat detail jumlah pemeriksaan.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-4 py-3 text-right">
                            <p id="rtTrendLabel" class="text-[10px] font-black uppercase tracking-[0.14em] text-emerald-700">
                                Bulan
                            </p>

                            <p id="rtTrendValue" class="mt-1 text-3xl font-black leading-none text-slate-950">
                                0
                            </p>

                            <p class="mt-1 text-[11px] font-semibold text-slate-500">
                                Pemeriksaan
                            </p>
                        </div>
                    </div>

                    <div
                        id="rtTrendChart"
                        class="rt-chart-wrap"
                        data-points='@json($trendData)'
                        data-source-url="{{ Route::has('bidan.dashboard.trend') ? route('bidan.dashboard.trend') : '' }}"
                    >
                        <svg
                            id="rtTrendSvg"
                            class="rt-chart-svg"
                            viewBox="0 0 720 300"
                            preserveAspectRatio="xMidYMid meet"
                            role="img"
                            aria-label="Grafik tren pemeriksaan enam bulan"
                        ></svg>

                        <div id="rtTrendTooltip" class="rt-chart-tooltip">
                            <div class="rt-tooltip-inner">
                                <p id="rtTooltipLabel" class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                    Bulan
                                </p>
                                <p id="rtTooltipValue" class="mt-1 text-xl font-black text-white">
                                    0
                                </p>
                                <p class="mt-0.5 text-[11px] font-semibold text-slate-400">
                                    Pemeriksaan
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="rtTrendMonths" class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-6"></div>
                </div>
            </div>

            {{-- Donut Chart --}}
            <div class="xl:col-span-4">
                <div class="nx-card h-full rounded-2xl p-5 md:p-6">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-teal-600">Status Validasi</p>
                            <h2 class="mt-0.5 text-lg font-bold text-slate-900">Ringkasan</h2>
                            <p class="mt-0.5 text-[11px] text-slate-500">Komposisi status.</p>
                        </div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200">
                            <i class="ph-fill ph-chart-pie-slice text-base"></i>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-5">
                        <div class="nx-donut relative h-36 w-36 rounded-full" style="{{ $donutStyle }}">
                            <div class="absolute inset-3.5 flex flex-col items-center justify-center rounded-full bg-white shadow-sm">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total</p>
                                <p class="mt-0.5 text-2xl font-bold tabular-nums text-slate-900">{{ $number($statusActualTotal) }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-500">Pemeriksaan</p>
                            </div>
                        </div>

                        <div class="grid w-full gap-1.5">
                            <div class="flex items-center justify-between rounded-xl border border-amber-100 bg-amber-50/50 px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500/20"></span>
                                    <span class="text-xs font-semibold text-slate-700">Menunggu Validasi</span>
                                </div>
                                <span class="text-xs font-bold tabular-nums text-slate-900">{{ $number($pendingCount) }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl border border-emerald-100 bg-emerald-50/50 px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/20"></span>
                                    <span class="text-xs font-semibold text-slate-700">Tervalidasi</span>
                                </div>
                                <span class="text-xs font-bold tabular-nums text-slate-900">{{ $number($verifiedCount) }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl border border-rose-100 bg-rose-50/50 px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500 shadow-sm shadow-rose-500/20"></span>
                                    <span class="text-xs font-semibold text-slate-700">Perlu Revisi</span>
                                </div>
                                <span class="text-xs font-bold tabular-nums text-slate-900">{{ $number($revisionCount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- PEMERIKSAAN TERBARU --}}
        <section class="nx-card rounded-2xl p-5 md:p-6">
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-teal-600">Pemeriksaan</p>
                    <h2 class="mt-0.5 text-lg font-bold text-slate-900">Pemeriksaan Terbaru</h2>
                    <p class="mt-0.5 text-[11px] text-slate-500">Data terbaru yang masuk ke sistem.</p>
                </div>
                <a href="{{ $routeSafe('bidan.pemeriksaan.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">
                    Lihat Semua
                    <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden overflow-hidden rounded-xl border border-slate-100 bg-slate-50/40 xl:block">
                <table class="nx-table w-full">
                    <thead>
                        <tr class="border-b border-slate-100 bg-white/70 text-left">
                            <th class="w-[30%] px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Sasaran</th>
                            <th class="w-[16%] px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Kategori</th>
                            <th class="w-[16%] px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="w-[28%] px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Parameter</th>
                            <th class="w-[10%] px-5 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentPemeriksaans->take(4) as $item)
                            @php
                                $showUrl = data_get($item, 'id') ? $routeSafe('bidan.pemeriksaan.show', data_get($item, 'id')) : '#';
                                $parameters = collect(data_get($item, 'parameter', []))->take(3);
                            @endphp
                            <tr class="bg-white/35 align-middle">
                                <td class="px-5 py-4">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ data_get($item, 'nama', '-') }}</p>
                                        <p class="mt-1 truncate text-xs text-slate-500">NIK: {{ data_get($item, 'nik', '-') }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ data_get($item, 'tanggal', '-') }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex max-w-full rounded-lg px-3 py-1 text-[11px] font-semibold {{ $kategoriTheme(data_get($item, 'kategori_raw')) }}">
                                        <span class="truncate">{{ data_get($item, 'kategori', '-') }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex max-w-full rounded-lg px-3 py-1 text-[11px] font-semibold {{ $statusTheme(data_get($item, 'status_raw')) }}">
                                        <span class="truncate">{{ data_get($item, 'status', '-') }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($parameters as $label => $value)
                                            <div class="flex items-center gap-2 rounded-lg border border-slate-100 bg-white px-2.5 py-1.5">
                                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</span>
                                                <span class="text-xs font-semibold text-slate-700">{{ $value }}</span>
                                            </div>
                                        @empty
                                            <span class="text-xs text-slate-400">Belum tersedia</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ $showUrl }}" class="nx-btn inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold">
                                        Detail
                                        <i class="ph-bold ph-arrow-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                                            <i class="ph ph-folder-simple-dashed text-xl text-slate-400"></i>
                                        </div>
                                        <p class="mt-2 text-sm font-semibold text-slate-700">Belum ada pemeriksaan</p>
                                        <p class="mt-0.5 text-xs text-slate-400">Data akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="space-y-3 xl:hidden">
                @forelse($recentPemeriksaans->take(4) as $item)
                    @php
                        $showUrl = data_get($item, 'id') ? $routeSafe('bidan.pemeriksaan.show', data_get($item, 'id')) : '#';
                        $parameters = collect(data_get($item, 'parameter', []))->take(3);
                    @endphp
                    <article class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-lg px-3 py-1 text-[11px] font-semibold {{ $kategoriTheme(data_get($item, 'kategori_raw')) }}">{{ data_get($item, 'kategori', '-') }}</span>
                            <span class="rounded-lg px-3 py-1 text-[11px] font-semibold {{ $statusTheme(data_get($item, 'status_raw')) }}">{{ data_get($item, 'status', '-') }}</span>
                        </div>
                        <h3 class="mt-2.5 truncate text-base font-semibold text-slate-900">{{ data_get($item, 'nama', '-') }}</h3>
                        <p class="mt-1 truncate text-xs text-slate-500">NIK: {{ data_get($item, 'nik', '-') }} &middot; {{ data_get($item, 'tanggal', '-') }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($parameters as $label => $value)
                                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                                    <p class="mt-0.5 text-xs font-semibold text-slate-700">{{ $value }}</p>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ $showUrl }}" class="nx-btn mt-3 inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-xs font-semibold">
                            Detail
                            <i class="ph-bold ph-arrow-right text-[10px]"></i>
                        </a>
                    </article>
                @empty
                    <div class="flex flex-col items-center rounded-xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                            <i class="ph ph-folder-simple-dashed text-xl text-slate-400"></i>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-slate-700">Belum ada pemeriksaan</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- BOTTOM GRID --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-4">
            {{-- Jadwal --}}
            <div class="nx-card flex min-h-[300px] flex-col rounded-2xl p-5">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-sky-600">Jadwal</p>
                        <h2 class="mt-0.5 text-base font-bold text-slate-900">Agenda</h2>
                    </div>
                    <a href="{{ $routeSafe('bidan.jadwal.index') }}" class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-sky-600 transition hover:bg-sky-50">Lihat</a>
                </div>
                <div class="flex-1 space-y-2.5">
                    @forelse($jadwalTerdekat->take(2) as $item)
                        <div class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 transition hover:border-sky-200 hover:bg-sky-50/40">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600 ring-1 ring-sky-200">
                                <i class="ph-fill ph-calendar-check text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="clamp-1 text-sm font-semibold text-slate-900">{{ data_get($item, 'judul', 'Jadwal Posyandu') }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ data_get($item, 'tanggal', '-') }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ data_get($item, 'waktu', '-') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="flex h-full flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 py-8 text-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                                <i class="ph ph-calendar-blank text-lg text-slate-400"></i>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-600">Belum ada jadwal</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Imunisasi --}}
            <div class="nx-card flex min-h-[300px] flex-col rounded-2xl p-5">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-cyan-600">Imunisasi</p>
                        <h2 class="mt-0.5 text-base font-bold text-slate-900">Terbaru</h2>
                    </div>
                    <a href="{{ $routeSafe('bidan.imunisasi.index') }}" class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-cyan-600 transition hover:bg-cyan-50">Lihat</a>
                </div>
                <div class="flex-1 space-y-2.5">
                    @forelse($recentImunisasi->take(2) as $item)
                        @php
                            $imunisasiUrl = data_get($item, 'id') ? $routeSafe('bidan.imunisasi.show', data_get($item, 'id')) : '#';
                        @endphp
                        <a href="{{ $imunisasiUrl }}" class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 transition hover:border-cyan-200 hover:bg-cyan-50/40">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600 ring-1 ring-cyan-200">
                                <i class="ph-fill ph-syringe text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-semibold text-slate-900">{{ data_get($item, 'nama', '-') }}</h3>
                                <p class="mt-1 clamp-1 text-xs text-slate-500">{{ data_get($item, 'jenis', '-') }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ data_get($item, 'tanggal', '-') }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="flex h-full flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 py-8 text-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                                <i class="ph ph-syringe text-lg text-slate-400"></i>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-600">Belum ada imunisasi</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Sasaran --}}
            <div class="nx-card flex min-h-[300px] flex-col rounded-2xl p-5">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600">Data Sasaran</p>
                        <h2 class="mt-0.5 text-base font-bold text-slate-900">Terdaftar</h2>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200">
                        <i class="ph-fill ph-users-three text-base"></i>
                    </div>
                </div>
                <div class="flex-1 space-y-3">
                    @foreach($sasaranItems as $item)
                        @php
                            $percent = round(((int) $item['value'] / $totalSasaran) * 100);
                        @endphp
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-slate-500">{{ $item['label'] }}</p>
                                    <p class="mt-1 text-xl font-bold tabular-nums text-slate-900">{{ $number($item['value']) }}</p>
                                </div>
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $item['theme'] }}">
                                    <i class="ph-fill {{ $item['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="nx-progress h-full rounded-full {{ $item['bar'] }}" style="width: {{ $percent }}%"></div>
                            </div>
                            <p class="mt-1 text-right text-[10px] font-semibold text-slate-400">{{ $percent }}%</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Notifikasi --}}
            <div class="nx-card flex min-h-[300px] flex-col rounded-2xl p-5">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-600">Notifikasi</p>
                        <h2 class="mt-0.5 text-base font-bold text-slate-900">Info</h2>
                    </div>
                    <a href="{{ $routeSafe('bidan.notifikasi.index') }}" class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-amber-600 transition hover:bg-amber-50">Lihat</a>
                </div>
                <div class="flex-1 space-y-2.5">
                    @forelse($notifications->take(2) as $item)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5">
                            <div class="flex gap-3">
                                <div class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ data_get($item, 'is_read') ? 'bg-slate-300' : 'bg-amber-500 shadow-sm shadow-amber-500/20' }}"></div>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-semibold text-slate-900">{{ data_get($item, 'title', 'Notifikasi') }}</h3>
                                    <p class="mt-1 clamp-2 text-xs leading-relaxed text-slate-500">{{ data_get($item, 'message', '-') }}</p>
                                    <p class="mt-1.5 text-[11px] font-medium text-slate-400">{{ data_get($item, 'time', '-') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex h-full flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 py-8 text-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                                <i class="ph ph-bell text-lg text-slate-400"></i>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-600">Belum ada notifikasi</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const chart = document.getElementById('rtTrendChart');
        const svg = document.getElementById('rtTrendSvg');
        const monthWrap = document.getElementById('rtTrendMonths');

        const selectedLabel = document.getElementById('rtTrendLabel');
        const selectedValue = document.getElementById('rtTrendValue');

        const tooltip = document.getElementById('rtTrendTooltip');
        const tooltipLabel = document.getElementById('rtTooltipLabel');
        const tooltipValue = document.getElementById('rtTooltipValue');

        if (!chart || !svg || !monthWrap) {
            return;
        }

        const config = {
            width: 720,
            height: 300,
            left: 54,
            right: 34,
            top: 36,
            bottom: 56,
        };

        const ns = 'http://www.w3.org/2000/svg';

        let trendData = normalizeData(parseJson(chart.dataset.points));
        let activeIndex = Math.max(0, trendData.length - 1);

        function parseJson(value) {
            try {
                return JSON.parse(value || '[]');
            } catch (error) {
                return [];
            }
        }

        function normalizeData(items) {
            const source = Array.isArray(items) ? items : [];

            return source.slice(0, 6).map((item, index) => ({
                label: String(item.label || item.short || `Bulan ${index + 1}`),
                short: String(item.short || item.label || `B${index + 1}`),
                count: Number(item.count || 0),
            }));
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(Number(value || 0));
        }

        function createSvg(name, attrs = {}) {
            const element = document.createElementNS(ns, name);

            Object.entries(attrs).forEach(([key, value]) => {
                element.setAttribute(key, value);
            });

            return element;
        }

        function getMaxValue() {
            return Math.max(1, ...trendData.map(item => Number(item.count || 0)));
        }

        function getPoint(item, index) {
            const maxValue = getMaxValue();
            const chartWidth = config.width - config.left - config.right;
            const chartHeight = config.height - config.top - config.bottom;

            const x = trendData.length === 1
                ? config.width / 2
                : config.left + ((chartWidth / (trendData.length - 1)) * index);

            const y = config.top + chartHeight - ((item.count / maxValue) * chartHeight);

            return {
                x,
                y,
                ...item,
            };
        }

        function smoothPath(points) {
            if (!points.length) {
                return '';
            }

            let d = `M ${points[0].x} ${points[0].y}`;

            for (let index = 1; index < points.length; index++) {
                const prev = points[index - 1];
                const curr = points[index];
                const midX = (prev.x + curr.x) / 2;

                d += ` C ${midX} ${prev.y}, ${midX} ${curr.y}, ${curr.x} ${curr.y}`;
            }

            return d;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderGrid() {
            const group = createSvg('g');

            const chartHeight = config.height - config.top - config.bottom;
            const chartWidth = config.width - config.left - config.right;

            for (let index = 0; index <= 4; index++) {
                const y = config.top + ((chartHeight / 4) * index);

                group.appendChild(createSvg('line', {
                    x1: config.left,
                    y1: y,
                    x2: config.left + chartWidth,
                    y2: y,
                    stroke: '#cbd5e1',
                    'stroke-width': '1',
                    'stroke-opacity': '.42',
                    'stroke-dasharray': '6 8',
                }));
            }

            return group;
        }

        function renderChart() {
            svg.innerHTML = '';
            monthWrap.innerHTML = '';

            if (!trendData.length) {
                trendData = normalizeData([
                    { label: 'Tidak Ada Data', short: '-', count: 0 },
                ]);
            }

            const points = trendData.map(getPoint);
            const lineD = smoothPath(points);
            const baseY = config.height - config.bottom;

            const defs = createSvg('defs');

            const lineGradient = createSvg('linearGradient', {
                id: 'rtLineGradient',
                x1: '0',
                y1: '0',
                x2: '1',
                y2: '0',
            });

            lineGradient.appendChild(createSvg('stop', {
                offset: '0%',
                'stop-color': '#047857',
            }));

            lineGradient.appendChild(createSvg('stop', {
                offset: '55%',
                'stop-color': '#10b981',
            }));

            lineGradient.appendChild(createSvg('stop', {
                offset: '100%',
                'stop-color': '#38bdf8',
            }));

            const areaGradient = createSvg('linearGradient', {
                id: 'rtAreaGradient',
                x1: '0',
                y1: '0',
                x2: '0',
                y2: '1',
            });

            areaGradient.appendChild(createSvg('stop', {
                offset: '0%',
                'stop-color': '#10b981',
                'stop-opacity': '.24',
            }));

            areaGradient.appendChild(createSvg('stop', {
                offset: '100%',
                'stop-color': '#10b981',
                'stop-opacity': '.02',
            }));

            defs.appendChild(lineGradient);
            defs.appendChild(areaGradient);
            svg.appendChild(defs);

            svg.appendChild(renderGrid());

            const area = createSvg('path', {
                d: `${lineD} L ${points[points.length - 1].x} ${baseY} L ${points[0].x} ${baseY} Z`,
                fill: 'url(#rtAreaGradient)',
            });

            svg.appendChild(area);

            const line = createSvg('path', {
                d: lineD,
                fill: 'none',
                stroke: 'url(#rtLineGradient)',
                'stroke-width': '5',
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
            });

            svg.appendChild(line);

            points.forEach((point, index) => {
                const group = createSvg('g');

                const valueText = createSvg('text', {
                    x: point.x,
                    y: Math.max(20, point.y - 18),
                    'text-anchor': 'middle',
                    fill: '#334155',
                    'font-size': '12',
                    'font-weight': '900',
                });

                valueText.textContent = formatNumber(point.count);

                const dot = createSvg('circle', {
                    cx: point.x,
                    cy: point.y,
                    r: index === activeIndex ? '8' : '6',
                    fill: index === activeIndex ? '#047857' : '#10b981',
                    stroke: '#ffffff',
                    'stroke-width': index === activeIndex ? '5' : '4',
                    class: index === activeIndex ? 'rt-chart-point is-active' : 'rt-chart-point',
                });

                const hit = createSvg('circle', {
                    cx: point.x,
                    cy: point.y,
                    r: '24',
                    class: 'rt-chart-hit',
                    tabindex: '0',
                    role: 'button',
                    'aria-label': `${point.label}, ${point.count} pemeriksaan`,
                    'data-index': index,
                });

                const label = createSvg('text', {
                    x: point.x,
                    y: config.height - 22,
                    'text-anchor': 'middle',
                    fill: index === activeIndex ? '#047857' : '#64748b',
                    'font-size': '11',
                    'font-weight': '900',
                });

                label.textContent = point.short;

                group.appendChild(valueText);
                group.appendChild(dot);
                group.appendChild(hit);
                group.appendChild(label);

                svg.appendChild(group);

                const monthButton = document.createElement('button');
                monthButton.type = 'button';
                monthButton.className = index === activeIndex
                    ? 'rt-month-btn is-active rounded-xl px-3 py-2 text-center text-xs font-black text-emerald-700'
                    : 'rt-month-btn rounded-xl px-3 py-2 text-center text-xs font-black text-slate-500';

                monthButton.innerHTML = `
                    <span class="block">${escapeHtml(point.short)}</span>
                    <span class="mt-0.5 block text-[10px] font-bold opacity-70">${formatNumber(point.count)} data</span>
                `;

                monthButton.addEventListener('click', () => {
                    selectPoint(index, false);
                });

                monthWrap.appendChild(monthButton);
            });

            bindChartEvents();
            updateSelectedCard();
        }

        function bindChartEvents() {
            svg.querySelectorAll('.rt-chart-hit').forEach(hit => {
                const index = Number(hit.dataset.index || 0);

                hit.addEventListener('click', () => {
                    selectPoint(index, false);
                });

                hit.addEventListener('keydown', event => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        selectPoint(index, false);
                    }
                });

                hit.addEventListener('mousemove', event => {
                    showTooltip(event, index);
                });

                hit.addEventListener('mouseleave', hideTooltip);

                hit.addEventListener('focus', event => {
                    showTooltip(event, index);
                });

                hit.addEventListener('blur', hideTooltip);
            });
        }

        function selectPoint(index, silent = false) {
            activeIndex = Math.max(0, Math.min(index, trendData.length - 1));
            renderChart();

            if (!silent) {
                showSelectedPulse();
            }
        }

        function updateSelectedCard() {
            const active = trendData[activeIndex] || trendData[trendData.length - 1];

            if (!active) {
                return;
            }

            selectedLabel.textContent = active.label;
            selectedValue.textContent = formatNumber(active.count);
        }

        function showSelectedPulse() {
            selectedValue.animate(
                [
                    { transform: 'scale(.96)', opacity: .65 },
                    { transform: 'scale(1)', opacity: 1 },
                ],
                {
                    duration: 180,
                    easing: 'cubic-bezier(.22, 1, .36, 1)',
                }
            );
        }

        function showTooltip(event, index) {
            const item = trendData[index];

            if (!item || !tooltip) {
                return;
            }

            tooltipLabel.textContent = item.label;
            tooltipValue.textContent = formatNumber(item.count);

            const rect = chart.getBoundingClientRect();
            const clientX = event.clientX || rect.left + rect.width / 2;
            const clientY = event.clientY || rect.top + rect.height / 2;

            tooltip.style.left = `${clientX - rect.left}px`;
            tooltip.style.top = `${clientY - rect.top}px`;
            tooltip.classList.add('is-show');
        }

        function hideTooltip() {
            tooltip?.classList.remove('is-show');
        }

        async function refreshTrendFromServer() {
            const sourceUrl = chart.dataset.sourceUrl;

            if (!sourceUrl) {
                return;
            }

            try {
                const response = await fetch(sourceUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const nextData = Array.isArray(payload) ? payload : payload.data;

                if (!Array.isArray(nextData) || !nextData.length) {
                    return;
                }

                trendData = normalizeData(nextData);
                activeIndex = Math.min(activeIndex, trendData.length - 1);
                renderChart();
            } catch (error) {
                // Dashboard tidak perlu ikut drama kalau fetch gagal.
            }
        }

        renderChart();

        if ('ResizeObserver' in window) {
            const resizeObserver = new ResizeObserver(() => {
                renderChart();
            });

            resizeObserver.observe(chart);
        } else {
            window.addEventListener('resize', renderChart);
        }

        setInterval(() => {
            if (!document.hidden) {
                refreshTrendFromServer();
            }
        }, 30000);
    })();
</script>
@endpush