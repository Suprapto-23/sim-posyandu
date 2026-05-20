@extends('layouts.kader')

@section('title', 'Absensi Posyandu')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $kategoriAktif = $kategori ?? request('kategori', 'balita');
    $pasiens = $pasiens ?? collect();
    $absensiData = $absensiData ?? collect();

    $kategoriMenus = [
        'balita' => [
            'label' => 'Balita / Anak',
            'short' => 'Balita',
            'icon' => 'fa-child-reaching',
            'tone' => 'emerald',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'short' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'tone' => 'gold',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'short' => 'Lansia',
            'icon' => 'fa-person-cane',
            'tone' => 'sky',
        ],
    ];

    $tanggalFormat = Carbon::parse($tanggal ?? now())->translatedFormat('l, d F Y');
    $totalPasien = $pasiens->count();
    $totalHadir = 0;
    $totalTercatat = 0;

    foreach ($pasiens as $pasien) {
        $detail = $absensiData->get($pasien->id);

        if ($detail) {
            $totalTercatat++;

            if ((bool) $detail->hadir) {
                $totalHadir++;
            }
        }
    }

    $totalAbsen = max(0, $totalPasien - $totalHadir);
    $persenHadir = $totalPasien > 0 ? round(($totalHadir / $totalPasien) * 100) : 0;
    $routeHas = fn ($name) => Route::has($name);
    $currentKategori = $kategoriMenus[$kategoriAktif] ?? $kategoriMenus['balita'];
@endphp

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

    :root {
        --emerald-50: #ecfdf5;
        --emerald-100: #d1fae5;
        --emerald-200: #a7f3d0;
        --emerald-500: #10b981;
        --emerald-600: #059669;
        --emerald-700: #047857;
        --gold-50: #fff8eb;
        --gold-100: #fef3c7;
        --gold-200: #fde68a;
        --gold-500: #f59e0b;
        --gold-600: #d97706;
        --sky-50: #f0f9ff;
        --sky-100: #e0f2fe;
        --sky-500: #0ea5e9;
        --rose-50: #fff1f2;
        --rose-100: #ffe4e6;
        --rose-500: #f43f5e;
        --rose-600: #e11d48;
        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-300: #cbd5e1;
        --slate-400: #94a3b8;
        --slate-500: #64748b;
        --slate-700: #334155;
        --slate-900: #0f172a;
    }

    .abs-page {
        position: relative;
        isolation: isolate;
        font-family: "Plus Jakarta Sans", Inter, ui-sans-serif, system-ui, sans-serif;
        animation: absFade .26s cubic-bezier(.16, 1, .3, 1) both;
    }

    .abs-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -2;
        pointer-events: none;
        background:
            radial-gradient(circle at 10% 8%, rgba(16, 185, 129, .16), transparent 28%),
            radial-gradient(circle at 88% 10%, rgba(245, 158, 11, .14), transparent 26%),
            radial-gradient(circle at 78% 86%, rgba(14, 165, 233, .10), transparent 30%),
            linear-gradient(135deg, #f7fffc 0%, #f8fafc 48%, #fffaf1 100%);
    }

    .abs-page::after {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        opacity: .28;
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 42px 42px;
        mask-image: linear-gradient(to bottom, rgba(0,0,0,.7), transparent 88%);
    }

    @keyframes absFade {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .abs-glass {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .52);
        background: rgba(255, 255, 255, .72);
        box-shadow:
            0 20px 50px rgba(15, 23, 42, .065),
            inset 0 1px 0 rgba(255, 255, 255, .88);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .abs-glass::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(180deg, rgba(255,255,255,.28), rgba(255,255,255,.05)),
            radial-gradient(circle at 95% 10%, rgba(16,185,129,.10), transparent 30%);
    }

    .abs-glass > * {
        position: relative;
        z-index: 1;
    }

    .abs-hero {
        border-color: rgba(167, 243, 208, .58);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.20), transparent 30%),
            radial-gradient(circle at 89% 16%, rgba(245,158,11,.18), transparent 32%),
            linear-gradient(135deg, rgba(255,255,255,.86), rgba(236,253,245,.74));
    }

    .abs-hero-glow {
        position: absolute;
        right: -70px;
        bottom: -90px;
        width: 245px;
        height: 245px;
        border-radius: 999px;
        background: conic-gradient(from 180deg, rgba(16,185,129,.22), rgba(245,158,11,.18), rgba(14,165,233,.10), rgba(16,185,129,.18));
        filter: blur(10px);
        opacity: .86;
        pointer-events: none;
    }

    .abs-badge {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        border-radius: 999px;
        border: 1px solid rgba(16, 185, 129, .18);
        background: rgba(236, 253, 245, .82);
        color: var(--emerald-700);
        padding: .7rem 1rem;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .abs-pill {
        border-radius: 18px;
        font-weight: 900;
        transition: transform .18s cubic-bezier(.16, 1, .3, 1), box-shadow .18s ease, background .18s ease, border-color .18s ease;
    }

    .abs-pill:hover {
        transform: translateY(-2px);
    }

    .abs-pill-primary {
        background: linear-gradient(135deg, var(--emerald-600), var(--emerald-500));
        color: white;
        box-shadow: 0 14px 30px rgba(5, 150, 105, .20);
    }

    .abs-pill-primary:hover {
        background: linear-gradient(135deg, var(--emerald-700), var(--emerald-600));
    }

    .abs-pill-dark {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: white;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .16);
    }

    .abs-pill-dark:hover {
        background: linear-gradient(135deg, var(--emerald-700), #064e3b);
    }

    .abs-pill-outline {
        border: 1px solid rgba(16,185,129,.18);
        background: rgba(255,255,255,.82);
        color: var(--emerald-700);
    }

    .abs-pill-outline:hover {
        background: rgba(236,253,245,.92);
        border-color: rgba(16,185,129,.28);
    }

    .abs-card {
        border-radius: 26px;
    }

    .abs-stat-emerald {
        border-color: rgba(16,185,129,.20);
        background:
            radial-gradient(circle at 88% 18%, rgba(16,185,129,.18), transparent 34%),
            linear-gradient(145deg, rgba(255,255,255,.86), rgba(236,253,245,.74));
    }

    .abs-stat-sky {
        border-color: rgba(14,165,233,.18);
        background:
            radial-gradient(circle at 88% 18%, rgba(14,165,233,.16), transparent 34%),
            linear-gradient(145deg, rgba(255,255,255,.86), rgba(240,249,255,.78));
    }

    .abs-stat-gold {
        border-color: rgba(245,158,11,.18);
        background:
            radial-gradient(circle at 88% 18%, rgba(245,158,11,.15), transparent 34%),
            linear-gradient(145deg, rgba(255,255,255,.86), rgba(255,248,235,.82));
    }

    .abs-stat-rose {
        border-color: rgba(244,63,94,.16);
        background:
            radial-gradient(circle at 88% 18%, rgba(244,63,94,.13), transparent 34%),
            linear-gradient(145deg, rgba(255,255,255,.86), rgba(255,241,242,.76));
    }

    .abs-panel {
        border-color: rgba(167,243,208,.42);
        background:
            radial-gradient(circle at 96% 12%, rgba(16,185,129,.10), transparent 30%),
            linear-gradient(145deg, rgba(255,255,255,.80), rgba(240,253,244,.68));
    }

    .abs-category-btn {
        border: 1px solid rgba(203, 213, 225, .72);
        background: rgba(255, 255, 255, .78);
        color: var(--slate-700);
        border-radius: 18px;
        font-weight: 900;
        transition: all .18s cubic-bezier(.16, 1, .3, 1);
    }

    .abs-category-btn:hover {
        transform: translateY(-2px);
        border-color: rgba(16, 185, 129, .22);
        color: var(--emerald-700);
        background: rgba(236, 253, 245, .90);
    }

    .abs-category-btn.is-active {
        border-color: rgba(16,185,129,.18);
        background: linear-gradient(135deg, var(--emerald-600), var(--emerald-500));
        color: white;
        box-shadow: 0 14px 30px rgba(5,150,105,.20);
    }

    .patient-scroll {
        max-height: min(640px, calc(100vh - 370px));
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: .45rem;
        scroll-behavior: smooth;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .patient-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .patient-scroll::-webkit-scrollbar-track {
        background: rgba(226, 232, 240, .50);
        border-radius: 999px;
    }

    .patient-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, var(--emerald-500), var(--gold-500));
        border-radius: 999px;
    }

    .patient-scroll::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, var(--emerald-600), var(--gold-600));
    }

    .abs-row {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(226,232,240,.86);
        background:
            linear-gradient(145deg, rgba(255,255,255,.93), rgba(248,250,252,.80));
        box-shadow: 0 12px 28px rgba(15,23,42,.038);
        transition:
            transform .18s cubic-bezier(.16,1,.3,1),
            box-shadow .18s ease,
            border-color .18s ease,
            background .18s ease;
    }

    .abs-row::before {
        content: "";
        position: absolute;
        left: 0;
        top: 14px;
        bottom: 14px;
        width: 4px;
        border-radius: 999px;
        background: linear-gradient(180deg, var(--emerald-500), var(--gold-500));
        opacity: .70;
    }

    .abs-row::after {
        content: "";
        position: absolute;
        right: -46px;
        top: -46px;
        width: 140px;
        height: 140px;
        border-radius: 999px;
        background: rgba(16,185,129,.08);
        opacity: 0;
        transition: opacity .18s ease;
        pointer-events: none;
    }

    .abs-row:hover {
        transform: translateY(-3px);
        border-color: rgba(16,185,129,.24);
        box-shadow: 0 18px 36px rgba(15,23,42,.075);
    }

    .abs-row:hover::after {
        opacity: 1;
    }

    .abs-row.is-present {
        border-color: rgba(16,185,129,.26);
        background:
            radial-gradient(circle at 96% 12%, rgba(16,185,129,.13), transparent 32%),
            linear-gradient(145deg, rgba(255,255,255,.94), rgba(236,253,245,.82));
    }

    .abs-row.is-present::before {
        background: linear-gradient(180deg, var(--emerald-500), var(--emerald-700));
        opacity: 1;
    }

    .abs-row.is-absent {
        border-color: rgba(244,63,94,.18);
        background:
            radial-gradient(circle at 96% 12%, rgba(244,63,94,.10), transparent 32%),
            linear-gradient(145deg, rgba(255,255,255,.94), rgba(255,241,242,.78));
    }

    .abs-row.is-absent::before {
        background: linear-gradient(180deg, #fb7185, var(--rose-600));
        opacity: .90;
    }

    .attendance-btn {
        border: 1px solid rgba(226,232,240,.88);
        background: rgba(255,255,255,.88);
        color: var(--slate-500);
        border-radius: 16px;
        transition: all .16s cubic-bezier(.16,1,.3,1);
    }

    .attendance-btn:hover {
        transform: translateY(-1px);
    }

    .attendance-btn.is-active[data-status="1"] {
        border-color: rgba(16,185,129,.36);
        background: linear-gradient(135deg, var(--emerald-50), var(--emerald-100));
        color: var(--emerald-700);
        box-shadow: 0 12px 24px rgba(16,185,129,.10);
    }

    .attendance-btn.is-active[data-status="0"] {
        border-color: rgba(244,63,94,.24);
        background: linear-gradient(135deg, var(--rose-50), var(--rose-100));
        color: var(--rose-600);
        box-shadow: 0 12px 24px rgba(244,63,94,.08);
    }

    .search-input {
        border: 1px solid rgba(226,232,240,.86);
        background: linear-gradient(135deg, rgba(255,255,255,.94), rgba(248,250,252,.90));
    }

    .search-input:focus {
        border-color: rgba(16,185,129,.24);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
    }

    .abs-sticky {
        border-color: rgba(167,243,208,.50);
        background:
            radial-gradient(circle at 95% 12%, rgba(245,158,11,.10), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.94), rgba(236,253,245,.88));
    }

    @media (max-width: 640px) {
        .abs-glass {
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }

        .abs-row:hover,
        .abs-pill:hover,
        .abs-category-btn:hover,
        .attendance-btn:hover {
            transform: none;
        }

        .patient-scroll {
            max-height: 62vh;
            padding-right: .15rem;
        }

        .abs-page::after {
            opacity: .16;
            background-size: 34px 34px;
        }

        .abs-hero-glow {
            width: 165px;
            height: 165px;
            right: -55px;
            bottom: -55px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .abs-page,
        .abs-row,
        .abs-pill,
        .abs-category-btn,
        .attendance-btn {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="abs-page space-y-6">

    {{-- HERO --}}
    <section class="abs-glass abs-hero rounded-[32px] p-5 sm:p-6 lg:p-7">
        <div class="abs-hero-glow"></div>

        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="abs-badge mb-4">
                    <i class="fa-solid fa-clipboard-check"></i>
                    Registrasi Kehadiran
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Absensi Posyandu
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Data warga otomatis diambil dari Data Sasaran. Kader cukup menandai hadir atau tidak hadir tanpa perlu menulis ulang data peserta.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                @if($routeHas('kader.absensi.riwayat'))
                    <a href="{{ route('kader.absensi.riwayat') }}" class="abs-pill abs-pill-outline inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Riwayat
                    </a>
                @endif

                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}" class="abs-pill abs-pill-dark inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-chart-simple"></i>
                        Dashboard
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- RINGKASAN --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="abs-glass abs-card abs-stat-emerald p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Kategori</p>
            <div class="mt-3 flex items-center gap-3">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="fa-solid {{ $currentKategori['icon'] }}"></i>
                </div>

                <div>
                    <h2 class="text-lg font-black text-slate-900">{{ $currentKategori['label'] }}</h2>
                    <p class="text-xs font-bold text-slate-400">Pertemuan ke-{{ $pertemuanBerikutnya ?? 1 }}</p>
                </div>
            </div>
        </div>

        <div class="abs-glass abs-card abs-stat-sky p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Total Sasaran</p>
            <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($totalPasien) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">{{ $tanggalFormat }}</p>
        </div>

        <div class="abs-glass abs-card abs-stat-gold p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Hadir</p>
            <h2 id="totalHadirText" class="mt-3 text-3xl font-black text-emerald-700">{{ number_format($totalHadir) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">
                <span id="persenHadirText">{{ $persenHadir }}</span>% dari sasaran
            </p>
        </div>

        <div class="abs-glass abs-card abs-stat-rose p-5">
            <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Belum / Tidak Hadir</p>
            <h2 id="totalAbsenText" class="mt-3 text-3xl font-black text-rose-600">{{ number_format($totalAbsen) }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">
                Sesi: {{ $sesiHariIni ? 'Update hari ini' : 'Baru hari ini' }}
            </p>
        </div>
    </section>

    {{-- FILTER KATEGORI --}}
    <section class="abs-glass abs-panel rounded-[28px] p-4 sm:p-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900">Pilih Kategori Sasaran</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">Absensi otomatis membaca data dari menu Data Sasaran.</p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                @foreach($kategoriMenus as $key => $item)
                    <a href="{{ route('kader.absensi.index', ['kategori' => $key]) }}"
                       class="abs-category-btn inline-flex items-center justify-center gap-2 px-5 py-3 text-sm {{ $kategoriAktif === $key ? 'is-active' : '' }}">
                        <i class="fa-solid {{ $item['icon'] }}"></i>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FORM ABSENSI --}}
    <form method="POST" action="{{ route('kader.absensi.store') }}" id="absensiForm" class="abs-glass abs-panel overflow-hidden rounded-[32px]">
        @csrf

        <input type="hidden" name="kategori" value="{{ $kategoriAktif }}">

        <div class="border-b border-emerald-100/60 p-4 sm:p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div>
                        <h3 class="text-lg font-black text-slate-900">Daftar Sasaran</h3>
                        <p class="text-xs font-bold text-slate-400">
                            <span id="visibleCount">{{ number_format($totalPasien) }}</span>
                            data ditampilkan dari {{ number_format($totalPasien) }} data kategori {{ $currentKategori['label'] }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                        <input
                            type="text"
                            id="searchAbsensi"
                            placeholder="Cari nama atau NIK..."
                            class="search-input h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700 outline-none transition sm:w-72"
                        >
                    </div>

                    <button type="button" id="btnHadirSemua" class="abs-pill abs-pill-primary inline-flex h-12 items-center justify-center gap-2 px-5 text-sm">
                        <i class="fa-solid fa-check-double"></i>
                        Hadir Semua
                    </button>

                    <button type="button" id="btnAbsenSemua" class="abs-pill inline-flex h-12 items-center justify-center gap-2 border border-rose-200 bg-white/80 px-5 text-sm font-black text-rose-600 hover:bg-rose-50">
                        <i class="fa-solid fa-xmark"></i>
                        Absen Semua
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-5">
            @if($pasiens->isNotEmpty())
                <div class="patient-scroll custom-scrollbar">
                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        @foreach($pasiens as $index => $pasien)
                            @php
                                $detail = $absensiData->get($pasien->id);
                                $saved = (bool) $detail;
                                $status = $detail ? (int) $detail->hadir : 0;
                                $nama = $pasien->nama_lengkap ?? $pasien->nama ?? 'Tanpa Nama';
                                $nik = $pasien->nik ?? '-';
                                $keterangan = $detail->keterangan ?? '';
                                $rowState = $saved ? ($status === 1 ? 'is-present' : 'is-absent') : '';
                            @endphp

                            <div class="abs-row {{ $rowState }} rounded-[24px] p-4"
                                 data-name="{{ strtolower($nama) }}"
                                 data-nik="{{ strtolower($nik) }}"
                                 data-saved="{{ $saved ? '1' : '0' }}">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-sm font-black text-emerald-700">
                                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        </div>

                                        <div class="min-w-0">
                                            <h4 class="truncate text-sm font-black text-slate-800">{{ $nama }}</h4>
                                            <p class="mt-1 text-xs font-bold text-slate-400">
                                                <i class="fa-solid fa-id-card mr-1 text-emerald-400"></i>
                                                {{ $nik }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <input
                                            type="hidden"
                                            name="kehadiran[{{ $pasien->id }}]"
                                            value="{{ $status }}"
                                            class="attendance-input"
                                            data-id="{{ $pasien->id }}"
                                        >

                                        <button
                                            type="button"
                                            class="attendance-btn px-4 py-2 text-xs font-black {{ $saved && $status === 1 ? 'is-active' : '' }}"
                                            data-id="{{ $pasien->id }}"
                                            data-status="1"
                                        >
                                            <i class="fa-solid fa-check mr-1"></i>
                                            Hadir
                                        </button>

                                        <button
                                            type="button"
                                            class="attendance-btn px-4 py-2 text-xs font-black {{ $saved && $status === 0 ? 'is-active' : '' }}"
                                            data-id="{{ $pasien->id }}"
                                            data-status="0"
                                        >
                                            <i class="fa-solid fa-xmark mr-1"></i>
                                            Absen
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <input
                                        type="text"
                                        name="keterangan[{{ $pasien->id }}]"
                                        value="{{ $keterangan }}"
                                        placeholder="Keterangan opsional..."
                                        class="w-full rounded-2xl border border-slate-100 bg-white/70 px-4 py-3 text-xs font-bold text-slate-600 outline-none transition focus:border-emerald-200 focus:bg-white focus:ring-4 focus:ring-emerald-50"
                                    >
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="abs-glass abs-sticky sticky bottom-4 z-20 mt-6 rounded-[26px] p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-black text-slate-900">Simpan Presensi Hari Ini</p>
                            <p class="mt-1 text-xs font-bold text-slate-400">
                                Data akan disimpan untuk {{ $tanggalFormat }}. Jika sudah pernah disimpan, sistem akan memperbarui data presensi.
                            </p>
                        </div>

                        <button type="submit" class="abs-pill abs-pill-primary inline-flex items-center justify-center gap-2 px-6 py-3 text-sm">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Simpan Presensi
                        </button>
                    </div>
                </div>
            @else
                <div class="rounded-[26px] border border-dashed border-slate-200 bg-white/70 p-8 text-center">
                    <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-3xl bg-emerald-50 text-emerald-600">
                        <i class="fa-solid fa-users-slash text-xl"></i>
                    </div>

                    <h3 class="text-lg font-black text-slate-800">Data sasaran belum tersedia</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-500">
                        Tambahkan data {{ $currentKategori['label'] }} terlebih dahulu supaya daftar absensi otomatis muncul.
                    </p>

                    @php
                        $dataRoute = match($kategoriAktif) {
                            'remaja' => 'kader.data.remaja.create',
                            'lansia' => 'kader.data.lansia.create',
                            default => 'kader.data.balita.create',
                        };
                    @endphp

                    @if($routeHas($dataRoute))
                        <a href="{{ route($dataRoute) }}" class="abs-pill abs-pill-primary mt-5 inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                            <i class="fa-solid fa-plus"></i>
                            Tambah Data Sasaran
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const totalPasien = Number(@json($totalPasien));
        const rows = Array.from(document.querySelectorAll('.abs-row'));
        const totalHadirText = document.getElementById('totalHadirText');
        const totalAbsenText = document.getElementById('totalAbsenText');
        const persenHadirText = document.getElementById('persenHadirText');
        const visibleCountText = document.getElementById('visibleCount');

        const updateSummary = () => {
            const inputs = Array.from(document.querySelectorAll('.attendance-input'));
            const hadir = inputs.filter(input => input.value === '1').length;
            const absen = Math.max(0, totalPasien - hadir);
            const persen = totalPasien > 0 ? Math.round((hadir / totalPasien) * 100) : 0;

            if (totalHadirText) totalHadirText.textContent = hadir.toLocaleString('id-ID');
            if (totalAbsenText) totalAbsenText.textContent = absen.toLocaleString('id-ID');
            if (persenHadirText) persenHadirText.textContent = persen;
        };

        const updateVisibleCount = () => {
            const visibleRows = rows.filter(row => row.style.display !== 'none').length;

            if (visibleCountText) {
                visibleCountText.textContent = visibleRows.toLocaleString('id-ID');
            }
        };

        const applyVisualState = (input) => {
            const row = input.closest('.abs-row');
            const buttons = document.querySelectorAll(`.attendance-btn[data-id="${input.dataset.id}"]`);

            if (!row) return;

            const isSaved = row.dataset.saved === '1';
            const value = String(input.value);

            row.classList.toggle('is-present', isSaved && value === '1');
            row.classList.toggle('is-absent', isSaved && value === '0');

            buttons.forEach(button => {
                button.classList.toggle('is-active', isSaved && button.dataset.status === value);
            });
        };

        const setStatus = (id, status) => {
            const input = document.querySelector(`.attendance-input[data-id="${id}"]`);

            if (!input) return;

            const row = input.closest('.abs-row');

            input.value = String(status);

            if (row) {
                row.dataset.saved = '1';
            }

            applyVisualState(input);
            updateSummary();
        };

        document.querySelectorAll('.attendance-btn').forEach(button => {
            button.addEventListener('click', () => {
                setStatus(button.dataset.id, button.dataset.status);
            });
        });

        const setAll = (status) => {
            document.querySelectorAll('.attendance-input').forEach(input => {
                setStatus(input.dataset.id, status);
            });
        };

        document.getElementById('btnHadirSemua')?.addEventListener('click', () => setAll(1));
        document.getElementById('btnAbsenSemua')?.addEventListener('click', () => setAll(0));

        document.getElementById('searchAbsensi')?.addEventListener('input', (event) => {
            const keyword = event.target.value.toLowerCase().trim();

            rows.forEach(row => {
                const name = row.dataset.name || '';
                const nik = row.dataset.nik || '';
                const match = name.includes(keyword) || nik.includes(keyword);

                row.style.display = match ? '' : 'none';
            });

            updateVisibleCount();
        });

        document.querySelectorAll('.attendance-input').forEach(input => applyVisualState(input));
        updateSummary();
        updateVisibleCount();
    });
</script>
@endpush