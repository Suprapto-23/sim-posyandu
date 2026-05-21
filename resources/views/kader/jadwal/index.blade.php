@extends('layouts.kader')

@section('title', 'Jadwal Posyandu')
@section('page-name', 'Jadwal Posyandu')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $statusOptions = [
        'semua' => ['label' => 'Semua', 'icon' => 'fa-border-all'],
        'aktif' => ['label' => 'Aktif', 'icon' => 'fa-calendar-check'],
        'selesai' => ['label' => 'Selesai', 'icon' => 'fa-circle-check'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'icon' => 'fa-circle-xmark'],
    ];

    $targetOptions = [
        'semua' => 'Semua Sasaran',
        'balita' => 'Balita / Anak',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        'umum' => 'Umum',
    ];

    $statusNow = $status ?? request('status', 'semua');
    $targetNow = $target ?? request('target', 'semua');
    $bulanNow = $bulan ?? request('bulan', now('Asia/Jakarta')->format('Y-m'));
    $searchNow = $search ?? request('search', '');
@endphp

@push('styles')
<style>
    .jadwal-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .jadwal-page::before {
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
        box-shadow: 0 20px 46px rgba(15,23,42,.075);
        border-color: rgba(16,185,129,.24);
    }

    .scroll-soft {
        max-height: 620px;
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
<div class="jadwal-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-calendar-days"></i>
                    Jadwal Read Only
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Jadwal Posyandu
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Kader melihat jadwal kegiatan yang dibuat oleh Bidan. Jadwal digunakan sebagai acuan pelaksanaan layanan, absensi, pengukuran, dan laporan bulanan.
                </p>
            </div>

            @if($routeHas('kader.dashboard'))
                <a href="{{ route('kader.dashboard') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(15,23,42,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-slate-800">
                    <i class="fa-solid fa-chart-simple"></i>
                    Dashboard
                </a>
            @endif
        </div>
    </section>

    {{-- STATS --}}
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="glass-panel rounded-[26px] p-5 card-hover">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Jadwal Aktif</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statAktif ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Masih berjalan</p>
        </div>

        <div class="glass-panel rounded-[26px] p-5 card-hover">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-amber-50/90 text-amber-700">
                <i class="fa-solid fa-calendar-week"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Bulan Ini</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statBulanIni ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Agenda bulan berjalan</p>
        </div>

        <div class="glass-panel rounded-[26px] p-5 card-hover">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100/90 text-slate-600">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Selesai</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statSelesai ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Sudah terlaksana</p>
        </div>

        <div class="glass-panel rounded-[26px] p-5 card-hover">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-sky-50/90 text-sky-700">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total Jadwal</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statTotal ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Seluruh arsip</p>
        </div>
    </section>

    {{-- FILTER --}}
    <section class="glass-panel rounded-[28px] p-4 sm:p-5">
        <form method="GET" action="{{ route('kader.jadwal.index') }}" class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_180px_200px_190px_auto]">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Cari Jadwal</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $searchNow }}"
                        class="input-premium h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700"
                        placeholder="Cari judul, lokasi, target, atau deskripsi..."
                    >
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Bulan</label>
                <input
                    type="month"
                    name="bulan"
                    value="{{ $bulanNow }}"
                    class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Status</label>
                <select name="status" class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach($statusOptions as $key => $item)
                        <option value="{{ $key }}" {{ $statusNow === $key ? 'selected' : '' }}>
                            {{ $item['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Target</label>
                <select name="target" class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach($targetOptions as $key => $label)
                        <option value="{{ $key }}" {{ $targetNow === $key ? 'selected' : '' }}>
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

                <a href="{{ route('kader.jadwal.index') }}"
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
                <h2 class="text-lg font-black text-slate-900">Daftar Jadwal Posyandu</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Jadwal dibuat oleh Bidan dan hanya dapat dilihat oleh Kader.
                </p>
            </div>

            <span class="w-fit rounded-full border border-emerald-100 bg-emerald-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">
                {{ $jadwals->total() ?? 0 }} Jadwal
            </span>
        </div>

        @if(isset($jadwals) && $jadwals->count())
            <div class="scroll-soft">
                <div class="space-y-3">
                    @foreach($jadwals as $jadwal)
                        @php
                            $statusData = $jadwal->status_badge ?? [
                                'color' => 'slate',
                                'icon' => 'fa-info-circle',
                                'text' => ucfirst($jadwal->status ?? '-')
                            ];

                            $tanggal = $jadwal->tanggal
                                ? Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y')
                                : '-';

                            $hari = $jadwal->tanggal
                                ? Carbon::parse($jadwal->tanggal)->translatedFormat('D')
                                : '-';

                            $tanggalAngka = $jadwal->tanggal
                                ? Carbon::parse($jadwal->tanggal)->format('d')
                                : '-';

                            $bulanLabel = $jadwal->tanggal
                                ? Carbon::parse($jadwal->tanggal)->translatedFormat('M')
                                : '-';

                            $isToday = $jadwal->tanggal
                                ? Carbon::parse($jadwal->tanggal)->isToday()
                                : false;

                            $statusClass = match($jadwal->status) {
                                'aktif' => 'bg-emerald-50/90 text-emerald-700 border-emerald-100',
                                'selesai' => 'bg-slate-100/90 text-slate-600 border-slate-200',
                                'dibatalkan' => 'bg-rose-50/90 text-rose-700 border-rose-100',
                                default => 'bg-amber-50/90 text-amber-700 border-amber-100',
                            };

                            $targetLabel = $jadwal->label_target
                                ?? ($targetOptions[$jadwal->target_peserta] ?? ucfirst(str_replace('_', ' ', $jadwal->target_peserta ?? 'Umum')));
                        @endphp

                        <article class="card-hover rounded-[26px] border border-white/70 bg-white/64 p-4 backdrop-blur-md">
                            <div class="grid gap-4 lg:grid-cols-[92px_1fr_auto] lg:items-center">

                                {{-- DATE --}}
                                <div class="rounded-[24px] border border-emerald-100 bg-emerald-50/80 p-3 text-center">
                                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">{{ $hari }}</p>
                                    <p class="mt-1 text-3xl font-black text-slate-900">{{ $tanggalAngka }}</p>
                                    <p class="text-xs font-black uppercase text-slate-400">{{ $bulanLabel }}</p>
                                </div>

                                {{-- CONTENT --}}
                                <div class="min-w-0">
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $statusClass }}">
                                            <i class="fa-solid {{ $statusData['icon'] ?? 'fa-circle-info' }}"></i>
                                            {{ $statusData['text'] ?? ucfirst($jadwal->status ?? '-') }}
                                        </span>

                                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] text-amber-700">
                                            <i class="fa-solid fa-users"></i>
                                            {{ $targetLabel }}
                                        </span>

                                        @if($isToday)
                                            <span class="inline-flex items-center gap-2 rounded-full border border-sky-100 bg-sky-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] text-sky-700">
                                                <i class="fa-solid fa-bolt"></i>
                                                Hari Ini
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="truncate text-lg font-black tracking-[-.03em] text-slate-900">
                                        {{ $jadwal->judul }}
                                    </h3>

                                    <div class="mt-2 grid gap-2 text-xs font-bold text-slate-500 sm:grid-cols-3">
                                        <p class="flex items-center gap-2">
                                            <i class="fa-solid fa-calendar text-emerald-600"></i>
                                            {{ $tanggal }}
                                        </p>

                                        <p class="flex items-center gap-2">
                                            <i class="fa-solid fa-clock text-emerald-600"></i>
                                            {{ $jadwal->waktu_lengkap ?? (Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i') . ' WIB') }}
                                        </p>

                                        <p class="flex items-center gap-2 truncate">
                                            <i class="fa-solid fa-location-dot text-emerald-600"></i>
                                            {{ $jadwal->lokasi }}
                                        </p>
                                    </div>
                                </div>

                                {{-- ACTION --}}
                                <div class="flex items-center justify-end">
                                    <a href="{{ route('kader.jadwal.show', $jadwal->id) }}"
                                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.16)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                                        <i class="fa-solid fa-eye"></i>
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            @if($jadwals->hasPages())
                <div class="mt-5">
                    {{ $jadwals->links() }}
                </div>
            @endif
        @else
            <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white/70 text-slate-400 backdrop-blur-md">
                    <i class="fa-solid fa-calendar-xmark text-xl"></i>
                </div>

                <h3 class="mt-4 text-lg font-black text-slate-900">Jadwal Tidak Ditemukan</h3>
                <p class="mx-auto mt-2 max-w-md text-sm font-bold leading-6 text-slate-400">
                    Tidak ada jadwal yang cocok dengan filter saat ini. Coba ubah status, bulan, target peserta, atau kata pencarian.
                </p>
            </div>
        @endif
    </section>
</div>
@endsection