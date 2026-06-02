@extends('layouts.kader')

@section('title', 'Detail Absensi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $absensi = $absensi ?? null;
    $details = $details ?? collect();
    $semuaSesi = $semuaSesi ?? collect();

    $totalPasien = (int) ($totalPasien ?? $details->count());
    $totalHadir = (int) ($totalHadir ?? $details->where('hadir', true)->count());
    $totalAbsen = (int) ($totalAbsen ?? max(0, $totalPasien - $totalHadir));

    $persenHadir = $totalPasien > 0 ? round(($totalHadir / $totalPasien) * 100, 1) : 0;
    $persenTidak = $totalPasien > 0 ? round(($totalAbsen / $totalPasien) * 100, 1) : 0;

    $routeHas = fn ($name) => Route::has($name);

    $kategori = $absensi?->kategori ?? 'balita';

    $kategoriLabel = match ($kategori) {
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        default => 'Balita',
    };

    $kategoriIcon = match ($kategori) {
        'remaja' => 'fa-user-graduate',
        'lansia' => 'fa-person-cane',
        default => 'fa-child-reaching',
    };

    $kategoriTone = match ($kategori) {
        'remaja' => 'violet',
        'lansia' => 'sky',
        default => 'emerald',
    };

    $formatTanggal = function ($date, $format = 'd F Y') {
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
                'ring' => 'border-violet-200 bg-violet-50 text-violet-800',
                default => 'from-violet-50 via-fuchsia-50 to-white border-violet-200 text-violet-800',
            },
            'sky' => match ($type) {
                'solid' => 'bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-sky-400/20',
                'ring' => 'border-sky-200 bg-sky-50 text-sky-800',
                default => 'from-sky-50 via-cyan-50 to-white border-sky-200 text-sky-800',
            },
            default => match ($type) {
                'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-emerald-400/20',
                'ring' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                default => 'from-emerald-50 via-teal-50 to-white border-emerald-200 text-emerald-800',
            },
        };
    };

    $statusRekap = 'Belum Ada Kehadiran';
    $statusTone = 'rose';

    if ($totalPasien <= 0) {
        $statusRekap = 'Belum Ada Peserta';
        $statusTone = 'slate';
    } elseif ($totalHadir === $totalPasien) {
        $statusRekap = 'Semua Hadir';
        $statusTone = 'emerald';
    } elseif ($totalHadir > 0) {
        $statusRekap = 'Sebagian Hadir';
        $statusTone = 'amber';
    }

    $kodeAbsensi = $absensi?->kode_absensi ?? 'ABS-' . str_pad($absensi?->id ?? 0, 4, '0', STR_PAD_LEFT);
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

    .detail-list {
    height: 610px;
    max-height: 610px;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-gutter: stable;
    padding-right: 8px;
}

.detail-main-card,
.detail-side-panel {
    height: 100%;
}

.detail-main-card {
    display: flex;
    flex-direction: column;
}

.detail-side-panel {
    display: flex;
    flex-direction: column;
}

