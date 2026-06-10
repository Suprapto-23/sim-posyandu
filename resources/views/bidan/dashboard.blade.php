@extends('layouts.bidan')

@section('title', 'Dashboard Bidan')
@section('page-name', 'Dashboard')
@section('page-title', 'Dashboard Bidan')

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    // --- LOGIKA DATA ---
    $stats = $stats ?? [];
    $monthlyStats = collect($monthlyStats ?? []);
    $recentPemeriksaans = collect($recentPemeriksaans ?? []);
    $recentImunisasi = collect($recentImunisasi ?? []);
    $jadwalTerdekat = collect($jadwalTerdekat ?? []);
    $notifications = collect($notifications ?? []);

    if ($monthlyStats->isEmpty()) {
        $monthlyStats = collect(range(5, 0))->map(function ($monthOffset) {
            $date = now('Asia/Jakarta')->copy()->subMonthsNoOverflow($monthOffset);
            return ['label' => $date->translatedFormat('M Y'), 'short' => $date->translatedFormat('M'), 'count' => 0];
        });
    }

    $trendData = $monthlyStats->take(6)->values()->map(function ($item) {
        return [
            'label' => data_get($item, 'label', data_get($item, 'short', '-')),
            'short' => data_get($item, 'short', data_get($item, 'label', '-')),
            'count' => (int) data_get($item, 'count', 0),
        ];
    });

    $routeSafe = fn($name, $params = []) => Route::has($name) ? route($name, $params) : '#';
    $number = fn($value) => number_format((int) ($value ?? 0), 0, ',', '.');
    $stat = fn($key, $default = 0) => data_get($stats, $key, $default);

    $user = Auth::user();
    $userName = data_get($user, 'name') ?? data_get($user, 'nama') ?? 'Bidan';
    $todayLabel = now('Asia/Jakarta')->translatedFormat('l, d F Y');

    $pendingCount = (int) $stat('menunggu_validasi');
    $verifiedCount = (int) $stat('tervalidasi');
    $revisionCount = (int) $stat('perlu_revisi');
    $statusActualTotal = $pendingCount + $verifiedCount + $revisionCount;
    $statusTotal = max(1, $statusActualTotal);

    $totalSasaran = max(1, (int) $stat('total_sasaran'));

    // TEMA KATEGORI (Dibuat lebih soft tanpa border keras)
    $kategoriTheme = fn($kategori) => match (strtolower((string) $kategori)) {
        'remaja' => 'bg-indigo-50 text-indigo-700',
        'lansia' => 'bg-teal-50 text-teal-700',
        default => 'bg-sky-50 text-sky-700',
    };

    // TEMA STATUS (Diperbaiki total menggunakan ring-inset agar bentuknya sempurna)
    $statusTheme = fn($status) => match (strtolower((string) $status)) {
        'verified', 'tervalidasi', 'approved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
        'rejected', 'ditolak', 'revisi', 'perlu_revisi', 'perlu_perbaikan' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20',
        default => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
    };

    $sasaranItems = [
        ['label' => 'Balita', 'value' => $stat('balita'), 'icon' => 'ph-baby', 'color' => 'sky', 'gradient' => 'from-sky-400 to-blue-500', 'shadow' => 'shadow-sky-500/30'],
        ['label' => 'Remaja', 'value' => $stat('remaja'), 'icon' => 'ph-user-focus', 'color' => 'indigo', 'gradient' => 'from-indigo-400 to-violet-500', 'shadow' => 'shadow-indigo-500/30'],
        ['label' => 'Lansia', 'value' => $stat('lansia'), 'icon' => 'ph-heartbeat', 'color' => 'emerald', 'gradient' => 'from-emerald-400 to-teal-500', 'shadow' => 'shadow-emerald-500/30'],
    ];
@endphp

