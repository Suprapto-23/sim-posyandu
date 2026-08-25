@extends('layouts.bidan')

@section('title', 'Dashboard Bidan | Pusat Kendali')
@section('page-name', 'Dashboard')
@section('page-title', 'Dashboard Bidan')

@php
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    // --- Ekstraksi Data ---
    $stats = $stats ?? [];
    $recentPemeriksaans = collect($recentPemeriksaans ?? []);
    $sasaranSummary = collect($sasaranSummary ?? []);
    $trendData = collect($trendData ?? []);
    
    $fullName = auth()->user()->name ?? auth()->user()->nama ?? 'Bidan';
    $firstName = trim(explode(' ', $fullName)[0]) ?: 'Bidan';
    
    // Fix untuk sapaan agar tidak "Bidan Bidan"
    $sapaanBidan = strtolower($firstName) === 'bidan' ? 'Bidan' : 'Bidan ' . $firstName;

    $routeUrl = fn ($name, $params = []) => Route::has($name) ? route($name, $params) : '#';

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

    $pendingCount = (int) data_get($stats, 'menunggu_validasi', 0);
    $pemeriksaanHariIni = (int) data_get($stats, 'pemeriksaan_hari_ini', 0);
    $jadwalAktif = (int) data_get($stats, 'jadwal_aktif', 0);
    $totalSasaran = max(1, (int) data_get($stats, 'total_sasaran', 0));

    $cardStats = [
        [
            'label' => 'Total Sasaran', 'value' => number_format($totalSasaran),
            'desc' => 'Warga terdaftar NIK', 'icon' => 'fa-users-line',
            'url' => '#',
            'theme' => 'emerald'
        ],
        [
            'label' => 'Pemeriksaan Masuk', 'value' => number_format($pemeriksaanHariIni),
            'desc' => 'Tercatat hari ini', 'icon' => 'fa-user-injured',
            'url' => $routeUrl('bidan.pemeriksaan.index'),
            'theme' => 'teal'
        ],
        [
            'label' => 'Perlu Validasi', 'value' => number_format($pendingCount),
            'desc' => 'Menunggu keputusan', 'icon' => 'fa-clipboard-check',
            'url' => $routeUrl('bidan.pemeriksaan.index', ['tab' => 'pending']),
            'theme' => 'amber'
        ],
        [
            'label' => 'Jadwal Aktif', 'value' => number_format($jadwalAktif),
            'desc' => 'Agenda mendatang', 'icon' => 'fa-calendar-check',
            'url' => $routeUrl('bidan.jadwal.index'),
            'theme' => 'blue'
        ],
    ];

    $breakdowns = $sasaranSummary->map(function($item) {
        $cat = strtolower(data_get($item, 'label', ''));
        $color = match($cat) {
            'remaja' => 'amber',
            'lansia' => 'blue',
            default => 'emerald'
        };
        $icon = match($cat) {
            'remaja' => 'fa-user-graduate',
            'lansia' => 'fa-person-cane',
            default => 'fa-baby'
        };
        return [
            'title' => data_get($item, 'label', 'Data'),
            'count' => data_get($item, 'value', 0),
            'icon' => $icon,
            'color' => $color,
            'url' => '#'
        ];
    })->all();
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body { background-color: #f1f5f9; }
    
    .bg-mesh-fixed {
        position: fixed;
        inset: 0;
        z-index: -10;
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        pointer-events: none;
    }
    
    .widget-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1.75rem; 
        box-shadow: 0 4px 20px -10px rgba(0, 0, 0, 0.05);
        transform: translateZ(0); 
        will-change: transform, box-shadow;
        transition: transform 0.25s ease-out, box-shadow 0.25s ease-out;
    }
    @media (min-width: 640px) {
        .widget-card { border-radius: 2rem; }
    }
    
    .widget-card:hover {
        transform: translateY(-4px) translateZ(0);
        box-shadow: 0 20px 40px -10px rgba(20, 184, 166, 0.12);
        border-color: rgba(20, 184, 166, 0.2);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s ease;
    }

    .slim-scroll {
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-y: contain;
    }
    .slim-scroll::-webkit-scrollbar { width: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.2); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.5); }

    .apexcharts-tooltip {
        border-radius: 1.5rem !important;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border: none !important;
    }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

<div class="px-3 py-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-5 sm:space-y-8">

    {{-- 1. FLOATING HERO WIDGET (Responsif & Ringkas di Mobile) --}}
    <div class="relative overflow-hidden rounded-[2rem] sm:rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-5 sm:p-8 lg:p-10 shadow-xl shadow-teal-500/15 flex flex-col lg:flex-row justify-between items-center gap-6 sm:gap-8 border-[4px] sm:border-[6px] border-white/40" style="transform: translateZ(0);">
        
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        
        <div class="relative z-10 w-full lg:w-1/2 flex flex-col gap-3 sm:gap-5 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-3 sm:px-4 py-1 text-[10px] sm:text-xs font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-200 animate-pulse"></span>
                    Posyandu Operation Center
                </span>
            </div>
            
            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                <span id="dynamic-greeting">Halo</span>,<br/>
                {{ $sapaanBidan }} <span id="dynamic-emoji" class="inline-block animate-bounce">👋</span>
            </h1>

            <p class="text-teal-50 text-xs sm:text-sm font-medium leading-relaxed max-w-md mx-auto lg:mx-0">
                Pusat kendali tenaga kesehatan. Tinjau pemeriksaan masuk dari Kader, validasi rekam medis, dan kelola agenda kegiatan hari ini.
            </p>

            <div class="flex flex-wrap justify-center lg:justify-start gap-2.5 sm:gap-4 mt-1">
                @if(Route::has('bidan.pemeriksaan.index'))
                    <a href="{{ route('bidan.pemeriksaan.index', ['tab' => 'pending']) }}" class="btn-pill bg-white text-teal-600 hover:text-teal-800 hover:bg-teal-50 px-4 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-bold shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check"></i> Validasi Baru
                        @if($pendingCount > 0)
                            <span class="bg-rose-500 text-white text-[9px] sm:text-[10px] font-black px-2 py-0.5 rounded-full ml-1 animate-pulse">{{ $pendingCount }}</span>
                        @endif
                    </a>
                @endif
                @if(Route::has('bidan.jadwal.create'))
                    <a href="{{ route('bidan.jadwal.create') }}" class="btn-pill bg-black/10 hover:bg-black/20 text-white border border-white/30 px-4 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-bold backdrop-blur-md transition-all flex items-center gap-2">
                        <i class="fa-solid fa-calendar-plus"></i> Buat Agenda
                    </a>
                @endif
            </div>
        </div>

        {{-- WIDGET JAM & STATISTIK BULANAN --}}
        <div class="relative z-10 w-full lg:w-auto grid grid-cols-2 sm:flex sm:flex-row gap-3 sm:gap-4 justify-center">
            
            <div class="widget-card !rounded-[1.5rem] sm:!rounded-[2.5rem] p-4 sm:p-6 flex flex-col items-center justify-center min-w-0 sm:min-w-[200px]">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center text-base sm:text-xl mb-2 sm:mb-3 shadow-inner">
                    <i class="fa-solid fa-stopwatch"></i>
                </div>
                <p id="clock-date" class="text-[9px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1 text-center">Memuat tanggal...</p>
                <div class="text-xl sm:text-3xl font-black text-slate-800 tracking-tighter tabular-nums flex items-baseline gap-1">
                    <span id="clock-hm">00:00</span>
                    <span id="clock-s" class="text-xs sm:text-lg text-teal-500">:00</span>
                </div>
            </div>

            <div class="widget-card !rounded-[1.5rem] sm:!rounded-[2.5rem] p-4 sm:p-6 flex flex-col justify-center gap-3 sm:gap-4 min-w-0 sm:min-w-[200px]">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center text-base sm:text-xl shrink-0">
                        <i class="fa-solid fa-file-medical"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase truncate">Tervalidasi Bulan Ini</p>
                        <p class="text-lg sm:text-2xl font-black text-slate-800">{{ number_format(data_get($stats, 'tervalidasi', 0)) }}</p>
                    </div>
                </div>
                <div class="w-full border-t-[2px] sm:border-t-[3px] border-dotted border-slate-100 rounded-full"></div>
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center text-base sm:text-xl shrink-0">
                        <i class="fa-solid fa-syringe"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase truncate">Imunisasi Bulan Ini</p>
                        <p class="text-lg sm:text-2xl font-black text-slate-800">{{ number_format(data_get($stats, 'imunisasi_bulan_ini', 0)) }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- 2. METRIK UTAMA --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        @foreach($cardStats as $card)
            <a href="{{ $card['url'] }}" class="widget-card p-4 sm:p-6 group block flex flex-col justify-between">
                <div class="flex justify-between items-center mb-3 sm:mb-5">
                    <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-full bg-{{ $card['theme'] }}-50 text-{{ $card['theme'] }}-500 flex items-center justify-center text-lg sm:text-2xl shrink-0 group-hover:bg-{{ $card['theme'] }}-500 group-hover:text-white group-hover:shadow-lg group-hover:shadow-{{ $card['theme'] }}-500/30 transition-all duration-300">
                        <i class="fa-solid {{ $card['icon'] }}"></i>
                    </div>
                    <span class="text-xl sm:text-3xl font-black text-slate-800 truncate ml-2">{{ $card['value'] }}</span>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-700 truncate">{{ $card['label'] }}</h3>
                    <p class="text-[10px] sm:text-xs font-medium text-slate-400 mt-1 sm:mt-1.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-{{ $card['theme'] }}-400 shrink-0"></span> <span class="truncate">{{ $card['desc'] }}</span>
                    </p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- 3. AREA KONTEN (CHART & ANTREAN VALIDASI & DEMOGRAFI) --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6 items-stretch">
        
        {{-- Kiri: Chart & Demografi (Col-span-2) --}}
        <div class="xl:col-span-2 flex flex-col gap-4 sm:gap-6">
            
            {{-- Pill Demografi Bulat --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                @foreach($breakdowns as $b)
                    <div class="widget-card !rounded-2xl sm:!rounded-full p-3 sm:pr-6 flex items-center gap-3 sm:gap-4 cursor-default">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-{{ $b['color'] }}-50 text-{{ $b['color'] }}-600 flex items-center justify-center text-base sm:text-xl shrink-0">
                            <i class="fa-solid {{ $b['icon'] }}"></i>
                        </div>
                        <div class="flex-1 flex justify-between items-center min-w-0">
                            <p class="text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest truncate">{{ $b['title'] }}</p>
                            <p class="text-base sm:text-xl font-black text-slate-800 ml-2">{{ number_format($b['count']) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Smooth Area Chart Tren Aktivitas --}}
            <div class="widget-card p-4 sm:p-6 lg:p-8 flex-1 flex flex-col min-h-[300px] sm:min-h-[350px]">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 sm:mb-6 gap-3 sm:gap-4 bg-slate-50/50 p-3 sm:p-2 sm:pl-6 rounded-2xl sm:rounded-full border border-slate-100">
                    <div>
                        <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-widest text-center sm:text-left">Tren Layanan Pemeriksaan</h2>
                    </div>
                    <div class="flex w-full sm:w-auto shrink-0 bg-white p-1 rounded-full shadow-sm border border-slate-100 relative justify-center">
                        <button type="button" class="btn-pill px-4 py-1.5 sm:py-2 text-[11px] sm:text-xs font-bold transition-all duration-300 bg-teal-500 text-white shadow-md">
                            6 Bulan Terakhir
                        </button>
                    </div>
                </div>
                <div id="bidanTrendChart" class="w-full flex-1" style="min-height: 220px;"></div>
            </div>
        </div>

        {{-- Kanan: Antrean Pemeriksaan Masuk --}}
        <div class="widget-card flex flex-col p-2 h-[420px] sm:h-[480px] xl:h-auto">
            <div class="p-3 sm:p-5 flex items-center justify-between pb-2 border-b border-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-sm sm:text-lg shadow-inner border border-amber-100">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-widest">Antrean Validasi</h2>
                </div>
                @if(Route::has('bidan.pemeriksaan.index'))
                    <a href="{{ route('bidan.pemeriksaan.index') }}" class="btn-pill w-9 h-9 sm:w-10 sm:h-10 bg-slate-50 hover:bg-teal-50 border border-slate-100 text-slate-400 hover:text-teal-600 flex items-center justify-center transition-all text-xs sm:text-base">
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto slim-scroll p-2 sm:p-3 space-y-2 sm:space-y-3">
                @forelse($recentPemeriksaans as $item)
                    @php 
                        $statusRaw = strtolower(data_get($item, 'status_raw', ''));
                        $isPending = in_array($statusRaw, ['menunggu', 'pending', 'belum_divalidasi']);
                    @endphp
                    <a href="{{ data_get($item, 'id') ? $routeUrl('bidan.pemeriksaan.validasi', data_get($item, 'id')) : '#' }}" 
                       class="group block rounded-[1.5rem] sm:rounded-[2rem] p-3 border transition-all {{ $isPending ? 'bg-gradient-to-r from-amber-50 to-orange-50 border-amber-200 shadow-sm' : 'bg-white border-slate-100 hover:border-teal-200 hover:shadow-md' }}">
                        <div class="flex gap-3 sm:gap-4 items-center">
                            <div class="flex flex-col items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-full shrink-0 shadow-inner {{ $isPending ? 'bg-amber-500 text-white shadow-amber-500/30' : 'bg-slate-50 text-slate-600 group-hover:bg-teal-500 group-hover:text-white' }} transition-colors">
                                <i class="fa-solid fa-file-medical text-lg sm:text-xl"></i>
                            </div>
                            <div class="min-w-0 flex-1 pr-2">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="truncate text-xs sm:text-sm font-bold text-slate-800">{{ data_get($item, 'nama', '-') }}</h3>
                                    @if($isPending)
                                        <span class="btn-pill bg-amber-500 px-2 py-0.5 text-[8px] sm:text-[9px] font-black text-white uppercase tracking-wider">TINJAU</span>
                                    @endif
                                </div>
                                <p class="btn-pill inline-flex bg-white border border-slate-100 px-2.5 py-0.5 sm:py-1 text-[9px] sm:text-[10px] font-bold text-slate-500 items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-tag text-teal-500"></i> {{ data_get($item, 'kategori', '-') }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="flex h-full flex-col items-center justify-center text-center opacity-70 p-6 sm:p-8 min-h-[200px]">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl sm:rounded-3xl bg-slate-50 flex items-center justify-center mb-3 sm:mb-4 border border-slate-100">
                            <i class="fa-solid fa-check-double text-2xl sm:text-3xl text-slate-300"></i>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-slate-500">Semua Tervalidasi</p>
                        <p class="text-[11px] sm:text-xs font-semibold text-slate-400 mt-1 sm:mt-2">Tidak ada pemeriksaan baru dari Kader yang perlu ditinjau hari ini.</p>
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
        else if (h >= 15 && h < 18) { greeting = 'Selamat Sore'; emoji = '🌤️'; }

        if (greetingEl) greetingEl.innerText = greeting;
        if (emojiEl) emojiEl.innerText = emoji;
    }

    updateRealtimeClock();
    setInterval(updateRealtimeClock, 1000);

    let rawData = @json($trendData);
    let chartInstance = null;

    function renderApexChart(data) {
        const categories = data.map(item => item.short);
        const dataPoints = data.map(item => item.count);

        const options = {
            series: [{ name: 'Pemeriksaan', data: dataPoints }],
            chart: {
                type: 'area', 
                height: '100%', 
                toolbar: { show: false }, 
                zoom: { enabled: false },
                fontFamily: 'inherit', 
                foreColor: '#94a3b8', 
                parentHeightOffset: 0,
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            colors: ['#0d9488'],
            xaxis: {
                categories: categories,
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
            tooltip: { theme: 'light', x: { show: true } }
        };

        if (chartInstance) chartInstance.destroy();
        chartInstance = new ApexCharts(document.querySelector("#bidanTrendChart"), options);
        chartInstance.render();
    }

    renderApexChart(rawData);
});
</script>
@endpush