@extends('layouts.kader')

@section('title', 'Dashboard Kader')

@php
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $fullName = auth()->user()->name ?? 'Kader';
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

    // Tone map yang ringan tanpa membebani GPU
    $tones = [
        'emerald' => [
            'bg' => 'from-emerald-50/50 to-white', 'border' => 'border-emerald-100', 
            'iconBg' => 'bg-emerald-500', 'iconText' => 'text-white', 'iconShadow' => 'shadow-emerald-500/30', 
            'text' => 'text-emerald-700'
        ],
        'sky' => [
            'bg' => 'from-sky-50/50 to-white', 'border' => 'border-sky-100', 
            'iconBg' => 'bg-sky-500', 'iconText' => 'text-white', 'iconShadow' => 'shadow-sky-500/30', 
            'text' => 'text-sky-700'
        ],
        'amber' => [
            'bg' => 'from-amber-50/50 to-white', 'border' => 'border-amber-100', 
            'iconBg' => 'bg-amber-500', 'iconText' => 'text-white', 'iconShadow' => 'shadow-amber-500/30', 
            'text' => 'text-amber-700'
        ],
        'violet' => [
            'bg' => 'from-violet-50/50 to-white', 'border' => 'border-violet-100', 
            'iconBg' => 'bg-violet-500', 'iconText' => 'text-white', 'iconShadow' => 'shadow-violet-500/30', 
            'text' => 'text-violet-700'
        ],
    ];

    $cardStats = [
        [
            'label' => 'Total Sasaran', 'value' => number_format($stats['total_sasaran'] ?? 0),
            'desc' => 'Balita, remaja, lansia', 'icon' => 'fa-users-line', 'tone' => 'emerald',
            'url' => $routeUrl('kader.data.balita.index'),
        ],
        [
            'label' => 'Hadir Hari Ini', 'value' => number_format($stats['hadir_hari_ini'] ?? 0),
            'desc' => ($stats['persentase_hari_ini'] ?? 0) . '% dari target', 'icon' => 'fa-user-check', 'tone' => 'sky',
            'url' => $routeUrl('kader.absensi.index'),
        ],
        [
            'label' => 'Menunggu Review', 'value' => number_format($stats['pengukuran_pending'] ?? 0),
            'desc' => 'Perlu tinjauan Bidan', 'icon' => 'fa-clock-rotate-left', 'tone' => 'amber',
            'url' => $routeUrl('kader.pemeriksaan.index'),
        ],
        [
            'label' => 'Jadwal Hari Ini', 'value' => $jadwalHariIni ? 'Aktif' : 'Kosong',
            'desc' => $jadwalHariIni->judul ?? 'Belum ada agenda', 'icon' => 'fa-calendar-check', 'tone' => 'violet',
            'url' => $routeUrl('kader.jadwal.index'),
        ],
    ];

    $totalTrendHadir = $trend['summary']['total_hadir'] ?? 0;
    $totalTrendPengukuran = $trend['summary']['total_pengukuran'] ?? 0;
@endphp

@push('styles')
<style>
    /* Grid background yang lebih ceria tapi tetap ringan */
    .nexus-bg {
        background-color: #f0fdf4; /* Very light emerald tint */
        background-image: 
            radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.08) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.08) 0px, transparent 50%),
            linear-gradient(rgba(15, 23, 42, .03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .03) 1px, transparent 1px);
        background-size: 100% 100%, 100% 100%, 28px 28px, 28px 28px;
    }
    
    .smooth-scroll::-webkit-scrollbar { width: 5px; }
    .smooth-scroll::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.3); border-radius: 10px; }
    .smooth-scroll::-webkit-scrollbar-track { background: transparent; }
    
    .apexcharts-tooltip {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1) !important;
    }
</style>
@endpush

