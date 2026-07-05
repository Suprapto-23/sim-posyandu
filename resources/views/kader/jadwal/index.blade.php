@extends('layouts.kader')

@section('title', 'Agenda Posyandu')
@section('page-name', 'Agenda Posyandu')
@section('page-title', 'Agenda Posyandu')

@section('content')
@php
    $filters = $filters ?? [
        'search' => request('search', ''),
        'kategori' => request('kategori', 'semua'),
        'periode' => request('periode', 'semua'),
    ];

    $stats = $stats ?? [
        'semua' => 0,
        'aktif' => 0,
        'mendatang' => 0,
        'selesai' => 0,
    ];

    $initialPayload = $initialPayload ?? [
        'items' => [],
        'stats' => $stats,
        'latest_id' => 0,
        'hash' => '',
        'server_time' => now('Asia/Jakarta')->format('H:i:s'),
        'unread_jadwal_notifikasi' => 0,
    ];

    $kategoriOptions = [
        'semua' => 'Semua Kategori',
        'posyandu' => 'Posyandu Rutin',
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        'imunisasi' => 'Imunisasi',
        'pemeriksaan' => 'Pemeriksaan',
        'lainnya' => 'Lainnya',
    ];

    $periodeOptions = [
        'semua' => 'Semua Periode',
        'hari_ini' => 'Hari Ini',
        'minggu_ini' => 'Minggu Ini',
        'bulan_ini' => 'Bulan Ini',
        'mendatang' => 'Mendatang',
        'selesai' => 'Selesai',
    ];

    // Optimasi Filter Count
    $activeFilterCount = collect($filters)
        ->filter(fn($val, $key) => $key === 'search' ? filled($val) : $val !== 'semua')
        ->count();
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
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.95); }

    /* Live List Transitions */
    .kj-live-list { transition: opacity .25s ease, transform .25s ease; will-change: opacity, transform; }
    .kj-live-list.is-loading { opacity: 0.4; transform: translateY(4px); pointer-events: none; }

    /* Custom Search Cancel Button */
    input[type="text"]::-webkit-search-cancel-button {
        -webkit-appearance: none;
        height: 14px; width: 14px;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%2394a3b8"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>') no-repeat 50% 50%;
        cursor: pointer; opacity: 0.7;
    }
    input[type="text"]::-webkit-search-cancel-button:hover { opacity: 1; }

    .animate-fade-in { animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Hilangkan scrollbar di menu dropdown */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Toast */
    .kj-toast {
        opacity: 0; pointer-events: none;
        transform: translateY(20px) scale(0.95);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, opacity;
    }
    .kj-toast.show { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
</style>
@endpush

<div class="relative z-10 mx-auto max-w-[1180px] pb-16 animate-fade-in space-y-6 pt-6">

    {{-- 1. HERO WIDGET --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-6 sm:p-10 shadow-2xl shadow-teal-500/30 flex flex-col xl:flex-row justify-between items-center gap-8 border-[4px] border-white/50">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-50 pointer-events-none"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent pointer-events-none"></div>
        
        <div class="relative z-10 w-full xl:w-1/2 flex flex-col gap-4 text-center xl:text-left">
            <div class="inline-flex justify-center xl:justify-start items-center gap-2 mb-1">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse relative"></span>
                    Mode Baca Saja
                </span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight drop-shadow-md">
                Jadwal Posyandu.
            </h1>

            <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-lg mx-auto xl:mx-0 drop-shadow-sm">
                Agenda dari Bidan akan tampil otomatis di halaman ini. Kader cukup memantau jadwal, target sasaran, lokasi, dan persiapan layanan.
            </p>

            <div class="flex flex-wrap justify-center xl:justify-start gap-3 mt-2">
                <a href="{{ route('kader.dashboard') }}" class="btn-pill bg-white text-teal-600 hover:text-teal-800 hover:bg-teal-50 px-6 py-3 text-sm font-bold shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="relative z-10 w-full xl:w-auto flex flex-col sm:flex-row gap-4 justify-center">
            <div class="widget-card !rounded-[2.5rem] !bg-white/90 p-5 flex items-center gap-4 min-w-[220px]">
                <div class="w-12 h-12 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center text-xl shadow-inner shrink-0">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Agenda</p>
                    <p class="text-2xl font-black text-slate-800" id="stat-semua">{{ $stats['semua'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. METRIK STATUS GRID --}}
    <section class="grid gap-4 sm:grid-cols-3">
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Jadwal Aktif</p>
                <p class="mt-1 text-3xl font-black text-slate-800" id="stat-aktif">{{ $stats['aktif'] ?? 0 }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-500 flex items-center justify-center text-2xl shadow-inner group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>
        
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Mendatang</p>
                <p class="mt-1 text-3xl font-black text-slate-800" id="stat-mendatang">{{ $stats['mendatang'] ?? 0 }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-sky-50 border border-sky-100 text-sky-500 flex items-center justify-center text-2xl shadow-inner group-hover:-rotate-6 transition-transform">
                <i class="fa-solid fa-calendar-plus"></i>
            </div>
        </div>

        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Selesai</p>
                <p class="mt-1 text-3xl font-black text-slate-800" id="stat-selesai">{{ $stats['selesai'] ?? 0 }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-100 text-amber-500 flex items-center justify-center text-2xl shadow-inner group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
    </section>

    {{-- 3. FILTER WIDGET (DROPDOWN SUDAH CUSTOM TAILWIND) --}}
    <form method="GET" action="{{ route('kader.jadwal.index') }}" id="filterForm" class="widget-card p-4 sm:p-6 flex flex-col lg:flex-row gap-4 items-center z-20 relative">
        
        {{-- Pencarian --}}
        <div class="w-full lg:flex-1 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 z-10"></i>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari judul, lokasi, atau kategori..." aria-label="Pencarian" class="w-full btn-pill border border-slate-200 bg-white/80 py-3.5 pl-11 pr-4 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10 shadow-inner">
        </div>

        {{-- Custom Dropdown Kategori --}}
        <div class="relative w-full lg:w-48 custom-dropdown-container">
            <input type="hidden" name="kategori" id="kategoriInput" value="{{ $filters['kategori'] ?? 'semua' }}">
            
            <button type="button" onclick="toggleCustomDropdown('menuKategori', 'iconKategori')" class="w-full btn-pill border border-slate-200 bg-white/80 px-4 py-3.5 text-sm font-bold text-slate-700 outline-none transition focus:bg-white focus:border-teal-400 shadow-inner flex justify-between items-center cursor-pointer">
                <span id="textKategori">{{ $kategoriOptions[$filters['kategori'] ?? 'semua'] ?? 'Semua Kategori' }}</span>
                <i id="iconKategori" class="fas fa-chevron-down text-xs text-slate-400 transition-transform duration-200 custom-dropdown-icon"></i>
            </button>
            
            <div id="menuKategori" class="custom-dropdown-menu absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 opacity-0 invisible transform scale-95 transition-all duration-200 origin-top">
                <div class="p-2 flex flex-col gap-1 max-h-64 overflow-y-auto no-scrollbar">
                    @foreach($kategoriOptions as $value => $label)
                        <button type="button" onclick="selectDropdownOption('kategori', '{{ $value }}')" class="w-full text-left px-3 py-2.5 rounded-xl text-sm transition-colors flex items-center justify-between {{ ($filters['kategori'] ?? 'semua') === $value ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-slate-900' }}">
                            <span>{{ $label }}</span>
                            @if(($filters['kategori'] ?? 'semua') === $value)
                                <i class="fas fa-check text-teal-500 text-xs"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Custom Dropdown Periode --}}
        <div class="relative w-full lg:w-48 custom-dropdown-container">
            <input type="hidden" name="periode" id="periodeInput" value="{{ $filters['periode'] ?? 'semua' }}">
            
            <button type="button" onclick="toggleCustomDropdown('menuPeriode', 'iconPeriode')" class="w-full btn-pill border border-slate-200 bg-white/80 px-4 py-3.5 text-sm font-bold text-slate-700 outline-none transition focus:bg-white focus:border-teal-400 shadow-inner flex justify-between items-center cursor-pointer">
                <span id="textPeriode">{{ $periodeOptions[$filters['periode'] ?? 'semua'] ?? 'Semua Periode' }}</span>
                <i id="iconPeriode" class="fas fa-chevron-down text-xs text-slate-400 transition-transform duration-200 custom-dropdown-icon"></i>
            </button>
            
            <div id="menuPeriode" class="custom-dropdown-menu absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 opacity-0 invisible transform scale-95 transition-all duration-200 origin-top">
                <div class="p-2 flex flex-col gap-1 max-h-64 overflow-y-auto no-scrollbar">
                    @foreach($periodeOptions as $value => $label)
                        <button type="button" onclick="selectDropdownOption('periode', '{{ $value }}')" class="w-full text-left px-3 py-2.5 rounded-xl text-sm transition-colors flex items-center justify-between {{ ($filters['periode'] ?? 'semua') === $value ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-slate-900' }}">
                            <span>{{ $label }}</span>
                            @if(($filters['periode'] ?? 'semua') === $value)
                                <i class="fas fa-check text-teal-500 text-xs"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex gap-2 w-full lg:w-auto">
            <button type="submit" class="flex-1 lg:flex-none btn-pill bg-slate-800 hover:bg-slate-700 px-6 py-3.5 text-sm font-bold text-white shadow-md transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            @if($activeFilterCount > 0)
                <a href="{{ route('kader.jadwal.index') }}" class="btn-pill border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 w-12 flex items-center justify-center text-slate-400 shadow-sm transition" aria-label="Reset Filter">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- 4. DAFTAR AGENDA (Realtime Area) --}}
    <section class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between px-2">
            <div>
                <h2 class="text-lg font-black text-slate-900">Daftar Agenda</h2>
                <p id="list-subtitle" class="mt-1 text-xs font-semibold text-slate-500">
                    Mensinkronisasi data dengan server...
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="btn-pill inline-flex items-center gap-2 border border-emerald-200 bg-emerald-50 px-4 py-2 text-[10px] font-black uppercase tracking-wider text-emerald-700 shadow-sm">
                    <span id="live-dot" class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="live-text">Realtime aktif</span>
                </div>

                <div class="btn-pill inline-flex items-center gap-2 border border-sky-200 bg-sky-50 px-4 py-2 text-[10px] font-black uppercase tracking-wider text-sky-700 shadow-sm">
                    <i class="fas fa-clock"></i>
                    <span id="server-time">{{ $initialPayload['server_time'] ?? '-' }}</span>
                </div>

                <div id="notif-pill" class="hidden items-center gap-2 btn-pill border border-amber-200 bg-amber-50 px-4 py-2 text-[10px] font-black uppercase tracking-wider text-amber-700 shadow-sm">
                    <i class="fas fa-bell animate-bounce"></i>
                    <span id="notif-count">0</span>
                </div>
            </div>
        </div>

        {{-- Grid Wrapper --}}
        <div id="jadwal-card-list" class="kj-live-list grid gap-6 md:grid-cols-2 lg:grid-cols-3 items-stretch"></div>

        {{-- Pagination Container --}}
        <div id="jadwal-pagination" class="w-full"></div>

        {{-- Empty State --}}
        <div id="jadwal-empty" class="hidden widget-card p-10 text-center flex-col items-center justify-center min-h-[300px]">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-3xl text-slate-400 shadow-inner mb-5">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3 class="text-xl font-black text-slate-800">Jadwal Tidak Ditemukan</h3>
            <p class="mt-2 max-w-md text-sm font-medium text-slate-500">
                Belum ada agenda Posyandu yang sesuai dengan filter.
            </p>
            <a href="{{ route('kader.jadwal.index') }}" class="btn-pill mt-6 bg-teal-50 hover:bg-teal-500 hover:text-white text-teal-600 border border-teal-200 px-6 py-2.5 text-xs font-bold uppercase tracking-wider transition-colors shadow-sm">
                Reset Filter
            </a>
        </div>
    </section>
</div>

{{-- TOAST NOTIFICATION --}}
<div id="jadwal-toast" class="kj-toast fixed bottom-6 right-6 z-50 w-full max-w-sm" role="alert">
    <div class="widget-card p-4 flex gap-4 items-center bg-white/95 border border-emerald-200">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 shadow-inner">
            <i class="fas fa-bell text-xl"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-black text-slate-900">Jadwal Diperbarui</p>
            <p id="jadwal-toast-text" class="mt-0.5 text-xs font-semibold leading-5 text-slate-500">
                Agenda dari Bidan sudah masuk ke daftar.
            </p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// --- LOGIKA KENDALI CUSTOM DROPDOWN ---
window.toggleCustomDropdown = function(menuId, iconId) {
    // 1. Tutup semua dropdown lain
    document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
        if (menu.id !== menuId) {
            menu.classList.add('invisible', 'opacity-0', 'scale-95');
            menu.classList.remove('opacity-100', 'scale-100');
        }
    });
    document.querySelectorAll('.custom-dropdown-icon').forEach(icon => {
        if (icon.id !== iconId) icon.classList.remove('rotate-180');
    });

    // 2. Buka/Tutup dropdown yang diklik
    const menu = document.getElementById(menuId);
    const icon = document.getElementById(iconId);
    
    if (menu.classList.contains('invisible')) {
        menu.classList.remove('invisible', 'opacity-0', 'scale-95');
        menu.classList.add('opacity-100', 'scale-100');
        icon.classList.add('rotate-180');
    } else {
        menu.classList.add('invisible', 'opacity-0', 'scale-95');
        menu.classList.remove('opacity-100', 'scale-100');
        icon.classList.remove('rotate-180');
    }
};

// Fungsi saat opsi dipilih
window.selectDropdownOption = function(inputName, value) {
    // Set input hidden ke value terbaru
    const form = document.getElementById('filterForm');
    form.querySelector(`input[name="${inputName}"]`).value = value;
    
    // Auto-Submit Filter untuk mempermudah UX
    form.submit(); 
};

// Tutup dropdown jika klik diluar area
document.addEventListener('click', (e) => {
    if (!e.target.closest('.custom-dropdown-container')) {
        document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
            menu.classList.add('invisible', 'opacity-0', 'scale-95');
            menu.classList.remove('opacity-100', 'scale-100');
        });
        document.querySelectorAll('.custom-dropdown-icon').forEach(icon => {
            icon.classList.remove('rotate-180');
        });
    }
});


