@extends('layouts.kader')

@section('title', 'Pengukuran Fisik')
@section('page-name', 'Pengukuran Fisik')
@section('page-title', 'Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use App\Models\Pemeriksaan;

    Carbon::setLocale('id');

    $kategori = $kategori ?? request('kategori', '');
    $search = $search ?? request('search', '');
    $status = $status ?? request('status', '');

    $kategoriAktif = ['balita', 'remaja', 'lansia'];
    $totalData = method_exists($pemeriksaans, 'total') ? $pemeriksaans->total() : $pemeriksaans->count();

    $kategoriOptions = [
        '' => 'Semua',
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    $statusOptions = [
        '' => 'Semua Status',
        'pending' => 'Menunggu Review',
        'ditolak' => 'Perlu Revisi',
        'verified' => 'Tervalidasi',
    ];

    $reviewedStatuses = [
        'verified',
        'terverifikasi',
        'valid',
        'approved',
        'disetujui',
        'selesai',
    ];

    $needFixStatuses = [
        'ditolak',
        'revisi',
        'perlu_revisi',
        'needs_revision',
        'rejected',
        'dikembalikan',
    ];

    $normalizeStatus = function ($value) use ($reviewedStatuses, $needFixStatuses) {
        $value = strtolower((string) ($value ?? 'pending'));

        if (in_array($value, $reviewedStatuses, true)) {
            return 'verified';
        }

        if (in_array($value, $needFixStatuses, true)) {
            return 'ditolak';
        }

        return 'pending';
    };

    $kategoriMeta = function ($value) {
        $value = strtolower((string) $value);

        return match ($value) {
            'balita' => [
                'label' => 'Balita',
                'desc' => 'Anak dan tumbuh kembang',
                'icon' => 'fa-child-reaching',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'soft' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white',
                'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white',
            ],
            'remaja' => [
                'label' => 'Remaja',
                'desc' => 'Sasaran usia remaja',
                'icon' => 'fa-user-graduate',
                'badge' => 'border-violet-200 bg-violet-50 text-violet-800',
                'soft' => 'border-violet-200 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-white',
                'solid' => 'bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white',
            ],
            'lansia' => [
                'label' => 'Lansia',
                'desc' => 'Sasaran usia lanjut',
                'icon' => 'fa-person-cane',
                'badge' => 'border-sky-200 bg-sky-50 text-sky-800',
                'soft' => 'border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white',
                'solid' => 'bg-gradient-to-br from-sky-500 to-cyan-500 text-white',
            ],
            default => [
                'label' => 'Umum',
                'desc' => 'Data sasaran',
                'icon' => 'fa-user',
                'badge' => 'border-slate-200 bg-slate-50 text-slate-700',
                'soft' => 'border-slate-200 bg-gradient-to-br from-slate-50 to-white',
                'solid' => 'bg-gradient-to-br from-slate-700 to-slate-900 text-white',
            ],
        };
    };

    $statusMeta = function ($value) use ($normalizeStatus) {
        $value = $normalizeStatus($value);

        return match ($value) {
            'verified' => [
                'label' => 'Tervalidasi',
                'desc' => 'Sudah dicek Bidan',
                'dot' => 'bg-emerald-500',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'card' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white',
                'icon' => 'fa-circle-check',
                'editable' => false,
            ],
            'ditolak' => [
                'label' => 'Perlu Revisi',
                'desc' => 'Harus diperbaiki',
                'dot' => 'bg-rose-500',
                'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
                'card' => 'border-rose-200 bg-gradient-to-br from-rose-50 via-orange-50 to-white',
                'icon' => 'fa-rotate-left',
                'editable' => true,
            ],
            default => [
                'label' => 'Menunggu',
                'desc' => 'Belum direview Bidan',
                'dot' => 'bg-amber-500',
                'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
                'card' => 'border-amber-200 bg-gradient-to-br from-amber-50 via-yellow-50 to-white',
                'icon' => 'fa-clock',
                'editable' => true,
            ],
        };
    };

    $formatTanggal = function ($item) {
        $tanggal = $item->tanggal_periksa
            ?? optional($item->kunjungan)->tanggal_kunjungan
            ?? $item->created_at
            ?? null;

        return $tanggal ? Carbon::parse($tanggal)->translatedFormat('d M Y') : '-';
    };

    $metric = function ($value, $unit = '') {
        if ($value === null || $value === '') {
            return '-';
        }

        return trim($value . ' ' . $unit);
    };

    $pasienNama = function ($item) {
        $pasien = optional($item->kunjungan)->pasien;

        return $pasien->nama_lengkap
            ?? $pasien->nama
            ?? $item->nama_pasien
            ?? 'Tanpa Nama';
    };

    $pasienNik = function ($item) {
        $pasien = optional($item->kunjungan)->pasien;

        return $pasien->nik
            ?? $item->nik_pasien
            ?? '-';
    };

    $summaryQuery = Pemeriksaan::query()
        ->whereIn('kategori_pasien', $kategoriAktif);

    if ($kategori && in_array($kategori, $kategoriAktif, true)) {
        $summaryQuery->where('kategori_pasien', $kategori);
    }

    $summaryRows = (clone $summaryQuery)->get(['status_verifikasi', 'kategori_pasien']);

    $pendingCount = $summaryRows->filter(fn ($item) => $normalizeStatus($item->status_verifikasi) === 'pending')->count();
    $revisiCount = $summaryRows->filter(fn ($item) => $normalizeStatus($item->status_verifikasi) === 'ditolak')->count();
    $verifiedCount = $summaryRows->filter(fn ($item) => $normalizeStatus($item->status_verifikasi) === 'verified')->count();

    $balitaCount = $summaryRows->where('kategori_pasien', 'balita')->count();
    $remajaCount = $summaryRows->where('kategori_pasien', 'remaja')->count();
    $lansiaCount = $summaryRows->where('kategori_pasien', 'lansia')->count();

    $activeFilterCount = collect([$kategori, $search, $status])->filter(fn ($v) => filled($v))->count();

    $filterCaption = $activeFilterCount > 0
        ? $activeFilterCount . ' filter aktif'
        : 'Semua data';
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
        border-radius: 2rem; 
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active {
        transform: scale(0.95);
    }

    .pc-row {
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.6);
        border-radius: 1.5rem;
    }
    .pc-row:hover {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }

    /* Table Scrollbar */
    .pc-table-wrap {
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(20, 184, 166, 0.3) transparent;
    }
    .pc-table-wrap::-webkit-scrollbar {
        height: 6px;
    }
    .pc-table-wrap::-webkit-scrollbar-track {
        background: transparent;
    }
    .pc-table-wrap::-webkit-scrollbar-thumb {
        background-color: rgba(20, 184, 166, 0.3);
        border-radius: 999px;
    }

    /* Modal Styling */
    .nexus-modal {
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .nexus-modal.is-open {
        opacity: 1;
        visibility: visible;
    }

    /* Smooth Transitions for AJAX */
    .animate-fade-in {
        animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .is-loading {
        opacity: 0.5;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
    
    /* Native Search Input Clear Button Fix */
    input[type="search"]::-webkit-search-cancel-button {
        -webkit-appearance: none;
        height: 14px;
        width: 14px;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%2394a3b8"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>') no-repeat 50% 50%;
        cursor: pointer;
        opacity: 0.7;
    }
    input[type="search"]::-webkit-search-cancel-button:hover {
        opacity: 1;
    }

    /* =========================================
       CUSTOM PAGINATION (LINGKARAN HIJAU)
       ========================================= */
    /* Menyembunyikan tampilan mobile default */
    .pc-pagination > nav > div:first-child {
        display: none !important;
    }
    
    /* Memaksa flexbox sejajar Kiri-Kanan */
    .pc-pagination nav .hidden.sm\:flex-1 {
        display: flex !important;
        flex-direction: column;
        gap: 1.5rem;
        align-items: center;
        width: 100%;
    }
    @media (min-width: 640px) {
        .pc-pagination nav .hidden.sm\:flex-1 {
            flex-direction: row;
            justify-content: space-between;
        }
    }

    /* Styling Teks Keterangan (Kiri) */
    .pc-pagination p.text-sm.text-gray-700 {
        color: #64748b !important;
        font-weight: 700 !important;
        font-size: 0.75rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }
    .pc-pagination p.text-sm.text-gray-700 span.font-medium {
        color: #0f172a !important;
        font-weight: 900 !important;
        font-size: 0.85rem !important;
    }

    /* Modifikasi wrapper tombol agar tidak saling menempel (Kanan) */
    .pc-pagination .relative.z-0.inline-flex {
        box-shadow: none !important;
        gap: 0.5rem !important; /* Jarak antar tombol */
    }

    /* Styling Dasar Tombol (Lingkaran Sempurna) */
    .pc-pagination .relative.inline-flex {
        border-radius: 50% !important; /* Membuat bulat */
        width: 40px !important;
        height: 40px !important;
        padding: 0 !important;
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #334155 !important;
        font-weight: 800 !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
        margin-left: 0 !important; /* Hapus margin negatif bawaan Tailwind */
    }

    /* Hover State untuk tombol yang bisa diklik */
    .pc-pagination a.relative.inline-flex:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #10b981 !important;
    }

    /* Active State (Halaman saat ini - Hijau Solid) */
    .pc-pagination span[aria-current="page"] > span {
        background: #10b981 !important; /* Hijau solid sesuai gambar */
        color: #ffffff !important;
        border-color: #10b981 !important;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2) !important;
    }

    /* Disabled State (Tombol panah mati) */
    .pc-pagination span[aria-disabled="true"] > span {
        background: #f8fafc !important;
        color: #cbd5e1 !important;
        border-color: #f1f5f9 !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
    }

    /* Sembunyikan pemisah "..." agar tidak berupa kotak */
    .pc-pagination span[aria-disabled="true"]:not([aria-label]) > span {
        border: none !important;
        background: transparent !important;
        color: #94a3b8 !important;
    }

    /* Perbaiki posisi icon panah SVG */
    .pc-pagination .relative.inline-flex svg {
        width: 1.25rem !important;
        height: 1.25rem !important;
    }
</style>
@endpush

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 relative pb-32 animate-fade-in">

    {{-- SYSTEM ALERT --}}
    @if(session('success') || session('error') || $errors->any())
        <div class="rounded-[2rem] p-6 shadow-sm border-2 flex items-center gap-4 {{ session('success') ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
            <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center shrink-0 shadow-inner">
                <i class="fa-solid {{ session('success') ? 'fa-circle-check text-emerald-500' : 'fa-triangle-exclamation text-rose-500' }} text-xl"></i>
            </div>
            <div>
                <h3 class="font-black text-lg">{{ session('success') ? 'Berhasil' : 'Peringatan' }}</h3>
                <p class="font-medium text-sm mt-1 opacity-80">
                    {{ session('success') ?? session('error') }}
                    @if($errors->any())
                        Ada input yang belum valid. Perbaiki dulu.
                    @endif
                </p>
            </div>
        </div>
    @endif

    {{-- 1. HEADER / HERO WIDGET --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-6 sm:p-10 shadow-2xl shadow-teal-500/30 flex flex-col xl:flex-row justify-between items-center gap-8 border-[4px] border-white/50">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-50"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
        
        <div class="relative z-10 w-full xl:w-1/2 flex flex-col gap-4 text-center xl:text-left">
            <div class="inline-flex justify-center xl:justify-start items-center gap-2 mb-1">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 animate-ping absolute"></span>
                    <span class="w-2 h-2 rounded-full bg-emerald-200 relative"></span>
                    Measurement Center
                </span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight drop-shadow-md">
                Pengukuran Fisik Posyandu.
            </h1>

            <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-lg mx-auto xl:mx-0 drop-shadow-sm">
                Kader mencatat data dasar seperti berat badan, tinggi badan, lingkar tubuh, tensi, dan catatan awal. Validasi klinis tetap dilakukan Bidan.
            </p>

            <div class="flex flex-wrap justify-center xl:justify-start gap-3 mt-2">
                <a href="{{ route('kader.pemeriksaan.create', ['kategori' => $kategori ?: 'balita']) }}" class="btn-pill bg-white text-teal-600 hover:text-teal-800 hover:bg-teal-50 px-6 py-3 text-sm font-bold shadow-[0_8px_20px_rgba(255,255,255,0.3)] flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Input Pengukuran
                </a>
                <a href="{{ route('kader.dashboard') }}" class="btn-pill bg-black/10 hover:bg-black/20 text-white border border-white/30 px-6 py-3 text-sm font-bold backdrop-blur-md flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
            </div>
        </div>

        <div id="hero-stats" class="relative z-10 w-full xl:w-auto flex flex-col sm:flex-row gap-4 justify-center">
            <div class="widget-card !rounded-[2.5rem] !bg-white/90 p-6 flex flex-col justify-center gap-2 min-w-[220px]">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Total Data Ditemukan</p>
                <div class="flex items-center justify-center gap-4 mt-2">
                    <div class="w-14 h-14 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center text-2xl shadow-inner shrink-0">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <p class="text-4xl font-black text-slate-800">{{ number_format($totalData) }}</p>
                </div>
                <p class="text-xs font-semibold text-teal-600 text-center mt-2 bg-teal-50 py-1 px-3 rounded-full mx-auto"><i class="fa-solid fa-filter mr-1"></i> {{ $filterCaption }}</p>
            </div>
        </div>
    </div>

    {{-- 2. METRIK / STATS GRID --}}
    <section id="stats-row-1" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Status Row --}}
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-500">Menunggu Review</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($pendingCount) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500 shadow-inner group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-clock text-xl"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Perlu Revisi</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($revisiCount) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-500 shadow-inner group-hover:-rotate-6 transition-transform">
                <i class="fa-solid fa-rotate-left text-xl"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-500">Tervalidasi</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($verifiedCount) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500 shadow-inner group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-circle-check text-xl"></i>
            </div>
        </div>
    </section>

    <section id="stats-row-2" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Kategori Row --}}
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-teal-600">Total Balita</p>
                <p class="mt-1 text-2xl font-black text-slate-800">{{ number_format($balitaCount) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-teal-50 text-teal-500 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-child-reaching"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-violet-600">Total Remaja</p>
                <p class="mt-1 text-2xl font-black text-slate-800">{{ number_format($remajaCount) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-violet-50 text-violet-500 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Total Lansia</p>
                <p class="mt-1 text-2xl font-black text-slate-800">{{ number_format($lansiaCount) }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-person-cane"></i>
            </div>
        </div>
    </section>

    {{-- 3. FILTER & TAB WIDGET --}}
    <form id="filterForm" method="GET" action="{{ route('kader.pemeriksaan.index') }}" class="flex flex-col gap-4 relative z-20">
        
        {{-- Kategori Tabs --}}
        <div class="flex flex-wrap gap-2 p-1.5 bg-white/60 backdrop-blur-md border border-white/80 rounded-full w-full sm:w-fit shadow-[0_8px_20px_-4px_rgba(0,0,0,0.05)]">
            @foreach($kategoriOptions as $key => $label)
                <label class="cursor-pointer relative flex-1 sm:flex-none">
                    <input type="radio" name="kategori" value="{{ $key }}" class="peer sr-only" @checked($kategori === $key)>
                    <div class="px-5 py-2.5 rounded-full text-sm font-bold text-slate-500 text-center peer-checked:bg-white peer-checked:text-teal-600 peer-checked:shadow-sm transition-all duration-300">
                        {{ $label }}
                    </div>
                </label>
            @endforeach
        </div>

        {{-- Search & Status --}}
        <div class="widget-card p-4 flex flex-col lg:flex-row gap-4 items-center">
            <div class="w-full lg:flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 z-10"></i>
                <input type="search" name="search" value="{{ $search }}" autocomplete="off" placeholder="Cari nama atau NIK sasaran..." class="w-full btn-pill border border-slate-200 bg-white/80 py-3.5 pl-11 pr-4 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10 shadow-inner">
            </div>

            <select name="status" class="w-full lg:w-48 btn-pill border border-slate-200 bg-white/80 px-4 py-3.5 text-sm font-bold text-slate-700 outline-none transition focus:bg-white focus:border-teal-400 shadow-inner appearance-none cursor-pointer">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- 4. DAFTAR PENGUKURAN --}}
    <section id="data-table-section" class="widget-card p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-lg font-black text-slate-800">Riwayat Pengukuran Fisik</h2>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">Menampilkan {{ number_format($totalData) }} data</p>
            </div>
            <a href="{{ route('kader.pemeriksaan.create', ['kategori' => $kategori ?: 'balita']) }}" class="btn-pill bg-teal-50 border border-teal-100 text-teal-600 hover:bg-teal-500 hover:text-white px-4 py-2 text-xs font-bold uppercase tracking-wider transition-colors shadow-sm flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus"></i> Tambah Data
            </a>
        </div>

        {{-- Desktop Table View --}}
        <div class="pc-table-wrap hidden lg:block">
            <table class="min-w-[1080px] w-full border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-left text-[11px] font-black uppercase tracking-[.16em] text-slate-400">
                        <th class="px-4 py-2">Sasaran</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Kategori</th>
                        <th class="px-4 py-2 text-center">BB</th>
                        <th class="px-4 py-2 text-center">TB</th>
                        <th class="px-4 py-2 text-center" title="Indeks Massa Tubuh">IMT</th>
                        <th class="px-4 py-2 text-center" title="Lingkar Perut">LP</th>
                        <th class="px-4 py-2 text-center">Tensi</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pemeriksaans as $item)
                        @php
                            $nama = $pasienNama($item);
                            $nik = $pasienNik($item);
                            $km = $kategoriMeta($item->kategori_pasien);
                            $sm = $statusMeta($item->status_verifikasi ?? null);
                        @endphp

                        <tr class="pc-row">
                            <td class="rounded-l-3xl px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $km['badge'] }} text-sm font-black shadow-inner">
                                        {{ Str::upper(Str::substr($nama, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="line-clamp-1 text-sm font-black text-slate-800">{{ $nama }}</p>
                                        <p class="text-[11px] font-bold text-slate-500 mt-0.5"><i class="fa-solid fa-id-card opacity-70"></i> {{ $nik }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-700">
                                <i class="fa-solid fa-calendar-day opacity-50 mr-1"></i> {{ $formatTanggal($item) }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="btn-pill inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-black uppercase tracking-wider {{ $km['badge'] }}">
                                    <i class="fa-solid {{ $km['icon'] }}"></i> {{ $km['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-700 text-center">
                                {{ $metric($item->berat_badan, 'kg') }}
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-700 text-center">
                                {{ $metric($item->tinggi_badan, 'cm') }}
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-700 text-center">
                                {{ $metric($item->imt) }}
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-700 text-center">
                                {{ $metric($item->lingkar_perut, 'cm') }}
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-700 text-center">
                                {{ $metric($item->tekanan_darah) }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="btn-pill inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-black uppercase tracking-wider {{ $sm['badge'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $sm['dot'] }}"></span> {{ $sm['label'] }}
                                </span>
                            </td>
                            <td class="rounded-r-3xl px-4 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('kader.pemeriksaan.show', $item->id) }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-500 hover:text-teal-600 hover:border-teal-200 hover:bg-teal-50" title="Detail">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>

                                    @if($sm['editable'])
                                        <a href="{{ route('kader.pemeriksaan.edit', $item->id) }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-500 hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50" title="Edit">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </a>

                                        <form action="{{ route('kader.pemeriksaan.destroy', $item->id) }}" method="POST" data-delete-form class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-pill w-8 h-8 flex items-center justify-center border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition" title="Hapus">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" title="Dikunci">
                                            <i class="fa-solid fa-lock text-xs"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-5 shadow-inner">
                                        <i class="fa-solid fa-folder-open text-3xl"></i>
                                    </div>
                                    <h3 class="font-black text-slate-800 text-xl">Data Belum Ada</h3>
                                    <p class="text-sm font-medium text-slate-500 mt-2 max-w-sm">Input pengukuran fisik pertama agar masuk antrean review Bidan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="space-y-3 lg:hidden">
            @forelse($pemeriksaans as $item)
                @php
                    $nama = $pasienNama($item);
                    $nik = $pasienNik($item);
                    $km = $kategoriMeta($item->kategori_pasien);
                    $sm = $statusMeta($item->status_verifikasi ?? null);
                    
                    // Mengambil catatan bidan langsung dari relasi/kolom
                    $note = $item->catatan_validasi ?? $item->catatan_bidan ?? $item->catatan_review ?? null;
                @endphp

                <article class="pc-row p-4">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $km['badge'] }} text-sm font-black shadow-inner">
                            {{ Str::upper(Str::substr($nama, 0, 1)) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="line-clamp-1 text-base font-black text-slate-800">{{ $nama }}</h3>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $km['badge'] }}">
                                    <i class="fa-solid {{ $km['icon'] }}"></i> {{ $km['label'] }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $sm['badge'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $sm['dot'] }}"></span> {{ $sm['label'] }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs font-semibold text-slate-500 mb-4">
                                <span class="flex items-center gap-1"><i class="fa-solid fa-id-card opacity-70"></i> {{ $nik }}</span>
                                <span class="flex items-center gap-1"><i class="fa-solid fa-calendar-day opacity-70"></i> {{ $formatTanggal($item) }}</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs mb-4">
                                <div class="bg-white/50 border border-slate-100 rounded-xl p-2 text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">BB</p>
                                    <p class="font-black text-slate-700 mt-0.5">{{ $metric($item->berat_badan, 'kg') }}</p>
                                </div>
                                <div class="bg-white/50 border border-slate-100 rounded-xl p-2 text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">TB</p>
                                    <p class="font-black text-slate-700 mt-0.5">{{ $metric($item->tinggi_badan, 'cm') }}</p>
                                </div>
                                <div class="bg-white/50 border border-slate-100 rounded-xl p-2 text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">IMT</p>
                                    <p class="font-black text-slate-700 mt-0.5">{{ $metric($item->imt) }}</p>
                                </div>
                                <div class="bg-white/50 border border-slate-100 rounded-xl p-2 text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">LP</p>
                                    <p class="font-black text-slate-700 mt-0.5">{{ $metric($item->lingkar_perut, 'cm') }}</p>
                                </div>
                            </div>

                            @if($note)
                                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50/50 p-3">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-rose-600 mb-1">Catatan Bidan</p>
                                    <p class="text-xs font-medium text-rose-800 leading-relaxed">{{ $note }}</p>
                                </div>
                            @endif

                            <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
                                <a href="{{ route('kader.pemeriksaan.show', $item->id) }}" class="btn-pill flex-1 py-2 text-center border border-slate-200 bg-white text-xs font-bold text-slate-600 shadow-sm">
                                    <i class="fa-solid fa-eye mr-1"></i> Detail
                                </a>

                                @if($sm['editable'])
                                    <a href="{{ route('kader.pemeriksaan.edit', $item->id) }}" class="btn-pill flex-1 py-2 text-center border border-slate-200 bg-white text-xs font-bold text-slate-600 shadow-sm">
                                        <i class="fa-solid fa-pen mr-1"></i> Edit
                                    </a>
                                    <form action="{{ route('kader.pemeriksaan.destroy', $item->id) }}" method="POST" data-delete-form class="flex-none">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-pill px-4 py-2 border border-rose-200 bg-rose-50 text-xs font-bold text-rose-600 shadow-sm">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="btn-pill flex-1 py-2 text-center border border-emerald-200 bg-emerald-50 text-xs font-bold text-emerald-700 cursor-not-allowed">
                                        <i class="fa-solid fa-lock mr-1"></i> Dikunci
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-[2rem] bg-white/50">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-4 shadow-inner">
                        <i class="fa-solid fa-folder-open text-2xl"></i>
                    </div>
                    <h3 class="font-black text-slate-800 text-lg">Data Belum Ada</h3>
                    <p class="text-sm font-medium text-slate-500 mt-1">Input pengukuran fisik pertama agar masuk antrean review Bidan.</p>
                </div>
            @endforelse
        </div>

        {{-- CUSTOM PC PAGINATION --}}
        @if(method_exists($pemeriksaans, 'links'))
            <div class="mt-8 pt-6 border-t border-slate-100 pc-pagination">
                {{ $pemeriksaans->links() }}
            </div>
        @endif
    </section>

    {{-- 5. MODAL HAPUS DATA --}}
    <div id="pcDeleteModal" class="nexus-modal fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" id="nexusAlertBackdrop"></div>
        <div class="widget-card bg-white w-full max-w-sm p-6 relative z-10 scale-95 transform transition-transform duration-300">
            <div class="w-16 h-16 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-4 text-rose-500 shadow-inner">
                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
            </div>
            <h3 class="text-xl font-black text-slate-800 text-center mb-2">Hapus Data?</h3>
            <p class="text-sm font-medium text-slate-500 text-center mb-6 leading-relaxed">
                Data yang belum direview Bidan dapat dihapus secara permanen. Lanjutkan?
            </p>
            <div class="flex gap-3">
                <button type="button" id="pcCancelDelete" class="w-full flex-1 btn-pill border border-slate-200 bg-white text-slate-700 px-4 py-3 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                    Batal
                </button>
                <button type="button" id="pcConfirmDelete" class="w-full flex-1 btn-pill bg-gradient-to-r from-rose-500 to-rose-600 text-white px-4 py-3 text-sm font-bold shadow-md hover:from-rose-600 hover:to-rose-700 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    let debounceTimer;

    // AJAX / Fetch Logic untuk Anti-Reload SPA
    async function fetchDataByUrl(url) {
        const dataContainer = document.getElementById('data-table-section');
        if (dataContainer) dataContainer.classList.add('is-loading');
        
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            
            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Array ID element yang akan diperbarui datanya
                const targetIds = ['hero-stats', 'stats-row-1', 'stats-row-2', 'data-table-section'];
                
                targetIds.forEach(id => {
                    const currentEl = document.getElementById(id);
                    const newEl = doc.getElementById(id);
                    if (currentEl && newEl) {
                        currentEl.innerHTML = newEl.innerHTML;
                    }
                });
                
                // Update URL di browser history secara halus (tanpa reload)
                window.history.pushState({}, '', url);
            } else {
                // Fallback jika API bermasalah
                window.location.href = url;
            }
        } catch (error) {
            window.location.href = url; // Fallback jika gagal terhubung
        } finally {
            if (dataContainer) dataContainer.classList.remove('is-loading');
        }
    }

    function submitFilter() {
        const form = document.getElementById('filterForm');
        if (!form) return;
        
        const url = new URL(form.action);
        const formData = new FormData(form);
        url.search = new URLSearchParams(formData).toString();
        
        fetchDataByUrl(url);
    }

    // EVENT DELEGATION: Listener di document agar tetap jalan meski elemen diganti AJAX
    
    // 1. Ketik pada input search (Debounce 500ms)
    document.addEventListener('input', function(e) {
        if (e.target.matches('input[name="search"]')) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(submitFilter, 500);
        }
    });

    // 2. Ubah Select Status atau Tab Kategori (Langsung ganti)
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name="status"], input[name="kategori"]')) {
            submitFilter();
        }
    });

    // 3. Mencegah user tekan "Enter" yang me-reload penuh
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'filterForm') {
            e.preventDefault();
            submitFilter();
        }
    });

    // 4. Mencegat klik pada pagination laravel agar menggunakan AJAX
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('.pagination a, nav[role="navigation"] a');
        if (pageLink && pageLink.href) {
            e.preventDefault();
            fetchDataByUrl(new URL(pageLink.href));
        }
    });

    // --- LOGIC MODAL HAPUS DATA ---
    let targetDeleteForm = null;
    const modal = document.querySelector('#pcDeleteModal');

    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    const cancelBtn = document.querySelector('#pcCancelDelete');
    const confirmBtn = document.querySelector('#pcConfirmDelete');
    const backdrop = document.querySelector('#nexusAlertBackdrop');

    function lockBody() { document.body.style.overflow = 'hidden'; }
    function unlockBody() { document.body.style.overflow = ''; }

    function openModal(form) {
        targetDeleteForm = form;
        if (!modal) return;
        
        modal.classList.add('is-open');
        modal.querySelector('.widget-card').classList.remove('scale-95');
        modal.querySelector('.widget-card').classList.add('scale-100');
        lockBody();
    }

    function closeModal() {
        targetDeleteForm = null;
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.querySelector('.widget-card').classList.remove('scale-100');
        modal.querySelector('.widget-card').classList.add('scale-95');
        unlockBody();
    }

    // Tangkap klik tombol hapus (menggunakan event delegation)
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-delete-form]');
        if (!form) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        openModal(form);
    }, true);

    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!targetDeleteForm) {
                closeModal();
                return;
            }

            confirmBtn.disabled = true;
            confirmBtn.classList.add('opacity-70', 'cursor-not-allowed');
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';

            HTMLFormElement.prototype.submit.call(targetDeleteForm);
        });
    }
});
</script>
@endpush