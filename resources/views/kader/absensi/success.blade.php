@extends('layouts.kader')

@section('title', 'Presensi Selesai')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $details = $absensi->details ?? collect();
    $totalPeserta = $details->count();
    $totalHadir = $details->where('hadir', true)->count();
    $totalTidakHadir = max(0, $totalPeserta - $totalHadir);
    $persentase = $totalPeserta > 0 ? round(($totalHadir / $totalPeserta) * 100) : 0;

    $kategoriLabel = match($absensi->kategori ?? '') {
        'balita' => 'Balita / Anak',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        default => 'Sasaran',
    };

    $kategoriIcon = match($absensi->kategori ?? '') {
        'balita' => 'fa-child-reaching',
        'remaja' => 'fa-user-graduate',
        'lansia' => 'fa-person-cane',
        default => 'fa-users',
    };

    $tanggal = !empty($absensi->tanggal_posyandu)
        ? Carbon::parse($absensi->tanggal_posyandu)->translatedFormat('l, d F Y')
        : '-';

    $namaKader = $absensi->kader->name
        ?? $absensi->kader->nama
        ?? auth()->user()->name
        ?? 'Kader Posyandu';

    $routeHas = fn ($name) => Route::has($name);
@endphp

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

    .success-page {
        position: relative;
        isolation: isolate;
        font-family: "Plus Jakarta Sans", Inter, ui-sans-serif, system-ui, sans-serif;
        animation: pageIn .22s ease-out both;
    }

    .success-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -2;
        pointer-events: none;
        background:
            radial-gradient(circle at 12% 8%, rgba(16,185,129,.15), transparent 28%),
            radial-gradient(circle at 88% 14%, rgba(245,158,11,.12), transparent 26%),
            linear-gradient(135deg, #f7fffc 0%, #f8fafc 52%, #fffaf1 100%);
    }

    @keyframes pageIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .success-hero {
        position: relative;
        overflow: hidden;
        min-height: 430px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(167,243,208,.72);
        background:
            radial-gradient(circle at 18% 22%, rgba(16,185,129,.14), transparent 32%),
            radial-gradient(circle at 86% 18%, rgba(245,158,11,.13), transparent 34%),
            linear-gradient(135deg, rgba(255,255,255,.96), rgba(236,253,245,.78));
        box-shadow: 0 22px 54px rgba(15,23,42,.07);
    }

    .success-hero::after {
        content: "";
        position: absolute;
        right: -90px;
        bottom: -120px;
        width: 300px;
        height: 300px;
        border-radius: 999px;
        background: rgba(16,185,129,.12);
        pointer-events: none;
    }

    .success-center {
        position: relative;
        z-index: 2;
        max-width: 760px;
        text-align: center;
    }

    .success-check-wrap {
        position: relative;
        width: 118px;
        height: 118px;
        margin: 0 auto 26px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: rgba(16,185,129,.12);
    }

    .success-check-wrap::before {
        content: "";
        position: absolute;
        inset: 16px;
        border-radius: inherit;
        background: rgba(16,185,129,.18);
    }

    .success-check {
        position: relative;
        z-index: 2;
        width: 74px;
        height: 74px;
        display: grid;
        place-items: center;
        border-radius: 26px;
        color: white;
        background: linear-gradient(135deg, #059669, #10b981);
        box-shadow: 0 18px 36px rgba(5,150,105,.22);
    }

    .success-badge {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        border-radius: 999px;
        border: 1px solid rgba(16,185,129,.20);
        background: rgba(236,253,245,.88);
        color: #047857;
        padding: .68rem 1rem;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .success-action {
        border-radius: 18px;
        font-weight: 900;
        transition: background .15s ease, transform .15s ease, border-color .15s ease;
    }

    .success-action:hover {
        transform: translateY(-1px);
    }

    .success-primary {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        box-shadow: 0 14px 28px rgba(5,150,105,.18);
    }

    .success-primary:hover {
        background: linear-gradient(135deg, #047857, #059669);
    }

    .success-dark {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: white;
        box-shadow: 0 14px 28px rgba(15,23,42,.14);
    }

    .success-outline {
        border: 1px solid rgba(16,185,129,.20);
        background: rgba(255,255,255,.88);
        color: #047857;
    }

    .success-outline:hover {
        background: #ecfdf5;
    }

    .success-surface {
        border: 1px solid rgba(226,232,240,.9);
        background: rgba(255,255,255,.92);
        box-shadow: 0 16px 38px rgba(15,23,42,.055);
    }

    .success-stat {
        border-radius: 26px;
        border: 1px solid rgba(226,232,240,.86);
        box-shadow: 0 12px 28px rgba(15,23,42,.045);
    }

    .stat-emerald {
        background: linear-gradient(145deg, #ffffff, #ecfdf5);
        border-color: rgba(16,185,129,.18);
    }

    .stat-gold {
        background: linear-gradient(145deg, #ffffff, #fff8eb);
        border-color: rgba(245,158,11,.16);
    }

    .stat-rose {
        background: linear-gradient(145deg, #ffffff, #fff1f2);
        border-color: rgba(244,63,94,.14);
    }

    .stat-sky {
        background: linear-gradient(145deg, #ffffff, #f0f9ff);
        border-color: rgba(14,165,233,.14);
    }

    .success-row {
        border: 1px solid rgba(226,232,240,.86);
        background: #fff;
        box-shadow: 0 6px 18px rgba(15,23,42,.035);
    }

    .success-row.present {
        background: #f0fdf4;
        border-color: rgba(16,185,129,.22);
    }

    .success-row.absent {
        background: #fff7f8;
        border-color: rgba(244,63,94,.18);
    }

    .success-scroll {
        max-height: 420px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: .25rem;
        overscroll-behavior: contain;
    }

    .success-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .success-scroll::-webkit-scrollbar-track {
        background: rgba(226,232,240,.55);
        border-radius: 999px;
    }

    .success-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }

    .confetti {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
        z-index: 1;
    }

    .confetti span {
        position: absolute;
        top: -20px;
        width: 8px;
        height: 14px;
        border-radius: 3px;
        opacity: .75;
        animation: confettiFall 2.6s ease-in-out forwards;
    }

    .confetti span:nth-child(1) { left: 8%; background:#10b981; animation-delay:.05s; }
    .confetti span:nth-child(2) { left: 16%; background:#f59e0b; animation-delay:.18s; }
    .confetti span:nth-child(3) { left: 25%; background:#38bdf8; animation-delay:.02s; }
    .confetti span:nth-child(4) { left: 35%; background:#fb7185; animation-delay:.16s; }
    .confetti span:nth-child(5) { left: 47%; background:#a78bfa; animation-delay:.08s; }
    .confetti span:nth-child(6) { left: 58%; background:#10b981; animation-delay:.22s; }
    .confetti span:nth-child(7) { left: 68%; background:#f59e0b; animation-delay:.06s; }
    .confetti span:nth-child(8) { left: 78%; background:#38bdf8; animation-delay:.14s; }
    .confetti span:nth-child(9) { left: 88%; background:#fb7185; animation-delay:.10s; }

    @keyframes confettiFall {
        0% {
            transform: translateY(-10px) rotate(0deg);
            opacity: 0;
        }

        10% {
            opacity: .75;
        }

        100% {
            transform: translateY(520px) rotate(240deg);
            opacity: 0;
        }
    }

    @media (max-width: 640px) {
        .success-hero {
            min-height: 390px;
        }

        .success-action:hover {
            transform: none;
        }

        .confetti span {
            animation-duration: 2s;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .success-page,
        .confetti span,
        .success-action {
            animation: none !important;
            transition: none !important;
        }

        .confetti {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="success-page space-y-6">

    {{-- HERO CELEBRATION --}}
    <section class="success-hero rounded-[34px] p-5 sm:p-8">
        <div class="confetti">
            <span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span>
        </div>

        <div class="success-center">
            <div class="success-check-wrap">
                <div class="success-check">
                    <i class="fa-solid fa-check text-3xl"></i>
                </div>
            </div>

            <div class="success-badge mb-4">
                <i class="fa-solid fa-shield-check"></i>
                Sinkronisasi Sistem Berhasil
            </div>

            <h1 class="text-3xl font-black tracking-[-.05em] text-emerald-900 sm:text-4xl lg:text-5xl">
                Presensi Selesai Disimpan
            </h1>

            <p class="mx-auto mt-4 max-w-2xl text-sm font-semibold leading-7 text-slate-500 sm:text-base">
                Data kehadiran {{ $kategoriLabel }} untuk {{ $tanggal }} sudah tercatat dan siap digunakan dalam rekap laporan bulanan PosyanduCare.
            </p>

            <div class="mt-8 grid grid-cols-1 gap-3 sm:flex sm:justify-center">
                @if($routeHas('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index', ['kategori' => $absensi->kategori]) }}" class="success-action success-outline inline-flex items-center justify-center gap-2 px-6 py-3 text-sm">
                        <i class="fa-solid fa-users-gear"></i>
                        Absen Kategori Lain
                    </a>
                @endif

                @if($routeHas('kader.absensi.riwayat'))
                    <a href="{{ route('kader.absensi.riwayat') }}" class="success-action success-primary inline-flex items-center justify-center gap-2 px-6 py-3 text-sm">
                        <i class="fa-solid fa-box-archive"></i>
                        Lihat Arsip Kehadiran
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- STAT --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="success-stat stat-sky p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Kategori</p>
            <div class="mt-3 flex items-center gap-3">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-50 text-sky-600">
                    <i class="fa-solid {{ $kategoriIcon }}"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900">{{ $kategoriLabel }}</h2>
                    <p class="text-xs font-bold text-slate-400">Pertemuan ke-{{ $absensi->nomor_pertemuan ?? 1 }}</p>
                </div>
            </div>
        </div>

        <div class="success-stat stat-emerald p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Hadir</p>
            <h2 class="mt-3 text-3xl font-black text-emerald-700">{{ number_format($totalHadir) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">{{ $persentase }}% dari peserta</p>
        </div>

        <div class="success-stat stat-rose p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Tidak Hadir</p>
            <h2 class="mt-3 text-3xl font-black text-rose-600">{{ number_format($totalTidakHadir) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Tidak hadir / belum hadir</p>
        </div>

        <div class="success-stat stat-gold p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Total Peserta</p>
            <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($totalPeserta) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Dicatat oleh {{ $namaKader }}</p>
        </div>
    </section>

    {{-- DETAIL --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">
        <div class="success-surface rounded-[30px] p-5 xl:col-span-5">
            <div class="mb-5">
                <h3 class="text-lg font-black text-slate-900">Ringkasan Sesi</h3>
                <p class="text-xs font-bold text-slate-400">Informasi utama presensi yang baru disimpan.</p>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Kode Absensi</span>
                    <span class="text-sm font-black text-slate-900">{{ $absensi->kode_absensi ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Tanggal</span>
                    <span class="text-sm font-black text-slate-900">{{ $tanggal }}</span>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Bulan / Tahun</span>
                    <span class="text-sm font-black text-slate-900">
                        {{ $absensi->bulan ?? '-' }}/{{ $absensi->tahun ?? '-' }}
                    </span>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                    <span class="text-xs font-black text-slate-500">Petugas</span>
                    <span class="text-sm font-black text-slate-900">{{ $namaKader }}</span>
                </div>
            </div>
        </div>

        <div class="success-surface rounded-[30px] p-5 xl:col-span-7">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Daftar Kehadiran</h3>
                    <p class="text-xs font-bold text-slate-400">Ringkasan peserta yang hadir dan tidak hadir.</p>
                </div>

                @if($routeHas('kader.absensi.show'))
                    <a href="{{ route('kader.absensi.show', $absensi->id) }}" class="success-action success-outline inline-flex items-center justify-center gap-2 px-4 py-2 text-xs">
                        <i class="fa-solid fa-eye"></i>
                        Detail Lengkap
                    </a>
                @endif
            </div>

            <div class="success-scroll space-y-3">
                @forelse($details as $detail)
                    @php
                        $pasien = $detail->pasien;
                        $nama = $pasien->nama_lengkap ?? $pasien->nama ?? $detail->nama_pasien ?? 'Data sasaran';
                        $nik = $pasien->nik ?? $detail->nik_pasien ?? '-';
                        $isHadir = (bool) $detail->hadir;
                    @endphp

                    <div class="success-row {{ $isHadir ? 'present' : 'absent' }} rounded-[20px] p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="truncate text-sm font-black text-slate-800">{{ $nama }}</h4>
                                <p class="mt-1 text-xs font-bold text-slate-400">
                                    <i class="fa-solid fa-id-card mr-1"></i>
                                    {{ $nik }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full px-3 py-1 text-[10px] font-black {{ $isHadir ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">
                                {{ $isHadir ? 'Hadir' : 'Tidak Hadir' }}
                            </span>
                        </div>

                        @if(!empty($detail->keterangan))
                            <p class="mt-3 rounded-2xl bg-white/70 px-3 py-2 text-xs font-bold text-slate-500">
                                {{ $detail->keterangan }}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                        <p class="text-sm font-black text-slate-500">Belum ada detail presensi.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ACTION BAWAH --}}
    <section class="success-surface rounded-[28px] p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900">Presensi sudah masuk sistem</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Data ini dapat digunakan untuk riwayat kehadiran dan laporan bulanan Kader.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                @if($routeHas('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index', ['kategori' => $absensi->kategori]) }}" class="success-action success-outline inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Absensi
                    </a>
                @endif

                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}" class="success-action success-dark inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-house"></i>
                        Dashboard
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection