@extends('layouts.kader')

@section('title', 'Agenda Posyandu')
@section('page-name', 'Agenda Posyandu')
@section('page-title', 'Agenda Posyandu')
@section('page_title', 'Agenda Posyandu')

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
@endphp

@push('styles')
<style>
    .fade-in-up {
        animation: fadeInUp .55s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .nexus-grid-bg {
        background-image:
            linear-gradient(rgba(16,185,129,.045) 1px, transparent 1px),
            linear-gradient(90deg, rgba(16,185,129,.045) 1px, transparent 1px);
        background-size: 28px 28px;
    }

    .nexus-orb {
        position: absolute;
        top: -150px;
        right: -130px;
        width: 520px;
        height: 520px;
        background: radial-gradient(circle, rgba(16,185,129,.16), rgba(56,189,248,.10), transparent 68%);
        filter: blur(30px);
        pointer-events: none;
        z-index: 0;
    }

    .nexus-card {
        background: rgba(255, 255, 255, .84);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, .82);
        border-radius: 32px;
        box-shadow: 0 24px 70px -36px rgba(15, 23, 42, .34);
    }

    .nexus-hero {
        background:
            radial-gradient(circle at 8% 0%, rgba(16,185,129,.16), transparent 30%),
            radial-gradient(circle at 88% 12%, rgba(14,165,233,.13), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.92), rgba(236,253,245,.72));
    }

    .dash-hover {
        transition: all .28s cubic-bezier(.4, 0, .2, 1);
    }

    .dash-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 48px -28px rgba(15, 23, 42, .35);
    }

    .agenda-card {
        min-height: 292px;
        transition: all .28s cubic-bezier(.4, 0, .2, 1);
    }

    .agenda-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 52px -30px rgba(15, 23, 42, .32);
        border-color: rgba(16,185,129,.32);
    }

    .tab-btn {
        transition: all .25s cubic-bezier(.4, 0, .2, 1);
    }

    .tab-btn.active {
        background: #059669;
        color: #fff;
        box-shadow: 0 16px 34px rgba(5,150,105,.22);
    }

    .tab-btn:not(.active):hover {
        background: rgba(255,255,255,.86);
        color: #047857;
        transform: translateY(-1px);
    }

    .panel-live {
        transition: opacity .18s ease, transform .18s ease;
    }

    .panel-live.loading {
        opacity: .55;
        transform: translateY(4px);
    }

    .page-btn {
        transition: all .2s ease;
    }

    .page-btn:hover {
        transform: translateY(-1px);
    }

    .toast-nexus {
        opacity: 0;
        pointer-events: none;
        transform: translateY(14px) scale(.96);
        transition: all .24s cubic-bezier(.16, 1, .3, 1);
    }

    .toast-nexus.show {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }
</style>
@endpush

