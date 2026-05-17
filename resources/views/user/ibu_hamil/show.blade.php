@extends('layouts.user')

{{-- Toleransi penamaan variabel dari Controller (bisa $ibuHamil atau $bumil) --}}
@php
    $dataBumil = $ibuHamil ?? $bumil ?? $data ?? null;
@endphp

@section('title', 'Buku KIA Maternal - ' . $dataBumil->nama_lengkap)

@section('content')
{{-- EKSTRAKSI DATA GRAFIK ANC (ANTENATAL CARE) DARI KOLEKSI RIWAYAT --}}
@php
    $riwayatAsc = isset($riwayat) ? $riwayat->reverse()->values() : collect();
    $grafikLabels = $riwayatAsc->map(fn($item) => \Carbon\Carbon::parse($item->tanggal_kunjungan)->format('d M y'))->toArray();
    
    // Grafik 1: Kenaikan Berat Badan Ibu (Indikator Gizi Maternal)
    $grafikBB = $riwayatAsc->map(fn($item) => $item->pemeriksaan->berat_badan ?? null)->toArray();
    
    // Grafik 2: Tinggi Fundus Uteri (Indikator Perkembangan Fisik Janin)
    $grafikTFU = $riwayatAsc->map(fn($item) => $item->pemeriksaan->tinggi_fundus ?? null)->toArray();
@endphp

<div class="bg-[#f8fafc] min-h-screen pb-12 font-poppins w-full">
    
    {{-- HEADER & BACK BUTTON --}}
    <div class="pt-6 pb-4 px-4 md:px-8 max-w-7xl mx-auto flex justify-between items-center animate-slide-up shrink-0">
        <a href="{{ route('user.monitoring.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-rose-600 transition-colors group">
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center group-hover:border-rose-300 group-hover:bg-rose-50 transition-all">
                <i class="fas fa-arrow-left text-xs"></i>
            </div>
            Kembali ke Dasbor
        </a>
        
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
            </span>
            <span class="text-[10px] font-black tracking-widest uppercase text-slate-500">Posyandu Maternal (ANC)</span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8">
        {{-- KUNCI UTAMA PRESISI: items-stretch mengunci tinggi kolom agar simetris --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-stretch w-full">
            
            {{-- KOLOM KIRI: PROFIL MATERNAL & STATUS KEHAMILAN --}}
            <div class="lg:col-span-4 h-full flex flex-col animate-slide-up-1">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_12px_40px_-15px_rgba(0,0,0,0.04)] overflow-hidden relative flex flex-col h-full w-full">
                    
                    {{-- Banner Atas --}}
                    <div class="h-32 bg-gradient-to-br from-rose-500 to-fuchsia-400 relative shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    </div>
                    
                    {{-- Konten Utama Profil --}}
                    <div class="relative px-6 pb-8 flex-1 flex flex-col justify-between">
                        {{-- Avatar --}}
                        <div class="w-20 h-20 md:w-24 md:h-24 rounded-[1.8rem] bg-white p-1.5 absolute -top-10 md:-top-12 shadow-xl shrink-0">
                            <div class="w-full h-full rounded-[1.4rem] bg-gradient-to-tr from-rose-50 to-fuchsia-50 text-rose-600 flex items-center justify-center text-3xl md:text-4xl font-black border border-rose-100">
                                {{ strtoupper(substr($dataBumil->nama_lengkap, 0, 1)) }}
                            </div>
                        </div>
                        
                        {{-- Identitas Biodata --}}
                        <div class="pt-14 md:pt-16 shrink-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h1 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight leading-tight break-words">{{ $dataBumil->nama_lengkap }}</h1>
                                    <p class="text-[11px] font-black text-rose-600 uppercase tracking-widest mt-1.5">
                                        <i class="fas fa-id-card mr-1"></i> NIK: {{ $dataBumil->nik ?? '-' }}
                                    </p>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-rose-50 border border-rose-100 text-[10px] font-black text-rose-600 uppercase tracking-wider shrink-0">
                                    Bumil
                                </span>
                            </div>
                        </div>

                        {{-- Metadata Grid (Fleksibel mengisi ruang) --}}
                        <div class="flex-1 flex flex-col mt-6 gap-6">
                            <div class="grid grid-cols-2 gap-3.5">
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Usia Ibu</p>
                                    <p class="text-sm font-bold text-slate-800">{{ \Carbon\Carbon::parse($dataBumil->tanggal_lahir)->age }} Tahun</p>
                                </div>
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Gol. Darah</p>
                                    <p class="text-sm font-bold text-rose-500"><i class="fas fa-droplet mr-1"></i> {{ $dataBumil->golongan_darah ?? '-' }}</p>
                                </div>
                                <div class="bg-rose-50/50 p-3.5 rounded-2xl border border-rose-100/50 col-span-2">
                                    <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest mb-1.5">Hari Perkiraan Lahir (HPL)</p>
                                    <p class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <i class="fas fa-calendar-check text-rose-400"></i>
                                        {{ $dataBumil->hpl ? \Carbon\Carbon::parse($dataBumil->hpl)->translatedFormat('l, d F Y') : 'Belum Ditentukan Bidan' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Info Suami/Penanggung Jawab (Dipaku di bawah dengan mt-auto) --}}
                            <div class="bg-slate-50/50 border border-slate-100 p-4 rounded-2xl flex items-center gap-4 mt-auto shrink-0">
                                <div class="w-10 h-10 rounded-xl bg-white text-slate-400 flex items-center justify-center shadow-sm border border-slate-100 shrink-0">
                                    <i class="fas fa-male text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Nama Suami</p>
                                    <p class="text-sm font-bold text-slate-700 mt-1.5 truncate">{{ $dataBumil->nama_suami ?? 'Tidak Terdata' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: WORKSPACE UTAMA (GRAFIK MAKSIMAL & TABEL KOMPLEKS) --}}
            <div class="lg:col-span-8 h-full flex flex-col gap-6 md:gap-8 animate-slide-up-2">
                
                {{-- 1. KARTU GRAFIK KESEHATAN MATERNAL (DUAL AXIS) --}}
                <div class="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-100 shadow-[0_12px_40px_-15px_rgba(0,0,0,0.03)] flex flex-col shrink-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4 shrink-0">
                        <div>
                            <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                                <i class="fas fa-person-pregnant text-rose-500"></i> Kurva Pemantauan Kehamilan
                            </h2>
                            <p class="text-[11px] font-medium text-slate-500 mt-0.5">Korelasi Kenaikan Berat Badan Ibu & Tinggi Fundus Uteri (Janin)</p>
                        </div>
                        <button onclick="refreshChartData()" id="btnRefreshChart" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-200 transition-all outline-none shrink-0">
                            <i class="fas fa-sync-alt transition-transform" id="iconSync"></i> Perbarui Grafik
                        </button>
                    </div>

                    @if(count($grafikLabels) > 0)
                        <div class="w-full h-[400px] relative bg-slate-50/20 p-4 rounded-[2rem] border border-slate-100/50">
                            <canvas id="bumilChart"></canvas>
                        </div>
                    @else
                        <div class="h-64 rounded-[2rem] border-2 border-dashed border-slate-200 flex flex-col items-center justify-center bg-slate-50 text-slate-400">
                            <i class="fas fa-stethoscope text-3xl mb-2 opacity-50"></i>
                            <p class="text-xs font-medium">Belum ada rekam medis ANC untuk divisualisasikan.</p>
                        </div>
                    @endif
                </div>

                {{-- 2. KARTU TABEL REKAM MEDIS (ANTENATAL CARE) --}}
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_12px_40px_-15px_rgba(0,0,0,0.03)] overflow-hidden flex flex-col flex-1" x-data="{ tab: 'anc' }">
                    
                    <div class="flex border-b border-slate-100 shrink-0">
                        <button @click="tab = 'anc'" :class="tab === 'anc' ? 'text-rose-600 border-rose-500 bg-rose-50/20' : 'text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-50'" class="flex-1 py-4 border-b-2 text-xs md:text-sm font-black tracking-wide transition-all uppercase outline-none">
                            <i class="fas fa-notes-medical mr-1.5"></i> Riwayat ANC (Pemeriksaan Kehamilan)
                        </button>
                    </div>

                    <div x-show="tab === 'anc'" x-transition:enter="transition ease-out duration-200" class="p-0 flex-1 flex flex-col">
                        <div class="overflow-x-auto w-full flex-1">
                            <table class="w-full text-left border-collapse min-w-[850px]">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400">
                                        <th class="p-4 font-black">Tanggal</th>
                                        <th class="p-4 font-black">Fisik Ibu (Maternal)</th>
                                        <th class="p-4 font-black">Kondisi Janin (Fetal)</th>
                                        <th class="p-4 font-black">Tindakan / Keluhan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(isset($riwayat) ? $riwayat : [] as $kunjungan)
                                        @php $pem = $kunjungan->pemeriksaan; @endphp
                                        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                            <td class="p-4 vertical-top">
                                                <p class="text-[13px] font-black text-slate-800">{{ \Carbon\Carbon::parse($kunjungan->tanggal_kunjungan)->translatedFormat('d M Y') }}</p>
                                                <p class="text-[10px] text-rose-600 font-bold mt-1 uppercase tracking-wider bg-rose-50 inline-block px-1.5 py-0.5 rounded">Usia Kehamilan: {{ $pem->usia_kandungan ?? '-' }} Mgg</p>
                                            </td>
                                            <td class="p-4">
                                                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                                                    <span class="text-slate-500">Berat: <b class="text-slate-800 font-bold">{{ $pem->berat_badan ?? '-' }} kg</b></span>
                                                    <span class="text-slate-500">Tinggi: <b class="text-slate-800 font-bold">{{ $pem->tinggi_badan ?? '-' }} cm</b></span>
                                                    <span class="text-slate-500">LILA: <b class="text-slate-800 font-bold">{{ $pem->lila ?? '-' }} cm</b></span>
                                                    <span class="text-slate-500">Tensi: <b class="text-slate-800 font-bold {{ (int)$pem->tekanan_darah > 130 ? 'text-rose-600' : '' }}">{{ $pem->tekanan_darah ?? '-' }}</b></span>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="flex flex-col gap-1 text-[10px] uppercase font-black">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-slate-400 font-bold tracking-wider w-16">TFU:</span>
                                                        <span class="text-slate-700">{{ $pem->tinggi_fundus ?? '-' }} cm</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 mt-1">
                                                        <span class="text-slate-400 font-bold tracking-wider w-16">DJJ Janin:</span>
                                                        <span class="text-slate-700"><i class="fas fa-heart pulse text-rose-400 mr-1"></i> {{ $pem->djj ?? '-' }} bpm</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <p class="text-[11px] text-slate-600 line-clamp-3 leading-relaxed">{{ $kunjungan->keluhan ?? 'Pemantauan rutin kehamilan tanpa keluhan spesifik.' }}</p>
                                                @if(isset($pem->tindakan) || isset($pem->resep))
                                                    <div class="mt-2 text-[10px] bg-slate-100 text-slate-500 p-1.5 rounded line-clamp-1 italic">Tindakan: {{ $pem->tindakan ?? $pem->resep ?? 'Pemberian TTD/Vitamin' }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-16 text-center">
                                                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-3">
                                                    <i class="fas fa-folder-open"></i>
                                                </div>
                                                <p class="text-sm font-bold text-slate-500">Belum Ada Riwayat ANC Terdata</p>
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
    .pulse { animation: pulseHeart 1.5s infinite; }
    @keyframes pulseHeart { 0% { transform: scale(1); } 50% { transform: scale(1.15); } 100% { transform: scale(1); } }
    [x-cloak] { display: none !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let bumilChartInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    initBumilChart();
});

function initBumilChart() {
    const labels = @json($grafikLabels);
    const dataBB = @json($grafikBB);
    const dataTFU = @json($grafikTFU);
    
    if (labels.length > 0) {
        const ctx = document.getElementById('bumilChart').getContext('2d');
        
        // Gradient Berat Badan
        const gradBB = ctx.createLinearGradient(0, 0, 0, 350);
        gradBB.addColorStop(0, 'rgba(244, 63, 94, 0.35)'); // Rose
        gradBB.addColorStop(1, 'rgba(244, 63, 94, 0)');

        // Gradient Tinggi Fundus (TFU)
        const gradTFU = ctx.createLinearGradient(0, 0, 0, 350);
        gradTFU.addColorStop(0, 'rgba(16, 185, 129, 0.35)'); // Emerald
        gradTFU.addColorStop(1, 'rgba(16, 185, 129, 0)');

        bumilChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Berat Badan Ibu (kg)',
                        data: dataBB,
                        borderColor: '#f43f5e',
                        backgroundColor: gradBB,
                        borderWidth: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#f43f5e',
                        pointRadius: 5,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Tinggi Fundus Janin (cm)',
                        data: dataTFU,
                        borderColor: '#10b981',
                        backgroundColor: gradTFU,
                        borderWidth: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#10b981',
                        pointRadius: 5,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, font: { family: "'Poppins', sans-serif", size: 12, weight: '700' } } },
                    tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.95)', padding: 14, cornerRadius: 16, titleFont: { size: 13 }, bodyFont: { size: 12 } }
                },
                scales: {
                    y: {
                        type: 'linear', display: true, position: 'left',
                        title: { display: true, text: 'Berat Badan Ibu (kg)', font: { size: 10, weight: 'bold' } },
                        grid: { borderDash: [5, 5], color: '#f1f5f9' }
                    },
                    y1: {
                        type: 'linear', display: true, position: 'right',
                        title: { display: true, text: 'Tinggi Fundus Uteri (cm)', font: { size: 10, weight: 'bold' } },
                        grid: { drawOnChartArea: false }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
}

function refreshChartData() {
    const btn = document.getElementById('btnRefreshChart');
    const icon = document.getElementById('iconSync');
    btn.disabled = true;
    btn.classList.add('opacity-60');
    icon.classList.add('fa-spin');
    setTimeout(() => { window.location.reload(); }, 600);
}
</script>
@endpush