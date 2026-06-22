@extends('layouts.kader')

@section('title', 'Presensi Berhasil')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $absensi = $absensi ?? null;
    $details = $absensi?->details ?? collect();

    $kategori = $absensi?->kategori ?? 'balita';

    $kategoriMenus = [
        'balita' => [
            'label' => 'Balita',
            'icon' => 'fa-child-reaching',
            'tone' => 'emerald',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'tone' => 'amber',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'tone' => 'blue',
        ],
    ];

    if (!array_key_exists($kategori, $kategoriMenus)) {
        $kategori = 'balita';
    }
    
    $currentKategori = $kategoriMenus[$kategori];
    $kategoriLabel = $currentKategori['label'];

    $totalPeserta = $details->count();
    $totalHadir = $details->where('hadir', true)->count();
    $totalTidakHadir = $details->where('hadir', false)->count();

    $kodeAbsensi = $absensi?->kode_absensi ?? 'ABS-' . str_pad($absensi?->id ?? 0, 4, '0', STR_PAD_LEFT);

    $routeHas = fn ($name) => Route::has($name);

    $formatTanggal = function ($date, $format = 'd M Y') {
        if (!$date) return '-';
        try {
            return Carbon::parse($date)->translatedFormat($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    try {
        $tanggalYmd = $absensi?->tanggal_posyandu
            ? Carbon::parse($absensi->tanggal_posyandu)->format('Y-m-d')
            : now('Asia/Jakarta')->toDateString();
    } catch (\Throwable $e) {
        $tanggalYmd = now('Asia/Jakarta')->toDateString();
    }
@endphp

@push('styles')
<style>
    /* Background identik dengan halaman index */
    body { 
        background-color: #f1f5f9; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    
    .widget-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2.5rem; 
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active {
        transform: scale(0.95);
    }

    .success-orb-container {
        position: relative;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 100px;
        height: 100px;
    }

    .success-orb-bg {
        position: absolute;
        inset: 0;
        background: #10b981;
        border-radius: 50%;
        opacity: 0.2;
        animation: pulse-glow 2s infinite ease-in-out;
    }

    .success-orb {
        position: relative;
        width: 76px;
        height: 76px;
        background: linear-gradient(135deg, #14b8a6, #10b981);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 2rem;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        z-index: 10;
        animation: pop-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    @keyframes pulse-glow {
        0%, 100% { transform: scale(1); opacity: 0.2; }
        50% { transform: scale(1.3); opacity: 0; }
    }

    @keyframes pop-in {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="widget-card w-full max-w-2xl p-8 sm:p-12 text-center relative overflow-hidden">
        
        {{-- Dekorasi Latar Belakang Card --}}
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-teal-400/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Icon Sukses --}}
        <div class="success-orb-container mb-6">
            <div class="success-orb-bg"></div>
            <div class="success-orb">
                <i class="fa-solid fa-check"></i>
            </div>
        </div>

        {{-- Teks Utama --}}
        <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            Tersimpan
        </div>
        
        <h1 class="text-3xl sm:text-4xl font-black text-slate-800 tracking-tight mb-3">
            Presensi Berhasil Disimpan.
        </h1>
        
        <p class="text-sm font-medium text-slate-500 max-w-md mx-auto leading-relaxed mb-8">
            Data absensi sasaran <strong class="text-teal-600">{{ $kategoriLabel }}</strong> untuk sesi pertemuan ini telah masuk ke dalam sistem.
        </p>

        {{-- Kotak Ringkasan Singkat --}}
        <div class="bg-slate-50/80 border border-slate-100 rounded-3xl p-5 mb-8 flex flex-wrap justify-center sm:justify-between items-center gap-4 shadow-inner max-w-lg mx-auto">
            <div class="text-center sm:text-left">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Kode Sesi</p>
                <p class="text-sm font-black text-slate-800">{{ $kodeAbsensi }}</p>
            </div>
            <div class="hidden sm:block w-px h-8 bg-slate-200"></div>
            <div class="text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Sasaran</p>
                <p class="text-sm font-black text-slate-800">{{ $totalPeserta }}</p>
            </div>
            <div class="hidden sm:block w-px h-8 bg-slate-200"></div>
            <div class="text-center sm:text-right">
                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-0.5">Hadir</p>
                <p class="text-sm font-black text-emerald-600">{{ $totalHadir }}</p>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex flex-col sm:flex-row justify-center items-center gap-3">
            @if($routeHas('kader.absensi.riwayat'))
                <a href="{{ route('kader.absensi.riwayat', ['kategori' => $kategori, 'bulan' => $absensi?->bulan ?? now('Asia/Jakarta')->month, 'tahun' => $absensi?->tahun ?? now('Asia/Jakarta')->year]) }}" 
                   class="btn-pill w-full sm:w-auto bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white px-8 py-3.5 text-sm font-bold shadow-[0_8px_20px_rgba(20,184,166,0.3)] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left"></i> Lihat Riwayat
                </a>
            @endif

            @if($routeHas('kader.absensi.index'))
                <a href="{{ route('kader.absensi.index', ['kategori' => $kategori, 'tanggal' => $tanggalYmd]) }}" 
                   class="btn-pill w-full sm:w-auto bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-teal-600 hover:border-teal-200 px-8 py-3.5 text-sm font-bold shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Update Presensi
                </a>
            @endif
        </div>

        {{-- Link Opsional Ekstra (Kembali / Detail) --}}
        <div class="mt-8 flex justify-center items-center gap-4">
            @if($routeHas('kader.dashboard'))
                <a href="{{ route('kader.dashboard') }}" class="text-[11px] font-bold text-slate-400 hover:text-teal-500 transition-colors flex items-center gap-1.5 uppercase tracking-wider">
                    <i class="fa-solid fa-arrow-left"></i> Dashboard
                </a>
            @endif

            @if($routeHas('kader.absensi.show') && $absensi)
                <span class="text-slate-300">•</span>
                <a href="{{ route('kader.absensi.show', $absensi->id) }}" class="text-[11px] font-bold text-slate-400 hover:text-teal-500 transition-colors flex items-center gap-1.5 uppercase tracking-wider">
                    Detail <i class="fa-solid fa-arrow-right"></i>
                </a>
            @endif
        </div>

    </div>
</div>
@endsection