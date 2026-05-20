@extends('layouts.bidan')

@section('title', 'Dashboard Klinis')
@section('page-name', 'Beranda Utama')

@push('styles')
<style>
    /* ====================================================================
       NEXUS CLINICAL ANIMATION & GLASS ENGINE
       ==================================================================== */
    .fade-in-up { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .delay-1 { animation-delay: 0.1s; } .delay-2 { animation-delay: 0.2s; } .delay-3 { animation-delay: 0.3s; }
    
    .nexus-glass-card { 
        background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); 
        border: 1px solid rgba(226, 232, 240, 0.8); 
        box-shadow: 0 10px 40px -10px rgba(6, 182, 212, 0.05); 
        border-radius: 28px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .nexus-glass-card:hover {
        transform: translateY(-4px); border-color: rgba(6, 182, 212, 0.3);
        box-shadow: 0 20px 50px -10px rgba(8, 145, 178, 0.12);
    }

    /* SCROLLBAR MICRO KHUSUS ANTRIAN */
    .micro-scroll::-webkit-scrollbar { width: 4px; }
    .micro-scroll::-webkit-scrollbar-track { background: transparent; }
    .micro-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .micro-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush

@section('content')
@php
    $jam = \Carbon\Carbon::now('Asia/Jakarta')->format('H');
    $sapaan = $jam < 11 ? 'Selamat Pagi' : ($jam < 15 ? 'Selamat Siang' : ($jam < 18 ? 'Selamat Sore' : 'Selamat Malam'));
    $namaDepan = explode(' ', Auth::user()->name)[0] ?? 'Bidan';
@endphp

<div class="max-w-[1400px] mx-auto relative pb-16">

    {{-- Latar Belakang Dekoratif Global --}}
    <div class="fixed top-0 right-0 w-[600px] h-[600px] bg-gradient-to-br from-cyan-50/80 to-transparent rounded-full blur-3xl pointer-events-none z-0"></div>

    {{-- =======================================================
         1. HERO SECTION (CLINICAL CYAN THEME)
         ======================================================= --}}
    <div class="relative rounded-[32px] md:rounded-[40px] p-6 md:p-10 mb-8 overflow-hidden shadow-[0_20px_60px_-15px_rgba(8,145,178,0.4)] fade-in-up border border-cyan-400/30 bg-gradient-to-br from-cyan-700 via-cyan-600 to-blue-700 z-10">
        
        {{-- Tekstur Background Hero --}}
        <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PHBhdGggZD0iTTAgMGgyMHYyMEgwem0xMCAxMGgxMHYxMEgxMHoiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMSIvPjwvc3ZnPg==')]"></div>
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-10 pointer-events-none hidden lg:block transform rotate-12"><i class="fas fa-user-nurse text-[120px] text-white"></i></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
            
            {{-- Kiri: Teks & Info --}}
            <div class="flex-1 text-center lg:text-left w-full">
                {{-- Kapsul Jam Realtime --}}
                <div class="inline-flex items-center gap-2.5 px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full mb-6">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                    </span>
                    <span id="realtime-clock" class="text-[11px] font-black text-white uppercase tracking-widest font-poppins">Memuat Waktu...</span>
                </div>

                <h1 class="text-3xl md:text-5xl lg:text-[54px] font-black text-white tracking-tight font-poppins mb-4 leading-tight">
                    {{ $sapaan }}, {{ $namaDepan }}! 👩‍⚕️
                </h1>
                <p class="text-cyan-100 font-medium text-[13px] md:text-[15px] leading-relaxed max-w-xl mx-auto lg:mx-0 mb-8">
                    Pusat komando validasi medis Posyandu Bantarkulon. Pantau antrian warga secara langsung dan berikan diagnosa yang presisi berdasarkan input kader.
                </p>

                {{-- Kapsul Statistik Cepat (Responsif Mobile) --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3">
                    <div class="px-4 py-2.5 bg-rose-500/20 backdrop-blur-md border border-rose-500/40 rounded-full flex items-center gap-2">
                        <i class="fas fa-procedures text-rose-300"></i>
                        <span class="text-white text-[12px] font-bold">{{ $stats['menunggu_validasi'] ?? 0 }} Antrian Menunggu</span>
                    </div>
                    <div class="px-4 py-2.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full flex items-center gap-2">
                        <i class="fas fa-check-double text-emerald-300"></i>
                        <span class="text-white text-[12px] font-bold">{{ $stats['selesai_divalidasi'] ?? 0 }} Validasi Selesai</span>
                    </div>
                </div>
            </div>

            {{-- Kanan: Tombol Action --}}
            <div class="shrink-0 w-full sm:w-auto mt-4 lg:mt-0">
                <a href="{{ route('bidan.pemeriksaan.index') }}" class="w-full inline-flex items-center justify-center gap-3 px-10 py-5 bg-white text-cyan-700 font-black text-[13px] uppercase tracking-widest rounded-2xl hover:bg-cyan-50 hover:scale-105 transition-all duration-300 shadow-[0_10px_25px_rgba(0,0,0,0.2)] group">
                    <i class="fas fa-bolt text-xl text-amber-500 group-hover:animate-pulse"></i> Buka Meja Validasi
                </a>
            </div>
        </div>
    </div>

    {{-- =======================================================
         2. STAT CARDS (4 KARTU GRID RESPONSIVE)
         ======================================================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8 relative z-10 fade-in-up delay-1">
        
        {{-- Card 1: Antrian Meja 5 --}}
        <div class="nexus-glass-card p-5 md:p-6 flex flex-col justify-between group overflow-hidden relative min-h-[140px] bg-gradient-to-br from-rose-50 to-white border-rose-100">
            <div class="flex justify-between items-start mb-2 relative z-10">
                <div class="w-12 h-12 rounded-[14px] bg-rose-100 text-rose-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform shadow-sm shrink-0"><i class="fas fa-procedures"></i></div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800 font-poppins leading-none">{{ number_format($stats['menunggu_validasi'] ?? 0) }}</h3>
                <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mt-1.5">Antrian Meja 5</p>
            </div>
            <div class="absolute -right-4 -bottom-4 text-[70px] text-rose-500/5 group-hover:text-rose-500/10 transition-colors pointer-events-none"><i class="fas fa-procedures"></i></div>
        </div>

        {{-- Card 2: Kinerja Hari Ini --}}
        <div class="nexus-glass-card p-5 md:p-6 flex flex-col justify-between group overflow-hidden relative min-h-[140px]">
            <div class="flex justify-between items-start mb-2 relative z-10">
                <div class="w-12 h-12 rounded-[14px] bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl border border-emerald-100 group-hover:scale-110 transition-transform shadow-sm shrink-0"><i class="fas fa-check-double"></i></div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800 font-poppins leading-none">{{ number_format($stats['selesai_divalidasi'] ?? 0) }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1.5">Kinerja Hari Ini</p>
            </div>
        </div>

        {{-- Card 3: Alert Risiko (Waspada Medis Terintegrasi) --}}
        @php
            $totalRisiko = ($alertRisiko['balita_waspada'] ?? 0) + ($alertRisiko['lansia_metabolik'] ?? 0);
            $risikoColor = $totalRisiko > 0 ? 'amber' : 'slate';
        @endphp
        <div class="nexus-glass-card p-5 md:p-6 flex flex-col justify-between group overflow-hidden relative min-h-[140px]">
            <div class="flex justify-between items-start mb-2 relative z-10">
                <div class="w-12 h-12 rounded-[14px] bg-{{ $risikoColor }}-50 text-{{ $risikoColor }}-500 flex items-center justify-center text-xl border border-{{ $risikoColor }}-100 group-hover:scale-110 transition-transform shadow-sm shrink-0"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800 font-poppins leading-none">{{ number_format($totalRisiko) }}</h3>
                <p class="text-[10px] font-black text-{{ $risikoColor }}-500 uppercase tracking-widest mt-1.5">Peringatan Medis</p>
            </div>
        </div>

        {{-- Card 4: Agenda Aktif (Dark Mode) --}}
        <div class="nexus-glass-card p-5 md:p-6 flex flex-col justify-between group overflow-hidden relative min-h-[140px] bg-gradient-to-br from-slate-800 to-slate-900 border-slate-700 text-white">
            <div class="flex justify-between items-start mb-2 relative z-10">
                <div class="w-12 h-12 rounded-[14px] bg-cyan-500/30 text-cyan-300 flex items-center justify-center text-xl border border-cyan-400/30 shadow-sm shrink-0"><i class="fas fa-calendar-check"></i></div>
                <span class="px-2.5 py-1 bg-cyan-500/20 text-cyan-300 text-[9px] font-black rounded-full flex items-center gap-1 border border-cyan-500/30">
                    <i class="fas fa-satellite-dish animate-pulse"></i> LIVE
                </span>
            </div>
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-white font-poppins leading-none">{{ number_format($stats['jadwal_hari_ini'] ?? 0) }}</h3>
                <p class="text-[10px] font-black text-cyan-400 uppercase tracking-widest mt-1.5">Agenda Aktif</p>
            </div>
        </div>

    </div>

    {{-- =======================================================
         3. WIDGET GRID UTAMA (LIVE ANTRIAN & GRAFIK)
         ======================================================= --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 relative z-10 fade-in-up delay-2">
        
        {{-- KIRI: LIVE ANTRIAN MEJA 5 (7 KOLOM) --}}
        <div class="xl:col-span-7 nexus-glass-card overflow-hidden flex flex-col min-h-[400px]">
            <div class="px-6 md:px-8 py-5 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                <div>
                    <h3 class="text-[16px] font-black text-slate-800 font-poppins leading-none flex items-center gap-2">
                        <span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span></span> 
                        Antrian Meja 5
                    </h3>
                    <p class="text-[11px] font-bold text-slate-400 mt-1">Dikirim oleh kader untuk divalidasi</p>
                </div>
                <a href="{{ route('bidan.pemeriksaan.index') }}" class="text-[11px] font-black text-cyan-600 hover:text-cyan-800 tracking-widest uppercase flex items-center gap-1">Lihat Semua <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="p-4 md:p-6 flex-1 overflow-y-auto micro-scroll bg-slate-50/30 max-h-[350px]">
                @if(isset($antrianLive) && $antrianLive->count() > 0)
                    <div class="space-y-3">
                        @foreach($antrianLive as $antrian)
                            @php 
                                // Memanggil relasi yang benar dari model Pemeriksaan -> Kunjungan -> Pasien
                                $pasienData = $antrian->kunjungan->pasien ?? null;
                                $namaPasien = $pasienData ? $pasienData->nama_lengkap : 'Anonim';
                                $kategori = strtoupper($antrian->kategori_pasien ?? 'UMUM');
                                
                                $warnaKat = match(strtolower($kategori)) {
                                    'balita' => 'text-rose-600 bg-rose-50 border-rose-100',
                                    'remaja' => 'text-sky-600 bg-sky-50 border-sky-100',
                                    'lansia' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                    default => 'text-slate-600 bg-slate-50 border-slate-100'
                                };
                            @endphp
                            <div class="p-4 bg-white border border-slate-200 rounded-[20px] hover:border-cyan-300 hover:shadow-lg transition-all group flex flex-col sm:flex-row sm:items-center gap-4">
                                <div class="w-12 h-12 rounded-[14px] bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 font-black font-poppins text-lg group-hover:bg-cyan-50 group-hover:text-cyan-600 transition-colors">
                                    {{ substr($namaPasien, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-[14px] font-black text-slate-800 font-poppins truncate group-hover:text-cyan-700 transition-colors">{{ $namaPasien }}</h4>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="px-2 py-0.5 text-[9px] font-black border rounded-md {{ $warnaKat }}">{{ $kategori }}</span>
                                        <span class="text-[10px] font-medium text-slate-400"><i class="far fa-clock mr-1"></i>{{ $antrian->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('bidan.pemeriksaan.validasi', $antrian->id) }}" class="w-full sm:w-auto px-6 py-3 bg-white border border-cyan-200 text-cyan-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-cyan-500 hover:text-white hover:border-cyan-500 transition-all text-center shrink-0 shadow-sm">
                                    Validasi Data
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center p-6">
                        <div class="text-5xl mb-4 animate-bounce"><i class="fas fa-mug-hot text-slate-300"></i></div>
                        <h4 class="text-[15px] font-black text-slate-700 font-poppins">Antrian Kosong</h4>
                        <p class="text-[12px] text-slate-400 mt-1 max-w-xs">Belum ada warga yang dikirim oleh kader lapangan untuk divalidasi. Waktunya bersantai sejenak!</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- KANAN: GRAFIK KINERJA & DEMOGRAFI (5 KOLOM) --}}
        <div class="xl:col-span-5 flex flex-col gap-6">
            
            {{-- Tren Validasi Line Chart --}}
            <div class="nexus-glass-card overflow-hidden flex flex-col min-h-[220px]">
                <div class="px-6 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <h3 class="text-[14px] font-black text-slate-800 font-poppins">Kinerja Validasi Mingguan</h3>
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center border border-cyan-100"><i class="fas fa-chart-line text-xs"></i></div>
                </div>
                <div class="p-5 flex-1 bg-white/30 relative">
                    <div class="relative w-full h-[150px]">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Demografi Donut Chart --}}
            <div class="nexus-glass-card overflow-hidden flex flex-col flex-1">
                <div class="px-6 py-4 border-b border-slate-100 bg-white/50 flex justify-between items-center">
                    <h3 class="text-[14px] font-black text-slate-800 font-poppins">Demografi Terdata</h3>
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100"><i class="fas fa-chart-pie text-xs"></i></div>
                </div>
                <div class="p-6 flex flex-col sm:flex-row items-center justify-center gap-6 md:gap-8 bg-white/30 flex-1">
                    <div class="relative w-[130px] h-[130px] shrink-0">
                        <canvas id="demografiChart"></canvas>
                    </div>
                    <div class="flex flex-col gap-2.5 w-full sm:w-auto">
                        <div class="flex justify-between items-center gap-4 text-[11px] font-bold"><span class="text-rose-500"><i class="fas fa-circle text-[8px] mr-1.5"></i>Balita</span> <span class="text-slate-800">{{ $demografi['balita'] ?? 0 }}</span></div>
                        <div class="flex justify-between items-center gap-4 text-[11px] font-bold"><span class="text-sky-500"><i class="fas fa-circle text-[8px] mr-1.5"></i>Remaja</span> <span class="text-slate-800">{{ $demografi['remaja'] ?? 0 }}</span></div>
                        <div class="flex justify-between items-center gap-4 text-[11px] font-bold"><span class="text-emerald-500"><i class="fas fa-circle text-[8px] mr-1.5"></i>Lansia</span> <span class="text-slate-800">{{ $demografi['lansia'] ?? 0 }}</span></div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
{{-- Wajib panggil CDN Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. JAM REALTIME FORMAT PREMIUM
    function initClock() {
        const el = document.getElementById('realtime-clock');
        if (!el) return;
        const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'], bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        setInterval(() => {
            const d = new Date(), hh = String(d.getHours()).padStart(2,'0'), mm = String(d.getMinutes()).padStart(2,'0');
            el.innerHTML = `${hari[d.getDay()]}, ${d.getDate()} ${bulan[d.getMonth()]} &bull; ${hh}:${mm} WIB`;
        }, 1000);
    }

    // 2. CHART TREN KINERJA (LINE)
    function renderTrendChart() {
        const ctx = document.getElementById('trendChart');
        if (!ctx) return;
        
        const labels = {!! json_encode($chartLabels ?? []) !!};
        const data = {!! json_encode($chartData ?? []) !!};
        
        if(window._trendChart) window._trendChart.destroy();
        
        const grad = ctx.getContext('2d').createLinearGradient(0, 0, 0, 150);
        grad.addColorStop(0, 'rgba(6, 182, 212, 0.3)'); // Cyan Glow
        grad.addColorStop(1, 'rgba(6, 182, 212, 0)');

        Chart.defaults.font.family = "'Poppins', sans-serif";
        
        window._trendChart = new Chart(ctx, {
            type: 'line',
            data: { 
                labels: labels, 
                datasets: [{ 
                    data: data, 
                    borderColor: '#06b6d4', 
                    backgroundColor: grad, 
                    borderWidth: 3, fill: true, tension: 0.4, 
                    pointBackgroundColor: '#ffffff', pointBorderColor: '#06b6d4', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                }] 
            },
            options: { 
                responsive: true, maintainAspectRatio: false, 
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 12, cornerRadius: 12, titleFont: { size: 13, family: "'Poppins', sans-serif" }, displayColors: false } }, 
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#f1f5f9', drawBorder: false }, ticks: { stepSize: 1, color: '#94a3b8' } }, 
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: {weight: 'bold'} } } 
                } 
            }
        });
    }

    // 3. CHART DEMOGRAFI (DONUT)
    function renderDemografiChart() {
        const ctx = document.getElementById('demografiChart');
        if (!ctx) return;
        
        if(window._demoChart) window._demoChart.destroy();
        
        const donutData = [
            {{ $demografi['balita'] ?? 0 }}, 
            {{ $demografi['remaja'] ?? 0 }}, 
            {{ $demografi['lansia'] ?? 0 }}
        ];
        
        const total = donutData.reduce((a,b) => a+b, 0);
        const finalData = total === 0 ? [1,1,1,1] : donutData;
        const bgColors = total === 0 ? ['#f1f5f9','#f1f5f9','#f1f5f9','#f1f5f9'] : ['#f43f5e', '#ec4899', '#0ea5e9', '#10b981'];

        window._demoChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Balita', 'Remaja', 'Lansia'],
                datasets: [{ 
                    data: finalData, 
                    backgroundColor: bgColors, 
                    borderColor: '#ffffff', borderWidth: 3, hoverOffset: 5 
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, cutout: '75%', 
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0, backgroundColor: '#1e293b', padding: 12, cornerRadius: 12, displayColors: false,
                        callbacks: { label: (item) => `${item.label}: ${total > 0 ? item.parsed : 0} orang` }
                    }
                } 
            }
        });
    }

    // INIT ALL SCRIPTS (Aman untuk SPA Navigation)
    document.addEventListener('DOMContentLoaded', () => {
        initClock();
        setTimeout(() => { renderTrendChart(); renderDemografiChart(); }, 200);
    });
</script>
@endpush