@push('styles')
<style>
    .premium-dashboard {
        font-family: 'Inter', system-ui, sans-serif;
        background-color: #f8fafc;
        position: relative;
        overflow-x: hidden;
    }

    .premium-dashboard::before {
        content: '';
        position: absolute;
        top: -10%;
        left: -5%;
        width: 50vw;
        height: 50vw;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }
    .premium-dashboard::after {
        content: '';
        position: absolute;
        top: 20%;
        right: -10%;
        width: 40vw;
        height: 40vw;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.04) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }

    .premium-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 1.25rem;
        box-shadow: 
            0 4px 6px -1px rgba(15, 23, 42, 0.02), 
            0 10px 15px -3px rgba(15, 23, 42, 0.04),
            inset 0 1px 0 rgba(255, 255, 255, 1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 10;
        overflow: hidden;
    }
    
    .premium-card:hover {
        box-shadow: 
            0 10px 25px -5px rgba(15, 23, 42, 0.08), 
            0 8px 10px -6px rgba(15, 23, 42, 0.04),
            inset 0 1px 0 rgba(255, 255, 255, 1);
        transform: translateY(-2px);
    }

    .progress-bar-fill {
        transition: width 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .progress-stripes {
        background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
        background-size: 1rem 1rem;
        animation: progress-animation 1s linear infinite;
    }

    @keyframes progress-animation {
        from { background-position: 1rem 0; }
        to { background-position: 0 0; }
    }

    .live-pulse {
        position: relative;
        display: flex;
        width: 8px;
        height: 8px;
    }
    .live-pulse span {
        position: absolute;
        display: inline-flex;
        height: 100%;
        width: 100%;
        border-radius: 9999px;
        background-color: #10b981;
        opacity: 0.75;
        animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    .live-pulse .dot {
        position: relative;
        display: inline-flex;
        border-radius: 9999px;
        height: 8px;
        width: 8px;
        background-color: #059669;
    }

    @keyframes ping {
        75%, 100% { transform: scale(2.5); opacity: 0; }
    }
    
    .premium-table tr {
        transition: background-color 0.2s ease;
    }
    .premium-table tr:hover td {
        background-color: rgba(248, 250, 252, 0.8);
    }

    .btn-glow {
        box-shadow: 0 0 15px -3px rgba(16, 185, 129, 0.4), 0 4px 6px -2px rgba(16, 185, 129, 0.2);
        transition: all 0.3s ease;
    }
    .btn-glow:hover {
        box-shadow: 0 0 25px -3px rgba(16, 185, 129, 0.6), 0 6px 10px -2px rgba(16, 185, 129, 0.3);
        transform: translateY(-1px);
    }

    .apexcharts-tooltip {
        border-radius: 12px !important;
        box-shadow: 0 15px 30px -5px rgba(15, 23, 42, 0.1) !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(4px);
    }
    .apexcharts-toolbar { z-index: 1 !important; }
</style>
@endpush

@section('content')
<div class="premium-dashboard min-h-screen p-4 md:p-6 lg:p-8 text-slate-800">
    <div class="mx-auto max-w-7xl relative z-10">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-5">
            <div>
                <div class="flex items-center gap-3 mb-2.5">
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-emerald-100 shadow-sm rounded-full text-[11px] font-bold uppercase tracking-wider text-slate-600">
                        <div class="live-pulse"><span class=""></span><div class="dot"></div></div>
                        <span class="text-emerald-700">Sistem Real-Time Aktif</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 flex items-center gap-1">
                        <i class="ph-bold ph-calendar-blank"></i> {{ $todayLabel }}
                    </span>
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                    Halo, <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">{{ explode(' ', $userName)[0] }}</span> 👋
                </h1>
                <p class="text-slate-500 mt-1.5 text-sm font-medium">Pantau validasi, rekam medis, dan matrik posyandu secara langsung hari ini.</p>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ $routeSafe('bidan.pemeriksaan.index', ['tab' => 'pending']) }}" class="btn-glow flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-6 py-3 rounded-xl font-bold transition-all border border-emerald-400/50">
                    <i class="ph-bold ph-stethoscope text-xl"></i>
                    <span>Validasi Baru</span>
                    @if($pendingCount > 0)
                        <span class="bg-white text-emerald-700 text-[11px] font-black px-2 py-0.5 rounded-full ml-1 animate-pulse shadow-sm">{{ $pendingCount }}</span>
                    @endif
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6">

            <div class="premium-card p-5 flex items-center justify-between group">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-emerald-500 transition-colors">Hari Ini</p>
                    <div class="flex items-end gap-2">
                        <h2 class="text-4xl font-black text-slate-800">{{ $number($stat('pemeriksaan_hari_ini')) }}</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Pemeriksaan Masuk</p>
                </div>
                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-100 flex items-center justify-center text-emerald-600 border border-emerald-100/50 shadow-sm group-hover:scale-110 transition-transform">
                    <i class="ph-fill ph-users text-2xl"></i>
                </div>
            </div>

            <div class="premium-card p-5 flex items-center justify-between group">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-amber-500 transition-colors">Perlu Validasi</p>
                    <div class="flex items-end gap-2">
                        <h2 class="text-4xl font-black text-amber-500">{{ $number($pendingCount) }}</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Menunggu Keputusan</p>
                </div>
                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-100 flex items-center justify-center text-amber-600 border border-amber-100/50 shadow-sm group-hover:scale-110 transition-transform">
                    <i class="ph-fill ph-clipboard-text text-2xl"></i>
                </div>
            </div>

            <div class="premium-card p-5 flex items-center justify-between group">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-sky-500 transition-colors">Jadwal Aktif</p>
                    <div class="flex items-end gap-2">
                        <h2 class="text-4xl font-black text-sky-600">{{ $number($stat('jadwal_aktif')) }}</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Agenda Mendatang</p>
                </div>
                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-sky-50 to-blue-100 flex items-center justify-center text-sky-600 border border-sky-100/50 shadow-sm group-hover:scale-110 transition-transform">
                    <i class="ph-fill ph-calendar-check text-2xl"></i>
                </div>
            </div>

            <div class="premium-card p-5 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 border-none shadow-xl flex flex-col justify-between overflow-hidden relative group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
                
                <h3 class="text-sm font-bold text-white/90 uppercase tracking-wider mb-3">Aksi Cepat</h3>
                <div class="space-y-2.5 relative z-10">
                    <a href="{{ $routeSafe('bidan.imunisasi.index') }}" class="flex items-center justify-between p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-all border border-white/5 group/btn">
                        <div class="flex items-center gap-3">
                            <div class="p-1.5 bg-emerald-500/20 rounded-lg text-emerald-400">
                                <i class="ph-fill ph-syringe text-lg"></i>
                            </div>
                            <span class="text-sm font-medium text-white">Imunisasi Balita</span>
                        </div>
                        <i class="ph-bold ph-arrow-right text-slate-400 group-hover/btn:text-white group-hover/btn:translate-x-1 transition-all"></i>
                    </a>
                    <a href="{{ $routeSafe('bidan.jadwal.index') }}" class="flex items-center justify-between p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-all border border-white/5 group/btn">
                        <div class="flex items-center gap-3">
                            <div class="p-1.5 bg-sky-500/20 rounded-lg text-sky-400">
                                <i class="ph-fill ph-calendar-plus text-lg"></i>
                            </div>
                            <span class="text-sm font-medium text-white">Kelola Jadwal</span>
                        </div>
                        <i class="ph-bold ph-arrow-right text-slate-400 group-hover/btn:text-white group-hover/btn:translate-x-1 transition-all"></i>
                    </a>
                </div>
            </div>

            <div class="premium-card p-6 md:col-span-3 lg:col-span-2 flex flex-col h-full">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Aktivitas Pemeriksaan</h3>
                        <p class="text-sm text-slate-500 font-medium">Tren layanan kesehatan 6 bulan terakhir</p>
                    </div>
                    <div class="p-2 bg-slate-50 text-slate-400 rounded-lg border border-slate-100 shadow-sm">
                        <i class="ph-bold ph-trend-up text-lg"></i>
                    </div>
                </div>
                
                <div id="apexTrendChart" class="w-full mt-auto" style="min-height: 260px;" data-source-url="{{ Route::has('bidan.dashboard.trend') ? route('bidan.dashboard.trend') : '' }}"></div>
            </div>

            <div class="premium-card p-6 lg:col-span-2 flex flex-col h-full relative overflow-hidden">
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-slate-50 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex justify-between items-center mb-7 relative z-10">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Distribusi Sasaran</h3>
                        <p class="text-sm text-slate-500 font-medium">Demografi pasien posyandu</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-100 shadow-sm text-slate-400">
                        <i class="ph-bold ph-chart-pie-slice text-xl"></i>
                    </div>
                </div>

                <div class="space-y-6 flex-1 flex flex-col justify-center relative z-10">
                    @foreach($sasaranItems as $item)
                        @php $percent = round(((int) $item['value'] / $totalSasaran) * 100); @endphp
                        <div class="group">
                            <div class="flex justify-between items-end mb-3">
                                <div class="flex items-center gap-3.5">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-gradient-to-br {{ $item['gradient'] }} text-white shadow-md {{ $item['shadow'] }} group-hover:scale-110 transition-transform duration-300">
                                        <i class="ph-fill {{ $item['icon'] }} text-2xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-base leading-none">{{ $item['label'] }}</h4>
                                        <span class="text-[12px] font-bold text-slate-400 mt-1.5 block">{{ $number($item['value']) }} Terdaftar</span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r {{ $item['gradient'] }} drop-shadow-sm">
                                        {{ $percent }}%
                                    </span>
                                </div>
                            </div>
                            
                            <div class="relative h-4 w-full bg-slate-100/80 rounded-full overflow-hidden shadow-inner p-0.5 border border-slate-200/50">
                                <div class="progress-bar-fill h-full rounded-full bg-gradient-to-r {{ $item['gradient'] }} relative shadow-sm" style="width: {{ $percent }}%">
                                    <div class="absolute inset-0 progress-stripes opacity-20"></div>
                                    <div class="absolute top-0 right-0 bottom-0 w-12 bg-gradient-to-l from-white/40 to-transparent rounded-r-full"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="premium-card md:col-span-3 lg:col-span-4 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-slate-100/60 flex justify-between items-center bg-white/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100/50">
                            <i class="ph-fill ph-list-dashes text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Pemeriksaan Terbaru</h3>
                    </div>
                    
                    <a href="{{ $routeSafe('bidan.pemeriksaan.index') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-700 flex items-center gap-1.5 bg-slate-50 hover:bg-emerald-50 px-4 py-2 rounded-lg border border-slate-200 hover:border-emerald-200 transition-all group">
                        Semua Riwayat <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <div class="overflow-x-auto p-2">
                    <table class="w-full text-left border-collapse premium-table">
                        <thead>
                            <tr>
                                <th class="py-4 px-6 text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100/80">Nama & NIK</th>
                                <th class="py-4 px-6 text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100/80">Kategori</th>
                                <th class="py-4 px-6 text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100/80">Status Validasi</th>
                                <th class="py-4 px-6 text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100/80">Parameter Kunci</th>
                                <th class="py-4 px-6 text-[11px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100/80 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($recentPemeriksaans->take(5) as $item)
                                <tr class="group rounded-xl">
                                    <td class="py-4 px-6 align-middle rounded-l-xl">
                                        <p class="font-bold text-slate-800">{{ data_get($item, 'nama', '-') }}</p>
                                        <p class="text-xs font-medium text-slate-400 mt-0.5 font-mono">{{ data_get($item, 'nik', '-') }}</p>
                                    </td>
                                    <td class="py-4 px-6 align-middle">
                                        <span class="inline-flex px-3 py-1 rounded-md text-[11px] font-bold {{ $kategoriTheme(data_get($item, 'kategori_raw')) }}">
                                            {{ data_get($item, 'kategori', '-') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-[11px] font-bold {{ $statusTheme(data_get($item, 'status_raw')) }}">
                                            @if(in_array(strtolower(data_get($item, 'status_raw', '')), ['menunggu', 'pending']))
                                                <span class="relative flex h-2 w-2">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                                </span>
                                            @else
                                                <i class="ph-fill ph-check-circle text-emerald-500 text-sm"></i>
                                            @endif
                                            {{ data_get($item, 'status', '-') }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 align-middle">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach(collect(data_get($item, 'parameter', []))->take(2) as $label => $value)
                                                <div class="bg-slate-50 px-2.5 py-1 rounded-md text-[11px]">
                                                    <span class="text-slate-500 font-semibold">{{ $label }}:</span> 
                                                    <span class="font-bold text-slate-800 ml-1">{{ $value }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-right rounded-r-xl">
                                        <a href="{{ data_get($item, 'id') ? $routeSafe('bidan.pemeriksaan.show', data_get($item, 'id')) : '#' }}" class="inline-flex items-center justify-center h-9 w-9 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-emerald-600 hover:border-emerald-300 hover:bg-emerald-50 transition-all shadow-sm group-hover:shadow-md">
                                            <i class="ph-bold ph-caret-right text-base"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-14 px-6 text-center text-slate-400 bg-slate-50/50 rounded-xl m-2">
                                        <div class="flex justify-center mb-3">
                                            <div class="h-14 w-14 rounded-full bg-white shadow-sm border border-slate-100 flex items-center justify-center">
                                                <i class="ph-fill ph-folder-open text-3xl text-slate-300"></i>
                                            </div>
                                        </div>
                                        <p class="font-bold text-sm text-slate-500">Belum ada antrean pemeriksaan</p>
                                        <p class="text-xs text-slate-400 mt-1">Data yang diinputkan kader akan otomatis muncul di sini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rawData = @json($trendData);
        let chartInstance = null;

        function renderApexChart(data) {
            const categories = data.map(item => item.short);
            const dataPoints = data.map(item => item.count);

            const options = {
                series: [{
                    name: 'Jumlah Pemeriksaan',
                    data: dataPoints
                }],
                chart: {
                    type: 'area',
                    height: 280,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    background: 'transparent',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        dynamicAnimation: { speed: 350 }
                    }
                },
                colors: ['#10b981'], 
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.0,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: { 
                    curve: 'smooth', 
                    width: 3 
                },
                xaxis: {
                    categories: categories,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 } },
                    tooltip: { enabled: false }
                },
                yaxis: {
                    labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 } }
                },
                grid: {
                    borderColor: 'rgba(241, 245, 249, 0.8)',
                    strokeDashArray: 4, 
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: true } },
                    padding: { top: 0, right: 0, bottom: 0, left: 10 }
                },
                tooltip: {
                    theme: 'light',
                    y: { formatter: function (val) { return val + " Pemeriksaan" } }
                },
                markers: {
                    size: 5,
                    colors: ['#ffffff'],
                    strokeColors: '#10b981',
                    strokeWidth: 2.5,
                    hover: { size: 7, sizeOffset: 3 }
                }
            };

            if (chartInstance) {
                chartInstance.destroy();
            }

            chartInstance = new ApexCharts(document.querySelector("#apexTrendChart"), options);
            chartInstance.render();
        }

        renderApexChart(rawData);

        const chartContainer = document.getElementById('apexTrendChart');
        setInterval(async () => {
            if (document.hidden || !chartContainer.dataset.sourceUrl) return;
            try {
                const res = await fetch(chartContainer.dataset.sourceUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                if (!res.ok) return;
                const payload = await res.json();
                const nextData = Array.isArray(payload) ? payload : payload.data;
                if (nextData && nextData.length) {
                    const normalizedData = nextData.slice(0,6).map((item) => ({
                        short: String(item.short || item.label), 
                        count: Number(item.count || 0)
                    }));
                    renderApexChart(normalizedData);
                }
            } catch (e) {}
        }, 30000);
    });
</script>
@endpush