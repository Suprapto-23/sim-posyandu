@extends('layouts.user')

@section('title', 'Pesan Bidan')
@section('page_title', 'Pesan Bidan')

@php
    use Illuminate\Support\Facades\Route;

    $filters = $filters ?? [
        'filter' => request('filter', 'semua'),
        'search' => request('search', ''),
    ];

    $counts = $counts ?? [
        'semua' => $allCount ?? 0,
        'belum' => $unreadCount ?? 0,
        'sudah' => $readCount ?? 0,
    ];

    $notifikasiCards = collect($notifikasiCards ?? []);

    $indexRoute = Route::has('user.notifikasi.index') ? route('user.notifikasi.index') : url('/user/notifikasi');
    $markAllRoute = Route::has('user.notifikasi.markall') ? route('user.notifikasi.markall') : '#';
    $readRoute = function ($id) { return Route::has('user.notifikasi.read') ? route('user.notifikasi.read', $id) : '#'; };

    // Rute Buka Detail: Akan mencoba menggunakan route user.notifikasi.show jika ada
    $showRoute = function ($id) { return Route::has('user.notifikasi.show') ? route('user.notifikasi.show', $id) : '#'; };

    $tabs = [
        ['key' => 'semua', 'label' => 'Semua Pesan', 'count' => $counts['semua'] ?? 0, 'icon' => 'fa-inbox'],
        ['key' => 'belum', 'label' => 'Belum Dibaca', 'count' => $counts['belum'] ?? 0, 'icon' => 'fa-envelope'],
        ['key' => 'sudah', 'label' => 'Sudah Dibaca', 'count' => $counts['sudah'] ?? 0, 'icon' => 'fa-envelope-open'],
    ];

    $toneMap = [
        'emerald' => ['icon_bg' => 'bg-emerald-50', 'icon_text' => 'text-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-600 border-emerald-200', 'button' => 'bg-white text-emerald-600 border-emerald-200 hover:bg-emerald-500 hover:text-white'],
        'rose' => ['icon_bg' => 'bg-rose-50', 'icon_text' => 'text-rose-500', 'badge' => 'bg-rose-50 text-rose-600 border-rose-200', 'button' => 'bg-white text-rose-600 border-rose-200 hover:bg-rose-500 hover:text-white'],
        'sky' => ['icon_bg' => 'bg-sky-50', 'icon_text' => 'text-sky-500', 'badge' => 'bg-sky-50 text-sky-600 border-sky-200', 'button' => 'bg-white text-sky-600 border-sky-200 hover:bg-sky-500 hover:text-white'],
        'amber' => ['icon_bg' => 'bg-amber-50', 'icon_text' => 'text-amber-500', 'badge' => 'bg-amber-50 text-amber-600 border-amber-200', 'button' => 'bg-white text-amber-600 border-amber-200 hover:bg-amber-500 hover:text-white'],
        'violet' => ['icon_bg' => 'bg-violet-50', 'icon_text' => 'text-violet-500', 'badge' => 'bg-violet-50 text-violet-600 border-violet-200', 'button' => 'bg-white text-violet-600 border-violet-200 hover:bg-violet-500 hover:text-white'],
        'slate' => ['icon_bg' => 'bg-slate-50', 'icon_text' => 'text-slate-500', 'badge' => 'bg-slate-50 text-slate-600 border-slate-200', 'button' => 'bg-white text-slate-600 border-slate-200 hover:bg-slate-500 hover:text-white'],
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

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .hero-grid { background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px); background-size: 24px 24px; }
    .btn-pill { border-radius: 12px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.97); }

    /* CSS Input Search */
    .input-soft {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: 10px 14px 10px 38px; /* Padding kiri dinaikkan untuk icon kaca pembesar */
        font-size: 12px; font-weight: 700; color: #334155;
        outline: none; transition: all .2s ease;
        width: 100%; height: 2.75rem;
    }
    .input-soft:focus { background: #ffffff; border-color: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,.1); }

    /* Perbaikan Posisi Ikon Search & Loading */
    .search-wrapper { position: relative; width: 100%; }
    .search-icon-left { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
    
    .search-loading { 
        position: absolute; right: 14px; top: 50%; transform: translateY(-50%); 
        opacity: 0; transition: opacity 0.2s ease; color: #10b981; pointer-events: none;
    }
    .search-loading.show { opacity: 1; animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

    /* Transisi AJAX Konten (Sangat Halus) */
    .ajax-container { transition: opacity 0.15s ease-in-out; opacity: 1; }
    .ajax-container.is-loading { opacity: 0.5; pointer-events: none; }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- HERO SECTION --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[2.5rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(20,184,166,.35)] border border-white/20 text-center md:text-left flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[70px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex-1">
            <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                <span class="bg-white/20 backdrop-blur-md border border-white/20 px-3 py-1.5 rounded-full shadow-sm">Kotak Masuk</span>
                <i class="fas fa-chevron-right text-[8px] opacity-70"></i>
                <span>Pemberitahuan Warga</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-black text-white font-poppins tracking-tight">Pesan Bidan</h1>
            <p class="text-white/80 text-sm font-medium mt-3 leading-relaxed max-w-xl mx-auto md:mx-0">Pusat informasi jadwal, hasil pemeriksaan, imunisasi, dan pemberitahuan penting Posyandu untuk keluarga Anda.</p>
        </div>

        <div class="relative z-10 shrink-0 flex gap-3" id="heroCounters">
            <div class="bg-white/20 border border-white/30 backdrop-blur-md rounded-[1.5rem] px-5 py-4 text-center shadow-inner min-w-[100px]">
                <p class="text-[9px] text-white/90 font-black uppercase tracking-widest mb-1">Total</p>
                <p class="text-2xl font-black text-white leading-none">{{ $counts['semua'] }}</p>
            </div>
            <div class="bg-rose-400/80 border border-rose-300/50 backdrop-blur-md rounded-[1.5rem] px-5 py-4 text-center shadow-inner min-w-[100px]">
                <p class="text-[9px] text-rose-50 font-black uppercase tracking-widest mb-1 flex items-center justify-center gap-1"><i class="fas fa-circle text-[6px]"></i> Baru</p>
                <p class="text-2xl font-black text-white leading-none">{{ $counts['belum'] }}</p>
            </div>
        </div>
    </section>

    @if(session('success') || session('error'))
        <div class="rounded-2xl border p-4 shadow-sm flex items-center justify-center gap-3 font-bold text-sm {{ session('error') ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
            <i class="fas {{ session('error') ? 'fa-triangle-exclamation' : 'fa-check-circle' }}"></i>
            {{ session('error') ?: session('success') }}
        </div>
    @endif

    {{-- TABS & SEARCH FORM --}}
    <div class="bg-white/70 backdrop-blur-xl border border-slate-200 shadow-sm rounded-[2rem] p-5 flex flex-col xl:flex-row gap-5 justify-between">
        <div class="flex flex-wrap gap-2 items-center" id="tabsContainer">
            @foreach($tabs as $tab)
                @php
                    $isActive = $filters['filter'] === $tab['key'];
                    $query = array_filter(['filter' => $tab['key'], 'search' => $filters['search']]);
                    $tabUrl = $indexRoute . '?' . http_build_query($query);
                @endphp
                <a href="{{ $tabUrl }}" data-filter="{{ $tab['key'] }}" class="ajax-link btn-pill h-11 px-4 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest border transition-all {{ $isActive ? 'bg-emerald-500 border-emerald-500 text-white shadow-md' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    <i class="fas {{ $tab['icon'] }}"></i>
                    <span class="hidden sm:inline">{{ $tab['label'] }}</span>
                    <span class="{{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }} px-2 py-0.5 rounded-full">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>

        <form action="{{ $indexRoute }}" method="GET" id="searchForm" class="flex-1 flex gap-2 xl:justify-end">
            <input type="hidden" name="filter" id="filterInput" value="{{ $filters['filter'] }}">
            <div class="search-wrapper xl:w-72">
                {{-- Icon statis di kiri --}}
                <div class="search-icon-left"><i class="fas fa-search"></i></div>
                
                {{-- Input --}}
                <input type="text" id="liveSearch" name="search" value="{{ $filters['search'] }}" autocomplete="off" placeholder="Cari pesan instan..." class="input-soft">
                
                {{-- Loading spinner di kanan (hanya muncul saat mencari) --}}
                <div class="search-loading" id="searchLoading"><i class="fas fa-spinner"></i></div>
            </div>
        </form>
    </div>

    {{-- BUNGKUS AJAX (HANYA INI YANG DIGANTI SAAT AJAX REQUEST) --}}
    <div id="ajaxContentArea">
        <div id="dynamicContentWrapper" class="ajax-container bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            
            <div class="bg-slate-50/70 px-6 sm:px-8 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-envelope-open-text opacity-50 text-lg"></i>
                    @if(filled($filters['search']))
                        Hasil Pencarian: "{{ $filters['search'] }}"
                    @else
                        Daftar Pesan
                    @endif
                </h5>
                
                @if(($counts['belum'] ?? 0) > 0 && blank($filters['search']))
                    <form action="{{ $markAllRoute }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-pill h-9 bg-white border border-slate-200 px-4 text-[9px] font-black uppercase tracking-widest text-emerald-600 shadow-sm transition-all hover:bg-emerald-50 hover:border-emerald-200">
                            <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                @endif
            </div>

            <div class="flex flex-col divide-y divide-slate-100">
                @forelse($notifikasiCards as $card)
                    @php $tone = $toneMap[$card['tone'] ?? 'slate'] ?? $toneMap['slate']; @endphp
                    <div class="flex flex-col lg:flex-row gap-5 p-6 sm:px-8 transition-colors duration-200 {{ !$card['is_read'] ? 'bg-emerald-50/30' : 'hover:bg-slate-50/50' }}">
                        
                        <div class="shrink-0 flex justify-center lg:justify-start">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-2xl shadow-sm border border-slate-100 relative {{ $tone['icon_bg'] }} {{ $tone['icon_text'] }}">
                                <i class="fas {{ $card['icon'] }}"></i>
                                @if(!$card['is_read'])
                                    <span class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-rose-500 border-2 border-white animate-pulse"></span>
                                @endif
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="inline-block px-2.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border {{ $tone['badge'] }}">{{ $card['label'] }}</span>
                                @if(!$card['is_read'])
                                    <span class="inline-block px-2.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border border-rose-200 bg-rose-50 text-rose-700"><i class="fas fa-circle text-[5px] mr-0.5 align-middle"></i> Baru</span>
                                @endif
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-auto lg:ml-2"><i class="fas fa-clock mr-0.5"></i> {{ $card['waktu'] }}</span>
                            </div>
                            <h3 class="text-base font-black text-slate-800 leading-tight mb-1" title="{{ $card['judul'] }}">{{ $card['judul'] }}</h3>
                            <p class="text-xs font-semibold text-slate-600 leading-relaxed max-w-4xl">{{ Str::limit($card['pesan'], 120) }}</p>
                            <p class="text-[10px] font-bold text-slate-400 mt-2">{{ $card['tanggal'] }}</p>
                        </div>

                        {{-- GANTI BAGIAN LINK DETAIL MENJADI SEPERTI INI --}}
<div class="w-full lg:w-40 flex flex-col justify-center gap-2 shrink-0 border-t lg:border-t-0 lg:border-l border-slate-100 pt-4 lg:pt-0 lg:pl-6 mt-2 lg:mt-0">
    @if(!$card['is_read'])
        <form action="{{ $readRoute($card['id']) }}" method="POST">
            @csrf
            <button type="submit" class="btn-pill w-full text-center px-4 py-2.5 rounded-[12px] text-[10px] font-black uppercase tracking-widest bg-emerald-500 text-white shadow-sm transition-all hover:bg-emerald-600">
                <i class="fa-solid fa-check mr-1"></i> Mengerti
            </button>
        </form>
    @endif

    {{-- PAKSA SELALU MENGGUNAKAN ROUTE SHOW --}}
    <a href="{{ route('user.notifikasi.show', $card['id']) }}" class="btn-pill w-full text-center px-4 py-2.5 rounded-[12px] text-[10px] font-black uppercase tracking-widest border shadow-sm transition-all {{ $tone['button'] }}">
        <i class="fa-solid fa-folder-open mr-1"></i> Buka Detail
    </a>
</div>
                    </div>
                @empty
                    <div class="p-16 text-center bg-slate-50/50">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-white border border-slate-200 text-2xl text-slate-300 shadow-sm mb-4"><i class="fas fa-search"></i></div>
                        @if(filled($filters['search']))
                            <h3 class="text-lg font-black text-slate-800">Pesan Tidak Ditemukan</h3>
                            <p class="mx-auto mt-1 max-w-sm text-sm font-semibold leading-6 text-slate-500">Tidak ada pesan yang cocok dengan <strong class="text-slate-700">"{{ $filters['search'] }}"</strong>.</p>
                        @else
                            <h3 class="text-lg font-black text-slate-800">Kotak Masuk Bersih</h3>
                            <p class="mx-auto mt-1 max-w-sm text-sm font-semibold leading-6 text-slate-500">Tidak ada pesan atau pemberitahuan pada kategori ini.</p>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- CUSTOM PAGINATION --}}
            @if(isset($notifikasis) && method_exists($notifikasis, 'hasPages') && $notifikasis->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/80 px-6 sm:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Halaman <span class="text-slate-900">{{ $notifikasis->currentPage() }}</span> dari <span class="text-slate-900">{{ $notifikasis->lastPage() }}</span></p>
                    <div class="flex items-center gap-1.5" id="paginationContainer">
                        @if ($notifikasis->onFirstPage())
                            <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-left text-[10px]"></i></button>
                        @else
                            <a href="{{ $notifikasis->previousPageUrl() }}&search={{ urlencode($filters['search']) }}&filter={{ $filters['filter'] }}" class="ajax-link btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-100"><i class="fas fa-chevron-left text-[10px]"></i></a>
                        @endif

                        @php $start = max(1, $notifikasis->currentPage() - 1); $end = min($notifikasis->lastPage(), $notifikasis->currentPage() + 1); @endphp
                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $notifikasis->currentPage())
                                <span class="btn-pill w-8 h-8 flex items-center justify-center bg-emerald-500 text-white font-black text-[10px] shadow-sm pointer-events-none">{{ $page }}</span>
                            @else
                                <a href="{{ $notifikasis->url($page) }}&search={{ urlencode($filters['search']) }}&filter={{ $filters['filter'] }}" class="ajax-link btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-[10px] shadow-sm hover:bg-slate-100">{{ $page }}</a>
                            @endif
                        @endfor

                        @if ($notifikasis->hasMorePages())
                            <a href="{{ $notifikasis->nextPageUrl() }}&search={{ urlencode($filters['search']) }}&filter={{ $filters['filter'] }}" class="ajax-link btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-100"><i class="fas fa-chevron-right text-[10px]"></i></a>
                        @else
                            <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-right text-[10px]"></i></button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div> {{-- Akhir ajaxContentArea --}}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('liveSearch');
        const searchForm = document.getElementById('searchForm');
        const searchLoading = document.getElementById('searchLoading');
        const ajaxContentArea = document.getElementById('ajaxContentArea');
        const tabsContainer = document.getElementById('tabsContainer');
        const filterInput = document.getElementById('filterInput');

        let fetchController;
        let debounceTimer;

        searchForm.addEventListener('submit', e => e.preventDefault());

        const fetchContent = (url) => {
            if (fetchController) fetchController.abort();
            fetchController = new AbortController();

            // Minta server HANYA merender blok tertentu (opsional jika backend mendukung, 
            // jika tidak, kita tangkap full HTML dan ekstrak bodynya)
            const targetContainer = document.getElementById('dynamicContentWrapper');
            if(targetContainer) targetContainer.classList.add('is-loading');
            searchLoading.classList.add('show');

            fetch(url, {
                signal: fetchController.signal,
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-PJAX': 'true' // penanda opsional untuk controller
                }
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // 1. Ekstrak Area Pesan (Anti ngedip: jangan ganti wrapper parent-nya jika bisa)
                const newContentWrapper = doc.getElementById('dynamicContentWrapper');
                if (newContentWrapper && targetContainer) {
                    targetContainer.innerHTML = newContentWrapper.innerHTML;
                }

                // 2. Ekstrak Tabs (untuk update count)
                const newTabs = doc.getElementById('tabsContainer');
                if (newTabs && tabsContainer) {
                    tabsContainer.innerHTML = newTabs.innerHTML;
                }

                window.history.pushState({}, '', url);
            })
            .catch(err => { if (err.name !== 'AbortError') console.error('Fetch error:', err); })
            .finally(() => {
                if(targetContainer) targetContainer.classList.remove('is-loading');
                searchLoading.classList.remove('show');
            });
        };

        // Live Search Input (Sangat responsif)
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const url = new URL(searchForm.action);
            url.searchParams.set('search', this.value);
            url.searchParams.set('filter', filterInput.value);

            // Debounce super cepat 100ms
            debounceTimer = setTimeout(() => fetchContent(url.toString()), 100);
        });

        // Event Delegation Tabs & Pagination
        document.body.addEventListener('click', function(e) {
            const targetLink = e.target.closest('a.ajax-link');
            if (targetLink) {
                e.preventDefault();
                const url = new URL(targetLink.href);
                
                // Update filter jika klik tab
                if(targetLink.dataset.filter) {
                    filterInput.value = targetLink.dataset.filter;
                    if(searchInput.value) url.searchParams.set('search', searchInput.value);
                }

                fetchContent(url.toString());
            }
        });

        window.addEventListener('popstate', () => window.location.reload());
        // =======================================================
        // LIVE POLLING ANTI RELOAD - MENCOCOKKAN REAL-TIME DATA
        // =======================================================
        const POLLING_INTERVAL = 8000; // Cek data setiap 8 detik secara tak kasat mata

        setInterval(function() {
            // Jangan ganggu jika user sedang aktif mengetik di kolom pencarian
            if (searchInput && searchInput.value.trim() !== '') return;

            const currentUrl = new URL(window.location.href);

            fetch(currentUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const freshDoc = parser.parseFromString(html, 'text/html');

                // 1. Sinkronisasi Wrapper Kartu Notifikasi Utama
                const oldWrapper = document.getElementById('dynamicContentWrapper');
                const newWrapper = freshDoc.getElementById('dynamicContentWrapper');
                if (oldWrapper && newWrapper && oldWrapper.innerHTML.trim() !== newWrapper.innerHTML.trim()) {
                    oldWrapper.innerHTML = newWrapper.innerHTML;
                }

                // 2. Sinkronisasi Angka Badge Counter di Atas Tab (Semua, Belum Dibaca, Sudah Dibaca)
                const oldTabs = document.getElementById('tabsContainer');
                const newTabs = freshDoc.getElementById('tabsContainer');
                if (oldTabs && newTabs) {
                    oldTabs.innerHTML = newTabs.innerHTML;
                }

                // 3. Sinkronisasi Grid Counter Header / Banner atas jika ada
                const oldHeroCounters = document.getElementById('heroCounters');
                const newHeroCounters = freshDoc.getElementById('heroCounters');
                if (oldHeroCounters && newHeroCounters) {
                    oldHeroCounters.innerHTML = newHeroCounters.innerHTML;
                }
            })
            .catch(err => console.log('Silent sync paused, reconnecting...'));
        }, POLLING_INTERVAL);
    });
</script>
@endpush