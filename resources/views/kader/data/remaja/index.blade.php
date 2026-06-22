@extends('layouts.kader')

@section('title', 'Data Remaja')
@section('page-name', 'Data Remaja')
@section('page-title', 'Data Remaja')

@section('content')
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $search = trim((string) ($search ?? request('search', '')));
    $statusAkun = $statusAkun ?? request('status_akun', 'semua');
    $jenisKelamin = $jenisKelamin ?? request('jenis_kelamin', 'semua');

    $indexRoute = $routeHas('kader.data.remaja.index')
        ? route('kader.data.remaja.index')
        : url('/kader/data/remaja');

    $createRoute = $routeHas('kader.data.remaja.create')
        ? route('kader.data.remaja.create')
        : url('/kader/data/remaja/create');

    $bulkDeleteRoute = $routeHas('kader.data.remaja.bulk-delete')
        ? route('kader.data.remaja.bulk-delete')
        : url('/kader/data/remaja/bulk-delete');

    $templateRoute = $routeHas('kader.import.template')
        ? route('kader.import.template', ['type' => 'remaja'])
        : null;

    $importRoute = $routeHas('kader.import.index')
        ? route('kader.import.index', ['type' => 'remaja'])
        : null;

    $statusOptions = [
        'semua' => 'Semua Status',
        'terhubung' => 'Terhubung',
        'belum' => 'Belum Terhubung',
    ];

    $genderOptions = [
        'semua' => 'Semua Gender',
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    $totalData = method_exists($items, 'total') ? $items->total() : $items->count();

    $statTotal = $statTotal ?? $totalData;
    $statLaki = $statLaki ?? 0;
    $statPerempuan = $statPerempuan ?? 0;
    $statTerhubung = $statTerhubung ?? 0;
    $statBelumTerhubung = $statBelumTerhubung ?? 0;

    $rangeText = method_exists($items, 'firstItem')
        ? 'Menampilkan ' . (($items->firstItem() ?? 0)) . '-' . (($items->lastItem() ?? 0)) . ' dari ' . $items->total() . ' sasaran'
        : 'Menampilkan ' . $items->count() . ' sasaran';

    $formatDate = function ($value) {
        if (! $value) return '-';
        return Carbon::parse($value)->translatedFormat('d M Y');
    };

    $getAge = function ($remaja) {
        if (isset($remaja->usia_label) && filled($remaja->usia_label)) return $remaja->usia_label;
        if (! $remaja->tanggal_lahir) return '-';

        $diff = Carbon::parse($remaja->tanggal_lahir)->diff(now());
        return $diff->y > 0 ? $diff->y . ' thn ' . $diff->m . ' bln' : $diff->m . ' bln';
    };

    $initial = function ($name) {
        return Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'R';
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
            'L' => 'text-sky-600 border-sky-200 bg-sky-50/50',
            'P' => 'text-rose-600 border-rose-200 bg-rose-50/50',
            default => 'text-slate-600 border-slate-200 bg-slate-50',
        };
    };

    $isConnected = function ($remaja) {
        return filled($remaja->user_id ?? null);
    };

    $accountLabel = function ($remaja) use ($isConnected) {
        return $isConnected($remaja) ? 'Terhubung' : 'Belum Terhubung';
    };

    $accountClass = function ($remaja) use ($isConnected) {
        return $isConnected($remaja)
            ? 'text-emerald-700 border-emerald-200 bg-emerald-50'
            : 'text-amber-700 border-amber-200 bg-amber-50';
    };

    // Helper Ekstraksi Data Pemeriksaan Remaja
    $lastCheckDate = function ($remaja) use ($formatDate) {
        $last = $remaja->pemeriksaan_terakhir ?? null;
        if (! $last) return 'Belum Periksa';
        return $formatDate($last->tanggal_periksa ?? $last->created_at ?? null);
    };

    $cleanNum = fn($val) => blank($val) ? '-' : rtrim(rtrim((string) $val, '0'), '.');
    
    // Penamaan kolom di-handle agar aman dari null error
    $lastWeight = fn($r) => $cleanNum($r->pemeriksaan_terakhir->berat_badan ?? null);
    $lastHeight = fn($r) => $cleanNum($r->pemeriksaan_terakhir->tinggi_badan ?? null);
    $lastLp = fn($r) => $cleanNum($r->pemeriksaan_terakhir->lingkar_perut ?? null);
    $lastTensi = fn($r) => ($r->pemeriksaan_terakhir->tekanan_darah_sistolik ?? $r->pemeriksaan_terakhir->tekanan_darah ?? '-');
    $lastHb = fn($r) => $cleanNum($r->pemeriksaan_terakhir->hemoglobin ?? null);

    $activeFilterCount = collect([$search, $statusAkun !== 'semua' ? $statusAkun : null, $jenisKelamin !== 'semua' ? $jenisKelamin : null])->filter(fn($v) => filled($v))->count();
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
        background: rgba(255, 255, 255, 0.86);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2rem; 
        box-shadow: 0 15px 35px -10px rgba(15, 23, 42, 0.05);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
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

    /* Grid Layout - Persis seperti di gambar */
    .data-row {
        display: grid;
        grid-template-columns: 24px minmax(220px, 1.2fr) minmax(130px, 0.7fr) minmax(180px, 1fr) minmax(210px, 1.1fr) 130px;
        gap: 16px;
        align-items: start;
    }

    /* CSS Line Clamp (Untuk Alamat) */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Action Box */
    .row-action {
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none;
    }
    .row-action:hover { transform: translateY(-1px); }
    
    .action-detail { background: #ecfdf5; border-color: #a7f3d0; color: #059669; }
    .action-edit { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
    .action-sync { background: #fffbeb; border-color: #fcd34d; color: #d97706; }
    .action-delete { background: #fff1f2; border-color: #fecdd3; color: #e11d48; }

    /* Modal Full Screen */
    .pc-modal-backdrop {
        position: fixed; inset: 0; z-index: 999999; display: none; align-items: center; justify-content: center;
        background: rgba(15, 23, 42, .65); backdrop-filter: blur(12px); padding: 1rem; width: 100vw; height: 100vh;
    }
    .pc-modal-backdrop.is-open { display: flex; }
    .pc-modal-card {
        width: 100%; max-width: 420px; background: white; border-radius: 2rem; padding: 0; overflow: hidden;
        transform: scale(0.95) translateY(15px); opacity: 0; transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
    }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }

    @media (max-width: 1280px) {
        .data-row { grid-template-columns: 24px minmax(0, 1fr); align-items: start; }
        .col-hidden-mobile { display: none; }
        .col-stack-mobile { display: flex; flex-direction: column; gap: 10px; }
    }
</style>
@endpush

<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6">

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
                    Data Remaja
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Kelola data Remaja untuk absensi, pengukuran fisik, HB, dan pelaporan Posyandu. Lakukan sinkronisasi akun agar riwayat kesehatan terhubung ke aplikasi warga.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto shrink-0 justify-center">
                @if($templateRoute)
                    <a href="{{ $templateRoute }}" class="btn-pill bg-white/20 hover:bg-white/30 text-white border border-white/30 px-6 py-3.5 text-sm font-bold backdrop-blur-md flex items-center justify-center gap-2 transition-all shadow-sm">
                        <i class="fa-solid fa-file-excel"></i> Template
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

    {{-- SYSTEM ALERT --}}
    @if(session('success') || session('error') || session('warning'))
        @php
            $flashType = session('error') ? 'error' : (session('warning') ? 'warning' : 'success');
            $flashClass = match ($flashType) {
                'error' => 'border-rose-200 bg-rose-50 text-rose-800',
                'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
                default => 'border-emerald-200 bg-emerald-50 text-emerald-800',
            };
            $flashIcon = match ($flashType) {
                'error' => 'fa-triangle-exclamation text-rose-500',
                'warning' => 'fa-circle-exclamation text-amber-500',
                default => 'fa-circle-check text-emerald-500',
            };
        @endphp
        <div class="rounded-[2rem] p-6 shadow-sm border-2 flex items-center gap-4 {{ $flashClass }} mb-8">
            <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center shrink-0 shadow-inner">
                <i class="fa-solid {{ $flashIcon }} text-xl"></i>
            </div>
            <div>
                <h3 class="font-black text-lg">{{ ucfirst($flashType) }}</h3>
                <p class="font-medium text-sm mt-1 opacity-80">{{ session($flashType) }}</p>
            </div>
        </div>
    @endif

    {{-- STATISTIK GRID --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Sasaran</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statTotal) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center text-2xl shadow-inner group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Laki-laki</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statLaki) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-sky-50 border border-sky-100 text-sky-500 flex items-center justify-center text-2xl shadow-inner group-hover:-rotate-6 transition-transform">
                <i class="fa-solid fa-mars"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Perempuan</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statPerempuan) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-100 text-rose-500 flex items-center justify-center text-2xl shadow-inner group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-venus"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Terhubung Akun</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($statTerhubung) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-500 flex items-center justify-center text-2xl shadow-inner group-hover:-rotate-6 transition-transform">
                <i class="fa-solid fa-link"></i>
            </div>
        </div>
    </section>

    {{-- FILTER WIDGET --}}
    <form id="liveSearchForm" action="{{ $indexRoute }}" method="GET" class="widget-card p-4 sm:p-6 flex flex-col xl:flex-row gap-4 items-center z-20 relative mb-8">
        <div class="w-full xl:flex-1 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 z-10"></i>
            <input type="text" id="liveSearchInput" name="search" value="{{ $search }}" autocomplete="off" placeholder="Ketik nama, NIK, atau sekolah..." class="w-full btn-pill border border-slate-200 bg-white/80 py-3.5 pl-11 pr-12 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10 shadow-inner">
            <div id="liveSearchState" class="absolute right-4 top-1/2 -translate-y-1/2 hidden">
                <i class="fa-solid fa-circle-notch fa-spin text-emerald-500"></i>
            </div>
        </div>

        <select id="jenis_kelamin" name="jenis_kelamin" class="w-full xl:w-40 btn-pill border border-slate-200 bg-white/80 px-4 py-3.5 text-sm font-bold text-slate-700 outline-none transition focus:bg-white focus:border-emerald-400 shadow-inner appearance-none cursor-pointer">
            @foreach($genderOptions as $key => $label)
                <option value="{{ $key }}" @selected($jenisKelamin === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <select id="status_akun" name="status_akun" class="w-full xl:w-44 btn-pill border border-slate-200 bg-white/80 px-4 py-3.5 text-sm font-bold text-slate-700 outline-none transition focus:bg-white focus:border-emerald-400 shadow-inner appearance-none cursor-pointer">
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected($statusAkun === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="flex gap-2 w-full xl:w-auto">
            <button type="submit" class="flex-1 xl:flex-none btn-pill bg-slate-800 hover:bg-slate-700 px-6 py-3.5 text-sm font-bold text-white shadow-md transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if($activeFilterCount > 0)
                <a href="{{ $indexRoute }}" id="resetSearchButton" class="btn-pill border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 w-12 flex items-center justify-center text-slate-400 shadow-sm transition" title="Reset filter">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- MAIN DATA WIDGET (Live Region) --}}
    <section id="remajaLiveRegion" data-live-region class="widget-card overflow-hidden">
        
        {{-- Header Data --}}
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900">Daftar Sasaran Remaja</h2>
                <p id="listRangeText" class="mt-1 text-xs font-semibold text-slate-500">{{ $rangeText }}</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <label class="btn-pill border border-slate-200 bg-white px-4 py-2 text-[11px] font-black uppercase tracking-wider text-slate-600 shadow-sm flex items-center justify-center gap-2 cursor-pointer hover:bg-slate-50">
                    <input type="checkbox" id="selectAllRemaja" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400 cursor-pointer">
                    Pilih Semua
                </label>

                @if($routeHas('kader.data.remaja.bulk-delete'))
                    <button type="button" id="bulkDeleteButton" disabled 
                            data-confirm-submit data-confirm-form="bulkDeleteForm"
                            data-confirm-title="Hapus Data Terpilih?" 
                            data-confirm-message="Remaja yang dicentang akan dihapus secara permanen dari sistem. Pastikan pilihan sudah benar." 
                            data-confirm-tone="danger" 
                            class="btn-pill bg-rose-50 border border-rose-200 text-rose-600 px-4 py-2 text-[11px] font-black uppercase tracking-wider shadow-sm transition opacity-50 cursor-not-allowed hover:bg-rose-500 hover:text-white">
                        <i class="fa-solid fa-trash-can mr-1"></i> Hapus Terpilih
                    </button>
                @endif
            </div>
        </div>

        {{-- CONTAINER DENGAN SCROLLBAR DAN BATAS TINGGI --}}
        <div class="pc-scroll-container max-h-[600px] p-3 sm:p-5 space-y-3 bg-white/40">
            @forelse($items as $item)
                @php
                    $name = $item->nama_lengkap ?? '-';
                    $nik = $item->nik ?? '-';
                    $connected = $isConnected($item);
                @endphp

                <article class="bg-white border border-emerald-100/70 rounded-[1.5rem] p-4 shadow-sm hover:shadow-md hover:border-emerald-300 transition duration-300">
                    <div class="data-row col-stack-mobile">
                        
                        {{-- 1. Checkbox --}}
                        <div class="pt-2 xl:pt-0 flex items-center justify-center relative z-10">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulkDeleteForm" class="remaja-check h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        </div>

                        {{-- 2. Identitas (Sesuai Layout Gambar) --}}
                        <div class="flex items-start gap-4 min-w-0 relative z-10">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[14px] bg-slate-900 text-xl font-black text-emerald-400 shadow-md mt-0.5">
                                {{ $initial($name) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-base font-black text-slate-900 leading-tight" title="{{ $name }}">{{ $name }}</h3>
                                <p class="mt-1 truncate text-xs font-bold text-slate-500">NIK {{ $nik }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="rounded border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $genderClass($item->jenis_kelamin) }}">{{ $genderLabel($item->jenis_kelamin) }}</span>
                                    <span class="rounded border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $accountClass($item) }}">{{ $accountLabel($item) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Pendidikan & Usia --}}
                        <div class="col-hidden-mobile min-w-0 relative z-10 mt-1">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Pendidikan & Usia</p>
                            <p class="text-xs font-black text-slate-800 leading-snug">{{ $item->sekolah ?: '-' }}</p>
                            <p class="text-[11px] font-bold text-slate-500 mt-1">{{ $getAge($item) }}</p>
                        </div>

                        {{-- 4. Orang Tua & Domisili (ALAMAT MENGGUNAKAN LINE-CLAMP, BUKAN TRUNCATE) --}}
                        <div class="col-hidden-mobile min-w-0 relative z-10 mt-1">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Orang Tua/Wali</p>
                            <p class="text-xs font-black text-slate-800" title="{{ $item->nama_ortu }}">Wali: {{ $item->nama_ortu ?: '-' }}</p>
                            <p class="text-[11px] font-bold text-slate-500 mt-1 line-clamp-2 leading-snug" title="{{ $item->alamat }}">{{ $item->alamat ?: '-' }}</p>
                        </div>

                        {{-- 5. Cek Terakhir Box (Sesuai Gambar) --}}
                        <div class="col-hidden-mobile min-w-0 relative z-10 bg-slate-50 border border-slate-100 rounded-[14px] p-3">
                            <div class="flex justify-between items-center mb-2">
                                <p class="text-[10px] font-black uppercase tracking-wider text-emerald-600">Cek Terakhir</p>
                                <p class="text-[9px] font-bold text-slate-400">{{ $lastCheckDate($item) }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-[11px]">
                                <p class="font-bold text-slate-600">BB: <span class="text-slate-900">{{ $lastWeight($item) }}</span></p>
                                <p class="font-bold text-slate-600">TB: <span class="text-slate-900">{{ $lastHeight($item) }}</span></p>
                                <p class="font-bold text-slate-600">LP: <span class="text-slate-900">{{ $lastLp($item) }}</span></p>
                                <p class="font-bold text-slate-600">Tensi: <span class="text-slate-900">{{ $lastTensi($item) }}</span></p>
                                <p class="font-bold text-rose-500 col-span-2 mt-1">HB: <span class="text-rose-600">{{ $lastHb($item) }}</span></p>
                            </div>
                        </div>

                        {{-- 6. Action Box (TIDAK ADA <FORM> SAMA SEKALI, MURNI EVENT DELEGATION ANTI-BUG) --}}
                        <div class="grid grid-cols-2 gap-1.5 w-full relative z-20 mt-1 xl:mt-0">
                            @if($routeHas('kader.data.remaja.show'))
                                <a href="{{ route('kader.data.remaja.show', $item->id) }}" class="col-span-1 row-action action-detail" title="Lihat Detail"><i class="fa-solid fa-eye"></i></a>
                            @endif

                            @if($routeHas('kader.data.remaja.edit'))
                                <a href="{{ route('kader.data.remaja.edit', $item->id) }}" class="col-span-1 row-action action-edit" title="Edit Data"><i class="fa-solid fa-pen"></i></a>
                            @endif

                            @if(! $connected && $routeHas('kader.data.remaja.sync'))
                                <button type="button" 
                                        class="col-span-2 row-action action-sync w-full" 
                                        data-confirm-submit
                                        data-action-url="{{ route('kader.data.remaja.sync', $item->id) }}"
                                        data-action-method="POST"
                                        data-confirm-title="Sinkronkan Akun?" 
                                        data-confirm-message="Sistem akan mencoba menghubungkan data Remaja ini dengan akun warga berdasarkan NIK yang sama." 
                                        data-confirm-tone="gold">
                                    <i class="fa-solid fa-rotate mr-1"></i> Sinkron
                                </button>
                            @endif

                            @if($routeHas('kader.data.remaja.destroy'))
                                <button type="button" 
                                        class="col-span-2 row-action action-delete w-full" 
                                        data-confirm-submit
                                        data-action-url="{{ route('kader.data.remaja.destroy', $item->id) }}"
                                        data-action-method="DELETE"
                                        data-confirm-title="Hapus Data Remaja?" 
                                        data-confirm-message="Data Remaja ini akan dihapus permanen. Tindakan ini tidak bisa dibatalkan." 
                                        data-confirm-tone="danger">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[2rem] border-2 border-dashed border-emerald-200 bg-emerald-50/50 p-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-3xl font-black text-emerald-300 shadow-sm mb-4">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800">Data Remaja Kosong</h3>
                    <p class="mx-auto mt-2 max-w-sm text-sm font-semibold leading-6 text-slate-500">Tidak ada sasaran yang cocok dengan pencarian, atau database masih kosong.</p>
                    <a href="{{ $createRoute }}" class="btn-pill mt-6 inline-flex bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-md hover:bg-emerald-700 transition-colors">
                        Tambah Remaja Baru
                    </a>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION CUSTOM TERBARU --}}
        @if(method_exists($items, 'hasPages') && $items->hasPages())
            <div id="remajaPagination" class="border-t border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Halaman <span class="text-slate-900">{{ $items->currentPage() }}</span> dari <span class="text-slate-900">{{ $items->lastPage() }}</span>
                </p>
                
                <div class="flex items-center gap-2">
                    {{-- Previous Page --}}
                    @if ($items->onFirstPage())
                        <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                    @else
                        <a href="{{ $items->previousPageUrl() }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>
                    @endif

                    {{-- Page Numbers (Max 5 pages visible around current) --}}
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

                    {{-- Next Page --}}
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

    {{-- BULK DELETE FORM (Dipakai untuk tombol atas "Hapus Terpilih") --}}
    @if($routeHas('kader.data.remaja.bulk-delete'))
        <form id="bulkDeleteForm" action="{{ $bulkDeleteRoute }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    {{-- GLOBAL ACTION FORM (Dipakai secara otomatis oleh JS untuk semua aksi di baris) --}}
    <form id="globalActionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="globalActionMethod" value="POST">
    </form>
</div>
@endsection

@push('modals')
{{-- MODAL KONFIRMASI (Z-Index Absolut Menutupi Layar Penuh) --}}
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
    // DEKLARASI GLOBAL SANGAT AMAN
    window.nexusPendingForm = null;
    window.nexusPendingUrl = null;
    window.nexusPendingMethod = null;

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('liveSearchForm');
        var searchInput = document.getElementById('liveSearchInput');
        var genderInput = document.getElementById('jenis_kelamin');
        var statusInput = document.getElementById('status_akun');
        var resetButton = document.getElementById('resetSearchButton');
        var liveRegion = document.querySelector('[data-live-region]');
        var liveState = document.getElementById('liveSearchState');
        
        var confirmModal = document.getElementById('nexusConfirmModal');
        var confirmHeader = document.getElementById('nexusConfirmHeader');
        var confirmIcon = document.getElementById('nexusConfirmIcon');
        var confirmTitle = document.getElementById('nexusConfirmTitle');
        var confirmMessage = document.getElementById('nexusConfirmMessage');
        var confirmCancel = document.getElementById('nexusConfirmCancel');
        var confirmOk = document.getElementById('nexusConfirmOk');

        var liveTimer = null;
        var liveController = null;

        // Pindahkan Modal ke Body agar z-index max bekerja dan menutupi Sidebar
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

        // ==========================================
        // SISTEM EVENT DELEGATION ANTI-BUG
        // ==========================================
        document.addEventListener('click', function (event) {
            // Deteksi jika yang diklik adalah tombol/icon ber-atribut data-confirm-submit
            var trigger = event.target.closest('[data-confirm-submit]');
            
            if (!trigger || trigger.disabled) return;

            // HENTIKAN aksi bawaan HTML
            event.preventDefault();

            // 1. Cek apakah ini tombol Dinamis (Delete/Sync di dalam baris)
            var actionUrl = trigger.getAttribute('data-action-url');
            if (actionUrl) {
                window.nexusPendingUrl = actionUrl;
                window.nexusPendingMethod = trigger.getAttribute('data-action-method') || 'POST';
                window.nexusPendingForm = null;
            } 
            // 2. Jika bukan, berarti tombol Statis (Bulk Delete)
            else {
                var formId = trigger.getAttribute('data-confirm-form');
                window.nexusPendingForm = formId ? document.getElementById(formId) : trigger.closest('form');
                window.nexusPendingUrl = null;
                
                if (!window.nexusPendingForm) {
                    alert('Sistem Error: Form tidak ditemukan!');
                    return;
                }
            }

            openConfirm({
                title: trigger.getAttribute('data-confirm-title'),
                message: trigger.getAttribute('data-confirm-message'),
                tone: trigger.getAttribute('data-confirm-tone')
            });
        });

        // Eksekusi Submit
        if (confirmOk) {
            confirmOk.addEventListener('click', function () {
                // A. Submit Form Dinamis (Delete/Sync Baris)
                if (window.nexusPendingUrl) {
                    var globalForm = document.getElementById('globalActionForm');
                    var globalMethod = document.getElementById('globalActionMethod');
                    
                    if (globalForm && globalMethod) {
                        globalForm.action = window.nexusPendingUrl;
                        globalMethod.value = window.nexusPendingMethod;
                        
                        closeConfirm();
                        
                        // Efek Loading Mencegah Double Click
                        confirmOk.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Proses...';
                        confirmOk.disabled = true;
                        
                        HTMLFormElement.prototype.submit.call(globalForm);
                    }
                } 
                // B. Submit Form Statis (Bulk Delete)
                else if (window.nexusPendingForm) {
                    var targetForm = window.nexusPendingForm;
                    closeConfirm();
                    HTMLFormElement.prototype.submit.call(targetForm);
                }
            });
        }

        // ==========================================
        // SISTEM LIVE SEARCH & FILTER
        // ==========================================
        function bindBulkSelection() {
            var selectAll = document.getElementById('selectAllRemaja');
            var checks = Array.from(document.querySelectorAll('.remaja-check'));
            var bulkButton = document.getElementById('bulkDeleteButton');

            function refresh() {
                var checkedCount = checks.filter(function(item) { return item.checked; }).length;
                if (bulkButton) {
                    bulkButton.disabled = checkedCount === 0;
                    if(checkedCount === 0) {
                        bulkButton.classList.add('opacity-50', 'cursor-not-allowed');
                        bulkButton.classList.remove('hover:bg-rose-500', 'hover:text-white', 'shadow-md');
                    } else {
                        bulkButton.classList.remove('opacity-50', 'cursor-not-allowed');
                        bulkButton.classList.add('hover:bg-rose-500', 'hover:text-white', 'shadow-md');
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
            var base = pageUrl || form.action;
            var url = new URL(base, window.location.origin);
            var keyword = searchInput.value.trim();
            var gender = genderInput.value || 'semua';
            var status = statusInput.value || 'semua';

            if(keyword) url.searchParams.set('search', keyword); else url.searchParams.delete('search');
            if(gender !== 'semua') url.searchParams.set('jenis_kelamin', gender); else url.searchParams.delete('jenis_kelamin');
            if(status !== 'semua') url.searchParams.set('status_akun', status); else url.searchParams.delete('status_akun');
            return url;
        }

        async function loadLiveResults(pageUrl) {
            if (!form || !liveRegion) return;
            if (liveController) liveController.abort();
            
            liveController = new AbortController();
            var url = buildSearchUrl(pageUrl);

            liveRegion.classList.add('live-loading');
            if (liveState) liveState.classList.remove('hidden');

            try {
                var response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    signal: liveController.signal
                });

                var html = await response.text();
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var nextRegion = doc.querySelector('[data-live-region]');

                if (!nextRegion) {
                    window.location.href = url.toString();
                    return;
                }

                liveRegion.innerHTML = nextRegion.innerHTML;
                window.history.replaceState({}, '', url.toString());

                bindBulkSelection();
                bindPaginationLinks();
            } catch (error) {
                if (error.name !== 'AbortError') console.error('Live search gagal:', error);
            } finally {
                liveRegion.classList.remove('live-loading');
                if (liveState) liveState.classList.add('hidden');
            }
        }

        function scheduleSearch() {
            clearTimeout(liveTimer);
            liveTimer = setTimeout(loadLiveResults, 260);
        }

        function bindPaginationLinks() {
            document.querySelectorAll('#remajaPagination a').forEach(function(link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    loadLiveResults(link.href);
                    var reg = document.getElementById('remajaLiveRegion');
                    if(reg) reg.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
        }

        // Search & Filter Events
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                this.value = this.value.replace(/\s+/g, ' ');
                scheduleSearch();
            });
        }
        if (genderInput) genderInput.addEventListener('change', loadLiveResults);
        if (statusInput) statusInput.addEventListener('change', loadLiveResults);
        if (form) form.addEventListener('submit', function(e) { e.preventDefault(); loadLiveResults(); });
        
        if (resetButton) {
            resetButton.addEventListener('click', function (event) {
                event.preventDefault();
                searchInput.value = '';
                genderInput.value = 'semua';
                statusInput.value = 'semua';
                loadLiveResults(form.action);
            });
        }

        // Modal Events
        if (confirmCancel) confirmCancel.addEventListener('click', closeConfirm);
        if (confirmModal) confirmModal.addEventListener('click', function(e) { if (e.target === confirmModal) closeConfirm(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && confirmModal && confirmModal.classList.contains('is-open')) closeConfirm(); });

        bindBulkSelection();
        bindPaginationLinks();
    });
</script>
@endpush