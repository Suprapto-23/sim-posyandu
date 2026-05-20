@extends('layouts.bidan')

@section('title', 'Register Imunisasi Terpadu')
@section('page-name', 'Buku Imunisasi')

@push('styles')
<style>
    /* NEXUS CLINICAL ANIMATION SYSTEM */
    .fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* GLASS PANEL UI */
    .nexus-glass { 
        background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); 
        border: 1px solid rgba(226, 232, 240, 0.8); 
        box-shadow: 0 10px 40px -10px rgba(6, 182, 212, 0.05); 
        border-radius: 28px; transition: all 0.4s ease;
    }

    /* TABLE MICRO-INTERACTION */
    .tr-nexus { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-bottom: 1px solid #f1f5f9; }
    .tr-nexus:hover { background-color: #f0fdfa; transform: scale(1.002); z-index: 10; position: relative; border-color: transparent; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(6,182,212,0.1); }
    
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="max-w-[1400px] mx-auto relative pb-16 fade-in-up">

    {{-- =================================================================
         1. HERO HEADER (CLINICAL STYLE)
         ================================================================= --}}
    <div class="bg-gradient-to-r from-cyan-600 to-blue-700 rounded-[32px] p-8 md:p-10 mb-8 relative overflow-hidden shadow-[0_15px_40px_-10px_rgba(8,145,178,0.4)] border border-cyan-400/50 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PHBhdGggZD0iTTAgMGgyMHYyMEgwem0xMCAxMGgxMHYxMEgxMHoiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMSIvPjwvc3ZnPg==')]"></div>
        <div class="absolute -right-10 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-10 pointer-events-none hidden lg:block transform rotate-12">
            <i class="fas fa-shield-virus text-[140px] text-white"></i>
        </div>
        
        <div class="flex items-center gap-6 relative z-10 text-white w-full md:w-auto">
            <div class="w-20 h-20 rounded-[22px] bg-white/20 backdrop-blur-md flex items-center justify-center text-4xl shrink-0 shadow-inner border border-white/30 transform -rotate-3">
                <i class="fas fa-syringe text-white"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-1.5">
                    <span class="flex h-2.5 w-2.5 relative"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-300 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-cyan-400"></span></span>
                    <h1 class="text-3xl font-black tracking-tight font-poppins">Buku Register Imunisasi</h1>
                </div>
                <p class="text-[14px] font-medium text-cyan-100 max-w-xl leading-relaxed">
                    Pusat pencatatan riwayat pemberian imunisasi dasar Balita . Seluruh data terintegrasi secara otomatis ke EMR KIA.
                </p>
            </div>
        </div>

        <a href="{{ route('bidan.imunisasi.create') }}" class="relative z-10 inline-flex items-center justify-center gap-3 px-8 py-4 bg-white text-cyan-700 text-[13px] font-black uppercase tracking-widest rounded-2xl hover:bg-cyan-50 transition-all shadow-xl hover:-translate-y-1 group w-full md:w-auto shrink-0 whitespace-nowrap">
            <i class="fas fa-plus-circle text-lg text-amber-500 group-hover:rotate-90 transition-transform duration-500"></i> Catat Imunisasi
        </a>
    </div>

    {{-- =================================================================
         2. WORKSPACE AREA (SERVER-SIDE SEARCH ENGINE)
         ================================================================= --}}
    <div class="nexus-glass overflow-hidden flex flex-col">
        
        <div class="px-6 md:px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <h3 class="text-[16px] font-black text-slate-800 font-poppins flex items-center gap-2">
                <i class="fas fa-clipboard-list text-cyan-500"></i> Riwayat Imunisasi Warga
            </h3>
            
            {{-- Form Search --}}
            <form id="searchForm" method="GET" action="{{ route('bidan.imunisasi.index') }}" class="relative w-full sm:w-[400px]" x-data="{ searchQuery: '{{ request('search') }}' }">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <i class="fas fa-search text-cyan-500" :class="searchQuery.length > 0 ? 'animate-bounce' : ''"></i>
                </div>
                
                <input type="text" name="search" x-model="searchQuery" 
                       placeholder="Cari nama warga, vaksin, NIK... (Tekan Enter)" 
                       class="w-full bg-white border border-slate-200 rounded-[16px] pl-12 pr-12 py-3.5 text-[12px] font-bold text-slate-700 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-50 outline-none transition-all shadow-sm">
                
                <a href="{{ route('bidan.imunisasi.index') }}" x-show="searchQuery.length > 0" x-cloak
                   class="absolute inset-y-0 right-0 pr-5 flex items-center text-rose-400 hover:text-rose-600 transition-colors" title="Bersihkan Pencarian">
                    <i class="fas fa-times-circle text-lg"></i>
                </a>
            </form>
        </div>

        {{-- =================================================================
             3. TABEL DATA IMUNISASI
             ================================================================= --}}
        <div class="overflow-x-auto custom-scrollbar p-2 md:p-4">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr>
                        <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">Identitas Warga</th>
                        <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">Detail Vaksin & Dosis</th>
                        <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">Waktu & Petugas Medis</th>
                        <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100 text-right">Manajemen Arsip</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imunisasis as $imu)
                    @php
                        // Keamanan Data (Failsafe)
                        $pasien = $imu->kunjungan->pasien ?? null;
                        $nama = $pasien->nama_lengkap ?? 'Warga Tidak Diketahui';
                        $kategoriRaw = strtolower(class_basename($imu->kunjungan->pasien_type ?? ''));
                        
                        // Mapping Visual Demografi
                        $config = match($kategoriRaw) {
                            'balita', 'bayi' => ['theme' => 'bg-sky-50 text-sky-600 border-sky-100', 'badge' => 'text-sky-600 border-sky-200', 'ico' => 'fa-baby', 'label' => 'Balita'],
                            default => ['theme' => 'bg-slate-50 text-slate-600 border-slate-100', 'badge' => 'text-slate-600 border-slate-200', 'ico' => 'fa-user', 'label' => 'Umum'],
                        };
                    @endphp

                    <tr class="tr-nexus group">
                        {{-- Kolom 1: Identitas --}}
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-[14px] {{ $config['theme'] }} flex items-center justify-center shrink-0 border shadow-sm group-hover:scale-110 transition-transform">
                                    <i class="fas {{ $config['ico'] }} text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-black text-slate-800 text-[14px] mb-1 group-hover:text-cyan-600 transition-colors font-poppins">{{ $nama }}</p>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-black uppercase tracking-widest bg-white border px-2 py-0.5 rounded shadow-sm {{ $config['badge'] }}">{{ $config['label'] }}</span>
                                        <span class="text-[10px] font-bold text-slate-400">ID: #{{ str_pad($imu->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Kolom 2: Detail Vaksin & KIPI --}}
                        <td class="py-5 px-6">
                            <div class="flex flex-col items-start gap-2">
                                <span class="text-cyan-700 bg-cyan-50 border border-cyan-100 px-3 py-1 rounded-lg font-black text-[13px] tracking-wide">{{ $imu->vaksin }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded text-[10px] font-bold">Dosis: {{ $imu->dosis }}</span>
                                    
                                    @if($imu->keterangan && $imu->keterangan != '-')
                                        <span class="text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded text-[10px] font-bold flex items-center gap-1" title="{{ $imu->keterangan }}">
                                            <i class="fas fa-exclamation-triangle"></i> KIPI Tercatat
                                        </span>
                                    @else
                                        <span class="text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded text-[10px] font-bold flex items-center gap-1">
                                            <i class="fas fa-check-circle"></i> Aman
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Kolom 3: Waktu & Petugas --}}
                        <td class="py-5 px-6">
                            <p class="font-bold text-slate-700 text-[12px] flex items-center gap-2">
                                <i class="far fa-calendar-check text-cyan-500"></i> {{ \Carbon\Carbon::parse($imu->tanggal_imunisasi)->translatedFormat('d M Y') }}
                            </p>
                            <div class="mt-1.5 inline-flex items-center gap-1.5 px-2 py-1 bg-indigo-50 border border-indigo-100 rounded text-[9px] font-black text-indigo-700 uppercase tracking-widest ml-5">
                                <i class="fas fa-user-nurse text-indigo-400"></i> {{ Str::words($imu->kunjungan->petugas->name ?? 'Sistem', 2, '') }}
                            </div>
                        </td>

                        {{-- Kolom 4: Aksi --}}
                        <td class="py-5 px-6 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <a href="{{ route('bidan.imunisasi.show', $imu->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[10px] uppercase tracking-widest rounded-xl hover:bg-cyan-50 hover:text-cyan-600 hover:border-cyan-200 transition-colors shadow-sm" title="Lihat Sertifikat">
                                    <i class="fas fa-certificate text-[14px]"></i> EMR
                                </a>
                                <form action="{{ route('bidan.imunisasi.destroy', $imu->id) }}" method="POST" onsubmit="return confirm('Hapus catatan imunisasi ini secara permanen?')">
                                    @csrf @method('DELETE')
                                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 transition-all shadow-sm" title="Hapus Data">
                                        <i class="fas fa-trash-alt text-[14px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-24 text-center">
                            @if(request('search'))
                                <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-300 flex items-center justify-center mx-auto mb-3 text-2xl"><i class="fas fa-search-minus"></i></div>
                                <h4 class="text-[15px] font-black text-slate-700 font-poppins">Pencarian Tidak Ditemukan</h4>
                                <p class="text-[12px] text-slate-500 mt-1">Tidak ada data rekam medis yang cocok dengan kata kunci "<b class="text-slate-700">{{ request('search') }}</b>".</p>
                                <a href="{{ route('bidan.imunisasi.index') }}" class="mt-5 inline-block px-5 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">Tampilkan Semua Data</a>
                            @else
                                <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-slate-50 border-2 border-dashed border-slate-200 text-slate-300 mb-6 relative shadow-inner">
                                    <i class="fas fa-syringe text-5xl relative z-10 opacity-50"></i>
                                </div>
                                <h3 class="text-[18px] font-black text-slate-800 font-poppins tracking-wide mb-2">Buku Register Kosong</h3>
                                <p class="text-[13px] font-medium text-slate-500 max-w-sm mx-auto leading-relaxed">
                                    Klik tombol <b class="text-cyan-600">Catat Imunisasi</b> di sudut kanan atas untuk menyimpan riwayat vaksinasi warga.
                                </p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($imunisasis) && $imunisasis->hasPages())
        <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:block">
                Menampilkan <span class="text-slate-800">{{ $imunisasis->firstItem() }}</span> - <span class="text-slate-800">{{ $imunisasis->lastItem() }}</span> dari <span class="text-slate-800">{{ $imunisasis->total() }}</span> Data
            </p>
            <div class="nexus-pagination text-xs">
                {{ $imunisasis->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection