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
            'pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'active' => 'bg-emerald-600 text-white shadow-emerald-200',
            'muted' => 'text-emerald-600',
            'bg' => 'from-emerald-500 to-teal-500',
            'icon' => 'fa-child-reaching',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'total' => $totalTahunan['remaja'] ?? 0,
            'desc' => 'Pengukuran dasar',
            'pill' => 'bg-teal-50 text-teal-700 ring-teal-200',
            'active' => 'bg-teal-600 text-white shadow-teal-200',
            'muted' => 'text-teal-600',
            'bg' => 'from-teal-500 to-cyan-500',
            'icon' => 'fa-user-graduate',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'total' => $totalTahunan['lansia'] ?? 0,
            'desc' => 'Kesehatan lansia',
            'pill' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'active' => 'bg-sky-500 text-white shadow-sky-200',
            'muted' => 'text-sky-600',
            'bg' => 'from-sky-500 to-blue-500',
            'icon' => 'fa-person-cane',
        ],
    ];

    $totalSemua = collect($kategori)->sum('total');
    $totalRiwayat = is_countable($riwayatBulanan) ? count($riwayatBulanan) : 0;
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

    .hero-grid {
        background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px);
        background-size: 24px 24px;
    }
    
    .pc-glass {
        background: rgba(255, 255, 255, .86);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .05);
        border: 1px solid rgba(255, 255, 255, 0.9);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active {
        transform: scale(0.95);
    }
</style>
@endpush

<div
    x-data="{
        activeTab: 'balita',
        yearType: 'balita',
        kategori: @js($kategori),
        currentPage: 1,
        itemsPerPage: 5,
        totalItems: {{ $totalRiwayat }},
        get activeMeta() {
            return this.kategori[this.activeTab] ?? this.kategori.balita
        },
        count(row) {
            return Number(row?.data?.[this.activeTab] ?? 0)
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.totalItems / this.itemsPerPage));
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.scrollToTop();
            }
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.scrollToTop();
            }
        },
        setPage(page) {
            this.currentPage = page;
            this.scrollToTop();
        },
        scrollToTop() {
            document.getElementById('arsip-bulanan-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        statusClass(status) {
            return status === 'Bulan Berjalan'
                ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                : 'bg-slate-100 text-slate-700 border-slate-200'
        },
        statusDotClass(status) {
            return status === 'Bulan Berjalan' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'
        },
        metricWrapClass(total) {
            return total > 0
                ? 'border-emerald-100 bg-emerald-50/50'
                : 'border-slate-200 bg-slate-50/50'
        },
        metricNumberClass(total) {
            return total > 0 ? 'text-emerald-700' : 'text-slate-900'
        },
        progressWidth(total) {
            if (total <= 0) return '5%'
            return Math.min(100, 22 + (total * 18)) + '%'
        },
        periodIconClass(status) {
            return status === 'Bulan Berjalan'
                ? 'bg-emerald-50 text-emerald-600 shadow-inner border border-emerald-100'
                : 'bg-slate-50 text-slate-500 shadow-inner border border-slate-100'
        }
    }"
    class="max-w-[1180px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6"
>
    {{-- Hero Section --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 lg:items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-full shadow-sm flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-white animate-pulse"></span>
                        Arsip Laporan Kader
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Pusat Laporan Posyandu
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Kelola pratinjau dan unduhan laporan Balita, Remaja, serta Lansia berdasarkan periode bulanan maupun tahunan secara praktis dan terstruktur.
                </p>

                <div class="mt-6 flex flex-wrap justify-center lg:justify-start gap-3">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold text-[11px] uppercase tracking-widest px-4 py-2 rounded-full shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-calendar-alt"></i>
                        Tahun {{ $tahun }}
                    </span>

                    <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold text-[11px] uppercase tracking-widest px-4 py-2 rounded-full shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-chart-pie"></i>
                        {{ number_format($totalSemua, 0, ',', '.') }} Pemeriksaan
                    </span>
                </div>
            </div>

            {{-- Unduh Laporan Tahunan Form --}}
            <form action="{{ route('kader.laporan.preview') }}" method="POST" class="bg-white/90 backdrop-blur-md rounded-3xl shadow-lg border border-white/50 p-6 w-full lg:w-80 shrink-0">
                @csrf
                <p class="text-[11px] font-black uppercase tracking-widest text-teal-600 mb-4 text-center"><i class="fas fa-download mr-1"></i> Rekap Tahunan</p>
                
                <select name="jenis_laporan" x-model="yearType" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100 transition-all mb-4 cursor-pointer">
                    @foreach($kategori as $key => $item)
                        <option value="{{ $key }}">{{ $item['label'] }}</option>
                    @endforeach
                </select>

                <input type="hidden" name="periode_tahun" value="{{ $tahun }}">

                <button type="submit" name="mode" value="download" class="w-full bg-slate-900 hover:bg-teal-600 text-white rounded-xl shadow-md transition-all px-5 py-3.5 text-xs font-black uppercase tracking-widest flex justify-center items-center gap-2">
                    <i class="fas fa-file-pdf"></i> Unduh PDF
                </button>
            </form>
        </div>
    </section>

    {{-- Rekap Bulanan Content --}}
    <section id="arsip-bulanan-section" class="pc-glass rounded-[2.5rem] p-6 sm:p-8">
        <div class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between border-b border-slate-100 pb-6">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-700 border border-emerald-200 shadow-sm mb-3">
                    <i class="fas fa-folder-open"></i> Arsip Bulanan
                </div>

                <h2 class="text-2xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                    Laporan <span x-text="activeMeta.label" class="text-teal-600"></span>
                </h2>

                <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-500">
                    Pilih kategori laporan dari tab di bawah, lalu buka pratinjau atau unduh PDF resmi sesuai periode bulanan.
                </p>
            </div>

            {{-- Tabs --}}
            <div class="flex flex-wrap gap-2 rounded-2xl bg-slate-100/80 p-2 border border-slate-200/60 shadow-inner shrink-0">
                @foreach($kategori as $key => $item)
                    <button
                        type="button"
                        @click="activeTab = '{{ $key }}'; currentPage = 1"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-xs font-black uppercase tracking-widest transition-all"
                        :class="activeTab === '{{ $key }}'
                            ? '{{ $item['active'] }} shadow-md transform scale-[1.02]'
                            : 'bg-transparent text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm'"
                    >
                        <i class="fas {{ $item['icon'] }} text-sm"></i>
                        <span>{{ $item['label'] }}</span>

                        <span
                            class="rounded-full px-2 py-0.5 text-[10px] font-black ml-1 border"
                            :class="activeTab === '{{ $key }}' ? 'bg-white/20 text-white border-white/30' : '{{ $item['pill'] }}'"
                        >
                            {{ number_format($item['total'], 0, ',', '.') }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Table Header Simulation --}}
        <div class="mb-4 hidden grid-cols-12 gap-4 rounded-2xl bg-slate-50/80 px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border border-slate-100 shadow-sm lg:grid">
            <div class="col-span-5">Periode Pemeriksaan</div>
            <div class="col-span-3">Jumlah Data</div>
            <div class="col-span-4 text-right">Aksi Laporan</div>
        </div>

        {{-- Daftar Bulan --}}
        <div class="space-y-4">
            @forelse($riwayatBulanan as $index => $row)
                <div 
                    x-show="Math.ceil({{ $index + 1 }} / itemsPerPage) === currentPage" 
                    x-transition.opacity.duration.300ms
                    class="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md hover:border-emerald-100 transition-all p-4 sm:p-5"
                >
                    <div class="grid gap-5 lg:grid-cols-12 lg:items-center">

                        {{-- Periode --}}
                        <div class="lg:col-span-5 flex items-center gap-4">
                            <div
                                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl"
                                :class="periodIconClass('{{ $row['status'] ?? 'Selesai' }}')"
                            >
                                <i class="fas fa-calendar-check"></i>
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="text-lg font-black tracking-tight text-slate-800">
                                        {{ $row['bulan'] }}
                                    </h3>

                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider border shadow-sm"
                                        :class="statusClass('{{ $row['status'] ?? 'Selesai' }}')"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="statusDotClass('{{ $row['status'] ?? 'Selesai' }}')"
                                        ></span>
                                        {{ $row['status'] ?? 'Selesai' }}
                                    </span>
                                </div>

                                <p class="text-xs font-semibold text-slate-500">
                                    Arsip bulanan <span class="font-black text-slate-600 uppercase" x-text="activeMeta.label"></span>
                                </p>
                            </div>
                        </div>

                        {{-- Jumlah --}}
                        <div class="lg:col-span-3">
                            <div
                                class="rounded-2xl border px-4 py-3 transition-all"
                                :class="metricWrapClass(count(@js($row)))"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">
                                            Jumlah Pemeriksaan
                                        </p>
                                        <div class="flex items-end gap-1.5">
                                            <span
                                                class="text-2xl font-black leading-none"
                                                :class="metricNumberClass(count(@js($row)))"
                                                x-text="count(@js($row))"
                                            ></span>
                                            <span class="text-[10px] font-bold text-slate-500 mb-0.5 uppercase">Data</span>
                                        </div>
                                    </div>
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm"
                                        :class="count(@js($row)) > 0
                                            ? 'bg-white text-emerald-500 border border-emerald-100 shadow-sm'
                                            : 'bg-white text-slate-300 border border-slate-100'"
                                    >
                                        <i class="fas fa-file-medical"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-white border border-slate-100 shadow-inner">
                                        <div
                                            class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500 transition-all duration-500"
                                            :style="`width:${progressWidth(count(@js($row)))} `"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Aksi --}}
                        <div class="lg:col-span-4 flex justify-end">
                            <form action="{{ route('kader.laporan.preview') }}" method="POST" class="flex w-full sm:w-auto gap-2">
                                @csrf
                                <input type="hidden" name="jenis_laporan" :value="activeTab">
                                <input type="hidden" name="periode_bulan" value="{{ $row['periode'] }}">

                                <button
                                    type="submit"
                                    name="mode"
                                    value="preview"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-black uppercase tracking-widest text-slate-600 shadow-sm transition-all hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200"
                                    title="Lihat Pratinjau"
                                >
                                    <i class="fas fa-eye text-sm"></i> Preview
                                </button>

                                <button
                                    type="submit"
                                    name="mode"
                                    value="download"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 hover:bg-teal-600 text-white px-5 py-2.5 text-xs font-black uppercase tracking-widest shadow-md transition-all hover:-translate-y-0.5"
                                    title="Unduh PDF"
                                >
                                    <i class="fas fa-download text-sm"></i> PDF
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-[2rem] border-2 border-dashed border-slate-200 bg-white/50 px-6 py-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-2xl text-slate-400 shadow-inner mb-4">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <p class="text-lg font-black text-slate-800">Belum ada arsip laporan</p>
                    <p class="mt-1 text-sm font-medium text-slate-500 max-w-sm mx-auto">Data bulanan akan tampil di sini setelah pemeriksaan di input dan diverifikasi.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination Controls --}}
        <div x-show="totalPages > 1" x-transition class="mt-8 flex flex-col items-center justify-center gap-3 border-t border-slate-100 pt-6">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                Halaman <span x-text="currentPage" class="text-slate-700"></span> dari <span x-text="totalPages" class="text-slate-700"></span>
            </p>
            <div class="flex items-center gap-2">
                <button @click="prevPage()" :disabled="currentPage === 1" type="button" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>

                <template x-for="page in totalPages" :key="page">
                    <button @click="setPage(page)" type="button"
                        class="btn-pill w-10 h-10 flex items-center justify-center text-sm font-bold transition-all shadow-sm"
                        :class="page === currentPage ? 'bg-emerald-500 text-white border-transparent shadow-md' : 'border border-slate-200 bg-white text-slate-600 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200'">
                        <span x-text="page"></span>
                    </button>
                </template>

                <button @click="nextPage()" :disabled="currentPage === totalPages" type="button" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>

    </section>
</div>
@endsection