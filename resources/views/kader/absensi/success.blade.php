@extends('layouts.kader')

@section('title', 'Presensi Berhasil')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $absensi = $absensi ?? null;
    $details = $absensi?->details ?? collect();

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

    $totalPeserta = $details->count();
    $totalHadir = $details->where('hadir', true)->count();
    $totalTidakHadir = $details->where('hadir', false)->count();

    $persenHadir = $totalPeserta > 0 ? round(($totalHadir / $totalPeserta) * 100, 1) : 0;
    $persenTidak = $totalPeserta > 0 ? round(($totalTidakHadir / $totalPeserta) * 100, 1) : 0;

    $kodeAbsensi = $absensi?->kode_absensi ?? 'ABS-' . str_pad($absensi?->id ?? 0, 4, '0', STR_PAD_LEFT);

    $routeHas = fn ($name) => Route::has($name);

    $formatTanggal = function ($date, $format = 'd F Y') {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    try {
        $tanggalYmd = $absensi?->tanggal_posyandu
            ? Carbon::parse($absensi->tanggal_posyandu)->format('Y-m-d')
            : now('Asia/Jakarta')->toDateString();
    } catch (\Throwable $e) {
        $tanggalYmd = now('Asia/Jakarta')->toDateString();
    }

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

    $statusText = 'Presensi Tersimpan';
    $statusTone = 'emerald';

    if ($totalPeserta <= 0) {
        $statusText = 'Belum Ada Detail';
        $statusTone = 'slate';
    } elseif ($totalHadir === $totalPeserta) {
        $statusText = 'Semua Hadir';
        $statusTone = 'emerald';
    } elseif ($totalHadir <= 0) {
        $statusText = 'Semua Tidak Hadir';
        $statusTone = 'orange';
    } else {
        $statusText = 'Sebagian Hadir';
        $statusTone = 'amber';
    }

    $previewLimit = 6;
    $remainingDetails = max(0, $details->count() - $previewLimit);
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

    .success-orb {
        animation: successPulse 2.4s ease-in-out infinite;
    }

    @keyframes successPulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 18px 50px rgba(16, 185, 129, .24);
        }

        50% {
            transform: scale(1.04);
            box-shadow: 0 24px 65px rgba(16, 185, 129, .34);
        }
    }

    .success-row {
        transition: border-color .16s ease, background .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .success-row:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 26px rgba(15, 23, 42, .065);
    }

    .success-main-card,
.success-side-panel {
    height: auto;
}

.success-main-card,
.success-side-panel {
    height: 100%;
}

.success-main-card {
    display: flex;
    flex-direction: column;
}

.success-side-panel {
    display: flex;
    flex-direction: column;
}

.success-side-panel .side-fill {
    flex: 1;
}

.success-preview-list {
    max-height: none;
    overflow: visible;
    padding-right: 0;
}

.success-main-footer {
    margin-top: auto;
    padding-top: 1rem;
}

