@extends('layouts.kader')

@section('title', 'Dashboard Kader')

@php
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $fullName = auth()->user()->name ?? 'Kader';
    $firstName = trim(explode(' ', $fullName)[0]) ?: 'Kader';

    $routeUrl = fn ($name) => Route::has($name) ? route($name) : '#';

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
            'emerald' => 'bg-emerald-500/10 text-emerald-700 border-emerald-200',
            'rose' => 'bg-rose-500/10 text-rose-700 border-rose-200',
            'sky' => 'bg-sky-500/10 text-sky-700 border-sky-200',
            'violet' => 'bg-violet-500/10 text-violet-700 border-violet-200',
            default => 'bg-amber-500/10 text-amber-700 border-amber-200',
        };
    };

    $cardStats = [
        [
            'label' => 'Total Sasaran',
            'value' => number_format($stats['total_sasaran'] ?? 0),
            'desc' => 'Balita, remaja, dan lansia',
            'icon' => 'fa-users-line',
            'tone' => 'emerald',
            'url' => $routeUrl('kader.data.balita.index'),
        ],
        [
            'label' => 'Hadir Hari Ini',
            'value' => number_format($stats['hadir_hari_ini'] ?? 0),
            'desc' => ($stats['persentase_hari_ini'] ?? 0) . '% dari ' . number_format($stats['target_absensi_hari_ini'] ?? 0) . ' sasaran',
            'icon' => 'fa-user-check',
            'tone' => 'sky',
            'url' => $routeUrl('kader.absensi.index'),
        ],
        [
            'label' => 'Menunggu Review',
            'value' => number_format($stats['pengukuran_pending'] ?? 0),
            'desc' => 'Pengukuran perlu ditinjau Bidan',
            'icon' => 'fa-clock-rotate-left',
            'tone' => 'amber',
            'url' => $routeUrl('kader.pemeriksaan.index'),
        ],
        [
            'label' => 'Jadwal Hari Ini',
            'value' => $jadwalHariIni ? 'Aktif' : 'Kosong',
            'desc' => $jadwalHariIni->judul ?? 'Belum ada agenda aktif',
            'icon' => 'fa-calendar-check',
            'tone' => 'violet',
            'url' => $routeUrl('kader.jadwal.index'),
        ],
    ];

    $toneMap = [
        'emerald' => [
            'card' => 'from-emerald-50 via-teal-50 to-white border-emerald-200/70',
            'icon' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-emerald-500/25',
            'label' => 'text-emerald-700',
        ],
        'sky' => [
            'card' => 'from-sky-50 via-cyan-50 to-white border-sky-200/70',
            'icon' => 'bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-sky-500/25',
            'label' => 'text-sky-700',
        ],
        'amber' => [
            'card' => 'from-amber-50 via-orange-50 to-white border-amber-200/70',
            'icon' => 'bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-amber-500/25',
            'label' => 'text-amber-700',
        ],
        'violet' => [
            'card' => 'from-violet-50 via-fuchsia-50 to-white border-violet-200/70',
            'icon' => 'bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-violet-500/25',
            'label' => 'text-violet-700',
        ],
    ];

    $totalTrendHadir = $trend['summary']['total_hadir'] ?? 0;
    $totalTrendPengukuran = $trend['summary']['total_pengukuran'] ?? 0;
@endphp

@push('styles')
<style>
    .kader-shell {
        background:
            radial-gradient(circle at 8% 6%, rgba(16, 185, 129, .18), transparent 30%),
            radial-gradient(circle at 92% 10%, rgba(14, 165, 233, .16), transparent 28%),
            radial-gradient(circle at 45% 95%, rgba(245, 158, 11, .10), transparent 32%),
            linear-gradient(135deg, #f1fdf8 0%, #eef8ff 45%, #f8fafc 100%);
    }

    .kader-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, .045) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .045) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .soft-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .soft-scroll::-webkit-scrollbar-thumb {
        background: rgba(15, 118, 110, .22);
        border-radius: 999px;
    }

    .soft-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .glass-card {
        background: rgba(255, 255, 255, .72);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    .text-balance {
        text-wrap: balance;
    }

    .apexcharts-tooltip {
        border-radius: 18px !important;
        overflow: hidden !important;
        border: 1px solid rgba(226, 232, 240, .95) !important;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .14) !important;
    }
</style>
@endpush

@section('content')
<div class="kader-shell relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 kader-grid opacity-60"></div>

    <div class="relative z-10 mx-auto max-w-[1440px] space-y-5">

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[2rem] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-cyan-50/90 to-white/90 shadow-[0_24px_70px_rgba(15,118,110,.14)]">
            <div class="absolute -right-20 -top-24 h-80 w-80 rounded-full bg-emerald-300/25 blur-3xl"></div>
            <div class="absolute -bottom-24 left-32 h-80 w-80 rounded-full bg-sky-300/20 blur-3xl"></div>

            <div class="relative grid gap-5 p-5 sm:p-7 xl:grid-cols-[minmax(0,1.45fr)_minmax(340px,.72fr)] xl:items-stretch">
                <div class="flex min-w-0 flex-col justify-between gap-6">
                    <div class="space-y-4">
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200/80 bg-white/70 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-400/50"></span>
                            Kader Operation Center
                        </div>

                        <div class="max-w-3xl">
                            <h1 class="text-balance text-2xl font-black tracking-tight text-slate-950 sm:text-3xl lg:text-[2.45rem] lg:leading-[1.08]">
                                Halo, {{ $firstName }}. Semua data lapangan ada dalam satu kendali.
                            </h1>
                            <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-slate-600 sm:text-base">
                                Pantau sasaran, registrasi hadir, pengukuran fisik, jadwal posyandu, dan rekap bulanan tanpa tampilan yang bikin mata pensiun dini.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if(Route::has('kader.absensi.index'))
                            <a href="{{ route('kader.absensi.index') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-500/25 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl">
                                <i class="fa-solid fa-user-check"></i>
                                Input Absensi
                            </a>
                        @endif

                        @if(Route::has('kader.pemeriksaan.create'))
                            <a href="{{ route('kader.pemeriksaan.create') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/75 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm backdrop-blur transition-all duration-300 hover:-translate-y-0.5 hover:bg-white">
                                <i class="fa-solid fa-stethoscope"></i>
                                Input Pengukuran
                            </a>
                        @endif
                    </div>
                </div>

                <div class="grid min-w-0 gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.6rem] border border-white/80 bg-white/70 p-4 shadow-lg shadow-emerald-100/70 backdrop-blur">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Bulan Ini</p>
                            <span class="shrink-0 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-black text-emerald-700">
                                {{ $laporanBulanan['periode'] ?? '-' }}
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-emerald-50 p-3">
                                <p class="text-xl font-black text-slate-950">{{ number_format($laporanBulanan['jumlah_hadir'] ?? 0) }}</p>
                                <p class="mt-1 text-[11px] font-bold text-slate-500">Hadir</p>
                            </div>
                            <div class="rounded-2xl bg-sky-50 p-3">
                                <p class="text-xl font-black text-slate-950">{{ number_format($laporanBulanan['jumlah_pengukuran'] ?? 0) }}</p>
                                <p class="mt-1 text-[11px] font-bold text-slate-500">Ukur</p>
                            </div>
                            <div class="rounded-2xl bg-amber-50 p-3">
                                <p class="text-xl font-black text-slate-950">{{ number_format($laporanBulanan['jumlah_jadwal'] ?? 0) }}</p>
                                <p class="mt-1 text-[11px] font-bold text-slate-500">Jadwal</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.6rem] border border-white/80 bg-white/70 p-4 shadow-lg shadow-sky-100/70 backdrop-blur">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-400/25">
                                <i class="fa-solid fa-shield-heart"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-950">{{ number_format($stats['pengukuran_tervalidasi'] ?? 0) }} data tervalidasi</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ number_format($stats['pengukuran_revisi'] ?? 0) }} data perlu revisi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($cardStats as $card)
                @php $tone = $toneMap[$card['tone']] ?? $toneMap['emerald']; @endphp

                <a href="{{ $card['url'] }}"
                   class="group relative flex min-h-[168px] flex-col justify-between overflow-hidden rounded-[1.7rem] border bg-gradient-to-br {{ $tone['card'] }} p-5 shadow-[0_18px_55px_rgba(15,23,42,.08)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(15,23,42,.12)]">
                    <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/70 blur-2xl transition-all duration-500 group-hover:scale-125"></div>

                    <div class="relative flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-[.16em] {{ $tone['label'] }}">
                                {{ $card['label'] }}
                            </p>
                            <p class="mt-3 truncate text-3xl font-black tracking-tight text-slate-950">
                                {{ $card['value'] }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $tone['icon'] }} shadow-lg">
                            <i class="fa-solid {{ $card['icon'] }}"></i>
                        </div>
                    </div>

                    <p class="relative mt-4 line-clamp-2 text-sm font-semibold leading-5 text-slate-500">
                        {{ $card['desc'] }}
                    </p>
                </a>
            @endforeach
        </section>

        {{-- BREAKDOWN --}}
        <section class="grid gap-4 md:grid-cols-3">
            <a href="{{ $routeUrl('kader.data.balita.index') }}"
               class="group rounded-[1.65rem] border border-sky-200/70 bg-white/75 p-4 shadow-[0_18px_45px_rgba(14,165,233,.10)] backdrop-blur transition-all duration-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-black text-sky-800">Balita</p>
                        <p class="mt-1 text-3xl font-black text-slate-950">{{ number_format($stats['total_balita'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-400/25">
                        <i class="fa-solid fa-child-reaching"></i>
                    </div>
                </div>
            </a>

            <a href="{{ $routeUrl('kader.data.remaja.index') }}"
               class="group rounded-[1.65rem] border border-violet-200/70 bg-white/75 p-4 shadow-[0_18px_45px_rgba(139,92,246,.10)] backdrop-blur transition-all duration-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-black text-violet-800">Remaja</p>
                        <p class="mt-1 text-3xl font-black text-slate-950">{{ number_format($stats['total_remaja'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-lg shadow-violet-400/25">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </a>

            <a href="{{ $routeUrl('kader.data.lansia.index') }}"
               class="group rounded-[1.65rem] border border-emerald-200/70 bg-white/75 p-4 shadow-[0_18px_45px_rgba(16,185,129,.10)] backdrop-blur transition-all duration-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-black text-emerald-800">Lansia</p>
                        <p class="mt-1 text-3xl font-black text-slate-950">{{ number_format($stats['total_lansia'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-400/25">
                        <i class="fa-solid fa-person-cane"></i>
                    </div>
                </div>
            </a>
        </section>

        {{-- MAIN GRID --}}
        <section class="grid items-stretch gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(360px,.75fr)]">
            {{-- CHART --}}
            <div class="glass-card min-w-0 rounded-[2rem] border border-white/80 p-5 shadow-[0_24px_70px_rgba(15,23,42,.09)] sm:p-6">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Live Trend</p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                            Aktivitas Kader
                        </h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Klik titik grafik untuk melihat detail angka per tanggal.
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2">
                        <button type="button" data-range="7"
                            class="trend-range rounded-2xl bg-emerald-600 px-3.5 py-2 text-xs font-black text-white shadow-lg shadow-emerald-300 transition-all duration-300">
                            7 Hari
                        </button>
                        <button type="button" data-range="14"
                            class="trend-range rounded-2xl bg-white px-3.5 py-2 text-xs font-black text-slate-600 shadow-sm ring-1 ring-slate-200 transition-all duration-300 hover:bg-slate-50">
                            14 Hari
                        </button>
                        <button type="button" data-range="30"
                            class="trend-range rounded-2xl bg-white px-3.5 py-2 text-xs font-black text-slate-600 shadow-sm ring-1 ring-slate-200 transition-all duration-300 hover:bg-slate-50">
                            30 Hari
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[1.6rem] border border-slate-200/80 bg-white/75 p-3 shadow-inner">
                    <div id="kaderTrendChart" class="min-h-[330px] w-full"></div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/85 p-4">
                        <p class="text-xs font-black uppercase tracking-[.14em] text-emerald-700">Kehadiran</p>
                        <p id="trendTotalHadir" class="mt-2 text-2xl font-black text-slate-950">{{ number_format($totalTrendHadir) }}</p>
                    </div>
                    <div class="rounded-2xl border border-sky-100 bg-sky-50/85 p-4">
                        <p class="text-xs font-black uppercase tracking-[.14em] text-sky-700">Pengukuran</p>
                        <p id="trendTotalPengukuran" class="mt-2 text-2xl font-black text-slate-950">{{ number_format($totalTrendPengukuran) }}</p>
                    </div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/85 p-4">
                        <p class="text-xs font-black uppercase tracking-[.14em] text-amber-700">Detail Klik</p>
                        <p id="trendClickTitle" class="mt-2 truncate text-sm font-black text-slate-950">Belum dipilih</p>
                        <p id="trendClickValue" class="mt-1 line-clamp-1 text-xs font-bold text-slate-500">
                            Klik titik grafiknya, jangan cuma ditatap.
                        </p>
                    </div>
                </div>
            </div>

            {{-- JADWAL --}}
            <div class="glass-card flex min-w-0 flex-col rounded-[2rem] border border-white/80 p-5 shadow-[0_24px_70px_rgba(15,118,110,.10)] sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Agenda</p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                            Jadwal Posyandu
                        </h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Dikelola Bidan, Kader hanya melihat.
                        </p>
                    </div>

                    @if(Route::has('kader.jadwal.index'))
                        <a href="{{ route('kader.jadwal.index') }}"
                           class="shrink-0 rounded-2xl bg-emerald-600 px-3.5 py-2 text-xs font-black text-white shadow-lg shadow-emerald-300 transition-all duration-300 hover:bg-emerald-500">
                            Lihat
                        </a>
                    @endif
                </div>

                <div class="soft-scroll max-h-[454px] space-y-3 overflow-y-auto pr-1">
                    @forelse($jadwalMendatang as $jadwal)
                        <a href="{{ Route::has('kader.jadwal.show') ? route('kader.jadwal.show', $jadwal->id) : '#' }}"
                           class="group block rounded-[1.5rem] border border-emerald-100/90 bg-gradient-to-br from-white to-emerald-50/70 p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                            <div class="flex min-w-0 gap-4">
                                <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-lg shadow-emerald-300">
                                    <span class="text-xl font-black leading-none">{{ $formatDate($jadwal->tanggal ?? null, 'd') }}</span>
                                    <span class="mt-1 text-[10px] font-black uppercase">{{ $formatDate($jadwal->tanggal ?? null, 'M') }}</span>
                                </div>

                                <div class="min-w-0 flex-1 pt-0.5">
                                    <div class="flex min-w-0 flex-wrap items-center gap-2">
                                        <h3 class="line-clamp-1 text-sm font-black text-slate-950">
                                            {{ $jadwal->judul ?? 'Jadwal Posyandu' }}
                                        </h3>

                                        @if(!empty($jadwal->tanggal) && Carbon::parse($jadwal->tanggal)->isToday())
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-black text-amber-700 ring-1 ring-amber-200">
                                                Hari Ini
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-xs font-black text-slate-600">
                                        {{ $formatTime($jadwal->waktu_mulai ?? null) }} WIB
                                        @if(!empty($jadwal->waktu_selesai))
                                            sampai {{ $formatTime($jadwal->waktu_selesai ?? null) }} WIB
                                        @endif
                                    </p>

                                    @if(!empty($jadwal->lokasi))
                                        <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">
                                            <i class="fa-solid fa-location-dot mr-1 text-emerald-500"></i>
                                            {{ $jadwal->lokasi }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-emerald-200 bg-emerald-50/60 p-6 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                                <i class="fa-solid fa-calendar-xmark"></i>
                            </div>
                            <p class="mt-3 text-sm font-black text-slate-800">Belum ada jadwal aktif.</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Jadwal akan muncul setelah dibuat oleh Bidan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- BOTTOM GRID --}}
        <section class="grid items-stretch gap-5 xl:grid-cols-3">
            {{-- PENGUKURAN TERBARU --}}
            <div class="glass-card flex min-w-0 flex-col rounded-[2rem] border border-white/80 p-5 shadow-[0_24px_70px_rgba(15,23,42,.08)] sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Validasi</p>
                        <h2 class="mt-1 text-lg font-black text-slate-950">Pengukuran Terbaru</h2>
                    </div>

                    <a href="{{ $routeUrl('kader.pemeriksaan.index') }}"
                       class="shrink-0 rounded-2xl bg-emerald-600 px-3 py-2 text-xs font-black text-white shadow-lg shadow-emerald-300">
                        Buka
                    </a>
                </div>

                <div class="soft-scroll max-h-[360px] space-y-3 overflow-y-auto pr-1">
                    @forelse($pengukuranTerbaru as $item)
                        <div class="rounded-[1.4rem] border border-slate-200/80 bg-white/75 p-4 shadow-sm">
                            <div class="flex min-w-0 items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="line-clamp-1 text-sm font-black text-slate-950">{{ $item->nama }}</h3>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ $item->kategori }} • {{ $formatDate($item->tanggal ?? null, 'd M Y') }}
                                    </p>
                                </div>

                                <span class="shrink-0 rounded-full border px-2.5 py-1 text-[10px] font-black {{ $badgeClass($item->badge ?? 'amber') }}">
                                    {{ $item->status ?? 'Menunggu' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.4rem] border border-dashed border-slate-200 bg-white/60 p-5 text-center">
                            <p class="text-sm font-black text-slate-700">Belum ada pengukuran.</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Data muncul setelah Kader input pemeriksaan awal.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- SASARAN BARU --}}
            <div class="glass-card flex min-w-0 flex-col rounded-[2rem] border border-white/80 p-5 shadow-[0_24px_70px_rgba(15,23,42,.08)] sm:p-6">
                <div class="mb-5">
                    <p class="text-xs font-black uppercase tracking-[.18em] text-sky-700">Database</p>
                    <h2 class="mt-1 text-lg font-black text-slate-950">Data Sasaran Terbaru</h2>
                </div>

                <div class="soft-scroll max-h-[360px] space-y-3 overflow-y-auto pr-1">
                    @forelse($sasaranBaru as $item)
                        <div class="flex min-w-0 items-center gap-3 rounded-[1.4rem] border border-slate-200/80 bg-white/75 p-4 shadow-sm">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $badgeClass($item->tone ?? 'emerald') }}">
                                <i class="fa-solid {{ $item->icon ?? 'fa-user' }}"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="line-clamp-1 text-sm font-black text-slate-950">{{ $item->nama }}</h3>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    {{ $item->kategori }} • {{ optional($item->created_at)->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.4rem] border border-dashed border-slate-200 bg-white/60 p-5 text-center">
                            <p class="text-sm font-black text-slate-700">Data sasaran masih kosong.</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Tambahkan Balita, Remaja, atau Lansia terlebih dahulu.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- LAPORAN --}}
            <div class="relative flex min-w-0 flex-col overflow-hidden rounded-[2rem] border border-emerald-200/80 bg-gradient-to-br from-emerald-50 via-teal-50 to-sky-50 p-5 shadow-[0_24px_70px_rgba(15,118,110,.13)] sm:p-6">
                <div class="absolute -right-20 -top-20 h-60 w-60 rounded-full bg-emerald-300/30 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-10 h-60 w-60 rounded-full bg-sky-300/25 blur-3xl"></div>

                <div class="relative flex h-full flex-col">
                    <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Rekap</p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Laporan Bulanan</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-slate-600">
                        Ringkasan operasional bulan {{ $laporanBulanan['periode'] ?? '-' }} untuk bahan laporan Kader.
                    </p>

                    <div class="mt-6 grid gap-3">
                        <div class="rounded-[1.4rem] border border-white/80 bg-white/70 p-4 shadow-sm backdrop-blur">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Jadwal bulan ini</span>
                                <span class="text-2xl font-black text-slate-950">{{ number_format($laporanBulanan['jumlah_jadwal'] ?? 0) }}</span>
                            </div>
                        </div>

                        <div class="rounded-[1.4rem] border border-white/80 bg-white/70 p-4 shadow-sm backdrop-blur">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Kehadiran tercatat</span>
                                <span class="text-2xl font-black text-slate-950">{{ number_format($laporanBulanan['jumlah_hadir'] ?? 0) }}</span>
                            </div>
                        </div>

                        <div class="rounded-[1.4rem] border border-white/80 bg-white/70 p-4 shadow-sm backdrop-blur">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Pengukuran fisik</span>
                                <span class="text-2xl font-black text-slate-950">{{ number_format($laporanBulanan['jumlah_pengukuran'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>

                    @if(Route::has('kader.laporan.index'))
                        <a href="{{ route('kader.laporan.index') }}"
                           class="relative mt-auto inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/25 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl">
                            <i class="fa-solid fa-file-lines"></i>
                            Buka Laporan Kader
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
(function () {
    const endpoint = @json(Route::has('kader.dashboard.trend') ? route('kader.dashboard.trend') : null);
    const initialTrend = @json($trend ?? ['labels' => [], 'series' => []]);
    const chartElement = document.querySelector('#kaderTrendChart');

    if (!chartElement || !window.ApexCharts) {
        return;
    }

    let currentRange = 7;

    const numberFormat = new Intl.NumberFormat('id-ID');

    const titleEl = document.querySelector('#trendClickTitle');
    const valueEl = document.querySelector('#trendClickValue');
    const totalHadirEl = document.querySelector('#trendTotalHadir');
    const totalPengukuranEl = document.querySelector('#trendTotalPengukuran');

    function setClickedDetail(label, seriesName, value) {
        if (!titleEl || !valueEl) return;

        titleEl.textContent = label || 'Data dipilih';
        valueEl.textContent = `${seriesName}: ${numberFormat.format(value || 0)} data`;
    }

    function updateSummary(data) {
        if (!data || !data.summary) return;

        if (totalHadirEl) {
            totalHadirEl.textContent = numberFormat.format(data.summary.total_hadir || 0);
        }

        if (totalPengukuranEl) {
            totalPengukuranEl.textContent = numberFormat.format(data.summary.total_pengukuran || 0);
        }
    }

    const chart = new ApexCharts(chartElement, {
        chart: {
            type: 'area',
            height: 330,
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
            foreColor: '#475569',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 450,
                animateGradually: {
                    enabled: true,
                    delay: 80
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            },
            events: {
                dataPointSelection: function (event, chartContext, config) {
                    const label = chartContext.w.globals.labels[config.dataPointIndex];
                    const seriesName = chartContext.w.globals.seriesNames[config.seriesIndex];
                    const value = chartContext.w.globals.series[config.seriesIndex][config.dataPointIndex];

                    setClickedDetail(label, seriesName, value);
                }
            }
        },
        colors: ['#10b981', '#0ea5e9'],
        series: initialTrend.series || [],
        xaxis: {
            categories: initialTrend.labels || [],
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px',
                    fontWeight: 800
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
            tooltip: { enabled: false }
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px',
                    fontWeight: 800
                }
            }
        },
        stroke: {
            curve: 'smooth',
            width: 4,
            lineCap: 'round'
        },
        dataLabels: { enabled: false },
        markers: {
            size: 5,
            strokeWidth: 3,
            strokeColors: '#ffffff',
            hover: { size: 8 }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 5,
            padding: {
                top: 8,
                right: 14,
                bottom: 0,
                left: 8
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 0.8,
                opacityFrom: 0.34,
                opacityTo: 0.04,
                stops: [0, 86, 100]
            }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (value) {
                    return numberFormat.format(value || 0) + ' data';
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            fontWeight: 900,
            fontSize: '13px',
            labels: {
                colors: '#334155'
            },
            markers: {
                width: 10,
                height: 10,
                radius: 999
            },
            itemMargin: {
                horizontal: 12,
                vertical: 4
            }
        },
        responsive: [
            {
                breakpoint: 768,
                options: {
                    chart: {
                        height: 300
                    },
                    stroke: {
                        width: 3
                    },
                    markers: {
                        size: 4
                    },
                    legend: {
                        fontSize: '12px'
                    }
                }
            }
        ]
    });

    chart.render();
    updateSummary(initialTrend);

    async function refreshTrend(range = currentRange) {
        if (!endpoint) return;

        try {
            const response = await fetch(`${endpoint}?range=${range}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) return;

            const data = await response.json();

            chart.updateOptions({
                xaxis: {
                    categories: data.labels || []
                }
            }, false, true);

            chart.updateSeries(data.series || [], true);
            updateSummary(data);
        } catch (error) {
            console.warn('Gagal memuat trend dashboard Kader:', error);
        }
    }

    document.querySelectorAll('.trend-range').forEach((button) => {
        button.addEventListener('click', function () {
            currentRange = parseInt(this.dataset.range || '7', 10);

            document.querySelectorAll('.trend-range').forEach((item) => {
                item.classList.remove('bg-emerald-600', 'text-white', 'shadow-lg', 'shadow-emerald-300');
                item.classList.add('bg-white', 'text-slate-600', 'shadow-sm', 'ring-1', 'ring-slate-200');
            });

            this.classList.add('bg-emerald-600', 'text-white', 'shadow-lg', 'shadow-emerald-300');
            this.classList.remove('bg-white', 'text-slate-600', 'shadow-sm', 'ring-1', 'ring-slate-200');

            refreshTrend(currentRange);
        });
    });

    window.addEventListener('focus', function () {
        refreshTrend(currentRange);
    });

    setInterval(function () {
        refreshTrend(currentRange);
    }, 30000);
})();
</script>
@endpush