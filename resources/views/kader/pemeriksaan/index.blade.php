@extends('layouts.kader')

@section('title', 'Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $kategori = $kategori ?? request('kategori', '');
    $status = $status ?? request('status', '');
    $search = $search ?? request('search', '');

    $kategoriMenus = [
        '' => ['label' => 'Semua Kategori', 'icon' => 'fa-layer-group'],
        'balita' => ['label' => 'Balita / Anak', 'icon' => 'fa-child-reaching'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane'],
    ];

    $statusMenus = [
        '' => ['label' => 'Semua Status'],
        'pending' => ['label' => 'Menunggu Review'],
        'verified' => ['label' => 'Sudah Ditinjau'],
        'ditolak' => ['label' => 'Perlu Perbaikan'],
    ];

    $labelKategori = fn($value) => match ($value) {
        'balita' => 'Balita / Anak',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        default => 'Sasaran',
    };

    $iconKategori = fn($value) => match ($value) {
        'balita' => 'fa-child-reaching',
        'remaja' => 'fa-user-graduate',
        'lansia' => 'fa-person-cane',
        default => 'fa-users',
    };

    $statusInfo = function ($value) {
        $value = strtolower($value ?? 'pending');

        return match (true) {
            in_array($value, ['verified', 'terverifikasi', 'approved', 'valid', 'sudah_ditinjau']) => [
                'label' => 'Sudah Ditinjau',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'dot' => 'bg-emerald-500',
            ],
            in_array($value, ['ditolak', 'rejected', 'direvisi', 'perlu_perbaikan']) => [
                'label' => 'Perlu Perbaikan',
                'class' => 'bg-rose-50 text-rose-700 border-rose-100',
                'dot' => 'bg-rose-500',
            ],
            default => [
                'label' => 'Menunggu Review',
                'class' => 'bg-amber-50 text-amber-700 border-amber-100',
                'dot' => 'bg-amber-500',
            ],
        };
    };

    $items = method_exists($pemeriksaans, 'getCollection') ? $pemeriksaans->getCollection() : collect($pemeriksaans);

    $totalData = method_exists($pemeriksaans, 'total') ? $pemeriksaans->total() : $items->count();

    $countPending = $items
        ->filter(
            fn($x) => in_array(strtolower($x->status_verifikasi ?? 'pending'), [
                'pending',
                'menunggu',
                'menunggu_review',
            ]),
        )
        ->count();

    $countReviewed = $items
        ->filter(
            fn($x) => in_array(strtolower($x->status_verifikasi ?? ''), [
                'verified',
                'terverifikasi',
                'approved',
                'valid',
                'sudah_ditinjau',
            ]),
        )
        ->count();

    $countNeedFix = $items
        ->filter(
            fn($x) => in_array(strtolower($x->status_verifikasi ?? ''), [
                'ditolak',
                'rejected',
                'direvisi',
                'perlu_perbaikan',
            ]),
        )
        ->count();

    $routeHas = fn($name) => Route::has($name);
@endphp

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

        .ukur-page {
            position: relative;
            isolation: isolate;
            font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
            animation: pageIn .18s ease-out both;
        }

        .ukur-page::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                radial-gradient(circle at 8% 6%, rgba(16, 185, 129, .12), transparent 26%),
                radial-gradient(circle at 92% 10%, rgba(245, 158, 11, .10), transparent 24%),
                linear-gradient(135deg, #f7fffc 0%, #f8fafc 55%, #fffaf1 100%);
        }

        @keyframes pageIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .ukur-hero {
            border: 1px solid rgba(167, 243, 208, .65);
            background:
                radial-gradient(circle at 12% 18%, rgba(16, 185, 129, .14), transparent 28%),
                radial-gradient(circle at 90% 16%, rgba(245, 158, 11, .12), transparent 30%),
                linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(236, 253, 245, .78));
            box-shadow: 0 14px 34px rgba(15, 23, 42, .055);
        }

        .panel {
            border: 1px solid rgba(226, 232, 240, .88);
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 10px 28px rgba(15, 23, 42, .045);
        }

        .badge-title {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border-radius: 999px;
            border: 1px solid rgba(16, 185, 129, .18);
            background: rgba(236, 253, 245, .88);
            color: #047857;
            padding: .55rem .85rem;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .btn-soft {
            border-radius: 16px;
            font-weight: 900;
            transition: background .15s ease, transform .15s ease, border-color .15s ease;
        }

        .btn-soft:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            color: white;
            background: linear-gradient(135deg, #059669, #10b981);
            box-shadow: 0 10px 20px rgba(5, 150, 105, .14);
        }

        .btn-dark {
            color: white;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            box-shadow: 0 10px 20px rgba(15, 23, 42, .12);
        }

        .btn-outline {
            border: 1px solid rgba(16, 185, 129, .18);
            color: #047857;
            background: white;
        }

        .btn-outline:hover {
            background: #ecfdf5;
        }

        .input-soft {
            border: 1px solid rgba(226, 232, 240, .9);
            background: white;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .input-soft:focus {
            border-color: rgba(16, 185, 129, .28);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, .08);
        }

        .stat-card {
            border: 1px solid rgba(226, 232, 240, .85);
            background: white;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
        }

        .stat-sky {
            background: linear-gradient(145deg, #fff, #f0f9ff);
            border-color: rgba(14, 165, 233, .14);
        }

        .stat-amber {
            background: linear-gradient(145deg, #fff, #fff8eb);
            border-color: rgba(245, 158, 11, .16);
        }

        .stat-emerald {
            background: linear-gradient(145deg, #fff, #ecfdf5);
            border-color: rgba(16, 185, 129, .18);
        }

        .stat-rose {
            background: linear-gradient(145deg, #fff, #fff1f2);
            border-color: rgba(244, 63, 94, .14);
        }

        .table-wrap {
            max-height: min(640px, calc(100vh - 330px));
            overflow-y: auto;
            overflow-x: auto;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
        }

        .table-wrap::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-wrap::-webkit-scrollbar-track {
            background: rgba(226, 232, 240, .55);
            border-radius: 999px;
        }

        .table-wrap::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #10b981, #f59e0b);
            border-radius: 999px;
        }

        .ukur-table {
            width: 100%;
            min-width: 980px;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .ukur-table thead th {
            padding: 0 14px 6px;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #94a3b8;
            text-align: left;
            white-space: nowrap;
        }

        .ukur-table tbody tr {
            background: white;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .035);
        }

        .ukur-table tbody td {
            padding: 14px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .ukur-table tbody td:first-child {
            border-left: 1px solid #e2e8f0;
            border-radius: 20px 0 0 20px;
            position: relative;
        }

        .ukur-table tbody td:first-child::before {
            content: "";
            position: absolute;
            left: 0;
            top: 14px;
            bottom: 14px;
            width: 4px;
            border-radius: 999px;
            background: linear-gradient(180deg, #10b981, #f59e0b);
        }

        .ukur-table tbody td:last-child {
            border-right: 1px solid #e2e8f0;
            border-radius: 0 20px 20px 0;
        }

        .mini-box {
            min-width: 74px;
            border-radius: 14px;
            background: #f8fafc;
            padding: 9px 10px;
        }

        @media (max-width: 640px) {
            .btn-soft:hover {
                transform: none;
            }

            .table-wrap {
                max-height: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .ukur-page,
            .btn-soft {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="ukur-page space-y-5">

        {{-- HERO COMPACT --}}
        <section class="ukur-hero rounded-[28px] p-5 sm:p-6">
            <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <div class="badge-title mb-3">
                        <i class="fa-solid fa-weight-scale"></i>
                        Pengukuran Fisik
                    </div>

                    <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                        Log Pengukuran Fisik
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                        Catatan pengukuran awal dari Kader untuk ditinjau Bidan sebagai dasar pemeriksaan lanjutan.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:flex">
                    @if ($routeHas('kader.pemeriksaan.create'))
                        <a href="{{ route('kader.pemeriksaan.create') }}"
                            class="btn-soft btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                            <i class="fa-solid fa-plus"></i>
                            Input Baru
                        </a>
                    @endif

                    @if ($routeHas('kader.dashboard'))
                        <a href="{{ route('kader.dashboard') }}"
                            class="btn-soft btn-dark inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                            <i class="fa-solid fa-chart-simple"></i>
                            Dashboard
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- FILTER COMPACT --}}
        <section class="panel rounded-[26px] p-4">
            <form method="GET" action="{{ route('kader.pemeriksaan.index') }}"
                class="grid grid-cols-1 gap-3 xl:grid-cols-[1.4fr_1fr_1fr_auto] xl:items-center">
                <div class="relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau NIK..."
                        class="input-soft h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700">
                </div>

                <select name="kategori" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach ($kategoriMenus as $key => $item)
                        <option value="{{ $key }}" {{ (string) $kategori === (string) $key ? 'selected' : '' }}>
                            {{ $item['label'] }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach ($statusMenus as $key => $item)
                        <option value="{{ $key }}" {{ (string) $status === (string) $key ? 'selected' : '' }}>
                            {{ $item['label'] }}
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-3">
                    <button type="submit"
                        class="btn-soft btn-primary inline-flex h-12 flex-1 items-center justify-center gap-2 px-5 text-sm xl:flex-none">
                        <i class="fa-solid fa-filter"></i>
                        Filter
                    </button>

                    @if (request('search') || request('kategori') || request('status'))
                        <a href="{{ route('kader.pemeriksaan.index') }}"
                            class="btn-soft btn-outline inline-flex h-12 items-center justify-center gap-2 px-5 text-sm">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- STAT COMPACT --}}
        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            <div class="stat-card stat-sky rounded-[22px] p-4">
                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Total</p>
                <h2 class="mt-2 text-2xl font-black text-slate-900">{{ number_format($totalData) }}</h2>
            </div>

            <div class="stat-card stat-amber rounded-[22px] p-4">
                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Menunggu</p>
                <h2 class="mt-2 text-2xl font-black text-amber-600">{{ number_format($countPending) }}</h2>
            </div>

            <div class="stat-card stat-emerald rounded-[22px] p-4">
                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Ditinjau</p>
                <h2 class="mt-2 text-2xl font-black text-emerald-700">{{ number_format($countReviewed) }}</h2>
            </div>

            <div class="stat-card stat-rose rounded-[22px] p-4">
                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Perbaikan</p>
                <h2 class="mt-2 text-2xl font-black text-rose-600">{{ number_format($countNeedFix) }}</h2>
            </div>
        </section>

        {{-- TABLE LIST --}}
        <section class="panel rounded-[28px] p-4 sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Daftar Pengukuran</h3>
                    <p class="text-xs font-bold text-slate-400">
                        {{ number_format($totalData) }} data sesuai filter aktif.
                    </p>
                </div>

                <span
                    class="hidden rounded-full bg-emerald-50 px-4 py-2 text-[10px] font-black uppercase tracking-[.12em] text-emerald-700 sm:inline-flex">
                    Compact View
                </span>
            </div>

            @if ($pemeriksaans->isNotEmpty())
                <div class="table-wrap">
                    <table class="ukur-table">
                        <thead>
                            <tr>
                                <th>Warga</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Berat</th>
                                <th>Tinggi</th>
                                <th>IMT</th>
                                <th>Tensi</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pemeriksaans as $pem)
                                @php
                                    $pasien = $pem->kunjungan->pasien ?? null;
                                    $nama = $pasien->nama_lengkap ?? ($pem->nama_pasien ?? 'Data sasaran');
                                    $nik = $pasien->nik ?? ($pem->nik_pasien ?? '-');

                                    $tanggalItem = !empty($pem->tanggal_periksa)
                                        ? Carbon::parse($pem->tanggal_periksa)->translatedFormat('d M Y')
                                        : '-';

                                    $statusRaw = strtolower($pem->status_verifikasi ?? 'pending');
                                    $statusData = $statusInfo($statusRaw);

                                    $isReviewed = in_array($statusRaw, [
                                        'verified',
                                        'terverifikasi',
                                        'approved',
                                        'valid',
                                        'sudah_ditinjau',
                                    ]);

                                    $needFix = in_array($statusRaw, [
                                        'ditolak',
                                        'rejected',
                                        'direvisi',
                                        'perlu_perbaikan',
                                    ]);

                                    $isLocked = $isReviewed;

                                    $catatanBidan =
                                        $pem->catatan_validasi ??
                                        ($pem->catatan_bidan ?? ($pem->catatan_review ?? null));
                                @endphp

                                <tr>
                                    <td>
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div
                                                class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                                                <i class="fa-solid {{ $iconKategori($pem->kategori_pasien) }}"></i>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                                    <span
                                                        class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-[.1em] text-emerald-700">
                                                        {{ $labelKategori($pem->kategori_pasien) }}
                                                    </span>
                                                </div>

                                                <h4 class="truncate text-sm font-black text-slate-900">{{ $nama }}
                                                </h4>
                                                <p class="mt-1 text-xs font-bold text-slate-400">
                                                    <i class="fa-solid fa-id-card mr-1"></i>
                                                    {{ $nik }}
                                                </p>
                                            </div>
                                        </div>

                                        @if ($needFix && !empty($catatanBidan))
                                            <div
                                                class="mt-3 rounded-2xl border border-rose-100 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700">
                                                <div class="mb-1 flex items-center gap-2 font-black">
                                                    <i class="fa-solid fa-note-sticky"></i>
                                                    Catatan Bidan
                                                </div>
                                                <p class="leading-5">{{ $catatanBidan }}</p>
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[10px] font-black uppercase tracking-[.08em] {{ $statusData['class'] }}">
                                            <span class="h-2 w-2 rounded-full {{ $statusData['dot'] }}"></span>
                                            {{ $statusData['label'] }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="text-sm font-black text-slate-700">{{ $tanggalItem }}</span>
                                    </td>

                                    <td>
                                        <div class="mini-box">
                                            <p class="text-[9px] font-black uppercase text-slate-400">Berat</p>
                                            <p class="mt-1 text-sm font-black text-slate-900">
                                                {{ $pem->berat_badan ?? '-' }} kg</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="mini-box">
                                            <p class="text-[9px] font-black uppercase text-slate-400">Tinggi</p>
                                            <p class="mt-1 text-sm font-black text-slate-900">
                                                {{ $pem->tinggi_badan ?? '-' }} cm</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="mini-box">
                                            <p class="text-[9px] font-black uppercase text-slate-400">IMT</p>
                                            <p class="mt-1 text-sm font-black text-slate-900">{{ $pem->imt ?? '-' }}</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="mini-box">
                                            <p class="text-[9px] font-black uppercase text-slate-400">Tensi</p>
                                            <p class="mt-1 text-sm font-black text-slate-900">
                                                {{ $pem->tekanan_darah ?? '-' }}</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="flex justify-end gap-2">
                                            @if ($routeHas('kader.pemeriksaan.show'))
                                                <a href="{{ route('kader.pemeriksaan.show', $pem->id) }}"
                                                    class="btn-soft btn-primary inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs">
                                                    <i class="fa-solid fa-eye"></i>
                                                    Detail
                                                </a>
                                            @endif

                                            @if (!$isLocked && $routeHas('kader.pemeriksaan.edit'))
                                                <a href="{{ route('kader.pemeriksaan.edit', $pem->id) }}"
                                                    class="btn-soft btn-outline inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs">
                                                    <i
                                                        class="fa-solid {{ $needFix ? 'fa-screwdriver-wrench' : 'fa-pen-to-square' }}"></i>
                                                    {{ $needFix ? 'Perbaiki' : 'Edit' }}
                                                </a>
                                            @endif

                                            @if ($isLocked)
                                                <span
                                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-100 px-4 py-2.5 text-xs font-black text-slate-500">
                                                    <i class="fa-solid fa-lock"></i>
                                                    Sudah Ditinjau
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($pemeriksaans->hasPages())
                    <div class="mt-5">
                        {{ $pemeriksaans->links() }}
                    </div>
                @endif
            @else
                <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                    <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-3xl bg-white text-slate-400">
                        <i class="fa-regular fa-folder-open text-xl"></i>
                    </div>

                    <h3 class="text-lg font-black text-slate-800">Belum ada data pengukuran</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-500">
                        Data belum tersedia atau tidak cocok dengan filter yang dipilih.
                    </p>

                    @if ($routeHas('kader.pemeriksaan.create'))
                        <a href="{{ route('kader.pemeriksaan.create') }}"
                            class="btn-soft btn-primary mt-5 inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                            <i class="fa-solid fa-plus"></i>
                            Input Pengukuran
                        </a>
                    @endif
                </div>
            @endif
        </section>
    </div>
@endsection
