@extends('layouts.kader')

@section('title', 'Import Data Warga')
@section('page-name', 'Import Data Warga')

@php
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $typeMeta = [
        'balita' => [
            'label' => 'Balita / Anak',
            'desc' => 'Import data sasaran Balita / Anak menggunakan template resmi.',
            'icon' => 'fa-child-reaching',
            'tone' => 'emerald',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Import data Remaja untuk kebutuhan pemantauan dan pengukuran.',
            'icon' => 'fa-user-graduate',
            'tone' => 'sky',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Import data Lansia sebagai sasaran pelayanan Posyandu.',
            'icon' => 'fa-person-cane',
            'tone' => 'amber',
        ],
    ];

    $latestImports = $latestImports ?? collect();

    $statusMeta = [
        'completed' => [
            'label' => 'Berhasil',
            'class' => 'border-emerald-100 bg-emerald-50/80 text-emerald-700',
            'icon' => 'fa-circle-check',
        ],
        'processing' => [
            'label' => 'Diproses',
            'class' => 'border-amber-100 bg-amber-50/80 text-amber-700',
            'icon' => 'fa-clock',
        ],
        'failed' => [
            'label' => 'Gagal',
            'class' => 'border-rose-100 bg-rose-50/80 text-rose-700',
            'icon' => 'fa-circle-xmark',
        ],
    ];
@endphp

@push('styles')
<style>
    .import-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .import-page::before {
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

    .card-hover {
        transition: all .3s ease-in-out;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.24);
        box-shadow: 0 20px 46px rgba(15,23,42,.075);
    }
</style>
@endpush

@section('content')
<div class="import-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-file-import"></i>
                    Import Data Warga
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Pusat Import Data Sasaran
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Kader dapat mengunggah data Balita / Anak, Remaja, dan Lansia menggunakan template resmi. Sistem akan memvalidasi NIK, tanggal lahir, jenis kelamin, dan mencegah data duplikat masuk ke database.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-upload"></i>
                        Import Baru
                    </a>
                @endif

                @if($routeHas('kader.import.history'))
                    <a href="{{ route('kader.import.history') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Riwayat
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                <i class="fa-solid fa-database"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total Import</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statTotal ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Seluruh aktivitas import</p>
        </div>

        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Berhasil</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statBerhasil ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Import selesai</p>
        </div>

        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-amber-50/90 text-amber-700">
                <i class="fa-solid fa-clock"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Diproses</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statProcessing ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Sedang diproses</p>
        </div>

        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-rose-50/90 text-rose-700">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Gagal</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statGagal ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Perlu dicek ulang</p>
        </div>
    </section>

    {{-- PILIH MODUL --}}
    <section class="glass-panel rounded-[30px] p-4 sm:p-5">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Pilih Jenis Data</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Gunakan template sesuai kategori agar kolom terbaca benar.
                </p>
            </div>

            <span class="w-fit rounded-full border border-emerald-100 bg-emerald-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">
                Template Resmi
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            @foreach($typeMeta as $key => $item)
                @php
                    $tone = $item['tone'];
                    $toneClass = match($tone) {
                        'sky' => 'border-sky-100 bg-sky-50/60 text-sky-700',
                        'amber' => 'border-amber-100 bg-amber-50/60 text-amber-700',
                        default => 'border-emerald-100 bg-emerald-50/60 text-emerald-700',
                    };
                @endphp

                <article class="card-hover rounded-[26px] border border-white/70 bg-white/56 p-5 backdrop-blur-md">
                    <div class="mb-4 grid h-13 w-13 place-items-center rounded-2xl {{ $toneClass }}">
                        <i class="fa-solid {{ $item['icon'] }} text-lg"></i>
                    </div>

                    <h3 class="text-lg font-black text-slate-900">{{ $item['label'] }}</h3>
                    <p class="mt-2 min-h-[48px] text-sm font-semibold leading-6 text-slate-500">
                        {{ $item['desc'] }}
                    </p>

                    <div class="mt-5 grid grid-cols-2 gap-2">
                        <a href="{{ route('kader.import.create', ['type' => $key]) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-3 text-xs font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.14)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                            <i class="fa-solid fa-upload"></i>
                            Import
                        </a>

                        <a href="{{ route('kader.import.template', $key) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3 text-xs font-black text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-amber-100/70">
                            <i class="fa-solid fa-download"></i>
                            Template
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- RIWAYAT TERBARU --}}
    <section class="glass-panel rounded-[30px] p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Riwayat Import Terbaru</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Menampilkan 5 aktivitas import terakhir.
                </p>
            </div>

            @if($routeHas('kader.import.history'))
                <a href="{{ route('kader.import.history') }}"
                   class="inline-flex w-fit items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-4 py-2 text-xs font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                    <i class="fa-solid fa-list"></i>
                    Lihat Semua
                </a>
            @endif
        </div>

        @if($latestImports->count())
            <div class="space-y-3">
                @foreach($latestImports as $import)
                    @php
                        $meta = $statusMeta[$import->status] ?? $statusMeta['processing'];
                        $jenisLabel = $typeMeta[$import->jenis_data]['label'] ?? ucfirst($import->jenis_data ?? '-');
                    @endphp

                    <div class="card-hover grid gap-3 rounded-[24px] border border-white/70 bg-white/54 p-4 backdrop-blur-md lg:grid-cols-[1fr_180px_160px_auto] lg:items-center">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-900">{{ $import->nama_file }}</p>
                            <p class="mt-1 text-xs font-bold text-slate-400">
                                {{ $import->created_at?->translatedFormat('d F Y, H:i') ?? '-' }} WIB
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Jenis Data</p>
                            <p class="mt-1 text-sm font-black text-slate-900">{{ $jenisLabel }}</p>
                        </div>

                        <div>
                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $meta['class'] }}">
                                <i class="fa-solid {{ $meta['icon'] }}"></i>
                                {{ $meta['label'] }}
                            </span>
                        </div>

                        @if($routeHas('kader.import.show'))
                            <a href="{{ route('kader.import.show', $import->id) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-black text-white transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                                <i class="fa-solid fa-eye"></i>
                                Detail
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white/60 text-slate-400 backdrop-blur-md">
                    <i class="fa-solid fa-folder-open text-xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-black text-slate-900">Belum Ada Riwayat Import</h3>
                <p class="mx-auto mt-2 max-w-md text-sm font-bold leading-6 text-slate-400">
                    Mulai import pertama menggunakan template resmi agar data warga masuk dengan format yang rapi.
                </p>
            </div>
        @endif
    </section>
</div>
@endsection