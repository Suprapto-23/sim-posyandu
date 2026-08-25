@extends('layouts.kader')

@section('title', 'Data Lansia')
@section('page-name', 'Data Lansia')
@section('page-title', 'Data Lansia')

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

    $indexRoute = $routeHas('kader.data.lansia.index')
        ? route('kader.data.lansia.index')
        : url('/kader/data/lansia');

    $createRoute = $routeHas('kader.data.lansia.create')
        ? route('kader.data.lansia.create')
        : url('/kader/data/lansia/create');

    $bulkDeleteRoute = $routeHas('kader.data.lansia.bulk-delete')
        ? route('kader.data.lansia.bulk-delete')
        : url('/kader/data/lansia/bulk-delete');

    $templateRoute = $routeHas('kader.import.template')
        ? route('kader.import.template', ['type' => 'lansia'])
        : null;

    $importRoute = $routeHas('kader.import.index')
        ? route('kader.import.index', ['type' => 'lansia'])
        : null;

    $statusOptions = [
        'semua' => 'Semua Status',
        'terhubung' => 'Terhubung',
        'belum' => 'Belum Terhubung',
    ];

    $totalData = method_exists($items, 'total') ? $items->total() : $items->count();

    $statTotal = $statTotal ?? $totalData;
    $statLaki = $statLaki ?? 0;
    $statPerempuan = $statPerempuan ?? 0;
    $statTerhubung = $statTerhubung ?? 0;

    $rangeText = method_exists($items, 'firstItem')
        ? 'Menampilkan ' . (($items->firstItem() ?? 0)) . '-' . (($items->lastItem() ?? 0)) . ' dari ' . $items->total() . ' sasaran'
        : 'Menampilkan ' . $items->count() . ' sasaran';

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

    $getAge = function ($lansia) {
        if (isset($lansia->usia_label) && filled($lansia->usia_label)) return $lansia->usia_label;
        if (! $lansia->tanggal_lahir) return '-';

        $diff = Carbon::parse($lansia->tanggal_lahir)->diff(now());
        return $diff->y > 0 ? $diff->y . ' tahun' : $diff->m . ' bulan';
    };

    $initial = function ($name) {
        return Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'L';
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

    $isConnected = function ($lansia) {
        return filled($lansia->user_id ?? null);
    };

    $accountLabel = function ($lansia) use ($isConnected) {
        return $isConnected($lansia) ? 'Terhubung' : 'Belum Terhubung';
    };

    $accountClass = function ($lansia) use ($isConnected) {
        return $isConnected($lansia)
            ? 'text-emerald-700 border-emerald-200 bg-emerald-50'
            : 'text-amber-700 border-amber-200 bg-amber-50';
    };

    $activeFilterCount = collect([$search, $statusAkun !== 'semua' ? $statusAkun : null])->filter(fn($v) => filled($v))->count();
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

    .animate-pop-in {
        animation: popIn .4s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.98) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .widget-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2rem; 
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
        scrollbar-color: rgba(20, 184, 166, 0.3) transparent;
        padding-right: 8px;
        scroll-behavior: smooth !important;
        -webkit-overflow-scrolling: touch;
        transform: translateZ(0); 
        will-change: scroll-position;
        contain: paint;
    }
    .pc-scroll-container::-webkit-scrollbar { width: 6px; }
    .pc-scroll-container::-webkit-scrollbar-track { background: transparent; }
    .pc-scroll-container::-webkit-scrollbar-thumb { background-color: rgba(20, 184, 166, 0.3); border-radius: 999px; }

    /* Squircle Row Styles */
    .pc-row {
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.6);
        border-radius: 1.75rem; 
    }
    .pc-row:hover {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 8px 25px rgba(20, 184, 166, 0.08); 
        transform: translateY(-2px);
    }

    /* Modal Full Screen */
    .pc-modal-backdrop {
        position: fixed; inset: 0; z-index: 999999; display: none; align-items: center; justify-content: center;
        background: rgba(15, 23, 42, .5); backdrop-filter: blur(8px); padding: 1rem; width: 100vw; height: 100vh;
    }
    .pc-modal-backdrop.is-open { display: flex; }
    .pc-modal-card {
        width: 100%; max-width: 420px; background: white; border-radius: 2.5rem; padding: 0; overflow: hidden;
        transform: scale(0.95) translateY(15px); opacity: 0; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }
</style>
@endpush

<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 relative">

    {{-- SYSTEM ALERT: CLEAN & RAMAH --}}
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
        
        <div id="clean-friendly-alert" class="w-full bg-white/90 backdrop-blur-md border border-slate-100 rounded-[2rem] p-4 sm:px-5 sm:py-4 mb-6 flex items-center justify-between gap-4 shadow-lg transition-all duration-300 z-50 relative">
            <div class="flex items-center gap-4 flex-1">
                <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center {{ $flashConfig['iconBg'] }} border border-white {{ $flashConfig['iconColor'] }} shadow-inner">
                    <i class="fa-solid {{ $flashConfig['icon'] }} text-xl"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-black text-slate-800 tracking-tight leading-tight mb-0.5">{{ $flashConfig['title'] }}</h3>
                    <p class="text-[13px] font-medium text-slate-500 leading-snug">{{ $flashMessage }}</p>
                </div>
            </div>
            
            <button type="button" onclick="closeCleanAlert()" class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" title="Tutup Notifikasi">
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
    <section class="bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 rounded-[2.5rem] sm:rounded-[3rem] p-6 sm:p-10 mb-6 sm:mb-8 relative overflow-hidden shadow-2xl shadow-teal-500/30 border-[3px] sm:border-[4px] border-white/50">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-50"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-6 sm:gap-8 lg:items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex justify-center lg:justify-start items-center gap-2 text-white/90 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-3 sm:mb-4">
                    <span class="btn-pill bg-white/20 border border-white/30 px-3 sm:px-4 py-1 sm:py-1.5 shadow-inner flex items-center gap-2 backdrop-blur-md">
                        <span class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-emerald-300 animate-pulse"></span>
                        Master Data Sasaran
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight drop-shadow-md mb-2 sm:mb-3">
                    Data Lansia
                </h1>

                <p class="text-teal-50 text-[11px] sm:text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0 drop-shadow-sm">
                    Kelola data Lansia untuk pemantauan kesehatan berkala, cek tensi, asam urat, dan pelaporan Posbindu. Lakukan sinkronisasi akun agar keluarga dapat memantau kesehatan lansia secara mandiri.[cite: 12]
                </p>
            </div>

            <div class="grid grid-cols-2 sm:flex sm:flex-row gap-2.5 sm:gap-3 w-full lg:w-auto shrink-0 justify-center">
                @if($templateRoute)
                    <a href="{{ $templateRoute }}" download class="btn-pill bg-white/20 hover:bg-white/30 text-white border border-white/30 px-4 sm:px-6 py-2.5 sm:py-3.5 text-xs sm:text-sm font-bold backdrop-blur-md flex items-center justify-center gap-2 transition-all shadow-sm">
                        <i class="fa-solid fa-download"></i> Template
                    </a>
                @endif
                
                @if($importRoute)
                    <a href="{{ $importRoute }}" class="btn-pill bg-black/10 hover:bg-black/20 text-white border border-white/30 px-4 sm:px-6 py-2.5 sm:py-3.5 text-xs sm:text-sm font-bold backdrop-blur-md flex items-center justify-center gap-2 transition-all shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Import
                    </a>
                @endif
                
                <a href="{{ $createRoute }}" class="col-span-2 sm:col-auto btn-pill bg-white hover:bg-teal-50 text-teal-600 px-4 sm:px-6 py-2.5 sm:py-3.5 text-xs sm:text-sm font-black shadow-[0_8px_20px_rgba(255,255,255,0.3)] flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus"></i> Tambah Data
                </a>
            </div>
        </div>
    </section>

    {{-- STATISTIK GRID --}}
    <section class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4 mb-6 sm:mb-8">
        <div class="widget-card p-4 sm:p-6 flex justify-between items-center group hover:-translate-y-1 transition-transform duration-300 min-w-0">
            <div class="min-w-0">
                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 truncate">Total Sasaran</p>
                <p class="mt-0.5 sm:mt-1 text-xl sm:text-3xl font-black text-slate-800">{{ number_format($statTotal) }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center text-lg sm:text-2xl shadow-inner group-hover:rotate-6 transition-transform shrink-0">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="widget-card p-4 sm:p-6 flex justify-between items-center group hover:-translate-y-1 transition-transform duration-300 min-w-0">
            <div class="min-w-0">
                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-sky-600 truncate">Laki-laki</p>
                <p class="mt-0.5 sm:mt-1 text-xl sm:text-3xl font-black text-slate-800">{{ number_format($statLaki) }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-sky-50 border border-sky-100 text-sky-500 flex items-center justify-center text-lg sm:text-2xl shadow-inner group-hover:-rotate-6 transition-transform shrink-0">
                <i class="fa-solid fa-mars"></i>
            </div>
        </div>
        <div class="widget-card p-4 sm:p-6 flex justify-between items-center group hover:-translate-y-1 transition-transform duration-300 min-w-0">
            <div class="min-w-0">
                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-rose-600 truncate">Perempuan</p>
                <p class="mt-0.5 sm:mt-1 text-xl sm:text-3xl font-black text-slate-800">{{ number_format($statPerempuan) }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-rose-50 border border-rose-100 text-rose-500 flex items-center justify-center text-lg sm:text-2xl shadow-inner group-hover:rotate-6 transition-transform shrink-0">
                <i class="fa-solid fa-venus"></i>
            </div>
        </div>
        <div class="widget-card p-4 sm:p-6 flex justify-between items-center group hover:-translate-y-1 transition-transform duration-300 min-w-0">
            <div class="min-w-0">
                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-emerald-600 truncate">Terhubung</p>
                <p class="mt-0.5 sm:mt-1 text-xl sm:text-3xl font-black text-slate-800">{{ number_format($statTerhubung) }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-500 flex items-center justify-center text-lg sm:text-2xl shadow-inner group-hover:-rotate-6 transition-transform shrink-0">
                <i class="fa-solid fa-link"></i>
            </div>
        </div>
    </section>

    {{-- FILTER WIDGET --}}
    <form id="liveSearchForm" action="{{ $indexRoute }}" method="GET" class="widget-card p-3 sm:p-5 flex flex-col lg:flex-row gap-3 sm:gap-4 items-center z-20 relative mb-6 sm:mb-8">
        <input type="hidden" name="jenis_kelamin" value="{{ $jenisKelamin }}">

        <div class="w-full lg:flex-1 relative flex items-center">
            <i class="fa-solid fa-magnifying-glass absolute left-4 text-slate-400 z-10 pointer-events-none"></i>
            <input type="text" id="liveSearchInput" name="search" value="{{ $search }}" autocomplete="off" placeholder="Ketik nama lansia atau NIK..." class="w-full btn-pill border border-slate-200 bg-white/80 py-3 sm:py-3.5 pl-11 pr-12 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10 shadow-inner">
            <div id="liveSearchState" class="absolute right-4 hidden pointer-events-none">
                <i class="fa-solid fa-circle-notch fa-spin text-teal-500 text-sm"></i>
            </div>
        </div>

        <div class="relative w-full lg:w-48" id="customSelectContainer">
            <select id="status_akun" name="status_akun" class="hidden">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected($statusAkun === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="button" id="customSelectTrigger" class="w-full btn-pill border border-slate-200 bg-white/90 px-4 sm:px-5 py-3 sm:py-3.5 text-sm font-bold text-slate-700 outline-none transition hover:border-teal-300 focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10 shadow-inner flex justify-between items-center cursor-pointer">
                <span id="customSelectLabel" class="truncate mr-2">{{ $statusOptions[$statusAkun] ?? 'Semua Status' }}</span>
                <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300" id="customSelectIcon"></i>
            </button>

            <ul id="customSelectMenu" class="absolute z-[100] w-full mt-2 bg-white border border-slate-100 rounded-[1.5rem] shadow-xl opacity-0 invisible transform -translate-y-2 transition-all duration-200 overflow-hidden py-1.5">
                @foreach($statusOptions as $key => $label)
                    <li>
                        <button type="button" class="custom-select-option w-full text-left px-5 py-2.5 text-sm font-semibold transition-colors {{ $statusAkun === $key ? 'bg-teal-50 text-teal-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}" data-value="{{ $key }}">
                            {{ $label }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="flex gap-2 w-full lg:w-auto">
            <button type="submit" class="flex-1 lg:flex-none btn-pill bg-slate-800 hover:bg-slate-700 px-5 sm:px-6 py-3 sm:py-3.5 text-sm font-bold text-white shadow-md transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if($activeFilterCount > 0)
                <a href="{{ $indexRoute }}" id="resetSearchButton" class="btn-pill border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 w-12 sm:w-[52px] flex items-center justify-center text-slate-400 shadow-sm transition" title="Reset filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- MAIN DATA WIDGET --}}
    <section id="lansiaLiveRegion" data-live-region class="w-full widget-card p-4 sm:p-6">
        
        <div class="pb-4 mb-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-base sm:text-xl font-black text-slate-900">Daftar Sasaran Lansia</h2>
                <p id="listRangeText" class="mt-0.5 sm:mt-1 text-[10px] sm:text-xs font-semibold text-slate-500">{{ $rangeText }}</p>
            </div>

            <div class="flex flex-row gap-2 sm:gap-3">
                <label class="btn-pill flex-1 sm:flex-none border border-slate-200 bg-white/50 px-4 py-2 sm:py-2.5 text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-slate-600 shadow-sm flex items-center justify-center gap-2 cursor-pointer hover:bg-slate-50 transition-colors">
                    <input type="checkbox" id="selectAllLansia" class="h-4 w-4 rounded border-slate-300 text-teal-500 focus:ring-teal-400 cursor-pointer transition">
                    Pilih Semua
                </label>

                @if($routeHas('kader.data.lansia.bulk-delete'))
                    <button type="button" id="bulkDeleteButton" disabled 
                            data-confirm-submit data-confirm-form="bulkDeleteForm"
                            data-confirm-title="Hapus Data Terpilih?" 
                            data-confirm-message="Lansia yang dicentang akan dihapus secara permanen dari sistem. Pastikan pilihan sudah benar." 
                            data-confirm-tone="danger" 
                            class="btn-pill flex-1 sm:flex-none bg-white border border-rose-200 text-rose-600 px-4 py-2 sm:py-2.5 text-[10px] sm:text-[11px] font-black uppercase tracking-wider shadow-sm transition opacity-50 cursor-not-allowed hover:bg-rose-500 hover:text-white">
                        <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                    </button>
                @endif
            </div>
        </div>

        <div class="pc-scroll-container max-h-[60vh] lg:max-h-[600px] py-2 space-y-3">
            @forelse($items as $item)
                @php
                    $name = $item->nama_lengkap ?? '-';
                    $nik = $item->nik ?? '-';
                    $connected = $isConnected($item);
                @endphp

                <article class="pc-row p-4 sm:p-5 relative overflow-hidden">
                    <div class="flex flex-col xl:flex-row gap-4 xl:items-center">
                        
                        {{-- 1. Area Profil & Checkbox --}}
                        <div class="flex items-start gap-3 sm:gap-4 xl:w-[35%] shrink-0">
                            <div class="mt-1 xl:mt-0">
                                <input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulkDeleteForm" class="lansia-check h-4 w-4 sm:h-5 sm:w-5 rounded border-slate-300 text-teal-600 focus:ring-teal-500 cursor-pointer">
                            </div>
                            <div class="flex h-11 w-11 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-full bg-slate-50 border border-slate-200 text-base sm:text-lg font-black text-slate-700 shadow-sm">
                                {{ $initial($name) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-sm sm:text-base font-black text-slate-800 leading-tight" title="{{ $name }}">{{ $name }}</h3>
                                <p class="mt-0.5 sm:mt-1 truncate text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wide"><i class="fa-solid fa-id-card opacity-70 mr-1"></i> {{ $nik }}</p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="rounded-full border px-2.5 py-0.5 text-[8px] sm:text-[9px] font-black uppercase tracking-wider shadow-sm {{ $genderClass($item->jenis_kelamin) }}">{{ $genderLabel($item->jenis_kelamin) }}</span>
                                    <span class="rounded-full border px-2.5 py-0.5 text-[8px] sm:text-[9px] font-black uppercase tracking-wider shadow-sm {{ $accountClass($item) }}">{{ $accountLabel($item) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Area Informasi Tambahan --}}
                        <div class="grid grid-cols-2 gap-3 xl:flex xl:w-[45%] shrink-0 xl:gap-4 border-t border-slate-100 pt-3 xl:border-0 xl:pt-0">
                            <div class="bg-slate-50/50 p-2.5 sm:p-3 rounded-2xl border border-slate-100 xl:flex-1">
                                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status & Usia</p>
                                <p class="text-[11px] sm:text-xs font-bold text-slate-700 leading-snug truncate" title="{{ $item->pekerjaan ?? '-' }}">{{ $item->pekerjaan ?? 'Lansia' }}</p>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 mt-0.5 truncate"><i class="fa-solid fa-clock opacity-50 mr-1"></i> {{ $getAge($item) }}</p>
                            </div>
                            <div class="bg-slate-50/50 p-2.5 sm:p-3 rounded-2xl border border-slate-100 xl:flex-1">
                                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pendamping / Keluarga</p>
                                <p class="text-[11px] sm:text-xs font-bold text-slate-700 truncate" title="{{ $item->nama_keluarga ?? '-' }}">Kel: {{ $item->nama_keluarga ?? '-' }}</p>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 mt-0.5 truncate" title="{{ $item->alamat }}"><i class="fa-solid fa-location-dot opacity-50 mr-1"></i> {{ $item->alamat ?: '-' }}</p>
                            </div>
                        </div>

                        {{-- 3. Area Tombol Aksi --}}
                        <div class="flex flex-wrap xl:flex-nowrap gap-2 pt-3 border-t border-slate-100 xl:border-0 xl:pt-0 xl:w-[20%] xl:justify-end shrink-0">
                            @if($routeHas('kader.data.lansia.show'))
                                <a href="{{ route('kader.data.lansia.show', $item->id) }}" class="btn-pill flex-1 xl:flex-none w-auto xl:w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-[11px] sm:text-xs font-bold text-slate-600 shadow-sm hover:border-teal-300 hover:text-teal-600 hover:bg-teal-50 transition-colors" title="Lihat Detail">
                                    <i class="fa-solid fa-eye"></i> <span class="xl:hidden ml-1.5 uppercase tracking-wider">Detail</span>
                                </a>
                            @endif

                            @if($routeHas('kader.data.lansia.edit'))
                                <a href="{{ route('kader.data.lansia.edit', $item->id) }}" class="btn-pill flex-1 xl:flex-none w-auto xl:w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-[11px] sm:text-xs font-bold text-slate-600 shadow-sm hover:border-amber-300 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Edit Data">
                                    <i class="fa-solid fa-pen"></i> <span class="xl:hidden ml-1.5 uppercase tracking-wider">Edit</span>
                                </a>
                            @endif

                            @if(! $connected)
                                @php $syncUrl = $routeHas('kader.data.lansia.sync') ? route('kader.data.lansia.sync', $item->id) : url('/kader/data/lansia/' . $item->id . '/sync'); @endphp
                                <button type="button" 
                                        class="btn-pill flex-1 xl:flex-none w-auto xl:w-10 h-10 flex items-center justify-center border border-amber-200 bg-amber-50 text-[11px] sm:text-xs font-bold text-amber-600 shadow-sm hover:bg-amber-500 hover:text-white transition-colors"
                                        data-confirm-submit 
                                        data-action-url="{{ $syncUrl }}"
                                        data-action-method="POST"
                                        data-confirm-title="Sinkronkan Akun?" 
                                        data-confirm-message="Sistem akan mencoba menghubungkan data Lansia ini dengan akun keluarga berdasarkan NIK yang sama." 
                                        data-confirm-tone="gold" title="Sinkronkan Akun">
                                    <i class="fa-solid fa-rotate"></i> <span class="xl:hidden ml-1.5 uppercase tracking-wider">Sync</span>
                                </button>
                            @endif

                            @if($routeHas('kader.data.lansia.destroy'))
                                <button type="button" 
                                        class="btn-pill flex-1 xl:flex-none w-auto xl:w-10 h-10 flex items-center justify-center border border-rose-200 bg-rose-50 text-[11px] sm:text-xs font-bold text-rose-500 shadow-sm hover:bg-rose-500 hover:text-white transition-colors"
                                        data-confirm-submit 
                                        data-action-url="{{ route('kader.data.lansia.destroy', $item->id) }}"
                                        data-action-method="DELETE"
                                        data-confirm-title="Hapus Data Lansia?" 
                                        data-confirm-message="Data Lansia ini akan dihapus permanen. Tindakan ini tidak bisa dibatalkan." 
                                        data-confirm-tone="danger" title="Hapus Data">
                                    <i class="fa-solid fa-trash-can"></i> <span class="xl:hidden ml-1.5 uppercase tracking-wider">Hapus</span>
                                </button>
                            @endif
                        </div>
                        
                    </div>
                </article>
            @empty
                <div class="rounded-[2rem] border-2 border-dashed border-slate-200 bg-white/50 p-8 sm:p-12 text-center transition-all">
                    <div class="mx-auto flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-slate-100 text-2xl sm:text-3xl font-black text-slate-400 shadow-inner mb-4">
                        <i class="fa-solid fa-person-cane"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black text-slate-800">Data Lansia Kosong</h3>
                    <p class="mx-auto mt-1 max-w-sm text-xs sm:text-sm font-semibold leading-relaxed text-slate-500">Tidak ada sasaran yang cocok dengan pencarian, atau database masih kosong.</p>
                </div>
            @endforelse
        </div>

        {{-- CUSTOM PAGINATION (LINGKARAN HIJAU TEAL) --}}
        @if(method_exists($items, 'hasPages') && $items->hasPages())
            <div id="lansiaPagination" class="pt-5 sm:pt-6 mt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Halaman <span class="text-slate-800">{{ $items->currentPage() }}</span> dari <span class="text-slate-800">{{ $items->lastPage() }}</span>
                </p>
                
                <div class="flex items-center gap-1.5 sm:gap-2">
                    @if ($items->onFirstPage())
                        <button type="button" disabled class="inline-flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-slate-50 border border-slate-100 text-slate-300 cursor-not-allowed">
                            <i class="fas fa-chevron-left text-[10px] sm:text-xs"></i>
                        </button>
                    @else
                        <a href="{{ $items->previousPageUrl() }}" class="inline-flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 shadow-sm hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all">
                            <i class="fas fa-chevron-left text-[10px] sm:text-xs"></i>
                        </a>
                    @endif

                    @php
                        $start = max(1, $items->currentPage() - 2);
                        $end = min($items->lastPage(), $items->currentPage() + 2);
                    @endphp
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $items->currentPage())
                            <span class="inline-flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-teal-500 text-white font-black text-xs sm:text-sm shadow-md shadow-teal-500/30 pointer-events-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $items->url($page) }}" class="inline-flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 font-bold text-xs sm:text-sm shadow-sm hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    @if ($items->hasMorePages())
                        <a href="{{ $items->nextPageUrl() }}" class="inline-flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 shadow-sm hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all">
                            <i class="fas fa-chevron-right text-[10px] sm:text-xs"></i>
                        </a>
                    @else
                        <button type="button" disabled class="inline-flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-slate-50 border border-slate-100 text-slate-300 cursor-not-allowed">
                            <i class="fas fa-chevron-right text-[10px] sm:text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </section>

    @if($routeHas('kader.data.lansia.bulk-delete'))
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
        <div id="nexusConfirmHeader" class="relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-teal-500 to-emerald-600">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="absolute -bottom-16 left-8 h-28 w-44 rounded-[40px] bg-white/10 pointer-events-none"></div>

            <div class="relative z-10">
                <i id="nexusConfirmIcon" class="fa-solid fa-circle-exclamation text-4xl mb-3 opacity-90 drop-shadow-md"></i>
                <h3 id="nexusConfirmTitle" class="text-lg sm:text-xl font-black tracking-tight mb-1">Konfirmasi Aksi</h3>
                <p id="nexusConfirmMessage" class="text-[11px] sm:text-xs font-semibold leading-relaxed opacity-90 px-4">
                    Pastikan data sudah benar sebelum melanjutkan.
                </p>
            </div>
        </div>

        <div class="p-6 bg-white text-center">
            <div class="flex gap-3">
                <button type="button" id="nexusConfirmCancel" class="w-full flex-1 btn-pill border border-slate-200 bg-white text-slate-700 px-4 py-3 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">Batal</button>
                <button type="button" id="nexusConfirmOk" class="w-full flex-1 btn-pill bg-teal-500 text-white px-4 py-3 text-sm font-bold shadow-md hover:bg-teal-600 transition-all">Lanjutkan</button>
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
                    customSelectTrigger.classList.add('border-teal-400', 'ring-4', 'ring-teal-500/10');
                } else {
                    customSelectTrigger.classList.remove('border-teal-400', 'ring-4', 'ring-teal-500/10');
                }
            });

            document.addEventListener('click', function(e) {
                if (!customSelectTrigger.contains(e.target) && !customSelectMenu.contains(e.target)) {
                    customSelectMenu.classList.remove('dropdown-open');
                    customSelectIcon.classList.remove('dropdown-icon-rotate');
                    customSelectTrigger.classList.remove('border-teal-400', 'ring-4', 'ring-teal-500/10');
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
                    this.className = 'custom-select-option w-full text-left px-5 py-2.5 text-sm font-semibold transition-colors bg-teal-50 text-teal-600';

                    if (statusInput) {
                        statusInput.value = val;
                        loadLiveResults();
                    }

                    customSelectMenu.classList.remove('dropdown-open');
                    customSelectIcon.classList.remove('dropdown-icon-rotate');
                    customSelectTrigger.classList.remove('border-teal-400', 'ring-4', 'ring-teal-500/10');
                });
            });
        }

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
            var tone = options.tone || 'teal';
            confirmTitle.textContent = options.title || 'Konfirmasi Aksi';
            confirmMessage.textContent = options.message || 'Pastikan data sudah benar sebelum melanjutkan.';

            if (tone === 'danger') {
                confirmHeader.className = 'relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-rose-500 to-rose-700';
                confirmIcon.className = 'fa-solid fa-trash-can text-4xl mb-3 opacity-90 drop-shadow-md';
                confirmOk.className = 'w-full flex-1 btn-pill bg-rose-500 text-sm font-bold text-white shadow-md hover:bg-rose-600 transition-colors py-3';
            } else if (tone === 'gold') {
                confirmHeader.className = 'relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-amber-500 to-orange-500';
                confirmIcon.className = 'fa-solid fa-rotate text-4xl mb-3 opacity-90 drop-shadow-md';
                confirmOk.className = 'w-full flex-1 btn-pill bg-amber-500 text-sm font-bold text-white shadow-md hover:bg-amber-600 transition-colors py-3';
            } else {
                confirmHeader.className = 'relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-teal-500 to-emerald-500';
                confirmIcon.className = 'fa-solid fa-circle-exclamation text-4xl mb-3 opacity-90 drop-shadow-md';
                confirmOk.className = 'w-full flex-1 btn-pill bg-teal-500 text-sm font-bold text-white shadow-md hover:bg-teal-600 transition-colors py-3';
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

        function bindBulkSelection() {
            var selectAll = document.getElementById('selectAllLansia');
            var checks = Array.from(document.querySelectorAll('.lansia-check'));
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
            document.querySelectorAll('#lansiaPagination a').forEach(function(link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    var href = this.getAttribute('href'); 
                    if (href) {
                        loadLiveResults(href);
                        var reg = document.getElementById('lansiaLiveRegion');
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
                            btn.className = 'custom-select-option w-full text-left px-5 py-2.5 text-sm font-semibold transition-colors bg-teal-50 text-teal-600';
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