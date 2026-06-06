@extends('layouts.admin')

@section('title', 'Manajemen Akun Kader')
@section('page-name', 'Data Kader')
@section('page-title', 'Manajemen Akun Kader')

@php
    use Illuminate\Support\Str;

    $stats = $stats ?? [
        'total' => 0,
        'aktif' => 0,
        'nonaktif' => 0,
    ];

    $search = request('search', '');
    $status = request('status', 'semua');
    $perPage = request('per_page', 10);

    $totalData = isset($kaders) && method_exists($kaders, 'total')
        ? $kaders->total()
        : count($kaders ?? []);

    $rangeText = isset($kaders) && method_exists($kaders, 'firstItem')
        ? 'Menampilkan ' . (($kaders->firstItem() ?? 0)) . ' sampai ' . (($kaders->lastItem() ?? 0)) . ' dari ' . $kaders->total() . ' akun kader'
        : 'Menampilkan ' . count($kaders ?? []) . ' akun kader';

    $getName = function ($kader) {
        return $kader->profile?->full_name ?? $kader->name ?? 'Kader';
    };

    $getNik = function ($kader) {
        return $kader->nik ?? $kader->profile?->nik ?? '-';
    };

    $getPhone = function ($kader) {
        return $kader->profile?->telepon ?? '-';
    };

    $getJabatan = function ($kader) {
        return $kader->kader?->jabatan ?? 'Kader Posyandu';
    };

    $getWilayah = function ($kader) {
        return $kader->kader?->wilayah_tugas ?? 'Posyandu Mugi Lestari';
    };

    $getInitial = function ($name) {
        return Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'K';
    };

    $statusText = function ($value) {
        return $value === 'active' ? 'Aktif' : 'Nonaktif';
    };

    $statusBadge = function ($value) {
        return $value === 'active'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-rose-200 bg-rose-50 text-rose-700';
    };

    $statusDot = function ($value) {
        return $value === 'active' ? 'bg-emerald-500' : 'bg-rose-500';
    };
@endphp

@push('styles')
<style>
    html {
        scroll-behavior: auto !important;
    }

    html.admin-modal-open,
    body.admin-modal-open {
        overflow: hidden !important;
    }

    .admin-kader-page {
        background:
            radial-gradient(circle at 8% 6%, rgba(16, 185, 129, .13), transparent 28%),
            radial-gradient(circle at 96% 4%, rgba(14, 165, 233, .12), transparent 26%),
            linear-gradient(135deg, #f4fff9 0%, #eef9ff 48%, #f8fafc 100%);
    }

    .admin-kader-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .admin-kader-glass {
        background: rgba(255, 255, 255, .86);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .055);
    }

    .admin-kader-soft {
        background: rgba(248, 250, 252, .72);
        border: 1px solid rgba(226, 232, 240, .88);
    }

    .admin-kader-field {
        width: 100%;
        border-radius: 1rem;
        border: 1px solid rgba(203, 213, 225, .95);
        background: rgba(255, 255, 255, .92);
        padding: .74rem .95rem;
        font-size: .875rem;
        font-weight: 800;
        color: #334155;
        outline: none;
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .admin-kader-field:focus {
        border-color: rgba(16, 185, 129, .55);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .10);
        background: rgba(255, 255, 255, .98);
    }

    .admin-kader-label {
        display: block;
        margin-bottom: .42rem;
        font-size: .68rem;
        font-weight: 950;
        letter-spacing: .13em;
        text-transform: uppercase;
        color: #64748b;
    }

    .admin-kader-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .38rem;
        border-width: 1px;
        border-radius: 999px;
        padding: .38rem .72rem;
        font-size: .68rem;
        font-weight: 950;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .admin-kader-action {
        display: inline-flex;
        min-height: 36px;
        min-width: 36px;
        align-items: center;
        justify-content: center;
        gap: .42rem;
        border-radius: .85rem;
        padding: .5rem .72rem;
        font-size: .74rem;
        font-weight: 950;
        line-height: 1;
        transition: transform .16s ease, background .16s ease, border-color .16s ease, box-shadow .16s ease;
        white-space: nowrap;
    }

    .admin-kader-action:hover {
        transform: translateY(-1px);
    }

    .admin-password-alert {
        border: 1px solid rgba(16, 185, 129, .22);
        background:
            linear-gradient(135deg, rgba(236, 253, 245, .96), rgba(240, 253, 250, .9)),
            rgba(255, 255, 255, .9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, .045);
    }

    .admin-password-box {
        border: 1px solid rgba(16, 185, 129, .22);
        background: rgba(255, 255, 255, .92);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7);
    }

    .admin-password-value {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 1rem;
        line-height: 1.45;
        letter-spacing: .01em;
        word-break: break-all;
        color: #0f172a;
    }

    .admin-kader-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
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

    .admin-kader-modal-backdrop.is-open {
        display: flex !important;
    }

    .admin-kader-modal-card {
        width: min(100%, 500px);
        transform: translateY(12px) scale(.97);
        opacity: 0;
        border-radius: 1.75rem;
        border: 1px solid rgba(255, 255, 255, .78);
        background:
            radial-gradient(circle at 0% 0%, rgba(16, 185, 129, .14), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(14, 165, 233, .12), transparent 34%),
            rgba(255, 255, 255, .96);
        box-shadow: 0 30px 90px rgba(15, 23, 42, .25);
        transition: transform .18s ease, opacity .18s ease;
    }

    .admin-kader-modal-backdrop.is-open .admin-kader-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    @media (max-width: 640px) {
        .admin-password-value {
            font-size: .92rem;
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
<div class="admin-kader-page relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 admin-kader-grid opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1280px] space-y-5">

        <section class="admin-kader-glass overflow-hidden rounded-[1.65rem] border border-white/80 p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">
                        <i class="fas fa-user-nurse"></i>
                        Petugas Lapangan
                    </div>

                    <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-950">
                        Manajemen Akun Kader
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-slate-600">
                        Kelola akun Kader untuk pendataan sasaran, absensi, pengukuran fisik, dan pengajuan laporan Posyandu.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="rounded-2xl border border-slate-200 bg-white/75 px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-slate-400">
                            Data Tampil
                        </p>
                        <p class="mt-1 text-sm font-black text-slate-900">
                            {{ number_format($totalData) }} akun
                        </p>
                    </div>

                    <a href="{{ route('admin.kaders.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5">
                        <i class="fas fa-plus"></i>
                        Tambah Kader
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="admin-kader-glass rounded-[1.4rem] border border-emerald-200 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-emerald-700">
                            Total Kader
                        </p>
                        <p class="mt-1 text-3xl font-black text-slate-950">
                            {{ number_format($stats['total'] ?? 0) }}
                        </p>
                        <p class="text-sm font-semibold text-slate-500">
                            Seluruh akun
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="admin-kader-glass rounded-[1.4rem] border border-sky-200 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-sky-700">
                            Kader Aktif
                        </p>
                        <p class="mt-1 text-3xl font-black text-slate-950">
                            {{ number_format($stats['aktif'] ?? 0) }}
                        </p>
                        <p class="text-sm font-semibold text-slate-500">
                            Siap bertugas
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-500 text-white shadow-lg shadow-sky-500/20">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="admin-kader-glass rounded-[1.4rem] border border-rose-200 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-rose-700">
                            Nonaktif
                        </p>
                        <p class="mt-1 text-3xl font-black text-slate-950">
                            {{ number_format($stats['nonaktif'] ?? 0) }}
                        </p>
                        <p class="text-sm font-semibold text-slate-500">
                            Akun dibatasi
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-500 text-white shadow-lg shadow-rose-500/20">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </section>

        @if(session('success') || session('warning') || $errors->any())
            <section class="space-y-3">
                @if(session('success'))
                    <div class="admin-password-alert rounded-[1.35rem] p-4">
                        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(320px,420px)] xl:items-center">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                                    <i class="fas fa-check-circle"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-black leading-6 text-emerald-800">
                                        {{ session('success') }}
                                    </p>

                                    @if(session('generated_password') || session('reset_password'))
                                        <p class="mt-1 max-w-2xl text-xs font-semibold leading-5 text-emerald-700">
                                            Password baru hanya ditampilkan sekali. Salin sekarang sebelum meninggalkan halaman.
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if(session('generated_password') || session('reset_password'))
                                <div class="admin-password-box rounded-[1.15rem] p-3.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[11px] font-black uppercase tracking-[.1em] text-emerald-700">
                                                Password Baru
                                            </p>

                                            <p id="generatedPasswordText" class="admin-password-value mt-1.5 font-black">
                                                {{ session('generated_password') ?? session('reset_password') }}
                                            </p>

                                            <p class="mt-2 truncate text-xs font-bold text-slate-500">
                                                {{ session('user_name') ?? session('reset_name') ?? '-' }}
                                                @if(session('user_email') || session('reset_email'))
                                                    <span class="mx-1 text-slate-300">•</span>
                                                    {{ session('user_email') ?? session('reset_email') }}
                                                @endif
                                            </p>
                                        </div>

                                        <button type="button"
                                                id="copyGeneratedPassword"
                                                class="shrink-0 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700 transition hover:bg-emerald-100">
                                            Salin
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="rounded-[1.35rem] border border-amber-200 bg-amber-50/90 p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-amber-600 shadow-sm">
                                <i class="fas fa-circle-info"></i>
                            </div>

                            <p class="text-sm font-black leading-6 text-amber-800">
                                {{ session('warning') }}
                            </p>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-[1.35rem] border border-rose-200 bg-rose-50/90 p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-rose-600 shadow-sm">
                                <i class="fas fa-triangle-exclamation"></i>
                            </div>

                            <div>
                                <p class="text-sm font-black text-rose-800">
                                    Terjadi kesalahan.
                                </p>

                                <ul class="mt-2 list-inside list-disc text-sm font-semibold text-rose-700">
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

        <section class="admin-kader-glass rounded-[1.5rem] border border-white/80 p-4">
            <form method="GET"
                  action="{{ route('admin.kaders.index') }}"
                  class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_140px_auto_auto] lg:items-end">
                <div>
                    <label for="search" class="admin-kader-label">Cari Kader</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="search"
                               type="search"
                               name="search"
                               value="{{ $search }}"
                               autocomplete="off"
                               class="admin-kader-field pl-11"
                               placeholder="Cari nama, email, NIK, atau telepon">
                    </div>
                </div>

                <div>
                    <label for="status" class="admin-kader-label">Status</label>
                    <select id="status" name="status" class="admin-kader-field">
                        <option value="semua" @selected($status === 'semua')>Semua</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                    </select>
                </div>

                <div>
                    <label for="per_page" class="admin-kader-label">Tampil</label>
                    <select id="per_page" name="per_page" class="admin-kader-field">
                        @foreach([5, 10, 12, 15, 25] as $option)
                            <option value="{{ $option }}" @selected((int) $perPage === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="inline-flex h-[45px] items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>

                <a href="{{ route('admin.kaders.index') }}"
                   class="inline-flex h-[45px] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-rotate-left"></i>
                    Reset
                </a>
            </form>
        </section>

        <section class="admin-kader-glass rounded-[1.5rem] border border-white/80 p-4">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                        Direktori Kader
                    </p>

                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                        Data Akun Kader
                    </h2>

                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        {{ $rangeText }}
                    </p>
                </div>

                <span class="inline-flex w-fit items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-black uppercase tracking-[.14em] text-emerald-700">
                    {{ number_format($totalData) }} Data
                </span>
            </div>

            <div class="hidden overflow-hidden rounded-[1.3rem] border border-slate-200 bg-white/85 lg:block">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/90">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-[.14em] text-slate-400">
                                Profil Kader
                            </th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-[.14em] text-slate-400">
                                NIK
                            </th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-[.14em] text-slate-400">
                                Kontak
                            </th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-[.14em] text-slate-400">
                                Status
                            </th>
                            <th class="px-4 py-3 text-right text-[11px] font-black uppercase tracking-[.14em] text-slate-400">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($kaders ?? [] as $kader)
                            @php
                                $name = $getName($kader);
                                $nik = $getNik($kader);
                                $phone = $getPhone($kader);
                                $jabatan = $getJabatan($kader);
                                $wilayah = $getWilayah($kader);
                            @endphp

                            <tr class="transition hover:bg-emerald-50/35">
                                <td class="px-4 py-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-sm font-black text-white shadow-lg shadow-emerald-500/20">
                                            {{ $getInitial($name) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-slate-950">
                                                {{ $name }}
                                            </p>

                                            <p class="mt-1 truncate text-xs font-bold text-slate-500">
                                                {{ $jabatan }} • {{ $wilayah }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <p class="font-mono text-sm font-black text-slate-700">
                                        {{ $nik }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    <p class="max-w-[230px] truncate text-sm font-black text-slate-700">
                                        {{ $kader->email }}
                                    </p>

                                    <p class="mt-1 text-xs font-bold text-slate-500">
                                        {{ $phone }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    <span class="admin-kader-chip {{ $statusBadge($kader->status) }}">
                                        <span class="h-2 w-2 rounded-full {{ $statusDot($kader->status) }}"></span>
                                        {{ $statusText($kader->status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.kaders.show', $kader->id) }}"
                                           class="admin-kader-action border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('admin.kaders.edit', $kader->id) }}"
                                           class="admin-kader-action border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100"
                                           title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        <form action="{{ route('admin.kaders.reset-password', $kader->id) }}"
                                              method="POST"
                                              data-admin-action-form
                                              data-action-type="password"
                                              data-action-title="Buat password baru?"
                                              data-action-message="Sistem akan membuat password acak baru untuk akun {{ $name }}. Password lama tidak dapat digunakan lagi."
                                              data-action-button="Buat Password">
                                            @csrf

                                            <button type="submit"
                                                    class="admin-kader-action border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100"
                                                    title="Buat Password Baru">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.kaders.destroy', $kader->id) }}"
                                              method="POST"
                                              data-admin-action-form
                                              data-action-type="delete"
                                              data-action-title="Hapus akun kader?"
                                              data-action-message="Akun {{ $name }} akan dihapus jika belum memiliki riwayat operasional. Jika sudah memiliki riwayat, sistem akan menolak penghapusan dan menyarankan nonaktifkan akun."
                                              data-action-button="Hapus Akun">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="admin-kader-action border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100"
                                                    title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                        <i class="fas fa-folder-open"></i>
                                    </div>

                                    <h3 class="mt-4 text-base font-black text-slate-950">
                                        Belum Ada Data Kader
                                    </h3>

                                    <p class="mt-2 text-sm font-semibold text-slate-500">
                                        Tambahkan akun Kader agar pendataan Posyandu dapat digunakan.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 lg:hidden">
                @forelse($kaders ?? [] as $kader)
                    @php
                        $name = $getName($kader);
                        $nik = $getNik($kader);
                        $phone = $getPhone($kader);
                        $jabatan = $getJabatan($kader);
                        $wilayah = $getWilayah($kader);
                    @endphp

                    <article class="rounded-[1.25rem] border border-slate-200 bg-white/85 p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-sm font-black text-white shadow-lg shadow-emerald-500/20">
                                {{ $getInitial($name) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-slate-950">
                                            {{ $name }}
                                        </p>

                                        <p class="mt-1 truncate text-xs font-bold text-slate-500">
                                            {{ $jabatan }} • {{ $wilayah }}
                                        </p>
                                    </div>

                                    <span class="admin-kader-chip {{ $statusBadge($kader->status) }}">
                                        <span class="h-2 w-2 rounded-full {{ $statusDot($kader->status) }}"></span>
                                        {{ $statusText($kader->status) }}
                                    </span>
                                </div>

                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <div class="admin-kader-soft rounded-2xl p-3">
                                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">
                                            NIK
                                        </p>

                                        <p class="mt-1 font-mono text-sm font-black text-slate-700">
                                            {{ $nik }}
                                        </p>
                                    </div>

                                    <div class="admin-kader-soft rounded-2xl p-3">
                                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">
                                            Kontak
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-700">
                                            {{ $phone }}
                                        </p>
                                    </div>
                                </div>

                                <p class="mt-3 truncate text-xs font-bold text-slate-500">
                                    {{ $kader->email }}
                                </p>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <a href="{{ route('admin.kaders.show', $kader->id) }}"
                                       class="admin-kader-action border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                                        <i class="fas fa-eye"></i>
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.kaders.edit', $kader->id) }}"
                                       class="admin-kader-action border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100">
                                        <i class="fas fa-pen"></i>
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.kaders.reset-password', $kader->id) }}"
                                          method="POST"
                                          data-admin-action-form
                                          data-action-type="password"
                                          data-action-title="Buat password baru?"
                                          data-action-message="Sistem akan membuat password acak baru untuk akun {{ $name }}. Password lama tidak dapat digunakan lagi."
                                          data-action-button="Buat Password">
                                        @csrf

                                        <button type="submit"
                                                class="admin-kader-action w-full border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100">
                                            <i class="fas fa-key"></i>
                                            Password
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.kaders.destroy', $kader->id) }}"
                                          method="POST"
                                          data-admin-action-form
                                          data-action-type="delete"
                                          data-action-title="Hapus akun kader?"
                                          data-action-message="Akun {{ $name }} akan dihapus jika belum memiliki riwayat operasional. Jika sudah memiliki riwayat, sistem akan menolak penghapusan dan menyarankan nonaktifkan akun."
                                          data-action-button="Hapus Akun">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="admin-kader-action w-full border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">
                                            <i class="fas fa-trash-alt"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.25rem] border border-dashed border-emerald-200 bg-emerald-50/70 p-8 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-600">
                            <i class="fas fa-folder-open"></i>
                        </div>

                        <h3 class="mt-4 text-base font-black text-slate-950">
                            Belum Ada Data Kader
                        </h3>

                        <p class="mt-2 text-sm font-semibold text-slate-500">
                            Tambahkan akun Kader agar pendataan Posyandu dapat digunakan.
                        </p>
                    </div>
                @endforelse
            </div>

            @if(isset($kaders) && method_exists($kaders, 'hasPages') && $kaders->hasPages())
                <div class="mt-4 rounded-2xl border border-slate-200 bg-white/75 p-3">
                    {{ $kaders->withQueryString()->links() }}
                </div>
            @endif
        </section>
    </div>

    <div id="adminKaderActionModal" class="admin-kader-modal-backdrop" aria-hidden="true">
        <div class="admin-kader-modal-card p-6">
            <div class="flex gap-4">
                <div id="adminKaderActionIconBox" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-500/20">
                    <i id="adminKaderActionIcon" class="fas fa-check-circle text-xl"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p id="adminKaderActionEyebrow" class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                        Konfirmasi
                    </p>

                    <h3 id="adminKaderActionTitle" class="mt-1 text-lg font-black text-slate-950">
                        Konfirmasi aksi?
                    </h3>

                    <p id="adminKaderActionMessage" class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Pastikan data sudah benar sebelum melanjutkan.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button"
                        id="adminKaderActionCancel"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="button"
                        id="adminKaderActionSubmit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
                    <i class="fas fa-check"></i>
                    Lanjutkan
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

    let selectedForm = null;

    const modal = document.querySelector('#adminKaderActionModal');
    const modalTitle = document.querySelector('#adminKaderActionTitle');
    const modalMessage = document.querySelector('#adminKaderActionMessage');
    const modalEyebrow = document.querySelector('#adminKaderActionEyebrow');
    const modalIconBox = document.querySelector('#adminKaderActionIconBox');
    const modalIcon = document.querySelector('#adminKaderActionIcon');
    const modalCancel = document.querySelector('#adminKaderActionCancel');
    const modalSubmit = document.querySelector('#adminKaderActionSubmit');

    const passwordText = document.querySelector('#generatedPasswordText');
    const copyPasswordButton = document.querySelector('#copyGeneratedPassword');

    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    function lockBody() {
        document.documentElement.classList.add('admin-modal-open');
        document.body.classList.add('admin-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('admin-modal-open');
        document.body.classList.remove('admin-modal-open');
    }

    function setSubmitLoading(text) {
        if (!modalSubmit) {
            return;
        }

        modalSubmit.disabled = true;
        modalSubmit.classList.add('opacity-70', 'cursor-not-allowed');
        modalSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + text;
    }

    function configureDialog(form) {
        const type = form.dataset.actionType || 'default';
        const isDelete = type === 'delete';
        const isPassword = type === 'password';

        modalTitle.textContent = form.dataset.actionTitle || 'Konfirmasi aksi?';
        modalMessage.textContent = form.dataset.actionMessage || 'Pastikan data sudah benar sebelum melanjutkan.';

        modalIconBox.className = 'flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] text-white shadow-lg';

        modalSubmit.disabled = false;
        modalSubmit.classList.remove('opacity-70', 'cursor-not-allowed');

        if (isDelete) {
            modalEyebrow.textContent = 'Konfirmasi Penghapusan';
            modalEyebrow.className = 'text-[11px] font-black uppercase tracking-[.18em] text-rose-700';
            modalIconBox.classList.add('bg-gradient-to-br', 'from-rose-500', 'to-orange-500', 'shadow-rose-500/20');
            modalIcon.className = 'fas fa-triangle-exclamation text-xl';
            modalSubmit.className = 'inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-orange-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-rose-400/20 transition hover:-translate-y-0.5';
            modalSubmit.innerHTML = '<i class="fas fa-trash-alt"></i> ' + (form.dataset.actionButton || 'Hapus Akun');
            return;
        }

        if (isPassword) {
            modalEyebrow.textContent = 'Keamanan Akun';
            modalEyebrow.className = 'text-[11px] font-black uppercase tracking-[.18em] text-amber-700';
            modalIconBox.classList.add('bg-gradient-to-br', 'from-amber-500', 'to-orange-500', 'shadow-amber-500/20');
            modalIcon.className = 'fas fa-key text-xl';
            modalSubmit.className = 'inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-amber-400/20 transition hover:-translate-y-0.5';
            modalSubmit.innerHTML = '<i class="fas fa-key"></i> ' + (form.dataset.actionButton || 'Buat Password');
            return;
        }

        modalEyebrow.textContent = 'Konfirmasi';
        modalEyebrow.className = 'text-[11px] font-black uppercase tracking-[.18em] text-emerald-700';
        modalIconBox.classList.add('bg-gradient-to-br', 'from-emerald-500', 'to-teal-500', 'shadow-emerald-500/20');
        modalIcon.className = 'fas fa-check-circle text-xl';
        modalSubmit.className = 'inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5';
        modalSubmit.innerHTML = '<i class="fas fa-check"></i> ' + (form.dataset.actionButton || 'Lanjutkan');
    }

    function openDialog(form) {
        if (!modal) {
            HTMLFormElement.prototype.submit.call(form);
            return;
        }

        selectedForm = form;
        configureDialog(form);

        lockBody();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeDialog() {
        selectedForm = null;

        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-admin-action-form]');

        if (!form) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        openDialog(form);
    }, true);

    if (modalCancel) {
        modalCancel.addEventListener('click', closeDialog);
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeDialog();
            }
        });
    }

    if (modalSubmit) {
        modalSubmit.addEventListener('click', function () {
            if (!selectedForm) {
                closeDialog();
                return;
            }

            const type = selectedForm.dataset.actionType || 'default';

            if (type === 'delete') {
                setSubmitLoading('Menghapus...');
            } else if (type === 'password') {
                setSubmitLoading('Membuat...');
            } else {
                setSubmitLoading('Memproses...');
            }

            HTMLFormElement.prototype.submit.call(selectedForm);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            closeDialog();
        }
    });

    if (copyPasswordButton && passwordText) {
        copyPasswordButton.addEventListener('click', function () {
            const value = passwordText.textContent.trim();

            if (!value) {
                return;
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(function () {
                    copyPasswordButton.textContent = 'Tersalin';
                    setTimeout(function () {
                        copyPasswordButton.textContent = 'Salin';
                    }, 1400);
                });

                return;
            }

            const tempInput = document.createElement('textarea');
            tempInput.value = value;
            tempInput.setAttribute('readonly', 'readonly');
            tempInput.style.position = 'fixed';
            tempInput.style.left = '-9999px';
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);

            copyPasswordButton.textContent = 'Tersalin';
            setTimeout(function () {
                copyPasswordButton.textContent = 'Salin';
            }, 1400);
        });
    }
})();
</script>
@endpush