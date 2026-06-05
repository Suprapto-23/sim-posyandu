@extends('layouts.kader')

@section('title', 'Data Lansia')
@section('page-name', 'Data Lansia')
@section('page-title', 'Data Lansia')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $search = trim((string) ($search ?? request('search', '')));
    $statusAkun = $statusAkun ?? request('status_akun', 'semua');
    $jenisKelamin = $jenisKelamin ?? request('jenis_kelamin', 'semua');
    $kemandirian = $kemandirian ?? request('kemandirian', 'semua');

    $indexRoute = $routeHas('kader.data.lansia.index')
        ? route('kader.data.lansia.index')
        : url('/kader/data/lansia');

    $createRoute = $routeHas('kader.data.lansia.create')
        ? route('kader.data.lansia.create')
        : url('/kader/data/lansia/create');

    $bulkDeleteRoute = $routeHas('kader.data.lansia.bulk-delete')
        ? route('kader.data.lansia.bulk-delete')
        : url('/kader/data/lansia/bulk-delete');

    $templateRoute = $routeHas('kader.import.template')
        ? route('kader.import.template', ['type' => 'lansia'])
        : null;

    $importRoute = $routeHas('kader.import.index')
        ? route('kader.import.index', ['type' => 'lansia'])
        : null;

    $statusOptions = [
        'semua' => 'Semua Status',
        'terhubung' => 'Terhubung',
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

    $totalData = method_exists($items, 'total') ? $items->total() : $items->count();

    $statTotal = $statTotal ?? $totalData;
    $statLaki = $statLaki ?? 0;
    $statPerempuan = $statPerempuan ?? 0;
    $statTerhubung = $statTerhubung ?? 0;
    $statBelumTerhubung = $statBelumTerhubung ?? 0;
    $statBulanIni = $statBulanIni ?? 0;
    $statMandiri = $statMandiri ?? 0;
    $statButuhBantuan = $statButuhBantuan ?? 0;
    $statTensiTercatat = $statTensiTercatat ?? 0;

    $rangeText = method_exists($items, 'firstItem')
        ? 'Menampilkan ' . (($items->firstItem() ?? 0)) . ' sampai ' . (($items->lastItem() ?? 0)) . ' dari ' . $items->total() . ' data'
        : 'Menampilkan ' . $items->count() . ' data';

    $formatDate = function ($value, $format = 'd M Y') {
        return $value ? Carbon::parse($value)->translatedFormat($format) : '-';
    };

    $ageText = function ($lansia) {
        if (! $lansia->tanggal_lahir) {
            return '-';
        }

        $birth = Carbon::parse($lansia->tanggal_lahir);
        $diff = $birth->diff(now());

        return $diff->y . ' tahun ' . $diff->m . ' bulan';
    };

    $initial = fn ($name) => Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'L';

    $genderLabel = fn ($gender) => match ($gender) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => '-',
    };

    $genderClass = fn ($gender) => match ($gender) {
        'L' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'P' => 'bg-rose-50 text-rose-700 ring-rose-200',
        default => 'bg-slate-50 text-slate-600 ring-slate-200',
    };

    $kemandirianLabel = fn ($value) => match ($value) {
        'mandiri' => 'Mandiri',
        'bantuan_sebagian' => 'Bantuan Sebagian',
        'ketergantungan_penuh' => 'Ketergantungan Penuh',
        default => 'Belum Diisi',
    };

    $kemandirianClass = fn ($value) => match ($value) {
        'mandiri' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'bantuan_sebagian' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'ketergantungan_penuh' => 'bg-rose-50 text-rose-700 ring-rose-200',
        default => 'bg-slate-50 text-slate-600 ring-slate-200',
    };

    $isConnected = fn ($lansia) => filled($lansia->user_id ?? null);

    $accountLabel = fn ($lansia) => $isConnected($lansia) ? 'Terhubung' : 'Belum Terhubung';

    $accountClass = fn ($lansia) => $isConnected($lansia)
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
        : 'bg-amber-50 text-amber-700 ring-amber-200';

    $numberValue = function ($value, $unit = '') {
        if (blank($value)) {
            return '-';
        }

        $value = rtrim(rtrim((string) $value, '0'), '.');

        return trim($value . ' ' . $unit);
    };
@endphp

@push('styles')
<style>
    .nexus-bg {
        background:
            radial-gradient(circle at 8% 8%, rgba(16, 185, 129, 0.16), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(245, 158, 11, 0.14), transparent 26%),
            radial-gradient(circle at 80% 82%, rgba(14, 165, 233, 0.12), transparent 30%),
            linear-gradient(135deg, #f8fafc 0%, #ecfdf5 42%, #eff6ff 100%);
    }

    .nexus-bg::before {
        content: "";
        position: fixed;
        inset: 0;
        pointer-events: none;
        background-image:
            linear-gradient(rgba(15, 23, 42, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, 0.035) 1px, transparent 1px);
        background-size: 34px 34px;
        mask-image: linear-gradient(to bottom, black, transparent 85%);
    }

    .nexus-glass {
        border: 1px solid rgba(255, 255, 255, 0.72);
        background: rgba(255, 255, 255, 0.76);
        backdrop-filter: blur(22px);
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
    }

    .stat-card {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 55px rgba(15, 23, 42, 0.07);
    }

    .stat-card::after {
        content: "";
        position: absolute;
        right: -34px;
        top: -34px;
        height: 120px;
        width: 120px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.42);
    }

    .data-row {
        display: grid;
        grid-template-columns: 36px minmax(250px, 1.1fr) minmax(150px, .65fr) minmax(210px, .9fr) minmax(180px, .75fr) 148px;
        gap: 14px;
        align-items: center;
    }

    .action-box {
        width: 148px;
        min-width: 148px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
    }

    .row-action {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        border: 1px solid transparent;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
        transition: transform .18s ease, background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .row-action:hover {
        transform: translateY(-1px);
    }

    .action-sync {
        grid-column: span 2;
        background: #fffbeb;
        border-color: #fcd34d;
        color: #92400e;
    }

    .action-detail {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }

    .action-edit {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .action-delete {
        grid-column: span 2;
        background: #fff1f2;
        border-color: #fecdd3;
        color: #be123c;
    }

    .live-loading {
        opacity: .55;
        pointer-events: none;
        transition: opacity .18s ease;
    }

    .nexus-modal {
        opacity: 0;
        pointer-events: none;
        transition: opacity .22s ease;
    }

    .nexus-modal.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .nexus-modal-card {
        transform: translateY(16px) scale(.96);
        opacity: 0;
        transition: transform .24s ease, opacity .24s ease;
    }

    .nexus-modal.is-open .nexus-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    @media (max-width: 1536px) {
        .data-row {
            grid-template-columns: 36px minmax(230px, 1fr) minmax(150px, .7fr) minmax(210px, .9fr) 148px;
        }

        .col-health {
            display: none;
        }
    }

    @media (max-width: 1280px) {
        .data-row {
            grid-template-columns: 34px minmax(0, 1fr);
            align-items: start;
        }

        .mobile-stack {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .action-box {
            width: 100%;
            min-width: 0;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .action-sync,
        .action-delete {
            grid-column: auto;
        }
    }

    @media (max-width: 640px) {
        .action-box {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .action-sync,
        .action-delete {
            grid-column: span 2;
        }
    }
</style>
@endpush

@section('content')
<div class="nexus-bg relative min-h-[calc(100vh-96px)] px-4 py-6 sm:px-6 lg:px-8">
    <div class="relative z-10 mx-auto max-w-7xl space-y-6">

        <section class="relative overflow-hidden rounded-[34px] bg-gradient-to-br from-slate-950 via-emerald-950 to-teal-800 shadow-[0_30px_90px_rgba(15,23,42,0.16)]">
            <div class="absolute right-0 top-0 h-44 w-44 rounded-bl-[90px] bg-white/10"></div>
            <div class="absolute -bottom-20 right-40 h-40 w-40 rounded-full bg-amber-300/10 blur-2xl"></div>

            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-300/25 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[0.22em] text-emerald-100 backdrop-blur-xl">
                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            Master Data Sasaran
                        </div>

                        <h1 class="mt-5 text-4xl font-black tracking-tight text-white sm:text-5xl">
                            Data Lansia
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold leading-7 text-emerald-50/80 sm:text-base">
                            Kelola data Lansia, pemeriksaan dasar, tingkat kemandirian, dan indikator kesehatan rutin Posyandu.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:w-[460px]">
                        @if($templateRoute)
                            <a href="{{ $templateRoute }}"
                               class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 text-sm font-black text-white backdrop-blur-xl transition hover:bg-white/15">
                                Template Excel
                            </a>
                        @endif

                        @if($importRoute)
                            <a href="{{ $importRoute }}"
                               class="rounded-2xl border border-emerald-300/25 bg-emerald-300/15 px-4 py-4 text-sm font-black text-emerald-50 backdrop-blur-xl transition hover:bg-emerald-300/20">
                                Import Data
                            </a>
                        @endif

                        <a href="{{ $createRoute }}"
                           class="rounded-2xl bg-emerald-400 px-4 py-4 text-center text-sm font-black text-emerald-950 shadow-[0_18px_40px_rgba(52,211,153,0.24)] transition hover:bg-emerald-300">
                            Tambah Lansia
                        </a>
                    </div>
                </div>
            </div>
        </section>

        @if(session('success') || session('error') || session('warning'))
            @php
                $flashType = session('error') ? 'error' : (session('warning') ? 'warning' : 'success');
                $flashText = session('error') ?: (session('warning') ?: session('success'));

                $flashClass = match ($flashType) {
                    'error' => 'border-rose-200 bg-rose-50/90 text-rose-800',
                    'warning' => 'border-amber-200 bg-amber-50/90 text-amber-800',
                    default => 'border-emerald-200 bg-emerald-50/90 text-emerald-800',
                };

                $flashTitle = match ($flashType) {
                    'error' => 'Aksi gagal',
                    'warning' => 'Perhatian',
                    default => 'Berhasil',
                };
            @endphp

            <div class="rounded-[24px] border px-5 py-4 shadow-sm backdrop-blur-xl {{ $flashClass }}">
                <p class="text-sm font-black">{{ $flashTitle }}</p>
                <p class="mt-1 text-sm font-semibold leading-6">{{ $flashText }}</p>
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="stat-card bg-white/72 p-5">
                <p class="relative text-xs font-black uppercase tracking-[0.18em] text-slate-400">Total Lansia</p>
                <p class="relative mt-3 text-4xl font-black text-slate-950">{{ number_format($statTotal) }}</p>
                <p class="relative mt-1 text-sm font-bold text-slate-500">Seluruh data sasaran</p>
            </div>

            <div class="stat-card bg-emerald-100/75 p-5">
                <p class="relative text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Mandiri</p>
                <p class="relative mt-3 text-4xl font-black text-emerald-950">{{ number_format($statMandiri) }}</p>
                <p class="relative mt-1 text-sm font-bold text-emerald-800/70">Tingkat kemandirian</p>
            </div>

            <div class="stat-card bg-amber-100/80 p-5">
                <p class="relative text-xs font-black uppercase tracking-[0.18em] text-amber-700">Butuh Bantuan</p>
                <p class="relative mt-3 text-4xl font-black text-amber-950">{{ number_format($statButuhBantuan) }}</p>
                <p class="relative mt-1 text-sm font-bold text-amber-800/70">Perlu perhatian</p>
            </div>

            <div class="stat-card bg-cyan-100/75 p-5">
                <p class="relative text-xs font-black uppercase tracking-[0.18em] text-cyan-700">Tensi Tercatat</p>
                <p class="relative mt-3 text-4xl font-black text-cyan-950">{{ number_format($statTensiTercatat) }}</p>
                <p class="relative mt-1 text-sm font-bold text-cyan-800/70">Data tekanan darah</p>
            </div>

            <div class="stat-card bg-rose-100/75 p-5">
                <p class="relative text-xs font-black uppercase tracking-[0.18em] text-rose-700">Belum Terhubung</p>
                <p class="relative mt-3 text-4xl font-black text-rose-950">{{ number_format($statBelumTerhubung) }}</p>
                <p class="relative mt-1 text-sm font-bold text-rose-800/70">Perlu sinkron akun</p>
            </div>
        </section>

        <section class="nexus-glass rounded-[30px] p-4 sm:p-5">
            <form id="liveSearchForm"
                  action="{{ $indexRoute }}"
                  method="GET"
                  class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_190px_220px_250px_auto] xl:items-end">
                <div>
                    <label for="liveSearchInput" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                        Cari Lansia
                    </label>

                    <div class="relative">
                        <input type="text"
                               id="liveSearchInput"
                               name="search"
                               value="{{ $search }}"
                               autocomplete="off"
                               placeholder="Ketik nama, NIK, alamat, penyakit, atau tensi..."
                               class="h-14 w-full rounded-[22px] border border-emerald-100 bg-emerald-50/70 px-5 pr-24 text-sm font-extrabold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">

                        <div id="liveSearchState"
                             class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-2xl bg-slate-950 px-3 py-2 text-[11px] font-black text-white">
                            Mencari
                        </div>
                    </div>

                    <p class="mt-2 text-xs font-bold text-slate-500">
                        Pencarian mengambil data dari server, bukan cuma data yang sedang tampil.
                    </p>
                </div>

                <div>
                    <label for="jenis_kelamin" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                        Gender
                    </label>

                    <select id="jenis_kelamin"
                            name="jenis_kelamin"
                            class="h-14 w-full rounded-[22px] border border-slate-200 bg-slate-50/85 px-4 text-sm font-black text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                        @foreach($genderOptions as $key => $label)
                            <option value="{{ $key }}" @selected($jenisKelamin === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="kemandirian" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                        Kemandirian
                    </label>

                    <select id="kemandirian"
                            name="kemandirian"
                            class="h-14 w-full rounded-[22px] border border-slate-200 bg-slate-50/85 px-4 text-sm font-black text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                        @foreach($kemandirianOptions as $key => $label)
                            <option value="{{ $key }}" @selected($kemandirian === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status_akun" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                        Status Akun
                    </label>

                    <select id="status_akun"
                            name="status_akun"
                            class="h-14 w-full rounded-[22px] border border-slate-200 bg-slate-50/85 px-4 text-sm font-black text-slate-800 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected($statusAkun === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="submit"
                            class="h-14 rounded-[22px] bg-slate-950 px-5 text-sm font-black text-white shadow-[0_14px_35px_rgba(15,23,42,0.18)] transition hover:bg-slate-800">
                        Filter
                    </button>

                    <a href="{{ $indexRoute }}"
                       id="resetSearchButton"
                       class="inline-flex h-14 items-center justify-center rounded-[22px] border border-emerald-100 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-emerald-50">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section id="lansiaLiveRegion" data-live-region>
            <div class="overflow-hidden rounded-[34px] border border-white/70 bg-white/78 shadow-[0_24px_85px_rgba(15,23,42,0.09)] backdrop-blur-xl">
                <div class="border-b border-emerald-900/20 bg-gradient-to-r from-slate-950 via-emerald-950 to-teal-900 px-5 py-5 text-white sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-200">
                                Daftar Lansia
                            </p>

                            <h2 class="mt-2 text-2xl font-black">
                                Data Sasaran Lansia
                            </h2>

                            <p class="mt-1 text-sm font-semibold text-white/65">
                                {{ $rangeText }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <label class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 text-sm font-black text-white backdrop-blur-xl">
                                <input type="checkbox"
                                       id="selectAllLansia"
                                       class="h-4 w-4 rounded border-white/40 text-emerald-500 focus:ring-emerald-400">
                                Pilih Semua
                            </label>

                            @if($routeHas('kader.data.lansia.bulk-delete'))
                                <button type="button"
                                        id="bulkDeleteButton"
                                        disabled
                                        data-confirm-submit
                                        data-confirm-form="bulkDeleteForm"
                                        data-confirm-title="Hapus Data Terpilih?"
                                        data-confirm-message="Data Lansia yang dipilih akan dihapus jika belum memiliki riwayat layanan."
                                        data-confirm-tone="danger"
                                        class="inline-flex h-11 items-center justify-center rounded-2xl border border-rose-300/40 bg-rose-400/15 px-4 text-sm font-black text-rose-100 opacity-50 transition hover:bg-rose-400/25 disabled:cursor-not-allowed">
                                    Hapus Terpilih
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-4 sm:p-5">
                    @forelse($items as $item)
                        @php
                            $name = $item->nama_lengkap ?? '-';
                            $nik = $item->nik ?? '-';
                            $connected = $isConnected($item);
                        @endphp

                        <article class="group overflow-hidden rounded-[28px] border border-emerald-100/70 bg-gradient-to-br from-white/92 via-emerald-50/35 to-amber-50/30 p-4 shadow-[0_16px_45px_rgba(15,23,42,0.05)] transition duration-300 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-[0_22px_65px_rgba(15,23,42,0.08)]">
                            <div class="data-row">
                                <div class="pt-3 xl:pt-0">
                                    <input type="checkbox"
                                           name="ids[]"
                                           value="{{ $item->id }}"
                                           form="bulkDeleteForm"
                                           class="lansia-check h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                </div>

                                <div class="mobile-stack min-w-0">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[20px] bg-slate-950 text-lg font-black text-emerald-200 shadow-[0_14px_32px_rgba(15,23,42,0.18)]">
                                            {{ $initial($name) }}
                                        </div>

                                        <div class="min-w-0">
                                            <h3 class="truncate text-base font-black text-slate-950">
                                                {{ $name }}
                                            </h3>

                                            <p class="mt-1 truncate text-sm font-extrabold text-slate-500">
                                                NIK {{ $nik }}
                                            </p>

                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <span class="rounded-full bg-emerald-600 px-3 py-1 text-[11px] font-black uppercase tracking-[0.08em] text-white">
                                                    Lansia
                                                </span>

                                                <span class="rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-[0.08em] ring-1 {{ $genderClass($item->jenis_kelamin) }}">
                                                    {{ $genderLabel($item->jenis_kelamin) }}
                                                </span>

                                                <span class="rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-[0.08em] ring-1 {{ $accountClass($item) }}">
                                                    {{ $accountLabel($item) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="min-w-0 rounded-[20px] border border-emerald-100/80 bg-white/78 p-3 shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                                        Usia
                                    </p>

                                    <p class="mt-1 truncate text-sm font-black text-slate-950">
                                        {{ $ageText($item) }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-bold text-slate-500">
                                        {{ $formatDate($item->tanggal_lahir) }}
                                    </p>
                                </div>

                                <div class="min-w-0 rounded-[20px] border border-emerald-100/80 bg-white/78 p-3 shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                                        Kemandirian
                                    </p>

                                    <p class="mt-1 truncate text-sm font-black text-slate-950">
                                        {{ $kemandirianLabel($item->tingkat_kemandirian ?? null) }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-bold text-slate-500">
                                        Tensi {{ $item->tekanan_darah ?: '-' }}
                                    </p>
                                </div>

                                <div class="col-health min-w-0 rounded-[20px] border border-emerald-100/80 bg-white/78 p-3 shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                                        Kesehatan
                                    </p>

                                    <p class="mt-1 truncate text-sm font-black text-slate-950">
                                        GD {{ $numberValue($item->gula_darah ?? null, 'mg/dL') }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-bold text-slate-500">
                                        Kol {{ $numberValue($item->kolesterol ?? null, 'mg/dL') }}
                                    </p>
                                </div>

                                <div class="action-box">
                                    @if(! $connected && $routeHas('kader.data.lansia.sync'))
                                        <form action="{{ route('kader.data.lansia.sync', $item->id) }}"
                                              method="POST"
                                              class="contents">
                                            @csrf

                                            <button type="button"
                                                    class="row-action action-sync"
                                                    data-confirm-submit
                                                    data-confirm-title="Sinkronkan Akun?"
                                                    data-confirm-message="Sistem akan mencoba menghubungkan data Lansia ini dengan akun warga berdasarkan NIK yang sama."
                                                    data-confirm-tone="gold">
                                                Sinkron
                                            </button>
                                        </form>
                                    @endif

                                    @if($routeHas('kader.data.lansia.show'))
                                        <a href="{{ route('kader.data.lansia.show', $item->id) }}"
                                           class="row-action action-detail">
                                            Detail
                                        </a>
                                    @endif

                                    @if($routeHas('kader.data.lansia.edit'))
                                        <a href="{{ route('kader.data.lansia.edit', $item->id) }}"
                                           class="row-action action-edit">
                                            Edit
                                        </a>
                                    @endif

                                    @if($routeHas('kader.data.lansia.destroy'))
                                        <form action="{{ route('kader.data.lansia.destroy', $item->id) }}"
                                              method="POST"
                                              class="contents">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button"
                                                    class="row-action action-delete"
                                                    data-confirm-submit
                                                    data-confirm-title="Hapus Data Lansia?"
                                                    data-confirm-message="Data Lansia ini akan dihapus jika belum memiliki riwayat layanan. Gunakan Edit kalau cuma mau memperbaiki data."
                                                    data-confirm-tone="danger">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[28px] border border-dashed border-emerald-300 bg-emerald-50/70 p-10 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[24px] bg-slate-950 text-2xl font-black text-emerald-200">
                                L
                            </div>

                            <h3 class="mt-5 text-xl font-black text-slate-950">
                                Data Lansia tidak ditemukan
                            </h3>

                            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                                Data dengan kata kunci tersebut belum ada. Tambahkan data baru kalau memang belum masuk.
                            </p>

                            <a href="{{ $createRoute }}"
                               class="mt-5 inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-[0_14px_35px_rgba(4,120,87,0.22)] transition hover:bg-emerald-800">
                                Tambah Lansia
                            </a>
                        </div>
                    @endforelse
                </div>

                @if(method_exists($items, 'links'))
                    <div id="lansiaPagination" class="border-t border-emerald-100/80 bg-emerald-50/45 px-5 py-4">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </section>

        @if($routeHas('kader.data.lansia.bulk-delete'))
            <form id="bulkDeleteForm" action="{{ $bulkDeleteRoute }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
@endsection

@push('modals')
<div id="nexusConfirmModal"
     class="nexus-modal fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm">
    <div class="nexus-modal-card w-full max-w-md overflow-hidden rounded-[32px] border border-white/70 bg-white/90 shadow-[0_30px_100px_rgba(15,23,42,0.28)] backdrop-blur-2xl">
        <div id="nexusConfirmHeader" class="relative overflow-hidden px-6 py-6 text-white">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-16 left-8 h-28 w-44 rounded-t-[80px] bg-amber-300/15"></div>

            <div class="relative">
                <div id="nexusConfirmBadge" class="mb-4 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-50">
                    Konfirmasi
                </div>

                <h3 id="nexusConfirmTitle" class="text-2xl font-black tracking-tight">
                    Konfirmasi Aksi
                </h3>

                <p id="nexusConfirmMessage" class="mt-2 text-sm font-semibold leading-6 text-white/75">
                    Pastikan data sudah benar sebelum melanjutkan.
                </p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white via-emerald-50/50 to-amber-50/35 px-6 py-5">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold leading-6 text-amber-800">
                Aksi ini akan langsung diproses oleh sistem setelah tombol konfirmasi ditekan.
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <button type="button"
                        id="nexusConfirmCancel"
                        class="h-12 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="button"
                        id="nexusConfirmOk"
                        class="h-12 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-[0_14px_35px_rgba(4,120,87,0.22)] transition hover:bg-emerald-800">
                    Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('liveSearchForm');
        const searchInput = document.getElementById('liveSearchInput');
        const genderInput = document.getElementById('jenis_kelamin');
        const kemandirianInput = document.getElementById('kemandirian');
        const statusInput = document.getElementById('status_akun');
        const resetButton = document.getElementById('resetSearchButton');
        const liveRegion = document.querySelector('[data-live-region]');
        const liveState = document.getElementById('liveSearchState');

        const confirmModal = document.getElementById('nexusConfirmModal');
        const confirmHeader = document.getElementById('nexusConfirmHeader');
        const confirmBadge = document.getElementById('nexusConfirmBadge');
        const confirmTitle = document.getElementById('nexusConfirmTitle');
        const confirmMessage = document.getElementById('nexusConfirmMessage');
        const confirmCancel = document.getElementById('nexusConfirmCancel');
        const confirmOk = document.getElementById('nexusConfirmOk');

        let liveTimer = null;
        let liveController = null;
        let pendingForm = null;

        function bindBulkSelection() {
            const selectAll = document.getElementById('selectAllLansia');
            const checks = Array.from(document.querySelectorAll('.lansia-check'));
            const bulkButton = document.getElementById('bulkDeleteButton');

            function refresh() {
                const checkedCount = checks.filter(function (item) {
                    return item.checked;
                }).length;

                if (bulkButton) {
                    bulkButton.disabled = checkedCount === 0;
                    bulkButton.classList.toggle('opacity-50', checkedCount === 0);
                }

                if (selectAll) {
                    selectAll.checked = checks.length > 0 && checkedCount === checks.length;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < checks.length;
                }
            }

            selectAll?.addEventListener('change', function () {
                checks.forEach(function (item) {
                    item.checked = selectAll.checked;
                });

                refresh();
            });

            checks.forEach(function (item) {
                item.addEventListener('change', refresh);
            });

            refresh();
        }

        function buildSearchUrl(pageUrl) {
            const base = pageUrl || form.action;
            const url = new URL(base, window.location.origin);

            const keyword = searchInput.value.trim();
            const gender = genderInput.value || 'semua';
            const kemandirian = kemandirianInput.value || 'semua';
            const status = statusInput.value || 'semua';

            keyword ? url.searchParams.set('search', keyword) : url.searchParams.delete('search');
            gender !== 'semua' ? url.searchParams.set('jenis_kelamin', gender) : url.searchParams.delete('jenis_kelamin');
            kemandirian !== 'semua' ? url.searchParams.set('kemandirian', kemandirian) : url.searchParams.delete('kemandirian');
            status !== 'semua' ? url.searchParams.set('status_akun', status) : url.searchParams.delete('status_akun');

            return url;
        }

        async function loadLiveResults(pageUrl) {
            if (!form || !liveRegion) return;

            if (liveController) {
                liveController.abort();
            }

            liveController = new AbortController();

            const url = buildSearchUrl(pageUrl);

            liveRegion.classList.add('live-loading');
            liveState?.classList.remove('hidden');

            try {
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: liveController.signal
                });

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextRegion = doc.querySelector('[data-live-region]');

                if (!nextRegion) {
                    window.location.href = url.toString();
                    return;
                }

                liveRegion.innerHTML = nextRegion.innerHTML;
                window.history.replaceState({}, '', url.toString());

                bindBulkSelection();
                bindPaginationLinks();
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Live search gagal:', error);
                }
            } finally {
                liveRegion.classList.remove('live-loading');
                liveState?.classList.add('hidden');
            }
        }

        function scheduleSearch() {
            clearTimeout(liveTimer);
            liveTimer = setTimeout(function () {
                loadLiveResults();
            }, 260);
        }

        function bindPaginationLinks() {
            document.querySelectorAll('#lansiaPagination a').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    loadLiveResults(link.href);
                });
            });
        }

        function openConfirm(options) {
            const tone = options.tone || 'emerald';

            confirmTitle.textContent = options.title || 'Konfirmasi Aksi';
            confirmMessage.textContent = options.message || 'Pastikan data sudah benar sebelum melanjutkan.';
            confirmHeader.className = 'relative overflow-hidden px-6 py-6 text-white';

            if (tone === 'danger') {
                confirmHeader.classList.add('bg-gradient-to-br', 'from-rose-950', 'via-rose-800', 'to-orange-700');
                confirmBadge.textContent = 'Aksi Hapus';
                confirmOk.className = 'h-12 rounded-2xl bg-rose-600 px-5 text-sm font-black text-white shadow-[0_14px_35px_rgba(225,29,72,0.22)] transition hover:bg-rose-700';
            } else if (tone === 'gold') {
                confirmHeader.classList.add('bg-gradient-to-br', 'from-amber-950', 'via-amber-700', 'to-emerald-800');
                confirmBadge.textContent = 'Sinkronisasi';
                confirmOk.className = 'h-12 rounded-2xl bg-amber-500 px-5 text-sm font-black text-amber-950 shadow-[0_14px_35px_rgba(245,158,11,0.22)] transition hover:bg-amber-400';
            } else {
                confirmHeader.classList.add('bg-gradient-to-br', 'from-emerald-950', 'via-emerald-800', 'to-teal-700');
                confirmBadge.textContent = 'Konfirmasi';
                confirmOk.className = 'h-12 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-[0_14px_35px_rgba(4,120,87,0.22)] transition hover:bg-emerald-800';
            }

            confirmModal.classList.add('is-open');
            document.body.classList.add('overflow-hidden');
        }

        function closeConfirm() {
            pendingForm = null;
            confirmModal?.classList.remove('is-open');
            document.body.classList.remove('overflow-hidden');
        }

        searchInput?.addEventListener('input', function () {
            this.value = this.value.replace(/\s+/g, ' ');
            scheduleSearch();
        });

        genderInput?.addEventListener('change', function () {
            loadLiveResults();
        });

        kemandirianInput?.addEventListener('change', function () {
            loadLiveResults();
        });

        statusInput?.addEventListener('change', function () {
            loadLiveResults();
        });

        form?.addEventListener('submit', function (event) {
            event.preventDefault();
            loadLiveResults();
        });

        resetButton?.addEventListener('click', function (event) {
            event.preventDefault();

            searchInput.value = '';
            genderInput.value = 'semua';
            kemandirianInput.value = 'semua';
            statusInput.value = 'semua';

            loadLiveResults(form.action);
        });

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-confirm-submit]');

            if (!trigger || trigger.disabled) return;

            const formId = trigger.dataset.confirmForm;
            pendingForm = formId ? document.getElementById(formId) : trigger.closest('form');

            if (!pendingForm) return;

            openConfirm({
                title: trigger.dataset.confirmTitle,
                message: trigger.dataset.confirmMessage,
                tone: trigger.dataset.confirmTone
            });
        });

        confirmCancel?.addEventListener('click', closeConfirm);

        confirmModal?.addEventListener('click', function (event) {
            if (event.target === confirmModal) closeConfirm();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && confirmModal?.classList.contains('is-open')) closeConfirm();
        });

        confirmOk?.addEventListener('click', function () {
            if (!pendingForm) {
                closeConfirm();
                return;
            }

            const targetForm = pendingForm;
            pendingForm = null;

            confirmModal?.classList.remove('is-open');
            document.body.classList.remove('overflow-hidden');

            HTMLFormElement.prototype.submit.call(targetForm);
        });

        bindBulkSelection();
        bindPaginationLinks();
    });
</script>
@endpush