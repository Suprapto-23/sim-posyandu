@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('page-name', 'Overview')

@section('content')
<style>
/* ── SOFT CLEAN & PROFESSIONAL DASHBOARD STYLES ── */

.animate-stagger-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes slideUpFade { 
    from { opacity: 0; transform: translateY(20px); } 
    to { opacity: 1; transform: translateY(0); } 
}
.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.15s; }
.delay-300 { animation-delay: 0.2s; }
.delay-400 { animation-delay: 0.25s; }

/* Hero Soft Clean Premium */
.hero-admin { 
    background: linear-gradient(135deg, #0ea5e9 0%, #14b8a6 100%); 
    border-radius: 2.5rem; /* 40px */
    padding: 3rem; /* 48px */
    position: relative; 
    overflow: hidden; 
    box-shadow: 0 20px 40px -10px rgba(14, 165, 233, 0.25); 
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid rgba(255,255,255,0.2);
}
/* Micro-pattern for hero */
.hero-admin::before { 
    content: ''; position: absolute; inset: 0; 
    background-image: radial-gradient(rgba(255,255,255,0.15) 1px, transparent 1px);
    background-size: 24px 24px; pointer-events: none; 
}
.hero-txt { position: relative; z-index: 1; }
.hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); color: #fff; font-size: 11px; font-weight: 800; padding: 6px 16px; border-radius: 50px; margin-bottom: 20px; letter-spacing: 1px; text-transform: uppercase; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.hero-title { font-size: 38px; font-weight: 900; color: #fff; line-height: 1.2; margin-bottom: 12px; letter-spacing: -0.5px; font-family: 'Poppins', sans-serif; text-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.hero-desc { font-size: 15px; color: rgba(255,255,255,0.95); max-width: 500px; font-weight: 500; line-height: 1.6; }

@keyframes floatSoft {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(2deg); box-shadow: 0 25px 40px -15px rgba(0,0,0,0.15); }
}
.animate-float-soft { animation: floatSoft 6s ease-in-out infinite; }

/* Section Cards Clean */
.section-card { background: #fff; border-radius: 2rem; border: 1px solid #f1f5f9; box-shadow: 0 10px 30px rgba(0,0,0,0.02); padding: 32px; height: 100%; display: flex; flex-direction: column; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.section-card:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.04); border-color: #e2e8f0; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid #f8fafc; padding-bottom: 16px;}
.section-title { font-size: 16px; font-weight: 800; color: #334155; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 12px; }
.section-icon-wrap { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: transform 0.3s; }
.section-card:hover .section-icon-wrap { transform: scale(1.1) rotate(-5deg); }

/* ✨ FITUR BARU: TIMELINE LOGS */
.timeline-container { position: relative; padding-left: 20px; }
.timeline-container::before { content: ''; position: absolute; left: 24px; top: 10px; bottom: 10px; width: 2px; background: #f1f5f9; border-radius: 2px; z-index: 0; }
.log-item { position: relative; z-index: 1; display: flex; align-items: center; gap: 16px; padding: 12px 0; transition: all 0.2s; border-radius: 16px; margin-left: -12px; padding-left: 12px; padding-right: 12px; }
.log-item:hover { background: #f8fafc; transform: translateX(4px); }
.log-avatar { width: 36px; height: 36px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; border: 3px solid #fff; box-sizing: content-box; z-index: 2; }

@media(max-width: 768px) { 
    .hero-admin { padding: 32px; flex-direction: column; text-align: center; border-radius: 2rem; } 
    .hero-title { font-size: 28px; } 
    .hero-badge { margin: 0 auto 20px auto; }
    .section-card { padding: 24px; border-radius: 1.5rem; } 
}
</style>

<div class="space-y-8">
    
    <div class="hero-admin animate-stagger-up">
        <div class="hero-txt">
            <div class="hero-badge"><i class="fas fa-check-circle mr-1"></i> Sistem Beroperasi Normal</div>
            <h1 class="hero-title"><span id="dynamicGreeting">Halo</span>, <span class="text-white">{{ auth()->user()->name }}</span></h1>
            <p class="hero-desc">Selamat datang di pusat kendali PosyanduCare. Pantau statistik kesehatan dan kelola entitas pengguna dengan antarmuka yang bersih dan profesional.</p>
        </div>
        <div class="hidden md:flex items-center justify-center w-40 h-40 bg-white/10 backdrop-blur-md border border-white/30 rounded-[2.5rem] shadow-2xl relative z-10 animate-float-soft">
            <i class="fas fa-laptop-medical text-6xl text-white drop-shadow-lg"></i>
        </div>
    </div>

    <div>
        <div class="flex items-center gap-2 mb-4 px-2">
            <h3 class="text-[13px] font-black text-slate-500 uppercase tracking-widest font-poppins"><i class="fas fa-users-cog mr-2"></i>Entitas Pengguna</h3>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white rounded-[2rem] border border-slate-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all relative overflow-hidden group animate-stagger-up delay-100">
                <i class="fas fa-users absolute -right-2 -bottom-4 text-7xl text-slate-50 opacity-60 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500"></i>
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Warga</p>
                        <h4 class="text-4xl font-black text-slate-700 font-poppins tracking-tight">{{ $stats['total_user'] ?? 0 }}</h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center text-xl group-hover:bg-sky-500 group-hover:text-white transition-colors duration-300"><i class="fas fa-users"></i></div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all relative overflow-hidden group animate-stagger-up delay-200">
                <i class="fas fa-user-plus absolute -right-2 -bottom-4 text-7xl text-slate-50 opacity-60 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500"></i>
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Warga Baru</p>
                        <h4 class="text-4xl font-black text-slate-700 font-poppins tracking-tight">{{ $userBaruBulanIni ?? 0 }}</h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300"><i class="fas fa-user-plus"></i></div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all relative overflow-hidden group animate-stagger-up delay-300">
                <i class="fas fa-user-nurse absolute -right-2 -bottom-4 text-7xl text-slate-50 opacity-60 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500"></i>
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Akun Kader</p>
                        <h4 class="text-4xl font-black text-slate-700 font-poppins tracking-tight">{{ $stats['total_kader'] ?? 0 }}</h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl group-hover:bg-indigo-500 group-hover:text-white transition-colors duration-300"><i class="fas fa-user-nurse"></i></div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-100 p-6 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all relative overflow-hidden group animate-stagger-up delay-400">
                <i class="fas fa-user-md absolute -right-2 -bottom-4 text-7xl text-slate-50 opacity-60 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500"></i>
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Akun Bidan</p>
                        <h4 class="text-4xl font-black text-slate-700 font-poppins tracking-tight">{{ $stats['total_bidan'] ?? 0 }}</h4>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-500 flex items-center justify-center text-xl group-hover:bg-teal-500 group-hover:text-white transition-colors duration-300"><i class="fas fa-user-md"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="flex items-center gap-2 mb-4 px-2">
            <h3 class="text-[13px] font-black text-slate-500 uppercase tracking-widest font-poppins"><i class="fas fa-notes-medical mr-2"></i>Arsip Data Kesehatan</h3>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white rounded-[2rem] border border-sky-50 p-6 shadow-sm hover:shadow-xl hover:shadow-sky-100 hover:-translate-y-1 hover:border-sky-100 transition-all relative overflow-hidden group animate-stagger-up delay-100">
                <div class="absolute right-0 top-0 w-24 h-24 bg-sky-50 rounded-bl-full -mr-4 -mt-4 opacity-50 transition-transform group-hover:scale-110"></div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <p class="text-[11px] font-black text-sky-400 uppercase tracking-widest mb-1">Data Balita</p>
                        <h4 class="text-4xl font-black text-slate-700 font-poppins tracking-tight">{{ $stats['total_balita'] ?? 0 }}</h4>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform"><i class="fas fa-baby"></i></div>
                </div>
            </div>

            

            <div class="bg-white rounded-[2rem] border border-indigo-50 p-6 shadow-sm hover:shadow-xl hover:shadow-indigo-100 hover:-translate-y-1 hover:border-indigo-100 transition-all relative overflow-hidden group animate-stagger-up delay-300">
                <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 opacity-50 transition-transform group-hover:scale-110"></div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <p class="text-[11px] font-black text-indigo-400 uppercase tracking-widest mb-1">Remaja</p>
                        <h4 class="text-4xl font-black text-slate-700 font-poppins tracking-tight">{{ $stats['total_remaja'] ?? 0 }}</h4>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform"><i class="fas fa-user-graduate"></i></div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-orange-50 p-6 shadow-sm hover:shadow-xl hover:shadow-orange-100 hover:-translate-y-1 hover:border-orange-100 transition-all relative overflow-hidden group animate-stagger-up delay-400">
                <div class="absolute right-0 top-0 w-24 h-24 bg-orange-50 rounded-bl-full -mr-4 -mt-4 opacity-50 transition-transform group-hover:scale-110"></div>
                <div class="relative z-10 flex justify-between items-center">
                    <div>
                        <p class="text-[11px] font-black text-orange-400 uppercase tracking-widest mb-1">Lanjut Usia</p>
                        <h4 class="text-4xl font-black text-slate-700 font-poppins tracking-tight">{{ $stats['total_lansia'] ?? 0 }}</h4>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform"><i class="fas fa-wheelchair"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 section-card animate-stagger-up delay-200">
            <div class="section-header">
                <h3 class="section-title">
                    <div class="section-icon-wrap bg-sky-50 text-sky-500"><i class="fas fa-chart-area"></i></div>
                    Tren Registrasi Warga
                </h3>
                <span class="px-3 py-1.5 bg-slate-50 text-slate-500 text-[10px] font-bold rounded-lg border border-slate-200 hidden sm:block">7 Bulan Terakhir</span>
            </div>
            <div class="relative flex-1 min-h-[280px]">
                <canvas id="regChart"></canvas>
            </div>
        </div>

        <div class="section-card p-0 overflow-hidden animate-stagger-up delay-300">
            <div class="section-header mx-6 mt-6 mb-2 border-b border-slate-50 pb-4">
                <h3 class="section-title">
                    <div class="section-icon-wrap bg-slate-50 text-slate-500"><i class="fas fa-history"></i></div>
                    Log Sistem Terbaru
                </h3>
            </div>
            <div class="flex-1 overflow-y-auto px-6 pb-6 custom-scrollbar timeline-container">
                @forelse($loginTerbaru ?? [] as $l)
                <div class="log-item">
                    @php
                        $bg = 'bg-slate-100 text-slate-500'; 
                        if($l->role == 'admin') $bg = 'bg-sky-500 text-white';
                        if($l->role == 'kader') $bg = 'bg-indigo-400 text-white';
                        if($l->role == 'bidan') $bg = 'bg-teal-400 text-white';
                    @endphp
                    <div class="log-avatar {{ $bg }} shadow-sm">
                        {{ strtoupper(substr($l->display_name ?? 'U', 0, 1)) }}
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-bold text-slate-700 truncate">{{ $l->display_name ?? '-' }}</div>
                        <div class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest">{{ $l->role }}</div>
                    </div>
                    
                    <div class="text-right flex-shrink-0">
                        @if($l->status === 'success')
                            <span class="text-emerald-500 text-[11px] font-bold"><i class="fas fa-check-circle"></i></span>
                        @else
                            <span class="text-rose-500 text-[11px] font-bold"><i class="fas fa-times-circle"></i></span>
                        @endif
                        <div class="text-[9px] font-medium text-slate-400 mt-1">{{ \Carbon\Carbon::parse($l->login_at)->diffForHumans(null, true, true) }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-slate-300 relative z-10">
                    <i class="fas fa-server text-4xl mb-3 opacity-50"></i>
                    <p class="text-xs font-bold uppercase tracking-widest">Tidak ada aktivitas.</p>
                </div>
                @endforelse
            </div>
        </div>
        
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // ✨ FITUR BARU: Dynamic Greeting based on time
    const hour = new Date().getHours();
    let greeting = 'Halo';
    if (hour >= 5 && hour < 12) greeting = 'Selamat Pagi';
    else if (hour >= 12 && hour < 15) greeting = 'Selamat Siang';
    else if (hour >= 15 && hour < 18) greeting = 'Selamat Sore';
    else greeting = 'Selamat Malam';
    document.getElementById('dynamicGreeting').innerText = greeting;

    // Konfigurasi Default Chart.js
    Chart.defaults.font.family = "'Poppins', 'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    const ctx = document.getElementById('regChart');
    if(ctx) {
        const c = ctx.getContext('2d');
        const gradient = c.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(14, 165, 233, 0.25)'); 
        gradient.addColorStop(1, 'rgba(14, 165, 233, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels'] ?? []) !!},
                datasets: [{
                    label: 'Warga Terdaftar',
                    data: {!! json_encode($chartData['userData'] ?? []) !!},
                    borderColor: '#0ea5e9', 
                    backgroundColor: gradient,
                    borderWidth: 3,
                    tension: 0.4, 
                    fill: true,
                    pointRadius: 5, 
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#ffffff', 
                    pointBorderColor: '#0ea5e9',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: { 
                        backgroundColor: '#ffffff', 
                        titleColor: '#64748b',
                        bodyColor: '#0f172a',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 12, 
                        borderRadius: 12, 
                        displayColors: false, 
                        titleFont: { size: 11, family: 'Inter', weight: 'bold' }, 
                        bodyFont: { size: 15, weight: '900', family: 'Poppins' },
                        callbacks: {
                            label: function(context) { return context.parsed.y + ' Warga Baru'; }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, border: { display: false }, 
                        grid: { color: '#f8fafc', drawBorder: false }, 
                        ticks: { stepSize: 1, font: { weight: '600' }, padding: 10 } 
                    },
                    x: { 
                        border: { display: false }, grid: { display: false }, 
                        ticks: { font: { weight: '600' }, padding: 10 } 
                    }
                },
                interaction: { mode: 'index', intersect: false }
            }
        });
    }
});
</script>
@endpush