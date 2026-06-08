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

<style>
    .kj-shell {
        animation: kjIn .32s cubic-bezier(.16, 1, .3, 1) both;
    }

    .kj-hero {
        background:
            radial-gradient(circle at 8% 0%, rgba(16, 185, 129, .16), transparent 28%),
            radial-gradient(circle at 86% 10%, rgba(14, 165, 233, .13), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.88), rgba(236,253,245,.72));
    }

    .kj-card {
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .kj-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 22px 55px rgba(15, 23, 42, .09);
        border-color: rgba(16, 185, 129, .24);
    }

    .kj-live-list {
        transition: opacity .2s ease, transform .2s ease;
    }

    .kj-live-list.is-loading {
        opacity: .58;
        transform: translateY(4px);
    }

    .kj-toast {
        opacity: 0;
        pointer-events: none;
        transform: translateY(14px) scale(.96);
        transition: opacity .22s ease, transform .22s ease;
    }

    .kj-toast.show {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    @keyframes kjIn {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="kj-shell mx-auto max-w-7xl space-y-6 px-4 pb-10 sm:px-6 lg:px-8">
    <section class="kj-hero overflow-hidden rounded-[34px] border border-white/80 p-5 shadow-[0_24px_80px_rgba(15,23,42,.07)] backdrop-blur-xl sm:p-7 lg:p-8">
        <div class="grid gap-5 xl:grid-cols-[1fr_390px] xl:items-stretch">
            <div class="rounded-[30px] border border-emerald-100/80 bg-white/65 p-6 shadow-sm backdrop-blur-xl sm:p-8">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Agenda Kader
                </div>

                <h1 class="mt-6 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    Jadwal Posyandu
                </h1>

                <p class="mt-3 max-w-3xl text-sm font-semibold leading-7 text-slate-600 sm:text-base">
                    Agenda dari Bidan akan tampil otomatis di halaman ini. Kader cukup memantau jadwal, target sasaran, lokasi, dan persiapan layanan.
                </p>

                <div class="mt-7 grid gap-3 sm:grid-cols-4">
                    <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm">
                        <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">Semua</p>
                        <p id="stat-semua" class="mt-2 text-2xl font-black text-slate-900">{{ $stats['semua'] ?? 0 }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm">
                        <p class="text-[11px] font-black uppercase tracking-[0.15em] text-emerald-500">Aktif</p>
                        <p id="stat-aktif" class="mt-2 text-2xl font-black text-emerald-600">{{ $stats['aktif'] ?? 0 }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm">
                        <p class="text-[11px] font-black uppercase tracking-[0.15em] text-sky-500">Mendatang</p>
                        <p id="stat-mendatang" class="mt-2 text-2xl font-black text-sky-600">{{ $stats['mendatang'] ?? 0 }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm">
                        <p class="text-[11px] font-black uppercase tracking-[0.15em] text-amber-500">Selesai</p>
                        <p id="stat-selesai" class="mt-2 text-2xl font-black text-amber-600">{{ $stats['selesai'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('kader.jadwal.index') }}" class="rounded-[30px] border border-white/80 bg-white/82 p-6 shadow-sm backdrop-blur-xl">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">Filter Jadwal</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Cari agenda tanpa reload berat.</p>
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
                        <button type="submit" class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-600 px-5 text-xs font-black uppercase tracking-[0.16em] text-white shadow-[0_14px_30px_rgba(16,185,129,.25)] transition hover:bg-emerald-700">
                            Terapkan
                        </button>

                        <a href="{{ route('kader.jadwal.index') }}" class="inline-flex h-12 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-5 text-xs font-black uppercase tracking-[0.16em] text-emerald-700 transition hover:bg-emerald-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="space-y-5">
        <div class="flex flex-col gap-3 rounded-[26px] border border-white/80 bg-white/76 p-4 shadow-sm backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Daftar Agenda</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Jadwal baru dari Bidan akan masuk otomatis tanpa pindah halaman.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.14em] text-emerald-700">
                    <span id="live-dot" class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span id="live-text">Realtime aktif</span>
                </div>

                <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.14em] text-sky-700">
                    <i class="fas fa-clock"></i>
                    <span id="server-time">{{ $initialPayload['server_time'] ?? '-' }}</span>
                </div>

                <div id="notif-pill" class="hidden items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.14em] text-amber-700">
                    <i class="fas fa-bell"></i>
                    <span id="notif-count">0</span> notifikasi
                </div>
            </div>
        </div>

        <div id="jadwal-card-list" class="kj-live-list grid gap-4 lg:grid-cols-2"></div>

        <div id="jadwal-empty" class="hidden rounded-[30px] border border-dashed border-emerald-300 bg-emerald-50/70 p-10 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[24px] bg-white text-2xl text-emerald-500 shadow-sm">
                <i class="fas fa-calendar-times"></i>
            </div>

            <h3 class="mt-5 text-xl font-black text-slate-800">Jadwal Tidak Ditemukan</h3>

            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                Belum ada agenda Posyandu yang sesuai dengan filter saat ini.
            </p>

            <a href="{{ route('kader.jadwal.index') }}" class="mt-5 inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-600 px-5 text-xs font-black uppercase tracking-[0.12em] text-white shadow-[0_12px_26px_rgba(16,185,129,.22)] transition hover:bg-emerald-700">
                Reset Filter
            </a>
        </div>
    </section>
</div>

<div id="jadwal-toast" class="kj-toast fixed bottom-6 right-6 z-[80] w-[calc(100%-2rem)] max-w-sm rounded-[26px] border border-emerald-200 bg-white/95 p-4 shadow-[0_24px_70px_rgba(15,23,42,.18)] backdrop-blur-xl">
    <div class="flex gap-3">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
            <i class="fas fa-bell"></i>
        </div>

        <div class="min-w-0">
            <p class="text-sm font-black text-slate-900">Jadwal baru diterima</p>
            <p id="jadwal-toast-text" class="mt-1 text-xs font-semibold leading-5 text-slate-500">
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

    const list = document.getElementById('jadwal-card-list');
    const empty = document.getElementById('jadwal-empty');
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
            balita: 'bg-sky-50 text-sky-700 border-sky-200',
            remaja: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            lansia: 'bg-orange-50 text-orange-700 border-orange-200',
            imunisasi: 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
            pemeriksaan: 'bg-teal-50 text-teal-700 border-teal-200',
            lainnya: 'bg-slate-100 text-slate-700 border-slate-200',
            posyandu: 'bg-emerald-50 text-emerald-700 border-emerald-200'
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
            selesai: 'bg-slate-100 text-slate-600 border-slate-200',
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
            <article class="kj-card overflow-hidden rounded-[30px] border border-white/80 bg-white/82 shadow-sm backdrop-blur-xl">
                <div class="flex h-full flex-col p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-black uppercase tracking-[0.12em] ${kategoriClass(item.kategori)}">
                                    <i class="fas ${kategoriIcon(item.kategori)}"></i>
                                    ${escapeHtml(item.kategori_label)}
                                </span>

                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-black uppercase tracking-[0.12em] ${statusClass(item.status)}">
                                    <span class="h-2 w-2 rounded-full ${statusDot(item.status)}"></span>
                                    ${escapeHtml(item.status_label)}
                                </span>
                            </div>

                            <h3 class="line-clamp-2 text-xl font-black leading-tight text-slate-900">
                                ${escapeHtml(item.judul)}
                            </h3>

                            <p class="mt-2 line-clamp-2 text-sm font-semibold leading-6 text-slate-500">
                                ${escapeHtml(item.deskripsi)}
                            </p>
                        </div>

                        <div class="hidden h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-xl text-emerald-600 sm:flex">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Tanggal</p>
                            <p class="mt-1 text-sm font-black text-slate-800">${escapeHtml(item.tanggal_label)}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Waktu</p>
                            <p class="mt-1 text-sm font-black text-slate-800">${escapeHtml(item.waktu_label)}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Lokasi</p>
                            <p class="mt-1 line-clamp-1 text-sm font-black text-slate-800">${escapeHtml(item.lokasi)}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Target</p>
                            <p class="mt-1 text-sm font-black text-slate-800">${escapeHtml(item.target_label)}</p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs font-semibold text-slate-400">
                            Read-only untuk Kader.
                        </p>

                        <a href="${escapeHtml(item.show_url)}" class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-900 px-5 text-xs font-black uppercase tracking-[0.14em] text-white transition hover:bg-emerald-700">
                            Detail
                        </a>
                    </div>
                </div>
            </article>
        `;
    };

    const setStatus = (text, mode = 'active') => {
        if (liveText) liveText.textContent = text;

        if (!liveDot) return;

        const color = mode === 'active'
            ? 'bg-emerald-500'
            : mode === 'loading'
                ? 'bg-amber-500'
                : 'bg-rose-500';

        liveDot.className = `h-2 w-2 rounded-full ${color}`;
    };

    const showToast = (message) => {
        if (!toast) return;

        if (toastText) toastText.textContent = message || 'Agenda dari Bidan sudah masuk ke daftar.';

        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 4200);
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

    const renderPayload = (payload, animate = true) => {
        const items = Array.isArray(payload?.items) ? payload.items : [];

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
            if (items.length === 0) {
                list.innerHTML = '';
                empty.classList.remove('hidden');
            } else {
                empty.classList.add('hidden');
                list.innerHTML = items.map(cardTemplate).join('');
            }

            list.classList.remove('is-loading');
        }, animate ? 130 : 0);
    };

    const buildUrl = () => {
        const url = new URL(liveUrl, window.location.origin);
        const params = new URLSearchParams(window.location.search);

        params.forEach((value, key) => {
            url.searchParams.set(key, value);
        });

        url.searchParams.set('_live', Date.now().toString());

        return url.toString();
    };

    const refreshJadwal = async (manual = false) => {
        if (isFetching) return;

        isFetching = true;

        if (manual) {
            setStatus('Memeriksa jadwal...', 'loading');
        }

        try {
            const response = await fetch(buildUrl(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('Gagal memuat jadwal.');
            }

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
            setStatus('Realtime tertunda', 'error');
            console.warn(error.message);
        } finally {
            isFetching = false;
        }
    };

    renderPayload(initialPayload, false);

    setTimeout(() => refreshJadwal(false), 700);
    setInterval(() => refreshJadwal(false), 2000);

    window.addEventListener('focus', () => refreshJadwal(true));

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refreshJadwal(true);
        }
    });
});
</script>
@endsection