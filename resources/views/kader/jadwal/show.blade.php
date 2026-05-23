@extends('layouts.kader')

@section('title', 'Detail Jadwal Posyandu')
@section('page-name', 'Detail Jadwal Posyandu')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $statusData = $jadwal->status_badge ?? [
        'color' => 'slate',
        'icon' => 'fa-info-circle',
        'text' => ucfirst($jadwal->status ?? '-')
    ];

    $tanggal = $jadwal->tanggal
        ? Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y')
        : '-';

    $tanggalSingkat = $jadwal->tanggal
        ? Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y')
        : '-';

    $isToday = $jadwal->tanggal
        ? Carbon::parse($jadwal->tanggal)->isToday()
        : false;

    $targetLabel = $jadwal->label_target
        ?? match($jadwal->target_peserta ?? 'umum') {
            'balita' => 'Balita / Anak',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => 'Umum',
        };

    $statusClass = match($jadwal->status) {
        'aktif' => 'bg-emerald-50/90 text-emerald-700 border-emerald-100',
        'selesai' => 'bg-slate-100/90 text-slate-600 border-slate-200',
        'dibatalkan' => 'bg-rose-50/90 text-rose-700 border-rose-100',
        default => 'bg-amber-50/90 text-amber-700 border-amber-100',
    };

    $waktuLabel = $jadwal->waktu_lengkap
        ?? (Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i') . ' WIB');

    $kategoriLabel = $jadwal->kategori
        ? ucwords(str_replace('_', ' ', $jadwal->kategori))
        : 'Pelayanan Posyandu';
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
</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="bg-nexus min-h-screen space-y-6 pb-10 font-['Plus_Jakarta_Sans']" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">

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
                    Detail Jadwal Read Only
                </div>

                <h1 class="text-3xl font-extrabold tracking-tight text-slate-800 sm:text-4xl">
                    Detail Jadwal Posyandu
                </h1>

                <p class="mt-3 max-w-2xl text-sm font-medium leading-relaxed text-slate-500">
                    Kader melihat detail jadwal yang dibuat oleh Bidan sebagai acuan pelaksanaan kegiatan Posyandu. 
                    Perubahan jadwal dilakukan oleh Bidan melalui modul Bidan.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                @if($routeHas('kader.jadwal.index'))
                    <a href="{{ route('kader.jadwal.index') }}"
                       class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl border border-emerald-100 bg-white/80 px-6 py-3.5 text-sm font-bold text-emerald-700 shadow-sm backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:bg-emerald-50 hover:shadow-md">
                        <i class="fa-solid fa-arrow-left transition-transform duration-300 group-hover:-translate-x-1"></i>
                        Kembali
                    </a>
                @endif

                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}"
                       class="group relative inline-flex items-center justify-center gap-3 overflow-hidden rounded-2xl bg-slate-800 px-6 py-3.5 text-sm font-bold text-white shadow-xl shadow-slate-900/20 transition-all duration-300 hover:-translate-y-1 hover:bg-slate-900 hover:shadow-2xl hover:shadow-slate-900/30">
                        <div class="absolute inset-0 translate-y-full bg-gradient-to-t from-emerald-500/20 to-transparent transition-transform duration-300 group-hover:translate-y-0"></div>
                        <i class="fa-solid fa-chart-simple relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                        <span class="relative z-10">Dashboard</span>
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- SUMMARY SECTION --}}
    <section class="rounded-[32px] border border-white/80 bg-white/60 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl transition-all duration-700 delay-100 ease-out sm:p-8"
             :class="loaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
        <div class="grid gap-6 xl:grid-cols-[1.25fr_.75fr] xl:items-center">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                <div class="grid h-16 w-16 shrink-0 place-items-center rounded-3xl bg-emerald-50 text-emerald-600 shadow-inner ring-1 ring-emerald-100">
                    <i class="fa-solid fa-calendar-days text-2xl"></i>
                </div>

                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">
                            <i class="fa-solid {{ $statusData['icon'] ?? 'fa-circle-info' }}"></i>
                            {{ $statusData['text'] ?? ucfirst($jadwal->status ?? '-') }}
                        </span>

                        <span class="inline-flex items-center gap-1.5 rounded-md border border-amber-100 bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-600">
                            <i class="fa-solid fa-users"></i>
                            {{ $targetLabel }}
                        </span>

                        @if($isToday)
                            <span class="relative flex items-center gap-1.5 rounded-md border border-sky-200 bg-sky-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-sky-600">
                                <span class="absolute -right-1 -top-1 flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-75"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-sky-500"></span></span>
                                <i class="fa-solid fa-bolt"></i> Hari Ini
                            </span>
                        @endif
                    </div>

                    <h2 class="text-2xl font-extrabold text-slate-800 sm:text-3xl">
                        {{ $jadwal->judul }}
                    </h2>

                    <p class="mt-1.5 text-sm font-medium text-slate-500">
                        {{ $kategoriLabel }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-[24px] border border-emerald-50 bg-emerald-50/50 p-5 transition-colors hover:bg-emerald-50">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-600/80">Tanggal</p>
                    <p class="mt-1.5 text-base font-extrabold text-slate-800">{{ $tanggalSingkat }}</p>
                </div>

                <div class="rounded-[24px] border border-amber-50 bg-amber-50/50 p-5 transition-colors hover:bg-amber-50">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-amber-600/80">Waktu</p>
                    <p class="mt-1.5 text-base font-extrabold text-slate-800">{{ $waktuLabel }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- READ ONLY INFO ALERT --}}
    <section class="rounded-[24px] border border-emerald-200/60 bg-emerald-50/80 p-5 shadow-sm transition-all duration-700 delay-200"
             :class="loaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
        <div class="flex items-start gap-4">
            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                <i class="fa-solid fa-lock text-lg"></i>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-emerald-800">Jadwal Terkunci untuk Kader</h3>
                <p class="mt-1 text-xs font-medium leading-relaxed text-emerald-700/80">
                    Kader hanya melihat jadwal sebagai acuan kegiatan. Pembuatan, perubahan, pembatalan, dan penghapusan jadwal dilakukan sepenuhnya oleh Bidan.
                </p>
            </div>
        </div>
    </section>

    {{-- DETAIL CONTENT (2 Columns) --}}
    <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

        {{-- INFORMASI JADWAL --}}
        <div class="rounded-[32px] border border-white/80 bg-white/60 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl transition-all duration-700 delay-300 xl:col-span-7"
             :class="loaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
            <div class="mb-5">
                <h3 class="text-xl font-extrabold text-slate-800">Informasi Jadwal</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Detail utama kegiatan Posyandu.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @php
                    $infoCards = [
                        ['label' => 'Tanggal Pelaksanaan', 'value' => $tanggal],
                        ['label' => 'Waktu', 'value' => $waktuLabel],
                        ['label' => 'Lokasi', 'value' => $jadwal->lokasi],
                        ['label' => 'Target Sasaran', 'value' => $targetLabel],
                        ['label' => 'Kategori Kegiatan', 'value' => $kategoriLabel],
                    ];
                @endphp

                @foreach($infoCards as $info)
                    <div class="group rounded-[20px] border border-slate-100 bg-white/50 p-4 transition-all duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:bg-white hover:shadow-lg hover:shadow-emerald-900/5">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 transition-colors group-hover:text-emerald-500">{{ $info['label'] }}</p>
                        <p class="mt-1.5 text-sm font-bold text-slate-800">{{ $info['value'] }}</p>
                    </div>
                @endforeach

                <div class="group rounded-[20px] border border-slate-100 bg-white/50 p-4 transition-all duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:bg-white hover:shadow-lg hover:shadow-emerald-900/5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 transition-colors group-hover:text-emerald-500">Status</p>
                    <div class="mt-1.5">
                        <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">
                            <i class="fa-solid {{ $statusData['icon'] ?? 'fa-circle-info' }}"></i>
                            {{ $statusData['text'] ?? ucfirst($jadwal->status ?? '-') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- DESKRIPSI --}}
        <div class="rounded-[32px] border border-white/80 bg-white/60 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl transition-all duration-700 delay-400 xl:col-span-5"
             :class="loaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
            <div class="mb-5">
                <h3 class="text-xl font-extrabold text-slate-800">Catatan Jadwal</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Keterangan dari Bidan terkait kegiatan ini.</p>
            </div>

            <div class="rounded-[24px] border border-slate-200/60 bg-slate-50/50 p-5 shadow-inner">
                <p class="text-sm font-medium leading-relaxed text-slate-600">
                    {{ $jadwal->deskripsi ?: 'Tidak ada catatan tambahan dari Bidan.' }}
                </p>
            </div>
        </div>
    </section>

    {{-- ALUR SISTEM TIMELINE --}}
    <section class="rounded-[32px] border border-white/80 bg-white/60 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl transition-all duration-700 delay-500"
             :class="loaded ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-slate-800">Alur Jadwal</h3>
            <p class="mt-1 text-sm font-medium text-slate-500">Pembagian peran pada data jadwal Posyandu.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div class="group rounded-[24px] border border-emerald-100 bg-emerald-50/40 p-5 transition-all duration-300 hover:-translate-y-1 hover:bg-emerald-50 hover:shadow-lg hover:shadow-emerald-900/5">
                <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-white text-emerald-600 shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <i class="fa-solid fa-user-nurse text-lg"></i>
                </div>
                <h4 class="text-base font-extrabold text-slate-800">Dibuat Bidan</h4>
                <p class="mt-1.5 text-xs font-medium leading-relaxed text-slate-500">Bidan membuat dan mengatur jadwal kegiatan Posyandu.</p>
            </div>

            <div class="group rounded-[24px] border border-amber-100 bg-amber-50/40 p-5 transition-all duration-300 hover:-translate-y-1 hover:bg-amber-50 hover:shadow-lg hover:shadow-amber-900/5">
                <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-white text-amber-600 shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <i class="fa-solid fa-clipboard-check text-lg"></i>
                </div>
                <h4 class="text-base font-extrabold text-slate-800">Digunakan Kader</h4>
                <p class="mt-1.5 text-xs font-medium leading-relaxed text-slate-500">Kader menggunakan jadwal sebagai acuan pelayanan, absensi, dan pengukuran.</p>
            </div>

            <div class="group rounded-[24px] border border-sky-100 bg-sky-50/40 p-5 transition-all duration-300 hover:-translate-y-1 hover:bg-sky-50 hover:shadow-lg hover:shadow-sky-900/5">
                <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-white text-sky-600 shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
                <h4 class="text-base font-extrabold text-slate-800">Dilihat Warga</h4>
                <p class="mt-1.5 text-xs font-medium leading-relaxed text-slate-500">Warga melihat jadwal aktif sebagai informasi kegiatan Posyandu.</p>
            </div>
        </div>
    </section>

    {{-- ACTION FOOTER --}}
    <section class="rounded-[28px] border border-white/80 bg-white/60 p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl transition-all duration-700 delay-700"
             :class="loaded ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800">Detail jadwal selesai ditampilkan</h3>
                <p class="mt-1 text-xs font-medium text-slate-500">
                    Untuk mengubah jadwal, gunakan akun Bidan melalui modul Jadwal Bidan.
                </p>
            </div>

            @if($routeHas('kader.jadwal.index'))
                <a href="{{ route('kader.jadwal.index') }}"
                   class="group flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-500 hover:shadow-xl hover:shadow-emerald-500/30">
                    <i class="fa-solid fa-list transition-transform duration-300 group-hover:scale-110"></i>
                    Daftar Jadwal
                </a>
            @endif
        </div>
    </section>
</div>
@endsection