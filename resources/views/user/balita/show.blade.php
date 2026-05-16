@extends('layouts.user')

@section('title', 'Buku KIA Digital - ' . $balita->nama_lengkap)

@section('content')
<div class="bg-[#f8fafc] min-h-screen pb-12 font-poppins w-full">
    
    {{-- HEADER & BACK BUTTON --}}
    <div class="pt-6 pb-4 px-4 md:px-8 max-w-7xl mx-auto flex justify-between items-center animate-slide-up shrink-0">
        <a href="{{ route('user.monitoring.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-teal-600 transition-colors group">
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center group-hover:border-teal-300 group-hover:bg-teal-50 transition-all">
                <i class="fas fa-arrow-left text-xs"></i>
            </div>
            Kembali ke Dasbor
        </a>
        
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <span class="text-[10px] font-black tracking-widest uppercase text-slate-500">KMS Terintegrasi</span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8">
        {{-- KUNCI UTAMA PRESISI: items-stretch memaksa tinggi kolom kiri dan kanan mengikat satu sama lain --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-stretch w-full">
            
            {{-- KOLOM KIRI: PROFIL BALITA (KTP DIGITAL) --}}
            <div class="lg:col-span-4 h-full flex flex-col animate-slide-up-1">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_12px_40px_-15px_rgba(0,0,0,0.04)] overflow-hidden relative flex flex-col h-full w-full">
                    
                    {{-- Banner Atas --}}
                    <div class="h-32 bg-gradient-to-br from-teal-500 to-emerald-400 relative shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    </div>
                    
                    {{-- Konten Utama Profil --}}
                    <div class="relative px-6 pb-8 flex-1 flex flex-col justify-between">
                        {{-- Avatar Inisial --}}
                        <div class="w-20 h-20 md:w-24 md:h-24 rounded-[1.8rem] bg-white p-1.5 absolute -top-10 md:-top-12 shadow-xl shrink-0">
                            <div class="w-full h-full rounded-[1.4rem] bg-gradient-to-tr from-teal-55 to-emerald-55 text-teal-600 flex items-center justify-center text-3xl md:text-4xl font-black border border-teal-100">
                                {{ strtoupper(substr($balita->nama_lengkap, 0, 1)) }}
                            </div>
                        </div>
                        
                        {{-- Identitas Biodata --}}
                        <div class="pt-14 md:pt-16 shrink-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h1 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight leading-tight break-words">{{ $balita->nama_lengkap }}</h1>
                                    <p class="text-[11px] font-black text-teal-600 uppercase tracking-widest mt-1.5">
                                        <i class="fas fa-tag mr-1"></i> {{ $balita->kode_balita ?? 'B-REGULAR' }}
                                    </p>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-teal-50 border border-teal-100 text-[10px] font-black text-teal-600 uppercase tracking-wider shrink-0">
                                    {{ $balita->kategori_medis ?? 'Balita' }}
                                </span>
                            </div>
                        </div>

                        {{-- Metadata Grid (Mengisi ruang secara proporsional) --}}
                        <div class="flex-1 flex flex-col justify-end mt-6 gap-6">
                            <div class="grid grid-cols-2 gap-3.5">
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Usia Saat Ini</p>
                                    <p class="text-sm font-bold text-slate-800">{{ $usia_tahun }} Thn <span class="text-slate-400 font-medium">{{ $usia_bulan }} Bln</span></p>
                                </div>
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Tanggal Lahir</p>
                                    <p class="text-sm font-bold text-slate-800">{{ \Carbon\Carbon::parse($balita->tanggal_lahir)->translatedFormat('d M Y') }}</p>
                                </div>
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Jenis Kelamin</p>
                                    <p class="text-sm font-bold {{ $balita->jenis_kelamin == 'L' ? 'text-sky-600' : 'text-pink-500' }}">
                                        <i class="fas {{ $balita->jenis_kelamin == 'L' ? 'fa-mars' : 'fa-venus' }} mr-1"></i>
                                        {{ $balita->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </p>
                                </div>
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Gol. Darah</p>
                                    <p class="text-sm font-bold text-rose-500"><i class="fas fa-droplet mr-1"></i> {{ $balita->golongan_darah ?? 'O' }}</p>
                                </div>
                            </div>

                            {{-- Penanggung Jawab / Info Ibu --}}
                            <div class="bg-slate-50/50 border border-slate-100 p-4 rounded-2xl flex items-center gap-4 mt-auto">
                                <div class="w-10 h-10 rounded-xl bg-white text-teal-500 flex items-center justify-center shadow-sm border border-slate-100 shrink-0">
                                    <i class="fas fa-female text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Nama Ibu Kandung</p>
                                    <p class="text-sm font-bold text-slate-700 mt-1.5 truncate">{{ $balita->nama_ibu ?? 'Tidak Terdata' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: WORKSPACE UTAMA (GRAFIK MAKSIMAL & TABEL KOMPLEKS) --}}
            <div class="lg:col-span-8 h-full flex flex-col gap-6 md:gap-8 animate-slide-up-2">
                
                {{-- 1. KARTU GRAFIK PERTUMBUHAN MAKSIMAL (DI-UPGRADE) --}}
                <div class="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-100 shadow-[0_12px_40px_-15px_rgba(0,0,0,0.03)] flex flex-col shrink-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4 shrink-0">
                        <div>
                            <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                                <i class="fas fa-chart-line text-teal-500"></i> Kurva Pertumbuhan KIA (KMS)
                            </h2>
                            <p class="text-[11px] font-medium text-slate-500 mt-0.5">Visualisasi realtime perkembangan fisik balita berdasarkan rentang bulan pemeriksaan</p>
                        </div>
                        
                        <button onclick="refreshKMSData()" id="btnRefreshKMS" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-200 transition-all outline-none shrink-0">
                            <i class="fas fa-sync-alt transition-transform" id="iconSync"></i> Sinkronisasi Grafik
                        </button>
                    </div>

                    @if(!empty($grafikData['labels']) && count($grafikData['labels']) > 0)
                        {{-- REVISI UKURAN: Diubah menjadi h-[420px] md:h-[480px] agar visualisasi kurva luas dan terbaca sempurna --}}
                        <div class="w-full h-[420px] md:h-[480px] relative bg-slate-50/20 p-4 rounded-[2rem] border border-slate-100/50">
                            <canvas id="kmsChart"></canvas>
                        </div>
                    @else
                        <div class="h-64 rounded-[2rem] border-2 border-dashed border-slate-200 flex flex-col items-center justify-center bg-slate-50 text-slate-400">
                            <i class="fas fa-chart-area text-3xl mb-2 opacity-50"></i>
                            <p class="text-xs font-medium">Data grafik antropometri belum tersedia.</p>
                        </div>
                    @endif
                </div>

                {{-- 2. KARTU TABEL REKAM MEDIS & IMUNISASI --}}
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_12px_40px_-15px_rgba(0,0,0,0.03)] overflow-hidden flex flex-col flex-1" x-data="{ tab: 'ukur' }">
                    
                    {{-- Tab Navigation --}}
                    <div class="flex border-b border-slate-100 shrink-0">
                        <button @click="tab = 'ukur'" :class="tab === 'ukur' ? 'text-teal-600 border-teal-500 bg-teal-50/20' : 'text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-50'" class="flex-1 py-4 border-b-2 text-xs md:text-sm font-black tracking-wide transition-all uppercase outline-none">
                            <i class="fas fa-microscope mr-1.5"></i> Riwayat Pengukuran Kompleks
                        </button>
                        <button @click="tab = 'vaksin'" :class="tab === 'vaksin' ? 'text-teal-600 border-teal-500 bg-teal-50/20' : 'text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-50'" class="flex-1 py-4 border-b-2 text-xs md:text-sm font-black tracking-wide transition-all uppercase outline-none">
                            <i class="fas fa-shield-virus mr-1.5"></i> Rekam Imunisasi Berkala
                        </button>
                    </div>

                    {{-- Content Tab 1: Tabel Hasil Pengukuran Kompleks --}}
                    <div x-show="tab === 'ukur'" x-transition:enter="transition ease-out duration-200" class="p-0 flex-1 flex flex-col">
                        <div class="overflow-x-auto w-full flex-1">
                            <table class="w-full text-left border-collapse min-w-[750px]">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400">
                                        <th class="p-4 font-black">Sesi / Tanggal</th>
                                        <th class="p-4 font-black">Anatomi Fisik</th>
                                        <th class="p-4 font-black">Indeks Standar Kemenkes (WHO)</th>
                                        <th class="p-4 font-black">Status Gizi Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($riwayatPemeriksaanDesc as $periksa)
                                        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="p-4">
                                                <p class="text-[13px] font-black text-slate-800">{{ \Carbon\Carbon::parse($periksa->tanggal_periksa)->translatedFormat('d M Y') }}</p>
                                                <p class="text-[10px] text-teal-600 font-bold mt-1 uppercase tracking-wider bg-teal-50 inline-block px-1.5 py-0.5 rounded">Usia: {{ $periksa->usia_saat_periksa ?? '-' }} Bln</p>
                                            </td>
                                            <td class="p-4">
                                                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                                                    <span class="text-slate-500">Berat: <b class="text-slate-800 font-bold">{{ $periksa->berat_badan ?? '-' }} kg</b></span>
                                                    <span class="text-slate-500">Tinggi: <b class="text-slate-800 font-bold">{{ $periksa->tinggi_badan ?? '-' }} cm</b></span>
                                                    <span class="text-slate-500">Kepala: <b class="text-slate-800 font-bold">{{ $periksa->lingkar_kepala ?? '-' }} cm</b></span>
                                                    <span class="text-slate-500">LILA: <b class="text-slate-800 font-bold">{{ $periksa->lila ?? '-' }} cm</b></span>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="flex flex-col gap-1 text-[10px] uppercase font-black">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-slate-400 font-bold tracking-wider w-12">BB/U:</span>
                                                        <span class="text-slate-700">{{ $periksa->status_bbu ?? 'Normal' }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-slate-400 font-bold tracking-wider w-12">TB/U:</span>
                                                        <span class="text-slate-700">{{ $periksa->status_tbu ?? 'Normal' }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-slate-400 font-bold tracking-wider w-12">BB/TB:</span>
                                                        <span class="text-slate-700">{{ $periksa->status_bbtb ?? 'Normal' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                @php
                                                    $gizi = strtolower($periksa->status_gizi ?? '');
                                                    $giziClass = str_contains($gizi, 'baik') || str_contains($gizi, 'normal') ? 'bg-emerald-100 text-emerald-700 border-emerald-200' :
                                                                (str_contains($gizi, 'kurang') ? 'bg-amber-100 text-amber-700 border-amber-200' : 
                                                                (str_contains($gizi, 'buruk') || str_contains($gizi, 'stunting') ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-slate-100 text-slate-600 border-slate-200'));
                                                @endphp
                                                <span class="inline-flex px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $giziClass }}">
                                                    {{ $periksa->status_gizi ?? 'Gizi Baik' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-16 text-center">
                                                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-3">
                                                    <i class="fas fa-clipboard-list"></i>
                                                </div>
                                                <p class="text-sm font-bold text-slate-500">Belum Ada Pengukuran Yang Tervalidasi</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Isi Konten Tab 2: Timeline Imunisasi Berkala --}}
                    <div x-cloak x-show="tab === 'vaksin'" x-transition:enter="transition ease-out duration-200" class="p-6 md:p-8 flex-1 overflow-y-auto max-h-[450px]">
                        <div class="relative w-full">
                            @forelse($riwayatImunisasi as $imun)
                                <div class="mb-6 last:mb-0 relative pl-8 md:pl-10 py-1 group">
                                    <div class="absolute left-[11px] md:left-[15px] top-6 bottom-[-24px] w-[2px] bg-teal-100 group-last:hidden"></div>
                                    <div class="absolute left-0 md:left-1 top-1.5 w-6 h-6 rounded-full bg-teal-500 text-white flex items-center justify-center ring-4 ring-white shadow-sm z-10">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </div>
                                    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm hover:shadow-md hover:border-teal-200 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ \Carbon\Carbon::parse($imun->tanggal_imunisasi)->translatedFormat('l, d F Y') }}</p>
                                            <h4 class="text-[14px] font-black text-teal-600">{{ $imun->jenis_imunisasi ?? $imun->nama_vaksin }}</h4>
                                        </div>
                                        @if($imun->keterangan)
                                        <div class="md:text-right bg-slate-50 p-2.5 rounded-xl border border-slate-100 md:max-w-xs shrink-0">
                                            <p class="text-[11px] font-medium text-slate-500 italic line-clamp-2">"{{ $imun->keterangan }}"</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center">
                                    <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-3">
                                        <i class="fas fa-shield-virus"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-500">Belum Ada Rekam Imunisasi Tersimpan</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .animate-slide-up-1 { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards; }
    .animate-slide-up-2 { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; }
    @keyframes slideUpFade { 
        from { opacity: 0; transform: translateY(25px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    [x-cloak] { display: none !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let kmsChartInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    initChart();
});

function initChart() {
    const chartData = @json($grafikData ?? []);
    
    if (chartData && chartData.labels && chartData.labels.length > 0) {
        const ctx = document.getElementById('kmsChart').getContext('2d');
        
        const gradientBerat = ctx.createLinearGradient(0, 0, 0, 400);
        gradientBerat.addColorStop(0, 'rgba(20, 184, 166, 0.35)'); // Teal
        gradientBerat.addColorStop(1, 'rgba(20, 184, 166, 0)');

        const gradientTinggi = ctx.createLinearGradient(0, 0, 0, 400);
        gradientTinggi.addColorStop(0, 'rgba(14, 165, 233, 0.35)'); // Sky Blue
        gradientTinggi.addColorStop(1, 'rgba(14, 165, 233, 0)');

        if (kmsChartInstance) {
            kmsChartInstance.destroy();
        }

        kmsChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Berat Badan (kg)',
                        data: chartData.berat,
                        borderColor: '#14b8a6',
                        backgroundColor: gradientBerat,
                        borderWidth: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#14b8a6',
                        pointBorderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.35
                    },
                    {
                        label: 'Tinggi Badan (cm)',
                        data: chartData.tinggi,
                        borderColor: '#0ea5e9',
                        backgroundColor: gradientTinggi,
                        borderWidth: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0ea5e9',
                        pointBorderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 10, bottom: 10, left: 5, right: 15 }
                },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            font: { family: "'Poppins', sans-serif", size: 12, weight: '700' },
                            color: '#475569',
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        padding: 14,
                        cornerRadius: 16,
                        titleFont: { family: "'Poppins', sans-serif", size: 13, weight: '800' },
                        bodyFont: { family: "'Poppins', sans-serif", size: 12, weight: '500' },
                        boxPadding: 6
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        ticks: { font: { family: "'Poppins', sans-serif", size: 11, weight: '600' }, color: '#94a3b8', padding: 8 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Poppins', sans-serif", size: 11, weight: '600' }, color: '#94a3b8', padding: 8 }
                    }
                }
            }
        });
    }
}

function refreshKMSData() {
    const btn = document.getElementById('btnRefreshKMS');
    const icon = document.getElementById('iconSync');
    btn.disabled = true;
    btn.classList.add('opacity-60');
    icon.classList.add('fa-spin');
    setTimeout(() => { window.location.reload(); }, 600);
}
</script>
@endpush