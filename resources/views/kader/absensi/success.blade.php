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

    $kodeAbsensi = $absensi?->kode_absensi ?? 'ABS-' . strtoupper(substr($kategori, 0, 3)) . '-' . now()->format('Ymd') . '-P01';

    $routeHas = fn ($name) => Route::has($name);

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
    body { 
        background-color: #f1f5f9; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    
    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 2.5rem; 
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        opacity: 0;
        animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }

    @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="min-h-[85vh] flex flex-col items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="widget-card w-full max-w-[500px] p-8 sm:p-12 text-center relative overflow-hidden">
        
        {{-- Dekorasi Latar Belakang Card --}}
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-teal-400/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- ANIMASI ICON CUSTOM (Murni SVG & CSS) --}}
        <div class="w-32 h-32 mx-auto mb-6 relative flex items-center justify-center">
            {{-- Gelombang Latar (Ripple) --}}
            <div class="absolute inset-0 bg-emerald-400 rounded-full animate-[ping_2.5s_ease-in-out_infinite] opacity-20"></div>
            <div class="absolute inset-2 bg-teal-300 rounded-full animate-[ping_3s_ease-in-out_infinite_0.5s] opacity-20"></div>

            {{-- SVG Core Icon --}}
            <svg class="relative z-10 w-24 h-24 drop-shadow-[0_10px_20px_rgba(16,185,129,0.4)]" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle class="svg-circle" cx="50" cy="50" r="46" fill="url(#gradColor)" />
                <path class="svg-check" d="M32 52 L45 65 L70 36" fill="none" stroke="#ffffff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" />
                <defs>
                    <linearGradient id="gradColor" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#34d399" />
                        <stop offset="100%" stop-color="#059669" />
                    </linearGradient>
                </defs>
                <style>
                    .svg-circle {
                        transform-origin: center;
                        animation: popBounce 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
                    }
                    .svg-check {
                        stroke-dasharray: 100;
                        stroke-dashoffset: 100;
                        animation: drawCheck 0.5s cubic-bezier(0.65, 0, 0.45, 1) 0.3s forwards;
                    }
                    @keyframes popBounce {
                        0% { transform: scale(0); opacity: 0; }
                        100% { transform: scale(1); opacity: 1; }
                    }
                    @keyframes drawCheck {
                        100% { stroke-dashoffset: 0; }
                    }
                </style>
            </svg>
        </div>
        
        {{-- Teks Utama --}}
        <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight mb-3 leading-tight">
            Presensi Berhasil<br>Disimpan.
        </h1>
        
        <p class="text-[13px] sm:text-sm font-medium text-slate-500 max-w-sm mx-auto leading-relaxed mb-8">
            Data absensi sasaran <strong class="text-teal-600">{{ $kategoriLabel }}</strong> untuk sesi pertemuan ini telah masuk ke dalam sistem.
        </p>

        {{-- Kotak Ringkasan --}}
        <div class="bg-slate-50 border border-slate-100 rounded-[1.5rem] p-5 mb-8 flex flex-col items-center gap-4 shadow-sm w-full">
            <div class="w-full text-center pb-4 border-b border-slate-200 border-dashed">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Kode Sesi</p>
                <p class="text-sm sm:text-base font-black text-slate-700 tracking-wider">{{ $kodeAbsensi }}</p>
            </div>
            
            <div class="grid grid-cols-2 w-full text-center divide-x divide-slate-200">
                <div class="px-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Sasaran</p>
                    <p class="text-lg font-black text-slate-800">{{ $totalPeserta }}</p>
                </div>
                <div class="px-2">
                    <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Hadir</p>
                    <p class="text-lg font-black text-emerald-600">{{ $totalHadir }}</p>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex flex-col gap-3 w-full">
            @if($routeHas('kader.absensi.riwayat'))
                <a href="{{ route('kader.absensi.riwayat', ['kategori' => $kategori, 'bulan' => $absensi?->bulan ?? now('Asia/Jakarta')->month, 'tahun' => $absensi?->tahun ?? now('Asia/Jakarta')->year]) }}" 
                   class="btn-pill w-full bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white px-8 py-3.5 sm:py-4 text-xs sm:text-sm font-bold shadow-[0_10px_25px_rgba(20,184,166,0.35)] flex items-center justify-center gap-2 hover:-translate-y-1">
                    <i class="fa-solid fa-clock-rotate-left"></i> Lihat Riwayat
                </a>
            @endif

            @if($routeHas('kader.absensi.index'))
                <a href="{{ route('kader.absensi.index', ['kategori' => $kategori, 'tanggal' => $tanggalYmd]) }}" 
                   class="btn-pill w-full bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-teal-600 px-8 py-3 text-xs font-bold shadow-sm flex items-center justify-center gap-2 mt-2">
                    <i class="fa-solid fa-pen-to-square"></i> Update Sesi Lain
                </a>
            @endif
        </div>

        {{-- Link Kembali ke Dashboard --}}
        @if($routeHas('kader.dashboard'))
            <div class="mt-8">
                <a href="{{ route('kader.dashboard') }}" class="text-[10px] sm:text-[11px] font-bold text-slate-400 hover:text-teal-500 transition-colors uppercase tracking-widest flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        @endif

    </div>
</div>
@endsection