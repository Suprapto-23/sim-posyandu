@extends('layouts.bidan')

@section('title', 'Electronic Medical Record')
@section('page-name', 'Detail Rekam Medis')

@push('styles')
<style>
    /* Animasi Masuk Halus */
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* Scrollbar Modern */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Nexus Card Style */
    .nexus-card {
        background: #ffffff;
        border-radius: 28px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 40px -10px rgba(15, 23, 42, 0.05);
        position: relative;
        overflow: hidden;
    }

    /* List Item Timeline Premium */
    .nexus-timeline-row { 
        background: #ffffff; 
        border-radius: 20px; 
        border: 1px solid #f1f5f9; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .nexus-timeline-row:hover { 
        transform: translateY(-3px) scale(1.005); 
        border-color: #bae6fd; 
        box-shadow: 0 15px 30px -5px rgba(6, 182, 212, 0.1); 
        z-index: 10;
        position: relative;
    }
</style>
@endpush

@section('content')
@php
    // Failsafe Identitas
    $nama = $pasien->nama_lengkap ?? 'Pasien Anonim';
    $nik = $pasien->nik ?? 'Tidak ada NIK';
    $tglLahir = $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir) : null;
    $umur = $tglLahir ? $tglLahir->age : 0;
    
    // PERBAIKAN FATAL TAILWIND: Mapping Kelas Utuh (Anti-PurgeCSS)
    $config = match($pasien_type) {
        'balita'    => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'border' => 'border-sky-100', 'grad' => 'from-sky-400 to-sky-600', 'icon' => 'fa-baby', 'label' => 'Bayi & Balita'],
        'ibu_hamil' => ['bg' => 'bg-pink-50', 'text' => 'text-pink-600', 'border' => 'border-pink-100', 'grad' => 'from-pink-400 to-pink-600', 'icon' => 'fa-female', 'label' => 'Ibu Hamil'],
        'remaja'    => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-100', 'grad' => 'from-indigo-400 to-indigo-600', 'icon' => 'fa-user-graduate', 'label' => 'Remaja'],
        'lansia'    => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'grad' => 'from-emerald-400 to-emerald-600', 'icon' => 'fa-wheelchair', 'label' => 'Geriatri / Lansia'],
        default     => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-100', 'grad' => 'from-slate-400 to-slate-600', 'icon' => 'fa-user', 'label' => 'Umum'],
    };
@endphp

<div class="max-w-[1300px] mx-auto space-y-6 animate-slide-up pb-16">

    {{-- =================================================================
         1. NAVIGASI ATAS (Dinamis Sesuai Tipe Pasien)
         ================================================================= --}}
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('bidan.pasien.' . $pasien_type) }}" class="inline-flex items-center gap-3 text-[11px] font-black text-slate-400 hover:text-cyan-600 transition-colors uppercase tracking-widest group">
            <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center group-hover:border-cyan-200 group-hover:bg-cyan-50 transition-all shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </div>
            Kembali ke Buku Induk
        </a>
    </div>

    {{-- =================================================================
         2. HEADER KARTU IDENTITAS (NEXUS CARD)
         ================================================================= --}}
    <div class="nexus-card">
        <div class="absolute right-0 top-0 w-80 h-80 {{ $config['bg'] }} rounded-full blur-3xl opacity-70 -translate-y-1/3 translate-x-1/4 pointer-events-none"></div>
        
        <div class="p-8 md:p-10 flex flex-col lg:flex-row items-center gap-8 relative z-10">
            {{-- Avatar Ikon --}}
            <div class="w-28 h-28 md:w-32 md:h-32 rounded-[28px] bg-gradient-to-br {{ $config['grad'] }} text-white flex items-center justify-center text-5xl shadow-[0_15px_30px_rgba(0,0,0,0.15)] border-[5px] border-white shrink-0">
                <i class="fas {{ $config['icon'] }}"></i>
            </div>
            
            {{-- Biodata --}}
            <div class="flex-1 text-center lg:text-left">
                <div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-4 justify-center lg:justify-start">
                    <h1 class="text-3xl md:text-4xl font-black text-slate-800 font-poppins tracking-tight">{{ $nama }}</h1>
                    <span class="px-3.5 py-1 {{ $config['bg'] }} {{ $config['text'] }} text-[10px] font-black uppercase tracking-widest rounded-lg border {{ $config['border'] }} shrink-0">
                        {{ $config['label'] }}
                    </span>
                </div>
                
                <div class="flex flex-wrap justify-center lg:justify-start gap-x-6 gap-y-3 text-slate-500 text-[13px] font-bold">
                    <span class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100"><i class="fas fa-fingerprint text-slate-400"></i> NIK: {{ $nik }}</span>
                    <span class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100"><i class="fas fa-birthday-cake text-slate-400"></i> {{ $tglLahir ? $tglLahir->translatedFormat('d M Y') : '-' }} ({{ $umur }} Tahun)</span>
                    <span class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 w-full sm:w-auto"><i class="fas fa-map-marker-alt text-slate-400"></i> {{ $pasien->alamat ?? 'Alamat tidak tercatat' }}</span>
                </div>
            </div>

            {{-- Counter Kunjungan --}}
            <div class="flex gap-4 shrink-0 mt-6 lg:mt-0 w-full lg:w-auto justify-center">
                <div class="bg-slate-800 text-white px-6 py-5 rounded-[24px] text-center min-w-[130px] shadow-xl flex flex-col justify-center">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Periksa</p>
                    <p class="text-3xl font-black font-poppins">{{ $riwayatMedis->count() }} <span class="text-[12px] font-bold text-slate-400">Log</span></p>
                </div>
                <div class="bg-cyan-50 border border-cyan-100 text-cyan-800 px-6 py-5 rounded-[24px] text-center min-w-[130px] shadow-sm flex flex-col justify-center">
                    <p class="text-[10px] font-black text-cyan-500 uppercase tracking-widest mb-1">Imunisasi</p>
                    <p class="text-3xl font-black font-poppins text-cyan-600">{{ $riwayatImunisasi->count() }} <span class="text-[12px] font-bold text-cyan-500/80">Log</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================
         3. ANALITIK: GRAFIK & LOG VAKSIN
         ================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- GRAFIK KIRI --}}
        <div class="lg:col-span-8 nexus-card p-6 md:p-8 flex flex-col">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-[16px] font-black text-slate-800 font-poppins flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center text-cyan-500 text-lg border border-cyan-100"><i class="fas fa-chart-area"></i></div>
                    Kurva Pertumbuhan Klinis
                </h3>
                <span class="text-[10px] font-black text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 uppercase tracking-widest shadow-sm">7 Sesi Terakhir</span>
            </div>
            
            @if($chartData->count() > 1)
                <div class="h-[320px] relative w-full flex-1">
                    <canvas id="emrChart"></canvas>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-slate-400 bg-slate-50/50 rounded-[20px] border-2 border-dashed border-slate-200 min-h-[300px]">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm border border-slate-100">
                        <i class="fas fa-chart-line text-2xl text-slate-300"></i>
                    </div>
                    <p class="text-[14px] font-black text-slate-600 font-poppins">Grafik Kurang Data</p>
                    <p class="text-[12px] font-medium mt-1">Sistem butuh minimal 2 rekam medis untuk merender kurva.</p>
                </div>
            @endif
        </div>

        {{-- ARSIP IMUNISASI KANAN --}}
        <div class="lg:col-span-4 nexus-card p-6 md:p-8 flex flex-col">
            <h3 class="text-[16px] font-black text-slate-800 font-poppins mb-6 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-500 text-lg border border-teal-100"><i class="fas fa-shield-virus"></i></div>
                Arsip Imunisasi
            </h3>
            
            <div class="space-y-3 flex-1 overflow-y-auto pr-2 custom-scrollbar max-h-[350px]">
                @forelse($riwayatImunisasi as $imu)
                <div class="p-4 bg-slate-50 rounded-[16px] border border-slate-100 flex justify-between items-center group hover:bg-white hover:border-teal-300 hover:shadow-md transition-all duration-300 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-teal-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center gap-4 pl-2">
                        <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-teal-500 shrink-0 shadow-sm group-hover:scale-110 transition-transform"><i class="fas fa-vial text-xs"></i></div>
                        <div>
                            <p class="text-[13px] font-black text-slate-800 tracking-tight font-poppins">{{ $imu->vaksin }}</p>
                            <p class="text-[11px] font-bold text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($imu->tanggal_imunisasi)->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                    <span class="text-[9px] font-black px-2.5 py-1.5 bg-white border border-slate-200 text-teal-600 rounded-lg uppercase tracking-widest shadow-sm shrink-0">DOSIS: {{ $imu->dosis ?? '-' }}</span>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center py-10">
                    <i class="fas fa-syringe text-4xl mb-4 text-slate-200"></i>
                    <p class="text-[13px] font-black text-slate-600">Belum Ada Vaksinasi</p>
                    <p class="text-[11px] font-medium text-slate-400 mt-1">Log imunisasi warga masih kosong.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- =================================================================
         4. TIMELINE PEMERIKSAAN MEDIS SEAMLESS
         ================================================================= --}}
    <div class="nexus-card">
        <div class="p-6 md:p-8 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-[18px] font-black text-slate-800 font-poppins flex items-center gap-3">
                <i class="fas fa-history text-cyan-500"></i> Log Pemeriksaan Klinis (EMR)
            </h3>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">Diurutkan Terbaru</span>
        </div>
        
        <div class="p-6 md:p-8 bg-slate-50/50">
            <div class="flex flex-col gap-4">
                @forelse($riwayatMedis as $med)
                <div class="nexus-timeline-row p-5 md:p-6 flex flex-col lg:flex-row lg:items-center w-full group relative overflow-hidden gap-5 lg:gap-0">
                    
                    {{-- Indikator Kiri --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-slate-200 group-hover:bg-cyan-500 transition-colors duration-300"></div>

                    {{-- Tanggal & ID --}}
                    <div class="w-full lg:w-[22%] shrink-0 pl-4">
                        <p class="text-[14px] font-black text-slate-800 font-poppins"><i class="far fa-calendar-alt text-cyan-500 mr-1.5"></i> {{ \Carbon\Carbon::parse($med->created_at)->translatedFormat('d F Y') }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400 font-mono bg-slate-100 px-2 py-0.5 rounded">ID: #{{ $med->id }}</span>
                            <span class="text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100 px-2 py-0.5 rounded">{{ $med->status_gizi ?? 'Normal' }}</span>
                        </div>
                    </div>

                    <div class="h-px w-full bg-slate-100 block lg:hidden"></div>

                    {{-- Antropometri Grid --}}
                    <div class="w-full lg:w-[28%] grid grid-cols-3 gap-3 lg:border-l lg:border-slate-100 lg:pl-6">
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 text-center shadow-sm">
                            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Berat</span>
                            <span class="text-[14px] font-black text-slate-700">{{ $med->berat_badan ?? '-' }}<span class="text-[10px] ml-0.5 text-slate-400">kg</span></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 text-center shadow-sm">
                            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Tinggi</span>
                            <span class="text-[14px] font-black text-slate-700">{{ $med->tinggi_badan ?? '-' }}<span class="text-[10px] ml-0.5 text-slate-400">cm</span></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 text-center shadow-sm">
                            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Tensi/LK</span>
                            <span class="text-[14px] font-black text-slate-700">{{ $med->tekanan_darah ?? ($med->lingkar_kepala ?? '-') }}</span>
                        </div>
                    </div>

                    <div class="h-px w-full bg-slate-100 block lg:hidden"></div>

                    {{-- Diagnosa & Keluhan --}}
                    <div class="w-full lg:w-[30%] flex flex-col gap-1 lg:border-l lg:border-slate-100 lg:pl-6 pr-4">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Diagnosa & Tindakan</span>
                        <p class="text-[13px] font-bold text-slate-800 line-clamp-2 leading-relaxed" title="{{ $med->diagnosa ?: 'Tidak ada keluhan klinis' }}">
                            {{ $med->diagnosa ?: 'Tidak ada keluhan klinis' }}
                        </p>
                        <p class="text-[11px] font-medium text-slate-500 line-clamp-1 mt-0.5">
                            <i class="fas fa-stethoscope text-slate-300 mr-1"></i> {{ $med->tindakan ?: 'Pemeriksaan antropometri rutin.' }}
                        </p>
                    </div>

                    {{-- Verifikator --}}
                    <div class="w-full lg:w-[20%] flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-center shrink-0 lg:border-l lg:border-slate-100 lg:pl-6 pr-4">
                        <div class="flex items-center gap-2 lg:hidden">
                            <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-[10px] border border-indigo-100"><i class="fas fa-user-nurse"></i></div>
                            <span class="text-[11px] font-bold text-slate-500">Petugas Medis</span>
                        </div>
                        <div class="text-right flex flex-col items-end">
                            <span class="text-[13px] font-black text-slate-800">{{ Str::words($med->verifikator->name ?? 'Bidan Desa', 2, '') }}</span>
                            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mt-1 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 flex items-center gap-1.5 shadow-sm">
                                <i class="fas fa-shield-alt text-emerald-500"></i> Verified EMR
                            </span>
                        </div>
                    </div>

                </div>
                @empty
                <div class="py-16 text-center bg-white rounded-[24px] border border-slate-100 shadow-sm flex flex-col items-center justify-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-5 border border-slate-100">
                        <i class="fas fa-folder-open text-3xl text-slate-300"></i>
                    </div>
                    <h3 class="text-[16px] font-black text-slate-800 font-poppins mb-1">Belum Ada Rekam Medis</h3>
                    <p class="text-[13px] font-medium text-slate-500">Pasien ini belum memiliki riwayat pemeriksaan klinis yang sah di sistem.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rawData = {!! json_encode($chartData ?? []) !!};
    
    // PERBAIKAN GRAFIK: Reverse data agar kronologis dari kiri ke kanan (Kuno -> Terbaru)
    if(rawData && rawData.length > 1) {
        const chartData = [...rawData].reverse(); 
        
        const ctx = document.getElementById('emrChart').getContext('2d');
        
        const labels = chartData.map(item => {
            const date = new Date(item.created_at || item.tanggal_periksa);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        });
        
        const bbData = chartData.map(item => parseFloat(item.berat_badan) || 0);
        const tbData = chartData.map(item => parseFloat(item.tinggi_badan) || 0);

        // Efek Gradasi Mewah
        const gradientBB = ctx.createLinearGradient(0, 0, 0, 300);
        gradientBB.addColorStop(0, 'rgba(6, 182, 212, 0.4)');
        gradientBB.addColorStop(1, 'rgba(6, 182, 212, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Berat Badan (kg)',
                        data: bbData,
                        borderColor: '#06b6d4',
                        backgroundColor: gradientBB,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#06b6d4',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Tinggi Badan (cm)',
                        data: tbData,
                        borderColor: '#94a3b8',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#94a3b8'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { 
                            usePointStyle: true,
                            boxWidth: 8, 
                            padding: 20, 
                            font: { size: 11, weight: '700', family: "'Poppins', sans-serif" },
                            color: '#64748b'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 13, family: "'Poppins', sans-serif", weight: 'bold' },
                        bodyFont: { size: 12, family: "'Poppins', sans-serif" },
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: true,
                        boxPadding: 6
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: false,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        border: { display: false },
                        ticks: { font: { size: 11, family: "'Poppins', sans-serif" }, color: '#94a3b8', padding: 10 }
                    },
                    x: { 
                        grid: { display: false, drawBorder: false },
                        border: { display: false },
                        ticks: { font: { size: 11, family: "'Poppins', sans-serif" }, color: '#94a3b8', padding: 10 }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection