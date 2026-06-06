@extends('layouts.user')

@section('title', 'Detail Lansia')
@section('page_title', 'Detail Lansia')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $dataLansia = $lansia ?? $dataLansia ?? $data ?? null;

    $routeTo = function ($names, $params = []) {
        foreach ((array) $names as $name) {
            if (Route::has($name)) {
                return route($name, $params);
            }
        }

        return '#';
    };

    $backRoute = $routeTo('user.monitoring.index');
    $dashboardRoute = $routeTo('user.dashboard');

    $metrics = collect($metrics ?? []);
    $healthMetrics = collect($healthMetrics ?? []);
    $trend = collect($trend ?? []);
    $riwayatCards = collect($riwayatCards ?? []);

    $ptmAnalysis = $ptmAnalysis ?? [
        'label' => 'Menunggu Data',
        'message' => 'Data pemeriksaan dasar belum tersedia.',
        'suggestion' => 'Lakukan pemeriksaan kesehatan dasar di Posyandu.',
        'tone' => 'slate',
        'icon' => 'fa-circle-info',
        'risks' => [],
    ];

    $genderLabel = match ($dataLansia->jenis_kelamin ?? null) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => 'Belum diisi',
    };

    $initial = strtoupper(substr(trim((string) ($dataLansia->nama_lengkap ?? 'L')), 0, 1)) ?: 'L';

    $formatDate = fn ($value, $format = 'd F Y') => $value
        ? Carbon::parse($value)->translatedFormat($format)
        : '-';

    $kemandirianLabel = match ($dataLansia->tingkat_kemandirian ?? null) {
        'mandiri' => 'Mandiri',
        'bantuan_sebagian' => 'Bantuan Sebagian',
        'ketergantungan_penuh' => 'Ketergantungan Penuh',
        default => 'Belum Diisi',
    };

    $analysisTone = match ($ptmAnalysis['tone'] ?? 'slate') {
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
        default => 'border-slate-200 bg-slate-50 text-slate-600',
    };

    $metricTone = function ($tone) {
        return match ($tone) {
            'emerald' => 'border-emerald-100 bg-emerald-50/78 text-emerald-800',
            'sky' => 'border-sky-100 bg-sky-50/78 text-sky-800',
            'amber' => 'border-amber-100 bg-amber-50/78 text-amber-800',
            'rose' => 'border-rose-100 bg-rose-50/78 text-rose-800',
            default => 'border-slate-100 bg-white/76 text-slate-800',
        };
    };

    $badgeTone = function ($tone) {
        return match ($tone) {
            'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'sky' => 'border-sky-200 bg-sky-50 text-sky-700',
            'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
            'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-50 text-slate-600',
        };
    };
@endphp

@push('styles')
<style>
    .lansia-page {
        background:
            radial-gradient(circle at 8% 8%, rgba(245, 158, 11, .13), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(16, 185, 129, .13), transparent 26%),
            radial-gradient(circle at 78% 88%, rgba(14, 165, 233, .10), transparent 28%),
            linear-gradient(135deg, #f8fafc 0%, #fffbeb 38%, #ecfdf5 100%);
    }

    .ls-enter {
        opacity: 0;
        animation: lsEnter .36s cubic-bezier(.16, 1, .3, 1) forwards;
    }

    .ls-enter-2 {
        opacity: 0;
        animation: lsEnter .36s cubic-bezier(.16, 1, .3, 1) .07s forwards;
    }

    @keyframes lsEnter {
        from {
            opacity: 0;
            transform: translateY(12px) scale(.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .ls-glass {
        border: 1px solid rgba(255, 255, 255, .76);
        background: rgba(255, 255, 255, .70);
        backdrop-filter: blur(20px);
        box-shadow: 0 16px 46px rgba(15, 23, 42, .055);
    }

    .ls-card {
        border: 1px solid rgba(226, 232, 240, .78);
        background: rgba(255, 255, 255, .74);
        backdrop-filter: blur(16px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .04);
    }

    .ls-hover {
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .ls-hover:hover {
        transform: translateY(-2px);
        border-color: rgba(16, 185, 129, .28);
        box-shadow: 0 16px 38px rgba(15, 23, 42, .065);
    }

    .ls-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .ls-scroll::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .55);
        border-radius: 999px;
    }

    @media (prefers-reduced-motion: reduce) {
        .ls-enter,
        .ls-enter-2 {
            animation: none;
            opacity: 1;
        }

        .ls-hover,
        .ls-hover:hover {
            transition: none;
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="lansia-page -mx-4 -my-4 min-h-[calc(100vh-96px)] px-4 py-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- HERO --}}
        <section class="ls-enter grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="ls-glass relative overflow-hidden rounded-[30px] p-5 sm:p-6">
                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-amber-300/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 left-8 h-56 w-56 rounded-full bg-emerald-300/16 blur-3xl"></div>

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[26px] bg-gradient-to-br from-amber-400 to-emerald-500 text-3xl font-black text-white shadow-[0_18px_42px_rgba(245,158,11,.20)]">
                        {{ $initial }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-amber-700">
                                Lansia
                            </span>

                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-emerald-700">
                                {{ $genderLabel }}
                            </span>

                            <span class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-sky-700">
                                {{ $kemandirianLabel }}
                            </span>
                        </div>

                        <h1 class="mt-3 line-clamp-2 text-3xl font-black tracking-tight text-slate-800 sm:text-4xl">
                            {{ $dataLansia->nama_lengkap ?? '-' }}
                        </h1>

                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                            NIK {{ $dataLansia->nik ?? '-' }} •
                            {{ $formatDate($dataLansia->tanggal_lahir ?? null) }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ $backRoute }}"
                               class="smooth-route inline-flex h-10 items-center justify-center rounded-2xl border border-amber-100 bg-white/82 px-4 text-xs font-black uppercase tracking-[0.12em] text-amber-700 transition hover:bg-amber-50">
                                Kembali
                            </a>

                            <a href="{{ $dashboardRoute }}"
                               class="smooth-route inline-flex h-10 items-center justify-center rounded-2xl bg-emerald-600 px-4 text-xs font-black uppercase tracking-[0.12em] text-white shadow-[0_12px_26px_rgba(16,185,129,.20)] transition hover:bg-emerald-700">
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PTM ANALYSIS --}}
            <div class="ls-glass rounded-[30px] p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-amber-700">
                    Analisis PTM
                </p>

                <div class="mt-4 rounded-[24px] border p-4 {{ $analysisTone }}">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[17px] bg-white/75 text-lg shadow-sm">
                            <i class="fas {{ $ptmAnalysis['icon'] ?? 'fa-circle-info' }}"></i>
                        </div>

                        <div>
                            <h2 class="text-xl font-black">
                                {{ $ptmAnalysis['label'] ?? 'Menunggu Data' }}
                            </h2>

                            <p class="mt-2 text-sm font-bold leading-6 opacity-80">
                                {{ $ptmAnalysis['message'] ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-sm font-semibold leading-6 text-slate-600">
                    {{ $ptmAnalysis['suggestion'] ?? 'Lakukan pemeriksaan kesehatan dasar secara berkala.' }}
                </p>
            </div>
        </section>

        {{-- PRIMARY METRICS --}}
        <section class="ls-enter-2 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @forelse($metrics as $metric)
                <div class="rounded-[24px] border p-5 shadow-[0_10px_28px_rgba(15,23,42,.04)] backdrop-blur-xl {{ $metricTone($metric['tone'] ?? 'slate') }}">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] opacity-70">
                        {{ $metric['label'] ?? '-' }}
                    </p>

                    <p class="mt-3 text-2xl font-black leading-tight">
                        {{ $metric['value'] ?? '-' }}
                    </p>

                    <p class="mt-1 text-sm font-bold opacity-70">
                        {{ $metric['caption'] ?? '-' }}
                    </p>
                </div>
            @empty
                <div class="rounded-[24px] border border-slate-100 bg-white/76 p-5 text-slate-700 shadow-sm">
                    <p class="text-sm font-black">Data ringkasan belum tersedia.</p>
                </div>
            @endforelse
        </section>

        {{-- SECONDARY HEALTH METRICS --}}
        <section class="ls-enter-2 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach($healthMetrics as $metric)
                <div class="ls-card rounded-[22px] p-4 {{ $metricTone($metric['tone'] ?? 'slate') }}">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] opacity-70">
                        {{ $metric['label'] ?? '-' }}
                    </p>

                    <p class="mt-2 truncate text-xl font-black leading-tight">
                        {{ $metric['value'] ?? '-' }}
                    </p>

                    <p class="mt-1 truncate text-xs font-bold opacity-70">
                        {{ $metric['caption'] ?? '-' }}
                    </p>
                </div>
            @endforeach
        </section>

        <section class="ls-enter-2 grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">

            <div class="space-y-5">

                {{-- TREND --}}
                <div class="ls-glass overflow-hidden rounded-[30px]">
                    <div class="border-b border-amber-100/80 bg-gradient-to-r from-white/86 via-amber-50/72 to-emerald-50/62 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-amber-700">
                            Tren Kesehatan
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-800">
                            Grafik Ringkas Gula Darah
                        </h2>
                    </div>

                    <div class="p-5">
                        @if($trend->isNotEmpty())
                            <div class="flex h-48 items-end gap-3 rounded-[26px] border border-amber-100 bg-gradient-to-br from-white/82 via-amber-50/52 to-emerald-50/45 p-4">
                                @foreach($trend as $point)
                                    <div class="flex min-w-0 flex-1 flex-col items-center justify-end gap-2">
                                        <div class="flex h-32 w-full items-end justify-center">
                                            <div class="w-full max-w-[34px] rounded-t-2xl bg-gradient-to-t from-amber-500 to-emerald-400 shadow-[0_10px_24px_rgba(245,158,11,.18)]"
                                                 style="height: {{ $point['height'] ?? 14 }}%;"></div>
                                        </div>

                                        <p class="truncate text-[10px] font-black text-slate-500">
                                            {{ $point['label'] ?? '-' }}
                                        </p>

                                        <p class="text-[10px] font-black text-amber-700">
                                            {{ filled($point['gula'] ?? null) ? $point['gula'] : '-' }}
                                        </p>

                                        <p class="text-[9px] font-black text-slate-400">
                                            Tensi {{ filled($point['sistolik'] ?? null) ? $point['sistolik'] : '-' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-[26px] border border-dashed border-amber-300 bg-amber-50/55 p-8 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-xl text-amber-500 shadow-sm">
                                    <i class="fas fa-chart-column"></i>
                                </div>

                                <h3 class="mt-4 text-base font-black text-slate-800">
                                    Tren Belum Tersedia
                                </h3>

                                <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                    Grafik akan muncul setelah ada riwayat pemeriksaan gula darah.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- PROFILE --}}
                <div class="ls-glass overflow-hidden rounded-[30px]">
                    <div class="border-b border-emerald-100/80 bg-gradient-to-r from-white/86 via-emerald-50/72 to-amber-50/62 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-emerald-700">
                            Profil Lansia
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-800">
                            Identitas dan Data Dasar
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">
                        <div class="ls-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Nama</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $dataLansia->nama_lengkap ?? '-' }}</p>
                        </div>

                        <div class="ls-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">NIK</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $dataLansia->nik ?? '-' }}</p>
                        </div>

                        <div class="ls-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Tempat Lahir</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $dataLansia->tempat_lahir ?? '-' }}</p>
                        </div>

                        <div class="ls-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Tanggal Lahir</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $formatDate($dataLansia->tanggal_lahir ?? null) }}</p>
                        </div>

                        <div class="ls-card rounded-[22px] p-4 sm:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Alamat</p>
                            <p class="mt-2 text-sm font-black leading-6 text-slate-800">{{ $dataLansia->alamat ?? '-' }}</p>
                        </div>

                        <div class="ls-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Penyakit Bawaan</p>
                            <p class="mt-2 text-sm font-black leading-6 text-slate-800">{{ $dataLansia->penyakit_bawaan ?: 'Tidak ada catatan.' }}</p>
                        </div>

                        <div class="ls-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Keluhan</p>
                            <p class="mt-2 text-sm font-black leading-6 text-slate-800">{{ $dataLansia->keluhan ?: 'Tidak ada keluhan.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIWAYAT --}}
            <aside class="ls-glass flex max-h-[820px] min-h-[520px] flex-col overflow-hidden rounded-[30px]">
                <div class="border-b border-amber-100/80 bg-gradient-to-r from-white/86 via-amber-50/72 to-emerald-50/62 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.20em] text-amber-700">
                        Riwayat Klinis
                    </p>
                    <h2 class="mt-1 text-xl font-black text-slate-800">
                        Pemeriksaan PTM
                    </h2>
                </div>

                <div class="ls-scroll flex-1 space-y-3 overflow-y-auto p-4">
                    @forelse($riwayatCards as $item)
                        <article class="rounded-[24px] border border-amber-100 bg-white/72 p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-slate-800">
                                        {{ $item['tanggal'] ?? '-' }}
                                    </p>

                                    <p class="mt-1 text-xs font-bold text-slate-500">
                                        Pemeriksaan Posyandu Lansia
                                    </p>
                                </div>

                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] text-emerald-700">
                                    {{ $item['status'] ?? 'Tervalidasi' }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">Tensi</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['tensi'] ?? '-' }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">Gula</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['gula'] ?? '-' }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">IMT</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['imt'] ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="mt-2 grid grid-cols-3 gap-2">
                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">Kolesterol</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['kolesterol'] ?? '-' }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">Asam Urat</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['asam_urat'] ?? '-' }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">Perut</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['lingkar_perut'] ?? '-' }}</p>
                                </div>
                            </div>

                            <p class="mt-3 line-clamp-2 text-sm font-semibold leading-6 text-slate-500">
                                {{ $item['keluhan'] ?? 'Tidak ada catatan keluhan.' }}
                            </p>

                            @if(!empty($item['edukasi']))
                                <div class="mt-3 rounded-[18px] border border-emerald-100 bg-emerald-50/70 px-3 py-3 text-sm font-semibold leading-6 text-emerald-700">
                                    <span class="font-black">Edukasi:</span> {{ $item['edukasi'] }}
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="flex h-full min-h-[260px] flex-col items-center justify-center rounded-[24px] border border-dashed border-amber-300 bg-amber-50/55 p-8 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-xl text-amber-500 shadow-sm">
                                <i class="fas fa-notes-medical"></i>
                            </div>

                            <h3 class="mt-4 text-base font-black text-slate-800">
                                Riwayat Masih Kosong
                            </h3>

                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                Riwayat pemeriksaan akan muncul setelah data divalidasi oleh Bidan.
                            </p>
                        </div>
                    @endforelse
                </div>
            </aside>
        </section>
    </div>
</div>
@endsection