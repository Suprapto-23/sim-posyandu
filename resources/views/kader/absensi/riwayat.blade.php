@extends('layouts.kader')

@section('title', 'Riwayat Absensi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $riwayats = $riwayats ?? $riwayat ?? collect();
    $bulan = (int) ($bulan ?? request('bulan', now()->month));
    $tahun = (int) ($tahun ?? request('tahun', now()->year));
    $kategoriAktif = $kategori ?? request('kategori');

    $bulanOptions = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $kategoriMenus = [
        '' => [
            'label' => 'Semua Kategori',
            'icon' => 'fa-layer-group',
        ],
        'balita' => [
            'label' => 'Balita / Anak',
            'icon' => 'fa-child-reaching',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
        ],
    ];

    $kategoriLabel = function ($kategori) {
        return match($kategori) {
            'balita' => 'Balita / Anak',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => 'Sasaran',
        };
    };

    $kategoriIcon = function ($kategori) {
        return match($kategori) {
            'balita' => 'fa-child-reaching',
            'remaja' => 'fa-user-graduate',
            'lansia' => 'fa-person-cane',
            default => 'fa-users',
        };
    };

    $totalSesi = $riwayats->count();
    $totalPeserta = $riwayats->sum(fn ($item) => (int) ($item->total_peserta ?? $item->details_count ?? 0));
    $totalHadir = $riwayats->sum(fn ($item) => (int) ($item->total_hadir ?? 0));
    $totalTidakHadir = max(0, $totalPeserta - $totalHadir);
    $persentase = $totalPeserta > 0 ? round(($totalHadir / $totalPeserta) * 100) : 0;

    $routeHas = fn ($name) => Route::has($name);
@endphp

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

    .history-page {
        position: relative;
        isolation: isolate;
        font-family: "Plus Jakarta Sans", Inter, ui-sans-serif, system-ui, sans-serif;
        animation: historyIn .2s ease-out both;
    }

    .history-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 10% 8%, rgba(16,185,129,.14), transparent 28%),
            radial-gradient(circle at 88% 12%, rgba(245,158,11,.11), transparent 26%),
            linear-gradient(135deg, #f7fffc 0%, #f8fafc 54%, #fffaf1 100%);
    }

    @keyframes historyIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .history-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(167,243,208,.72);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.16), transparent 30%),
            radial-gradient(circle at 88% 18%, rgba(245,158,11,.13), transparent 32%),
            linear-gradient(135deg, rgba(255,255,255,.96), rgba(236,253,245,.78));
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .history-hero::after {
        content: "";
        position: absolute;
        right: -80px;
        bottom: -110px;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(16,185,129,.12);
        pointer-events: none;
    }

    .history-badge {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        border-radius: 999px;
        border: 1px solid rgba(16,185,129,.18);
        background: rgba(236,253,245,.88);
        color: #047857;
        padding: .68rem 1rem;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .history-surface {
        border: 1px solid rgba(226,232,240,.88);
        background: rgba(255,255,255,.92);
        box-shadow: 0 14px 34px rgba(15,23,42,.05);
    }

    .history-stat {
        border-radius: 26px;
        border: 1px solid rgba(226,232,240,.86);
        box-shadow: 0 12px 28px rgba(15,23,42,.04);
    }

    .stat-emerald {
        background: linear-gradient(145deg, #ffffff, #ecfdf5);
        border-color: rgba(16,185,129,.18);
    }

    .stat-gold {
        background: linear-gradient(145deg, #ffffff, #fff8eb);
        border-color: rgba(245,158,11,.16);
    }

    .stat-rose {
        background: linear-gradient(145deg, #ffffff, #fff1f2);
        border-color: rgba(244,63,94,.14);
    }

    .stat-sky {
        background: linear-gradient(145deg, #ffffff, #f0f9ff);
        border-color: rgba(14,165,233,.14);
    }

    .history-action {
        border-radius: 18px;
        font-weight: 900;
        transition: background .15s ease, transform .15s ease, border-color .15s ease;
    }

    .history-action:hover {
        transform: translateY(-1px);
    }

    .history-primary {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        box-shadow: 0 12px 24px rgba(5,150,105,.16);
    }

    .history-dark {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: white;
        box-shadow: 0 12px 24px rgba(15,23,42,.13);
    }

    .history-outline {
        border: 1px solid rgba(16,185,129,.18);
        background: white;
        color: #047857;
    }

    .history-outline:hover {
        background: #ecfdf5;
    }

    .filter-input {
        border: 1px solid rgba(226,232,240,.9);
        background: white;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .filter-input:focus {
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
    }

    .history-row {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(226,232,240,.86);
        background: #fff;
        box-shadow: 0 8px 22px rgba(15,23,42,.035);
        transition: border-color .14s ease, background .14s ease;
    }

    .history-row::before {
        content: "";
        position: absolute;
        left: 0;
        top: 16px;
        bottom: 16px;
        width: 4px;
        border-radius: 999px;
        background: linear-gradient(180deg, #10b981, #f59e0b);
        opacity: .75;
    }

    .history-row:hover {
        border-color: rgba(16,185,129,.25);
        background: #fbfffd;
    }

    .progress-track {
        height: 9px;
        border-radius: 999px;
        background: #f1f5f9;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #059669, #10b981, #f59e0b);
    }

    .history-scroll {
        max-height: 650px;
        overflow-y: auto;
        padding-right: .35rem;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .history-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .history-scroll::-webkit-scrollbar-track {
        background: rgba(226,232,240,.55);
        border-radius: 999px;
    }

    .history-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }

    @media (max-width: 640px) {
        .history-action:hover {
            transform: none;
        }

        .history-scroll {
            max-height: none;
            overflow: visible;
            padding-right: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .history-page,
        .history-action {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="history-page space-y-6">

    {{-- HERO --}}
    <section class="history-hero rounded-[32px] p-5 sm:p-6 lg:p-7">
        <div class="relative z-10 grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="history-badge mb-4">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Arsip Kehadiran
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Riwayat Absensi Posyandu
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Pantau seluruh sesi presensi berdasarkan bulan, tahun, dan kategori sasaran. Jadi tidak perlu bongkar catatan manual yang nasibnya sering lebih kusut dari kabel charger.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                @if($routeHas('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index') }}" class="history-action history-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-plus"></i>
                        Presensi Baru
                    </a>
                @endif

                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}" class="history-action history-dark inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-chart-simple"></i>
                        Dashboard
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- STAT --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="history-stat stat-sky p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Total Sesi</p>
            <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($totalSesi) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">{{ $bulanOptions[$bulan] ?? '-' }} {{ $tahun }}</p>
        </div>

        <div class="history-stat stat-emerald p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Total Hadir</p>
            <h2 class="mt-3 text-3xl font-black text-emerald-700">{{ number_format($totalHadir) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">{{ $persentase }}% dari peserta tercatat</p>
        </div>

        <div class="history-stat stat-rose p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Tidak Hadir</p>
            <h2 class="mt-3 text-3xl font-black text-rose-600">{{ number_format($totalTidakHadir) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Tidak hadir / belum hadir</p>
        </div>

        <div class="history-stat stat-gold p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Total Peserta</p>
            <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($totalPeserta) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Akumulasi dari semua sesi</p>
        </div>
    </section>

    {{-- FILTER --}}
    <section class="history-surface rounded-[30px] p-5">
        <form method="GET" action="{{ route('kader.absensi.riwayat') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Bulan</label>
                <select name="bulan" class="filter-input h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach($bulanOptions as $key => $label)
                        <option value="{{ $key }}" {{ $bulan === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tahun</label>
                <select name="tahun" class="filter-input h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @for($year = now()->year + 1; $year >= now()->year - 5; $year--)
                        <option value="{{ $year }}" {{ $tahun === $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Kategori</label>
                <select name="kategori" class="filter-input h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach($kategoriMenus as $key => $item)
                        <option value="{{ $key }}" {{ (string) $kategoriAktif === (string) $key ? 'selected' : '' }}>
                            {{ $item['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="history-action history-primary inline-flex h-12 flex-1 items-center justify-center gap-2 px-5 text-sm lg:flex-none">
                    <i class="fa-solid fa-filter"></i>
                    Filter
                </button>

                <a href="{{ route('kader.absensi.riwayat') }}" class="history-action history-outline inline-flex h-12 items-center justify-center gap-2 px-5 text-sm">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </section>

    {{-- LIST RIWAYAT --}}
    <section class="history-surface rounded-[30px] p-5">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900">Daftar Sesi Presensi</h3>
                <p class="text-xs font-bold text-slate-400">
                    Menampilkan {{ number_format($totalSesi) }} sesi pada periode {{ $bulanOptions[$bulan] ?? '-' }} {{ $tahun }}.
                </p>
            </div>

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                <input
                    type="text"
                    id="searchRiwayat"
                    placeholder="Cari kode, kategori, tanggal..."
                    class="filter-input h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700 sm:w-80"
                >
            </div>
        </div>

        @if($riwayats->isNotEmpty())
            <div class="history-scroll space-y-4">
                @foreach($riwayats as $item)
                    @php
                        $peserta = (int) ($item->total_peserta ?? $item->details_count ?? 0);
                        $hadir = (int) ($item->total_hadir ?? 0);
                        $tidakHadir = max(0, $peserta - $hadir);
                        $persenItem = $peserta > 0 ? round(($hadir / $peserta) * 100) : 0;

                        $tanggalItem = !empty($item->tanggal_posyandu)
                            ? Carbon::parse($item->tanggal_posyandu)->translatedFormat('d F Y')
                            : '-';

                        $searchText = strtolower(($item->kode_absensi ?? '') . ' ' . $kategoriLabel($item->kategori) . ' ' . $tanggalItem);
                    @endphp

                    <div class="history-row rounded-[24px] p-4 sm:p-5" data-search="{{ $searchText }}">
                        <div class="grid gap-4 xl:grid-cols-[1.3fr_1fr_auto] xl:items-center">
                            <div class="flex min-w-0 items-center gap-4">
                                <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                                    <i class="fa-solid {{ $kategoriIcon($item->kategori) }}"></i>
                                </div>

                                <div class="min-w-0">
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[.1em] text-emerald-700">
                                            {{ $kategoriLabel($item->kategori) }}
                                        </span>

                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-[.1em] text-amber-700">
                                            Pertemuan {{ $item->nomor_pertemuan ?? '-' }}
                                        </span>
                                    </div>

                                    <h4 class="truncate text-base font-black text-slate-900">
                                        {{ $item->kode_absensi ?? 'Kode Absensi' }}
                                    </h4>

                                    <p class="mt-1 text-xs font-bold text-slate-400">
                                        <i class="fa-regular fa-calendar mr-1"></i>
                                        {{ $tanggalItem }}
                                        <span class="mx-2">•</span>
                                        Dicatat oleh {{ $item->kader->name ?? $item->kader->nama ?? 'Kader' }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-xs font-black text-slate-500">Kehadiran</span>
                                    <span class="text-xs font-black text-emerald-700">{{ $persenItem }}%</span>
                                </div>

                                <div class="progress-track">
                                    <div class="progress-fill" style="width: {{ $persenItem }}%"></div>
                                </div>

                                <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-sm font-black text-slate-900">{{ number_format($peserta) }}</p>
                                        <p class="mt-1 text-[10px] font-black uppercase text-slate-400">Peserta</p>
                                    </div>

                                    <div class="rounded-2xl bg-emerald-50 p-3">
                                        <p class="text-sm font-black text-emerald-700">{{ number_format($hadir) }}</p>
                                        <p class="mt-1 text-[10px] font-black uppercase text-emerald-600">Hadir</p>
                                    </div>

                                    <div class="rounded-2xl bg-rose-50 p-3">
                                        <p class="text-sm font-black text-rose-600">{{ number_format($tidakHadir) }}</p>
                                        <p class="mt-1 text-[10px] font-black uppercase text-rose-500">Absen</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row xl:flex-col">
                                @if($routeHas('kader.absensi.show'))
                                    <a href="{{ route('kader.absensi.show', $item->id) }}" class="history-action history-primary inline-flex items-center justify-center gap-2 px-4 py-3 text-xs">
                                        <i class="fa-solid fa-eye"></i>
                                        Detail
                                    </a>
                                @endif

                                @if($routeHas('kader.absensi.index'))
                                    <a href="{{ route('kader.absensi.index', ['kategori' => $item->kategori]) }}" class="history-action history-outline inline-flex items-center justify-center gap-2 px-4 py-3 text-xs">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Update
                                    </a>
                                @endif

                                @if($routeHas('kader.absensi.destroy'))
                                    <form method="POST" action="{{ route('kader.absensi.destroy', $item->id) }}" class="delete-history-form">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="history-action inline-flex w-full items-center justify-center gap-2 rounded-[18px] border border-rose-200 bg-white px-4 py-3 text-xs font-black text-rose-600 hover:bg-rose-50">
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="emptySearchBox" class="mt-5 hidden rounded-[24px] border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-white text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <p class="text-sm font-black text-slate-500">Data tidak ditemukan dari pencarian.</p>
            </div>
        @else
            <div class="rounded-[26px] border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-3xl bg-white text-slate-400">
                    <i class="fa-regular fa-folder-open text-xl"></i>
                </div>

                <h3 class="text-lg font-black text-slate-800">Belum ada riwayat absensi</h3>
                <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-500">
                    Belum ada presensi pada filter yang dipilih. Buat presensi baru dulu, jangan cuma berharap data muncul dari alam gaib.
                </p>

                @if($routeHas('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index') }}" class="history-action history-primary mt-5 inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-plus"></i>
                        Buat Presensi
                    </a>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchRiwayat');
        const rows = Array.from(document.querySelectorAll('.history-row'));
        const emptySearchBox = document.getElementById('emptySearchBox');

        searchInput?.addEventListener('input', (event) => {
            const keyword = event.target.value.toLowerCase().trim();
            let visible = 0;

            rows.forEach(row => {
                const text = row.dataset.search || '';
                const match = text.includes(keyword);

                row.style.display = match ? '' : 'none';

                if (match) {
                    visible++;
                }
            });

            if (emptySearchBox) {
                emptySearchBox.classList.toggle('hidden', visible > 0 || keyword === '');
            }
        });

        document.querySelectorAll('.delete-history-form').forEach(form => {
            form.addEventListener('submit', (event) => {
                const ok = confirm('Yakin ingin menghapus riwayat presensi ini? Data detail kehadiran pada sesi ini juga akan terhapus.');

                if (!ok) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
@endpush