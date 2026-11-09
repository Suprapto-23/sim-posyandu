@extends('layouts.kader')

@section('title', 'Dashboard Kader | Operation Center')
@section('page-name', 'Dashboard')
@section('page-title', 'Dashboard Kader')

@php
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $fullName  = auth()->user()->name ?? 'Kader';
    $firstName = trim(explode(' ', $fullName)[0]) ?: 'Kader';

    $routeUrl = fn ($name) => Route::has($name) ? route($name) : '#';

    $formatDate = function ($date, $format = 'd M Y') {
        if (!$date) return '-';
        try { return Carbon::parse($date)->translatedFormat($format); }
        catch (\Throwable $e) { return '-'; }
    };

    $formatTime = function ($time) {
        if (!$time) return '-';
        try { return Carbon::parse($time)->format('H:i'); }
        catch (\Throwable $e) { return '-'; }
    };

    // Kelas Tailwind ditulis utuh (tidak dirangkai) supaya aman dari purge saat build.
    $themes = [
        'emerald' => [
            'icon'   => 'bg-emerald-50 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white group-hover:shadow-emerald-500/30',
            'dot'    => 'bg-emerald-400',
            'pill'   => 'bg-emerald-100 text-emerald-600',
            'border' => 'hover:border-emerald-300',
        ],
        'teal' => [
            'icon'   => 'bg-teal-50 text-teal-500 group-hover:bg-teal-500 group-hover:text-white group-hover:shadow-teal-500/30',
            'dot'    => 'bg-teal-400',
            'pill'   => 'bg-teal-100 text-teal-600',
            'border' => 'hover:border-teal-300',
        ],
        'amber' => [
            'icon'   => 'bg-amber-50 text-amber-500 group-hover:bg-amber-500 group-hover:text-white group-hover:shadow-amber-500/30',
            'dot'    => 'bg-amber-400',
            'pill'   => 'bg-amber-100 text-amber-600',
            'border' => 'hover:border-amber-300',
        ],
        'blue' => [
            'icon'   => 'bg-blue-50 text-blue-500 group-hover:bg-blue-500 group-hover:text-white group-hover:shadow-blue-500/30',
            'dot'    => 'bg-blue-400',
            'pill'   => 'bg-blue-100 text-blue-600',
            'border' => 'hover:border-blue-300',
        ],
    ];

    $cardStats = [
        [
            'label' => 'Total Sasaran', 'value' => number_format($stats['total_sasaran'] ?? 0),
            'desc' => 'Balita, remaja, lansia', 'icon' => 'fa-users-line',
            'url' => $routeUrl('kader.data.balita.index'), 'theme' => 'emerald',
        ],
        [
            'label' => 'Hadir Hari Ini', 'value' => number_format($stats['hadir_hari_ini'] ?? 0),
            'desc' => ($stats['persentase_hari_ini'] ?? 0) . '% dari target', 'icon' => 'fa-user-check',
            'url' => $routeUrl('kader.absensi.index'), 'theme' => 'teal',
        ],
        [
            'label' => 'Menunggu Review', 'value' => number_format($stats['pengukuran_pending'] ?? 0),
            'desc' => 'Tinjauan Bidan', 'icon' => 'fa-clock-rotate-left',
            'url' => $routeUrl('kader.pemeriksaan.index'), 'theme' => 'amber',
        ],
        [
            'label' => 'Jadwal Hari Ini', 'value' => $jadwalHariIni ? 'Aktif' : 'Kosong',
            'desc' => $jadwalHariIni->judul ?? 'Belum ada agenda', 'icon' => 'fa-calendar-check',
            'url' => $routeUrl('kader.jadwal.index'), 'theme' => 'blue',
        ],
    ];

    $breakdowns = [
        ['title' => 'Balita', 'count' => $stats['total_balita'] ?? 0, 'icon' => 'fa-child-reaching', 'url' => $routeUrl('kader.data.balita.index'), 'theme' => 'emerald'],
        ['title' => 'Remaja', 'count' => $stats['total_remaja'] ?? 0, 'icon' => 'fa-user-graduate', 'url' => $routeUrl('kader.data.remaja.index'), 'theme' => 'amber'],
        ['title' => 'Lansia', 'count' => $stats['total_lansia'] ?? 0, 'icon' => 'fa-person-cane', 'url' => $routeUrl('kader.data.lansia.index'), 'theme' => 'blue'],
    ];

    // Progress ring kehadiran hari ini
    $persen     = max(0, min(100, (float) ($stats['persentase_hari_ini'] ?? 0)));
    $ringC      = round(2 * M_PI * 52, 2);
    $ringOffset = round($ringC * (1 - $persen / 100), 2);
@endphp

@push('styles')
{{-- Percepat pemuatan ApexCharts --}}
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preload" as="script" href="https://cdn.jsdelivr.net/npm/apexcharts@3">
<style>
    /* Latar belakang ambient gradient mesh */
    body {
        background-color: #f1f5f9;
        background-image:
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    /* ---------- Widget card (semua sudut membulat) ---------- */
    .widget-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2rem;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: transform .3s cubic-bezier(.4, 0, .2, 1), box-shadow .3s cubic-bezier(.4, 0, .2, 1), background .3s ease;
    }
    .widget-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -10px rgba(20, 184, 166, 0.15);
        background: rgba(255, 255, 255, 0.95);
    }
    /* Card besar (chart, agenda) tidak ikut "terangkat" saat kursor lewat */
    .widget-static:hover {
        transform: none;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        background: rgba(255, 255, 255, 0.75);
    }

    .btn-pill { border-radius: 9999px; transition: all .3s ease; }

    /* ---------- Segmented control dengan thumb yang meluncur ---------- */
    .seg {
        position: relative; isolation: isolate;
        display: grid; grid-template-columns: repeat(3, 1fr);
        width: 100%; padding: 4px;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 9999px;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, .05);
    }
    @media (min-width: 1024px) { .seg { width: auto; min-width: 16rem; } }
    .seg-thumb {
        position: absolute; z-index: 0; top: 4px; bottom: 4px; left: 4px;
        width: calc((100% - 8px) / 3);
        border-radius: 9999px; background: #14b8a6;
        box-shadow: 0 4px 12px -2px rgba(20, 184, 166, .45);
        transform: translateX(calc(var(--seg-i, 0) * 100%));
        transition: transform .38s cubic-bezier(.4, 0, .2, 1);
    }
    .seg-btn {
        position: relative; z-index: 1;
        padding: .55rem 1rem; border-radius: 9999px;
        font-size: .75rem; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap;
        color: #64748b; transition: color .25s ease;
    }
    .seg-btn:hover { color: #0f172a; }
    .seg-btn.is-active, .seg-btn.is-active:hover { color: #fff; }
    .seg-btn:focus-visible { outline: 2px solid #14b8a6; outline-offset: 2px; }

    /* ---------- Chip ringkasan di atas chart ---------- */
    .trend-chip {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .45rem .95rem; border-radius: 9999px;
        background: #f8fafc; border: 1px solid #e2e8f0;
        font-size: .75rem; font-weight: 600; color: #64748b;
    }
    .trend-chip strong { font-size: .95rem; font-weight: 900; color: #0f172a; font-variant-numeric: tabular-nums; }
    .trend-dot { width: .6rem; height: .6rem; border-radius: 9999px; flex-shrink: 0; }

    /* ---------- Area chart ---------- */
    .kd-chart-wrap { position: relative; height: 260px; margin-top: 1rem; }
    @media (min-width: 640px)  { .kd-chart-wrap { height: 320px; } }
    @media (min-width: 1024px) { .kd-chart-wrap { height: 340px; } }
    .kd-chart { width: 100%; height: 100%; }

    .trend-empty {
        position: absolute; inset: 0; z-index: 2;
        display: none; align-items: center; justify-content: center;
        pointer-events: none;
    }
    .trend-empty.is-visible { display: flex; }
    .trend-empty p {
        padding: .5rem 1rem; border-radius: 9999px;
        background: rgba(255, 255, 255, .92); border: 1px solid #e2e8f0;
        font-size: .75rem; font-weight: 600; color: #64748b;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .05);
    }

    /* ---------- Agenda ---------- */
    .kd-agenda-list { max-height: 300px; overflow-y: auto; }
    .kd-agenda-item {
        display: block; padding: .75rem;
        border-radius: 1.75rem !important;
        border: 1px solid #f1f5f9; background: rgba(255, 255, 255, .8);
        transition: border-color .25s, box-shadow .25s;
    }
    .kd-agenda-item:hover { border-color: #99f6e4; box-shadow: 0 8px 20px -8px rgba(20, 184, 166, .25); }
    .kd-agenda-item.is-today {
        background: linear-gradient(90deg, #fffbeb, #fff7ed);
        border-color: #fde68a; box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }
    .kd-agenda-item.is-today:hover { border-color: #fcd34d; }

    .kd-empty { display: flex; min-height: 10rem; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 1.5rem; }
    .kd-empty-icon {
        display: flex; align-items: center; justify-content: center;
        width: 3.5rem; height: 3.5rem; margin-bottom: .75rem;
        border-radius: 1.5rem; background: #f1f5f9; border: 1px solid #e2e8f0;
        box-shadow: inset 0 2px 4px rgba(15, 23, 42, .06);
    }

    /* ---------- Progress ring ---------- */
    .kd-ring-card { flex: 1; display: flex; flex-direction: column; }
    .kd-ring-body { flex: 1; display: flex; align-items: center; gap: 1.25rem; padding-top: 1rem; }
    .kd-ring { position: relative; width: 7rem; height: 7rem; flex-shrink: 0; }
    @media (min-width: 640px)  { .kd-ring { width: 8rem; height: 8rem; } }
    @media (min-width: 1280px) { .kd-ring { width: 9rem; height: 9rem; } }
    .kd-ring svg { display: block; width: 100%; height: 100%; transform: rotate(-90deg); }
    .kd-ring-label { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .ring-progress { animation: ring-fill 1.2s cubic-bezier(.4, 0, .2, 1) both; }
    @keyframes ring-fill { from { stroke-dashoffset: var(--ring-c); } }

    /* Scrollbar tipis */
    .slim-scroll::-webkit-scrollbar { width: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.2); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.5); }

    /* Tooltip ApexCharts */
    .apexcharts-tooltip {
        border-radius: 1rem !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .12) !important;
        overflow: hidden !important;
    }
    .apexcharts-tooltip-title { background: #f8fafc !important; border-bottom: 1px solid #e2e8f0 !important; font-weight: 700 !important; }

    @media (prefers-reduced-motion: reduce) {
        .widget-card, .seg-btn, .seg-thumb { transition: none; }
        .ring-progress, .animate-bounce { animation: none; }
    }
</style>
@endpush

@section('content')
<div class="px-3 py-4 sm:px-6 sm:py-8 lg:px-8 max-w-[1400px] mx-auto space-y-4 sm:space-y-6 overflow-x-hidden">

    {{-- 1. HERO --}}
    <div class="relative overflow-hidden rounded-[2.5rem] sm:rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-5 sm:p-10 shadow-2xl shadow-teal-500/20 flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-5 sm:gap-8 border-[4px] sm:border-[6px] border-white/40">

        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px] pointer-events-none"></div>

        <div class="relative z-10 w-full lg:w-1/2 flex flex-col gap-3 sm:gap-5 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] sm:text-xs font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-200 animate-pulse"></span>
                    Posyandu Center
                </span>
            </div>

            <h1 class="text-[1.65rem] leading-[1.15] sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight sm:leading-tight">
                <span id="dynamic-greeting">Halo</span>,<br/>
                {{ $firstName }} <span id="dynamic-emoji" class="inline-block animate-bounce">👋</span>
            </h1>

            <p class="text-teal-50 text-xs sm:text-sm font-medium leading-relaxed max-w-md mx-auto lg:mx-0">
                Pusat operasional Posyandu terpadu. Kelola data sasaran, pantau tren kehadiran, dan rekam hasil pengukuran secara real-time.
            </p>

            <div class="grid grid-cols-2 sm:flex sm:flex-wrap justify-center lg:justify-start gap-2.5 sm:gap-4 mt-1 sm:mt-2">
                @if(Route::has('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index') }}" class="btn-pill bg-white text-teal-600 hover:text-teal-800 hover:bg-teal-50 px-4 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-bold shadow-lg flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-check"></i> <span class="truncate">Catat Absensi</span>
                    </a>
                @endif
                @if(Route::has('kader.pemeriksaan.create'))
                    <a href="{{ route('kader.pemeriksaan.create') }}" class="btn-pill bg-black/10 hover:bg-black/20 text-white border border-white/30 px-4 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-bold backdrop-blur-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-stethoscope"></i> <span class="truncate">Cek Kesehatan</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Jam & ringkasan bulanan --}}
        <div class="relative z-10 w-full lg:w-auto grid grid-cols-2 sm:flex sm:flex-row gap-3 sm:gap-4 justify-center items-stretch">

            <div class="widget-card widget-static !rounded-[2rem] sm:!rounded-[2.5rem] !bg-white/80 p-4 sm:p-6 flex flex-col items-center justify-center sm:min-w-[200px]">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center text-sm sm:text-xl mb-2 sm:mb-3 shadow-inner">
                    <i class="fa-solid fa-stopwatch"></i>
                </div>
                <p id="clock-date" class="text-[9px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1 text-center">Memuat tanggal...</p>
                <div class="text-xl sm:text-3xl font-black text-slate-800 tracking-tighter tabular-nums flex items-baseline gap-1">
                    <span id="clock-hm">00:00</span>
                    <span id="clock-s" class="text-xs sm:text-lg text-teal-500">:00</span>
                </div>
            </div>

            <div class="widget-card widget-static !rounded-[2rem] sm:!rounded-[2.5rem] !bg-white/80 p-4 sm:p-6 flex flex-col justify-center gap-3 sm:gap-4 sm:min-w-[200px]">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center text-sm sm:text-xl shrink-0 shadow-inner">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 uppercase truncate">Hadir Bulan Ini</p>
                        <p class="text-base sm:text-2xl font-black text-slate-800">{{ number_format($laporanBulanan['jumlah_hadir'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="w-full border-t-[2px] sm:border-t-[3px] border-dotted border-slate-200 rounded-full"></div>
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center text-sm sm:text-xl shrink-0 shadow-inner">
                        <i class="fa-solid fa-weight-scale"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 uppercase truncate">Telah Diukur</p>
                        <p class="text-base sm:text-2xl font-black text-slate-800">{{ number_format($laporanBulanan['jumlah_pengukuran'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- 2. METRIK UTAMA --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        @foreach($cardStats as $card)
            @php $t = $themes[$card['theme']]; @endphp
            <a href="{{ $card['url'] }}" class="widget-card group flex flex-col justify-between min-w-0 p-4 sm:p-6">
                <div class="flex justify-between items-center mb-3 sm:mb-5">
                    <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-full {{ $t['icon'] }} flex items-center justify-center text-base sm:text-2xl shrink-0 group-hover:shadow-lg transition-all duration-300 shadow-inner">
                        <i class="fa-solid {{ $card['icon'] }}"></i>
                    </div>
                    <span class="text-lg sm:text-3xl font-black text-slate-800 truncate ml-2">{{ $card['value'] }}</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-700 truncate normal-case">{{ $card['label'] }}</h3>
                    <p class="text-[10px] sm:text-xs font-medium text-slate-400 mt-1 sm:mt-1.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full {{ $t['dot'] }} shrink-0"></span>
                        <span class="truncate">{{ $card['desc'] }}</span>
                    </p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- 3. CHART + AGENDA --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6 items-stretch">

        {{-- Kiri: demografi + chart --}}
        <div class="xl:col-span-2 flex flex-col gap-4 sm:gap-6 min-w-0">

            <div class="grid grid-cols-3 gap-2 sm:gap-4">
                @foreach($breakdowns as $b)
                    @php $t = $themes[$b['theme']]; @endphp
                    <a href="{{ $b['url'] }}" class="widget-card !rounded-[2rem] p-3 sm:p-4 sm:pr-6 flex flex-col sm:flex-row items-center gap-2 sm:gap-4 text-center sm:text-left {{ $t['border'] }} min-w-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full {{ $t['pill'] }} flex items-center justify-center text-sm sm:text-xl shrink-0 shadow-inner">
                            <i class="fa-solid {{ $b['icon'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-0 sm:gap-2 w-full">
                            <p class="text-[9px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest truncate">{{ $b['title'] }}</p>
                            <p class="text-base sm:text-xl font-black text-slate-800 tabular-nums">{{ number_format($b['count']) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Tren aktivitas --}}
            <div class="widget-card widget-static p-4 sm:p-6 lg:p-7 flex-1 flex flex-col">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center text-sm sm:text-lg shadow-inner shrink-0">
                            <i class="fa-solid fa-chart-column"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-sm sm:text-base font-extrabold text-slate-800 normal-case">Tren aktivitas</h2>
                            <p id="trend-subtitle" class="text-[11px] sm:text-xs font-medium text-slate-400 truncate">Kehadiran dan pengukuran 7 hari terakhir</p>
                        </div>
                    </div>

                    <div id="trend-seg" class="seg" style="--seg-i: 0" role="group" aria-label="Rentang waktu">
                        <span class="seg-thumb" aria-hidden="true"></span>
                        @foreach([7, 14, 30] as $days)
                            <button type="button" data-range="{{ $days }}" aria-pressed="{{ $days === 7 ? 'true' : 'false' }}" class="trend-range seg-btn {{ $days === 7 ? 'is-active' : '' }}">
                                {{ $days }} hari
                            </button>
                        @endforeach
                    </div>
                </div>

                <div id="trend-summary" class="mt-4 flex flex-wrap gap-2 sm:gap-3"></div>

                <div id="trendChartWrap" class="kd-chart-wrap">
                    <div id="kaderTrendChart" class="kd-chart"></div>
                    <div id="trend-empty" class="trend-empty"><p>Belum ada aktivitas pada rentang ini</p></div>
                </div>
            </div>
        </div>

        {{-- Kanan: agenda + kehadiran hari ini --}}
        <div class="flex flex-col gap-4 sm:gap-6 min-w-0">

            <div class="widget-card widget-static flex flex-col p-3">
                <div class="p-3 sm:p-4 flex items-center justify-between pb-3 border-b border-slate-100 gap-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center text-sm sm:text-lg shadow-inner shrink-0">
                            <i class="fa-regular fa-calendar-days"></i>
                        </div>
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-800 truncate normal-case">Agenda Posyandu</h2>
                    </div>
                    @if(Route::has('kader.jadwal.index'))
                        <a href="{{ route('kader.jadwal.index') }}" aria-label="Lihat semua jadwal" class="btn-pill w-9 h-9 sm:w-10 sm:h-10 bg-slate-50 hover:bg-teal-50 border border-slate-100 text-slate-400 hover:text-teal-600 flex items-center justify-center shrink-0 text-xs sm:text-base shadow-sm">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                </div>

                <div class="kd-agenda-list slim-scroll p-2 sm:p-3 space-y-3">
                    @forelse($jadwalMendatang as $jadwal)
                        @php $isToday = !empty($jadwal->tanggal) && Carbon::parse($jadwal->tanggal)->isToday(); @endphp
                        <a href="{{ Route::has('kader.jadwal.show') ? route('kader.jadwal.show', $jadwal->id) : '#' }}"
                           class="kd-agenda-item group {{ $isToday ? 'is-today' : '' }}">
                            <div class="flex gap-3 items-center">
                                <div class="flex flex-col items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-full shrink-0 shadow-inner transition-colors {{ $isToday ? 'bg-amber-500 text-white shadow-amber-500/30' : 'bg-slate-100 text-slate-600 group-hover:bg-teal-500 group-hover:text-white' }}">
                                    <span class="text-base sm:text-lg font-black leading-none">{{ $formatDate($jadwal->tanggal ?? null, 'd') }}</span>
                                    <span class="text-[9px] font-bold uppercase tracking-widest mt-0.5">{{ $formatDate($jadwal->tanggal ?? null, 'M') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-xs sm:text-sm font-bold leading-snug text-slate-800 normal-case">{{ $jadwal->judul ?? 'Agenda' }}</h3>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                        <span class="btn-pill inline-flex items-center gap-1.5 bg-slate-50 border border-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 shadow-inner">
                                            <i class="fa-regular fa-clock text-teal-500"></i>
                                            {{ $formatTime($jadwal->waktu_mulai ?? null) }} - {{ $formatTime($jadwal->waktu_selesai ?? null) }}
                                        </span>
                                        @if($isToday)
                                            <span class="btn-pill bg-amber-500 px-2.5 py-1 text-[9px] font-black text-white uppercase tracking-wider shadow-sm">Hari ini</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="kd-empty">
                            <div class="kd-empty-icon"><i class="fa-solid fa-mug-hot text-2xl text-slate-300"></i></div>
                            <p class="text-sm font-bold text-slate-500">Belum ada agenda terdekat</p>
                            <p class="text-xs font-medium text-slate-400 mt-1.5">Waktu yang pas untuk mengevaluasi data sasaran.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="widget-card widget-static kd-ring-card p-4 sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center text-sm sm:text-lg shadow-inner shrink-0">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <h2 class="text-sm sm:text-base font-extrabold text-slate-800 normal-case">Kehadiran hari ini</h2>
                </div>

                <div class="kd-ring-body">
                    <div class="kd-ring">
                        <svg viewBox="0 0 120 120" aria-hidden="true">
                            <defs>
                                <linearGradient id="ringGradient" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#14b8a6"/>
                                    <stop offset="100%" stop-color="#34d399"/>
                                </linearGradient>
                            </defs>
                            <circle cx="60" cy="60" r="52" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                            @if($persen > 0)
                                <circle class="ring-progress" cx="60" cy="60" r="52" fill="none" stroke="url(#ringGradient)" stroke-width="12" stroke-linecap="round"
                                        style="stroke-dasharray: {{ $ringC }}; stroke-dashoffset: {{ $ringOffset }}; --ring-c: {{ $ringC }};"/>
                            @endif
                        </svg>
                        <div class="kd-ring-label">
                            <span class="text-xl sm:text-2xl font-black text-slate-800 tabular-nums">{{ round($persen) }}%</span>
                            <span class="text-[10px] font-semibold text-slate-400">dari target</span>
                        </div>
                    </div>

                    <dl class="min-w-0 flex-1 space-y-3">
                        <div>
                            <dt class="text-[11px] font-semibold text-slate-400">Sudah hadir</dt>
                            <dd class="text-lg sm:text-xl font-black text-slate-800 tabular-nums">{{ number_format($stats['hadir_hari_ini'] ?? 0) }}</dd>
                        </div>
                        <div class="border-t border-dashed border-slate-200 pt-3">
                            <dt class="text-[11px] font-semibold text-slate-400">Menunggu review</dt>
                            <dd class="text-lg sm:text-xl font-black text-slate-800 tabular-nums">{{ number_format($stats['pengukuran_pending'] ?? 0) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ---------- 1. Jam realtime & sapaan ---------- */
    function updateRealtimeClock() {
        const now = new Date();
        const dateEl = document.getElementById('clock-date');
        const hmEl = document.getElementById('clock-hm');
        const sEl = document.getElementById('clock-s');
        const greetingEl = document.getElementById('dynamic-greeting');
        const emojiEl = document.getElementById('dynamic-emoji');

        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        if (dateEl) dateEl.innerText = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`;

        const pad = (n) => String(n).padStart(2, '0');
        if (hmEl) hmEl.innerText = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
        if (sEl) sEl.innerText = `:${pad(now.getSeconds())}`;

        const h = now.getHours();
        let greeting = 'Selamat Malam';
        let emoji = '🌙';
        if (h >= 4 && h < 11) { greeting = 'Selamat Pagi'; emoji = '🌅'; }
        else if (h >= 11 && h < 15) { greeting = 'Selamat Siang'; emoji = '☀️'; }
        else if (h >= 15 && h < 18) { greeting = 'Selamat Sore'; emoji = '🌤️'; }

        if (greetingEl) greetingEl.innerText = greeting;
        if (emojiEl) emojiEl.innerText = emoji;
    }

    updateRealtimeClock();
    setInterval(updateRealtimeClock, 1000);

    /* ---------- 2. Grafik tren aktivitas ---------- */
    const chartEl    = document.querySelector('#kaderTrendChart');
    const wrapEl     = document.querySelector('#trendChartWrap');
    const summaryEl  = document.querySelector('#trend-summary');
    const subtitleEl = document.querySelector('#trend-subtitle');
    const emptyEl    = document.querySelector('#trend-empty');
    const segEl      = document.querySelector('#trend-seg');
    const rangeBtns  = document.querySelectorAll('.trend-range');

    const endpoint = @json(Route::has('kader.dashboard.trend') ? route('kader.dashboard.trend') : null);
    const initialTrend = @json($trend ?? ['labels' => [], 'series' => []]);

    if (!chartEl || !window.ApexCharts) return;

    const COLORS = ['#0d9488', '#f59e0b'];
    const RANGES = [7, 14, 30];
    const STALE_MS = 60000; // data lebih tua dari ini disegarkan diam-diam di latar belakang
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const sum = (arr) => arr.reduce((a, b) => a + b, 0);

    // Data selalu berbentuk { labels: [], series: [{ name, data: [angka] }] }
    function normalize(data) {
        const series = Array.isArray(data?.series) ? data.series : [];
        return {
            labels: Array.isArray(data?.labels) ? data.labels : [],
            series: series.map((s, i) => ({
                name: s?.name || `Seri ${i + 1}`,
                data: Array.isArray(s?.data) ? s.data.map((v) => Number(v) || 0) : [],
            })),
        };
    }

    /* --- Cache: semua rentang diambil di awal, jadi ganti rentang tidak perlu menunggu --- */
    const store = new Map();    // hari -> { data, at }
    const inflight = new Map(); // hari -> Promise

    store.set(7, { data: normalize(initialTrend), at: Date.now() });

    function load(days, force = false) {
        const hit = store.get(days);
        if (hit && !force) return Promise.resolve(hit.data);
        if (!endpoint) return Promise.resolve(hit ? hit.data : null);
        if (inflight.has(days)) return inflight.get(days);

        const p = fetch(`${endpoint}?range=${days}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((res) => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then((json) => {
                const data = normalize(json);
                store.set(days, { data, at: Date.now() });
                return data;
            })
            .catch((e) => {
                console.warn(`Gagal memuat tren ${days} hari:`, e);
                return hit ? hit.data : null;
            })
            .finally(() => inflight.delete(days));

        inflight.set(days, p);
        return p;
    }

    /* --- Opsi chart --- */
    // Sumbu Y kelipatan 4 supaya 4 garis grid selalu bilangan bulat
    function yMax(series) {
        const peak = Math.max(0, ...series.flatMap((s) => s.data));
        return Math.max(4, Math.ceil(peak / 4) * 4);
    }

    // Lebar kolom (persen dari slot) mengikuti jumlah hari
    const colPct = (count) => (count <= 7 ? 0.56 : count <= 14 ? 0.64 : 0.78);

    // Radius ujung batang ikut lebar batang, jadi selalu membulat (tidak lancip)
    function barRadius(count, seriesCount) {
        const plotW = Math.max(240, (wrapEl.clientWidth || 600) - 56);
        const barW = (plotW / Math.max(count, 1)) * colPct(count) / Math.max(seriesCount, 1) - 2;
        return Math.max(3, Math.min(10, Math.floor(barW / 2)));
    }

    function buildOptions(trend) {
        const count = trend.labels.length;
        return {
            chart: {
                type: 'bar',
                height: '100%',
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'inherit',
                foreColor: '#94a3b8',
                parentHeightOffset: 0,
                animations: {
                    enabled: !reduceMotion,
                    easing: 'easeinout',
                    speed: 500,
                    animateGradually: { enabled: true, delay: 40 },
                    dynamicAnimation: { enabled: true, speed: 350 },
                },
            },
            series: trend.series,
            colors: COLORS,
            plotOptions: {
                bar: {
                    columnWidth: `${Math.round(colPct(count) * 100)}%`,
                    borderRadius: barRadius(count, trend.series.length),
                    borderRadiusApplication: 'end',
                },
            },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            dataLabels: { enabled: false },
            legend: { show: false }, // legenda diganti chip ringkasan di atas chart
            xaxis: {
                categories: trend.labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                crosshairs: { show: false }, // hilangkan blok abu-abu persegi saat hover
                tooltip: { enabled: false },
                labels: { rotate: 0, hideOverlappingLabels: true, style: { fontSize: '11px', fontWeight: 600 } },
            },
            yaxis: {
                min: 0,
                max: yMax(trend.series),
                tickAmount: 4,
                forceNiceScale: false,
                labels: { formatter: (v) => String(Math.round(v)), style: { fontSize: '11px', fontWeight: 600 } },
            },
            grid: {
                borderColor: '#e2e8f0',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { top: 0, right: 8, bottom: 0, left: 4 },
            },
            tooltip: { shared: true, intersect: false, theme: 'light', y: { formatter: (v) => String(Math.round(v)) } },
            responsive: [{
                breakpoint: 640,
                options: {
                    xaxis: { labels: { style: { fontSize: '10px' } } },
                    yaxis: { labels: { style: { fontSize: '10px' } } },
                },
            }],
        };
    }

    /* --- Chip ringkasan dengan angka yang berhitung halus --- */
    function tween(el, to, ms = 350) {
        const from = Number(el.dataset.v || 0);
        el.dataset.v = String(to);
        cancelAnimationFrame(Number(el.dataset.raf || 0));
        if (reduceMotion || from === to) { el.textContent = to.toLocaleString('id-ID'); return; }

        const t0 = performance.now();
        const step = (t) => {
            const p = Math.min(1, (t - t0) / ms);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(from + (to - from) * eased).toLocaleString('id-ID');
            if (p < 1) el.dataset.raf = String(requestAnimationFrame(step));
        };
        el.dataset.raf = String(requestAnimationFrame(step));
    }

    function renderSummary(trend, days) {
        if (summaryEl.children.length !== trend.series.length) {
            summaryEl.replaceChildren(...trend.series.map((_, i) => {
                const chip = document.createElement('div');
                chip.className = 'trend-chip';

                const dot = document.createElement('span');
                dot.className = 'trend-dot';
                dot.style.background = COLORS[i % COLORS.length];

                const name = document.createElement('span');
                name.className = 'trend-name';

                const total = document.createElement('strong');
                total.className = 'trend-total';

                chip.append(dot, name, total);
                return chip;
            }));
        }

        trend.series.forEach((s, i) => {
            const chip = summaryEl.children[i];
            chip.querySelector('.trend-name').textContent = s.name;
            tween(chip.querySelector('.trend-total'), sum(s.data));
        });

        emptyEl.classList.toggle('is-visible', !trend.series.some((s) => sum(s.data) > 0));
        subtitleEl.textContent = `Kehadiran dan pengukuran ${days} hari terakhir`;
    }

    /* --- Render awal (memakai data dari server, tanpa menunggu jaringan) --- */
    let trend = store.get(7).data;
    let shownRange = 7;
    let ticket = 0;

    const chart = new ApexCharts(chartEl, buildOptions(trend));
    chart.render();
    renderSummary(trend, shownRange);

    // Ambil rentang lain di latar belakang supaya klik berikutnya instan
    RANGES.filter((d) => d !== shownRange).forEach((d) => load(d));

    function applyTrend(data, days) {
        trend = data;
        shownRange = days;
        chart.updateOptions(buildOptions(trend), true, true);
        renderSummary(trend, days);
    }

    function setActive(days) {
        const index = Math.max(0, RANGES.indexOf(days));
        segEl.style.setProperty('--seg-i', String(index));
        rangeBtns.forEach((b) => {
            const active = parseInt(b.dataset.range, 10) === days;
            b.classList.toggle('is-active', active);
            b.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    async function showRange(days) {
        const myTicket = ++ticket;
        const hit = store.get(days);
        const data = hit ? hit.data : await load(days);

        if (myTicket !== ticket) return; // ada klik yang lebih baru

        if (!data) {
            setActive(shownRange);
            subtitleEl.textContent = 'Data tidak bisa dimuat. Coba lagi.';
            return;
        }

        applyTrend(data, days);

        // Data cache sudah lama: tampilkan dulu yang ada, segarkan diam-diam
        if (hit && Date.now() - hit.at > STALE_MS) {
            load(days, true).then((fresh) => {
                if (fresh && myTicket === ticket && JSON.stringify(fresh) !== JSON.stringify(trend)) {
                    applyTrend(fresh, days);
                }
            });
        }
    }

    rangeBtns.forEach((btn) => {
        btn.addEventListener('click', function () {
            const days = parseInt(this.dataset.range || '7', 10);
            setActive(days); // thumb langsung meluncur, tanpa menunggu data
            if (days === shownRange) { ticket++; return; } // batalkan permintaan yang masih menunggu
            showRange(days);
        });
    });
});
</script>
@endpush