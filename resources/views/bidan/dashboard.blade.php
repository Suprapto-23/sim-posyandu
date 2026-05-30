@extends('layouts.bidan')

@section('title', 'Dashboard Bidan')
@section('page-name', 'Dashboard Bidan')
@section('page-title', 'Dashboard Bidan')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $routeHas = fn ($name) => Route::has($name);

    $stats = $stats ?? [];
    $recentPemeriksaans = $recentPemeriksaans ?? [];
    $jadwalTerdekat = $jadwalTerdekat ?? [];
    $notifications = $notifications ?? [];
    $weeklyStats = $weeklyStats ?? [];

    $maxWeekly = collect($weeklyStats)->max('count') ?: 1;

    $statusBadge = function ($status) {
        return match ($status) {
            'Sudah Ditinjau' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'Perlu Perbaikan' => 'bg-rose-50 text-rose-700 ring-rose-200',
            default => 'bg-amber-50 text-amber-700 ring-amber-200',
        };
    };

    $kategoriBadge = function ($kategori) {
        return match ($kategori) {
            'Balita' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'Remaja' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'Lansia' => 'bg-amber-50 text-amber-700 ring-amber-200',
            default => 'bg-slate-50 text-slate-600 ring-slate-200',
        };
    };

    $targetCards = [
        [
            'label' => 'Balita',
            'value' => $stats['balita'] ?? 0,
            'icon' => 'ph-baby',
            'class' => 'bg-sky-50 text-sky-700 ring-sky-100',
        ],
        [
            'label' => 'Remaja',
            'value' => $stats['remaja'] ?? 0,
            'icon' => 'ph-user-focus',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Lansia',
            'value' => $stats['lansia'] ?? 0,
            'icon' => 'ph-heartbeat',
            'class' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
    ];

    $reviewCards = [
        [
            'label' => 'Menunggu Review',
            'value' => $stats['menunggu_review'] ?? 0,
            'icon' => 'ph-clock-countdown',
            'class' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
        [
            'label' => 'Sudah Ditinjau',
            'value' => $stats['sudah_ditinjau'] ?? 0,
            'icon' => 'ph-check-circle',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Perlu Perbaikan',
            'value' => $stats['perlu_perbaikan'] ?? 0,
            'icon' => 'ph-warning-circle',
            'class' => 'bg-rose-50 text-rose-700 ring-rose-100',
        ],
    ];

    $quickActions = [
        [
            'label' => 'Pemeriksaan Klinis',
            'desc' => 'Review data Kader',
            'icon' => 'ph-stethoscope',
            'route' => 'bidan.pemeriksaan.index',
        ],
        [
            'label' => 'Laporan Masuk',
            'desc' => 'Validasi laporan bulanan',
            'icon' => 'ph-file-text',
            'route' => 'bidan.laporan.index',
        ],
        [
            'label' => 'Kelola Jadwal',
            'desc' => 'Atur agenda Posyandu',
            'icon' => 'ph-calendar-check',
            'route' => 'bidan.jadwal.index',
        ],
    ];
@endphp

@section('content')
<style>
    .nexus-font {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .nexus-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, 0.35) transparent;
    }

    .nexus-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .nexus-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .nexus-scroll::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, 0.35);
        border-radius: 999px;
    }
</style>