// --- LOGIKA REALTIME JADWAL & PAGINATION (Tetap Teroptimasi) ---
document.addEventListener('DOMContentLoaded', () => {
    const initialPayload = @json($initialPayload);
    const liveUrl = @json(route('kader.jadwal.live'));
    const lastSeenKey = 'posyandu_kader_last_seen_jadwal_id';

    const DOM = {
        list: document.getElementById('jadwal-card-list'),
        empty: document.getElementById('jadwal-empty'),
        pagination: document.getElementById('jadwal-pagination'),
        subtitle: document.getElementById('list-subtitle'),
        liveText: document.getElementById('live-text'),
        liveDot: document.getElementById('live-dot'),
        serverTime: document.getElementById('server-time'),
        toast: document.getElementById('jadwal-toast'),
        toastText: document.getElementById('jadwal-toast-text'),
        notifPill: document.getElementById('notif-pill'),
        notifCount: document.getElementById('notif-count'),
        filterForm: document.getElementById('filterForm')
    };

    let isFetching = false;
    let currentHash = initialPayload?.hash || '';
    let lastSeenId = Number(localStorage.getItem(lastSeenKey) || 0);
    
    let currentPage = 1;
    const itemsPerPage = 6;
    let currentItems = [];
    
    let pollInterval = 4000; 
    let pollTimer = null;
    let abortController = null;

    if (!lastSeenId && initialPayload?.latest_id) {
        lastSeenId = Number(initialPayload.latest_id);
        localStorage.setItem(lastSeenKey, String(lastSeenId));
    }

    const escapeHtml = (text) => {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    };

    const uiMap = {
        kategori: {
            balita: { c: 'bg-emerald-50 text-emerald-700 border-emerald-200', i: 'fa-child-reaching' },
            remaja: { c: 'bg-teal-50 text-teal-700 border-teal-200', i: 'fa-user-graduate' },
            lansia: { c: 'bg-sky-50 text-sky-700 border-sky-200', i: 'fa-person-cane' },
            imunisasi: { c: 'bg-indigo-50 text-indigo-700 border-indigo-200', i: 'fa-syringe' },
            pemeriksaan: { c: 'bg-violet-50 text-violet-700 border-violet-200', i: 'fa-stethoscope' },
            posyandu: { c: 'bg-rose-50 text-rose-700 border-rose-200', i: 'fa-calendar-check' },
            default: { c: 'bg-slate-50 text-slate-700 border-slate-200', i: 'fa-layer-group' }
        },
        status: {
            aktif: { c: 'bg-emerald-50 text-emerald-700 border-emerald-200', d: 'bg-emerald-500' },
            selesai: { c: 'bg-slate-100 text-slate-500 border-slate-200', d: 'bg-slate-400' },
            dibatalkan: { c: 'bg-rose-50 text-rose-700 border-rose-200', d: 'bg-rose-500' },
            default: { c: 'bg-amber-50 text-amber-700 border-amber-200', d: 'bg-amber-500' }
        }
    };

    const cardTemplate = (item) => {
        const cat = uiMap.kategori[item.kategori] || uiMap.kategori.default;
        const stat = uiMap.status[item.status] || uiMap.status.default;
        
        return `
            <article class="widget-card p-5 sm:p-6 flex flex-col h-full border border-slate-200">
                <div class="flex items-start gap-4 mb-5 border-b border-slate-100 pb-5">
                    <div class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center text-2xl shadow-sm border bg-white ${cat.c}">
                        <i class="fas ${cat.i}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm ${stat.c}">
                                <span class="h-2 w-2 rounded-full ${stat.d}"></span>
                                ${escapeHtml(item.status_label)}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border border-slate-200 text-slate-600 bg-slate-50 shadow-sm">
                                ${escapeHtml(item.kategori_label)}
                            </span>
                        </div>
                        <h3 class="line-clamp-2 text-lg font-black text-slate-900 leading-tight">${escapeHtml(item.judul)}</h3>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-5 text-xs font-bold text-slate-600">
                    <div class="bg-slate-50/50 border border-slate-100 rounded-xl p-3 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Tanggal</p>
                        <p class="text-slate-800">${escapeHtml(item.tanggal_label)}</p>
                    </div>
                    <div class="bg-slate-50/50 border border-slate-100 rounded-xl p-3 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Waktu</p>
                        <p class="text-slate-800">${escapeHtml(item.waktu_label)}</p>
                    </div>
                    <div class="bg-slate-50/50 border border-slate-100 rounded-xl p-3 col-span-2 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Lokasi</p>
                        <p class="text-slate-800 line-clamp-1">${escapeHtml(item.lokasi)}</p>
                    </div>
                </div>
                
                <p class="text-xs font-medium text-slate-500 line-clamp-3 mb-6 leading-relaxed flex-1 min-h-[3.5rem]">${escapeHtml(item.deskripsi)}</p>
                
                <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[10px] font-black uppercase tracking-wider text-teal-600 max-w-[50%] truncate bg-teal-50 border border-teal-100 px-2.5 py-1.5 rounded-md shadow-sm" title="${escapeHtml(item.target_label)}">
                        <i class="fas fa-users mr-1"></i> ${escapeHtml(item.target_label)}
                    </p>
                    <a href="${escapeHtml(item.show_url)}" class="btn-pill bg-slate-800 text-white px-6 py-2.5 text-[11px] font-black uppercase tracking-wider hover:bg-teal-600 shadow-md">Detail</a>
                </div>
            </article>
        `;
    };

    const setStatus = (text, mode = 'active') => {
        if (DOM.liveText) DOM.liveText.textContent = text;
        if (!DOM.liveDot) return;
        const color = mode === 'active' ? 'bg-emerald-500' : mode === 'loading' ? 'bg-amber-500' : 'bg-rose-500';
        DOM.liveDot.className = `h-2 w-2 rounded-full ${color} ${mode === 'active' ? 'animate-pulse' : ''}`;
    };

    const showToast = (message) => {
        if (!DOM.toast) return;
        if (DOM.toastText) DOM.toastText.textContent = message || 'Agenda dari Bidan sudah masuk ke daftar.';
        DOM.toast.classList.add('show');
        setTimeout(() => DOM.toast.classList.remove('show'), 4200);
    };

    const renderPaginationControls = (totalItems, totalPages) => {
        if (totalPages <= 1) {
            DOM.pagination.innerHTML = '';
            return;
        }

        let html = `
            <div class="px-2 py-6 mt-2 flex flex-col sm:flex-row items-center justify-between gap-4 w-full border-t border-slate-100/50">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Halaman <span class="text-slate-900">${currentPage}</span> dari <span class="text-slate-900">${totalPages}</span>
                </p>
                <div class="flex items-center gap-2">
        `;
        
        if (currentPage === 1) {
            html += `<button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60"><i class="fas fa-chevron-left text-xs"></i></button>`;
        } else {
            html += `<button type="button" data-page="${currentPage - 1}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600"><i class="fas fa-chevron-left text-xs"></i></button>`;
        }

        let windowSize = 2; 
        let start = Math.max(1, currentPage - windowSize);
        let end = Math.min(totalPages, currentPage + windowSize);

        if (currentPage <= windowSize) end = Math.min(totalPages, 1 + (windowSize * 2));
        if (currentPage > totalPages - windowSize) start = Math.max(1, totalPages - (windowSize * 2));

        for (let i = start; i <= end; i++) {
            if (i === currentPage) {
                html += `<span class="btn-pill w-10 h-10 flex items-center justify-center bg-emerald-500 text-white font-black text-sm shadow-md pointer-events-none">${i}</span>`;
            } else {
                html += `<button type="button" data-page="${i}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-sm shadow-sm hover:bg-emerald-50 hover:text-emerald-600">${i}</button>`;
            }
        }

        if (currentPage === totalPages) {
            html += `<button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60"><i class="fas fa-chevron-right text-xs"></i></button>`;
        } else {
            html += `<button type="button" data-page="${currentPage + 1}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600"><i class="fas fa-chevron-right text-xs"></i></button>`;
        }

        html += `</div></div>`;
        DOM.pagination.innerHTML = html;
    };

    const renderListContent = () => {
        const totalItems = currentItems.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (currentPage > totalPages) currentPage = totalPages || 1;
        if (currentPage < 1) currentPage = 1;

        if (totalItems === 0) {
            DOM.list.innerHTML = '';
            DOM.empty.classList.replace('hidden', 'flex');
            DOM.pagination.innerHTML = '';
            DOM.subtitle.textContent = 'Tidak ada jadwal yang ditampilkan.';
            return;
        }

        DOM.empty.classList.replace('flex', 'hidden');

        const startIdx = (currentPage - 1) * itemsPerPage;
        const endIdx = startIdx + itemsPerPage;
        const paginatedItems = currentItems.slice(startIdx, endIdx);

        window.requestAnimationFrame(() => {
            DOM.list.innerHTML = paginatedItems.map(cardTemplate).join('');
            const endDisplay = Math.min(endIdx, totalItems);
            DOM.subtitle.innerHTML = `Menampilkan <strong class="text-slate-800">${startIdx + 1}-${endDisplay}</strong> dari <strong class="text-slate-800">${totalItems}</strong> agenda tersinkronisasi.`;
            renderPaginationControls(totalItems, totalPages);
        });
    };

    const renderPayload = (payload, animate = true) => {
        currentItems = Array.isArray(payload?.items) ? payload.items : [];

        const stats = payload?.stats || {};
        ['semua', 'aktif', 'mendatang', 'selesai'].forEach(k => {
            const el = document.getElementById(`stat-${k}`);
            if (el) el.textContent = stats[k] ?? 0;
        });

        const unreadCount = Number(payload?.unread_jadwal_notifikasi || 0);
        if (DOM.notifCount) DOM.notifCount.textContent = unreadCount;
        if (DOM.notifPill) DOM.notifPill.classList.toggle('hidden', unreadCount === 0);
        if (DOM.notifPill) DOM.notifPill.classList.toggle('inline-flex', unreadCount > 0);
        
        if (DOM.serverTime && payload?.server_time) DOM.serverTime.textContent = payload.server_time;

        if (!DOM.list) return;

        if (animate) DOM.list.classList.add('is-loading');
        
        setTimeout(() => {
            renderListContent();
            if (animate) DOM.list.classList.remove('is-loading');
        }, animate ? 150 : 0);
    };

    const refreshJadwal = async (manual = false) => {
        if (isFetching) return;
        isFetching = true;

        if (manual) setStatus('Memeriksa jadwal...', 'loading');

        if (abortController) abortController.abort();
        abortController = new AbortController();

        try {
            const url = new URL(liveUrl, window.location.origin);
            new URLSearchParams(window.location.search).forEach((v, k) => url.searchParams.set(k, v));
            url.searchParams.set('_r', Date.now().toString());

            const response = await fetch(url, {
                method: 'GET',
                signal: abortController.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });

            if (!response.ok) throw new Error('Fetch failed');

            const payload = await response.json();
            const newestId = Number(payload?.latest_id || 0);

            if (payload.hash !== currentHash) {
                if (newestId > lastSeenId && lastSeenId > 0) showToast('Bidan menambahkan atau memperbarui jadwal Posyandu.');
                renderPayload(payload, true);
                currentHash = payload.hash || '';
            } else {
                if (DOM.serverTime && payload?.server_time) DOM.serverTime.textContent = payload.server_time;
                const unread = Number(payload?.unread_jadwal_notifikasi || 0);
                if (DOM.notifCount) DOM.notifCount.textContent = unread;
                if (DOM.notifPill) DOM.notifPill.classList.toggle('hidden', unread === 0);
                if (DOM.notifPill) DOM.notifPill.classList.toggle('inline-flex', unread > 0);
            }

            if (newestId > lastSeenId) {
                lastSeenId = newestId;
                localStorage.setItem(lastSeenKey, String(lastSeenId));
            }

            setStatus('Realtime aktif', 'active');
        } catch (error) {
            if (error.name !== 'AbortError') setStatus('Koneksi terputus', 'error');
        } finally {
            isFetching = false;
        }
    };

    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-page]');
        if (btn) {
            currentPage = parseInt(btn.dataset.page);
            DOM.list.classList.add('is-loading');
            setTimeout(() => {
                renderListContent();
                DOM.list.classList.remove('is-loading');
            }, 150);
        }
    });

    if (DOM.filterForm) {
        DOM.filterForm.addEventListener('submit', () => currentPage = 1);
        DOM.filterForm.addEventListener('input', () => currentPage = 1);
    }

    const startPolling = () => {
        if(pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(() => refreshJadwal(false), pollInterval);
    };
    const stopPolling = () => { if(pollTimer) clearInterval(pollTimer); };

    window.addEventListener('focus', () => { refreshJadwal(true); startPolling(); });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stopPolling();
        else { refreshJadwal(true); startPolling(); }
    });

    renderPayload(initialPayload, false);
    setTimeout(() => refreshJadwal(false), 500); 
    startPolling();
});
</script>
@endpush