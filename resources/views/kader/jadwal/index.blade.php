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

    $activeFilterCount = collect([
        $filters['search'], 
        $filters['kategori'] !== 'semua' ? $filters['kategori'] : null, 
        $filters['periode'] !== 'semua' ? $filters['periode'] : null
    ])->filter(fn ($value) => filled($value))->count();
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

    /* Live List Transitions */
    .kj-live-list {
        transition: opacity .3s ease, transform .3s ease;
    }
    .kj-live-list.is-loading {
        opacity: .5;
        transform: translateY(4px);
    }

    /* Custom Search Cancel Button */
    input[type="text"]::-webkit-search-cancel-button {
        -webkit-appearance: none;
        height: 14px;
        width: 14px;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%2394a3b8"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>') no-repeat 50% 50%;
        cursor: pointer;
        opacity: 0.7;
    }
    input[type="text"]::-webkit-search-cancel-button:hover { opacity: 1; }

    .animate-fade-in {
        animation: fadeIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Toast */
    .kj-toast {
        opacity: 0;
        pointer-events: none;
        transform: translateY(20px) scale(0.95);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .kj-toast.show {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }
</style>
@endpush

{{-- PERBAIKAN WRAPPER: Menggunakan struktur natural agar tidak tertabrak sidebar --}}
<div class="relative z-10 mx-auto max-w-[1180px] pb-16 animate-fade-in space-y-6 pt-6">

    {{-- 1. HERO WIDGET --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-6 sm:p-10 shadow-2xl shadow-teal-500/30 flex flex-col xl:flex-row justify-between items-center gap-8 border-[4px] border-white/50">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-50"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
        
        <div class="relative z-10 w-full xl:w-1/2 flex flex-col gap-4 text-center xl:text-left">
            <div class="inline-flex justify-center xl:justify-start items-center gap-2 mb-1">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse relative"></span>
                    Mode Baca Saja (Read-Only)
                </span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight drop-shadow-md">
                Jadwal Posyandu.
            </h1>

            <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-lg mx-auto xl:mx-0 drop-shadow-sm">
                Agenda dari Bidan akan tampil otomatis di halaman ini. Kader cukup memantau jadwal, target sasaran, lokasi, dan persiapan layanan.
            </p>

            <div class="flex flex-wrap justify-center xl:justify-start gap-3 mt-2">
                <a href="{{ route('kader.dashboard') }}" class="btn-pill bg-white text-teal-600 hover:text-teal-800 hover:bg-teal-50 px-6 py-3 text-sm font-bold shadow-[0_8px_20px_rgba(255,255,255,0.3)] flex items-center gap-2 transition-all">
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

    {{-- 3. FILTER WIDGET --}}
    <form method="GET" action="{{ route('kader.jadwal.index') }}" id="filterForm" class="widget-card p-4 sm:p-6 flex flex-col lg:flex-row gap-4 items-center z-20 relative">
        <div class="w-full lg:flex-1 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 z-10"></i>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari judul, lokasi, atau kategori..." class="w-full btn-pill border border-slate-200 bg-white/80 py-3.5 pl-11 pr-4 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10 shadow-inner">
        </div>

        <select name="kategori" class="w-full lg:w-48 btn-pill border border-slate-200 bg-white/80 px-4 py-3.5 text-sm font-bold text-slate-700 outline-none transition focus:bg-white focus:border-teal-400 shadow-inner appearance-none cursor-pointer">
            @foreach($kategoriOptions as $value => $label)
                <option value="{{ $value }}" @selected(($filters['kategori'] ?? 'semua') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="periode" class="w-full lg:w-48 btn-pill border border-slate-200 bg-white/80 px-4 py-3.5 text-sm font-bold text-slate-700 outline-none transition focus:bg-white focus:border-teal-400 shadow-inner appearance-none cursor-pointer">
            @foreach($periodeOptions as $value => $label)
                <option value="{{ $value }}" @selected(($filters['periode'] ?? 'semua') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="flex gap-2 w-full lg:w-auto">
            <button type="submit" class="flex-1 lg:flex-none btn-pill bg-slate-800 hover:bg-slate-700 px-6 py-3.5 text-sm font-bold text-white shadow-md transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <div id="manualFilterContainer">
                @if($activeFilterCount > 0)
                    <a href="{{ route('kader.jadwal.index') }}" class="btn-pill border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 w-12 flex items-center justify-center text-slate-400 shadow-sm transition" title="Bersihkan filter">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- 4. DAFTAR AGENDA (Realtime Area dengan Pagination) --}}
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
                    <span id="notif-count">0</span> notifikasi
                </div>
            </div>
        </div>

        {{-- Grid Wrapper --}}
        <div id="jadwal-card-list" class="kj-live-list grid gap-6 md:grid-cols-2 lg:grid-cols-3 items-stretch"></div>

        {{-- Pagination Container --}}
        <div id="jadwal-pagination" class="flex flex-wrap items-center justify-center gap-2 mt-8"></div>

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
<div id="jadwal-toast" class="kj-toast fixed bottom-6 right-6 z-50 w-full max-w-sm">
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
document.addEventListener('DOMContentLoaded', () => {
    const initialPayload = @json($initialPayload);
    const liveUrl = @json(route('kader.jadwal.live'));
    const lastSeenKey = 'posyandu_kader_last_seen_jadwal_id';

    const list = document.getElementById('jadwal-card-list');
    const empty = document.getElementById('jadwal-empty');
    const pagination = document.getElementById('jadwal-pagination');
    const subtitle = document.getElementById('list-subtitle');
    
    const liveText = document.getElementById('live-text');
    const liveDot = document.getElementById('live-dot');
    const serverTime = document.getElementById('server-time');
    const toast = document.getElementById('jadwal-toast');
    const toastText = document.getElementById('jadwal-toast-text');
    const notifPill = document.getElementById('notif-pill');
    const notifCount = document.getElementById('notif-count');

    let isFetching = false;
    let currentHash = initialPayload?.hash || '';
    let lastSeenId = Number(localStorage.getItem(lastSeenKey) || 0);

    // Variabel Pagination Client-side
    let currentPage = 1;
    const itemsPerPage = 6;
    let currentItems = [];

    if (!lastSeenId && initialPayload?.latest_id) {
        lastSeenId = Number(initialPayload.latest_id);
        localStorage.setItem(lastSeenKey, String(lastSeenId));
    }

    const escapeHtml = (value) => {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    };

    const kategoriClass = (kategori) => {
        const map = {
            balita: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            remaja: 'bg-teal-50 text-teal-700 border-teal-200',
            lansia: 'bg-sky-50 text-sky-700 border-sky-200',
            imunisasi: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            pemeriksaan: 'bg-violet-50 text-violet-700 border-violet-200',
            lainnya: 'bg-slate-50 text-slate-700 border-slate-200',
            posyandu: 'bg-rose-50 text-rose-700 border-rose-200'
        };
        return map[kategori] || map.posyandu;
    };

    const kategoriIcon = (kategori) => {
        const map = {
            balita: 'fa-child-reaching',
            remaja: 'fa-user-graduate',
            lansia: 'fa-person-cane',
            imunisasi: 'fa-syringe',
            pemeriksaan: 'fa-stethoscope',
            lainnya: 'fa-layer-group',
            posyandu: 'fa-calendar-check'
        };
        return map[kategori] || map.posyandu;
    };

    const statusClass = (status) => {
        const map = {
            aktif: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            selesai: 'bg-slate-100 text-slate-500 border-slate-200',
            dibatalkan: 'bg-rose-50 text-rose-700 border-rose-200',
            terjadwal: 'bg-amber-50 text-amber-700 border-amber-200'
        };
        return map[status] || map.terjadwal;
    };

    const statusDot = (status) => {
        const map = {
            aktif: 'bg-emerald-500',
            selesai: 'bg-slate-400',
            dibatalkan: 'bg-rose-500',
            terjadwal: 'bg-amber-500'
        };
        return map[status] || map.terjadwal;
    };

    const cardTemplate = (item) => {
        return `
            <article class="widget-card p-5 sm:p-6 flex flex-col h-full hover:-translate-y-1 transition-transform border border-slate-200">
                <div class="flex items-start gap-4 mb-5 border-b border-slate-100 pb-5">
                    <div class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center text-2xl shadow-sm border bg-white ${kategoriClass(item.kategori)}">
                        <i class="fas ${kategoriIcon(item.kategori)}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm ${statusClass(item.status)}">
                                <span class="h-2 w-2 rounded-full ${statusDot(item.status)}"></span>
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
                    <a href="${escapeHtml(item.show_url)}" class="btn-pill bg-slate-800 text-white px-6 py-2.5 text-[11px] font-black uppercase tracking-wider hover:bg-teal-600 transition-colors shadow-md">Detail</a>
                </div>
            </article>
        `;
    };

    const setStatus = (text, mode = 'active') => {
        if (liveText) liveText.textContent = text;
        if (!liveDot) return;
        const color = mode === 'active' ? 'bg-emerald-500' : mode === 'loading' ? 'bg-amber-500' : 'bg-rose-500';
        liveDot.className = `h-2 w-2 rounded-full ${color} ${mode === 'active' ? 'animate-pulse' : ''}`;
    };

    const showToast = (message) => {
        if (!toast) return;
        if (toastText) toastText.textContent = message || 'Agenda dari Bidan sudah masuk ke daftar.';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4200);
    };

    const updateStats = (stats) => {
        const pairs = {
            'stat-semua': stats?.semua ?? 0,
            'stat-aktif': stats?.aktif ?? 0,
            'stat-mendatang': stats?.mendatang ?? 0,
            'stat-selesai': stats?.selesai ?? 0,
        };
        Object.entries(pairs).forEach(([id, value]) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        });
    };

    const updateNotif = (count) => {
        const total = Number(count || 0);
        if (notifCount) notifCount.textContent = total;
        if (!notifPill) return;

        if (total > 0) {
            notifPill.classList.remove('hidden');
            notifPill.classList.add('inline-flex');
        } else {
            notifPill.classList.add('hidden');
            notifPill.classList.remove('inline-flex');
        }
    };

    // Fungsi Render Pagination Controls
    const renderPaginationControls = (totalItems, totalPages) => {
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let html = '';
        
        // Prev Button
        html += `<button type="button" data-page="${currentPage - 1}" class="btn-pill w-10 h-10 flex items-center justify-center border ${currentPage === 1 ? 'border-slate-100 text-slate-300 pointer-events-none bg-slate-50' : 'border-slate-200 text-slate-600 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 bg-white shadow-sm'}"><i class="fas fa-chevron-left text-xs"></i></button>`;

        // Number Buttons
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                if (i === currentPage) {
                    html += `<button type="button" class="btn-pill w-10 h-10 flex items-center justify-center bg-emerald-500 text-white font-black text-sm shadow-md pointer-events-none">${i}</button>`;
                } else {
                    html += `<button type="button" data-page="${i}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-sm hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 shadow-sm">${i}</button>`;
                }
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span class="w-10 h-10 flex items-center justify-center text-slate-400 font-black">...</span>`;
            }
        }

        // Next Button
        html += `<button type="button" data-page="${currentPage + 1}" class="btn-pill w-10 h-10 flex items-center justify-center border ${currentPage === totalPages ? 'border-slate-100 text-slate-300 pointer-events-none bg-slate-50' : 'border-slate-200 text-slate-600 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 bg-white shadow-sm'}"><i class="fas fa-chevron-right text-xs"></i></button>`;

        pagination.innerHTML = html;
    };

    // Render List Content yang dibatasi Pagination
    const renderListContent = () => {
        const totalItems = currentItems.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        if (totalItems === 0) {
            list.innerHTML = '';
            empty.classList.remove('hidden');
            empty.classList.add('flex');
            pagination.innerHTML = '';
            subtitle.textContent = 'Tidak ada jadwal yang ditampilkan.';
            return;
        }

        empty.classList.add('hidden');
        empty.classList.remove('flex');

        const startIdx = (currentPage - 1) * itemsPerPage;
        const endIdx = startIdx + itemsPerPage;
        const paginatedItems = currentItems.slice(startIdx, endIdx);

        list.innerHTML = paginatedItems.map(cardTemplate).join('');
        
        // Update text subtitle
        const endDisplay = Math.min(endIdx, totalItems);
        subtitle.innerHTML = `Menampilkan <strong class="text-slate-800">${startIdx + 1}-${endDisplay}</strong> dari <strong class="text-slate-800">${totalItems}</strong> agenda tersinkronisasi.`;

        renderPaginationControls(totalItems, totalPages);
    };

    const renderPayload = (payload, animate = true) => {
        currentItems = Array.isArray(payload?.items) ? payload.items : [];

        updateStats(payload?.stats || {});
        updateNotif(payload?.unread_jadwal_notifikasi || 0);

        if (serverTime && payload?.server_time) {
            serverTime.textContent = payload.server_time;
        }

        if (!list || !empty) return;

        if (animate) {
            list.classList.add('is-loading');
        }

        setTimeout(() => {
            renderListContent();
            list.classList.remove('is-loading');
        }, animate ? 150 : 0);
    };

    const buildUrl = () => {
        const url = new URL(liveUrl, window.location.origin);
        const params = new URLSearchParams(window.location.search);
        params.forEach((value, key) => url.searchParams.set(key, value));
        url.searchParams.set('_live', Date.now().toString());
        return url.toString();
    };

    const refreshJadwal = async (manual = false) => {
        if (isFetching) return;
        isFetching = true;

        if (manual) setStatus('Memeriksa jadwal...', 'loading');

        try {
            const response = await fetch(buildUrl(), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                cache: 'no-store'
            });

            if (!response.ok) throw new Error('Gagal memuat jadwal.');

            const payload = await response.json();
            const newestId = Number(payload?.latest_id || 0);

            if (payload.hash !== currentHash) {
                if (newestId > lastSeenId && lastSeenId > 0) {
                    showToast('Bidan menambahkan atau memperbarui jadwal Posyandu.');
                }
                renderPayload(payload, true);
                currentHash = payload.hash || '';
            } else {
                updateNotif(payload?.unread_jadwal_notifikasi || 0);
                if (serverTime && payload?.server_time) {
                    serverTime.textContent = payload.server_time;
                }
            }

            if (newestId > lastSeenId) {
                lastSeenId = newestId;
                localStorage.setItem(lastSeenKey, String(lastSeenId));
            }

            setStatus('Realtime aktif', 'active');
        } catch (error) {
            setStatus('Koneksi terputus', 'error');
        } finally {
            isFetching = false;
        }
    };

    // Event Listener Pagination Controls
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-page]');
        if (btn) {
            currentPage = parseInt(btn.dataset.page);
            list.classList.add('is-loading');
            setTimeout(() => {
                renderListContent();
                list.classList.remove('is-loading');
                // Scroll smooth ke atas area jadwal
                document.getElementById('data-table-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    });

    // Reset pagination ketika filter berubah
    document.getElementById('filterForm')?.addEventListener('submit', () => {
        currentPage = 1;
    });
    document.getElementById('filterForm')?.addEventListener('input', () => {
        currentPage = 1;
    });

    // Initial render
    renderPayload(initialPayload, false);

    // Polling setup
    setTimeout(() => refreshJadwal(false), 700);
    setInterval(() => refreshJadwal(false), 3000); // 3 seconds polling

    window.addEventListener('focus', () => refreshJadwal(true));
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refreshJadwal(true);
    });
});
</script>
@endpush