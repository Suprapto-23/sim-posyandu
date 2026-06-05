@extends('layouts.kader')

@section('title', 'Agenda Posyandu')
@section('page-name', 'Agenda Posyandu')
@section('page-title', 'Agenda Posyandu')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $indexRoute = $routeHas('kader.jadwal.index')
        ? route('kader.jadwal.index')
        : url('/kader/jadwal');

    $search = trim((string) ($search ?? request('search', '')));
    $status = $status ?? request('status', 'semua');
    $target = $target ?? request('target', 'semua');
    $bulan = $bulan ?? request('bulan', now('Asia/Jakarta')->format('Y-m'));

    $statTotal = $statTotal ?? (method_exists($jadwals, 'total') ? $jadwals->total() : $jadwals->count());
    $statAktif = $statAktif ?? 0;
    $statSelesai = $statSelesai ?? 0;
    $statBulanIni = $statBulanIni ?? 0;

    $statusOptions = [
        'semua' => 'Semua Status',
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    $targetOptions = [
        'semua' => 'Semua Sasaran',
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    $kategoriLabel = fn ($value) => match ($value) {
        'posyandu' => 'Posyandu Rutin',
        'imunisasi' => 'Imunisasi',
        'pemeriksaan' => 'Pemeriksaan Klinis',
        'lainnya' => 'Kegiatan Lainnya',
        default => Str::title(str_replace('_', ' ', (string) $value)),
    };

    $targetLabel = fn ($value) => match ($value) {
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        'semua' => 'Semua Sasaran',
        default => Str::title(str_replace('_', ' ', (string) $value)),
    };

    $statusLabel = fn ($value) => match ($value) {
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        default => Str::title(str_replace('_', ' ', (string) $value)),
    };

    $statusClass = fn ($value) => match ($value) {
        'aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'selesai' => 'border-slate-200 bg-slate-50 text-slate-600',
        'dibatalkan' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-50 text-slate-600',
    };

    $targetClass = fn ($value) => match ($value) {
        'balita' => 'border-sky-200 bg-sky-50 text-sky-700',
        'remaja' => 'border-violet-200 bg-violet-50 text-violet-700',
        'lansia' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        default => 'border-amber-200 bg-amber-50 text-amber-700',
    };

    $kategoriClass = fn ($value) => match ($value) {
        'posyandu' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'imunisasi' => 'border-sky-200 bg-sky-50 text-sky-700',
        'pemeriksaan' => 'border-amber-200 bg-amber-50 text-amber-700',
        'lainnya' => 'border-violet-200 bg-violet-50 text-violet-700',
        default => 'border-slate-200 bg-slate-50 text-slate-600',
    };

    $formatDate = fn ($value, $format = 'd F Y') => $value
        ? Carbon::parse($value)->translatedFormat($format)
        : '-';

    $formatTime = fn ($value) => $value
        ? Carbon::parse($value)->format('H:i')
        : '-';

    $rangeText = method_exists($jadwals, 'firstItem')
        ? 'Menampilkan ' . (($jadwals->firstItem() ?? 0)) . ' sampai ' . (($jadwals->lastItem() ?? 0)) . ' dari ' . $jadwals->total() . ' jadwal'
        : 'Menampilkan ' . $jadwals->count() . ' jadwal';

    $nextSchedule = collect($jadwals ?? [])->first(function ($item) {
        return ($item->status ?? null) === 'aktif'
            && filled($item->tanggal ?? null)
            && Carbon::parse($item->tanggal)->greaterThanOrEqualTo(now('Asia/Jakarta')->startOfDay());
    });
@endphp

@push('styles')
<style>
    .agenda-page {
        background:
            radial-gradient(circle at 10% 8%, rgba(16, 185, 129, 0.13), transparent 30%),
            radial-gradient(circle at 88% 12%, rgba(245, 158, 11, 0.12), transparent 28%),
            radial-gradient(circle at 78% 88%, rgba(14, 165, 233, 0.10), transparent 30%),
            linear-gradient(135deg, #f8fafc 0%, #ecfdf5 45%, #eff6ff 100%);
    }

    .agenda-page::before {
        content: "";
        position: fixed;
        inset: 0;
        pointer-events: none;
        background-image:
            linear-gradient(rgba(15, 23, 42, 0.026) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, 0.026) 1px, transparent 1px);
        background-size: 34px 34px;
        mask-image: linear-gradient(to bottom, black, transparent 84%);
    }

    .glass {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.76);
        backdrop-filter: blur(24px);
        box-shadow: 0 18px 60px rgba(15, 23, 42, 0.062);
    }

    .hero-panel {
        border: 1px solid rgba(255, 255, 255, 0.80);
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.90), rgba(236, 253, 245, 0.78), rgba(239, 246, 255, 0.72));
        backdrop-filter: blur(26px);
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.072);
    }

    .stat-tile {
        min-height: 118px;
        position: relative;
        overflow: hidden;
        border-radius: 26px;
        border: 1px solid rgba(255, 255, 255, 0.76);
        backdrop-filter: blur(18px);
        box-shadow: 0 14px 42px rgba(15, 23, 42, 0.052);
    }

    .stat-tile::after {
        content: "";
        position: absolute;
        right: -32px;
        top: -34px;
        width: 108px;
        height: 108px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.48);
    }

    .agenda-row {
        display: grid;
        grid-template-columns: 88px minmax(0, 1fr) 190px;
        gap: 16px;
        align-items: stretch;
    }

    .date-card {
        min-height: 118px;
        border-radius: 24px;
        border: 1px solid rgba(167, 243, 208, 0.95);
        background: linear-gradient(145deg, rgba(236, 253, 245, 0.96), rgba(255, 251, 235, 0.78));
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.70);
    }

    .soft-input {
        height: 3.5rem;
        width: 100%;
        border-radius: 22px;
        border: 1px solid rgba(209, 250, 229, 0.95);
        background: rgba(236, 253, 245, 0.64);
        padding-left: 1.25rem;
        padding-right: 1rem;
        font-size: 0.875rem;
        font-weight: 800;
        color: rgb(51, 65, 85);
        outline: none;
        transition: all .22s ease;
    }

    .soft-input:focus {
        border-color: rgba(16, 185, 129, 0.75);
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.10);
    }

    .live-loading {
        opacity: .55;
        pointer-events: none;
        transition: opacity .18s ease;
    }

    @media (max-width: 1024px) {
        .agenda-row {
            grid-template-columns: 1fr;
        }

        .date-card {
            min-height: auto;
            align-items: flex-start;
            padding: 16px;
        }
    }
