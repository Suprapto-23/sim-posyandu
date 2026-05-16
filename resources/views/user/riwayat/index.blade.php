@extends('layouts.user')

@section('title', 'Riwayat Rekam Medis')

@push('styles')
<style>
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { 
        from { opacity: 0; transform: translateY(30px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto pb-32 px-4 md:px-8 font-poppins mobile-padding-bottom">
    
    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 md:mb-10 animate-slide-up">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-teal-50 text-teal-600 text-[10px] font-black uppercase tracking-[0.15em] mb-4 border border-teal-100 shadow-sm">
                <i class="fas fa-folder-open"></i> Arsip Kesehatan Terpadu
            </div>
            <h1 class="text-2xl md:text-4xl font-black text-slate-800 tracking-tight leading-tight mb-2">Rekam Medis Keluarga 🏥</h1>
            <p class="text-[13px] md:text-sm font-medium text-slate-500 leading-relaxed">
                Pusat arsip seluruh hasil pemeriksaan Anda dan keluarga yang telah divalidasi resmi oleh Bidan Posyandu.
            </p>
        </div>
    </div>

    {{-- 2. TIMELINE / DAFTAR RIWAYAT (Langsung ke konten utama) --}}
    <div class="space-y-6">
        @forelse($riwayat as $index => $item)
            @php
                // Pencocokan Data Pasien
                $pasien = collect($targets)->first(function($t) use ($item) {
                    $kat = $t['kat'] ?? $t['kategori'] ?? '';
                    return $t['id'] == $item->pasien_id && $kat == $item->kategori_pasien;
                });
                
                $namaPasien = $pasien ? $pasien['nama'] : 'Pasien Tidak Diketahui';
                $katPasien = $pasien ? ($pasien['kat'] ?? $pasien['kategori'] ?? 'umum') : $item->kategori_pasien;

                // Tema Warna Dinamis & Ikon
                $cardBorder = 'border-slate-100 hover:border-slate-300';
                $iconBg = 'bg-slate-50 text-slate-500';
                $icon = 'fa-file-medical';
                $kategoriLabel = 'Pemeriksaan Umum';
                $glowColor = 'slate';

                if($katPasien == 'balita') { 
                    $cardBorder = 'border-teal-100 hover:border-teal-300 hover:shadow-teal-100/40'; 
                    $iconBg = 'bg-gradient-to-tr from-teal-400 to-emerald-500 text-white'; 
                    $icon = 'fa-baby'; 
                    $kategoriLabel = 'Tumbuh Kembang Anak'; 
                    $glowColor = 'teal';
                }
                elseif($katPasien == 'ibu_hamil' || $katPasien == 'bumil') { 
                    $cardBorder = 'border-rose-100 hover:border-rose-300 hover:shadow-rose-100/40'; 
                    $iconBg = 'bg-gradient-to-tr from-rose-400 to-purple-500 text-white'; 
                    $icon = 'fa-person-pregnant'; 
                    $kategoriLabel = 'Pemeriksaan Kehamilan'; 
                    $glowColor = 'rose';
                }
                elseif($katPasien == 'remaja') { 
                    $cardBorder = 'border-indigo-100 hover:border-indigo-300 hover:shadow-indigo-100/40'; 
                    $iconBg = 'bg-gradient-to-tr from-indigo-500 to-sky-400 text-white'; 
                    $icon = 'fa-person-snowboarding'; 
                    $kategoriLabel = 'Cek Fisik Remaja'; 
                    $glowColor = 'indigo';
                }
                elseif($katPasien == 'lansia') { 
                    $cardBorder = 'border-amber-100 hover:border-amber-300 hover:shadow-amber-100/40'; 
                    $iconBg = 'bg-gradient-to-tr from-amber-400 to-orange-500 text-white'; 
                    $icon = 'fa-person-cane'; 
                    $kategoriLabel = 'Pemantauan Lansia'; 
                    $glowColor = 'amber';
                }

                // Kalkulasi Delay Animasi
                $delay = min($index * 0.1 + 0.1, 0.8);
            @endphp

            <div class="bg-white rounded-[2rem] border {{ $cardBorder }} p-6 md:p-8 transition-all duration-500 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.02)] hover:shadow-2xl relative overflow-hidden group animate-slide-up" style="animation-delay: {{ $delay }}s;">
                
                {{-- Watermark Ikon Latar Belakang --}}
                <div class="absolute -right-10 -bottom-10 opacity-[0.03] group-hover:scale-110 group-hover:rotate-12 transition-transform duration-700 pointer-events-none">
                    <i class="fas {{ $icon }} text-[12rem] text-{{ $glowColor }}-900"></i>
                </div>

                <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 relative z-10">
                    
                    {{-- IDENTITAS (Kiri) --}}
                    <div class="lg:w-1/3 shrink-0 lg:border-r border-slate-100 lg:pr-8 flex flex-col justify-center">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-14 h-14 rounded-[1.2rem] {{ $iconBg }} flex items-center justify-center text-xl shadow-lg shadow-{{ $glowColor }}-500/30 border-2 border-white shrink-0 group-hover:scale-105 transition-transform duration-500">
                                <i class="fas {{ $icon }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ $kategoriLabel }}</p>
                                <h3 class="text-base md:text-lg font-black text-slate-800 tracking-tight truncate">{{ $namaPasien }}</h3>
                            </div>
                        </div>
                        
                        <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-100 flex items-center justify-between">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Tanggal Periksa</p>
                                <span class="text-[12px] font-bold text-slate-700">{{ \Carbon\Carbon::parse($item->tanggal_periksa)->translatedFormat('d F Y') }}</span>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm shadow-inner" title="Divalidasi Bidan">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>

                    {{-- METRIK & KETERANGAN (Kanan) --}}
                    <div class="flex-1">
                        
                        {{-- Grid Metrik --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 mb-5">
                            
                            @if($item->berat_badan)
                                <div class="bg-slate-50/70 p-3.5 rounded-[1.2rem] border border-slate-100 hover:border-slate-300 transition-colors">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Berat</p>
                                    <p class="text-[14px] font-black text-slate-800">{{ $item->berat_badan }} <span class="text-[9px] text-slate-500 font-bold">kg</span></p>
                                </div>
                            @endif

                            @if($item->tinggi_badan)
                                <div class="bg-slate-50/70 p-3.5 rounded-[1.2rem] border border-slate-100 hover:border-slate-300 transition-colors">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Tinggi</p>
                                    <p class="text-[14px] font-black text-slate-800">{{ $item->tinggi_badan }} <span class="text-[9px] text-slate-500 font-bold">cm</span></p>
                                </div>
                            @endif

                            @if($item->tekanan_darah)
                                <div class="bg-rose-50/50 p-3.5 rounded-[1.2rem] border border-rose-100 hover:border-rose-200 transition-colors">
                                    <p class="text-[8px] font-black text-rose-400 uppercase tracking-widest mb-1">Tensi</p>
                                    <p class="text-[14px] font-black text-rose-700">{{ $item->tekanan_darah }}</p>
                                </div>
                            @endif

                            @if($item->gula_darah)
                                <div class="bg-sky-50/50 p-3.5 rounded-[1.2rem] border border-sky-100 hover:border-sky-200 transition-colors">
                                    <p class="text-[8px] font-black text-sky-500 uppercase tracking-widest mb-1">Gula Darah</p>
                                    <p class="text-[14px] font-black text-sky-700">{{ $item->gula_darah }} <span class="text-[9px] text-sky-500 font-bold">mg/dL</span></p>
                                </div>
                            @endif

                            @if($item->hemoglobin || $item->hb)
                                <div class="bg-indigo-50/50 p-3.5 rounded-[1.2rem] border border-indigo-100 hover:border-indigo-200 transition-colors">
                                    <p class="text-[8px] font-black text-indigo-400 uppercase tracking-widest mb-1">Status HB</p>
                                    <p class="text-[14px] font-black text-indigo-700">{{ $item->hemoglobin ?? $item->hb }}</p>
                                </div>
                            @endif

                            @if($item->tfu)
                                <div class="bg-amber-50/50 p-3.5 rounded-[1.2rem] border border-amber-100 hover:border-amber-200 transition-colors">
                                    <p class="text-[8px] font-black text-amber-500 uppercase tracking-widest mb-1">TFU</p>
                                    <p class="text-[14px] font-black text-amber-700">{{ $item->tfu }} <span class="text-[9px] text-amber-600 font-bold">cm</span></p>
                                </div>
                            @endif

                            @if($item->lingkar_kepala)
                                <div class="bg-violet-50/50 p-3.5 rounded-[1.2rem] border border-violet-100 hover:border-violet-200 transition-colors">
                                    <p class="text-[8px] font-black text-violet-500 uppercase tracking-widest mb-1">Ling. Kepala</p>
                                    <p class="text-[14px] font-black text-violet-700">{{ $item->lingkar_kepala }} <span class="text-[9px] text-violet-600 font-bold">cm</span></p>
                                </div>
                            @endif

                        </div>

                        {{-- Catatan & Status Gizi --}}
                        @if($item->keterangan || $item->status_gizi)
                            <div class="bg-slate-50/80 border border-slate-100 rounded-[1.2rem] p-4 flex flex-col sm:flex-row gap-4">
                                @if($item->status_gizi)
                                    <div class="shrink-0">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Status Gizi</p>
                                        @php
                                            $gizi = strtolower($item->status_gizi);
                                            $giziClass = str_contains($gizi, 'baik') || str_contains($gizi, 'normal') ? 'bg-emerald-100 text-emerald-700 border-emerald-200' :
                                                        (str_contains($gizi, 'kurang') ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-rose-100 text-rose-700 border-rose-200');
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border {{ $giziClass }}">
                                            {{ $item->status_gizi }}
                                        </span>
                                    </div>
                                @endif
                                
                                @if($item->keterangan)
                                    <div class="{{ $item->status_gizi ? 'sm:border-l sm:border-slate-200 sm:pl-4' : '' }} flex-1 min-w-0">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Catatan Bidan</p>
                                        <p class="text-[12px] font-medium text-slate-600 italic leading-relaxed">"{{ $item->keterangan }}"</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @empty
            {{-- EMPTY STATE --}}
            <div class="py-20 flex flex-col items-center justify-center text-center bg-white rounded-[3rem] border border-slate-100 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.03)] animate-slide-up" style="animation-delay: 0.1s;">
                <div class="relative w-24 h-24 mb-6 group cursor-default">
                    <div class="absolute inset-0 bg-teal-100 rounded-[1rem] rotate-12 opacity-50 group-hover:rotate-[24deg] transition-transform duration-500"></div>
                    <div class="absolute inset-0 bg-white border border-slate-100 rounded-[1.2rem] flex items-center justify-center text-slate-300 shadow-sm transition-transform duration-500 group-hover:scale-105">
                        <i class="fas fa-folder-open text-4xl"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">Belum Ada Rekam Medis</h3>
                <p class="text-[13px] font-medium text-slate-500 mt-2 max-w-sm mx-auto leading-relaxed">
                    Kami belum menemukan riwayat pemeriksaan yang divalidasi oleh Bidan untuk NIK Anda atau keluarga.
                </p>
            </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if($riwayat->hasPages())
        <div class="mt-10 animate-slide-up" style="animation-delay: 0.5s;">
            {{ $riwayat->links() }}
        </div>
    @endif

</div>
@endsection