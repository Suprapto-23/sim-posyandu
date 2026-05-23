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
    /* Mengoptimalkan Background Premium */
    .bg-nexus {
        background-color: #f8fafc;
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 84%, 39%, 0.08) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(38, 92%, 50%, 0.08) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(200, 98%, 39%, 0.05) 0px, transparent 50%);
        background-attachment: fixed;
    }

    /* Scrollbar Halus */
    .scroll-soft::-webkit-scrollbar { width: 6px; height: 6px; }
    .scroll-soft::-webkit-scrollbar-track { background: transparent; }
    .scroll-soft::-webkit-scrollbar-thumb { 
        background: #cbd5e1; 
        border-radius: 10px; 
    }
    .scroll-soft:hover::-webkit-scrollbar-thumb { background: #94a3b8; }
</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="bg-nexus min-h-screen space-y-6 font-['Plus_Jakarta_Sans']" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">

    {{-- HERO SECTION --}}
    <section class="relative overflow-hidden rounded-[32px] border border-white/80 bg-white/40 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl transition-all duration-700 ease-out sm:p-8"
             :class="loaded ? 'translate-y-0 opacity-100' : '-translate-y-4 opacity-0'">
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-emerald-400/10 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-amber-400/10 blur-3xl"></div>

        <div class="relative z-10 grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-200/50 bg-emerald-50/80 px-4 py-1.5 text-[11px] font-bold uppercase tracking-widest text-emerald-600 shadow-sm backdrop-blur-sm">
                    <span class="relative flex h-2 w-2">
                      <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    Jadwal Read Only
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-800 sm:text-4xl">
                    Jadwal Posyandu
                </h1>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-relaxed text-slate-500">
                    Kader melihat jadwal kegiatan yang dibuat oleh Bidan. Jadwal digunakan sebagai acuan pelaksanaan layanan, absensi, pengukuran, dan laporan bulanan.
                </p>
            </div>

            @if($routeHas('kader.dashboard'))
                <a href="{{ route('kader.dashboard') }}"
                   class="group relative inline-flex items-center justify-center gap-3 overflow-hidden rounded-2xl bg-slate-800 px-6 py-3.5 text-sm font-bold text-white shadow-xl shadow-slate-900/20 transition-all duration-300 hover:-translate-y-1 hover:bg-slate-900 hover:shadow-2xl hover:shadow-slate-900/30">
                    <div class="absolute inset-0 translate-y-full bg-gradient-to-t from-emerald-500/20 to-transparent transition-transform duration-300 group-hover:translate-y-0"></div>
                    <i class="fa-solid fa-chart-simple relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="relative z-10">Dashboard</span>
                </a>
            @endif
        </div>
    </section>

    {{-- STATS SECTION (Staggered Animation) --}}
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @php
            $stats = [
                ['label' => 'Jadwal Aktif', 'value' => $statAktif ?? 0, 'desc' => 'Masih berjalan', 'icon' => 'fa-calendar-check', 'color' => 'emerald', 'delay' => '100'],
                ['label' => 'Bulan Ini', 'value' => $statBulanIni ?? 0, 'desc' => 'Agenda berjalan', 'icon' => 'fa-calendar-week', 'color' => 'amber', 'delay' => '200'],
                ['label' => 'Selesai', 'value' => $statSelesai ?? 0, 'desc' => 'Sudah terlaksana', 'icon' => 'fa-circle-check', 'color' => 'slate', 'delay' => '300'],
                ['label' => 'Total Jadwal', 'value' => $statTotal ?? 0, 'desc' => 'Seluruh arsip', 'icon' => 'fa-layer-group', 'color' => 'sky', 'delay' => '400'],
            ];
        @endphp

        @foreach($stats as $stat)
            <div class="group relative overflow-hidden rounded-[28px] border border-white/60 bg-white/50 p-6 shadow-sm backdrop-blur-xl transition-all duration-500 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/50 hover:ring-1 hover:ring-{{ $stat['color'] }}-100"
                 style="transition-delay: {{ $stat['delay'] }}ms"
                 :class="loaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
                <div class="mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <i class="fa-solid {{ $stat['icon'] }} text-xl"></i>
                </div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">{{ $stat['label'] }}</p>
                <h2 class="mt-1 text-3xl font-extrabold text-slate-800">{{ $stat['value'] }}</h2>
                <p class="mt-1 text-xs font-medium text-slate-400">{{ $stat['desc'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- FILTER SECTION --}}
    <section class="rounded-[28px] border border-white/80 bg-white/60 p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl transition-all duration-700 delay-500"
             :class="loaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'">
        <form method="GET" action="{{ route('kader.jadwal.index') }}" class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_180px_200px_190px_auto]">
            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Cari Jadwal</label>
                <div class="group relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-emerald-500"></i>
                    <input type="text" name="search" value="{{ $searchNow }}" placeholder="Cari judul, lokasi..."
                           class="h-12 w-full rounded-2xl border border-slate-200 bg-white/50 pl-11 pr-4 text-sm font-medium text-slate-700 outline-none backdrop-blur-sm transition-all focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                </div>
            </div>

            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Bulan</label>
                <input type="month" name="bulan" value="{{ $bulanNow }}"
                       class="h-12 w-full rounded-2xl border border-slate-200 bg-white/50 px-4 text-sm font-medium text-slate-700 outline-none backdrop-blur-sm transition-all focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
            </div>

            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Status</label>
                <select name="status" class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-white/50 px-4 text-sm font-medium text-slate-700 outline-none backdrop-blur-sm transition-all focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    @foreach($statusOptions as $key => $item)
                        <option value="{{ $key }}" {{ $statusNow === $key ? 'selected' : '' }}>{{ $item['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Target</label>
                <select name="target" class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-white/50 px-4 text-sm font-medium text-slate-700 outline-none backdrop-blur-sm transition-all focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                    @foreach($targetOptions as $key => $label)
                        <option value="{{ $key }}" {{ $targetNow === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3">
                <button type="submit" class="group flex h-12 items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition-all hover:-translate-y-0.5 hover:bg-emerald-500 hover:shadow-xl hover:shadow-emerald-500/30">
                    <i class="fa-solid fa-filter transition-transform group-hover:scale-110"></i> Filter
                </button>
                <a href="{{ route('kader.jadwal.index') }}" class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white/80 text-slate-500 transition-all hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </section>

    {{-- LIST SECTION --}}
    <section class="rounded-[32px] border border-white/80 bg-white/60 p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl transition-all duration-700 delay-700 sm:p-6"
             :class="loaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800">Daftar Jadwal Posyandu</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Jadwal dibuat oleh Bidan dan hanya dapat dilihat oleh Kader.</p>
            </div>
            <span class="inline-flex h-8 items-center rounded-full border border-emerald-100 bg-emerald-50/80 px-4 text-[11px] font-bold uppercase tracking-widest text-emerald-600">
                Total {{ $jadwals->total() ?? 0 }} Data
            </span>
        </div>

        @if(isset($jadwals) && $jadwals->count())
            <div class="scroll-soft max-h-[600px] overflow-y-auto pr-2">
                <div class="space-y-4">
                    @foreach($jadwals as $index => $jadwal)
                        @php
                            // ... [LOGIKA PHP ANDA TETAP SAMA] ...
                            $statusData = $jadwal->status_badge ?? ['color' => 'slate', 'icon' => 'fa-info-circle', 'text' => ucfirst($jadwal->status ?? '-')];
                            $tanggal = $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') : '-';
                            $hari = $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->translatedFormat('D') : '-';
                            $tanggalAngka = $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->format('d') : '-';
                            $bulanLabel = $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->translatedFormat('M') : '-';
                            $isToday = $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->isToday() : false;
                            
                            $statusClass = match($jadwal->status) {
                                'aktif' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'selesai' => 'bg-slate-100 text-slate-600 border-slate-200',
                                'dibatalkan' => 'bg-rose-50 text-rose-600 border-rose-100',
                                default => 'bg-amber-50 text-amber-600 border-amber-100',
                            };
                            $targetLabel = $jadwal->label_target ?? ($targetOptions[$jadwal->target_peserta] ?? ucfirst(str_replace('_', ' ', $jadwal->target_peserta ?? 'Umum')));
                        @endphp

                        <article class="group relative rounded-[24px] border border-slate-100 bg-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-100 hover:shadow-xl hover:shadow-emerald-900/5">
                            <div class="grid gap-5 lg:grid-cols-[100px_1fr_auto] lg:items-center">
                                
                                {{-- KOTAK TANGGAL --}}
                                <div class="flex flex-col items-center justify-center rounded-[20px] bg-slate-50/80 p-3 transition-colors group-hover:bg-emerald-50/50">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 group-hover:text-emerald-500">{{ $hari }}</span>
                                    <span class="my-0.5 text-3xl font-black text-slate-800 group-hover:text-emerald-600">{{ $tanggalAngka }}</span>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 group-hover:text-emerald-600">{{ $bulanLabel }}</span>
                                </div>

                                {{-- KONTEN --}}
                                <div class="min-w-0 py-1">
                                    <div class="mb-2.5 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                            <i class="fa-solid {{ $statusData['icon'] ?? 'fa-circle-info' }}"></i> {{ $statusData['text'] ?? ucfirst($jadwal->status ?? '-') }}
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 rounded-md border border-amber-100 bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-600">
                                            <i class="fa-solid fa-users"></i> {{ $targetLabel }}
                                        </span>
                                        @if($isToday)
                                            <span class="relative flex items-center gap-1.5 rounded-md border border-sky-200 bg-sky-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-sky-600">
                                                <span class="absolute -right-1 -top-1 flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-75"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-sky-500"></span></span>
                                                <i class="fa-solid fa-bolt"></i> Hari Ini
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="truncate text-lg font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">
                                        {{ $jadwal->judul }}
                                    </h3>

                                    <div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs font-medium text-slate-500">
                                        <p class="flex items-center gap-1.5"><i class="fa-regular fa-calendar text-slate-400"></i> {{ $tanggal }}</p>
                                        <p class="flex items-center gap-1.5"><i class="fa-regular fa-clock text-slate-400"></i> {{ $jadwal->waktu_lengkap ?? (Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i') . ' WIB') }}</p>
                                        <p class="flex items-center gap-1.5 truncate"><i class="fa-solid fa-location-dot text-slate-400"></i> {{ $jadwal->lokasi }}</p>
                                    </div>
                                </div>

                                {{-- ACTION --}}
                                <div class="flex items-center lg:justify-end">
                                    <a href="{{ route('kader.jadwal.show', $jadwal->id) }}"
                                       class="flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-100 px-5 text-sm font-bold text-slate-600 transition-all hover:bg-emerald-500 hover:text-white hover:shadow-lg hover:shadow-emerald-500/20">
                                        Detail <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            @if($jadwals->hasPages())
                <div class="mt-6 border-t border-slate-100 pt-5">
                    {{ $jadwals->links() }}
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center rounded-[24px] border-2 border-dashed border-slate-200 bg-slate-50/50 py-16 text-center">
                <div class="mb-4 grid h-20 w-20 place-items-center rounded-full bg-white shadow-sm">
                    <i class="fa-solid fa-folder-open text-3xl text-slate-300"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Tidak ada jadwal ditemukan</h3>
                <p class="mt-2 max-w-sm text-sm font-medium text-slate-500">Coba sesuaikan filter bulan, status, atau kata kunci pencarian Anda untuk melihat jadwal lainnya.</p>
            </div>
        @endif
    </section>
</div>
@endsection