@extends('layouts.user')

@section('title', 'Rekam Medis Remaja - ' . $remaja->nama_lengkap)

@section('content')
{{-- EKSTRAKSI DATA GRAFIK & AUTO-KALKULASI IMT --}}
@php
    $riwayatAsc = $riwayat->reverse()->values();
    $grafikLabels = $riwayatAsc->map(fn($item) => \Carbon\Carbon::parse($item->tanggal_kunjungan)->format('d M y'))->toArray();

    $grafikIMT = $riwayatAsc->map(function($item) {
        $pem = $item->pemeriksaan;
        if (!empty($pem->imt)) return (float) $pem->imt;
        if (!empty($pem->berat_badan) && !empty($pem->tinggi_badan)) {
            $tb_meter = $pem->tinggi_badan / 100;
            return round($pem->berat_badan / ($tb_meter * $tb_meter), 2);
        }
        return null;
    })->toArray();

    // AI KALKULATOR GIZI CADANGAN (Otomatis menganalisis nilai IMT terakhir)
    $imtTerakhir = !empty($grafikIMT) ? end($grafikIMT) : null;
    $statusGizi = 'Belum Ada Data';
    $pesanGizi = 'Lakukan pemeriksaan BB & TB di Posyandu untuk mendapatkan analisis gizi.';
    $rekomendasiGizi = '-';
    $warnaBg = 'bg-slate-50 border-slate-100';
    $warnaTeks = 'text-slate-500';
    $ikon = 'fa-robot text-slate-300';

    if ($imtTerakhir) {
        if ($imtTerakhir < 18.5) {
            $statusGizi = 'Kekurangan Berat Badan (Kurus)';
            $pesanGizi = 'Indeks Massa Tubuh di bawah standar normal. Perlu asupan gizi tambahan.';
            $rekomendasiGizi = 'Perbanyak protein, karbohidrat, dan konsultasikan dengan Bidan.';
            $warnaBg = 'bg-amber-50 border-amber-200';
            $warnaTeks = 'text-amber-700';
            $ikon = 'fa-exclamation-triangle text-amber-500';
        } elseif ($imtTerakhir >= 18.5 && $imtTerakhir <= 25.0) {
            $statusGizi = 'Gizi Normal (Proporsional)';
            $pesanGizi = 'Pertumbuhan dan gizi sangat baik. Anda berada di jalur yang sehat!';
            $rekomendasiGizi = 'Pertahankan pola makan bergizi dan olahraga secara teratur.';
            $warnaBg = 'bg-emerald-50 border-emerald-200';
            $warnaTeks = 'text-emerald-700';
            $ikon = 'fa-check-circle text-emerald-500';
        } else {
            $statusGizi = 'Kelebihan Berat Badan (Gemuk)';
            $pesanGizi = 'Indeks Massa Tubuh berlebih. Waspada risiko obesitas di usia muda.';
            $rekomendasiGizi = 'Kurangi makanan manis/berlemak, dan tingkatkan aktivitas fisik harian.';
            $warnaBg = 'bg-rose-50 border-rose-200';
            $warnaTeks = 'text-rose-700';
            $ikon = 'fa-weight-scale text-rose-500';
        }
    }
@endphp

<div class="bg-[#f8fafc] min-h-screen pb-12 font-poppins w-full">
    
    {{-- HEADER --}}
    <div class="pt-6 pb-4 px-4 md:px-8 max-w-7xl mx-auto flex justify-between items-center shrink-0">
        <a href="{{ route('user.monitoring.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-indigo-600 transition-colors">
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm hover:border-indigo-200 hover:bg-indigo-50 transition-all">
                <i class="fas fa-arrow-left text-xs"></i>
            </div>
            Kembali ke Dasbor
        </a>
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
            <span class="text-[10px] font-black tracking-widest uppercase text-slate-500">Posyandu Remaja</span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-stretch w-full">
            
            {{-- KOLOM KIRI: PROFIL & ANALISIS GIZI --}}
            <div class="lg:col-span-4 h-full flex flex-col">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.03)] overflow-hidden flex flex-col h-full relative">
                    
                    <div class="h-28 bg-gradient-to-br from-indigo-500 to-sky-400 relative shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    </div>
                    
                    <div class="relative px-6 pb-8 flex-1 flex flex-col justify-between">
                        <div class="w-20 h-20 rounded-[1.8rem] bg-white p-1.5 absolute -top-10 shadow-lg shrink-0">
                            <div class="w-full h-full rounded-[1.4rem] bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl font-black">
                                {{ strtoupper(substr($remaja->nama_lengkap, 0, 1)) }}
                            </div>
                        </div>
                        
                        <div class="pt-14 shrink-0">
                            <h1 class="text-xl md:text-2xl font-black text-slate-800 break-words leading-tight">{{ $remaja->nama_lengkap }}</h1>
                            <p class="text-[11px] font-black text-indigo-600 uppercase tracking-wider mt-1.5">
                                <i class="fas fa-id-card mr-1"></i> NIK: {{ $remaja->nik ?? '-' }}
                            </p>
                        </div>

                        <div class="flex-1 flex flex-col mt-6 gap-5">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Usia</p>
                                    <p class="text-sm font-bold text-slate-800">{{ $remaja->usia_tahun }} Tahun</p>
                                </div>
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Gender</p>
                                    <p class="text-sm font-bold {{ $remaja->jenis_kelamin == 'L' ? 'text-sky-600' : 'text-pink-500' }}">
                                        <i class="fas {{ $remaja->jenis_kelamin == 'L' ? 'fa-mars' : 'fa-venus' }} mr-1"></i> {{ $remaja->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </p>
                                </div>
                                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100/70 col-span-2">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Institusi Pendidikan</p>
                                    <p class="text-sm font-bold text-slate-800"><i class="fas fa-school text-slate-400 mr-1.5"></i> {{ $remaja->sekolah ?? 'Belum Diisi' }}</p>
                                </div>
                            </div>

                            {{-- KOTAK ANALISIS CERDAS (FALLBACK BLADE) --}}
                            @php
                                $usia = (int) $remaja->usia_tahun;
                                $isUsiaValid = $usia >= 10 && $usia <= 19;
                            @endphp

                            @if(!$isUsiaValid)
                                <div class="bg-amber-50 border border-amber-200 p-5 rounded-2xl flex flex-col gap-2 mt-auto shrink-0 shadow-inner">
                                    <h4 class="text-[12px] font-black text-amber-800 uppercase tracking-widest"><i class="fas fa-user-clock mr-1"></i> Perbarui Usia</h4>
                                    <p class="text-[12px] font-bold text-slate-700">Usia {{ $usia }} tahun di luar standar Remaja (10-19 Thn). Silakan perbarui profil agar analisis gizi aktif.</p>
                                </div>
                            @else
                                <div class="{{ $warnaBg }} border p-5 rounded-2xl flex flex-col gap-2 mt-auto shrink-0 shadow-inner">
                                    <div class="flex items-center gap-2 mb-1">
                                        <i class="fas {{ $ikon }} text-lg"></i>
                                        <h4 class="text-[12px] font-black {{ $warnaTeks }} uppercase tracking-widest">Status Gizi (IMT)</h4>
                                    </div>
                                    <h5 class="text-[14px] font-black text-slate-800">{{ $statusGizi }}</h5>
                                    <p class="text-[12px] font-medium text-slate-600 leading-snug">"{{ $pesanGizi }}"</p>
                                    <div class="mt-1 pt-2 border-t border-slate-200/50">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Saran Bidan:</p>
                                        <p class="text-[11px] font-medium text-slate-600 italic"><i class="fas fa-lightbulb text-amber-500 mr-1"></i> {{ $rekomendasiGizi }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: WORKSPACE UTAMA --}}
            <div class="lg:col-span-8 h-full flex flex-col gap-6 md:gap-8">
                
                {{-- 1. KARTU GRAFIK IMT --}}
                <div class="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.03)] flex flex-col shrink-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <div>
                            <h2 class="text-lg font-black text-slate-800"><i class="fas fa-chart-line text-indigo-500 mr-2"></i>Tren Indeks Massa Tubuh (IMT)</h2>
                            <p class="text-[11px] font-medium text-slate-500 mt-1">Pemantauan risiko kurang gizi atau obesitas pada masa pubertas</p>
                        </div>
                    </div>

                    @if(count($grafikLabels) > 0)
                        <div class="w-full h-[350px] relative bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                            <canvas id="imtChart"></canvas>
                        </div>
                    @else
                        <div class="h-[350px] rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center bg-slate-50 text-slate-400">
                            <i class="fas fa-chart-line text-3xl mb-2 opacity-50"></i>
                            <p class="text-xs font-medium">Belum ada data pemeriksaan untuk membentuk kurva.</p>
                        </div>
                    @endif
                </div>

                {{-- 2. TABEL REKAM MEDIS KLINIS --}}
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.03)] overflow-hidden flex flex-col flex-1">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-sm font-black text-slate-700 uppercase tracking-wide"><i class="fas fa-stethoscope mr-2 text-indigo-500"></i>Riwayat Pemeriksaan Klinis</h3>
                    </div>
                    
                    <div class="overflow-x-auto w-full p-2">
                        <table class="w-full text-left border-collapse min-w-[750px]">
                            <thead>
                                <tr class="text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                    <th class="p-4 font-black">Tanggal Kunjungan</th>
                                    <th class="p-4 font-black">Antropometri & IMT</th>
                                    <th class="p-4 font-black">Tekanan Darah & HB</th>
                                    <th class="p-4 font-black">Keluhan / Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayat as $kunjungan)
                                    @php 
                                        $pem = $kunjungan->pemeriksaan; 
                                        $hitungImt = isset($pem->berat_badan) && isset($pem->tinggi_badan) ? round($pem->berat_badan / (($pem->tinggi_badan / 100) ** 2), 2) : '-';
                                        $imtAkhir = $pem->imt ?? $hitungImt;
                                    @endphp
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                        <td class="p-4 vertical-top">
                                            <p class="text-[13px] font-black text-slate-800">{{ \Carbon\Carbon::parse($kunjungan->tanggal_kunjungan)->translatedFormat('d M Y') }}</p>
                                        </td>
                                        <td class="p-4">
                                            <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-xs">
                                                <span class="text-slate-500">Berat: <b class="text-slate-800 font-bold">{{ $pem->berat_badan ?? '-' }} kg</b></span>
                                                <span class="text-slate-500">Tinggi: <b class="text-slate-800 font-bold">{{ $pem->tinggi_badan ?? '-' }} cm</b></span>
                                                <span class="text-slate-500 col-span-2 mt-1">IMT: <b class="px-2 py-0.5 rounded bg-indigo-50 border border-indigo-100 text-indigo-700 font-black">{{ $imtAkhir }}</b></span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col gap-2 text-xs">
                                                <span class="text-slate-500"><i class="fas fa-heart-pulse text-rose-400 mr-1.5 w-3"></i> Tensi: <b class="text-slate-800 font-bold">{{ $pem->tekanan_darah ?? '-' }} mmHg</b></span>
                                                <span class="text-slate-500"><i class="fas fa-droplet text-rose-500 mr-1.5 w-3"></i> HB: <b class="text-slate-800 font-bold">{{ $pem->hb ?? '-' }} g/dL</b></span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <p class="text-[11px] text-slate-600 line-clamp-3 leading-relaxed">{{ $kunjungan->keluhan ?? 'Tidak ada catatan keluhan spesifik.' }}</p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-16 text-center text-slate-400">
                                            <i class="fas fa-clipboard-list text-3xl mb-3 text-slate-300"></i>
                                            <p class="text-sm font-bold">Belum ada riwayat pemeriksaan klinis tercatat.</p>
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
    const dataIMT = @json($grafikIMT);
    
    if (labels.length > 0) {
        const ctx = document.getElementById('imtChart').getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 350);
        grad.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
        grad.addColorStop(1, 'rgba(99, 102, 241, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nilai IMT',
                    data: dataIMT,
                    borderColor: '#6366f1',
                    backgroundColor: grad,
                    borderWidth: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 10, bottom: 10, left: 10, right: 10 } },
                plugins: {
                    legend: { display: false },
                    tooltip: { 
                        backgroundColor: 'rgba(15, 23, 42, 0.95)', 
                        padding: 14, 
                        cornerRadius: 12,
                        titleFont: { family: "'Poppins', sans-serif", size: 13, weight: '700' },
                        bodyFont: { family: "'Poppins', sans-serif", size: 12 }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: false, 
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        title: { display: true, text: 'Indeks Massa Tubuh (IMT)', font: { family: "'Poppins', sans-serif", size: 11, weight: 'bold' } },
                        ticks: { font: { family: "'Poppins', sans-serif", size: 11 }, color: '#94a3b8', padding: 8 }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { family: "'Poppins', sans-serif", size: 11, weight: '600' }, color: '#94a3b8', padding: 8 }
                    }
                }
            }
        });
    }
});
</script>
@endpush