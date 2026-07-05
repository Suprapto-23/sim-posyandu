@extends('layouts.user')

@section('title', 'Agenda Posyandu')
@section('page-title', 'Jadwal Kegiatan')

@php
    $specificAccess = array_filter($hakAkses, fn($h) => $h !== 'semua');
    $hasMultipleAccess = count($specificAccess) > 1;
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
        animation: popIn .5s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.95) translateY(15px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 2rem; 
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* Dipercepat dari 0.4s ke 0.3s */
    }
    
    .widget-card:hover {
        transform: translateY(-4px); /* Efek angkat dikurangi agar lebih smooth */
        box-shadow: 0 20px 40px -12px rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.4);
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.2); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.5); }

    /* Transisi Halus Grid SPA */
    #jadwal-grid {
        transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-8">

    {{-- 1. HERO WIDGET --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></span>
                        Sistem Monitoring Real-time
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Agenda Pemeriksaan & Jadwal
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Memantau jadwal Posyandu terkini untuk Anda dan keluarga. Data disinkronkan secara presisi untuk memastikan Anda tidak melewatkan agenda penting dari Bidan atau Kader.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto shrink-0 justify-center">
                <div class="bg-white/20 border border-white/30 backdrop-blur-md rounded-full px-8 py-5 text-center shadow-inner">
                    <p class="text-[10px] text-teal-100 font-black uppercase tracking-widest mb-1">Total Agenda</p>
                    <p class="text-4xl font-black text-white" id="summary-bulan-ini">{{ $summary['semua'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. NAVIGASI SASARAN (Digeser ke kiri dengan justify-start) --}}
    <div id="jadwal-tabs-container">
        @if($hasMultipleAccess)
            <!-- Menggunakan justify-start agar tab berada di pojok kiri -->
            <div id="jadwal-tabs" class="flex overflow-x-auto custom-scrollbar pb-3 pt-1 gap-4 justify-start">
                
                <a href="{{ route('user.jadwal.index', ['filter' => 'semua']) }}" 
                   class="whitespace-nowrap flex items-center gap-2 px-6 py-3.5 rounded-full border font-black text-xs transition-all {{ $filterTarget === 'semua' ? 'bg-teal-600 text-white border-teal-600 shadow-md shadow-teal-500/20' : 'bg-white text-slate-500 border-slate-200 hover:border-teal-300 hover:bg-teal-50' }}">
                    Semua Agenda
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ $filterTarget === 'semua' ? 'bg-teal-500 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['semua'] }}</span>
                </a>

                @if(in_array('balita', $hakAkses))
                    <a href="{{ route('user.jadwal.index', ['filter' => 'balita']) }}" 
                       class="whitespace-nowrap flex items-center gap-2 px-6 py-3.5 rounded-full border font-black text-xs transition-all {{ $filterTarget === 'balita' ? 'bg-rose-500 text-white border-rose-500 shadow-md shadow-rose-500/20' : 'bg-white text-slate-500 border-slate-200 hover:border-rose-300 hover:bg-rose-50' }}">
                        <i class="fas fa-child-reaching {{ $filterTarget === 'balita' ? 'text-white' : 'text-rose-500' }}"></i> Balita
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $filterTarget === 'balita' ? 'bg-rose-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['balita'] }}</span>
                    </a>
                @endif

                @if(in_array('remaja', $hakAkses))
                    <a href="{{ route('user.jadwal.index', ['filter' => 'remaja']) }}" 
                       class="whitespace-nowrap flex items-center gap-2 px-6 py-3.5 rounded-full border font-black text-xs transition-all {{ $filterTarget === 'remaja' ? 'bg-sky-500 text-white border-sky-500 shadow-md shadow-sky-500/20' : 'bg-white text-slate-500 border-slate-200 hover:border-sky-300 hover:bg-sky-50' }}">
                        <i class="fas fa-user-graduate {{ $filterTarget === 'remaja' ? 'text-white' : 'text-sky-500' }}"></i> Remaja
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $filterTarget === 'remaja' ? 'bg-sky-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['remaja'] }}</span>
                    </a>
                @endif

                @if(in_array('lansia', $hakAkses))
                    <a href="{{ route('user.jadwal.index', ['filter' => 'lansia']) }}" 
                       class="whitespace-nowrap flex items-center gap-2 px-6 py-3.5 rounded-full border font-black text-xs transition-all {{ $filterTarget === 'lansia' ? 'bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-500/20' : 'bg-white text-slate-500 border-slate-200 hover:border-amber-300 hover:bg-amber-50' }}">
                        <i class="fas fa-person-cane {{ $filterTarget === 'lansia' ? 'text-white' : 'text-amber-500' }}"></i> Lansia
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $filterTarget === 'lansia' ? 'bg-amber-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['lansia'] }}</span>
                    </a>
                @endif
            </div>
        @else
            @if(count($specificAccess) === 1)
                @php
                    $singleAccess = array_values($specificAccess)[0];
                    $singleLabel = match($singleAccess) {
                        'balita' => 'Jadwal Khusus Balita',
                        'remaja' => 'Jadwal Khusus Remaja',
                        'lansia' => 'Jadwal Khusus Lansia',
                        default => 'Jadwal Umum'
                    };
                    $singleIcon = match($singleAccess) {
                        'balita' => 'fa-child-reaching',
                        'remaja' => 'fa-user-graduate',
                        'lansia' => 'fa-person-cane',
                        default => 'fa-users'
                    };
                @endphp
                <div class="flex justify-start mb-4 mt-2 px-1">
                    <span class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white border border-slate-200 text-xs font-black text-slate-600 shadow-sm cursor-default">
                        <i class="fa-solid {{ $singleIcon }} text-teal-500 text-base"></i> {{ $singleLabel }}
                    </span>
                </div>
            @endif
        @endif
    </div>

    {{-- 3. AREA KARTU JADWAL --}}
    <div class="relative pt-4">
        
        <div id="live-loader" class="absolute -top-2 left-2 hidden items-center gap-2 z-20 transition-opacity duration-300 bg-white/90 backdrop-blur-sm px-4 py-1.5 rounded-full shadow-sm border border-emerald-100">
            <span class="flex h-2.5 w-2.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Memperbarui...</span>
        </div>

        <div id="jadwal-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 opacity-100 scale-100 mt-2">
            @forelse($jadwalCards as $card)
                @php
                    $statusColor = match($card['status_tone']) {
                        'emerald' => 'bg-emerald-500 text-white',
                        'sky'     => 'bg-sky-500 text-white',
                        'amber'   => 'bg-amber-500 text-white',
                        default   => 'bg-slate-200 text-slate-500',
                    };
                    $targetColor = match($card['target_tone']) {
                        'rose'  => 'text-rose-600 bg-rose-50 border-rose-100',
                        'sky'   => 'text-sky-600 bg-sky-50 border-sky-100',
                        'amber' => 'text-amber-600 bg-amber-50 border-amber-100',
                        default => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                    };
                @endphp

                <!-- Kartu Detail Tanpa Loader -->
                <a href="{{ route('user.jadwal.show', $card['id']) }}" class="widget-card flex flex-col relative group h-full overflow-hidden {{ $card['is_past'] ? 'opacity-75 grayscale-[20%]' : '' }}">
                    
                    {{-- Badge Status --}}
                    <div class="absolute top-0 right-0 {{ $statusColor }} text-[9px] font-black uppercase tracking-widest px-4 py-1.5 rounded-bl-2xl z-10 shadow-sm">
                        {{ $card['status_label'] }}
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start gap-5 mb-6">
                            <div class="w-16 h-16 rounded-2xl {{ $card['is_today'] ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : ($card['is_past'] ? 'bg-slate-100 text-slate-400' : 'bg-slate-50 text-slate-700 border border-slate-200') }} flex flex-col items-center justify-center shrink-0">
                                <span class="text-[10px] font-black uppercase tracking-widest">{{ $card['bulan'] }}</span>
                                <span class="text-2xl font-black leading-none mt-0.5">{{ $card['hari'] }}</span>
                            </div>
                            
                            <div class="min-w-0 pr-6 pt-1">
                                <span class="inline-block px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest mb-2.5 border {{ $targetColor }}">
                                    {{ $card['target_label'] }}
                                </span>
                                <h3 class="text-[15px] font-black text-slate-800 leading-tight group-hover:text-teal-600 transition-colors truncate" title="{{ $card['judul'] }}">
                                    {{ $card['judul'] }}
                                </h3>
                            </div>
                        </div>

                        <div class="space-y-3.5 mt-auto pt-5 border-t border-slate-100/80">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                    <i class="far fa-clock text-[11px]"></i>
                                </div>
                                <div class="pt-0.5">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Waktu</p>
                                    <p class="text-xs font-bold text-slate-700 mt-0.5">{{ $card['waktu'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                    <i class="fas fa-map-marker-alt text-[11px]"></i>
                                </div>
                                <div class="pt-0.5 min-w-0">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Lokasi</p>
                                    <p class="text-xs font-bold text-slate-700 mt-0.5 truncate" title="{{ $card['lokasi'] }}">{{ $card['lokasi'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-20 flex flex-col items-center justify-center bg-white/40 backdrop-blur-md rounded-[3rem] border-2 border-dashed border-slate-200">
                    <div class="w-24 h-24 bg-white text-slate-300 rounded-[2rem] flex items-center justify-center text-4xl mb-6 shadow-sm border border-slate-100">
                        <i class="far fa-calendar-times"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">Jadwal Kosong</h3>
                    <p class="text-sm font-medium text-slate-500 text-center max-w-md leading-relaxed">Saat ini belum ada agenda Posyandu untuk kategori sasaran yang Anda pilih.</p>
                    @if($filterTarget !== 'semua' && $hasMultipleAccess)
                        <a href="{{ route('user.jadwal.index') }}" class="mt-8 rounded-full font-black text-xs text-white bg-teal-500 hover:bg-teal-600 px-8 py-4 shadow-lg shadow-teal-500/30 transition-all hover:-translate-y-1">
                            Kembali ke Semua Jadwal
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    {{-- 4. PAGINATION --}}
    <div id="jadwal-pagination" class="mt-12 flex justify-center">
        @if($jadwalKegiatan->hasPages())
            <nav class="inline-flex items-center justify-center gap-2 bg-white/80 backdrop-blur-md py-2.5 px-4 rounded-full border border-slate-200 shadow-sm">
                
                {{-- Previous Arrow --}}
                @if ($jadwalKegiatan->onFirstPage())
                    <span class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 text-slate-300 cursor-not-allowed">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </span>
                @else
                    <a href="{{ $jadwalKegiatan->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-full bg-teal-50 text-teal-600 hover:bg-teal-500 hover:text-white transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                @endif

                @php
                    $currentPage = $jadwalKegiatan->currentPage();
                    $lastPage = $jadwalKegiatan->lastPage();
                    
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);

                    if ($currentPage <= 3) {
                        $end = min(5, $lastPage);
                    }
                    
                    if ($lastPage - $currentPage < 2) {
                        $start = max(1, $lastPage - 4);
                    }
                @endphp

                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $currentPage)
                        <span class="w-9 h-9 flex items-center justify-center rounded-full bg-teal-500 text-white shadow-md font-black text-sm transition-all transform scale-110">
                            {{ $i }}
                        </span>
                    @else
                        <a href="{{ $jadwalKegiatan->url($i) }}" class="w-9 h-9 flex items-center justify-center rounded-full bg-transparent text-slate-500 hover:bg-teal-50 hover:text-teal-600 transition-all text-sm font-bold">
                            {{ $i }}
                        </a>
                    @endif
                @endfor

                {{-- Next Arrow --}}
                @if ($jadwalKegiatan->hasMorePages())
                    <a href="{{ $jadwalKegiatan->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-full bg-teal-50 text-teal-600 hover:bg-teal-500 hover:text-white transition-all shadow-sm">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                @else
                    <span class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 text-slate-300 cursor-not-allowed">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </span>
                @endif
            </nav>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentUrl = new URL(window.location.href);
    const liveLoader = document.getElementById('live-loader');

    /**
     * Fungsi Inti Pengambilan Data (Anti Reload SPA & Transisi Halus)
     */
    async function fetchAndUpdate(urlToFetch, isUserAction = false) {
        const grid = document.getElementById('jadwal-grid');
        
        try {
            if(liveLoader && !isUserAction) {
                // Tampilkan loader kecil di pojok hanya saat background polling
                liveLoader.style.display = 'flex';
                setTimeout(() => liveLoader.style.opacity = '1', 10);
            }

            if (isUserAction && grid) {
                // Efek fade halus saat pindah tab
                grid.style.opacity = '0.5'; 
            }

            const response = await fetch(urlToFetch.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            });

            if (!response.ok) throw new Error('Network error');

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const elementsToSync = ['jadwal-grid', 'jadwal-tabs-container', 'jadwal-pagination', 'summary-bulan-ini'];

            elementsToSync.forEach(id => {
                const newEl = doc.getElementById(id);
                const currentEl = document.getElementById(id);
                if (newEl && currentEl) currentEl.innerHTML = newEl.innerHTML;
            });

            if (isUserAction && grid) {
                setTimeout(() => {
                    grid.style.opacity = '1';
                }, 50);
            }
        } catch (error) {
            console.warn('Silent Fetch Error:', error);
            if (isUserAction && grid) {
                grid.style.opacity = '1';
            }
        } finally {
            if(liveLoader) {
                liveLoader.style.opacity = '0';
                setTimeout(() => {
                    if(liveLoader.style.opacity === '0') liveLoader.style.display = 'none';
                }, 300);
            }
        }
    }

    // 1. POLLING BACKGROUND (Setiap 30 Detik)
    setInterval(() => fetchAndUpdate(currentUrl, false), 30000);

    // 2. INTERCEPT KLIK TABS & PAGINATION
    document.body.addEventListener('click', function(e) {
        const spalink = e.target.closest('#jadwal-tabs a, #jadwal-pagination a');
        
        if (spalink) {
            e.preventDefault(); 
            const url = new URL(spalink.href);
            currentUrl = url; 
            window.history.pushState({}, '', url); 
            fetchAndUpdate(url, true);
        }
    });

    // 3. HANDLE TOMBOL BACK/FORWARD BROWSER UNTUK FILTER TABS
    window.addEventListener('popstate', function() {
        currentUrl = new URL(window.location.href);
        fetchAndUpdate(currentUrl, true);
    });
});
</script>
@endpush