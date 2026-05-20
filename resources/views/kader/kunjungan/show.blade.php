@extends('layouts.kader')
@section('title', 'Detail Kunjungan')
@section('page-name', 'Nota Kehadiran')

@push('styles')
<style>
    /* NEXUS ANIMATION SYSTEM */
    .fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .stagger-1 { animation-delay: 0.1s; } .stagger-2 { animation-delay: 0.2s; }
    
    /* NEXUS GLASS CARD */
    .nexus-glass-card {
        background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);
        border: 1px solid #ffffff; border-radius: 32px;
        box-shadow: 0 20px 40px -10px rgba(6, 182, 212, 0.08); overflow: hidden; relative;
    }
    
    /* DETAIL GRID ITEM */
    .info-box {
        background: #ffffff; border: 1px solid #f1f5f9; border-radius: 20px;
        padding: 1.25rem; transition: all 0.3s ease; box-shadow: 0 2px 10px -2px rgba(0,0,0,0.02);
    }
    .info-box:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(6, 182, 212, 0.1); border-color: #cffafe; }

    /* CSS CETAK CERDAS (PRINT) */
    .print-watermark { display: none; }
    @media print {
        body * { visibility: hidden; }
        .nexus-glass-card, .nexus-glass-card * { visibility: visible; }
        .nexus-glass-card { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; background: white !important; filter: none !important; border-radius: 0 !important; }
        .no-print { display: none !important; }
        .info-box { border: 1px solid #cbd5e1 !important; box-shadow: none !important; transform: none !important; break-inside: avoid; }
        .print-watermark { display: block; margin-top: 40px; text-align: center; font-size: 11px; color: #64748b; font-family: monospace; border-top: 1px dashed #cbd5e1; padding-top: 10px; }
    }
</style>
@endpush

@section('content')
<div class="max-w-[900px] mx-auto fade-in-up pb-12 relative z-10">

    {{-- Latar Belakang Abstrak --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-2xl h-96 bg-gradient-to-b from-cyan-50/80 to-transparent rounded-full blur-3xl pointer-events-none z-0 no-print"></div>

    {{-- 1. HEADER NAVIGASI (NO PRINT) --}}
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between gap-5 relative z-10 no-print">
        <div class="flex items-center gap-4 w-full sm:w-auto">
            <a href="{{ route('kader.kunjungan.index') }}" class="w-12 h-12 rounded-[16px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-cyan-500 hover:text-white hover:border-cyan-500 transition-all shadow-sm group shrink-0">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-poppins">Arsip Nota</h1>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Rekam Jejak Kunjungan Valid</span>
                </div>
            </div>
        </div>
        
        <button onclick="window.print()" class="w-full sm:w-auto px-6 py-3.5 bg-white border border-slate-200 text-slate-700 font-black text-[11px] rounded-[16px] hover:bg-slate-800 hover:text-white hover:border-slate-800 shadow-sm transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
            <i class="fas fa-print text-sm"></i> Cetak Bukti Layanan
        </button>
    </div>

    {{-- 2. KARTU DETAIL UTAMA (NEXUS GLASS CARD) --}}
    <div class="nexus-glass-card relative z-10">
        
        {{-- Ornamen Kartu Internal --}}
        <div class="absolute right-0 top-0 w-64 h-64 bg-cyan-500/10 rounded-bl-full pointer-events-none blur-3xl no-print"></div>

        {{-- Banner Atas --}}
        <div class="p-8 md:p-10 border-b border-slate-100 relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left bg-slate-50/30">
            <div class="w-24 h-24 rounded-[24px] bg-gradient-to-br from-cyan-50 to-white border border-cyan-100 text-cyan-500 flex items-center justify-center text-4xl shadow-sm shrink-0 transform -rotate-3">
                <i class="fas fa-hospital-user drop-shadow-sm"></i>
            </div>
            <div class="flex-1">
                @php 
                    $tipe = class_basename($kunjungan->pasien_type); 
                    $badgeBadge = match($tipe) {
                        'Balita'   => 'bg-sky-50 text-sky-700 border-sky-200',
                        'Remaja'   => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'Lansia'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        default    => 'bg-slate-50 text-slate-700 border-slate-200'
                    };
                @endphp
                <div class="inline-flex items-center gap-1.5 px-3 py-1 border {{ $badgeBadge }} text-[9px] font-black uppercase tracking-widest rounded-md mb-3 shadow-sm">
                    <i class="fas fa-tag"></i> {{ match($tipe) {
    'Balita' => 'Balita / Anak',
    'Remaja' => 'Remaja',
    'Lansia' => 'Lansia',
    default => $tipe
} }}
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-slate-800 font-poppins mb-2 tracking-tight">{{ $kunjungan->pasien->nama_lengkap ?? 'Data Terhapus' }}</h2>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                    <span class="text-[11px] font-bold text-slate-500 font-mono bg-white px-2 py-1 rounded-lg border border-slate-200"><i class="fas fa-id-card mr-1 text-slate-400"></i> ID Pasien: {{ $kunjungan->pasien->nik ?? $kunjungan->pasien->kode_balita ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Grid Informasi Rinci --}}
        <div class="p-8 md:p-10 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                
                {{-- KOLOM KIRI --}}
                <div class="space-y-6 stagger-1">
                    
                    {{-- Check-In Card Berbahasa Indonesia --}}
                    <div class="info-box bg-cyan-50/50 border-cyan-100">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[10px] font-black text-cyan-600 uppercase tracking-widest"><i class="fas fa-sign-in-alt mr-1.5"></i> Waktu Kedatangan</p>
                        </div>
                        <p class="text-[16px] font-black text-cyan-900 font-poppins leading-tight">
                            {{ \Carbon\Carbon::parse($kunjungan->created_at)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </p>
                        <p class="text-[12px] font-bold text-cyan-600 mt-1">
                            <i class="far fa-clock"></i> Pukul {{ \Carbon\Carbon::parse($kunjungan->created_at)->timezone('Asia/Jakarta')->format('H:i') }} WIB
                        </p>
                    </div>

                    <div class="info-box">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3"><i class="fas fa-tasks text-cyan-400 mr-1.5"></i> Layanan yang Diterima</p>
                        <div class="space-y-3">
                            {{-- Modul Fisik --}}
                            @if($kunjungan->pemeriksaan)
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center shrink-0"><i class="fas fa-stethoscope"></i></div>
                                <div>
                                    <p class="text-[12px] font-black text-slate-800">Cek Fisik & Medis Dasar</p>
                                    <a href="{{ route('kader.pemeriksaan.show', $kunjungan->pemeriksaan->id) }}" class="text-[10px] font-bold text-blue-600 hover:underline mt-0.5 inline-block no-print">Lihat Detail Rekam Medis &rarr;</a>
                                </div>
                            </div>
                            @endif

                            {{-- Modul Vaksin --}}
                            @if($kunjungan->imunisasis && $kunjungan->imunisasis->count() > 0)
                            @foreach($kunjungan->imunisasis as $imun)
                            <div class="flex items-start gap-3 p-3 bg-teal-50 rounded-xl border border-teal-100">
                                <div class="w-8 h-8 bg-teal-100 text-teal-600 rounded-lg flex items-center justify-center shrink-0"><i class="fas fa-syringe"></i></div>
                                <div>
                                    <p class="text-[12px] font-black text-slate-800">Vaksin: {{ $imun->vaksin }} (Dosis {{ $imun->dosis }})</p>
                                    <p class="text-[10px] font-bold text-teal-600 mt-0.5">Tipe: {{ $imun->jenis_imunisasi }}</p>
                                </div>
                            </div>
                            @endforeach
                            @endif

                            {{-- Kosong --}}
                            @if(!$kunjungan->pemeriksaan && (!$kunjungan->imunisasis || $kunjungan->imunisasis->count() == 0))
                            <div class="p-3 text-center border-2 border-dashed border-slate-200 rounded-xl text-slate-400 font-bold text-[11px]">
                                Data ini hanya berisi presensi atau kunjungan umum tanpa pengukuran fisik.
                            </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- KOLOM KANAN --}}
                <div class="space-y-6 stagger-2">

                    <div class="info-box">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-500 border border-amber-200 flex items-center justify-center shrink-0 shadow-sm"><i class="fas fa-comment-medical text-xs"></i></div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tujuan Kedatangan</p>
                        </div>
                        <p class="text-[14px] font-bold text-slate-700 italic leading-relaxed border-l-4 border-amber-300 pl-3">"{{ $kunjungan->keluhan ?? 'Melakukan kunjungan rutin operasional posyandu bulanan.' }}"</p>
                    </div>

                    <div class="info-box">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3"><i class="fas fa-user-shield text-slate-300 mr-1.5"></i> Otoritas Resepsionis (Meja 1)</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 font-black shrink-0 shadow-inner">
                                {{ strtoupper(substr($kunjungan->petugas->name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-[13px] font-black text-slate-800 font-poppins">{{ $kunjungan->petugas->name ?? 'Sistem Posyandu' }}</p>
                                <p class="text-[10px] font-bold text-slate-400">Petugas Pendaftar</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            
            {{-- Footer Print --}}
            <div class="print-watermark mt-8">
                DOKUMEN RESMI BUKTI LAYANAN POSYANDU TERPADU<br>
                Dicetak pada: {{ now()->timezone('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y HH:mm:ss') }} WIB | ID: {{ $kunjungan->kode_kunjungan }}
            </div>
        </div>
    </div>
</div>
@endsection