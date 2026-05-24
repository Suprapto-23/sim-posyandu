@extends('layouts.bidan')

@section('title', 'Dashboard Bidan')
@section('page-name', 'Dashboard Bidan')

@php
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $maxWeekly = collect($weeklyStats ?? [])->max('count') ?: 1;

    $statusClass = function ($status) {
        return match($status) {
            'Sudah Ditinjau' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
            'Perlu Perbaikan' => 'border-rose-100 bg-rose-50 text-rose-700',
            default => 'border-amber-100 bg-amber-50 text-amber-700',
        };
    };

    $kategoriClass = function ($kategori) {
        return match($kategori) {
            'Balita' => 'border-sky-100 bg-sky-50 text-sky-700',
            'Remaja' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
            'Lansia' => 'border-amber-100 bg-amber-50 text-amber-700',
            default => 'border-slate-100 bg-slate-50 text-slate-600',
        };
    };
@endphp

@push('styles')
<style>
    .bidan-dashboard {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .bidan-dashboard::before {
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

    .stat-card {
        border: 1px solid rgba(226,232,240,.78);
        background: rgba(255,255,255,.60);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.25);
        box-shadow: 0 18px 34px rgba(15,23,42,.055);
    }

    .mini-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        border-width: 1px;
        border-radius: 999px;
        padding: .32rem .66rem;
        font-size: 10px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .soft-scroll {
        max-height: 460px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .soft-scroll::-webkit-scrollbar {
        width: 7px;
    }

    .soft-scroll::-webkit-scrollbar-track {
        background: rgba(241,245,249,.8);
        border-radius: 999px;
    }

    .soft-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }

    .bar-item {
        transition: all .25s ease-in-out;
    }

    .bar-item:hover {
        transform: translateY(-2px);
    }

    .clamp-1 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
    }

    .clamp-2 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
</style>
@endpush

@section('content')
<div class="bidan-dashboard space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 xl:grid-cols-[1fr_auto] xl:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-user-nurse"></i>
                    Dashboard Bidan
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Pusat Validasi dan Pelayanan Kesehatan
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Pantau pemeriksaan yang masuk dari Kader, tinjau status kesehatan sasaran, kelola imunisasi, dan cek jadwal Posyandu terdekat.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-[22px] border border-white/70 bg-white/55 p-4 backdrop-blur-md">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Hari Ini</p>
                    <h3 class="mt-2 text-2xl font-black text-slate-900">{{ $stats['pemeriksaan_hari_ini'] ?? 0 }}</h3>
                    <p class="mt-1 text-xs font-bold text-slate-400">Pemeriksaan</p>
                </div>

                <div class="rounded-[22px] border border-white/70 bg-white/55 p-4 backdrop-blur-md">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Bulan Ini</p>
                    <h3 class="mt-2 text-2xl font-black text-slate-900">{{ $stats['pemeriksaan_bulan_ini'] ?? 0 }}</h3>
                    <p class="mt-1 text-xs font-bold text-slate-400">Pemeriksaan</p>
                </div>

                <div class="rounded-[22px] border border-amber-100 bg-amber-50/75 p-4 backdrop-blur-md">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-amber-700">Prioritas</p>
                    <h3 class="mt-2 text-2xl font-black text-slate-900">{{ $stats['menunggu_review'] ?? 0 }}</h3>
                    <p class="mt-1 text-xs font-bold text-amber-700">Menunggu review</p>
                </div>
            </div>
        </div>
    </section>

    {{-- SASARAN STATS --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="stat-card rounded-[26px] p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total Balita</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $stats['balita'] ?? 0 }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">Sasaran aktif tercatat</p>
                </div>

                <div class="grid h-12 w-12 place-items-center rounded-2xl border border-sky-100 bg-sky-50 text-sky-700">
                    <i class="fa-solid fa-child-reaching"></i>
                </div>
            </div>
        </div>

        <div class="stat-card rounded-[26px] p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total Remaja</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $stats['remaja'] ?? 0 }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">Sasaran aktif tercatat</p>
                </div>

                <div class="grid h-12 w-12 place-items-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>
        </div>

        <div class="stat-card rounded-[26px] p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total Lansia</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $stats['lansia'] ?? 0 }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">Sasaran aktif tercatat</p>
                </div>

                <div class="grid h-12 w-12 place-items-center rounded-2xl border border-amber-100 bg-amber-50 text-amber-700">
                    <i class="fa-solid fa-person-cane"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- VALIDATION STATS --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="stat-card rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl border border-amber-100 bg-amber-50 text-amber-700">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>

            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Menunggu Review</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $stats['menunggu_review'] ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Data perlu ditinjau Bidan</p>
        </div>

        <div class="stat-card rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Sudah Ditinjau</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $stats['sudah_ditinjau'] ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Data pemeriksaan selesai</p>
        </div>

        <div class="stat-card rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-700">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Perlu Perbaikan</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $stats['perlu_perbaikan'] ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Data perlu koreksi Kader</p>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

        {{-- RECENT PEMERIKSAAN --}}
        <div class="glass-panel rounded-[30px] p-4 sm:p-5 xl:col-span-8">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Pemeriksaan Terbaru</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        Data pemeriksaan terakhir yang perlu dipantau oleh Bidan.
                    </p>
                </div>

                @if($routeHas('bidan.pengukuran.index'))
                    <a href="{{ route('bidan.pengukuran.index') }}"
                       class="inline-flex w-fit items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-xs font-black text-white transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-stethoscope"></i>
                        Review Pengukuran
                    </a>
                @endif
            </div>

            @if(!empty($recentPemeriksaans))
                <div class="soft-scroll space-y-3 pr-1">
                    @foreach($recentPemeriksaans as $item)
                        <article class="rounded-[24px] border border-white/70 bg-white/58 p-4 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:border-emerald-100 hover:shadow-[0_18px_38px_rgba(15,23,42,.06)]">
                            <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                                <div class="min-w-0">
                                    <div class="mb-2 flex flex-wrap gap-2">
                                        <span class="mini-badge {{ $kategoriClass($item['kategori']) }}">
                                            {{ $item['kategori'] }}
                                        </span>

                                        <span class="mini-badge {{ $statusClass($item['status']) }}">
                                            {{ $item['status'] }}
                                        </span>
                                    </div>

                                    <h3 class="clamp-1 text-sm font-black text-slate-900">
                                        {{ $item['nama'] }}
                                    </h3>

                                    <p class="mt-1 text-xs font-bold text-slate-400">
                                        NIK {{ $item['nik'] }} • {{ $item['tanggal'] }} • {{ $item['waktu'] }}
                                    </p>

                                    <div class="mt-3 grid grid-cols-2 gap-2 md:grid-cols-4">
                                        <div class="rounded-2xl bg-slate-50/80 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[.1em] text-slate-400">BB</p>
                                            <p class="mt-1 text-sm font-black text-slate-900">{{ $item['bb'] }}</p>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50/80 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[.1em] text-slate-400">TB</p>
                                            <p class="mt-1 text-sm font-black text-slate-900">{{ $item['tb'] }}</p>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50/80 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[.1em] text-slate-400">IMT</p>
                                            <p class="mt-1 text-sm font-black text-slate-900">{{ $item['imt'] }}</p>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50/80 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[.1em] text-slate-400">Tensi</p>
                                            <p class="mt-1 text-sm font-black text-slate-900">{{ $item['tensi'] }}</p>
                                        </div>
                                    </div>

                                    <p class="mt-3 clamp-2 text-xs font-bold leading-5 text-slate-500">
                                        {{ $item['catatan'] }}
                                    </p>
                                </div>

                                <div class="flex justify-start lg:justify-end">
                                    @if($routeHas('bidan.pengukuran.show') && !empty($item['id']))
                                        <a href="{{ route('bidan.pengukuran.show', $item['id']) }}"
                                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-2.5 text-xs font-black text-emerald-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-100">
                                            <i class="fa-solid fa-eye"></i>
                                            Detail
                                        </a>
                                    @else
                                        <span class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-2.5 text-xs font-black text-slate-400">
                                            <i class="fa-solid fa-eye-slash"></i>
                                            Detail
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-[26px] border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white/60 text-slate-400">
                        <i class="fa-solid fa-folder-open text-xl"></i>
                    </div>

                    <h3 class="mt-4 text-base font-black text-slate-900">Belum Ada Pemeriksaan</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm font-bold leading-6 text-slate-400">
                        Data pemeriksaan akan muncul setelah Kader menginput pengukuran fisik.
                    </p>
                </div>
            @endif
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-5 xl:col-span-4">

            {{-- WEEKLY --}}
            <div class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-4">
                    <h2 class="text-lg font-black text-slate-900">Aktivitas 7 Hari</h2>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        Jumlah pemeriksaan per hari.
                    </p>
                </div>

                <div class="space-y-3">
                    @foreach($weeklyStats ?? [] as $item)
                        @php
                            $width = $maxWeekly > 0 ? (($item['count'] / $maxWeekly) * 100) : 0;
                        @endphp

                        <div class="bar-item">
                            <div class="mb-1 flex items-center justify-between gap-3">
                                <p class="text-xs font-black text-slate-500">{{ $item['label'] }}</p>
                                <p class="text-xs font-black text-slate-900">{{ $item['count'] }}</p>
                            </div>

                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ max($width, $item['count'] > 0 ? 8 : 0) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- JADWAL --}}
            <div class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Jadwal Terdekat</h2>
                        <p class="mt-1 text-xs font-bold text-slate-400">Agenda Posyandu berikutnya.</p>
                    </div>

                    @if($routeHas('bidan.jadwal.index'))
                        <a href="{{ route('bidan.jadwal.index') }}"
                           class="grid h-10 w-10 place-items-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-100">
                            <i class="fa-solid fa-calendar-days"></i>
                        </a>
                    @endif
                </div>

                @if(!empty($jadwalTerdekat))
                    <div class="space-y-3">
                        @foreach($jadwalTerdekat as $item)
                            <div class="rounded-[22px] border border-white/70 bg-white/58 p-4">
                                <h3 class="clamp-1 text-sm font-black text-slate-900">{{ $item['judul'] }}</h3>
                                <p class="mt-2 text-xs font-bold text-slate-400">
                                    <i class="fa-solid fa-calendar mr-1"></i>
                                    {{ $item['tanggal'] }} • {{ $item['waktu'] }}
                                </p>
                                <p class="mt-1 text-xs font-bold text-slate-400">
                                    <i class="fa-solid fa-location-dot mr-1"></i>
                                    {{ $item['lokasi'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50/70 p-6 text-center">
                        <p class="text-sm font-black text-slate-700">Belum ada jadwal terdekat</p>
                        <p class="mt-1 text-xs font-bold text-slate-400">Agenda akan tampil saat jadwal dibuat.</p>
                    </div>
                @endif
            </div>

            {{-- NOTIFIKASI --}}
            <div class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Notifikasi</h2>
                        <p class="mt-1 text-xs font-bold text-slate-400">Informasi terbaru untuk Bidan.</p>
                    </div>

                    <div class="grid h-10 w-10 place-items-center rounded-2xl border border-amber-100 bg-amber-50 text-amber-700">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                </div>

                @if(!empty($notifications))
                    <div class="space-y-3">
                        @foreach($notifications as $item)
                            <div class="rounded-[22px] border {{ $item['is_read'] ? 'border-slate-100 bg-white/52' : 'border-emerald-100 bg-emerald-50/60' }} p-4">
                                <div class="flex gap-3">
                                    <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $item['is_read'] ? 'bg-slate-300' : 'bg-emerald-500' }}"></div>

                                    <div class="min-w-0">
                                        <h3 class="clamp-1 text-sm font-black text-slate-900">{{ $item['title'] }}</h3>
                                        <p class="mt-1 clamp-2 text-xs font-bold leading-5 text-slate-500">{{ $item['message'] }}</p>
                                        <p class="mt-2 text-[11px] font-black uppercase tracking-[.08em] text-slate-400">{{ $item['time'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50/70 p-6 text-center">
                        <p class="text-sm font-black text-slate-700">Belum ada notifikasi</p>
                        <p class="mt-1 text-xs font-bold text-slate-400">Notifikasi baru akan muncul di sini.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection