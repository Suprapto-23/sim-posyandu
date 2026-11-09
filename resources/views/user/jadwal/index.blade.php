@extends('layouts.user')

@section('title', 'Agenda Posyandu')
@section('page-title', 'Jadwal Kegiatan')

{{-- ============================================================
     STYLES
     ============================================================ --}}
@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image:
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    /* Animasi masuk halaman */
    .animate-pop-in {
        animation: popIn .5s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }
    @keyframes popIn {
        from { opacity: 0; transform: scale(.95) translateY(15px); }
        to   { opacity: 1; transform: none; }
    }

    /* Pola titik pada hero */
    .hero-dots {
        background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='2' cy='2' r='2' fill='%23ffffff' fill-opacity='0.05'/%3E%3C/svg%3E");
        opacity: .2;
    }

    /* Kartu jadwal */
    .widget-card {
        background: rgba(255, 255, 255, .95);
        border: 1px solid rgba(226, 232, 240, .8);
        border-radius: 2rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, .05);
        transition: transform .3s cubic-bezier(.4, 0, .2, 1),
                    box-shadow .3s cubic-bezier(.4, 0, .2, 1),
                    border-color .3s cubic-bezier(.4, 0, .2, 1);
    }
    @media (hover: hover) {
        .widget-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(16, 185, 129, .15);
            border-color: rgba(16, 185, 129, .4);
        }
    }
    .widget-card:focus-visible,
    #jadwal-tabs a:focus-visible,
    #jadwal-pagination a:focus-visible {
        outline: 2px solid #10b981;
        outline-offset: 3px;
    }

    /* Scrollbar tab */
    .custom-scrollbar::-webkit-scrollbar { height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, .2); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, .5); }

    /* Transisi grid saat pindah tab / halaman (tanpa memblokir klik) */
    #jadwal-grid { transition: opacity .3s ease-in-out; }
    #jadwal-grid.is-loading { opacity: .5; }

    /* Offset scroll agar tidak tertutup header sticky */
    #jadwal-tabs-container { scroll-margin-top: 6rem; }

    /* Indikator "Memperbarui..." untuk polling latar belakang */
    #live-loader {
        opacity: 0;
        visibility: hidden;
        transition: opacity .3s ease, visibility .3s;
    }
    #live-loader.is-visible { opacity: 1; visibility: visible; }

    @media (prefers-reduced-motion: reduce) {
        .animate-pop-in { animation: none; opacity: 1; }
        .widget-card, #jadwal-grid, #live-loader { transition: none; }
        .widget-card:hover { transform: none; }
    }
</style>
@endpush

{{-- ============================================================
     CONTENT
     ============================================================ --}}
@section('content')
@php
    /* ---------- Data dasar ---------- */
    $hakAkses          = $hakAkses ?? [];
    $summary           = $summary ?? [];
    $specificAccess    = array_values(array_filter($hakAkses, fn ($h) => $h !== 'semua'));
    $hasMultipleAccess = count($specificAccess) > 1;

    /* ---------- Konfigurasi tab sasaran ----------
       Class ditulis utuh (bukan disusun dari variabel) supaya
       terdeteksi oleh Tailwind saat build. */
    $tabs = [
        'semua'  => [
            'label' => 'Semua Agenda', 'icon' => null, 'icon_color' => '',
            'active' => 'bg-teal-600 border-teal-600 shadow-md shadow-teal-500/20',
            'badge'  => 'bg-teal-500',
            'hover'  => 'hover:border-teal-300 hover:bg-teal-50',
        ],
        'balita' => [
            'label' => 'Balita', 'icon' => 'fa-child-reaching', 'icon_color' => 'text-rose-500',
            'active' => 'bg-rose-500 border-rose-500 shadow-md shadow-rose-500/20',
            'badge'  => 'bg-rose-400',
            'hover'  => 'hover:border-rose-300 hover:bg-rose-50',
            'single' => 'Jadwal Khusus Balita',
        ],
        'remaja' => [
            'label' => 'Remaja', 'icon' => 'fa-user-graduate', 'icon_color' => 'text-sky-500',
            'active' => 'bg-sky-500 border-sky-500 shadow-md shadow-sky-500/20',
            'badge'  => 'bg-sky-400',
            'hover'  => 'hover:border-sky-300 hover:bg-sky-50',
            'single' => 'Jadwal Khusus Remaja',
        ],
        'lansia' => [
            'label' => 'Lansia', 'icon' => 'fa-person-cane', 'icon_color' => 'text-amber-500',
            'active' => 'bg-amber-500 border-amber-500 shadow-md shadow-amber-500/20',
            'badge'  => 'bg-amber-400',
            'hover'  => 'hover:border-amber-300 hover:bg-amber-50',
            'single' => 'Jadwal Khusus Lansia',
        ],
    ];

    $visibleTabs = array_filter(
        $tabs,
        fn ($tab, $key) => $key === 'semua' || in_array($key, $hakAkses, true),
        ARRAY_FILTER_USE_BOTH
    );

    /* ---------- Warna badge kartu ---------- */
    $statusTones = [
        'emerald' => 'bg-emerald-500 text-white',
        'sky'     => 'bg-sky-500 text-white',
        'amber'   => 'bg-amber-500 text-white',
        'default' => 'bg-slate-200 text-slate-500',
    ];
    $targetTones = [
        'rose'    => 'text-rose-600 bg-rose-50 border-rose-100',
        'sky'     => 'text-sky-600 bg-sky-50 border-sky-100',
        'amber'   => 'text-amber-600 bg-amber-50 border-amber-100',
        'default' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
    ];

    /* ---------- Jendela pagination (maks. 5 nomor) ---------- */
    $current   = $jadwalKegiatan->currentPage();
    $last      = $jadwalKegiatan->lastPage();
    $pageStart = max(1, min($current - 2, $last - 4));
    $pageEnd   = min($last, $pageStart + 4);
    $pageBtn   = 'w-9 h-9 flex items-center justify-center rounded-full transition-all';
@endphp

<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- 1. HERO --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
        <div class="hero-dots absolute inset-0 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <span class="inline-flex items-center gap-2 mb-4 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 shadow-sm text-white/90 text-[10px] font-black uppercase tracking-widest">
                    <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></span>
                    Sistem Monitoring Real-time
                </span>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Agenda Pemeriksaan &amp; Jadwal
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Memantau jadwal Posyandu terkini untuk Anda dan keluarga. Data disinkronkan secara presisi untuk memastikan Anda tidak melewatkan agenda penting dari Bidan atau Kader.
                </p>
            </div>

            <div class="shrink-0 bg-white/20 border border-white/30 backdrop-blur-md rounded-full px-8 py-5 text-center shadow-inner">
                <p class="text-[10px] text-teal-100 font-black uppercase tracking-widest mb-1">Total Agenda</p>
                <p class="text-4xl font-black text-white" id="summary-total">{{ $summary['semua'] ?? 0 }}</p>
            </div>
        </div>
    </section>

    {{-- 2. FILTER SASARAN --}}
    <div id="jadwal-tabs-container">
        @if ($hasMultipleAccess)
            <nav id="jadwal-tabs" aria-label="Filter sasaran agenda"
                 class="flex overflow-x-auto custom-scrollbar px-1 pt-1 pb-2 gap-4 justify-start">
                @foreach ($visibleTabs as $key => $tab)
                    @php $isActive = $filterTarget === $key; @endphp
                    <a href="{{ route('user.jadwal.index', ['filter' => $key]) }}" data-spa
                       @if ($isActive) aria-current="page" @endif
                       class="whitespace-nowrap flex items-center gap-2 px-6 py-3.5 rounded-full border font-black text-xs transition-all
                              {{ $isActive ? $tab['active'] . ' text-white' : 'bg-white text-slate-500 border-slate-200 ' . $tab['hover'] }}">
                        @if ($tab['icon'])
                            <i class="fas {{ $tab['icon'] }} {{ $isActive ? 'text-white' : $tab['icon_color'] }}"></i>
                        @endif
                        {{ $tab['label'] }}
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $isActive ? $tab['badge'] . ' text-white' : 'bg-slate-100 text-slate-500' }}">
                            {{ $summary[$key] ?? 0 }}
                        </span>
                    </a>
                @endforeach
            </nav>
        @elseif (count($specificAccess) === 1)
            @php $single = $tabs[$specificAccess[0]] ?? null; @endphp
            <div class="flex justify-start px-1">
                <span class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white border border-slate-200 text-xs font-black text-slate-600 shadow-sm cursor-default">
                    <i class="fas {{ $single['icon'] ?? 'fa-users' }} text-teal-500 text-base"></i>
                    {{ $single['single'] ?? 'Jadwal Umum' }}
                </span>
            </div>
        @endif
    </div>

    {{-- 3. KARTU JADWAL --}}
    <div class="relative">

        <div id="live-loader" role="status" aria-live="polite"
             class="absolute -top-4 left-2 z-20 flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 backdrop-blur-sm border border-emerald-100 shadow-sm pointer-events-none">
            <span class="flex h-2.5 w-2.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Memperbarui...</span>
        </div>

        <div id="jadwal-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($jadwalCards as $card)
                @php
                    $statusClass = $statusTones[$card['status_tone']] ?? $statusTones['default'];
                    $targetClass = $targetTones[$card['target_tone']] ?? $targetTones['default'];
                    $dateClass   = match (true) {
                        $card['is_today'] => 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20',
                        $card['is_past']  => 'bg-slate-100 text-slate-400',
                        default           => 'bg-slate-50 text-slate-700 border border-slate-200',
                    };
                    $infoRows = [
                        ['icon' => 'far fa-clock',          'label' => 'Waktu',  'value' => $card['waktu']],
                        ['icon' => 'fas fa-map-marker-alt', 'label' => 'Lokasi', 'value' => $card['lokasi']],
                    ];
                @endphp

                <a href="{{ route('user.jadwal.show', $card['id']) }}"
                   class="widget-card group relative flex flex-col h-full overflow-hidden {{ $card['is_past'] ? 'opacity-75 grayscale-[20%]' : '' }}">

                    <span class="absolute top-0 right-0 z-10 px-4 py-1.5 rounded-bl-2xl shadow-sm text-[9px] font-black uppercase tracking-widest {{ $statusClass }}">
                        {{ $card['status_label'] }}
                    </span>

                    <div class="flex flex-col flex-1 p-6">
                        {{-- Tanggal + judul --}}
                        <div class="flex items-start gap-5 mb-6">
                            <div class="w-16 h-16 shrink-0 rounded-2xl flex flex-col items-center justify-center {{ $dateClass }}">
                                <span class="text-[10px] font-black uppercase tracking-widest">{{ $card['bulan'] }}</span>
                                <span class="mt-0.5 text-2xl font-black leading-none">{{ $card['hari'] }}</span>
                            </div>

                            <div class="min-w-0 pt-1 pr-6">
                                <span class="inline-block mb-2.5 px-3 py-1 rounded-full border text-[9px] font-black uppercase tracking-widest {{ $targetClass }}">
                                    {{ $card['target_label'] }}
                                </span>
                                <h3 class="truncate text-[15px] font-black leading-tight text-slate-800 group-hover:text-teal-600 transition-colors" title="{{ $card['judul'] }}">
                                    {{ $card['judul'] }}
                                </h3>
                            </div>
                        </div>

                        {{-- Waktu & lokasi --}}
                        <div class="mt-auto pt-5 space-y-3.5 border-t border-slate-100/80">
                            @foreach ($infoRows as $row)
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 shrink-0 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                                        <i class="{{ $row['icon'] }} text-[11px]"></i>
                                    </div>
                                    <div class="min-w-0 pt-0.5">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ $row['label'] }}</p>
                                        <p class="mt-0.5 truncate text-xs font-bold text-slate-700" title="{{ $row['value'] }}">{{ $row['value'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white/40 backdrop-blur-md rounded-[3rem] border-2 border-dashed border-slate-200">
                    <div class="w-24 h-24 mb-6 flex items-center justify-center rounded-[2rem] bg-white border border-slate-100 shadow-sm text-4xl text-slate-300">
                        <i class="far fa-calendar-times"></i>
                    </div>
                    <h3 class="mb-2 text-2xl font-black text-slate-800">Jadwal Kosong</h3>
                    <p class="max-w-md text-center text-sm font-medium leading-relaxed text-slate-500">
                        Saat ini belum ada agenda Posyandu untuk kategori sasaran yang Anda pilih.
                    </p>
                    @if ($filterTarget !== 'semua' && $hasMultipleAccess)
                        <a href="{{ route('user.jadwal.index') }}" data-spa
                           class="mt-8 px-8 py-4 rounded-full bg-teal-500 hover:bg-teal-600 text-xs font-black text-white shadow-lg shadow-teal-500/30 transition-all hover:-translate-y-1">
                            Kembali ke Semua Jadwal
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    {{-- 4. PAGINATION --}}
    <div id="jadwal-pagination" class="flex justify-center">
        @if ($jadwalKegiatan->hasPages())
            <nav aria-label="Navigasi halaman"
                 class="inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-full bg-white/80 backdrop-blur-md border border-slate-200 shadow-sm">

                @if ($jadwalKegiatan->onFirstPage())
                    <span class="{{ $pageBtn }} bg-slate-100 text-slate-300 cursor-not-allowed" aria-disabled="true">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </span>
                @else
                    <a href="{{ $jadwalKegiatan->previousPageUrl() }}" data-spa aria-label="Halaman sebelumnya"
                       class="{{ $pageBtn }} bg-teal-50 text-teal-600 hover:bg-teal-500 hover:text-white shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                @endif

                @for ($i = $pageStart; $i <= $pageEnd; $i++)
                    @if ($i === $current)
                        <span aria-current="page" class="{{ $pageBtn }} bg-teal-500 text-white shadow-md font-black text-sm scale-110">{{ $i }}</span>
                    @else
                        <a href="{{ $jadwalKegiatan->url($i) }}" data-spa aria-label="Halaman {{ $i }}"
                           class="{{ $pageBtn }} text-slate-500 hover:bg-teal-50 hover:text-teal-600 text-sm font-bold">{{ $i }}</a>
                    @endif
                @endfor

                @if ($jadwalKegiatan->hasMorePages())
                    <a href="{{ $jadwalKegiatan->nextPageUrl() }}" data-spa aria-label="Halaman berikutnya"
                       class="{{ $pageBtn }} bg-teal-50 text-teal-600 hover:bg-teal-500 hover:text-white shadow-sm">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                @else
                    <span class="{{ $pageBtn }} bg-slate-100 text-slate-300 cursor-not-allowed" aria-disabled="true">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </span>
                @endif
            </nav>
        @endif
    </div>

</div>
@endsection

{{-- ============================================================
     SCRIPTS — navigasi tanpa reload + auto-refresh
     ============================================================ --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const POLL_INTERVAL = 30000; // 30 detik
    const SYNC_IDS      = ['jadwal-grid', 'jadwal-tabs-container', 'jadwal-pagination', 'summary-total'];

    const grid   = document.getElementById('jadwal-grid');
    const loader = document.getElementById('live-loader');
    if (!grid) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    let currentUrl = new URL(window.location.href);
    let controller = null;   // request yang sedang berjalan
    let inFlight   = false;
    let lastSync   = Date.now();

    /* ---------- Indikator loading ---------- */
    function setBusy(isBusy, isUserAction) {
        grid.classList.toggle('is-loading', isBusy && isUserAction);
        loader?.classList.toggle('is-visible', isBusy && !isUserAction);
    }

    /* ---------- Salin bagian yang berubah saja ---------- */
    function render(doc) {
        SYNC_IDS.forEach(id => {
            const next    = doc.getElementById(id);
            const current = document.getElementById(id);
            if (next && current && current.innerHTML !== next.innerHTML) {
                current.innerHTML = next.innerHTML;
            }
        });
        lastSync = Date.now();
    }

    /* ---------- Ambil data & perbarui tampilan ---------- */
    async function load(url, { userAction = false } = {}) {
        controller?.abort();                       // batalkan request lama agar tidak saling timpa
        controller = new AbortController();
        const { signal } = controller;

        inFlight = true;
        setBusy(true, userAction);

        try {
            const res = await fetch(url, {
                signal,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            });

            // Sesi habis / diarahkan ke halaman lain (mis. login) -> ikuti redirect
            if (res.redirected && new URL(res.url).pathname !== url.pathname) {
                window.location.assign(res.url);
                return;
            }
            if (!res.ok) throw new Error('HTTP ' + res.status);

            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            if (!doc.getElementById('jadwal-grid')) throw new Error('Respons tidak berisi daftar jadwal');

            render(doc);
        } catch (err) {
            if (err.name === 'AbortError') return;
            console.warn('Gagal memuat jadwal:', err);
            // Klik pengguna tidak boleh "mati": jika AJAX gagal, buka halaman biasa
            if (userAction) window.location.assign(url);
        } finally {
            if (!signal.aborted) {                 // jika dibatalkan, request baru yang mengatur status
                inFlight = false;
                setBusy(false, userAction);
            }
        }
    }

    /* ---------- Klik tab & pagination ---------- */
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[data-spa]');
        if (!link) return;

        // Biarkan browser menangani ctrl/cmd-klik, klik tengah, dan tab baru
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || link.target === '_blank') return;

        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return;

        e.preventDefault();
        if (url.href === currentUrl.href) return;  // sudah di halaman ini

        currentUrl = url;
        window.history.pushState({}, '', url);
        load(url, { userAction: true });

        // Setelah ganti halaman, kembali ke atas daftar
        if (link.closest('#jadwal-pagination')) {
            document.getElementById('jadwal-tabs-container')
                ?.scrollIntoView({ behavior: reduceMotion.matches ? 'auto' : 'smooth', block: 'start' });
        }
    });

    /* ---------- Tombol back/forward browser ---------- */
    window.addEventListener('popstate', () => {
        currentUrl = new URL(window.location.href);
        load(currentUrl, { userAction: true });
    });

    /* ---------- Auto-refresh (hanya saat tab aktif) ---------- */
    setInterval(() => {
        if (!document.hidden && !inFlight) load(currentUrl);
    }, POLL_INTERVAL);

    // Saat kembali ke tab, segarkan jika data sudah lama
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !inFlight && Date.now() - lastSync > POLL_INTERVAL) load(currentUrl);
    });
});
</script>
@endpush