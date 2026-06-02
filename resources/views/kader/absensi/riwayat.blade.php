@extends('layouts.kader')

@section('title', 'Riwayat Absensi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $riwayats = $riwayats ?? collect();
    $bulanAktif = (int) ($bulan ?? now('Asia/Jakarta')->month);
    $tahunAktif = (int) ($tahun ?? now('Asia/Jakarta')->year);
    $kategoriAktif = $kategori ?? request('kategori');
    $searchAktif = $search ?? request('search', '');

    $bulanList = [
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

    $tahunSekarang = (int) now('Asia/Jakarta')->year;
    $tahunList = range($tahunSekarang + 1, max(2024, $tahunSekarang - 4));

    $kategoriMenus = [
        '' => [
            'label' => 'Semua',
            'caption' => 'Semua kategori',
            'icon' => 'fa-layer-group',
            'tone' => 'slate',
        ],
        'balita' => [
            'label' => 'Balita',
            'caption' => 'Data anak',
            'icon' => 'fa-child-reaching',
            'tone' => 'emerald',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'caption' => 'Usia remaja',
            'icon' => 'fa-user-graduate',
            'tone' => 'violet',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'caption' => 'Usia lanjut',
            'icon' => 'fa-person-cane',
            'tone' => 'sky',
        ],
    ];

    $routeHas = fn ($name) => Route::has($name);
    $routeUrl = fn ($name, $params = []) => Route::has($name) ? route($name, $params) : '#';

    $kategoriLabel = function ($kategori) {
        return match ($kategori) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => 'Semua Kategori',
        };
    };

    $formatTanggal = function ($date, $format = 'd M Y') {
        if (!$date) return '-';

        try {
            return Carbon::parse($date)->translatedFormat($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $badgeClass = function ($tone) {
        return match ($tone) {
            'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'sky' => 'border-sky-200 bg-sky-50 text-sky-700',
            'violet' => 'border-violet-200 bg-violet-50 text-violet-700',
            'orange' => 'border-orange-200 bg-orange-50 text-orange-700',
            'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
            'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
            default => 'border-slate-200 bg-slate-50 text-slate-700',
        };
    };

    $toneClass = function ($tone, $type = 'soft') {
        return match ($tone) {
            'violet' => match ($type) {
                'solid' => 'bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-violet-400/20',
                default => 'from-violet-50 via-fuchsia-50 to-white border-violet-200 text-violet-800',
            },
            'sky' => match ($type) {
                'solid' => 'bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-sky-400/20',
                default => 'from-sky-50 via-cyan-50 to-white border-sky-200 text-sky-800',
            },
            'slate' => match ($type) {
                'solid' => 'bg-gradient-to-br from-slate-600 to-slate-800 text-white shadow-slate-400/20',
                default => 'from-slate-50 via-white to-white border-slate-200 text-slate-800',
            },
            default => match ($type) {
                'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-emerald-400/20',
                default => 'from-emerald-50 via-teal-50 to-white border-emerald-200 text-emerald-800',
            },
        };
    };

    $totalSesi = $riwayats->count();
    $totalPeserta = $riwayats->sum(fn ($item) => (int) ($item->total_peserta ?? 0));
    $totalHadir = $riwayats->sum(fn ($item) => (int) ($item->total_hadir ?? 0));
    $totalTidakHadir = $riwayats->sum(fn ($item) => (int) ($item->total_tidak_hadir ?? 0));
    $persenHadir = $totalPeserta > 0 ? round(($totalHadir / $totalPeserta) * 100, 1) : 0;
@endphp

@push('styles')
<style>
    .kader-shell {
        background:
            radial-gradient(circle at 8% 5%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 95% 8%, rgba(14, 165, 233, .13), transparent 26%),
            radial-gradient(circle at 50% 96%, rgba(251, 191, 36, .10), transparent 30%),
            linear-gradient(135deg, #f3fff9 0%, #eef9ff 48%, #f8fafc 100%);
    }

    .kader-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .glass-card {
        background: rgba(255, 255, 255, .84);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .soft-card {
        box-shadow: 0 16px 40px rgba(15, 23, 42, .065);
    }

    .history-list {
        max-height: 720px;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        padding-right: 8px;
    }

    .history-list::-webkit-scrollbar {
        width: 7px;
    }

    .history-list::-webkit-scrollbar-track {
        background: rgba(226, 232, 240, .50);
        border-radius: 999px;
    }

    .history-list::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, .42);
        border-radius: 999px;
    }

    .history-row {
        transition: border-color .16s ease, background .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .history-row:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 26px rgba(15, 23, 42, .065);
    }

    @media (max-width: 1279px) {
        .history-list {
            max-height: none;
            overflow: visible;
            padding-right: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="kader-shell relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 kader-grid opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1400px] space-y-5">

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[1.75rem] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-cyan-50/90 to-white/95 soft-card">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-24 h-72 w-72 rounded-full bg-sky-300/16 blur-3xl"></div>

            <div class="relative grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,.7fr)] xl:items-stretch">
                <div class="flex min-w-0 flex-col justify-between gap-5">
                    <div>
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/75 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Attendance History
                        </div>

                        <h1 class="mt-4 max-w-3xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2.05rem]">
                            Riwayat absensi Posyandu tersusun rapi.
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-600">
                            Pantau sesi presensi berdasarkan bulan, tahun, dan kategori. Ini halaman bukti kerja, bukan tempat angka jalan-jalan tanpa arah.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if($routeHas('kader.absensi.index'))
                            <a href="{{ route('kader.absensi.index', ['kategori' => $kategoriAktif ?: 'balita']) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5">
                                <i class="fa-solid fa-user-check"></i>
                                Input Absensi
                            </a>
                        @endif

                        @if($routeHas('kader.dashboard'))
                            <a href="{{ route('kader.dashboard') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                                <i class="fa-solid fa-chart-line"></i>
                                Dashboard
                            </a>
                        @endif
                    </div>
                </div>

                <div class="grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.45rem] border border-white/80 bg-white/75 p-4 shadow-md shadow-emerald-100/60">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Periode</p>
                            <span class="shrink-0 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-black text-emerald-700">
                                {{ $bulanList[$bulanAktif] ?? '-' }} {{ $tahunAktif }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-400/20">
                                <i class="fa-solid fa-calendar-days text-lg"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-950">{{ $kategoriLabel($kategoriAktif) }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ number_format($totalSesi) }} sesi ditemukan</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.45rem] border border-white/80 bg-white/75 p-4 shadow-md shadow-sky-100/60">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-400/20">
                                <i class="fa-solid fa-chart-simple"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-950">{{ $persenHadir }}% hadir</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ number_format($totalHadir) }} dari {{ number_format($totalPeserta) }} catatan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ALERT --}}
        @if(session('success') || session('error'))
            <section class="space-y-3">
                @if(session('success'))
                    <div class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50/90 p-4 text-sm font-bold text-emerald-800 shadow-sm">
                        <i class="fa-solid fa-circle-check mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded-[1.35rem] border border-rose-200 bg-rose-50/90 p-4 text-sm font-bold text-rose-800 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif
            </section>
        @endif

        {{-- SUMMARY --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[1.55rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white p-4 soft-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">Total Sesi</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalSesi) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Sesi absensi</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-400/20">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.55rem] border border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white p-4 soft-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-sky-700">Total Peserta</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalPeserta) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Catatan detail</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-400/20">
                        <i class="fa-solid fa-users-line"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.55rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-green-50 to-white p-4 soft-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">Total Hadir</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalHadir) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $persenHadir }}% kehadiran</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-green-500 text-white shadow-lg shadow-emerald-400/20">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.55rem] border border-orange-200 bg-gradient-to-br from-orange-50 via-amber-50 to-white p-4 soft-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-orange-700">Tidak Hadir</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalTidakHadir) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Catatan absen</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 text-white shadow-lg shadow-orange-400/20">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                </div>
            </div>
        </section>

        {{-- FILTER --}}
        <section class="glass-card rounded-[1.75rem] border border-white/80 p-5 soft-card">
            <div class="mb-4">
                <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Filter Data</p>
                <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950">Cari riwayat absensi</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Gunakan filter seperlunya. Jangan semua diisi kalau tujuannya cuma bikin halaman terlihat sibuk.</p>
            </div>

            <form method="GET" action="{{ $routeUrl('kader.absensi.riwayat') }}" class="grid gap-3 xl:grid-cols-[1fr_180px_160px_190px_auto] xl:items-end">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.14em] text-slate-500">Pencarian</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text"
                               name="search"
                               value="{{ $searchAktif }}"
                               placeholder="Kode absensi, tanggal, kategori..."
                               class="w-full rounded-2xl border border-slate-200 bg-white/85 py-3 pl-11 pr-4 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.14em] text-slate-500">Kategori</label>
                    <select name="kategori"
                            class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                        <option value="">Semua</option>
                        <option value="balita" @selected($kategoriAktif === 'balita')>Balita</option>
                        <option value="remaja" @selected($kategoriAktif === 'remaja')>Remaja</option>
                        <option value="lansia" @selected($kategoriAktif === 'lansia')>Lansia</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.14em] text-slate-500">Bulan</label>
                    <select name="bulan"
                            class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                        @foreach($bulanList as $number => $label)
                            <option value="{{ $number }}" @selected($bulanAktif === $number)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.14em] text-slate-500">Tahun</label>
                    <select name="tahun"
                            class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                        @foreach($tahunList as $year)
                            <option value="{{ $year }}" @selected($tahunAktif === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="inline-flex h-[46px] items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
                        <i class="fa-solid fa-filter"></i>
                        Filter
                    </button>

                    <a href="{{ $routeUrl('kader.absensi.riwayat') }}"
                       class="inline-flex h-[46px] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white/80 px-4 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </section>

        {{-- CATEGORY QUICK FILTER --}}
        <section class="grid gap-3 md:grid-cols-4">
            @foreach($kategoriMenus as $key => $item)
                @php
                    $active = (string) $kategoriAktif === (string) $key || ($key === '' && blank($kategoriAktif));
                    $url = route('kader.absensi.riwayat', [
                        'kategori' => $key ?: null,
                        'bulan' => $bulanAktif,
                        'tahun' => $tahunAktif,
                        'search' => $searchAktif ?: null,
                    ]);
                @endphp

                <a href="{{ $url }}"
                   class="rounded-[1.35rem] border p-4 transition hover:-translate-y-0.5
                          {{ $active ? 'bg-gradient-to-br ' . $toneClass($item['tone']) . ' shadow-md' : 'border-slate-200 bg-white/70 hover:border-emerald-200' }}">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $toneClass($item['tone'], 'solid') }}">
                            <i class="fa-solid {{ $item['icon'] }}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-black text-slate-950">{{ $item['label'] }}</p>
                            <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">{{ $item['caption'] }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </section>

        {{-- LIST --}}
        <section class="glass-card rounded-[1.75rem] border border-white/80 p-5 soft-card">
            <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Daftar Riwayat</p>
                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950 sm:text-xl">
                        Sesi absensi {{ $bulanList[$bulanAktif] ?? '-' }} {{ $tahunAktif }}
                    </h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Menampilkan {{ number_format($totalSesi) }} sesi absensi.
                    </p>
                </div>
            </div>

            @if($riwayats->isNotEmpty())
                <div class="history-list space-y-3">
                    @foreach($riwayats as $index => $item)
                        @php
                            $peserta = (int) ($item->total_peserta ?? 0);
                            $hadir = (int) ($item->total_hadir ?? 0);
                            $tidak = (int) ($item->total_tidak_hadir ?? 0);
                            $persen = $peserta > 0 ? round(($hadir / $peserta) * 100, 1) : 0;

                            $statusBadge = $item->status_rekap_badge ?? 'slate';
                            $statusText = $item->status_rekap_text ?? 'Belum Ada Data';

                            $kategoriItem = $item->kategori ?? '-';
                            $kategoriTone = match ($kategoriItem) {
                                'remaja' => 'violet',
                                'lansia' => 'sky',
                                default => 'emerald',
                            };
                        @endphp

                        <div class="history-row rounded-[1.45rem] border border-slate-200 bg-white/80 p-4">
                            <div class="grid gap-4 xl:grid-cols-[54px_minmax(0,1fr)_360px] xl:items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $toneClass($kategoriTone, 'solid') }} text-sm font-black">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="line-clamp-1 text-base font-black text-slate-950">
                                            {{ $item->kode_absensi ?? 'ABS-' . str_pad($item->id, 4, '0', STR_PAD_LEFT) }}
                                        </h3>

                                        <span class="rounded-full border px-2.5 py-1 text-[10px] font-black {{ $badgeClass($kategoriTone) }}">
                                            {{ $kategoriLabel($kategoriItem) }}
                                        </span>

                                        <span class="rounded-full border px-2.5 py-1 text-[10px] font-black {{ $badgeClass($statusBadge) }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold text-slate-500">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                            <i class="fa-solid fa-calendar-day text-slate-400"></i>
                                            {{ $formatTanggal($item->tanggal_posyandu ?? null, 'd F Y') }}
                                        </span>

                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                            <i class="fa-solid fa-hashtag text-slate-400"></i>
                                            Pertemuan ke-{{ $item->nomor_pertemuan ?? '-' }}
                                        </span>

                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                            <i class="fa-solid fa-user-pen text-slate-400"></i>
                                            {{ $item->kader?->name ?? 'Kader' }}
                                        </span>
                                    </div>

                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500"
                                             style="width: {{ min(100, $persen) }}%"></div>
                                    </div>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="rounded-2xl bg-slate-50 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-500">Peserta</p>
                                            <p class="mt-1 text-lg font-black text-slate-950">{{ number_format($peserta) }}</p>
                                        </div>

                                        <div class="rounded-2xl bg-emerald-50 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">Hadir</p>
                                            <p class="mt-1 text-lg font-black text-slate-950">{{ number_format($hadir) }}</p>
                                        </div>

                                        <div class="rounded-2xl bg-orange-50 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[.12em] text-orange-700">Tidak</p>
                                            <p class="mt-1 text-lg font-black text-slate-950">{{ number_format($tidak) }}</p>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        @if($routeHas('kader.absensi.show'))
                                            <a href="{{ route('kader.absensi.show', $item->id) }}"
                                               class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-300/50 transition hover:-translate-y-0.5 hover:bg-emerald-500"
                                               title="Lihat detail">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        @endif

                                        @if($routeHas('kader.absensi.destroy'))
                                            <form method="POST"
                                                  action="{{ route('kader.absensi.destroy', $item->id) }}"
                                                  class="delete-history-form">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:-translate-y-0.5 hover:bg-rose-100"
                                                        title="Hapus riwayat">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-[1.55rem] border border-dashed border-emerald-200 bg-emerald-50/65 p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                        <i class="fa-solid fa-calendar-xmark text-lg"></i>
                    </div>
                    <h3 class="mt-4 text-base font-black text-slate-950">Riwayat absensi belum tersedia</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm font-medium leading-6 text-slate-500">
                        Belum ada sesi absensi untuk filter ini. Coba ganti bulan, tahun, kategori, atau mulai input absensi baru.
                    </p>

                    @if($routeHas('kader.absensi.index'))
                        <a href="{{ route('kader.absensi.index', ['kategori' => $kategoriAktif ?: 'balita']) }}"
                           class="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
                            <i class="fa-solid fa-user-check"></i>
                            Input Absensi
                        </a>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('.delete-history-form');

        if (!form) return;

        const confirmed = confirm('Hapus riwayat absensi ini? Detail kehadiran di dalamnya juga akan dihapus.');

        if (!confirmed) {
            event.preventDefault();
            return;
        }

        const button = form.querySelector('button[type="submit"]');

        if (button) {
            button.disabled = true;
            button.classList.add('opacity-70', 'cursor-not-allowed');
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        }
    });
})();
</script>
@endpush