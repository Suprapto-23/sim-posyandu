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
            'desc' => 'Tinjauan Bidan', 'icon' => 'fa-clock-rotate-left',
            'url' => $routeUrl('kader.pemeriksaan.index'),
            'theme' => 'amber'
        ],
        [
            'label' => 'Jadwal Hari Ini', 'value' => $jadwalHariIni ? 'Aktif' : 'Kosong',
            'desc' => $jadwalHariIni->judul ?? 'Belum ada agenda', 'icon' => 'fa-calendar-check',
            'url' => $routeUrl('kader.jadwal.index'),
            'theme' => 'blue'
        ],
    ];

    $breakdowns = [
        ['title' => 'Balita', 'count' => $stats['total_balita'] ?? 0, 'icon' => 'fa-child-reaching', 'url' => $routeUrl('kader.data.balita.index'), 'color' => 'emerald'],
        ['title' => 'Remaja', 'count' => $stats['total_remaja'] ?? 0, 'icon' => 'fa-user-graduate', 'url' => $routeUrl('kader.data.remaja.index'), 'color' => 'amber'],
        ['title' => 'Lansia', 'count' => $stats['total_lansia'] ?? 0, 'icon' => 'fa-person-cane', 'url' => $routeUrl('kader.data.lansia.index'), 'color' => 'blue'],
    ];
@endphp

@push('styles')
<style>
    /* Latar belakang Ambient Gradient Mesh */
    body { 
        background-color: #f1f5f9; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    
    /* Widget Card Super Membulat (Squircle) */
    .widget-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2rem; 
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .widget-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -10px rgba(20, 184, 166, 0.15);
        background: rgba(255, 255, 255, 0.95);
    }

    /* Tombol Pil (Pill Buttons) */
    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s ease;
    }

    /* Scrollbar Super Membulat */
    .slim-scroll::-webkit-scrollbar { width: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.2); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.5); }

    /* Fix Tooltip ApexChart agar membulat juga */
    .apexcharts-tooltip {
        border-radius: 1.5rem !important;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border: none !important;
    }
</style>
@endpush

@section('content')
<div class="px-3 py-4 sm:px-6 sm:py-8 lg:px-8 max-w-[1400px] mx-auto space-y-4 sm:space-y-8 overflow-x-hidden">

    {{-- 1. FLOATING HERO WIDGET --}}
    <div class="relative overflow-hidden rounded-[1.75rem] sm:rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-5 sm:p-10 shadow-2xl shadow-teal-500/20 flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-5 sm:gap-8 border-[4px] sm:border-[6px] border-white/40">
        
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        
        <div class="relative z-10 w-full lg:w-1/2 flex flex-col gap-3 sm:gap-5 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-3.5 py-1.5 text-[10px] sm:text-xs font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
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
                    <a href="{{ route('kader.pemeriksaan.create') }}" class="btn-pill bg-black/10 hover:bg-black/20 text-white border border-white/30 px-4 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-bold backdrop-blur-md transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-stethoscope"></i> <span class="truncate">Cek Kesehatan</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- WIDGET JAM & STATISTIK BULANAN --}}
        <div class="relative z-10 w-full lg:w-auto grid grid-cols-2 sm:flex sm:flex-row gap-3 sm:gap-4 justify-center items-stretch">
            
            {{-- Jam Realtime Panel --}}
            <div class="widget-card !rounded-[1.5rem] sm:!rounded-[2.5rem] !bg-white/80 p-3.5 sm:p-6 flex flex-col items-center justify-center sm:min-w-[200px]">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center text-sm sm:text-xl mb-1.5 sm:mb-3 shadow-inner">
                    <i class="fa-solid fa-stopwatch"></i>
                </div>
                <p id="clock-date" class="text-[9px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1 text-center">Memuat tanggal...</p>
                <div class="text-xl sm:text-3xl font-black text-slate-800 tracking-tighter tabular-nums flex items-baseline gap-1">
                    <span id="clock-hm">00:00</span>
                    <span id="clock-s" class="text-xs sm:text-lg text-teal-500">:00</span>
                </div>
            </div>

            {{-- Ringkasan Bulanan --}}
            <div class="widget-card !rounded-[1.5rem] sm:!rounded-[2.5rem] !bg-white/80 p-3.5 sm:p-6 flex flex-col justify-center gap-2.5 sm:gap-4 sm:min-w-[200px]">
                <div class="flex items-center gap-2.5 sm:gap-4">
                    <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center text-sm sm:text-xl shrink-0">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 uppercase truncate">Hadir Bulan Ini</p>
                        <p class="text-base sm:text-2xl font-black text-slate-800">{{ number_format($laporanBulanan['jumlah_hadir'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="w-full border-t-[2px] sm:border-t-[3px] border-dotted border-slate-200 rounded-full"></div>
                <div class="flex items-center gap-2.5 sm:gap-4">
                    <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center text-sm sm:text-xl shrink-0">
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

    {{-- 2. METRIK UTAMA (4 BUBBLE WIDGETS) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        @foreach($cardStats as $card)
            <a href="{{ $card['url'] }}" class="widget-card p-3.5 sm:p-6 group block flex flex-col justify-between min-w-0">
                <div class="flex justify-between items-center mb-3 sm:mb-5">
                    <div class="w-9 h-9 sm:w-14 sm:h-14 rounded-full bg-{{ $card['theme'] }}-50 text-{{ $card['theme'] }}-500 flex items-center justify-center text-sm sm:text-2xl shrink-0 group-hover:bg-{{ $card['theme'] }}-500 group-hover:text-white group-hover:shadow-lg group-hover:shadow-{{ $card['theme'] }}-500/30 transition-all duration-300">
                        <i class="fa-solid {{ $card['icon'] }}"></i>
                    </div>
                    <span class="text-lg sm:text-3xl font-black text-slate-800 truncate ml-2">{{ $card['value'] }}</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-700 truncate">{{ $card['label'] }}</h3>
                    <p class="text-[10px] sm:text-xs font-medium text-slate-400 mt-1 sm:mt-1.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-{{ $card['theme'] }}-400 shrink-0"></span> <span class="truncate">{{ $card['desc'] }}</span>
                    </p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- 3. AREA KONTEN (CHART & JADWAL & DEMOGRAFI) --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6 items-stretch">
        
        {{-- Kiri: Chart & Demografi (Col-span-2) --}}
        <div class="xl:col-span-2 flex flex-col gap-4 sm:gap-6">
            
            {{-- Pill Demografi --}}
            <div class="grid grid-cols-3 gap-2 sm:gap-4">
                @foreach($breakdowns as $b)
                    <a href="{{ $b['url'] }}" class="widget-card !rounded-2xl sm:!rounded-full p-2.5 sm:p-3 sm:pr-6 flex flex-col sm:flex-row items-center gap-1.5 sm:gap-4 text-center sm:text-left hover:border-{{ $b['color'] }}-300 min-w-0">
                        <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-{{ $b['color'] }}-100 text-{{ $b['color'] }}-600 flex items-center justify-center text-sm sm:text-xl shrink-0">
                            <i class="fa-solid {{ $b['icon'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-0 sm:gap-2 w-full">
                            <p class="text-[9px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest truncate">{{ $b['title'] }}</p>
                            <p class="text-base sm:text-xl font-black text-slate-800">{{ number_format($b['count']) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Smooth Area Chart --}}
            <div class="widget-card p-3.5 sm:p-6 lg:p-8 flex-1 flex flex-col min-h-[300px] sm:min-h-[350px]">
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-4 bg-slate-50/50 p-2.5 sm:p-2 sm:pl-6 rounded-2xl sm:rounded-full border border-slate-100">
                    <div class="text-center sm:text-left">
                        <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-widest">Tren Aktivitas</h2>
                    </div>
                    <div class="flex w-full sm:w-auto shrink-0 bg-white p-1 rounded-full shadow-sm border border-slate-100 relative">
                        @foreach([7, 14, 30] as $days)
                            <button type="button" data-range="{{ $days }}" class="trend-range btn-pill flex-1 sm:flex-initial px-2 sm:px-4 py-1.5 sm:py-2 text-[11px] sm:text-xs font-bold transition-all duration-300 {{ $days === 7 ? 'bg-teal-500 text-white shadow-md' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                                {{ $days }} Hari
                            </button>
                        @endforeach
                    </div>
                </div>
                <div id="kaderTrendChart" class="w-full flex-1"></div>
            </div>
        </div>

        {{-- Kanan: Jadwal Terdekat (Pill List) --}}
        <div class="widget-card flex flex-col p-2">
            <div class="p-3.5 sm:p-5 flex items-center justify-between pb-2 border-b border-slate-50 gap-2">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center text-sm sm:text-lg shadow-inner shrink-0">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-widest truncate">Agenda Posyandu</h2>
                </div>
                @if(Route::has('kader.jadwal.index'))
                    <a href="{{ route('kader.jadwal.index') }}" class="btn-pill w-8 h-8 sm:w-10 sm:h-10 bg-slate-50 hover:bg-teal-50 border border-slate-100 text-slate-400 hover:text-teal-600 flex items-center justify-center transition-all shrink-0 text-xs sm:text-base">
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto slim-scroll p-2.5 sm:p-3 space-y-2.5 sm:space-y-3 max-h-[320px] xl:max-h-[100%]">
                @forelse($jadwalMendatang as $jadwal)
                    @php $isToday = !empty($jadwal->tanggal) && Carbon::parse($jadwal->tanggal)->isToday(); @endphp
                    <a href="{{ Route::has('kader.jadwal.show') ? route('kader.jadwal.show', $jadwal->id) : '#' }}" 
                       class="group block rounded-[2rem] p-3 border transition-all {{ $isToday ? 'bg-gradient-to-r from-amber-50 to-orange-50 border-amber-200 shadow-sm' : 'bg-white border-slate-100 hover:border-teal-200 hover:shadow-md' }}">
                        <div class="flex gap-4 items-center">
                            <div class="flex flex-col items-center justify-center w-14 h-14 rounded-full shrink-0 shadow-inner {{ $isToday ? 'bg-amber-500 text-white shadow-amber-500/30' : 'bg-slate-50 text-slate-600 group-hover:bg-teal-500 group-hover:text-white' }} transition-colors">
                                <span class="text-lg font-black leading-none">{{ $formatDate($jadwal->tanggal ?? null, 'd') }}</span>
                                <span class="text-[9px] font-bold uppercase tracking-widest mt-0.5">{{ $formatDate($jadwal->tanggal ?? null, 'M') }}</span>
                            </div>
                            <div class="min-w-0 flex-1 pr-2">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="truncate text-sm font-bold text-slate-800">{{ $jadwal->judul ?? 'Agenda' }}</h3>
                                    @if($isToday)
                                        <span class="btn-pill bg-amber-500 px-2 py-0.5 text-[9px] font-black text-white uppercase tracking-wider">HARI INI</span>
                                    @endif
                                </div>
                                <p class="btn-pill inline-flex bg-slate-50 border border-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-teal-500"></i> {{ $formatTime($jadwal->waktu_mulai ?? null) }} - {{ $formatTime($jadwal->waktu_selesai ?? null) }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="flex h-full flex-col items-center justify-center text-center opacity-70 p-6 sm:p-8 min-h-[200px] sm:min-h-[250px]">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-slate-50 flex items-center justify-center mb-3 sm:mb-4 border border-slate-100">
                            <i class="fa-solid fa-mug-hot text-2xl sm:text-3xl text-slate-300"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-500">Belum ada agenda terdekat</p>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Waktunya beristirahat atau melakukan evaluasi data sasaran.</p>
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
    
    // 1. JAM REALTIME DENGAN DETIK PRESISI
    function updateRealtimeClock() {
        const now = new Date();
        const dateEl = document.getElementById('clock-date');
        const hmEl = document.getElementById('clock-hm');
        const sEl = document.getElementById('clock-s');
        const greetingEl = document.getElementById('dynamic-greeting');
        const emojiEl = document.getElementById('dynamic-emoji');

        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        if (dateEl) {
            dateEl.innerText = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`;
        }

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        if (hmEl) hmEl.innerText = `${hours}:${minutes}`;
        if (sEl) sEl.innerText = `:${seconds}`;

        const h = now.getHours();
        let greeting = 'Selamat Malam';
        let emoji = '🌙';

        if (h >= 4 && h < 11) { greeting = 'Selamat Pagi'; emoji = '🌅'; } 
        else if (h >= 11 && h < 15) { greeting = 'Selamat Siang'; emoji = '☀️'; } 
        else if (h >= 15 && h < 18) { greeting = 'Selamat Sore'; emoji = '🌇'; }

        if (greetingEl) greetingEl.innerText = greeting;
        if (emojiEl) emojiEl.innerText = emoji;
    }

    updateRealtimeClock();
    setInterval(updateRealtimeClock, 1000);

    // 2. GRAFIK TREN DENGAN ANIMASI SANGAT HALUS (SMOOTH MORPHING)
    const endpoint = @json(Route::has('kader.dashboard.trend') ? route('kader.dashboard.trend') : null);
    const initialTrend = @json($trend ?? ['labels' => [], 'series' => []]);
    const chartElement = document.querySelector('#kaderTrendChart');

    if (!chartElement || !window.ApexCharts) return;

    let currentRange = 7;
    const chart = new ApexCharts(chartElement, {
        chart: {
            type: 'area', 
            height: '100%', 
            toolbar: { show: false }, 
            zoom: { enabled: false },
            fontFamily: 'inherit', 
            foreColor: '#94a3b8', 
            parentHeightOffset: 0,
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800, // Kecepatan transisi garis yang mulus
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 500 // Animasi saat update series
                }
            }
        },
        colors: ['#0d9488', '#f59e0b'], 
        series: initialTrend.series || [],
        xaxis: {
            categories: initialTrend.labels || [],
            axisBorder: { show: false }, 
            axisTicks: { show: false },
            labels: { style: { fontSize: '11px', fontWeight: 600 } }
        },
        yaxis: { 
            min: 0, 
            forceNiceScale: true, 
            labels: { style: { fontSize: '11px', fontWeight: 600 } } 
        },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.01, stops: [0, 100] }
        },
        grid: { borderColor: 'transparent', strokeDashArray: 0, padding: { top: 0, right: 0, left: 10, bottom: 0 } },
        legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px', fontWeight: 700, markers: { radius: 12 } },
        tooltip: { theme: 'light', x: { show: true } }
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
            
            // Update Series dan Kategori sumbu X menggunakan API built-in agar animasinya aktif
            chart.updateOptions({ xaxis: { categories: data.labels || [] } }, false, true);
            chart.updateSeries(data.series || [], true);
        } catch (e) { console.warn('Gagal memuat trend:', e); }
    }

    // Interaksi Tombol Filter Waktu
    document.querySelectorAll('.trend-range').forEach(btn => {
        btn.addEventListener('click', function () {
            currentRange = parseInt(this.dataset.range || '7', 10);
            
            // Ubah gaya tombol secara visual
            document.querySelectorAll('.trend-range').forEach(item => {
                item.className = 'trend-range btn-pill flex-1 sm:flex-initial px-2 sm:px-4 py-1.5 sm:py-2 text-[11px] sm:text-xs font-bold transition-all duration-300 text-slate-500 hover:text-slate-800 hover:bg-slate-50';
            });
            this.className = 'trend-range btn-pill flex-1 sm:flex-initial px-2 sm:px-4 py-1.5 sm:py-2 text-[11px] sm:text-xs font-bold transition-all duration-300 bg-teal-500 text-white shadow-md';
            
            // Panggil fungsi refresh chart (dengan animasi smooth morphing)
            refreshTrend(currentRange);
        });
    });
});
</script>
@endpush