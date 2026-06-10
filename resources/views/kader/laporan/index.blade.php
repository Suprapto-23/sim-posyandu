@extends('layouts.kader')

@section('title', 'Pusat Laporan Posyandu')
@section('page-name', 'Laporan Kader')

@section('content')
@php
    $tahun = $totalTahunan['tahun'] ?? now('Asia/Jakarta')->year;

    $kategori = [
        'balita' => [
            'label' => 'Balita',
            'total' => $totalTahunan['balita'] ?? 0,
            'desc' => 'Pertumbuhan dan gizi',
            'pill' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'active' => 'bg-sky-600 text-white shadow-sky-200',
            'muted' => 'text-sky-600',
            'bg' => 'from-sky-500 to-cyan-500',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'total' => $totalTahunan['remaja'] ?? 0,
            'desc' => 'Pengukuran dasar',
            'pill' => 'bg-violet-50 text-violet-700 ring-violet-200',
            'active' => 'bg-violet-600 text-white shadow-violet-200',
            'muted' => 'text-violet-600',
            'bg' => 'from-violet-500 to-indigo-500',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'total' => $totalTahunan['lansia'] ?? 0,
            'desc' => 'Kesehatan lansia',
            'pill' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'active' => 'bg-amber-500 text-white shadow-amber-200',
            'muted' => 'text-amber-600',
            'bg' => 'from-amber-500 to-orange-500',
        ],
    ];

    $totalSemua = collect($kategori)->sum('total');
@endphp

<div
    x-data="{
        activeTab: 'balita',
        yearType: 'balita',
        kategori: @js($kategori),
        get activeMeta() {
            return this.kategori[this.activeTab] ?? this.kategori.balita
        },
        count(row) {
            return Number(row?.data?.[this.activeTab] ?? 0)
        },
        statusClass(status) {
            return status === 'Bulan Berjalan'
                ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                : 'bg-slate-100 text-slate-600 ring-slate-200'
        },
        statusDotClass(status) {
            return status === 'Bulan Berjalan' ? 'bg-emerald-500' : 'bg-slate-400'
        },
        metricWrapClass(total) {
            return total > 0
                ? 'border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-teal-50'
                : 'border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-50'
        },
        metricNumberClass(total) {
            return total > 0 ? 'text-emerald-700' : 'text-slate-900'
        },
        progressWidth(total) {
            if (total <= 0) return '12%'
            return Math.min(100, 22 + (total * 18)) + '%'
        },
        periodIconClass(status) {
            return status === 'Bulan Berjalan'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-600'
                : 'border-slate-200 bg-slate-50 text-slate-500'
        }
    }"
    class="relative min-h-[85vh] overflow-hidden bg-gradient-to-br from-emerald-50/80 via-slate-50 to-sky-50 px-4 py-8 sm:px-6 lg:px-8"
