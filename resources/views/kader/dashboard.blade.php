@extends('layouts.kader')

@section('title', 'Dashboard Kader')
@section('page-name', 'Beranda Posyandu')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. STRICT TYPOGRAPHY ENGINE
       ==================================================================== */
    .font-poppins { font-family: 'Poppins', sans-serif !important; }
    .font-sans { font-family: 'Plus Jakarta Sans', sans-serif !important; }

    /* ====================================================================
       2. ENTERPRISE GLASSMORPHISM & SHADOWS (COMPACT VERSION)
       ==================================================================== */
    .card-premium {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 8px 24px -10px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(0,0,0,0.01);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .card-premium:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px -10px rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.25);
    }
    
    /* ====================================================================
       3. CINEMATIC ANIMATIONS & MICRO-INTERACTIONS
       ==================================================================== */
    @keyframes cinematicFadeUp {
        0% { opacity: 0; transform: translateY(16px) scale(0.99); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-fade-up { opacity: 0; animation: cinematicFadeUp 0.7s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
    .delay-100 { animation-delay: 100ms; } 
    .delay-200 { animation-delay: 200ms; }
    
    .hover-lift { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
    .hover-lift:hover { transform: translateX(4px); }

    .widget-scroll::-webkit-scrollbar { width: 4px; }
    .widget-scroll::-webkit-scrollbar-track { background: transparent; }
    .widget-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .widget-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
@endpush

@php
    $fullName = auth()->check() ? auth()->user()->name : 'Kader';
    $firstName = trim(explode(' ', $fullName)[0]);
    if(empty($firstName)) $firstName = 'Kader';
@endphp

@section('content')
<div class="max-w-[1400px] mx-auto z-10 relative font-sans">

    {{-- HEADER: SAPAAN DINAMIS & JAM DIGITAL PREMIUM --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-6 animate-fade-up relative">
        <div class="absolute -top-12 -left-12 w-64 h-64 bg-emerald-400 rounded-full blur-[120px] opacity-15 pointer-events-none"></div>

        <div class="flex-1 relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[10px] bg-white border border-slate-200 text-emerald-600 text-[10px] font-black uppercase tracking-widest mb-3 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Kader Posyandu Aktif
            </div>
            
            <h1 id="dynamic-greeting" class="font-poppins text-2xl md:text-[32px] font-black text-slate-800 tracking-tight leading-none mb-2.5 transition-opacity duration-700 opacity-0">
                Memuat sapaan...
            </h1>
            
            <p id="dynamic-quote" class="text-[13px] font-medium text-slate-500 leading-relaxed max-w-2xl border-l-[3px] border-emerald-400 pl-3.5 transition-opacity duration-700 opacity-0">
                Memuat motivasi...
            </p>
        </div>

        {{-- WIDGET WAKTU DIGITAL --}}
        <div class="bg-white/95 backdrop-blur-xl px-4 py-3.5 rounded-[20px] border border-slate-200 shadow-[0_8px_24px_-5px_rgba(15,23,42,0.05)] shrink-0 flex items-center gap-3.5 group hover:border-emerald-300 transition-colors z-10 cursor-default">
            <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 flex items-center justify-center border border-emerald-200 shadow-inner group-hover:scale-105 transition-transform duration-300">
                <i class="ph-fill ph-clock text-[22px]"></i>
            </div>
            <div class="flex flex-col justify-center">
                <p id="tgl-indo" class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Memuat Tanggal...</p>
                <div class="flex items-baseline gap-0.5 font-poppins">
                    <p id="jam-digital" class="text-xl font-black text-slate-800 tracking-tighter leading-none">00:00</p>
                    <p id="detik-digital" class="text-[11px] font-black text-emerald-500 tracking-tighter mb-0.5 animate-pulse">:00</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 4 KARTU METRIK UTAMA (Lebih Ringkas & Proporsional) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6 animate-fade-up delay-100 relative z-10">
        
        <div class="card-premium p-5 flex flex-col justify-between min-h-[140px] group cursor-default">
            <div class="flex justify-between items-center mb-3 relative z-10">
                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 flex items-center justify-center group-hover:scale-105 transition-transform border border-emerald-200 shadow-sm">
                    <i class="ph-fill ph-baby text-[20px]"></i>
                </div>
                {{-- Indikator Pendaftaran Baru Hari Ini --}}
                <div class="bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-[8px] flex items-center gap-1 shadow-sm">
                    <i class="ph-bold ph-plus text-emerald-600 text-[9px]"></i>
                    <span class="text-[10px] font-black text-emerald-700">{{ $stats['balita_baru'] ?? 3 }} <span class="font-bold text-[9px] text-emerald-600/80">Baru</span></span>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="font-poppins text-2xl font-black text-slate-800 tracking-tight mb-0.5">{{ number_format($stats['total_balita']) }}</h3>
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Balita Aktif</p>
                    <span class="text-[9px] font-bold text-slate-400">Usia 12-59 Bln</span>
                </div>
            </div>
            <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-50 rounded-full blur-2xl opacity-60 group-hover:opacity-90 transition-opacity"></div>
        </div>

        <div class="card-premium p-5 flex flex-col justify-between min-h-[140px] group cursor-default">
            <div class="flex justify-between items-center mb-3 relative z-10">
                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 flex items-center justify-center group-hover:scale-105 transition-transform border border-indigo-200 shadow-sm">
                    <i class="ph-fill ph-graduation-cap text-[20px]"></i>
                </div>
                <div class="bg-indigo-50 border border-indigo-200/60 px-2 py-0.5 rounded-[8px] flex items-center gap-1 shadow-sm">
                    <i class="ph-bold ph-plus text-indigo-600 text-[9px]"></i>
                    <span class="text-[10px] font-black text-indigo-700">{{ $stats['remaja_baru'] ?? 0 }} <span class="font-bold text-[9px] text-indigo-600/80">Baru</span></span>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="font-poppins text-2xl font-black text-slate-800 tracking-tight mb-0.5">{{ number_format($stats['total_remaja']) }}</h3>
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Remaja Terdaftar</p>
                    <span class="text-[9px] font-bold text-slate-400">Gizi Posbindu</span>
                </div>
            </div>
            <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-indigo-50 rounded-full blur-2xl opacity-60 group-hover:opacity-90 transition-opacity"></div>
        </div>

        <div class="card-premium p-5 flex flex-col justify-between min-h-[140px] group cursor-default">
            <div class="flex justify-between items-center mb-3 relative z-10">
                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center group-hover:scale-105 transition-transform border border-amber-200 shadow-sm">
                    <i class="ph-fill ph-wheelchair text-[20px]"></i>
                </div>
                <div class="bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-[8px] flex items-center gap-1 shadow-sm">
                    <i class="ph-bold ph-plus text-amber-600 text-[9px]"></i>
                    <span class="text-[10px] font-black text-amber-700">{{ $stats['lansia_baru'] ?? 1 }} <span class="font-bold text-[9px] text-amber-600/80">Baru</span></span>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="font-poppins text-2xl font-black text-slate-800 tracking-tight mb-0.5">{{ number_format($stats['total_lansia']) }}</h3>
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Lansia Terdaftar</p>
                    <span class="text-[9px] font-bold text-slate-400">Klinis Rutin</span>
                </div>
            </div>
            <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-amber-50 rounded-full blur-2xl opacity-60 group-hover:opacity-90 transition-opacity"></div>
        </div>

        <div class="card-premium p-5 flex flex-col justify-between min-h-[140px] group border-sky-200 bg-gradient-to-br from-white to-sky-50/50 cursor-default">
            <div class="flex justify-between items-center mb-3 relative z-10">
                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-sky-100 to-sky-200 text-sky-600 flex items-center justify-center group-hover:scale-105 transition-transform shadow-sm border border-sky-300">
                    <i class="ph-bold ph-users-three text-[20px]"></i>
                </div>
                <div class="bg-white border border-sky-200 px-2 py-0.5 rounded-[8px] shadow-sm flex items-center gap-1">
                    <i class="ph-bold ph-trend-up text-sky-500 text-[9px]"></i>
                    <span class="text-[10px] font-black text-sky-600">{{ $stats['persentase_hari_ini'] }}%</span>
                </div>
            </div>
            <div class="relative z-10">
                <h3 class="font-poppins text-2xl font-black text-slate-800 tracking-tight mb-0.5">
                    {{ number_format($stats['kehadiran_hari_ini']) }} <span class="text-xs font-bold text-slate-400 font-sans tracking-normal">Hadir</span>
                </h3>
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Kunjungan Hari Ini</p>
                    <span class="text-[9px] font-bold text-sky-500 flex items-center gap-0.5"><span class="h-1.5 w-1.5 rounded-full bg-sky-400 animate-pulse"></span> Meja 1</span>
                </div>
            </div>
        </div>

    </div>

    {{-- KONTEN BAWAH (Grafik & Panel Info) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 animate-fade-up delay-200 relative z-10">
        
        <div class="lg:col-span-2 card-premium p-5 md:p-6 flex flex-col">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="font-poppins text-[16px] font-black text-slate-800 flex items-center gap-2 mb-0.5">
                        <i class="ph-fill ph-chart-line-up text-emerald-500 text-lg"></i> Peta Kunjungan Posyandu
                    </h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Statistik 7 Hari Terakhir</p>
                </div>
                <div class="inline-flex px-2.5 py-1 bg-slate-50 rounded-[8px] border border-slate-200 items-center gap-1.5 shadow-sm shrink-0">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-500">Realtime Sync</span>
                </div>
            </div>
            <div class="flex-1 w-full min-h-[280px] relative">
                <canvas id="kehadiranChart"></canvas>
            </div>
        </div>

        <div class="flex flex-col gap-5">
            
            <div class="card-premium p-5 flex-1 flex flex-col max-h-[380px]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-poppins text-[13px] font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                        <i class="ph-fill ph-calendar-star text-rose-500 text-[16px]"></i> Agenda Sistem
                    </h3>
                    <button class="text-[9px] font-bold text-emerald-600 uppercase bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded transition-colors">Semua</button>
                </div>

                <div class="overflow-y-auto widget-scroll pr-1 space-y-2.5 flex-1">
                    @forelse($jadwal_mendatang as $jadwal)
                        <div class="flex items-start gap-3 p-3 rounded-[14px] bg-slate-50 hover:bg-white border border-slate-100 hover:border-slate-200 transition-all group hover-lift cursor-pointer shadow-sm">
                            <div class="w-10 h-10 rounded-[12px] bg-white text-rose-500 flex flex-col items-center justify-center shrink-0 border border-slate-200 shadow-sm group-hover:border-rose-200 transition-colors">
                                <span class="font-poppins text-[14px] font-black leading-none group-hover:text-rose-600">{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d') }}</span>
                                <span class="text-[8px] font-black uppercase mt-0.5 text-slate-400">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('M') }}</span>
                            </div>
                            <div class="pt-0.5">
                                <h4 class="text-[12px] font-black text-slate-800 line-clamp-1 mb-1 group-hover:text-emerald-600 transition-colors">{{ $jadwal->judul }}</h4>
                                <div class="inline-flex px-1.5 py-0.5 rounded-[5px] border border-slate-200 bg-white items-center gap-1 text-[9px] font-bold text-slate-500">
                                    <i class="ph-bold ph-clock text-slate-400"></i> {{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-center py-6">
                            <div class="w-12 h-12 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mb-2.5">
                                <i class="ph-fill ph-calendar-blank text-xl text-slate-300"></i>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400">Kalender bersih.<br>Belum ada kegiatan terdekat.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card-premium p-5 flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-poppins text-[13px] font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                        <i class="ph-fill ph-sparkle text-amber-500 text-[16px]"></i> Warga Terdaftar
                    </h3>
                    <button class="text-[9px] font-bold text-emerald-600 uppercase bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded transition-colors">Detail</button>
                </div>
                
                <div class="space-y-3 flex-1 overflow-y-auto widget-scroll pr-1">
                    @forelse($sasaran_baru as $warga)
                        <div class="flex items-center gap-2.5 group hover-lift cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                            <div class="w-8 h-8 rounded-[10px] bg-emerald-50 text-emerald-600 flex items-center justify-center text-[12px] font-black shadow-sm shrink-0 border border-emerald-100 font-poppins">
                                {{ substr($warga->nama_lengkap, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[12px] font-black text-slate-800 truncate mb-0.5 group-hover:text-emerald-600 transition-colors">{{ $warga->nama_lengkap }}</h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"><i class="ph-bold ph-clock-counter-clockwise mr-0.5"></i> {{ \Carbon\Carbon::parse($warga->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-[10px] text-center font-bold text-slate-400 py-3">Database warga baru masih kosong.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // =======================================================
        // 1. ENGINE MOTIVASI ACAK & SAPAAN
        // =======================================================
        const namaKader = "{!! addslashes($firstName) !!}"; 
        const greetingEl = document.getElementById('dynamic-greeting');
        const quoteEl = document.getElementById('dynamic-quote');

        const quotes = {
            pagi: [
                "Awali hari dengan senyum hangat! Dedikasimu pagi ini adalah kunci tumbuh kembang generasi masa depan.",
                "Semangat pagi, Pahlawan Desa! Udara segar sangat pas untuk memulai pelayanan prima hari ini.",
                "Mari tebarkan energi positif! Kebaikan kecilmu hari ini berdampak besar bagi seluruh warga."
            ],
            siang: [
                "Tetap semangat! Lelahmu melayani warga di siang ini adalah amal jariyah yang tak ternilai.",
                "Jangan lupa minum air putih dan istirahat sejenak. Pelayanan prima butuh kader yang sehat!",
                "Posyandu sedang ramai? Tarik napas, berikan senyum terbaik, dan lanjutkan pekerjaan hebatmu!"
            ],
            sore: [
                "Hampir di penghujung hari. Evaluasi sejenak dan pastikan data rekam medis telah tercatat presisi.",
                "Terima kasih atas kerja kerasmu seharian ini. Setiap peluhmu mencatat sejarah kesehatan desa.",
                "Sore yang pas untuk merekap data. Pastikan semua jadwal dan register warga sudah tersinkronisasi."
            ],
            malam: [
                "Malam yang tenang. Terima kasih telah menyelesaikan tugas mulia hari ini, saatnya beristirahat.",
                "Sistem telah merekap semua kerjamu hari ini. Sekarang waktunya rileks dan nikmati waktu untuk dirimu sendiri.",
                "Kerja kerasmu hari ini sangat berharga. Mari pulihkan energi untuk menyambut esok yang lebih baik."
            ]
        };

        function updateGreeting() {
            const currentHour = new Date().getHours();
            let greeting, icon, category;

            if (currentHour >= 4 && currentHour < 11) {
                greeting = 'Selamat Pagi'; icon = 'ph-sun text-amber-500'; category = 'pagi';
            } else if (currentHour >= 11 && currentHour < 15) {
                greeting = 'Selamat Siang'; icon = 'ph-sun-dim text-amber-600'; category = 'siang';
            } else if (currentHour >= 15 && currentHour < 18) {
                greeting = 'Selamat Sore'; icon = 'ph-cloud-sun text-orange-500'; category = 'sore';
            } else {
                greeting = 'Selamat Malam'; icon = 'ph-moon-stars text-indigo-500'; category = 'malam';
            }

            const randomQuote = quotes[category][Math.floor(Math.random() * quotes[category].length)];

            greetingEl.innerHTML = `${greeting}, <span class="text-emerald-500">${namaKader}</span>! <i class="ph-fill ${icon} text-[32px] animate-pulse"></i>`;
            quoteEl.innerHTML = `"${randomQuote}"`;
            
            setTimeout(() => {
                greetingEl.classList.remove('opacity-0');
                quoteEl.classList.remove('opacity-0');
            }, 100);
        }
        updateGreeting();

        // =======================================================
        // 2. JAM DIGITAL APPLE-STYLE REALTIME
        // =======================================================
        function initDigitalClock() {
            const elJam = document.getElementById('jam-digital');
            const elDetik = document.getElementById('detik-digital');
            const elTgl = document.getElementById('tgl-indo');
            const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

            setInterval(() => {
                const now = new Date();
                elJam.innerText = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                elDetik.innerText = ':' + String(now.getSeconds()).padStart(2, '0');
                elTgl.innerText = `${hari[now.getDay()]} ,  ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`;
            }, 1000);
        }
        initDigitalClock();

        // =======================================================
        // 3. CHART.JS CONFIGURATION
        // =======================================================
        const ctx = document.getElementById('kehadiranChart').getContext('2d');
        let gradientFill = ctx.createLinearGradient(0, 0, 0, 280);
        gradientFill.addColorStop(0, 'rgba(16, 185, 129, 0.18)'); 
        gradientFill.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: ' Total Kehadiran ',
                    data: {!! json_encode($chartData) !!},
                    borderColor: '#10b981', 
                    borderWidth: 2.5,
                    backgroundColor: gradientFill,
                    fill: true,
                    tension: 0.4, 
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 1.5,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#0f172a',
                        bodyColor: '#64748b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 12,
                        displayColors: false,
                        titleFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif", weight: '900' },
                        bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif", weight: '700' }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        suggestedMax: 10, 
                        grid: { color: '#f8fafc', drawBorder: false }, 
                        ticks: { stepSize: 5, padding: 8, color: '#94a3b8', font: {size: 10, family: "'Plus Jakarta Sans', sans-serif", weight: '700'} } 
                    },
                    x: { 
                        grid: { display: false, drawBorder: false }, 
                        ticks: { padding: 8, color: '#94a3b8', font: {size: 10, family: "'Plus Jakarta Sans', sans-serif", weight: '700'} } 
                    }
                },
                interaction: { intersect: false, mode: 'index' },
                animation: { duration: 1200, easing: 'easeOutQuart' }
            }
        });
    });
</script>
@endpush
@endsection