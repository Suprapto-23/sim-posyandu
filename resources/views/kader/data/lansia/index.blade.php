@extends('layouts.kader')

@section('title', 'Data Lansia')
@section('page-name', 'Data Lansia')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $search = $search ?? request('search', '');
    $statusAkun = $statusAkun ?? request('status_akun', 'semua');
    $jenisKelamin = $jenisKelamin ?? request('jenis_kelamin', 'semua');
    $kemandirian = $kemandirian ?? request('kemandirian', 'semua');

    $statusOptions = [
        'semua' => 'Semua Status',
        'terhubung' => 'Terhubung Akun',
        'belum' => 'Belum Terhubung',
    ];

    $genderOptions = [
        'semua' => 'Semua Gender',
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    $kemandirianOptions = [
        'semua' => 'Semua Kemandirian',
        'mandiri' => 'Mandiri',
        'bantuan_sebagian' => 'Bantuan Sebagian',
        'ketergantungan_penuh' => 'Ketergantungan Penuh',
    ];

    $genderLabel = fn ($value) => match($value) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => '-',
    };

    $genderClass = fn ($value) => match($value) {
        'L' => 'border-sky-100 bg-sky-50 text-sky-700',
        'P' => 'border-pink-100 bg-pink-50 text-pink-700',
        default => 'border-slate-100 bg-slate-50 text-slate-500',
    };

    $kemandirianLabel = fn ($value) => match($value) {
        'mandiri' => 'Mandiri',
        'bantuan_sebagian', 'bantuan_ringan', 'bantuan_sedang' => 'Bantuan Sebagian',
        'ketergantungan_penuh', 'ketergantungan_tinggi' => 'Ketergantungan Penuh',
        default => 'Belum Diisi',
    };

    $kemandirianClass = fn ($value) => match($value) {
        'mandiri' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
        'bantuan_sebagian', 'bantuan_ringan', 'bantuan_sedang' => 'border-amber-100 bg-amber-50 text-amber-700',
        'ketergantungan_penuh', 'ketergantungan_tinggi' => 'border-rose-100 bg-rose-50 text-rose-700',
        default => 'border-slate-100 bg-slate-50 text-slate-500',
    };

    $imtClass = function ($value) {
        if ($value === null || $value === '') {
            return 'border-slate-100 bg-slate-50 text-slate-500';
        }

        if ((float) $value < 18.5) {
            return 'border-amber-100 bg-amber-50 text-amber-700';
        }

        if ((float) $value < 25) {
            return 'border-emerald-100 bg-emerald-50 text-emerald-700';
        }

        return 'border-rose-100 bg-rose-50 text-rose-700';
    };

    $formatNumber = function ($value, $suffix = '') {
        if ($value === null || $value === '') {
            return '-';
        }

        $number = number_format((float) $value, 1, ',', '.');
        $number = rtrim(rtrim($number, '0'), ',');

        return $number . $suffix;
    };
@endphp

@push('styles')
<style>
    .lansia-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .lansia-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.12), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(245,158,11,.09), transparent 26%),
            radial-gradient(circle at 50% 100%, rgba(14,165,233,.07), transparent 32%),
            linear-gradient(135deg, #f8fffc 0%, #f8fafc 58%, #fffaf0 100%);
    }

    .glass-panel {
        border: 1px solid rgba(255,255,255,.78);
        background: rgba(255,255,255,.64);
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .hero-panel {
        border: 1px solid rgba(167,243,208,.72);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.16), transparent 32%),
            radial-gradient(circle at 88% 16%, rgba(245,158,11,.12), transparent 32%),
            linear-gradient(135deg, rgba(255,255,255,.72), rgba(236,253,245,.70));
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .input-premium {
        border: 1px solid rgba(226,232,240,.92);
        background: rgba(255,255,255,.74);
        outline: none;
        transition: all .3s ease-in-out;
    }

    .input-premium:focus {
        border-color: rgba(16,185,129,.42);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
        background: rgba(255,255,255,.90);
    }

    .mini-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        border-width: 1px;
        border-radius: 999px;
        padding: .28rem .58rem;
        font-size: 10px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .data-table-wrap {
        border: 1px solid rgba(226,232,240,.74);
        background: rgba(255,255,255,.58);
        backdrop-filter: blur(14px);
        overflow: hidden;
    }

    .data-table-scroll {
        overflow-x: auto;
    }

    .data-table-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .data-table-scroll::-webkit-scrollbar-track {
        background: rgba(241,245,249,.8);
        border-radius: 999px;
    }

    .data-table-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #10b981, #f59e0b);
        border-radius: 999px;
    }

    .data-table-body {
        max-height: 640px;
        overflow-y: auto;
    }

    .data-table-body::-webkit-scrollbar {
        width: 7px;
    }

    .data-table-body::-webkit-scrollbar-track {
        background: rgba(241,245,249,.8);
        border-radius: 999px;
    }

    .data-table-body::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }

    .table-row {
        transition: all .25s ease-in-out;
    }

    .table-row:hover {
        background: rgba(236,253,245,.42);
    }

    .metric-box {
        border: 1px solid rgba(226,232,240,.68);
        background: rgba(255,255,255,.68);
        border-radius: 14px;
        padding: 8px 10px;
        min-height: 54px;
    }

    .clamp-1 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
    }

    .clamp-2 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
</style>
@endpush

@section('content')
<div class="lansia-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[28px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-person-cane"></i>
                    Database Lansia
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Data Lansia
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Kelola data Lansia, tingkat kemandirian, pemeriksaan kesehatan dasar, riwayat penyakit, dan keluhan.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create', ['type' => 'lansia']) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-100 bg-amber-50/80 px-5 py-3 text-sm font-black text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-amber-100/80">
                        <i class="fa-solid fa-file-import"></i>
                        Import
                    </a>
                @endif

                @if($routeHas('kader.data.lansia.create'))
                    <a href="{{ route('kader.data.lansia.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-plus"></i>
                        Tambah Lansia
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div class="glass-panel rounded-[22px] p-4">
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total</p>
            <div class="mt-2 flex items-end justify-between gap-2">
                <h2 class="text-2xl font-black text-slate-900">{{ $statTotal ?? 0 }}</h2>
                <i class="fa-solid fa-users text-emerald-600"></i>
            </div>
        </div>

        <div class="glass-panel rounded-[22px] p-4">
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Laki-laki</p>
            <div class="mt-2 flex items-end justify-between gap-2">
                <h2 class="text-2xl font-black text-slate-900">{{ $statLaki ?? 0 }}</h2>
                <i class="fa-solid fa-mars text-sky-600"></i>
            </div>
        </div>

        <div class="glass-panel rounded-[22px] p-4">
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Perempuan</p>
            <div class="mt-2 flex items-end justify-between gap-2">
                <h2 class="text-2xl font-black text-slate-900">{{ $statPerempuan ?? 0 }}</h2>
                <i class="fa-solid fa-venus text-pink-600"></i>
            </div>
        </div>

        <div class="glass-panel rounded-[22px] p-4">
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Mandiri</p>
            <div class="mt-2 flex items-end justify-between gap-2">
                <h2 class="text-2xl font-black text-slate-900">{{ $statMandiri ?? 0 }}</h2>
                <i class="fa-solid fa-person-walking text-emerald-600"></i>
            </div>
        </div>

        <div class="glass-panel rounded-[22px] p-4">
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Butuh Bantuan</p>
            <div class="mt-2 flex items-end justify-between gap-2">
                <h2 class="text-2xl font-black text-slate-900">{{ $statButuhBantuan ?? 0 }}</h2>
                <i class="fa-solid fa-hand-holding-heart text-amber-600"></i>
            </div>
        </div>

        <div class="glass-panel rounded-[22px] p-4">
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Tensi</p>
            <div class="mt-2 flex items-end justify-between gap-2">
                <h2 class="text-2xl font-black text-slate-900">{{ $statTensiTercatat ?? 0 }}</h2>
                <i class="fa-solid fa-heart-pulse text-rose-600"></i>
            </div>
        </div>
    </section>

    {{-- FILTER --}}
    <section class="glass-panel rounded-[26px] p-4">
        <form method="GET" action="{{ route('kader.data.lansia.index') }}" class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_175px_215px_205px_auto]">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Cari Lansia</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="input-premium h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700"
                        placeholder="Cari nama, NIK, alamat, penyakit, keluhan, atau tensi..."
                    >
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Gender</label>
                <select name="jenis_kelamin" class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach($genderOptions as $key => $label)
                        <option value="{{ $key }}" {{ $jenisKelamin === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Kemandirian</label>
                <select name="kemandirian" class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach($kemandirianOptions as $key => $label)
                        <option value="{{ $key }}" {{ $kemandirian === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Status Akun</label>
                <select name="status_akun" class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" {{ $statusAkun === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="h-12 rounded-2xl bg-emerald-600 px-5 text-sm font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-filter mr-1"></i>
                    Filter
                </button>

                <a href="{{ route('kader.data.lansia.index') }}"
                   class="grid h-12 w-12 place-items-center rounded-2xl border border-white/70 bg-white/60 text-slate-500 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50 hover:text-emerald-700">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </section>

    {{-- TABLE --}}
    <section class="glass-panel rounded-[28px] p-4">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Daftar Lansia</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Data dibuat lebih compact dan sejajar agar mudah dibaca.
                </p>
            </div>

            <span class="w-fit rounded-full border border-emerald-100 bg-emerald-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">
                {{ $items->total() ?? 0 }} Data
            </span>
        </div>

        @if(isset($items) && $items->count())
            <form method="POST" action="{{ route('kader.data.lansia.bulk-delete') }}" id="bulkDeleteForm">
                @csrf
                @method('DELETE')

                <div class="mb-4 hidden rounded-[20px] border border-rose-100 bg-rose-50/80 p-4" id="bulkActionBar">
                    <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
                        <div>
                            <p class="text-sm font-black text-rose-700">Mode hapus massal aktif</p>
                            <p class="mt-1 text-xs font-bold text-rose-500">
                                <span id="selectedCount">0</span> data dipilih.
                            </p>
                        </div>

                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-sm font-black text-white transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-rose-700"
                                onclick="return confirm('Hapus data Lansia yang dipilih? Data yang sudah punya riwayat pelayanan tidak dapat dihapus.');">
                            <i class="fa-solid fa-trash"></i>
                            Hapus Terpilih
                        </button>
                    </div>
                </div>

                <div class="data-table-wrap rounded-[24px]">
                    <div class="data-table-scroll">
                        <table class="min-w-[1180px] w-full table-fixed">
                            <thead>
                                <tr class="border-b border-slate-200/70 bg-slate-50/50">
                                    <th class="w-[44px] px-4 py-3"></th>
                                    <th class="w-[230px] px-3 py-3 text-left text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Profil</th>
                                    <th class="w-[220px] px-3 py-3 text-left text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Identitas</th>
                                    <th class="w-[340px] px-3 py-3 text-left text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Pemeriksaan Dasar</th>
                                    <th class="w-[235px] px-3 py-3 text-left text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Riwayat dan Keluhan</th>
                                    <th class="w-[155px] px-3 py-3 text-left text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Aksi</th>
                                </tr>
                            </thead>
                        </table>

                        <div class="data-table-body">
                            <table class="min-w-[1180px] w-full table-fixed">
                                <tbody>
                                    @foreach($items as $item)
                                        @php
                                            $tanggalLahir = $item->tanggal_lahir ? Carbon::parse($item->tanggal_lahir) : null;
                                            $usiaText = '-';

                                            if ($tanggalLahir) {
                                                $diff = $tanggalLahir->diff(now('Asia/Jakarta'));
                                                $usiaText = $diff->y . ' tahun';
                                            }

                                            $akunTerhubung = filled($item->user_id);
                                            $pemeriksaan = $item->pemeriksaan_terakhir;
                                            $imtValue = $item->imt ?? null;

                                            $keluhan = $item->keluhan ?: 'Tidak ada keluhan';
                                            $penyakit = $item->penyakit_bawaan ?: 'Tidak ada riwayat';
                                        @endphp

                                        <tr class="table-row border-b border-slate-100 last:border-b-0">
                                            <td class="w-[44px] px-4 py-4 align-middle">
                                                <input
                                                    type="checkbox"
                                                    name="ids[]"
                                                    value="{{ $item->id }}"
                                                    class="bulk-checkbox h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                >
                                            </td>

                                            <td class="w-[230px] px-3 py-4 align-middle">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                                                        <span class="text-sm font-black">
                                                            {{ strtoupper(substr($item->nama_lengkap ?? 'L', 0, 1)) }}
                                                        </span>
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="mb-1 flex flex-wrap gap-1.5">
                                                            <span class="mini-badge border-emerald-100 bg-emerald-50 text-emerald-700">Lansia</span>
                                                            <span class="mini-badge {{ $genderClass($item->jenis_kelamin) }}">
                                                                {{ $genderLabel($item->jenis_kelamin) }}
                                                            </span>
                                                        </div>

                                                        <h3 class="clamp-1 text-sm font-black text-slate-900">
                                                            {{ $item->nama_lengkap }}
                                                        </h3>

                                                        <p class="mt-1 text-xs font-bold text-slate-400">
                                                            NIK {{ $item->nik ?? '-' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="w-[220px] px-3 py-4 align-middle">
                                                <div class="mb-2 flex flex-wrap gap-1.5">
                                                    <span class="mini-badge {{ $kemandirianClass($item->tingkat_kemandirian ?? null) }}">
                                                        {{ $kemandirianLabel($item->tingkat_kemandirian ?? null) }}
                                                    </span>

                                                    @if($akunTerhubung)
                                                        <span class="mini-badge border-emerald-100 bg-emerald-50 text-emerald-700">Akun</span>
                                                    @else
                                                        <span class="mini-badge border-amber-100 bg-amber-50 text-amber-700">Belum Sinkron</span>
                                                    @endif
                                                </div>

                                                <p class="text-sm font-black text-slate-900">{{ $usiaText }}</p>

                                                <p class="mt-1 clamp-1 text-xs font-bold text-slate-400">
                                                    {{ $item->tempat_lahir ?? '-' }},
                                                    {{ $tanggalLahir ? $tanggalLahir->translatedFormat('d M Y') : '-' }}
                                                </p>

                                                <p class="mt-1 clamp-1 text-xs font-bold text-slate-400">
                                                    {{ $item->alamat ?? '-' }}
                                                </p>
                                            </td>

                                            <td class="w-[340px] px-3 py-4 align-middle">
                                                <div class="mb-2 flex flex-wrap gap-1.5">
                                                    <span class="mini-badge border-sky-100 bg-sky-50 text-sky-700">
                                                        Tensi {{ $item->tekanan_darah ?: '-' }}
                                                    </span>

                                                    <span class="mini-badge {{ $imtClass($imtValue) }}">
                                                        IMT {{ $imtValue ?: '-' }}
                                                    </span>
                                                </div>

                                                <div class="grid grid-cols-4 gap-2">
                                                    <div class="metric-box">
                                                        <p class="text-[9px] font-black uppercase tracking-[.08em] text-slate-400">BB</p>
                                                        <p class="mt-1 text-xs font-black text-slate-900">{{ $formatNumber($item->berat_badan ?? null, ' kg') }}</p>
                                                    </div>

                                                    <div class="metric-box">
                                                        <p class="text-[9px] font-black uppercase tracking-[.08em] text-slate-400">TB</p>
                                                        <p class="mt-1 text-xs font-black text-slate-900">{{ $formatNumber($item->tinggi_badan ?? null, ' cm') }}</p>
                                                    </div>

                                                    <div class="metric-box">
                                                        <p class="text-[9px] font-black uppercase tracking-[.08em] text-slate-400">LP</p>
                                                        <p class="mt-1 text-xs font-black text-slate-900">{{ $formatNumber($item->lingkar_perut ?? null, ' cm') }}</p>
                                                    </div>

                                                    <div class="metric-box">
                                                        <p class="text-[9px] font-black uppercase tracking-[.08em] text-slate-400">Gula</p>
                                                        <p class="mt-1 text-xs font-black text-slate-900">{{ $formatNumber($item->gula_darah ?? null) }}</p>
                                                    </div>

                                                    <div class="metric-box">
                                                        <p class="text-[9px] font-black uppercase tracking-[.08em] text-slate-400">Kol</p>
                                                        <p class="mt-1 text-xs font-black text-slate-900">{{ $formatNumber($item->kolesterol ?? null) }}</p>
                                                    </div>

                                                    <div class="metric-box">
                                                        <p class="text-[9px] font-black uppercase tracking-[.08em] text-slate-400">AU</p>
                                                        <p class="mt-1 text-xs font-black text-slate-900">{{ $formatNumber($item->asam_urat ?? null) }}</p>
                                                    </div>

                                                    <div class="metric-box col-span-2">
                                                        <p class="text-[9px] font-black uppercase tracking-[.08em] text-slate-400">Berkala</p>
                                                        <p class="mt-1 text-xs font-black {{ $pemeriksaan ? 'text-emerald-600' : 'text-slate-400' }}">
                                                            {{ $pemeriksaan ? 'Ada pemeriksaan' : 'Belum ada' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="w-[235px] px-3 py-4 align-middle">
                                                <div class="rounded-2xl bg-white/65 p-3">
                                                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Riwayat</p>
                                                    <p class="mt-1 clamp-2 text-sm font-black leading-5 text-slate-900">
                                                        {{ $penyakit }}
                                                    </p>
                                                </div>

                                                <div class="mt-2 rounded-2xl bg-white/65 p-3">
                                                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Keluhan</p>
                                                    <p class="mt-1 clamp-2 text-xs font-bold leading-5 text-slate-500">
                                                        {{ $keluhan }}
                                                    </p>
                                                </div>
                                            </td>

                                            <td class="w-[155px] px-3 py-4 align-middle">
                                                <div class="flex flex-col gap-2">
                                                    @if(!$akunTerhubung && $routeHas('kader.data.lansia.sync'))
                                                        <form method="POST" action="{{ route('kader.data.lansia.sync', $item->id) }}">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-amber-100 bg-amber-50/80 px-3 py-2 text-xs font-black text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-amber-100/80">
                                                                <i class="fa-solid fa-rotate"></i>
                                                                Sinkron
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <div class="grid grid-cols-3 gap-2">
                                                        @if($routeHas('kader.data.lansia.show'))
                                                            <a href="{{ route('kader.data.lansia.show', $item->id) }}"
                                                               class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-3 py-2 text-xs font-black text-white transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </a>
                                                        @endif

                                                        @if($routeHas('kader.data.lansia.edit'))
                                                            <a href="{{ route('kader.data.lansia.edit', $item->id) }}"
                                                               class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-2 text-xs font-black text-slate-600 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-slate-100">
                                                                <i class="fa-solid fa-pen"></i>
                                                            </a>
                                                        @endif

                                                        @if($routeHas('kader.data.lansia.destroy'))
                                                            <form method="POST"
                                                                  action="{{ route('kader.data.lansia.destroy', $item->id) }}"
                                                                  onsubmit="return confirm('Hapus data Lansia ini? Data yang sudah memiliki riwayat pelayanan tidak dapat dihapus.');">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit"
                                                                        class="inline-flex w-full items-center justify-center rounded-2xl border border-rose-100 bg-rose-50/80 px-3 py-2 text-xs font-black text-rose-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-rose-100/80">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

            @if($items->hasPages())
                <div class="mt-5">
                    {{ $items->links() }}
                </div>
            @endif
        @else
            <div class="rounded-[26px] border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white/60 text-slate-400 backdrop-blur-md">
                    <i class="fa-solid fa-person-cane text-xl"></i>
                </div>

                <h3 class="mt-4 text-lg font-black text-slate-900">Data Lansia Kosong</h3>

                <p class="mx-auto mt-2 max-w-md text-sm font-bold leading-6 text-slate-400">
                    Belum ada data Lansia yang cocok dengan filter saat ini. Tambahkan manual atau gunakan import Excel.
                </p>
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    const bar = document.getElementById('bulkActionBar');
    const count = document.getElementById('selectedCount');

    const updateBulkBar = () => {
        const selected = document.querySelectorAll('.bulk-checkbox:checked').length;

        if (!bar || !count) {
            return;
        }

        count.textContent = selected;
        bar.classList.toggle('hidden', selected === 0);
    };

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkBar);
    });

    updateBulkBar();
});
</script>
@endpush