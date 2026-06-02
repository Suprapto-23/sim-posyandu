@extends('layouts.kader')

@section('title', 'Data Balita')
@section('page-name', 'Data Balita')
@section('page-title', 'Data Balita')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $search = $search ?? request('search', '');
    $statusAkun = $statusAkun ?? request('status_akun', 'semua');

    $totalData = method_exists($items, 'total') ? $items->total() : $items->count();

    $statTotal = $statTotal ?? $totalData;
    $statTerhubung = $statTerhubung ?? 0;
    $statBelumTerhubung = $statBelumTerhubung ?? 0;
    $statBulanIni = $statBulanIni ?? 0;

    $statusOptions = [
        'semua' => 'Semua Status',
        'terhubung' => 'Akun Terhubung',
        'belum' => 'Belum Terhubung',
    ];

    $formatTanggal = function ($value) {
        if (! $value) {
            return '-';
        }

        return Carbon::parse($value)->translatedFormat('d M Y');
    };

    $usiaBalita = function ($balita) {
        if (isset($balita->usia_label) && filled($balita->usia_label)) {
            return $balita->usia_label;
        }

        if (! $balita->tanggal_lahir) {
            return '-';
        }

        $bulan = (int) Carbon::parse($balita->tanggal_lahir)->diffInMonths(now());

        if ($bulan < 12) {
            return $bulan . ' bulan';
        }

        $tahun = intdiv($bulan, 12);
        $sisa = $bulan % 12;

        return $sisa > 0 ? $tahun . ' tahun ' . $sisa . ' bulan' : $tahun . ' tahun';
    };

    $initialName = function ($name) {
        return Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'B';
    };

    $genderLabel = function ($value) {
        return match ($value) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    };

    $genderClass = function ($value) {
        return match ($value) {
            'L' => 'border-sky-200 bg-sky-50 text-sky-700',
            'P' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-50 text-slate-600',
        };
    };

    $accountConnected = function ($balita) {
        return filled($balita->user_id ?? null);
    };

    $accountLabel = function ($balita) use ($accountConnected) {
        return $accountConnected($balita) ? 'Terhubung' : 'Belum Terhubung';
    };

    $accountClass = function ($balita) use ($accountConnected) {
        return $accountConnected($balita)
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-amber-200 bg-amber-50 text-amber-700';
    };

    $lastCheckLabel = function ($balita) use ($formatTanggal) {
        $last = $balita->pemeriksaan_terakhir ?? null;

        if (! $last) {
            return 'Belum Periksa';
        }

        $tanggal = $last->tanggal_periksa ?? $last->created_at ?? null;

        return $formatTanggal($tanggal);
    };

    $lastWeight = function ($balita) {
        $last = $balita->pemeriksaan_terakhir ?? null;

        if (! $last || blank($last->berat_badan ?? null)) {
            return '-';
        }

        return rtrim(rtrim((string) $last->berat_badan, '0'), '.') . ' kg';
    };

    $lastHeight = function ($balita) {
        $last = $balita->pemeriksaan_terakhir ?? null;

        if (! $last || blank($last->tinggi_badan ?? null)) {
            return '-';
        }

        return rtrim(rtrim((string) $last->tinggi_badan, '0'), '.') . ' cm';
    };

    $rangeText = method_exists($items, 'firstItem')
        ? 'Menampilkan ' . (($items->firstItem() ?? 0)) . ' sampai ' . (($items->lastItem() ?? 0)) . ' dari ' . $items->total() . ' data Balita'
        : 'Menampilkan ' . $items->count() . ' data Balita';

    $importRoute = Route::has('kader.import.index')
        ? route('kader.import.index', ['type' => 'balita'])
        : null;

    $templateRoute = Route::has('kader.import.template')
        ? route('kader.import.template', ['type' => 'balita'])
        : null;
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

    .pc-stat-card {
        min-height: 116px;
    }

    .pc-field {
        width: 100%;
        border-radius: 1rem;
        border: 1px solid rgba(203, 213, 225, .95);
        background: rgba(255, 255, 255, .9);
        padding: .82rem 1rem;
        font-size: .875rem;
        font-weight: 800;
        color: #334155;
        outline: none;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .035);
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .pc-field:focus {
        border-color: rgba(16, 185, 129, .55);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .10);
        background: rgba(255, 255, 255, .98);
    }

    .pc-label {
        display: block;
        margin-bottom: .45rem;
        font-size: .72rem;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #64748b;
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

    .pc-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .pc-table th {
        padding: 0 14px 8px 14px;
        text-align: left;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #94a3b8;
        white-space: nowrap;
    }

    .pc-table td {
        padding: 12px 14px;
        background: rgba(255, 255, 255, .88);
        border-top: 1px solid rgba(226, 232, 240, .95);
        border-bottom: 1px solid rgba(226, 232, 240, .95);
        vertical-align: middle;
    }

    .pc-table td:first-child {
        border-left: 1px solid rgba(226, 232, 240, .95);
        border-top-left-radius: 1.2rem;
        border-bottom-left-radius: 1.2rem;
    }

    .pc-table td:last-child {
        border-right: 1px solid rgba(226, 232, 240, .95);
        border-top-right-radius: 1.2rem;
        border-bottom-right-radius: 1.2rem;
    }

    .pc-table-row {
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .pc-table-row:hover td {
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 14px 32px rgba(15, 23, 42, .055);
    }

    .pc-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        border-width: 1px;
        border-radius: 999px;
        padding: .28rem .6rem;
        font-size: 10.5px;
        font-weight: 950;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .pc-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .42rem;
        min-height: 36px;
        border-radius: .9rem;
        padding: .48rem .75rem;
        font-size: 12px;
        font-weight: 950;
        transition: transform .16s ease, background .16s ease, border-color .16s ease;
        white-space: nowrap;
    }

    .pc-action-btn:hover {
        transform: translateY(-1px);
    }

    .pc-mobile-card {
        display: none;
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
        width: min(100%, 480px);
        transform: translateY(12px) scale(.97);
        opacity: 0;
        border-radius: 1.75rem;
        border: 1px solid rgba(255, 255, 255, .78);
        background:
            radial-gradient(circle at 0% 0%, rgba(16, 185, 129, .14), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(14, 165, 233, .12), transparent 34%),
            rgba(255, 255, 255, .96);
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

        .pc-mobile-card {
            display: block;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
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

            <div class="relative grid gap-6 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.3fr)_360px] xl:items-stretch">
                <div class="flex min-w-0 flex-col justify-between gap-6">
                    <div>
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/75 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Balita Data Center
                        </div>

                        <h1 class="mt-4 max-w-4xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2rem]">
                            Data sasaran Balita, rapi untuk absensi dan pengukuran.
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-slate-600">
                            Kelola identitas Balita sebagai dasar absensi, pengukuran fisik, imunisasi, dan laporan. Pencarian dibuat tegas: huruf membaca nama Balita, angka membaca NIK Balita.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('kader.data.balita.create') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5">
                            <i class="fa-solid fa-plus"></i>
                            Tambah Balita
                        </a>

                        @if($importRoute)
                            <a href="{{ $importRoute }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                                <i class="fa-solid fa-file-import"></i>
                                Import Data
                            </a>
                        @endif

                        @if($templateRoute)
                            <a href="{{ $templateRoute }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-sky-200 bg-sky-50/80 px-4 py-2.5 text-sm font-black text-sky-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                                <i class="fa-solid fa-file-excel"></i>
                                Template Excel
                            </a>
                        @endif
                    </div>
                </div>

                <div class="pc-glass flex min-h-[230px] flex-col justify-between rounded-[1.55rem] border border-white/80 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Total Balita</p>
                            <p class="mt-3 text-4xl font-black leading-none tracking-tight text-slate-950">{{ number_format($statTotal) }}</p>
                            <p class="mt-2 text-sm font-bold text-slate-500">Seluruh data sasaran</p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-400/20">
                            <i class="fa-solid fa-child-reaching"></i>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[1.2rem] border border-emerald-200 bg-emerald-50/80 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black text-emerald-700">Kunci sinkron</p>
                                <p class="mt-1 text-xs font-semibold leading-5 text-slate-600">
                                    Akun warga dihubungkan memakai NIK Balita.
                                </p>
                            </div>
                            <i class="fa-solid fa-link text-lg text-emerald-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FLEXBOX STATISTIK TETAP --}}
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">Total Balita</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($statTotal) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Seluruh data sasaran</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-400/20">
                        <i class="fa-solid fa-database"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-sky-700">Terhubung</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($statTerhubung) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Akun warga tersedia</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-400/20">
                        <i class="fa-solid fa-link"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-amber-200 bg-gradient-to-br from-amber-50 via-yellow-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-amber-700">Belum Terhubung</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($statBelumTerhubung) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Perlu sinkron akun</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-400/20">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-violet-200 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-violet-700">Bulan Ini</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($statBulanIni) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Data baru tercatat</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-lg shadow-violet-400/20">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                </div>
            </div>
        </section>

        {{-- FILTER --}}
        <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
            <form id="balitaFilterForm"
                  method="GET"
                  action="{{ route('kader.data.balita.index') }}"
                  class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_260px_auto_auto] lg:items-end">
                <div>
                    <label class="pc-label">Cari Balita</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                        <input type="search"
                               id="balitaLiveSearch"
                               name="search"
                               value="{{ $search }}"
                               autocomplete="off"
                               placeholder="Ketik nama Balita atau NIK Balita..."
                               class="pc-field pl-11 pr-11">

                        <button type="button"
                                id="clearLiveSearch"
                                class="absolute right-3 top-1/2 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                aria-label="Bersihkan pencarian">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <p class="mt-2 text-xs font-bold text-slate-500">
                        Huruf mencari nama Balita. Angka mencari NIK Balita.
                    </p>
                </div>

                <div>
                    <label class="pc-label">Status Akun</label>
                    <select name="status_akun"
                            id="statusAkunFilter"
                            class="pc-field">
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected($statusAkun === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="inline-flex h-[48px] items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5">
                    <i class="fa-solid fa-filter"></i>
                    Filter
                </button>

                <a href="{{ route('kader.data.balita.index') }}"
                   class="inline-flex h-[48px] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fa-solid fa-rotate-left"></i>
                    Reset
                </a>
            </form>
        </section>

        {{-- DAFTAR BALITA BARU --}}
        <section id="balitaListBlock" class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
            <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Daftar Balita</p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Data sasaran Balita</h2>
                    <p id="balitaHeaderSubtitle" class="mt-1 text-sm font-medium text-slate-500">
                        {{ $rangeText }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span id="balitaVisibleCounter"
                          class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs font-black uppercase tracking-[.14em] text-emerald-700">
                        {{ number_format($totalData) }} Data
                    </span>

                    <button type="button"
                            id="selectAllBalita"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-regular fa-square-check"></i>
                        Pilih Semua
                    </button>

                    <button type="button"
                            id="bulkDeleteTrigger"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-black text-rose-700 shadow-sm transition hover:bg-rose-100">
                        <i class="fa-solid fa-trash"></i>
                        Hapus Terpilih
                    </button>
                </div>
            </div>

            <div id="balitaListArea">
                <div class="pc-table-wrap">
                    <table class="pc-table">
                        <thead>
                            <tr>
                                <th class="w-[44px]"></th>
                                <th class="w-[320px]">Nama Balita</th>
                                <th class="w-[170px]">NIK Balita</th>
                                <th class="w-[125px]">Usia</th>
                                <th class="w-[135px]">Jenis Kelamin</th>
                                <th class="w-[210px]">Alamat</th>
                                <th class="w-[180px]">Status</th>
                                <th class="w-[230px] text-right">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="balitaTableBody">
                            @forelse($items as $item)
                                @php
                                    $nama = $item->nama_lengkap ?? '-';
                                    $nik = $item->nik ?? '-';
                                    $connected = $accountConnected($item);
                                @endphp

                                <tr class="pc-table-row"
                                    data-live-row
                                    data-name="{{ Str::lower($nama) }}"
                                    data-nik="{{ $nik }}"
                                    data-account="{{ $connected ? 'terhubung' : 'belum' }}">
                                    <td>
                                        <input type="checkbox"
                                               value="{{ $item->id }}"
                                               class="balita-checkbox h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    </td>

                                    <td>
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-sm font-black text-white shadow-lg shadow-emerald-400/20">
                                                {{ $initialName($nama) }}
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-black text-slate-950">{{ $nama }}</p>

                                                <div class="mt-1 flex flex-wrap gap-1.5">
                                                    <span class="pc-chip border-emerald-200 bg-emerald-50 text-emerald-700">Balita</span>
                                                    <span class="pc-chip {{ $accountClass($item) }}">{{ $accountLabel($item) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <p class="text-sm font-black text-slate-700">{{ $nik }}</p>
                                    </td>

                                    <td>
                                        <p class="text-sm font-black text-slate-800">{{ $usiaBalita($item) }}</p>
                                        <p class="mt-0.5 text-[11px] font-bold text-slate-400">Maks. 59 bulan</p>
                                    </td>

                                    <td>
                                        <span class="pc-chip {{ $genderClass($item->jenis_kelamin) }}">
                                            {{ $genderLabel($item->jenis_kelamin) }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="flex min-w-0 items-start gap-2">
                                            <i class="fa-solid fa-location-dot mt-1 text-emerald-500"></i>
                                            <p class="line-clamp-2 text-sm font-semibold leading-5 text-slate-600">
                                                {{ $item->alamat ?: '-' }}
                                            </p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="space-y-1">
                                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600">
                                                {{ $lastCheckLabel($item) }}
                                            </span>
                                            <p class="text-[11px] font-bold text-slate-400">
                                                BB {{ $lastWeight($item) }} | TB {{ $lastHeight($item) }}
                                            </p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="flex justify-end gap-2">
                                            @if(! $connected)
                                                <form action="{{ route('kader.data.balita.sync', $item->id) }}"
                                                      method="POST"
                                                      data-confirm-form
                                                      data-confirm-type="sync"
                                                      data-confirm-title="Sinkron akun warga?"
                                                      data-confirm-message="Sistem akan mencari akun warga berdasarkan NIK Balita {{ $nik }}."
                                                      data-confirm-button="Sinkron">
                                                    @csrf

                                                    <button type="submit"
                                                            class="pc-action-btn border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100">
                                                        <i class="fa-solid fa-link"></i>
                                                        Sinkron
                                                    </button>
                                                </form>
                                            @endif

                                            <a href="{{ route('kader.data.balita.show', $item->id) }}"
                                               class="pc-action-btn border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                                                <i class="fa-solid fa-eye"></i>
                                                Detail
                                            </a>

                                            <a href="{{ route('kader.data.balita.edit', $item->id) }}"
                                               class="pc-action-btn border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100">
                                                <i class="fa-solid fa-pen"></i>
                                                Edit
                                            </a>

                                            <form action="{{ route('kader.data.balita.destroy', $item->id) }}"
                                                  method="POST"
                                                  data-confirm-form
                                                  data-confirm-type="delete"
                                                  data-confirm-title="Hapus data Balita?"
                                                  data-confirm-message="Data {{ $nama }} akan dihapus jika belum memiliki riwayat kunjungan atau pengukuran."
                                                  data-confirm-button="Hapus Data">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="pc-action-btn border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">
                                                    <i class="fa-solid fa-trash"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <article class="pc-mobile-card pc-soft-hover rounded-[1.4rem] border border-slate-200 bg-white/88 p-4 shadow-sm"
                                         data-live-row
                                         data-name="{{ Str::lower($nama) }}"
                                         data-nik="{{ $nik }}"
                                         data-account="{{ $connected ? 'terhubung' : 'belum' }}">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox"
                                               value="{{ $item->id }}"
                                               class="balita-checkbox mt-2 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">

                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-sm font-black text-white shadow-lg shadow-emerald-400/20">
                                            {{ $initialName($nama) }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <h3 class="truncate text-base font-black text-slate-950">{{ $nama }}</h3>
                                            <p class="mt-1 text-xs font-black text-slate-500">NIK {{ $nik }}</p>

                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                <span class="pc-chip border-emerald-200 bg-emerald-50 text-emerald-700">Balita</span>
                                                <span class="pc-chip {{ $genderClass($item->jenis_kelamin) }}">{{ $genderLabel($item->jenis_kelamin) }}</span>
                                                <span class="pc-chip {{ $accountClass($item) }}">{{ $accountLabel($item) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                                            <p class="text-[11px] font-black uppercase tracking-[.12em] text-slate-400">Usia</p>
                                            <p class="mt-1 text-sm font-black text-slate-800">{{ $usiaBalita($item) }}</p>
                                        </div>

                                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                                            <p class="text-[11px] font-black uppercase tracking-[.12em] text-slate-400">Periksa</p>
                                            <p class="mt-1 text-sm font-black text-slate-800">{{ $lastCheckLabel($item) }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-3 rounded-2xl border border-slate-200 bg-white/80 p-3">
                                        <p class="text-sm font-semibold leading-6 text-slate-600">
                                            <i class="fa-solid fa-location-dot mr-1 text-emerald-500"></i>
                                            {{ $item->alamat ?: '-' }}
                                        </p>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2">
                                        @if(! $connected)
                                            <form action="{{ route('kader.data.balita.sync', $item->id) }}"
                                                  method="POST"
                                                  data-confirm-form
                                                  data-confirm-type="sync"
                                                  data-confirm-title="Sinkron akun warga?"
                                                  data-confirm-message="Sistem akan mencari akun warga berdasarkan NIK Balita {{ $nik }}."
                                                  data-confirm-button="Sinkron">
                                                @csrf

                                                <button type="submit"
                                                        class="pc-action-btn w-full border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100">
                                                    <i class="fa-solid fa-link"></i>
                                                    Sinkron
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('kader.data.balita.show', $item->id) }}"
                                           class="pc-action-btn border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                                            <i class="fa-solid fa-eye"></i>
                                            Detail
                                        </a>

                                        <a href="{{ route('kader.data.balita.edit', $item->id) }}"
                                           class="pc-action-btn border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100">
                                            <i class="fa-solid fa-pen"></i>
                                            Edit
                                        </a>

                                        <form action="{{ route('kader.data.balita.destroy', $item->id) }}"
                                              method="POST"
                                              data-confirm-form
                                              data-confirm-type="delete"
                                              data-confirm-title="Hapus data Balita?"
                                              data-confirm-message="Data {{ $nama }} akan dihapus jika belum memiliki riwayat kunjungan atau pengukuran."
                                              data-confirm-button="Hapus Data">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="pc-action-btn w-full border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">
                                                <i class="fa-solid fa-trash"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="rounded-[1.4rem] border border-dashed border-emerald-200 bg-emerald-50/70 p-8 text-center">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                                                <i class="fa-solid fa-folder-open"></i>
                                            </div>

                                            <h3 class="mt-4 text-base font-black text-slate-950">Data Balita belum ditemukan</h3>
                                            <p class="mt-2 text-sm font-medium text-slate-500">
                                                Tambahkan data Balita atau ubah filter pencarian.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="balitaNoLiveResult" class="hidden rounded-[1.5rem] border border-dashed border-amber-200 bg-amber-50/75 p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-amber-600 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>

                    <h3 class="mt-4 text-base font-black text-slate-950">Tidak ada hasil di halaman ini</h3>
                    <p class="mt-2 text-sm font-medium text-slate-500">
                        Sistem tetap mencari ke database. Kalau datanya ada, daftar akan diperbarui otomatis.
                    </p>
                </div>

                @if(method_exists($items, 'links'))
                    <div id="balitaPagination" class="mt-5">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </section>
    </div>

    <form id="bulkDeleteForm"
          action="{{ route('kader.data.balita.bulk-delete') }}"
          method="POST"
          class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <div id="pcConfirmModal" class="pc-modal-backdrop" aria-hidden="true">
        <div class="pc-modal-card p-6">
            <div class="flex gap-4">
                <div id="pcModalIconBox" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-500/20">
                    <i id="pcModalIcon" class="fa-solid fa-circle-check text-xl"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p id="pcModalEyebrow" class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Konfirmasi</p>
                    <h3 id="pcModalTitle" class="mt-1 text-lg font-black text-slate-950">Konfirmasi aksi?</h3>
                    <p id="pcModalMessage" class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Pastikan data sudah benar sebelum melanjutkan.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button"
                        id="pcModalCancel"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="button"
                        id="pcModalConfirm"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
                    <i class="fa-solid fa-check"></i>
                    Lanjutkan
                </button>
            </div>
        </div>
    </div>

    @if(session('success') || session('error') || session('warning'))
        @php
            $infoType = session('error') ? 'error' : (session('warning') ? 'warning' : 'success');

            $infoTitle = match ($infoType) {
                'error' => 'Ada kendala',
                'warning' => 'Perlu perhatian',
                default => 'Berhasil',
            };

            $infoMessage = session('error') ?: (session('warning') ?: session('success'));

            $infoClass = match ($infoType) {
                'error' => 'from-rose-500 to-orange-500 shadow-rose-500/20',
                'warning' => 'from-amber-500 to-orange-500 shadow-amber-500/20',
                default => 'from-emerald-500 to-teal-500 shadow-emerald-500/20',
            };

            $infoTextClass = match ($infoType) {
                'error' => 'text-rose-700',
                'warning' => 'text-amber-700',
                default => 'text-emerald-700',
            };

            $infoIcon = match ($infoType) {
                'error' => 'fa-triangle-exclamation',
                'warning' => 'fa-circle-info',
                default => 'fa-circle-check',
            };
        @endphp

        <div id="pcInfoModal" class="pc-modal-backdrop" aria-hidden="true">
            <div class="pc-modal-card p-6">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] bg-gradient-to-br {{ $infoClass }} text-white shadow-lg">
                        <i class="fa-solid {{ $infoIcon }} text-xl"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] {{ $infoTextClass }}">Informasi</p>
                        <h3 class="mt-1 text-lg font-black text-slate-950">{{ $infoTitle }}</h3>
                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">{{ $infoMessage }}</p>
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

    const filterForm = document.querySelector('#balitaFilterForm');
    const searchInput = document.querySelector('#balitaLiveSearch');
    const clearSearchBtn = document.querySelector('#clearLiveSearch');
    const statusFilter = document.querySelector('#statusAkunFilter');
    const bulkDeleteForm = document.querySelector('#bulkDeleteForm');
    const selectAllBtn = document.querySelector('#selectAllBalita');
    const bulkDeleteTrigger = document.querySelector('#bulkDeleteTrigger');

    let ajaxTimer = null;
    let ajaxController = null;
    let latestRequestId = 0;
    let pendingForm = null;
    let pendingMode = null;

    let confirmModal = document.querySelector('#pcConfirmModal');
    let infoModal = document.querySelector('#pcInfoModal');

    if (confirmModal && confirmModal.parentElement !== document.body) {
        document.body.appendChild(confirmModal);
    }

    if (infoModal && infoModal.parentElement !== document.body) {
        document.body.appendChild(infoModal);
    }

    const modalTitle = document.querySelector('#pcModalTitle');
    const modalMessage = document.querySelector('#pcModalMessage');
    const modalEyebrow = document.querySelector('#pcModalEyebrow');
    const modalIconBox = document.querySelector('#pcModalIconBox');
    const modalIcon = document.querySelector('#pcModalIcon');
    const modalCancel = document.querySelector('#pcModalCancel');
    const modalConfirm = document.querySelector('#pcModalConfirm');
    const closeInfoBtn = document.querySelector('#pcCloseInfo');

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function openModal(modal) {
        if (!modal) return;

        lockBody();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    function resetConfirmButton() {
        modalConfirm.disabled = false;
        modalConfirm.classList.remove('opacity-70', 'cursor-not-allowed');
    }

    function configureConfirmModal(type, title, message, buttonText) {
        const isDelete = type === 'delete' || type === 'bulk-delete';
        const isSync = type === 'sync';
        const isWarning = type === 'warning';

        modalTitle.textContent = title || 'Konfirmasi aksi?';
        modalMessage.textContent = message || 'Pastikan data sudah benar sebelum melanjutkan.';

        modalIconBox.className = 'flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] text-white shadow-lg';

        if (isDelete) {
            modalEyebrow.textContent = 'Konfirmasi';
            modalEyebrow.className = 'text-[11px] font-black uppercase tracking-[.18em] text-rose-700';
            modalIconBox.classList.add('bg-gradient-to-br', 'from-rose-500', 'to-orange-500', 'shadow-rose-500/20');
            modalIcon.className = 'fa-solid fa-triangle-exclamation text-xl';
            modalConfirm.className = 'inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-orange-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-rose-400/20 transition hover:-translate-y-0.5';
            modalConfirm.innerHTML = '<i class="fa-solid fa-trash"></i> ' + (buttonText || 'Hapus Data');
            return;
        }

        if (isSync) {
            modalEyebrow.textContent = 'Konfirmasi';
            modalEyebrow.className = 'text-[11px] font-black uppercase tracking-[.18em] text-amber-700';
            modalIconBox.classList.add('bg-gradient-to-br', 'from-amber-500', 'to-orange-500', 'shadow-amber-500/20');
            modalIcon.className = 'fa-solid fa-link text-xl';
            modalConfirm.className = 'inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-amber-400/20 transition hover:-translate-y-0.5';
            modalConfirm.innerHTML = '<i class="fa-solid fa-link"></i> ' + (buttonText || 'Sinkron');
            return;
        }

        if (isWarning) {
            modalEyebrow.textContent = 'Informasi';
            modalEyebrow.className = 'text-[11px] font-black uppercase tracking-[.18em] text-amber-700';
            modalIconBox.classList.add('bg-gradient-to-br', 'from-amber-500', 'to-orange-500', 'shadow-amber-500/20');
            modalIcon.className = 'fa-solid fa-circle-info text-xl';
            modalConfirm.className = 'inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-slate-800 to-slate-950 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-400/20 transition hover:-translate-y-0.5';
            modalConfirm.innerHTML = '<i class="fa-solid fa-check"></i> ' + (buttonText || 'Mengerti');
            return;
        }

        modalEyebrow.textContent = 'Konfirmasi';
        modalEyebrow.className = 'text-[11px] font-black uppercase tracking-[.18em] text-emerald-700';
        modalIconBox.classList.add('bg-gradient-to-br', 'from-emerald-500', 'to-teal-500', 'shadow-emerald-500/20');
        modalIcon.className = 'fa-solid fa-circle-check text-xl';
        modalConfirm.className = 'inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5';
        modalConfirm.innerHTML = '<i class="fa-solid fa-check"></i> ' + (buttonText || 'Lanjutkan');
    }

    function openConfirm(form, options) {
        pendingForm = form;
        pendingMode = options.type || null;

        resetConfirmButton();
        configureConfirmModal(
            options.type,
            options.title,
            options.message,
            options.button
        );

        openModal(confirmModal);
    }

    function closeConfirm() {
        pendingForm = null;
        pendingMode = null;
        closeModal(confirmModal);
    }

    function getRows() {
        return Array.from(document.querySelectorAll('[data-live-row]'));
    }

    function normalizeText(value) {
        return String(value || '').toLowerCase().trim();
    }

    function isNumericKeyword(value) {
        return /^[0-9]+$/.test(String(value || '').trim());
    }

    function updateClearButton() {
        if (!clearSearchBtn || !searchInput) return;

        const hasValue = searchInput.value.trim().length > 0;
        clearSearchBtn.classList.toggle('hidden', !hasValue);
        clearSearchBtn.classList.toggle('inline-flex', hasValue);
    }

    function updateCounter(count) {
        const counter = document.querySelector('#balitaVisibleCounter');
        if (!counter) return;

        counter.textContent = count + ' Data';
    }

    function filterCurrentPage() {
        const keyword = normalizeText(searchInput ? searchInput.value : '');
        const accountValue = statusFilter ? statusFilter.value : 'semua';
        const rows = getRows();

        let matched = 0;

        rows.forEach(function (row) {
            const name = normalizeText(row.dataset.name);
            const nik = normalizeText(row.dataset.nik);
            const account = normalizeText(row.dataset.account);

            let visible = true;

            if (keyword !== '') {
                visible = isNumericKeyword(keyword)
                    ? nik.includes(keyword)
                    : name.includes(keyword);
            }

            if (visible && accountValue !== 'semua') {
                visible = account === accountValue;
            }

            row.classList.toggle('hidden', !visible);

            if (visible) matched++;
        });

        const noResult = document.querySelector('#balitaNoLiveResult');
        if (noResult) {
            noResult.classList.toggle('hidden', matched !== 0 || rows.length === 0);
        }

        updateCounter(matched);
        updateClearButton();
    }

    function buildUrlFromForm() {
        const url = new URL(filterForm.action, window.location.origin);
        const formData = new FormData(filterForm);

        for (const [key, value] of formData.entries()) {
            if (String(value).trim() !== '') {
                url.searchParams.set(key, value);
            }
        }

        url.searchParams.delete('page');

        return url;
    }

    function showListLoading() {
        const block = document.querySelector('#balitaListBlock');
        if (!block) return;

        block.style.opacity = '.58';
        block.style.pointerEvents = 'none';
    }

    function hideListLoading() {
        const block = document.querySelector('#balitaListBlock');
        if (!block) return;

        block.style.opacity = '';
        block.style.pointerEvents = '';
    }

    function replaceListFromHtml(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const nextBlock = doc.querySelector('#balitaListBlock');
        const currentBlock = document.querySelector('#balitaListBlock');

        if (nextBlock && currentBlock) {
            currentBlock.innerHTML = nextBlock.innerHTML;
        }

        filterCurrentPage();
        updateSelectedState();
    }

    function fetchFilteredList(pushState = true) {
        if (!filterForm) return;

        const requestId = ++latestRequestId;
        const url = buildUrlFromForm();

        if (ajaxController) {
            ajaxController.abort();
        }

        ajaxController = new AbortController();
        showListLoading();

        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            },
            signal: ajaxController.signal,
            cache: 'no-store'
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Gagal mengambil data.');
                return response.text();
            })
            .then(function (html) {
                if (requestId !== latestRequestId) return;

                replaceListFromHtml(html);

                if (pushState) {
                    window.history.replaceState({}, '', url.toString());
                }
            })
            .catch(function (error) {
                if (error.name !== 'AbortError') {
                    filterCurrentPage();
                }
            })
            .finally(function () {
                if (requestId === latestRequestId) {
                    hideListLoading();
                }
            });
    }

    function scheduleAjaxSearch() {
        filterCurrentPage();

        clearTimeout(ajaxTimer);

        ajaxTimer = setTimeout(function () {
            fetchFilteredList(true);
        }, 280);
    }

    function getCheckedBoxes() {
        return Array.from(document.querySelectorAll('.balita-checkbox:checked'));
    }

    function updateSelectedState() {
        const checked = getCheckedBoxes();
        const allBoxes = Array.from(document.querySelectorAll('.balita-checkbox'));

        const trigger = document.querySelector('#bulkDeleteTrigger');
        const selectBtn = document.querySelector('#selectAllBalita');

        if (trigger) {
            trigger.disabled = checked.length === 0;
            trigger.classList.toggle('opacity-50', checked.length === 0);
            trigger.classList.toggle('cursor-not-allowed', checked.length === 0);
        }

        if (selectBtn) {
            const allSelected = allBoxes.length > 0 && checked.length === allBoxes.length;
            selectBtn.innerHTML = allSelected
                ? '<i class="fa-solid fa-square-check"></i> Batal Pilih'
                : '<i class="fa-regular fa-square-check"></i> Pilih Semua';
        }
    }

    function prepareBulkDeleteForm() {
        if (!bulkDeleteForm) return;

        bulkDeleteForm.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
            input.remove();
        });

        getCheckedBoxes().forEach(function (box) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = box.value;
            bulkDeleteForm.appendChild(input);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', scheduleAjaxSearch);
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            searchInput.value = '';
            searchInput.focus();
            scheduleAjaxSearch();
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            filterCurrentPage();
            fetchFilteredList(true);
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            fetchFilteredList(true);
        });
    }

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('balita-checkbox')) {
            updateSelectedState();
        }
    });

    document.addEventListener('click', function (event) {
        const selectBtn = event.target.closest('#selectAllBalita');

        if (selectBtn) {
            const boxes = Array.from(document.querySelectorAll('.balita-checkbox'));
            const checked = getCheckedBoxes();
            const shouldCheck = checked.length !== boxes.length;

            boxes.forEach(function (box) {
                const row = box.closest('[data-live-row]');
                const visible = !row || !row.classList.contains('hidden');

                if (visible) {
                    box.checked = shouldCheck;
                }
            });

            updateSelectedState();
            return;
        }

        const bulkBtn = event.target.closest('#bulkDeleteTrigger');

        if (bulkBtn) {
            const checked = getCheckedBoxes();

            if (checked.length === 0) {
                openConfirm(null, {
                    type: 'warning',
                    title: 'Belum ada data dipilih',
                    message: 'Pilih minimal satu data Balita sebelum menghapus.',
                    button: 'Mengerti'
                });
                return;
            }

            openConfirm(bulkDeleteForm, {
                type: 'bulk-delete',
                title: 'Hapus data terpilih?',
                message: checked.length + ' data Balita akan dihapus jika belum memiliki riwayat kunjungan atau pengukuran.',
                button: 'Hapus Terpilih'
            });
            return;
        }

        const paginationLink = event.target.closest('#balitaPagination a');

        if (paginationLink) {
            event.preventDefault();

            const url = new URL(paginationLink.href);

            showListLoading();

            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                cache: 'no-store'
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Gagal memuat halaman.');
                    return response.text();
                })
                .then(function (html) {
                    replaceListFromHtml(html);
                    window.history.replaceState({}, '', url.toString());
                })
                .catch(function () {
                    window.location.href = url.toString();
                })
                .finally(hideListLoading);
        }
    });

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-confirm-form]');

        if (!form) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        openConfirm(form, {
            type: form.dataset.confirmType || 'default',
            title: form.dataset.confirmTitle || 'Konfirmasi aksi?',
            message: form.dataset.confirmMessage || 'Pastikan data sudah benar sebelum melanjutkan.',
            button: form.dataset.confirmButton || 'Lanjutkan'
        });
    }, true);

    if (modalCancel) {
        modalCancel.addEventListener('click', closeConfirm);
    }

    if (confirmModal) {
        confirmModal.addEventListener('click', function (event) {
            if (event.target === confirmModal) {
                closeConfirm();
            }
        });
    }

    if (modalConfirm) {
        modalConfirm.addEventListener('click', function () {
            if (!pendingForm) {
                closeConfirm();
                return;
            }

            if (pendingMode === 'bulk-delete') {
                prepareBulkDeleteForm();
            }

            modalConfirm.disabled = true;
            modalConfirm.classList.add('opacity-70', 'cursor-not-allowed');
            modalConfirm.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

            HTMLFormElement.prototype.submit.call(pendingForm);
        });
    }

    if (infoModal) {
        requestAnimationFrame(function () {
            openModal(infoModal);
        });

        infoModal.addEventListener('click', function (event) {
            if (event.target === infoModal) {
                closeModal(infoModal);
            }
        });
    }

    if (closeInfoBtn) {
        closeInfoBtn.addEventListener('click', function () {
            closeModal(infoModal);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeConfirm();
            closeModal(infoModal);
        }
    });

    filterCurrentPage();
    updateSelectedState();
    updateClearButton();
})();
</script>
@endpush