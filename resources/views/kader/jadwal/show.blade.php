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
    .jadwal-detail-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .jadwal-detail-page::before {
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

    .info-card {
        border: 1px solid rgba(226,232,240,.78);
        background: rgba(255,255,255,.62);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .info-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.24);
        box-shadow: 0 18px 34px rgba(15,23,42,.055);
    }

    .timeline-card {
        border: 1px solid rgba(255,255,255,.74);
        background: rgba(255,255,255,.56);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .timeline-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 34px rgba(15,23,42,.055);
    }
</style>
@endpush

@section('content')
<div class="jadwal-detail-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-calendar-check"></i>
                    Detail Jadwal Read Only
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Detail Jadwal Posyandu
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Kader melihat detail jadwal yang dibuat oleh Bidan sebagai acuan pelaksanaan kegiatan Posyandu.
                    Perubahan jadwal dilakukan oleh Bidan melalui modul Bidan.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                @if($routeHas('kader.jadwal.index'))
                    <a href="{{ route('kader.jadwal.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                @endif

                @if($routeHas('kader.dashboard'))
                    <a href="{{ route('kader.dashboard') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(15,23,42,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-slate-800">
                        <i class="fa-solid fa-chart-simple"></i>
                        Dashboard
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="glass-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 xl:grid-cols-[1.25fr_.75fr] xl:items-center">
            <div class="flex items-start gap-4">
                <div class="grid h-16 w-16 shrink-0 place-items-center rounded-3xl bg-emerald-50/90 text-emerald-700">
                    <i class="fa-solid fa-calendar-days text-xl"></i>
                </div>

                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
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

                    <h2 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                        {{ $jadwal->judul }}
                    </h2>

                    <p class="mt-2 text-sm font-bold leading-6 text-slate-500">
                        {{ $kategoriLabel }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-emerald-50/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">Tanggal</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $tanggalSingkat }}</p>
                </div>

                <div class="rounded-2xl bg-amber-50/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-amber-700">Waktu</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $waktuLabel }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- READ ONLY INFO --}}
    <section class="rounded-[26px] border border-emerald-100 bg-emerald-50/80 p-4">
        <div class="flex items-start gap-3">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70 text-emerald-700">
                <i class="fa-solid fa-lock"></i>
            </div>

            <div>
                <h3 class="text-sm font-black text-emerald-800">Jadwal Terkunci untuk Kader</h3>
                <p class="mt-1 text-xs font-bold leading-5 text-emerald-700">
                    Kader hanya melihat jadwal sebagai acuan kegiatan. Pembuatan, perubahan, pembatalan, dan penghapusan jadwal dilakukan oleh Bidan.
                </p>
            </div>
        </div>
    </section>

    {{-- DETAIL --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

        {{-- INFORMASI JADWAL --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-7">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Informasi Jadwal</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Detail utama kegiatan Posyandu.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tanggal Pelaksanaan</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $tanggal }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Waktu</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $waktuLabel }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Lokasi</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $jadwal->lokasi }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Target Sasaran</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $targetLabel }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Kategori Kegiatan</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $kategoriLabel }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Status</p>
                    <p class="mt-2">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $statusClass }}">
                            <i class="fa-solid {{ $statusData['icon'] ?? 'fa-circle-info' }}"></i>
                            {{ $statusData['text'] ?? ucfirst($jadwal->status ?? '-') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        {{-- DESKRIPSI --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-5">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Catatan Jadwal</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Keterangan dari Bidan terkait kegiatan ini.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                <p class="text-sm font-bold leading-6 text-slate-600">
                    {{ $jadwal->deskripsi ?: 'Tidak ada catatan tambahan dari Bidan.' }}
                </p>
            </div>
        </div>
    </section>

    {{-- ALUR SISTEM --}}
    <section class="glass-panel rounded-[30px] p-5">
        <div class="mb-4">
            <h3 class="text-lg font-black text-slate-900">Alur Jadwal</h3>
            <p class="mt-1 text-xs font-bold text-slate-400">
                Pembagian peran pada data jadwal Posyandu.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="timeline-card rounded-2xl border-emerald-100 bg-emerald-50/70 p-4">
                <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-white/70 text-emerald-700">
                    <i class="fa-solid fa-user-nurse"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Dibuat Bidan</h4>
                <p class="mt-1 text-xs font-bold leading-5 text-slate-500">
                    Bidan membuat dan mengatur jadwal kegiatan Posyandu.
                </p>
            </div>

            <div class="timeline-card rounded-2xl border-amber-100 bg-amber-50/70 p-4">
                <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-white/70 text-amber-700">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Digunakan Kader</h4>
                <p class="mt-1 text-xs font-bold leading-5 text-slate-500">
                    Kader menggunakan jadwal sebagai acuan pelayanan, absensi, dan pengukuran.
                </p>
            </div>

            <div class="timeline-card rounded-2xl border-sky-100 bg-sky-50/70 p-4">
                <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-white/70 text-sky-700">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Dilihat Warga</h4>
                <p class="mt-1 text-xs font-bold leading-5 text-slate-500">
                    Warga melihat jadwal aktif sebagai informasi kegiatan Posyandu.
                </p>
            </div>
        </div>
    </section>

    {{-- ACTION --}}
    <section class="glass-panel rounded-[26px] p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900">Detail jadwal selesai ditampilkan</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Untuk mengubah jadwal, gunakan akun Bidan melalui modul Jadwal Bidan.
                </p>
            </div>

            @if($routeHas('kader.jadwal.index'))
                <a href="{{ route('kader.jadwal.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.16)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-list"></i>
                    Daftar Jadwal
                </a>
            @endif
        </div>
    </section>
</div>
@endsection