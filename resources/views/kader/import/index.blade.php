@extends('layouts.kader')

@section('title', 'Pusat Import Data')
@section('page-name', 'Pusat Import Data')

@php
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $typeMeta = [
        'balita' => ['label' => 'Balita / Anak', 'icon' => 'fa-child-reaching', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate', 'color' => 'text-sky-600', 'bg' => 'bg-sky-50'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
    ];

    $latestImports = $latestImports ?? collect();

    $statusMeta = [
        'completed' => ['label' => 'Berhasil', 'class' => 'border-emerald-100 bg-emerald-50 text-emerald-700', 'icon' => 'fa-circle-check'],
        'processing' => ['label' => 'Diproses', 'class' => 'border-amber-100 bg-amber-50 text-amber-700', 'icon' => 'fa-clock'],
        'failed' => ['label' => 'Gagal', 'class' => 'border-rose-100 bg-rose-50 text-rose-700', 'icon' => 'fa-circle-xmark'],
    ];
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image: radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .animate-pop-in {
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 2rem; 
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.08);
        border-color: rgba(16, 185, 129, 0.3);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- HERO WIDGET --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 lg:items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-full shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-server"></i>
                        Pusat Monitoring
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Dashboard Import Data
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Pantau seluruh aktivitas unggahan data sasaran (Balita, Remaja, Lansia) oleh Kader. Periksa status sinkronisasi, log kegagalan, dan laporan masuknya data ke dalam sistem.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto shrink-0 justify-center">
                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create') }}" class="btn-pill bg-white hover:bg-emerald-50 text-emerald-600 px-8 py-4 text-sm font-black shadow-[0_8px_20px_rgba(255,255,255,0.3)] flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
                        <i class="fa-solid fa-rocket"></i> Import Data Baru
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- STATISTIK GRID --}}
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="widget-card p-5 card-hover flex justify-between items-center">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total Import</p>
                <h2 class="mt-1 text-3xl font-black text-slate-800">{{ $statTotal ?? 0 }}</h2>
                <p class="text-xs font-bold text-slate-400 mt-1">Seluruh aktivitas</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-database"></i>
            </div>
        </div>

        <div class="widget-card p-5 card-hover flex justify-between items-center">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[.13em] text-emerald-600">Berhasil</p>
                <h2 class="mt-1 text-3xl font-black text-slate-800">{{ $statBerhasil ?? 0 }}</h2>
                <p class="text-xs font-bold text-slate-400 mt-1">Data valid masuk</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-500 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="widget-card p-5 card-hover flex justify-between items-center">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[.13em] text-amber-600">Diproses</p>
                <h2 class="mt-1 text-3xl font-black text-slate-800">{{ $statProcessing ?? 0 }}</h2>
                <p class="text-xs font-bold text-slate-400 mt-1">Dalam antrean</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-500 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>

        <div class="widget-card p-5 card-hover flex justify-between items-center">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[.13em] text-rose-600">Gagal</p>
                <h2 class="mt-1 text-3xl font-black text-slate-800">{{ $statGagal ?? 0 }}</h2>
                <p class="text-xs font-bold text-slate-400 mt-1">Ditolak sistem</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-500 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
        </div>
    </section>

    {{-- RIWAYAT IMPORT --}}
    <section class="widget-card p-6 sm:p-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-50 pb-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shrink-0 shadow-inner">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900">Aktivitas Import Terakhir</h2>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">
                        Pantau file yang baru saja diunggah ke dalam sistem.
                    </p>
                </div>
            </div>

            @if($routeHas('kader.import.history'))
                <a href="{{ route('kader.import.history') }}" class="btn-pill border border-slate-200 bg-white hover:bg-slate-50 px-5 py-2.5 text-[11px] font-black uppercase tracking-widest text-slate-600 shadow-sm flex items-center gap-2">
                    Riwayat Lengkap <i class="fa-solid fa-arrow-right"></i>
                </a>
            @endif
        </div>

        @if(isset($latestImports) && $latestImports->count())
            <div class="space-y-3">
                @foreach($latestImports as $import)
                    @php
                        $meta = $statusMeta[$import->status] ?? $statusMeta['processing'];
                        $jenisLabel = $typeMeta[$import->jenis_data]['label'] ?? ucfirst($import->jenis_data ?? '-');
                        $jenisIcon = $typeMeta[$import->jenis_data]['icon'] ?? 'fa-file-excel';
                        $jenisColor = $typeMeta[$import->jenis_data]['color'] ?? 'text-slate-600';
                        $jenisBg = $typeMeta[$import->jenis_data]['bg'] ?? 'bg-slate-50';
                    @endphp

                    <div class="card-hover grid gap-4 rounded-[1.5rem] border border-slate-100 bg-slate-50/30 p-4 lg:grid-cols-[1fr_180px_160px_auto] lg:items-center">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-inner {{ $jenisBg }} {{ $jenisColor }}">
                                <i class="fa-solid {{ $jenisIcon }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-slate-900" title="{{ $import->nama_file }}">{{ $import->nama_file }}</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-500">
                                    {{ $import->created_at?->translatedFormat('d F Y, H:i') ?? '-' }} WIB
                                </p>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Target Sasaran</p>
                            <p class="mt-0.5 text-sm font-black text-slate-800">{{ $jenisLabel }}</p>
                        </div>

                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $meta['class'] }}">
                                <i class="fa-solid {{ $meta['icon'] }}"></i>
                                {{ $meta['label'] }}
                            </span>
                        </div>

                        @if($routeHas('kader.import.show'))
                            <div class="flex justify-end">
                                <a href="{{ route('kader.import.show', $import->id) }}" class="btn-pill flex items-center justify-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-emerald-600 px-5 py-2.5 text-xs font-black shadow-sm w-full sm:w-auto">
                                    <i class="fa-solid fa-eye"></i> Detail / Log
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-[2rem] border-2 border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-xl font-black text-slate-300 shadow-sm border border-slate-100 mb-4">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <h3 class="text-base font-black text-slate-800">Database Masih Bersih</h3>
                <p class="mx-auto mt-1 max-w-sm text-xs font-semibold leading-relaxed text-slate-500">
                    Belum ada satupun aktivitas import data. Gunakan tombol "Jalankan Import Baru" di atas untuk mulai memasukkan data massal.
                </p>
            </div>
        @endif
    </section>
</div>
@endsection