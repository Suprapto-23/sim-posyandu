@extends('layouts.kader')

@section('title', 'Pengukuran Fisik')
@section('page-name', 'Pengukuran Fisik')
@section('page-title', 'Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use App\Models\Pemeriksaan;

    Carbon::setLocale('id');

    $kategori = $kategori ?? request('kategori', '');
    $search = $search ?? request('search', '');
    $status = $status ?? request('status', '');

    $kategoriAktif = ['balita', 'remaja', 'lansia'];
    $totalData = method_exists($pemeriksaans, 'total') ? $pemeriksaans->total() : $pemeriksaans->count();

    $kategoriOptions = [
        '' => 'Semua Kategori',
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    $statusOptions = [
        '' => 'Semua Status',
        'pending' => 'Menunggu Review',
        'ditolak' => 'Perlu Revisi',
        'verified' => 'Tervalidasi',
    ];

    $reviewedStatuses = [
        'verified',
        'terverifikasi',
        'valid',
        'approved',
        'disetujui',
        'selesai',
    ];

    $needFixStatuses = [
        'ditolak',
        'revisi',
        'perlu_revisi',
        'needs_revision',
        'rejected',
        'dikembalikan',
    ];

    $normalizeStatus = function ($value) use ($reviewedStatuses, $needFixStatuses) {
        $value = strtolower((string) ($value ?? 'pending'));

        if (in_array($value, $reviewedStatuses, true)) {
            return 'verified';
        }

        if (in_array($value, $needFixStatuses, true)) {
            return 'ditolak';
        }

        return 'pending';
    };

    $kategoriMeta = function ($value) {
        $value = strtolower((string) $value);

        return match ($value) {
            'balita' => [
                'label' => 'Balita',
                'desc' => 'Anak dan tumbuh kembang',
                'icon' => 'fa-child-reaching',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'soft' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white',
                'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white',
            ],
            'remaja' => [
                'label' => 'Remaja',
                'desc' => 'Sasaran usia remaja',
                'icon' => 'fa-user-graduate',
                'badge' => 'border-violet-200 bg-violet-50 text-violet-800',
                'soft' => 'border-violet-200 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-white',
                'solid' => 'bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white',
            ],
            'lansia' => [
                'label' => 'Lansia',
                'desc' => 'Sasaran usia lanjut',
                'icon' => 'fa-person-cane',
                'badge' => 'border-sky-200 bg-sky-50 text-sky-800',
                'soft' => 'border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white',
                'solid' => 'bg-gradient-to-br from-sky-500 to-cyan-500 text-white',
            ],
            default => [
                'label' => 'Umum',
                'desc' => 'Data sasaran',
                'icon' => 'fa-user',
                'badge' => 'border-slate-200 bg-slate-50 text-slate-700',
                'soft' => 'border-slate-200 bg-gradient-to-br from-slate-50 to-white',
                'solid' => 'bg-gradient-to-br from-slate-700 to-slate-900 text-white',
            ],
        };
    };

    $statusMeta = function ($value) use ($normalizeStatus) {
        $value = $normalizeStatus($value);

        return match ($value) {
            'verified' => [
                'label' => 'Tervalidasi',
                'desc' => 'Sudah dicek Bidan',
                'dot' => 'bg-emerald-500',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'card' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white',
                'icon' => 'fa-circle-check',
                'editable' => false,
            ],
            'ditolak' => [
                'label' => 'Perlu Revisi',
                'desc' => 'Harus diperbaiki',
                'dot' => 'bg-rose-500',
                'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
                'card' => 'border-rose-200 bg-gradient-to-br from-rose-50 via-orange-50 to-white',
                'icon' => 'fa-rotate-left',
                'editable' => true,
            ],
            default => [
                'label' => 'Menunggu Review',
                'desc' => 'Belum direview Bidan',
                'dot' => 'bg-amber-500',
                'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
                'card' => 'border-amber-200 bg-gradient-to-br from-amber-50 via-yellow-50 to-white',
                'icon' => 'fa-clock',
                'editable' => true,
            ],
        };
    };

    $formatTanggal = function ($item) {
        $tanggal = $item->tanggal_periksa
            ?? optional($item->kunjungan)->tanggal_kunjungan
            ?? $item->created_at
            ?? null;

        return $tanggal ? Carbon::parse($tanggal)->translatedFormat('d M Y') : '-';
    };

    $metric = function ($value, $unit = '') {
        if ($value === null || $value === '') {
            return '-';
        }

        return trim($value . ' ' . $unit);
    };

    $pasienNama = function ($item) {
        $pasien = optional($item->kunjungan)->pasien;

        return $pasien->nama_lengkap
            ?? $pasien->nama
            ?? $item->nama_pasien
            ?? 'Tanpa Nama';
    };

    $pasienNik = function ($item) {
        $pasien = optional($item->kunjungan)->pasien;

        return $pasien->nik
            ?? $item->nik_pasien
            ?? '-';
    };

    $catatanBidan = function ($item) {
        return $item->catatan_validasi
            ?? $item->catatan_bidan
            ?? $item->catatan_review
            ?? null;
    };

    $summaryQuery = Pemeriksaan::query()
        ->whereIn('kategori_pasien', $kategoriAktif);

    if ($kategori && in_array($kategori, $kategoriAktif, true)) {
        $summaryQuery->where('kategori_pasien', $kategori);
    }

    $summaryRows = (clone $summaryQuery)->get(['status_verifikasi', 'kategori_pasien']);

    $pendingCount = $summaryRows->filter(fn ($item) => $normalizeStatus($item->status_verifikasi) === 'pending')->count();
    $revisiCount = $summaryRows->filter(fn ($item) => $normalizeStatus($item->status_verifikasi) === 'ditolak')->count();
    $verifiedCount = $summaryRows->filter(fn ($item) => $normalizeStatus($item->status_verifikasi) === 'verified')->count();

    $balitaCount = $summaryRows->where('kategori_pasien', 'balita')->count();
    $remajaCount = $summaryRows->where('kategori_pasien', 'remaja')->count();
    $lansiaCount = $summaryRows->where('kategori_pasien', 'lansia')->count();

    $activeFilterCount = collect([$kategori, $search, $status])->filter(fn ($v) => filled($v))->count();

    $filterCaption = $activeFilterCount > 0
        ? $activeFilterCount . ' filter aktif'
        : 'Semua data';
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

    .pc-hero {
        min-height: 280px;
    }

    .pc-hero-total {
        background:
            radial-gradient(circle at 100% 0%, rgba(16, 185, 129, .18), transparent 34%),
            radial-gradient(circle at 0% 100%, rgba(14, 165, 233, .14), transparent 38%),
            rgba(255, 255, 255, .82);
    }

    .pc-stat-card {
        min-height: 118px;
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

    .pc-modal-backdrop {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: 0 !important;
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
            radial-gradient(circle at 0% 0%, rgba(244, 63, 94, .12), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(251, 146, 60, .10), transparent 34%),
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

        .pc-hero {
            min-height: unset;
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

        <section class="pc-hero relative overflow-hidden rounded-[1.75rem] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-cyan-50/90 to-white/95 pc-glass">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-24 h-72 w-72 rounded-full bg-sky-300/16 blur-3xl"></div>

            <div class="relative grid h-full gap-6 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.3fr)_340px] xl:items-stretch">
                <div class="flex min-w-0 flex-col justify-between gap-6">
                    <div>
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/75 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Kader Measurement Center
                        </div>

                        <h1 class="mt-4 max-w-4xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2rem]">
                            Pengukuran fisik sasaran, rapi sebelum direview Bidan.
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-slate-600">
                            Kader mencatat data dasar seperti berat badan, tinggi badan, lingkar tubuh, tensi, dan catatan awal. Validasi klinis tetap dilakukan Bidan, biar sistem ini tidak berubah jadi pasar malam data.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('kader.pemeriksaan.create') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5">
                            <i class="fa-solid fa-plus"></i>
                            Input Pengukuran
                        </a>

                        <a href="{{ route('kader.dashboard') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-chart-line"></i>
                            Dashboard
                        </a>
                    </div>
                </div>

                <div class="pc-hero-total flex h-full min-h-[210px] flex-col justify-between rounded-[1.55rem] border border-white/80 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">
                                Total Data
                            </p>
                            <p class="mt-3 text-4xl font-black leading-none tracking-tight text-slate-950">
                                {{ number_format($totalData) }}
                            </p>
                            <p class="mt-2 text-sm font-bold text-slate-500">
                                {{ $filterCaption }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-700 to-slate-950 text-white shadow-lg shadow-slate-400/20">
                            <i class="fa-solid fa-database"></i>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[1.2rem] border border-emerald-200 bg-emerald-50/80 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black text-emerald-700">Output sistem</p>
                                <p class="mt-1 text-xs font-semibold leading-5 text-slate-600">
                                    Data masuk antrean review Bidan sebelum tampil sebagai riwayat warga.
                                </p>
                            </div>
                            <i class="fa-solid fa-shield-heart text-lg text-emerald-600"></i>
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
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        Ada input yang belum valid. Perbaiki dulu, jangan biarkan form jadi jebakan kecil.
                    </div>
                @endif
            </section>
        @endif

        <section class="grid gap-4 md:grid-cols-3">
            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-amber-200 bg-gradient-to-br from-amber-50 via-yellow-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-amber-700">Menunggu</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($pendingCount) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Belum direview Bidan</p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/85 text-amber-600 shadow-sm">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-rose-200 bg-gradient-to-br from-rose-50 via-orange-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-rose-700">Revisi</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($revisiCount) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Harus diperbaiki</p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/85 text-rose-600 shadow-sm">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">Tervalidasi</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($verifiedCount) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Sudah dicek Bidan</p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/85 text-emerald-600 shadow-sm">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="pc-stat-card pc-glass pc-soft-hover rounded-[1.55rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-700">Balita</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($balitaCount) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Data pengukuran</p>
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
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($remajaCount) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Data pengukuran</p>
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
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ number_format($lansiaCount) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Data pengukuran</p>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-400/20">
                        <i class="fa-solid fa-person-cane"></i>
                    </div>
                </div>
            </div>
        </section>

        <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
            <form method="GET"
                  action="{{ route('kader.pemeriksaan.index') }}"
                  class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_210px_210px_auto] lg:items-center">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                    <input type="search"
                           name="search"
                           value="{{ $search }}"
                           autocomplete="off"
                           placeholder="Cari nama atau NIK sasaran..."
                           class="w-full rounded-2xl border border-slate-200 bg-white/85 py-3 pl-11 pr-4 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                </div>

                <select name="kategori"
                        class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                    @foreach($kategoriOptions as $key => $label)
                        <option value="{{ $key }}" @selected($kategori === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="status"
                        class="w-full rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="grid grid-cols-[1fr_auto] gap-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5">
                        <i class="fa-solid fa-filter"></i>
                        Filter
                    </button>

                    @if($activeFilterCount > 0)
                        <a href="{{ route('kader.pemeriksaan.index') }}"
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
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Daftar Pengukuran</p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                        Riwayat input pengukuran fisik
                    </h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">
                        {{ number_format($totalData) }} data ditemukan. Data tervalidasi otomatis dikunci.
                    </p>
                </div>

                <a href="{{ route('kader.pemeriksaan.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Data
                </a>
            </div>

            <div class="pc-table-wrap hidden lg:block">
                <table class="min-w-[1080px] w-full border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-left text-[11px] font-black uppercase tracking-[.16em] text-slate-400">
                            <th class="px-4 py-2">Sasaran</th>
                            <th class="px-4 py-2">Tanggal</th>
                            <th class="px-4 py-2">Kategori</th>
                            <th class="px-4 py-2">BB</th>
                            <th class="px-4 py-2">TB</th>
                            <th class="px-4 py-2">IMT</th>
                            <th class="px-4 py-2">Tensi</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($pemeriksaans as $item)
                            @php
                                $nama = $pasienNama($item);
                                $nik = $pasienNik($item);
                                $km = $kategoriMeta($item->kategori_pasien);
                                $sm = $statusMeta($item->status_verifikasi ?? null);
                            @endphp

                            <tr class="pc-soft-hover rounded-[1.35rem] border border-slate-200 bg-white/80 shadow-sm">
                                <td class="rounded-l-[1.35rem] border-y border-l border-slate-200 px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $km['badge'] }} border text-sm font-black">
                                            {{ Str::upper(Str::substr($nama, 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="line-clamp-1 text-sm font-black text-slate-950">{{ $nama }}</p>
                                            <p class="mt-1 text-xs font-bold text-slate-500">NIK {{ $nik }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4 text-sm font-black text-slate-700">
                                    {{ $formatTanggal($item) }}
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-black {{ $km['badge'] }}">
                                        <i class="fa-solid {{ $km['icon'] }}"></i>
                                        {{ $km['label'] }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4 text-sm font-black text-slate-700">
                                    {{ $metric($item->berat_badan, 'kg') }}
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4 text-sm font-black text-slate-700">
                                    {{ $metric($item->tinggi_badan, 'cm') }}
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4 text-sm font-black text-slate-700">
                                    {{ $metric($item->imt) }}
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4 text-sm font-black text-slate-700">
                                    {{ $metric($item->tekanan_darah) }}
                                </td>

                                <td class="border-y border-slate-200 px-4 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-black {{ $sm['badge'] }}">
                                        <span class="h-2 w-2 rounded-full {{ $sm['dot'] }}"></span>
                                        {{ $sm['label'] }}
                                    </span>
                                </td>

                                <td class="rounded-r-[1.35rem] border-y border-r border-slate-200 px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('kader.pemeriksaan.show', $item->id) }}"
                                           class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-700 shadow-sm transition hover:bg-slate-50">
                                            <i class="fa-solid fa-eye"></i>
                                            Detail
                                        </a>

                                        @if($sm['editable'])
                                            <a href="{{ route('kader.pemeriksaan.edit', $item->id) }}"
                                               class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl bg-slate-950 px-3 text-xs font-black text-white shadow-sm transition hover:bg-slate-800">
                                                <i class="fa-solid fa-pen"></i>
                                                Edit
                                            </a>

                                            <form action="{{ route('kader.pemeriksaan.destroy', $item->id) }}"
                                                  method="POST"
                                                  data-delete-form>
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-3 text-xs font-black text-rose-700 transition hover:bg-rose-100"
                                                        title="Hapus data">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 text-xs font-black text-emerald-700">
                                                <i class="fa-solid fa-lock"></i>
                                                Dikunci
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="rounded-[1.35rem] border border-dashed border-emerald-200 bg-emerald-50/70 p-8 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                                        <i class="fa-solid fa-folder-open"></i>
                                    </div>

                                    <h3 class="mt-4 text-base font-black text-slate-950">Data pengukuran belum ada</h3>
                                    <p class="mt-2 text-sm font-medium text-slate-500">
                                        Input pengukuran fisik pertama agar masuk antrean review Bidan.
                                    </p>

                                    <a href="{{ route('kader.pemeriksaan.create') }}"
                                       class="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20">
                                        <i class="fa-solid fa-plus"></i>
                                        Input Pengukuran
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 lg:hidden">
                @forelse($pemeriksaans as $item)
                    @php
                        $nama = $pasienNama($item);
                        $nik = $pasienNik($item);
                        $km = $kategoriMeta($item->kategori_pasien);
                        $sm = $statusMeta($item->status_verifikasi ?? null);
                        $note = $catatanBidan($item);
                    @endphp

                    <article class="pc-soft-hover rounded-[1.35rem] border p-4 {{ $sm['card'] }}">
                        <div class="flex items-start gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $km['badge'] }} border text-sm font-black">
                                {{ Str::upper(Str::substr($nama, 0, 1)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="line-clamp-1 text-sm font-black text-slate-950">{{ $nama }}</h3>
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-black {{ $sm['badge'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $sm['dot'] }}"></span>
                                        {{ $sm['label'] }}
                                    </span>
                                </div>

                                <p class="mt-1 text-xs font-bold text-slate-500">NIK {{ $nik }}</p>

                                <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-bold text-slate-600">
                                    <div class="rounded-2xl bg-white/75 p-3">
                                        <p class="text-slate-400">Tanggal</p>
                                        <p class="mt-1 text-slate-800">{{ $formatTanggal($item) }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-white/75 p-3">
                                        <p class="text-slate-400">Kategori</p>
                                        <p class="mt-1 text-slate-800">{{ $km['label'] }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-white/75 p-3">
                                        <p class="text-slate-400">BB</p>
                                        <p class="mt-1 text-slate-800">{{ $metric($item->berat_badan, 'kg') }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-white/75 p-3">
                                        <p class="text-slate-400">TB</p>
                                        <p class="mt-1 text-slate-800">{{ $metric($item->tinggi_badan, 'cm') }}</p>
                                    </div>
                                </div>

                                @if($note)
                                    <div class="mt-3 rounded-2xl border border-rose-200 bg-white/80 p-3">
                                        <p class="text-[11px] font-black uppercase tracking-[.14em] text-rose-700">Catatan Bidan</p>
                                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-600">{{ $note }}</p>
                                    </div>
                                @endif

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="{{ route('kader.pemeriksaan.show', $item->id) }}"
                                       class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-black text-slate-700 shadow-sm">
                                        <i class="fa-solid fa-eye"></i>
                                        Detail
                                    </a>

                                    @if($sm['editable'])
                                        <a href="{{ route('kader.pemeriksaan.edit', $item->id) }}"
                                           class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-slate-950 px-3 py-2.5 text-xs font-black text-white shadow-sm">
                                            <i class="fa-solid fa-pen"></i>
                                            Edit
                                        </a>
                                    @else
                                        <span class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-xs font-black text-emerald-700">
                                            <i class="fa-solid fa-lock"></i>
                                            Dikunci
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.35rem] border border-dashed border-emerald-200 bg-emerald-50/70 p-8 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>

                        <h3 class="mt-4 text-base font-black text-slate-950">Data pengukuran belum ada</h3>
                        <p class="mt-2 text-sm font-medium text-slate-500">
                            Input pengukuran fisik pertama agar masuk antrean review Bidan.
                        </p>

                        <a href="{{ route('kader.pemeriksaan.create') }}"
                           class="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20">
                            <i class="fa-solid fa-plus"></i>
                            Input Pengukuran
                        </a>
                    </div>
                @endforelse
            </div>

            @if(method_exists($pemeriksaans, 'links'))
                <div class="mt-5">
                    {{ $pemeriksaans->links() }}
                </div>
            @endif
        </section>
    </div>

    <div id="pcDeleteModal" class="pc-modal-backdrop" aria-hidden="true">
        <div class="pc-modal-card p-6">
            <div class="flex gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-rose-500 to-orange-500 text-white shadow-lg shadow-rose-500/20">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-rose-700">Konfirmasi</p>
                    <h3 class="mt-1 text-lg font-black text-slate-950">Hapus data pengukuran?</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Data yang belum direview Bidan dapat dihapus. Data tervalidasi tetap dikunci oleh sistem.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button"
                        id="pcCancelDelete"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="button"
                        id="pcConfirmDelete"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-orange-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-rose-400/20 transition hover:-translate-y-0.5">
                    <i class="fa-solid fa-trash"></i>
                    Hapus Data
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    let targetDeleteForm = null;

    let modal = document.querySelector('#pcDeleteModal');

if (modal && modal.parentElement !== document.body) {
    document.body.appendChild(modal);
}

const cancelBtn = document.querySelector('#pcCancelDelete');
const confirmBtn = document.querySelector('#pcConfirmDelete');

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function openModal(form) {
        targetDeleteForm = form;

        if (!modal) {
            return;
        }

        lockBody();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        setTimeout(function () {
            if (confirmBtn) {
                confirmBtn.focus();
            }
        }, 80);
    }

    function closeModal() {
        targetDeleteForm = null;

        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-delete-form]');

        if (!form) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        openModal(form);
    }, true);

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!targetDeleteForm) {
                closeModal();
                return;
            }

            confirmBtn.disabled = true;
            confirmBtn.classList.add('opacity-70', 'cursor-not-allowed');
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';

            HTMLFormElement.prototype.submit.call(targetDeleteForm);
        });
    }
})();
</script>
@endpush