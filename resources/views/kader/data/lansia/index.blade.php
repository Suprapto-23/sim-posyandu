@extends('layouts.kader')

@section('title', 'Data Lansia')
@section('page-name', 'Data Lansia')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $items = $items ?? ($lansias ?? collect());
    $search = $search ?? request('search', '');
    $statusAkun = $statusAkun ?? request('status_akun', 'semua');
    $jenisKelamin = $jenisKelamin ?? request('jenis_kelamin', 'semua');
    $kemandirian = $kemandirian ?? request('kemandirian', 'semua');

    $statTotal = $statTotal ?? 0;
    $statLaki = $statLaki ?? 0;
    $statPerempuan = $statPerempuan ?? 0;
    $statTerhubung = $statTerhubung ?? 0;
    $statBelumTerhubung = $statBelumTerhubung ?? 0;
    $statMandiri = $statMandiri ?? 0;
    $statButuhBantuan = $statButuhBantuan ?? 0;
    $statTensiTercatat = $statTensiTercatat ?? 0;

    $sessionType = session('success') ? 'success' : (session('warning') ? 'warning' : (session('error') ? 'error' : null));
    $sessionMessage = session('success') ?? session('warning') ?? session('error');

    $genderLabel = function ($jk) {
        return match ($jk) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    };

    $genderBadgeClass = function ($jk) {
        return match ($jk) {
            'L' => 'border-sky-100 bg-sky-50 text-sky-700',
            'P' => 'border-pink-100 bg-pink-50 text-pink-700',
            default => 'border-slate-100 bg-slate-50 text-slate-600',
        };
    };

    $kemandirianLabel = function ($value) {
        return match ($value) {
            'mandiri' => 'Mandiri',
            'bantuan_sebagian' => 'Bantuan Sebagian',
            'ketergantungan_penuh' => 'Ketergantungan Penuh',
            default => 'Belum Diisi',
        };
    };

    $kemandirianBadgeClass = function ($value) {
        return match ($value) {
            'mandiri' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
            'bantuan_sebagian' => 'border-amber-100 bg-amber-50 text-amber-700',
            'ketergantungan_penuh' => 'border-rose-100 bg-rose-50 text-rose-700',
            default => 'border-slate-100 bg-slate-50 text-slate-600',
        };
    };
@endphp

@section('content')
    <style>
        .nexus-toast-show {
            animation: nexusToastShow .2s ease-out both;
        }

        .nexus-toast-hide {
            animation: nexusToastHide .16s ease-in both;
        }

        .nexus-modal-show {
            animation: nexusModalShow .16s ease-out both;
        }

        @keyframes nexusToastShow {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes nexusToastHide {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateY(-10px) scale(.985);
            }
        }

        @keyframes nexusModalShow {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        body.nexus-modal-lock {
            overflow: hidden !important;
        }

        #nexusConfirmOverlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 999999 !important;
            display: none;
            place-items: center;
            min-height: 100dvh;
            padding: 18px;
            background: rgba(15, 23, 42, .48);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        #nexusConfirmOverlay.is-open {
            display: grid !important;
        }

        #nexusConfirmBox {
            width: min(100%, 440px);
            max-height: calc(100dvh - 36px);
            overflow-y: auto;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, .8);
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 26px 80px rgba(15, 23, 42, .22);
        }

        #nexusMiniToast {
            position: fixed !important;
            top: 16px !important;
            right: 16px !important;
            z-index: 1000000 !important;
            width: min(calc(100% - 32px), 390px);
        }

        @media (max-width: 640px) {
            #nexusConfirmOverlay {
                padding: 14px;
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
            }

            #nexusConfirmBox {
                border-radius: 24px;
            }

            #nexusMiniToast {
                top: 12px !important;
                right: 12px !important;
                width: calc(100% - 24px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .nexus-toast-show,
            .nexus-toast-hide,
            .nexus-modal-show {
                animation: none !important;
            }
        }
    </style>

    <div class="w-full space-y-5">

        {{-- SESSION TOAST --}}
        @if($sessionType && $sessionMessage)
            <div id="nexusSessionToast" class="fixed right-4 top-4 z-[100000] w-[calc(100%-2rem)] max-w-md nexus-toast-show">
                <div
                    class="overflow-hidden rounded-3xl border bg-white shadow-2xl shadow-slate-900/10
                    {{ $sessionType === 'success' ? 'border-emerald-100' : ($sessionType === 'warning' ? 'border-amber-100' : 'border-rose-100') }}"
                >
                    <div class="flex items-start gap-3 p-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border
                            {{ $sessionType === 'success' ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : ($sessionType === 'warning' ? 'border-amber-100 bg-amber-50 text-amber-700' : 'border-rose-100 bg-rose-50 text-rose-700') }}"
                        >
                            <i class="ph-fill {{ $sessionType === 'success' ? 'ph-check-circle' : ($sessionType === 'warning' ? 'ph-warning-circle' : 'ph-x-circle') }} text-2xl"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm font-black
                                {{ $sessionType === 'success' ? 'text-emerald-800' : ($sessionType === 'warning' ? 'text-amber-800' : 'text-rose-800') }}"
                            >
                                {{ $sessionType === 'success' ? 'Berhasil Diproses' : ($sessionType === 'warning' ? 'Perhatian Sistem' : 'Aksi Gagal') }}
                            </p>

                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                                {{ $sessionMessage }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="nexus-toast-close flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Tutup notifikasi"
                        >
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <div
                        class="h-1 w-full
                        {{ $sessionType === 'success' ? 'bg-emerald-500' : ($sessionType === 'warning' ? 'bg-amber-500' : 'bg-rose-500') }}"
                    ></div>
                </div>
            </div>
        @endif

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[1.75rem] border border-emerald-100 bg-gradient-to-br from-emerald-50 via-teal-50 to-slate-50 p-5 shadow-sm sm:p-6">
            <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-emerald-200/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-amber-200/20 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-2xl border border-emerald-100 bg-white/70 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        <i class="ph-fill ph-person-simple-walk text-base"></i>
                        Database Lansia
                    </div>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                        Data Lansia
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-7 text-slate-600">
                        Kelola data sasaran Lansia untuk pemantauan kesehatan dasar, tingkat kemandirian, pemeriksaan fisik, dan laporan Posyandu.
                    </p>

                    <div class="mt-3 max-w-2xl rounded-2xl border border-emerald-100 bg-white/60 px-4 py-3 text-xs font-bold leading-6 text-slate-600">
                        <i class="ph-fill ph-info mr-1 text-emerald-600"></i>
                        Akun warga Lansia memakai <span class="font-black text-emerald-700">NIK Lansia</span>. Data kesehatan seperti tensi, gula darah, kolesterol, asam urat, dan kemandirian wajib dijaga konsisten.
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row lg:items-center">
                    @if($routeHas('kader.import.create'))
                        <a
                            href="{{ route('kader.import.create') }}"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 text-sm font-black text-amber-700 shadow-sm transition-all duration-150 ease-out hover:bg-amber-100"
                        >
                            <i class="ph-fill ph-file-arrow-up text-lg"></i>
                            Import
                        </a>
                    @endif

                    @if($routeHas('kader.data.lansia.create'))
                        <a
                            href="{{ route('kader.data.lansia.create') }}"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800"
                        >
                            <i class="ph-bold ph-plus text-lg"></i>
                            Tambah Lansia
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- STATS --}}
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                        <i class="ph-fill ph-users-three text-xl"></i>
                    </div>
                    <span class="rounded-full bg-slate-50 px-3 py-1 text-[11px] font-black text-slate-500">Total</span>
                </div>
                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Total Lansia</p>
                <h2 class="mt-1 text-3xl font-black text-slate-900">{{ $statTotal }}</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Seluruh sasaran lansia</p>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-700">
                        <i class="ph-fill ph-person-simple-walk text-xl"></i>
                    </div>
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-[11px] font-black text-teal-700">Mandiri</span>
                </div>
                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Mandiri</p>
                <h2 class="mt-1 text-3xl font-black text-slate-900">{{ $statMandiri }}</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Tingkat kemandirian mandiri</p>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                        <i class="ph-fill ph-hand-heart text-xl"></i>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-black text-amber-700">Bantuan</span>
                </div>
                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Butuh Bantuan</p>
                <h2 class="mt-1 text-3xl font-black text-slate-900">{{ $statButuhBantuan }}</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Sebagian atau penuh</p>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                        <i class="ph-fill ph-heartbeat text-xl"></i>
                    </div>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-[11px] font-black text-sky-700">Tensi</span>
                </div>
                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Tensi Tercatat</p>
                <h2 class="mt-1 text-3xl font-black text-slate-900">{{ $statTensiTercatat }}</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $statTerhubung }} akun terhubung</p>
            </div>
        </section>

        {{-- FILTER --}}
        <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-4 shadow-sm">
            <form id="filterForm" action="{{ route('kader.data.lansia.index') }}" method="GET" class="grid gap-3 2xl:grid-cols-[1fr_190px_210px_240px_auto_auto] 2xl:items-end">
                <div>
                    <label for="searchInput" class="mb-2 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                        Cari Lansia
                    </label>

                    <div class="relative">
                        <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input
                            id="searchInput"
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            autocomplete="off"
                            placeholder="Ketik nama, NIK, alamat, tensi, penyakit, atau keluhan..."
                            class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50/80 py-2.5 pl-11 pr-4 text-sm font-bold text-slate-700 outline-none transition-all duration-150 ease-out placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>

                    <p class="mt-2 text-xs font-semibold text-slate-400">
                        Live search aktif. Ketik minimal 2 huruf, sistem akan mencari otomatis.
                    </p>
                </div>

                <div>
                    <label for="jenisKelaminFilter" class="mb-2 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                        Jenis Kelamin
                    </label>

                    <select
                        id="jenisKelaminFilter"
                        name="jenis_kelamin"
                        class="live-filter h-11 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm font-black text-slate-700 outline-none transition-all duration-150 ease-out focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="semua" @selected($jenisKelamin === 'semua')>Semua</option>
                        <option value="L" @selected($jenisKelamin === 'L')>Laki-laki</option>
                        <option value="P" @selected($jenisKelamin === 'P')>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label for="statusAkunFilter" class="mb-2 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                        Status Akun
                    </label>

                    <select
                        id="statusAkunFilter"
                        name="status_akun"
                        class="live-filter h-11 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm font-black text-slate-700 outline-none transition-all duration-150 ease-out focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="semua" @selected($statusAkun === 'semua')>Semua Status</option>
                        <option value="terhubung" @selected($statusAkun === 'terhubung')>Terhubung Akun</option>
                        <option value="belum" @selected($statusAkun === 'belum')>Belum Terhubung</option>
                    </select>
                </div>

                <div>
                    <label for="kemandirianFilter" class="mb-2 block text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                        Kemandirian
                    </label>

                    <select
                        id="kemandirianFilter"
                        name="kemandirian"
                        class="live-filter h-11 w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm font-black text-slate-700 outline-none transition-all duration-150 ease-out focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="semua" @selected($kemandirian === 'semua')>Semua</option>
                        <option value="mandiri" @selected($kemandirian === 'mandiri')>Mandiri</option>
                        <option value="bantuan_sebagian" @selected($kemandirian === 'bantuan_sebagian')>Bantuan Sebagian</option>
                        <option value="ketergantungan_penuh" @selected($kemandirian === 'ketergantungan_penuh')>Ketergantungan Penuh</option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800"
                >
                    <i class="ph-fill ph-funnel text-lg"></i>
                    Filter
                </button>

                <a
                    href="{{ route('kader.data.lansia.index') }}"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-black text-slate-600 transition-all duration-150 ease-out hover:bg-slate-100"
                >
                    <i class="ph-bold ph-arrow-counter-clockwise text-lg"></i>
                    Reset
                </a>
            </form>
        </section>

        @if($routeHas('kader.data.lansia.bulk-delete'))
            <form
                id="bulkDeleteForm"
                action="{{ route('kader.data.lansia.bulk-delete') }}"
                method="POST"
                class="hidden"
                data-confirm="true"
                data-confirm-variant="danger"
                data-confirm-title="Hapus Data Terpilih?"
                data-confirm-message="Data Lansia yang dipilih akan dihapus. Data yang sudah memiliki riwayat pemeriksaan tetap akan ditolak sistem."
                data-confirm-button="Ya, Hapus"
            >
                @csrf
                @method('DELETE')
            </form>
        @endif

        {{-- LIST --}}
        <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-4 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-900">
                        Daftar Lansia
                    </h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        Menampilkan data Lansia berdasarkan filter aktif.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span id="visibleCountBadge" class="inline-flex h-9 items-center rounded-full bg-emerald-50 px-4 text-xs font-black uppercase tracking-[0.12em] text-emerald-700">
                        {{ method_exists($items, 'total') ? $items->total() : $items->count() }} Data
                    </span>

                    @if($routeHas('kader.data.lansia.bulk-delete') && $items->count())
                        <label class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-xs font-black text-slate-600 transition-all duration-150 ease-out hover:bg-slate-100">
                            <input
                                id="checkAllLansia"
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500"
                            >
                            Pilih Semua
                        </label>

                        <button
                            id="bulkDeleteButton"
                            type="submit"
                            form="bulkDeleteForm"
                            disabled
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 text-xs font-black text-white opacity-45 shadow-sm transition-all duration-150 ease-out hover:bg-rose-700 disabled:cursor-not-allowed"
                        >
                            <i class="ph-fill ph-trash text-base"></i>
                            Hapus Terpilih
                        </button>
                    @endif
                </div>
            </div>

            @if($items->count())
                <div id="bulkDeleteInfo" class="mb-4 hidden rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-black text-rose-700">
                    <i class="ph-fill ph-warning-circle mr-1"></i>
                    <span id="selectedCountText">0 data dipilih.</span>
                </div>

                <div id="lansiaList" class="grid gap-3">
                    @foreach($items as $item)
                        @php
                            $tanggalLahir = filled($item->tanggal_lahir ?? null) ? Carbon::parse($item->tanggal_lahir) : null;

                            $usiaText = '-';
                            if ($tanggalLahir) {
                                $diff = $tanggalLahir->diff(now('Asia/Jakarta'));
                                $usiaText = $diff->y . ' tahun ' . $diff->m . ' bulan';
                            }

                            $akunTerhubung = filled(data_get($item, 'user_id'));
                            $pemeriksaan = data_get($item, 'pemeriksaan_terakhir');
                            $initial = strtoupper(substr($item->nama_lengkap ?? 'L', 0, 1));
                            $imt = $item->imt ?? null;

                            $searchText = Str::lower(collect([
                                $item->nama_lengkap ?? '',
                                $item->nik ?? '',
                                $item->kode_lansia ?? '',
                                $item->tempat_lahir ?? '',
                                $item->alamat ?? '',
                                $item->penyakit_bawaan ?? '',
                                $item->tingkat_kemandirian ?? '',
                                $item->tekanan_darah ?? '',
                                $item->gula_darah ?? '',
                                $item->kolesterol ?? '',
                                $item->asam_urat ?? '',
                                $item->lingkar_perut ?? '',
                                $item->keluhan ?? '',
                                $genderLabel($item->jenis_kelamin),
                                $kemandirianLabel($item->tingkat_kemandirian ?? null),
                                $akunTerhubung ? 'terhubung akun siap' : 'belum terhubung sinkron',
                            ])->join(' '));
                        @endphp

                        <article
                            class="lansia-card rounded-[1.5rem] border border-slate-100 bg-gradient-to-br from-white to-slate-50/80 p-4 shadow-sm transition-all duration-150 ease-out hover:border-emerald-100 hover:shadow-md"
                            data-search-text="{{ $searchText }}"
                        >
                            <div class="grid gap-4 xl:grid-cols-[1fr_170px] xl:items-start">

                                {{-- INFO --}}
                                <div class="flex min-w-0 gap-3">
                                    @if($routeHas('kader.data.lansia.bulk-delete'))
                                        <div class="pt-1.5">
                                            <input
                                                type="checkbox"
                                                name="ids[]"
                                                value="{{ $item->id }}"
                                                form="bulkDeleteForm"
                                                class="bulk-check h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500"
                                                aria-label="Pilih {{ $item->nama_lengkap }}"
                                            >
                                        </div>
                                    @endif

                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-700 text-lg font-black text-white shadow-sm">
                                        {{ $initial }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex items-center rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-emerald-700">
                                                Lansia
                                            </span>

                                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.11em] {{ $genderBadgeClass($item->jenis_kelamin) }}">
                                                {{ $genderLabel($item->jenis_kelamin) }}
                                            </span>

                                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.11em] {{ $kemandirianBadgeClass($item->tingkat_kemandirian ?? null) }}">
                                                {{ $kemandirianLabel($item->tingkat_kemandirian ?? null) }}
                                            </span>

                                            @if($akunTerhubung)
                                                <span class="inline-flex items-center gap-1 rounded-full border border-teal-100 bg-teal-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-teal-700">
                                                    <i class="ph-fill ph-link-simple"></i>
                                                    Akun Terhubung
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full border border-amber-100 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-amber-700">
                                                    <i class="ph-fill ph-warning-circle"></i>
                                                    Belum Terhubung
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="mt-2 truncate text-lg font-black text-slate-900">
                                            {{ $item->nama_lengkap }}
                                        </h3>

                                        <p class="mt-0.5 text-sm font-bold text-slate-500">
                                            NIK Lansia:
                                            <span class="font-black text-slate-700">{{ $item->nik ?? '-' }}</span>
                                        </p>

                                        <div class="mt-3 grid gap-2 md:grid-cols-4">
                                            <div class="rounded-2xl border border-slate-100 bg-white/75 p-3">
                                                <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-400">Usia</p>
                                                <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $usiaText }}</p>
                                                <p class="text-xs font-semibold text-slate-500">
                                                    {{ $tanggalLahir ? $tanggalLahir->translatedFormat('d F Y') : '-' }}
                                                </p>
                                            </div>

                                            <div class="rounded-2xl border border-slate-100 bg-white/75 p-3">
                                                <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-400">Tensi</p>
                                                <p class="mt-1 truncate text-sm font-black text-slate-800">
                                                    {{ $item->tekanan_darah ?? '-' }}
                                                </p>
                                                <p class="truncate text-xs font-semibold text-slate-500">mmHg</p>
                                            </div>

                                            <div class="rounded-2xl border border-slate-100 bg-white/75 p-3">
                                                <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-400">Gula/Kolesterol</p>
                                                <p class="mt-1 truncate text-sm font-black text-slate-800">
                                                    GD {{ $item->gula_darah ?? '-' }}
                                                </p>
                                                <p class="truncate text-xs font-semibold text-slate-500">
                                                    Kol {{ $item->kolesterol ?? '-' }}
                                                </p>
                                            </div>

                                            <div class="rounded-2xl border border-slate-100 bg-white/75 p-3">
                                                <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-400">IMT/Lingkar</p>
                                                <p class="mt-1 truncate text-sm font-black text-slate-800">
                                                    IMT {{ $imt ?? '-' }}
                                                </p>
                                                <p class="truncate text-xs font-semibold text-slate-500">
                                                    LP {{ $item->lingkar_perut ?? '-' }} cm
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-2 grid gap-2 md:grid-cols-2">
                                            <div class="rounded-2xl border border-slate-100 bg-white/70 px-3 py-2.5 text-sm font-semibold text-slate-500">
                                                <i class="ph-fill ph-map-pin mr-1 text-emerald-600"></i>
                                                {{ $item->alamat ?? 'Alamat belum diisi' }}
                                            </div>

                                            <div class="rounded-2xl border border-slate-100 bg-white/70 px-3 py-2.5 text-sm font-semibold text-slate-500">
                                                <i class="ph-fill ph-note-pencil mr-1 text-amber-600"></i>
                                                {{ $item->keluhan ?? 'Keluhan belum diisi' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- AKSI --}}
                                <div class="grid gap-1.5 xl:w-[170px]">
                                    @if($pemeriksaan)
                                        <div class="inline-flex h-9 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 px-2.5 text-[11px] font-black text-emerald-700">
                                            <i class="ph-fill ph-check-circle mr-1 text-sm"></i>
                                            Pemeriksaan Ada
                                        </div>
                                    @else
                                        <div class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 px-2.5 text-[11px] font-black text-slate-500">
                                            <i class="ph-fill ph-clock mr-1 text-sm"></i>
                                            Belum Pemeriksaan
                                        </div>
                                    @endif

                                    @if(!$akunTerhubung && $routeHas('kader.data.lansia.sync'))
                                        <form
                                            action="{{ route('kader.data.lansia.sync', $item->id) }}"
                                            method="POST"
                                            data-confirm="true"
                                            data-confirm-variant="warning"
                                            data-confirm-title="Sinkronkan Akun?"
                                            data-confirm-message="Pastikan akun warga sudah dibuat Admin memakai NIK Lansia yang sama."
                                            data-confirm-button="Ya, Sinkronkan"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-2.5 text-[11px] font-black text-amber-700 transition-all duration-150 ease-out hover:bg-amber-100"
                                            >
                                                <i class="ph-bold ph-link-simple text-sm"></i>
                                                Sinkron
                                            </button>
                                        </form>
                                    @else
                                        <div class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-xl border border-teal-100 bg-teal-50 px-2.5 text-[11px] font-black text-teal-700">
                                            <i class="ph-fill ph-check-circle text-sm"></i>
                                            Akun Siap
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-1.5">
                                        @if($routeHas('kader.data.lansia.show'))
                                            <a
                                                href="{{ route('kader.data.lansia.show', $item->id) }}"
                                                class="inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-slate-200 bg-white px-2 text-[11px] font-black text-slate-600 transition-all duration-150 ease-out hover:bg-slate-50"
                                            >
                                                <i class="ph-fill ph-eye text-sm"></i>
                                                Detail
                                            </a>
                                        @endif

                                        @if($routeHas('kader.data.lansia.edit'))
                                            <a
                                                href="{{ route('kader.data.lansia.edit', $item->id) }}"
                                                class="inline-flex h-9 items-center justify-center gap-1 rounded-xl border border-sky-200 bg-sky-50 px-2 text-[11px] font-black text-sky-700 transition-all duration-150 ease-out hover:bg-sky-100"
                                            >
                                                <i class="ph-fill ph-pencil-simple text-sm"></i>
                                                Edit
                                            </a>
                                        @endif
                                    </div>

                                    @if($routeHas('kader.data.lansia.destroy'))
                                        <form
                                            action="{{ route('kader.data.lansia.destroy', $item->id) }}"
                                            method="POST"
                                            data-confirm="true"
                                            data-confirm-variant="danger"
                                            data-confirm-title="Hapus Data Lansia?"
                                            data-confirm-message="Data Lansia {{ $item->nama_lengkap }} akan dihapus. Jika sudah punya riwayat pemeriksaan, sistem akan menolak penghapusan."
                                            data-confirm-button="Ya, Hapus"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-2.5 text-[11px] font-black text-rose-700 transition-all duration-150 ease-out hover:bg-rose-100"
                                            >
                                                <i class="ph-fill ph-trash text-sm"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div id="noLiveResult" class="hidden rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50/70 px-5 py-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                        <i class="ph-fill ph-magnifying-glass text-3xl"></i>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-900">
                        Data Tidak Ditemukan
                    </h3>

                    <p class="mx-auto mt-2 max-w-lg text-sm font-semibold leading-7 text-slate-500">
                        Tidak ada data Lansia yang cocok dengan kata kunci pencarian saat ini.
                    </p>
                </div>

                @if(method_exists($items, 'hasPages') && $items->hasPages())
                    <div id="paginationWrap" class="mt-5">
                        {{ $items->links() }}
                    </div>
                @endif
            @else
                <div class="rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50/70 px-5 py-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                        <i class="ph-fill ph-person-simple-walk text-3xl"></i>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-900">
                        Data Lansia Kosong
                    </h3>

                    <p class="mx-auto mt-2 max-w-lg text-sm font-semibold leading-7 text-slate-500">
                        Belum ada data Lansia yang cocok dengan filter saat ini.
                    </p>
                </div>
            @endif
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchInput');
            const liveFilters = Array.from(document.querySelectorAll('.live-filter'));

            const checkAll = document.getElementById('checkAllLansia');
            const bulkChecks = Array.from(document.querySelectorAll('.bulk-check'));
            const bulkDeleteButton = document.getElementById('bulkDeleteButton');
            const bulkDeleteInfo = document.getElementById('bulkDeleteInfo');
            const selectedCountText = document.getElementById('selectedCountText');
            const visibleCountBadge = document.getElementById('visibleCountBadge');
            const noLiveResult = document.getElementById('noLiveResult');
            const paginationWrap = document.getElementById('paginationWrap');
            const cards = Array.from(document.querySelectorAll('.lansia-card'));

            const sessionToast = document.getElementById('nexusSessionToast');

            let pendingForm = null;
            let isSubmitting = false;
            let toastTimer = null;
            let searchTimer = null;
            const initialSearch = searchInput ? searchInput.value.trim() : '';

            function normalizeText(value) {
                return (value || '').toString().toLowerCase().trim();
            }

            function filterCardsLocal() {
                if (!searchInput || cards.length === 0) {
                    return;
                }

                const keyword = normalizeText(searchInput.value);
                let visibleCount = 0;

                cards.forEach(function (card) {
                    const text = normalizeText(card.dataset.searchText);
                    const matched = keyword === '' || text.includes(keyword);

                    card.classList.toggle('hidden', !matched);

                    if (matched) {
                        visibleCount += 1;
                    }

                    const checkbox = card.querySelector('.bulk-check');

                    if (!matched && checkbox) {
                        checkbox.checked = false;
                    }
                });

                if (visibleCountBadge) {
                    visibleCountBadge.textContent = visibleCount + ' Data';
                }

                if (noLiveResult) {
                    noLiveResult.classList.toggle('hidden', visibleCount > 0);
                }

                if (paginationWrap) {
                    paginationWrap.classList.toggle('hidden', keyword.length > 0);
                }

                updateBulkState();
            }

            function scheduleServerSearch() {
                if (!filterForm || !searchInput) {
                    return;
                }

                const keyword = searchInput.value.trim();

                clearTimeout(searchTimer);

                searchTimer = setTimeout(function () {
                    if (keyword === initialSearch) {
                        return;
                    }

                    if (keyword.length === 0 || keyword.length >= 2) {
                        filterForm.submit();
                    }
                }, 650);
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    filterCardsLocal();
                    scheduleServerSearch();
                });
            }

            liveFilters.forEach(function (select) {
                select.addEventListener('change', function () {
                    if (filterForm) {
                        filterForm.submit();
                    }
                });
            });

            function createFloatingLayer() {
                document.getElementById('nexusConfirmOverlay')?.remove();
                document.getElementById('nexusMiniToast')?.remove();

                document.body.insertAdjacentHTML('beforeend', `
                    <div id="nexusConfirmOverlay" aria-hidden="true">
                        <div id="nexusConfirmBox" class="nexus-modal-show p-5" role="dialog" aria-modal="true" aria-labelledby="nexusConfirmTitle">
                            <div class="flex items-start gap-3.5">
                                <div id="nexusConfirmIconWrap" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700">
                                    <i id="nexusConfirmIcon" class="ph-fill ph-check-circle text-2xl"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 id="nexusConfirmTitle" class="text-lg font-black leading-6 text-slate-900">
                                        Konfirmasi Aksi
                                    </h3>

                                    <p id="nexusConfirmMessage" class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                        Pastikan data sudah benar sebelum diproses.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-2">
                                <button
                                    id="nexusConfirmCancel"
                                    type="button"
                                    class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 transition-all duration-150 ease-out hover:bg-slate-50"
                                >
                                    Batal
                                </button>

                                <button
                                    id="nexusConfirmSubmit"
                                    type="button"
                                    class="inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800"
                                >
                                    Ya, Lanjutkan
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="nexusMiniToast" class="hidden">
                        <div class="nexus-toast-show rounded-3xl border border-amber-100 bg-white p-4 shadow-2xl shadow-slate-900/10">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-amber-100 bg-amber-50 text-amber-700">
                                    <i class="ph-fill ph-warning-circle text-xl"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-black text-amber-800">Perhatian</p>
                                    <p id="nexusMiniToastMessage" class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                                        Aksi belum bisa diproses.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }

            createFloatingLayer();

            const confirmOverlay = document.getElementById('nexusConfirmOverlay');
            const confirmTitle = document.getElementById('nexusConfirmTitle');
            const confirmMessage = document.getElementById('nexusConfirmMessage');
            const confirmSubmit = document.getElementById('nexusConfirmSubmit');
            const confirmCancel = document.getElementById('nexusConfirmCancel');
            const confirmIconWrap = document.getElementById('nexusConfirmIconWrap');
            const confirmIcon = document.getElementById('nexusConfirmIcon');

            const miniToast = document.getElementById('nexusMiniToast');
            const miniToastMessage = document.getElementById('nexusMiniToastMessage');

            function showMiniToast(message) {
                if (!miniToast || !miniToastMessage) {
                    return;
                }

                miniToastMessage.textContent = message;
                miniToast.classList.remove('hidden', 'nexus-toast-hide');
                miniToast.classList.add('nexus-toast-show');

                clearTimeout(toastTimer);

                toastTimer = setTimeout(function () {
                    miniToast.classList.remove('nexus-toast-show');
                    miniToast.classList.add('nexus-toast-hide');

                    setTimeout(function () {
                        miniToast.classList.add('hidden');
                    }, 220);
                }, 2400);
            }

            function closeSessionToast() {
                if (!sessionToast) {
                    return;
                }

                sessionToast.classList.remove('nexus-toast-show');
                sessionToast.classList.add('nexus-toast-hide');

                setTimeout(function () {
                    sessionToast.remove();
                }, 240);
            }

            if (sessionToast) {
                setTimeout(closeSessionToast, 3800);

                const closeButton = sessionToast.querySelector('.nexus-toast-close');

                if (closeButton) {
                    closeButton.addEventListener('click', closeSessionToast);
                }
            }

            function updateBulkState() {
                const selectedCount = bulkChecks.filter(function (checkbox) {
                    return checkbox.checked;
                }).length;

                if (bulkDeleteButton) {
                    bulkDeleteButton.disabled = selectedCount === 0;
                    bulkDeleteButton.classList.toggle('opacity-45', selectedCount === 0);
                    bulkDeleteButton.classList.toggle('opacity-100', selectedCount > 0);
                }

                if (bulkDeleteInfo) {
                    bulkDeleteInfo.classList.toggle('hidden', selectedCount === 0);
                }

                if (selectedCountText) {
                    selectedCountText.textContent = selectedCount + ' data dipilih.';
                }

                if (checkAll) {
                    checkAll.checked = bulkChecks.length > 0 && selectedCount === bulkChecks.length;
                    checkAll.indeterminate = selectedCount > 0 && selectedCount < bulkChecks.length;
                }
            }

            function setConfirmVariant(variant) {
                const variants = {
                    danger: {
                        wrap: 'flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-700',
                        icon: 'ph-fill ph-trash text-2xl',
                        button: 'inline-flex h-11 items-center justify-center rounded-2xl bg-rose-600 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-rose-700',
                    },
                    warning: {
                        wrap: 'flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-amber-100 bg-amber-50 text-amber-700',
                        icon: 'ph-fill ph-warning-circle text-2xl',
                        button: 'inline-flex h-11 items-center justify-center rounded-2xl bg-amber-600 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-amber-700',
                    },
                    success: {
                        wrap: 'flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700',
                        icon: 'ph-fill ph-check-circle text-2xl',
                        button: 'inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800',
                    },
                };

                const selected = variants[variant] || variants.success;

                confirmIconWrap.className = selected.wrap;
                confirmIcon.className = selected.icon;
                confirmSubmit.className = selected.button;
            }

            function openConfirm(form) {
                pendingForm = form;

                confirmTitle.textContent = form.getAttribute('data-confirm-title') || 'Konfirmasi Aksi';
                confirmMessage.textContent = form.getAttribute('data-confirm-message') || 'Pastikan data sudah benar sebelum diproses.';
                confirmSubmit.textContent = form.getAttribute('data-confirm-button') || 'Ya, Lanjutkan';

                setConfirmVariant(form.getAttribute('data-confirm-variant') || 'success');

                confirmOverlay.classList.add('is-open');
                confirmOverlay.setAttribute('aria-hidden', 'false');
                document.body.classList.add('nexus-modal-lock');
            }

            function closeConfirm() {
                pendingForm = null;
                confirmOverlay.classList.remove('is-open');
                confirmOverlay.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('nexus-modal-lock');
            }

            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    bulkChecks.forEach(function (checkbox) {
                        if (!checkbox.closest('.lansia-card')?.classList.contains('hidden')) {
                            checkbox.checked = checkAll.checked;
                        }
                    });

                    updateBulkState();
                });
            }

            bulkChecks.forEach(function (checkbox) {
                checkbox.addEventListener('change', updateBulkState);
            });

            document.querySelectorAll('form[data-confirm="true"]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (isSubmitting) {
                        return;
                    }

                    if (form.id === 'bulkDeleteForm') {
                        const selectedCount = bulkChecks.filter(function (checkbox) {
                            return checkbox.checked;
                        }).length;

                        if (selectedCount === 0) {
                            event.preventDefault();
                            showMiniToast('Pilih minimal satu data Lansia dulu sebelum menghapus.');
                            return;
                        }

                        form.setAttribute(
                            'data-confirm-message',
                            'Hapus ' + selectedCount + ' data Lansia yang dipilih? Data yang sudah punya riwayat pemeriksaan tetap akan ditolak oleh sistem.'
                        );
                    }

                    if (!form.dataset.confirmed) {
                        event.preventDefault();
                        openConfirm(form);
                    }
                });
            });

            if (confirmCancel) {
                confirmCancel.addEventListener('click', closeConfirm);
            }

            if (confirmOverlay) {
                confirmOverlay.addEventListener('click', function (event) {
                    if (event.target === confirmOverlay) {
                        closeConfirm();
                    }
                });
            }

            if (confirmSubmit) {
                confirmSubmit.addEventListener('click', function () {
                    if (!pendingForm || isSubmitting) {
                        return;
                    }

                    isSubmitting = true;
                    pendingForm.dataset.confirmed = 'true';
                    confirmSubmit.disabled = true;
                    confirmSubmit.classList.add('opacity-70', 'cursor-wait');
                    confirmSubmit.textContent = 'Memproses...';

                    if (pendingForm.requestSubmit) {
                        pendingForm.requestSubmit();
                    } else {
                        pendingForm.submit();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && confirmOverlay && confirmOverlay.classList.contains('is-open')) {
                    closeConfirm();
                }
            });

            filterCardsLocal();
            updateBulkState();
        });
    </script>
@endsection