@extends('layouts.kader')

@section('title', 'Log Imunisasi')
@section('page-name', 'Log Imunisasi')
@section('page-title', 'Log Imunisasi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    // Filter Variables
    $search = trim((string) request('search', ''));
    $vaksin = request('vaksin', 'semua');
    
    $activeFilterCount = collect([$search, $vaksin !== 'semua' ? $vaksin : null])->filter()->count();

    // ==========================================
    // FIX ERROR: DEKLARASI $filterCaption
    // ==========================================
    $filterCaption = 'Semua Riwayat';
    if ($search) {
        $filterCaption = 'Pencarian: "' . $search . '"';
    } elseif ($vaksin !== 'semua') {
        $filterCaption = 'Filter Vaksin: ' . strtoupper($vaksin);
    }

    // Identifikasi Data (Menggunakan Fallback agar aman dari error backend)
    $items = $imunisasis ?? $items ?? $imunisasi ?? collect();
    $totalData = method_exists($items, 'total') ? $items->total() : $items->count();

    $statTotal = $statTotal ?? $totalData;
    $statBulanIni = $statBulanIni ?? 0;
    $statVaksinDasar = $statVaksinDasar ?? 0;
    $statLanjutan = $statLanjutan ?? 0;

    $rangeText = method_exists($items, 'firstItem')
        ? 'Menampilkan ' . (($items->firstItem() ?? 0)) . '-' . (($items->lastItem() ?? 0)) . ' dari ' . $items->total() . ' log'
        : 'Menampilkan ' . $items->count() . ' log';
        
    $formatDate = function ($value) {
        if (! $value) return '-';
        return Carbon::parse($value)->translatedFormat('d M Y');
    };
    
    $initial = function ($name) {
        return Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'B';
    };
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image: radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .animate-pop-in {
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 2rem; 
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.95); }

    /* Custom Scrollbar */
    .pc-scroll-container {
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, 0.4) transparent;
        padding-right: 8px;
    }
    .pc-scroll-container::-webkit-scrollbar { width: 6px; }
    .pc-scroll-container::-webkit-scrollbar-track { background: transparent; }
    .pc-scroll-container::-webkit-scrollbar-thumb { background-color: rgba(16, 185, 129, 0.4); border-radius: 999px; }

    /* Layout Data Card (Grid Kerapihan) */
    .data-row {
        display: grid;
        grid-template-columns: minmax(220px, 1.2fr) minmax(150px, 0.8fr) minmax(180px, 1fr) minmax(150px, 0.8fr) 120px;
        gap: 16px;
        align-items: start;
    }

    .row-action {
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        border: 1px solid transparent;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none;
        width: 100%;
        background: #ecfdf5; border-color: #a7f3d0; color: #059669;
    }
    .row-action:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(16,185,129,0.15); }

    @media (max-width: 1280px) {
        .data-row { grid-template-columns: minmax(0, 1fr); align-items: start; gap: 12px;}
        .col-hidden-mobile { display: none; }
    }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- HERO WIDGET --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 lg:items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-full shadow-sm flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-amber-300 animate-pulse"></span>
                        Rekam Medis Terpadu
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Log Imunisasi Warga
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Pantau riwayat pemberian vaksin dan imunisasi pada sasaran Balita. Halaman ini bersifat <strong class="font-black">Mode Baca Saja (Read-Only)</strong> bagi Kader untuk tujuan transparansi dan laporan.
                </p>
            </div>
        </div>
    </section>

    {{-- STATISTIK GRID --}}
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="widget-card p-6 flex justify-between items-center transition-transform hover:-translate-y-1 hover:shadow-lg">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Log</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statTotal) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center text-2xl shadow-inner">
                <i class="fa-solid fa-syringe"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center transition-transform hover:-translate-y-1 hover:shadow-lg">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-teal-600">Bulan Ini</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statBulanIni) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-100 text-teal-500 flex items-center justify-center text-2xl shadow-inner">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center transition-transform hover:-translate-y-1 hover:shadow-lg">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-amber-600">Vaksin Dasar</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statVaksinDasar) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-100 text-amber-500 flex items-center justify-center text-2xl shadow-inner">
                <i class="fa-solid fa-shield-virus"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center transition-transform hover:-translate-y-1 hover:shadow-lg">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-sky-600">Lanjutan</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statLanjutan) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-sky-50 border border-sky-100 text-sky-500 flex items-center justify-center text-2xl shadow-inner">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>
    </section>

    {{-- FILTER WIDGET --}}
    <form action="{{ url()->current() }}" method="GET" class="widget-card p-4 sm:p-6 flex flex-col lg:flex-row gap-4 items-center">
        <div class="w-full lg:flex-1 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 z-10"></i>
            <input type="text" name="search" value="{{ $search }}" autocomplete="off" placeholder="Ketik nama sasaran atau NIK..." class="w-full btn-pill border border-slate-200 bg-slate-50/50 hover:bg-white py-3.5 pl-11 pr-4 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10 shadow-inner">
        </div>

        <select name="vaksin" class="w-full lg:w-48 btn-pill border border-slate-200 bg-slate-50/50 hover:bg-white px-4 py-3.5 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-400 shadow-inner appearance-none cursor-pointer">
            <option value="semua">Semua Vaksin</option>
            <option value="bcg" @selected($vaksin === 'bcg')>BCG</option>
            <option value="dpt" @selected($vaksin === 'dpt')>DPT</option>
            <option value="polio" @selected($vaksin === 'polio')>Polio</option>
            <option value="campak" @selected($vaksin === 'campak')>Campak</option>
        </select>

        <div class="flex gap-2 w-full lg:w-auto">
            <button type="submit" class="flex-1 lg:flex-none btn-pill bg-slate-800 hover:bg-slate-700 px-6 py-3.5 text-sm font-bold text-white shadow-md transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if($activeFilterCount > 0)
                <a href="{{ url()->current() }}" class="btn-pill border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 w-12 flex items-center justify-center text-slate-400 shadow-sm transition" title="Reset filter">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- MAIN DATA WIDGET --}}
    <section class="widget-card overflow-hidden">
        
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900">Daftar Log Imunisasi</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Menampilkan {{ number_format($totalData) }} data <span class="mx-1">•</span> <span class="text-emerald-600 font-bold">{{ $filterCaption }}</span>
                </p>
            </div>

            <div class="flex items-center">
                <span class="btn-pill bg-slate-200/50 border border-slate-200 text-slate-500 px-4 py-2 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 cursor-not-allowed">
                    <i class="fa-solid fa-lock"></i> Mode Baca Saja
                </span>
            </div>
        </div>

        <div class="pc-scroll-container max-h-[600px] p-4 sm:p-6 space-y-4 bg-white/40">
            @forelse($items as $item)
                @php
    // Fallback Property Access (Disesuaikan dengan relasi di Controller)
    $nama = $item->kunjungan->pasien->nama_lengkap ?? $item->kunjungan->pasien->nama ?? '-';
    $nik = $item->kunjungan->pasien->nik ?? '-';
    $namaVaksin = $item->vaksin ?? $item->jenis_imunisasi ?? 'Vaksin Umum';
    $tanggal = $formatDate($item->tanggal_imunisasi ?? $item->created_at);
    $petugas = $item->kunjungan->petugas->name ?? $item->kunjungan->petugas->nama ?? 'Petugas Medis';
    $usiaVaksin = $item->usia_saat_vaksin ?? 'Sesuai Jadwal';
