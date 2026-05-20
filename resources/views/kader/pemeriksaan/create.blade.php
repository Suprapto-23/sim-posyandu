@extends('layouts.kader')
@section('title', 'Input Pengukuran Fisik')
@section('page-name', 'Rekam Antropometri & Klinis')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    body { 
        font-family: 'Plus Jakarta Sans', sans-serif !important; 
        background-color: #f1f5f9; /* Slate 100 - Sangat Bersih */
        -webkit-font-smoothing: antialiased;
    }
    
    /* Scrollbar Tipis & Elegan untuk Dropdown */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
    
    /* Hilangkan panah up/down di input angka */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }

    /* SweetAlert Premium */
    .swal2-container { z-index: 1000000 !important; backdrop-filter: blur(4px); background: rgba(15, 23, 42, 0.4) !important; }
    .nexus-swal { border-radius: 28px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15) !important; border: 1px solid #f8fafc; padding: 2.5rem 2rem !important; }
    .nexus-swal-title { font-family: 'Plus Jakarta Sans', sans-serif !important; font-weight: 800 !important; color: #0f172a !important; font-size: 1.25rem !important; }
    .nexus-swal-text { font-family: 'Plus Jakarta Sans', sans-serif !important; color: #64748b !important; font-weight: 500 !important; }
</style>
@endpush

@section('content')
<div class="max-w-[1100px] mx-auto px-4 py-8">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Input <span class="text-emerald-600">Pengukuran Fisik</span></h1>
            <p class="text-slate-500 font-semibold text-sm mt-1">Modul pencatatan antropometri dan skrining PTM Kader Posyandu.</p>
        </div>
        <a href="{{ route('kader.pemeriksaan.index') }}" class="flex items-center justify-center px-6 py-3.5 bg-white border border-slate-200 text-slate-600 rounded-2xl font-bold text-xs hover:bg-slate-50 hover:text-emerald-600 transition-all shadow-sm shrink-0">
            <i class="fas fa-history mr-2"></i> Log Riwayat Medis
        </a>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 p-5 rounded-2xl mb-8 flex gap-4 items-start shadow-sm">
        <div class="bg-rose-100 text-rose-500 w-8 h-8 rounded-full flex items-center justify-center shrink-0">
            <i class="fas fa-exclamation-triangle text-sm"></i>
        </div>
        <div>
            <h3 class="text-rose-800 font-extrabold text-xs uppercase tracking-widest mb-1">Gagal Menyimpan Data</h3>
            <p class="text-rose-600 font-medium text-sm">{{ $errors->first() }}</p>
        </div>
    </div>
    @endif

    <form action="{{ route('kader.pemeriksaan.store') }}" method="POST" id="formPemeriksaan">
        @csrf
        
        {{-- KARTU MASTER (SOLID WHITE UI) --}}
        <div class="bg-white border border-slate-200 rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] p-6 md:p-10 relative">
            
            {{-- BAGIAN 1: SEGMENTASI KATEGORI --}}
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-4">
                    <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-3 py-1 rounded-lg uppercase tracking-widest">Langkah 1</span>
                    <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-widest">Pilih Klaster Sasaran</h2>
                </div>
                <input type="hidden" name="kategori_pasien" id="kategori_pasien" value="{{ $kategori_awal }}">
                
                <div class="grid grid-cols-3 gap-3 bg-slate-50 p-2.5 rounded-2xl border border-slate-100">
                    <div class="tab-btn {{ $kategori_awal == 'balita' ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:bg-slate-200/50' }} flex items-center justify-center gap-2.5 py-4 rounded-xl font-extrabold text-xs uppercase tracking-widest cursor-pointer transition-all duration-300" data-target="balita">
                        <i class="fas fa-child text-sm"></i> <span class="hidden sm:inline">Balita (12-59 Bln)</span><span class="sm:hidden">Balita</span>
                    </div>
                    <div class="tab-btn {{ $kategori_awal == 'remaja' ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:bg-slate-200/50' }} flex items-center justify-center gap-2.5 py-4 rounded-xl font-extrabold text-xs uppercase tracking-widest cursor-pointer transition-all duration-300" data-target="remaja">
                        <i class="fas fa-user-graduate text-sm"></i> <span class="hidden sm:inline">Remaja</span><span class="sm:hidden">Remaja</span>
                    </div>
                    <div class="tab-btn {{ $kategori_awal == 'lansia' ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:bg-slate-200/50' }} flex items-center justify-center gap-2.5 py-4 rounded-xl font-extrabold text-xs uppercase tracking-widest cursor-pointer transition-all duration-300" data-target="lansia">
                        <i class="fas fa-wheelchair text-sm"></i> <span class="hidden sm:inline">Lansia</span><span class="sm:hidden">Lansia</span>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100 mb-10">

            {{-- BAGIAN 2: IDENTITAS & TANGGAL --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 mb-10">
                
                {{-- Modul Live Search (Z-INDEX 50) --}}
                <div class="md:col-span-8 relative z-[50]">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-3 py-1 rounded-lg uppercase tracking-widest">Langkah 2</span>
                        <label class="text-sm font-extrabold text-slate-800 uppercase tracking-widest">Cari Identitas Warga <span class="text-rose-500">*</span></label>
                    </div>
                    
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                        <input type="text" id="pasien_search" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-4 py-4 font-bold text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="Ketik minimal 2 huruf nama atau NIK warga..." autocomplete="off">
                        <input type="hidden" name="pasien_id" id="pasien_id">
                    </div>

                    {{-- PORTAL DROPDOWN (Z-INDEX 9999 ABSOLUTE) --}}
                    <div id="comboMenu" class="absolute top-[105%] left-0 w-full bg-white border border-slate-200 rounded-2xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] custom-scroll max-h-[300px] overflow-y-auto hidden z-[9999]"></div>
                </div>

                {{-- Modul Tanggal (Z-INDEX 10) --}}
                <div class="md:col-span-4 relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-3 py-1 rounded-lg uppercase tracking-widest">Langkah 3</span>
                        <label class="text-sm font-extrabold text-slate-800 uppercase tracking-widest">Tanggal <span class="text-rose-500">*</span></label>
                    </div>
                    <input type="date" name="tanggal_periksa" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300 cursor-pointer" max="{{ date('Y-m-d') }}">
                </div>
            </div>

            <hr class="border-slate-100 mb-10">

            {{-- BAGIAN 3: WORKSPACE PARAMETER FISIK --}}
            <div class="relative z-10 mb-10">
                <div class="flex items-center gap-3 mb-8">
                    <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-3 py-1 rounded-lg uppercase tracking-widest">Langkah 4</span>
                    <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-widest" id="headerTitle">Form Parameter Pengukuran</h2>
                </div>

                {{-- WORKSPACE: BALITA --}}
                <div id="box_balita" class="workspace-area">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Berat Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="berat_badan" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-black text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-emerald-600 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">KG</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Tinggi Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="tinggi_badan" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-black text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-emerald-600 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Lingkar Kepala</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_kepala" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-black text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-emerald-600 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">LiLA (Lengan)</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_lengan" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-black text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-emerald-600 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">CM</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- WORKSPACE: REMAJA --}}
                <div id="box_remaja" class="workspace-area hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Berat Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="bb_remaja" name="berat_badan" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-black text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-emerald-600 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">KG</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Tinggi Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="tb_remaja" name="tinggi_badan" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-black text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-emerald-600 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Tensi Darah</label>
                            <input type="text" name="tekanan_darah" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-black font-mono text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="120/80">
                        </div>
                        
                        {{-- Panel IMT Soft Emerald --}}
                        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 flex flex-col justify-center items-center shadow-inner h-full min-h-[85px]">
                            <span class="text-[9px] font-extrabold text-emerald-600 uppercase tracking-widest mb-1">Indeks Massa Tubuh</span>
                            <div id="imt_remaja_screen" class="text-3xl font-black text-emerald-700 leading-none">0.0</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Gula Darah</label>
                            <div class="relative">
                                <input type="number" name="gula_darah" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-20 py-4 font-bold text-base text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-slate-500 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">mg/dL</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">LiLA (Lengan Atas)</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="lila_remaja" name="lingkar_lengan" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-bold text-base text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-slate-500 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Lingkar Perut</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_perut" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-bold text-base text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-slate-500 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Hemoglobin (Hb)</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="hemoglobin" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-bold text-base text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-slate-500 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">g/dL</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- WORKSPACE: LANSIA --}}
                <div id="box_lansia" class="workspace-area hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Berat Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="bb_lansia" name="berat_badan" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-black text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-emerald-600 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">KG</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Tinggi Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="tb_lansia" name="tinggi_badan" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-black text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-emerald-600 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Tensi Darah</label>
                            <input type="text" name="tekanan_darah" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-black font-mono text-xl text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="130/80">
                        </div>
                        
                        {{-- Panel IMT Soft Emerald --}}
                        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 flex flex-col justify-center items-center shadow-inner h-full min-h-[85px]">
                            <span class="text-[9px] font-extrabold text-emerald-600 uppercase tracking-widest mb-1">Indeks Massa Tubuh</span>
                            <div id="imt_lansia_screen" class="text-3xl font-black text-emerald-700 leading-none">0.0</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Gula Darah</label>
                            <div class="relative">
                                <input type="number" name="gula_darah" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-20 py-4 font-bold text-base text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-slate-500 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">mg/dL</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Kolesterol</label>
                            <div class="relative">
                                <input type="number" name="kolesterol" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-20 py-4 font-bold text-base text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-slate-500 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">mg/dL</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Asam Urat</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="asam_urat" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-20 py-4 font-bold text-base text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-slate-500 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">mg/dL</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Lingkar Perut</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_perut" class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-5 pr-16 py-4 font-bold text-base text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-white text-slate-500 border border-slate-200 font-extrabold text-[10px] px-2.5 py-1 rounded-lg pointer-events-none shadow-sm">CM</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ALERT UNIVERSAL KEK --}}
                <div id="warn_kek" class="bg-amber-50 border border-amber-200 p-5 rounded-2xl mt-6 hidden items-center gap-4 shadow-sm">
                    <div class="bg-amber-100 text-amber-600 w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                        <i class="fas fa-exclamation-triangle text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-amber-800 font-extrabold text-xs uppercase tracking-widest mb-1">Peringatan KEK</h3>
                        <p class="text-amber-700 font-medium text-xs leading-relaxed">Nilai LiLA di bawah 23.5 cm terindikasi Risiko Kurang Energi Kronis. Data penandaan akan otomatis diteruskan ke Bidan Desa.</p>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100 mb-8">

            {{-- BAGIAN 4: CATATAN TAMBAHAN --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Keluhan Utama</label>
                    <textarea name="keluhan" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-5 font-semibold text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300 resize-none h-24" placeholder="Tuliskan keluhan subjektif warga..."></textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Catatan Kader</label>
                    <textarea name="catatan_kader" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-5 font-semibold text-slate-800 text-sm focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 transition-all duration-300 resize-none h-24" placeholder="Pesan singkat dari kader untuk Bidan..."></textarea>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-col sm:flex-row justify-end gap-4 mt-8">
            <a href="{{ route('kader.pemeriksaan.index') }}" class="px-8 py-4 rounded-xl bg-white border border-slate-200 text-slate-600 font-extrabold text-center hover:bg-slate-50 hover:text-slate-800 transition-all uppercase tracking-widest text-[11px] shadow-sm">
                Batalkan
            </a>
            <button type="submit" id="btnSubmit" class="px-10 py-4 rounded-xl bg-emerald-600 text-white font-extrabold text-center hover:bg-emerald-700 shadow-lg shadow-emerald-600/30 transition-all uppercase tracking-widest text-[11px] flex justify-center items-center gap-2 hover:-translate-y-0.5 border border-transparent">
                <i class="fas fa-save text-sm"></i> Simpan Data Pengukuran
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // --- 1. TAB ENGINE (AMAN DARI BUG SUBMIT) ---
        const tabs = document.querySelectorAll('.tab-btn');
        const inputKategori = document.getElementById('kategori_pasien');
        const headerTitle = document.getElementById('headerTitle');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => {
                    t.classList.remove('bg-white', 'text-emerald-600', 'shadow-sm', 'ring-1', 'ring-slate-200');
                    t.classList.add('text-slate-500', 'hover:bg-slate-200/50');
                });
                this.classList.remove('text-slate-500', 'hover:bg-slate-200/50');
                this.classList.add('bg-white', 'text-emerald-600', 'shadow-sm', 'ring-1', 'ring-slate-200');
                
                const target = this.dataset.target;
                inputKategori.value = target;

                const titles = { balita: 'Antropometri Balita (12-59 Bulan)', remaja: 'Skrining Fisik Remaja', lansia: 'Skrining Fisik Lansia' };
                headerTitle.textContent = titles[target];

                // Mematikan form yang tersembunyi agar payload bersih
                document.querySelectorAll('.workspace-area').forEach(wa => {
                    wa.classList.add('hidden');
                    wa.querySelectorAll('input').forEach(input => input.disabled = true);
                });

                const activeWorkspace = document.getElementById('box_' + target);
                activeWorkspace.classList.remove('hidden');
                activeWorkspace.querySelectorAll('input').forEach(input => input.disabled = false);

                // Reset field pasien
                document.getElementById('pasien_id').value = '';
                document.getElementById('pasien_search').value = '';
                document.getElementById('warn_kek').classList.add('hidden');
                document.getElementById('warn_kek').classList.remove('flex');
            });
        });

        // Inisialisasi Kategori
        const initCat = inputKategori.value || 'balita';
        const activeTab = Array.from(tabs).find(t => t.dataset.target === initCat);
        if (activeTab) activeTab.click();


        // --- 2. LIVE SEARCH DENGAN HIGHLIGHT STABILO ---
        const searchInput = document.getElementById('pasien_search');
        const comboMenu = document.getElementById('comboMenu');
        let debounceTimer;

        // Fungsi memberi highlight (stabilo) pada kata yang diketik
        function highlightTeks(teks, query) {
            if (!teks) return '-';
            const regex = new RegExp(`(${query})`, "gi");
            return teks.replace(regex, "<span class='bg-emerald-200 text-emerald-900 px-0.5 rounded'>$1</span>");
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            const kat = inputKategori.value;

            // Reset ID Tersembunyi saat ngetik
            document.getElementById('pasien_id').value = '';

            if (query.length < 2) {
                comboMenu.classList.add('hidden');
                return;
            }

            // Memunculkan status loading (Cepat)
            comboMenu.innerHTML = '<div class="p-6 text-center text-slate-400 text-sm font-semibold"><i class="fas fa-spinner fa-spin mr-2"></i>Mencari warga...</div>';
            comboMenu.classList.remove('hidden');

            debounceTimer = setTimeout(async () => {
                try {
                    const response = await fetch(`{{ route('kader.pemeriksaan.api') }}?kategori=${kat}&search=${encodeURIComponent(query)}`);
                    const res = await response.json();
                    
                    if (res.status === 'success' && res.data.length > 0) {
                        comboMenu.innerHTML = res.data.map(p => {
                            // Proteksi Variabel dari Server
                            const nama = p.nama || p.nama_lengkap || '-';
                            const nik = p.nik || '-';
                            
                            return `
                            <div class="px-5 py-4 hover:bg-emerald-50 border-b border-slate-100 cursor-pointer transition-colors flex justify-between items-center portal-option group" data-id="${p.id}" data-nama="${nama}">
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">${highlightTeks(nama, query)} ${p.is_hadir ? '<span class="ml-2 bg-emerald-100 text-emerald-700 text-[9px] px-2 py-0.5 rounded-md font-extrabold uppercase tracking-wider">Hadir Hari Ini</span>' : ''}</div>
                                    <div class="text-[11px] font-semibold text-slate-400 mt-1">NIK: ${highlightTeks(nik, query)}</div>
                                </div>
                                <i class="fas fa-check-circle text-emerald-500 text-sm opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </div>
                        `}).join('');
                        
                        // Handler Klik List
                        document.querySelectorAll('.portal-option').forEach(row => {
                            row.addEventListener('click', function() {
                                document.getElementById('pasien_id').value = this.dataset.id;
                                document.getElementById('pasien_search').value = this.dataset.nama;
                                comboMenu.classList.add('hidden');
                            });
                        });
                    } else {
                        comboMenu.innerHTML = '<div class="p-6 text-center text-rose-400 text-sm font-bold">Nama atau NIK tidak ditemukan.</div>';
                    }
                } catch (e) {
                    comboMenu.innerHTML = '<div class="p-6 text-center text-slate-400 text-sm font-bold">Terjadi gangguan jaringan.</div>';
                }
            }, 300);
        });

        // Tutup jika klik diluar
        document.addEventListener('click', function(e) {
            if (!comboMenu.contains(e.target) && e.target !== searchInput) {
                comboMenu.classList.add('hidden');
            }
        });


        // --- 3. KALKULATOR IMT REAL-TIME ---
        function runImt(bbId, tbId, screenId) {
            const bb = parseFloat(document.getElementById(bbId).value);
            const tb = parseFloat(document.getElementById(tbId).value) / 100;
            const display = document.getElementById(screenId);

            if (bb > 0 && tb > 0) {
                display.textContent = (bb / (tb * tb)).toFixed(1);
            } else {
                display.textContent = '0.0';
            }
        }

        document.getElementById('bb_remaja').addEventListener('input', () => runImt('bb_remaja', 'tb_remaja', 'imt_remaja_screen'));
        document.getElementById('tb_remaja').addEventListener('input', () => runImt('bb_remaja', 'tb_remaja', 'imt_remaja_screen'));
        document.getElementById('bb_lansia').addEventListener('input', () => runImt('bb_lansia', 'tb_lansia', 'imt_lansia_screen'));
        document.getElementById('tb_lansia').addEventListener('input', () => runImt('bb_lansia', 'tb_lansia', 'imt_lansia_screen'));


        // --- 4. PERINGATAN KEK UNIVERSAL (Semua Gender) ---
        const noticeKek = document.getElementById('warn_kek');
        const lilaRemaja = document.getElementById('lila_remaja');

        lilaRemaja.addEventListener('input', function() {
            const lila = parseFloat(this.value);
            // Universal, tidak memandang gender P/L
            if (lila > 0 && lila < 23.5) {
                noticeKek.classList.remove('hidden');
                noticeKek.classList.add('flex');
            } else {
                noticeKek.classList.add('hidden');
                noticeKek.classList.remove('flex');
            }
        });


        // --- 5. ANTI-DOUBLE SUBMIT & PREMIUM SWEETALERT ---
        document.getElementById('formPemeriksaan').addEventListener('submit', function(e) {
            if (!document.getElementById('pasien_id').value) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Identitas Warga Kosong',
                    html: '<p class="text-sm">Silakan ketik dan pilih nama warga pada Langkah 2 terlebih dahulu.</p>',
                    confirmButtonText: 'Baik, Mengerti',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'nexus-swal',
                        title: 'nexus-swal-title',
                        htmlContainer: 'nexus-swal-text',
                        confirmButton: 'w-full bg-emerald-600 text-white font-bold py-3.5 rounded-xl hover:bg-emerald-700 transition-colors mt-4 uppercase tracking-widest text-xs shadow-md'
                    }
                });
                return;
            }

            Swal.fire({
                title: 'Menyimpan Rekam Medis',
                html: '<p class="text-sm">Mohon tunggu, sistem sedang memvalidasi data...</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
                customClass: { popup: 'nexus-swal', title: 'nexus-swal-title', htmlContainer: 'nexus-swal-text' },
                didOpen: () => { Swal.showLoading(); }
            });
            
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        });
    });
</script>
@endpush
@endsection