<div class="relative z-10 mx-auto max-w-[1180px] pb-16 fade-in-up">
    <div class="nexus-orb"></div>

    <section class="nexus-grid-bg relative mb-6 overflow-hidden rounded-[36px] bg-emerald-50/35 p-5 md:p-8">
        <div class="nexus-hero relative z-10 rounded-[34px] border border-white/80 p-7 shadow-[0_24px_70px_-38px_rgba(15,23,42,.36)] backdrop-blur-xl md:p-9">
            <div class="grid gap-6 xl:grid-cols-[1fr_390px] xl:items-stretch">
                <div class="rounded-[30px] border border-emerald-100/80 bg-white/62 p-6 shadow-sm backdrop-blur-xl md:p-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/70 px-4 py-2 text-[11px] font-black tracking-[0.16em] text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Agenda Kader
                    </div>

                    <h1 class="mt-6 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                        Jadwal Posyandu
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold leading-7 text-slate-600 md:text-base">
                        Pantau jadwal terbaru dari Bidan, pisahkan agenda aktif dan riwayat selesai dalam tampilan tab yang lebih rapi.
                    </p>

                    <div class="mt-7 grid gap-3 sm:grid-cols-4">
                        <div class="dash-hover rounded-[24px] border border-white/80 bg-white/82 p-4 shadow-sm">
                            <p class="text-[10px] font-black tracking-[0.14em] text-slate-400">Semua</p>
                            <p id="stat-semua" class="mt-2 text-2xl font-black text-slate-900">{{ $stats['semua'] ?? 0 }}</p>
                        </div>

                        <div class="dash-hover rounded-[24px] border border-white/80 bg-white/82 p-4 shadow-sm">
                            <p class="text-[10px] font-black tracking-[0.14em] text-emerald-500">Aktif</p>
                            <p id="stat-aktif" class="mt-2 text-2xl font-black text-emerald-600">{{ $stats['aktif'] ?? 0 }}</p>
                        </div>

                        <div class="dash-hover rounded-[24px] border border-white/80 bg-white/82 p-4 shadow-sm">
                            <p class="text-[10px] font-black tracking-[0.14em] text-sky-500">Mendatang</p>
                            <p id="stat-mendatang" class="mt-2 text-2xl font-black text-sky-600">{{ $stats['mendatang'] ?? 0 }}</p>
                        </div>

                        <div class="dash-hover rounded-[24px] border border-white/80 bg-white/82 p-4 shadow-sm">
                            <p class="text-[10px] font-black tracking-[0.14em] text-amber-500">Selesai</p>
                            <p id="stat-selesai" class="mt-2 text-2xl font-black text-amber-600">{{ $stats['selesai'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('kader.jadwal.index') }}" class="rounded-[30px] border border-white/80 bg-white/80 p-6 shadow-sm backdrop-blur-xl">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black tracking-[0.18em] text-emerald-700">Filter Jadwal</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Cari agenda tanpa bikin layar berantakan.</p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <i class="fas fa-filter"></i>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Cari judul, lokasi, atau kategori..."
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                        >

                        <div class="grid gap-3 sm:grid-cols-2">
                            <select name="kategori" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                                @foreach($kategoriOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['kategori'] ?? 'semua') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <select name="periode" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                                @foreach($periodeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['periode'] ?? 'semua') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button type="submit" class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-600 px-5 text-xs font-black tracking-[0.16em] text-white shadow-[0_14px_30px_rgba(16,185,129,.25)] transition hover:-translate-y-0.5 hover:bg-emerald-700">
                                Terapkan
                            </button>

                            <a href="{{ route('kader.jadwal.index') }}" class="inline-flex h-12 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-5 text-xs font-black tracking-[0.16em] text-emerald-700 transition hover:-translate-y-0.5 hover:bg-emerald-100">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="nexus-card overflow-hidden p-5 md:p-6">
        <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-900">Daftar Agenda</h2>
                <p id="tab-desc" class="mt-1 text-sm font-semibold text-slate-500">
                    Menampilkan jadwal aktif dan mendatang dari Bidan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-[11px] font-black tracking-[0.14em] text-emerald-700">
                    <span id="live-dot" class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span id="live-text">Realtime aktif</span>
                </div>

                <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-4 py-2 text-[11px] font-black tracking-[0.14em] text-sky-700">
                    <i class="fas fa-clock"></i>
                    <span id="server-time">{{ $initialPayload['server_time'] ?? '-' }}</span>
                </div>

                <div id="notif-pill" class="hidden items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-[11px] font-black tracking-[0.14em] text-amber-700">
                    <i class="fas fa-bell"></i>
                    <span id="notif-count">0</span> notifikasi
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_360px] lg:items-center">
            <div class="grid gap-3 rounded-[26px] border border-slate-100 bg-slate-50/75 p-2 sm:grid-cols-2">
                <button type="button" data-tab="active" class="tab-btn active flex h-14 items-center justify-between rounded-[20px] px-5 text-left text-xs font-black tracking-[0.13em]">
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-calendar-day"></i>
                        Jadwal Terbaru
                    </span>
                    <span id="tab-active-count" class="rounded-full bg-white/20 px-3 py-1 text-[11px]">0</span>
                </button>

                <button type="button" data-tab="history" class="tab-btn flex h-14 items-center justify-between rounded-[20px] border border-slate-200 bg-white px-5 text-left text-xs font-black tracking-[0.13em] text-slate-600">
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-clock-rotate-left"></i>
                        Jadwal Selesai
                    </span>
                    <span id="tab-history-count" class="rounded-full bg-slate-100 px-3 py-1 text-[11px] text-slate-500">0</span>
                </button>
            </div>

            <div id="tab-summary" class="rounded-[24px] border border-emerald-100 bg-emerald-50/70 px-5 py-4 text-sm font-bold leading-6 text-emerald-800">
                Jadwal baru akan langsung masuk ke tab ini tanpa pindah halaman.
            </div>
        </div>

        <div id="agenda-panel" class="panel-live mt-5">
            <div id="agenda-list" class="grid gap-4 lg:grid-cols-2"></div>

            <div id="agenda-empty" class="hidden rounded-[28px] border border-dashed border-emerald-300 bg-emerald-50/70 p-10 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[24px] bg-white text-2xl text-emerald-500 shadow-sm">
                    <i id="empty-icon" class="fas fa-calendar-times"></i>
                </div>

                <h3 id="empty-title" class="mt-5 text-xl font-black text-slate-800">Belum Ada Jadwal</h3>

                <p id="empty-text" class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                    Jadwal baru dari Bidan akan muncul otomatis di halaman ini.
                </p>
            </div>

            <div id="agenda-pagination" class="mt-5 flex flex-wrap items-center justify-center gap-2"></div>
        </div>
    </section>
</div>

<div id="agenda-toast" class="toast-nexus fixed bottom-6 right-6 z-[80] w-[calc(100%-2rem)] max-w-sm rounded-[26px] border border-emerald-200 bg-white/95 p-4 shadow-[0_24px_70px_rgba(15,23,42,.18)] backdrop-blur-xl">
    <div class="flex gap-3">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
            <i class="fas fa-bell"></i>
        </div>

        <div class="min-w-0">
            <p class="text-sm font-black text-slate-900">Jadwal diperbarui</p>
            <p id="agenda-toast-text" class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                Agenda dari Bidan sudah masuk ke daftar.
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const initialPayload = @json($initialPayload);
    const liveUrl = @json(route('kader.jadwal.live'));
    const lastSeenKey = 'posyandu_kader_last_seen_jadwal_id';

    const tabButtons = document.querySelectorAll('[data-tab]');
    const list = document.getElementById('agenda-list');
    const panel = document.getElementById('agenda-panel');
    const empty = document.getElementById('agenda-empty');
    const emptyIcon = document.getElementById('empty-icon');
    const emptyTitle = document.getElementById('empty-title');
    const emptyText = document.getElementById('empty-text');
    const pagination = document.getElementById('agenda-pagination');
    const tabDesc = document.getElementById('tab-desc');
    const tabSummary = document.getElementById('tab-summary');
    const activeCount = document.getElementById('tab-active-count');
    const historyCount = document.getElementById('tab-history-count');
    const liveText = document.getElementById('live-text');
    const liveDot = document.getElementById('live-dot');
    const serverTime = document.getElementById('server-time');
    const notifPill = document.getElementById('notif-pill');
    const notifCount = document.getElementById('notif-count');
    const toast = document.getElementById('agenda-toast');
    const toastText = document.getElementById('agenda-toast-text');

    const perPage = 4;
    let activeTab = 'active';
    let activePage = 1;
    let historyPage = 1;
    let activeItems = [];
    let historyItems = [];
    let isFetching = false;
    let currentHash = initialPayload?.hash || '';
    let lastSeenId = Number(localStorage.getItem(lastSeenKey) || 0);

    if (!lastSeenId && initialPayload?.latest_id) {
        lastSeenId = Number(initialPayload.latest_id);
        localStorage.setItem(lastSeenKey, String(lastSeenId));
    }

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const todayYmd = () => {
        const date = new Date();
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    };

    const isHistory = item => {
        const status = String(item?.status || '').toLowerCase();
        const tanggal = item?.tanggal_raw || '';
        return status === 'selesai' || status === 'dibatalkan' || (tanggal && tanggal < todayYmd());
    };

    const meta = {
        kategoriClass: {
            balita: 'bg-sky-50 text-sky-700 border-sky-200',
            remaja: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            lansia: 'bg-orange-50 text-orange-700 border-orange-200',
            imunisasi: 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
            pemeriksaan: 'bg-teal-50 text-teal-700 border-teal-200',
            lainnya: 'bg-slate-100 text-slate-700 border-slate-200',
            posyandu: 'bg-emerald-50 text-emerald-700 border-emerald-200'
        },
        kategoriIcon: {
            balita: 'fa-child-reaching',
            remaja: 'fa-user-graduate',
            lansia: 'fa-person-cane',
            imunisasi: 'fa-syringe',
            pemeriksaan: 'fa-stethoscope',
            lainnya: 'fa-layer-group',
            posyandu: 'fa-calendar-check'
        },
        statusClass: {
            aktif: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            selesai: 'bg-slate-100 text-slate-600 border-slate-200',
            dibatalkan: 'bg-rose-50 text-rose-700 border-rose-200',
            terjadwal: 'bg-amber-50 text-amber-700 border-amber-200'
        },
        statusDot: {
            aktif: 'bg-emerald-500',
            selesai: 'bg-slate-400',
            dibatalkan: 'bg-rose-500',
            terjadwal: 'bg-amber-500'
        }
    };

    const card = item => `
        <article class="agenda-card rounded-[30px] border border-white/80 bg-white/86 p-5 shadow-sm backdrop-blur-xl sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-black tracking-[0.12em] ${meta.kategoriClass[item.kategori] || meta.kategoriClass.posyandu}">
                            <i class="fas ${meta.kategoriIcon[item.kategori] || meta.kategoriIcon.posyandu}"></i>
                            ${escapeHtml(item.kategori_label)}
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-black tracking-[0.12em] ${meta.statusClass[item.status] || meta.statusClass.terjadwal}">
                            <span class="h-2 w-2 rounded-full ${meta.statusDot[item.status] || meta.statusDot.terjadwal}"></span>
                            ${escapeHtml(item.status_label)}
                        </span>
                    </div>

                    <h3 class="line-clamp-2 text-xl font-black leading-tight text-slate-900">${escapeHtml(item.judul)}</h3>
                    <p class="mt-2 line-clamp-2 text-sm font-semibold leading-6 text-slate-500">${escapeHtml(item.deskripsi)}</p>
                </div>

                <div class="hidden h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-xl text-emerald-600 sm:flex">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50/82 p-4">
                    <p class="text-[10px] font-black tracking-[0.16em] text-slate-400">Tanggal</p>
                    <p class="mt-1 text-sm font-black text-slate-800">${escapeHtml(item.tanggal_label)}</p>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/82 p-4">
                    <p class="text-[10px] font-black tracking-[0.16em] text-slate-400">Waktu</p>
                    <p class="mt-1 text-sm font-black text-slate-800">${escapeHtml(item.waktu_label)}</p>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/82 p-4">
                    <p class="text-[10px] font-black tracking-[0.16em] text-slate-400">Lokasi</p>
                    <p class="mt-1 line-clamp-1 text-sm font-black text-slate-800">${escapeHtml(item.lokasi)}</p>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/82 p-4">
                    <p class="text-[10px] font-black tracking-[0.16em] text-slate-400">Target</p>
                    <p class="mt-1 text-sm font-black text-slate-800">${escapeHtml(item.target_label)}</p>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3">
                <p class="text-xs font-semibold text-slate-400">Read-only untuk Kader.</p>
                <a href="${escapeHtml(item.show_url)}" class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-900 px-5 text-xs font-black tracking-[0.14em] text-white transition hover:bg-emerald-700">Detail</a>
            </div>
        </article>
    `;

    const splitItems = items => {
        const clean = Array.isArray(items) ? items : [];
        return {
            active: clean.filter(item => !isHistory(item)).sort((a, b) => `${a.tanggal_raw || '9999-12-31'} ${a.waktu_label || ''}`.localeCompare(`${b.tanggal_raw || '9999-12-31'} ${b.waktu_label || ''}`)),
            history: clean.filter(item => isHistory(item)).sort((a, b) => `${b.tanggal_raw || '0000-00-00'} ${b.waktu_label || ''}`.localeCompare(`${a.tanggal_raw || '0000-00-00'} ${a.waktu_label || ''}`))
        };
    };

    const itemsNow = () => activeTab === 'active' ? activeItems : historyItems;
    const pageNow = () => activeTab === 'active' ? activePage : historyPage;
    const setPage = page => activeTab === 'active' ? activePage = page : historyPage = page;

    const buttonPage = (label, page, active, disabled) => `
        <button type="button" data-page="${page}" class="page-btn inline-flex h-10 min-w-10 items-center justify-center rounded-2xl border px-3 text-xs font-black ${active ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700'} ${disabled ? 'pointer-events-none opacity-40' : ''}">
            ${label}
        </button>
    `;

    const renderPagination = (total, page) => {
        const pages = Math.max(1, Math.ceil(total / perPage));
        if (!pagination || pages <= 1) {
            if (pagination) pagination.innerHTML = '';
            return;
        }

        let html = buttonPage('<i class="fas fa-chevron-left"></i>', Math.max(1, page - 1), false, page === 1);

        for (let i = 1; i <= pages; i++) {
            if (i === 1 || i === pages || Math.abs(i - page) <= 1) {
                html += buttonPage(i, i, i === page, false);
            } else if (Math.abs(i - page) === 2) {
                html += `<span class="inline-flex h-10 items-center px-1 text-xs font-black text-slate-400">...</span>`;
            }
        }

        html += buttonPage('<i class="fas fa-chevron-right"></i>', Math.min(pages, page + 1), false, page === pages);
        pagination.innerHTML = html;
    };

    const syncTabCopy = () => {
        if (activeTab === 'active') {
            tabDesc.textContent = 'Menampilkan jadwal aktif dan mendatang dari Bidan.';
            tabSummary.textContent = 'Jadwal baru akan langsung masuk ke tab ini tanpa pindah halaman.';
            emptyIcon.className = 'fas fa-calendar-day';
            emptyTitle.textContent = 'Belum Ada Jadwal Terbaru';
            emptyText.textContent = 'Jadwal baru dari Bidan akan muncul otomatis di tab ini.';
        } else {
            tabDesc.textContent = 'Menampilkan agenda yang sudah selesai, lewat, atau dibatalkan.';
            tabSummary.textContent = 'Riwayat dipisah agar jadwal terbaru tetap bersih dan tidak ketumpuk.';
            emptyIcon.className = 'fas fa-clock-rotate-left';
            emptyTitle.textContent = 'Belum Ada Jadwal Selesai';
            emptyText.textContent = 'Jadwal yang sudah selesai akan tersimpan di tab ini.';
        }

        tabButtons.forEach(btn => {
            const active = btn.dataset.tab === activeTab;
            btn.classList.toggle('active', active);
            btn.classList.toggle('bg-white', !active);
            btn.classList.toggle('text-slate-600', !active);
            btn.classList.toggle('border', !active);
            btn.classList.toggle('border-slate-200', !active);
        });
    };

    const render = (animate = true) => {
        const items = itemsNow();
        let page = pageNow();
        const pages = Math.max(1, Math.ceil(items.length / perPage));

        if (page > pages) {
            page = pages;
            setPage(page);
        }

        activeCount.textContent = activeItems.length;
        historyCount.textContent = historyItems.length;
        syncTabCopy();

        if (animate) panel.classList.add('loading');

        setTimeout(() => {
            if (!items.length) {
                list.innerHTML = '';
                empty.classList.remove('hidden');
                pagination.innerHTML = '';
            } else {
                empty.classList.add('hidden');
                list.innerHTML = items.slice((page - 1) * perPage, page * perPage).map(card).join('');
                renderPagination(items.length, page);
            }

            panel.classList.remove('loading');
        }, animate ? 110 : 0);
    };

    const updateStats = stats => {
        const map = {
            'stat-semua': stats?.semua ?? 0,
            'stat-aktif': stats?.aktif ?? 0,
            'stat-mendatang': stats?.mendatang ?? 0,
            'stat-selesai': stats?.selesai ?? 0,
        };

        Object.entries(map).forEach(([id, value]) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        });
    };

    const updateNotif = count => {
        const total = Number(count || 0);
        notifCount.textContent = total;

        if (total > 0) {
            notifPill.classList.remove('hidden');
            notifPill.classList.add('inline-flex');
        } else {
            notifPill.classList.add('hidden');
            notifPill.classList.remove('inline-flex');
        }
    };

    const statusLive = (text, mode = 'active') => {
        liveText.textContent = text;
        liveDot.className = `h-2 w-2 rounded-full ${mode === 'active' ? 'bg-emerald-500' : mode === 'loading' ? 'bg-amber-500' : 'bg-rose-500'}`;
    };

    const showToast = message => {
        toastText.textContent = message || 'Agenda dari Bidan sudah masuk ke daftar.';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4200);
    };

    const renderPayload = (payload, animate = true) => {
        updateStats(payload?.stats || {});
        updateNotif(payload?.unread_jadwal_notifikasi || 0);

        if (payload?.server_time) serverTime.textContent = payload.server_time;

        const split = splitItems(payload?.items || []);
        activeItems = split.active;
        historyItems = split.history;

        render(animate);
    };

    const buildUrl = () => {
        const url = new URL(liveUrl, window.location.origin);
        new URLSearchParams(window.location.search).forEach((value, key) => url.searchParams.set(key, value));
        url.searchParams.set('_live', Date.now().toString());
        return url.toString();
    };

    const refresh = async (manual = false) => {
        if (isFetching) return;
        isFetching = true;

        if (manual) statusLive('Memeriksa jadwal...', 'loading');

        try {
            const res = await fetch(buildUrl(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                cache: 'no-store'
            });

            if (!res.ok) throw new Error('Gagal memuat jadwal.');

            const payload = await res.json();
            const newestId = Number(payload?.latest_id || 0);

            if (payload.hash !== currentHash) {
                if (newestId > lastSeenId && lastSeenId > 0) {
                    showToast('Bidan menambahkan atau memperbarui jadwal Posyandu.');
                    activeTab = 'active';
                    activePage = 1;
                }

                renderPayload(payload, true);
                currentHash = payload.hash || '';
            } else {
                updateNotif(payload?.unread_jadwal_notifikasi || 0);
                if (payload?.server_time) serverTime.textContent = payload.server_time;
            }

            if (newestId > lastSeenId) {
                lastSeenId = newestId;
                localStorage.setItem(lastSeenKey, String(lastSeenId));
            }

            statusLive('Realtime aktif', 'active');
        } catch (error) {
            statusLive('Realtime tertunda', 'error');
        } finally {
            isFetching = false;
        }
    };

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            activeTab = btn.dataset.tab || 'active';
            render(true);
        });
    });

    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-page]');
        if (!btn) return;
        setPage(Number(btn.dataset.page || 1));
        render(true);
    });

    renderPayload(initialPayload, false);
    setTimeout(() => refresh(false), 700);
    setInterval(() => refresh(false), 2500);
    window.addEventListener('focus', () => refresh(true));
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refresh(true);
    });
});
</script>
@endsection