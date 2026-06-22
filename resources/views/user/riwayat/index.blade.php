@extends('layouts.user')

@section('title', 'Riwayat Terpadu')
@section('page_title', 'Riwayat Terpadu')

@php
    use Illuminate\Support\Facades\Route;

    $filters = $filters ?? [
        'search' => '',
        'kategori' => 'semua',
        'periode' => 'semua',
    ];

    $counts = $counts ?? [
        'target' => 0,
        'total' => 0,
        'balita' => 0,
        'remaja' => 0,
        'lansia' => 0,
    ];

    $riwayatCards = collect($riwayatCards ?? []);

    $indexRoute = Route::has('user.riwayat.index')
        ? route('user.riwayat.index')
        : url('/user/riwayat');

    // TEMA DINAMIS UNTUK HERO BANNER & TOMBOL
    $heroTheme = 'emerald'; // Default
    if ($counts['target'] > 0) {
        if ($counts['balita'] > 0 && $counts['remaja'] == 0 && $counts['lansia'] == 0) $heroTheme = 'rose';
        elseif ($counts['remaja'] > 0 && $counts['balita'] == 0 && $counts['lansia'] == 0) $heroTheme = 'sky';
        elseif ($counts['lansia'] > 0 && $counts['balita'] == 0 && $counts['remaja'] == 0) $heroTheme = 'amber';
    }

    $heroClasses = match($heroTheme) {
        'rose' => 'from-rose-500 via-rose-400 to-pink-500 shadow-[0_20px_40px_-12px_rgba(244,63,94,.35)]',
        'sky' => 'from-sky-500 via-sky-400 to-blue-500 shadow-[0_20px_40px_-12px_rgba(14,165,233,.35)]',
        'amber' => 'from-amber-500 via-amber-400 to-yellow-500 shadow-[0_20px_40px_-12px_rgba(245,158,11,.35)]',
        default => 'from-emerald-500 via-teal-500 to-teal-600 shadow-[0_20px_40px_-12px_rgba(20,184,166,.35)]',
    };

    // TEMA DINAMIS UNTUK TOMBOL FILTER AGAR MATCH DENGAN BANNER
    $btnClasses = match($heroTheme) {
        'rose' => 'bg-rose-500 hover:bg-rose-600 focus:ring-rose-500/20',
        'sky' => 'bg-sky-500 hover:bg-sky-600 focus:ring-sky-500/20',
        'amber' => 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-500/20',
        default => 'bg-emerald-500 hover:bg-emerald-600 focus:ring-emerald-500/20',
    };

    $iconFocusClass = match($heroTheme) {
        'rose' => 'text-rose-500',
        'sky' => 'text-sky-500',
        'amber' => 'text-amber-500',
        default => 'text-emerald-500',
    };

    // MAP TEMA LIST VIEW
    $toneMap = [
        'rose' => [
            'icon_bg' => 'bg-rose-50', 'icon_text' => 'text-rose-500',
            'badge' => 'bg-rose-50 text-rose-600 border-rose-200',
            'button' => 'bg-white text-rose-600 border-rose-200 hover:bg-rose-500 hover:text-white hover:border-rose-500 shadow-sm',
            'hover_row' => 'hover:bg-rose-50/40'
        ],
        'sky' => [
            'icon_bg' => 'bg-sky-50', 'icon_text' => 'text-sky-500',
            'badge' => 'bg-sky-50 text-sky-600 border-sky-200',
            'button' => 'bg-white text-sky-600 border-sky-200 hover:bg-sky-500 hover:text-white hover:border-sky-500 shadow-sm',
            'hover_row' => 'hover:bg-sky-50/40'
        ],
        'amber' => [
            'icon_bg' => 'bg-amber-50', 'icon_text' => 'text-amber-500',
            'badge' => 'bg-amber-50 text-amber-600 border-amber-200',
            'button' => 'bg-white text-amber-600 border-amber-200 hover:bg-amber-500 hover:text-white hover:border-amber-500 shadow-sm',
            'hover_row' => 'hover:bg-amber-50/40'
        ],
    ];

    $kategoriOptions = [
        'semua' => 'Semua Sasaran',
        'balita' => 'Hanya Balita',
        'remaja' => 'Hanya Remaja',
        'lansia' => 'Hanya Lansia',
    ];

    $periodeOptions = [
        'semua' => 'Semua Waktu',
        'bulan_ini' => 'Bulan Ini',
        '3_bulan' => '3 Bulan Terakhir',
        '6_bulan' => '6 Bulan Terakhir',
        'tahun_ini' => 'Tahun Ini',
    ];
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

    .hero-grid {
        background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px);
        background-size: 24px 24px;
    }

    .btn-pill {
        border-radius: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.97); }

    .input-soft {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        outline: none;
        transition: all .2s ease;
    }
    .input-soft:focus {
        background: #ffffff;
        border-color: currentColor;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- 1. HERO SECTION (DYNAMIC THEME) --}}
    <section class="bg-gradient-to-br {{ $heroClasses }} rounded-[2.5rem] p-8 md:p-10 relative overflow-hidden border border-white/20 text-center md:text-left flex flex-col md:flex-row items-center justify-between gap-6 transition-all duration-500">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[70px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex-1">
            <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                <span class="bg-white/20 backdrop-blur-md border border-white/20 px-3 py-1.5 rounded-full shadow-sm">Kesehatan Terpadu</span>
                <i class="fas fa-chevron-right text-[8px] opacity-70"></i>
                <span>Log Pemeriksaan</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white font-poppins tracking-tight">
                Riwayat Rekam Medis
            </h1>

            <p class="text-white/80 text-sm font-medium mt-3 leading-relaxed max-w-xl mx-auto md:mx-0">
                Pusat arsip klinis seluruh anggota keluarga (Balita, Remaja, Lansia) yang telah divalidasi oleh Bidan.
            </p>
        </div>

        <div class="relative z-10 shrink-0">
            <div class="bg-white/20 border border-white/30 backdrop-blur-md rounded-[1.5rem] px-6 py-5 text-center shadow-inner min-w-[140px]">
                <p class="text-[10px] text-white/90 font-black uppercase tracking-widest mb-1">Total Riwayat</p>
                <p class="text-4xl font-black text-white leading-none">{{ $counts['total'] }}</p>
            </div>
        </div>
    </section>

    {{-- 2. ADVANCED FILTER WIDGET (DENGAN WARNA TOMBOL DINAMIS) --}}
    <form action="{{ $indexRoute }}" method="GET" class="bg-white/70 backdrop-blur-xl border border-slate-200 shadow-sm rounded-[2rem] p-5 sm:p-6 flex flex-col lg:flex-row gap-4">
        
        <div class="flex-1">
            <label for="search" class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2 pl-1">
                Pencarian Spesifik
            </label>
            <div class="relative">
                {{-- Kelas iconFocusClass ditambahkan ke input agar saat diklik garisnya menyesuaikan warna tema --}}
                <input type="text" id="search" name="search" value="{{ $filters['search'] }}" autocomplete="off" placeholder="Ketik nama, NIK, atau keluhan..." class="input-soft w-full pl-10 h-11 focus:{{ $iconFocusClass }} focus:ring-4 focus:ring-current/10">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-48">
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2 pl-1">Sasaran</label>
            <select name="kategori" class="input-soft w-full h-11 cursor-pointer focus:{{ $iconFocusClass }} focus:ring-4 focus:ring-current/10">
                @foreach($kategoriOptions as $key => $label)
                    <option value="{{ $key }}" @selected($filters['kategori'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full lg:w-48">
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2 pl-1">Periode</label>
            <select name="periode" class="input-soft w-full h-11 cursor-pointer focus:{{ $iconFocusClass }} focus:ring-4 focus:ring-current/10">
                @foreach($periodeOptions as $key => $label)
                    <option value="{{ $key }}" @selected($filters['periode'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2 items-end mt-4 lg:mt-0">
            {{-- Tombol Filter Sekarang Mengikuti Warna Banner --}}
            <button type="submit" class="h-11 rounded-[14px] {{ $btnClasses }} px-6 text-[11px] font-black uppercase tracking-widest text-white shadow-sm transition-all flex items-center justify-center">
                Filter
            </button>
            <a href="{{ $indexRoute }}" class="h-11 w-11 rounded-[14px] border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:bg-slate-50 hover:text-rose-500 transition-all">
                <i class="fas fa-undo"></i>
            </a>
        </div>
    </form>

    {{-- 3. CONTAINER MASTER (ADMIN STYLE LIST) --}}
    <section class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
        
        <div class="bg-slate-50/70 px-6 sm:px-8 py-6 border-b border-slate-100 flex items-center justify-between">
            <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-list-check opacity-50 text-lg"></i>
                Daftar Riwayat Klinis
            </h5>
            <span class="rounded-lg bg-white border border-slate-200 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-500 shadow-sm">
                Menampilkan {{ count($riwayatCards) }} Data
            </span>
        </div>

        <div class="flex flex-col divide-y divide-slate-100">
            @forelse($riwayatCards as $card)
                @php $tone = $toneMap[$card['tone']] ?? $toneMap['sky']; @endphp

                <div class="flex flex-col p-6 sm:px-8 gap-4 transition-colors duration-200 {{ $tone['hover_row'] }}">
                    
                    <div class="flex flex-col lg:flex-row lg:items-center gap-5">
                        <div class="flex items-center gap-4 w-full lg:w-4/12 shrink-0">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-2xl shadow-sm border border-slate-100 {{ $tone['icon_bg'] }} {{ $tone['icon_text'] }}">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="inline-block px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border {{ $tone['badge'] }}">
                                        {{ $card['kategori_label'] }}
                                    </span>
                                    <span class="inline-block px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border border-emerald-200 bg-emerald-50 text-emerald-700">
                                        <i class="fas fa-check mr-0.5"></i> {{ $card['status'] }}
                                    </span>
                                </div>
                                <h3 class="truncate text-base font-black text-slate-800 leading-tight" title="{{ $card['nama'] }}">
                                    {{ $card['nama'] }}
                                </h3>
                                <p class="truncate text-[10px] font-bold text-slate-500 mt-1">
                                    {{ $card['tanggal'] }}
                                </p>
                            </div>
                        </div>

                        <div class="flex-1 w-full lg:w-auto grid grid-cols-2 sm:grid-cols-4 gap-2 bg-slate-50/50 lg:bg-transparent p-4 lg:p-0 rounded-xl lg:rounded-none lg:border-l lg:border-r border-slate-200/60 lg:px-4">
                            @foreach($card['metrics'] as $metric)
                                <div class="text-center lg:text-left flex flex-col justify-center px-1">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5 truncate" title="{{ $metric['label'] }}">
                                        {{ $metric['label'] }}
                                    </p>
                                    <p class="text-[11px] font-black text-slate-700 truncate">
                                        {{ $metric['value'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <div class="w-full lg:w-2/12 flex justify-end shrink-0">
                            <a href="{{ $card['route'] }}" data-no-delay="true" class="w-full lg:w-auto text-center px-4 py-2.5 rounded-[12px] text-[10px] font-black uppercase tracking-widest border shadow-sm transition-all {{ $tone['button'] }}">
                                <i class="fa-solid fa-folder-open md:mr-1"></i> <span class="hidden md:inline">Detail</span>
                            </a>
                        </div>
                    </div>

                    @if(!empty($card['catatan']))
                        <div class="bg-white border border-slate-100/80 rounded-xl px-4 py-3 text-[11px] font-semibold text-slate-600 shadow-sm leading-relaxed">
                            <span class="font-black text-slate-400 uppercase tracking-widest text-[9px] block mb-0.5"><i class="fa-regular fa-comment-dots mr-1"></i> Catatan Pemeriksaan</span>
                            {{ $card['catatan'] }}
                        </div>
                    @endif
                </div>

            @empty
                <div class="p-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-slate-50 border border-slate-100 text-2xl text-slate-300 shadow-inner mb-4">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-800">Tidak Ada Riwayat</h3>
                    <p class="mx-auto mt-1 max-w-sm text-sm font-semibold leading-6 text-slate-500">
                        Pencarian atau filter yang Anda terapkan tidak menemukan data yang cocok.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- CUSTOM PAGINATION --}}
        @if(method_exists($riwayat, 'links') && $riwayat->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/80 px-6 sm:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Halaman <span class="text-slate-900">{{ $riwayat->currentPage() }}</span> dari <span class="text-slate-900">{{ $riwayat->lastPage() }}</span>
                </p>
                
                <div class="flex items-center gap-1.5">
                    @if ($riwayat->onFirstPage())
                        <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-left text-[10px]"></i></button>
                    @else
                        <a href="{{ $riwayat->previousPageUrl() }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-100 transition-all"><i class="fas fa-chevron-left text-[10px]"></i></a>
                    @endif

                    @php 
                        $start = max(1, $riwayat->currentPage() - 1); 
                        $end = min($riwayat->lastPage(), $riwayat->currentPage() + 1); 
                    @endphp
                    
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $riwayat->currentPage())
                            {{-- Warna pill pagination juga mengikuti tema --}}
                            <span class="btn-pill w-8 h-8 flex items-center justify-center {{ match($heroTheme) { 'rose' => 'bg-rose-500', 'sky' => 'bg-sky-500', 'amber' => 'bg-amber-500', default => 'bg-emerald-500' } }} text-white font-black text-[10px] shadow-sm pointer-events-none">{{ $page }}</span>
                        @else
                            <a href="{{ $riwayat->url($page) }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-[10px] shadow-sm hover:bg-slate-100 transition-all">{{ $page }}</a>
                        @endif
                    @endfor

                    @if ($riwayat->hasMorePages())
                        <a href="{{ $riwayat->nextPageUrl() }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-100 transition-all"><i class="fas fa-chevron-right text-[10px]"></i></a>
                    @else
                        <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-right text-[10px]"></i></button>
                    @endif
                </div>
            </div>
        @endif

    </section>

</div>
@endsection