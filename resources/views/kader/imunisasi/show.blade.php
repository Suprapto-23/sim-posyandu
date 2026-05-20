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

    @media print {
        .no-print {
            display: none !important;
        }

        body * {
            visibility: hidden !important;
        }

        #printArea, #printArea * {
            visibility: visible !important;
        }

        #printArea {
            position: absolute;
            inset: 0;
            width: 100%;
            background: white !important;
        }
    }
</style>
@endpush

@section('content')
<div class="detail-imunisasi space-y-5">

    <section class="hero-soft rounded-[28px] p-5 sm:p-6 no-print">
        <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-file-medical"></i>
                    Detail Read Only
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Detail Riwayat Imunisasi
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Arsip imunisasi balita yang dicatat oleh Bidan atau petugas kesehatan. Kader hanya melihat data untuk pemantauan dan laporan.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                @if($routeHas('kader.imunisasi.index'))
                    <a href="{{ route('kader.imunisasi.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white px-5 py-3 text-sm font-black text-emerald-700">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                @endif

                <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white">
                    <i class="fa-solid fa-print"></i>
                    Cetak
                </button>
            </div>
        </div>
    </section>

    <div id="printArea" class="space-y-5">
        <section class="panel-soft rounded-[28px] p-5 sm:p-6">
            <div class="grid gap-5 xl:grid-cols-[1.2fr_.8fr] xl:items-center">
                <div class="flex items-start gap-4">
                    <div class="grid h-16 w-16 shrink-0 place-items-center rounded-3xl bg-emerald-50 text-emerald-700">
                        <i class="fa-solid fa-syringe text-xl"></i>
                    </div>

                    <div>
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

        <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">
            <div class="panel-soft rounded-[28px] p-5 xl:col-span-7">
                <h3 class="text-lg font-black text-slate-900">Identitas Sasaran</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Data balita penerima imunisasi.
                </p>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Balita</p>
                        <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->nama_penerima }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">NIK</p>
                        <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->nik_penerima }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Kategori</p>
                        <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->kategori_sasaran }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Dicatat Oleh</p>
                        <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->nama_petugas }}</p>
                    </div>
                </div>
            </div>

            <div class="panel-soft rounded-[28px] p-5 xl:col-span-5">
                <h3 class="text-lg font-black text-slate-900">Detail Vaksin</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Informasi tindakan imunisasi.
                </p>

                <div class="mt-4 space-y-3">
                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Jenis Imunisasi</p>
                        <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->jenis_imunisasi }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Vaksin</p>
                        <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->vaksin_label }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nomor Batch</p>
                        <p class="mt-2 text-sm font-black text-slate-900">{{ $imunisasi->batch_label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel-soft rounded-[28px] p-5">
            <h3 class="text-lg font-black text-slate-900">Catatan</h3>
            <p class="mt-1 text-xs font-bold text-slate-400">
                Catatan tambahan dari petugas kesehatan.
            </p>

            <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-sm font-bold leading-6 text-slate-600">
                    {{ $imunisasi->catatan_label }}
                </p>
            </div>
        </section>
    </div>
</div>
@endsection