@extends('layouts.kader')

@section('title', 'Registrasi Hadir')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $kategoriAktif = $kategori ?? request('kategori', 'balita');
    $tanggalInput = $tanggal ?? request('tanggal', now('Asia/Jakarta')->toDateString());
    $pasiens = $pasiens ?? collect();
    $absensiData = $absensiData ?? collect();
    $sesiHariIni = $sesiHariIni ?? null;
    $pertemuanBerikutnya = $pertemuanBerikutnya ?? ($sesiHariIni?->nomor_pertemuan ?? 1);

    $kategoriMenus = [
        'balita' => [
            'label' => 'Balita',
            'caption' => 'Anak dan tumbuh kembang',
            'icon' => 'fa-child-reaching',
            'tone' => 'emerald',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'caption' => 'Sasaran usia remaja',
            'icon' => 'fa-user-graduate',
            'tone' => 'amber',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'caption' => 'Sasaran usia lanjut',
            'icon' => 'fa-person-cane',
            'tone' => 'blue',
        ],
    ];

    if (!array_key_exists($kategoriAktif, $kategoriMenus)) {
        $kategoriAktif = 'balita';
    }

    $currentKategori = $kategoriMenus[$kategoriAktif];

    try {
        $tanggalCarbon = Carbon::parse($tanggalInput, 'Asia/Jakarta');
    } catch (\Throwable $e) {
        $tanggalCarbon = now('Asia/Jakarta');
    }

    $tanggalFormat = $tanggalCarbon->translatedFormat('l, d F Y');
    $tanggalPendek = $tanggalCarbon->translatedFormat('d M Y');

    $totalSasaran = $pasiens->count();
    $totalTercatat = 0;
    $totalHadir = 0;
    $totalTidakHadir = 0;

    foreach ($pasiens as $pasien) {
        $detail = $absensiData->get($pasien->id);
        if ($detail) {
            $totalTercatat++;
            if ((bool) $detail->hadir) {
                $totalHadir++;
            } else {
                $totalTidakHadir++;
            }
        }
    }

    $belumTercatat = max(0, $totalSasaran - $totalTercatat);
    $persenHadir = $totalSasaran > 0 ? round(($totalHadir / $totalSasaran) * 100) : 0;
    $persenTercatat = $totalSasaran > 0 ? round(($totalTercatat / $totalSasaran) * 100) : 0;

    $routeHas = fn ($name) => Route::has($name);

    $toneClass = function ($tone, $type = 'soft') {
        return match ($tone) {
            'amber' => match ($type) {
                'solid' => 'bg-amber-500 text-white',
                'light' => 'bg-amber-50 text-amber-600',
                default => 'bg-amber-50 text-amber-600',
            },
            'blue' => match ($type) {
                'solid' => 'bg-blue-500 text-white',
                'light' => 'bg-blue-50 text-blue-600',
                default => 'bg-blue-50 text-blue-600',
            },
            default => match ($type) {
                'solid' => 'bg-emerald-500 text-white',
                'light' => 'bg-emerald-50 text-emerald-600',
                default => 'bg-emerald-50 text-emerald-600',
            },
        };
    };

    $getPasienAge = function ($pasien) {
        if (empty($pasien->tanggal_lahir)) return null;
        try {
            return Carbon::parse($pasien->tanggal_lahir)->age . ' tahun';
        } catch (\Throwable $e) {
            return null;
        }
    };
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
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 1.5rem; /* Dibuat sedikit lebih kecil di mobile */
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @media(min-width: 640px) {
        .widget-card { border-radius: 2rem; }
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active {
        transform: scale(0.92);
    }

    .slim-scroll::-webkit-scrollbar { width: 4px; }
    @media(min-width: 640px) { .slim-scroll::-webkit-scrollbar { width: 6px; } }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.3); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.6); }

    .pc-row {
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.6);
        border-radius: 1.25rem;
    }
    .pc-row:hover {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }
    
    .pc-row.is-present { background-color: #f0fdf4; border-color: #6ee7b7; }
    .pc-row.is-absent { background-color: #fffbeb; border-color: #fcd34d; }

    .pc-choice-btn { position: relative; overflow: hidden; }
    
    .pc-choice-btn.btn-present-active {
        background-color: #10b981 !important; color: white !important;
        border-color: #059669 !important; 
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.5), inset 0 -2px 0 rgba(0,0,0,0.1) !important;
        transform: scale(1.05); font-weight: 800; z-index: 10;
    }

    .pc-choice-btn.btn-absent-active {
        background-color: #f59e0b !important; color: white !important;
        border-color: #d97706 !important; 
        box-shadow: 0 0 15px rgba(245, 158, 11, 0.5), inset 0 -2px 0 rgba(0,0,0,0.1) !important;
        transform: scale(1.05); font-weight: 800; z-index: 10;
    }

    .nexus-modal-overlay { transition: opacity 0.4s ease, backdrop-filter 0.4s ease; z-index: 999999 !important; }
    .nexus-modal-content { transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 40px rgba(245, 158, 11, 0.15); }
    
    @keyframes pulse-glow { 0%, 100% { opacity: 0.5; transform: scale(1); } 50% { opacity: 0.8; transform: scale(1.2); } }
    .orb-glow { animation: pulse-glow 3s infinite ease-in-out; }
</style>
@endpush

@section('content')
<div id="kaderAbsensiPage" class="px-3 py-6 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-4 sm:space-y-6 relative pb-24 md:pb-8">

    {{-- SYSTEM / VALIDATION ERROR ALERT --}}
    @if(session('error') || $errors->any())
    <div class="rounded-[1.5rem] sm:rounded-[2rem] bg-rose-50 border-2 border-rose-200 p-4 sm:p-6 shadow-lg shadow-rose-100/50 flex flex-col sm:flex-row items-center sm:items-start gap-3 sm:gap-4 relative overflow-hidden">
        <div class="bg-white rounded-full w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center shrink-0 shadow-inner">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-lg sm:text-xl"></i>
        </div>
        <div class="text-center sm:text-left">
            <h3 class="font-black text-rose-800 text-base sm:text-lg">Gagal Menyimpan Data</h3>
            @if(session('error'))
                <p class="text-rose-600 font-medium text-[11px] sm:text-sm mt-1">{{ session('error') }}</p>
            @endif
            @if($errors->any())
                <ul class="text-rose-600 font-medium text-[11px] sm:text-sm mt-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    @endif
    {{-- END OF ERROR ALERT --}}

    {{-- 1. FLOATING HERO WIDGET --}}
    <div class="relative overflow-hidden rounded-[2rem] sm:rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-5 sm:p-10 shadow-2xl shadow-teal-500/30 flex flex-col xl:flex-row justify-between items-center gap-6 sm:gap-8 border-[3px] sm:border-[4px] border-white/50">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-50"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
        
        <div class="relative z-10 w-full xl:w-1/2 flex flex-col gap-3 sm:gap-4 text-center xl:text-left">
            <div class="inline-flex justify-center xl:justify-start items-center gap-2 mb-0.5 sm:mb-1">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-3 sm:px-4 py-1 sm:py-1.5 text-[9px] sm:text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-1.5 sm:gap-2">
                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-300 animate-ping absolute"></span>
                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-200 relative"></span>
                    Attendance Hub
                </span>
            </div>
            
            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight drop-shadow-md">
                Registrasi hadir {{ $currentKategori['label'] }}.
            </h1>

            <p class="text-teal-50 text-[11px] sm:text-sm font-medium leading-relaxed max-w-lg mx-auto xl:mx-0 drop-shadow-sm hidden sm:block">
                Pilih hadir atau tidak hadir untuk setiap sasaran. Data yang tersimpan akan langsung terintegrasi dengan laporan bulanan Posyandu.
            </p>

            <div class="flex flex-wrap justify-center xl:justify-start gap-2.5 sm:gap-3 mt-1 sm:mt-2">
                @if($routeHas('kader.absensi.riwayat'))
                    <a href="{{ route('kader.absensi.riwayat', ['kategori' => $kategoriAktif]) }}" class="btn-pill bg-white text-teal-600 hover:text-teal-800 hover:bg-teal-50 px-4 sm:px-6 py-2 sm:py-3 text-[11px] sm:text-sm font-bold shadow-[0_8px_20px_rgba(255,255,255,0.3)] flex items-center gap-1.5 sm:gap-2">
                        <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                    </a>
                @endif
                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}" class="btn-pill bg-black/10 hover:bg-black/20 text-white border border-white/30 px-4 sm:px-6 py-2 sm:py-3 text-[11px] sm:text-sm font-bold backdrop-blur-md flex items-center gap-1.5 sm:gap-2 transition-all">
                        <i class="fa-solid fa-chart-line"></i> Dashboard
                    </a>
                @endif
            </div>
        </div>

        {{-- WIDGET INFORMASI SESI & KATEGORI --}}
        <div class="relative z-10 w-full xl:w-auto flex flex-row gap-3 sm:gap-4 justify-center">
            <div class="widget-card !rounded-[1.5rem] sm:!rounded-[2.5rem] !bg-white/90 p-3 sm:p-5 flex-1 sm:flex-initial flex flex-col sm:flex-row items-center gap-2 sm:gap-4 sm:min-w-[200px] text-center sm:text-left">
                <div class="w-8 h-8 sm:w-12 sm:h-12 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center text-sm sm:text-xl shadow-inner shrink-0">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sesi Absensi</p>
                    <p class="text-[11px] sm:text-sm font-black text-slate-800">{{ $tanggalPendek }}</p>
                </div>
            </div>

            <div class="widget-card !rounded-[1.5rem] sm:!rounded-[2.5rem] !bg-white/90 p-3 sm:p-5 flex-1 sm:flex-initial flex flex-col sm:flex-row items-center gap-2 sm:gap-4 sm:min-w-[200px] text-center sm:text-left">
                <div class="w-8 h-8 sm:w-12 sm:h-12 rounded-full {{ $toneClass($currentKategori['tone'], 'light') }} flex items-center justify-center text-sm sm:text-xl shadow-inner shrink-0">
                    <i class="fa-solid {{ $currentKategori['icon'] }}"></i>
                </div>
                <div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori</p>
                    <p class="text-[11px] sm:text-sm font-black text-slate-800">{{ $currentKategori['label'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. METRIK UTAMA --}}
    <section class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        <div class="widget-card p-4 sm:p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Sasaran</p>
                <p class="mt-0.5 sm:mt-1 text-xl sm:text-3xl font-black text-slate-800">{{ number_format($totalSasaran) }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-lg sm:text-2xl rotate-3 group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-users-viewfinder"></i>
            </div>
        </div>
        <div class="widget-card p-4 sm:p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-emerald-500">Hadir</p>
                <p class="mt-0.5 sm:mt-1 text-xl sm:text-3xl font-black text-slate-800" id="hadirCountText">{{ number_format($totalHadir) }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500 -rotate-3 group-hover:-rotate-6 transition-transform shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                <i class="fa-solid fa-user-check text-lg sm:text-2xl"></i>
            </div>
        </div>
        <div class="widget-card p-4 sm:p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-amber-500">Tidak</p>
                <p class="mt-0.5 sm:mt-1 text-xl sm:text-3xl font-black text-slate-800" id="tidakCountText">{{ number_format($totalTidakHadir) }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500 rotate-3 group-hover:rotate-6 transition-transform shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                <i class="fa-solid fa-user-xmark text-lg sm:text-2xl"></i>
            </div>
        </div>
        <div class="widget-card p-4 sm:p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Belum</p>
                <p class="mt-0.5 sm:mt-1 text-xl sm:text-3xl font-black text-slate-800" id="belumCountText">{{ number_format($belumTercatat) }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 -rotate-3 group-hover:-rotate-6 transition-transform">
                <i class="fa-solid fa-clipboard-list text-lg sm:text-2xl"></i>
            </div>
        </div>
    </section>

    {{-- 3. FILTER WIDGETS --}}
    <section class="grid items-stretch gap-4 sm:gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="widget-card p-4 sm:p-6 flex flex-col justify-center">
            <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3 sm:mb-4">Pilih Kategori</p>
            <div class="grid gap-2 sm:gap-3 grid-cols-3">
                @foreach($kategoriMenus as $key => $item)
                    @php
                        $active = $kategoriAktif === $key;
                        $url = route('kader.absensi.index', ['kategori' => $key, 'tanggal' => $tanggalInput]);
                    @endphp
                    <a href="{{ $url }}" class="btn-pill border p-2 sm:p-3 flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-1 sm:gap-3 transition-all {{ $active ? 'border-teal-500 bg-teal-500 text-white shadow-[0_5px_15px_rgba(20,184,166,0.4)] transform scale-[1.02]' : 'border-slate-100 bg-white/50 hover:bg-white hover:border-teal-300' }}">
                        <div class="flex h-7 w-7 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-full {{ $active ? 'bg-white/20 text-white' : $toneClass($item['tone'], 'light') }}">
                            <i class="fa-solid {{ $item['icon'] }} text-[11px] sm:text-base"></i>
                        </div>
                        <div class="text-center sm:text-left">
                            <p class="text-[10px] sm:text-sm font-bold {{ $active ? 'text-white' : 'text-slate-700' }}">{{ $item['label'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="widget-card p-4 sm:p-6 flex flex-col justify-center">
            <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3 sm:mb-4">Pilih Tanggal</p>
            <form method="GET" action="{{ route('kader.absensi.index') }}">
                <input type="hidden" name="kategori" value="{{ $kategoriAktif }}">
                <div class="flex gap-2">
                    <input type="date" name="tanggal" value="{{ $tanggalInput }}" max="{{ now('Asia/Jakarta')->toDateString() }}" class="w-full btn-pill border border-slate-200 bg-white/50 px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold text-slate-700 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-500/10 shadow-inner">
                    <button type="submit" class="btn-pill bg-teal-500 px-4 sm:px-5 text-white shadow-[0_5px_15px_rgba(20,184,166,0.3)] hover:bg-teal-600 hover:-translate-y-0.5">
                        <i class="fa-solid fa-filter text-sm"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- 4. MAIN FORM GRID --}}
    <form id="attendanceForm" method="POST" action="{{ route('kader.absensi.store') }}" class="flex flex-col xl:flex-row gap-4 sm:gap-6 items-stretch">
        @csrf
        <input type="hidden" name="kategori" value="{{ $kategoriAktif }}">
        <input type="hidden" name="tanggal" value="{{ $tanggalInput }}">

        {{-- LEFT: ATTENDANCE LIST --}}
        <section class="widget-card flex-1 flex flex-col p-4 sm:p-6 min-h-[500px]">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 sm:gap-4 mb-4 shrink-0 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-800">Daftar Presensi</h2>
                    <p class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5" id="visibleCountText">Menampilkan {{ number_format($totalSasaran) }} data.</p>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="markAllPresent" class="btn-pill bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 px-3 py-1.5 sm:px-4 sm:py-2 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider transition-colors shadow-sm">Semua Hadir</button>
                    <button type="button" id="markAllAbsent" class="btn-pill bg-amber-50 hover:bg-amber-100 text-amber-600 border border-amber-200 px-3 py-1.5 sm:px-4 sm:py-2 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider transition-colors shadow-sm">Semua Absen</button>
                    <button type="button" id="resetChanges" class="btn-pill border border-slate-200 bg-white/50 text-slate-500 px-2.5 py-1.5 sm:px-3 sm:py-2 text-[10px] sm:text-[11px] font-bold hover:bg-slate-50 shadow-sm" title="Reset Semua"><i class="fa-solid fa-rotate-left"></i></button>
                </div>
            </div>

            {{-- SEARCH BAR --}}
            <div class="relative mb-4 shrink-0 group">
                <i class="fa-solid fa-magnifying-glass absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 text-slate-400 z-10 pointer-events-none text-xs sm:text-sm"></i>
                <input type="text" id="attendanceSearch" autocomplete="off" placeholder="Cari nama atau NIK sasaran..." class="w-full btn-pill border border-slate-200 bg-white/50 py-2.5 sm:py-3.5 pl-9 sm:pl-11 pr-10 sm:pr-12 text-xs sm:text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10 shadow-inner relative z-0">
                <button type="button" id="clearSearch" class="absolute right-1.5 sm:right-2 top-1/2 -translate-y-1/2 w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-full transition-all hidden z-20 cursor-pointer" title="Bersihkan Pencarian">
                    <i class="fa-solid fa-xmark text-sm sm:text-lg pointer-events-none"></i>
                </button>
            </div>

            @if($pasiens->isNotEmpty())
                <div id="attendanceList" class="flex-1 slim-scroll overflow-y-auto pr-1 sm:pr-2 space-y-2.5 sm:space-y-3 pb-2 sm:pb-4 max-h-[60vh] xl:max-h-full">
                    @foreach($pasiens as $index => $pasien)
                        @php
                            $detail = $absensiData->get($pasien->id);
                            $saved = (bool) $detail;
                            $statusValue = $saved ? (int) $detail->hadir : null;
                            $oldStatus = old("kehadiran.$pasien->id", $statusValue);

                            $nama = $pasien->nama_lengkap ?? $pasien->nama ?? 'Tanpa Nama';
                            $nik = $pasien->nik ?? '-';
                            $umur = $getPasienAge($pasien);
                            $keterangan = old("keterangan.$pasien->id", $detail->keterangan ?? '');

                            $stateValue = $oldStatus === null ? '' : (string) $oldStatus;
                            $rowState = 'is-pending';
                            if ($stateValue === '1') $rowState = 'is-present';
                            elseif ($stateValue === '0') $rowState = 'is-absent';
                        @endphp

                        <div class="pc-row p-3 sm:p-4 {{ $rowState }}"
                             data-row="attendance"
                             data-search-name="{{ mb_strtolower($nama ?? '', 'UTF-8') }}"
                             data-search-nik="{{ preg_replace('/\D/', '', (string) $nik) }}"
                             data-original-index="{{ $index }}">
                             
                            <input type="hidden" name="kehadiran[{{ $pasien->id }}]" value="{{ $stateValue }}" data-state-input data-initial="{{ $stateValue }}">

                            {{-- TATA LETAK LEBIH KOMPAK DI MOBILE --}}
                            <div class="flex flex-col md:flex-row justify-between md:items-center gap-3 sm:gap-4">
                                
                                {{-- IDENTITAS SASARAN --}}
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm">
                                        <i class="fa-solid fa-user text-sm sm:text-lg"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-sm sm:text-base font-black text-slate-800 break-words leading-tight mb-0.5 sm:mb-1" title="{{ $nama }}">
                                            {{ $nama }}
                                        </h3>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] sm:text-[11px] font-semibold text-slate-500">
                                            <span class="flex items-center gap-1 whitespace-nowrap"><i class="fa-solid fa-id-card opacity-70"></i> {{ $nik }}</span>
                                            <span class="text-slate-300 hidden sm:inline">•</span>
                                            <span class="flex items-center gap-1 whitespace-nowrap"><i class="fa-solid fa-cake-candles opacity-70"></i> {{ $umur }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- ACTION BUTTONS & CATATAN --}}
                                <div class="flex flex-col gap-2 w-full md:w-auto shrink-0 pl-12 md:pl-0">
                                    <div class="flex gap-1.5 sm:gap-2 w-full md:w-auto bg-slate-100/50 p-1 rounded-[1.25rem] border border-slate-100">
                                        <button type="button" data-choice="1" class="pc-choice-btn btn-pill flex-1 md:flex-none border border-transparent bg-white px-3 sm:px-6 py-2 sm:py-2.5 text-[11px] sm:text-xs font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-700 shadow-sm {{ $stateValue === '1' ? 'btn-present-active' : '' }}">
                                            <i class="fa-solid fa-check mr-1 opacity-50"></i> Hadir
                                        </button>
                                        <button type="button" data-choice="0" class="pc-choice-btn btn-pill flex-1 md:flex-none border border-transparent bg-white px-3 sm:px-6 py-2 sm:py-2.5 text-[11px] sm:text-xs font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-700 shadow-sm {{ $stateValue === '0' ? 'btn-absent-active' : '' }}">
                                            <i class="fa-solid fa-xmark mr-1 opacity-50"></i> Tidak
                                        </button>
                                        <button type="button" data-reset-row class="btn-pill w-8 sm:w-10 flex justify-center items-center border border-transparent bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-600 shadow-sm" title="Reset">
                                            <i class="fa-solid fa-rotate-left text-[10px] sm:text-[11px]"></i>
                                        </button>
                                    </div>
                                    <input type="text" name="keterangan[{{ $pasien->id }}]" value="{{ $keterangan }}" data-note-input placeholder="Catatan opsional..." class="w-full md:w-56 btn-pill border border-slate-200 bg-white/70 px-3 py-1.5 sm:px-4 sm:py-2 text-[11px] sm:text-xs font-medium text-slate-700 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-2 focus:ring-teal-500/10 shadow-inner">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center btn-pill border-2 border-dashed border-slate-200 bg-white/50 p-6 sm:p-8 text-center rounded-[1.5rem] sm:rounded-[2rem]">
                    <div class="flex h-12 w-12 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3 sm:mb-4 shadow-inner">
                        <i class="fa-solid fa-users-slash text-xl sm:text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-base sm:text-lg">Data Kosong</h3>
                    <p class="text-[11px] sm:text-sm text-slate-500 mt-1 max-w-sm">Tambahkan sasaran terlebih dahulu pada menu Data Pasien di Posyandu Management System.</p>
                </div>
            @endif
        </section>

        {{-- RIGHT: SUMMARY INPUT --}}
        <aside class="widget-card w-full xl:w-[320px] flex flex-col p-4 sm:p-6 shrink-0 bg-gradient-to-b from-white/90 to-white/60">
            <div class="flex-1 flex flex-col">
                <div class="mb-4 sm:mb-6 border-b border-slate-100 pb-3 sm:pb-5">
                    <p class="text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-teal-500 mb-0.5 sm:mb-1 flex items-center gap-1.5 sm:gap-2">
                        <i class="fa-solid fa-shield-halved"></i> Validasi Form
                    </p>
                    <h2 class="text-base sm:text-lg font-black text-slate-800">Summary Input</h2>
                    <p class="mt-1 sm:mt-2 text-[11px] sm:text-xs font-medium text-slate-500 leading-relaxed">Pastikan semua status kehadiran telah menyala/terpilih sebelum klik simpan data presensi.</p>
                </div>

                <div class="space-y-2.5 sm:space-y-3">
                    <div class="btn-pill border border-slate-100 bg-white/80 p-3 sm:p-4 flex justify-between items-center shadow-sm">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</span>
                        <span class="text-xs sm:text-sm font-black text-slate-800">{{ $currentKategori['label'] }}</span>
                    </div>
                    <div class="btn-pill border border-slate-100 bg-white/80 p-3 sm:p-4 flex justify-between items-center shadow-sm">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Tanggal</span>
                        <span class="text-xs sm:text-sm font-black text-slate-800">{{ $tanggalPendek }}</span>
                    </div>
                    <div class="btn-pill border border-slate-100 bg-white/80 p-3 sm:p-4 flex justify-between items-center shadow-sm">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</span>
                        <span class="text-[8px] sm:text-[9px] font-black uppercase tracking-wider btn-pill px-2.5 py-1 sm:px-3 sm:py-1.5 shadow-inner {{ $sesiHariIni ? 'bg-sky-100 text-sky-600' : 'bg-teal-100 text-teal-600' }}">
                            <i class="fa-solid {{ $sesiHariIni ? 'fa-pen-to-square' : 'fa-plus' }} mr-1"></i> {{ $sesiHariIni ? 'Update' : 'Baru' }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 sm:mt-6 rounded-[1.5rem] sm:rounded-[2rem] border border-slate-100 bg-white p-4 sm:p-6 shadow-lg shadow-slate-100/50 flex-1 flex flex-col justify-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-teal-400 to-emerald-400"></div>
                    <p class="text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-slate-400 mb-3 sm:mb-4 text-center">Progress Sesi</p>
                    
                    <div class="flex justify-center items-end gap-1.5 sm:gap-2 mb-3 sm:mb-4">
                        <span class="text-4xl sm:text-5xl font-black text-teal-500 drop-shadow-sm" id="sideHadirText">{{ $totalHadir }}</span>
                        <span class="text-sm sm:text-base font-bold text-slate-400 mb-1 sm:mb-1.5">/ {{ $totalSasaran }}</span>
                    </div>

                    <div class="h-2.5 sm:h-3 w-full btn-pill bg-slate-100 overflow-hidden shadow-inner">
                        <div id="presenceProgressBar" class="h-full bg-gradient-to-r from-teal-400 to-emerald-500 transition-all duration-700 ease-out relative" style="width: {{ $persenHadir }}%">
                            <div class="absolute top-0 right-0 bottom-0 left-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4yIi8+PC9zdmc+')]"></div>
                        </div>
                    </div>
                    <div class="text-center mt-2.5 sm:mt-3">
                        <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider" id="sideProgressText">{{ $persenHadir }}% Sasaran Hadir</span>
                    </div>
                </div>
            </div>

            {{-- SUBMIT BUTTON --}}
            @if($pasiens->isNotEmpty())
                <div class="mt-4 sm:mt-6 pt-1 sm:pt-2">
                    <button type="submit" id="submitAttendanceBtn" class="w-full flex items-center justify-center gap-2 btn-pill bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 px-4 sm:px-5 py-3 sm:py-4 text-xs sm:text-sm font-bold text-white shadow-[0_10px_25px_rgba(20,184,166,0.4)] hover:-translate-y-1 transition-all">
                        <i class="fa-solid fa-cloud-arrow-up text-base sm:text-lg"></i> Simpan Presensi
                    </button>
                </div>
            @endif
        </aside>
    </form>

    {{-- MODAL --}}
    <div id="nexusAlertModal" class="nexus-modal-overlay fixed inset-0 z-[999999] hidden items-center justify-center bg-slate-900/40 backdrop-blur-md opacity-0 p-4">
        <div class="nexus-modal-content relative w-full max-w-md transform scale-90 rounded-[2rem] sm:rounded-[2.5rem] bg-white/95 p-6 sm:p-10 border border-white/80 overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)]">
            <div class="absolute -top-16 -left-16 w-40 h-40 bg-amber-400/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-16 -right-16 w-40 h-40 bg-rose-400/10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 flex flex-col items-center">
                <div class="mx-auto flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center relative mb-5 sm:mb-6">
                    <div class="absolute inset-0 bg-amber-400 rounded-full blur-xl opacity-40 orb-glow"></div>
                    <div class="relative flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-full bg-gradient-to-br from-amber-100 to-amber-50 border border-amber-200 text-amber-500 shadow-inner">
                        <i class="fa-solid fa-triangle-exclamation text-3xl sm:text-4xl drop-shadow-md"></i>
                    </div>
                </div>
                
                <h3 class="text-center text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Perhatian!</h3>
                <div class="w-10 sm:w-12 h-1.5 bg-amber-400 rounded-full mx-auto mt-2 sm:mt-3 mb-4 sm:mb-5"></div>
                
                <p id="nexusAlertMessage" class="text-center text-sm sm:text-base font-medium text-slate-600 leading-relaxed px-1 sm:px-2 max-w-sm">
                    Presensi belum lengkap. Masih ada data sasaran yang belum dipilih. Pilih <strong class="text-emerald-500">Hadir</strong> atau <strong class="text-amber-500">Tidak</strong> terlebih dahulu.
                </p>
                
                <div class="mt-6 sm:mt-8 w-full">
                    <button type="button" id="nexusAlertClose" class="w-full flex items-center justify-center btn-pill bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 px-5 sm:px-6 py-3 sm:py-4 text-sm sm:text-base font-bold text-white shadow-[0_10px_25px_rgba(245,158,11,0.4)] hover:-translate-y-1 transition-all">
                        <i class="fa-solid fa-check-circle text-lg sm:text-xl mr-2"></i> Saya Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const numberFormat = new Intl.NumberFormat('id-ID');
    let searchTimer = null;

    function qs(selector, root = document) { return root.querySelector(selector); }
    function qsa(selector, root = document) { return Array.from(root.querySelectorAll(selector)); }
    function getRows() { return qsa('[data-row="attendance"]'); }

    // --- TELEPORT MODAL KE BODY AGAR FULL SCREEN ---
    const nexusModal = document.getElementById('nexusAlertModal');
    if (nexusModal) {
        document.body.appendChild(nexusModal); 
    }

    const nexusModalInner = nexusModal ? nexusModal.querySelector('.nexus-modal-content') : null;
    const nexusMessage = document.getElementById('nexusAlertMessage');
    const nexusCloseBtn = document.getElementById('nexusAlertClose');

    function showNexusAlert(message) {
        if (!nexusModal) return; 
        
        nexusMessage.innerHTML = message;
        nexusModal.classList.remove('hidden');
        nexusModal.classList.add('flex'); 
        
        void nexusModal.offsetWidth; 
        
        nexusModal.classList.remove('opacity-0');
        nexusModal.classList.add('opacity-100');
        if (nexusModalInner) {
            nexusModalInner.classList.remove('scale-90');
            nexusModalInner.classList.add('scale-100');
        }
    }

    function closeNexusAlert() {
        if (!nexusModal) return;
        
        nexusModal.classList.remove('opacity-100');
        nexusModal.classList.add('opacity-0');
        if (nexusModalInner) {
            nexusModalInner.classList.remove('scale-100');
            nexusModalInner.classList.add('scale-90');
        }
        
        setTimeout(() => {
            nexusModal.classList.add('hidden');
            nexusModal.classList.remove('flex');
        }, 400); 
    }

    if(nexusCloseBtn) {
        nexusCloseBtn.addEventListener('click', closeNexusAlert);
    }
    
    if(nexusModal) {
        nexusModal.addEventListener('click', function(e) {
            if(e.target === nexusModal) closeNexusAlert();
        });
    }

    function paintRow(row) {
        const input = qs('[data-state-input]', row);
        const value = input ? input.value : '';
        const buttons = qsa('[data-choice]', row);
        
        row.classList.remove('is-present', 'is-absent', 'is-pending');
        buttons.forEach(btn => btn.classList.remove('btn-present-active', 'btn-absent-active'));

        if (value === '1') {
            row.classList.add('is-present');
            const btn = qs('[data-choice="1"]', row);
            if (btn) btn.classList.add('btn-present-active');
        } else if (value === '0') {
            row.classList.add('is-absent');
            const btn = qs('[data-choice="0"]', row);
            if (btn) btn.classList.add('btn-absent-active'); 
        } else {
            row.classList.add('is-pending');
        }
    }

    function updateCounter() {
        const rows = getRows();
        const total = rows.length;
        let hadir = 0, tidak = 0, belum = 0;

        rows.forEach(function (row) {
            const input = qs('[data-state-input]', row);
            const val = input ? input.value : '';
            if (val === '1') hadir++;
            else if (val === '0') tidak++;
            else belum++;
            paintRow(row);
        });

        const persenHadir = total > 0 ? Math.round((hadir / total) * 100) : 0;

        if (qs('#hadirCountText')) qs('#hadirCountText').textContent = numberFormat.format(hadir);
        if (qs('#tidakCountText')) qs('#tidakCountText').textContent = numberFormat.format(tidak);
        if (qs('#belumCountText')) qs('#belumCountText').textContent = numberFormat.format(belum);
        
        if (qs('#sideHadirText')) {
            const el = qs('#sideHadirText');
            el.textContent = hadir;
            el.classList.remove('scale-110');
            void el.offsetWidth;
            el.classList.add('scale-110', 'transition-transform');
            setTimeout(() => el.classList.remove('scale-110'), 300);
        }
        if (qs('#sideProgressText')) qs('#sideProgressText').textContent = persenHadir + '% Sasaran Hadir';
        
        const bar = qs('#presenceProgressBar');
        if (bar) bar.style.width = persenHadir + '%';

        return { total, hadir, tidak, belum };
    }

    document.addEventListener('click', function(e) {
        const choiceBtn = e.target.closest('[data-choice]');
        if (choiceBtn) {
            e.preventDefault();
            const row = choiceBtn.closest('[data-row="attendance"]');
            if (row) {
                const input = qs('[data-state-input]', row);
                if (input) {
                    input.value = choiceBtn.getAttribute('data-choice');
                    paintRow(row);
                    updateCounter();
                }
            }
            return;
        }

        const resetBtn = e.target.closest('[data-reset-row]');
        if (resetBtn) {
            e.preventDefault();
            const row = resetBtn.closest('[data-row="attendance"]');
            if (row) {
                const input = qs('[data-state-input]', row);
                const note = qs('[data-note-input]', row);
                if (input) input.value = input.dataset.initial || '';
                if (note) note.value = note.dataset.initialNote || '';
                paintRow(row);
                updateCounter();
            }
            return;
        }

        if (e.target.closest('#markAllPresent')) {
            e.preventDefault();
            getRows().forEach(row => { qs('[data-state-input]', row).value = '1'; });
            updateCounter();
        }
        if (e.target.closest('#markAllAbsent')) {
            e.preventDefault();
            getRows().forEach(row => { qs('[data-state-input]', row).value = '0'; });
            updateCounter();
        }
        if (e.target.closest('#resetChanges')) {
            e.preventDefault();
            getRows().forEach(row => { qs('[data-state-input]', row).value = qs('[data-state-input]', row).dataset.initial || ''; });
            updateCounter();
        }
    });

    // --- FUNGSI SEARCH ---
    const searchInput = qs('#attendanceSearch');
    const clearBtn = qs('#clearSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimer);
            const query = e.target.value.toLowerCase().trim();
            
            if (clearBtn) {
                if (query.length > 0) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }
            }
            
            searchTimer = setTimeout(() => {
                getRows().forEach(row => {
                    const name = row.dataset.searchName || '';
                    const nik = row.dataset.searchNik || '';
                    if (name.includes(query) || nik.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }, 150);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                searchInput.focus();
            }
            clearBtn.classList.add('hidden');
        });
    }

    const form = qs('#attendanceForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const stats = updateCounter();
            if (stats.belum > 0) {
                e.preventDefault(); 
                showNexusAlert(`Presensi belum lengkap. Masih ada <strong class="text-slate-800">${stats.belum} data sasaran</strong> yang belum dipilih. Pilih <span class="text-emerald-500 font-bold">Hadir</span> atau <span class="text-amber-500 font-bold">Tidak</span> terlebih dahulu.`);
            }
        });
    }

    updateCounter();
});
</script>
@endpush