@endphp

                <article class="bg-white border border-slate-100 rounded-[1.5rem] p-5 shadow-sm hover:shadow-md hover:border-emerald-300 transition duration-300">
                    <div class="data-row">
                        
                        {{-- 1. Identitas Penerima --}}
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-12 h-12 shrink-0 rounded-[14px] bg-slate-900 text-emerald-400 flex items-center justify-center text-xl font-black shadow-md">
                                {{ $initial($nama) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-base font-black text-slate-900 leading-tight" title="{{ $nama }}">{{ $nama }}</h3>
                                <p class="mt-1 text-[11px] font-bold text-slate-500">NIK {{ $nik }}</p>
                            </div>
                        </div>

                        {{-- 2. Jenis Vaksin & Usia --}}
                        <div class="col-hidden-mobile min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Vaksin Diberikan</p>
                            <p class="text-sm font-black text-emerald-600 truncate">{{ strtoupper($namaVaksin) }}</p>
                            <p class="text-[11px] font-bold text-slate-500 mt-1 truncate">Usia: {{ $usiaVaksin }}</p>
                        </div>

                        {{-- 3. Tanggal Imunisasi --}}
                        <div class="col-hidden-mobile min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Waktu Pelaksanaan</p>
                            <p class="text-sm font-black text-slate-800"><i class="fa-regular fa-calendar-check text-emerald-500 mr-1"></i> {{ $tanggal }}</p>
                        </div>

                        {{-- 4. Petugas Medis --}}
                        <div class="col-hidden-mobile min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Petugas / Bidan</p>
                            <p class="text-xs font-bold text-slate-700 truncate"><i class="fa-solid fa-user-nurse text-sky-500 mr-1"></i> {{ $petugas }}</p>
                        </div>

                        {{-- 5. Aksi (Detail Saja) --}}
                        <div class="flex items-center justify-end w-full">
                            @if($routeHas('kader.imunisasi.show'))
                                <a href="{{ route('kader.imunisasi.show', $item->id) }}" class="row-action" title="Lihat Rekam Medis">
                                    <i class="fa-solid fa-file-medical mr-1.5"></i> Detail
                                </a>
                            @else
                                <span class="row-action opacity-50 cursor-not-allowed" title="Akses Dibatasi">
                                    <i class="fa-solid fa-lock mr-1.5"></i> Terkunci
                                </span>
                            @endif
                        </div>

                    </div>
                </article>
            @empty
                <div class="rounded-[2rem] border-2 border-dashed border-emerald-200 bg-emerald-50/50 p-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-3xl font-black text-emerald-400 shadow-sm mb-4">
                        <i class="fa-solid fa-syringe"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800">Log Imunisasi Kosong</h3>
                    <p class="mx-auto mt-2 max-w-sm text-sm font-semibold leading-6 text-slate-500">Tidak ada riwayat imunisasi yang cocok dengan filter pencarian, atau database belum diisi oleh Bidan.</p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if(method_exists($items, 'links') && $items->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Halaman <span class="text-slate-900">{{ $items->currentPage() }}</span> dari <span class="text-slate-900">{{ $items->lastPage() }}</span>
                </p>
                <div class="flex items-center gap-2">
                    @if ($items->onFirstPage())
                        <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60"><i class="fas fa-chevron-left text-xs"></i></button>
                    @else
                        <a href="{{ $items->previousPageUrl() }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600 transition-all"><i class="fas fa-chevron-left text-xs"></i></a>
                    @endif

                    @php
                        $start = max(1, $items->currentPage() - 2);
                        $end = min($items->lastPage(), $items->currentPage() + 2);
                    @endphp
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $items->currentPage())
                            <span class="btn-pill w-10 h-10 flex items-center justify-center bg-emerald-500 text-white font-black text-sm shadow-md pointer-events-none">{{ $page }}</span>
                        @else
                            <a href="{{ $items->url($page) }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-sm shadow-sm hover:bg-emerald-50 hover:text-emerald-600 transition-all">{{ $page }}</a>
                        @endif
                    @endfor

                    @if ($items->hasMorePages())
                        <a href="{{ $items->nextPageUrl() }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600 transition-all"><i class="fas fa-chevron-right text-xs"></i></a>
                    @else
                        <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60"><i class="fas fa-chevron-right text-xs"></i></button>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>
@endsection