@section('content')
<div class="nexus-bg min-h-screen font-sans text-slate-800">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

        {{-- HERO SECTION --}}
        <section class="relative overflow-hidden rounded-[1.8rem] border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-cyan-50 shadow-[0_8px_30px_rgba(16,185,129,0.06)]">
            <!-- Decorative lightweight blobs (menggunakan radial-gradient polos, tanpa blur filter) -->
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full" style="background: radial-gradient(circle, rgba(110,231,183,0.3) 0%, rgba(255,255,255,0) 70%);"></div>
            <div class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full" style="background: radial-gradient(circle, rgba(125,211,252,0.3) 0%, rgba(255,255,255,0) 70%);"></div>

            <div class="relative flex flex-col gap-6 p-6 sm:p-8 xl:flex-row xl:items-center xl:justify-between">
                <div class="max-w-2xl space-y-5">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1.5 text-xs font-bold text-emerald-700 shadow-sm ring-1 ring-inset ring-emerald-200">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        </span>
                        Kader Operation Center
                    </div>
                    
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                        Halo, {{ $firstName }}. 👋
                    </h1>
                    
                    <p class="text-base font-medium leading-relaxed text-slate-600">
                        Pantau sasaran, registrasi hadir, pengukuran fisik, dan jadwal posyandu dalam satu kendali dengan antarmuka yang bersih dan responsif.
                    </p>
                    
                    <div class="flex flex-wrap gap-3 pt-2">
                        @if(Route::has('kader.absensi.index'))
                            <a href="{{ route('kader.absensi.index') }}" class="group relative inline-flex items-center gap-2 overflow-hidden rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/30 transition-all hover:-translate-y-0.5 hover:bg-emerald-500 hover:shadow-xl transform-gpu">
                                <i class="fa-solid fa-user-check"></i> Input Absensi
                            </a>
                        @endif
                        @if(Route::has('kader.pemeriksaan.create'))
                            <a href="{{ route('kader.pemeriksaan.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-emerald-700 shadow-sm ring-1 ring-inset ring-emerald-200 transition-all hover:-translate-y-0.5 hover:bg-emerald-50 transform-gpu">
                                <i class="fa-solid fa-stethoscope"></i> Input Pengukuran
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Hero Mini Stats --}}
                <div class="relative shrink-0">
                    <div class="rounded-[1.5rem] bg-white/90 p-5 shadow-lg shadow-slate-200/50 ring-1 ring-slate-100">
                        <p class="text-xs font-bold uppercase tracking-widest text-emerald-600">Bulan Ini</p>
                        <div class="mt-4 flex gap-6">
                            <div class="rounded-xl bg-emerald-50 p-3 text-center">
                                <p class="text-2xl font-black text-emerald-700">{{ number_format($laporanBulanan['jumlah_hadir'] ?? 0) }}</p>
                                <p class="mt-1 text-[11px] font-bold text-emerald-600/70">Kehadiran</p>
                            </div>
                            <div class="rounded-xl bg-sky-50 p-3 text-center">
                                <p class="text-2xl font-black text-sky-700">{{ number_format($laporanBulanan['jumlah_pengukuran'] ?? 0) }}</p>
                                <p class="mt-1 text-[11px] font-bold text-sky-600/70">Pengukuran</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI CARDS --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($cardStats as $card)
                @php $tone = $tones[$card['tone']] ?? $tones['emerald']; @endphp
                
                <a href="{{ $card['url'] }}" class="group relative flex flex-col justify-between overflow-hidden rounded-[1.5rem] bg-gradient-to-br {{ $tone['bg'] }} p-6 shadow-sm border {{ $tone['border'] }} transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-200/50 transform-gpu">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider {{ $tone['text'] }} opacity-80">{{ $card['label'] }}</p>
                            <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $card['value'] }}</p>
                        </div>
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $tone['iconBg'] }} {{ $tone['iconText'] }} shadow-lg {{ $tone['iconShadow'] }} transition-transform group-hover:scale-110 transform-gpu">
                            <i class="fa-solid {{ $card['icon'] }} text-lg"></i>
                        </div>
                    </div>
                    <p class="mt-5 text-sm font-semibold text-slate-500">{{ $card['desc'] }}</p>
                </a>
            @endforeach
        </section>

        {{-- BREAKDOWN --}}
        <section class="grid gap-4 md:grid-cols-3">
            @php 
                $breakdowns = [
                    ['title' => 'Balita', 'count' => $stats['total_balita'] ?? 0, 'icon' => 'fa-child-reaching', 'url' => $routeUrl('kader.data.balita.index'), 'color' => 'bg-emerald-500 text-white shadow-emerald-500/30'],
                    ['title' => 'Remaja', 'count' => $stats['total_remaja'] ?? 0, 'icon' => 'fa-user-graduate', 'url' => $routeUrl('kader.data.remaja.index'), 'color' => 'bg-sky-500 text-white shadow-sky-500/30'],
                    ['title' => 'Lansia', 'count' => $stats['total_lansia'] ?? 0, 'icon' => 'fa-person-cane', 'url' => $routeUrl('kader.data.lansia.index'), 'color' => 'bg-violet-500 text-white shadow-violet-500/30'],
                ];
            @endphp
            
            @foreach($breakdowns as $b)
                <a href="{{ $b['url'] }}" class="group flex items-center justify-between rounded-[1.3rem] bg-white p-5 border border-slate-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] transition-all hover:-translate-y-0.5 hover:border-slate-200 transform-gpu">
                    <div>
                        <p class="text-sm font-bold text-slate-500">{{ $b['title'] }}</p>
                        <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($b['count']) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $b['color'] }} shadow-lg transition-transform group-hover:rotate-6 transform-gpu">
                        <i class="fa-solid {{ $b['icon'] }}"></i>
                    </div>
                </a>
            @endforeach
        </section>

        {{-- MAIN GRID: CHART & JADWAL --}}
        <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
            
            {{-- CHART --}}
            <div class="flex flex-col rounded-[1.6rem] bg-white p-6 border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Aktivitas Operasional</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Tren kehadiran dan pengukuran fisik</p>
                    </div>
                    <div class="flex shrink-0 gap-1 rounded-xl bg-slate-50 p-1.5 border border-slate-100">
                        @foreach([7, 14, 30] as $days)
                            <button type="button" data-range="{{ $days }}" class="trend-range rounded-lg px-4 py-2 text-xs font-bold transition-all {{ $days === 7 ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:text-slate-800' }}">
                                {{ $days }} Hari
                            </button>
                        @endforeach
                    </div>
                </div>

                <div id="kaderTrendChart" class="min-h-[320px] w-full"></div>

                <div class="mt-4 grid grid-cols-3 gap-4 border-t border-slate-100 pt-5">
                    <div class="rounded-xl bg-emerald-50/50 p-3">
                        <p class="text-xs font-bold text-emerald-600">Kehadiran</p>
                        <p id="trendTotalHadir" class="text-xl font-black text-slate-900 mt-1">{{ number_format($totalTrendHadir) }}</p>
                    </div>
                    <div class="rounded-xl bg-sky-50/50 p-3">
                        <p class="text-xs font-bold text-sky-600">Pengukuran</p>
                        <p id="trendTotalPengukuran" class="text-xl font-black text-slate-900 mt-1">{{ number_format($totalTrendPengukuran) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-500" id="trendClickTitle">Detail Data</p>
                        <p id="trendClickValue" class="text-sm font-bold text-slate-800 mt-1">Pilih titik grafik</p>
                    </div>
                </div>
            </div>

            {{-- JADWAL --}}
            <div class="flex flex-col rounded-[1.6rem] bg-white p-6 border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Agenda Posyandu</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Dikelola oleh Bidan</p>
                    </div>
                    @if(Route::has('kader.jadwal.index'))
                        <a href="{{ route('kader.jadwal.index') }}" class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition-colors">Lihat Semua</a>
                    @endif
                </div>

                <div class="smooth-scroll flex-1 space-y-3 overflow-y-auto pr-2 max-h-[390px]">
                    @forelse($jadwalMendatang as $jadwal)
                        <a href="{{ Route::has('kader.jadwal.show') ? route('kader.jadwal.show', $jadwal->id) : '#' }}" class="group block rounded-[1.2rem] border border-slate-100 bg-white p-4 transition-all hover:border-emerald-200 hover:shadow-md hover:shadow-emerald-100/50 transform-gpu">
                            <div class="flex gap-4">
                                <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-md shadow-emerald-400/30">
                                    <span class="text-xl font-black leading-none">{{ $formatDate($jadwal->tanggal ?? null, 'd') }}</span>
                                    <span class="mt-0.5 text-[10px] font-bold uppercase tracking-widest">{{ $formatDate($jadwal->tanggal ?? null, 'M') }}</span>
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <div class="flex items-center gap-2">
                                        <h3 class="truncate text-sm font-bold text-slate-900">{{ $jadwal->judul ?? 'Jadwal Posyandu' }}</h3>
                                        @if(!empty($jadwal->tanggal) && Carbon::parse($jadwal->tanggal)->isToday())
                                            <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700">Hari Ini</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ $formatTime($jadwal->waktu_mulai ?? null) }} - {{ $formatTime($jadwal->waktu_selesai ?? null) }} WIB
                                    </p>
                                    @if(!empty($jadwal->lokasi))
                                        <p class="mt-1.5 truncate text-xs font-medium text-slate-500"><i class="fa-solid fa-location-dot mr-1 text-emerald-500"></i> {{ $jadwal->lokasi }}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-[1.2rem] border-2 border-dashed border-slate-100 bg-slate-50 py-10 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm mb-3">
                                <i class="fa-solid fa-calendar-xmark text-slate-400 text-lg"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Belum ada agenda</p>
                            <p class="mt-1 text-xs text-slate-500">Menunggu jadwal dari Bidan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const endpoint = @json(Route::has('kader.dashboard.trend') ? route('kader.dashboard.trend') : null);
    const initialTrend = @json($trend ?? ['labels' => [], 'series' => []]);
    const chartElement = document.querySelector('#kaderTrendChart');

    if (!chartElement || !window.ApexCharts) return;

    let currentRange = 7;
    const numberFormat = new Intl.NumberFormat('id-ID');
    const titleEl = document.querySelector('#trendClickTitle');
    const valueEl = document.querySelector('#trendClickValue');
    const totalHadirEl = document.querySelector('#trendTotalHadir');
    const totalPengukuranEl = document.querySelector('#trendTotalPengukuran');

    function updateSummary(data) {
        if (!data || !data.summary) return;
        if (totalHadirEl) totalHadirEl.textContent = numberFormat.format(data.summary.total_hadir || 0);
        if (totalPengukuranEl) totalPengukuranEl.textContent = numberFormat.format(data.summary.total_pengukuran || 0);
    }

    const chart = new ApexCharts(chartElement, {
        chart: {
            type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false },
            fontFamily: 'inherit', foreColor: '#64748b',
            events: {
                dataPointSelection: (event, chartContext, config) => {
                    const label = chartContext.w.globals.labels[config.dataPointIndex];
                    const seriesName = chartContext.w.globals.seriesNames[config.seriesIndex];
                    const value = chartContext.w.globals.series[config.seriesIndex][config.dataPointIndex];
                    if (titleEl && valueEl) {
                        titleEl.textContent = label;
                        valueEl.textContent = `${seriesName}: ${numberFormat.format(value || 0)}`;
                    }
                }
            }
        },
        colors: ['#0ea5e9', '#10b981'], // Sky dan Emerald agar lebih ceria
        series: initialTrend.series || [],
        xaxis: {
            categories: initialTrend.labels || [],
            axisBorder: { show: false }, axisTicks: { show: false }, tooltip: { enabled: false },
            labels: { style: { fontWeight: 600 } }
        },
        yaxis: { min: 0, forceNiceScale: true, labels: { style: { fontWeight: 600 } } },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.05, stops: [0, 100] }
        },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { top: 0, right: 0, left: 10, bottom: 0 } },
        legend: { position: 'top', horizontalAlign: 'right', fontWeight: 700, markers: { radius: 12 } },
        tooltip: { theme: 'light' }
    });

    chart.render();
    updateSummary(initialTrend);

    async function refreshTrend(range = currentRange) {
        if (!endpoint) return;
        try {
            const res = await fetch(`${endpoint}?range=${range}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            chart.updateOptions({ xaxis: { categories: data.labels || [] } }, false, true);
            chart.updateSeries(data.series || [], true);
            updateSummary(data);
        } catch (e) { console.warn('Gagal memuat trend:', e); }
    }

    document.querySelectorAll('.trend-range').forEach(btn => {
        btn.addEventListener('click', function () {
            currentRange = parseInt(this.dataset.range || '7', 10);
            
            document.querySelectorAll('.trend-range').forEach(item => {
                item.className = 'trend-range rounded-lg px-4 py-2 text-xs font-bold transition-all text-slate-500 hover:text-slate-800';
            });
            
            this.className = 'trend-range rounded-lg px-4 py-2 text-xs font-bold transition-all bg-white text-emerald-700 shadow-sm ring-1 ring-slate-200';
            
            refreshTrend(currentRange);
        });
    });

    window.addEventListener('focus', () => refreshTrend(currentRange));
});
</script>
@endpush