<div class="nexus-font space-y-5 pb-8 text-slate-800">

    {{-- TOP GRID --}}
    <section class="grid gap-5 xl:grid-cols-12">

        {{-- HERO --}}
        <div class="xl:col-span-8">
            <div class="relative h-full min-h-[260px] overflow-hidden rounded-[28px] border border-white/70 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 p-6 shadow-[0_18px_50px_rgba(15,118,110,0.16)]">
                <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/20 blur-3xl"></div>
                <div class="absolute -bottom-28 -left-24 h-72 w-72 rounded-full bg-cyan-200/25 blur-3xl"></div>

                <div class="relative flex h-full flex-col justify-between gap-8">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-50 backdrop-blur">
                            <i class="ph ph-pulse text-base"></i>
                            Dashboard Bidan
                        </div>

                        <h1 class="mt-5 max-w-2xl text-[28px] font-black leading-tight tracking-[-0.03em] text-white md:text-[34px]">
                            Pusat Review Kesehatan Posyandu
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50/90">
                            Pantau pemeriksaan dari Kader, validasi data kesehatan, cek jadwal, dan siapkan laporan bulanan dalam satu ruang kerja yang waras. Akhirnya dashboard tidak perlu kelihatan seperti tabel Excel yang kabur dari kantor.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/20 bg-white/15 p-4 backdrop-blur">
                            <p class="text-[11px] font-bold uppercase tracking-[0.15em] text-emerald-50/75">Hari Ini</p>
                            <h3 class="mt-2 text-3xl font-black tracking-tight text-white">{{ $stats['pemeriksaan_hari_ini'] ?? 0 }}</h3>
                            <p class="mt-1 text-xs font-medium text-emerald-50/80">Pemeriksaan</p>
                        </div>

                        <div class="rounded-2xl border border-white/20 bg-white/15 p-4 backdrop-blur">
                            <p class="text-[11px] font-bold uppercase tracking-[0.15em] text-emerald-50/75">Bulan Ini</p>
                            <h3 class="mt-2 text-3xl font-black tracking-tight text-white">{{ $stats['pemeriksaan_bulan_ini'] ?? 0 }}</h3>
                            <p class="mt-1 text-xs font-medium text-emerald-50/80">Total data</p>
                        </div>

                        <div class="rounded-2xl border border-white/20 bg-white/15 p-4 backdrop-blur">
                            <p class="text-[11px] font-bold uppercase tracking-[0.15em] text-emerald-50/75">Prioritas</p>
                            <h3 class="mt-2 text-3xl font-black tracking-tight text-white">{{ $stats['menunggu_review'] ?? 0 }}</h3>
                            <p class="mt-1 text-xs font-medium text-emerald-50/80">Menunggu review</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="xl:col-span-4">
            <div class="h-full min-h-[260px] rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">Aksi Cepat</p>
                        <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">Ruang Kerja</h2>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                        <i class="ph ph-first-aid-kit text-xl"></i>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($quickActions as $action)
                        @if($routeHas($action['route']))
                            <a href="{{ route($action['route']) }}"
                               class="group flex min-h-[64px] items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3 transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-100 hover:bg-emerald-50/70">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-700 ring-1 ring-emerald-100">
                                    <i class="ph {{ $action['icon'] }} text-xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-black text-slate-900">{{ $action['label'] }}</p>
                                    <p class="truncate text-xs font-medium text-slate-500">{{ $action['desc'] }}</p>
                                </div>
                                <i class="ph ph-caret-right text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-emerald-600"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- KPI GRID --}}
    <section class="grid gap-5 xl:grid-cols-12">

        {{-- TARGET SASARAN --}}
        <div class="xl:col-span-6">
            <div class="h-full rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Data Sasaran</p>
                        <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">Sasaran Terdaftar</h2>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach($targetCards as $card)
                        <div class="min-h-[118px] rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl ring-1 {{ $card['class'] }}">
                                    <i class="ph {{ $card['icon'] }} text-lg"></i>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-slate-500">{{ $card['label'] }}</p>
                            <h3 class="mt-1 text-3xl font-black tracking-tight text-slate-900">{{ $card['value'] }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- REVIEW STATUS --}}
        <div class="xl:col-span-6">
            <div class="h-full rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Status Validasi</p>
                        <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">Ringkasan Review</h2>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach($reviewCards as $card)
                        <div class="min-h-[118px] rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl ring-1 {{ $card['class'] }}">
                                <i class="ph {{ $card['icon'] }} text-lg"></i>
                            </div>
                            <p class="truncate text-sm font-bold text-slate-500">{{ $card['label'] }}</p>
                            <h3 class="mt-1 text-3xl font-black tracking-tight text-slate-900">{{ $card['value'] }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <section class="grid gap-5 xl:grid-cols-12">

        {{-- PEMERIKSAAN TERBARU --}}
        <div class="xl:col-span-8">
            <div class="h-[620px] rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">Validasi Klinis</p>
                        <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">Pemeriksaan Terbaru</h2>
                        <p class="mt-1 text-sm text-slate-500">Daftar dibatasi agar dashboard tidak panjang sebelah seperti niat diet manusia.</p>
                    </div>

                    @if($routeHas('bidan.pemeriksaan.index'))
                        <a href="{{ route('bidan.pemeriksaan.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-700">
                            <i class="ph ph-list-checks"></i>
                            Lihat Semua
                        </a>
                    @endif
                </div>

                <div class="nexus-scroll h-[520px] space-y-3 overflow-y-auto pr-1">
                    @forelse($recentPemeriksaans as $item)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 transition-all duration-300 hover:border-emerald-100 hover:bg-emerald-50/50">
                            <div class="grid gap-4 lg:grid-cols-[1fr_340px] lg:items-center">
                                <div class="min-w-0">
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $kategoriBadge($item['kategori'] ?? '-') }}">
                                            {{ $item['kategori'] ?? '-' }}
                                        </span>
                                        <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $statusBadge($item['status'] ?? '-') }}">
                                            {{ $item['status'] ?? '-' }}
                                        </span>
                                    </div>

                                    <h3 class="truncate text-base font-black tracking-[-0.01em] text-slate-900">
                                        {{ $item['nama'] ?? '-' }}
                                    </h3>

                                    <p class="mt-1 line-clamp-1 text-sm font-medium text-slate-500">
                                        NIK {{ $item['nik'] ?? '-' }} • {{ $item['tanggal'] ?? '-' }} • {{ $item['waktu'] ?? '-' }}
                                    </p>

                                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">
                                        {{ $item['catatan'] ?? '-' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-4 gap-2">
                                    <div class="rounded-2xl bg-white p-3 text-center ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">BB</p>
                                        <p class="mt-1 truncate text-sm font-black text-slate-900">{{ $item['bb'] ?? '-' }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-white p-3 text-center ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">TB</p>
                                        <p class="mt-1 truncate text-sm font-black text-slate-900">{{ $item['tb'] ?? '-' }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-white p-3 text-center ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">IMT</p>
                                        <p class="mt-1 truncate text-sm font-black text-slate-900">{{ $item['imt'] ?? '-' }}</p>
                                    </div>

                                    <div class="rounded-2xl bg-white p-3 text-center ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Tensi</p>
                                        <p class="mt-1 truncate text-sm font-black text-slate-900">{{ $item['tensi'] ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex justify-end">
                                @if($routeHas('bidan.pemeriksaan.show') && !empty($item['id']))
                                    <a href="{{ route('bidan.pemeriksaan.show', $item['id']) }}"
                                       class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-bold text-emerald-700 ring-1 ring-emerald-100 transition hover:bg-emerald-600 hover:text-white">
                                        Detail
                                        <i class="ph ph-caret-right"></i>
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-bold text-slate-400 ring-1 ring-slate-100">
                                        Detail
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex h-full items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-center">
                            <div>
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                                    <i class="ph ph-clipboard-text text-3xl"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-black text-slate-800">Belum Ada Pemeriksaan</h3>
                                <p class="mt-2 max-w-sm text-sm text-slate-500">
                                    Data pemeriksaan akan muncul setelah Kader menginput pengukuran fisik.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT PANELS --}}
        <div class="xl:col-span-4">
            <div class="grid h-[620px] grid-rows-[1fr_1fr_1fr] gap-5">

                {{-- AKTIVITAS 7 HARI --}}
                <div class="min-h-0 rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-600">Statistik</p>
                            <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900">Aktivitas 7 Hari</h2>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                            <i class="ph ph-chart-bar text-lg"></i>
                        </div>
                    </div>

                    <div class="nexus-scroll h-[120px] space-y-3 overflow-y-auto pr-1">
                        @forelse($weeklyStats as $item)
                            @php
                                $width = $maxWeekly > 0 ? (($item['count'] / $maxWeekly) * 100) : 0;
                            @endphp

                            <div>
                                <div class="mb-1.5 flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-500">{{ $item['label'] ?? '-' }}</span>
                                    <span class="font-black text-slate-900">{{ $item['count'] ?? 0 }}</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500"
                                         style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">
                                Belum ada aktivitas minggu ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- JADWAL --}}
                <div class="min-h-0 rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-600">Agenda</p>
                            <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900">Jadwal Terdekat</h2>
                        </div>

                        @if($routeHas('bidan.jadwal.index'))
                            <a href="{{ route('bidan.jadwal.index') }}"
                               class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 ring-1 ring-amber-100 transition hover:bg-amber-500 hover:text-white">
                                <i class="ph ph-calendar-check text-lg"></i>
                            </a>
                        @endif
                    </div>

                    <div class="nexus-scroll h-[120px] space-y-3 overflow-y-auto pr-1">
                        @forelse($jadwalTerdekat as $item)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                <h3 class="truncate text-sm font-black text-slate-900">{{ $item['judul'] ?? 'Jadwal Posyandu' }}</h3>
                                <p class="mt-1 truncate text-xs font-medium text-slate-500">
                                    {{ $item['tanggal'] ?? '-' }} • {{ $item['waktu'] ?? '-' }}
                                </p>
                                <p class="mt-2 truncate text-xs font-bold text-slate-600">
                                    <i class="ph ph-map-pin"></i>
                                    {{ $item['lokasi'] ?? '-' }}
                                </p>
                            </div>
                        @empty
                            <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">
                                Belum ada jadwal terdekat.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- NOTIFIKASI --}}
                <div class="min-h-0 rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-rose-600">Informasi</p>
                            <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900">Notifikasi</h2>
                        </div>

                        @if($routeHas('bidan.notifikasi.index'))
                            <a href="{{ route('bidan.notifikasi.index') }}"
                               class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-50 text-rose-700 ring-1 ring-rose-100 transition hover:bg-rose-500 hover:text-white">
                                <i class="ph ph-bell-ringing text-lg"></i>
                            </a>
                        @endif
                    </div>

                    <div class="nexus-scroll h-[120px] space-y-3 overflow-y-auto pr-1">
                        @forelse($notifications as $item)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                <h3 class="truncate text-sm font-black text-slate-900">{{ $item['title'] ?? '-' }}</h3>
                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">
                                    {{ $item['message'] ?? '-' }}
                                </p>
                                <p class="mt-2 text-[11px] font-bold text-slate-400">
                                    {{ $item['time'] ?? '-' }}
                                </p>
                            </div>
                        @empty
                            <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">
                                Belum ada notifikasi baru.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection