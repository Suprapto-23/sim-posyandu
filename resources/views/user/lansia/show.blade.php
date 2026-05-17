@extends('layouts.user')

@php
    $dataLansia = $lansia ?? $data ?? null;
@endphp

@section('title', 'Buku Kesehatan Lansia - ' . $dataLansia->nama_lengkap)

@section('content')
{{-- EKSTRAKSI DATA GRAFIK & AUTO-SKRINING PTM (Blade Level AI) --}}
@php
    $riwayatAsc = isset($riwayat) ? $riwayat->reverse()->values() : collect();
    $grafikLabels = $riwayatAsc->map(fn($item) => \Carbon\Carbon::parse($item->tanggal_kunjungan)->format('d M y'))->toArray();
    
    $grafikBB = $riwayatAsc->map(fn($item) => (float)($item->pemeriksaan->berat_badan ?? 0) ?: null)->toArray();
    $grafikGula = $riwayatAsc->map(fn($item) => (float)($item->pemeriksaan->gula_darah ?? 0) ?: null)->toArray();

    // AI KALKULATOR SKRINING PTM (Otomatis menganalisis tensi & gula darah kunjungan terakhir)
    $kunjunganTerakhir = $riwayatAsc->last();
    $pemTerakhir = $kunjunganTerakhir ? $kunjunganTerakhir->pemeriksaan : null;
    
    $statusPTM = 'Menunggu Data Skrining';
    $pesanPTM = 'Lakukan cek tensi dan gula darah di Posyandu terdekat.';
    $rekomendasiPTM = '-';
    $warnaBg = 'bg-slate-50 border-slate-100';
    $warnaTeks = 'text-slate-500';
    $ikon = 'fa-robot text-slate-300';

    if ($pemTerakhir) {
        // Parsing Tensi (Ambil angka Sistolik di depan '/')
        $tensiStr = $pemTerakhir->tekanan_darah ?? '0/0';
        $sistolik = (int) explode('/', $tensiStr)[0];
        $gulaDarah = (float) ($pemTerakhir->gula_darah ?? 0);

        if ($sistolik >= 140 && $gulaDarah >= 200) {
            $statusPTM = 'Risiko Tinggi (Hipertensi & Diabetes)';
            $pesanPTM = 'Tensi (' . $tensiStr . ') dan Gula Darah (' . $gulaDarah . ') di atas batas normal.';
            $rekomendasiPTM = 'Segera rujuk ke Puskesmas. Jaga pola makan rendah garam & gula.';
            $warnaBg = 'bg-rose-50 border-rose-200';
            $warnaTeks = 'text-rose-700';
            $ikon = 'fa-truck-medical text-rose-500';
        } elseif ($sistolik >= 140) {
            $statusPTM = 'Indikasi Hipertensi';
            $pesanPTM = 'Tekanan darah (' . $tensiStr . ') cukup tinggi.';
            $rekomendasiPTM = 'Kurangi makanan asin/bergaram, hindari stres, dan perbanyak istirahat.';
            $warnaBg = 'bg-amber-50 border-amber-200';
            $warnaTeks = 'text-amber-700';
            $ikon = 'fa-heart-pulse text-amber-500 animate-pulse';
        } elseif ($gulaDarah >= 200) {
            $statusPTM = 'Indikasi Diabetes';
            $pesanPTM = 'Kadar gula darah sewaktu (' . $gulaDarah . ' mg/dL) melebih batas normal.';
            $rekomendasiPTM = 'Kurangi konsumsi karbohidrat berlebih dan gula pasir.';
            $warnaBg = 'bg-amber-50 border-amber-200';
            $warnaTeks = 'text-amber-700';
            $ikon = 'fa-vial text-amber-500';
        } elseif ($sistolik > 0 || $gulaDarah > 0) {
            $statusPTM = 'Kondisi Terkontrol (Normal)';
            $pesanPTM = 'Tekanan darah dan gula darah dalam batas wajar untuk lansia.';
            $rekomendasiPTM = 'Pertahankan aktivitas fisik ringan dan senam lansia.';
            $warnaBg = 'bg-emerald-50 border-emerald-200';
            $warnaTeks = 'text-emerald-700';
            $ikon = 'fa-check-circle text-emerald-500';
        }
    }
@endphp

<div class="bg-[#f8fafc] min-h-screen pb-12 font-poppins w-full">
    
    {{-- HEADER --}}
    <div class="pt-6 pb-4 px-4 md:px-8 max-w-7xl mx-auto flex justify-between items-center shrink-0">
        <a href="{{ route('user.monitoring.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-amber-600 transition-colors">
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm hover:border-amber-200 hover:bg-amber-50 transition-all">
                <i class="fas fa-arrow-left text-xs"></i>
            </div>
            Kembali ke Dasbor
        </a>
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
            <span class="text-[10px] font-black tracking-widest uppercase text-slate-500">Posyandu Lansia (PTM)</span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-stretch w-full">
            
            {{-- KOLOM KIRI: PROFIL & ANALISIS --}}
            <div class="lg:col-span-4 h-full flex flex-col">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.03)] overflow-hidden flex flex-col h-full relative">
                    
                    <div class="h-28 bg-gradient-to-br from-amber-500 to-orange-400 relative shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    </div>
                    
                    <div class="relative px-6 pb-8 flex-1 flex flex-col justify-between">
                        <div class="w-20 h-20 rounded-[1.8rem] bg-white p-1.5 absolute -top-10 shadow-lg shrink-0">
                            <div class="w-full h-full rounded-[1.4rem] bg-amber-50 text-amber-600 flex items-center justify-center text-3xl font-black">
                                {{ strtoupper(substr($dataLansia->nama_lengkap, 0, 1)) }}
                            </div>
                        </div>
                        
                        <div class="pt-14 shrink-0">
                            <h1 class="text-xl md:text-2xl font-black text-slate-800 break-words leading-tight">{{ $dataLansia->nama_lengkap }}</h1>
                            <p class="text-[11px] font-black text-amber-600 uppercase tracking-wider mt-1.5">
                                <i class="fas fa-id-card mr-1"></i> NIK: {{ $dataLansia->nik ?? '-' }}
                            </p>
                        </div>

                        <div class="flex-1 flex flex-col mt-6 gap-5">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Usia</p>
                                    <p class="text-sm font-bold text-slate-800">{{ \Carbon\Carbon::parse($dataLansia->tanggal_lahir)->age }} Tahun</p>
                                </div>
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Gol. Darah</p>
                                    <p class="text-sm font-bold text-rose-500">
                                        <i class="fas fa-droplet mr-1"></i> {{ $dataLansia->golongan_darah ?? '-' }}
                                    </p>
                                </div>
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70 col-span-2">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Riwayat Medis Bawaan</p>
                                    <p class="text-sm font-bold text-slate-800"><i class="fas fa-notes-medical text-amber-400 mr-1.5"></i> {{ $dataLansia->riwayat_penyakit ?? 'Tidak ada catatan kronis' }}</p>
                                </div>
                            </div>

                            {{-- KOTAK ANALISIS CERDAS (HASIL AUTO-SKRINING BLADE) --}}
                            <div class="{{ $warnaBg }} border p-5 rounded-2xl flex flex-col gap-2 mt-auto shrink-0 shadow-inner">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas {{ $ikon }} text-lg"></i>
                                    <h4 class="text-[12px] font-black {{ $warnaTeks }} uppercase tracking-widest">Skrining PTM</h4>
                                </div>
                                <h5 class="text-[14px] font-black text-slate-800">{{ $statusPTM }}</h5>
                                <p class="text-[12px] font-medium text-slate-600 leading-snug">"{{ $pesanPTM }}"</p>
                                <div class="mt-1 pt-2 border-t border-slate-200/50">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Saran Bidan:</p>
                                    <p class="text-[11px] font-medium text-slate-600 italic"><i class="fas fa-lightbulb text-amber-500 mr-1"></i> {{ $rekomendasiPTM }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: WORKSPACE UTAMA --}}
            <div class="lg:col-span-8 h-full flex flex-col gap-6 md:gap-8">
                
                {{-- 1. KARTU GRAFIK DUAL-AXIS (BB & GULA DARAH) --}}
                <div class="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.03)] flex flex-col shrink-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <div>
                            <h2 class="text-lg font-black text-slate-800"><i class="fas fa-heart-pulse text-amber-500 mr-2"></i>Tren Metabolik & Fisik</h2>
                            <p class="text-[11px] font-medium text-slate-500 mt-1">Pemantauan interaksi Berat Badan dengan fluktuasi Gula Darah</p>
                        </div>
                    </div>

                    @if(count($grafikLabels) > 0)
                        <div class="w-full h-[350px] relative bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                            <canvas id="lansiaChart"></canvas>
                        </div>
                    @else
                        <div class="h-[350px] rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center bg-slate-50 text-slate-400">
                            <i class="fas fa-chart-area text-3xl mb-2 opacity-50"></i>
                            <p class="text-xs font-medium">Belum ada data pemeriksaan untuk membentuk kurva.</p>
                        </div>
                    @endif
                </div>

                {{-- 2. TABEL REKAM MEDIS KLINIS LANSIA --}}
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.03)] overflow-hidden flex flex-col flex-1">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-sm font-black text-slate-700 uppercase tracking-wide"><i class="fas fa-notes-medical mr-2 text-amber-500"></i>Riwayat Pemeriksaan PTM</h3>
                    </div>
                    
                    <div class="overflow-x-auto w-full p-2">
                        <table class="w-full text-left border-collapse min-w-[750px]">
                            <thead>
                                <tr class="text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                    <th class="p-4 font-black">Tanggal Skrining</th>
                                    <th class="p-4 font-black">Fisik Dasar</th>
                                    <th class="p-4 font-black">Tanda Vital & Lab Sederhana</th>
                                    <th class="p-4 font-black">Edukasi / Keluhan Utama</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayat as $kunjungan)
                                    @php 
                                        $pem = $kunjungan->pemeriksaan; 
                                        $tensi = (int) explode('/', $pem->tekanan_darah ?? '0')[0];
                                        $isHipertensi = $tensi >= 140;
                                        $isDiabetes = (int)($pem->gula_darah ?? 0) >= 200;
                                    @endphp
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                        <td class="p-4 vertical-top">
                                            <p class="text-[13px] font-black text-slate-800">{{ \Carbon\Carbon::parse($kunjungan->tanggal_kunjungan)->translatedFormat('d M Y') }}</p>
                                        </td>
                                        <td class="p-4">
                                            <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-xs">
                                                <span class="text-slate-500">Berat: <b class="text-slate-800 font-bold">{{ $pem->berat_badan ?? '-' }} kg</b></span>
                                                <span class="text-slate-500">Tinggi: <b class="text-slate-800 font-bold">{{ $pem->tinggi_badan ?? '-' }} cm</b></span>
                                                <span class="text-slate-500 col-span-2 mt-1">Lingkar Perut: <b class="text-slate-800 font-bold">{{ $pem->lingkar_perut ?? '-' }} cm</b></span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col gap-2 text-xs">
                                                <span class="text-slate-500"><i class="fas fa-heart-pulse {{ $isHipertensi ? 'text-rose-500 animate-pulse' : 'text-slate-400' }} mr-1.5 w-3"></i> Tensi: <b class="{{ $isHipertensi ? 'text-rose-600 font-black' : 'text-slate-800 font-bold' }}">{{ $pem->tekanan_darah ?? '-' }} mmHg</b></span>
                                                <span class="text-slate-500"><i class="fas fa-vial {{ $isDiabetes ? 'text-amber-500' : 'text-slate-400' }} mr-1.5 w-3"></i> Gula Darah: <b class="{{ $isDiabetes ? 'text-amber-600 font-black' : 'text-slate-800 font-bold' }}">{{ $pem->gula_darah ?? '-' }} mg/dL</b></span>
                                                <span class="text-slate-500"><i class="fas fa-flask text-slate-400 mr-1.5 w-3"></i> Kolesterol: <b class="text-slate-800 font-bold">{{ $pem->kolesterol ?? '-' }}</b></span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed mb-1">{{ $kunjungan->keluhan ?? 'Tidak ada keluhan kronis.' }}</p>
                                            @if(!empty($pem->edukasi))
                                                <span class="inline-block bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-1 rounded border border-amber-100">
                                                    Edukasi: {{ $pem->edukasi }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-16 text-center text-slate-400">
                                            <i class="fas fa-clipboard-list text-3xl mb-3 text-slate-300"></i>
                                            <p class="text-sm font-bold">Belum ada riwayat rekam medis PTM tercatat.</p>
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = @json($grafikLabels);
    const dataBB = @json($grafikBB);
    const dataGula = @json($grafikGula);
    
    if (labels.length > 0) {
        const ctx = document.getElementById('lansiaChart').getContext('2d');
        
        // Gradient BB
        const gradBB = ctx.createLinearGradient(0, 0, 0, 350);
        gradBB.addColorStop(0, 'rgba(245, 158, 11, 0.3)'); // Amber
        gradBB.addColorStop(1, 'rgba(245, 158, 11, 0)');

        // Gradient Gula
        const gradGula = ctx.createLinearGradient(0, 0, 0, 350);
        gradGula.addColorStop(0, 'rgba(225, 29, 72, 0.3)'); // Rose
        gradGula.addColorStop(1, 'rgba(225, 29, 72, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Berat Badan (kg)',
                        data: dataBB,
                        borderColor: '#f59e0b',
                        backgroundColor: gradBB,
                        borderWidth: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#f59e0b',
                        pointRadius: 5,
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Gula Darah (mg/dL)',
                        data: dataGula,
                        borderColor: '#e11d48',
                        backgroundColor: gradGula,
                        borderWidth: 4,
                        borderDash: [5, 5],
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#e11d48',
                        pointRadius: 5,
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 10, bottom: 10, left: 10, right: 10 } },
                plugins: {
                    legend: { position: 'top', labels: { font: { family: "'Poppins', sans-serif", size: 11, weight: 'bold' } } },
                    tooltip: { 
                        backgroundColor: 'rgba(15, 23, 42, 0.95)', 
                        padding: 14, 
                        cornerRadius: 12,
                        titleFont: { family: "'Poppins', sans-serif", size: 13, weight: '700' }
                    }
                },
                scales: {
                    y: { 
                        type: 'linear', position: 'left',
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        title: { display: true, text: 'Berat Badan (kg)', font: { size: 10, weight: 'bold' } }
                    },
                    y1: { 
                        type: 'linear', position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Gula Darah (mg/dL)', font: { size: 10, weight: 'bold' } }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush