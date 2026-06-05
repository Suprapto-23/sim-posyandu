@extends('layouts.kader')

@section('title', 'Detail Agenda Posyandu')
@section('page-name', 'Detail Agenda Posyandu')
@section('page-title', 'Detail Agenda Posyandu')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $indexRoute = $routeHas('kader.jadwal.index')
        ? route('kader.jadwal.index')
        : url('/kader/jadwal');

    $tanggal = filled($jadwal->tanggal ?? null)
        ? Carbon::parse($jadwal->tanggal)
        : null;

    $waktuMulai = filled($jadwal->waktu_mulai ?? null)
        ? Carbon::parse($jadwal->waktu_mulai)->format('H:i')
        : '-';

    $waktuSelesai = filled($jadwal->waktu_selesai ?? null)
        ? Carbon::parse($jadwal->waktu_selesai)->format('H:i')
        : '-';

    $targetLabel = match ($jadwal->target_peserta ?? null) {
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        'semua' => 'Semua Sasaran',
        default => Str::title(str_replace('_', ' ', (string) ($jadwal->target_peserta ?? '-'))),
    };

    $kategoriLabel = match ($jadwal->kategori ?? null) {
        'posyandu' => 'Posyandu Rutin',
        'imunisasi' => 'Imunisasi',
        'pemeriksaan' => 'Pemeriksaan Klinis',
        'lainnya' => 'Kegiatan Lainnya',
        default => Str::title(str_replace('_', ' ', (string) ($jadwal->kategori ?? '-'))),
    };

    $statusLabel = match ($jadwal->status ?? null) {
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        default => Str::title(str_replace('_', ' ', (string) ($jadwal->status ?? '-'))),
    };

    $statusBadge = match ($jadwal->status ?? null) {
        'aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'selesai' => 'border-slate-200 bg-slate-50 text-slate-600',
        'dibatalkan' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-50 text-slate-600',
    };

    $targetBadge = match ($jadwal->target_peserta ?? null) {
        'balita' => 'border-sky-200 bg-sky-50 text-sky-700',
        'remaja' => 'border-violet-200 bg-violet-50 text-violet-700',
        'lansia' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        default => 'border-amber-200 bg-amber-50 text-amber-700',
    };

    $kategoriBadge = match ($jadwal->kategori ?? null) {
        'posyandu' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'imunisasi' => 'border-sky-200 bg-sky-50 text-sky-700',
        'pemeriksaan' => 'border-amber-200 bg-amber-50 text-amber-700',
        'lainnya' => 'border-violet-200 bg-violet-50 text-violet-700',
        default => 'border-slate-200 bg-slate-50 text-slate-600',
    };

    $isToday = $tanggal?->isToday() ?? false;

    $isUpcoming = $tanggal
        ? $tanggal->greaterThanOrEqualTo(now('Asia/Jakarta')->startOfDay())
        : false;

    $isPast = $tanggal
        ? $tanggal->lt(now('Asia/Jakarta')->startOfDay())
        : false;

    $createdAt = filled($jadwal->created_at ?? null)
        ? Carbon::parse($jadwal->created_at)->translatedFormat('d M Y, H:i')
        : '-';

    $updatedAt = filled($jadwal->updated_at ?? null)
        ? Carbon::parse($jadwal->updated_at)->translatedFormat('d M Y, H:i')
        : '-';

    $creatorName = data_get($jadwal, 'bidan.name')
        ?? data_get($jadwal, 'bidan.nama_lengkap')
        ?? data_get($jadwal, 'user.name')
        ?? data_get($jadwal, 'creator.name')
        ?? 'Bidan';

    $executionText = match (true) {
        ($jadwal->status ?? null) === 'dibatalkan' => 'Agenda dibatalkan atau ditunda.',
        ($jadwal->status ?? null) === 'selesai' => 'Agenda sudah selesai dilaksanakan.',
        $isToday => 'Agenda berlangsung hari ini. Siapkan data dan perlengkapan.',
        $isUpcoming => 'Agenda mendatang. Kader perlu menyiapkan kebutuhan layanan.',
        $isPast => 'Agenda sudah melewati tanggal pelaksanaan.',
        default => 'Periksa detail agenda untuk persiapan layanan.',
    };
@endphp

@push('styles')
<style>
    .detail-page {
        background:
            radial-gradient(circle at 8% 8%, rgba(16, 185, 129, 0.13), transparent 30%),
            radial-gradient(circle at 90% 10%, rgba(245, 158, 11, 0.11), transparent 28%),
            radial-gradient(circle at 76% 88%, rgba(14, 165, 233, 0.10), transparent 30%),
            linear-gradient(135deg, #f8fafc 0%, #ecfdf5 48%, #eff6ff 100%);
    }

    .detail-page::before {
        content: "";
        position: fixed;
        inset: 0;
        pointer-events: none;
        background-image:
            linear-gradient(rgba(15, 23, 42, 0.026) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, 0.026) 1px, transparent 1px);
        background-size: 34px 34px;
        mask-image: linear-gradient(to bottom, black, transparent 84%);
    }

    .glass-panel {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.76);
        backdrop-filter: blur(24px);
        box-shadow: 0 18px 60px rgba(15, 23, 42, 0.062);
    }

    .hero-panel {
        border: 1px solid rgba(255, 255, 255, 0.82);
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(236, 253, 245, 0.80), rgba(239, 246, 255, 0.74));
        backdrop-filter: blur(26px);
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.072);
    }

    .summary-card {
        min-height: 124px;
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.76);
        backdrop-filter: blur(18px);
        box-shadow: 0 14px 42px rgba(15, 23, 42, 0.052);
    }

    .data-card {
        min-height: 104px;
        border-radius: 22px;
        border: 1px solid rgba(209, 250, 229, 0.88);
        background: rgba(255, 255, 255, 0.76);
        padding: 16px;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.04);
    }

    .data-label {
        font-size: 10.5px;
        font-weight: 900;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgb(100, 116, 139);
    }

    .data-value {
        margin-top: 7px;
        font-size: 15px;
        font-weight: 900;
        color: rgb(30, 41, 59);
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .section-head {
        border-bottom: 1px solid rgba(209, 250, 229, 0.82);
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.90), rgba(236, 253, 245, 0.72), rgba(239, 246, 255, 0.58));
        padding: 18px 22px;
    }

    .timeline-dot {
        position: relative;
    }

    .timeline-dot::after {
        content: "";
        position: absolute;
        left: 18px;
        top: 42px;
        width: 2px;
        height: calc(100% - 18px);
        background: linear-gradient(to bottom, rgba(16, 185, 129, 0.28), rgba(14, 165, 233, 0.06));
    }

    .timeline-dot:last-child::after {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="detail-page relative min-h-[calc(100vh-96px)] px-4 py-5 sm:px-6 lg:px-8">
    <div class="relative z-10 mx-auto max-w-6xl space-y-5">

        {{-- HERO --}}
        <section class="hero-panel overflow-hidden rounded-[32px]">
            <div class="relative p-5 sm:p-6 lg:p-7">
                <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-emerald-300/20 blur-3xl"></div>
                <div class="absolute -bottom-24 left-12 h-64 w-64 rounded-full bg-amber-300/16 blur-3xl"></div>
                <div class="absolute bottom-10 right-1/3 h-24 w-24 rounded-full bg-sky-300/14 blur-2xl"></div>

                <div class="relative grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-center">
                    <div class="flex min-w-0 flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="flex h-24 w-24 shrink-0 flex-col items-center justify-center rounded-[28px] bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-[0_18px_42px_rgba(16,185,129,0.22)]">
                            <span class="text-xs font-black uppercase tracking-[0.14em]">
                                {{ $tanggal ? $tanggal->translatedFormat('M') : '-' }}
                            </span>
                            <span class="text-4xl font-black leading-none">
                                {{ $tanggal ? $tanggal->format('d') : '-' }}
                            </span>
                            <span class="mt-1 text-[10px] font-black uppercase tracking-[0.14em]">
                                {{ $tanggal ? $tanggal->format('Y') : '-' }}
                            </span>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full border px-3 py-1 text-[10.5px] font-black uppercase tracking-[0.11em] {{ $statusBadge }}">
                                    {{ $statusLabel }}
                                </span>

                                <span class="rounded-full border px-3 py-1 text-[10.5px] font-black uppercase tracking-[0.11em] {{ $targetBadge }}">
                                    {{ $targetLabel }}
                                </span>

                                <span class="rounded-full border px-3 py-1 text-[10.5px] font-black uppercase tracking-[0.11em] {{ $kategoriBadge }}">
                                    {{ $kategoriLabel }}
                                </span>

                                @if($isToday)
                                    <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[10.5px] font-black uppercase tracking-[0.11em] text-amber-700">
                                        Hari Ini
                                    </span>
                                @endif
                            </div>

                            <h1 class="mt-3 line-clamp-2 text-3xl font-black tracking-tight text-slate-800 sm:text-4xl">
                                {{ $jadwal->judul ?? '-' }}
                            </h1>

                            <p class="mt-2 max-w-2xl text-sm font-bold leading-6 text-slate-600">
                                Agenda pelayanan Posyandu untuk persiapan Kader. Halaman ini bersifat read-only karena jadwal dikelola oleh Bidan.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-white/80 bg-white/72 p-4 shadow-[0_16px_45px_rgba(15,23,42,0.055)] backdrop-blur-xl">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">
                            Ringkasan Agenda
                        </p>

                        <p class="mt-3 text-2xl font-black text-slate-800">
                            {{ $waktuMulai }}
                        </p>

                        <p class="mt-1 text-sm font-bold leading-6 text-slate-500">
                            sampai {{ $waktuSelesai }} WIB
                        </p>

                        <a href="{{ $indexRoute }}"
                           class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-2xl border border-emerald-100 bg-white/78 px-4 text-sm font-black text-slate-700 shadow-sm transition hover:bg-emerald-50">
                            Kembali ke Jadwal
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ALERT --}}
        @if(session('success') || session('error') || session('warning'))
            @php
                $flashType = session('error') ? 'error' : (session('warning') ? 'warning' : 'success');
                $flashText = session('error') ?: (session('warning') ?: session('success'));

                $flashClass = match ($flashType) {
                    'error' => 'border-rose-200 bg-rose-50/90 text-rose-800',
                    'warning' => 'border-amber-200 bg-amber-50/90 text-amber-800',
                    default => 'border-emerald-200 bg-emerald-50/90 text-emerald-800',
                };

                $flashTitle = match ($flashType) {
                    'error' => 'Aksi gagal',
                    'warning' => 'Perhatian',
                    default => 'Berhasil',
                };
            @endphp

            <div class="rounded-[22px] border px-5 py-4 shadow-sm backdrop-blur-xl {{ $flashClass }}">
                <p class="text-sm font-black">{{ $flashTitle }}</p>
                <p class="mt-1 text-sm font-semibold leading-6">{{ $flashText }}</p>
            </div>
        @endif

        {{-- DASHBOARD SUMMARY --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="summary-card bg-white/78 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                    Tanggal
                </p>

                <p class="mt-3 text-2xl font-black leading-tight text-slate-800">
                    {{ $tanggal ? $tanggal->translatedFormat('d M Y') : '-' }}
                </p>

                <p class="mt-2 text-sm font-bold text-slate-500">
                    {{ $tanggal ? $tanggal->translatedFormat('l') : '-' }}
                </p>
            </div>

            <div class="summary-card bg-emerald-100/72 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-700">
                    Waktu
                </p>

                <p class="mt-3 text-2xl font-black leading-tight text-emerald-800">
                    {{ $waktuMulai }}
                </p>

                <p class="mt-2 text-sm font-bold text-emerald-700/70">
                    sampai {{ $waktuSelesai }} WIB
                </p>
            </div>

            <div class="summary-card bg-amber-100/76 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-amber-700">
                    Status
                </p>

                <p class="mt-3 text-2xl font-black leading-tight text-amber-800">
                    {{ $statusLabel }}
                </p>

                <p class="mt-2 text-sm font-bold text-amber-700/70">
                    {{ $executionText }}
                </p>
            </div>

            <div class="summary-card bg-sky-100/72 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-sky-700">
                    Sasaran
                </p>

                <p class="mt-3 text-2xl font-black leading-tight text-sky-800">
                    {{ $targetLabel }}
                </p>

                <p class="mt-2 text-sm font-bold text-sky-700/70">
                    Target peserta layanan
                </p>
            </div>
        </section>

        {{-- PREPARATION PANEL --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="glass-panel rounded-[28px] p-5">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">
                    Lokasi Kegiatan
                </p>

                <h2 class="mt-2 text-xl font-black text-slate-800">
                    {{ $jadwal->lokasi ?? '-' }}
                </h2>

                <p class="mt-2 min-h-[48px] text-sm font-semibold leading-6 text-slate-600">
                    Pastikan lokasi kegiatan, meja layanan, dan perlengkapan pendukung sudah siap sebelum jam pelaksanaan.
                </p>
            </div>

            <div class="glass-panel rounded-[28px] p-5">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">
                    Persiapan Kader
                </p>

                <h2 class="mt-2 text-xl font-black text-slate-800">
                    Kesiapan Layanan
                </h2>

                <p class="mt-2 min-h-[48px] text-sm font-semibold leading-6 text-slate-600">
                    Siapkan absensi, alat ukur, data sasaran, dan catatan layanan sesuai kategori kegiatan.
                </p>
            </div>

            <div class="glass-panel rounded-[28px] p-5">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sky-700">
                    Penanggung Jawab
                </p>

                <h2 class="mt-2 text-xl font-black text-slate-800">
                    {{ $creatorName }}
                </h2>

                <p class="mt-2 min-h-[48px] text-sm font-semibold leading-6 text-slate-600">
                    Jadwal ini dikelola oleh Bidan. Kader hanya melihat detail agenda dan menyiapkan pelaksanaan.
                </p>
            </div>
        </section>

        {{-- MAIN DETAIL --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">

            <section class="glass-panel overflow-hidden rounded-[28px]">
                <div class="section-head">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">
                        Detail Agenda
                    </p>

                    <h2 class="mt-1 text-2xl font-black text-slate-800">
                        Informasi Pelaksanaan Posyandu
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                    <div class="data-card">
                        <p class="data-label">Judul Kegiatan</p>
                        <p class="data-value">{{ $jadwal->judul ?? '-' }}</p>
                    </div>

                    <div class="data-card">
                        <p class="data-label">Kategori</p>
                        <p class="data-value">{{ $kategoriLabel }}</p>
                    </div>

                    <div class="data-card">
                        <p class="data-label">Target Peserta</p>
                        <p class="data-value">{{ $targetLabel }}</p>
                    </div>

                    <div class="data-card">
                        <p class="data-label">Tanggal</p>
                        <p class="data-value">{{ $tanggal ? $tanggal->translatedFormat('l, d F Y') : '-' }}</p>
                    </div>

                    <div class="data-card">
                        <p class="data-label">Jam Mulai</p>
                        <p class="data-value">{{ $waktuMulai }} WIB</p>
                    </div>

                    <div class="data-card">
                        <p class="data-label">Jam Selesai</p>
                        <p class="data-value">{{ $waktuSelesai }} WIB</p>
                    </div>

                    <div class="data-card lg:col-span-3">
                        <p class="data-label">Lokasi</p>
                        <p class="data-value">{{ $jadwal->lokasi ?? '-' }}</p>
                    </div>

                    <div class="data-card lg:col-span-3">
                        <p class="data-label">Catatan / Deskripsi</p>
                        <p class="data-value">
                            {{ $jadwal->deskripsi ?: 'Tidak ada catatan tambahan.' }}
                        </p>
                    </div>
                </div>
            </section>

            <aside class="space-y-5">
                <section class="glass-panel rounded-[28px] p-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">
                        Alur Persiapan
                    </p>

                    <h2 class="mt-2 text-xl font-black text-slate-800">
                        Checklist Kader
                    </h2>

                    <div class="mt-5 space-y-4">
                        <div class="timeline-dot flex gap-3">
                            <div class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
                                1
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">Cek sasaran</p>
                                <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                    Pastikan target peserta sesuai dengan data sasaran.
                                </p>
                            </div>
                        </div>

                        <div class="timeline-dot flex gap-3">
                            <div class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-sm font-black text-amber-700">
                                2
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">Siapkan alat</p>
                                <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                    Siapkan alat ukur dan perlengkapan pelayanan.
                                </p>
                            </div>
                        </div>

                        <div class="timeline-dot flex gap-3">
                            <div class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sm font-black text-sky-700">
                                3
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">Catat layanan</p>
                                <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                    Gunakan menu absensi dan pengukuran saat kegiatan berlangsung.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="glass-panel rounded-[28px] p-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">
                        Catatan Sistem
                    </p>

                    <div class="mt-4 space-y-3">
                        <div class="data-card min-h-0">
                            <p class="data-label">Status Jadwal</p>
                            <p class="data-value">{{ $statusLabel }}</p>
                        </div>

                        <div class="data-card min-h-0">
                            <p class="data-label">Dibuat</p>
                            <p class="data-value">{{ $createdAt }}</p>
                        </div>

                        <div class="data-card min-h-0">
                            <p class="data-label">Terakhir Diperbarui</p>
                            <p class="data-value">{{ $updatedAt }}</p>
                        </div>
                    </div>

                    <a href="{{ $indexRoute }}"
                       class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 text-sm font-black text-white shadow-[0_12px_28px_rgba(16,185,129,0.22)] transition hover:bg-emerald-700">
                        Kembali ke Daftar Jadwal
                    </a>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection