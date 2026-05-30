@extends('layouts.bidan')

@section('title', 'Sertifikat & Detail Imunisasi')
@section('page-name', 'Arsip Vaksinasi')

@push('styles')
<style>
    /* ANIMASI MASUK HALUS */
    .fade-in-up { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    /* TIPOGRAFI DATA (PRESISI NEXUS) */
    .data-label { 
        display: block; font-size: 11px; font-weight: 800; color: #64748b; 
        text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 8px; 
        font-family: 'Poppins', sans-serif;
    }
    .data-box {
        width: 100%; background: #ffffff; border: 2px solid #f1f5f9; border-radius: 16px; 
        padding: 16px 20px; color: #0f172a; font-weight: 700; font-size: 14.5px; 
        box-shadow: 0 2px 6px rgba(15,23,42,0.02); display: flex; align-items: center; gap: 12px;
    }
    .data-box-highlight {
        background: #f0f9ff; border-color: #bae6fd; color: #0369a1;
    }

    /* IKON SEKSI */
    .section-icon {
        width: 34px; height: 34px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
        font-size: 14px; background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
@php
    // Failsafe Tarikan Data (Mencegah Layar Putih jika Relasi Kosong)
    $kunjungan = $imunisasi->kunjungan ?? null;
    $pasien = $kunjungan ? $kunjungan->pasien : null;
    $petugas = $kunjungan ? $kunjungan->petugas : null;

    $nama = $pasien->nama_lengkap ?? 'Identitas Tidak Ditemukan';
    $nik = $pasien->nik ?? 'Tidak Ada NIK';
    
    // Konfigurasi Kategori Warga Berdasarkan Relasi Polimorfik
    $kategoriRaw = strtolower(class_basename($kunjungan->pasien_type ?? ''));
    $kategoriConfig = match($kategoriRaw) {
        'balita' => ['label' => 'Balita', 'icon' => 'fa-baby', 'theme' => 'bg-cyan-100 text-cyan-600 border-cyan-200'],
        default                          => ['label' => 'Umum', 'icon' => 'fa-user', 'theme' => 'bg-slate-100 text-slate-600 border-slate-200']
    };
@endphp

<div class="max-w-[1050px] mx-auto fade-in-up pb-24 relative">
    
    {{-- NAVIGASI ATAS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 px-2">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 rounded-[20px] bg-gradient-to-tr from-cyan-500 to-blue-600 text-white flex items-center justify-center text-2xl shadow-[0_10px_25px_rgba(6,182,212,0.35)]">
                <i class="fas fa-file-medical"></i>
            </div>
            <div>
                <h1 class="text-[26px] font-black text-slate-800 tracking-tight font-poppins leading-none">Arsip Injeksi & Vaksin</h1>
                <p class="text-[13px] font-semibold text-slate-500 mt-1.5">Sertifikat dan rincian tindakan imunisasi warga.</p>
            </div>
        </div>
        <a href="{{ route('bidan.imunisasi.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white border border-slate-200 text-slate-600 font-bold text-[11.5px] uppercase tracking-widest rounded-[16px] hover:bg-slate-50 hover:text-cyan-600 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-slate-400"></i> Kembali
        </a>
    </div>

    {{-- =====================================================================
         KONTANER UTAMA (NEXUS STANDARD)
         ===================================================================== --}}
    <div class="bg-white rounded-[36px] shadow-[0_25px_70px_-15px_rgba(0,0,0,0.06)] border border-slate-100 overflow-hidden relative z-10">
        
        {{-- HEADER KARTU ARSIP --}}
        <div class="px-8 md:px-12 py-8 bg-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative overflow-hidden">
            <div class="flex items-center gap-5 relative z-10">
                <div class="w-12 h-12 rounded-[14px] bg-white border border-slate-200 text-cyan-600 flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-shield-virus"></i>
                </div>
                <div>
                    <h2 class="text-[18px] font-black text-slate-800 tracking-tight font-poppins">Data Vaksinasi Valid</h2>
                    <p class="text-[12px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest">ID REKAM: IMU-{{ str_pad($imunisasi->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
            <div class="inline-flex items-center gap-3 px-5 py-3 bg-white rounded-2xl border border-slate-100 shadow-sm relative z-10">
                <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]"></div>
                <span class="text-[11px] font-black text-slate-600 uppercase tracking-widest">Disahkan Bidan</span>
            </div>
        </div>

        {{-- 1. IDENTITAS PENERIMA VAKSIN --}}
        <div class="p-8 md:p-12 border-b border-slate-100 bg-white">
            <div class="flex items-center gap-4 mb-8">
                <div class="section-icon"><i class="fas fa-id-card"></i></div>
                <h3 class="text-[13px] font-black text-slate-800 uppercase tracking-widest font-poppins">Identitas Penerima</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-0 md:pl-[50px]">
                <div>
                    <span class="data-label">Nama Lengkap</span>
                    <div class="data-box">
                        <i class="far fa-user text-slate-400"></i> {{ $nama }}
                    </div>
                </div>
                <div>
                    <span class="data-label">Kategori & NIK</span>
                    <div class="data-box justify-between">
                        <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg border {{ $kategoriConfig['theme'] }}">
                            <i class="fas {{ $kategoriConfig['icon'] }} mr-1"></i> {{ $kategoriConfig['label'] }}
                        </span>
                        <span class="tracking-wide text-slate-600 text-[13px]"><i class="fas fa-fingerprint text-slate-300 mr-1.5"></i> {{ $nik }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. RINCIAN TINDAKAN MEDIS --}}
        <div class="p-8 md:p-12 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-4 mb-8">
                <div class="section-icon"><i class="fas fa-syringe"></i></div>
                <h3 class="text-[13px] font-black text-slate-800 uppercase tracking-widest font-poppins">Rincian Tindakan Medis</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-0 md:pl-[50px]">
                <div>
                    <span class="data-label">Program Imunisasi</span>
                    <div class="data-box">
                        <i class="fas fa-layer-group text-slate-400"></i> {{ $imunisasi->jenis_imunisasi ?? '-' }}
                    </div>
                </div>
                <div>
                    <span class="data-label">Tanggal Pelaksanaan</span>
                    <div class="data-box">
                        <i class="far fa-calendar-check text-slate-400"></i> 
                        {{ $imunisasi->tanggal_imunisasi ? \Carbon\Carbon::parse($imunisasi->tanggal_imunisasi)->translatedFormat('d F Y') : '-' }}
                    </div>
                </div>
                
                <div>
                    <span class="data-label">Jenis / Nama Vaksin</span>
                    <div class="data-box data-box-highlight">
                        <i class="fas fa-vial text-cyan-500"></i> {{ $imunisasi->vaksin ?? '-' }}
                    </div>
                </div>
                <div>
                    <span class="data-label">Dosis Diberikan</span>
                    <div class="data-box">
                        <i class="fas fa-prescription-bottle text-slate-400"></i> {{ $imunisasi->dosis ?? 'Tidak tercatat' }}
                    </div>
                </div>
                
                <div class="md:col-span-2">
                    <span class="data-label">Bidan Penanggung Jawab</span>
                    <div class="data-box bg-slate-50">
                        <i class="fas fa-user-nurse text-indigo-400"></i> {{ $petugas->name ?? 'Sistem Puskesmas' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. OBSERVASI KLINIS (KIPI) --}}
        <div class="p-8 md:p-12 bg-white">
            <div class="flex items-center gap-4 mb-8">
                <div class="section-icon"><i class="fas fa-file-medical-alt"></i></div>
                <h3 class="text-[13px] font-black text-slate-800 uppercase tracking-widest font-poppins">Observasi Klinis (KIPI)</h3>
            </div>
            
            <div class="pl-0 md:pl-[50px]">
                @php 
                    $keterangan = trim($imunisasi->keterangan ?? '');
                    $hasKipi = !empty($keterangan) && strtolower($keterangan) != '-' && strtolower($keterangan) != 'aman' && strtolower($keterangan) != 'tidak ada'; 
                @endphp
                
                <div class="p-6 md:p-8 rounded-[24px] border-2 {{ $hasKipi ? 'border-amber-200 bg-amber-50/80 text-amber-900' : 'border-emerald-100 bg-emerald-50/50 text-emerald-800' }} relative overflow-hidden shadow-sm">
                    <i class="fas {{ $hasKipi ? 'fa-exclamation-triangle text-amber-500/10' : 'fa-check-circle text-emerald-500/10' }} absolute -right-6 -top-6 text-9xl pointer-events-none"></i>
                    
                    <div class="flex flex-col sm:flex-row sm:items-start gap-6 relative z-10">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center shrink-0 {{ $hasKipi ? 'bg-amber-100 text-amber-500' : 'bg-emerald-100 text-emerald-500' }} shadow-sm">
                            <i class="fas {{ $hasKipi ? 'fa-exclamation-triangle' : 'fa-check' }} text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-[13px] font-black uppercase tracking-widest {{ $hasKipi ? 'text-amber-600' : 'text-emerald-600' }} mb-2 font-poppins">
                                {{ $hasKipi ? 'Terdapat Catatan Pasca Imunisasi' : 'Aman & Terkendali' }}
                            </p>
                            <p class="text-[14.5px] font-medium leading-relaxed opacity-90">
                                {{ $hasKipi ? $imunisasi->keterangan : 'Hasil observasi menunjukkan tidak ditemukan gejala atau keluhan klinis (KIPI) yang merugikan pasca penyuntikan vaksin.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FOOTER ARSIP --}}
        <div class="px-8 md:px-12 py-6 bg-slate-900 flex justify-center sm:justify-end shrink-0 relative z-0">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                Tercatat pada: {{ $imunisasi->created_at ? $imunisasi->created_at->translatedFormat('d M Y - H:i') : '-' }} WIB
            </p>
        </div>
        
    </div>
</div>
@endsection