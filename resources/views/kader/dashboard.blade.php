@extends('layouts.kader')

@section('title', 'Dashboard Kader')

@php
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $fullName = auth()->user()->name ?? 'Kader';
    $firstName = trim(explode(' ', $fullName)[0]) ?: 'Kader';

    $routeHas = fn ($name) => Route::has($name);

    $maxChart = max($chartData ?: [0]) ?: 1;

    $formatDate = function ($date, $format = 'd M Y') {
        if (!$date) return '-';

        try {
            return Carbon::parse($date)->translatedFormat($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatTime = function ($time) {
        if (!$time) return '-';

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $badgeClass = function ($badge) {
        return match ($badge) {
            'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'rose' => 'bg-rose-50 text-rose-700 border-rose-100',
            'sky' => 'bg-sky-50 text-sky-700 border-sky-100',
            default => 'bg-amber-50 text-amber-700 border-amber-100',
        };
    };
@endphp

@push('styles')
<style>
    .k-dashboard {
        animation: kdFade .26s cubic-bezier(.16, 1, .3, 1) both;
    }

    @keyframes kdFade {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .kd-card {
        border: 1px solid rgba(226, 232, 240, .86);
        background: rgba(255, 255, 255, .86);
        box-shadow: 0 18px 44px rgba(15, 23, 42, .055), inset 0 1px 0 rgba(255, 255, 255, .9);
        backdrop-filter: blur(14px);
    }

    .kd-soft-card {
        border: 1px solid rgba(226, 232, 240, .8);
        background: rgba(255, 255, 255, .76);
        box-shadow: 0 14px 34px rgba(15, 23, 42, .045);
    }

    .kd-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .kd-chart-bar {
        min-height: 8px;
        background: linear-gradient(180deg, #34d399 0%, #059669 100%);
        box-shadow: 0 10px 18px rgba(5, 150, 105, .16);
        transition: height .22s cubic-bezier(.16, 1, .3, 1), opacity .16s ease;
    }

    .kd-chart-bar:hover {
        opacity: .78;
    }

    .kd-action {
        transition: transform .18s cubic-bezier(.16, 1, .3, 1), box-shadow .18s ease, border-color .18s ease;
    }

    .kd-action:hover {
        transform: translateY(-2px);
    }

    @media (max-width: 640px) {
        .kd-card,
        .kd-soft-card {
            backdrop-filter: none;
        }
    }
</style>
@endpush

@section('content')
<div class="k-dashboard space-y-6">

    {{-- HERO --}}
    <section class="relative overflow-hidden rounded-[30px] border border-emerald-100/80 bg-white/85 p-5 shadow-[0_24px_60px_rgba(15,23,42,.07)] sm:p-6 lg:p-7">
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-100/80 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-20 h-72 w-72 rounded-full bg-amber-100/70 blur-3xl"></div>

        <div class="relative z-10 grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">
                    <i class="fa-solid fa-user-nurse"></i>
                    Command Center Kader
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Halo, {{ $firstName }}
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Pantau data sasaran, absensi, pengukuran fisik, jadwal dari Bidan, dan rekap laporan bulanan dari satu halaman.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                @if($routeHas('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index') }}" class="kd-action inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700">
                        <i class="fa-solid fa-clipboard-check"></i>
                        Absensi
                    </a>
                @endif

                @if($routeHas('kader.pemeriksaan.index'))
                    <a href="{{ route('kader.pemeriksaan.index') }}" class="kd-action inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm hover:border-emerald-200 hover:text-emerald-700">
                        <i class="fa-solid fa-weight-scale"></i>
                        Pengukuran
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- STAT UTAMA --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="kd-card rounded-[26px] p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Total Sasaran</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['total_sasaran'] ?? 0) }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">Balita, remaja, dan lansia</p>
                </div>

                <div class="kd-icon h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
        </div>

        <div class="kd-card rounded-[26px] p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Absensi Hari Ini</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['hadir_hari_ini'] ?? 0) }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        {{ $stats['persentase_hari_ini'] ?? 0 }}% dari {{ number_format($stats['target_absensi_hari_ini'] ?? 0) }} sasaran
                    </p>
                </div>

                <div class="kd-icon h-12 w-12 rounded-2xl bg-sky-50 text-sky-600">
                    <i class="fa-solid fa-user-check text-lg"></i>
                </div>
            </div>
        </div>

        <div class="kd-card rounded-[26px] p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Menunggu Validasi</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['pengukuran_pending'] ?? 0) }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">Pengukuran fisik oleh Kader</p>
                </div>

                <div class="kd-icon h-12 w-12 rounded-2xl bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-hourglass-half text-lg"></i>
                </div>
            </div>
        </div>

        <div class="kd-card rounded-[26px] p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Jadwal Hari Ini</p>
                    <h2 class="mt-3 text-xl font-black text-slate-900">
                        {{ $jadwalHariIni ? 'Ada Kegiatan' : 'Tidak Ada' }}
                    </h2>
                    <p class="mt-1 max-w-[180px] truncate text-xs font-bold text-slate-400">
                        {{ $jadwalHariIni->judul ?? 'Belum ada jadwal aktif hari ini' }}
                    </p>
                </div>

                <div class="kd-icon h-12 w-12 rounded-2xl bg-violet-50 text-violet-600">
                    <i class="fa-solid fa-calendar-day text-lg"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- BREAKDOWN --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <a href="{{ $routeHas('kader.data.balita.index') ? route('kader.data.balita.index') : '#' }}" class="kd-soft-card kd-action rounded-[24px] p-5">
            <div class="flex items-center gap-3">
                <div class="kd-icon h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-child-reaching"></i>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total_balita'] ?? 0) }}</p>
                    <p class="text-xs font-black uppercase tracking-[.1em] text-slate-400">Balita / Anak</p>
                </div>
            </div>
        </a>

        <a href="{{ $routeHas('kader.data.remaja.index') ? route('kader.data.remaja.index') : '#' }}" class="kd-soft-card kd-action rounded-[24px] p-5">
            <div class="flex items-center gap-3">
                <div class="kd-icon h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total_remaja'] ?? 0) }}</p>
                    <p class="text-xs font-black uppercase tracking-[.1em] text-slate-400">Remaja</p>
                </div>
            </div>
        </a>

        <a href="{{ $routeHas('kader.data.lansia.index') ? route('kader.data.lansia.index') : '#' }}" class="kd-soft-card kd-action rounded-[24px] p-5">
            <div class="flex items-center gap-3">
                <div class="kd-icon h-12 w-12 rounded-2xl bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-person-cane"></i>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total_lansia'] ?? 0) }}</p>
                    <p class="text-xs font-black uppercase tracking-[.1em] text-slate-400">Lansia</p>
                </div>
            </div>
        </a>
    </section>

    {{-- KONTEN UTAMA --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

        {{-- GRAFIK --}}
        <div class="kd-card rounded-[28px] p-5 xl:col-span-7">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Grafik Absensi 7 Hari</h3>
                    <p class="text-xs font-bold text-slate-400">Berdasarkan kehadiran yang tercatat</p>
                </div>

                <span class="w-fit rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-black text-emerald-700">
                    Rekap Cepat
                </span>
            </div>

            <div class="flex h-64 items-end gap-3 rounded-[24px] border border-slate-100 bg-slate-50/75 p-4">
                @foreach($chartData as $index => $value)
                    @php
                        $height = $maxChart > 0 ? max(8, ($value / $maxChart) * 100) : 8;
                    @endphp

                    <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
                        <div class="text-[11px] font-black text-slate-500">{{ $value }}</div>

                        <div
                            class="kd-chart-bar w-full rounded-t-2xl"
                            style="height: {{ $height }}%"
                            title="{{ $chartLabels[$index] ?? '-' }}: {{ $value }} hadir"
                        ></div>

                        <div class="max-w-full truncate text-[10px] font-black text-slate-400">
                            {{ $chartLabels[$index] ?? '-' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- JADWAL --}}
        <div class="kd-card rounded-[28px] p-5 xl:col-span-5">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Jadwal Posyandu</h3>
                    <p class="text-xs font-bold text-slate-400">Read-only dari Bidan</p>
                </div>

                @if($routeHas('kader.jadwal.index'))
                    <a href="{{ route('kader.jadwal.index') }}" class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-black text-emerald-700 hover:bg-emerald-100">
                        Lihat
                    </a>
                @endif
            </div>

            <div class="space-y-3">
                @forelse($jadwalMendatang as $jadwal)
                    <a
                        href="{{ $routeHas('kader.jadwal.show') ? route('kader.jadwal.show', $jadwal) : '#' }}"
                        class="kd-action flex items-center gap-4 rounded-[22px] border border-slate-100 bg-white p-4 shadow-sm hover:border-emerald-100"
                    >
                        <div class="kd-icon h-14 w-14 rounded-2xl bg-emerald-50 text-center text-emerald-700">
                            <div>
                                <p class="text-lg font-black leading-4">{{ $formatDate($jadwal->tanggal ?? null, 'd') }}</p>
                                <p class="text-[10px] font-black uppercase">{{ $formatDate($jadwal->tanggal ?? null, 'M') }}</p>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-sm font-black text-slate-800">{{ $jadwal->judul ?? 'Jadwal Posyandu' }}</h4>
                            <p class="mt-1 text-xs font-bold text-slate-400">
                                <i class="fa-regular fa-clock mr-1"></i>
                                {{ $formatTime($jadwal->waktu_mulai ?? null) }} WIB
                            </p>

                            @if(!empty($jadwal->lokasi))
                                <p class="mt-1 truncate text-xs font-semibold text-slate-400">
                                    <i class="fa-solid fa-location-dot mr-1"></i>
                                    {{ $jadwal->lokasi }}
                                </p>
                            @endif
                        </div>

                        @if(!empty($jadwal->tanggal) && Carbon::parse($jadwal->tanggal)->isToday())
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black text-amber-700">
                                Hari Ini
                            </span>
                        @endif
                    </a>
                @empty
                    <div class="rounded-[22px] border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                        <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-white text-slate-400">
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                        <p class="text-sm font-black text-slate-500">Belum ada jadwal aktif.</p>
                        <p class="mt-1 text-xs font-bold text-slate-400">Jadwal akan muncul setelah dibuat oleh Bidan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- BAWAH --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-3">

        {{-- PENGUKURAN --}}
        <div class="kd-card rounded-[28px] p-5">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Pengukuran Terbaru</h3>
                    <p class="text-xs font-bold text-slate-400">Status validasi Bidan</p>
                </div>

                <div class="kd-icon h-10 w-10 rounded-2xl bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($pengukuranTerbaru as $item)
                    <div class="rounded-[20px] border border-slate-100 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="truncate text-sm font-black text-slate-800">{{ $item->nama }}</h4>
                                <p class="mt-1 text-xs font-bold text-slate-400">
                                    {{ $item->kategori }} • {{ $formatDate($item->tanggal ?? null, 'd M Y') }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-3 py-1 text-[10px] font-black {{ $badgeClass($item->badge ?? 'amber') }}">
                                {{ $item->status ?? 'Menunggu' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[20px] bg-slate-50 p-6 text-center">
                        <p class="text-xs font-bold text-slate-400">Belum ada pengukuran fisik.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- SASARAN BARU --}}
        <div class="kd-card rounded-[28px] p-5">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Data Sasaran Terbaru</h3>
                    <p class="text-xs font-bold text-slate-400">Balita, remaja, dan lansia</p>
                </div>

                <div class="kd-icon h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-address-book"></i>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($sasaranBaru as $item)
                    <div class="flex items-center gap-3 rounded-[20px] border border-slate-100 bg-white p-3">
                        <div class="kd-icon h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-600">
                            <i class="fa-solid {{ $item->icon }}"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-sm font-black text-slate-800">{{ $item->nama }}</h4>
                            <p class="text-xs font-bold text-slate-400">{{ $item->kategori }}</p>
                        </div>

                        <p class="shrink-0 text-[10px] font-black text-slate-300">
                            {{ optional($item->created_at)->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-[20px] bg-slate-50 p-6 text-center">
                        <p class="text-xs font-bold text-slate-400">Data sasaran masih kosong.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- LAPORAN --}}
        <div class="kd-card rounded-[28px] p-5">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Laporan Bulanan</h3>
                    <p class="text-xs font-bold text-slate-400">{{ $laporanBulanan['periode'] ?? '-' }}</p>
                </div>

                <div class="kd-icon h-10 w-10 rounded-2xl bg-slate-100 text-slate-600">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-[20px] bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Jadwal bulan ini</span>
                    <span class="text-sm font-black text-slate-900">{{ number_format($laporanBulanan['jumlah_jadwal'] ?? 0) }}</span>
                </div>

                <div class="flex items-center justify-between rounded-[20px] bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Kehadiran tercatat</span>
                    <span class="text-sm font-black text-slate-900">{{ number_format($laporanBulanan['jumlah_hadir'] ?? 0) }}</span>
                </div>

                <div class="flex items-center justify-between rounded-[20px] bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Pengukuran fisik</span>
                    <span class="text-sm font-black text-slate-900">{{ number_format($laporanBulanan['jumlah_pengukuran'] ?? 0) }}</span>
                </div>

                @if($routeHas('kader.laporan.index'))
                    <a href="{{ route('kader.laporan.index') }}" class="kd-action mt-2 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">
                        <i class="fa-solid fa-file-export"></i>
                        Buka Laporan
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection