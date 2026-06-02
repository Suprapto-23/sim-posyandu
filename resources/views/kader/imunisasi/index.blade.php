@extends('layouts.kader')

@section('title', 'Log Imunisasi')
@section('page-name', 'Log Imunisasi')
@section('page-title', 'Log Imunisasi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $kategori = $kategori ?? request('kategori', 'semua');
    $search = $search ?? request('search', '');
    $bulan = (int) ($bulan ?? request('bulan', now()->month));
    $tahun = (int) ($tahun ?? request('tahun', now()->year));

    $totalData = method_exists($imunisasis, 'total') ? $imunisasis->total() : $imunisasis->count();

    $kategoriOptions = [
        'semua' => 'Semua Sasaran',
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    $bulanOptions = collect(range(1, 12))->mapWithKeys(function ($month) {
        return [
            $month => Carbon::create(now()->year, $month, 1)->locale('id')->translatedFormat('F'),
        ];
    });

    $tahunOptions = range(now()->year + 1, now()->year - 4);

    $activeFilterCount = collect([$kategori !== 'semua' ? $kategori : null, $search, $bulan !== now()->month ? $bulan : null, $tahun !== now()->year ? $tahun : null])
        ->filter(fn ($value) => filled($value))
        ->count();

    $lastUpdateLabel = $statLastUpdate
        ? Carbon::parse($statLastUpdate)->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i')
        : '-';
@endphp

@push('styles')
<style>
    html {
        scroll-behavior: auto !important;
    }

    html.pc-modal-open,
    body.pc-modal-open {
        overflow: hidden !important;
    }

    .pc-page {
        background:
            radial-gradient(circle at 8% 5%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 95% 8%, rgba(14, 165, 233, .13), transparent 26%),
            radial-gradient(circle at 50% 96%, rgba(251, 191, 36, .10), transparent 30%),
            linear-gradient(135deg, #f3fff9 0%, #eef9ff 48%, #f8fafc 100%);
    }

    .pc-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .pc-glass {
        background: rgba(255, 255, 255, .86);
        backdrop-filter: blur(9px);
        -webkit-backdrop-filter: blur(9px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .065);
    }

    .pc-soft-hover {
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .pc-soft-hover:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, .07);
    }

    .pc-table-wrap {
        overflow-x: auto;
        scrollbar-gutter: stable;
    }

    .pc-table-wrap::-webkit-scrollbar {
        height: 8px;
    }

    .pc-table-wrap::-webkit-scrollbar-track {
        background: rgba(226, 232, 240, .55);
        border-radius: 999px;
    }

    .pc-table-wrap::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, .38);
        border-radius: 999px;
    }

    .pc-stat-card {
        min-height: 118px;
    }

    .pc-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 2147483647 !important;
        display: none;
        align-items: center;
        justify-content: center;
        width: 100vw !important;
        height: 100vh !important;
        height: 100dvh !important;
        margin: 0 !important;
        padding: 1rem;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .pc-modal-backdrop.is-open {
        display: flex !important;
    }

    .pc-modal-card {
        width: min(100%, 470px);
        transform: translateY(12px) scale(.97);
        opacity: 0;
        border-radius: 1.75rem;
        border: 1px solid rgba(255, 255, 255, .78);
        background:
            radial-gradient(circle at 0% 0%, rgba(16, 185, 129, .14), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(14, 165, 233, .12), transparent 34%),
            rgba(255, 255, 255, .95);
        box-shadow: 0 30px 90px rgba(15, 23, 42, .25);
        transition: transform .18s ease, opacity .18s ease;
    }

    .pc-modal-backdrop.is-open .pc-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    @media (max-width: 1023px) {
        .pc-table-wrap {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="pc-page relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 pc-grid opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1400px] space-y-5">

        <section class="relative overflow-hidden rounded-[1.75rem] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-cyan-50/90 to-white/95 pc-glass">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-24 h-72 w-72 rounded-full bg-sky-300/16 blur-3xl"></div>

            <div class="relative grid gap-6 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.28fr)_350px] xl:items-stretch">
                <div class="flex min-w-0 flex-col justify-between gap-6">
                    <div>
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/75 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Read-only Kader
                        </div>

                        <h1 class="mt-4 max-w-4xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2rem]">
                            Log imunisasi sasaran, rapi dan siap dipantau.
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-slate-600">
                            Halaman ini dipakai Kader untuk melihat riwayat imunisasi yang dicatat oleh Bidan. Kader tidak mengubah data imunisasi, karena sistem yang sehat itu punya batas wewenang, bukan semua orang bebas pencet tombol seperti lift rusak.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('kader.dashboard') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5">
                            <i class="fa-solid fa-chart-line"></i>
                            Dashboard
                        </a>

                        <a href="{{ route('kader.jadwal.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-calendar-days"></i>
                            Jadwal Posyandu
                        </a>
                    </div>
                </div>

                <div class="pc-glass flex min-h-[220px] flex-col justify-between rounded-[1.55rem] border border-white/80 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Total Log</p>
                            <p class="mt-3 text-4xl font-black leading-none tracking-tight text-slate-950">{{ number_format($totalData) }}</p>
                            <p class="mt-2 text-sm font-bold text-slate-500">{{ $statBulanLabel ?? '-' }}</p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-700 to-slate-950 text-white shadow-lg shadow-slate-400/20">
                            <i class="fa-solid fa-syringe"></i>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[1.2rem] border border-emerald-200 bg-emerald-50/80 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black text-emerald-700">Update terakhir</p>
                                <p class="mt-1 text-xs font-semibold leading-5 text-slate-600">{{ $lastUpdateLabel }}</p>
                            </div>
                            <i class="fa-solid fa-shield-heart text-lg text-emerald-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">Balita</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($statBalita ?? 0) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Log imunisasi</p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-400/20">
                        <i class="fa-solid fa-child-reaching"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-violet-200 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-violet-700">Remaja</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($statRemaja ?? 0) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Log imunisasi</p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-lg shadow-violet-400/20">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-sky-700">Lansia</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($statLansia ?? 0) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Log imunisasi</p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-400/20">
                        <i class="fa-solid fa-person-cane"></i>
                    </div>
                </div>
            </div>
        </section>

        <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
            <form method="GET"
                  action="{{ route('kader.imunisasi.index') }}"
                  class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_180px_170px_150px_auto] xl:items-center">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="search"
                           name="search"
                           value="{{ $search }}"
                           autocomplete="off"
                           placeholder="Cari nama, NIK, vaksin, batch, atau penyelenggara..."
                           class="w-full rounded-2xl border border-slate-200 bg-white/85 py-3 pl-11 pr-4 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                </div>

                <select name="kategori"
                        class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                    @foreach($kategoriOptions as $key => $label)
                        <option value="{{ $key }}" @selected($kategori === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="bulan"
                        class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                    @foreach($bulanOptions as $key => $label)
                        <option value="{{ $key }}" @selected((int) $bulan === (int) $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="tahun"
                        class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                    @foreach($tahunOptions as $year)
                        <option value="{{ $year }}" @selected((int) $tahun === (int) $year)>{{ $year }}</option>
                    @endforeach
                </select>

                <div class="grid grid-cols-[1fr_auto] gap-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5">
                        <i class="fa-solid fa-filter"></i>
                        Filter
                    </button>

                    @if($activeFilterCount > 0)
                        <a href="{{ route('kader.imunisasi.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                           title="Bersihkan filter">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Daftar Log</p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Riwayat imunisasi tercatat</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">
                        {{ number_format($totalData) }} data ditemukan. Kader hanya melihat, Bidan yang mengelola data.
                    </p>
                </div>

                <span class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-700">
                    <i class="fa-solid fa-lock"></i>
                    Mode Baca Saja
                </span>
            </div>

            <div class="pc-table-wrap hidden lg:block">
                <table class="min-w-[1080px] w-full border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-left text-[11px] font-black uppercase tracking-[.16em] text-slate-400">
                            <th class="px-4 py-2">Penerima</th>
                            <th class="px-4 py-2">Tanggal</th>
                            <th class="px-4 py-2">Kategori</th>
                            <th class="px-4 py-2">Vaksin</th>
                            <th class="px-4 py-2">Dosis</th>
                            <th class="px-4 py-2">Batch</th>
                            <th class="px-4 py-2">Petugas</th>
                            <th class="px-4 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($imunisasis as $imunisasi)
                            @php
                                $kategoriTheme = $imunisasi->kategori_theme;
                                $badgeTheme = $imunisasi->badge_theme;
                                $nama = $imunisasi->nama_penerima;
                            @endphp

                            <tr class="pc-soft-hover rounded-[1.35rem] border border-slate-200 bg-white/80 shadow-sm">
                                <td class="rounded-l-[1.35rem] border-y border-l border-slate-200 px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $kategoriTheme['badge'] }} border text-sm font-black">
                                            {{ Str::upper(Str::substr($nama, 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="line-clamp-1 text-sm font-black text-slate-950">{{ $nama }}</p>
                                            <p class="mt-1 text-xs font-bold text-slate-500">NIK {{ $imunisasi->nik_penerima }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4 text-sm font-black text-slate-700">
                                    {{ $imunisasi->tanggal_label }}
                                    <p class="mt-1 text-xs font-bold text-slate-400">{{ $imunisasi->jam_label }}</p>
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-black {{ $kategoriTheme['badge'] }}">
                                        <i class="fa-solid {{ $kategoriTheme['icon'] }}"></i>
                                        {{ $kategoriTheme['label'] }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4">
                                    <p class="line-clamp-1 text-sm font-black text-slate-950">{{ $imunisasi->vaksin_label }}</p>
                                    <span class="mt-1 inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[11px] font-black {{ $badgeTheme['badge'] }}">
                                        <i class="fa-solid {{ $badgeTheme['icon'] }}"></i>
                                        {{ $badgeTheme['label'] }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4 text-sm font-black text-slate-700">
                                    {{ $imunisasi->dosis_label }}
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4 text-sm font-black text-slate-700">
                                    {{ $imunisasi->batch_label }}
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4 text-sm font-black text-slate-700">
                                    {{ $imunisasi->nama_petugas }}
                                </td>

                                <td class="rounded-r-[1.35rem] border-y border-r border-slate-200 px-4 py-4">
                                    <div class="flex justify-end">
                                        <a href="{{ route('kader.imunisasi.show', $imunisasi->id) }}"
                                           class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-700 shadow-sm transition hover:bg-slate-50">
                                            <i class="fa-solid fa-eye"></i>
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="rounded-[1.35rem] border border-dashed border-emerald-200 bg-emerald-50/70 p-8 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                                        <i class="fa-solid fa-folder-open"></i>
                                    </div>

                                    <h3 class="mt-4 text-base font-black text-slate-950">Log imunisasi belum ada</h3>
                                    <p class="mt-2 text-sm font-medium text-slate-500">
                                        Tidak ada data yang cocok dengan filter. Sistemnya kosong, bukan kamu yang halu.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 lg:hidden">
                @forelse($imunisasis as $imunisasi)
                    @php
                        $kategoriTheme = $imunisasi->kategori_theme;
                        $badgeTheme = $imunisasi->badge_theme;
                        $nama = $imunisasi->nama_penerima;
                    @endphp

                    <article class="pc-soft-hover rounded-[1.35rem] border border-slate-200 bg-white/85 p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $kategoriTheme['badge'] }} border text-sm font-black">
                                {{ Str::upper(Str::substr($nama, 0, 1)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="line-clamp-1 text-sm font-black text-slate-950">{{ $nama }}</h3>
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-black {{ $kategoriTheme['badge'] }}">
                                        <i class="fa-solid {{ $kategoriTheme['icon'] }}"></i>
                                        {{ $kategoriTheme['label'] }}
                                    </span>
                                </div>

                                <p class="mt-1 text-xs font-bold text-slate-500">NIK {{ $imunisasi->nik_penerima }}</p>

                                <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-bold text-slate-600">
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-slate-400">Tanggal</p>
                                        <p class="mt-1 text-slate-800">{{ $imunisasi->tanggal_label }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-slate-400">Dosis</p>
                                        <p class="mt-1 text-slate-800">{{ $imunisasi->dosis_label }}</p>
                                    </div>
                                </div>

                                <div class="mt-3 rounded-2xl border border-slate-200 bg-white/80 p-3">
                                    <p class="text-[11px] font-black uppercase tracking-[.14em] text-slate-400">Vaksin</p>
                                    <p class="mt-1 text-sm font-black text-slate-950">{{ $imunisasi->vaksin_label }}</p>
                                    <span class="mt-2 inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[11px] font-black {{ $badgeTheme['badge'] }}">
                                        <i class="fa-solid {{ $badgeTheme['icon'] }}"></i>
                                        {{ $badgeTheme['label'] }}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <a href="{{ route('kader.imunisasi.show', $imunisasi->id) }}"
                                       class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-black text-slate-700 shadow-sm">
                                        <i class="fa-solid fa-eye"></i>
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.35rem] border border-dashed border-emerald-200 bg-emerald-50/70 p-8 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>

                        <h3 class="mt-4 text-base font-black text-slate-950">Log imunisasi belum ada</h3>
                        <p class="mt-2 text-sm font-medium text-slate-500">
                            Tidak ada data yang cocok dengan filter.
                        </p>
                    </div>
                @endforelse
            </div>

            @if(method_exists($imunisasis, 'links'))
                <div class="mt-5">
                    {{ $imunisasis->links() }}
                </div>
            @endif
        </section>
    </div>

    @if(session('success') || session('error'))
        <div id="pcInfoModal" class="pc-modal-backdrop" aria-hidden="true">
            <div class="pc-modal-card p-6">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] {{ session('error') ? 'bg-gradient-to-br from-rose-500 to-orange-500 shadow-rose-500/20' : 'bg-gradient-to-br from-emerald-500 to-teal-500 shadow-emerald-500/20' }} text-white shadow-lg">
                        <i class="fa-solid {{ session('error') ? 'fa-triangle-exclamation' : 'fa-circle-check' }} text-xl"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] {{ session('error') ? 'text-rose-700' : 'text-emerald-700' }}">
                            Informasi
                        </p>
                        <h3 class="mt-1 text-lg font-black text-slate-950">
                            {{ session('error') ? 'Ada kendala' : 'Berhasil' }}
                        </h3>
                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                            {{ session('error') ?: session('success') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button"
                            id="pcCloseInfo"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-900/10">
                        <i class="fa-solid fa-check"></i>
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    let modal = document.querySelector('#pcInfoModal');

    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    const closeBtn = document.querySelector('#pcCloseInfo');

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function openInfo() {
        if (!modal) return;

        lockBody();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeInfo() {
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    if (modal) {
        requestAnimationFrame(openInfo);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeInfo();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeInfo);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeInfo();
        }
    });
})();
</script>
@endpush