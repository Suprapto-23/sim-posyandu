@extends('layouts.kader')

@section('title', 'Riwayat Imunisasi')

@php
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);
@endphp

@push('styles')
<style>
    .imunisasi-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .imunisasi-page::before {
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

    .input-soft {
        border: 1px solid rgba(226,232,240,.9);
        background: #fff;
        outline: none;
    }

    .input-soft:focus {
        border-color: rgba(16,185,129,.38);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
    }

    .table-scroll {
        max-height: 520px;
        overflow: auto;
        overscroll-behavior: contain;
    }

    .table-scroll::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .table-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }

    .table-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }
</style>
@endpush

@section('content')
<div class="imunisasi-page space-y-5">

    <section class="hero-soft rounded-[28px] p-5 sm:p-6">
        <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-eye"></i>
                    Read Only Kader
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Riwayat Imunisasi Balita
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Kader hanya melihat arsip imunisasi yang sudah dicatat oleh Bidan. Data ini digunakan sebagai pemantauan dan bahan laporan, bukan untuk input tindakan medis.
                </p>
            </div>

            @if($routeHas('kader.dashboard'))
                <a href="{{ route('kader.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white">
                    <i class="fa-solid fa-chart-simple"></i>
                    Dashboard
                </a>
            @endif
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="panel-soft rounded-[24px] p-5">
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total Arsip</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statTotal ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Seluruh data imunisasi balita</p>
        </div>

        <div class="panel-soft rounded-[24px] border-emerald-100 bg-emerald-50 p-5">
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-emerald-700">Bulan Ini</p>
            <h2 class="mt-2 text-3xl font-black text-emerald-700">{{ $statBulanIni ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-emerald-600">Dosis tercatat bulan berjalan</p>
        </div>

        <div class="panel-soft rounded-[24px] border-amber-100 bg-amber-50 p-5">
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-amber-700">Total Sasaran</p>
            <h2 class="mt-2 text-3xl font-black text-amber-700">{{ $statBalita ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-amber-600">Balita terdaftar di data sasaran</p>
        </div>
    </section>

    <section class="panel-soft rounded-[26px] p-4 sm:p-5">
        <form method="GET" action="{{ route('kader.imunisasi.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_220px_auto]">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Cari Data</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search ?? '' }}"
                        class="input-soft h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700"
                        placeholder="Cari nama, NIK, vaksin, atau batch..."
                    >
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Bulan</label>
                <input
                    type="month"
                    name="bulan"
                    value="{{ $bulan ?? now()->format('Y-m') }}"
                    class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700"
                >
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="h-12 rounded-2xl bg-emerald-600 px-5 text-sm font-black text-white shadow-[0_10px_20px_rgba(5,150,105,.16)]">
                    <i class="fa-solid fa-filter mr-1"></i>
                    Filter
                </button>

                <a href="{{ route('kader.imunisasi.index') }}" class="grid h-12 w-12 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </section>

    <section class="panel-soft rounded-[26px] p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Daftar Riwayat Imunisasi</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Data bersifat read-only untuk Kader.
                </p>
            </div>

            <span class="w-fit rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">
                {{ $imunisasis->total() ?? 0 }} Data
            </span>
        </div>

        @if($imunisasis->count())
            <div class="table-scroll">
                <div class="min-w-[980px] space-y-3">
                    @foreach($imunisasis as $imun)
                        @php
                            $theme = $imun->badge_theme;
                        @endphp

                        <div class="grid grid-cols-[1.1fr_1.35fr_1.25fr_1fr_auto] items-center gap-3 rounded-[22px] border border-slate-100 bg-white p-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tanggal</p>
                                <p class="mt-1 text-sm font-black text-slate-900">{{ $imun->tanggal_label }}</p>
                                <p class="mt-1 text-xs font-bold text-slate-400">{{ $imun->jam_label }}</p>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                                    <i class="fa-solid fa-child-reaching"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900">{{ $imun->nama_penerima }}</p>
                                    <p class="mt-1 text-xs font-bold text-slate-400">{{ $imun->nik_penerima }}</p>
                                </div>
                            </div>

                            <div>
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $theme['class'] }}">
                                    <i class="fa-solid {{ $theme['icon'] }}"></i>
                                    {{ $theme['label'] }}
                                </span>
                                <p class="mt-2 text-sm font-black text-slate-900">{{ $imun->vaksin_label }}</p>
                                <p class="mt-1 text-xs font-bold text-slate-400">{{ $imun->jenis_imunisasi }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Dicatat Oleh</p>
                                <p class="mt-1 text-sm font-black text-slate-900">{{ $imun->nama_petugas }}</p>
                                <p class="mt-1 text-xs font-bold text-slate-400">Bidan / Petugas</p>
                            </div>

                            <a href="{{ route('kader.imunisasi.show', $imun->id) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white">
                                <i class="fa-solid fa-eye"></i>
                                Detail
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($imunisasis->hasPages())
                <div class="mt-5">
                    {{ $imunisasis->links() }}
                </div>
            @endif
        @else
            <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white text-slate-400">
                    <i class="fa-solid fa-folder-open text-xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-black text-slate-900">Riwayat Imunisasi Kosong</h3>
                <p class="mt-2 text-sm font-bold text-slate-400">
                    Belum ada data imunisasi yang cocok dengan filter pencarian.
                </p>
            </div>
        @endif
    </section>
</div>
@endsection