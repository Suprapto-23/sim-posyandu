@extends('layouts.user')

@section('title', 'Riwayat Terpadu')
@section('page_title', 'Riwayat Terpadu')

@php
    use Illuminate\Support\Facades\Route;

    $filters = $filters ?? [
        'search' => '',
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
    $heroTheme = 'emerald'; 
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

    $iconFocusClass = match($heroTheme) {
        'rose' => 'text-rose-500',
        'sky' => 'text-sky-500',
        'amber' => 'text-amber-500',
        default => 'text-emerald-500',
    };

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
        display: block;
        width: 100%;
    }
    .input-soft:focus {
        background: #ffffff;
        border-color: currentColor;
    }
    
    .search-padding {
        padding-left: 44px !important;
    }

    #riwayat-container {
        transition: opacity 0.2s ease;
    }
</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- 1. HERO SECTION --}}
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
                Pusat arsip klinis rekam medis personal Anda yang telah divalidasi oleh Bidan dan Tenaga Kesehatan.
            </p>
        </div>

        <div class="relative z-10 shrink-0">
            <div class="bg-white/20 border border-white/30 backdrop-blur-md rounded-[1.5rem] px-6 py-5 text-center shadow-inner min-w-[140px]">
                <p class="text-[10px] text-white/90 font-black uppercase tracking-widest mb-1">Total Riwayat</p>
                <p class="text-4xl font-black text-white leading-none" id="total-riwayat-badge">{{ $counts['total'] }}</p>
            </div>
        </div>
    </section>

    {{-- 2. FILTER WIDGET (SOLUSI ABSOLUT DENGAN CSS GRID) --}}
    <form id="filter-form" action="{{ $indexRoute }}" method="GET" class="bg-white/70 backdrop-blur-xl border border-slate-200 shadow-sm rounded-[2rem] p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-4 items-end relative z-20">
        
        {{-- Pencarian Spesifik: Dipaksa mengambil 8 dari 12 kolom layar desktop --}}
        <div class="lg:col-span-8 w-full">
            <label for="search" class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2 pl-1">
                Pencarian Spesifik
            </label>
            <div class="relative w-full">
                <input type="text" id="search" name="search" value="{{ $filters['search'] }}" autocomplete="off" placeholder="Ketik keluhan, hasil periksa, atau tanggal..." class="input-soft search-padding w-full h-11 focus:{{ $iconFocusClass }} focus:ring-4 focus:ring-current/10">
                
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <i class="fas fa-search"></i>
                </div>
                
                <div id="search-loader" class="absolute right-4 top-1/2 -translate-y-1/2 text-{{ $iconFocusClass }} hidden pointer-events-none">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>
            </div>
        </div>

        {{-- Dropdown Custom: Periode: Dipaksa mengambil 3 dari 12 kolom --}}
        <div class="lg:col-span-3 w-full" x-data="{ open: false, selected: '{{ $filters['periode'] }}' }">
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2 pl-1">Periode Periksa</label>
            <div class="relative w-full">
                <input type="hidden" name="periode" x-model="selected" id="periode-input">
                
                <button type="button" @click="open = !open" @click.away="open = false" class="input-soft w-full h-11 flex items-center justify-between focus:{{ $iconFocusClass }} focus:ring-4 focus:ring-current/10">
                    <span x-text="selected === 'semua' ? 'Semua Waktu' : (selected === 'bulan_ini' ? 'Bulan Ini' : (selected === '3_bulan' ? '3 Bulan Terakhir' : (selected === '6_bulan' ? '6 Bulan Terakhir' : 'Tahun Ini')))"></span>
                    <i class="fas fa-chevron-down text-slate-400 transition-transform" :class="{'rotate-180': open}"></i>
                </button>

                <div x-show="open" x-transition.opacity.duration.200ms class="absolute z-30 w-full mt-2 bg-white rounded-xl shadow-lg border border-slate-100 py-2" style="display: none;">
                    @foreach($periodeOptions as $key => $label)
                        <div @click="selected = '{{ $key }}'; open = false; document.getElementById('periode-input').dispatchEvent(new Event('change', { bubbles: true }))" 
                             class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer transition-colors"
                             :class="{'bg-slate-50 text-{{ $iconFocusClass }}': selected === '{{ $key }}'}">
                            {{ $label }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tombol Reset: Dipaksa mengambil sisa 1 kolom --}}
        <div class="lg:col-span-1 w-full flex justify-end lg:justify-center">
            <button type="submit" class="hidden" id="submit-btn">Filter</button>
            <a href="{{ $indexRoute }}" title="Reset Pencarian" class="h-11 w-11 rounded-[14px] border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:bg-slate-50 hover:text-rose-500 transition-all shadow-sm">
                <i class="fas fa-undo"></i>
            </a>
        </div>
    </form>

    {{-- 3. CONTAINER MASTER LIST --}}
    <div id="riwayat-container">
        <section class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            
            <div class="bg-slate-50/70 px-6 sm:px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-list-check opacity-50 text-lg"></i>
                    Daftar Riwayat Klinis
                </h5>
                <span class="rounded-lg bg-white border border-slate-200 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-500 shadow-sm">
                    Menampilkan <span id="list-count">{{ count($riwayatCards) }}</span> Data
                </span>
            </div>

            <div class="flex flex-col divide-y divide-slate-100" id="riwayat-list">
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
                            Belum ada catatan rekam medis atau filter tidak menemukan data.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- CUSTOM PAGINATION --}}
            <div id="pagination-wrapper">
                @if(method_exists($riwayat, 'links') && $riwayat->hasPages())
                    <div class="border-t border-slate-100 bg-slate-50/80 px-6 sm:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                            Halaman <span class="text-slate-900">{{ $riwayat->currentPage() }}</span> dari <span class="text-slate-900">{{ $riwayat->lastPage() }}</span>
                        </p>
                        
                        <div class="flex items-center gap-1.5 pagination-links">
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
            </div>

        </section>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filter-form');
    const searchInput = document.getElementById('search');
    const searchLoader = document.getElementById('search-loader');
    const periodeInput = document.getElementById('periode-input');
    const container = document.getElementById('riwayat-container');
    
    let debounceTimer;

    async function fetchFilteredData(urlParams) {
        searchLoader.classList.remove('hidden');
        container.style.opacity = '0.5';

        try {
            const url = new URL(form.action);
            url.search = urlParams.toString();

            const response = await fetch(url.toString(), {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'text/html' 
                }
            });

            if (!response.ok) throw new Error('Network error');

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const elementsToSync = [
                'riwayat-list', 
                'pagination-wrapper', 
                'total-riwayat-badge',
                'list-count'
            ];

            elementsToSync.forEach(id => {
                const newEl = doc.getElementById(id);
                const currentEl = document.getElementById(id);
                if (newEl && currentEl) {
                    currentEl.innerHTML = newEl.innerHTML;
                }
            });

            window.history.pushState({}, '', url);
            attachPaginationListeners();

        } catch (error) {
            console.error('Fetch error:', error);
        } finally {
            searchLoader.classList.add('hidden');
            container.style.opacity = '1';
        }
    }

    function triggerFilter() {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        fetchFilteredData(params);
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(triggerFilter, 400); 
    });

    periodeInput.addEventListener('change', triggerFilter);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        triggerFilter();
    });

    function attachPaginationListeners() {
        document.querySelectorAll('#pagination-wrapper a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                fetchFilteredData(url.searchParams);
            });
        });
    }

    attachPaginationListeners();

    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        searchInput.value = urlParams.get('search') || '';
        fetchFilteredData(urlParams);
    });
});
</script>
@endpush