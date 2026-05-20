@extends('layouts.kader')
@section('title', 'Generator Laporan PDF')
@section('page-name', 'Pusat Arsip Digital')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    /* NEXUS ANIMATION SYSTEM */
    .fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .stagger-1 { animation-delay: 0.1s; } .stagger-2 { animation-delay: 0.15s; } .stagger-3 { animation-delay: 0.2s; }
    .stagger-4 { animation-delay: 0.25s; } .stagger-5 { animation-delay: 0.3s; }

    /* CLEAN NEXUS CARDS */
    .report-card { 
        background: #ffffff; 
        border: 1px solid #f1f5f9; 
        border-radius: 28px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px -5px rgba(15, 23, 42, 0.03);
    }
    .report-card:hover { 
        transform: translateY(-6px); 
        box-shadow: 0 25px 50px -12px rgba(99, 102, 241, 0.12); 
        border-color: #e2e8f0; z-index: 10;
    }
    
    /* CLEAN CAPSULE SELECTOR */
    .select-capsule { 
        appearance: none; width: 100%; cursor: pointer;
        background-color: #f8fafc; border: 1px solid #e2e8f0; color: #334155;
        font-size: 0.8rem; font-weight: 800; border-radius: 9999px; padding: 0.8rem 2.5rem 0.8rem 1.25rem;
        transition: all 0.3s ease; 
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); 
        background-repeat: no-repeat; background-position: right 1rem center; background-size: 1rem; 
    }
    .select-capsule:focus { background-color: #ffffff; border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); outline: none; }
    
    /* SOFT BUTTON GENERATE */
    .btn-generate { 
        width: 100%; border-radius: 9999px; padding: 1rem; font-weight: 800; font-size: 0.75rem; 
        text-transform: uppercase; letter-spacing: 0.05em; transition: all 0.3s ease; 
        display: flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid transparent;
    }

    /* ==========================================================
       SWEETALERT NEXUS OVERRIDE (ANTI GAGAL)
       ========================================================== */
    .swal2-container.nexus-backdrop { 
        backdrop-filter: blur(8px) !important; 
        background: rgba(15, 23, 42, 0.5) !important; 
    }
    .swal2-popup.nexus-popup {
        border-radius: 36px !important; /* Memaksa sudut bulat penuh */
        padding: 2.5rem 2rem !important;
        background: rgba(255, 255, 255, 0.98) !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 25px 60px -15px rgba(0,0,0,0.15) !important;
        width: 28em !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1300px] mx-auto fade-in-up pb-16 relative z-10">

    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-gradient-to-br from-indigo-50 to-blue-50/30 rounded-full blur-3xl pointer-events-none z-0"></div>

    {{-- HEADER BANNER --}}
    <div class="bg-white/80 backdrop-blur-xl rounded-[36px] border border-white p-8 md:p-12 mb-10 relative overflow-hidden shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] flex flex-col lg:flex-row items-center justify-between gap-8 z-10">
        <div class="absolute -left-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute right-10 -bottom-10 w-40 h-40 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6 w-full">
            <div class="w-20 h-20 rounded-[24px] bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-4xl shrink-0 shadow-sm transform -rotate-3 hover:rotate-0 transition-all duration-300">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="flex-1 text-center sm:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-600 text-white text-[9px] font-black uppercase tracking-[0.15em] rounded-full mb-3 shadow-sm">
                    <i class="fas fa-check-circle"></i> Mesin Cetak Standar Kemenkes
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight font-poppins mb-2">Pusat Cetak Laporan</h1>
                <p class="text-slate-500 font-medium text-[13.5px] max-w-2xl leading-relaxed">
                    Arsip digital terintegrasi. Pilih instrumen laporan dan tentukan periode waktu, sistem akan menyusun data rekam medis menjadi dokumen PDF resmi secara otomatis.
                </p>
            </div>
        </div>
    </div>

    @php
        $reports = [
            ['type' => 'balita', 'title' => 'Laporan Balita', 'desc' => 'Tumbuh Kembang & Status Gizi', 'icon' => 'fa-baby', 'base' => 'sky'],
            ['type' => 'remaja', 'title' => 'Laporan Remaja', 'desc' => 'Skrining Kesehatan PTM', 'icon' => 'fa-user-graduate', 'base' => 'indigo'],
            ['type' => 'lansia', 'title' => 'Laporan Lansia', 'desc' => 'Pemeriksaan Tensi & Lab', 'icon' => 'fa-wheelchair', 'base' => 'emerald'],
            ['type' => 'imunisasi', 'title' => 'Laporan Imunisasi', 'desc' => 'Rekapitulasi Vaksin Warga', 'icon' => 'fa-shield-virus', 'base' => 'violet'],
        ];
        $currentMonth = date('m');
        $currentYear = date('Y');
    @endphp

    {{-- GRID KARTU LAPORAN --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 relative z-10 justify-center">
        @foreach($reports as $index => $r)
        @php $staggerClass = 'stagger-' . (($index % 5) + 1); @endphp
        
        <div class="report-card p-8 flex flex-col group {{ $staggerClass }}">
            <div class="flex items-center gap-5 mb-8">
                <div class="w-16 h-16 rounded-[20px] bg-{{ $r['base'] }}-50 text-{{ $r['base'] }}-500 flex items-center justify-center text-3xl border border-{{ $r['base'] }}-100 shrink-0 group-hover:scale-110 transition-transform duration-300 shadow-sm">
                    <i class="fas {{ $r['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="font-black text-slate-800 text-[18px] font-poppins leading-tight">{{ $r['title'] }}</h3>
                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">{{ $r['desc'] }}</p>
                </div>
            </div>

            <form action="{{ route('kader.laporan.generate') }}" method="GET" class="mt-auto flex flex-col gap-5">
                <input type="hidden" name="type" value="{{ $r['type'] }}">
                
                <div class="flex items-center gap-3">
                    <div class="w-3/5 relative">
                        <select name="bulan" class="select-capsule w-full">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month((int)$m)->locale('id')->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-2/5 relative">
                        <select name="tahun" class="select-capsule w-full pl-4 pr-8">
                            @foreach(range($currentYear-2, $currentYear) as $y)
                                <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" onclick="tampilkanLoading('{{ $r['title'] }}', '{{ $r['base'] }}')" class="btn-generate bg-slate-50 text-slate-600 group-hover:bg-{{ $r['base'] }}-500 group-hover:text-white group-hover:shadow-[0_10px_20px_rgba(0,0,0,0.15)] mt-2">
                    <i class="fas fa-file-pdf"></i> Unduh PDF Dokumen
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. Pesan Data Kosong (Sangat Membulat)
    @if(session('error'))
        Swal.fire({
            html: `
                <div class="flex flex-col items-center justify-center text-center p-4">
                    <div class="w-20 h-20 bg-rose-50 rounded-[24px] flex items-center justify-center mb-6 border border-rose-100 shadow-sm">
                        <i class="fas fa-folder-open text-rose-500 text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 font-poppins tracking-tight mb-3">Data Kosong</h2>
                    <p class="text-[13px] text-slate-500 font-medium leading-relaxed max-w-xs mx-auto">
                        {{ session("error") }}
                    </p>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Kembali',
            buttonsStyling: false,
            customClass: { 
                container: 'nexus-backdrop',
                popup: 'nexus-popup',
                confirmButton: 'bg-slate-800 hover:bg-slate-900 text-white font-bold text-[12px] uppercase tracking-widest px-8 py-3.5 rounded-full transition-all mt-4 w-full shadow-md'
            }
        });
    @endif

    // 2. Loading State (MUTLAK HALUS, TANPA SPINNER DEFAULT)
    function tampilkanLoading(judul, warna) {
        
        // Peta Warna Tailwind Khusus
        const colorMap = {
            'sky': 'border-sky-500 text-sky-500',
            'pink': 'border-pink-500 text-pink-500',
            'indigo': 'border-indigo-500 text-indigo-500',
            'emerald': 'border-emerald-500 text-emerald-500',
            'violet': 'border-violet-500 text-violet-500',
            'amber': 'border-amber-500 text-amber-500'
        };
        const tc = colorMap[warna] || 'border-indigo-500 text-indigo-500';
        const borderColor = tc.split(' ')[0];
        const textColor = tc.split(' ')[1];

        Swal.fire({
            html: `
                <div class="flex flex-col items-center justify-center text-center p-4">
                    
                    <div class="w-24 h-24 mb-8 relative flex items-center justify-center">
                        <div class="absolute inset-0 border-[4px] border-slate-100 rounded-full"></div>
                        <div class="absolute inset-0 border-[4px] ${borderColor} border-t-transparent rounded-full animate-spin"></div>
                        <i class="fas fa-file-pdf ${textColor} text-3xl animate-pulse"></i>
                    </div>

                    <h2 class="text-2xl font-black text-slate-800 font-poppins tracking-tight mb-3">Menyusun ${judul}</h2>
                    <p class="text-[13px] text-slate-500 font-medium leading-relaxed max-w-[280px] mx-auto">
                        Sistem sedang memproses data rekam medis ke dalam format PDF Kemenkes...
                    </p>
                </div>
            `,
            allowOutsideClick: false, 
            showConfirmButton: false,
            timer: 3500,
            // HAPUS didOpen: Swal.showLoading() AGAR SPINNER DEFAULT MATI
            customClass: { 
                container: 'nexus-backdrop',
                popup: 'nexus-popup'
            }
        });
    }
</script>
@endpush
@endsection