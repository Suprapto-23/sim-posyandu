@extends('layouts.kader')

@section('title', 'Detail Riwayat Imunisasi')

@php
    use Illuminate\Support\Facades\Route;

    $theme = $imunisasi->badge_theme;
    $routeHas = fn ($name) => Route::has($name);
@endphp

@push('styles')
<style>
    .detail-imunisasi {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .detail-imunisasi::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.12), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(245,158,11,.10), transparent 26%),
            linear-gradient(135deg, #f8fffc, #f8fafc 58%, #fffaf0);
    }

    .panel-soft {
        border: 1px solid rgba(226,232,240,.88);
        background: rgba(255,255,255,.95);
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
    }

    .hero-soft {
        border: 1px solid rgba(167,243,208,.7);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.14), transparent 30%),
            radial-gradient(circle at 88% 16%, rgba(245,158,11,.12), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.96), rgba(236,253,245,.76));
    }

    .info-card {
        border: 1px solid rgba(226,232,240,.86);
        background: #ffffff;
        transition: border-color .15s ease, background .15s ease;
    }

    .info-card:hover {
        border-color: rgba(16,185,129,.22);
        background: #fbfffd;
    }

    .readonly-box {
        border: 1px solid rgba(16,185,129,.16);
        background: #ecfdf5;
        color: #047857;
    }

    .timeline-dot {
        box-shadow: 0 0 0 6px rgba(16,185,129,.10);
    }
</style>
@endpush

@section('content')
<div class="detail-imunisasi space-y-5">

    {{-- HERO --}}
    <section class="hero-soft rounded-[28px] p-5 sm:p-6">
        <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-eye"></i>
                    Read Only Kader
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Detail Riwayat Imunisasi
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Halaman ini hanya menampilkan riwayat imunisasi balita yang dicatat oleh Bidan.
Kader menggunakan data ini untuk pemantauan dan bahan laporan, bukan untuk menambah atau mengubah data imunisasi.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                @if($routeHas('kader.imunisasi.index'))
                    <a href="{{ route('kader.imunisasi.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white px-5 py-3 text-sm font-black text-emerald-700">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                @endif

                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white">
                        <i class="fa-solid fa-chart-simple"></i>
                        Dashboard
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- RINGKASAN UTAMA --}}
    <section class="panel-soft rounded-[28px] p-5 sm:p-6">
        <div class="grid gap-5 xl:grid-cols-[1.2fr_.8fr] xl:items-center">
            <div class="flex items-start gap-4">
                <div class="grid h-16 w-16 shrink-0 place-items-center rounded-3xl bg-emerald-50 text-emerald-700">
                    <i class="fa-solid fa-syringe text-xl"></i>
                </div>

                <div class="min-w-0">
                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $theme['class'] }}">
                        <i class="fa-solid {{ $theme['icon'] }}"></i>
                        {{ $theme['label'] }}
                    </span>

                    <h2 class="mt-3 text-2xl font-black tracking-[-.04em] text-slate-900">
                        {{ $imunisasi->vaksin_label }}
                    </h2>

                    <p class="mt-2 text-sm font-bold text-slate-500">
                        {{ $imunisasi->jenis_imunisasi }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tanggal</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->tanggal_label }}</p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Jam Input</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->jam_label }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- INFO READ ONLY --}}
    <section class="readonly-box rounded-[24px] p-4">
        <div class="flex items-start gap-3">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-emerald-700">
                <i class="fa-solid fa-lock"></i>
            </div>

            <div>
                <h3 class="text-sm font-black">Data Terkunci untuk Kader</h3>
                <p class="mt-1 text-xs font-bold leading-5">
                    Kader hanya dapat melihat detail imunisasi. Perubahan, input, dan koreksi data imunisasi dilakukan oleh Bidan melalui modul Bidan.
                    Laporan cetak dibuat melalui menu Laporan Kader atau laporan global agar formatnya lebih resmi.
                </p>
            </div>
        </div>
    </section>

    {{-- DETAIL --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

        {{-- IDENTITAS --}}
        <div class="panel-soft rounded-[28px] p-5 xl:col-span-7">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Identitas Sasaran</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Data balita penerima imunisasi.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Balita</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->nama_penerima }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">NIK</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->nik_penerima }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Kategori Sasaran</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->kategori_sasaran }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Dicatat Bidan</p>
<p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->nama_petugas }}</p>
                </div>
            </div>
        </div>

        {{-- DETAIL VAKSIN --}}
        <div class="panel-soft rounded-[28px] p-5 xl:col-span-5">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Detail Vaksin</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Informasi tindakan imunisasi.
                </p>
            </div>

            <div class="space-y-3">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Jenis Imunisasi</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->jenis_imunisasi }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Vaksin</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->vaksin_label }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nomor Batch</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->batch_label }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- TIMELINE --}}
    <section class="panel-soft rounded-[28px] p-5">
        <div class="mb-4">
            <h3 class="text-lg font-black text-slate-900">Alur Data</h3>
            <p class="mt-1 text-xs font-bold text-slate-400">
                Menjelaskan posisi data imunisasi dalam sistem.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                <div class="mb-3 grid h-10 w-10 place-items-center rounded-2xl bg-white text-emerald-700 timeline-dot">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Dicatat Bidan</h4>
<p class="mt-1 text-xs font-bold leading-5 text-slate-500">
    Data imunisasi dibuat dan dikelola oleh Bidan.
</p>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                <div class="mb-3 grid h-10 w-10 place-items-center rounded-2xl bg-white text-amber-700">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Dipantau Kader</h4>
<p class="mt-1 text-xs font-bold leading-5 text-slate-500">
    Kader melihat riwayat imunisasi untuk pemantauan sasaran balita.
</p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="mb-3 grid h-10 w-10 place-items-center rounded-2xl bg-white text-slate-600">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Masuk Laporan</h4>
                <p class="mt-1 text-xs font-bold leading-5 text-slate-500">
                    Rekap imunisasi dicetak melalui modul laporan, bukan dari halaman detail satu data.
                </p>
            </div>
        </div>
    </section>

    {{-- CATATAN --}}
    <section class="panel-soft rounded-[28px] p-5">
        <div class="mb-4">
            <h3 class="text-lg font-black text-slate-900">Catatan Bidan</h3>
            <p class="mt-1 text-xs font-bold text-slate-400">
                Catatan tambahan dari Bidan.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-sm font-bold leading-6 text-slate-600">
                {{ $imunisasi->catatan_label }}
            </p>
        </div>
    </section>

    {{-- ACTION BAWAH --}}
    <section class="panel-soft rounded-[24px] p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900">Detail imunisasi selesai ditampilkan</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Untuk cetak data, gunakan modul laporan agar rekap lebih lengkap dan formatnya resmi.
                </p>
            </div>

            @if($routeHas('kader.imunisasi.index'))
                <a href="{{ route('kader.imunisasi.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white">
                    <i class="fa-solid fa-list"></i>
                    Riwayat Imunisasi
                </a>
            @endif
        </div>
    </section>
</div>
@endsection