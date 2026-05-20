@extends('layouts.kader')

@section('title', 'Dashboard Kader')

@php
    $fullName = auth()->user()->name ?? 'Kader';
    $firstName = trim(explode(' ', $fullName)[0]) ?: 'Kader';
    $maxChart = max($chartData ?: [0]) ?: 1;

    $routeHas = fn ($name) => \Illuminate\Support\Facades\Route::has($name);
@endphp

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <section class="relative overflow-hidden rounded-[28px] border border-emerald-100/80 bg-white/85 p-6 shadow-[0_22px_55px_rgba(15,23,42,.06)]">
        <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-emerald-100/80 blur-3xl"></div>
        <div class="absolute -bottom-24 left-24 h-56 w-56 rounded-full bg-amber-100/70 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">
                    <i class="fa-solid fa-user-nurse"></i>
                    Command Center Kader
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 md:text-3xl">
                    Halo, {{ $firstName }}
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Pantau data sasaran, absensi, pengukuran fisik, jadwal dari Bidan, dan rekap laporan bulanan dari satu halaman.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex sm:items-center">
                @if($routeHas('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-clipboard-check"></i>
                        Absensi
                    </a>
                @endif

                @if($routeHas('kader.pemeriksaan.index'))
                    <a href="{{ route('kader.pemeriksaan.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-700">
                        <i class="fa-solid fa-weight-scale"></i>
                        Pengukuran
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- STAT UTAMA --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="kader-card p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Total Sasaran</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['total_sasaran']) }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">Balita, remaja, dan lansia</p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
        </div>

        <div class="kader-card p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Absensi Hari Ini</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['hadir_hari_ini']) }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        {{ $stats['persentase_hari_ini'] }}% dari {{ number_format($stats['target_absensi_hari_ini']) }} sasaran
                    </p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-50 text-sky-600">
                    <i class="fa-solid fa-user-check text-lg"></i>
                </div>
            </div>
        </div>

        <div class="kader-card p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Menunggu Validasi</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['pengukuran_pending']) }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">Pengukuran fisik oleh kader</p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-hourglass-half text-lg"></i>
                </div>
            </div>
        </div>

        <div class="kader-card p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Jadwal Hari Ini</p>
                    <h2 class="mt-3 text-xl font-black text-slate-900">
                        {{ $jadwalHariIni ? 'Ada Kegiatan' : 'Tidak Ada' }}
                    </h2>
                    <p class="mt-1 line-clamp-1 text-xs font-bold text-slate-400">
                        {{ $jadwalHariIni->judul ?? 'Belum ada jadwal aktif hari ini' }}
                    </p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-violet-50 text-violet-600">
                    <i class="fa-solid fa-calendar-day text-lg"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- BREAKDOWN SASARAN --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-[24px] border border-emerald-100 bg-white/80 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-child-reaching"></i>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total_balita']) }}</p>
                    <p class="text-xs font-black uppercase tracking-[.1em] text-slate-400">Balita / Anak</p>
                </div>
            </div>
        </div>

        <div class="rounded-[24px] border border-indigo-100 bg-white/80 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="grid h-11 w-11 place-items-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total_remaja']) }}</p>
                    <p class="text-xs font-black uppercase tracking-[.1em] text-slate-400">Remaja</p>
                </div>
            </div>
        </div>

        <div class="rounded-[24px] border border-amber-100 bg-white/80 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-person-cane"></i>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total_lansia']) }}</p>
                    <p class="text-xs font-black uppercase tracking-[.1em] text-slate-400">Lansia</p>
                </div>
            </div>
        </div>
    </section>

    {{-- KONTEN UTAMA --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

        {{-- GRAFIK ABSENSI --}}
        <div class="kader-card p-5 xl:col-span-7">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Grafik Absensi 7 Hari</h3>
                    <p class="text-xs font-bold text-slate-400">Berdasarkan data absensi yang tercatat</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-black text-emerald-700">
                    Live Rekap
                </span>
            </div>

            <div class="flex h-64 items-end gap-3 rounded-[22px] border border-slate-100 bg-slate-50/70 p-4">
                @foreach($chartData as $index => $value)
                    @php
                        $height = $maxChart > 0 ? max(8, ($value / $maxChart) * 100) : 8;
                    @endphp

                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                        <div class="text-[11px] font-black text-slate-500">{{ $value }}</div>
                        <div class="w-full rounded-t-2xl bg-gradient-to-t from-emerald-600 to-emerald-300 shadow-sm transition hover:opacity-80"
                             style="height: {{ $height }}%">
                        </div>
                        <div class="text-[10px] font-black text-slate-400">{{ $chartLabels[$index] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- JADWAL --}}
        <div class="kader-card p-5 xl:col-span-5">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Jadwal Posyandu</h3>
                    <p class="text-xs font-bold text-slate-400">Read-only dari Bidan</p>
                </div>
                <i class="fa-solid fa-calendar-check text-emerald-500"></i>
            </div>

            <div class="space-y-3">
                @forelse($jadwalMendatang as $jadwal)
                    <div class="flex items-center gap-4 rounded-[20px] border border-slate-100 bg-white p-4 shadow-sm">
                        <div class="grid h-14 w-14 flex-shrink-0 place-items-center rounded-2xl bg-emerald-50 text-center text-emerald-700">
                            <div>
                                <p class="text-lg font-black leading-4">{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d') }}</p>
                                <p class="text-[10px] font-black uppercase">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('M') }}</p>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-sm font-black text-slate-800">{{ $jadwal->judul }}</h4>
                            <p class="mt-1 text-xs font-bold text-slate-400">
                                <i class="fa-regular fa-clock mr-1"></i>
                                {{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H:i') }} WIB
                            </p>
                        </div>

                        @if(\Carbon\Carbon::parse($jadwal->tanggal)->isToday())
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black text-amber-700">
                                Hari Ini
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="rounded-[22px] border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
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
        <div class="kader-card p-5">
            <div class="mb-5">
                <h3 class="text-lg font-black text-slate-900">Pengukuran Terbaru</h3>
                <p class="text-xs font-bold text-slate-400">Status validasi Bidan</p>
            </div>

            <div class="space-y-3">
                @forelse($pengukuranTerbaru as $item)
                    <div class="rounded-[18px] border border-slate-100 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="truncate text-sm font-black text-slate-800">{{ $item->nama }}</h4>
                                <p class="mt-1 text-xs font-bold text-slate-400">{{ $item->kategori }}</p>
                            </div>

                            @php
                                $badgeClass = match($item->badge) {
                                    'emerald' => 'bg-emerald-50 text-emerald-700',
                                    'rose' => 'bg-rose-50 text-rose-700',
                                    default => 'bg-amber-50 text-amber-700',
                                };
                            @endphp

                            <span class="rounded-full px-3 py-1 text-[10px] font-black {{ $badgeClass }}">
                                {{ $item->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-[18px] bg-slate-50 p-5 text-center text-xs font-bold text-slate-400">
                        Belum ada pengukuran fisik.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- SASARAN BARU --}}
        <div class="kader-card p-5">
            <div class="mb-5">
                <h3 class="text-lg font-black text-slate-900">Data Sasaran Terbaru</h3>
                <p class="text-xs font-bold text-slate-400">Balita, remaja, dan lansia</p>
            </div>

            <div class="space-y-3">
                @forelse($sasaranBaru as $item)
                    <div class="flex items-center gap-3 rounded-[18px] border border-slate-100 bg-white p-3">
                        <div class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <i class="fa-solid {{ $item->icon }}"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-sm font-black text-slate-800">{{ $item->nama }}</h4>
                            <p class="text-xs font-bold text-slate-400">{{ $item->kategori }}</p>
                        </div>

                        <p class="text-[10px] font-black text-slate-300">
                            {{ optional($item->created_at)->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <p class="rounded-[18px] bg-slate-50 p-5 text-center text-xs font-bold text-slate-400">
                        Data sasaran masih kosong.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- LAPORAN --}}
        <div class="kader-card p-5">
            <div class="mb-5">
                <h3 class="text-lg font-black text-slate-900">Laporan Bulanan</h3>
                <p class="text-xs font-bold text-slate-400">{{ $laporanBulanan['periode'] }}</p>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-[18px] bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Jadwal bulan ini</span>
                    <span class="text-sm font-black text-slate-900">{{ number_format($laporanBulanan['jumlah_jadwal']) }}</span>
                </div>

                <div class="flex items-center justify-between rounded-[18px] bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Kehadiran tercatat</span>
                    <span class="text-sm font-black text-slate-900">{{ number_format($laporanBulanan['jumlah_hadir']) }}</span>
                </div>

                <div class="flex items-center justify-between rounded-[18px] bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Pengukuran fisik</span>
                    <span class="text-sm font-black text-slate-900">{{ number_format($laporanBulanan['jumlah_pengukuran']) }}</span>
                </div>

                @if($routeHas('kader.laporan.index'))
                    <a href="{{ route('kader.laporan.index') }}" class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-file-lines"></i>
                        Buka Laporan
                    </a>
                @endif
            </div>
        </div>
    </section>

</div>
@endsection