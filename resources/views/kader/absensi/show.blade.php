@extends('layouts.kader')

@section('title', 'Detail Absensi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $details = $details ?? ($absensi->details ?? collect());
    $totalPasien = $totalPasien ?? $details->count();
    $totalHadir = $totalHadir ?? $details->where('hadir', true)->count();
    $totalAbsen = $totalAbsen ?? max(0, $totalPasien - $totalHadir);
    $persentase = $totalPasien > 0 ? round(($totalHadir / $totalPasien) * 100) : 0;

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

    $tanggalSingkat = !empty($absensi->tanggal_posyandu)
        ? Carbon::parse($absensi->tanggal_posyandu)->translatedFormat('d M Y')
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

    .show-page {
        position: relative;
        isolation: isolate;
        font-family: "Plus Jakarta Sans", Inter, ui-sans-serif, system-ui, sans-serif;
        animation: showIn .2s ease-out both;
    }

    .show-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 10% 8%, rgba(16,185,129,.14), transparent 28%),
            radial-gradient(circle at 88% 12%, rgba(245,158,11,.11), transparent 26%),
            linear-gradient(135deg, #f7fffc 0%, #f8fafc 54%, #fffaf1 100%);
    }

    @keyframes showIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .show-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(167,243,208,.72);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.16), transparent 30%),
            radial-gradient(circle at 88% 18%, rgba(245,158,11,.13), transparent 32%),
            linear-gradient(135deg, rgba(255,255,255,.96), rgba(236,253,245,.78));
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .show-hero::after {
        content: "";
        position: absolute;
        right: -80px;
        bottom: -110px;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(16,185,129,.12);
        pointer-events: none;
    }

    .show-badge {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        border-radius: 999px;
        border: 1px solid rgba(16,185,129,.18);
        background: rgba(236,253,245,.88);
        color: #047857;
        padding: .68rem 1rem;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .show-surface {
        border: 1px solid rgba(226,232,240,.88);
        background: rgba(255,255,255,.94);
        box-shadow: 0 14px 34px rgba(15,23,42,.05);
    }

    .show-stat {
        border-radius: 26px;
        border: 1px solid rgba(226,232,240,.86);
        box-shadow: 0 12px 28px rgba(15,23,42,.04);
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

    .show-action {
        border-radius: 18px;
        font-weight: 900;
        transition: background .15s ease, transform .15s ease, border-color .15s ease;
    }

    .show-action:hover {
        transform: translateY(-1px);
    }

    .show-primary {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        box-shadow: 0 12px 24px rgba(5,150,105,.16);
    }

    .show-dark {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: white;
        box-shadow: 0 12px 24px rgba(15,23,42,.13);
    }

    .show-outline {
        border: 1px solid rgba(16,185,129,.18);
        background: white;
        color: #047857;
    }

    .show-outline:hover {
        background: #ecfdf5;
    }

    .show-row {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(226,232,240,.86);
        background: #fff;
        box-shadow: 0 6px 18px rgba(15,23,42,.035);
    }

    .show-row::before {
        content: "";
        position: absolute;
        left: 0;
        top: 14px;
        bottom: 14px;
        width: 4px;
        border-radius: 999px;
        background: linear-gradient(180deg, #10b981, #f59e0b);
        opacity: .75;
    }

    .show-row.present {
        background: #f0fdf4;
        border-color: rgba(16,185,129,.24);
    }

    .show-row.present::before {
        background: #059669;
        opacity: 1;
    }

    .show-row.absent {
        background: #fff7f8;
        border-color: rgba(244,63,94,.18);
    }

    .show-row.absent::before {
        background: #e11d48;
        opacity: .9;
    }

    .detail-scroll {
        max-height: 640px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: .35rem;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .detail-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .detail-scroll::-webkit-scrollbar-track {
        background: rgba(226,232,240,.55);
        border-radius: 999px;
    }

    .detail-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }

    .filter-input {
        border: 1px solid rgba(226,232,240,.9);
        background: white;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .filter-input:focus {
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
    }

    .progress-track {
        height: 10px;
        border-radius: 999px;
        background: #f1f5f9;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #059669, #10b981, #f59e0b);
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #printArea,
        #printArea * {
            visibility: visible !important;
        }

        #printArea {
            position: absolute;
            inset: 0;
            width: 100%;
            background: white !important;
        }

        .no-print {
            display: none !important;
        }
    }

    @media (max-width: 640px) {
        .show-action:hover {
            transform: none;
        }

        .detail-scroll {
            max-height: none;
            overflow: visible;
            padding-right: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .show-page,
        .show-action {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="show-page space-y-6">

    {{-- HERO --}}
    <section class="show-hero rounded-[32px] p-5 sm:p-6 lg:p-7 no-print">
        <div class="relative z-10 grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="show-badge mb-4">
                    <i class="fa-solid fa-file-circle-check"></i>
                    Detail Presensi
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Detail Absensi Posyandu
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Rincian lengkap presensi {{ $kategoriLabel }} pada {{ $tanggal }}. Halaman ini bisa dipakai untuk verifikasi data sebelum laporan bulanan dibuat.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                @if($routeHas('kader.absensi.riwayat'))
                    <a href="{{ route('kader.absensi.riwayat') }}" class="show-action show-outline inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-arrow-left"></i>
                        Riwayat
                    </a>
                @endif

                @if($routeHas('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index', ['kategori' => $absensi->kategori]) }}" class="show-action show-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-pen-to-square"></i>
                        Update
                    </a>
                @endif

                <button type="button" onclick="window.print()" class="show-action show-dark inline-flex items-center justify-center gap-2 px-5 py-3 text-sm col-span-2 sm:col-span-1">
                    <i class="fa-solid fa-print"></i>
                    Cetak
                </button>
            </div>
        </div>
    </section>

    <div id="printArea" class="space-y-6">

        {{-- HEADER PRINT --}}
        <section class="show-surface rounded-[30px] p-5 sm:p-6">
            <div class="grid gap-5 xl:grid-cols-[1.2fr_.8fr] xl:items-center">
                <div class="flex items-start gap-4">
                    <div class="grid h-16 w-16 shrink-0 place-items-center rounded-3xl bg-emerald-50 text-emerald-700">
                        <i class="fa-solid {{ $kategoriIcon }} text-xl"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[.14em] text-emerald-700">Kode Absensi</p>
                        <h2 class="mt-1 break-all text-xl font-black text-slate-900 sm:text-2xl">
                            {{ $absensi->kode_absensi ?? '-' }}
                        </h2>

                        <p class="mt-2 text-sm font-bold leading-6 text-slate-500">
                            {{ $kategoriLabel }} • {{ $tanggal }} • Pertemuan ke-{{ $absensi->nomor_pertemuan ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Petugas</p>
                        <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $namaKader }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Periode</p>
                        <p class="mt-2 text-sm font-black text-slate-900">
                            {{ $absensi->bulan ?? '-' }}/{{ $absensi->tahun ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- STAT --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="show-stat stat-sky p-5">
                <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Kategori</p>
                <div class="mt-3 flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-50 text-sky-600">
                        <i class="fa-solid {{ $kategoriIcon }}"></i>
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-slate-900">{{ $kategoriLabel }}</h2>
                        <p class="text-xs font-bold text-slate-400">Sasaran Posyandu</p>
                    </div>
                </div>
            </div>

            <div class="show-stat stat-emerald p-5">
                <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Hadir</p>
                <h2 class="mt-3 text-3xl font-black text-emerald-700">{{ number_format($totalHadir) }}</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">{{ $persentase }}% dari peserta</p>
            </div>

            <div class="show-stat stat-rose p-5">
                <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Tidak Hadir</p>
                <h2 class="mt-3 text-3xl font-black text-rose-600">{{ number_format($totalAbsen) }}</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">Tidak hadir / belum hadir</p>
            </div>

            <div class="show-stat stat-gold p-5">
                <p class="text-xs font-black uppercase tracking-[.12em] text-slate-400">Total Peserta</p>
                <h2 class="mt-3 text-3xl font-black text-slate-900">{{ number_format($totalPasien) }}</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">Tercatat pada sesi ini</p>
            </div>
        </section>

        {{-- CONTENT --}}
        <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

            {{-- RINGKASAN --}}
            <div class="show-surface rounded-[30px] p-5 xl:col-span-4">
                <div class="mb-5">
                    <h3 class="text-lg font-black text-slate-900">Ringkasan Kehadiran</h3>
                    <p class="text-xs font-bold text-slate-400">Persentase kehadiran pada sesi ini.</p>
                </div>

                <div class="rounded-[26px] bg-slate-50 p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-black text-slate-500">Kehadiran</span>
                        <span class="text-sm font-black text-emerald-700">{{ $persentase }}%</span>
                    </div>

                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ $persentase }}%"></div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white p-4 text-center">
                            <p class="text-2xl font-black text-emerald-700">{{ number_format($totalHadir) }}</p>
                            <p class="mt-1 text-[10px] font-black uppercase text-slate-400">Hadir</p>
                        </div>

                        <div class="rounded-2xl bg-white p-4 text-center">
                            <p class="text-2xl font-black text-rose-600">{{ number_format($totalAbsen) }}</p>
                            <p class="mt-1 text-[10px] font-black uppercase text-slate-400">Tidak Hadir</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                        <span class="text-xs font-black text-slate-500">Tanggal</span>
                        <span class="text-sm font-black text-slate-900">{{ $tanggalSingkat }}</span>
                    </div>

                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                        <span class="text-xs font-black text-slate-500">Pertemuan</span>
                        <span class="text-sm font-black text-slate-900">Ke-{{ $absensi->nomor_pertemuan ?? '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                        <span class="text-xs font-black text-slate-500">Total Detail</span>
                        <span class="text-sm font-black text-slate-900">{{ number_format($details->count()) }}</span>
                    </div>
                </div>
            </div>

            {{-- DAFTAR DETAIL --}}
            <div class="show-surface rounded-[30px] p-5 xl:col-span-8">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Daftar Peserta</h3>
                        <p class="text-xs font-bold text-slate-400">
                            <span id="visibleDetailCount">{{ number_format($details->count()) }}</span>
                            data ditampilkan dari {{ number_format($details->count()) }} peserta.
                        </p>
                    </div>

                    <div class="relative no-print">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                        <input
                            type="text"
                            id="searchDetail"
                            placeholder="Cari nama, NIK, status..."
                            class="filter-input h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700 sm:w-80"
                        >
                    </div>
                </div>

                @if($details->isNotEmpty())
                    <div class="detail-scroll space-y-3">
                        @foreach($details as $index => $detail)
                            @php
                                $pasien = $detail->pasien;
                                $nama = $pasien->nama_lengkap ?? $pasien->nama ?? $detail->nama_pasien ?? 'Data sasaran';
                                $nik = $pasien->nik ?? $detail->nik_pasien ?? '-';
                                $isHadir = (bool) $detail->hadir;
                                $statusText = $isHadir ? 'Hadir' : 'Tidak Hadir';
                                $searchText = strtolower($nama . ' ' . $nik . ' ' . $statusText);
                            @endphp

                            <div class="show-row {{ $isHadir ? 'present' : 'absent' }} rounded-[22px] p-4"
                                 data-search="{{ $searchText }}">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-sm font-black {{ $isHadir ? 'text-emerald-700' : 'text-rose-600' }}">
                                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        </div>

                                        <div class="min-w-0">
                                            <h4 class="truncate text-sm font-black text-slate-800">{{ $nama }}</h4>
                                            <p class="mt-1 text-xs font-bold text-slate-400">
                                                <i class="fa-solid fa-id-card mr-1"></i>
                                                {{ $nik }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <span class="rounded-full px-3 py-1 text-[10px] font-black {{ $isHadir ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>

                                @if(!empty($detail->keterangan))
                                    <div class="mt-3 rounded-2xl bg-white/80 px-4 py-3 text-xs font-bold text-slate-500">
                                        <i class="fa-solid fa-note-sticky mr-1 text-amber-500"></i>
                                        {{ $detail->keterangan }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div id="emptyDetailSearch" class="mt-5 hidden rounded-[24px] border border-dashed border-slate-200 bg-slate-50 p-8 text-center no-print">
                        <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-white text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <p class="text-sm font-black text-slate-500">Peserta tidak ditemukan dari pencarian.</p>
                    </div>
                @else
                    <div class="rounded-[26px] border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                        <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-3xl bg-white text-slate-400">
                            <i class="fa-regular fa-folder-open text-xl"></i>
                        </div>

                        <h3 class="text-lg font-black text-slate-800">Belum ada detail peserta</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-500">
                            Detail presensi belum tersedia untuk sesi ini.
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- SESI LAIN --}}
        @if(!empty($semuaSesi) && $semuaSesi->isNotEmpty())
            <section class="show-surface rounded-[30px] p-5 no-print">
                <div class="mb-5">
                    <h3 class="text-lg font-black text-slate-900">Sesi Lainnya</h3>
                    <p class="text-xs font-bold text-slate-400">Presensi terbaru lain pada kategori yang sama.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                    @foreach($semuaSesi as $sesi)
                        @php
                            $tglSesi = !empty($sesi->tanggal_posyandu)
                                ? Carbon::parse($sesi->tanggal_posyandu)->translatedFormat('d M Y')
                                : '-';
                        @endphp

                        <a href="{{ route('kader.absensi.show', $sesi->id) }}" class="rounded-[22px] border border-slate-200 bg-white p-4 transition hover:border-emerald-200 hover:bg-emerald-50">
                            <p class="text-xs font-black uppercase tracking-[.12em] text-emerald-700">
                                Pertemuan {{ $sesi->nomor_pertemuan ?? '-' }}
                            </p>
                            <h4 class="mt-2 truncate text-sm font-black text-slate-900">
                                {{ $sesi->kode_absensi ?? 'Kode Absensi' }}
                            </h4>
                            <p class="mt-1 text-xs font-bold text-slate-400">{{ $tglSesi }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- ACTION BAWAH --}}
    <section class="show-surface rounded-[28px] p-5 no-print">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900">Detail presensi siap digunakan</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Gunakan halaman ini untuk cek ulang data sebelum dicetak atau dijadikan bahan laporan.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                @if($routeHas('kader.absensi.riwayat'))
                    <a href="{{ route('kader.absensi.riwayat') }}" class="show-action show-outline inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Kembali ke Riwayat
                    </a>
                @endif

                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}" class="show-action show-dark inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-house"></i>
                        Dashboard
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchDetail');
        const rows = Array.from(document.querySelectorAll('.show-row'));
        const emptyBox = document.getElementById('emptyDetailSearch');
        const visibleText = document.getElementById('visibleDetailCount');

        const updateVisible = () => {
            const visible = rows.filter(row => row.style.display !== 'none').length;

            if (visibleText) {
                visibleText.textContent = visible.toLocaleString('id-ID');
            }

            if (emptyBox) {
                const hasKeyword = searchInput && searchInput.value.trim() !== '';
                emptyBox.classList.toggle('hidden', visible > 0 || !hasKeyword);
            }
        };

        searchInput?.addEventListener('input', (event) => {
            const keyword = event.target.value.toLowerCase().trim();

            rows.forEach(row => {
                const text = row.dataset.search || '';
                row.style.display = text.includes(keyword) ? '' : 'none';
            });

            updateVisible();
        });

        updateVisible();
    });
</script>
@endpush