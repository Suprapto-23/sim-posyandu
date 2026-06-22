@extends('layouts.bidan')

@section('title', 'Kelola Jadwal Posyandu')
@section('page-name', 'Kelola Jadwal')
@section('page-title', 'Kelola Jadwal Posyandu')

@php
    use Carbon\Carbon;

    Carbon::setLocale('id');

    $jadwals = $jadwals ?? collect();

    $search = $search ?? request('search', '');
    $status = $status ?? request('status', 'semua');
    $kategori = $kategori ?? request('kategori', 'semua');
    $target = $target ?? request('target', 'semua');

    $stats = $stats ?? ['total' => 0, 'aktif' => 0, 'bulan_ini' => 0, 'mendatang' => 0];

    // PEMETAAN ICON & WARNA (MENGGUNAKAN FONTAWESOME)
    $kategoriOptions = [
        'semua'       => ['label' => 'Semua Kategori', 'icon' => 'fa-layer-group'],
        'posyandu'    => ['label' => 'Posyandu Rutin', 'icon' => 'fa-house-medical'],
        'imunisasi'   => ['label' => 'Imunisasi Balita', 'icon' => 'fa-syringe'],
        'pemeriksaan' => ['label' => 'Pemeriksaan Klinis', 'icon' => 'fa-stethoscope'],
        'lainnya'     => ['label' => 'Kegiatan Lainnya', 'icon' => 'fa-calendar-plus'],
    ];

    $targetOptions = [
        'semua'  => ['label' => 'Semua Sasaran', 'icon' => 'fa-users'],
        'balita' => ['label' => 'Balita', 'icon' => 'fa-baby'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane'],
    ];

    $statusOptions = [
        'semua'      => ['label' => 'Semua Status', 'icon' => 'fa-filter'],
        'aktif'      => ['label' => 'Aktif', 'icon' => 'fa-circle-check'],
        'selesai'    => ['label' => 'Selesai', 'icon' => 'fa-flag-checkered'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'icon' => 'fa-circle-xmark'],
    ];

    // FORMATTER TANGGAL & WAKTU
    $formatTanggal = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y') : '-';
    $formatHari = fn($date) => $date ? Carbon::parse($date)->translatedFormat('l') : '-';
    $formatBulanPendek = fn($date) => $date ? Carbon::parse($date)->translatedFormat('M') : '-';
    $formatTanggalAngka = fn($date) => $date ? Carbon::parse($date)->format('d') : '-';
    
    $formatWaktu = function ($mulai, $selesai) {
        if(!$mulai || !$selesai) return '-';
        return Carbon::parse($mulai)->format('H:i') . ' - ' . Carbon::parse($selesai)->format('H:i') . ' WIB';
    };

    $isToday = fn($date) => $date ? Carbon::parse($date)->isToday() : false;
    $isPastDate = fn($date) => $date ? Carbon::parse($date)->startOfDay()->lt(now()->startOfDay()) : false;

    $canModifyJadwal = function ($jadwal) {
        if (($jadwal->status ?? 'aktif') !== 'aktif') return false;
        if (empty($jadwal->tanggal)) return false;
        try {
            $startDateTime = Carbon::parse(Carbon::parse($jadwal->tanggal)->format('Y-m-d') . ' ' . ($jadwal->waktu_mulai ?? '00:00:00'));
            return now()->lt($startDateTime);
        } catch (\Throwable $e) { return false; }
    };

    // TEMA WARNA BAGE
    $statusTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'aktif' => ['label' => 'Aktif', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500', 'icon' => 'fa-circle-check'],
            'selesai' => ['label' => 'Selesai', 'badge' => 'bg-slate-50 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400', 'icon' => 'fa-flag-checkered'],
            'dibatalkan' => ['label' => 'Dibatalkan', 'badge' => 'bg-rose-50 text-rose-700 border-rose-200', 'dot' => 'bg-rose-500', 'icon' => 'fa-circle-xmark'],
            default => ['label' => ucfirst((string) $value), 'badge' => 'bg-slate-50 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400', 'icon' => 'fa-info-circle'],
        };
    };

    $kategoriTheme = function ($value) use ($kategoriOptions) {
        $val = strtolower((string) $value);
        $icon = $kategoriOptions[$val]['icon'] ?? 'fa-house-medical';
        return match ($val) {
            'imunisasi' => ['badge' => 'bg-cyan-50 text-cyan-700 border-cyan-200', 'icon' => $icon],
            'pemeriksaan' => ['badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => $icon],
            'lainnya' => ['badge' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => $icon],
            default => ['badge' => 'bg-sky-50 text-sky-700 border-sky-200', 'icon' => $icon],
        };
    };

    $targetTheme = function ($value) use ($targetOptions) {
        $val = strtolower((string) $value);
        $icon = $targetOptions[$val]['icon'] ?? 'fa-users';
        return match ($val) {
            'balita' => ['badge' => 'bg-sky-50 text-sky-700 border-sky-200', 'icon' => $icon],
            'remaja' => ['badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'icon' => $icon],
            'lansia' => ['badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => $icon],
            default => ['badge' => 'bg-slate-50 text-slate-700 border-slate-200', 'icon' => $icon],
        };
    };

    $scheduleState = function ($jadwal) use ($canModifyJadwal) {
        if (($jadwal->status ?? 'aktif') === 'dibatalkan') return ['label' => 'Dibatalkan', 'icon' => 'fa-ban', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'];
        if (($jadwal->status ?? 'aktif') === 'selesai') return ['label' => 'Selesai', 'icon' => 'fa-lock', 'class' => 'bg-slate-100 text-slate-500 border-slate-200'];
        if (!$canModifyJadwal($jadwal)) return ['label' => 'Terkunci', 'icon' => 'fa-lock', 'class' => 'bg-slate-100 text-slate-500 border-slate-200'];
        return ['label' => 'Bisa Diedit', 'icon' => 'fa-pen', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'];
    };

    $totalData = method_exists($jadwals, 'total') ? $jadwals->total() : count($jadwals);
    $currentCount = method_exists($jadwals, 'count') ? $jadwals->count() : count($jadwals);

    $summaryCards = [
        ['label' => 'Total Jadwal', 'value' => $stats['total'] ?? 0, 'icon' => 'fa-calendar-days', 'theme' => 'slate'],
        ['label' => 'Jadwal Aktif', 'value' => $stats['aktif'] ?? 0, 'icon' => 'fa-calendar-check', 'theme' => 'emerald'],
        ['label' => 'Bulan Ini', 'value' => $stats['bulan_ini'] ?? 0, 'icon' => 'fa-calendar-day', 'theme' => 'cyan'],
        ['label' => 'Mendatang', 'value' => $stats['mendatang'] ?? 0, 'icon' => 'fa-clock-rotate-left', 'theme' => 'amber'],
    ];
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
        transform: translateZ(0); 
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .widget-card:hover {
        transform: translateY(-2px) translateZ(0);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }

    .input-soft {
        width: 100%; background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 1.2rem; padding: 12px 16px; font-size: 13px;
        font-weight: 700; color: #1e293b; outline: none; transition: all .25s ease;
    }
    .input-soft:focus {
        background: #ffffff; border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, .15);
    }
    
    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.97); }

    .slim-scroll { -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    .slim-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.3); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.5); }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    /* Nexus Modal Styles */
    .pc-modal-backdrop {
        position: fixed !important; inset: 0 !important; z-index: 9999 !important; display: none;
        align-items: center; justify-content: center; background: rgba(15, 23, 42, .6); backdrop-filter: blur(10px); padding: 1rem; opacity: 0; transition: opacity 0.3s ease;
    }
    .pc-modal-backdrop.is-open { display: flex !important; opacity: 1; }
    .pc-modal-card {
        width: 100%; max-width: 440px; background: white; border-radius: 2.5rem; padding: 2.5rem 2rem;
        transform: scale(0.9) translateY(20px); opacity: 0; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;
    }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 animate-pop-in">

    {{-- 1. HERO BANNER --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 p-8 sm:p-10 shadow-2xl shadow-emerald-500/20 flex flex-col lg:flex-row justify-between items-center gap-8 border-[6px] border-white/40" style="transform: translateZ(0);">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full lg:w-2/3 flex flex-col gap-4 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2 mb-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check"></i> Agenda Pelayanan
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                Kelola Jadwal Posyandu
            </h1>

            <p class="text-white/90 text-sm font-medium leading-relaxed max-w-xl mx-auto lg:mx-0">
                Buat dan atur agenda pelayanan. Jadwal aktif yang belum dimulai masih dapat Anda perbaiki, sedangkan jadwal lampau akan terkunci sebagai arsip.
            </p>

            <div class="flex justify-center lg:justify-start mt-2">
                <a href="{{ route('bidan.jadwal.create') }}" class="btn-pill bg-white text-emerald-600 hover:bg-emerald-50 px-6 py-3.5 text-[11px] font-black uppercase tracking-widest shadow-md flex items-center gap-2 transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus text-sm"></i> Buat Jadwal Baru
                </a>
            </div>
        </div>

        <div class="relative z-10 w-full lg:w-1/3 flex justify-center lg:justify-end">
            <div class="widget-card !rounded-[2rem] !shadow-none bg-white/20 border border-white/30 backdrop-blur-md px-6 py-4 flex items-center gap-5">
                <div>
                    <span class="block text-[10px] font-black uppercase tracking-widest text-white/90">Total Tampil</span>
                    <span class="block text-3xl font-black text-white mt-0.5" id="jadwalVisibleCounter">{{ $currentCount }}</span>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-500 shadow-lg">
                    <i class="fa-solid fa-calendar-day text-2xl"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. SUMMARY CARDS --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="widget-card p-5 group flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 group-hover:text-{{ $card['theme'] }}-500 transition-colors">{{ $card['label'] }}</p>
                    <h2 class="text-3xl font-black text-slate-800 leading-none">{{ $card['value'] }}</h2>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-{{ $card['theme'] }}-50 text-{{ $card['theme'] }}-500 border border-{{ $card['theme'] }}-100 flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid {{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach
    </section>

    {{-- 3. FILTER FORM --}}
    <section class="widget-card p-6">
        <form method="GET" action="{{ route('bidan.jadwal.index') }}" id="jadwalFilterForm" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_180px_180px_180px_auto_auto] items-end">
            <div>
                <label class="form-label" for="jadwalSearch">Pencarian</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input id="jadwalSearch" type="search" name="search" value="{{ $search }}" autocomplete="off" class="input-soft pl-10" placeholder="Cari judul / lokasi...">
                </div>
            </div>
            <div>
                <label class="form-label" for="jadwalStatus">Status</label>
                <select id="jadwalStatus" name="status" class="input-soft cursor-pointer">
                    @foreach($statusOptions as $key => $option)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="jadwalKategori">Kategori</label>
                <select id="jadwalKategori" name="kategori" class="input-soft cursor-pointer">
                    @foreach($kategoriOptions as $key => $option)
                        <option value="{{ $key }}" @selected($kategori === $key)>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="jadwalTarget">Sasaran</label>
                <select id="jadwalTarget" name="target" class="input-soft cursor-pointer">
                    @foreach($targetOptions as $key => $option)
                        <option value="{{ $key }}" @selected($target === $key)>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            
            <button type="submit" class="btn-pill bg-slate-900 text-white px-6 py-3.5 text-[11px] font-black uppercase tracking-widest shadow-md hover:bg-emerald-600 transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if($search || $status !== 'semua' || $kategori !== 'semua' || $target !== 'semua')
                <a href="{{ route('bidan.jadwal.index') }}" class="btn-pill bg-white border border-slate-200 text-slate-600 px-6 py-3.5 text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition-colors flex items-center justify-center gap-2">
                    Reset
                </a>
            @endif
        </form>
    </section>

    {{-- 4. MAIN TABLE AREA --}}
    <section class="widget-card overflow-hidden flex flex-col relative">
        <div class="p-6 border-b border-slate-100 bg-white flex items-center gap-3 shrink-0 z-20">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg shadow-sm border border-emerald-100"><i class="fa-solid fa-calendar-check"></i></div>
            <div>
                <h2 class="text-base font-black tracking-tight text-slate-800 uppercase">Daftar Jadwal</h2>
                <p class="text-[10px] font-bold text-slate-400 tracking-widest uppercase mt-0.5">Agenda Pelayanan Posyandu</p>
            </div>
        </div>

        <div class="min-h-[200px] max-h-[600px] overflow-y-auto overflow-x-auto slim-scroll relative bg-slate-50/30">
            <div class="hidden lg:block w-full min-w-[1100px]">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-slate-200 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Tanggal & Agenda</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Waktu & Lokasi</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Kategori & Sasaran</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Akses</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="jadwalTableBody" class="divide-y divide-slate-100 bg-white">
                        @forelse($jadwals as $jadwal)
                            @php
                                $sTitle = mb_strtolower(trim((string) ($jadwal->judul ?? '')), 'UTF-8');
                                $sLoc = mb_strtolower(trim((string) ($jadwal->lokasi ?? '')), 'UTF-8');
                                $sStat = strtolower((string) ($jadwal->status ?? 'aktif'));
                                $sKat = strtolower((string) ($jadwal->kategori ?? 'posyandu'));
                                $sTarg = strtolower((string) ($jadwal->target_peserta ?? 'semua'));

                                $statData = $statusTheme($jadwal->status ?? 'aktif');
                                $katData = $kategoriTheme($jadwal->kategori ?? 'posyandu');
                                $targData = $targetTheme($jadwal->target_peserta ?? 'semua');
                                $stateData = $scheduleState($jadwal);

                                $katLabel = $kategoriOptions[$sKat]['label'] ?? ucfirst($sKat);
                                $targLabel = $targetOptions[$sTarg]['label'] ?? ucfirst($sTarg);
                            @endphp

                            <tr class="js-jadwal-row hover:bg-slate-50/80 transition-colors group" data-title="{{ $sTitle }}" data-location="{{ $sLoc }}" data-status="{{ $sStat }}" data-kategori="{{ $sKat }}" data-target="{{ $sTarg }}">
                                <td class="px-6 py-5 align-middle">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-[1rem] bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 shadow-sm group-hover:border-emerald-300 transition-colors">
                                            <span class="text-[9px] font-black uppercase text-emerald-600">{{ $formatBulanPendek($jadwal->tanggal ?? null) }}</span>
                                            <span class="text-xl font-black leading-none text-slate-800">{{ $formatTanggalAngka($jadwal->tanggal ?? null) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-black text-slate-800 group-hover:text-emerald-600 transition-colors truncate max-w-[250px]" title="{{ $jadwal->judul }}">{{ $jadwal->judul ?? '-' }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 mt-1"><i class="fa-solid fa-calendar-day mr-1"></i> {{ $formatHari($jadwal->tanggal ?? null) }}, {{ $formatTanggal($jadwal->tanggal ?? null) }}</p>
                                            @if($isToday($jadwal->tanggal)) <span class="inline-flex mt-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-widest text-emerald-600 border border-emerald-100">Hari Ini</span> @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-xs font-bold text-slate-800"><i class="fa-solid fa-clock text-slate-400 mr-1.5"></i> {{ $formatWaktu($jadwal->waktu_mulai, $jadwal->waktu_selesai) }}</p>
                                    <p class="text-[10px] font-bold text-slate-500 mt-1 truncate max-w-[180px]" title="{{ $jadwal->lokasi }}"><i class="fa-solid fa-map-location-dot text-slate-400 mr-1.5"></i> {{ $jadwal->lokasi ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle space-y-1.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-[10px] font-black border {{ $katData['badge'] }}">
                                        <i class="fa-solid {{ $katData['icon'] }}"></i> {{ $katLabel }}
                                    </span>
                                    <br>
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-[10px] font-black border {{ $targData['badge'] }}">
                                        <i class="fa-solid {{ $targData['icon'] }}"></i> {{ $targLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black border {{ $statData['badge'] }}">
                                        <i class="fa-solid {{ $statData['icon'] }}"></i> {{ $statData['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[10px] font-black border {{ $stateData['class'] }}">
                                        <i class="fa-solid {{ $stateData['icon'] }}"></i> {{ $stateData['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 align-middle text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('bidan.jadwal.show', $jadwal) }}" class="btn-pill inline-flex h-9 w-9 items-center justify-center border border-slate-200 bg-white text-slate-500 hover:border-emerald-300 hover:text-emerald-600 hover:bg-emerald-50 transition shadow-sm" title="Detail">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </a>
                                        @if($canModifyJadwal($jadwal))
                                            <a href="{{ route('bidan.jadwal.edit', $jadwal) }}" class="btn-pill inline-flex h-9 w-9 items-center justify-center border border-slate-200 bg-white text-slate-500 hover:border-amber-300 hover:text-amber-600 hover:bg-amber-50 transition shadow-sm" title="Edit">
                                                <i class="fa-solid fa-pen text-sm"></i>
                                            </a>
                                            <form action="{{ route('bidan.jadwal.destroy', $jadwal) }}" method="POST" data-delete-form data-delete-title="Hapus jadwal ini?" class="inline-block">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-pill inline-flex h-9 w-9 items-center justify-center border border-slate-200 bg-white text-slate-500 hover:border-rose-300 hover:text-rose-600 hover:bg-rose-50 transition shadow-sm" title="Hapus">
                                                    <i class="fa-solid fa-trash text-sm"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex h-9 w-9 items-center justify-center border border-slate-100 bg-slate-50 text-slate-300 rounded-full cursor-not-allowed" title="Terkunci"><i class="fa-solid fa-lock text-sm"></i></span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-14 text-center bg-white">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-slate-50 border border-slate-100 text-slate-300 mb-4 shadow-inner">
                                        <i class="fa-solid fa-folder-open text-3xl"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700">Belum Ada Jadwal</h3>
                                    <p class="text-xs font-medium text-slate-400 mt-1 mb-4">Agenda posyandu belum ditambahkan.</p>
                                    <a href="{{ route('bidan.jadwal.create') }}" class="btn-pill bg-emerald-500 text-white px-5 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-md">Buat Jadwal</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE VIEW --}}
            <div id="jadwalCardContainer" class="space-y-4 lg:hidden p-4">
                @forelse($jadwals as $jadwal)
                    @php
                        $sTitle = mb_strtolower(trim((string) ($jadwal->judul ?? '')), 'UTF-8');
                        $sLoc = mb_strtolower(trim((string) ($jadwal->lokasi ?? '')), 'UTF-8');
                        $sStat = strtolower((string) ($jadwal->status ?? 'aktif'));
                        $sKat = strtolower((string) ($jadwal->kategori ?? 'posyandu'));
                        $sTarg = strtolower((string) ($jadwal->target_peserta ?? 'semua'));
                        
                        $statData = $statusTheme($jadwal->status ?? 'aktif');
                        $katData = $kategoriTheme($jadwal->kategori ?? 'posyandu');
                        $targData = $targetTheme($jadwal->target_peserta ?? 'semua');
                        
                        $katLabel = $kategoriOptions[$sKat]['label'] ?? ucfirst($sKat);
                    @endphp
                    <article class="js-jadwal-row rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm" data-title="{{ $sTitle }}" data-location="{{ $sLoc }}" data-status="{{ $sStat }}" data-kategori="{{ $sKat }}" data-target="{{ $sTarg }}">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $jadwal->judul }}</h3>
                                <p class="text-[10px] text-slate-400 mt-0.5"><i class="fa-solid fa-calendar-day mr-1"></i> {{ $formatTanggal($jadwal->tanggal) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mb-4 bg-slate-50 p-2 rounded-lg border border-slate-100">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">{{ $katLabel }}</span>
                            <span class="text-slate-300">•</span>
                            <span class="text-[10px] font-bold text-slate-500"><i class="fa-solid {{ $statData['icon'] }} mr-1"></i>{{ $statData['label'] }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-50">
                            <a href="{{ route('bidan.jadwal.show', $jadwal) }}" class="btn-pill inline-flex justify-center items-center border border-slate-200 bg-slate-50 px-3 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-500">Detail</a>
                            @if($canModifyJadwal($jadwal))
                                <a href="{{ route('bidan.jadwal.edit', $jadwal) }}" class="btn-pill inline-flex justify-center items-center border border-slate-200 bg-white px-3 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-600 shadow-sm">Edit</a>
                            @else
                                <span class="btn-pill inline-flex justify-center items-center border border-slate-100 bg-slate-50 px-3 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-400 cursor-not-allowed"><i class="fa-solid fa-lock mr-1"></i> Terkunci</span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="text-center py-6 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                        <p class="text-xs font-bold text-slate-400">Belum ada jadwal</p>
                    </div>
                @endforelse
            </div>

            {{-- LIVE EMPTY STATE --}}
            <div id="jadwalNoLiveResult" class="hidden rounded-[2rem] border border-dashed border-slate-300 bg-white/50 p-10 text-center m-6">
                <i class="fa-solid fa-magnifying-glass text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-sm font-black text-slate-700">Jadwal Tidak Ditemukan</h3>
                <p class="text-xs font-medium text-slate-500 mt-1">Pencarian atau filter Anda tidak cocok dengan data manapun.</p>
            </div>
        </div>

        {{-- PAGINATION CUSTOM UI (Rounded/Membulat) --}}
        @if(method_exists($jadwals, 'hasPages') && $jadwals->hasPages())
            <div id="jadwalPagination" class="bg-white p-6 border-t border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shrink-0 rounded-b-[2rem]">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    Menampilkan <span class="text-slate-700">{{ $jadwals->firstItem() }}</span> - <span class="text-slate-700">{{ $jadwals->lastItem() }}</span> dari <span class="text-slate-800">{{ $jadwals->total() }}</span> data
                </p>
                
                <div class="flex items-center gap-1.5">
                    {{-- Previous --}}
                    @if ($jadwals->onFirstPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-100"><i class="fa-solid fa-chevron-left text-[10px]"></i></span>
                    @else
                        <a href="{{ $jadwals->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-left text-[10px]"></i></a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $start = max(1, $jadwals->currentPage() - 1);
                        $end = min($jadwals->lastPage(), $jadwals->currentPage() + 1);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $jadwals->url(1) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">1</a>
                        @if($start > 2)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                    @endif

                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $jadwals->currentPage())
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md shadow-emerald-500/30 text-xs font-black">{{ $page }}</span>
                        @else
                            <a href="{{ $jadwals->url($page) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $jadwals->lastPage())
                        @if($end < $jadwals->lastPage() - 1)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                        <a href="{{ $jadwals->url($jadwals->lastPage()) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">{{ $jadwals->lastPage() }}</a>
                    @endif

                    {{-- Next --}}
                    @if ($jadwals->hasMorePages())
                        <a href="{{ $jadwals->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-right text-[10px]"></i></a>
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-100"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>

{{-- MODAL HAPUS --}}
<div id="pcJadwalDeleteModal" class="pc-modal-backdrop">
    <div class="pc-modal-card text-center">
        <div class="w-20 h-20 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-5 text-rose-500 shadow-inner">
            <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
        </div>
        <h3 id="pcJadwalDeleteTitle" class="text-2xl font-black text-slate-800 mb-2">Hapus Jadwal?</h3>
        <p id="pcJadwalDeleteMessage" class="text-sm font-medium text-slate-500 mb-6 leading-relaxed px-4">Jadwal yang dihapus tidak akan tampil lagi di sistem.</p>
        <div class="flex gap-3">
            <button type="button" id="pcJadwalDeleteCancel" class="btn-pill w-full flex-1 border border-slate-200 bg-white text-slate-700 px-4 py-3.5 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">Batal</button>
            <button type="button" id="pcJadwalDeleteSubmit" class="btn-pill w-full flex-1 bg-gradient-to-r from-rose-500 to-orange-500 text-white px-4 py-3.5 text-sm font-bold shadow-md hover:from-rose-600 hover:to-orange-600 transition-all flex items-center justify-center gap-2"><i class="fa-solid fa-trash"></i> Hapus</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // Live Search Filter
    const searchInput = document.querySelector('#jadwalSearch');
    const statusInput = document.querySelector('#jadwalStatus');
    const kategoriInput = document.querySelector('#jadwalKategori');
    const targetInput = document.querySelector('#jadwalTarget');
    const visibleCounter = document.querySelector('#jadwalVisibleCounter');
    const emptyLive = document.querySelector('#jadwalNoLiveResult');
    const pagination = document.querySelector('#jadwalPagination');
    const rows = Array.from(document.querySelectorAll('.js-jadwal-row'));

    const normalize = val => String(val || '').toLowerCase().trim();

    function filterRows() {
        const keyword = searchInput ? normalize(searchInput.value) : '';
        const status = statusInput ? statusInput.value : 'semua';
        const kategori = kategoriInput ? kategoriInput.value : 'semua';
        const target = targetInput ? targetInput.value : 'semua';

        let totalVisible = 0;
        rows.forEach(row => {
            const title = row.dataset.title;
            const loc = row.dataset.location;
            const rStat = row.dataset.status;
            const rKat = row.dataset.kategori;
            const rTarg = row.dataset.target;

            let visible = true;
            if (keyword !== '') visible = title.includes(keyword) || loc.includes(keyword);
            if (visible && status !== 'semua') visible = rStat === status;
            if (visible && kategori !== 'semua') visible = rKat === kategori;
            if (visible && target !== 'semua') visible = rTarg === target;

            row.classList.toggle('hidden', !visible);
            if (visible) totalVisible++;
        });

        if (visibleCounter) visibleCounter.textContent = totalVisible;
        if (emptyLive) emptyLive.classList.toggle('hidden', totalVisible > 0);
        if (pagination) pagination.classList.toggle('hidden', keyword !== '' || status !== 'semua' || kategori !== 'semua' || target !== 'semua');
    }

    if (searchInput) searchInput.addEventListener('input', filterRows);
    [statusInput, kategoriInput, targetInput].forEach(inp => { if(inp) inp.addEventListener('change', filterRows); });

    // Modal Logic
    let modal = document.querySelector('#pcJadwalDeleteModal');
    const modalCancel = document.querySelector('#pcJadwalDeleteCancel');
    const modalSubmit = document.querySelector('#pcJadwalDeleteSubmit');
    let selectedForm = null;

    document.addEventListener('submit', e => {
        const form = e.target.closest('[data-delete-form]');
        if (!form) return;
        e.preventDefault();
        selectedForm = form;
        modal.classList.add('is-open');
    }, true);

    modalCancel?.addEventListener('click', () => modal.classList.remove('is-open'));
    modalSubmit?.addEventListener('click', () => {
        if(selectedForm) {
            modalSubmit.disabled = true;
            modalSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';
            HTMLFormElement.prototype.submit.call(selectedForm);
        }
    });
})();
</script>
@endpush