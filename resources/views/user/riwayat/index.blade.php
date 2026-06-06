@extends('layouts.user')

@section('title', 'Riwayat Terpadu')
@section('page_title', 'Riwayat Terpadu')

@php
    use Illuminate\Support\Facades\Route;

    $filters = $filters ?? [
        'search' => '',
        'kategori' => 'semua',
        'periode' => 'semua',
    ];

    $counts = $counts ?? [
        'target' => 0,
        'total' => 0,
        'balita' => 0,
        'remaja' => 0,
        'lansia' => 0,
    ];

    $riwayatCards = collect($riwayatCards ?? []);

    $indexRoute = Route::has('user.riwayat.index')
        ? route('user.riwayat.index')
        : url('/user/riwayat');

    $toneMap = [
        'rose' => [
            'icon' => 'border-rose-100 bg-rose-50 text-rose-500',
            'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            'line' => 'bg-rose-400',
            'button' => 'text-rose-700 hover:bg-rose-50',
        ],
        'sky' => [
            'icon' => 'border-sky-100 bg-sky-50 text-sky-500',
            'badge' => 'border-sky-200 bg-sky-50 text-sky-700',
            'line' => 'bg-sky-400',
            'button' => 'text-sky-700 hover:bg-sky-50',
        ],
        'amber' => [
            'icon' => 'border-amber-100 bg-amber-50 text-amber-500',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
            'line' => 'bg-amber-400',
            'button' => 'text-amber-700 hover:bg-amber-50',
        ],
    ];

    $kategoriOptions = [
        'semua' => 'Semua Kategori',
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    $periodeOptions = [
        'semua' => 'Semua Periode',
        'bulan_ini' => 'Bulan Ini',
        '3_bulan' => '3 Bulan',
        '6_bulan' => '6 Bulan',
        'tahun_ini' => 'Tahun Ini',
    ];
@endphp

@push('styles')
<style>
    .rh-page {
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.14), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(14,165,233,.11), transparent 26%),
            radial-gradient(circle at 76% 88%, rgba(245,158,11,.11), transparent 28%),
            linear-gradient(135deg,#f8fafc 0%,#ecfdf5 42%,#eff6ff 100%);
    }

    .rh-enter {
        opacity: 0;
        animation: rhEnter .36s cubic-bezier(.16,1,.3,1) forwards;
    }

    .rh-enter-2 {
        opacity: 0;
        animation: rhEnter .36s cubic-bezier(.16,1,.3,1) .07s forwards;
    }

    @keyframes rhEnter {
        from {
            opacity: 0;
            transform: translateY(12px) scale(.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .rh-glass {
        border: 1px solid rgba(255,255,255,.76);
        background: rgba(255,255,255,.70);
        backdrop-filter: blur(20px);
        box-shadow: 0 16px 46px rgba(15,23,42,.055);
    }

    .rh-card {
        border: 1px solid rgba(226,232,240,.78);
        background: rgba(255,255,255,.74);
        backdrop-filter: blur(16px);
        box-shadow: 0 10px 28px rgba(15,23,42,.04);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .rh-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 16px 38px rgba(15,23,42,.065);
    }

    @media (prefers-reduced-motion: reduce) {
        .rh-enter,
        .rh-enter-2 {
            animation: none;
            opacity: 1;
        }

        .rh-card,
        .rh-card:hover {
            transition: none;
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="rh-page -mx-4 -my-4 min-h-[calc(100vh-96px)] px-4 py-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">

        <section class="rh-enter rh-glass relative overflow-hidden rounded-[30px] p-5 sm:p-6">
            <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-8 h-56 w-56 rounded-full bg-sky-300/14 blur-3xl"></div>

            <div class="relative grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_420px] xl:items-center">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/90 px-3.5 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Arsip Kesehatan Terpadu
                    </span>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-800 sm:text-4xl">
                        Riwayat Rekam Medis
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-7 text-slate-600">
                        Pusat arsip pemeriksaan Balita, Remaja, dan Lansia yang sudah divalidasi oleh Bidan.
                    </p>

                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-[20px] border border-emerald-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Riwayat</p>
                            <p class="mt-1 text-2xl font-black text-slate-800">{{ $counts['total'] }}</p>
                        </div>

                        <div class="rounded-[20px] border border-rose-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Balita</p>
                            <p class="mt-1 text-2xl font-black text-rose-600">{{ $counts['balita'] }}</p>
                        </div>

                        <div class="rounded-[20px] border border-sky-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Remaja</p>
                            <p class="mt-1 text-2xl font-black text-sky-600">{{ $counts['remaja'] }}</p>
                        </div>

                        <div class="rounded-[20px] border border-amber-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Lansia</p>
                            <p class="mt-1 text-2xl font-black text-amber-600">{{ $counts['lansia'] }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ $indexRoute }}" method="GET" class="rounded-[26px] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-white/82 to-sky-50/90 p-5 shadow-[0_14px_40px_rgba(15,23,42,0.055)] backdrop-blur-xl">
                    <label for="search" class="block text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        Filter Riwayat
                    </label>

                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ $filters['search'] }}"
                           autocomplete="off"
                           placeholder="Cari nama, NIK, status, atau catatan..."
                           class="mt-3 h-12 w-full rounded-2xl border border-emerald-100 bg-white/78 px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <select name="kategori"
                                class="h-11 rounded-2xl border border-slate-200 bg-white/78 px-3 text-xs font-black text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                            @foreach($kategoriOptions as $key => $label)
                                <option value="{{ $key }}" @selected($filters['kategori'] === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <select name="periode"
                                class="h-11 rounded-2xl border border-slate-200 bg-white/78 px-3 text-xs font-black text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">
                            @foreach($periodeOptions as $key => $label)
                                <option value="{{ $key }}" @selected($filters['periode'] === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <button type="submit"
                                class="h-11 rounded-2xl bg-emerald-600 px-4 text-xs font-black uppercase tracking-[0.12em] text-white shadow-[0_12px_26px_rgba(16,185,129,.22)] transition hover:bg-emerald-700">
                            Terapkan
                        </button>

                        <a href="{{ $indexRoute }}"
                           class="inline-flex h-11 items-center justify-center rounded-2xl border border-emerald-100 bg-white/82 px-4 text-xs font-black uppercase tracking-[0.12em] text-emerald-700 transition hover:bg-emerald-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </section>

        @if(!empty($loadError))
            <section class="rounded-[24px] border border-rose-200 bg-rose-50/85 p-5 text-rose-800 shadow-sm backdrop-blur-xl">
                <p class="text-sm font-black">{{ $loadError }}</p>
            </section>
        @endif

        <section class="rh-enter-2 space-y-4">
            @forelse($riwayatCards as $card)
                @php
                    $tone = $toneMap[$card['tone']] ?? $toneMap['sky'];
                @endphp

                <article class="rh-card group relative overflow-hidden rounded-[28px] p-4 sm:p-5">
                    <div class="absolute left-0 top-0 h-full w-1.5 {{ $tone['line'] }}"></div>

                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,280px)_1fr_auto] xl:items-center">
                        <div class="flex items-start gap-4">
                            <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-[20px] border text-lg {{ $tone['icon'] }}">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] {{ $tone['badge'] }}">
                                        {{ $card['kategori_label'] }}
                                    </span>

                                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] text-emerald-700">
                                        {{ $card['status'] }}
                                    </span>
                                </div>

                                <h2 class="mt-3 line-clamp-1 text-lg font-black text-slate-800">
                                    {{ $card['nama'] }}
                                </h2>

                                <p class="mt-1 text-sm font-semibold text-slate-500">
                                    {{ $card['tanggal'] }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                            @foreach($card['metrics'] as $metric)
                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">
                                        {{ $metric['label'] }}
                                    </p>

                                    <p class="mt-1 truncate text-sm font-black text-slate-800">
                                        {{ $metric['value'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex flex-col gap-3 xl:w-[150px]">
                            <a href="{{ $card['route'] }}"
                               class="smooth-route inline-flex h-10 items-center justify-center rounded-2xl border border-slate-100 bg-white/82 px-4 text-xs font-black uppercase tracking-[0.12em] transition {{ $tone['button'] }}">
                                Detail
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 rounded-[20px] border border-slate-100 bg-white/60 px-4 py-3 text-sm font-semibold leading-6 text-slate-600">
                        {{ $card['catatan'] }}
                    </div>
                </article>
            @empty
                <div class="rounded-[28px] border border-dashed border-emerald-300 bg-emerald-50/65 p-10 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[24px] bg-white text-2xl text-emerald-500 shadow-sm">
                        <i class="fas fa-folder-open"></i>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-800">
                        Belum Ada Riwayat
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                        Riwayat pemeriksaan yang sudah divalidasi Bidan belum tersedia untuk filter saat ini.
                    </p>
                </div>
            @endforelse
        </section>

        @if(method_exists($riwayat, 'links') && $riwayat->hasPages())
            <section class="rh-enter-2 rounded-[24px] border border-white/75 bg-white/70 px-4 py-4 shadow-sm backdrop-blur-xl">
                {{ $riwayat->links() }}
            </section>
        @endif
    </div>
</div>
@endsection