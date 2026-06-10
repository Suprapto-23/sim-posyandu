@extends('layouts.kader')

@section('title', 'Dashboard Kader | Operation Center')
@section('page-name', 'Dashboard')
@section('page-title', 'Dashboard Kader')

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

    $cardStats = [
        [
            'label' => 'Total Sasaran', 'value' => number_format($stats['total_sasaran'] ?? 0),
            'desc' => 'Balita, remaja, lansia', 'icon' => 'fa-users-line',
            'url' => $routeUrl('kader.data.balita.index'),
            'theme' => 'emerald'
        ],
        [
            'label' => 'Hadir Hari Ini', 'value' => number_format($stats['hadir_hari_ini'] ?? 0),
            'desc' => ($stats['persentase_hari_ini'] ?? 0) . '% dari target', 'icon' => 'fa-user-check',
            'url' => $routeUrl('kader.absensi.index'),
            'theme' => 'teal'
        ],
        [
            'label' => 'Menunggu Review', 'value' => number_format($stats['pengukuran_pending'] ?? 0),
            'desc' => 'Perlu tinjauan Bidan', 'icon' => 'fa-clock-rotate-left',
            'url' => $routeUrl('kader.pemeriksaan.index'),
            'theme' => 'gold'
        ],
        [
            'label' => 'Jadwal Hari Ini', 'value' => $jadwalHariIni ? 'Aktif' : 'Kosong',
            'desc' => $jadwalHariIni->judul ?? 'Belum ada agenda', 'icon' => 'fa-calendar-check',
            'url' => $routeUrl('kader.jadwal.index'),
            'theme' => 'blue'
        ],
    ];

    $totalTrendHadir = $trend['summary']['total_hadir'] ?? 0;
    $totalTrendPengukuran = $trend['summary']['total_pengukuran'] ?? 0;
@endphp

@push('styles')
<style>
    /* Latar belakang global abu-abu sangat muda/bersih */
    body { background-color: #f8fafc; }

    /* Kartu putih elegan untuk konten bawah */
    .premium-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 20px -10px rgba(0,0,0,0.05);
        border-radius: 1.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .premium-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px -10px rgba(20, 184, 166, 0.15);
        border-color: rgba(20, 184, 166, 0.2);
    }

    /* Animasi masuk yang mulus */
    .fade-up {
        animation: smoothFadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }
    @keyframes smoothFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .delay-1 { animation-delay: 100ms; }
    .delay-2 { animation-delay: 200ms; }
    .delay-3 { animation-delay: 300ms; }

    /* Custom Scrollbar */
    .slim-scroll::-webkit-scrollbar { width: 4px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.2); border-radius: 10px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.5); }

    /* Palet Warna Ikon Bawah */
    .icon-emerald { background: #ecfdf5; color: #10b981; }
    .icon-teal { background: #f0fdfa; color: #14b8a6; }
    .icon-gold { background: #fffbeb; color: #f59e0b; }
    .icon-blue { background: #eff6ff; color: #3b82f6; }
</style>
@endpush

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6">

    {{-- 1. HERO SECTION : Premium Tosca (Teal) Gradient --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-teal-600 via-teal-500 to-emerald-500 p-8 lg:p-10 shadow-xl shadow-teal-500/20 fade-up">
        
        <div class="absolute -right-10 -top-24 w-96 h-96 bg-white/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/4 -bottom-20 w-72 h-72 bg-emerald-400/30 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 top-1/4 w-64 h-64 bg-teal-400/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-10">
            
            <div class="max-w-2xl">
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 text-[10px] font-black uppercase tracking-widest text-white shadow-sm backdrop-blur-md">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                        Kader Operation Center
                    </span>
                    <span id="realtime-clock" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 text-[11px] font-bold text-white shadow-sm backdrop-blur-md">
                        <i class="far fa-clock text-emerald-200"></i> <span id="clock-text">Memuat waktu...</span>
                    </span>
                </div>

                <h1 class="text-3xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    <span id="dynamic-greeting">Selamat Pagi</span>,<br class="hidden sm:block"/>
                    Ibu {{ $firstName }} <span id="dynamic-emoji">✨</span>
                </h1>

                <p class="mt-4 text-sm sm:text-base font-medium text-teal-50/90 leading-relaxed max-w-xl">
                    Sistem manajemen terpusat Posyandu. Pantau sasaran warga, registrasi kehadiran bulanan, dan kelola pengukuran fisik secara real-time.
                </p>
                
                <div class="flex flex-wrap gap-4 mt-7">
                    @if(Route::has('kader.absensi.index'))
                        <a href="{{ route('kader.absensi.index') }}" class="group inline-flex items-center gap-2 bg-white text-teal-700 px-6 py-3 rounded-xl text-sm font-bold shadow-[0_8px_20px_rgba(0,0,0,0.1)] hover:bg-teal-50 hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-user-check text-teal-500"></i> Input Absensi
                        </a>
                    @endif
                    @if(Route::has('kader.pemeriksaan.create'))
                        <a href="{{ route('kader.pemeriksaan.create') }}" class="group inline-flex items-center gap-2 bg-white/10 text-white border border-white/30 px-6 py-3 rounded-xl text-sm font-bold backdrop-blur-md hover:bg-white/20 hover:border-white/50 hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-stethoscope"></i> Input Pengukuran
                        </a>
                    @endif
                </div>
            </div>

            <div class="shrink-0 w-full lg:w-auto">
                <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-[1.8rem] p-7 shadow-2xl">
                    <p class="text-[10px] font-black uppercase tracking-widest text-teal-100 text-center mb-6">Capaian Bulan Ini</p>
                    <div class="flex items-center gap-8">
                        <div class="text-center px-2">
                            <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-white/20 text-white mb-3 shadow-inner">
                                <i class="fa-solid fa-clipboard-check text-lg"></i>
                            </div>
                            <p class="text-4xl font-black text-white">{{ number_format($laporanBulanan['jumlah_hadir'] ?? 0) }}</p>
                            <p class="mt-1.5 text-[10px] font-bold text-teal-100 uppercase tracking-wider">Kehadiran</p>
                        </div>
                        <div class="h-20 w-[1px] bg-white/20"></div>
                        <div class="text-center px-2">
                            <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-white/20 text-white mb-3 shadow-inner">
                                <i class="fa-solid fa-weight-scale text-lg"></i>
                            </div>
                            <p class="text-4xl font-black text-white">{{ number_format($laporanBulanan['jumlah_pengukuran'] ?? 0) }}</p>
                            <p class="mt-1.5 text-[10px] font-bold text-teal-100 uppercase tracking-wider">Pengukuran</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. KPI CARDS : Clean Premium White --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 fade-up delay-1">
        @foreach($cardStats as $card)
            <a href="{{ $card['url'] }}" class="premium-card group block p-6 relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-{{ $card['theme'] }}-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                
                <div class="relative z-10 flex justify-between items-start mb-4">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $card['label'] }}</p>
                        <h3 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1">{{ $card['value'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-full icon-{{ $card['theme'] }} flex items-center justify-center text-xl shadow-sm">
                        <i class="fa-solid {{ $card['icon'] }}"></i>
                    </div>
                </div>
                <div class="relative z-10 flex items-center gap-2 pt-4 border-t border-slate-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $card['theme'] }}-400"></span>
                    <p class="text-[11px] font-semibold text-slate-500">{{ $card['desc'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- 3. BREAKDOWN DEMOGRAFI : Horizontal Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 fade-up delay-2">
        @php 
            $breakdowns = [
                ['title' => 'Balita Aktif', 'count' => $stats['total_balita'] ?? 0, 'icon' => 'fa-child-reaching', 'url' => $routeUrl('kader.data.balita.index'), 'color' => 'emerald'],
                ['title' => 'Remaja Aktif', 'count' => $stats['total_remaja'] ?? 0, 'icon' => 'fa-user-graduate', 'url' => $routeUrl('kader.data.remaja.index'), 'color' => 'amber'],
                ['title' => 'Lansia Aktif', 'count' => $stats['total_lansia'] ?? 0, 'icon' => 'fa-person-cane', 'url' => $routeUrl('kader.data.lansia.index'), 'color' => 'teal'],
            ];
        @endphp
        
        @foreach($breakdowns as $b)
            <a href="{{ $b['url'] }}" class="premium-card p-5 flex items-center gap-5 group">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 text-{{ $b['color'] }}-500 flex items-center justify-center text-2xl group-hover:bg-{{ $b['color'] }}-50 transition-colors">
                    <i class="fa-solid {{ $b['icon'] }}"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $b['title'] }}</p>
                    <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight leading-none mt-1">{{ number_format($b['count']) }} <span class="text-[10px] font-medium text-slate-400 tracking-normal">Jiwa</span></h3>
                </div>
            </a>
        @endforeach
    </div>

    {{-- 4. MAIN GRID: CHART & JADWAL --}}
    <div class="grid grid-cols-1 xl:grid-cols-[2fr_1fr] gap-6 fade-up delay-3">
        
        {{-- CHART PANEL --}}
        <div class="premium-card p-6 md:p-8 flex flex-col">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800 tracking-tight">Aktivitas Operasional</h2>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Tren kehadiran dan pengukuran fisik</p>
                </div>
                <div class="flex shrink-0 gap-1 rounded-xl bg-slate-50 p-1 border border-slate-100 shadow-inner">
                    @foreach([7, 14, 30] as $days)
                        <button type="button" data-range="{{ $days }}" class="trend-range rounded-lg px-4 py-1.5 text-[10px] font-bold transition-all {{ $days === 7 ? 'bg-teal-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-200/50' }}">
                            {{ $days }} Hari
                        </button>
                    @endforeach
                </div>
            </div>

            <div id="kaderTrendChart" class="min-h-[280px] w-full flex-1 border-t border-slate-100 pt-6"></div>
        </div>

        {{-- JADWAL PANEL --}}
        <div class="premium-card flex flex-col h-[400px] xl:h-auto overflow-hidden">
            <div class="p-6 pb-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800 tracking-tight">Agenda Posyandu</h2>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Jadwal operasional terdekat</p>
                </div>
                @if(Route::has('kader.jadwal.index'))
                    <a href="{{ route('kader.jadwal.index') }}" class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-slate-400 hover:bg-teal-50 hover:text-teal-600 transition-colors border border-slate-200 shadow-sm">
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto slim-scroll p-6 space-y-4">
                @forelse($jadwalMendatang as $jadwal)
                    <a href="{{ Route::has('kader.jadwal.show') ? route('kader.jadwal.show', $jadwal->id) : '#' }}" class="group block rounded-[1.2rem] bg-slate-50 p-4 border border-slate-100 shadow-sm hover:shadow-md hover:bg-white hover:border-teal-100 transition-all">
                        <div class="flex gap-4 items-center">
                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-[14px] bg-white text-teal-600 border border-slate-200 shadow-sm group-hover:bg-teal-500 group-hover:border-teal-500 group-hover:text-white transition-colors">
                                <span class="text-xl font-extrabold leading-none">{{ $formatDate($jadwal->tanggal ?? null, 'd') }}</span>
                                <span class="text-[9px] font-black uppercase tracking-widest mt-1">{{ $formatDate($jadwal->tanggal ?? null, 'M') }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <h3 class="truncate text-sm font-bold text-slate-800">{{ $jadwal->judul ?? 'Agenda' }}</h3>
                                    @if(!empty($jadwal->tanggal) && Carbon::parse($jadwal->tanggal)->isToday())
                                        <span class="shrink-0 rounded bg-amber-100 px-1.5 py-0.5 text-[8px] font-black uppercase tracking-wider text-amber-700">Hari Ini</span>
                                    @endif
                                </div>
                                <p class="text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
                                    <i class="far fa-clock text-slate-400"></i> {{ $formatTime($jadwal->waktu_mulai ?? null) }} - {{ $formatTime($jadwal->waktu_selesai ?? null) }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="flex h-full flex-col items-center justify-center text-center opacity-60">
                        <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <i class="fa-regular fa-calendar-xmark text-slate-400 text-2xl"></i>
                        </div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Belum ada agenda</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. JAM & SAPAAN REALTIME
    function updateClockAndGreeting() {
        const now = new Date();
        const clockTextEl = document.getElementById('clock-text');
        const greetingEl = document.getElementById('dynamic-greeting');
        const emojiEl = document.getElementById('dynamic-emoji');

        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const dayName = days[now.getDay()];
        const date = String(now.getDate()).padStart(2, '0');
        const month = months[now.getMonth()];
        const year = now.getFullYear();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        if (clockTextEl) clockTextEl.innerText = `${dayName}, ${date} ${month} ${year} • ${hours}:${minutes}:${seconds} WIB`;

        const h = now.getHours();
        let greeting = 'Selamat Malam';
        let emoji = '🌙';

        if (h >= 4 && h < 11) { greeting = 'Selamat Pagi'; emoji = '🌅'; } 
        else if (h >= 11 && h < 15) { greeting = 'Selamat Siang'; emoji = '☀️'; } 
        else if (h >= 15 && h < 18) { greeting = 'Selamat Sore'; emoji = '🌇'; }

        if (greetingEl) greetingEl.innerText = greeting;
        if (emojiEl) emojiEl.innerText = emoji;
    }

    updateClockAndGreeting();
    setInterval(updateClockAndGreeting, 1000);

    // 2. GRAFIK KADER
    const endpoint = @json(Route::has('kader.dashboard.trend') ? route('kader.dashboard.trend') : null);
    const initialTrend = @json($trend ?? ['labels' => [], 'series' => []]);
    const chartElement = document.querySelector('#kaderTrendChart');

    if (!chartElement || !window.ApexCharts) return;

    let currentRange = 7;
    const chart = new ApexCharts(chartElement, {
        chart: {
            type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false },
            fontFamily: 'inherit', foreColor: '#94a3b8'
        },
        colors: ['#14b8a6', '#f59e0b'], // Teal dan Amber
        series: initialTrend.series || [],
        xaxis: {
            categories: initialTrend.labels || [],
            axisBorder: { show: false }, axisTicks: { show: false },
            labels: { style: { fontSize: '10px', fontWeight: 600 } }
        },
        yaxis: { min: 0, forceNiceScale: true, labels: { style: { fontSize: '10px', fontWeight: 600 } } },
        stroke: { curve: 'smooth', width: 2.5 },
        dataLabels: { enabled: false },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.0, stops: [0, 100] }
        },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { top: 0, right: 0, left: 10, bottom: 0 } },
        legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px', fontWeight: 700, markers: { radius: 12 } },
        tooltip: { theme: 'light' }
    });

    chart.render();

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
        } catch (e) { console.warn('Gagal memuat trend:', e); }
    }

    document.querySelectorAll('.trend-range').forEach(btn => {
        btn.addEventListener('click', function () {
            currentRange = parseInt(this.dataset.range || '7', 10);
            document.querySelectorAll('.trend-range').forEach(item => {
                item.className = 'trend-range rounded-lg px-4 py-1.5 text-[10px] font-bold transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-200/50';
            });
            this.className = 'trend-range rounded-lg px-4 py-1.5 text-[10px] font-bold transition-all bg-teal-500 text-white shadow-sm';
            refreshTrend(currentRange);
        });
    });
});
</script>
@endpush