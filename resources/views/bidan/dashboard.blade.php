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
            $date = now('Asia/Jakarta')->subMonths($monthOffset);

            return [
                'label' => $date->translatedFormat('M Y'),
                'short' => $date->translatedFormat('M'),
                'count' => 0,
            ];
        });
    }

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

    $maxMonthly = max(1, (int) $monthlyStats->max('count'));
    $totalSasaran = max(1, (int) $stat('total_sasaran'));

    $kategoriTheme = function ($kategori) {
        return match (strtolower((string) $kategori)) {
            'remaja' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
            'lansia' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            default => 'border-sky-200 bg-sky-50 text-sky-700',
        };
    };

    $statusTheme = function ($status) {
        $status = strtolower((string) $status);

        return match ($status) {
            'verified', 'tervalidasi', 'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'rejected', 'ditolak', 'revisi', 'perlu_revisi', 'perlu_perbaikan' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-amber-200 bg-amber-50 text-amber-700',
        };
    };

    $kpiCards = [
        [
            'label' => 'Menunggu Validasi',
            'value' => $stat('menunggu_validasi'),
            'desc' => 'Data perlu ditinjau',
            'icon' => 'ph-clock-countdown',
            'url' => $routeSafe('bidan.pemeriksaan.index', ['tab' => 'pending']),
            'theme' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
        [
            'label' => 'Tervalidasi',
            'value' => $stat('tervalidasi'),
            'desc' => 'Masuk Rekam Medis',
            'icon' => 'ph-check-circle',
            'url' => $routeSafe('bidan.pemeriksaan.index', ['tab' => 'verified']),
            'theme' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Jadwal Aktif',
            'value' => $stat('jadwal_aktif'),
            'desc' => 'Agenda pelayanan',
            'icon' => 'ph-calendar-check',
            'url' => $routeSafe('bidan.jadwal.index'),
            'theme' => 'bg-sky-50 text-sky-700 ring-sky-100',
        ],
        [
            'label' => 'Imunisasi Bulan Ini',
            'value' => $stat('imunisasi_bulan_ini'),
            'desc' => 'Catatan Balita',
            'icon' => 'ph-syringe',
            'url' => $routeSafe('bidan.imunisasi.index'),
            'theme' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
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
            'theme' => 'bg-sky-50 text-sky-700 ring-sky-100',
        ],
        [
            'label' => 'Remaja',
            'value' => $stat('remaja'),
            'icon' => 'ph-user-focus',
            'theme' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
        ],
        [
            'label' => 'Lansia',
            'value' => $stat('lansia'),
            'icon' => 'ph-heartbeat',
            'theme' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
    ];
@endphp

@push('styles')
<style>
    .bidan-dashboard {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background:
            radial-gradient(circle at 8% 4%, rgba(16, 185, 129, .10), transparent 25%),
            radial-gradient(circle at 88% 7%, rgba(14, 165, 233, .08), transparent 27%),
            linear-gradient(135deg, #f6fbf9 0%, #f8fafc 48%, #eefcf6 100%);
    }

    .dash-card {
        background: rgba(255, 255, 255, .82);
        border: 1px solid rgba(255, 255, 255, .86);
        box-shadow: 0 16px 34px rgba(15, 23, 42, .048);
        backdrop-filter: blur(18px);
    }

    .dash-soft {
        background: rgba(248, 250, 252, .82);
        border: 1px solid rgba(226, 232, 240, .78);
    }

    .dash-enter {
        animation: dashEnter .14s cubic-bezier(.22, 1, .36, 1) both;
    }

    .dash-bar {
        transform-origin: bottom;
        animation: dashBar .5s cubic-bezier(.22, 1, .36, 1) both;
    }

    .chart-grid-line {
        background-image:
            linear-gradient(to top, rgba(148, 163, 184, .14) 1px, transparent 1px);
        background-size: 100% 25%;
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

    @keyframes dashEnter {
        from {
            opacity: 0;
            transform: translate3d(0, 4px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes dashBar {
        from {
            transform: scaleY(.25);
            opacity: .45;
        }

        to {
            transform: scaleY(1);
            opacity: 1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .dash-enter,
        .dash-bar {
            animation: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="bidan-dashboard dash-enter -m-4 min-h-screen p-4 pb-8 text-slate-800 md:-m-6 md:p-6">
    <div class="mx-auto max-w-[1540px] space-y-5">

        {{-- HERO --}}
        <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <div class="relative h-full min-h-[312px] overflow-hidden rounded-[30px] bg-gradient-to-br from-emerald-800 via-teal-700 to-cyan-700 p-6 text-white shadow-[0_24px_58px_rgba(16,185,129,.16)] md:p-7">
                    <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute bottom-0 right-12 h-32 w-32 rounded-full bg-cyan-300/20 blur-2xl"></div>

                    <div class="relative z-10 flex h-full flex-col justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-[11px] font-black uppercase tracking-[0.14em] text-emerald-50">
                                <i class="ph ph-heartbeat text-sm"></i>
                                Dashboard Bidan
                            </div>

                            <h1 class="mt-4 max-w-3xl text-[29px] font-black leading-[1.16] tracking-[-0.04em] md:text-[36px]">
                                Ringkasan Layanan Kesehatan Posyandu
                            </h1>

                            <p class="mt-3 max-w-2xl text-sm font-medium leading-7 text-emerald-50/90">
                                Pantau validasi pemeriksaan, jadwal pelayanan, imunisasi Balita, dan Rekam Medis dalam satu ruang kerja yang rapi.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-emerald-50/75">
                                    Hari Ini
                                </p>

                                <p class="mt-2 text-[30px] font-black leading-none">
                                    {{ $number($stat('pemeriksaan_hari_ini')) }}
                                </p>

                                <p class="mt-2 text-xs font-bold text-emerald-50/80">
                                    Pemeriksaan
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-emerald-50/75">
                                    Bulan Ini
                                </p>

                                <p class="mt-2 text-[30px] font-black leading-none">
                                    {{ $number($stat('pemeriksaan_bulan_ini')) }}
                                </p>

                                <p class="mt-2 text-xs font-bold text-emerald-50/80">
                                    Total data
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-emerald-50/75">
                                    Prioritas
                                </p>

                                <p class="mt-2 text-[30px] font-black leading-none">
                                    {{ $number($stat('menunggu_validasi')) }}
                                </p>

                                <p class="mt-2 text-xs font-bold text-emerald-50/80">
                                    Validasi
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="xl:col-span-5">
                <div class="dash-card h-full min-h-[312px] rounded-[30px] p-5 md:p-6">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                                Ruang Kerja
                            </p>

                            <h2 class="mt-1 text-[20px] font-black tracking-[-0.03em] text-slate-900">
                                Aksi Cepat
                            </h2>

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Akses menu utama Bidan.
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                            <i class="ph ph-briefcase-medical text-lg"></i>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($quickActions as $action)
                            <a href="{{ $action['url'] }}"
                               class="group rounded-2xl border border-slate-100 bg-slate-50/75 p-4 transition hover:border-emerald-100 hover:bg-emerald-50/65 hover:shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-700 ring-1 ring-emerald-100 transition group-hover:scale-[1.03]">
                                        <i class="ph {{ $action['icon'] }} text-lg"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="truncate text-[15px] font-black text-slate-900">
                                            {{ $action['label'] }}
                                        </h3>

                                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                            {{ $action['desc'] }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.12em] text-emerald-700">
                                    Tanggal Sistem
                                </p>

                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ $todayLabel }}
                                </p>
                            </div>

                            <i class="ph ph-calendar-check text-xl text-emerald-700"></i>
                        </div>
                    </div>
                </div>
            </aside>
        </section>

        {{-- KPI --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($kpiCards as $card)
                <a href="{{ $card['url'] }}"
                   class="dash-card rounded-[24px] p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-[11px] font-black uppercase tracking-[0.12em] text-slate-400">
                                {{ $card['label'] }}
                            </p>

                            <h3 class="mt-2 text-[30px] font-black leading-none text-slate-900">
                                {{ $number($card['value']) }}
                            </h3>

                            <p class="mt-2 truncate text-xs font-semibold text-slate-500">
                                {{ $card['desc'] }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['theme'] }}">
                            <i class="ph {{ $card['icon'] }} text-lg"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </section>

        {{-- ANALYTICS --}}
        <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">
            <div class="xl:col-span-8">
                <div class="dash-card h-full rounded-[30px] p-5 md:p-6">
                    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                                Aktivitas Bulanan
                            </p>

                            <h2 class="mt-1 text-[20px] font-black tracking-[-0.03em] text-slate-900">
                                Tren Pemeriksaan Posyandu
                            </h2>

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Data pemeriksaan 6 bulan terakhir berdasarkan catatan yang masuk.
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                            <i class="ph ph-chart-bar text-lg"></i>
                        </div>
                    </div>

                    <div class="dash-soft rounded-[24px] p-4 md:p-5">
                        <div class="chart-grid-line relative h-[248px] rounded-[22px] bg-white/70 p-4">
                            <div class="grid h-full grid-cols-6 items-end gap-3">
                                @foreach($monthlyStats->take(6) as $item)
                                    @php
                                        $count = (int) data_get($item, 'count', 0);
                                        $height = $count > 0 ? max(14, round(($count / $maxMonthly) * 100)) : 4;
                                    @endphp

                                    <div class="flex h-full min-w-0 flex-col items-center justify-end">
                                        <p class="mb-2 text-[11px] font-black text-slate-500">
                                            {{ $count }}
                                        </p>

                                        <div class="flex h-[142px] w-full items-end justify-center">
                                            <div class="dash-bar w-full max-w-[38px] rounded-t-2xl bg-gradient-to-t from-emerald-600 via-teal-500 to-cyan-400 shadow-sm"
                                                 style="height: {{ $height }}%">
                                            </div>
                                        </div>

                                        <p class="mt-3 truncate text-center text-xs font-black text-slate-700">
                                            {{ data_get($item, 'short', '-') }}
                                        </p>

                                        <p class="mt-0.5 truncate text-center text-[10px] font-bold text-slate-400">
                                            {{ data_get($item, 'label', '-') }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-4">
                <div class="dash-card h-full rounded-[30px] p-5 md:p-6">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                                Status Validasi
                            </p>

                            <h2 class="mt-1 text-[20px] font-black tracking-[-0.03em] text-slate-900">
                                Ringkasan Pemeriksaan
                            </h2>

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Komposisi status saat ini.
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                            <i class="ph ph-chart-donut text-lg"></i>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-5">
                        <div class="relative h-40 w-40 rounded-full shadow-inner" style="{{ $donutStyle }}">
                            <div class="absolute inset-5 flex flex-col items-center justify-center rounded-full bg-white shadow-sm">
                                <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-400">
                                    Total
                                </p>

                                <p class="mt-1 text-[32px] font-black leading-none text-slate-900">
                                    {{ $number($statusActualTotal) }}
                                </p>

                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Pemeriksaan
                                </p>
                            </div>
                        </div>

                        <div class="grid w-full gap-2.5">
                            <div class="flex items-center justify-between rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                    <span class="text-sm font-black text-slate-700">Menunggu Validasi</span>
                                </div>

                                <span class="text-sm font-black text-slate-900">{{ $number($pendingCount) }}</span>
                            </div>

                            <div class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-sm font-black text-slate-700">Tervalidasi</span>
                                </div>

                                <span class="text-sm font-black text-slate-900">{{ $number($verifiedCount) }}</span>
                            </div>

                            <div class="flex items-center justify-between rounded-2xl border border-rose-100 bg-rose-50/70 px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                    <span class="text-sm font-black text-slate-700">Perlu Revisi</span>
                                </div>

                                <span class="text-sm font-black text-slate-900">{{ $number($revisionCount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- PEMERIKSAAN TERBARU FULL WIDTH --}}
        <section class="dash-card rounded-[30px] p-5 md:p-6">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Pemeriksaan
                    </p>

                    <h2 class="mt-1 text-[20px] font-black tracking-[-0.03em] text-slate-900">
                        Pemeriksaan Terbaru
                    </h2>

                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Data pemeriksaan terbaru yang masuk ke sistem.
                    </p>
                </div>

                <a href="{{ $routeSafe('bidan.pemeriksaan.index') }}"
                   class="inline-flex min-h-[38px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 transition hover:bg-slate-50">
                    Lihat Semua
                </a>
            </div>

            {{-- DESKTOP --}}
            <div class="hidden overflow-hidden rounded-[24px] border border-slate-100 bg-slate-50/70 xl:block">
                <table class="w-full table-fixed">
                    <thead>
                        <tr class="border-b border-slate-100 bg-white/75 text-left">
                            <th class="w-[30%] px-5 py-3 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Sasaran
                            </th>

                            <th class="w-[18%] px-5 py-3 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Kategori
                            </th>

                            <th class="w-[18%] px-5 py-3 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Status
                            </th>

                            <th class="w-[24%] px-5 py-3 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Parameter Utama
                            </th>

                            <th class="w-[10%] px-5 py-3 text-right text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentPemeriksaans->take(4) as $item)
                            @php
                                $showUrl = data_get($item, 'id')
                                    ? $routeSafe('bidan.pemeriksaan.show', data_get($item, 'id'))
                                    : '#';

                                $parameters = collect(data_get($item, 'parameter', []))->take(3);
                            @endphp

                            <tr class="bg-white/45 align-middle transition hover:bg-white/85">
                                <td class="px-5 py-4">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-black text-slate-900">
                                            {{ data_get($item, 'nama', '-') }}
                                        </h3>

                                        <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                            NIK: {{ data_get($item, 'nik', '-') }}
                                        </p>

                                        <p class="mt-1 text-xs font-semibold text-slate-400">
                                            {{ data_get($item, 'tanggal', '-') }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex max-w-full rounded-full border px-3 py-1 text-[11px] font-black {{ $kategoriTheme(data_get($item, 'kategori_raw')) }}">
                                        <span class="truncate">
                                            {{ data_get($item, 'kategori', '-') }}
                                        </span>
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex max-w-full rounded-full border px-3 py-1 text-[11px] font-black {{ $statusTheme(data_get($item, 'status_raw')) }}">
                                        <span class="truncate">
                                            {{ data_get($item, 'status', '-') }}
                                        </span>
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="grid gap-1.5">
                                        @forelse($parameters as $label => $value)
                                            <div class="grid grid-cols-[70px_minmax(0,1fr)] items-center gap-2 rounded-xl border border-slate-100 bg-white px-3 py-2">
                                                <span class="truncate text-[11px] font-black uppercase tracking-[0.08em] text-slate-400">
                                                    {{ $label }}
                                                </span>

                                                <span class="truncate text-right text-xs font-black text-slate-700">
                                                    {{ $value }}
                                                </span>
                                            </div>
                                        @empty
                                            <span class="text-xs font-semibold text-slate-400">
                                                Parameter belum tersedia
                                            </span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a href="{{ $showUrl }}"
                                       class="inline-flex min-h-[36px] items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center">
                                    <i class="ph ph-folder-simple-dashed text-2xl text-slate-400"></i>
                                    <p class="mt-2 text-sm font-black text-slate-700">Belum ada pemeriksaan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE --}}
            <div class="space-y-3 xl:hidden">
                @forelse($recentPemeriksaans->take(4) as $item)
                    @php
                        $showUrl = data_get($item, 'id')
                            ? $routeSafe('bidan.pemeriksaan.show', data_get($item, 'id'))
                            : '#';

                        $parameters = collect(data_get($item, 'parameter', []))->take(3);
                    @endphp

                    <article class="rounded-[22px] border border-slate-100 bg-slate-50/75 p-4">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border px-3 py-1 text-[11px] font-black {{ $kategoriTheme(data_get($item, 'kategori_raw')) }}">
                                {{ data_get($item, 'kategori', '-') }}
                            </span>

                            <span class="rounded-full border px-3 py-1 text-[11px] font-black {{ $statusTheme(data_get($item, 'status_raw')) }}">
                                {{ data_get($item, 'status', '-') }}
                            </span>
                        </div>

                        <h3 class="mt-2 truncate text-[16px] font-black text-slate-900">
                            {{ data_get($item, 'nama', '-') }}
                        </h3>

                        <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                            NIK: {{ data_get($item, 'nik', '-') }} · {{ data_get($item, 'tanggal', '-') }}
                        </p>

                        <div class="mt-3 grid gap-1.5 sm:grid-cols-3">
                            @foreach($parameters as $label => $value)
                                <div class="rounded-xl border border-slate-100 bg-white px-3 py-2">
                                    <p class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400">
                                        {{ $label }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-black text-slate-700">
                                        {{ $value }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ $showUrl }}"
                           class="mt-3 inline-flex min-h-[36px] items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                            Detail
                        </a>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                        <i class="ph ph-folder-simple-dashed text-2xl text-slate-400"></i>
                        <p class="mt-2 text-sm font-black text-slate-700">Belum ada pemeriksaan</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- BOTTOM GRID SIMETRIS --}}
        <section class="grid grid-cols-1 gap-5 xl:grid-cols-4">
            {{-- JADWAL --}}
            <div class="dash-card flex min-h-[330px] flex-col rounded-[30px] p-5 md:p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-sky-600">
                            Jadwal
                        </p>

                        <h2 class="mt-1 text-[19px] font-black tracking-[-0.03em] text-slate-900">
                            Agenda
                        </h2>
                    </div>

                    <a href="{{ $routeSafe('bidan.jadwal.index') }}"
                       class="text-xs font-black text-sky-700 hover:text-sky-800">
                        Lihat
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($jadwalTerdekat->take(2) as $item)
                        <article class="rounded-2xl border border-slate-100 bg-slate-50/75 p-4">
                            <div class="flex gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                                    <i class="ph ph-calendar-check text-lg"></i>
                                </div>

                                <div class="min-w-0">
                                    <h3 class="clamp-1 text-sm font-black text-slate-900">
                                        {{ data_get($item, 'judul', 'Jadwal Posyandu') }}
                                    </h3>

                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ data_get($item, 'tanggal', '-') }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                        {{ data_get($item, 'waktu', '-') }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="flex flex-1 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
                            <div>
                                <i class="ph ph-calendar-blank text-2xl text-slate-400"></i>
                                <p class="mt-2 text-sm font-black text-slate-700">Belum ada jadwal</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- IMUNISASI --}}
            <div class="dash-card flex min-h-[330px] flex-col rounded-[30px] p-5 md:p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-cyan-600">
                            Imunisasi
                        </p>

                        <h2 class="mt-1 text-[19px] font-black tracking-[-0.03em] text-slate-900">
                            Terbaru
                        </h2>
                    </div>

                    <a href="{{ $routeSafe('bidan.imunisasi.index') }}"
                       class="text-xs font-black text-cyan-700 hover:text-cyan-800">
                        Lihat
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recentImunisasi->take(2) as $item)
                        @php
                            $imunisasiUrl = data_get($item, 'id')
                                ? $routeSafe('bidan.imunisasi.show', data_get($item, 'id'))
                                : '#';
                        @endphp

                        <a href="{{ $imunisasiUrl }}"
                           class="block rounded-2xl border border-slate-100 bg-slate-50/75 p-4 transition hover:bg-cyan-50/60">
                            <div class="flex gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                                    <i class="ph ph-syringe text-lg"></i>
                                </div>

                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-black text-slate-900">
                                        {{ data_get($item, 'nama', '-') }}
                                    </h3>

                                    <p class="mt-1 clamp-1 text-xs font-semibold text-slate-500">
                                        {{ data_get($item, 'jenis', '-') }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-semibold text-slate-400">
                                        {{ data_get($item, 'tanggal', '-') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="flex flex-1 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
                            <div>
                                <i class="ph ph-syringe text-2xl text-slate-400"></i>
                                <p class="mt-2 text-sm font-black text-slate-700">Belum ada imunisasi</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- SASARAN --}}
            <div class="dash-card flex min-h-[330px] flex-col rounded-[30px] p-5 md:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                            Data Sasaran
                        </p>

                        <h2 class="mt-1 text-[19px] font-black tracking-[-0.03em] text-slate-900">
                            Terdaftar
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                        <i class="ph ph-users-three text-lg"></i>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($sasaranItems as $item)
                        @php
                            $percent = round(((int) $item['value'] / $totalSasaran) * 100);
                        @endphp

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/75 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black text-slate-500">
                                        {{ $item['label'] }}
                                    </p>

                                    <p class="mt-1 text-[22px] font-black leading-none text-slate-900">
                                        {{ $number($item['value']) }}
                                    </p>
                                </div>

                                <div class="flex h-9 w-9 items-center justify-center rounded-xl ring-1 {{ $item['theme'] }}">
                                    <i class="ph {{ $item['icon'] }} text-base"></i>
                                </div>
                            </div>

                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white ring-1 ring-slate-100">
                                <div class="h-full rounded-full bg-emerald-500"
                                     style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- NOTIFIKASI --}}
            <div class="dash-card flex min-h-[330px] flex-col rounded-[30px] p-5 md:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-amber-600">
                            Notifikasi
                        </p>

                        <h2 class="mt-1 text-[19px] font-black tracking-[-0.03em] text-slate-900">
                            Info
                        </h2>
                    </div>

                    <a href="{{ $routeSafe('bidan.notifikasi.index') }}"
                       class="text-xs font-black text-amber-700 hover:text-amber-800">
                        Lihat
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($notifications->take(2) as $item)
                        <article class="rounded-2xl border border-slate-100 bg-slate-50/75 p-4">
                            <div class="flex gap-3">
                                <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ data_get($item, 'is_read') ? 'bg-slate-300' : 'bg-amber-500' }}"></div>

                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-black text-slate-900">
                                        {{ data_get($item, 'title', 'Notifikasi') }}
                                    </h3>

                                    <p class="mt-1 clamp-2 text-xs font-semibold leading-5 text-slate-500">
                                        {{ data_get($item, 'message', '-') }}
                                    </p>

                                    <p class="mt-1 text-[11px] font-bold text-slate-400">
                                        {{ data_get($item, 'time', '-') }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="flex flex-1 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
                            <div>
                                <i class="ph ph-bell text-2xl text-slate-400"></i>
                                <p class="mt-2 text-sm font-black text-slate-700">
                                    Belum ada notifikasi
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
@endsection