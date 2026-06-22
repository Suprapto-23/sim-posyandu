@extends('layouts.kader')

@section('title', 'Detail Kunjungan')
@section('page-name', 'Nota Kehadiran')
@section('page-title', 'Arsip Nota Kunjungan')

@php
    use Carbon\Carbon;
    Carbon::setLocale('id');

    $tipe = class_basename($kunjungan->pasien_type); 
    
    $badgeTheme = match(strtolower($tipe)) {
        'balita' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-200', 'icon' => 'fa-baby', 'label' => 'Balita / Anak'],
        'remaja' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'icon' => 'fa-user-graduate', 'label' => 'Remaja'],
        'lansia' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'fa-person-cane', 'label' => 'Lansia'],
        default  => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'icon' => 'fa-user', 'label' => $tipe],
    };

    $namaPasien = $kunjungan->pasien->nama_lengkap ?? 'Data Terhapus';
    $nikPasien = $kunjungan->pasien->nik ?? $kunjungan->pasien->kode_balita ?? 'Tanpa ID';
    
    // Teks Keluhan yang lebih relevan dengan alur medis Posyandu
    $keluhan = $kunjungan->keluhan ?: 'Pemeriksaan rutin. Tidak ada keluhan medis khusus yang dicatat.';
    
    $waktuCheckIn = Carbon::parse($kunjungan->created_at)->timezone('Asia/Jakarta');
    $petugasName = $kunjungan->petugas->name ?? 'Sistem Posyandu';
    $petugasInitial = strtoupper(substr($petugasName, 0, 1));
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body { background-color: #f4f7f6; } 

    .bg-mesh-fixed {
        position: fixed; inset: 0; z-index: -10;
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        pointer-events: none;
    }

    .widget-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.97); }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    /* CSS KHUSUS CETAK (PRINT) */
    .print-watermark { display: none; }
    @media print {
        body { background: white !important; }
        .bg-mesh-fixed, .no-print, .pc-sidebar, header { display: none !important; }
        .widget-card { border: 1px solid #cbd5e1 !important; box-shadow: none !important; break-inside: avoid; border-radius: 1rem !important; }
        .print-area { display: block !important; width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .print-watermark { display: block; margin-top: 40px; text-align: center; font-size: 11px; color: #64748b; font-family: monospace; border-top: 1px dashed #cbd5e1; padding-top: 10px; }
    }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed no-print"></div>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1200px] mx-auto space-y-6 animate-pop-in print-area">

    {{-- 1. HERO BANNER (Profile Pasien) --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 p-8 sm:p-10 shadow-2xl shadow-emerald-500/20 flex flex-col md:flex-row justify-between items-center gap-8 border-[6px] border-white/40 no-print">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full flex flex-col gap-4 text-center md:text-left">
            <div class="flex flex-col sm:flex-row items-center gap-3 justify-center md:justify-start">
                <a href="{{ route('kader.kunjungan.index') }}" class="btn-pill inline-flex items-center gap-2 border border-white/30 bg-white/20 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/30 shadow-sm backdrop-blur-md transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/20 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-white shadow-sm backdrop-blur-md">
                    <i class="fa-solid fa-address-book"></i> Arsip Nota Kunjungan
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                {{ $namaPasien }}
            </h1>

            <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-3 gap-y-2 mt-2">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black border border-white/30 bg-white/20 text-white backdrop-blur-md">
                    <i class="fa-solid fa-id-card"></i> ID: {{ $nikPasien }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black border bg-white text-slate-700 shadow-sm">
                    <i class="fa-solid {{ $badgeTheme['icon'] }} {{ str_replace('text-', 'text-', $badgeTheme['text']) }}"></i> {{ $badgeTheme['label'] }}
                </span>
            </div>
        </div>

        <div class="relative z-10 shrink-0">
            <button onclick="window.print()" class="btn-pill bg-white text-emerald-600 hover:bg-emerald-50 px-6 py-3.5 text-[11px] font-black uppercase tracking-widest shadow-md flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5 w-full sm:w-auto">
                <i class="fa-solid fa-print text-sm"></i> Cetak Bukti
            </button>
        </div>
    </section>

    {{-- HEADER PRINT ONLY (Hanya muncul saat dicetak) --}}
    <div class="hidden print:block mb-8 text-center border-b-2 border-slate-800 pb-4">
        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-widest">BUKTI PELAYANAN POSYANDU</h1>
        <p class="text-sm font-bold text-slate-600 mt-1">Nama: {{ $namaPasien }} | NIK/ID: {{ $nikPasien }} | Sasaran: {{ $badgeTheme['label'] }}</p>
    </div>

    {{-- 2. GRID UTAMA PRESISI (items-stretch untuk mengunci tinggi, flex-1 di kartu bawah) --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-stretch">
        
        {{-- KOLOM KIRI (Col 8) --}}
        <div class="xl:col-span-8 flex flex-col gap-6 h-full">
            
            {{-- Waktu Pelayanan --}}
            <section class="widget-card p-6 sm:p-8 shrink-0">
                <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Detail Waktu Pelayanan</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Waktu Pencatatan Sistem</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-6 bg-slate-50/50 rounded-[1.5rem] border border-slate-100 p-6">
                    <div class="flex flex-col items-center justify-center w-20 h-20 bg-white rounded-[1rem] text-emerald-600 shadow-sm border border-emerald-100 shrink-0">
                        <span class="text-[10px] font-black uppercase tracking-widest mb-1">{{ $waktuCheckIn->translatedFormat('M') }}</span>
                        <span class="text-3xl font-black leading-none">{{ $waktuCheckIn->format('d') }}</span>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-lg font-black text-slate-800 mb-1">{{ $waktuCheckIn->translatedFormat('l, d F Y') }}</p>
                        <p class="text-sm font-bold text-slate-500"><i class="fa-solid fa-clock mr-1 text-emerald-500"></i> Pukul {{ $waktuCheckIn->format('H:i') }} WIB</p>
                    </div>
                </div>
            </section>

            {{-- Layanan Diterima (Menggunakan flex-1 agar merenggang rata dengan kolom kanan) --}}
            <section class="widget-card p-6 sm:p-8 flex flex-col flex-1 h-full">
                <div class="flex items-center justify-between gap-4 mb-5 border-b border-slate-100 pb-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-500 border border-cyan-100 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-notes-medical"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Layanan Diterima</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Pemeriksaan / Vaksinasi</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 flex-1">
                    {{-- Modul Pemeriksaan Fisik --}}
                    @if($kunjungan->pemeriksaan)
                    <div class="flex items-start gap-4 p-4 bg-white rounded-[1.5rem] border border-slate-200 shadow-sm transition-transform hover:-translate-y-0.5">
                        <div class="w-12 h-12 bg-sky-50 text-sky-600 border border-sky-100 rounded-xl flex items-center justify-center shrink-0 text-xl"><i class="fa-solid fa-stethoscope"></i></div>
                        <div>
                            <p class="text-sm font-black text-slate-800 mb-1">Cek Fisik & Medis Dasar</p>
                            <p class="text-[11px] font-medium text-slate-500 mb-2">Telah dilakukan pengukuran antropometri / tanda-tanda vital.</p>
                            <a href="{{ route('kader.pemeriksaan.show', $kunjungan->pemeriksaan->id) }}" class="btn-pill inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-100 text-sky-700 text-[9px] font-black uppercase tracking-widest no-print hover:bg-sky-200">
                                Lihat Rekam Medis <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Modul Imunisasi --}}
                    @if($kunjungan->imunisasis && $kunjungan->imunisasis->count() > 0)
                        @foreach($kunjungan->imunisasis as $imun)
                        <div class="flex items-start gap-4 p-4 bg-white rounded-[1.5rem] border border-slate-200 shadow-sm transition-transform hover:-translate-y-0.5">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-xl flex items-center justify-center shrink-0 text-xl"><i class="fa-solid fa-syringe"></i></div>
                            <div>
                                <p class="text-sm font-black text-slate-800 mb-1">Vaksin: {{ $imun->vaksin }} <span class="text-slate-400 font-medium">(Dosis {{ $imun->dosis }})</span></p>
                                <p class="text-[11px] font-bold text-emerald-600"><span class="text-slate-400 font-medium">Kategori:</span> {{ $imun->jenis_imunisasi }}</p>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    {{-- Jika Kosong --}}
                    @if(!$kunjungan->pemeriksaan && (!$kunjungan->imunisasis || $kunjungan->imunisasis->count() == 0))
                    <div class="p-6 text-center border-2 border-dashed border-slate-200 rounded-[1.5rem] bg-slate-50/50">
                        <i class="fa-solid fa-person-circle-check text-3xl text-slate-300 mb-2"></i>
                        <p class="text-xs font-bold text-slate-500">Data ini hanya berisi riwayat kehadiran awal tanpa pengukuran medis lanjutan.</p>
                    </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- KOLOM KANAN (Col 4): Tujuan & Petugas --}}
        <aside class="xl:col-span-4 flex flex-col gap-6 h-full">
            
            {{-- Keluhan Awal --}}
            <section class="widget-card p-6 shrink-0">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center shrink-0 shadow-sm"><i class="fa-solid fa-comment-medical text-sm"></i></div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Keluhan Awal / Catatan</p>
                </div>
                <div class="bg-amber-50/50 border border-amber-100 rounded-2xl p-4">
                    <p class="text-sm font-bold text-slate-700 italic leading-relaxed border-l-4 border-amber-400 pl-3">"{{ $keluhan }}"</p>
                </div>
            </section>

            {{-- Otoritas Petugas (Menggunakan flex-1 agar rata bawah dengan kolom kiri) --}}
            <section class="widget-card p-6 flex flex-col flex-1 h-full">
                <div class="flex items-center gap-3 mb-4 shrink-0">
                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center shrink-0 shadow-sm"><i class="fa-solid fa-user-shield text-sm"></i></div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Petugas Pencatat</p>
                </div>
                <div class="flex items-center gap-4 bg-slate-50 border border-slate-100 rounded-2xl p-4">
                    <div class="w-12 h-12 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-600 font-black shrink-0 shadow-sm text-lg">
                        {{ $petugasInitial }}
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">{{ $petugasName }}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Kader Bertugas</p>
                    </div>
                </div>

                {{-- Footer Print Area di dalam card agar selalu di bawah --}}
                <div class="print-watermark mt-auto pt-6">
                    DOKUMEN BUKTI LAYANAN POSYANDU TERPADU<br>
                    Dicetak pada: {{ now()->timezone('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y HH:mm:ss') }} WIB | ID: {{ $kunjungan->kode_kunjungan ?? 'KJ-N/A' }}
                </div>
            </section>

        </aside>
    </div>

</div>
@endsection