@media (max-width: 1279px) {
    .success-main-card,
    .success-side-panel {
        height: auto;
    }

    .success-main-card,
    .success-side-panel {
        display: block;
    }

    .success-side-panel .side-fill {
        flex: unset;
    }

    .success-main-footer {
        margin-top: 1rem;
        padding-top: 0;
    }
}

    .action-pill {
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease, background .16s ease;
    }

    .action-pill:hover {
        transform: translateY(-1px);
    }

    @media (max-width: 1279px) {
        .success-main-card,
        .success-side-panel {
            height: auto;
        }

        .success-side-panel {
            display: block;
        }

        .success-preview-list {
            max-height: none;
            overflow: visible;
            padding-right: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .success-orb {
            animation: none;
        }
    }
</style>
@endpush

@section('content')
<div class="kader-shell relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 kader-grid opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1400px] space-y-5">

        {{-- HERO SUCCESS --}}
        <section class="relative overflow-hidden rounded-[1.9rem] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-cyan-50/90 to-white/95 soft-card">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-24 h-72 w-72 rounded-full bg-sky-300/16 blur-3xl"></div>

            <div class="relative grid gap-6 p-5 sm:p-7 xl:grid-cols-[minmax(0,1.35fr)_minmax(330px,.72fr)] xl:items-center">
                <div class="flex min-w-0 flex-col gap-5 md:flex-row md:items-start">
                    <div class="success-orb flex h-20 w-20 shrink-0 items-center justify-center rounded-[1.65rem] bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-400/25">
                        <i class="fa-solid fa-check text-3xl"></i>
                    </div>

                    <div class="min-w-0">
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/75 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Presensi berhasil disimpan
                        </div>

                        <h1 class="mt-4 max-w-3xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.9rem] lg:text-[2.15rem]">
                            Data absensi {{ $kategoriLabel }} sudah masuk sistem.
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-600">
                            Sesi presensi berhasil tersimpan lengkap dan siap digunakan untuk riwayat, detail, serta rekap laporan.
                        </p>

                        <div class="mt-5 grid gap-2 sm:grid-cols-3">
                            @if($routeHas('kader.absensi.show') && $absensi)
                                <a href="{{ route('kader.absensi.show', $absensi->id) }}"
                                   class="action-pill inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20">
                                    <i class="fa-solid fa-eye"></i>
                                    Detail
                                </a>
                            @endif

                            @if($routeHas('kader.absensi.index'))
                                <a href="{{ route('kader.absensi.index', [
                                    'kategori' => $kategori,
                                    'tanggal' => $tanggalYmd,
                                ]) }}"
                                   class="action-pill inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-3 text-sm font-black text-emerald-800 shadow-sm hover:bg-white">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Update
                                </a>
                            @endif

                            @if($routeHas('kader.absensi.riwayat'))
                                <a href="{{ route('kader.absensi.riwayat', [
                                    'kategori' => $kategori,
                                    'bulan' => $absensi?->bulan ?? now('Asia/Jakarta')->month,
                                    'tahun' => $absensi?->tahun ?? now('Asia/Jakarta')->year,
                                ]) }}"
                                   class="action-pill inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white/75 px-4 py-3 text-sm font-black text-slate-700 shadow-sm hover:bg-white">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    Riwayat
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.6rem] border border-white/80 bg-white/75 p-5 shadow-md shadow-emerald-100/60">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Kode Sesi</p>
                        <span class="rounded-full border px-2.5 py-1 text-[10px] font-black {{ $badgeClass($statusTone) }}">
                            {{ $statusText }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $toneClass($kategoriTone, 'solid') }}">
                            <i class="fa-solid {{ $kategoriIcon }} text-lg"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="line-clamp-1 text-sm font-black text-slate-950">{{ $kodeAbsensi }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                {{ $formatTanggal($absensi?->tanggal_posyandu ?? null, 'd F Y') }}
                                • Pertemuan ke-{{ $absensi?->nomor_pertemuan ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border px-4 py-3 {{ $badgeClass($kategoriTone) }}">
                            <p class="text-[10px] font-black uppercase tracking-[.14em]">Kategori</p>
                            <p class="mt-1 text-sm font-black">{{ $kategoriLabel }}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700">
                            <p class="text-[10px] font-black uppercase tracking-[.14em]">Petugas</p>
                            <p class="mt-1 text-sm font-black">{{ $absensi?->kader?->name ?? 'Kader' }}</p>
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
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalPeserta) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Data tersimpan</p>
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
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalTidakHadir) }}</p>
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
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-violet-700">Status</p>
                        <p class="mt-2 text-xl font-black tracking-tight text-slate-950">{{ $statusText }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Hasil sesi</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-lg shadow-violet-400/20">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </section>

        {{-- MAIN --}}
        <section class="grid items-stretch gap-5 xl:grid-cols-[minmax(0,1.38fr)_minmax(340px,.62fr)]">
            <div class="success-main-card glass-card min-w-0 rounded-[1.75rem] border border-white/80 p-5 soft-card">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Ringkasan Detail</p>
                        <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950 sm:text-xl">
                            Preview data tersimpan
                        </h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Beberapa data pertama dari sesi ini ditampilkan sebagai pengecekan cepat.
                        </p>
                    </div>

                    @if($routeHas('kader.absensi.show') && $absensi)
                        <a href="{{ route('kader.absensi.show', $absensi->id) }}"
                           class="action-pill inline-flex w-fit items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-black text-emerald-700 shadow-sm hover:border-emerald-300 hover:bg-emerald-100">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-md shadow-emerald-300/50">
                                <i class="fa-solid fa-list-check text-xs"></i>
                            </span>
                            Detail lengkap
                        </a>
                    @endif
                </div>

                @if($details->isNotEmpty())
                    <div class="success-preview-list space-y-3 pb-1">
                        @foreach($details->take($previewLimit) as $index => $detail)
                            @php
                                $hadir = (bool) $detail->hadir;
                                $statusLabel = $hadir ? 'Hadir' : 'Tidak Hadir';
                                $statusToneRow = $hadir ? 'emerald' : 'orange';
                                $statusIcon = $hadir ? 'fa-circle-check' : 'fa-circle-xmark';

                                $nama = $detail->nama_pasien ?? $detail->pasien?->nama_lengkap ?? $detail->pasien?->nama ?? 'Data sasaran';
                                $nik = $detail->nik_pasien ?? $detail->pasien?->nik ?? '-';
                                $usia = $detail->usia_pasien ?? '-';
                                $keterangan = $detail->keterangan_text ?? ($detail->keterangan ?: '-');
                            @endphp

                            <div class="success-row rounded-[1.35rem] border border-slate-200 bg-white/80 p-4">
                                <div class="grid gap-4 xl:grid-cols-[52px_minmax(0,1fr)_190px] xl:items-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $toneClass($kategoriTone, 'ring') }} border text-sm font-black">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                                            <h3 class="line-clamp-1 text-[15px] font-black text-slate-950">{{ $nama }}</h3>

                                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-black {{ $badgeClass($statusToneRow) }}">
                                                <i class="fa-solid {{ $statusIcon }} mr-1"></i>
                                                {{ $statusLabel }}
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
                                                <i class="fa-solid fa-note-sticky text-slate-400"></i>
                                                <span class="line-clamp-1">{{ $keterangan }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="rounded-[1.2rem] border p-4 text-center {{ $badgeClass($statusToneRow) }}">
                                        <p class="text-[10px] font-black uppercase tracking-[.14em]">Status</p>
                                        <p class="mt-1 text-sm font-black">{{ $statusLabel }}</p>
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
                        <h3 class="mt-4 text-base font-black text-slate-950">Detail belum tersedia</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm font-medium leading-6 text-slate-500">
                            Presensi tersimpan, tetapi detail peserta belum ditemukan. Ini harus dicek, jangan dipelihara.
                        </p>
                    </div>
                @endif
                <div class="success-main-footer">
    <div class="rounded-[1.35rem] border border-dashed border-emerald-200 bg-emerald-50/70 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-black text-emerald-800">
                    Preview menampilkan {{ number_format(min($previewLimit, $details->count())) }} dari {{ number_format($details->count()) }} data sasaran.
                </p>

                <p class="mt-1 text-xs font-semibold leading-5 text-emerald-700">
                    Detail lengkap tetap tersedia untuk pengecekan seluruh data presensi.
                </p>
            </div>

            @if($remainingDetails > 0 && $routeHas('kader.absensi.show') && $absensi)
                <a href="{{ route('kader.absensi.show', $absensi->id) }}"
                   class="inline-flex shrink-0 items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-300/50 transition hover:-translate-y-0.5 hover:bg-emerald-500">
                    <i class="fa-solid fa-list-check"></i>
                    Buka semua
                </a>
            @endif
        </div>
    </div>
</div>
            </div>

            {{-- SIDE PANEL --}}
            <aside class="success-side-panel min-w-0 space-y-5 xl:sticky xl:top-5">
                <div class="glass-card rounded-[1.75rem] border border-white/80 p-5 soft-card">
                    <div class="rounded-[1.45rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-sky-50 p-5">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Langkah Berikutnya</p>
                        <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950">Data siap dipakai</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-600">
                            Gunakan tombol berikut untuk membuka detail, riwayat, atau memperbarui presensi.
                        </p>

                        <div class="mt-5 space-y-3">
                            @if($routeHas('kader.absensi.show') && $absensi)
                                <a href="{{ route('kader.absensi.show', $absensi->id) }}"
                                   class="action-pill flex items-center justify-between gap-3 rounded-[1.2rem] border border-white/80 bg-white/80 p-4 shadow-sm hover:border-emerald-200 hover:bg-white">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                            <i class="fa-solid fa-eye"></i>
                                        </span>
                                        <span>
                                            <span class="block text-sm font-black text-slate-800">Lihat detail sesi</span>
                                            <span class="block text-xs font-semibold text-slate-500">Buka seluruh daftar sasaran</span>
                                        </span>
                                    </span>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                                </a>
                            @endif

                            @if($routeHas('kader.absensi.riwayat'))
                                <a href="{{ route('kader.absensi.riwayat', [
                                    'kategori' => $kategori,
                                    'bulan' => $absensi?->bulan ?? now('Asia/Jakarta')->month,
                                    'tahun' => $absensi?->tahun ?? now('Asia/Jakarta')->year,
                                ]) }}"
                                   class="action-pill flex items-center justify-between gap-3 rounded-[1.2rem] border border-white/80 bg-white/80 p-4 shadow-sm hover:border-emerald-200 hover:bg-white">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </span>
                                        <span>
                                            <span class="block text-sm font-black text-slate-800">Buka riwayat absensi</span>
                                            <span class="block text-xs font-semibold text-slate-500">Lihat sesi bulan ini</span>
                                        </span>
                                    </span>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                                </a>
                            @endif

                            @if($routeHas('kader.absensi.index'))
                                <a href="{{ route('kader.absensi.index', [
                                    'kategori' => $kategori,
                                    'tanggal' => $tanggalYmd,
                                ]) }}"
                                   class="action-pill flex items-center justify-between gap-3 rounded-[1.2rem] border border-white/80 bg-white/80 p-4 shadow-sm hover:border-emerald-200 hover:bg-white">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </span>
                                        <span>
                                            <span class="block text-sm font-black text-slate-800">Update presensi ini</span>
                                            <span class="block text-xs font-semibold text-slate-500">Perbaiki data bila perlu</span>
                                        </span>
                                    </span>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                                </a>
                            @endif

                            @if($routeHas('kader.dashboard'))
                                <a href="{{ route('kader.dashboard') }}"
                                   class="action-pill flex items-center justify-between gap-3 rounded-[1.2rem] border border-white/80 bg-white/80 p-4 shadow-sm hover:border-emerald-200 hover:bg-white">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                            <i class="fa-solid fa-chart-line"></i>
                                        </span>
                                        <span>
                                            <span class="block text-sm font-black text-slate-800">Kembali ke dashboard</span>
                                            <span class="block text-xs font-semibold text-slate-500">Buka ringkasan Kader</span>
                                        </span>
                                    </span>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="side-fill glass-card rounded-[1.75rem] border border-white/80 p-5 soft-card">
    <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Validasi</p>
                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950">Ringkasan sistem</h2>

                    <div class="mt-4 space-y-3">
                        <div class="rounded-[1.2rem] border border-slate-200 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Kode</span>
                                <span class="text-right text-xs font-black text-slate-950">{{ $kodeAbsensi }}</span>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-slate-200 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Kategori</span>
                                <span class="text-sm font-black text-slate-950">{{ $kategoriLabel }}</span>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-slate-200 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Tanggal</span>
                                <span class="text-sm font-black text-slate-950">
                                    {{ $formatTanggal($absensi?->tanggal_posyandu ?? null, 'd M Y') }}
                                </span>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-slate-200 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Petugas</span>
                                <span class="text-sm font-black text-slate-950">
                                    {{ $absensi?->kader?->name ?? 'Kader' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[1.25rem] border border-emerald-200 bg-emerald-50/70 p-4">
                        <p class="text-xs font-black uppercase tracking-[.14em] text-emerald-700">Status data</p>
                        <p class="mt-2 text-sm font-bold leading-6 text-emerald-800">
                            Presensi tersimpan dengan {{ number_format($totalPeserta) }} detail sasaran.
                        </p>
                    </div>
                </div>
            </aside>
        </section>
    </div>
</div>
@endsection