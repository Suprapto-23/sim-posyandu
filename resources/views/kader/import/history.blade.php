@extends('layouts.kader')

@section('title', 'Riwayat Import Data')
@section('page-name', 'Riwayat Import Data')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $typeMeta = [
        'balita' => [
            'label' => 'Balita / Anak',
            'icon' => 'fa-child-reaching',
            'class' => 'border-emerald-100 bg-emerald-50/80 text-emerald-700',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'class' => 'border-sky-100 bg-sky-50/80 text-sky-700',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'class' => 'border-amber-100 bg-amber-50/80 text-amber-700',
        ],
    ];

    $statusMeta = [
        'completed' => [
            'label' => 'Berhasil',
            'icon' => 'fa-circle-check',
            'class' => 'border-emerald-100 bg-emerald-50/80 text-emerald-700',
        ],
        'processing' => [
            'label' => 'Diproses',
            'icon' => 'fa-clock',
            'class' => 'border-amber-100 bg-amber-50/80 text-amber-700',
        ],
        'failed' => [
            'label' => 'Gagal',
            'icon' => 'fa-circle-xmark',
            'class' => 'border-rose-100 bg-rose-50/80 text-rose-700',
        ],
    ];

    $jenisData = $jenisData ?? request('jenis_data', 'semua');
    $status = $status ?? request('status', 'semua');
    $tanggal = $tanggal ?? request('tanggal');
    $search = $search ?? request('search', '');

    $totalImport = $imports->total() ?? 0;
@endphp

@push('styles')
<style>
    .import-history-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .import-history-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.13), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(245,158,11,.10), transparent 26%),
            radial-gradient(circle at 50% 100%, rgba(14,165,233,.08), transparent 32%),
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
            radial-gradient(circle at 88% 16%, rgba(245,158,11,.13), transparent 32%),
            linear-gradient(135deg, rgba(255,255,255,.72), rgba(236,253,245,.70));
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .input-premium {
        border: 1px solid rgba(226,232,240,.9);
        background: rgba(255,255,255,.72);
        outline: none;
        transition: all .3s ease-in-out;
    }

    .input-premium:focus {
        border-color: rgba(16,185,129,.42);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
        background: rgba(255,255,255,.86);
    }

    .card-hover {
        transition: all .3s ease-in-out;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.24);
        box-shadow: 0 20px 46px rgba(15,23,42,.075);
    }

    .scroll-soft {
        max-height: 640px;
        overflow: auto;
        overscroll-behavior: contain;
    }

    .scroll-soft::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .scroll-soft::-webkit-scrollbar-track {
        background: rgba(241,245,249,.8);
        border-radius: 999px;
    }

    .scroll-soft::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }
</style>
@endpush

@section('content')
<div class="import-history-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Riwayat Import
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Riwayat Import Data Warga
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Halaman ini menampilkan jejak upload Excel oleh Kader. Riwayat digunakan untuk memeriksa file, kategori data, jumlah baris tersimpan, dan status proses import.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.import.index'))
                    <a href="{{ route('kader.import.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-layer-group"></i>
                        Pusat Import
                    </a>
                @endif

                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-upload"></i>
                        Import Baru
                    </a>
                @endif
            </div>
        </div>
    </section>

    
    {{-- FILTER --}}
    <section class="glass-panel rounded-[30px] p-4 sm:p-5">
        <form method="GET" action="{{ route('kader.import.history') }}" class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_170px_190px_170px_auto]">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Cari Log</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="input-premium h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700"
                        placeholder="Cari nama file, catatan, atau jenis data..."
                    >
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tanggal</label>
                <input
                    type="date"
                    name="tanggal"
                    value="{{ $tanggal }}"
                    class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Jenis Data</label>
                <select name="jenis_data" class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    <option value="semua" {{ $jenisData === 'semua' ? 'selected' : '' }}>Semua Data</option>
                    @foreach($typeMeta as $key => $item)
                        <option value="{{ $key }}" {{ $jenisData === $key ? 'selected' : '' }}>
                            {{ $item['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Status</label>
                <select name="status" class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    @foreach($statusMeta as $key => $item)
                        <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>
                            {{ $item['label'] }}
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

                <a href="{{ route('kader.import.history') }}"
                   class="grid h-12 w-12 place-items-center rounded-2xl border border-white/70 bg-white/60 text-slate-500 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50 hover:text-emerald-700">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </section>

    {{-- LIST --}}
    <section class="glass-panel rounded-[30px] p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Daftar Riwayat Import</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Menampilkan log upload file Excel berdasarkan filter aktif.
                </p>
            </div>

            <span class="w-fit rounded-full border border-emerald-100 bg-emerald-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">
                {{ $totalImport }} Log
            </span>
        </div>

        @if(isset($imports) && $imports->count())
            <div class="scroll-soft">
                <div class="space-y-3">
                    @foreach($imports as $import)
                        @php
                        $totalTerbaca = $import->total_data ?? null;
$dataBaru = $import->data_berhasil ?? $import->data_tersimpan ?? 0;
$dataTidakMasuk = $import->data_gagal ?? (
    $totalTerbaca !== null ? max(0, $totalTerbaca - $dataBaru) : null
);
                            $statusData = $statusMeta[$import->status] ?? $statusMeta['processing'];
                            $typeData = $typeMeta[$import->jenis_data] ?? [
                                'label' => ucfirst($import->jenis_data ?? '-'),
                                'icon' => 'fa-database',
                                'class' => 'border-slate-100 bg-slate-50/80 text-slate-700',
                            ];

                            $tanggalImport = $import->created_at
                                ? $import->created_at->translatedFormat('d F Y')
                                : '-';

                            $jamImport = $import->created_at
                                ? $import->created_at->format('H:i') . ' WIB'
                                : '-';

                            $creatorName = $import->creator?->name
                                ?? $import->creator?->nama
                                ?? 'Kader';
                        @endphp

                        <article class="card-hover rounded-[26px] border border-white/70 bg-white/56 p-4 backdrop-blur-md">
                            <div class="grid gap-4 xl:grid-cols-[1.3fr_170px_170px_130px_auto] xl:items-center">
                                <div class="min-w-0">
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $typeData['class'] }}">
                                            <i class="fa-solid {{ $typeData['icon'] }}"></i>
                                            {{ $typeData['label'] }}
                                        </span>

                                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $statusData['class'] }}">
                                            <i class="fa-solid {{ $statusData['icon'] }}"></i>
                                            {{ $statusData['label'] }}
                                        </span>
                                    </div>

                                    <h3 class="truncate text-base font-black text-slate-900">
                                        {{ $import->nama_file }}
                                    </h3>

                                    <p class="mt-1 text-xs font-bold text-slate-400">
                                        ID Log #{{ str_pad($import->id, 5, '0', STR_PAD_LEFT) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Waktu Upload</p>
                                    <p class="mt-1 text-sm font-black text-slate-900">{{ $tanggalImport }}</p>
                                    <p class="mt-1 text-xs font-bold text-slate-400">{{ $jamImport }}</p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Diunggah Oleh</p>
                                    <p class="mt-1 text-sm font-black text-slate-900">{{ $creatorName }}</p>
                                    <p class="mt-1 text-xs font-bold text-slate-400">Kader</p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Data Baru</p>
<p class="mt-1 text-2xl font-black text-slate-900">{{ $dataBaru }}</p>
<p class="mt-1 text-xs font-bold text-slate-400">
    @if($totalTerbaca !== null)
        dari {{ $totalTerbaca }} baris
    @else
        Log lama
    @endif
</p>
                                </div>

                                <div class="flex flex-wrap gap-2 xl:justify-end">
                                    @if($routeHas('kader.import.show'))
                                        <a href="{{ route('kader.import.show', $import->id) }}"
                                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-xs font-black text-white shadow-[0_10px_20px_rgba(5,150,105,.14)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                                            <i class="fa-solid fa-eye"></i>
                                            Detail
                                        </a>
                                    @endif

                                    @if($routeHas('kader.import.destroy'))
                                        <form method="POST"
                                              action="{{ route('kader.import.destroy', $import->id) }}"
                                              onsubmit="return confirm('Hapus riwayat import ini? Data warga yang sudah masuk tidak ikut terhapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-100 bg-rose-50/80 px-4 py-2.5 text-xs font-black text-rose-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-rose-100/80">
                                                <i class="fa-solid fa-trash"></i>
                                                Hapus Log
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            @if($imports->hasPages())
                <div class="mt-5">
                    {{ $imports->links() }}
                </div>
            @endif
        @else
            <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white/60 text-slate-400 backdrop-blur-md">
                    <i class="fa-solid fa-folder-open text-xl"></i>
                </div>

                <h3 class="mt-4 text-lg font-black text-slate-900">Riwayat Import Kosong</h3>
                <p class="mx-auto mt-2 max-w-md text-sm font-bold leading-6 text-slate-400">
                    Belum ada aktivitas import yang cocok dengan filter saat ini. Mulai import baru kalau database warga masih perlu diisi massal.
                </p>

                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create') }}"
                       class="mt-5 inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.16)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-upload"></i>
                        Import Pertama
                    </a>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection