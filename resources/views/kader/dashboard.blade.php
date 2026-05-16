@extends('layouts.kader')

@section('title', 'Dashboard Operasional Kader')
@section('page-name', 'Beranda Utama')

@push('styles')
{{-- Memastikan Phosphor Icons selalu termuat penuh --}}
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. EXPO-OUT ENTRANCE ANIMATIONS (SMOOTH & LIGHTWEIGHT)
       ==================================================================== */
    @keyframes nexusFadeUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .stagger-nexus > * {
        opacity: 0;
        animation: nexusFadeUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    .stagger-nexus > *:nth-child(1) { animation-delay: 50ms; }
    .stagger-nexus > *:nth-child(2) { animation-delay: 150ms; }
    .stagger-nexus > *:nth-child(3) { animation-delay: 250ms; }

    /* ====================================================================
       2. FLOATING MICRO-INTERACTIONS (GPU OPTIMIZED)
       ==================================================================== */
    @keyframes floatSlow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    @keyframes floatFast {
        0%, 100% { transform: translateY(0) rotate(0); }
        50% { transform: translateY(-15px) rotate(5deg); }
    }
    .anim-float-slow { 
        animation: floatSlow 6s ease-in-out infinite; 
        will-change: transform; /* Hanya aktifkan GPU di elemen kecil ini */
    }
    .anim-float-fast { 
        animation: floatFast 4s ease-in-out infinite; 
        will-change: transform;
    }

    /* ====================================================================
       3. NEXUS CARD SYSTEM (PERFORMANCE 100%)
       ==================================================================== */
    .stat-card-nexus {
        /* MENGHAPUS BACKDROP-FILTER BLUR YG MEMBUAT BERAT SCROLL */
        background: #ffffff; 
        border-radius: 24px;
        box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.04);
        border: 1px solid #f1f5f9;
        /* Optimasi transisi hanya pada properti spesifik */
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease, border-color 0.3s ease;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card-nexus:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -12px rgba(37, 99, 235, 0.12);
        border-color: rgba(59, 130, 246, 0.2);
    }

    .widget-panel {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 10px 40px -15px rgba(15, 23, 42, 0.04);
        border: 1px solid #f1f5f9;
        transition: box-shadow 0.3s ease;
    }
    .widget-panel:hover { box-shadow: 0 15px 50px -15px rgba(37, 99, 235, 0.1); }

    /* Custom Scrollbar Premium */
    .micro-scroll::-webkit-scrollbar { width: 5px; }
    .micro-scroll::-webkit-scrollbar-track { background: transparent; }
    .micro-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .micro-scroll::-webkit-scrollbar-thumb:hover { background: #3b82f6; }
</style>
@endpush

@section('content')
@php
    $jam = \Carbon\Carbon::now('Asia/Jakarta')->format('H');
    $sapaan = $jam < 11 ? 'Selamat Pagi' : ($jam < 15 ? 'Selamat Siang' : ($jam < 18 ? 'Selamat Sore' : 'Selamat Malam'));
    $emoji  = $jam < 11 ? '🌤️' : ($jam < 15 ? '☀️' : ($jam < 18 ? '🌅' : '🌙'));
    $namaDepan = explode(' ', Auth::user()->name ?? 'Kader')[0];
    
    $stats = $stats ?? [];
    $totalWarga = ($stats['total_bayi'] ?? 0) + ($stats['total_balita'] ?? 0) + ($stats['total_remaja'] ?? 0) + ($stats['total_lansia'] ?? 0) + ($stats['total_ibu_hamil'] ?? 0);
@endphp

{{-- Hapus gpu-accel dari parent agar RAM tidak jebol --}}
<div class="max-w-[1500px] mx-auto relative pb-12 stagger-nexus">

    {{-- FIX: Ambient Glow diubah dari FIXED blur menjadi ABSOLUTE radial-gradient ringan --}}
    <div class="absolute top-0 right-0 w-full h-[600px] bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-100/40 via-transparent to-transparent pointer-events-none z-0"></div>

    {{-- =======================================================
         1. HERO SECTION (DARK PREMIUM)
         ======================================================= --}}
    <div class="relative rounded-[32px] p-8 md:p-12 mb-8 overflow-hidden bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0f172a] shadow-xl z-10 border border-slate-700/50">
        
        <div class="absolute -bottom-24 -left-24 w-64 h-64 border-[40px] border-white/5 rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
            
            <div class="flex-1 text-center lg:text-left w-full">
                <div class="inline-flex items-center gap-3 px-4 py-2 bg-slate-800/80 border border-slate-600 rounded-full mb-6 shadow-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span id="realtime-clock" class="text-[10.5px] font-black text-slate-200 uppercase tracking-[0.15em] font-poppins">Memuat Waktu...</span>
                </div>

                <h1 class="text-[32px] md:text-[42px] font-black text-white tracking-tight font-poppins mb-3 leading-tight">
                    {{ $sapaan }}, {{ $namaDepan }}! {{ $emoji }}
                </h1>
                <p class="text-slate-300 font-medium text-[14px] leading-relaxed max-w-2xl mx-auto lg:mx-0 mb-8 opacity-90">
                    Sistem operasional Posyandu aktif. Pastikan seluruh kedatangan warga diregistrasi melalui Meja 1 sebelum diarahkan ke area pemeriksaan fisik dan pencatatan gizi.
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4">
                    <div class="px-5 py-3.5 bg-slate-800 border border-slate-700 rounded-[20px] flex items-center gap-4 transition-transform hover:-translate-y-1 cursor-default group">
                        <div class="w-11 h-11 rounded-[14px] bg-blue-500/20 flex items-center justify-center text-blue-400 border border-blue-500/30 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                            <i class="ph-fill ph-users-three text-[22px]"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Total Arsip Demografis</p>
                            <p class="text-white text-[18px] font-black font-poppins mt-1">{{ number_format($totalWarga) }}</p>
                        </div>
                    </div>
                    <div class="px-5 py-3.5 bg-slate-800 border border-slate-700 rounded-[20px] flex items-center gap-4 transition-transform hover:-translate-y-1 cursor-default group">
                        <div class="w-11 h-11 rounded-[14px] bg-emerald-500/20 flex items-center justify-center text-emerald-400 border border-emerald-500/30 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                            <i class="ph-fill ph-clipboard-text text-[22px]"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">Kehadiran Hari Ini</p>
                            <p class="text-white text-[18px] font-black font-poppins mt-1">{{ $stats['kehadiran_hari_ini'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3D Floating Icons (Hapus pulse & blur berlebih) --}}
            <div class="hidden lg:flex w-56 h-56 relative items-center justify-center shrink-0">
                <div class="w-28 h-28 bg-gradient-to-br from-blue-500 to-cyan-400 rounded-[28px] flex items-center justify-center text-[56px] text-white shadow-[0_15px_30px_rgba(37,99,235,0.3)] relative z-20 anim-float-slow border border-white/20">
                    <i class="ph-fill ph-shield-check"></i>
                </div>
                
                <div class="absolute top-0 right-4 w-12 h-12 bg-amber-400/90 rounded-full border border-amber-300 flex items-center justify-center text-white text-[24px] anim-float-fast shadow-lg z-30">
                    <i class="ph-fill ph-sparkle"></i>
                </div>
                
                <div class="absolute bottom-4 -left-4 w-14 h-14 bg-rose-400/90 rounded-[16px] border border-rose-300 flex items-center justify-center text-white text-[28px] anim-float-slow shadow-lg z-30" style="animation-delay: 1s;">
                    <i class="ph-fill ph-heartbeat"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- =======================================================
         2. STAT CARDS DEMOGRAFIS (6 KOLOM)
         ======================================================= --}}
    @php
        $statCards = [
            ['label' => 'Bayi (<1 Thn)', 'val' => $stats['total_bayi'] ?? 0, 'icon' => 'ph-baby', 'col' => 'sky'],
            ['label' => 'Balita (1-5 Thn)', 'val' => $stats['total_balita'] ?? 0, 'icon' => 'ph-smiley', 'col' => 'rose'],
            ['label' => 'Ibu Hamil', 'val' => $stats['total_ibu_hamil'] ?? 0, 'icon' => 'ph-person', 'col' => 'pink'],
            ['label' => 'Remaja', 'val' => $stats['total_remaja'] ?? 0, 'icon' => 'ph-student', 'col' => 'violet'],
            ['label' => 'Lansia', 'val' => $stats['total_lansia'] ?? 0, 'icon' => 'ph-wheelchair', 'col' => 'emerald'],
        ];
        $maxVal = max(1, $stats['total_bayi'] ?? 1, $stats['total_balita'] ?? 1, $stats['total_ibu_hamil'] ?? 1, $stats['total_remaja'] ?? 1, $stats['total_lansia'] ?? 1);
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-5 mb-8 relative z-10">
        
        @foreach($statCards as $c)
        <div class="stat-card-nexus p-5 h-[175px] group">
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-{{ $c['col'] }}-50 rounded-full opacity-50 pointer-events-none group-hover:scale-[2] transition-transform duration-700"></div>
            
            <div class="w-12 h-12 rounded-[14px] bg-{{ $c['col'] }}-50 text-{{ $c['col'] }}-500 flex items-center justify-center text-[24px] mb-auto border border-{{ $c['col'] }}-100 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-300 shadow-sm relative z-10">
                <i class="ph-fill {{ $c['icon'] }}"></i>
            </div>
            
            <div class="mt-4 relative z-10">
                <h3 class="text-[32px] font-black text-slate-800 font-poppins leading-none tracking-tight mb-1">{{ number_format($c['val']) }}</h3>
                <p class="text-[9.5px] font-black text-slate-400 uppercase tracking-widest">{{ $c['label'] }}</p>
                <div class="w-full h-1.5 bg-slate-100 rounded-full mt-3.5 overflow-hidden">
                    <div class="h-full bg-{{ $c['col'] }}-500 rounded-full transition-all duration-1000" style="width: {{ min(100, ($c['val'] / $maxVal) * 100) }}%"></div>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Kartu ke-6: Agenda Aktif --}}
        <div class="stat-card-nexus p-5 h-[175px] bg-gradient-to-br from-[#f8fafc] to-[#f1f5f9] group">
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-indigo-50 rounded-full opacity-50 pointer-events-none group-hover:scale-[2] transition-transform duration-700"></div>

            <div class="flex justify-between items-start mb-auto relative z-10">
                <div class="w-12 h-12 rounded-[14px] bg-indigo-500 text-white flex items-center justify-center text-[24px] shadow-sm group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
                    <i class="ph-fill ph-calendar-check"></i>
                </div>
                <span class="px-2.5 py-1 bg-amber-100 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-[8px] border border-amber-200 flex items-center gap-1">
                    <i class="ph-fill ph-lightning"></i> Aktif
                </span>
            </div>
            
            <div class="mt-4 relative z-10">
                <h3 class="text-[32px] font-black text-slate-800 font-poppins leading-none tracking-tight mb-1">{{ $stats['jadwal_hari_ini'] ?? 0 }}</h3>
                <p class="text-[9.5px] font-black text-slate-500 uppercase tracking-widest">Agenda Hari Ini</p>
                <div class="w-full h-1.5 bg-slate-200 rounded-full mt-3.5 overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full" style="width: {{ ($stats['jadwal_hari_ini'] ?? 0) > 0 ? '100%' : '0%' }}"></div>
                </div>
            </div>
        </div>

    </div>

    {{-- =======================================================
         3. WIDGET BAWAH (GRAFIK & DAFTAR WARGA BARU)
         ======================================================= --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 relative z-10 items-stretch">
        
        {{-- KIRI: TRAFIK KEHADIRAN --}}
        <div class="xl:col-span-2 widget-panel flex flex-col h-full">
            <div class="px-8 py-6 border-b border-slate-100 bg-white flex flex-wrap justify-between items-center gap-4 rounded-t-[24px]">
                <div>
                    <h3 class="text-[17px] font-black text-slate-800 font-poppins leading-none mb-1.5">Trafik Kehadiran Posyandu</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Registrasi Meja 1 (7 Hari Terakhir)</p>
                </div>
                <div class="px-4 py-2 bg-blue-50 border border-blue-100 rounded-[12px] flex items-center gap-2 text-blue-600 cursor-default">
                    <i class="ph-fill ph-chart-line-up text-[16px]"></i>
                    <span class="text-[10px] font-black uppercase tracking-wider">Grafik Operasional</span>
                </div>
            </div>
            
            <div class="p-8 flex-1 relative w-full min-h-[300px]">
                <canvas id="trafficChart"></canvas>
            </div>
        </div>

        {{-- KANAN: WARGA BARU TERDAFTAR --}}
        <div class="xl:col-span-1 widget-panel flex flex-col h-full">
            <div class="px-8 py-6 border-b border-slate-100 bg-white rounded-t-[24px] flex justify-between items-center">
                <div>
                    <h3 class="text-[17px] font-black text-slate-800 font-poppins leading-none mb-1.5">Pendaftaran Baru</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Arsip Balita Terkini</p>
                </div>
            </div>
            
            <div class="p-5 flex-1 overflow-y-auto micro-scroll min-h-[250px] bg-slate-50">
                @if(isset($balita_baru) && count($balita_baru) > 0)
                    <div class="space-y-3">
                        @foreach($balita_baru as $balita)
                            <div class="p-4 bg-white border border-slate-100 rounded-[18px] flex items-center gap-4 hover:shadow-sm hover:border-blue-200 transition-all duration-300 hover:-translate-y-0.5 group cursor-default">
                                <div class="w-11 h-11 rounded-[12px] bg-sky-50 text-sky-500 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:bg-sky-100 transition-all"><i class="ph-fill ph-baby text-[22px]"></i></div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <p class="text-[14px] font-black text-slate-800 truncate font-poppins group-hover:text-blue-600 transition-colors">{{ $balita->nama_lengkap ?? 'Balita' }}</p>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 truncate uppercase tracking-widest">Ibu: <span class="text-slate-600">{{ $balita->nama_ibu ?? '-' }}</span></p>
                                </div>
                                <span class="text-[9px] font-black px-2.5 py-1.5 bg-slate-50 text-slate-500 rounded-[8px] border border-slate-200 shrink-0">{{ $balita->created_at->translatedFormat('d M') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center p-6">
                        <div class="w-16 h-16 bg-slate-100 rounded-[18px] flex items-center justify-center text-[32px] mb-4 border border-slate-200 text-slate-400"><i class="ph-fill ph-tray"></i></div>
                        <h4 class="text-[15px] font-black text-slate-700 font-poppins">Belum Ada Data</h4>
                        <p class="text-[11px] font-medium text-slate-400 mt-1 max-w-[200px] mx-auto">Arsip pendaftaran balita baru akan otomatis masuk ke daftar ini.</p>
                    </div>
                @endif
            </div>
            
            <div class="p-5 border-t border-slate-100 bg-white rounded-b-[24px]">
                <a href="{{ route('kader.data.balita.index') ?? '#' }}" class="block w-full py-3.5 bg-slate-800 hover:bg-blue-600 text-white rounded-[16px] text-[11px] font-black uppercase tracking-[0.2em] text-center transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                    Lihat Seluruh Database
                </a>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. WAKTU REALTIME
    function initClock() {
        const el = document.getElementById('realtime-clock');
        if (!el) return;
        const hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

        function tick() {
            const d  = new Date();
            const hh = String(d.getHours()).padStart(2,'0');
            const mm = String(d.getMinutes()).padStart(2,'0');
            el.innerHTML = `${hari[d.getDay()]}, ${d.getDate()} ${bulan[d.getMonth()]} &bull; ${hh}:${mm} WIB`;
        }
        tick(); setInterval(tick, 1000);
    }

    // 2. RENDER GRAFIK CHART.JS
    function renderTrafficChart() {
        const canvas = document.getElementById('trafficChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const labels = {!! json_encode($chartLabels ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']) !!};
        const data   = {!! json_encode($chartData ?? [0, 5, 12, 8, 25, 10, 0]) !!};

        if (window._trafficChart) window._trafficChart.destroy();

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)'); 
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.02)');

        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        
        window._trafficChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Kehadiran Harian',
                    data: data,
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 4,
                    fill: true,
                    tension: 0.45, /* Kurva bezier halus */
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)', 
                        padding: 14, 
                        cornerRadius: 16,
                        titleFont: { size: 13, family: "'Poppins', sans-serif", weight: 'bold' },
                        bodyFont: { size: 13, weight: '500' }, 
                        displayColors: false,
                        backdropFilter: 'blur(10px)'
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(241, 245, 249, 0.8)', drawBorder: false }, 
                        ticks: { stepSize: 5, padding: 15, color: '#94a3b8', font: {weight: 'bold'} } 
                    },
                    x: { 
                        grid: { display: false, drawBorder: false }, 
                        ticks: { padding: 10, color: '#64748b', font: {weight: 'bold'} } 
                    }
                },
                interaction: { intersect: false, mode: 'index' },
                animation: { duration: 1500, easing: 'easeOutQuart' }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initClock();
        setTimeout(() => renderTrafficChart(), 200);
    });
</script>
@endpush