@extends('layouts.kader')

@section('title', 'Registrasi Hadir')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $kategoriAktif = $kategori ?? request('kategori', 'balita');
    $tanggalInput = $tanggal ?? request('tanggal', now('Asia/Jakarta')->toDateString());
    $pasiens = $pasiens ?? collect();
    $absensiData = $absensiData ?? collect();
    $sesiHariIni = $sesiHariIni ?? null;
    $pertemuanBerikutnya = $pertemuanBerikutnya ?? ($sesiHariIni?->nomor_pertemuan ?? 1);

    $kategoriMenus = [
        'balita' => [
            'label' => 'Balita',
            'caption' => 'Anak dan tumbuh kembang',
            'icon' => 'fa-child-reaching',
            'tone' => 'emerald',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'caption' => 'Sasaran usia remaja',
            'icon' => 'fa-user-graduate',
            'tone' => 'violet',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'caption' => 'Sasaran usia lanjut',
            'icon' => 'fa-person-cane',
            'tone' => 'sky',
        ],
    ];

    if (!array_key_exists($kategoriAktif, $kategoriMenus)) {
        $kategoriAktif = 'balita';
    }

    $currentKategori = $kategoriMenus[$kategoriAktif];

    try {
        $tanggalCarbon = Carbon::parse($tanggalInput, 'Asia/Jakarta');
    } catch (\Throwable $e) {
        $tanggalCarbon = now('Asia/Jakarta');
    }

    $tanggalFormat = $tanggalCarbon->translatedFormat('l, d F Y');
    $tanggalPendek = $tanggalCarbon->translatedFormat('d M Y');

    $totalSasaran = $pasiens->count();
    $totalTercatat = 0;
    $totalHadir = 0;
    $totalTidakHadir = 0;

    foreach ($pasiens as $pasien) {
        $detail = $absensiData->get($pasien->id);

        if ($detail) {
            $totalTercatat++;

            if ((bool) $detail->hadir) {
                $totalHadir++;
            } else {
                $totalTidakHadir++;
            }
        }
    }

    $belumTercatat = max(0, $totalSasaran - $totalTercatat);
    $persenHadir = $totalSasaran > 0 ? round(($totalHadir / $totalSasaran) * 100) : 0;
    $persenTercatat = $totalSasaran > 0 ? round(($totalTercatat / $totalSasaran) * 100) : 0;

    $routeHas = fn ($name) => Route::has($name);

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

    $getPasienAge = function ($pasien) {
        if (empty($pasien->tanggal_lahir)) {
            return null;
        }

        try {
            return Carbon::parse($pasien->tanggal_lahir)->age . ' tahun';
        } catch (\Throwable $e) {
            return null;
        }
    };

    $dataCreateRoute = match ($kategoriAktif) {
        'remaja' => 'kader.data.remaja.create',
        'lansia' => 'kader.data.lansia.create',
        default => 'kader.data.balita.create',
    };
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

    .pc-shell {
        background:
            radial-gradient(circle at 8% 5%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 95% 8%, rgba(14, 165, 233, .13), transparent 26%),
            radial-gradient(circle at 50% 96%, rgba(251, 191, 36, .10), transparent 30%),
            linear-gradient(135deg, #f3fff9 0%, #eef9ff 48%, #f8fafc 100%);
    }

    .pc-grid-bg {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .pc-glass {
        background: rgba(255, 255, 255, .86);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        box-shadow: 0 16px 40px rgba(15, 23, 42, .065);
    }

    .pc-attendance-list {
        height: 610px;
        max-height: 610px;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        padding-right: 8px;
    }

    .pc-attendance-list::-webkit-scrollbar {
        width: 7px;
    }

    .pc-attendance-list::-webkit-scrollbar-track {
        background: rgba(226, 232, 240, .5);
        border-radius: 999px;
    }

    .pc-attendance-list::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, .42);
        border-radius: 999px;
    }

    .pc-row {
        min-height: 126px;
        transition: border-color .16s ease, background .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .pc-row:hover {
        box-shadow: 0 12px 26px rgba(15, 23, 42, .065);
        transform: translateY(-1px);
    }

    .pc-row.is-pending {
        border-color: rgba(203, 213, 225, .95);
        background: linear-gradient(135deg, rgba(248, 250, 252, .98), rgba(255, 255, 255, .96));
    }

    .pc-row.is-present {
        border-color: rgba(16, 185, 129, .45);
        background: linear-gradient(135deg, rgba(236, 253, 245, .98), rgba(255, 255, 255, .96));
    }

    .pc-row.is-absent {
        border-color: rgba(251, 146, 60, .48);
        background: linear-gradient(135deg, rgba(255, 247, 237, .98), rgba(255, 255, 255, .96));
    }

    .pc-choice-btn {
        transition: background .16s ease, color .16s ease, border-color .16s ease, transform .16s ease;
    }

    .pc-choice-btn:hover {
        transform: translateY(-1px);
    }

    .pc-choice-btn.is-present {
        color: white;
        border-color: transparent;
        background: linear-gradient(135deg, #10b981, #14b8a6);
    }

    .pc-choice-btn.is-absent {
        color: white;
        border-color: transparent;
        background: linear-gradient(135deg, #f59e0b, #f97316);
    }

    .pc-status-badge.is-pending {
        color: #64748b;
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .pc-status-badge.is-present {
        color: #047857;
        background: #ecfdf5;
        border-color: #a7f3d0;
    }

    .pc-status-badge.is-absent {
        color: #c2410c;
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .pc-fade {
        opacity: .7;
        pointer-events: none;
    }

    .pc-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 999999 !important;
        display: none;
        align-items: center;
        justify-content: center;
        width: 100vw;
        min-height: 100vh;
        min-height: 100dvh;
        padding: 1rem;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .pc-modal-backdrop.is-open {
        display: flex !important;
    }

    .pc-modal-card {
        width: min(100%, 460px);
        transform: translateY(12px) scale(.97);
        opacity: 0;
        border-radius: 1.75rem;
        border: 1px solid rgba(255, 255, 255, .78);
        background:
            radial-gradient(circle at 0% 0%, rgba(16, 185, 129, .14), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(14, 165, 233, .12), transparent 34%),
            rgba(255, 255, 255, .94);
        box-shadow: 0 30px 90px rgba(15, 23, 42, .25);
        transition: transform .18s ease, opacity .18s ease;
    }

    .pc-modal-backdrop.is-open .pc-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    .pc-modal-icon {
        display: flex;
        height: 3.25rem;
        width: 3.25rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 1.25rem;
        color: white;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .14);
    }

    .pc-modal-icon.is-success {
        background: linear-gradient(135deg, #10b981, #14b8a6);
    }

    .pc-modal-icon.is-warning {
        background: linear-gradient(135deg, #f59e0b, #f97316);
    }

    .pc-modal-icon.is-danger {
        background: linear-gradient(135deg, #ef4444, #f43f5e);
    }

    .pc-modal-icon.is-info {
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
    }

    .pc-modal-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        border-radius: 1rem;
        padding: .75rem 1rem;
        font-size: .875rem;
        font-weight: 900;
        transition: transform .16s ease, background .16s ease, box-shadow .16s ease;
    }

    .pc-modal-btn:hover {
        transform: translateY(-1px);
    }

    .pc-modal-btn-primary {
        background: linear-gradient(135deg, #10b981, #14b8a6);
        color: white;
        box-shadow: 0 14px 32px rgba(16, 185, 129, .24);
    }

    .pc-modal-btn-warning {
        background: linear-gradient(135deg, #f59e0b, #f97316);
        color: white;
        box-shadow: 0 14px 32px rgba(245, 158, 11, .24);
    }

    .pc-modal-btn-danger {
        background: linear-gradient(135deg, #ef4444, #f43f5e);
        color: white;
        box-shadow: 0 14px 32px rgba(239, 68, 68, .24);
    }

    .pc-modal-btn-ghost {
        border: 1px solid rgba(203, 213, 225, .95);
        background: rgba(255, 255, 255, .8);
        color: #475569;
    }
.pc-main-panel {
    min-height: 100%;
}

.pc-side-panel {
    min-height: 100%;
}

.pc-progress-card {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

.pc-attendance-list {
    flex: 1 1 auto;
    height: 560px;
    max-height: 560px;
    min-height: 560px;
}

@media (min-width: 1536px) {
    .pc-attendance-list {
        height: 590px;
        max-height: 590px;
        min-height: 590px;
    }
}
    @media (max-width: 1279px) {
        .pc-attendance-list {
    height: auto;
    max-height: none;
    min-height: 0;
    overflow: visible;
    padding-right: 0;
}

        .pc-row {
            min-height: unset;
        }
    }
</style>
@endpush

@section('content')
<div id="kaderAbsensiPage" class="pc-shell relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 pc-grid-bg opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1400px] space-y-5">

        <section class="relative overflow-hidden rounded-[1.75rem] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-cyan-50/90 to-white/95 pc-glass">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-24 h-72 w-72 rounded-full bg-sky-300/16 blur-3xl"></div>

            <div class="relative grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,.7fr)] xl:items-stretch">
                <div class="flex min-w-0 flex-col justify-between gap-5">
                    <div>
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/75 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Kader Attendance Center
                        </div>

                        <h1 class="mt-4 max-w-3xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2.05rem]">
                            Registrasi hadir {{ $currentKategori['label'] }} siap direkap.
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-600">
                            Pilih hadir atau tidak hadir untuk setiap sasaran. Jika salah klik, reset barisnya sebelum disimpan.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if($routeHas('kader.absensi.riwayat'))
                            <a href="{{ route('kader.absensi.riwayat', ['kategori' => $kategoriAktif]) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                Riwayat Absensi
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
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Sesi Absensi</p>
                            <span class="shrink-0 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-black text-emerald-700">
                                {{ $sesiHariIni ? 'Update Data' : 'Sesi Baru' }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-400/20">
                                <span class="text-lg font-black leading-none">{{ $tanggalCarbon->translatedFormat('d') }}</span>
                                <span class="mt-1 text-[10px] font-black uppercase">{{ $tanggalCarbon->translatedFormat('M') }}</span>
                            </div>

                            <div class="min-w-0">
                                <p class="line-clamp-1 text-sm font-black text-slate-950">{{ $tanggalFormat }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Pertemuan ke-{{ $pertemuanBerikutnya }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.45rem] border border-white/80 bg-white/75 p-4 shadow-md shadow-sky-100/60">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $toneClass($currentKategori['tone'], 'solid') }}">
                                <i class="fa-solid {{ $currentKategori['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-950">{{ $currentKategori['label'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $currentKategori['caption'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if(session('success') || session('error') || $errors->any())
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

                @if($errors->any())
                    <div class="rounded-[1.35rem] border border-amber-200 bg-amber-50/90 p-4 text-sm font-bold text-amber-800 shadow-sm">
                        <div class="flex gap-2">
                            <i class="fa-solid fa-circle-info mt-0.5"></i>
                            <div>
                                <p>Form belum valid. Bereskan dulu sebelum disimpan.</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5 text-xs font-semibold">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[1.55rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white p-4 pc-glass">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">Total Sasaran</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalSasaran) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $currentKategori['label'] }} terdaftar</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-400/20">
                        <i class="fa-solid fa-users-line"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.55rem] border border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white p-4 pc-glass">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-sky-700">Hadir</p>
                        <p id="hadirCountText" class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalHadir) }}</p>
                        <p id="hadirPercentText" class="mt-1 text-sm font-semibold text-slate-500">{{ $persenHadir }}% dari sasaran</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-400/20">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.55rem] border border-orange-200 bg-gradient-to-br from-orange-50 via-amber-50 to-white p-4 pc-glass">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-orange-700">Tidak Hadir</p>
                        <p id="tidakCountText" class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($totalTidakHadir) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Sudah ditandai tidak hadir</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 text-white shadow-lg shadow-orange-400/20">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.55rem] border border-violet-200 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-white p-4 pc-glass">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-violet-700">Belum Dipilih</p>
                        <p id="belumCountText" class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($belumTercatat) }}</p>
                        <p id="tercatatPercentText" class="mt-1 text-sm font-semibold text-slate-500">{{ $persenTercatat }}% sudah diproses</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-lg shadow-violet-400/20">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid items-stretch gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(330px,.45fr)]">
            <div class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                <div class="mb-4">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Kategori Sasaran</p>
                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950">Pilih kelompok presensi</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Data langsung menyesuaikan sesuai kategori dan tanggal kegiatan.
                    </p>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    @foreach($kategoriMenus as $key => $item)
                        @php
                            $active = $kategoriAktif === $key;
                            $url = route('kader.absensi.index', ['kategori' => $key, 'tanggal' => $tanggalInput]);
                        @endphp

                        <button type="button"
                                data-ajax-url="{{ $url }}"
                                class="rounded-[1.35rem] border p-4 text-left transition hover:-translate-y-0.5 {{ $active ? 'bg-gradient-to-br ' . $toneClass($item['tone']) . ' shadow-md' : 'border-slate-200 bg-white/70 hover:border-emerald-200' }}">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $toneClass($item['tone'], 'solid') }}">
                                    <i class="fa-solid {{ $item['icon'] }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-slate-950">{{ $item['label'] }}</p>
                                    <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">{{ $item['caption'] }}</p>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                <div class="mb-4">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-sky-700">Filter Tanggal</p>
                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950">Tanggal kegiatan</h2>
                </div>

                <form method="GET" action="{{ route('kader.absensi.index') }}" data-ajax-form="true" class="space-y-3">
                    <input type="hidden" name="kategori" value="{{ $kategoriAktif }}">

                    <input type="date"
                           name="tanggal"
                           value="{{ $tanggalInput }}"
                           max="{{ now('Asia/Jakarta')->toDateString() }}"
                           class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">

                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
                        <i class="fa-solid fa-filter"></i>
                        Terapkan Filter
                    </button>
                </form>
            </div>
        </section>

        <form id="attendanceForm" method="POST" action="{{ route('kader.absensi.store') }}" class="grid items-stretch gap-5 xl:grid-cols-[minmax(0,1.48fr)_minmax(330px,.58fr)]">
            @csrf

            <input type="hidden" name="kategori" value="{{ $kategoriAktif }}">
            <input type="hidden" name="tanggal" value="{{ $tanggalInput }}">

            <section class="pc-main-panel pc-glass flex h-full min-w-0 flex-col rounded-[1.75rem] border border-white/80 p-5">
                <div class="mb-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_390px] lg:items-start">
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Daftar Sasaran</p>
                        <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950 sm:text-xl">
                            Presensi {{ $currentKategori['label'] }}
                        </h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            <span id="visibleCountText">
                                Menampilkan {{ number_format($totalSasaran) }} data {{ $currentKategori['label'] }}.
                            </span>
                            Pencarian membaca nama atau NIK dari awal kata.
                        </p>
                    </div>

                    <div class="grid w-full gap-2 sm:grid-cols-2">
                        <button type="button"
                                id="markAllPresent"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-3 text-xs font-black text-white shadow-md shadow-emerald-300/60 transition hover:-translate-y-0.5 hover:bg-emerald-500">
                            <i class="fa-solid fa-check-double"></i>
                            Hadir Semua
                        </button>

                        <button type="button"
                                id="markAllAbsent"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-orange-500 px-3 text-xs font-black text-white shadow-md shadow-orange-300/60 transition hover:-translate-y-0.5 hover:bg-orange-400">
                            <i class="fa-solid fa-user-xmark"></i>
                            Tidak Hadir
                        </button>

                        <button type="button"
                                id="resetChanges"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white/85 px-3 text-xs font-black text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 sm:col-span-2">
                            <i class="fa-solid fa-rotate-left"></i>
                            Reset Semua Pilihan
                        </button>
                    </div>
                </div>

                <div class="mb-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                        <input type="search"
                               id="attendanceSearch"
                               autocomplete="off"
                               spellcheck="false"
                               inputmode="search"
                               placeholder="Cari nama atau NIK sasaran..."
                               class="w-full rounded-2xl border border-slate-200 bg-white/85 py-3 pl-11 pr-11 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">

                        <button type="button"
                                id="instantClearSearch"
                                class="absolute right-4 top-1/2 hidden -translate-y-1/2 text-slate-400 transition hover:text-emerald-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <button type="button"
                            id="clearSearch"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-xmark"></i>
                        Bersihkan
                    </button>
                </div>

                @if($pasiens->isNotEmpty())
                    <div id="attendanceList" class="pc-attendance-list flex-1 space-y-3">
                        @foreach($pasiens as $index => $pasien)
                            @php
                                $detail = $absensiData->get($pasien->id);
                                $saved = (bool) $detail;
                                $statusValue = $saved ? (int) $detail->hadir : null;
                                $oldStatus = old("kehadiran.$pasien->id", $statusValue);

                                $nama = $pasien->nama_lengkap ?? $pasien->nama ?? 'Tanpa Nama';
                                $nik = $pasien->nik ?? '-';
                                $umur = $getPasienAge($pasien);
                                $keterangan = old("keterangan.$pasien->id", $detail->keterangan ?? '');

                                $stateValue = $oldStatus === null ? '' : (string) $oldStatus;

                                $rowState = 'is-pending';
                                $badgeText = 'Belum dipilih';

                                if ($stateValue === '1') {
                                    $rowState = 'is-present';
                                    $badgeText = 'Hadir';
                                } elseif ($stateValue === '0') {
                                    $rowState = 'is-absent';
                                    $badgeText = 'Tidak hadir';
                                }

                                $extraInfo = match ($kategoriAktif) {
                                    'balita' => $pasien->nama_ibu ?? $pasien->alamat ?? null,
                                    'remaja' => $pasien->sekolah ?? $pasien->kelas ?? $pasien->alamat ?? null,
                                    'lansia' => $pasien->tingkat_kemandirian ?? $pasien->tekanan_darah ?? $pasien->alamat ?? null,
                                    default => $pasien->alamat ?? null,
                                };
                            @endphp

                            <div class="pc-row {{ $rowState }} rounded-[1.35rem] border bg-white/78 p-4"
                                 data-row="attendance"
                                 data-search-name="{{ mb_strtolower($nama ?? '', 'UTF-8') }}"
                                 data-search-nik="{{ preg_replace('/\D/', '', (string) $nik) }}"
                                 data-original-index="{{ $index }}">
                                <input type="hidden"
                                       name="kehadiran[{{ $pasien->id }}]"
                                       value="{{ $stateValue }}"
                                       data-state-input
                                       data-initial="{{ $stateValue }}">

                                <div class="grid gap-3 xl:grid-cols-[52px_minmax(0,1fr)_282px] xl:items-start">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $toneClass($currentKategori['tone'], 'ring') }} border text-sm font-black">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                                            <h3 class="line-clamp-1 text-[15px] font-black text-slate-950">{{ $nama }}</h3>

                                            <span class="pc-status-badge {{ $rowState }} rounded-full border px-2.5 py-1 text-[10px] font-black" data-status-label>
                                                {{ $badgeText }}
                                            </span>

                                            @if($saved)
                                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-black text-emerald-700">
                                                    Tersimpan
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold text-slate-500">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                                <i class="fa-solid fa-id-card text-slate-400"></i>
                                                {{ $nik }}
                                            </span>

                                            @if($umur)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                                    <i class="fa-solid fa-cake-candles text-slate-400"></i>
                                                    {{ $umur }}
                                                </span>
                                            @endif

                                            @if($extraInfo)
                                                <span class="inline-flex min-w-0 items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400"></i>
                                                    <span class="line-clamp-1">{{ $extraInfo }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button"
                                                data-choice="1"
                                                class="pc-choice-btn h-11 rounded-2xl border border-emerald-200 bg-white px-3 text-xs font-black text-emerald-700 {{ $stateValue === '1' ? 'is-present' : '' }}">
                                            <i class="fa-solid fa-check mr-1"></i>
                                            Hadir
                                        </button>

                                        <button type="button"
                                                data-choice="0"
                                                class="pc-choice-btn h-11 rounded-2xl border border-orange-200 bg-white px-3 text-xs font-black text-orange-700 {{ $stateValue === '0' ? 'is-absent' : '' }}">
                                            <i class="fa-solid fa-xmark mr-1"></i>
                                            Tidak
                                        </button>

                                        <button type="button"
                                                data-reset-row
                                                class="pc-choice-btn h-11 rounded-2xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-500 hover:bg-slate-50"
                                                title="Reset pilihan baris ini">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </button>
                                    </div>

                                    <div class="xl:col-start-2 xl:col-span-2">
                                        <input type="text"
                                               name="keterangan[{{ $pasien->id }}]"
                                               value="{{ $keterangan }}"
                                               data-note-input
                                               data-initial-note="{{ $keterangan }}"
                                               placeholder="Catatan singkat, misal sakit / izin / alasan tidak hadir"
                                               class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-2.5 text-xs font-bold text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div id="attendanceSearchEmpty" class="hidden rounded-[1.35rem] border border-dashed border-emerald-200 bg-emerald-50/70 p-6 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>

                            <h3 class="mt-3 text-sm font-black text-slate-950">Data tidak ditemukan</h3>
                            <p id="attendanceSearchEmptyText" class="mt-1 text-xs font-semibold text-slate-500">
                                Tidak ada sasaran yang cocok dengan pencarian.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="rounded-[1.55rem] border border-dashed border-emerald-200 bg-emerald-50/65 p-8 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                            <i class="fa-solid fa-users-slash text-lg"></i>
                        </div>
                        <h3 class="mt-4 text-base font-black text-slate-950">Data sasaran belum tersedia</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm font-medium leading-6 text-slate-500">
                            Tambahkan data {{ $currentKategori['label'] }} terlebih dahulu supaya daftar absensi otomatis muncul.
                        </p>

                        @if($routeHas($dataCreateRoute))
                            <a href="{{ route($dataCreateRoute) }}"
                               class="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
                                <i class="fa-solid fa-user-plus"></i>
                                Tambah Data Sasaran
                            </a>
                        @endif
                    </div>
                @endif
            </section>

            <aside class="pc-side-panel flex h-full flex-col space-y-5 xl:sticky xl:top-5">
                <div class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="rounded-[1.45rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-sky-50 p-5">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Ringkasan Input</p>
                        <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950">Cek sebelum simpan</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-600">
                            Semua sasaran harus dipilih hadir atau tidak hadir sebelum disimpan.
                        </p>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-black text-slate-600">Kategori</span>
                                    <span class="text-sm font-black text-slate-950">{{ $currentKategori['label'] }}</span>
                                </div>
                            </div>

                            <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-black text-slate-600">Tanggal</span>
                                    <span class="text-sm font-black text-slate-950">{{ $tanggalPendek }}</span>
                                </div>
                            </div>

                            <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-black text-slate-600">Status sesi</span>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-black {{ $sesiHariIni ? 'bg-sky-100 text-sky-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $sesiHariIni ? 'Update' : 'Baru' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($pasiens->isNotEmpty())
                            <button type="submit"
                                    id="submitAttendanceBtn"
                                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Simpan Presensi
                            </button>

                            <p class="mt-3 text-center text-xs font-semibold leading-5 text-slate-500">
                                Jika masih ada yang belum dipilih, sistem akan menolak agar data tidak bolong.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="pc-progress-card pc-glass flex-1 rounded-[1.75rem] border border-white/80 p-5">
    <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Progress</p>

                    <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                        <div id="presenceProgressBar"
                             class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-300"
                             style="width: {{ $persenHadir }}%"></div>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <div class="rounded-2xl bg-emerald-50 p-3">
                            <p class="text-xs font-black text-emerald-700">Hadir</p>
                            <p id="sideHadirText" class="mt-1 text-xl font-black text-slate-950">{{ number_format($totalHadir) }}</p>
                        </div>

                        <div class="rounded-2xl bg-orange-50 p-3">
                            <p class="text-xs font-black text-orange-700">Tidak</p>
                            <p id="sideTidakText" class="mt-1 text-xl font-black text-slate-950">{{ number_format($totalTidakHadir) }}</p>
                        </div>

                        <div class="rounded-2xl bg-violet-50 p-3">
                            <p class="text-xs font-black text-violet-700">Belum</p>
                            <p id="sideBelumText" class="mt-1 text-xl font-black text-slate-950">{{ number_format($belumTercatat) }}</p>
                        </div>
                    </div>

                    <p id="sideProgressText" class="mt-3 text-xs font-bold text-slate-500">
                        {{ $persenHadir }}% sasaran ditandai hadir.
                    </p>
                </div>
            </aside>
        </form>
    </div>
</div>
@endsection

@push('modals')
<div id="pcConfirmModal" class="pc-modal-backdrop" aria-hidden="true">
    <div class="pc-modal-card p-5 sm:p-6" role="dialog" aria-modal="true">
        <div class="flex gap-4">
            <div id="pcModalIcon" class="pc-modal-icon is-info">
                <i id="pcModalIconClass" class="fa-solid fa-circle-info text-xl"></i>
            </div>

            <div class="min-w-0 flex-1">
                <p id="pcModalEyebrow" class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                    Konfirmasi
                </p>

                <h3 id="pcModalTitle" class="mt-1 text-lg font-black leading-snug text-slate-950">
                    Konfirmasi aksi
                </h3>

                <p id="pcModalMessage" class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                    Pastikan data sudah benar sebelum melanjutkan.
                </p>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" id="pcModalCancel" class="pc-modal-btn pc-modal-btn-ghost">
                Batal
            </button>

            <button type="button" id="pcModalConfirm" class="pc-modal-btn pc-modal-btn-primary">
                Lanjutkan
            </button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    window.history.scrollRestoration = 'manual';

    const numberFormat = new Intl.NumberFormat('id-ID');
    let searchTimer = null;
    let ajaxBusy = false;

    function qs(selector, root = document) {
        return root.querySelector(selector);
    }

    function qsa(selector, root = document) {
        return Array.from(root.querySelectorAll(selector));
    }

    function getPage() {
        return qs('#kaderAbsensiPage');
    }

    function getRows() {
        return qsa('[data-row="attendance"]');
    }

    function getStateInput(row) {
        return qs('[data-state-input]', row);
    }

    function getState(row) {
        const input = getStateInput(row);
        return input ? input.value : '';
    }

    function normalizeText(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function normalizeNumber(value) {
        return String(value || '').replace(/\D/g, '');
    }

    function getNameWords(value) {
        return normalizeText(value).split(' ').filter(Boolean);
    }

    function unlockLayout() {
        document.body.classList.remove('overflow-hidden', 'is-loading', 'page-loading');

        const loader = qs('#loading-screen') || qs('#page-loader') || qs('[data-loader]');

        if (loader) {
            loader.style.opacity = '0';
            loader.style.pointerEvents = 'none';
            loader.style.display = 'none';
        }
    }

    function forceTop() {
        requestAnimationFrame(function () {
            const page = getPage();

            if (page) {
                page.scrollIntoView({ block: 'start', behavior: 'auto' });
            }

            window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
        });
    }

    function createFallbackModal() {
        if (qs('#pcConfirmModal')) {
            return;
        }

        document.body.insertAdjacentHTML('beforeend', `
            <div id="pcConfirmModal" class="pc-modal-backdrop" aria-hidden="true">
                <div class="pc-modal-card p-5 sm:p-6" role="dialog" aria-modal="true">
                    <div class="flex gap-4">
                        <div id="pcModalIcon" class="pc-modal-icon is-info">
                            <i id="pcModalIconClass" class="fa-solid fa-circle-info text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p id="pcModalEyebrow" class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Konfirmasi</p>
                            <h3 id="pcModalTitle" class="mt-1 text-lg font-black leading-snug text-slate-950">Konfirmasi aksi</h3>
                            <p id="pcModalMessage" class="mt-2 text-sm font-semibold leading-6 text-slate-600">Pastikan data sudah benar sebelum melanjutkan.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" id="pcModalCancel" class="pc-modal-btn pc-modal-btn-ghost">Batal</button>
                        <button type="button" id="pcModalConfirm" class="pc-modal-btn pc-modal-btn-primary">Lanjutkan</button>
                    </div>
                </div>
            </div>
        `);
    }

    function pcModal() {
        createFallbackModal();

        const backdrop = qs('#pcConfirmModal');
        const iconBox = qs('#pcModalIcon');
        const iconClass = qs('#pcModalIconClass');
        const eyebrow = qs('#pcModalEyebrow');
        const title = qs('#pcModalTitle');
        const message = qs('#pcModalMessage');
        const cancelBtn = qs('#pcModalCancel');
        const confirmBtn = qs('#pcModalConfirm');

        if (!backdrop || !title || !message || !cancelBtn || !confirmBtn || !iconBox || !iconClass || !eyebrow) {
            return null;
        }

        function lockBody() {
            document.documentElement.classList.add('pc-modal-open');
            document.body.classList.add('pc-modal-open');
        }

        function unlockBody() {
            document.documentElement.classList.remove('pc-modal-open');
            document.body.classList.remove('pc-modal-open');
        }

        function close(resolve, value) {
            backdrop.classList.remove('is-open');
            backdrop.setAttribute('aria-hidden', 'true');

            setTimeout(function () {
                unlockBody();
                resolve(value);
            }, 120);
        }

        function open(options = {}) {
            return new Promise(function (resolve) {
                const type = options.type || 'info';
                const showCancel = options.showCancel !== false;

                const iconMap = {
                    success: 'fa-circle-check',
                    warning: 'fa-triangle-exclamation',
                    danger: 'fa-trash',
                    info: 'fa-circle-info'
                };

                iconBox.className = `pc-modal-icon is-${type}`;
                iconClass.className = `fa-solid ${iconMap[type] || iconMap.info} text-xl`;

                eyebrow.textContent = options.eyebrow || 'Konfirmasi';
                title.textContent = options.title || 'Konfirmasi aksi';
                message.textContent = options.message || 'Pastikan data sudah benar sebelum melanjutkan.';

                cancelBtn.textContent = options.cancelText || 'Batal';
                cancelBtn.style.display = showCancel ? 'inline-flex' : 'none';

                confirmBtn.innerHTML = options.confirmIcon
                    ? `<i class="fa-solid ${options.confirmIcon}"></i> ${options.confirmText || 'Lanjutkan'}`
                    : (options.confirmText || 'Lanjutkan');

                confirmBtn.className = 'pc-modal-btn';

                if (type === 'danger') {
                    confirmBtn.classList.add('pc-modal-btn-danger');
                } else if (type === 'warning') {
                    confirmBtn.classList.add('pc-modal-btn-warning');
                } else {
                    confirmBtn.classList.add('pc-modal-btn-primary');
                }

                lockBody();

                backdrop.classList.add('is-open');
                backdrop.setAttribute('aria-hidden', 'false');

                setTimeout(function () {
                    confirmBtn.focus();
                }, 80);

                const onConfirm = function () {
                    cleanup();
                    close(resolve, true);
                };

                const onCancel = function () {
                    cleanup();
                    close(resolve, false);
                };

                const onBackdrop = function (event) {
                    if (event.target === backdrop && showCancel) {
                        cleanup();
                        close(resolve, false);
                    }
                };

                const onKeydown = function (event) {
                    if (event.key === 'Escape' && showCancel) {
                        cleanup();
                        close(resolve, false);
                    }
                };

                function cleanup() {
                    confirmBtn.removeEventListener('click', onConfirm);
                    cancelBtn.removeEventListener('click', onCancel);
                    backdrop.removeEventListener('click', onBackdrop);
                    document.removeEventListener('keydown', onKeydown);
                }

                confirmBtn.addEventListener('click', onConfirm);
                cancelBtn.addEventListener('click', onCancel);
                backdrop.addEventListener('click', onBackdrop);
                document.addEventListener('keydown', onKeydown);
            });
        }

        return { open };
    }

    const modal = pcModal();

    async function showNotice(type, title, message) {
        if (!modal) {
            alert(message);
            return;
        }

        await modal.open({
            type: type,
            eyebrow: type === 'warning' ? 'Perhatian' : 'Informasi',
            title: title,
            message: message,
            confirmText: 'Saya mengerti',
            confirmIcon: 'fa-check',
            showCancel: false
        });
    }

    async function showConfirm(type, title, message, confirmText = 'Lanjutkan') {
        if (!modal) {
            return confirm(message);
        }

        return await modal.open({
            type: type,
            eyebrow: 'Konfirmasi',
            title: title,
            message: message,
            confirmText: confirmText,
            confirmIcon: 'fa-floppy-disk',
            cancelText: 'Batal',
            showCancel: true
        });
    }

    function paintRow(row) {
        const value = getState(row);
        const badge = qs('[data-status-label]', row);

        row.classList.remove('is-present', 'is-absent', 'is-pending');

        qsa('[data-choice]', row).forEach(function (button) {
            button.classList.remove('is-present', 'is-absent');
        });

        if (badge) {
            badge.classList.remove('is-present', 'is-absent', 'is-pending');
        }

        if (value === '1') {
            row.classList.add('is-present');

            const button = qs('[data-choice="1"]', row);

            if (button) {
                button.classList.add('is-present');
            }

            if (badge) {
                badge.textContent = 'Hadir';
                badge.classList.add('is-present');
            }

            return;
        }

        if (value === '0') {
            row.classList.add('is-absent');

            const button = qs('[data-choice="0"]', row);

            if (button) {
                button.classList.add('is-absent');
            }

            if (badge) {
                badge.textContent = 'Tidak hadir';
                badge.classList.add('is-absent');
            }

            return;
        }

        row.classList.add('is-pending');

        if (badge) {
            badge.textContent = 'Belum dipilih';
            badge.classList.add('is-pending');
        }
    }

    function updateCounter() {
        const rows = getRows();
        const total = rows.length;

        let hadir = 0;
        let tidak = 0;
        let belum = 0;
        let tercatat = 0;

        rows.forEach(function (row) {
            const value = getState(row);

            if (value === '1') {
                hadir++;
                tercatat++;
            } else if (value === '0') {
                tidak++;
                tercatat++;
            } else {
                belum++;
            }

            paintRow(row);
        });

        const persenHadir = total > 0 ? Math.round((hadir / total) * 100) : 0;
        const persenTercatat = total > 0 ? Math.round((tercatat / total) * 100) : 0;

        const textMap = {
            hadirCountText: numberFormat.format(hadir),
            tidakCountText: numberFormat.format(tidak),
            belumCountText: numberFormat.format(belum),
            sideHadirText: numberFormat.format(hadir),
            sideTidakText: numberFormat.format(tidak),
            sideBelumText: numberFormat.format(belum),
            hadirPercentText: `${persenHadir}% dari sasaran`,
            tercatatPercentText: `${persenTercatat}% sudah diproses`,
            sideProgressText: `${persenHadir}% sasaran ditandai hadir.`
        };

        Object.keys(textMap).forEach(function (id) {
            const element = qs('#' + id);

            if (element) {
                element.textContent = textMap[id];
            }
        });

        const progressBar = qs('#presenceProgressBar');

        if (progressBar) {
            progressBar.style.width = `${persenHadir}%`;
        }

        return { total, hadir, tidak, belum, tercatat };
    }

    function setState(row, value) {
        const input = getStateInput(row);

        if (!input) {
            return;
        }

        input.value = value;
        paintRow(row);
        updateCounter();
    }

    function resetRow(row) {
        const input = getStateInput(row);
        const note = qs('[data-note-input]', row);

        if (input) {
            input.value = input.dataset.initial || '';
        }

        if (note) {
            note.value = note.dataset.initialNote || '';
        }

        paintRow(row);
        updateCounter();
    }

    function setAll(value) {
        getRows().forEach(function (row) {
            const input = getStateInput(row);

            if (input) {
                input.value = value;
            }

            paintRow(row);
        });

        updateCounter();
    }

    function resetAllChanges() {
        getRows().forEach(function (row) {
            resetRow(row);
        });

        updateCounter();
    }

    function filterRows(keyword) {
        const rawQuery = String(keyword || '').trim();
        const queryText = normalizeText(rawQuery);
        const queryNumber = normalizeNumber(rawQuery);

        const rows = getRows();
        const list = qs('#attendanceList');
        const emptyBox = qs('#attendanceSearchEmpty');
        const emptyText = qs('#attendanceSearchEmptyText');
        const visibleCountText = qs('#visibleCountText');
        const instantClear = qs('#instantClearSearch');

        if (!list) {
            return;
        }

        if (instantClear) {
            instantClear.classList.toggle('hidden', rawQuery === '');
        }

        if (rawQuery === '') {
            rows
                .sort(function (a, b) {
                    return Number(a.dataset.originalIndex || 0) - Number(b.dataset.originalIndex || 0);
                })
                .forEach(function (row) {
                    row.style.display = '';
                    list.appendChild(row);
                });

            if (emptyBox) {
                emptyBox.classList.add('hidden');
                list.appendChild(emptyBox);
            }

            if (visibleCountText) {
                visibleCountText.textContent = `Menampilkan ${numberFormat.format(rows.length)} data sasaran.`;
            }

            return;
        }

        const isNumeric = /^\d+$/.test(rawQuery);
        const matchedRows = [];
        const unmatchedRows = [];

        rows.forEach(function (row) {
            const name = row.dataset.searchName || '';
            const nik = row.dataset.searchNik || '';
            const originalIndex = Number(row.dataset.originalIndex || 0);

            let matched = false;
            let rank = 999;

            if (isNumeric) {
                if (nik === queryNumber) {
                    matched = true;
                    rank = 0;
                } else if (nik.startsWith(queryNumber)) {
                    matched = true;
                    rank = 1;
                } else if (queryNumber.length >= 4 && nik.includes(queryNumber)) {
                    matched = true;
                    rank = 2;
                }
            } else {
                const normalizedName = normalizeText(name);
                const words = getNameWords(name);

                if (normalizedName === queryText) {
                    matched = true;
                    rank = 0;
                } else if (normalizedName.startsWith(queryText)) {
                    matched = true;
                    rank = 1;
                } else if (words.some(function (word) {
                    return word.startsWith(queryText);
                })) {
                    matched = true;
                    rank = 2;
                }
            }

            if (matched) {
                matchedRows.push({
                    row: row,
                    rank: rank,
                    originalIndex: originalIndex
                });
            } else {
                unmatchedRows.push(row);
            }
        });

        matchedRows.sort(function (a, b) {
            if (a.rank !== b.rank) {
                return a.rank - b.rank;
            }

            return a.originalIndex - b.originalIndex;
        });

        matchedRows.forEach(function (item) {
            item.row.style.display = '';
            list.appendChild(item.row);
        });

        unmatchedRows.forEach(function (row) {
            row.style.display = 'none';
            list.appendChild(row);
        });

        if (emptyBox) {
            list.appendChild(emptyBox);

            if (matchedRows.length === 0) {
                emptyBox.classList.remove('hidden');

                if (emptyText) {
                    emptyText.textContent = `Tidak ada sasaran yang cocok dengan "${rawQuery}".`;
                }
            } else {
                emptyBox.classList.add('hidden');
            }
        }

        if (visibleCountText) {
            visibleCountText.textContent = `Menampilkan ${numberFormat.format(matchedRows.length)} dari ${numberFormat.format(rows.length)} data sasaran.`;
        }
    }

    window.pcFilterAbsensi = filterRows;

    async function loadAbsensiPage(url, push = true) {
        if (!url || ajaxBusy) {
            return;
        }

        const page = getPage();

        if (!page) {
            window.location.href = url;
            return;
        }

        ajaxBusy = true;
        page.classList.add('pc-fade');

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('Gagal memuat halaman absensi.');
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const incomingPage = doc.querySelector('#kaderAbsensiPage');

            if (!incomingPage) {
                throw new Error('Konten absensi tidak ditemukan.');
            }

            page.innerHTML = incomingPage.innerHTML;

            if (push) {
                window.history.pushState({ absensiAjax: true }, '', url);
            }

            unlockLayout();
            updateCounter();
            filterRows('');

            const list = qs('#attendanceList');

            if (list) {
                list.scrollTop = 0;
            }

            forceTop();
        } catch (error) {
            console.warn(error);
            window.location.href = url;
        } finally {
            const freshPage = getPage();

            if (freshPage) {
                freshPage.classList.remove('pc-fade');
            }

            ajaxBusy = false;
        }
    }

    document.addEventListener('click', function (event) {
        const choiceButton = event.target.closest('[data-choice]');

        if (choiceButton) {
            event.preventDefault();
            event.stopPropagation();

            const row = choiceButton.closest('[data-row="attendance"]');

            if (row) {
                setState(row, choiceButton.dataset.choice);
            }

            return;
        }

        const resetButton = event.target.closest('[data-reset-row]');

        if (resetButton) {
            event.preventDefault();
            event.stopPropagation();

            const row = resetButton.closest('[data-row="attendance"]');

            if (row) {
                resetRow(row);
            }

            return;
        }

        const ajaxButton = event.target.closest('[data-ajax-url]');

        if (ajaxButton) {
            event.preventDefault();
            event.stopPropagation();

            loadAbsensiPage(ajaxButton.getAttribute('data-ajax-url'), true);
            return;
        }

        if (event.target.closest('#markAllPresent')) {
            event.preventDefault();
            event.stopPropagation();
            setAll('1');
            return;
        }

        if (event.target.closest('#markAllAbsent')) {
            event.preventDefault();
            event.stopPropagation();
            setAll('0');
            return;
        }

        if (event.target.closest('#resetChanges')) {
            event.preventDefault();
            event.stopPropagation();
            resetAllChanges();
            return;
        }

        if (event.target.closest('#clearSearch') || event.target.closest('#instantClearSearch')) {
            event.preventDefault();
            event.stopPropagation();

            const input = qs('#attendanceSearch');

            if (input) {
                input.value = '';
                filterRows('');
                input.focus();
            }
        }
    }, true);

    document.addEventListener('input', function (event) {
        if (!event.target.matches('#attendanceSearch')) {
            return;
        }

        clearTimeout(searchTimer);

        const value = event.target.value;

        searchTimer = setTimeout(function () {
            filterRows(value);
        }, 10);
    }, true);

    document.addEventListener('search', function (event) {
        if (!event.target.matches('#attendanceSearch')) {
            return;
        }

        filterRows(event.target.value);
    }, true);

    document.addEventListener('submit', async function (event) {
        const filterForm = event.target.closest('form[data-ajax-form="true"]');

        if (filterForm) {
            event.preventDefault();
            event.stopPropagation();

            const url = filterForm.action + '?' + new URLSearchParams(new FormData(filterForm)).toString();
            loadAbsensiPage(url, true);
            return;
        }

        const form = event.target.closest('#attendanceForm');

        if (!form) {
            return;
        }

        event.preventDefault();

        const stats = updateCounter();

        if (stats.total <= 0) {
            await showNotice(
                'warning',
                'Data sasaran kosong',
                'Belum ada data sasaran yang bisa disimpan. Tambahkan data sasaran terlebih dahulu.'
            );

            return;
        }

        if (stats.belum > 0) {
            await showNotice(
                'warning',
                'Presensi belum lengkap',
                `Masih ada ${stats.belum} data sasaran yang belum dipilih. Pilih Hadir atau Tidak terlebih dahulu.`
            );

            return;
        }

        const approved = await showConfirm(
            'success',
            'Simpan presensi?',
            `Presensi akan disimpan dengan ${stats.hadir} hadir dan ${stats.tidak} tidak hadir.`,
            'Simpan Presensi'
        );

        if (!approved) {
            return;
        }

        const submitBtn = qs('#submitAttendanceBtn');

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        }

        HTMLFormElement.prototype.submit.call(form);
    }, true);

    window.addEventListener('popstate', function () {
        loadAbsensiPage(window.location.href, false);
    });

    window.addEventListener('pageshow', function () {
        unlockLayout();
        updateCounter();
        filterRows(qs('#attendanceSearch')?.value || '');
        forceTop();
    });

    unlockLayout();
    updateCounter();
    filterRows(qs('#attendanceSearch')?.value || '');
    forceTop();

    console.info('PosyanduCare Absensi JS aktif.');
})();
</script>
@endpush