</style>
@endpush

@section('content')
<div class="agenda-page relative min-h-[calc(100vh-96px)] px-4 py-6 sm:px-6 lg:px-8">
    <div class="relative z-10 mx-auto max-w-7xl space-y-6">

        <section class="hero-panel overflow-hidden rounded-[34px]">
            <div class="relative p-6 sm:p-8">
                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/22 blur-3xl"></div>
                <div class="absolute -bottom-28 left-10 h-72 w-72 rounded-full bg-amber-300/18 blur-3xl"></div>
                <div class="absolute bottom-8 right-1/3 h-24 w-24 rounded-full bg-sky-300/14 blur-2xl"></div>

                <div class="relative grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_380px] xl:items-end">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/90 px-4 py-2 text-xs font-black uppercase tracking-[0.20em] text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Dashboard Agenda Kader
                        </div>

                        <h1 class="mt-5 text-4xl font-black tracking-tight text-slate-800 sm:text-5xl">
                            Jadwal Pelayanan Posyandu
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm font-semibold leading-7 text-slate-600 sm:text-base">
                            Pantau agenda pelayanan yang dibuat oleh Bidan. Halaman ini fokus untuk membantu Kader menyiapkan absensi, alat ukur, dan data sasaran sebelum kegiatan dimulai.
                        </p>

                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="rounded-[22px] border border-emerald-100 bg-white/72 px-4 py-3 backdrop-blur-xl">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Status</p>
                                <p class="mt-1 text-sm font-black text-emerald-700">Read Only Kader</p>
                            </div>

                            <div class="rounded-[22px] border border-amber-100 bg-white/72 px-4 py-3 backdrop-blur-xl">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Sumber</p>
                                <p class="mt-1 text-sm font-black text-amber-700">Dikelola Bidan</p>
                            </div>

                            <div class="rounded-[22px] border border-sky-100 bg-white/72 px-4 py-3 backdrop-blur-xl">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Fokus</p>
                                <p class="mt-1 text-sm font-black text-sky-700">Persiapan Layanan</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[30px] border border-white/80 bg-white/72 p-5 shadow-[0_16px_45px_rgba(15,23,42,0.055)] backdrop-blur-xl">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">
                                    Jadwal Terdekat
                                </p>

                                @if($nextSchedule)
                                    <h2 class="mt-3 line-clamp-2 text-2xl font-black leading-tight text-slate-800">
                                        {{ $nextSchedule->judul }}
                                    </h2>

                                    <p class="mt-2 text-sm font-bold leading-6 text-slate-500">
                                        {{ $formatDate($nextSchedule->tanggal, 'l, d F Y') }}
                                        <br>
                                        {{ $formatTime($nextSchedule->waktu_mulai) }} sampai {{ $formatTime($nextSchedule->waktu_selesai) }} WIB
                                    </p>
                                @else
                                    <h2 class="mt-3 text-2xl font-black text-slate-800">
                                        Belum Ada Agenda Aktif
                                    </h2>

                                    <p class="mt-2 text-sm font-bold leading-6 text-slate-500">
                                        Jadwal aktif belum tersedia pada filter saat ini.
                                    </p>
                                @endif
                            </div>

                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-lg font-black text-emerald-700">
                                J
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if(session('success') || session('error') || session('warning'))
            @php
                $flashType = session('error') ? 'error' : (session('warning') ? 'warning' : 'success');
                $flashText = session('error') ?: (session('warning') ?: session('success'));

                $flashClass = match ($flashType) {
                    'error' => 'border-rose-200 bg-rose-50/90 text-rose-800',
                    'warning' => 'border-amber-200 bg-amber-50/90 text-amber-800',
                    default => 'border-emerald-200 bg-emerald-50/90 text-emerald-800',
                };

                $flashTitle = match ($flashType) {
                    'error' => 'Aksi gagal',
                    'warning' => 'Perhatian',
                    default => 'Berhasil',
                };
            @endphp

            <div class="rounded-[24px] border px-5 py-4 shadow-sm backdrop-blur-xl {{ $flashClass }}">
                <p class="text-sm font-black">{{ $flashTitle }}</p>
                <p class="mt-1 text-sm font-semibold leading-6">{{ $flashText }}</p>
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="stat-tile bg-white/78 p-5">
                <p class="relative text-xs font-black uppercase tracking-[0.18em] text-slate-400">Total Jadwal</p>
                <p class="relative mt-3 text-4xl font-black text-slate-800">{{ number_format($statTotal) }}</p>
                <p class="relative mt-1 text-sm font-bold text-slate-500">Seluruh agenda</p>
            </div>

            <div class="stat-tile bg-emerald-100/72 p-5">
                <p class="relative text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Aktif</p>
                <p class="relative mt-3 text-4xl font-black text-emerald-800">{{ number_format($statAktif) }}</p>
                <p class="relative mt-1 text-sm font-bold text-emerald-700/70">Perlu disiapkan</p>
            </div>

            <div class="stat-tile bg-amber-100/76 p-5">
                <p class="relative text-xs font-black uppercase tracking-[0.18em] text-amber-700">Bulan Ini</p>
                <p class="relative mt-3 text-4xl font-black text-amber-800">{{ number_format($statBulanIni) }}</p>
                <p class="relative mt-1 text-sm font-bold text-amber-700/70">Agenda periode ini</p>
            </div>

            <div class="stat-tile bg-sky-100/72 p-5">
                <p class="relative text-xs font-black uppercase tracking-[0.18em] text-sky-700">Selesai</p>
                <p class="relative mt-3 text-4xl font-black text-sky-800">{{ number_format($statSelesai) }}</p>
                <p class="relative mt-1 text-sm font-bold text-sky-700/70">Sudah terlaksana</p>
            </div>
        </section>

        <section class="glass rounded-[32px] p-4 sm:p-5">
            <form id="jadwalFilterForm"
                  action="{{ $indexRoute }}"
                  method="GET"
                  class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_180px_190px_190px_auto] xl:items-end">

                <div>
                    <label for="liveSearchInput" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                        Cari Jadwal
                    </label>

                    <div class="relative">
                        <input type="text"
                               id="liveSearchInput"
                               name="search"
                               value="{{ $search }}"
                               autocomplete="off"
                               placeholder="Ketik judul, lokasi, sasaran, kategori, atau catatan..."
                               class="soft-input pr-24">

                        <div id="liveSearchState"
                             class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-2xl bg-emerald-600 px-3 py-2 text-[11px] font-black text-white shadow-sm">
                            Mencari
                        </div>
                    </div>
                </div>

                <div>
                    <label for="bulan" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                        Bulan
                    </label>

                    <input type="month"
                           id="bulan"
                           name="bulan"
                           value="{{ $bulan }}"
                           class="soft-input">
                </div>

                <div>
                    <label for="status" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                        Status
                    </label>

                    <select id="status" name="status" class="soft-input">
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected($status === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="target" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                        Sasaran
                    </label>

                    <select id="target" name="target" class="soft-input">
                        @foreach($targetOptions as $key => $label)
                            <option value="{{ $key }}" @selected($target === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="submit"
                            class="h-14 rounded-[22px] bg-emerald-600 px-5 text-sm font-black text-white shadow-[0_14px_35px_rgba(16,185,129,0.22)] transition hover:bg-emerald-700">
                        Filter
                    </button>

                    <a href="{{ $indexRoute }}"
                       id="resetFilterButton"
                       class="inline-flex h-14 items-center justify-center rounded-[22px] border border-emerald-100 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-emerald-50">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section id="jadwalLiveRegion" data-live-region>
            <div class="overflow-hidden rounded-[34px] border border-white/70 bg-white/76 shadow-[0_24px_85px_rgba(15,23,42,0.075)] backdrop-blur-xl">
                <div class="border-b border-emerald-100 bg-gradient-to-r from-white/90 via-emerald-50/80 to-sky-50/70 px-5 py-5 sm:px-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-700">
                                Agenda Board
                            </p>

                            <h2 class="mt-2 text-2xl font-black text-slate-800">
                                Daftar Jadwal Posyandu
                            </h2>

                            <p class="mt-1 text-sm font-semibold text-slate-500">
                                {{ $rangeText }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-white/72 px-4 py-3 text-sm font-black text-emerald-700 shadow-sm backdrop-blur-xl">
                            Jadwal hanya dapat dikelola Bidan
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-4 sm:p-5">
                    @forelse($jadwals as $jadwal)
                        @php
                            $tanggal = filled($jadwal->tanggal ?? null)
                                ? Carbon::parse($jadwal->tanggal)
                                : null;

                            $isToday = $tanggal?->isToday() ?? false;
                            $isPast = $tanggal ? $tanggal->lt(now('Asia/Jakarta')->startOfDay()) : false;

                            $detailRoute = $routeHas('kader.jadwal.show')
                                ? route('kader.jadwal.show', $jadwal->id)
                                : url('/kader/jadwal/' . $jadwal->id);
                        @endphp

                        <article class="group overflow-hidden rounded-[28px] border border-emerald-100/70 bg-gradient-to-br from-white/94 via-emerald-50/34 to-sky-50/42 p-4 shadow-[0_14px_42px_rgba(15,23,42,0.045)] transition duration-300 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-[0_20px_58px_rgba(15,23,42,0.065)]">
                            <div class="agenda-row">
                                <div class="date-card">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-700">
                                        {{ $tanggal ? $tanggal->translatedFormat('M') : '-' }}
                                    </p>

                                    <p class="mt-1 text-4xl font-black leading-none text-slate-800">
                                        {{ $tanggal ? $tanggal->format('d') : '-' }}
                                    </p>

                                    <p class="mt-2 text-sm font-black text-slate-500">
                                        {{ $tanggal ? $tanggal->translatedFormat('l') : '-' }}
                                    </p>
                                </div>

                                <div class="min-w-0 rounded-[24px] border border-emerald-100/80 bg-white/72 p-4 shadow-sm">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full border px-3 py-1 text-[11px] font-black uppercase tracking-[0.08em] {{ $statusClass($jadwal->status) }}">
                                            {{ $statusLabel($jadwal->status) }}
                                        </span>

                                        <span class="rounded-full border px-3 py-1 text-[11px] font-black uppercase tracking-[0.08em] {{ $targetClass($jadwal->target_peserta) }}">
                                            {{ $targetLabel($jadwal->target_peserta) }}
                                        </span>

                                        <span class="rounded-full border px-3 py-1 text-[11px] font-black uppercase tracking-[0.08em] {{ $kategoriClass($jadwal->kategori) }}">
                                            {{ $kategoriLabel($jadwal->kategori) }}
                                        </span>

                                        @if($isToday)
                                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.08em] text-amber-700">
                                                Hari Ini
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="mt-3 line-clamp-2 text-xl font-black leading-tight text-slate-800">
                                        {{ $jadwal->judul ?? '-' }}
                                    </h3>

                                    <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-3">
                                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">
                                                Waktu
                                            </p>
                                            <p class="mt-1 text-sm font-black text-slate-800">
                                                {{ $formatTime($jadwal->waktu_mulai) }} sampai {{ $formatTime($jadwal->waktu_selesai) }} WIB
                                            </p>
                                        </div>

                                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3 lg:col-span-2">
                                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">
                                                Lokasi
                                            </p>
                                            <p class="mt-1 truncate text-sm font-black text-slate-800">
                                                {{ $jadwal->lokasi ?? '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if(filled($jadwal->deskripsi ?? null))
                                        <p class="mt-3 line-clamp-2 text-sm font-semibold leading-6 text-slate-600">
                                            {{ $jadwal->deskripsi }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex flex-col justify-between gap-3 rounded-[24px] border border-emerald-100/80 bg-white/72 p-4 shadow-sm">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-700">
                                            Kesiapan
                                        </p>

                                        <p class="mt-2 text-sm font-bold leading-6 text-slate-600">
                                            @if(($jadwal->status ?? null) === 'aktif' && ! $isPast)
                                                Siapkan absensi, alat ukur, dan data sasaran sesuai target.
                                            @elseif(($jadwal->status ?? null) === 'selesai')
                                                Agenda sudah selesai dilaksanakan.
                                            @elseif(($jadwal->status ?? null) === 'dibatalkan')
                                                Agenda dibatalkan atau ditunda.
                                            @else
                                                Baca detail agenda untuk informasi lengkap.
                                            @endif
                                        </p>
                                    </div>

                                    <a href="{{ $detailRoute }}"
                                       class="inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-600 px-5 text-sm font-black text-white shadow-[0_12px_28px_rgba(16,185,129,0.22)] transition hover:bg-emerald-700">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[28px] border border-dashed border-emerald-300 bg-emerald-50/70 p-10 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[24px] bg-emerald-600 text-2xl font-black text-white">
                                J
                            </div>

                            <h3 class="mt-5 text-xl font-black text-slate-800">
                                Jadwal Tidak Ditemukan
                            </h3>

                            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                                Tidak ada jadwal yang cocok dengan filter saat ini.
                            </p>

                            <a href="{{ $indexRoute }}"
                               class="mt-5 inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-600 px-5 text-sm font-black text-white shadow-[0_14px_35px_rgba(16,185,129,0.22)] transition hover:bg-emerald-700">
                                Reset Filter
                            </a>
                        </div>
                    @endforelse
                </div>

                @if(method_exists($jadwals, 'links') && $jadwals->hasPages())
                    <div id="jadwalPagination" class="border-t border-emerald-100/80 bg-emerald-50/45 px-5 py-4">
                        {{ $jadwals->links() }}
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('jadwalFilterForm');
        const searchInput = document.getElementById('liveSearchInput');
        const bulanInput = document.getElementById('bulan');
        const statusInput = document.getElementById('status');
        const targetInput = document.getElementById('target');
        const resetButton = document.getElementById('resetFilterButton');
        const liveRegion = document.querySelector('[data-live-region]');
        const liveState = document.getElementById('liveSearchState');

        let liveTimer = null;
        let liveController = null;

        function buildSearchUrl(pageUrl) {
            const base = pageUrl || form.action;
            const url = new URL(base, window.location.origin);

            const keyword = searchInput.value.trim();
            const bulan = bulanInput.value;
            const status = statusInput.value || 'semua';
            const target = targetInput.value || 'semua';

            keyword ? url.searchParams.set('search', keyword) : url.searchParams.delete('search');
            bulan ? url.searchParams.set('bulan', bulan) : url.searchParams.delete('bulan');
            status !== 'semua' ? url.searchParams.set('status', status) : url.searchParams.delete('status');
            target !== 'semua' ? url.searchParams.set('target', target) : url.searchParams.delete('target');

            return url;
        }

        async function loadLiveResults(pageUrl) {
            if (!form || !liveRegion) return;

            if (liveController) liveController.abort();

            liveController = new AbortController();

            const url = buildSearchUrl(pageUrl);

            liveRegion.classList.add('live-loading');
            liveState?.classList.remove('hidden');

            try {
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: liveController.signal
                });

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextRegion = doc.querySelector('[data-live-region]');

                if (!nextRegion) {
                    window.location.href = url.toString();
                    return;
                }

                liveRegion.innerHTML = nextRegion.innerHTML;
                window.history.replaceState({}, '', url.toString());

                bindPaginationLinks();
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Live search jadwal gagal:', error);
                }
            } finally {
                liveRegion.classList.remove('live-loading');
                liveState?.classList.add('hidden');
            }
        }

        function scheduleSearch() {
            clearTimeout(liveTimer);
            liveTimer = setTimeout(function () {
                loadLiveResults();
            }, 260);
        }

        function bindPaginationLinks() {
            document.querySelectorAll('#jadwalPagination a').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    loadLiveResults(link.href);
                });
            });
        }

        searchInput?.addEventListener('input', function () {
            this.value = this.value.replace(/\s+/g, ' ');
            scheduleSearch();
        });

        bulanInput?.addEventListener('change', function () {
            loadLiveResults();
        });

        statusInput?.addEventListener('change', function () {
            loadLiveResults();
        });

        targetInput?.addEventListener('change', function () {
            loadLiveResults();
        });

        form?.addEventListener('submit', function (event) {
            event.preventDefault();
            loadLiveResults();
        });

        resetButton?.addEventListener('click', function (event) {
            event.preventDefault();

            searchInput.value = '';
            statusInput.value = 'semua';
            targetInput.value = 'semua';
            bulanInput.value = '{{ now("Asia/Jakarta")->format("Y-m") }}';

            loadLiveResults(form.action);
        });

        bindPaginationLinks();
    });
</script>
@endpush