@extends('layouts.kader')

@section('title', 'Data Balita')
@section('page-name', 'Data Balita')
@section('page-title', 'Data Balita')

@section('content')
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $search = trim((string) ($search ?? request('search', '')));
    $statusAkun = request('status_akun', request('status', 'semua')); 
    $jenisKelamin = request('jenis_kelamin', 'semua'); 

    $statusOptions = [
        'semua' => 'Semua Status',
        'terhubung' => 'Terhubung',
        'belum' => 'Belum Terhubung',
    ];

    $indexRoute = $routeHas('kader.data.balita.index')
        ? route('kader.data.balita.index')
        : url('/kader/data/balita');

    $createRoute = $routeHas('kader.data.balita.create')
        ? route('kader.data.balita.create')
        : url('/kader/data/balita/create');

    $bulkDeleteRoute = $routeHas('kader.data.balita.bulk-delete')
        ? route('kader.data.balita.bulk-delete')
        : url('/kader/data/balita/bulk-delete');

    $templateRoute = $routeHas('kader.import.template')
        ? route('kader.import.template', ['type' => 'balita'])
        : null;

    $importRoute = $routeHas('kader.import.index')
        ? route('kader.import.index', ['type' => 'balita'])
        : null;

    $totalData = method_exists($items, 'total') ? $items->total() : $items->count();

    $statTotal = $statTotal ?? $totalData;
    $statTerhubung = $statTerhubung ?? 0;
    $statBelumTerhubung = $statBelumTerhubung ?? 0;
    $statBulanIni = $statBulanIni ?? 0;

    $rangeText = method_exists($items, 'firstItem')
        ? 'Menampilkan ' . (($items->firstItem() ?? 0)) . '-' . (($items->lastItem() ?? 0)) . ' dari ' . $items->total() . ' sasaran'
        : 'Menampilkan ' . $items->count() . ' sasaran';

    // PENGURUTAN ABJAD (A-Z) SECARA OTOMATIS
    if (isset($items)) {
        if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator || $items instanceof \Illuminate\Pagination\Paginator) {
            $sorted = $items->getCollection()->sortBy('nama_lengkap', SORT_NATURAL | SORT_FLAG_CASE)->values();
            $items->setCollection($sorted);
        } else if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->sortBy('nama_lengkap', SORT_NATURAL | SORT_FLAG_CASE)->values();
        }
    }

    $formatDate = function ($value) {
        if (! $value) return '-';
        return Carbon::parse($value)->translatedFormat('d M Y');
    };

    $getAge = function ($balita) {
        if (isset($balita->usia_label) && filled($balita->usia_label)) return $balita->usia_label;
        if (! $balita->tanggal_lahir) return '-';

        $months = (int) Carbon::parse($balita->tanggal_lahir)->diffInMonths(now());
        if ($months < 12) return $months . ' bulan';

        $years = intdiv($months, 12);
        $rest = $months % 12;

        return $rest > 0 ? $years . ' thn ' . $rest . ' bln' : $years . ' tahun';
    };

    $initial = function ($name) {
        return Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'B';
    };

    $genderLabel = function ($gender) {
        return match ($gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    };

    $genderClass = function ($gender) {
        return match ($gender) {
            'L' => 'text-sky-600 border-sky-200 bg-sky-50',
            'P' => 'text-rose-600 border-rose-200 bg-rose-50',
            default => 'text-slate-600 border-slate-200 bg-slate-50',
        };
    };

    $isConnected = function ($balita) {
        return filled($balita->user_id ?? null);
    };

    $accountLabel = function ($balita) use ($isConnected) {
        return $isConnected($balita) ? 'Terhubung' : 'Belum Terhubung';
    };

    $accountClass = function ($balita) use ($isConnected) {
        return $isConnected($balita)
            ? 'text-emerald-700 border-emerald-200 bg-emerald-50'
            : 'text-amber-700 border-amber-200 bg-amber-50';
    };

    $activeFilterCount = collect([$search, $statusAkun !== 'semua' ? $statusAkun : null])->filter(fn($v) => filled($v))->count();
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
        animation: popIn .4s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.98) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 2rem; 
        box-shadow: 0 10px 40px -10px rgba(15, 23, 42, 0.04);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }

    /* Custom Dropdown Animation */
    .dropdown-open {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0) !important;
    }
    .dropdown-icon-rotate {
        transform: rotate(180deg);
    }

    /* Scroll Optimization */
    .pc-scroll-container {
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, 0.3) transparent;
        padding-right: 8px;
        scroll-behavior: smooth !important;
        -webkit-overflow-scrolling: touch;
        transform: translateZ(0); 
        will-change: scroll-position;
        contain: paint;
    }
    .pc-scroll-container::-webkit-scrollbar { width: 6px; }
    .pc-scroll-container::-webkit-scrollbar-track { background: transparent; }
    .pc-scroll-container::-webkit-scrollbar-thumb { background-color: rgba(16, 185, 129, 0.3); border-radius: 999px; }

    /* Grid Layout Bersih & Responsif */
    .data-row {
        display: grid;
        grid-template-columns: 24px minmax(220px, 1.2fr) minmax(180px, 1fr) minmax(180px, 1fr) 140px;
        gap: 16px;
        align-items: center;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .action-box { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }

    .row-action {
        height: 32px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; border: 1px solid transparent; font-size: 10px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.05em; transition: all .2s ease; cursor: pointer; text-decoration: none;
    }
    .row-action:hover { transform: translateY(-1px); }
    
    .action-detail { background: #f8fafc; border-color: #e2e8f0; color: #475569; }
    .action-detail:hover { background: #ecfdf5; border-color: #a7f3d0; color: #059669; }
    
    .action-edit { background: #f8fafc; border-color: #e2e8f0; color: #475569; }
    .action-edit:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
    
    .action-sync { grid-column: span 2; background: #fffbeb; border-color: #fde68a; color: #d97706; }
    .action-sync:hover { background: #fef3c7; }

    .action-delete { grid-column: span 2; background: #fff1f2; border-color: #fecdd3; color: #e11d48; }
    .action-delete:hover { background: #ffe4e6; }

    /* Modal Full Screen */
    .pc-modal-backdrop {
        position: fixed; inset: 0; z-index: 999999; display: none; align-items: center; justify-content: center;
        background: rgba(15, 23, 42, .5); backdrop-filter: blur(8px); padding: 1rem; width: 100vw; height: 100vh;
    }
    .pc-modal-backdrop.is-open { display: flex; }
    .pc-modal-card {
        width: 100%; max-width: 420px; background: white; border-radius: 2rem; padding: 0; overflow: hidden;
        transform: scale(0.95) translateY(15px); opacity: 0; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }

    @media (max-width: 1280px) {
        .data-row { grid-template-columns: 24px minmax(0, 1fr); align-items: start; }
        .col-hidden-mobile { display: none; }
        .col-stack-mobile { display: flex; flex-direction: column; gap: 10px; }
    }
</style>
@endpush

<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 relative">

    {{-- SYSTEM ALERT: CLEAN & RAMAH (Menyatu dengan UI) --}}
    @if(session('success') || session('error') || session('warning'))
        @php
            $flashType = session('error') ? 'error' : (session('warning') ? 'warning' : 'success');
            
            $flashConfig = match ($flashType) {
                'error' => [
                    'iconBg' => 'bg-rose-50',
                    'iconColor' => 'text-rose-500',
                    'icon' => 'fa-circle-exclamation',
                    'title' => 'Gagal Sinkronisasi',
                ],
                'warning' => [
                    'iconBg' => 'bg-amber-50',
                    'iconColor' => 'text-amber-500',
                    'icon' => 'fa-triangle-exclamation',
                    'title' => 'Perhatian',
                ],
                default => [
                    'iconBg' => 'bg-emerald-50',
                    'iconColor' => 'text-emerald-500',
                    'icon' => 'fa-circle-check',
                    'title' => 'Berhasil',
                ],
            };

            $flashMessage = session($flashType);
            
            if (str_contains($flashMessage, 'Akun warga dengan NIK') && str_contains($flashMessage, 'belum ditemukan')) {
                $flashMessage = 'Sistem tidak menemukan akun warga dengan NIK tersebut. Silakan minta Admin mendaftarkannya.';
            }
        @endphp
        
        <div id="clean-friendly-alert" class="w-full bg-white border border-slate-100 rounded-[1.5rem] p-4 sm:px-5 sm:py-4 mb-8 flex items-center justify-between gap-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300">
            <div class="flex items-center gap-4 flex-1">
                <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center {{ $flashConfig['iconBg'] }} {{ $flashConfig['iconColor'] }}">
                    <i class="fa-solid {{ $flashConfig['icon'] }} text-xl"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-slate-800 tracking-tight leading-tight mb-0.5">{{ $flashConfig['title'] }}</h3>
                    <p class="text-[13px] font-medium text-slate-500 leading-snug">{{ $flashMessage }}</p>
                </div>
            </div>
            
            <button type="button" onclick="closeCleanAlert()" class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors" title="Tutup Notifikasi">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <script>
            function closeCleanAlert() {
                const alertWrapper = document.getElementById('clean-friendly-alert');
                if (alertWrapper) {
                    alertWrapper.style.opacity = '0';
                    alertWrapper.style.transform = 'scale(0.98)';
                    setTimeout(() => { alertWrapper.style.display = 'none'; }, 300);
                }
            }
            setTimeout(closeCleanAlert, 7000);
        </script>
    @endif

    {{-- HERO WIDGET --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 lg:items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-full shadow-sm flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-amber-300 animate-pulse"></span>
                        Master Data Sasaran
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Data Balita
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Kelola data Balita untuk absensi, pengukuran fisik, imunisasi, dan pelaporan Posyandu. Lakukan sinkronisasi akun agar orang tua dapat memantau grafik tumbuh kembang secara mandiri.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto shrink-0 justify-center">
                @if($templateRoute)
                    {{-- DIHAPUS TARGET BLANK AGAR TIDAK BUKA TAB BARU. DOWNLOAD TERJADI INSTAN DI HALAMAN SAMA --}}
                    <a href="{{ $templateRoute }}" download class="btn-pill bg-white/20 hover:bg-white/30 text-white border border-white/30 px-6 py-3.5 text-sm font-bold backdrop-blur-md flex items-center justify-center gap-2 transition-all shadow-sm">
                        <i class="fa-solid fa-download"></i> Template
                    </a>
                @endif
                
                @if($importRoute)
                    <a href="{{ $importRoute }}" class="btn-pill bg-teal-800/40 hover:bg-teal-800/60 text-white border border-teal-300/30 px-6 py-3.5 text-sm font-bold backdrop-blur-md flex items-center justify-center gap-2 transition-all shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Import
                    </a>
                @endif
                
                <a href="{{ $createRoute }}" class="btn-pill bg-white hover:bg-emerald-50 text-emerald-600 px-6 py-3.5 text-sm font-black shadow-[0_8px_20px_rgba(255,255,255,0.3)] flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus"></i> Tambah Data
                </a>
            </div>
        </div>
    </section>

    {{-- STATISTIK GRID --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1 transition-transform duration-300">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Sasaran</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statTotal) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center text-2xl shadow-inner group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1 transition-transform duration-300">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Terhubung</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statTerhubung) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-500 flex items-center justify-center text-2xl shadow-inner group-hover:-rotate-6 transition-transform">
                <i class="fa-solid fa-link"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1 transition-transform duration-300">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Belum Terhubung</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statBelumTerhubung) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-100 text-amber-500 flex items-center justify-center text-2xl shadow-inner group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-link-slash"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1 transition-transform duration-300">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Bulan Ini</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statBulanIni) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-sky-50 border border-sky-100 text-sky-500 flex items-center justify-center text-2xl shadow-inner group-hover:-rotate-6 transition-transform">
                <i class="fa-solid fa-user-plus"></i>
            </div>
        </div>
    </section>

    {{-- FILTER WIDGET --}}
    <form id="liveSearchForm" action="{{ $indexRoute }}" method="GET" class="widget-card p-4 sm:p-5 flex flex-col lg:flex-row gap-4 items-center z-20 relative mb-8">
        <input type="hidden" name="jenis_kelamin" value="semua">

        <div class="w-full lg:flex-1 relative flex items-center">
            <i class="fa-solid fa-magnifying-glass absolute left-4 text-slate-400 z-10 pointer-events-none"></i>
            <input type="text" id="liveSearchInput" name="search" value="{{ $search }}" autocomplete="off" placeholder="Ketik nama balita atau NIK..." class="w-full btn-pill border border-slate-200 bg-white/80 py-3.5 pl-11 pr-12 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10 shadow-inner">
            <div id="liveSearchState" class="absolute right-4 hidden pointer-events-none">
                <i class="fa-solid fa-circle-notch fa-spin text-emerald-500 text-sm"></i>
            </div>
        </div>

        <div class="relative w-full lg:w-48" id="customSelectContainer">
            <select id="status_akun" name="status_akun" class="hidden">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected($statusAkun === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="button" id="customSelectTrigger" class="w-full btn-pill border border-slate-200 bg-white/90 px-5 py-3.5 text-sm font-bold text-slate-700 outline-none transition hover:border-emerald-300 focus:bg-white focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10 shadow-inner flex justify-between items-center cursor-pointer">
                <span id="customSelectLabel">{{ $statusOptions[$statusAkun] ?? 'Semua Status' }}</span>
                <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300" id="customSelectIcon"></i>
            </button>

            <ul id="customSelectMenu" class="absolute z-[100] w-full mt-2 bg-white border border-slate-100 rounded-2xl shadow-xl opacity-0 invisible transform -translate-y-2 transition-all duration-200 overflow-hidden py-1.5">
                @foreach($statusOptions as $key => $label)
                    <li>
                        <button type="button" class="custom-select-option w-full text-left px-5 py-2.5 text-sm font-semibold transition-colors {{ $statusAkun === $key ? 'bg-emerald-50 text-emerald-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}" data-value="{{ $key }}">
                            {{ $label }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="flex gap-2 w-full lg:w-auto">
            <button type="submit" class="flex-1 lg:flex-none btn-pill bg-slate-800 hover:bg-slate-700 px-6 py-3.5 text-sm font-bold text-white shadow-md transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if($activeFilterCount > 0)
                <a href="{{ $indexRoute }}" id="resetSearchButton" class="btn-pill border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 w-12 flex items-center justify-center text-slate-400 shadow-sm transition" title="Reset filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- MAIN DATA WIDGET --}}
    <section id="balitaLiveRegion" data-live-region class="w-full">
        
        <div class="px-2 py-2 mb-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900">Daftar Sasaran Balita</h2>
                <p id="listRangeText" class="mt-1 text-xs font-semibold text-slate-500">{{ $rangeText }}</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <label class="btn-pill border border-slate-200 bg-white px-4 py-2 text-[11px] font-black uppercase tracking-wider text-slate-600 shadow-sm flex items-center justify-center gap-2 cursor-pointer hover:bg-slate-50 transition-colors">
                    <input type="checkbox" id="selectAllBalita" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400 cursor-pointer transition">
                    Pilih Semua
                </label>

                @if($routeHas('kader.data.balita.bulk-delete'))
                    <button type="button" id="bulkDeleteButton" disabled 
                            data-confirm-submit data-confirm-form="bulkDeleteForm"
                            data-confirm-title="Hapus Data Terpilih?" 
                            data-confirm-message="Balita yang dicentang akan dihapus secara permanen dari sistem. Pastikan pilihan sudah benar." 
                            data-confirm-tone="danger" 
                            class="btn-pill bg-white border border-rose-200 text-rose-600 px-4 py-2 text-[11px] font-black uppercase tracking-wider shadow-sm transition opacity-50 cursor-not-allowed hover:bg-rose-500 hover:text-white">
                        <i class="fa-solid fa-trash-can mr-1"></i> Hapus Terpilih
                    </button>
                @endif
            </div>
        </div>

        <div class="pc-scroll-container max-h-[600px] py-2 space-y-3">
            @forelse($items as $item)
                @php
                    $name = $item->nama_lengkap ?? '-';
                    $nik = $item->nik ?? '-';
                    $connected = $isConnected($item);
                @endphp

                <article class="bg-white border border-slate-100 rounded-[1.25rem] p-4 shadow-sm hover:shadow-md hover:border-emerald-200 transition duration-300 transform translate-z-0">
                    <div class="data-row col-stack-mobile">
                        
                        <div class="pt-2 xl:pt-0 flex items-center justify-center relative z-10">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulkDeleteForm" class="balita-check h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        </div>

                        <div class="flex items-start gap-4 min-w-0 relative z-10">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-50 border border-slate-100 text-lg font-black text-slate-700 shadow-sm mt-0.5">
                                {{ $initial($name) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-[15px] font-bold text-slate-800 leading-tight" title="{{ $name }}">{{ $name }}</h3>
                                <p class="mt-1 truncate text-[11px] font-bold text-slate-400 uppercase tracking-wide">NIK {{ $nik }}</p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="rounded-[6px] border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $genderClass($item->jenis_kelamin) }}">{{ $genderLabel($item->jenis_kelamin) }}</span>
                                    <span class="rounded-[6px] border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $accountClass($item) }}">{{ $accountLabel($item) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-hidden-mobile min-w-0 relative z-10">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Usia & TTL</p>
                            <p class="text-[13px] font-bold text-slate-700 leading-snug" title="{{ $getAge($item) }}">{{ $getAge($item) }}</p>
                            <p class="text-[11px] font-semibold text-slate-500 mt-1">{{ $formatDate($item->tanggal_lahir) }}</p>
                        </div>

                        <div class="col-hidden-mobile min-w-0 relative z-10">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Orang Tua / Wali</p>
                            <p class="text-[13px] font-bold text-slate-700 truncate" title="{{ $item->nama_ibu }}">Ibu: {{ $item->nama_ibu ?: '-' }}</p>
                            <p class="text-[11px] font-semibold text-slate-500 mt-1 line-clamp-2 leading-snug" title="{{ $item->alamat }}">{{ $item->alamat ?: '-' }}</p>
                        </div>

                        <div class="action-box relative z-20">
                            @if($routeHas('kader.data.balita.show'))
                                <a href="{{ route('kader.data.balita.show', $item->id) }}" class="row-action action-detail" title="Lihat Detail"><i class="fa-solid fa-eye"></i></a>
                            @endif

                            @if($routeHas('kader.data.balita.edit'))
                                <a href="{{ route('kader.data.balita.edit', $item->id) }}" class="row-action action-edit" title="Edit Data"><i class="fa-solid fa-pen"></i></a>
                            @endif

                            @if(! $connected)
                                @php
                                    $syncUrl = $routeHas('kader.data.balita.sync') ? route('kader.data.balita.sync', $item->id) : url('/kader/data/balita/' . $item->id . '/sync');
                                @endphp
                                <button type="button" 
                                        class="row-action action-sync" 
                                        data-confirm-submit
                                        data-action-url="{{ $syncUrl }}"
                                        data-action-method="POST"
                                        data-confirm-title="Sinkronkan Akun?" 
                                        data-confirm-message="Sistem akan mencoba menghubungkan data Balita ini dengan akun orang tua berdasarkan NIK yang sama." 
                                        data-confirm-tone="gold">
                                    <i class="fa-solid fa-rotate mr-1"></i> Sinkron
                                </button>
                            @endif

                            @if($routeHas('kader.data.balita.destroy'))
                                <button type="button" 
                                        class="row-action action-delete" 
                                        data-confirm-submit
                                        data-action-url="{{ route('kader.data.balita.destroy', $item->id) }}"
                                        data-action-method="DELETE"
                                        data-confirm-title="Hapus Data Balita?" 
                                        data-confirm-message="Data Balita ini akan dihapus permanen. Tindakan ini tidak bisa dibatalkan." 
                                        data-confirm-tone="danger">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-white/50 p-12 text-center transition-all">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-3xl font-black text-slate-300 shadow-sm mb-4">
                        <i class="fa-solid fa-child-reaching"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800">Data Balita Kosong</h3>
                    <p class="mx-auto mt-2 max-w-sm text-sm font-semibold leading-6 text-slate-500">Tidak ada sasaran yang cocok dengan pencarian, atau database masih kosong.</p>
                </div>
            @endforelse
        </div>

        @if(method_exists($items, 'hasPages') && $items->hasPages())
            <div id="balitaPagination" class="px-2 py-6 mt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Halaman <span class="text-slate-900">{{ $items->currentPage() }}</span> dari <span class="text-slate-900">{{ $items->lastPage() }}</span>
                </p>
                
                <div class="flex items-center gap-2">
                    @if ($items->onFirstPage())
                        <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                    @else
                        <a href="{{ $items->previousPageUrl() }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>
                    @endif

                    @php
                        $start = max(1, $items->currentPage() - 2);
                        $end = min($items->lastPage(), $items->currentPage() + 2);
                    @endphp
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $items->currentPage())
                            <span class="btn-pill w-10 h-10 flex items-center justify-center bg-emerald-500 text-white font-black text-sm shadow-md pointer-events-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $items->url($page) }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-sm shadow-sm hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    @if ($items->hasMorePages())
                        <a href="{{ $items->nextPageUrl() }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    @else
                        <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </section>

    @if($routeHas('kader.data.balita.bulk-delete'))
        <form id="bulkDeleteForm" action="{{ $bulkDeleteRoute }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <form id="globalActionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="globalActionMethod" value="POST">
    </form>
</div>
@endsection

@push('modals')
<div id="nexusConfirmModal" class="pc-modal-backdrop">
    <div class="pc-modal-card">
        <div id="nexusConfirmHeader" class="relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-emerald-600 to-teal-700">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="absolute -bottom-16 left-8 h-28 w-44 rounded-t-[80px] bg-amber-300/15 pointer-events-none"></div>

            <div class="relative z-10">
                <i id="nexusConfirmIcon" class="fa-solid fa-circle-exclamation text-4xl mb-3 opacity-90"></i>
                <h3 id="nexusConfirmTitle" class="text-xl font-black tracking-tight mb-1">Konfirmasi Aksi</h3>
                <p id="nexusConfirmMessage" class="text-xs font-semibold leading-relaxed opacity-80 px-4">
                    Pastikan data sudah benar sebelum melanjutkan.
                </p>
            </div>
        </div>

        <div class="p-6 bg-white text-center">
            <div class="grid grid-cols-2 gap-3">
                <button type="button" id="nexusConfirmCancel" class="btn-pill h-11 border border-slate-200 bg-white text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="button" id="nexusConfirmOk" class="btn-pill h-11 bg-emerald-600 text-sm font-bold text-white shadow-md hover:bg-emerald-700 transition-colors">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    window.nexusPendingForm = null;
    window.nexusPendingUrl = null;
    window.nexusPendingMethod = null;

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('liveSearchForm');
        var searchInput = document.getElementById('liveSearchInput');
        var statusInput = document.getElementById('status_akun'); 
        var resetButton = document.getElementById('resetSearchButton');
        var liveRegion = document.querySelector('[data-live-region]');
        var liveState = document.getElementById('liveSearchState');
        
        var liveTimer = null;
        var liveController = null;

        /* ====================================================
           CUSTOM DROPDOWN LOGIC
           ==================================================== */
        var customSelectTrigger = document.getElementById('customSelectTrigger');
        var customSelectMenu = document.getElementById('customSelectMenu');
        var customSelectIcon = document.getElementById('customSelectIcon');
        var customSelectLabel = document.getElementById('customSelectLabel');
        var customSelectOptions = document.querySelectorAll('.custom-select-option');

        if (customSelectTrigger && customSelectMenu) {
            customSelectTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                var isOpen = customSelectMenu.classList.contains('dropdown-open');
                
                document.querySelectorAll('.dropdown-open').forEach(el => {
                    el.classList.remove('dropdown-open');
                });
                document.querySelectorAll('.dropdown-icon-rotate').forEach(el => {
                    el.classList.remove('dropdown-icon-rotate');
                });

                if (!isOpen) {
                    customSelectMenu.classList.add('dropdown-open');
                    customSelectIcon.classList.add('dropdown-icon-rotate');
                    customSelectTrigger.classList.add('border-emerald-400', 'ring-4', 'ring-emerald-500/10');
                } else {
                    customSelectTrigger.classList.remove('border-emerald-400', 'ring-4', 'ring-emerald-500/10');
                }
            });

            document.addEventListener('click', function(e) {
                if (!customSelectTrigger.contains(e.target) && !customSelectMenu.contains(e.target)) {
                    customSelectMenu.classList.remove('dropdown-open');
                    customSelectIcon.classList.remove('dropdown-icon-rotate');
                    customSelectTrigger.classList.remove('border-emerald-400', 'ring-4', 'ring-emerald-500/10');
                }
            });

            customSelectOptions.forEach(function(optionBtn) {
                optionBtn.addEventListener('click', function() {
                    var val = this.getAttribute('data-value');
                    var txt = this.textContent.trim();

                    customSelectLabel.textContent = txt;

                    customSelectOptions.forEach(btn => {
                        btn.className = 'custom-select-option w-full text-left px-5 py-2.5 text-sm font-semibold transition-colors text-slate-600 hover:bg-slate-50 hover:text-slate-900';
                    });
                    this.className = 'custom-select-option w-full text-left px-5 py-2.5 text-sm font-semibold transition-colors bg-emerald-50 text-emerald-600';

                    if (statusInput) {
                        statusInput.value = val;
                        loadLiveResults();
                    }

                    customSelectMenu.classList.remove('dropdown-open');
                    customSelectIcon.classList.remove('dropdown-icon-rotate');
                    customSelectTrigger.classList.remove('border-emerald-400', 'ring-4', 'ring-emerald-500/10');
                });
            });
        }


        /* ====================================================
           MODAL & KONFIRMASI LOGIC
           ==================================================== */
        var confirmModal = document.getElementById('nexusConfirmModal');
        var confirmHeader = document.getElementById('nexusConfirmHeader');
        var confirmIcon = document.getElementById('nexusConfirmIcon');
        var confirmTitle = document.getElementById('nexusConfirmTitle');
        var confirmMessage = document.getElementById('nexusConfirmMessage');
        var confirmCancel = document.getElementById('nexusConfirmCancel');
        var confirmOk = document.getElementById('nexusConfirmOk');

        if (confirmModal && confirmModal.parentElement !== document.body) {
            document.body.appendChild(confirmModal);
        }

        function openConfirm(options) {
            if (!confirmModal) return;
            var tone = options.tone || 'emerald';
            confirmTitle.textContent = options.title || 'Konfirmasi Aksi';
            confirmMessage.textContent = options.message || 'Pastikan data sudah benar sebelum melanjutkan.';

            if (tone === 'danger') {
                confirmHeader.className = 'relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-rose-600 to-rose-800';
                confirmIcon.className = 'fa-solid fa-trash-can text-4xl mb-3 opacity-90';
                confirmOk.className = 'btn-pill h-11 bg-rose-600 text-sm font-bold text-white shadow-md hover:bg-rose-700 transition-colors';
            } else if (tone === 'gold') {
                confirmHeader.className = 'relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-amber-500 to-orange-600';
                confirmIcon.className = 'fa-solid fa-rotate text-4xl mb-3 opacity-90';
                confirmOk.className = 'btn-pill h-11 bg-amber-500 text-sm font-bold text-amber-950 shadow-md hover:bg-amber-400 transition-colors';
            } else {
                confirmHeader.className = 'relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-emerald-600 to-teal-700';
                confirmIcon.className = 'fa-solid fa-circle-exclamation text-4xl mb-3 opacity-90';
                confirmOk.className = 'btn-pill h-11 bg-emerald-600 text-sm font-bold text-white shadow-md hover:bg-emerald-700 transition-colors';
            }
            confirmModal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeConfirm() {
            window.nexusPendingForm = null;
            window.nexusPendingUrl = null;
            window.nexusPendingMethod = null;
            if (confirmModal) confirmModal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-confirm-submit]');
            if (!trigger || trigger.disabled) return;
            event.preventDefault();

            var actionUrl = trigger.getAttribute('data-action-url');
            if (actionUrl) {
                window.nexusPendingUrl = actionUrl;
                window.nexusPendingMethod = trigger.getAttribute('data-action-method') || 'POST';
                window.nexusPendingForm = null;
            } else {
                var formId = trigger.getAttribute('data-confirm-form');
                window.nexusPendingForm = formId ? document.getElementById(formId) : trigger.closest('form');
                window.nexusPendingUrl = null;
            }

            openConfirm({
                title: trigger.getAttribute('data-confirm-title'),
                message: trigger.getAttribute('data-confirm-message'),
                tone: trigger.getAttribute('data-confirm-tone')
            });
        });

        if (confirmOk) {
            confirmOk.addEventListener('click', function () {
                if (window.nexusPendingUrl) {
                    var globalForm = document.getElementById('globalActionForm');
                    var globalMethod = document.getElementById('globalActionMethod');
                    if (globalForm && globalMethod) {
                        globalForm.action = window.nexusPendingUrl;
                        globalMethod.value = window.nexusPendingMethod;
                        closeConfirm();
                        confirmOk.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Proses...';
                        confirmOk.disabled = true;
                        HTMLFormElement.prototype.submit.call(globalForm);
                    }
                } else if (window.nexusPendingForm) {
                    var targetForm = window.nexusPendingForm;
                    closeConfirm();
                    HTMLFormElement.prototype.submit.call(targetForm);
                }
            });
        }


        /* ====================================================
           BULK SELECTION & LIVE SEARCH LOGIC
           ==================================================== */
        function bindBulkSelection() {
            var selectAll = document.getElementById('selectAllBalita');
            var checks = Array.from(document.querySelectorAll('.balita-check'));
            var bulkButton = document.getElementById('bulkDeleteButton');

            function refresh() {
                var checkedCount = checks.filter(function(item) { return item.checked; }).length;
                if (bulkButton) {
                    bulkButton.disabled = checkedCount === 0;
                    if(checkedCount === 0) {
                        bulkButton.classList.add('opacity-50', 'cursor-not-allowed', 'bg-white');
                        bulkButton.classList.remove('hover:bg-rose-500', 'hover:text-white', 'shadow-md', 'bg-rose-50');
                    } else {
                        bulkButton.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-white');
                        bulkButton.classList.add('hover:bg-rose-500', 'hover:text-white', 'shadow-md', 'bg-rose-50');
                    }
                }
                if (selectAll) {
                    selectAll.checked = checks.length > 0 && checkedCount === checks.length;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < checks.length;
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checks.forEach(function(item) { item.checked = selectAll.checked; });
                    refresh();
                });
            }
            checks.forEach(function(item) { item.addEventListener('change', refresh); });
            refresh();
        }

        function buildSearchUrl(pageUrl) {
            var base = (typeof pageUrl === 'string') ? pageUrl : form.action;
            var url = new URL(base, window.location.origin);
            var keyword = searchInput ? searchInput.value.trim() : '';
            var status = statusInput ? statusInput.value : 'semua';

            if(keyword) url.searchParams.set('search', keyword); else url.searchParams.delete('search');
            if(status !== 'semua') {
                url.searchParams.set('status_akun', status);
                url.searchParams.set('status', status); 
            } else {
                url.searchParams.delete('status_akun');
                url.searchParams.delete('status');
            }
            return url;
        }

        async function loadLiveResults(pageUrl) {
            if (!form || !liveRegion) return;
            if (liveController) liveController.abort();
            
            liveController = new AbortController();
            var url = buildSearchUrl(pageUrl);

            if (liveState) liveState.classList.remove('hidden');

            try {
                var response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    signal: liveController.signal
                });
                if (!response.ok) return; 

                var html = await response.text();
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var nextRegion = doc.querySelector('[data-live-region]');
                if (!nextRegion) return;

                liveRegion.innerHTML = nextRegion.innerHTML;
                window.history.replaceState({}, '', url.toString());

                bindBulkSelection();
                bindPaginationLinks();
            } catch (error) {
                if (error.name !== 'AbortError') console.error('Live search error:', error);
            } finally {
                if (liveState) liveState.classList.add('hidden');
            }
        }

        function scheduleSearch() {
            clearTimeout(liveTimer);
            liveTimer = setTimeout(loadLiveResults, 20); 
        }

        function bindPaginationLinks() {
            document.querySelectorAll('#balitaPagination a').forEach(function(link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    var href = this.getAttribute('href'); 
                    if (href) {
                        loadLiveResults(href);
                        var reg = document.getElementById('balitaLiveRegion');
                        if(reg) reg.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                this.value = this.value.replace(/\s+/g, ' ');
                scheduleSearch(); 
            });
        }

        if (form) {
            form.addEventListener('submit', function(e) { 
                e.preventDefault(); 
                loadLiveResults(); 
            });
        }
        
        if (resetButton) {
            resetButton.addEventListener('click', function (event) {
                event.preventDefault();
                if(searchInput) searchInput.value = '';
                
                if(statusInput) statusInput.value = 'semua';
                if(customSelectLabel) customSelectLabel.textContent = 'Semua Status';
                if(customSelectOptions) {
                    customSelectOptions.forEach(btn => {
                        if (btn.getAttribute('data-value') === 'semua') {
                            btn.className = 'custom-select-option w-full text-left px-5 py-2.5 text-sm font-semibold transition-colors bg-emerald-50 text-emerald-600';
                        } else {
                            btn.className = 'custom-select-option w-full text-left px-5 py-2.5 text-sm font-semibold transition-colors text-slate-600 hover:bg-slate-50 hover:text-slate-900';
                        }
                    });
                }
                loadLiveResults(form.action);
            });
        }

        if (confirmCancel) confirmCancel.addEventListener('click', closeConfirm);
        if (confirmModal) confirmModal.addEventListener('click', function(e) { if (e.target === confirmModal) closeConfirm(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && confirmModal && confirmModal.classList.contains('is-open')) closeConfirm(); });

        bindBulkSelection();
        bindPaginationLinks();
    });
</script>
@endpush