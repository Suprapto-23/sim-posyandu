@extends('layouts.kader')
@section('title', 'Detail Rekam Medis')
@section('page-name', 'Log Pemeriksaan Pasien')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    /* =================================================================
       NEXUS SAAS DESIGN SYSTEM (PRECISION READ-ONLY EDITION)
       ================================================================= */
    
    /* Animasi Masuk Beruntun */
    .animate-fade-in { opacity: 0; animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .delay-100 { animation-delay: 0.1s; } .delay-200 { animation-delay: 0.15s; } .delay-300 { animation-delay: 0.2s; } .delay-400 { animation-delay: 0.25s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* Kartu Detail (Lebih Lembut & Elegan) */
    .nexus-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 28px;
        box-shadow: 0 10px 40px -10px rgba(15, 23, 42, 0.04); overflow: hidden; margin-bottom: 2rem;
    }
    .nexus-card-header {
        background: #ffffff; border-bottom: 1px solid #f8fafc; padding: 1.5rem 2rem;
        display: flex; align-items: center; gap: 1rem;
    }
    .nexus-card-body { padding: 2rem; background: #fcfcfd; }

    /* Tipografi Label & Value (Anti-Kaku) */
    .data-label { display: block; font-size: 0.65rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; }
    .data-value { font-family: 'Inter', sans-serif; font-size: 1.05rem; font-weight: 600; color: #0f172a; display: flex; align-items: baseline; gap: 0.25rem; }
    .data-unit { font-size: 0.75rem; font-weight: 500; color: #94a3b8; text-transform: lowercase; }
    
    /* Blok Data (Soft Pill Widget Design) */
    .data-block { 
        background: #ffffff; border: 1px solid #f1f5f9; border-radius: 20px; 
        padding: 1.25rem 1.5rem; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.01); 
    }
    .data-block:hover { border-color: #e2e8f0; box-shadow: 0 6px 15px -3px rgba(15, 23, 42, 0.03); transform: translateY(-2px); }

    /* Isolasi SweetAlert */
    body.swal2-shown:not(.swal2-toast-shown) .swal2-container { z-index: 10000 !important; backdrop-filter: blur(6px) !important; background: rgba(15, 23, 42, 0.4) !important; }
    .nexus-modal { border-radius: 28px !important; padding: 2rem !important; background: #ffffff !important; width: 26em !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; border: 1px solid #f1f5f9 !important; }
    .nexus-modal .swal2-title { font-family: 'Poppins', sans-serif !important; font-weight: 700 !important; font-size: 1.25rem !important; color: #0f172a !important; margin-bottom: 0.5rem !important; }
    .btn-swal-danger { background: #f43f5e !important; color: white !important; border-radius: 100px !important; padding: 12px 24px !important; font-weight: 600 !important; font-size: 11px !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; border: none !important; margin-right: 8px !important; transition: 0.2s !important; }
    .btn-swal-cancel { background: #f1f5f9 !important; color: #475569 !important; border-radius: 100px !important; padding: 12px 24px !important; font-weight: 600 !important; font-size: 11px !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; border: none !important; transition: 0.2s !important; }
</style>
@endpush

@section('content')
<div class="max-w-[1000px] mx-auto animate-fade-in pb-20 relative z-10 mt-2">

    {{-- AURA BACKGROUND --}}
    <div class="fixed top-0 right-0 w-[500px] h-[500px] bg-indigo-500/5 rounded-full blur-[120px] pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 left-0 w-[400px] h-[400px] bg-sky-500/5 rounded-full blur-[120px] pointer-events-none -z-10"></div>

    {{-- HEADER SAAS MINIMALIST --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div class="flex items-center gap-5">
            <a href="{{ route('kader.pemeriksaan.index') }}" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-sm shrink-0">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight font-poppins mb-1">Rekam Medis Pasien</h1>
                <p class="text-slate-500 font-medium text-[13px]">Detail antropometri dan indikator klinis.</p>
            </div>
        </div>

        {{-- ACTION BUTTONS (Edit & Delete) --}}
        <div class="flex items-center gap-3 shrink-0">
            @if(in_array($pemeriksaan->status_verifikasi, ['pending', 'ditolak', 'rejected']))
                <a href="{{ route('kader.pemeriksaan.edit', $pemeriksaan->id) }}" class="px-5 py-2.5 rounded-xl font-semibold text-amber-600 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition-colors uppercase text-[10px] tracking-widest text-center shadow-sm flex items-center gap-2">
                    <i class="fas fa-pen"></i> Koreksi Data
                </a>
                <form action="{{ route('kader.pemeriksaan.destroy', $pemeriksaan->id) }}" method="POST" class="delete-form m-0 p-0">
                    @csrf @method('DELETE')
                    <button type="button" class="btn-delete px-4 py-2.5 rounded-xl font-semibold text-rose-500 bg-white border border-rose-200 hover:bg-rose-50 hover:border-rose-300 transition-colors shadow-sm flex items-center justify-center" title="Hapus Permanen">
                        <i class="fas fa-trash-alt text-[13px]"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- STATUS BANNER (Elegan & Soft) --}}
    @if(in_array($pemeriksaan->status_verifikasi, ['tervalidasi', 'verified', 'approved']))
        <div class="bg-emerald-50 border border-emerald-100 rounded-[24px] p-5 md:p-6 mb-8 flex items-center gap-5 shadow-sm text-emerald-800 relative overflow-hidden animate-fade-in delay-100">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-xl shrink-0 text-emerald-600"><i class="fas fa-check-circle"></i></div>
            <div>
                <p class="text-[15px] font-bold font-poppins mb-0.5">Telah Divalidasi Bidan</p>
                <p class="text-xs font-medium text-emerald-600">Data telah dikunci dan disahkan menjadi rekam medis sistem.</p>
            </div>
        </div>
    @elseif(in_array($pemeriksaan->status_verifikasi, ['ditolak', 'rejected']))
        <div class="bg-rose-50 border border-rose-100 rounded-[24px] p-5 md:p-6 mb-8 flex items-center gap-5 shadow-sm text-rose-800 relative overflow-hidden animate-fade-in delay-100">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 flex items-center justify-center text-xl shrink-0 text-rose-600"><i class="fas fa-times-circle"></i></div>
            <div>
                <p class="text-[15px] font-bold font-poppins mb-0.5">Memerlukan Revisi (Ditolak)</p>
                <p class="text-xs font-medium text-rose-600">Bidan menemukan kejanggalan. Silakan baca catatan dan lakukan koreksi data.</p>
            </div>
        </div>
    @else
        <div class="bg-indigo-50 border border-indigo-100 rounded-[24px] p-5 md:p-6 mb-8 flex items-center gap-5 shadow-sm text-indigo-800 relative overflow-hidden animate-fade-in delay-100">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-xl shrink-0 text-indigo-500"><i class="fas fa-hourglass-half animate-pulse"></i></div>
            <div>
                <p class="text-[15px] font-bold font-poppins mb-0.5">Menunggu Validasi</p>
                <p class="text-xs font-medium text-indigo-600">Data ini berada dalam antrean pemeriksaan Bidan di Meja 5.</p>
            </div>
        </div>
    @endif

    {{-- ==========================================================
         BLOK 1: IDENTITAS PASIEN (GRID PRESISI, TYPOGRAPHY LEMBUT)
         ========================================================== --}}
    <div class="nexus-card animate-fade-in delay-100">
        <div class="nexus-card-header">
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center text-lg border border-slate-100"><i class="fas fa-id-badge"></i></div>
            <div>
                <h5 class="font-bold text-slate-800 text-[14px] uppercase tracking-widest font-poppins">Profil Pasien</h5>
            </div>
        </div>
        
        <div class="nexus-card-body">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                {{-- Baris 1 --}}
                <div class="data-block md:col-span-5">
                    <span class="data-label">Nama Lengkap</span>
                    <span class="data-value">{{ $pemeriksaan->nama_pasien }}</span>
                </div>
                <div class="data-block md:col-span-4">
                    <span class="data-label">NIK Kependudukan</span>
                    <span class="data-value font-mono text-[0.95rem] tracking-wide">{{ $pemeriksaan->nik_pasien }}</span>
                </div>
                <div class="data-block md:col-span-3">
                    <span class="data-label">Kategori Sasaran</span>
                    @php
                        $katDisplay = match($pemeriksaan->kategori_pasien) {
                            'balita' => 'Balita', 'remaja' => 'Remaja', 'lansia' => 'Lansia',
                            default => strtoupper(str_replace('_', ' ', $pemeriksaan->kategori_pasien))
                        };
                    @endphp
                    <span class="data-value">{{ $katDisplay }}</span>
                </div>
                
                {{-- Baris 2 --}}
                <div class="data-block md:col-span-5">
                    <span class="data-label">ID Kunjungan & Waktu</span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="bg-slate-100 text-slate-600 font-semibold text-[10px] uppercase tracking-widest px-2 py-1 rounded border border-slate-200">{{ $pemeriksaan->kunjungan->kode_kunjungan ?? 'SYNC' }}</span>
                        <span class="text-slate-500 font-medium text-[0.95rem]">{{ $pemeriksaan->tanggal_periksa->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
                <div class="data-block md:col-span-7 flex flex-col justify-center">
                    <span class="data-label">Petugas Pencatat</span>
                    <span class="data-value"><i class="fas fa-user-circle text-slate-300 text-xl mr-1"></i> {{ $pemeriksaan->petugas->name ?? 'Sistem' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================================
         BLOK 2: UKUR DASAR (SEMUA KATEGORI)
         ========================================================== --}}
    <div class="nexus-card animate-fade-in delay-200">
        <div class="nexus-card-header">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg border border-emerald-100"><i class="fas fa-weight"></i></div>
            <div>
                <h5 class="font-bold text-slate-800 text-[14px] uppercase tracking-widest font-poppins">Antropometri Dasar</h5>
                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Hasil pengukuran tanda vital utama.</p>
            </div>
        </div>
        
        <div class="nexus-card-body">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                <div class="data-block bg-white flex flex-col justify-center">
                    <span class="data-label">Berat Badan</span>
                    <span class="text-[1.35rem] font-bold text-slate-800 font-poppins mt-0.5">{{ $pemeriksaan->berat_badan ?? '-' }} <span class="data-unit">kg</span></span>
                </div>
                <div class="data-block bg-white flex flex-col justify-center">
                    <span class="data-label">Tinggi/Panjang</span>
                    <span class="text-[1.35rem] font-bold text-slate-800 font-poppins mt-0.5">{{ $pemeriksaan->tinggi_badan ?? '-' }} <span class="data-unit">cm</span></span>
                </div>
                <div class="data-block bg-white flex flex-col justify-center">
                    <span class="data-label">Suhu Tubuh</span>
                    <span class="text-[1.35rem] font-bold text-slate-800 font-poppins mt-0.5">{{ $pemeriksaan->suhu_tubuh ?? '-' }} <span class="data-unit">°C</span></span>
                </div>

                {{-- IMT Widget (Sleek Dark Mode, Tetap Konsisten) --}}
                @if($pemeriksaan->kategori_pasien != 'balita')
                <div class="col-span-2 md:col-span-1 bg-slate-900 rounded-2xl p-4 flex flex-col justify-center shadow-md relative overflow-hidden h-full">
                    <div class="absolute -right-3 -top-3 text-white/5 text-6xl transform rotate-12"><i class="fas fa-calculator"></i></div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 relative z-10">Nilai IMT</span>
                    <div class="flex items-center justify-between mt-1 relative z-10">
                        <span class="text-2xl font-bold text-white font-poppins leading-none">{{ $pemeriksaan->imt ?? '-' }}</span>
                        @php
                            $imt = $pemeriksaan->imt;
                            $lbl = '-'; $c = 'bg-slate-800 text-slate-400 border-slate-700';
                            if($imt) {
                                if($imt < 18.5) { $lbl = 'Kurus'; $c = 'bg-amber-500/20 text-amber-300 border-amber-400/50'; }
                                elseif($imt >= 25 && $imt < 27) { $lbl = 'Gemuk'; $c = 'bg-rose-500/20 text-rose-300 border-rose-400/50'; }
                                elseif($imt >= 27) { $lbl = 'Obesitas'; $c = 'bg-rose-600 text-white border-rose-500'; }
                                else { $lbl = 'Normal'; $c = 'bg-emerald-500/20 text-emerald-300 border-emerald-400/50'; }
                            }
                        @endphp
                        <span class="px-2.5 py-1 rounded text-[10px] font-semibold uppercase tracking-widest border {{ $c }}">{{ $lbl }}</span>
                    </div>
                </div>
                @else
                <div class="data-block bg-slate-50 border-slate-100 flex flex-col justify-center items-center text-center">
                    <span class="data-label text-[10px] mb-1">Indikator</span>
                    <span class="text-xs font-medium text-slate-500">IMT tidak diterapkan pada balita.</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ==========================================================
         BLOK 3: FORM DINAMIS (KATEGORI SPESIFIK)
         ========================================================== --}}
    @php
        $kat = $pemeriksaan->kategori_pasien;
        $isBaby = ($kat == 'balita' && $pemeriksaan->kunjungan && $pemeriksaan->kunjungan->pasien && $pemeriksaan->kunjungan->pasien->usia_bulan < 12);
        
        $themeMap = [
            'balita' => ['color' => 'sky', 'icon' => 'fa-child', 'title' => 'Pengukuran Balita'],
            'remaja' => ['color' => 'indigo', 'icon' => 'fa-user-graduate', 'title' => 'Pemeriksaan Remaja'],
            'lansia' => ['color' => 'emerald', 'icon' => 'fa-wheelchair', 'title' => 'Cek Medis Lansia']
        ];

        if ($isBaby) {
            $themeMap['balita']['icon'] = 'fa-baby';
            $themeMap['balita']['title'] = 'Pengukuran Bayi';
        }

        $theme = $themeMap[$kat] ?? ['color' => 'slate', 'icon' => 'fa-stethoscope', 'title' => 'Pemeriksaan Khusus'];
        $c = $theme['color'];
    @endphp

    <div class="nexus-card animate-fade-in delay-300">
        <div class="nexus-card-header">
            <div class="w-10 h-10 rounded-xl bg-{{$c}}-50 text-{{$c}}-600 flex items-center justify-center text-lg border border-{{$c}}-100"><i class="fas {{ $theme['icon'] }}"></i></div>
            <div>
                <h5 class="font-bold text-slate-800 text-[14px] uppercase tracking-widest font-poppins">{{ $theme['title'] }}</h5>
                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Hasil indikator klinis spesifik kategori.</p>
            </div>
        </div>
        
        <div class="nexus-card-body">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                
                {{-- KONTEN BALITA --}}
                @if($kat == 'balita')
                    <div class="data-block bg-sky-50/40 border-sky-100 flex flex-col justify-center">
                        <span class="data-label text-sky-700">Lingkar Kepala</span>
                        <span class="data-value text-sky-900">{{ $pemeriksaan->lingkar_kepala ?? '-' }} <span class="data-unit">cm</span></span>
                    </div>
                    <div class="data-block bg-sky-50/40 border-sky-100 flex flex-col justify-center">
                        <span class="data-label text-sky-700">Lingkar Lengan (LiLA)</span>
                        <span class="data-value text-sky-900">{{ $pemeriksaan->lingkar_lengan ?? '-' }} <span class="data-unit">cm</span></span>
                    </div>
                @endif

                
                {{-- KONTEN REMAJA --}}
                @if($kat == 'remaja')
                    <div class="data-block bg-indigo-50/40 border-indigo-100 flex flex-col justify-center">
                        <span class="data-label text-indigo-700">Tensi Darah</span>
                        <span class="data-value text-indigo-900 font-mono">{{ $pemeriksaan->tekanan_darah ?? '-' }}</span>
                    </div>
                    <div class="data-block bg-indigo-50/40 border-indigo-100 flex flex-col justify-center">
                        <span class="data-label text-indigo-700">Hemoglobin (Hb)</span>
                        <span class="data-value text-indigo-900">{{ $pemeriksaan->hemoglobin ?? '-' }} <span class="data-unit">g/dL</span></span>
                    </div>
                    <div class="data-block bg-indigo-50/40 border-indigo-100 flex flex-col justify-center">
                        <span class="data-label text-indigo-700">Gula Darah</span>
                        <span class="data-value text-indigo-900">{{ $pemeriksaan->gula_darah ?? '-' }} <span class="data-unit">mg/dL</span></span>
                    </div>
                    <div class="data-block bg-indigo-50/40 border-indigo-100 flex flex-col justify-center">
                        <span class="data-label text-indigo-700">Lingkar Perut</span>
                        <span class="data-value text-indigo-900">{{ $pemeriksaan->lingkar_perut ?? '-' }} <span class="data-unit">cm</span></span>
                    </div>
                    <div class="data-block bg-rose-50 border-rose-100 md:col-span-2 flex flex-col justify-center">
                        <span class="data-label text-rose-700">LiLA (Khusus Putri)</span>
                        <span class="data-value text-rose-700 text-xl font-bold">{{ $pemeriksaan->lingkar_lengan ?? '-' }} <span class="data-unit text-rose-500">cm</span></span>
                    </div>
                @endif

                {{-- KONTEN LANSIA --}}
                @if($kat == 'lansia')
                    <div class="data-block bg-emerald-50/40 border-emerald-100 flex flex-col justify-center">
                        <span class="data-label text-emerald-700">Tensi Darah</span>
                        <span class="data-value text-emerald-900 font-mono">{{ $pemeriksaan->tekanan_darah ?? '-' }}</span>
                    </div>
                    <div class="data-block bg-emerald-50/40 border-emerald-100 flex flex-col justify-center">
                        <span class="data-label text-emerald-700">Gula Darah</span>
                        <span class="data-value text-emerald-900">{{ $pemeriksaan->gula_darah ?? '-' }} <span class="data-unit">mg/dL</span></span>
                    </div>
                    <div class="data-block bg-emerald-50/40 border-emerald-100 flex flex-col justify-center">
                        <span class="data-label text-emerald-700">Kolesterol</span>
                        <span class="data-value text-emerald-900">{{ $pemeriksaan->kolesterol ?? '-' }} <span class="data-unit">mg/dL</span></span>
                    </div>
                    <div class="data-block bg-emerald-50/40 border-emerald-100 flex flex-col justify-center">
                        <span class="data-label text-emerald-700">Asam Urat</span>
                        <span class="data-value text-emerald-900">{{ $pemeriksaan->asam_urat ?? '-' }} <span class="data-unit">mg/dL</span></span>
                    </div>
                    <div class="data-block bg-emerald-50/40 border-emerald-100 md:col-span-2 flex flex-col justify-center">
                        <span class="data-label text-emerald-700">Lingkar Perut</span>
                        <span class="data-value text-emerald-900">{{ $pemeriksaan->lingkar_perut ?? '-' }} <span class="data-unit">cm</span></span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ==========================================================
         BLOK 4: CATATAN & DIAGNOSA (SPLIT VIEW DGN FLEX-STRETCH)
         ========================================================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in delay-400 items-stretch">
        
        {{-- Sisi Kader --}}
        <div class="nexus-card mb-0 flex flex-col h-full border-amber-100 shadow-sm shadow-amber-500/5">
            <div class="nexus-card-header bg-amber-50/40 border-b border-amber-100">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg border border-amber-200"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <h5 class="font-bold text-amber-900 text-[13px] uppercase tracking-widest font-poppins">Catatan Lapangan</h5>
                    <p class="text-[11px] font-medium text-amber-700/70 mt-0.5">Dari Kader Posyandu.</p>
                </div>
            </div>
            <div class="nexus-card-body flex-1 flex flex-col gap-5 bg-white">
                <div>
                    <span class="data-label text-amber-700">Keluhan Utama</span>
                    <p class="text-[13px] font-medium text-slate-700 leading-relaxed">{{ $pemeriksaan->keluhan ?: 'Tidak ada keluhan yang dilaporkan.' }}</p>
                </div>
                <div>
                    <span class="data-label text-amber-700">Pesan Kader</span>
                    <p class="text-[13px] font-medium text-slate-700 leading-relaxed">{{ $pemeriksaan->catatan_kader ?: 'Tidak ada catatan tambahan.' }}</p>
                </div>
            </div>
        </div>

        {{-- Sisi Bidan --}}
        <div class="nexus-card mb-0 flex flex-col h-full border-indigo-100 shadow-sm shadow-indigo-500/5">
            <div class="nexus-card-header bg-indigo-50/40 border-b border-indigo-100">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg border border-indigo-200"><i class="fas fa-user-md"></i></div>
                <div>
                    <h5 class="font-bold text-indigo-900 text-[13px] uppercase tracking-widest font-poppins">Hasil Diagnosa</h5>
                    <p class="text-[11px] font-medium text-indigo-700/70 mt-0.5">Verifikasi Medis Meja 5.</p>
                </div>
            </div>
            <div class="nexus-card-body flex-1 flex flex-col bg-white p-6">
                @if(in_array($pemeriksaan->status_verifikasi, ['tervalidasi', 'verified', 'approved']))
                    <div>
                        <span class="data-label text-indigo-600 mb-2"><i class="fas fa-check-circle mr-1"></i> Bidan: {{ $pemeriksaan->verifikator->name ?? 'Puskesmas' }}</span>
                        <p class="text-[13px] font-medium text-indigo-900 leading-relaxed italic border-l-4 border-indigo-300 pl-4 py-1">"{{ $pemeriksaan->diagnosa ?: 'Kondisi pasien terpantau baik.' }}"</p>
                    </div>
                @elseif(in_array($pemeriksaan->status_verifikasi, ['ditolak', 'rejected']))
                    <div>
                        <span class="data-label text-rose-600 mb-2"><i class="fas fa-exclamation-circle mr-1"></i> Catatan Revisi Bidan</span>
                        <p class="text-[13px] font-medium text-rose-800 leading-relaxed italic border-l-4 border-rose-300 pl-4 py-1">"{{ $pemeriksaan->diagnosa ?: 'Silakan periksa kembali angka yang diinput.' }}"</p>
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-indigo-100 rounded-2xl bg-indigo-50/30">
                        <i class="fas fa-lock text-3xl text-indigo-200 mb-3"></i>
                        <p class="text-[11px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Kolom Terkunci</p>
                        <p class="text-xs font-medium text-indigo-400/80">Menunggu Bidan melakukan diagnosa klinis.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fitur Hapus
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.delete-form');
            Swal.fire({
                title: 'Hapus Permanen?',
                html: '<p class="text-sm text-slate-500 mt-1">Data rekam medis ini akan dihanguskan dari sistem.</p>',
                icon: 'warning',
                showCancelButton: true,
                buttonsStyling: false,
                reverseButtons: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                backdrop: true,
                customClass: { 
                    popup: 'nexus-modal', 
                    confirmButton: 'btn-swal-danger',
                    cancelButton: 'btn-swal-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
</script>
@endpush
@endsection