.detail-side-panel .side-fill {
    flex: 1;
}

    .detail-list::-webkit-scrollbar {
        width: 7px;
    }

    .detail-list::-webkit-scrollbar-track {
        background: rgba(226, 232, 240, .50);
        border-radius: 999px;
    }

    .detail-list::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, .42);
        border-radius: 999px;
    }

    .detail-row {
        transition: border-color .16s ease, background .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .detail-row:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 26px rgba(15, 23, 42, .065);
    }

    @media (max-width: 1279px) {
    .detail-list {
        height: auto;
        max-height: none;
        overflow: visible;
        padding-right: 0;
    }

    .detail-main-card,
    .detail-side-panel {
        height: auto;
    }

    .detail-side-panel {
        display: block;
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
                            Attendance Detail
                        </div>

                        <h1 class="mt-4 max-w-3xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2.05rem]">
                            Detail absensi {{ $kategoriLabel }}.
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-600">
                            Menampilkan rincian hadir dan tidak hadir untuk satu sesi posyandu. Ini bagian yang bikin rekap bisa dipertanggungjawabkan, bukan sekadar angka cakep di dashboard.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if($routeHas('kader.absensi.riwayat'))
                            <a href="{{ route('kader.absensi.riwayat', [
                                'kategori' => $kategori,
                                'bulan' => $absensi?->bulan ?? now('Asia/Jakarta')->month,
                                'tahun' => $absensi?->tahun ?? now('Asia/Jakarta')->year,
                            ]) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                Riwayat Absensi
                            </a>
                        @endif

                        @if($routeHas('kader.absensi.index'))
                            <a href="{{ route('kader.absensi.index', [
                                'kategori' => $kategori,
                                'tanggal' => optional($absensi?->tanggal_posyandu)->format('Y-m-d'),
                            ]) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Update Presensi
                            </a>
                        @endif
                    </div>
                </div>

                <div class="grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.45rem] border border-white/80 bg-white/75 p-4 shadow-md shadow-emerald-100/60">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Kode Sesi</p>
                            <span class="shrink-0 rounded-full border px-3 py-1 text-xs font-black {{ $badgeClass($kategoriTone) }}">
                                {{ $kategoriLabel }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $toneClass($kategoriTone, 'solid') }}">
                                <i class="fa-solid {{ $kategoriIcon }} text-lg"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="line-clamp-1 text-sm font-black text-slate-950">{{ $kodeAbsensi }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Pertemuan ke-{{ $absensi?->nomor_pertemuan ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.45rem] border border-white/80 bg-white/75 p-4 shadow-md shadow-sky-100/60">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-400/20">
                                <i class="fa-solid fa-calendar-day"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-950">
                                    {{ $formatTanggal($absensi?->tanggal_posyandu ?? null, 'd F Y') }}
                                </p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Dicatat oleh {{ $absensi?->kader?->name ?? 'Kader' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SUMMARY --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[1.55rem] border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-white p-4 soft-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-slate-600">Total Peserta</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalPasien) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Catatan sasaran</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 text-white shadow-lg shadow-slate-400/20">
                        <i class="fa-solid fa-users-line"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.55rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-green-50 to-white p-4 soft-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">Hadir</p>
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
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalAbsen) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $persenTidak }}% tidak hadir</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 text-white shadow-lg shadow-orange-400/20">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.55rem] border border-violet-200 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-white p-4 soft-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-violet-700">Status Rekap</p>
                        <p class="mt-2 text-xl font-black tracking-tight text-slate-950">{{ $statusRekap }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Kualitas sesi</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-lg shadow-violet-400/20">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </section>

        {{-- MAIN --}}
        <section class="grid items-stretch gap-5 xl:grid-cols-[minmax(0,1.48fr)_minmax(330px,.58fr)]">
            <div class="detail-main-card glass-card min-w-0 rounded-[1.75rem] border border-white/80 p-5 soft-card">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Daftar Detail</p>
                        <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950 sm:text-xl">
                            Rincian kehadiran {{ $kategoriLabel }}
                        </h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Menampilkan {{ number_format($totalPasien) }} data sasaran dalam sesi ini.
                        </p>
                    </div>

                    <div class="w-full max-w-md">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text"
                                   id="detailSearch"
                                   placeholder="Cari nama, NIK, status..."
                                   class="w-full rounded-2xl border border-slate-200 bg-white/85 py-3 pl-11 pr-4 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                        </div>
                    </div>
                </div>

                @if($details->isNotEmpty())
                    <div class="detail-list flex-1 space-y-3">
                        @foreach($details as $index => $detail)
                            @php
                                $hadir = (bool) $detail->hadir;
                                $statusText = $hadir ? 'Hadir' : 'Tidak Hadir';
                                $statusTone = $hadir ? 'emerald' : 'orange';
                                $statusIcon = $hadir ? 'fa-circle-check' : 'fa-circle-xmark';

                                $nama = $detail->nama_pasien ?? $detail->pasien?->nama_lengkap ?? $detail->pasien?->nama ?? 'Data sasaran';
                                $nik = $detail->nik_pasien ?? $detail->pasien?->nik ?? '-';
                                $usia = $detail->usia_pasien ?? '-';
                                $infoTambahan = $detail->info_tambahan_pasien ?? '-';
                                $keterangan = $detail->keterangan_text ?? ($detail->keterangan ?: '-');
                            @endphp

                            <div class="detail-row rounded-[1.35rem] border border-slate-200 bg-white/80 p-4"
                                 data-search="{{ strtolower($nama . ' ' . $nik . ' ' . $statusText . ' ' . $keterangan) }}">
                                <div class="grid gap-4 xl:grid-cols-[52px_minmax(0,1fr)_220px] xl:items-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $toneClass($kategoriTone, 'ring') }} border text-sm font-black">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                                            <h3 class="line-clamp-1 text-[15px] font-black text-slate-950">{{ $nama }}</h3>

                                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-black {{ $badgeClass($statusTone) }}">
                                                <i class="fa-solid {{ $statusIcon }} mr-1"></i>
                                                {{ $statusText }}
                                            </span>
                                        </div>

                                        <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold text-slate-500">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                                <i class="fa-solid fa-id-card text-slate-400"></i>
                                                {{ $nik }}
                                            </span>

                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                                <i class="fa-solid fa-cake-candles text-slate-400"></i>
                                                {{ $usia }}
                                            </span>

                                            <span class="inline-flex min-w-0 items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                                <i class="fa-solid fa-circle-info text-slate-400"></i>
                                                <span class="line-clamp-1">{{ $infoTambahan }}</span>
                                            </span>
                                        </div>

                                        <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-xs font-bold text-slate-600">
                                            <i class="fa-solid fa-note-sticky mr-1 text-slate-400"></i>
                                            {{ $keterangan }}
                                        </div>
                                    </div>

                                    <div class="rounded-[1.25rem] border p-4 text-center {{ $badgeClass($statusTone) }}">
                                        <p class="text-[10px] font-black uppercase tracking-[.14em]">Status</p>
                                        <p class="mt-1 text-sm font-black">{{ $statusText }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-[1.55rem] border border-dashed border-emerald-200 bg-emerald-50/65 p-8 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                            <i class="fa-solid fa-users-slash text-lg"></i>
                        </div>
                        <h3 class="mt-4 text-base font-black text-slate-950">Detail absensi belum tersedia</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm font-medium leading-6 text-slate-500">
                            Sesi ini belum memiliki detail peserta. Cek ulang input absensi atau hapus sesi jika data tidak valid.
                        </p>
                    </div>
                @endif
            </div>

            {{-- SIDE PANEL --}}
            <aside class="detail-side-panel min-w-0 space-y-5 xl:sticky xl:top-5">
                <div class="glass-card rounded-[1.75rem] border border-white/80 p-5 soft-card">
                    <div class="rounded-[1.45rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-sky-50 p-5">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Informasi Sesi</p>
                        <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950">Ringkasan absensi</h2>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-black text-slate-600">Kode</span>
                                    <span class="text-right text-xs font-black text-slate-950">{{ $kodeAbsensi }}</span>
                                </div>
                            </div>

                            <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-black text-slate-600">Kategori</span>
                                    <span class="text-sm font-black text-slate-950">{{ $kategoriLabel }}</span>
                                </div>
                            </div>

                            <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-black text-slate-600">Tanggal</span>
                                    <span class="text-sm font-black text-slate-950">
                                        {{ $formatTanggal($absensi?->tanggal_posyandu ?? null, 'd M Y') }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-black text-slate-600">Status</span>
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $badgeClass($statusTone) }}">
                                        {{ $statusRekap }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 h-3 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500"
                                 style="width: {{ min(100, $persenHadir) }}%"></div>
                        </div>

                        <p class="mt-3 text-xs font-bold text-slate-500">
                            {{ $persenHadir }}% sasaran tercatat hadir pada sesi ini.
                        </p>
                    </div>
                </div>

                <div class="side-fill glass-card rounded-[1.75rem] border border-white/80 p-5 soft-card">
    <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Sesi Terdekat</p>
                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950">Riwayat kategori sama</h2>

                    <div class="mt-4 space-y-3">
                        @forelse($semuaSesi as $sesi)
                            <a href="{{ $routeHas('kader.absensi.show') ? route('kader.absensi.show', $sesi->id) : '#' }}"
                               class="block rounded-[1.2rem] border border-slate-200 bg-white/75 p-4 transition hover:-translate-y-0.5 hover:border-emerald-200">
                                <p class="line-clamp-1 text-sm font-black text-slate-950">
                                    {{ $sesi->kode_absensi ?? 'ABS-' . str_pad($sesi->id, 4, '0', STR_PAD_LEFT) }}
                                </p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    {{ $formatTanggal($sesi->tanggal_posyandu ?? null, 'd M Y') }}
                                    • Pertemuan ke-{{ $sesi->nomor_pertemuan ?? '-' }}
                                </p>
                            </a>
                        @empty
                            <div class="rounded-[1.2rem] border border-dashed border-slate-200 bg-white/65 p-5 text-center">
                                <p class="text-sm font-black text-slate-700">Belum ada sesi lain.</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">Kategori ini baru punya satu sesi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const searchInput = document.querySelector('#detailSearch');
    const rows = Array.from(document.querySelectorAll('.detail-row'));
    let timer = null;

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        clearTimeout(timer);

        timer = setTimeout(() => {
            const keyword = this.value.trim().toLowerCase();

            rows.forEach((row) => {
                const text = row.dataset.search || '';
                row.classList.toggle('hidden', keyword !== '' && !text.includes(keyword));
            });
        }, 80);
    });
})();
</script>
@endpush