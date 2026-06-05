@extends('layouts.kader')

@section('title', 'Detail Data Lansia')
@section('page-name', 'Detail Data Lansia')
@section('page-title', 'Detail Data Lansia')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $indexRoute = $routeHas('kader.data.lansia.index')
        ? route('kader.data.lansia.index')
        : url('/kader/data/lansia');

    $editRoute = $routeHas('kader.data.lansia.edit')
        ? route('kader.data.lansia.edit', $lansia->id)
        : null;

    $syncRoute = $routeHas('kader.data.lansia.sync')
        ? route('kader.data.lansia.sync', $lansia->id)
        : null;

    $tanggalLahir = filled($lansia->tanggal_lahir ?? null)
        ? Carbon::parse($lansia->tanggal_lahir)
        : null;

    if ($tanggalLahir) {
        $diff = $tanggalLahir->diff(now());
        $usiaText = $diff->y . ' tahun ' . $diff->m . ' bulan';
    } else {
        $usiaText = '-';
    }

    $genderLabel = match ($lansia->jenis_kelamin ?? null) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => '-',
    };

    $genderBadge = match ($lansia->jenis_kelamin ?? null) {
        'L' => 'border-sky-200 bg-sky-50 text-sky-700',
        'P' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-50 text-slate-600',
    };

    $linkedUser = $userTerhubung ?? $lansia->user ?? null;
    $akunTerhubung = filled($lansia->user_id ?? null) || filled($linkedUser);

    $akunBadge = $akunTerhubung
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-amber-200 bg-amber-50 text-amber-700';

    $initial = Str::upper(Str::substr(trim((string) ($lansia->nama_lengkap ?? 'L')), 0, 1)) ?: 'L';

    $kunjungans = collect($lansia->kunjungans ?? [])->take(5);

    $pemeriksaanTerakhir = $lansia->pemeriksaan_terakhir ?? null;

    if (! $pemeriksaanTerakhir) {
        $pemeriksaanTerakhir = optional($kunjungans->first(function ($item) {
            return filled(data_get($item, 'pemeriksaan'));
        }))->pemeriksaan;
    }

    $formatDate = function ($value, $format = 'd F Y') {
        return $value ? Carbon::parse($value)->translatedFormat($format) : '-';
    };

    $numberValue = function ($value, $unit = '') {
        if (blank($value)) {
            return '-';
        }

        $value = rtrim(rtrim((string) $value, '0'), '.');

        return trim($value . ' ' . $unit);
    };

    $kemandirianLabel = match ($lansia->tingkat_kemandirian ?? null) {
        'mandiri' => 'Mandiri',
        'bantuan_sebagian' => 'Bantuan Sebagian',
        'ketergantungan_penuh' => 'Ketergantungan Penuh',
        default => 'Belum Diisi',
    };

    $kemandirianBadge = match ($lansia->tingkat_kemandirian ?? null) {
        'mandiri' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'bantuan_sebagian' => 'border-amber-200 bg-amber-50 text-amber-700',
        'ketergantungan_penuh' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-50 text-slate-600',
    };

    $imtValue = $lansia->imt ?? null;

    if (blank($imtValue) && filled($lansia->berat_badan ?? null) && filled($lansia->tinggi_badan ?? null)) {
        $tinggiMeter = ((float) $lansia->tinggi_badan) / 100;
        $imtValue = $tinggiMeter > 0
            ? round(((float) $lansia->berat_badan) / ($tinggiMeter * $tinggiMeter), 2)
            : null;
    }

    $imtLabel = '-';

    if (filled($imtValue)) {
        $imtNumber = (float) $imtValue;

        $imtLabel = match (true) {
            $imtNumber < 18.5 => 'Kurus',
            $imtNumber < 25 => 'Normal',
            $imtNumber < 30 => 'Berlebih',
            default => 'Obesitas',
        };
    }

    $lastCheckDate = $pemeriksaanTerakhir
        ? $formatDate($pemeriksaanTerakhir->tanggal_periksa ?? $pemeriksaanTerakhir->created_at ?? null, 'd M Y')
        : 'Belum ada';

    $sessionType = session('success')
        ? 'success'
        : (session('warning') ? 'warning' : (session('error') ? 'error' : null));

    $sessionMessage = session('success') ?? session('warning') ?? session('error');
@endphp

@push('styles')
<style>
    .detail-page {
        background:
            radial-gradient(circle at 10% 8%, rgba(16, 185, 129, 0.16), transparent 30%),
            radial-gradient(circle at 88% 12%, rgba(245, 158, 11, 0.13), transparent 28%),
            radial-gradient(circle at 76% 88%, rgba(14, 165, 233, 0.10), transparent 30%),
            linear-gradient(135deg, #f8fafc 0%, #ecfdf5 48%, #eff6ff 100%);
    }

    .detail-page::before {
        content: "";
        position: fixed;
        inset: 0;
        pointer-events: none;
        background-image:
            linear-gradient(rgba(15, 23, 42, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, 0.035) 1px, transparent 1px);
        background-size: 34px 34px;
        mask-image: linear-gradient(to bottom, black, transparent 84%);
    }

    .glass-card {
        border: 1px solid rgba(255, 255, 255, 0.72);
        background: rgba(255, 255, 255, 0.78);
        backdrop-filter: blur(22px);
        box-shadow: 0 20px 65px rgba(15, 23, 42, 0.075);
    }

    .data-card {
        min-height: 96px;
        border-radius: 22px;
        border: 1px solid rgba(209, 250, 229, 0.88);
        background: rgba(255, 255, 255, 0.78);
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
        font-size: 14px;
        font-weight: 900;
        color: rgb(15, 23, 42);
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .summary-card {
        min-height: 128px;
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(18px);
        box-shadow: 0 14px 42px rgba(15, 23, 42, 0.055);
    }

    .section-head {
        border-bottom: 1px solid rgba(209, 250, 229, 0.82);
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.88), rgba(236, 253, 245, 0.70), rgba(255, 251, 235, 0.48));
        padding: 18px 22px;
    }

    .nexus-modal {
        opacity: 0;
        pointer-events: none;
        transition: opacity .22s ease;
    }

    .nexus-modal.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .nexus-modal-card {
        transform: translateY(16px) scale(.96);
        opacity: 0;
        transition: transform .24s ease, opacity .24s ease;
    }

    .nexus-modal.is-open .nexus-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="detail-page relative min-h-[calc(100vh-96px)] px-4 py-5 sm:px-6 lg:px-8">
    <div class="relative z-10 mx-auto max-w-6xl space-y-5">

        <section class="glass-card overflow-hidden rounded-[28px]">
            <div class="relative bg-gradient-to-br from-white/90 via-emerald-50/72 to-amber-50/48 p-5 sm:p-6">
                <div class="absolute -right-20 -top-24 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl"></div>
                <div class="absolute -bottom-24 left-12 h-56 w-56 rounded-full bg-amber-300/16 blur-3xl"></div>

                <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[24px] bg-gradient-to-br from-emerald-500 to-emerald-700 text-3xl font-black text-white shadow-[0_18px_42px_rgba(4,120,87,0.22)]">
                            {{ $initial }}
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10.5px] font-black uppercase tracking-[0.11em] text-emerald-700">
                                    Detail Lansia
                                </span>

                                <span class="rounded-full border px-3 py-1 text-[10.5px] font-black uppercase tracking-[0.11em] {{ $genderBadge }}">
                                    {{ $genderLabel }}
                                </span>

                                <span class="rounded-full border px-3 py-1 text-[10.5px] font-black uppercase tracking-[0.11em] {{ $akunBadge }}">
                                    {{ $akunTerhubung ? 'Akun Terhubung' : 'Belum Terhubung' }}
                                </span>

                                <span class="rounded-full border px-3 py-1 text-[10.5px] font-black uppercase tracking-[0.11em] {{ $kemandirianBadge }}">
                                    {{ $kemandirianLabel }}
                                </span>
                            </div>

                            <h1 class="mt-3 truncate text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                                {{ $lansia->nama_lengkap ?? '-' }}
                            </h1>

                            <p class="mt-1 text-sm font-bold text-slate-600">
                                NIK Lansia: {{ $lansia->nik ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid w-full grid-cols-2 gap-3 sm:w-[300px]">
                        <a href="{{ $indexRoute }}" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                            Kembali
                        </a>

                        @if($editRoute)
                            <a href="{{ $editRoute }}" class="inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-[0_12px_28px_rgba(4,120,87,0.20)] transition hover:bg-emerald-800">
                                Edit Data
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @if($sessionType && $sessionMessage)
            @php
                $alertClass = match ($sessionType) {
                    'error' => 'border-rose-200 bg-rose-50/90 text-rose-800',
                    'warning' => 'border-amber-200 bg-amber-50/90 text-amber-800',
                    default => 'border-emerald-200 bg-emerald-50/90 text-emerald-800',
                };

                $alertTitle = match ($sessionType) {
                    'error' => 'Aksi gagal',
                    'warning' => 'Perhatian',
                    default => 'Berhasil',
                };
            @endphp

            <div class="rounded-[22px] border px-5 py-4 shadow-sm backdrop-blur-xl {{ $alertClass }}">
                <p class="text-sm font-black">{{ $alertTitle }}</p>
                <p class="mt-1 text-sm font-semibold leading-6">{{ $sessionMessage }}</p>
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="summary-card bg-white/78 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Usia</p>
                <p class="mt-3 text-2xl font-black leading-tight text-slate-950">{{ $usiaText }}</p>
                <p class="mt-2 text-sm font-bold text-slate-500">Berdasarkan tanggal lahir</p>
            </div>

            <div class="summary-card bg-emerald-100/76 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-700">Kemandirian</p>
                <p class="mt-3 text-2xl font-black leading-tight text-emerald-950">{{ $kemandirianLabel }}</p>
                <p class="mt-2 text-sm font-bold text-emerald-800/70">Status aktivitas harian</p>
            </div>

            <div class="summary-card bg-amber-100/82 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-amber-700">Tekanan Darah</p>
                <p class="mt-3 text-2xl font-black leading-tight text-amber-950">{{ $lansia->tekanan_darah ?: '-' }}</p>
                <p class="mt-2 text-sm font-bold text-amber-800/70">Format sistolik/diastolik</p>
            </div>

            <div class="summary-card bg-cyan-100/76 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Status Akun</p>
                <p class="mt-3 text-2xl font-black leading-tight text-cyan-950">{{ $akunTerhubung ? 'Aktif' : 'Belum' }}</p>
                <p class="mt-2 text-sm font-bold text-cyan-800/70">Akses warga</p>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="glass-card rounded-[28px] p-5">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Akun Warga</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">{{ $akunTerhubung ? 'Sudah Terhubung' : 'Belum Terhubung' }}</h2>
                <p class="mt-2 min-h-[48px] text-sm font-semibold leading-6 text-slate-600">
                    {{ $akunTerhubung ? 'Data Lansia sudah terhubung dengan akun warga.' : 'Akun warga belum tersinkron dengan data Lansia ini.' }}
                </p>

                @if($linkedUser)
                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/80 p-4">
                        <p class="data-label">Akun</p>
                        <p class="data-value">{{ $linkedUser->name ?? '-' }}</p>
                    </div>
                @elseif($syncRoute)
                    <form id="syncLansiaForm" action="{{ $syncRoute }}" method="POST" class="mt-4">
                        @csrf
                        <button type="button" id="openSyncConfirm" class="inline-flex h-11 w-full items-center justify-center rounded-2xl bg-amber-400 px-5 text-sm font-black text-amber-950 shadow-[0_12px_28px_rgba(245,158,11,0.20)] transition hover:bg-amber-300">
                            Sinkron Akun
                        </button>
                    </form>
                @endif
            </div>

            <div class="glass-card rounded-[28px] p-5">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Antropometri</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">BB, TB, dan IMT</h2>
                <p class="mt-2 min-h-[48px] text-sm font-semibold leading-6 text-slate-600">
                    Ringkasan pengukuran fisik dasar Lansia.
                </p>

                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
                        <p class="data-label">BB</p>
                        <p class="data-value">{{ $numberValue($lansia->berat_badan ?? null, 'kg') }}</p>
                    </div>

                    <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
                        <p class="data-label">TB</p>
                        <p class="data-value">{{ $numberValue($lansia->tinggi_badan ?? null, 'cm') }}</p>
                    </div>

                    <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
                        <p class="data-label">IMT</p>
                        <p class="data-value">{{ $numberValue($imtValue ?? null) }}</p>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-[28px] p-5">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-cyan-700">Aksi Cepat</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Kelola Data</h2>
                <p class="mt-2 min-h-[48px] text-sm font-semibold leading-6 text-slate-600">
                    Perbaiki data Lansia atau kembali ke daftar utama.
                </p>

                <div class="mt-4 grid grid-cols-1 gap-3">
                    @if($editRoute)
                        <a href="{{ $editRoute }}" class="inline-flex h-11 w-full items-center justify-center rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-[0_12px_28px_rgba(4,120,87,0.20)] transition hover:bg-emerald-800">
                            Edit Data Lansia
                        </a>
                    @endif

                    <a href="{{ $indexRoute }}" class="inline-flex h-11 w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </section>

        <section class="glass-card overflow-hidden rounded-[28px]">
            <div class="section-head">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Profil Lansia</p>
                <h2 class="mt-1 text-2xl font-black text-slate-950">Data Dasar dan Keluarga</h2>
            </div>

            <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                <div class="data-card">
                    <p class="data-label">Nama Lengkap</p>
                    <p class="data-value">{{ $lansia->nama_lengkap ?? '-' }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">NIK Lansia</p>
                    <p class="data-value">{{ $lansia->nik ?? '-' }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Jenis Kelamin</p>
                    <p class="data-value">{{ $genderLabel }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Tempat Lahir</p>
                    <p class="data-value">{{ $lansia->tempat_lahir ?? '-' }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Tanggal Lahir</p>
                    <p class="data-value">{{ $tanggalLahir ? $tanggalLahir->translatedFormat('d F Y') : '-' }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Usia Saat Ini</p>
                    <p class="data-value">{{ $usiaText }}</p>
                </div>

                <div class="data-card lg:col-span-3">
                    <p class="data-label">Alamat</p>
                    <p class="data-value">{{ $lansia->alamat ?? '-' }}</p>
                </div>
            </div>
        </section>

        <section class="glass-card overflow-hidden rounded-[28px]">
            <div class="section-head">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Kesehatan Lansia</p>
                <h2 class="mt-1 text-2xl font-black text-slate-950">Pemeriksaan Dasar dan Keluhan</h2>
            </div>

            <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4 sm:p-6">
                <div class="data-card">
                    <p class="data-label">Berat Badan</p>
                    <p class="data-value">{{ $numberValue($lansia->berat_badan ?? null, 'kg') }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Tinggi Badan</p>
                    <p class="data-value">{{ $numberValue($lansia->tinggi_badan ?? null, 'cm') }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Lingkar Perut</p>
                    <p class="data-value">{{ $numberValue($lansia->lingkar_perut ?? null, 'cm') }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">IMT</p>
                    <p class="data-value">{{ filled($imtValue) ? $imtValue . ' ' . $imtLabel : '-' }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Tekanan Darah</p>
                    <p class="data-value">{{ $lansia->tekanan_darah ?: '-' }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Gula Darah</p>
                    <p class="data-value">{{ $numberValue($lansia->gula_darah ?? null, 'mg/dL') }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Kolesterol</p>
                    <p class="data-value">{{ $numberValue($lansia->kolesterol ?? null, 'mg/dL') }}</p>
                </div>

                <div class="data-card">
                    <p class="data-label">Asam Urat</p>
                    <p class="data-value">{{ $numberValue($lansia->asam_urat ?? null, 'mg/dL') }}</p>
                </div>

                <div class="data-card lg:col-span-2">
                    <p class="data-label">Penyakit Bawaan</p>
                    <p class="data-value">{{ $lansia->penyakit_bawaan ?: '-' }}</p>
                </div>

                <div class="data-card lg:col-span-2">
                    <p class="data-label">Keluhan</p>
                    <p class="data-value">{{ $lansia->keluhan ?: '-' }}</p>
                </div>
            </div>
        </section>

        <section class="glass-card overflow-hidden rounded-[28px]">
            <div class="section-head">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Riwayat</p>
                <h2 class="mt-1 text-2xl font-black text-slate-950">Riwayat Kunjungan</h2>
            </div>

            <div class="space-y-3 p-5 sm:p-6">
                @forelse($kunjungans as $kunjungan)
                    @php
                        $tanggalKunjungan = filled($kunjungan->tanggal_kunjungan ?? null)
                            ? Carbon::parse($kunjungan->tanggal_kunjungan)
                            : null;

                        $jenisKunjungan = $kunjungan->jenis_kunjungan ?? 'kunjungan';

                        $jenisLabel = match ($jenisKunjungan) {
                            'imunisasi' => 'Imunisasi',
                            'pemeriksaan' => 'Pemeriksaan Fisik',
                            default => 'Kunjungan Posyandu',
                        };

                        $petugas = data_get($kunjungan, 'petugas.name')
                            ?? data_get($kunjungan, 'petugas.nama_lengkap')
                            ?? '-';
                    @endphp

                    <div class="rounded-[24px] border border-emerald-100/80 bg-white/76 p-4 shadow-[0_12px_34px_rgba(15,23,42,0.04)]">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-sm font-black text-white">
                                {{ $loop->iteration }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="text-base font-black text-slate-950">{{ $jenisLabel }}</h3>
                                        <p class="mt-1 text-sm font-bold text-slate-500">
                                            {{ $tanggalKunjungan ? $tanggalKunjungan->translatedFormat('d M Y') : '-' }}
                                        </p>
                                    </div>

                                    <span class="inline-flex w-max rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.10em] text-emerald-700 ring-1 ring-emerald-200">
                                        {{ $petugas }}
                                    </span>
                                </div>

                                <p class="mt-3 text-sm font-semibold leading-6 text-slate-600">
                                    @if($kunjungan->pemeriksaan)
                                        BB {{ $numberValue($kunjungan->pemeriksaan->berat_badan ?? null, 'kg') }},
                                        TB {{ $numberValue($kunjungan->pemeriksaan->tinggi_badan ?? null, 'cm') }}
                                    @else
                                        Tidak ada data antropometri pada kunjungan ini.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[24px] border border-dashed border-emerald-300 bg-emerald-50/75 p-7 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-base font-black text-white">
                            0
                        </div>

                        <h3 class="mt-3 text-lg font-black text-slate-950">
                            Riwayat Masih Kosong
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                            Belum ada kunjungan atau pemeriksaan yang tercatat.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection

@push('modals')
<div id="nexusSyncConfirm"
     class="nexus-modal fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm">
    <div class="nexus-modal-card w-full max-w-md overflow-hidden rounded-[30px] border border-white/70 bg-white/90 shadow-[0_30px_100px_rgba(15,23,42,0.28)] backdrop-blur-2xl">
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-950 via-amber-700 to-emerald-800 px-6 py-6 text-white">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-16 left-8 h-28 w-44 rounded-t-[80px] bg-amber-300/15"></div>

            <div class="relative">
                <div class="mb-4 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-amber-50">
                    Sinkronisasi
                </div>

                <h3 class="text-2xl font-black tracking-tight">
                    Sinkronkan Akun Warga?
                </h3>

                <p class="mt-2 text-sm font-semibold leading-6 text-white/75">
                    Sistem akan mencari akun warga dengan NIK yang sama dengan NIK Lansia ini.
                </p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white via-emerald-50/50 to-amber-50/35 px-6 py-5">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold leading-6 text-amber-800">
                Sinkronisasi hanya berhasil jika akun warga sudah dibuat oleh Admin dengan NIK yang sesuai.
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <button type="button" id="nexusSyncCancel" class="h-12 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="button" id="nexusSyncOk" class="h-12 rounded-2xl bg-amber-500 px-5 text-sm font-black text-amber-950 shadow-[0_14px_35px_rgba(245,158,11,0.22)] transition hover:bg-amber-400">
                    Sinkron
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('nexusSyncConfirm');
        const openButton = document.getElementById('openSyncConfirm');
        const cancelButton = document.getElementById('nexusSyncCancel');
        const okButton = document.getElementById('nexusSyncOk');
        const form = document.getElementById('syncLansiaForm');

        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        function openModal() {
            modal?.classList.add('is-open');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            modal?.classList.remove('is-open');
            document.body.classList.remove('overflow-hidden');
        }

        openButton?.addEventListener('click', openModal);
        cancelButton?.addEventListener('click', closeModal);

        modal?.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal?.classList.contains('is-open')) closeModal();
        });

        okButton?.addEventListener('click', function () {
            closeModal();

            if (form) {
                HTMLFormElement.prototype.submit.call(form);
            }
        });
    });
</script>
@endpush