>
    <div class="pointer-events-none absolute -top-24 right-10 h-72 w-72 rounded-full bg-emerald-300/25 blur-3xl"></div>
    <div class="pointer-events-none absolute left-8 top-72 h-80 w-80 rounded-full bg-sky-300/20 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 right-1/4 h-72 w-72 rounded-full bg-teal-300/20 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl space-y-7">

        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-sky-500 via-cyan-500 to-emerald-500 p-[1px] shadow-2xl shadow-sky-200/70">
            <div class="relative overflow-hidden rounded-[1.95rem] bg-white/75 px-6 py-7 backdrop-blur-2xl sm:px-8 lg:px-10">
                <div class="absolute inset-0 opacity-60">
                    <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/70 blur-3xl"></div>
                    <div class="absolute bottom-0 left-1/2 h-60 w-60 rounded-full bg-emerald-200/50 blur-3xl"></div>
                </div>

                <div class="relative grid gap-7 lg:grid-cols-[1.25fr_.75fr] lg:items-center">
                    <div>
                        <div class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/85 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-700 ring-1 ring-emerald-200">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-70"></span>
                                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            </span>
                            Arsip Laporan Kader
                        </div>

                        <h1 class="max-w-3xl text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-4xl lg:text-5xl">
                            Pusat Laporan Pemeriksaan Posyandu
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm font-semibold leading-7 text-slate-600">
                            Kelola pratinjau dan unduhan laporan Balita, Remaja, serta Lansia berdasarkan periode bulanan maupun tahunan dengan tampilan yang lebih rapi, profesional, dan nyaman dibaca.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/85 px-4 py-2 text-xs font-extrabold text-slate-700 ring-1 ring-slate-200">
                                <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                                </svg>
                                Tahun {{ $tahun }}
                            </span>

                            <span class="inline-flex items-center gap-2 rounded-full bg-white/85 px-4 py-2 text-xs font-extrabold text-slate-700 ring-1 ring-slate-200">
                                <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <ellipse cx="12" cy="5" rx="8" ry="3"></ellipse>
                                    <path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"></path>
                                    <path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"></path>
                                </svg>
                                {{ number_format($totalSemua, 0, ',', '.') }} total pemeriksaan
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('kader.laporan.preview') }}" method="POST" class="bg-white rounded-2xl shadow-sm ring-1 ring-black/[0.06] hover:shadow-md transition-shadow p-5">
    @csrf

    <select name="jenis_laporan" x-model="yearType" class="border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 transition-all w-full bg-white/90 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
        @foreach($kategori as $key => $item)
            <option value="{{ $key }}">{{ $item['label'] }}</option>
        @endforeach
    </select>

    <input type="hidden" name="periode_tahun" value="{{ $tahun }}">

    <button type="submit" name="mode" value="download" class="bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm hover:shadow-emerald-200 hover:shadow-md active:scale-95 transition-all inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-extrabold text-white">
        Unduh
    </button>
</form>
                </div>
            </div>
        </section>

        {{-- Rekap Bulanan --}}
        <section class="bg-white rounded-2xl shadow-sm ring-1 ring-black/[0.06] hover:shadow-md transition-shadow p-5 sm:p-6">
            <div class="mb-6 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-extrabold text-emerald-700 ring-1 ring-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Arsip Bulanan
                    </div>

                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">
                        Rekap Pemeriksaan <span x-text="activeMeta.label"></span>
                    </h2>

                    <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-500">
                        Pilih kategori laporan, lalu buka pratinjau atau unduh PDF resmi sesuai periode pemeriksaan.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 rounded-2xl bg-slate-100/90 p-2 ring-1 ring-slate-200">
                    @foreach($kategori as $key => $item)
                        <button
                            type="button"
                            @click="activeTab = '{{ $key }}'"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-black transition-all active:scale-95"
                            :class="activeTab === '{{ $key }}'
                                ? '{{ $item['active'] }} shadow-md'
                                : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 hover:text-slate-900'"
                        >
                            <span class="grid h-5 w-5 place-items-center">
                                @if($key === 'balita')
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="7" r="4"></circle>
                                        <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                                    </svg>
                                @elseif($key === 'remaja')
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 12h-4"></path>
                                        <path d="M6 12H2"></path>
                                        <path d="M12 6V2"></path>
                                        <path d="M12 22v-4"></path>
                                        <circle cx="12" cy="12" r="4"></circle>
                                    </svg>
                                @else
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 21s-7-4.6-9.5-9A5.7 5.7 0 0 1 12 4.2 5.7 5.7 0 0 1 21.5 12C19 16.4 12 21 12 21Z"></path>
                                    </svg>
                                @endif
                            </span>

                            <span>{{ $item['label'] }}</span>

                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-black"
                                :class="activeTab === '{{ $key }}' ? 'bg-white/20 text-white' : '{{ $item['pill'] }} ring-1'"
                            >
                                {{ number_format($item['total'], 0, ',', '.') }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mb-4 hidden grid-cols-12 gap-4 rounded-2xl bg-slate-50 px-5 py-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-500 ring-1 ring-slate-200 lg:grid">
                <div class="col-span-5">Periode Pemeriksaan</div>
                <div class="col-span-3">Jumlah Data</div>
                <div class="col-span-4 text-right">Aksi Laporan</div>
            </div>

            <div class="space-y-4">
                @forelse($riwayatBulanan as $row)
                    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-black/[0.06] hover:shadow-md transition-shadow p-4 sm:p-5">
                        <div class="grid gap-4 xl:grid-cols-[1.2fr_.85fr_.8fr] xl:items-center">

                            {{-- Periode --}}
                            <div class="flex items-center gap-4">
                                <div
                                    class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl border"
                                    :class="periodIconClass('{{ $row['status'] ?? 'Selesai' }}')"
                                >
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="3"></rect>
                                        <path d="M16 2v4M8 2v4M3 10h18"></path>
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-xl font-black tracking-tight text-slate-950">
                                            {{ $row['bulan'] }}
                                        </h3>

                                        <span
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ring-1"
                                            :class="statusClass('{{ $row['status'] ?? 'Selesai' }}')"
                                        >
                                            <span
                                                class="h-2 w-2 rounded-full"
                                                :class="statusDotClass('{{ $row['status'] ?? 'Selesai' }}')"
                                            ></span>
                                            {{ $row['status'] ?? 'Selesai' }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-sm font-medium text-slate-500">
                                        Arsip laporan bulanan untuk kategori <span class="font-bold text-slate-700" x-text="activeMeta.label"></span>
                                    </p>
                                </div>
                            </div>

                            {{-- Jumlah --}}
                            <div>
                                <div
                                    class="rounded-[1.4rem] border px-4 py-4 transition-all"
                                    :class="metricWrapClass(count(@js($row)))"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">
                                                Jumlah Data
                                            </p>

                                            <div class="mt-2 flex items-end gap-3">
                                                <span
                                                    class="text-4xl font-black leading-none"
                                                    :class="metricNumberClass(count(@js($row)))"
                                                    x-text="count(@js($row))"
                                                ></span>

                                                <div class="pb-1">
                                                    <p class="text-sm font-black text-slate-700">Pemeriksaan</p>
                                                    <p class="text-xs font-semibold text-slate-400">periode ini</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl"
                                            :class="count(@js($row)) > 0
                                                ? 'bg-white text-emerald-600 ring-1 ring-emerald-200'
                                                : 'bg-white text-slate-400 ring-1 ring-slate-200'"
                                        >
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M9 12h6"></path>
                                                <path d="M9 16h6"></path>
                                                <path d="M9 8h6"></path>
                                                <rect x="4" y="4" width="16" height="16" rx="2"></rect>
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="h-2.5 overflow-hidden rounded-full bg-white/80 ring-1 ring-black/[0.04]">
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-300"
                                                :style="`width:${progressWidth(count(@js($row)))} `"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Aksi --}}
                            <div>
                                <form action="{{ route('kader.laporan.preview') }}" method="POST" class="flex flex-wrap justify-start gap-2 sm:justify-end">
    @csrf

    <input type="hidden" name="jenis_laporan" :value="activeTab">
    <input type="hidden" name="periode_bulan" value="{{ $row['periode'] }}">

    <button
        type="submit"
        name="mode"
        value="preview"
        class="inline-flex h-11 min-w-[130px] items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95"
        title="Lihat Pratinjau"
    >
        Pratinjau
    </button>

    <button
        type="submit"
        name="mode"
        value="download"
        class="bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm hover:shadow-emerald-200 hover:shadow-md active:scale-95 transition-all inline-flex h-11 min-w-[112px] items-center justify-center gap-2 px-4 text-sm font-black text-white"
        title="Unduh PDF"
    >
        PDF
    </button>
</form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-14 text-center">
                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-white text-slate-500 ring-1 ring-slate-200">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"></path>
                            </svg>
                        </div>
                        <p class="mt-4 text-sm font-black text-slate-800">Belum ada arsip laporan</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">Data bulanan akan tampil setelah pemeriksaan tersedia.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection